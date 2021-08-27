<?php

namespace OCA\Enotes\Dto;

/**
 * Settings for the available mail accounts
 */
class MailAccountSetting implements \JsonSerializable
{
	protected string $id;

	protected string $email;

	protected bool $active;

	public function __construct(int $id, string $email, bool $active)
	{
		$this->id = $id;
		$this->email = $email;
		$this->active = $active;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function isActive(): bool
	{
		return $this->active;
	}

	public function jsonSerialize()
	{
		return get_object_vars($this);
	}

}
