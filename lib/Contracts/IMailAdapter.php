<?php

namespace OCA\Enotes\Contracts;

use OCA\Enotes\Db\Settings;
use OCA\Enotes\Dto\MailAccountSetting;
use OCA\Mail\Exception\ClientException;
use OCA\Enotes\Dto\Account;

interface IMailAdapter
{
	/**
	 * Returns the initial settings. These will be overwritten with changes made by a user.
	 *
	 * @return Settings
	 */
	public function getDefaultSettings(): Settings;

	/**
	 * Returns an array with MailAccountSettings objects.
	 *
	 * @return MailAccountSetting[]
	 */
	public function getMailAccountSettings(): array;

	/**
	 * Returns email attachments.
	 *
	 * @param Account[] $accounts
	 * @param string $filterFromMailAddresses
	 * @return array
	 */
	public function fetchMailAttachments(array $accounts, string $filterFromMailAddresses): array;

	/**
	 * Return the activated mail accounts.
	 *
	 * @param Settings $settings
	 * @return Account[]
	 * @throws ClientException
	 */
	public function fetchActiveMailAccounts(Settings $settings): array;
}
