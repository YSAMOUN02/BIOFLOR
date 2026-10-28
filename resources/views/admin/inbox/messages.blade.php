@extends('admin.layout')

@section('title', 'Contact messages')

@section('content')
    <div class="card">
        <div class="card__head">
            <h2>Messages</h2>
            <span class="pill pill--off">{{ $messages->total() }}</span>
        </div>

        @if ($messages->isEmpty())
            <p class="empty">No messages yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>From</th><th>Subject &amp; message</th><th>Received</th><th></th></tr></thead>
                    <tbody>
                        @foreach ($messages as $message)
                            <tr>
                                <td style="white-space:nowrap">
                                    {{ $message->name }}<br>
                                    <a href="mailto:{{ $message->email }}" style="font-size:.8125rem">{{ $message->email }}</a>
                                </td>
                                <td style="max-width:560px">
                                    <strong>{{ $message->subject ?: 'No subject' }}</strong>
                                    <p style="margin:.25rem 0 0;color:var(--muted);white-space:pre-wrap">{{ $message->message }}</p>
                                </td>
                                <td style="white-space:nowrap">{{ $message->created_at->format('d M Y H:i') }}</td>
                                <td class="actions">
                                    <a class="btn btn--ghost btn--sm"
                                       href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: '.($message->subject ?: 'your enquiry')) }}">Reply</a>
                                    <form method="post" action="{{ route('admin.messages.destroy', $message) }}"
                                          style="display:inline" data-confirm="Delete this message?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn--danger btn--sm" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $messages->links('admin.pagination') }}
        @endif
    </div>
@endsection
