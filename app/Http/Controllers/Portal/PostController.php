<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Models\Post;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Display a listing of posts.
     */
    public function index(Request $request): Response
    {
        $posts = Post::with(['author', 'targetGrade', 'targetTeacher'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Portal/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['search', 'type']),
        ]);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(Request $request): Response
    {
        $grades = Grade::orderBy('sort_order')->get(['id', 'name']);
        $teachers = User::where('role', User::ROLE_TEACHER)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Portal/Posts/Create', [
            'grades' => $grades,
            'teachers' => $teachers,
            'initialType' => $request->query('type') === 'event' ? 'event' : 'news',
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $this->normalizePostInput($request);

        $validated = $request->validate([
            'type' => 'required|in:news,event',
            'is_school_closure' => 'boolean',
            'is_public' => 'boolean',
            'audience' => 'nullable|in:all,teachers_only,grade_teachers,specific_teacher',
            'target_grade_id' => 'nullable|exists:grades,id',
            'target_teacher_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'event_start_date' => 'required_if:type,event|nullable|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|url|max:500',
            'recurrence_type' => 'nullable|in:none,daily,weekly,biweekly,monthly',
            'recurrence_end_date' => 'nullable|date|after:event_start_date',
            'publish_now' => 'boolean',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        // Determine target fields based on audience
        $targetGradeId = null;
        $targetTeacherId = null;
        
        $audience = $validated['audience'] ?? 'all';

        if ($audience === 'grade_teachers' && !empty($validated['target_grade_id'])) {
            $targetGradeId = $validated['target_grade_id'];
        } elseif ($audience === 'specific_teacher' && !empty($validated['target_teacher_id'])) {
            $targetTeacherId = $validated['target_teacher_id'];
        }

        // For school closures, force audience to 'all'
        if ($request->boolean('is_school_closure')) {
            $audience = 'all';
            $targetGradeId = null;
            $targetTeacherId = null;
        }

        // Parse datetime-local input as Eastern Time and store as-is (app timezone is America/New_York)
        // so Laravel persists and reads the same wall-clock time without misinterpretation
        $eventStartDate = null;
        $eventEndDate = null;
        if (!empty($validated['event_start_date'])) {
            $eventStartDate = Carbon::parse($validated['event_start_date'], 'America/New_York');
        }
        if (!empty($validated['event_end_date'])) {
            $eventEndDate = Carbon::parse($validated['event_end_date'], 'America/New_York');
        }

        $post = Post::create([
            'user_id' => $request->user()->id,
            'type' => $validated['type'],
            'is_school_closure' => $request->boolean('is_school_closure'),
            'is_public' => $request->boolean('is_public', false),
            'audience' => $audience,
            'target_grade_id' => $targetGradeId,
            'target_teacher_id' => $targetTeacherId,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'event_start_date' => $eventStartDate,
            'event_end_date' => $eventEndDate,
            'button_text' => $validated['button_text'] ?? null,
            'button_url' => $validated['button_url'] ?? null,
            'recurrence_type' => $validated['recurrence_type'] ?? 'none',
            'recurrence_end_date' => $validated['recurrence_end_date'] ?? null,
            // Store in app timezone so all `published_at <= now()` queries match correctly.
            'published_at' => $request->boolean('publish_now') ? now() : null,
        ]);

        return redirect()->route('portal.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post): Response
    {
        $grades = Grade::orderBy('sort_order')->get(['id', 'name']);
        $teachers = User::where('role', User::ROLE_TEACHER)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Portal/Posts/Edit', [
            'post' => $post->load(['targetGrade', 'targetTeacher']),
            'grades' => $grades,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post)
    {
        $this->normalizePostInput($request);

        $validated = $request->validate([
            'type' => 'required|in:news,event',
            'is_school_closure' => 'boolean',
            'is_public' => 'boolean',
            'audience' => 'nullable|in:all,teachers_only,grade_teachers,specific_teacher',
            'target_grade_id' => 'nullable|exists:grades,id',
            'target_teacher_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'boolean',
            'event_start_date' => 'required_if:type,event|nullable|date',
            'event_end_date' => 'nullable|date|after_or_equal:event_start_date',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|url|max:500',
            'recurrence_type' => 'nullable|in:none,daily,weekly,biweekly,monthly',
            'recurrence_end_date' => 'nullable|date|after:event_start_date',
        ]);

        // Handle image
        $imagePath = $post->image_path;
        
        if ($request->boolean('remove_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }
        
        if ($request->hasFile('image')) {
            // Delete old image
            if ($post->image_path) {
                Storage::disk('public')->delete($post->image_path);
            }
            $imagePath = $request->file('image')->store('posts', 'public');
        }

        // Determine target fields based on audience
        $targetGradeId = null;
        $targetTeacherId = null;
        
        $audience = $validated['audience'] ?? 'all';

        if ($audience === 'grade_teachers' && !empty($validated['target_grade_id'])) {
            $targetGradeId = $validated['target_grade_id'];
        } elseif ($audience === 'specific_teacher' && !empty($validated['target_teacher_id'])) {
            $targetTeacherId = $validated['target_teacher_id'];
        }

        // For school closures, force audience to 'all'
        if ($request->boolean('is_school_closure')) {
            $audience = 'all';
            $targetGradeId = null;
            $targetTeacherId = null;
        }

        // Parse datetime-local input as Eastern Time and store as-is (app timezone is America/New_York)
        $eventStartDate = null;
        $eventEndDate = null;
        if (!empty($validated['event_start_date'])) {
            $eventStartDate = Carbon::parse($validated['event_start_date'], 'America/New_York');
        }
        if (!empty($validated['event_end_date'])) {
            $eventEndDate = Carbon::parse($validated['event_end_date'], 'America/New_York');
        }

        $post->update([
            'type' => $validated['type'],
            'is_school_closure' => $request->boolean('is_school_closure'),
            'is_public' => $request->boolean('is_public', false),
            'audience' => $audience,
            'target_grade_id' => $targetGradeId,
            'target_teacher_id' => $targetTeacherId,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $imagePath,
            'event_start_date' => $eventStartDate,
            'event_end_date' => $eventEndDate,
            'button_text' => $validated['button_text'] ?? null,
            'button_url' => $validated['button_url'] ?? null,
            'recurrence_type' => $validated['recurrence_type'] ?? 'none',
            'recurrence_end_date' => $validated['recurrence_end_date'] ?? null,
            // Don't update published_at on edit - preserve existing value
            // Use togglePublish action to change publish status
        ]);

        return redirect()->route('portal.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Upload an image for use inside post content (WYSIWYG).
     */
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
        ]);

        $path = $request->file('image')->store('posts/inline', 'public');
        $url = asset('storage/' . $path);

        return response()->json(['url' => $url]);
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post)
    {
        // Delete associated image
        if ($post->image_path) {
            Storage::disk('public')->delete($post->image_path);
        }

        $post->delete();

        return redirect()->route('portal.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Toggle publish status.
     */
    public function togglePublish(Post $post)
    {
        $post->update([
            // Store in app timezone so all `published_at <= now()` queries match correctly.
            'published_at' => $post->published_at ? null : now(),
        ]);

        $status = $post->published_at ? 'published' : 'unpublished';

        return back()->with('success', "Post {$status} successfully.");
    }

    /**
     * Turn blank FormData placeholders into null before validation.
     * Nullable ids, dates, and URLs otherwise fail when submitted as "" or "null".
     */
    private function normalizePostInput(Request $request): void
    {
        $blank = ['', 'null', 'undefined'];
        $merged = [];

        foreach ([
            'audience',
            'target_grade_id',
            'target_teacher_id',
            'event_start_date',
            'event_end_date',
            'button_text',
            'button_url',
            'recurrence_type',
            'recurrence_end_date',
        ] as $field) {
            if (! $request->exists($field)) {
                continue;
            }

            $value = $request->input($field);
            if (is_string($value)) {
                $value = trim($value);
            }

            if ($value === null || in_array($value, $blank, true)) {
                $merged[$field] = null;
            } elseif (is_string($value)) {
                $merged[$field] = $value;
            }
        }

        if (! $request->hasFile('image')) {
            $image = $request->input('image');
            $image = is_string($image) ? trim($image) : $image;
            if ($image === null || in_array($image, $blank, true)) {
                $merged['image'] = null;
            }
        }

        if ($merged !== []) {
            $request->merge($merged);
        }
    }
}

