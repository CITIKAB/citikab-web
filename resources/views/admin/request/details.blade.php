@extends('admin.template')

@section('main')


 <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-xs-12">

          <div class="box">
            <div class="box-header">
              <h3 class="box-title">Request Details</h3>              
            </div>
            <!-- /.box-header -->
            <div class="box-body">            
              <div class="col-md-6">
                    <dl class="row">

                        <input type="hidden" id='pickup_latitude' value="{{@$request_details->pickup_latitude}}">
                        <input type="hidden" id='pickup_longitude' value="{{@$request_details->pickup_longitude}}">
                        <input type="hidden" id='drop_latitude' value="{{@$request_details->drop_latitude}}">
                        <input type="hidden" id='drop_longitude' value="{{@$request_details->drop_longitude}}">
                        <div class="payment-details clearfix">
                            <dt class="col-sm-5">Vehicle name</dt>
                            <dd class="col-sm-7">{{@$request_details->driver->car_type}}</dd>
                        </div>
                        <div class="payment-details clearfix">
                            <dt class="col-sm-5">Rider Name :</dt>
                            <dd class="col-sm-7">{{@$request_details->users->first_name.' '.@$request_details->users->last_name}}</dd>
                        </div>
                        <div class="payment-details clearfix">
                            <dt class="col-sm-5">Driver Name :</dt>
                            @if($request_status=="No one accepted")
                                <div class="payment-details clearfix">
                                    <dd class="col-sm-7">Driver not yet assigned!</dd>
                                </div>
                            @else
                                <div class="payment-details clearfix">
                                    <dd class="col-sm-7">{{ @$driver_name}}</dd> 
                                </div>  
                            @endif
                        </div>
                        @if(LOGIN_USER_TYPE != 'company' && isset($company_name))
                        <div class="payment-details clearfix">
                            <dt class="col-sm-5">Company Name :</dt>
                                <div class="payment-details clearfix">
                                    <dd class="col-sm-7">{{ @$company_name}}</dd> 
                                </div>
                        </div>
                        @endif


                        <div class="payment-details clearfix">
                        
                        <dt class="col-sm-5">Pickup Address :</dt>
                        <dd class="col-sm-7">{{@$request_details->pickup_location}}</dd></div>
                        <div class="payment-details clearfix">
                            <dt class="col-sm-5">Drop Address :</dt>
                            <dd class="col-sm-7">{{@$request_details->drop_location}}</dd>
                        </div>
                        
                        @if(@$is_tripped)
                        <div class="payment-details clearfix">
                         <dt class="col-sm-5">Total Distance :</dt>
                        <dd class="col-sm-7">{{ (@$request_details->accepted_trips->total_km =='') ? '-' : @$request_details->accepted_trips->total_km}}</dd></div>
                        @if($request_details->accepted_trips->status=="End trip" || $request_details->accepted_trips->status=="Rating" || $request_details->accepted_trips->status=="Payment" || $request_details->accepted_trips->status=="Completed")
                        <div class="payment-details clearfix">
                        <dt class="col-sm-5">Ride Start Time :</dt>
                        <dd class="col-sm-7">
                          {!! date("l jS \of F Y h:i:s A", strtotime(@$request_details->accepted_trips->begin_trip)) !!}
                        </dd>
                    </div>
                        @endif
                        @if($request_details->accepted_trips->status=="Rating" || $request_details->accepted_trips->status=="Payment" || $request_details->accepted_trips->status=="Completed")
                        <div class="payment-details clearfix">
                        <dt class="col-sm-5">Ride End Time :</dt>
                        <dd class="col-sm-7">
                        {!! date("l jS \of F Y h:i:s A", strtotime(@$request_details->accepted_trips->end_trip)) !!}
                        </dd>
                    </div>
                        @endif
                        @if(@$request_details->accepted_trips->base_fare !='')
                        <div class="payment-details clearfix">
                        <dt class="col-sm-5">Base Fare :</dt>
                        <dd class="col-sm-7">{{@$default_currency->symbol.' '.@$request_details->accepted_trips->base_fare}}</dd></div>
                        @endif
<div class="payment-details clearfix">
                        @if(@$request_details->accepted_trips->distance_fare !='')
                        <dt class="col-sm-5">Distance Fare :</dt>
                        <dd class="col-sm-7">{{@$default_currency->symbol.' '.@$request_details->accepted_trips->distance_fare}}</dd></div>
                        @endif

                        @if(@$request_details->accepted_trips->time_fare !='')
                        <div class="payment-details clearfix">
                            <dt class="col-sm-5">Time Fare :</dt>
                            <dd class="col-sm-7">{{@$default_currency->symbol.' '.@$request_details->accepted_trips->time_fare}}</dd>
                        </div>
                        @endif

                        @if( @$request_details->accepted_trips->peak_fare > 0 )
                        <div class="payment-details clearfix">
                            <dt class="col-sm-5"> Normal Fare </dt>
                            <dd class="col-sm-7"> 
                                {{ @$default_currency->symbol.' '.@$request_details->accepted_trips->subtotal_fare }}
                            </dd>
                        </div>
                        <div class="payment-details clearfix">
                            <dt class="col-sm-5"> Peak time Fare  x{{ $request_details->accepted_trips->peak_fare }} </dt>
                            <dd class="col-sm-7"> 
                                {{ @$default_currency->symbol.' '.@$request_details->accepted_trips->peak_amount }}
                            </dd></div>
                            <div class="payment-details clearfix">
                            <dt class="col-sm-5"> Subtotal </dt>
                            <dd class="col-sm-7"> 
                                {{ @$default_currency->symbol.' '.@$request_details->accepted_trips->peak_subtotal_fare }}
                            </dd></div>
                        @endif

                        @if(@$request_details->accepted_trips->wallet_amount !='0')
                        <div class="payment-details clearfix">
                        <dt class="col-sm-5">Wallet Amount :</dt>
                        <dd class="col-sm-7">{{@$default_currency->symbol.' '.@$request_details->accepted_trips->wallet_amount}}</dd></div>
                        @endif
<div class="payment-details clearfix">
                        @if(@$request_details->accepted_trips->promo_amount !='0')
                        <dt class="col-sm-5">Promo Amount :</dt>
                        <dd class="col-sm-7">{{@$default_currency->symbol.' '.@$request_details->accepted_trips->promo_amount}}</dd></div>
                        @endif
@if(LOGIN_USER_TYPE!='company')
<div class="payment-details clearfix">
                        <dt class="col-sm-5">Admin Commission :</dt>
                        <dd class="col-sm-7">{{@$default_currency->symbol.' '.@$request_details->accepted_trips->access_fee}}</dd></div>
@endif                        
<div class="payment-details clearfix">
                        <dt class="col-sm-5">Admin Commission :</dt>
                        <dd class="col-sm-7">{{@$default_currency->symbol.' '.@$request_details->accepted_trips->driver_or_company_commission}}</dd></div>
<div class="payment-details clearfix">
                            @if(@$request_details->accepted_trips->total_fare !='')
                        @if(LOGIN_USER_TYPE!='company')
                        <dt class="col-sm-5">Total Amount :</dt>
                        <dd class="col-sm-7">{{@$default_currency->symbol.' '.@$request_details->accepted_trips->total_fare}}</dd></div>
                        @endif                        
                        @endif
<div class="payment-details clearfix">
                        <dt class="col-sm-5">Payment Mode : </dt>
                        <dd class="col-sm-7">{{@$request_details->accepted_trips->payment_mode}}</dd></div>
                        
                        @if($request_details->accepted_trips->cash_collectable !=0)
                        <div class="payment-details clearfix">
                        <dt class="col-sm-5">Cash Collected : </dt>
                        <dd class="col-sm-7">{{@$default_currency->symbol}} {{@$request_details->accepted_trips->cash_collectable}}</dd></div>
                        @endif
                        @if(LOGIN_USER_TYPE!='company') 
                        <div class="payment-details clearfix">
                        <dt class="col-sm-5">Driver Payout:</dt>
                        <dd class="col-sm-7">{{@$default_currency->symbol}} {{ number_format($request_details->accepted_trips->payable_driver_payout,2, '.', '') }}</dd>
</div>
                        

                        <div class="payment-details clearfix">
                        <dt class="col-sm-5">Driver Payout Account :</dt>
                        <dd class="col-sm-7">{{@$request_details->driver->payout_id}}</dd>
</div>
                        
                        @endif
                        @endif
<div class="payment-details clearfix">
                        <dt class="col-sm-5">Ride Status : </dt>
                        <dd class="col-sm-7">
                            {{@$request_status}}
                        </dd>
</div>
                    </dl>
                </div>
                <div class="col-md-6">
                    <div id="map"></div>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <style type="text/css">
    #map {
        height: 500px;
        width: 100%;
       }
    dl.row {
        font-size: 15px;
        
    }
    dl dt , dl dd{
        padding: 5px;
    }  
  </style>
@endsection

