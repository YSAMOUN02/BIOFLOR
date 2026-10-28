@extends('admin.layout')

@section('title', 'Launch checklist')

@section('content')
    <div class="card">
        <div class="card__body checklist-head">
            <div>
                <h2 style="margin:0 0 .25rem">{{ $done }} of {{ $tasks->count() }} tasks done</h2>
                <p class="checklist-head__text">
                    Work through the list in order. Steps marked <span class="pill pill--off">checks itself</span> tick
                    automatically once the sample text is replaced; tick the others yourself with <strong>Mark done</strong>.
                </p>
            </div>
            @include('admin.partials.progress', ['done' => $done, 'total' => $tasks->count()])
        </div>
    </div>

    @foreach ($tasks as $task)
        @php $left = collect($task['steps'])->reject(fn ($s) => $s['done'] || $s['optional'])->count(); @endphp
        <details class="card task {{ $task['done'] ? 'is-done' : '' }}" id="task-{{ $task['number'] }}" @if (! $task['done']) open @endif>
            <summary class="task__head">
                <span class="task__number" aria-hidden="true">@if ($task['done']) &#10003; @else {{ $task['number'] }} @endif</span>
                <span class="task__title">{{ $task['number'] }}. {{ $task['title'] }}</span>
                <span class="card__spacer"></span>
                @if ($task['done'])
                    <span class="pill pill--on">Done</span>
                @else
                    <span class="pill pill--warn">{{ $left }} {{ \Illuminate\Support\Str::plural('step', $left) }} left</span>
                @endif
            </summary>

            <div class="task__body">
                @if ($task['hint'])
                    <p class="task__hint">{{ $task['hint'] }}</p>
                @endif

                <ol class="task__steps">
                    @foreach ($task['steps'] as $step)
                        <li class="step {{ $step['done'] ? 'is-done' : '' }}">
                            <span class="step__check" aria-label="{{ $step['done'] ? 'Done' : 'To do' }}">@if ($step['done'])&#10003;@endif</span>
                            <div class="step__text">
                                <span class="step__label">{{ $step['label'] }}</span>
                                @if ($step['optional'])
                                    <span class="pill pill--off">optional</span>
                                @endif
                                @if (! empty($step['note']))
                                    <span class="pill pill--off">{{ $step['note'] }}</span>
                                @elseif (! $step['manual'])
                                    <span class="pill pill--off">checks itself</span>
                                @endif
                                @if ($step['detail'])
                                    <span class="step__detail">{{ $step['detail'] }}</span>
                                @endif
                            </div>
                            <div class="step__actions">
                                @if ($step['url'])
                                    <a class="btn btn--ghost btn--sm" href="{{ $step['url'] }}" @if (! str_contains($step['url'], '/admin')) target="_blank" rel="noopener" @endif>Open</a>
                                @endif
                                @if ($step['manual'])
                                    <form method="post" action="{{ route('admin.checklist.toggle', $step['id']) }}">
                                        @csrf
                                        <input type="hidden" name="task" value="{{ $task['number'] }}">
                                        <button class="btn btn--sm {{ $step['done'] ? 'btn--ghost' : 'btn--primary' }}" type="submit">
                                            {{ $step['done'] ? 'Undo' : 'Mark done' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </details>
    @endforeach
@endsection
