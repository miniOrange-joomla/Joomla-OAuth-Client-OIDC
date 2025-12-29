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

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

require_once JPATH_ADMINISTRATOR . '/components/com_miniorange_oauth/helpers/mo_oauth_utility.php';

class MoOauthCustomer
{
    
    public $email;
    public $phone;
    public $customerKey;
    public $transactionId;
    
    /*
    ** Initial values are hardcoded to support the miniOrange framework to generate OTP for email.
    ** We need the default value for creating the OTP the first time,
    ** As we don't have the Default keys available before registering the user to our server.
    ** This default values are only required for sending an One Time Passcode at the user provided email address.
    */
    
    //auth
    private $defaultCustomerKey = "16555";
    private $defaultApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
    
    
    function request_for_demo($email, $plan, $description,$demo_trail=null,$callDate=null,$timeZone=null)
    { 
        $customerKey = "16555";
        $fromEmail             = $email;
        $currentUserEmail     = Factory::getUser();
        $adminEmail         = $currentUserEmail->email;
        $jVersion             = new Version();
        $phpVersion         = phpversion();
        $moSystemOS         = MoOauthUtility::get_operating_system();
        $jCmsVersion         = $jVersion->getShortVersion();
        $moPluginVersion     = MoOauthUtility::GetPluginVersion();

        $content='<div>Hello, <br><br><strong>Company</strong> :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br><strong>Admin Email : </strong><a href="mailto:'.$adminEmail.'" target="_blank">'.$adminEmail.'</a><br><br><b>Email :</b><a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br><b>Plugin Name: </b>'.$plan. '<br><br><b>Description: </b>' .$description;

        if(is_null($callDate) && is_null($timeZone)) {
            $content='<div>Hello, <br><br>Company :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br><strong>Admin Email : </strong><a href="mailto:'.$adminEmail.'" target="_blank">'.$adminEmail.'</a><br><br><b>Email :</b><a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br><b>Plugin Name: </b>'.$plan. '<br><br><b>Description: </b>' .$description;
            $subject  = "miniOrange Joomla Oauth Client Request for ".$demo_trail. " ";
        }
        else
        {
            $subject  = "miniOrange Joomla Oauth Client Free - Screen Share/Call Request";
            $content='<div>Hello, <br><br><strong>Company</strong> :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br><strong>Admin Email : </strong><a href="mailto:'.$adminEmail.'" target="_blank">'.$adminEmail.'</a><br><br><b>Email :</b><a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a><br><br><b>Time Zone:</b> '.$timeZone. '<br><br><b>Date to set up call: </b>' .$callDate. '<br><br><b>Issue :</b> ' .$plan. '<br><br><b>Description: </b>'.$description;
        }

        $content .= '<br><br><b>System Information: </b>Joomla:'. $jCmsVersion .' | PHP: '. $phpVersion .' | Plugin: '. $moPluginVersion .' | OS: '.$moSystemOS.'<br></div>';

        
        $fields = array(
            'customerKey'    => $customerKey,
            'sendEmail'     => true,
            'email'         => array(
                'customerKey'   => $customerKey,
                'fromEmail'     => $fromEmail,                
                'fromName'      => 'miniOrange',
                'toEmail'       => 'joomlasupport@xecurify.com',
                'toName'        => 'joomlasupport@xecurify.com',
                'subject'       => $subject,
                'content'       => $content
            ),
        );
        
        return self::send_email($fields);
    }
    
    public static function submit_feedback_form($email,$phone,$query)
    {
        $customerKey = "16555";
        $fromEmail             = $email;
        $currentUserEmail     = Factory::getUser();
        $adminEmail         = $currentUserEmail->email;
        $jVersion             = new Version();
        $phpVersion         = phpversion();
        $jCmsVersion         = $jVersion->getShortVersion();
        $osName = php_uname('s');      
        $osRelease = php_uname('r');
        $osArch = php_uname('m'); 
        if(class_exists("MoOAuthUtility")) {
            $moPluginVersion     = MoOauthUtility::GetPluginVersion();
        } else {
            $moPluginVersion = "NA";
        }
        $server_name = self::getServerType();
        $subject = "Feedback for miniOrange Joomla Oauth Client Free";

        $query1 =" miniOrange Joomla [Free] Oauth Client";
        $content='<div> Hello, <br><br>
                    <strong>Company</strong> :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
                    <strong>Phone Number</strong> :'.$phone.'<br><br>
                    <strong>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a></strong><br><br>
                    <strong>Admin Email : </strong><a href="mailto:'.$adminEmail.'" target="_blank">'.$adminEmail.'</a><br><br>
                    <b>Plugin Deactivated: '.$query1. '</b><br><br>
                    <b>Reason: ' .$query. '</b><br><br>
                    <b>System Information: Joomla: '.$jCmsVersion.' | PHP: '.$phpVersion.' | Plugin: '.$moPluginVersion.' | OS: '.$osName.' '.$osRelease.' '.$osArch.'</b><br>
                    <b>Server Name:'. $server_name .'</b></div>';

        $fields = array(
            'customerKey'   => $customerKey,
            'sendEmail'     => true,
            'email'         => array(
                'customerKey'   => $customerKey,
                'fromEmail'     => $fromEmail,                
                'fromName'      => 'miniOrange',
                'toEmail'       => 'joomlasupport@xecurify.com',
                'toName'        => 'joomlasupport@xecurify.com',
                'subject'       => $subject,
                'content'       => $content
            ),
        );

        return self::send_email($fields);
    }

    public static function getAccountDetails()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__miniorange_oauth_customer'));
        $query->where($db->quoteName('id')." = 1");

        $db->setQuery($query);
        $result=$db->loadAssoc();
        return $result;
    }
    
    public static function getConfigurationDetails()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from($db->quoteName('#__miniorange_oauth_config'));
        $query->where($db->quoteName('id')." = 1");

        $db->setQuery($query);
        $result=$db->loadAssoc();
        return $result;
    }
    
    //Efficency check of the plugin for better improvement
    public static function plugin_efficiency_check($email,$appname,$base_url, $c_time, $dno_ssos, $tno_ssos, $previous_update, $present_update,$reason='NA', $scope='NULL', $authorisationURL='NULL', $accesstokenurl='NULL', $resourceownerdetailsurl='NULL', $in_header_or_body='NULL', $test_configuration="")
    {
        $customer_details = self::getAccountDetails();
        $config_details = self::getConfigurationDetails();

        $customerKey          = "16555";
        $fromEmail            = $email;
        $currentUserEmail     = Factory::getUser();
        $adminEmail           = $currentUserEmail->email;
        $subject              = "miniOrange Joomla OAuth Client [Free] for Efficiency";
        $sso_test = base64_decode($customer_details['sso_test']);
        $sso_var = base64_decode($customer_details['sso_var']);
        $base_url = Uri::root();
        $appname = empty($appname) && isset($config_details['appname']) ? $config_details['appname'] : $appname;
        $scope = ($scope == 'NULL') && isset($config_details['app_scope']) ? $config_details['app_scope'] : $scope;
        $authorisationURL = ($authorisationURL == 'NULL') && isset($config_details['authorize_endpoint']) ? $config_details['authorize_endpoint'] : $authorisationURL;
        $accesstokenurl  = ($accesstokenurl == 'NULL') && isset($config_details['access_token_endpoint']) ? $config_details['access_token_endpoint'] : $accesstokenurl;
        $resourceownerdetailsurl  = ($resourceownerdetailsurl == 'NULL') && isset($config_details['user_info_endpoint']) ? $config_details['user_info_endpoint'] : $resourceownerdetailsurl;
        $server_name = self::getServerType();

        $query1 ="miniOrange Joomla [Free] OAuth Client to improve efficiency ";
        $content='<div >Hello, <br><br>
            Company :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
            Server :'.$appname.'<br><br>
            <strong>Admin Email : </strong><a href="mailto:'.$adminEmail.'" target="_blank">'.$adminEmail.'</a><br><br>
            <b>Email :<a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a></b><br><br>
            <b>Plugin Efficency Check: '.$query1. '</b><br><br>
            <b>Website: ' .$base_url. '</b><br>
            Creation Date:'.$c_time.'<br> 
            Daily SSO:'.$dno_ssos.'<br> 
            Total SSO:'.$tno_ssos.'<br>
            Login Count:'. $sso_test .'<br>
            Login Limit:'. $sso_var .'<br>
            Previous Update:'.$previous_update.'<br> 
            Current Update:'.$present_update.'<br> 
            Scope: '.$scope.'<br>  
            Authorize Endpoint:'.$authorisationURL.'<br> 
            Token Endpoint: '.$accesstokenurl.'<br> 
            Userinfo Endpoint: '.$resourceownerdetailsurl.' <br> 
            Header/Body: '.$in_header_or_body.'<br> 
            Test Configuration: '. $test_configuration .' <br> 
            Message:'.$reason.'<br>
            Server Type:' . $server_name .'</div>';

        $fields = array(
            'customerKey'    => $customerKey,
            'sendEmail'     => true,
            'email'         => array(
                'customerKey'   => $customerKey,
                'fromEmail'     => $fromEmail,                
                'fromName'      => 'miniOrange',
                'bccEmail'      => 'nikhil.bhot@xecurify.com',
                'toEmail'       => 'nutan.barad@xecurify.com',
                'toName'        => 'nutan.barad@xecurify.com',
                'subject'       => $subject,
                'content'       => $content
            ),
        );

        self::send_email($fields);
    }

    public static function send_installation_email()
    {
        $customerKey          = "16555";
        $currentUserEmail     = Factory::getUser();
        $adminEmail           = $currentUserEmail->email;
        $jVersion             = new Version();
        $phpVersion         = phpversion();
        $jCmsVersion         = $jVersion->getShortVersion();
        $osName = php_uname('s');      
        $osRelease = php_uname('r');
        $osArch = php_uname('m'); 
        if(class_exists("MoOAuthUtility")) {
            $moPluginVersion     = MoOauthUtility::GetPluginVersion();
        } else {
            $moPluginVersion = "NA";
        }
        $server_name = self::getServerType();

        $subject  = "Installation of Joomla OAuth Client [Free]";

        $content='<div >Hello, <br><br>
            Company :<a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
            <strong>Admin Email : </strong><a href="mailto:'. $adminEmail .'" target="_blank">'. $adminEmail .'</a><br><br>
            <b>System Information: </b>Joomla:'. $jCmsVersion .' | PHP: '. $phpVersion .' | Plugin: '. $moPluginVersion .' | OS: '.$osName.' '.$osRelease.' '.$osArch.'<br>
            <b>Server Name: </b>'. $server_name .'</div>';


        $fields = array(
            'customerKey'   => $customerKey,
            'sendEmail'     => true,
            'email'         => array(
                'customerKey'   => $customerKey,
                'fromEmail'     => $adminEmail,                
                'fromName'      => 'miniOrange',
                'toEmail'       => 'nutan.barad@xecurify.com',
                'bccEmail'       => 'nikhil.bhot@xecurify.com',
                'toName'        => 'nutan.barad@xecurify.com',
                'subject'       => $subject,
                'content'       => $content
            ),
        );

        self::send_email($fields);
    }

    function submit_contact_us( $q_email, $q_phone, $query, $attributes )
    {
        if(!MoOauthUtility::is_curl_installed()) {
            return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }

        $customerKey = "16555";
        $fromEmail    = $q_email;
        $currentUserEmail     = Factory::getUser();
        $adminEmail         = $currentUserEmail->email;
        $jVersion             = new Version();
        $phpVersion         = phpversion();
        $moSystemOS         = MoOauthUtility::get_operating_system();
        $jCmsVersion         = $jVersion->getShortVersion();
        $moPluginVersion     = MoOauthUtility::GetPluginVersion();
        $server_name = self::getServerType();
        $query = '[miniOrange Joomla Oauth Client Free | '.$phpVersion. ' | '.$jCmsVersion.' | '.$moPluginVersion.' | ' . $moSystemOS.'] ' . $query;
        $query = $query.'<br><strong>Configuration: </strong><br> <strong>App Name:</strong>  '.$attributes['appname'].'<br> <strong>Custom App: </strong> '. $attributes['custom_app'].' <br> <strong>App Scope: </strong>'.$attributes['app_scope'].'<br> <strong>Authorize Endpoint: </strong>'.$attributes['authorize_endpoint'];
        $subject = "Query for miniOrange Joomla Oauth Client Free  - ".$fromEmail;
        $content='<div >Hello, <br><br>
            <strong>Company: </strong> <a href="'.$_SERVER['SERVER_NAME'].'" target="_blank" >'.$_SERVER['SERVER_NAME'].'</a><br><br>
            <strong>Phone Number: </strong>'.$q_phone.'<br><br>
            <strong>Admin Email: </strong><a href="mailto:'.$adminEmail.'" target="_blank">'.$adminEmail.'</a><br><br>
            <b>Email: <a href="mailto:'.$fromEmail.'" target="_blank">'.$fromEmail.'</a></b><br>
            <b>Server Name: </b>'  . $server_name. '<br>
            <b>Query: </b>'.$query. '</b></div>';
    
        $fields = array(
            'customerKey'    => $customerKey,
            'sendEmail'     => true,
            'email'         => array(
                'customerKey'     => $customerKey,
                'fromEmail'     => $fromEmail,                
                'fromName'      => 'miniOrange',
                'toEmail'       => 'joomlasupport@xecurify.com',
                'toName'        => 'joomlasupport@xecurify.com',
                'subject'         => $subject,
                'content'         => $content
            ),
        );
        
        return self::send_email($fields);
    }
    private static function send_email($fields)
    {
        $field_string = json_encode($fields);

        if(!MoOauthUtility::is_curl_installed()) {
            return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
        
        $hostname = MoOauthUtility::getHostname();
        $url = $hostname . '/moas/api/notify/send';
        $ch = curl_init($url);

        $customerKey = "16555";
        $apiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
        $currentTimeInMillis= round(microtime(true) * 1000);
        $stringToHash         = $customerKey .  number_format($currentTimeInMillis, 0, '', '') . $apiKey;
        $hashValue             = hash("sha512", $stringToHash);
        $customerKeyHeader     = "Customer-Key: " . $customerKey;
        $timestampHeader     = "Timestamp: " .  number_format($currentTimeInMillis, 0, '', '');
        $authorizationHeader= "Authorization: " . $hashValue;

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  

        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
            $timestampHeader, $authorizationHeader)
        );
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
        $proxy_server =  self::getConfigurationDetails();
        $proxy_host_name = isset($proxy_server['proxy_host_name']) ? $proxy_server['proxy_host_name'] : '';
        $port_number = isset($proxy_server['port_number']) ? $proxy_server['port_number'] : '';
        $username = isset($proxy_server['username']) ? $proxy_server['username'] : '';
        $password = isset($proxy_server['password']) ? base64_decode($proxy_server['password']) : '';
        if (!empty($proxy_host_name)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxy_host_name);
            curl_setopt($ch, CURLOPT_PROXYPORT, $port_number);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $username . ':' . $password);
        }
        $content = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Request Error:' . curl_error($ch);
            exit();
        }
        curl_close($ch);

        return ($content);
    }

    public static function getServerType()
    {
        $server = $_SERVER['SERVER_SOFTWARE'] ?? '';

        if (stripos($server, 'Apache') !== false) {
            return 'Apache';
        }

        if (stripos($server, 'nginx') !== false) {
            return 'Nginx';
        }

        if (stripos($server, 'LiteSpeed') !== false) {
            return 'LiteSpeed';
        }

        if (stripos($server, 'IIS') !== false) {
            return 'IIS';
        }

        return 'Unknown';
    }


    function getAppJason()
    {
        return '{	
        "azure": {
            "label":"Azure AD", "type":"oauth", "image":"azure.png", "scope": "openid email profile", "authorize": "https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize", "token": "https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token", "userinfo":"https://graph.microsoft.com/beta/me", "guide":"https://plugins.miniorange.com/azure-ad-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-windowslive"
        },
        "cognito": {
            "label":"AWS Cognito", "type":"oauth", "image":"cognito.png", "scope": "openid", "authorize": "https://{domain}/oauth2/authorize", "token": "https://{domain}/oauth2/token", "userinfo": "https://{domain}/oauth2/userInfo", "guide":"https://plugins.miniorange.com/aws-cognito-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-amazon"
        },
        "whmcs": {
            "label":"WHMCS", "type":"oauth", "image":"whmcs.png", "scope": "openid profile email", "authorize": "https://{domain}/oauth/authorize.php", "token": "https://{domain}/oauth/token.php", "userinfo": "https://{domain}/oauth/userinfo.php?access_token=", "guide":"https://plugins.miniorange.com/whmcs-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "slack": {
            "label":"Slack", "type":"oauth", "image":"slack.png", "scope": "users.profile:read", "authorize": "https://slack.com/oauth/authorize", "token": "https://slack.com/api/oauth.access", "userinfo": "https://slack.com/api/users.profile.get", "guide":"https://plugins.miniorange.com/slack-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-slack"
        },
        "discord": {
            "label":"Discord", "type":"oauth", "image":"discord.png", "scope": "identify email", "authorize": "https://discordapp.com/api/oauth2/authorize", "token": "https://discordapp.com/api/oauth2/token", "userinfo": "https://discordapp.com/api/users/@me", "guide":"https://plugins.miniorange.com/discord-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "invisioncommunity": {
            "label":"Invision Community", "type":"oauth", "image":"invis.png", "scope": "email", "authorize": "{domain}/oauth/authorize/", "token": "https://{domain}/oauth/token/", "userinfo": "https://{domain}/oauth/me", "guide":"https://plugins.miniorange.com/invision-community-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "bitrix24": {
            "label":"Bitrix24", "type":"oauth", "image":"bitrix24.png", "scope": "user", "authorize": "https://{accountid}.bitrix24.com/oauth/authorize", "token": "https://{accountid}.bitrix24.com/oauth/token", "userinfo": "https://{accountid}.bitrix24.com/rest/user.current.json?auth=", "guide":"https://plugins.miniorange.com/bitrix24-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-clock-o"
        },
        "wso2": {
            "label":"WSO2", "type":"oauth", "image":"wso2.png", "scope": "openid", "authorize": "https://{domain}/wso2/oauth2/authorize", "token": "https://{domain}/wso2/oauth2/token", "userinfo": "https://{domain}/wso2/oauth2/userinfo", "guide":"https://plugins.miniorange.com/wso2-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "gapps": {
            "label":"Google", "type":"oauth", "image":"google.png", "scope": "email", "authorize": "https://accounts.google.com/o/oauth2/auth", "token": "https://www.googleapis.com/oauth2/v4/token", "userinfo": "https://www.googleapis.com/oauth2/v1/userinfo", "guide":"https://plugins.miniorange.com/google-apps-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-google-plus"
        },
        "fbapps": {
            "label":"Facebook", "type":"oauth", "image":"facebook.png", "scope": "public_profile email", "authorize": "https://www.facebook.com/dialog/oauth", "token": "https://graph.facebook.com/v2.8/oauth/access_token", "userinfo": "https://graph.facebook.com/me/?fields=id,name,email,age_range,first_name,gender,last_name,link", "guide":"https://plugins.miniorange.com/facebook-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-facebook"
        },
        "gluu": {
            "label":"Gluu Server", "type":"oauth", "image":"gluu.png", "scope": "openid", "authorize": "http://{domain}/oxauth/restv1/authorize", "token": "http://{domain}/oxauth/restv1/token", "userinfo": "http:///{domain}/oxauth/restv1/userinfo", "guide":"https://plugins.miniorange.com/gluu-server-single-sign-on-sso-joomla-login-using-gluu", "logo_class":"fa fa-lock"
        },
        "linkedin": {
            "label":"LinkedIn", "type":"oauth", "image":"linkedin.png", "scope": "openid email profile", "authorize": "https://www.linkedin.com/oauth/v2/authorization", "token": "https://www.linkedin.com/oauth/v2/accessToken", "userinfo": "https://api.linkedin.com/v2/me", "guide":"https://plugins.miniorange.com/linkedin-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-linkedin-square"
        },
        "strava": {
            "label":"Strava", "type":"oauth", "image":"strava.png", "scope": "public", "authorize": "https://www.strava.com/oauth/authorize", "token": "https://www.strava.com/oauth/token", "userinfo": "https://www.strava.com/api/v3/athlete", "guide":"https://plugins.miniorange.com/strava-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "fitbit": {
            "label":"FitBit", "type":"oauth", "image":"fitbit.png", "scope": "profile", "authorize": "https://www.fitbit.com/oauth2/authorize", "token": "https://api.fitbit.com/oauth2/token", "userinfo": "https://www.fitbit.com/1/user", "guide":"https://plugins.miniorange.com/fitbit-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "box": {
            "label":"Box", "type":"oauth", "image":"box.png", "scope": "root_readwrite", "authorize": "https://account.box.com/api/oauth2/authorize", "token": "https://api.box.com/oauth2/token", "userinfo": "https://api.box.com/2.0/users/me", "guide":"https://plugins.miniorange.com/box-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "github": {
            "label":"GitHub", "type":"oauth", "image":"github.png", "scope": "user repo", "authorize": "https://github.com/login/oauth/authorize", "token": "https://github.com/login/oauth/access_token", "userinfo": "https://api.github.com/user", "guide":"https://plugins.miniorange.com/github-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-github"
        },
        "gitlab": {
            "label":"GitLab", "type":"oauth", "image":"gitlab.png", "scope": "read_user", "authorize": "https://gitlab.com/oauth/authorize", "token": "http://gitlab.com/oauth/token", "userinfo": "https://gitlab.com/api/v4/user", "guide":"https://plugins.miniorange.com/gitlab-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-gitlab"
        },
        "clever": {
            "label":"Clever", "type":"oauth", "image":"clever.png", "scope": "read:students read:teachers read:user_id", "authorize": "https://clever.com/oauth/authorize", "token": "https://clever.com/oauth/tokens", "userinfo": "https://api.clever.com/v1.1/me", "guide":"https://plugins.miniorange.com/clever-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "salesforce": {
            "label":"Salesforce", "type":"oauth", "image":"salesforce.png", "scope": "email", "authorize": "https://login.salesforce.com/services/oauth2/authorize", "token": "https://login.salesforce.com/services/oauth2/token", "userinfo": "https://login.salesforce.com/services/oauth2/userinfo", "guide":"https://plugins.miniorange.com/salesforce-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "reddit": {
            "label":"Reddit", "type":"oauth", "image":"reddit.png", "scope": "identity", "authorize": "https://www.reddit.com/api/v1/authorize", "token": "https://www.reddit.com/api/v1/access_token", "userinfo": "https://www.reddit.com/api/v1/me", "guide":"https://plugins.miniorange.com/reddit-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-reddit"
        },
        "spotify": {
            "label":"Spotify", "type":"oauth", "image":"spotify.png", "scope": "user-read-private user-read-email", "authorize": "https://accounts.spotify.com/authorize", "token": "https://accounts.spotify.com/api/token", "userinfo": "https://api.spotify.com/v1/me", "guide":"https://plugins.miniorange.com/spotify-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-spotify"
        },
        "eveonlinenew": {
            "label":"Eve Online", "type":"oauth", "image":"eveonline.png", "scope": "publicData", "authorize": "https://login.eveonline.com/oauth/authorize", "token": "https://login.eveonline.com/oauth/token", "userinfo": "https://esi.evetech.net/verify", "guide":"https://plugins.miniorange.com/oauth-openid-connect-single-sign-on-sso-into-joomla-using-eve-online", "logo_class":"fa fa-lock"
        },
        "vkontakte": {
            "label":"VKontakte", "type":"oauth", "image":"vk.png", "scope": "openid", "authorize": "https://oauth.vk.com/authorize", "token": "https://oauth.vk.com/access_token", "userinfo": "https://api.vk.com/method/users.get?fields=id,name,email,age_range,first_name,gender,last_name,link&access_token=", "guide":"https://plugins.miniorange.com/vkontakte-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-vk"
        },
        "pinterest": {
            "label":"Pinterest", "type":"oauth", "image":"pinterest.png", "scope": "read_public", "authorize": "https://api.pinterest.com/oauth/", "token": "https://api.pinterest.com/v1/oauth/token", "userinfo": "https://api.pinterest.com/v1/me/", "guide":"https://plugins.miniorange.com/pinterest-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-pinterest"
        },
        "vimeo": {
            "label":"Vimeo", "type":"oauth", "image":"vimeo.png", "scope": "public", "authorize": "https://api.vimeo.com/oauth/authorize", "token": "https://api.vimeo.com/oauth/access_token", "userinfo": "https://api.vimeo.com/me", "guide":"https://plugins.miniorange.com/vimeo-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-vimeo"
        },
        "deviantart": {
            "label":"DeviantArt", "type":"oauth", "image":"devart.png", "scope": "browse", "authorize": "https://www.deviantart.com/oauth2/authorize", "token": "https://www.deviantart.com/oauth2/token", "userinfo": "https://www.deviantart.com/api/v1/oauth2/user/profile", "guide":"https://plugins.miniorange.com/deviantart-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-deviantart"
        },
        "dailymotion": {
            "label":"Dailymotion", "type":"oauth", "image":"dailymotion.png", "scope": "email", "authorize": "https://www.dailymotion.com/oauth/authorize", "token": "https://api.dailymotion.com/oauth/token", "userinfo": "https://api.dailymotion.com/user/me?fields=id,username,email,first_name,last_name", "guide":"https://plugins.miniorange.com/dailymotion-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "meetup": {
            "label":"Meetup", "type":"oauth", "image":"meetup.png", "scope": "basic", "authorize": "https://secure.meetup.com/oauth2/authorize", "token": "https://secure.meetup.com/oauth2/access", "userinfo": "https://api.meetup.com/members/self", "guide":"https://plugins.miniorange.com/joomla-oauth-client-changelog#top", "logo_class":"fa fa-lock"
        },
        "autodesk": {
            "label":"Autodesk", "type":"oauth", "image":"autodesk.png", "scope": "user:read user-profile:read", "authorize": "https://developer.api.autodesk.com/authentication/v1/authorize", "token": "https://developer.api.autodesk.com/authentication/v1/gettoken", "userinfo": "https://developer.api.autodesk.com/userprofile/v1/users/@me", "guide":"https://plugins.miniorange.com/autodesk-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "zendesk": {
            "label":"Zendesk", "type":"oauth", "image":"zendesk.png", "scope": "read write", "authorize": "https://{domain}/oauth/authorizations/new", "token": "https://{domain}/oauth/tokens", "userinfo": "https://{domain}/api/v2/users", "guide":"https://plugins.miniorange.com/zendesk-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "laravel": {
            "label":"Laravel", "type":"oauth", "image":"laravel.png", "scope": "", "authorize": "http://{domain}/oauth/authorize", "token": "http://{domain}/oauth/token", "userinfo": "http://{domain}}/api/user/get", "guide":"https://plugins.miniorange.com/laravel-passport-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "identityserver": {
            "label":"Identity Server", "type":"oauth", "image":"identityserver.png", "scope": "openid", "authorize": "https://{domain}/connect/authorize", "token": "https://{domain}/connect/token", "userinfo": "https://{domain}/connect/introspect", "guide":"https://plugins.miniorange.com/identityserver3-oauth-openid-connect-single-sign-on-sso-into-joomla-identityserver3-sso-login", "logo_class":"fa fa-lock"
        },
        "nextcloud": {
            "label":"Nextcloud", "type":"oauth", "image":"nextcloud.png", "scope": "user:read:email", "authorize": "https://{domain}/index.php/apps/oauth2/authorize", "token": "https://{domain}/index.php/apps/oauth2/api/v1/token", "userinfo": "https://{domain}/ocs/v2.php/cloud/user?format=json", "guide":"https://plugins.miniorange.com/nextcloud-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "twitch": {
            "label":"Twitch", "type":"oauth", "image":"twitch.png", "scope": "Analytics:read:extensions", "authorize": "https://id.twitch.tv/oauth2/authorize", "token": "https://id.twitch.tv/oauth2/token", "userinfo": "https://id.twitch.tv/oauth2/userinfo", "guide":"https://plugins.miniorange.com/twitch-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "wildApricot": {
            "label":"Wild Apricot", "type":"oauth", "image":"wildApricot.png", "scope": "auto", "authorize": "https://{domain}/sys/login/OAuthLogin", "token": "https://oauth.wildapricot.org/auth/token", "userinfo": "https://api.wildapricot.org/v2.1/accounts/{accountid}/contacts/me", "guide":"https://plugins.miniorange.com/wildapricot-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "connect2id": {
            "label":"Connect2id", "type":"oauth", "image":"connect2id.png", "scope": "openid", "authorize": "https://c2id.com/login", "token": "https://{domain}/token", "userinfo": "https://{domain}/userinfo", "guide":"https://plugins.miniorange.com/connect2id-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "miniorange": {
            "label":"miniOrange", "type":"oauth", "image":"miniorange.png", "scope": "openid", "authorize": "https://login.xecurify.com/moas/idp/openidsso", "token": "https://login.xecurify.com/moas/rest/oauth/token", "userinfo": "https://logins.xecurify.com/moas/rest/oauth/getuserinfo", "guide":"https://plugins.miniorange.com/miniorange-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "timezynk": {
            "label":"Timezynk", "type":"oauth", "image":"timezynk.png", "scope": "read:user", "authorize": "https://api.timezynk.com/api/oauth2/v1/auth", "token": "https://api.timezynk.com/api/oauth2/v1/token", "userinfo": "https://api.timezynk.com/api/oauth2/v1/userinfo", "guide":"https://plugins.miniorange.com/joomla-oauth-client-changelog#top", "logo_class":"fa fa-lock"
        },
        "Amazon": {
            "label":"Amazon", "type":"oauth", "image":"cognito.png", "scope": "profile", "authorize": "https://www.amazon.com/ap/oa", "token": "https://api.amazon.com/auth/o2/token", "userinfo": "https://api.amazon.com/user/profile", "guide":"https://plugins.miniorange.com/amazon-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "Office 365": {
            "label":"Office 365", "type":"oauth", "image":"microsoft.webp", "scope": "openid email profile", "authorize": "https://login.microsoftonline.com/{tenant}/oauth2/v2.0/authorize", "token": "https://login.microsoftonline.com/{tenant}/oauth2/v2.0/token", "userinfo": "https://graph.microsoft.com/beta/me", "guide":"https://plugins.miniorange.com/office365-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "Instagram": {
            "label":"Instagram", "type":"oauth", "image":"instagram.png", "scope": "user_profile user_media", "authorize": "https://api.instagram.com/oauth/authorize", "token": "https://api.instagram.com/oauth/access_token", "userinfo": "https://graph.instagram.com/me?fields=id,username&access_token=", "guide":"https://plugins.miniorange.com/instagram-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "Line":{
            "label":"Line", "type":"oauth", "image":"line.webp", "scope": "profile openid email", "authorize": "https://access.line.me/oauth2/v2.1/authorize", "token": "https://api.line.me/oauth2/v2.1/token", "userinfo": "https://api.line.me/v2/profile", "guide":"https://plugins.miniorange.com/line-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "PingFederate": {
            "label":"PingFederate", "type":"oauth", "image":"ping.webp", "scope": "openid", "authorize": "https://{domain}/as/authorization.oauth2", "token": "https://{domain}/as/token.oauth2", "userinfo": "https://{domain}/idp/userinfo.oauth2", "guide":"https://plugins.miniorange.com/ping-federate-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "OpenAthens": {
            "label":"OpenAthens", "type":"oauth", "image":"openathens.webp", "scope": "openid", "authorize": "https://sp.openathens.net/oauth2/authorize", "token": "https://sp.openathens.net/oauth2/token", "userinfo": "https://sp.openathens.net/oauth2/userInfo", "guide":"https://plugins.miniorange.com/openathens-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "Intuit": {
            "label":"Intuit", "type":"oauth", "image":"intuit.webp", "scope": "openid email profile", "authorize": "https://appcenter.intuit.com/connect/oauth2", "token": "https://oauth.platform.intuit.com/oauth2/v1/tokens/bearer", "userinfo": "https://accounts.platform.intuit.com/v1/openid_connect/userinfo", "guide":"https://plugins.miniorange.com/oauth-openid-connect-single-sign-on-sso-into-joomla-using-intuit", "logo_class":"fa fa-lock"
        },
        "Twitter": {
            "label":"Twitter", "type":"oauth", "image":"twitter-logo.webp", "scope": "email", "authorize": "https://api.twitter.com/oauth/authorize", "token": "https://api.twitter.com/oauth2/token", "userinfo": "https://api.twitter.com/1.1/users/show.json?screen_name=here-comes-twitter-screen-name", "guide":"https://plugins.miniorange.com/twitter-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "WordPress": {
            "label":"WordPress", "type":"oauth", "image":"wordpress.png", "scope": "profile openid email custom", "authorize": "http://{site_base_url}/wp-json/moserver/authorize", "token": "http://{site_base_url}/wp-json/moserver/token", "userinfo": "http://{site_base_url}/wp-json/moserver/resource", "guide":"https://plugins.miniorange.com/oauth-openid-connect-single-sign-on-sso-into-joomla-using-wordpress", "logo_class":"fa fa-lock"
        },
        "Subscribestar": {
            "label":"Subscribestar", "type":"oauth", "image":"Subscriberstar-logo.png", "scope": "user.read user.email.read", "authorize": "https://www.subscribestar.com/oauth2/authorize", "token": "https://www.subscribestar.com/oauth2/token", "userinfo": "https://www.subscribestar.com/api/graphql/v1?query={user{name,email}}", "guide":"https://plugins.miniorange.com/subscribestar-oauth-openid-connect-single-sign-on-sso-into-joomla-subscribestar-sso-login", "logo_class":"fa fa-lock"
        },
        "Classlink": {
            "label":"Classlink", "type":"oauth", "image":"classlink.webp", "scope": "email profile oneroster full", "authorize": "https://launchpad.classlink.com/oauth2/v2/auth", "token": "https://launchpad.classlink.com/oauth2/v2/token", "userinfo": "https://nodeapi.classlink.com/v2/my/info", "guide":"https://plugins.miniorange.com/classlink-oauth-sso-openid-connect-single-sign-on-in-joomla-classlink-sso-login", "logo_class":"fa fa-lock"
        },
        "HP": {
            "label":"HP", "type":"oauth", "image":"hp-logo.webp", "scope": "read", "authorize": "https://{hp_domain}/v1/oauth/authorize", "token": "https://{hp_domain}/v1/oauth/token", "userinfo": "https://{hp_domain}/v1/userinfo", "guide":"https://plugins.miniorange.com/hp-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
        "Basecamp": {
            "label":"Basecamp", "type":"oauth", "image":"basecamp-logo.webp", "scope": "openid", "authorize": "https://launchpad.37signals.com/authorization/new?type=web_server", "token": "https://launchpad.37signals.com/authorization/token?type=web_server", "userinfo": "https://launchpad.37signals.com/authorization.json", "guide":"https://plugins.miniorange.com/basecamp-oauth-and-openid-connect-single-sign-on-sso-login", "logo_class":"fa fa-lock"
        },
        "Feide": {
            "label":"Feide", "type":"oauth", "image":"feide-logo.webp", "scope": "openid", "authorize": "https://auth.dataporten.no/oauth/authorization", "token": "https://auth.dataporten.no/oauth/token", "userinfo": "https://auth.dataporten.no/openid/userinfo", "guide":"https://plugins.miniorange.com/feide-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
        "ServiceNow": {
            "label":"ServiceNow", "type":"oauth", "image":"servicenow-logo.webp", "scope": "email profile", "authorize": "https://{your-servicenow-domain}/oauth_auth.do", "token": "https://{your-servicenow-domain}/oauth_token.do", "userinfo": "https://{your-servicenow-domain}/{base-api-path}?access_token=", "guide":"https://plugins.miniorange.com/servicenow-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
        "IMIS": {
            "label":"IMIS", "type":"oauth", "image":"imis-logo.webp", "scope": "openid", "authorize": "https://{your-imis-domain}/sso-pages/Aurora-SSO-Redirect.aspx", "token": "https://{your-imis-domain}/token", "userinfo": "https://{your-imis-domain}/api/iqa?queryname=$/Bearer_Info_Aurora", "guide":"https://plugins.miniorange.com/imis-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
        "OpenedX": {
            "label":"OpenedX", "type":"oauth", "image":"openedx-logo.webp", "scope": "email profile", "authorize": "https://{your-domain}/oauth2/authorize", "token": "https://{your-domain}/oauth2/access_token", "userinfo": "https://{your-domain}/api/mobile/v1/my_user_info", "guide":"https://plugins.miniorange.com/open-edx-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
        "DigitalOcean": {
            "label":"DigitalOcean", "type":"oauth", "image":"digitalocean-logo.webp", "scope": "read", "authorize": "https://cloud.digitalocean.com/v1/oauth/authorize", "token": "https://cloud.digitalocean.com/v1/oauth/token", "userinfo": "https://api.digitalocean.com/v2/account", "guide":"https://plugins.miniorange.com/digital-ocean-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
        "MemberClicks": {
			"label":"MemberClicks", "type":"oauth", "image":"memberclicks-logo.webp", "scope": "read write", "authorize": "https://{orgId}.memberclicks.net/oauth/v1/authorize", "token": "https://{orgId}.memberclicks.net/oauth/v1/token", "userinfo": "https://{orgId}.memberclicks.net/api/v1/profile/me", "guide":"https://plugins.miniorange.com/memberclicks-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
		"Neon CRM": {
			"label":"Neon CRM", "type":"oauth", "image":"neon-logo.webp", "scope": "openid", "authorize": "https://{your Neon CRM organization id}.z2systems.com/np/oauth/auth", "token": "https://{your Neon CRM organization id}.z2systems.com/np/oauth/token", "userinfo": "https://api.neoncrm.com/neonws/services/api/account/retrieveIndividualAccount?accountId=", "guide":"https://plugins.miniorange.com/neoncrm-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
		"Canvas": {
			"label":"Canvas", "type":"oauth", "image":"canvas-logo.webp", "scope": "openid profile", "authorize": "https://{your-site-url}/login/oauth2/auth", "token": "https://{your-site-url}/login/oauth2/token", "userinfo": "https://{your-site-url}/login/v2.1/users/self", "guide":"https://plugins.miniorange.com/canvas-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
        "Otoy": {
			"label":"Otoy", "type":"oauth", "image":"otoy-logo.webp", "scope": "openid", "authorize": "https://account.otoy.com/oauth/authorize", "token": "https://account.otoy.com/oauth/token", "userinfo": "https://account.otoy.com/api/v1/user.json", "guide":"https://plugins.miniorange.com/otoy-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
        "azureb2c": {
            "label":"Azure B2C", "type":"openidconnect", "image":"azure.png", "scope": "openid email", "authorize": "https://{tenant}.b2clogin.com/{tenant}.onmicrosoft.com/{policy}/oauth2/v2.0/authorize", "token": "https://{tenant}.b2clogin.com/{tenant}.onmicrosoft.com/{policy}/oauth2/v2.0/token", "userinfo": "", "guide":"https://plugins.miniorange.com/azure-ad-b2c-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-windowslive"
        },
        "adfs": {
            "label":"ADFS", "type":"openidconnect", "image":"adfs.png", "scope": "openid", "authorize": "https://{domain}/adfs/oauth2/authorize/", "token": "https://{domain}/adfs/oauth2/token/", "userinfo": "", "guide":"", "logo_class":"fa fa-windowslive"
        },
        "keycloak": {
            "label":"keycloak", "type":"openidconnect", "image":"keycloak.png", "scope": "openid", "authorize": "{domain}realms/{realm}/protocol/openid-connect/auth", "token": "{domain}realms/{realm}/protocol/openid-connect/token", "userinfo": "{domain}realms/{realm}/protocol/openid-connect/userinfo", "guide":"https://plugins.miniorange.com/keycloak-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "okta": {
            "label":"Okta", "type":"openidconnect", "image":"okta.png", "scope": "openid email profile", "authorize": "https://{domain}/oauth2/default/v1/authorize", "token": "https://{domain}/oauth2/default/v1/token", "userinfo": "", "guide":"https://plugins.miniorange.com/login-with-okta-using-joomla", "logo_class":"fa fa-lock"
        },
        "onelogin": {
            "label":"OneLogin", "type":"openidconnect", "image":"onelogin.png", "scope": "openid", "authorize": "https://{domain}/oidc/auth", "token": "https://{domain}/oidc/token", "userinfo": "", "guide":"https://plugins.miniorange.com/onelogin-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "paypal": {
            "label":"PayPal", "type":"openidconnect", "image":"paypal.png", "scope": "openid", "authorize": "https://www.paypal.com/signin/authorize", "token": "https://api.paypal.com/v1/oauth2/token", "userinfo": "", "guide":"https://plugins.miniorange.com/paypal-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-paypal"
        },
        "swiss-rx-login": {
            "label":"Swiss RX Login", "type":"openidconnect", "image":"swiss-rx-login.png", "scope": "anonymous", "authorize": "https://www.swiss-rx-login.ch/oauth/authorize", "token": "https://swiss-rx-login.ch/oauth/token", "userinfo": "", "guide":"https://plugins.miniorange.com/joomla-oauth-client-changelog#top", "logo_class":"fa fa-lock"
        },
        "yahoo": {
            "label":"Yahoo", "type":"openidconnect", "image":"yahoo.png", "scope": "openid", "authorize": "https://api.login.yahoo.com/oauth2/request_auth", "token": "https://api.login.yahoo.com/oauth2/get_token", "userinfo": "", "guide":"https://plugins.miniorange.com/yahoo-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-yahoo"
        },
        "orcid": {
            "label":"ORCID", "type":"openidconnect", "image":"orcid.png", "scope": "openid", "authorize": "https://orcid.org/oauth/authorize", "token": "https://orcid.org/oauth/token", "userinfo": "", "guide":"https://plugins.miniorange.com/orcid-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "diaspora": {
            "label":"Diaspora", "type":"openidconnect", "image":"diaspora.png", "scope": "openid", "authorize": "https://{domain}/api/openid_connect/authorizations/new", "token": "https://{domain}/api/openid_connect/access_tokens", "userinfo": "", "guide":"https://plugins.miniorange.com/joomla-oauth-client-changelog#top", "logo_class":"fa fa-lock"
        },
        "MineCraft": {
			"label":"MineCraft", "type":"openidconnect", "image":"minecraft-logo.webp", "scope": "openid", "authorize": "https://login.live.com/oauth20_authorize.srf", "token": "https://login.live.com/oauth20_token.srf", "userinfo": "", "guide":"https://plugins.miniorange.com/minecraft-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
        "Freja EID": {
            "label":"Freja EID", "type":"openidconnect", "image":"frejaeid-logo.webp", "scope": "openid profile email", "authorize": "https://oidc.prod.frejaeid.com/oidc/authorize", "token": "https://oidc.prod.frejaeid.com/oidc/token", "userinfo": "", "guide":"https://plugins.miniorange.com/freja-eid-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
        "Elvanto": {
            "label":"Elvanto", "type":"openidconnect", "image":"elvanto-logo.webp", "scope": "ManagePeople", "authorize": "https://api.elvanto.com/oauth?", "token": "https://api.elvanto.com/oauth/token", "userinfo": "", "guide":"https://plugins.miniorange.com/elvanto-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
        "UNA": {
            "label":"UNA", "type":"openidconnect", "image":"una-logo.webp", "scope": "basic", "authorize": "https://{site-url}.una.io/oauth2/authorize?", "token": "https://{site-url}.una.io/oauth2/access_token", "userinfo": "", "guide":"https://plugins.miniorange.com/una-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
		"Ticketmaster": {
			"label":"Ticketmaster", "type":"openidconnect", "image":"ticketmaster-logo.webp", "scope": "openid email", "authorize": "https://auth.ticketmaster.com/as/authorization.oauth2", "token": "https://auth.ticketmaster.com/as/token.oauth2", "userinfo": "", "guide":"https://plugins.miniorange.com/ticketmaster-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
		"Mindbody": {
			"label":"Mindbody", "type":"openidconnect", "image":"mindbody-logo.webp", "scope": "email profile openid", "authorize": "https://signin.mindbodyonline.com/connect/authorize", "token": "https://signin.mindbodyonline.com/connect/token", "userinfo": "", "guide":"https://plugins.miniorange.com/mindbody-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
		"iGov": {
			"label":"iGov", "type":"openidconnect", "image":"iGov-logo.webp", "scope": "openid profile", "authorize": "https://idp.government.gov/oidc/authorization", "token": "https://idp.government.gov/token", "userinfo": "", "guide":"https://plugins.miniorange.com/igov-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
		"LearnWorlds": {
			"label":"LearnWorlds", "type":"openidconnect", "image":"learnworlds-logo.webp", "scope": "openid profile", "authorize": "https://api.learnworlds.com/oauth", "token": "https://api.learnworlds.com/oauth2/access_token", "userinfo": "", "guide":"https://plugins.miniorange.com/learnworlds-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
        "other": {
            "label":"Custom OAuth", "type":"oauth", "image":"customapp.png", "scope": "", "authorize": "", "token": "", "userinfo": "", "guide":"https://plugins.miniorange.com/joomla-single-sign-on-with-custom-oauth-provider", "logo_class":"fa fa-lock"
        },
        "openidconnect": {
            "label":"Custom OpenID Connect App", "type":"openidconnect", "image":"customapp.png", "scope": "", "authorize": "", "token": "", "userinfo": "", "guide":"https://plugins.miniorange.com/joomla-single-sign-on-with-custom-openid-connect-provider", "logo_class":"fa fa-lock"
        }
        }';
    }

    function getAppData()
    {
        return '{
			"azure": {
				"0":"both","1":"Tenant"
			},
			"azureb2c": {
				"0":"both","1":"Tenant,Policy"
			},
			"cognito": {
				"0":"both","1": "Domain"
			},
			"adfs": {
				"0":"both","1":"Domain"
			},
			"whmcs": {
				"0":"both","1":"Domain"
			},
			"keycloak": {
				"0":"both","1":"Domain,Realm"
			},
			"invisioncommunity": {
				"0":"both","1":"Domain"
			},
			"bitrix24": {
				"0":"both","1":"Domain"
			},
			"wso2": {
				"0":"both","1":"Domain"
			},
			"okta": {
				"0":"header","1":"Domain"
			},
			"onelogin": {
				"0":"both","1":"Domain"
			},
			"gluu": {
				"0":"both","1": "Domain" 
			},
			"zendesk": {
				"0":"both","1":"Domain"
			},
			"laravel": {
				"0":"both","1":"Domain"
			},
			"identityserver": {
				"0":"both","1":"Domain"
			},
			"nextcloud": {
				"0":"both","1":"Domain"
			},
			"wildApricot": {
				"0":"both","1":"Domain,AccountId"
			},
			"connect2id": {
				"0":"both","1":"Domain"
			},
			"diaspora": {
				"0":"both","1":"Domain" 
			},
			"Office 365": {
				"0":"both","1":"Tenant" 
			},
			"PingFederate": {
				"0":"both","1":"Domain"
			},
			"HP": {
				"0":"both","1":"Domain"
			},
			"Neon CRM": {
				"0":"both","1":"Domain"
			},
			"Canvas": {
				"0":"both","1":"Domain"
			},
			"UNA": {
				"0":"both","1":"Domain"
			},
			"OpenedX": {
				"0":"both","1":"Domain"
			},
			"ServiceNow": {
				"0":"both","1":"Domain"
			},
			"WordPress": {
				"0":"both","1":"Domain"
			},
			"MemberClicks": {
				"0":"both","1":"Domain"
			},
			"IMIS": {
				"0":"both","1":"Domain"
			}
	
			
		}';
    }
}?>
