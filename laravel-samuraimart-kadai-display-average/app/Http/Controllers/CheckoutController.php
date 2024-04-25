<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class CheckoutController extends Controller
{
    public function index() //注文内容の確認ページを表示する（CartControllerのindexアクションと同じ処理）
    {
        $cart = Cart::instance(Auth::user()->id)->content();

        $total = 0;
        $has_carriage_cost = false;
        $carriage_cost = 0;

        foreach ($cart as $c) {
            $total += $c->qty * $c->price;
            if ($c->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        if ($has_carriage_cost) {
            $total += env('CARRIAGE');
            $carriage_cost = env('CARRIAGE');
        }

        return view('checkout.index', compact('cart', 'total', 'carriage_cost'));
    }

    public function store(Request $request) //Stripe APIに支払情報を送信し、Stripeの決済ページにリダイレクトさせる
    {
        $cart = Cart::instance(Auth::user()->id)->content();

        $has_carriage_cost = false;

        foreach ($cart as $product) {
            if ($product->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        Stripe::setApiKey(env('STRIPE_SECRET'));

        $line_items = [];

        foreach ($cart as $product) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $product->name,
                    ],
                    'unit_amount' => $product->price,
                ],
                'quantity' => $product->qty,
            ];
        }

        if ($has_carriage_cost) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => '送料',
                    ],
                    'unit_amount' => env('CARRIAGE'),
                ],
                'quantity' => 1,
            ];
        }

        $checkout_session = Session::create([ //stripe-phpライブラリが提供するメソッドでStripeに送信する支払情報をセッションとして作成
            'line_items' => $line_items, //支払い対象となる商品：カート内の商品および送料（送料は必要な場合）
            'mode' => 'payment', //支払モード：一回限りの支払い
            'success_url' => route('checkout.success'), //決済性工事のリダイレクト先URL：決済完了後の案内ページ
            'cancel_url' => route('checkout.index'), //決済キャンセル時のリダイレクト先URL：注文内容の確認ページ
        ]);

        return redirect($checkout_session->url);
    }

    public function success() //決済完了後の案内ページを表示する
    {
        $user_shoppingcarts = DB::table('shoppingcart')->get();
        $number = DB::table('shoppingcart')->where('instance', Auth::user()->id)->count();

        $count = $user_shoppingcarts->count();

        $count += 1;
        $number += 1;
        $cart = Cart::instance(Auth::user()->id)->content();

        $price_total = 0;
        $qty_total = 0;
        $has_carriage_cost = false;

        foreach ($cart as $c) {
            $price_total += $c->qty * $c->price;
            $qty_total += $c->qty;
            if ($c->options->carriage) {
                $has_carriage_cost = true;
            }
        }

        if ($has_carriage_cost) {
            $price_total += env('CARRIAGE');
        }

        Cart::instance(Auth::user()->id)->store($count);

        DB::table('shoppingcart')->where('instance', Auth::user()->id)->where('number', null)->update(
            [
                'code' => substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 10),
                'number' => $number,
                'price_total' => $price_total,
                'qty' => $qty_total,
                'buy_flag' => true,
                'updated_at' => date("Y/m/d H:i:s")
            ]
        );

        Cart::instance(Auth::user()->id)->destroy();

        return view('checkout.success');
    }
}
