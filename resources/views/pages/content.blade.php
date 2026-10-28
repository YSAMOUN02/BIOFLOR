{{-- Plain content page (legal texts, cookies, accessibility). --}}
@extends('layouts.app')

@section('title', str_replace('{brand}', brand(), $page->title).' | '.brand())
@section('meta_description', str_replace('{brand}', brand(), (string) $page->meta_description))

@section('content')
    <div class="container breadcrumb-row">
        @include('partials.breadcrumb', ['items' => [[$page->title, null]]])
    </div>

    <div class="container content-page">
        <h1 class="h1-sm">{{ str_replace('{brand}', brand(), $page->title) }}</h1>
        @if ($page->subtitle)
            <p class="text-subtitle">{{ str_replace('{brand}', brand(), $page->subtitle) }}</p>
        @endif
        <div class="rich-text content-page__body">
            {!! str_replace('{brand}', brand(), (string) $page->body) !!}
        </div>
    </div>
@endsection
