<?php

namespace travelsoft\bx24customizer\adapters;

use Bitrix\Highloadblock\HighloadBlockTable as HL;
use travelsoft\bx24customizer\Cache;
use travelsoft\bx24customizer\Fields;

\Bitrix\Main\Loader::includeModule("highloadblock");

/**
 * Класс адаптер для bitrix highloadblock
 *
 * @author dimabresky
 * @copyright (c) 2017, travelsoft
 */
abstract class Highloadblock extends Store
{

    /**
     * @var string
     */
    protected static $storeName = null;

    /**
     * Возвращает полученные данные из хранилища в виде массива
     * @param array $query
     * @param callable $callback
     * @return array
     */
    public static function get(array $query = array(), bool $likeArray = true, callable $callback = null)
    {

        $table = self::getTable();
        $dbList = $table::getList((array)$query);

        if (!$likeArray) {

            return $dbList;
        }

        $result = array();
        if ($callback) {
            while ($res = $dbList->fetch()) {
                $callback($res);
                $result[$res["ID"]] = $res;
            }
        } else {
            while ($res = $dbList->fetch()) {
                $result[$res["ID"]] = $res;
            }
        }

        return (array)$result;
    }

    /**
     * Обновление записи по id
     * @param int $id
     * @param array $arUpdate
     * @return boolean
     */
    public static function update(int $id, array $arUpdate): bool
    {

        $table = self::getTable();
        $result = boolval($table::update($id, $arUpdate));
        if ($result) {
            self::clearCache();
        }
        return $result;
    }

    /**
     * Добавляет запись в хранилище
     * @param array $arSave
     * @return int
     */
    public static function add(array $arSave): int
    {

        $table = self::getTable();

        $result = (int)$table::add($arSave)->getId();
        if ($result > 0) {
            self::clearCache();
        }
        return $result;
    }

    /**
     *
     * @param int $id
     */
    public static function delete(int $id): bool
    {

        $table = self::getTable();
        $result = boolval($table::delete($id));
        if ($result) {
            self::clearCache();
        }
        return $result;
    }

    /**
     * Возвращает поля записи таблицы по id
     * @param int $id
     * @param array $select
     * @return array
     */
    public static function getById(int $id, array $select = array()): array
    {

        $class = get_called_class();
        $query = array("filter" => array("ID" => $id));
        if (!empty($select)) {
            $query["select"] = $select;
        }
        $result = current($class::get($query));
        if (is_array($result) && !empty($result)) {

            return $result;
        } else {

            return array();
        }
    }

    /**
     * @return string
     */
    protected static function getTable(): string
    {

        return HL::compileEntity(HL::getById(self::getTableId())->fetch())->getDataClass();
    }

    /**
     * @return int
     */
    protected static function getTableId(): int
    {
        $class = get_called_class();
        $method = $class::$storeName . "StoreId";

        return Fields::$method();
    }

    protected static function clearCache()
    {
        Cache::clearByTag("highloadblock_" . self::getTableId());
    }

}
