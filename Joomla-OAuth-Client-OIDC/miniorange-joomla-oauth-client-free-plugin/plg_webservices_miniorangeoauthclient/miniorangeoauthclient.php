<?php
/**
 * @package    Joomla.Plugin
 * @subpackage Webservices.miniorangeoauthclient
 *
 * @author    miniOrange Security Software Pvt. Ltd.
 * @copyright Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license   GNU General Public License version 3; see LICENSE.txt
 * @contact   info@xecurify.com
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\ApiRouter;

require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_oauth_utility.php';
require_once JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_miniorange_oauth' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'mo_customer_setup.php';

class PlgWebservicesMiniorangeoauthclient extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var   boolean
	 * @since 4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Registers com_content's API's routes in the application
	 *
	 * @param ApiRouter &$router The API Routing object
	 *
	 * @return void
	 *
	 * @since 4.0.0
	 */
	public function onBeforeApiRoute(&$router)
	{
		$router->createCRUDRoutes(
			'v1/miniorangeoauth',
			'miniorangeoauth',
			['com_miniorange_oauth'],
		);

		$this->handleOAuthClientRequest($router);
	}


	/**
	 * Create contenthistory routes
	 *
	 * @param ApiRouter &$router The API Routing object
	 *
	 * @return void
	 *
	 * @since 4.0.0
	 */
	public function handleOAuthClientRequest(&$router)
	{

		if (!MoOAuthUtility::loadMoOauthClientHandler())
		{
			return;
		}

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

		$queryParams = $input->getArray();

		if (isset($queryParams['error']) && isset($queryParams['error_description']))
		{
			$msg = "<strong>Error: </strong> " . $queryParams['error'] . "<br>" .
			   "<strong>Description: </strong> " . $queryParams['error_description'];
			MoOauthCustomer::pluginEfficiencyCheck('', '', '', '', '', '', '', '', $msg);
			echo $msg;
			exit();
		}

		$moOAuthClientHandler = new MoOauthClientHandler;

		if (isset($queryParams['morequest']) && $queryParams['morequest'] == 'testattrmappingconfig')
		{
			$moOAuthClientHandler->handleOAuthRequest($queryParams);
		}
		elseif (isset($queryParams['morequest']) && $queryParams['morequest'] == 'oauthredirect')
		{
			$moOAuthClientHandler->handleOAuthRequest($queryParams);
		}
		elseif (isset($queryParams['code']))
		{
			$moOAuthClientHandler->handleOAuthRequest($queryParams);
		}
	}
}
