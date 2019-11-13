@extends('admin.template')

@section('main')
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Manage Trips Details
      </h1>
      <ol class="breadcrumb">
        <li><a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="{{ url(LOGIN_USER_TYPE.'/trips') }}">Trips</a></li>
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
            {!! Form::open(['url' => 'admin/trips/payout/'.$result->id, 'class' => 'form-horizontal', 'style' => 'word-wrap: break-word']) !!}
              <div class="box-body">
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Vehicle name
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->car_type->car_name }}
                  </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Driver name
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->driver_name}}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Rider name
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->users->first_name }}
                   </div>
                </div>

                @if(LOGIN_USER_TYPE != 'company')
                  <div class="form-group">
                    <label class="col-sm-3 control-label">
                      Company name
                    </label>
                    <div class="col-sm-6 col-sm-offset-1 form-control-static">
                      {{ $result->driver->company->name }}
                     </div>
                  </div>
                @endif

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Begin Trip
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->formatted_begin_trip }}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    End Trip
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->formatted_end_trip }}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Pickup Location
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->pickup_location }}
                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Drop Location
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->drop_location }}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Currency
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency_code }}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Base fare
                  </label>

                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ $result->base_fare }}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Time fare
                  </label>

                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ $result->time_fare }}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Distance fare
                  </label>

                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ $result->distance_fare }}
                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Distance
                  </label>

                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->total_km }} KM
                   </div>
                </div>
                @if( $result->schedule_fare > 0 )
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Schedule fare
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }} {{ $result->schedule_fare }}
                   </div>
                </div>
                @endif
                @if( $result->peak_fare > 0 )
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Normal fare
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }} {{ $result->subtotal_fare }}
                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Peak time pricing  x{{ $result->peak_fare }}
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }} {{ $result->peak_amount }}
                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Subtotal
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }} {{ $result->peak_subtotal_fare }}
                   </div>
                </div>
                @endif
                @if(LOGIN_USER_TYPE!='company')
                  <div class="form-group">
                    <label class="col-sm-3 control-label">
                      Service fee
                    </label>
                    <div class="col-sm-6 col-sm-offset-1 form-control-static">
                      {{ $result->currency->symbol }}{{ $result->access_fee }}
                     </div>
                  </div>
                @endif
                @if( $result->peak_fare > 0 )
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Driver Peak Amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }} {{ $result->driver_peak_amount }}
                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Admin Peak Amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }} {{ $result->peak_amount - $result->driver_peak_amount }}
                   </div>
                </div>
                @endif
                @if(LOGIN_USER_TYPE!='company')
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Total fare
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ number_format($result->base_fare + $result->time_fare +  $result->distance_fare +  $result->peak_amount + $result->access_fee + $result->schedule_fare,2,'.','') }}
                   </div>
                </div>
                @endif
                
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Admin Commission
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ number_format($result->driver_or_company_commission,2,'.','') }}
                   </div>
                </div>
                
                @if(@$result->owe_amount !='0')
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Owe amount
                    @if(LOGIN_USER_TYPE == 'company')
                    <br>
                    <span> ( Service fee + Admin Commission) </span>
                    @endif
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ $result->owe_amount }}
                   </div>
                </div>
                @endif
                
                @if(@$result->applied_owe_amount !='0')
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Applied Owe amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ $result->applied_owe_amount  }}
                   </div>
                </div>
                @endif

                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Remaining Owe amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ $result->remaining_owe_amount  }}
                   </div>
                </div>

                @if(@$result->wallet_amount !='0')
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Wallet amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ $result->wallet_amount }}
                   </div>
                </div>
                @endif

                @if(@$result->promo_amount !='0')
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Promo amount
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }}{{ $result->promo_amount }}
                   </div>
                </div>
                @endif   
                @if($result->cash_collectable > 0)
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cash Collected by Driver
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->currency->symbol }} {{ @$result->cash_collectable }}
                   </div>
                </div>
                @endif
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Payment Mode
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->payment_mode }}
                   </div>
                </div>               
                @if($result->driver->default_payout_credentials != '')
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    @if($result->driver->default_payout_credentials->type == 'paypal')
                      Driver payout Email id
                    @else
                      Driver payout Account
                    @endif
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->driver->payout_id }}
                  </div>
                </div> 
                @endif

                 

           
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Status
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ $result->status }}
                   </div>
                </div>
               
                @if($result->status == "Cancelled")
                  <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cancelled Reason
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ @$result->cancel->cancel_reason }}
                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cancelled Message
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ @$result->cancel->cancel_comments }}
                   </div>
                </div>

                 <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cancelled By
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ @$result->cancel->cancelled_by }}
                   </div>
                </div>
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Cancelled Date
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ @$result->cancel->created_at }}
                   </div>
                </div>
                @endif
                @if($result->payment_mode == "Cash" && $result->payment_mode == "Cash & Wallet")
                <div class="form-group">
                  <label class="col-sm-3 control-label">
                    Transaction ID
                  </label>
                  <div class="col-sm-6 col-sm-offset-1 form-control-static">
                    {{ @$result->paykey }}
                   </div>
                </div>
                @endif

                @if(LOGIN_USER_TYPE != 'company')
                  @if($result->driver->defult_bank_detail == '')
                    <div class="form-group">
                      <label class="col-sm-3 control-label">
                      </label>
                      <div class="col-sm-6 col-sm-offset-1 form-control-static">
                        Yet, Driver doesn't enter his Payout details.
                      </div>
                    </div>
                  @elseif($result->status == "Completed" && $result->payout_status == "Pending" && $result->payment_mode != 'Cash' && $result->driver_payout>0)
                    <div class="form-group">
                      <label class="col-sm-3 control-label">
                        Driver Payout Amount
                      </label>
                      <div class="col-sm-6 col-sm-offset-1 form-control-static">
                        {{ number_format($result->driver_payout ,2) }}
                       </div>
                    </div>
                  @elseif($result->status == "Completed" && $result->payout_status == "Paid")
                   <div class="form-group">
                      <label class="col-sm-3 control-label">
                        Payout Status
                      </label>
                      <div class="col-sm-6 col-sm-offset-1 form-control-static">
                        Payout successfully sent..
                      </div>
                  </div>
                @endif
              @endif

              
              @if(LOGIN_USER_TYPE != 'company')
              @if($result->driver->company->id != 1)
                @if($result->driver->company->default_payout_credentials)
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
                    {{$result->driver->company->default_payout_credentials->holder_name}}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Account Number  
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                  {{$result->driver->company->default_payout_credentials->account_number}}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Bank Name  
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                   {{$result->driver->company->default_payout_credentials->bank_name}}
                   </div>
                </div>

                 <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Bank Location   
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                     {{$result->driver->company->default_payout_credentials->bank_location}}
                   </div>
                </div>
              </div>
           </div>
           @else
           <div class="form-group">
              <label class="col-sm-3 control-label">
              </label>
              <div class="col-sm-6 col-sm-offset-1 form-control-static">
                Yet, Company doesn't enter his Payout details.
              </div>
            </div>
           @endif
              @endif
              
                @if($result->driver->defult_bank_detail)
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
                    {{$result->driver->defult_bank_detail->holder_name}}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Account Number  
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                  {{$result->driver->defult_bank_detail->account_number}}
                   </div>
                </div>

                <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Bank Name  
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                   {{$result->driver->defult_bank_detail->bank_name}}
                   </div>
                </div>

                 <div class="form-group">
                  <label class="col-sm-4 control-label">
                    Bank Location   
                  </label>
                  <div class="col-sm-5 col-sm-offset-1 form-control-static">
                     {{$result->driver->defult_bank_detail->bank_location}}
                   </div>
                </div>
              </div>
           </div>
           @endif
           @endif

                
              <!-- /.box-body -->
            </form> 
             
              <div class="box-footer text-center">
                <a class="btn btn-default" href="{{$back_url}}">Back</a>
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
  @endsection
  @push('scripts')
<script>
  $('#input_dob').datepicker({ 'format': 'dd-mm-yyyy'});
</script>
@endpush
