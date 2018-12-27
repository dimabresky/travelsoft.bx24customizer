<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 27.12.18
 * Time: 15:29
 */

namespace travelsoft\bx24customizer\traits;

trait MasterTourRelatedStores
{
    public static function getIblockIdByMasterTourId($masterId)
    {
        $items = self::get(['filter' => ['PROPERTY_MASTER_TOUR_ID' => $masterId]]);

        if(empty($items)){
            return '';
        }

        $item = current($items);

        if($item['PROPERTIES']['MASTER_TOUR_ID']['VALUE'] != $masterId){
            return '';
        }

        return $item['ID'];
    }
}
