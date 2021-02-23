<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\Pivot;

class BorrowRequest extends Pivot
{
    use Searchable;

    protected $table = 'borrow_requests';

    public $incrementing = true;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function getStatusTextAttribute()
    {
        $configKey = 'app.borrow-request.status-code';
        switch ($this->status ?? -1) {
            case config("{$configKey}.rejected"):
                return trans('library.borrow.rejected');

            case config("{$configKey}.accepted"):
            case config("{$configKey}.overdue"):
                return now()->toDateString() > $this->to
                        ? trans('library.borrow.overdue')
                        : trans('library.borrow.borrowing');

            case config("{$configKey}.returned"):
                return trans('library.borrow.returned');

            case config("{$configKey}.returned-late"):
                return trans('library.borrow.returned-late');

            default:
                return trans('library.borrow.processing');
        }
    }

    public function getNoteTextAttribute()
    {
        $configKey = 'app.borrow-request.status-code';

        $note = $this->note ?? '';
        $to = new Carbon($this->to);
        $status = $this->status ?? -1;

        if (
            ($status == config("{$configKey}.accepted") || $status == config("{$configKey}.overdue"))
            && $to->isPast()
        ) {
            $note = trans('library.borrow.overdue') .' ('. trans_choice('library.borrow.days-late', $to->diffInDays(now())) .')';
        }

        if ($status == config("{$configKey}.returned-late")) {
            $returnedAt = new Carbon($this->returned_at);
            $note .= ' ('. trans_choice('library.borrow.days-late', $to->diffInDays($returnedAt)) .')';
        }

        return $note;
    }

    public function scopeDefaultSort($query)
    {
        return $query->latest('created_at', 'from', 'to', 'returned_at');
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'user' => optional($this->user)->fullname,
            'book' => optional($this->book)->title,
            'from' => $this->from,
            'to' => $this->to,
        ];
    }

    protected function makeAllSearchableUsing($query)
    {
        return $query->with('user', 'book');
    }

}
