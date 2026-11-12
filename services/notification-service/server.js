const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const { Kafka } = require('kafkajs');
const QRCode = require('qrcode');
const nodemailer = require('nodemailer');
const { v4: uuidv4 } = require('uuid');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 8005;
const KAFKA_BROKER = process.env.KAFKA_BROKER || 'kafka:9092';

// Cấu hình Database Pool
const dbConfig = {
    host: process.env.DB_HOST || 'notification-db',
    port: parseInt(process.env.DB_PORT || '3306'),
    user: process.env.DB_USER || 'notification_user',
    password: process.env.DB_PASSWORD || 'notification_secret',
    database: process.env.DB_NAME || 'notification_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

let pool;

async function initDB() {
    try {
        pool = mysql.createPool(dbConfig);
        await pool.query(`
            CREATE TABLE IF NOT EXISTS notification_logs (
                id VARCHAR(36) PRIMARY KEY,
                booking_id VARCHAR(36) NOT NULL,
                recipient_email VARCHAR(255) NOT NULL,
                ticket_code VARCHAR(100) NOT NULL,
                movie_title VARCHAR(255),
                theater_name VARCHAR(255),
                room VARCHAR(100),
                seat_code VARCHAR(20),
                price INT,
                qr_code_data LONGTEXT NOT NULL,
                status ENUM('SENT', 'FAILED') DEFAULT 'SENT',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        `);
        console.log('[Notification Service] Database connected, notification_logs table ready.');
    } catch (err) {
        console.error('[Notification Service] DB Init Error (will retry):', err.message);
        setTimeout(initDB, 5000);
    }
}

// =========================================================================
// KHỞI TẠO KAFKA CONSUMER (Lắng nghe topic: booking-events)
// =========================================================================
const kafka = new Kafka({
    clientId: 'notification-service',
    brokers: [KAFKA_BROKER]
});
const consumer = kafka.consumer({ groupId: 'notification-group' });
let kafkaConsumerConnected = false;

async function initKafkaConsumer() {
    try {
        await consumer.connect();
        await consumer.subscribe({ topic: 'booking-events', fromBeginning: false });
        kafkaConsumerConnected = true;
        console.log('[Notification Service] Kafka Consumer subscribed to "booking-events" topic.');

        await consumer.run({
            eachMessage: async ({ topic, partition, message }) => {
                try {
                    const rawValue = message.value.toString();
                    const event = JSON.parse(rawValue);
                    console.log(`[Notification Service] 📨 Nhận sự kiện từ Kafka [${topic}]:`, event.booking_id);

                    // 1. Tự động sinh mã QR Code cho vé
                    const qrDataUrl = await QRCode.toDataURL(
                        JSON.stringify({
                            ticket_id: event.booking_id,
                            movie: event.movie_title,
                            seat: event.seat_code,
                            theater: event.theater_name,
                            room: event.room,
                            email: event.user_email
                        }),
                        { errorCorrectionLevel: 'H', margin: 2, width: 300 }
                    );

                    // 2. Lưu log thông báo vào notification_db
                    const notiId = uuidv4();
                    await pool.query(
                        `INSERT INTO notification_logs 
                        (id, booking_id, recipient_email, ticket_code, movie_title, theater_name, room, seat_code, price, qr_code_data, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "SENT")`,
                        [
                            notiId,
                            event.booking_id,
                            event.user_email,
                            event.booking_id.substring(0, 8).toUpperCase(),
                            event.movie_title,
                            event.theater_name,
                            event.room,
                            event.seat_code,
                            event.price,
                            qrDataUrl
                        ]
                    );

                    // 3. Giả lập gửi Email vé
                    console.log(`[Notification Service] 📧 Đã gửi email xác nhận và vé QR Code tới: ${event.user_email} cho phim "${event.movie_title}", ghế ${event.seat_code}!`);

                } catch (msgErr) {
                    console.error('[Notification Service] Lỗi xử lý message Kafka:', msgErr.message);
                }
            }
        });
    } catch (err) {
        console.error('[Notification Service] Kafka Consumer connection error (will retry):', err.message);
        setTimeout(initKafkaConsumer, 5000);
    }
}

// =========================================================================
// REST API (Port 8005)
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
        service: 'Notification Service',
        status: 'UP',
        database: dbStatus,
        kafka_consumer: kafkaConsumerConnected ? 'CONNECTED' : 'DISCONNECTED',
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// Sinh mã QR Code theo yêu cầu
app.get('/tickets/:code/qr', async (req, res) => {
    try {
        const ticketCode = req.params.code;
        const qrDataUrl = await QRCode.toDataURL(ticketCode, {
            errorCorrectionLevel: 'H',
            margin: 2,
            width: 300
        });

        res.json({
            ticket_code: ticketCode,
            qr_image_base64: qrDataUrl
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Lịch sử gửi thông báo
app.get('/logs', async (req, res) => {
    try {
        const [rows] = await pool.query('SELECT id, booking_id, recipient_email, ticket_code, movie_title, seat_code, status, created_at FROM notification_logs ORDER BY created_at DESC LIMIT 50');
        res.json({ success: true, count: rows.length, data: rows });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`[Notification Service] Running on port ${PORT}`);
    initDB();
    initKafkaConsumer();
});
