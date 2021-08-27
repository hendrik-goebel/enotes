<?php

namespace OCA\Enotes\Tests\Unit;

use OCA\Enotes\Db\Settings;
use OCA\Enotes\Contracts\IMailAdapter;
use OCA\Enotes\Dto\MailAccountSetting;

class MailAdapterFixture implements IMailAdapter
{
	protected Settings $defaultSettings;

	public function __construct()
	{

		$this->defaultSettings = new Settings();
		$this->defaultSettings->setMailAccountSettings(
			[
				new MailAccountSetting(1, 'mail1@xyz.de', true),
				new MailAccountSetting(2, 'mail2@xyz.de', true),
				new MailAccountSetting(3, 'mail4@xyz.de', true)
			]
		);
	}

	public function getDefaultSettings(): Settings
	{
		return $this->defaultSettings;
	}

	/**
	 * Returns an array with MailAccountSettings objects.
	 * @return MailAccountSetting[]
	 */
	public function getMailAccountSettings(): array
	{
		return [];
	}

	/**
	 * Returns email attachments.
	 *
	 * @param Account[] $accounts
	 * @param Str[] $filterFromMailAddresses
	 * @return array
	 */
	public function fetchMailAttachments(array $accounts, $filterFromMailAddresses): array
	{
		return [];
	}

	/**
	 * Return the activated mail accounts.
	 *
	 * @param Settings $settings
	 * @return Account[]
	 * @throws \OCA\Mail\Exception\ClientException
	 */
	public function fetchActiveMailAccounts(Settings $settings): array
	{
		return [];
	}
}
