<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Webservices.miniOrnage
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 defined('_JEXEC') or die;

 use Joomla\CMS\Plugin\CMSPlugin;
 use Joomla\CMS\Router\ApiRouter;
 use Joomla\Router\Route;
 use Joomla\CMS\User\User;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use Joomla\CMS\String\PunycodeHelper;
jimport( 'joomla.plugin.plugin' );
jimport('miniorangescimplugin.utility.MoScimUtilitiesClient');
jimport('miniorangescimplugin.utility.MoScimUtilitiesServer');
jimport("miniorangescimplugin.moscim.moScimConstants");
jimport("miniorangescimplugin.moscim.moScimRequest");
jimport("miniorangescimplugin.moscim.moScimUser");

class PlgWebservicesMiniorangescim extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_content's API's routes in the application
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
    public function onBeforeApiRoute(&$router)
    {
        $router->createCRUDRoutes(
            'v1/mini/miniorangescim',       // API path
            'miniorange_scim',               // View name
            ['component' => 'com_miniorange_scim'] // Correct component name
        );
        $this->createContentHistoryRoutes($router);
    }    

	/**
	 * Create fields routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createFieldsRoutes(&$router)
	{
		
	}

	/**
	 * Create contenthistory routes
	 *
	 * @param   ApiRouter  &$router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function createContentHistoryRoutes(&$router)
	{
		$joomla_version=MoScimUtilitiesClient::getJoomlaCmsVersion();
        if($joomla_version>=4)
        {
            $uri = Uri::getInstance();
        
             if(strpos($uri,moScimConstants::SCIM_EXTENSION)){
                $moScimRequest = new moScimRequest($uri);
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
        }
       
	}

}

 
	