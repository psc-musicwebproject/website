<div class="card mt-4">
    <div class="card-body">
        @if(isset($eventRoute))
            <div id="calendar" data-event-route="{{ $eventRoute }}" style="min-height: 600px;"></div>
        @else
            <div id="calendar" data-event-route="{{ route('dash.calendar.events') }}" style="min-height: 600px;"></div>
        @endif
    </div>
</div>