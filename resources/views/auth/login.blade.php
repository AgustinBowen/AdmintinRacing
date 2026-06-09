@extends('layouts.guest')

@section('content')
<form method="POST" action="{{ route('login') }}" class="login-form" autocomplete="off">
    @csrf

    <div class="login-field">
        <label for="name">Usuario</label>
        <input 
            type="text" 
            id="name" 
            name="name" 
            placeholder="Ingresá tu usuario" 
            value="{{ old('name') }}"
            required 
            autofocus 
            autocomplete="username" />
    </div>

    <div class="login-field">
        <label for="password">Contraseña</label>
        <input 
            type="password" 
            id="password" 
            name="password" 
            placeholder="Ingresá tu contraseña" 
            required 
            autocomplete="current-password" />
    </div>

    @if($errors->any() || session('error'))
    <div class="login-error show">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        <span>Usuario o contraseña incorrectos</span>
    </div>
    @endif

    <button type="submit" class="login-btn">Ingresar &#9654;</button>
</form>
@endsection