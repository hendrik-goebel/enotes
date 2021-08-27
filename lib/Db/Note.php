<?php
declare(strict_types=1);

namespace OCA\Enotes\Db;

use OCP\AppFramework\Db\Entity;
use stdClass;
use JsonSerializable;

class Note extends Entity implements JsonSerializable
{
	protected $type;

	protected $location;

	protected $content;

	protected $bookId;

	protected $hash;

	public function __toString()
	{
		return $this->hash;
	}

	public function __construct()
	{
		$this->addType('id', 'integer');
		$this->addType('type', 'string');
		$this->addType('location', 'string');
		$this->addType('content', 'text');
		$this->addType('bookId', 'integer');
		$this->addType('hash', 'string');
	}

	/**
	 * @param string $type
	 */
	public function setType(string $type)
	{
		parent::setType($type);
		$this->updateHash();
	}

	/**
	 * @param string $location
	 */
	public function setLocation(string $location)
	{
		parent::setLocation($location);
		$this->updateHash();
	}

	/**
	 * @param string $content
	 */
	public function setContent(string $content)
	{
		$this->content = $content;
		$this->markFieldUpdated('content');
		$this->updateHash();
	}

	/**
	 * @param int $bookId
	 */
	public function setBookId(int $bookId)
	{
		parent::setBookId($bookId);
		$this->updateHash();
	}

	public function updateHash()
	{
		if ($this->getBookId() && $this->getLocation() && $this->getContent() && $this->getType()) {
			$this->setHash(hash('sha256',
				$this->getBookId() .
				$this->getLocation() .
				$this->getContent() .
				$this->getType()));
		}
	}

	/**
	 * @return stdClass
	 */
	public function jsonSerialize(): stdClass
	{
		$obj = new StdClass();
		$obj->type = $this->getType();
		$obj->content = $this->getContent();
		$obj->bookId = $this->getBookId();
		$obj->location = $this->getLocation();
		return $obj;
	}
}
