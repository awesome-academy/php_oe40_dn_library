<?php

namespace App\Repositories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\Interfaces\BookRepositoryInterface;

class BookRepository extends BaseRepository implements BookRepositoryInterface
{

    public function getModel()
    {
        return Book::class;
    }

    public function getRandomBooks($limit = null)
    {
        $limit = $limit ?? config('app.random-items');

        return $this->model->inRandomOrder()
                            ->with('author', 'image')
                            ->limit($limit)
                            ->get();
    }

    public function search($categoryIds, $search = null)
    {
        $result = $this->model->with('image', 'author', 'reviews');

        if (count($categoryIds)) {
            $result->whereIn('category_id', $categoryIds);
        }

        if (!empty($search)) {
            $result->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(title) like ?', $search)
                        ->orWhereRaw('LOWER(description) like ?', $search)
                        ->orWhereHas('author', function (Builder $query) use ($search) {
                            $query->whereRaw('LOWER(first_name) like ?', $search)
                                    ->orWhereRaw('LOWER(last_name) like ?', $search);
                        })
                        ->orWhereHas('publisher', function (Builder $query) use ($search) {
                            $query->whereRaw('LOWER(name) like ?', $search);
                        });
            });
        }

        return $result->paginate(config('app.num-rows'))->withQueryString();
    }

    public function ofAuthor($authorId)
    {
        return $this->model->where('author_id', $authorId)
                            ->with('image', 'reviews')
                            ->paginate(config('app.num-rows'), ['*'], 'pageBooks');
    }

}
