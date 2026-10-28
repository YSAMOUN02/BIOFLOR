@extends('admin.layout')

@section('title', 'My account')

@section('content')
    <div class="card">
        <div class="card__head"><h2>Change password</h2></div>
        <form class="card__body form-grid" method="post" action="{{ route('admin.account.update') }}" style="max-width:480px">
            @csrf
            @method('PUT')

            <div class="field">
                <label>Email</label>
                <input type="text" value="{{ $user->email }}" readonly>
            </div>

            <div class="field">
                <label for="current_password">Current password</label>
                <input type="password" id="current_password" name="current_password" autocomplete="current-password" required>
                @error('current_password') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password">New password</label>
                <input type="password" id="password" name="password" autocomplete="new-password" minlength="10" required>
                <p class="help">At least 10 characters.</p>
                @error('password') <p class="error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="password_confirmation">Repeat the new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
            </div>

            <div class="form-actions">
                <button class="btn btn--primary" type="submit">Save password</button>
            </div>
        </form>
    </div>
@endsection
