<?php

namespace App\Http\Controllers;

use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminMovieController extends Controller
{
    public function index()
    {
        $movies = Movie::all();
        return view('admin.movies.index', compact('movies'));
    }

    public function create()
    {
        return view('admin.movies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|numeric|min:1',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trailer' => 'nullable|string',
            'release_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('uploads/movies', 'public');
        }

        Movie::create([
            'title' => $request->title,
            'description' => $request->description,
            'duration' => $request->duration,
            'photo' => $photoPath,
            'trailer' => $request->trailer,
            'release_date' => $request->release_date,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.movies.index')->with('success', 'Thêm phim thành công!');
    }

    public function edit($id)
    {
        $movie = Movie::findOrFail($id);
        return view('admin.movies.edit', compact('movie'));
    }

    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|numeric|min:1',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'trailer' => 'nullable|string',
            'release_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
        ]);

        $photoPath = $movie->photo;
        if ($request->hasFile('photo')) {
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
            }
            $photoPath = $request->file('photo')->store('uploads/movies', 'public');
        }

        $movie->update([
            'title' => $request->title,
            'description' => $request->description,
            'duration' => $request->duration,
            'photo' => $photoPath,
            'trailer' => $request->trailer,
            'release_date' => $request->release_date,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.movies.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        if ($movie->photo && Storage::disk('public')->exists($movie->photo)) {
            Storage::disk('public')->delete($movie->photo);
        }
        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Xóa phim thành công!');
    }
}
