<?php

/**
 * @package    Joomla.System
 * @subpackage plg_system_miniorangeoauth
 *
 * @author    miniOrange Security Software Pvt. Ltd.
 * @copyright Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license   GNU General Public License version 3; see LICENSE.txt
 * @contact   info@xecurify.com
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

jimport('joomla.plugin.plugin');
jimport('miniorangeoauthplugin.utility.MoOauthClientHandler');

require_once JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_miniorange_oauth'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'mo_customer_setup.php';

class plgSystemMiniorangeoauth extends CMSPlugin
{

    public function onAfterRender()
    {
        $app            = Factory::getApplication();
        $body           = $app->getBody();
        $tab = 0;
        $tables = Factory::getDbo()->getTableList();
        foreach ($tables as $table)
        {
            if (strpos($table, "miniorange_oauth_config") !== false) {
                $tab = $table;
                break;
            }
        }

        if($tab == 0) {
            return;
        }

        $customerResult = MoOauthClientHandler::miniOauthFetchDb('#__miniorange_oauth_config', array('id'=>'1'));
        $applicationName= isset($customerResult['appname']) ? $customerResult['appname'] : '';
        $sso_status      = isset($customerResult['sso_enable']) ? $customerResult['sso_enable'] : 0;
        $sso_button_enable = isset($customerResult['sso_button_enable']) ? $customerResult['sso_button_enable'] : 0;

        $versionObj = new Version();
        $version = $versionObj->getShortVersion();

        $redirectUrlByVersion = "";
    

        if(version_compare($version, '4.0.0', '>=')) {
            $redirectUrlByVersion = "api/index.php/v1/miniorangeoauth";
        }

        if ($sso_status == 1 && $sso_button_enable == 1 && $app->isClient('site')) {
            if (stristr($body, "user.login")) {
                // Match the Joomla "Log in" button block specifically
                $pattern = '/(<div[^>]*class=["\']mod-login__submit form-group["\'][^>]*>\s*<button[^>]*name=["\']Submit["\'][^>]*>.*?<\/button>\s*<\/div>)/is';

                // Your custom SSO login button
                $linkAddPlace = '
                    <div class="form-group mt-2">
                        <a href="' . Uri::root() . $redirectUrlByVersion . '?morequest=oauthredirect&app_name=' . $applicationName . '" 
                           class="btn btn-primary w-100">
                           Login with ' . $applicationName . '
                        </a>
                    </div>';

                // Append custom button after Joomla login button
                $replacement = '$1' . $linkAddPlace;

                $body = preg_replace($pattern, $replacement, $body, 1); // replace once
                $app->setBody($body);
            }
        }
    }

    public function onAfterInitialise()
    {
        $app = Factory::getApplication();
        // Get input object
        if (method_exists($app, 'getInput')) {
            $input = $app->getInput();
        } else { // Joomla 3
            $input = $app->input;
        }

        // Get all POST data
        $post = $input->post->getArray();

        $cookie = $input->cookie;
        // $mo_user_info = $cookie->get('mo_user_info', null);

        $lang = $app->getLanguage();

        $lang->load(
            'plg_system_miniorangeoauth', 
            JPATH_ADMINISTRATOR
        );

        if (isset($post['mojsp_feedback'])) {
           
            $radio = !empty($post['deactivate_plugin']) ? $post['deactivate_plugin'] : '';
            $data = !empty($post['query_feedback']) ? $post['query_feedback'] : '';
            if(isset($post['miniorange_feedback_skip']) && $data == '') {
                $data = 'Skipped';
            }

            $current_user = Factory::getUser();
            $feedback_email = !empty($post['feedback_email']) ? $post['feedback_email'] : $current_user->email;

            $fields = array(
            'uninstall_feedback'=>1
            );
            $conditions = array(
                'id'=>'1'
            );

            MoOauthClientHandler::miniOauthUpdateDb('#__miniorange_oauth_customer', $fields, $conditions);
            $customerResult= MoOauthClientHandler::miniOauthFetchDb('#__miniorange_oauth_customer', array('id'=>'1'));
            $admin_email = (isset($customerResult['email']) && !empty($customerResult['email'])) ? $customerResult['email'] : $feedback_email;
            $admin_phone = $customerResult['admin_phone'];
            $data1 = $radio . ' : ' . $data;
            include_once JPATH_BASE . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php';
            MoOauthCustomer::submit_feedback_form($admin_email, $admin_phone, $data1);
            include_once JPATH_SITE . DIRECTORY_SEPARATOR . 'libraries' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Installer' . DIRECTORY_SEPARATOR . 'Installer.php';
            
            foreach ($post['result'] as $fbkey) 
            {
                $result = MoOauthClientHandler::miniOauthFetchDb('#__extensions', array('extension_id'=>$fbkey), 'loadColumn', 'type');
                $type = 0;
                foreach ($result as $results) 
                {
                    $type = $results;
                }
                if ($type) {
                    $cid = 0;
                    $installer = new Installer();
                     $installer->setDatabase(Factory::getDbo()); 
                    $installer->uninstall($type, $fbkey, $cid);
                }
            }
        }

        if ($cookie->get('mo_site', null)) {
            $rawSessionId = $cookie->get('session_id', '');
            $session_id = $rawSessionId !== '' ? base64_decode($rawSessionId) : '';
            $rawUserId = $cookie->get('user_id', '');
            $user_id = $rawUserId !== '' ? base64_decode($rawUserId) : '';

            if ($session_id && $user_id) {
                setcookie('mo_site', '', time() - 300, '/', "",  true, true);
                setcookie('session_id', '', time() - 300, '/', "", true, true);
                setcookie('user_id', '', time() - 300, '/', "", true, true);

                $session = Factory::getSession();
            
                if($user_id) {
                    $user = User::getInstance($user_id);
                    $session->set('user', $user);
                    $session->set('session_id', $session_id);
                }
            }

            $app->redirect(Uri::root() . 'index.php');
        }
    }


    function onExtensionBeforeUninstall($id)
    {
        $app = Factory::getApplication();
        if (method_exists($app, 'getInput')) {
            $input = $app->getInput();
        } else { // Joomla 3
            $input = $app->input;
        }
        $post = $input->post->getArray();
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('extension_id');
        $query->from('#__extensions');
        $query->where($db->quoteName('name') . " = " . $db->quote('COM_MINIORANGE_OAUTH'));
        $db->setQuery($query);
        $result = $db->loadColumn();
        $tables = Factory::getDbo()->getTableList();
        $tab = 0;
        foreach ($tables as $table) {
            if (strpos($table, "miniorange_oauth_customer")) {
                $tab = $table;
            }
        }
        if ($tab) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select('uninstall_feedback');
            $query->from('#__miniorange_oauth_customer');
            $query->where($db->quoteName('id') . " = " . $db->quote(1));
            $db->setQuery($query);
            $fid = $db->loadColumn();
            $tpostData = $post;
            foreach ($fid as $value) 
            {
                if ($value == 0) {
                    foreach ($result as $results) 
                    {
                        if ($results == $id) {
                            ?>
                            <div id="myModal" class="modal" style="display: none;">
                                <div class="modal-content">
                                    <img src="<?php echo Uri::root() . 'plugins/system/miniorangeoauth/assets/image/think.jpg'; ?>" style="width:70px;height;70px;" alt="">
                                    <p style="font-size:20px;line-height:30px;">
                                    <?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_UNINSTALL_FEEDBACK'); ?>
                                    </p>
                                    <br><br>
                                    <a style="display:inline-block" href="<?php echo Uri::base()?>index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support" class="mo_btn mo_btn-primary"><?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_CONTACT_US'); ?></a>
                                    &nbsp;&nbsp;&nbsp;
                                    <button  class="mo_btn mo_btn-primary" onclick="skip()" >Skip</button>
                                </div>
                            </div>
                            <div class="form-style-6 " id="form-style-6" style="display: block;">
                                <!-- <span class="mojsp_close">&times;</span> -->
                                <h1> <?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_TITLE'); ?> </h1>
                                <h3> <?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED'); ?> </h3>
                                <form name="f" method="post" action="" id="mojsp_feedback">
                                    <input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>
                                    <div>
                                        <p style="margin-left:2%">
                                        <?php
                                        $deactivate_reasons = array(
                                            Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_1'),
                                            Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_2'),
                                            Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_3'),
                                            Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_4'),
                                            Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_5'),
                                            Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_7'),
                                            Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_8'),
                                            Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_9')
                                        );
                                        foreach ($deactivate_reasons as $deactivate_reasons) { ?>
                                        <div class=" radio " style="padding:1px;margin-left:2%;cursor:pointer">
                                            <label style="font-weight:normal;font-size:14.6px"
                                                   for="<?php echo $deactivate_reasons; ?>">
                                                <input type="radio" name="deactivate_plugin"
                                                       value="<?php echo $deactivate_reasons; ?>" required>
                                                <?php echo $deactivate_reasons; ?></label>
                                        </div>
                                        <?php } ?>
                                        <br>
                                        <textarea id="query_feedback" name="query_feedback" rows="4"
                                                  style="margin-left:2%"
                                                  cols="50" placeholder="<?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_QUERY_PLACEHOLDER'); ?>"></textarea><br><br><br>
                                        <tr>
                                <td width="20%"><b> <?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_EMAIL'); ?> <span style="color: #ff0000;">*</span>:</b></td>
                                <td><input type="email" name="feedback_email" required placeholder="<?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_EMAIL_PLACEHOLDER'); ?>" style="width:55%"/></td>
                                       </tr>
                                            <?php
                                            foreach ($tpostData['cid'] as $key) { ?>
                                            <input type="hidden" name="result[]" value=<?php echo $key ?>>
                                            <?php } ?>
                                        <br><br>
                                        <div class="mojsp_modal-footer">
                                            <input type="submit" name="miniorange_feedback_submit"
                                                   class="button button-primary button-large" value="<?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_SUBMIT_BUTTON'); ?>"/>
                                        </div>
                                        <br>
                                        <div class="mojsp_modal-footer">
                                            <input type="submit" name="miniorange_feedback_skip"
                                                   class="button button-primary button-large" value="<?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_SKIP_BUTTON'); ?>" formnovalidate/>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
                            <script>
                                jQuery('input:radio[name="deactivate_plugin"]').click(function () {
                                    var reason = jQuery(this).val();
                                    jQuery('#query_feedback').removeAttr('required')
                                    if (reason == 'Facing issues During Registration') {
                                        jQuery('#query_feedback').attr("placeholder", "Can you please describe the issue in detail?");
                                    } else if (reason == "Does not have the features I'm looking for") {
                                        jQuery('#query_feedback').attr("placeholder", "Let us know what feature are you looking for");
                                    } else if (reason == "Other Reasons:") {
                                        jQuery('#query_feedback').attr("placeholder", "Can you let us know the reason for deactivation");
                                        jQuery('#query_feedback').prop('required', true);
                                    } else if (reason == "Not able to Configure") {
                                        jQuery('#query_feedback').attr("placeholder", "Not able to Configure? let us know so that we can improve the interface");
                                    } else if (reason == "Confusing Interface") {
                                        jQuery('#query_feedback').attr("placeholder", "Confusing Interface? Reach out to us at joomlasupport@xecurify.com, we'll help set up the plugin");
                                    } else if (reason == "Redirecting back to login page after Authentication") {
                                        jQuery('#query_feedback').attr("placeholder", "Reach out to us at joomlasupport@xecurify.com, we'll help you resolve the issue");
                                    } else if (reason == "Bugs in the plugin") {
                                        jQuery('#query_feedback').attr("placeholder", "Kindly let us know at joomlasupport@xecurify.com, what issues were you facing");
                                    }else if (reason == "Not Working") {
                                        jQuery('#query_feedback').attr("placeholder", "Kindly let us know at joomlasupport@xecurify.com, which functionality of the plugin is not working for you");
                                        jQuery('#query_feedback').prop('required', true);
                                    }
                                });
                                
                                function skip(){
                                    jQuery("#myModal").css("display","none");
                                    jQuery('#form-style-6').css("display","block");
                                }
                            </script>
                            <style type="text/css">
                                .form-style-6 {
                                    font: 95% Arial, Helvetica, sans-serif;
                                    max-width: 400px;
                                    margin: 10px auto;
                                    padding: 16px;
                                    background: #F1F4F8;
                                    display: none;
                                }
                                .form-style-6 h1 {
                                    background: #1F3047;
                                    padding: 20px 0;
                                    font-size: 140%;
                                    font-weight: 300;
                                    text-align: center;
                                    color: #fff;
                                    margin: -16px -16px 16px -16px;
                                }
                                .form-style-6 input[type="text"],
                                .form-style-6 input[type="date"],
                                .form-style-6 input[type="datetime"],
                                .form-style-6 input[type="email"],
                                .form-style-6 input[type="number"],
                                .form-style-6 input[type="search"],
                                .form-style-6 input[type="time"],
                                .form-style-6 input[type="url"],
                                .form-style-6 textarea,
                                .form-style-6 select {
                                    transition: all 0.30s ease-in-out;
                                    outline: none;
                                    box-sizing: border-box;
                                    width: 100%;
                                    background: #fff;
                                    margin-bottom: 4%;
                                    border: 1px solid #ccc;
                                    padding: 3%;
                                    color: #1F3047;
                                    font: 95% Arial, Helvetica, sans-serif;
                                }
                                .form-style-6 input[type="text"]:focus,
                                .form-style-6 input[type="date"]:focus,
                                .form-style-6 input[type="datetime"]:focus,
                                .form-style-6 input[type="email"]:focus,
                                .form-style-6 input[type="number"]:focus,
                                .form-style-6 input[type="search"]:focus,
                                .form-style-6 input[type="time"]:focus,
                                .form-style-6 input[type="url"]:focus,
                                .form-style-6 textarea:focus,
                                .form-style-6 select:focus {
                                    box-shadow: 0 0 5px #2E486B;
                                    border: 1px solid #2E486B;
                                    padding: 3%;
                                }
                                .form-style-6 input[type="submit"],
                                .form-style-6 input[type="button"] {
                                    box-sizing: border-box;
                                    width: 100%;
                                    padding: 3%;
                                    background: #2E486B;
                                    border-bottom: 2px solid #1F3047;
                                    border: none;
                                    color: #fff;
                                    cursor: pointer;
                                }
                                .form-style-6 input[type="submit"]:hover,
                                .form-style-6 input[type="button"]:hover {
                                    background: #36547D;
                                }
                                .mo_btn {
                                    border: 1px solid #ccc;
                                    padding: 10px;
                                    height: auto;
                                    width: auto;
                                    border-radius: 10px;
                                }
                                .mo_btn-primary {
                                    background-color: #2E486B;
                                    color: white;
                                    text-decoration: none;
                                }
                                .modal {
                                    position: fixed;
                                    z-index: 1;
                                    left: 0;
                                    top: 0!important;
                                    width: 100%!important;
                                    height: 100%!important;
                                    overflow: auto;
                                    background-color: rgba(31, 48, 71, 0.6)!important;
                                    text-align: center!important;
                                }
                                .modal-content {
                                    background-color: #fefefe;
                                    margin: 15% auto;
                                    padding: 20px;
                                    border: 1px solid #888;
                                    width: 30%;
                                    height: auto;
                                    border: 3px solid #2E486B;
                                }
                                .close {
                                    color: #888;
                                    float: right;
                                    font-size: 28px;
                                    font-weight: bold;
                                }
                                .close:hover,
                                .close:focus {
                                    color: #1F3047;
                                    text-decoration: none;
                                    cursor: pointer;
                                }

                            </style>
                                <?php
                                exit;
                        }
                    }
                }
            }
        }
    }

    public function onAfterRoute()
    {
        $app = Factory::getApplication();

        if (method_exists($app, 'getInput')) {
            $input = $app->getInput();
        } else { // Joomla 3
            $input = $app->input;
        }

        $get = $input->get->getArray();
        $moOauthClientHandler = new MoOauthClientHandler();
        if(isset($get['morequest']) && $get['morequest'] == 'testattrmappingconfig') {
            $moOauthClientHandler->handleOAuthRequest($get);
        }
        else if(isset($get['morequest']) and $get['morequest'] == 'oauthredirect') {
            $moOauthClientHandler->handleOAuthRequest($get);
        }
        else if(isset($get['code'])) {
            $moOauthClientHandler->handleOAuthRequest($get);
        }
    }  
}