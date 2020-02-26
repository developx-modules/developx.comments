<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader,
    Bitrix\Main\Context,
    Bitrix\Main\Localization\Loc;

/**
 * @var $APPLICATION CMain
 * @var $USER CUser
 */

CModule::IncludeModule("iblock");
Loc::loadMessages(__FILE__);

class DevelopxCommentsComponent extends \CBitrixComponent
{
    const DEFAULT_CACHE_TIME = 36000000;
    const CAPTCHA_ACTION = 'commentSent';
    const EVENT_NAME = "DEVELOPX_NEW_COMMENT";
    const MODULE_NAME = 'developx.comments';
    const CAPTCHA_MODULE_NAME = 'developx.gcaptcha';

    public function onPrepareComponentParams($arParams)
    {
        if (!isset($arParams["CACHE_TIME"])) {
            $arParams["CACHE_TIME"] = self::DEFAULT_CACHE_TIME;
        }
        if (!isset($arParams['PUBLISH_AT_ONCE'])) {
            $arParams['PUBLISH_AT_ONCE'] = 'Y';
        }
        if ($arParams['INCLUDE_GOOGLE_CAPTCHA'] == 'Y' &&
            !Loader::includeModule(self::CAPTCHA_MODULE_NAME)
        ) {
            $arParams['INCLUDE_GOOGLE_CAPTCHA'] = 'N';
        }
        $arParams['CAPTCHA_ACTION'] = self::CAPTCHA_ACTION;
        return $arParams;
    }

    private function getUser()
    {
        global $USER;
        $user_id = $USER->getId();
        if ($user_id) {
            $rsUser = CUser::GetByID($user_id);
            $this->arResult['USER'] = $rsUser->Fetch();
        } else {
            $this->arResult['USER'] = false;
        }
    }

    private function prepareFields()
    {
        $this->arResult['FIELDS'] = $this->getFormFields();
        if ($this->arResult['USER']) {
            $this->arResult['FIELDS'] = $this->checkUserDefaults($this->arResult['FIELDS'], $this->arResult['USER']);
        }
    }

    /**
     * @return array
     */
    private function getFormFields()
    {
        $fields = [
            'NAME' => [
                'NAME' => Loc::getMessage('DX_CMT_OPT_NAME'),
                'TYPE' => 'S',
                'IS_REQUIRED' => 'Y',
            ],
            'PREVIEW_TEXT' => [
                'NAME' => Loc::getMessage('DX_CMT_OPT_TEXT'),
                'TYPE' => 'HTML',
                'IS_REQUIRED' => 'Y',
            ]
        ];
        return $fields;
    }

    /**
     * @param array $fields
     * @param array $user
     * @return array
     */
    private function checkUserDefaults($fields, $user)
    {
        $arUserFileds = [
            'NAME',
            'EMAIL'
        ];
        foreach ($arUserFileds as $userFiled) {
            if (!empty($fields[$userFiled]) && empty($fields[$userFiled]['VALUE']) && !empty($user[$userFiled])) {
                $fields[$userFiled]['VALUE'] = $user[$userFiled];
            }
        }
        return $fields;
    }

    private function checkRequest()
    {
        $arRequest = $this->getRequest();
        if (
            $arRequest["AJAX_CALL"] == "Y" &&
            isset($arRequest["ACTION"])
        ) {
            if ($arRequest["ACTION"] == 'addLike') {
                $this->addLike($arRequest["ID"], $arRequest["COUNT"]);
            } elseif ($arRequest["ACTION"] == 'addComment') {
                if (
                    $this->checkAjax($arRequest['bxajaxid']) &&
                    $this->checkFieldsResult($arRequest['RESULT']) &&
                    $this->checkCaptcha()
                ) {
                    $this->arResult["RESULT"] = $this->addComment();
                }
            }
        }
    }

    /**
     * @return array
     */
    private function getRequest()
    {
        return Context::getCurrent()->getRequest();
    }

    /**
     * @param integer $elementId
     * @param integer $likesCount
     */
    private function addLike($elementId, $likesCount)
    {
        $GLOBALS['APPLICATION']->RestartBuffer();
        if (empty($_SESSION['ADDED_LIKES'][$elementId])) {
            $likesCount++;
            $liked = true;
            $_SESSION['ADDED_LIKES'][$elementId] = $elementId;
        } else {
            $likesCount--;
            $liked = false;
            unset($_SESSION['ADDED_LIKES'][$elementId]);
        }
        CIBlockElement::SetPropertyValuesEx($elementId, $this->arParams['IBLOCK_ID'], ['LIKE' => $likesCount]);
        $this->clearCache('items');

        echo json_encode([
            'COUNT' => $likesCount,
            'LIKED' => $liked
        ]);
        die();
    }

    /**
     * @param array $bxajaxid
     * @return boolean
     */
    private function checkAjax($bxajaxid)
    {
        return ($this->arParams['AJAX_ID'] == $bxajaxid);
    }

    /**
     * @param array $arRequestResult
     * @return boolean
     */
    public function checkFieldsResult($arRequestResult)
    {
        $checked = true;
        foreach ($this->arResult['FIELDS'] as $code => $field) {
            if ($field['IS_REQUIRED'] == 'Y' && empty($arRequestResult[$code])) {
                $checked = false;
                $this->arResult['FIELDS'][$code]['ERROR'] = 'Y';
            }
            $this->arResult['FIELDS'][$code]['VALUE'] = $arRequestResult[$code];
        }
        return $checked;
    }

    /**
     * @return boolean
     */
    private function checkCaptcha()
    {
        if ($this->arParams['INCLUDE_GOOGLE_CAPTCHA'] != 'Y'){
            return true;
        }
        $captchaObj = new Developx\Gcaptcha\Main();
        if ($captchaObj->checkCaptcha()) {
            return true;
        } else {
            $this->arResult["RESULT"] = [
                'SUCCESS' => false,
                'MESSAGE' => Loc::getMessage('DX_CMT_CAPTCHA_ERROR')
            ];
            return false;
        }
    }

    /**
     * @return array
     */
    private function addComment()
    {
        $el = new CIBlockElement;

        foreach ($this->arResult['FIELDS'] as $code => $field) {
            $arLoadProductArray[$code] = $field['VALUE'];
        }
        $arLoadProductArray['IBLOCK_ID'] = $this->arParams["IBLOCK_ID"];
        $arLoadProductArray['ACTIVE'] = $this->arParams['PUBLISH_AT_ONCE'];

        if (!empty($this->arParams['ELEMENT_ID'])) {
            $arLoadProductArray['PROPERTY_VALUES']['ELEMENT_ID'] = $this->arParams['ELEMENT_ID'];
        }

        if ($this->arResut['USER']) {
            $arLoadProductArray['PROPERTY_VALUES']['USER_ID'] = $this->arResult['USER']['ID'];
        }

        if ($id = $el->Add($arLoadProductArray)) {
            $this->SentMessage([
                'ID' => $id,
                'USER_ID' => $this->arResult['USER'] ? $this->arResult['USER']['ID'] : false,
                'NAME' => $this->arResult['FIELDS']['NAME']['VALUE'],
                'COMMENT' => $this->arResult['FIELDS']['PREVIEW_TEXT']['VALUE'],
                'ELEMENT_NAME' => !empty($this->arParams['ELEMENT_ID']) ? $this->arParams['ELEMENT_ID'] : false,
                'ACTIVE' => $this->arParams['PUBLISH_AT_ONCE'],
                'IBLOCK_ID' => $this->arParams["IBLOCK_ID"],
                'IBLOCK_TYPE' => $this->arParams["IBLOCK_TYPE"],
            ]);
            if ($this->arParams['PUBLISH_AT_ONCE'] == 'Y') {
                $this->clearCache('items');
            }
            $_SESSION['COMMENT_ADDED'] = 'Y';
            return [
                'SUCCESS' => true
            ];
        } else {
            global $strError;
            return [
                'SUCCESS' => false,
                'MESSAGE' => $strError
            ];
        }
    }

    /**
     * @param array $fields
     */
    private function SentMessage($fields)
    {
        CEvent::Send(self::EVENT_NAME, SITE_ID, $fields);
    }

    private function checkProps()
    {
        $props = [
            [
                'NAME' => Loc::getMessage('DX_CMT_OPT_TEXT'),
                'CODE' => 'ELEMENT_ID',
                'PROPERTY_TYPE' => 'E'
            ],
            [
                'NAME' => Loc::getMessage('DX_CMT_OPT_LIKES'),
                'CODE' => 'LIKE',
                'PROPERTY_TYPE' => 'N'
            ],
            [
                'NAME' => Loc::getMessage('DX_CMT_OPT_USER_ID'),
                'CODE' => 'USER_ID',
                'PROPERTY_TYPE' => 'S',
                'USER_TYPE' => 'UserID'
            ]
        ];

        $propsExist = [];
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($this->arParams['CACHE_TIME'], $this->getCacheName('props'), '/')) {
            $propsExist = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $rsProperty = CIBlockProperty::GetList(
                [],
                ['IBLOCK_ID' => $this->arParams['IBLOCK_ID']]
            );
            while ($prop = $rsProperty->Fetch()) {
                $propsExist[$prop['CODE']] = $prop;
            }
            $obCache->EndDataCache($propsExist);
        }

        $clearCache = false;
        foreach ($props as $prop) {
            if (empty($propsExist[$prop['CODE']])) {
                $this->addProp($prop);
                $clearCache = true;
            }
        }

        if ($clearCache) {
            $this->clearCache('props');
        }
    }

    /**
     * @param array $arPropFields
     */
    private function addProp($arPropFields)
    {
        $arPropFields['ACTIVE'] = 'Y';
        $arPropFields['IBLOCK_ID'] = $this->arParams['IBLOCK_ID'];
        $ibp = new CIBlockProperty;
        $ibp->Add($arPropFields);
    }

    private function getItems()
    {
        $obCache = new \CPHPCache();
        if ($obCache->InitCache($this->arParams['CACHE_TIME'], $this->getCacheName('items'), '/')) {
            $items = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $items = [];
            $arSelect = ["ID", "NAME", "PREVIEW_TEXT", "DETAIL_TEXT", "DATE_CREATE", "PROPERTY_LIKE"];
            $arFilter = [
                "IBLOCK_ID" => $this->arParams['IBLOCK_ID'],
                "ACTIVE" => "Y",
                "PROPERTY_ELEMENT_ID" => !empty($this->arParams['ELEMENT_ID']) ? $this->arParams['ELEMENT_ID'] : false
            ];
            $res = CIBlockElement::GetList(["ID" => "ASC"], $arFilter, false, false, $arSelect);
            while ($ob = $res->GetNext()) {
                $arButtons = CIBlock::GetPanelButtons(
                    $this->arParams['IBLOCK_ID'],
                    $ob["ID"],
                    0,
                    array("SECTION_BUTTONS" => false, "SESSID" => false)
                );
                $items[] = [
                    'ID' => $ob['ID'],
                    'S_NAME' => substr($ob['NAME'], 0, 1),
                    'NAME' => $ob['NAME'],
                    'COMMENT' => $ob['PREVIEW_TEXT'],
                    'ANSWER' => $ob['DETAIL_TEXT'],
                    'DATE_CREATE' => $this->getFormatDate($ob['DATE_CREATE']),
                    'LIKES' => !empty($ob['PROPERTY_LIKE_VALUE']) ? $ob['PROPERTY_LIKE_VALUE'] : 0,
                    'EDIT_LINK' => $arButtons["edit"]["edit_element"]["ACTION_URL"],
                    'DELETE_LINK' => $arButtons["edit"]["delete_element"]["ACTION_URL"]
                ];
            }
            $obCache->EndDataCache($items);
        }
        foreach ($items as $key => $item) {
            $items[$key]['CLASS'] = empty($_SESSION['ADDED_LIKES'][$item['ID']]) ? '' : 'liked';
        }
        $this->arResult['ITEMS'] = $items;
    }

    /**
     * @param string $data
     * @return string
     */
    private function getFormatDate($data)
    {
        $date = new DateTime($data);
        $mounth = explode(',', Loc::getMessage('DX_CMT_MONTHS'));
        return $date->format('d') . ' ' . $mounth[(int)$date->format('m') - 1] . ' ' . $date->format('H') . ':' . $date->format('i');
    }

    /**
     * @param string $type
     */
    private function clearCache($type)
    {
        $cache = new \CPHPCache();
        $cache->Clean($this->getCacheName($type), '/');
    }

    /**
     * @param string $type
     * @return string
     */
    private function getCacheName($type)
    {
        return self::MODULE_NAME . $type . $this->arParams['IBLOCK_ID'];
    }

    private function checkCommentAval()
    {
        $this->arResult['ADD_COMMENT_AVAL'] = !($this->arParams['ONE_COMMENT_SESSION'] == 'Y' && $_SESSION['COMMENT_ADDED'] == 'Y');
    }

    public function executeComponent()
    {
        if (empty($this->arParams['IBLOCK_ID'])) {
            return;
        }

        $this->getUser();
        $this->prepareFields();
        $this->checkRequest();
        $this->checkProps();
        $this->getItems();
        $this->checkCommentAval();

        $this->includeComponentTemplate();
    }
}