<?php

use Bitrix\Main\Context;

class ContextCust {
    public static function GetUrl() {
        
        $HOSTNAME = \Bitrix\Main\Context::getCurrent()->getServer()->get('HOSTNAME'); 
        
        if(isset($HOSTNAME) && !empty($HOSTNAME)){
            if($HOSTNAME == 'bitrix24.ek.local'){
                return 'bitrix';
            } else{
                return NULL;
            }
        } else{
            $server = Context::getCurrent()->getServer(); // сервер
            $ServerName = $server->getServerName();

            if(isset($ServerName) && !empty($ServerName)){
                if('bx24.kitchenbecker.ru' == $ServerName){
                    return 'bitrix';
                }
                else{
                    return NULL;
                }
            } else {
                $url = \Bitrix\Main\Engine\UrlManager::getInstance()->getHostUrl();
                if($url == 'http://bx24.kitchenbecker.ru'){
                    return 'bitrix';
                }
            }
            
        }
        
        return NULL;
    }
}
