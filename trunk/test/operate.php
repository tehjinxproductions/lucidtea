<?php
/**********************************************************************************
* operate.php                                                                     *
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
require 'settings.php';
function q($username,$password)
 {
 return md5(crypt(mhash(MHASH_CRC32, md5($password)),md5(crypt(mhash(MHASH_CRC32, md5($username)),md5($password)))).mhash(MHASH_CRC32, crypt(mhash(MHASH_CRC32, $password).md5($username),$username.md5($password))));
 }
session_start();
if ($_POST['submit']=='Login')
 {
 $username=mysql_real_escape_string(stripslashes($_POST['username']));
 $password=mysql_real_escape_string(stripslashes(q($_POST['username'],$_POST['password'])));
 if(mysql_num_rows(mysql_query("SELECT * FROM ".$db_prefix."operators WHERE username='".$username."' AND password='".$password."'")))
  {
  $_SESSION['username']=$username;
  $_SESSION['password']=$password; 
  }
 }
if (isset($_GET['logout']) OR !mysql_num_rows(mysql_query("SELECT * FROM ".$db_prefix."operators WHERE username='".$_SESSION['username']."' AND password='".$_SESSION['password']."'")))
 {
 $_SESSION = array();
 if (isset($_COOKIE[session_name()])) setcookie(session_name(), '', time()-42000, '/');
 session_destroy();
 }
?>
<html>
<head>
<title>Operate</title>
</head>

<body>
<?php
if(session_is_registered(username) AND mysql_num_rows(mysql_query("SELECT * FROM ".$db_prefix."operators WHERE username='".$_SESSION['username']."' AND password='".$_SESSION['password']."'")))
 {
 ?>
<a href="?operate=user">User</a> <a href="?operate=bbs">BBS</a> <!--<a href="?operate=moderation">Moderation</a>--> <a href="?logout">Logout</a><br />
<?php
 if($_GET['operate']=='user')
  {
  echo '<div style="text-align: center">';
  if($_POST['user']=='Edit')
   {
   if (mysql_num_rows(mysql_query("SELECT * FROM ".$db_prefix."operators WHERE username='".$_SESSION['username']."' AND password='".mysql_real_escape_string(stripslashes(q($_SESSION['username'],$_POST['currentpassword'])))."'")))
    {
    mysql_query("UPDATE ".$db_prefix."operators SET username='".mysql_real_escape_string(stripslashes($_POST['username']))."' WHERE username='".$_SESSION['username']."' LIMIT 1");
    $_SESSION['username']=mysql_real_escape_string(stripslashes($_POST['username']));
    $_POST['newpassword']=$_POST['newpassword']?$_POST['newpassword']:$_POST['currentpassword'];
    mysql_query("UPDATE ".$db_prefix."operators SET password='".mysql_real_escape_string(stripslashes(q($_SESSION['username'],$_POST['newpassword'])))."' WHERE username='".$_SESSION['username']."'");
    $_SESSION['password']=mysql_real_escape_string(stripslashes(q($_SESSION['username'],$_POST['newpassword'])));
    }
   else echo 'Incorrect Password';
   }
?>
<table width="300" border="0" cellpadding="0" cellspacing="1" style="background-color: #CCCCCC; margin-left: auto; margin-right: auto; text-align: left;">
<tr>
<form name="form1" method="post" action="?operate=user">
<td>
<table width="100%" border="0" cellpadding="3" cellspacing="1" style="background-color: #FFFFFF">
<tr>
<td colspan="3" width="840"><strong>User</strong></td>
</tr>
<tr>
<td width="840">Username</td>
<td width="6">:</td>
<td width="294"><input name="username" type="text" id="username" value="<?php echo $_SESSION['username']; ?>" /></td>
</tr>
<tr>
<td width="840">New Password (optional)</td>
<td>:</td>
<td><input name="newpassword" type="password" id="newpassword" /></td>
</tr>
<tr>
<td width="840">Current Password</td>
<td>:</td>
<td><input name="currentpassword" type="password" id="currentpassword" /></td>
</tr>
<tr>
<td width="840">&nbsp;</td>
<td>&nbsp;</td>
<td><input type="submit" name="user" value="Edit"></td>
</tr>
</table>
</td>
</form>
</tr>
</table>
<?php
  }
 elseif($_GET['operate']=='bbs')
  {
  if($_GET['action']=='configure')
   {
   echo '<br />';
   echo '<div style="text-align: center">';
   $_GET['bbs']=mysql_real_escape_string(stripslashes($_GET['bbs']));
   $bbs=mysql_fetch_array(mysql_query("SELECT * FROM ".$db_prefix."bbs WHERE bbs='".$_GET['bbs']."'"));
   if($_POST['submit']=='Configure' AND $bbs)
    {
    $_POST['title']=mysql_real_escape_string(stripslashes($_POST['title']));
    $_POST['postname']=mysql_real_escape_string(stripslashes($_POST['postname']));
    $_POST['show_id']=sprintf("%d",$_POST['show_id']);
    $_POST['display']=mysql_real_escape_string(stripslashes($_POST['display']));
    if(mysql_query("UPDATE ".$db_prefix."bbs SET title='".$_POST['title']."',postname='".$_POST['postname']."',show_id='".$_POST['show_id']."',display='".$_POST['display']."' WHERE bbs='".$bbs['id']."' LIMIT 1"))
     {
     echo 'Configuration successful<br />'."\n";
     if($_POST['bbs']!=$bbs['bbs'])
      {
      $_POST['bbs']=mysql_real_escape_string(stripslashes($_POST['bbs']));
      if (mysql_query("UPDATE ".$db_prefix."bbs SET bbs='".$_POST['bbs']."' WHERE id='".$bbs['id']."' LIMIT 1"))
       {
       echo 'Successfully changed BBS directory<br />'."\n";
       if (file_exists("../".$_GET['bbs']."/index.html")) unlink("../".$_GET['bbs']."/index.html");
       if (file_exists("../".$_GET['bbs']."/subback.html")) unlink("../".$_GET['bbs']."/subback.html");
       if (file_exists("../".$_GET['bbs']."/.htaccess")) unlink("../".$_GET['bbs']."/.htaccess");
       if (!count(glob("../".$_GET['bbs']."/*"))) rmdir("../".$_GET['bbs']."/");
       if(!file_exists('../'.$_POST['bbs'].'/')) mkdir('../'.$_POST['bbs'].'/',0777);
       if(!file_exists('../'.$_POST['bbs'].'/.htaccess'))
        {
        $fp=fopen('../'.$_POST['bbs'].'/.htaccess','w');
        fwrite($fp,'DirectoryIndex index.html');
        fclose($fp);
        }
       $_GET['bbs']=$_POST['bbs'];
       }
      }
     echo 'Go to BBS: <a href="../'.$_GET['bbs'].'/index.html">'.$_POST['title'].'</a><br />'."\n";
     require 'page_elements.php';
     $fp=fopen("../".$_GET['bbs']."/index.html",'w');
     fwrite($fp,head($bbs_name,$_POST['title']));
     fwrite($fp,index_display($bbs_name,$_POST['title'],$bbs['display']));
     fwrite($fp,index_menu($bbs['id'],$db_prefix,$menu_n,$index_n));
     fwrite($fp,index($bbs['id'],$db_prefix,$index_posts_n,$index_n,$_POST['postname'],$_POST['show_id']));
     fwrite($fp,form($_GET['bbs']));
     fwrite($fp,$foot);
     fclose($fp);

     $fp=fopen("../".$_GET['bbs']."/subback.html",'w');
     fwrite($fp,$head);
     fwrite($fp,index_menu($bbs['id'],$db_prefix));
     fwrite($fp,$foot);
     fclose($fp);
     }
    }
   echo 'BBS Configuration:
<form action="?operate=bbs&action=configure&bbs='.$_GET['bbs'].'" method="post">
<table style="margin-left: auto; margin-right: auto; text-align: left;" border="1">
   ';
   echo '<tr><td>Directory</td><td><input type="text" name="bbs" value="'.$_GET['bbs'].'" /></td></tr>';
   echo '<tr><td>Title</td><td><input type="text" name="title" value="'.$bbs['title'].'" /></td></tr>';
   echo '<tr><td>Posting Name</td><td><input type="text" name="postname" value="'.$bbs['postname'].'" /></td></tr>';
   echo '<tr><td>Show ID</td><td><input type="radio" name="show_id" value="0"'.($bbs['show_id']?'':' checked').' /> No<br /><input type="radio" name="show_id" value="1"'.($bbs['show_id']?' checked':'').' /> Yes</td></tr>';
   echo '<tr><td>Front Display</td><td><textarea name="display">'.$bbs['display'].'</textarea></td></tr>';
   echo '</table>';
   echo '<input type="submit" name="submit" value="Configure" /></form></div>';
   }
  elseif($_GET['action']=='delete')
   {
   $_GET['bbs']=mysql_real_escape_string(stripslashes($_GET['bbs']));
   $bbs=mysql_fetch_array(mysql_query("SELECT * FROM ".$db_prefix."bbs WHERE bbs='".$_GET['bbs']."'"));
   echo '<br />'."\n";
   echo '<div style="text-align: center">';
   if ($_POST['delete']=='Yes')
    {
    if ($deleted=mysql_query("DELETE FROM ".$db_prefix."bbs WHERE id=".$bbs['id']." LIMIT 1"));
     {
     echo $bbs['title'].' (Directory: '.$_GET['bbs'].')'.' successfully deleted<br />'."\n";
     echo '<a href="?operate=bbs">Back</a>'."\n";
     if (file_exists("../".$_GET['bbs']."/index.html")) unlink("../".$_GET['bbs']."/index.html");
     if (file_exists("../".$_GET['bbs']."/subback.html")) unlink("../".$_GET['bbs']."/subback.html");
     if (file_exists("../".$_GET['bbs']."/.htaccess")) unlink("../".$_GET['bbs']."/.htaccess");
     if (!count(glob("../".$_GET['bbs']."/*"))) rmdir("../".$_GET['bbs']."/");
     mysql_query("DELETE FROM ".$db_prefix."messages WHERE bbs=".$bbs['id']);
     mysql_query("DELETE FROM ".$db_prefix."threads WHERE bbs=".$bbs['id']);
     }
    }
   if(!$deleted)
    {
    echo 'Are you sure you want to delete<br />'."\n";
    echo '<a href="../'.$_GET['bbs'].'/index.html">'.$bbs['title'].'</a>?<br />'."\n";
    echo '<form action="?operate=bbs&action=delete&bbs='.$_GET['bbs'].'" method="post">'."\n";
    echo '<input type="submit" name="delete" value="Yes" />'."\n";
    echo '</form>'."\n";
    }
   echo '</div>'."\n";
   }
  elseif($_GET['action']=='create')
   {
   echo '<br />'."\n";
   echo '<div style="text-align: center">';
   if($_POST['submit']=='Create')
    {
    $_POST['bbs']=mysql_real_escape_string(stripslashes($_POST['bbs']));
    $_POST['title']=mysql_real_escape_string(stripslashes($_POST['title']));
    $_POST['postname']=mysql_real_escape_string(stripslashes($_POST['postname']));
    $_POST['show_id']=sprintf("%d",$_POST['show_id']);
    $_POST['display']=mysql_real_escape_string(stripslashes($_POST['display']));
    if(mysql_query("INSERT INTO ".$db_prefix."bbs (bbs,title,postname,show_id,display) VALUES ('".$_POST['bbs']."','".$_POST['title']."','".$_POST['postname']."','".$_POST['show_id']."','".$_POST['display']."')"))
     {
     echo 'Successfully created<br />'."\n";
     echo 'Go to BBS: <a href="../'.$_POST['bbs'].'/index.html">'.$_POST['title'].'</a><br />'."\n";
     if(!file_exists('../'.$_POST['bbs'].'/')) mkdir('../'.$_POST['bbs'].'/',0777);
     if(!file_exists('../'.$_POST['bbs'].'/.htaccess'))
      {
      $fp=fopen('../'.$_POST['bbs'].'/.htaccess','w');
      fwrite($fp,'DirectoryIndex index.html');
      fclose($fp);
      }
     require 'page_elements.php';
     $fp=fopen("../".$_POST['bbs']."/index.html",'w');
     fwrite($fp,head($bbs_name,$_POST['title']));
     fwrite($fp,index_display($bbs_name,$_POST['title'],$bbs['display']));
     fwrite($fp,index_menu($_POST['bbs'],$db_prefix,$menu_n,$index_n));
     fwrite($fp,index($_POST['bbs'],$db_prefix,$index_posts_n,$index_n,$_POST['postname'],$_POST['show_id']));
     fwrite($fp,form($_POST['bbs']));
     fwrite($fp,$foot);
     fclose($fp);

     $fp=fopen("../".$_POST['bbs']."/subback.html",'w');
     fwrite($fp,$head);
     fwrite($fp,index_menu($_POST['bbs'],$db_prefix));
     fwrite($fp,$foot);
     fclose($fp);
     }
    }
   ?>
Create BBS:
<form action="?operate=bbs&action=create" method="post">
<table style="margin-left: auto; margin-right: auto; text-align: left;" border="1">
<tr><td>Directory</td><td><input type="text" name="bbs" /></td></tr>
<tr><td>Title</td><td><input type="text" name="title" /></td></tr>
<tr><td>Posting Name</td><td><input type="text" name="postname" /></td></tr>
<tr><td>Show ID</td><td><input type="radio" name="show_id" value="0" /> No<br /><input type="radio" name="show_id" value="1" /> Yes</td></tr>
<tr><td>Front Display</td><td><textarea name="display"></textarea></td></tr>
</table>
<input type="submit" name="submit" value="Create" /></form></div>
<?php
   }
  else
   {
   $bbslist=mysql_query("SELECT * FROM ".$db_prefix."bbs");
   echo '<br />
<div style="text-align: center">Bulletin Boards:
<table style="margin-left: auto; margin-right: auto; text-align: left;" border="1">
<tr><td>BBS</td><td>Title</td></tr>';
   while ($bbs=mysql_fetch_array($bbslist))
    {
    echo '<tr><td>'.$bbs['bbs'].'</td><td>'.$bbs['title'].'</td><td><a href="?operate=bbs&action=configure&bbs='.$bbs['bbs'].'">Configure</a></td><td><a href="?operate=bbs&action=delete&bbs='.$bbs['bbs'].'">Delete</a></td></tr>
';
    }
   echo '</table>'."\n";
   echo '<a href="?operate=bbs&action=create">Create New</a></div>'."\n";
   }
  }
 elseif($_GET['operate']=='moderation')
  {
  ?>
<a href="?operate=moderation&page=bans">Bans</a> <a href="?operate=moderation&page=posts">Posts</a><br />
  <?
  }
 }
else
 {
 ?>
<div style="text-align: center">
<table width="300" border="0" cellpadding="0" cellspacing="1" style="background-color: #CCCCCC; margin-left: auto; margin-right: auto; text-align: left;">
<tr>
<form name="form1" method="post" action="?">
<td>
<table width="100%" border="0" cellpadding="3" cellspacing="1" style="background-color: #FFFFFF">
<tr>
<td colspan="3"><strong>Login </strong></td>
</tr>
<tr>
<td width="78">Username</td>
<td width="6">:</td>
<td width="294"><input name="username" type="text" id="username"></td>
</tr>
<tr>
<td>Password</td>
<td>:</td>
<td><input name="password" type="password" id="password"></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>&nbsp;</td>
<td><input type="submit" name="submit" value="Login"></td>
</tr>
</table>
</td>
</form>
</tr>
</table>
<? if ($_POST['submit']=='Login') echo 'Wrong username or password'; ?>
</div>
 <?
 }
?>
</body>
</html>