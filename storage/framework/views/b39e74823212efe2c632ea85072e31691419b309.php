<?php $__env->startSection('main'); ?>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Api Credentials
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/dashboard')); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Api Credential</a></li>
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
              <h3 class="box-title">Api Credentials Form</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
              <?php echo Form::open(['url' => 'admin/api_credentials', 'class' => 'form-horizontal']); ?>

              <div class="box-body">
              <span class="text-danger">(*)Fields are Mandatory</span>
                <div class="form-group">
                  <label for="input_google_map_key" class="col-sm-3 control-label">Google Map Key<em class="text-danger">*</em></label>

                  <div class="col-sm-6">
                    <?php echo Form::text('google_map_key', $result[0]->value, ['class' => 'form-control', 'id' => 'input_google_map_key', 'placeholder' => 'Google Map KEY']); ?>

                    <span class="text-danger"><?php echo e($errors->first('google_map_key')); ?></span>
                  </div>
                </div>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label for="input_google_map_server_key" class="col-sm-3 control-label">Google Map Server Key<em class="text-danger">*</em></label>

                  <div class="col-sm-6">
                    <?php echo Form::text('google_map_server_key', $result[1]->value, ['class' => 'form-control', 'id' => 'input_google_map_server_key', 'placeholder' => 'Google Map Server Key']); ?>

                    <span class="text-danger"><?php echo e($errors->first('google_map_server_key')); ?></span>
                  </div>
                </div>
              </div>
              
              <div class="box-body">
                <div class="form-group">
                  <label for="input_twillo_sid" class="col-sm-3 control-label">Twillo SID <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('twillo_sid', $result[2]->value, ['class' => 'form-control', 'id' => 'input_twillo_sid', 'placeholder' => 'Twillo SID']); ?>

                    <span class="text-danger"><?php echo e($errors->first('twillo_sid')); ?></span>
                  </div>
                </div>
              </div>

              <div class="box-body">
                <div class="form-group">
                  <label for="input_twillo_token" class="col-sm-3 control-label">Twillo Token <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('twillo_token', $result[3]->value, ['class' => 'form-control', 'id' => 'input_twillo_token', 'placeholder' => 'Twillo Token']); ?>

                    <span class="text-danger"><?php echo e($errors->first('twillo_token')); ?></span>
                  </div>
                </div>
              </div>

              <div class="box-body">
                <div class="form-group">
                  <label for="input_twillo_from" class="col-sm-3 control-label">Twillo From Number <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('twillo_from', $result[4]->value, ['class' => 'form-control', 'id' => 'input_twillo_from', 'placeholder' => 'Twillo From Number']); ?>

                    <span class="text-danger"><?php echo e($errors->first('twillo_from')); ?></span>
                  </div>
                </div>
              </div>

              <div class="box-body">
                <div class="form-group">
                  <label for="input_fcm_server_key" class="col-sm-3 control-label">FCM Server Key <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('fcm_server_key', $result[5]->value, ['class' => 'form-control', 'id' => 'input_fcm_server_key', 'placeholder' => 'FCM Server Key ']); ?>

                    <span class="text-danger"><?php echo e($errors->first('fcm_server_key')); ?></span>
                  </div>
                </div>
              </div>

              <div class="box-body">
                <div class="form-group">
                  <label for="input_fcm_sender_id" class="col-sm-3 control-label">FCM Sender Id <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('fcm_sender_id', $result[6]->value, ['class' => 'form-control', 'id' => 'input_fcm_sender_id', 'placeholder' => 'FCM Sender Id']); ?>

                    <span class="text-danger"><?php echo e($errors->first('fcm_sender_id')); ?></span>
                  </div>
                </div>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label for="input_fcm_sender_id" class="col-sm-3 control-label">Facebook Client ID<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('facebook_client_id', @$result[7]->value, ['class' => 'form-control', 'id' => 'input_facebook_client_id', 'placeholder' => 'Facebook Client ID']); ?>

                    <span class="text-danger"><?php echo e($errors->first('facebook_client_id')); ?></span>
                  </div>
                </div>
              </div>
              <div class="box-body">
                <div class="form-group">
                  <label for="input_fcm_sender_id" class="col-sm-3 control-label">Facebook Client Secret<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('facebook_client_secret', @$result[8]->value, ['class' => 'form-control', 'id' => 'input_facebook_client_secret', 'placeholder' => 'Facebook Client Secret']); ?>

                    <span class="text-danger"><?php echo e($errors->first('facebook_client_secret')); ?></span>
                  </div>
                </div>
              </div>

              <div class="box-body">
                <div class="form-group">
                  <label for="input_fcm_sender_id" class="col-sm-3 control-label"> Google Client ID <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('google_client', old('google_client',@$result[11]->value), ['class' => 'form-control', 'id' => 'input_account_secret', 'placeholder' => 'Google Client Id']); ?>

                    <span class="text-danger"><?php echo e($errors->first('google_client')); ?></span>
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