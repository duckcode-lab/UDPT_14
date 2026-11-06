<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Booking;

class AdminController extends Controller
{
    public function dashboard()
    {
        $movies = Movie::count();
        $showtimes = Showtime::count();
        $bookings = Booking::count();

        return view('admin.dashboard', compact('movies', 'showtimes', 'bookings'));
    }
    public function index()
    {
        $movies = Movie::latest()->get();
        return view('admin.movies.index', compact('movies'));
    }
}

