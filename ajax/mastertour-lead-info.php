<?php

define("PUBLIC_AJAX_MODE", true);
define("NO_KEEP_STATISTIC", "Y");
define("NO_AGENT_STATISTIC", "Y");
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

Bitrix\Main\Loader::includeModule("travelsoft.bx24customizer");

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

if (!\check_bitrix_sessid() || !$request->isPost() || !$request->isAjaxRequest()) {
    travelsoft\bx24customizer\Tools::send404();
}

if (!empty($request->get("phone"))) {

    $cache = new \travelsoft\bx24customizer\Cache("travelsoft.bx24cusomizer-mastertour-info-" . $request->get("phone"), "/travelsoft/bx24cusomizer/mastertour-info", 3600);

    $response = [];
    if (empty($response = $cache->get())) {
        $response = $cache->caching(function () use ($request) {

            $response = travelsoft\bx24customizer\mastertour\Gateway::getMastertourLeadInfoByPhone($request->get("phone"));

            if (is_array($response)) {
                return $response;
            }

            return [];
        });

        if (is_array($response)) {

            $arLead = travelsoft\bx24customizer\Tools::getLeadByPhone($request->get("phone"));
            if (@$arLead["ID"] > 0) {
                travelsoft\bx24customizer\Tools::saveLeadInfo($arLead["ID"], [Bitrix\Main\Config\Option::get("travelsoft.bx24customizer", "MASTERTOUR_INFO_LEAD_CODE_FIELD") => travelsoft\bx24customizer\Tools::createMastertourLeadInfoForSave($response)]);
            }
        }
    }

    if (is_array($response)) {
        travelsoft\bx24customizer\Tools::sendJsonResponse(["errors" => false, "message" => "", "data" => $response]);
    }
}

travelsoft\bx24customizer\Tools::sendJsonResponse(["errors" => true, "message" => "Error data getter.", "data" => ""]);
