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
use Joomla\CMS\Language\Text;

require_once __DIR__ . '/mo_oauth_logger.php';
require_once __DIR__ . '/mo_oauth_utility.php';

$language = Factory::getLanguage();
$language->load('com_miniorange_oauth', JPATH_ADMINISTRATOR, null, false, true);

class Mo_OAuth_Hanlder
{
	/**
	 * @var  string
	 */
	private $error;

	public function __construct($error = '')
	{
		$this->error = $error;
	}

	public function getAccessToken($tokenendpoint, $grantType, $clientid, $clientsecret, $code, $redirectUrl, $inHeaderOrBody)
	{
		if (!MoOauthUtility::isCurlInstalled())
		{
			$this->handleCurlNotInstalled();

			return ['', ''];
		}

		$session = Factory::getSession();
		$ch = curl_init($tokenendpoint);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		MoOauthUtility::applySecureCurlOptions($ch);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_POST, true);

		if ($inHeaderOrBody == 'both')
		{
			curl_setopt(
				$ch, CURLOPT_HTTPHEADER, array(
				'Accept: application/json',
				'Authorization: Basic ' . base64_encode($clientid . ":" . $clientsecret)
				)
			);
			curl_setopt(
				$ch,
				CURLOPT_POSTFIELDS,
				http_build_query(
					[
						'redirect_uri'  => $redirectUrl,
						'grant_type'    => $grantType,
						'client_id'     => $clientid,
						'client_secret' => $clientsecret,
						'code'          => $code,
					]
				)
			);
		}
		elseif ($inHeaderOrBody == 'inHeader')
		{
			curl_setopt(
				$ch, CURLOPT_HTTPHEADER, array(
				'Accept: application/json',
				'Authorization: Basic ' . base64_encode($clientid . ":" . $clientsecret)
				)
			);
			curl_setopt(
				$ch,
				CURLOPT_POSTFIELDS,
				http_build_query(
					[
						'redirect_uri' => $redirectUrl,
						'grant_type'   => $grantType,
						'code'         => $code,
					]
				)
			);
		}
		else
		{
			curl_setopt(
				$ch, CURLOPT_HTTPHEADER, array(
				'Accept: application/json'
				)
			);
			curl_setopt(
				$ch,
				CURLOPT_POSTFIELDS,
				http_build_query(
					[
						'redirect_uri'  => $redirectUrl,
						'grant_type'    => $grantType,
						'client_id'     => $clientid,
						'client_secret' => $clientsecret,
						'code'          => $code,
					]
				)
			);
		}

		$content = curl_exec($ch);

		if (curl_error($ch))
		{
			MoOAuthLogger::addLog('Error : ' . curl_error($ch), 'CRITICAL', 'MOOAUTH-A02');
			$this->setError('[MOOAUTH-A02] : ' . curl_error($ch));
			$session->set('mo_reason', curl_error($ch));
		}

		$content = json_decode($content, true);

		if (!is_array($content))
		{
			MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
			$this->setError("[MOOAUTH-009] : Invalid response received.");
			$session->set('mo_reason', 'Invalid response received.');
		}

		// First check if any error received
		if (isset($content["error_description"]))
		{
			MoOAuthLogger::addLog('Error : ' . $content["error_description"], 'CRITICAL', 'MOOAUTH-A03');
			$this->setError("[MOOAUTH-A03] : " . $content["error_description"]);
			$session->set('mo_reason', $content["error_description"]);
		}
		elseif (isset($content["error"]))
		{
			MoOAuthLogger::addLog('Error : ' . $content["error"], 'CRITICAL', 'MOOAUTH-A04');
			$this->setError("[MOOAUTH-A04] : " . $content["error"]);
			$session->set('mo_reason',  $content["error"]);
		}

		// Extract access_token and id_token
		$idToken = isset($content["id_token"]) ? $content["id_token"] : '';
		$accessToken = isset($content["access_token"]) ? $content["access_token"] : '';

		if (empty($idToken) && empty($accessToken))
		{
			MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
			$this->setError('[MOOAUTH-009] : ' . Text::_('COM_MINIORANGE_OAUTH_ACCESS_ID_TOKEN_MISSING') . Text::_('COM_MINIORANGE_OAUTH_ACCESS_ID_TOKEN_MISSING_SOLUTION'));
			$session->set('mo_reason', 'Invalid response received from OAuth Provider. Contact your administrator for more details.');
		}

		return array($accessToken, $idToken);
	}

	public function getResourceOwnerFromIdToken($idToken)
	{
		$session = Factory::getSession();
		$idArray = explode(".", $idToken);

		if (isset($idArray[1]))
		{
			$idBody = $this->base64urlDecode($idArray[1]);

			if (is_array(json_decode($idBody, true)))
			{
				return json_decode($idBody, true);
			}
		}

		MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
		$this->setError('[MOOAUTH-009] : ' . Text::_('COM_MINIORANGE_OAUTH_INVALID_ID_TOKEN') . $idToken);
		$session->set('mo_reason', ' Invalid response received.<br><b>Id_token : </b>' . $idToken);

		return false;
	}

	public function getResourceOwner($resourceownerdetailsurl, $accessToken, $idToken)
	{
		$session = Factory::getSession();

		if (!MoOauthUtility::isCurlInstalled())
		{
			$this->handleCurlNotInstalled();

			return false;
		}

		if (!empty($idToken) && !is_null($idToken))
		{
			return $this->getResourceOwnerFromIdToken($idToken);
		}

		$ch = curl_init($resourceownerdetailsurl);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		MoOauthUtility::applySecureCurlOptions($ch);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt(
			$ch, CURLOPT_HTTPHEADER, array(
			'Authorization: Bearer ' . $accessToken,
			'User-Agent:web'
			)
		);
		$content = curl_exec($ch);

		if (curl_error($ch))
		{
			$this->setError(curl_error($ch));
			$session->set('mo_reason', curl_error($ch));

			return false;
		}

		$content = json_decode($content, true);

		if (!is_array($content))
		{
			MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
			$this->setError("[MOOAUTH-009] : " . Text::_('COM_MINIORANGE_OAUTH_INVALID_RESPONSE'));
			$session->set('mo_reason', "Invalid response received.");

			return false;
		}

		if (isset($content["error_description"]))
		{
			MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
			$this->setError('[MOOAUTH-009] : ' . $content["error_description"]);
			$session->set('mo_reason', '[MOOAUTH-009]' . $content["error_description"]);

			return false;
		}
		elseif (isset($content["error"]))
		{
			MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
			$this->setError('[MOOAUTH-009] : ' . $content["error"]);
			$session->set('mo_reason', '[MOOAUTH-009 : ]' . $content["error"]);

			return false;
		}

		return $content;
	}

	private function base64urlDecode($data)
	{
		$remainder = strlen($data) % 4;

		if ($remainder)
		{
			$padlen = 4 - $remainder;
			$data .= str_repeat('=', $padlen);
		}

		$data = strtr($data, '-_', '+/');

		return base64_decode($data);
	}

	private function handleCurlNotInstalled()
	{
		$session = Factory::getSession();
		$errorMessage = Text::_('COM_MINIORANGE_OAUTH_PHP_CURL') . ' [<a href="https://www.php.net/manual/en/curl.installation.php" target="_blank">' . Text::_('COM_MINIORANGE_OAUTH_LEARN_MORE') . '</a>]';

		MoOAuthLogger::addLog('PHP cURL extension is not installed or disabled.', 'CRITICAL', 'MOOAUTH-CURL');
		$this->setError($errorMessage);
		$session->set('mo_reason', Text::_('COM_MINIORANGE_OAUTH_PHP_CURL'));
	}

	public function setError($error)
	{
		$this->error = $error;
	}

	public function isError()
	{
		if (empty($this->error))
		{
			return false;
		}

		return true;
	}

	public function printError()
	{
		if (!$this->isError())
		{
			return;
		}

		if (is_array($this->error))
		{
			print_r($this->error);
		}
		else
		{
			echo ($this->error);
		}

		echo Text::_('COM_MINIORANGE_OAUTH_LOGS_SUGGESTION');
		exit;
	}

	public function showFormattedErrorMessage(string $errorMessage, string $description = '')
	{
		$body = "<p style='margin:0 0 10px; font-weight:bold; font-size:16px;'>" . htmlspecialchars($errorMessage) . "</p>";

		if (!empty($description))
		{
			$body .= "<p style='margin:5px 0;'>" . nl2br(htmlspecialchars($description)) . "</p>";
		}

		echo "
        <div style='
            background: #fff;
            padding: 18px;
            border-radius: 8px;
            border: 2px solid #cc3333;
            box-shadow: 0 0 12px rgba(0,0,0,0.18);
            margin-top: 15px;
            font-family: Arial, sans-serif;
        '>
            <div style='font-size:14px; color:#333; line-height:1.5;'>
                $body
            </div>
        </div>
        ";
	}
}
