@extends('admin.layout')

@section('title', 'Newsletter subscribers')

@section('content')
    <div class="card">
        <div class="card__head">
            <h2>Subscribers</h2>
            <span class="pill pill--off">{{ $subscribers->total() }}</span>
            <span class="card__spacer"></span>
            <a class="btn btn--primary btn--sm" href="{{ route('admin.subscribers.export') }}">Export CSV</a>
        </div>

        @if ($subscribers->isEmpty())
            <p class="empty">No one has subscribed yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Email</th><th>Locale</th><th>Consent</th><th>Subscribed</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($subscribers as $subscriber)
                            <tr>
                                <td>{{ $subscriber->email }}</td>
                                <td>{{ $subscriber->locale }}</td>
                                <td>
                                    <span class="pill {{ $subscriber->consent ? 'pill--on' : 'pill--off' }}">
                                        {{ $subscriber->consent ? 'Yes' : 'No' }}
                                    </span>
                                </td>
                                <td>{{ $subscriber->created_at->format('d M Y H:i') }}</td>
                                <td class="actions">
                                    <form method="post" action="{{ route('admin.subscribers.destroy', $subscriber) }}"
                                          data-confirm="Remove this subscriber?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn--danger btn--sm" type="submit">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $subscribers->links('admin.pagination') }}
        @endif
    </div>
@endsection
