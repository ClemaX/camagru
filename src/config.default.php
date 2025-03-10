<?php

return [
	'EXTERNAL_URL' => 'http://camagru.localhost:8080',
	'ADMIN_EMAIL' => 'admin@camagru.localhost',
	'DATABASE_DSN' => 'pgsql:host=db;dbname=camagru;port=5432',
	'DATABASE_USERNAME' => 'camagru',
	'DATABASE_PASSWORD' => 'camagru',
	'USER_UNLOCK_TOKEN_LIFETIME' => '15 minutes',
	'DEBUG' => 'false',
	'APP_ENV' => 'production',
	'STORAGE_DIRECTORY' => '/var/lib/camagru',
	'STORAGE_EXTERNAL_URL' => 'http://media.camagru.localhost:8080',
	'POST_PICTURE_BUCKET_ID' => 'post.picture',
];
