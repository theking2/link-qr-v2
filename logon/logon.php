<?php declare(strict_types=1);
require_once '../config.php';
require_once ROOT . 'inc/session.inc.php';
require_once ROOT . 'inc/utils.inc.php';

$script = $_POST['script'] ?? $_GET['s'] ?? '';
/**
 * do we have a logon attempt?
 */
if( !array_key_exists( 'username', $_POST ) or strlen( $_POST['username'] ) === 0 ) {
	// no logon attempt
	// redirect to login page
	header( "Location: ./" );
	exit;
}

if( isset ( $_POST['action'] ) ) {

	$user = \Kingsoft\LinkQr\User::find( where: [ 'username' => mb_strtolower( $_POST['username'] ) ] );

	if( $user and $user->checkPassword( $_POST['password'] ) ) {
		// successful logon
		$user->last_login = new \DateTime();
		$user->freeze();
		$_SESSION['user_id']        = $user->id;
		$_SESSION['username']       = $user->username;
		$_SESSION['failed attempt'] = 0;
		header( "Location: /" );

	} else {
		header( 'Location: ./' );
		error_log( '[' . date( 'Y-m-d H:i:s' ) . '] ' . __FILE__ . ':' . __LINE__ . ':Logon error for user ' . $_POST['username'] );
		exit();
	}

} else {
	header( "Location: /logon" );
}
