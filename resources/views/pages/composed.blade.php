{{-- A page built from page_sections (hubs, range page, about us, Q&A, ...). --}}
@extends('layouts.app')

@section('title', $page ? str_replace('{brand}', brand(), $page->title).' | '.brand() : '')
@section('meta_description', $page ? str_replace('{brand}', brand(), (string) $page->meta_description) : '')
@section('body_class', 'page-'.$pageKey)

@section('content')
    @include('partials.sections', ['sections' => $sections, 'context' => ['page' => $page]])
@endsection
