<x-app :title="trans('app.books')">
    <div class="col-9 mx-auto my-5 bg-white border p-0 pt-4">
        @php
            $search = Request::input('search') ?: null;
            $page = Request::input('page') ?: null;
        @endphp
        <div class="col-3 float-left">
            <ul class="list-group list-group-flush">
                <a href="{{ route('library.index') }}" class="list-group-item h6 font-weight-bold text-uppercase">
                    {{ trans('library.all-books') }}
                </a>
                @foreach ($categories as $category)
                    @php
                        $href = route('library.index', [
                            'category' => $category->id,
                            'search' => $search,
                        ]);
                    @endphp
                    <a href="{{ $href }}" class="list-group-item h6 font-weight-bold text-uppercase">
                        {{ $category->name }}
                    </a>
                    @if (count($category->childs))
                        <li class="list-group-item">
                            <ul class="list-group list-group-flush">
                                @include('layouts.home.subcategories', [
                                    'categories' => $category->childs()->with('childs')->get(),
                                ])
                            </ul>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>

        <div class="col-9 float-right">
            <form action="{{ route('library.index') }}" method="GET" class="d-flex">
                @if (Request::filled('category'))
                    <input type="text" name="category" value="{{ Request::input('category') }}" hidden>
                @endif
                <div class="input-group flex-fill">
                    <input type="text" class="form-control" placeholder="{{ trans('library.search-placeholder') }}" aria-describedby="btn-search" name="search" value="{{ $search }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit" id="btn-search">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
            <div class="row row-cols-1 my-3 p-3">
                @forelse ($books as $book)
                    <div class="card p-0">
                        <div class="row no-gutters">
                            <div class="col-4">
                                <a href="{{ route('library.book', $book) }}">
                                    <img src="{{ $book->cover }}" class="card-img-top">
                                </a>
                            </div>
                            <div class="col-8">
                                <div class="card-body">
                                    <h5 class="card-title mb-2">
                                        <a href="{{ route('library.book', $book) }}" class="h4 font-weight-bold text-reset text-decoration-none">
                                            {{ Str::title($book->title) }}
                                        </a>
                                        <br />
                                        <a href="{{ route('library.author', $book->author) }}" class="text-reset text-decoration-none text-uppercase">
                                            <small>{{ $book->author->fullname }}</small>
                                        </a>
                                        <br />
                                        <small>{!! $book->printAvgRatingText() !!}</small>
                                    </h5>
                                    <p class="card-text text-justify">{{ $book->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <span class="col-12 h5 text-center text-uppercase">{{ trans('general.no-result') }}</span>
                @endforelse

                {{ $books->links('vendor.pagination.bootstrap-4') }}
            </div>
        </div>

        <div class="clearfix"></div>
    </div>
</x-app>
