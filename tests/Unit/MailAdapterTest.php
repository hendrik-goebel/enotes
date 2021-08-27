<?php

namespace OCA\Enotes\Tests\Unit;

use OCA\Enotes\Db\Settings;
use OCA\Enotes\MailAdapter;
use OCA\Enotes\Dto\MailAccountSetting;
use OCA\Mail\Account;
use OCA\Mail\Address;
use OCA\Mail\AddressList;
use OCA\Mail\Db\MailAccount;
use OCA\Mail\Db\MailAccountMapper;
use OCA\Enotes\Db\MessageMapper;
use OCA\Mail\Db\Message;
use OCA\Mail\Db\Mailbox;

use OCA\Mail\Service\AccountService;
use OCA\Mail\Service\MailManager;
use OCA\Mail\Service\Search\MailSearch;
use PHPUnit\Framework\TestCase;

class MailAdapterTest extends TestCase
{
	protected MailAdapter $mailAdapter;

	public function setUp(): void
	{
		$this->userId = 'geronimo';

		$accountParams = [
			'emailAddress' => 'mail1@mail.de',
			'accountId' => '123'
		];
		$this->mailAccount = new MailAccount($accountParams);

		$mailAccountMapper = $this->getMockBuilder(MailAccountMapper::class)
			->disableOriginalConstructor()
			->getMock();

		$mailAccountMapper->method('findById')
			->willReturn($this->mailAccount);

		$this->accountService = $this->getMockBuilder(AccountService::class)
			->disableOriginalConstructor()
			->getMock();

		$this->accountService->method('findByUserId')
			->willreturn([$this->mailAccount]);

		$mailSearch = $this->getMockBuilder(MailSearch::class)
			->disableOriginalConstructor()
			->getMock();

		$this->mailbox = $this->getMockBuilder(Mailbox::class)
			->disableOriginalConstructor()
			->getMock();

		$this->mailManager = $this->getMockBuilder(MailManager::class)
			->disableOriginalConstructor()
			->getMock();

		$this->mailManager
			->method('getMailBoxes')
			->willReturn([$this->mailbox]);

		$this->messageMapper = $this->getMockBuilder(MessageMapper::class)
			->disableOriginalConstructor()
			->getMock();

		$this->account = $this->getMockBuilder(Account::class)
			->disableOriginalConstructor()
			->getMock();

		$this->mailAdapter = new MailAdapter(
			$this->accountService,
			$this->mailManager,
			$mailSearch,
			$this->messageMapper,
			$this->userId
		);
	}

	/**
	 * A MailAdapter will return the current mailAccountSettings
	 */
	public function testGetMailAccountSettings()
	{
		$result = $this->mailAdapter->getMailAccountSettings();
		$this->assertEquals('mail1@mail.de', $result[0]->getEmail());
		$this->assertEquals('123', $result[0]->getId());
	}

	/**
	 * Corresponding MailAccounts which are set as active in the settings will be returned.
	 * @throws \OCA\Mail\Exception\ClientException
	 */
	public function testFetchActiveMailAccounts()
	{
		$settings = new Settings();
		$settings->setMailAccountSettings([new MailAccountSetting(1, 'email@email.de', true)]);


		$this->accountService->expects($this->once())
			->method('find')
			->with(
				$this->equalTo($this->userId),
				$this->equalTo(1)
			);

		$mailAccounts = $this->mailAdapter->fetchActiveMailAccounts($settings);
	}

	/**
	 * A mailAdapter will returned mails with attachments according to the given filter.
	 */
	public function testFetchMailAttachments()
	{
		$fromMailAddress = 'from@test.de';

		$mailAttachment = [
			'content' => 'Attachment content',
			'name' => 'notebooks.csv',
			'size' => '1'
		];
		$this->mailManager
			->method('getMailAttachments')
			->willReturn([$mailAttachment]);

		$mailRow = [
			'id' => 1
		];

		$mailAddress = $this->getMockBuilder(Address::class)
			->disableOriginalConstructor()
			->getMock();

		$mailAddress->method('getEmail')
			->willReturn($fromMailAddress);

		$addressList = new AddressList([$mailAddress]);


		$this->messageMapper->method('findMailsWithAttachments')
			->willReturn([$mailRow]);

		$message = $this->getMockBuilder(Message::class)
			->disableOriginalConstructor()
			->getMock();

		$message
			->method('getFrom')
			->willReturn($addressList);

		$this->mailManager
			->method('getMessage')
			->willReturn($message);

		$returnedAttachments = $this->mailAdapter->fetchMailAttachments([$this->account], [$fromMailAddress]);
		$this->assertEquals($mailAttachment['content'], $returnedAttachments[0]->getContent());
		$this->assertEquals('Kindle', $returnedAttachments[0]->getType());

	}
}
