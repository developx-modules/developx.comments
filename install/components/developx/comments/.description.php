<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentDescription = array(
	"NAME" => GetMessage("DX_CMT_DESC_DETAIL"),
	"DESCRIPTION" => GetMessage("DX_CMT_DESC_DETAIL_DESC"),
	"SORT" => 0,
    "CACHE_PATH" => "Y",
    "PATH" => array(
        "ID" => "catalog",
        "NAME" => GetMessage("DX_CMT_DESC_CATALOG"),
        "SORT" => 40,
    ),
);