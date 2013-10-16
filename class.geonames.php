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
 * @filesource
 */
 
class GeoNames_API{

  //GeoNames API host url
  private $host = 'api.geonames.org';

  function __construct( username = '' ){
  
    if( !is_null( $username ) )
      $this->set_username( $username );

  }
  
  function set_username( $username ){
    $this->username = $username;
  }
  
}
