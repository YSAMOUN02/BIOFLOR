@extends('admin.layout')

@section('title', $title)

@section('content')
    @php $isEdit = $record->exists; @endphp

    <div class="card">
        <div class="card__head">
            <h2>{{ $title }}</h2>
            <span class="card__spacer"></span>
            <a class="btn btn--ghost btn--sm" href="{{ route($routeName.'.index') }}">Back to list</a>
        </div>

        <div class="card__body">
            <form method="post"
                  action="{{ $isEdit ? route($routeName.'.update', $record->getKey()) : route($routeName.'.store') }}"
                  class="form-grid"
                  @if (! empty($hasUploads)) enctype="multipart/form-data" @endif>
                @csrf
                @if ($isEdit) @method('PUT') @endif

                @foreach ($fields as $name => $field)
                    @include('admin.crud.field', ['name' => $name, 'field' => $field, 'record' => $record])
                @endforeach

                @include('admin.crud.translations')

                <div class="form-actions">
                    <button class="btn btn--primary" type="submit">{{ $isEdit ? 'Save changes' : 'Create' }}</button>
                    <a class="btn btn--ghost" href="{{ route($routeName.'.index') }}">Cancel</a>
                    <span class="card__spacer"></span>

                    @if ($isEdit && method_exists($record, 'getUrlAttribute'))
                        <a class="btn btn--ghost" href="{{ $record->url }}" target="_blank" rel="noopener">View on site &rarr;</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
