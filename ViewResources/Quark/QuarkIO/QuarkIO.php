<?php
namespace Quark\ViewResources\Quark\QuarkIO;

use Quark\IQuarkSpecifiedViewResource;
use Quark\IQuarkViewResource;
use Quark\IQuarkViewResourceWithDependencies;
use Quark\IQuarkLocalViewResource;
use Quark\IQuarkViewResourceType;

use Quark\QuarkJSViewResourceType;
use Quark\QuarkLocalCoreJSViewResource;

use Quark\ViewResources\jQuery\jQueryCore;

/**
 * Class QuarkIO
 *
 * @package Quark\ViewResources\Quark\QuarkIO
 */
class QuarkIO implements IQuarkSpecifiedViewResource, IQuarkLocalViewResource, IQuarkViewResourceWithDependencies {
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
		return __DIR__ . '/QuarkIO.js';
	}

	/**
	 * @return bool
	 */
	public function Minimize () {
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