<?php
/**
 * Created by PhpStorm.
 * User: sun
 * Date: 27.12.18
 * Time: 14:15
 */

namespace travelsoft\bx24customizer\stores;

use travelsoft\bx24customizer\adapters\Iblock;
use travelsoft\bx24customizer\traits\MasterTourRelatedStores;

class Country extends Iblock
{
    use MasterTourRelatedStores;

    /**
     * @var int
     */
    protected static $storeName = 'country';
}
