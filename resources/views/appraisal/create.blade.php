
{{ Form::open(['url' => 'appraisal', 'method' => 'post']) }}
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('branch', __('Company'), ['class' => 'form-label']) }}<span class="text-danger">*</span>

                <select name="branch" id="branch" required class="form-control ">
                    <option selected disabled value="0">{{__('Select Company')}}</option>

                    @foreach ($brances as $value)
                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>


        <div class="mt-2 col-md-6">
            <div class="form-group">
                {{ Form::label('employee', __('Employee'), ['class' => 'form-label']) }}<span class="text-danger">*</span>

                <div class="employee_div">

                    <select name="employee" id="employee" class="form-control " required>
                    </select>
                </div>
            </div>
        </div>


        <div class="col-md-6">
            <div class="form-group">
                {{ Form::label('appraisal_date', __('Select Month'), ['class' => 'col-form-label']) }}<span class="text-danger">*</span>
                {{ Form::month('appraisal_date', '', ['class' => 'form-control ','autocomplete'=>'off' ,'required' => 'required']) }}
            </div>
        </div>
        <div class="col-md-12">
            <div class="form-group">
                {{ Form::label('remark', __('Remarks'), ['class' => 'col-form-label']) }}
                {{ Form::textarea('remark', null, ['class' => 'form-control', 'rows' => '3','placeholder'=>'Enter remark']) }}
            </div>
        </div>
    </div>
    <div class="row" id="stares">
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="Cancel" class="btn btn-light" data-bs-dismiss="modal">
    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
</div>
{{ Form::close() }}



<script>

    $('#employee').change(function(){

        var emp_id = $('#employee').val();
        $.ajax({
            url: "{{ route('empByStar') }}",
            type: "post",
            data:{
                "employee": emp_id,
                "_token": "{{ csrf_token() }}",
            },

            cache: false,
            success: function(data) {
                $('#stares').html(data.html);
            }
        })
    });
</script>

<script>
    $('#branch').on('change', function() {
        var branch_id = this.value;

        $.ajax({
            url: "{{ route('getemployee') }}",
            type: "post",
            data:{
                "branch_id": branch_id,
                "_token": "{{ csrf_token() }}",
            },

            cache: false,
            success: function(data) {

                $('#employee').html('<option value="">Select Employee</option>');
                $.each(data.employee, function (key, value) {
                    $("#employee").append('<option value="' + value.id + '">' + value.name + '</option>');
                });

            }
        })


    });
</script>







