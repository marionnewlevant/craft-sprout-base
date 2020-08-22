<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\sentemail\elements;

use barrelstrength\sproutbase\app\email\base\EmailElement;
use barrelstrength\sproutbase\app\sentemail\elements\actions\DeleteEmail;
use barrelstrength\sproutbase\app\sentemail\elements\db\SentEmailQuery;
use barrelstrength\sproutbase\app\sentemail\models\SentEmailInfoTable;
use barrelstrength\sproutbase\app\sentemail\records\SentEmail as SentEmailRecord;
use barrelstrength\sproutbase\SproutBase;
use barrelstrength\sproutbase\web\assetbundles\email\EmailAsset;
use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use DateTime;
use yii\base\Exception;
use yii\base\InvalidConfigException;

class SentEmail extends Element
{
    const SENT = 'sent';
    const FAILED = 'failed';

    /**
     * @var bool
     */
    public $saveAsNew;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $subjectLine;

    /**
     * @var bool
     */
    public $enableFileAttachments;

    // Sender Info

    /**
     * @var string
     */
    public $fromName;

    /**
     * @var string
     */
    public $fromEmail;

    /**
     * @var string
     */
    public $toEmail;

    /**
     * @var string
     */
    public $textBody;

    /**
     * @var string
     */
    public $htmlBody;

    /**
     * @var string
     */
    public $info;

    /**
     * @var string
     */
    public $status;

    /**
     * @var DateTime
     */
    public $dateSent;

    /**
     * @var array
     */
    protected $fields;

    public static function displayName(): string
    {
        return Craft::t('sprout', 'Sent Email');
    }

    public static function pluralDisplayName(): string
    {
        return Craft::t('sprout', 'Sent Emails');
    }

    public static function find(): ElementQueryInterface
    {
        return new SentEmailQuery(static::class);
    }

    /**
     * @inheritDoc
     *
     * @throws InvalidConfigException
     */
    public static function indexHtml(
        ElementQueryInterface $elementQuery, /** @noinspection PhpOptionalBeforeRequiredParametersInspection */
        array $disabledElementIds = null, array $viewState, /** @noinspection PhpOptionalBeforeRequiredParametersInspection */
        string $sourceKey = null, /** @noinspection PhpOptionalBeforeRequiredParametersInspection */
        string $context = null, bool $includeContainer, bool $showCheckboxes
    ): string {
        $html = parent::indexHtml($elementQuery, $disabledElementIds, $viewState, $sourceKey, $context, $includeContainer,
            $showCheckboxes);

        Craft::$app->getView()->registerAssetBundle(EmailAsset::class);

        Craft::$app->getView()->registerJs('new SproutModal();');

        SproutBase::$app->mailers->includeMailerModalResources();

        return $html;
    }

    /**
     * @inheritDoc
     */
    protected static function defineSources(string $context = null): array
    {
        $sources = [
            [
                'key' => '*',
                'label' => Craft::t('sprout', 'All Sent Emails'),
                'defaultSort' => ['dateCreated', 'desc'],
            ],
        ];

        return $sources;
    }

    protected static function defineSearchableAttributes(): array
    {
        return ['toEmail', 'subjectLine'];
    }

    protected static function defineSortOptions(): array
    {
        $attributes = [
            'elements.dateCreated' => Craft::t('sprout', 'Date Sent'),
        ];

        return $attributes;
    }

    protected static function defineTableAttributes(): array
    {
        $attributes = [
            'dateSent' => ['label' => Craft::t('sprout', 'Date Sent')],
            'toEmail' => ['label' => Craft::t('sprout', 'Recipient')],
            'subjectLine' => ['label' => Craft::t('sprout', 'Subject')],
            'info' => ['label' => Craft::t('sprout', 'Details')],
        ];

        if (Craft::$app->getUser()->checkPermission('sprout:email:resendEmails')) {
            $attributes['resend'] = ['label' => Craft::t('sprout', 'Resend')];
        }

        $attributes['preview'] = ['label' => Craft::t('sprout', 'Preview'), 'icon' => 'view'];

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    protected static function defineActions(string $source = null): array
    {
        $actions = [];

        $actions[] = DeleteEmail::class;

        return $actions;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getLocaleNiceDateTime();
    }

    public function getIsEditable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getTableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'preview':

                $previewUrl = UrlHelper::cpUrl('sprout/preview/email/'.$this->id);

                return '<a class="email-preview" '.
                    'data-email-id="'.$this->id.'" '.
                    'data-preview-url="'.$previewUrl.'" '.
                    'href="'.$previewUrl.'" '.
                    '" data-icon="view"></a>';

                break;
            case 'resend':

                return '<a class="prepare btn small formsubmit" 
                                data-action="sprout/sent-email/get-resend-modal" 
                                data-email-id="'.$this->id.'">'.
                    Craft::t('sprout', 'Prepare').
                    '</a>';

                break;

            case 'info':

                return '<a class="prepare btn small formsubmit"
                                data-action="sprout/sent-email/get-info-html" 
                                data-email-id="'.$this->id.'" 
                                data-type="'.get_class($this).'"
                                data-id="'.$this->id.'"
                                data-site-id="'.$this->siteId.'"
                                data-status="'.$this->getStatus().'"
                                data-label="'.$this.'"
                                data-url="'.$this->getUrl().'"              
                                data-level="'.$this->level.'">'.
                    Craft::t('sprout', 'Details').
                    '</a>';
                break;

            default:
        }

        return parent::getTableAttributeHtml($attribute);
    }

    /**
     * @return string
     */
    public function getLocaleNiceDateTime(): string
    {
        return $this->dateCreated->format('M j, Y H:i:s A');
    }

    /**
     * @param bool $isNew
     *
     * @throws \Exception
     */
    public function afterSave(bool $isNew)
    {
        // Get the entry record
        if (!$isNew) {
            $record = SentEmailRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid campaign email ID: '.$this->id);
            }
        } else {
            $record = new SentEmailRecord();
            $record->id = $this->id;
        }

        $record->title = $this->title;
        $record->subjectLine = $this->subjectLine;
        $record->fromEmail = $this->fromEmail;
        $record->fromName = $this->fromName;
        $record->toEmail = $this->toEmail;
        $record->textBody = $this->textBody;
        $record->htmlBody = $this->htmlBody;
        $record->info = $this->info;
        $record->status = $this->status;
        $record->dateCreated = $this->dateCreated;
        $record->dateUpdated = $this->dateUpdated;

        $record->save(false);

        // Update the entry's descendants, who may be using this entry's URI in their own URIs
        Craft::$app->getElements()->updateElementSlugAndUri($this);

        parent::afterSave($isNew);
    }

    /**
     * @return SentEmailInfoTable
     */
    public function getInfo(): SentEmailInfoTable
    {
        $infoTable = new SentEmailInfoTable();
        $infoTable->setAttributes(Json::decode($this->info), false);

        return $infoTable;
    }

    public function getPreviewPermission(): string
    {
        return 'sprout:sentEmail:viewSentEmail';
    }

    public function getPreviewType(): string
    {
        return EmailElement::EMAIL_TEMPLATE_TYPE_STATIC;
    }
}
