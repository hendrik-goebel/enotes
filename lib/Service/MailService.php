<?php
declare(strict_types=1);

namespace OCA\Enotes\Service;

use OCA\Mail\Service\AccountService;
use OCA\Mail\Contracts\IMailManager;
use OCA\Mail\Contracts\IMailSearch;

class MailService {

	protected string $currentUserId;

	protected AccountService $accountService;

	protected IMailManager $mailManager;

	public function __construct(
		AccountService $accountService,
		IMailManager $mailManager,
		IMailSearch $mailSearch,


		?string $UserId
	) {
		$this->accountService = $accountService;
		$this->currentUserId = $UserId;
		$this->mailManager = $mailManager;
		$this->mailSearch = $mailSearch;
	}

	public function getMailAttachments(): array {

		$mailAccounts = $this->getMailAccounts();
		$attachments = [];

		foreach ($mailAccounts as $mailAccount) {
			try {
				$mailbox = $this->mailManager->getMailbox($this->currentUserId, $mailAccount->getId());
				$mails = $this->getMails($mailAccount, $mailbox);
				$folder = $mailAccount->getMailbox($mailbox->getName());
				$attachments = $this->getCsvAttachments($mails, $folder);
			} catch (\Exception $e) {
				if (!empty($attachments)) {
					return $attachments;
				}
				throw $e;
			}
		}
		return $attachments;
	}

	public function getMailAccounts(): array {

		return $this->accountService->findByUserId($this->currentUserId);
	}


	public function getCsvAttachments(array $mails, $folder): array {

		$csvAttachments = [];
		foreach ($mails as $mail) {
			$message = $folder->getMessage($mail->getUid());

			foreach ($message->attachments as $attachmentArray) {
				$attachment = $folder->getAttachment($message->getUid(), $attachmentArray['id']);
				if (preg_match('#\.csv$#', $attachment->getName(), $matches) === 1) {
					$csvAttachments[] = $attachment;
				}
			}
		}
		return $csvAttachments;
	}
}
