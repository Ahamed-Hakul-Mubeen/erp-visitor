{{ Form::model($asset, array('route' => array('asset_management.update', $asset->id), 'method' => 'PUT')) }}

<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="form-group">
                {{ Form::label('product_name', __('Assets Type'), ['class' => 'form-label']) }}
                {{ Form::select('product_name', $productTypes, $asset->product_type_id, ['id' => 'product_name', 'class' => 'form-control', 'placeholder' => __('Select Product Type')]) }}
                @error('product_name')
                <span class="invalid-product_name" role="alert">
                    <strong class="text-danger">{{ $message }}</strong>
                </span>
                @enderror
            </div>
        </div>

        <div class="col-12">
            <div id="asset_properties_section">
                <!-- Asset properties will be dynamically inserted here -->
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
    <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
</div>

{{ Form::close() }}

<script type="text/javascript">
    $(document).ready(function() {
        var productTypeId = $('#product_name').val(); // Get the currently selected product type
        var existingProperties = {!! $asset->asset_properties_values !!}; // Get existing asset property values

        if (productTypeId) {
            $.ajax({
                url: '{{ route('get.asset.properties') }}',
                type: 'GET',
                data: { product_type_id: productTypeId },
                success: function(response) {
                    var propertiesHtml = '';
                    $.each(response, function(index, property) {
                        var propertyValue = existingProperties[property] ? existingProperties[property] : ''; // Get existing value
                        propertiesHtml += `<div class="form-group">
                                                <div class="row">
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control" value="${property}" readonly />
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <input type="text" class="form-control" name="asset_property_values[${property}]" value="${propertyValue}" placeholder="Enter The value" />
                                                    </div>
                                                </div>
                                            </div>`;
                    });
                    $('#asset_properties_section').html(propertiesHtml);
                },
                error: function(error) {
                    console.error(error);
                }
            });
        }
    });
</script>