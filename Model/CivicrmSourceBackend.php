<?php
/**
 * COmanage Registry CiviCRM OrgIdentitySource Backend Model
 *
 * This model requires HTTP_Request2
 * https://pear.php.net/package/HTTP_Request2/
 * Install with pear via "pear install HTTP_Request2"
 *
 * Copyright (C) 2020 Modern Language Association
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 *
 * @copyright     Copyright (C) 2020 Modern Language Association
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry-plugin
 * @since         COmanage Registry v3.2.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 * @version       $Id$
 */

App::uses("OrgIdentitySourceBackend", "Model");
require_once 'HTTP/Request2.php';

class CivicrmSourceBackend extends OrgIdentitySourceBackend {
  public $name = "CivicrmSourceBackend";
  
/* Useful if organizations will be returned from CiviCRM

  protected $groupAttrs = array(
    'organizations' => 'Organizations'
  );
  
  protected $groupAdminRoles = array('chair', 'liaison', 'liason', 'secretary', 'executive');

 */
  
  /** 
   * Build a query URL for a CiviCRM API call to search contacts, based on the plugin's configuration.
   *
   * Need to query by contact first and then query membership by contact id due to inconsistent REST API performance (see buildMembershipCheckUrl)
   * @since  COmanage Registry v3.2.0
   * @param  Array $attributes Array of attributes as passed to search()
   * @return String URL
   */

  protected function buildQueryUrl($attributes) {
    $url = "";

    $attrs = $attributes;
    $attrs['entity'] = "Contact";
    $attrs['action'] = "get";
    $attrs['api_key'] = $this->pluginCfg['userkey'];
    $attrs['key'] = $this->pluginCfg['sitekey'];
    $attrs['version'] = "3";
    $attrs['sequential'] = "1";
    $attrs['json'] = "1";
    $attrs['location_type_id'] = "Main";
    $attrs['is_primary'] = "1";
    $attrs['contact_is_deleted'] = "0";
    $attrs['return'] = "id,email,external_identifier,contact_type,contact_sub_type,formal_title,first_name,middle_name,last_name,job_title,organization_name";

    $url = $this->pluginCfg['apiroot'] . '?';
    $url .=  http_build_query($attrs);
    return $url;
  }
  
  /** 
   * Build a query URL for a CiviCRM API call to check membership status
   *
   * As of V 5.20, the CiviCRM REST API is returning incorrect results for membership/contact queries. The following query by name works, but the query by email does not.
   *
   * https://example.org/wp-content/plugins/civicrm/civicrm/extern/rest.php?
   *           entity=Membership&action=get&api_key=userkey&key=sitekey&json={"sequential":1,"contact_id.last_name":"smith","active_only":1}
   *
   * https://example.org/wp-content/plugins/civicrm/civicrm/extern/rest.php?
   *           entity=Membership&action=get&api_key=userkey&key=sitekey&json={"sequential":1,"contact_id.email":"smith@example.org","active_only":1}
   *
   * @since  COmanage Registry v3.2.0
   * @param  Array $attributes Array with contact_id as returned by search()
   * @return String URL
   */

  protected function buildMembershipCheckUrl($attributes) {
    $url = "";

    $attrs = $attributes;
    $attrs['entity'] = "Membership";
    $attrs['action'] = "get";
    $attrs['api_key'] = $this->pluginCfg['userkey'];
    $attrs['key'] = $this->pluginCfg['sitekey'];
    $attrs['version'] = "3";
    $attrs['sequential'] = "1";
    $attrs['json'] = "1";
    $attrs['active_only'] = "1";

    $url = $this->pluginCfg['apiroot'] . '?';
    $url .=  http_build_query($attrs);
    return $url;
  }
  
  /**
   * Generate the set of attributes for the IdentitySource that can be used to map
   * to group memberships. The returned array should be of the form key => label,
   * where key is meaningful to the IdentitySource (eg: a number or a field name)
   * and label is the localized string to be displayed to the user. Backends should
   * only return a non-empty array if they wish to take advantage of the automatic
   * group mapping service.
   *
   * @since  COmanage Registry v3.2.0
   * @return Array As specified
   */
  
  public function groupableAttributes() {
    return $this->groupAttrs;
  }
  
  /**
   * Obtain all available records in the IdentitySource, as a list of unique keys
   * (ie: suitable for passing to retrieve()).
   *
   * @since  COmanage Registry v3.2.0
   * @return Array Array of unique keys
   * @throws DomainException If the backend does not support this type of requests
   */
  
  public function inventory() {

    throw new DomainException("NOT IMPLEMENTED");

/* Possible to load initially from a file of IDs
    $syncFile = App::pluginPath('CivicrmSource') . 'Config/full-sync-ids';
    $ret = array();
    
    if(is_readable($syncFile)) {
      $ids = array_filter(explode("\n", file_get_contents($syncFile)));
      
      foreach($ids as $id) {
        $ret[] = preg_replace("/[^0-9]/", "", $id);
      }
    }
    
    return $ret;
 */

  }
  
  /**
   * Execute a REST request.
   *
   * @since  COmanage Registry v3.2.0
   * @param  String $url URL (request endpoint)
   * @param  String $body Request body, or NULL
   * @param  String $httpMethod HTTP request method (eg: "GET")
   * @todo   Move to app/Lib
   */
  
  protected function makeRestRequest($url, $httpMethod="GET", $body=null) {
    $ret = array();
    
    $requestBody = $body;
    
    $request = new HTTP_Request2($url);
    
    try {
      $timeout = 30;
      
      $request->setConfig('connect_timeout', $timeout);
      $request->setConfig(array('timeout' => $timeout));
      
      switch($httpMethod) {
        case 'GET':
          $request->setMethod(HTTP_Request2::METHOD_GET);
          break;
        case 'POST':
          $request->setMethod(HTTP_Request2::METHOD_POST);
          break;
        case 'PUT':
          $request->setMethod(HTTP_Request2::METHOD_PUT);
          break;
      }

      // Disable SSL cert verification for testing with "bad" certs
      // CiviCRM API needs verify turned off until proper cert chain can be installed
      //$request->setConfig(array('ssl_verify_peer' => false));
      $request->setBody($requestBody);

      $response = $request->send();
    }
    catch(HTTP_Request2_Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
    catch(Exception $e) {
      throw new RuntimeException($e->getMessage());
    }
    
    $ret['status'] = $response->getStatus();
    $ret['body'] = $response->getBody();
    
    return $ret;
  }
  
  /**
   * Query the CiviCRM API for contact entity.
   *
   * @since  COmanage Registry v3.2.0
   * @param  Array $attributes Attributes to query (ie: searchableAttributes())
   * @return Array Search results
   * @throws RuntimeException
   */
  
  protected function queryCivicrmApi($attributes) {
    $url = $this->buildQueryUrl($attributes);
    $url_parts = explode( '?', $url );
    $response = $this->makeRestRequest( $url_parts[0], 'POST', $url_parts[1] );

    if($response['status'] == 200) {
      return json_decode($response['body'], true);
    } else {
      throw new RuntimeException('Received ' . $response['status'] . ' response');
    }
  }

  /**
   * Query the CiviCRM API for membership entity.
   *
   * @since  COmanage Registry v3.2.0
   * @param  Array $attributes Attributes to query (ie: searchableAttributes())
   * @return Array Search results
   * @throws RuntimeException
   */
  
  protected function queryCivicrmMembershipApi($attributes) {
    $url = $this->buildMembershipCheckUrl($attributes);
    $url_parts = explode( '?', $url );
    $response = $this->makeRestRequest( $url_parts[0], 'POST', $url_parts[1] );

    if($response['status'] == 200) {
      return json_decode($response['body'], true);
    } else {
      throw new RuntimeException('Received ' . $response['status'] . ' response');
    }
  }

  /**
   * Convert a raw result, as from eg retrieve(), into an array of attributes that
   * can be used for group mapping.
   *
   * @since  COmanage Registry v3.2.0
   * @param  String $raw Raw record, as obtained via retrieve()
   * @return Array Array, where keys are attribute names and values are lists (arrays) of attributes
   */
  
  public function resultToGroups($raw) {
    $ret = array();
    
    //TODO This may need to be custom for each install.
 
    return $ret;
  }
  
  /**
   * Convert a search result into an Org Identity.
   *
   * @since  COmanage Registry v3.2.0
   * @param  Array $result netFORUM Search Result
   * @return Array Org Identity and related models, in the usual format
   */
  
  protected function resultToOrgIdentity($result) {
    $orgdata = array();
    $orgdata['OrgIdentity'] = array();

    // Until we have some rules, everyone is a member
    $orgdata['OrgIdentity']['affiliation'] = AffiliationEnum::Member;
    
    //TODO CiviCRM API should provide these - determine where these values are stored
    if(!empty($result['comanage_custom']['primary_address_affiliation'])) {
      $orgdata['OrgIdentity']['o'] = $result['comanage_custom']['primary_address_affiliation'];
    }
    if(!empty($result['comanage_custom']['primary_address_rank'])) {
      $orgdata['OrgIdentity']['title'] = $result['comanage_custom']['primary_address_rank'];
    }

    $localTZ = new DateTimeZone("America/New_York");
    $utcTZ = new DateTimeZone("UTC");

    //TODO CiviCRM API should provide this - determine where this value is stored
    if(!empty($result['membership']['starting_date'])) {
      // Format is MM/DD/YYYY, from start of day Eastern Time
      
      $d = $result['membership']['starting_date'] . " 00:00:00";
      
      // Create a DateTime object in localtime
      $localDT = new DateTime($d, $localTZ);
      // And convert it to UTC before emitting
      $localDT->setTimezone($utcTZ);
      
      $orgdata['OrgIdentity']['valid_from'] = $localDT->format("Y-m-d H:i:s");
    }
    
    //TODO CiviCRM API should provide this - determine where this value is stored
    if(!empty($result['membership']['expiring_date'])) {
      // Format is MM/DD/YYYY, presumably valid through end of day Eastern Time
      
      $d = $result['membership']['expiring_date'] . " 23:59:59";
      
      // Create a DateTime object in localtime
      $localDT = new DateTime($d, $localTZ);
      // And convert it to UTC before emitting
      $localDT->setTimezone($utcTZ);
      
      $orgdata['OrgIdentity']['valid_through'] = $localDT->format("Y-m-d H:i:s");
    }

    $orgdata['Name'] = array();
    
    if(!empty($result['first_name']))
      $orgdata['Name'][0]['given'] = $result['first_name'];
    if(!empty($result['last_name']))
      $orgdata['Name'][0]['family'] = $result['last_name'];
    $orgdata['Name'][0]['primary_name'] = true;
    $orgdata['Name'][0]['type'] = NameEnum::Official;
    
    $orgdata['EmailAddress'] = array();
    
    if(!empty($result['email'])) {
      $orgdata['EmailAddress'][0]['mail'] = $result['email'];
      $orgdata['EmailAddress'][0]['type'] = EmailAddressEnum::Official;
      $orgdata['EmailAddress'][0]['verified'] = true;
    }
    
    //TODO CiviCRM API may be able to provide other identifers that would get mapped here (IDP, ORCID, etc.)

    return $orgdata;
  }
  
  /**
   * Retrieve a single record from the IdentitySource. The return array consists
   * of two entries: 'raw', a string containing the raw record as returned by the
   * IdentitySource backend, and 'orgidentity', the data in OrgIdentity format.
   *
   * @since  COmanage Registry v3.2.0
   * @param  String $id Unique key to identify record
   * @return Array As specified
   * @throws InvalidArgumentException if not found
   * @throws OverflowException if more than one match
   */
  
  public function retrieve($id) {
    $ret = array();
    
    $results = $this->queryCivicrmApi(array('id' => $id));

    if($results['is_error'] != 0 || $results['count'] == 0) {
      throw new InvalidArgumentException(_txt('er.id.unk-a', array($id)));
    }

    $ret['raw'] = json_encode($results, JSON_PRETTY_PRINT);
    $ret['orgidentity'] = $this->resultToOrgIdentity($results['values'][0]);

    return $ret;
  }
  
  /**
   * Perform a search against the IdentitySource. The returned array should be of
   * the form uniqueId => attributes, where uniqueId is a persistent identifier
   * to obtain the same record and attributes represent an OrgIdentity, including
   * related models.
   *
   * @since  COmanage Registry v3.2.0
   * @param  Array $attributes Array in key/value format, where key is the same as returned by searchAttributes()
   * @return Array Array of search results, as specified
   */
    
  public function search($attributes) {
    $ret = array();
    
    $attrs = $attributes;

    // OIS infrastructure expects 'mail', but CiviCRM API uses 'email'
    if(isset($attrs['mail'])) {
      $attrs['email'] = $attrs['mail'];
      unset($attrs['mail']);
    }
    
    //TODO Add any extra parameters we might need.
    // $attrs['membership_status'] = 'ALL';
    
    $results = $this->queryCivicrmApi($attrs);
    
    // Turn the results into an array
    if($results['is_error'] == '0'
       && $results['count'] > 0) {
      foreach($results['values'] as $r) {
        //Loop through results and Check for active membership because the CiviCRM API is not returning correct results when joining contact and membership in one call
        $membershipCheck = $this->queryCivicrmMembershipApi(array( 'contact_id' => $r['id']));
        if($membershipCheck['count'] > 0) {
          // Use the record ID as the unique ID
          $ret[ $r['id'] ] = $this->resultToOrgIdentity($r);
        }
      }
    }
    return $ret;
  }
  
  /**
   * Generate the set of searchable attributes for the IdentitySource.
   * The returned array should be of the form key => label, where key is meaningful
   * to the IdentitySource (eg: a number or a field name) and label is the localized
   * string to be displayed to the user.
   *
   * @since  COmanage Registry v3.2.0
   * @return Array As specified
   */
  
  public function searchableAttributes() {
    return array(
      'first_name' => _txt('fd.name.given'),
      'last_name'  => _txt('fd.name.family'),
      'email'      => _txt('fd.email_address.mail')
    );
  }

  /**
   * Test the CiviCRM API to verify that the connection information is valid.
   *
   * @since  COmanage Registry v3.2.0
   * @param  String API Root
   * @param  String Site Key
   * @param  String User Key
   * @return Boolean True if parameters are valid
   * @throws RuntimeException
   */
  
  public function verifyCivicrmServer($apiRoot, $siteKey, $userKey) {
    $this->pluginCfg = array();
    $this->pluginCfg['apiroot'] = $apiRoot;
    $this->pluginCfg['sitekey'] = $siteKey;
    $this->pluginCfg['userkey'] = $userKey;
    
    // Based on similar code in CoLdapProvisionerTarget
    
    $results = $this->queryCivicrmApi(array('id' => 0));

    //TODO Remove this and provide a value for a test id. Should define a configuration setting containing id that can be queried to test config.
    return true;
    
    if(count($results) < 1) {
      throw new RuntimeException(_txt('er.civicrmsource.connect'));
    }
    
    return true;
  }
}
