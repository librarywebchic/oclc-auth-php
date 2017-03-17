<?php
/**
 * OCLC-Auth
 * Copyright 2013 OCLC
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @package OCLC/Auth
 * @copyright Copyright (c) 2013 OCLC
 * @license http://www.opensource.org/licenses/Apache-2.0
 * @author Karen A. Coombs <coombsk@oclc.org>
*/
namespace OCLC\Auth;
/**
 * A class that represents the Authorization code object
 * 
 */

class AuthCode
{
	/**
	 * The url for the authorization server
	 * @var string
	 */
    public static $authorizationServer = 'https://authn.sd00.worldcat.org/oauth2';

    /**
     * Whether or not to run in test mode
     * @var boolean
     */
    private $testMode = false;
    
    /**
     * The key value of the WSKey
     * @var string
     */
    private $clientId;

    /**
     * The authenticating institution id
     * @var integer
     */
    private $authenticatingInstitutionId;

    /**
     * The context institution id
     * @var integer
     */
    private $contextInstitutionId;

    /**
     * The redirect uri
     * @var string
     */
    private $redirectUri;

    /**
     * An array of scope values
     * @var array
     */
    private $scope;

    /**
     * Construct an Authorization Code object using
     *
     * @param string $client_id 
     * @param string $redirectUri            
     * @param array $scope
     * @param array $options
     *       
     * - integer $authenticatingInstitutionId            
     * - contextInstitutionId integer (optional)             
     * - testMode boolean           
     */
    public function __construct($client_id, $redirectUri, $scope, $options = null)
    {
        if (isset($options['testMode'])){
            $this->testMode = $options['testMode'];
        } else {
            $this->testMode = false;
        }
        
        if (empty($client_id)) {
            Throw new \BadMethodCallException('You must pass a valid key to construct an AuthCode');
        } elseif ($this->testMode == false && isset($options['contextInstitutionId']) && empty($options['authenticatingInstitutionId'])) {
            Throw new \BadMethodCallException('If you pass a contextInstitutionId, you must pass an authenticatingInstitutionId');
        } elseif ($this->testMode == false && isset($options['authenticatingInstitutionId']) && ! (is_int($options['authenticatingInstitutionId']))) {
            Throw new \BadMethodCallException('You must pass a valid integer for the authenticatingInstitutionId');
        } elseif ($this->testMode == false && isset($options['authenticatingInstitutionId']) &&  empty($options['contextInstitutionId'])) {
            Throw new \BadMethodCallException('If you pass an authenticatingInstitutionId, you must pass a contextInstitutionId');
        } elseif ($this->testMode == false && isset($options['contextInstitutionId']) && ! (is_int($options['contextInstitutionId']))) {
            Throw new \BadMethodCallException('You must pass a valid integer for the contextInstitutionId');
        } elseif ($this->testMode == false && empty($redirectUri)) {
            Throw new \BadMethodCallException('You must pass a redirectUri');
        } elseif ($this->testMode == false && filter_var($redirectUri, FILTER_VALIDATE_URL) === FALSE) {
            Throw new \BadMethodCallException('You must pass a valid redirectUri');
        } elseif ($this->testMode == false && (empty($scope) || ! (is_array($scope)))) {
            Throw new \BadMethodCallException('You must pass an array of at least one scope');
        }
        
        $this->clientId = $client_id;
        if (isset($options['authenticatingInstitutionId'])){
            $this->authenticatingInstitutionId = (int) $options['authenticatingInstitutionId'];
        }
        if (isset($options['contextInstitutionId'])){
            $this->contextInstitutionId = (int) $options['contextInstitutionId'];
        }
        $this->redirectUri = $redirectUri;
        $this->scope = $scope;
    }

    /**
     * Build the URL for logging user into Authorization Server
     *
     * @return string
     */
    public function getLoginUrl()
    {
        $loginURL = static::$authorizationServer . '/authorizeCode?client_id=' . $this->clientId;
        if (!empty($this->authenticatingInstitutionId)){
            $loginURL .= '&authenticatingInstitutionId=' . $this->authenticatingInstitutionId;
        }
        if (!empty($this->contextInstitutionId)){
            $loginURL .= '&contextInstitutionId=' . $this->contextInstitutionId;
        }
        $loginURL .= '&redirect_uri=' . urlencode($this->redirectUri) . '&response_type=code';
        if (isset($this->scope)){
            $loginURL.= '&scope=' . implode($this->scope, ' ');
        }
        return $loginURL;
    }
}
