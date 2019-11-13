<?php $__env->startSection('main'); ?>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Manage Trips Details
      </h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/dashboard')); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo e(url(LOGIN_USER_TYPE.'/trips')); ?>">Trips</a></li>
        <li class="active">Details</li>
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
              <h3 class="box-title">Trips Details</h3>
            </div>
            <!-- /.box-header -->
            <!-- form start -->
            <?php echo Form::open(['url' => 'admin/trips/payout/'.$result->id, 'class' => 'form-horizontal', 'style' => 'word-wrap: break-word']); ?>

              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Vehicle name
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->car_type->car_name); ?>

                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Driver name
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->driver_name); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Rider name
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->users->first_name); ?>

                   </div>
                </div>

                <?php if(LOGIN_USER_TYPE != 'company'): ?>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">
                      Company name
                    </label>
                    <div class="col-sm-6 col-sm-offset-1 form-control-static">
                      <?php echo e($result->driver->company->name); ?>

                     </div>
                  </div>
                <?php endif; ?>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Begin Trip
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->formatted_begin_trip); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    End Trip
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->formatted_end_trip); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Pickup Location
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->pickup_location); ?>

                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Drop Location
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->drop_location); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Currency
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency_code); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Base fare
                  </label>

                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e($result->base_fare); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Time fare
                  </label>

                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e($result->time_fare); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Distance fare
                  </label>

                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e($result->distance_fare); ?>

                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Distance
                  </label>

                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->total_km); ?> KM
                   </div>
                </div>
                <?php if( $result->schedule_fare > 0 ): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Schedule fare
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?> <?php echo e($result->schedule_fare); ?>

                   </div>
                </div>
                <?php endif; ?>
                <?php if( $result->peak_fare > 0 ): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Normal fare
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?> <?php echo e($result->subtotal_fare); ?>

                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Peak time pricing  x<?php echo e($result->peak_fare); ?>

                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?> <?php echo e($result->peak_amount); ?>

                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Subtotal
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?> <?php echo e($result->peak_subtotal_fare); ?>

                   </div>
                </div>
                <?php endif; ?>
                <?php if(LOGIN_USER_TYPE!='company'): ?>
                  <div class="form-group">
                    <label class="col-sm-3 control-label">
                      Service fee
                    </label>
                    <div class="col-sm-6 col-sm-offset-1 form-control-static">
                      <?php echo e($result->currency->symbol); ?><?php echo e($result->access_fee); ?>

                     </div>
                  </div>
                <?php endif; ?>
                <?php if( $result->peak_fare > 0 ): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Driver Peak Amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?> <?php echo e($result->driver_peak_amount); ?>

                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Admin Peak Amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?> <?php echo e($result->peak_amount - $result->driver_peak_amount); ?>

                   </div>
                </div>
                <?php endif; ?>
                <?php if(LOGIN_USER_TYPE!='company'): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Total fare
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e(number_format($result->base_fare + $result->time_fare +  $result->distance_fare +  $result->peak_amount + $result->access_fee + $result->schedule_fare,2,'.','')); ?>

                   </div>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Admin Commission
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e(number_format($result->driver_or_company_commission,2,'.','')); ?>

                   </div>
                </div>
                
                <?php if(@$result->owe_amount !='0'): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Owe amount
                    <?php if(LOGIN_USER_TYPE == 'company'): ?>
                    <br>
                    <span> ( Service fee + Admin Commission) </span>
                    <?php endif; ?>
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e($result->owe_amount); ?>

                   </div>
                </div>
                <?php endif; ?>
                
                <?php if(@$result->applied_owe_amount !='0'): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Applied Owe amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e($result->applied_owe_amount); ?>

                   </div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Remaining Owe amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e($result->remaining_owe_amount); ?>

                   </div>
                </div>

                <?php if(@$result->wallet_amount !='0'): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Wallet amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e($result->wallet_amount); ?>

                   </div>
                </div>
                <?php endif; ?>

                <?php if(@$result->promo_amount !='0'): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Promo amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?><?php echo e($result->promo_amount); ?>

                   </div>
                </div>
                <?php endif; ?>   
                <?php if($result->cash_collectable > 0): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cash Collected by Driver
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->currency->symbol); ?> <?php echo e(@$result->cash_collectable); ?>

                   </div>
                </div>
                <?php endif; ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Payment Mode
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->payment_mode); ?>

                   </div>
                </div>               
                <?php if($result->driver->default_payout_credentials != ''): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    <?php if($result->driver->default_payout_credentials->type == 'paypal'): ?>
                      Driver payout Email id
                    <?php else: ?>
                      Driver payout Account
                    <?php endif; ?>
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->driver->payout_id); ?>

                  </div>
                </div> 
                <?php endif; ?>

                 

           
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Status
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e($result->status); ?>

                   </div>
                </div>
               
                <?php if($result->status == "Cancelled"): ?>
                  <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cancelled Reason
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e(@$result->cancel->cancel_reason); ?>

                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cancelled Message
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e(@$result->cancel->cancel_comments); ?>

                   </div>
                </div>

                 <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cancelled By
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e(@$result->cancel->cancelled_by); ?>

                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cancelled Date
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e(@$result->cancel->created_at); ?>

                   </div>
                </div>
                <?php endif; ?>
                <?php if($result->payment_mode == "Cash" && $result->payment_mode == "Cash & Wallet"): ?>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Transaction ID
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    <?php echo e(@$result->paykey); ?>

                   </div>
                </div>
                <?php endif; ?>

                <?php if(LOGIN_USER_TYPE != 'company'): ?>
                  <?php if($result->driver->defult_bank_detail == ''): ?>
                    <div class="form-group">
                      <label class="col-sm-3 control-label">
                      </label>
                      <div class="col-sm-6 col-sm-offset-1 form-control-static">
                        Yet, Driver doesn't enter his Payout details.
                      </div>
                    </div>
                  <?php elseif($result->status == "Completed" && $result->payout_status == "Pending" && $result->payment_mode != 'Cash' && $result->driver_payout>0): ?>
                    <div class="form-group">
                      <label class="col-sm-3 control-label">
                        Driver Payout Amount
                      </label>
                      <div class="col-sm-6 col-sm-offset-1 form-control-static">
                        <?php echo e(number_format($result->driver_payout ,2)); ?>

                       </div>
                    </div>
                  <?php elseif($result->status == "Completed" && $result->payout_status == "Paid"): ?>
                   <div class="form-group">
                      <label class="col-sm-3 control-label">
                        Payout Status
                      </label>
                      <div class="col-sm-6 col-sm-offset-1 form-control-static">
                        Payout successfully sent..
                      </div>
                  </div>
                <?php endif; ?>
              <?php endif; ?>

              
              <?php if(LOGIN_USER_TYPE != 'company'): ?>
              <?php if($result->driver->company->id != 1): ?>
                <?php if($result->driver->company->default_payout_credentials): ?>
                <div class="form-group">
                  

                  <div class="col-sm-9 col-sm-offset-1 form-control-static bank-list">
                    <label class="col-sm-4 control-label">
                  Company Bank Details
   
                  </label>
                 </div>
                 

                <div class="col-sm-9 col-sm-offset-1 form-control-static pay-list">
                 <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Account Holder Name
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                    <?php echo e($result->driver->company->default_payout_credentials->holder_name); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Account Number  
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                  <?php echo e($result->driver->company->default_payout_credentials->account_number); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Bank Name  
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                   <?php echo e($result->driver->company->default_payout_credentials->bank_name); ?>

                   </div>
                </div>

                 <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Bank Location   
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                     <?php echo e($result->driver->company->default_payout_credentials->bank_location); ?>

                   </div>
                </div>
              </div>
           </div>
           <?php else: ?>
           <div class="form-group">
              <label class="col-sm-3 control-label">
              </label>
              <div class="col-sm-6 col-sm-offset-1 form-control-static">
                Yet, Company doesn't enter his Payout details.
              </div>
            </div>
           <?php endif; ?>
              <?php endif; ?>
              
                <?php if($result->driver->defult_bank_detail): ?>
                <div class="form-group">
                  

                  <div class="col-sm-9 col-sm-offset-1 form-control-static bank-list">
                    <label class="col-sm-4 control-label">
                  Driver Bank Details
   
                  </label>
                 </div>
                 

                <div class="col-sm-9 col-sm-offset-1 form-control-static pay-list">
                 <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Account Holder Name
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                    <?php echo e($result->driver->defult_bank_detail->holder_name); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Account Number  
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                  <?php echo e($result->driver->defult_bank_detail->account_number); ?>

                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Bank Name  
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                   <?php echo e($result->driver->defult_bank_detail->bank_name); ?>

                   </div>
                </div>

                 <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Bank Location   
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                     <?php echo e($result->driver->defult_bank_detail->bank_location); ?>

                   </div>
                </div>
              </div>
           </div>
           <?php endif; ?>
           <?php endif; ?>

                
              <!-- /.box-body -->
            </form> 
             
              <div class="box-footer text-center">
                <a class="btn btn-default" href="<?php echo e($back_url); ?>">Back</a>
              </div>

              <!-- /.box-footer -->
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