<?php

namespace travelsoft\bx24customizer;

/**
 * Обработчики событий
 *
 * @author dimabresky
 */
class EventsHandlers
{
    /**
     * Load custom scripts
     */
    public static function loadJsOfCustomizationTelephonyPopup()
    {
        $arJsConfig = array(
            "bx24customizer_telephony_popup" => array(
                "js" => "/local/modules/travelsoft.bx24customizer/scripts/customization_telephony_popup/main.js",
                "css" => "/local/modules/travelsoft.bx24customizer/scripts/customization_telephony_popup/css/main.css"
            )
        );

        foreach ($arJsConfig as $ext => $arExt) {
            \CJSCore::RegisterExt($ext, $arExt);
        }

        \CUtil::InitJSCore(array('bx24customizer_telephony_popup'));
    }

}
