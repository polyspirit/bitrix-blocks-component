# Bitrix Blocks Component
Simple caching component for bitrix

## How to install
Put all files to 
`/local/components/%your_namespace%/blocks/`
For example:
`/local/components/other/blocks/`

Then create some templates directories in your theme template directory.
`/local/templates/%template_name%/components/%your_namespace%/blocks/news.list/`
For example:
`/local/templates/main/components/other/blocks/news.list/`

Then create **template.php** in the same directory.
Create **result.php** or **result_modifier.php** for getting and modifying data.

## How to include

You can use this code wherever you want.

```php
$APPLICATION->IncludeComponent(
    'other:blocks',
    'news.list',
    [
        'CACHE_TIME' => 3600,
        'CACHE_TIME_DB' => '3600',
        'CACHE_GROUPS' => 'N',
        'CACHE_SALT' => '#news.list.001' // some unique string
        ... // your parameters will be stored in $arParams variable
    ]
);
```

## result_modifier.php or **result.php** example

```php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// use https://github.com/polyspirit/bitrix-builder
use \polyspirit\Bitrix\Builder\IBlock;

$fields = $arParams['FIELDS'] ?: ['NAME'];
$filter = $arParams['FILTER'] ?: [];

$handler = function (&$item) {
    $item['FILE'] = CFile::GetPath($item['PROPS']['FILE']['VALUE']);
};

$iBlock = new IBlock('news');
$arResult['NEWS'] = $iBlock->active()
                            ->filter($filter)
                            ->fields($fields)
                            ->getElements($handler);
```

## template.php example

```php
foreach ($arResult['NEWS'] as $newsItem) {
    // some code
}
```

## Bitrix Modify mode

To enable this mode add to **result.php** IBLOCK_ID:

```php
$arResult['IBLOCK_ID'] = CURRENT_IBLOCK_ID;
```

And add this id attribute to parent html-tag in **template.php**

```php
<div class="parent-container" id="<?php echo $this->GetEditAreaId($arResult['AREA_ID']); ?>">
    ...
</div>
```

Also you can add SECTION_ID and ELEMENT_ID if you need:

```php
$arResult['SECTION_ID'] = CURRENT_SECTION_ID;
$arResult['ID'] = CURRENT_ELEMENT_ID;
```