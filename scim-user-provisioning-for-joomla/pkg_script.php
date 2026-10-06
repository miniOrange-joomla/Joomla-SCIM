<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * Script file of miniorange_saml_system_plugin.
 *
 * The name of this class is dependent on the component being installed.
 * The class name should have the component's name, directly followed by
 * the text InstallerScript (ex:. com_helloWorldInstallerScript).
 *
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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

class pkg_miniorange_scimInstallerScript
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
        //echo '<p>' . Text::_('COM_HELLOWORLD_UNINSTALL_TEXT') . '</p>';
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
        //echo '<p>' . Text::sprintf('COM_HELLOWORLD_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';
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
       if ($type == 'uninstall') {
        return true;
        }
       $this->showInstallMessage('');
    }

    protected function showInstallMessage($messages=array()) {
        ?>
        <style>
            .mo_tfa_install_btns{
                background-color: #001b4c;
                color: white !important;
            }

            .mo_tfa_install_btns:hover{
                color: white !important;
                background-color: #001b4c !important;
            }

            .btn-scim:hover
            {
                border-color: black;
            }

            :root[data-color-scheme=dark] 
            {
                .mo_tfa_install_btns{
                    background-color:#33383f;
                }
                .mo_tfa_install_btns:hover{
                    color: white !important;
                }
            }
        </style>

        <p>Plugin package for miniOrange SCIM User Provisioning</p>
        <p>Our plugin is compatible with Joomla 3, 4 , 5 as well as 6.</p>
        <h4>What this plugin does?</h4>
        <p>The Joomla SCIM User Provisioning plugin enables automatic user creation and synchronization from your Identity Provider (IdP) to Joomla.</p>
    	<div class="mo-row">
            <a class="btn btn-scim mo_tfa_install_btns" onClick="window.location.reload();" href="index.php?option=com_miniorange_scim&tab-panel=plugin_overview">Start Using miniOrange SCIM User Provisioning</a>
            <a class="btn btn-secondary mo_tfa_install_btns" href="https://plugins.miniorange.com/joomla-scim-user-provisioning" target="_blank">Read the miniOrange documents</a>
		    <a class="btn btn-secondary mo_tfa_install_btns" href="https://www.miniorange.com/contact" target="_blank">Get Support!</a>
        </div>
        <?php
    }
}