{{Form::open(array('url'=>'attendancerequest/changeaction','method'=>'post', 'id' => 'changeaction_form'))}}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <table class="table modal-table">
                <tr>
                    <th>{{__('Are you approve this attendace request')}}</th>
                </tr>
                <input type="hidden" value="{{ $attendance_request->id }}" name="attendance_request_id">
            </table>
        </div>
    </div>
</div>

@if(\Auth::user()->type != 'Employee')
<div class="modal-footer">
        <input type="hidden" name="status" id="status_id">
        <input type="button" id="approval_btn" class="btn btn-success" value="Approval">
        <input type="button" id="_reject_btn" class="btn btn-danger" value="Reject">
</div>
@endif
{{Form::close()}}

<script>
$(document).on("click", "#approval_btn", function(e) {
    $("#status_id").val("Approved");
    $("#approval_type").val("Final");
    $("#changeaction_form").submit();
});
$(document).on("click", "#_reject_btn", function(e) {
    $("#status_id").val("Rejected");
    $("#approval_type").val("Final");
    $("#changeaction_form").submit();
});
</script>
