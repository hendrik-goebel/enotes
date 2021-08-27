<?php

namespace OCA\Enotes\Tests\Unit\Controller;

use OC\L10N\LazyL10N;
use OCA\Enotes\Db\BookMapper;
use OCA\Enotes\Db\Settings;
use OCA\Enotes\Db\SettingsMapper;
use OCA\Enotes\Dto\MailAccountSetting;
use OCA\Enotes\Tests\Unit\MailAdapterFixture;
use OCA\Enotes\Service\MailService;
use OCA\Enotes\Service\NoteService;
use OCA\Enotes\Service\SettingsService;
use OCA\Enotes\Controller\SettingsController;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IL10N;
use PHPunit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SettingsControllerTest extends TestCase
{
	protected $controller;
	/**
	 * @var IL10N
	 */
	protected $l;
	/**
	 * @var IMailManager
	 */
	protected $mailManager;

	/**
	 * @var string
	 */
	protected $currentUserId;

	/**
	 * @var AccountService
	 */
	protected $accountService;

	/**
	 * @var MailService
	 */
	protected $mailService;

	/**
	 * @var NoteService
	 */
	protected $noteService;

	/**
	 * @var BookMapper
	 */
	protected $bookMapper;

	/**
	 * @var SettingsMapper
	 */
	protected $settingsMapper;

	protected Settings $exampleSettings;

	protected MailAdapterFixture $mailAdapter;

	protected array $settingsParams;

	public function setUp(): void
	{
		$this->settings = new Settings();
		$this->settings->setMailAccountSettings(
			[
				new MailAccountSetting(1, 'mail@xyz.de', true),
				new MailAccountSetting(2, 'mail@xyz.de', true)
			]
		);

		$this->defaultSettings = new Settings();
		$this->settings->setMailAccountSettings(
			[
				new MailAccountSetting(1, 'mail1@xyz.de', true),
				new MailAccountSetting(2, 'mail2@xyz.de', true),
				new MailAccountSetting(3, 'mail4@xyz.de', true)
			]
		);

		$this->settingsParams = [
			'userId' => 'admin',
			'mailAccountSettings' => [
				['id' => 1,
					'email' => 'mail1@xyz.de',
					'active' => true
				],
				[
					'id' => 2,
					'email' => 'mail2@xyz.de',
					'active' => true
				],
			],
			'types' => 'type1@type.de,type2@type.de'
		];

		$this->settingsMapper = $this->getMockBuilder(SettingsMapper::class)
			->disableOriginalConstructor()
			->getMock();
		$this->settingsMapper->method('findByUserId')->willReturn($this->settings);

		$this->mailAdapter = $this->getMockBuilder(MailAdapterFixture::class)
			->getMock();

		$this->mailAdapter->method('getDefaultSettings')
			->willReturn($this->defaultSettings);

		$this->settingsService = $this->getMockBuilder(SettingsService::class)
			->disableOriginalConstructor()
			->getMock();

		$this->noteService = $this->getMockBuilder(NoteService::class)
			->disableOriginalConstructor()
			->getMock();

		$this->bookMapper = $this->getMockBuilder(BookMapper::class)
			->disableOriginalConstructor()
			->getMock();

		$this->settingsService
			->method('getSettings')
			->willReturn($this->settings);

		$this->request = $this->getMockBuilder('OCP\IRequest')->getMock();
	}

	protected function createSettingsController()
	{
		$appName = 'enotes';
		$userId = 'sauron';

		$l = $this->getMockBuilder(LazyL10N::class)
			->disableOriginalConstructor()
			->getMock();

		$logger = $this->getMockBuilder(LoggerInterface::class)
			->disableOriginalConstructor()
			->getMock();

		return new SettingsController(
			$appName,
			$l,
			$this->request,
			$this->settingsMapper,
			$this->settingsService,
			$this->noteService,
			$this->bookMapper,
			$this->mailAdapter,
			$logger,
			$userId
		);
	}

	/**
	 * For a user who updates settings for the first time,
	 * a new Settings record will be created in the database.
	 */
	public function testCreateSettings()
	{
		$this->settingsMapper->method('findByUserId')
			->willThrowException(new DoesNotExistException('does not exist'));

		$this->settingsMapper->expects($this->once())->method('insert');
		$settingsController = $this->createSettingsController();

		$settingsController->update($this->settingsParams);
	}

	/**
	 * When an account will be added to the default account, it must be added to the userAccount.
	 * The already existing mailAccounts will not be changed.
	 */
	public function testUpdateMailAccounts()
	{
		$userMailAccounts = [
			new MailAccountSetting(1, 'mail1@xyz.de', false),
			new MailAccountSetting(2, 'mail2@xyz.de', false)
		];

		$newMailAccounts = [
			new MailAccountSetting(1, 'mail1@xyz.de', true),
			new MailAccountSetting(2, 'mail2@xyz.de', true),
			new MailAccountSetting(3, 'mail3@xyz.de', true),
		];

		$this->settings->setMailAccountSettings([]);
		$this->settings->mergeWithDefaultMailAccounts($newMailAccounts);

		$resultMailAccounts = $this->settings->getMailAccountSettings();

		$this->assertCount(3, $resultMailAccounts);
		$this->assertEquals('mail2@xyz.de', $resultMailAccounts[1]->getEmail());


		$this->settings->setMailAccountSettings($userMailAccounts);
		$this->settings->mergeWithDefaultMailAccounts($newMailAccounts);

		$resultMailAccounts = $this->settings->getMailAccountSettings();

		$this->assertCount(3, $resultMailAccounts);
		$this->assertEquals('mail3@xyz.de', $resultMailAccounts[2]->getEmail());
		$this->assertFalse($resultMailAccounts[0]->isActive());
	}

	/**
	 * For a user who updates settings which already exist in the database,
	 * the existing Settings record will be updated.
	 */
	public function testUpdateSettings()
	{
		$this->settingsMapper->method('findByUserId')
			->willReturn($this->settings);
		$this->settingsMapper->expects($this->once())->method('update');
		$settingsController = $this->createSettingsController();

		$settingsController->update($this->settingsParams);
	}

	/**
	 * A settings controller get method returns a setting object
	 * of the current user if it exists.
	 */
	public function testGetStoredSettings()
	{
		$settingsController = $this->createSettingsController();
		$result = $settingsController->get()->getData();
		$decodedResult = json_decode($result);
		$this->assertEquals($decodedResult->mailAccountSettings[0]->email, $this->settings->getMailAccountSettings()[0]->getEmail());
		$this->assertEquals($decodedResult->mailAccountSettings[1]->email, $this->settings->getMailAccountSettings()[1]->getEmail());
	}
}
