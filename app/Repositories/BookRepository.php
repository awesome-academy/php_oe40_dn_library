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
                            ->with('author', 'imageRelation')
                            ->limit($limit)
                            ->get();
    }

    public function search($categoryIds, $search = null)
    {
        $result = $this->model->with('imageRelation', 'author', 'reviews');

        if (count($categoryIds)) {
            $result->whereIn('category_id', $categoryIds);
        }

        if (!empty($search)) {
            $result = $this->model->search($search)->constrain($result);
        }

        return $result->paginate(config('app.num-rows'))->withQueryString();
    }

    public function ofAuthor($authorId)
    {
        return $this->model->where('author_id', $authorId)
                            ->with('imageRelation', 'reviews')
                            ->paginate(config('app.num-rows'), ['*'], 'pageBooks');
    }

}
