<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\BannerAdminController;
use App\Http\Controllers\Admin\MovieAdminController;
use App\Http\Controllers\Admin\ShowtimeAdminController;
use App\Http\Controllers\Admin\UserAdminController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShowtimeController;
use Illuminate\Support\Facades\Route;

// KHÁCH (Chưa đăng nhập)
Route::get('/', [MovieController::class, 'index'])->name('home');
Route::get('/movies', [MovieController::class, 'index'])->name('movies.index');
Route::get('/movies/list', [MovieController::class, 'list'])->name('movies.list');
Route::get('/search', [\App\Http\Controllers\MovieController::class, 'search'])->name('movies.search');
Route::get('/movies/{id}', [MovieController::class, 'show'])->name('movies.show');
Route::post('/movies/{movie}/comments', [CommentController::class, 'store'])
    ->middleware('auth')
    ->name('comments.store');
Route::get('/verify-otp', function () {
    return view('auth.verify-otp');
})->name('verify.otp');
Route::post('/resend-otp', [RegisteredUserController::class, 'resendOtp'])
    ->name('resend.otp');
Route::post('/verify-otp', [RegisteredUserController::class, 'verifyOtp'])
    ->name('verify.otp.submit');
// NGƯỜI DÙNG ĐÃ ĐĂNG NHẬP
Route::middleware(['auth'])->group(function () {
    Route::get('/movies/{id}/book', [MovieController::class, 'book'])->name('movies.book');
    Route::post('/movies/{id}/book', [BookingController::class, 'store'])->name('movies.book.store');
    Route::post('/movies/{id}/comment', [MovieController::class, 'storeComment'])->name('movies.comment');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy'])->name('bookings.destroy')->middleware('auth');
});

// ADMIN
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('movies', MovieAdminController::class)->except(['show']);
    Route::resource('showtimes', ShowtimeAdminController::class);
    Route::get('/showtimes/create', [ShowtimeController::class, 'create'])->name('showtimes.create.custom');
    Route::post('/showtimes/store', [ShowtimeController::class, 'store'])->name('showtimes.store.custom');
    Route::resource('banners', BannerAdminController::class);
    Route::resource('users', UserAdminController::class)->except(['show']);
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::delete('/admin/bookings/{id}', [BookingController::class, 'adminDestroy'])->name('admin.bookings.destroy');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('showtimes', ShowtimeAdminController::class);
    Route::get('/showtimes/create', [ShowtimeAdminController::class, 'create'])->name('showtimes.create');
    Route::post('/showtimes/store', [ShowtimeAdminController::class, 'store'])->name('showtimes.store');
    Route::get('/bookings', [BookingController::class, 'adminIndex'])->name('bookings.index');
    Route::delete('/bookings/{id}', [BookingController::class, 'adminDestroy'])->name('bookings.destroy');
});

require __DIR__.'/auth.php';
