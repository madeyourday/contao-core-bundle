<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao;


/**
 * Class BackendFile
 *
 * Back end file picker.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class BackendFile extends \Backend
{

	/**
	 * Current Ajax object
	 * @var object
	 */
	protected $objAjax;


	/**
	 * Initialize the controller
	 *
	 * 1. Import the user
	 * 2. Call the parent constructor
	 * 3. Authenticate the user
	 * 4. Load the language files
	 * DO NOT CHANGE THIS ORDER!
	 */
	public function __construct()
	{
		$this->import('BackendUser', 'User');
		parent::__construct();

		$this->User->authenticate();
		\System::loadLanguageFile('default');
	}


	/**
	 * Run the controller and parse the template
	 */
	public function run()
	{
		$objTemplate = new \BackendTemplate('be_picker');
		$objTemplate->main = '';

		// Ajax request
		if ($_POST && \Environment::get('isAjaxRequest'))
		{
			$this->objAjax = new \Ajax(\Input::post('action'));
			$this->objAjax->executePreActions();
		}

		$strTable = \Input::get('table');
		$strField = \Input::get('field');

		// Define the current ID
		define('CURRENT_ID', (\Input::get('table') ? $this->Session->get('CURRENT_ID') : \Input::get('id')));

		$this->loadDataContainer($strTable);
		$strDriver = 'DC_' . $GLOBALS['TL_DCA'][$strTable]['config']['dataContainer'];
		$objDca = new $strDriver($strTable);
		$objDca->field = $strField;

		// Set the active record
		if ($this->Database->tableExists($strTable))
		{
			$strModel = \Model::getClassFromTable($strTable);
			$objModel = $strModel::findByPk(\Input::get('id'));

			if ($objModel !== null)
			{
				$objDca->activeRecord = $objModel;
			}
		}

		// AJAX request
		if ($_POST && \Environment::get('isAjaxRequest'))
		{
			$this->objAjax->executePostActions($objDca);
		}

		$this->Session->set('filePickerRef', \Environment::get('request'));
		$arrValues = array_filter(explode(',', \Input::get('value')));

		// Convert UUIDs to binary
		foreach ($arrValues as $k=>$v)
		{
			// Can be a UUID or a path
			if (\Validator::isStringUuid($v))
			{
				$arrValues[$k] = \String::uuidToBin($v);
			}
		}

		// Call the load_callback
		if (is_array($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['load_callback']))
		{
			foreach ($GLOBALS['TL_DCA'][$strTable]['fields'][$strField]['load_callback'] as $callback)
			{
				if (is_array($callback))
				{
					$this->import($callback[0]);
					$arrValues = $this->$callback[0]->$callback[1]($arrValues, $objDca);
				}
				elseif (is_callable($callback))
				{
					$arrValues = $callback($arrValues, $objDca);
				}
			}
		}

		// Prepare the widget
		$class = $GLOBALS['BE_FFL']['fileSelector'];
		$objFileTree = new $class($class::getAttributesFromDca($GLOBALS['TL_DCA'][$strTable]['fields'][$strField], $strField, $arrValues, $strField, $strTable, $objDca));

		$objTemplate->main = $objFileTree->generate();
		$objTemplate->theme = \Backend::getTheme();
		$objTemplate->base = \Environment::get('base');
		$objTemplate->language = $GLOBALS['TL_LANGUAGE'];
		$objTemplate->title = specialchars($GLOBALS['TL_LANG']['MSC']['filepicker']);
		$objTemplate->charset = \Config::get('characterSet');
		$objTemplate->addSearch = false;
		$objTemplate->search = $GLOBALS['TL_LANG']['MSC']['search'];
		$objTemplate->action = ampersand(\Environment::get('request'));
		$objTemplate->value = $this->Session->get('file_selector_search');
		$objTemplate->manager = $GLOBALS['TL_LANG']['MSC']['fileManager'];
		$objTemplate->managerHref = 'contao/main.php?do=files&amp;popup=1';
		$objTemplate->breadcrumb = $GLOBALS['TL_DCA']['tl_files']['list']['sorting']['breadcrumb'];

		if (\Input::get('switch'))
		{
			$this->Template->switch = $GLOBALS['TL_LANG']['MSC']['pagePicker'];
			$this->Template->switchHref = str_replace('contao/file.php', 'contao/page.php', ampersand(\Environment::get('request')));
		}

		\Config::set('debugMode', false);
		$objTemplate->output();
	}
}
