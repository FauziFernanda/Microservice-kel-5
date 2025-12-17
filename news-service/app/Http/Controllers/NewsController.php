<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class NewsController extends Controller
{

    public function indexApi()
    {
        // Ensure we have a correlation id (from header or generated) and include it in logs/responses
        $correlationId = $this->getOrCreateCorrelationId(request());

        Log::info("API: Fetching news list", ['correlation_id' => $correlationId]);

        $news = News::all();

        if ($news->isEmpty()) {
            Log::warning("API: No news found", ['correlation_id' => $correlationId]);
        }

        Log::info("API: Returning JSON response", ['count' => $news->count(), 'correlation_id' => $correlationId]);

        return response()->json([
            'success' => true,
            'data' => $news
        ], 200)->header('X-Correlation-ID', $correlationId);
    }
    public function storeApi(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'date' => 'required|date',
            ]);

            if ($validator->fails()) {
                Log::error("API Validation Error", $validator->errors()->toArray());
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $validated = $validator->validated();

            $validated['created_by'] = Auth::id() ?: null;
            
            $news = News::create($validated);

            return response()->json($news, 201);

        } catch (\Exception $e) {
            $corr = $this->getOrCreateCorrelationId($request);
            Log::error("API Error", ['message' => $e->getMessage(), 'correlation_id' => $corr]);
            return response()->json(['error' => 'Internal server error', 'correlation_id' => $corr], 500)
                ->header('X-Correlation-ID', $corr);
        }
    }
    public function showApi($id)
    {
        try {
            $news = News::findOrFail($id);

            Log::info("API: Retrieved news item", ['id' => $id]);

            return response()->json($news, 200);

        } catch (ModelNotFoundException $e) {
            $corr = $this->getOrCreateCorrelationId(request());
            Log::warning("API: News not found", ['id' => $id, 'correlation_id' => $corr]);
            return response()->json(['error' => 'News not found', 'correlation_id' => $corr], 404)
                ->header('X-Correlation-ID', $corr);

        } catch (\Exception $e) {
            $corr = $this->getOrCreateCorrelationId(request());
            Log::error("API: Error fetching news", ['message' => $e->getMessage(), 'correlation_id' => $corr]);
            return response()->json(['error' => 'Internal server error', 'correlation_id' => $corr], 500)
                ->header('X-Correlation-ID', $corr);
        }
    }
    public function updateApi(Request $request, $id)
    {
        try {
            $news = News::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'date' => 'sometimes|required|date',
            ]);

            if ($validator->fails()) {
                Log::warning("API: Validation failed on update", $validator->errors()->toArray());
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $news->update($validator->validated());

            Log::info("API: News updated", ['id' => $id]);

            return response()->json($news, 200);

        } catch (ModelNotFoundException $e) {
            Log::warning("API: Update failed, news not found", ['id' => $id]);
            return response()->json(['error' => 'News not found'], 404);

        } catch (\Exception $e) {
            Log::error("API: Update error", ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    public function destroyApi($id)
    {
        try {
            $news = News::findOrFail($id);

            $news->delete();

            Log::info("API: News deleted", ['id' => $id]);

            return response()->json(['message' => 'News deleted successfully'], 200);

        } catch (ModelNotFoundException $e) {
            Log::warning("API: Delete failed, news not found", ['id' => $id]);
            return response()->json(['error' => 'News not found'], 404);

        } catch (\Exception $e) {
            Log::error("API: Delete error", ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }


    // ADMIN: List all news
    public function index()
    {
        try {
            Log::info('Fetching all news articles');
            $news = News::with('creator')->orderByDesc('date')->paginate(10);
            $total = News::count();
            Log::info('Successfully fetched news articles', ['total' => $total, 'page' => request()->get('page', 1)]);
            return view('admin.news.index', compact('news', 'total'));
        } catch (\Exception $e) {
            Log::error('Error fetching news articles', ['error' => $e->getMessage()]);
            return redirect()->route('admin.news.index')->with('error', 'Failed to load news');
        }
    }

    // ADMIN: Show create form
    public function create()
    {
        return view('admin.news.create');
    }

    // ADMIN: Store news
    public function store(Request $request)
    {
        try {
            Log::info('Creating new news article', ['title' => $request->input('title')]);
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'description' => 'required|string',
                'date' => 'required|date',
            ]);
            
            if ($request->hasFile('image')) {
                $validated['image'] = $request->file('image')->store('news_images', 'public');
                Log::info('Image uploaded successfully', ['image' => $validated['image']]);
            }
            
            $validated['created_by'] = Auth::id() ?: null;
            $news = News::create($validated);
            
            Log::info('News article created successfully', ['id' => $news->id, 'title' => $news->title]);
            return redirect()->route('admin.news.index')->with('success', 'News created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error while creating news', ['errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error creating news article', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create news');
        }
    }

    // ADMIN: Show edit form
    public function edit(News $news)
    {
        return view('admin.news.edit', compact('news'));
    }

    // ADMIN: Update news
    public function update(Request $request, News $news)
    {
        try {
            Log::info('Updating news article', ['id' => $news->id, 'title' => $news->title]);
            
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'description' => 'required|string',
                'date' => 'required|date',
            ]);
            
            if ($request->hasFile('image')) {
                if ($news->image) Storage::disk('public')->delete($news->image);
                $validated['image'] = $request->file('image')->store('news_images', 'public');
                Log::info('Image updated for news', ['id' => $news->id, 'image' => $validated['image']]);
            }
            
            $news->update($validated);
            Log::info('News article updated successfully', ['id' => $news->id]);
            return redirect()->route('admin.news.index')->with('success', 'News updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Validation error while updating news', ['id' => $news->id, 'errors' => $e->errors()]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Error updating news article', ['id' => $news->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to update news');
        }
    }

    // ADMIN: Delete news
    public function destroy(News $news)
    {
        try {
            Log::info('Deleting news article', ['id' => $news->id, 'title' => $news->title]);
            
            if ($news->image) {
                Storage::disk('public')->delete($news->image);
                Log::info('Image deleted for news', ['id' => $news->id]);
            }
            
            $news->delete();
            Log::info('News article deleted successfully', ['id' => $news->id]);
            return redirect()->route('admin.news.index')->with('success', 'News deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting news article', ['id' => $news->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to delete news');
        }
    }

    // API: List news for student (JSON) with Correlation ID support
    public function list(Request $request)
    {
        try {
            $correlationId = $this->getOrCreateCorrelationId($request);
            Log::info('API: Fetching news list', ['correlation_id' => $correlationId]);
            
            // For API consumers return JSON, but if the request comes from a browser
            // (Accept: text/html) return the admin view so the endpoint is browser-friendly.
            $news = News::with('creator')->orderByDesc('date')->paginate(10);
            $total = News::count();
            
            // If client expects JSON, return JSON
            if ($request->wantsJson()) {
                $news = News::orderByDesc('date')->get();
                Log::info('API: Returning JSON response', ['count' => $news->count(), 'correlation_id' => $correlationId]);
                return response()->json($news)->header('X-Correlation-ID', $correlationId);
            }
            
            // Otherwise return the admin HTML view (same as admin index)
            Log::info('API: Returning HTML response', ['correlation_id' => $correlationId]);
            return response()->view('admin.news.index', compact('news', 'total'))
                ->header('X-Correlation-ID', $correlationId);
        } catch (\Exception $e) {
            Log::error('API: Error fetching news list', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to fetch news'], 500)
                ->header('X-Correlation-ID', $this->getOrCreateCorrelationId($request));
        }
    }

    // Endpoint: accept session/login notifications from gateway (best-effort)
    public function sessionNotify(Request $request)
    {
        $correlationId = $this->getOrCreateCorrelationId($request);
        Log::info('API: Session notification received', ['correlation_id' => $correlationId, 'payload' => $request->all()]);

        return response()->json(['ok' => true])->header('X-Correlation-ID', $correlationId);
    }

    // API: Like news (requires authenticated user)
    public function like(Request $request, $id)
    {
        try {
            $correlationId = $this->getOrCreateCorrelationId($request);
            Log::info('API: Like news request received', ['news_id' => $id, 'correlation_id' => $correlationId]);
            
            $news = News::findOrFail($id);
            $user = Auth::user();
            
            if (!$user) {
                Log::warning('API: Like attempt without authentication', ['news_id' => $id, 'correlation_id' => $correlationId]);
                return response()->json(['error' => 'Unauthenticated.'], 401)->header('X-Correlation-ID', $correlationId);
            }
            
            if ($news->likedUsers()->where('user_id', $user->id)->exists()) {
                Log::info('API: User already liked this news', ['news_id' => $id, 'user_id' => $user->id, 'correlation_id' => $correlationId]);
                return response()->json(['likes' => $news->likes])->header('X-Correlation-ID', $correlationId);
            }
            
            $news->likedUsers()->attach($user->id);
            $news->increment('likes');
            Log::info('API: News liked successfully', ['news_id' => $id, 'user_id' => $user->id, 'likes' => $news->likes, 'correlation_id' => $correlationId]);
            return response()->json(['likes' => $news->likes])->header('X-Correlation-ID', $correlationId);
        } catch (\Exception $e) {
            Log::error('API: Error liking news', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to like news'], 500)
                ->header('X-Correlation-ID', $this->getOrCreateCorrelationId($request));
        }
    }

    // API: Increment view (tracks Correlation ID)
    public function incrementView(Request $request, $id)
    {
        try {
            $correlationId = $this->getOrCreateCorrelationId($request);
            Log::info('API: Increment view request received', ['news_id' => $id, 'correlation_id' => $correlationId]);
            
            $news = News::findOrFail($id);
            $news->increment('views');
            
            Log::info('API: View incremented successfully', ['news_id' => $id, 'views' => $news->views, 'correlation_id' => $correlationId]);
            return response()->json(['views' => $news->views])->header('X-Correlation-ID', $correlationId);
        } catch (\Exception $e) {
            Log::error('API: Error incrementing view', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to increment view'], 500)
                ->header('X-Correlation-ID', $this->getOrCreateCorrelationId($request));
        }
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