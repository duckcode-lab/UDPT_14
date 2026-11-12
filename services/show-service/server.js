const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const grpc = require('@grpc/grpc-js');
const protoLoader = require('@grpc/proto-loader');
const path = require('path');
const fs = require('fs');

const app = express();
app.use(cors());
app.use(express.json());

const HTTP_PORT = process.env.PORT || 8002;
const GRPC_PORT = process.env.GRPC_PORT || 50051;

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
        
        // 1. Bảng Phim
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

        // 2. Bảng Suất chiếu
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

        // 3. Seed dữ liệu mẫu nếu bảng rỗng
        const [existingMovies] = await pool.query('SELECT COUNT(*) as count FROM movies');
        if (existingMovies[0].count === 0) {
            await pool.query(`
                INSERT INTO movies (title, description, duration) VALUES 
                ('Avengers: Secret Wars', 'Bom tấn siêu anh hùng đa vũ trụ đỉnh cao', 150),
                ('Dune: Phần Hai', 'Hành trình báo thù trên hành tinh cát Arrakis', 166),
                ('Mai', 'Tác phẩm điện ảnh tâm lý xã hội sâu sắc', 131);
            `);

            await pool.query(`
                INSERT INTO showtimes (movie_id, theater_name, room, time, price) VALUES 
                (1, 'CGV Vincom', 'Phòng 01 (IMAX)', NOW() + INTERVAL 2 HOUR, 120000),
                (1, 'CGV Vincom', 'Phòng 02 (2D)', NOW() + INTERVAL 4 HOUR, 90000),
                (2, 'Lotte Cinema', 'Phòng 05 (2D)', NOW() + INTERVAL 3 HOUR, 85000);
            `);
            console.log('[Show Service] Seeded movies and showtimes data.');
        }

        console.log('[Show Service] Database connected and schema ready.');
    } catch (err) {
        console.error('[Show Service] DB Init Error (will retry):', err.message);
        setTimeout(initDB, 5000);
    }
}

// =========================================================================
// KHỞI TẠO gRPC SERVER (Port 50051)
// Cung cấp RPC CheckSeatAvailability và GetShowDetails cho Booking Service
// =========================================================================
function startGrpcServer() {
    let protoPath = path.join(__dirname, 'proto', 'show.proto');
    if (!fs.existsSync(protoPath)) {
        protoPath = path.join(__dirname, '..', '..', 'proto', 'show.proto');
    }

    if (!fs.existsSync(protoPath)) {
        console.error('[Show Service] Không tìm thấy file show.proto tại:', protoPath);
        return;
    }

    const packageDefinition = protoLoader.loadSync(protoPath, {
        keepCase: true,
        longs: String,
        enums: String,
        defaults: true,
        oneofs: true
    });

    const showProto = grpc.loadPackageDefinition(packageDefinition).show;
    const server = new grpc.Server();

    server.addService(showProto.ShowService.service, {
        // RPC: Kiểm tra tính khả dụng của ghế
        CheckSeatAvailability: async (call, callback) => {
            const { showtime_id, seat_code } = call.request;
            try {
                const [rows] = await pool.query(
                    'SELECT s.*, m.title as movie_title FROM showtimes s JOIN movies m ON s.movie_id = m.id WHERE s.id = ?',
                    [showtime_id]
                );

                if (rows.length === 0) {
                    return callback(null, {
                        available: false,
                        showtime_id,
                        seat_code,
                        price: 0,
                        message: 'Suất chiếu không tồn tại'
                    });
                }

                const show = rows[0];
                // Kiểm tra định dạng ghế hợp lệ (A1 - E8)
                const validRows = ['A', 'B', 'C', 'D', 'E'];
                const row = seat_code.charAt(0).toUpperCase();
                const col = parseInt(seat_code.slice(1));

                if (!validRows.includes(row) || isNaN(col) || col < 1 || col > 8) {
                    return callback(null, {
                        available: false,
                        showtime_id,
                        seat_code,
                        price: 0,
                        message: `Mã ghế ${seat_code} không hợp lệ trong sơ đồ rạp (hợp lệ: A1 - E8)`
                    });
                }

                const price = row === 'E' ? show.price + 15000 : show.price;

                callback(null, {
                    available: true,
                    showtime_id: show.id,
                    seat_code: seat_code.toUpperCase(),
                    price,
                    movie_title: show.movie_title,
                    theater_name: show.theater_name,
                    room: show.room,
                    show_time: new Date(show.time).toISOString(),
                    message: 'Ghế hợp lệ và sẵn sàng đặt'
                });
            } catch (err) {
                callback(err);
            }
        },

        // RPC: Lấy thông tin chi tiết suất chiếu
        GetShowDetails: async (call, callback) => {
            const { showtime_id } = call.request;
            try {
                const [rows] = await pool.query(
                    'SELECT s.*, m.title as movie_title FROM showtimes s JOIN movies m ON s.movie_id = m.id WHERE s.id = ?',
                    [showtime_id]
                );

                if (rows.length === 0) {
                    return callback({ code: grpc.status.NOT_FOUND, details: 'Suất chiếu không tồn tại' });
                }

                const show = rows[0];
                callback(null, {
                    id: show.id,
                    movie_title: show.movie_title,
                    theater_name: show.theater_name,
                    room: show.room,
                    show_time: new Date(show.time).toISOString(),
                    price: show.price
                });
            } catch (err) {
                callback(err);
            }
        }
    });

    server.bindAsync(`0.0.0.0:${GRPC_PORT}`, grpc.ServerCredentials.createInsecure(), (err, port) => {
        if (err) {
            console.error('[Show Service] Lỗi khởi động gRPC Server:', err.message);
            return;
        }
        console.log(`[Show Service] gRPC Server running on port ${port}`);
    });
}

// =========================================================================
// REST API (Port 8002)
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
        service: 'Show Service',
        status: 'UP',
        database: dbStatus,
        http_port: HTTP_PORT,
        grpc_port: GRPC_PORT,
        timestamp: new Date().toISOString()
    });
});

// Danh sách phim
app.get('/movies', async (req, res) => {
    try {
        const [movies] = await pool.query('SELECT * FROM movies ORDER BY id DESC');
        res.json({ success: true, data: movies });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Thêm phim mới (Admin)
app.post('/movies', async (req, res) => {
    try {
        const { title, description, duration, trailer, photo } = req.body;
        if (!title || !duration) {
            return res.status(400).json({ error: 'Vui lòng cung cấp title và duration' });
        }
        const [result] = await pool.query(
            'INSERT INTO movies (title, description, duration, trailer, photo) VALUES (?, ?, ?, ?, ?)',
            [title, description || '', duration, trailer || null, photo || null]
        );
        res.status(201).json({ message: 'Tạo phim thành công', movie_id: result.insertId });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Chi tiết phim
app.get('/movies/:id', async (req, res) => {
    try {
        const [movies] = await pool.query('SELECT * FROM movies WHERE id = ?', [req.params.id]);
        if (movies.length === 0) return res.status(404).json({ error: 'Không tìm thấy phim' });
        res.json({ success: true, data: movies[0] });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Danh sách suất chiếu
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
        res.json({ success: true, count: showtimes.length, data: showtimes });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Tạo suất chiếu mới (Admin)
app.post('/showtimes', async (req, res) => {
    try {
        const { movie_id, theater_name, room, time, price } = req.body;
        if (!movie_id || !theater_name || !room || !time) {
            return res.status(400).json({ error: 'Thiếu thông tin bắt buộc: movie_id, theater_name, room, time' });
        }

        const [result] = await pool.query(
            'INSERT INTO showtimes (movie_id, theater_name, room, time, price) VALUES (?, ?, ?, ?, ?)',
            [movie_id, theater_name, room, time, price || 85000]
        );
        res.status(201).json({ message: 'Tạo suất chiếu thành công', showtime_id: result.insertId });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Sơ đồ ghế ngồi theo suất chiếu (Ma trận 5 hàng x 8 cột = 40 ghế)
app.get('/showtimes/:id/seats', async (req, res) => {
    try {
        const [showtimes] = await pool.query(
            'SELECT s.*, m.title as movie_title FROM showtimes s JOIN movies m ON s.movie_id = m.id WHERE s.id = ?',
            [req.params.id]
        );
        if (showtimes.length === 0) return res.status(404).json({ error: 'Không tìm thấy suất chiếu' });

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
            total_seats: seats.length,
            seats
        });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

app.listen(HTTP_PORT, () => {
    console.log(`[Show Service] HTTP REST API running on port ${HTTP_PORT}`);
    initDB();
    startGrpcServer();
});
