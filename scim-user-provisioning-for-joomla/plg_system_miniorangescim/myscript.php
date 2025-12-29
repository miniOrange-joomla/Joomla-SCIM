<?php
// No direct access to this file
//defined('_JEXEC') or die('Restricted access');

/**
 * @package     Joomla.System
 * @subpackage  plg_system_miniorangescim
 * @author      miniOrange Security Software Pvt. Ltd.
 * @copyright   Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license     GNU General Public License version 3; see LICENSE.txt
 * @contact     info@xecurify.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;


class plgSystemMiniorangescimInstallerScript
{
    /**
     * This method is called after a component is installed.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function install($parent) 
    {
          $db  = Factory::getDbo();
          $query = $db->getQuery(true);
          $query->update('#__extensions');
          $query->set($db->quoteName('enabled') . ' = 1');
          $query->where($db->quoteName('element') . ' = ' . $db->quote('miniorangescim'));
          $query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
          $db->setQuery($query);
          $db->execute();            
    }

    /**
     * This method is called after a component is uninstalled.
     *
     * @param  \stdClass $parent - Parent object calling this method.
     *
     * @return void
     */
    public function uninstall($parent) 
    {
    }

    /**
     * This method is called after a component is updated.
     *
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function update($parent) 
    {
    }

    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {
        //echo '<p>' . Text::_('COM_HELLOWORLD_PREFLIGHT_' . $type . '_TEXT') . '</p>';
    }

    /**
     * Runs right after any installation action is performed on the component.
     *
     * @param  string    $type   - Type of PostFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    function postflight($type, $parent) 
    {
       // echo '<p>' . Text::_('COM_HELLOWORLD_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
    }
}