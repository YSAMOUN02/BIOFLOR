@include('partials.references', [
    'id'   => $section->uid('body'),
    'body' => $section->get('body'),
    'show' => $section->get('label_show'),
    'hide' => $section->get('label_hide'),
])
