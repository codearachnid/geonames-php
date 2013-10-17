<?php

require_once 'class.geonames.php';

$geonames = new GeoNames_API( 'codearachnid' );

// search for all cities named 'Paris'
$cities = $geonames->search(array('name_equals' => 'Paris'));

echo "List of cities named Paris:\n";
foreach ($cities->geonames as $city) {
    printf(" - %s (%s)\n", $city->name, $city->countryName);
}
echo "\n";

// find all postal codes near by "Toulouse" in a radius of 10km 
$postalCodes = $geonames->findNearbyPostalCodes(array(
    'lat'     => 43.606,
    'lng'     => 1.444,
    'radius'  => 10, // 10km
    'maxRows' => 100
));
echo "List of postal codes near by Toulouse in a radius of 10km:\n";
foreach ($postalCodes->geonames as $code) {
    printf(" - %s (%s)\n", $code->postalCode, $code->placeName);
}
echo "\n";

// get the list of all countries and capitals in spanish
$countries = $geonames->countryInfo(array('lang' => 'es'));
echo "List of all countries in spanish language:\n";
foreach ($countries->geonames as $country) {
    printf(" - %s (capital: %s)\n", $country->countryName, $country->capital);
}
echo "\n";

// get the neightbours countries of France
$array      = $geonames->countryInfo(array('country' => 'FR'));
$france     = $array[0];
$neighbours = $geonames->neighbours(array('geonameId' => $france->geonameId));
echo "Neighbours of France are:\n";
foreach ($neighbours->geonames as $neighbour) {
    printf(" - %s\n", $neighbour->countryName);
}