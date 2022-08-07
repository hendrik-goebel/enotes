<?php
declare(strict_types=1);

namespace OCA\Enotes\Controller;

use OCA\Enotes\Db\BookMapper;
use OCA\Enotes\MailAdapter;
use OCA\Enotes\Service\SettingsService;
use OCP\IRequest;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Controller;
use OCP\Util;
use OCP\IL10N;

use OCA\Mail\Contracts\IMailManager;
use OCA\Mail\Contracts\IMailSearch;
use OCA\Mail\Service\AccountService;
use OCA\Mail\Service\AliasesService;

use OCA\Enotes\Service\NoteService;

class NoteController extends Controller
{
	/**
	 * @var
	 */
	protected $appName;

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
#
	protected  $mailAdapter;

	protected  $settingsService;

	protected  $bookMapper;

	public function __construct(
		$appName,
		IRequest $request,
		IMailManager $mailManager,
		IL10N $l,
		IMailSearch $mailSearch,
		AccountService $accountService,
		AliasesService $aliasesService,
		MailAdapter $mailAdapter,
		SettingsService $settingsService,
		NoteService $noteService,
		BookMapper $bookMapper,
		?string $UserId
	)
	{
		parent::__construct($appName, $request);
		$this->appName = $appName;
		$this->mailManager = $mailManager;
		$this->l = $l;
		$this->accountService = $accountService;
		$this->currentUserId = $UserId;
		$this->aliasesService = $aliasesService;
		$this->mailSearch = $mailSearch;
		$this->mailAdapter = $mailAdapter;
		$this->deviceId = 'Kindle';
		$this->settingsService = $settingsService;
		$this->noteService = $noteService;
		$this->bookMapper = $bookMapper;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function list()
	{
		Util::addScript($this->appName, 'enotes-main');
		Util::addStyle($this->appName, 'icons');

		$books = $this->bookMapper->findByUserId($this->currentUserId);
		if (!empty($books)) {
			return new JSONResponse(json_encode($books));
		}
	}
}
