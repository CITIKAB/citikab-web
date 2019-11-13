<?php $__env->startSection('main'); ?>
  <div class="content-wrapper" ng-controller='later_booking'>
    <section class="content-header">
      <h1>
        Manage Bookings
        <small>Control panel</small>
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/dashboard')); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Bookings</li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Manage Bookings </h3>
            </div>
            <div class="box-body">
              <?php echo $dataTable->table(); ?>

            </div>
          </div>
        </div>
      </div>
    </section>
    <div class="modal fade" id="cancel_popup" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Booking Cancel</h4>
          </div>
          <div class="modal-body">
            <?php echo Form::open(['method'=>'POST','url' => 'admin/manual_booking/cancel', 'class' => 'form-horizontal manual_booking_cancel','id'=>'manual_booking_cancel']); ?>

              <?php echo Form::hidden('manual_booking_id', '', ['ng-model' => 'manual_booking_cancel_id']); ?>

              <div class="row">
                <div class="col-md-3">
                  Cancel Reason
                </div>
                <div class="col-md-9">
                  <?php echo Form::textarea('cancel_reason', '', ['class' => 'form-control', 'id' => 'input_cancel_reason', 'placeholder' => 'Cancel Reason']); ?>

                </div>
              </div>
              <div class="row" align="center">
                <input type="submit" name="submit" class="btn btn-primary">
              </div>
            <?php echo Form::close(); ?>

          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="cancel_reason_popup" role="dialog">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Booking Cancel Reason</h4>
          </div>
          <div class="modal-body">
            <p>Cancel By: <span class="cancel_by">{{cancel_by}}</span></p>
            <p>Cancel Reason: <span class="cancel_reason">{{cancel_reason}}</span></p>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
  <link rel="stylesheet" href="<?php echo e(url('css/buttons.dataTables.css')); ?>">
  <script src="<?php echo e(url('js/dataTables.buttons.js')); ?>"></script>
  <script src="<?php echo e(url('js/buttons.server-side.js')); ?>"></script>
  <script type="text/javascript">
    var REQUEST_URL = "<?php echo e(url('/'.LOGIN_USER_TYPE)); ?>"; 
  </script>
  <?php echo $dataTable->scripts(); ?>

<?php $__env->stopPush(); ?>
<style type="text/css">
  .fa-eye{
    font-size: 20px !important;
  }
</style>
<?php echo $__env->make('admin.template', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>