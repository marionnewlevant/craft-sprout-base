<?php

namespace barrelstrength\sproutcore\web\sproutcore\cp;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class CpAsset extends AssetBundle
{
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->sourcePath = '@sproutcore/web/sproutcore/cp/dist';

		$this->css = [
			'css/sproutcp.css',
		];

		parent::init();
	}
}