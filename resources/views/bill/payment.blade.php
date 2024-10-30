{{ Form::open(array('route' => array('bill.payment', $bill->id),'method'=>'post','enctype' => 'multipart/form-data')) }}
    <div class="modal-body">
        <div class="row">
            @if ($vender->debit_balance > 0)
                <div class="form-group col-md-12">
                    {{ Form::label('debit_balance', __('Debit Balance'), ['class' => 'form-label']) }}
                    <select class="form-control select" name="debit_balance" id="debit_balance">
                        <option value="No">Don't use Debit Balance</option>
                        <option value="Yes" data-amount="{{ $vender->debit_balance }}">Use
                            {{ Auth::user()->priceFormat($vender->debit_balance) }} Debit Balance</option>
                    </select>
                </div>
            @endif

            <div class="form-group col-md-6">
                {{ Form::label('date', __('Date'),['class'=>'form-label']) }}
                {{ Form::date('date', '', array('class' => 'form-control ','required'=>'required')) }}

            </div>
            <div class="form-group col-md-6">
                {{ Form::label('amount', __('Amount'),['class'=>'form-label']) }}
                {{ Form::number('amount',$bill->getDue(), array('class' => 'form-control','required'=>'required','step'=>'0.01')) }}

            </div>
            <div class="form-group col-md-6">
                {{ Form::label('account_id', __('Account'),['class'=>'form-label']) }}
                {{ Form::select('account_id',$accounts,null, array('class' => 'form-control ', 'id' => 'account_id','required'=>'required')) }}
            </div>
            <div class="form-group col-md-6">
                {{ Form::label('reference', __('Reference'),['class'=>'form-label']) }}
                {{ Form::text('reference', '', array('class' => 'form-control')) }}

            </div>
            <div class="form-group col-md-12">
                {{ Form::label('description', __('Description'),['class'=>'form-label']) }}
                {{ Form::textarea('description', '', array('class' => 'form-control','rows'=>3)) }}
            </div>


            <div class="col-md-6 form-group">
                {{ Form::label('add_receipt', __('Payment Receipt'), ['class' => 'form-label']) }}
                <div class="choose-file ">
                    <label for="file" class="form-label">
                        <input type="file" name="add_receipt" id="image" class="form-control" >
                    </label>
                    <p class="upload_file"></p>
                </div>
            </div>

        </div>
        <div class="modal-footer">

            <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
            <button type="submit" class="btn btn-primary">{{ __('Add') }}</button>
        </div>

    </div>
{{ Form::close() }}

<script>
    
    $(document).ready(function() {
        $("#debit_balance").change(function() {
            var debit_balance = $("#debit_balance").val();
            var amount = $("#debit_balance option:selected").attr('data-amount');
            if (debit_balance == "Yes") {
                $("#amount").val(amount);
                $('#account_id').prop('disabled', true);
            }
        });
    });
</script>