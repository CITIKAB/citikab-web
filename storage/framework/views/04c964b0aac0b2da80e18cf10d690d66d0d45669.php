<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Admin Panel</title>
  <link rel="shortcut icon" href="<?php echo e($favicon); ?>">
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.5 -->
  <link rel="stylesheet" href="<?php echo e(url('admin_assets/bootstrap/css/bootstrap.min.css')); ?>">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css') }}">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo e(url('admin_assets/dist/css/AdminLTE.css')); ?>">
  <!-- iCheck -->
  <link rel="stylesheet" href="<?php echo e(url('admin_assets/plugins/iCheck/square/blue.css')); ?>">

  <!-- slider !-->
  <link rel="stylesheet" href="<?php echo e(url('admin_assets/plugins/login_slider/bootstrap.min.css')); ?>">
  <link rel="stylesheet" href="<?php echo e(url('admin_assets/plugins/login_slider/style.css')); ?>">
  <!-- slider !-->

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body class="hold-transition login-page">

<div class="flash-container" style="left:15px;">
<?php if(Session::has('message') && !Auth::guard('admin')->check()): ?>
  <div class="alert <?php echo e(Session::get('alert-class')); ?>" role="alert">
    <a href="#" class="alert-close" data-dismiss="alert">&times;</a>
  <?php echo e(Session::get('message')); ?>

  </div>
<?php endif; ?>
</div>

<div class="login-box">
  <div class="register-container container">
            <div class="row">

                <div class="register span4">
                    <form action="<?php echo e(url('admin/authenticate')); ?>" method="post">
                    <?php echo Form::token(); ?>

                        <h2>LOGIN TO <span class="red"><strong><?php echo e($site_name); ?></strong></span></h2>
                        <label for="username">Username</label>
                        <input type="text" id="username" value="" name="username" placeholder="Enter the username">
                        <label for="password">Password</label>
                        <input type="password" id="password" value="" name="password" placeholder="Enter the password">
                        <button type="submit">LOGIN</button>
                    </form>
                </div>
            </div>
        </div>
</div>



<!-- /.login-box -->

<!-- jQuery 2.1.4 -->
<script src="<?php echo e(url('admin_assets/plugins/jQuery/jQuery-2.1.4.min.js')); ?>"></script>
<!-- Bootstrap 3.3.5 -->
<script src="<?php echo e(url('admin_assets/bootstrap/js/bootstrap.min.js')); ?>"></script>
<!-- iCheck -->
<script src="<?php echo e(url('admin_assets/plugins/iCheck/icheck.min.js')); ?>"></script>

<script src="<?php echo e(url('admin_assets/plugins/login_slider/scripts.js')); ?>"></script>

<script src="<?php echo e(url('admin_assets/plugins/login_slider/jquery.backstretch.min.js')); ?>"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script>
</body>
</html>
