<?php

namespace App\Http\Controllers;

use App\Models\NewsEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class NewsEventController extends Controller
{
    public function index()
    {
        $newsEvents = NewsEvent::with('creator')->latest()->get();
        return view('news_events.index', compact('newsEvents'));
    }

    public function create()
    {
        return view('news_events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip|max:5120',
            'youtube_link' => 'nullable|url|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = Auth::id();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('news_events', 'public');
            $validated['image'] = $path;
        }

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('news_events/attachments', 'public');
            $validated['attachment'] = $path;
        }

        NewsEvent::create($validated);

        return redirect()->route('news-events.index')->with('success', 'Pengumuman berhasil ditambahkan');
    }

    public function edit(NewsEvent $newsEvent)
    {
        return view('news_events.edit', compact('newsEvent'));
    }

    public function update(Request $request, NewsEvent $newsEvent)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attachment' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip|max:5120',
            'youtube_link' => 'nullable|url|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $validated['is_active'] = $request->has('is_active');

        if ($request->hasFile('image')) {
            if ($newsEvent->image) {
                Storage::disk('public')->delete($newsEvent->image);
            }
            $path = $request->file('image')->store('news_events', 'public');
            $validated['image'] = $path;
        }

        if ($request->hasFile('attachment')) {
            if ($newsEvent->attachment) {
                Storage::disk('public')->delete($newsEvent->attachment);
            }
            $path = $request->file('attachment')->store('news_events/attachments', 'public');
            $validated['attachment'] = $path;
        }

        $newsEvent->update($validated);

        return redirect()->route('news-events.index')->with('success', 'Pengumuman berhasil diperbarui');
    }

    public function destroy(NewsEvent $newsEvent)
    {
        if ($newsEvent->image) {
            Storage::disk('public')->delete($newsEvent->image);
        }
        if ($newsEvent->attachment) {
            Storage::disk('public')->delete($newsEvent->attachment);
        }
        $newsEvent->delete();

        return redirect()->route('news-events.index')->with('success', 'Pengumuman berhasil dihapus');
    }
}
