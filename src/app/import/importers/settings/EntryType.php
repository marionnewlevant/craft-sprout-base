<?php

namespace barrelstrength\sproutbase\app\import\importers\settings;

use barrelstrength\sproutbase\app\import\base\SettingsImporter;
use barrelstrength\sproutbase\SproutBase;
use craft\models\EntryType as EntryTypeModel;
use craft\records\EntryType as EntryTypeRecord;
use craft\elements\Entry;
use Craft;

class EntryType extends SettingsImporter
{
    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('sprout-base', 'Entry Type');
    }

    /**
     * @return string
     */
    public function getModelName()
    {
        return EntryTypeModel::class;
    }

    /**
     * @param EntryTypeModel $entryType
     * @param array          $rows
     *
     * @return mixed|void
     * @throws \Exception
     */
    public function setModel($entryType, array $rows = [])
    {
        // Set the simple stuff
        $entryType->sectionId = $rows['sectionId'] ?? null;
        $entryType->name = $rows['name'] ?? null;
        $entryType->handle = $rows['handle'] ?? null;
        $entryType->hasTitleField = $rows['hasTitleField'] ?? true;
        $entryType->titleLabel = $rows['titleLabel'] ?? Craft::t('sprout-base', 'Title');
        $entryType->titleFormat = $rows['titleFormat'] ?? '';

        if (isset($rows['fieldLayout'])) {
            $fieldLayoutTabs = $rows['fieldLayout'];
            $fieldLayout = [];
            $requiredFields = [];

            foreach ($fieldLayoutTabs as $tab) {
                $tabName = $tab['name'];
                $fields = $tab['fields'];

                foreach ($fields as $fieldSettings) {
                    $model = SproutBase::$app->importers->getImporter($fieldSettings);

                    $field = SproutBase::$app->settingsImporter->saveSetting($fieldSettings, $model);

                    $fieldLayout[$tabName][] = $field->id;

                    if ($field->required) {
                        $requiredFields[] = $field->id;
                    }
                }
            }

            if ($entryType->getFieldLayout() != null) {
                // Remove previous field layout and update layout
                Craft::$app->getFields()->deleteLayoutById($entryType->fieldLayoutId);
            }

            $fieldLayout = Craft::$app->getFields()->assembleLayout($fieldLayout, $requiredFields);

            // Make Entry element as default
            $fieldLayout->type = empty($rows['elementType']) ? Entry::class : $rows['elementType'];

            $entryType->setFieldLayout($fieldLayout);
        }

        $this->model = $entryType;
    }

    /**
     * @inheritdoc
     */
    public function getRecordName()
    {
        return EntryTypeRecord::class;
    }

    /**
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\EntryTypeNotFoundException
     */
    public function save()
    {
        return Craft::$app->getSections()->saveEntryType($this->model);
    }

    /**
     * @param $id
     *
     * @return mixed|void
     */
    public function deleteById($id)
    {
        //return craft()->sections->deleteEntryTypeById($id);
    }

    /**
     * @param null $handle
     *
     * @return mixed
     */
    public function getModelByHandle($handle = null)
    {
        $types = Craft::$app->getSections()->getEntryTypesByHandle($handle);

        if (!empty($types)) {
            return $types[0];
        }

        return null;
    }
}