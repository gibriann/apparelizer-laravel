<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\Purchase;
use App\Models\PurchasedProduct;
use Error;

use Exception;

use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Config;
use RealRashid\SweetAlert\Facades\Alert;

class CartController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $carts = Cart::where('user_id', auth()->user()->id)->get();
        if ($carts->isEmpty()) {
            Alert::toast('Keranjang kosong, silahkan belanja', 'error');
            return redirect('/products');
        } else{
            $custDetails = Customer::where('user_id', auth()->user()->id)->first();
            $total_val = [];
            foreach ($carts as $cart) {
                array_push($total_val, $cart->products->product_price * $cart->amount);
                
            }
            $total_order = array_sum($total_val);
            return view('pages.cart', compact('carts', 'custDetails', 'total_order'));
        }
    }

    public function buy()
    {
        
        $carts = Cart::where('user_id', auth()->user()->id)->get();
        if ($carts->isEmpty()) {
            Alert::toast('Keranjang kosong, silahkan belanja', 'error');
            return redirect('/products');
        } else{
            $custDetails = Customer::where('user_id', auth()->user()->id)->first();
            $total_weight = 0;
            $dataprovinsis = $this->province();
            $total_val = [];
            foreach ($carts as $cart) {
                $weight = $cart->products->product_weight * $cart->amount;
                $total_weight += $weight;

                array_push($total_val, $cart->products->product_price * $cart->amount);
            }
            $total_order = array_sum($total_val);
            return view('pages.shipment', compact('carts', 'custDetails', 'dataprovinsis', 'total_weight', 'total_order'));
        }
    }

    public function store(Request $request, $id, $slug )
    {
        $rules = [
            'size_id' => 'required',
            'color_id' => 'required',
            'printing_id' => 'required',
            'amount' => 'required|integer|between:6,150',
            'link_design' => 'required'
        ];
        
        $customMessage = [
            'required' => 'Silahkan masukan :attribute',
            'between' => ':attribute hanya :min - :max.',
        ];
        
        $attributeName = [
            'size_id' => 'ukuran',
            'color_id' => 'warna',
            'printing_id' => 'jenis sablon',
            'amount' => 'jumlah',
            'link_design' => 'link desain',
        ];

        $this->validate($request, $rules, $customMessage, $attributeName);

        $product = Product::where('id', $id)->where('slug', $slug)->first();
        $cart = Cart::where([
            'user_id' => auth()->user()->id,
            'product_id' => $product->id,
            'size_id' => $request->size_id,
            'color_id' => $request->color_id,
            'link_design' => $request->link_design,
            'printing_id' => $request->printing_id
        ])->first();

        if ($cart == null){
            Cart::create([
                'user_id' => auth()->user()->id,
                'product_id' => $product->id,
                'size_id' => $request->size_id,
                'color_id' => $request->color_id,
                'printing_id' => $request->printing_id,
                'amount' => $request->amount,
                'link_design' => $request->link_design,
            ]);
            Alert::success('Berhasil', 'Produk telah ditambahkan ke keranjang!');
            return redirect('/cart');
        } else {
            $amount = $cart->amount + $request->amount;
            $cart->update([
                'amount' => $amount
            ]);
            return redirect('/cart');
        }
    }
    
    public function storePayment(Request $request){
        $rules = [
            'user_name' => 'required',
            'phone_number' => 'required',
            'province_name' => 'required',
            'district_name' => 'required',
            'address' => 'required',
            'postal_code' => 'required',
            'courier_name' => 'required',
            'service_name' => 'required'
        ];
        
        $customMessage = [
            'required' => 'Silahkan masukan :attribute',
        ];
        
        $attributeName = [
            'user_name' => 'Nama penerima',
            'phone_number' => 'Nomor Telepon',
            'province_name' => 'Provinsi',
            'district_name' => 'Kota/Kabupaten',
            'address' => 'Alamat',
            'postal_code' => 'Kode Pos',
            'courier_name' => 'Kurir pilihan',
            'service_name' => 'Paket pilihan'
        ];

        $this->validate($request, $rules, $customMessage, $attributeName);
        
        $code = 'FMS-' . mt_rand(00000, 99999);
        $purchase = Purchase::create([
            'user_id' => auth()->user()->id,
            'shipping_status_id' => 1,
            'payment_status_id' => 1,
            'total_payment' => $request->totalnya,
            'total_weight' => $request->total_berat,
            'shipment_fee' => $request->ongkir,
            'shipment_receipt' => null,
            'recipient_address' => $request->address,
            'recipient' => $request->user_name,
            'recipient_number' =>$request->phone_number,
            'province' => $request->provinsi,
            'district' => $request->distrik,
            'postal_code' => $request->postal_code,
            'courier' => $request->ekspedisi,
            'service' => $request->paket,
            'etd' => $request->estimasi,
            'notes' => $request->notes,
            'code' => $code
        ]);

        $carts = Cart::where('user_id', auth()->user()->id)->get();
        foreach ($carts as $cart) {
            PurchasedProduct::create([
                'purchase_id' => $purchase->id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->amount,
                'sub_weight' => $cart->products->product_weight,
                'total_order' => $cart->products->product_price * $cart->amount,
                'product_name' => $cart->products->product_name,
                'size_id' => $cart->size_id,
                'color_id' => $cart->color_id,
                'printing_id' => $cart->printing_id,
                'product_design' => $cart->link_design,
            ]);
        }

        Cart::where('user_id', Auth::user()->id)->delete();

        // Midtrans Configuration
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        // Sending data to Midtrans
        $midtrans = [
            'transaction_details' => [
                'order_id' => $code,
                'gross_amount' => $request->totalnya,
            ],
            'customer_details' => [
                'first_name' => $request->user_name,
                'email' => auth()->user()->email,
                'phone' => $request->phone_number,
            ],
            'vtweb' => []
        ];

        try {
            $paymentUrl = Snap::createTransaction($midtrans)->redirect_url;
            Purchase::where('id', $purchase->id)->update([
                'payment_url' => $paymentUrl,
            ]);
            header('Location: ' . $paymentUrl);
        } catch (Exception $e) {
            echo $e->getMessage();
        }


        return redirect($paymentUrl);
    }

    public function callback(Request $request) {
        // Set konfigurasi midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Buat instance midtrans notification

        
        // Assign ke variable untuk memudahkan coding
        $notification_body = json_decode($request->getContent(), true);
        $order_id = $notification_body['order_id'];
        $type = $notification_body['payment_type'];
        $fraud = $notification_body['fraud_status'];
        $status = $notification_body['transaction_status'];
        

        // Cari transaksi berdasarkan ID
        $purchase = Purchase::where('code', $order_id)->first();

        // Handle notification status midtrans
        if ($status == 'capture') {
            if ($type == 'credit_card'){
                if($fraud == 'challenge'){
                    $purchase->payment_status_id = 3;
                }
                else {
                    $purchase->payment_status_id = 2;
                }
            }
        }
        else if ($status == 'settlement'){
            $purchase->payment_status_id = 2;
            $purchase->shipping_status_id = 2;
        }
        else if($status == 'pending'){
            $purchase->payment_status_id = 1;
        }
        else if ($status == 'deny') {
            $purchase->payment_status_id = 3;
            $purchase->shipping_status_id = 6;

        }
        else if ($status == 'expire') {
            $purchase->payment_status_id = 3;
            $purchase->shipping_status_id = 6;
        }
        else if ($status == 'cancel') {
            $purchase->payment_status_id = 3;
            $purchase->shipping_status_id = 6;
        }

        // Simpan transaksi
        $purchase->save();

        // Kirimkan email
        if ($purchase)
        {
            if($status == 'capture' && $fraud == 'accept' )
            {
                //
            }
            else if ($status == 'settlement')
            {
                //
            }
            else if ($status == 'success')
            {
                //
            }
            else if($status == 'capture' && $fraud == 'challenge' )
            {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'message' => 'Midtrans Payment Challenge'
                    ]
                ]);
            }
            else
            {
                return response()->json([
                    'meta' => [
                        'code' => 200,
                        'message' => 'Midtrans Payment not Settlement'
                    ]
                ]);
            }

            return response()->json([
                'meta' => [
                    'code' => 200,
                    'message' => 'Midtrans Notification Success'
                ]
            ]);
        }
    }

    public function province()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/province",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "                "key: " . config('rajaongkir.api_key')"
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            echo "cURL Error #:" . $err;
            } else {
        
            $array_response = json_decode($response, TRUE);
            $dataprovinsi = $array_response["rajaongkir"]["results"];
        }
        return $dataprovinsi;
    }

    public function district($idProvinsi) 
    {
        
        $id_provinsi_terpilih = $idProvinsi;
        // dd($id_provinsi_terpilih);
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/city?province=".$id_provinsi_terpilih,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "                "key: " . config('rajaongkir.api_key')"
            ),
        ));
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        $array_response = json_decode($response, TRUE);
        $datadistrik = $array_response["rajaongkir"]["results"];


        return response()->json([
            'datadistrik' => $datadistrik
        ]);
        
    }

    public function options($ekspedisi, $idDistrik, $berat) 
    {
        
        $ekspedisi_terpilih = $ekspedisi;
        $distrik_terpilih = $idDistrik;
        $total_berat = $berat;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.rajaongkir.com/starter/cost",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "origin=37&destination=".$distrik_terpilih."&weight=".$total_berat."&courier=".$ekspedisi_terpilih,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded",
                "                "key: " . config('rajaongkir.api_key')"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        $array_response = json_decode($response,TRUE);
        $paket = $array_response["rajaongkir"]["results"]["0"]["costs"];
        // var_dump($paket); exit;

        return response()->json([
            'paket' => $paket
        ]);
    }

    public function delete($id){
        $cart = Cart::find($id);
        if ($cart->user_id==auth()->user()->id) {
            Cart::destroy($id);

            Alert::toast('Berhasil Menghapus', 'success');
            return redirect(route('cart'));
        } else {
            Alert::toast('Gagal Menghapus', 'error');
            return redirect(route('cart'));
        }
    }
}
