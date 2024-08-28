
{{ Form::open(['route' => 'zoom-meeting.store','id'=>'store-user','method'=>'post']) }}
<div class="modal-body">
    {{-- start for ai module--}}
    @php
        $plan= \App\Models\Utility::getChatGPTSettings();
    @endphp
    @if($plan->chatgpt == 1)
    <div class="text-end">
        <a href="#" data-size="md" class="btn btn-primary btn-icon btn-sm" data-ajax-popup-over="true" data-url="{{ route('generate',['zoom meeting']) }}"
           data-bs-placement="top" data-title="{{ __('Generate content with AI') }}">
            <i class="fas fa-robot"></i> <span>{{__('Generate with AI')}}</span>
        </a>
    </div>
    @endif
    {{-- end for ai module--}}
    <div class="row">
        <div class="form-group col-md-12">
            {{ Form::label('title', __('Title') ,['class'=>'form-label'])}}
            {{ Form::text('title', null, ['class' => 'form-control', 'placeholder' => __('Enter Meeting Title'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('projects', __('Project'),['class'=>'form-label'])}}
            {{ Form::select('project_id', $projects, null, ['class' => 'form-control select project_select', 'id' => 'project_select', 'data-toggle' => 'select']) }}
        </div>
        <div class="form-group col-md-6" >
            {{ Form::label('projects', __('Users'),['class'=>'form-label'])}}
            <div  id="user_div">
            <select class="form-control select employee_select" id="user_id" name="user_id[]" >
                <option value="">{{__('Select User')}}</option>
            </select>
            </div>
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('datetime', __('Start Date / Time'),['class'=>'form-label'])}}
            {{ Form::datetimeLocal('start_date',null,['class' => 'form-control date', 'placeholder' => __('Select Date/Time'), 'required' => 'required']) }}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('duration', __('Duration'),['class'=>'form-label'])}}
            {{ Form::number('duration',null,['class' => 'form-control', 'placeholder' => __('Enter Duration in minutes'), 'required' => 'required']) }}
        </div>

        <div class="form-group col-md-6">
            {{ Form::label('password', __('Password ( Optional )'),['class'=>'form-label'])}}
            {{ Form::password('password',['class' => 'form-control', 'placeholder' => __('Enter Password')]) }}
        </div>

        @if(isset($settings['google_calendar_enable']) && $settings['google_calendar_enable'] == 'on')
            <div class="form-group col-md-6">
                {{Form::label('synchronize_type',__('Synchronize in Google Calendar ?'),array('class'=>'form-label')) }}
                <div class="form-switch">
                    <input type="checkbox" class="mt-2 form-check-input" name="synchronize_type" id="switch-shadow" value="google_calender">
                    <label class="form-check-label" for="switch-shadow"></label>
                </div>
            </div>
        @endif

        <div class="form-group col-md-6">
            <div class="form-switch form-switch-right">
                <input class="form-check-input" type="checkbox" name="client_id" id="client_id" checked>
                <label class="form-check-label" for="client_id">{{__('Invite Client For Zoom Meeting')}}</label>
            </div>
        </div>
    </div>
</div>

<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <button type="submit" class="btn btn-primary">{{__('Create')}}</button>
</div>
{{ Form::close() }}


<script type="text/javascript">
    $(document).on('change', '.project_select', function () {

        var project_id = $(this).val();

        getparent(project_id);
    });
    function getparent(bid) {

        $.ajax({
            url: `{{ url('zoom-meeting/projects/select')}}/${bid}`,
            type: 'GET',
            success: function (data) {
                $("#user_div").html('');
                $('#user_div').append('<select class="form-control " id="user_id" name="user_id[]"  multiple></select>');

                $.each(data, function (i, item) {

                    $('#user_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                });

                var multipleCancelButton = new Choices('#user_id', {
                    removeItemButton: true,
                });

                if (data == '') {
                    $('#user_id').empty();
                }
            }
        });
    }


</script>







