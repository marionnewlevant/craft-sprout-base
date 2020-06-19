<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\SeoSettings;
use barrelstrength\sproutbase\migrations\metadata\Install;
use barrelstrength\sproutbase\SproutBase;
use Craft;

/**
 *
 * @property array $cpNavItem
 * @property array $cpUrlRules
 * @property string $description
 * @property array[]|array $userPermissions
 * @property array|string[] $controllerMapKeys
 * @property string $key
 */
class SeoConfig extends Config
{
    public function getKey(): string
    {
        return 'seo';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'SEO');
    }

    public function getDescription(): string
    {
        return Craft::t('sprout', 'Manage SEO metadata');
    }

    public function createSettingsModel()
    {
        return new SeoSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'SEO'),
            'url' => 'sprout/seo/globals',
            'subnav' => [
                'globals' => [
                    'label' => Craft::t('sprout', 'Globals'),
                    'url' => 'sprout/seo/globals'
                ]
            ]
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:seo:editGlobals' => [
                'label' => Craft::t('sprout', 'Edit Globals')
            ]
        ];
    }

    /**
     * @return array
     */
    public function getCpUrlRules(): array
    {
        return [
            'sprout/seo/globals/<selectedTabHandle:[^\/]+>/<siteHandle:[^\/]+>' =>
                'sprout/global-metadata/edit-global-metadata',
            'sprout/seo/globals/<selectedTabHandle:[^\/]+>' =>
                'sprout/global-metadata/edit-global-metadata',
            'sprout/seo/globals' => [
                'route' => 'sprout/global-metadata/edit-global-metadata',
                'params' => [
                    'selectedTabHandle' => 'website-identity'
                ]
            ],
            'sprout/seo' => [
                'route' => 'sprout/global-metadata/edit-global-metadata',
                'params' => [
                    'selectedTabHandle' => 'website-identity'
                ]
            ],
        ];
    }

    public function setEdition()
    {
        $sproutSeoIsPro = SproutBase::$app->config->isPluginEdition('sprout-seo', Config::EDITION_PRO);

        if ($sproutSeoIsPro) {
            $this->_edition = Config::EDITION_PRO;
        }
    }

    public function getControllerMapKeys(): array
    {
        return [
            'global-metadata'
        ];
    }
}
