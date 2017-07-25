<?php
$tpldir=TPL_DIR."/".$pl."/";
$tpltpl="/templates/".$pl."/";
$view = new View($tpldir);
require CORE_DIR.'/def_pages.php';
$view->set('tpldir', $tpltpl);
$view->set('title', $cfg['title']);
$view->set('sitename', $cfg['sitename']);
$page=$_GET['page'];
#login
$view->set('cfg_invest', $pay['invest']);
#STATISTIC
$users=$DATABASE->SelectCell("SELECT count(`id`) FROM `users`");
$view->set('stat_users', $users+$stat['users']);
$insys=$DATABASE->SelectCell("SELECT sum(`insys`) FROM `users`");
$view->set('stat_insys', $insys+$stat['insys']);
$outsys=$DATABASE->SelectCell("SELECT sum(`paided`) FROM `users`");
$view->set('stat_outsys', $outsys+$stat['outsys']);

$sd=$cfg['startdate'];
$sd=strtotime($sd);
$alive=time()-$sd;
$days=intval($alive/86400);
$view->set('dayslive', $days);


#qq
#zachislenie
if($pay['plus']=='auto')
{
if($pay['limit']!=0)
	{
		$zapr=" and `percent`<".$pay['limit']." ";
	}
	$t1=time()-3600*$pay['first_days'];
	
	$t2=time()-3600*$pay['next_days'];
	$t1=intval($t1);
	$t2=intval($t2);
	#print $pay['first_days']; print "|"; print $pay['next_days'];
	#exit;
	
	
	
	if($t1>$t2) { $tt=$t1; }else{ $tt=$t2; }
		
		$deps=$DATABASE->Select("SELECT * FROM `deposits` WHERE `fake`!=1".$zapr." and `last`<'".$tt."'");
		foreach($deps as $ColName=>$CellValue)
		{
			$perc=0.01*$_POST['addperc'];
			
			if($CellValue['last']<time()-3600*$pay['first_days'] && $CellValue['last']==$CellValue['date'])
			{
				
				
				$perc=0.01*$pay['first_perc'];
				$nh=3600*$pay['first_days'];
					$sum=$CellValue['summ']*$perc;
								$DATABASE->query("UPDATE `users` SET `balance`=`balance`+'".$sum."',`from_invests`=`from_invests`+'".$sum."' where `id`='".$CellValue['ident']."'");
			$DATABASE->query("INSERT INTO `history` (`ident`,`date`,`text`) values ('".$CellValue['ident']."','".time()."','Начислено ".$pay['first_perc']." % к вашему вкладу')");
			$DATABASE->query("UPDATE `deposits` SET `percent`=`percent`+'".$pay['first_perc']."',`last`=`last`+'".$nh."' WHERE `id`='".$CellValue['id']."'");
			
			}elseif($CellValue['last']<time()-3600*$pay['next_days'])
			{
				$perc=0.01*$pay['next_perc'];
				$nh=3600*$pay['next_days'];
				$sum=$CellValue['summ']*$perc;
								$DATABASE->query("UPDATE `users` SET `balance`=`balance`+'".$sum."',`from_invests`=`from_invests`+'".$sum."' where `id`='".$CellValue['ident']."'");
			$DATABASE->query("INSERT INTO `history` (`ident`,`date`,`text`) values ('".$CellValue['ident']."','".time()."','Начислено ".$pay['next_perc']." % к вашему вкладу')");
			$DATABASE->query("UPDATE `deposits` SET `percent`=`percent`+'".$pay['next_perc']."',`last`=`last`+'".$nh."' WHERE `id`='".$CellValue['id']."'");
			
			}
		}


}

#
if(isset($_POST['login']) && !isset($_SESSION['id']))
{
	$login=$_POST['login'];
	$pass=$_POST['pass'];
	if(!preg_match("/^[a-zA-Z0-9]+$/",$login) OR $login=='')
	{
		$error="Некорректно введено имя пользователя";
	}elseif(!preg_match("/^[a-zA-Z0-9]+$/",$pass) OR $pass=='')
	{
		$error="Некорректно введён пароль";
	}elseif(!$DATABASE->SelectRow("SELECT * FROM `users` WHERE `login`='".$login."' AND `password`='".md5(md5($pass))."'"))
	{	
		$error="Пользователь с указанными данными не зарегистрирован";
	}else{
		$check=$DATABASE->SelectRow("SELECT * FROM `users` WHERE `login`='".$login."' AND `password`='".md5(md5($pass))."'");
		$_SESSION['id']=$check['id'];
		$_SESSION['login']=$check['login'];	
		$good="Авторизация прошла успешно...перенаправляем в аккаунт.<meta http-equiv='Refresh' Content='1; URL=/index.php?page=account'>";
	}
}
if(isset($_SESSION['id']))
{
	$about=$DATABASE->SelectRow("SELECT * FROM `users` WHERE `id`='".$_SESSION['id']."'");
	$view->set('mybal', $about['balance']);
	$view->set('mybalance', number_format($about['balance'],2,'.',' '));
	$view->set('myinsys', number_format($about['insys'],2,'.',' '));
	$view->set('mypaided', number_format($about['paided'],2,'.',' '));
	$view->set('from_refs', number_format($about['from_refs'],2,'.',' '));
	$view->set('from_invests', number_format($about['from_invests'],2,'.',' '));
	$view->set('myinvests', $about['invests']);
	$view->set('mypartner', $about['partner_login']);
	$view->set('myemail', $about['email']);
	$view->set('myfio', $about['fio']);
	$view->set('myskype', $about['skype']);
	$view->set('myperfect', $about['perfect']);
	$view->set('log_form', $view->load('miniacc.tpl'));
}else{
	$view->set('log_form', $view->load('logform.tpl'));
}
if(isset($_GET['ref']))
{
	$chkref=$DATABASE->SelectRow("SELECT * FROM `users` WHERE `login`='".$_GET['ref']."'");
	if($chkref)
	{
		$_SESSION['partner']=$chkref['id'];
		$_SESSION['partner_login']=$_GET['ref'];
	}
}
$orev=$DATABASE->SelectRow("SELECT * FROM `reviews` WHERE `modered`=1 ORDER BY `date` DESC LIMIT 1");
$view->set('lastrevtext', substr($orev['text'],0,120)."...");
$view->set('lastrevdate', date('M-d-Y', $orev['date']));
$view->set('lastrevposter', $orev['login']);
$view->set('lastnewtext', substr($DATABASE->SelectCell("SELECT `text` FROM `news` ORDER BY `date` DESC LIMIT 1"),0,230)."...");
$lastinv=$DATABASE->Select("SELECT * FROM `deposits` ORDER BY `date` DESC LIMIT 10");
foreach($lastinv as $ColName=>$CellValue)
{	
	$li10.="<li><a><img src='".$tpltpl."img/ico/user_foot.gif' width=12 height=12 align=left style='padding:0;margin:0;'>&nbsp;".$CellValue['login']."<span style='float:right;'>".number_format($CellValue['summ'],2,'.',' ')."<img src='".$tpltpl."img/ico/dollar.png' width=12 height=12 align=absmiddle style='padding:0;margin:0;'>&nbsp;</span></a></li>";
}
$lastouts=$DATABASE->Select("SELECT * FROM `paidouts` WHERE `status`=1 ORDER BY `date` DESC LIMIT 10");
foreach($lastouts as $ColName=>$CellValue)
{	
	$lp10.="<li><a><img src='".$tpltpl."img/ico/user_foot.gif' width=12 height=12 align=left style='padding:0;margin:0;'>&nbsp;".$CellValue['login']."<span style='float:right;'>".number_format($CellValue['summ'],2,'.',' ')."<img src='".$tpltpl."img/ico/dollar.png' width=12 height=12 align=absmiddle style='padding:0;margin:0;'>&nbsp;</span></a></li>";
}
$view->set('li10',$li10);
$view->set('lp10',$lp10);
if(isset($_GET['page']))
{
switch($page)
{
	case 'admin': require CORE_DIR.'/admin/index.php'; exit; break;
	case 'faq': $pagename='Помощь';require CORE_DIR.'/pages/faq.php'; break;
	case 'reviews': $pagename='Отзывы участников';require CORE_DIR.'/pages/reviews.php'; break;
	case 'news': $pagename='Новости системы';require CORE_DIR.'/pages/news.php'; break;
	case 'register': $pagename='Регистрация'; require CORE_DIR.'/pages/register.php'; break;
	case 'account': $pagename='Личный кабинет'; require CORE_DIR.'/pages/account.php'; break;
	case 'referals': $pagename='Ваши рефералы'; require CORE_DIR.'/pages/reflist.php'; break;
	case 'deposits': $pagename='Ваши вклады'; require CORE_DIR.'/pages/deposits.php'; break;
	case 'paidouts': $pagename='Ваши выплаты'; require CORE_DIR.'/pages/paidouts.php'; break;
	case 'history': $pagename='История операций'; require CORE_DIR.'/pages/history.php'; break;
	case 'reflinks': $pagename='Рекламные материалы'; require CORE_DIR.'/pages/reflinks.php'; break;
	case 'edit': $pagename='Личные данные'; require CORE_DIR.'/pages/edit.php'; break;
	case 'partners': $pagename='ТОП 10 партнёров'; require CORE_DIR.'/pages/partners.php'; break;
	case 'investors': $pagename='ТОП 10 инвесторов'; require CORE_DIR.'/pages/investors.php'; break;
	case 'addinvest': $pagename='Создание вклада'; require CORE_DIR.'/pages/addinvest.php'; break;
	case 'paidout': $pagename='Заказ выплаты'; require CORE_DIR.'/pages/paidout.php'; break;
	case 'perfect': $pagename='Заказ выплаты'; require CORE_DIR.'/pages/perfect.php'; exit; break;
	case 'deltakey': $pagename='Заказ выплаты'; require CORE_DIR.'/pages/deltakey.php'; exit; break;
	case 'interkassa': $pagename='Заказ выплаты'; require CORE_DIR.'/pages/interkassa.php'; exit; break;
	case 'exit': $pagename='Выход из аккаунта'; unset($_SESSION['login']);unset($_SESSION['id']);
	$good="Приходите ещё...<meta http-equiv='Refresh' Content='1; URL=/index.php'>";
	break;
	default: 
		$content=@file_get_contents(CACHE_DIR.'/'.$page.'.php');
		$main_page=1;
		if($content=='')
		{
			 $pagename='О программе '.$cfg['sitename']; require CORE_DIR.'/pages/first.php';
		}else{
			$opage=$DATABASE->SelectRow("SELECT * FROM `pages` WHERE `name`='".$page."'");
			$pagename=$opage['nazn'];
			}
	break;
	
}; 
}else{

 $pagename='О программе '.$cfg['sitename']; require CORE_DIR.'/pages/first.php';
}$us_pages=array('paidout','addinvest','referals','edit','reflinks','history','deposits','account','paidouts','exit');if(in_array($page,$us_pages) && !isset($_SESSION['id'])){ $error='Для доступа к этой странице необходима авторизация'; $content='';}
if(!empty($error))
{
	$view->set('alert', $error);
	$content=$view->load('error.tpl').$content;
}
if(!empty($good))
{
	$view->set('alert', $good);
	$content=$view->load('good.tpl');
}
if(isset($_SESSION['id']) && !in_array($page,$def_menuacc_pages) && $main_page!=1)
{
$content=$view->load('menuacc.tpl').$content;
}
$view->set('pagename', $pagename);
$view->set('content', $content);
$view->display('index.tpl');

?>
