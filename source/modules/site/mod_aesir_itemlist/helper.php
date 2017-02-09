<?php
/**
 * @package     Reditem.Module
 * @subpackage  Frontend.mod_aesir_items_gmap
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('reditem.library');

use Aesir\Helper\ItemsModuleHelper;

/**
 * Module helper for placing category items on Google map.
 *
 * @since  1.0.0
 */
class ModAesirItemlistHelper extends ItemsModuleHelper
{
	/**
	 * Filter the module items through AJAX.
	 *
	 * @return  void
	 */
	public static function filterAjax()
	{
		ReditemHelperAjax::validateAjaxRequest();

		JLoader::registerPrefix('ModAesirItems', __DIR__);

		$app = JFactory::getApplication();

		$moduleId = $app->input->getInt('module_id');

		if (!$moduleId)
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo JText::_('MOD_AESIR_ITEMS_ERROR_INVALID_DATA_RECEIVED');
			$app->close();
		}

		$module = ReditemEntityModule::load($moduleId);

		if (!$module->isLoaded())
		{
			$app->setHeader('status', 422);
			$app->sendHeaders();
			echo JText::sprintf('MOD_AESIR_ITEMS_ERROR_MODULE_NOT_FOUND', $moduleId);
			$app->close();
		}

		$modelState = array();

		$search = $app->input->getString('search');
		$category = $app->input->get('category', array(), 'ARRAY');
		$type = $app->input->get('types', array(), 'ARRAY');

		if ($search)
		{
			$modelState['filter.search'] = $search;
		}

		$modelState['filter.category_ancestor'] = $category;
		$modelState['filter.type'] = $type;
		$modelState['filter.field_value'] = self::getFilterFieldValuesFromRequest();

		$module = new ModAesirItemsModule($module->getParams());

		$items = $module->getItems($modelState);

		$items = array_values($items->toObjects());

		$items = array_map('self::filterItemProperties', $items);

		echo json_encode($items);

		$app->close();
	}
}
