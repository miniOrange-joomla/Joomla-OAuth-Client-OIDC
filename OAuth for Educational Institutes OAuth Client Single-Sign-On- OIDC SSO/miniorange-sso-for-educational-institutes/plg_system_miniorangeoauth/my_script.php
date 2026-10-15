<?php

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Script file of miniorange_oauth_system_plugin.
 *
 * The name of this class is dependent on the component being installed.
 * The class name should have the component's name, directly followed by
 * the text InstallerScript (ex:. com_helloWorldInstallerScript).
 *
 * This class will be called by Joomla!'s installer, if specified in your component's
 * manifest file, and is used for custom automation actions in its installation process.
 *
 * In order to use this automation script, you should reference it in your component's
 * manifest file as follows:
 * <scriptfile>script.php</scriptfile>
 *

 *
 * Script file of miniorange_oauth_plugin.
 *
 * @author    miniOrange Security Software Pvt. Ltd.
 * @copyright Copyright (C) 2015 miniOrange (https://www.miniorange.com)
 * @license   GNU General Public License version 3; see LICENSE.txt
 * @contact   info@xecurify.com
 */

class PlgSystemMiniorangeoauthInstallerScript
{
	private $tableColumnCache = [];

	/**
	 * This method is called after a component is installed.
	 *
	 * @param \stdClass $parent - Parent object calling this method.
	 *
	 * @return void
	 */
	public function install($parent)
	{
		$db = $this->getDatabase();

		$query = $db->getQuery(true);
		$query->update('#__extensions');
		$query->set($db->quoteName('enabled') . ' = 1');
		$query->where($db->quoteName('element') . ' = ' . $db->quote('miniorangeoauth'));
		$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * This method is called after a component is uninstalled.
	 *
	 * @param \stdClass $parent - Parent object calling this method.
	 *
	 * @return void
	 */
	public function uninstall($parent)
	{
		// Echo '<p>' . Text::_('COM_HELLOWORLD_UNINSTALL_TEXT') . '</p>';
	}

	/**
	 * This method is called after a component is updated.
	 *
	 * @param \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function update($parent)
	{
		$this->addColumnIfMissing(
			'#__miniorange_oauth_config',
			'update_email_on_sso',
			'TINYINT(1) NOT NULL DEFAULT 0'
		);
	}

	private function addColumnIfMissing($tableName, $columnName, $columnDefinition)
	{
		$tableName = (string) $tableName;
		$columnName = (string) $columnName;
		$columnDefinition = trim((string) $columnDefinition);

		if ($tableName === '' || $columnName === '' || $columnDefinition === '')
		{
			return;
		}

		if (!preg_match('/^#__[A-Za-z0-9_]+$/', $tableName) || !preg_match('/^[A-Za-z0-9_]+$/', $columnName))
		{
			return;
		}

		if (preg_match('/[;]|--|\/\*|\*\//', $columnDefinition))
		{
			return;
		}

		$db = $this->getDatabase();
		$cacheKey = strtolower($tableName);
		$columnKey = strtolower($columnName);

		if (!array_key_exists($cacheKey, $this->tableColumnCache))
		{
			$this->tableColumnCache[$cacheKey] = $this->loadTableColumns($db, $tableName);
		}

		if (!is_array($this->tableColumnCache[$cacheKey]) || isset($this->tableColumnCache[$cacheKey][$columnKey]))
		{
			return;
		}

		try
		{
			$query = 'ALTER TABLE ' . $db->quoteName($tableName)
				. ' ADD COLUMN ' . $db->quoteName($columnName) . ' ' . $columnDefinition;
			$db->setQuery($query);
			$db->execute();
		}
		catch (\Exception $exception)
		{
			if (!$this->isDuplicateColumnException($exception))
			{
				throw $exception;
			}
		}

		$this->tableColumnCache[$cacheKey][$columnKey] = true;
	}

	private function loadTableColumns($db, $tableName)
	{
		$columns = [];

		try
		{
			$loaded = $db->getTableColumns($tableName);
		}
		catch (\Exception $exception)
		{
			if ($this->isMissingTableException($exception))
			{
				return null;
			}

			throw $exception;
		}

		if (!is_array($loaded))
		{
			return $columns;
		}

		foreach (array_keys($loaded) as $existingColumn)
		{
			$columns[strtolower((string) $existingColumn)] = true;
		}

		return $columns;
	}

	private function isDuplicateColumnException($exception)
	{
		$current = $exception;

		while ($current instanceof \Exception)
		{
			$code = (string) $current->getCode();

			if ($code === '1060' || $code === '42S21')
			{
				return true;
			}

			$message = $current->getMessage();

			if (stripos($message, 'Duplicate column') !== false || stripos($message, '42S21') !== false)
			{
				return true;
			}

			$current = $current->getPrevious();
		}

		return false;
	}

	private function isMissingTableException($exception)
	{
		$current = $exception;

		while ($current instanceof \Exception)
		{
			$code = (string) $current->getCode();

			if ($code === '1146' || $code === '42S02')
			{
				return true;
			}

			$message = $current->getMessage();

			if (stripos($message, 'Base table or view not found') !== false || stripos($message, "doesn't exist") !== false)
			{
				return true;
			}

			$current = $current->getPrevious();
		}

		return false;
	}

	private function getDatabase()
	{
		$app = Factory::getApplication();

		if (method_exists($app, 'getDatabase'))
		{
			return $app->getDatabase();
		}

		if (method_exists(Factory::class, 'getDbo'))
		{
			return Factory::getDbo();
		}

		return Factory::getContainer()->get('Joomla\\Database\\DatabaseInterface');
	}

	/**
	 * Runs just before any installation action is performed on the component.
	 * Verifications and pre-requisites should run in this function.
	 *
	 * @param string    $type   - Type of PreFlight action. Possible values are:
	 *                          - * install
	 *                          - * update
	 *                          - * discover_install
	 * @param \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function preflight($type, $parent)
	{
		// Echo '<p>' . Text::_('COM_HELLOWORLD_PREFLIGHT_' . $type . '_TEXT') . '</p>';
	}

	/**
	 * Runs right after any installation action is performed on the component.
	 *
	 * @param string    $type   - Type of PostFlight action. Possible values are:
	 *                          - * install
	 *                          - * update
	 *                          - * discover_install
	 * @param \stdClass $parent - Parent object calling object.
	 *
	 * @return void
	 */
	public function postflight($type, $parent)
	{
		// Echo '<p>' . Text::_('COM_HELLOWORLD_POSTFLIGHT_' . $type . '_TEXT') . '</p>';
	}
}
