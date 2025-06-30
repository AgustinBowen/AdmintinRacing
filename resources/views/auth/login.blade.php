@extends('layouts.guest')

@section('content')
<div class="auth-header">
    <h2>Iniciar Sesión</h2>
    <p>Ingresa tus credenciales para acceder al panel</p>
</div>

@if(session('error'))
<div class="alert-error">
    <i class="fas fa-exclamation-triangle"></i>
    {{ session('error') }}
</div>
@endif

@if($errors->any())
<div class="alert-error">
    <i class="fas fa-exclamation-triangle"></i>
    <div>
        @foreach($errors->all() as $error)
        <div>{{ $error }}</div>
        @endforeach
    </div>
</div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="form-group">
        <label for="name" class="form-label">
            <i class="fas fa-user" style="margin-right: 0.5rem; color: hsl(var(--muted-foreground));"></i>
            Usuario
        </label>
        <input
            id="name"
            name="name"
            type="text"
            required
            autofocus
            value="{{ old('name') }}"
            placeholder="verstappen33"
            class="form-input @error('name') error @enderror">
        @error('name')
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">
            <i class="fas fa-lock" style="margin-right: 0.5rem; color: hsl(var(--muted-foreground));"></i>
            Contraseña
        </label>
        <input
            id="password"
            name="password"
            type="password"
            required
            placeholder="••••••••"
            class="form-input @error('password') error @enderror">
        @error('password')
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            {{ $message }}
        </div>
        @enderror
    </div>

    <button type="submit" class="btn-primary">
        <i class="fas fa-sign-in-alt" style="margin-right: 0.5rem;"></i>
        Iniciar Sesión
    </button>
</form>

<style>
    .error-message {
        color: hsl(var(--destructive));
        font-size: 0.75rem;
        margin-top: 0.25rem;
        display: flex;
        align-items: center;
    }

    .error-message i {
        margin-right: 0.25rem;
    }

    .form-input.error {
        border-color: hsl(var(--destructive));
        box-shadow: 0 0 0 2px hsl(var(--destructive) / 0.2);
    }

    .form-input.error:focus {
        border-color: hsl(var(--destructive));
        box-shadow: 0 0 0 2px hsl(var(--destructive) / 0.2);
    }
</style>
@endsection