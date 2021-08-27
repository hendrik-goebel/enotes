<?php

return [
    'routes' => [
	   ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
	   ['name' => 'page#test', 'url' => '/test', 'verb' => 'GET'],
	   ['name' => 'note#list', 'url' => '/note', 'verb' => 'GET'],
	   ['name' => 'settings#fetchNotes', 'url' => '/fetch', 'verb' => 'GET'],
	   ['name' => 'settings#get', 'url' => '/settings', 'verb' => 'GET'],
	   ['name' => 'settings#update', 'url' => '/settings', 'verb' => 'PUT'],
    ]
];
