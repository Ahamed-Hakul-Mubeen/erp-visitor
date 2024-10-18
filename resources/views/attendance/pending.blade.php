@extends('layouts.admin')
@section('page-title')
    {{__('Pending Attendance List')}}
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{route('dashboard')}}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Pending Attendance')}}</li>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-border-style">
                    <div class="table-responsive">
                        <table class="table datatable">
                            <thead>
                            <tr>
                                @if(\Auth::user()->type!='Employee')
                                    <th>{{__('Employee')}}</th>
                                @endif
                                <th>{{__('Date')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>{{__('Clock In')}}</th>
                                <th>{{__('Clock Out')}}</th>
                                <th>{{__('Late')}}</th>
                                <th>{{__('Early Leaving')}}</th>
                                <th>{{__('Overtime')}}</th>
                                <th>{{__('Break Time')}}</th>
                                <th>{{__('Work From Home')}}</th>
                                <th>{{__('Approval Status')}}</th>
                                @if((Gate::check('edit attendance') || Gate::check('delete attendance')) && (\Auth::user()->type!='Employee'))
                                    <th>{{__('Action')}}</th>
                                @endif
                            </tr>
                            </thead>
                            <tbody>

                            @foreach ($attendanceEmployee as $attendance)
                                <tr>
                                    @if(\Auth::user()->type!='Employee')
                                        <td>{{!empty($attendance->employee)?$attendance->employee->name:'' }}</td>
                                    @endif
                                    <td>{{ \Auth::user()->dateFormat($attendance->date) }}</td>
                                    <td>{{ $attendance->status }}</td>
                                    <td>
                                        {{ ($attendance->clock_in !='00:00:00') ?\Auth::user()->timeFormat( $attendance->clock_in):'00:00' }}
                                    </td>
                                    <td>{{ ($attendance->clock_out !='00:00:00') ?\Auth::user()->timeFormat( $attendance->clock_out):'00:00' }}</td>
                                    <td>{{ $attendance->late }}</td>
                                    <td>{{ $attendance->early_leaving }}</td>
                                    <td>{{ $attendance->overtime }}</td>
                                    <td>{{ $attendance->total_break_duration }}</td>
                                    <td>
                                        @if($attendance->work_from_home == 1)
                                        <div>
                                            <span class="p-2 px-3 rounded badge bg-danger">{{ __('Yes') }}</span>
                                        </div>
                                        @else
                                        <div>
                                            <span class="p-2 px-3 rounded badge bg-primary">{{ __('No') }}</span>
                                        </div>
                                        @endif
                                    </td>
                                    <td>{{ $attendance->approval_status}}</td>

                                    @if(Gate::check('edit attendance') && (\Auth::user()->type!='Employee') && $attendance->approval_status == "Pending")
                                        <td>
                                            @can('edit attendance')
                                            <div class="action-btn bg-warning ms-2">
                                                <a href="#"
                                                data-url="{{ URL::to('attendance/'.$attendance->id.'/action') }}"
                                                data-size="lg"
                                                data-ajax-popup="true"
                                                data-title="{{__('Attendance Action')}}"
                                                class="mx-3 btn btn-sm align-items-center"
                                                data-bs-toggle="tooltip"
                                                title="{{__('Attendance Action')}}">
                                                    <i class="text-white ti ti-caret-right"></i>
                                                </a>
                                            </div>
                                            @endcan
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
