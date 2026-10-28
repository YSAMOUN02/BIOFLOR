@php
    // Wrap the product line ("250mg Sachets") onto at most two lines.
    $lines = explode("\n", wordwrap($headline, 16, "\n", true));
    $lines = array_slice($lines, 0, 2);
    $bottom = trim($pack ?: $form);
@endphp
<svg xmlns="http://www.w3.org/2000/svg" width="500" height="400" viewBox="0 0 500 400" role="img" aria-label="{{ $brand }} {{ $headline }}">
    <defs>
        <linearGradient id="band" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="{{ $accent }}" stop-opacity=".55"/>
            <stop offset="1" stop-color="{{ $accent }}"/>
        </linearGradient>
    </defs>
    <ellipse cx="262" cy="362" rx="150" ry="14" fill="#001660" opacity=".08"/>
    {{-- carton: top, side, front --}}
    <path d="M140 92 L198 62 L388 62 L330 92 Z" fill="#f4f6fb" stroke="#d5dbe8"/>
    <path d="M330 92 L388 62 L388 322 L330 352 Z" fill="#e6eaf3" stroke="#d5dbe8"/>
    <rect x="140" y="92" width="190" height="260" fill="#ffffff" stroke="#d5dbe8"/>
    <path d="M140 300 L330 262 L330 352 L140 352 Z" fill="url(#band)"/>
    <path d="M330 262 L388 236 L388 322 L330 352 Z" fill="{{ $accent }}" opacity=".75"/>
    <text x="156" y="148" font-family="Impact, 'Arial Narrow Bold', 'Arial Black', Arial, sans-serif" font-size="40" fill="#1d3f9a" letter-spacing="1">{{ $brand }}<tspan font-size="14" dy="-18">®</tspan></text>
    @foreach ($lines as $i => $line)
        <text x="156" y="{{ 182 + $i * 28 }}" font-family="Arial, Helvetica, sans-serif" font-size="22" font-weight="700" fill="{{ $accent }}">{{ $line }}</text>
    @endforeach
    <text x="156" y="{{ 182 + count($lines) * 28 + 4 }}" font-family="Arial, Helvetica, sans-serif" font-size="11" font-style="italic" fill="#001660">{{ $strain }}</text>
    @if ($bottom !== '')
        <text x="156" y="336" font-family="Arial, Helvetica, sans-serif" font-size="16" font-weight="700" fill="#ffffff">{{ mb_strtoupper($bottom) }}</text>
    @endif
</svg>
