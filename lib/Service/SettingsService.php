<?php

namespace OCA\Enotes\Service;

use OCA\Enotes\Contracts\IMailAdapter;
use OCA\Enotes\Db\Settings;
use OCA\Enotes\Db\SettingsMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IL10N;

class SettingsService
{
	protected string $appName;

	protected IL10N $l;

	protected SettingsMapper $settingsMapper;

	protected Settings $settings;

	protected IMailAdapter $mailAdapter;

	protected string $userId;

	public function __construct(
		$appName,
		IL10N $l,
		SettingsMapper $settingsMapper,
		IMailAdapter $mailAdapter,
		?string $UserId
	)
	{
		$this->appName = $appName;
		$this->l = $l;
		$this->settingsMapper = $settingsMapper;
		$this->mailAdapter = $mailAdapter;
		$this->userId = $UserId;
	}

	public function getSettings(): Settings
	{
		try {
			$defaultSettings = $this->mailAdapter->getDefaultSettings();
			$settings = $this->settingsMapper->findByUserId($this->userId);
			$settings->mergeWithDefaultMailAccounts($defaultSettings->getMailAccountSettings());
		} catch (DoesNotExistException $e) {
			$settings = $defaultSettings;
			$settings->setUserId($this->userId);
		}
		return $settings;
	}
}
