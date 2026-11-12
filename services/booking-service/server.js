const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const Redis = require('ioredis');
const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');
const { Kafka } = require('kafkajs');
const CircuitBreaker = require('opossum');
const { v4: uuidv4 } = require('uuid');
const crypto = require('crypto');
const path = require('path');
const fs = require('fs');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 8003;
const JWT_SECRET = process.env.JWT_SECRET || 'super_secret_jwt_key_cinema_distributed';
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

// Helper: Giải mã và xác thực JWT Bearer Token bằng crypto native (HS256)
function verifyToken(req) {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    if (!token) return null;
    try {
        const parts = token.split('.');
        if (parts.length !== 3) return null;
        const [headerB64, payloadB64, sigB64] = parts;
        const expectedSig = crypto.createHmac('sha256', JWT_SECRET)
            .update(`${headerB64}.${payloadB64}`)
            .digest('base64url');
        if (expectedSig !== sigB64) return null;
        const payload = JSON.parse(Buffer.from(payloadB64, 'base64url').toString('utf8'));
        if (payload.exp && Date.now() >= payload.exp * 1000) return null;
        return payload;
    } catch (e) {
        return null;
    }
}

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

// Lấy danh sách ghế đã bán hoặc đang bị khóa theo suất chiếu
app.get('/showtimes/:id/occupied-seats', async (req, res) => {
    try {
        const showtimeId = req.params.id;
        // 1. Ghế đã bán thành công trong DB
        const [rows] = await pool.query(
            'SELECT seat_code FROM bookings WHERE showtime_id = ? AND status = "CONFIRMED"',
            [showtimeId]
        );
        const bookedSeats = rows.map(r => r.seat_code.toUpperCase());

        // 2. Ghế đang có lock trong Redis
        const lockPattern = `lock:show:${showtimeId}:seat:*`;
        const lockKeys = await redis.keys(lockPattern);
        const lockedSeats = lockKeys.map(k => k.split(':').pop().toUpperCase());

        const occupied = Array.from(new Set([...bookedSeats, ...lockedSeats]));

        res.json({
            success: true,
            showtime_id: parseInt(showtimeId),
            count: occupied.length,
            occupied_seats: occupied,
            booked_seats: bookedSeats,
            locked_seats: lockedSeats
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Đặt vé / Giữ chỗ (Hỗ trợ JWT Authentication & Idempotency-Key)
app.post('/book', async (req, res) => {
    const userPayload = verifyToken(req);
    const { showtime_id, seat_code, payment_method } = req.body;

    const currentUserId = userPayload ? userPayload.id : (req.body.user_id || 1);
    const currentUserEmail = userPayload ? userPayload.email : (req.body.user_email || 'khachhang@gmail.com');

    if (!showtime_id || !seat_code || !currentUserEmail) {
        return res.status(400).json({ error: 'Thiếu thông tin bắt buộc: showtime_id, seat_code, user_email' });
    }

    // 0. KIỂM TRA IDEMPOTENCY KEY (Chống trùng lặp giao dịch khi retry)
    const idempotencyKey = req.headers['idempotency-key'];
    if (idempotencyKey) {
        try {
            const cachedRes = await redis.get(`idempotency:${idempotencyKey}`);
            if (cachedRes) {
                console.log(`[Booking Service] Idempotency Cache HIT cho key: ${idempotencyKey}`);
                return res.status(200).json(JSON.parse(cachedRes));
            }
        } catch (e) {}
    }

    const cleanSeat = seat_code.trim().toUpperCase();
    const lockKey = `lock:show:${showtime_id}:seat:${cleanSeat}`;
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
                [bookingId, currentUserId, currentUserEmail, showtime_id, cleanSeat, price, seatCheck.movie_title, seatCheck.theater_name, seatCheck.room]
            );

            const outboxId = uuidv4();
            const eventPayload = {
                booking_id: bookingId,
                user_id: currentUserId,
                user_email: currentUserEmail,
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

            const responseData = {
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
            };

            // Lưu cache Idempotency Key 24h
            if (idempotencyKey) {
                try {
                    await redis.set(`idempotency:${idempotencyKey}`, JSON.stringify(responseData), 'EX', 86400);
                } catch (e) {}
            }

            res.status(201).json(responseData);
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

// Tra cứu danh sách vé của người dùng (Xác thực JWT hoặc Header)
app.get('/my-bookings', async (req, res) => {
    try {
        const userPayload = verifyToken(req);
        const userId = userPayload ? userPayload.id : (req.headers['x-user-id'] || req.query.user_id || 1);
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

// Hủy vé và giải phóng ghế trên Redis
app.delete('/:id', async (req, res) => {
    try {
        const userPayload = verifyToken(req);
        let query = 'SELECT * FROM bookings WHERE id = ?';
        let params = [req.params.id];
        if (userPayload && userPayload.role !== 'admin') {
            query += ' AND user_id = ?';
            params.push(userPayload.id);
        }

        const [rows] = await pool.query(query, params);
        if (rows.length === 0) return res.status(404).json({ error: 'Không tìm thấy vé hoặc bạn không có quyền hủy vé này' });

        const booking = rows[0];
        await pool.query('UPDATE bookings SET status = "CANCELLED" WHERE id = ?', [req.params.id]);
        
        const lockKey = `lock:show:${booking.showtime_id}:seat:${booking.seat_code}`;
        await redis.del(lockKey);

        res.json({ message: 'Hủy vé thành công, ghế đã được trả lại sơ đồ!', booking_id: req.params.id, seat_code: booking.seat_code });
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
