<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\controllers\SettingsController;
use barrelstrength\sproutbase\config\models\settings\ControlPanelSettings;
use Craft;

class ControlPanelConfig extends Config
{
    public static function getControllerMap(): array
    {
        return [
            'settings' => SettingsController::class,
        ];
    }

    public static function getKey(): string
    {
        return 'control-panel';
    }

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Control Panel');
    }

    public static function hasControlPanelSettings(): bool
    {
        return false;
    }

    public function createSettingsModel()
    {
        return new ControlPanelSettings();
    }

//    public function getUserPermissions(): array
//    {
//        return [
//            'sprout:sitemaps:editSitemaps' => [
//                'label' => Craft::t('sprout', 'Edit Sitemaps')
//            ],
//        ];
//    }

    public function getCpUrlRules(): array
    {
        return [
            'sprout/settings/<configKey:[^\/]+>/<subNavKey:[^\/]+>' =>
                'sprout/settings/edit-settings',
            'sprout/settings/<configKey:[^\/]+>' =>
                'sprout/settings/edit-settings',
            'sprout/settings' =>
                'sprout/settings/hello',
            'sprout' =>
                'sprout/settings/hello',

            // Welcome and Upgrade
            'sprout/welcome/<pluginId:[^\/]+>' =>
                'sprout/settings/welcome-template',
            'sprout/upgrade/<pluginId:[^\/]+>' =>
                'sprout/settings/upgrade-template',
        ];
    }

    public function isUpgradable(): bool
    {
        return false;
    }
}

