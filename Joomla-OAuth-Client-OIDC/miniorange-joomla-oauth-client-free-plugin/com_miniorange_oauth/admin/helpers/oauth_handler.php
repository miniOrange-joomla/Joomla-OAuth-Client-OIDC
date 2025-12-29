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
    public $error;
    function __construct($error='')
    {
        $this->error=$error;
    }

    function getAccessToken($tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url,$in_header_or_body)
    {
        if(!MoOauthUtility::is_curl_installed()) {
            return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }

        $session = Factory::getSession();
        $ch = curl_init($tokenendpoint);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        
        if($in_header_or_body=='both') {
            curl_setopt(
                $ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($clientid . ":" . $clientsecret)
                )
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&client_id='.urlencode($clientid).'&client_secret='.urlencode($clientsecret).'&code='.$code);

        }
        elseif($in_header_or_body=='inHeader') {
            curl_setopt(
                $ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode($clientid . ":" . $clientsecret)
                )
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&code='.$code);
        }
        else{
            curl_setopt(
                $ch, CURLOPT_HTTPHEADER, array(
                'Accept: application/json'
                )
            );
                  curl_setopt($ch, CURLOPT_POSTFIELDS, 'redirect_uri='.urlencode($redirect_url).'&grant_type='.$grant_type.'&client_id='.$clientid.'&client_secret='.$clientsecret.'&code='.$code);
        }
        
        $content = curl_exec($ch);

        if(curl_error($ch)) {
            MoOAuthLogger::addLog('Error : ' . curl_error($ch), 'CRITICAL', 'MOOAUTH-A02');
            $this->setError('[MOOAUTH-A02] : ' . curl_error($ch));
            $session->set('mo_reason', curl_error($ch));
        }

        $content =json_decode($content, true);
        if(!is_array($content)) {
            MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
            $this->setError("[MOOAUTH-009] : Invalid response received.");
            $session->set('mo_reason', 'Invalid response received.');
        }

        // first check if any error received
        if(isset($content["error_description"])) {
            MoOAuthLogger::addLog('Error : ' . $content["error_description"], 'CRITICAL', 'MOOAUTH-A03');
            $this->setError("[MOOAUTH-A03] : " . $content["error_description"]);
            $session->set('mo_reason', $content["error_description"]);

        } else if(isset($content["error"])) {
            MoOAuthLogger::addLog('Error : ' . $content["error"], 'CRITICAL', 'MOOAUTH-A04');
            $this->setError("[MOOAUTH-A04] : " . $content["error"]);
            $session->set('mo_reason',  $content["error"]);
        }
        // extract access_token and id_token
        $idToken=isset($content["id_token"])?$content["id_token"]:'';
        $access_token=isset($content["access_token"])?$content["access_token"]:'';
        if(empty($idToken) && empty($access_token)) {
            MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
            $this->setError('[MOOAUTH-009] : ' . Text::_('COM_MINIORANGE_OAUTH_ACCESS_ID_TOKEN_MISSING') . Text::_('COM_MINIORANGE_OAUTH_ACCESS_ID_TOKEN_MISSING_SOLUTION'));
            $session->set('mo_reason', 'Invalid response received from OAuth Provider. Contact your administrator for more details.');
        }
        
        return array($access_token,$idToken);
    }
    
    function getResourceOwnerFromIdToken($id_token)
    {
        $session = Factory::getSession();
        $id_array = explode(".", $id_token);
        if(isset($id_array[1])) {
            $id_body = $this->base64url_decode($id_array[1]);
            if(is_array(json_decode($id_body, true))) {
                return json_decode($id_body, true);
            }
        }
        MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
        $this->setError('[MOOAUTH-009] : ' . Text::_('COM_MINIORANGE_OAUTH_INVALID_ID_TOKEN') . $id_token);
        $session->set('mo_reason', ' Invalid response received.<br><b>Id_token : </b>'.$id_token);
        return false;
    }

    function getResourceOwner($resourceownerdetailsurl, $access_token,$idToken)
    {
        $session = Factory::getSession();
        if(!MoOauthUtility::is_curl_installed()) {
            return json_encode(array("status"=>'CURL_ERROR','statusMessage'=>'<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.'));
        }
        if(!empty($idToken) && !is_null($idToken)) {
            return $this->getResourceOwnerFromIdToken($idToken);
        }
        $ch = curl_init($resourceownerdetailsurl);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt(
            $ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '.$access_token,
            'User-Agent:web'
            )
        );    
        $content = curl_exec($ch);
        
        if(curl_error($ch)) {
            $this->setError(curl_error($ch));
            $session->set('mo_reason', curl_error($ch));
            return false;
        }
        $content = json_decode($content, true);
        if(!is_array($content)) {
            MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
            $this->setError("[MOOAUTH-009] : " . Text::_('COM_MINIORANGE_OAUTH_INVALID_RESPONSE'));
            $session->set('mo_reason', "Invalid response received.");
            return false;
        }
        
        if(isset($content["error_description"])) {
            MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
            $this->setError('[MOOAUTH-009] : ' . $content["error_description"]);
            $session->set('mo_reason', '[MOOAUTH-009]' .$content["error_description"]);
            return false;
        } else if(isset($content["error"])) {
            MoOAuthLogger::addLog('Error Invalid Response', 'ERROR');
            $this->setError('[MOOAUTH-009] : '. $content["error"]);
            $session->set('mo_reason', '[MOOAUTH-009 : ]'. $content["error"]);
            return false;
        } 
        return $content;
    }

    function base64url_decode($data) 
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        $data = strtr($data, '-_', '+/');
        return base64_decode($data);
    }

    function setError($error)
    {
        $this->error=$error;
    }
    
    function isError()
    {
        if(empty($this->error)) {
            return false;
        }
        return true;
    }

    function printError()
    {
        if(!$this->isError()) {
            return;
        }

        if(is_array($this->error)) {
            print_r($this->error);
        } else {
            echo($this->error);
        }

        echo Text::_('COM_MINIORANGE_OAUTH_LOGS_SUGGESTION');
        exit;
    }

    function showFormattedErrorMessage(string $errorMessage, string $description = '')
    {
        $body = "<p style='margin:0 0 10px; font-weight:bold; font-size:16px;'>" . htmlspecialchars($errorMessage) . "</p>";

        if (!empty($description)) {
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