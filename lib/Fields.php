<?php

namespace travelsoft\bx24customizer;


class Fields
{
    private static function getCodeField($codeField)
    {
        return \Bitrix\Main\Config\Option::get("travelsoft.bx24customizer", $codeField);
    }

    public static function getIdCodeField()
    {
        return self::getCodeField('MASTERTOUR_ID_CODE_FIELD');
    }

    public static function getInfoCodeField()
    {
        return self::getCodeField('MASTERTOUR_INFO_DEAL_CODE_FIELD');
    }

    public static function getSecretApiKey()
    {
        return self::getCodeField('MASTERTOUR_SECRET_API_KEY');
    }

    public static function getMasterApiUrl()
    {
        return self::getCodeField('MASTERTOUR_API_URL');
    }
}