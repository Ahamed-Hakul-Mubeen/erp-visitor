
{{ Form::model($lead, array('route' => array('leads.discussion.store', $lead->id), 'method' => 'POST')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12 form-group">
            {{ Form::label('comment', __('Message'),['class'=>'form-label']) }}
            {{ Form::textarea('comment', null, array('class' => 'form-control')) }}
        </div>
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn  btn-light" data-bs-dismiss="modal">
    <button type="submit" class="btn btn-primary">{{ __('Add') }}</button>
</div>
{{Form::close()}}

