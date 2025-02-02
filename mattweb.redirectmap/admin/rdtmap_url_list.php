<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
use Bitrix\Main,
Bitrix\Main\Loader,
Bitrix\Main\Localization\Loc,
Mattweb\Redirectmap;

use \Bitrix\Main\Entity;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(Loc::GetMessage("ACCESS_DENIED"));
}

Loader::includeModule("mattweb.redirectmap");

$APPLICATION->SetTitle(Loc::GetMessage('RDTM_URL_LIST_TITLE'));

$sTableID = "tbl_rdtm_entity";
$oSort = new CAdminSorting($sTableID, "OLD_URL", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$arHeaders = array(
    array("id"=>"ID", "content"=>"ID", "sort"=>"ID", "default"=>true),
    array("id"=>"OLD_URL", "content"=>Loc::GetMessage('RDTM_ENTITY_OLD_URL_FIELD'), "sort"=>"OLD_URL", "default"=>true),
    array("id"=>"NEW_URL", "content"=>Loc::GetMessage('RDTM_ENTITY_NEW_URL_FIELD'), "sort"=>"NEW_URL", "default"=>true),
    array("id"=>"URL_NOTE", "content"=>Loc::GetMessage('RDTM_ENTITY_URL_NOTE_FIELD'), "sort"=>"URL_NOTE", "default"=>true),
);

$lAdmin->AddHeaders($arHeaders);

$rdmtEntity = Redirectmap\RedirectmapTable::getEntity();

$arID = $lAdmin->GroupAction();
if(!empty($arID) && is_array($arID)){
    $actionId = $lAdmin->GetAction(); 
    if($actionId !== null){
        foreach ($arID as $ID)
		{
			$ID = (int)$ID;

			if (!$ID)
			{
				continue;
			}

			switch ($actionId)
			{
				case "delete":
                    Redirectmap\RedirectmapTable::delete($ID);					
					break;
			}
		} 
    }
}



$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());

$getListOrder = ($by === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));

// select data
$query = new Entity\Query($rdmtEntity);
$query->setSelect($lAdmin->GetVisibleHeaderColumns());
$query->setOrder($getListOrder);

$rsData = $query->exec();


$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// build list
$lAdmin->NavText($rsData->GetNavPrint(Loc::GetMessage("PAGES")));

while($arRes = $rsData->NavNext(true, "f_")){
    $row = $lAdmin->AddRow($f_ID, $arRes);
	$can_edit = true;

	$arActions = Array();

    $arActions[] = array(
		"ICON"=>"edit",
		"TEXT"=>GetMessage($can_edit ? "MAIN_ADMIN_MENU_EDIT" : "MAIN_ADMIN_MENU_VIEW"),
		"ACTION"=>$lAdmin->ActionRedirect("rdtmap_url_edit.php?ID=".$f_ID)
	);

	$arActions[] = array(
		"ICON"=>"delete",
		"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
		"ACTION" => "if(confirm('".GetMessageJS('RDTM_ADMIN_DELETE_TEAM_CONFIRM')."')) ".
			$lAdmin->ActionRedirect("rdtmap_url_edit.php?action=delete&ID=".$f_ID.'&'.bitrix_sessid_get())
	);

	$row->AddActions($arActions);

}


// групповые действия
$lAdmin->AddGroupActionTable(Array(
	"delete"=>Loc::getMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
	/*"activate"=>Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"), 
	"deactivate"=>Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"), */
));

// резюме таблицы
$lAdmin->AddFooter(
	array(
		array("title"=>Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
		array("counter"=>true, "title"=>Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // счетчик выбранных элементов
	)
);

// menu
$aMenu = [];
$aMenu[] = [
    "TEXT" => GetMessage('RDTM_TEAMS_ADD_TITLE'),
    "TITLE" => GetMessage('RDTM_TEAMS_ADD_TITLE'),
    "LINK" => "rdtmap_url_edit.php?lang=" . LANGUAGE_ID,
    "ICON" => "btn_new",
];

$lAdmin->AddAdminContextMenu($aMenu);


/** Так ПРАВИЛЬНО добалять меню ↑↑↑ */

/** Так не нужно добалять меню ↓↓↓ */
//$adminContextMenu = new CAdminContextMenu($aMenu);
//$adminContextMenu->Show();


$lAdmin->CheckListMode();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");


/*$sql = $query->getQuery();
echo $sql;*/

/*$rdmtRes = $query->exec();

while($arR = $rdmtRes->fetch()){
    echo '<pre>';
    var_export($arR);
    echo '</pre>';
}*/

?>



