$('#input_dob').datepicker({ 'dateFormat': 'dd-mm-yy'});
  var v = $("#add_room_form").validate({
      rules: {
        calendar: { required: true },
        bedrooms: { required: true },
        beds: { required: true },
        bed_type: { required: true },
        bathrooms: { required: true },
        property_type: { required: true },
        room_type: { required: true },
        accommodates: { required: true },
        name: { required: true },
        summary: { required: true },
        country: { required: true },
        address_line_1: { required: true },
        city: { required: true },
        state: { required: true },
        night: { required: true, digits: true },
        currency_code: { required: true },
        'photos[]': { required: { depends: function(element){
          if($('#js-photo-grid li').length == 0){
            return true;
          }
          else{
            return false;
          }
        } } },
        cancel_policy: { required: true },
        user_id: { required: true },
      },
      errorElement: "span",
      errorClass: "text-danger",
    });
   
   $('.frm').hide();
   $('.frm:first').show();

   function step(step)
   {
      $(".frm").hide();
      $("#sf"+step).show();
   }

   function next(step)
   {
    if(v.form())
    {
      if(step != 10)
      {
        $(".frm").hide();
        $("#sf"+(step+1)).show();
      }
      else
      {
        $('#add_room_form').submit();
      }
    }
   }

   function back(step)
   {
    $(".frm").hide();
    $("#sf"+(step-1)).show();
   }

app.controller('rooms_admin', ['$scope', '$http', '$compile', function($scope, $http, $compile) {

initAutocomplete(); // Call Google Autocomplete Initialize Function

// Google Place Autocomplete Code

var autocomplete;

function initAutocomplete()
{
  autocomplete = new google.maps.places.Autocomplete(document.getElementById('address_line_1'));
    autocomplete.addListener('place_changed', fillInAddress);
}

function fillInAddress() 
{
    fetchMapAddress(autocomplete.getPlace());
}

function fetchMapAddress(data)
{
  var componentForm = {
      street_number: 'short_name',
      route: 'long_name',
      locality: 'long_name',
      administrative_area_level_1: 'long_name',
      country: 'short_name',
      postal_code: 'short_name'
  };

    $('#city').val('');
    $('#state').val('');
    $('#country').val('');
    $('#address_line_1').val('');
    $('#address_line_2').val('');
    $('#postal_code').val('');

    var place = data;

    for (var i = 0; i < place.address_components.length; i++) 
    {
      var addressType = place.address_components[i].types[0];
      if (componentForm[addressType]) 
      {
        var val = place.address_components[i][componentForm[addressType]];
      
      if(addressType       == 'street_number')
        $scope.street_number = val;
      if(addressType       == 'route')
        $('#address_line_1').val(val);
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

  var address   = $('#address_line_1').val();
  var latitude  = place.geometry.location.lat();
  var longitude = place.geometry.location.lng();

    if($('#address_line_1').val() == '')
      $('#address_line_1').val($('#city').val());

    if($('#city').val() == '')
      $('#city').val('');
    if($('#state').val() == '')
      $('#state').val('');
    if($('#postal_code').val() == '')
      $('#postal_code').val('');

  $('#latitude').val(latitude);
  $('#longitude').val(longitude);
}   

$( "#username" ).autocomplete({
  source: APP_URL+'/admin/rooms/users_list',
  select: function(event, ui)
  {
    $('#user_id').val(ui.item.id);
  }
});

$(document).on('click', '.month-nav', function()
{
  var month = $(this).attr('data-month');
  var year = $(this).attr('data-year');

  var data_params = {};

  data_params['month'] = month;
  data_params['year'] = year;

  var data = JSON.stringify(data_params);

  /*$('.ui-datepicker-backdrop').removeClass('hide');
  $('.spinner-next-to-month-nav').addClass('loading');*/

  $http.post(APP_URL+'/admin/ajax_calendar/'+$('#room_id').val(), { data:data }).then(function(response) {
    $( "#ajax_container" ).html( $compile(response.data)($scope) );
    /*$('.spinner-next-to-month-nav').removeClass('loading');
    $('.ui-datepicker-backdrop').addClass('hide');*/
    });
  return false;
});

$(document).on('change', '#calendar_dropdown', function()
{
  var year_month = $(this).val();
  var year = year_month.split('-')[0];
  var month = year_month.split('-')[1];
  
  var data_params = {};

  data_params['month'] = month;
  data_params['year'] = year;

  var data = JSON.stringify(data_params);

  $http.post(APP_URL+'/admin/ajax_calendar/'+$('#room_id').val(), { data:data }).then(function(response) {
    $( "#ajax_container" ).html( $compile(response.data)($scope) );
    });
  return false;
});

$(document).on('click', '.delete-photo-btn', function()
{
  var id = $(this).attr('data-photo-id');
  
  if($('[id^="photo_li_"]').size() > 1)
  {
  $http.post(APP_URL+'/admin/delete_photo', { photo_id : id }).then(function(response) 
  {
    if(response.data.success == 'true')
    {
      $('#photo_li_'+id).remove();
    }
  });
  }
  else
  {
    alert('You cannnot delete last photo. Please upload alternate photos and delete this photo.');
  }
});

$(document).on('click', '.featured-photo-btn', function()
{
  var id = $(this).attr('data-featured-id');
  var room_id = $("input[id=room_id]").val();
  //alert(id + "" + room_id); 
  
  $http.post(APP_URL+'/admin/featured_image', { id : room_id ,photo_id  : id}).then(function(response) 
  {
    if(response.data.success == 'true')
    {
      alert('success');
    }
  });
 
});

$(document).on('keyup', '.highlights', function()
{
  var value = $(this).val();
  var id = $(this).attr('data-photo-id');
  $('#saved_message').fadeIn();
  $http.post(APP_URL+'/admin/photo_highlights', { photo_id : id, data : value }).then(function(response)
  {
    $('#saved_message').fadeOut();
  });
});

$(document).on('change', '#additional_guest', function() {
  disableAdditionalGuestCharge();
});
disableAdditionalGuestCharge();
function disableAdditionalGuestCharge() {
  if ($('#additional_guest').val() == "0")
    $('#guests').prop('disabled', true);
  else
    $('#guests').prop('disabled', false);
}
}]);