<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

/**
 * Routing class of com_content
 *
 * @since  3.3
 */
jimport("miniorangescimplugin.moscim.moScimConstants");
jimport("miniorangescimplugin.moscim.moScimRequest");
jimport('miniorangescimplugin.utility.MoScimUtilitiesServer');
class miniorange_scimRouter extends JComponentRouterView
{
	
	protected $noIDs = false;

    public function __construct(CMSApplication $app = null, AbstractMenu $menu = null)
	{

		$category = new RouterViewConfiguration('category');
		$category->setKey('id')->setNestable();
		$this->registerView($category);
		$scim = new RouterViewConfiguration('miniorange_scim');
		$scim->setKey('id')->setParent($category, 'catid');
		$this->registerView($scim);

		parent::__construct($app, $menu);
		$uri = Uri::getInstance();

        if(strpos($uri,moScimConstants::SCIM_EXTENSION)){
            $moScimRequest = new moScimRequest($uri);
            $event = JEventDispatcher::getInstance();

            $tokenFromHeader = MoScimUtilitiesServer::get_token_from_header();
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
                MoScimUtilitiesServer::sendAuthorizationFailedResponse();
            }

            if($moScimRequest->getRequestEndpoint()!==moScimConstants::NOT_SUPPORTED_ENDPOINT){
                if(is_null($moScimRequest->getResourceId())){
                    $theHandlerFunction = "moScim".$moScimRequest->getRequestEndpoint();
                }
                else{
                    $theHandlerFunction = "moScim".$moScimRequest->getRequestEndpoint()."ById";
                }
                MoScimUtilitiesServer::$theHandlerFunction($moScimRequest);

            }
            else
            {
                MoScimUtilitiesClient::saveSCIMConfig('#__miniorange_scim_details','scim_configuration',true);
                MoScimUtilitiesClient::keepRecords('No Error','scim_config');
            }
            exit;
        }
		$this->attachRule(new MenuRules($this));

		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}
}