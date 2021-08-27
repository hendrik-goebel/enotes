<?php

namespace OCA\Enotes;

use OCA\Enotes\Constants\Device;
use OCA\Enotes\Db\MessageMapper;
use OCA\Enotes\Dto\MailAccountSetting;
use OCA\Mail\Service\AccountService;
use OCA\Mail\Contracts\IMailManager;
use OCA\Mail\Contracts\IMailSearch;
use OCA\Enotes\Contracts\IMailAdapter;
use OCA\Enotes\Db\Settings;
use OCA\Enotes\Dto\Attachment;

/**
 * Provides access to the classes of OCA\Mail app.
 * @package OCA\Enotes
 */
class MailAdapter implements IMailAdapter
{
	protected string $currentUserId;

	protected AccountService $accountService;

	protected IMailManager $mailManager;

	protected IMailSearch $mailSearch;

	protected MessageMapper $messageMapper;

	public function __construct(
		AccountService $accountService,
		IMailManager   $mailManager,
		IMailSearch    $mailSearch,
		MessageMapper  $messageMapper,
		?string        $userId
	)
	{
		$this->mailManager = $mailManager;
		$this->currentUserId = $userId;
		$this->mailSearch = $mailSearch;
		$this->accountService = $accountService;
		$this->messageMapper = $messageMapper;
	}

	public function getDefaultSettings(): Settings
	{
		$defaultSettings = new Settings();
		$defaultSettings->setMailAccountSettings($this->getMailAccountSettings());
		$defaultSettings->setTypes('no-reply@amazon.com');
		return $defaultSettings;
	}

	/**
	 * @return MailAccountSetting[]
	 */
	public function getMailAccountSettings(): array
	{
		$accounts = $this->accountService->findByUserId($this->currentUserId);
		$settings = [];
		foreach ($accounts as $account) {
			$settings[] = new MailAccountSetting($account->getId(), $account->getEmail(), true);
		}
		return $settings;
	}

	/**
	 * @param Account[] $accounts
	 * @param Str[] $filterFromMailAddresses
	 * @return Attachment[]
	 * @throws \OCA\Mail\Exception\ClientException
	 * @throws \OCA\Mail\Exception\ServiceException
	 * @throws \OCP\DB\Exception
	 */
	public function fetchMailAttachments(array $accounts, $filterFromMailAddresses): array
	{
		$attachments = [];
		foreach ($accounts as $account) {

			$mailboxes = $this->mailManager->getMailBoxes($account);
			foreach ($mailboxes as $mailbox) {

				$rows = $this->messageMapper->findMailsWithAttachments($mailbox);
				foreach ($rows as $row) {
					$message = $this->mailManager->getMessage($this->currentUserId, $row['id']);
					$senders = $message->getFrom();
					foreach ($senders->iterate() as $sender) {
						$senderMailAddress = $sender->getEmail();
						if (in_array($senderMailAddress, $filterFromMailAddresses)) {
							$mailAttachments = $this->mailManager->getMailAttachments($account, $mailbox, $message);
							$mailAttachmentsFiltered = array_filter($mailAttachments, function ($item) {
								if (preg_match('#\.csv$#', $item['name'])) {
									return true;
								}
								return false;
							});
							$attachmentObjects = array_map(function ($item) use ($filterFromMailAddresses) {
								return new Attachment($item['name'], Device::KINDLE, $item['content']);

							}, $mailAttachmentsFiltered);
							$attachments = array_merge($attachments, $attachmentObjects);
						}
					}
				}
			}
		}
		return $attachments;
	}

	/**
	 * @param Settings $settings
	 * @return Account[]
	 * @throws \OCA\Mail\Exception\ClientException
	 */
	public function fetchActiveMailAccounts(Settings $settings): array
	{
		$mailAccounts = [];
		foreach ($settings->getMailAccountSettings() as $mailAccountSetting) {
			if ($mailAccountSetting->isActive()) {
				$mailAccounts[] = $this->accountService->find($this->currentUserId, $mailAccountSetting->getId());
			}
		}
		return $mailAccounts;
	}
}
