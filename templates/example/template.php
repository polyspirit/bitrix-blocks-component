<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (empty($arResult['ITEMS'])) return;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>

<div class="MainInformer">
    <div class="Container">
        <div class="MainInformer__Content">
            <div class="MainInformer__Left">
                <span class="MainInformer__Sticker">
                    <?php echo Loc::getMessage('TPL_HEADER_IMPORTANT_TITLE'); ?>
                </span>
            </div>
            <div class="MainInformer__Right">
                <?php foreach ($arResult['ITEMS'] as $item) { ?>
                    <a class="MainInformer__Link" href="<?=$item['PROPERTY_URL_VALUE']?>" title="">
                        <?php echo $item['NAME']; ?>
                    </a>
                <?php } ?>
            </div>
        </div>
    </div>
</div>