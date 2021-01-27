<?php

namespace App\Http\Controllers\Home;

use App\Models\Review;
use Illuminate\Http\Request;
use App\Http\Requests\RateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\BorrowFormRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\LikeRepositoryInterface;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use App\Repositories\Interfaces\FollowRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\BorrowRequestRepositoryInterface;

class LibraryController extends Controller
{

    public function __construct(
        UserRepositoryInterface $userRepo,
        BookRepositoryInterface $bookRepo,
        LikeRepositoryInterface $likeRepo,
        FollowRepositoryInterface $followRepo,
        AuthorRepositoryInterface $authorRepo,
        CategoryRepositoryInterface $categoryRepo,
        BorrowRequestRepositoryInterface $borrowsRepo
    ) {
        $this->middleware('auth')->except([
            'index',
            'viewBook',
            'viewAuthor',
        ]);

        $this->userRepo = $userRepo;
        $this->bookRepo = $bookRepo;
        $this->likeRepo = $likeRepo;
        $this->followRepo = $followRepo;
        $this->authorRepo = $authorRepo;
        $this->borrowsRepo = $borrowsRepo;
        $this->categoryRepo = $categoryRepo;
    }

    public function index(Request $request)
    {
        $subCategories = [];
        if ($request->filled('category')) {
            try {
                $subCategories = $this->categoryRepo->find($request->category)->childArray();
                $subCategories[] = $request->category;
            } catch (ModelNotFoundException $e) {
                return redirect()->route('library.index');
            }
        }

        if ($request->filled('search')) {
            $search = '%'. str_replace(' ', '%', $request->search ?: '') .'%';
        } else {
            $search = null;
        }

        $categories = $this->categoryRepo->getRootCategories();
        $books = $this->bookRepo->search($subCategories, $search);

        return view('home.library.index', compact('categories', 'books'));
    }

    public function viewBook($bookId)
    {
        $book = $this->bookRepo->find($bookId);
        $reviews = $book->reviews()->with('image')->latest('reviewed_at')->paginate(config('app.num-rows'));

        return view('home.library.book', compact('book', 'reviews'));
    }

    public function borrowBook(BorrowFormRequest $request, $bookId)
    {
        $this->bookRepo->find($bookId);
        $this->borrowsRepo->createBorrowRequest(Auth::id(), $bookId, $request->from, $request->to);

        return back();
    }

    public function borrowHistory()
    {
        $history = $this->borrowsRepo->ofUser(Auth::id());

        return view('home.library.borrow-history', compact('history'));
    }

    public function toggleLike($bookId)
    {
        $user = Auth::user();
        $book = $this->bookRepo->find($bookId);

        $this->likeRepo->toggle($user->id, $bookId);

        $likeButton = view('layouts.home.like-button', compact('user', 'book'))->render();
        $likeCount = $book->likes()->count();

        return response()->json([
            'likeButton' => $likeButton,
            'likeCount' => $likeCount .' '. trans_choice('library.likes', $likeCount),
        ]);
    }

    public function toggleFollow($followableType, $followableId)
    {
        $user = Auth::user();
        $btnClasses = $followableType === 'book' ? 'btn-lg btn-block' : '';

        $this->followRepo->toggle($user->id, $followableType, $followableId);

        return view('layouts.home.follow-button', compact('user', 'followableType', 'followableId', 'btnClasses'))->render();
    }

    public function rateBook(RateRequest $request, $bookId)
    {
        $book = $this->bookRepo->find($bookId);

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

    public function viewAuthor($authorId)
    {
        $author = $this->authorRepo->find($authorId);
        $follows = $this->followRepo->getAuthorFollowers($authorId);
        $books = $this->bookRepo->ofAuthor($authorId);

        return view('home.library.author', compact('author', 'follows', 'books'));
    }

    public function viewProfile($userId = null)
    {
        $user = is_null($userId) ? Auth::user() : $this->userRepo->find($userId);

        $likedBooks = $this->likeRepo->getByUserId($user->id);
        $followers = $this->followRepo->getUserFollowers($user->id);

        $followings = [];
        $followableTypes = [
            'user',
            'author',
            'book',
        ];
        foreach($followableTypes as $followableType) {
            $followings[$followableType] = $this->followRepo->getFollowings($user->id, $followableType);
        }

        $reviews = $user->reviews()->with('author', 'image')->latest('reviewed_at')->paginate(config('app.num-rows'));

        return view('home.library.profile', compact('user', 'likedBooks', 'followers', 'followings', 'reviews'));
    }

}
