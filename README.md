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

Then create **result_modifier.php** and **template.php** in the same directory.

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

## result_modifier.php example

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