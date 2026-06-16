<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\TicketConfirmationMail;
use App\Mail\AdminCancelBookingMail;
use App\Mail\UserCancelBookingMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Booking;
use App\Models\Showtime;
use App\Models\User;

class BookingController extends Controller
{
    public function index()
    {
        if (auth()->user()->role === 'admin') {
            $bookings = Booking::with(['user', 'showtime.movie'])->latest()->get();
        } else {
            $bookings = Booking::with(['showtime.movie'])
                ->where('user_id', auth()->id())
                ->latest()
                ->get();
        }

        return view('bookings.index', compact('bookings'));
    }
    public function adminIndex()
    {
        $bookings = Booking::with(['user', 'showtime.movie'])
            ->latest()
            ->get();

        return view('admin.bookings.index', compact('bookings'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,id',
            'seats' => 'required|string',
        ]);

        $showtime = Showtime::findOrFail($request->showtime_id);
        $seats = array_map('trim', explode(',', $request->seats));

        $bookedSeats = Booking::where('showtime_id', $showtime->id)
            ->pluck('seats')
            ->flatMap(fn($s) => array_map('trim', explode(',', $s)))
            ->toArray();

        $conflict = array_intersect($seats, $bookedSeats);
        if (!empty($conflict)) {
            return back()->with('error', '⚠️ Các ghế sau đã được đặt: ' . implode(', ', $conflict));
        }

        $seatCount = count($seats);
        $singlePrice = $showtime->ticket_price ?? ($showtime->ticketPrice->price ?? 0);
        $totalPrice = $seatCount * $singlePrice;

        $ticket = Booking::create([
            'user_id' => auth()->id(),
            'showtime_id' => $showtime->id,
            'movie_title' => $showtime->movie->title ?? 'Không rõ',
            'seats' => implode(', ', $seats),
            'show_time' => $showtime->time,
            'date' => now(),
            'code' => strtoupper(Str::random(8)),
            'total_price' => $totalPrice,
        ]);

        try {
            Mail::to(auth()->user()->email)->send(new TicketConfirmationMail($ticket));
            $message = '🎟️ Đặt vé thành công! Vé đã được gửi về email của bạn.';
        } catch (\Exception $e) {
            Log::error('Lỗi gửi mail: ' . $e->getMessage());
            $message = '🎟️ Đặt vé thành công nhưng hệ thống chưa gửi được email.';
        }

        return redirect()->route('bookings.index')->with('success', $message);
    }

    public function destroy($id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            Mail::to(auth()->user()->email)->send(new UserCancelBookingMail($booking));
        } catch (\Exception $e) {
            Log::error('Lỗi gửi mail huỷ vé: ' . $e->getMessage());
        }

        $booking->delete();

        return redirect()->route('bookings.index')
            ->with('success', '🎫 Vé đã được huỷ thành công! Ghế đã được trả lại.');
    }

    public function adminDestroy($id)
    {
        $booking = Booking::with(['user', 'showtime.movie'])->findOrFail($id);
        $user = $booking->user;

        $movieTitle = $booking->movie_title;
        $showTime = \Carbon\Carbon::parse($booking->show_time)->format('H:i d/m/Y');
        $seats = $booking->seats;

        $booking->delete();

        try {
            if ($user && $user->email) {
                Mail::to($user->email)->send(new AdminCancelBookingMail($user, $movieTitle, $showTime, $seats));
            }
        } catch (\Exception $e) {
            Log::error("Không gửi được mail huỷ vé: " . $e->getMessage());
        }

        return redirect()->back()->with('success', '🎫 Vé đã bị huỷ và email thông báo đã được gửi đến khách hàng.');
    }
}