@extends('layouts.full')
@section('title', __('Ranking'))

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div class="d-block text-center my-4">
                    @foreach($config as $value)
                        @if($value['enabled'])
                            <button class="btn btn-primary me-1 mb-2 {{ $value['route'] === 'ranking.player' ? 'active' : '' }}" data-link="{{ is_array($value['route'])? route($value['route']['name'], $value['route']['params'] ?? []): route($value['route']) }}">{{ __($value['name']) }}</button>
                        @endif
                    @endforeach
                </div>
                <div id="content-ranking">
                    @if($type == 'guild')
                        @include('ranking.ranking.guild')
                    @else
                        @include('ranking.ranking.player')
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).ready(function () {
            $('[data-link]').on('click', function (e) {
                e.preventDefault();

                let link = $(this).data('link');
                if (location.protocol === 'https:' && link.startsWith('http:')) {
                    link = link.replace(/^http:/, 'https:');
                }

                $('[data-link]').removeClass('active');
                $(this).addClass('active');

                $('#content-ranking').html(`
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                </div>
                `);

                $.get(link, function (res) {
                    $('#content-ranking').html(res);
                }).fail(function () {
                    $('#content-ranking').html('<div class="alert alert-danger">Failed to load Ranking.</div>');
                });
            });
        });
    </script>
@endpush
