<?php
namespace Quark\Extensions\Mail\Providers;

use Quark\QuarkURI;

use Quark\Extensions\Mail\IQuarkMailProvider;

/**
 * Class YandexMail
 *
 * @package Quark\Extensions\Mail\Providers
 */
class YandexMail implements IQuarkMailProvider {
	/**
	 * @param string $username
	 * @param string $password
	 *
	 * @return QuarkURI
	 */
	public function MailSMTP ($username, $password) {
		return QuarkURI::FromURI('ssl://smtp.yandex.ru:465')->User($username, $password);
	}
}