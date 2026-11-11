const express = require('express');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcryptjs');
const mysql = require('mysql2/promise');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 8001;
const JWT_SECRET = process.env.JWT_SECRET || 'super_secret_jwt_key_cinema_distributed';

// Cấu hình Database Pool
const dbConfig = {
    host: process.env.DB_HOST || 'auth-db',
    port: parseInt(process.env.DB_PORT || '3306'),
    user: process.env.DB_USER || 'auth_user',
    password: process.env.DB_PASSWORD || 'auth_secret',
    database: process.env.DB_NAME || 'auth_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

let pool;

async function initDB() {
    try {
        pool = mysql.createPool(dbConfig);
        // Tạo bảng users nếu chưa có
        await pool.query(`
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                role ENUM('user', 'admin') DEFAULT 'user',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        `);
        console.log('[Auth Service] Database connected and schema initialized.');
    } catch (err) {
        console.error('[Auth Service] DB Init Error (will retry):', err.message);
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
        service: 'Auth Service',
        status: 'UP',
        database: dbStatus,
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// ------------------------------------
// Đăng ký
// ------------------------------------
app.post('/register', async (req, res) => {
    try {
        const { name, email, password } = req.body;
        if (!name || !email || !password) {
            return res.status(400).json({ error: 'Vui lòng cung cấp đầy đủ: name, email, password' });
        }

        const [existing] = await pool.query('SELECT id FROM users WHERE email = ?', [email]);
        if (existing.length > 0) {
            return res.status(409).json({ error: 'Email đã tồn tại trong hệ thống' });
        }

        const hashedPassword = await bcrypt.hash(password, 10);
        const [result] = await pool.query(
            'INSERT INTO users (name, email, password) VALUES (?, ?, ?)',
            [name, email, hashedPassword]
        );

        const token = jwt.sign(
            { id: result.insertId, email, name, role: 'user' },
            JWT_SECRET,
            { expiresIn: '24h' }
        );

        res.status(201).json({
            message: 'Đăng ký tài khoản thành công',
            user: { id: result.insertId, name, email, role: 'user' },
            token
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// ------------------------------------
// Đăng nhập (Cấp phát JWT)
// ------------------------------------
app.post('/login', async (req, res) => {
    try {
        const { email, password } = req.body;
        if (!email || !password) {
            return res.status(400).json({ error: 'Vui lòng nhập email và password' });
        }

        const [users] = await pool.query('SELECT * FROM users WHERE email = ?', [email]);
        if (users.length === 0) {
            return res.status(401).json({ error: 'Tài khoản hoặc mật khẩu không chính xác' });
        }

        const user = users[0];
        const isMatch = await bcrypt.compare(password, user.password);
        if (!isMatch) {
            return res.status(401).json({ error: 'Tài khoản hoặc mật khẩu không chính xác' });
        }

        const token = jwt.sign(
            { id: user.id, email: user.email, name: user.name, role: user.role },
            JWT_SECRET,
            { expiresIn: '24h' }
        );

        res.json({
            message: 'Đăng nhập thành công',
            token,
            user: { id: user.id, name: user.name, email: user.email, role: user.role }
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// ------------------------------------
// Xác thực JWT Token (Introspection)
// ------------------------------------
app.get('/verify', (req, res) => {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
        return res.status(401).json({ valid: false, error: 'Thiếu Authorization Bearer token' });
    }

    try {
        const decoded = jwt.verify(token, JWT_SECRET);
        res.json({ valid: true, user: decoded });
    } catch (err) {
        res.status(403).json({ valid: false, error: 'Token không hợp lệ hoặc đã hết hạn' });
    }
});

app.listen(PORT, () => {
    console.log(`[Auth Service] Running on port ${PORT}`);
    initDB();
});
