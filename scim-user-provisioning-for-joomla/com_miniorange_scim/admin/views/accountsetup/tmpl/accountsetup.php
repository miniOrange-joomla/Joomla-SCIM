<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_miniorange_scim
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

jimport('miniorangescimplugin.utility.MoScimUtilitiesClient');
jimport("miniorangescimplugin.moscim.moScimuser");

HTMLHelper::_('jquery.framework');

// Joomla 3/4/5 compatible document handling
$document = Factory::getDocument();

$document->addStyleSheet(Uri::base() . 'components/com_miniorange_scim/assets/css/miniorange_boot.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_scim/assets/css/miniorange_scim.css');
$document->addScript(Uri::base() . 'components/com_miniorange_scim/assets/js/moScimMapping.js');
$document->addScript(Uri::base() . 'components/com_miniorange_scim/assets/js/utility.js');
$document->addScript(Uri::base() . 'components/com_miniorange_scim/assets/js/bootstrap.js');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css');

// Backward compatibility for Joomla 3/4/5
$app = Factory::getApplication();
$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
$scim_active_tab = 'plugin_overview';
$active_tab = ($input && $input->get) ? $input->get->getArray() : [];
if (isset($active_tab['tab-panel']) && !empty($active_tab['tab-panel'])) {
    $scim_active_tab = $active_tab['tab-panel'];
}

?>
<?php
$json_file_path = Uri::base() . 'components/com_miniorange_scim/assets/json/tabs.json';

$json_data = @file_get_contents($json_file_path);

if ($json_data === false) {
    error_log('Error: Unable to read JSON file at ' . $json_file_path);
    $tabs = []; 
} else {
    $tabs = json_decode($json_data, true);
    if ($tabs === null) {
        error_log('Error: JSON decoding failed for file ' . $json_file_path);
        $tabs = []; 
    }
}
?>
    <div class="mo_boot_row mo_boot_p-3">
        <div class="mo_boot_col-sm-12">
            <a class="mo_boot_btn mo_boot_px-4 mo_boot_py-1 mo_heading_export_btn" href="<?php echo Uri::base()?>index.php?option=com_miniorange_scim&tab-panel=support">
                <i class="fa fa-envelope mo_boot_mx-1"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_SUPPORT_BTN');?>
            </a>
            <a class="mo_boot_btn mo_boot_px-4 mo_boot_py-1 mo_heading_export_btn" href="index.php?option=com_miniorange_scim&view=accountsetup&tab-panel=trial_request">
                <i class="fa fa-envelope mo_boot_mx-1"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_TRIAL_REQUEST');?></a>
        </div>
    </div>
    <div class="mo_boot_container-fluid mo_boot_m-0 mo_boot_p-0">
        <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0">
            <div class="mo_boot_col-sm-12 mo_boot_m-0 mo_boot_p-0 mo_scim_navbar_wrapper">
                <?php foreach ($tabs as $id => $tab): ?>
                    <a id="<?php echo $id; ?>tab" 
                       class="mo_boot_py-3 mo_nav-tab mo_scim_dark_white <?php echo $scim_active_tab == $id ? 'mo_nav_tab_active' : ''; ?>" 
                       href="#<?php echo $id; ?>" 
                       onclick="add_css_tab('#<?php echo $id; ?>tab');" 
                       data-toggle="tab">
                       <span class="mo_nav_tab_icon"><i class="<?php echo $tab['icon']; ?>"></i></span>
                       <span class="tab-label"><?php echo Text::_($tab['label']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>


    <div class="mo_boot_container-fluid mo_boot_mt-2 mo_scim_tab_content_wrapper">
        <div class="tab-content" id="myTabContent">
            <div class="mo_boot_col-sm-12 mo_boot_p-0 mo_boot_m-0 tab-pane <?php echo $scim_active_tab == 'plugin_overview' ? 'active' : ''; ?>" id="plugin_overview">
                <div class="mo_boot_row mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_scim_tab_pane_content">
                        <?php plugin_overview(); ?>
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_p-0 mo_boot_m-0 tab-pane <?php echo $scim_active_tab == 'scimsettings' ? 'active' : ''; ?>" id="scimsettings">
                <div class="mo_boot_row mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_scim_tab_pane_content">
                        <?php scimconfig(); ?>
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_p-0 mo_boot_m-0 tab-pane <?php echo $scim_active_tab == 'scimmapping' ? 'active' : ''; ?>" id="scimmapping">
                <div class="mo_boot_row mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_scim_tab_pane_content">
                        <?php scimMapping(); ?>
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_p-0 mo_boot_m-0 tab-pane <?php echo $scim_active_tab == 'scimrolemapping' ? 'active' : ''; ?>" id="scimrolemapping">
                <div class="mo_boot_row mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_scim_tab_pane_content">
                        <?php scimRoleMapping(); ?>
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_p-0 mo_boot_m-0 tab-pane <?php echo $scim_active_tab == 'scimadvancesettings' ? 'active' : ''; ?>" id="scimadvancesettings">
                <div class="mo_boot_row mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_scim_tab_pane_content">
                        <?php scimAdvanceSettings(); ?>
                    </div>
                </div>
            </div>
            <div id="trial_request" class="tab-pane mo_boot_col-sm-12 mo_boot_p-0 <?php echo $scim_active_tab == 'trial_request' ? 'active' : ''; ?>">
                <div class="mo_boot_row mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_scim_tab_pane_content">
                        <?php trial_request(); ?>
                    </div>
                </div>
            </div>
            <div id="support" class="tab-pane mo_boot_col-sm-12 mo_boot_p-0 <?php echo $scim_active_tab == 'support' ? 'active' : ''; ?>">
                <div class="mo_boot_row mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_scim_tab_pane_content">
                        <?php mo_scim_support(); ?>
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_p-0 mo_boot_m-0 tab-pane <?php echo $scim_active_tab == 'upgradeplans' ? 'active' : ''; ?>" id="upgradeplans">
                <div class="mo_boot_row mo_boot_m-0">
                    <div class="mo_boot_col-sm-12 mo_boot_p-3 mo_scim_tab_pane_content">
                        <?php mo_scim_licensing_page(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php


function plugin_overview()
{
    ?>
    <div class="mo_boot_container-fluid mo_boot_m-0 mo_boot_p-0">
        <div class="mo_boot_row mo_boot_m-0">
            <div class="mo_boot_col-12">
                <h3><?php echo Text::_('COM_MINIORANGE_SCIM_PLUGIN'); ?></h3>
                <hr>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-lg-7 mo_boot_col-md-12 mo_boot_justify-content-center">
                        <p class="mo_boot_text-justify"><?php echo Text::_('COM_MINIORANGE_SCIM_DESCRIPTION1'); ?></p>
                        <p class="mo_boot_text-justify"><b><?php echo Text::_('COM_MINIORANGE_SCIM_DESCRIPTION2'); ?></b> <?php echo Text::_('COM_MINIORANGE_SCIM_DESCRIPTION3'); ?></p>
                        <p class="mo_boot_text-justify"><?php echo Text::_('COM_MINIORANGE_SCIM_DESCRIPTION4'); ?></p>
                    </div>
                    <div class="mo_boot_col-lg-5 mo_boot_col-md-12 mo_boot_text-center mo_boot_mt-3">
                        <img src="<?php echo Uri::base() . 'components/com_miniorange_scim/assets/images/joomla-scim-user-provisioning-hero.png'; ?>" alt="SCIM User Provisioning" class="mo_scim_overview_image">
                    </div>
                </div>
                <div class="mo_boot_row mo_boot_mt-3">
                    <div class="mo_boot_col-lg-12 mo_boot_col-md-12 mo_scim_highlight">
                        <p class="mo_boot_text-justify"><?php echo Text::_('COM_MINIORANGE_SCIM_DESCRIPTION5'); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function scimconfig()
{
    $groups = MoScimUtilitiesClient::loadGroups();
    $scimConfig = MoScimUtilitiesClient::get_scim_config();
    if ($scimConfig['bearer_token'] == "") {
        $bearer_token = MoScimUtilitiesClient::create_bearer_token();
    } else {
        $bearer_token = $scimConfig['bearer_token'];
    }
    $premiumDisabled = 'enabled';
    $selectedRoles = json_decode($scimConfig['default_roles'], true);
    $moScimParentRole = $scimConfig['moScimParentRole'];
    $scim_url = Uri::root() . 'api/index.php/v1/mini/miniorangescim';

    ?>
    <div class="mo_boot_col-sm-12">
        <h4 class="form-head mo_boot_mb-4"> <?php echo Text::_('COM_MINIORANGE_SCIM_CONFIG'); ?>  </h4>
        <input type="hidden" name="option" value=""/>
        <div class="mo_scim_section_box mo_boot_mb-3 mo_boot_row">
            <div class="mo_scim_confg_idp mo_boot_col-sm-12">
                <div class="mo_scim_ip mo_boot_col-sm-4">
                    <strong><?php echo Text::_('COM_MINIORANGE_SCIM_IDP_PROVIDER'); ?>  </strong>
                </div>
                <div class="mo_scim_select_idp mo_boot_col-sm-8">
                    <select name="idpguide" id="idpguide" class="mo_boot_col-sm-12 mo-form-control mo-form-control-select mo_scim_config_idp scim-table mo_scim_textfield">
                        <option value="" selected="" disabled=""><?php echo Text::_('COM_MINIORANGE_SCIM_SELECT_IDP_GUIDE'); ?> </option>
                        <option value="https://plugins.miniorange.com/joomla-azure-ad-user-sync-provisioning">Azure AD</option>
                        <option value="https://plugins.miniorange.com/joomla-onelogin-user-sync-provisioning">One Login</option>
                        <option value="https://plugins.miniorange.com/joomla-okta-user-sync-provisioning">Okta</option>
                        <option value="https://plugins.miniorange.com/joomla-scim-user-provisioning-with-jumpcloud">Jump Cloud</option>
                        <option value="https://plugins.miniorange.com/scim-user-provisioning-using-google-apps-into-joomla">Google Apps</option>
                        <option value="https://plugins.miniorange.com/wordpress-scim-user-provisioning-with-pingone">Ping One</option>
                        <option value="https://plugins.miniorange.com/scim-user-provisioning-using-miniorange-into-joomla">miniOrange</option>
                        <option value="https://plugins.miniorange.com/scim-user-provisioning-using-centrify-into-joomla">Centrify</option>
                        <option value="https://plugins.miniorange.com/scim-user-provisioning-using-cyberark-into-joomla">CyberArk</option>
                        <option value="https://plugins.miniorange.com/joomla-scim-setup-guides">Others</option>
                    </select>
                </div>
            </div>
        </div>
        <form name="f" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_scim&view=accountsetup&task=accountsetup.create_bearer_token'); ?>" class="mo_scim_form_wrapper">
            <div class="mo_boot_col-sm-12 table-responsive mo_boot_mt-3">
                <table class="mo_scim_table mo_scim_table_bordered">
                    <tr>
                        <td><b><?php echo Text::_('COM_MINIORANGE_SCIM_BASE_URL'); ?>:</b></td>
                        <td><span id="base_url"><?php echo $scim_url; ?></span> <em class="fa fa-lg fa-copy mo_copy " onclick="copyToClipboard('#base_url');"> </em>
                        </td>
                    </tr>
                    <tr>
                        <td><b><?php echo Text::_('COM_MINIORANGE_SCIM_TOKEN'); ?>:</b></td>
                        <td><span id="bearer_token"><?php echo $bearer_token; ?></span> <em class="fa fa-lg fa-copy mo_copy" onclick="copyToClipboard('#bearer_token');"></em></td>
                    </tr>
                </table>
                <div class="mo_boot_col-sm-12 text-center mo_boot_mt-3 mo_boot_mb-5">
                    <input type="submit" value="<?php echo Text::_('COM_MINIORANGE_SCIM_GENERATE'); ?>" class="mo_scim_button mo_boot_btn mo_boot_btn-primary">
                </div>
            
            </div>
            <strong class="mo_scim_note"><?php echo Text::_('COM_MINIORANGE_SCIM_NOTE'); ?></strong><?php echo Text::_('COM_MINIORANGE_SCIM_CONFIG_NOTE1'); ?>
            <a href='<?php echo Uri::base() ?>index.php?option=com_miniorange_scim&tab-panel=scimmapping'><?php echo Text::_('COM_MINIORANGE_SCIM_CONFIG_NOTE2'); ?> </a>.
        </form>

        <div class="mo_boot_col-sm-12 mo_boot_mt-4">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12 mo_boot_alert mo_boot_alert-info">
                    <h4><?php echo Text::_('COM_MINIORANGE_SCIM_INSTRUCTION'); ?></h4>
                    <ol class="mo_scim_note_list">
                        <li><?php echo Text::_('COM_MINIORANGE_SCIM_INSTRUCTION1'); ?></li>
                        <li><?php echo Text::_('COM_MINIORANGE_SCIM_INSTRUCTION2'); ?></li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="mo_scim_operation mo_scim_section_box mo_boot_mt-3">
            <h4 class="form-head"> <?php echo Text::_('COM_MINIORANGE_SCIM_SCIM_OPERATIONS'); ?>  </h4>
            <hr>
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <dl>
                        <dd>
                            <strong><?php echo Text::_('COM_MINIORANGE_SCIM_CREATE'); ?></strong> <?php echo Text::_('COM_MINIORANGE_SCIM_NOTE_CREATE'); ?>
                        </dd>
                        <dd class="mo_boot_alert mo_boot_alert-info mo_boot_mt-1 mo_scim_config_note">
                            <p class="mo_boot_p-1">
                                <strong class="mo_scim_config_note"><?php echo Text::_('COM_MINIORANGE_SCIM_NOTE'); ?></strong><?php echo Text::_('COM_MINIORANGE_SCIM_NOTE_DESC'); ?>
                            </p>
                        </dd>
                        <dd class="mo_boot_mt-3">
                            <strong><?php echo Text::_('COM_MINIORANGE_SCIM_DELETE'); ?></strong> <?php echo Text::_('COM_MINIORANGE_SCIM_DELETE_DESC'); ?>
                        </dd>
                        <dd class="mo_boot_mt-3">
                            <strong><?php echo Text::_('COM_MINIORANGE_SCIM_UPDATE'); ?></strong><?php echo Text::_('COM_MINIORANGE_SCIM_UPDATE_DESC'); ?>
                        </dd>
                        <dd class="mo_boot_mt-3">
                            <strong><?php echo Text::_('COM_MINIORANGE_SCIM_DEACTIVATE'); ?></strong><?php echo Text::_('COM_MINIORANGE_SCIM_DEACTIVATE_DESC'); ?>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function scimMapping()
{
    $mappingDetails = MoScimUtilitiesClient::get_scim_config()['moScimAttributeMap'];
    $mappingDetails = json_decode($mappingDetails, true)['profile'];
    ?>
    <div class="mo_boot_col-sm-12">
        <h4 class="form-head"><?php echo Text::_('COM_MINIORANGE_SCIM_MAPPING'); ?></h4>
        <input type="hidden" name="option" value=""/>
        <form name="advance_attr_mapping_form" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_scim&view=accountsetup&task=accountsetup.saveAttributeMapping'); ?>">
            <div class="mo_boot_container mo_scim_mapping_subhead mo_boot_ml-2 mo_boot_col-sm-12 mo_scim_content_box" id="userProfileAttrDiv">
                <h5><?php echo Text::_('COM_MINIORANGE_SCIM_MAPPING_SUBHEADING'); ?><input type="button" class="mo_boot_btn mo_boot_btn-primary" id="moScimUserProfilePlusButton" value="+"/></h5>
                <p class="mo_boot_alert mo_boot_alert-info"><strong><?php echo Text::_('COM_MINIORANGE_SCIM_NOTE');?></strong> <?php echo Text::_('COM_MINIORANGE_SCIM_MAP_NOTE_DESC');?></p>
                <div class="mo_boot_row  moScimAttributeColumns mo_boot_mt-5">
                    <div><h6><?php echo Text::_('COM_MINIORANGE_SCIM_JOOMLA_ATTRIBUTE');?></h6></div>
                    <div><h6><?php echo Text::_('COM_MINIORANGE_SCIM_CLIENT_ATTRIBUTE');?></h6></div>
                </div>

                <?php
                $counter = 0;
                $allUserAttributes = MoScimUtilitiesClient::getAllAttributesOfUserSchema();
                echo "<input type='hidden' id='moScimUserFieldsFromDb' value='" . json_encode($allUserAttributes) . "'/>";
                $allJoomlaAttributes = array(
                    'u_email' => 'email',
                    'u_username' => 'username'
                );
                $userFieldAttributes = MoScimUtilitiesClient::miniScimFetchDb('#__fields', array('context' => 'com_users.user'), 'loadAssocList', array('name', 'label'));


                foreach ($userFieldAttributes as $value) {
                    $allJoomlaAttributes[$value['name']] = $value['label'];
                }

                echo "<input type='hidden' id='moScimAllJoomlaAttributes' value='" . json_encode($allJoomlaAttributes) . "'/>";
                foreach ($mappingDetails as $key => $value) {
                    $options = "";

                    foreach ($allUserAttributes as $fieldID) {
                        $options = $options . "<option value='" . $fieldID . "' " . ($key == $fieldID ? "selected" : "") . ">" . $fieldID . "</option>";
                    }
                    $options1 = '<optgroup label="User Table Fields">';
                    $type = "user";


                    foreach ($allJoomlaAttributes as $key1 => $userField) {

                        if ($type == "user" && substr($key1, 0, 3) == "up_") {
                            $type = "userField";
                            $options1 = $options1 . '</optgroup><optgroup label="User Profile Fields">';
                        } elseif ($type == "userField" && substr($key1, 0, 3) != "up_") {
                            $options1 = $options1 . '</optgroup><optgroup label="User Field Attributes">';
                        }
                        $options1 = $options1 . '<option value="' . $key1 . '" ' . ($value == $key1 ? "selected" : "") . '>' . $userField . '</option>';
                    }
                    $options1 = $options1 . '</optgroup>';

                    echo '
                         <div class="mo_boot_row userAttr userProfileAttributeRows"  id="uparow_profile_' . $counter . '" >
                             <select type="text" class="moScimAttributeValue mo-form-control mo-form-control-select mo_scim_textfield" name="user_profile_attr_value[' . $counter . ']" value="' . $key1 . '" >'.$options1.'</select>
                             <select type="text" class="moScimAttributeName mo-form-control mo-form-control-select mo_scim_textfield" name="user_profile_attr_name[' . $counter . ']" value="' .$key . '" >'.$options.'</select>
                             <input type="button" class="btn btn-danger mo_scim_danger_button userAttributeMinusButton" name="user_profile_attr_minus_button[' . $counter . ']"  value="-" onclick="remove_user_attibute(this)"/>
                         </div>';

                    $counter += 1;
                }

                ?>
                <div class="mo_boot_col-sm-12 text-center  mo_boot_mt-3 mo_boot_mb-2">
                    <input type="submit" name="moOauthAttrMapSaveButton" 
                        class="mo_boot_btn mo_boot_btn-primary " 
                        value="<?php echo Text::_('COM_MINIORANGE_SCIM_SAVE_SETTING'); ?>"/>
                </div>
            </div>
        </form>
        <div class="mo_boot_container mo_scim_mapping_subhead mo_boot_ml-2 mo_boot_col-sm-12 mo_scim_content_box" id="userProfileAttrDiv">
            <h5><?php echo Text::_('COM_MINIORANGE_SCIM_ADVANCE_JOOMLA_ATTRIBUTE'); ?>
                <sup>
                    <a href="<?php echo Uri::base(); ?>index.php?option=com_miniorange_scim&tab-panel=upgradeplans">
                        <img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_scim/assets/images/crown.webp" alt="Premium">
                    </a>
                </sup>
            </h5>
        
            <div class="mo_boot_row moScimAttributeColumns mo_boot_mt-4">
                <div class="mo_scim_mapping_subhead"><h6><?php echo Text::_('COM_MINIORANGE_SCIM_USER_PROFILE_ATTRIBUTE'); ?></h6></div>
                <div class="mo_scim_mapping_subhead"><h6><?php echo Text::_('COM_MINIORANGE_SCIM_CLIENT_ATTRIBUTE'); ?></h6></div>
            </div>
          <?php

            $counter = 0;

            $options = "<option value='userName'>userName</option>";
            $options1 = '<optgroup label="User Table Fields">';
            $options1 = $options1 . '<option value="name">name</option></optgroup>';

            echo '
                  <div class="mo_boot_row userAttr userProfileAttributeRows" id="uparow_profile_' . $counter . '" >
                       <select type="text" class="moScimAttributeName mo-form-control mo-form-control-select mo_scim_textfield" disabled >' . $options . '</select>
                       <select type="text" class="moScimAttributeValue mo-form-control mo-form-control-select mo_scim_textfield" disabled  >' . $options1 . '</select>
                  </div>';
            ?>
            <div class="mo_boot_col-sm-12 text-center mo_boot_mt-3 mo_boot_mb-2">
                <input type="submit" name="moOauthAttrMapSaveButton" 
                    class="mo_boot_btn mo_boot_btn-primary " 
                    value="<?php echo Text::_('COM_MINIORANGE_SCIM_SAVE_SETTING'); ?>" disabled/>
            </div>
        </div>
    </div>
    <?php
}

function scimRoleMapping()
{
    $groups = MoScimUtilitiesClient::loadGroups();
    $scimConfig = MoScimUtilitiesClient::get_scim_config();
    if ($scimConfig['bearer_token'] == "") {
        $bearer_token = MoScimUtilitiesClient::create_bearer_token();
    } else {
        $bearer_token = $scimConfig['bearer_token'];
    }
    $premiumDisabled = 'enabled';
    $selectedRoles = json_decode($scimConfig['default_roles'], true);
    $moScimParentRole = $scimConfig['moScimParentRole'];
    $scim_url = Uri::root() . 'api/index.php/v1/mini/miniorangescim';

    ?>
    <div class="mo_boot_col-sm-12">
        <h4 class="form-head">
            <?php echo Text::_('COM_MINIORANGE_SCIM_ROLE_MAPPING'); ?>
            <sup>
                <a href="<?php echo Uri::base(); ?>index.php?option=com_miniorange_scim&tab-panel=upgradeplans">
                    <img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_scim/assets/images/crown.webp" alt="Premium">
                </a>
            </sup>
        </h4>

        <div id="moJoom-scimClient-supportForm">
            <form name="f" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_scim&view=accountsetup&task=accountsetup.defaultRoles'); ?>" class="mo_scim_form_wrapper">
                <div class="mo_boot_row">
                    <div class="mo_scim_mapping_subhead mo_boot_col-sm-12">
                        <h5><?php echo Text::_('COM_MINIORANGE_SCIM_ROLE_CHILD'); ?></h5>
                    </div>
                    <div class="mo_boot_col-sm-6">
                        <div class="mo_boot_form-check form-switch">
                            <input class="mo_boot_form-check-input" type="checkbox" id="selectAllUsers" disabled />
                            <label class="mo_boot_form-check-label mo_scim_form mo_boot_mx-sm-4" for="selectAllUsers">Select All Users</label>
                        </div>
                    </div>

                    <?php
                    foreach ($groups as $key => $value) {
                        $pre = $key % 2 == 0 ? '<tr>' : '';
                        $post = $key % 2 == 1 || count($groups) == $key ? '</tr>' : '';

                        // Determine if the switch should be checked
                        $checked = $value['id'] == $moScimParentRole ? 'checked' : '';
                        $disabled = 'disabled'; // All switches are disabled

                        // Output the row with toggle switch
                        echo $pre . '
                              <div class="mo_boot_col-sm-6 d-flex align-items-center">
                                   <div class="mo_boot_form-check form-switch">
                                       <input class="mo_boot_form-check-input" type="checkbox" id="switch' . $value['id'] . '" value="' . $value['id'] . '" name="moScimParentRole" ' . $checked . ' ' . $disabled . ' />
                                       <label class="mo_boot_form-check-label mo_scim_form mo_boot_mx-sm-4" for="switch' . $value['id'] . '">' . $value['title'] . '</label>
                                    </div>
                               </div>
                        ' . $post;
                    }
                    ?>

                </div>
                
                <div class="mo_boot_row  mo_boot_mt-3">
                    <div class=" mo_scim_mapping_subhead mo_boot_col-sm-12">
                        <h5><?php echo Text::_('COM_MINIORANGE_SCIM_ASSIGN_ROLE'); ?></h5>
                    </div>
                    <div class="mo_boot_col-sm-6  ">
                        <div class="mo_boot_form-check form-switch">
                            <input class="mo_boot_form-check-input" type="checkbox" id="selectAllUsers" disabled />
                            <label class="mo_boot_form-check-label mo_scim_form mo_boot_mx-sm-4" for="selectAllUsers">Select All Users</label>
                        </div>
                    </div>


                    <?php

                    foreach ($groups as $key => $value) {

                        $pre = $key % 2 == 0 ? '<tr>' : '';
                        $post = $key % 2 == 1 || count($groups) == $key ? '</tr>' : '';


                        // Determine if the switch should be checked
                        $checked = in_array($value['id'], $selectedRoles) ? 'checked' : '';
                        $disabled = 'disabled'; // All switches are disabled

                        // Output the row with toggle switch
                        echo $pre . '
                             <div class="mo_boot_col-sm-6 d-flex align-items-center">
                                  <div class="mo_boot_form-check form-switch">
                                        <input class="mo_boot_form-check-input" type="checkbox" id="switch' . $value['id'] . '" value="mo_scim" name="' . str_replace(' ', '_', $value['id']) . '" ' . $checked . ' ' . $disabled . ' />
                                        <label class="mo_boot_form-check-label mo_scim_form mo_boot_mx-sm-4" for="switch' . $value['id'] . '">' . $value['title'] . '</label>
                                   </div>
                             </div>
                        ' . $post;
                    }
                    ?>
                    <div class="mo_boot_mt-3 mo_scim_role_btn">
                        <p><input type="submit" value="<?php echo Text::_('COM_MINIORANGE_SCIM_SAVE_CONFIG'); ?>" class="mo_boot_mx-3 mo_boot_btn mo_boot_btn-primary mo_boot_mt-3" disabled></p>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}

function scimAdvanceSettings()
{
    ?>
    <div class="mo_boot_col-sm-12">
        <h4 class="form-head">
            <?php echo Text::_('COM_MINIORANGE_SCIM_ADVANCE_SETTINGS'); ?>
            <sup>
                <a href="<?php echo Uri::base(); ?>index.php?option=com_miniorange_scim&tab-panel=upgradeplans">
                    <img class="crown_img_small" src="<?php echo Uri::base(); ?>/components/com_miniorange_scim/assets/images/crown.webp" alt="Premium">
                </a>
            </sup>
        </h4>
        <input type="hidden" name="option" value=""/>
        <form id="" name="advance_attr_mapping_form" method="post"
              action="<?php echo Route::_('index.php?option=com_miniorange_scim&view=accountsetup&task=accountsetup.saveAdvanceSettings'); ?>" class="mo_scim_form_wrapper">
            <div class="mo_boot_col-sm-12 mt-8">
                <p><?php echo Text::_('COM_MINIORANGE_SCIM_ADVANCE_SUBHEADING'); ?></p>
                <div class="mo_boot_form-check form-switch mo_boot_mb-3">
                    <input class="mo_boot_form-check-input" type="checkbox" id="switchDoNothing" checked disabled>
                    <label class="mo_boot_form-check-label mo_scim_form mo_boot_mx-sm-4" for="switchDoNothing"><?php echo Text::_('COM_MINIORANGE_SCIM_DO_NOTHING'); ?></label>
                </div>

                <div class="mo_boot_form-check form-switch mo_boot_mb-3">
                    <input class="mo_boot_form-check-input" type="checkbox" id="switchDeactivateUsers" disabled>
                    <label class="mo_boot_form-check-label mo_scim_form mo_boot_mx-sm-4" for="switchDeactivateUsers"><?php echo Text::_('COM_MINIORANGE_SCIM_DEACTIVATE_USERS'); ?></label>
                </div>

                <div class="mo_boot_form-check form-switch mo_boot_mb-3">
                    <input class="mo_boot_form-check-input" type="checkbox" id="switchDeleteUser" disabled>
                    <label class="mo_boot_form-check-label mo_scim_form mo_boot_mx-sm-4" for="switchDeleteUser"><?php echo Text::_('COM_MINIORANGE_SCIM_DELETE_USER'); ?></label>
                </div>
            </div>
          
        <input type="submit" name="moOauthAttrMapSaveButton" class="mo_boot_mx-sm-4 mo_boot_btn mo_boot_btn-primary mo_boot_mt-3" value="<?php echo Text::_('COM_MINIORANGE_SCIM_SAVE_SETTING'); ?>" disabled=""/>
        </form>
    </div> <?php
}

function mo_scim_licensing_page()
{
    $result = MoScimUtilitiesClient::getCustomerDetails();
    $user_email = isset($result['email']) ? $result['email'] : '';

    $circle_icon = '
        <svg class="min-w-[8px] min-h-[8px]" width="8" height="8" viewBox="0 0 18 18" fill="none">
            <circle id="a89fc99c6ce659f06983e2283c1865f1" cx="9" cy="9" r="7" stroke="rgb(99 102 241)" stroke-width="4"></circle>
        </svg>
    ';
    ?>

    <div class="mo_boot_container mo_customapi_main_section">
        <div class="mo_boot_row mo_boot_justify-content-center">
            <div class="mo_boot_col-12">
                <div class="mo_boot_mb-4">
                    <h3><?php echo Text::_('COM_MINIORANGE_SCIM_LICENSING_PLAN'); ?></h3>
                </div>
                
                <div class="mo_scim_pricing-container">
                    <div class="mo_scim_pricing-card">
                        <h3 class="mo_boot_py-1"><?php echo Text::_('COM_MINIORANGE_SCIM_FREE'); ?></h3>
                        <div><h3 class="mo_boot_py-1"><strong>$0</strong></h3></div>
                        <button class="mo_scim_contact-btn"><?php echo Text::_('COM_MINIORANGE_SCIM_CURRENT_PLAN'); ?></button>
                        
                        <div class="mo_scim_feature-section">
                            <div id="scim-free-included-list" class="mo_scim_feature-list">
                                <ul>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_CREATE_USER'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_REAL_TIME_PROVISE'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_PRE_CONFIG_IDP'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_END_TO_END_IDP'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mo_scim_pricing-card">
                        <h3 class="mo_boot_py-1"><?php echo Text::_('COM_MINIORANGE_SCIM_PREMIUM_PLAN'); ?></h3>
                        <div><h3 class="mo_boot_py-1"><strong>$299</strong></h3></div>
                        <a class="mo_scim_contact-btn" href="https://portal.miniorange.com/initializePayment?requestOrigin=joomla_scim_premium_plan" target="_blank"><?php echo Text::_('COM_MINIORANGE_SCIM_UPGRADE_NOW'); ?></a>
                        
                        <div class="mo_scim_feature-section">
                            <div id="scim-premium-included-list" class="mo_scim_feature-list">
                                <ul>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_CREATE'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_PROVISIONING'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_PRE_CONFG_IDP'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_GENERATE_TOKEN'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_UPDATE'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_GROUP_MAP'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_CUSTOM_ATTRRIBUTE_NAME'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_DEACTIVE'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_DELETE'); ?></li>
                                    <li>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_FEATURE_IDP_CONFG'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mo_boot_col-sm-12 mo_boot_my-4 mo_customapi_main_section">
        <h4 class="form-head"><?php echo Text::_('COM_MINIORANGE_SCIM_UPGRADE_PREMIUM_HEADING'); ?></h4>
        <section id="mo_saml_section-steps" class="mo_boot_mt-4 mo_scim_steps">
            <div class="mo_boot_col-sm-12 mo_boot_row ">
                <div class=" mo_boot_col-sm-6 mo_works-step">
                    <div class="mo_scim_number"><strong>1</strong></div>
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_UPGRADE_ONE');?></p>
                </div>
                <div class="mo_boot_col-sm-6 mo_works-step">
                    <div class="mo_scim_number"><strong>4</strong></div>
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_UPGRADE_FOUR');?></p>
                </div>
            </div>

            <div class="mo_boot_col-sm-12 mo_boot_row">
                <div class=" mo_boot_col-sm-6 mo_works-step">
                    <div class="mo_scim_number"><strong>2</strong></div>
                    <p> <?php echo Text::_('COM_MINIORANGE_SCIM_UPGRADE_TWo');?> </p>
                </div>
                <div class="mo_boot_col-sm-6 mo_works-step">
                    <div class="mo_scim_number"><strong>5</strong></div>
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_UPGRADE_FIVE');?> </p>
                </div>
            </div>

            <div class="mo_boot_col-sm-12 mo_boot_row ">
                <div class="mo_boot_col-sm-6 mo_works-step">
                    <div class="mo_scim_number"><strong>3</strong></div>
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_UPGRADE_THREE');?></p>
                </div>
                <div class=" mo_boot_col-sm-6 mo_works-step">
                    <div class="mo_scim_number"><strong>6</strong></div>
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_UPGRADE_SIX');?></p>
                </div>
            </div>
        </section>
    </div>
    <div class="mo_boot_col-sm-12 mo_boot_my-4">
        <h4 class="form-head form-head-bar-licensing-c"><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ'); ?></h4>
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-6">
                <h3 class="mo_scim_faq_page"><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_A'); ?></h3>
                <div class="mo_scim_faq_body">
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_A1'); ?></p>
                </div>
                <hr>
            </div>

            <div class="mo_boot_col-sm-6">
                <h3 class="mo_scim_faq_page"><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_B'); ?></h3>
                <div class="mo_scim_faq_body">
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_B1'); ?></p>
                </div>
                <hr>
            </div>
        </div>
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-6">
                <h3 class="mo_scim_faq_page"><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_C'); ?></h3>
                <div class="mo_scim_faq_body">
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_C1'); ?></p>
                </div>
                <hr>
            </div>

            <div class="mo_boot_col-sm-6">
                <h3 class="mo_scim_faq_page"><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_D'); ?></h3>
                <div class="mo_scim_faq_body">
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_D1'); ?></p>
                </div>
                <hr>
            </div>
        </div>
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-6">
                <h3 class="mo_scim_faq_page"><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_E'); ?></h3>
                <div class="mo_scim_faq_body">
                    <p><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_E1'); ?></p>
                </div>
                <hr>
            </div>
            <div class="mo_boot_col-sm-6">
                <h3 class="mo_scim_faq_page"><?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_F'); ?></h3>
                <div class="mo_scim_faq_body">
                    <?php echo Text::_('COM_MINIORANGE_SCIM_FAQ_F1'); ?>
                </div>
                <hr>
            </div>
        </div>
    </div>
    <?php
}

function mo_scim_support(){
    ?>
    <div class="mo_boot_col-sm-12">
        <div class="mo_scim_support_head mo_boot_mb-3">
            <h3><?php echo Text::_('COM_MINIORANGE_SCIM_SUPPORT');?></h3>
        </div>
        <form name="f" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_scim&view=accountsetup&task=accountsetup.contactUs');?>" class="mo_scim_form_wrapper">
            <div class="mo_boot_row scim-table">
                <div class="mo_boot_col-sm-12">
                    <p ><?php echo Text::_('COM_MINIORANGE_SCIM_SUPPORT_DESC');?></p>
                </div>
            </div>  
            <div class="mo_boot_row scim-table ">
                <div class="mo_boot_col-sm-12">
                    <input type="email" class="mo-form-control mo_scim_textfield" name="query_email" value="" placeholder="<?php echo Text::_('COM_MINIORANGE_SCIM_SUPPORT_MAIL');?>" required />
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_pt-3">
                    <input type="text" class="mo-form-control mo_scim_textfield" name="query_phone" value="" placeholder="<?php echo Text::_('COM_MINIORANGE_SCIM_SUPPORT_PHONE');?>"/>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_pt-3">
                    <textarea class="mo-form-control-textarea mo_scim_textfield" name="mo_scim_textfield" cols="52" rows="4" placeholder="<?php echo Text::_('COM_MINIORANGE_SCIM_SUPPORT_QUERY');?>" required></textarea>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_pt-5 mo_scim_support_save mo_boot_mb-5">
                    <input type="submit" name="send_query" value="<?php echo Text::_('COM_MINIORANGE_SCIM_SUPPORT_SUBMIT');?>" class="mo_boot_btn mo_boot_btn-primary " />
                </div>
            </div>
        </form>
    </div>
    <?php
}

function trial_request()
{
    $current_user = Factory::getUser();
    $result = new MoSCIMUtility();
    $result = $result->load_database_values('#__miniorange_scim_customer');
    $admin_email = isset($result['email']) ? $result['email'] : '';
    if ($admin_email == '') $admin_email = $current_user->email;
  
    ?>
    <div class="mo_boot_container mo_customapi_main_section">
        <div class="mo_boot_row mo_boot_justify-content-center">
            <div class="mo_boot_col-md-8">
                <h4 class="mo_boot_text-justify mo_boot_mb-3 mo_boot_mt-2"><?php echo Text::_('COM_MINIORANGE_SCIM_TRIAL_TAB'); ?></h4>
                <div class="mo_scim_form_wrapper">
                    <div class="mo_boot_p-3">
                        <form name="demo_request" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_scim&task=accountsetup.requestForTrialPlan'); ?>">
                            <input type="hidden" name="option1" value="mo_customapi_trial_demo" />
                            <div class="mo_boot_mb-3">
                                <h4 class="mo_boot_mb-1"><?php echo Text::_('COM_MINIORANGE_SCIM_EMAIL');?>:<span class="mo_required_field">*</span></h4>
                                <input type="email" class="mo-form-control" name="email" value="<?php echo $admin_email; ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_SCIM_EMAIL_PLACEHOLDER');?>" required>
                            </div>

                            <div class="mo_boot_mb-3">
                                <h4 class="mo_boot_mb-1"><?php echo Text::_('COM_MINIORANGE_SCIM_PHONE_NUMBER');?>:</h4>
                                <div class="mo_boot_d-flex">
                                    <select class="mo-form-control-select mo-form-control mo_boot_me-2" name="country_code" style="flex: 0 0 120px;">
                                        <?php
                                        // Load country codes from JSON file
                                        $country_codes_json_path = Uri::base() . 'components/com_miniorange_scim/assets/json/country_codes.json';
                                        $country_codes_json = @file_get_contents($country_codes_json_path);
                                        
                                        if ($country_codes_json === false) {
                                            // Fallback to default codes if JSON file is not found
                                            $country_codes = array('+1', '+44', '+91');
                                        } else {
                                            $country_codes = json_decode($country_codes_json, true);
                                            if ($country_codes === null) {
                                                $country_codes = array('+1', '+44', '+91');
                                            }
                                        }
                                        
                                        foreach ($country_codes as $code) {
                                            echo '<option value="' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($code, ENT_QUOTES, 'UTF-8') . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <input type="tel" class="mo-form-control mo_boot_flex-fill" name="query_phone" placeholder="<?php echo Text::_('COM_MINIORANGE_SCIM_PHONE_PLACEHOLDER');?>">
                                </div>
                            </div>

                            <div class="mo_boot_mb-3">
                                <h4 class="mo_boot_mb-1"><?php echo Text::_('COM_MINIORANGE_SCIM_REQUEST_TRIAL'); ?>:</h4>
                                <input type="text" class="mo-form-control" name="plan" value="SCIM User Provisioning premium plugin" readonly>
                            </div>

                            <div class="mo_boot_mb-3">
                                <h4 class="mo_boot_mb-1"><?php echo Text::_('COM_MINIORANGE_SCIM_DESCRIPTION'); ?>:<span class="mo_required_field">*</span></h4>
                                <textarea class="mo-form-control-textarea" name="description" rows="4" placeholder="<?php echo Text::_('COM_MINIORANGE_SCIM_TRIAL_ASSISTANCE'); ?>" required></textarea>
                            </div>

                            <input type="hidden" name="option1" value="mo_usync_login_send_query">
                            <div class="mo_boot_text-center">
                                <button type="submit" class="mo_boot_btn mo_boot_btn-primary">
                                    <i class="fa fa-envelope mo_boot_me-2"></i>&nbsp;<?php echo Text::_('COM_MINIORANGE_SCIM_TC_BTN'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="mo_boot_mt-3 mo_boot_text-justify">
                    <?php echo Text::_('COM_MINIORANGE_SCIM_TRIAL_DESC'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>
