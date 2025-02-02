<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('mattweb.redirectmap'))
{
	return false;
}

return array(
    "parent_menu" => "global_menu_services",
    'section' => '',
	"sort" => 10,
    "text" => Loc::GetMessage("ADM_MENU_HEADER_TXT"),
    "title" => Loc::GetMessage("ADM_MENU_HEADER_TITLE"),
    "url" => "rdtmap_admin_index.php?lang=".LANGUAGE_ID,
	"icon" => "util_menu_icon",
	"page_icon" => "util_page_icon",
	"items_id" => "menu_rdtmapadmin",
    "items" => array(
        array(
            "text" => Loc::GetMessage("ADM_MENU_URL_LIST_TXT"),
            "url" => "/bitrix/admin/rdtmap_url_list.php?lang=".LANGUAGE_ID,
            "more_url" => array(),
            "title" => Loc::GetMessage("ADM_MENU_URL_LIST_TXT"), 
        ),
        array(
            "text" => Loc::GetMessage("ADM_MENU_CSV_IMPORT_TXT"),
            "url" => "/bitrix/admin/rdtmap_import_csv.php?lang=".LANGUAGE_ID,
            "more_url" => array(),
            "title" => Loc::GetMessage("ADM_MENU_CSV_IMPORT_TXT"), 
        ),
    )
);