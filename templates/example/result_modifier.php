<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule('iblock');

$obResult = CIBlockElement::GetList(
    ['SORT' => 'ASC'],
    [
        'IBLOCK_ID' => 1,
        'ACTIVE' => 'Y',
        'ACTIVE_DATE' => 'Y'
    ],
    false,
    false,
    [
        'ID',
        'NAME',
        'PROPERTY_URL'
    ]
);
while ($arItem = $obResult->GetNext()) {
    $arResult['ITEMS'][] = $arItem;
}

