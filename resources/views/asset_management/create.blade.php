{{ Form::open(array('url' => 'asset_management', 'method' => 'post')) }}
<div class="modal-body">
    <div class="row">
        <div class="col-12">
             <div class="form-group">
                {{ Form::label('product_name', __('Assets Type'), ['class' => 'form-label']) }}
                {{ Form::select('product_name', $productTypes, null, ['class' => 'form-control', 'placeholder' => __('Select Assets Type')]) }}
                @error('product_name')
                <span class="invalid-product_name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('product_description', __('Product Name'), ['class' => 'form-label']) }}
                {{ Form::text('product_description', null, ['class' => 'form-control', 'placeholder' => __('Enter Product Name')]) }}
                @error('product_description')
                <span class="invalid-product_description" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('product_configuration', __('Product Configuration'), ['class' => 'form-label']) }}
                {{ Form::textarea('product_configuration', null, ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Enter Product Configuration')]) }}
                @error('product_configuration')
                <span class="invalid-product_configuration" role="alert">
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