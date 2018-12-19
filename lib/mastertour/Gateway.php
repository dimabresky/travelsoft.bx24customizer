<?php

namespace travelsoft\bx24customizer\mastertour;

use travelsoft\bx24customizer\Fields;
use travelsoft\bx24customizer\Tools;

/**
 * Description of Gateway
 *
 * @author dimabresky
 */
class Gateway
{

    /**
     * @param string $method
     * @param array $parameters
     * @return string ** Json String **
     */
    public static function sendRequest(string $method, array $parameters)
    {
        $url = Fields::getMasterApiUrl();
        $result = \file_get_contents($url . $method, false, stream_context_create(
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
    public static function getMastertourLeadInfoByPhone(string $phone)
    {
        return \json_decode(self::sendRequest('GetAgreements', [
                "phone" => $phone,
                "signature" => Tools::createMastertourSignByLeadPhone($phone)]
        ), true);
    }

    /**
     * @param $dateBegin
     * @param $dateEnd
     * @return mixed
     */
    public static function getAgreementsByDates($dateBegin, $dateEnd)
    {
        $parameters = [
            'dateBegin' => $dateBegin,
            'dateEnd' => $dateEnd,
            'signature' => Tools::createMastertourSignatureByDates($dateBegin, $dateEnd)
        ];

        return \json_decode(self::sendRequest('GetAgreementsByDate', $parameters), true);
    }
}
