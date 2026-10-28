@extends('admin.layout')

@section('title', 'Reviews')

@section('content')
    <div class="card">
        <div class="card__head">
            <h2>Reviews</h2>
            <span class="pill pill--off">{{ $reviews->total() }}</span>
            <span class="card__spacer"></span>

            <a class="btn btn--ghost btn--sm" href="{{ route('admin.reviews.index') }}"
               @if (! $filter) style="border-color:var(--primary)" @endif>All</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.reviews.index', ['filter' => 'pending']) }}"
               @if ($filter === 'pending') style="border-color:var(--primary)" @endif>Pending</a>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.reviews.index', ['filter' => 'approved']) }}"
               @if ($filter === 'approved') style="border-color:var(--primary)" @endif>Published</a>
        </div>

        @if ($reviews->isEmpty())
            <p class="empty">No reviews match this filter.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Product</th><th>Rating</th><th>Review</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($reviews as $review)
                            <tr>
                                <td class="cell-truncate" style="max-width:200px">
                                    {!! $review->product?->name ?? '—' !!}
                                </td>
                                <td style="white-space:nowrap;color:var(--warning)">
                                    {{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}
                                </td>
                                <td style="max-width:420px">
                                    <strong>{{ $review->title ?: '—' }}</strong><br>
                                    <span style="color:var(--muted);font-size:.875rem">{{ \Illuminate\Support\Str::limit($review->body, 140) }}</span><br>
                                    <span style="color:var(--muted);font-size:.8125rem">
                                        {{ $review->author }} &middot; {{ $review->created_at->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="pill {{ $review->is_approved ? 'pill--on' : 'pill--warn' }}">
                                        {{ $review->is_approved ? 'Published' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="actions">
                                    <form method="post" action="{{ route('admin.reviews.toggle', $review) }}" style="display:inline">
                                        @csrf
                                        <button class="btn btn--ghost btn--sm" type="submit">
                                            {{ $review->is_approved ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>
                                    <form method="post" action="{{ route('admin.reviews.destroy', $review) }}"
                                          style="display:inline" data-confirm="Delete this review?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn--danger btn--sm" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $reviews->links('admin.pagination') }}
        @endif
    </div>
@endsection
