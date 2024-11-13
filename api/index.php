<?php declare(strict_types=1);
require_once '../config.php';
require_once ROOT . 'inc/session.inc.php';
require_once ROOT . 'inc/utils.inc.php';

use Kingsoft\Http\{StatusCode, Response};
use Kingsoft\PersistRest\{PersistRest, PersistRequest};

try {
  $request = new PersistRequest(
    [ 'Code' ],
    implode( ',',SETTINGS[ 'api' ][ 'allowedmethods' ]),
    "*",
    2
  );
  $request->setLogger( LOG );
  $api = new PersistRest( $request, LOG );
  $api->handleRequest();
} catch ( Exception $e ) {
  Response::sendError( $e->getMessage(), StatusCode::InternalServerError->value );
}