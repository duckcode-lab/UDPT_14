<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerAdminController extends Controller
{
    public function index()
    {
        $banners = Banner::latest()->paginate(10);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|string|max:255',
            'image' => 'required_without:image_url|nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'image_url' => 'required_without:image|nullable|url:http,https|max:255',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('uploads/banners', 'public');
        } else {
            $validated['image'] = $validated['image_url'];
        }
        unset($validated['image_url']);

        Banner::create($validated);
        return redirect()->route('admin.banners.index')->with('success', 'Thêm banner thành công!');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'link' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'image_url' => 'nullable|url:http,https|max:255',
        ]);

        if ($request->hasFile('image') || $request->filled('image_url')) {
            if ($banner->image && !Str::startsWith($banner->image, ['http://', 'https://'])
                && Storage::disk('public')->exists($banner->image)) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->hasFile('image')
                ? $request->file('image')->store('uploads/banners', 'public')
                : $validated['image_url'];
        }
        unset($validated['image_url']);

        $banner->update($validated);
        return redirect()->route('admin.banners.index')->with('success', 'Cập nhật banner thành công!');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->image && !Str::startsWith($banner->image, ['http://', 'https://'])
            && Storage::disk('public')->exists($banner->image)) {
            Storage::disk('public')->delete($banner->image);
        }

        $banner->delete();
        return redirect()->route('admin.banners.index')->with('success', 'Đã xóa banner!');
    }
}
