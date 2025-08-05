<?php
$sse = new ServerSentEvents();
$sse->sendFolders();

class ServerSentEvents
{
	function __construct() {
		$this->setHeaders();
		$this->sendPing();
	}

	function __destruct() {
		$this->sendDone();
	}

	/**
	 * Return event data as object
	 *
	 * @param array|object|string $eventData
	 * @return object
	 * @throws \Exception
	 */
	private function eventDataToObject(array|object|string $eventData): object
	{
		switch (true) {
			case is_object($eventData):
				return $eventData;
				break;
			case is_array($eventData):
				return (object) $eventData;
				break;
			case is_string($eventData):
				return (object) [
					'text' => $eventData
				];
				break;
			default:
				throw new Exception('Invalid event data type');
		}
	}

	/**
	 * Send echoed data to the end user's browser
	 *
	 * @return void
	 */
	private function flushStream(): void
	{
		if (ob_get_contents())
		{
			ob_end_flush();
		}

		flush();
	}

	/**
	 * Send closing message: End of EventStream
	 *
	 * @return void
	 */
	private function sendDone(): void
	{
		$this->message('done', 'End of data');
	}

	/**
	 * Set the required headers
	 *
	 * @return void
	 */
	private function setHeaders(): void {
		date_default_timezone_set('Europe/Amsterdam');
		header('X-Accel-Buffering: no');
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache');
	}

	/**
	 * Echo message in EventStream format
	 *
	 * @param string $eventType
	 * @param array|object|string $eventData
	 * @return void
	 */
	public function message(string $eventType, array|object|string $eventData): void
	{
		$eventData = $this->eventDataToObject($eventData);
		$eventData = json_encode($eventData);
		echo
			'event: ', $eventType,
			"\n",
			'data: ', $eventData,
			"\n\n";
		$this->flushStream();
	}

	/**
	 * Scan folder (no subfolders) and return the
	 *   detected folders as EventStream message
	 *
	 * @return void
	 */
	public function sendFolders(): void
	{
		$folders = scandir('.');

		foreach($folders as $folder)
		{
			if (true !== is_dir($folder))
			{
				continue;
			}

			if ($folder === '.' || $folder === '..')
			{
				continue;
			}

			$data = (object) [
				'path' => urlencode($folder),
				'name' => $folder,
			];
			$this->message('list', $data);

			if (connection_aborted())
			{
				break;
			}
		}
	}

	/**
	 * Send PING message over EventStream
	 *
	 * @return void
	 */
	public function sendPing(): void {
		$data = (object) [
			'time' => date(DATE_ISO8601),
		];
		$this->message('ping', $data);
	}
}
