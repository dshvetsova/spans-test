<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

function getIblockIdByCode($code)
{
    try {
        Loader::includeModule('iblock');
        $app = Application::getInstance();
    } catch (LoaderException $e) {
        ShowError($e->getMessage());
    }

    $cache = $app->getCache();
    $taggedCache = $app->getTaggedCache();

    if ($cache->initCache(3600000, 'sections_codes', '/iblock')) {
        $result = $cache->getVars();
    } elseif ($cache->startDataCache()) {
        $result = [];
        $res = \CIBlock::GetList([], ['CHECK_PERMISSIONS' => 'N']);

        while ($row = $res->Fetch()) {
            $result[$row['CODE']] = (int)$row['ID'];
        }

        if (empty($result)) {
            $cache->abortDataCache();
        }

        $taggedCache->startTagCache('/iblocks');
        $taggedCache->registerTag('iblock_id_new');
        $taggedCache->endTagCache();

        $cache->endDataCache($result);
    }

    if (!empty($result[$code])) {
        return $result[$code];
    }

    return false;
}