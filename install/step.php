<?
use Bitrix\Main\Localization\Loc;
global $APPLICATION, $MODULE_ID;?>

<form action="<? echo $APPLICATION->GetCurPage(false) ?>" name="form1">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<? echo LANG ?>">
    <input type="hidden" name="id" value="<?= $GLOBALS['MODULE_ID'] ?>">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">


    <table class="pre-options" cellpadding="3" cellspacing="0" border="0" width="100%">

        <tr class="notice">
            <td><h2><?= Loc::getMessage('TRAVELSOFT_BX24CUSTOMIZER_ACTIONS_CHOOSE') ?></h2></td>
        </tr>

        <tr>
            <td>
                <input type="checkbox" name="install_actions[]" value="customization_of_telephony_popup_and_load_data_from_mastertour">
                <b><?= Loc::getMessage("TRAVELSOFT_BX24CUSTOMIZER_ACTION_1") ?></b>
                <br/>
                <input type="checkbox" name="install_actions[]" value="customization_of_create_deals">
                <b><?= Loc::getMessage("TRAVELSOFT_BX24CUSTOMIZER_ACTION_2") ?></b>
            </td>
        </tr>

        <tr class="next-btn"><td><br><input class="adm-btn-save" type="submit" name="next" value="<? echo GetMessage("TRAVELSOFT_BX24CUSTOMIZER_NEXT_BNT") ?>"></td></tr>
    </table>

</form>

