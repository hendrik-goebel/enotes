<?php

namespace OCA\Enotes\AppInfo;

use OCA\Mail\Contracts\IMailManager;
use OCA\Mail\Contracts\IMailSearch;
use OCA\Mail\Service\Search\MailSearch;
use OCA\Mail\Service\MailManager;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\IServerContainer;
use OCP\Util;
use Psr\Container\ContainerInterface;
use OCA\Enotes\MailAdapter;
use OCA\Enotes\Contracts\IMailAdapter;


class Application extends App implements IBootstrap
{
	public const APP_ID = 'enotes';

	public function __construct(array $urlParams = array())
	{
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void
	{
		$context->registerParameter('hostname', Util::getServerHostName());
		$context->registerService('userFolder', function (ContainerInterface $c) {
			$userContainer = $c->get(IServerContainer::class);
			$uid = $c->get('UserId');

			return $userContainer->getUserFolder($uid);
		});

		$context->registerServiceAlias(IMailManager::class, MailManager::class);
		$context->registerServiceAlias(IMailAdapter::class, MailAdapter::class);
		$context->registerServiceAlias(IMailSearch::class, MailSearch::class);
	}

	public function boot(IBootContext $context): void
	{
	}
}
