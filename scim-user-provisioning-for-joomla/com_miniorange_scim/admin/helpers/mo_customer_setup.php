<?php
/** Copyright (C) 2015  miniOrange
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.

* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.

* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>
* @package 		miniOrange Scim
* @license		http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/
/**
* This library is miniOrange Authentication Service. 
* Contains Request Calls to Customer service.
**/

defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Version;

jimport('miniorangescimplugin.utility.MoScimUtilitiesClient');
class MoScimCustomer
{
    public $email;
    public $phone;
    private $defaultCustomerKey = "16555";
    private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";

    function submit_contact_us( $q_email, $q_phone, $query ) {
        if(!MoScimUtilitiesClient::is_curl_installed()) {
            return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
        $hostname = MoScimUtilitiesClient::getHostname();
        $url = 'https://login.xecurify.com/moas/api/notify/send';
        $current_user =  Factory::getUser();
        $phpVersion = phpversion();
        $jVersion = new Version;
        $jCmsVersion = $jVersion->getShortVersion();
        $moPluginVersion = MoScimUtilitiesClient::GetPluginVersion();
        $customerKey =  $this->defaultCustomerKey;

        $pluginInfo = '[Joomla SCIM Free Plugin | '.$phpVersion. ' | '.$jCmsVersion.' | ' . $moPluginVersion.'] ' . $query;
        $subject = "Query for miniOrange Joomla SCIM User Provisioning Free -" . $q_email;
        $bccEmail='joomlasupport@xecurify.com';
        $content = '<div >Hello, <br><br>
        <strong>Company :</strong><a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
        <strong>Email :</strong><a href="mailto:' . $q_email . '" target="_blank">' . $q_email . '</a><br><br>
        <strong>Plugin Info: </strong>'.$pluginInfo.'<br><br>
        <strong>Query: </strong>' . $query . '</div>';
      

        $fields = array(
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $q_email,
                'bccEmail' 		=> $bccEmail,
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> $bccEmail,
                'toName' 		=> $bccEmail,
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
        $field_string = json_encode( $fields );
        return self::curl_call($url, $field_string );
    }

    public function submit_feedback_form($email, $phone, $query,$task)
    {

        if(!MoScimUtilitiesClient::is_curl_installed()) {
            return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }

        $url = 'https://login.xecurify.com/moas/api/notify/send';

        $current_user =  Factory::getUser();
        $phpVersion = phpversion();
        $jVersion = new Version;
        $jCmsVersion = $jVersion->getShortVersion();
        $moPluginVersion = MoScimUtilitiesClient::GetPluginVersion();
        $moServerType = MoSCIMUtility::getServerType();
        $fromEmail = $email;

        //Get Installed Miniorange SAML IDP plugin version
        $customer_details=MoScimUtilitiesClient::getCustomerDetails();
        $plugin_config =MoScimUtilitiesClient::miniScimFetchDb('#__miniorange_scim_details', array('id' => 1));
        $scimConfiguration = ($plugin_config['scim_configuration']==true)? 'Successful': 'Unsuccessful'; 
        if($task=='scim_config')
        {
            $user_creation='Not done';
        }else
        {   
            $user_creation = ($plugin_config['user_creation']==true)? 'Successful': 'Unsuccessful';
        }
        $users=isset($plugin_config['users'])?$plugin_config['users']:0;
        $ad_email       = isset($plugin_config ['email']) ? $plugin_config ['email'] : '';
        $query1 = '[ Joomla '.$jCmsVersion.' | '.$moPluginVersion.' | PHP ' . $phpVersion.' | Server Type '. $moServerType .'] ';
        $ccEmail= 'nutan.barad@xecurify.com'; 
        $bccEmail='pritee.shinde@xecurify.com';
        $content = '<div >Hello, <br><br><strong>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" ></strong>' . $_SERVER['SERVER_NAME'] . '</a><br><br><strong>Phone Number :<strong>' . $phone . '<br><br><strong>Admin Email :<a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a></strong><br><br><strong>Email :<a href="mailto:' . $ad_email . '" target="_blank">' . $ad_email . '</a></strong> <br><br> <strong>SCIM Configuration:</strong> '.$scimConfiguration.'<br><br><strong>User Creation:</strong>'.$user_creation.'<strong><br><br>Possible Cause:</strong> '.$query .'<br><br><strong> Users Created: </strong>' . $users . '<br><br><strong> System Information: </strong>' . $query1 . '</div>';
        $subject = "miniOrange Joomla SCIM User Provisioning [Free] for Efficiency";
        

        $fields = array(
            'customerKey' => $this->defaultCustomerKey,
            'sendEmail' => true,
            'email' => array(
                'customerKey' 	=> $this->defaultCustomerKey,
                'fromEmail' 	=> $fromEmail,
                'bccEmail' 		=> $bccEmail,
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> $ccEmail,
                'toName' 		=> $bccEmail,
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
        $field_string = json_encode($fields);

        return self::curl_call($url, $field_string );
    }

    public static function submit_uninstall_feedback_form($email, $phone, $query,$cause)
    {
        $url = 'https://login.xecurify.com/moas/api/notify/send';

        $customerKey = "16555";
        $apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        $fromEmail = $email;
        $phpVersion = phpversion();
        $dVar=new JConfig();
        $check_email = $dVar->mailfrom;
        $jCmsVersion =  MoScimUtilitiesClient::getJoomlaCmsVersion();;
        $moPluginVersion =  MoScimUtilitiesClient::GetPluginVersion();
        $os_version    = MoSCIMUtility::mo_scim_get_operating_system();
        $pluginName    = 'SCIM User Provisioning Free Plugin';
        $admin_email   = !empty($email)?$email:$check_email;
        
        $query1 = '['.$pluginName.' | Plugin '.$moPluginVersion.' | PHP ' . $phpVersion.' | Joomla ' . $jCmsVersion.' | OS ' . $os_version.'] ';
        
        $ccEmail = 'joomlasupport@xecurify.com';
        $bccEmail = 'joomlasupport@xecurify.com';
        $content = '<div>Hello, <br><br>'
                . '<strong>Company: </strong><a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank">' . $_SERVER['SERVER_NAME'] . '</a><br><br>'
                . '<strong>Phone Number: </strong>' . $phone . '<br><br>'
                . '<strong>Admin Email: </strong><a href="mailto:' .$admin_email . '" target="_blank">' . $admin_email . '</a><br><br>'
                . '<strong>Feedback: </strong>' . $query . '<br><br>'
                . '<strong>Additional Details: </strong>' . $cause . '<br><br>'
                . '<strong>System Information: </strong>' . $query1 
                . '</div>';
        
        $subject = "Feedback for miniOrange Joomla SCIM User Provisioning Free Plugin";

        $fields = array(
            'customerKey' => $customerKey,
            'sendEmail' => true,
            'email' => array(
                'customerKey' 	=> $customerKey,
                'fromEmail' 	=> $fromEmail,
                'bccEmail' 		=> $bccEmail,
                'fromName' 		=> 'miniOrange',
                'toEmail' 		=> $ccEmail,
                'toName' 		=> $bccEmail,
                'subject' 		=> $subject,
                'content' 		=> $content
            ),
        );
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string);
    }

    function request_for_trial($email, $plan,$demo,$description = '', $phone = '')
    {
        $hostname = MoScimUtilitiesClient::getHostname();
        $url = $hostname . '/moas/api/notify/send';
        $customerKey = "16555";
        $apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        $fromEmail = $email;
        $subject = 'miniOrange Joomla SCIM User Provisioning request for trial';
        $phpVersion = phpversion();
        $jCmsVersion = MoScimUtilitiesClient::getJoomlaCmsVersion();
        $moPluginVersion =  MoScimUtilitiesClient::GetPluginVersion();

        $pluginInfo = '[Plugin '.$moPluginVersion.'| Joomla ' . $jCmsVersion.' | PHP ' . $phpVersion.'] : ' .$plan;

        $phoneInfo = !empty($phone) ? '<strong>Phone Number: </strong>' . $phone . '<br><br>' : '';

        $content = '<div >Hello, <br>
                        <br><strong>Company :</strong><a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
                        <strong>Email :</strong><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br>
                        ' . $phoneInfo . '
                        <strong>Plugin Info: </strong>'.$pluginInfo.'<br><br>
                        <strong>Description: </strong>' . $description . '</div>';

        $fields = array(
            'customerKey' => $this->defaultCustomerKey,
            'sendEmail' => true,
            'email' => array(
                'customerKey' =>$this->defaultCustomerKey,
                'fromEmail' => $fromEmail,
                'fromName' => 'miniOrange',
                'toEmail' => 'joomlasupport@xecurify.com',
                'toName' => 'joomlasupport@xecurify.com',
                'subject' => $subject,
                'content' => $content
            ),
        );
        $field_string = json_encode($fields);

        return self::curl_call($url,$field_string,0);
    }

    public static function curl_call($url,$field_string)
    {
        $ch = curl_init($url);
        $customerKey = '16555';
        $apiKey = 'fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq';
        /* Current time in milliseconds since midnight, January 1, 1970 UTC. */
        $currentTimeInMillis = round(microtime(true) * 1000);
     
        /* Creating the Hash using SHA-512 algorithm */
        $stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue = hash("sha512", $stringToHash);

        $customerKeyHeader = "Customer-Key: " . $customerKey;
        $timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader = "Authorization: " . $hashValue;
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    # required for https urls

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Request Error:' . curl_error($ch);
            exit();
        }
        curl_close($ch);
        return $content;
    }

}