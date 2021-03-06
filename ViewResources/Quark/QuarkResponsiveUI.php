<?php
namespace Quark\ViewResources\Quark;

use Quark\IQuarkViewResource;
use Quark\IQuarkLocalViewResource;
use Quark\IQuarkInlineViewResource;

/**
 * Class QuarkResponsiveUI
 *
 * @package Quark\ViewResources\Quark
 */
class QuarkResponsiveUI implements IQuarkViewResource, IQuarkLocalViewResource, IQuarkInlineViewResource {
	const DEVICE_WIDTH = 'device-width';
	const DEVICE_HEIGHT = 'device-height';
	const SCALE1 = 1.0;
	const SCALE2 = 2.0;

	/**
	 * @var string $_width = self::DEVICE_WIDTH
	 */
	private $_width = self::DEVICE_WIDTH;

	/**
	 * @var float $_scale = self::SCALE1
	 */
	private $_scale = self::SCALE1;

	/**
	 * @param string $width = self::DEVICE_WIDTH
	 * @param float $scale = self::SCALE1
	 */
	public function __construct ($width = self::DEVICE_WIDTH, $scale = self::SCALE1) {
		$this->_width = $width;
		$this->_scale = $scale;
	}

	/**
	 * @param bool $minimize
	 *
	 * @return string
	 */
	public function HTML ($minimize) {
		return '<meta name="viewport" content="width=' . $this->_width . ', initial-scale=' . $this->_scale . '" />';
	}

	/**
	 * @return bool
	 */
	public function Minimize () {
		return true;
	}
}