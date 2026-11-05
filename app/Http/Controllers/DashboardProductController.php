<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Intervention\Image\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;


class DashboardProductController extends Controller
{
    public function index()
    {
        $products = Product::paginate(8);
        return view('pages.admin-new.dashboard-products', compact('products'));
    }

    public function details($id)
    {
        $product = Product::find($id);
        return view('pages.admin-new.dashboard-products-detail', compact('product'));
    }

    public function add()
    {
        return view('pages.admin-new.dashboard-products-add');
        
    }

    public function store(Request $request) {
        // return $request;
        $rules = [
            'product_name' => 'required|max:255',
            'product_image' => 'required|mimes:png',
            'product_price' => 'required|max:255',
            'product_weight' => 'required',
            'product_description' => 'required'
        ];

        $customMessage = [
            'required' => 'Silahkan masukan :attribute',
            'mimes' => 'Harus dengan format .png'
        ];
        
        $attributeName = [
            'product_name' => 'nama produk',
            'product_image' => 'gambar produk',
            'product_price' => 'harga',
            'product_weight' => 'berat',
            'product_description' => 'deskripsi',
        ];

        $this->validate($request, $rules, $customMessage, $attributeName);

        $uploadFolder = 'foto_produk';
        $image = $request->file('product_image');
        $ogImage = $image->getClientOriginalName();
        $imageExtension = pathinfo($ogImage, PATHINFO_EXTENSION);
        $imageName = Str::random(30);
        $image_path =  'public/'.$uploadFolder.'/'.$imageName.'.'.$imageExtension;
        if (!Storage::disk('public')->exists($uploadFolder)) {
            Storage::disk('public')->makeDirectory($uploadFolder);
        }  
        $image = Image::make($image)->resize(1280, NULL, function($constraint){
            $constraint->aspectRatio();
        })->save(storage_path('app/'.$image_path));

        $slug = Str::of($request->product_name)->slug('-');

        Product::create([
            'product_name' => $request->product_name,
            'product_image' => $image_path,
            'product_price' => $request->product_price,
            'product_weight' => $request->product_weight,
            'product_description' => $request->product_description,
            'slug' => $slug,
        ]);

        Alert::success('Berhasil');
        return redirect(route('dashboard-products'));
    }

    public function update(Request $request, $id) {
        $product = Product::find($id);

        $rules = [
            'product_name' => 'required|max:255',
            'product_price' => 'required|max:255',
            'product_weight' => 'required',
            'product_description' => 'required'
        ];

        
        $customMessage = [
            'required' => 'Silahkan masukan :attribute',
        ];
        
        $attributeName = [
            'product_name' => 'nama produk',
            'product_price' => 'harga',
            'product_weight' => 'berat',
            'product_description' => 'deskripsi',
        ];

        $this->validate($request, $rules, $customMessage, $attributeName);

        $slug = Str::of($request->product_name)->slug('-');

        $product->update([
            'product_name' => $request->product_name,
            'product_price' => $request->product_price,
            'product_weight' => $request->product_weight,
            'product_description' => $request->product_description,
            'slug' => $slug,
        ]);

        if($request->hasFile('product_image')){
            $rules = [
                'product_image' => 'required|mimes:png',
            ];
    
            
            $customMessage = [
                'required' => 'Silahkan masukan :attribute',
                'mimes' => 'Harus dengan format .png'
            ];
            
            $attributeName = [
                'product_image' => 'gambar produk',
            ];

            $this->validate($request, $rules, $customMessage, $attributeName);

            $image = str_replace('public', 'storage', $product->product_image);
            if(is_file($image)){
                unlink($image);
                $uploadFolder = 'foto_produk';
                $image = $request->file('product_image');
                $ogImage = $image->getClientOriginalName();
                $imageExtension = pathinfo($ogImage, PATHINFO_EXTENSION);
                $imageName = Str::random(30);
                $image_path =  'public/'.$uploadFolder.'/'.$imageName.'.'.$imageExtension;
                if (!Storage::disk('public')->exists($uploadFolder)) {
                    Storage::disk('public')->makeDirectory($uploadFolder);
                }  
                $image = Image::make($image)->resize(1280, NULL, function($constraint){
                    $constraint->aspectRatio();
                })->save(storage_path('app/'.$image_path));
                $product->update([
                    'product_image' => $image_path,
                ]);
            }else {
                Alert::toast('Error', 'error');
            }
        }

        Alert::success('Berhasil memperbarui produk');
        return redirect(route('dashboard-products'));
    }

    public function delete($id){
        $product = Product::find($id);
        $image = str_replace('public','storage',$product->product_image);
        // dd($image);
        if(is_file($image)){
            unlink($image);;
        }else{
            Alert::toast('Error','error');
        }
        Product::destroy($id);
        Alert::success('Success!','Produk telah berhasil dihapus');
        return redirect(route('dashboard-products'));
    }
}
