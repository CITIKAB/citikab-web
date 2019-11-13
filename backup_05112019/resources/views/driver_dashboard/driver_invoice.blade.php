<title>Invoice</title>
@extends('template_driver_dashboard') 

@section('main')
<!-- {{App::getlocale()}} -->
<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content" style="padding:0px;" ng-controller="trip">
<div id="printableArea">

<div class="page-lead separated--bottom  text--center text--uppercase pull-left"><h1 class="flush-h1 flush">{{trans('messages.dashboard.trip_invoice')}}</h1>
<small style="    text-transform: none;
    text-align: left;
    float: left;
    padding: 20px 20px 0px;">{{trans('messages.driver_dashboard.download_invoice_driver')}} {{$site_name}} {{trans('messages.driver_dashboard.client_feedback')}} </small>
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
            
            @if($all_invoice == 'true')
            <tbody class="driver_trips_details" ng-cloak>
                <tr class="trip-expand__origin collapsed" ng-init="driver_trips={{ $trips }};currentPage=driver_trips.current_page;totalPages=driver_trips.last_page" ng-repeat="trip in driver_trips.data" ng-cloak>
                    <td data-title="Invoice Number">@{{ trip.id }}</td>
                    <td data-title="Trip date">@{{ trip.begin_date|date:'MM/dd/yyyy'}}</td>
                    <td data-title="Invoice"><span ng-bind-html="trip.currency.original_symbol"></span>&nbsp;@{{ trip.total_invoice }}</td>
                    <td data-title="Download" class="not-need"> <a href="{{ url('download_invoice/')}}/@{{ trip.id}}"  target="_self" class="color--primary" style="font-weight:bold;">{{trans('messages.dashboard.pdf')}} </a></td>
                    <td data-title="Print" class="not-need"> <a href="{{ url('print_invoice/')}}/@{{ trip.id}}"  target="_self" class="color--primary" style="font-weight:bold;"> {{trans('messages.dashboard.print')}}</a></td>
                </tr>  
                    <tr >
                        <td ng-show="driver_trips.data.length==0" colspan="5" style="height: 46px;text-align: center;">
                        {{trans('messages.dashboard.no_details')}}.
                        </td>
                    </tr>
            </tbody>              
            @else
            <tbody ng-cloak>
                <tr class="trip-expand__origin collapsed" >
                    <td data-title="Invoice Number">{{ $trip->id }}</td>
                    <td data-title="Trip date">{{ date('F d, Y',strtotime($trip->created_at))}}</td>
                    <td data-title="Invoice" >{{ $trip->currency->original_symbol }} {{ $trip->total_invoice }}</td>
                    <td data-title="Download" class="not-need"> <a href="{{ url('download_invoice/'.$trip->id)}}"  target="_self" class="color--primary" style="font-weight:bold;">{{trans('messages.dashboard.pdf')}}</a></td>
                    <td data-title="Print" class="not-need"> <a href="#" onclick="printDiv('printableArea')" class="color--primary" style="font-weight:bold;">{{trans('messages.dashboard.print')}}</a></td>
                </tr>
            </tbody>
            @endif
            
        </table>
    </div>
    <div class="page-form push-small--top clearfix"  ng-if="{{ $all_invoice == 'true'}}">
        <div class="select float--left push-tiny--right"  style="margin-left: 10px;"><br>
            <select name="per_page" id="per_page" ng-init="selectedItem='10'" ng-model="selectedItem" ng-change="getInvoice()">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
        </div>
        <div style="padding:25px;">
        <div class="pagination-buttons-container row-space-8 float--right" ng-cloak>
            <div class="results_count pagination inline-group btn-group btn-group--bordered" style="float: right;margin-top: 20px;">
                <div class="inline-group__item" ng-show="driver_trips.data.length > 1">
                    <invoices-pagination></invoices-pagination>
                </div>
            </div>   
        </div>
    </div>
    </div>
    @if($all_invoice == 'false')
    <div class="page-lead separated--bottom col-lg-12 col-md-12 col-sm-12 col-xs-12" >
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="dr_invo">               
                <img src="{{ $trip->rider_profile_picture }}" class='img--circle img--bordered img--shadow driver-avatar'>              
            </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <p>{{trans('messages.dashboard.invoice_issued')}} {{$site_name}} {{trans('messages.dashboard.behalf')}}</p>
            <p>{{ $trip->rider_name }}</p>
            <div class="text--left">
                <div class="trip-address grid grid--full soft-double--bottom">

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
    <div id="no-more-tables" class="table-no-border" style="overflow: visible;" class="tr_ico" ng-cloak>
        <table class="col-sm-12 table-bordered table-striped table-condensed cf">
            <thead class="cf">
                <tr>
                    <th>{{trans('messages.dashboard.date')}}</th>
                    <th>{{trans('messages.dashboard.desc')}}</th>                   
                    <th>{{trans('messages.dashboard.net_amt')}}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="trip-expand__origin collapsed" >   
                    <td data-title="Tax Point Date">{{ date('F d, Y')}}</td>
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.payment_mode')}}</td>
                    <td data-title="Description">{{ $trip->payment_mode}} </td>
                </tr>
                <tr class="trip-expand__origin collapsed" >
                    <td></td>   
                    <td data-title="Description">{{trans('messages.dashboard.base_fare')}}</td>
                    <td data-title="Net Amount">{{ $trip->currency->original_symbol }} {{ $trip->base_fare }}  </td>
                </tr>
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.distance_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->distance_fare }}  </td>
                </tr>
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.time_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->time_fare }}  </td>
                </tr>
                <tr class="trip-expand__origin collapsed" ng-show="{{ $trip->peak_fare > 0 }}">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.normal_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->subtotal_fare }}  </td>
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
                
                @if(($trip->payment_mode=="PayPal" || $trip->payment_mode=="PayPal & Wallet") && $trip->applied_owe_amount>0)
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.total_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }}  {{ $trip->total_trip_fare}}</td>
                </tr> 
                @elseif($trip->payment_mode=="PayPal" || $trip->payment_mode=="PayPal & Wallet")
                @else
                @if($trip->total_trip_fare>0)
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.total_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }}  {{ $trip->total_trip_fare}}</td>
                </tr> 
                @endif
                @endif

                @if($trip->owe_amount!=0)
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.admin_amt')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->owe_amount}} </td>
                </tr>
                @endif
                @if($trip->applied_owe_amount!=0)
                <tr class="trip-expand__origin collapsed">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.owe_amt')}}</td>
                    <td data-title="Description"> - {{ $trip->currency->original_symbol }} {{ $trip->applied_owe_amount}} </td>
                </tr>
                @endif

                @if(($trip->payment_mode=="Cash" || $trip->payment_mode=="Cash & Wallet") && ($trip->cash_collect_frontend<($trip->total_fare-$trip->access_fee)) && ($trip->wallet_amount>0 || $trip->promo_amount))
                @if($trip->driver_front_payout>0)
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.driver_payout')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->driver_front_payout}} </td>
                </tr>
                @endif
                @endif

                @if($trip->payment_mode=="Cash" || $trip->payment_mode=="Cash & Wallet")
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.cash_collect')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->cash_collect_frontend}} </td>
                </tr>
                @elseif($trip->cash_collect_frontend>0)
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.cash_collect')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->cash_collect_frontend}} </td>
                </tr>
                @endif

                @if($trip->payment_mode=="Cash")
                @elseif(($trip->payment_mode=="PayPal" || $trip->payment_mode=="PayPal & Wallet" ))
                <tr class="trip-expand__origin collapsed" >   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.total_payout')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->total_payout_frontend}} </td>
                </tr>
                @endif

            </tbody>
        </table>
    </div>
</div>
<ul class="col-lg-12 col-md-12 col-sm-12 col-xs-12 table-ul">

</ul>
@endif
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