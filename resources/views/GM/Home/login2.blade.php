<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ env(strtoupper($proj).'_APP_NAME') }}</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="/adminlte/plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="/adminlte/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="/adminlte/dist/css/adminlte.min.css">
</head>

@if($proj == 'Analysis')
<style type="text/css">
.card-primary.card-outline{
  border-top-color: #28a745;
}
.btn-primary {
    color: #fff;
    background-color: #28a745;
    border-color: #28a745;
    box-shadow: none;
}
</style>
@endif

@if($proj == 'Merchant')
<style type="text/css">
.card-primary.card-outline{
  border-top-color: #ffc107;
}
.btn-primary {
    color: #fff;
    background-color: #ffc107;
    border-color: #ffc107;
    box-shadow: none;
}
</style>
@endif

<body class="hold-transition login-page">
<div class="login-box">
  <!-- /.login-logo -->
  <div class="card card-outline card-primary">
    <div class="card-header text-center">
      <a href="#" class="h1"><b>{{ __('ts.'.env(strtoupper($proj).'_APP_NAME')) }}</b></a>
    </div>
    <div class="card-body">
      <p class="login-box-msg">{{ __('ts.Sign in to start your session') }}</p>

      <form action="/login" method="post">
        @csrf
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="{{ __('ts.Username') }}" name="username" value="">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-user"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="{{ __('ts.Password') }}" name="password" value="">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="{{ __('ts.Google Captcha Code') }}" name="googleCaptchaCode">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
            <div class="icheck-primary" style="display: none;">
              <input type="checkbox" id="remember">
              <label for="remember">
                Remember Me
              </label>
            </div>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="submit" class="btn btn-primary btn-block">{{ __('ts.Sign In') }}</button>
          </div>
          <!-- /.col -->
        </div>
      </form>


    </div>
    <!-- /.card-body -->
  </div>
  <!-- /.card -->
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="/adminlte/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="/adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="/adminlte/dist/js/adminlte.min.js"></script>
<script src="/adminlte/custom/common.js?q={{ env('JS_VER') }}"></script>

<script>
$(document).ready(function () {
    //判断是否在iframe中
    if(self!=top){
        parent.window.location.replace(window.location.href);
    }
})
</script>
</body>
</html>
