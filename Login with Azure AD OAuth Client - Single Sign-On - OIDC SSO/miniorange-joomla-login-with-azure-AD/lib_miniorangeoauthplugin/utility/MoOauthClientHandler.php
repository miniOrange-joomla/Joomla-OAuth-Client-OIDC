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
use Joomla\CMS\Router\Route;
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
					$conditions = array('id' => '1');
					$result = self::miniOauthUpdateDb('#__miniorange_oauth_customer', $fields, $conditions);
					$dVar = new JConfig;
					$checkEmail = $dVar->mailfrom;

					if (isset($ssoEff['contact_admin_emiail']) && $ssoEff['contact_admin_emiail'] != null)
					{
						$checkEmail = $ssoEff['contact_admin_emiail'];
					}

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
					include_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php';
					$dVar = new JConfig;
					$checkEmail = $dVar->mailfrom;

					if (isset($ssoEff['contact_admin_emiail']) && $ssoEff['contact_admin_emiail'] != null)
					{
							$checkEmail = $ssoEff['contact_admin_emiail'];
					}

					$baseUrl = Uri::root();
					$appname = '';
					$cTime = date('m/d/Y H:i:s', $ssoEff['cd_plugin']);
					$presentUpdate = date('m/d/Y H:i:s', time());
					$previousUpdate = date('m/d/Y H:i:s', intval($ssoEff['previous_update']));
					$dnoSsos = $ssoEff['dno_ssos'];
					$reason = "Can't create new user - " . $session->get('mo_reason');
					echo '<div style="font-family: Calibri, sans-serif; padding: 2% 5%; background-color: #f0f4f8; border: 1px solid #2E486B; border-radius: 8px; max-width: 800px; margin: 30px auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <div style="color: #ffffff; background-color: #1F3047; padding: 20px; font-size: 22px; text-align: center; font-weight: bold; border-radius: 5px; border-bottom: 1px solid #2E486B;">
                                ' . Text::_('LIB_MINIORANGEOAUTH_USER_AUTO_CREATION_NOT_AVAILABLE') . '
                            </div>
                            <div style="color: #1F3047; font-size: 16px; line-height: 1.6; padding: 20px;">
                                ' . Text::_('LIB_MINIORANGEOAUTH_USER_AUTO_CREATION_NOT_AVAILABLE_REASON_CAUSE_SOLUTION') . '
                            </div>
                            <div style="text-align:center; margin-top: 10px;">
                                <a href="https://plugins.miniorange.com/joomla-single-sign-on-sso-oauth-oidc#pricing"
                                   style="background-color: #2E486B; color: white; padding: 12px 25px; font-size: 16px; text-decoration: none; border-radius: 5px;" target="_blank">
                                   ' . Text::_('LIB_MINIORANGEOAUTH_UPGRADE_PLUGIN') . '
                                </a>
                            </div>
                        </div>
                        <br>';

					$homeLink = Uri::root();
					echo '<div style="text-align:center; margin-top: 20px;">
                            <a href="' . $homeLink . '"
                               style="background-color: #1F3047; color: white; padding: 10px 20px; font-size: 16px; text-decoration: none; border-radius: 5px;">
                               ' . Text::_('LIB_MINIORANGEOAUTH_BACK_TO_WEBSITE') . '
                            </a>
                          </div>';

					MoOAuthLogger::addLog('Auto creation not available', 'INFO');
					exit;
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
			echo '<div style="font-family:Calibri;padding:0 3%;">';
			echo '<div style="color: #3c763d;background-color: #dff0d8; padding:2%;margin-bottom:20px;text-align:center; border:1px solid #AEDB9A; font-size:18pt;">TEST SUCCESSFUL</div>
                <div style="display:block;text-align:center;margin-bottom:4%;"><img style="width:15%;"src="' . $siteUrl . 'green_check.png"></div><br>
                <span style="font-size:14pt;"><b>Hello, ' . $email . '</b>,<br/> </span><br/>
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

			$dVar = new JConfig;
			$checkEmail = $dVar->mailfrom;

			if (isset($resultCustomer['contact_admin_email']) && $resultCustomer['contact_admin_email'] != null)
			{
				$checkEmail = $resultCustomer['contact_admin_email'];
			}

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

		return array($email,$name);
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
					echo $nestedprefix;
				}

				echo $key . "</td><td style='padding:2%;border:2px solid #949090; word-wrap:break-word;'>" . $resource . "</td></tr>";
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
		if (empty($email)) {
			return null;
		}

		// Email is the stable OAuth identity; username matching can select another account.
		$db = self::getDBObject();
		$query = $db->getQuery(true)
			->select('id')
			->from('#__users')
			->where($db->quoteName('email') . ' = ' . $db->quote($email));
		$db->setQuery($query);
		$checkUser = $db->loadObject();

		return $checkUser;
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

		if ($user->block == 1)
		{
			// Redirect to site login with flag; system plugin will show Joomla message and clean URL
			$loginUrl = Uri::root() . 'index.php?option=com_users&view=login&mo_oauth_blocked=1';
			$app->redirect($loginUrl);

			return;
		}

		$this->updateCurrentUserNameOrEmail($user->id, $username, $email);
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
		$app = Factory::getApplication();

		if (method_exists($app, 'getDatabase'))
		{
			return $app->getDatabase();
		}

		return Factory::getDbo();
	}

}
