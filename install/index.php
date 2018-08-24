<?php

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\ModuleManager,
    Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

class travelsoft_bx24customizer extends CModule {

    public $MODULE_ID = "travelsoft.bx24customizer";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_GROUP_RIGHTS = "N";
    public $namespaceFolder = "travelsoft";
    public $componentsList = [];
    public $adminFilesList = [];

    function __construct() {
        $arModuleVersion = array();
        $path_ = str_replace("\\", "/", __FILE__);
        $path = substr($path_, 0, strlen($path_) - strlen("/index.php"));
        include($path . "/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }
        $this->MODULE_NAME = Loc::getMessage("TRAVELSOFT_BX24CUSTOMIZER_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TRAVELSOFT_BX24CUSTOMIZER_MODULE_DESC");
        $this->PARTNER_NAME = Loc::getMessage("TRAVELSOFT_BX24CUSTOMIZER_COMPANY");
        $this->PARTNER_URI = "http://travelsoft.by/";

        set_time_limit(0);
    }

    public function DoInstall() {

        $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

        try {

            if ($request->get("step") !== "2") {
                
                $GLOBALS['MODULE_ID'] = $this->MODULE_ID;
                
                $GLOBALS['APPLICATION']->IncludeAdminFile(Loc::getMessage("TRAVELSOFT_BX24CUSTOMIZER_STEP_1"), $_SERVER["DOCUMENT_ROOT"] . "/local/modules/" . $this->MODULE_ID . "/install/step.php");
            } else {

                if (
                        !\check_bitrix_sessid() ||
                        !is_array($request->get("install_actions")) ||
                        empty($actions = $this->getPreparedInstallActions($request->get("install_actions")))
                ) {
                    print_r($request->get("install_actions"));die;
                    $this->redirect();
                }

                # регистрируем модуль
                ModuleManager::registerModule($this->MODULE_ID);

                # добавление зависимостей модуля
                $this->addModuleDependencies($actions);

                # добавление параметров выбора для модуля
                $this->addOptions($actions);
            }
        } catch (Exception $ex) {

            $GLOBALS["APPLICATION"]->ThrowException($ex->getMessage());

            $this->DoUninstall();

            return false;
        }

        return true;
    }

    public function DoUninstall() {

        # удаляем зависимости модуля
        $this->deleteModuleDependencies();

        # удаление параметров модуля
        $this->deleteOptions();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    public function getPreparedInstallActions(array $actions_from_request) {

        $allowActions = [
            "customization_of_telephony_popup_and_load_data_from_mastertour"
        ];

        return (array) \array_unique(\array_values(\array_filter($actions_from_request, function ($action) use ($allowActions) {
                                    return in_array($action, $allowActions);
                                })));
    }

    public function redirect() {
        global $APPLICATION;
        \LocalRedirect($APPLICATION->GetCurPageParams("", ["step"], false));
    }

    public function addModuleDependencies(array $actions) {

        foreach ($actions as $action) {
            switch ($action) {

                case "customization_of_telephony_popup_and_load_data_from_mastertour":

                    RegisterModuleDependences("crm", "onCrmLeadAdd", $this->MODULE_ID, "\\travelsoft\\bx24customizer\\EventsHandlers", "loadDataForLeadFromMasterTourOnAdd");
                    RegisterModuleDependences("crm", "onCrmLeadUpdate", $this->MODULE_ID, "\\travelsoft\\bx24customizer\\EventsHandlers", "loadDataForLeadFromMasterTourOnUpdate");
                    RegisterModuleDependences("main", "OnEpilog", $this->MODULE_ID, "\\travelsoft\\bx24customizer\\EventsHandlers", "loadJsOfCustomizationTelephonyPopup");

                    break;

                default:
                    throw new \Exception(Loc::getMessage("TRAVELSOFT_BX24CUSTOMIZER_INSTALL_ACTION_ERROR"));
            }
        }
    }

    public function deleteModuleDependencies() {

        UnRegisterModuleDependences("crm", "onCrmLeadAdd", $this->MODULE_ID, "\\travelsoft\\bx24customizer\\EventsHandlers", "loadDataForLeadFromMasterTourOnAdd");
        UnRegisterModuleDependences("crm", "onCrmLeadUpdate", $this->MODULE_ID, "\\travelsoft\\bx24customizer\\EventsHandlers", "loadDataForLeadFromMasterTourOnUpdate");
        UnRegisterModuleDependences("main", "OnEpilog", $this->MODULE_ID, "\\travelsoft\\bx24customizer\\EventsHandlers", "loadJsOfCustomizationTelephonyPopup");
    }

    public function addOptions(array $actions) {

        foreach ($actions as $action) {
            switch ($action) {
                case "customization_of_telephony_popup_and_load_data_from_mastertour":

                    Option::set($this->MODULE_ID, "MASTERTOUR_API_URL");
                    Option::set($this->MODULE_ID, "MASTERTOUR_INFO_LEAD_CODE_FIELD");

                    break;
            }
        }
    }

    public function deleteOptions() {
        Option::delete($this->MODULE_ID, array("name" => "MASTERTOUR_API_URL"));
        Option::delete($this->MODULE_ID, array("name" => "MASTERTOUR_INFO_LEAD_CODE_FIELD"));
    }

}
