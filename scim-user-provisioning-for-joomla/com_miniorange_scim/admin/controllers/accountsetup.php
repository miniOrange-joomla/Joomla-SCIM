<?php

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class Miniorange_scimControllerAccountsetup extends FormController
{
    function __construct()
    {
        $this->view_list = 'accountsetup';
        parent::__construct();
    }

    function create_bearer_token(){
        MoScimUtilitiesClient::create_bearer_token();
        $this->setRedirect('index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=scimsettings',Text::_('COM_MINIORANGE_SCIM_TOKEN_GENERATE'));
    }

    function saveAttributeMapping(){
        $app = Factory::getApplication();
        $post = $app->input->post->getArray();

        if( count($post)==0 ){
            $app->redirect("index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=scimmapping");
            return;
        }

        $userProfileAttributeNames  = isset($post['user_profile_attr_name'])?$post['user_profile_attr_name']:array();
        $userProfileAttributeValues = isset($post['user_profile_attr_value'])?$post['user_profile_attr_value']:array();
        $profileAttributesMap       = MoScimUtilitiesClient::customCombineArray($userProfileAttributeNames,$userProfileAttributeValues);
        $conditions = array('id'=>1);
        $fields     = array("moScimAttributeMap"=>json_encode(array("profile"=>$profileAttributesMap)));

        MoScimUtilitiesClient::miniScimUpdateDb('#__miniorange_scim_details',$fields,$conditions);
        $message = Text::_('COM_MINIORANGE_SCIM_ATTR_MAPPING_UPDATED');
        $this->setRedirect('index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=scimmapping',$message );
    }



    function emptyPostCheck($post,$page){
        $app = Factory::getApplication();
	    $pluginPages = array(
	        'accountsettings'=>'index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=plugin_overview',
             'scimConfig'=>'index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=scimsettings'
        );
	    if(empty($post))
        {
            $app->redirect($pluginPages[$page]);
            return;
        }
    }


    function contactUs()
    {
        $post = Factory::getApplication()->input->post->getArray();
        $this->emptyPostCheck($post,'account');
        $accountPage = 'index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=plugin_overview';

        if (isset($post['query_phone']) && $post['query_phone'] != NULL) {
            $pgone_num_validate = preg_match("/^\+?[0-9]+$/", $post['query_phone']);
            if (!$pgone_num_validate) {
                $this->setRedirect($accountPage, Text::_('COM_MINIORANGE_SCIM_INVALID_PHONE'), 'error');
                return;
            }
        }

        if (MoScimUtilitiesClient::check_empty_or_null($post['query_email'])) {
            $this->setRedirect($accountPage, Text::_('COM_MINIORANGE_SCIM_SUBMIT_QUERY_WITH_EMAIL'), 'error');
            return;
        } else if (MoScimUtilitiesClient::check_empty_or_null(trim($post['mo_scim_textfield'] || trim($post['mo_scim_textfield'])))) {
            $this->setRedirect($accountPage, Text::_('COM_MINIORANGE_SCIM_QUERY_CANNOT_BE_EMPTY'), 'error');
            return;
        } else {
            $query = $post['mo_scim_textfield'];
            $email = $post['query_email'];
            $phone = $post['query_phone'];
            $contact_us = new MoScimCustomer();
            $submited = json_decode($contact_us->submit_contact_us($email, $phone, $query), true);
            if (json_last_error() == JSON_ERROR_NONE) {
                if (is_array($submited) && array_key_exists('status', $submited) && $submited['status'] == 'ERROR') {
                    $this->setRedirect($accountPage, $submited['message'], 'error');
                    return;
                } else {
                    if ($submited == false) {
                        $this->setRedirect($accountPage, Text::_('COM_MINIORANGE_SCIM_QUERY_SUBMISSION_FAILED'), 'error');          
                        return;
                    } else {
                        $this->setRedirect($accountPage, Text::_('COM_MINIORANGE_SCIM_THANKS_FOR_CONTACTING'));
                    }
                }
            }
        }
    }

    function requestForTrialPlan()
    {
        $app = Factory::getApplication();
        $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
        $post = ($input && $input->post) ? $input->post->getArray() : [];
        if ((!isset($post['email'])) || (!isset($post['plan'])) || (!isset($post['description']))) {
            $this->setRedirect('index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=trial_request',Text::_('COM_MINIORANGE_SCIM_MSG_A'), 'error');
            return;
        }
        $email = $post['email'];
        $plan = $post['plan'];
        $description = trim($post['description']);
        $demo = 'Trial';
        
        // Handle phone number data
        $phone = isset($post['query_phone']) ? $post['query_phone'] : '';
        $country_code = isset($post['country_code']) ? $post['country_code'] : '';
        
        // Combine country code and phone number if both are provided
        if (!empty($country_code) && !empty($phone)) {
            $phone = $country_code . ' ' . $phone;
        }
        
        if ( empty($email) ||empty($plan) || empty($description)) {
            $this->setRedirect('index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=trial_request', Text::_('COM_MINIORANGE_SCIM_MSG_A'), 'error');
            return;
        }

        $customer = new MoScimCustomer();
        $response = json_decode($customer->request_for_trial($email, $plan, $demo, $description, $phone));

        if ($response->status != 'ERROR')
        {
            $msg=($demo == 'Demo')? Text::sprintf('COM_MINIORANGE_SCIM_MSG_B',$email):Text::sprintf('COM_MINIORANGE_SCIM_MSG_C',$email);
            $this->setRedirect('index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=trial_request', $msg);
        }
        else {
            $this->setRedirect('index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=trial_request',Text::_('COM_MINIORANGE_SCIM_MSG_D'), 'error');
            return;
        }
    }
}