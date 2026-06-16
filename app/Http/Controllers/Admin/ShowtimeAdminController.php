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
        // 1. Cấu hình kiểm tra dữ liệu đầu vào (Validate)
        $validated = $request->validate([
            'movie_id'        => 'required|exists:movies,id',
            'theater_name'    => 'required|string|max:255',
            'ticket_price'    => 'required|numeric|min:0',
            'room'            => 'required|string|max:255',
            'time'            => 'required|date',
        ]);

        // 2. Lưu trực tiếp mảng đã được kiểm tra tính hợp lệ vào CSDL
        Showtime::create($validated);

        return redirect()->route('admin.showtimes.index')
            ->with('success', '✅ Thêm suất chiếu mới thành công!');
    }

    public function edit($id)
    {
        $showtime = Showtime::findOrFail($id);
        $movies = Movie::all();

        // Bỏ load dữ liệu bảng phụ, chỉ truyền dữ liệu suất chiếu hiện tại để sửa
        return view('admin.showtimes.edit', compact('showtime', 'movies'));
    }

    public function update(Request $request, $id)
    {
        // Cấu hình validate tương tự hàm store khi bấm cập nhật
        $request->validate([
            'movie_id'        => 'required|exists:movies,id',
            'theater_name'    => 'required|string|max:255',
            'ticket_price'    => 'required|numeric|min:0',
            'room'            => 'required|string|max:255',
            'time'            => 'required|date',
        ]);

        $showtime = Showtime::findOrFail($id);
        $showtime->movie_id     = $request->movie_id;
        $showtime->theater_name = $request->theater_name;
        $showtime->ticket_price = $request->ticket_price;
        $showtime->room         = $request->room;
        $showtime->time         = $request->time;
        $showtime->save();

        return redirect()->route('admin.showtimes.index')
            ->with('success', '✅ Cập nhật thông tin suất chiếu thành công!');
    }
    public function destroy($id)
    {
        Showtime::findOrFail($id)->delete();
        return redirect()->route('admin.showtimes.index')->with('success', 'Đã xóa suất chiếu.');
    }
}