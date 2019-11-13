<div class="container-fluid page-footer-back" style="padding: 0">
	<div class="footer-img1  footercontent"><img src="<?php echo e(url('images/icon/footer2_2.png')); ?>"></div>
</div>

<footer class="container-fluid" style="background:#000;">

	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 footer-back pull-app-gutter--sides soft-app-gutter--sides">
		<div class="footer-head nt_fot col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="layout">
				<div class="layout_item col-lg-3 col-md-4 col-sm-4 col-xs-12">
					<a href="<?php echo e(url('/')); ?>"><img class="footer-logo" src="<?php echo e(url(PAGE_LOGO_URL)); ?>"></a>
				</div>
				<?php if(!Auth::user()): ?>
				<div class="layout_item col-lg-3 col-md-4 col-sm-4 col-xs-6">

					<a href="<?php echo e(url('signup_rider')); ?>" class="btn btn--reverse" style="margin: 0px;
					width: 165px;overflow: hidden;text-overflow: ellipsis;"><?php echo e(trans('messages.footer.siginup_ride')); ?></a>

				</div>
				<div class="layout_item col-lg-3 col-md-4 col-sm-4 col-xs-6 sm-pull-right">
					<a href="<?php echo e(url('signup_driver')); ?>" style="width: 165px;overflow: hidden;text-overflow: ellipsis;" class="btn btn--reverse-outline"><?php echo e(trans('messages.footer.driver')); ?></a>
				</div>
				<?php if(Auth::guard('company')->user()==null): ?>
				<div class="layout_item col-lg-3 col-md-4 col-sm-4 col-xs-6 sm-pull-right">
					<a href="<?php echo e(url('signup_company')); ?>" style="width: 165px;overflow: hidden;text-overflow: ellipsis;" class="btn btn--reverse-outline"><?php echo e(trans('messages.home.become_company')); ?></a>
				</div>
				<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 footer-back pull-app-gutter--sides soft-app-gutter--sides" style="padding-top: 35px !important;">
		<div class="footer-head">
			<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 social-icons">
				<div class="foot_soc">
					<?php for($i=0; $i < count($join_us); $i++): ?>
					<?php if($join_us[$i]->value): ?>
					<a href="<?php echo e($join_us[$i]->value); ?>" target="_blank"> 
						<span class="fa fa-<?php echo e(str_replace('_','-',$join_us[$i]->name)); ?>"></span>
					</a>        
					<?php endif; ?>
					<?php endfor; ?>
				</div>
				<div class="app-links clearfix">
				<?php if($app_links[2]->value !="" || $app_links[0]->value !="" ): ?>
					<div class="app-title col-xs-12 p-0">
						<?php echo e(trans('messages.footer.rider_app')); ?>

					</div>
					<?php endif; ?>
					<?php if($app_links[0]->value !="" ): ?>
					<a class="googleplay_class" href="<?php echo e($app_links[0]->value); ?>" target="_blank">
							<img src="<?php echo e(url('images/appstore.svg')); ?>" alt="Download on the Appstore" class="CToWUd">
						</a>
					<?php endif; ?>
				<?php if($app_links[2]->value !="" ): ?>
						<a href="<?php echo e($app_links[2]->value); ?>" target="_blank" class="ios_class">
							<img src="<?php echo e(url('images/icon/google-play1.png')); ?>" alt="Get it on Googleplay" class="CToWUd bot_footimg">
						</a>
					<?php endif; ?>
				</div>
			</div>
			<div class="col-lg-1 col-md-2 col-sm-2 col-xs-12">
				<ul class="nav-list-one " style="padding:0px 15px;">
					<li>
						<a href="<?php echo e(url('ride')); ?>" class="city-link"><?php echo e(trans('messages.footer.ride')); ?>

						</a>
					</li>
					<li>
						<a href="<?php echo e(url('drive')); ?>" class="city-link"><?php echo e(trans('messages.footer.drive')); ?>

						</a>
					</li>
					<li>
						<a href="<?php echo e(url('safety')); ?>" class="city-link"><?php echo e(trans('messages.footer.safety')); ?>

						</a>
					</li>

				</ul>
			</div>
			<div class="col-lg-2 col-md-3 col-sm-3 col-xs-12">
				<ul class="nav-list-one " id="top-city-footer-small" style="padding:0px 15px;">
					<?php $__currentLoopData = $company_pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company_page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<li>
						<a class="_style_2HGMjk" href="<?php echo e(url($company_page->url)); ?>">
							<?php echo e($company_page->name); ?>

						</a>
					</li>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					<li>
						<a class="_style_2HGMjk" href="<?php echo e(url('how_it_works')); ?>"> 
							How It Works 
						</a>
					</li> 
				</ul>
			</div>
			<div class="col-lg-2 col-md-3 col-sm-3 col-xs-12">
				<div class="currency_select">
					<?php echo Form::select('language',$language, (Session::get('language')) ? Session::get('language') : $default_language[0]->value, ['class' => 'select payment-select paysel', 'aria-labelledby' => 'language-selector-label', 'id' => 'js-language-select','style'=>"width: 100%; background-color: #000;"]); ?>

				</div>
			</div>
			<div class="col-lg-2 col-md-3 col-sm-3 col-xs-12">
				<div class="currency_select">
					<select id="js-currency-select" class="select payment-select paysel" style="width: 100%; background-color: #000;">
						<?php $__currentLoopData = $currency_select; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($code); ?>" <?php if(session('currency') == $code ): ?> selected="selected" <?php endif; ?> ><?php echo e($code); ?>

						</option>
						<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
					</select>
				</div>
			</div>
			<div class="col-lg-4 col-md-3 col-sm-3 col-xs-12">
				
					<div class="app-links clearfix mobile-links">
						<?php if($app_links[2]->value !="" || $app_links[0]->value !="" ): ?>
							<div class="app-title col-xs-12 p-0">
							<?php echo e(trans('messages.footer.rider_app')); ?>

							</div>
						<?php endif; ?>
				<?php if($app_links[0]->value !="" ): ?>
					<a class="googleplay_class" href="<?php echo e($app_links[0]->value); ?>" target="_blank">
							<img src="<?php echo e(url('images/appstore.svg')); ?>" alt="Download on the Appstore" class="CToWUd">
						</a>
					<?php endif; ?>
				<?php if($app_links[2]->value !="" ): ?>
						<a href="<?php echo e($app_links[2]->value); ?>" target="_blank" class="ios_class">
							<img src="<?php echo e(url('images/icon/google-play1.png')); ?>" alt="Get it on Googleplay" class="CToWUd bot_footimg">
						</a>
					<?php endif; ?>
				</div>

				<div class="app-links clearfix">
				<?php if($app_links[3]->value !="" || $app_links[1]->value !="" ): ?>
					<div class="app-title col-xs-12 p-0">
						<?php echo e(trans('messages.footer.driver_app')); ?>

					</div>
					<?php endif; ?>
				<?php if($app_links[1]->value !="" ): ?>
				<a class="googleplay_class" href="<?php echo e($app_links[1]->value); ?>" target="_blank">
						<img src="<?php echo e(url('images/appstore.svg')); ?>" alt="Download on the Appstore" class="CToWUd">
					</a>
					<?php endif; ?>

				<?php if($app_links[3]->value !="" ): ?>

					<a href="<?php echo e($app_links[3]->value); ?>" target="_blank" class="ios_class">
						<img src="<?php echo e(url('images/icon/google-play1.png')); ?>" alt="Get it on Googleplay" class="CToWUd bot_footimg">
					</a>
					<?php endif; ?>

				</div>
			</div>
			<div class="col-lg-12 col-sm-12 col-md-12 col-xs-12" >
				<div class="text-center footlo">
					<span class="_style_zVjAb" dir="ltr" data-reactid="661">© <?php echo e($site_name); ?>, Inc.</span>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</footer>
<style type="text/css">
	footer .nav-list-one li {
		padding-bottom: 5px;
	}
	#top-city-footer li {
		display: inline-block;
		padding-right: 15px;
	}
	#top-city-footer li a {
		font-size: 13px !important;
	}
</style>