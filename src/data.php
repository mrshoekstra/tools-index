<?php
date_default_timezone_set('Europe/Amsterdam');
header('X-Accel-Buffering: no');
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$data = (object) [
	'time' => date(DATE_ISO8601),
];
message('ping', $data);

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
	message('list', $data);
	flushStream();

	if (connection_aborted())
	{
		break;
	}
}

message('done', 'End of data');

function flushStream(): void
{
	if (ob_get_contents())
	{
		ob_end_flush();
	}

	flush();
}

function message(string $event, string|object $data): void
{
	echo 'event: ', $event,
	     "\n",
	     'data: ', is_object($data) ? json_encode($data) : $data,
	     "\n\n";
	flushStream();
}