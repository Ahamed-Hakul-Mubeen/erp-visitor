{{ Form::open(['route' => ['project.attachment.store', $project->id], 'files' => true]) }}
<div class="modal-body px-3">
    <div class="form-group">
        {{ Form::label('name', __('Name'), ['class' => 'form-label']) }}
        {{ Form::text('name', null, ['class' => 'form-control', 'required' => 'required']) }}
        @error('name')
            <span class="invalid-name" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </span>
        @enderror
    </div>
    <div class="form-group">
        {{ Form::label('file', __('Upload File'), ['class' => 'form-label']) }}
        {{ Form::file('file', ['class' => 'form-control', 'required' => 'required']) }}
        @error('file')
            <span class="invalid-file" role="alert">
                <strong class="text-danger">{{ $message }}</strong>
            </span>
        @enderror
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{ __('Cancel') }}" class="btn btn-light" data-bs-dismiss="modal">
    <input type="submit" value="{{ __('Create') }}" class="btn btn-primary">
</div>
{{ Form::close() }}
