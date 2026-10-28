{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($entries as $entry)
@foreach ($locales as $locale)
    <url>
        <loc>{{ url($locale.'/'.$entry['path']) }}</loc>
@if ($entry['lastmod'])
        <lastmod>{{ $entry['lastmod'] }}</lastmod>
@endif
@if (count($locales) > 1)
@foreach ($locales as $alternate)
        <xhtml:link rel="alternate" hreflang="{{ str_replace('_', '-', $codes[$alternate] ?? $alternate) }}" href="{{ url($alternate.'/'.$entry['path']) }}"/>
@endforeach
@endif
    </url>
@endforeach
@endforeach
</urlset>
