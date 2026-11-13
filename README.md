# UDPT Cinema

Website đặt vé xem phim xây dựng bằng Laravel 10 và MySQL, phục vụ cho môn học Ứng dụng Phân tán (UDPT).

## Mục tiêu dự án

Dự án này là hệ thống đặt vé rạp chiếu phim cho người dùng và quản trị viên, giúp xử lý các chức năng chính của một ứng dụng bán vé trực tuyến như:

- Xem danh sách phim, lọc và tìm kiếm phim
- Xem chi tiết phim cùng suất chiếu
- Đặt vé cho khách hàng
- Quản lý lịch chiếu, phòng chiếu và ghế ngồi
- Quản lý banner, người dùng và đơn đặt vé
- Giao diện quản trị cho admin

## Công nghệ sử dụng

- PHP 8.1
- Laravel 10.x
- MySQL
- Tailwind CSS
- Vite
- Sanctum
- Spatie Permission

## Cấu trúc dự án chính

- `app/Models` : model như `Movie`, `Showtime`, `Booking`, `Ticket`, `Theater`, `Room`, `User`
- `app/Http/Controllers` : controller xử lý nghiệp vụ người dùng và admin
- `routes/web.php` : định nghĩa route chính
- `resources/views` : giao diện Blade
- `database/migrations` : schema dữ liệu
- `database/seeders` : dữ liệu mẫu

## Tính năng chính

### Người dùng

- Trang chủ hiển thị phim và banner
- Tìm kiếm phim theo tên/keyword
- Xem chi tiết phim
- Đặt vé theo suất chiếu và ghế
- Xem lịch sử đặt vé
- Cập nhật hồ sơ cá nhân
- Xác thực OTP khi đăng ký

### Quản trị viên

- Quản lý phim
- Quản lý suất chiếu
- Quản lý banner
- Quản lý người dùng
- Xem và xóa đơn đặt vé
- Dashboard quản trị

## Yêu cầu môi trường

Trước khi cài đặt, hãy đảm bảo máy bạn đã có:

- PHP >= 8.1
- Composer
- Node.js >= 18
- MySQL hoặc MariaDB
- Git

## Cài đặt

### 1. Clone project

```bash
git clone <repository-url>
cd UDPT_14-main
```

### 2. Cài đặt PHP dependencies

```bash
composer install
```

### 3. Cài đặt frontend dependencies

```bash
npm install
```

### 4. Tạo file môi trường

```bash
cp .env.example .env
```

### 5. Cấu hình MySQL trong file `.env`

```env
APP_NAME="UDPT Cinema"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=udpt_cinema
DB_USERNAME=root
DB_PASSWORD=
```

> Lưu ý: nếu MySQL của bạn chạy trên cổng 3306 thì đổi `DB_PORT=3306`.

### 6. Tạo database

Trong MySQL, chạy:

```sql
CREATE DATABASE udpt_cinema;
```

### 7. Tạo key Laravel

```bash
php artisan key:generate
```

### 8. Chạy migration

```bash
php artisan migrate
```

Nếu muốn tạo dữ liệu mẫu:

```bash
php artisan db:seed
```

## Chạy dự án

### Backend

```bash
php artisan serve
```

Mặc định ứng dụng chạy tại:

```text
http://localhost:8000
```

### Frontend

```bash
npm run dev
```

Hoặc build production:

```bash
npm run build
```

## Route chính

- Trang chủ: `/`
- Danh sách phim: `/movies`
- Chi tiết phim: `/movies/{id}`
- Đặt vé: `/movies/{id}/book`
- Lịch sử đặt vé: `/bookings`
- Trang admin: `/admin/dashboard`

## Admin

Sau khi đăng nhập tài khoản admin, truy cập:

```text
/admin/dashboard
```

## Troubleshooting

### Lỗi kết nối database

- Kiểm tra MySQL đang chạy
- Kiểm tra đúng port `DB_PORT`
- Kiểm tra database đã tồn tại
- Kiểm tra username/password trong `.env`

Ví dụ kiểm tra lại:

```bash
php artisan config:clear
php artisan migrate
```

### Lỗi APP_KEY

```bash
php artisan key:generate
```

### Lỗi node dependencies

```bash
rm -rf node_modules package-lock.json
npm install
```

## License

Dự án này được phân phối theo giấy phép MIT.

## Ghi chú

Dự án này phù hợp cho môn Ứng dụng Phân tán, mô phỏng một hệ thống đặt vé rạp chiếu phim theo hướng ứng dụng web phân tán hiện đại, với khả năng quản lý dữ liệu, người dùng, lịch chiếu và giao dịch đặt vé trong môi trường web thực tế.
