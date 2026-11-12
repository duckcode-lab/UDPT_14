# HƯỚNG DẪN KỊCH BẢN KIỂM THỬ & DEMO HỆ THỐNG PHÂN TÁN (10 USE CASES)

Tài liệu này cung cấp **10 Kịch bản Kiểm thử & Demo thực nghiệm (Use Cases)** phục vụ báo cáo đồ án, chứng minh đầy đủ các tính chất phân tán của hệ thống: **Nginx API Gateway, JWT RBAC, gRPC binary, Redis Distributed Lock, Transactional Outbox, Apache Kafka Event-Driven, Circuit Breaker, Sao lưu DB, và k6 Stress Testing**.

---

## CÁCH THỰC HIỆN DEMO

Bạn có thể thực hiện kiểm thử và demo theo **3 hình thức linh hoạt**:
1. **Hình thức 1 (Trực quan nhất):** Mở trình duyệt Chrome/Edge truy cập `http://localhost/` để tương tác trực tiếp trên giao diện Cinema Dashboard.
2. **Hình thức 2 (Menu tự động 1-click):** Chạy lệnh PowerShell tương tác:
   ```powershell
   powershell -ExecutionPolicy Bypass -File .\scripts\demo-menu.ps1
   ```
3. **Hình thức 3 (Dòng lệnh cURL / REST Client):** Gửi từng request theo các Use Case dưới đây.

---

## DANH SÁCH 10 KỊCH BẢN USE CASE CHI TIẾT

### USE CASE 1: NGINX API GATEWAY & RATE LIMITING CHỐNG BOT/SPAM (Chương 2)
* **Ý nghĩa:** Chứng minh Nginx hoạt động như Single Entry Point, bảo vệ hệ thống trước tình trạng bot càn quét vé đợt Flash-sale.
* **Cách thực hiện:**
  Bắn 15 requests dồn dập trong 1 giây vào endpoint đặt vé qua Gateway:
  ```powershell
  1..15 | ForEach-Object { try { (Invoke-WebRequest -Uri "http://localhost/api/bookings/health" -UseBasicParsing -TimeoutSec 1).StatusCode } catch { $_.Exception.Response.StatusCode.value__ } }
  ```
* **Kết quả kỳ vọng:**
  - 10 requests đầu tiên trả về HTTP `200 OK`.
  - Các request vượt ngưỡng bị Gateway chặn đứng tức thì với HTTP **`429 Too Many Requests`**.

---

### USE CASE 2: AUTH SERVICE - XÁC THỰC STATELESS JWT & PHÂN QUYỀN RBAC (Chương 2 & 5)
* **Ý nghĩa:** Chứng minh hệ thống xác thực phân tán không lưu trạng thái trên server (Stateless Token) và kiểm soát truy cập theo vai trò.
* **Bước 1 (User thường):**
  ```powershell
  $user = Invoke-RestMethod -Uri "http://localhost/api/auth/login" -Method Post -ContentType "application/json" -Body '{"email":"user@cinema.com","password":"User@123456"}'
  # Thử truy cập API quản trị của Admin:
  Invoke-RestMethod -Uri "http://localhost/api/auth/users" -Headers @{ Authorization = "Bearer $($user.token)" }
  ```
  $\rightarrow$ **Kết quả:** Bị từ chối với HTTP **`403 Forbidden`** (*"Không có quyền truy cập: Yêu cầu vai trò [admin]"*).
* **Bước 2 (Quản trị viên):**
  ```powershell
  $admin = Invoke-RestMethod -Uri "http://localhost/api/auth/login" -Method Post -ContentType "application/json" -Body '{"email":"admin@cinema.com","password":"Admin@123456"}'
  Invoke-RestMethod -Uri "http://localhost/api/auth/users" -Headers @{ Authorization = "Bearer $($admin.token)" }
  ```
  $\rightarrow$ **Kết quả:** Thành công HTTP `200 OK`, trả về danh sách tất cả tài khoản từ `auth_db`.

---

### USE CASE 3: SHOW SERVICE - QUẢN LÝ PHIM & SƠ ĐỒ MA TRẬN GHẾ (Chương 2)
* **Ý nghĩa:** Truy xuất dữ liệu từ `show_db` độc lập, trả về ma trận sơ đồ rạp 40 ghế (A1 - E8).
* **Lệnh gọi:**
  ```powershell
  Invoke-RestMethod -Uri "http://localhost/api/shows/showtimes/1/seats" | ConvertTo-Json -Depth 3
  ```
* **Kết quả:** Trả về danh sách 40 ghế, phân loại Standard (85.000 VNĐ) và VIP hàng E (100.000 VNĐ).

---

### USE CASE 4: ĐỒNG BỘ gRPC & CHỐNG RACE CONDITION BẰNG REDIS DISTRIBUTED LOCK (Chương 3, 4 & 6)
* **Ý nghĩa:** **Trọng tâm đề tài.** Chứng minh lệnh nguyên tử `SETNX` với TTL trong Redis ngăn chặn tuyệt đối tình trạng 2 người mua cùng 1 ghế.
* **Tình huống:** Khách hàng 1 và Khách hàng 2 cùng bấm mua ghế `B4`:
  ```powershell
  # 1. Khách hàng 1 đặt ghế B4:
  Invoke-RestMethod -Uri "http://localhost/api/bookings/book" -Method Post -ContentType "application/json" -Body '{"showtime_id": 1, "seat_code": "B4", "user_id": 1, "user_email": "khach1@gmail.com", "payment_method": "VNPAY"}'

  # 2. Khách hàng 2 đặt lại ngay lập tức ghế B4:
  Invoke-RestMethod -Uri "http://localhost/api/bookings/book" -Method Post -ContentType "application/json" -Body '{"showtime_id": 1, "seat_code": "B4", "user_id": 2, "user_email": "khach2@gmail.com", "payment_method": "MOMO"}'
  ```
* **Kết quả kỳ vọng:**
  - Khách hàng 1: Thành công HTTP **`201 Created`**, trả về UUID vé.
  - Khách hàng 2: Bị chặn ngay lập tức với HTTP **`409 Conflict`** (*"Ghế đang được người khác giữ chỗ hoặc đang trong tiến trình thanh toán!"*).
  - Tỷ lệ Overbooking = **0%**.

---

### USE CASE 5: PAYMENT SERVICE & CƠ CHẾ IDEMPOTENCY (Chương 2 & 5)
* **Ý nghĩa:** Chứng minh nếu mạng bị chập chờn hoặc người dùng bấm đúp nút thanh toán, Payment Service không bao giờ trừ tiền 2 lần cho cùng một `booking_id`.
* **Cách thực hiện:** Gửi cùng 1 `booking_id` 2 lần vào `/api/payments/process`:
  ```powershell
  $body = '{"booking_id":"FIXED-UUID-TEST-01","user_id":1,"amount":120000,"payment_method":"VNPAY"}'
  Invoke-RestMethod -Uri "http://localhost/api/payments/process" -Method Post -ContentType "application/json" -Body $body
  Invoke-RestMethod -Uri "http://localhost/api/payments/process" -Method Post -ContentType "application/json" -Body $body
  ```
* **Kết quả:** Lần 2 hệ thống trả về thông báo: *"Giao dịch đã được thanh toán trước đó (Idempotent)"* kèm mã giao dịch cũ, không tạo record mới trong `payment_db`.

---

### USE CASE 6: EVENT-DRIVEN KAFKA, TRANSACTIONAL OUTBOX & NOTIFICATION QR (Chương 2, 4 & 6)
* **Ý nghĩa:** Chứng minh tính toàn vẹn sự kiện (Transactional Outbox) và xử lý thông báo bất đồng bộ qua message broker Kafka.
* **Cách kiểm tra:**
  1. Kiểm tra bảng `outbox` trong `booking_db`:
     ```powershell
     docker exec booking-db mariadb -u booking_user -pbooking_secret booking_db -e "SELECT id, aggregate_id, event_type, status, created_at FROM outbox;"
     ```
     $\rightarrow$ Bản ghi có status là **`PROCESSED`**.
  2. Xem log Kafka Consumer của `notification-service`:
     ```powershell
     docker logs --tail 20 notification-service
     ```
     $\rightarrow$ Log hiển thị: `[Notification Service] 📨 Nhận sự kiện từ Kafka [booking-events]` và `📧 Đã gửi email xác nhận và vé QR Code`.
  3. Lấy ảnh mã QR Code vé:
     ```powershell
     Invoke-RestMethod -Uri "http://localhost/api/notifications/tickets/DEMO-QR/qr"
     ```

---

### USE CASE 7: TÍNH CHỊU LỖI (FAULT TOLERANCE) VỚI CIRCUIT BREAKER OPOSSUM (Chương 8)
* **Ý nghĩa:** Chứng minh khi các dịch vụ phụ trợ (Show hoặc Payment) gặp sự cố, Circuit Breaker ngắt mạch (`OPEN`) để phản hồi lỗi fallback ngay lập tức, không làm sập Booking Service.
* **Cách kiểm tra:**
  ```powershell
  Invoke-RestMethod -Uri "http://localhost/api/bookings/health" | Select-Object -ExpandProperty circuit_breakers | ConvertTo-Json
  ```
* **Kết quả:** Hiển thị trạng thái `CLOSED` khi bình thường, và tự động chuyển sang `OPEN` / `HALF_OPEN` khi tỷ lệ lỗi vượt ngưỡng 50%.

---

### USE CASE 8: SAO LƯU VÀ PHỤC HỒI DỮ LIỆU TẬP TRUNG (Chương 7)
* **Ý nghĩa:** Quy trình khắc phục thảm họa (Disaster Recovery) cho mô hình Database-per-Service.
* **Sao lưu:**
  ```powershell
  powershell -ExecutionPolicy Bypass -File .\scripts\backup-db.ps1
  ```
  $\rightarrow$ Tự động kết xuất 5 file `.sql` vào thư mục `backups/YYYY-MM-DD_HH-mm-ss/`.
* **Phục hồi:**
  ```powershell
  powershell -ExecutionPolicy Bypass -File .\scripts\restore-db.ps1
  ```
  $\rightarrow$ Tự động nạp dữ liệu sạch vào lại 5 container MariaDB.

---

### USE CASE 9: KIỂM THỬ CHỊU TẢI FLASH-SALE BẰNG k6 (Chương 6)
* **Ý nghĩa:** Bắn tải thực tế mô phỏng 20 Virtual Users liên tục tranh chấp 3 ghế VIP trong 10 giây (gần 1.000 requests).
* **Lệnh chạy:**
  ```powershell
  powershell -ExecutionPolicy Bypass -File .\scripts\run-k6.ps1
  ```
* **Kết quả thực tế đã đạt:**
  - `successful_bookings`: **Chính xác 3** (mỗi ghế chỉ bán cho 1 người).
  - `race_condition_occurred`: **0.00%**.
  - `seat_locked_409`: **107 requests** bị Redis Lock chặn.
  - `rate_limited_429`: **887 requests** bị Nginx Gateway Rate Limiting bảo vệ.

---

### USE CASE 10: DEMO TRỰC QUAN TRÊN TRÌNH DUYỆT (CHROME / EDGE)
* **Ý nghĩa:** Dành cho việc trình chiếu trực tiếp trên máy chiếu khi thuyết minh đồ án.
* **Cách thực hiện:**
  1. Mở trình duyệt bất kỳ và truy cập địa chỉ: **`http://localhost/`**
  2. Bạn sẽ thấy ngay **Cinema Distributed Dashboard**:
     - Xem danh sách phim từ Show Service.
     - Click chọn ghế trên sơ đồ rạp 5x8 tương tác (ghế đổi màu vàng).
     - Bấm nút **"XÁC NHẬN ĐẶT VÉ (REDIS LOCK)"** $\rightarrow$ nhận vé điện tử kèm **ảnh mã QR Code vé**.
     - Bấm nút **"Test Tranh chấp Ghế"** $\rightarrow$ hệ thống tự động bắn 2 request đồng thời và hiển thị kết quả: 1 người thành công, 1 người bị chặn kèm mã HTTP 409!
