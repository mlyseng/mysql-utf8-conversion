<?php
# ini_set('memory_limit', '256M');
# ini_set('max_execution_time', 120);
 
# some config data
define('HOST', 'localhost');
define('USER', '');
define('PASS', '');
define('DB', '');
define('FILE', 'utf8data-dump.sql');
 
mysql_connect(HOST, USER, PASS);
mysql_select_db(DB);
 
# retrieves a list of table names from the database
$rs = mysql_query('SHOW TABLES FROM ' . DB);
$content = 'SET foreign_key_checks = 0;';
while ($row = mysql_fetch_row($rs))
{
	# for each table, get its structure
	$table_name = $row[0];
	$content .= "\r\n-- TABLE STRUCTURE OF $table_name--\r\n";
	$table_struct = mysql_query("SHOW CREATE TABLE " . $table_name);
	$table_struct = mysql_fetch_array($table_struct);
 
	# add a DROP IF EXISTS query
	$content .= "DROP TABLE IF EXISTS $table_name; \r\n";
 
	# add a CREATE TABLE query
	# remember, we must replace latin1 charset with utf8
	$content .= str_replace('latin1', 'utf8', $table_struct[1]) . "; \r\n";
 
	# now, the data
	$content .= "\r\n-- DATA OF $table_name--\r\n";
 
	$table_data = mysql_query("SELECT * FROM $table_name");
 
	# if the table is empty, hell with it
	if (mysql_num_rows($table_data) == 0) continue;
 
	$content .= "INSERT INTO $table_name VALUES ";
 
	# populate the data
	$str = '';
	while ($data_row = mysql_fetch_row($table_data))
	{
		$str .= '(';
		foreach ($data_row as $field)
		{
			//$str .= mb_convert_encoding(sprintf("'%s',", addslashes($field)),"UTF-8");
			$str .= mb_convert_encoding(macRomanToIso(sprintf("'%s',", addslashes($field))), "UTF-8");
		}
		$str = rtrim($str, ',');
		$str .= '),';
	}
	$str = rtrim($str, ',');
 
	$content .= "$str; \r\n";
}
 
# write the (formatted) data into the dump file.
$handle = fopen(FILE, 'wb');
fwrite($handle, $content);
fclose($handle);


function macRomanToIso($string)
{
	return strtr($string,"\x80\x81\x82\x83\x84\x85\x86\x87\x88\x89\x8a\x8b\x8c\x8d\x8e\x8f\x90\x91\x92\x93\x94\x95\x96\x97\x98\x99\x9a\x9b\x9c\x9d\x9e\x9f\xa1\xa4\xa6\xa7\xa8\xab\xac\xae\xaf\xb4\xbb\xbc\xbe\xbf\xc0\xc1\xc2\xc7\xc8\xca\xcb\xcc\xd6\xd8\xdb\xe1\xe5\xe6\xe7\xe8\xe9\xea\xeb\xec\xed\xee\xef\xf1\xf2\xf3\xf4\xf8\xfc\xd2\xd3\xd4\xd5","\xc4\xc5\xc7\xc9\xd1\xd6\xdc\xe1\xe0\xe2\xe4\xe3\xe5\xe7\xe9\xe8\xea\xeb\xed\xec\xee\xef\xf1\xf3\xf2\xf4\xf6\xf5\xfa\xf9\xfb\xfc\xb0\xa7\xb6\xdf\xae\xb4\xa8\xc6\xd8\xa5\xaa\xba\xe6\xf8\xbf\xa1\xac\xab\xbb\xa0\xc0\xc3\xf7\xff\xa4\xb7\xc2\xca\xc1\xcb\xc8\xcd\xce\xcf\xcc\xd3\xd4\xd2\xda\xdb\xd9\xaf\xb8\x22\x22\x27\x27");
}