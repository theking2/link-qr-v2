<?php declare(strict_types=1);

require "../inc/settings.inc.php";
require "../inc/connect.inc.php";

function show_routine(string $type, string $name) {
  global $db;
  $query = "SHOW CREATE $type `$name`";
  $result = $db->query( $query );
  foreach( $result as $row ) {
    echo "<h1>$name</h1>";
    if( is_null($row["Create $type"]) ) {
      echo "<p>Not found</p>";
      continue;
    }
    $proc   = $row[$type];
    $source = $row["Create $type"];
    $source = preg_replace( "/(DEFINER=`\w*`@`\w*`)/", "/* $1 */", $source );
    echo "<pre>$source</pre>";
    $fh = fopen( "./$type/$name.sql", "w" );
    fwrite( $fh, "DROP $type IF EXISTS `$name`;\n");
    fwrite( $fh, "DELIMITER $$\n" );
    fwrite( $fh, $source );
    fwrite( $fh, "$$\nDELIMITER ;\n" );
    fclose( $fh );
  }
}


foreach( $db->query("show procedure status where db = 'minidwh'") as $row ) {
  $name = $row["Name"];
  show_routine("Procedure", $name);
}


foreach( $db->query("show function status where db = 'minidwh'") as $row ) {
  $name = $row["Name"];
  show_routine("Function", $name);
}