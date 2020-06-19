<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\seo\schema;

class IntangibleSchema extends ThingSchema
{
    /**
     * @return string
     */
    public function getName(): string
    {
        return 'Intangible';
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return 'Intangible';
    }

    /**
     * @return bool
     */
    public function isUnlistedSchemaType(): bool
    {
        return false;
    }
}