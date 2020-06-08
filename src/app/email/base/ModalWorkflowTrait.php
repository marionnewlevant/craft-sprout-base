<?php

namespace barrelstrength\sproutbase\app\email\base;

use barrelstrength\sproutbase\app\email\models\ModalResponse;
use Craft;
use Exception;
use Throwable;

trait ModalWorkflowTrait
{
    /**
     * Gives mailers the ability to include their own modal resources for the Email Element Index page
     *
     * @example
     * Mailers should be calling the following functions from within their implementation
     *
     * Craft::$app->getView()->registerAssetBundle(MyMailerAsset::class);
     */
    public function includeModalResources()
    {
        return null;
    }

    /**
     * Gives a mailer the ability to register an action to post to when a [prepare] modal is launched.
     *
     * @return string
     * @example
     *
     * The Copy/Paste mailer uses this to handle the Copy/Paste workflow instead of a Send workflow
     *
     */
    public function getActionForPrepareModal(): string
    {
        return 'sprout/mailers/get-prepare-modal';
    }

    /**
     * @param EmailElement $email
     *
     * @return ModalResponse
     * @throws Throwable
     */
    public function getPrepareModal(EmailElement $email): ModalResponse
    {
        $response = new ModalResponse();

        try {
            $response->success = true;
            $response->content = $this->getPrepareModalHtml($email);

            return $response;
        } catch (Exception $e) {
            $response->success = false;
            $response->message = $e->getMessage();

            return $response;
        }
    }

    /**
     * @param EmailElement $email
     *
     * @return string
     * @throws Throwable
     */
    public function getPrepareModalHtml(EmailElement $email): string
    {
        if (!empty($email->recipients)) {
            $recipients = $email->recipients;
        }

        if (empty($recipients)) {
            $recipients = Craft::$app->getUser()->getIdentity()->email;
        }

        if (empty($email->getEmailTemplateId())) {
            $email->addError('emailTemplateId', Craft::t('sprout', 'No email template setting found.'));
        }

        return Craft::$app->getView()->renderTemplate('sprout/email/_modals/prepare-email-snapshot', [
            'email' => $email,
            'recipients' => $recipients
        ]);
    }
}
