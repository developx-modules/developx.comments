<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

if (class_exists('developx_comments')) {
    return;
}

class developx_comments extends CModule
{
    /** @var string */
    public $MODULE_ID;

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME;

    /** @var string */
    public $MODULE_DESCRIPTION;

    /** @var string */
    public $MODULE_GROUP_RIGHTS;

    /** @var string */
    public $PARTNER_NAME;

    /** @var string */
    public $PARTNER_URI;

    public $eventName = "DEVELOPX_NEW_COMMENT";

    public function __construct()
    {
        $arModuleVersion = [];

        include __DIR__ . '/version.php';
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        Loc::loadMessages(__FILE__);
        $this->MODULE_ID = 'developx.comments';
        $this->MODULE_NAME = Loc::getMessage('DX_CMT_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('DX_CMT_MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'N';
        $this->PARTNER_NAME = 'Developx';
        $this->PARTNER_URI = 'https://developx.ru';
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallFiles();
        $this->addEvent();
    }

    public function doUninstall()
    {
        $this->uninstallFiles();
        $this->deleteEvent();
        ModuleManager::unregisterModule($this->MODULE_ID);
    }

    public function InstallFiles()
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/developx.comments/install/components", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components", true, true);
        return true;
    }

    public function uninstallFiles()
    {
        DeleteDirFilesEx("/bitrix/components/developx/comments");
    }

    public function addEvent()
    {
        $obEventType = new CEventType;
        $obEventType->Add(array(
            "EVENT_NAME" => $this->eventName,
            "NAME" => "Новый комментарий",
            "SITE_ID" => "ru",
            "DESCRIPTION" => "
#ID# - ID комментария
#USER_ID# - ID пользователя
#NAME# - Имя пользователя
#COMMENT# - Комментарий
#ELEMENT_NAME# - Комментируемый элемент
#ACTIVE# - Активность
#IBLOCK_ID# - ID Инфоблока
#IBLOCK_TYPE# - Тип инфоблока"
        ));


        $arSites = array();
        $rsSites = CSite::GetList($by="sort", $order="desc");
        while ($arSite = $rsSites->Fetch())
        {
            $arSites[] = $arSite['LID'];
        }
        $obTemplate = new CEventMessage;
        $obTemplate->Add(array(
            "ACTIVE" => "Y",
            "EVENT_NAME" => $this->eventName,
            "LID" => $arSites,
            "EMAIL_FROM" => "#DEFAULT_EMAIL_FROM#",
            "EMAIL_TO" => "#DEFAULT_EMAIL_FROM#",
            "BCC" => "",
            "SUBJECT" => "На сайте #SITE_NAME# был оставлен новый комментарий - #ID#",
            "BODY_TYPE" => "html",
            "MESSAGE" => "На сайте #SITE_NAME# был оставлен новый комментарий<br>
<br>
Пользователь: #USER_ID#<br>
Имя: #NAME#<br>
Комментарий: #COMMENT#<br>
Елемент: #ELEMENT_NAME#<br>
Активность: #ACTIVE#<br>
<br>
<a href='#SERVER_NAME#/bitrix/admin/iblock_element_edit.php?IBLOCK_ID=#IBLOCK_ID#&type=#IBLOCK_TYPE#&ID=#ID#&lang=ru&find_section_section=0&WF=Y'>Ссылка на комментарий</a>"
        ));
    }

    public function deleteEvent()
    {
        $arFilter = Array(
            "TYPE_ID" => $this->eventName,
        );

        $rsMess = CEventMessage::GetList($by="site_id", $order="desc", $arFilter);

        while($arMess = $rsMess->GetNext())
        {
            $emessage = new CEventMessage;
            $emessage->Delete($arMess['ID']);
        }

        $et = new CEventType;
        $et->Delete($this->eventName);
    }

}
