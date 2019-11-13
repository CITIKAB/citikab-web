<?php $__env->startSection('main'); ?>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" ng-controller="driver_management">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Edit Driver
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/dashboard')); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/driver')); ?>">Drivers</a></li>
        <li class="active">Edit</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <!-- right column -->
        <div class="col-md-8 col-sm-offset-2 ne_ed">
          <!-- Horizontal Form -->
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Edit Driver Form</h3>
              
            </div>
           
            <!-- /.box-header -->
            <!-- form start -->
            <?php echo Form::open(['url' => LOGIN_USER_TYPE.'/edit_driver/'.$result->id, 'class' => 'form-horizontal','files' => true]); ?>

              <div class="box-body ed_bld">
              <span class="text-danger">(*)Fields are Mandatory</span>
                <div class="form-group">
                  <label for="input_first_name" class="col-sm-3 control-label">First Name<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('first_name', $result->first_name, ['class' => 'form-control', 'id' => 'input_first_name', 'placeholder' => 'First Name']); ?>

                    <span class="text-danger"><?php echo e($errors->first('first_name')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_last_name" class="col-sm-3 control-label">Last Name<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('last_name', $result->last_name, ['class' => 'form-control', 'id' => 'input_last_name', 'placeholder' => 'Last Name']); ?>

                    <span class="text-danger"><?php echo e($errors->first('last_name')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_email" class="col-sm-3 control-label">Email<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::text('email', $result->email, ['class' => 'form-control', 'id' => 'input_email', 'placeholder' => 'Email']); ?>

                    <span class="text-danger"><?php echo e($errors->first('email')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_password" class="col-sm-3 control-label">Password</label>
                  <div class="col-sm-6">
                    <?php echo Form::text('password', '', ['class' => 'form-control', 'id' => 'input_password', 'placeholder' => 'Password']); ?>

                    <span class="text-danger"><?php echo e($errors->first('password')); ?></span>
                  </div>
                </div>
             
                
                    <?php echo Form::hidden('user_type','Driver', ['class' => 'form-control', 'id' => 'user_type', 'placeholder' => 'Select']); ?>

                 <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Country Code<em class="text-danger">*</em></label>

                  <div class="col-sm-6">
                  <select class ='form-control' id = 'input_status' name='country_code'>
                    <option value="" disabled=""> Select </option>
                    <?php $__currentLoopData = $country_code_option; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country_code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                      <option value="<?php echo e(@$country_code->phone_code); ?>" <?php echo e(($country_code->phone_code == $result->country_code) ? 'Selected' : ''); ?>><?php echo e($country_code->long_name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                  </select>
                    <span class="text-danger"><?php echo e($errors->first('country_code')); ?></span>
                  </div>
                </div>  
               
                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Mobile Number </label>

                  <div class="col-sm-6">                   
                     <?php echo Form::text('mobile_number',$result->mobile_number, ['class' => 'form-control', 'id' => 'mobile_number', 'placeholder' => 'Mobile Number']); ?>

                    <span class="text-danger"><?php echo e($errors->first('mobile_number')); ?></span>
                  </div>
                </div>
                <?php if(LOGIN_USER_TYPE!='company'): ?>
                  <div class="form-group">
                    <label for="input_company" class="col-sm-3 control-label">Company Name<em class="text-danger">*</em></label>

                    <div class="col-sm-6">
                      <?php echo Form::select('company_name_view', $company, $result->company_id, ['class' => 'form-control', 'id' => 'input_company_name', 'placeholder' => 'Select','disabled']); ?>


                      <?php echo Form::hidden('company_name', $result->company_id); ?>

                      <span class="text-danger"><?php echo e($errors->first('company_name')); ?></span>
                    </div>
                  </div>
                <?php endif; ?>
                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Status<em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::select('status', array('Active' => 'Active', 'Inactive' => 'Inactive', 'Pending' => 'Pending', 'Car_details' => 'Car_details', 'Document_details' => 'Document_details'), $result->status, ['class' => 'form-control', 'id' => 'input_status', 'placeholder' => 'Select']); ?>

                    <span class="text-danger"><?php echo e($errors->first('status')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Address Line 1 </label>

                  <div class="col-sm-6">
                     <?php echo Form::text('address_line1',@$address->address_line1, ['class' => 'form-control', 'id' => 'address_line1', 'placeholder' => 'Address Line 1']); ?>

                    <span class="text-danger"><?php echo e($errors->first('address_line1')); ?></span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Address Line 2 </label>

                  <div class="col-sm-6">
                     <?php echo Form::text('address_line2',@$address->address_line2, ['class' => 'form-control', 'id' => 'address_line2', 'placeholder' => 'Address Line 2']); ?>

                    <span class="text-danger"><?php echo e($errors->first('address_line2')); ?></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">City </label>
                  <div class="col-sm-6">
                    
                       <?php echo Form::text('city',@$address->city, ['class' => 'form-control', 'id' => 'city', 'placeholder' => 'City']); ?>

                    <span class="text-danger"><?php echo e($errors->first('city')); ?></span>
                  </div>
                </div>

                
                 <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">State</label>

                  <div class="col-sm-6">
                    
                     <?php echo Form::text('state',$address->state, ['class' => 'form-control', 'id' => 'state', 'placeholder' => 'State']); ?>

                    <span class="text-danger"><?php echo e($errors->first('state')); ?></span>
                  </div>
                </div> 
                <div class="form-group">
                  <label for="input_status" class="col-sm-3 control-label">Postal Code </label>
                  <div class="col-sm-6">

                       <?php echo Form::text('postal_code',@$address->postal_code, ['class' => 'form-control', 'id' => 'postal_code', 'placeholder' => 'Postal Code']); ?>

                    <span class="text-danger"><?php echo e($errors->first('postal_code')); ?></span>
                  </div>
                </div>

                <div class="form-group">
                  <label for="input_license_back" class="col-sm-3 control-label">Driver's License - ( Back/Reverse)  <em class="text-danger">*</em></label>
                  <div class="col-sm-6">
                    <?php echo Form::file('license_back', ['class' => 'form-control', 'id' => 'input_license_back', 'accept' => "image/*"]); ?>

                    <span class="text-danger"><?php echo e($errors->first('license_back')); ?></span>
                    <?php if(@$driver_documents->license_back): ?>
                      <a href="<?php echo e(@$driver_documents->license_back); ?>" target="_blank"> <img style="width: 200px;height: 100px" src="<?php echo e(@$driver_documents->license_back); ?>"></a>
                    <?php else: ?>
                      <img style="width: 100px;height: 100px; padding-top: 5px;" src="<?php echo e(url('images/driver_doc.png')); ?>">
                    <?php endif; ?>
                  </div>
                </div>
                <div class="form-group">
                  <label for="input_license_front" class="col-sm-3 control-label">Driver's License - (Front) <em class="text-danger">*</em></label>

                  <div class="col-sm-6">
                    <?php echo Form::file('license_front', ['class' => 'form-control', 'id' => 'input_license_front', 'accept' => "image/*"]); ?>

                    <span class="text-danger"><?php echo e($errors->first('license_front')); ?></span>
                    <?php if(@$driver_documents->license_front): ?>
                      <a href="<?php echo e(@$driver_documents->license_front); ?>" target="_blank"><img style="width: 200px;height: 100px" src="<?php echo e(@$driver_documents->license_front); ?>"> </a>
                    <?php else: ?>
                       <img style="width: 100px;height: 100px; padding-top: 5px;" src="<?php echo e(url('images/driver_doc.png')); ?>">
                    <?php endif; ?>
                  </div>
                </div>
                <?php if(LOGIN_USER_TYPE!='company' || Auth::guard('company')->user()->id != 1): ?>
                  <span class="bank_detail">
                    <div class="form-group">
                      <label for="input_status" class="col-sm-3 control-label">Account Holder Name <em class="text-danger">*</em></label>
                      <div class="col-sm-6">
                       
                           <?php echo Form::text('account_holder_name',@$result->bank_detail->holder_name, ['class' => 'form-control', 'id' => 'account_holder_name', 'placeholder' => 'Account Holder Name']); ?>

                        <span class="text-danger"><?php echo e($errors->first('account_holder_name')); ?></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="input_status" class="col-sm-3 control-label">Account Number <em class="text-danger">*</em></label>
                      <div class="col-sm-6">
                       
                           <?php echo Form::text('account_number',@$result->bank_detail->account_number, ['class' => 'form-control', 'id' => 'account_number', 'placeholder' => 'Account Number']); ?>

                        <span class="text-danger"><?php echo e($errors->first('account_number')); ?></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="input_status" class="col-sm-3 control-label">Name of Bank <em class="text-danger">*</em></label>
                      <div class="col-sm-6">
                       
                           <?php echo Form::text('bank_name',@$result->bank_detail->bank_name, ['class' => 'form-control', 'id' => 'bank_name', 'placeholder' => 'Name of Bank']); ?>

                        <span class="text-danger"><?php echo e($errors->first('bank_name')); ?></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="input_status" class="col-sm-3 control-label">Bank Location <em class="text-danger">*</em></label>
                      <div class="col-sm-6">
                       
                           <?php echo Form::text('bank_location',@$result->bank_detail->bank_location, ['class' => 'form-control', 'id' => 'bank_location', 'placeholder' => 'Bank Location']); ?>

                        <span class="text-danger"><?php echo e($errors->first('bank_location')); ?></span>
                      </div>
                    </div><!-- 
                    <div class="form-group">
                      <label for="input_status" class="col-sm-3 control-label">BIC/SWIFT Code <em class="text-danger">*</em></label>
                      <div class="col-sm-6">
                       
                           <?php echo Form::text('bank_code',@$result->bank_detail->code, ['class' => 'form-control', 'id' => 'bank_code', 'placeholder' => 'BIC/SWIFT Code']); ?>

                        <span class="text-danger"><?php echo e($errors->first('bank_code')); ?></span>
                      </div>
                    </div> -->
                  </span>
                <?php endif; ?>
              </div>
              <!-- /.box-body -->
              <div class="box-footer">
                 <button type="submit" class="btn btn-info pull-right" name="submit" value="submit">Submit</button>
                <button type="submit" class="btn btn-default pull-left" name="cancel" value="cancel">Cancel</button>
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