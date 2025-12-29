<?php
// No direct access to this file
//defined('_JEXEC') or die('Restricted access');

/**
 * Script file of miniorangescim plugin.
 
 * This class will be called by Joomla!'s installer, if specified in your component's
 * manifest file, and is used for custom automation actions in its installation process.
 *
 * In order to use this automation script, you should reference it in your component's
 * manifest file as follows:
 * <scriptfile>script.php</scriptfile>
 
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;


class PlgWebservicesMiniorangescimInstallerScript
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
}