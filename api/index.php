<?php
define( '_JEXEC', 1 );
define( 'DS', DIRECTORY_SEPARATOR );
define('JPATH_BASE', dirname(dirname(__FILE__) ));

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );
$direct_script_access=TRUE; 

// initialize the application 
$mainframe =& JFactory::getApplication('site');
$mainframe->initialise();

require 'Slim/Slim.php';

require 'Slim/Middleware.php';
//require 'Slim/Extras/Middleware/HttpBasicAuth.php';
require 'Slim/Extras/Middleware/Jsonp.php';

require 'Webapps/Zoo.php';
require 'Config/configuration.php';


\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$APIConfig = new APIConfig();
//$app->add(new Slim\Extras\Middleware\HttpBasicAuth($APIConfig->username,$APIConfig->password));
$app->add(new \Slim\Extras\Middleware\JSONPMiddleware());

$app->get('/', function () {});
//Zoo
$app->get('/zoo/info', 'getZooInfo');
$app->get('/zoo/app/:appalias/:lang/:offset', 'getZooApp');
$app->get('/zoo/frontpage/:appalias/:lang/:offset', 'getZooFrontpage');
$app->get('/zoo/category/:appalias/:id/:lang/:offset/:items_per_page_global/:order_global', 'getZooCategory');
$app->get('/zoo/item/:id/:lang', 'getZooItem');
$app->get('/zoo/comments/:id', 'getZooItemComments');
$app->get('/zoo/calendar/:appalias/:lang', 'getZooAppCalendar');
$app->post('/zoo/comment', 'postZooComment');


$app->response()->header('Content-Type', 'application/json;charset=utf-8');
$app->run();




