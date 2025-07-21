@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Pages'])

    <div class="card shadow-lg mx-4">
        <div class="row">
            <main class="main-content mt-0 ps">
                <div class="page-header min-vh-100" style="background-image: url('/img/illustrations/404.svg');">
                    <div class="container">
                        <div class="row justify-content-center">
                            <div class="col-lg-6 col-md-7 mx-auto text-center">
                                <h1 class="display-1 text-bolder text-primary">{{ $errorpages }}</h1>
                                <h2>Halaman Tidak Ditemukan</h2>
                                <p class="lead">Silahkan Hubungi Admin.</p>
                                <a href="/" class="btn bg-gradient-dark mt-4">Halaman Utama</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                    <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                </div>
                <div class="ps__rail-y" style="top: 0px; right: 0px;">
                    <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
                </div>
            </main>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('js')
    <script src="./assets/js/plugins/chartjs.min.js"></script>
    <script>
        var ctx1 = document.getElementById("chart-line").getContext("2d");

        var gradientStroke1 = ctx1.createLinearGradient(0, 230, 0, 50);

        gradientStroke1.addColorStop(1, 'rgba(251, 99, 64, 0.2)');
        gradientStroke1.addColorStop(0.2, 'rgba(251, 99, 64, 0.0)');
        gradientStroke1.addColorStop(0, 'rgba(251, 99, 64, 0)');
    </script>
@endpush
