@extends('adminlte::page')

@section('title', '商品一括登録')

@section('content_header')
<h1>商品一括登録</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-10">
        @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card card-primary">
            <form method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="file">CSVファイル</label>
                        <input type="file" class="form-control" id="csvFile" name="csvFile" accept=".csv">
                        <label for="file"><a href="">sample.csv</a></label>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">一括登録</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
@stop