<div class="container-fluid dash-head">
<!-- <div class="container" style="text-align: center;padding:0px;"> -->
<div class="col-lg-12 col-md-12 col-sm-11 col-xs-12 dash-panel">
<!-- <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12 headlog"></div> -->
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 headlog">	
<button type="button" class="navbar-toggle nav-click hide-md-760" data-toggle="collapse" data-target="#menu-collapse">
          <a href="#" data-slide-menu="#slide-menu" data-slide-menu-content="#slide-menu-content" class="text--uppercase menu-a"><span class="icon icon_settings-alt push-half--right color--neutral micro"></span><?php echo e(trans('messages.header.menu')); ?></a>
        </button>
   <div class="nav-div" style="padding:0px !important;">
 <div class="icon-remove remove-bold " style="padding: 15px 15px !important;float: right !important;"> </div>
<div class="flexbox__item flexbox__item--expand"><ul class="site-nav site-nav--flush site-nav--dark block-list push-half--bottom"><li><div class="flexbox" style="margin-top:25px;"><div class="flexbox__item one-eighth pull-left">
  <div class="img--circle img--bordered img--shadow fixed-ratio fixed-ratio--1-1 head_profile">
  <?php if(@Auth::user()->profile_picture->src == ''): ?>
  <img src="https://d1w2poirtb3as9.cloudfront.net/default.jpeg" class="img--full fixed-ratio__content">
  <?php else: ?>
  <img src="<?php echo e(@Auth::user()->profile_picture->src); ?>" class="img--full fixed-ratio__content profile_picture">
  <?php endif; ?>
  </div>
  </div><div class="flexbox__item four-eighths soft-half--left pull-left" style="margin: 5px 30px 0px;
    font-size: 13px;"><div class="text--normal"><?php echo e(@Auth::user()->first_name); ?> <?php echo e(@Auth::user()->last_name); ?></div></div><div class="flexbox__item three-eighths text--right"></div></div><div id="slide-menu-account-progress" class="pro_bar" style="margin-top:20px;"><div class="soft--top milli text--left"><div class="grid"><div class="grid__item three-quarters"><strong><?php echo e(trans('messages.header.profile')); ?></strong></div><div class="grid__item one-quarter text--right"><strong class="color--negative">33%</strong></div></div><div class="progress push-half--top push--bottom"><div style="width: 33%" class="progress__bar progress__bar--negative"></div></div><div><span class="micro icon icon1--circle icon_check push-half--right icon1--inactive"></span><a href="/payment" class="link--immutable"><?php echo e(trans('messages.header.credit_card')); ?></a></div><div><span class="micro icon icon1--circle icon_check push-half--right icon1--inactive"></span><a href="#" data-toggle="modal" data-target="#verify-email-modal" class="link--immutable"><?php echo e(trans('messages.header.verify_email')); ?></a></div></div></div></li><li><a href="<?php echo e(url('trip')); ?>" class="active"><?php echo e(trans('messages.header.mytrips')); ?></a></li><li><a href="<?php echo e(url('profile')); ?>"><?php echo e(trans('messages.header.profile')); ?></a></li><li><a href="<?php echo e(url('payment')); ?>"><?php echo e(trans('messages.header.payment')); ?></a></li>
    <li><a href="#" class="free-rides-button hide-md-760"><?php echo e(trans('messages.header.logout')); ?></a></li>
    </ul><div class="soft-half hide-sm-760"><ul class="block-list text--uppercase"><li><a href="<?php echo e(url('sign_out')); ?>"><?php echo e(trans('messages.header.logout')); ?></a></li></ul></div>

    </div>
 </div> 
 <a href="<?php echo e(url('/')); ?>">    
<img class="dash-head-logo" src="<?php echo e(url(PAGE_LOGO_URL)); ?>"></a>

<ul class="nav--block float--right flush hidden--portable hide-sm-760"><li class="user-flyout flyout flyout--right"><a href="#" class="flyout__origin"><span class="icon icon_profile alpha push-half--right"></span><span class="push-half--right"><?php echo e(@Auth::user()->first_name); ?> <?php echo e(@Auth::user()->last_name); ?></span><span class="icon icon_down-arrow milli"></span></a><div class="flyout__content" style="margin:-4px 0px 0px 0px !important;padding:0px;"><ul class="site-nav site-nav--flush"><li><div class="grid"><div class="grid__item one-quarter">
<?php if(@Auth::user()->profile_picture->src == ''): ?>
<img src="https://d1w2poirtb3as9.cloudfront.net/default.jpeg" class="img--bordered head_profile img--full">
<?php else: ?>
<img src="<?php echo e(@Auth::user()->profile_picture->src); ?>" class="img--bordered img--full head_profile profile_picture">
<?php endif; ?>
</div><div class="grid__item three-quarters"><h3 class="push-half--bottom"><?php echo e(@Auth::user()->first_name); ?> <?php echo e(@Auth::user()->last_name); ?></h3></div></div></li><li><a href="<?php echo e(url('trip')); ?>" class="active"><?php echo e(trans('messages.header.mytrips')); ?></a></li><li><a href="<?php echo e(url('profile')); ?>"><?php echo e(trans('messages.header.profile')); ?></a></li><li><a href="<?php echo e(url('sign_out')); ?>"><?php echo e(trans('messages.header.logout')); ?></a></li></ul></div></li></ul>
</div>
</div>
<!-- </div> -->
</div>
<div class="flash-container">
    <?php if(Session::has('message')): ?>
      <div class="alert text-center participant-alert " style="    background: #1fbad6 !important;color: #fff !important;" role="alert">
        <a href="#" class="alert-close text-white" data-dismiss="alert">&times;</a>
      <?php echo Session::get('message'); ?>

      </div>
    <?php endif; ?>
</div>