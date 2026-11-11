const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const QRCode = require('qrcode');
const { v4: uuidv4 } = require('uuid');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 8005;

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
                recipient_email VARCHAR(255) NOT NULL,
                ticket_code VARCHAR(100) NOT NULL,
                qr_code_data TEXT NOT NULL,
                status ENUM('SENT', 'FAILED') DEFAULT 'SENT',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        `);
        console.log('[Notification Service] Database connected, notification_logs table initialized.');
    } catch (err) {
        console.error('[Notification Service] DB Init Error (will retry):', err.message);
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
        service: 'Notification Service',
        status: 'UP',
        database: dbStatus,
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// ------------------------------------
// Sinh mã QR Code cho vé
// ------------------------------------
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

app.listen(PORT, () => {
    console.log(`[Notification Service] Running on port ${PORT}`);
    initDB();
});
