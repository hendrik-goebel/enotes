<?php
declare(strict_types=1);

namespace OCA\Enotes\Db;

use OCP\AppFramework\Db\Entity;
use JsonSerializable;

class Book extends Entity implements JsonSerializable
{
	protected $title;

	protected $deviceId;

	protected $userId;

	protected $hash;

	protected $notes;

	public function __toString()
	{
		return $this->hash;
	}

	public function __construct()
	{
		$this->addType('id', 'integer');
		$this->addType('title', 'string');
		$this->addType('deviceId', 'string');
		$this->addType('userId', 'string');
		$this->addType('hash', 'string');
	}

	/**
	 * @param string $title
	 */
	public function setTitle(string $title)
	{
		parent::setTitle($title);
		$this->updateHash();
	}

	/**
	 * @param string $deviceId
	 */
	public function setDeviceId(string $deviceId)
	{
		parent::setDeviceId($deviceId);
		$this->updateHash();
	}

	/**
	 * @param string $userId
	 */
	public function setUserId(string $userId)
	{
		parent::setUserId($userId);
		$this->updateHash();
	}

	/**
	 * @return Note[]
	 */
	public function getNotes(): array
	{
		return $this->notes;
	}

	/**
	 * @return bool
	 */
	public function updateHash(): bool
	{
		if ($this->getTitle() && $this->getDeviceId() && $this->getUserId()) {
			$this->setHash(
				hash('sha256',
					$this->getTitle() . $this->getDeviceId() . $this->getUserId()
				)
			);
			return true;
		}
		return false;
	}


	/**
	 * @param Note[] $notes
	 */
	public function setNotes(array $notes)
	{
		$this->notes = $notes;
	}

	/**
	 * @param Note $note
	 */
	public function addNote(Note $note)
	{

		$this->notes[] = $note;
	}

	/**
	 * @return array
	 */
	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'title' => $this->getTitle(),
			'notes' => $this->getNotes()
		];
	}
}
