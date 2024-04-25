@extends('layouts.app')

@section('content')
<div class="container pt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <h1 class="text-center mb-3">ご注文ありがとうございます！</h1>
      
      <p class="text-center lh-lg mb-5">
        商品が到着するまでしばらくお待ち下さい。
      </p>

      <div class="text-center">
        <a href="{{ url('/') }}" class="btn samuraimart-submit-button w-75 text-white">トップページへ</a>
      </div>
    </div>
  </div>
</div>
@endsection      