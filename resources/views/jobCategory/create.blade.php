
    {{Form::open(array('url'=>'job-category','method'=>'post'))}}
    <div class="modal-body">

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {{Form::label('title',__('Title'),['class'=>'form-label'])}}
                {{Form::text('title',null,array('class'=>'form-control','placeholder'=>__('Enter category title')))}}
            </div>
        </div>

    </div>
</div>
    <div class="modal-footer">
        <input type="button" value="{{__('Cancel')}}" class="btn btn-light" data-bs-dismiss="modal">
        <button type="submit" class="btn btn-primary">{{__('Create')}}</button>
    </div>
    {{Form::close()}}

