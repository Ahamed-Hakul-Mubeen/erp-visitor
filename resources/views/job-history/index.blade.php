@extends('layouts.admin')

@section('content')
<div class="container">
    <h5>{{ __('Company Timeline') }}</h5>

    <div class="row mb-4">
        <div class="col-md-4">
            <label for="employee_filter">{{ __('Filter by Employee') }}</label>
            <select class="form-control mt-2" id="employee_filter" name="employee_filter" onchange="filterTimelineByEmployee()">
                <option value="">{{ __('Select an Employee') }}</option>
                @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ request()->get('employee_id') == $employee->id ? 'selected' : '' }}>
                        {{ $employee->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    @if ($selectedEmployeeId)
        @if($timelineEvents->isEmpty())
            <p>{{ __('No timeline events available for this employee.') }}</p>
        @else
            <div class="timeline">
                @foreach($timelineEvents as $event)
                    <div class="timeline-item">
                        <div class="timeline-date">
                            {{ \Carbon\Carbon::parse($event->promotion_date)->format('d M Y') }}
                        </div>
                        <div class="timeline-content">
                            <h5>{{ $event->employee->name ?? '--' }} 
                                - {{ $event->promotion_title === 'Joined the Company' ? __('Joining Date') : __('Promotion') }}
                            </h5>
                            <p>
                                <strong>{{ __('Previous Designation:') }}</strong> {{ $event->previous_designation_name ?? '--' }}<br>
                                <strong>{{ __('New Designation:') }}</strong> {{ $event->promotion_title }}<br>
                            </p>
                            <!-- Use null coalescing operator for description -->
                            <p>{{ $event->description ?? __('No description available') }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @else
        <p>{{ __('Please select an employee to view their Job History.') }}</p>
    @endif
</div>
@endsection

<style>
   .timeline {
    position: relative;
    margin: 20px 0;
    padding: 0;
    list-style: none;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #007bff;
    left: 50%;
    margin-left: -2px;
}

.timeline-item {
    position: relative;
}

.timeline-item:nth-child(odd) {
    padding-left: 51%;
}

.timeline-item:nth-child(even) {
    padding-right: 51%;
    text-align: right;
}

.timeline-item::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    width: 12px;
    height: 12px;
    background: #007bff;
    border-radius: 50%;
    margin-left: -6px;
    z-index: 1;
}

.timeline-item .timeline-date {
    position: absolute;
    top: 10px;
    left: calc(50% - 100px);
    width: 100px;
    text-align: center;
    font-weight: bold;
    font-size: 14px;
}

.timeline-item:nth-child(even) .timeline-date {
    left: auto;
    right: calc(50% - 100px);
}

.timeline-item .timeline-content {
    padding: 15px;
    background: #fff;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    position: relative;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    font-size: 14px;
    transition: all 0.3s ease;
}

.timeline-item:hover .timeline-content {
    background-color: #f8f9fa;
    transform: translateY(-5px);
}

.timeline-item:nth-child(even) .timeline-content {
    text-align: left;
}
</style>

@push('script-page')
<script type="text/javascript">
function filterTimelineByEmployee() {
    const employeeId = document.getElementById('employee_filter').value;
    const url = new URL(window.location.href);
    if (employeeId) {
        url.searchParams.set('employee_id', employeeId);
    } else {
        url.searchParams.delete('employee_id');
    }
    window.location.href = url.href;
}
</script>
@endpush
