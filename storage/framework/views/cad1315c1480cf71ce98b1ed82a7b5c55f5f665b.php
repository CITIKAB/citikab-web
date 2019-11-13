<div class="container mar-zero" style="padding:0px;">
<div class="col-lg-10 col-md-10 col-sm-13 col-xs-12 height--full dash-panel">
<div class="height--full separated--sides pull-left full-width">
<div style="padding:0px;" class="col-lg-3 col-md-3 col-sm-3 col-xs-12 flexbox__item one-fifth page-sidebar hidden--portable hide-sm-760">
<ul class="site-nav"><li class="soft--ends">
<div class="center-block three-quarters push-half--bottom"><div class="img--circle img--bordered img--shadow fixed-ratio fixed-ratio--1-1">
<?php if(@Auth::user()->profile_picture->src == ''): ?>
<img src="<?php echo e(url('images/user.jpeg')); ?>" class="img--full fixed-ratio__content">
<?php else: ?>
<img src="<?php echo e(@Auth::user()->profile_picture->src); ?>" class="img--full fixed-ratio__content profile_picture">
<?php endif; ?>
</div>
</div>
<div class="text--center"><div style="    font-size: 16px;
    font-weight: 200;"><?php echo e(@Auth::user()->first_name); ?> <?php echo e(@Auth::user()->last_name); ?></div><div class="soft-half--top"></div></div></li><li><a href="<?php echo e(url('trip')); ?>" aria-selected="<?php echo e((Route::current()->uri() == 'trip') ? 'true' : 'false'); ?>" class="side-nav-a"><?php echo e(trans('messages.header.mytrips')); ?></a></li><li><a href="<?php echo e(url('profile')); ?>" aria-selected="<?php echo e((Route::current()->uri() == 'profile') ? 'true' : 'false'); ?>" class="side-nav-a"><?php echo e(trans('messages.header.profil')); ?></a></li>
    <!-- <li><a href="<?php echo e(url('payment')); ?>" aria-selected="<?php echo e((Route::current()->uri() == 'payment') ? 'true' : 'false'); ?>" class="side-nav-a">Payment</a></li> -->
   	<li><a href="<?php echo e(url('sign_out')); ?>"><?php echo e(trans('messages.header.logout')); ?></a></li>
    </ul>
    </div>