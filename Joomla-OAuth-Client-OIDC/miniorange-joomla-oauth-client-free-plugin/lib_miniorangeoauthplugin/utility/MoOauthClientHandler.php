<?php
/**
 * @package    Joomla.Plugin
 * @subpackage lib_miniorangeoauthplugin
 *
 * @author    miniOrange Security Software Pvt. Ltd.
 * @copyright Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license   GNU General Public License version 3; see LICENSE.txt
 * @contact   info@xecurify.com
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\CMS\User\User;

require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_oauth_utility.php';
require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_oauth_logger.php';
require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php';

class MoOauthClientHandler
{
	/**
	 * @var string
	 */
	private $attributesNames = '';

	public static function miniOauthFetchDb($tableName,$condition,$method='loadAssoc',$columns='*')
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$columns = is_array($columns) ? $db->quoteName($columns) : $columns;
		$query->select($columns);
		$query->from($db->quoteName($tableName));

		foreach ($condition as $key => $value)
		{
			$query->where($db->quoteName($key) . " = " . $db->quote($value));
		}

		$db->setQuery($query);

		if ($method == 'loadColumn')
		{
			return $db->loadColumn();
		}
		elseif ($method == 'loadObjectList')
		{
			return $db->loadObjectList();
		}
		elseif ($method == 'loadResult')
		{
			return $db->loadResult();
		}
		elseif ($method == 'loadRow')
		{
			return $db->loadRow();
		}
		else
		{
			return $db->loadAssoc();
		}
	}

	public static function miniOauthUpdateDb($tableName, $data, $condition)
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$query->update($db->quoteName($tableName));

		foreach ($data as $key => $value)
		{
			$query->set($db->quoteName($key) . ' = ' . $db->quote($value));
		}

		foreach ($condition as $key => $value)
		{
			$query->where($db->quoteName($key) . ' = ' . $db->quote($value));
		}

		$db->setQuery($query);

		return $db->execute();
	}

	public function handleOAuthRequest($params)
	{
		$app = Factory::getApplication();
		$session = Factory::getSession();

		$lang = $app->getLanguage();
		$lang->load(
			'lib_miniorangeoauthplugin',
			JPATH_ROOT
		);

		$versionObj = new Version;
		$version = $versionObj->getShortVersion();

		$redirectUrlByVersion = "";

		if (version_compare($version, '4.0.0', '>='))
		{
			$redirectUrlByVersion = "api/index.php/v1/miniorangeoauth";
		}

		if (isset($params['morequest']) && $params['morequest'] == 'testattrmappingconfig')
		{
			$moOauthAppName = isset($params['app']) ? (string) $params['app'] : '';
			$testExpires = isset($params['test_expires']) ? (int) $params['test_expires'] : 0;
			$testToken = isset($params['test_token']) ? (string) $params['test_token'] : '';

			if (!MoOAuthUtility::verifyOAuthTestSignature($moOauthAppName, $testExpires, $testToken)) {
				MoOAuthLogger::addLog('Unauthorized OAuth test request', 'WARNING');
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			$testUrl = Uri::root() . $redirectUrlByVersion
				. '?morequest=oauthredirect&app_name=' . rawurlencode($moOauthAppName)
				. '&test=true&test_expires=' . $testExpires
				. '&test_token=' . rawurlencode($testToken);
			$app->redirect(Route::_($testUrl));
		}
		elseif (isset($params['morequest']) && $params['morequest'] == 'oauthredirect')
		{
			/*
			-------------------------OAuth SSO starts with this if-----------
			Opening of OAuth server dialog box
			Step 1 of Oauth/OpenID flow
			*/
			$appname = isset($params['app_name']) ? (string) $params['app_name'] : '';
			$isTestRequest = isset($params['test']);
			$testExpires = isset($params['test_expires']) ? (int) $params['test_expires'] : 0;
			$testToken = isset($params['test_token']) ? (string) $params['test_token'] : '';

			if (
				$isTestRequest
				&& !MoOAuthUtility::verifyOAuthTestSignature($appname, $testExpires, $testToken)
			) {
				MoOAuthLogger::addLog('Unauthorized OAuth test redirect', 'WARNING');
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			if ($isTestRequest) {
				$testCookieOptions = MoOauthUtility::getSecureCookieOptions($testExpires);
				setcookie('mo_oauth_test', '1', $testCookieOptions);
				setcookie('mo_oauth_test_app', $appname, $testCookieOptions);
				setcookie('mo_oauth_test_exp', (string) $testExpires, $testCookieOptions);
				setcookie('mo_oauth_test_token', $testToken, $testCookieOptions);
			} else {
				$expiredCookieOptions = MoOauthUtility::getSecureCookieOptions(time() - 300);
				setcookie('mo_oauth_test', '', $expiredCookieOptions);
				setcookie('mo_oauth_test_app', '', $expiredCookieOptions);
				setcookie('mo_oauth_test_exp', '', $expiredCookieOptions);
				setcookie('mo_oauth_test_token', '', $expiredCookieOptions);
			}

			// Save same-site referrer in cookie so we can return after SSO (validated; never trust raw input).
			if (method_exists($app, 'getInput'))
			{
				$oauthInput = $app->getInput();
			}
			else
			{
				$oauthInput = $app->input;
			}

			$referer = $oauthInput->server->getString('HTTP_REFERER', '');
			$safeReturnUrl = MoOAuthUtility::getSafePostLoginRedirectUrl($referer, '');

			if ($safeReturnUrl !== '')
			{
				setcookie('returnurl', $safeReturnUrl, MoOauthUtility::getSecureCookieOptions());
			}

			// Get Ouath configuration from database

			$appdata = self::miniOauthFetchDb('#__miniorange_oauth_config', array('custom_app' => $appname));

			if (session_id() == '' || !isset($session))
			{
				session_start();
			}

			$session->set('appname', $appname);

			if (is_null($appdata))
			{
				$appdata = self::miniOauthFetchDb('#__miniorange_oauth_config', array('appname' => $appname));
			}

			if (empty($appdata['client_id']) || empty($appdata['app_scope']))
			{
				echo "<center><h3 style='color:indianred;border:1px dotted black;'>[MOOAUTH-001] : " . Text::_('LIB_MINIORANGEOAUTH_CLIENT_ID_MISSING') . "</h3></center>";
				MoOAuthLogger::addLog('Client ID, Client secret or scope is missing', 'ERROR');
				exit;
			}

			if ($appdata['sso_enable'] == 0 && !$isTestRequest)
			{
				$errMessage = "[MOOAUTH-002] : " . Text::_('LIB_MINIORANGEOAUTH_SSO_DISABLE_WARNING');
				$app->enqueueMessage($errMessage, 'error');
				MoOAuthLogger::addLog('SSO is Disable', 'WARNING');
				$app->redirect(Uri::root());
			}

			$state = $this->createOAuthState($appname);
			$authorizationUrl = $appdata['authorize_endpoint'];

			if (strpos($authorizationUrl, '?') !== false)
			{
				$authorizationUrl = $authorizationUrl . "&client_id=" . $appdata['client_id'] . "&scope=" . $appdata['app_scope'] . "&redirect_uri=" . Uri::root() . $redirectUrlByVersion . "&response_type=code&state=" . $state;
			}
			else
			{
				$authorizationUrl = $authorizationUrl . "?client_id=" . $appdata['client_id'] . "&scope=" . $appdata['app_scope'] . "&redirect_uri=" . Uri::root() . $redirectUrlByVersion . "&response_type=code&state=" . $state;
			}

			$session->set('oauth2state', $state);
			setcookie(
				'mo_oauth_state',
				$state,
				MoOauthUtility::getSecureCookieOptions(time() + 3600)
			);

			header('Location: ' . $authorizationUrl);
			exit;
		}
		elseif (isset($params['code']))
		{
			/*
			*   Step 2 of OAuth Flow starts. We got the code
			*
			*/

			if (session_id() == '' || !isset($session))
			{
				session_start();
			}

			try
			{
				$input = method_exists($app, 'getInput') ? $app->getInput() : $app->input;
				$providedState = isset($params['state']) ? (string) $params['state'] : '';
				$sessionState = (string) $session->get('oauth2state', '');
				$cookieState = (string) $input->cookie->get('mo_oauth_state', '');
				$stateMatchesSession = $sessionState !== '' && hash_equals($sessionState, $providedState);
				$stateMatchesCookie = $cookieState !== '' && hash_equals($cookieState, $providedState);

				$session->clear('oauth2state');
				setcookie(
					'mo_oauth_state',
					'',
					MoOauthUtility::getSecureCookieOptions(time() - 300)
				);

				if ($providedState === '' || (!$stateMatchesSession && !$stateMatchesCookie)) {
					MoOAuthLogger::addLog('OAuth state validation failed', 'WARNING');
					exit('[MOOAUTH-003] : ' . Text::_('LIB_MINIORANGEOAUTH_NO_REQUEST_FOUND'));
				}

				// Use the session app when available and the validated state on stateless API callbacks.
				$currentappname = '';
				$sessionVar = $session->get('appname');

				if (isset($sessionVar) && !empty($sessionVar)) {
					$currentappname = $session->get('appname');
				} else {
					$currentappname = $this->getAppNameFromState($providedState);
				}

				if (empty($currentappname)) {
					MoOAuthLogger::addLog('No request found for this application', 'ERROR');
					exit('[MOOAUTH-003] : ' . Text::_('LIB_MINIORANGEOAUTH_NO_REQUEST_FOUND'));
				}

				// Get OAuth configuration
				$appname = $session->get('appname');

				if ($appname == null || $appname == '')
				{
					$appname = $currentappname;
				}

				$nameAttr = '';
				$emailAttr = '';
				$appdata = self::miniOauthFetchDb('#__miniorange_oauth_config', array('custom_app' => $appname));

				if (is_null($appdata))
				{
					$appdata = self::miniOauthFetchDb('#__miniorange_oauth_config', array('appname' => $appname));
				}

				if ($appdata['userslim'] < $appdata['usrlmt'])
				{
					$userslimitexeed = 0;
				}
				else
				{
					$userslimitexeed = 1;
				}

				$currentapp = $appdata;

				if (isset($appdata['email_attr']))
				{
					$emailAttr = $appdata['email_attr'];
				}

				if (isset($appdata['username_attr']))
				{
					$nameAttr = $appdata['username_attr'];
				}

				if (!$currentapp)
				{
					MoOAuthLogger::addLog('Application not configured', 'WARNING');
					exit("[MOOAUTH-004] : " . Text::_('LIB_MINIORANGEOAUTH_APPLICATION_NOT_CONFIGURED'));
				}

				$authBase = JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth';
				include_once $authBase . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'oauth_handler.php';

				$moOauthHandler = new Mo_OAuth_Hanlder;

				/*
				 * Make a back channel request for access token
				 * we may also get an ID token in openid flow
				 *
				 */
				list($accessToken,$idToken) = $moOauthHandler->getAccessToken(
					$currentapp['access_token_endpoint'], 'authorization_code',
					$currentapp['client_id'], $currentapp['client_secret'], $params['code'], Uri::root() . $redirectUrlByVersion, $currentapp['in_header_or_body']
				);

				$moOauthHandler->printError();

				/*
				 * If access token is valid then call userInfo endpoint to get user info or resource  owner details or extract from Id-token
				 */
				$resourceownerdetailsurl = $currentapp['user_info_endpoint'];

				if (substr($resourceownerdetailsurl, -1) == "=")
				{
					$resourceownerdetailsurl .= $accessToken;
				}

				$resourceOwner = $moOauthHandler->getResourceOwner($resourceownerdetailsurl, $accessToken, $idToken);
				$moOauthHandler->printError();
				list($email,$username) = $this->getEmailAndName($resourceOwner, $emailAttr, $nameAttr);
				$checkUser = $this->getUserFromJoomla($email, $username);

				// Efficiency of the plugin
				$ssoEff = self::miniOauthFetchDb('#__miniorange_oauth_customer', array('id' => '1'));

				$fields = array(
					'dno_ssos' => $ssoEff['dno_ssos'] + 1,
					'sso_var' => base64_encode(25),
				);
				$conditions = array(
				   'id' => '1'
				);
				self::miniOauthUpdateDb('#__miniorange_oauth_customer', $fields, $conditions);
				$thrs = 85400;

				if ($ssoEff['previous_update'] == '' || time() > $ssoEff['previous_update'] + $thrs)
				{
					$tnoSsos = $ssoEff['tno_ssos'] + $ssoEff['dno_ssos'];
					$fields = array(
						'previous_update' => time(),
						'dno_ssos' => 1,
						'tno_ssos' => $tnoSsos,
					);

					$baseUrl = Uri::root();
					$appname = '';
					$cTime = date('m/d/Y H:i:s', $ssoEff['cd_plugin']);
					$presentUpdate = date('m/d/Y H:i:s', time());
					$previousUpdate = date('m/d/Y H:i:s', intval($ssoEff['previous_update']));
					$dnoSsos = $ssoEff['dno_ssos'];
				}

				if ($checkUser)
				{
					$result = self::miniOauthFetchDb('#__miniorange_oauth_customer', array('id' => '1'));
					$test = base64_decode($result['sso_var']);
					$test2 = base64_decode($result['sso_test']);
					$appname = '';
					$baseUrl = Uri::root();
					$cTime = date('m/d/Y H:i:s', $result['cd_plugin']);
					$presentUpdate = date('m/d/Y H:i:s', time());
					$previousUpdate = date('m/d/Y H:i:s', intval($result['previous_update']));
					$dnoSsos = $result['dno_ssos'];
					$tnoSsos = $result['tno_ssos'];

					if ((int) $test2 >= (int) $test)
					{
						$moOauthHandler->showFormattedErrorMessage(Text::_('LIB_MINIORANGEOAUTH_AUTHENTICATION_LIMIT_REACHED'));
						MoOAuthLogger::addLog('Authentication limit reached', 'INFO');
						exit;
					}

					$this->loginCurrentUser($checkUser, $username, $email);
				}
				else
				{
					// Free plan does not create accounts. Existing users can still sign in with SSO.
					MoOAuthLogger::addLog('OAuth login user not found', 'INFO');
					$this->showFreePlanUserCreationNotice();
				}
			}
			catch (Exception $e)
			{
				MoOAuthLogger::addLog('Exception : ' . $e, 'CRITICAL', 'MOOAUTH-A01');
				exit("[MOOAUTH-A01] : " . $e->getMessage());
			}
		}
	}

	public function getEmailAndName($resourceOwner, $emailAttr, $nameAttr)
	{
		$app = Factory::getApplication();
		$lang = $app->getLanguage();
		$currentLang = $app->getLanguage()->getTag();

		$lang->load(
			'lib_miniorangeoauthplugin',
			JPATH_ROOT,
			$currentLang,
			true,
			false
		);

		// TEST Configuration

		$session = Factory::getSession();
		$resultAttr = self::miniOauthFetchDb('#__miniorange_oauth_config', array('id' => '1'));
		$resultCustomer = self::miniOauthFetchDb('#__miniorange_oauth_customer', array('id' => '1'));
		$siteUrl = Uri::root();
		$siteUrl = $siteUrl . '/administrator/components/com_miniorange_oauth/assets/images/';

		$email = isset($resourceOwner['email']) ? $resourceOwner['email'] : 'there';

		$app = Factory::getApplication();

		if (method_exists($app, 'getInput'))
		{
			$input = $app->getInput();
		}
		else
		{
			// Joomla 3
			$input = $app->input;
		}

		$testCookie = $input->cookie->get('mo_oauth_test');

		if ((string) $testCookie === '1') {
			$testApp = (string) $input->cookie->get('mo_oauth_test_app', '');
			$testExpires = (int) $input->cookie->get('mo_oauth_test_exp', 0);
			$testToken = (string) $input->cookie->get('mo_oauth_test_token', '');

			if (!MoOAuthUtility::verifyOAuthTestSignature($testApp, $testExpires, $testToken)) {
				MoOAuthUtility::clearOAuthFlowCookies();
				MoOAuthLogger::addLog('OAuth test authorization expired or invalid', 'WARNING');
				throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			MoOAuthUtility::clearOAuthFlowCookies();
			$helloEmail = $email;

			if (!is_scalar($helloEmail) && $helloEmail !== null)
			{
				$helloEmail = '';
			}

			$helloEmail = htmlspecialchars((string) $helloEmail, ENT_QUOTES, 'UTF-8');
			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #3c763d;background-color: #dff0d8; padding:2%;margin-bottom:20px;text-align:center; border:1px solid #AEDB9A; font-size:18pt;">TEST SUCCESSFUL</div>
                <div style="display:block;text-align:center;margin-bottom:4%;"><img style="width:15%;"src="' . $siteUrl . 'green_check.png"></div><br>
                <span style="font-size:14pt;"><b>Hello, ' . $helloEmail . '</b>,<br/> </span><br/>
                <table style="border-collapse:collapse;border-spacing:0; table-layout:fixed; display:table;width:100%; font-size:14pt;background-color:#EDEDED;">
                <tr style="text-align:center;"><td style="font-weight:bold;border:2px solid #949090;padding:2%;">ATTRIBUTE NAME</td><td style="font-weight:bold;padding:2%;border:2px solid #949090; word-wrap:break-word;">ATTRIBUTE VALUE</td></tr>';

			echo '<div style="background:#EDEDED;padding:5px;">
                <p style="color:red;"><b><u>Next Steps :</u></b></p>
                <p>' . Text::_('LIB_MINIORANGEOAUTH_ATTRIBUTE_CONFIG_MISSING_MSG') . '</p>
                </div>
                <p style="font-weight:bold;font-size:14pt;margin-left:1%;">ATTRIBUTES RECEIVED:</p><br>';
			self::testattrmappingconfig("", $resourceOwner);
			echo "</table> <br><br>";
			$userAttributes = $this->attributesNames;

			$appname = isset($resultAttr['appname']) ? $resultAttr['appname'] : '';

			self::miniOauthUpdateDb('#__miniorange_oauth_config', array('test_attribute_name' => $userAttributes), array("id" => 1));
			$refreshUrl = Uri::root() . "administrator/index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp=" . $resultAttr['appname'] . "&progress=step3";
			echo "<script>
                if (window.opener) {
                    window.opener.location.href = '" . $refreshUrl . "';
                }";

			exit();
		}

		if (!empty($emailAttr))
		{
			$email = $this->getnestedattribute($resourceOwner, $emailAttr);
		}
		else
		{
			$session->set('mo_reason', 'Login not Allowed.Attibute Mapping is empty. Please configure it');
			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
            <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p>' . Text::_('LIB_MINIORANGEOAUTH_LOGIN_NOT_ALLOWED') . '</p>
            <p><strong>Causes</strong>: ' . Text::_('LIB_MINIORANGEOAUTH_ATTRIBUTE_MAPPING_EMPTY') . '</p>
            </div>';
			$baseUrl = Uri::root();
			echo '<p align="center"><a href="' . $baseUrl . '" style="text-decoration: none; padding:1%;width:100px;background: #0091CD none repeat scroll 0% 0%;cursor: pointer;font-size:15px;border-width: 1px;border-style: solid;border-radius: 3px;white-space: nowrap;box-sizing: border-box;border-color: #0073AA;box-shadow: 0px 1px 0px rgba(120, 200, 230, 0.6) inset;color: #FFF;"type="button">Done</a></p>';
			MoOAuthLogger::addLog('Test Configuration Success', 'INFO');
			exit;
		}

		$name = '';

		if (!empty($nameAttr))
		{
			$name = $this->getnestedattribute($resourceOwner, $nameAttr);
		}

		if (empty($email))
		{
			$homeLink = Uri::root();
			$session->set('mo_reason', 'Email address not received. Check your Attribute Mapping configuration.');
			echo '<div style="font-family:Calibri;padding:0 3%;"><div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                    <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p>' . Text::_('LIB_MINIORANGEOAUTH_EMAIL_ID_NOT_RECIVED') . '</p>
                    <p>' . Text::_('LIB_MINIORANGEOAUTH_EMAIL_ID_MISSING_WARNING') . '</p></div></div><br>';
			$homeLink = Uri::root();
			echo '<p align="center"><a href=' . $homeLink . ' type="button" style="color: white; background: #185b91; padding: 10px 20px;">' . Text::_('LIB_MINIORANGEOAUTH_BACK_TO_WEBSITE') . '</a><p>';
			MoOAuthLogger::addLog('email not received', 'ERROR');
			exit('[MOOAUTH-008] : Email attribute is not received.');
		}

		$name = $this->normalizeLoginUsername($name);

		if ($name === null)
		{
			$session->set('mo_reason', 'Username not received. Check your Attribute Mapping configuration.');
			echo '<div style="font-family:Calibri;padding:0 3%;"><div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;"> ERROR</div>
                    <div style="color: #a94442;font-size:14pt; margin-bottom:20px;"><p>' . Text::_('LIB_MINIORANGEOAUTH_USERNAME_NOT_RECEIVED') . '</p>
                    <p>' . Text::_('LIB_MINIORANGEOAUTH_USERNAME_MISSING_WARNING') . '</p></div></div><br>';
			$homeLink = Uri::root();
			echo '<p align="center"><a href=' . $homeLink . ' type="button" style="color: white; background: #185b91; padding: 10px 20px;">' . Text::_('LIB_MINIORANGEOAUTH_BACK_TO_WEBSITE') . '</a><p>';
			MoOAuthLogger::addLog('username not received', 'ERROR');
			exit('[MOOAUTH-009] : Username attribute is not received.');
		}

		return array($email, $name);
	}

	public function testattrmappingconfig($nestedprefix, $resourceOwnerDetails)
	{
		if (!empty($nestedprefix))
		{
			$nestedprefix .= ".";
		}

		foreach ($resourceOwnerDetails as $key => $resource)
		{
			if (is_array($resource) || is_object($resource))
			{
				$this->testattrmappingconfig($nestedprefix . $key, $resource);
			}
			else
			{
				echo "<tr><td style='font-weight:bold;border:2px solid #949090;padding:2%;'>";

				if (!empty($nestedprefix))
				{
					echo htmlspecialchars((string) $nestedprefix, ENT_QUOTES, 'UTF-8');
				}

				echo htmlspecialchars((string) $key, ENT_QUOTES, 'UTF-8') . "</td><td style='padding:2%;border:2px solid #949090; word-wrap:break-word;'>" . htmlspecialchars((string) $resource, ENT_QUOTES, 'UTF-8') . "</td></tr>";
				$this->attributesNames .= $nestedprefix . $key . ',';
			}
		}
	}

	public function getnestedattribute($resource, $key)
	{
		if (trim($key) == "")
		{
			return "";
		}

		$keys = explode(".", $key);

		if (count($keys) > 1)
		{
			$currentKey = $keys[0];

			if (isset($resource[$currentKey]))
			{
				return $this->getnestedattribute($resource[$currentKey], str_replace($currentKey . ".", "", $key));
			}
		}
		else
		{
			$currentKey = $keys[0];

			if (isset($resource[$currentKey]))
			{
				return $resource[$currentKey];
			}
		}

		return "";
	}

	public function getUserFromJoomla($email, $username)
	{
		$loginUsername = $this->normalizeLoginUsername($username);

		if ($loginUsername === null)
		{
			return null;
		}

		// Find the existing Joomla account by username. $username is the value of the mapped username attribute.
		// quote() is used instead of bind() so Joomla 3, 4, 5, and 6 all run this query.
		$db = self::getDBObject();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('username') . ' = ' . $db->quote($loginUsername));
		$db->setQuery($query, 0, 1);
		$userId = (int) $db->loadResult();

		if ($userId < 1)
		{
			return null;
		}

		return (object) ['id' => $userId];
	}

	private function normalizeLoginUsername($username)
	{
		if (is_bool($username) || !is_scalar($username))
		{
			return null;
		}

		$loginUsername = trim((string) $username);
		// Same characters core strips with the USERNAME input filter before it searches.
		$loginUsername = preg_replace('/[\x00-\x1F\x7F<>"\'%&]/', '', $loginUsername);

		if (!is_string($loginUsername) || $loginUsername === '')
		{
			return null;
		}

		return $loginUsername;
	}

	public function loginCurrentUser($checkUser, $username = '', $email = '')
	{
		$app = Factory::getApplication();
		$lang = $app->getLanguage();
		$lang->load(
			'lib_miniorangeoauthplugin',
			JPATH_ROOT
		);

		$user = User::getInstance($checkUser->id);
		$failure = $this->getOAuthLoginFailure($user);

		if ($failure === 'blocked')
		{
			// Redirect to site login with flag; system plugin will show Joomla message and clean URL
			$loginUrl = Uri::root() . 'index.php?option=com_users&view=login&mo_oauth_blocked=1';
			$app->redirect($loginUrl);

			return;
		}

		if ($failure !== '')
		{
			MoOAuthLogger::addLog('OAuth login ' . $failure, 'INFO');
			$this->redirectOAuthLoginFailure($failure);

			return;
		}

		$session = Factory::getSession();

		// Get current session vars
		// Register the needed session variables
		$session->set('user', $user);

		// $app->checkSession();

		$sessionId = $session->getId();
		$session->set('session_id', $sessionId);
		$this->updateUsernameToSessionId($user->id, $user->username, $sessionId);

		$result = self::miniOauthFetchDb('#__miniorange_oauth_customer', array('id' => 1), 'loadAssoc', '*');
		$test = base64_decode(empty($result['sso_test']) ? base64_encode(0) : $result['sso_test']);

		$ssoTest = (int) $test + 1;
		$ssoTest = base64_encode($ssoTest);
		$ssoVar = base64_encode(25);
		$data = [
			'sso_test' => $ssoTest,
			'sso_var'  => $ssoVar
		];

		$condition = [
			'id' => 1
		];

		self::miniOauthUpdateDb('#__miniorange_oauth_customer', $data, $condition);

		$user->setLastVisit();

		if (!empty($appdata['update_email_on_sso']))
		{
			try
			{
				$this->updateMappedUserEmail((int) $checkUser->id, $email);
			}
			catch (Exception $emailUpdateException)
			{
				MoOAuthLogger::addLog('SSO email update failed: ' . $emailUpdateException->getMessage(), 'WARNING');
			}
		}

		if (method_exists($app, 'getInput'))
		{
			$input = $app->getInput();
		}
		else
		{
			$input = $app->input;
		}

		$cookieData = $input->cookie->getArray();
		$defaultRedirect = MoOAuthUtility::getDefaultPostLoginRedirectUrl();
		$storedReturnUrl = isset($cookieData['returnurl']) ? (string) $cookieData['returnurl'] : '';
		$redirectloginuri = MoOAuthUtility::getSafePostLoginRedirectUrl($storedReturnUrl, $defaultRedirect);

		MoOAuthUtility::clearReturnUrlCookie();

		$bridgeExpires = time() + 300;
		$sessionCookieOptions = MoOauthUtility::getSecureCookieOptions($bridgeExpires);
		$bridgeSignature = MoOauthUtility::buildSsoBridgeSignature($sessionId, $user->id, $bridgeExpires);
		setcookie('mo_site', 'site', $sessionCookieOptions);
		setcookie('session_id', base64_encode($sessionId), $sessionCookieOptions);
		setcookie('user_id', base64_encode($user->id), $sessionCookieOptions);
		setcookie('mo_oauth_exp', (string) $bridgeExpires, $sessionCookieOptions);
		setcookie('mo_oauth_sig', $bridgeSignature, $sessionCookieOptions);

		$app->redirect($redirectloginuri);
	}

	private function getOAuthLoginFailure($user)
	{
		if (empty($user) || empty($user->id))
		{
			return 'nouser';
		}

		if ((int) $user->block === 1)
		{
			return 'blocked';
		}

		if (!$user->authorise('core.login.site'))
		{
			return 'denied';
		}

		return '';
	}

	private function showFreePlanUserCreationNotice()
	{
		MoOAuthUtility::clearOAuthFlowCookies();

		$app = Factory::getApplication();
		$homeUrl = htmlspecialchars(Uri::root(), ENT_QUOTES, 'UTF-8');
		$learnMoreUrl = 'https://plugins.miniorange.com/joomla-single-sign-on-sso-oauth-oidc';
		$upgradeUrl = 'https://plugins.miniorange.com/joomla-single-sign-on-sso-oauth-oidc#pricing';
		$learnMoreUrl = htmlspecialchars($learnMoreUrl, ENT_QUOTES, 'UTF-8');
		$upgradeUrl = htmlspecialchars($upgradeUrl, ENT_QUOTES, 'UTF-8');
		$title = htmlspecialchars(Text::_('LIB_MINIORANGEOAUTH_USER_AUTO_CREATION_NOT_AVAILABLE'), ENT_QUOTES, 'UTF-8');
		$existingUser = htmlspecialchars(Text::_('LIB_MINIORANGEOAUTH_FREE_PLAN_SSO_EXISTING_USER_ONLY'), ENT_QUOTES, 'UTF-8');
		$upgrade = htmlspecialchars(Text::_('LIB_MINIORANGEOAUTH_FREE_PLAN_UPGRADE_MESSAGE'), ENT_QUOTES, 'UTF-8');
		$contact = htmlspecialchars(Text::_('LIB_MINIORANGEOAUTH_FREE_PLAN_CONTACT_ADMIN'), ENT_QUOTES, 'UTF-8');
		$learnMore = htmlspecialchars(Text::_('LIB_MINIORANGEOAUTH_FOR_MORE_INFORMATION'), ENT_QUOTES, 'UTF-8');
		$backLabel = htmlspecialchars(Text::_('LIB_MINIORANGEOAUTH_BACK_TO_WEBSITE'), ENT_QUOTES, 'UTF-8');
		$upgradeLabel = htmlspecialchars(Text::_('LIB_MINIORANGEOAUTH_UPGRADE_PLUGIN'), ENT_QUOTES, 'UTF-8');
		$planLabel = htmlspecialchars(Text::_('LIB_MINIORANGEOAUTH_FREE_PLAN'), ENT_QUOTES, 'UTF-8');

		if (!headers_sent())
		{
			header('Content-Type: text/html; charset=utf-8');
		}

		echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>' . $title . '</title>
<style>
body {
	margin: 0;
	min-height: 100vh;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 24px 16px;
	background: #f4f7fb;
	color: #1f3047;
	font-family: Arial, Helvetica, sans-serif;
}
.mo-notice {
	width: 100%;
	max-width: 640px;
	background: #fff;
	border: 1px solid #d7e2ef;
	border-radius: 12px;
	box-shadow: 0 10px 30px rgba(31, 48, 71, 0.08);
	padding: 32px 28px;
	box-sizing: border-box;
}
.mo-notice__badge {
	display: inline-block;
	margin: 0 0 12px;
	padding: 4px 10px;
	border-radius: 999px;
	background: #e8f2fb;
	color: #185b91;
	font-size: 12px;
	font-weight: bold;
	letter-spacing: 0.04em;
	text-transform: uppercase;
}
.mo-notice h1 {
	margin: 0 0 16px;
	font-size: 24px;
	line-height: 1.3;
}
.mo-notice p {
	margin: 0 0 14px;
	font-size: 16px;
	line-height: 1.6;
}
.mo-notice__contact {
	margin: 18px 0 24px;
	padding: 14px 16px;
	background: #f7fafc;
	border-left: 4px solid #185b91;
	border-radius: 6px;
}
.mo-notice__contact p {
	margin: 0;
}
.mo-notice__actions {
	display: flex;
	flex-wrap: wrap;
	gap: 12px;
	align-items: center;
}
.mo-notice__button {
	display: inline-block;
	padding: 12px 18px;
	border-radius: 6px;
	text-decoration: none;
	font-size: 15px;
	font-weight: bold;
}
.mo-notice__button--primary {
	background: #185b91;
	color: #fff;
}
.mo-notice__button--secondary {
	background: #fff;
	color: #185b91;
	border: 1px solid #185b91;
}
.mo-notice__more {
	margin-top: 18px;
	font-size: 14px;
}
.mo-notice__more a {
	color: #185b91;
}
</style>
</head>
<body>
<div class="mo-notice">
<p class="mo-notice__badge">' . $planLabel . '</p>
<h1>' . $title . '</h1>
<p>' . $existingUser . '</p>
<p>' . $upgrade . '</p>
<div class="mo-notice__contact"><p>' . $contact . '</p></div>
<div class="mo-notice__actions">
<a class="mo-notice__button mo-notice__button--primary" href="' . $homeUrl . '">' . $backLabel . '</a>
<a class="mo-notice__button mo-notice__button--secondary" href="' . $upgradeUrl . '" target="_blank" rel="noopener noreferrer">' . $upgradeLabel . '</a>
</div>
<p class="mo-notice__more"><a href="' . $learnMoreUrl . '" target="_blank" rel="noopener noreferrer">' . $learnMore . '</a></p>
</div>
</body>
</html>';

		$app->close();
	}

	private function redirectOAuthLoginFailure($reason)
	{
		$allowed = ['nouser', 'denied'];

		if (!in_array($reason, $allowed, true))
		{
			$reason = 'nouser';
		}

		$app = Factory::getApplication();
		$loginUrl = Uri::root() . 'index.php?option=com_users&view=login&mo_oauth_login=' . $reason;
		$app->redirect($loginUrl);
	}

	private function updateMappedUserEmail($userId, $email)
	{
		if (!is_scalar($email))
		{
			MoOAuthLogger::addLog('SSO email update skipped because the mapped email is not a single value', 'WARNING');

			return;
		}

		$email = trim((string) $email);

		if (class_exists(PunycodeHelper::class))
		{
			$email = PunycodeHelper::emailToPunycode($email);
		}

		if ($email === '' || strlen($email) > 100 || !MailHelper::isEmailAddress($email))
		{
			MoOAuthLogger::addLog('SSO email update skipped because the mapped email is empty or invalid', 'WARNING');

			return;
		}

		$db = self::getDBObject();
		$userQuery = $db->getQuery(true)
			->select($db->quoteName(['email', 'block', 'activation']))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('id') . ' = ' . (int) $userId);
		$db->setQuery($userQuery, 0, 1);
		$currentUser = $db->loadAssoc();

		if (empty($currentUser) || !isset($currentUser['email']))
		{
			return;
		}

		if (strcasecmp((string) $currentUser['email'], $email) === 0)
		{
			return;
		}

		$duplicateQuery = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('email') . ' = ' . $db->quote($email))
			->where($db->quoteName('id') . ' != ' . (int) $userId);
		$db->setQuery($duplicateQuery, 0, 1);

		if ((int) $db->loadResult() > 0)
		{
			MoOAuthLogger::addLog('SSO email update skipped because the email is already used by another Joomla user', 'WARNING');

			return;
		}

		$updateData = [
			'email' => $email,
		];

		if (empty($currentUser['block']) && !empty($currentUser['activation']))
		{
			$updateData['activation'] = '';
		}

		self::miniOauthUpdateDb('#__users', $updateData, ['id' => (int) $userId]);
		MoOAuthLogger::addLog('Joomla user email updated from the mapped SSO email attribute', 'INFO');
	}

	public function updateCurrentUserNameOrEmail($id, $username, $email)
	{
		if (empty($username) && empty($email))
		{
			return;
		}

		$data = [
			'username' => $username,
			'email' => $email
		];

		$condition = [
			'id' => $id
		];

		$result = self::miniOauthUpdateDb('#__users', $data, $condition);

		return $result;
	}

	public function updateUsernameToSessionId($userID, $username, $sessionId)
	{
		$data = [
		'username' => $username,
		'guest' => '1',
		'userid' => $userID
		];

		$condition = [
		'session_id' => $sessionId
		];

		$result = self::miniOauthUpdateDb('#__session', $data, $condition);

		return $result;
	}

	private function createOAuthState($appName) {
		try {
			$nonce = bin2hex(random_bytes(32));
		} catch (Exception $e) {
			$nonce = hash('sha256', uniqid((string) mt_rand(), true));
		}

		$encodedAppName = rtrim(strtr(base64_encode((string) $appName), '+/', '-_'), '=');

		return $encodedAppName . '.' . $nonce;
	}

	private function getAppNameFromState($state) {
		$stateParts = explode('.', (string) $state, 2);

		if (count($stateParts) !== 2 || $stateParts[0] === '' || $stateParts[1] === '') {
			return '';
		}

		$encodedAppName = strtr($stateParts[0], '-_', '+/');
		$paddingLength = (4 - strlen($encodedAppName) % 4) % 4;
		$decodedAppName = base64_decode(
			$encodedAppName . str_repeat('=', $paddingLength),
			true
		);

		return $decodedAppName === false ? '' : $decodedAppName;
	}

	private static function getDBObject()
	{
		// Application::getDatabase() is protected. The container is Joomla 4+;
		// Factory::getDbo() remains for Joomla 3.
		if (method_exists(Factory::class, 'getContainer'))
		{
			$container = Factory::getContainer();
			$databaseClass = 'Joomla\Database\DatabaseInterface';

			if (class_exists($databaseClass) && $container->has($databaseClass))
			{
				return $container->get($databaseClass);
			}
		}

		return Factory::getDbo();
	}

}
