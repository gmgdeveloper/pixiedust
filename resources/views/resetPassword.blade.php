<h1>Reset Password</h1>

@if ($errors->any())
<div class="alert alert-danger">
    <ul>
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('resetPassword') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $resetData->token }}">
    
    <div class="form-group">
        <label for="user_password">Enter your new password:</label>
        <input type="password" name="user_password" id="user_password" class="form-control" placeholder="Enter your password" required>
        @error('user_password')
        <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    
    <div class="form-group">
        <label for="password_confirmation">Confirm your new password:</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Confirm your password" required>
    </div>
    
    <input type="submit" class="btn btn-primary" value="Reset Password">
</form>
