<html>
<head>
<title>Install Lucid Tea</title>
</head>

<body style="text-align: center">
<?php
/**********************************************************************************
* install.php                                                                     *
***********************************************************************************
* Lucid Tea                                                                       *
* =============================================================================== *
* Software Version:           Lucid Tea 0.1                                       *
* Software by:                Qiming Zeng (http://www.28chan.org/)                *
* Copyright 2008 by:          Qiming Zeng (http://www.28chan.org/)                *
* Contact at:                 aworldwithoutsnowflakes@gmail.com                   *
***********************************************************************************
* This program is free software; you can redistribute it and/or modify it under   *
* the terms of the GNU General Public License as published by the Free Software   *
* Foundation; either version 2 of the License, or (at your option) any later      *
* version.                                                                        *
*                                                                                 *
* This program is distributed in the hope that it will be useful, but WITHOUT ANY *
* WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR   *
* A PARTICULAR PURPOSE.  See the GNU General Public License for more details.     *
*                                                                                 *
* You should have received a copy of the GNU General Public License along with    *
* this software at license.txt ; if not, write to the Free Software Foundation,   *
* Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA                  *
**********************************************************************************/
function q($username,$password)
 {
 return md5(crypt(mhash(MHASH_CRC32, md5($password)),md5(crypt(mhash(MHASH_CRC32, md5($username)),md5($password)))).mhash(MHASH_CRC32, crypt(mhash(MHASH_CRC32, $password).md5($username),$username.md5($password))));
 }

if(isset($_POST['db_host']) AND isset($_POST['db_user']) AND isset($_POST['db_password']))
 {
 if($con=@mysql_connect($_POST['db_host'],$_POST['db_user'],$_POST['db_password']))
  {
  if($database_set=@mysql_select_db($_POST['db_name'],$con))
   {
   $tables_setup=(mysql_query("SELECT * FROM `".$_POST['db_prefix']."bbs`") AND mysql_query("SELECT * FROM `".$_POST['db_prefix']."messages`") AND mysql_query("SELECT * FROM `".$_POST['db_prefix']."operators`") AND mysql_query("SELECT * FROM `".$_POST['db_prefix']."threads`"))?1:0;
   if($tables_setup)
    {
    if($operator_set=mysql_num_rows(mysql_query("SELECT * FROM `".$_POST['db_prefix']."operators`")))
     {
     mysql_close($con);
     if(file_exists('settings.php')) require 'settings.php';
     if ($bbs_name) if ($menu_n) if ($index_n) if ($index_posts_n) if ($selected) $settings_set=1;
     }
    }
   }
  else echo 'Cannot select the database.<br />'."\n";
  }
 else echo 'Cannot connect to database.<br />'."\n";
 }

if($database_set)
 {
 if($tables_setup)
  {
  if($operator_set)
   {
   if($settings_set)
    {
    if($_POST['deletion']=='Delete') $deletion_set=1;
    }
   else
    {
    if($_POST['settings']=='Submit')
     {
     $fp=fopen('settings.php','w');
     fwrite($fp,'<?php'."\n");
     fwrite($fp,'/**********************************************************************************
* settings.php                                                                    *
***********************************************************************************
* Lucid Tea                                                                       *
* =============================================================================== *
* Software Version:           Lucid Tea 0.1                                       *
* Software by:                Qiming Zeng (http://www.28chan.org/)                *
* Copyright 2008 by:          Qiming Zeng (http://www.28chan.org/)                *
* Contact at:                 aworldwithoutsnowflakes@gmail.com                   *
***********************************************************************************
* This program is free software; you can redistribute it and/or modify it under   *
* the terms of the GNU General Public License as published by the Free Software   *
* Foundation; either version 2 of the License, or (at your option) any later      *
* version.                                                                        *
*                                                                                 *
* This program is distributed in the hope that it will be useful, but WITHOUT ANY *
* WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR   *
* A PARTICULAR PURPOSE.  See the GNU General Public License for more details.     *
*                                                                                 *
* You should have received a copy of the GNU General Public License along with    *
* this software at license.txt ; if not, write to the Free Software Foundation,   *
* Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA                  *
**********************************************************************************/'."\n");
     fwrite($fp,"\$bbs_name= '".$_POST['bbs_name']."';\n");
     fwrite($fp,"\n");
     fwrite($fp,"\$menu_n= ".($_POST['menu_n']?$_POST['menu_n']:200).";\n");
     fwrite($fp,"\$index_n= ".($_POST['index_n']?$_POST['index_n']:10).";\n");
     fwrite($fp,"\$index_posts_n= ".($_POST['index_posts_n']?$_POST['index_posts_n']:10).";\n");
     fwrite($fp,"\n");
     fwrite($fp,"\$db_host= '".$_POST['db_host']."';\n");
     fwrite($fp,"\$db_name= '".$_POST['db_name']."';\n");
     fwrite($fp,"\$db_user= '".$_POST['db_user']."';\n");
     fwrite($fp,"\$db_password= '".$_POST['db_password']."';\n");
     fwrite($fp,"\$db_prefix= '".$_POST['db_prefix']."';\n");
     fwrite($fp,"\n");
     fwrite($fp,'$selected=mysql_select_db($db_name,mysql_connect($db_host,$db_user,$db_password));'."\n");
     fwrite($fp,'?>');
     fclose($fp);
     $settings_set=1;
     }
    }
   }
  else
   {
   if($_POST['operator']=='Submit')
    {
    mysql_query("INSERT INTO `".$_POST['db_prefix']."operators` (username, password) VALUES ('".$_POST['username']."','".q($_POST['username'],$_POST['password'])."')");
    $operator_set=1;
    }
   }
  }
 else
  {
  if($_POST['database']=='Submit')
   {
   mysql_query('CREATE TABLE `'.$_POST['db_prefix'].'bbs` (id int(10) NOT NULL auto_increment, bbs varchar(255) NOT NULL, title varchar(255) NOT NULL, postname varchar(225) NOT NULL, show_id tinyint(1) NOT NULL, display text NOT NULL, PRIMARY KEY id (id)) ENGINE=MyISAM  DEFAULT CHARSET=utf8');
   mysql_query('CREATE TABLE `'.$_POST['db_prefix'].'messages` (id int(10) NOT NULL auto_increment, `key` int(10) NOT NULL, bbs int(10) NOT NULL, `from` varchar(255) NOT NULL, tripcode varchar(30) NOT NULL, mail varchar(255) NOT NULL, message text NOT NULL, `datetime` datetime NOT NULL, ip char(32) NOT NULL, PRIMARY KEY id (id)) ENGINE=MyISAM  DEFAULT CHARSET=utf8');
   mysql_query('CREATE TABLE `'.$_POST['db_prefix'].'operators` (id int(10) NOT NULL auto_increment, username varchar(255) NOT NULL, password varchar(255) NOT NULL, PRIMARY KEY id (id)) ENGINE=MyISAM  DEFAULT CHARSET=utf8');
   mysql_query('CREATE TABLE `'.$_POST['db_prefix'].'threads` (id int(10) NOT NULL auto_increment, id_first int(10) NOT NULL, bbs varchar(255) NOT NULL, subject varchar(255) NOT NULL, last datetime NOT NULL, PRIMARY KEY id (id)) ENGINE=MyISAM  DEFAULT CHARSET=utf8');
   $tables_setup=1;
   }
  }
 }

if($database_set)
 {
 if($operator_set)
  {
  if($settings_set)
   {
   if($deletion_set)
    {
    if (unlink('install.php'))
     {
     echo 'This file was successfully deleted.'."\n";
     echo 'Click <a href="operate.php">here</a> to start.'."\n";
     }
    }
   else
    {
?>
Successfully installed.<br />
<form action="?" method="post">
<input type="hidden" name="db_host" value="<?php echo $_POST['db_host']; ?>" />
<input type="hidden" name="db_name" value="<?php echo $_POST['db_name']; ?>" />
<input type="hidden" name="db_user" value="<?php echo $_POST['db_user']; ?>" />
<input type="hidden" name="db_password" value="<?php echo $_POST['db_password']; ?>" />
<input type="hidden" name="db_prefix" value="<?php echo $_POST['db_prefix']; ?>" />
Click the following button to delete this file: <input type="submit" name="deletion" value="Delete" />
</form>
<?php
    }
   }
   else
   {
?>
<form action="?" method="post">
<input type="hidden" name="db_host" value="<?php echo $_POST['db_host']; ?>" />
<input type="hidden" name="db_name" value="<?php echo $_POST['db_name']; ?>" />
<input type="hidden" name="db_user" value="<?php echo $_POST['db_user']; ?>" />
<input type="hidden" name="db_password" value="<?php echo $_POST['db_password']; ?>" />
<input type="hidden" name="db_prefix" value="<?php echo $_POST['db_prefix']; ?>" />
Configure the BBS settings:
<table style="margin-left: auto; margin-right: auto; text-align: left" border="1">
<tr><td>BBS Name:</td><td><input type="text" name="bbs_name" /></td></tr>
<tr><td>Number of threads to display on menu:</td><td><input type="text" name="menu_n" value="200" /></td></tr>
<tr><td>Number of threads to display on index page:</td><td><input type="text" name="index_n" value="10" /></td></tr>
<tr><td>Number of posts per thread to display on index page:</td><td><input type="text" name="index_posts_n" value="10" /></td></tr>
<tr><td>&nbsp;</td><td><input type="submit" name="settings" value="Submit" /></td></tr>
</table>
</form>
<?php
   }
  }
 else
  {
?>
<form action="?" method="post">
<input type="hidden" name="db_host" value="<?php echo $_POST['db_host']; ?>" />
<input type="hidden" name="db_name" value="<?php echo $_POST['db_name']; ?>" />
<input type="hidden" name="db_user" value="<?php echo $_POST['db_user']; ?>" />
<input type="hidden" name="db_password" value="<?php echo $_POST['db_password']; ?>" />
<input type="hidden" name="db_prefix" value="<?php echo $_POST['db_prefix']; ?>" />
Choose the username and password for the operator account:
<table style="margin-left: auto; margin-right: auto; text-align: left" border="1">
<tr><td>Database User:</td><td><input type="text" name="username" /></td></tr>
<tr><td>Database Password:</td><td><input type="password" name="password" /></td></tr>
<tr><td>&nbsp;</td><td><input type="submit" name="operator" value="Submit" /></td></tr>
</table>
</form>
<?php
  }
 }
else
 {
?>
<form action="?" method="post">
Give the database information:
<table style="margin-left: auto; margin-right: auto; text-align: left" border="1">
<tr><td>Database Host:</td><td><input type="text" name="db_host" <?php echo ($_POST['db_host']?'value="'.$_POST['db_host'].'" ':''); ?>/></td></tr>
<tr><td>Database Name:</td><td><input type="text" name="db_name" <?php echo ($_POST['db_name']?'value="'.$_POST['db_name'].'" ':''); ?>/></td></tr>
<tr><td>Database User:</td><td><input type="text" name="db_user" <?php echo ($_POST['db_user']?'value="'.$_POST['db_user'].'" ':''); ?>/></td></tr>
<tr><td>Database Password:</td><td><input type="password" name="db_password" <?php echo ($_POST['db_password']?'value="'.$_POST['db_password'].'" ':''); ?>/></td></tr>
<tr><td>Database Prefix:</td><td><input type="text" name="db_prefix" <?php echo ($_POST['db_prefix']?'value="'.$_POST['db_prefix'].'" ':''); ?>/></td></tr>
<tr><td>&nbsp;</td><td><input type="submit" name="database" value="Submit" /></td></tr>
</table>
</form>
<?php
 }
?>
</body>
</html>