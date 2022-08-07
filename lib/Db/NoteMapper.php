<?php
declare(strict_types=1);

namespace OCA\Enotes\Db;

use OC\DB\Exceptions\DbalException;
use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;

class NoteMapper extends QBMapper
{
	public function __construct(IDBConnection $db)
	{
		parent::__construct($db, 'enote_note', Note::class);
	}

	/**
	 * @param Entity $entity
	 * @return Entity
	 * @throws DbalException
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function insert(Entity $entity): Entity
	{
		try {
			return parent::insert($entity);
		} catch (DbalException $e) {
			if ($e->getReason() === DbalException::REASON_UNIQUE_CONSTRAINT_VIOLATION) {
				//Already stored, return entity
				return $this->findByHash($entity->getHash());
			} else {
				throw $e;
			}
		}
	}

	/**
	 * @param string $hash
	 * @return Entity
	 * @throws DoesNotExistException
	 * @throws MultipleObjectsReturnedException
	 */
	public function findByHash(string $hash): Entity
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('hash', $qb->createNamedParameter($hash))
			);

		return $this->findEntity($qb);
	}
}
