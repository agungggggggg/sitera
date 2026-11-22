<form method="post" action="{{ route('logout') }}" class="logout-form">
    @csrf
    <button class="exit-link" type="submit" aria-label="Keluar" title="Keluar">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M16.1 7.4a6.5 6.5 0 1 0 0 9.2" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
            <path d="M10.5 12h9m-2.7-2.7L19.5 12l-2.7 2.7" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
</form>
