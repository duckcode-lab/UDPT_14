<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $movies = \App\Models\Movie::latest()->paginate(9);
        return view('client.movies.index', compact('movies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $movie = Movie::findOrFail($id);
        return view('client.movies.show', compact('movie'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Movie $movie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movie $movie)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movie $movie)
    {
        //
    }
    public function book($id)
    {
        $movie = Movie::findOrFail($id);
        $showtimes = $movie->showtimes;

        return view('client.movies.book', compact('movie', 'showtimes'));
    }
    public function storeBooking(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Bạn cần đăng nhập để đặt vé!');
        }

        $request->validate([
            'showtime_id' => 'required|exists:showtimes,id',
            'seats' => 'required|string',
        ]);

        $showtime = \App\Models\Showtime::findOrFail($request->showtime_id);
        $seats = explode(',', $request->seats);
        $total = count($seats) * $showtime->price;

        Booking::create([
            'user_id' => Auth::id(),
            'showtime_id' => $showtime->id,
            'seats' => implode(',', $seats),
            'total_price' => $total,
        ]);

        return redirect()->route('movies.show', $id)->with('success', '🎉 Đặt vé thành công!');
    }
}
