<?php
  function distance($lat1, $lon1, $lat2, $lon2, $unit) {

    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);
  
    if ($unit == "K") {
        return ($miles * 1.609344);
    } else if ($unit == "N") {
        return ($miles * 0.8684);
    } else {
        return $miles;
    }
  }

  function map_calculator($from, $to) {    
    $from = str_replace( array(' '), '', $from);
    $to = str_replace( array(' '), '', $to);

    $from_locality = "";
    $from_region = "";
    $from_country = "";
    $from_data = [$from_locality, $from_region, $from_country];
    $k = 0;
    $commas_from_count = 0;
    for($j = 0; $j <= strlen($from);$j++){
      if($from[$i] == ","){
        $commas_from_count += 1;
      }
    }
    for($j = 0; $j <= strlen($to);$j++){
      if($to[$i] == ","){
        $commas_to_count += 1;
      }
    }
    if($commas_from_count > 0){
      for($i = 0; $i <= strlen($from);$i++){
        if($from[$i] == ","){
          $from_data[$k];
          $k += 1;
        }
        $from_data[$k] .= $from[$i];
      }
    }

    $to_locality = "";
    $to_region = "";
    $to_country = "";
    $to_data = [$to_locality, $to_region, $to_country];
    $k = 0;
    if($commas_to_count > 0){
      for($i = 0; $i <= strlen($to);$i++){
        if($to[$i] == ","){
          $to_data[$k];
          $k += 1;
        }
        $to_data[$k] .= $to[$i];
      }
    }

    // print_r($from_data[0] . '|' . $from_data[1] . '|' . $from_data[2]);
    if($commas_from_count > 0){
      $from_coords_url = 'https://api.openrouteservice.org/geocode/search/structured?api_key=5b3ce3597851110001cf6248a1d91314224a42598022ddf99014c333&locality=' . $from_data[0] . '&region=' . $from_data[1] . '&country=' . $from_data[2];
    }
    else{
      $from_coords_url = 'https://api.openrouteservice.org/geocode/search?api_key=5b3ce3597851110001cf6248a1d91314224a42598022ddf99014c333&text=' . $from;
    }
    $from_coords_curl = curl_init($from_coords_url); 
    curl_setopt($from_coords_curl, CURLOPT_RETURNTRANSFER, true);
    $from_coords_response = curl_exec($from_coords_curl); 
    curl_close($from_coords_curl);
    $from_coords_response = json_decode($from_coords_response, true);
    $from_coords = [$from_coords_response['features'][0]['geometry']['coordinates'][1],$from_coords_response['features'][0]['geometry']['coordinates'][0]];
    
    if($commas_to_count > 0){
      $to_coords_url = 'https://api.openrouteservice.org/geocode/search/structured?api_key=5b3ce3597851110001cf6248a1d91314224a42598022ddf99014c333&locality=' . $to_data[0] . '&region=' . $to_data[1] . '&country=' . $to_data[2];
    }
    else{
      $to_coords_url = 'https://api.openrouteservice.org/geocode/search?api_key=5b3ce3597851110001cf6248a1d91314224a42598022ddf99014c333&text=' . $to;
    }
    $to_coords_curl = curl_init($to_coords_url); 
    curl_setopt($to_coords_curl, CURLOPT_RETURNTRANSFER, true);
    $to_coords_response = curl_exec($to_coords_curl); 
    curl_close($to_coords_curl);
    $to_coords_response = json_decode($to_coords_response, true);
    $to_coords = [$to_coords_response['features'][0]['geometry']['coordinates'][1],$to_coords_response['features'][0]['geometry']['coordinates'][0]];
    
    $km_distance = distance($from_coords[0],$from_coords[1],$to_coords[0],$to_coords[1], "K");
    $km_distance = number_format($km_distance , 0);
    $km_distance = str_replace( array(' ',','), '', $km_distance);
    $miles_distance = distance($from_coords[0],$from_coords[1],$to_coords[0],$to_coords[1], "M");
    $miles_distance = number_format($miles_distance , 0);
    $miles_distance = str_replace( array(' ',','), '', $miles_distance);
    $coords = [$from_coords, $to_coords, $km_distance, $miles_distance];

    return $coords;
  }
?>

<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
      <style>
          #container {
    height: 100%;
    display: flex;
  }
  #map {
    flex-basis: 0;
    flex-grow: 4;
    width: 200px;
    height: 350px;
  }

  #sidebar {
    flex-direction: column;
  }  
  .submit_button{
    align-self: end;
    width:30%;
  }
  @media(max-width:630px){
    .form_container{
      display:flex;
      flex-direction:column;
      justify-content:center;
      align-items:center;
    }
    .submit_button{
      align-self: center;
      width: 100%;
      margin-top: 15px;
    }
    label{
      margin-top: 15px;
    }
  }
      </style>
      <div id="container">
        <div id="map"></div>
      </div>
      <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCK6jMzXAjEiSUmXkN01SIPDG8x3d9Hsr8&libraries=places,geometry&callback=initMap&v=weekly"
        defer
      ></script>

      <form id="formId" action="/destination-calculator/" method="post" class="form_container" style="    
          display: flex;
          align-items: center;
          justify-content: center;
          background-color: #e4fdbb; 
          border: 1px solid grey;
          border-radius: 10px; 
          width: 100%;
          padding: 15px;
          margin-top:20px;">
          <div style="display: flex;flex-direction: column;margin-right: 20px;">
              <label for="from" style="font-size: 18px;font-weight: 500;margin-bottom: 5px;">
                  Distance from
              </label>
              <input name="from" id="autocomplete1" type="text" style="width: 100%;height: 30px;border:1px solid grey; border-radius: 5px;">
          </div>
          <div style="display: flex;flex-direction: column;margin-right: 20px;">
              <label for="to" style="font-size: 18px;font-weight: 500;margin-bottom: 5px;">
                  Distance to
              </label>
              <input name="to" id="autocomplete2" type="text" style="width: 100%;height: 30px;border:1px solid grey; border-radius: 5px;">
          </div>
          <button class="submit_button" type="submit" name='submit' style="background-color: #1e89d7;box-shadow: none;border: none;outline: inherit;color: white;padding: 6px 10px;border-radius: 5px;font-size: 16px;">Measure Distance</button>
      </form>
      <p>Please fill fields:city, region, country separated with comma.</br> Example: Washington, DC, USA </br>(if there are no region just input space example "Paris, ,France").</p>

      <?php 
          if($_SERVER["REQUEST_METHOD"] == "POST"){
              $from = $_POST['from'];
              $to = $_POST['to'];

              $coords = map_calculator($from, $to);
              $from_coords = $coords[0];
              $to_coords = $coords[1];
              $km_distance = $coords[2];
              $miles_distance = $coords[3];   
              $fly_time = number_format(($km_distance / 800),0);
              if($fly_time < 1){
                $fly_time = 1;
              }
      ?>
                  <div class="form_container" style="    
          display: flex;
          align-items: center;
          justify-content: center;
          background-color: #e6edf3; 
          border: 1px solid grey;
          border-radius: 10px; 
          width: 100%;
          padding: 15px;">
          <div style="display: flex;flex-direction: column;margin-right: 20px;">
              <label style="font-size: 18px;font-weight: 500;margin-bottom: 5px;">
                  Distance in km
              </label>
              <div style="width: 100%;height: 30px;border:1px solid grey; border-radius: 5px;background-color:white;padding-left:5px;"> 
                <p id="fly_distance"><?= $km_distance . ' km' ?> </p>
              </div>
          </div>
          <div style="display: flex;flex-direction: column;margin-right: 20px;">
              <label style="font-size: 18px;font-weight: 500;margin-bottom: 5px;">
                  Distance in miles
              </label>
              <div style="width: 100%;height: 30px;border:1px solid grey; border-radius: 5px;background-color:white;padding-left:5px;">
                <p><?= $miles_distance . " miles" ?> </p>
              </div>
          </div>
          <div style="display: flex;flex-direction: column;margin-right: 20px;">
              <label style="font-size: 18px;font-weight: 500;margin-bottom: 5px;">
                  Flight duration
              </label>
              <div style="width: 100%;height: 30px;border:1px solid grey; border-radius: 5px;background-color:white;padding-left:5px;">
                <p id="flight_duration"><?= $fly_time . " h" ?></p>
              </div>
          </div>
      </div>
        <p id="from_lat" style="display:none;"><?= $from_coords[0] ?></p>
        <p id="from_lng" style="display:none;"><?= $from_coords[1] ?></p>
        <p id="to_lat" style="display:none;"><?= $to_coords[0] ?> </p>
        <p id="to_lng" style="display:none;"><?= $to_coords[1] ?></p>
      <?php
          }
          else{
              $from = CFS()->get('from');
              $to = CFS()->get('to');

              $coords = map_calculator($from, $to);
              $from_coords = $coords[0];
              $to_coords = $coords[1];
              $km_distance = $coords[2];
              $miles_distance = $coords[3];   
              $fly_time = number_format(($km_distance / 800));
              if($fly_time < 1){
                $fly_time = 1;
              }
            ?>
          <div class="form_container" style="    
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #e6edf3; 
            border: 1px solid grey;
            border-radius: 10px; 
            width: 100%;
            padding: 15px;">
          <div style="display: flex;flex-direction: column;margin-right: 20px;">
              <label style="font-size: 18px;font-weight: 500;margin-bottom: 5px;">
                  Distance in km
              </label>
              <div style="width: 100%;height: 30px;border:1px solid grey; border-radius: 5px;background-color:white;padding-left:5px;"> 
                <p id="fly_distance"><?= $km_distance . ' km' ?> </p>
              </div>
          </div>
          <div style="display: flex;flex-direction: column;margin-right: 20px;">
              <label style="font-size: 18px;font-weight: 500;margin-bottom: 5px;">
                  Distance in miles
              </label>
              <div style="width: 100%;height: 30px;border:1px solid grey; border-radius: 5px;background-color:white;padding-left:5px;">
                <p><?= $miles_distance . " miles" ?> </p>
              </div>
          </div>
          <div style="display: flex;flex-direction: column;margin-right: 20px;">
              <label style="font-size: 18px;font-weight: 500;margin-bottom: 5px;">
                  Flight duration
              </label>
              <div style="width: 100%;height: 30px;border:1px solid grey; border-radius: 5px;background-color:white;padding-left:5px;">
                <p id="flight_duration"><?= $fly_time . " h" ?></p>
              </div>
          </div>
      </div>
        <p id="from_lat" style="display:none;"><?= $from_coords[0] ?></p>
        <p id="from_lng" style="display:none;"><?= $from_coords[1] ?></p>
        <p id="to_lat" style="display:none;"><?= $to_coords[0] ?> </p>
        <p id="to_lng" style="display:none;"><?= $to_coords[1] ?></p>
        <?php
          };
        ?>
  <script defer>
  function initMap() {
    // const input1 = document.getElementById("autocomplete1");
    // const input2 = document.getElementById("autocomplete2");
    // const options = {
    //   fields: ["formatted_address"],
    //   types: ['geocode']
    // };
    // const autocomplete1 = new google.maps.places.Autocomplete(input1, options);
    // const autocomplete2 = new google.maps.places.Autocomplete(input2, options);

    const bounds = new google.maps.LatLngBounds();
    const markersArray = [];
    const map = new google.maps.Map(document.getElementById("map"), {
      center: { lat: 55.53, lng: 9.4 },
      zoom: 10,
    });
    // autocomplete2.bindTo("bounds", map);
    // autocomplete1.bindTo("bounds", map);

    const from_lat = parseFloat(document.getElementById("from_lat").innerHTML);
    const from_lng = parseFloat(document.getElementById("from_lng").innerHTML);
    const to_lat = parseFloat(document.getElementById("to_lat").innerHTML);
    const to_lng = parseFloat(document.getElementById("to_lng").innerHTML);

    const from_coords = {'lat':from_lat,'lng':from_lng}
    const to_coords = {'lat':to_lat,'lng':to_lng}

    deleteMarkers(markersArray);

    map.fitBounds(bounds.extend(from_coords));
    map.fitBounds(bounds.extend(to_coords));

    markersArray.push(
      new google.maps.Marker({
        map,
        position: from_coords,
        label: "O",
      }),
    );
    markersArray.push(
      new google.maps.Marker({
        map,
        position: to_coords,
        label: "D",
      }),
    );

  }
  function deleteMarkers(markersArray) {
    for (let i = 0; i < markersArray.length; i++) {
      markersArray[i].setMap(null);
    }

    markersArray = [];
  }

  window.initMap = initMap;
  </script>