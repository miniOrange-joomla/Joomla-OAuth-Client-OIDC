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

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
use Joomla\CMS\Router\Route;

jimport('joomla.plugin.plugin');

require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_oauth_utility.php';
require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php';

MoOAuthUtility::loadMoOauthClientHandler();

class PlgSystemMiniorangeoauth extends CMSPlugin
{
	public function onAfterRender()
	{
		$app = Factory::getApplication();
		$body = $app->getBody();
		$tab = 0;
		$tables = MoOAuthUtility::getDBObject()->getTableList();

		foreach ($tables as $table)
		{
			if (strpos($table, "miniorange_oauth_config") !== false)
			{
				$tab = $table;
				break;
			}
		}

		if ($tab == 0)
		{
			return;
		}

		$customerResult = MoOAuthUtility::miniOauthFetchDb('#__miniorange_oauth_config', array('id' => '1'));
		$applicationName = isset($customerResult['appname']) ? $customerResult['appname'] : '';
		$ssoStatus       = isset($customerResult['sso_enable']) ? $customerResult['sso_enable'] : 0;
		$ssoButtonEnable = isset($customerResult['sso_button_enable']) ? $customerResult['sso_button_enable'] : 0;

		$versionObj = new Version;
		$version = $versionObj->getShortVersion();

		$redirectUrlByVersion = "";

		if (version_compare($version, '4.0.0', '>='))
		{
			$redirectUrlByVersion = "api/index.php/v1/miniorangeoauth";
		}

		if ($ssoStatus == 1 && $ssoButtonEnable == 1 && $app->isClient('site'))
		{
			if (stristr($body, 'user.login') && strpos($body, 'morequest=oauthredirect') === false)
			{
				$isJoomla4Plus = version_compare($version, '4.0.0', '>=');
				$btnClass = $isJoomla4Plus ? 'btn btn-primary w-100' : 'btn btn-primary';
				$wrapperClass = $isJoomla4Plus ? 'form-group mt-2' : 'control-group';
				$ssoUrl = Uri::root() . $redirectUrlByVersion . '?morequest=oauthredirect&app_name=' . $applicationName;

				$linkAddPlace = '
					<div class="' . $wrapperClass . '">
						<a href="' . htmlspecialchars($ssoUrl, ENT_QUOTES, 'UTF-8') . '"
						   class="' . $btnClass . '">
						   Login with ' . htmlspecialchars($applicationName, ENT_QUOTES, 'UTF-8') . '
						</a>
					</div>';

				// Match submit blocks across Joomla 3/4/5/6 default login forms (module + component)
				$patterns = [
					// Joomla 4/5/6 mod_login
					'/(<div[^>]*class=["\'][^"\']*mod-login__submit[^"\']*["\'][^>]*>\s*<button[^>]*type=["\']submit["\'][^>]*>.*?<\/button>\s*<\/div>)/is',
					// Joomla 4/5/6 com_users login
					'/(<div[^>]*class=["\'][^"\']*com-users-login__submit[^"\']*["\'][^>]*>\s*<div[^>]*>\s*<button[^>]*type=["\']submit["\'][^>]*>.*?<\/button>\s*<\/div>\s*<\/div>)/is',
					// Joomla 3 mod_login
					'/(<div[^>]*id=["\']form-login-submit["\'][^>]*>\s*<div[^>]*>\s*<button[^>]*type=["\']submit["\'][^>]*>.*?<\/button>\s*<\/div>\s*<\/div>)/is',
					// Joomla 3 com_users login (control-group + controls around submit)
					'/(<div[^>]*class=["\']control-group["\'][^>]*>\s*<div[^>]*class=["\']controls["\'][^>]*>\s*<button[^>]*type=["\']submit["\'][^>]*class=["\'][^"\']*btn-primary[^"\']*["\'][^>]*>.*?<\/button>\s*<\/div>\s*<\/div>)/is',
					// Generic fallback: submit button inside a login form
					'/(<button[^>]*type=["\']submit["\'][^>]*>.*?<\/button>)/is',
					// Legacy: input type=submit
					'/(<input[^>]*type=["\']submit["\'][^>]*\/?>)/is',
				];

				$updatedBody = preg_replace_callback(
					'/<form\b[^>]*>.*?<\/form>/is',
					function ($matches) use ($linkAddPlace, $patterns) {
						$formHtml = $matches[0];

						if (stripos($formHtml, 'user.login') === false)
						{
							return $formHtml;
						}

						foreach ($patterns as $pattern)
						{
							$replaced = preg_replace_callback(
								$pattern,
								function ($m) use ($linkAddPlace) {
									return $m[1] . $linkAddPlace;
								},
								$formHtml,
								1,
								$count
							);

							if ($count > 0)
							{
								return $replaced;
							}
						}

						return $formHtml;
					},
					$body
				);

				if ($updatedBody !== null && $updatedBody !== $body)
				{
					$app->setBody($updatedBody);
				}
			}
		}
	}

	public function onAfterInitialise()
	{
		$app = Factory::getApplication();

		// Get input object
		if (method_exists($app, 'getInput'))
		{
			$input = $app->getInput();
		}
		else
		{
			// Joomla 3
			$input = $app->input;
		}

		// Show Joomla blocked-user message when redirected from OAuth callback (blocked account)
		if ($app->isClient('site') && $input->get('mo_oauth_blocked'))
		{
			$app->enqueueMessage(Text::_('JERROR_NOLOGIN_BLOCKED'), 'error');
			$app->redirect(Route::_('index.php', false));

			return;
		}

		// Get all POST data
		$post = $input->post->getArray();

		$cookie = $input->cookie;

		$lang = $app->getLanguage();

		$lang->load('plg_system_miniorangeoauth', JPATH_ADMINISTRATOR);

		if (isset($post['mojsp_feedback']))
		{
			$radio = !empty($post['deactivate_plugin']) ? $post['deactivate_plugin'] : '';
			$data = !empty($post['query_feedback']) ? $post['query_feedback'] : '';

			if (isset($post['miniorange_feedback_skip']) && $data == '')
			{
				$data = 'Skipped';
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

			$feedbackEmail = !empty($post['feedback_email']) ? $post['feedback_email'] : '';

			$fields = array(
				'uninstall_feedback' => 1
			);
			$conditions = array(
				'id' => '1'
			);

			MoOAuthUtility::miniOauthUpdateDb('#__miniorange_oauth_customer', $fields, $conditions);
			$customerResult = MoOAuthUtility::miniOauthFetchDb('#__miniorange_oauth_customer', array('id' => '1'));
			$adminPhone = isset($customerResult['admin_phone']) ? $customerResult['admin_phone'] : '';
			$data1 = $radio . ' : ' . $data;
			MoOauthCustomer::submitFeedbackForm($feedbackEmail, $adminPhone, $data1);

			if (!empty($post['result']) && is_array($post['result']))
			{
				foreach ($post['result'] as $fbkey)
				{
					$result = MoOAuthUtility::miniOauthFetchDb(
						'#__extensions',
						array('extension_id' => $fbkey),
						'loadColumn',
						'type'
					);
					$type = 0;

					if (is_array($result))
					{
						foreach ($result as $results)
						{
							$type = $results;
						}
					}

					if ($type)
					{
						$cid = 0;
						$db = MoOAuthUtility::getDBObject();

						if (class_exists('Joomla\\CMS\\Installer\\Installer'))
						{
							$installer = new Installer;

							if (method_exists($installer, 'setDatabase'))
							{
								$installer->setDatabase($db);
							}
						}
						else
						{
							jimport('joomla.installer.installer');
							$installer = JInstaller::getInstance();
						}

						$installer->uninstall($type, $fbkey, $cid);
					}
				}
			}
		}

		if ($cookie->get('mo_site', null))
		{
			$rawSessionId = $cookie->get('session_id', '');
			$sessionId = $rawSessionId !== '' ? base64_decode($rawSessionId) : '';

			$rawUserId = $cookie->get('user_id', '');
			$userId = $rawUserId !== '' ? base64_decode($rawUserId) : '';
			$bridgeExpires = (int) $cookie->get('mo_oauth_exp', 0);
			$bridgeSignature = (string) $cookie->get('mo_oauth_sig', '');

			if ($sessionId && $userId)
			{
				$signatureValid = MoOauthUtility::verifySsoBridgeSignature(
					$sessionId,
					$userId,
					$bridgeExpires,
					$bridgeSignature
				);
				$dbSessionRow = null;

				try
				{
					$dbSessionRow = MoOauthUtility::findSsoBridgeSession($sessionId, $userId);
				}
				catch (\Throwable $e)
				{
					$dbSessionRow = null;
				}

				$cookieUserIdMatchesDb = is_array($dbSessionRow)
					&& (string) ($dbSessionRow['userid'] ?? '') === (string) $userId;

				MoOauthUtility::clearSsoBridgeCookies();

				if (!$signatureValid || !$cookieUserIdMatchesDb)
				{
					$app->redirect(Uri::root());

					return;
				}

				$session = Factory::getSession();
				$user = Factory::getUser((int) $userId);

				if ($user && !$user->guest)
				{
					$session->fork();
					$session->set('user', $user);
					$user->setLastVisit();

					// Mark that this login is via OAuth SSO (checked in onUserLogin to send efficiency email)
					$session->set('mo_oauth_sso', true);

					// Load user plugins so Joomla's session/login handlers run (J3–J6+)
					if (class_exists(PluginHelper::class))
					{
						PluginHelper::importPlugin('user');
					}
					elseif (class_exists('JPluginHelper'))
					{
						\JPluginHelper::importPlugin('user');
					}

					// Required by plg_user_joomla::onUserLogin() authorise() check
					$loginAction = $app->isClient('administrator')
						? 'core.login.admin'
						: 'core.login.site';

					$app->triggerEvent('onUserLogin', [
						[
							'username' => $user->username,
							'userid'   => $user->id,
						],
						[
							'action' => $loginAction,
							'silent' => true,
						],
						]
					);

					$app->redirect(Uri::root());

					return;
				}
			}
			else
			{
				MoOauthUtility::clearSsoBridgeCookies();
			}

			$app->redirect(Uri::root());
		}
	}

	public function onUserLogin($first, $second = null)
	{
		$session = Factory::getSession();

		if (!$session->get('mo_oauth_sso'))
		{
			return;
		}

		$session->set('mo_oauth_sso', false);

		$user = null;

		// Joomla 4+: first argument is LoginEvent
		if (is_object($first) && method_exists($first, 'getArgument'))
		{
			$user = Factory::getUser();

			if (!$user || $user->guest)
			{
				return;
			}
		}
		else
		{
			// Joomla 3 / our triggerEvent: credentials and options arrays
			$credentials = is_array($first) ? $first : array();

			if (empty($credentials['userid']))
			{
				return;
			}

			$user = Factory::getUser((int) $credentials['userid']);
		}

		if (!$user || $user->guest)
		{
			return;
		}

		$customerResult = MoOAuthUtility::miniOauthFetchDb('#__miniorange_oauth_customer', array('id' => '1'));
		$baseUrl = Uri::root();
		$cTime = date('m/d/Y H:i:s', $customerResult['cd_plugin'] ?? time());
		$presentUpdate = date('m/d/Y H:i:s', time());
		$previousUpdate = date('m/d/Y H:i:s', $customerResult['previous_update'] ?? time());
		$dnoSsos = $customerResult['dno_ssos'] ?? 0;
		$tnoSsos = $customerResult['tno_ssos'] ?? 0;
		$reason = 'User Successfully login.';

		MoOauthCustomer::pluginEfficiencyCheck(
			$user->email,
			'',
			$baseUrl,
			$cTime,
			$dnoSsos,
			$tnoSsos,
			$previousUpdate,
			$presentUpdate,
			$reason
		);
	}

	public function onExtensionBeforeUninstall($id)
	{
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

		$post = $input->post->getArray();
		$db = MoOAuthUtility::getDBObject();
		$query = $db->getQuery(true);
		$query->select('extension_id');
		$query->from('#__extensions');
		$query->where($db->quoteName('name') . " = " . $db->quote('COM_MINIORANGE_OAUTH'));
		$db->setQuery($query);
		$result = $db->loadColumn();
		$tables = MoOAuthUtility::getDBObject()->getTableList();
		$tab = 0;

		foreach ($tables as $table)
		{
			if (strpos($table, "miniorange_oauth_customer"))
			{
				$tab = $table;
			}
		}

		if ($tab)
		{
			$db = MoOAuthUtility::getDBObject();
			$query = $db->getQuery(true);
			$query->select('uninstall_feedback');
			$query->from('#__miniorange_oauth_customer');
			$query->where($db->quoteName('id') . " = " . $db->quote(1));
			$db->setQuery($query);
			$fid = $db->loadColumn();
			$tpostData = $post;

			foreach ($fid as $value)
			{
				if ($value == 0)
				{
					foreach ($result as $results)
					{
						if ($results == $id)
						{
							?>
							<div class="form-style-6 " id="form-style-6" style="display: block;">
								<h1 class="feedback-title">
									<?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_TITLE'); ?>

									<button type="submit"
											name="miniorange_feedback_skip"
											class="close-x"
											form="mojsp_feedback"
											formnovalidate
											title="<?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_SKIP_BUTTON'); ?>">
										✕
									</button>
								</h1>
								<h3> <?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED'); ?> </h3>
								<form name="f" method="post" action="" id="mojsp_feedback">
									<input type="hidden" name="mojsp_feedback" value="mojsp_feedback"/>
									<div>
										<p style="margin-left:2%">
										<?php
										$deactivateReasons = array(
											Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_1'),
											Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_2'),
											Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_4'),
											Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_5'),
											Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_7'),
											Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_8'),
											Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_9')
										);

										echo $this->buildDeactivateReasonOptions($deactivateReasons);
										?>
										<br>
										<textarea id="query_feedback" name="query_feedback" rows="4"
												  style="margin-left:2%"
												  cols="50" minlength="20" placeholder="<?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_QUERY_PLACEHOLDER'); ?>"></textarea><br><br><br>
										<tr>
								<td width="20%"><b> <?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_EMAIL'); ?> <span style="color: #ff0000;">*</span>:</b></td>
								<td><input type="email" name="feedback_email" required placeholder="<?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_EMAIL_PLACEHOLDER'); ?>" style="width:55%"/></td>
									   </tr>
											<?php
											echo $this->buildHiddenCidInputs($tpostData['cid']);
											?>
										<br><br>
										<div class="mojsp_modal-footer">
											<input type="submit" name="miniorange_feedback_submit"
												   class="button button-primary button-large" value="<?php echo Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_SUBMIT_BUTTON'); ?>"/>
										</div>
										<br>
									</div>
								</form>
							</div>
							<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
							<script>
								jQuery('input:radio[name="deactivate_plugin"]').click(function () {
									var reason = jQuery(this).val();
									jQuery('#query_feedback').removeAttr('required');
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
								.feedback-title {
									position: relative;
								}
								.close-x {
									position: absolute;
									top: 50%;
									right: 15px;
									transform: translateY(-50%);
									background: transparent;
									border: none;
									color: #fff;
									font-size: 22px;
									font-weight: bold;
									cursor: pointer;
									padding: 0;
								}
								.close-x:hover {
									color: #ffdddd;
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

		if (method_exists($app, 'getInput'))
		{
			$input = $app->getInput();
		}
		else
		{
			// Joomla 3
			$input = $app->input;
		}

		$get = $input->get->getArray();

		if (!MoOAuthUtility::loadMoOauthClientHandler())
		{
			return;
		}

		$moOauthClientHandler = new MoOauthClientHandler;

		if (isset($get['morequest']) && $get['morequest'] == 'testattrmappingconfig')
		{
			$moOauthClientHandler->handleOAuthRequest($get);
		}
		elseif (isset($get['morequest']) && $get['morequest'] == 'oauthredirect')
		{
			$moOauthClientHandler->handleOAuthRequest($get);
		}
		elseif (isset($get['code']))
		{
			$moOauthClientHandler->handleOAuthRequest($get);
		}
	}

	private function buildDeactivateReasonOptions(array $deactivateReasons): string
	{
		$markup = '';

		foreach ($deactivateReasons as $deactivateReason)
		{
			$markup .= '<div class=" radio " style="padding:1px;margin-left:2%;cursor:pointer">
			<label style="font-weight:normal;font-size:14.6px"
				   for="' . $deactivateReason . '">
				<input type="radio" name="deactivate_plugin"
					   value="' . $deactivateReason . '" required>
				' . $deactivateReason . '</label>
		</div>';
		}

		return $markup;
	}

	private function buildHiddenCidInputs(array $cids): string
	{
		$markup = '';

		foreach ($cids as $key)
		{
			$markup .= '<input type="hidden" name="result[]" value="' . $key . '">';
		}

		return $markup;
	}
}
