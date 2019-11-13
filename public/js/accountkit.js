app.controller('facebook_account_kit', ['$scope', '$http','fileUploadService', function($scope, $http,fileUploadService) {


  $scope.selectFile = function(){
   $("#file").click();
}
$scope.select_image = function()
{
  $("#file").click();
}
$scope.fileNameChanged = function(element)
{
  files = element.files; 
    if(files)
    {

      file = files[0];
      if(file)
      {
        $('.profile_update-loader').addClass('loading');
        url = APP_URL+'/'+'profile_upload';
        upload = fileUploadService.uploadFileToUrl(file, url);
        upload.then(
          function(response){
            if(response.success == 'true')
            {
              $('.profile_picture').attr('src',response.profile_url);
              $('.flash-container').html('<div class="alert alert-success text-center col-ssm-12" style="background: #2ec5e1 !important;border-color: #2ec5e1 !important;color: #fff !important;" >' + response.status_message + '</div>');
              $(".flash-container").fadeIn(3000);
              $(".flash-container").fadeOut(3000);
              $('.profile_update-loader').removeClass('loading');
              
            }
            else
            {
              $('.flash-container').html('<div class="alert alert-danger text-center col-ssm-12" >' + response.status_message + '</div>');
              $(".flash-container").fadeIn(3000);
              $(".flash-container").fadeOut(3000);
              $('.profile_update-loader').removeClass('loading');
            }
            
          }
        );
      }
    }
}


function loginCallback(response) {

  if (response.status === "PARTIALLY_AUTHENTICATED") {
    document.getElementById('code').value = response.code;
    document.getElementById('_token').value = response.state;
    document.getElementById('submit-btn').setAttribute("ng-click", "");
    document.getElementById('submit-btn').setAttribute("type", "submit");

    document.getElementById('form').submit();
  }

  else if (response.status === "NOT_AUTHENTICATED") {
      // handle authentication failure
      // alert('You are not Authenticated');
  }
  else if (response.status === "BAD_PARAMS") {
    // handle bad parameters
    alert('wrong inputs');
  }
}



$scope.showPopup = function() {
  url = $('#form').attr('action')
  if (url.includes('update_profile')) {
    $('.mobile-text-danger').hide()
    document.getElementById('form').submit();
    // smsLogin();
  }else{
    $('.text-danger').hide()
    $('#request_type').val('validation')
    $.post(url,$('#form').serialize(),function(data){
      data = $.parseJSON(data)
      if (data!='') {
        $.each(data, function( index, value ) {
          $('.'+index+'_error').show()
          $('.'+index+'_error').html(value[0])
        });
      }else{
        $('#request_type').val('submit')
        $('.mobile-text-danger').hide()
        document.getElementById('form').submit();
        /*if ($scope.old_country_code != $('#mobile_country').val() ||$scope.old_mobile_number ==''||  $scope.old_mobile_number != $('#mobile').val()) {
          smsLogin();
        }else{
          document.getElementById('form').submit();
        }*/
      }
    });
  }
}


// phone form submission handler
function smsLogin() {

  var countryCode = "+"+document.getElementById('mobile_country').value;
  var phoneNumber = document.getElementById('mobile').value;
  AccountKit.login('PHONE', {countryCode: countryCode, phoneNumber: phoneNumber},loginCallback
  );

$("._6x33").hide();

}


// // email form submission handler
// function emailLogin() {
//   var emailAddress = document.getElementById("email").value;
//   AccountKit.login('EMAIL', {emailAddress: emailAddress}, loginCallback);
// }

// Auto Complete for driver location
// Call Google Autocomplete Initialize Function
initAutocomplete();

// Google Place Autocomplete Code
$scope.location_found = false;
$scope.autocomplete_used = false;
var autocomplete;


function initAutocomplete()
{
  autocomplete = new google.maps.places.Autocomplete(document.getElementById('home_address'));
  autocomplete.addListener('place_changed', fillInAddress);
}

function fillInAddress() 
{
  $scope.autocomplete_used = true;
  fetchMapAddress(autocomplete.getPlace());
}

function fetchMapAddress(data)
{ 
  if(data['types'] == 'street_address')
    $scope.location_found = true;
    var componentForm = {
      street_number: 'short_name',
      route: 'long_name',
      sublocality_level_1: 'long_name',
      sublocality: 'long_name',
      locality: 'long_name',
      administrative_area_level_1: 'long_name',
      country: 'short_name',
      postal_code: 'short_name'
    };

    $('#address_line1').val('');
    $('#address_line2').val('');
    $('#city').val('');
    $('#state').val('');    
    $('#postal_code').val('');

    var place = data;
    $scope.street_number = '';
    for (var i = 0; i < place.address_components.length; i++) 
    {
      var addressType = place.address_components[i].types[0];
      if (componentForm[addressType]) 
      {
        var val = place.address_components[i][componentForm[addressType]];        
      if(addressType       == 'street_number')
        $scope.street_number = val;
      if(addressType       == 'route')
        var street_address = $scope.street_number+' '+val;
        $('#address_line1').val($.trim(street_address));
      if(addressType == 'sublocality_level_1')
        $('#address_line2').val(val);
      if(addressType       == 'postal_code')
        $('#postal_code').val(val);
      if(addressType       == 'locality')
        $('#city').val(val);
      if(addressType       == 'administrative_area_level_1')
        $('#state').val(val);
      if(addressType       == 'country')
        $('#country').val(val);
      }
    }
    
    var latitude  = place.geometry.location.lat();
    var longitude = place.geometry.location.lng();

    $('#latitude').val(latitude);
    $('#longitude').val(longitude);
}

$('#mobile_country').click(function() {
  $('#select-title-stage').text($(this).find(':selected').attr('data-value'));    
});


}]);