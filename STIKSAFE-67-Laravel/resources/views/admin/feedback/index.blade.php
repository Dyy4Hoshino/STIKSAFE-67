@extends('layouts.app')
@section('title', 'Feedback User | STIKSAFE-67')
@section('content')
<div class="eyebrow">ADMIN / QUALITY REVIEW</div>
<h1>Feedback User.</h1>
<p class="muted">Tinjau laporan salah klasifikasi yang dikirimkan setelah scan selesai.</p>
<form method="get" class="panel" style="margin:20px 0;display:flex;gap:12px;align-items:end;flex-wrap:wrap">
    <div class="field"><label for="status">STATUS</label><select id="status" name="status"><option value="">Semua</option><option value="new" @selected(request('status') === 'new')>Belum ditinjau</option><option value="reviewed" @selected(request('status') === 'reviewed')>Sudah ditinjau</option></select></div>
    <div class="field"><label for="type">JENIS</label><select id="type" name="type"><option value="">Semua</option><option value="false_positive" @selected(request('type') === 'false_positive')>False positive</option><option value="false_negative" @selected(request('type') === 'false_negative')>False negative</option></select></div>
    <button class="btn" type="submit">Filter</button>
</form>
<section class="panel">
    @if ($feedback->isEmpty())
        <p class="muted">Belum ada feedback untuk filter ini.</p>
    @else
        <div class="table-wrap"><table class="data"><caption class="muted" style="text-align:left;padding:8px 0">Laporan feedback pengguna</caption><thead><tr><th scope="col">Waktu</th><th scope="col">Pengguna</th><th scope="col">Scan</th><th scope="col">Jenis</th><th scope="col">Komentar</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead><tbody>
        @foreach ($feedback as $item)
            <tr><td>{{ $item->created_at?->timezone(config('app.timezone'))->format('d M Y H:i') }}</td><td>{{ $item->user?->name }}<br><small>{{ $item->user?->email }}</small></td><td>{{ $item->scan?->original_filename }}<br><small>Hasil: {{ $item->scan?->result_label?->value ?? $item->scan?->result_label }}</small></td><td>{{ str_replace('_', ' ', ucfirst($item->type)) }}</td><td>{{ $item->comment ?: '—' }}</td><td>{{ $item->reviewed_at ? 'Ditinjau' : 'Baru' }}</td><td>@if (! $item->reviewed_at)<form method="post" action="{{ route('admin.feedback.review', $item) }}">@csrf @method('PATCH')<button class="btn" type="submit">Tandai ditinjau</button></form>@else{{ $item->reviewer?->name ?? '—' }}@endif</td></tr>
        @endforeach
        </tbody></table></div>
        <div class="actions">{{ $feedback->links() }}</div>
    @endif
</section>
@endsection
