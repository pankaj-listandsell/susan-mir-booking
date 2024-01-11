@extends('layouts.app')

@section ('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Availability Calendar</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Availability Calendar</li>
            </ol>
          </div>
        </div>
      </div>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                Availability Calendar
              </div>
              <div class="panel">
                        
                <div class="panel-body no-padding" style="background: #f4f6f8;padding: 0px 15px;">
                    <div class="row">
                        <div class="col-md-3" style="border-right: 1px solid #dee2e6;">
                            <ul class="nav nav-tabs  flex-column vertical-nav" id="items_tab"  role="tablist">
                                @foreach($rows as $k=>$item)
                                    <li class="nav-item event-name ">
                                        <a class="nav-link" data-id="{{$item->id}}" data-toggle="tab" href="#calendar-{{$item->id}}" title="{{$item->name}}" >#{{$item->id}} - {{$item->name}}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="col-md-9" style="background: white;padding: 15px;">
                            <div id='calendar'></div>
                        </div>
                    </div>
                </div>
            </div>
             
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div class="modal" id="eventModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                {{-- <h5 class="modal-title" id="eventModalTitle"></h5> --}}
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="maxPeopleForm">
                <div class="modal-body">
                        <div class="form-group">
                            <label for="maxPeopleInput">Maximum Number of People:</label>
                            <input type="hidden" class="form-control" id="slot_id" value="1">
                            <input type="number" class="form-control" id="number_people" required>
                        </div>
                        
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <!-- Add additional buttons or actions here -->
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
@section ('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.9.0/fullcalendar.js"></script>
<!-- Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
<script>
    $(document).ready(function () {
       
    var SITEURL = "{{ url('/') }}";
      
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
      
    var calendar = $('#calendar').fullCalendar({
        editable: true,
        events: SITEURL + "/fullcalender",
        displayEventTime: true,
        editable: true,
        eventRender: function (event, element, view) {
            if (event.allDay === 'true') {
                    event.allDay = false;
            } else {
                    event.allDay = false;
            }
        },
        selectable: true,
        selectHelper: true,
        select: function (start, end, allDay) {
            var title = prompt('Event Title:');
            if (title) {
                var start = $.fullCalendar.formatDate(start, "Y-MM-DD");
                var end = $.fullCalendar.formatDate(end, "Y-MM-DD");
                $.ajax({
                    url: SITEURL + "/fullcalenderAjax",
                    data: {
                        title: title,
                        start: start,
                        end: end,
                        type: 'add'
                    },
                    type: "POST",
                    success: function (data) {
                        displayMessage("Event Created Successfully");
    
                        calendar.fullCalendar('renderEvent',
                            {
                                id: data.id,
                                title: title,
                                start: start,
                                end: end,
                                allDay: allDay
                            },true);
    
                        calendar.fullCalendar('unselect');
                    }
                });
            }
        },
        eventDrop: function (event, delta) {
            var start = $.fullCalendar.formatDate(event.start, "Y-MM-DD");
            var end = $.fullCalendar.formatDate(event.end, "Y-MM-DD");
    
            $.ajax({
                url: SITEURL + '/fullcalenderAjax',
                data: {
                    title: event.title,
                    start: start,
                    end: end,
                    id: event.id,
                    type: 'update'
                },
                type: "POST",
                success: function (response) {
                    displayMessage("Event Updated Successfully");
                }
            });
        },
        eventClick: function (event) {
             $('#slot_id').val(event.id);
             $('#number_people').val(event.max_people);

            $('#eventModalTitle').text(event.title);
            $('#eventModal').modal('show');
        }
    
    });

    $('#maxPeopleForm').submit(function (e) {
            e.preventDefault();
            var max_people = $('#number_people').val();
            var slot_id = $('#slot_id').val();
            
            $.ajax({
                url: SITEURL + '/fullcalenderAjax',
                data: {
                    max_people: max_people,
                    id: slot_id,
                    type: 'update'
                },
                type: "POST",
                success: function (response) {
                    displayMessage("Celender Updated Successfully");
                    calendar.fullCalendar('rerenderEvents');
                    $('#eventModal').modal('hide');  
                    location.reload();
                }
            });
        });
     
    });
     
    function displayMessage(message) {
        toastr.success(message, 'Event');
    } 

    

   
      
</script>

  
@endsection

