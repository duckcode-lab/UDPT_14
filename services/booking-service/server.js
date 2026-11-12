const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const Redis = require('ioredis');
const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');
const { Kafka } = require('kafkajs');
const CircuitBreaker = require('opossum');
const { v4: uuidv4 } = require('uuid');
const path = require('path');
const fs = require('fs');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 8003;
const SHOW_GRPC_HOST = process.env.SHOW_GRPC_HOST || 'show-service:50051';
const PAYMENT_GRPC_HOST = process.env.PAYMENT_GRPC_HOST || 'payment-service:50052';
const KAFKA_BROKER = process.env.KAFKA_BROKER || 'kafka:9092';

// Cấu hình Database Pool
const dbConfig = {
    host: process.env.DB_HOST || 'booking-db',
    port: parseInt(process.env.DB_PORT || '3306'),
    user: process.env.DB_USER || 'booking_user',
    password: process.env.DB_PASSWORD || 'booking_secret',
    database: process.env.DB_NAME || 'booking_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

let pool;

// Khởi tạo Redis Client
const redis = new Redis({
    host: process.env.REDIS_HOST || 'redis',
    port: parseInt(process.env.REDIS_PORT || '6379'),
    lazyConnect: true,
    retryStrategy: (times) => Math.min(times * 500, 3000)
});

redis.connect().then(() => {
    console.log('[Booking Service] Connected to Redis successfully.');
}).catch(err => {
    console.error('[Booking Service] Redis connection error (will retry):', err.message);
});

// Khởi tạo Kafka Producer
const kafka = new Kafka({
    clientId: 'booking-service',
    brokers: [KAFKA_BROKER]
});
const producer = kafka.producer();
let kafkaConnected = false;

async function initKafka() {
    try {
        await producer.connect();
        kafkaConnected = true;
        console.log('[Booking Service] Kafka Producer connected successfully.');
    } catch (err) {
        console.error('[Booking Service] Kafka Producer connection error (will retry):', err.message);
        setTimeout(initKafka, 5000);
    }
}

// Khởi tạo Database Schema
async function initDB() {
    try {
        pool = mysql.createPool(dbConfig);
        await pool.query(`
            CREATE TABLE IF NOT EXISTS bookings (
                id VARCHAR(36) PRIMARY KEY,
                user_id INT NOT NULL,
                user_email VARCHAR(255) NOT NULL,
                showtime_id INT NOT NULL,
                seat_code VARCHAR(20) NOT NULL,
                price INT NOT NULL,
                movie_title VARCHAR(255),
                theater_name VARCHAR(255),
                room VARCHAR(100),
                status ENUM('PENDING', 'CONFIRMED', 'CANCELLED') DEFAULT 'PENDING',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_show_seat (showtime_id, seat_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        `);

        // Đảm bảo có đầy đủ các cột mở rộng
        await pool.query(`
            ALTER TABLE bookings 
            ADD COLUMN IF NOT EXISTS movie_title VARCHAR(255), 
            ADD COLUMN IF NOT EXISTS theater_name VARCHAR(255), 
            ADD COLUMN IF NOT EXISTS room VARCHAR(100);
        `).catch(() => {});

        await pool.query(`
            CREATE TABLE IF NOT EXISTS outbox (
                id VARCHAR(36) PRIMARY KEY,
                aggregate_type VARCHAR(50) NOT NULL,
                aggregate_id VARCHAR(36) NOT NULL,
                event_type VARCHAR(100) NOT NULL,
                payload JSON NOT NULL,
                status ENUM('PENDING', 'PROCESSED', 'FAILED') DEFAULT 'PENDING',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        `);

        console.log('[Booking Service] Database connected, bookings & outbox tables initialized.');
    } catch (err) {
        console.error('[Booking Service] DB Init Error (will retry):', err.message);
        setTimeout(initDB, 5000);
    }
}

// =========================================================================
// KHỞI TẠO gRPC CLIENTS
// =========================================================================
let showClient;
let paymentClient;

function initGrpcClients() {
    let showProtoPath = path.join(__dirname, 'proto', 'show.proto');
    if (!fs.existsSync(showProtoPath)) showProtoPath = path.join(__dirname, '..', '..', 'proto', 'show.proto');

    if (fs.existsSync(showProtoPath)) {
        const showPackageDef = protoLoader.loadSync(showProtoPath, { keepCase: true, longs: String, enums: String, defaults: true, oneofs: true });
        const showProto = grpc.loadPackageDefinition(showPackageDef).show;
        showClient = new showProto.ShowService(SHOW_GRPC_HOST, grpc.credentials.createInsecure());
        console.log(`[Booking Service] gRPC Show Client connected to ${SHOW_GRPC_HOST}`);
    }

    let paymentProtoPath = path.join(__dirname, 'proto', 'payment.proto');
    if (!fs.existsSync(paymentProtoPath)) paymentProtoPath = path.join(__dirname, '..', '..', 'proto', 'payment.proto');

    if (fs.existsSync(paymentProtoPath)) {
        const paymentPackageDef = protoLoader.loadSync(paymentProtoPath, { keepCase: true, longs: String, enums: String, defaults: true, oneofs: true });
        const paymentProto = grpc.loadPackageDefinition(paymentPackageDef).payment;
        paymentClient = new paymentProto.PaymentService(PAYMENT_GRPC_HOST, grpc.credentials.createInsecure());
        console.log(`[Booking Service] gRPC Payment Client connected to ${PAYMENT_GRPC_HOST}`);
    }
}

// Helper: Gọi gRPC Show CheckSeatAvailability bằng Promise
function checkSeatViaGrpc(showtime_id, seat_code) {
    return new Promise((resolve, reject) => {
        if (!showClient) return reject(new Error('Show gRPC Client chưa sẵn sàng'));
        showClient.CheckSeatAvailability({ showtime_id, seat_code }, (err, response) => {
            if (err) return reject(err);
            resolve(response);
        });
    });
}

// Helper: Gọi gRPC Payment ProcessPayment bằng Promise
function processPaymentViaGrpc(booking_id, user_id, amount, payment_method) {
    return new Promise((resolve, reject) => {
        if (!paymentClient) return reject(new Error('Payment gRPC Client chưa sẵn sàng'));
        paymentClient.ProcessPayment({ booking_id, user_id, amount, payment_method }, (err, response) => {
            if (err) return reject(err);
            resolve(response);
        });
    });
}

// =========================================================================
// CẤU HÌNH CIRCUIT BREAKER (TÍNH CHỊU LỖI - FAULT TOLERANCE)
// =========================================================================
const breakerOptions = {
    timeout: 4000,                  // 4s timeout nếu gRPC server không phản hồi
    errorThresholdPercentage: 50,  // Ngưỡng 50% request lỗi để kích hoạt ngắt mạch (OPEN)
    resetTimeout: 10000             // Sau 10s chuyển sang HALF_OPEN để thăm dò lại
};

// 1. Circuit Breaker cho Show Service
const showBreaker = new CircuitBreaker(checkSeatViaGrpc, breakerOptions);
showBreaker.fallback(() => ({
    available: false,
    message: '⚠️ Show Service đang quá tải hoặc gặp sự cố tạm thời (Circuit Breaker OPEN)'
}));

// 2. Circuit Breaker cho Payment Service
const paymentBreaker = new CircuitBreaker(processPaymentViaGrpc, breakerOptions);
paymentBreaker.fallback(() => ({
    success: false,
    message: '⚠️ Cổng thanh toán tạm thời không thể kết nối (Circuit Breaker OPEN)'
}));

showBreaker.on('open', () => console.warn('[Circuit Breaker] SHOW SERVICE CIRCUIT OPENED!'));
showBreaker.on('close', () => console.log('[Circuit Breaker] SHOW SERVICE CIRCUIT CLOSED (NORMAL)'));
paymentBreaker.on('open', () => console.warn('[Circuit Breaker] PAYMENT SERVICE CIRCUIT OPENED!'));
paymentBreaker.on('close', () => console.log('[Circuit Breaker] PAYMENT SERVICE CIRCUIT CLOSED (NORMAL)'));

// =========================================================================
// REST API
// =========================================================================

// Health check kèm trạng thái Circuit Breaker
app.get('/health', async (req, res) => {
    let dbStatus = 'DISCONNECTED';
    let redisStatus = 'DISCONNECTED';

    try {
        if (pool) {
            await pool.query('SELECT 1');
            dbStatus = 'CONNECTED';
        }
    } catch (err) {
        dbStatus = 'ERROR: ' + err.message;
    }

    try {
        const ping = await redis.ping();
        if (ping === 'PONG') redisStatus = 'CONNECTED';
    } catch (err) {
        redisStatus = 'ERROR: ' + err.message;
    }

    res.json({
        service: 'Booking Service',
        status: 'UP',
        database: dbStatus,
        redis: redisStatus,
        kafka_producer: kafkaConnected ? 'CONNECTED' : 'DISCONNECTED',
        circuit_breakers: {
            show_service: {
                state: showBreaker.opened ? 'OPEN' : (showBreaker.halfOpen ? 'HALF_OPEN' : 'CLOSED'),
                stats: showBreaker.stats
            },
            payment_service: {
                state: paymentBreaker.opened ? 'OPEN' : (paymentBreaker.halfOpen ? 'HALF_OPEN' : 'CLOSED'),
                stats: paymentBreaker.stats
            }
        },
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// Đặt vé / Giữ chỗ
app.post('/book', async (req, res) => {
    const { showtime_id, seat_code, user_id, user_email, payment_method } = req.body;

    if (!showtime_id || !seat_code || !user_email) {
        return res.status(400).json({ error: 'Thiếu thông tin bắt buộc: showtime_id, seat_code, user_email' });
    }

    const cleanSeat = seat_code.trim().toUpperCase();
    const lockKey = `lock:show:${showtime_id}:seat:${cleanSeat}`;
    const currentUserId = user_id || 1;
    let lockAcquired = false;

    try {
        // 1. REDIS DISTRIBUTED LOCK (SETNX 300s)
        const lockResult = await redis.set(lockKey, currentUserId, 'NX', 'EX', 300);
        if (!lockResult) {
            return res.status(409).json({
                error: 'Ghế đang được người khác giữ chỗ hoặc đang trong tiến trình thanh toán!',
                seat: cleanSeat,
                code: 'SEAT_LOCKED'
            });
        }
        lockAcquired = true;

        // 2. GỌI gRPC SHOW SERVICE THÔNG QUA CIRCUIT BREAKER
        const seatCheck = await showBreaker.fire(showtime_id, cleanSeat);
        if (!seatCheck.available) {
            await redis.del(lockKey);
            return res.status(400).json({
                error: seatCheck.message || 'Ghế không hợp lệ hoặc suất chiếu không tồn tại',
                code: 'SEAT_INVALID'
            });
        }

        // 3. KIỂM TRA TRÙNG LẶP TRONG BOOKING DB
        const [existing] = await pool.query(
            'SELECT id FROM bookings WHERE showtime_id = ? AND seat_code = ? AND status = "CONFIRMED"',
            [showtime_id, cleanSeat]
        );
        if (existing.length > 0) {
            await redis.del(lockKey);
            return res.status(409).json({ error: 'Ghế này đã được bán thành công cho người khác!', code: 'SEAT_ALREADY_SOLD' });
        }

        // 4. GỌI gRPC PAYMENT SERVICE THÔNG QUA CIRCUIT BREAKER
        const bookingId = uuidv4();
        const price = seatCheck.price || 85000;
        const paymentResult = await paymentBreaker.fire(bookingId, currentUserId, price, payment_method || 'VNPAY_MOCK');

        if (!paymentResult.success) {
            await redis.del(lockKey);
            return res.status(400).json({ error: 'Thanh toán không thành công: ' + paymentResult.message, code: 'PAYMENT_FAILED' });
        }

        // 5. TRANSACTIONAL OUTBOX & LƯU BOOKING
        const connection = await pool.getConnection();
        try {
            await connection.beginTransaction();

            await connection.query(
                `INSERT INTO bookings (id, user_id, user_email, showtime_id, seat_code, price, movie_title, theater_name, room, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "CONFIRMED")
                 ON DUPLICATE KEY UPDATE status="CONFIRMED", price=VALUES(price)`,
                [bookingId, currentUserId, user_email, showtime_id, cleanSeat, price, seatCheck.movie_title, seatCheck.theater_name, seatCheck.room]
            );

            const outboxId = uuidv4();
            const eventPayload = {
                booking_id: bookingId,
                user_id: currentUserId,
                user_email,
                showtime_id,
                seat_code: cleanSeat,
                price,
                movie_title: seatCheck.movie_title,
                theater_name: seatCheck.theater_name,
                room: seatCheck.room,
                transaction_id: paymentResult.transaction_id,
                timestamp: new Date().toISOString()
            };

            await connection.query(
                'INSERT INTO outbox (id, aggregate_type, aggregate_id, event_type, payload, status) VALUES (?, "BOOKING", ?, "BOOKING_CONFIRMED", ?, "PENDING")',
                [outboxId, bookingId, JSON.stringify(eventPayload)]
            );

            await connection.commit();

            // 6. BẮN SỰ KIỆN KAFKA
            if (kafkaConnected) {
                try {
                    await producer.send({
                        topic: 'booking-events',
                        messages: [{ key: bookingId, value: JSON.stringify(eventPayload) }]
                    });
                    await pool.query('UPDATE outbox SET status = "PROCESSED" WHERE id = ?', [outboxId]);
                    console.log(`[Booking Service] Published event to Kafka for booking: ${bookingId}`);
                } catch (kafkaErr) {
                    console.error('[Booking Service] Kafka publish error (outbox will retry):', kafkaErr.message);
                }
            }

            await redis.set(lockKey, 'SOLD', 'EX', 86400);

            res.status(201).json({
                message: '🎟️ Đặt vé và thanh toán thành công!',
                booking: {
                    id: bookingId,
                    movie_title: seatCheck.movie_title,
                    theater_name: seatCheck.theater_name,
                    room: seatCheck.room,
                    seat_code: cleanSeat,
                    price,
                    status: 'CONFIRMED',
                    transaction_id: paymentResult.transaction_id
                }
            });
        } catch (dbErr) {
            await connection.rollback();
            throw dbErr;
        } finally {
            connection.release();
        }

    } catch (err) {
        if (lockAcquired) {
            await redis.del(lockKey).catch(() => {});
        }
        res.status(500).json({ error: err.message });
    }
});

app.get('/my-bookings', async (req, res) => {
    try {
        const userId = req.headers['x-user-id'] || 1;
        const [rows] = await pool.query('SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC', [userId]);
        res.json({ success: true, count: rows.length, data: rows });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.get('/:id', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM bookings WHERE id = ?', [req.params.id]);
        if (rows.length === 0) return res.status(404).json({ error: 'Không tìm thấy vé' });
        res.json({ success: true, booking: rows[0] });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.delete('/:id', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT * FROM bookings WHERE id = ?', [req.params.id]);
        if (rows.length === 0) return res.status(404).json({ error: 'Không tìm thấy vé' });

        const booking = rows[0];
        await pool.query('UPDATE bookings SET status = "CANCELLED" WHERE id = ?', [req.params.id]);
        
        const lockKey = `lock:show:${booking.showtime_id}:seat:${booking.seat_code}`;
        await redis.del(lockKey);

        res.json({ message: 'Hủy vé thành công, ghế đã được trả lại', booking_id: req.params.id });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`[Booking Service] Running on port ${PORT}`);
    initDB();
    initGrpcClients();
    initKafka();
});
