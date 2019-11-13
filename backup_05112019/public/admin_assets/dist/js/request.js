function initMap() {
  var mapCanvas = document.getElementById('map');
  
  if(!mapCanvas){
      return false;
  }
  var mapOptions = {
     zoom: 2,
     minZoom: 1,
     zoomControl: true,
     center:{lat: 0, lng: 0},
  }
  var map = new google.maps.Map(mapCanvas, mapOptions);

  var marker = new google.maps.Marker({
      map: map,
      icon: APP_URL+'/images/point_circle.png',
      scaledSize: new google.maps.Size(23, 30),
      anchorPoint: new google.maps.Point(0, -29)
  });

   var markerSecond = new google.maps.Marker({
      map: map,
      icon: APP_URL+'/images/point.png',
      scaledSize: new google.maps.Size(23, 30),
      anchorPoint: new google.maps.Point(0, -29)
  });

  var pickup_latitude  = parseFloat($('#pickup_latitude').val());
  var pickup_longitude = parseFloat($('#pickup_longitude').val());
  var drop_latitude    = parseFloat($('#drop_latitude').val());
  var drop_longitude   = parseFloat($('#drop_longitude').val());

  var bounds = new google.maps.LatLngBounds();

        start = new google.maps.LatLng(pickup_latitude,pickup_longitude);
        end = new google.maps.LatLng(drop_latitude,drop_longitude);

        marker.setPosition(start);
        markerSecond.setPosition(end);

        var directionsService = new google.maps.DirectionsService;
        var directionsDisplay = new google.maps.DirectionsRenderer({suppressMarkers: true, preserveViewport: true,polylineOptions: { strokeColor: "#1c1c1c" }});
        directionsDisplay.setMap(map);

        directionsService.route({
            origin: start,
            destination: end,
            travelMode: google.maps.TravelMode.DRIVING
        }, function(result, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                directionsDisplay.setDirections(result);

                marker.setPosition(result.routes[0].legs[0].start_location);
                markerSecond.setPosition(result.routes[0].legs[0].end_location);
            }
        });

        bounds.extend(marker.getPosition());
        bounds.extend(markerSecond.getPosition());
        map.fitBounds(bounds);
}