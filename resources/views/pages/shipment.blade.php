@extends('layouts.app')

@section('title')
    Firmos - Keranjang
@endsection

@push('fontawesome')
    <script src="https://kit.fontawesome.com/1fb5cee488.js" crossorigin="anonymous"></script>
@endpush

@section('content')
    <div class="page-content page-cart">
        <section class="store-breadcrumbs" >
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="/index.html">Beranda</a>
                                </li>
                                <li class="breadcrumb-item active">Keranjang</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </section>

        <section class="store-cart">
            <div class="container">
                <div class="row">
                    <div class="col-sm-6 order-12 order-sm-1">
                        <div class="row" >
                            
                            <div class="col-12">
                                <h2 class="mb-4">Detail Pengiriman</h2>
                            </div>
                        </div>
                        <!-- Pengiriman -->
                        <form action="{{ route('checkout') }}" method="POST">
                            @csrf
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_name">Nama Penerima</label>
                                        <input type="text" class="form-control @error('user_name') is-invalid @enderror" id="user_name" name="user_name" placeholder="{{ $custDetails->users['name'] }}" value="{{ $custDetails->users['name'] }}" />
                                        @error('user_name')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone_number">Nomor Telepon</label>
                                        <input type="number" class="form-control" id="phone_number" name="phone_number" placeholder="{{ $custDetails->phone_number }}" value="{{ $custDetails->phone_number }}" />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Provinsi</label>
                                        <select name="province_name" id="province" class="form-control @error('province_name') is-invalid @enderror" >
                                                <option value="">Pilih Provinsi</option>
                                            @foreach ($dataprovinsis as $provinces)
                                                <option value="{{ $provinces['province_id'] }}"> {{ $provinces['province'] }}</option>
                                            @endforeach
                                        </select>
                                        @error('province_name')
                                            <div class="invalid-feedback">
                                                {{$message}}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="district">Distrik</label>
                                        <select name="district_name" id="district" class="form-control">
                                            <option value="">Pilih Kabupaten/Kota</option>
                                    </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2" >
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address">Alamat</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="address"
                                            name="address"
                                            value="Jalan Jati Perwira No.26"
                                        />
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2" >
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="postal_code">Kode Pos</label>
                                        <input type="number" class="form-control" maxlength = "5" id="postal_code" name="postal_code"/>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="phone">Kurir</label>
                                        <select name="courier_name" id="courier_name" class="form-control">
                                            <option value="">Pilih Ekspedisi</option>
                                            <option value="pos">POS Indonesia</option>
                                            <option value="tiki">TIKI</option>
                                            <option value="jne">JNE</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="phone">Durasi</label>
                                        <select name="service_name" onchange="serviceChange()" id="service_name" class="form-control">
                                            
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-2" >
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes">Catatan</label>
                                        <input type="text" class="form-control" id="notes" name="notes"/>
                                    </div>
                                </div>
                            </div>
                            <div class="d-block d-sm-none">
                                <h2 class="mb-3">Informasi Pembayaran</h2>
                                <div class="row">
                                    <div class="col-8 col-sm-8 mt-md-2">
                                        <div class="product-subtitle">Total Pemesanan</div>
                                    </div>
                                    <div class="col-4 col-sm-4 ">
                                        <div id="total_order" class="product-title text-right">Rp {{ str_replace(',', '.', number_format($total_order))}}</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-8 col-sm-8 mt-md-2">
                                        <div class="product-subtitle">Ongkos Kirim</div>
                                    </div>
                                    <div class="col-4 col-sm-4">
                                        <div id="shippingCost" class="text-right" > - </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6 col-sm-6 mt-md-3 ">
                                        <div class="product-subtitle">Total Pembayaran</div>
                                    </div>
                                    <div class="col-6 col-sm-6">
                                        <div id="total_payment"  class="product-total text-success text-right">Rp {{ str_replace(',', '.', number_format($total_order))}}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <input type="hidden" name="total_berat" readonly value="{{ $total_weight }}">
                                {{-- <input type="hidden" name="total" readonly value="{{ $total_order }}"> --}}
                                <input type="hidden" name="provinsi" readonly>
                                <input type="hidden" name="distrik" readonly>
                                <input type="hidden" name="kodepos" readonly>
                                <input type="hidden" name="ekspedisi" readonly>
                                <input type="hidden" name="paket" readonly>
                                <input type="hidden" name="ongkir" readonly>
                                <input type="hidden" name="estimasi" readonly>
                                <input type="hidden" name="totalnya" readonly>

                                <div class="col-6 col-md-3 mt-3 mt-md-0 ml-auto">
                                    <button id="pay-button" type="submit" class="py-2 btn text-center btn-success btn-block text-white" > Bayar </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-sm-6 splitter order-1 order-sm-12">
                        <div class="row">
                        </div>
                        <div class="row" >
                            <div class="col-12 table-responsive pr-0">
                                <table class="table table-borderless table-cart">
                                    <thead>
                                        <tr>
                                            <td colspan="2">Detail Produk</td>
                                            <td>Berat </td>
                                            <td>Harga </td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($carts as $cart)
                                        <tr>
                                            <td class="text-center" style="width: 10%">
                                                <img style="height:50px;" src=" {{ asset(str_replace('public', 'storage', $cart->products->product_image)) }}" alt="">
                                            </td>
                                            <td style="width: 30%">
                                                <div class="product-title">{{ $cart->products->product_name }}</div>
                                                <div class="product-subtitle">{{ $cart->colors->color_name }}, {{ $cart->sizes->size_name }}</div>
                                            </td>
                                            <td style="width: 30%">
                                                <div class="product-title">{{ str_replace(',', '.', number_format($cart->products->product_weight * $cart->amount)) }}Gr</div>
                                                <div class="product-subtitle">({{ $cart->products->product_weight }}Gr/pcs)</div>
                                            </td>
                                            <td style="width: 30%">
                                                <div class="product-title">Rp {{ str_replace(',', '.', number_format($cart->products->product_price * $cart->amount))   }}</div>
                                                <div class="product-subtitle">Qty : {{  $cart->amount }}</div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <hr>
                        <div class="d-none d-sm-block">
                            <h2 class="mb-3">Informasi Pembayaran</h2>
                            <div class="row">
                                <div class="col-8 col-sm-8 mt-md-2">
                                    <div class="product-subtitle">Total Pemesanan</div>
                                </div>
                                <div class="col-4 col-sm-4 ">
                                    <div id="total_orderPC" class="product-title text-right">Rp {{ str_replace(',', '.', number_format($total_order))}}</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-8 col-sm-8 mt-md-2">
                                    <div class="product-subtitle">Ongkos Kirim</div>
                                </div>
                                <div class="col-4 col-sm-4">
                                    <div id="shippingCostPC" class="product-title text-right"> - </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6 col-sm-6 mt-md-3 ">
                                    <div class="product-subtitle">Total Pembayaran</div>
                                </div>
                                <div class="col-6 col-sm-6">
                                    <div id="total_paymentPC"  class="product-total text-success text-right">Rp {{ str_replace(',', '.', number_format($total_order))}}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    
    
@endsection

@push('addon-script')
    <script src="https://code.jquery.com/jquery-3.6.1.min.js" integrity="sha256-o88AwQnZB+VDvE9tvIXrMQaPlFFSUTR+nldQm1LuPXQ=" crossorigin="anonymous"></script>

    <script>
        function serviceChange() {
            var durasi = $("#service_name").find(":selected").val();
            var ongkir = formatRupiah(durasi, 'Rp ');
            $('#shippingCost').html(ongkir);
            $('#shippingCostPC').html(ongkir);

            var totalOrder = {{ $total_order }};
            var totalPayment = parseInt(durasi) + totalOrder;
            var totalPaymentView = (formatRupiah(totalPayment.toString(), 'Rp '));
            $("#total_payment").html(totalPaymentView);
            $("#total_paymentPC").html(totalPaymentView);
            $("input[name=totalnya]").val(totalPayment);
        }

        function formatRupiah(angka, prefix) {
			var number_string = angka.replace(/[^,\d]/g, '').toString(),
			split   		= number_string.split(','),
			sisa     		= split[0].length % 3,
			rupiah     		= split[0].substr(0, sisa),
			ribuan     		= split[0].substr(sisa).match(/\d{3}/gi);

			// tambahkan titik jika yang di input sudah menjadi angka ribuan
			if(ribuan){
				separator = sisa ? '.' : '';
				rupiah += separator + ribuan.join('.');
			}

			rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
			return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
		}

        $(document).ready(function(){
            $.ajax({
                type:'post',
                url:'dataprovinsi.php',
                success:function(hasil_provinsi)
                {
                    let provinceOptionsHtml = `<option value=''>Pilih Provinsi</option>`
                    $("select[name=province_name]").html(hasil_provinsi);
                }
            });

            //Pilih provinsi
            $("select[name=province_name]").on("change",function(){
                var id_provinsi_terpilih = $("option:selected",this).attr("value");
                //Hit API local -> yang nge hit -> raja ongkir ambil districts 
                $.ajax({
                    type:'get',
                    url:'/rajaongkir/provinsi/' + id_provinsi_terpilih + '/distrik',
                    data:'id_provinsi='+id_provinsi_terpilih,
                    success:function(hasil_distrik)
                    {
                        //Ngerapihin data dari API
                        const dataDistriks = hasil_distrik.datadistrik;

                        let districtOptionsHtml = `<option value=''>Pilih Kabupaten/Kota</option>`
                        dataDistriks.forEach(dataDistrik => {
                            districtOptionsHtml += `<option value="${dataDistrik.city_id}">${dataDistrik.type} ${dataDistrik.city_name}</option>`
                        });

                        //Masukkin ke dropdown district
                        $("select[name=district_name]").html(districtOptionsHtml);
                    }
                });
            });

            $.ajax({
                type:'post',
                url:'dataekspedisi.php',
                success:function(hasil_ekspedisi)
                {
                    $("select[name=courier_name]").html(hasil_ekspedisi);
                }
            });

            // Pilih ekspedisi
            $("select[name=courier_name]").on("change",function(){
                // mendapatkan ekspedisi yang dipilih
                var ekspedisi_terpilih = $("select[name=courier_name]").val();

                // mendapatkan id_distrik yang dilipih pengguna
                var distrik_terpilih = $("option:selected","select[name=district_name]").attr("value");

                // mendapatkan total berat dari inputan
                var total_berat = $("input[name=total_berat]").val();
                $.ajax({
                    type:'get',
                    url:'/rajaongkir/ekspedisi/' + ekspedisi_terpilih + '/distrik/' + distrik_terpilih + '/berat/' + total_berat,
                    data: 'ekspedisi='+ekspedisi_terpilih+'&distrik='+distrik_terpilih+'&berat='+total_berat,
                    success:function(hasil_paket)
                    {
                        const dataPakets = hasil_paket.paket;
                        
                        let paketOptionsHtml = `<option value=''> Pilih Paket </option>`
                        dataPakets.forEach(dataPaket => {
                            paketOptionsHtml += `<option value="${dataPaket["cost"]["0"]["value"]}" etd="${dataPaket["cost"]["0"]["etd"]}" paket="${dataPaket.service}"> ${dataPaket.service}  (${dataPaket["cost"]["0"]["etd"]}) </option>`
                        });
                        $("select[name=service_name]").html(paketOptionsHtml);
                        $("input[name=ekspedisi]").val(ekspedisi_terpilih);
                    }
                });
            });
            
            $("select[name=district_name]").on("change",function(){
                var prov = $("#province option:selected").text();
                var dist = $("#district option:selected").text();
                
                $("input[name=provinsi]").val(prov);
                $("input[name=distrik]").val(dist);
            });

            $("#postal_code").on("input", function(){
                if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);

                var postal_code = $("#postal_code").val();
                $("input[name=kodepos]").val(postal_code);
            });

            $("select[name=service_name]").on("change",function(){
                var paket = $("option:selected", this).attr("paket");
                var etd = $("option:selected", this).attr("etd");
                var durasi = $("#service_name").find(":selected").val();
                
                $("input[name=paket]").val(paket);
                $("input[name=estimasi]").val(etd);
                $("input[name=ongkir]").val(durasi);
            })
        });
    </script>

    <script type="text/javascript">
        // For example trigger on button clicked, or any time you need
        var payButton = document.getElementById('pay-button');
        payButton.addEventListener('click', function () {
        // Trigger snap popup. @TODO: Replace TRANSACTION_TOKEN_HERE with your transaction token
        window.snap.pay('TRANSACTION_TOKEN_HERE');
        // customer will be redirected after completing payment pop-up
        });
    </script>
@endpush
