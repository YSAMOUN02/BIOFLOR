@extends('layouts.app')

@section('title', __('Sitemap').' | '.brand())
@section('hide_newsletter', '1')

@section('content')
    <div class="container breadcrumb-row">
        @include('partials.breadcrumb', ['items' => [[__('Sitemap'), null]]])
    </div>

    <div class="container content-page">
        <h1 class="h1-sm">{{ __('Sitemap') }}</h1>

        <div class="sitemap">
            <div>
                <h2 class="h3"><a href="{{ route('products') }}">{{ __('Our products') }}</a></h2>
                <ul>
                    @foreach ($products as $product)
                        <li><a href="{{ $product->url }}">{!! $product->name !!}</a></li>
                    @endforeach
                </ul>
            </div>

            @foreach (['gut-health' => __('Gut health'), 'our-uniqueness' => __('Our uniqueness'), 'news' => __('Our news')] as $category => $label)
                <div>
                    <h2 class="h3"><a href="{{ route('page', ['slug' => $category === 'news' ? 'our-news' : $category]) }}">{{ $label }}</a></h2>
                    <ul>
                        @foreach ($articles[$category] ?? [] as $article)
                            <li><a href="{{ $article->url }}">{!! $article->title !!}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <div>
                <h2 class="h3">{{ __('Information') }}</h2>
                <ul>
                    <li><a href="{{ route('faq') }}">{{ __('Q&A') }}</a></li>
                    <li><a href="{{ route('contact') }}">{{ __('Contact us') }}</a></li>
                    <li><a href="{{ route('where-to-buy') }}">{{ setting('header.where_to_buy', __('Where to buy')) }}</a></li>
                    @foreach ($pages->whereIn('slug', ['about-us', 'data-protection']) as $page)
                        <li><a href="{{ route('page', ['slug' => $page->slug]) }}">{{ $page->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
