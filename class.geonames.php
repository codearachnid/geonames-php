<?php

/**
 * GeoNames PHP API
 * You must have a GeoNames username in order to access the api
 * go to http://www.geonames.org to register
 *
 * Tested minimum PHP version 5.2
 *
 * LICENSE: This source file is subject to the MIT license that is available
 * through the world-wide-web at the following URI:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Remote API
 * @author    Timothy Wood @codearachnid <codearachnid@gmail.com>
 * @copyright 2013 Timothy Wood
 * @license   http://opensource.org/licenses/mit-license.php MIT License 
 * @version   0.1
 * @link      http://www.geonames.org/export/web-services.html
 */
 
class GeoNames_API{

	//GeoNames API host url
	protected $host = 'api.geonames.org';
	
	/**
     * Auth username, only relevant for the geonames commercial web services:
     * {@link http://www.geonames.org/commercial-webservices.html}
     *
     * @var string $username
     * @see Services_GeoNames::__construct()
     */
	protected $username = null;

	/**
     * Auth token, only relevant for the geonames commercial web services:
     * {@link http://www.geonames.org/commercial-webservices.html}
     *
     * @var string $token
     * @see Services_GeoNames::__construct()
     */
	protected $token = null;

	const AUTHORIZATION_EXCEPTION          = 10;
    const RECORD_DOES_NOT_EXIST            = 11;
    const OTHER_ERROR                      = 12;
    const DATABASE_TIMEOUT                 = 13;
    const INVALID_PARAMETER                = 14;
    const NO_RESULT_FOUND                  = 15;
    const DUPLICATE_EXCEPTION              = 16;
    const POSTAL_CODE_NOT_FOUND            = 17;
    const DAILY_LIMIT_OF_CREDITS_EXCEEDED  = 18;
    const HOURLY_LIMIT_OF_CREDITS_EXCEEDED = 19;
    const WEEKLY_LIMIT_OF_CREDITS_EXCEEDED = 20;
    const INVALID_INPUT                    = 21;
    const SERVER_OVERLOADED_EXCEPTION      = 22;
    const SERVICE_NOT_IMPLEMENTED          = 23;

	 /**
     * Array of supported endpoints (listed alphabetically) and their 
     * corresponding root property (if any). You can retrieve the list of 
     * endpoints (only the keys of this array) with the 
     * Services_GeoNames::getSupportedEndpoints() method.
     * 
     * Note that we only support json endpoints, so the following endpoints are
     * not supported:
     * - extendedFindNearby (JSON not available for now)
     * - rssToGeo (RSS/KML only)
     *
     * For a full documentation of the available endpoints services, please 
     * see: {@link http://www.geonames.org/export/ws-overview.html}.
     *
     * @var array $endpoints
     * @see Services_GeoNames::getSupportedEndpoints()
     */
    protected $endpoints = array(
        'children'                => 'geonames',
        'cities'                  => 'geonames',
        'countryCode'             => false,
        'countryInfo'             => 'geonames',
        'countrySubdivision'      => false,
        'earthquakes'             => 'earthquakes',
        'findNearby'              => 'geonames',
        'findNearbyPlaceName'     => 'geonames',
        'findNearbyPostalCodes'   => 'postalCodes',
        'findNearbyStreets'       => 'streetSegment',
        'findNearByWeather'       => 'weatherObservation',
        'findNearbyWikipedia'     => 'geonames',
        'findNearestAddress'      => 'address',
        'findNearestIntersection' => 'intersection',
        'get'                     => false,
        'gtopo30'                 => false,
        'hierarchy'               => 'geonames',
        'neighbourhood'           => 'neighbourhood',
        'neighbours'              => 'geonames',
        'postalCodeCountryInfo'   => 'geonames',
        'postalCodeLookup'        => 'postalcodes', // not a typo
        'postalCodeSearch'        => 'postalCodes',
        'search'                  => 'geonames',
        'siblings'                => 'geonames',
        'weather'                 => 'weatherObservations',
        'weatherIcao'             => 'weatherObservation',
        'srtm3'                   => false,
        'timezone'                => false,
        'wikipediaBoundingBox'    => 'geonames',
        'wikipediaSearch'         => 'geonames',
    );

	/**
     * Constructor, if you're using a commercial account (optional), you must 
     * pass your "username" and "token".
     * 
     * @param string $username Username for commercial webservice (optional)
     * @param string $token    Token for commercial webservice (optional)
     * 
     * @return void
     * @access public
     */
	public function __construct( $username = null, $token = null ){

		if( !is_null( $username ) ) {
			$this->set_username( $username );
		}

		if( !is_null( $token ) ) {
			$this->set_token( $token );
		}

	}

	/**
     * Method interceptor that retrieves the corresponding endpoint and return
     * a json decoded object or throw a Services_GeoNames_Exception.
     * 
     * @param string $endpoint The endpoint to call
     * @param array  $params   Array of parameters to pass to the endpoint
     * 
     * @return mixed stdclass|array The JSON decoded response or an array
     * @throws Services_GeoNames_Exception When an invalid method is called or 
     *                                     when the websercices returns an error
     */
	public function __call( $endpoint, $params = array() ){
		// check that endpoint is supported
		if (!in_array($endpoint, $this->get_supported_endpoints())) {
			throw new GeoNames_API_Exception(
				"Unknown service endpoint {$endpoint}",
				self::UNSUPPORTED_ENDPOINT
			);
		}

		// setup username for removing limit on api access
        if ($this->username !== null) {
            $params['username'] = $this->username;
        }
        // setup authentication to commercial webservice
        if ($this->token !== null) {
            $params['token'] = $this->token;
        }

        $data = json_decode( $this->send_request( $endpoint, $params ) );

        return $data;
	}

	public function set_username( $username ){
		$this->username = $username;
	}

	public function set_token( $token ){
		$this->token = $token;
	}

	protected function send_request( $endpoint = null, $params = array() ){
        // only support for json format
        if (isset($params['type'])) {
            unset($params['type']);
        }

		// build the url and retrieve the result
        $url_path = 'http://' . $this->host . '/' . $endpoint . 'JSON?' . $this->format_query_string( $params );

        // curl the request
        $request = curl_init( $url_path );
		curl_setopt( $request, CURLOPT_RETURNTRANSFER, true );
		$response = curl_exec($request);
		curl_close($request);

		return $response;
	}

	/**
     * Builds a valid query string (url and utf8 encoded) to pass to the 
     * endpoint and returns it.
     * 
     * @param array $params Associative array of query parameters (name=>val)
     * 
     * @return string The formatted query string
     */
    protected function format_query_string($params = array())
    {
        $qString = array();
        foreach ($params as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $val = mb_detect_encoding($val, "UTF-8") == "UTF-8" ? $val : utf8_encode($val);
                    $qString[] = $name . '=' . urlencode($val);
                }
            } else {
                $value = mb_detect_encoding($val, "UTF-8") == "UTF-8" ? $value : utf8_encode($value);
                $qString[] = $name . '=' . urlencode($value);
            }
        }
        return implode('&', $qString);
    }

	/**
     * Returns an array of supported services endpoints.
     * 
     * @return array The endpoints array
     * @see GeoNames_API::$endpoints
     */
    public function get_supported_endpoints(){
        return array_keys( $this->endpoints );
    }
  
}

/**
 * GeoNames_API_Exception
 *
 * Custom exception handler for GeoNames_API class
 */
class GeoNames_API_Exception extends Exception {
    public function __construct($message = null, $code = 0, Exception $previous = null){
        parent::__construct($message, $code, $previous);
        error_log($message);
    }
}
