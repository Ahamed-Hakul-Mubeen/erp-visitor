{{ Form::open(array('url' => 'social_link', 'method' => 'post')) }}
<div class="modal-body">

    <div class="row">
        <!-- Social Link Name Input -->
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('name', __('Social Link Name'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Social Link Name'), 'required' => 'required']) }}
                @error('name')
                <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>
    </div>

</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <button type="submit" class="btn btn-primary">{{ __('Create') }}</button>
</div>
{{ Form::close() }}
