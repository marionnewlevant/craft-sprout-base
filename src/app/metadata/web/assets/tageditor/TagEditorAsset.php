<?php
/**
 * @link      https://sprout.barrelstrengthdesign.com
 * @copyright Copyright (c) Barrel Strength Design LLC
 * @license   https://craftcms.github.io/license
 */

namespace barrelstrength\sproutbase\app\metadata\web\assets\tageditor;

use craft\web\AssetBundle;

class TagEditorAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@sproutbaselib';

        $this->css = [
            'jquery.tag-editor/css/jquery.tag-editor.css'
        ];

        $this->js = [
            'jquery.tag-editor/js/jquery.tag-editor.min.js',
            'jquery.caret/js/jquery.caret.min.js'
        ];

        parent::init();
    }
}