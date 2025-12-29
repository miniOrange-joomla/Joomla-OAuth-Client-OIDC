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
       
        jimport('miniorangeoauthplugin.utility.MoOauthClientHandler');
        $app = Factory::getApplication();

        if (method_exists($app, 'getInput')) {
            $input = $app->getInput();
        } else { // Joomla 3
            $input = $app->input;
        }

        $queryParams = $input->getArray();
        
        if(isset($queryParams['error']) && isset($queryParams['error_description']))
        {
            $msg = "<strong>Error: </strong> " . $queryParams['error'] . "<br>" .
               "<strong>Description: </strong> " . $queryParams['error_description'];
            echo $msg; 
            exit();
        }
        
        $moOAuthClientHandler = new MoOauthClientHandler();

        if (isset($queryParams['morequest']) and $queryParams['morequest'] == 'testattrmappingconfig') {
            $moOAuthClientHandler->handleOAuthRequest($queryParams);
        }
        else if (isset($queryParams['morequest']) and $queryParams['morequest'] == 'oauthredirect') {
            $moOAuthClientHandler->handleOAuthRequest($queryParams);
        }
        else if (isset($queryParams['code'])) {
            $moOAuthClientHandler->handleOAuthRequest($queryParams);
        }
    }
}
