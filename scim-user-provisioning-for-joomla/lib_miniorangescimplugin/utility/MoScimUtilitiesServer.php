<?php

/**
 * @package     Joomla.Utility
 * @subpackage  lib_miniorangescim
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\User\User;
use Joomla\CMS\Factory;
use Joomla\CMS\String\PunycodeHelper;
jimport( 'joomla.plugin.plugin' );
jimport('miniorangescimplugin.utility.MoScimUtilitiesClient');
jimport("miniorangescimplugin.moscim.moScimConstants");
jimport("miniorangescimplugin.moscim.moScimRequest");
jimport("miniorangescimplugin.moscim.moScimUser");
class MoScimUtilitiesServer
{
	public static function create_user_for_joomla($finalMap){
		MoScimUtilitiesClient::mo_log('create_user_for_joomla');

        if(isset($finalMap->userTable->username)){
            $data['username'] = $finalMap->userTable->username;
        }
        else
        {
            $data['username'] =PunycodeHelper::emailToPunycode($finalMap->userTable->email);
        }

		if(isset($finalMap->userTable->email)){
            $data['email'] =PunycodeHelper::emailToPunycode($finalMap->userTable->email);
        }   
		else{
		    $data['email'] = $finalMap->userTable->username;
        }

        $data['name'] = isset($finalMap->userTable->name) ? $finalMap->userTable->name :  $data['username'];

		
		$data['password'] = $data['password1'] = $data['password2'] = !isset($finalMap->userTable->password)?MoScimUtilitiesServer::getPass(8):$finalMap->userTable->password;
        $defaultRoles = MoScimUtilitiesServer::getScimConfig()->default_roles;

		$data['groups']=json_decode($defaultRoles,true);

        MoScimUtilitiesClient::mo_log('email'.$data['email']);
        MoScimUtilitiesClient::mo_log('username'.$data['username']);
        MoScimUtilitiesClient::mo_log('name'.$data['name']);
        MoScimUtilitiesClient::mo_log('roles'.$defaultRoles);

		$user = new User;
		if(!$user->bind($data)) {
            MoScimUtilitiesClient::mo_log('Could not bind data. Error:');
            MoScimUtilitiesClient::saveSCIMConfig('#__miniorange_scim_details','user_creation',false);
            MoScimUtilitiesClient::keepRecords('Could not bind data','user_creation');
			JLog::add("Could not bind data. Error: " . $user->getError(), JLog::ERROR, 'miniorange_scim');
			return array(0,$user->getError());
		}
		if (!$user->save()) {
            MoScimUtilitiesClient::mo_log('Could not save data. Error: ');
            MoScimUtilitiesClient::saveSCIMConfig('#__miniorange_scim_details','user_creation',false);
            MoScimUtilitiesClient::keepRecords('Could not bind data','user_creation');
			JLog::add("Could not save data. Error: " . $user->getError(), JLog::ERROR, 'miniorange_scim');
            if($user->getError()==="Username in use.")
            {
                MoScimUtilitiesClient::saveSCIMConfig('#__miniorange_scim_details','user_creation',false);
                MoScimUtilitiesClient::keepRecords('Username in use','user_creation');
                MoScimUtilitiesClient::mo_log('Username in use. Error: ');
                return array(-1,$user->getError());
            }
			   
		}

        MoScimUtilitiesClient::mo_log('user_creation');
        $details=MoScimUtilitiesClient::miniScimFetchDb('#__miniorange_scim_details', array('id' => 1));
        $no_users=isset($details['users'])?($details['users']+1):1;
        $updatefieldsarray = array(
            'users' =>  $no_users,
        );
        MoScimUtilitiesClient::updateDBValues('#__miniorange_scim_details', $updatefieldsarray);
        MoScimUtilitiesClient::saveSCIMConfig('#__miniorange_scim_details','user_creation',true);
        if($no_users==1 || ($no_users%5==0))
        {
            MoScimUtilitiesClient::keepRecords('No error','user_creation');
        }

		$activation = isset($finalMap->userTable->activation)?intval($finalMap->userTable->activation):1;
        $block = isset($finalMap->userTable->block)?intval($finalMap->userTable->block):1;
        MoScimUtilitiesServer::activate_user_for_joomla($data['username'] ,$activation,$block);

		return array($user->id,"");
	}

	public static function activate_user_for_joomla($username,$activation,$block){
		$fields = array(
			'activation'=>$activation,
			'block'=> $block
		);
		$conditions = array('username'=>$username);
		MoScimUtilitiesClient::miniScimUpdateDb('#__users',$fields,$conditions);
	}

	public static function getScimConfig(){
        return MoScimUtilitiesClient::miniScimFetchDb('#__miniorange_scim_details',array('id'=>1),'loadObject');
	}
	
    public static function getPass($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    // User Creation
    public static function moScimUsers(moScimRequest $scimRequest){
        if( $scimRequest->getRequestType() == moScimConstants::GET_REQUEST  ){
           $users = moScimUser::getUsersByFilter($scimRequest);
           $json = json_encode($users);

           foreach ($users as $user){
               $userName = $user['username'];
               unset($user['username']);
               $user['userName'] = $userName;
           }
          MoScimUtilitiesServer::sendUserListResponse($json,count($users));
        }
        elseif ( $scimRequest->getRequestType() == moScimConstants::POST_REQUEST ){
            $moScimUser = new moScimUser($scimRequest);
            if($moScimUser->alreadyExists){
                MoScimUtilitiesServer::sendUserConflictResponse();
            }
            else{
                $attributeMap = MoScimUtilitiesClient::get_scim_config()['moScimAttributeMap'];
                $finalMap = $moScimUser->getAttributeMap($attributeMap);
                
                list($id,$error)=MoScimUtilitiesServer::create_user_for_joomla($finalMap);

                // we need to decide why the user creation failed
                if($id==0)
                {
                    // some internal error occured;
                    MoScimUtilitiesServer::someInternalServerErrorOccured($error);
                }
                else if($id==-1){
                    MoScimUtilitiesServer::sendUserConflictResponse();
                }
                else
                    MoScimUtilitiesServer::sendUserCreatedResponse($id);
            }
        }
    }

    public static function  sendUserConflictResponse(){
        header("HTTP/1.1 409 Conflict");
        header('Content-Type: application/json;charset=utf-8');
        echo '{
					    "schemas": ["urn:ietf:params:scim:api:messages:2.0:Error"],
					    "scimType":"uniqueness",
					    "detail": "User already exists in the database.",
					    "status": 409
					}';
        exit;
    }

    public static function sendUserCreatedResponse($id){
        header("HTTP/1.1 201 Created");
        header('Content-Type: application/json;charset=utf-8');
        $user  = Factory::getUser($id);
        $response = new stdClass();
        $response->schemas = array("urn:ietf:params:scim:schemas:core:2.0:User");
        $response->id = strval($user->id);
        $response->meta = new stdClass();
        $response->meta->resourceType="User";
        $response->userName = $user->username;
        echo json_encode($response);
        exit;
    }

    public static function sendUserNotFoundResponse($id){
        header("HTTP/1.1 404 Not Found");
        header('Content-Type: application/json;charset=utf-8');
	    echo '{
            "schemas": ["urn:ietf:params:scim:api:messages:2.0:Error"],
            "detail":"Resource '.strval($id).' not found",
            "status": "404"
            }';
	    exit;
    }

    public static function  sendUserFoundResponse(User $user){
        $name = explode(" ", $user->name);
        header("HTTP/1.1 200 OK");
        header('Content-Type: application/json;charset=utf-8');
        echo '{
					    "schemas": ["urn:ietf:params:scim:schemas:core:2.0:User"],
					    "id": "'.strval($user->id).'",
					    "userName": "'.$user->username.'",
					    "name": {
					        "givenName": "'.$name[0].'",
					        "middleName": "",
					        "familyName": "'.(isset($name[1])?$name[1]:$name[0]).'"
					    },
					    "active":'.strval( $user->activation).',
					    "meta": {
					        "resourceType": "User"
					    }
					}';
        exit;
    }

    public static function sendUserListResponse($userJson,$totalResults){
        header("HTTP/1.1 200 OK");
        header('Content-Type: application/json;charset=utf-8');
	    echo '{
            "schemas":["urn:ietf:params:scim:api:messages:2.0:ListResponse"],
            "totalResults":'.strval($totalResults).',
            "startIndex": 1,
            "itemsPerPage":'.strval(Max($totalResults,10)),',
            "Resources":'.$userJson.'
        }';
	    exit;
    }

    public static  function someInternalServerErrorOccured($error=''){
        header("HTTP/1.1 500 Internal Server Error");
        header('Content-Type: application/json;charset=utf-8');
        echo '{
                    "schemas": ["urn:ietf:params:scim:api:messages:2.0:Error"],
                    "detail": "Internal error occurred:'.$error.'",
                    "status": 500
                }';
        exit;
    }
    public static function get_token_from_header() {
        $headers = MoScimUtilitiesServer::getAuthorizationHeader();
	    // HEADER: Get the access token from the header
	    if (!empty($headers)) {
	        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
	            return $matches[1];
	        }
	    }
	    return null;
	}

    public static function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    public static function sendAuthorizationFailedResponse($error=''){
        header("HTTP/1.1 401 Authorization failure");
        header('Content-Type: application/json;charset=utf-8');
        echo '{
                    "schemas": ["urn:ietf:params:scim:api:messages:2.0:Error"],
                    "detail": "The  authorization header is missing or invalid.",
                    "status": 401
                }';
        exit;
    }
}