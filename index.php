<?php declare(strict_types=1);

require_once 'config.php';
require_once ROOT . '/inc/utils.inc.php';
require_once ROOT . '/inc/session.inc.php';
use \Kingsoft\Db\Database;
use \Kingsoft\LinkQr\Code;
use \Kingsoft\Utils\Html;

if (array_key_exists('code', $_GET)) {

	$select = Database::getConnection()->prepare('select get_url(:code)');
	try {
		if ($select->execute(['code' => $_GET['code']]) && ($url = $select->fetchColumn())) {
			$select = null;

			header("Cache-Control: no-cache");
			header("Pragma: no-cache");
			header("Location: $url");
			exit();
		} else {
			header("HTTP/1.1 404 Not Found");
			exit('<h1>404 Not Found');
		}
	} catch (PDOException $e) {
		header("HTTP/1.1 500 Internal Server Error");
		exit('<h1>500 Internal Server Error');
	}
}


define( 'BASE_URL', 
	($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']) .
	(($_SERVER['SERVER_PORT'] == 80) ? '' : ':' . $_SERVER['SERVER_PORT']) .
	(($_SERVER['SERVER_PORT'] == 443) ? '' : ':' . $_SERVER['SERVER_PORT']) .
	'/' 
);

if (!array_key_exists('user_id', $_SESSION??[])) {
	header('Location:./logon');
	exit(0);
}

// test for url but ignore our own
if( array_key_exists('url', $_GET) and (false === strpos($_GET['url'], BASE_URL)) ) {
	$url = trim($_GET['url']);
	if (strlen($url) < strlen(BASE_URL) + 8) {
		// don't make longer urls
		$full_url = $url;
	} else {
		// save full_url for name of the svg file
		$full_url = $url;

		$select = Database::getConnection()->prepare('select set_url(:user_id, :url)');
		$select->execute(['url' => $full_url, 'user_id' => $_SESSION['user_id']]);

		//echo $select-> errorInfo()[2];
		if ($code = $select->fetchColumn()) {
			$url = BASE_URL . $code;
		} else {
			$url = SETTINGS['default_url'];
		}
	}
} else {
	// nothing to do get the default
	$url = $full_url = SETTINGS['default_url'];
}
//var_dump($_GET);

?>
<!DOCTYPE html>
<html lang="de">

<head>
	<title>go321</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="./lib/qrcode.js" defer></script>
	<link rel="stylesheet" href="./assets/main.css">
</head>

<body>
	<main>

		<h1>go321</h1>
		<a href="/logon">Abmelden</a>
		<h2>Von dir registrierten Codes</h2>
		<p>Auf URL klicken um zu ändern, zum Bestätigen Eingabe-Taste, Esc. für Abbbrechen</p>
		<table id="code-table">
			<tr>
				<th>Code
				<th>URL
			</tr>
			<?php
			foreach (Code::findAll(where: ['user_id' => $_SESSION['user_id']]) as $code) {
				echo '<tr>';
				echo Html::wrap_tag(
					'td',
					"<a target=\"_blank\" href=\"/$code->code\">$code->code</a>"
				);
				echo Html::wrap_tag('td', $code->url);
			}
			?>
		</table>
		<h2>Erstellen</h2>
		<p>URL eingeben und Farben/Grösse einstellen
		<p>Klick auf QR Code für Download.
		<p>Kürzen mit Enter-Taste. Gekürzt werden nur längere URLs.
		<form id="form-container" method="get">


			<label for="url">URL</label>
			<textarea id="url" name="url"><?= $url ?></textarea>

			<span></span>
			<div>
				<input type=submit value="Kürzen" id="shorten" data-full-url="<?= $full_url ?>"><br />
			</div>

			<label for="bg-color">Hintergrundfarbe</label>
			<input type="color" id="bg-color" value="#8cbf35">

			<label for="color">Farbe</label>
			<input type="color" id="color" value="#000000">

			<label for="size">Grösse</label>
			<input type="range" id="size" min="50" max="500" value="100">

			<span></span>
			<button id="do-qr">OK</button>

			<span>QR-Code</span>
			<div id="container"></div>
			<input id="base-url" type="hidden" value="<?= BASE_URL ?>">
		</form>
		<p><a href="./impressum.html">IMPRESSUM</a></p>

		<script src="./assets/main.js"></script>
	</main>
</body>
<link rel="apple-touch-icon" sizes="57x57" href="/assets/icons/apple-icon-57x57.png">
<link rel="apple-touch-icon" sizes="60x60" href="/assets/icons/apple-icon-60x60.png">
<link rel="apple-touch-icon" sizes="72x72" href="/assets/icons/apple-icon-72x72.png">
<link rel="apple-touch-icon" sizes="76x76" href="/assets/icons/apple-icon-76x76.png">
<link rel="apple-touch-icon" sizes="114x114" href="/assets/icons/apple-icon-114x114.png">
<link rel="apple-touch-icon" sizes="120x120" href="/assets/icons/apple-icon-120x120.png">
<link rel="apple-touch-icon" sizes="144x144" href="/assets/icons/apple-icon-144x144.png">
<link rel="apple-touch-icon" sizes="152x152" href="/assets/icons/apple-icon-152x152.png">
<link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/apple-icon-180x180.png">
<link rel="icon" type="image/png" sizes="192x192"  href="/assets/icons/android-icon-192x192.png">
<link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/assets/icons/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="16x16" href="/assets/icons/favicon-16x16.png">
<link rel="manifest" href="/assets/icons/manifest.json">
<meta name="msapplication-TileColor" content="#49a09d">
<meta name="msapplication-TileImage" content="/assets/icons/ms-icon-144x144.png">
<meta name="theme-color" content="#49a09d">
</html>
