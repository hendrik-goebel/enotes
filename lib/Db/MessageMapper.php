<?php

namespace OCA\Enotes\Db;

use OCA\Mail\Db\Mailbox;
use \OCP\DB\Exception;
class MessageMapper extends \OCA\Mail\Db\MessageMapper
{
	/**
	 * @param Mailbox $mailbox
	 * @return array
	 * @throws Exception
	 */
	public function findMailsWithAttachments(Mailbox $mailbox): array
	{
		$queryBuilder = $this->db->getQueryBuilder();
		$query = $queryBuilder->select('*')
			->from('mail_messages')
			->where('flag_attachments = 1')
			->andWhere('mailbox_id = :mailboxId')
			->setParameter('mailboxId', $mailbox->getId());
		return $query->execute()->fetchAll();
	}
}
