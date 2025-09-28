@extends('layouts.full')
@section('title', __('Downloads'))

@section('content')
    <div class="container">
        <div class="row">
            <h2 class="my-4">{{ __('Download Client') }}</h2>

            @forelse($data as $value)
                <div class="col-md-3 mb-4">
                    <div class="card">
                        @if ($value->image)
                            <img src="{{ $value->image }}" class="card-img-top object-fit-contain p-3" width="" height="100" alt="...">
                        @endif
                        <div class="card-body text-center">
                            <h5 class="card-title">{{ $value->name }}</h5>
                            <p class="card-text">{{ $value->desc }}</p>

                            <div class="d-grid mx-auto">
                                <a href="{{ $value->url }}" target="_blank" class="btn btn-primary">{{ __('Download') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="alert alert-danger text-center" role="alert">
                    {{ __('No Downloads Available!') }}
                </div>
            @endforelse
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row">
                    <h2 class="my-4">{{ __('System Requirements') }}</h2>

                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table-dark">
                            <tr>
                                <th scope="col">{{ __('Category') }}</th>
                                <th scope="col">{{ __('Minimum Requirements') }}</th>
                                <th scope="col">{{ __('Recommended Requirements') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ __('CPU') }}</td>
                                <td>{{ __('Pentium 3 800MHz or higher') }}</td>
                                <td>{{ __('Intel i3 or higher') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('RAM') }}</td>
                                <td>{{ __('2GB') }}</td>
                                <td>{{ __('4GB') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('VGA') }}</td>
                                <td>{{ __('3D speed over GeForce2 or ATI 9000') }}</td>
                                <td>{{ __('3D speed over GeForce FX 5600 or ATI9500') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('SOUND') }}</td>
                                <td>{{ __('DirectX 9.0c Compatibility card') }}</td>
                                <td>{{ __('DirectX 9.0c Compatibility card') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('HDD') }}</td>
                                <td>{{ __('5GB or higher(including swap and temporary file)') }}</td>
                                <td>{{ __('8GB or higher(including swap and temporary file)') }}</td>
                            </tr>
                            <tr>
                                <td>{{ __('OS') }}</td>
                                <td>{{ __('Windows 7') }}</td>
                                <td>{{ __('Windows 10') }}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
