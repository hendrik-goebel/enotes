<?php

namespace OCA\Enotes\Service;

use OCA\Enotes\Db\Note;
use OCA\Enotes\Db\Book;

class NoteService
{
	protected string $currentUserId;

	public function __construct(
		?string    $UserId
	)
	{
		$this->currentUserId = $UserId;
	}

	/**
	 * Parses the incoming CSV. This needs to be refactored as soon as
	 * we want to support multiple formats. Currently, the Kindle csv format
	 * is hard coded.
	 *
	 * @param string $csv
	 * @param string $deviceId
	 * @return Book
	 */
	public function parseCsv(string $csv, string $deviceId): Book
	{
		$titleCell = [1, 0];
		$noteRowStart = 8;
		$noteTypeCol = 0;
		$positionCol = 1;
		$noteCol = 3;

		$rows = explode(PHP_EOL, $csv);
		$rows = array_map(function ($r) {
			return str_getcsv($r);

		}, $rows);

		$title = $rows[$titleCell[0]][$titleCell[1]];
		$book = new Book();
		$book->setDeviceId($deviceId);
		$book->setTitle($title);
		$book->setUserId($this->currentUserId);

		$noteRows = array_slice($rows, $noteRowStart);
		$notes = [];

		foreach ($noteRows as $row) {
			$note = new Note();
			$content = $row[$noteCol] ?? '';
			$note->setContent($content);
			$note->setType((string)$row[$noteTypeCol] ?? '');
			$note->setLocation((string)$row[$positionCol] ?? '');

			if ($note->getContent()) {
				$notes[] = $note;
			}
		}
		$book->setNotes($notes);
		return $book;
	}
}
