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
        
        // 1. Bảng users với role RBAC
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

        // 2. Tạo tài khoản mẫu mặc định nếu chưa có (1 Admin, 1 User)
        const [users] = await pool.query('SELECT COUNT(*) as count FROM users');
        if (users[0].count === 0) {
            const adminPass = await bcrypt.hash('Admin@123456', 10);
            const userPass = await bcrypt.hash('User@123456', 10);

            await pool.query(`
                INSERT INTO users (name, email, password, role) VALUES 
                ('Hệ thống Quản trị viên', 'admin@cinema.com', ?, 'admin'),
                ('Khách hàng Thử nghiệm', 'user@cinema.com', ?, 'user');
            `, [adminPass, userPass]);
            console.log('[Auth Service] Seeded default admin (admin@cinema.com) and user (user@cinema.com)');
        }

        console.log('[Auth Service] Database connected and RBAC schema initialized.');
    } catch (err) {
        console.error('[Auth Service] DB Init Error (will retry):', err.message);
        setTimeout(initDB, 5000);
    }
}

// ------------------------------------
// Middlewares Xác thực & Phân quyền (RBAC)
// ------------------------------------
function authenticateToken(req, res, next) {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];

    if (!token) {
        return res.status(401).json({ error: 'Truy cập bị từ chối: Thiếu JWT Bearer token' });
    }

    try {
        const decoded = jwt.verify(token, JWT_SECRET);
        req.user = decoded;
        next();
    } catch (err) {
        return res.status(403).json({ error: 'Token không hợp lệ hoặc đã hết hạn' });
    }
}

function requireRole(allowedRoles) {
    return (req, res, next) => {
        if (!req.user || !allowedRoles.includes(req.user.role)) {
            return res.status(403).json({ 
                error: `Không có quyền truy cập: Yêu cầu vai trò [${allowedRoles.join(', ')}]`,
                your_role: req.user ? req.user.role : 'none'
            });
        }
        next();
    };
}

// ------------------------------------
// 1. Health Check
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
// 2. Đăng ký tài khoản
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
            'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "user")',
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
// 3. Đăng nhập (Cấp phát JWT Token)
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
// 4. Xác thực Token (Introspection API)
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

// ------------------------------------
// 5. Lấy Profile của User hiện tại (Yêu cầu JWT)
// ------------------------------------
app.get('/profile', authenticateToken, async (req, res) => {
    try {
        const [users] = await pool.query('SELECT id, name, email, role, created_at FROM users WHERE id = ?', [req.user.id]);
        if (users.length === 0) return res.status(404).json({ error: 'Không tìm thấy người dùng' });
        res.json({ success: true, profile: users[0] });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// ------------------------------------
// 6. Quản trị: Xem danh sách người dùng (Chỉ Admin)
// ------------------------------------
app.get('/users', authenticateToken, requireRole(['admin']), async (req, res) => {
    try {
        const [users] = await pool.query('SELECT id, name, email, role, created_at FROM users ORDER BY id DESC');
        res.json({ success: true, count: users.length, users });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// ------------------------------------
// 7. Quản trị: Cập nhật phân quyền Role (Chỉ Admin)
// ------------------------------------
app.patch('/users/:id/role', authenticateToken, requireRole(['admin']), async (req, res) => {
    try {
        const { role } = req.body;
        if (!['user', 'admin'].includes(role)) {
            return res.status(400).json({ error: 'Vai trò không hợp lệ (chỉ chấp nhận "user" hoặc "admin")' });
        }

        await pool.query('UPDATE users SET role = ? WHERE id = ?', [role, req.params.id]);
        res.json({ message: `Cập nhật quyền thành công cho người dùng #${req.params.id} sang vai trò: ${role}` });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`[Auth Service] Running on port ${PORT}`);
    initDB();
});
