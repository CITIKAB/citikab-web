 <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image">
          <?php 
            if(LOGIN_USER_TYPE=='company'){
              $user = Auth::guard('company')->user();
              $company_user = true;
              $first_segment = 'company';
            }else{
              $user = Auth::guard('admin')->user();
              $company_user = false;
              $first_segment = 'admin';
            }
           ?>
          <?php if(!$company_user || $user->profile ==null): ?>
            <img src="<?php echo e(url('admin_assets/dist/img/avatar04.png')); ?>"  class="img-circle" alt="User Image">
          <?php else: ?>
            <img src="<?php echo e($user->profile); ?>"  class="img-circle" alt="User Image">
          <?php endif; ?>
        </div>
        <div class="pull-left info"> 
          <p><?php echo e((!$company_user)?$user->username:$user->name); ?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
        </div>
      </div>
      <!-- sidebar menu: : style can be found in sidebar.less -->
      <ul class="sidebar-menu">

        <li class="header">MAIN NAVIGATION</li>

        <li class="<?php echo e((Route::current()->uri() == $first_segment.'/dashboard') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/dashboard')); ?>"><i class="fa fa-dashboard"></i><span>Dashboard</span></a></li>

        <?php if(@$user->can('manage_admin')): ?>
          <li class="treeview <?php echo e((Route::current()->uri() == 'admin/admin_users') ? 'active' : ''); ?>">
          <a href="#">
            <i class="fa fa-user-plus"></i> <span>Manage Admin</span> <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo e((Route::current()->uri() == 'admin/admin_user') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/admin_user')); ?>"><i class="fa fa-circle-o"></i><span>Admin Users</span></a></li>
            <li class="<?php echo e((Route::current()->uri() == 'admin/roles') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/roles')); ?>"><i class="fa fa-circle-o"></i><span>Roles & Permissions</span></a></li>
          </ul>
          </li>
        <?php endif; ?>

        <?php if($company_user && $user->id != 1): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/payout_preferences') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/payout_preferences')); ?>"><i class="fa fa-paypal"></i><span>Payout Preferences</span></a></li>
        <?php endif; ?>
        
        <?php if($company_user || @$user->can('manage_send_message')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/send_message') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/send_message')); ?>"><i class="fa fa-bullhorn"></i><span>Send Messages</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('email_settings') || @$user->can('send_email')): ?>
          <li class="treeview <?php echo e((Route::current()->uri() == 'admin/email_settings' || Route::current()->uri() == 'admin/send_email') ? 'active' : ''); ?>">
            <a href="#">
              <i class="fa fa-envelope-o"></i> 
                <span>Manage Emails</span><i class="fa fa-angle-left pull-right"></i>
            </a>
            <ul class="treeview-menu">
              <?php if(@$user->can('send_email')): ?>
                <li class="<?php echo e((Route::current()->uri() == 'admin/send_email') ? 'active' : ''); ?>">
                  <a href="<?php echo e(url('admin/send_email')); ?>"><i class="fa fa-circle-o"></i>
                    <span>Send Email</span>
                  </a>
                </li>
              <?php endif; ?>
              <?php if(@$user->can('email_settings')): ?>
                <li class="<?php echo e((Route::current()->uri() == 'admin/email_settings') ? 'active' : ''); ?>">
                  <a href="<?php echo e(url('admin/email_settings')); ?>"><i class="fa fa-circle-o"></i>
                    <span>Email Settings</span>
                  </a>
                </li>
              <?php endif; ?>
            </ul>
          </li>
        <?php endif; ?>

        <?php if(@$user->can('view_rider')): ?>
          <li class="<?php echo e((Route::current()->uri() == 'admin/rider') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/rider')); ?>"><i class="fa fa-users"></i><span>Manage Riders</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('view_company')): ?>
          <li class="<?php echo e((Route::current()->uri() == 'admin/company') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/company')); ?>"><i class="fa fa-building"></i><span>Manage Company</span></a></li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('view_driver')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/driver') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/driver')); ?>"><i class="fa fa-dribbble"></i><span>Manage Driver</span></a></li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('manage_vehicle')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/vehicle') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/vehicle')); ?>"><i class="fa fa-taxi"></i><span>Manage Vehicles</span></a></li>
        <?php endif; ?>

         <?php if(@$user->can('manage_car_type')): ?>
          <li class="<?php echo e((Route::current()->uri() == 'admin/car_type') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/car_type')); ?>"><i class="fa fa-car"></i><span>Manage Vehicles Type</span></a></li>
        <?php endif; ?>
        

        <?php if(@$user->can('manage_locations')): ?>
             <li class="<?php echo e((Route::current()->uri() == 'admin/locations') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/locations')); ?>"><i class="fa fa-map-o"></i><span>Manage Locations</span></a></li> 
        <?php endif; ?>

        <?php if(($company_user && @$user->status == 'Active') || @$user->can('manage_manual_booking')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/manual_booking/{id?}') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/manual_booking')); ?>"><i class="fa fa-address-book" aria-hidden="true"></i><span>Manual Booking</span></a></li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('manage_manual_booking')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/later_booking') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/later_booking')); ?>"><i class="fa fa-list-alt"></i><span>View Manual Booking</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_peak_based_fare')): ?>
          <li class="<?php echo e((Route::current()->uri() == 'admin/manage_fare') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/manage_fare')); ?>"><i class="fa fa fa-dollar"></i><span>Manage Fare</span></a></li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('manage_map') || @$user->can('manage_heat_map')): ?>
          <li class="treeview <?php echo e((Route::current()->uri() == $first_segment.'/map' || Route::current()->uri() == $first_segment.'/heat-map') ? 'active' : ''); ?>">
          <a href="#">
            <i class="fa fa-map-marker" aria-hidden="true"></i> <span>Manage Map</span> <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo e((Route::current()->uri() == $first_segment.'/map') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/map')); ?>"><i class="fa fa-circle-o"></i><span>Map View</span></a></li>
            <li class="<?php echo e((Route::current()->uri() == $first_segment.'/heat-map') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/heat-map')); ?>"><i class="fa fa-circle-o"></i><span>HeatMap</span></a></li> 
          </ul>
          </li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('manage_trips')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/trips') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/trips')); ?>"><i class="fa fa-taxi"></i><span> Manage Trips</span></a></li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('manage_driver_payments') || @$user->can('manage_company_payments')): ?>
          <li class="treeview <?php echo e((Route::current()->uri() == 'admin/payout/overall' || Route::current()->uri() == 'admin/payout/company/overall' || Route::current()->uri() == 'company/payout/overall') ? 'active' : ''); ?>">
          <a href="#">
            <i class="fa fa-dollar" aria-hidden="true"></i> <span>Manage Payouts</span> <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <?php if(@$user->can('manage_company_payment')): ?>
              <li class="<?php echo e((Route::current()->uri() == 'admin/payout/company/overall') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/payout/company/overall')); ?>"><i class="fa fa-circle-o"></i><span>Company Payouts</span></a></li>
            <?php endif; ?>
            <?php if($company_user || @$user->can('manage_driver_payments')): ?>
              <li class="<?php echo e((Route::current()->uri() == $first_segment.'/payout/overall') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/payout/overall')); ?>"><i class="fa fa-circle-o"></i><span>Driver Payouts</span></a></li>
            <?php endif; ?>
          </ul>
          </li>
        <?php endif; ?>

        <!-- <?php if($company_user || @$user->can('manage_driver_payments') || @$user->can('manage_company_payments')): ?>
          <li class="<?php echo e((Route::current()->uri() == 'admin/payout/overall') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/payout/overall')); ?>"><i class="fa fa-dollar"></i><span> Manage Driver Payouts</span></a></li>
        <?php endif; ?> -->

        <?php if($company_user ||  @$user->can('manage_statements')): ?>
          <li class="treeview <?php echo e((Route::current()->uri() == $first_segment.'/statements/{type}') ? 'active' : ''); ?>">
          <a href="#">
            <i class="fa fa-area-chart"></i> <span>Manage Statements</span> <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo e((Route::current()->uri() == $first_segment.'/statements/overall') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/statements/overall')); ?>"><i class="fa fa-circle-o"></i><span>Overall Ride Statments</span></a></li>
            <li class="<?php echo e((Route::current()->uri() == $first_segment.'/statements/driver') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/statements/driver')); ?>"><i class="fa fa-circle-o"></i><span>Driver Statement</span></a></li>
          </ul>
          </li>
        <?php endif; ?>

        <?php if(@$user->can('manage_wallet') || @$user->can('manage_promo_code')): ?>
          <li class="treeview <?php echo e((Route::current()->uri() == 'admin/wallet' || Route::current()->uri() == 'admin/promo_code') ? 'active' : ''); ?>">
          <a href="#">
            <i class="fa fa-google-wallet"></i> <span>Manage Wallet & Promo</span> <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <?php if($company_user || @$user->can('manage_wallet')): ?>
             <li class="<?php echo e((Route::current()->uri() == 'admin/wallet') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/wallet')); ?>"><i class="fa fa-circle-o"></i><span>Manage Wallet Amount</span></a></li>
            <?php endif; ?>
            <?php if(@$user->can('manage_promo_code')): ?>
              <li class="<?php echo e((Route::current()->uri() == 'admin/promo_code') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/promo_code')); ?>"><i class="fa fa-circle-o"></i><span>Manage Promo Code</span></a></li>
            <?php endif; ?>
          </ul>
          </li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('manage_cancel_trips')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/cancel_trips') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/cancel_trips')); ?>"><i class="fa fa-chain-broken"></i><span>Manage Canceled Trips</span></a></li>
        <?php endif; ?>
        <?php if($company_user || @$user->can('manage_owe_amount')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/owe') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/owe')); ?>"><i class="fa fa-money"></i><span>Manage Owe Amount</span></a>
          </li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('manage_requests')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/request') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/request')); ?>"><i class="fa fa-paper-plane-o"></i><span>Manage Ride Requests</span></a></li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('manage_payments')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/payments') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/payments')); ?>"><i class="fa fa-usd"></i><span>Manage Payments</span></a></li>
        <?php endif; ?>

        <?php if($company_user || @$user->can('manage_rating')): ?>
          <li class="<?php echo e((Route::current()->uri() == $first_segment.'/rating') ? 'active' : ''); ?>"><a href="<?php echo e(url($first_segment.'/rating')); ?>"><i class="fa fa-star"></i><span>Ratings</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_help')): ?>
          <li class="treeview <?php echo e((Route::current()->uri() == 'admin/help' || Route::current()->uri() == 'admin/help_category' || Route::current()->uri() == 'admin/help_subcategory') ? 'active' : ''); ?>">
          <a href="#">
            <i class="fa fa-support"></i> <span>Manage Help</span> <i class="fa fa-angle-left pull-right"></i>
          </a>
          <ul class="treeview-menu">
            <li class="<?php echo e((Route::current()->uri() == 'admin/help') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/help')); ?>"><i class="fa fa-circle-o"></i><span>Help</span></a></li>
            <li class="<?php echo e((Route::current()->uri() == 'admin/help_category') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/help_category')); ?>"><i class="fa fa-circle-o"></i><span>Category</span></a></li>
            <li class="<?php echo e((Route::current()->uri() == 'admin/help_subcategory') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/help_subcategory')); ?>"><i class="fa fa-circle-o"></i><span>Subcategory</span></a></li>
          </ul>
          </li>
        <?php endif; ?>

        <?php if(@$user->can('manage_fees')): ?>
           <li class="<?php echo e((Route::current()->uri() == 'admin/fees') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/fees')); ?>"><i class="fa fa-dollar"></i><span>Manage Fees</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_metas')): ?>
           <li class="<?php echo e((Route::current()->uri() == 'admin/metas') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/metas')); ?>"><i class="fa fa-bar-chart"></i><span>Manage Metas</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_static_pages')): ?>
           <li class="<?php echo e((Route::current()->uri() == 'admin/pages') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/pages')); ?>"><i class="fa fa-newspaper-o"></i><span>Manage Static Pages</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_currency')): ?>
           <li class="<?php echo e((Route::current()->uri() == 'admin/currency') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/currency')); ?>"><i class="fa fa-eur"></i><span>Manage Currency</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_language')): ?>
           <li class="<?php echo e((Route::current()->uri() == 'admin/language') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/language')); ?>"><i class="fa fa-language"></i><span>Manage Language</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_country')): ?>
          <li class="<?php echo e((Route::current()->uri() == 'admin/country') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/country')); ?>"><i class="fa fa-globe"></i><span>Manage Country</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_api_credentials')): ?>
          <li class="<?php echo e((Route::current()->uri() == 'admin/api_credentials') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/api_credentials')); ?>"><i class="fa fa-gear"></i><span>Api Credentials</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_payment_gateway')): ?>
          <li class="<?php echo e((Route::current()->uri() == 'admin/payment_gateway') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/payment_gateway')); ?>"><i class="fa fa-paypal"></i><span>Payment Gateway</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_join_us')): ?>
          <li class="<?php echo e((Route::current()->uri() == 'admin/join_us') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/join_us')); ?>"><i class="fa fa-share-alt"></i><span>Join Us Links</span></a></li>
        <?php endif; ?>

        <?php if(@$user->can('manage_site_settings')): ?>
           <li class="<?php echo e((Route::current()->uri() == 'admin/site_setting') ? 'active' : ''); ?>"><a href="<?php echo e(url('admin/site_setting')); ?>"><i class="fa fa-cogs"></i><span>Site Setting</span></a></li>
        <?php endif; ?>


      </ul>
    </section>
    <!-- /.sidebar -->
  </aside>