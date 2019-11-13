<?php $__env->startSection('main'); ?>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" ng-controller="vehicle_management">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Edit Vehicles
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/dashboard')); ?>"><i class="fa fa-dashboard"></i> Home </a></li>
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/vehicle')); ?>"> Vehicles </a></li>
        <li class="active"> Edit </li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content" ng-init='vehicle_id="<?php echo e($result->id); ?>"'>
      <div class="row">
        <!-- right column -->
        <div class="col-md-8 col-sm-offset-2 ne_ed">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Edit Vehicles Form</h3>
              
            </div>
           
            <!-- /.box-header -->
            <!-- form start -->
            <?php echo Form::open(['url' => LOGIN_USER_TYPE.'/edit_vehicle/'.$result->id, 'class' => 'form-horizontal','files' => true,'id'=>'vehicle_form']); ?>

              <div class="box-body ed_bld">
              <span class="text-danger">(*)Fields are Mandatory</span>
                <?php if(LOGIN_USER_TYPE!='company'): ?>
                  <div class="form-group" ng-init='company_name = "<?php echo e($result->company_id); ?>"'>
                    <label for="input_company" class="col-sm-3 control-label">Company Name<em class="text-danger">*</em></label>

                    <div class="col-sm-6" ng-init='get_driver()'>
                      <?php echo Form::select('company_name', $company, $result->status==null?0:$result->status, ['class' => 'form-control', 'id' => 'input_company_name', 'placeholder' => 'Select','ng-model' => 'company_name','ng-change' => 'get_driver()']); ?>

                      <span class="text-danger"><?php echo e($errors->first('company_name')); ?></span>
                    </div>
                  </div>
                <?php else: ?>
                  <span ng-init='company_name="<?php echo e(Auth::guard("company")->user()->id); ?>";get_driver()'></span>
                <?php endif; ?>
                <div class="form-group">
                  <label for="input_company" class="col-sm-3 control-label">Driver Name<em class="text-danger">*</em></label>

                  <div class="col-sm-6" ng-init='driver_name = "<?php echo e($result->user_id); ?>"'>
                    <span class="loading" style="display: none;padding-left: 50%"><img src="<?php echo e(url('images/loader.gif')); ?>" style="width: 25px;height: 25px; "><br></span>
                    <select class='form-control' ng-cloak name="driver_name" id="input_driver_name">
                      <option value="">Select</option>
                      <option ng-repeat="driver in drivers" value="{{driver.id}}" ng-selected="{{driver_name}} == {{driver.id}}">{{driver.first_name}} {{driver.last_name}} - {{driver.id}}</option>
                    </select>
                    <span class="text-danger" id="driver-error"><?php echo e($errors->first('driver_name')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Status<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive'), $result->status, ['class' => 'form-control', 'id' => 'input_status', 'placeholder' => 'Select']); ?>

                    <span class="text-danger"><?php echo e($errors->first('status')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Vehicle Type <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                      <?php echo Form::select('vehicle_id', $car_type,@$result->vehicle_id, ['class' => 'form-control', 'id' => 'input_status', 'placeholder' => 'Select']); ?>

                    <span class="text-danger"><?php echo e($errors->first('vehicle_id')); ?></span>
                  </div>
                </div>
                 <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Vehicle Name <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    
                     <?php echo Form::text('vehicle_name',@$result->vehicle_name, ['class' => 'form-control', 'id' => 'vehicle_name', 'placeholder' => 'Vehicle Name']); ?>

                    <span class="text-danger"><?php echo e($errors->first('vehicle_name')); ?></span>
                  </div>
                </div>  
                 <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Vehicle Number <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    
                     <?php echo Form::text('vehicle_number',@$result->vehicle_number, ['class' => 'form-control', 'id' => 'vehicle_number', 'placeholder' => 'Vehicle Number']); ?>

                    <span class="text-danger"><?php echo e($errors->first('vehicle_number')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_license_back" class="col-sm-3 control-label">Motor Insurance Certificate   <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::file('insurance',  ['class' => 'form-control', 'id' => 'input_insurance', 'accept' => 'image/*']); ?>

                    <span class="text-danger"><?php echo e($errors->first('insurance')); ?></span><br>
                    <?php if(@$result->insurance): ?>
                      <a href="<?php echo e(@$result->insurance); ?>" target="_blank"><img id="insurance_img" style="width: 200px;height: 100px" src="<?php echo e(@$result->insurance); ?>"></a>

                    <?php else: ?>
                       <img style="width: 100px;height: 100px; padding-top: 5px;" src="<?php echo e(url('images/driver_doc.png')); ?>">
                    <?php endif; ?>
                   
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_license_back" class="col-sm-3 control-label">Certificate of Registration <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::file('rc', ['class' => 'form-control', 'id' => 'rc', 'accept' => 'image/*']); ?>

                    <span class="text-danger"><?php echo e($errors->first('rc')); ?></span><br>
                    <?php if(@$result->rc): ?>
                       <a href="<?php echo e(@$result->rc); ?>" target="_blank"><img id="rc_img" style="width: 200px;height: 100px" src="<?php echo e(@$result->rc); ?>"></a>
                    <?php else: ?>
                      <img style="width: 100px;height: 100px; padding-top: 5px;" src="<?php echo e(url('images/driver_doc.png')); ?>">
                    <?php endif; ?>
                 

                  </div>
                </div>
                <div class="form-group">
                  <label for="input_license_back" class="col-sm-3 control-label">Contact Carriage Permit <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::file('permit', ['class' => 'form-control', 'id' => 'permit', 'accept' => 'image/*']); ?>

                    <span class="text-danger"><?php echo e($errors->first('permit')); ?></span><br>
                    <?php if(@$result->permit): ?>
                      <a href="<?php echo e(@$result->permit); ?>" target="_blank"><img id="permit_img" style="width: 200px;height: 100px" src="<?php echo e(@$result->permit); ?>"></a>
                    <?php else: ?>
                       <img style="width: 100px;height: 100px; padding-top: 5px; " src="<?php echo e(url('images/driver_doc.png')); ?>">
                    <?php endif; ?>
                       

                  </div>
                </div>

              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                 <button type="submit" class="btn btn-info pull-right" name="submit" value="submit">Submit</button>
                <a href="<?php echo e(url(LOGIN_USER_TYPE.'/vehicle')); ?>"><span class="btn btn-default pull-left">Cancel</span></a>
              </div>
              <!-- /.box-footer -->
            </form>
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
  <?php $__env->startPush('scripts'); ?>
<script>
  $('#input_dob').datepicker({ 'format': 'dd-mm-yyyy'});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('admin.template', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>