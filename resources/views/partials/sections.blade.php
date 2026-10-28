{{-- Render an ordered list of page sections. Extra context (e.g. $product) is passed through. --}}
@foreach ($sections as $section)
    @includeIf($section->view, ['section' => $section] + ($context ?? []))
@endforeach
