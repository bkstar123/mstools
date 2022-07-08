@extends('cms.layouts.master')
@section('title', 'List of trackings')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Trackings 
                </h3>
                @can('trackings.massiveDestroy')
                    {{ CrudView::removeAllBtn(route('trackings.massiveDestroy')) }}
                @else
                    <button class="btn btn-danger" disabled>
                        Remove all
                    </button>
                @endcan
                <div class="card-tools">
                    {{ CrudView::searchInput(route('trackings.index')) }}
                </div>
            </div><!-- /.card-header -->
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr style="background-color: #4681AF; color: white">
                            <th>
                                {{ CrudView::checkAllBox('danger') }}
                            </th>
                            <th>Sites</th>
                            <th>Tracked by</th>
                            <th>Status</th>
                            <th>Action</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trackings as $tracking)
                        <tr>
                            <td>
                                {{ CrudView::checkBox($tracking, 'danger') }}
                            </td>
                            <td>
                                {{ $tracking->sites }}
                            </td>
                            <td>
                                {{ $tracking->admin->email }}
                            </td>
                            <td>
                                @if($tracking->status)
                                    @can('trackings.off', $tracking)
                                    {{ CrudView::activeStatus($tracking, route('trackings.off', [
                                        'tracking' => $tracking->id
                                        ]), '', 'ON') }}
                                    @else
                                    <button class="btn btn-success" disabled>
                                        Active
                                    </button>
                                    @endcan
                                @else
                                    @can('trackings.on', $tracking)
                                    {{ CrudView::disabledStatus($tracking, route('trackings.on', [
                                        'tracking' => $tracking->id
                                        ]), '', 'OFF') }}
                                    @else
                                    <button class="btn btn-secondary" disabled>
                                        Disabled
                                    </button>
                                    @endcan
                                @endif
                            </td>
                            <td>
                                @can('trackings.destroy', $tracking)
                                {{ CrudView::removeBtn($tracking, route('trackings.destroy', [
                                    'tracking' => $tracking->id
                                    ])) }}
                                @else
                                <button class="btn btn-danger" disabled>
                                    Remove
                                </button>
                                @endcan
                            </td>
                            <td>
                                {{ $tracking->created_at }}
                            </td>
                            <td>
                                {{ $tracking->updated_at }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div><!-- /.card-body -->
        </div><!-- /.card -->
        Shows {{ $trackings->count() }} result(s)
        {{ $trackings->links() }}
    </div>
</div>
@endsection