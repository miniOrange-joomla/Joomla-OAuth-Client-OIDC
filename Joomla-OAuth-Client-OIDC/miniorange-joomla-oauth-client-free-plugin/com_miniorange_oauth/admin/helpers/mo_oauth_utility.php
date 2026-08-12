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

class MoOAuthUtility
{
	public static function checkEmptyOrNull($value)
	{
		if (! isset($value) || empty($value))
		{
			return true;
		}

		return false;
	}

	public static function isCurlInstalled()
	{
		if (extension_loaded('curl') && function_exists('curl_init'))
		{
			return 1;
		}

		return 0;
	}

	public static function getHostname()
	{
		return 'https://login.xecurify.com';
	}

	public static function getCustomerDetails()
	{
		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__miniorange_oauth_customer'));
		$query->where($db->quoteName('id') . " = 1");

		$db->setQuery($query);
		$customerDetails = $db->loadAssoc();

		return $customerDetails;
	}

	public static function getPluginVersion()
	{
		$db = self::getDBObject();
		$dbQuery = $db->getQuery(true)
			->select('manifest_cache')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . " = " . $db->quote('com_miniorange_oauth'));
		$db->setQuery($dbQuery);
		$manifest = json_decode($db->loadResult());

		return($manifest->version);
	}

	public static function getDBObject()
	{
		$app = Factory::getApplication();

		if (method_exists($app, 'getDatabase'))
		{
			return $app->getDatabase();
		}

		return Factory::getDbo();
	}

	public static function loadMoOauthClientHandler()
	{
		if (class_exists('MoOauthClientHandler', false))
		{
			return true;
		}

		$handlerPath = JPATH_LIBRARIES . DIRECTORY_SEPARATOR . 'miniorangeoauthplugin'
			. DIRECTORY_SEPARATOR . 'utility' . DIRECTORY_SEPARATOR . 'MoOauthClientHandler.php';

		if (is_file($handlerPath))
		{
			require_once $handlerPath;
		}
		elseif (function_exists('jimport'))
		{
			jimport('miniorangeoauthplugin.utility.MoOauthClientHandler');
		}

		return class_exists('MoOauthClientHandler', false);
	}

	public static function miniOauthFetchDb($tableName, $condition, $method = 'loadAssoc', $columns = '*')
	{
		if (self::loadMoOauthClientHandler())
		{
			return MoOauthClientHandler::miniOauthFetchDb($tableName, $condition, $method, $columns);
		}

		$db = self::getDBObject();
		$query = $db->getQuery(true);
		$columns = is_array($columns) ? $db->quoteName($columns) : $columns;
		$query->select($columns);
		$query->from($db->quoteName($tableName));

		foreach ($condition as $key => $value)
		{
			$query->where($db->quoteName($key) . ' = ' . $db->quote($value));
		}

		$db->setQuery($query);

		if ($method === 'loadColumn')
		{
			return $db->loadColumn();
		}

		if ($method === 'loadObjectList')
		{
			return $db->loadObjectList();
		}

		if ($method === 'loadResult')
		{
			return $db->loadResult();
		}

		if ($method === 'loadRow')
		{
			return $db->loadRow();
		}

		return $db->loadAssoc();
	}

	public static function miniOauthUpdateDb($tableName, $data, $condition)
	{
		if (self::loadMoOauthClientHandler())
		{
			return MoOauthClientHandler::miniOauthUpdateDb($tableName, $data, $condition);
		}

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

	public static function getOperatingSystem()
	{
		if (isset($_SERVER))
		{
			$userAgent = $_SERVER['HTTP_USER_AGENT'];
		}
		elseif (isset($GLOBALS['HTTP_SERVER_VARS']))
		{
			$userAgent = $GLOBALS['HTTP_SERVER_VARS']['HTTP_USER_AGENT'];
		}
		elseif (isset($GLOBALS['HTTP_USER_AGENT']))
		{
			$userAgent = $GLOBALS['HTTP_USER_AGENT'];
		}
		else
		{
			$userAgent = '';
		}

		$osArray = [
		'windows nt 10' => 'Windows 10',
		'windows nt 6.3' => 'Windows 8.1',
		'windows nt 6.2' => 'Windows 8',
		'windows nt 6.1|windows nt 7.0' => 'Windows 7',
		'windows nt 6.0' => 'Windows Vista',
		'windows nt 5.2' => 'Windows Server 2003/XP x64',
		'windows nt 5.1' => 'Windows XP',
		'windows xp' => 'Windows XP',
		'windows nt 5.0|windows nt5.1|windows 2000' => 'Windows 2000',
		'windows me' => 'Windows ME',
		'windows nt 4.0|winnt4.0' => 'Windows NT',
		'windows ce' => 'Windows CE',
		'windows 98|win98' => 'Windows 98',
		'windows 95|win95' => 'Windows 95',
		'win16' => 'Windows 3.11',
		'mac os x 10.1[^0-9]' => 'Mac OS X Puma',
		'macintosh|mac os x' => 'Mac OS X',
		'mac_powerpc' => 'Mac OS 9',
		'linux' => 'Linux',
		'ubuntu' => 'Linux - Ubuntu',
		'iphone' => 'iPhone',
		'ipod' => 'iPod',
		'ipad' => 'iPad',
		'android' => 'Android',
		'blackberry' => 'BlackBerry',
		'webos' => 'Mobile',

		'(media center pc).([0-9]{1,2}\.[0-9]{1,2})' => 'Windows Media Center',
		'(win)([0-9]{1,2}\.[0-9x]{1,2})' => 'Windows',
		'(win)([0-9]{2})' => 'Windows',
		'(windows)([0-9x]{2})' => 'Windows',
		'Win 9x 4.90' => 'Windows ME',
		'(windows)([0-9]{1,2}\.[0-9]{1,2})' => 'Windows',
		'win32' => 'Windows',
		'(java)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,2})' => 'Java',
		'(Solaris)([0-9]{1,2}\.[0-9x]{1,2}){0,1}' => 'Solaris',
		'dos x86' => 'DOS',
		'Mac OS X' => 'Mac OS X',
		'Mac_PowerPC' => 'Macintosh PowerPC',
		'(mac|Macintosh)' => 'Mac OS',
		'(sunos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'SunOS',
		'(beos)([0-9]{1,2}\.[0-9]{1,2}){0,1}' => 'BeOS',
		'(risc os)([0-9]{1,2}\.[0-9]{1,2})' => 'RISC OS',
		'unix' => 'Unix',
		'os/2' => 'OS/2',
		'freebsd' => 'FreeBSD',
		'openbsd' => 'OpenBSD',
		'netbsd' => 'NetBSD',
		'irix' => 'IRIX',
		'plan9' => 'Plan9',
		'osf' => 'OSF',
		'aix' => 'AIX',
		'GNU Hurd' => 'GNU Hurd',
		'(fedora)' => 'Linux - Fedora',
		'(kubuntu)' => 'Linux - Kubuntu',
		'(ubuntu)' => 'Linux - Ubuntu',
		'(debian)' => 'Linux - Debian',
		'(CentOS)' => 'Linux - CentOS',
		'(Mandriva).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - Mandriva',
		'(SUSE).([0-9]{1,3}(\.[0-9]{1,3})?(\.[0-9]{1,3})?)' => 'Linux - SUSE',
		'(Dropline)' => 'Linux - Slackware (Dropline GNOME)',
		'(ASPLinux)' => 'Linux - ASPLinux',
		'(Red Hat)' => 'Linux - Red Hat',
		/*
		 * Loads of Linux machines will be detected as unix.
		 * Actually, all of the linux machines I've checked have the 'X11' in the User Agent.
		 * 'X11'=>'Unix',
		 */
		'(linux)' => 'Linux',
		'(amigaos)([0-9]{1,2}\.[0-9]{1,2})' => 'AmigaOS',
		'amiga-aweb' => 'AmigaOS',
		'amiga' => 'Amiga',
		'AvantGo' => 'PalmOS',
		/*
		 * '(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1}-([0-9]{1,2}) i([0-9]{1})86){1}'=>'Linux',
		 * '(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1} i([0-9]{1}86)){1}'=>'Linux',
		 * '(Linux)([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3}(rel\.[0-9]{1,2}){0,1})'=>'Linux',
		 */
		'[0-9]{1,2}\.[0-9]{1,2}\.[0-9]{1,3})' => 'Linux',
		'(webtv)/([0-9]{1,2}\.[0-9]{1,2})' => 'WebTV',
		'Dreamcast' => 'Dreamcast OS',
		'GetRight' => 'Windows',
		'go!zilla' => 'Windows',
		'gozilla' => 'Windows',
		'gulliver' => 'Windows',
		'ia archiver' => 'Windows',
		'NetPositive' => 'Windows',
		'mass downloader' => 'Windows',
		'microsoft' => 'Windows',
		'offline explorer' => 'Windows',
		'teleport' => 'Windows',
		'web downloader' => 'Windows',
		'webcapture' => 'Windows',
		'webcollage' => 'Windows',
		'webcopier' => 'Windows',
		'webstripper' => 'Windows',
		'webzip' => 'Windows',
		'wget' => 'Windows',
		'Java' => 'Unknown',
		'flashget' => 'Windows',

		// Delete next line if the script show not the right OS
		// '(PHP)/([0-9]{1,2}.[0-9]{1,2})'=>'PHP',
		'MS FrontPage' => 'Windows',
		'(msproxy)/([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
		'(msie)([0-9]{1,2}.[0-9]{1,2})' => 'Windows',
		'libwww-perl' => 'Unix',
		'UP.Browser' => 'Windows CE',
		'NetAnts' => 'Windows',
		];

		$archRegex = '/\b(x86_64|x86-64|Win64|WOW64|x64|ia64|amd64|ppc64|sparc64|IRIX64)\b/ix';
		$arch = preg_match($archRegex, $userAgent) ? '64' : '32';

		foreach ($osArray as $regex => $value)
		{
			if (preg_match('{\b(' . $regex . ')\b}i', $userAgent))
			{
				return $value . ' x' . $arch;
			}
		}

		return 'Unknown';
	}

	public static function applySecureCurlOptions($ch)
	{
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

		return $ch;
	}

	public static function isSecureRequest()
	{
		$app = Factory::getApplication();

		if (method_exists($app, 'isHttps'))
		{
			return $app->isHttps();
		}

		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
			|| (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
	}

	public static function getSecureCookieOptions($expires = 0, $path = '/')
	{
		return [
			'expires'  => $expires,
			'path'     => $path,
			'domain'   => '',
			'secure'   => self::isSecureRequest(),
			'httponly' => true,
			'samesite' => 'Lax',
		];
	}

	public static function buildSsoBridgeSignature($sessionId, $userId, $expires)
	{
		$secret = Factory::getConfig()->get('secret');

		if ($secret === null || $secret === '')
		{
			return '';
		}

		$payload = (string) $sessionId . '|' . (int) $userId . '|' . (int) $expires;

		return hash_hmac('sha256', $payload, (string) $secret);
	}

	public static function verifySsoBridgeSignature($sessionId, $userId, $expires, $signature)
	{
		if ($sessionId === '' || (int) $userId <= 0 || (int) $expires <= 0 || $signature === '')
		{
			return false;
		}

		if ((int) $expires < time())
		{
			return false;
		}

		$expected = self::buildSsoBridgeSignature($sessionId, $userId, $expires);

		if ($expected === '')
		{
			return false;
		}

		return hash_equals($expected, (string) $signature);
	}

	public static function findSsoBridgeSession($sessionId, $userId)
	{
		if ($sessionId === '' || (int) $userId <= 0)
		{
			return null;
		}

		$db = self::getDBObject();
		$query = $db->getQuery(true)
			->select($db->quoteName(['session_id', 'userid', 'guest', 'username']))
			->from($db->quoteName('#__session'))
			->where($db->quoteName('session_id') . ' = ' . $db->quote($sessionId))
			->where($db->quoteName('userid') . ' = ' . (int) $userId)
			->where($db->quoteName('userid') . ' > 0');
		$db->setQuery($query);

		$row = $db->loadAssoc();

		return is_array($row) ? $row : null;
	}

	public static function clearSsoBridgeCookies()
	{
		$expired = self::getSecureCookieOptions(time() - 300);
		setcookie('mo_site', '', $expired);
		setcookie('session_id', '', $expired);
		setcookie('user_id', '', $expired);
		setcookie('mo_oauth_sig', '', $expired);
		setcookie('mo_oauth_exp', '', $expired);
	}

	public static function getMiniOrangeCustomerKey()
	{
		return self::resolveMiniOrangeCredential('MINIORANGE_OAUTH_CUSTOMER_KEY', 'customer_key');
	}

	public static function getMiniOrangeApiKey()
	{
		return self::resolveMiniOrangeCredential('MINIORANGE_OAUTH_API_KEY', 'api_key');
	}

	private static function resolveMiniOrangeCredential($envName, $dbColumn)
	{
		$value = getenv($envName);

		if (false !== $value && '' !== $value)
		{
			return $value;
		}

		$customerDetails = self::getCustomerDetails();

		if (!empty($customerDetails[$dbColumn]))
		{
			return $customerDetails[$dbColumn];
		}

		$credentialsPath = JPATH_ADMINISTRATOR . '/components/com_miniorange_oauth/secrets/credentials.php';

		if (is_file($credentialsPath))
		{
			$credentials = require $credentialsPath;

			if (is_array($credentials) && !empty($credentials[$dbColumn]))
			{
				return $credentials[$dbColumn];
			}
		}

		return '';
	}

}
