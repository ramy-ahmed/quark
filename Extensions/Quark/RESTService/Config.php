<?php
namespace Quark\Extensions\Quark\RESTService;

use Quark\IQuarkExtensionConfig;

use Quark\Quark;

/**
 * Class Config
 *
 * @package Quark\Extensions\Quark\RESTService
 */
class Config implements IQuarkExtensionConfig {
	/**
	 * @var IQuarkRESTServiceDescriptor
	 */
	private $_descriptor;

	/**
	 * @var string
	 */
	private $_endpoint = '';

	/**
	 * @var string
	 */
	private $_source = '';

	/**
	 * @param IQuarkRESTServiceDescriptor $descriptor
	 * @param string $endpoint
	 * @param string $source
	 */
	public function __construct (IQuarkRESTServiceDescriptor $descriptor = null, $endpoint = '', $source = '') {
		if (func_num_args() == 1)
			$endpoint = Quark::WebHost();

		if (strlen(trim($source)) == 0)
			$source = Quark::ClassOf($descriptor);

		$this->_descriptor = $descriptor;
		$this->_endpoint = $endpoint;
		$this->_source = $source;
	}

	/**
	 * @param IQuarkRESTServiceDescriptor $descriptor
	 *
	 * @return IQuarkRESTServiceDescriptor
	 */
	public function Descriptor (IQuarkRESTServiceDescriptor $descriptor = null) {
		if (func_num_args() == 1)
			$this->_descriptor = $descriptor;

		return $this->_descriptor;
	}

	/**
	 * @param string $endpoint
	 *
	 * @return string
	 */
	public function Endpoint ($endpoint = '') {
		if (func_num_args() == 1)
			$this->_endpoint = $endpoint;

		return $this->_endpoint;
	}

	/**
	 * @param string $source
	 *
	 * @return string
	 */
	public function Source ($source = '') {
		if (func_num_args() == 1)
			$this->_source = $source;

		return $this->_source;
	}
}