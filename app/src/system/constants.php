<?php 

	/*Security*/
	define('SECRETE_KEY', 'supersecretkeyyoushouldnotcommittogithub');//to generate unique one thats long and random, unguesable
	
	/* Database Connection */
	define('DB_USERNAME', 'root');
	define('DB_PASSWORD', 'developer@@');
	define('DB_HOST', 'localhost');
	define('DB_NAME', 'ultramed');

	define('DB_PWD', 'online one correct one');
	define('DB_LNAME', 'ultramedhealth_members');
	define('DB_USERL', 'ultramedhealth_dashboard');
	define('emailPD', 'online one has correct');
	/*Data Type*/
	define('BOOLEAN', 	'1');
	define('INTEGER', 	'2');
	define('STRING', 	'3');

	/*Response Codes Codes*/
	define('SUCCESS_RESPONSE', 						200);
	define('CREATED', 								201);
	define('NOT_MODIFIED', 							304);
	define('BAD_REQUEST', 							400);
	define('UNAUTHORISED', 							401);
	define('FORBIDEN', 								403);
	define('NOT_FOUND',								404);
	define('UNPROCESSABLE_ENTITY', 					422);
	define('INTERNAL_SERVER_ERROR', 				500);