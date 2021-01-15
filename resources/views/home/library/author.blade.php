<x-app :title="Str::title($author->fullname)">
    @php
        $user = Auth::user();
    @endphp
    <div class="col-9 mx-auto my-5 bg-white border p-4">
        <div class="d-flex mb-5">
            <div class="col-3 mr-3 text-center">
                <a href="{{ route('library.author', $author) }}">
                    <img src="{{ $author->avatar }}" class="img-thumbnail author-avatar">
                </a>
            </div>

            <div class="flex-fill mr-4">
                <div class="d-flex align-items-center">
                    <a href="{{ route('library.author', $author) }}" class="text-reset text-decoration-none h2 text-uppercase mt-1 mb-0 mr-3">
                        {{ $author->fullname }}
                    </a>

                    <div class="fl-button">
                        @include('layouts.home.follow-button', [
                            'followable' => $author,
                        ])
                    </div>

                    <div class="clearfix"></div>
                </div>
                <hr>
                <div class="table-responsive">
                    <table class="table table-borderless table-sm">
                        <tr>
                            <th class="col-2">{{ trans('users.gender') }}</th>
                            <td>{{ $author->gender ? trans('users.female') : trans('users.male') }}</td>
                        </tr>
                        <tr>
                            <th class="col-2">{{ trans('users.birthday') }}</th>
                            <td>{{ date('d-m-Y', strtotime($author->date_of_birth)) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="d-flex">
            <div class="col-3 mr-3">
                <h6 class="text-uppercase font-weight-bold mb-0">
                    {{ trans('library.author-followers', ['fullname' => $author->fullname]) }}
                    ({{ $follows->total() }})
                </h6>
                <hr>
                <div>
                    @foreach ($follows as $follow)
                        <a href="">
                            <img src="{{ $follow->user->avatar() }}" class="mb-2 follower-avatar">
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="flex-fill mr-4">
                <h6 class="text-uppercase font-weight-bold mb-0">
                    {{ trans('library.author-books', ['fullname' => $author->fullname]) }}
                    ({{ $books->total() }})
                </h6>
                <hr>
                @if ($books->count())
                    <x-pagination :data="$books"/>
                @endif

                <div class="row row-cols-1 px-3">
                    @forelse ($books as $book)
                        <div class="card p-0 bg-light">
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
                                            <a href="{{ route('library.author', $author) }}" class="text-reset text-decoration-none text-uppercase">
                                                <small>{{ $author->fullname }}</small>
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
                </div>

                @if ($books->count())
                    <x-pagination :data="$books"/>
                @endif
                <hr>
            </div>
        </div>
    </div>

    @push('page_scripts')
        <script src="{{ mix('js/all.js') }}"></script>
    @endpush
</x-app>
