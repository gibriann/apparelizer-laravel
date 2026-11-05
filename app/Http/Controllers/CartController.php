<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
            $customer = Customer::where('user_id', auth()->user()->id)->first();

            $total_weight = 0;
            foreach ($carts as $cart) {
                $total_weight += $cart->products->product_weight * $cart->amount;
                
            }
            $total_val = [];
            foreach ($carts as $cart) {
                array_push($total_val, $cart->products->product_price * $cart->amount);
                
            }
            $total_order = array_sum($total_val);
            return view('pages.shipment', compact('carts', 'customer', 'total_order', 'total_weight'));
        }
    }

    public function store(Request $request, $id, $slug)
    {
        $product = Product::where('id', $id)->first();
        $carts = Cart::where('user_id', auth()->user()->id)->where('product_id', $id)->first();

        if ($carts) {
            if ($request->amount != null) {
                $carts->amount = $carts->amount + $request->amount;
            } else {
                $carts->amount = $carts->amount + 1;
            }
            if (!empty($request->file('linkdesain'))) {
                $imageName = time() . '.' . $request->linkdesain->extension();
                $request->linkdesain->move(public_path('images'), $imageName);
                $carts->link_design = 'images/' . $imageName;
            }
            $carts->update();

        } else {

            $imageName = '';
            if (!empty($request->file('linkdesain'))) {
                $imageName = time() . '.' . $request->linkdesain->extension();
                $request->linkdesain->move(public_path('images'), $imageName);
            }


            Cart::create([
                'user_id' => auth()->user()->id,
                'product_id' => $id,
                'amount' => $request->amount != null ? $request->amount : 1,
                'link_design' => $imageName != '' ? 'images/' . $imageName : null,
                'size_id' => $request->size_id,
                'color_id' => $request->color_id,
                'printing_type_id' => $request->printing_id,
                'notes' => $request->notes ? $request->notes : null
            ]);
        }

        Alert::toast('Berhasil ditambah ke keranjang', 'success');
        return redirect()->back();
    }

    public function delete($id)
    {
        $carts = Cart::where('id', $id)->first();
        $carts->delete();
        Alert::toast('Berhasil dihapus dari keranjang', 'success');
        return redirect()->back();
    }

    public function storePayment(Request $request)
    {
        $code = 'TRX-' . mt_rand(00000, 99999);
        $carts = Cart::where('user_id', Auth::user()->id)->get();
        
        // Calculate total weight
        $total_weight = 0;
        foreach ($carts as $cart) {
            $total_weight += $cart->products->product_weight * $cart->amount;
        }

        $purchase = Purchase::create([
            'code' => $code,
            'total_payment' => $request->totalnya,
            'total_weight' => $total_weight,
            'shipment_fee' => $request->ongkir,
            'shipping_status_id' => 1,
            'payment_status_id' => 1,
            'user_id' => Auth::user()->id,
            'recipient' => $request->user_name,
            'recipient_address' => $request->address,
            'recipient_number' => $request->phone_number,
            'province' => $request->kota ?? 'N/A',
            'district' => $request->kota ?? 'N/A',
            'postal_code' => $request->postal_code ?? '00000',
            'courier' => $request->kurir,
            'service' => $request->kurir_name,
            'etd' => $request->etd ?? '-',
            'notes' => $request->notes,
        ]);

        foreach ($carts as $cart) {
            PurchasedProduct::create([
                'purchase_id' => $purchase->id,
                'product_id' => $cart->product_id,
                'quantity' => $cart->amount,
                'size_id' => $cart->size_id,
                'color_id' => $cart->color_id,
                'printing_id' => $cart->printing_type_id,
                'sub_weight' => $cart->products->product_weight * $cart->amount,
                'total_order' => $cart->products->product_price * $cart->amount,
                'product_design' => $cart->link_design ?? '',
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

            return redirect($paymentUrl);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function success()
    {

        $purchases = Purchase::where('user_id', auth()->user()->id)->orderBy('created_at', 'desc')->first();


        return view('pages.success', compact('purchases'));
    }

    public function callback(Request $request)
    {
        // Set konfigurasi midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');

        // Instance midtrans notification
        $notification = new Notification();

        // Assign ke variable untuk memudahkan 
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;

        // Cari transaksi berdasarkan ID
        $transaction = Purchase::where('transaction_code', $order_id)->first();

        // Handle notification status midtrans
        if ($status == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $transaction->payment_status_id = 1;
                } else {
                    $transaction->payment_status_id = 2;
                }
            }
        } else if ($status == 'settlement') {
            $transaction->payment_status_id = 2;
        } else if ($status == 'pending') {
            $transaction->payment_status_id = 1;
        } else if ($status == 'deny') {
            $transaction->payment_status_id = 1;
        } else if ($status == 'expire') {
            $transaction->payment_status_id = 1;
        } else if ($status == 'cancel') {
            $transaction->payment_status_id = 1;
        }

        // Simpan transaksi
        $transaction->save();

        // Kirim email midtrans
        if ($transaction) {
            if ($status == 'capture' && $fraud == 'accept') {
                //
            } else if ($status == 'settlement') {
                //
            } else if ($status == 'pending') {
                //
            } else if ($status == 'deny') {
                //
            } else if ($status == 'expire') {
                //
            } else if ($status == 'cancel') {
                //
            }
        }

        // Retun response untuk midtrans
        return response()->json([
            'meta' => [
                'code' => 200,
                'message' => 'Midtrans Notification Success'
            ]
        ]);
    }

    /**
     * Search domestic destination using RajaOngkir API V2
     * New endpoint: GET /destination/domestic-destination
     */
    public function searchDestination(Request $request)
    {
        $search = $request->input('search', '');
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);

        try {
            $response = Http::withHeaders([
                'key' => config('rajaongkir.api_key')
            ])->get('https://rajaongkir.komerce.id/api/v1/destination/domestic-destination', [
                'search' => $search,
                'limit' => $limit,
                'offset' => $offset
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json($data);
            } else {
                return response()->json([
                    'meta' => [
                        'code' => $response->status(),
                        'message' => 'Failed to fetch destinations',
                        'status' => 'error'
                    ],
                    'data' => null
                ], $response->status());
            }
        } catch (Exception $e) {
            return response()->json([
                'meta' => [
                    'code' => 500,
                    'message' => $e->getMessage(),
                    'status' => 'error'
                ],
                'data' => null
            ], 500);
        }
    }

    /**
     * Calculate domestic shipping cost using RajaOngkir API V2
     * New endpoint: POST /calculate/domestic-cost
     */
    public function calculateCost(Request $request)
    {
        $validated = $request->validate([
            'origin' => 'required|integer',
            'destination' => 'required|integer',
            'weight' => 'required|integer|min:1',
            'courier' => 'required|string'
        ]);

        try {
            $response = Http::withHeaders([
                'key' => config('rajaongkir.api_key'),
                'Content-Type' => 'application/x-www-form-urlencoded'
            ])->asForm()->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin' => $validated['origin'],
                'destination' => $validated['destination'],
                'weight' => $validated['weight'],
                'courier' => $validated['courier'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json($data);
            } else {
                return response()->json([
                    'meta' => [
                        'code' => $response->status(),
                        'message' => 'Failed to calculate shipping cost',
                        'status' => 'error'
                    ],
                    'data' => null
                ], $response->status());
            }
        } catch (Exception $e) {
            return response()->json([
                'meta' => [
                    'code' => 500,
                    'message' => $e->getMessage(),
                    'status' => 'error'
                ],
                'data' => null
            ], 500);
        }
    }

    // Keep old methods for backward compatibility, but mark as deprecated
    // These will be removed in future versions
    
    /**
     * @deprecated Use searchDestination() instead
     */
    public function province()
    {
        // Redirect to new search-based method
        return $this->searchDestination(new Request());
    }

    /**
     * @deprecated Use searchDestination() instead
     */
    public function district($idProvinsi) 
    {
        return response()->json([
            'meta' => [
                'code' => 410,
                'message' => 'This endpoint is deprecated. Please use the new search-based destination API.',
                'status' => 'error'
            ],
            'data' => null
        ], 410);
    }

    /**
     * @deprecated Use calculateCost() instead
     */
    public function options($ekspedisi, $idDistrik, $berat) 
    {
        return response()->json([
            'meta' => [
                'code' => 410,
                'message' => 'This endpoint is deprecated. Please use the new calculate cost API.',
                'status' => 'error'
            ],
            'data' => null
        ], 410);
    }
}
