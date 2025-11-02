<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Showtime;
use App\Models\Movie;
use App\Models\Theater;
use App\Models\TicketPrice;

class ShowtimeAdminController extends Controller
{
    public function index()
    {
        $showtimes = Showtime::with(['movie', 'theater', 'ticketPrice'])->get();
        return view('admin.showtimes.index', compact('showtimes'));
    }

    public function create()
    {
        $movies = Movie::all();
        $theaters = Theater::all();
        $prices = TicketPrice::orderBy('price', 'asc')->get();
        return view('admin.showtimes.create', compact('movies', 'theaters', 'prices'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'theater_id' => 'required|exists:theaters,id',
            'ticket_price_id' => 'required|exists:ticket_prices,id',
            'time' => 'required|date',
        ]);

        Showtime::create([
            'movie_id' => $request->movie_id,
            'theater_id' => $request->theater_id,
            'ticket_price_id' => $request->ticket_price_id,
            'room' => $request->room,
            'time' => $request->time,
        ]);

        return redirect()->route('admin.showtimes.index')->with('success', '✅ Thêm suất chiếu thành công!');
    }
    public function edit($id)
    {
        $showtime = Showtime::findOrFail($id);
        $movies = Movie::all();
        $theaters = Theater::all();
        $prices = TicketPrice::orderBy('price', 'asc')->get();
        return view('admin.showtimes.edit', compact('showtime', 'movies', 'theaters', 'prices'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'theater_id' => 'required|exists:theaters,id',
            'ticket_price_id' => 'required|exists:ticket_prices,id',
            'time' => 'required|date',
            'room' => 'required|string',
        ]);

        $showtime = Showtime::findOrFail($id);
        $showtime->movie_id = $request->movie_id;
        $showtime->theater_id = $request->theater_id;
        $showtime->ticket_price_id = $request->ticket_price_id;
        $showtime->time = $request->time;
        $showtime->room = $request->room;
        $showtime->save();

        return redirect()->route('admin.showtimes.index')
            ->with('success', 'Cập nhật suất chiếu thành công!');
    }

    public function destroy($id)
    {
        Showtime::findOrFail($id)->delete();
        return redirect()->route('admin.showtimes.index')->with('success', 'Đã xóa suất chiếu.');
    }
}
