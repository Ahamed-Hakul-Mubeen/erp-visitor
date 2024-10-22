{{ Form::open(array('url' => 'employment_status', 'method' => 'post')) }}
<div class="modal-body">

    <div class="row">
        <!-- Name Input -->
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => __('Enter Employment Status Name'), 'required' => 'required']) }}
                @error('name')
                <span class="invalid-name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>

        <!-- Color Value Dropdown -->
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('color_value', __('Color Value'), ['class' => 'form-label']) }}<span class="text-danger">*</span>
                {{ Form::select('color_value', [
                    'primary' => __('Primary'),
                    'success' => __('Success'),
                    'info' => __('Info'),
                    'warning' => __('Warning'),
                    'danger' => __('Danger'),
                    'purple' => __('Purple'),
                ], null, ['class' => 'form-control', 'placeholder' => __('Select a color value'), 'required' => 'required']) }}
                @error('color_value')
                <span class="invalid-color_value" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>

        <!-- Description Input -->
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('description', __('Description'), ['class' => 'form-label']) }}
                {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('Add description here'), 'rows' => 3]) }}
                @error('description')
                <span class="invalid-description" role="alert">
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
