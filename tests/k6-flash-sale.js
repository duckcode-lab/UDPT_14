import http from 'k6/http';
import { check, sleep } from 'k6';
import { Counter, Rate } from 'k6/metrics';

// Định nghĩa Custom Metrics
export const successfulBookings = new Counter('successful_bookings');
export const lockedSeatErrors = new Counter('seat_locked_409');
export const rateLimitErrors = new Counter('rate_limited_429');
export const raceConditionRate = new Rate('race_condition_occurred');

// Cấu hình Kịch bản Kiểm thử Tải Flash Sale
export const options = {
    scenarios: {
        flash_sale_rush: {
            executor: 'constant-vus',
            vus: 20,              // 20 người dùng ảo đồng thời
            duration: '10s',       // Bắn liên tục trong 10 giây
        },
    },
    thresholds: {
        http_req_duration: ['p(95)<1500'], // 95% request hoàn thành dưới 1.5s
        'race_condition_occurred': ['rate==0'], // Không cho phép bất kỳ tranh chấp trùng ghế nào
    },
};

const BASE_URL = __ENV.API_URL || 'http://api-gateway';
const targetSeats = ['E1', 'E2', 'E3']; // 3 ghế VIP mục tiêu tranh chấp

export default function () {
    // Mỗi VU chọn ngẫu nhiên 1 trong 3 ghế đang mở bán Flash-sale
    const selectedSeat = targetSeats[Math.floor(Math.random() * targetSeats.length)];
    const userId = __VU;
    const userEmail = `buyer_${__VU}_${__ITER}@gmail.com`;

    const payload = JSON.stringify({
        showtime_id: 1,
        seat_code: selectedSeat,
        user_id: userId,
        user_email: userEmail,
        payment_method: 'VNPAY_FLASH_SALE'
    });

    const params = {
        headers: {
            'Content-Type': 'application/json',
        },
        timeout: '4s'
    };

    const res = http.post(`${BASE_URL}/api/bookings/book`, payload, params);

    // Kiểm tra các trường hợp phản hồi từ hệ thống phân tán
    if (res.status === 201) {
        successfulBookings.add(1);
        check(res, {
            'Đặt vé thành công (HTTP 201)': (r) => r.status === 201,
            'Có mã giao dịch hợp lệ': (r) => r.json().booking.transaction_id !== undefined,
        });
    } else if (res.status === 409) {
        lockedSeatErrors.add(1);
        check(res, {
            'Ghế đã bị khóa / bán (HTTP 409 SEAT_LOCKED)': (r) => r.status === 409,
        });
    } else if (res.status === 429) {
        rateLimitErrors.add(1);
        check(res, {
            'Bị chặn bởi Gateway Rate Limit (HTTP 429)': (r) => r.status === 429,
        });
    } else {
        check(res, {
            'Lỗi bất thường': (r) => false,
        });
    }

    sleep(0.2); // Tạm nghỉ 200ms giữa các lần thử
}
