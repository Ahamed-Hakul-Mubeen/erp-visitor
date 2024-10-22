@extends('layouts.admin')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{__('Dashboard')}}</a></li>
    <li class="breadcrumb-item">{{__('Employment Status')}}</li>
@endsection

@section('action-btn')
    <div class="float-end">
        
            <a href="#" data-url="{{ route('employment_status.create') }}" data-ajax-popup="true" data-title="{{__('Create Employment Status')}}" data-bs-toggle="tooltip" title="{{__('Create')}}" class="btn btn-sm btn-primary">
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
                            <th>{{__('Preview')}}</th>
                            <th>{{__('Description')}}</th>
                            <th>{{__('Action')}}</th>
                        </tr>
                        </thead>
                        <tbody class="font-style">
                        @foreach ($statuses as $status)
                            <tr>
                                <!-- Display Name -->
                                <td>{{ $status->name }}</td>

                                <!-- Display Preview with color value -->
                                <td>
                                    <span class="badge bg-{{ strtolower($status->color_value) }} p-2 px-3 rounded">{{ $status->name }}</span>
                                </td>

                                <!-- Display Description -->
                                <td>{{ $status->description ?? '-' }}</td>

                                <!-- Action Buttons -->
                                <td class="Action">
                                    {{-- @can('edit employment status') --}}
                                    <div class="action-btn bg-primary ms-2">
                                        <a href="#" data-url="{{ URL::to('employment_status/'.$status->id.'/edit') }}" data-ajax-popup="true" data-title="{{__('Edit Employment Status')}}" class="mx-3 btn btn-sm align-items-center" data-bs-toggle="tooltip" title="{{__('Edit')}}"><i class="ti ti-pencil text-white"></i></a>
                                    </div>
                                    {{-- @endcan --}}

                                    {{-- @can('delete employment status') --}}
                                    <div class="action-btn bg-danger ms-2">
                                        {!! Form::open(['method' => 'DELETE', 'route' => ['employment_status.destroy', $status->id], 'id'=>'delete-form-'.$status->id]) !!}
                                        <a href="#" class="mx-3 btn btn-sm align-items-center bs-pass-para" data-bs-toggle="tooltip" title="{{__('Delete')}}" data-confirm="{{__('Are You Sure?').'|'.__('Do you want to Delete This?')}}" data-confirm-yes="document.getElementById('delete-form-{{$status->id}}').submit();"><i class="ti ti-trash text-white"></i></a>
                                        {!! Form::close() !!}
                                    </div>
                                    {{-- @endcan --}}
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
