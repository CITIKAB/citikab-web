$('.field__input').on('input', function() {
  var $field = $(this).closest('.field');
  if (this.value) {
    $field.addClass('field--not-empty');
  } else {
    $field.removeClass('field--not-empty');
  }
});
$http = angular.injector(["ng"]).get("$http");
$('#js-currency-select').on('change', function(){
	currency_code = $(this).val();
	$http.post(APP_URL+'/set_session', {currency: currency_code}).then(function(response){
		location.reload();
	});
});
$('#js-language-select').on('change', function(){
  language_code = $(this).val();
  $http.post(APP_URL+'/set_session', {language: language_code
  }).then(function(response){
    location.reload();
  });
});

//Payout Preferences
app.controller('payout_preferences', ['$scope', '$http', function($scope, $http) {

  $(document).ready(function () {
    $("#ssn_last_4").keypress(function (e) {
         //if the letter is not digit then display error and don't type anything
         if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {            
          return false;
        }
      });
    $scope.change_currency();
  });

  $('#payout-next').click(function() {
    var validation_container = '<div class="alert alert-error alert-error alert-header"><a class="close alert-close" href="javascript:void(0);"></a><i class="icon alert-icon icon-alert-alt"></i>';
    if ($('#payout_info_payout_address1').val().trim() == '') {
      $('#popup1_flash-container').html(validation_container+$('#blank_address').val()+'</div>');
      return false;
    }
    if ($('#payout_info_payout_city').val().trim() == '') {
      $('#popup1_flash-container').html(validation_container+$('#blank_city').val()+'</div>');
      return false;
    }
    if ($('#payout_info_payout_zip').val().trim() == '') {
      $('#popup1_flash-container').html(validation_container+$('#blank_post').val()+'</div>');
      return false;
    }
    if ($('#payout_info_payout_country').val().trim() == null) {
      $('#popup1_flash-container').html(validation_container+$('#blank_country').val()+'</div>');
      return false;
    }

    $('#payout_info_payout2_address1').val($('#payout_info_payout_address1').val());
    $('#payout_info_payout2_address2').val($('#payout_info_payout_address2').val());
    $('#payout_info_payout2_city').val($('#payout_info_payout_city').val());
    $('#payout_info_payout2_state').val($('#payout_info_payout_state').val());
    $('#payout_info_payout2_zip').val($('#payout_info_payout_zip').val());
    $('#payout_info_payout2_country').val($('#payout_info_payout_country').val());

    $('#payout_popup1').modal('hide');
    $('#payout_popup2').modal('show');
    $('body').addClass('new_fix');
  });

  $('#payout_info_payout_country').change(function() {
    $scope.country = $(this).val();
    $('#payout_info_payout_country1').val($(this).val());
    if($('#payout_info_payout_country1').val() == '' || $('#payout_info_payout_country1').val() == undefined)
    {            
      $("#payout_info_payout_country1").val('');
      $scope.payout_country = '';
      $scope.payout_currency = '';
    }
    else
    {
      $scope.payout_country = $(this).val();
      $('#payout_info_payout_country1').trigger("change");
      $scope.change_currency();
    }
  });

  $('#select-payout-method-submit').click(function() {
    var validation_container = '<div class="alert alert-error alert-error alert-header"><a class="close alert-close" href="javascript:void(0);"></a><i class="icon alert-icon icon-alert-alt"></i>';
    if ($('[id="payout2_method"]:checked').val() == undefined) {
      $('#popup2_flash-container').html(validation_container+$('#choose_method').val()+'</div>');
      return false;
    }

    $('#payout_info_payout3_address1').val($('#payout_info_payout2_address1').val());
    $('#payout_info_payout3_address2').val($('#payout_info_payout2_address2').val());
    $('#payout_info_payout3_city').val($('#payout_info_payout2_city').val());
    $('#payout_info_payout3_state').val($('#payout_info_payout2_state').val());
    $('#payout_info_payout3_zip').val($('#payout_info_payout2_zip').val());
    $('#payout_info_payout3_country').val($('#payout_info_payout2_country').val());
    $('#payout3_method').val($('[id="payout2_method"]:checked').val());

    $('#payout_info_payout4_address1').val($('#payout_info_payout2_address1').val());
    $('#payout_info_payout4_address2').val($('#payout_info_payout2_address2').val());
    $('#payout_info_payout4_city').val($('#payout_info_payout2_city').val());
    $('#payout_info_payout4_state').val($('#payout_info_payout2_state').val());
    $('#payout_info_payout4_zip').val($('#payout_info_payout2_zip').val());
    $('#payout_info_payout4_country').val($('#payout_info_payout2_country').val());
    $('#payout4_method').val($('[id="payout2_method"]:checked').val());

    payout_method = $("#payout3_method").val();
    if(payout_method == 'Stripe')
    {
      $('#payout_popup2').modal('hide');
      $('#payout_popupstripe').modal('show');
    }
    else
    {
      $('#payout_popup2').modal('hide');
      $('#payout_popup3').modal('show');
    }

  });

  $('#payout_paypal').submit(function() {
    payout_method = $("#payout3_method").val();
    if(payout_method != 'PayPal') {
      return true;
    }
    var validation_container = '<div class="alert alert-error alert-error alert-header"><a class="close alert-close" href="javascript:void(0);"></a><i class="icon alert-icon icon-alert-alt"></i>';
    var emailChar = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    if (emailChar.test($('#paypal_email').val())) {
      return true;
    }
    else {
      $('#popup3_flash-container').removeClass('hide');
      return false;
    }
  });
    // change currency based on country selected
    $scope.change_currency = function()
    {
      var selected_country = [];
      angular.forEach($scope.country_currency, function(value, key) {          
        if($('#payout_info_payout_country1').val() == key)
         selected_country = value;
     });

      if(selected_country)
      {
        var $el = $("#payout_info_payout_currency");
                    $el.empty(); // remove old options
                    $el.append($("<option></option>").attr("value", '').text('Select'));
                    $.each(selected_country, function(key,value) {
                      $el.append($("<option></option>").attr("value", value).text(value));
                      if($scope.old_currency != '')
                      {
                        $('#payout_info_payout_currency').val($scope.payout_currency);
                      }
                      else
                      {
                        $('#payout_info_payout_currency').val(selected_country[0]);
                      }
                    });
                    
                    if($('#payout_info_payout_country1').val() == 'GB' && $('#payout_info_payout_currency').val() == 'EUR')
                    {
                     $('.routing_number_cls').addClass('hide');
                     $('.account_number_cls').html('IBAN');
                   }
                   else
                   {
                    $('.routing_number_cls').removeClass('hide');
                    $('.account_number_cls').html('Account Number');
                  }
                }
                else
                {
                  var $el = $("#payout_info_payout_currency");
                  $el.empty(); // remove old options                   
                  $el.append($("<option></option>").attr("value", '').text('Select'));
                }

                if($('#payout_info_payout_currency').val() == '' || $('#payout_info_payout_currency').val() == null) {
                  $("#payout_info_payout_currency").val($("#payout_info_payout_currency option:first").val());
                }
              }

              $(document).on('change', '#payout_info_payout_country1', function() {

                $scope.change_currency();

                if($('#payout_info_payout_country1').val() == 'GB' && $('#payout_info_payout_currency').val() == 'EUR')
                {
                 $('.routing_number_cls').addClass('hide');
                 $('.account_number_cls').html('IBAN');

               }
               else
               {
                $('.routing_number_cls').removeClass('hide');
                $('.account_number_cls').html('Account Number');
              }
              $scope.payout_currency = $('#payout_info_payout_currency').val();
              $("#payout_info_payout_currency").val($("#payout_info_payout_currency option:first").val());
              $('#payout_info_payout_country').val($('#payout_info_payout_country1').val());

            });
              $(document).on('change', '#payout_info_payout_currency', function() {
                $scope.payout_currency = $('#payout_info_payout_currency').val()
                if($('#payout_info_payout_country1').val() == 'GB' && $('#payout_info_payout_currency').val() == 'EUR')
                {
                 $('.routing_number_cls').addClass('hide');
                 $('.account_number_cls').html('IBAN');

               }
               else
               {
                $('.routing_number_cls').removeClass('hide');
                $('.account_number_cls').html('Account Number');


              }

            });
    // set publishable key for stripe validation on js //
    var stripe_publish_key = document.getElementById("stripe_publish_key").value;
    var stripe = Stripe.setPublishableKey(stripe_publish_key);

    $('#payout_stripe').submit(function() {                 

      $('#payout_info_payout4_address1').val($('#payout_info_payout_address1').val());
      $('#payout_info_payout4_address2').val($('#payout_info_payout_address2').val());
      $('#payout_info_payout4_city').val($('#payout_info_payout_city').val());
      $('#payout_info_payout4_state').val($('#payout_info_payout_state').val());
      $('#payout_info_payout4_zip').val($('#payout_info_payout_zip').val());        

        // check stripe token already exist
        stripe_token = $("#stripe_token").val();
        if(stripe_token != ''){
          return true;
        }
        // required field validation --start-- //
        if($('#payout_info_payout_country1').val() == '')
        {
          $("#stripe_errors").html('Please fill all required fields');               
          return false;
        }
        if($('#payout_info_payout_currency').val() == '')
        {
          $("#stripe_errors").html('Please fill all required fields');               
          return false;
        }
        if($('#holder_name').val() == '')
        {
          $("#stripe_errors").html('Please fill all required fields');               
          return false;
        }
        
        is_iban = $('#is_iban').val();
        is_branch_code = $('#is_branch_code').val();

        // bind bank account params to get stripe token
        var bankAccountParams = {
          country: $('#payout_info_payout_country1').val(),
          currency: $('#payout_info_payout_currency').val(),              
          account_number: $('#account_number').val(),
          account_holder_name: $('#holder_name').val(),
          account_holder_type: $('#holder_type').val()
        };

          // check whether iban supported country or not for bind routing number
          if(is_iban == 'No')
          {            
            if(is_branch_code == 'Yes')
            {
              // here routing number is combination of routing number and branch code
              if($('#payout_info_payout_country1').val() != 'GB' && $('#payout_info_payout_currency').val() != 'EUR')
              {
                if($('#routing_number').val() == '')
                {
                  $("#stripe_errors").html('Please fill all required fields');               
                  return false;
                }
                if($('#branch_code').val() == '')
                {
                  $("#stripe_errors").html('Please fill all required fields');                
                  return false;
                }

                bankAccountParams.routing_number = $('#routing_number').val()+'-'+$('#branch_code').val();
              }
            }
            else
            {

              if($('#payout_info_payout_country1').val() != 'GB' && $('#payout_info_payout_currency').val() != 'EUR')
              {
                if($('#routing_number').val() == '')
                {
                  $("#stripe_errors").html('Please fill all required fields');                
                  return false;
                }
                bankAccountParams.routing_number = $('#routing_number').val();
              }
            }
          }

          // required field validation --end-- //
          $('#payout_stripe').addClass('loading');
          country = $scope.payout_country;
          Stripe.bankAccount.createToken(bankAccountParams, stripeResponseHandler);


          return false;
        });
    $('.panel-close').click(function() {
      $(this).parent().parent().parent().parent().parent().addClass('hide');
    });

    $('[id$="_flash-container"]').on('click', '.alert-close', function() {
      $(this).parent().parent().html('');
    });

    // response handler function from for create stripe token
    function stripeResponseHandler(status, response) {

      $('#payout_stripe').removeClass('loading');
      if (response.error) {       
        $("#stripe_errors").html("");
        if(response.error.message == "Must have at least one letter"){
          $("#stripe_errors").html('Please fill all required fields');
        }else{
          $("#stripe_errors").html(response.error.message); 
        }

        return false;
      }
      else {
        $("#stripe_errors").html("");
        var token = response['id'];
        $("#stripe_token").val(token); 
        $('#payout_stripe').removeClass('loading');
        $("#payout_stripe").submit();
        return true;
      }
    }
  }]);
app.controller('help', ['$scope', '$http', function($scope, $http) {

 $('.help-nav .navtree-list .navtree-next').click(function() {
  var id = $(this).data('id');
  var name = $(this).data('name');
  $('.help-nav #navtree').addClass('active');    
  $('.help-nav #navtree').removeClass('not-active');
  $('.help-nav .subnav-list li:first-child a').attr('aria-selected', 'false');
  $('.help-nav .subnav-list').append('<li> <a class="subnav-item" href="#" data-node-id="0" aria-selected="true"> ' + name + ' </a> </li>');
  $('.help-nav #navtree-'+id).css({
    'display': 'block'
  });
});

 $('.help-nav .navtree-list .navtree-back').click(function() {
  var id = $(this).data('id');
  var name = $(this).data('name');
  $('.help-nav #navtree').removeClass('active');
  $('.help-nav #navtree').addClass('not-active');
  $('.help-nav .subnav-list li:first-child a').attr('aria-selected', 'true');
  $('.help-nav .subnav-list li').last().remove();
  $('.help-nav #navtree-' + id).css({
    'display': 'none'
  });
});


 $('#help_search').autocomplete({
  source: function(request, response) {
    $.ajax({
      url: APP_URL + "/ajax_help_search",
      type: "GET",
      dataType: "json",
      data: {
        term: request.term
      },
      success: function(data) {
        response(data);
        $(this).removeClass('ui-autocomplete-loading');
      }
    });
  },
  search: function() {
    $(this).addClass('loading');
  },
  open: function() {
    $(this).removeClass('loading');
  }
})
 .autocomplete("instance")._renderItem = function(ul, item) {
  if (item.id != 0) {
    $('#help_search').removeClass('ui-autocomplete-loading');
    return $("<li>")
    .append("<a href='" + APP_URL + "/help/article/" + item.id + "/" + item.question + "' class='article-link article-link-panel link-reset'><div class='hover-item__content'><div class='col-middle-alt article-link-left'><i class='icon icon-light-gray icon-size-2 article-link-icon icon-description'></i></div><div class='col-middle-alt article-link-right'>" + item.value + "</div></div></a>")
    .appendTo(ul);

  } else {
    $('#help_search').removeClass('ui-autocomplete-loading');
    return $("<li style='pointer-events: none;'>")
    .append("<span class='article-link article-link-panel link-reset'><div class='hover-item__content'><div class='col-middle-alt article-link-left'><i class='icon icon-light-gray icon-size-2 article-link-icon icon-description'></i></div><div class='col-middle-alt article-link-right'>" + item.value + "</div></div></span>")
    .appendTo(ul);

  }
};

}]);

$(document).ready(function() {
  function res_menu()
  {
    $('.sub_menu_header').click(function()
    {
      $('.sub_menu_header').toggleClass('open');
    });
  };

  $(document).ready(function(){
    res_menu();
  });
});

$('.pay_close').click(function(){
  $('body').removeClass('new_fix');
});
