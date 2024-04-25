@extends('layouts.app')

@section('content')
<div class="container pt-5">
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <h1 class="mb-5">ご注文内容</h1>

            <h5 class="fw-bold mb-3">購入商品</h5>

            <div class="row justify-content-between">
                <div class="col-lg-7">
                    <hr class="mt-0 mb-4">

                    <div class="mb-5">
                        @if ($cart->isEmpty())
                            <div class="row">
                                <p class="mb-0">カートの中身は空です。</p>
                            </div>
                        @else
                            @foreach ($cart as $product)
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-3">
                                        <a href="{{ route('products.show', $product->id) }}">
                                            @if ($product->options->image)
                                                <img src="{{ asset($product->options->image) }}" class="img-thumbnail">
                                            @else
                                                <img src="{{ asset('img/dummy.png')}}" class="img-thumbnail">
                                            @endif
                                        </a>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="flex-column">
                                            <p class="fs-5 mb-2">
                                                <a href="{{ route('products.show', $product->id) }}" class="link-dark text-decoration-none">{{ $product->name }}</a>
                                            </p>
                                            <div class="row mb-2">
                                                <div class="col-xxl-3">
                                                    数量：{{ number_format($product->qty) }}
                                                </div>
                                                <div class="col-xxl-9">
                                                    合計：￥{{ number_format($product->qty * $product->price) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <h5 class="fw-bold mb-3">お届け先</h5>

                    <hr class="mb-4">

                    <div class="mb-5">
                        <p class="mb-2">{{ Auth::user()->name }} 様</p>
                        <p class="mb-2">〒{{ Auth::user()->postal_code . ' ' . Auth::user()->address }}</p>
                        <p class="mb-2">{{ Auth::user()->phone }}</p>
                        <p>{{ Auth::user()->email }}</p>
                    </div>
                </div>
                <div class="col col-xxl-4">
                    <div class="bg-white p-4 mb-4">
                        <div class="row mb-2">
                            <div class="col-md-5">
                                小計
                            </div>
                            <div class="col-md-7">
                                ￥{{ number_format($total - $carriage_cost) }}
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-5">
                                送料
                            </div>
                            <div class="col-md-7">
                                ￥{{ number_format($carriage_cost) }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-5">
                                <span class="fs-5 fw-bold">合計</span>
                            </div>
                            <div class="col-7 d-flex align-items-center">
                                <span class="fs-5 fw-bold">￥{{ number_format($total) }}</span><span class="small">（税込）</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        @if ($total > 0)
                            <form action="{{ route('checkout.store') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn samuraimart-submit-button text-white w-100">お支払い</a>
                            </form>
                        @else
                            <button class="btn samuraimart-submit-button disabled w-100">お支払い</button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection