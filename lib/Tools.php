<?php

namespace travelsoft\bx24customizer;

/**
 * Tools clss
 *
 * @author dimabresky
 */
class Tools
{

    /**
     * @param mixed $var
     */
    public static function dump($var)
    {
        echo "<pre>";
        var_dump($var);
        echo "</pre>";
    }

    /**
     * @param mixed $var
     */
    public static function toLog($var)
    {
        ob_start();
        self::dump($var);
        \file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/local/travelsoft.bx24customizer.log.txt", ob_get_clean());
    }

    /**
     * @global CMain $APPLICATION
     * @param array $parameters
     */
    public static function sendJsonResponse(array $parameters)
    {
        global $APPLICATION;

        \header('Content-Type: application/json; charset=' . \SITE_CHARSET);

        $APPLICATION->RestartBuffer();

        echo \Bitrix\Main\Web\Json::encode($parameters);

        die();
    }

    public static function send404()
    {
        $protocol = \filter_input(\INPUT_SERVER, 'SERVER_PROTOCOL');
        \header($protocol . " 404 Not Found");
        die();
    }

    /**
     * @param string $phone
     * @return string
     */
    public static function normalizePhone(string $phone)
    {
        return str_replace(
            ["+", "(", ")", " ", "-", "МТС"], ["", "", "", "", "", ""], $phone
        );
    }

    /**
     * @param string $currency
     * @return mixed
     */
    private static function normalizeCurrency(string $currency)
    {
        $arrCurrencyMatching = [
            '$' => 'USD',
            'EU' => 'EUR',
            'BN' => 'BYN',
        ];

        return $arrCurrencyMatching[$currency] ?? '';
    }

    /**
     * @param string $phone
     * @return array
     */
    public static function getLeadByPhone(string $phone)
    {
        $arLead = [];

        if (\Bitrix\Main\Loader::includeModule("crm")) {

            $arFields = \CCrmFieldMulti::GetList([], [
                "ENTITY_ID" => "LEAD",
                "TYPE_ID" => "PHONE",
                "VALUE" => self::normalizePhone($phone)
            ])->Fetch();

            if (@$arFields["ID"] > 0 && @$arFields["ELEMENT_ID"] > 0) {
                $arLead = \CCrmLead::GetList(false, ["ID" => $arFields["ELEMENT_ID"], "CHECK_PERMISSIONS" => "N"])->Fetch();
            }
        }

        return $arLead;
    }

    /**
     * @param int $leadId
     * @param array $save
     * @return $mixed
     */
    public static function saveLeadInfo(int $leadId, array $save)
    {
        $lead = new \CCrmLead(false);

        if ($leadId > 0) {
            return $lead->Update($leadId, $save);
        } else {
            return $lead->Add($save);
        }
    }

    private static function updateOrCreateDeal($dealFields, $agreementCode = null)
    {
        $deal = new \CCrmDeal(false);

        $dealId = '';
        if (!is_null($agreementCode)) {
            $arDeal = \CCrmDeal::GetList(false, [Fields::getIdCodeField() => $agreementCode, "CHECK_PERMISSIONS" => "N"])->Fetch();

            $dealId = $arDeal['ID'];
        }

        if ($dealId > 0) {
            return $deal->Update($dealId, $dealFields);
        } else {
            return $deal->Add($dealFields);
        }
    }

    /**
     * @param string $phone
     * @return string
     */
    public static function createMastertourSignByLeadPhone(string $phone)
    {
        $secretApiKey = Fields::getSecretApiKey();

        return md5($phone . $secretApiKey);
    }

    /**
     * @param string $dateBegin
     * @param string $dateEnd
     * @return string
     */
    public static function createMastertourSignatureByDates(string $dateBegin, string $dateEnd)
    {
        $secretApiKey = Fields::getSecretApiKey();

        return md5($dateBegin . $dateEnd . $secretApiKey);
    }

    /**
     * @param array $data
     * @return string
     */
    public static function createMastertourLeadInfoForSave(array $data)
    {
        $delemiter = "\r\n--------------------------\r\n";

        $string = "ДАННЫЕ О КЛИЕНТЕ\r\n";
        $string .= "Имя: " . $data[0]["clientName"] . "\r\n";
        $string .= "Адрес: " . $data[0]["clientAddress"] . "\r\n";
        $string .= "Возраст: " . $data[0]["clientAge"] . "\r\n";
        $string .= "Дата рождения: " . $data[0]["clientBirthday"] . $delemiter;

        foreach ($data as $voucher) {

            $string .= "ИНФОАМАЦИЯ ПО ПУТЕВКЕ #" . $voucher["dogovorCode"] . "\r\n";
            $string .= "Название тура: " . $voucher["tourName"] . "\r\n";
            $string .= "Дата тура: " . $voucher["turDate"] . "\r\n";
            $string .= "Количество ночей: " . $voucher["nights"] . "\r\n";
            $string .= "Общая стоимость: " . $voucher["fullPrice"] . $voucher["currency"] . "\r\n";
            $string .= "Скидка: " . $voucher["discount"] . $voucher["currency"] . "\r\n";
            $string .= "Услуги:\r\n";
            foreach ($voucher["services"] as $service) {
                $string .= $service["name"] . "[" . $voucher["dateBegin"] . "-" . $voucher["dateEnd"] . "]\r\n";
            }
            $string .= "Туристы:\r\n";
            foreach ($voucher["turists"] as $tourist) {
                $string .= $tourist["firstName"] . " " . $tourist["lastName"] . "[возраст:" . $tourist["age"] . ", дата рождения: " . $tourist["birthday"] . ", гражданство: " . $tourist["citizen"] . "]\r\n";
            }
        }

        $string .= $delemiter;

        return $string;
    }

    /**
     * @param array $agreement
     * @return string
     */
    private static function createMasterTourDealInfoForSave(array $agreement)
    {
        $delimiter = "\r\n--------------------------\r\n";

        $string = "ДАННЫЕ О КЛИЕНТЕ\r\n";
        $string .= "Имя: {$agreement["clientName"]}\r\n";
        $string .= "Адрес: {$agreement["clientAddress"]}\r\n";
        $string .= "Возраст: {$agreement["clientAge"]}\r\n";
        $string .= "Дата рождения: {$agreement["clientBirthday"]}{$delimiter}";

        $string .= "ИНФОАМАЦИЯ ПО ПУТЕВКЕ #{$agreement["dogovorCode"]}\r\n";
        $string .= "Название тура: {$agreement["tourName"]}\r\n";
        $string .= "Дата тура: {$agreement["turDate"]}\r\n";
        $string .= "Количество ночей: {$agreement["nights"]}\r\n";
        $string .= "Общая стоимость: {$agreement["fullPrice"]}{$agreement["currency"]}\r\n";
        $string .= "Скидка: {$agreement["discount"]}{$agreement["currency"]}\r\n";
        $string .= "Услуги:\r\n";

        foreach ($agreement["services"] as $service) {
            $string .= "{$service["name"]}[{$service["dateBegin"]}-{$service["dateEnd"]}]\r\n";
        }

        $string .= "Туристы:\r\n";
        foreach ($agreement["turists"] as $tourist) {
            $string .= "{$tourist["firstName"]} {$tourist["lastName"]}[возраст: {$tourist["age"]}, дата рождения: {$tourist["birthday"]}, гражданство: {$tourist["citizen"]}]\r\n";
        }

        $string .= $delimiter;

        return $string;
    }


    /**
     * @param $dateBegin
     * @param $dateEnd
     */
    public static function generateDeals($dateBegin, $dateEnd)
    {
        $agreements = \travelsoft\bx24customizer\mastertour\Gateway::getAgreementsByDates($dateBegin, $dateEnd);

        foreach ($agreements as $agreement) {

            $idCodeField = Fields::getIdCodeField();

            $dealFields = [
                'TITLE' => "{$agreement['tourName']} {$agreement['turDate']}",
                'CURRENCY_ID' => self::normalizeCurrency($agreement['currency']),
                'OPPORTUNITY' => $agreement['fullPrice'],
                $idCodeField => $agreement['dogovorCode'],
            ];
            if (!empty($agreement['turists'][0]['phone'])) {
                $lead = self::getLeadByPhone($agreement['turists'][0]['phone']);

                if (!empty($lead)) {
                    $dealFields['LEAD_ID'] = $lead['ID'];
                }
            }

            $infoDealCodeField = Fields::getInfoCodeField();
            $dealFields[$infoDealCodeField] = self::createMasterTourDealInfoForSave($agreement);

            $result = self::updateOrCreateDeal($dealFields, $agreement['dogovorCode']);
        }
    }
}
