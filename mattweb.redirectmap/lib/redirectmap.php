<?php
namespace Mattweb\Redirectmap;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class RedirectmapTable
 * 
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> old_url string(255) mandatory
 * <li> new_url string(255) mandatory
 * <li> url_note string(255) mandatory
 * </ul>
 *
 * @package Mattweb\Redirectmap
 **/

class RedirectmapTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'mw_redirectmap';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('REDIRECTMAP_ENTITY_ID_FIELD'),
				]
			),
			new StringField(
				'OLD_URL',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('REDIRECTMAP_ENTITY_OLD_URL_FIELD'),
				]
			),
			new StringField(
				'NEW_URL',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('REDIRECTMAP_ENTITY_NEW_URL_FIELD'),
				]
			),
			new StringField(
				'URL_NOTE',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
					'title' => Loc::getMessage('REDIRECTMAP_ENTITY_URL_NOTE_FIELD'),
				]
			),
		];
	}
}