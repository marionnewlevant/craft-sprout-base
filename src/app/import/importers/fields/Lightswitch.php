<?php

namespace barrelstrength\sproutbase\app\import\importers\fields;

use barrelstrength\sproutbase\app\import\base\FieldImporter;
use craft\fields\Lightswitch as LightswitchField;

class Lightswitch extends FieldImporter
{
    /**
     * @return string
     */
    public function getModelName()
    {
        return LightswitchField::class;
    }

    /**
     * Returns a boolean value
     *
     * @return mixed
     * @throws \Exception
     */
    public function getMockData()
    {
        return random_int(0, 1);
    }
}