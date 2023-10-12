@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="panel-body">

                <form id="form" enctype="multipart/form-data" class="form-horizontal">
                    @csrf
                    <div class="form-group">
                        <label for="task" class="text-xl font-extrabold ">Select File</label>
                        <input type="file" name="file" id="upload-file">
                        <button type="submit" class="border border-solid text-gray-500 border-gray-500 p-2 rounded-sm">
                            Upload CSV
                        </button>

                    </div>

                </form>

                <div class="col-12 mt-20">
                    <table class="table table-striped table-bordered table-responsive">
                        <thead>
                            <tr>
                                <td class="">Time</td>
                                <td class="">File Name</td>
                                <td class="">Status</td>
                            </tr>
                        </thead>
                        <tbody id="upload-tbody">
                            @if (count($data) < 1)
                                <tr>
                                    <td colspan="3">
                                        <p style="text-align: center;padding-top:15px; color:#aaa">No Record.</p>
                                    </td>
                                </tr>
                            @endif
                            @foreach ($data ?? [] as $key => $value)
                                <tr>
                                    <td>
                                        {{ $value->created_at->diffForHumans() }}
                                    </td>
                                    <td>
                                        {{ $value->original_name }}
                                    </td>
                                    <td>
                                        <span>
                                            @if ($value['progress']->percentage < 1)
                                                Pending
                                            @elseif ($value['progress']->percentage == 100)
                                                Completed
                                            @else
                                                Processing
                                            @endif
                                        </span>

                                        <span id="percent{{ $value->id }}">
                                            @if ($value['progress']->percentage < 100)
                                                {{ $value['progress']->percentage }}
                                                %
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
