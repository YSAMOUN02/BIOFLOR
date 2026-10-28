<div class="container breadcrumb-row">
    @include('partials.breadcrumb', ['items' => [[$section->get('title', $page->title ?? ''), null]]])
</div>
