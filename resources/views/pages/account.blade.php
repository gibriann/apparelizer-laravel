@extends('layouts.app')

@section('title')
    Profil Saya
@endsection

@section('content')
    <div class="page-content page-home">
        <section class="store-products">
            <div class="container">
                
                <div class="row justify-content-center mt-2">
                    <div class="col-md-10 mt-2 ">
                        <div class="row mb-3">
                            <div class="col-12" data-aos="fade-up">
                                <h5 >Akun Saya</h5>
                            </div>
                        </div>
                        <div class="card card-white shadow p-5 mb-2">
                            <div class="transaction-section-content">
                                <form action="{{ route('account') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="card-body">
                                        <div class="row mb-2" data-aos="fade-up" data-aos-delay="200">
                                            <div class="col-12">
                                                <div class="media">
                                                    <div class="conditioner px-2">
                                                        <img src="{{ asset('../../images/user-icon.jpg') }}" class="profile-img img-responsive rounded-circle align-self-center" alt="...">
                                                        <div class="middle">
                                                            <div class="text"><a href="#">Ubah Foto</a> </div>
                                                        </div>
                                                    </div>
                                                    <div class="media-body ml-4">
                                                        <div class="form-group">
                                                            <label for="">Nama Lengkap</label>
                                                            <input type="text" value="{{ Auth::user()->name }}" name="name" class="form-control @error('name') is-invalid @enderror" autofocus />
                                                            @error('name')
                                                                <div class="invalid-feedback">
                                                                    {{$message}}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                    
                                                        <div class="form-group">
                                                            <label for="">Email</label>
                                                            <input type="email" name="email" value="{{ Auth::user()->email }}" class="form-control @error('email') is-invalid @enderror"/>
                                                            @error('email')
                                                                <div class="invalid-feedback">
                                                                    {{$message}}
                                                                </div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-2" data-aos="fade-up" data-aos-delay="300">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="notes">Alamat</label>
                                                    <input type="text" class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" value="{{ Auth::user()->customers->first()->address }}" />
                                                    @error('alamat')
                                                        <div class="invalid-feedback">
                                                            {{$message}}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-2" data-aos="fade-up" data-aos-delay="300">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone">Kode POS</label>
                                                    <input type="text" class="form-control @error('postalCode') is-invalid @enderror" id="postalCode" name="postalCode" value="{{ Auth::user()->customers->first()->postal_code }}" />
                                                    @error('postalCode')
                                                        <div class="invalid-feedback">
                                                            {{$message}}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Nomor Telepon</label>
                                                    <input type="number" value="{{ Auth::user()->customers->first()->phone_number }}" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror"/>
                                                    @error('phone_number')
                                                        <div class="invalid-feedback">
                                                            {{$message}}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mt-4 text-right">
                                            <button type="submit" class="py-2 px-4 btn text-center btn-success text-white"> Simpan </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection