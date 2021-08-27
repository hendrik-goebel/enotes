<?php

namespace OCA\Enotes\Tests\Unit;

use OCA\Enotes\Constants\Device;
use OCA\Enotes\Service\NoteService;
use PHPUnit\Framework\TestCase;

class NoteServiceTest extends TestCase
{
	protected string $userId;

	protected NoteService $noteService;

	protected string $csv;

	protected Device $devideId;

	public function setUp(): void
	{
		$this->userId = 'smiggels';
		$this->noteService = new NoteService($this->userId);
		$this->csv = <<<NOTE
"Ihre Kindle-Notizen für:",,,
"GROSSE DENKER IN 60 MINUTEN - BAND 1: PLATON, ROUSSEAU, SMITH, KANT, HEGEL",,,
"von Walther Ziegler",,,
"Kostenlose sofortige Kindle-Vorschau:",,,
"https://amzn.eu/1C6G4DO",,,
----------------------------------------------,,,
,,,
"Anmerkungstyp","Position","Markiert?","Anmerkung"
"Markierung (Gelb)","Position 1518","","Der Mensch ist von seinem ganzen Wesen her ein Egoist."
NOTE;

		$this->deviceId = Device::KINDLE;
	}

	/**
	 * a note in csv format can be parsed to an object.
	 */
	public function testParseCsv()
	{
		$book = $this->noteService->parseCsv($this->csv, $this->deviceId);
		$this->assertEquals('GROSSE DENKER IN 60 MINUTEN - BAND 1: PLATON, ROUSSEAU, SMITH, KANT, HEGEL', $book->getTitle());
		$notes = $book->getNotes();
		$note = reset($notes);
		$this->assertEquals('Der Mensch ist von seinem ganzen Wesen her ein Egoist.', $note->getContent());
	}

}
