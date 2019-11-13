<?php $__env->startSection('main'); ?>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Payment Gateway
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/dashboard')); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/payment_gateway')); ?>">Payment Gateway</a></li>
        <li class="active">Edit</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- right column -->
        <div class="col-md-8 col-sm-offset-2">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Payment Gateway Form</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
              <?php echo Form::open(['url' => 'admin/payment_gateway', 'class' => 'form-horizontal']); ?>

              <div class="box-body">
              <span class="text-danger">(*)Fields are Mandatory</span>
                

              <div class="box-body">
                <div class="form-group">
                  <label for="input_fcm_sender_id" class="col-sm-3 control-label">Merchant ID<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('merchant_id', @$result[0]->value, ['class' => 'form-control', 'id' => 'input_merchant_id', 'placeholder' => 'Merchant ID']); ?>

                    <span class="text-danger"><?php echo e($errors->first('merchant_id')); ?></span>
                  </div>
                </div>
              </div>

              <div class="box-body">
                <div class="form-group">
                  <label for="input_fcm_sender_id" class="col-sm-3 control-label">Merchant Key<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('merchant_key', @$result[1]->value, ['class' => 'form-control', 'id' => 'input_merchant_key', 'placeholder' => 'Merchant Key']); ?>

                    <span class="text-danger"><?php echo e($errors->first('merchant_key')); ?></span>
                  </div>
                </div>
              </div>
              <div class="form-group">
                  <label for="input_paypal_mode" class="col-sm-3 control-label">Mode</label>

                  <div class="col-sm-6">
                    <?php echo Form::select('url', array('https://demo.api.gladepay.com/payment' => 'Sandbox', 'https://api.gladepay.com/payment' => 'Live'), $result[2]->value, ['class' => 'form-control', 'id' => 'input_paypal_mode']); ?>

                    <span class="text-danger"><?php echo e($errors->first('url')); ?></span>
                  </div>
                </div>


              </div>

              <!-- /.box-body -->
              <div class="box-footer">
                <button type="submit" class="btn btn-info pull-right" name="submit" value="submit">Submit</button>
                <button type="submit" class="btn btn-default pull-left" name="cancel" value="cancel">Cancel</button>
              </div>
              <!-- /.box-footer -->
            <?php echo Form::close(); ?>

          </div>
          <!-- /.box -->
        </div>
        <!--/.col (right) -->
      </div>
      <!-- /.row -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.template', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>