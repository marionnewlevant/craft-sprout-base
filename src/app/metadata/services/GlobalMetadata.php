<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\metadata\services;

use barrelstrength\sproutbase\app\metadata\migrations\InsertDefaultGlobalsBySite;
use barrelstrength\sproutbase\app\metadata\models\Globals;
use barrelstrength\sproutbase\app\metadata\records\GlobalMetadata as GlobalMetadataRecord;
use Craft;
use craft\base\Component;
use craft\db\Query;
use craft\errors\SiteNotFoundException;
use craft\helpers\Json;
use craft\models\Site;
use Throwable;
use yii\db\Exception;

/**
 * Class SproutSeo_GlobalMetadataService
 *
 * @package Craft
 *
 * @property array $transforms
 */
class GlobalMetadata extends Component
{
    /**
     * Get Global Metadata values
     *
     * @param Site|null $site
     *
     * @return Globals
     * @throws SiteNotFoundException
     * @throws \yii\base\Exception
     */
    public function getGlobalMetadata($site = null): Globals
    {
        $siteId = $site->id ?? null;

        $query = (new Query())
            ->select('*')
            ->from([GlobalMetadataRecord::tableName()]);

        if ($siteId) {
            $query->where(['siteId' => $siteId]);
        } else {
            $site = Craft::$app->getSites()->getPrimarySite();
            $query->where(['siteId' => $site->id]);
        }

        $results = $query->one();

        $results['identity'] = isset($results['identity']) ? Json::decode($results['identity']) : null;
        $results['contacts'] = isset($results['contacts']) ? Json::decode($results['contacts']) : null;
        $results['ownership'] = isset($results['ownership']) ? Json::decode($results['ownership']) : null;
        $results['social'] = isset($results['social']) ? Json::decode($results['social']) : null;
        $results['robots'] = isset($results['robots']) ? Json::decode($results['robots']) : null;
        $results['settings'] = isset($results['settings']) ? Json::decode($results['settings']) : null;

        return new Globals($results);
    }

    /**
     * Save Global Metadata to database
     *
     * @param string  $globalColumn
     * @param Globals $globals
     *
     * @return bool
     * @throws Throwable
     * @throws Exception
     */
    public function saveGlobalMetadata($globalColumn, $globals): bool
    {
        $values[$globalColumn] = $globals->getGlobalByKey($globalColumn, 'json');
        $values['siteId'] = $globals->siteId;

        // new site?
        $results = (new Query())
            ->select('*')
            ->from([GlobalMetadataRecord::tableName()])
            ->where(['[[siteId]]' => $globals->siteId])
            ->one();

        if (!$results) {
            //save default settings
            $migration = new InsertDefaultGlobalsBySite([
                'siteId' => $globals->siteId,
            ]);

            ob_start();
            $migration->up();
            ob_end_clean();
        }

        Craft::$app->db->createCommand()->update(GlobalMetadataRecord::tableName(),
            $values,
            ['siteId' => $globals->siteId]
        )->execute();

        return true;
    }

    /**
     * @return array
     */
    public function getTransforms(): array
    {
        $options = [
            '' => Craft::t('sprout', 'None')
        ];

        $options[] = ['optgroup' => Craft::t('sprout', 'Default Transforms')];

        $options['sproutSeo-socialSquare'] = Craft::t('sprout', 'Square – 400x400');
        $options['sproutSeo-ogRectangle'] = Craft::t('sprout', 'Rectangle – 1200x630 – Open Graph');
        $options['sproutSeo-twitterRectangle'] = Craft::t('sprout', 'Rectangle – 1024x512 – Twitter Card');

        $transforms = Craft::$app->assetTransforms->getAllTransforms();

        if (count($transforms)) {
            $options[] = ['optgroup' => Craft::t('sprout', 'Custom Transforms')];

            foreach ($transforms as $transform) {
                $options[$transform->handle] = $transform->name;
            }
        }

        return $options;
    }
}