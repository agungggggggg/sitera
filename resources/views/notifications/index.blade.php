@extends('layouts.sitera')

@section('title', 'Notifikasi — SITERA')
@section('breadcrumb', 'Notifikasi')

@section('content')
<div class="page-head"><div><div class="eyebrow">Pemberitahuan akun</div><h1>Notifikasi</h1><p>{{ $unreadCount }} belum dibaca</p></div></div>
@if (session('status'))<div class="notice" style="margin-bottom:16px"><span>✓</span><div>{{ session('status') }}</div></div>@endif
<section class="panel"><div class="table-wrap"><div class="notification-list">
@forelse ($notifications as $notification)
<article class="notification-item {{ $notification->read_at ? '' : 'unread' }}"><div class="notification-icon">{{ $notification->data['type'] === 'hasil_verifikasi' ? '✓' : '▤' }}</div><div class="notification-copy"><h2>{{ $notification->data['title'] ?? 'Notifikasi SITERA' }}</h2><p>{{ $notification->data['message'] ?? '' }}</p><div class="sub">{{ $notification->created_at?->format('d M Y H:i') }} WIB · {{ $notification->data['ticket'] ?? '' }}</div></div><div class="notification-actions"><a class="secondary" href="{{ $notification->data['url'] ?? route('reports.index') }}">Buka</a>@if (! $notification->read_at)<form method="post" action="{{ route('notifications.read', $notification->id) }}">@csrf<button class="secondary" type="submit">Tandai dibaca</button></form>@endif</div></article>
@empty
<div class="empty-state">Belum ada notifikasi.</div>
@endforelse
</div></div><div style="padding:16px">{{ $notifications->links() }}</div></section>
@endsection

@push('styles')
<style>
.notification-list{display:grid}.notification-item{display:flex;gap:14px;align-items:flex-start;padding:18px 20px;border-bottom:1px solid var(--line)}.notification-item.unread{background:#f0f8f6}.notification-icon{flex:none;width:36px;height:36px;border-radius:50%;display:grid;place-items:center;background:var(--mint);color:var(--teal);font-weight:800}.notification-copy{flex:1;min-width:0}.notification-copy h2{font-size:14px;margin:0 0 4px}.notification-copy p{margin:0 0 7px;color:var(--muted)}.notification-actions{display:flex;gap:8px;flex-wrap:wrap}.notification-actions form{margin:0}.notification-actions .secondary{padding:7px 10px;font-size:11px}.unread-count{position:absolute;top:-5px;right:-5px;min-width:17px;height:17px;border-radius:9px;background:#d25353;color:white;font-size:10px;display:grid;place-items:center;padding:0 4px}
@media(max-width:700px){.notification-item{flex-wrap:wrap;padding:14px}.notification-copy{flex:1 1 calc(100% - 50px)}.notification-actions{width:100%;margin-left:50px}.notification-actions a,.notification-actions button,.notification-actions .secondary{min-height:44px;display:inline-flex;align-items:center;justify-content:center}}
</style>
@endpush
