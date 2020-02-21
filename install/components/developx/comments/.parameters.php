<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("iblock"))
    return;

global $APPLICATION;

$arIBlockType = CIBlockParameters::GetIBlockTypes();
$rsIBlock = CIBlock::GetList(array(
    "sort" => "asc",
), array(
    "TYPE" => $arCurrentValues["IBLOCK_TYPE"],
    "ACTIVE" => "Y",
));
while ($arr = $rsIBlock->Fetch()) {
    $arIBlock[$arr["ID"]] = "[" . $arr["ID"] . "] " . $arr["NAME"];
}

$arComponentParameters = array(
    "GROUPS" => array(
        "FIELDS" => array(
            "NAME" => GetMessage("DX_CMT_PARAMS_GROUPS_FIELDS"),
        ),
        "ADDITIONALLY" => array(
            "NAME" => GetMessage("DX_CMT_PARAMS_GROUPS_ADDITIONALLY"),
        ),
    ),
    "PARAMETERS" => array(
        "IBLOCK_TYPE" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("DX_CMT_PARAMS_ITEM_IBLOCK_TYPE"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arIBlockType,
            "REFRESH" => "Y",
        ),
        "IBLOCK_ID" => array(
            "PARENT" => "DATA_SOURCE",
            "NAME" => GetMessage("DX_CMT_PARAMS_ITEM_IBLOCK_ID"),
            "TYPE" => "LIST",
            "ADDITIONAL_VALUES" => "Y",
            "VALUES" => $arIBlock,
        ),
        "PUBLISH_AT_ONCE" => array(
            "PARENT" => "ADDITIONALLY",
            "NAME" => GetMessage("DX_CMT_PARAMS_ITEM_PUBLISH_AT_ONCE"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "INCLUDE_JQUERY" => array(
            "PARENT" => "ADDITIONALLY",
            "NAME" => GetMessage("DX_CMT_PARAMS_ITEM_INCLUDE_JQUERY"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "LIKES_SHOW" => array(
            "PARENT" => "ADDITIONALLY",
            "NAME" => GetMessage("DX_CMT_PARAMS_ITEM_LIKES_SHOW"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
         "ONE_COMMENT_SESSION" => array(
            "PARENT" => "ADDITIONALLY",
            "NAME" => GetMessage("DX_CMT_PARAMS_ITEM_ONE_COMMENT_SESSION"),
            "TYPE" => "CHECKBOX",
            "DEFAULT" => "Y",
        ),
        "CACHE_TIME" => array(
            "DEFAULT" => 36000000,
        ),
    ),
);
?>
