{{ Form::open(array('route' => array('bill.debit.note',$bill_id),'mothod'=>'post')) }}
<div class="modal-body">
    <div class="row">
        <div class="form-group col-md-6">
            {{ Form::label('date', __('Date'),['class'=>'form-label']) }}<span class="text-danger">*</span>
            {{Form::date('date',null,array('class'=>'form-control','required'=>'required'))}}
        </div>
        <div class="form-group col-md-6">
            {{ Form::label('return_type', __('Return Type'),['class'=>'form-label']) }}
            {{ Form::select('return_type',['' => 'Select Type', 'Reusable' => 'Reusable', 'Expired' => 'Expired'],null, array('class' => 'form-control','required'=>'required')) }}
        </div>
    </div>

    @foreach($billDue->items as $key => $iteam)
        <div class="row">
            <div class="form-group col-md-6">
                {{ Form::text('productName', $iteam->product->name, array('class' => 'form-control','required'=>'required', 'readonly' => true)) }}
                <input type="hidden" name="product_id[]" value="{{ $iteam->product->id }}">
            </div>
            <div class="form-group col-md-6">
                {{ Form::number('qty[]', 0, array('class' => 'form-control', 'step'=>'1', 'min' => 0, 'required'=>'required')) }}
            </div>
        </div>
    @endforeach

    <div class="form-group">
        {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
        {!! Form::textarea('description', null, ['class'=>'form-control','rows'=>'2' , 'placeholder' => __('Enter Description')]) !!}
    </div>
</div>
<div class="modal-footer">
    <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
    <button type="submit" class="btn btn-primary">{{ __('Add') }}</button>
</div>
{{ Form::close() }}
