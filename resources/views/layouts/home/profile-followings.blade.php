<div class="tab-pane fade show active" id="following-users" role="tabpanel" aria-labelledby="following-users-tab">
    <div class="row row-cols-6">
        @foreach ($followings['user'] as $follow)
            <figure class="figure col mt-1 p-0 text-center">
                <a href="{{ route('library.profile', $follow->followable) }}">
                    <img src="{{ $follow->followable->avatar }}" class="figure-img img-fluid img-thumbnail" width="64" height="64">
                    <figcaption class="figure-caption">
                        {{ $follow->followable->fullname }}
                    </figcaption>
                </a>
            </figure>
        @endforeach
    </div>
</div>

<div class="tab-pane fade" id="following-authors" role="tabpanel" aria-labelledby="following-authors-tab">
    <div class="row row-cols-5">
        @foreach ($followings['author'] as $follow)
            <figure class="figure col mt-1 p-0 text-center">
                <a href="{{ route('library.author', $follow->followable) }}">
                    <img src="{{ $follow->followable->avatar }}" class="figure-img img-fluid img-thumbnail" width="256" height="256">
                    <figcaption class="figure-caption">
                        {{  $follow->followable->fullname }}
                    </figcaption>
                </a>
            </figure>
        @endforeach
    </div>
</div>

<div class="tab-pane fade" id="following-books" role="tabpanel" aria-labelledby="following-books-tab">
    <div class="row row-cols-5">
        @foreach ($followings['book'] as $follow)
            <figure class="figure col mt-1 p-0 text-center">
                <a href="{{ route('library.book', $follow->followable) }}">
                    <img src="{{ $follow->followable->cover }}" class="figure-img img-fluid img-thumbnail book-cover">
                    <figcaption class="figure-caption">
                        {{ $follow->followable->title }}
                    </figcaption>
                </a>
            </figure>
        @endforeach
    </div>
</div>
