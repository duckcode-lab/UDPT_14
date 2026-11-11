const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');

const app = express();
app.use(cors());
app.use(express.json());

const PORT = process.env.PORT || 8002;

// Cấu hình Database Pool
const dbConfig = {
    host: process.env.DB_HOST || 'show-db',
    port: parseInt(process.env.DB_PORT || '3306'),
    user: process.env.DB_USER || 'show_user',
    password: process.env.DB_PASSWORD || 'show_secret',
    database: process.env.DB_NAME || 'show_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
};

let pool;

async function initDB() {
    try {
        pool = mysql.createPool(dbConfig);
        // Schema Phim, Rạp, Suất chiếu, Ghế
        await pool.query(`
            CREATE TABLE IF NOT EXISTS movies (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                duration INT NOT NULL,
                trailer VARCHAR(255),
                photo VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        `);

        await pool.query(`
            CREATE TABLE IF NOT EXISTS showtimes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                movie_id INT NOT NULL,
                theater_name VARCHAR(255) NOT NULL,
                room VARCHAR(100) NOT NULL,
                time DATETIME NOT NULL,
                price INT NOT NULL DEFAULT 85000,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        `);

        // Chèn dữ liệu mẫu nếu chưa có
        const [existingMovies] = await pool.query('SELECT COUNT(*) as count FROM movies');
        if (existingMovies[0].count === 0) {
            await pool.query(`
                INSERT INTO movies (title, description, duration) VALUES 
                ('Avengers: Secret Wars', 'Bom tấn siêu anh hùng đa vũ trụ', 150),
                ('Dune: Phần Hai', 'Hành trình báo thù trên hành tinh cát', 166),
                ('Mai', 'Tác phẩm điện ảnh tâm lý xã hội', 131);
            `);

            await pool.query(`
                INSERT INTO showtimes (movie_id, theater_name, room, time, price) VALUES 
                (1, 'CGV Vincom', 'Phòng 01 (IMAX)', NOW() + INTERVAL 2 HOUR, 120000),
                (1, 'CGV Vincom', 'Phòng 02 (2D)', NOW() + INTERVAL 4 HOUR, 90000),
                (2, 'Lotte Cinema', 'Phòng 05 (2D)', NOW() + INTERVAL 3 HOUR, 85000);
            `);
            console.log('[Show Service] Seeded initial movies and showtimes data.');
        }

        console.log('[Show Service] Database connected and schema ready.');
    } catch (err) {
        console.error('[Show Service] DB Init Error (will retry):', err.message);
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
        service: 'Show Service',
        status: 'UP',
        database: dbStatus,
        port: PORT,
        timestamp: new Date().toISOString()
    });
});

// ------------------------------------
// Danh sách Phim & Chi tiết Phim
// ------------------------------------
app.get('/movies', async (req, res) => {
    try {
        const [movies] = await pool.query('SELECT * FROM movies ORDER BY id DESC');
        res.json({ success: true, data: movies });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.get('/movies/:id', async (req, res) => {
    try {
        const [movies] = await pool.query('SELECT * FROM movies WHERE id = ?', [req.params.id]);
        if (movies.length === 0) return res.status(404).json({ error: 'Không tìm thấy phim' });
        res.json({ success: true, data: movies[0] });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// ------------------------------------
// Danh sách Suất chiếu & Sơ đồ ghế
// ------------------------------------
app.get('/showtimes', async (req, res) => {
    try {
        const { movie_id } = req.query;
        let query = 'SELECT s.*, m.title as movie_title FROM showtimes s JOIN movies m ON s.movie_id = m.id';
        const params = [];
        if (movie_id) {
            query += ' WHERE s.movie_id = ?';
            params.push(movie_id);
        }
        query += ' ORDER BY s.time ASC';

        const [showtimes] = await pool.query(query, params);
        res.json({ success: true, data: showtimes });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.get('/showtimes/:id/seats', async (req, res) => {
    try {
        const [showtimes] = await pool.query('SELECT * FROM showtimes WHERE id = ?', [req.params.id]);
        if (showtimes.length === 0) return res.status(404).json({ error: 'Không tìm thấy suất chiếu' });

        // Tạo ma trận sơ đồ ghế chuẩn 5 hàng x 8 cột (A1-E8)
        const rows = ['A', 'B', 'C', 'D', 'E'];
        const seats = [];
        rows.forEach(r => {
            for (let i = 1; i <= 8; i++) {
                seats.push({
                    code: `${r}${i}`,
                    row: r,
                    col: i,
                    type: r === 'E' ? 'VIP' : 'STANDARD',
                    price: r === 'E' ? showtimes[0].price + 15000 : showtimes[0].price
                });
            }
        });

        res.json({
            success: true,
            showtime: showtimes[0],
            seats
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(PORT, () => {
    console.log(`[Show Service] Running on port ${PORT}`);
    initDB();
});
