const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');
const { v4: uuidv4 } = require('uuid');
const path = require('path');
const fs = require('fs');

const app = express();
app.use(cors());
app.use(express.json());

const HTTP_PORT = process.env.PORT || 8004;
const GRPC_PORT = process.env.GRPC_PORT || 50052;

// Cấu hình Database Pool
const dbConfig = {
    host: process.env.DB_HOST || 'payment-db',
    port: parseInt(process.env.DB_PORT || '3306'),
    user: process.env.DB_USER || 'payment_user',
    password: process.env.DB_PASSWORD || 'payment_secret',
    database: process.env.DB_NAME || 'payment_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

let pool;

async function initDB() {
    try {
        pool = mysql.createPool(dbConfig);
        await pool.query(`
            CREATE TABLE IF NOT EXISTS transactions (
                id VARCHAR(36) PRIMARY KEY,
                booking_id VARCHAR(36) NOT NULL UNIQUE,
                user_id INT NOT NULL,
                amount INT NOT NULL,
                payment_method VARCHAR(50) DEFAULT 'VNPAY_MOCK',
                status ENUM('SUCCESS', 'FAILED') DEFAULT 'SUCCESS',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        `);
        console.log('[Payment Service] Database connected, transactions table ready.');
    } catch (err) {
        console.error('[Payment Service] DB Init Error (will retry):', err.message);
        setTimeout(initDB, 5000);
    }
}

// =========================================================================
// KHỞI TẠO gRPC SERVER (Port 50052)
// Cung cấp RPC ProcessPayment cho Booking Service gọi nội bộ
// =========================================================================
function startGrpcServer() {
    let protoPath = path.join(__dirname, 'proto', 'payment.proto');
    if (!fs.existsSync(protoPath)) {
        protoPath = path.join(__dirname, '..', '..', 'proto', 'payment.proto');
    }

    if (!fs.existsSync(protoPath)) {
        console.error('[Payment Service] Không tìm thấy file payment.proto tại:', protoPath);
        return;
    }

    const packageDefinition = protoLoader.loadSync(protoPath, {
        keepCase: true,
        longs: String,
        enums: String,
        defaults: true,
        oneofs: true
    });

    const paymentProto = grpc.loadPackageDefinition(packageDefinition).payment;
    const server = new grpc.Server();

    server.addService(paymentProto.PaymentService.service, {
        // RPC: Xử lý thanh toán có Idempotency
        ProcessPayment: async (call, callback) => {
            const { booking_id, user_id, amount, payment_method } = call.request;
            try {
                // 1. Kiểm tra Idempotency
                const [existing] = await pool.query('SELECT * FROM transactions WHERE booking_id = ?', [booking_id]);
                if (existing.length > 0) {
                    const tx = existing[0];
                    return callback(null, {
                        success: true,
                        transaction_id: tx.id,
                        booking_id: tx.booking_id,
                        amount: tx.amount,
                        status: tx.status,
                        message: 'Giao dịch đã được thanh toán trước đó (Idempotent)',
                        created_at: new Date(tx.created_at).toISOString()
                    });
                }

                // 2. Tạo giao dịch mới
                const transactionId = uuidv4();
                await pool.query(
                    'INSERT INTO transactions (id, booking_id, user_id, amount, payment_method, status) VALUES (?, ?, ?, ?, ?, "SUCCESS")',
                    [transactionId, booking_id, user_id || 1, amount, payment_method || 'VNPAY_MOCK']
                );

                callback(null, {
                    success: true,
                    transaction_id: transactionId,
                    booking_id,
                    amount,
                    status: 'SUCCESS',
                    message: 'Thanh toán giả lập thành công',
                    created_at: new Date().toISOString()
                });
            } catch (err) {
                console.error('[Payment Service] gRPC ProcessPayment error:', err.message);
                callback(null, {
                    success: false,
                    transaction_id: '',
                    booking_id,
                    amount,
                    status: 'FAILED',
                    message: 'Lỗi thanh toán: ' + err.message,
                    created_at: new Date().toISOString()
                });
            }
        },

        // RPC: Tra cứu trạng thái giao dịch
        GetPaymentStatus: async (call, callback) => {
            const { booking_id } = call.request;
            try {
                const [rows] = await pool.query('SELECT * FROM transactions WHERE booking_id = ?', [booking_id]);
                if (rows.length === 0) {
                    return callback({ code: grpc.status.NOT_FOUND, details: 'Không tìm thấy giao dịch cho mã booking này' });
                }

                const tx = rows[0];
                callback(null, {
                    success: true,
                    transaction_id: tx.id,
                    booking_id: tx.booking_id,
                    amount: tx.amount,
                    status: tx.status,
                    message: 'Tra cứu giao dịch thành công',
                    created_at: new Date(tx.created_at).toISOString()
                });
            } catch (err) {
                callback(err);
            }
        }
    });

    server.bindAsync(`0.0.0.0:${GRPC_PORT}`, grpc.ServerCredentials.createInsecure(), (err, port) => {
        if (err) {
            console.error('[Payment Service] Lỗi khởi động gRPC Server:', err.message);
            return;
        }
        console.log(`[Payment Service] gRPC Server running on port ${port}`);
    });
}

// =========================================================================
// REST API (Port 8004)
// =========================================================================

// Health check
app.get('/health', async (req, res) => {
    let dbStatus = 'DISCONNECTED';
    try {
        if (pool) {
            await pool.query('SELECT 1');
            dbStatus = 'CONNECTED';
        }
    } catch (err) {
        dbStatus = 'ERROR: ' + err.message;
    }

    res.json({
        service: 'Payment Service',
        status: 'UP',
        database: dbStatus,
        http_port: HTTP_PORT,
        grpc_port: GRPC_PORT,
        timestamp: new Date().toISOString()
    });
});

// Tra cứu giao dịch theo booking_id
app.get('/transactions/:booking_id', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM transactions WHERE booking_id = ?', [req.params.booking_id]);
        if (rows.length === 0) return res.status(404).json({ error: 'Không tìm thấy giao dịch' });
        res.json({ success: true, transaction: rows[0] });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(HTTP_PORT, () => {
    console.log(`[Payment Service] HTTP REST API running on port ${HTTP_PORT}`);
    initDB();
    startGrpcServer();
});
