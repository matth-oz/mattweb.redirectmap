<?php
define('CURRENT_MODULE_NAME', 'mattweb.redirectmap');
define('RDTM_TABLE_ID', 'tbl_rdtm_entity');

use Bitrix\Main\Loader;
use \Bitrix\Main\Entity;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Mattweb\Redirectmap;

class RedirectmapEvent{

    public static function RequestHandler(){
        
        if(!defined('ADMIN_SECTION') || defined("ERROR_404")){
            global $APPLICATION;
            $reqUrl = $APPLICATION->GetCurPageParam();

            if(strpos($reqUrl, '?') !== false){
                $arReqUrl = explode('?',  $reqUrl);
                $url = $arReqUrl[0];
            }
            else{
                $url = $reqUrl;
            }

            Loader::IncludeModule(CURRENT_MODULE_NAME);

            //Запрос с кешированием
            $cache = new CPHPCache();
            $cache_time = (int) Option::get(CURRENT_MODULE_NAME, 'cache_time');           
            $cache_id = 'redirect_'.serialize($url);
            $cache_path = '/redirect_map/';

            if($cache_time > 0){
                if ($cache->InitCache($cache_time, $cache_id, $cache_path)){
                    $res = $cache->GetVars();

                    if(is_array($res['redirectMapRec']) && count($res['redirectMapRec']) > 0){
                        $newUrl = $res['redirectMapRec'];
                    }
                }
            }

            if(empty($newUrl)){
                $newUrl = RedirectmapActions::getRedirectUrl($url);
                if(!empty($newUrl) && $cache_time > 0){
                    $cache->StartDataCache($cache_time, $cache_id, $cache_path);
                    $cache->EndDataCache(array("redirectMapRec" => $newUrl));
                }
            }           

            if(!empty($newUrl)){
                LocalRedirect($newUrl, false, "301 Moved Permanently");
            }
        }        
    }
}