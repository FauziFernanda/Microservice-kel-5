<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    // ADMIN: List all news
    public function index()
    {
        $news = News::with('creator')->orderByDesc('date')->paginate(10);
        $total = News::count();
        return view('admin.news.index', compact('news', 'total'));
    }

    // ADMIN: Show create form
    public function create()
    {
        return view('admin.news.create');
    }

    // ADMIN: Store news
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|string',
            'date' => 'required|date',
        ]);
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('news_images', 'public');
        }
        $validated['created_by'] = Auth::id() ?: null;
        News::create($validated);
        return redirect()->route('admin.news.index')->with('success', 'News created successfully.');
    }

    // ADMIN: Show edit form
    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    // ADMIN: Update news
    public function update(Request $request, News $news)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'required|string',
            'date' => 'required|date',
        ]);
        if ($request->hasFile('image')) {
            if ($news->image) Storage::disk('public')->delete($news->image);
            $validated['image'] = $request->file('image')->store('news_images', 'public');
        }
        $news->update($validated);
        return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
    }

    // ADMIN: Delete news
    public function destroy(News $news)
    {
        if ($news->image) Storage::disk('public')->delete($news->image);
        $news->delete();
        return redirect()->route('admin.news.index')->with('success', 'News deleted successfully.');
    }

    // API: List news for student (JSON) with Correlation ID support
    public function list(Request $request)
    {
        $correlationId = $this->getOrCreateCorrelationId($request);
        // For API consumers return JSON, but if the request comes from a browser
        // (Accept: text/html) return the admin view so the endpoint is browser-friendly.
        $news = News::with('creator')->orderByDesc('date')->paginate(10);
        $total = News::count();
        // If client expects JSON, return JSON
        if ($request->wantsJson()) {
            $news = News::orderByDesc('date')->get();
            return response()->json($news)->header('X-Correlation-ID', $correlationId);
        }
        // Otherwise return the admin HTML view (same as admin index)
        return response()->view('admin.news.index', compact('news', 'total'))
            ->header('X-Correlation-ID', $correlationId);
    }

    // API: Like news (requires authenticated user)
    public function like(Request $request, $id)
    {
        $correlationId = $this->getOrCreateCorrelationId($request);
        $news = News::findOrFail($id);
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated.'], 401)->header('X-Correlation-ID', $correlationId);
        }
        if ($news->likedUsers()->where('user_id', $user->id)->exists()) {
            return response()->json(['likes' => $news->likes])->header('X-Correlation-ID', $correlationId);
        }
        $news->likedUsers()->attach($user->id);
        $news->increment('likes');
        return response()->json(['likes' => $news->likes])->header('X-Correlation-ID', $correlationId);
    }

    // API: Increment view (tracks Correlation ID)
    public function incrementView(Request $request, $id)
    {
        $correlationId = $this->getOrCreateCorrelationId($request);
        $news = News::findOrFail($id);
        $news->increment('views');
        return response()->json(['views' => $news->views])->header('X-Correlation-ID', $correlationId);
    }

    // Get placeholder image
    public function placeholder($newsId)
    {
        $news = News::findOrFail($newsId);
        $title = substr($news->title, 0, 30);
        
        // Create SVG placeholder
        $colors = ['#ec4899', '#f97316', '#8b5cf6', '#06b6d4'];
        $hash = crc32($newsId);
        $color = $colors[abs($hash) % count($colors)];
        
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="600" height="300">
    <defs>
        <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color:{$color};stop-opacity:0.9" />
            <stop offset="100%" style="stop-color:#1f2937;stop-opacity:0.9" />
        </linearGradient>
    </defs>
    <rect width="600" height="300" fill="url(#grad)"/>
    <text x="50%" y="50%" font-size="24" font-weight="bold" text-anchor="middle" dominant-baseline="middle" fill="white">
        {$title}
    </text>
</svg>
SVG;
        
        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'max-age=31536000');
    }

    // Helper: return or create Correlation ID
    private function getOrCreateCorrelationId(Request $request)
    {
        $id = $request->header('X-Correlation-ID') ?? $request->header('X-CorrelationID');
        return $id ?? Str::uuid()->toString();
    }

    // Helper: check admin quickly
    private function isAdmin()
    {
        return false;
    }
} 