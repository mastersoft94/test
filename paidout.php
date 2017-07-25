<?

require CORE_DIR.'/cfg.php';

require CORE_DIR.'/pm_cfg.php';

$view->set('pm_pay', $pm['pay']);

$content=$view->load('paidout.tpl');

if(isset($_POST['amount']) && $_POST['amount']!=0)

{

	$amount=abs(floatval($_POST['amount']));

	if($amount>$about['balance'])

	{

		$error='Недостаточно средств для заказа выплаты';

	}else{

		$DATABASE->query("INSERT INTO `paidouts` (`ident`,`summ`,`date`,`status`,`purse`,`login`) values ('".$_SESSION['id']."','".$amount."','".time()."','0','".$about['perfect']."','".$about['login']."')");

		$idpay=$DATABASE->SelectCell("SELECT * FROM `paidouts` WHERE `ident`='".$_SESSION['id']."' ORDER BY `date` DESC LIMIT 1");

		$DATABASE->query("UPDATE `users` SET `balance`=`balance`-'".$amount."' WHERE `id`='".$_SESSION['id']."'");

		if($pm['pay']!='auto')

		{	

			$good='Заявка на выплату принята';

		}else{

		

		#######

		

	$fromacc=$pm['id'];

				$passphrase=$pm['pass'];

				$frompay=$pm['out_purse'];;

				$to=$about['perfect'];

				$pm_memo=str_ireplace('{user}',$_SESSION['login'],$pm['memo']);

				$pm_memo=str_replace(' ','+',$pm_memo);

				$pm_memo=iconv('windows-1251','utf-8',$pm_memo);

				$f=fopen('https://perfectmoney.com/acct/confirm.asp?AccountID='.$fromacc.'&PassPhrase='.$passphrase.'&Payer_Account='.$frompay.'&Payee_Account='.$to.'&Amount='.$amount.'&PAY_IN=1&Memo='.$pm_memo.'&PAYMENT_ID='.time(), 'rb');



if($f===false){



   $error='Произошла ошибка при выплате';

   }else{

   $out=array(); $out="";

while(!feof($f)) $out.=fgets($f);



fclose($f);

if(!preg_match_all("/<input name='(.*)' type='hidden' value='(.*)'>/", $out, $result, PREG_SET_ORDER)){

   $error='Произошла ошибка при выплате';

   

}else{



$ar="";

foreach($result as $item){

   $key=$item[1];

   $ar[$key]=$item[2];

}



	if(empty($ar['ERROR']))

	{

		$DATABASE->query("UPDATE `users` SET `paided`=`paided`+'".$amount."' WHERE `id`='".$_SESSION['id']."'");

		$DATABASE->query("UPDATE `paidouts` SET `status`='1' WHERE `id`='".$idpay."'");

		$DATABASE->query("INSERT INTO `history` (`ident`,`date`,`text`) values ('".$_SESSION['id']."','".time()."','Произведена выплата на сумму $ ".$amount."')");

		$good='Выплата произведена!';

		

		

	}else{

		$error='Произошла ошибка при выплате';

	}

	}

	}

	#####

		

		

		

		}

		

	

	}

}





?>
