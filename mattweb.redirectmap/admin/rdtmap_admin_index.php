<?php
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */
use Bitrix\Main,
Bitrix\Main\Loader,
Mattweb\Redirectmap;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule("mattweb.redirectmap");

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

$APPLICATION->SetTitle('Карта редиректов');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<div class="adm-info-message">
    <p>Модуль «Карта редиректов» (mattweb.redirectmap) предназначен для хранения записей для перенаправления со старых url’ов на новые.</p>
    <p>Будет полезен при создании нового сайта и переноса контента со старого на новый сайт.</p>
    <p>На <a href="/bitrix/admin/rdtmap_url_list.php">странице со списком редиректов</a> можно добавлять, редактировать и удалять записи.</p>
    <p>Для загрузки записей редиректов из csv-файла создана <a href="/bitrix/admin/rdtmap_import_csv.php">страница «Импорт редиректов»</a></p>
    <p><a href="/bitrix/modules/mattweb.redirectmap/admin/redirect_map_example.csv">Скачать</a> шаблон csv-файла для загрузки.</p>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>