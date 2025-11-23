<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Confirm Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-4">Confirm Your Password</h4>

                    <p class="text-muted text-center mb-4">
                        This is a secure area of the application. Please confirm your password before continuing.
                    </p>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('password.confirm') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   autofocus
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Confirm</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">Forgot Your Password?</a>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
