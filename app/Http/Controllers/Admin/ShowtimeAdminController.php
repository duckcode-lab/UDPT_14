<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Showtime;
use App\Models\Movie;
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
        $prices = TicketPrice::with('theater')->orderBy('price')->get();

        return view('admin.showtimes.create', compact('movies', 'prices'));
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id'        => 'required|exists:movies,id',
            'ticket_price_id' => 'required|exists:ticket_prices,id',
            'room'            => 'required|string|max:255',
            'time'            => 'required|date',
        ]);

        $ticketPrice = TicketPrice::findOrFail($validated['ticket_price_id']);
        $validated['theater_id'] = $ticketPrice->theater_id;
        Showtime::create($validated);

        return redirect()->route('admin.showtimes.index')
            ->with('success', '✅ Thêm suất chiếu mới thành công!');
    }

    public function edit($id)
    {
        $showtime = Showtime::findOrFail($id);
        $movies = Movie::all();
        $prices = TicketPrice::with('theater')->orderBy('price')->get();
        return view('admin.showtimes.edit', compact('showtime', 'movies', 'prices'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'movie_id'        => 'required|exists:movies,id',
            'ticket_price_id' => 'required|exists:ticket_prices,id',
            'room'            => 'required|string|max:255',
            'time'            => 'required|date',
        ]);

        $showtime = Showtime::findOrFail($id);
        $ticketPrice = TicketPrice::findOrFail($validated['ticket_price_id']);
        $validated['theater_id'] = $ticketPrice->theater_id;
        $showtime->update($validated);

        return redirect()->route('admin.showtimes.index')
            ->with('success', '✅ Cập nhật thông tin suất chiếu thành công!');
    }
    public function destroy($id)
    {
        Showtime::findOrFail($id)->delete();
        return redirect()->route('admin.showtimes.index')->with('success', 'Đã xóa suất chiếu.');
    }
}