@php($unreadCount = auth()->user()?->unreadNotifications()->count() ?? 0)
<a class="icon-button notification-link" href="{{ route('notifications.index') }}" aria-label="Notifikasi" title="Notifikasi">
    <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
    @if ($unreadCount > 0)
        <span class="unread-badge">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
    @endif
</a>
