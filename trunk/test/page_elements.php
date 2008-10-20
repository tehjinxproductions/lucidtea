<?php
/**********************************************************************************
* page_elements.php                                                               *
***********************************************************************************
* Lucid Tea                                                                       *
* =============================================================================== *
* Software Version:           Lucid Tea 0.1                                          *
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
function head($bbs_name,$bbs_subject)
 {
 return '<html>
<head>
<title>'.$bbs_subject.'@'.$bbs_name.'</title>
</head>

<body>
';
 }

function index_display($bbs_name,$bbs_subject,$bbs_display)
 {
 return '<table width="100%"><tr><td>
<span style="float: right"><a href="#menu">&#9632;</a><a href="#1">&#9660;</a></span><b>'.$bbs_subject.'@'.$bbs_name.'</b>'.$bbs_display.'
</td></tr></table>';
 }

function index_menu($bbs_id,$db_prefix,$menu_n=0,$max_page_link=0)
 {
 $threads=mysql_query("SELECT * FROM ".$db_prefix."threads WHERE bbs='".$bbs_id."' ORDER BY last DESC");
 $page='<a name="menu"></a><table width="100%"><tr><td>';
 $i=1;
 while ($thread=mysql_fetch_array($threads) AND (($i<=$menu_n)OR!$menu_n)) 
  {
  $page.= '<a href="../test/read.php?thread='.$thread['id'].'&read=l50">'.$i.':';
  if($i<=$max_page_link) $page.= '</a> <a href="#'.$i.'">'; else $page.=' ';
  $page.= $thread['subject'].' ('.mysql_num_rows(mysql_query("SELECT * FROM ".$db_prefix."messages WHERE `key`=".$thread['id'])).')</a> ';
  $i++;
  }
 if ($menu_n) $page.='<div align="right"><a href="subback.html"><b>All Threads</b></a></div>';
 $page.='</td></tr></table>

';
 return $page;
 }

function index($bbs_id,$db_prefix,$index_n,$index_posts_n,$postname,$show_id)
 {
 $threads=mysql_query("SELECT * FROM ".$db_prefix."threads WHERE bbs='".$bbs_id."' ORDER BY last DESC");
 $i=1;
 $max=$index_n;
 while ($thread=mysql_fetch_array($threads) AND ($i<=$index_n))
  {
  $c=1;
  $posts=mysql_query("SELECT *, UNIX_TIMESTAMP(datetime) AS time FROM ".$db_prefix."messages WHERE `key`=".$thread['id']." ORDER BY datetime ASC");
  $posts_n=mysql_num_rows($posts);
  $page.= '<table width="100%"><tr><td><dl><a name="'.$i.'"></a><div style="text-align: right"><a href="#menu">&#9632;</a><a href="#'.(($i-1)?($i-1):$max).'">&#9650;</a><a href="#'.((($i+1)<=$max)?($i+1):1).'">&#9660;</a></div>
<b>&#12304;'.$i++.':'.$posts_n.'&#12305; '.$thread['subject'].'</b>

';
  $first=mysql_fetch_array($posts);
  $page.= '<p><dt>1 Name: '.($first['mail']?('<a href="mailto:'.$first['mail'].'">'):'').(($first['from'] OR $first['tripcode'])?(($first['from']?'<b>'.$first['from'].'</b>':'').($first['tripcode']?'&#9670;'.$first['tripcode']:'')):'<b>'.($postname?$postname:'Anonymous').'</b>').($first['mail']?'</a>':'').' '.date("Y/m/d(l) H:i:s",$first['time']).($show_id?(' ID:'.substr($first['ip'], 0, 9)):'')."
<dd>".$first['message']."</dd></dt></p>

";
  $c=max((1+$posts_n-$index_posts_n),2);
  $posts=mysql_query("SELECT *, UNIX_TIMESTAMP(datetime) AS time FROM ".$db_prefix."messages WHERE `key`=".$thread['id']." ORDER BY datetime ASC LIMIT ".($c-1).",".$index_posts_n);
  while ($post=mysql_fetch_array($posts))
   {
   $page.= '<p><dt>'.$c.' Name: '.($post['mail']?('<a href="mailto:'.$post['mail'].'">'):'').(($post['from'] OR $post['tripcode'])?(($post['from']?'<b>'.$post['from'].'</b>':'').($post['tripcode']?'&#9670;'.$post['tripcode']:'')):'<b>'.($postname?$postname:'Anonymous').'</b>').($post['mail']?'</a>':'').' '.date("Y/m/d(l) H:i:s",$post['time']).($show_id?(' ID:'.substr($post['ip'], 0, 9)):'')."
<dd>".$post['message']."</dd></dt></p>

"; $c++;
   }
  $page.= '<p><dd><form method="post" action="../test/bbs.php">
<input type="hidden" value="'.$thread['id'].'" name="key" />
<input type="submit" value="Submit"> Name: <input type="text" name="from" /> E-mail: <input type="text" name="mail" /><br />
<textarea name="message"></textarea><br />
<a href="../test/read.php?thread='.$thread['id'].'">Read Entire</a> <a href="../test/read.php?thread='.$thread['id'].'&read=l50">Latest 50</a> <a href="../test/read.php?thread='.$thread['id'].'&read=-100">1-100</a> <a href="#menu">Top</a> <a href="index.html">Reload</a>
</form>
</p></dd>
</dl></td></tr></table>
<br />
';
  }
 return $page;
 }

function form($bbs)
 {
 return '<form method="post" action="../test/bbs.php">
<input name="bbs" value="'.$bbs.'" type="hidden" />
Subject: <input name="subject" type="text" /> <input value="Submit" type="submit" /><br />
Name: <input name="from" type="text" /> E-mail: <input name="mail" type="text" /><br />
Message: <textarea name="message"></textarea>
</form>
';
 }

$foot='Powered by Lucid Tea 0.1 | <a href="../test/copyright.html">Lucid Tea &#169; 2008, Qiming Zeng</a>
</body>
</html>';

function post_redirect($dir){return '<html>
<head>
<title>Successful Post</title>
<meta http-equiv="refresh" content="5;url=../'.$dir.'/index.html" />
</head>

<body>
Your message has been posted successfully.<br />
<br />
You will be redirected in 5 seconds, or you can click <a href="../'.$dir.'/index.html">here</a> if you do not want to wait.<br />
<br />
Powered by Lucid Tea 0.1 | <a href="../test/copyright.html">Lucid Tea &#169; 2008, Qiming Zeng</a>
</body>
</html>';}
?>