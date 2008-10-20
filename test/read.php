<?php
/**********************************************************************************
* read.php                                                                        *
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
require "settings.php"; $_GET['thread']=sprintf("%d",$_GET['thread']);
$thread=mysql_fetch_array(mysql_query("SELECT * FROM ".$db_prefix."threads WHERE id=".$_GET['thread']));
$bbs=mysql_fetch_array(mysql_query("SELECT * FROM ".$db_prefix."bbs WHERE id='".$thread['bbs']."'"));
?>
<html>
<head>
<title><?php echo $thread['subject']; ?></title>
</head>

<body>
<div style="margin-top:1em;"><a href="../"><?php echo $bbs_name; ?></a> <a href="../<?php echo $bbs['bbs']; ?>/index.html">&#9632;Return to bulletin board&#9632;</a> <a href="?thread=<?php echo $_GET['thread']; ?>">Entire</a> <a href="?thread=<?php echo $_GET['thread']; ?>&read=-100">1-</a> <a href="?thread=<?php echo $_GET['thread']; ?>&read=l50">Latest 50</a></div>
<hr style="background-color:#888;color:#888;border-width:0;height:1px;position:relative;top:-.4em;" />
<h2><?php echo $thread['subject']; ?></h2>
<?php
if (!$_GET['thread']) {echo "Error: no thread specified"; exit;}
$i=0;
$posts=mysql_query("SELECT *, UNIX_TIMESTAMP(datetime) AS time FROM ".$db_prefix."messages WHERE `key`='".$_GET['thread']."' ORDER BY datetime");
$max=mysql_num_rows($posts);
if(ereg('l[0-9]*',$_GET['read'],$num))
 {
 $first=mysql_fetch_array($posts);
 echo '<p><dt>1: '.($first['mail']?'<a href="mailto:'.$first['mail'].'">':'').(($first['from'] OR $first['tripcode'])?(($first['from']?'<b>'.$first['from'].'</b>':'').($first['tripcode']?'&#9670;'.$first['tripcode']:'')):'<b>'.($bbs['postname']?$bbs['postname']:'Anonymous').'</b>').($first['mail']?'</a>':'').': '.date("Y/m/d(l) H:i:s",$first['time']).($bbs['show_id']?(' ID:'.substr($first['ip'], 0, 9)):'').'<br />
<dd>'.$first['message']."</dd></dt></p>

";
 $n=substr($num[0],1);
 $n=sprintf("%d",$n?$n:10);
 $i=max(mysql_num_rows($posts)-$n,1);
 $posts=mysql_query("SELECT *, UNIX_TIMESTAMP(datetime) AS time FROM ".$db_prefix."messages WHERE `key`='".$_GET['thread']."' ORDER BY datetime LIMIT ".$i.",".$n);
 }
elseif(strstr($_GET['read'],'-'))
 {
 $first=mysql_fetch_array($posts);
 echo '<p><dt>'.++$i.': '.($first['mail']?'<a href="mailto:'.$first['mail'].'">':'').(($first['from'] OR $first['tripcode'])?(($first['from']?'<b>'.$first['from'].'</b>':'').($first['tripcode']?'&#9670;'.$first['tripcode']:'')):'<b>'.($bbs['postname']?$bbs['postname']:'Anonymous').'</b>').($first['mail']?'</a>':'').': '.date("Y/m/d(l) H:i:s",$first['time']).($bbs['show_id']?(' ID:'.substr($first['ip'], 0, 9)):'').'<br />
<dd>'.$first['message']."</dd></dt></p>

";
 $limits=explode('-',$_GET['read']);
 $first=max(2,eregi_replace('[^0-9]','',$limits[0]));
 if($first<=mysql_num_rows($posts))
  {
  $last=eregi_replace('[^0-9]','',$limits[1]);
  $last=$last?min(mysql_num_rows($posts),$last):mysql_num_rows($posts);
  $posts=mysql_query("SELECT *, UNIX_TIMESTAMP(datetime) AS time FROM ".$db_prefix."messages WHERE `key`='".$_GET['thread']."' ORDER BY datetime LIMIT ".($i=($first-1)).",".max((1+$last-$first),0));
  }
 }
elseif(ereg('[0-9]',$_GET['read']))
 {
 if(($n=eregi_replace('[^0-9]','',$_GET['read']))<=mysql_num_rows($posts)) $posts=mysql_query("SELECT *, UNIX_TIMESTAMP(datetime) AS time FROM ".$db_prefix."messages WHERE `key`='".$_GET['thread']."' ORDER BY datetime LIMIT ".($i=($n-1)).",1");
 }
$prev1=max(1,$i-50);
$prev2=max(1,$i);
while ($post=mysql_fetch_array($posts))
 {
 echo "<p><dt>".++$i.': '.($post['mail']?'<a href="mailto:'.$post['mail'].'">':'').(($post['from'] OR $post['tripcode'])?(($post['from']?'<b>'.$post['from'].'</b>':'').($post['tripcode']?'&#9670;'.$post['tripcode']:'')):'<b>'.($bbs['postname']?$bbs['postname']:'Anonymous').'</b>').($post['mail']?'</a>':'').': '.date("Y/m/d(l) H:i:s",$post['time']).($bbs['show_id']?(' ID:'.substr($post['ip'], 0, 9)):'')."<br />
<dd>".$post['message']."</dd></dt></p>

";
 }
$next1=min($max,$i+1);
$next2=min($max,$i+51);
?>
<hr>
<a href="../<?php echo $bbs['bbs']; ?>/index.html">Return to bulletin board</a> <a href="?thread=<?php echo $_GET['thread']; ?>">Entire</a> <a href="?thread=<?php echo $_GET['thread']; ?>&read=<?php echo $prev1.'-'.$prev2; ?>">Previous 100</a> <a href="?thread=<?php echo $_GET['thread']; ?>&read=<?php echo $next1.'-'.$next2; ?>">Next 100</a> <a href="?thread=<?php echo $_GET['thread']; ?>&read=l50">Latest 50</a>
<form method="post" action="bbs.php">
<input type="hidden" value="<?php echo $_GET['thread']; ?>" name="key" />
<input type="submit" value="Submit"> Name: <input type="text" name="from" /> E-mail: <input type="text" name="mail" /><br />
<textarea name="message"></textarea>
</form>
Powered by Lucid Tea 0.1 | <a href="../test/copyright.html">Lucid Tea &#169; 2008, Qiming Zeng</a>
</body>
</html>