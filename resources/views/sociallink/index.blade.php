@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Social Links')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        <a href="#" data-url="{{ route('social_link.create') }}" data-ajax-popup="true" data-title="{{__('Create Social Link')}}" data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
            <i class="ti ti-plus"></i>
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-3">
        @include('layouts.hrm_setup') <!-- Sidebar inclusion if necessary -->
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-body table-border-style">
                <div class="table-responsive">
                    <table class="table datatable">
                        <thead>
                        <tr>
                            <th>{{__('Name')}}</th>
                            <th>{{__('Action')}}</th>
                        </tr>
                        </thead>
                        <tbody class="font-style">
                        @foreach ($social_links as $link)
                            <tr>
                            
                                <td>{{ $link->name }}</td>

                               
                                <td class="Action">
                                   
                                    <div class="action-btn bg-primary ms-2">
                                        <a href="#" data-url="{{ URL::to('social_link/'.$link->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Social Link')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                    </div>
                                  

                                  
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['social_link.destroy', $link->id], 'id'=>'delete-form-'.$link->id]) !!}
                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('Do you want to delete this?')}}" data-confirm-yes="document.getElementById('delete-form-{{$link->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                                        {!! Form::close() !!}
                                    </div>
                                    
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
