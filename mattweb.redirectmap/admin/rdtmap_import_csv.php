<?php
define('CURRENT_MODULE_NAME', 'mattweb.redirectmap');
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
use Bitrix\Main,
Bitrix\Main\Loader,
Bitrix\Main\Localization\Loc,
Bitrix\Main\Config\Option,
Mattweb\Redirectmap;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

Loc::LoadMessages(__FILE__);
Loc::LoadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

// check rights
if (!$USER->IsAdmin())
{
	$APPLICATION->AuthForm(Loc::getMessage('ADMIN_TOOLS_ACCESS_DENIED'));
}
if (!Loader::includeModule(CURRENT_MODULE_NAME))
{
	$APPLICATION->AuthForm(Loc::getMessage('ADMIN_TOOLS_ACCESS_DENIED'));
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$server = $context->getServer();
$request = $context->getRequest();

// process
if (
	$request->get('start') == 'Y' &&
	$server->getRequestMethod() == 'GET' &&
	check_bitrix_sessid()
)
{ 
    // init
	$errors = array();
    $startTime = time();   
    
    require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_js.php');

    $saveErrosToLog = Option::get(CURRENT_MODULE_NAME, 'save_errors_to_log') == 'Y';
    $importLogFilePath = $server->getDocumentRoot().Option::get(CURRENT_MODULE_NAME, 'import_log_fpath');

    $csvFilePath = $request->get('url_data_file');
    $skipFirstRow = (int) $request->get('skip_first_row');
    
    if(!empty($csvFilePath)){
        $csvFilePath = $server->getDocumentRoot().$csvFilePath;

        $isSkipFirstRow = ($skipFirstRow == 1);

        $csvFile = new CCSVData('R', $isSkipFirstRow);

        $fp = $csvFile->LoadFile($csvFilePath);

        if($fp !== false){

            $csvFile->SetDelimiter(';');
            while ($data = $csvFile->Fetch()){
                $arRows[] = $data;
            }

            if(!empty($arRows)){
                $successImportRows = 0;
                foreach($arRows as $arRow){
                    $arFields = [
                     'OLD_URL' => $arRow[0],
                     'NEW_URL' => $arRow[1],
                     'URL_NOTE' => $arRow[2],
                    ];

                    try{
                        $obRes = Redirectmap\RedirectmapTable::add($arFields);
                    
                        if ($obRes->isSuccess())
                        {
                            $recId = $obRes->getId();
                            $successImportRows++;
                            //echo $recId.'<br />';
                        }
                        else{
                            $errors[] = $obRes->getErrorMessages();                            
                        } 
                    }
                    catch(Exception $e){
        
                        $errMess = $e->getMessage();
                        $errors[] = $errMess;
        
                        if($saveErrosToLog){
                            file_put_contents($importLogFilePath, date('d.m.Y h:i').' '.$errMess."\r\n", FILE_APPEND | LOCK_EX);
                            file_put_contents($importLogFilePath, "===\r\n", FILE_APPEND | LOCK_EX);
                        }
                    }
                }         
            }
            else{
                // файл пустой
                $errors[] = Loc::GetMessage('RDTM_ADMIN_FILE_IS_EMPTY');
            }
        }
        else{
            // ошибка при открытии файла
            $errors[] = Loc::GetMessage('RDTM_ADMIN_FILE_OPEN_ERROR');
        }
    }
    else{
        // ошибка - нет файла
        $errors[] = Loc::GetMessage('RDTM_ADMIN_FILE_NOT_FOUND');
    }

    // show message (error or processing)
    if (!empty($errors))
    {
        \CAdminMessage::ShowMessage(array(
            'MESSAGE' => Loc::getMessage('RDTM_ADMIN_TOOLS_ERROR_IMPORT'),
            'DETAILS' => implode('<br/>', $errors),
            'HTML' => true,
            'TYPE' => 'ERROR',
        ));
        
    }

    if($successImportRows > 0){
        $result .= '<br/>'.Loc::getMessage('RDTM_ADMIN_TOOLS_PROCESS_FINAL');
        
        \CAdminMessage::ShowMessage(array(
			'MESSAGE' => Loc::getMessage('RDTM_ADMIN_TOOLS_TITLE_IMPORT'),
			'DETAILS' => $result,
			'HTML' => true,
			'TYPE' => 'PROGRESS',
		));

        \CAdminMessage::ShowMessage(array(
            'MESSAGE' => Loc::getMessage('RDTM_ADMIN_TOOLS_PROCESS_FINISH_DELETE'),
            'DETAILS' => '',
            'HTML' => true,
            'TYPE' => 'ERROR',
        ));
    }  
    
    echo '<script>CloseWaitWindow();</script>';
    echo '<script>EndImport();</script>';
    require($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/include/epilog_admin_js.php');
}

// form
$aTabs = array(
	array(
		'DIV' => 'import',
		'TAB' => Loc::getMessage('RDTM_ADMIN_TOOLS_TITLE_IMPORT'),
		'TITLE' => Loc::getMessage('RDTM_ADMIN_TOOLS_IMPORT_TITLE')
	)
);
$tabControl = new CAdminTabControl('tabControl', $aTabs);

$APPLICATION->SetTitle(Loc::GetMessage('RDTM_ADMIN_TOOLS_IMPORT_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<!--Импорт списка url из csv-файла /bitrix/modules/highloadblock/admin/highloadblock_import.php-->
<div id="tools_result_div"></div>

<script>
	var running = false;

    function DoNext()
    {
        var queryString =
                        'start=Y'
                        + '&lang=<?=LANGUAGE_ID?>'
                        + '&<?= bitrix_sessid_get()?>'
                        ;
        
            queryString += '&url_data_file=' + jsUtils.urlencode(BX('url_data_file').value);
            queryString += '&skip_first_row=' + (BX('skip_first_row').checked ? 1 : 0);
        

        if (running)
        {
            ShowWaitWindow();
            BX.ajax.get(
                '<?= \CUtil::JSEscape($APPLICATION->getCurPage())?>?'+queryString,                
                function(result) {
                    BX('tools_result_div').innerHTML = result;
                }
            );
        }
    }

	function StartImport()
	{
		running = BX('start_button').disabled = true;
		DoNext();
	}

	function EndImport()
	{
		running = BX('start_button').disabled = false;
	}
</script>

<form name="form_tools" method="get" action="<?=$APPLICATION->GetCurPage()?>">
    <?
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	?>
    <tr>
		<td width="40%"><?= Loc::getMessage('RDTM_ADMIN_TOOLS_FIELD_IMPORT_FILE')?>:</td>
		<td>
            <input type="text" id="url_data_file" size="30" value="" />
            <input type="button" value="…" OnClick="BtnClick()" />
            <?
            CAdminFileDialog::ShowScript
            (
                Array(
                    'event' => 'BtnClick',
                    'arResultDest' => array('FORM_NAME' => 'form_tools', 'FORM_ELEMENT_NAME' => 'url_data_file'),
                    'arPath' => array('SITE' => SITE_ID, 'PATH' =>'/upload'),
                    'select' => 'F',// F - file only, D - folder only
                    'operation' => 'O',// O - open, S - save
                    'showUploadTab' => true,
                    'showAddToMenuTab' => false,
                    'fileFilter' => 'csv',
                    'allowAllFiles' => true,
                    'SaveConfig' => true,
                )
            );
            ?>
        </td>
    </tr>
    <tr>
		<td width="40%"><?= Loc::getMessage('RDTM_ADMIN_SKIP_FIRST_ROW_FIELD_TITLE')?>:</td>
        <td>
            <input type="checkbox" id="skip_first_row" value="Y" checked="checked" />
        </td>
    </tr>
	<?$tabControl->Buttons();?>
	<input type="button" id="start_button" value="<?= Loc::getMessage('RDTM_ADMIN_TOOLS_START_IMPORT')?>" OnClick="StartImport();" class="adm-btn-save" />
	<!--input type="button" id="stop_button" value="<?= Loc::getMessage('RDTM_ADMIN_TOOLS_STOP_IMPORT')?>" OnClick="EndImport();" /-->
	<?$tabControl->End();?>
</form>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>