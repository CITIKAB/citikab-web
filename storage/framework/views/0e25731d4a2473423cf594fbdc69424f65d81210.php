<?php $__env->startSection('main'); ?>
<div class="container-fluid fixed-header" style="background:rgb(248, 248, 249);z-index:99;">
<ul class="sub-header">
<li><a href="<?php echo e(url('safety')); ?>"><?php echo e(trans('messages.footer.safety')); ?></a></li>
<li><a href="<?php echo e(url('how_it_works')); ?>"><?php echo e(trans('messages.header.how_it_works')); ?></a></li>
</ul>
</div>
<div class="container-fluid ride-div-main" style="padding:0px !important;">
<div style="position:relative;float:left;width:100%;">
<div class="slide-img slide-img-ride"></div>

<div class="pattern" style="height: 520px; width: 274px; right: 0px; position: absolute; z-index: 10;"><div style="background-color: #A6DAEC; height: 100%; overflow: hidden;"><div aria-label="Decorative pattern" style="height: 100%;"><div class="isvg loaded" style="height: 100%;"><img src="<?php echo e(url('images/icon/patten_274_520.jpg')); ?>">
</div>
</div>
</div>
</div>
<?php if(Auth::user()==null): ?>
<div class="mini-green ride-mini-green" >
    <div href="#" class="_style_4jQAPw green-mini-div" style="width: 206px; padding: 32px 20px 20px 32px; display: block; position: relative; height: 206px; background-color: rgb(55, 112, 55);">
    <div class="_style_1PPmFR" style="font-weight: 500; color: rgb(255, 255, 255);font-size: 21px; line-height: 1.4;"><?php echo e(trans('messages.ride.ride_with_gofer',['site_name'=>$site_name])); ?></div>
<a class="btn btn--primary btn--arrow position--relative error-retry-btn" href="<?php echo e(url('signup_rider')); ?>" style= "background: transparent !important;     border: none !important;    float: right;margin-top: 55px;    margin-right: -16px;">
<div class="block-context soft-small--right"><?php echo e(trans('messages.home.siginup')); ?></div>
<i class="icon_right-arrow-thin icon transition delta position--absolute"></i>
</a>
    </div>
    </div>
<?php endif; ?>
</div>
</div>
<div class="container-fluid pad-sm-20 height-fluid" style="padding:0px;background:#fff !important;">
<div class="col-lg-10 col-lg-push-2 col-md-12 col-sm-12 col-xs-12 ride-always">
<div class="pad-44">
<h1 class="slide-head ride-head"><?php echo e(trans('messages.ride.always_ride')); ?></h1><p class="ride-content slide-content">
<?php echo e(trans('messages.ride.get_whatever')); ?></p>
<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12  column-content" style="padding:0px;">
	<div class="arrive-content">
<div style="position: relative !important;"><p class="_style_ZJW1y" style="
    margin-bottom: 25px !important;"><?php echo e(trans('messages.ride.tap_button')); ?></p>
</div><div><p class="cmln__paragraph"><?php echo e(trans('messages.ride.tap_button_content')); ?></p>
</div></div>
	</div>
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12   column-content" style="padding:0px;">
	<div class="arrive-content">
<div style="position: relative !important;"><p class="_style_ZJW1y" style="
    margin-bottom: 25px !important;"><?php echo e(trans('messages.ride.always_on')); ?></p>
</div><div><p class="cmln__paragraph"><?php echo e(trans('messages.ride.always_on_content')); ?></p>
</div></div>
	</div>
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12  column-content" style="padding:0px;">

	<div class="arrive-content">
<div style="position: relative !important;"><p class="_style_ZJW1y" style="
    margin-bottom: 25px !important;"><?php echo e(trans('messages.ride.we_listen')); ?> </p>
</div><div><p class="cmln__paragraph"><?php echo e(trans('messages.ride.we_listen_content')); ?></p>
</div></div>
	</div>
</div>
</div>
</div>
<div class="container-fluid" style="padding:0px;background:#F8F8F9!important">
<div class="col-lg-10 col-lg-push-2 col-md-12 col-sm-12 col-xs-12 ride-always" style="background:#F8F8F9!important;margin-top:0px !important;padding: 0px !important;padding-bottom: 0px !important;">
<div class="pad-44">
<h1 class="slide-head ride-slide"><?php echo e(trans('messages.ride.every_price')); ?></h1><p class="slide-content">
<?php echo e(trans('messages.ride.any_occasion')); ?></p>
<div class="col-lg-11 col-md-11 col-sm-12 col-xs-12" id="pad-sm-zero">
   <div id="mySliderTabsContainer" style="   margin-top: 25px;">
           
            <div id="mySliderTabs">
              <ul>
                <li><a href="#GoferGO"><?php echo e(trans('messages.ride.economy')); ?></a></li> 
                <li><a href="#GoferX"><?php echo e(trans('messages.ride.premium')); ?></a></li> 
                <li><a href="#GoferXL"><?php echo e(trans('messages.ride.accessibility')); ?></a></li> 
              </ul>
              <div id="GoferGO">
                <img src="<?php echo e(url('images/icon/slider_eco.jpg')); ?>">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;margin-top:50px;">
                </div>
              </div>     
              <div id="GoferX">
                <img src="<?php echo e(url('images/icon/slider_pre.png')); ?>">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;margin-top:50px;">
                </div>
              </div>     
              <div id="GoferXL">
                <img src="<?php echo e(url('images/icon/slider_acc.jpg')); ?>">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;margin-top:50px;">
                </div>
              </div>     
            </div>

            <div class="clear: both;">

            </div>
          </div>
</div>
</div>
</div>
</div>
	
	<div class="contianer-fluid" style="    background-color: #F8F8F9!important;">
	<div class="container ash-cont">
	<p class="_style_ZJW1y" style="
    margin-bottom: 25px !important;"><?php echo e(trans('messages.footer.safety')); ?></p>
    <h1 class="slide-head ride-head"><?php echo e(trans('messages.ride.start_finish')); ?></h1><p class="ride-content slide-content">
<?php echo e(trans('messages.ride.your_safety')); ?></p>
<a href="<?php echo e(url('safety')); ?>" class="btn btn--link hard--bottom rider-signup text--uppercase primary-font--bold  borderless--left" href="#"><?php echo e(trans('messages.ride.keep_safe')); ?>

<i class="icon icon_right-arrow push-tiny--left"></i></a>
	</div>
	</div>
	<div class="container-fluid" style="padding:0px;">
	<div class="col-lg-11 col-lg-push-1 col-sm-push-1 col-md-12 col-sm-11 col-xs-12 pattern-ride" >
	<div class="pattern" id="pattern" style="display:block !important;height: 100% !important; left: 0px; position: absolute; z-index: 10;">
	<div style="background-color:#A6DAEC; height: 100%; overflow: hidden;"><div aria-label="Decorative pattern" style="height: 100%;"><div class="isvg loaded" style="height: 100%;">
<img src="<?php echo e(url('images/icon/patten_90_238.jpg')); ?>">
</div>
</div>
</div>
</div>
<div class="col-lg-9 col-md-10 col-sm-12 col-xs-11 pad-ride-red">
<p class="ride-content slide-content col-lg-6 col-md-6 col-sm-6 col-xs-12">
<?php echo e(trans('messages.ride.your_first')); ?> <?php echo e($site_name); ?> <?php echo e(trans('messages.ride.min_away')); ?></p>
<?php if(Auth::user()==null): ?>
<a class="pull-right btn btn--primary btn--arrow position--relative error-retry-btn width-sm mar-top-37" href="<?php echo e(url('signup_rider')); ?>">
<div class="block-context soft-small--right" style="    width: 180px;    font-size: 13px !important;"><?php echo e(trans('messages.footer.siginup_ride')); ?></div>
<i class="icon_right-arrow-thin icon transition delta position--absolute"></i>
</a>
<?php endif; ?>
</div>
	</div>
	</div>
	<div class="container-fluid con-back-div" style="background-color: #F1F1F1!important;">
	<div class="col-lg-10 col-lg-push-2 col-md-12 col-sm-12 col-xs-12" style="overflow:hidden;padding:0px;">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;">
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 mar-height ride-three" style="padding-right: 20px; padding-left: 20px;">
<img src="images/icon/suitcase.png" class="cont-img">
<div class="arrive-content">
<div style="position: relative !important;">
<a style="text-decoration:none !important;font-size: 15px;"><?php echo e(trans('messages.ride.business_travel')); ?></a>
<p class="_style_ZJW1y" style="margin: 10px 0px 15px !important; min-height: unset;"><?php echo e(trans('messages.ride.trip_seperate')); ?></p>
</div><div><p class="cmln__paragraph"><?php echo e(trans('messages.ride.trip_seperate_content')); ?> <?php echo e($site_name); ?> <?php echo e(trans('messages.ride.hard_as')); ?></p>
</div></div>
</div>
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 mar-height ride-three" style="padding-right: 20px; padding-left: 20px;">
<img src="images/icon/two_person.png" class="cont-img">
<div class="arrive-content">
<div style="position: relative !important;">
<a style="text-decoration:none !important;font-size: 15px;"><?php echo e($site_name); ?> <?php echo e(trans('messages.ride.pool')); ?></a>
<p class="_style_ZJW1y" style="margin: 10px 0px 15px !important; min-height: unset;"><?php echo e(trans('messages.ride.share_save')); ?></p>
</div><div><p class="cmln__paragraph"><?php echo e($site_name); ?> <?php echo e(trans('messages.ride.pool_content')); ?> <?php echo e($site_name); ?>. <?php echo e(trans('messages.ride.ride_adds')); ?> </p>
</div></div>
</div>
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 mar-height ride-three" style="padding-right: 20px; padding-left: 20px;">
<img src="images/icon/half_plane.png" class="cont-img">
<div class="arrive-content">
<div style="position: relative !important;">
<a style="text-decoration:none !important;font-size: 15px;"><?php echo e(trans('messages.ride.airport')); ?></a>
<p class="_style_ZJW1y" style="margin: 10px 0px 15px !important; min-height: unset;"><?php echo e(trans('messages.ride.shuttle')); ?></p>
</div><div><p class="cmln__paragraph"><?php echo e(trans('messages.ride.shuttle_content')); ?></p>
</div></div>
</div>
</div>

</div>
</div>

</main>
<?php $__env->stopSection(); ?>
<style type="text/css">
	.arrive-content{width: 100% !important;    padding-right: 60px;}
	.arrive-content ._style_ZJW1y {
    font-weight: 200 !important;
    color: #494949 !important;
    font-size: 26px !important;
    line-height: 30px;
    min-height: 60px;
}
#mySliderTabsContainer .btn--bit{padding:0px;
    height: auto !important;}
#mySliderTabsContainer .btn--bit i {
    background: #fff;
    padding: 11px;
}
.page-footer-back{
	background: #f8f8f9;
}
#mySliderTabsContainer .btn--bit i:hover {
    background: #e3e3e3;
   color: #000;
}
.btn-input:hover, .btn:hover, .file-input:hover, .tooltip:hover , .btn, .btn-input, .file-input, .tooltip{background:transparent !important; border: none !important}
.btn.btn--primary:hover{background:transparent !important; color: #fff !important;}
</style>
<?php echo $__env->make('templatesign', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>