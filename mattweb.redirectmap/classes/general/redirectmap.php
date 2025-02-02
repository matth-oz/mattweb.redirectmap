<?php
use Mattweb\Redirectmap;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Entity;

class RedirectmapActions{

    public static function dumpFile($var, $fname = 'dump.txt') {
        $f = fopen($_SERVER['DOCUMENT_ROOT'] . '/'.$fname, 'ab');
        fwrite($f, date('d.m.Y H:i:s') . "\r\n");
        fwrite($f, var_export($var, 1) . "\r\n==\r\n");
        fclose($f);
    }

    public static function getRedirectUrl($reqUrl){
        $rdmtEntity = Redirectmap\RedirectmapTable::getEntity();
        // select data
        $query = new Entity\Query($rdmtEntity);

        $query->setSelect(["ID", "OLD_URL", "NEW_URL", "URL_NOTE"]);
        $query->setFilter(['OLD_URL' => $reqUrl]);
        $arData = $query->exec()->fetch();

        return !(empty($arData["NEW_URL"])) ? $arData["NEW_URL"] : null;
    }
    
}