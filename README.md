# link-qr
# Config
Create a file `/config.php` with this content
```php
<?php
define('ROOT', __DIR__ . '/');

// Base URL and Default URL
$base_url = 'http://link-qr.localhost/';
$default_url = 'https://de.wikipedia.org/';

// Database Configuration
$db = [
    'hostname' => 'localhost',
    'database' => 'link_qr',
    'username' => 'link_qr',
    'password' => 'link_qr'
];

// Log Configuration
$log = [
    'name' => 'qr',
    'location' => 'D:/Projekten/logs',
    'level' => 'Info'
];

// Log Rotate Configuration
$logrotate = [
    'cronExpression' => '0 0 * * */6',
    'maxFiles' => 2,
    'minSize' => 120,
    'compress' => false
];

$api = [
    'namespace' => 'Kingsoft\LinkQr',
    'allowedendpoints' => [ 'Code', 'User', 'UserEmail'],
    'allowedmethods' => [ 'GET', 'POST', 'PUT', 'DELETE' ]
];

// Output the configuration as an array (if needed for debugging)
define( 'SETTINGS', [
    'base_url' => $base_url,
    'default_url' => $default_url,
    'db' => $db,
    'api' => $api,
    'log' => $log,
    'logrotate' => $logrotate
]);

```

## discover
 1. Open `/vendor/kingsoft/persist-db/discover.php` to generate the class files.
 2. Copy the files to the root folder `/classes`
 3. Copy the composer.json section to the `composer.json` file and run `composer dump-autoload`
 4. update the User.php with this:

```php
	public function __toString()
	{
	  return sprintf( '%s [%s %s]',
		$this->username, $this-> vorname, $this-> nachname
	  );
	}
  
	/**
	 * Create a new UUID for this user
	 */
	public function createUUID()
	{
	  $this-> __set( 'uuid', base64url_encode(random_bytes(48)) );
	}
	/**
	 * Set the hash for this user's password
	 */
	public function setPasswordHash(string $password): void {
	  // use the setter to mark as dirty
	  $this-> __set('hash', password_hash($password, PASSWORD_ARGON2ID) );
	}
	/**
	 * Check the password against the hash
	 */
	public function checkPassword(?string $password): bool
	{
	  return password_verify($password, $this->hash);
	}
  
```
 5. Logon and register should work now. 

## Tables and views
```sql
CREATE TABLE IF NOT EXISTS `code` (
  `user_id` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `code` char(5) COLLATE ascii_bin NOT NULL,
  `url` varchar(4096) COLLATE utf8mb4_unicode_520_ci DEFAULT NULL,
  `last_used` datetime NOT NULL DEFAULT current_timestamp(),
  `hits` int(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`code`) USING HASH
) ENGINE=Aria DEFAULT CHARSET=latin1 COLLATE=latin1_bin PACK_KEYS=0;

CREATE TABLE `user` (
  `id` int(10) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `vorname` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nachname` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `hash` varchar(255) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=Aria DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
  ADD UNIQUE KEY `ix_user_username` (`username`);

CREATE TABLE `user_email` (
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `uuid` char(64) DEFAULT NULL,
  `confirm_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `register_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=Aria DEFAULT CHARSET=utf8;
ALTER TABLE `user_email`
  ADD PRIMARY KEY (`email`) USING BTREE;


CREATE VIEW `used`  AS
  SELECT count(`code`.`url`) AS `used`, count(0) AS `total`
  FROM `code``code`  ;


CREATE VIEW `vw_user_email` AS
  select 
    `username` AS `username`,
    `email` AS `email`
  from `user_email`
  join (
    select 
      `username`,
      max(`confirm_date`) AS `confirm_date`
    from `user_email`
    group by `username`
  ) `_last` using(`username`, `confirm_date`))

DELIMITER $$
CREATE FUNCTION `get_url` (`c` CHAR(5))
  RETURNS VARCHAR(4096)
  begin
    declare result varchar(4096);
    update code
	    set hits = hits + 1,last_used=current_timestamp()
    where code = c;
    return (
      select url
      from code
      where code = c
    );
 end$$
DELIMITER ;

DELIMITER $$
CREATE FUNCTION `set_url` (`the_user_id` INT, `the_url` VARCHAR(4096))
  RETURNS CHAR(5) CHARSET utf8 DETERMINISTIC
  begin
    declare result char(5);

    select `code` into result from `code`
    where url_md5_l=conv(left(md5(the_url),16),16,10)
      and url_md5_r=conv(right(md5(the_url),16),16,10) limit 1;
    if result is null then
      select `code` into result from `code` where url is null limit 1;
		  update `code` set user_id=the_user_id, url=the_url where `code`=result;
    end if;
    return result;
  end$$
DELIMITER ;

```

##
Watch live on [go321.eu](https://go321.eu) or[go321.ch](https://go321.ch)

[beispiel](http://go321.eu/aaaaa)

![image](https://github.com/theking2/link-qr/assets/1612152/d3e2dce5-ed13-4a95-b5a9-dcd814dcfb66)

or (white on black)

![Screen Shot 2024-02-25 at 21 00 46](https://github.com/theking2/link-qr/assets/1612152/99d0960e-b297-4d82-8b80-1732f771fc34)

