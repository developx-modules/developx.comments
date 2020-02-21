<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<? $this->setFrameMode(true); ?>
<?

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
?>
<div class="comments-block">
    <div class="comments-block__content loadJs">
        <div class="comments-block__title">
            <?=Loc::getMessage('DX_CMT_TITLE')?>
        </div>

        <div class="comments-block__commnets">
            <? if (count($arResult['ITEMS']) > 0) { ?>
                <? foreach ($arResult['ITEMS'] as $item) { ?>
                    <?
                    $this->AddEditAction($item['ID'], $item['EDIT_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_EDIT"));
                    $this->AddDeleteAction($item['ID'], $item['DELETE_LINK'], CIBlock::GetArrayByID($item["IBLOCK_ID"], "ELEMENT_DELETE"), array("CONFIRM" => GetMessage('CT_BNL_ELEMENT_DELETE_CONFIRM')));
                    ?>
                    <div class="comment-item" id="<?=$this->GetEditAreaId($item['ID']);?>">
                        <div class="comment-item__ava">
                            <?= $item['S_NAME'] ?>
                        </div>
                        <div class="comment-item__name">
                            <?= $item['NAME'] ?>
                        </div>
                        <div class="comment-item__date">
                            <?= $item['DATE_CREATE'] ?>
                        </div>
                        <?if ($arParams['LIKES_SHOW'] == 'Y'){?>
                            <div class="comment-item__like likeJs <?= $item['CLASS'] ?>" data-id="<?= $item['ID'] ?>">
                                <?= $item['LIKES'] ?>
                            </div>
                        <?}?>
                        <div class="comment-item__text">
                            <?= $item['COMMENT'] ?>
                        </div>
                        <? if (!empty($item['ANSWER'])) { ?>
                            <div class="comment-item__answer">
                                <span>Ответ:</span>
                                <?= $item['ANSWER'] ?>
                            </div>
                        <? } ?>
                    </div>
                <? } ?>
            <? } else { ?>
                <p class="comments-block__no-comments noCommentJs "><?=Loc::getMessage('DX_CMT_NO_COMMENTS')?></p>
            <? } ?>
        </div>

        <? if (!empty($arResult["RESULT"])) { ?>
            <div class="comments-block__form_text">
                <? if ($arResult["RESULT"]['SUCCESS']) { ?>
                    <?=Loc::getMessage('DX_CMT_ADDED')?>
                <? } else { ?>
                    <?= $arResult["RESULT"]["MESSAGE"]; ?>
                <? } ?>
            </div>
        <? } else { ?>
            <form class="comments-block__form commentFormJs" data-goal="make_order"
                  action="<?= $arParams["PAGE_URL"] ?>"
                  method="post">
                <p class="comments-block__title"><?=Loc::getMessage('DX_CMT_FORM_TITLE')?></p>
                <? if ($arResult['OPTIONS']['CAPTCHA_ACTIVE'] == 'Y') { ?>
                    <input type="hidden" name="token" value="<? $_POST['token'] ?>">
                <? } ?>
                <? if ($arResult['USER']) { ?>
                    <input type="hidden" name="USER_ID" value="<? $arResult['USER']['ID'] ?>">
                <? } ?>
                <input type="hidden" name="ACTION" value="addComment">
                <? foreach ($arResult["FIELDS"] as $code => $field) { ?>
                    <span class="comments-block__group <? if (isset($field['ERROR'])) { ?>comments-block__error<? } ?> <? if (!empty($field['VALUE'])) { ?>not-empty<? } ?>">
                        <? if ($field["TYPE"] == "S") { ?>
                            <input type="text" name="RESULT[<?= $code ?>]" value="<?= $field["VALUE"] ?>"
                                   <? if ($field["IS_REQUIRED"] == "Y"){ ?>required<? } ?>>
                        <? } elseif ($field["TYPE"] == "HTML") { ?>
                            <textarea type="textarea" name="RESULT[<?= $code ?>]"
                                      <? if ($field["IS_REQUIRED"] == "Y"){ ?>required<? } ?>><?= $field["VALUE"] ?></textarea>
                        <? } ?>
                        <span class="comments-block__highlight"></span>
                        <span class="comments-block__bar"></span>
                        <label>
                            <?= $field["NAME"] ?> <? if ($field['IS_REQUIRED'] == 'Y') { ?>*<? } ?>
                        </label>
                        <span class="comments-block__error-text"><?= Loc::getMessage('DX_CMT_EMPTY_FIELD') ?></span>
                    </span>
                <? } ?>
                <div class="comments-block__buttons">
                    <div class="comments-block__btn comments-block__btn--cancel commentCancelJs"><?=Loc::getMessage('DX_CMT_FORM_CANCEL')?></div>
                    <input type="submit" value="<?=Loc::getMessage('DX_CMT_FORM_SENT')?>" class="comments-block__btn">
                </div>
            </form>
            <?if ($arResult['ADD_COMMENT_AVAL']){?>
                <div class="comments-block__btn addCommentJs"><?=Loc::getMessage('DX_CMT_FORM_ADD')?></div>
            <?}?>
        <? } ?>
    </div>
</div>

<? if (empty($_REQUEST["AJAX_CALL"]) && $_REQUEST["AJAX_CALL"] != 'Y') { ?>
    <? if ($arParams['INCLUDE_JQUERY'] == 'Y') { ?>
        <? $APPLICATION->AddHeadScript($templateFolder . "/jquery.min.js"); ?>
    <? } ?>
    <? if ($arResult['OPTIONS']['CAPTCHA_ACTIVE'] == 'Y') { ?>
        <script src='//www.google.com/recaptcha/api.js?render=<?= $arResult['OPTIONS']['CAPTCHA_KEY'] ?>'></script>
    <? } ?>
    <script>
        $(document).ready(function ($) {
            DevelopxComments_ = new DevelopxComments('<?=$arResult['OPTIONS']['CAPTCHA_ACTIVE']?>', '<?=$arResult['OPTIONS']['CAPTCHA_KEY']?>', '<?=$arParams['CAPTCHA_TYPE']?>');
        });
    </script>
<? } else { ?>
    <? if (empty($arResult["RESULT"]) && $arResult['OPTIONS']['CAPTCHA_ACTIVE'] == 'Y') { ?>
        <script>
            DevelopxComments_.resetCaptcha();
        </script>
    <? } ?>
<? } ?>