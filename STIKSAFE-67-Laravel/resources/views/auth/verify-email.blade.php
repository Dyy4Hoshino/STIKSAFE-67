@extends('layouts.app')
@section('title', 'Verifikasi Email | STIKSAFE-67')
@section('content')
<div style="max-width:590px;margin:35px auto"><section class="panel"><span class="eyebrow">ACCOUNT SECURITY / VERIFICATION</span><h1>Verifikasi<br><span style="color:#FF6700">email Anda.</span></h1><p class="muted">Kami sudah mengirim tautan verifikasi ke alamat email Anda. Buka email tersebut untuk melanjutkan ke dashboard STIKSAFE.</p>@if(session('status'))<p class="alert" role="status" style="border-color:#10b98166;background:#10b98111;color:#6ee7b7">{{ session('status') }}</p>@endif@if($errors->any())<div class="alert" role="alert">{{ $errors->first() }}</div>@endif<form method="post" action="{{ route('verification.send') }}">@csrf<button class="btn primary" type="submit">Kirim ulang email →</button></form><form method="post" action="{{ route('logout') }}" style="margin-top:12px">@csrf<button class="btn" type="submit">Keluar</button></form></section></div>
@endsection
