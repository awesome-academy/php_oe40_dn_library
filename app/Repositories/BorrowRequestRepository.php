<?php

namespace App\Repositories;

use App\Models\BorrowRequest;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Interfaces\BorrowRequestRepositoryInterface;

class BorrowRequestRepository extends BaseRepository implements BorrowRequestRepositoryInterface
{

    public function getModel()
    {
        return BorrowRequest::class;
    }

    public function createBorrowRequest($userId, $bookId, $from, $to)
    {
        $this->model->create([
            'user_id' => $userId,
            'book_id' => $bookId,
            'from' => $from,
            'to' => $to,
        ]);
    }

    public function getLatestProcessing($userId, $bookId)
    {
        return $this->model->where('user_id', $userId)
                            ->where('book_id', $bookId)
                            ->where('status', config('app.borrow-request.status-code.new'))
                            ->latest()
                            ->first();
    }

    public function getCurrentBorrowing($userId, $bookId)
    {
        return $this->model->where('user_id', $userId)
                            ->where('book_id', $bookId)
                            ->whereIn('status', [
                                config('app.borrow-request.status-code.accepted'),
                                config('app.borrow-request.status-code.overdue'),
                            ])
                            ->where('returned_at', null)
                            ->latest()
                            ->first();
    }

    public function ofUser($userId)
    {
        return $this->model->with('book')
                            ->where('user_id', $userId)
                            ->defaultSort()
                            ->paginate(config('app.num-rows'));
    }

    public function search($search, $filter, $withPaginator = true)
    {
        $configKey = 'app.borrow-request.status-code';
        $result = $this->model->with('user', 'book');

        if (is_null($filter) || $filter === '') {
            $filter = config("{$configKey}.new");
        }

        switch ($filter) {
            case 'all':
                break;

            case config("{$configKey}.new"):
            case config("{$configKey}.accepted"):
            case config("{$configKey}.rejected"):
            case config("{$configKey}.returned"):
            case config("{$configKey}.returned-late"):
                $result->where('status', $filter);
                break;

            case config("{$configKey}.overdue"):
                $result->whereIn('status', [
                            config("{$configKey}.accepted"),
                            config("{$configKey}.overdue"),
                        ])
                        ->where('returned_at', null)
                        ->where('to', '<', date('Y-m-d'));
                break;
        }

        $result->defaultSort();

        if (!empty($search)) {
            $result = $this->model->search($search)->constrain($result);
        }

        return $withPaginator
                ? $result->paginate(config('app.num-rows'))
                            ->withQueryString()
                : $result->get();
    }

    public function updateBorrowRequest($borrowRequestId, $action)
    {
        $configKey = 'app.borrow-request.status-code';
        $borrowRequest = $this->find($borrowRequestId);
        $bookAmount = $borrowRequest->book->amount;

        $attributes = [];
        switch ($action) {
            case 'accept':
                if ($bookAmount < 1) {
                    abort(403);
                }

                $attributes['status'] = config("{$configKey}.accepted");
                $attributes['processed_at'] = now();
                $borrowRequest->book->amount = --$bookAmount;
                break;
            case 'reject':
                if ($bookAmount < 1) {
                    $attributes['note'] = trans('books.out-of-stock');
                }

                $attributes['status'] = config("{$configKey}.rejected");
                $attributes['processed_at'] = now();
                break;
            case 'return':
                $attributes['status'] = date('Y-m-d') > $borrowRequest->to
                                ? config("{$configKey}.returned-late")
                                : config("{$configKey}.returned");
                $attributes['returned_at'] = now();
                $borrowRequest->book->amount = ++$bookAmount;
                break;
            default:
                return;
        }

        if ($borrowRequest->status !== $attributes['status']) {
            $this->update($borrowRequestId, $attributes);
            $borrowRequest->push();
        }
    }

    public function whereDate($date, $field = 'created_at')
    {
        return $this->model->whereDate($field, $date)->get();
    }

    public function whereMonth($month, $field = 'created_at')
    {
        return $this->model->whereMonth($field, $month)->get();
    }

    public function whereYear($year, $field = 'created_at')
    {
        return $this->model->whereYear($field, $year)->get();
    }

}
