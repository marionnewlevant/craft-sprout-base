<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\models\settings;

use barrelstrength\sproutbase\config\base\Settings;
use barrelstrength\sproutbase\config\controllers\SettingsController;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use ReflectionException;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use yii\base\Exception;

/**
 *
 * @property array $settingsNavItem
 */
class ControlPanelSettings extends Settings
{
    public $modules;

//    public $campaignsPluginName = '';
//
//    public $emailPluginName = '';
//
//    public $formsPluginName = '';
//
//    public $listsPluginName = '';
//
//    public $reportsPluginName = '';
//
//    public $redirectsPluginName = '';
//
//    public $seoPluginName = '';
//
//    public $sentEmailPluginName = '';
//
//    public $sitemapsPluginName = '';

    /**
     * @return array|array[]
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     * @throws ReflectionException
     */
    public function getSettingsNavItem(): array
    {
        $configs = SproutBase::$app->config->getConfigs(false);

        $currentSite = $this->getCurrentSite();

        $cpSettingsRows = [];

        if ($this->modules) {
            // Update settings to be indexed by module key
            $savedModuleKeys = array_column($this->modules, 'moduleKey');
            $projectConfigModules = array_combine($savedModuleKeys, $this->modules);
        } else {
            $projectConfigModules = null;
        }

        $i = 0;
        foreach ($configs as $config) {
            if (!$config->hasControlPanelSettings()) {
                continue;
            }

            $projectConfigSettings = $projectConfigModules[$config->getKey()] ?? null;
            $enabledValue = (isset($projectConfigSettings['enabled']) && !empty($projectConfigSettings['enabled'])) ? $projectConfigSettings['enabled'] : false;
            $alternateNameValue = (isset($projectConfigSettings['alternateName']) && !empty($projectConfigSettings['alternateName'])) ? $projectConfigSettings['alternateName'] : '';

            $headingInputHtml = Craft::$app->getView()->renderTemplate('_includes/forms/text', [
                'name' => 'modules['.$i.'][moduleKey]',
                'value' => $config->getKey(),
                'type' => 'hidden'
            ]);

            $enabledInputHtml = Craft::$app->getView()->renderTemplate('_includes/forms/lightswitch', [
                'name' => 'modules['.$i.'][enabled]',
                'on' => $enabledValue,
                'value' => $currentSite->id,
                'small' => true
            ]);


            $infoHtml = $config->getDescription() !== ''
                ? '&nbsp;<span class="info">'.$config->getDescription().'</span>'
                : '';

            $cpSettingsRows[] = [
                'moduleKey' => $config->getKey(),
                'heading' => $config::displayName().$headingInputHtml.$infoHtml,
                'enabled' => $enabledInputHtml,
                'alternateName' => $alternateNameValue
            ];

            $i++;
        }

        return [
            'control-panel' => [
                'label' => Craft::t('sprout', 'Control Panel'),
                'template' => 'sprout/config/_settings/control-panel',
//                'multisite' => true,
                'variables' => [
                    'cpSettingsRows' => $cpSettingsRows
                ]
            ],
            'welcome' => [
                'label' => Craft::t('sprout', 'Welcome'),
                'template' => 'sprout/config/_settings/welcome',
                'settingsTarget' => SettingsController::SETTINGS_TARGET_DB
            ]
        ];
    }
}
