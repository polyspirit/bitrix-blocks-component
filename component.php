<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$filter = $arParams['FILTER'] ?? [];

$cacheParams = [];
foreach ($arParams['CACHE_PARAMS'] ?: [] as $val) {
    $cacheParams[$val] = (is_array($_GET[$val])) ? serialize($_GET[$val]) : htmlspecialchars($_GET[$val]);
};

if ($this->StartResultCache(
    false, 
    [
        ($arParams['CACHE_GROUPS'] === 'N' ? false : $USER->GetGroups()),
        serialize($filter),
        serialize($cacheParams)
    ]
)) {
    $this->SetResultCacheKeys([]);
    $this->IncludeComponentTemplate();
}
