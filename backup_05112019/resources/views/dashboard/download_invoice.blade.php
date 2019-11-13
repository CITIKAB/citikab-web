<title>Invoice</title>
@extends('template_without_header_footer') 

@section('main')
 <style>
    
h1{
            padding-left    :   50px; 
            font-size       :   30px;
            text-align: center;       
            font-weight     :   bold;
        }
        table{
            border          :   1px solid black;
            border-collapse :   collapse;
            width           :   750px;
            font-size       :   16px; 
        }
        tr, td{
            border          :   1px solid black;
            padding-left    :   15px;
        }
        th{
            padding-left    :   15px;
        }
        tr{         
            border-collapse :   collapse;
        }
        div{
            padding-top     :   20px;
        }
        div{
            padding-top     :   25px;
        }
        img {
            border: 1px solid #c2c2c2;
            border-radius: 470% !important;
            object-fit: cover;
            height: 150px;
            width: 150px;
        }
        p{
             line-height: 15px;
        }        
        .no-border,.no-border tr,.no-border td {border: none;}
        .width-60{ width: 40%; } .width-40{ width: 60%; }
        * { font-family: DejaVu Sans, sans-serif; }
</style>
<div style="padding-top: 10px;">
<div ><h1>{{trans('messages.dashboard.trip_invoice')}}</h1>

</div>
    <div >
        <table >
            <thead >
                <tr>                      
                    <th>{{trans('messages.dashboard.invoice_no')}}</th>
                    <th >{{trans('messages.dashboard.trip_date')}}</th>
                    <th>{{trans('messages.dashboard.invoice')}}</th>
                    <!-- <th>Download</th>-->
                </tr>
            </thead>
            <tbody>
                <tr  >
                    <td data-title="Invoice Number">{{ $trip->id }}</td>
                    <td data-title="Trip date">{{ date('F d, Y',strtotime($trip->created_at))}}</td>
                    <td data-title="Invoice">{{ $trip->currency->original_symbol }}  {{ $trip->total_invoice }}</td>
                    <!-- <td data-title="Download"> <a onclick="print_receipt()" href="{{ url('download_invoice/'.$trip->id)}}" class="color--primary" style="font-weight:bold;"> PDF </a></td> -->
                </tr>
            </tbody>
        </table>

        <table class="no-border">
            <tr>
                <td class="width-60">
                    <div class="col-sm-6">
                        <img src="{{ $trip->rider_profile_picture }}" >
                    </div>
                </td>
                <td class="width-40">
                    <div class="col-sm-6">
                        <p>{{trans('messages.dashboard.invoice_issued')}}{{$site_name}}{{trans('messages.dashboard.behalf')}}</p>
                        <p>{{ $trip->rider_name }}<br>
                        <p class="flush">{{ $trip->pickup_time }}</p>
                        <h6 class="color--neutral flush">{{ $trip->pickup_location }}</h6><br>
                    
                        <p class="flush">{{ $trip->drop_time }}</p>
                        <h6 class="color--neutral flush">{{ $trip->drop_location }}</h6><br>
                        </p>                         
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div>
        <table>
            <thead class="cf">
                <tr>
                    <th>{{trans('messages.dashboard.date')}}</th>
                    <th>{{trans('messages.dashboard.desc')}}</th>                   
                    <th>{{trans('messages.dashboard.net_amt')}}</th>
                </tr>
            </thead>
            <tbody>
                <tr>   
                    <td data-title="Tax Point Date">{{ date('F d, Y')}}</td> 
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.payment_mode')}}</td>
                    <td data-title="Description">{{ $trip->payment_mode}} </td>
                </tr>
                <tr>
                    <td></td>  
                    <td data-title="Description">{{trans('messages.dashboard.base_fare')}}</td>
                    <td data-title="Net Amount">{{ $trip->currency->original_symbol }} {{ $trip->base_fare }} </td>
                </tr>
                @if($trip->distance_fare > 0 )
                <tr ng-show="{{ $trip->distance_fare > 0 }}">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.distance_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->distance_fare }}</td>
                </tr>
                @endif
                @if($trip->time_fare > 0 )
                <tr ng-show="{{ $trip->time_fare > 0 }}">   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.time_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->time_fare }}</td>
                </tr>
                @endif
                @if($trip->schedule_fare > 0 )
                <tr>
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.schedule_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->schedule_fare }}  </td>
                </tr>
                @endif
                @if($trip->peak_fare > 0 )
                <tr>
                    <td></td>
                    <td data-title="Tax Point Date">{{trans('messages.normal_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->subtotal_fare }}</td>
                </tr>
                <tr>
                    <td></td>
                    <td data-title="Tax Point Date">{{trans('messages.peak_time_fare')}}  x{{ $trip->peak_fare }}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->peak_amount }} </td>
                </tr>
                <tr>   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.peak_subtotal_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->peak_subtotal_fare }} </td>
                </tr>
                @endif
                @if(($trip->payment_mode=="PayPal" || $trip->payment_mode=="PayPal & Wallet") && $trip->applied_owe_amount>0)
                <tr>   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.total_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }}  {{ $trip->total_trip_fare}}</td>
                </tr> 
                @elseif($trip->payment_mode=="PayPal" || $trip->payment_mode=="PayPal & Wallet")
                @else
                @if($trip->total_trip_fare>0)
                <tr>   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.total_fare')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }}  {{ $trip->total_trip_fare}}</td>
                </tr> 
                @endif
                @endif
                
                @if($trip->owe_amount!=0)
                <tr>   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.admin_amt')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->owe_amount}} </td>
                </tr>
                @endif
                @if($trip->applied_owe_amount!=0)
                <tr>   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.owe_amt')}}</td>
                    <td data-title="Description"> - {{ $trip->currency->original_symbol }} {{ $trip->applied_owe_amount}} </td>
                </tr>
                @endif
                
                @if(($trip->payment_mode=="Cash" || $trip->payment_mode=="Cash & Wallet") && ($trip->cash_collect_frontend<($trip->total_fare-$trip->access_fee)) && ($trip->wallet_amount>0 || $trip->promo_amount))
                @if($trip->driver_front_payout>0)
                <tr>   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.driver_payout')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->driver_front_payout}} </td>
                </tr>
                @endif
                @endif

                @if($trip->payment_mode=="Cash" || $trip->payment_mode=="Cash & Wallet")
                <tr>   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.cash_collect')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->cash_collect_frontend}} </td>
                </tr>
                @elseif($trip->cash_collect_frontend>0)
                <tr>   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.cash_collect')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->cash_collect_frontend}} </td>
                </tr>
                @endif

                @if($trip->payment_mode=="Cash")
                @elseif(($trip->payment_mode=="PayPal" || $trip->payment_mode=="PayPal & Wallet" ))
                <tr>   
                    <td></td>   
                    <td data-title="Tax Point Date">{{trans('messages.dashboard.total_payout')}}</td>
                    <td data-title="Description">{{ $trip->currency->original_symbol }} {{ $trip->total_payout_frontend}} </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
    <ul class="col-lg-12 col-md-12 col-sm-12 col-xs-12 table-ul">
       
    </ul>
</div>
</div>
</div>
</div>
</div>
</main>
<style type="text/css">

</style>

@stop