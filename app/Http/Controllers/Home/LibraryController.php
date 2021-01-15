<?php

namespace App\Http\Controllers\Home;

use App\Models\Book;
use App\Models\Like;
use App\Models\Author;
use App\Models\Follow;
use App\Models\Review;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Requests\RateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\BorrowFormRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class LibraryController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth')->except([
            'index',
            'viewBook',
        ]);
    }

    public function index(Request $request)
    {
        $books = Book::with('image', 'author', 'reviews');

        if ($request->filled('category')) {
            $books->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $search = '%'. str_replace(' ', '%', $request->search ?: '') .'%';
            $books->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(title) like ?', $search)
                        ->orWhereRaw('LOWER(description) like ?', $search)
                        ->orWhereHas('author', function (Builder $query) use ($search) {
                            $query->whereRaw('LOWER(first_name) like ?', $search)
                                    ->orWhereRaw('LOWER(last_name) like ?', $search);
                        });
            });
        }

        return view('home.library.index', [
            'categories' => Category::doesntHave('parent')->with('childs')->get(),
            'books' => $books->paginate(config('app.num-rows'))->withQueryString(),
        ]);
    }

    public function viewBook(Book $book)
    {
        return view('home.library.book', [
            'book' => $book,
            'reviews' => $book->reviews()->with('image')->latest('reviewed_at')->paginate(config('app.num-rows')),
        ]);
    }

    public function borrowBook(BorrowFormRequest $request, Book $book)
    {
        Auth::user()->bookBorrowRequests()->attach($book, [
            'from' => $request->from,
            'to' => $request->to,
        ]);

        return back();
    }

    public function borrowHistory()
    {
        return view('home.library.borrow-history', [
            'history' => Auth::user()->bookBorrowRequests()->latest('pivot_created_at', 'from', 'to')->paginate(config('app.num-rows')),
        ]);
    }

    public function toggleLike(Book $book)
    {
        $user = Auth::user();

        $like = Like::of($user, $book);
        if ($like === null) {
            $user->likes()->create([
                'book_id' => $book->id,
            ]);
        } elseif ($like->trashed()) {
            $like->restore();
        } else {
            $like->delete();
        }

        $isLiked = Like::check($user, $book);
        $likeCount = $book->likes()->count();

        return response()->json([
            'likeButton' => view('layouts.home.like-button', [
                'user' => $user,
                'book' => $book,
            ])->render(),
            'likeCount' => $likeCount .' '. trans_choice('library.likes', $likeCount),
        ]);
    }

    public function toggleFollow($followableType, $followableId)
    {
        $user = Auth::user();
        $model = Relation::getMorphedModel($followableType);
        $followable = $model::findOrFail($followableId);

        $follow = Follow::of($user, $followable);
        if ($follow === null) {
            $followable->followers()->save(new Follow([
                'user_id' => $user->id,
            ]));
        } elseif ($follow->trashed()) {
            $follow->restore();
        } else {
            $follow->delete();
        }

        return view('layouts.home.follow-button', [
            'user' => $user,
            'followable' => $followable,
            'btnClasses' => $followable instanceof Book ? 'btn-lg btn-block' : '',
        ])->render();
    }

    public function rateBook(RateRequest $request, Book $book)
    {
        if (Review::hasReview(Auth::user(), $book)) {
            abort(403, trans('general.messages.already-reviewed'));
        }

        $book->reviews()->attach(Auth::id(), [
            'rating' => $request->rating,
            'comment' => $request->comment,
            'reviewed_at' => now(),
        ]);

        $reviewCount = $book->reviews()->count();

        return response()->json([
            'reviewList' => view('layouts.home.reviews', [
                            'reviews' => $book->reviews()->latest('reviewed_at')->paginate(config('app.num-rows')),
                        ])->render(),
            'avgRating' => $book->printAvgRatingText() .' '. $book->avg_rating .' / 5',
            'reviewCount' => $reviewCount .' '. trans_choice('library.reviews', $reviewCount),
        ]);
    }

    public function viewAuthor(Author $author)
    {
        return view('home.library.author', [
            'author' => $author,
            'follows' => $author->followers()->with('user', 'user.image')->latest('followed_at')->paginate(config('app.num-followers')),
            'books' => $author->books()->with('image', 'reviews')->paginate(config('app.num-rows')),
        ]);
    }

}
