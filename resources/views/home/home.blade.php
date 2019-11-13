
@extends('templatesign')

@section('main')
<div class="" ng-controller="user">
<div class="container" >
<div class="pad-44">
<h1 class="slide-head">{{trans('messages.home.title')}}</h1><p class="slide-content">{{trans('messages.home.desc')}}</p></div>

</div>
<div style="position:relative;">
<div  class="slide-img home_pageslide"></div>
@if(Auth::user()==null)
<div class="signup-home form_slide">
	
<div class="home__drive-form-sidebar home-sidebar">
{{ Form::open(array('url' => 'driver_register','class' => 'layout layout--flush section-drive-hero soft portable-soft-large')) }}

@if(Auth::user()==null)
<div class="layout__item one-whole">
<div class="home__preview-rider-image float--left"></div>
<div class="float--left ride-header">	<h4 class="flush hard"> {{trans('messages.user.ride_with')}} {{$site_name}}</h4>
<a class="btn btn--link hard--bottom rider-signup text--uppercase primary-font--bold  borderless--left" href="{{ url('signup_rider') }}"><!-- react-text: 6995 -->{{trans('messages.home.siginup')}}<!-- /react-text -->
<i class="icon icon_right-arrow push-tiny--left"></i></a>
</div></div><div class="background-line">
<span class="push-tiny--sides text--uppercase small">{{trans('messages.home.or')}}</span></div>
<div class="layout__item one-whole margin-medium margin-side">
<div class="home__preview-driver-image float--left"></div>


<div class="float--left ride-header">	<h4 class="flush hard ">{{trans('messages.home.siginup_drive')}}</h4>
<a class="btn btn--link hard--bottom rider-signup text--uppercase primary-font--bold  borderless--left" href="{{ url('signup_driver') }}"><!-- react-text: 6995 -->{{trans('messages.home.siginup')}}<!-- /react-text -->
<i class="icon icon_right-arrow push-tiny--left"></i></a>
</div>

</div>
@endif
@if(Auth::guard('company')->user()==null && Auth::user()==null)
<div class="background-line">
<span class="push-tiny--sides text--uppercase small">{{trans('messages.home.or')}}</span></div>
<div class="layout__item one-whole margin-medium margin-side">
<div class="home__preview-driver-image company-signup-icon float--left"></div>
@endif
@if(Auth::guard('company')->user()==null)
<div class="float--left ride-header">	<h4 class="flush hard">{{trans('messages.home.siginup_company')}}</h4>
<a class="btn btn--link hard--bottom rider-signup text--uppercase primary-font--bold borderless--left" href="{{ url('signup_company') }}"><!-- react-text: 6995 -->{{trans('messages.home.siginup')}}<!-- /react-text -->
<i class="icon icon_right-arrow push-tiny--left"></i></a>
</div>
@endif
</div>
</div>
</div>
@endif
<!--<div class="layout__item one-whole layout layout--flush">
<div class="layout__item one-half push-tiny--bottom">
<div class="">
{!! Form::text('first_name', '', ['class' => 'text-input text-input--joined flush--bottom border--left border--ends'.($errors->has('first_name') ? ' error-border' : ''),'placeholder' => trans('messages.user.firstname'),'id' => 'fname','style'=>'border-left: 1px solid #E5E5E4;' ]) !!}
</div></div><div class="layout__item one-half">
<div class="">	
	{!! Form::text('last_name', '', ['class' => 'text-input text-input--joined--right flush--bottom border--sides border--ends'.($errors->has('last_name') ? ' error-border' : ''),'placeholder' => trans('messages.user.lastname'),'id' => 'lname','style'=>'border-left: 1px solid #E5E5E4;' ]) !!}
</div></div>
<div class="layout__item one-whole push-tiny--bottom">
	<div class="">
		{!! Form::email('email', '', ['class' => 'text-input border--top border--sides'.($errors->has('email') ? ' error-border' : ''),'placeholder' => trans('messages.user.email') ,'style'=>'border-top: 1px solid #E5E5E4;']) !!}
	</div>
</div>
<div class="layout col-md-12 layout--flush float mobile-container left two-char">
	<div class="layout__item" id="country">
		<div id="select-title-stage" style="padding: 14px 8px;"> +{{old('country_code',1)}} </div>
		<div class="select select--xl">
			<label for="mobile-country"><div class="flag US"></div></label>
			<select name="country_code" tabindex="-1" id="mobile_country" class="square borderless--right">
			@foreach($country as $key => $value)
				<option value="{{$value->phone_code}}" data-value="+{{ $value->phone_code}}">{{ $value->long_name}}
				</option>
			@endforeach
			</select>
		</div>
	</div>
	<div class="layout__item number">
		<div class="input-group icon-input right ">
		<div class="layout layout--flush col-md-12 container-field clearfix push-small--bottom">
			<div class="field">
				<label class="field__label" for="mobile" style="padding-left:0px;font-weight:bold;margin-top: 0px !important;">Mobile number</label>
				{!! Form::tel('mobile_number', '', ['class' => 'text-input flush--bottom border--top border--sides'.($errors->has('mobile_number') ? ' error-border' : ''),'placeholder' => trans('messages.profile.mobile') ]) !!}
			</div>
			<span class="text-danger">{{ $errors->first('mobile_number') }}</span>
		</div>
		</div>
	</div>
</div>

<div class="layout__item one-whole push-tiny--bottom"><div class="">
{!! Form::password('password', array('class' => 'text-input border--ends border--sides'.($errors->has('password') ? ' error-border' : ''),'placeholder' => trans('messages.user.paswrd'),'id' => 'password') ) !!} 
</div></div>
<div class="layout__item one-whole push-tiny--bottom"><div><div style="position: relative;"><div class="_style_3jmRTe" style="-webkit-box-lines: multiple; flex-wrap: wrap; transition: all 400ms ease; height: 52px; border-color: rgb(229, 229, 228); margin: 0px 0px 24px; background-color: rgb(255, 255, 255); border-style: solid; border-width: 1px; box-sizing: border-box;">
{!! Form::text('home_address', '', ['class' => 'text-input autocomplete__input _style_JS22f'.($errors->has('home_address') ? ' error-border' : ''),'placeholder' => trans('messages.profile.profile_city'),'id' => 'home_address','autocomplete' => 'false','style' => 'flex-grow: 1; transition: all 400ms ease; font-family: ff-clan-web-pro, &quot;Helvetica Neue&quot;, Helvetica, sans-serif; font-size: 14px; font-weight: normal; line-height: 24px; border: none; padding: 10px; margin-bottom: 0px; background: transparent; box-sizing: border-box; color: rgb(0, 0, 0); outline: none;']) !!}
<input type="hidden" name="city" id='city' value="">
<input type="hidden" name="state" id="state" value="">
<input type="hidden" name="country" id="country" value="">
<input type="hidden" name="address_line1" id="address_line1" value="">
<input type="hidden" name="address_line2" id="address_line2" value="">
<input type="hidden" name="postal_code" id="postal_code">
<input type="hidden" name="latitude" id="latitude" value="">
<input type="hidden" name="longitude" id="longitude" value="">
</div>
<div style="box-sizing: border-box; border: 1px solid rgb(229, 229, 228); position: absolute; width: 100%; background: rgb(255, 255, 255); z-index: 1000; visibility: hidden;">
<div style="max-height: 300px; overflow: auto;">
<div aria-live="assertive">
	
</div>
</div>
</div>
</div>
</div>
</div>
<div class="layout__item one-whole push-tiny--bottom">
<div class="">
<input type="text" class="text-input border--ends border--sides" placeholder="Invite Code (Optional)" value="">
</div> 
</div>
<div class="layout__item one-whole push-small--bottom">
<input type="hidden" name="user_type" value="Driver">
<button name="step" value="basics" class="btn btn--primary btn--arrow position--relative btn--full error-retry-btn" style="box-sizing:border-box;text-decoration:none;color:#FFFFFF;display:inline-block;vertical-align:middle;text-align:center;margin:0;cursor:pointer;overflow:visible;background-color:#11939A;font-family:ff-clan-web-pro, &quot;Helvetica Neue&quot;, Helvetica, sans-serif;font-weight:600;font-size:14px;padding:11px 20px;border-radius:0px;border:2px solid #11939A;text-transform:uppercase;outline:none;line-height:18px;position:relative;transition:all 400ms ease;-moz-box-sizing:border-box;-webkit-transition:all 400ms ease;" >{{trans('messages.home.siginup_drive')}}<i class="icon_right-arrow-thin icon transition delta position--absolute"></i>
                </button>
</div>
<input type="submit" hidden="">
<div class="layout__item one-whole"><div class="push-tiny--bottom">{{trans('messages.home.or')}} <a href="{{ url('signup_rider') }}">{{trans('messages.home.siginup')}}</a> {{trans('messages.home.rider_acnt')}} </div><p class="legal-disclaimer flush"> {{trans('messages.user.proceed')}}{{$site_name}}{{trans('messages.user.contact')}}</p></div></div> --> {{ Form::close() }}</div></div>
<div class="pattern" style="height: 500px; width: 500px; right: 0px; position: absolute; z-index: 10;display: none;">
<div style="background-color: #A6DAEC; height: 100%; overflow: hidden;"><div aria-label="Decorative pattern" style="height: 100%;"><div class="isvg loaded" style="height: 100%;"><svg>
    <defs>
        <pattern id="a___288468778" width="60" height="60" patternUnits="userSpaceOnUse">
            <path class="pattern-stroke" d="M8.5 13L0 30l17-8.5c1.9-1 3.5-2.5 4.5-4.5L30 0 13 8.5c-2 1-3.5 2.5-4.5 4.5zM47 8.5L30 0l8.5 17c1 1.9 2.5 3.5 4.5 4.5L60 30l-8.5-17c-1-2-2.5-3.5-4.5-4.5zM51.5 47L60 30l-17 8.5c-1.9 1-3.5 2.5-4.5 4.5L30 60l17-8.5c2-1 3.5-2.5 4.5-4.5z"></path>
            <path class="pattern-stroke" d="M38.5 13L30 30l17-8.5c1.9-1 3.5-2.5 4.5-4.5L60 0 43 8.5c-2 1-3.5 2.5-4.5 4.5zm-17 34L30 30l-17 8.5c-2 1-3.5 2.5-4.5 4.5L0 60l17-8.5c2-1 3.5-2.5 4.5-4.5zM17 8.5L0 0l8.5 17c1 1.9 2.5 3.5 4.5 4.5L30 30l-8.5-17c-1-2-2.5-3.5-4.5-4.5z"></path>
            <path class="pattern-stroke" d="M17 38.5L0 30l8.5 17c1 1.9 2.5 3.5 4.5 4.5L30 60l-8.5-17c-1-2-2.5-3.5-4.5-4.5zm30 0L30 30l8.5 17c1 1.9 2.5 3.5 4.5 4.5L60 60l-8.5-17c-1-2-2.5-3.5-4.5-4.5z"></path>
        </pattern>
    </defs>
    <rect fill="url(#a___288468778)" height="100%" width="100%"></rect>
</svg>
</div>
</div>
</div>
</div>

<div class="mini-green" style="z-index: 20; position: absolute; right: 45px; bottom: -36px;"><a href="{{ url('signup_rider') }}" target="_self" class="_style_4jQAPw green-mini-div" style="width: 206px; padding: 32px 20px 20px 32px; display: block; position: relative; height: 206px; background-color: rgb(55, 112, 55);"><div class="_style_1PPmFR" style="font-weight: 500; color: rgb(255, 255, 255);font-size: 15px; line-height: 1.4;">Start riding with {{$site_name}}</div><div class="_style_3sF6Ag" style="text-transform: uppercase; font-weight: 600; position: absolute; bottom: 0px; font-size: 15px; color: rgb(255, 255, 255); padding: 32px 20px 20px 0px !important; right: 0px;"><!-- react-text: 16471 -->{{trans('messages.home.siginup')}}<!-- /react-text --><span style="transition: all 400ms ease; transform: translate(0px, 4px); margin-left: 12px; display: inline-block;"><svg viewBox="0 0 64 64" width="20px" height="20px" class=" _style_4wJp4e"><path fill-rule="evenodd" clip-rule="evenodd" d="M59.9270592,31.9847012L60,32.061058L43.7665291,49.1333275l-3.2469215-3.5932007 L51.3236885,34H4v-4h47.3943481L40.5196075,18.4069672l3.2469215-3.4938126L60,31.946312L59.9270592,31.9847012z"></path></svg></span></div></a></div>
</div>
<div class="container pad-container" style="overflow:hidden;">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 mar-height" style="padding-right: 20px; padding-left: 20px;">
<img src="images/icon/easy_way.png" class="cont-img">
<h2 class="cont-h2">{{trans('messages.home.easy_way')}}</h2>
<p class="cont-p">{{trans('messages.home.easy_content')}}</p>
</div>
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 mar-height" style="padding-right: 20px; padding-left: 20px;">
<img src="images/icon/cont2.png" class="cont-img">
<h2 class="cont-h2">{{trans('messages.home.anywhere')}}</h2>
<p class="cont-p">{{trans('messages.home.anywhere_content')}}</p>
</div>
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 mar-height" style="padding-right: 20px; padding-left: 20px;">
<img src="images/icon/cont3.png" class="cont-img">
<h2 class="cont-h2">{{trans('messages.home.lowcost')}}</h2>
<p class="cont-p">{{trans('messages.home.lowcost_content')}}</p>
</div>
</div>
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
<a class="btn btn--primary btn--arrow position--relative error-retry-btn" href="{{ url('ride') }}" style="margin-top:50px;margin-left: 20px !important;">
<div class="block-context soft-small--right" style="    width: 190px;">{{trans('messages.home.reason')}}</div>
<i class="icon_right-arrow-thin icon transition delta position--absolute"></i>
</a>
</div>
</div>

	
	<div  class="community-bottom-img">
	<div class="container pad-container-small">
	<div class="com-div" ><div style="margin-bottom: 16px;">
	<h2 class="com-head" style="font-weight:normal;letter-spacing:2px;">{{trans('messages.home.drive_you')}}</h2>
	<h3 class="com-head">{{trans('messages.home.you_need')}}</h3>
	</div>
	<div class="com-content"><p class="cmln__paragraph">{{trans('messages.home.drive_with')}}{{$site_name}}{{trans('messages.home.goals')}}</p>
</div><a class="btn btn--primary btn--arrow position--relative error-retry-btn" href="{{ url('drive') }}">
<div class="block-context soft-small--right" style="    width: 180px;">{{trans('messages.home.reason')}}</div>
<i class="icon_right-arrow-thin icon transition delta position--absolute"></i>
</a></div>
	</div>
	</div>
	
</div>
<!--<div class="phone-back">
<div class="display-flex" style=" flex-wrap: wrap;-webkit-box-lines: multiple;">
<div class="flex-value" >
<div  class="phone-img" ></div></div>
<div class="phone-content" style="padding: 52px;">
<div style="position: relative !important;"><p class="_style_ZJW1y">The new app</p>
<h2 class="_style_3zaJwR">Gets you there faster</h2></div><div><p class="cmln__paragraph">The updated Gofer app is rolling out now to cities around the world. And it’s filled with new features that make getting where you want to go faster and easier.</p>
</div><a class="btn btn--link hard--bottom rider-signup text--uppercase primary-font--bold  borderless--left" href="#">See What's New<!-/react-text --
<i class="icon icon_right-arrow push-tiny--left"></i></a></div></div></div> -->
<div class="container-fluid" style="background:rgb(241,241,241);">
<div class="container pad-container-small" style="padding-bottom:75px;">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12  column-content" style="padding:0px;">

	<div class="arrive-content" style="width:100% !important;">
<div style="position: relative !important;"><p class="_style_ZJW1y ride-support" style="
    margin-bottom: 25px !important;">{{trans('messages.home.suprt')}}</p>
</div><div><p class="cmln__paragraph ride-para">{{trans('messages.home.provide')}}</p>
</div></div>
	</div>
</div>
</div>
<div class="container-fluid" style="background:#fff;">
<div class="container pad-container-small" data-reactid="328">
	<div class="" data-reactid="329"><div class="_style_VxPAE" data-reactid="330"><p class="_style_ZJW1y" data-reactid="331">{{trans('messages.home.now_arrive')}}</p><h2 style="margin-bottom:40px !important;" class="_style_3zaJwR" data-reactid="332">{{trans('messages.home.safe')}}</h2></div>
	</div>
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;">
	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 bor-right min-xs-size" style="padding:0px;">
	<img src="images/icon/cont4.png" width="120px" height="120px">
	<div class="arrive-content">
<div style="position: relative !important;"><p class="_style_ZJW1y" style="
    margin-bottom: 25px !important;">{{trans('messages.home.helping')}}</p>
</div><div><p class="cmln__paragraph">{{trans('messages.home.city_with')}} {{$site_name}} {{trans('messages.home.city_with_content')}}</p>
</div></div>
	</div>
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 pull-right pad-left-40 min-xs-size" style="padding:0px;">
	<img src="images/icon/cont5.png" width="120px" height="120px">
	<div class="arrive-content">
<div style="position: relative !important;"><p class="_style_ZJW1y" style="    
    margin-bottom: 25px !important;">{{trans('messages.home.safe_ride')}}</p>
</div><div><p class="cmln__paragraph">{{trans('messages.home.backseat')}} {{$site_name}}{{trans('messages.home.designed')}}</p>
</div></div>
	</div>
	</div>
	</div>
</div>

</div>
</main>
@stop
<style type="text/css">
	.page-footer-back{
	background: #f8f8f9;
}
.btn-input:hover, .btn:hover, .file-input:hover, .tooltip:hover , .btn, .btn-input, .file-input, .tooltip{background:transparent !important; border: none !important}
.btn.btn--primary:hover{background:transparent !important; color: #fff !important;}
</style>