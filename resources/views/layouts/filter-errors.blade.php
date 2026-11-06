@if ($errors->any())
<div class="notice" role="alert"><span class="notice-icon">!</span><div><strong>Periksa pilihan filter.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif
