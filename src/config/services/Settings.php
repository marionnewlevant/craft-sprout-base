<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com/
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   http://sprout.barrelstrengthdesign.com/license
 */

namespace barrelstrength\sproutbase\config\services;

use barrelstrength\sproutbase\config\base\ConfigInterface;
use barrelstrength\sproutbase\config\base\Settings as BaseSettings;
use barrelstrength\sproutbase\config\base\SettingsInterface;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\helpers\ProjectConfig as ProjectConfigHelper;
use ReflectionException;
use yii\base\Component;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\NotSupportedException;
use yii\web\ServerErrorHttpException;

class Settings extends Component
{
    /**
     * Gets settings as defined in project config
     *
     * @param bool $includeFileConfigSettings
     *
     * @return SettingsInterface|SettingsInterface[]
     * @throws ReflectionException
     */
    public function getSettings($includeFileConfigSettings = true)
    {
        $configTypes = SproutBase::$app->config->getConfigs();

        $settings = [];

        foreach ($configTypes as $configType) {
            if ($currentSettings = $this->mergeSettings($configType, $includeFileConfigSettings)) {
                $settings[$configType->getKey()] = $currentSettings;
            }
        }

        ksort($settings, SORT_NATURAL);

        return $settings;
    }

    /**
     * Gets current settings as defined in project config and config overrides
     *
     * @param null $handle
     * @param bool $includeFileConfigSettings
     *
     * @return SettingsInterface|SettingsInterface[]
     * @throws ReflectionException
     */
    public function getSettingsByKey($handle, $includeFileConfigSettings = true)
    {
        $settings = $this->getSettings($includeFileConfigSettings);

        return $settings[$handle] ?? [];
    }

    /**
     * @param BaseSettings $settings
     *
     * @return bool
     * @throws ErrorException
     * @throws Exception
     * @throws NotSupportedException
     * @throws ReflectionException
     * @throws ServerErrorHttpException
     */
    public function saveSettings(BaseSettings $settings): bool
    {
        // Have namespace?
//        $settings = $settings['settings'] ?? $settings;
        // Set sprout scenario validation on the settings model
//        $scenario = $settings['validationScenario'] ?? null;

        // Add settings to new SproutSettings model to validate
        // We used this when validating a model in Sprout Forms where only
        // one field was required and others may not exist in the post request.
        if (!$settings->validate()) {
            return false;
        }

        $projectConfigSettingsKey = Config::CONFIG_SPROUT_KEY.'.'.$settings->getKey();
        $newSettings = ProjectConfigHelper::packAssociativeArrays($settings->toArray());

        Craft::$app->getProjectConfig()->set($projectConfigSettingsKey, $newSettings, "Update Sprout Settings for “{$settings->getKey()}”");

        return true;
    }

    /**
     * Returns all settings for a given config
     *
     * Settings priorities are as follows:
     * 1. Settings found in `config/sprout.php` file (optional)
     * 2. Settings found under `sprout.[configHandle]` in project config
     * 3. Default settings from Settings model
     *
     * @param ConfigInterface $configType
     * @param bool            $includeFileConfigSettings
     *
     * @return SettingsInterface|null
     * @throws ReflectionException
     */
    protected function mergeSettings(ConfigInterface $configType, bool $includeFileConfigSettings)
    {
        $projectConfigService = Craft::$app->getProjectConfig();
        $allProjectConfigSettings = $projectConfigService->get(Config::CONFIG_SPROUT_KEY) ?? [];

        $settingsModel = $configType->createSettingsModel();

        if ($settingsModel !== null) {
            $defaultSettings = $settingsModel->getAttributes() ?? [];
            $projectConfigSettings = $allProjectConfigSettings[$settingsModel->getKey()] ?? [];

            $mergedSettings = array_merge($defaultSettings, $projectConfigSettings);

            if ($includeFileConfigSettings) {
                $allFileConfigSettings = Craft::$app->getConfig()->getConfigFromFile('sprout');
                $fileConfigSettings = $allFileConfigSettings[$settingsModel->getKey()] ?? [];

                $mergedSettings = array_merge($mergedSettings, $fileConfigSettings);
            }

            $settingsModel->setAttributes($mergedSettings, false);
        }

        return $settingsModel;
    }
}