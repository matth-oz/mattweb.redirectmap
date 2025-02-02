<?php

use \Bitrix\Main\IO\Directory;
use Bitrix\Main\IO;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Entity\Base;
use \Bitrix\Main\Application;
use \Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

Class mattweb_redirectmap extends CModule{
    var $errors;

    function __construct(){
        $arModuleVersion = array();
        include(__DIR__."/version.php");

        $this->MODULE_ID = 'mattweb.redirectmap';
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('MATTWEB_REDIRECTMAP_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('MATTWEB_REDIRECTMAP_MODULE_DESC');

        $this->PARTNER_NAME = Loc::getMessage('MATTWEB_REDIRECTMAP_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('MATTWEB_REDIRECTMAP_PARTNER_URI');

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    //Определяем место размещения модуля
    public function GetPath($notDocumentRoot=false)
    {
        if($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(),'',dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

    function InstallDB(){
        global $DB, $APPLICATION;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/mattweb.redirectmap/install/db/mysql/install.sql");
        
        if($this->errors !== false){
            $APPLICATION->ThrowException(implode('', $this->errors));
            return false;
        }

        return true;
    }

    function UnInstallDB(){
        global $DB, $APPLICATION;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/mattweb.redirectmap/install/db/mysql/uninstall.sql");
        
        if($this->errors !== false){
            $APPLICATION->ThrowException(implode('', $this->errors));
            return false;
        }

        return true;    
    }

    function InstallEvents(){
        EventManager::getInstance()->registerEventHandler(
            "main",
            "OnBeforeProlog",
            $this->MODULE_ID,
            "RedirectmapEvent",
            "RequestHandler"
        );
     
       return true;
    }

    function UnInstallEvents(){
        EventManager::getInstance()->unRegisterEventHandler(
            "main",
            "OnBeforeProlog",
            $this->MODULE_ID,
            "RedirectmapEvent",
            "RequestHandler"
        );
       
        return true;
    }

    function InstallFiles($arParams = array()){
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mattweb.redirectmap/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
        
        return true;
    }

    function UnInstallFiles(){
        $dirPath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mattweb.redirectmap/install/admin";

        $dir = new Directory($dirPath);
        $arDir = $dir->getChildren();
        
        foreach($arDir as $dirItem){
            if ($dirItem->isFile())
                $arDirFiles[] = $dirItem->getName();
        }

        if(is_array($arDirFiles) && count($arDirFiles) > 0){
            foreach($arDirFiles as $fileName){
                $fileAdminPath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/".$fileName;
                IO\File::deleteFile($fileAdminPath);
            }
        }

        return true;    
    }
    
    function DoInstall(){
        global $USER, $APPLICATION;

        if ($USER->IsAdmin())
		{
            if ($this->isVersionD7()){
                // создание таблиц и загрузка данных
                $this->InstallDB();
                // создание и регистрация событий
                $this->InstallEvents();
                // копирование файлов
                $this->InstallFiles();

                // регистрация модуля в системе
                \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            }
            else
            {
                $APPLICATION-ThrowException(Loc::getMessage('MATTWEB_REDIRECTMAP_INSTALL_ERROR_VERSION'));
            }

            $APPLICATION->IncludeAdminFile(Loc::getMessage("MATTWEB_REDIRECTMAP_INSTALL_TITLE"), $this->GetPath()."/install/step.php");
        }        
    }

    function DoUninstall(){
        global $USER, $APPLICATION;

        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        if($request['step'] < 2){
            $APPLICATION->IncludeAdminFile(Loc::getMessage("MATTWEB_REDIRECTMAP_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep1.php");
        }
        elseif ($request['step'] == 2){
            $this->UnInstallFiles();
            $this->UnInstallEvents();

            if($request['savedata'] !=  'Y')
                $this->UnInstallDB();

            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(Loc::getMessage("MATTWEB_REDIRECTMAP_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep2.php");
        }
    }

}