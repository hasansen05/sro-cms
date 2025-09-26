@extends('layouts.full')
@section('title', __('News'))

@section('content')
    <div class="container">
        <div class="row g-4">
            @forelse($data as $value)
                <div class="col-lg-4">
                    <div class="card h-100">
                        @if ($value->image)
                            <img src="{{ $value->image }}" class="card-img-top" alt="..." style="height: 200px;">
                        @else
                            <div class="bg-secondary" style="height: 200px;">
                                <div class="h-100 d-flex align-items-center justify-content-center text-white">
                                    [News Image Placeholder]
                                </div>
                            </div>
                        @endif
                        <div class="card-body">
                            <div class="small mb-2 font-cinzel">
                                @switch($value->category)
                                    @case('news')
                                        <span class="badge text-bg-warning">News</span>
                                        @break
                                    @case('update')
                                        <span class="badge text-bg-primary">Update</span>
                                        @break
                                    @case('event')
                                        <span class="badge text-bg-success">Event</span>
                                        @break
                                    @default
                                        <span class="badge text-bg-warning">News</span>
                                @endswitch
                                {{ $value->published_at->format("M j, Y") }}</div>
                            <a href="{{ route('pages.post.show', ['slug' => $value->slug]) }}" class="text-decoration-none">
                                <h3 class="card-title fw-bold font-cinzel h5">{{ \Illuminate\Support\Str::words(strip_tags($value->title), 3, '...') }}</h3>
                            </a>
                            <div class="card-text">
                                {{ \Illuminate\Support\Str::words(strip_tags($value->content), 20, '...') }}
                            </div>
                            <a href="{{ route('pages.post.show', ['slug' => $value->slug]) }}" class="text-decoration-none font-cinzel mt-4">
                                Read More →
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-danger text-center" role="alert">
                    {{ __('No Posts Available!') }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
