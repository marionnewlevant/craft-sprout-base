<?php

namespace barrelstrength\sproutbase\app\campaigns\mailers;

use barrelstrength\sproutbase\app\campaigns\base\CampaignEmailSenderInterface;
use barrelstrength\sproutbase\app\campaigns\elements\CampaignEmail;
use barrelstrength\sproutbase\app\campaigns\web\assets\CopyPasteAsset;
use barrelstrength\sproutbase\app\email\base\EmailElement;
use barrelstrength\sproutbase\app\email\base\Mailer;
use barrelstrength\sproutbase\app\email\models\ModalResponse;
use Craft;
use Exception;
use Throwable;
use yii\base\InvalidConfigException;

/**
 *
 * @property string $actionForPrepareModal
 * @property string $name
 * @property string $senderHtml
 */
class CopyPasteMailer extends Mailer implements CampaignEmailSenderInterface
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Copy/Paste';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return Craft::t('sprout', 'Copy and paste your email campaigns to better (or worse) places.');
    }

    /**
     * @inheritdoc
     */
    public function hasSender(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function hasRecipients(): bool
    {
        return false;
    }

    /**
     * @return string
     */
    public function getActionForPrepareModal(): string
    {
        return 'sprout/campaign-email/send-campaign-email';
    }

    /**
     * @param EmailElement $email
     *
     * @return string
     */
    public function getPrepareModalHtml(EmailElement $email): string
    {
        return '';
    }

    /**
     * Gives mailers the ability to include their own modal resources and register their dynamic action handlers
     *
     * @throws InvalidConfigException
     */
    public function includeModalResources()
    {
        Craft::$app->getView()->registerAssetBundle(CopyPasteAsset::class);
    }

    /**
     * @param CampaignEmail $campaignEmail
     *
     * @return ModalResponse|mixed|null
     * @throws Throwable
     */
    public function sendCampaignEmail(CampaignEmail $campaignEmail)
    {
        try {
            $response = new ModalResponse();
            $response->success = true;

            $response->content = Craft::$app->getView()->renderPageTemplate('sprout-base-campaigns/_components/mailers/copypaste/schedulecampaignemail',
                [
                    'email' => $campaignEmail,
                    'html' => $campaignEmail->getEmailTemplates()->getHtmlBody(),
                    'text' => $campaignEmail->getEmailTemplates()->getTextBody()
                ]);

            return $response;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @inheritdoc
     *
     * @return mixed|null
     */
    public function sendTestCampaignEmail(CampaignEmail $campaignEmail)
    {
        return null;
    }

    public function getSenderHtml(): string
    {
        return '';
    }
}