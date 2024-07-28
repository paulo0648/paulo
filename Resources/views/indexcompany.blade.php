@extends('general.index', $setup)
@section('thead')
    <th>{{ __('Name') }}</th>
    <th>{{ __('Description') }}</th>
    <th>{{ __('Activ') }}</th>
    <th>{{ __('crud.actions') }}</th>
@endsection
@section('tbody')
    @foreach ($setup['items'] as $item)
        <tr>
            <td>{{ $item->name }}</td>
            <td>{{ $item->description }}</td>
            <td>{{ $item->getConfig($companyID.'_activ','false')=="true"?__('Yes'):__('No') }}</td>
            <td>

                <!-- Docs -->
                <a target="_blank" href="{{$item->documentation_url}}" class="btn btn-info btn-sm">
                    {{ __('Documentation')}}
                </a>

                <!-- Config -->
                <a href="{{ route('flowisebots.configure',['bot'=>$item->id]) }}" class="btn btn-primary btn-sm">
                    {{ __('Configure')}}
                </a>

                @if ($companyID==$item->company_id)
                    <!-- EDIT -->
                    <a href="{{ route('flowisebots.edit',['bot'=>$item->id]) }}" class="btn btn-primary btn-sm">
                        <i class="ni ni-ruler-pencil"></i>
                    </a>
                    <!-- Delete -->
                    <a href="{{ route('flowisebots.delete',['bot'=>$item->id]) }}" class="btn btn-danger btn-sm">
                        <i class="ni ni ni-fat-remove"></i>
                    </a>
                @endif
                
            </td>
        </tr> 
    @endforeach
@endsection