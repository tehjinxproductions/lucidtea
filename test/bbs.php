<?php
/**********************************************************************************
* bbs.php                                                                         *
***********************************************************************************
* Lucid Tea                                                                       *
* Open-Source Project by Medicine (aworldwithoutsnowflakes@gmail.com              *
* =============================================================================== *
* Software Version:           Lucid Tea 0.1                                       *
* Software by:                Qiming Zeng (http://www.28chan.org/)                *
* Copyright 2008 by:          Qiming Zeng (http://www.28chan.org/)                *
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
require 'page_elements.php';

if(!$_POST['message']) {echo "No message"; exit;}
if(!$_POST['key']&&!$_POST['subject']) {echo "No subject"; exit;}

$_POST['bbs']=stripslashes($_POST['bbs']);
$_POST['bbs']=mysql_real_escape_string($_POST['bbs']);
if($_POST['key']=sprintf("%d",$_POST['key']))
 {
 $reply_thread=mysql_fetch_array(mysql_query("SELECT * FROM ".$db_prefix."threads WHERE id=".$_POST['key']));
 $bbs=mysql_fetch_array(mysql_query("SELECT * FROM ".$db_prefix."bbs WHERE id=".$reply_thread['bbs']));
 }
else $bbs=mysql_fetch_array(mysql_query("SELECT * FROM ".$db_prefix."bbs WHERE bbs='".$_POST['bbs']."'"));
if(!mysql_num_rows(mysql_query("SELECT * FROM ".$db_prefix."bbs WHERE id='".$bbs['id']."'"))) {echo "No valid BBS specified"; exit;}

$_POST['subject']=str_replace("<","&lt;",$_POST['subject']);
$_POST['subject']=str_replace(">","&gt;",$_POST['subject']);
$_POST['subject']=stripslashes($_POST['subject']);
$_POST['subject']=mysql_real_escape_string($_POST['subject']);
if(mysql_num_rows(mysql_query("SELECT * FROM ".$db_prefix."threads WHERE subject='".$_POST['subject']."' AND bbs='".$bbs['id']."'")) && !$_POST['key']) {echo "Error: duplicate subject"; exit;}

if(!$_POST['key'])
 {
 if (!mysql_query("INSERT INTO ".$db_prefix."threads (bbs,subject,last) VALUES ('".$bbs['id']."','".$_POST['subject']."',NOW())"))
  {
  echo "Your thread has not been posted successfully.";
  exit;
  }
 $thread=mysql_fetch_array(mysql_query("SELECT id FROM ".$db_prefix."threads WHERE subject='".$_POST['subject']."' AND bbs='".$bbs['id']."'"));
 $_POST['key']=$thread['id'];
 }

$_POST['from']=str_replace("<","&lt;",$_POST['from']);
$_POST['from']=str_replace(">","&gt;",$_POST['from']);
$_POST['from']=stripslashes($_POST['from']);
$_POST['from']=mysql_real_escape_string($_POST['from']);
if(strstr($_POST['from'],'#'))
 {
 $part = explode('#',$_POST['from'],2);
 $_POST['from'] = $part[0];
 $input = iconv('UTF-8','SHIFT_JIS',$part[1]);
 $input = strtr($input,array('&'=>'&amp;','<'=>'&lt;','>'=>'&gt;','"'=>'&quot;',"'"=>'&#39;'));
 $salt = substr($input."H.",1,2);
 $salt = ereg_replace("[^\.-z]",".",$salt);
 $salt = strtr($salt,":;<=>?@[\\]^_`","ABCDEFGabcdef"); 
 $tripcode = substr(crypt($input,$salt),-10);
 $tripcode = stripslashes($tripcode);
 $tripcode = mysql_real_escape_string($tripcode);
 }

$_POST['mail']=str_replace("<","&lt;",$_POST['mail']);
$_POST['mail']=str_replace(">","&gt;",$_POST['mail']);
$_POST['mail']=stripslashes($_POST['mail']);
$_POST['mail']=mysql_real_escape_string($_POST['mail']);

$_POST['message']=str_replace("<","&lt;",$_POST['message']);
$_POST['message']=str_replace(">","&gt;",$_POST['message']);
while (ereg('&gt;&gt;[0-9]+',$_POST['message'],$quote))
 {
 $_POST['message']=str_replace($quote[0],'<a href="../test/read.php?thread='.$_POST['key'].'&read='.substr($quote[0],8).'">>>'.substr($quote[0],8).'</a>',$_POST['message']);
 }
$_POST['message']=str_replace("
","<br />",$_POST['message']);
$_POST['message']=mysql_real_escape_string($_POST['message']);
$_POST['message']=str_replace("\\r<br />","<br />\n",$_POST['message']);
$_POST['message']=stripslashes($_POST['message']);

$ip=md5(md5(getenv('REMOTE_ADDR').mhash(MHASH_CRC32,getenv('REMOTE_ADDR'))).mhash(MHASH_CRC32,getenv('REMOTE_ADDR').crypt(getenv('REMOTE_ADDR'),getenv('REMOTE_ADDR').md5(getenv('REMOTE_ADDR')))));
$ip=stripslashes($ip);
$ip=mysql_real_escape_string($ip);

if (mysql_query("INSERT INTO ".$db_prefix."messages (`key`,bbs,`from`,tripcode,mail,message,`datetime`,ip) VALUES ('".$_POST['key']."','".$bbs['id']."','".$_POST['from']."','".$tripcode."','".$_POST['mail']."','".$_POST['message']."',NOW(),'".$ip."')"))
 {
 if(!$thread AND ($_POST['mail']!='sage')) mysql_query("UPDATE ".$db_prefix."threads SET last=NOW() WHERE id=".$_POST['key']);
 echo post_redirect($bbs['bbs']);
 }
else
 {echo "Your message has not been posted successfully."; exit;}
if($thread)
 {
 $m=mysql_fetch_array(mysql_query("SELECT id FROM ".$db_prefix."messages WHERE `key`=".$thread['id']));
 mysql_query("UPDATE ".$db_prefix."threads SET id_first=".$m['id']." WHERE id=".$thread['id']);
 }

$fp=fopen("../".$bbs['bbs']."/index.html",'w');
fwrite($fp,head($bbs_name,$bbs['title']));
fwrite($fp,index_display($bbs_name,$bbs['title'],$bbs['display']));
fwrite($fp,index_menu($bbs['id'],$db_prefix,$menu_n,$index_n));
fwrite($fp,index($bbs['id'],$db_prefix,$index_posts_n,$index_n,$bbs['postname'],$bbs['show_id']));
fwrite($fp,form($bbs['bbs']));
fwrite($fp,$foot);
fclose($fp);

$fp=fopen("../".$bbs['bbs']."/subback.html",'w');
fwrite($fp,$head);
fwrite($fp,index_menu($bbs['id'],$db_prefix));
fwrite($fp,$foot);
fclose($fp);
?>