<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="uploads-base" content="{{ asset('uploads') }}/">
    <meta name="asset-base" content="{{ asset('') }}">
    <title>@yield('title', 'Admin') &middot; {{ brand() }}</title>
    <link rel="icon" href="{{ asset('img/favicon.svg') }}" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600;700;800&display=swap">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
@php
    $nav = [
        'Content' => [
            ['label' => 'Dashboard',     'route' => 'admin.dashboard'],
            ['label' => 'Launch checklist', 'route' => 'admin.checklist'],
            ['label' => 'Page sections', 'route' => 'admin.sections.index'],
            ['label' => 'Articles',      'route' => 'admin.articles.index'],
            ['label' => 'Content pages', 'route' => 'admin.pages.index'],
            ['label' => 'Q&A',           'route' => 'admin.faqs.index'],
            ['label' => 'Media library', 'route' => 'admin.media.index'],
        ],
        'Catalogue' => [
            ['label' => 'Products',         'route' => 'admin.products.index'],
            ['label' => 'Product Q&A',      'route' => 'admin.product-faqs.index'],
            ['label' => 'Categories',       'route' => 'admin.categories.index'],
            ['label' => 'Attributes',       'route' => 'admin.attributes.index'],
            ['label' => 'Attribute groups', 'route' => 'admin.attribute-groups.index'],
            ['label' => 'Product types',    'route' => 'admin.attribute-sets.index'],
            ['label' => 'Import products',  'route' => 'admin.import.index'],
        ],
        'Site' => [
            ['label' => 'Settings',     'route' => 'admin.settings.index'],
            ['label' => 'Navigation',   'route' => 'admin.nav.index'],
            ['label' => 'Where to buy', 'route' => 'admin.retailers.index'],
            ['label' => 'Reviews',      'route' => 'admin.reviews.index'],
        ],
        'Inbox' => [
            ['label' => 'Subscribers', 'route' => 'admin.subscribers'],
            ['label' => 'Messages',    'route' => 'admin.messages'],
        ],
    ];
@endphp

<div class="shell">
    <aside class="sidebar">
        <a class="sidebar__brand" href="{{ route('admin.dashboard') }}">
            <img src="{{ asset('img/favicon.svg') }}" alt="">
            <span>Admin</span>
        </a>

        <nav aria-label="Admin navigation">
            @foreach ($nav as $group => $items)
                <p class="sidebar__group-title">{{ $group }}</p>
                <ul>
                    @foreach ($items as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               @if (request()->routeIs(str_replace('.index', '.*', $item['route']))) aria-current="page" @endif>
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </nav>

        <div class="sidebar__foot">
            <a href="{{ route('home') }}" target="_blank" rel="noopener">View site &rarr;</a>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="btn btn--ghost btn--sm" type="submit">Sign out</button>
            </form>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <span class="topbar__title">@yield('title', 'Admin')</span>
            <span class="topbar__spacer"></span>
            <a class="topbar__user" href="{{ route('admin.account') }}" title="Change password">{{ auth()->user()?->email }}</a>
        </header>

        <div class="content">
            @if (session('status'))
                <p class="alert alert--success" role="status">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <p class="alert alert--error" role="alert">
                    {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('problem', $errors->count()) }} to fix below.
                </p>
            @endif

            @yield('content')
        </div>
    </div>
</div>
<script src="{{ asset('js/admin.js') }}?v={{ filemtime(public_path('js/admin.js')) }}" defer></script>
</body>
</html>
