@extends('layouts.admin')
@section('page-title')
    {{__('Dashboard')}}
@endsection
@push('script-page')
    <script>
        $(document).ready(function()
        {
            get_data();
        });

        function get_data()
        {
            var calender_type=$('#calender_type :selected').val();
            $('#calendar').removeClass('local_calender');
            $('#calendar').removeClass('goggle_calender');
            if(calender_type==undefined){
                $('#calendar').addClass('local_calender');
            }
            $('#calendar').addClass(calender_type);
            $.ajax({
                url: $("#event_dashboard").val()+"/event/get_event_data" ,
                method:"POST",
                data: {"_token": "{{ csrf_token() }}",'calender_type':calender_type},
                success: function(data) {
                    (function () {
                        var etitle;
                        var etype;
                        var etypeclass;
                        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'timeGridDay,timeGridWeek,dayGridMonth'
                            },
                            buttonText: {
                                timeGridDay: "{{__('Day')}}",
                                timeGridWeek: "{{__('Week')}}",
                                dayGridMonth: "{{__('Month')}}"
                            },
                            slotLabelFormat: {
                                hour: '2-digit',
                                minute: '2-digit',
                                hour12: false,
                            },
                            themeSystem: 'bootstrap',
                            navLinks: true,
                            droppable: true,
                            selectable: true,
                            selectMirror: true,
                            editable: true,
                            dayMaxEvents: true,
                            handleWindowResize: true,
                            height: 'auto',
                            timeFormat: 'H(:mm)',
                            {{--events: {!! json_encode($arrEvents) !!},--}}
                            events: data,
                            locale: '{{basename(App::getLocale())}}',
                            dayClick: function (e) {
                                var t = moment(e).toISOString();
                                $("#new-event").modal("show"), $(".new-event--title").val(""), $(".new-event--start").val(t), $(".new-event--end").val(t)
                            },
                            eventResize: function (event) {
                                var eventObj = {
                                    start: event.start.format(),
                                    end: event.end.format(),
                                };
                            },
                            viewRender: function (t) {
                                e.fullCalendar("getDate").month(), $(".fullcalendar-title").html(t.title)
                            },
                            eventClick: function (e, t) {
                                var title = e.title;
                                var url = e.url;

                                if (typeof url != 'undefined') {
                                    $("#commonModal .modal-title").html(title);
                                    $("#commonModal .modal-dialog").addClass('modal-md');
                                    $("#commonModal").modal('show');
                                    $.get(url, {}, function (data) {
                                        $('#commonModal .modal-body').html(data);
                                    });
                                    return false;
                                }
                            }
                        });
                        calendar.render();
                    })();
                }
            });
        }
    </script>
@endpush
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('HRM')}}</li>
@endsection
@php
    $setting = \App\Models\Utility::settings();

    $break_time = $setting['break_time'];

    // Find total hour in Office
    $start_date = new DateTime($officeTime['startTime']);
    $since_start = $start_date->diff(new DateTime($officeTime['endTime']));
    $since_start_h = ($since_start->h < 10)?"0".$since_start->h:$since_start->h;
    $since_start_i = ($since_start->i < 10)?"0".$since_start->i:$since_start->i;

    // Find total worked hour
    $clock_in = new DateTime($employeeAttendance->clock_in);
    $worked_hour = $clock_in->diff(new DateTime(date("H:i:s")));
    $worked_h = ($worked_hour->h < 10)?"0".$worked_hour->h:$worked_hour->h;
    $worked_i = ($worked_hour->i < 10)?"0".$worked_hour->i:$worked_hour->i;
    $total_worked_hour = $worked_h.":".$worked_i;

    dd($total_worked_hour);

    // Find Real work hour
    $break_arr = explode(":", $employeeAttendance->total_break_duration);
    $real_worked_h = $worked_h - $break_arr[0];
    $real_worked_i = $worked_i - $break_arr[1]; 

    dd($break_arr[0]);
       
    $real_worked_h = ($real_worked_h < 10)?"0".$real_worked_h:$real_worked_h;
    $real_worked_i = ($real_worked_i < 10)?"0".$real_worked_i:$real_worked_i;
    $real_worked_hour = $real_worked_h.":".$real_worked_i;

    dd($real_worked_hour);

    // Find Balance
    
    $total_schedule_min = 60 * ($since_start_h) + $since_start_i;
    $total_schedule_work_min = $total_schedule_min - $break_time;

    $total_schedule_work_hour = ($total_schedule_work_min / 60);
    $schedule_work_hour_arr = explode(":", $total_schedule_work_hour);

    $schedule_work_h = $schedule_work_hour_arr[0];
    $schedule_work_i = isset($schedule_work_hour_arr[1])?$schedule_work_hour_arr[1]:0;    
    $schedule_work_h = ($schedule_work_h < 10)?"0".$schedule_work_h:$schedule_work_h;
    $schedule_work_i = ($schedule_work_i < 10)?"0".$schedule_work_i:$schedule_work_i;

    $balane_work_hour = date("H:i", strtotime($schedule_work_h.":".$schedule_work_i.":00 -".$real_worked_h." hour, -".$real_worked_h." minutes"));

@endphp
@section('content')
    @if(\Auth::user()->type != 'client' && \Auth::user()->type != 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-xxl-6">
                        <div class="card">
                            {{-- <div class="card-header">
                                <h4>{{__('Mark Attandance')}}</h4>
                            </div> --}}
                            <div class="card-body dash-card-body">
                                <h4>Hi {{ \Auth::user()->name }}!</h4>
                                <p class="text-muted pb-0-5">{{__('My Office Time: '.$officeTime['startTime'].' to '.$officeTime['endTime'])}}</p>
                            
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-container">
                                                <div class="info-item">
                                                    <p class="label"><strong>Worked:</strong></p>
                                                    <p class="time"> {{ $real_worked_hour }}</p>
                                                </div>
                                                <div class="info-item">
                                                    <p class="label"><strong>Break:</strong></p>
                                                    <p class="time">
                                                        {{ date("H:i", strtotime($employeeAttendance->total_break_duration)) }}
                                                    </p>
                                                </div>
                                                <div class="info-item">
                                                    <p class="label"><strong>Balance:</strong></p>
                                                    <p class="time">{{ $balane_work_hour }}</p>
                                                </div>
                                                <div class="info-item">
                                                    <p class="label"><strong>Overtime</strong></p>
                                                    <p class="time"> {{ isset($employeeAttendance->total_break_duration) && !empty($employeeAttendance->overtime) ? $employeeAttendance->overtime : '00:00:00' }}</p>
                                                </div>
                                                
                                            </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="button-container">
                                            {{ Form::open(array('url' => 'attendanceemployee/attendance', 'method' => 'post')) }}
                                            @if(empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-success">
                                                    {{ __('CLOCK IN') }}
                                                </button>
                                            @else
                                                <button type="submit" value="0" name="in" id="clock_in" class="btn btn-success disabled" disabled>
                                                    {{ __('CLOCK IN') }}
                                                </button>
                                            @endif
                                            {{ Form::close() }}
                                    
                        
                                    <!-- Take a Break Button -->
                                    
                                        @if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                            <button id="take_break" class="btn btn-warning">
                                                {{ __('TAKE A BREAK') }}
                                            </button>
                                        @else
                                            <button id="take_break" class="btn btn-warning disabled d-none" disabled>
                                                {{ __('TAKE A BREAK') }}
                                            </button>
                                        @endif
                                   
                        
                                    
                                        @if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                            {{ Form::model($employeeAttendance, array('route' => array('attendanceemployee.update', $employeeAttendance->id), 'method' => 'PUT')) }}
                                            <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger">
                                                {{ __('CLOCK OUT') }}
                                            </button>
                                        @else
                                            <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger disabled" disabled>
                                                {{ __('CLOCK OUT') }}
                                            </button>
                                        @endif
                                        {{ Form::close() }}
                                    </div>
                                       
                               
                                </div>
                               
                            </div>
                           
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h5>{{ __('Event') }}</h5>
                                    </div>
                                    <div class="col-lg-6">
                                        @if (isset($setting['google_calendar_enable']) && $setting['google_calendar_enable'] == 'on')
                                        <select class="form-control" name="calender_type" id="calender_type" onchange="get_data()">
                                            <option value="goggle_calender">{{__('Google Calender')}}</option>
                                            <option value="local_calender" selected="true">{{__('Local Calender')}}</option>
                                        </select>
                                        @endif
                                        <input type="hidden" id="event_dashboard" value="{{url('/')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id='calendar' class='calendar e-height'></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-6">
                        <div class="card list_card">
                            <div class="card-header">
                                <h4>{{__('Announcement List')}}</h4>
                            </div>
                            <div class="card-body dash-card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead>
                                        <tr>
                                            <th>{{__('Title')}}</th>
                                            <th>{{__('Start Date')}}</th>
                                            <th>{{__('End Date')}}</th>
                                            <th>{{__('description')}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($announcements as $announcement)
                                            <tr>
                                                <td>{{ $announcement->title }}</td>
                                                <td>{{ \Auth::user()->dateFormat($announcement->start_date)  }}</td>
                                                <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>
                                                <td>{{ $announcement->description }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4">
                                                    <div class="text-center">
                                                        <h6>{{__('There is no Announcement List')}}</h6>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="card list_card">
                            <div class="card-header">
                                <h4>{{__('Meeting List')}}</h4>
                            </div>
                            <div class="card-body dash-card-body">
                                @if(count($meetings) > 0)
                                    <div class="table-responsive">
                                        <table class="table align-items-center">
                                            <thead>
                                            <tr>
                                                <th>{{__('Meeting title')}}</th>
                                                <th>{{__('Meeting Date')}}</th>
                                                <th>{{__('Meeting Time')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($meetings as $meeting)
                                                <tr>
                                                    <td>{{ $meeting->title }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                                    <td>{{ \Auth::user()->timeFormat($meeting->time) }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="p-2">
                                        {{__('No meeting scheduled yet.')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-xxl-12">
                <div class="card">
                    <div class="card-header">
                        <h5>{{__("Today's Not Clock In")}}</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row g-3 flex-nowrap team-lists horizontal-scroll-cards">
                                    @foreach($notClockIns as $notClockIn)
                                    @php
                                        $user = $notClockIn->user;
                                        $logo= asset(Storage::url('uploads/avatar/'));
                                        $avatar = !empty($notClockIn->user->avatar) ? $notClockIn->user->avatar : 'avatar.png';
                                    @endphp
                                        <div class="col-auto">
                                            <img src="{{ $logo . $avatar }}" alt="">
                                            <p class="mt-2">{{ $notClockIn->name }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <h5>{{ __('Event') }}</h5>
                                    </div>
                                    <div class="col-lg-6">

                                        @if(isset($setting['google_calendar_enable']) && $setting['google_calendar_enable'] == 'on')
                                            <select class="form-control" name="calender_type" id="calender_type" onchange="get_data()">
                                                <option value="goggle_calender">{{__('Google Calender')}}</option>
                                                <option value="local_calender" selected="true">{{__('Local Calender')}}</option>
                                            </select>
                                        @endif
                                        <input type="hidden" id="event_dashboard" value="{{url('/')}}">
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id='calendar' class='calendar'></div>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-3">
                        <div class="col-xxl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5>{{__('Staff')}}</h5>
                                    <div class="row  mt-4">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-users"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Staff')}}</p>
                                                    <h4 class="mb-0 text-success">{{ $countUser +   $countClient}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 my-3 my-sm-0">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Employee')}}</p>
                                                    <h4 class="mb-0 text-info">{{$countUser}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Client')}}</p>
                                                    <h4 class="mb-0 text-danger">{{$countClient}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5>{{__('Job')}}</h5>
                                    <div class="row  mt-4">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-award"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Jobs')}}</p>
                                                    <h4 class="mb-0 text-success">{{$activeJob + $inActiveJOb}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 my-3 my-sm-0">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-check"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Active Jobs')}}</p>
                                                    <h4 class="mb-0 text-info">{{$activeJob}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="ti ti-x"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Inactive Jobs')}}</p>
                                                    <h4 class="mb-0 text-danger">{{$inActiveJOb}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="col-xxl-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5>{{__('Training')}}</h5>
                                    <div class="row  mt-4">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-users"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Total Training')}}</p>
                                                    <h4 class="mb-0 text-success">{{ $onGoingTraining +   $doneTraining}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6 my-3 my-sm-0">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Trainer')}}</p>
                                                    <h4 class="mb-0 text-info">{{$countTrainer}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="ti ti-user-check"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Active Training')}}</p>
                                                    <h4 class="mb-0 text-danger">{{$onGoingTraining}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="d-flex align-items-start mb-3">
                                                <div class="theme-avtar bg-secondary">
                                                    <i class="ti ti-user-minus"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="text-muted text-sm mb-0">{{__('Done Training')}}</p>
                                                    <h4 class="mb-0 text-secondary">{{$doneTraining}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">

                                <h5>{{__('Announcement List')}}</h5>
                            </div>
                            <div class="card-body" style="min-height: 295px;">
                                <div class="table-responsive">
                                    @if(count($announcements) > 0)
                                        <table class="table align-items-center">
                                            <thead>
                                            <tr>
                                                <th>{{__('Title')}}</th>
                                                <th>{{__('Start Date')}}</th>
                                                <th>{{__('End Date')}}</th>

                                            </tr>
                                            </thead>
                                            <tbody class="list">
                                            @foreach($announcements as $announcement)
                                                <tr>
                                                    <td>{{ $announcement->title }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($announcement->start_date) }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($announcement->end_date) }}</td>

                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="p-2">
                                            {{__('No accouncement present yet.')}}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{__('Meeting schedule')}}</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    @if(count($meetings) > 0)
                                        <table class="table align-items-center">
                                            <thead>
                                            <tr>
                                                <th>{{__('Title')}}</th>
                                                <th>{{__('Date')}}</th>
                                                <th>{{__('Time')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody class="list">
                                            @foreach($meetings as $meeting)
                                                <tr>
                                                    <td>{{ $meeting->title }}</td>
                                                    <td>{{ \Auth::user()->dateFormat($meeting->date) }}</td>
                                                    <td>{{  \Auth::user()->timeFormat($meeting->time) }}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @else
                                        <div class="p-2">
                                            {{__('No meeting scheduled yet.')}}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif
     <!-- Break Modal -->
<div class="modal fade" id="breakModal" tabindex="-1" role="dialog" aria-labelledby="breakModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="breakModalLabel">{{ __('Select Break Type') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </button>
            </div>
            <div class="modal-body">
                <form id="breakForm">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="breakType" id="break1" value="morning">
                        <label class="form-check-label" for="morningBreak">{{ __('Morning Break') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="breakType" id="break2" value="lunch">
                        <label class="form-check-label" for="lunchBreak">{{ __('Lunch Break') }}</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="breakType" id="break3" value="evening">
                        <label class="form-check-label" for="eveningBreak">{{ __('Evening Break') }}</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="startBreak" class="btn btn-primary">{{ __('Start') }}</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- Break Timer Modal -->
<div class="modal fade" id="breakTimerModal" tabindex="-1" role="dialog" aria-labelledby="breakTimerModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="breakTimerModalLabel">{{ __('Break Timer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </button>
            </div>
            <div class="modal-body">
                <div id="breakTimer">00:00:00</div>
            </div>
            <div class="modal-footer">
                <button type="button" id="endBreak" class="btn btn-danger">{{ __('End') }}</button>
            </div>
        </div>
    </div>
</div>
@endsection
@push('script-page')
<script>
$(document).ready(function() {
    let timerInterval;
    let startTime;
    

    // Function to start the break timer
    function startBreakTimer() {
        timerInterval = setInterval(function() {
            const now = new Date();
            const elapsed = (now - startTime) / 1000; // in seconds
            const hours = Math.floor(elapsed / 3600);
            const minutes = Math.floor((elapsed % 3600) / 60);
            const seconds = Math.floor(elapsed % 60);
            document.getElementById('breakTimer').innerText = 
                ('0' + hours).slice(-2) + ':' + 
                ('0' + minutes).slice(-2) + ':' + 
                ('0' + seconds).slice(-2);
        }, 1000);
    }

    
    function initializeTimer() {
        const storedStartTime = localStorage.getItem('breakStartTime');
        if (storedStartTime) {
            startTime = new Date(storedStartTime);
            startBreakTimer();
            $('#take_break').text('On Break'); 
           
        }
    }

    $('#breakModal').on('show.bs.modal', function () {
        $('#startBreak').prop('disabled', true);
    });

   
    $('input[name="breakType"]').change(function() {
        $('#startBreak').prop('disabled', false);
    });

   
    $('#take_break').click(function() {
        if (localStorage.getItem('breakStartTime')) {
            $('#breakTimerModal').modal('show');
        } else {
            $('#breakModal').modal('show');
        }
    });

    $('#startBreak').click(function() {
        if (!$('input[name="breakType"]:checked').length) {
            alert('Please select a break type.');
            return;
        }
        
        const breakType = $('input[name="breakType"]:checked').val();
        startTime = new Date();
        localStorage.setItem('breakStartTime', startTime.toISOString()); 
        $('#breakModal').modal('hide');
        $('#breakTimerModal').modal('show');
        startBreakTimer();
        
        $('#take_break').text('On Break'); 
        const formattedStartTime = startTime.toTimeString().split(' ')[0]; 
        $.post('{{ route("breaks.store") }}', 
        { 
          employee_id: {{ auth()->user()->id }}, 
          break_start_time: formattedStartTime, 
          break_type: breakType,
          _token: '{{ csrf_token() }}' 
        });
    });

    $('#endBreak').click(function() {
        const endTime = new Date();
        clearInterval(timerInterval);
        $('#breakTimerModal').modal('hide');
        $('#take_break').text('Take a Break'); 
        
        const formattedEndTime = endTime.toTimeString().split(' ')[0]; 

        // Send break end time to the server
        $.post('{{ route("breaks.end") }}', 
        { 
          employee_id: {{ auth()->user()->id }}, 
          break_end_time: formattedEndTime, 
          _token: '{{ csrf_token() }}' 
        });

        // Clear local storage
        localStorage.removeItem('breakStartTime');
    });

    $('#clock_out').click(function(event) {
        const breakStartTime = localStorage.getItem('breakStartTime');
        if (breakStartTime) {
            event.preventDefault();
            alert('Please end your break before clocking out.');
            return false;
        }
        
       
    });
    $('#breakModal').on('hidden.bs.modal', function () {
        $('#breakForm')[0].reset(); // Reset form fields
        $('#startBreak').prop('disabled', true); // Disable the start button
    });
     
 
    initializeTimer();
});
</script>
@endpush
<style>
    
    .modal-header {
    background-color: #f8f9fa;
    border-bottom: none;
}

.modal-title {
    font-weight: bold;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    border-top: none;
    padding: 15px;
}

.form-check {
    padding: 10px;
    margin-bottom: 15px;
    background-color: #f1f1f1;
    border-radius: 8px;
    cursor: pointer;
}

.form-check-input {
    margin-right: 15px;
    margin-top: 5px;
    cursor: pointer;
}

.form-check-label {
    display: flex;
    align-items: center;
    font-size: 16px;
    font-weight: 500;
}

.form-check-label i {
    font-size: 24px;
    margin-right: 15px;
}

.form-check-label p {
    margin: 0;
    font-size: 12px;
    color: #6c757d;
}

#startBreak:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}

#breakTimer {
    font-size: 48px;
    font-weight: bold;
    margin-bottom: 10px;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

/* Responsive Design */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 1.75rem auto;
    }

    .form-check-label {
        flex-direction: column;
        align-items: flex-start;
    }

    .form-check-label i {
        margin-bottom: 5px;
    }
}
.dash-card-body {
padding: 20px;
}

.row {

justify-content: space-between;
align-items: center;
}

.button-container {
    display: flex;
    width: 100%;
}

.btn {
    width: 150px; /* Fixed width for all buttons */
    height: 50px; /* Fixed height for all buttons */
    padding: 10px;
    font-size: 16px;
    margin: 0 5px;
    text-align: center;
    line-height: 30px; /* Adjust line height to vertically center the text */
    display: inline-block;
}

.info-container {
display: flex;
justify-content: center;
align-items: flex-start;
gap: 10px;
}

.info-container p {
margin: 0;
font-size: 14px;
color: #555;
}
.info-container {
    display: flex;
    justify-content: space-between;
    width: 100%;
}

.info-item {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin-bottom: 10px;
}

.label {
    font-size: 14px;
    color: #555;
}

.time {
    font-size: 14px;
    font-weight: bold;
    color: #333;
}
</style>


