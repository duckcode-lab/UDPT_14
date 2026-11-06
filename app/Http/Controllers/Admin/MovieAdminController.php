<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MovieAdminController extends Controller
{
    public function index()
    {
        $movies = Movie::with('genres')->orderBy('created_at', 'desc')->paginate(12);
        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        $genres = Genre::all();
        return view('admin.movies.create', compact('genres'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|numeric|min:1',
            'genres' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trailer' => 'nullable|string',
            'release_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
        ]);

        // xử lý trailer
        if (!empty($validated['trailer'])) {
            $validated['trailer'] = preg_replace(
                ['/watch\?v=([a-zA-Z0-9_-]+)/', '/youtu\.be\/([a-zA-Z0-9_-]+)/'],
                'https://www.youtube.com/embed/$1',
                $validated['trailer']
            );
        }

        // upload ảnh
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('uploads/movies', 'public');
        }

        // tạo movie
        $movie = Movie::create($validated);

        // xử lý genres
        $genreIds = [];

        if ($request->genres) {
            $genreNames = explode(',', $request->genres);

            foreach ($genreNames as $name) {
                $name = trim($name);
                if (!$name) continue;

                $genre = Genre::firstOrCreate(['name' => $name]);
                $genreIds[] = $genre->id;
            }
        }

        $movie->genres()->sync($genreIds);

        return redirect()->route('admin.movies.index')
            ->with('success', '🎬 Thêm phim thành công!');
    }

    public function edit(Movie $movie)
    {
        $genreNames = $movie->genres->pluck('name')->toArray();
        $movie->genre_string = implode(', ', $genreNames);
        $genres = Genre::all();
        return view('admin.movies.edit', compact('movie', 'genres'));
    }

    public function update(Request $request, Movie $movie)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|integer|min:1',
            'genres' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trailer' => 'nullable|string|max:255',
            'release_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
        ]);

        if (!empty($validated['trailer'])) {
            $pattern = '/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/|shorts\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/';
            if (preg_match($pattern, $validated['trailer'], $matches)) {
                $validated['trailer'] = 'https://www.youtube.com/embed/' . $matches[1];
            }
        }

        if ($request->hasFile('photo')) {
            if ($movie->photo && Storage::disk('public')->exists($movie->photo)) {
                Storage::disk('public')->delete($movie->photo);
            }
            $validated['photo'] = $request->file('photo')->store('uploads/movies', 'public');
        }

        $movie->update($validated);

        if ($request->genres) {
            $genreNames = explode(',', $request->genres);

            foreach ($genreNames as $name) {
                $name = trim($name);
                if (!$name) continue;

                $genre = \App\Models\Genre::firstOrCreate([
                    'name' => $name
                ]);

                $genreIds[] = $genre->id;
            }
        }

        $movie->genres()->sync($genreIds ?? []);

        return redirect()->route('admin.movies.index')->with('success', '🎥 Cập nhật phim thành công!');
    }

    public function destroy(Movie $movie)
    {

        if ($movie->photo && Storage::disk('public')->exists($movie->photo)) {
            Storage::disk('public')->delete($movie->photo);
        }

        $movie->genres()->detach();

        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', '🗑️ Đã xóa phim thành công!');
    }
}