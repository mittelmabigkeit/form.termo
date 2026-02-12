<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Sale;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\Basket;
use Bitrix\Highloadblock as HL;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class AmigolabFormTermo extends CBitrixComponent
{
    private $_request;

    /**
     * Проверка наличия модулей требуемых для работы компонента
     * @return bool
     * @throws Exception
     */
    private function _checkModules()
    {
        if (!Loader::includeModule('iblock')) {
            throw new Main\LoaderException("Модуль iblock не установлен");
        }
        if (!Loader::includeModule('sale')) {
            throw new Main\LoaderException("Модуль sale не установлен");
        }
        if (!Loader::includeModule('catalog')) {
            throw new Main\LoaderException("Модуль catalog не установлен");
        }
        if (!Loader::includeModule('highloadblock')) {
            throw new Main\LoaderException("Модуль highloadblock не установлен");
        }
    }

    /**
     * Обертка над глобальной переменной
     * @return CAllMain|CMain
     */
    private function _app()
    {
        global $APPLICATION;
        return $APPLICATION;
    }

    /**
     * Обертка над глобальной переменной
     * @return CAllUser|CUser
     */
    private function _user()
    {
        global $USER;
        return $USER;
    }

    /**
     * Подготовка параметров компонента
     * @param $arParams
     * @return mixed
     */
    public function onPrepareComponentParams($arParams)
    {
        // тут пишем логику обработки параметров, дополнение параметрами по умолчанию
        // и прочие нужные вещи
        $result = [];
        foreach ($arParams as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }


    private $sizeCode = "RAZMER";

    /**
     * получение результатов
     */
    protected function getResult()
    {
        $this->arResult['AJAX_MODE'] = $this->arParams['AJAX_MODE'];
        $this->arResult['ID_FORM'] = $this->arParams['ID_FORM'];
    }

    /**
     * получаем товары для термонанесения, их размеры и цвета
     */
    protected function getTermoSettings()
    {
        function unique_multidim_array($array, $key)
        {
            $temp_array = array();
            $i = 0;
            $key_array = array();
            foreach ($array as $val) {
                if (!in_array($val[$key], $key_array)) {
                    $key_array[$i] = $val[$key];
                    $temp_array[$i] = $val;
                }
                $i++;
            }
            return $temp_array;
        }

        $arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'PREVIEW_TEXT', 'PROPERTY_NAIMENOVANIE_IM', 'PROPERTY_CML2_ARTICLE', 'PROPERTY_TERMO_PHOTO_BACK', 'PROPERTY_TERMO_PHOTO_FRONT', 'PROPERTY_TERMO_FONT_COLOR', 'PROPERTY_TERMO_SPONSOR_BACK', 'PROPERTY_TERMO_SPONSOR_FRONT'];

        if ($this->arParams["SPECIAL"] === true) {
            $arSelect = array_merge($arSelect, ["PROPERTY_TERMO_SPECIAL_FRONT", "PROPERTY_TERMO_SPECIAL_BACK"]);
        }


        $arFilter = ['IBLOCK_ID' => IBLOCK_CATALOG_ID, 'ACTIVE' => 'Y', 'PROPERTY_TERMO' => '119'];
        $res = CIBlockElement::GetList([], $arFilter, false, [], $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            if (!empty($arFields['PROPERTY_NAIMENOVANIE_IM_VALUE'])) {
                $arFields['NAME'] = $arFields['PROPERTY_NAIMENOVANIE_IM_VALUE'];
            }
            $arFields['PROPERTY_TERMO_PHOTO_BACK_VALUE'] = CFile::GetPath($arFields['PROPERTY_TERMO_PHOTO_BACK_VALUE']);
            $arFields['PROPERTY_TERMO_PHOTO_FRONT_VALUE'] = CFile::GetPath($arFields['PROPERTY_TERMO_PHOTO_FRONT_VALUE']);
            $arFields['PROPERTY_TERMO_SPONSOR_BACK_VALUE'] = CFile::GetPath($arFields['PROPERTY_TERMO_SPONSOR_BACK_VALUE']);
            $arFields['PROPERTY_TERMO_SPONSOR_FRONT_VALUE'] = CFile::GetPath($arFields['PROPERTY_TERMO_SPONSOR_FRONT_VALUE']);

            if ($this->arParams["SPECIAL"] === true) {
                $arFields['PROPERTY_TERMO_SPECIAL_FRONT_VALUE'] = CFile::GetPath($arFields['PROPERTY_TERMO_SPECIAL_FRONT_VALUE']);
                $arFields['PROPERTY_TERMO_SPECIAL_BACK_VALUE'] = CFile::GetPath($arFields['PROPERTY_TERMO_SPECIAL_BACK_VALUE']);
            }

            $minCount = \Bitrix\Main\Config\Option::get("grain.customsettings", "catalog_min_count");
            $resSize = CCatalogSKU::getOffersList($arFields['ID'], IBLOCK_CATALOG_ID, ['ACTIVE' => 'Y', 'AVAILABLE' => 'Y', '>CATALOG_QUANTITY' => $minCount],
                ['NAME, PROPERTY_' . $this->sizeCode], ['CODE' => [$this->sizeCode]]);
            foreach ($resSize[$arFields['ID']] as $arSize) {
                //$res = CIBlockElement::GetByID($arSize['ID']);
                //if($ar_res = $res->GetNext()) {
                //$arSize['PROPERTIES']['SIZE']['SKU_SORT'] = $ar_res['SORT'];
                //}
                $arSize['PROPERTIES'][$this->sizeCode]['OFFER_ID'] = $arSize['ID'];
                $arFields['SIZES'][] = $arSize['PROPERTIES'][$this->sizeCode];
            }
            usort($arFields['SIZES'], function ($a, $b) {
                return ($a['VALUE_SORT'] - $b['VALUE_SORT']);
            });

            //  pre( $arFields['SIZES']);
            $arFields['SIZES'] = unique_multidim_array($arFields['SIZES'], 'VALUE');
            $arFields['SIZES'][0]['CHECKED'] = 'Y';
            foreach ($arFields['SIZES'] as $key => $size) {
                $resColor = CCatalogSKU::getOffersList($arFields['ID'], IBLOCK_CATALOG_ID,
                    ['ACTIVE' => 'Y', 'PROPERTY_' . $this->sizeCode . '_VALUE' => $size['VALUE'], '>CATALOG_QUANTITY' => $minCount], ['NAME, PROPERTY_COLOR'],
                    ['CODE' => ['COLOR']]);
                $hlblock = HL\HighloadBlockTable::getById(2)->fetch();
                $entity = HL\HighloadBlockTable::compileEntity($hlblock);
                $entityClass = $entity->getDataClass();
                foreach ($resColor[$arFields['ID']] as $arColor) {
                    $result = $entityClass::getList([
                        'select' => ['UF_XML_ID', 'UF_FILE'],
                        'filter' => ['UF_XML_ID' => $arColor['PROPERTIES']['COLOR']['VALUE']]
                    ]);
                    $row = $result->fetch();
                    if (!empty($row)) {
                        $row['UF_FILE'] = CFile::GetPath($row['UF_FILE']);
                        $arFields['SIZES'][$key]['COLORS'][] = $row;
                    }
                }
            }
            if ($arFields['SIZES'][0]['COLORS']) {
                $arFields['COLORS'] = $arFields['SIZES'][0]['COLORS'];
            }
            $this->arResult['ITEMS'][] = $arFields;
        }

    }

    /**
     * преобразовываем результаты запросов
     */
    protected function queryConversion()
    {
        // устанавливаем выбранный товар первым в массиве
        if ($this->_request['type-form-id']) {
            $i = 1;
            foreach ($this->arResult['ITEMS'] as $item) {
                if ($this->_request['type-form-id'] == $item['ID']) {
                    $this->arResult['ITEMS'][0] = $item;
                } else {
                    $this->arResult['ITEMS'][$i] = $item;
                    $i++;
                }
            }
        }
        // отмечаем выбранный размер

        if ($this->_request['size']) {
            foreach ($this->arResult['ITEMS'][0]['SIZES'] as $keySize => $size) {

                if ($size['CHECKED']) {
                    unset($this->arResult['ITEMS'][0]['SIZES'][$keySize]['CHECKED']);
                }
            }
            $checked = false;
            foreach ($this->arResult['ITEMS'][0]['SIZES'] as $keySize => $size) {
                if ($this->_request['size'] == $size['VALUE']) {
                    $this->arResult['ITEMS'][0]['SIZES'][$keySize]['CHECKED'] = 'Y';
                    $checked = true;
                    if ($size['COLORS']) {
                        $this->arResult['ITEMS'][0]['COLORS'] = $size['COLORS'];
                    } else {
                        unset($this->arResult['ITEMS'][0]['COLORS']);
                    }
                }
            }
            if ($checked == false) {
                $this->arResult['ITEMS'][0]['SIZES'][0]['CHECKED'] = 'Y';
            }
        }
        // устанавливаем выбранный цвет первым в массиве
        if ($this->_request['color']) {
            foreach ($this->arResult['ITEMS'][0]['COLORS'] as $color) {
                if ($this->_request['color'] == $color['UF_XML_ID']) {
                    $stock = true;
                    break;
                }
            }
            if ($stock) {
                $i = 1;
                foreach ($this->arResult['ITEMS'][0]['COLORS'] as $color) {
                    if ($this->_request['color'] == $color['UF_XML_ID']) {
                        $this->arResult['ITEMS'][0]['COLORS'][0] = $color;
                    } else {
                        $this->arResult['ITEMS'][0]['COLORS'][$i] = $color;
                        $i++;
                    }
                }
            }
        }
    }

    /**
     * получаем список игроков
     */
    protected function getPlayers()
    {
        $arSelect = ['ID', 'NAME', 'PROPERTY_NUM'];
        $arFilter = ['IBLOCK_ID' => 12, 'ACTIVE' => 'Y'];
        $res = CIBlockElement::GetList(['PROPERTY_NUM' => 'ASC'], $arFilter, false, [], $arSelect);
        while ($ob = $res->Fetch()) {
            $this->arResult['PLAYERS'][] = $ob;
        }
        $this->arResult['CURRENT_TAB'] = 'my';
        $this->arResult['TYPE_TERMO'] = 'my-name';
        $this->arResult['CURRENT_PLAYER_NAME'] = $this->arResult['PLAYERS'][0]['NAME'];
        $this->arResult['CURRENT_PLAYER_NUMBER'] = $this->arResult['PLAYERS'][0]['PROPERTY_NUM_VALUE'];
        $this->arResult['CURRENT_MY_NAME'] = ($this->_user()->IsAuthorized()) ? \Bitrix\Main\Engine\CurrentUser::get()->getLastName() : "Александров";
        $this->arResult['CURRENT_MY_NUMBER'] = "99";
        $this->arResult['SHOW_MY_NAME'] = "Y";
        $this->arResult['SHOW_MY_NUMBER'] = "Y";
        $this->arResult['COUNT_NUM'] = 2;
    }

    /**
     * получаем цену товара
     */
    protected function getProductPrice()
    {
        if (!empty($this->arResult['ITEMS'][0]['SIZES'])) {
            foreach ($this->arResult['ITEMS'][0]['SIZES'] as $size) {
                if ($size['CHECKED']) {
                    $offerId = $size['OFFER_ID'];
                    break;
                }
            }
            $arSelect = ['ID', 'NAME', 'CATALOG_GROUP_1'];
            $arFilter = ['IBLOCK_ID' => IBLOCK_OFFERS_CATALOG_ID, 'ID' => $offerId, 'ACTIVE' => 'Y'];

            $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
            if ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();


                $this->arResult['PRODUCT_PRICE'] = $arFields['CATALOG_PRICE_1'];
                $this->arResult['PRODUCT_PRICE_FORMATED'] = CurrencyFormat($arFields['CATALOG_PRICE_1'],
                    $arFields['CATALOG_CURRENCY_1']);
            }
        } else {

            $arSelect = ['ID', 'NAME', 'CATALOG_GROUP_1'];
            $arFilter = ['IBLOCK_ID' => IBLOCK_CATALOG_ID, 'ID' => $this->arResult['ITEMS'][0]['ID'], 'ACTIVE' => 'Y'];
            $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
            if ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $this->arResult['PRODUCT_PRICE'] = $arFields['CATALOG_PRICE_1'];
                $this->arResult['PRODUCT_PRICE_FORMATED'] = CurrencyFormat($arFields['CATALOG_PRICE_1'],
                    $arFields['CATALOG_CURRENCY_1']);
            }
        }

    }

    /**
     * получаем цену услуги нанесения номера на футболку
     */
    protected function getServicePriceMyNumber()
    {
        $arSelect = ['ID', 'NAME', 'CATALOG_GROUP_1'];
        $arFilter = ['IBLOCK_ID' => $this->arParams["IBLOCK_TERMO_SERVICE"], 'ID' => $this->arParams["PRICE_ITEMS"]["NUMBER"], 'ACTIVE' => 'Y'];
        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        if ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $this->arResult['COUNT_NUMBER'] = $arFields['CATALOG_PRICE_1'];
            $this->arResult['NUMBER_PRICE'] = $arFields['CATALOG_PRICE_1'];
            $numberPrice = $arFields['CATALOG_PRICE_1'];
            $numberPriceFormated = $numberPrice;
            $numberPriceFormated = number_format($numberPriceFormated, 0, '.', ' ');
            $numberPriceFormated = '+' . $numberPriceFormated . ' <span>₽</span>';
            $this->arResult['NUMBER_PRICE_FORMATED'] = $numberPriceFormated;
        }
    }

    /**
     * получаем цену услуги нанесения имени на футболку
     */
    protected function getServicePriceMyName()
    {
        $arSelect = ['ID', 'NAME', 'CATALOG_GROUP_1'];
        $arFilter = ['IBLOCK_ID' => $this->arParams["IBLOCK_TERMO_SERVICE"], 'ID' => $this->arParams["PRICE_ITEMS"]["NAME"], 'ACTIVE' => 'Y'];
        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        if ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $this->arResult['NAME_PRICE'] = $arFields['CATALOG_PRICE_1'];
            $namePrice = $arFields['CATALOG_PRICE_1'];
            $namePriceFormated = $namePrice;
            $namePriceFormated = number_format($namePriceFormated, 0, '.', ' ');
            $namePriceFormated = '+' . $namePriceFormated . ' <span>₽</span>';
            $this->arResult['NAME_PRICE_FORMATED'] = $namePriceFormated;
        }
    }

    /**
     * получаем цену услуги нанесения спонсора
     */
    protected function getServicePriceSponsor()
    {
        $arSelect = ['ID', 'NAME', 'CATALOG_GROUP_1'];
        $arFilter = ['IBLOCK_ID' => $this->arParams["IBLOCK_TERMO_SERVICE"], 'ID' => $this->arParams["PRICE_ITEMS"]["SPONSOR"], 'ACTIVE' => 'Y'];
        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        if ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $this->arResult['SPONSOR_NAME'] = $arFields['NAME'];
            $this->arResult['SPONSOR_SELECTED'] = 'N';
            $this->arResult['SPONSOR_VALUE'] = '';
            $this->arResult['SPONSOR_PRICE'] = $arFields['CATALOG_PRICE_1'];
            $this->arResult['SHOW_SPONSOR'] = 'N';
            $this->arResult['SHOW_SIDE'] = 'BACK';
            $sponsorPrice = $arFields['CATALOG_PRICE_1'];
            $sponsorPriceFormated = $sponsorPrice;
            $sponsorPriceFormated = number_format($sponsorPriceFormated, 0, '.', ' ');
            $sponsorPriceFormated = $sponsorPriceFormated . ' ₽';
            $this->arResult['SPONSOR_PRICE_FORMATED'] = $sponsorPriceFormated;
        }
    }

    /**
     * получаем цену услуги нанесения по Спецпредложению
     */
    protected function getSpecialPrice()
    {
        if ($this->arParams["SPECIAL"] === true) {

            $arSelect = ['ID', 'NAME', 'CATALOG_GROUP_1'];
            $arFilter = ['IBLOCK_ID' => $this->arParams["IBLOCK_TERMO_SERVICE"], 'ID' => $this->arParams["PRICE_ITEMS"]["SPECIAL"], 'ACTIVE' => 'Y'];
            $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
            if ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $this->arResult['SPECIAL_NAME'] = $arFields['NAME'];
                $this->arResult['SPECIAL_SELECTED'] = 'N';
                $this->arResult['SPECIAL_VALUE'] = 'Y';
                $this->arResult['SPECIAL_PRICE'] = $arFields['CATALOG_PRICE_1'];
                $this->arResult['SPECIAL_SPONSOR'] = 'N';
                $specialPrice = $arFields['CATALOG_PRICE_1'];
                $specialPriceFormated = $specialPrice;
                $specialPriceFormated = number_format($specialPriceFormated, 0, '.', ' ');
                $specialPriceFormated = $specialPriceFormated . ' ₽';
                $this->arResult['SPECIAL_PRICE_FORMATED'] = $specialPriceFormated;
            }
        }
    }

    protected function setSpecialPrice()
    {
        foreach ($this->arParams["ZERO_PRICE_PRODUCT"] as $type) {
            $this->arResult[$type . '_PRICE'] = 0;
            $this->arResult[$type . '_PRICE_FORMATED'] = $this->arResult[$type . '_PRICE'] . ' ₽';
        }
    }

    /**
     * получаем текущую цену
     */
    protected function getCurrentPrices()
    {
        if ($this->arResult['CURRENT_TAB'] == "my") {
            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * 2);
            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                0, '.', '');
//            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * 2),
//                    0, '.', ' ') . ' ₽';
        } else {
            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'];
            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                0, '.', '');
//            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'],
//                    0, '.', ' ') . ' ₽';
        }
    }

    /**
     * Точка входа в компонент
     * Должна содержать только последовательность вызовов вспомогательых ф-ий и минимум логики
     * всю логику стараемся разносить по классам и методам
     */
    public function executeComponent()
    {
        $this->_checkModules();
        $this->_request = Application::getInstance()->getContext()->getRequest();
        $basket = Sale\Basket::loadItemsForFUser(Sale\Fuser::getId(), Bitrix\Main\Context::getCurrent()->getSite());
        //$currentUser = $this->_user()->GetById($this->_user()->GetId())->Fetch();

        $this->getResult();
        $this->getTermoSettings();
        $this->queryConversion();
        $this->getPlayers();
        $this->getProductPrice();
        $this->getServicePriceMyNumber();
        $this->getServicePriceMyName();
        $this->getServicePriceSponsor();
        $this->getCurrentPrices();
        $this->getSpecialPrice();

        try {
            // если форма отправлена
            if ($this->_request['AJAX_CALL'] == 'Y') {
                $this->arResult['CURRENT_TAB'] = $this->_request['current-tab'];
                $this->arResult['CURRENT_PLAYER_NAME'] = $this->_request['player-name'];
                $this->arResult['CURRENT_PLAYER_NUMBER'] = $this->_request['player-number'];
                $this->arResult['CURRENT_MY_NAME'] = $this->_request['my-name'];
                $this->arResult['CURRENT_MY_NUMBER'] = $this->_request['my-number'];
                $this->arResult['SHOW_MY_NAME'] = $this->_request['show-my-name'];
                $this->arResult['SHOW_MY_NUMBER'] = $this->_request['show-my-number'];
                if (!empty($this->_request['count-num'])) {
                    $this->arResult['COUNT_NUM'] = $this->_request['count-num'];
                } else {
                    $this->arResult['COUNT_NUM'] = 0;
                }
                $this->arResult['SPECIAL_VALUE'] = $this->_request['special-checkbox'];
                $this->arResult['TEMP'] = $this->_request['special-checkbox'];

                if ($this->_request['special-checkbox']) {
                    $this->arResult['SPECIAL_SELECTED'] = "Y";
                    $this->setSpecialPrice();
                }

                if ($this->_request['current-tab'] == 'my') {
                    if (!empty($this->_request['player-checkobox'])) {
                        if ($this->_request['show-my-name'] == 'Y' && !empty($this->_request['my-name']) && $this->_request['show-my-number'] == 'Y') {

                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']) + $this->arResult['SPONSOR_PRICE'];


                            //$this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'], 0, '.', ' ') . ' ₽';
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'], 0, '.', '');

                        } elseif ($this->_request['show-my-name'] == 'Y' && empty($this->_request['my-name']) && $this->_request['show-my-number'] == 'Y') {

                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']) + $this->arResult['SPONSOR_PRICE'];


                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'], 0, '.', '');
                            //$this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']) + $this->arResult['SPONSOR_PRICE'], 0, '.', ' ') . ' ₽';

                        } elseif ($this->_request['show-my-name'] == 'Y' && !empty($this->_request['my-name']) && $this->_request['show-my-number'] != 'Y') {

                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + $this->arResult['SPONSOR_PRICE'];


                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + $this->arResult['SPONSOR_PRICE'],
//                                    0, '.', ' ') . ' ₽';

                        } elseif ($this->_request['show-my-name'] == 'Y' && empty($this->_request['my-name']) && $this->_request['show-my-number'] != 'Y') {

                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + $this->arResult['SPONSOR_PRICE'];
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + $this->arResult['SPONSOR_PRICE'],
//                                    0, '.', ' ') . ' ₽';

                        } elseif ($this->_request['show-my-name'] != 'Y' && $this->_request['show-my-number'] == 'Y') {

                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']) + $this->arResult['SPONSOR_PRICE'];
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']) + $this->arResult['SPONSOR_PRICE'],
//                                    0, '.', ' ') . ' ₽';

                        } else {

                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + $this->arResult['SPONSOR_PRICE'];
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['SPONSOR_PRICE'],
//                                    0, '.', ' ') . ' ₽';

                        }
                    } else {
                        if ($this->_request['show-my-name'] == 'Y' && !empty($this->_request['my-name']) && $this->_request['show-my-number'] == 'Y') {

                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']);
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']),
//                                    0, '.', ' ') . ' ₽';

                        } elseif ($this->_request['show-my-name'] == 'Y' && empty($this->_request['my-name']) && $this->_request['show-my-number'] == 'Y') {

                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']);
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']),
//                                    0, '.', ' ') . ' ₽';

                        } elseif ($this->_request['show-my-name'] == 'Y' && !empty($this->_request['my-name']) && $this->_request['show-my-number'] != 'Y') {
                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'];
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'],
//                                    0, '.', ' ') . ' ₽';
                        } elseif ($this->_request['show-my-name'] == 'Y' && empty($this->_request['my-name']) && $this->_request['show-my-number'] != 'Y') {
                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'];
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'],
//                                    0, '.', ' ') . ' ₽';
                        } elseif ($this->_request['show-my-name'] != 'Y' && $this->_request['show-my-number'] == 'Y') {
                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']);
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + ($this->arResult['NUMBER_PRICE'] * $this->arResult['COUNT_NUM']),
//                                    0, '.', ' ') . ' ₽';
                        } else {
                            $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'];
                            $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                                0, '.', '');
//                            $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'],
//                                    0, '.', ' ') . ' ₽';
                        }
                    }
                } else {
                    if (!empty($this->_request['player-checkobox'])) {
                        $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * strlen($this->_request['player-number'])) + $this->arResult['SPONSOR_PRICE'];
                        $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'], 0, '.', '');
//                        $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * strlen($this->_request['player-number'])) + $this->arResult['SPONSOR_PRICE'],
//                                0, '.', ' ') . ' ₽';
                    } else {
                        $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * strlen($this->_request['player-number']));
                        $this->arResult['CURRENT_TOTAL_PRICE'] = number_format($this->arResult['CURRENT_TOTAL_PRICE'],
                            0, '.', '');
//                        $this->arResult['CURRENT_TOTAL_PRICE_FORMATED'] = number_format($this->arResult['PRODUCT_PRICE'] + $this->arResult['NAME_PRICE'] + ($this->arResult['NUMBER_PRICE'] * strlen($this->_request['player-number'])),
//                                0, '.', ' ') . ' ₽';
                    }
                }

                if ($this->_request['special-checkbox']) {
                    $this->arResult['CURRENT_TOTAL_PRICE'] = $this->arResult['CURRENT_TOTAL_PRICE'] + $this->arResult['SPECIAL_PRICE'];
                    $this->arResult['IS_SPECIAL'] = true;
                }

                if (!empty($this->_request['player-checkobox'])) {
                    if (!empty($this->arResult['ITEMS'][0]['PROPERTY_TERMO_SPONSOR_FRONT_VALUE']) && !empty($this->arResult['ITEMS'][0]['PROPERTY_TERMO_SPONSOR_BACK_VALUE'])) {
                        $this->arResult['SPONSOR_SELECTED'] = 'Y';
                        $this->arResult['SPONSOR_VALUE'] = $this->_request['player-checkobox'];
                        $this->arResult['SHOW_SPONSOR'] = $this->_request['show-sponsor'];
                    }
                } else {
                    $this->arResult['SPONSOR_SELECTED'] = 'N';
                    $this->arResult['SPONSOR_VALUE'] = '';
                    $this->arResult['SHOW_SPONSOR'] = $this->_request['show-sponsor'];
                }
                if ($this->_request['type-termo'] == 'player-name') {
                    $this->arResult['TYPE_TERMO'] = $this->_request['type-termo'];
                } elseif ($this->_request['type-termo'] == 'my-name') {
                    $this->arResult['TYPE_TERMO'] = $this->_request['type-termo'];
                }
                if ($this->_request['show-side'] == 'FRONT') {
                    $this->arResult['SHOW_SIDE'] = $this->_request['show-side'];
                } elseif ($this->_request['show-side'] == 'BACK') {
                    $this->arResult['SHOW_SIDE'] = $this->_request['show-side'];
                }
                if ($this->_request['get-buy'] == 'Y') {
                    if (empty($this->_request['type-form-id']) || empty($this->_request['size'])) {
                        if (empty($this->_request['type-form-id'])) {
                            $this->arResult['ERROR'][0] = 'Отсутсвует идентификатор товара';
                        } else {
                            $this->arResult['TYPE_FORM_ID'] = $this->_request['type-form-id'];
                        }
                        if (empty($this->_request['size'])) {
                            $this->arResult['ERROR'][1] = 'Отсутсвует размер товара';
                        } else {
                            $this->arResult['SIZE'] = $this->_request['size'];
                        }
                    } else {
                        $this->arResult['TYPE_FORM_ID'] = $this->_request['type-form-id'];
                        $this->arResult['SIZE'] = $this->_request['size'];
                    }
                    if (!empty($this->_request['color'])) {
                        $this->arResult['COLOR'] = $this->_request['color'];
                    }
                    if (empty($this->arResult['ERROR'])) {
                        $minCount = \Bitrix\Main\Config\Option::get("grain.customsettings", "catalog_min_count");
                        $resOffer = CCatalogSKU::getOffersList($this->arResult['TYPE_FORM_ID'], IBLOCK_CATALOG_ID, [
                            'ACTIVE' => 'Y',
                            'AVAILABLE' => 'Y',
                            'PROPERTY_' . $this->sizeCode . '_VALUE' => $this->arResult['SIZE'],
                            '>CATALOG_QUANTITY' => $minCount
                        ], ['PROPERTY_COLOR'], []);
                        $countOffers = count($resOffer[$this->arResult['TYPE_FORM_ID']]);
                        if ($countOffers > 1) {
                            $resOffer[$this->arResult['TYPE_FORM_ID']] = array_values($resOffer[$this->arResult['TYPE_FORM_ID']]);
                            $i = 1;
                            foreach ($resOffer[$this->arResult['TYPE_FORM_ID']] as $arOffer) {
                                if ($this->arResult['COLOR'] == $arOffer['PROPERTY_COLOR_VALUE']) {
                                    $resOffer[$this->arResult['TYPE_FORM_ID']][0] = $arOffer;
                                } else {
                                    $resOffer[$this->arResult['TYPE_FORM_ID']][$i] = $arOffer;
                                    $i++;
                                }
                            }
                        }
                        $termoParams = [];
                        if ($this->_request['type-termo'] == 'player-name') {
                            $termoParams['NAME'] = $this->_request['player-name'];
                            $termoParams['NUMBER'] = $this->_request['player-number'];
                        } elseif ($this->_request['type-termo'] == 'my-name') {
                            $termoParams['NAME'] = $this->_request['my-name'];
                            $termoParams['NUMBER'] = $this->_request['my-number'];
                        }
                        if (!empty($this->_request['player-checkobox'])) {
                            $sponsor = $this->_request['player-checkobox'];
                        } else {
                            $sponsor = '';
                        }
                        foreach ($resOffer[$this->arResult['TYPE_FORM_ID']] as $arOffer) {
                            if ($item = $basket->getExistsItem('catalog', $arOffer['ID'])) {
                                $item->setField('QUANTITY', $item->getQuantity() + 1);
                            } else {
                                $item = $basket->createItem('catalog', $arOffer['ID']);
                                $item->setFields([
                                    'QUANTITY' => 1,
                                    'CURRENCY' => Bitrix\Currency\CurrencyManager::getBaseCurrency(),
                                    'LID' => Bitrix\Main\Context::getCurrent()->getSite(),
                                    'PRICE' => $this->arResult['CURRENT_TOTAL_PRICE'],
                                    'CUSTOM_PRICE' => 'Y',
                                    'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider'
                                ]);
                                $item->getPropertyCollection()->setProperty([
                                    [
                                        'NAME' => 'Термонанесение Имя',
                                        'CODE' => 'TERMO_NAME',
                                        'VALUE' => $termoParams['NAME']
                                    ],
                                    [
                                        'NAME' => 'Термонанесение Номер',
                                        'CODE' => 'TERMO_NUMBER',
                                        'VALUE' => $termoParams['NUMBER']
                                    ],
                                    [
                                        'NAME' => 'Cпонсор',
                                        'CODE' => 'TERMO_SPONSOR',
                                        'VALUE' => $sponsor
                                    ],
                                    [
                                        'NAME' => 'Размер',
                                        'CODE' => 'RAZMER',
                                        'VALUE' => $this->_request['size']
                                    ],
                                    [
                                        'NAME' => 'Спецпредложение',
                                        'CODE' => 'TERMO_SPECIAL',
                                        'VALUE' => $this->_request['special-checkbox'] ? $this->arResult['SPECIAL_NAME'] : null
                                    ]
                                ]);
                            }
                            $basket->save();
                            LocalRedirect('/personal/cart/');
                            break;
                        }
                    }
                }
            }
            $this->includeComponentTemplate();
        } catch (Exception $e) {
            $this->arResult['ERROR'] = $e->getMessage();
        }
    }
}
