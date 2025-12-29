<?php
/**
 * @package     Joomla.System
 * @subpackage  plg_system_miniorangescim
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\User\User;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Installer\Installer;
jimport( 'joomla.plugin.plugin' );
jimport('miniorangescimplugin.utility.MoScimUtilitiesClient');
jimport("miniorangescimplugin.moscim.moScimConstants");
jimport("miniorangescimplugin.moscim.moScimRequest");
jimport("miniorangescimplugin.moscim.moScimUser");

include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_scim' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_scim_utility.php';

class plgSystemMiniorangescim extends CMSPlugin	
{

    public function onAfterInitialise()
	{
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $get = ($input && $input->get) ? $input->get->getArray() : [];
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        $tab = 0;
        $tables = Factory::getDbo()->getTableList();
    

        foreach ($tables as $table) {
            if ((strpos($table, "miniorange_scim_customer") !== FALSE) ||(strpos($table, "miniorange_scim_details") !== FALSE)  )
                $tab = $table;
        }
        if ($tab === 0)
            return;


        if (isset($post['mojsp_feedback']) || isset($post['mojspfree_skip_feedback'])) {
        
            if($tab)
            {
                $radio = isset($post['deactivate_plugin'])? $post['deactivate_plugin']:'';
                $data = isset($post['query_feedback'])?$post['query_feedback']:'';
                $feedback_email = isset($post['feedback_email'])? $post['feedback_email']:'';
    
                $database_name = '#__miniorange_scim_details';
                $updatefieldsarray = array(
                    'uninstall_feedback' => 1,
                );
                $result = new MoSCIMUtility();
                $result->generic_update_query($database_name, $updatefieldsarray);
                $current_user = Factory::getUser();
    
                $customerResult = new MoSCIMUtility();
                $customerResult = $customerResult->load_database_values('#__miniorange_scim_customer');
    
                $dVar=new JConfig();
                $check_email = $dVar->mailfrom;
                $admin_email = !empty($customerResult['admin_email']) ? $customerResult['admin_email'] :$check_email;
                $admin_email = !empty($admin_email)?$admin_email:self::getSuperUser();
                $admin_phone = $customerResult['admin_phone'];
                $data1 = $radio . ' : ' . $data . '  <br><br><strong>Email:</strong>  ' . $feedback_email;
    
                if(isset($post['mojspfree_skip_feedback']))
                {
                    $data1='Skipped the feedback';
                }
    
                if(file_exists(JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_scim' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php'))
                {
                    require_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_scim' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php';
    
                    MoScimCustomer::submit_uninstall_feedback_form($admin_email, $admin_phone, $data1,'');
                }
              
                require_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Installer.php';
    
                foreach ($post['result'] as $fbkey) {
    
                    $result = MoSCIMUtility::loadDBValues('#__extensions', 'loadColumn','type',  'extension_id', $fbkey);
                    $identifier = $fbkey;
                    $type = 0;
                    foreach ($result as $results) {
                        $type = $results;
                    }
    
                    if ($type) {
                        $cid = 0;
                        try {
                            $installer = null;
                            // Try Joomla 4+ dependency injection container first
                            if (method_exists('Joomla\CMS\Factory', 'getContainer')) {
                                try {
                                    $container = Factory::getContainer();
                                    if ($container && method_exists($container, 'get')) {
                                        $installer = $container->get(Installer::class);
                                    }
                                } catch (Exception $e) {
                                    // Container approach failed, continue to fallback
                                }
                            }
                            
                            // Fallback: manual instantiation for all versions
                            if (!$installer) {
                                $installer = new Installer();
                                if (method_exists($installer, 'setDatabase')) {
                                    $installer->setDatabase(Factory::getDbo());
                                }
                            }
                            
                            $installer->uninstall($type, $identifier, $cid);
                            
                        } catch (Exception $e) {
                            $app = Factory::getApplication();
                            if (method_exists($app, 'enqueueMessage')) {
                                $app->enqueueMessage('Error uninstalling extension: ' . $e->getMessage(), 'warning');
                            }
                        }
                    }
                }
            }
        }
        $joomla_version=MoScimUtilitiesClient::getJoomlaCmsVersion();
        if($joomla_version<4)
        {
            $uri = Uri::getInstance();
        
            if(strpos($uri,moScimConstants::SCIM_EXTENSION)){
                $moScimRequest = new moScimRequest($uri);
                $tokenFromHeader = self::get_token_from_header();
                $scimConfig = MoScimUtilitiesClient::get_scim_config();
                $bearer_token = $scimConfig['bearer_token'];

                MoScimUtilitiesClient::mo_log('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~Date:'. date('m/d/Y').'~~~~~~~~~~~~~~~~~~~~~~~~~~~~');
                MoScimUtilitiesClient::mo_log("uri=>".$uri);
                MoScimUtilitiesClient::mo_log("moScimRequest=>".json_encode($moScimRequest));
                MoScimUtilitiesClient::mo_log("getPost=>".$moScimRequest->getPost());
                MoScimUtilitiesClient::mo_log("getRequestType=>".$moScimRequest->getRequestType());
                MoScimUtilitiesClient::mo_log("getRequestEndpoint=>".$moScimRequest->getRequestEndpoint());
                MoScimUtilitiesClient::mo_log("getRequestType=>".$moScimRequest->getRequestType());
                MoScimUtilitiesClient::mo_log("getResourceID=>".$moScimRequest->getResourceId());
                MoScimUtilitiesClient::mo_log('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');

                if($moScimRequest->getRequestType()=='PATCH')
                {
                    MoScimUtilitiesClient::saveSCIMConfig('#__miniorange_scim_details','user_creation',false);
                    MoScimUtilitiesClient::keepRecords('Patch Request','user_creation');
                }

                if($bearer_token !== $tokenFromHeader){
                    MoScimUtilitiesClient::saveSCIMConfig('#__miniorange_scim_details','scim_configuration',false);
                    MoScimUtilitiesClient::keepRecords('Error in configuration','scim_config');
                    self::sendAuthorizationFailedResponse();
                }

               

                if($moScimRequest->getRequestEndpoint()!==moScimConstants::NOT_SUPPORTED_ENDPOINT){
                    if(is_null($moScimRequest->getResourceId())){
                        $theHandlerFunction = "moScim".$moScimRequest->getRequestEndpoint();
                    }
                    else{
                        $theHandlerFunction = "moScim".$moScimRequest->getRequestEndpoint()."ById";
                    }
                    self::$theHandlerFunction($moScimRequest);

                }
                else
                {
                    MoScimUtilitiesClient::saveSCIMConfig('#__miniorange_scim_details','scim_configuration',true);
                    MoScimUtilitiesClient::keepRecords('No Error','scim_config');
                }
                exit;
            }
        }
	}

    public static function getSuperUser()
    {
        $db = Factory::getDBO();
        $query = $db->getQuery(true)->select('user_id')->from('#__user_usergroup_map')->where('group_id=' . $db->quote(8));
        $db->setQuery($query);
        $results = $db->loadColumn();
        return  $results[0];
    }

	function create_user_for_joomla($finalMap){
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

		
		$data['password'] = $data['password1'] = $data['password2'] = !isset($finalMap->userTable->password)?$this->getPass(8):$finalMap->userTable->password;
        $defaultRoles = $this->getScimConfig()->default_roles;

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
			Log::add("Could not bind data. Error: " . $user->getError(), Log::ERROR, 'miniorange_scim');
			return array(0,$user->getError());
		}
		if (!$user->save()) {
            MoScimUtilitiesClient::mo_log('Could not save data. Error: ');
            MoScimUtilitiesClient::saveSCIMConfig('#__miniorange_scim_details','user_creation',false);
            MoScimUtilitiesClient::keepRecords('Could not bind data','user_creation');
			Log::add("Could not save data. Error: " . $user->getError(), Log::ERROR, 'miniorange_scim');
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
        $this->activate_user_for_joomla($data['username'] ,$activation,$block);

		return array($user->id,"");
	}

	function activate_user_for_joomla($username,$activation,$block){
		$fields = array(
			'activation'=>$activation,
			'block'=> $block
		);
		$conditions = array('username'=>$username);
		MoScimUtilitiesClient::miniScimUpdateDb('#__users',$fields,$conditions);
	}

	function getScimConfig(){
        return MoScimUtilitiesClient::miniScimFetchDb('#__miniorange_scim_details',array('id'=>1),'loadObject');
	}
	
	function getAuthorizationHeader(){
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
	/**
	 * get access token from header
	 * */
	function get_token_from_header() {
        $headers = $this->getAuthorizationHeader();
	    // HEADER: Get the access token from the header
	    if (!empty($headers)) {
	        if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
	            return $matches[1];
	        }
	    }
	    return null;
	}

    function getPass($n) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    // User Creation
    function moScimUsers(moScimRequest $scimRequest){
        if( $scimRequest->getRequestType() == moScimConstants::GET_REQUEST  ){
           $users = moScimUser::getUsersByFilter($scimRequest);
           $json = json_encode($users);

           foreach ($users as $user){
               $userName = $user['username'];
               unset($user['username']);
               $user['userName'] = $userName;
           }
          $this->sendUserListResponse($json,count($users));
        }
        elseif ( $scimRequest->getRequestType() == moScimConstants::POST_REQUEST ){
            $moScimUser = new moScimUser($scimRequest);
            if($moScimUser->alreadyExists){
                $this->sendUserConflictResponse();
            }
            else{
                $attributeMap = MoScimUtilitiesClient::get_scim_config()['moScimAttributeMap'];
                $finalMap = $moScimUser->getAttributeMap($attributeMap);
                
                list($id,$error)=$this->create_user_for_joomla($finalMap);

                // we need to decide why the user creation failed
                if($id==0)
                {
                    // some internal error occured;
                    $this->someInternalServerErrorOccured($error);
                }
                else if($id==-1){
                    $this->sendUserConflictResponse();
                }
                else
                    $this->sendUserCreatedResponse($id);
            }
        }
    }

    function sendUserConflictResponse(){
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

    public function sendUserCreatedResponse($id){
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

    public function sendUserNotFoundResponse($id){
        header("HTTP/1.1 404 Not Found");
        header('Content-Type: application/json;charset=utf-8');
	    echo '{
            "schemas": ["urn:ietf:params:scim:api:messages:2.0:Error"],
            "detail":"Resource '.strval($id).' not found",
            "status": "404"
            }';
	    exit;
    }

    public function  sendUserFoundResponse(User $user){
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

    public function sendUserListResponse($userJson,$totalResults){
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

    function someInternalServerErrorOccured($error=''){
        header("HTTP/1.1 500 Internal Server Error");
        header('Content-Type: application/json;charset=utf-8');
        echo '{
                    "schemas": ["urn:ietf:params:scim:api:messages:2.0:Error"],
                    "detail": "Internal error occurred:'.$error.'",
                    "status": 500
                }';
        exit;
    }

    function sendAuthorizationFailedResponse($error=''){
        header("HTTP/1.1 401 Authorization failure");
        header('Content-Type: application/json;charset=utf-8');
        echo '{
                    "schemas": ["urn:ietf:params:scim:api:messages:2.0:Error"],
                    "detail": "The  authorization header is missing or invalid.",
                    "status": 401
                }';
        exit;
    }

    function onExtensionBeforeUninstall($id)
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        $tables = Factory::getDbo()->getTableList();
        $result = MoSCIMUtility::loadDBValues('#__extensions', 'loadColumn', 'extension_id', 'element', 'com_miniorange_scim');
        $tables = Factory::getDbo()->getTableList();
        $tab = 0;
        $tables = Factory::getDbo()->getTableList();
        foreach ($tables as $table) {
            if (strpos($table, "miniorange_scim_details") !== FALSE)
                $tab = $table;
        }
        if ($tab === 0)
            return;
        if ($tab) {
            $fid = new MoSCIMUtility();
            $fid = $fid->load_database_values('#__miniorange_scim_details');
            $fid = isset($fid['uninstall_feedback'])?$fid['uninstall_feedback']:null;
            $tpostData = $post;
            $customerResult = new MoSCIMUtility();
            $customerResult = $customerResult->load_database_values('#__miniorange_scim_customer');
            $dVar=new JConfig();
            $check_email = $dVar->mailfrom;
            $feedback_email = !empty($customerResult ['admin_email']) ? $customerResult ['admin_email'] :$check_email;

            if (1) {
                if ($fid == 0) {
                    foreach ($result as $results) {
                        if ($results == $id) {?>
                          <link rel="stylesheet" type="text/css" href="<?php echo Uri::base();?>/components/com_miniorange_scim/assets/css/miniorange_scim.css" />
                          <link rel="stylesheet" type="text/css" href="<?php echo Uri::base();?>/components/com_miniorange_scim/assets/css/miniorange_boot.css" />
                            <div class="form-style-6 mo_boot_mt-2 mo_boot_offset-4 mo_boot_col-4 ">
                                <h1>Feedback form for SCIM User Provisioning Free Plugin</h1>
                                <form name="f" method="post" action="" id="mojsp_feedback" classs="mo_boot_p-5">
                                    <h3>What Happened? </h3>
                                    <input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>
                                    <div>
                                        <p class="mo_boot_ml-2">
                                            <?php
                                            $deactivate_reasons = array(
                                                    'Does not have the features I am looking for?',
                                                    'Confusing Interface',
                                                    'Not able to Configure',
                                                    'I found a better plugin',
                                                    'Bugs in the plugin',
                                                    'Not working',
                                                    'Pricing concern',
                                                    'Other Reasons:'
                                                );
                                            foreach ($deactivate_reasons as $deactivate_reasons) { ?>
                                            <div class="radio" class="mo_boot_p-2 mo_boot_ml-2">
                                                <label for="<?php echo $deactivate_reasons; ?>">
                                                    <input type="radio" name="deactivate_plugin" value="<?php echo $deactivate_reasons; ?>" required>
                                                    <?php echo $deactivate_reasons; ?></label>
                                            </div>
    
                                            <?php } ?>
                                            <br>
    
                                            <textarea id="query_feedback" name="query_feedback" rows="4" class="mo-form-control-textarea mo_boot_mb-3" cols="50" placeholder="Write your query here"></textarea>
                                            <tr>
                                                <td><strong>Email<span style="color: #ff0000;">*</span>:</strong></td>
                                                <td><input type="email" name="feedback_email" required value="<?php echo $feedback_email; ?>" placeholder="Enter email to contact." class="mo-form-control"/></td>
                                            </tr>
    
                                            <?php
                                            foreach ($tpostData['cid'] as $key) { ?>
                                                <input type="hidden" name="result[]" value=<?php echo $key ?>>
                                            <?php } ?>
                                            <br><br>
                                            <div class="mojsp_modal-footer" class="mo_boot_text-center">
                                                <input type="submit" name="miniorange_feedback_submit" class="mo_boot_btn mo_heading_export_btn mo_boot_p-2 mo_boot_col-12" value="Submit"/>
                                            </div>
                                    </div>
                                </form>
                                <form name="f" method="post" action="" id="mojspfree_feedback_form_close" class="mo_boot_mt-3">
                                    <input type="hidden" name="mojspfree_skip_feedback" value="mojspfree_skip_feedback"/>
                                    <div class="mo_boot_text-center">
                                        <button class="mo_boot_btn mo_heading_export_btn mo_boot_col-12 mo_boot_p-2" onClick="skipSCIMForm()">Skip Feedback</button>
                                    </div>
                                    <?php
                                        foreach ($tpostData['cid'] as $key) { ?>
                                            <input type="hidden" name="result[]" value=<?php echo $key ?>>
                                        <?php }
                                    ?>
                                </form>
                            </div>
                            <script src="https://code.jquery.com/jquery-3.6.3.js"></script>
                            <script>
                                jQuery('input:radio[name="deactivate_plugin"]').click(function () {
                                    var reason = jQuery(this).val();
                                    jQuery('#query_feedback').removeAttr('required')
                                    if (reason === 'Confusing Interface') {
                                        jQuery('#query_feedback').attr("placeholder",'Can you please describe the issue in detail?');
                                    } else if (reason === 'Does not have the features I am looking for?') {
                                        jQuery('#query_feedback').attr("placeholder", 'Let us know what feature are you looking for');
                                    } else if (reason === 'I found a better plugin'){
                                        jQuery('#query_feedback').attr("placeholder", 'Can you please name that plugin which one you feel better?');
                                    }else if (reason === 'Not working'){
                                        jQuery('#query_feedback').attr("placeholder", 'Can you please let us know which plugin part you find not working?');
                                    } else if (reason === 'Other Reasons:' || reason === 'It is a temporary deactivation' ) {
                                        jQuery('#query_feedback').attr("placeholder", 'Can you let us know the reason for deactivation?');
                                        jQuery('#query_feedback').prop('required', true);
                                    } else if (reason === 'Bugs in the plugin') {
                                        jQuery('#query_feedback').attr("placeholder", 'Can you let us know the issue your facing so that we can improve the component.');
                                    }
                                });

                                function skipSCIMForm(){
                                    jQuery('#mojspfree_feedback_form_close').submit();
                                }
                            </script>
                            <?php
                            exit;
                        }
                    }
                }
            }
        }
    }
}