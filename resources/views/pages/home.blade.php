@extends('layouts.app')

@section('body_class', 'page-home')

@section('content')
    @if ($sections->isNotEmpty())
        @include('partials.sections', ['sections' => $sections])
    @else
        <div class="container rich-text-block">
            <h2>{{ __('No content yet') }}</h2>
            <p class="text-intro">Run <code>php artisan db:seed</code> to load the home page sections.</p>
        </div>
    @endif
@endsection
