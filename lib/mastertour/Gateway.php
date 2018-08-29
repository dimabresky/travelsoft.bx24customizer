<?php

namespace travelsoft\bx24customizer\mastertour;

use travelsoft\bx24customizer\Tools;

/**
 * Description of Gateway
 *
 * @author dimabresky
 */
class Gateway {

    /**
     * @param array $parameters
     * @return string  ** Json String **
     */
    public static function sendRequest(array $parameters) {

        $result = \file_get_contents(
                \Bitrix\Main\Config\Option::get("travelsoft.bx24customizer", "MASTERTOUR_API_URL"), false, stream_context_create(
                        array(
                            'ssl' => array("verify_peer" => false),
                            'http' => array(
                                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                                'method' => 'POST',
                                'content' => \json_encode($parameters),
                            ),
                        )
                )
        );

        return $result;
    }

    /**
     * @param string $phone
     * @return mixed
     */
    public static function getMastertourLeadInfoByPhone(string $phone) {

        return \json_decode(self::sendRequest(
                        ["phone" => $phone,
                            "signature" => Tools::createMastertourSignByLeadPhone($phone)]), true);
    }

}
