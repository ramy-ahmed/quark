<?php
namespace Quark\Scenarios\Generate;

use Quark\IQuarkTask;

use Quark\Quark;
use Quark\QuarkCLIBehavior;

/**
 * Class Password
 *
 * @package Quark\Scenarios\Generate
 */
class Password implements IQuarkTask {
	use QuarkCLIBehavior;

	/**
	 * @param int $argc
	 * @param array $argv
	 *
	 * @return void
	 */
	public function Task ($argc, $argv) {
		$length = $this->ServiceArg();
		
		$this->ShellView(
			'Generate/Password',
			'Your generated password is: ' . $this->ShellLineSuccess(Quark::GeneratePassword($length ? $length : 10))
		);
	}
}