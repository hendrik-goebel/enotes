<?php

namespace OCA\Enotes\Db;

use OC\DB\Exceptions\DbalException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\Entity;

class BookMapper extends QBMapper
{
	protected NoteMapper $noteMapper;

	public function __construct(
		IDBConnection $db,
		NoteMapper    $noteMapper
	)
	{
		$this->noteMapper = $noteMapper;
		parent::__construct($db, 'enote_book', Book::class);
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
			$bookSaved = parent::insert($entity);
		} catch (DbalException $e) {
			if ($e->getReason() === DbalException::REASON_UNIQUE_CONSTRAINT_VIOLATION) {
				// If already exists return the existing entity
				$bookSaved = $this->findByHash($entity->getHash());
			} else {
				throw $e;
			}
		}

		foreach ($entity->getNotes() as $note) {
			$note->setBookId($bookSaved->getId());
			$noteSaved = $this->noteMapper->insert($note);
			$bookSaved->addNote($noteSaved);
		}

		return $bookSaved;
	}

	/**
	 * @param $userId
	 * @return array
	 * @throws \OCP\DB\Exception
	 */
	public function findByUserId($userId): array
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select(
			'b.id as book_id', 'b.title as book_title', 'b.hash as book_hash', 'b.device_id as book_device_id',
			'n.id as note_id', 'n.content as note_content', 'n.type as note_type', 'n.location as note_location', 'n.book_id as note_book_id', 'n.hash as note_hash'
		)
			->from('enote_book', 'b')
			->join('b', 'enote_note', 'n', 'b.id = n.book_id')
			->where(
				$qb->expr()->eq('user_id', $qb->createNamedParameter($userId))
			);

		$cursor = $qb->execute();
		$books = [];

		while ($row = $cursor->fetch()) {
			$bookRow = [];
			$noteRow = [];
			foreach ($row as $key => $value) {
				if (str_starts_with($key, 'book_')) {
					$bookRow[substr($key, 5)] = $value;
				}
				if (str_starts_with($key, 'note_')) {
					$noteRow[substr($key, 5)] = $value;
				}
			}

			if (!key_exists($bookRow['id'], $books)) {
				$books[$bookRow['id']] = Book::fromRow($bookRow);
			}

			$note = Note::fromRow($noteRow);
			$books[$bookRow['id']]->addNote($note);
		}
		$cursor->closeCursor();
		return array_values($books);
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
