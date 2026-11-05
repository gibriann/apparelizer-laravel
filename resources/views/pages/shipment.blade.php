@extends('layouts.app')

@section('title')
    Firmos - Pengiriman
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
                                    <a href="/">Beranda</a>
                                </li>
                                <li class="breadcrumb-item active">Pengiriman</li>
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
                        <form action="{{ route('checkout') }}" method="POST" id="checkoutForm">
                            @csrf
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="user_name">Nama Penerima</label>
                                        <input type="text" class="form-control @error('user_name') is-invalid @enderror" id="user_name" name="user_name" value="{{ $customer->users['name'] }}" required />
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
                                        <input type="tel" class="form-control" id="phone_number" name="phone_number" value="{{ $customer->phone_number }}" required />
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="destination_search">Cari Kota/Kecamatan Tujuan</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="destination_search" 
                                               placeholder="Ketik nama kota atau kecamatan..."
                                               autocomplete="off"
                                               required>
                                        <div id="destination_results" class="list-group mt-2" style="position: absolute; z-index: 1000; max-height: 300px; overflow-y: auto; display: none; width: calc(100% - 30px);"></div>
                                        <input type="hidden" name="destination_id" id="destination_id" required>
                                        <input type="hidden" name="kota" id="kota">
                                        <small class="form-text text-muted">Contoh: Jakarta, Bandung, Surabaya, dll</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="address">Alamat Lengkap</label>
                                        <textarea class="form-control" 
                                                  id="address" 
                                                  name="address" 
                                                  rows="3"
                                                  placeholder="Masukkan alamat lengkap..."
                                                  required></textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="courier_name">Kurir</label>
                                        <select name="kurir" id="courier_name" class="form-control" required>
                                            <option value="">Pilih Ekspedisi</option>
                                            <option value="jne">JNE</option>
                                            <option value="pos">POS Indonesia</option>
                                            <option value="tiki">TIKI</option>
                                            <option value="jnt">J&T Express</option>
                                            <option value="sicepat">SiCepat</option>
                                            <option value="anteraja">AnterAja</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="service_name">Layanan & Durasi</label>
                                        <select name="kurir_name" id="service_name" class="form-control" required disabled>
                                            <option value="">Pilih kurir terlebih dahulu</option>
                                        </select>
                                        <input type="hidden" name="ongkir" id="shipping_cost_value">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="notes">Catatan (Opsional)</label>
                                        <input type="text" class="form-control" id="notes" name="notes" placeholder="Catatan untuk kurir..."/>
                                    </div>
                                </div>
                            </div>

                            <div class="d-block d-sm-none">
                                <h2 class="mb-3">Informasi Pembayaran</h2>
                                <div class="row">
                                    <div class="col-8">
                                        <div class="product-subtitle">Subtotal Produk</div>
                                    </div>
                                    <div class="col-4">
                                        <div class="product-title text-right">Rp {{ number_format($total_order, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-8">
                                        <div class="product-subtitle">Ongkos Kirim</div>
                                    </div>
                                    <div class="col-4">
                                        <div id="shipping_cost_mobile" class="product-title text-right">-</div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="product-subtitle font-weight-bold">Total Pembayaran</div>
                                    </div>
                                    <div class="col-6">
                                        <div id="total_payment_mobile" class="product-total text-success text-right font-weight-bold">Rp {{ number_format($total_order, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="subtotal" value="{{ $total_order }}">
                            <input type="hidden" name="totalnya" id="total_payment_value" value="{{ $total_order }}">

                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-success btn-block" id="checkout_button">
                                        <i class="fas fa-shopping-cart mr-2"></i> Lanjutkan Pembayaran
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="col-sm-6 splitter order-1 order-sm-12">
                        <div class="row">
                            <div class="col-12">
                                <h2 class="mb-4">Detail Pesanan</h2>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table class="table table-borderless table-cart">
                                    <thead>
                                        <tr>
                                            <th colspan="2">Produk</th>
                                            <th class="text-center">Berat</th>
                                            <th class="text-right">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($carts as $cart)
                                        <tr>
                                            <td style="width: 60px">
                                                <img style="height:50px; object-fit: cover;" 
                                                     src="{{ Storage::disk('public')->exists($cart->products->product_image) ? Storage::url($cart->products->product_image) : asset($cart->products->product_image) }}" 
                                                     alt="{{ $cart->products->product_name }}"
                                                     class="img-fluid">
                                            </td>
                                            <td>
                                                <div class="product-title">{{ $cart->products->product_name }}</div>
                                                <div class="product-subtitle">{{ $cart->colors->color_name }}, {{ $cart->sizes->size_name }}</div>
                                                <div class="product-subtitle">Qty: {{ $cart->amount }}</div>
                                            </td>
                                            <td class="text-center">
                                                <div class="product-title">{{ number_format($cart->products->product_weight * $cart->amount, 0, ',', '.') }}g</div>
                                            </td>
                                            <td class="text-right">
                                                <div class="product-title">Rp {{ number_format($cart->products->product_price * $cart->amount, 0, ',', '.') }}</div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <hr>
                        <div class="d-none d-sm-block">
                            <h2 class="mb-3">Ringkasan Pembayaran</h2>
                            <div class="row mb-2">
                                <div class="col-8">
                                    <div class="product-subtitle">Subtotal Produk</div>
                                </div>
                                <div class="col-4">
                                    <div class="product-title text-right">Rp {{ number_format($total_order, 0, ',', '.') }}</div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">
                                    <div class="product-subtitle">Berat Total</div>
                                </div>
                                <div class="col-4">
                                    <div class="product-title text-right">{{ number_format($total_weight, 0, ',', '.') }} gram</div>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-8">
                                    <div class="product-subtitle">Ongkos Kirim</div>
                                </div>
                                <div class="col-4">
                                    <div id="shipping_cost_desktop" class="product-title text-right">-</div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6">
                                    <div class="product-subtitle font-weight-bold">Total Pembayaran</div>
                                </div>
                                <div class="col-6">
                                    <div id="total_payment_desktop" class="product-total text-success text-right font-weight-bold">Rp {{ number_format($total_order, 0, ',', '.') }}</div>
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
<script src="https://code.jquery.com/jquery-3.6.1.min.js"></script>
<script>
$(document).ready(function() {
    const TOTAL_ORDER = {{ $total_order }};
    const TOTAL_WEIGHT = {{ $total_weight }};
    let searchTimeout;
    let selectedDestinationId = null;

    // Search destination with debounce
    $('#destination_search').on('input', function() {
        clearTimeout(searchTimeout);
        const searchQuery = $(this).val().trim();
        
        if (searchQuery.length < 3) {
            $('#destination_results').hide();
            return;
        }

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: '/shipping/search-destination',
                method: 'GET',
                data: {
                    search: searchQuery,
                    limit: 10
                },
                success: function(response) {
                    console.log('API Response:', response); // Debug log
                    if (response.meta.status === 'success' && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(function(item) {
                            console.log('Item:', item); // Debug each item
                            
                            // Build display text from available fields
                            const subdistrict = item.subdistrict || item.district || '';
                            const city = item.city || item.city_name || '';
                            const province = item.province || item.province_name || '';
                            const zipCode = item.zip_code || item.postal_code || '-';
                            
                            const displayText = [subdistrict, city, province].filter(Boolean).join(', ');
                            
                            html += `
                                <a href="#" class="list-group-item list-group-item-action destination-item" 
                                   data-id="${item.id}" 
                                   data-label="${displayText}">
                                    <strong>${displayText}</strong>
                                    <br><small class="text-muted">Kode Pos: ${zipCode}</small>
                                </a>
                            `;
                        });
                        $('#destination_results').html(html).show();
                    } else {
                        $('#destination_results').html('<div class="list-group-item">Tidak ada hasil ditemukan</div>').show();
                    }
                },
                error: function() {
                    $('#destination_results').html('<div class="list-group-item text-danger">Error mencari lokasi</div>').show();
                }
            });
        }, 500);
    });

    // Select destination
    $(document).on('click', '.destination-item', function(e) {
        e.preventDefault();
        selectedDestinationId = $(this).data('id');
        const label = $(this).data('label');
        
        $('#destination_search').val(label);
        $('#destination_id').val(selectedDestinationId);
        $('#kota').val(label);
        $('#destination_results').hide();
        
        // Reset courier selection when destination changes
        $('#service_name').prop('disabled', true).html('<option value="">Pilih kurir terlebih dahulu</option>');
        $('#courier_name').val('');
        updateShippingCost(0);
    });

    // Hide results when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#destination_search, #destination_results').length) {
            $('#destination_results').hide();
        }
    });

    // Calculate shipping cost when courier is selected
    $('#courier_name').on('change', function() {
        const courier = $(this).val();
        
        if (!courier) {
            $('#service_name').prop('disabled', true).html('<option value="">Pilih kurir terlebih dahulu</option>');
            return;
        }

        if (!selectedDestinationId) {
            alert('Silakan pilih tujuan pengiriman terlebih dahulu');
            $(this).val('');
            return;
        }

        $('#service_name').prop('disabled', true).html('<option value="">Loading...</option>');

        // Origin ID (hardcoded - should be from admin settings)
        const originId = 31555; // Example: Jakarta Pusat

        $.ajax({
            url: '/shipping/calculate-cost',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                origin: originId,
                destination: selectedDestinationId,
                weight: TOTAL_WEIGHT,
                courier: courier
            },
            success: function(response) {
                if (response.meta.status === 'success' && response.data.length > 0) {
                    let html = '<option value="">Pilih layanan</option>';
                    response.data.forEach(function(service) {
                        const serviceName = `${service.service} (${service.description})`;
                        const etd = service.etd || '-';
                        html += `<option value="${service.cost}" data-service="${service.service}" data-etd="${etd}">
                                    ${serviceName} - Rp ${formatNumber(service.cost)} (${etd} hari)
                                 </option>`;
                    });
                    $('#service_name').html(html).prop('disabled', false);
                } else {
                    $('#service_name').html('<option value="">Tidak ada layanan tersedia</option>');
                    alert('Kurir tidak tersedia untuk tujuan ini');
                }
            },
            error: function(xhr) {
                $('#service_name').html('<option value="">Error memuat layanan</option>');
                alert('Gagal menghitung ongkir. Silakan coba lagi.');
            }
        });
    });

    // Update total when service is selected
    $('#service_name').on('change', function() {
        const shippingCost = parseInt($(this).val()) || 0;
        const etd = $(this).find(':selected').data('etd') || '-';
        
        // Store ETD in hidden field
        $('<input>').attr({
            type: 'hidden',
            name: 'etd',
            value: etd
        }).appendTo('#checkoutForm');
        
        updateShippingCost(shippingCost);
    });

    function updateShippingCost(cost) {
        const total = TOTAL_ORDER + cost;
        
        $('#shipping_cost_value').val(cost);
        $('#total_payment_value').val(total);
        
        const costFormatted = cost > 0 ? 'Rp ' + formatNumber(cost) : '-';
        const totalFormatted = 'Rp ' + formatNumber(total);
        
        $('#shipping_cost_mobile, #shipping_cost_desktop').text(costFormatted);
        $('#total_payment_mobile, #total_payment_desktop').text(totalFormatted);
    }

    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Form validation before submit
    $('#checkoutForm').on('submit', function(e) {
        if (!selectedDestinationId) {
            e.preventDefault();
            alert('Silakan pilih tujuan pengiriman');
            $('#destination_search').focus();
            return false;
        }

        if (!$('#service_name').val()) {
            e.preventDefault();
            alert('Silakan pilih layanan pengiriman');
            $('#service_name').focus();
            return false;
        }

        return true;
    });
});
</script>
@endpush
