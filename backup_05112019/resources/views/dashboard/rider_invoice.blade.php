<title>Invoice</title>
@extends('template_dashboard') 

@section('main')
<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content" style="padding-top: 10px;">
<div id="printableArea">
<div class="page-lead separated--bottom  text--center text--uppercase pull-left" style="
    margin-bottom: 0px !important;
    padding-bottom: 5px !important;
"><h1 class="flush-h1 flush">{{trans('messages.dashboard.trip_invoice')}}</h1>
<small style="    text-transform: none;
    text-align: left;
    float: left;
    padding: 20px 20px 0px;">{{trans('messages.dashboard.dwnld_invoice')}}{{ $site_name }}{{trans('messages.dashboard.feedback')}}</small>
</div>

    <div id="no-more-tables" style="overflow: visible;" class="tr_ico">
        <table class="col-sm-12 table-bordered table-striped table-condensed cf">
            <thead class="cf">
                <tr>                      
                    <th>{{trans('messages.dashboard.invoice_no')}}</th>
                    <th >{{trans('messages.dashboard.trip_date')}}</th>
                    <th>{{trans('messages.dashboard.invoice')}}</th>
                    <th class="not-need">{{trans('messages.dashboard.download')}}</th>                    
                    <th class="not-need">{{trans('messages.dashboard.print')}}</th>                    
                </tr>
            </thead>
            <tbody>
                <tr class="trip-expand__origin collapsed" >
                    <td data-title="Invoice Number">{{ $trip->id }}</td>
                    <td data-title="Trip date">{{ date('F d, Y',strtotime($trip->created_at))}}</td>
                    <td data-title="Invoice">{{ $trip->currency->original_symbol }} {{ $trip->total_fare }}</td>
                    <td data-title="Download" class="not-need"> <a href="{{ url('download_rider_invoice/'.$trip->id)}}"  class="color--primary" style="font-weight:bold;">{{trans('messages.dashboard.pdf')}}</a></td>
                    <td data-title="Print" class="not-need"> <a href="#" onclick="printDiv('printableArea')" class="color--primary" style="font-weight:bold;">{{trans('messages.dashboard.print')}}</a></td>
                    
                </tr>
            </tbody>
        </table>
    </div>
    <div class="page-lead separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" >
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="dr_invo">               
                <img src="{{ $trip->driver_thumb_image }}" class='img--circle img--bordered img--shadow driver-avatar'>              
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <p>{{trans('messages.dashboard.invoice_issued')}} {{$site_name}} {{trans('messages.dashboard.behalf')}}</p>
            <p>{{ $trip->driver_name }}</p>
            <div class="text--left">
            <div class="trip-address grid grid--full soft-double--bottom">
                <div class="trip-address__path"></div>
                <div class="grid__item one-tenth" style="margin:6px 0px;">
                    <div class="icon icon_route-dot color--positive"></div>
                </div>
                <div class="grid__item nine-tenths">
                    <p class="flush">{{ $trip->pickup_time }}</p>
                    <h6 class="color--neutral flush">{{ $trip->pickup_location }}</h6>
                </div>
            </div>
            <div class="trip-address grid grid--full">
                <div class="grid__item one-tenth" style="margin:6px 0px;">
                    <div class="icon icon_route-dot color--negative"></div>
                </div>
                <div class="grid__item nine-tenths">
                    <p class="flush">{{ $trip->drop_time }}</p>
                    <h6 class="color--neutral flush">{{ $trip->drop_location }}</h6>
                </div>
            </div>
            </div>
            
        </div>
        
    </div>
    <div id="no-more-tables" class="table-no-border" style="overflow: visible;"  class="tr_ico" ng-cloak>
        <table class="col-sm-12 table-bordered table-striped table-condensed cf">
            <thead class="cf">
                <tr>
                    <th>{{trans('messages.dashboard.date')}}</th>
                    <th>{{trans('messages.dashboard.desc')}}</th>
                    <th>{{trans('messages.dashboard.net_amt')}}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="trip-expand__origin collapsed">
                    <td data-title="Tax Point Date">{{ date('F d, Y')}}</td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.payment_mode')}}</td>
                    <td data-title="Description">{{ $trip->payment_mode }}</td>
                </tr>
                <tr class="trip-expand__origin collapsed" >
                    <td></td>
                    <td data-title="Description">{{trans('messages.dashboard.base_fare')}}</td>
                    <td data-title="Net Amount">{{ $trip->currency->original_symbol }} {{ $trip->base_fare }}  </td>
                </tr>
                <tr class="trip-expand__origin collapsed" ng-show="{{ $trip->distance_fare > 0 }}">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.distance_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->distance_fare }}  </td>
                </tr>
                <tr class="trip-expand__origin collapsed" ng-show="{{ $trip->time_fare > 0 }}">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.time_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->time_fare }}  </td>
                </tr>
                <tr class="trip-expand__origin collapsed" ng-show="{{ $trip->schedule_fare > 0 }}">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.schedule_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->schedule_fare }}  </td>
                </tr>
                <tr class="trip-expand__origin collapsed">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.normal_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->subtotal_fare }} </td>
                </tr>
                <tr class="trip-expand__origin collapsed" ng-show="{{ $trip->peak_fare > 0 }}">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.peak_time_fare')}}  x{{ $trip->peak_fare }}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->peak_amount }} </td>
                </tr> 
                <tr class="trip-expand__origin collapsed">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.peak_subtotal_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->peak_subtotal_fare }} </td>
                </tr>  
                <tr class="trip-expand__origin collapsed" ng-show="{{ $trip->access_fee > 0 }}">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.access_fee')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->access_fee }}  </td>
                </tr>      
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.total_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->total_fare}} </td>
                </tr> 
                @if($trip->promo_amount!=0)
                <tr class="trip-expand__origin collapsed">
                <td></td>   
                <td data-title="Tax Point Date">{{trans('messages.dashboard.promo_amt')}}</td>
                <td data-title="Description"> - {{ $trip->currency->original_symbol }} {{ $trip->promo_amount }}</td>
                </tr>
                @endif
                @if($trip->wallet_amount!=0)
                <tr class="trip-expand__origin collapsed">
                <td></td>   
                <td data-title="Tax Point Date">{{trans('messages.dashboard.wallet_amt')}}</td>
                <td data-title="Description"> - {{ $trip->currency->original_symbol }} {{ $trip->wallet_amount }}</td>
                </tr>
                @endif
                @if($trip->payment_mode=="Cash" || $trip->payment_mode=="Cash & Wallet" || $trip->wallet_amount>0 || $trip->promo_amount > 0)
                <tr class="trip-expand__origin collapsed">
                <td></td>   
                <td data-title="Tax Point Date">{{trans('messages.dashboard.paid_amt')}}</td>
                <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->rider_paid_amount }}</td>
                </tr>
                @endif
                
            </tbody>
        </table>
    </div>
    </div>
    <ul class="col-lg-12 col-md-12 col-sm-12 col-xs-12 table-ul">
       
    </ul>
</div>
</div>
</div>
</div>
</div>
</main>
@stop

<script>
function printDiv(divName) {
    $('.not-need').addClass('hide');
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;     
     document.body.innerHTML = printContents;

     window.print();
     

     document.body.innerHTML = originalContents;
     $('.not-need').removeClass('hide');
}
</script>