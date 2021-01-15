<x-app>
    @if ($randomBooks->count())
        <div class="col-9 mx-auto my-5">
            <div class="mb-3 d-flex">
                <h2 class="col-2 text-break m-0">{{ trans('home.random-books') }}</h2>
                <div class="align-self-end ml-auto">
                    <a class="text-uppercase" href="{{ route('library.index') }}">{{ trans('home.browse-books') }}</a>
                </div>
            </div>
            <div class="card-deck row-cols-4">
                @foreach ($randomBooks as $book)
                    <div class="card text-center">
                        <a href="{{ route('library.book', $book) }}">
                            <img src="{{ $book->cover }}" class="card-img-top">
                        </a>
                        <div class="card-body">
                            <p class="card-text">
                                <p class="text-muted">
                                    <a href="{{ route('library.author', $book->author) }}" class="text-reset text-decoration-none">
                                        {{ Str::title($book->author->fullname) }}
                                    </a>
                                </p>
                                <a href="{{ route('library.book', $book) }}" class="h5 font-weight-bold text-reset text-decoration-none">
                                    {{ Str::title($book->title) }}
                                </a>
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($randomAuthors->count())
        <div class="col-9 mx-auto my-5">
            <div class="mb-3 d-flex">
                <h2 class="col-2 text-break m-0">{{ trans('home.random-authors') }}</h2>
                <div class="align-self-end ml-auto"><a class="text-uppercase" href="">{{ trans('home.browse-authors') }}</a></div>
            </div>
            <div class="card-deck row-cols-4">
                @foreach ($randomAuthors as $author)
                    <div class="card text-center">
                        <div class="card-body">
                            <p class="card-text text-uppercase font-weight-bold">
                                {{ Str::title($author->fullname) }}
                            </p>
                        </div>
                        <img src="{{ $author->avatar }}" class="card-img-bottom" alt="Title">

                        <a href="{{ route('library.author', $author) }}" class="stretched-link"></a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-app>
