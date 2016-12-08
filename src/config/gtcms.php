<?php

return [

	'premium' => false,

	'allowedUserRoles' => [
		'admin'
	],
	'cmsPrefix' => 'admin',
	'throwExceptions' => true,
	'adminAutoLogin' => false,
	'allowDelete' => true,
	'preventRedirectOnSave' => false,
	'maxLoginAttempts' => 5,
	'loginLockoutDuration' => 10,
	'defaultModel' => 'Page',
	'defaultNamespace' => 'App',
	'defaultDateFormat' => "d.m.Y. H:i",
	'siteName' => 'Site Name',
	'showTestAdminLoginInfo' => true,
	'adminEmail' => 'info@site.name',
	'fromEmail' => 'no-reply@site.name',
	'fromPerson' => 'Site Name',
	'contactMessageSubject' => '[Site Name] - New message',
	'ogImage' => false,
	'skin' => 'dark'

];