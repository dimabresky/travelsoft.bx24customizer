<?php

namespace travelsoft\bx24customizer;

/**
 * Tools clss
 *
 * @author dimabresky
 */
class Tools {

    /**
     * @param mixed $var
     */
    public static function dump($var) {
        echo "<pre>";
        var_dump($var);
        echo "</pre>";
    }

    /**
     * @param mixed $var
     */
    public static function toLog($var) {

        ob_start();
        self::dump($var);
        \file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/local/travelsoft.bx24customizer.log.txt", ob_get_clean());
    }

    /**
     * @global CMain $APPLICATION
     * @param array $parameters
     */
    public static function sendJsonResponse(array $parameters) {

        global $APPLICATION;

        \header('Content-Type: application/json; charset=' . \SITE_CHARSET);

        $APPLICATION->RestartBuffer();

        echo \Bitrix\Main\Web\Json::encode($parameters);

        die();
    }

    public static function send404() {
        $protocol = \filter_input(\INPUT_SERVER, 'SERVER_PROTOCOL');
        \header($protocol . " 404 Not Found");
        die();
    }

    /**
     * @param string $phone
     * @return string
     */
    public static function normalizePhone(string $phone) {

        return str_replace(
                ["+", "(", ")", " ", "-"], ["", "", "", "", ""], $phone
        );
    }

    /**
     * @param string $phone
     * @return array
     */
    public static function getLeadByPhone(string $phone) {

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
    public static function saveLeadInfo(int $leadId, array $save) {
        
        $lead = new \CCrmLead(false);
        
        if ($leadId > 0) {
            return $lead->Update($leadId, $save);
        } else {
            return $lead->Add($save);
        }
        
    }
    
    /**
     * @param string $phone
     * @return string
     */
    public static function createMastertourSignByLeadPhone(string $phone) {
        
        return md5($phone . \Bitrix\Main\Config\Option::get("travelsoft.bx24customizer", "MASTERTOUR_SECRET_API_KEY"));
    }
    
    /**
     * @param array $data
     * @return string
     */
    public static function createMastertourLeadInfoForSave (array $data) {
        
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
                $string .= $service["name"] . "[". $voucher["dateBegin"] ."-". $voucher["dateEnd"] . "]\r\n";
            }
            $string .= "Туристы:\r\n";
            foreach ($voucher["turists"] as $tourist) {
                $string .= $tourist["firstName"] . " " . $tourist["lastName"] . "[возраст:" .$tourist["age"]. ", дата рождения: " . $tourist["birthday"] . ", гражданство: " . $tourist["citizen"] ."]\r\n";
            }
        }
        
        $string .= $delemiter;
        
        return $string;
        
    }
}
