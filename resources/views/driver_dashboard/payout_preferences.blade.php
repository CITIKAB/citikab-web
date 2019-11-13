@extends('template_driver_dashboard')

@section('main')
  <div class="col-lg-9 col-md-9 col-sm-12 col-xs-12 flexbox__item four-fifths page-content">
    <div class="page-lead separated--bottom  text--center text--uppercase"><h1 class="flush-h1 flush">  {{ trans('messages.account.payout_methods') }}</h1>
</div>
<main id="site-content" role="main" ng-controller="payout_preferences">

<div class=" row-space-top-4 row-space-4">
  <div class="row">
   
 
    <div class="col-md-12">
      <div class="payout_setup" id="payout_setup">
        <div class="panel row-space-4">
          <div class="panel-header">
              {{ trans('messages.account.payout_methods') }}
          </div>
          <div class="panel-body" id="payout_intro">
            <p class="payout_intro">
              {{ trans('messages.account.payout_methods_desc') }}.
            </p>
            <div class="scroll_table">
              <table class="table table-striped" id="payout_methods">
              @if(count($bank_detail))
             <thead>
                  <tr class="text-truncate">
                    <th>{{ trans('messages.account.method') }}</th>
                    <th>{{ trans('messages.account.details') }}</th>
                    <th>{{ trans('messages.driver_dashboard.status') }}</th>
                  </tr>
                </thead>
                <tbody>
                @foreach($bank_detail as $row)
                  <tr>
                    <td>
                      {{trans('messages.payout.bank_transfer')}}
                      @if($row->is_default == 'Yes')
                      <span class="label label-info">{{ trans('messages.account.default') }}</span>
                      @endif
                    </td>
                    <td>
                        {{$row->account_number}}
                          <br>
                        {{$row->bank_name}}
                    </td>
                    
                    <td class="payout-options">
                    @if($row->is_default != 'Yes')
                    <li class="dropdown-trigger list-unstyled">
                        <a data-prevent-default="" href="javascript:void(0);" class="link-reset text-truncate" id="payout-options-{{ $row->id }}">
                         {{ trans('messages.account.options') }}
                          <i class="icon icon-caret-down"></i>
                        </a>
                        <ul data-sticky="true" data-trigger="#payout-options-{{ $row->id }}" class="tooltip tooltip-top-left list-unstyled dropdown-menu" aria-hidden="true">
                          <li>
                            <a rel="nofollow" data-method="post" class="link-reset menu-item" href="{{ url('/') }}/payout_delete/{{ $row->id }}">{{ trans('messages.account.remove') }}</a>
                          </li>
                          <li>
                            <a rel="nofollow" data-method="post" class="link-reset menu-item" href="{{ url('/') }}/payout_default/{{ $row->id }}">{{ trans('messages.account.set_default') }}</a>
                          </li>
                        </ul>
                    </li>
                    @else
                    {{trans('messages.account.ready')}}
                    @endif        
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              @endif
                <tfoot>
                  <tr id="add_payout_method_section">
                    <td colspan="5">
                        <a id="add-payout-method-button" class="btn btn-primary pop-striped" href="javascript:void(0);" data-toggle="modal" data-target="#payout_popup1">
                        {{ trans('messages.account.add_payout_method') }}
                        </a>
                    </td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>
        <div style="display:none;" class="add_payout_section" id="payout_new_select"></div>
        <div style="display:none;" class="add_payout_section" id="payout_edit"></div>
      </div>
      <div id="taxes"></div>
    </div>
  

  </div>
</div>
    </main>

    <div class="modal poppayout fade" id="payout_popup1" aria-hidden="false" style="" tabindex="-1">
     
          <div id="modal-add-payout-set-address" class="modal-content">
  <div class="panel-header">
     <button type="button" class="close" data-dismiss="modal">&times;</button>
    {{ trans('messages.account.add_payout_method') }}
  </div>
  <div class="flash-container" id="popup1_flash-container"> </div>
  <form class="modal-add-payout-pref" action="{{ url('payout_preferences/'.Auth::user()->id) }}" id="add_payout_method" method="post" id="address">
    {!! Form::token() !!}
    <div class="panel-body">
      <input type="hidden" name="new" value="new">
      <div class="payout_popup_view">
        <label for="holder_name">{{ trans('messages.payout.account_holder_name') }} <span style="color:red">*</span></label>        
        <div class="payout_input_field">
        {!! Form::text('account_holder_name', '', ['id' => 'account_holder_name','autocomplete'=>"off"]) !!} 
      </div>
          <p class="text-danger" >{{$errors->first('account_holder_name')}}</p>
      </div>
      <div class="payout_popup_view">
        <label for="account_number">{{ trans('messages.payout.account_number') }} <span style="color:red">*</span></label>        
        <div class="payout_input_field">
        {!! Form::text('account_number', '', ['id' => 'account_number','autocomplete'=>"off"]) !!}
      </div>
          <p class="text-danger" >{{$errors->first('account_number')}}</p>
      </div>
      <div class="payout_popup_view">
        <label for="bank_name">{{ trans('messages.payout.bank_name') }} <span style="color:red">*</span></label>        
        <div class="payout_input_field">
        {!! Form::text('bank_name', '', ['id' => 'bank_name','autocomplete'=>"off"]) !!}
      </div>
          <p class="text-danger" >{{$errors->first('bank_name')}}</p>
      </div>
      <div class="payout_popup_view">
        <label for="bank_location">{{ trans('messages.payout.bank_location') }} <span style="color:red">*</span></label>        
        <div class="payout_input_field">
        {!! Form::text('bank_location', '', ['id' => 'bank_location','autocomplete'=>"off"]) !!}
      </div>
          <p class="text-danger" >{{$errors->first('bank_location')}}</p>
      </div>
      
    </div>
    <div class="panel-footer payout_footer paypal-list">
      <input type="submit" value="{{ trans('messages.user.submit') }}" class="btn btn-primary" >
    </div>
  </form>
</div>
</div>


<div class="modal poppayout perferenace_payout" id="payout_popup2" aria-hidden="false" style="padding-left: 0 !important;" tabindex="-1">

    <div id="modal-add-payout-set-address" class="modal-content">
  <div class="panel-header">
    <!-- <a data-behavior="modal-close" class="panel-close " href="javascript:void(0);"></a> -->
    <button type="button" class="close pay_close" data-dismiss="modal">&times;</button>
    {{ trans('messages.account.add_payout_method') }}
  </div>
  <div class="flash-container" id="popup2_flash-container"> </div>
  <form class="modal-add-payout-pref" id="country_options" accept-charset="UTF-8">
  {!! Form::token() !!}
  
    <input type="hidden" id="payout_info_payout2_address1" value="" name="address1">
    <input type="hidden" id="payout_info_payout2_address2" value="" name="address2">
    <input type="hidden" id="payout_info_payout2_city" value="" name="city">
    <input type="hidden" id="payout_info_payout2_country" value="" name="country">
    <input type="hidden" id="payout_info_payout2_state" value="" name="state">
    <input type="hidden" id="payout_info_payout2_zip" value="" name="postal_code">

  <div class="panel-body">
      <div>
        <p>{{ trans('messages.account.payout_released_desc1') }}</p>
        <p>{{ trans('messages.account.payout_released_desc2') }}</p> 
        <p>{{ trans('messages.account.payout_released_desc3') }}</p>
      </div>
         <div class="scroll_table">
      <table id="payout_method_descriptions" class="table table-striped">
        <thead><tr>
          <th></th>
          <th>{{ trans('messages.account.payout_method') }}</th>
          <th>{{ trans('messages.account.processing_time') }}</th>
          <th>{{ trans('messages.account.additional_fees') }}</th>
          <th>{{ trans('messages.account.currency') }}</th>
          <th>{{ trans('messages.account.details') }}</th>
        </tr></thead>
        <tbody>
          <tr>
            <td>
              <input type="radio" {{ old('payout_method') =='PayPal' ? 'checked' : ''}} value="PayPal" name="payout_method" id="payout2_method">
            </td>
            <td class="type"><label for="payout_method">PayPal</label></td>
            <td>3-5 {{ trans('messages.account.business_days') }}</td>
            <td>{{ trans('messages.account.none') }}</td>
            <td>{{ PAYPAL_CURRENCY_CODE }}</td>
            <td>{{ trans('messages.account.business_day_processing') }}</td>
          </tr>
          <tr>
            <td>
              <input type="radio" {{ old('payout_method') =='Stripe' ? 'checked' : ''}} value="Stripe" name="payout_method" id="payout2_method">
            </td>
            <td class="type"><label for="payout_method">Stripe</label></td>
            <td>5-7 {{ trans('messages.account.business_days') }}</td>
            <td>{{ trans('messages.account.none') }}</td>
            <td>{{ PAYPAL_CURRENCY_CODE }}</td>
            <td>{{ trans('messages.account.business_day_processing') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel-footer payout_footer">
      <input type="submit" value="{{ trans('messages.account.next') }}" id="select-payout-method-submit" class="btn btn-primary" data-target="">
  </div>
</form>

</div></div>


<div class="modal poppayout" id="payout_popup3" aria-hidden="false" style="" tabindex="-1">
    <div id="modal-add-payout-set-address" class="modal-content">
  <div class="panel-header">
    <!-- <a data-behavior="modal-close" class="panel-close " href="javascript:void(0);"></a> -->
    <button type="button" class="close pay_close" data-dismiss="modal">&times;</button>
    {{ trans('messages.account.add_payout_method') }}
  </div>
  <div class="flash-container hide" id="popup3_flash-container"><div class="alert alert-error alert-error alert-header"><a class="close alert-close" href="javascript:void(0);"></a><i class="icon alert-icon icon-alert-alt"></i>{{trans('messages.account.valid_email')}}</div>  </div>  

  <form method="post" id="payout_paypal" action="{{ url('payout_preferences/'.Auth::user()->id) }}" accept-charset="UTF-8">
  {!! Form::token() !!}

  <input type="hidden" id="payout_info_payout3_address1" value="" name="address1">
  <input type="hidden" id="payout_info_payout3_address2" value="" name="address2">
  <input type="hidden" id="payout_info_payout3_city" value="" name="city">
  <input type="hidden" id="payout_info_payout3_country" value="" name="country">
  <input type="hidden" id="payout_info_payout3_state" value="" name="state">
  <input type="hidden" id="payout_info_payout3_zip" value="" name="postal_code">
  <input type="hidden" id="payout3_method" value="" name="payout_method" ng-model="payout_method">

  <div class="panel-body">

  PayPal {{ trans('messages.account.email_id') }}
    <input type="text" name="paypal_email" id="paypal_email" >
  </div>

  <div class="panel-footer payout_footer">
    <input type="submit" value="{{ trans('messages.account.submit') }}" id="modal-paypal-submit" class="btn btn-primary">
  </div>
</form>
</div>
</div>



 <!-- Popup for get Stripe datas -->
<div class="modal poppayout" id="payout_popupstripe" aria-hidden="false" style="" tabindex="-1">
<div id="modal-add-payout-set-address" class="modal-content">
  <div class="panel-header">
    <a data-behavior="modal-close" class="panel-close" href="javascript:void(0);"></a>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    {{ trans('messages.account.add_payout_method') }}
  </div>
  <div class="flash-container" id="popup4_flash-container"> </div>

  <form method="post" id="payout_stripe" action="{{ url('update_payout_preferences/'.Auth::user()->id) }}" accept-charset="UTF-8" enctype="multipart/form-data">
  {!! Form::token() !!}

  <input type="hidden" id="payout_info_payout4_address1" value="" name="address1">
  <input type="hidden" id="payout_info_payout4_address2" value="" name="address2">
  <input type="hidden" id="payout_info_payout4_city" value="" name="city">
  <input type="hidden" id="payout_info_payout4_country" value="" name="country">
  <input type="hidden" id="payout_info_payout4_state" value="" name="state">
  <input type="hidden" id="payout_info_payout4_zip" value="" name="postal_code">
  <input type="hidden" id="payout4_method" value="" name="payout_method" ng-model="payout_method">

  <div class="panel-body panel-body-payout" >
    
   

      <!-- Bank Name -->
      <div ng-show="mandatory[payout_country][3]">
        <label class="" for="bank_name">@{{mandatory[payout_country][3]}}<span style="color:red">*</span></label>
        
          
          {!! Form::text('bank_name', '', ['id' => 'bank_name', 'class' => 'form-control']) !!}

          <p class="text-danger" >{{$errors->first('bank_name')}}</p>

      </div>
      <!-- Bank Name -->
      <!-- Branch Name -->
      <div ng-show="mandatory[payout_country][4]">
        <label class="" for="bank_name">@{{mandatory[payout_country][4]}}<span style="color:red">*</span></label>
        
          
          {!! Form::text('branch_name', '', ['id' => 'branch_name', 'class' => 'form-control']) !!}

          <p class="text-danger" >{{$errors->first('branch_name')}}</p>

      </div>
      <!-- Branch Name -->
      <!-- Routing number  -->
      
      <div ng-if="payout_country" class="routing_number_cls" ng-hide="iban_supported_countries.includes(payout_country)">
        <label class="" for="routing_number">@{{mandatory[payout_country][0]}}<span style="color:red">*</span></label>
        
        <div class="">
          {!! Form::text('routing_number', @$payout_preference->routing_number, ['id' => 'routing_number', 'class' => 'form-control']) !!}
          <p class="text-danger" >{{$errors->first('routing_number')}}</p>
        </div>
      </div>
    
      <!-- Routing number -->
      
      
      <!-- Branch code -->
      <div ng-show="mandatory[payout_country][2]"> 
        <label class="" for="branch_code">@{{mandatory[payout_country][2]}}<span style="color:red">*</span></label>
        
          
          {!! Form::text('branch_code', '', ['id' => 'branch_code', 'class' => 'form-control','maxlength'=>'3']) !!}

          <p class="text-danger" >{{$errors->first('branch_code')}}</p>

      </div>
      <!-- Branch code -->

      <!-- Account Number -->
      <div ng-if="payout_country">
        <label class="" for="account_number" ng-hide="iban_supported_countries.includes(payout_country)"><span class="account_number_cls">@{{mandatory[payout_country][1]}}</span><span style="color:red">*</span></label>
        <label class="" for="account_number" ng-show="iban_supported_countries.includes(payout_country)">{{ trans('messages.account.iban_number') }}<span style="color:red">*</span></label>
          
          {!! Form::text('account_number', '', ['id' => 'account_number', 'class' => 'form-control']) !!}

          <p class="text-danger" >{{$errors->first('account_number')}}</p>

      </div>
      <!-- Account Number -->

       <!-- Account Holder name -->
      <div>
        <label ng-if="payout_country == 'JP'" for="holder_name">@{{mandatory[payout_country][5]}}<span style="color:red">*</span></label>          
        <label ng-if="payout_country != 'JP'" for="holder_name">{{ trans('messages.account.holder_name') }}<span style="color:red">*</span></label>          
          {!! Form::text('holder_name', '', ['id' => 'holder_name', 'class' => 'form-control']) !!}
          <p class="text-danger" >{{$errors->first('holder_name')}}</p>

      </div>
      <!-- Account Holder name -->

      <!-- SSN Last 4 only for US -->
      <div ng-show="payout_country == 'US'">
           
        <label ng-if="payout_country == 'US'" for="ssn_last_4">{{ trans('messages.account.ssn_last_4') }}<span style="color:red">*</span></label>          
          {!! Form::text('ssn_last_4', '', ['id' => 'ssn_last_4', 'class' => 'form-control','maxlength'=>'4']) !!}
          <p class="text-danger" >{{$errors->first('ssn_last_4')}}</p>

      </div>
      <!-- SSN Last 4 only for US -->

      <!-- Phone number only for Japan -->
      <div ng-show="payout_country == 'JP'">
        <label class="" for="phone_number" >{{ trans('messages.profile.phone_number') }}<span style="color:red">*</span></label>
        
          {!! Form::text('phone_number', '', ['id' => 'phone_number', 'class' => 'form-control']) !!}

          <p class="text-danger" >{{$errors->first('phone_number')}}</p>

      </div>
      <!-- Phone number only for Japan -->
      <input type="hidden" id="is_iban" name="is_iban" ng-value="iban_supported_countries.includes(payout_country) ? 'Yes' : 'No'">
      <input type="hidden" id="is_branch_code" name="is_branch_code" ng-value="branch_code_required.includes(payout_country) ? 'Yes' : 'No'">
      <!-- Gender only for Japan -->
      @if(!Auth::user()->gender)
      <div ng-if="payout_country == 'JP'" class="col-md-6 col-sm-12 p-0 select-cls row-space-3">
      <label for="user_gender">
          {{ trans('messages.profile.gender') }}
      </label>
      <div class="select">
        {!! Form::select('gender', ['male' => trans('messages.profile.male'), 'female' => trans('messages.profile.female')], Auth::user()->gender, ['id' => 'user_gender', 'placeholder' => trans('messages.profile.gender'), 'class' => 'focus','style'=>'min-width:140px;']) !!}
        <span class="text-danger">{{ $errors->first('gender') }}</span>    
      </div>
                
      </div>
      @endif
      <!-- Gender only for Japan -->

      <!-- Address Kanji Only for Japan -->
      <div ng-class="(payout_country == 'JP'? 'jp_form row':'')" class="clearfix ">       
        <div ng-if="payout_country == 'JP'" class="col-md-12 col-sm-12">
        <label><b>Address Kanji:</b></label>
        <div>
          <label for="payout_info_payout_address2">{{ trans('messages.account.address') }} 1<span style="color:red">*</span></label>
          {!! Form::text('kanji_address1', '', ['id' => 'kanji_address1', 'class' => 'form-control']) !!}
            <p class="text-danger" >{{$errors->first('kanji_address1')}}</p>
        </div>

        <div>
            <label for="payout_info_payout_address2">Town<span style="color:red">*</span></label>
            {!! Form::text('kanji_address2', '', ['id' => 'kanji_address2', 'class' => 'form-control']) !!}
            <p class="text-danger" >{{$errors->first('kanji_address2')}}</p>
        </div>

        <div>
          <label for="payout_info_payout_city">{{ trans('messages.account.city') }} <span style="color:red">*</span></label>
            {!! Form::text('kanji_city', '', ['id' => 'kanji_city', 'class' => 'form-control']) !!}
            <p class="text-danger" >{{$errors->first('kanji_city')}}</p>
        </div>

        <div>
          <label for="payout_info_payout_state">{{ trans('messages.account.state') }} / {{ trans('messages.account.province') }}<span style="color:red">*</span></label>
            {!! Form::text('kanji_state', '', ['id' => 'kanji_state', 'class' => 'form-control']) !!}
            <p class="text-danger" >{{$errors->first('kanji_state')}}</p>
        </div>

        <div>
          <label for="payout_info_payout_zip">{{ trans('messages.account.postal_code') }} <span style="color:red">*</span></label>
            {!! Form::text('kanji_postal_code', '', ['id' => 'kanji_postal_code', 'class' => 'form-control']) !!}
            <p class="text-danger" >{{$errors->first('kanji_postal_code')}}</p>
        </div>
        </div>
      </div>
      <!-- Address Kanji Only for Japan -->

      <!-- Legal document -->

      <div id="legal_document" class="legal_document">
        <div class="row">
          <label class="control-label required-label col-md-12 col-sm-12 row-space-2" for="document">@lang('messages.account.legal_document') @lang('messages.account.legal_document_format')<span style="color:red">*</span></label>
          <div class="col-md-12 col-sm-12 ">
              {!! Form::file('document', ['id' => 'document', 'class' => '',"accept"=>".jpg,.jpeg,.png"]) !!}
              <p class="text-danger" >{{$errors->first('document')}}</p>
          </div>  
          </div>                       
      </div>
      <!-- Legal document -->
      
      <input type="hidden" name="holder_type" value="individual" id="holder_type">
      <input type="hidden" name="stripe_token" id="stripe_token" >
      <p  class="text-danger col-sm-12" id="stripe_errors"></p>
  </div>

  <div class="panel-footer payout_footer">
    <input type="submit" value="{{ trans('messages.account.submit') }}" id="modal-stripe-submit" class="btn btn-primary">
  </div>
</form>
</div>
</div>

<!-- end Popup -->


<input type="hidden" id="blank_address" value="{{trans('messages.account.blank_address')}}">
<input type="hidden" id="blank_city" value="{{trans('messages.account.blank_city')}}">
<input type="hidden" id="blank_post" value="{{trans('messages.account.blank_post')}}">
<input type="hidden" id="blank_country" value="{{trans('messages.account.blank_country')}}">
<input type="hidden" id="choose_method" value="{{trans('messages.account.choose_method')}}">
<input type="hidden" id="blank_holder_name" value="{{trans('messages.account.blank_holder_name')}}">
<input type="hidden" name="stripe_publish_key" id="stripe_publish_key" value="{{@$stripe_data[0]->value}}">

</div>
</div>
</div>
</div>
@stop
@push('scripts')
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script type="text/javascript">
  var payout_errors = {!! count($errors->getMessages()) !!};  
  if(payout_errors > 0 && '{{Auth::user()->company_id <= 1}}'){
    $('#payout_popupstripe').modal('show');
  }
</script>
@endpush