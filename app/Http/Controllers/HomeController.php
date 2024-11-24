<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Xendit\Configuration;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        Configuration::setXenditKey("xnd_development_l7Uu6Pa5giLot6ZvxekDt6VLsX2p0qeZPi5j6n2GGLNC2JPDghBoRLedmYepzL");
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $products = Product::all();
        return view('welcome', compact('products'));
    }

    public function detail($id){
        $product = Product::find($id);

        return view('detail-product', compact("product"));
    }

    public function payment(Request $request) {
        // cek data product
        $product = Product::find($request->id);
        $uuid = (string) Str::uuid();

        // call xendit
        $apiInstance = new InvoiceApi();
        $createInvoice = new CreateInvoiceRequest([
            'external_id' => $uuid,
            'description' => $product->description,
            'amount' => $product->price,
            'currency' => 'IDR',
            "customer" => array(
                "given_names" => $request->name,
                "email" => $request->email,
            ),
            "success_redirect_url" => "http://belajar_laravel.test/",
            "failure_redirect_url" => "http://belajar_laravel.test/",
        ]);


        try {
            $result = $apiInstance->createInvoice($createInvoice);
            // insert ke table orders
            $order = new Order();
            $order->product_id = $product->id;
            $order->checkout_link = $result["invoice_url"];
            $order->external_id = $uuid;
            $order->status = "pending";
            $order->save();

            return redirect($result["invoice_url"]);
                
        } catch (\Xendit\XenditSdkException $e) {
            echo 'Full Error: ', json_encode($e->getFullError()), PHP_EOL;
        }
    }

    public function notification($id) {
        $apiInstance = new InvoiceApi();
        
        $result = $apiInstance->getInvoices(null, $id);

        // get data
        $order = Order::where('external_id', $id)->firstOrFail();

        if($order->status == 'settled') {
            return response()->json('payment anda berhasil diproses');
        }

        // update status
        $order->status = $result[0]['status'];
        $order->save();

        return response()->json('success');
    }
}
