<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_scim
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

require_once JPATH_COMPONENT . '/helpers/mo_customer_setup.php';

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_miniorange_pagerestriction'))
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');
 
// Get an instance of the controller prefixed by JoomlaIdp
$controller = BaseController::getInstance('miniorange_scim');

// Perform the Request task
// Backward compatibility for Joomla 3/4/5
$app = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$task = ($input && method_exists($input, 'get')) ? $input->get('task') : '';
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();