<?php
namespace Quark\ViewResources\Quark\JS;

use Quark\IQuarkViewResource;
use Quark\IQuarkViewResourceWithDependencies;
use Quark\IQuarkLocalViewResource;
use Quark\IQuarkViewResourceType;

use Quark\QuarkJSViewResourceType;
use Quark\QuarkLocalCoreJSViewResource;

use Quark\ViewResources\jQuery\jQueryCore;

/**
 * Class QuarkMVC
 *
 * @package Quark\ViewResources\Quark\JS
 */
class QuarkMVC implements IQuarkViewResource, IQuarkLocalViewResource, IQuarkViewResourceWithDependencies {
	/**
	 * @return IQuarkViewResourceType
	 */
	public function Type () {
		return new QuarkJSViewResourceType();
	}

	/**
	 * @return string
	 */
	public function Location () {
		return __DIR__ . '/QuarkMVC.js';
	}

	/**
	 * @return bool
	 */
	public function CacheControl () {
		return true;
	}

	/**
	 * @return IQuarkViewResource[]
	 */
	public function Dependencies () {
		return array(
			new jQueryCore(),
			new QuarkLocalCoreJSViewResource()
		);
	}
}