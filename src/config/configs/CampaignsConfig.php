<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\config\configs;

use barrelstrength\sproutbase\config\base\Config;
use barrelstrength\sproutbase\config\models\settings\CampaignsSettings;
use barrelstrength\sproutbase\migrations\campaigns\Install;
use Craft;

class CampaignsConfig extends Config
{
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Campaigns');
    }

    public function getConfigGroup()
    {
        return new EmailConfig();
    }

    public function createSettingsModel()
    {
        return new CampaignsSettings();
    }

    public function createInstallMigration()
    {
        return new Install();
    }

    public function getCpNavItem(): array
    {
        return [
            'label' => Craft::t('sprout', 'Campaigns'),
            'url' => 'sprout/campaigns'
        ];
    }

    public function getUserPermissions(): array
    {
        return [
            'sprout:campaigns:editCampaigns' => [
                'label' => Craft::t('sprout', 'Edit Campaign Emails'),
                'nested' => [
                    'sprout:campaigns:sendCampaigns' => [
                        'label' => Craft::t('sprout', 'Send Campaign Emails')
                    ]
                ]
            ]
        ];
    }

    public function getCpUrlRules(): array
    {
        return [
            'sprout/campaigns/<campaignTypeId:\d+>/<emailId:new>' =>
                'sprout/campaign-email/edit-campaign-email',
            'sprout/campaigns/edit/<emailId:\d+>' =>
                'sprout/campaign-email/edit-campaign-email',
            'sprout/campaigns' =>
                'sprout/campaign-email/campaign-email-index-template',

            // DB Settings
            'sprout/settings/<settingsSectionHandle:campaigns>/<settingsSubSectionHandle:campaign-types>/edit/<campaignTypeId:\d+>' => [
                'route' => 'sprout/campaign-type/edit-campaign-type'
            ],

            // @todo - migration break out into 'messages' module that
            //         handles messages and previewing them
            'sprout/email/preview/<emailId:\d+>' => [
                'template' => 'sprout/email/_special/preview'
            ]
        ];
    }
}

