<!-- Add the sidebar's background. This div must be placed
       immediately after the control sidebar -->
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

<!-- jQuery 2.1.4 -->
<script src="<?php echo e(url('admin_assets/plugins/jQuery/jQuery-2.1.4.min.js')); ?>"></script>
<script src="<?php echo e(url('admin_assets/plugins/jQueryUI/jquery-ui.min.js')); ?>"></script>

  <!-- Latest compiled and minified JavaScript -->
  <script src="<?php echo e(url('admin_assets/dist/js/bootstrap-select.min.js')); ?>"></script>
<script src="<?php echo e(url('js/angular.js')); ?>"></script>
<script src="<?php echo e(url('js/angular-sanitize.js')); ?>"></script>

<script> 
var app = angular.module('App', ['ngSanitize']);
var APP_URL = <?php echo json_encode(url('/')); ?>; 
var COMPANY_ADMIN_URL = <?php echo json_encode(url('/'.LOGIN_USER_TYPE)); ?>; 
var LOGIN_USER_TYPE = '<?php echo LOGIN_USER_TYPE; ?>';
</script>

<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button);
</script>

<!-- Bootstrap 3.3.5 -->
<script src="<?php echo e(url('admin_assets/bootstrap/js/bootstrap.min.js')); ?>"></script>
<script src="<?php echo e(url('admin_assets/plugins/datepicker/bootstrap-datepicker.js')); ?>"></script>
<?php if(!isset($exception)): ?>   

    <?php if(Route::current()->uri() == 'admin/dashboard' || Route::current()->uri() == 'company/dashboard'): ?>
    	<!-- Morris.js charts -->
      <script src="<?php echo e(url('admin_assets/plugins/morris/raphael-min.js')); ?>"></script>
      <script src="<?php echo e(url('admin_assets/plugins/morris/morris.min.js')); ?>"></script>
      <!-- datepicker -->
      
      <!-- AdminLTE dashboard demo (This is only for demo purposes) -->
		  <script src="<?php echo e(url('admin_assets/dist/js/dashboard.js')); ?>"></script>
    <?php endif; ?>

     <?php if(Route::current()->uri() == 'admin/add_user' || Route::current()->uri() == 'admin/edit_user/{id}'): ?>
      <script src="<?php echo e(url('admin_assets/plugins/datepicker/bootstrap-datepicker.js')); ?>"></script>
    <?php endif; ?>

    <?php if(Route::current()->uri() == 'admin/add_coupon_code' || Route::current()->uri() == 'admin/edit_coupon_code/{id}'): ?>
      <script src="<?php echo e(url('admin_assets/plugins/datepicker/bootstrap-datepicker.js')); ?>"></script>
    <?php endif; ?>

    <?php if(Route::current()->uri() == 'admin/driver' || Route::current()->uri() == 'admin/vehicle' || Route::current()->uri() == 'company/vehicle' || Route::current()->uri() == 'admin/rider' || Route::current()->uri() == 'admin/admin_user' || Route::current()->uri() == 'admin/car_type'|| Route::current()->uri() == 'admin/rating' || Route::current()->uri() == 'company/rating' ||  Route::current()->uri() == 'admin/request' ||  Route::current()->uri() == 'company/request' ||  Route::current()->uri() == 'admin/cancel_trips' ||  Route::current()->uri() == 'company/cancel_trips' ||  Route::current()->uri() == 'admin/trips' ||  Route::current()->uri() == 'company/trips' ||  Route::current()->uri() == 'admin/payments' ||  Route::current()->uri() == 'company/payments'|| Route::current()->uri() == 'admin/pages' || Route::current()->uri() == 'admin/metas' || Route::current()->uri() == 'admin/wallet' || Route::current()->uri() == 'admin/promo_code' || Route::current()->uri() == 'admin/statements/{type}' || Route::current()->uri() == 'company/statements/{type}' || Route::current()->uri() == 'admin/view_driver_statement/{driver_id}' || Route::current()->uri() == 'company/view_driver_statement/{driver_id}' || Route::current()->uri() == 'admin/currency' || Route::current()->uri() == 'admin/locations' || Route::current()->uri() == 'admin/roles' || Route::current()->uri() == 'admin/manage_fare' || Route::current()->uri() == 'admin/language' || Route::current()->uri() == 'admin/help_category' || Route::current()->uri() == 'admin/help_subcategory' || Route::current()->uri() == 'admin/help' || Route::current()->uri() == 'admin/country' || Route::current()->uri() == 'admin/payout/overall' || Route::current()->uri() == 'company/payout/overall' || Route::current()->uri() == 'admin/payout/company/overall' || Route::current()->uri() == 'admin/weekly_payout/{driver_id}' || Route::current()->uri() == 'company/weekly_payout/{driver_id}' || Route::current()->uri() == 'admin/weekly_payout/company/{company_id}' || Route::current()->uri() == 'admin/per_week_report/{driver_id}/{start_date}/{end_date}' || Route::current()->uri() == 'company/per_week_report/{driver_id}/{start_date}/{end_date}' || Route::current()->uri() == 'admin/per_week_report/company/{company_id}/{start_date}/{end_date}' || Route::current()->uri() == 'admin/per_day_report/{driver_id}/{date}' || Route::current()->uri() == 'company/per_day_report/{driver_id}/{date}' || Route::current()->uri() == 'admin/per_day_report/company/{company_id}/{date}' || Route::current()->uri() == 'admin/later_booking' || Route::current()->uri() == 'company/later_booking' || Route::current()->uri() == 'admin/company' || Route::current()->uri() == 'company/driver'): ?>
      <script src="<?php echo e(url('admin_assets/plugins/datatables/jquery.dataTables.min.js')); ?>"></script>
      <script src="<?php echo e(url('admin_assets/plugins/datatables/dataTables.bootstrap.min.js')); ?>"></script>
    <?php endif; ?>

    <?php if(Route::current()->uri() == 'admin/add_room' || Route::current()->uri() == 'admin/edit_room/{id}' || Route::current()->uri() == 'admin/edit_rider/{id}' || Route::current()->uri() == 'admin/add_rider' || Route::current()->uri() == 'admin/edit_page/{id}' || Route::current()->uri() == 'admin/add_page/{id}' || Route::current()->uri() == 'admin/later_booking' || Route::current()->uri() == 'company/later_booking' || Route::current()->uri() == 'admin/add_company' || Route::current()->uri() == 'admin/edit_company/{id}' || Route::current()->uri() == 'admin/company' || Route::current()->uri() == 'company/edit_company/{id}'): ?>
      <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($map_key); ?>&sensor=false&libraries=places"></script>
      <script src="<?php echo e(url('admin_assets/plugins/jQuery/jquery.validate.js')); ?>"></script>
      <script src="<?php echo e(url('admin_assets/dist/js/rooms.js')); ?>"></script>
    <?php endif; ?>

    <?php if(Route::current()->uri() == 'admin/add_vehicle' || Route::current()->uri() == 'admin/edit_vehicle/{id}' || Route::current()->uri() == 'company/add_vehicle' || Route::current()->uri() == 'company/edit_vehicle/{id}'): ?>
      <script src="<?php echo e(url('admin_assets/plugins/jQuery/jquery.validate.js')); ?>"></script>
    <?php endif; ?>

    <?php if(Route::current()->uri() == 'admin/trips' || Route::current()->uri() == 'admin/payments'): ?>
    <script src="<?php echo e(url('admin_assets/dist/js/reports.js')); ?>"></script>
    <?php endif; ?>

    <?php if(Route::current()->uri() == 'admin/add_page' || Route::current()->uri() == 'admin/edit_page/{id}' || Route::current()->uri() == 'admin/send_email' || Route::current()->uri() == 'admin/add_help' || Route::current()->uri() == 'admin/edit_help/{id}'): ?>
    <script src="<?php echo e(url('admin_assets/plugins/editor/editor.js')); ?>"></script>
      <script type="text/javascript"> 
        $("[name='submit']").click(function(){
          $('#content').text($('#txtEditor').Editor("getText"));
          $('#message').text($('#txtEditor').Editor("getText"));
          $('#answer').text($('#txtEditor').Editor("getText"));
        });
      </script>
    <?php endif; ?>

     <?php if(Route::current()->uri() == 'admin/map' || Route::current()->uri() == 'company/map' || Route::current()->uri() == 'admin/detail_request/{id}' || Route::current()->uri() == 'company/detail_request/{id}'): ?>
       <script async defer type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($map_key); ?>&sensor=false&callback=initMap"></script>
     <?php endif; ?>

     <?php if(Route::current()->uri() == 'admin/heat-map' || Route::current()->uri() == 'company/heat-map'): ?>
       <script async defer type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($map_key); ?>&libraries=visualization"></script>
        <script src="<?php echo e(url('admin_assets/dist/js/heat_map.js')); ?>"></script>
     <?php endif; ?>

     <?php if(Route::current()->uri() == 'admin/map' || Route::current()->uri() == 'company/map'): ?>
        <script src="<?php echo e(url('admin_assets/dist/js/map.js')); ?>"></script>
     <?php endif; ?>

     <?php if(Route::current()->uri() == 'admin/manual_booking/{id?}' || Route::current()->uri() == 'company/manual_booking/{id?}'): ?>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($map_key); ?>&sensor=false&libraries=places"></script>
        <script src="<?php echo e(url('admin_assets/dist/js/manual_booking.js')); ?>"></script>
        <script src="<?php echo e(url('admin_assets/dist/js/moment.min.js')); ?>"></script>
        <script src="<?php echo e(url('admin_assets/dist/js/bootstrap-datetimepicker.min.js')); ?>"></script>
        <script src="<?php echo e(url('js/selectize.js')); ?>"></script>
        <script src="<?php echo e(url('admin_assets/plugins/jQuery/jquery.validate.js')); ?>"></script>
     <?php endif; ?>
     
     <?php if(Route::current()->uri() == 'admin/detail_request/{id}' || Route::current()->uri() == 'company/detail_request/{id}'): ?>
       <script src="<?php echo e(url('admin_assets/dist/js/request.js')); ?>"></script>
     <?php endif; ?>

     <?php if(Route::current()->uri() == 'admin/add_location' || Route::current()->uri() == 'admin/edit_location/{id}'): ?>
       <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo e($map_key); ?>&libraries=drawing,places,geometry"></script>
     <?php endif; ?>

     <?php if(Route::current()->uri() == 'admin/add_manage_fare' || Route::current()->uri() == 'admin/edit_manage_fare/{id}' || Route::current()->uri() == 'admin/add_company' || Route::current()->uri() == 'admin/edit_company/{id}' || Route::current()->uri() == 'company/edit_company/{id}'): ?>
       <script src="<?php echo e(url('admin_assets/dist/js/moment.min.js')); ?>"></script>
     <?php endif; ?>

<?php endif; ?>

<!-- AdminLTE App -->
<script src="<?php echo e(url('admin_assets/dist/js/app.js')); ?>"></script>
<script src="<?php echo e(url('admin_assets/dist/js/common.js?v='.str_random(6))); ?>"></script>
  <?php if(Route::current()->uri() == 'company/payout_preferences'): ?>
    <?php echo Html::script('js/common.js'); ?>

  <?php endif; ?>


<!-- AdminLTE for demo purposes -->
<script src="<?php echo e(url('admin_assets/dist/js/demo.js')); ?>"></script>

<?php echo $__env->yieldPushContent('scripts'); ?>

<script type="text/javascript">
  $('#dataTableBuilder_length').addClass('dt-buttons');
  $('#dataTableBuilder_wrapper > div:not("#dataTableBuilder_length").dt-buttons').css('margin-left','20%');
</script>

<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script type="text/javascript">

function googleTranslateElementInit() {

  new google.translate.TranslateElement({pageLanguage: 'en'}, 'google_translate_element');
}

function preventBack(){
  previous_url = document.referrer.substr(document.referrer.lastIndexOf('/') + 1)
  if (previous_url == "signin" || previous_url == "" || previous_url == "signin_company") {
    window.history.forward();
  }
}
setTimeout("preventBack()", 0);
</script>

</body>
</html>