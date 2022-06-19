@extends('cms.layouts.master')
@section('title', 'List of recent files')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    Files <sub><i class="bg bg-danger">Each file is valid for 5 minutes since its created time</i></sub>
                </h3>
            </div><!-- /.card-header -->
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-bordered">
                    <thead>
                        <tr style="background-color: #4681AF; color: white">
                            <th>File Name</th>
                            @if(auth()->user()->hasRole(\Bkstar123\BksCMS\AdminPanel\Role::SUPERADMINS))
                                <th>Created By</th>
                            @endif
                            <th>Created (UTC+7)</th>
                            <th>Updated (UTC+7)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>
                                <a href="{{ route('get-file', [
                                        'disk' => $report->disk,
                                        'path' => $report->path,
                                        'name' => $report->name,
                                        'contentType' => $report->mime
                                    ]) }}">
                                    {{ $report->name }}
                                </a>
                            </td>
                            @if(auth()->user()->hasRole(\Bkstar123\BksCMS\AdminPanel\Role::SUPERADMINS))
                                <td>{{ $report->admin->email }}</td>
                            @endif
                            <td>
                                {{ $report->created_at }}
                            </td>
                            <td>
                                {{ $report->updated_at }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div><!-- /.card-body -->
        </div><!-- /.card -->
        Shows {{ $reports->count() }} result(s)
        {{ $reports->links() }}
    </div>
</div>
@endsection