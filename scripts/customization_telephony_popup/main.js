/* 
 * Customization of telephony popup
 * Load data from mastertour
 * 
 * @author dimabresky
 */


(function (w) {

    var bx = w.BX;
    var popups = {};
    var ajax_in_proccess = false;
    
    if (typeof w.travelsoft !== "object") {
        w.travelsoft = {
            bx24customizer: {}
        };
    }
    
    w.travelsoft.bx24customizer = {
        showPopupWithDetailVoucherInfo: __showPopupWithDetailVoucherInfo
    };
    
    /**
     * @param {String} voucher_id
     * @param {Object} data
     * @returns {undefined}
     */
    function __showPopupWithDetailVoucherInfo (voucher_id, data) {
        if (typeof popups[voucher_id] === "undefined") {
            popups[voucher_id] = new bx.PopupWindow("travelsoft-bx24customizer-info-popup-voucher-" + voucher_id, null, {
                content: (function () {

                    var content = `
                        <div class="travelsoft-bx24customizer-popup-info detail-voucher-info-popup">
                            <div class="block-info-data">
                                <div class="block-title">Услуги</div>
                                ${data.services.map(function (item) {
                                   return `
                                        <ul class="main-info-data">
                                            <li>Название: <b>${item.name}</b></li>
                                            <li>Дата c: <b>${item.dateBegin}</b></li>
                                            <li>Дата по: <b>${item.dateEnd}</b></li>
                                        </ul>

                                    `;
                                }).join("")}
                            </div>
                            <div class="block-info-data">
                                <div class="block-title">Туристы</div>
                                ${data.turists.map(function (item) {

                                   return `
                                        <ul class="main-info-data">
                                            <li>Пол: <b>${item.sex}</b></li>
                                            <li>Имя: <b>${item.firstName + item.lastName}</b></li>
                                            <li>Адрес: <b>${item.address}</b></li>
                                            <li>Телефон: <b>${item.phone}</b></li>
                                            <li>Email: <b>${item.email}</b></li>
                                            <li>Гражданство: <b>${item.citizen}</b></li>
                                            <li>Возраст: <b>${item.age}</b></li>
                                            <li>День рождения: <b>${item.birthday}</b></li>
                                        </ul>
                                    `;
                                }).join("")}
                            </div>
                        </div>

                    `;
                    return content;

                })(),
                closeIcon: {right: "0px", top: "14px"},
                offsetLeft: 0,
                offsetTop: -99999,
                zIndex: 9999999,
                draggable: true,
                width: 400,
                titleBar: {content: bx.create("div", {html: `<span tltle="можно перетащить в другое место" class="druggable-icon"></span><span class="travelsoft-bx24customizer-title-bar-title">Подробности по путевке #${data.dogovorCode}</span>`, props: {'className': 'travelsoft-bx24customizer-title-bar'}})}
            });
        } else {
            popups[voucher_id].close();
        }
        
        popups[voucher_id].show();
    }
    
    /**
     * @param {String} phone
     * @returns {undefined}
     */
    function __showPopupWithLeadInfo(phone) {
        
        if (typeof popups[phone] === "undefined") {
            if (!ajax_in_proccess) {
                ajax_in_proccess = true;
                bx.ajax({
                    url: "/local/modules/travelsoft.bx24customizer/ajax/mastertour-lead-info.php",
                    data: {sessid: bx.bitrix_sessid(), phone: phone},
                    method: "POST",
                    dataType: "json",
                    timeout: 60,
                    async: true,
                    proccessData: true,
                    scriptRunFirst: false,
                    emulateOnLoad: true,
                    start: true,
                    cache: false,
                    onsuccess: function (resp) {
                        if (!resp.errors && bx.type.isArray(resp.data) && typeof resp.data[0].dogovorCode === "string") {

                            // создаем popup
                            popups[phone] = new bx.PopupWindow("travelsoft-bx24customizer-info-popup-", null, {
                                content: (function (data) {

                                    return `
                                        <div class="travelsoft-bx24customizer-popup-info">
                                            <ul class="main-info-data">
                                                <li>ФИО: <b>${data[0].clientName}</b></li>
                                                <li>Дата рождения: <b>${data[0].clientBirthday}</b></li>
                                                <li>Возраст: <b>${data[0].clientAge}</b></li>
                                                <li>Адрес: <b>${data[0].clientAddress}</b></li>
                                            </ul>
                                            <div class="detail-vouchers-info-data">
                                            ${data.map(function (item) {
                                                return `<div id="block-info-data-${item.dogovorCode}" class="block-info-data">
                                                            <div class="block-title">Путевка #${item.dogovorCode}</div>
                                                            <ul class="main-info-data">
                                                                <li>Страна: <b>${item.countryName}</b></li>
                                                                <li>Дата тура: <b>${item.turDate}</b></li>
                                                                <li>Название: <b>${item.tourName}</b></li>
                                                                <li>Количество ночей: <b>${item.nights}</b></li>
                                                                <li>Количество туристов: <b>${item.turists.length}</b></li>
                                                                <li>Общая стоимость: <b>${item.fullPrice + item.currency}</b></li>
                                                                <li>Скидка: <b>${item.discount + item.currency}</b></li>
                                                                <li onclick='travelsoft.bx24customizer.showPopupWithDetailVoucherInfo(${item.dogovorCode}, ${w.JSON.stringify(item)})' data-voucher-id="${item.dogovorCode}" class="detail-by-voucher">Подробности по путевке</li>
                                                            </ul>

                                                        </div>`;
                                            }).join("")}
                                            </div>
                                        </div>

                                    `;
                                    
                                })(resp.data),
                                closeIcon: {right: "0px", top: "14px"},
                                offsetLeft: 99999,
                                offsetTop: -99999,
                                zIndex: 9999999,
                                draggable: true,
                                width: 400,
                                titleBar: {content: bx.create("div", {html: '<span tltle="можно перетащить в другое место" class="druggable-icon"></span><span class="travelsoft-bx24customizer-title-bar-title">Информация из ПК-МастреТур</span>', props: {'className': 'travelsoft-bx24customizer-title-bar'}})}
                            });
                            popups[phone].show();
                            
                            
                        } else {
                            console.log(resp.message);
                        }
                        ajax_in_proccess = false;
                    },
                    onfailure: function () {
                        ajax_in_proccess = false;
                    }
                });
            }
        } else {
            popups[phone].show();
        }

    }
    
    /**
     * @param {String} str
     * @returns {String}
     */
    function __getNormalizedString (str) {
        return str.split(" ").join("").split("(").join("").split(")").join("").split("-").join("").replace("+", "");
    }

    /**
     * @param {String} str
     * @param {Regex} regex
     * @returns {String|null}
     */
    function __getLeadNormalazedPhone(str, regex) {
        var regex_phone = /[0-9]+/g;
        var matches = __getNormalizedString(str).match(regex);
        
        if (matches && matches[0] !== "") {
            matches = matches[0].match(regex_phone);
            
            if (matches && matches[0] !== "") {
                return matches[0];
            }
        }
        return null;
    }

    bx.ready(function () {

        bx.addCustomEvent('onAjaxSuccess', function (data, parameters) {

            var regex = [/\[0-9]+Входящийзвонок/g, /Звонокот[0-9]+/g];
            var phone = null;
            var timer_id = null;
            var iterations_count = 0;
            // проверка на входящий звонок
            if (parameters.url.indexOf("call.ajax.php?CALL_CRM_CARD") !== -1 && typeof data === "string") {
                
                phone = __getLeadNormalazedPhone(data, regex[0]);
                if (phone && phone !== "") {
                    __showPopupWithLeadInfo(phone);
                } else {
                    timer_id = setInterval(function () {
                        
                        var domElementContainsPhone = bx.findChild(bx("popup-window-content-im-phone-call-view"), {className: "im-phone-call-title-text"}, true);
                        
                        if (domElementContainsPhone && domElementContainsPhone.innerText !== "") {
                            phone = __getLeadNormalazedPhone(domElementContainsPhone.innerText, regex[1]);
                            if (phone && phone !== "") {
                                __showPopupWithLeadInfo(phone);
                            }
                            clearInterval(timer_id);
                        }
                        
                        iterations_count++;
                        if (iterations_count > 5) {
                            clearInterval(timer_id);
                        }
                    }, 100);
                }
            }
        });
    });

})(window);