<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

// check rights
if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(Loc::getMessage('ADMIN_TOOLS_ACCESS_DENIED'));
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

//$request = HttpApplication::getInstance()->getContext()->getRequest();

$module_id = htmlspecialcharsbx($request["mid"] != "" ? $request["mid"] : $request["id"]);

$arSites = [];
$db_res = CSite::GetList($by , $sort ,array("ACTIVE"=>"Y"));
while( $res = $db_res->Fetch() ){
    $arSites[] = $res;
}

$arImportOptions = [
    ['save_errors_to_log', Loc::GetMessage('RDTM_SAVE_ERRORS_TO_LOG'), ["checkbox", "Y"]],
    ['import_log_fpath', Loc::GetMessage('RDTM_IMPORT_LOG_FILE_PATH'), ["text", 30]],
    ['cache_time', Loc::GetMessage('RDTM_CACHE_TIME'), ["text", 30]],
];

foreach($arSites as $key => $arSite){
    $arTabs[] = [
        "DIV" => "edit_1",
        "TAB" => Loc::GetMessage("RDTM_MAIN_OPTIONS"),
        "ICON" => "settings",
        "TITLE" => Loc::GetMessage("RDTM_MAIN_OPTIONS_TITLE"),
        "PAGE_TYPE" => "site_settings",
        "SITE_ID" => $arSite["ID"],
        "OPTIONS" => [
            "IMPORT" => $arImportOptions,
        ]
    ];
}

$tabControl = new CAdminTabControl("tabControl", $arTabs);
Loader::includeModule($module_id);

if ($request->isPost() && check_bitrix_sessid()) {
    if (isset($request["RestoreDefaults"])){
        Option::delete($module_id);
    }
    else{
        foreach($arTabs as $arTab){

            foreach ($arTab["OPTIONS"] as $arOptions) {
                foreach ($arOptions as $arOption) {
                    if (!is_array($arOption)) {
                        continue;
                    }

                    $name = $arOption[0];
                    $val = trim($request[$name], " \t\n\r");
                    if ($arOption[2][0] == "checkbox" && $val != "Y") $val = "N";
                    Option::set($module_id, $name, $val);
                }
            }
        }
    }
}
?>
<form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
<?
    $tabControl->Begin();
    foreach($arTabs as $arTab) {
        if ($arTab["OPTIONS"]) {
            $tabControl->BeginNextTab();       
        }
        ?>
        <?foreach($arTab["OPTIONS"] as $arrOptKey => $arrOptValues){?>
            <tr class="heading">
                <td colspan="2"><?=GetMessage("RDTM_".$arrOptKey."_OPTIONS_TITLE")?></td>
            </tr>
            <?
            foreach($arrOptValues as $optKey => $arOption) {
                $val = Option::get($module_id, $arOption[0], "");
                $type = $arOption[2];
                ?>
                <tr>
                <td width="40%" nowrap <?if($type[0]=="textarea" || isset($type[4])) echo 'class="adm-detail-valign-top"'?>>
                    <label for="<?echo htmlspecialcharsbx($arOption[0])?>"><?echo $arOption[1]?>:</label>
                </td>
                <td width="60%">
                <?if($type[0]=="checkbox"):?>
                        <input type="checkbox" name="<?echo htmlspecialcharsbx($arOption[0])?>" id="<?echo htmlspecialcharsbx($arOption[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
                    <?elseif($type[0]=="text"):?>
                        <input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($arOption[0])?>" id="<?echo htmlspecialcharsbx($arOption[0])?>">
                    <?elseif($type[0]=="textarea"):?>
                        <textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($arOption[0])?>" id="<?echo htmlspecialcharsbx($arOption[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
                    <?elseif($type[0]=="file"):?>
                        <?CAdminFileDialog::ShowScript(Array
                        (
                        "event" => "OpenImage_".$optKey,
                        "arResultDest" => Array("FUNCTION_NAME" => "SetImageUrl"),
                        "arPath" => Array(),
                        "select" => 'F',
                        "operation" => 'O',
                        "showUploadTab" => true,
                        "showAddToMenuTab" => false,
                        "fileFilter" => 'image',
                        "allowAllFiles" => true,
                        "saveConfig" => true
                        )
                        );?>
                        <input id="<?echo htmlspecialcharsbx($arOption[0])?>_<?=$optKey?>" name="<?echo htmlspecialcharsbx($arOption[0])?>" size="<?echo $type[1]?>" value="<?echo htmlspecialcharsbx($val)?>" type="text" />
                        <input value="<?echo htmlspecialcharsbx($type[3])?>" type="button" onclick="window.OpenImage_<?=$optKey?>()" />
                        <script>
                            var SetImageUrl = function(filename, filepath)
                            {
                                var oInput = BX('<?echo htmlspecialcharsbx($arOption[0])?>_<?=$optKey?>');

                                if (typeof filename == 'object')
                                    oInput.value = filename.src;
                                else
                                    oInput.value = (filepath + '/' + filename).replace(/\/\//ig, '/');
                            }
                        </script>
                    <?elseif($type[0]=="selectbox"):
                        ?><select name="<?echo htmlspecialcharsbx($arOption[0])?>"><?
                        foreach($type[1] as $key => $value):
                            ?><option value="<?echo htmlspecialcharsbx($key)?>"<?if($key==$val) echo ' selected="selected"'?>><?echo htmlspecialcharsEx($value)?></option><?
                        endforeach;
                        ?></select><?
                    endif?>
                    <?if(isset($type[4])){?>
                    <div class="notes"><?=htmlspecialcharsbx($type[4]);?></div>
                    <?}?>
                </td>
            <?}?>
        <?}?>
    <?}?>
    <?$tabControl->Buttons();?>
    <input type="submit" name="Update" value="<?=Loc::GetMessage("MAIN_SAVE")?>" title="<?=Loc::GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
    <input type="submit" name="Apply" value="<?=Loc::GetMessage("MAIN_OPT_APPLY")?>" title="<?=Loc::GetMessage("MAIN_OPT_APPLY_TITLE")?>">
    <input type="hidden" name="Update" value="Y">
    <input type="submit" name="RestoreDefaults" title="<?=Loc::GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" onclick="return confirm('<?=AddSlashes(Loc::GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?=Loc::GetMessage("MAIN_RESTORE_DEFAULTS")?>">
    <?=bitrix_sessid_post();?>
    <?$tabControl->End();?>
</form>