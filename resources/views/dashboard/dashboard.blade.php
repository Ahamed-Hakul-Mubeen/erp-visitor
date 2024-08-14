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
    // date_default_timezone_set("Asia/Calcutta");
    $setting = \App\Models\Utility::settings();

    if(\Auth::user()->type != 'client' && \Auth::user()->type != 'company')
    {
        $employeeAttendance_clock_in = "00:00:00";
        $employeeAttendance_total_break_duration = "00:00:00";

        $employeeAttendance_clock_in = isset($employeeAttendance->clock_in)?$employeeAttendance->clock_in:"00:00:00";
        $employeeAttendance_total_break_duration = isset($employeeAttendance->total_break_duration)?$employeeAttendance->total_break_duration:"00:00:00";

        $break_time = $setting['break_time'];

        // Find total hour in Office
        $start_date = new DateTime($officeTime['startTime']);
        $since_start = $start_date->diff(new DateTime($officeTime['endTime']));
        $since_start_h = ($since_start->h < 10)?"0".$since_start->h:$since_start->h;
        $since_start_i = ($since_start->i < 10)?"0".$since_start->i:$since_start->i;

        // Find total worked hour
        if($employeeAttendance_clock_in != "00:00:00")
        {
            $clock_in = new DateTime($employeeAttendance_clock_in);
            $worked_hour = $clock_in->diff(new DateTime(date("H:i:s")));
            $worked_h = ($worked_hour->h < 10)?"0".$worked_hour->h:$worked_hour->h;
            $worked_i = ($worked_hour->i < 10)?"0".$worked_hour->i:$worked_hour->i;
            $total_worked_hour = $worked_h.":".$worked_i;
        }
        else {
            $total_worked_hour = "00:00:00";
            $worked_h = "00";
            $worked_i = "00";
        }

        // Find Real work hour
        $break_arr = explode(":", $employeeAttendance_total_break_duration);
        $real_worked_h = $worked_h - $break_arr[0];
        $real_worked_i = $worked_i - $break_arr[1];

        $real_worked_h = ($real_worked_h < 10)?"0".$real_worked_h:$real_worked_h;
        $real_worked_i = ($real_worked_i < 10)?"0".$real_worked_i:$real_worked_i;
        $real_worked_hour = $real_worked_h.":".$real_worked_i;

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
    }
    
@endphp
@section('content')
    <style>
        .my-text-bold
        {
            font-weight:800;
            font-size:18px;
        }
    </style>
    @if(\Auth::user()->type != 'client' && \Auth::user()->type != 'company')
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-xxl-12">
                        <div class="card">
                            {{-- <div class="card-header">
                                <h4>{{__('Mark Attandance')}}</h4>
                            </div> --}}
                            <div class="card-body dash-card-body">
                                <h4>Hi {{ \Auth::user()->name }}!</h4>
                                <p class="text-muted pb-0-5">{{__('My Office Time: '.$officeTime['startTime'].' to '.$officeTime['endTime'])}}</p>
                                <div class="row">
                                    <div class="col-sm-7">
                                        <div class="row">
                                            <div class="text-center col-sm-3 col-lg-3 col-6">
                                                <p class="my-text-bold">Worked</p>
                                                <p class="time"> {{ $real_worked_hour }}</p>
                                            </div>
                                            <div class="text-center col-sm-3 col-lg-3 col-6">
                                                <p class="my-text-bold">Break</p>
                                                <p class="time">
                                                    {{ date("H:i", strtotime($employeeAttendance_total_break_duration)) }}
                                                </p>
                                            </div>
                                            <div class="text-center col-sm-3 col-lg-3 col-6">
                                                <p class="my-text-bold">Balance</p>
                                                <p class="time">{{ $balane_work_hour }}</p>
                                            </div>
                                            <div class="text-center col-sm-3 col-lg-3 col-6">
                                                <p class="my-text-bold">Overtime</p>
                                                <p class="time"> {{ isset($employeeAttendance->total_break_duration) && !empty($employeeAttendance->overtime) ? $employeeAttendance->overtime : '00:00:00' }}</p>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-sm-5">
                                        <div class="d-flex justify-content-end">

                                            {{Form::open(array('url'=>'attendanceemployee/attendance','method'=>'post'))}}
                                            @if(empty($employeeAttendance) || $employeeAttendance->clock_out != '00:00:00')
                                                <button type="submit" value="0" name="in" id="clock_in" class="mt-2 btn btn-success ">{{__('CLOCK IN')}}</button>
                                            @else
                                                {{-- <button type="submit" value="0" name="in" id="clock_in" class="btn btn-success disabled" disabled>{{__('CLOCK IN')}}</button> --}}
                                            @endif
                                            {{Form::close()}}

                                            @if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                                {{Form::model($employeeAttendance,array('route'=>array('attendanceemployee.update',$employeeAttendance->id),'method' => 'PUT')) }}
                                                <button type="submit" value="1" name="out" id="clock_out" class="mt-2 btn btn-danger">{{__('CLOCK OUT')}}</button>
                                            @else
                                                {{-- <button type="submit" value="1" name="out" id="clock_out" class="btn btn-danger disabled" disabled>{{__('CLOCK OUT')}}</button> --}}
                                            @endif
                                            {{Form::close()}}

                                            <!-- Take a Break Button -->
                                    
                                            @if(!empty($employeeAttendance) && $employeeAttendance->clock_out == '00:00:00')
                                                <button id="take_break" class="mt-2 btn btn-warning" style="margin-left: 10px;">
                                                    {{ __('TAKE A BREAK') }}
                                                </button>
                                            @else
                                                {{-- <button id="take_break" class="btn btn-warning disabled d-none" disabled>
                                                    {{ __('TAKE A BREAK') }}
                                                </button> --}}
                                            @endif

                                        </div>
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
                    <div class="col-xxl-12">
                        <div class="card list_card">
                            <div class="card-header">
                                <h4>{{__('Announcement List')}}</h4>
                            </div>
                            <div class="card-body dash-card-body">
                                <div class="table-responsive">
                                    <table class="table mb-0 table-striped">
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
                                    <div class="mt-4 row">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-users"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Total Staff')}}</p>
                                                    <h4 class="mb-0 text-success">{{ $countUser +   $countClient}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="my-3 col-md-6 col-sm-6 my-sm-0">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Total Employee')}}</p>
                                                    <h4 class="mb-0 text-info">{{$countUser}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Total Client')}}</p>
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
                                    <div class="mt-4 row">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-award"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Total Jobs')}}</p>
                                                    <h4 class="mb-0 text-success">{{$activeJob + $inActiveJOb}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="my-3 col-md-6 col-sm-6 my-sm-0">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-check"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Active Jobs')}}</p>
                                                    <h4 class="mb-0 text-info">{{$activeJob}}</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="ti ti-x"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Inactive Jobs')}}</p>
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
                                    <div class="mt-4 row">
                                        <div class="col-md-6 col-sm-6">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-primary">
                                                    <i class="ti ti-users"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Total Training')}}</p>
                                                    <h4 class="mb-0 text-success">{{ $onGoingTraining +   $doneTraining}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="my-3 col-md-6 col-sm-6 my-sm-0">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-info">
                                                    <i class="ti ti-user"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Trainer')}}</p>
                                                    <h4 class="mb-0 text-info">{{$countTrainer}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-danger">
                                                    <i class="ti ti-user-check"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Active Training')}}</p>
                                                    <h4 class="mb-0 text-danger">{{$onGoingTraining}}</h4>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-sm-6">
                                            <div class="mb-3 d-flex align-items-start">
                                                <div class="theme-avtar bg-secondary">
                                                    <i class="ti ti-user-minus"></i>
                                                </div>
                                                <div class="ms-2">
                                                    <p class="mb-0 text-sm text-muted">{{__('Done Training')}}</p>
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
@endsection


