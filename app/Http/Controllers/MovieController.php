<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use App\Models\Showtime;
use Illuminate\Support\Facades\DB;
use App\Models\Banner;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $weeklyMovies = Movie::latest()->take(4)->get();

        $monthlyMovies = Movie::select(
            'movies.id',
            'movies.title',
            DB::raw('MAX(movies.photo) as photo'),
            DB::raw('COUNT(bookings.id) as total_tickets')
        )
        ->join('showtimes', 'showtimes.movie_id', '=', 'movies.id')
        ->join('bookings', 'bookings.showtime_id', '=', 'showtimes.id')
        ->whereMonth('bookings.created_at', now()->month)
        ->whereYear('bookings.created_at', now()->year)
        ->groupBy('movies.id', 'movies.title')
        ->orderByDesc('total_tickets')
        ->take(4)
        ->get();

        $banners = Banner::latest()->take(5)->get();

        return view('client.movies.index', compact('weeklyMovies', 'monthlyMovies','banners'));
    }

    public function list()
    {
        $movies = \App\Models\Movie::latest()->paginate(12);
        return view('client.movies.list', compact('movies'));
    }
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if ($query === '') {
            return redirect()->back()->with('error', 'Vui lòng nhập từ khóa để tìm kiếm.');
        }

        $movies = \App\Models\Movie::where('title', 'LIKE', '%' . $query . '%')
            ->orderBy('title')
            ->get();

        if ($request->ajax()) {
            return response()->json($movies);
        }
        return view('client.movies.search_results', compact('movies', 'query'));
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
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|integer',
            'trailer' => 'nullable|url',
            'photo' => 'nullable|image|max:2048',
        ]);

        $data = $request->only('title', 'description', 'duration', 'trailer');

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/movies', $filename);
            $data['photo'] = $filename;
        }

        Movie::create($data);
        return redirect()->route('admin.movies.index')->with('success', 'Phim đã được thêm thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $movie = Movie::with('comments.user')->findOrFail($id);
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

        $showtimes = Showtime::with(['ticketPrice', 'theater', 'bookings'])
            ->where('movie_id', $id)
            ->get();
        $bookedSeatsByShowtime = [];
        foreach ($showtimes as $showtime) {
            $bookedSeatsByShowtime[$showtime->id] = $showtime->bookings
                ->flatMap(fn($b) => array_map('trim', explode(',', $b->seats)))
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        }

        return view('client.movies.book', compact('movie', 'showtimes', 'bookedSeatsByShowtime'));
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

        return redirect()->route('movies.show', $id)->with('success', ' Đặt vé thành công!');
    }
    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string|max:500',
        ]);

        Comment::create([
            'user_id' => Auth::id(),
            'movie_id' => $id,
            'content' => $request->content,
        ]);
        return redirect()->back()->with('success', 'Bình luận đã được gửi!');
    }

}