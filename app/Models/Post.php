<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'user_id',
        'type',
        'is_school_closure',
        'is_public',
        'audience',
        'target_grade_id',
        'target_teacher_id',
        'target_classroom_id',
        'title',
        'content',
        'image_path',
        'event_start_date',
        'event_end_date',
        'button_text',
        'button_url',
        'recurrence_type',
        'recurrence_end_date',
        'published_at',
    ];

    protected $casts = [
        'event_start_date' => 'datetime',
        'event_end_date' => 'datetime',
        'recurrence_end_date' => 'date',
        'published_at' => 'datetime',
        'is_school_closure' => 'boolean',
        'is_public' => 'boolean',
    ];

    /**
     * Check if this is a school closure.
     */
    public function isSchoolClosure(): bool
    {
        return (bool) $this->is_school_closure;
    }

    /**
     * Scope to only school closure events.
     */
    public function scopeSchoolClosures(Builder $query): Builder
    {
        return $query->where('is_school_closure', true);
    }

    /**
     * Check if this is a recurring event.
     */
    public function isRecurring(): bool
    {
        return $this->recurrence_type && $this->recurrence_type !== 'none';
    }

    /**
     * Generate all occurrences of a recurring event within a date range.
     */
    public function getOccurrences(\DateTime $rangeStart, \DateTime $rangeEnd): array
    {
        if (!$this->isRecurring() || !$this->event_start_date) {
            return [$this->event_start_date];
        }

        $occurrences = [];
        $current = clone $this->event_start_date;
        $endDate = $this->recurrence_end_date 
            ? min($this->recurrence_end_date, $rangeEnd) 
            : $rangeEnd;

        while ($current <= $endDate) {
            if ($current >= $rangeStart && $current <= $rangeEnd) {
                $occurrences[] = clone $current;
            }

            switch ($this->recurrence_type) {
                case 'daily':
                    $current->modify('+1 day');
                    break;
                case 'weekly':
                    $current->modify('+1 week');
                    break;
                case 'biweekly':
                    $current->modify('+2 weeks');
                    break;
                case 'monthly':
                    $current->modify('+1 month');
                    break;
                default:
                    return $occurrences;
            }
        }

        return $occurrences;
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
                
                // Ensure unique slug
                $originalSlug = $post->slug;
                $count = 1;
                while (static::where('slug', $post->slug)->exists()) {
                    $post->slug = $originalSlug . '-' . $count++;
                }
            }
        });
    }

    /**
     * Get the author of the post.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the target grade for this post (if grade-targeted).
     */
    public function targetGrade(): BelongsTo
    {
        return $this->belongsTo(Grade::class, 'target_grade_id');
    }

    /**
     * Get the target teacher for this post (if teacher-targeted).
     */
    public function targetTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_teacher_id');
    }

    /**
     * Get the target classroom for this post (if classroom-targeted).
     * @deprecated Classrooms are being removed - use targetGrade() instead
     */
    public function targetClassroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'target_classroom_id');
    }

    /**
     * Scope to only published posts.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope to only news posts.
     */
    public function scopeNews(Builder $query): Builder
    {
        return $query->where('type', 'news');
    }

    /**
     * Scope to only event posts.
     */
    public function scopeEvents(Builder $query): Builder
    {
        return $query->where('type', 'event');
    }

    /**
     * Scope to only upcoming events (not past).
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('type', 'event')
            ->where(function ($q) {
                $q->where('event_start_date', '>=', now())
                    ->orWhere('event_end_date', '>=', now());
            });
    }

    /**
     * Scope to posts visible to everyone (public).
     */
    public function scopePublicAudience(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('audience', 'all')
                ->orWhereNull('audience');
        });
    }

    /**
     * Scope to only public events (visible on public website).
     */
    public function scopePublicEvents(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to posts for all teachers (not public).
     */
    public function scopeTeachersOnly(Builder $query): Builder
    {
        return $query->where('audience', 'teachers_only');
    }

    /**
     * Scope to posts for a specific grade's teachers.
     */
    public function scopeForGrade(Builder $query, int $gradeId): Builder
    {
        return $query->where('audience', 'grade_teachers')
            ->where('target_grade_id', $gradeId);
    }

    /**
     * Scope to posts for a specific teacher.
     */
    public function scopeForTeacher(Builder $query, int $teacherId): Builder
    {
        return $query->where('audience', 'specific_teacher')
            ->where('target_teacher_id', $teacherId);
    }

    /**
     * Scope to posts visible to a specific teacher (all applicable posts).
     */
    public function scopeVisibleToTeacher(Builder $query, User $teacher, ?int $gradeId = null): Builder
    {
        return $query->where(function ($q) use ($teacher, $gradeId) {
            // All teachers announcements
            $q->where('audience', 'teachers_only')
                // Or grade-specific (if teacher has a grade)
                ->when($gradeId, function ($q) use ($gradeId) {
                    $q->orWhere(function ($q) use ($gradeId) {
                        $q->where('audience', 'grade_teachers')
                            ->where('target_grade_id', $gradeId);
                    });
                })
                // Or specifically for this teacher
                ->orWhere(function ($q) use ($teacher) {
                    $q->where('audience', 'specific_teacher')
                        ->where('target_teacher_id', $teacher->id);
                });
        });
    }

    /**
     * Scope to posts for a specific classroom.
     * @deprecated Classrooms are being removed - use scopeForGrade() instead
     */
    public function scopeForClassroom(Builder $query, int $classroomId): Builder
    {
        return $query->where('audience', 'classroom')
            ->where('target_classroom_id', $classroomId);
    }

    /**
     * Scope to posts visible to a specific parent (based on their children's grades).
     */
    public function scopeVisibleToParent(Builder $query, User $parent): Builder
    {
        $childrenGradeIds = $parent->children()->pluck('grade_id')->unique()->filter()->toArray();

        return $query->where(function ($q) use ($childrenGradeIds) {
            // All-school posts (audience = 'all' or null)
            $q->where(function ($subQ) {
                $subQ->where('audience', 'all')
                    ->orWhereNull('audience');
            });

            // Grade-level posts for their children's grades
            if (!empty($childrenGradeIds)) {
                $q->orWhere(function ($subQ) use ($childrenGradeIds) {
                    $subQ->where('audience', 'grade')
                        ->whereIn('target_grade_id', $childrenGradeIds);
                });
            }
        });
    }

    /**
     * Check if this post is for teachers only (any staff-only type).
     */
    public function isTeachersOnly(): bool
    {
        return in_array($this->audience, ['teachers_only', 'grade_teachers', 'specific_teacher']);
    }

    /**
     * Check if this post is for a specific classroom.
     * @deprecated Classrooms are being removed
     */
    public function isForClassroom(): bool
    {
        return $this->audience === 'classroom';
    }

    /**
     * Check if this post is for a specific grade.
     */
    public function isForGrade(): bool
    {
        return $this->audience === 'grade_teachers';
    }

    /**
     * Check if this post is for a specific teacher.
     */
    public function isForSpecificTeacher(): bool
    {
        return $this->audience === 'specific_teacher';
    }

    /**
     * Get the target description for display.
     */
    public function getTargetDescription(): string
    {
        switch ($this->audience) {
            case 'teachers_only':
                return 'All Staff';
            case 'grade_teachers':
                return $this->targetGrade?->name . ' Teachers' ?? 'Grade Teachers';
            case 'specific_teacher':
                return $this->targetTeacher?->name ?? 'Specific Teacher';
            default:
                return 'Everyone';
        }
    }

    /**
     * Check if this is a news post.
     */
    public function isNews(): bool
    {
        return $this->type === 'news';
    }

    /**
     * Check if this is an event post.
     */
    public function isEvent(): bool
    {
        return $this->type === 'event';
    }

    /**
     * Check if this event has passed.
     */
    public function isPastEvent(): bool
    {
        if (!$this->isEvent()) {
            return false;
        }

        $endDate = $this->event_end_date ?? $this->event_start_date;
        return $endDate && $endDate->isPast();
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

