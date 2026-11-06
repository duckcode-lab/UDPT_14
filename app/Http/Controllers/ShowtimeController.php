<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\TicketPrice;
use App\Models\Showtime;
use Illuminate\Http\Request;

class ShowtimeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $movies = Movie::all();
        $prices = TicketPrice::with('theater')->orderBy('price')->get();

        return view('admin.showtimes.create', compact('movies', 'prices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'ticket_price_id' => 'required|exists:ticket_prices,id',
            'time' => 'required|date',
            'room' => 'required|string|max:255',
        ]);

        $ticketPrice = TicketPrice::findOrFail($request->ticket_price_id);
        Showtime::create([
            'movie_id' => $request->movie_id,
            'theater_id' => $ticketPrice->theater_id,
            'ticket_price_id' => $request->ticket_price_id,
            'time' => $request->time,
            'room' => $request->room,
        ]);

        return redirect()->route('admin.showtimes.index')->with('success', 'Thêm suất chiếu thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Showtime $showtime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Showtime $showtime)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Showtime $showtime)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Showtime $showtime)
    {
        $showtime->delete();
        return back()->with('success', '🗑️ Đã xóa suất chiếu!');
    }
}
