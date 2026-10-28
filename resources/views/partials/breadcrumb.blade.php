{{-- $items: [[label, url|null], ...] — Home is added first. --}}
<nav class="breadcrumb {{ $class ?? '' }}" aria-label="{{ __('Breadcrumb') }}">
    <ol>
        <li><a href="{{ route('home') }}">{{ __('Home') }}</a></li>
        @foreach ($items as [$label, $url])
            <li>
                @if ($url && ! $loop->last)
                    <a href="{{ $url }}">{!! $label !!}</a>
                @else
                    <span aria-current="page">{!! $label !!}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
