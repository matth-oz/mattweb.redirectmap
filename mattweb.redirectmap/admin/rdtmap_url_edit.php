<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
use Bitrix\Main,
\Bitrix\Main\Entity,
Bitrix\Main\Loader,
Bitrix\Main\Localization\Loc,
Mattweb\Redirectmap;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(Loc::GetMessage("ACCESS_DENIED"));
}

Loader::includeModule("mattweb.redirectmap");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

// form
$aTabs = array(
	array(
		'DIV' => 'edit1',
		'TAB' => Loc::GetMessage('RDTM_ADMIN_URL_TITLE'),
		'TITLE' => Loc::GetMessage('RDTM_ADMIN_URL_TITLE')
	)
);

$tabControl = new CAdminTabControl('tabControl', $aTabs);

// init vars
$is_create_form = true;
$is_update_form = false;
$isEditMode = true;
$errors = array();

$ID = (int)$request->get('ID');
$save = trim((string)$request->get('save'));
$apply = trim((string)$request->get('apply'));
$action = trim((string)$request->get('action'));
$requestMethod = $request->getRequestMethod();

if($ID > 0){
    $rdmtEntity = Redirectmap\RedirectmapTable::getEntity();
    $query = new Entity\Query($rdmtEntity); 
    $query->setSelect(['ID', 'OLD_URL', 'NEW_URL', 'URL_NOTE']);
    $query->setFilter(['=ID' => $ID]);   
   
    $rsData = $query->exec();
    $arRdmt = $rsData->fetch();

    if(!empty($arRdmt)){
        $is_create_form = false;
        $is_update_form = true;
    }
}

// default values for create form / page title
if ($is_create_form){
	$rdmt = array_fill_keys(array('ID', 'OLD_URL', 'NEW_URL', 'URL_NOTE'), '');
	$APPLICATION->SetTitle(Loc::GetMessage('RDTM_ADMIN_URL_EDIT_PAGE_TITLE_NEW'));
}
else{
    $APPLICATION->SetTitle(Loc::GetMessage('RDTM_ADMIN_URL_EDIT_PAGE_TITLE_EDIT'));

    $rdmtEntity = Redirectmap\RedirectmapTable::getEntity();
    $query = new Entity\Query($rdmtEntity); 
    $query->setSelect(['ID', 'OLD_URL', 'NEW_URL', 'URL_NOTE']);
    $query->setFilter(['=ID' => $ID]);
    $query->countTotal(true);

    $rsData = $query->exec();

    $arRdmt['ROWS_COUNT'] = $rsData->getCount();
}

// delete action
if ($is_update_form && $action === 'delete' && check_bitrix_sessid()){
    $result = Redirectmap\RedirectmapTable::delete($arRdmt['ID']);
	if ($result->isSuccess())
	{
		\LocalRedirect('rdtmap_url_list.php?lang='.LANGUAGE_ID);
	}
	else
	{
		$errors = $result->getErrorMessages();
	}
}

// save action
if (($save != '' || $apply != '') && $requestMethod == 'POST' && check_bitrix_sessid())
{
    $data = array(
        'OLD_URL' => trim($request['OLD_URL']),
        'NEW_URL' => trim($request['NEW_URL']),
        'URL_NOTE' => trim($request['URL_NOTE']),
    );

    if ($is_update_form){
        $result = Redirectmap\RedirectmapTable::update($ID, $data);
    }
    else{
        $result = Redirectmap\RedirectmapTable::add($data);
        $ID = $result->getId();
    }

    if ($result->isSuccess())
	{
        if ($save != '')
		{
			\LocalRedirect('rdtmap_url_list.php?lang='.LANGUAGE_ID);
		}
		else
		{
			\LocalRedirect('rdtmap_url_list.php?ID='.$ID.'&lang='.LANGUAGE_ID.'&'.$tabControl->ActiveTabParam());
		}
    }
    else
	{
		$errors = $result->getErrorMessages();
	}

    // rewrite original value by form value to restore form
	foreach ($data as $k => $v)
	{
		$arRdmt[$k] = $v;
	}
}

// view
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

// menu
$aMenu = array(
	array(
		'TEXT'	=> GetMessage('RDTM_ADMIN_ROWS_RETURN_TO_LIST_BUTTON'),
		'TITLE'	=> GetMessage('RDTM_ADMIN_ROWS_RETURN_TO_LIST_BUTTON'),
		'LINK'	=> 'rdtmap_url_list.php?lang='.LANGUAGE_ID,
		'ICON'	=> 'btn_list',
	)
);

$adminContextMenu = new CAdminContextMenu($aMenu);
$adminContextMenu->Show();

if (!empty($errors))
{
	CAdminMessage::ShowMessage(join("\n", $errors));
}
?>
<form name="form1" method="POST" action="<?=$APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="ID" value="<?= htmlspecialcharsbx($arRdmt['ID'])?>">
	<input type="hidden" name="lang" value="<?= LANGUAGE_ID?>">
    <?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
    <tr>
        <td width="40%"><strong><?=GetMessage('RDTM_ADMIN_OLD_URL_ENTITY_ID_FIELD')?></strong></td>
        <td><?
			if (!$isEditMode):
				?><input type="text" name="OLD_URL" size="30" value="" /><?
			else:
				?><input type="text" name="OLD_URL" size="30" value="<?= htmlspecialcharsbx($arRdmt['OLD_URL'])?>"><?
			endif;
		?></td>
    </tr>
    <tr>
        <td width="40%"><strong><?=GetMessage('RDTM_ADMIN_NEW_URL_ENTITY_ID_FIELD')?></strong></td>
        <td><?
			if (!$isEditMode):
				?><input type="text" name="NEW_URL" size="30" value="" /><?
			else:
				?><input type="text" name="NEW_URL" size="30" value="<?= htmlspecialcharsbx($arRdmt['NEW_URL'])?>"><?
			endif;
		?></td>
    </tr>
    <tr>
        <td width="40%"><strong><?=GetMessage('RDTM_ADMIN_URL_NOTE_ENTITY_ID_FIELD')?></strong></td>
        <td><?
			if (!$isEditMode):
				?><input type="text" name="URL_NOTE" size="30" value="" /><?
			else:
				?><input type="text" name="URL_NOTE" size="30" value="<?= htmlspecialcharsbx($arRdmt['URL_NOTE'])?>"><?
			endif;
		?></td>
    </tr>
    <?
	$tabControl->Buttons(array('disabled' => !$isEditMode, 'back_url' => 'rdtmap_url_edit.php?lang='.LANGUAGE_ID));
	$tabControl->End();
	?>
</form>
<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
?>