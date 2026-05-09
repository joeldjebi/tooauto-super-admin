<!DOCTYPE html>
<head>
   <meta charset="UTF-8" />
   <meta name="viewport" content="width=device-width,initial-scale=1" />
   <link rel="shortcut icon" type="image/x-icon" href="../../dist-assets/images/logo.png">
   <meta http-equiv="X-UA-Compatible" content="ie=edge" />
   <title>Signin | Gull Bootstrap 5 Admin Template</title>
   <link
      href="https://fonts.googleapis.com/css?family=Nunito:300,400,400i,600,700,800,900"
      rel="stylesheet"
      />
   <link
      href="../../dist-assets/css/themes/lite-purple.min.css"
      rel="stylesheet"
      />
</head>
<div
   class="auth-layout-wrap"
   style="background-image: url(../../dist-assets/images/photo-wide-4.jpg)"
   >
   <div class="auth-content">
    @if(session()->has("message"))
        <div style="padding: 10px" class="alert {{session()->get('type')}}">{{ session()->get('message') }} </div>
    @endif
      <div class="card o-hidden">
         <div class="row">
            <div class="col-md-6">
               <div class="p-4">
                  <div class="auth-logo text-center mb-4">
                     <img src="../../dist-assets/images/logo.png" alt="" />
                  </div>
                  <h1 class="mb-3 text-18">Se connecter</h1>
                  <form action="{{ route('logins') }}" method="POST">
                    @csrf
                     <div class="form-group">
                        <label for="email">Adresse email</label>
                        <input class="form-control form-control-rounded" name="email" id="email" type="email" />
                     </div>
                     <div class="form-group">
                        <label for="password">Mot de passe</label>
                        <input
                           class="form-control form-control-rounded" name="password" id="password" type="password" />
                     </div>
                     <button class="btn btn-rounded btn-primary w-100 mt-2">
                     Se connecter
                     </button>
                  </form>
                  <div class="mt-3 text-center">
                     <a class="text-muted" href="forgot.html">
                     <u>Mot de passe oublié?</u></a
                        >
                  </div>
               </div>
            </div>
            <div class="col-md-6 text-center" style=" background-size: cover; background-image: url(../../dist-assets/images/photo-long-3.jpg);">
               <div class="pe-3 auth-right">
               </div>
            </div>
         </div>
      </div>
   </div>
</div>