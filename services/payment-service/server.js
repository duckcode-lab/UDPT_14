const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const { v4: uuidv4 } = require('uuid');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 8004;

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
        console.log('[Payment Service] Database connected, transactions table initialized.');
    } catch (err) {
        console.error('[Payment Service] DB Init Error (will retry):', err.message);
        setTimeout(initDB, 5000);
    }
}

// ------------------------------------
// Health Check
// ------------------------------------
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
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// ------------------------------------
// Xử lý thanh toán giả lập (Idempotency)
// ------------------------------------
app.post('/process', async (req, res) => {
    try {
        const { booking_id, user_id, amount, payment_method } = req.body;
        if (!booking_id || !amount) {
            return res.status(400).json({ error: 'Thiếu thông tin booking_id hoặc amount' });
        }

        // Kiểm tra Idempotency (Trùng lặp giao dịch)
        const [existing] = await pool.query('SELECT * FROM transactions WHERE booking_id = ?', [booking_id]);
        if (existing.length > 0) {
            return res.json({
                message: 'Giao dịch đã được thanh toán trước đó (Idempotent)',
                transaction: existing[0]
            });
        }

        const transactionId = uuidv4();
        await pool.query(
            'INSERT INTO transactions (id, booking_id, user_id, amount, payment_method, status) VALUES (?, ?, ?, ?, ?, ?)',
            [transactionId, booking_id, user_id || 1, amount, payment_method || 'VNPAY_MOCK', 'SUCCESS']
        );

        res.status(201).json({
            message: 'Thanh toán thành công',
            transaction: {
                id: transactionId,
                booking_id,
                amount,
                status: 'SUCCESS'
            }
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`[Payment Service] Running on port ${PORT}`);
    initDB();
});
