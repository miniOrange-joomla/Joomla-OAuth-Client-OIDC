<?php
/**
 * @package    Joomla.Administrator
 * @subpackage com_miniorange_oauth
 *
 * @author    miniOrange Security Software Pvt. Ltd.
 * @copyright Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license   GNU General Public License version 3; see LICENSE.txt
 * @contact   info@xecurify.com
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Version;

$document = Factory::getApplication()->getDocument();
$document->addScript(Uri::base() . 'components/com_miniorange_oauth/assets/js/bootstrap.js');
$document->addScript(Uri::base() . 'components/com_miniorange_oauth/assets/js/myscript.js');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_oauth/assets/css/miniorange_oauth.css');
$document->addStyleSheet(Uri::base() . 'components/com_miniorange_oauth/assets/css/miniorange_boot.css');
$document->addStyleSheet('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');

$versionObj = new Version();
$cms_version = $versionObj->getShortVersion();

if(version_compare($cms_version, '4.0.0', '>=')) {
    $document->addScript('https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js');
}

HTMLHelper::_('jquery.framework');

?>  
<?php
if (MoOAuthUtility::is_curl_installed() == 0) { ?>
    <p class="mo_oauth_red_color"> <?php echo Text::_('COM_MINIORANGE_OAUTH_WARNING'); ?>: <?php echo Text::_('COM_MINIORANGE_OAUTH_PHP_CURL'); ?> [<a href="https://www.php.net/manual/en/curl.installation.php" target="_blank"><?php echo Text::_('COM_MINIORANGE_OAUTH_LEARN_MORE'); ?></a>]</p>
    <?php
}
$app   = Factory::getApplication();

$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;

$active_tab = $input->get->getArray();

$tabs = [
    'overview' => array(
        'id' => 'overviewtab',
        'href' => '#overview',
        'label' => 'COM_MINIORANGE_OAUTH_OVERVIEW',
        'icon' =>  'fa-solid fa-house',
        'premium' => false
    ),

    'configuration' => array(
        'id' => 'configtab',
        'href' => '#configuration',
        'label' => 'COM_MINIORANGE_OAUTH_TAB1_CONFIGURE_OAUTH',
        'icon' =>  'fa-solid fa-bars',
        'premium' => false
    ),

    'attrrolemapping' => array(
        'id' => 'attributetab',
        'href' => '#attrrolemapping',
        'label' => 'COM_MINIORANGE_OAUTH_USER_ATTRIBUTE_SETTINGS',
        'icon' =>  'fa-solid fa-address-card',
        'premium' => true
    ),

    'loginlogoutsettings' => array(
        'id' => 'advancetab',
        'href' => '#loginlogoutsettings',
        'label' => 'COM_MINIORANGE_OAUTH_ADVANCE_SETTINGS',
        'icon' =>  'fa-solid fa-gears',
        'premium' => true
    ),

    'loggerreport' => array(
        'id' => 'loggertab',
        'href' => '#loggerreport',
        'label' => 'COM_MINIORANGE_OAUTH_TAB6_LOGGER_REPORT',
        'icon' =>  'fa-regular fa-file-lines',
        'premium' => false
    ),

    'license' => array(
        'id' => 'licensetab',
        'href' => '#licensing-plans',
        'label' => 'COM_MINIORANGE_OAUTH_TAB5_LICENSING_PLANS',
        'icon' =>  'fa-solid fa-coins',
        'premium' => false
    ),
];


$oauth_active_tab = isset($active_tab['tab-panel']) && !empty($active_tab['tab-panel']) ? $active_tab['tab-panel'] : 'overview';
global $license_tab_link;
$license_tab_link="index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=license";
$current_user = Factory::getUser();
if(!PluginHelper::isEnabled('system', 'miniorangeoauth')) {
    ?>
    <div id="system-message-container">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <div class="alert alert-error">
            <h4 class="alert-heading"><?php echo Text::_('COM_MINIORANGE_OAUTH_WARNING');?></h4>
            <div class="alert-message">
                <h4><?php echo Text::_('COM_MINIORANGE_OAUTH_WARNING_TEXT');?>
                </h4>
                <h4><?php echo Text::_('COM_MINIORANGE_OAUTH_STEPS');?></h4>
                <ul>
                    <li><?php echo Text::_('COM_MINIORANGE_OAUTH_STEPS_S1');?></li>
                    <li><?php echo Text::_('COM_MINIORANGE_OAUTH_STEPS_S2');?></li>
                    <li><?php echo Text::_('COM_MINIORANGE_OAUTH_STEPS_S3');?></li>
                </ul>
            </div>
        </div>
    </div>
<?php } ?>

<div id="TC_Modal" class="TC_modal">
    <div class="modal-content">
        <div class="mo_boot_row">
            <h5 class="mo_boot_col-sm-11 mo_boot_m-0"><?php echo Text::_('COM_MINIORANGE_OAUTH_TERMS_AND_CONDITIONS');?></h5>
            <span class="mo_boot_col-sm-1 close" onclick="closeModel()" style="cursor: pointer;"> X </span>
        </div>
        <div>
            <hr>
            <ul> 
                <li><?php echo Text::_('COM_MINIORANGE_OAUTH_TERMS_AND_CONDITIONS1');?></li>
                <li><?php echo Text::_('COM_MINIORANGE_OAUTH_TERMS_AND_CONDITIONS2');?></li>
                <li><?php echo Text::_('COM_MINIORANGE_OAUTH_TERMS_AND_CONDITIONS3');?></li>
                <li><?php echo Text::_('COM_MINIORANGE_OAUTH_TERMS_AND_CONDITIONS4');?></li>
                <li>
                    <form method="post" name="f" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.saveAdminMail'); ?>" >
                        <?php
                            $dVar=new JConfig(); 
                            $check_email = $dVar->mailfrom;
                            $call= new MoOauthCustomer();
                            $result=$call->getAccountDetails();
                        if($result['contact_admin_email']!=null) {
                            $check_email =$result['contact_admin_email'];
                        }
                        ?>
                        <div class="mo_boot_row mo_boot_mt-3">
                            <div class="mo_boot_col-sm-8">
                                <input type="email" name="oauth_client_admin_email"  class="mo-form-control" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_EMAIL_PLACEHOLDER');?>" value="<?php echo $check_email;?>">
                            </div>
                            <div class="mo_boot_col-sm-3">
                                <input type="submit" class="oauth_blue_button">
                            </div>
                        </div>                            
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="mo_boot_row mo_oauth_navbar">
    <div class="mo_boot_col-sm-12">
        <button id="mo_TC"  onclick="show_TC_modal()" class="mo_boot_px-4 mo_boot_py-1 oauth_blue_button btn_oauth_custom_top"> <i class="fa-solid fa-file-shield"></i> T&C</button>
        <a class="mo_boot_px-4 mo_boot_py-1 oauth_blue_button btn_oauth_custom_top" href="<?php echo Uri::base()?>index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support">
            <i class="fa-solid fa-envelope"></i>
            <?php echo Text::_('COM_MINIORANGE_OAUTH_SUPPORT');?>
        </a>
        <a class="mo_boot_px-4 mo_boot_py-1 oauth_blue_button btn_oauth_custom_top"
           href="<?php echo Uri::base()?>index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support&subtab=mo_request_demo">
           <i class="fa-solid fa-globe"></i>
           <?php echo Text::_('COM_MINIORANGE_OAUTH_FREE_TRIAL');?>
        </a>
    </div>
</div>


<div class="mo_boot_container-fluid mo_oauth_plugin">
    <div class="mo_boot_row mo_oauth_navbar">
        <?php foreach ($tabs as $key => $tab): ?>
        <a id="<?php echo $tab['id']; ?>"
            class="mo_boot_col mo_oauth_nav-tab mo_nav_tab_<?php echo $oauth_active_tab == $key ? 'active' : ''; ?>"
            href="<?php echo $tab['href']; ?>"
            onclick="add_css_tab('#<?php echo $tab['id']; ?>');"
            data-toggle="tab">
            
            <span><i class="fa fa-solid <?php echo $tab['icon']; ?>"></i></span>
            <span class="tab-label"><?php echo Text::_($tab['label']); ?></span>
            
                <?php if (!empty($tab['premium']) && $tab['premium'] === true) : ?>
                <span title="<?php echo Text::_('COM_MINIORANGE_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>">
                    <sup>
                        <img class="crown_img_small"
                             src="<?php echo Uri::base(); ?>/components/com_miniorange_oauth/assets/images/crown.webp">
                    </sup>
                </span>
                <?php else: ?>
                    <span class="premium-icon-placeholder"></span>
                <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="mo_boot_container-fluid mo_oauth_tab-content">
   <div class="tab-content" id="myTabContent">
        <div id="overview" class="tab-pane <?php echo $oauth_active_tab == 'overview' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php echo mo_oauth_overview(); ?>
                </div>
            </div>
        </div>
        <div id="configuration" class="tab-pane <?php echo $oauth_active_tab == 'configuration' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php echo moOAuthConfiguration(); ?>
                </div>
            </div>
        </div>
        <div id="attrrolemapping" class="tab-pane <?php echo $oauth_active_tab == 'attrrolemapping' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php attributerole(); ?>
                </div>
            </div>
        </div>
        <div id="loginlogoutsettings" class="tab-pane <?php echo $oauth_active_tab == 'loginlogoutsettings' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php loginlogoutsettings(); ?>
                </div>
            </div>
        </div>
        <div id="proxy-setup" class="tab-pane <?php echo $oauth_active_tab == 'proxy' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php proxy_setup(); ?>
                </div>
            </div>
        </div>
        <div id="support" class="tab-pane <?php echo $oauth_active_tab == 'support' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php support();   ?>
                </div>
            </div>
        </div>
        <div id="licensing-plans" class="tab-pane <?php echo $oauth_active_tab == 'license' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php echo mo_oauth_licensing_plan(); ?>
                </div>
            </div>
        </div>
        <div id="loggerreport" class="tab-pane <?php echo $oauth_active_tab == 'loggerreport' ? 'active' : ''; ?>">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <?php echo moOAuthLoggerReport(); ?>
                </div>
            </div>
        </div>   
    </div>
 </div>
<?php

function mo_oauth_overview()
{    
    ?>
           <div class="mo_boot_col-sm-12 mo_oauth_dark_bg">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-12 mo_boot_mt-4">
                        <h3 class="element">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_NAME');?>
                        </h3>
                    </div>
                </div>
                <div class="mo_boot_row mt-2">
                    <div class="mo_boot_col-lg-7 mo_boot_col-sm-11 mo_boot_text-justify mo_boot_py-4">
                        <strong>
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_DESCRIPTION');?>
                        </strong>
                        <br><br>
                        <a class="oauth_blue_button mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://plugins.miniorange.com/joomla-single-sign-on-sso-oauth-oidc"> <?php echo Text::_('COM_MINIORANGE_OAUTH_VISIT_SITE');?></a>
                        <a class="oauth_blue_button mo_boot_px-3 mo_boot_mx-1" href="<?php echo Uri::root().'administrator/index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=license';?>"> <?php echo Text::_('COM_MINIORANGE_OAUTH_LICENSE_PLANS');?></a>
                        <a class="oauth_blue_button mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=oauth-client"> <?php echo Text::_('COM_MINIORANGE_OAUTH_GUIDES');?></a>
                        <a class="oauth_blue_button mo_boot_px-3 mo_boot_mx-1" target="_blank" href="https://faq.miniorange.com/kb/joomla/"> <?php echo Text::_('COM_MINIORANGE_OAUTH_FAQ');?> </a>
                    </div>
                    <div class="mo_boot_col-sm-5 mo_oauth_overview_img">
                        <img class="" src="<?php echo Uri::root().'administrator\components\com_miniorange_oauth\assets\images\joomla-oauth-oidc-banner.webp'?>" alt="Joomla Single sign on">
                    </div>
                </div>
            </div>

    <?php
}

function selectAppByIcon()
{
    $utility_data = new MoOauthCustomer();
    $app_data = $utility_data->getAppJason();
    $appArray = json_decode($app_data, true);
    $ImagePath=Uri::base().'components/com_miniorange_oauth/assets/images/';
    $imageTableHtmlOAuth = "<h3 class='element'>".Text::_('COM_MINIORANGE_OAUTH_OAUTH_APPS') ."</h3> <table id='moOAuthAppsTable' class='moAuthAppsTable'>";
    $imageTableHtmlOpenIDConnect = "<h3 class='element'>".Text::_('COM_MINIORANGE_OAUTH_OPENID_CONNECT_APPS')."</h3> <table id='moOpenIDConnectAppsTable' class='moAuthAppsTable'>";
    $i=1;
    $PreConfiguredApps = array_slice($appArray, 0, count($appArray)-2);
    $flag=0;
    foreach ($PreConfiguredApps as $key => $value) 
    {
        if($value['type'] == 'openidconnect' && $flag==0) {
            $flag=1;
            $i=1;
        }
        if($value['type'] == 'oauth') {
            $img=$ImagePath.$value['image'];
            if($i%6==1) {
                $imageTableHtmlOAuth.='<tr>';
            }
            $imageTableHtmlOAuth=$imageTableHtmlOAuth."<td class='border' moAuthAppSelector='".$value['label']."'><a class='select_app' href='".Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp='.$key)."''><div><img class='mo_oauth_img_resize' src='".$img."'><br><p>".$value['label']."</p></div></a></td>";
            if($i%6==0 || $i==count($appArray)) {
                $imageTableHtmlOAuth.='</tr>';
            }
            $i++;
        }
        else{
            $img=$ImagePath.$value['image'];
            if($i%6==1) {
                $imageTableHtmlOpenIDConnect.='<tr>';
            }
            $imageTableHtmlOpenIDConnect=$imageTableHtmlOpenIDConnect."<td class='border' moAuthAppSelector='".$value['label']."'><a class='select_app' href='".Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp='.$key)."''><div><img class='mo_oauth_img_resize' src='".$img."'><br><p>".$value['label']."</p></div></a></td>";
            if($i%6==0 || $i==count($appArray)) {
                $imageTableHtmlOpenIDConnect.='</tr>';
            }
            $i++;
        }
    } 

    $imageTableHtmlOAuth.='</table>';
    $imageTableHtmlOpenIDConnect.='</table>';
    ?>
    <div class="mo_boot_container-fluid mo_boot_m-0 mo_boot_p-0">
        <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3 ">
            <div class="mo_boot_col-sm-12 mo_boot_mt-4">
                <div class="mo_boot_row">
                    <div class="mo_boot_col-sm-11 mo_boot_m-0 mo_boot_p-0">
                        <input type="text" class="mo-form-control mo_boot_m-0" name="appsearch" id="moAuthAppsearchInput" value="" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_SELECT_APP');?>">
                    </div>
                    <div class="mo_boot_col-sm-1 mo_boot_m-0 mo_boot_pt-2 mo_boot_border mo_oauth_search_btn mo_boot_text-center mo_boot_align-middle">
                        <span class="mo_oauth_icon_search"><i class="fa-solid fa-magnifying-glass"></i></span>
                    </div>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_mt-4">
                <div class="mo_boot_my-3">
                    <?php
                        echo $imageTableHtmlOAuth;
                    ?>
                </div>
            </div>
            <div class="mo_boot_col-sm-12 mo_boot_mt-4">
                <div class="mo_boot_my-3">
                    <?php
                        echo $imageTableHtmlOpenIDConnect;
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function selectCustomApp()
{
    $utility_data = new MoOauthCustomer();
    $app_data = $utility_data->getAppJason();
    $appArray = json_decode($app_data, true);
    $ImagePath=Uri::base().'components/com_miniorange_oauth/assets/images/';
    ?> 
    <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3">
        <div class="mo_boot_col-sm-12 mo_boot_my-2">
            <br>
            <span class="mo_boot_p-1"><?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOM_APPLICATIONS_NOTE');?></span>
        </div>
        <div class="mo_boot_col-sm-6 mo_boot_my-5 mo_boot_text-center" moAuthAppSelector='moCustomOuth2App'>
            <a class="mo_oauth_select_app" href="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp=other');?>">
                <div class=" border mo_oauth_border">
                    <img class='mo_oauth_img_resize' alt="" src="<?php echo  $ImagePath.$appArray['other']['image']; ?>"><br><p><?php echo $appArray['other']['label'];?></p>
                </div>
            </a>
        </div>
        <div class="mo_boot_col-sm-6 mo_boot_my-5 mo_boot_text-center"  moAuthAppSelector='moCustomOpenIdConnectApp'>
            <a class="mo_oauth_select_app" href="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp=openidconnect');?>">
                <div class=" border mo_oauth_border">
                    <img class='mo_oauth_img_resize' alt="" src="<?php echo  $ImagePath.$appArray['openidconnect']['image']; ?>"><br><p><?php echo $appArray['openidconnect']['label'];?></p>
                </div>
            </a>
        </div>
    </div>
    <?php
}

function getAppDetails()
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('*');
    $query->from($db->quoteName('#__miniorange_oauth_config'));
    $query->where($db->quoteName('id') . " = 1");
    $db->setQuery($query);
    return $db->loadAssoc();
}

function configuration($OauthApp,$appLabel)
{
    global $license_tab_link;
    $attribute = getAppDetails();
    $utility_data = new MoOauthCustomer();
    $app_data = $utility_data->getAppJason();
    $appJson = json_decode($app_data, true);
    $appData = json_decode($utility_data->getAppData(), true);
    if($appJson[$appLabel]["guide"]!="") {
        $guide=$appJson[$appLabel]["guide"];
    }
    else
    {
        $guide="https://plugins.miniorange.com/joomla-sso-ldap-mfa-solutions?section=oauth-client";
    }
    $mo_oauth_app = $appLabel;
    $custom_app = "";
    $client_id = "";
    $client_secret = "";
    $email_attr = "";
    $first_name_attr = "";
    $isAppConfigured = false;
    $mo_oauth_in_header = "checked=true";
    $mo_oauth_in_body   = "";
    $sso_enable = isset($attribute['sso_enable']) ? $attribute['sso_enable'] : '1';
    $sso_button_enable = isset($attribute['sso_button_enable']) ? $attribute['sso_button_enable'] : '1';
    if(isset($attribute['in_header_or_body'])) {
        if($attribute['in_header_or_body']=='inBody' ) {
            $mo_oauth_in_header = "";
            $mo_oauth_in_body   = "checked=true";
        }
        else if($attribute['in_header_or_body']=='inHeader' ) {
            $mo_oauth_in_header = "checked=true";
            $mo_oauth_in_body   = "";
        }
        else if($attribute['in_header_or_body']=='both' ) {
            $mo_oauth_in_header = "checked=true";
            $mo_oauth_in_body   = "checked=true";
        }
    }
    else
    {
        if(isset($appData[$appLabel]) && $appData[$appLabel][0]=='both' ) {
            $mo_oauth_in_header = "checked=true";
            $mo_oauth_in_body   = "checked=true";
        }
        else if(isset($appData['appLabel']) && $appData['appLabel'][0]=='body' ) {
            $mo_oauth_in_header = "";
            $mo_oauth_in_body   = "checked=true";
        }
        else if(isset($appData['appLabel']) && $appData['appLabel'][0]=='header' ) {
            $mo_oauth_in_header = "checked=true";
            $mo_oauth_in_body   = "";
        }
    }
    if (isset($attribute['client_id'])) {
        $mo_oauth_app = empty($attribute['appname'])?$appLabel:$attribute['appname'];
        $custom_app = $attribute['custom_app'];
        $client_id = $attribute['client_id'];
        $client_secret = $attribute['client_secret'];
        $isAppConfigured = empty($client_id) || empty($client_secret) || empty($custom_app)||empty($attribute['redirecturi'])?false:true;
        $step1Check = empty($attribute['redirecturi'])?false:true;
        $step2Check = empty($client_id) || empty($client_secret) || empty($custom_app)||empty($attribute['redirecturi'])?false:true;
        $app_scope = empty($attribute['app_scope'])?$OauthApp['scope']:$attribute['app_scope'];
        $authorize_endpoint = empty($attribute['authorize_endpoint'])?null:$attribute['authorize_endpoint'];
        $access_token_endpoint = empty($attribute['access_token_endpoint'])?null:$attribute['access_token_endpoint'];
        $user_info_endpoint = empty($attribute['user_info_endpoint'])?null:$attribute['user_info_endpoint'];
        $email_attr = $attribute['email_attr'];
        $first_name_attr = $attribute['username_attr'];
        $attributesNames = $attribute['test_attribute_name'] ?? "";
        $step3Check = empty($email_attr)?false:true;
        $attributesNames = explode(",", $attributesNames);
    }

    $versionObj = new Version();
    $version = $versionObj->getShortVersion();

    $redirectUrlByVersion = "";

    if(version_compare($version, '4.0.0', '>=')) {
        $redirectUrlByVersion = "api/index.php/v1/miniorangeoauth";
    }
    
    $redirecturi = empty($attribute['redirecturi'])?explode('//', Uri::root())[1]. $redirectUrlByVersion :explode('//', $attribute['redirecturi'])[1];
    $redirecturi_domain = empty($attribute['redirecturi'])?explode('//', Uri::root())[0]:explode('//', $attribute['redirecturi'])[0];
    $app = Factory::getApplication();
    if (method_exists($app, 'getInput')) {
        $input = $app->getInput();
    } else { // Joomla 3
        $input = $app->input;
    }
    $get = $input->get->getArray();
    $progress = isset($get['progress'])?$get['progress']:"step1"; 
    $step1Check = empty($attribute['redirecturi'])? true : false;
    $step2Check = empty($client_id) || empty($client_secret) || empty($authorize_endpoint) || empty($access_token_endpoint) ? true : false;
    $step3Check = empty($email_attr) ? true : false;
    ?>

    <div class="mo_boot_col-sm-12 mo_main_oauth_section">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12">
                <div class="mo_boot_row mo_boot_my-0">
                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                        <h3><?php echo Text::_('COM_MINIORANGE_OAUTH_CONFIG');?></h3>
                    </div>
                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-4">
                        <form method="post" name="clear_config" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.clearConfig'); ?>" class="mo_boot_float-right mo_boot_mx-1" onclick="return confirm('<?php echo Text::_('COM_MINIORANGE_DELETE_APPLICATION_CONFIRMATION');?>');">
                            <button class="mo_oauth_clear_config_btn" title="<?php echo Text::_('COM_MINIORANGE_OAUTH_CLEAR_CONFIGURATION'); ?>"><span><i class="fa-regular fa-trash-can"></i></span></button>
                        </form>
                        <a href="<?php echo $guide;?>" target="_blank" style="cursor: pointer;" class=" mo_boot_float-right mo_boot_mx-1 mo_oauth_guide_link"><span><i class="fa-regular fa-file"></i></span> <?php echo Text::_('COM_MINIORANGE_OAUTH_GUIDE');?></a>
                        <a href="https://www.youtube.com/playlist?list=PL2vweZ-PcNpd8-9AvYGYrYx_hXn2vSIsc" style="cursor: pointer;" target="_blank" class=" mo_boot_float-right mo_boot_mx-1 mo_oauth_guide_link"><span><i class="fa-brands fa-youtube"></i></span> <?php echo Text::_('COM_MINIORANGE_OAUTH_VIDEO_SET');?></a>
                        
                    </div>
                </div>
                
                <!-- Step 1  -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <!-- Header with toggle -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_tab_content_step1', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_STEP1'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> <?php echo $progress === 'step1' ? '-' : '+'; ?></div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_tab_content_step1" class="mo_oauth_tab_content" style="display: <?php echo $progress === 'step1' ? 'block' : 'none'; ?>;">
                        <div class="mo_boot_col-sm-12">
                            <div class="mo_boot_row mo_boot_mt-3">
                                <div class="mo_boot_col-sm-3">
                                    <strong >
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_APPLICATION');?>
                                        <span class="mo_oauth_highlight">*</span>
                                    </strong>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <?php echo "<span class='mo_oauth_label'>".$OauthApp['label']."</span>";?>
                                    <input type="hidden" name="mo_oauth_app_name" value="<?php echo $mo_oauth_app; ?>">
                                </div>
                            </div>
                            <div class="mo_boot_row mo_boot_mt-3">
                                <div class="mo_boot_col-sm-3">
                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_CALLBACK_URL');?></strong>
                                </div>
                                <div class="mo_boot_col-sm-8 mo_boot_m-0">
                                    <form id="oauth_config_form_step1" method="post" 
                                          action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.saveConfig'); ?>">
                                        <input type="hidden" name="mo_oauth_app_name" value="<?php echo $mo_oauth_app; ?>">
                                        <input type="hidden" name="oauth_config_form_step1" value="true">
                                        <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0">
                                            <div class="mo_boot_col-sm-3 mo_boot_m-0 mo_boot_p-0">
                                                <select class="d-inline-block mo-form-control mo-form-control-select" 
                                                        name="callbackurlhttp" id="callbackurlhttp">
                                                    <option value="http://" <?php echo ($redirecturi_domain == 'http:' ? 'selected' : ''); ?>>http</option>
                                                    <option value="https://" <?php echo ($redirecturi_domain == 'https:' ? 'selected' : ''); ?>>https</option>
                                                </select>
                                            </div>
                                            <div class="mo_boot_col-sm-9 mo_boot_m-0 mo_boot_p-0">
                                                <input class="mo-form-control" id="callbackurl" name="callbackurl" type="text" readonly  
                                                       value='<?php echo $redirecturi; ?>'>
                                            </div>
                                            <small class="d-block mt-1">
                                                <?php echo Text::_('COM_MINIORANGE_OAUTH_CALLBACK_URL_NOTE');?>
                                            </small>
                                        </div>
                                    </form>
                                </div>
                                <div class="mo_boot_col-sm-1">
                                    <em class="fa-regular fa-copy mo_copy copytooltip mo_oauth_copy_btn" 
                                        onclick="copyToClipboard('#callbackurl','#callbackurlhttp');">
                                        <span class="copytooltiptext"> <?php echo Text::_('COM_MINIORANGE_OAUTH_COPIED'); ?></span>
                                    </em>
                                </div>
                            </div>
                        </div>

                        <div class="mo_boot_col-sm-12">
                            <div class="mo_boot_row mo_boot_mt-4">
                                <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                    <button name="send_query" onclick="callbackURLFormSubmit()" class="oauth_blue_button">
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_N_NEXT');?> 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2  -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header <?php echo $step1Check == true ? 'mo_oauth_cursor' : ''; ?>"
                         onclick="<?php echo $step1Check == true ? 'return false;' : "toggleCollapse('mo_oauth_tab_content_step2', this.querySelector('.mo_toggle_icon'))"; ?>">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_STEP2'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right">
                            <?php echo $progress === 'step2' ? '-' : '+'; ?>
                        </div>
                    </div>
                    <div id="mo_oauth_tab_content_step2" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: <?php echo $progress === 'step2' ? 'block' : 'none'; ?>;">
                        <form id="oauth_config_form_step2" name="" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.saveConfig'); ?>">
                            <input type="hidden" name="oauth_config_form_step2" value="true">
                            <div class="mo_boot_row mo_boot_m-1 mo_boot_mt-3">
                                <div class="mo_boot_col-sm-12">
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-sm-12">
                                            <input type="hidden" id="mo_oauth_custom_app_name" name="mo_oauth_custom_app_name" value='<?php echo $OauthApp['label']; ?>' required>
                                            <input type="hidden" name="moOauthAppName" value="<?php echo $appLabel; ?>">
                                            <input type="hidden" name="mo_oauth_app_name" value="<?php echo $mo_oauth_app; ?>">
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3">
                                        <div class="mo_boot_col-sm-3">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_CLIENT_ID'); ?><span class="mo_oauth_highlight">*</span> : </strong>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_CLIENT_ID_PLACEHOLDER');?>" class="mo-form-control" required="" type="text" name="mo_oauth_client_id" id="mo_oauth_client_id" value='<?php echo $client_id; ?>'>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-3">
                                        <div class="mo_boot_col-sm-3">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_CLIENT_SECRET'); ?><span class="mo_oauth_highlight">*</span> : </strong>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <input placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_CLIENT_SECRET_PLACEHOLDER');?>" class="mo-form-control" required type="text" id="mo_oauth_client_secret" name="mo_oauth_client_secret" value='<?php echo $client_secret; ?>'>
                                        </div>
                                    </div>
                                    <?php
                                    if($authorize_endpoint==null) {
                                        if(isset($appData[$appLabel])) {
                                            $fields = explode(",", $appData[$appLabel]['1']);
                                            foreach($fields as $key => $value)
                                            {
                                                if($value == 'Tenant') {
                                                    $placeholder = Text::_('COM_MINIORANGE_OAUTH_ENTER_THE_TENANT_ID');
                                                }
                                                else if($value=='Domain') {
                                                    $placeholder = Text::_('COM_MINIORANGE_OAUTH_ENTER_THE_DOMAIN');
                                                }
                                                else
                                                {
                                                    $placeholder = Text::_('COM_MINIORANGE_OAUTH_ENTER_THE_DETAILS').$value ;
                                                }
                                                echo '<div class="mo_boot_row mo_boot_mt-3"><div class="mo_boot_col-sm-3">
                                                    <strong>'.$value.' <span class="mo_oauth_highlight">*</span> : </strong>
                                                    </div>
                                                    <div class="mo_boot_col-sm-8">
                                                        <input class="mo-form-control" placeholder="'.$placeholder.'" type="text" id="" name="'.$value.'" value="" required>
                                                    </div></div>';
                                            }
                                        }
                                        else
                                        { ?>
                                                <div class="mo_boot_row mo_boot_mt-3">
                                                    <div class="mo_boot_col-sm-3">
                                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_APP_SCOPE');?><span class="mo_oauth_highlight">*</span> : </strong>
                                                    </div>
                                                    <div class="mo_boot_col-sm-8">
                                                        <input class="mo-form-control" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_APP_SCOPE_PLACEHOLDER');?>" type="text" id="mo_oauth_scope" name="mo_oauth_scope" value='<?php echo $app_scope ?>' required>
                                                    </div>
                                                </div>
                                                <div class="mo_boot_row mo_boot_mt-3">
                                                    <div class="mo_boot_col-sm-3">
                                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_AUTHORIZE_ENDPOINT');?><span class="mo_oauth_highlight">*</span> : </strong>
                                                    </div>
                                                    <div class="mo_boot_col-sm-8">
                                                        <input class="mo-form-control" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_AUTHORIZE_ENDPOINT_PLACEHOLDER');?>" type="text" id="mo_oauth_authorizeurl" name="mo_oauth_authorizeurl" value='<?php echo $appJson[$appLabel]["authorize"] ?>' required>
                                                    </div>
                                                    <div class="mo_boot_col-sm-1">
                                                        <em class="fa-regular fa-copy mo_copy copytooltip mo_oauth_copy_btn" ; onclick="copyToClipboard1('#mo_oauth_authorizeurl');";>
                                                            <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTH_COPIED');?></span>
                                                        </em>
                                                    </div>
                                                </div>
                                                <div class="mo_boot_row mo_boot_mt-3">
                                                    <div class="mo_boot_col-sm-3">
                                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_TOKEN_ENDPOINT'); ?><span class="mo_oauth_highlight">*</span> : </strong>
                                                    </div>
                                                    <div class="mo_boot_col-sm-8">
                                                        <input class="mo-form-control" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_TOKEN_ENDPOINT_PLACEHOLDER');?>" type="text" id="mo_oauth_accesstokenurl" name="mo_oauth_accesstokenurl" value='<?php echo $appJson[$appLabel]['token']; ?>' required>
                                                    </div>
                                                    <div class="mo_boot_col-sm-1">
                                                        <em class="fa-regular fa-copy mo_copy copytooltip mo_oauth_copy_btn" onclick="copyToClipboard1('#mo_oauth_accesstokenurl');";>
                                                            <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTH_COPIED');?></span>
                                                        </em>
                                                    </div>
                                                </div>
                                                <?php
                                                if(!isset($OauthApp['type']) || $OauthApp['type']=='oauth') {?>
                                                        <div class="mo_boot_row mo_boot_mt-3" id="mo_oauth_resourceownerdetailsurl_div">
                                                            <div class="mo_boot_col-sm-3">
                                                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_INFO_ENDPOINT'); ?><span class="mo_oauth_highlight">*</span> : </strong>
                                                            </div>
                                                            <div class="mo_boot_col-sm-8">
                                                                <input class="mo-form-control" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_INFO_ENDPOINT_PLACEHOLDER');?>" type="text" id="mo_oauth_resourceownerdetailsurl" name="mo_oauth_resourceownerdetailsurl" value='<?php echo $appJson[$appLabel]['userinfo']; ?>' required>
                                                            </div>
                                                            <div class="mo_boot_col-sm-1">
                                                                <em class="fa-regular fa-copy mo_copy copytooltip mo_oauth_copy_btn" onclick="copyToClipboard1('#mo_oauth_resourceownerdetailsurl');";>
                                                                    <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTH_COPIED');?></span>
                                                                </em>
                                                            </div>
                                                        </div>
                                                <?php }
                                        }
                                    }
                                    else
                                        { ?>
                                            <div class="mo_boot_row mo_boot_mt-3">
                                                <div class="mo_boot_col-sm-3">
                                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_APP_SCOPE');?><span class="mo_oauth_highlight">*</span> : </strong>
                                                </div>
                                                <div class="mo_boot_col-sm-8">
                                                    <input class="mo-form-control" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_APP_SCOPE_PLACEHOLDER');?>" type="text" id="mo_oauth_scope" name="mo_oauth_scope" value='<?php echo $app_scope ?>' required>
                                                </div>
                                            </div>
                                            <div class="mo_boot_row mo_boot_mt-3">
                                                <div class="mo_boot_col-sm-3">
                                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_AUTHORIZE_ENDPOINT'); ?><span class="mo_oauth_highlight">*</span> : </strong>
                                                </div>
                                                <div class="mo_boot_col-sm-8">
                                                    <input class="mo-form-control" type="text" id="mo_oauth_authorizeurl" name="mo_oauth_authorizeurl" value='<?php echo $authorize_endpoint; ?>' required>
                                                </div>
                                                <div class="mo_boot_col-sm-1">
                                                    <em class="fa-regular fa-copy mo_copy copytooltip mo_oauth_copy_btn" ; onclick="copyToClipboard1('#mo_oauth_authorizeurl');";>
                                                        <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTH_COPIED');?></span>
                                                    </em>
                                                </div>
                                            </div>
                                            <div class="mo_boot_row mo_boot_mt-3">
                                                <div class="mo_boot_col-sm-3">
                                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_TOKEN_ENDPOINT'); ?><span class="mo_oauth_highlight">*</span> : </strong>
                                                </div>
                                                <div class="mo_boot_col-sm-8">
                                                    <input class="mo-form-control" type="text" id="mo_oauth_accesstokenurl" name="mo_oauth_accesstokenurl" value='<?php echo $access_token_endpoint; ?>' required>
                                                </div>
                                                <div class="mo_boot_col-sm-1">
                                                    <em class="fa-regular fa-copy mo_copy copytooltip mo_oauth_copy_btn" onclick="copyToClipboard1('#mo_oauth_accesstokenurl');";>
                                                        <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTH_COPIED');?></span>
                                                    </em>
                                                </div>
                                            </div>
                                            <?php
                                            if(!isset($OauthApp['type']) || $OauthApp['type']=='oauth') {?>
                                                    <div class="mo_boot_row mo_boot_mt-3" id="mo_oauth_resourceownerdetailsurl_div">
                                                        <div class="mo_boot_col-sm-3">
                                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_INFO_ENDPOINT'); ?><span class="mo_oauth_highlight">*</span> : </strong>
                                                        </div>
                                                        <div class="mo_boot_col-sm-8">
                                                            <input class="mo-form-control" type="text" id="mo_oauth_resourceownerdetailsurl" name="mo_oauth_resourceownerdetailsurl" value='<?php echo $user_info_endpoint; ?>' required>
                                                        </div>
                                                        <div class="mo_boot_col-sm-1">
                                                            <em class="fa-regular fa-copy mo_copy copytooltip mo_oauth_copy_btn" onclick="copyToClipboard1('#mo_oauth_resourceownerdetailsurl');";>
                                                                <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTH_COPIED');?></span>
                                                            </em>
                                                        </div>
                                                    </div>
                                            <?php }
                                    }
                                    ?>    
                                    <?php if($mo_oauth_app == "okta") { ?>
                                        <div class="mo_boot_row mo_boot_mt-3">
                                            <div class="mo_boot_col-sm-3 mo_boot_d-flex mo_boot_align-items-center">
                                                <b><?php echo Text::_('COM_MINIORANGE_OAUTH_SET_CLIENT_CREDENTIALS'); ?></b>
                                            </div>
                                            <div class="form-check form-switch mo_boot_col-lg-2 mo_boot_col-sm-4 mo_boot_mx-4">
                                                <input type="radio" class="mo_oauth_radio form-check-input" name="mo_oauth_option" id="mo_oauth_in_header" value="header" 
                                                <?php echo ($mo_oauth_in_header == 'checked=true') ? 'checked' : ''; ?>>
                                                &nbsp; <?php echo Text::_('COM_MINIORANGE_OAUTH_SET_CREDENTIAL_IN_HEADER'); ?>
                                            </div>
                                            <div class="form-check form-switch mo_boot_col-lg mo_boot_col-sm-3">
                                                <input type="radio" class="mo_oauth_radio form-check-input" name="mo_oauth_option" id="mo_oauth_body" value="body" 
                                                <?php echo ($mo_oauth_in_body == 'checked=true') ? 'checked' : ''; ?>>
                                                &nbsp; <?php echo Text::_('COM_MINIORANGE_OAUTH_SET_CREDENTIAL_IN_BODY'); ?>
                                            </div>
                                        </div>
                                    <?php } else { ?>
                                        <div class="mo_boot_row mo_boot_mt-3 ">
                                            <div class="mo_boot_col-sm-3 mo_boot_d-flex mo_boot_align-items-center">
                                                    <b><?php echo Text::_('COM_MINIORANGE_OAUTH_SET_CLIENT_CREDENTIALS');?></b>
                                            </div>
                                            <div class="form-check form-switch mo_boot_col-lg-2 mo_boot_col-sm-4 mo_boot_mx-4">
                                                <input type="checkbox" class='mo_oauth_checkbox form-check-input' name="mo_oauth_in_header" id="mo_oauth_in_header" value="1" <?php echo " ".$mo_oauth_in_header ; ?> >&nbsp; <?php echo Text::_('COM_MINIORANGE_OAUTH_SET_CREDENTIAL_IN_HEADER');?>
                                            </div>
                                            <div class="form-check form-switch mo_boot_col-lg mo_boot_col-sm-3">
                                                    <input type="checkbox" class="mo_oauth_checkbox form-check-input" name="mo_oauth_body" id="mo_oauth_body" value="1" <?php echo " ".$mo_oauth_in_body ; ?> >&nbsp; <?php echo Text::_('COM_MINIORANGE_OAUTH_SET_CREDENTIAL_IN_BODY');?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                                    
                            <div class="mo_boot_row mo_boot_mt-2">
                                <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                    <button type="submit" class="oauth_blue_button"><?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_CONFIG');?></button>
                                </div>
                            </div>
                        </form>         
                    </div>
                </div>
                
                <!-- Step 3  -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header <?php echo $step2Check == true ? 'mo_oauth_cursor' : ''; ?>" 
                        onclick="<?php echo $step2Check == true ? 'return false;' : "toggleCollapse('mo_oauth_tab_content_step3', this.querySelector('.mo_toggle_icon'))"; ?>">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_STEP3'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> 
                            <?php echo $progress === 'step3' ? '-' : '+'; ?>
                        </div>
                    </div>
                    <div id="mo_oauth_tab_content_step3" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: <?php echo $progress === 'step3' ? 'block' : 'none'; ?>;">
                        <div class="mo_boot_row mo_boot_p-3">
                            <div class="mo_boot_col-sm-3">
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_TEST_CONFIG');?></strong>
                            </div>
                            <div class="mo_boot_col-sm-7">
                                <button class="oauth_blue_button " onclick="testConfiguration()"><?php echo Text::_('COM_MINIORANGE_OAUTH_TEST_CONFIG');?></button>
                            </div>
                            <div class="mo_boot_col-sm-12 mo_boot_mb-5">
                                <br>
                                <span>
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_TEST_CONFIG_NOTE');?> </strong> <?php echo Text::_('COM_MINIORANGE_OAUTH_TEST_CONFIG_NOTE_1');?>
                                </span>
                            </div>
                        </div>
                        <form id="oauth_mapping_form" name="oauth_config_form" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.saveMapping'); ?>">
                            <div class="mo_boot_row mo_boot_p-3 mo_boot_my-0 mo_boot_d-flex mo_oauth_align-items-center">
                                <div class="mo_boot_col-sm-3">
                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_EMAIL_ATTR'); ?><span class="mo_oauth_highlight">*</span> : </strong>
                                </div>
                                <div class="mo_boot_col-sm-7">
                                    <?php
                                    if (count($attributesNames) != 0 && count($attributesNames) != 1 ) {
                                        ?>
                                            <select required class="mo-form-control mo-form-control-select mo_boot_h-100" name="mo_oauth_email_attr" id="mo_oauth_email_attr">
                                                <option value="" selected><?php echo Text::_('COM_MINIORANGE_OAUTH_EMAIL_ATTR_NOTE');?></option>
                                            <?php
                                            foreach($attributesNames as $key => $value)
                                                {
                                                if($value == $email_attr) {
                                                    $checked = "selected";
                                                }
                                                else
                                                {
                                                    $checked = "";
                                                }
                                                if($value!="") {
                                                    echo"<option ".$checked." value='".$value."'>".$value."</option>";
                                                }
                                            }
                                            ?>
                                            </select>
                                            <?php
                                    } else {
                                        ?>
                                            <input type="text" name="" class="mo-form-control" disabled placeholder=" <?php echo Text::_('COM_MINIORANGE_OAUTH_TEST_CONFIG_NOTE_2');?> " id="">
                                            <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="mo_boot_row mo_boot_p-3 mo_boot_my-0 mo_boot_d-flex mo_oauth_align-items-center">
                                <div class="mo_boot_col-sm-3">
                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_FIRST_NAME_ATTR'); ?><span class="mo_oauth_highlight">*</span> : </strong>
                                </div>
                                <div class="mo_boot_col-sm-7">
                                    <?php
                                    if (count($attributesNames) != 0 && count($attributesNames) != 1 ) {
                                        ?>
                                            <select required class="mo-form-control mo-form-control-select mo_boot_h-100" name="mo_oauth_first_name_attr" id="mo_oauth_first_name_attr">
                                                <option value="" selected><?php echo Text::_('COM_MINIORANGE_OAUTH_FIRST_NAME_ATTR_NOTE');?></option>
                                                <?php
                                                foreach($attributesNames as $key => $value)
                                                    {
                                                    if($value == $first_name_attr) {
                                                        $checked = "selected";
                                                    }
                                                    else
                                                    {
                                                        $checked = "";
                                                    }
                                                    if($value!="") {
                                                        echo"<option ".$checked." value='".$value."'>".$value."</option>";
                                                    }
                                                }
                                                ?>
                                            </select>
                                            <?php
                                    }
                                    else
                                        {
                                        ?>
                                            <input type="text" name="" class="mo-form-control" disabled placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_TEST_CONFIG_NOTE_2'); ?>" id="">
                                        <?php
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="mo_boot_row mo_boot_my-3 mo_boot_p-3">
                                <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_d-flex mo_oauth_justify-center">
                                    <input type="submit" name="send_query" class="oauth_blue_button" value="<?php echo Text::_('COM_MINIORANGE_OAUTH_FINISH_CONFIG'); ?>" <?php echo ((count($attributesNames) > 1) ? '' : 'disabled'); ?>>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header <?php echo $step3Check==true ? 'mo_oauth_cursor' : ''; ?>" onclick="<?php echo $step3Check==true ? 'return false;' : "toggleCollapse('mo_oauth_tab_content_step4', this.querySelector('.mo_toggle_icon'))"; ?>">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_STEP4'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> <?php echo $progress === 'step4' ? '-' : '+'; ?></div>
                    </div>

                    <div id="mo_oauth_tab_content_step4" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: <?php echo $progress === 'step4' ? 'block' : 'none'; ?>;">
                        <div class="mo_boot_row mo_boot_p-3">
                            <div class="mo_boot_col mo_boot_mb-3 mo_oauth_alert">
                                <?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_URL_NOTE');?>
                            </div>
                            <div class="mo_boot_row mo_boot_col-sm-12 mo_boot_my-1">
                                <div class="mo_boot_col-sm-3">
                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_URL'); ?></strong>
                                </div>
                                <div class="mo_boot_col-sm-8">
                                    <input class="mo-form-control" id="loginUrl" type="text" readonly value='<?php echo Uri::root() . $redirectUrlByVersion . '?morequest=oauthredirect&app_name=' . $mo_oauth_app; ?>'>
                                </div>
                                <div class="mo_boot_col-sm-1 d-flex align-items-center">
                                    <em class="fa-regular fa-copy mo_copy copytooltip mo_oauth_copy_btn"
                                        onclick="copyToClipboard1('#loginUrl');">
                                        <span class="copytooltiptext"><?php echo Text::_('COM_MINIORANGE_OAUTH_COPIED'); ?></span>
                                    </em>
                                </div>
                            </div>
                            <div class="mo_boot_col mo_boot_mt-3">
                                <p><?php echo Text::_('COM_MINIORANGE_OAUTH_USER_AUTO_CREATION_NOT_AVAILABLE_NOTE');?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Advance Settings -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_tab_content_advance_settings', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCE_SETTINGS'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> <?php echo $progress === 'advance_setting' ? '-' : '+'; ?></div>
                    </div>

                    <div id="mo_oauth_tab_content_advance_settings" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: <?php echo $progress === 'advance_setting' ? 'block' : 'none'; ?>;">
                        <form method="POST" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.enableSSO'); ?>">
                            <input type="hidden" name="mo_oauth_app_name" value="<?php echo $mo_oauth_app; ?>">
                            <div class="mo_boot_row mo_boot_p-3">
                                <div class="mo_boot_col-sm-3">
                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SSO_ENABLE_DISABLE'); ?></strong>:
                                </div>
                                <div class="mo_boot_col-sm-1">
                                    <div class="form-switch">
                                        <input class="form-check-input" type="checkbox" value="1" name="mo_oauth_enable_sso" 
                                               id="mo_oauth_enable_sso" <?php echo ($sso_enable ? 'checked' : ''); ?> />
                                    </div>
                                </div>
                                <div class="mo_boot_col-sm-7 mo_boot_mx-3">
                                    <p><em><?php echo Text::_('COM_MINIORANGE_OAUTH_SSO_ENABLE_DISABLE_NOTE');?></em></p>
                                </div>
                            </div>

                            <div class="mo_boot_row mo_boot_p-3">
                                <div class="mo_boot_col-sm-3">
                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SSO_BUTTON_ENABLE_DISABLE'); ?></strong>:
                                </div>
                                <div class="mo_boot_col-sm-1">
                                    <div class="form-switch">
                                        <input class="form-check-input" type="checkbox" value="1" name="mo_oauth_enable_sso_button" 
                                               id="mo_oauth_enable_sso_button" <?php echo ($sso_button_enable ? 'checked' : ''); ?> />
                                    </div>
                                </div>
                                <div class="mo_boot_col-sm-7 mo_boot_mx-3">
                                    <p><em><?php echo Text::_('COM_MINIORANGE_OAUTH_SSO_BUTTON_NOTE');?></em></p>
                                </div>
                            </div>
                              <!-- Submit Button -->
                            <div class="mo_boot_col-sm-12 mo_boot_my-5 mo_boot_d-flex mo_oauth_justify-center">
                                <button type="submit" class="oauth_blue_button "><?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_SETTINGS'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Import/Export -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_tab_content_import', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title mo_boot_d-flex mo_oauth_align-items-center">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_IMPORT'); ?>
                            <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_IMPORT_EXPORT_CONFIGURATION_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/import-export-configuration" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right">+</div>
                    </div>

                    <div id="mo_oauth_tab_content_import" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <?php moImportAndExport($mo_oauth_app)?>
                    </div>
                </div>

                <!-- Premium Features -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_tab_content_premium', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_PRMIUM_FEATURE'); ?> <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_PREMIUM_FEATURE_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/grant-types" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span> <span title="<?php echo Text::_('COM_MINIORANGE_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>"><sup><img class="crown_img_small" src="<?php echo Uri::base();?>/components/com_miniorange_oauth/assets/images/crown.webp"></sup></span>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right">+</div>
                    </div>

                    <div id="mo_oauth_tab_content_premium" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row mo_boot_p-3  mo_boot_d-flex mo_oauth_align-items-center">
                            <div class="mo_boot_col-sm-3">
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_ADD_FEATURES_PKCE'); ?></strong>:
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <div class="form-check form-switch">
                                    <input class="mo_oauth_checkbox form-check-input mo_oauth_cursor" type="checkbox" disabled/>
                                </div>
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_p-3 mo_boot_d-flex mo_oauth_align-items-center">
                            <div class="mo_boot_col-sm-3">
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_ADD_FEATURES_JWT'); ?></strong>:
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <div class="form-check form-switch">
                                    <input class="mo_oauth_checkbox form-check-input mo_oauth_cursor" type="checkbox" disabled/>
                                </div>
                            </div>
                        </div>
                                    
                        <div class="mo_boot_row mo_boot_p-3 mo_boot_d-flex mo_oauth_align-items-center">
                            <div class="mo_boot_col-sm-3">
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_JWT_ALGO'); ?></strong>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <select readonly class="mo-form-control mo-form-control-select">
                                    <option value="HSA"><?php echo Text::_('COM_MINIORANGE_OAUTH_JWT_ALGO_HSA'); ?></option>
                                    <option value="RSA"><?php echo Text::_('COM_MINIORANGE_OAUTH_JWT_ALGO_RSA'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_p-3 mo_boot_d-flex mo_oauth_align-items-center">
                            <div class="mo_boot_col-sm-3">
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_JWKS_URI'); ?></strong>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input class="mo_security_textfield mo-form-control " required type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_JKWS_ENTER'); ?>" disabled="disabled" value="" />
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_p-3 mo_boot_d-flex mo_oauth_align-items-center">
                            <div class="mo_boot_col-sm-3">
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_TYPE'); ?></strong>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <select readonly class="mo-form-control mo-form-control-select">
                                    <option value="code"><?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_TYPE1'); ?></option>
                                    <option value="implicit"><?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_TYPE2'); ?></option>
                                    <option value="password"><?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_TYPE3'); ?></option>
                                    <option value="client"><?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_TYPE4'); ?></option>
                                    <option value="refresh"><?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_TYPE5'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        function testConfiguration() {
            var appname = "<?php echo $appLabel; ?>";
            var winl = (screen.width - 800) / 2;
            var wint = (screen.height - 600) / 2;
                
            var winprops =
                'height=600,' +
                'width=800,' +
                'top=' + wint + ',' +
                'left=' + winl + ',' +
                'scrollbars=1,resizable=1';
                
            var popupUrl =
                '<?php echo Uri::root() . $redirectUrlByVersion; ?>' +
                '?morequest=testattrmappingconfig&app=' + encodeURIComponent(appname);
                
            var myWindow = window.open(popupUrl, "Test Attribute Configuration", winprops);
                
            if (myWindow) {
                // Check every 500ms if the popup is closed
                var timer = setInterval(function () {
                    if (myWindow.closed) {
                        clearInterval(timer);
                        window.location.reload();
                    }
                }, 500);
            } else {
                // If popup is blocked
                alert("Please allow pop-ups for this site to test the configuration.");
            }
        }

    </script>
    <?php
}

function attributerole()
{
    global $license_tab_link;
    $attribute = getAppDetails();
    $email = isset($attribute['email_attr'])?$attribute['email_attr']:"";
    $username = isset($attribute['username_attr'])?$attribute['username_attr']:"";
    ?>

    <div class="mo_boot_col-sm-12 mo_main_oauth_section">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12">
                <div class="mo_boot_row mo_boot_my-0">
                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-8 mo_boot_d-flex mo_oauth_align-items-center">
                        <h3><?php echo Text::_('COM_MINIORANGE_OAUTH_ATTRIBUTE_MAPPING_2');?></h3>
                        <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_ATTRIBUTE_MAPPING_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/attribute-mapping" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span>
                    </div>
                </div>

                <!-- Basic Attribute Mapping -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-2 mo_oauth_mini_section">
                    <!-- Header -->
                    <div class="mo_oauth_tab_header mo_boot_pb-0">
                        <div class="mo_boot_col-sm-12 mo_oauth_tab_title mo_boot_d-flex mo_oauth_align_items-baseline gap-2">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_BASIC_ATT'); ?> <span><small>  <?php echo Text::_('COM_MINIORANGE_OAUTH_USER_ATT_TEXT'); ?>  </small></span>
                        </div>
                    </div>
                    <!-- Content -->
                    <div class="mo_oauth_tab_content mo_boot_pt-0">
                        <div class="mo_boot_col-sm-12">
                            <div class="mo_boot_row">
                                <div class="mo_boot_col-sm-12">
                                    <div class="mo_boot_row mo_boot_mt-0">
                                        <div class="mo_boot_col-sm-3">
                                            <label for=""><strong><?php echo Text::_('COM_MINIORANGE_OAUTH_USERNAME'); ?></strong><span class="mo_oauth_highlight">*</span> : </label>
                                        </div>
                                        <div class="mo_boot_col-sm-9">
                                            <input class="mo-form-control mo_oauth_cursor " disabled readonly type="text" id="mo_oauth_uname_attr" name="mo_oauth_uname_attr" value='<?php echo $username?>' placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_USERNAME_PLACE'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-sm-3">
                                            <label for=""><strong><?php echo Text::_('COM_MINIORANGE_OAUTH_EMAIL'); ?></strong><span class="mo_oauth_highlight">*</span> : </label>
                                        </div>
                                        <div class="mo_boot_col-sm-9">
                                            <input class="mo-form-control mo_oauth_cursor " disabled readonly type="text" name="mo_oauth_email_attr" value='<?php echo $email?>' placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_USERNAME_PLACE'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-sm-3">
                                            <label for="">
                                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_DISPLAY'); ?></strong><span class="mo_oauth_highlight">*</span> :
                                            </label>    
                                        </div>
                                        <div class="mo_boot_col-sm-9">
                                            <input class="mo-form-control mo_oauth_cursor " disabled type="text"  id="mo_oauth_dname_attr" name="mo_oauth_dname_attr" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_USERNAME_PLACE'); ?>" value=''>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_my-2">
                                        <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                            <input type="submit" disabled class="oauth_blue_button" name="send_query" value='<?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_ATTRIBUTE_MAPPING');?>'/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Map Additional User Attribute -->
                <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_user_additional_attribute', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_ADDITIONAL_USER_ATTRIBUTE_MAPPING'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> - </div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_user_additional_attribute" class="mo_oauth_tab_content" style="display:block">
                        
                        <!-- Profile -->
                        <div class="mo_boot_row mo_oauth_hightlight_white_bg mo_boot_mx-3">
                            <div class="mo_boot_col mo_boot_m-2">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_mx-4">
                                    <h3 class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_OAUTH_PROFILE_ATT'); ?></h3>
                                    <div>
                                        <input type="button" class="mo_boot_btn mo_oauth_input mo_oauth_all_btn mo_boot_px-3" disabled="true"  value="+" />
                                    </div>
                                </div>
                                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-sm-6 mo_boot_text-center">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_USER_PROFILE_ATTRIBUTE');?></strong>
                                        </div>
                                        <div class="mo_boot_col-sm-5 mo_boot_text-center">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SERVER_ATTRIBUTE');?></strong>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0 mo_boot_my-3">
                                        <div class="mo_boot_col-sm-6">
                                            <select class="mo-form-control mo-form-control-select" readonly>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_S_USER_PROFILE_ATTRIBUTE'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_ADD1'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_ADD2'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_CITY'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_REGION'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_COUNTRY'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_PIN'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_PHONE'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_WEBSITE'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_FAV_BOOK'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_ABOUT_ME'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_DOB'); ?></option>
                                            </select>
                                        </div>
                                        <div class="mo_boot_col-sm-5">
                                            <input type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_DISPLAY_NOTE'); ?>"  class="mo-form-control mo_oauth_cursor " disabled="disabled"/>
                                        </div>
                                        <div class="mo_boot_col-sm-1">
                                           <input type="button" class="mo_boot_btn float-right mo_boot_btn-secondary mo_boot_px-3 mo_boot_mx-1 mo_oauth_cursor " disabled="true" value="-" />
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0 mo_boot_my-3">
                                        <div class="mo_boot_col-sm-6">
                                            <select class="mo-form-control mo-form-control-select" readonly>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_S_USER_PROFILE_ATTRIBUTE'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_ADD1'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_ADD2'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_CITY'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_REGION'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_COUNTRY'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_PIN'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_PHONE'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_WEBSITE'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_FAV_BOOK'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_ABOUT_ME'); ?></option>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_DOB'); ?></option>
                                            </select>
                                        </div>
                                        <div class="mo_boot_col-sm-5">
                                            <input type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_MAP'); ?>"  class="mo-form-control mo_oauth_cursor " disabled="disabled"/>
                                        </div>
                                        <div class="mo_boot_col-sm-1">
                                           <input type="button" class="mo_boot_btn float-right mo_boot_btn-secondary mo_boot_px-3 mo_boot_mx-1 mo_oauth_cursor " disabled="true" value="-" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Field -->
                        <div class="mo_boot_row mo_oauth_hightlight_white_bg mo_boot_mx-3">
                            <div class="mo_boot_col mo_boot_m-2">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_mx-4">
                                    <h3 class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_OAUTH_FIELD_ATT'); ?></h3>
                                    <div>
                                        <input type="button" class="mo_boot_btn mo_oauth_input mo_oauth_all_btn mo_boot_px-3" disabled="true"  value="+" />
                                    </div>
                                </div>
                                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-sm-6 mo_boot_text-center">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_USER_FIELD_ATTRIBUTE');?></strong>
                                        </div>
                                        <div class="mo_boot_col-sm-5 mo_boot_text-center">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SERVER_ATTRIBUTE');?></strong>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0 mo_boot_my-3">
                                        <div class="mo_boot_col-sm-6">
                                            <input class="mo-form-control mo_oauth_cursor " type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_DISPLAY_NOTE2'); ?>" disabled/>
                                        </div>
                                        <div class="mo_boot_col-sm-5">
                                            <input class="mo-form-control mo_oauth_cursor " type="text" disabled placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_DISPLAY_NOTE'); ?>"  />
                                        </div>
                                        <div class="mo_boot_col-sm-1">
                                            <input type="button" class="mo_boot_btn float-right mo_boot_btn-secondary mo_boot_px-3 mo_boot_mx-1 mo_oauth_cursor " disabled="true" value="-" />
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0 mo_boot_my-3">
                                        <div class="mo_boot_col-sm-6">
                                            <input class="mo-form-control mo_oauth_cursor " type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_DISPLAY_NOTE2'); ?>" disabled/>
                                        </div>
                                        <div class="mo_boot_col-sm-5">
                                            <input class="mo-form-control mo_oauth_cursor " type="text" disabled placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_DISPLAY_NOTE'); ?>" />
                                        </div>
                                        <div class="mo_boot_col-sm-1">
                                            <input type="button" class="mo_boot_btn float-right mo_boot_btn-secondary mo_boot_px-3 mo_boot_mx-1 mo_oauth_cursor " disabled="true" value="-" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact  -->
                         <div class="mo_boot_row mo_oauth_hightlight_white_bg mo_boot_mx-3">
                            <div class="mo_boot_col mo_boot_m-2">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_mx-4">
                                    <h3 class="mo_boot_mb-0"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_MAPPING'); ?></h3>
                                    <div>
                                        <input type="button" class="mo_boot_btn mo_oauth_input mo_oauth_all_btn mo_boot_px-3" disabled="true"  value="+" />
                                    </div>
                                </div>
                                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                                    <div class="mo_boot_row">
                                        <div class="mo_boot_col-sm-6 mo_boot_text-center">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE');?></strong>
                                        </div>
                                        <div class="mo_boot_col-sm-5 mo_boot_text-center">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SERVER_ATTRIBUTE');?></strong>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0 mo_boot_my-3">
                                        <div class="mo_boot_col-sm-6">
                                            <select class="mo-form-control mo-form-control-select" readonly>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_SELECT_CONTACT_ATTRIBUTE'); ?></option>
                                                <option value="image"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_IMAGE'); ?></option>
                                                <option value="con_position"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_POSITION'); ?></option>
                                                <option value="email_to"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_EMAIL'); ?></option>
                                                <option value="address"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_ADDRESS'); ?></option>
                                                <option value="suburb"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_CITY'); ?></option>
                                                <option value="state"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_STATE'); ?></option>
                                                <option value="postcode"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_POSTAL_CODE'); ?></option>
                                                <option value="country"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_COUNTRY'); ?></option>
                                                <option value="telephone"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_TELEPHONE'); ?></option>
                                                <option value="mobile"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_MOBILE'); ?></option>
                                                <option value="fax"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_FAX'); ?></option>
                                                <option value="webpage"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_WEBSITE'); ?></option>
                                                <option value="sortname1"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_FIRST_SORT_FIELD'); ?></option>
                                                <option value="sortname2"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_SECOND_SORT_FIELD'); ?></option>
                                                <option value="sortname3"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_THIRD_SORT_FIELD'); ?></option>
                                            </select>
                                        </div>
                                        <div class="mo_boot_col-sm-5">
                                            <input type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_DISPLAY_NOTE'); ?>"  class="mo-form-control mo_oauth_cursor " disabled="disabled"/>
                                        </div>
                                        <div class="mo_boot_col-sm-1">
                                           <input type="button" class="mo_boot_btn float-right mo_boot_btn-secondary mo_boot_px-3 mo_boot_mx-1 mo_oauth_cursor " disabled="true" value="-" />
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_m-0 mo_boot_p-0 mo_boot_my-3">
                                        <div class="mo_boot_col-sm-6">
                                            <select class="mo-form-control mo-form-control-select" readonly>
                                                <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_SELECT_CONTACT_ATTRIBUTE'); ?></option>
                                                <option value="image"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_IMAGE'); ?></option>
                                                <option value="con_position"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_POSITION'); ?></option>
                                                <option value="email_to"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_EMAIL'); ?></option>
                                                <option value="address"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_ADDRESS'); ?></option>
                                                <option value="suburb"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_CITY'); ?></option>
                                                <option value="state"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_STATE'); ?></option>
                                                <option value="postcode"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_POSTAL_CODE'); ?></option>
                                                <option value="country"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_COUNTRY'); ?></option>
                                                <option value="telephone"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_TELEPHONE'); ?></option>
                                                <option value="mobile"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_MOBILE'); ?></option>
                                                <option value="fax"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_FAX'); ?></option>
                                                <option value="webpage"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_WEBSITE'); ?></option>
                                                <option value="sortname1"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_FIRST_SORT_FIELD'); ?></option>
                                                <option value="sortname2"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_SECOND_SORT_FIELD'); ?></option>
                                                <option value="sortname3"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_ATTRIBUTE_THIRD_SORT_FIELD'); ?></option>
                                            </select>
                                        </div>
                                        <div class="mo_boot_col-sm-5">
                                            <input type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_MAP'); ?>"  class="mo-form-control mo_oauth_cursor " disabled="disabled"/>
                                        </div>
                                        <div class="mo_boot_col-sm-1">
                                           <input type="button" class="mo_boot_btn float-right mo_boot_btn-secondary mo_boot_px-3 mo_boot_mx-1 mo_oauth_cursor " disabled="true" value="-" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mo_boot_row mo_boot_my-2">
                            <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                <input type="submit" disabled class="oauth_blue_button" name="send_query" value='<?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_ATTRIBUTE_MAPPING');?>'/>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Group And Attribute Mapping -->
            <div class="mo_boot_col-sm-12 mo_boot_mt-5">
               <div class="mo_boot_row mo_boot_my-0">
                   <div class="mo_boot_col-lg-6 mo_boot_col-sm-8 mo_boot_d-flex mo_oauth_align-items-center">
                        <h3><?php echo Text::_('COM_MINIORANGE_OAUTH_GROUPS');?></h3>
                        <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_GROUP_ROLE_MAPPING_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/role-mapping" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span>
                    </div>
               </div>

               <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_group_mapping', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_GROUP_MAPPING'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> + </div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_group_mapping" class="mo_oauth_tab_content mo_boot_pt-0" style="display:none">
                        <div class="mo_boot_row mo_boot_px-3">
                            <div class="mo_boot_col-sm-12 mo_boot_my-2">
                                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center">
                                    <div class="mo_boot_col-sm-4">
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_SELECT_DEFAULT_GROUP_FOR_NEW_USER');?>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <?php
                                            $db = Factory::getDbo();
                                            $db->setQuery(
                                                $db->getQuery(true)
                                                    ->select('*')
                                                    ->from("#__usergroups")
                                            );
                                            $groups = $db->loadrowList();
                                        
                                            echo '<select class="mo-form-control mo-form-control-select mo_oauth_cursor-pointer" readonly name="mapping_value_default" id="default_group_mapping">';
                                        
                                        foreach ($groups as $group)
                                                {
                                            if ($group[4] != 'Super Users') {
                                                echo '<option selected="selected" value = "' . $group[0] . '">' . $group[4] . '</option>';
                                            }
                                        }
                                        ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                                <div class="mo_boot_row mo_boot_mt-2 mo_boot_d-flex mo_oauth_align-items-center">
                                    <div class="mo_boot_col-sm-4">
                                        <p><?php echo Text::_('COM_MINIORANGE_OAUTH_GROUP_ATTRIBUTE_NAMES');?></p>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input class="mo-form-control mo_oauth_cursor " placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_GROUP_ATTRIBUTE_NAMES_PLACEHOLDER');?>" type="text" id="mo_oauth_gname_attr" name="mo_oauth_gname_attr" value='' disabled>
                                    </div>
                                </div>
                                <hr class="bg-dark">
                            </div>
                            <div class=" mo_boot_col-sm-12 mo_boot_my-2">
                                <div class="mo_boot_row mo_boot_mt-3">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_GROUP_NAME_IN_JOOMLA');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_GROUP_ROLE_NAME_IN_CONFIGURED_APP');?></strong>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3">
                                    <?php
                                        $user_role = array();
                                    if (empty($role_mapping_key_value)) {
                                        foreach ($groups as $group) {
                                            if ($group[4] != 'Super Users') {
                                                echo '<div class="mo_boot_col-sm-4 mo_boot_mt-2">' . $group[4] . '</div><div class="mo_boot_col-sm-8 mo_boot_mt-2"><input class="mo-form-control"  disabled type="text" id="oauth_group_attr_values' . $group[0] . '" name="oauth_group_attr_values' . $group[0] . '" value= "" placeholder="'.Text::_('COM_MINIORANGE_OAUTH_GROUP_ROLE_NAME_IN_CONFIGURED_APP_PLACEHOLDER'). $group[4] . '" "' . ' /></div>';
                                            }
                                        }
                                    }
                                    else
                                        {
                                        foreach ($groups as $group)
                                        {
                                            if ($group[4] != 'Super Users') {
                                                $role_value = array_key_exists($group[0], $role_mapping_key_value) ? $role_mapping_key_value[$group[0]] : "";
                                                echo '<div class="mo_boot_col-sm-4 offset-sm-1"><strong>' . $group[4] . '</strong></div><div class="mo_boot_col-sm-6"><input  class="mo-form-control"  disabled type="text" id="oauth_group_attr_values' . $group[0] . '" name="oauth_group_attr_values' . $group[0] . '" value= "' . $role_value . '" placeholder="'.Text::_('COM_MINIORANGE_OAUTH_GROUP_ROLE_NAME_IN_CONFIGURED_APP_PLACEHOLDER'). $group[4] . '" "' . ' /></div>';
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_mt-4">
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                        <input type="submit" name="send_query" value='<?php echo Text::_('COM_MINIORANGE_SAVE_GROUP_MAPPING');?>' disabled class="oauth_blue_button mo_oauth-cursor"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

               </div>

               <div class="mo_boot_col-sm-12 mo_boot_p-2 mo_boot_mt-4 mo_oauth_mini_section">
                    <!-- Header -->
                    <div class="mo_oauth_tab_header" onclick="toggleCollapse('mo_oauth_advance_group_mapping', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_boot_col-sm-11 mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCED_GROUP_MAPPING'); ?>
                        </div>
                        <div class="mo_boot_col-sm-1 mo_toggle_icon mo_boot_text-right"> + </div>
                    </div>
                    <!-- Content -->
                    <div id="mo_oauth_advance_group_mapping" class="mo_oauth_tab_content mo_boot_pt-0" style="display:none">
                        <div class="mo_boot_row mo_boot_p-3">
                            <div class="mo_boot_col-sm-12">  

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="mo_oauth_check1" disabled>
                                    <label class="form-check-label" for="mo_oauth_check1">
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_TEXT_FILE'); ?>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="mo_oauth_check2" disabled>
                                    <label class="form-check-label" for="mo_oauth_check2">
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_DO_NOT_UPDATE_EXISTING_USER_GROUPS'); ?>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="mo_oauth_check3" disabled>
                                    <label class="form-check-label" for="mo_oauth_check3">
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_DO_NOT_UPDATE_EXISTING_USER_GROUPS_AND_NEWLY_MAPPED_ROLES'); ?>
                                    </label>
                                </div>
                                                
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="mo_oauth_check4" disabled>
                                    <label class="form-check-label" for="mo_oauth_check4">
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_DO_NOT_AUTO_CREATE_USERS_IF_ROLES_NOT_MAPPED'); ?>
                                    </label>
                                </div>

                            </div>

                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_mt-4">
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                        <input type="submit" disabled name="send_query" 
                                               value="<?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_ADD_SETTINGS'); ?>" 
                                               class="oauth_blue_button mo_oauth_cursor"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

               </div>
            </div>

        </div>
    </div>
    <?php
}

function proxy_setup()
{
    // Fetch saved proxy configuration from the database
    $db = Factory::getDbo();
    $query = $db->getQuery(true)
        ->select('*')
        ->from($db->quoteName('#__miniorange_oauth_config'));
    $db->setQuery($query);
    $proxyConfig = $db->loadObject();

    // Set default values if no config is found
    $proxy_host_name = $proxyConfig->proxy_host_name ?? '';
    $port_number = $proxyConfig->port_number ?? '';
    $username = $proxyConfig->username ?? '';
    $password = $proxyConfig->password ?? '';
    // Render the Proxy Setup Form
    ?>
    <div  class="mo_boot_container-fluid">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12">
                <h1 class="mo_export_heading mo_boot_pt-4 "><?php echo Text::_('COM_MINIORANGE_PROXY_SETUP'); ?></h1>
                <p><?php echo Text::_('COM_MINIORANGE_PROXY_SETUP_DESCRIPTION'); ?></p>
                <form action="<?php echo Route::_('index.php?option=com_miniorange_oauth&task=accountsetup.proxyConfig'); ?>" method="post" name="proxy_form">
                    <div class="mo_boot_col-sm-12">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-3">
                                <label for="mo_proxy_host">
                                    <?php echo Text::_('COM_MINIORANGE_PROXY_HOSTNAME'); ?><span class="mo_oauth_highlight">*</span>:
                                </label>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input class="mo-form-control" type="text" id="mo_proxy_host" name="mo_proxy_host" value="<?php echo htmlspecialchars($proxy_host_name); ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_PROXY_HOSTNAME_PLACEHOLDER'); ?>" required>
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_mt-3">
                            <div class="mo_boot_col-sm-3">
                                <label for="mo_proxy_port">
                                    <?php echo Text::_('COM_MINIORANGE_PROXY_PORT'); ?><span class="mo_oauth_highlight">*</span>:
                                </label>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input class="mo-form-control" type="number" id="mo_proxy_port" name="mo_proxy_port" value="<?php echo htmlspecialchars($port_number); ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_PROXY_PORT_PLACEHOLDER'); ?>" required>
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_mt-3">
                            <div class="mo_boot_col-sm-3">
                                <label for="mo_proxy_username"><?php echo Text::_('COM_MINIORANGE_PROXY_USERNAME'); ?>:</label>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input class="mo-form-control" type="text" id="mo_proxy_username" name="mo_proxy_username" value="<?php echo htmlspecialchars($username); ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_PROXY_USERNAME_PLACEHOLDER'); ?>">
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_mt-3">
                            <div class="mo_boot_col-sm-3">
                                <label for="mo_proxy_password"><?php echo Text::_('COM_MINIORANGE_PROXY_PASSWORD'); ?>:</label>
                            </div>
                            <div class="mo_boot_col-sm-9">
                                <input class="mo-form-control" type="password" id="mo_proxy_password" name="mo_proxy_password" value="<?php echo htmlspecialchars($password); ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_PROXY_PASSWORD_PLACEHOLDER'); ?>">
                            </div>
                        </div>
                        <div class="mo_boot_row mo_boot_mt-2">
                            <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center mo_boot_mb-3">
                                <input type="submit" value=<?php echo Text::_('COM_MINIORANGE_SAVE'); ?> class="mo_boot_btn mo_oauth_cursor mo_oauth_all_btn mo_boot_p-1">
                                <input type="button" value=<?php echo Text::_('COM_MINIORANGE_RESET'); ?> onclick="window.location='<?php echo Route::_('index.php?option=com_miniorange_oauth&task=accountsetup.proxyConfigReset'); ?>'" class="mo_boot_btn mo_oauth_cursor mo_oauth_all_btn mo_boot_p-1">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>   

    
    <?php
}

function moOAuthConfiguration()
{
    global $license_tab_link;
    global $license_tab_link;
    $utility_data = new MoOauthCustomer();
    $app_data = $utility_data->getAppJason();
    $appArray = json_decode($app_data, true);
    $app = Factory::getApplication();
    if (method_exists($app, 'getInput')) {
        $input = $app->getInput();
    } else { // Joomla 3
        $input = $app->input;
    }
    $get = $input->get->getArray();
    $attribute = getAppDetails();
    $isAppConfigured = empty($attribute['client_secret']) || empty($attribute['client_id']) || empty($attribute['custom_app'])|| empty($attribute['redirecturi'])?false:true;
    if(isset($get['moAuthAddApp']) && !empty($get['moAuthAddApp']) ) {
        configuration($appArray[$get['moAuthAddApp']], $get['moAuthAddApp']);
        return;
    }
    else if($isAppConfigured) {
        configuration($appArray[$attribute['appname']], $attribute['appname']);
        return;
    }
    else
    { ?>

    
            <div class="mo_boot_row mo_boot_px-5 mo_boot_mt-4 mo_boot_d-flex mo_ouath_flex-gap-3 ">
                <div onclick = "changeSubMenu('#configuration' , this , '#mo_pre_configure_app')" class="mo_boot_col mo_oauth_sub_menu mo_oauth_sub_menu_active">
                    <span><?php echo Text::_('COM_MINIORANGE_OAUTH_PRE_CONFIG_APPS');?></span>
                </div>
                 <div onclick = "changeSubMenu('#configuration' ,this,'#mo_custom_app')" class=" mo_boot_col mo_oauth_sub_menu">
                    <span><?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOM_APPLICATION');?></span>
                </div>
                <div onclick = "changeSubMenu('#configuration' ,this,'#mo_multiple_provider')" class="mo_boot_col mo_oauth_sub_menu">
                    <span><?php echo Text::_('COM_MINIORANGE_OAUTH_ADD_MORE_APPS');?></span>
                    <span title="<?php echo Text::_('COM_MINIORANGE_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>" > <sup><img class="crown_img_small" src="<?php echo Uri::base();?>/components/com_miniorange_oauth/assets/images/crown.webp"></sup> </span>
                </div>
            </div>

            <div class="mo_boot_row mo_boot_m-0 mo_boot_p-1">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3" id="mo_pre_configure_app">
                        <?php selectAppByIcon();?>
                    </div>
                    <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3" style="display:none" id="mo_custom_app">
                        <?php selectCustomApp(); ?>
                    </div>
                    <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3" style="display:none" id="mo_multiple_provider">
                        <div class="mo_boot_col-sm-12 alert-info mo_boot_p-5 mo_boot_my-4">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_FUNCTIONALITY');?>
                            <strong> <a href="mailto:joomlasupport@xecurify.com">joomlasupport@xecurify.com</a></strong>.
                        </div>
                    </div>
                </div>
            </div>

        <?php
    }
}

function grant_type_settings()
{
    global $license_tab_link;
    ?>
    <div class="mo_boot_row mr-1 mo_boot_my-3 ">
        <div class="mo_boot_col-sm-12 mo_boot_mt-4">
            <h3 style="display:none"><?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_SETTINGS');?><sup><code><small><a href="<?php echo $license_tab_link;?>"  rel="noopener noreferrer">[PREMIUM,ENTERPRISE]</a></small></code></sup></h3>
            <br>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-2">
            <h4><?php echo Text::_('COM_MINIORANGE_OAUTH_S_GRANT_TYPE');?></h4>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-2 grant_types">
            <div class="mo_boot_form-check mo_boot_form-switch">
            <input checked disabled type="checkbox" class="mo_boot_form-check-input">&emsp;<strong><?php echo Text::_('COM_MINIORANGE_OAUTH_AUTH_CODE_GRANT');?></strong>&emsp;<code><small>[DEFAULT]</small></code>
            <blockquote><?php echo Text::_('COM_MINIORANGE_OAUTH_CODE_TEXT');?></blockquote>
            <input disabled type="checkbox" class="mo_boot_form-check-input">&emsp;<strong><?php echo Text::_('COM_MINIORANGE_OAUTH_IMPLICIT_GRANT');?></strong>
            <blockquote><?php echo Text::_('COM_MINIORANGE_OAUTH_CODE_TEXT2');?></blockquote>
            <input disabled type="checkbox" class="mo_boot_form-check-input">&emsp;<strong><?php echo Text::_('COM_MINIORANGE_OAUTH_PWD_GRANT');?></strong>
            <blockquote><?php echo Text::_('COM_MINIORANGE_OAUTH_CODE_TEXT3');?></blockquote>
            <input disabled type="checkbox" class="mo_boot_form-check-input">&emsp;<strong><?php echo Text::_('COM_MINIORANGE_OAUTH_REFRESH_TOKEN_GRANT');?></strong>
            <blockquote><?php echo Text::_('COM_MINIORANGE_OAUTH_CODE_TEXT4');?></blockquote>
            </div>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-2">
            <br>
            <h3 class="mo_oauth_display-inline"><?php echo Text::_('COM_MINIORANGE_OAUTH_JWT_VALID');?><sup><code><small><a href="<?php echo $license_tab_link;?>"  rel="noopener noreferrer">[PREMIUM,ENTERPRISE]</a></small></code></sup></h3>
            <br>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-2 mo_boot_form-check mo_boot_form-switch">
            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_JWT_VERIFY');?></strong>
            <input type="checkbox"class="mo_boot_form-check-input" value="" disabled/>
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_mt-2">
            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_JWT_ALGO');?></strong>
            <select disabled>
                <option><?php echo Text::_('COM_MINIORANGE_OAUTH_JWT_ALGO_HSA');?></option>
                <option><?php echo Text::_('COM_MINIORANGE_OAUTH_JWT_ALGO_RSA');?></option>
            </select> 
        </div>
        <div class="mo_boot_col-sm-12 mo_boot_my-2">
            <div class="notes">
                <hr /><?php echo Text::_('COM_MINIORANGE_OAUTH_CODE_TEXT5');?>
                <a href="<?php echo $license_tab_link;?>" rel="noopener noreferrer"><?php echo Text::_('COM_MINIORANGE_OAUTH_CODE_TEXT6');?></a> <?php echo Text::_('COM_MINIORANGE_OAUTH_CODE_TEXT7');?>
            </div>
        </div>
    </div>
    <?php
}

function loginlogoutsettings()
{
    global $license_tab_link;
    ?>

    <div class="mo_boot_col-sm-12 mo_main_oauth_section">
        <div class="mo_boot_row">
            <div class="mo_boot_col-sm-12">
                <!-- Additional Settings -->
                <div class="mo_boot_col-sm-12 mo_oauth_mini_section">

                    <!-- Header with toggle -->
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_additional_settings', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title mo_boot_d-flex mo_oauth_align-items-center">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_ADD_SETTINGS'); ?>
                            <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_ADDITIONAL_SETTINGS_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/advanced-setting" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_additional_settings" class="mo_oauth_tab_content" style="display: none;">
                        <div class="mo_boot_col mo_boot_px-0">
                            <div class="mo_boot_form-check form-switch mo_boot_mb-3">
                                <input type="checkbox" class="mo_oauth_checkbox form-check-input mo_oauth_cursor" 
                                       name="mo_oauth_auto_redirect" id="mo_oauth_auto_redirect" value="1" disabled>
                                <label id="mo_oauth_switch">
                                    <span><?php echo Text::_('COM_MINIORANGE_OAUTH_RESTRICT_ANNONYMOUS_ACCESS');?></span>
                                </label>
                            </div>

                            <div class="mo_boot_text-center mo_boot_mt-3">
                                <input type="submit" name="send_query" 
                                       value="<?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_SETTINGS'); ?>" 
                                       disabled class="oauth_blue_button mo_oauth_cursor"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Domain Restriction -->
                 <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section">
                    <!-- Header -->
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_domain_restriction', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title mo_boot_d-flex mo_oauth_align-items-center">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_CODE_DOMAIN_REST'); ?>
                            <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_DOMAIN_RESTRICTION_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/advanced-setting" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <!-- Content -->
                    <div id="mo_oauth_domain_restriction" class="mo_oauth_tab_content" style="display: none;">
                        <div class="mo_boot_col-sm-12 mo_boot_px-0">
                            <div class="mo_boot_row mo_boot_p-3 mo_boot_mt-0">
                                <div class="mo_boot_col mo_oauth_alert mo_boot_mx-0 ">
                                    <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_TEST_CONFIG_NOTE');?></strong> <?php echo Text::_('COM_MINIORANGE_OAUTH_RESTRICTED_DOMAINS_TEXT');?>
                                </div>
                                <div class="mo_boot_col-sm-12 mo_boot_px-0 mo_boot_my-4">
                                    <div class="mo_boot_row mo_boot_m-1">
                                        <div class="mo_boot_col-sm-3">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_RESTRICTED_DOMAINS');?></strong>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <textarea class="mo_boot_col-sm-12 mo_boot_p-2" name="" id="" rows="6" id="mo_oauth_restricted_domains" name="mo_oauth_restricted_domains" value='' disabled placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_RESTRICTED_DOMAINS_NAME_NOTE');?>"></textarea>
                                            <p><em><?php echo Text::_('COM_MINIORANGE_OAUTH_RESTRICTED_DOMAINS_NOTE');?></em></p>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_m-1 mo_boot_mt-2">
                                        <div class="mo_boot_col-sm-3">
                                            <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_ALLOWED_DOMAINS');?></strong>
                                        </div>
                                        <div class="mo_boot_col-sm-8">
                                            <textarea class="mo_boot_col-sm-12 mo_boot_p-2" name="" id="" rows="6" value='' disabled placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_RESTRICTED_DOMAINS_NAME_NOTE');?>"></textarea>
                                            <p><em><?php echo Text::_('COM_MINIORANGE_OAUTH_ALLOWED_DOMAINS_NOTE');?></em></p>
                                        </div>
                                    </div>
                                    <div class="mo_boot_row mo_boot_mt-2">
                                        <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                            <input type="submit" disabled name="send_query" value='<?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_DOMAIN_RESTRICTION');?>' class="oauth_blue_button mo_oauth_cursor"/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                 </div>

                 <!-- Redirect Urls -->
                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_redirect_urls', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title mo_boot_d-flex mo_oauth_align-items-center">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_REDIRECT_URLS'); ?>
                            <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_REDIRECT_URLS_SETTING_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/advanced-setting" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <div id="mo_oauth_redirect_urls" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row mo_boot_p-0" >
                            <div class="mo_boot_col mo_oauth_alert">
                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_TEST_CONFIG_NOTE');?></strong><?php echo Text::_('COM_MINIORANGE_OAUTH_RESTRICTED_DOMAINS_NOTE2');?>
                            </div>
                            <div class="mo_boot_col-sm-12 mo_boot_mt-4">
                                <div class="mo_boot_row mo_boot_p-0 mo_boot_m-1">
                                    <div class="mo_boot_col-sm-3 mo_boot_p-0">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_REDIRECT_URL');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8 mo_boot_p-0">
                                        <input class="mo-form-control mo_oauth_cursor" type="text" value='' disabled placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_REDIRECT_URL_NOTE_SSO');?>">
                                        <p><em><?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_REDIRECT_URL_NOTE');?></em></p>
                                    </div>
                                </div>

                                <div class="mo_boot_row mo_boot_p-0 mo_boot_m-1">
                                    <div class="mo_boot_col-sm-3 mo_boot_p-0">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_LOGOUT_REDIRECT_URL');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8 mo_boot_p-0">
                                        <input class="mo-form-control mo_oauth_cursor" type="text" value='' disabled placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_REDIRECT_URL_NOTE_SSO');?>">
                                        <p><em><?php echo Text::_('COM_MINIORANGE_OAUTH_LOGOUT_REDIRECT_URL_NOTE');?></em></p>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_p-0 mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                        <input type="submit" disabled name="send_query" value='<?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_REDIRECT_URL');?>' class="oauth_blue_button mo_oauth-cursor"/>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>

                <!-- Backdoor Url -->
                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_backdoor_url', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title mo_boot_d-flex mo_oauth_align-items-center">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_BACKDOOR_URL'); ?>
                            <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_BACKDOOR_URL_SETTINGS_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/advanced-setting" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <div id="mo_oauth_backdoor_url" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row">
                            <div class=" mo_boot_col-sm-12 mo_boot_my-2">
                                <div class="mo_boot_row ">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOM_LOGIN_URL');?><code> /administrator</code> )</strong>:
                                    </div>
                                    <div class="mo_boot_col-sm-2 mo_boot_ml-5 mo_boot_form-check form-switch">
                                        <input class="mo_oauth_checkbox form-check-input mo_oauth_cursor" type="checkbox" disabled/>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_ACCESS');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input class="mo_security_textfield admin_log_url mo-form-control mo_oauth_cursor" required type="text" name="access_lgn_urlky" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_ENTER_LOGIN_KEY');?>" disabled="disabled" value="" />
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center">
                                    <div class="mo_boot_col-sm-4">
                                        <strong> <?php echo Text::_('COM_MINIORANGE_OAUTH_CURR_LOGIN');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <?php echo Uri::base(); ?>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_ALU');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <?php echo Uri::base().'?{accessKey}'; ?>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_NOTE_ADMIN_FAIL');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <select class="mo_security_dropdown redirect_after_failure mo-form-control mo-form-control-select mo_oauth_cursor" id="failure_response" name="after_adm_failure_response" disabled="disabled" readonly>
                                            <option value="redirect_homepage" ><?php echo Text::_('COM_MINIORANGE_OAUTH_NOTE_HOMEPAGE');?></option>
                                            <option value="404_custom_message" ><?php echo Text::_('COM_MINIORANGE_OAUTH_NOTE_404');?></option>
                                            <option value="custom_redirect_url" ><?php echo Text::_('COM_MINIORANGE_OAUTH_NOTE_REDIRECT');?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" style="display:none" id="custom_fail_dest">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_NOTE_ADMIN_REDIRECT_FAIL');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input class="mo-form-control mo_security_textfield mo_boot_col-sm-12 mo_oauth_cursor" type="text" disabled="disabled" name="custom_failure_destination" disabled="disabled" value=""/>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3" style="display:none" id="custom_message">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOM_MSG');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <textarea class="mo-form-control mo_security_textfield mo_boot_col-sm-12 mo_oauth_cursor" disabled="disabled" name="custom_message_after_fail"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_mt-4">
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                        <input type="submit" disabled name="send_query" value=' <?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_GROUP_MAPPING');?>' class="oauth_blue_button mo_oauth_cursor"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Single Logout URl -->
                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_single_logout_url', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title mo_boot_d-flex mo_oauth_align-items-center">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_SINGLE_LOGOUT'); ?>
                            <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_SINGLE_LOGOUT_SETTINGS_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/openid-connect-single-logout" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <div id="mo_oauth_single_logout_url" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center">
                                    <div class="mo_boot_col-sm-5 mo_boot_col-lg-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_ENABLE_SINGLE_LOGOUT');?></strong>:
                                    </div>
                                    <div class="mo_boot_col-sm-7 mo_boot_col-lg-8">
                                        <div class="mo_boot_form-check form-switch">
                                            <input class="mo_oauth_checkbox form-check-input mo_oauth_cursor" type="checkbox" disabled/>
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center">
                                    <div class="mo_boot_col-sm-5 mo_boot_col-lg-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_FRONTCHANNEL_LOGOUT');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-7 mo_boot_col-lg-8">
                                        <input class="mo_security_textfield mo-form-control mo_oauth_cursor" required type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_KEY');?>" disabled="disabled" value="" />
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_d-flex mo_oauth_align-items-center">
                                    <div class="mo_boot_col-sm-5 mo_boot_col-lg-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_BACKCHANNEL_LOGOUT');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-7 mo_boot_col-lg-8">
                                        <input class="mo_security_textfield mo-form-control mo_oauth_cursor " required type="text" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_KEY');?>" disabled="disabled" value="" />
                                    </div>
                                </div>
                            </div>
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_mt-4">
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                        <input type="submit" name="send_query" value='<?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_SINGLE_LOGOUT');?>' disabled class="oauth_blue_button mo_oauth_cursor"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fetch Access Token -->
                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section" >
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_fetch_access_token', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title mo_boot_d-flex mo_oauth_align-items-center">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_FETCH_ACCESS_TOKEN'); ?>
                            <span title="<?php echo Text::_('COM_MINIORANGE_OAUTH_FETCH_ACCESS_TOKEN_SETTINGS_KNOW_MORE'); ?>"> <sup> <a href="https://developers.miniorange.com/docs/oauth-joomla/advanced-setting" target="_blank"> <i class="fa-solid fa-circle-info"></i> </a> </sup></span>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <div id="mo_oauth_fetch_access_token" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row">
                                    <div class="mo_boot_col-sm-5">
                                        <div class="mo_boot_form-check form-switch">
                                            <input disabled type="checkbox" class="mo_oauth_checkbox form-check-input mo_oauth_cursor" name=" mo_oauth_custom_checkbox" id=" mo_oauth_check">
                                            <label  class="mo_boot_ml-2">
                                                <?php echo Text::_('COM_MINIORANGE_OAUTH_COOKIE');?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mo_boot_col-sm-7">
                                        <input disabled type="text" class="mo-form-control mo_oauth_cursor" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_COOKIE_NAME');?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                                <div class="mo_boot_row">
                                    <div class="mo_boot_col-sm-5">
                                        <div class="mo_boot_form-check form-switch">
                                            <input disabled type="checkbox" class="mo_oauth_checkbox form-check-input mo_oauth_cursor" name=" mo_oauth_custom_checkbox" id=" mo_oauth_check">
                                            <label class="mo_boot_ml-2">
                                               <?php echo Text::_('COM_MINIORANGE_OAUTH_HTTP');?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mo_boot_col-sm-7">
                                        <input disabled type="text" class="mo-form-control mo_oauth_cursor" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_COOKIE_NAME_1');?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                                <div class="mo_boot_row">
                                    <div class="mo_boot_col-sm-5">
                                        <div class="mo_boot_form-check form-switch">
                                            <input disabled type="checkbox" class="mo_oauth_checkbox form-check-input mo_oauth_cursor" name=" mo_oauth_custom_checkbox" id=" mo_oauth_check">
                                            <label class="mo_boot_ml-2">
                                                <?php echo Text::_('COM_MINIORANGE_OAUTH_LOCAL_STORAGE');?>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="mo_boot_col-sm-7">
                                        <input disabled type="text" class="mo-form-control mo_oauth_cursor" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_VARIABLE_NAME');?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                        <input type="submit" disabled name="send_query" value='<?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_SETTINGS');?>' class="oauth_blue_button mo_oauth_cursor"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Custom SSO Button -->
                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_custom_sso_button', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOM_SSO_BUTTON'); ?> <small><span> (<?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOMIZE_ICON_NOTE'); ?>)</span></small>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <div id="mo_oauth_custom_sso_button" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_my-2">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOMIZE_ICON_CSS');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <textarea disabled type="text" class="mo_oauth_cursor mo_oauth_textarea form-control " rows="6">.oauthloginbutton{background: #7272dc;height:40px;padding:8px;mo_boot_text-align:center;color:#fff;}</textarea>
                                    </div>
                                </div>
                                <div class="mo_boot_row">
                                    <div class="mo_boot_col-sm-4">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOMIZE_ICON_BUTTON');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-8">
                                        <input class="mo-form-control mo_oauth_textarea mo_oauth_cursor" disabled type="text" placeholder ="<?php echo Text::_('COM_MINIORANGE_OAUTH_LOGOUT');?>">
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-4">
                                    <div class="mo_boot_col-sm-12 mo_boot_mt-3 mo_boot_text-center">
                                        <input type="submit" disabled name="send_query" value='<?php echo Text::_('COM_MINIORANGE_OAUTH_SAVE_CUSTOMIZE_ICON_SETTINGS');?>' class="oauth_blue_button mo_oauth_cursor"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SSO Report -->
                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section" >
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_sso_report', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_OAUTH_SSO_REPORT'); ?>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                   <div id="mo_oauth_sso_report" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                       <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-12">
                                        <input disabled type="button" class="mo_boot_btn mo_boot_btn-danger mo_oauth_input mo_boot_m-1" id="cleartext" value="<?php echo Text::_('COM_MINIORANGE_OAUTH_USER_ANALYTICS_AND_TRANSACTION_REPORTS_CLEAR_REPORTS');?>" />
                                        <input disabled type="button" class="mo_boot_btn mo_oauth_all_btn mo_oauth_input mo_boot_m-1" id="refreshtext" value="<?php echo Text::_('COM_MINIORANGE_OAUTH_USER_ANALYTICS_AND_TRANSACTION_REPORTS_REFRESH');?>" />
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-3">
                                    <div class="mo_boot_col-sm-12 mo_boot_table-responsive">
                                        <table class="mo_boot_table mo_boot_table-striped mo_boot_table-hover mo_boot_table-bordered">
                                            <thead>
                                                <tr>
                                                    <th><?php echo Text::_('COM_MINIORANGE_OAUTH_USER_ANALYTICS_AND_TRANSACTION_REPORTS_USERNAME');?></th>
                                                    <th><?php echo Text::_('COM_MINIORANGE_OAUTH_USER_ANALYTICS_AND_TRANSACTION_REPORTS_APPLICATION');?></th>
                                                    <th><?php echo Text::_('COM_MINIORANGE_OAUTH_USER_ANALYTICS_AND_TRANSACTION_REPORTS_STATUS');?></th>
                                                    <th><?php echo Text::_('COM_MINIORANGE_OAUTH_USER_ANALYTICS_AND_TRANSACTION_REPORTS_LOGIN_TIMESTAMP');?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr><td></td><td></td><td></td><td></td></tr>
                                                <tr><td colspan="4" style="text-align: center;font-size: 14px; font-weight: bold;"><?php echo Text::_('COM_MINIORANGE_OAUTH_NO_USER_ACTIVITY'); ?></td></tr>
                                                <tr><td></td><td></td><td></td><td></td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                   </div>
                </div>

            </div>
        </div>
    </div>
    <?php
}

function support()
{
    global $license_tab_link;
    ?>

    <div class="mo_boot_row mo_boot_px-5 mo_boot_mt-4 mo_boot_d-flex mo_ouath_flex-gap-3 ">
        <div onclick = "changeSubMenu('#support',this , '#mo_general_support')" class="mo_boot_col mo_oauth_sub_menu mo_oauth_sub_menu_active">
            <span><?php echo Text::_('COM_MINIORANGE_OAUTH_GENERAL_QUERY');?></span>
        </div>
         <div onclick = "changeSubMenu('#support',this,'#mo_request_demo')" class=" mo_boot_col mo_oauth_sub_menu">
            <span><?php echo Text::_('COM_MINIORANGE_OAUTH_REQUEST_DEMO_TRIAL');?></span>
        </div>
        <div onclick = "changeSubMenu('#support',this,'#mo_screen_share')" class="mo_boot_col mo_oauth_sub_menu">
            <span><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_SCREEN');?></span>
        </div>
    </div>

    <div class="mo_boot_row mo_boot_m-0 mo_boot_p-1" >
        <div class="mo_boot_col-sm-12">

            <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3" id="mo_general_support">
                <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                    <div class="mo_boot_row mo_boot_mt-2">
                        <div class="mo_boot_col-sm-12">
                            <h5 class="element">
                                <?php echo Text::_('COM_MINIORANGE_OAUTH_SUPPORT');?>
                            </h5>
                            <br>
                        </div>
                        <br><br>
                        <div class="mo_boot_col-sm-12 mo_boot_mt-2">
                            <div class="mo_boot_row mo_boot_m-2">
                                <?php
                                    
                                    $current_user = Factory::getUser();
                                    $result = MoOAuthUtility::getCustomerDetails();
                                    $admin_email = empty(trim($result['email']))?$current_user->email:$result['email'];
                                    $user_email= new MoOauthCustomer();
                                    $result=$user_email->getAccountDetails();
                                if($result['contact_admin_email']!=null) {
                                    $admin_email =$result['contact_admin_email'];
                                }
                                    $admin_phone = $result['admin_phone'];
                                    
                                ?>
                                <form name="f" class="mo_boot_col-sm-12" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.contactUs'); ?>">
                                    <div class="mo_boot_col-sm-12">
                                        <p class="mo_oauth_p"><?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_US_DETAILS');?></p>
                                        <br>
                                    </div>
                                    <div class="mo_boot_col-sm-12">
                                        <div class="mo_boot_row mo_boot_mt-2">
                                            <div class="mo_boot_col-sm-3 offset-1">
                                                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_EMAIL');?><span class="mo_oauth_highlight">*</span> : </strong>
                                            </div>
                                            <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                                                <input type="email" class="mo-form-control oauth-table mo_oauth_textbox" name="query_email" value="<?php echo $admin_email?>" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_EMAIL_PLACEHOLDER');?>" required />
                                            </div>
                                        </div>
                                        <div class="mo_boot_row mo_boot_mt-2">
                                            <div class="mo_boot_col-sm-3 offset-1"> <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_MOBILE_NO');?> :</strong></div>
                                            <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                                                <input type="number" class="mo-form-control oauth-table mo_oauth_textbox" name="query_phone" value="<?php echo $admin_phone ?>" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_MOBILE_NO_PLACEHOLDER');?>"/>
                                            </div>
                                        </div>
                                        <div class="mo_boot_row mo_boot_mt-2">
                                            <div class="mo_boot_col-sm-3 offset-1"><strong><?php echo Text::_('COM_MINIORANGE_OAUTH_QUERY');?></strong><span class="mo_oauth_highlight">*</span> : </div>
                                            <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                                                <textarea class="mo_boot_px-2 form-control mo_oauth_textbox mo_boot_col-sm-12" name="query" type="text" rows="4" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_QUERY_PLACEHOLDER');?>" required></textarea>
                                            </div>
                                        </div>
                                        <div class="mo_boot_row mo_boot_mt-2">
                                            <div class="mo_boot_col-sm-3 offset-1"></div>
                                            <div class="mo_boot_col-sm-6">
                                                <div class="mo_boot_form-check mo_boot_form-switch">
                                                    <input id="mo_oauth_query_withconfig"  type="checkbox" class="mo_boot_form-check-input" name="mo_oauth_query_withconfig" value="1" >&nbsp;<?php echo Text::_('COM_MINIORANGE_OAUTH_SEND_CONFIGURATION');?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mo_boot_row mo_boot_my-4 mo_boot_text-center">
                                            <div class="mo_boot_col-sm-12">
                                                <input type="submit" name="send_query"  value="<?php echo Text::_('COM_MINIORANGE_OAUTH_SUBMIT_QUERY');?>" class="oauth_blue_button"/>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <br/>              
                            </div>
                        </div>
                    </div>  
                </div>
            </div>
            <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3" style="display:none" id="mo_request_demo">
                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                    <h5 class="element">
                        <?php echo Text::_('COM_MINIORANGE_OAUTH_DEMO_TITLE');?>
                    </h5>
                    <br>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_my-4">
                    <div class="mo_boot_row mo_boot_m-2">
                        <div class="mo_boot_col-sm-12">
                            <div class="mo_oauth_p">
                                <?php echo Text::_('COM_MINIORANGE_OAUTH_REQUEST_DEMO_NOTE');?>
                            </div><br>
                        </div>
                        <div class="mo_boot_col-sm-12">
                            <form id="demo_request" name="demo_request" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.requestForDemoPlan'); ?>">
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_REQUEST_DEMO_EMAIL');?><span class="mo_oauth_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                                        <input required class="mo-form-control mo_oauth_textbox" onblur="validateEmail(this)" type="email" name="email" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_EMAIL_PLACEHOLDER'); ?>" value="<?php echo $admin_email ?>"/>
                                        <p class="mo_oauth_red_color" style="display:none" id="email_error">Invalid Email</p>
                                    </div>
                                </div>

                                <div class="mo_boot_row mo_boot_mt-2 mo_boot_my-1">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <p> <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_REQUEST_FOR');?><span class="mo_oauth_highlight">*</span> : </strong></p>
                                    </div>
                                    <div class="mo_boot_col-sm-4">
                                        <label><input type="radio" name="demo" class="mx-2" value="Trial of 7 days" CHECKED><?php echo Text::_('COM_MINIORANGE_OAUTH_TRIAL');?></label>
                                    </div>
                                    <div class="mo_boot_col-sm-4">
                                        <label><input type="radio" name="demo" class="mx-2"  value="Demo" ><?php echo Text::_('COM_MINIORANGE_OAUTH_DEMO');?></label>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_REQUEST_TRIAL_DEMO');?><span class="mo_oauth_highlight">*</span> :</strong>
                                    </div>
                                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                                        <select required class="mo-form-control mo-form-control-select mo_oauth_textbox" name="plan" id="rfd_id">
                                            <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_DEMO_SELECT');?></option>
                                            <option value="Joomla OAuth Client Standard Plugin"><?php echo Text::_('COM_MINIORANGE_OAUTH_CLIENT_STANDARD_PLUGIN');?></option>
                                            <option value="Joomla OAuth Client Premium Plugin"><?php echo Text::_('COM_MINIORANGE_OAUTH_CLIENT_PREMIUM_PLUGIN');?></option>
                                            <option value="Joomla OAuth Client Enterprise Plugin"><?php echo Text::_('COM_MINIORANGE_OAUTH_CLIENT_ENTERPRISE_PLUGIN');?></option>
                                            <option value="Not Sure"><?php echo Text::_('COM_MINIORANGE_OAUTH_NOT_SURE');?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_DEMO_DESCRIPTION');?><span class="mo_oauth_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-lg-6 mo_boot_col-sm-8">
                                        <textarea class="mo_boot_px-2 mo_oauth_textbox form-control mo_boot_col-sm-12" required type="text" name="description" rows="5" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_DEMO_DESCRIPTION_PLACEHOLDER');?>" value=""></textarea>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_my-4 mo_boot_text-center">
                                    <div class="mo_boot_col-sm-12">
                                        <input type="submit" name="submit" value="<?php echo Text::_('COM_MINIORANGE_OAUTH_SUBMIT_DEMO_REQUEST');?>" class="oauth_blue_button"/>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                    </div>
                </div>
            </div>
            <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3" style="display:none" id="mo_screen_share">
                <div class="mo_boot_col-sm-12 mo_boot_mt-3">
                    <h5 class="element">
                        <?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_MEETING');?>
                    </h5>
                    <br>
                </div>
                <div class="mo_boot_col-sm-12 mo_boot_my-3">
                    <?php
                        $arrContextOptions=array(
                            "ssl"=>array(
                                "verify_peer"=>false,
                                "verify_peer_name"=>false,
                            ),
                        );  
                        
                        $strJsonFileContents = file_get_contents(Uri::root()."/administrator/components/com_miniorange_oauth/assets/json/timezones.json", false, stream_context_create($arrContextOptions));
                        $timezoneJsonArray = json_decode($strJsonFileContents, true);

                        ?>
                    <form name="f" method="post" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.callContactUs'); ?>">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12 mo_boot_px-5">
                                <p  class="mo_oauth_p"><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_NOTE');?></p>
                            </div>
                            <div class="mo_boot_col-sm-12">
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_EMAIL');?><span class="mo_oauth_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <input class="mo-form-control mo_boot_px-3 mo_oauth_textbox"  type="email" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_EMAIL_PLACEHOLDER'); ?>"  name="mo_oauth_setup_call_email" value="<?php echo $admin_email; ?>"  required>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_ISSUE');?><span class="mo_oauth_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <select required id="issue_dropdown"  class="mo-form-control mo-form-control-select mo_oauth_textbox" name="mo_oauth_setup_call_issue" required>
                                            <option value=""><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_SELECT_ISSUE');?></option>
                                            <option id="sso_setup_issue"><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_SSO_SETUP_ISSUE');?></option>
                                            <option><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_CUSTOM_REQUIREMENT');?></option>
                                            <option id="other_issue"><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_OTHER');?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_DATE');?><span class="mo_oauth_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <input class="mo-form-control mo_callsetup_table_textbox mo_oauth_textbox" name="mo_oauth_setup_call_date" type="datetime-local"  id="calldate" required>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_TIME');?><span class="mo_oauth_highlight">*</span> : </strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <select class="mo_callsetup_table_textbox mo_boot_px-2 mo_oauth_textbox mo_boot_col-sm-12 mo-form-control mo-form-control-select" name="mo_oauth_setup_call_timezone" id="timezone" required>
                                        <?php
                                        foreach($timezoneJsonArray as $data)
                                            {
                                            echo "<option>".$data."</option>";
                                        }
                                        ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_mt-2">
                                    <div class="mo_boot_col-sm-3 offset-1">
                                        <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_DESCRIPTION');?></strong>
                                    </div>
                                    <div class="mo_boot_col-sm-6">
                                        <textarea id="issue_description" rows="4" class="mo_boot_px-2 mo_oauth_textbox form-control mo_boot_col-sm-12" name="mo_oauth_setup_call_desc" placeholder="<?php echo Text::_('COM_MINIORANGE_OAUTH_SETUP_CALL_DESCRIPTION_PLACEHOLDER');?>" ></textarea>
                                    </div>
                                </div>
                                <div class="mo_boot_row mo_boot_my-4 mo_boot_text-center">
                                    <div class="mo_boot_col-sm-12">
                                        <input type="submit" name="send_query"  value="<?php echo Text::_('COM_MINIORANGE_OAUTH_SUBMIT_REQUEST');?>" class="oauth_blue_button">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
}

function mo_oauth_licensing_plan()
{
    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('*');
    $query->from($db->quoteName('#__miniorange_oauth_customer'));
    $query->where($db->quoteName('id')." = 1");
    $db->setQuery($query);
    $useremail = $db->loadAssoc();
    global $license_tab_link;

    if(isset($useremail)) {
        $user_email =$useremail['email'];
    } else {
        $user_email="xyz";
    }
    ?>
    
    <div class="mo_boot_row">
        <div class="mo_boot_col-sm-12">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12 mo_boot_my-4">
                    <div class="mo_oauth_pricing_wrapper">
                        <!-- Free -->
                        <div class="mo_oauth_pricing_table">
                            <div class="mo_oauth_license_plan_name"><?php echo Text::_('COM_MINIORANGE_OAUTH_FREE_PLAN'); ?></div>
                            <div class="mo_oauth_license_price mo_boot_my-2"><?php echo Text::_('COM_MINIORANGE_OAUTH_FREE'); ?><small><small></small></small></div>
                            <div class="mo_oauth_license_btn">
                                <a href=""><?php echo Text::_('COM_MINIORANGE_OAUTH_CURRENT_PLAN'); ?></a>
                            </div>

                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_free_feature_include')">
                                    <div ><span class="mo_oauth_square_check"><i class="fa-solid fa-square-check"></i></span></div>
                                    <div class="mo_oauth_feature_title"><?php echo Text::_('COM_MINIORANGE_OAUTH_INCLUDED_FEATURES'); ?></div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_free_feature_include" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_LIMITED_SSO_AUTHENTICATIONS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_AUTO_FILL_OAUTH_SERVER_CONFIGURATION'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_BASIC_ATTRIBUTE_MAPPING1'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_AUTHORIZATION_CODE_GRANT'); ?> </li>
                                </ul>
                            </div>
                            
                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_free_feature_exclude')">
                                    <div ><span class="mo_oauth_square_xmark"><i class="fa-solid fa-square-xmark"></i></span></div>
                                    <div class="mo_oauth_feature_title"> <?php echo Text::_('COM_MINIORANGE_OAUTH_NOT_INCLUDED_FEATURES'); ?> </div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_free_feature_exclude" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_UNLIMITED_AUTO_CREATION_OF_USERS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_UNLIMITED_SSO_AUTHENTICATIONS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCE_ATTRIBUTE_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOM_REDIRECT_URL'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCE_GROUP_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_FORCE_AUTHENTICATION_PROTECT_COMPLETE_SITE'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_OPENID_CONNECT_SUPPORT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_DOMAIN_SPECIFIC_REGISTRATION_AND_LOGIN'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_TYPES'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_PKCE_SUPPORT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_FETCH_ACCESS_TOKEN'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_REPORTS_ANALYTICS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOMIZED_SSO_BUTTON'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADMIN_SSO'); ?> </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Standard Plan -->
                        <div class="mo_oauth_pricing_table">
                            <div class="mo_oauth_license_plan_name"><?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_STANDARD_PLAN'); ?></div>
                            <div class="mo_oauth_license_price mo_boot_my-2"><?php echo Text::_('COM_MINIORANGE_STANDARD');?> <small><small><?php echo Text::_('COM_MINIORANGE_OAUTH_PER_YEAR');?></small></small> </div>
                            <div class="mo_oauth_license_btn">
                                <a href="https://portal.miniorange.com/initializepayment?requestOrigin=joomla_oauth_client_standard_plan" target="_blank"> <?php echo Text::_('COM_MINIORANGE_UPGRADE_NOW'); ?> </a>
                            </div>

                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_standard_feature_include')">
                                    <div ><span class="mo_oauth_square_check"><i class="fa-solid fa-square-check"></i></span></div>
                                    <div class="mo_oauth_feature_title"><?php echo Text::_('COM_MINIORANGE_OAUTH_INCLUDED_FEATURES'); ?></div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_standard_feature_include" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_UNLIMITED_AUTO_CREATION_OF_USERS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_UNLIMITED_SSO_AUTHENTICATIONS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_BASIC_ATTRIBUTE_MAPPING2'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOM_REDIRECT_URL'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_AUTHORIZATION_CODE_GRANT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_AUTO_FILL_OAUTH_SERVER_CONFIGURATION'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOMIZED_SSO_BUTTON'); ?> </li>
                                </ul>
                            </div>
                            
                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_standard_feature_exclude')">
                                    <div ><span class="mo_oauth_square_xmark"><i class="fa-solid fa-square-xmark"></i></span></div>
                                    <div class="mo_oauth_feature_title"><?php echo Text::_('COM_MINIORANGE_OAUTH_NOT_INCLUDED_FEATURES'); ?></div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_standard_feature_exclude" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCE_ATTRIBUTE_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCE_GROUP_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_FORCE_AUTHENTICATION_PROTECT_COMPLETE_SITE'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_OPENID_CONNECT_SUPPORT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_DOMAIN_SPECIFIC_REGISTRATION_AND_LOGIN'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_TYPES'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_PKCE_SUPPORT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_FETCH_ACCESS_TOKEN'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_REPORTS_ANALYTICS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADMIN_SSO'); ?> </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Premium Plan -->
                        <div class="mo_oauth_pricing_table">
                            <div class="mo_oauth_license_plan_name"> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_PLAN'); ?> </div>
                            <div class="mo_oauth_license_price mo_boot_my-2"> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_PREMIUM_COST'); ?> <small><small><?php echo Text::_('COM_MINIORANGE_OAUTH_PER_YEAR');?></small></small></div>
                            <div class="mo_oauth_license_btn">
                                <a href="https://portal.miniorange.com/initializepayment?requestOrigin=joomla_oauth_client_premium_plan" target="_blank"> <?php echo Text::_('COM_MINIORANGE_UPGRADE_NOW'); ?> </a>
                            </div>

                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_premium_feature_include')">
                                    <div ><span class="mo_oauth_square_check"><i class="fa-solid fa-square-check"></i></span></div>
                                    <div class="mo_oauth_feature_title"><?php echo Text::_('COM_MINIORANGE_OAUTH_INCLUDED_FEATURES'); ?></div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_premium_feature_include" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_UNLIMITED_AUTO_CREATION_OF_USERS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_UNLIMITED_SSO_AUTHENTICATIONS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_AUTHORIZATION_CODE_GRANT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_AUTO_FILL_OAUTH_SERVER_CONFIGURATION'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_BASIC_ATTRIBUTE_MAPPING2'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOM_REDIRECT_URL'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCE_ATTRIBUTE_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCE_GROUP_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_FORCE_AUTHENTICATION_PROTECT_COMPLETE_SITE'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_OPENID_CONNECT_SUPPORT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_DOMAIN_SPECIFIC_REGISTRATION_AND_LOGIN'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOMIZED_SSO_BUTTON'); ?> </li>

                                </ul>
                            </div>
                            
                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_premium_feature_exclude')">
                                    <div ><span class="mo_oauth_square_xmark"><i class="fa-solid fa-square-xmark"></i></span></div>
                                    <div class="mo_oauth_feature_title"><?php echo Text::_('COM_MINIORANGE_OAUTH_NOT_INCLUDED_FEATURES'); ?></div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_premium_feature_exclude" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_GRANT_TYPES'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_PKCE_SUPPORT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_FETCH_ACCESS_TOKEN'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_REPORTS_ANALYTICS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADMIN_SSO'); ?> </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Enterprise Plan -->
                        <div class="mo_oauth_pricing_table">
                            <div class="mo_oauth_license_plan_name"> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_ENTERPRISE_PLAN'); ?> </div>
                            <div class="mo_oauth_license_price mo_boot_my-2"> <?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_ENTERPRISE_PLAN_COST'); ?> <small><small><?php echo Text::_('COM_MINIORANGE_OAUTH_PER_YEAR');?></small></small> </div>
                            <div class="mo_oauth_license_btn">
                                <a href="https://portal.miniorange.com/initializepayment?requestOrigin=joomla_oauth_client_enterprise_plan" target="_blank"> <?php echo Text::_('COM_MINIORANGE_UPGRADE_NOW'); ?> </a>
                            </div>

                            <div class="mo_boot_my-4">
                                <div class="mo_boot_d-flex mo_oauth_justify-content-between" onclick="toggleFeatureList('mo_enterprise_feature_include')">
                                    <div ><span class="mo_oauth_square_check"><i class="fa-solid fa-square-check"></i></span></div>
                                    <div class="mo_oauth_feature_title"><?php echo Text::_('COM_MINIORANGE_OAUTH_INCLUDED_FEATURES'); ?></div>
                                    <div><span class="mo_oauth_feature_arrow"> <i class="fa-solid fa-chevron-down"></i> </span></div>
                                </div>

                                <ul id="mo_enterprise_feature_include" class="mo_feature_list" style="display: none;">
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_UNLIMITED_AUTO_CREATION_OF_USERS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_UNLIMITED_SSO_AUTHENTICATIONS'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_AUTO_FILL_OAUTH_SERVER_CONFIGURATION'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_BASIC_ATTRIBUTE_MAPPING2'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOM_REDIRECT_URL'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCE_ATTRIBUTE_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADVANCE_GROUP_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CONTACT_MAPPING'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_FORCE_AUTHENTICATION_PROTECT_COMPLETE_SITE'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_OPENID_CONNECT_SUPPORT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_DOMAIN_SPECIFIC_REGISTRATION_AND_LOGIN'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ALL_GRANT_TYPES'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_PKCE_SUPPORT'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_FETCH_ACCESS_TOKEN'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_ADMIN_SSO'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_CUSTOMIZED_SSO_BUTTON'); ?> </li>
                                    <li> <?php echo Text::_('COM_MINIORANGE_OAUTH_LOGIN_REPORTS_ANALYTICS'); ?> </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mo_boot_col-sm-12">
                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_how_to_upgrade', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_UPGRADE_PLAN'); ?>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <div id="mo_oauth_how_to_upgrade" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row  ">
                            <div class="  mo_boot_col-sm-6  mo_oauth_works-step ">
                                <div><strong>1</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP1');?></p>
                            </div>
                            <div class=" mo_boot_col-sm-6  mo_oauth_works-step ">
                                <div ><strong>4</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP4');?></p>
                            </div>            
                        </div>
                        <div class=" mo_boot_row  ">
                            <div class="  mo_boot_col-sm-6  mo_oauth_works-step">
                                <div ><strong>2</strong></div>
                                <p> <?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP2');?></p>
                            </div>
                            <div class=" mo_boot_col-sm-6  mo_oauth_works-step">
                                <div ><strong>5</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP5');?> </p>
                            </div>         
                        </div>
                        <div class=" mo_boot_row  ">
                            <div class=" mo_boot_col-sm-6  mo_oauth_works-step">
                                <div ><strong>3</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP3');?></p>
                            </div>
                            <div class="  mo_boot_col-sm-6  mo_oauth_works-step">
                                <div ><strong>6</strong></div>
                                <p><?php echo Text::_('COM_MINIORANGE_UPGRADE_STEP6');?></p>
                            </div>       
                        </div> 
                    </div>
                </div>


                <div class="mo_boot_col-sm-12 mo_boot_mt-4 mo_oauth_mini_section">
                    <div class="mo_oauth_tab_header mo_boot_d-flex mo_oauth_justify-content-between mo_oauth_align-items-center mo_boot_p-3"
                         onclick="toggleCollapse('mo_oauth_return_policy', this.querySelector('.mo_toggle_icon'))">
                        <div class="mo_oauth_tab_title">
                            <?php echo Text::_('COM_MINIORANGE_RETURN_POLICY'); ?>
                        </div>
                        <div class="mo_toggle_icon"> + </div>
                    </div>

                    <div id="mo_oauth_return_policy" class="mo_boot_col-sm-12 mo_boot_mt-3" style="display: none;">
                        <div class="mo_boot_row">
                            <div class="mo_boot_col-sm-12 mo_boot_pb-3">
                                <p style="font-size:16px;"> <?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_DETAILS');?></p><br>
                                <div class="mo_oauth_return_policy_work">
                                    <h4><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_HOW_IT_WORKS');?> : </h4>
                                    <ol>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_HOW_IT_WORKS_1'); ?></li>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_HOW_IT_WORKS_2'); ?></li>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_HOW_IT_WORKS_3'); ?></li>
                                    </ol>
                                </div>
                                <div class="mo_oauth_return_policy_work">
                                    <h4><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NOT_ISSUED');?> : </h4>
                                    <ul>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NOT_ISSUED_1'); ?></li>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NOT_ISSUED_2'); ?></li>
                                        <li><?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NOT_ISSUED_3'); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <br>
                <div class="mo_boot_col-sm-12 mo_boot_px-0">
                    <p> 📧 <?php echo Text::_('COM_MINIORANGE_RETURN_POLICY_NEED_HELP'); ?></p>
                </div>
            </div>
        </div>
    </div>

        
    <?php
}

function moImportAndExport($mo_oauth_app)
{
    ?>
    <div class="mo_boot_row mo_boot_px-4" id="import_export_form">
        <div class="mo_boot_col-sm-12 mo_oauth_hightlight_white_bg mo_boot_p-3">
            <div class="mo_boot_my-2 mo_oauth_export_import_config" >
                <strong><?php echo Text::_('COM_MINIORANGE_EXPORT_CONFIGURATION');?></strong>
            </div>

            <div class="mo_boot_mb-2" ><?php echo Text::_('COM_MINIORANGE_EXPORT_CONFIGURATION_TEXT');?></div>

            <div class="mo_boot_mt-4 mo_boot_mb-3">
                <a href='index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.exportConfiguration' class="oauth_blue_button mo_boot_p-2">
                    <span><i class="fa-duotone fa-solid fa-download"></i></span>
                    <?php echo Text::_('COM_MINIORANGE_EXPORT_CONFIGURATION');?>
                </a>
            </div>
        </div>

        <div class="mo_boot_col-sm-12 mo_oauth_hightlight_white_bg mo_boot_mt-3 mo_boot_p-3">
             <div class="mo_boot_my-2 mo_oauth_export_import_config" >
                <strong><?php echo Text::_('COM_MINIORANGE_OAUTH_IMPORT_CONFIGURATION');?></strong><span title="<?php echo Text::_('COM_MINIORANGE_AVAILABLE_IN_PAID_PLANS_ONLY'); ?>" ><sup><img class="crown_img_small" src="<?php echo Uri::base();?>/components/com_miniorange_oauth/assets/images/crown.webp"></sup></span>
            </div>
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-4">
                    <strong><?php echo Text::_('COM_MINIORANGE_FEATURE_COMPARISION_UPLOAD_CONFIGURATION');?></strong> 
                </div>
                <div class="mo_boot_col-sm-4">
                    <input type="file" class="mo-form-control-file d-inline" name="configuration_file" disabled="disabled">
                </div>
            </div>
            
           <div class="mo_boot_my-2">
                <button type="button" class="oauth_blue_button" disabled>
                    <span><i class="fa-solid fa-upload"></i></span>
                    <?php echo Text::_('COM_MINIORANGE_OAUTH_IMPORT_CONFIGURATION'); ?>
                </button>
            </div>
        </div>
    </div>
    <?php
}

function moOAuthLoggerReport()
{
    $all_log_record = MoOAuthLogger::getAllLogs();
    $appData = getAppDetails();
    $loggers_enabled = isset($appData['loggers_enable']) ? $appData['loggers_enable'] : 0;
    $app   = Factory::getApplication();
    $input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
    $limit = 20;
    $page  = (int) $input->cookie->get('log_page', 1, 'INT');
    $page = max(1, $page);

    $totalLogs = count($all_log_record);
    $totalPages = max(1, ceil($totalLogs / $limit));

    // Ensure page does not exceed total pages
    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $offset = ($page - 1) * $limit;
    $logsToShow = array_slice($all_log_record, $offset, $limit);
    ?>

    <div class="mo_boot_row mo_boot_m-1 mo_boot_my-3" id="logger_report">
        <div class="mo_boot_col-sm-12">
            <div class="mo_boot_row">
                <div class="mo_boot_col-sm-12">
                    <div class="mo_boot_row logger_settings mo_boot_mb-3 mo_boot_d-flex mo_boot_align-items-center">
                        <!-- Enable Logs -->
                        <div class="mo_boot_col-sm-3 mo_boot_d-flex mo_boot_align-items-center">
                            <div class="mo_boot_my-auto">
                                <label for="mo_enable_logs" class="mo_boot_mx-2" style="font-weight: bold;"><?php echo Text::_('COM_MINIORANGE_OAUTH_ENABLE_LOGS'); ?></label>
                            </div>
                            <div class="mo_boot_form-check form-switch mo_boot_my-auto">
                                <form method="POST" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.moEnableLogs') ?>">
                                    <input type="hidden" name="mo_enable_logs" value="0">
                                    <input type="checkbox" class="mo_oauth_checkbox form-check-input" 
                                        name="mo_enable_logs" id="mo_enable_logs" value="1" 
                                        <?php echo ($loggers_enabled == 1 ? 'checked' : ''); ?> 
                                        onchange="this.form.submit();">
                                    <?php echo HTMLHelper::_('form.token'); ?>
                                </form>
                            </div>
                        </div>

                        <!-- Clear and Download Logs -->
                        <div class="mo_boot_col-auto">
                            <div class="mo_boot_row">
                                <div>
                                    <button type="submit" class="mo_oauth_logger_btn" 
                                        onclick="
                                            let siteBase = window.location.origin + window.location.pathname.split('administrator')[0];
                                            let targetUrl = siteBase + 'administrator/index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=loggerreport';
                                            window.location.href = targetUrl;
                                        "
                                        id="refreshLogsBtn">
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_REFRESH_LOGS'); ?>
                                    </button>
                                </div>
                                <form method="POST" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.moDownloadLogs'); ?>">
                                    <?php echo HTMLHelper::_('form.token'); ?>
                                    <button type="submit" class=" mo_oauth_logger_btn mo_boot_mx-2"
                                        id="downloadLogsBtn">
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_DOWNLOAD_LOGS_BUTTON'); ?>
                                    </button>
                                </form>
                                <form method="POST" action="<?php echo Route::_('index.php?option=com_miniorange_oauth&view=accountsetup&task=accountsetup.moClearLogs'); ?>">
                                    <?php echo HTMLHelper::_('form.token'); ?>
                                    <button type="submit" class="mo_boot_btn mo_boot_btn-danger" 
                                        onclick="return confirm(<?php echo Text::_('COM_MINIORANGE_OAUTH_LOGS_CLEAR_WARNING'); ?>);" 
                                        id="clearLogsBtn">
                                        <?php echo Text::_('COM_MINIORANGE_OAUTH_CLEAR_LOGS_BUTTON'); ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="mo_boot_row mo_boot_mt-3">
                <div class="mo_boot_col-sm-12 mo_boot_table-responsive">
                    <table class="logger_report_table mo_boot_table mo_boot_table-hover mo_boot_table-bordered">
                        <thead class="mo_boot_text-center">
                            <tr>
                                <th class="mo_boot_col-sm-2"><?php echo Text::_('COM_MINIORANGE_OAUTH_LOGS_TIMESTAMP'); ?></th>
                                <th class="mo_boot_col-sm-1"><?php echo Text::_('COM_MINIORANGE_OAUTH_LOGS_PRIORITY'); ?></th>
                                <th class="mo_boot_col-sm-4"><?php echo Text::_('COM_MINIORANGE_OAUTH_LOGS_CODE'); ?></th>
                                <th class="mo_boot_col-sm-5"><?php echo Text::_('COM_MINIORANGE_OAUTH_LOGS_PATH'); ?></th>
                            </tr>
                        </thead>
                        <tbody class="mo_boot_text-center">
                            <?php 
                            if (empty($logsToShow)) {
                                ?> <tr><td colspan="4" style="font-size: 14px; font-weight: bold;"> <?php echo Text::_('COM_MINIORANGE_OAUTH_LOGS_NO_AVAILABLE'); ?></td></tr>
                                <?php
                            } else {
                                foreach ($logsToShow as $logs_data) { 
                                    $logs_data = (array) $logs_data;
                                    $messData = json_decode($logs_data['message'], true);
                                    ?>
                                    <tr>
                                        <td class="timestamp-cell"><?php echo $logs_data['timestamp']; ?></td>
                                        <td class="priority-cell"><?php echo $logs_data['log_level'] ?></td>
                                        <td class="code-cell"><?php echo ($messData['code'] . ' : ' . $messData['issue']); ?></td>
                                        <td class="path-cell"><?php echo ($logs_data['file'] . ' ' . Text::_('COM_MINIORANGE_OAUTH_LOGS_IN_FUNCTION') . ' ' . $logs_data['function_call'] . ' ' .  Text::_('COM_MINIORANGE_OAUTH_LOGS_AT_LINE') . ' ' . $logs_data['line_number']); ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1) : ?>
                <div class="pagination" style="text-align:center; margin-top:10px;">
                    <!-- Prev Button -->
                    <button 
                        type="button" 
                        class="page-btn" 
                        data-page="<?php echo max(1, $page - 1); ?>" 
                        <?php echo ($page <= 1) ? 'disabled' : ''; ?>>
                        <?php echo Text::_('COM_MINIORANGE_OAUTH_LOGS_PREV_BUTTON'); ?>
                    </button>
                    
                    <!-- Page Numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <button 
                            type="button" 
                            class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>" 
                            data-page="<?php echo $i; ?>">
                            <?php echo $i; ?>
                        </button>
                    <?php endfor; ?>

                    <!-- Next Button -->
                    <button 
                        type="button" 
                        class="page-btn" 
                        data-page="<?php echo min($totalPages, $page + 1); ?>" 
                        <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>>
                        <?php echo Text::_('COM_MINIORANGE_OAUTH_LOGS_NEXT_BUTTON'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

?>
