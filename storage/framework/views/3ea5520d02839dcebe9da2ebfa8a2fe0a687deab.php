<?php $__env->startSection('main'); ?>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Site Settings
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/dashboard')); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Site Settings</a></li>
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
              <h3 class="box-title">Site Settings Form</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
              <?php echo Form::open(['url' => 'admin/site_setting', 'class' => 'form-horizontal', 'files' => true]); ?>

              <div class="box-body">
              <span class="text-danger">(*)Fields are Mandatory</span>
                <div class="form-group">
                  <label for="input_site_name" class="col-sm-3 control-label">Site Name<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('site_name', $result[0]->value, ['class' => 'form-control', 'id' => 'input_site_name', 'placeholder' => 'Site Name']); ?>

                    <span class="text-danger"><?php echo e($errors->first('site_name')); ?></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="input_site_name" class="col-sm-3 control-label">Version</label>
                  <div class="col-sm-6">
                    <?php echo Form::text('version', $result[2]->value, ['class' => 'form-control', 'id' => 'input_version', 'placeholder' => 'Version']); ?>

                    <span class="text-danger"><?php echo e($errors->first('version')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_logo" class="col-sm-3 control-label">Header Logo</label>
                  <em>Size: 140x80</em>
                  <div class="col-sm-6">
                    <?php echo Form::file('logo', ['class' => 'form-control', 'id' => 'input_logo', 'accept' => 'image/*']); ?>

                    <span class="text-danger"><?php echo e($errors->first('logo')); ?></span>
                    <img src="<?php echo e($logo_url); ?>" class="image-cls">
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_logo" class="col-sm-3 control-label">Header white logo</label>
                  <em>Size: 140x80</em>
                  <div class="col-sm-6">
                    <?php echo Form::file('page_logo', ['class' => 'form-control', 'id' => 'input_page_logo', 'accept' => 'image/*']); ?>

                    <span class="text-danger"><?php echo e($errors->first('page_logo')); ?></span>
                    <img src="<?php echo e(url(PAGE_LOGO_URL).'?'.rand()); ?>" class="image-cls">
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_favicon" class="col-sm-3 control-label">Favicon</label>
                  <em>Size: 16x16</em>
                  <div class="col-sm-6">
                    <?php echo Form::file('favicon', ['class' => 'form-control', 'id' => 'input_favicon', 'accept' => 'image/*']); ?>

                    <span class="text-danger"><?php echo e($errors->first('favicon')); ?></span>
                    <img src="<?php echo e($favicon.'?'.rand()); ?>" class="fav_class">
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_default_currency" class="col-sm-3 control-label">Default Currency</label>
                  <div class="col-sm-6">
                    <?php echo Form::select('default_currency', $currency, $default_currency, ['class' => 'form-control', 'id' => 'input_default_currency']); ?>

                    <span class="text-danger"><?php echo e($errors->first('default_currency')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_default_currency" class="col-sm-3 control-label">PayPal Currency</label>
                  <div class="col-sm-6">
                    <?php echo Form::select('paypal_currency', $paypal_currency, $result[1]->value, ['class' => 'form-control', 'id' => 'input_paypal_currency']); ?>

                    <span class="text-danger"><?php echo e($errors->first('paypal_currency')); ?></span>
                  </div>
                </div>

                   <div class="form-group">
                  <label for="input_site_name" class="col-sm-3 control-label">Driver Kilo Meter</label>
                  <div class="col-sm-6">
                    <?php echo Form::text('driver_km', $result[6]->value, ['class' => 'form-control', 'id' => 'input_head_code', 'placeholder' => 'Driver kilo meter']); ?>

                    <span class="text-danger"><?php echo e($errors->first('driver_km')); ?></span>
                  </div>
                </div>


                    <div class="form-group">
                  <label for="input_site_name" class="col-sm-3 control-label">Add code to the < head >(for tracking codes such as google analytics)</label>
                  <div class="col-sm-6">
                    <?php echo Form::textarea('head_code', $result[7]->value, ['class' => 'form-control', 'id' => 'input_head_code', 'placeholder' => 'Head Code']); ?>

                    <span class="text-danger"><?php echo e($errors->first('head_code')); ?></span>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="input_default_language" class="col-sm-3 control-label">Default Language</label>
                  <div class="col-sm-6">
                    <?php echo Form::select('default_language', $language, $default_language[0]->value, ['class' => 'form-control', 'id' => 'input_default_language']); ?>

                    <span class="text-danger"><?php echo e($errors->first('default_language')); ?></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="input_admin_country_code" class="col-sm-3 control-label">
                    Country Code  <em class="text-danger">*</em>
                  </label>
                  <div class="col-sm-6">
                    <select class='form-control' id = 'input_admin_country_code' name='admin_country_code' >
                      <option value="" disabled> Select </option>
                      <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country_code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($country_code->phone_code); ?>" <?php echo e(($country_code->phone_code == old('admin_country_code',$result[9]->value)) ? 'Selected' : ''); ?> ><?php echo e($country_code->long_name); ?></option>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <span class="text-danger"><?php echo e($errors->first('admin_country_code')); ?></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="input_mobile_number" class="col-sm-3 control-label">Manual Booking Contact Number <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('admin_contact', old('admin_contact',$result[8]->value), ['class' => 'form-control', 'id' => 'input_head_code', 'placeholder' => 'Contact Number']); ?>

                    <span class="text-danger"><?php echo e($errors->first('admin_contact')); ?></span>
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
  <style type="text/css">
    .image-cls{
      width: 140px;
    height: 80px;
    }
    .fav_class{
      height: 16px;
      width: 16px;
    }
  </style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin.template', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>