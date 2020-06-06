<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\forms\integrations\sproutreports\datasources;

use barrelstrength\sproutbase\app\reports\base\DataSource;
use barrelstrength\sproutbase\app\reports\elements\Report;
use barrelstrength\sproutbase\app\forms\elements\Form;
use barrelstrength\sproutbase\app\forms\records\EntriesSpamLog as EntriesSpamLogRecord;
use barrelstrength\sproutbase\app\forms\records\Entry as EntryRecord;
use barrelstrength\sproutbase\app\forms\records\Form as FormRecord;
use barrelstrength\sproutbase\SproutBase;
use Craft;
use craft\db\Query;
use craft\helpers\DateTimeHelper;
use DateTime;
use Exception;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Class SpamLogDataSource
 *
 * @package barrelstrength\sproutforms\integrations\sproutreports\datasources
 */
class SpamLogDataSource extends DataSource
{
    /**
     * @return string
     */
    public static function displayName(): string
    {
        return Craft::t('sprout', 'Spam Log (Sprout Forms)');
    }

    /**
     * @return null|string
     */
    public function getDescription(): string
    {
        return Craft::t('sprout', 'Overview of spam submissions');
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function getResults(Report $report, array $settings = []): array
    {
        $startDate = null;
        $endDate = null;
        $formId = null;

        $startEndDate = $report->getStartEndDate();
        $startDate = $startEndDate->getStartDate();
        $endDate = $startEndDate->getEndDate();

        $rows = [];

        $formId = $report->getSetting('formId');

        $query = (new Query())
            ->select([
                'entries_spam_log.id',
                'entries_spam_log.entryId',
                'entries_spam_log.type',
                'entries_spam_log.errors',
                'entries_spam_log.dateCreated',
            ])
            ->from(EntriesSpamLogRecord::tableName().' entries_spam_log')
            ->innerJoin(
                EntryRecord::tableName().' AS entries',
                '[[entries_spam_log.entryId]] = [[entries.id]]'
            )
            ->innerJoin(
                FormRecord::tableName().' forms',
                '[[entries.formId]] = [[forms.id]]'
            );

        if ($formId !== '*') {
            $query->andWhere(['[[entries.formId]]' => $formId]);
        }

        if ($startDate && $endDate) {
            $query->andWhere('[[entries_spam_log.dateCreated]] > :startDate', [
                ':startDate' => $startDate->format('Y-m-d H:i:s')
            ]);
            $query->andWhere('[[entries_spam_log.dateCreated]] < :endDate', [
                ':endDate' => $endDate->format('Y-m-d H:i:s')
            ]);
        }

        $results = $query->all();

        if (!$results) {
            return $rows;
        }

        foreach ($results as $key => $result) {
            $captcha = new $result['type']();

            $rows[$key]['id'] = $result['id'];
            $rows[$key]['entryId'] = $result['entryId'];
            $rows[$key]['captchaName'] = $captcha->name;
            $rows[$key]['errors'] = $result['errors'];
            $rows[$key]['dateCreated'] = $result['dateCreated'];
        }

        return $rows;
    }

    /**
     * @inheritDoc
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws Exception
     */
    public function getSettingsHtml(array $settings = [])
    {
        /** @var Form[] $forms */
        $forms = Form::find()->limit(null)->orderBy('name')->all();

        if (empty($settings)) {
            $settings = (array)$this->report->getSettings();
        }

        $formOptions[] = ['label' => 'All', 'value' => '*'];

        foreach ($forms as $form) {
            $formOptions[] = [
                'label' => $form->name,
                'value' => $form->id
            ];
        }

        // @todo Determine sensible default start and end date based on Order data
        $defaultStartDate = null;
        $defaultEndDate = null;

        if (count($settings)) {
            if (isset($settings['startDate'])) {
                $startDateValue = (array)$settings['startDate'];

                $settings['startDate'] = DateTimeHelper::toDateTime($startDateValue);
            }

            if (isset($settings['endDate'])) {
                $endDateValue = (array)$settings['endDate'];

                $settings['endDate'] = DateTimeHelper::toDateTime($endDateValue);
            }
        }

        $dateRanges = SproutBase::$app->reports->getDateRanges(false);

        return Craft::$app->getView()->renderTemplate('sprout-base-forms/_integrations/sproutreports/datasources/SpamLogDataSource/settings', [
            'formOptions' => $formOptions,
            'defaultStartDate' => new DateTime($defaultStartDate),
            'defaultEndDate' => new DateTime($defaultEndDate),
            'dateRanges' => $dateRanges,
            'options' => $settings
        ]);
    }

    /**
     * @inheritdoc
     *
     * @throws Exception
     */
    public function prepSettings(array $settings)
    {
        // Convert date strings to DateTime
        $settings['startDate'] = DateTimeHelper::toDateTime($settings['startDate']) ?: null;
        $settings['endDate'] = DateTimeHelper::toDateTime($settings['endDate']) ?: null;

        return $settings;
    }
}