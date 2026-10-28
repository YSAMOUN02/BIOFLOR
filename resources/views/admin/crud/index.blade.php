@extends('admin.layout')

@section('title', $title)

@section('content')
    <div class="card">
        <div class="card__head">
            <h2>{{ $title }}</h2>
            <span class="pill pill--off">{{ $records->total() }}</span>
            <span class="card__spacer"></span>

            <form class="search" method="get" action="{{ route($routeName.'.index') }}">
                <label class="sr-only" for="q" style="position:absolute;left:-9999px">Search</label>
                <input type="text" id="q" name="q" value="{{ $q }}" placeholder="Search…">
                <button class="btn btn--ghost btn--sm" type="submit">Search</button>
                @if ($q)
                    <a class="btn btn--ghost btn--sm" href="{{ route($routeName.'.index') }}">Clear</a>
                @endif
            </form>

            <a class="btn btn--primary btn--sm" href="{{ route($routeName.'.create') }}">New {{ strtolower($singular) }}</a>
        </div>

        @if ($records->isEmpty())
            <p class="empty">Nothing here yet. <a href="{{ route($routeName.'.create') }}">Create the first one</a>.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            @foreach ($columns as $label)
                                <th>{{ $label }}</th>
                            @endforeach
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                @foreach ($columns as $attribute => $label)
                                    <td class="cell-truncate">
                                        @php $value = $record->{$attribute}; @endphp

                                        @if (is_bool($value))
                                            <span class="pill {{ $value ? 'pill--on' : 'pill--off' }}">{{ $value ? 'Yes' : 'No' }}</span>
                                        @elseif ($value instanceof \DateTimeInterface)
                                            {{ $value->format('d M Y') }}
                                        @elseif ($value === null || $value === '')
                                            <span style="color:var(--muted)">—</span>
                                        @else
                                            {!! strip_tags((string) $value) !!}
                                        @endif
                                    </td>
                                @endforeach

                                <td class="actions">
                                    <a class="btn btn--ghost btn--sm" href="{{ route($routeName.'.edit', $record->getKey()) }}">Edit</a>
                                    <form method="post" action="{{ route($routeName.'.destroy', $record->getKey()) }}"
                                          style="display:inline"
                                          data-confirm="Delete this {{ strtolower($singular) }}? This cannot be undone.">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn--danger btn--sm" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $records->links('admin.pagination') }}
        @endif
    </div>
@endsection
