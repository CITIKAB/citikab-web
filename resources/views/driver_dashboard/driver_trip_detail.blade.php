<title>Your trip</title>
@extends('template_driver_dashboard')

@section('main')
<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content" style="padding:0px;">
<div class="page-lead separated--bottom  text--center text--uppercase"><h1 class="flush-h1 flush">{{trans('messages.profile.your_trip')}}</h1>
<div class="color--neutral" style="    margin-bottom: 0px !important; margin-top: 20px;">{{ $trip->pickup_time }} on {{ date('F d, Y',strtotime($trip->created_at))}}</div>
</div>
<div class="page-lead separated--bottom  text--center">
<a href="{{ url('driver_invoice/'.$trip->id)}}" style="    padding: 0px 15px 0px 0px !important;
    font-size: 14px !important;" type="submit" class="btn btn--primary btn-blue"><span style="padding: 7px;" class="icon icon_download"></span>{{trans('messages.profile.dwnld_invoice')}}</a>
</div>
<div class="trip-details__breakdown">
<div class="">
<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 palm-one-whole soft--bottom">
<div class="separated">
<div class="fixed-ratio fixed-ratio--1-1">
<div class="fixed-ratio__content ab_cont">
<img src="http://maps.googleapis.com/maps/api/staticmap?size=640x480&zoom=14&path=color:0x000000ff%7Cweight:4%7Cenc:{{ $trip->trip_path }}&markers=size:mid|icon:{{ url('images/pickup.png') }}|{{ $trip->pickup_latitude}},{{ $trip->pickup_longitude}}&markers=size:mid|icon:{{ url('images/drop.png')}}|{{ $trip->drop_latitude}},{{ $trip->drop_longitude}}&sensor=false&key={{$map_key}}" class="img--full img--flush">
<k></k>
</div>
</div>
</div>
<div class="separated section--light">
<div class="soft separated--bottom" style="padding:20px 10px;">
<div class="trip-address grid grid--full soft-double--bottom">
<div class="trip-address__path">
	
</div>
<div class="grid__item one-tenth pull-left" style="margin-top:6px;">
<div class="icon icon_route-dot color--positive">
	
</div>
</div>
<div class="grid__item nine-tenths">
<p class="flush">{{ $trip->pickup_time }}</p>
<h6 class="color--neutral flush">{{ $trip->pickup_location }}</h6>
</div>
</div>
<div class="trip-address grid grid--full">
<div class="grid__item one-tenth pull-left" style="margin-top:6px;">
<div class="icon icon_route-dot color--negative"></div>
</div>
<div class="grid__item nine-tenths">
<p class="flush">{{ $trip->drop_time }}</p>
<h6 class="color--neutral flush">{{ $trip->drop_location }}</h6>
</div>
</div>
</div>
<div class="soft--top">
<div class="flexbox color--neutral" style="padding-top: 20px;
    margin-bottom: 10px;">
<div class="flexbox__item text--center col-lg-4 col-md-4 col-sm-4 col-xs-4">
<div class="micro text--uppercase">{{trans('messages.profile.car')}}</div>
<h5>{{ $trip->vehicle_name }}</h5>
</div>
<div class="flexbox__item text--center col-lg-4 col-md-4 col-sm-4 col-xs-4">
<div class="micro text--uppercase">{{trans('messages.profile.kilometer')}}</div>
<h5>{{ $trip->total_km }}</h5>
</div>
<div class="flexbox__item text--center col-lg-4 col-md-4 col-sm-4 col-xs-4">
<div class="micro text--uppercase">{{trans('messages.profile.trip_time')}}</div>
<h5>{{ $trip->trip_time}}</h5>
</div>
</div>
</div>
</div>
</div>
<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 palm-one-whole soft--bottom" ng-cloak>
<h2 class="text--center text--uppercase" style="    font-size: 17px; font-weight: 600;">{{trans('messages.profile.fare_break')}}</h2><table class="table table--condensed fare-breakdown separated--top">
<tbody>
	<tr class="gamma fare-breakdown__primary-charge">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.payment_mode')}}</td>
	<td class="text--right">{{ $trip->payment_mode }}</td>
	</tr>
	<tr class="gamma fare-breakdown__primary-charge">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.base_fare')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->base_fare }}</td>
	</tr>
	<tr class="gamma fare-breakdown__primary-charge" ng-show="{{ $trip->distance_fare > 0 }}">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.distance_fare')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->distance_fare }}</td>
	</tr>
	<tr class="gamma fare-breakdown__primary-charge" ng-show="{{ $trip->time_fare > 0 }}">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.time_fare')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }} {{ $trip->time_fare }}</td>
	</tr>
	<tr class="gamma fare-breakdown__primary-charge" ng-show="{{ $trip->schedule_fare > 0 }}">
	<td colspan="2" class="text--left">{{trans('messages.schedule_fare')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }} {{ $trip->schedule_fare }}</td>
	</tr>
	<tr class="gamma fare-breakdown__primary-charge" ng-show="{{ $trip->peak_fare > 0 }}">
	<td colspan="2" class="text--left">{{trans('messages.normal_fare')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->subtotal_fare }}</td>
	</tr>
	<tr class="gamma fare-breakdown__primary-charge" ng-show="{{ $trip->peak_fare > 0}}">
	<td colspan="2" class="text--left">{{trans('messages.peak_time_fare')}}  x{{ $trip->peak_fare }} </td>
	<td class="text--right"> {{ $trip->currency->original_symbol }} {{ $trip->peak_amount }} </td>
	</tr>
	<tr class="gamma fare-breakdown__primary-charge">
	<td colspan="2" class="text--left">{{trans('messages.peak_subtotal_fare')}} </td>
	<td class="text--right"> {{ $trip->currency->original_symbol }} {{ $trip->peak_subtotal_fare }} </td>
	</tr>
	@if(($trip->payment_mode=="PayPal" || $trip->payment_mode=="PayPal & Wallet") && $trip->applied_owe_amount>0)
	<tr class="gamma weight--semibold separated--top">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.total_fare')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->total_trip_fare}}</td>
	</tr>
	@elseif($trip->payment_mode=="PayPal" || $trip->payment_mode=="PayPal & Wallet")
	@else
	@if($trip->total_trip_fare>0)
	<tr class="gamma weight--semibold separated--top">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.total_fare')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->total_trip_fare}}</td>
	</tr>
	@endif
	@endif
	
	@if($trip->owe_amount!=0)
	<tr class="gamma fare-breakdown__primary-charge">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.admin_amt')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->owe_amount }}</td>
	</tr>
	@endif
	
	@if($trip->applied_owe_amount!=0)
	<tr class="gamma fare-breakdown__primary-charge">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.owe_amt')}}</td>
	<td class="text--right"> - {{ $trip->currency->original_symbol }}  {{ $trip->applied_owe_amount }}</td>
	</tr>
	@endif

	@if(($trip->payment_mode=="Cash" || $trip->payment_mode=="Cash & Wallet") && ($trip->cash_collect_frontend<($trip->total_fare-$trip->access_fee)) && ($trip->wallet_amount>0 || $trip->promo_amount))
	@if($trip->driver_front_payout>0)
	<tr class="gamma fare-breakdown__primary-charge">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.driver_payout')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->driver_front_payout}}</td>
	</tr>	
	@endif
	@endif

	@if($trip->payment_mode=="Cash" || $trip->payment_mode=="Cash & Wallet")
	<tr class="gamma weight--semibold separated--top">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.cash_collect')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->cash_collect_frontend}}</td>
	</tr>
	@elseif($trip->cash_collect_frontend>0)
	<tr class="gamma weight--semibold separated--top">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.cash_collect')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->cash_collect_frontend}}</td>
	</tr>
	@endif

	@if($trip->payment_mode=="Cash")
	@elseif(($trip->payment_mode=="PayPal" || $trip->payment_mode=="PayPal & Wallet" ))
	<tr class="gamma weight--semibold separated--top">
	<td colspan="2" class="text--left">{{trans('messages.dashboard.total_payout')}}</td>
	<td class="text--right">{{ $trip->currency->original_symbol }}  {{ $trip->total_payout_frontend}}</td>
	</tr>
	@endif
</tbody>
</table>
</div>
</div>
</div>
<div class="trip-details__review col-lg-12 col-md-12 col-sm-12 col-xs-12 section--light separated--top hard--ends" style="padding:15px;">
<div class=" soft--top">
<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 palm-one-whole">
<table class="table">
<tbody>
<tr class="hard"><td>

<div class="img--circle img--bordered img--shadow driver-avatar">
<img src="{{ $trip->rider_thumb_image }}">
</div>
</td>
<td class="text--center beta"  id="pad-30" style="font-size: 18px !important;">{{trans('messages.profile.you_rode')}} {{ $trip->rider_name}}</td>
</tr>
</tbody>
</table>
</div>
</div></div>
</div>
</div>
</div>
</div>
</main>
@stop