@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    @php $checklistDone = $checklist->where('done', true)->count(); @endphp
    <div class="card">
        <div class="card__head">
            <h2>Launch checklist</h2>
            <span class="pill {{ $checklistNext->isEmpty() ? 'pill--on' : 'pill--warn' }}">{{ $checklistDone }} of {{ $checklist->count() }} done</span>
            <span class="card__spacer"></span>
            <a class="btn btn--primary btn--sm" href="{{ route('admin.checklist') }}">Open checklist</a>
        </div>
        <div class="card__body" style="display:grid;gap:1rem">
            @include('admin.partials.progress', ['done' => $checklistDone, 'total' => $checklist->count()])

            @if ($checklistNext->isEmpty())
                <p style="margin:0">Everything on the list is done.</p>
            @else
                <div>
                    <p class="checklist-head__text" style="margin:0 0 .5rem">Next up:</p>
                    <ul class="next-tasks">
                        @foreach ($checklistNext as $task)
                            @php $left = collect($task['steps'])->reject(fn ($s) => $s['done'] || $s['optional'])->count(); @endphp
                            <li>
                                <a href="{{ route('admin.checklist') }}#task-{{ $task['number'] }}">
                                    <span class="task__number task__number--sm">{{ $task['number'] }}</span>
                                    <span>{{ $task['title'] }}</span>
                                </a>
                                <span class="checklist-head__text">{{ $left }} {{ \Illuminate\Support\Str::plural('step', $left) }} left</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <div class="stat-grid">
        @foreach ($stats as $stat)
            <a class="stat-card" href="{{ route($stat['route']) }}">
                <div class="stat-card__value">{{ $stat['value'] }}</div>
                <div class="stat-card__label">{{ $stat['label'] }}</div>
            </a>
        @endforeach
    </div>

    @if ($pendingReviews || $draftArticles)
        <div class="card">
            <div class="card__head"><h2>Needs attention</h2></div>
            <div class="card__body" style="display:grid;gap:.75rem">
                @if ($pendingReviews)
                    <p style="margin:0">
                        <span class="pill pill--warn">{{ $pendingReviews }}</span>
                        review{{ $pendingReviews === 1 ? '' : 's' }} awaiting approval —
                        <a href="{{ route('admin.reviews.index', ['filter' => 'pending']) }}">moderate</a>
                    </p>
                @endif
                @if ($draftArticles)
                    <p style="margin:0">
                        <span class="pill pill--warn">{{ $draftArticles }}</span>
                        article{{ $draftArticles === 1 ? '' : 's' }} without a publish date —
                        <a href="{{ route('admin.articles.index') }}">review drafts</a>
                    </p>
                @endif
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card__head">
            <h2>Latest messages</h2>
            <span class="card__spacer"></span>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.messages') }}">View all</a>
        </div>

        @if ($latestMessages->isEmpty())
            <p class="empty">No messages yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>From</th><th>Subject</th><th>Received</th></tr></thead>
                    <tbody>
                        @foreach ($latestMessages as $message)
                            <tr>
                                <td>{{ $message->name }}<br><span style="color:var(--muted);font-size:.8125rem">{{ $message->email }}</span></td>
                                <td class="cell-truncate">{{ $message->subject ?: '—' }}</td>
                                <td>{{ $message->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card__head">
            <h2>Latest subscribers</h2>
            <span class="card__spacer"></span>
            <a class="btn btn--ghost btn--sm" href="{{ route('admin.subscribers') }}">View all</a>
        </div>

        @if ($latestSubs->isEmpty())
            <p class="empty">No subscribers yet.</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Email</th><th>Locale</th><th>Signed up</th></tr></thead>
                    <tbody>
                        @foreach ($latestSubs as $sub)
                            <tr>
                                <td>{{ $sub->email }}</td>
                                <td>{{ $sub->locale }}</td>
                                <td>{{ $sub->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
