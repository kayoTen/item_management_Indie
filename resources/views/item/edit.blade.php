@extends('adminlte::page')

@section('title', '商品編集・削除')

@section('content_header')
<h1>商品編集・削除</h1>
@stop

@section('content')
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
            <form method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">名前</label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="名前" value={{ old('name', $itemdetail[0]['name']) }}>
                    </div>

                    <div class="form-group">
                        <label for="type">種別</label>
                        <input type="number" class="form-control" id="type" name="type" placeholder="1, 2, 3, ...">
                    </div>

                    <div class="form-group">
                        <label for="detail">詳細</label>
                        <input type="text" class="form-control" id="detail" name="detail" placeholder="詳細説明" value={{ old('detail', $itemdetail[0]['detail'])}}>
                    </div>

                    <input type="hidden" name="id" id="id" value="{{ old('id',$itemdetail[0]['id'])}}" />
                    <input type="hidden" name="user_id" id="user_id" value={{ old('user_id',$itemdetail[0]['user_id'])}} />

                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary" name="edit" id="edit" value="編集" onclick="input_form.action_key.value='edit'">編集</button>
                    <button type="submit" class="btn btn-primary" name="delete" id="delete" value="削除" onclick="input_form.action_key.value='delete'">削除</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

<script>
    window.onload = () => {
        if (<?php echo $val = count($itemdetail) > 0 ? "true" : "false"; ?>) {
            document.querySelector("#type").value = <?php echo $result = count($itemdetail) > 0 ? $itemdetail[0]['type'] : 0; ?>
        }
    }

    function confirm_action() {
        const action_key = document.querySelector("#action_key").value;
        switch (action_key) {
            case "edit":
                return confirm(`入力内容で編集してもよろしいですか？`);
                break;

            case "delete":
                return confirm(`登録内容を削除してもよろしいですか？`);
                break;

            default:
                return null;
                break;
        }
    }
</script>