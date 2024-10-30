<?php declare(strict_types=1);

require_once ROOT . '/vendor/autoload.php';
require_once ROOT . '/inc/logger.inc.php';

/**
 * convert bin to url friendly base64
 */
function base64url_encode( $data ){
  return rtrim( strtr( base64_encode( $data ), '+/', '-_'), '=');
}
/**
 * convert url friendly base64 to bin
 */
function base64url_decode( $data ){
  return base64_decode( strtr( $data, '-_', '+/') . str_repeat('=', 3 - ( 3 + strlen( $data )) % 4 ));
}