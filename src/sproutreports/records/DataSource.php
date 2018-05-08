<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\sproutreports\records;

use craft\db\ActiveRecord;

/**
 * Class DataSource
 *
 * @property int    $id       ID
 * @property int    $pluginId Plugin ID
 * @property string $type     Data Source Class
 * @property bool   $allowNew Allow New
 *
 * @package barrelstrength\sproutbase\sproutreports\records
 */
class DataSource extends ActiveRecord
{
    /**
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%sproutreports_datasources}}';
    }
}