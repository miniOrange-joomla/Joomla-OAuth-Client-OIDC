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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\HTML\HTMLHelper;

if (function_exists('jimport'))
{
	jimport('joomla.plugin.plugin');
}

$moOauthHelperPath = JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR;

// The component can be removed while this plugin is still installed, so its helpers must never be hard requirements.
if (is_file($moOauthHelperPath . 'mo_oauth_utility.php'))
{
	require_once $moOauthHelperPath . 'mo_oauth_utility.php';
}

if (is_file($moOauthHelperPath . 'mo_customer_setup.php'))
{
	require_once $moOauthHelperPath . 'mo_customer_setup.php';
}

unset($moOauthHelperPath);

if (class_exists('MoOAuthUtility', false))
{
	MoOAuthUtility::loadMoOauthClientHandler();
}

class PlgSystemMiniorangeoauth extends CMSPlugin
{
	private static $uninstallFeedbackHandled = false;

	public function onAfterRender()
	{
		if (!$this->hasComponentHelpers())
		{
			return;
		}

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
		if (!$this->hasComponentHelpers())
		{
			return;
		}

		$app = Factory::getApplication();
		$input = $this->getAppInput($app);

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

		$lang = method_exists($app, 'getLanguage') ? $app->getLanguage() : Factory::getLanguage();

		$lang->load('plg_system_miniorangeoauth', JPATH_ADMINISTRATOR);

		if (isset($post['mojsp_feedback']))
		{
			self::$uninstallFeedbackHandled = true;
			$this->processUninstallFeedback($app, $post);
		}
		elseif ($this->interceptUninstallRequest($app, $input))
		{
			return;
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
		if (!$this->hasComponentHelpers())
		{
			return;
		}

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
	}

	public function onAfterRoute()
	{
		if (!$this->hasComponentHelpers())
		{
			return;
		}

		$app = Factory::getApplication();
		$input = $this->getAppInput($app);

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

	private function hasComponentHelpers(): bool
	{
		return class_exists('MoOAuthUtility', false);
	}

	private function getAppInput($app)
	{
		if (method_exists($app, 'getInput'))
		{
			return $app->getInput();
		}

		// Joomla 3
		return $app->input;
	}

	/**
	 * Catches the com_installer removal request before Joomla touches any extension. Hooking
	 * onExtensionBeforeUninstall instead would stop the installer half way through a package
	 * removal and leave the site with orphaned extensions.
	 */
	private function interceptUninstallRequest($app, $input): bool
	{
		if (self::$uninstallFeedbackHandled || !$app->isClient('administrator'))
		{
			return false;
		}

		if (strtoupper((string) $input->getMethod()) !== 'POST')
		{
			return false;
		}

		if ($input->getCmd('option', '') !== 'com_installer')
		{
			return false;
		}

		if (strpos((string) $input->post->getCmd('task', ''), 'manage.remove') !== 0)
		{
			return false;
		}

		$cids = $this->normaliseExtensionIds($input->post->get('cid', [], 'array'));

		if ($cids === [] || !$this->canManageInstallerUninstall($app) || !Session::checkToken('post'))
		{
			return false;
		}

		if (array_intersect($cids, $this->getMiniOrangeExtensionIds()) === [])
		{
			return false;
		}

		if ($this->isUninstallFeedbackGiven())
		{
			return false;
		}

		self::$uninstallFeedbackHandled = true;
		$this->renderUninstallFeedbackForm($app, $cids);

		return true;
	}

	/**
	 * Renders a standalone feedback page that posts straight back to com_installer, so Joomla itself
	 * performs the uninstall on whichever version is running.
	 */
	private function renderUninstallFeedbackForm($app, array $cids): void
	{
		$title             = $this->escape(Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_TITLE'));
		$skipLabel         = $this->escape(Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_SKIP_BUTTON'));
		$whatHappened      = $this->escape(Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED'));
		$emailLabel        = $this->escape(Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_EMAIL'));
		$emailPlaceholder  = $this->escape(Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_EMAIL_PLACEHOLDER'));
		$queryPlaceholder  = $this->escape(Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_QUERY_PLACEHOLDER'));
		$submitLabel       = $this->escape(Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_SUBMIT_BUTTON'));
		$formAction        = $this->escape(Route::_('index.php?option=com_installer&view=manage', false));
		$token             = HTMLHelper::_('form.token');
		$options           = $this->buildReasonOptions();
		$hiddenIds         = $this->buildExtensionIdInputs($cids);

		$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>$title</title>
<style>
body {
	font: 95% Arial, Helvetica, sans-serif;
	background: #fff;
	margin: 0;
	padding: 24px 12px;
	color: #1F3047;
}
.mo-feedback {
	max-width: 460px;
	margin: 0 auto;
	background: #F1F4F8;
	padding: 16px;
}
.mo-feedback__title {
	position: relative;
	background: #1F3047;
	padding: 20px 40px 20px 16px;
	font-size: 140%;
	font-weight: 300;
	text-align: center;
	color: #fff;
	margin: -16px -16px 16px -16px;
}
.mo-feedback__close {
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
	line-height: 1;
}
.mo-feedback__close:hover {
	color: #ffdddd;
}
.mo-feedback__option {
	padding: 2px 0;
}
.mo-feedback__option label {
	font-weight: normal;
	font-size: 14.6px;
	cursor: pointer;
	margin-left: 6px;
}
.mo-feedback__label {
	display: block;
	font-weight: bold;
	margin: 12px 0 4px;
}
.mo-feedback__required {
	color: #ff0000;
}
.mo-feedback textarea,
.mo-feedback input[type="email"] {
	transition: all 0.30s ease-in-out;
	outline: none;
	box-sizing: border-box;
	width: 100%;
	background: #fff;
	margin: 8px 0;
	border: 1px solid #ccc;
	padding: 3%;
	color: #1F3047;
	font: 95% Arial, Helvetica, sans-serif;
}
.mo-feedback textarea:focus,
.mo-feedback input[type="email"]:focus {
	box-shadow: 0 0 5px #2E486B;
	border: 1px solid #2E486B;
}
.mo-feedback input[type="submit"] {
	box-sizing: border-box;
	width: 100%;
	padding: 3%;
	background: #2E486B;
	border: none;
	color: #fff;
	cursor: pointer;
	margin-top: 12px;
}
.mo-feedback input[type="submit"]:hover {
	background: #36547D;
}
</style>
</head>
<body>
<div class="mo-feedback">
<form method="post" action="$formAction" id="mojsp_feedback">
<h1 class="mo-feedback__title">
$title
<button type="submit" name="miniorange_feedback_skip" value="1" class="mo-feedback__close" formnovalidate title="$skipLabel">&times;</button>
</h1>
<h3>$whatHappened</h3>
$options
<textarea id="mo-feedback-detail" name="query_feedback" rows="4" placeholder="$queryPlaceholder"></textarea>
<label class="mo-feedback__label" for="mo-feedback-email">$emailLabel <span class="mo-feedback__required">*</span></label>
<input type="email" id="mo-feedback-email" name="feedback_email" required placeholder="$emailPlaceholder">
<input type="submit" name="miniorange_feedback_submit" value="$submitLabel">
<input type="hidden" name="mojsp_feedback" value="1">
<input type="hidden" name="option" value="com_installer">
<input type="hidden" name="task" value="manage.remove">
$hiddenIds$token
</form>
</div>
<script>
(function () {
	var detail = document.getElementById('mo-feedback-detail');
	var radios = document.querySelectorAll('input[name="deactivate_plugin"]');

	for (var i = 0; i < radios.length; i++) {
		radios[i].addEventListener('change', function () {
			detail.setAttribute('placeholder', this.getAttribute('data-placeholder'));

			if (this.getAttribute('data-detail') === '1') {
				detail.setAttribute('required', 'required');
			} else {
				detail.removeAttribute('required');
			}
		});
	}
})();
</script>
</body>
</html>
HTML;

		if (!headers_sent())
		{
			header('Content-Type: text/html; charset=utf-8');
		}

		echo $html;

		$app->close();
	}

	private function buildReasonOptions(): string
	{
		$markup = '';
		$index = 0;

		foreach ($this->getUninstallReasons() as $reason)
		{
			$inputId = 'mo-feedback-reason-' . $index++;
			$label = $this->escape($reason['label']);

			$markup .= '<div class="mo-feedback__option">'
				. '<input type="radio" name="deactivate_plugin" required'
				. ' id="' . $inputId . '"'
				. ' value="' . $label . '"'
				. ' data-placeholder="' . $this->escape($reason['placeholder']) . '"'
				. ' data-detail="' . ($reason['detail'] ? '1' : '0') . '">'
				. '<label for="' . $inputId . '">' . $label . '</label>'
				. '</div>' . "\n";
		}

		return $markup;
	}

	private function getUninstallReasons(): array
	{
		return [
			[
				'label'       => Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_1'),
				'placeholder' => 'Let us know what feature are you looking for',
				'detail'      => false,
			],
			[
				'label'       => Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_2'),
				'placeholder' => 'Confusing Interface? Reach out to us at joomlasupport@xecurify.com, we\'ll help set up the plugin',
				'detail'      => false,
			],
			[
				'label'       => Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_4'),
				'placeholder' => 'Reach out to us at joomlasupport@xecurify.com, we\'ll help you resolve the issue',
				'detail'      => false,
			],
			[
				'label'       => Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_5'),
				'placeholder' => 'Kindly let us know which functionality of the plugin is not working for you',
				'detail'      => true,
			],
			[
				'label'       => Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_7'),
				'placeholder' => 'Kindly let us know what issues you were facing',
				'detail'      => false,
			],
			[
				'label'       => Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_8'),
				'placeholder' => 'Not able to configure? Let us know so that we can improve the interface',
				'detail'      => false,
			],
			[
				'label'       => Text::_('PLG_SYSTEM_MINIORANGEOAUTH_FEEDBACK_FORM_WHAT_HAPPENED_OPTION_9'),
				'placeholder' => 'Can you let us know the reason for deactivation',
				'detail'      => true,
			],
		];
	}

	private function buildExtensionIdInputs(array $cids): string
	{
		$markup = '';

		foreach ($cids as $cid)
		{
			$markup .= '<input type="hidden" name="cid[]" value="' . (int) $cid . '">' . "\n";
		}

		return $markup;
	}

	/**
	 * Records the feedback and then lets the request fall through to com_installer, which runs the
	 * actual uninstall. Nothing in here may abort the request, otherwise the extension stays behind.
	 */
	private function processUninstallFeedback($app, array $post): void
	{
		if (!$this->canManageInstallerUninstall($app) || !Session::checkToken())
		{
			return;
		}

		try
		{
			MoOAuthUtility::miniOauthUpdateDb(
				'#__miniorange_oauth_customer',
				['uninstall_feedback' => 1],
				['id' => '1']
			);
		}
		catch (\Throwable $e)
		{
			// A missing table or column must not stop the uninstall.
		}

		if (isset($post['miniorange_feedback_skip']) || !class_exists('MoOauthCustomer', false))
		{
			return;
		}

		$reason = !empty($post['deactivate_plugin']) ? (string) $post['deactivate_plugin'] : '';
		$details = !empty($post['query_feedback']) ? (string) $post['query_feedback'] : '';
		$feedbackEmail = !empty($post['feedback_email']) ? (string) $post['feedback_email'] : '';

		if ($reason === '' && $details === '')
		{
			return;
		}

		try
		{
			$customerResult = MoOAuthUtility::miniOauthFetchDb('#__miniorange_oauth_customer', ['id' => '1']);
			$adminPhone = isset($customerResult['admin_phone']) ? $customerResult['admin_phone'] : '';

			MoOauthCustomer::submitFeedbackForm($feedbackEmail, $adminPhone, trim($reason . ' : ' . $details, ' :'));
		}
		catch (\Throwable $e)
		{
			// A failed feedback call must not stop the uninstall.
		}
	}

	private function canManageInstallerUninstall($app): bool
	{
		if (!$app->isClient('administrator'))
		{
			return false;
		}

		if (method_exists($app, 'getIdentity'))
		{
			$user = $app->getIdentity();
		}
		else
		{
			$user = Factory::getUser();
		}

		if ($user === null || (int) $user->id === 0)
		{
			return false;
		}

		return $user->authorise('core.admin')
			|| $user->authorise('core.manage', 'com_installer');
	}

	private function normaliseExtensionIds($ids): array
	{
		if (!is_array($ids))
		{
			$ids = [$ids];
		}

		$normalised = [];

		foreach ($ids as $id)
		{
			$id = (int) $id;

			if ($id > 0)
			{
				$normalised[] = $id;
			}
		}

		return array_values(array_unique($normalised));
	}

	/**
	 * Extension ids of the package, its children and the component, so the feedback form is shown no
	 * matter which part of the bundle the administrator selected.
	 */
	private function getMiniOrangeExtensionIds(): array
	{
		try
		{
			$db = MoOAuthUtility::getDBObject();
			$query = $db->getQuery(true)
				->select($db->quoteName('extension_id'))
				->from($db->quoteName('#__extensions'))
				->where(
					$db->quoteName('element') . ' IN ('
					. $db->quote('pkg_oauthclient') . ', '
					. $db->quote('com_miniorange_oauth') . ')'
				);
			$db->setQuery($query);
			$rootIds = $this->normaliseExtensionIds($db->loadColumn());
		}
		catch (\Throwable $e)
		{
			return [];
		}

		if ($rootIds === [])
		{
			return [];
		}

		try
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('extension_id'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('package_id') . ' IN (' . implode(', ', $rootIds) . ')');
			$db->setQuery($query);
			$childIds = $this->normaliseExtensionIds($db->loadColumn());
		}
		catch (\Throwable $e)
		{
			$childIds = [];
		}

		return array_values(array_unique(array_merge($rootIds, $childIds)));
	}

	private function isUninstallFeedbackGiven(): bool
	{
		try
		{
			$result = MoOAuthUtility::miniOauthFetchDb(
				'#__miniorange_oauth_customer',
				['id' => '1'],
				'loadColumn',
				'uninstall_feedback'
			);
		}
		catch (\Throwable $e)
		{
			// Never block an uninstall because the feedback state could not be read.
			return true;
		}

		if (!is_array($result) || $result === [])
		{
			return true;
		}

		return (int) reset($result) !== 0;
	}

	private function escape($value): string
	{
		return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
	}
}
