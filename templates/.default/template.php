<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addJs(SITE_TEMPLATE_PATH_DEFAULT_SHOP . 'dist/scripts/circletype.min.js');
//pre($arResult);
?>
<? if (!empty($arResult['ITEMS'])) { ?>

    <form id="<?= $arParams["ID_FORM"]; ?>" action="<?= $_SERVER["PHP_SELF"] ?>" method="POST">
        <section class="section relative choose-print"
                 style="background-image: url('<?= SITE_TEMPLATE_PATH_DEFAULT_SHOP; ?>dist/img/constructor-bg.png');">
            <div class="container">
                <div class="title-1 max-w-sm">Термонанесение<span class="text-loko-blue-green"> на футболку</span></div>

                <div class="relative grid grid-cols-1 md:grid-cols-2 xl:grid-cols-[28.9855%_42.0289%_28.9855%] gap-12 md:gap-4 items-start mt-10 lg:mt-0 mb-22 lg:mb-32 z-10">
                    <div class="choose-print__left">
                        <div class="choose-print__left__wrap">
                            <div class="choose-select" data-name-input="type-form">
                                <select class="form-choose-s" id="choiseProd">
                                    <? foreach ($arResult['ITEMS'] as $item) { ?>
                                        <option value="<?= $item['NAME'] ?>" class="form-choose" data-avaible="Y"
                                                data-name="<?= $item['NAME'] ?>"
                                                data-id="<?= $item['ID'] ?>" <?= ($arResult['ITEMS'][0]['NAME'] == $item['NAME']) ? 'selected' : false; ?>>
                                            <?= $item['NAME'] ?>
                                        </option>
                                    <? } ?>
                                </select>
                                <svg class="absolute right-0 top-2 w-2.5 h-2.5 pointer-events-none">
                                    <use xlink:href="<?= SITE_TEMPLATE_PATH_DEFAULT_SHOP; ?>dist/img/icons.svg#select-arrow"></use>
                                </svg>
                            </div>
                            <div class="choose-select__type">Футболка</div>
                            <div class="choose__props">
                                <div class="choose__size">
                                    <? foreach ($arResult['ITEMS'][0]["SIZES"] as $size) { ?>
                                        <label class="choose__size-radio" for="<?= $size['VALUE'] ?>">
                                            <input class="peer"
                                                   type="radio" <?= ($size['CHECKED'] == 'Y') ? 'checked="checked"' : false; ?>
                                                   data-id="<?= $size['VALUE'] ?>" id="<?= $size['VALUE'] ?>"
                                                   name="size"
                                                   value="<?= $size['VALUE'] ?>">
                                            <span class="choose__size-radio-text"><?= $size['VALUE'] ?></span>
                                        </label>
                                    <? } ?>
                                </div>
                                <? if ($arResult['ITEMS'][0]['COLORS']) { ?>
                                    <div class="ml-auto" data-name-input="color">
                                        <select class="custom-select --color" name="color" id="color">
                                            <? foreach ($arResult['ITEMS'][0]['COLORS'] as $color) { ?>
                                                <option data-name="<?= $color['UF_XML_ID'] ?>"
                                                        value="<?= $color['UF_XML_ID'] ?>"
                                                        data-color="<?= $color['UF_XML_ID'] ?>"
                                                        style="background-image: url(<?= $color['UF_FILE'] ?>);" <?= ($arResult['ITEMS'][0]['COLORS'][0]['UF_XML_ID'] == $color['UF_XML_ID']) ? 'selected' : false; ?>></option>
                                            <? } ?>
                                        </select>
                                    </div>
                                <? } ?>
                            </div>

                            <!-- <div class="choose__bot">JOMA Футболка Локомотив TM10601A0102 &#40;Белый&#41; </div> -->
                            <div class="choose__desc">В нанесении запрещено использовать нецензурные выражения,
                                провокационные
                                высказывания, оскорбляющие честь и достоинство, деловую репутацию физических и
                                юридических лиц
                            </div>
                            <? if (!empty($arResult['ITEMS'][0]['PROPERTY_CML2_ARTICLE_VALUE'])) { ?>
                                <div class="choose__articl">
                                    Артикул: <?= $arResult['ITEMS'][0]['PROPERTY_CML2_ARTICLE_VALUE'] ?></div>
                            <? } ?>
                        </div>
                    </div>

                    <div class="choose-print__mid order-first xl:order-none md:col-span-2 xl:col-span-1 mb-16 xl:mb-0">
                        <div class="choose__slider swiper max-w-[542px]" id="choose__slider">
                            <div class="swiper-wrapper">
                                <? if ($arResult['SHOW_SPONSOR'] == "N") { ?>
                                    <div class="swiper-slide">
                                        <div class="choose__slider__item preview"
                                             style="background-image: url('<?= $arResult['ITEMS'][0]['PROPERTY_TERMO_PHOTO_BACK_VALUE'] ?>');">
                                            <? if ($arResult['CURRENT_TAB'] == "player") { ?>
                                                <div class="choose__slider__item__name"
                                                     id="playerName" <?= (!empty($arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'])) ? 'style="color:' . $arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'] . ' "' : false; ?>>
                                                    <div><?= $arResult['CURRENT_PLAYER_NAME'] ?></div>
                                                </div>
                                                <div class="choose__slider__item__num" <?= (!empty($arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'])) ? 'style="color:' . $arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'] . ' "' : false; ?>><?= $arResult['CURRENT_PLAYER_NUMBER'] ?></div>
                                            <? } else { ?>
                                                <div class="choose__slider__item__name"
                                                     id="playerName" <?= (!empty($arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'])) ? 'style="color:' . $arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'] . ' "' : false; ?>>
                                                    <div><?= $arResult['CURRENT_MY_NAME'] ?></div>
                                                </div>
                                                <div class="choose__slider__item__num" <?= (!empty($arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'])) ? 'style="color:' . $arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'] . ' "' : false; ?>><?= $arResult['CURRENT_MY_NUMBER'] ?></div>
                                            <? } ?>
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="choose__slider__item detail"
                                             style="background-image: url('<?= $arResult['ITEMS'][0]['PROPERTY_TERMO_PHOTO_FRONT_VALUE'] ?>');">
                                            <?
                                            if ($arResult['ITEMS'][0]["PROPERTY_TERMO_SPECIAL_FRONT_VALUE"] && $arResult['IS_SPECIAL']) {
                                                ?>
                                                <div class="choose__slider__item_image">
                                                    <img src="<?= $arResult['ITEMS'][0]["PROPERTY_TERMO_SPECIAL_FRONT_VALUE"]; ?>">
                                                </div>
                                                <?
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <? } elseif ($arResult['SHOW_SPONSOR'] == "Y") { ?>
                                    <div class="swiper-slide">
                                        <div class="choose__slider__item preview"
                                             style="background-image: url('<?= $arResult['ITEMS'][0]['PROPERTY_TERMO_SPONSOR_BACK_VALUE'] ?>');">
                                            <!-- <img class="sponsor-top w-full"
                                                 src="https://shop.fclm.ru/upload/iblock/bec/ebacjnxom8e511wltwjh8iaulo8gmy7j.png"> -->

                                            <? if ($arResult['CURRENT_TAB'] == "player") { ?>
                                                <div class="choose__slider__item__name"
                                                     id="playerName" <?= (!empty($arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'])) ? 'style="color:' . $arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'] . ' "' : false; ?>>
                                                    <div><?= $arResult['CURRENT_PLAYER_NAME'] ?></div>
                                                </div>
                                                <div class="choose__slider__item__num" <?= (!empty($arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'])) ? 'style="color:' . $arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'] . ' "' : false; ?>><?= $arResult['CURRENT_PLAYER_NUMBER'] ?></div>
                                            <? } else { ?>
                                                <div class="choose__slider__item__name"
                                                     id="playerName" <?= (!empty($arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'])) ? 'style="color:' . $arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'] . ' "' : false; ?>>
                                                    <div><?= $arResult['CURRENT_MY_NAME'] ?></div>
                                                </div>
                                                <div class="choose__slider__item__num" <?= (!empty($arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'])) ? 'style="color:' . $arResult['ITEMS'][0]['PROPERTY_TERMO_FONT_COLOR_VALUE'] . ' "' : false; ?>><?= $arResult['CURRENT_MY_NUMBER'] ?></div>
                                            <? } ?>

                                            <!-- <img class="sponsor-bottom" src="https://shop.fclm.ru/upload/iblock/bec/ebacjnxom8e511wltwjh8iaulo8gmy7j.png"> -->
                                        </div>
                                    </div>
                                    <div class="swiper-slide">
                                        <div class="choose__slider__item detail"
                                             style="background-image: url('<?= $arResult['ITEMS'][0]['PROPERTY_TERMO_SPONSOR_FRONT_VALUE'] ?>');">
                                            <?
                                            if ($arResult['ITEMS'][0]["PROPERTY_TERMO_SPECIAL_FRONT_VALUE"] && $arResult['IS_SPECIAL']) {
                                                ?>
                                                <div class="choose__slider__item_image">
                                                    <img src="<?= $arResult['ITEMS'][0]["PROPERTY_TERMO_SPECIAL_FRONT_VALUE"]; ?>">
                                                </div>
                                                <?
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <? } ?>
                            </div>
                            <div class="choose__slider__bot"></div>
                        </div>
                    </div>

                    <div class="choose-print__right">
                        <div class="print__type">
                            <label class="print__type__label js-player-name">
                                <input class="peer" type="radio" name="print__type"
                                       value="player" <?= ($arResult['CURRENT_TAB'] == "player") ? "checked" : false; ?>>
                                <svg class="print__type__label-ico">
                                    <use xlink:href="<?= SITE_TEMPLATE_PATH_DEFAULT_SHOP; ?>dist/img/icons.svg#play"></use>
                                </svg>
                                <span>имя игрока</span>
                            </label>
                            <label class="print__type__label js-my-name">
                                <input class="peer" type="radio" name="print__type"
                                       value="my" <?= ($arResult['CURRENT_TAB'] == "my") ? "checked" : false; ?>>
                                <svg class="print__type__label-ico">
                                    <use xlink:href="<?= SITE_TEMPLATE_PATH_DEFAULT_SHOP; ?>dist/img/icons.svg#play"></use>
                                </svg>
                                <span>свое имя</span>
                            </label>
                        </div>

                        <div class="termo__player-name" <?= ($arResult['CURRENT_TAB'] == 'player') ? false : 'style="display: none;"'; ?>
                             data-name-input="player-name">
                            <div class="select-custom-holder">
                                <div class="select-styled filed">
                                    <div class="select-text">
                                        #<?= $arResult['CURRENT_PLAYER_NUMBER'] ?> <?= $arResult['CURRENT_PLAYER_NAME'] ?></div>
                                </div>
                                <ul class="select-options" style="display: none;" name="player-name" id="player-name">
                                    <? foreach ($arResult['PLAYERS'] as $player) { ?>
                                        <li data-num="<?= $player['PROPERTY_NUM_VALUE'] ?>"
                                            data-name="<?= $player['NAME'] ?>">
                                            #<?= $player['PROPERTY_NUM_VALUE'] ?> <?= $player['NAME'] ?>
                                        </li>
                                    <? } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="termo__personal-name" >
                            <div <?= ($arResult['CURRENT_TAB'] == 'my') ? false : 'style="display: none;"'; ?>>


                            <div class="termo__item-content">
                                <? if ($arResult['SHOW_MY_NAME'] == 'Y') { ?>
                                    <div class="termo__item-row">
                                        <input class="print-input" type="text"
                                               value="<?= $arResult['CURRENT_MY_NAME'] ?>"
                                               maxlength="13"
                                               id="print-input-name"
                                               name="my-name">
                                        <div class="termo__item-inputs">
                                            <div class="print-input__bot">
                                                Введите ваше имя
                                                <div class="termo__remove-field" data-type="name" id="remove-name">
                                                    <span>Убрать поле</span>
                                                </div>
                                            </div>
                                            <div class="print__type-price">
                                                <?= $arResult['NAME_PRICE_FORMATED'] ?>
                                            </div>
                                        </div>
                                    </div>
                                <? } else { ?>
                                    <div class="termo__item-btn" data-type="name" id="add-name">
                                        Добавить фамилию
                                    </div>
                                <? } ?>
                            </div>
                            <div class="termo__item-content">
                                <? if ($arResult['SHOW_MY_NUMBER'] == 'Y') { ?>
                                    <div class="termo__item-row">
                                        <input class="print-input" type="text"
                                               value="<?= $arResult['CURRENT_MY_NUMBER'] ?>" id="print-input-num"
                                               name="my-number"
                                               maxlength="2">
                                        <div class="termo__item-inputs">
                                            <div class="print-input__bot">
                                                Введите номер
                                                <div class="termo__remove-field" data-type="number" id="remove-number">
                                                    <span>Убрать поле</span>
                                                </div>
                                            </div>
                                            <div class="print__type-price">
                                                <?= $arResult['NUMBER_PRICE_FORMATED'] ?>
                                            </div>
                                        </div>
                                    </div>
                                <? } else { ?>
                                    <div class="termo__item-btn" data-type="number" id="add-number">
                                        Добавить номер
                                    </div>
                                <? } ?>
                            </div>
                            </div>
                            <? if (!empty($arResult['ITEMS'][0]['PROPERTY_TERMO_SPONSOR_FRONT_VALUE']) && !empty($arResult['ITEMS'][0]['PROPERTY_TERMO_SPONSOR_BACK_VALUE'])) { ?>
<!--
                                <label class="group input-checkbox shrink-0 mt-2 checkbox-sponsor"
                                       for="player-checkobox-1">
                                    <input type="checkbox" name="player-checkobox" id="player-checkobox-1"
                                           value="<?= $arResult['SPONSOR_VALUE'] ?>"<?= ($arResult['SPONSOR_SELECTED'] == 'Y') ? ' checked' : false; ?>/>
                                    <div class="input-checkbox__ico text-loko-blue-green">
                                        <svg width="19" height="14" viewBox="0 0 19 14" fill="none">
                                            <path d="M2 7L7 12L17 2" stroke="currentColor" stroke-width="2"
                                                  stroke-linecap="square"></path>
                                        </svg>
                                    </div>
                                    <div class="input-checkbox__text">
                                        <span id="sponsor_value"
                                              data-value="<?= $arResult['SPONSOR_NAME'] ?>"><?= $arResult['SPONSOR_NAME'] ?> +<?= $arResult['SPONSOR_PRICE_FORMATED']; ?></span>
                                    </div>
                                </label>!-->
                            <? } ?>

                            <? if ($arParams["SPECIAL"] === true) { ?>

                                <label class="group input-checkbox shrink-0 mt-2 checkbox-sponsor"
                                       for="special-checkbox">
                                    <input type="checkbox" name="special-checkbox" id="special-checkbox"
                                           value="Y" <?= ($arResult['SPECIAL_SELECTED'] == 'Y') ? ' checked' : false; ?>/>
                                    <div class="input-checkbox__ico text-loko-blue-green">
                                        <svg width="19" height="14" viewBox="0 0 19 14" fill="none">
                                            <path d="M2 7L7 12L17 2" stroke="currentColor" stroke-width="2"
                                                  stroke-linecap="square"></path>
                                        </svg>
                                    </div>
                                    <div class="input-checkbox__text">
                                        <span id="sponsor_value"
                                              data-value="<?= $arResult['SPECIAL_NAME'] ?>"><?= $arResult['SPECIAL_NAME'] ?> за <?= $arResult['SPECIAL_PRICE_FORMATED']; ?></span>
                                    </div>
                                </label>


                            <? } ?>


                        </div>

                        <div class="print__bottom-wrap">
                            <div class="print__price"><span class="value_price"
                                                            data-price="<?= $arResult['CURRENT_TOTAL_PRICE'] ?>"><?= $arResult['CURRENT_TOTAL_PRICE'] ?></span>
                                <span class="currnecy">₽</span>
                            </div>

                            <input type="hidden" name="get-buy" value="N">
                            <button class="btn btn--special btn-v" type="button" name="buy">
                                <span>В корзину</span>
                                <div class="btn--special__line"></div>
                            </button>
                            <button class="btn btn--special btn-v" type="submit" id="sendForm"
                                    style="display: none;">В корзину
                            </button>

                            <input type="hidden" name="type-form-id"
                                   value="<?= $arResult['ITEMS'][0]['ID'] ?>"/>
                            <input type="hidden" name="type-form" value="<?= $arResult['ITEMS'][0]['NAME'] ?>"/>
                            <? if ($arResult['ITEMS'][0]['COLORS']) { ?>
                                <input type="hidden" name="color"
                                       value="<?= $arResult['ITEMS'][0]['COLORS'][0]['UF_XML_ID'] ?>"/>
                            <? } ?>
                            <input type="hidden" name="count-num" value="<?= $arResult['COUNT_NUM'] ?>"/>
                            <input type="hidden" name="show-sponsor" value="<?= $arResult['SHOW_SPONSOR'] ?>"/>
                            <input type="hidden" name="show-my-name" value="<?= $arResult['SHOW_MY_NAME'] ?>"/>
                            <input type="hidden" name="show-my-number" value="<?= $arResult['SHOW_MY_NUMBER'] ?>"/>
                            <input type="hidden" name="type-termo" value="<?= $arResult['TYPE_TERMO'] ?>"/>
                            <input type="hidden" name="current-tab" value="<?= $arResult['CURRENT_TAB'] ?>"/>
                            <input type="hidden" name="player-name" value="<?= $arResult['CURRENT_PLAYER_NAME'] ?>"/>
                            <input type="hidden" name="player-number"
                                   value="<?= $arResult['CURRENT_PLAYER_NUMBER'] ?>"/>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script>
            var chooseSlider = new Swiper(".choose__slider", {
                effect: "flip",
                grabCursor: true,
            });
        </script>
    </form>
<? } ?>