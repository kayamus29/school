<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

<div id='full_calendar_events'></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
    // A modified version of codes from
    // https://www.positronx.io/create-events-in-laravel-using-fullcalendar-and-jquery-ajax/
    $(document).ready(function () {

        var SITEURL = "{{ url('/') }}";

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var calendar = $('#full_calendar_events').fullCalendar({
            header: {
                left: 'prev,next',
                center: 'title',
                right: 'month,agendaWeek,agendaDay'
            },
            height: {{($editable == 'true')?500:"parent"}},
            groupByResource: true,
            defaultView: 'month',
            editable: {{$editable}},
            eventLimit: true, // when too many events in a day, show the popover
            events: SITEURL + '/calendar-event',
            displayEventTime: true,
            selectable: {{$selectable}},
            selectHelper: {{$selectable}},
            select: function (event_start, event_end) {
                var event_name = prompt("Event Name:");
                if (event_name) {
                    var event_start = $.fullCalendar.formatDate(event_start, "Y-MM-DD HH:mm:ss");
                    var event_end = $.fullCalendar.formatDate(event_end, "Y-MM-DD HH:mm:ss");
                    $.ajax({
                        url: SITEURL + "/calendar-crud-ajax",
                        data: {
                            title: event_name,
                            start: event_start,
                            end: event_end,
                            type: 'create'
                        },
                        type: "POST",
                        success: function (data) {
                            displayMessage("Event created.");
                            var createdEvent = data.event || data;

                            calendar.fullCalendar('renderEvent', {
                                id: createdEvent.id,
                                title: createdEvent.title || event_name,
                                start: createdEvent.start || event_start,
                                end: createdEvent.end || event_end
                            }, true);
                            calendar.fullCalendar('unselect');
                        }
                    });
                }
            },
            eventResize: function (event, delta) {
                var event_start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                var event_end = $.fullCalendar.formatDate(event.end, "Y-MM-DD");

                $.ajax({
                    url: SITEURL + '/calendar-crud-ajax',
                    data: {
                        title: event.title,
                        start: event_start,
                        end: event_end,
                        id: event.id,
                        type: 'edit'
                    },
                    type: "POST",
                    success: function (response) {
                        displayMessage("Event updated");
                    }
                });
            },
            eventDrop: function (event) {
                var event_start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                var event_end = event.end ? $.fullCalendar.formatDate(event.end, "Y-MM-DD") : event_start;

                $.ajax({
                    url: SITEURL + '/calendar-crud-ajax',
                    data: {
                        title: event.title,
                        start: event_start,
                        end: event_end,
                        id: event.id,
                        type: 'edit'
                    },
                    type: "POST",
                    success: function () {
                        displayMessage("Event updated");
                    }
                });
            },
            eventClick: function (event) {
                if({{$selectable}}){
                    var action = prompt("Type 'edit' to rename this event, or 'delete' to remove it.", "edit");
                    if (!action) {
                        return;
                    }

                    action = action.toLowerCase().trim();

                    if (action === 'edit') {
                        var eventTitle = prompt("Update event name:", event.title);
                        if (!eventTitle) {
                            return;
                        }

                        var event_start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
                        var event_end = event.end ? $.fullCalendar.formatDate(event.end, "Y-MM-DD") : event_start;
                        $.ajax({
                            url: SITEURL + '/calendar-crud-ajax',
                            data: {
                                title: eventTitle,
                                start: event_start,
                                end: event_end,
                                id: event.id,
                                type: 'edit'
                            },
                            type: "POST",
                            success: function (response) {
                                event.title = eventTitle;
                                calendar.fullCalendar('updateEvent', event);
                                displayMessage("Event updated");
                            }
                        });
                        return;
                    }

                    if (action === 'delete') {
                        var eventDelete = confirm("Are you sure to delete?");
                        if (eventDelete) {
                            $.ajax({
                                type: "POST",
                                url: SITEURL + '/calendar-crud-ajax',
                                data: {
                                    id: event.id,
                                    type: 'delete'
                                },
                                success: function () {
                                    calendar.fullCalendar('removeEvents', event.id);
                                    displayMessage("Event removed");
                                }
                            });
                        }
                    }
                }
            }
        });
    });

    function displayMessage(message) {
        toastr.success(message, 'Event');            
    }

</script>
