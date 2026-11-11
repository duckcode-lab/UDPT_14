const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const Redis = require('ioredis');
const { v4: uuidv4 } = require('uuid');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 8003;

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

async function initDB() {
    try {
        pool = mysql.createPool(dbConfig);
        // Bảng bookings chính thức với UUID
        await pool.query(`
            CREATE TABLE IF NOT EXISTS bookings (
                id VARCHAR(36) PRIMARY KEY,
                user_id INT NOT NULL,
                user_email VARCHAR(255) NOT NULL,
                showtime_id INT NOT NULL,
                seat_code VARCHAR(20) NOT NULL,
                price INT NOT NULL,
                status ENUM('PENDING', 'CONFIRMED', 'CANCELLED') DEFAULT 'PENDING',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_show_seat (showtime_id, seat_code)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        `);

        // Bảng Transactional Outbox Pattern
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

// ------------------------------------
// Health Check (DB + Redis)
// ------------------------------------
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
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// ------------------------------------
// Danh sách vé của người dùng
// ------------------------------------
app.get('/my-bookings', async (req, res) => {
    try {
        const userId = req.headers['x-user-id'] || 1;
        const [rows] = await pool.query('SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC', [userId]);
        res.json({ success: true, data: rows });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`[Booking Service] Running on port ${PORT}`);
    initDB();
});
