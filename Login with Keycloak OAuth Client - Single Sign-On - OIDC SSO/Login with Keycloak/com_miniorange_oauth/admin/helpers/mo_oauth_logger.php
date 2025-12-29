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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die;

class MoOAuthLogger
{
    public static function addLog($message, $type = 'INFO', $category = 'miniorange_oauth_logs')
    {
        // Check if logging is enabled
        if (!self::isLoggingEnabled()) {
            return;
        }
        
        static $loggerInitialized = false;
        if (!$loggerInitialized) {
            Log::addLogger(array('text_file' => 'miniorange_oauth_logs.log', 'text_entry_format' => '{DATE} {TIME} {CATEGORY} [{PRIORITY}] {MESSAGE}'), Log::ALL, array($category));
            $loggerInitialized = true;
        }

        $priorityMap = [
            'INFO'     => Log::INFO,
            'NOTICE'   => Log::NOTICE,
            'WARNING'  => Log::WARNING,
            'ERROR'    => Log::ERROR,
            'ALERT'    => Log::ALERT,
            'CRITICAL' => Log::CRITICAL,
        ];

        $priority = $priorityMap[$type] ?? Log::INFO;

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = $trace[1] ?? $trace[0];

        $file = $caller['file'] ?? 'Unknown file';
        $function = $caller['function'] ?? 'Unknown function';
        $line = $caller['line'] ?? 'Unknown line';

        $maxMessageLength = 1000;

        if (strlen($message) > $maxMessageLength) {
            $message = substr($message, 0, $maxMessageLength) . '... [truncated]';
        }

        $formattedMessage = sprintf("[%s:%s] [%s] - %s", basename($file), $line, $function, $message);

        Log::add($formattedMessage, $priority, $category);

        self::saveLogToDatabase($message, $type, basename($file), $line, $function);
    }
    
    private static function saveLogToDatabase($message, $type = 'info', $file = null, $line = null, $function = null)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $maxLogs = 10000; // currently unused in your code

        $logCode = self::getLogCode($message);

        $fields = array(
        $db->quoteName('timestamp')    . ' = ' . $db->quote(date('Y-m-d H:i:s')),
        $db->quoteName('log_level')    . ' = ' . $db->quote($type),
        $db->quoteName('message')      . ' = ' . $db->quote(json_encode($logCode)),
        $db->quoteName('file')         . ' = ' . $db->quote($file),
        $db->quoteName('line_number')  . ' = ' . $db->quote($line),
        $db->quoteName('function_call'). ' = ' . $db->quote($function)
        );

        $query->insert($db->quoteName('#__miniorange_oauth_logs'))->set($fields);

        $db->setQuery($query);
        $db->execute();

        $query = $db->getQuery(true)->select('COUNT(*)')->from($db->quoteName('#__miniorange_oauth_logs'));

        $db->setQuery($query);
        $totalLogs = (int)$db->loadResult();

        // Delete the oldest logs if the limit is exceeded
        if ($totalLogs > $maxLogs) {
            $logsToDelete = $totalLogs - $maxLogs;
            $query = $db->getQuery(true)->delete($db->quoteName('#__miniorange_oauth_logs'))->order($db->quoteName('timestamp') . ' ASC')->setLimit($logsToDelete);

            $db->setQuery($query);
            $db->execute();
        }
    }


    private static function getLogCode(string $message)
    {
        $app = Factory::getApplication();
        $language = $app->getLanguage();
        $language->load('com_miniorange_oauth', JPATH_ADMINISTRATOR, null, false, true);

        $logDetails = [
            'Client ID, Client secret or scope is missing'  => ['code' => 'MOOAUTH-001', 'issue' => Text::_('COM_MINIORANGE_OAUTH_LOGS_ISSUE_MOOAUTH_001')],
            'SSO is Disable'                                => ['code' => 'MOOAUTH-002', 'issue' => Text::_('COM_MINIORANGE_OAUTH_LOGS_ISSUE_MOOAUTH_002')],
            'No request found for this application'         => ['code' => 'MOOAUTH-003', 'issue' => Text::_('COM_MINIORANGE_OAUTH_LOGS_ISSUE_MOOAUTH_003')],
            'Application not configured'                    => ['code' => 'MOOAUTH-004', 'issue' => Text::_('COM_MINIORANGE_OAUTH_LOGS_ISSUE_MOOAUTH_004')],
            'Authentication limit reached'                  => ['code' => 'MOOAUTH-005', 'issue' => Text::_('COM_MINIORANGE_OAUTH_LOGS_ISSUE_MOOAUTH_005')],
            'Auto creation not available'                   => ['code' => 'MOOAUTH-006', 'issue' => Text::_('COM_MINIORANGE_OAUTH_LOGS_ISSUE_MOOAUTH_006')],
            'Test Configuration Success'                    => ['code' => 'MOOAUTH-007', 'issue' => Text::_('COM_MINIORANGE_OAUTH_LOGS_ISSUE_MOOAUTH_007')],
            'email not received'                            => ['code' => 'MOOAUTH-008', 'issue' => Text::_('COM_MINIORANGE_OAUTH_LOGS_ISSUE_MOOAUTH_008')],
            'Error Invalid Response'                        => ['code' => 'MOOAUTH-009', 'issue' => Text::_('COM_MINIORANGE_OAUTH_LOGS_ISSUE_MOOAUTH_009')],
        ];
        
        if (isset($logDetails[$message])) {
            return $logDetails[$message];
        }
        
        // Otherwise, return a generated code with the original message as the issue
        return [
            'code' => 'MOOAUTH_' . str_pad(mt_rand(100, 999), 3, '0', STR_PAD_LEFT),
            'issue' => $message
        ];
    }

    public static function getAllLogs()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)->select($db->quoteName(['timestamp', 'log_level', 'message', 'file', 'line_number', 'function_call']))->from($db->quoteName('#__miniorange_oauth_logs'))->order($db->quoteName('timestamp') . ' DESC');

        return $db->setQuery($query)->loadObjectList() ?: [];
    }

    private static function isLoggingEnabled()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('loggers_enable'))
            ->from($db->quoteName('#__miniorange_oauth_config'))
            ->setLimit(1);

        $db->setQuery($query);
        $result = $db->loadResult();

        return (bool)$result;
    }
}
