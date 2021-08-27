<?php
declare(strict_types=1);

namespace OCA\Enotes\Db;

use OCA\Enotes\Dto\MailAccountSetting;
use OCP\AppFramework\Db\Entity;
use JsonSerializable;

class Settings extends Entity implements JsonSerializable
{
	protected string $userId = '';

	/**
	 * @var MailAccountSetting[]
	 */
	public $mailAccountSettings;

	protected string $types = '';

	/**
	 * Maps the settings state so that it fits to the default settings
	 *
	 * @param MailAccountSetting[] $defaultMailAccounts
	 * @return $this
	 */
	public function mergeWithDefaultMailAccounts(array $defaultMailAccounts): Settings
	{
		if (empty($this->mailAccountSettings)) {
			$this->setMailAccountSettings($defaultMailAccounts);
			return $this;
		}

		$idsUser = array_map(function ($item) {
			return $item->getId();
		}, $this->mailAccountSettings);

		$idsDefault = array_map(function ($item) {
			return $item->getId();
		}, $defaultMailAccounts);

		$mailAccountsUser = array_combine($idsUser, $this->mailAccountSettings);
		$mailAccountsDefault = array_combine($idsDefault, $defaultMailAccounts);

		$resultAccounts = [];
		foreach ($mailAccountsDefault as $idDefault => $accountDefault) {
			$account = $accountDefault;
			if (in_array($idDefault, $idsUser)) {
				$account = $mailAccountsUser[$idDefault];
			}
			$resultAccounts[] = $account;
		}

		$this->setMailAccountSettings($resultAccounts);
		return $this;
	}

	public function jsonSerialize()
	{
		return get_object_vars($this);
	}
}
