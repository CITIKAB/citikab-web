<link rel="shortcut icon" href="<?php echo e($favicon); ?>">
<!-- <div class="flash-container">
    <?php if(Session::has('message')): ?>
      <div class="alert text-center participant-alert " style="    background: #1fbad6 !important;color: #fff !important;" role="alert">
        <a href="#" class="alert-close text-white" data-dismiss="alert">&times;</a>
      <?php echo Session::get('message'); ?>

      </div>
    <?php endif; ?>
</div> -->


<?php $__env->startSection('main'); ?>
<div class="ux-content text-center signin" ng-controller="user">
    <a href="<?php echo e(url('/')); ?>">
      <img class="white_logo" src="<?php echo e($logo_url); ?>" style="width: 109px;height: 50px;background-color: white;background-size: contain;">
    </a>
    <div class="stage-wrapper narrow portable-one-whole forward" id="app-body" data-reactid="10" style="margin-top: 0px;">
      <div class="soft-tiny" data-reactid="11">
         <div data-reactid="12">
            <form class="push--top-small forward" method="POST" data-reactid="13">
               <input type="hidden" name="user_type" value="Company" id="user_type">
               <div data-reactid="15" class="email_phone-sec">
               <h4 data-reactid="14" style="text-align: left;"><?php echo e(trans('messages.header.signin')); ?></h4>

                  <div style="-moz-box-sizing:border-box;font-family:ff-clan-web-pro, &quot;Helvetica Neue&quot;, Helvetica, sans-serif;font-weight:500;font-size:12px;line-height:24px;text-align:none;color:#939393;box-sizing:border-box;margin-bottom:0;margin-top:0;" data-reactid="16"></div>
                  <div style="width:100%;" data-reactid="17">
                     <div style="font-family:ff-clan-web-pro, &quot;Helvetica Neue&quot;, Helvetica, sans-serif;font-weight:500;font-size:14px;line-height:24px;text-align:none;color:#3e3e3e;box-sizing:border-box;margin-bottom:24px;" data-reactid="19">
                        <div class="_style_CZTQ8" data-reactid="20">
                           <input class="text-input input-group-addon" id="email_phone" placeholder="<?php echo e(trans('messages.user.email_address')); ?>" autocorrect="off" autocapitalize="off" name="textInputValue" data-reactid="21" type="text" value="">
                        </div>
                        <div class="_style_CZTQ8 signin-email-error">
                        <span class="text-danger email-error" id="email-error"></span>
                        </div>
                     </div>
                  </div>
               </div>
               <h3 class="email_or_phone password-sec hide text-center" style="margin-top: 0px;margin-bottom: 20px;"></h3>
               <div data-reactid="15" class="password-sec hide">
                  <div style="-moz-box-sizing:border-box;font-family:ff-clan-web-pro, &quot;Helvetica Neue&quot;, Helvetica, sans-serif;font-weight:500;font-size:12px;line-height:24px;text-align:none;color:#939393;box-sizing:border-box;margin-bottom:0;margin-top:0;" data-reactid="16"></div>
                  <div style="width:100%;" data-reactid="17">
                     <div style="font-family:ff-clan-web-pro, &quot;Helvetica Neue&quot;, Helvetica, sans-serif;font-weight:500;font-size:14px;line-height:24px;text-align:none;color:#3e3e3e;box-sizing:border-box;margin-bottom:24px;" data-reactid="19">
                        <div class="_style_CZTQ8" data-reactid="20">
                           <input class="text-input input-group-addon password_btn" id="password" placeholder="<?php echo e(trans('messages.user.paswrd')); ?>" autocorrect="off" autocapitalize="off" name="password" data-reactid="21" type="password" value="">
                        </div>
                        <div class="_style_CZTQ8 signin-email-error">
                        <span class="text-danger email-error"></span>
                        </div>
                     </div>
                  </div>
               </div>
               <button class="btn btn--arrow btn--full blue-signin-btn singin_rider email_phone-sec-1" data-reactid="22" data-type='email'><span class="push-small--right" data-reactid="23"><?php echo e(trans('messages.user.next')); ?></span><i class="fa fa-long-arrow-right icon icon_right-arrow-thin"></i></button>

            </form>
         </div>
         <div class="small push-small--bottom push-small--top" id="sign-up-link-only" data-reactid="26">
            <p class=" display--inline email_phone-sec" data-reactid="27"><?php echo e(trans('messages.user.no_account')); ?><a href="<?php echo e(url('signup_company')); ?>"><?php echo e(trans('messages.home.siginup')); ?></a></p>
            <p class="pull-right forgot password-sec hide">
               <a href="<?php echo e(url('forgot_password_company')); ?>" class="forgot-password"><?php echo e(trans('messages.user.forgot_paswrd')); ?></a>
            </p>
         </div>
      </div>
   </div>
</div>
</main>
<style>
   .logo-link
   {
      display: none;
   }
   .funnel
   {
    height: 0px !important;
   }

</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('templatesign', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>