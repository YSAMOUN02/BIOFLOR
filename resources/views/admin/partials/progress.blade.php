@php $percent = $total ? (int) round($done / $total * 100) : 0; @endphp
<div class="progress-row">
    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $done }}"
         aria-label="{{ $done }} of {{ $total }} tasks done">
        <div class="progress__bar" style="width: {{ $percent }}%"></div>
    </div>
    <span class="progress__label">{{ $percent }}%</span>
</div>
