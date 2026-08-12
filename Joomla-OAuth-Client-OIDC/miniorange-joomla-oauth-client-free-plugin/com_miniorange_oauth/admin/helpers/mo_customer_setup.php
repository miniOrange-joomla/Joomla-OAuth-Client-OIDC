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
	/**
	 * Customer email address.
	 *
	 * @var    string
	 */
	public $email;

	/**
	 * Customer phone number.
	 *
	 * @var    string
	 */
	public $phone;

	/**
	 * Customer key identifier.
	 *
	 * @var    string
	 */
	public $customerKey;

	/**
	 * Transaction identifier.
	 *
	 * @var    string
	 */
	public $transactionId;

	public function requestForDemo($email, $plan, $description, $demoTrail = null, $callDate = null, $timeZone = null)
	{
		$customerKey = MoOauthUtility::getMiniOrangeCustomerKey();
		$fromEmail = $email;
		$app = Factory::getApplication();

		if (method_exists($app, 'getIdentity'))
		{
			// Joomla 4+
			$user = $app->getIdentity();
		}
		else
		{
			// Joomla 3
			$user = Factory::getUser();
		}

		$adminEmail = $user->email;
		$jVersion = new Version;
		$phpVersion = phpversion();
		$moSystemOS = MoOauthUtility::getOperatingSystem();
		$jCmsVersion = $jVersion->getShortVersion();
		$moPluginVersion = MoOauthUtility::getPluginVersion();
		$timezone = self::getUserTimezone();

		$content = '<div>Hello, <br><br><strong>Company</strong> :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br><strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br><b>Email :</b><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br><b>Plugin Name: </b>' . $plan . '<br><br><b>Description: </b>' . $description;

		if (is_null($callDate) && is_null($timeZone))
		{
			$content = '<div>Hello, <br><br>Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br><strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br><b>Email :</b><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br><b>Plugin Name: </b>' . $plan . '<br><br><b>Description: </b>' . $description;
			$subject  = "Joomla OAuth Client Request for " . $demoTrail . " - " . $fromEmail;
		}
		else
		{
			$subject  = "Joomla OAuth Client Request for Screen Share/Call - " . $fromEmail;
			$content = '<div>Hello, <br><br><strong>Company</strong> :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br><strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br><b>Email :</b><a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a><br><br><b>Time Zone:</b> ' . $timeZone . '<br><br><b>Date to set up call: </b>' . $callDate . '<br><br><b>Issue :</b> ' . $plan . '<br><br><b>Description: </b>' . $description;
		}

		$content .= '<br><br><b>System Information: </b>Joomla:' . $jCmsVersion . ' | PHP: ' . $phpVersion . ' | Plugin: ' . $moPluginVersion . ' | OS: ' . $moSystemOS . ' | Time Zone: ' . $timezone . '<br></div>';

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

		return self::sendEmail($fields);
	}

	public static function submitFeedbackForm($email, $phone, $query)
	{
		$customerKey = MoOauthUtility::getMiniOrangeCustomerKey();
		$fromEmail   = $email;
		$app = Factory::getApplication();

		if (method_exists($app, 'getIdentity'))
		{
			// Joomla 4+
			$user = $app->getIdentity();
		}
		else
		{
			// Joomla 3
			$user = Factory::getUser();
		}

		$adminEmail         = $user->email;
		$jVersion           = new Version;
		$phpVersion         = phpversion();
		$jCmsVersion        = $jVersion->getShortVersion();
		$osName = php_uname('s');
		$osRelease = php_uname('r');
		$osArch = php_uname('m');
		$timezone = self::getUserTimezone();

		if (class_exists("MoOAuthUtility"))
		{
			$moPluginVersion     = MoOauthUtility::getPluginVersion();
		}
		else
		{
			$moPluginVersion = "NA";
		}

		$serverName = self::getServerType();
		$subject = "Joomla OAuth Client Free Feedback - " . $fromEmail;

		$query1 = " miniOrange Joomla [Free] Oauth Client";
		$content = '<div> Hello, <br><br>
                    <strong>Company</strong> :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
                    <strong>Phone Number</strong> :' . $phone . '<br><br>
                    <strong>Email :<a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a></strong><br><br>
                    <strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br>
                    <b>Plugin Deactivated: </b>' . $query1 . '<br><br>
                    <b>Reason: </b> ' . $query . '<br><br>
                    <b>System Information: Joomla: </b>' . $jCmsVersion . ' | PHP: ' . $phpVersion . ' | Plugin: ' . $moPluginVersion . ' | OS: ' . $osName . ' ' . $osRelease . ' ' . $osArch . ' | Time Zone: ' . $timezone . '<br>
                    <b>Server Name: </b>' . $serverName . '</div>';

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

		return self::sendEmail($fields);
	}

	public static function getAccountDetails()
	{
		$db = self::getDBObject();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_oauth_customer'));
		$query->where($db->quoteName('id') . " = 1");

		$db->setQuery($query);
		$result = $db->loadAssoc();

		return $result;
	}

	private static function getDBObject()
	{
		$app = Factory::getApplication();

		if (method_exists($app, 'getDatabase'))
		{
			return $app->getDatabase();
		}

		return Factory::getDbo();
	}

	public static function getConfigurationDetails()
	{
		$db = self::getDBObject();

		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_oauth_config'));
		$query->where($db->quoteName('id') . " = 1");

		$db->setQuery($query);
		$result = $db->loadAssoc();

		return $result;
	}

	// Efficency check of the plugin for better improvement
	public static function pluginEfficiencyCheck($email, $appname, $baseUrl, $cTime, $dnoSsos, $tnoSsos, $previousUpdate, $presentUpdate, $reason = 'NA', $scope = 'NULL', $authorisationURL = 'NULL', $accesstokenurl = 'NULL', $resourceownerdetailsurl = 'NULL', $inHeaderOrBody = 'NULL', $testConfiguration = "")
	{
		$customerDetails = self::getAccountDetails();
		$configDetails = self::getConfigurationDetails();

		$customerKey = MoOauthUtility::getMiniOrangeCustomerKey();
		$fromEmail = $email;
		$app = Factory::getApplication();

		if (method_exists($app, 'getIdentity'))
		{
			// Joomla 4+
			$user = $app->getIdentity();
		}
		else
		{
			// Joomla 3
			$user = Factory::getUser();
		}

		$adminEmail = $user !== null && isset($user->email) ? $user->email : $email;
		$subject = "miniOrange Joomla OAuth Client [Free] for Efficiency";
		$ssoTest = base64_decode($customerDetails['sso_test']);
		$ssoVar = base64_decode($customerDetails['sso_var']);
		$baseUrl = Uri::root();
		$appname = empty($appname) && isset($configDetails['appname']) ? $configDetails['appname'] : $appname;
		$scope = ($scope == 'NULL') && isset($configDetails['app_scope']) ? $configDetails['app_scope'] : $scope;
		$authorisationURL = ($authorisationURL == 'NULL') && isset($configDetails['authorize_endpoint']) ? $configDetails['authorize_endpoint'] : $authorisationURL;
		$accesstokenurl  = ($accesstokenurl == 'NULL') && isset($configDetails['access_token_endpoint']) ? $configDetails['access_token_endpoint'] : $accesstokenurl;
		$resourceownerdetailsurl  = ($resourceownerdetailsurl == 'NULL') && isset($configDetails['user_info_endpoint']) ? $configDetails['user_info_endpoint'] : $resourceownerdetailsurl;
		$serverName = self::getServerType();
		$timezone = self::getUserTimezone();

		$query1 = "miniOrange Joomla [Free] OAuth Client to improve efficiency ";
		$content = '<div >Hello, <br><br>
            Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
            Server :' . $appname . '<br><br>
            <strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br>
            <b>Email :<a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a></b><br><br>
            <b>Plugin Efficency Check: ' . $query1 . '</b><br><br>
            <b>Website: ' . $baseUrl . '</b><br><br>
            <b>Time Zone: ' . $timezone . '</b><br><br>
            <b>Server Name:' . $serverName . '</b><br><br>
            Creation Date:' . $cTime . '<br>
            Daily SSO:' . $dnoSsos . '<br>
            Total SSO:' . $tnoSsos . '<br>
            Login Count:' . $ssoTest . '<br>
            Login Limit:' . $ssoVar . '<br>
            Previous Update:' . $previousUpdate . '<br>
            Current Update:' . $presentUpdate . '<br>
            Scope: ' . $scope . '<br>
            Authorize Endpoint:' . $authorisationURL . '<br>
            Token Endpoint: ' . $accesstokenurl . '<br>
            Userinfo Endpoint: ' . $resourceownerdetailsurl . ' <br>
            Header/Body: ' . $inHeaderOrBody . '<br>
            Test Configuration: ' . $testConfiguration . ' <br>
            Message:' . $reason . '</div>';

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

		self::sendEmail($fields);
	}

	public static function sendInstallationEmail()
	{
		$customerKey          = MoOauthUtility::getMiniOrangeCustomerKey();
		$app = Factory::getApplication();

		if (method_exists($app, 'getIdentity'))
		{
			// Joomla 4+
			$user = $app->getIdentity();
		}
		else
		{
			// Joomla 3
			$user = Factory::getUser();
		}

		$adminEmail = $user->email;
		$jVersion = new Version;
		$phpVersion = phpversion();
		$jCmsVersion = $jVersion->getShortVersion();
		$osName = php_uname('s');
		$osRelease = php_uname('r');
		$osArch = php_uname('m');
		$timezone = self::getUserTimezone();

		if (class_exists("MoOAuthUtility"))
		{
			$moPluginVersion = MoOauthUtility::getPluginVersion();
		}
		else
		{
			$moPluginVersion = "NA";
		}

		$serverName = self::getServerType();

		$subject  = "Installation of Joomla OAuth Client [Free]";

		$content = '<div >Hello, <br><br>
            Company :<a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
            <strong>Admin Email : </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br>
            <b>System Information: </b>Joomla:' . $jCmsVersion . ' | PHP: ' . $phpVersion . ' | Plugin: ' . $moPluginVersion . ' | OS: ' . $osName . ' ' . $osRelease . ' ' . $osArch . ' | Time Zone: ' . $timezone . '<br>
            <b>Server Name: </b>' . $serverName . '</div>';

		$fields = array(
			'customerKey'   => $customerKey,
			'sendEmail'     => true,
			'email'         => array(
				'customerKey'   => $customerKey,
				'fromEmail'     => $adminEmail,
				'fromName'      => 'miniOrange',
				'toEmail'       => 'nutan.barad@xecurify.com',
				'bccEmail'      => 'nikhil.bhot@xecurify.com',
				'toName'        => 'nutan.barad@xecurify.com',
				'subject'       => $subject,
				'content'       => $content
			),
		);

		self::sendEmail($fields);
	}

	public function submitContactUs($qEmail, $qPhone, $query, $attributes)
	{
		if (!MoOauthUtility::isCurlInstalled())
		{
			return json_encode(array("status" => 'CURL_ERROR','statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}

		$customerKey = MoOauthUtility::getMiniOrangeCustomerKey();
		$fromEmail    = $qEmail;
		$app = Factory::getApplication();

		if (method_exists($app, 'getIdentity'))
		{
			// Joomla 4+
			$user = $app->getIdentity();
		}
		else
		{
			// Joomla 3
			$user = Factory::getUser();
		}

		$adminEmail = $user->email;
		$jVersion = new Version;
		$phpVersion = phpversion();
		$moSystemOS = MoOauthUtility::getOperatingSystem();
		$jCmsVersion = $jVersion->getShortVersion();
		$moPluginVersion = MoOauthUtility::getPluginVersion();
		$serverName = self::getServerType();
		$timezone = self::getUserTimezone();
		$query = '[miniOrange Joomla Oauth Client Free | ' . $phpVersion . ' | ' . $jCmsVersion . ' | ' . $moPluginVersion . ' | ' . $moSystemOS . ' | ' . $timezone . '] <br> ' . $query;
		$query = $query . '<br><strong>Configuration: </strong><br> <strong>App Name:</strong>  ' . $attributes['appname'] . '<br> <strong>Custom App: </strong> ' . $attributes['custom_app'] . ' <br> <strong>App Scope: </strong>' . $attributes['app_scope'] . '<br> <strong>Authorize Endpoint: </strong>' . $attributes['authorize_endpoint'];
		$subject = "Query for Joomla OAuth Client Free  - " . $fromEmail;
		$content = '<div>Hello, <br><br>
            <strong>Company: </strong> <a href="' . $_SERVER['SERVER_NAME'] . '" target="_blank" >' . $_SERVER['SERVER_NAME'] . '</a><br><br>
            <strong>Phone Number: </strong>' . $qPhone . '<br><br>
            <strong>Admin Email: </strong><a href="mailto:' . $adminEmail . '" target="_blank">' . $adminEmail . '</a><br><br>
            <b>Email: <a href="mailto:' . $fromEmail . '" target="_blank">' . $fromEmail . '</a></b><br>
            <b>Server Name: </b>' . $serverName . '<br>
            <b>Query: </b>' . $query . '</div>';

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

		return self::sendEmail($fields);
	}

	private static function sendEmail($fields)
	{
		$fieldString = json_encode($fields);

		if (!MoOauthUtility::isCurlInstalled())
		{
			return json_encode(array("status" => 'CURL_ERROR','statusMessage' => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
		}

		$hostname = MoOauthUtility::getHostname();
		$url = $hostname . '/moas/api/notify/send';
		$ch = curl_init($url);

		$customerKey = MoOauthUtility::getMiniOrangeCustomerKey();
		$apiKey = MoOauthUtility::getMiniOrangeApiKey();

		if (empty($customerKey) || empty($apiKey))
		{
			return json_encode(
				[
					'status'        => 'Error',
					'statusMessage' => 'MiniOrange API credentials are not configured.',
				]
			);
		}

		$currentTimeInMillis = round(microtime(true) * 1000);
		$stringToHash = $customerKey . number_format($currentTimeInMillis, 0, '', '') . $apiKey;
		$hashValue = hash("sha512", $stringToHash);
		$customerKeyHeader = "Customer-Key: " . $customerKey;
		$timestampHeader = "Timestamp: " . number_format($currentTimeInMillis, 0, '', '');
		$authorizationHeader = "Authorization: " . $hashValue;

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		MoOauthUtility::applySecureCurlOptions($ch);

		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt(
			$ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", $customerKeyHeader,
			$timestampHeader, $authorizationHeader)
		);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldString);
		$proxyServer = self::getConfigurationDetails();
		$proxyHostName = isset($proxyServer['proxy_host_name']) ? $proxyServer['proxy_host_name'] : '';
		$portNumber = isset($proxyServer['port_number']) ? $proxyServer['port_number'] : '';
		$username = isset($proxyServer['username']) ? $proxyServer['username'] : '';
		$password = isset($proxyServer['password']) ? base64_decode($proxyServer['password']) : '';

		if (!empty($proxyHostName))
		{
			curl_setopt($ch, CURLOPT_PROXY, $proxyHostName);
			curl_setopt($ch, CURLOPT_PROXYPORT, $portNumber);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_PROXYUSERPWD, $username . ':' . $password);
		}

		$content = curl_exec($ch);

		if (curl_errno($ch))
		{
			return json_encode(array("status" => 'Error','message' => 'Request Error: ' . curl_error($ch)));
		}

		return ($content);
	}

	public static function getServerType()
	{
		return $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
	}

	public static function getUserTimezone()
	{
		$app = Factory::getApplication();

		try
		{
			if (!empty($_COOKIE['user_tz']))
			{
				$tz = new DateTimeZone($_COOKIE['user_tz']);
				$dt = new DateTime('now', $tz);

				return 'UTC' . $dt->format('P') . ' (' . $_COOKIE['user_tz'] . ')';
			}

			if (method_exists($app, 'getIdentity'))
			{
				// Joomla 4+
				$user = $app->getIdentity();
			}
			else
			{
				// Joomla 3
				$user = Factory::getUser();
			}

			if ($user && $user->id)
			{
				$userTz = $user->getParam('timezone');

				if (!empty($userTz))
				{
					$tz = new \DateTimeZone($userTz);
					$dt = new \DateTime('now', $tz);

					return 'UTC' . $dt->format('P');
				}
			}

			if (method_exists($app, 'getConfig'))
			{
				// Joomla 4+
				$siteTz = $app->getConfig()->get('offset');
			}
			else
			{
				// Joomla 3
				$siteTz = Factory::getConfig()->get('offset');
			}

			if (!empty($siteTz))
			{
				$tz = new \DateTimeZone($siteTz);
				$dt = new \DateTime('now', $tz);

				return 'UTC' . $dt->format('P');
			}
		}
		catch (\Exception $e)
		{
			return 'UTC+00:00';
		}

		return 'UTC+00:00';
	}

	public function getAppJason()
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
        "pinterest": {
            "label":"Pinterest", "type":"oauth", "image":"pinterest.png", "scope": "read_public", "authorize": "https://api.pinterest.com/oauth/", "token": "https://api.pinterest.com/v1/oauth/token", "userinfo": "https://api.pinterest.com/v1/me/", "guide":"https://plugins.miniorange.com/pinterest-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-pinterest"
        },
        "vimeo": {
            "label":"Vimeo", "type":"oauth", "image":"vimeo.png", "scope": "public", "authorize": "https://api.vimeo.com/oauth/authorize", "token": "https://api.vimeo.com/oauth/access_token", "userinfo": "https://api.vimeo.com/me", "guide":"https://plugins.miniorange.com/vimeo-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-vimeo"
        },
        "dailymotion": {
            "label":"Dailymotion", "type":"oauth", "image":"dailymotion.png", "scope": "email", "authorize": "https://www.dailymotion.com/oauth/authorize", "token": "https://api.dailymotion.com/oauth/token", "userinfo": "https://api.dailymotion.com/user/me?fields=id,username,email,first_name,last_name", "guide":"https://plugins.miniorange.com/dailymotion-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "autodesk": {
            "label":"Autodesk", "type":"oauth", "image":"autodesk.png", "scope": "user:read user-profile:read", "authorize": "https://developer.api.autodesk.com/authentication/v1/authorize", "token": "https://developer.api.autodesk.com/authentication/v1/gettoken", "userinfo": "https://developer.api.autodesk.com/userprofile/v1/users/@me", "guide":"https://plugins.miniorange.com/autodesk-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "zendesk": {
            "label":"Zendesk", "type":"oauth", "image":"zendesk.png", "scope": "read write", "authorize": "https://{domain}/oauth/authorizations/new", "token": "https://{domain}/oauth/tokens", "userinfo": "https://{domain}/api/v2/users", "guide":"", "logo_class":"fa fa-lock"
        },
        "laravel": {
            "label":"Laravel", "type":"oauth", "image":"laravel.png", "scope": "", "authorize": "http://{domain}/oauth/authorize", "token": "http://{domain}/oauth/token", "userinfo": "http://{domain}}/api/user/get", "guide":"https://plugins.miniorange.com/laravel-passport-sso-single-sign-on-joomla-using-oauth-client-openid-connect", "logo_class":"fa fa-lock"
        },
        "identityserver": {
            "label":"Identity Server", "type":"oauth", "image":"identityserver.png", "scope": "openid", "authorize": "https://{domain}/connect/authorize", "token": "https://{domain}/connect/token", "userinfo": "https://{domain}/connect/introspect", "guide":"", "logo_class":"fa fa-lock"
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
            "label":"WordPress", "type":"oauth", "image":"wordpress.png", "scope": "profile openid email custom", "authorize": "http://{site_base_url}/wp-json/moserver/authorize", "token": "http://{site_base_url}/wp-json/moserver/token", "userinfo": "http://{site_base_url}/wp-json/moserver/resource", "guide":"", "logo_class":"fa fa-lock"
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
        "ServiceNow": {
            "label":"ServiceNow", "type":"oauth", "image":"servicenow-logo.webp", "scope": "email profile", "authorize": "https://{your-servicenow-domain}/oauth_auth.do", "token": "https://{your-servicenow-domain}/oauth_token.do", "userinfo": "https://{your-servicenow-domain}/{base-api-path}?access_token=", "guide":"https://plugins.miniorange.com/servicenow-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
        },
        "IMIS": {
            "label":"IMIS", "type":"oauth", "image":"imis-logo.webp", "scope": "openid", "authorize": "https://{your-imis-domain}/sso-pages/Aurora-SSO-Redirect.aspx", "token": "https://{your-imis-domain}/token", "userinfo": "https://{your-imis-domain}/api/iqa?queryname=$/Bearer_Info_Aurora", "guide":"", "logo_class":"fa fa-lock"
        },
		"Canvas": {
			"label":"Canvas", "type":"oauth", "image":"canvas-logo.webp", "scope": "openid profile", "authorize": "https://{your-site-url}/login/oauth2/auth", "token": "https://{your-site-url}/login/oauth2/token", "userinfo": "https://{your-site-url}/login/v2.1/users/self", "guide":"https://plugins.miniorange.com/canvas-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
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
			"label":"MineCraft", "type":"openidconnect", "image":"minecraft-logo.webp", "scope": "openid", "authorize": "https://login.live.com/oauth20_authorize.srf", "token": "https://login.live.com/oauth20_token.srf", "userinfo": "", "guide":"", "logo_class":"fa fa-lock"
		},
        "Freja EID": {
            "label":"Freja EID", "type":"openidconnect", "image":"frejaeid-logo.webp", "scope": "openid profile email", "authorize": "https://oidc.prod.frejaeid.com/oidc/authorize", "token": "https://oidc.prod.frejaeid.com/oidc/token", "userinfo": "", "guide":"", "logo_class":"fa fa-lock"
        },
        "Elvanto": {
            "label":"Elvanto", "type":"openidconnect", "image":"elvanto-logo.webp", "scope": "ManagePeople", "authorize": "https://api.elvanto.com/oauth?", "token": "https://api.elvanto.com/oauth/token", "userinfo": "", "guide":"", "logo_class":"fa fa-lock"
        },
        "UNA": {
            "label":"UNA", "type":"openidconnect", "image":"una-logo.webp", "scope": "basic", "authorize": "https://{site-url}.una.io/oauth2/authorize?", "token": "https://{site-url}.una.io/oauth2/access_token", "userinfo": "", "guide":"", "logo_class":"fa fa-lock"
        },
		"Ticketmaster": {
			"label":"Ticketmaster", "type":"openidconnect", "image":"ticketmaster-logo.webp", "scope": "openid email", "authorize": "https://auth.ticketmaster.com/as/authorization.oauth2", "token": "https://auth.ticketmaster.com/as/token.oauth2", "userinfo": "", "guide":"https://plugins.miniorange.com/ticketmaster-sso-single-sign-on-into-joomla-using-oauth-openid-connect", "logo_class":"fa fa-lock"
		},
		"Mindbody": {
			"label":"Mindbody", "type":"openidconnect", "image":"mindbody-logo.webp", "scope": "email profile openid", "authorize": "https://signin.mindbodyonline.com/connect/authorize", "token": "https://signin.mindbodyonline.com/connect/token", "userinfo": "", "guide":"", "logo_class":"fa fa-lock"
		},
		"iGov": {
			"label":"iGov", "type":"openidconnect", "image":"iGov-logo.webp", "scope": "openid profile", "authorize": "https://idp.government.gov/oidc/authorization", "token": "https://idp.government.gov/token", "userinfo": "", "guide":"", "logo_class":"fa fa-lock"
		},
		"LearnWorlds": {
			"label":"LearnWorlds", "type":"openidconnect", "image":"learnworlds-logo.webp", "scope": "openid profile", "authorize": "https://api.learnworlds.com/oauth", "token": "https://api.learnworlds.com/oauth2/access_token", "userinfo": "", "guide":"", "logo_class":"fa fa-lock"
		},
        "other": {
            "label":"Custom OAuth", "type":"oauth", "image":"customapp.png", "scope": "", "authorize": "", "token": "", "userinfo": "", "guide":"https://plugins.miniorange.com/joomla-single-sign-on-with-custom-oauth-provider", "logo_class":"fa fa-lock"
        },
        "openidconnect": {
            "label":"Custom OpenID Connect App", "type":"openidconnect", "image":"customapp.png", "scope": "", "authorize": "", "token": "", "userinfo": "", "guide":"", "logo_class":"fa fa-lock"
        }
        }';
	}

	public function getAppData()
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

	public function getCountryCodes()
	{
		return '[
            { "country_name": "Afghanistan", "country_id": "AF", "country_code": "+93" },
            { "country_name": "Albania", "country_id": "AL", "country_code": "+355" },
            { "country_name": "Algeria", "country_id": "DZ", "country_code": "+213" },
            { "country_name": "Andorra", "country_id": "AD", "country_code": "+376" },
            { "country_name": "Angola", "country_id": "AO", "country_code": "+244" },
            { "country_name": "Argentina", "country_id": "AR", "country_code": "+54" },
            { "country_name": "Armenia", "country_id": "AM", "country_code": "+374" },
            { "country_name": "Australia", "country_id": "AU", "country_code": "+61" },
            { "country_name": "Austria", "country_id": "AT", "country_code": "+43" },
            { "country_name": "Azerbaijan", "country_id": "AZ", "country_code": "+994" },
            { "country_name": "Bahamas", "country_id": "BS", "country_code": "+1" },
            { "country_name": "Bahrain", "country_id": "BH", "country_code": "+973" },
            { "country_name": "Bangladesh", "country_id": "BD", "country_code": "+880" },
            { "country_name": "Belarus", "country_id": "BY", "country_code": "+375" },
            { "country_name": "Belgium", "country_id": "BE", "country_code": "+32" },
            { "country_name": "Belize", "country_id": "BZ", "country_code": "+501" },
            { "country_name": "Benin", "country_id": "BJ", "country_code": "+229" },
            { "country_name": "Bhutan", "country_id": "BT", "country_code": "+975" },
            { "country_name": "Bolivia", "country_id": "BO", "country_code": "+591" },
            { "country_name": "Bosnia and Herzegovina", "country_id": "BA", "country_code": "+387" },
            { "country_name": "Botswana", "country_id": "BW", "country_code": "+267" },
            { "country_name": "Brazil", "country_id": "BR", "country_code": "+55" },
            { "country_name": "Brunei", "country_id": "BN", "country_code": "+673" },
            { "country_name": "Bulgaria", "country_id": "BG", "country_code": "+359" },
            { "country_name": "Cambodia", "country_id": "KH", "country_code": "+855" },
            { "country_name": "Cameroon", "country_id": "CM", "country_code": "+237" },
            { "country_name": "Canada", "country_id": "CA", "country_code": "+1" },
            { "country_name": "Chile", "country_id": "CL", "country_code": "+56" },
            { "country_name": "China", "country_id": "CN", "country_code": "+86" },
            { "country_name": "Colombia", "country_id": "CO", "country_code": "+57" },
            { "country_name": "Costa Rica", "country_id": "CR", "country_code": "+506" },
            { "country_name": "Croatia", "country_id": "HR", "country_code": "+385" },
            { "country_name": "Cuba", "country_id": "CU", "country_code": "+53" },
            { "country_name": "Cyprus", "country_id": "CY", "country_code": "+357" },
            { "country_name": "Czech Republic", "country_id": "CZ", "country_code": "+420" },
            { "country_name": "Denmark", "country_id": "DK", "country_code": "+45" },
            { "country_name": "Dominican Republic", "country_id": "DO", "country_code": "+1" },
            { "country_name": "Ecuador", "country_id": "EC", "country_code": "+593" },
            { "country_name": "Egypt", "country_id": "EG", "country_code": "+20" },
            { "country_name": "El Salvador", "country_id": "SV", "country_code": "+503" },
            { "country_name": "Estonia", "country_id": "EE", "country_code": "+372" },
            { "country_name": "Ethiopia", "country_id": "ET", "country_code": "+251" },
            { "country_name": "Finland", "country_id": "FI", "country_code": "+358" },
            { "country_name": "France", "country_id": "FR", "country_code": "+33" },
            { "country_name": "Georgia", "country_id": "GE", "country_code": "+995" },
            { "country_name": "Germany", "country_id": "DE", "country_code": "+49" },
            { "country_name": "Ghana", "country_id": "GH", "country_code": "+233" },
            { "country_name": "Greece", "country_id": "GR", "country_code": "+30" },
            { "country_name": "Hungary", "country_id": "HU", "country_code": "+36" },
            { "country_name": "Iceland", "country_id": "IS", "country_code": "+354" },
            { "country_name": "India", "country_id": "IN", "country_code": "+91" },
            { "country_name": "Indonesia", "country_id": "ID", "country_code": "+62" },
            { "country_name": "Iran", "country_id": "IR", "country_code": "+98" },
            { "country_name": "Iraq", "country_id": "IQ", "country_code": "+964" },
            { "country_name": "Ireland", "country_id": "IE", "country_code": "+353" },
            { "country_name": "Israel", "country_id": "IL", "country_code": "+972" },
            { "country_name": "Italy", "country_id": "IT", "country_code": "+39" },
            { "country_name": "Japan", "country_id": "JP", "country_code": "+81" },
            { "country_name": "Kenya", "country_id": "KE", "country_code": "+254" },
            { "country_name": "Kuwait", "country_id": "KW", "country_code": "+965" },
            { "country_name": "Malaysia", "country_id": "MY", "country_code": "+60" },
            { "country_name": "Mexico", "country_id": "MX", "country_code": "+52" },
            { "country_name": "Nepal", "country_id": "NP", "country_code": "+977" },
            { "country_name": "Netherlands", "country_id": "NL", "country_code": "+31" },
            { "country_name": "New Zealand", "country_id": "NZ", "country_code": "+64" },
            { "country_name": "Nigeria", "country_id": "NG", "country_code": "+234" },
            { "country_name": "Norway", "country_id": "NO", "country_code": "+47" },
            { "country_name": "Pakistan", "country_id": "PK", "country_code": "+92" },
            { "country_name": "Philippines", "country_id": "PH", "country_code": "+63" },
            { "country_name": "Poland", "country_id": "PL", "country_code": "+48" },
            { "country_name": "Portugal", "country_id": "PT", "country_code": "+351" },
            { "country_name": "Qatar", "country_id": "QA", "country_code": "+974" },
            { "country_name": "Romania", "country_id": "RO", "country_code": "+40" },
            { "country_name": "Russia", "country_id": "RU", "country_code": "+7" },
            { "country_name": "Saudi Arabia", "country_id": "SA", "country_code": "+966" },
            { "country_name": "Singapore", "country_id": "SG", "country_code": "+65" },
            { "country_name": "South Africa", "country_id": "ZA", "country_code": "+27" },
            { "country_name": "South Korea", "country_id": "KR", "country_code": "+82" },
            { "country_name": "Spain", "country_id": "ES", "country_code": "+34" },
            { "country_name": "Sri Lanka", "country_id": "LK", "country_code": "+94" },
            { "country_name": "Sweden", "country_id": "SE", "country_code": "+46" },
            { "country_name": "Switzerland", "country_id": "CH", "country_code": "+41" },
            { "country_name": "Thailand", "country_id": "TH", "country_code": "+66" },
            { "country_name": "Turkey", "country_id": "TR", "country_code": "+90" },
            { "country_name": "Ukraine", "country_id": "UA", "country_code": "+380" },
            { "country_name": "United Arab Emirates", "country_id": "AE", "country_code": "+971" },
            { "country_name": "United Kingdom", "country_id": "GB", "country_code": "+44" },
            { "country_name": "United States", "country_id": "US", "country_code": "+1" },
            { "country_name": "Vietnam", "country_id": "VN", "country_code": "+84" },
            { "country_name": "Zimbabwe", "country_id": "ZW", "country_code": "+263" }
        ]';

	}
}
