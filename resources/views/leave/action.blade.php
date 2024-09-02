{{Form::open(array('url'=>'leave/changeaction','method'=>'post', 'id' => 'changeaction_form'))}}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
                <table class="table modal-table">
                    <tr role="row">
                        <th>{{__('Employee')}}</th>
                        <td>{{ !empty($employee->name)?$employee->name:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Leave Type ')}}</th>
                        <td>{{ !empty($leavetype->title)?$leavetype->title:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Appplied On')}}</th>
                        <td>{{\Auth::user()->dateFormat( $leave->applied_on) }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Start Date')}}</th>
                        <td>{{ \Auth::user()->dateFormat($leave->start_date) }}</td>
                    </tr>
                    <tr>
                        <th>{{__('End Date')}}</th>
                        <td>{{ \Auth::user()->dateFormat($leave->end_date) }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Leave Reason')}}</th>
                        <td>{{ !empty($leave->leave_reason)?$leave->leave_reason:'' }}</td>
                    </tr>
                    <tr>
                        <th>{{__('Status')}}</th>
                        <td>{{ !empty($leave->status)?$leave->status:'' }}</td>
                    </tr>
                    <input type="hidden" value="{{ $leave->id }}" name="leave_id">
                </table>
        </div>
    </div>
</div>
{{-- @if(\Auth::user()->type == 'company' || Auth::user()->type == 'HR' ) --}}
@if(\Auth::user()->type == 'company' )
<div class="modal-footer">
    <input type="hidden" name="status" id="status_id">
    <input type="button" id="approval_btn" class="btn btn-success" value="Approval">
    <input type="button" id="reject_btn" class="btn btn-danger" value="Reject">
</div>
@endif
{{Form::close()}}
<script>
$(document).on("click", "#approval_btn", function(e) {
    $("#status_id").val("Approval");
    $("#changeaction_form").submit();
});
$(document).on("click", "#reject_btn", function(e) {
    $("#status_id").val("Reject");
    $("#changeaction_form").submit();
});
</script>