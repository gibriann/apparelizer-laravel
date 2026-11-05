<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Models\StatusUser;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function adminAccount() 
    {
        return view('pages.admin-new.dashboard-account');
    }

    public function adminList()
    {
        $admins = User::where('role_id', 1)->get();
        return view('pages.admin-new.dashboard-admins', compact('admins'));
    }

    public function customerList()
    {
        $customers = User::where('role_id', 3)->get();
        return view('pages.admin-new.dashboard-customers', compact('customers'));
    }

    public function adminCreate()
    {
        return view('pages.admin-new.dashboard-admins-add');
    }

    public function adminStore(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'email' => 'required|unique:users,email',
        ];
        
        $customMessage = [
            'required' => 'Silahkan masukan :attribute',
            'unique' => ':attribute telah digunakan'
        ];
        
        $attributeName = [
            'name' => 'Nama',
            'email' => 'Email',
        ];

        $this->validate($request, $rules, $customMessage, $attributeName);

        User::create([
            'name' => $request->name,
            'role_id' => 1,
            'user_status_id' => 1,
            'email' => $request->email,
            'password' => Hash::make(123456),
        ]);

        Alert::success('Berhasil','Admin berhasil ditambahkan');
        return redirect(route('dashboard-admins'));
    }

    public function adminUpdate(Request $request, $id)
    {
        $user = User::find($id);
        $rules = [
            'name' => 'required|max:255',
            'email' => "required|unique:users,email,$user->id",
        ];
        
        $customMessage = [
            'required' => 'Silahkan masukan :attribute',
            'unique' => ':attribute telah digunakan'
        ];
        
        $attributeName = [
            'name' => 'Nama',
            'email' => 'Email',
        ];

        $this->validate($request, $rules, $customMessage, $attributeName);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        Alert::success('Berhasil','Admin berhasil diubah');
        return redirect(route('dashboard-admins'));
    }

    public function updateStatusUser($id)
    {
        $user = User::find($id);
        if ($user->user_status_id == 1) {
            $user->update([
                'user_status_id' => 2,
            ]);
            Alert::success('Berhasil','Admin dinon-aktifkan');
        }elseif ($user->user_status_id == 2) {
            $user->update([
                'user_status_id' => 1,
            ]);
            Alert::success('Berhasil','Admin diaktifkan');
        }
        if ($user->role_id == 1) {
            return redirect(route('dashboard-admins'));
        } else {
            return redirect(route('dashboard-customers'));
        }
        
    }

    public function account() 
    {
        return view('pages.account');
    }

    protected function update(Request $request)
    {
        $user= User::where('id', Auth::user()->id)->first();
        $userId= $user->id;


        if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2 ){
            $rules = [
                'name' => 'required|max:255',
                'email' => "required|max:255|unique:users,email,$userId",
            ];
        }elseif (Auth::user()->role_id == 3) {
            $customer = Customer::where('user_id', Auth::user()->id)->first();
            $customerId = $customer->id;
            $rules = [
                'name' => 'required|max:255',
                'email' => "required|max:255|unique:users,email,$userId",
                'alamat' => 'required|max:255',
                'postalCode'=> 'required|integer',
                'phone_number' => "required|min:11|max:13|unique:customers,phone_number,$customerId"
            ];
        }
        
        $customMessage = [
            'required' => 'Silahkan Masukan :attribute',
            'unique' => ':attribute Telah Digunakan',
            'min' => ':attribute minimal :min',
            'max' => ':attribute maksimal :max',
        ];
        
        $attributeName = [
            'name' => 'Nama',
            'email' => 'Email',
            'alamat' => 'Alamat',
            'postalCode'=> 'Kode POS',
            'phone_number' => 'Nomor Telepon',
        ];

        $this->validate($request, $rules, $customMessage, $attributeName);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        if (Auth::user()->role_id == 3) {
            $customer = Customer::where('user_id', Auth::user()->id)->first();
            $customer->update([
                'address' => $request->alamat,
                'postal_code' =>  $request->postalCode,
                'phone_number' => $request->phone_number,
            ]);
        }

        Alert::success('Berhasil','Data akun telah diperbaharui');
        if(Auth::user()->role_id == 1 || Auth::user()->role_id == 2) {
            return redirect(route('dashboard-account'));
        }elseif (Auth::user()->role_id == 3) {
            return redirect(route('account'));
        }
    }
}
