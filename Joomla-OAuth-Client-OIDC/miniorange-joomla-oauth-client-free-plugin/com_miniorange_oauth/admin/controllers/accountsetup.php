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
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

class MiniorangeOauthControllerAccountsetup extends FormController
{
	public function __construct()
	{
		$this->viewList = 'accountsetup';
		parent::__construct();
	}

	public function saveAdminMail()
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
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('contact_admin_email') . ' = ' . $db->quote($post['oauth_client_admin_email']),

		);

		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);

		$query->update($db->quoteName('#__miniorange_oauth_customer'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();
		$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup', Text::_('COM_MINIORANGE_OAUTH_ADMIN_EMAIL_CHANGED'));

		return;
	}

	public function saveConfig()
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
		$appD = new MoOauthCustomer;

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup');

			return;
		}
		elseif (isset($post['oauth_config_form_step1']))
		{
			if (isset($post['callbackurl']))
			{
				$callbackurlhttp           = isset($post['callbackurlhttp']) ? $post['callbackurlhttp'] : 'http';
				$redirectUri               = isset($post['callbackurl']) ? $post['callbackurl'] : '';
				$redirectUri               = $callbackurlhttp . "" . $redirectUri;
				$appname                   = isset($post['mo_oauth_app_name']) ? $post['mo_oauth_app_name'] : '';
				$db     = self::getDBObject();
				$query  = $db->getQuery(true);
				$fields = array(
					$db->quoteName('appname') . ' = ' . $db->quote($appname),
					$db->quoteName('redirecturi') . ' = ' . $db->quote($redirectUri),
				);

				$conditions = array(
					$db->quoteName('id') . ' = 1'
				);

				$query->update($db->quoteName('#__miniorange_oauth_config'))->set($fields)->where($conditions);
				$db->setQuery($query);
				$result = $db->execute();
				$returnURL  = 'index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp=' . $post['mo_oauth_app_name'] . '&progress=step2';
				$errMessage = Text::_('COM_MINIORANGE_OAUTH_STEP2_CONFIG_SUCCESS_MSG');
			}
			else
			{
				$returnURL  = 'index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp=' . $post['mo_oauth_app_name'];
				$errMessage = Text::_('COM_MINIORANGE_OAUTH_REDIRECT_URI_ALERT');
				$this->setRedirect($returnURL, $errMessage, 'error');

				return;
			}
		}
		elseif (isset($post['oauth_config_form_step2']))
		{
			$clientid                = isset($post['mo_oauth_client_id']) ? $post['mo_oauth_client_id'] : '';
			$clientsecret            = isset($post['mo_oauth_client_secret']) ? $post['mo_oauth_client_secret'] : '';
			$scope                   = isset($post['mo_oauth_scope']) ? $post['mo_oauth_scope'] : '';
			$appname                 = isset($post['mo_oauth_app_name']) ? $post['mo_oauth_app_name'] : '';
			$customappname           = isset($post['mo_oauth_custom_app_name']) ? $post['mo_oauth_custom_app_name'] : '';
			$appEndpoints            = json_decode($appD->getAppJason(), true);
			$appEndpoints            = $appEndpoints[$appname];
			$authorizeurl            = isset($post['mo_oauth_authorizeurl']) ? $post['mo_oauth_authorizeurl'] : '';
			$accesstokenurl          = isset($post['mo_oauth_accesstokenurl']) ? $post['mo_oauth_accesstokenurl'] : '';
			$resourceownerdetailsurl = isset($post['mo_oauth_resourceownerdetailsurl']) ? $post['mo_oauth_resourceownerdetailsurl'] : '';
			$current = "";

			if ($authorizeurl == "" && $accesstokenurl == "" && $resourceownerdetailsurl == "")
			{
				$authorizeurl            = isset($appEndpoints['authorize']) ? $appEndpoints['authorize'] : '';
				$accesstokenurl          = isset($appEndpoints['token']) ? $appEndpoints['token'] : '';
				$resourceownerdetailsurl = isset($appEndpoints['userinfo']) ? $appEndpoints['userinfo'] : '';
				$appData                 = json_decode($appD->getAppData(), true);
				$appData                 = explode(",", $appData[$appname]['1']);
				$scope                   = isset($appEndpoints['scope']) ? $appEndpoints['scope'] : 'email';

				foreach ($appData as $key => $val)
				{
					if (strpos($post[$val], 'http') !== false && $appname != 'keycloak')
					{
						if (strpos($post[$val], 'https://') !== false)
						{
							$current = trim($post[$val], "https:// /");
						}

						if (strpos($post[$val], 'http://') !== false)
						{
							$current = trim($post[$val], "http:// /");
						}
					}
					else
					{
						$current = $post[$val];
					}

					$authorizeurl            = str_replace("{" . strtolower($val) . "}", $current, $authorizeurl);
					$accesstokenurl          = str_replace("{" . strtolower($val) . "}", $current, $accesstokenurl);
					$resourceownerdetailsurl = str_replace("{" . strtolower($val) . "}", $current, $resourceownerdetailsurl);
				}
			}

			$inHeader = isset($post['mo_oauth_in_header']) ? $post['mo_oauth_in_header'] : '';
			$inBody = isset($post['mo_oauth_body']) ? $post['mo_oauth_body'] : '';

			if (isset($post['mo_oauth_option']))
			{
				if ($post['mo_oauth_option'] == 'body')
				{
					$inBody = 1;
				}

				if ($post['mo_oauth_option'] == 'header')
				{
					$inHeader = 1;
				}
			}

			$inHeaderOrBody       = "inHeader";

			if ($inHeader == '1' && $inBody == '1')
			{
				$inHeaderOrBody = "both";
			}
			elseif ($inBody == '1')
			{
				$inHeaderOrBody = "inBody";
			}

			$db     = self::getDBObject();
			$query  = $db->getQuery(true);
			$fields = array(
				$db->quoteName('appname') . ' = ' . $db->quote($appname),
				$db->quoteName('custom_app') . ' = ' . $db->quote($customappname),
				$db->quoteName('client_id') . ' = ' . $db->quote(trim($clientid)),
				$db->quoteName('client_secret') . ' = ' . $db->quote(trim($clientsecret)),
				$db->quoteName('app_scope') . ' = ' . $db->quote($scope),
				$db->quoteName('authorize_endpoint') . ' = ' . $db->quote(trim($authorizeurl)),
				$db->quoteName('access_token_endpoint') . ' = ' . $db->quote(trim($accesstokenurl)),
				$db->quoteName('user_info_endpoint') . ' = ' . $db->quote(trim($resourceownerdetailsurl)),
				$db->quoteName('in_header_or_body') . '=' . $db->quote($inHeaderOrBody)

			);
			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);

			$query->update($db->quoteName('#__miniorange_oauth_config'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();
			$returnURL  = 'index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp=' . $post['mo_oauth_app_name'] . '&progress=step3';
			$errMessage = Text::_('COM_MINIORANGE_OAUTH_STEP3_CONFIG_SUCCESS_MSG');
		}

		$cDate = MoOauthCustomer::getAccountDetails();

		if ($cDate['cd_plugin'] == '')
		{
			$time = time();
			$cTime = date('m/d/Y H:i:s', time());
			$db = self::getDBObject();
			$query = $db->getQuery(true);
			$fields = array(
				$db->quoteName('cd_plugin') . ' = ' . $db->quote($time),
			);

			$conditions = array(
				$db->quoteName('id') . ' = 1'
			);

			$query->update($db->quoteName('#__miniorange_oauth_customer'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$db->execute();
		}
		else
		{
			$cTime = date('m/d/Y H:i:s', $cDate['cd_plugin']);
		}

		$dVar = new JConfig;
		$checkEmail = $dVar->mailfrom;
		$baseUrl = Uri::root();
		$dnoSsos = 0;
		$tnoSsos = 0;
		$previousUpdate = '';
		$presentUpdate = '';
		$message = isset($post['oauth_config_form_step1']) ? 'Step 1 saved.' : 'Step 2 saved';
		MoOauthCustomer::pluginEfficiencyCheck($checkEmail, $appname, $baseUrl, $cTime, $dnoSsos, $tnoSsos, $previousUpdate, $presentUpdate, $message, $scope, $authorizeurl, $accesstokenurl, $resourceownerdetailsurl, $inHeaderOrBody);
		$this->setRedirect($returnURL, $errMessage);
	}

	public function saveMapping()
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

		$emailAttr = isset($post['mo_oauth_email_attr']) ? $post['mo_oauth_email_attr'] : '';
		$firstNameAttr = isset($post['mo_oauth_first_name_attr']) ? $post['mo_oauth_first_name_attr'] : '';

		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('email_attr') . ' = ' . $db->quote($emailAttr),
			$db->quoteName('username_attr') . ' = ' . $db->quote($firstNameAttr),
		);

		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);

		$query->update($db->quoteName('#__miniorange_oauth_config'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$db->execute();

		$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&progress=step4', Text::_('COM_MINIORANGE_OAUTH_ATTRIBUTE_MAPPING_SAVED_SUCCESSFULLY'));
	}

	public function clearConfig()
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$fields = array(
			$db->quoteName('appname') . ' = ' . $db->quote(''),
			$db->quoteName('custom_app') . ' = ' . $db->quote(''),
			$db->quoteName('client_id') . ' = ' . $db->quote(''),
			$db->quoteName('client_secret') . ' = ' . $db->quote(''),
			$db->quoteName('app_scope') . ' = ' . $db->quote(''),
			$db->quoteName('authorize_endpoint') . ' = ' . $db->quote(''),
			$db->quoteName('access_token_endpoint') . ' = ' . $db->quote(''),
			$db->quoteName('user_info_endpoint') . ' = ' . $db->quote(''),
			$db->quoteName('redirecturi') . ' = ' . $db->quote(''),
			$db->quoteName('email_attr') . ' = ' . $db->quote(''),
			$db->quoteName('username_attr') . ' = ' . $db->quote(''),
			$db->quoteName('test_attribute_name') . ' = ' . $db->quote(''),
		);

		$conditions = array(
			$db->quoteName('id') . ' = 1'
		);

		$query->update($db->quoteName('#__miniorange_oauth_config'))->set($fields)->where($conditions);
		$db->setQuery($query);
		$db->execute();

		$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration', Text::_('COM_MINIORANGE_OAUTH_APP_CONFIGURATION_RESET'));
	}

	public function requestForDemoPlan()
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

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support');

			return;
		}

		$email          = $post['email'];
		$plan           = $post['plan'];
		$description    = $post['description'];
		$demoTrial     = $post['demo'];
		$customer       = new MoOauthCustomer;

		if ($plan == "Not Sure")
		{
			$description = $post['description'];
		}

		$response = json_decode($customer->requestForDemo($email, $plan, $description, $demoTrial));

		if ($response->status != 'ERROR')
		{
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_OAUTH_YOUR_QUERY_IS_SUBMITTED'));
		}
		else
		{
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_OAUTH_AN_ERROR_OCCURRED'), 'error');
		}
	}

	public function callContactUs()
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

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support');

			return;
		}

		$queryEmail = $post['mo_oauth_setup_call_email'];
		$query       = $post['mo_oauth_setup_call_issue'];
		$description = $post['mo_oauth_setup_call_desc'];
		$callDate    = $post['mo_oauth_setup_call_date'];
		$timeZone    = $post['mo_oauth_setup_call_timezone'];

		if (MoOAuthUtility::checkEmptyOrNull($timeZone) ||MoOAuthUtility::checkEmptyOrNull($callDate) ||MoOAuthUtility::checkEmptyOrNull($queryEmail) || MoOAuthUtility::checkEmptyOrNull($query))
		{
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup', Text::_('COM_MINIORANGE_OAUTH_ENTER_ALL_FIELDS_TO_SETUP_A_CALL'), 'error');

			return;
		}
		else
		{
			$contactUs = new MoOauthCustomer;
			$submited = json_decode($contactUs->requestForDemo($queryEmail, $query, $description, 'true', $callDate, $timeZone), true);

			if (json_last_error() == JSON_ERROR_NONE)
			{
				if (is_array($submited) && array_key_exists('status', $submited) && $submited['status'] == 'ERROR')
				{
					$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support', $submited['message'], 'error');
				}
				else
				{
					if ($submited == false)
					{
						$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_OAUTH_YOUR_QUERY_COULD_NOT_BE_SUBMITTED'), 'error');
					}
					else
					{
						$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_OAUTH_YOUR_QUERY_IS_SUBMITTED'));
					}
				}
			}
		}
	}
	public function contactUs()
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

		if (count($post) == 0)
		{
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support');

			return;
		}

		$queryEmail = isset($post['query_email']) ? $post['query_email'] : '';
		$query       = isset($post['query']) ? $post['query'] : '';
		$phoneCode  = isset($post['country_code']) ? $post['country_code'] : '';
		$phone       = isset($post['query_phone']) ? $post['query_phone'] : '';
		$queryWithconfig = isset($post['mo_oauth_query_withconfig']) ? $post['mo_oauth_query_withconfig'] : '';
		$appDetails = $this->retrieveAttributes('#__miniorange_oauth_config');
		$phone = $phoneCode . ' ' . $phone;

		if ($queryWithconfig != 1)
		{
			$appDetails['appname'] = '';
			$appDetails['custom_app'] = '';
			$appDetails['app_scope'] = '';
			$appDetails['authorize_endpoint'] = '';
		}

		if (MoOAuthUtility::checkEmptyOrNull($queryEmail) || MoOAuthUtility::checkEmptyOrNull($query))
		{
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_OAUTH_SUBMIT_QUERY_WITH_EMAIL'), 'error');

			return;
		}
		else
		{
			$contactUs = new MoOauthCustomer;
			$submited = json_decode($contactUs->submitContactUs($queryEmail, $phone, $query, $appDetails), true);

			if (json_last_error() == JSON_ERROR_NONE)
			{
				if (is_array($submited) && array_key_exists('status', $submited) && $submited['status'] == 'ERROR')
				{
					$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support', $submited['message'], 'error');
				}
				else
				{
					if ($submited == false)
					{
						$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_OAUTH_YOUR_QUERY_COULD_NOT_BE_SUBMITTED'), 'error');
					}
					else
					{
						$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=support', Text::_('COM_MINIORANGE_OAUTH_YOUR_QUERY_IS_SUBMITTED'));
					}
				}
			}
		}
	}

	protected function updateDatabaseQuery($databaseName, $updatefieldsarray)
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);

		foreach ($updatefieldsarray as $key => $value)
		{
			$databaseFields[] = $db->quoteName($key) . ' = ' . $db->quote($value);
		}

		$query->update($db->quoteName($databaseName))->set($databaseFields)->where($db->quoteName('id') . " = 1");
		$db->setQuery($query);
		$db->execute();
	}

	public function exportConfiguration()
	{
		$appDetails = $this->retrieveAttributes('#__miniorange_oauth_config');
		$customerDetails = $this->retrieveAttributes('#__miniorange_oauth_customer');
		$customapp = $appDetails['appname'];
		$clientid = $appDetails['client_id'];
		$clientsecret = $appDetails['client_secret'];

		if ($clientid == '' && $clientsecret == '')
		{
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=overview', Text::_('COM_MINIORANGE_OAUTH_ENTER_CLIENT_ID_BEFORE_DOWNLOADING'), 'error');

			return;
		}

		$pluginConfiguration = array();
		array_push($pluginConfiguration, $appDetails, $customerDetails);

		$clientSecret = $pluginConfiguration[0]['client_secret'];
		$ciphering = "AES-128-CTR";
		$encryptionIv = '4488882453112245';
		$encryptionKey = "minOrangeOauth";
		$options = 0;
		$encreptedClientSecret = openssl_encrypt($clientSecret, $ciphering, $encryptionKey, $options, $encryptionIv);

		$pluginConfiguration[0]['client_secret'] = $encreptedClientSecret;
		$filecontentd = json_encode($pluginConfiguration, JSON_PRETTY_PRINT);

		header('Content-Disposition: attachment; filename=oauth-client.json');
		header('Content-Type: application/json');
		print_r($filecontentd);

		$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp=' . $customapp, Text::_('COM_MINIORANGE_OAUTH_PLUGIN_CONFIGURATION_DOWNLOADED_SUCCESSFULLY'));
		exit;
	}

	protected function retrieveAttributes($tablename)
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName($tablename));
		$query->where($db->quoteName('id') . " = 1");
		$db->setQuery($query);

		return $db->loadAssoc();
	}

	public function moOAuthProxyConfigReset()
	{
		$nameOfDatabase = '#__miniorange_oauth_config';
		$updateFieldsArray = array('proxy_server_url' => '', 'proxy_server_port' => '80', 'proxy_username' => '', 'proxy_password' => '', 'proxy_set' => '');

		$this->updateDatabaseQuery($nameOfDatabase, $updateFieldsArray);
		$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=account', Text::_('COM_MINIORANGE_OAUTH_PROXY_SETTING_RESET'));
	}

	public function proxyConfig()
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
		$proxyHostName = isset($post['mo_proxy_host']) ? $post['mo_proxy_host'] : '';
		$proxyPortNumber = isset($post['mo_proxy_port']) ? $post['mo_proxy_port'] : '';
		$proxyUsername = isset($post['mo_proxy_username']) ? $post['mo_proxy_username'] : '';
		$proxyPassword = isset($post['mo_proxy_password']) ? base64_encode($post['mo_proxy_password']) : '';
		$updateFieldsArray = array(
			'proxy_host_name' => $proxyHostName,
			'port_number'     => $proxyPortNumber,
			'username'        => $proxyUsername,
			'password'        => $proxyPassword,
		);

			$this->updateDatabaseQuery('#__miniorange_oauth_config', $updateFieldsArray);
			$this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=proxy', Text::_('COM_MINIORANGE_OAUTH_PROXY_SERVER_SAVED_SUCCESSFULLY'));
	}
	public function proxyConfigReset()
	{
		$updateFieldsArray = array(
		'proxy_host_name' => '',
		'port_number'     => '',
		'username'        => '',
		'password'        => ''
		);

		   $this->updateDatabaseQuery('#__miniorange_oauth_config', $updateFieldsArray);
		   $this->setRedirect('index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=proxy', Text::_('COM_MINIORANGE_OAUTH_PROXY_SETTING_RESET'));
	}
	public function enableSSO()
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

		$ssoStatus = isset($post['mo_oauth_enable_sso']) ? 1 : 0;
		$moOauthEnableSsoButton = isset($post['mo_oauth_enable_sso_button']) ? 1 : 0;

		$updateFieldsArray = array(
			'sso_enable' => $ssoStatus,
			'sso_button_enable' => $moOauthEnableSsoButton
		);

		$messg = Text::_('COM_MINIORANGE_OAUTH_SSO_SETTING_SAVED_SUCCESSFULLY');

		$this->updateDatabaseQuery('#__miniorange_oauth_config', $updateFieldsArray);

		$this->setRedirect(
			'index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=configuration&moAuthAddApp=' . $post['mo_oauth_app_name'] . '&progress=advance_setting',
			$messg
		);
	}

	public function moEnableLogs()
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

		$enableLogs = isset($post['mo_enable_logs']) ? ($post['mo_enable_logs'] == 1 ? 1 : 0) : 0;

		$updateFieldsArray = array(
			'loggers_enable' => $enableLogs
		);

		$this->updateDatabaseQuery('#__miniorange_oauth_config', $updateFieldsArray);

		$messg = $enableLogs == 1 ? Text::_('COM_MINIORANGE_OAUTH_LOGS_ENABLED_SUCCESSFULLY') : Text::_('COM_MINIORANGE_OAUTH_LOGS_DISABLED_SUCCESSFULY');
		$this->setRedirect(
			'index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=loggerreport',
			$messg
		);
	}

	public function moClearLogs()
	{
		$db = self::getDBObject();

		$query = "SELECT COUNT(*) FROM " . $db->quoteName('#__miniorange_oauth_logs');
		$db->setQuery($query);
		$totalLogs = $db->loadResult();

		if ($totalLogs == 0)
		{
			$this->setRedirect(
				'index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=loggerreport',
				Text::_('COM_MINIORANGE_OAUTH_LOGS_ARE_ALREADY_EMPTY'),
				'warning'
			);

			return;
		}

		$query = "TRUNCATE TABLE " . $db->quoteName('#__miniorange_oauth_logs');
		$db->setQuery($query);
		$db->execute();

		$messg = Text::_('COM_MINIORANGE_OAUTH_LOGS_CLEAR_SUCCESSFULLY');
		$this->setRedirect(
			'index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=loggerreport',
			$messg
		);
	}

	public function moDownloadLogs()
	{
		$allLogs = MoOAuthLogger::getAllLogs();

		if (empty($allLogs))
		{
			$this->setRedirect(
				'index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=loggerreport',
				Text::_('COM_MINIORANGE_OAUTH_LOGS_DOWNLOAD_WARNING'),
				'warning'
			);

			return;
		}

		// Define CSV file name
		$fileName = 'miniorange_oauth_logs_' . date('Y-m-d_H-i-s') . '.csv';

		// Set headers for CSV download
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		header('Pragma: no-cache');
		header('Expires: 0');

		// Open PHP output stream as file
		$output = fopen('php://output', 'w');

		// Add CSV column headers
		fputcsv($output, ['  Timestamp  ', '  Log Level  ', '  Message (Code: Issue)  ', ' Location ']);

		// Loop through logs and format each row
		foreach ($allLogs as $log)
		{
			$logEntry = (array) $log;
			$messageData = json_decode($logEntry['message'], true);
			$code = $messageData['code'] ?? '';
			$issue = $messageData['issue'] ?? $logEntry['message'];
			$timestamp    = '  ' . $logEntry['timestamp'] . '  ';
			$logLevel    = '  ' . $logEntry['log_level'] . '  ';
			$formattedMessage = '  ' . $code . ' : ' . $issue . '  ';
			$location = '  ' . $logEntry['file'] . ' in function ' . $logEntry['function_call'] . '() at ' . $logEntry['line_number'];

			fputcsv(
				$output, [
				$timestamp,
				$logLevel,
				$formattedMessage,
				$location
				]
			);
		}

		fclose($output);
		exit;
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
