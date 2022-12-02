<?php
#components/other/blocks/class.php

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Loader;

use \polyspirit\Bitrix\Builder\IBlock;
use \polyspirit\Bitrix\Builder\ISection;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class Blocks extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable, \Bitrix\Main\Errorable
{
    /** @var ErrorCollection */
    protected $errorCollection;

    private $iBlock;

    public function configureActions()
    {
        //если действия не нужно конфигурировать, то пишем просто так. И будет конфиг по умолчанию 
        return [];
    }

    public function onPrepareComponentParams($arParams)
    {
        $this->errorCollection = new ErrorCollection();

        $this->arParams = $arParams;

        $this->arResult['TEMPLATE'] = $arParams['TEMPLATE'] ?: 'default';

        $this->getResult();

        return $this->arParams;
    }

    protected function checkModules()
    {
        if (!Loader::includeModule('iblock')) {
            throw new SystemException(Loc::getMessage('CPS_MODULE_NOT_INSTALLED', array('#NAME#' => 'iblock')));
        } 
    }

    public function executeComponent()
    {
        global $USER;

        $filter = $arParams['FILTER'] ?? [];

        $cacheParams = [];
        foreach ($this->arParams['CACHE_PARAMS'] ?: [] as $val) {
            $cacheParams[$val] = (is_array($_GET[$val])) ? serialize($_GET[$val]) : htmlspecialchars($_GET[$val]);
        };

        if ($this->StartResultCache(
            false,
            [
                ($this->arParams['CACHE_GROUPS'] === 'N' ? false : $USER->GetGroups()),
                serialize($filter),
                serialize($cacheParams)
            ]
        )) {
            try {
                $this->checkModules();
                $this->SetResultCacheKeys(['TITLE']);
                $this->setEditButtons();
                $this->IncludeComponentTemplate();
            } catch (SystemException $e) {
                ShowError($e->getMessage());
            }
        }
    }

    protected function getResult()
    {
        $arParams = $this->arParams;

        if (!empty($arParams['IBLOCK_ID'])) {

            switch ($this->getTemplateName()) {
                case 'items.list':
                    $this->getList();
                    break;
                case 'items.detail':
                    $this->getDetail();
                    break;
                case 'items.sections':
                    $this->getSections();
                    break;

                default:
                    $this->getList();
                    break;
            }
        } else {
            $templateParts = explode('.', $this->getTemplateName());
            $this->arResult['IBLOCK_ID'] = IBlock::getIdByCode($templateParts[0]);
            
            $file = $_SERVER['DOCUMENT_ROOT'] . SITE_TEMPLATE_PATH . '/components/other/blocks/' . $this->getTemplateName() . '/result.php';

            if (file_exists($file)) {
                $arResult = $this->arResult;
                require $file;
                $this->arResult = $arResult;
            }
        }
    }

    public function getList(): void
    {
        $arParams = $this->arParams;

        $params = [
            'sort' => $arParams['SORT'] ?: ['sort' => 'ASC', 'date_active_from' => 'DESC', 'created_date' => 'DESC'],
            'filter' => $arParams['FILTER'] ?: ['ACTIVE' => 'Y'],
            'fields' => $arParams['FIELDS'] ?: ['NAME', 'CODE', 'PREVIEW_TEXT', 'PREVIEW_PICTURE'],
            'navs' => $arParams['NAVS'] ?: [],
            'sizes' => $arParams['SIZES'] ?: ['width' => 720, 'height' => 480]
        ];

        $fileHandle = null;
        if (!empty($arParams['FILE'])) {
            $fileHandle = function (&$item) use (&$arParams) {
                $file = CFile::GetByID($item['PROPS'][$arParams['FILE']]['VALUE'])->Fetch();
                $item['FILE'] = $file;
                $item['FILE_PATH'] = '/upload/' . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
                $item['FILE_SIZE'] = CFile::FormatSize($file['FILE_SIZE']);
            };
        }

        $this->iBlock = new IBlock($arParams['IBLOCK_ID']);
        $this->arResult['ITEMS'] = $this->iBlock->params($params)->getElements($fileHandle);
        $this->arResult['IBLOCK_ID'] = $this->iBlock->getIblockId();

        if (!empty($this->arParams['PAGINATION'])) {
            $this->arResult['NAV_STRING'] = $this->iBlock->getObResult()->GetPageNavStringEx(
                $navComponentObject,
                '1',
                $this->arParams['NAV_TEMPLATE'] ?: 'news.list.pagenavigation',
                'N',
                $this->__component
            );
        }
    }

    public function getDetail(): void
    {
        $arParams = $this->arParams;

        $params = [
            'filter' => $arParams['FILTER'] ?: ['ACTIVE' => 'Y'],
            'fields' => $arParams['FIELDS'] ?: ['NAME', 'CODE', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'PROPERTY_*'],
            'sizes' => $arParams['SIZES'] ?: ['width' => 720, 'height' => 480]
        ];
        
        $fileHandle = null;
        if (!empty($arParams['FILE'])) {
            $fileHandle = function(&$item) use(&$arParams) {
                $item['FILE'] = CFile::GetPath($item['PROPS'][$arParams['FILE']]['VALUE']);
            };
        }
        
        $this->iBlock = new IBlock($arParams['IBLOCK_ID']);
        $this->arResult['ITEM'] = $this->iBlock->params($params)->getElement($fileHandle);
        $this->arResult['IBLOCK_ID'] = $this->iBlock->getIblockId();
        $this->arResult['ID'] = $this->arResult['ITEM']['ID'];
    }

    public function getSections(): void
    {
        $arParams = $this->arParams;

        $params = [
            'sort' => $arParams['SORT'] ?: ['sort' => 'ASC', 'date_active_from' => 'DESC', 'created_date' => 'DESC'],
            'filter' => $arParams['FILTER'] ?: ['ACTIVE' => 'Y'],
            'fields' => $arParams['FIELDS'] ?: ['NAME', 'CODE', 'DESCRIPTION', 'PICTURE'],
            'sizes' => $arParams['SIZES'] ?: ['width' => 657, 'height' => 354]
        ];
        
        $this->iBlock = new ISection($arParams['IBLOCK_ID']);
        $this->arResult['ITEMS'] = $this->iBlock->params($params)->getElements();
        $this->arResult['IBLOCK_ID'] = $this->iBlock->getIblockId();
    }

    protected function setEditButtons()
    {
        global $APPLICATION;

        if (!$APPLICATION->GetShowIncludeAreas() || $this->showEditButtons === false) {
            return false;
        }

        $this->arResult['AREA_ID'] = $this->arResult['ID'] ?? $this->arResult['SECTION_ID'] ?? $this->arResult['IBLOCK_ID'];

        $arButtons = \CIBlock::GetPanelButtons(
            $this->arResult['IBLOCK_ID'],
            $this->arResult['ID'] ?? 0,
            $this->arResult['SECTION_ID'] ?? 0,
            ['SECTION_BUTTONS' => true, 'SESSID' => false]
        );

        $APPLICATION->SetEditArea(
            $this->getEditAreaId($this->arResult['AREA_ID']),
            \CIBlock::GetComponentMenu('configure', $arButtons)
        );
    }

    /**
     * Getting array of errors.
     * @return Error[]
     */
    public function getErrors()
    {
        return $this->errorCollection->toArray();
    }

    /**
     * Getting once error with the necessary code.
     * @param string $code Code of error.
     * @return Error
     */
    public function getErrorByCode($code)
    {
        return $this->errorCollection->getErrorByCode($code);
    }
}
