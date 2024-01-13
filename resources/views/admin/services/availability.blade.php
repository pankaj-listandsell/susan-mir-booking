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
                <h5 class="modal-title"> Manage </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="maxPeopleForm">
                <div class="modal-body">
                        <div class="form-group">
                            <label for="maxPeopleInput">Maximum Number of People:</label>
                            <input type="hidden" class="form-control" id="service_id" >
                            <input type="hidden" class="form-control" id="start_date" >
                            <input type="number" class="form-control" id="number_people" required>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label >{{__('Status')}}</label>
                                <br>
                                <label ><input true-value=1 false-value=0 type="checkbox" id="booking_available"> {{__('Available for booking?')}}</label>
                            </div>
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

<div id="bravo_modal_calendar" class="modal fade">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('Date Information')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form class="row form_modal_calendar form-horizontal" novalidate onsubmit="return false">
                   
                    <div class="col-md-6">
                        <div class="form-group">
                            <label >{{__('Status')}}</label>
                            <br>
                            <label ><input true-value=1 false-value=0 type="checkbox" v-model="form.active"> Available for booking?</label>
                        </div>
                    </div>
                   
                    <div class="col-md-6" >
                        <div class="form-group">
                            <label >Max People</label>
                            <input type="number" id="max_people" class="form-control">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" @click="saveForm">Save changes</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('css')
    <link rel="stylesheet" href="{{asset('libs/fullcalendar-4.2.0/core/main.css')}}">
    <link rel="stylesheet" href="{{asset('libs/fullcalendar-4.2.0/daygrid/main.css')}}">>
@endsection    
@section('js')
    <script src="{{asset('libs/fullcalendar-4.2.0/core/main.js')}}"></script>
    <script src="{{asset('libs/fullcalendar-4.2.0/interaction/main.js')}}"></script>
    <script src="{{asset('libs/fullcalendar-4.2.0/daygrid/main.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script>
          var SITEURL = "{{ url('/') }}";
          $.ajaxSetup({
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
		var calendarEl,calendar,lastId,formModal;
        $('#items_tab').on('show.bs.tab',function (e) {
			calendarEl = document.getElementById('calendar');
			lastId = $(e.target).data('id');
            if(calendar){
				calendar.destroy();
            }
			calendar = new FullCalendar.Calendar(calendarEl, {
                buttonText:{
                    today:  '{{ __('Today') }}',
                },
				plugins: [ 'dayGrid' ,'interaction'],
				header: {},
				selectable: true,
				selectMirror: false,
				allDay:false,
				editable: false,
				eventLimit: true,
				defaultView: 'dayGridMonth',
                firstDay:1,
				events:{
                    url:'{{route('availability.loadDates')}}',
                    extraParams:{
                        id:lastId,
                    }
                },
                eventClick:function (info) {
                    $('#service_id').val(lastId);
                    var form = Object.assign({},info.event.extendedProps);
                    var start_date = moment(info.event.start).format('YYYY-MM-DD');
                    $('#booking_available').prop('checked', form.active == 1);
                    $('#start_date').val(start_date);
                    $('#number_people').val(form.max_people);
                    $('#eventModal').modal('show');
                },
                eventRender: function (info) {
                    $(info.el).find('.fc-title').html(info.event.title);
                }
			});
			calendar.render();
		});

        $('.event-name:first-child a').trigger('click');

        $('#maxPeopleForm').submit(function (e) {
            e.preventDefault();
            var me = this;
            var max_people = $('#number_people').val();
            var service_id = $('#service_id').val();
            var start_date = $('#start_date').val();
            var booking_available =  $('#booking_available').prop('checked') ? 1 : 0;
            
            $.ajax({
                url:'{{route('availability.store')}}',
                data: {
                    max_people: max_people,
                    service_id: service_id,
                    start_date: start_date,
                    active: booking_available
                },
                type: "POST",
                success: function (response) {
                    if(calendar)
                    calendar.refetchEvents();
                    $('#eventModal').modal('hide');  
                }
            });
        });
          
</script>

  
@endsection

