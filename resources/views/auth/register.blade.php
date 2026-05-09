
<!DOCTYPE html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1"><!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="../../dist-assets/images/logo.png">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Gull - Laravel + Bootstrap 4 admin template</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900" rel="stylesheet">
    <link href="../../dist-assets/css/themes/lite-purple.min.css" rel="stylesheet">
</head>
<div class="auth-layout-wrap" style="background-image: url(../../dist-assets/images/photo-wide-4.jpg)">
    <div class="auth-content">
        @if(session()->has("message"))
            <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card o-hidden">
            <div class="row">
                <div class="col-md-6 text-center" style="background-size: cover;background-image: url(../../dist-assets/images/photo-long-3.jpg)">
                    <div class="ps-3 auth-right">
                        <div class="auth-logo text-center mt-4"><img src="../../dist-assets/images/logo.png" alt=""></div>
                        <div class="flex-grow-1"></div>
                        <div class="w-100 mb-4">
                        </div>
                        <div class="flex-grow-1"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-4">
                        <h1 class="mb-3 text-18">S'inscrire</h1>
                        <form action="{{ route('registers') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="nom">Votre nom</label>
                                <input required class="form-control form-control-rounded" name="nom" id="nom" type="text" value="{{ old('nom') }}">
                            </div>
                            <div class="form-group">
                                <label for="prenoms">Votre prénoms</label>
                                <input required class="form-control form-control-rounded" name="prenoms" id="prenoms" type="text" value="{{ old('prenoms') }}">
                            </div>
                            <div class="form-group">
                                <label for="mobile">Numéro de téléphone</label>
                                <input required class="form-control form-control-rounded" name="mobile" id="mobile" type="number" value="{{ old('mobile') }}">
                            </div>
                            <div class="form-group">
                                <label for="email">Adresse email</label>
                                <input required class="form-control form-control-rounded" name="email" id="email" type="email" value="{{ old('email') }}">
                            </div>
                            <div class="form-group">
                                <label for="password">Mot de passe</label>
                                <input required class="form-control form-control-rounded" name="password" id="password" type="password">
                            </div>
                            <div class="form-group">
                                <label for="repassword">Retaper le mot de passe</label>
                                <input required class="form-control form-control-rounded" name="password_confirmation" id="repassword" type="password">
                            </div>
                            <button class="btn btn-primary w-100 btn-rounded mt-3">S'inscrire</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>