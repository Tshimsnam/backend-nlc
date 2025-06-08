<!DOCTYPE html>
<html>
<head>
    <title>Définir votre mot de passe</title>
</head>
<body>
    <h1>Définir le mot de passe</h1>

    @if ($errors->any())
        <div style="color:red;">
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div>
            <label>Nouveau mot de passe</label><br>
            <input type="password" name="password" required>
        </div>

        <div>
            <label>Confirmation</label><br>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit">Définir mot de passe</button>
    </form>
</body>
</html>
