<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-4">Register</h4>

                    <form method="POST" action="{{ route('register.form') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input id="name"
                                   type="text"
                                   name="name"
                                   value="{{ old('name') }}"
                                   required
                                   autofocus
                                   class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input id="email"
                                   type="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   class="form-control @error('email') is-invalid @enderror">
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input id="password"
                                   type="password"
                                   name="password"
                                   required
                                   class="form-control @error('password') is-invalid @enderror">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password</label>
                            <input id="password_confirmation"
                                   type="password"
                                   name="password_confirmation"
                                   required
                                   class="form-control">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">Register</button>
                        </div>
                    </form>

                </div>
            </div>

            <div class="text-center mt-3">
                <a href="{{ route('login.form') }}">Already have an account? Login</a>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
