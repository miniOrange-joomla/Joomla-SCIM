<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_oauth
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * Account Setup View
 *
 * @since  0.0.1
 */
class miniorange_scimViewAccountSetup extends HtmlView
{
	function display($tpl = null)
	{
		// Get data from the model
		// $this->lists		= $this->get('List');
		// //$this->pagination	= $this->get('Pagination');
 
		// // Check for errors.
		// if (count($errors = $this->get('Errors')))
		// {
		// 	Factory::getApplication()->enqueueMessage(500, implode('<br />', $errors));
 
		// 	return false;
		// }
		$this->setLayout('accountsetup');
		// Set the toolbar
		$this->addToolBar();
 
		// Display the template
		parent::display($tpl);
	}
 
	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	 protected function addToolBar()
	 {
		ToolbarHelper::title("mini<span style='color:orange;margin-right:0px'><strong>O</strong></span>range SCIM Server Free",'mo_scim_logo mo_scim_icon');
	 }
	
	
}