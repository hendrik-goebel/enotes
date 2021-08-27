<?php
declare(strict_types=1);

namespace OCA\Enotes\Controller;

use OCA\Enotes\Dto\MailAccountSetting;
use OCP\AppFramework\Http\Response;
use OCA\Enotes\AppInfo\Application;
use OCA\Enotes\Db\BookMapper;
use OCA\Enotes\Db\SettingsMapper;
use OCA\Enotes\Db\Settings;
use OCA\Enotes\Contracts\IMailAdapter;
use OCA\Enotes\Service\SettingsService;
use OCA\Enotes\Service\NoteService;
use OCP\AppFramework\App;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use Exception;
use OCP\IL10N;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class SettingsController extends Controller
{
	protected IL10N $l;

	protected SettingsMapper $settingsMapper;

	protected SettingsService $settingsService;

	protected Settings $settings;

	protected string $userId;

	protected LoggerInterface $logger;

	protected NoteService $noteService;

	protected IMailAdapter $mailAdapter;

	protected BookMapper $bookMapper;

	public function __construct(
		$appName,
		IL10N $l,
		IRequest $request,
		SettingsMapper $settingsMapper,
		SettingsService $settingsService,
		NoteService $noteService,
		BookMapper $bookMapper,
		IMailAdapter $mailAdapter,
		LoggerInterface $logger,
		?string $UserId
	)
	{
		parent::__construct($appName, $request);
		$this->appName = $appName;
		$this->l = $l;
		$this->settingsMapper = $settingsMapper;
		$this->settingsService = $settingsService;
		$this->mailAdapter = $mailAdapter;
		$this->bookMapper = $bookMapper;
		$this->userId = $UserId;
		$this->logger = $logger;
		$this->noteService = $noteService;
	}

	/**
	 * @NoAdminRequired
	 */
	public function get(): Response
	{
		try {
			$settings = $this->settingsService->getSettings();
			$settingsJson = json_encode($settings);
			return new JSONResponse($settingsJson);
		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			$message = $this->l->t('error.settings.get') . $e->getMessage();
			return new JSONResponse($message, Http::STATUS_CONFLICT);
		}
	}

	/**
	 * @NoAdminRequired
	 * @param Settings[] $settings
	 * @return Response
	 */
	public function update(array $settings = []): Response
	{
		$settingsParams = $settings;
		$isInsert = false;
		try {
			$settings = $this->settingsMapper->findByUserId($this->userId);
		} catch (DoesNotExistException $e) {
			$app = new App(Application::APP_ID);
			$settings = $app->getContainer()->get(Settings::class);
			$isInsert = true;
		}

		$settings->setUserId($this->userId);
		$mailAccountSettings = [];
		foreach ($settingsParams['mailAccountSettings'] as $mailAccountSettingParam) {
			$mailAccountSettings[] = new MailAccountSetting((int)$mailAccountSettingParam['id'], $mailAccountSettingParam['email'], $mailAccountSettingParam['active']);
		}
		$settings->setMailAccountSettings($mailAccountSettings);
		$settings->setTypes($settingsParams['types']);

		try {
			if ($isInsert) {
				$this->settingsMapper->insert($settings);
			} else {
				$this->settingsMapper->update($settings);
			}

		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			$message = $this->l->t('error.settings.update') . $e->getMessage();
			return new JSONResponse($message, Http::STATUS_CONFLICT);
		}
		return new DataResponse([], Http::STATUS_OK);
	}

	/**
	 * @NoAdminRequired
	 */
	public function fetchNotes()
	{
		try {
			$settings = $this->settingsService->getSettings();
			$mailAccounts = $this->mailAdapter->fetchActiveMailAccounts($settings);

			$attachments = $this->mailAdapter->fetchMailAttachments(
				$mailAccounts,
				explode(',', $settings->getTypes())
			);

			$books = [];
			foreach ($attachments as $attachment) {
				$bookParsed = $this->noteService->parseCsv($attachment->getContent(), $attachment->getType());
				$book = $this->bookMapper->insert($bookParsed);
				$books[] = $book;
			}

			if (empty($books)) {
				throw new Exception('No mail attachments containing ebook reader notes could be found.');
			}

			$message = $this->l->t('success.settings.sync');
			return new DataResponse(['message' => $message, 'data' => json_encode($books)]);

		} catch (Exception $e) {
			$this->logger->error($e->getMessage(), $e->getTrace());
			$message = $this->l->t('error.settings.sync') . $e->getMessage();
			return new JSONResponse($message, Http::STATUS_CONFLICT);
		}
	}
}
