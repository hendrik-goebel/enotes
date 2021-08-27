<?php
namespace OCA\Enotes\Dto;

class Attachment
{
	protected string $name;
	protected string $type;
	protected string $content;

	public function __construct(string $name, string $type, string $content)
	{
		$this->name = $name;
		$this->type = $type;
		$this->content = $content;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return string
	 */
	public function getContent(): string
	{
		return $this->content;
	}
}
