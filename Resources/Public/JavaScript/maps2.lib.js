function createMarker(uid, pointPosition, infoWindowContent, mapObject, boundsObject) {
  /* <![CDATA[ */
  var marker = new google.maps.Marker({
    position: pointPosition,
    map: mapObject
  });
  /* ]]> */

  if (boundsObject != null) {
    boundsObject.extend(marker.position);
  }

  /* <![CDATA[ */
  infoWindow[uid] = new google.maps.InfoWindow({
    content: infoWindowContent
  });
  /* ]]> */
  infoWindowIsOpen[uid] = false;

  google.maps.event.addListener(marker, "click", function () {
    if (infoWindowIsOpen[uid] == false) {
      infoWindow[uid].open(mapObject, marker);
      infoWindowIsOpen[uid] = true;
    } else {
      infoWindow[uid].close();
      infoWindowIsOpen[uid] = false;
    }
  });
}