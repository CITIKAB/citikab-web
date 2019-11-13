$('#confirm-delete').on('show.bs.modal', function(e) {
  $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
  $(".confirm-delete").on('click',function(event) {
    if($(this).attr('disabled')) {
      event.preventDefault();
    }
    $(".confirm-delete").attr("disabled", true);
  });
});
$('#close_recent').on('click', function(e) {
    $(".recent_rides_section").slideToggle();
});
$(document).ready(function(){
  setTimeout(function() {$('#js-currency-select').show()},1000)
  $('#js-currency-select').on('change', function(){
    currency_code = $(this).val();
    $.post(APP_URL+'/company/set_session', {currency: currency_code}).then(function(response){
      location.reload();
    });
  });
})

app.controller('help', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

$scope.change_category = function(value) {
	$http.post(APP_URL+'/admin/ajax_help_subcategory/'+value).then(function(response) {
    	$scope.subcategory = response.data;
    	$timeout(function() { $('#input_subcategory_id').val($('#hidden_subcategory_id').val()); $('#hidden_subcategory_id').val('') }, 10);
    });
};

$timeout(function() { $scope.change_category($scope.category_id); }, 10);
$scope.multiple_editors = function(index) {
     setTimeout(function() {
            $("#editor_"+index).Editor();
            $("#editor_"+index).parent().find('.Editor-editor').html($('#content_'+index).val());
        }, 100);
    }
    $("[name='submit']").click(function(e){
        $scope.content_update();
    });
    // $(document).on('blur', '.Editor-container .Editor-editor', function(){
    //     i = $(this).parent().parent().children('.editors').attr('data-index');
    //     $('#content_'+i).text($('#editor_'+i).Editor("getText"));
    //     $('#content_'+i).valid();
    // });
    $scope.content_update = function() {
        $.each($scope.translations,function(i, val) {
            $('#content_'+i).text($('#editor_'+i).Editor("getText"));
        })
        return  false;
    }
    // var v = $("#admin_page_form").validate({
    //     ignore: '',
    // });
}]);
app.filter('checkKeyValueUsedInStack', ["$filter", function($filter) {
  return function(value, key, stack) {
    var found = $filter('filter')(stack, {locale: value});
    var found_text = $filter('filter')(stack, {key: ''+value}, true);
    return !found.length && !found_text.length;
  };
}])

app.filter('checkActiveTranslation', ["$filter", function($filter) {
  return function(translations, languages) {
    var filtered =[];
    $.each(translations, function(i, translation){
        if(languages.hasOwnProperty(translation.locale))
        {
            filtered.push(translation);
        }
    });
    return filtered;
  };
}])
var currenttime = $('#current_time').val();

var montharray=new Array("January","February","March","April","May","June","July","August","September","October","November","December")
var serverdate=new Date(currenttime)

function padlength(what){
var output=(what.toString().length==1)? "0"+what : what
return output
}

function displaytime(){
serverdate.setSeconds(serverdate.getSeconds()+1)
var datestring=montharray[serverdate.getMonth()]+" "+padlength(serverdate.getDate())+", "+serverdate.getFullYear()
var timestring=padlength(serverdate.getHours())+":"+padlength(serverdate.getMinutes())+":"+padlength(serverdate.getSeconds())
document.getElementById("show_date_time").innerHTML="<b>"+datestring+"</b>"+"&nbsp;<b>"+timestring+"</b>";
}

window.onload=function(){
setInterval("displaytime()", 1000)
}

app.controller('destination_admin', ['$scope', '$http', '$compile', function($scope, $http, $compile) {

initAutocomplete(); // Call Google Autocomplete Initialize Function

// Google Place Autocomplete Code

var autocomplete;

function initAutocomplete()
{
  autocomplete = new google.maps.places.Autocomplete(document.getElementById('input_home_location'));
    autocomplete.addListener('place_changed', fillInAddress);
   
}

function fillInAddress() 
{
    fetchMapAddress(autocomplete.getPlace());
    
}

function fetchMapAddress(data)
{

    var place = data;
   

   $('#input_home_location').val(place.formatted_address);
  var latitude  = place.geometry.location.lat();
  var longitude = place.geometry.location.lng();

  $('#home_latitude').val(latitude);
  $('#home_longitude').val(longitude);
 
}  

initAutocomplete1(); // Call Google Autocomplete Initialize Function

// Google Place Autocomplete Code

var autocomplete1;

function initAutocomplete1()
{
  autocomplete1 = new google.maps.places.Autocomplete(document.getElementById('input_work_location'));
    autocomplete1.addListener('place_changed', fillInAddress1);
   
}

function fillInAddress1() 
{
    fetchMapAddress1(autocomplete1.getPlace());
    
}

function fetchMapAddress1(data)
{
  /*var componentForm = {
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
    $('#destination_address').val('');
    $('#address_line_2').val('');
    $('#postal_code').val('');*/

    var place1 = data;
    /*for (var i = 0; i < place.address_components.length; i++) 
    {
      var addressType = place.address_components[i].types[0];
      if (componentForm[addressType]) 
      {
        var val = place.address_components[i][componentForm[addressType]];
      
      if(addressType       == 'street_number')
        $scope.street_number = val;
      if(addressType       == 'route')
        $('#destination_address').val(val);
      if(addressType       == 'postal_code')
        $('#postal_code').val(val);
      if(addressType       == 'locality')
        $('#city').val(val);
      if(addressType       == 'administrative_area_level_1')
        $('#state').val(val);
      if(addressType       == 'country')
        $('#country').val(val);
      }
    }*/

   $('#input_work_location').val(place1.formatted_address);
  var latitude  = place1.geometry.location.lat();
  var longitude = place1.geometry.location.lng();
 

  $('#work_latitude	').val(latitude);
  $('#work_longitude').val(longitude);
 
  // $('#zoom').val(zoom);
  // $('#bounds').val(bounds);
}  

}]);

app.controller('manage_locations', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

  $scope.bounds = new google.maps.LatLngBounds();
  $scope.selectedShape = null;
  var draw_polygon
  var map
  // Draw polygon on google map
  $scope.addPolygons = function(map) {
    // angular.forEach($scope.formatted_coords, function(coordinates) {
      draw_polygon = new google.maps.Polygon({
        paths: $scope.formatted_coords,
        strokeWeight: 0.3,
        fillOpacity: 0.5,
        editable: false,
        draggable: true,
        fillColor: '#fe2c2c'
      });
      
    // });
    draw_polygon.setMap(map);
    $scope.updateCenter(map,draw_polygon);
    $scope.addEventListeners(draw_polygon);
    $scope.setSelection(draw_polygon);
    map.fitBounds($scope.bounds);
    map.setCenter($scope.map_center);
  }
  $('.form').submit(function(e){
    $scope.coordinates =$scope.getCoordinates(draw_polygon);
    $('.coordinates').val($scope.coordinates);
  })
  // Get the center point of polygon
  $scope.updateCenter = function(map,polygon) {
    var coordinates = polygon.getPath().getArray();

    for (var i = 0; i < coordinates.length; i++) {
      $scope.bounds.extend(coordinates[i]);
    }

    $scope.coordinates.push($scope.getCoordinates(polygon));
    $('.coordinates').val($scope.coordinates);
    $scope.bounds = $scope.bounds;
    $scope.map_center = $scope.bounds.getCenter();
  }

  // Get Formatted Coordinates of given polygon
  $scope.getCoordinates = function(polygon) {
    var polygon_cords = '(';
    for (var i = 0; i < polygon.getPath().getLength(); i++) {
      polygon_cords += polygon.getPath().getAt(i).lat().toFixed(6)+' '+polygon.getPath().getAt(i).lng().toFixed(6)+', ';
    }
    var first_cords = polygon.getPath().getAt(0).lat().toFixed(6)+' '+polygon.getPath().getAt(0).lng().toFixed(6)+'';
    return polygon_cords+first_cords+')';
  }

  // Make Selected Polygon editable
  $scope.setSelection = function(shape) {
    $scope.clearSelection();
    $scope.selectedShape = shape;
    var selected_coordinate = $scope.getCoordinates($scope.selectedShape);
    $scope.cur_index = $.inArray(selected_coordinate, $scope.coordinates);
    shape.setEditable(true);
    $('.remove_location').removeClass('hide');
  }

  // Make Selected Polygon non editable
  $scope.clearSelection = function() {
    if ($scope.selectedShape) {
      $scope.selectedShape.setEditable(false);
      $scope.selectedShape = null;
      $('.remove_location').addClass('hide');
    }
  }

  // Remove Selected Polygon
  $scope.removeSelection = function() {
    if ($scope.selectedShape) {      
      $scope.coordinates.splice($scope.cur_index, 1);
      $('.coordinates').val($scope.coordinates);
      $scope.selectedShape.setMap(null);
      $scope.selectedShape = null;
      $('.remove_location').addClass('hide');
    }
  }

  // Register Click Event When Click any polygon
  $scope.addEventListeners = function(shape) {
    // Add Click event to shape for select shape and edit
    google.maps.event.addListener(shape, 'click', function() {
      $scope.setSelection(shape);
    });

    // Register set_at event to all paths to listen user change points to another
    shape.getPaths().forEach(function(path, index){
      google.maps.event.addListener(path, 'set_at', function(){
        $scope.coordinates[$scope.cur_index] =$scope.getCoordinates(shape);
        $('.coordinates').val($scope.coordinates);
      });
    });

    // Register Dragend event to update coordinates after move to new position
    google.maps.event.addListener(shape, 'dragend', function() {
      $scope.coordinates[$scope.cur_index] =$scope.getCoordinates(shape);
      $('.coordinates').val($scope.coordinates);
    });
  }

  $scope.RemoveShapeControl = function(controlDiv, map)
  {
    // Set CSS for the control border.
    var controlUI = document.createElement('div');
    controlUI.className = "remove_location hide";
    controlUI.style.backgroundColor = '#fff';
    controlUI.style.borderRadius = '3px';
    controlUI.style.boxShadow = '0 2px 6px rgba(0,0,0,.3)';
    controlUI.style.cursor = 'pointer';
    controlUI.style.textAlign = 'center';
    controlUI.title = 'Click to remove the Location';
    controlDiv.appendChild(controlUI);

    // Set CSS for the control interior.
    var controlText = document.createElement('div');
    controlText.style.color = 'rgb(25,25,25)';
    controlText.style.fontFamily = 'Roboto,Arial,sans-serif';
    controlText.style.fontSize = '14px';
    controlText.style.lineHeight = '5px';
    controlText.style.padding = '5px';
    controlText.style.margin = '5px';
    controlText.innerHTML = 'Remove Location';
    controlUI.appendChild(controlText);

    // Setup the click event listeners: simply set the map to Chicago.
    controlUI.addEventListener('click', function() {
        $scope.removeSelection();
    });

  }

  function initMap() {
    var mapCanvas = document.getElementById('map');
    var input = document.getElementById('pac-input');
    var mapOptions = {
      zoom: 2,
      minZoom: 1,
      zoomControl: true,
      fullscreenControl: false,
      center:{lat: 0, lng: 0},
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var drawingControlOptions = {
      position: google.maps.ControlPosition.TOP_CENTER,
      drawingModes: ['polygon']
    };
    var polygonOptions = {
      strokeWeight: 0,
      fillOpacity: 0.45,
      editable: true,
      draggable: true,
      fillColor: '#fe2c2c'
    };
    var polyLineOptions = {
      strokeWeight: 0,
      fillOpacity: 0.45,
      editable: true,
      fillColor: '#fe2c2c'
    };
    var markers = [];

    if(!mapCanvas) {
        return false;
    }
    
    var map = new google.maps.Map(mapCanvas,mapOptions);

    // Create the DIV to hold the control to remove selected polygon
    var removeControlDiv = document.createElement('div');
    var removeControl = $scope.RemoveShapeControl(removeControlDiv, map);
    removeControlDiv.index = 1;
    map.controls[google.maps.ControlPosition.TOP_CENTER].push(removeControlDiv);

    var drawingManager = new google.maps.drawing.DrawingManager({
      drawingMode: null,
      drawingControl: true,
      drawingControlOptions: drawingControlOptions,
      markerOptions: {
        draggable: true
      },
      polygonOptions: polygonOptions,
      polyLineOptions: polyLineOptions
    });
    drawingManager.setMap(map);

    // Create the search box and link it to the UI element.
    var searchBox = new google.maps.places.SearchBox(input);
    map.controls[google.maps.ControlPosition.TOP_RIGHT].push(input);

    // Bias the SearchBox results towards current map's viewport.
    map.addListener('bounds_changed', function() {
      searchBox.setBounds(map.getBounds());
    });

    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener('places_changed', function() {
      var places = searchBox.getPlaces();

      if (places.length == 0) {
        return;
      }

      // Clear out the old markers.
      markers.forEach(function(marker) {
        marker.setMap(null);
      });

      // For each place, get the icon, name and location.
      var bounds = new google.maps.LatLngBounds();
      places.forEach(function(place) {
        if (!place.geometry) {
          console.log("Returned place contains no geometry");
          return;
        }

        if (place.geometry.viewport) {
          // Only geocodes have viewport.
          bounds.union(place.geometry.viewport);
        }
        else {
          bounds.extend(place.geometry.location);
        }
      });
      map.fitBounds(bounds);
    });

    // Load already drawed polygons to map
    google.maps.event.addListenerOnce(map, 'tilesloaded', function(event) {
      $('#pac-input').removeClass('hide');
      if($scope.formatted_coords.length > 0 ) {
        setTimeout(function(){
          $('.remove_location').removeClass('hide');
        },1000)
        $scope.addPolygons(map);
      }
    });
    
    // Remove Polygon Selection while click outside
    google.maps.event.addListener(map, 'click', function(event) {
      $scope.clearSelection();
    });

    google.maps.event.addListener(drawingManager, 'drawingmode_changed', function(event) {
      if($scope.coordinates.length > 0){
        drawingManager.setDrawingMode(null);
        return;
      }
    });

    google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {

      var coordinates = $scope.getCoordinates(event.overlay);
      $scope.coordinates.push(coordinates);
      $('.coordinates').val($scope.coordinates);

      // Add an event listener that selects the newly-drawn shape when the user click on it.
      draw_polygon = event.overlay;
      $scope.addEventListeners(draw_polygon);
      $scope.setSelection(draw_polygon);

      // Disable Drawing mode after Complete any overlay
      drawingManager.setDrawingMode(null);

    });
  }

  google.maps.event.addDomListener(window, "load", initMap);

}]);


app.controller('manage_peak_fare', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {
  $scope.disabled_times = {};
  $scope.selected_times = {};

  // Add New Peak Fare or Night Fare
  $scope.add_price_rule = function(type) {
    if(type == 'peak')
    {
      new_period = $scope.peak_fare_details.length;
      $scope.peak_fare_details.push({'start_time' : '','end_time' : '','price' : ''});
    }
  }

  // Remove Existing Peak Fare or Night Fare
  $scope.remove_price_rule = function(type, index,day) {
    if(type == 'peak') {
      fare_detail =$scope.peak_fare_details[index];
      $scope.removed_fares += ','+fare_detail.id;
      $scope.peak_fare_details.splice(index, 1);
      if(typeof $scope.selected_times[day] != 'undefined'){
        delete $scope.selected_times[day][index];
      }

      if(typeof $scope.disabled_times[index] != 'undefined'){
        delete $scope.disabled_times[index];
      }
      // Remove the selected time value for selected index and update other selected time values
      $scope.updateSelectedTimeKeys();
    }
  };

  // Convert Given time to moment time object
  $scope.convertToTime = function(time) {
    return moment("2001-01-01 "+time,"YYYY-MM-DD HH:mm:ss")
  };

  // Re arrange Selected Time keys
  $scope.updateSelectedTimeKeys = function() {
    $scope.selected_times = {};
    $('.peak_fare_day_details').each(function() {
        var index       = $(this).data('index');
        var day         = $scope.peak_fare_details[index].day;
        var start_time  = $scope.peak_fare_details[index].start_time;
        var end_time    = $scope.peak_fare_details[index].end_time;

        if($scope.selected_times[day] == undefined) {
          $scope.selected_times[day] = {}
        }

        $scope.selected_times[day][index] = [start_time,end_time];
    });
  };

  // Update Time Options after Choose
  $scope.update_time = function(index,day) {
    if(typeof $scope.peak_fare_details[index] == 'undefined')
      return;
    var start_time = $scope.peak_fare_details[index].start_time;
    var end_time = $scope.peak_fare_details[index].end_time;
    var day = day;
    if(typeof start_time != 'undefined' && typeof end_time != 'undefined' && typeof day != 'undefined' && start_time != '' && end_time != '') {
      if(typeof $scope.selected_times[day] == 'undefined'){
        $scope.selected_times[day] = {} 
      }
      // validate time after select any date
      var chck_between_time = $scope.isDisabled(index,day,start_time,end_time);
      if(start_time <= end_time && !chck_between_time)
        $scope.selected_times[day][index] = [start_time,end_time];
    }
  };

  // Disable Day if all times are selected within that day
  $scope.ifDayDisabled = function(index,day) {
    index = index+''; // Convert to String
    $scope.disabled_days = $scope.getTimesSelected(day);
    if(typeof $scope.disabled_days == 'undefined') {
      return false;
    }
    else if (typeof $scope.disabled_days[day] == 'undefined') {
      return false;
    }
    else if($.inArray(day, $scope.disabled_days[day]['disable_day']) != -1 && $.inArray(index, $scope.disabled_days[day]['added_days']) == -1) {
      return true;
    }
  };

  // returns Selected times for given day
  $scope.getTimesSelected = function(day) {
    if(typeof $scope.selected_times[day] == 'undefined')
      return;
    disabled_days = {};
    var all_times = [];
    var added_days = [];

    $.each($scope.selected_times[day],function(key, value) {
      added_days.push(key);
      all_times =all_times.concat($scope.generate_time(value[0],value[1]));
    });

    if(all_times.length == 24) {
      disabled_days[day] = {};
      disabled_days[day]['added_days'] = added_days;
      disabled_days[day]['disable_day'] = day;
    }
    return disabled_days;
  };

  // Check whether current time already selected or not
  $scope.isDisabled = function(index,day,start_time,end_time) {
    var select_except_key = $scope.selected_times[day];
    // Convert time string to moment time object
    var selected_start = $scope.convertToTime(start_time);
    var selected_end = $scope.convertToTime(end_time);

    if(start_time != '' && end_time != '' && selected_start >= selected_end){
      $('#Peak_fare_error_'+index).removeClass('hide');
      return;
    }

    if(typeof select_except_key != 'undefined'){
      // get the all other dates except current day
      var tempArr = [];
      $.each(select_except_key,function(i,v){
        tempArr[i] = v;
      });
      select_except_key =  tempArr;
      select_except_key.splice(index, 1);
      between_time = false;

      if(select_except_key.length > 0 ) {
        $.each(select_except_key,function(key, value) {
          if(typeof value == 'undefined')
            return;
          var start = $scope.convertToTime(value[0]);
          var end = $scope.convertToTime(value[1]);
          // Check other dates within selected range
          if(selected_start.isBetween(start,end) || selected_end.isBetween(start,end) || (selected_start <= start && selected_end >= end)) {
            between_time = true;
          }

        });
      }

      // Display or remove error rule
      if(between_time) {
        $('#Peak_fare_error_'+index).removeClass('hide');
        return true;
      }
      else {
        $('#Peak_fare_error_'+index).addClass('hide');
        return false;
      }
    }
  }

  // Prevent submit form when select any of invalid times
  $scope.disableButton = function($event) {
    var error_length = $('.peak_fare_error:not(.hide)').length;
    if(error_length > 0)
      $event.preventDefault();
  }

  // Get all times between two times 
  $scope.generate_time = function(start_time,end_time)
  {
    var start = $scope.convertToTime(start_time);
    var end = $scope.convertToTime(end_time);
    var times = [];

    while(start <= end){
      times.push(start.format('HH:mm:ss'));
      start.add(1, 'hours');
    }

    return times;
  }

  // Remove selected time when change the day from dropdown
  $scope.update_day = function(index,day) {
    var old_day = $('#peak_fare_day_'+index).attr('data-old_day');
    if(typeof $scope.selected_times[old_day] != 'undefined'){
      delete $scope.selected_times[old_day][index];
      $scope.updateSelectedTimeKeys();
    }
    if(typeof $scope.disabled_times[index] != 'undefined'){
      delete $scope.disabled_times[index];
    }
    // Update time after change day
    $scope.update_time(index,day);
  }

  // Disable Select box options based on selected day
  $scope.checkIfDisabled = function(index,day,time,type) {
    var select_except_key = $scope.selected_times[day];
    var cur_time = $scope.convertToTime(time);

    if(typeof select_except_key != 'undefined'){
      var tempArr = [];
      $.each(select_except_key,function(i,v){
        tempArr[i] = v;
      });
      select_except_key =  tempArr;
      select_except_key.splice(index, 1);

      if(select_except_key.length > 0 ) {
        $scope.disabled_times[index] = [];
        $.each(select_except_key,function(key, value) {
          if(typeof value != 'undefined' && value[0] != "") {
            var start = $scope.convertToTime(value[0]);
            var end = $scope.convertToTime(value[1]);
            if(type == 'end_time') {
              var check = ( cur_time.isBetween(start,end) || cur_time.isSame(end) );
            }
            else {
              var check = ( cur_time.isBetween(start,end) || cur_time.isSame(start) );
            }
            if(check){
              if(typeof $scope.disabled_times[index] == 'undefined')
                $scope.disabled_times[index] = [time];
              else if($.inArray(time, $scope.disabled_times[index])== -1)
                $scope.disabled_times[index].push(time);
            }
          }
        });
      }
    }

    return ($.inArray(time, $scope.disabled_times[index]) != -1);
  }

}]);

app.controller('email_settings', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

  $scope.change_driver = function() {
    if($scope.email_driver == 'mailgun') {
      $("#input_domain").val($scope.saved_domain);
      $("#input_secret").val($scope.saved_secret);
    }
    else {
      $('#input_username').val($scope.smtp_username);
      $('#input_password').val($scope.smtp_password);
    }
  }

}]);
app.controller('category_language', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {

/*$scope.add_catgory = function(){
      $scope.help_category.push({'name' : ''});
}
$scope.remove_category = function(index){
    alert(index);
     $scope.help_category.splice(index, 1);    
}*/


}]);
app.controller('later_booking', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {
 $("#manual_booking_cancel").validate({
    rules: {
      cancel_reason: { required: true },
      errorElement: "span",
      errorClass: "text-danger",
    }
  });
  $(document).on("click",'.cancel_button', function(){
    $scope.manual_booking_cancel_id = $(this).attr('schedule_id')
    $('.cancel_by').html($(this).attr('cancel_by'))
    $('.cancel_reason').html($(this).attr('cancel_reason'))
    $(".cancel_button").removeAttr("id");
    $(this).attr('id','clicked')
    $('#input_cancel_reason').val('')
  })
  $("#manual_booking_cancel").submit(function(){
    if ($('#input_cancel_reason').val() != '') {
      $http.post(REQUEST_URL+'/manual_booking/cancel',{ id: $scope.manual_booking_cancel_id, reason: $('#input_cancel_reason').val() }).then(function(response) {
        if (response.data==1) {
          $("#clicked").attr("data-target","#cancel_reason_popup");
          $('#clicked').html('Cancel Reason')
          $('.cancel_'+$scope.manual_booking_cancel_id).html('Cancelled by Admin')
          $('.edit_'+$scope.manual_booking_cancel_id).hide()
          $('#clicked').attr('cancel_reason',$('#input_cancel_reason').val())
          $('#clicked').attr('cancel_by','Admin')
          $('.modal.in').modal('hide') 
        }
      });
    }
    return false;
  });
  $(document).on("click",'#immediate_request',function(){
    schedule_id = $(this).attr('schedule_id');
    $('.immediate_request_'+schedule_id).html('loading...')
    // loading()
    $(this).addClass('immediate_request_active_'+schedule_id);
    $(this).hide();
    $http.post(REQUEST_URL+'/immediate_request',{ id: schedule_id }).then(function(response) {
      response=response.data;
      if (response.status_code==1) {
        // $('.immediate_request_'+schedule_id).removeClass('loading')
        $('.immediate_request_'+schedule_id).html(response.status_message)
        if (response.status_message == 'Car Not Found') {
          $('.immediate_request_active_'+schedule_id).show();
          $('.immediate_request_active_'+schedule_id).removeClass('immediate_request_active_'+schedule_id);
        }
      }else{
        location.reload()
      }
    });
    return false;
  });
  /*function loading(){
    var originalText = 'Loading', i  = 0;
    $(".loading").html("Loading");
    setInterval(function() {
      $(".loading").append(".");
      i++;
      if(i == 4){
        $(".loading").html(originalText);
        i = 0;
      }
    }, 500);
  }*/

}]);

app.controller('company_management', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {
  $(document).on("click",'.delete_button', function(){
    $scope.company_id = $(this).attr('company_id')
    href = $('#delete_link').attr('href')
    $('#delete_link').attr('href',href+$scope.company_id)
  })
}]);

app.controller('driver_management', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {
  if ($('#input_company_name').val()==1) {
    $('.bank_detail').hide()
  }
  $('#input_company_name').change(function(){
    $scope.company_name = $(this).val()
    if ($(this).val()==1) {
      $('.bank_detail').hide()
    }else{
      $('.bank_detail').show()
    }
  })
}]);

app.controller('vehicle_management', ['$scope', '$http', '$compile', '$timeout', function($scope, $http, $compile, $timeout) {
  $scope.get_driver= function(){
    if ($scope.company_name=='' || typeof $scope.company_name === 'undefined') {
      $scope.drivers = []
    }else{
      $('.loading').show()
      $('#input_driver_name').hide()
      $http.post(COMPANY_ADMIN_URL+'/manage_vehicle/'+$scope.company_name+'/get_driver', {vehicle_id: $scope.vehicle_id}).then(function(response) {
        response=response.data;
        if (response.status_code==1) {
          $scope.drivers = response.drivers
          if (response.drivers.length<=0) {
            $('#driver-error').html('No drivers found')
          }else{
            $('#driver-error').html('')
          }
        }
        $('.loading').hide()
        $('#input_driver_name').show()
      });
    }
  }
  var v = $("#vehicle_form").validate({
    rules: {
      company_name: { required: true },
      driver_name: { required: true },
      status: { required: true },
      vehicle_id: { required: true },
      vehicle_name: { required: true },
      vehicle_number: { required: true },
      insurance: { 
        required: { 
          depends: function(element){
            if($('#insurance_img').length<=0){
              return true;
            }
            else{
              return false;
            }
          } 
        } ,
        extension:"png|jpg|jpeg"
      },
      rc: { 
        required: { 
          depends: function(element){
            if($('#rc_img').length<=0){
              return true;
            }
            else{
              return false;
            }
          } 
        } ,
        extension:"png|jpg|jpeg"
      },
      permit: { 
        required: { 
          depends: function(element){
            if($('#permit_img').length<=0){
              return true;
            }
            else{
              return false;
            }
          } 
        } ,
        extension:"png|jpg|jpeg"
      },
    },
    messages: {
      auto_assign_status : {
        required : 'This field is required if no driver assigned.'
      },
    },
    errorElement: "span",
    errorClass: "text-danger",
     errorPlacement: function( label, element ) {
        if(element.attr( "data-error-placement" ) === "container" ){
            container = element.attr('data-error-container');
            $(container).append(label);
        } else {
            label.insertAfter( element ); 
        }
      },
  });
  $.validator.addMethod("extension", function(value, element, param) {
    param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g";
    return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
  }, $.validator.format("Please upload the images like JPG,JPEG,PNG File Only."));
}]);