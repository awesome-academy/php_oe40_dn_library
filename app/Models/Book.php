<?php

namespace App\Models;

use App\Traits\HasImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasImage;

    protected $guarded = [];

    public function getCoverAttribute()
    {
        return $this->image;
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function borrowers()
    {
        return $this->belongsToMany(User::class, 'borrow_requests')
                    ->using(BorrowRequest::class)
                    ->withPivot('from', 'to', 'note', 'status', 'processed_at', 'returned_at')
                    ->withTimestamps();
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function followers()
    {
        return $this->morphMany(Follow::class, 'followable');
    }

    public function reviews()
    {
        return $this->belongsToMany(User::class, 'reviews')
                    ->using(Review::class)
                    ->withPivot('rating', 'comment', 'reviewed_at');
    }

    public function getTitleAttribute($title)
    {
        return Str::title($title);
    }

    public function getAvgRatingAttribute()
    {
        return (int) $this->reviews->avg('pivot.rating');
    }

    public function printAvgRatingText()
    {
        $star = '';

        $avgRating = $this->avg_rating;
        if ($avgRating) {
            $star .= '<span class="text-danger">';
            for ($i = 0; $i < $avgRating; $i++) {
                $star .= '★';
            }
            $star .= '</span>';
        }

        for ($i = 0; $i < (5 - $avgRating); $i++) {
            $star .= '☆';
        }

        return $star;
    }

}
