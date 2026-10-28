{{-- Fallback range page, used only when the 'our-products' page has no sections. --}}
@extends('layouts.app')

@section('title', __('Our products').' | '.brand())

@section('content')
    <div class="container breadcrumb-row">
        @include('partials.breadcrumb', ['items' => [[__('Our products'), null]]])
    </div>

    <section class="container">
        <h1 class="h1-sm">{{ __('Our products') }}</h1>
        <div class="product-grid">
            @foreach ($products as $product)
                @include('partials.product-tile', ['product' => $product, 'headingTag' => 'h2'])
            @endforeach
        </div>
    </section>
@endsection
