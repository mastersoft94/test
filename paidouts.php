<?

$refs=$DATABASE->Select("SELECT * FROM `paidouts` WHERE `ident`='".$_SESSION['id']."' ORDER BY `date` DESC LIMIT 10");
#print_r($refs);exit;
if(!$refs)
{
	$tb='<tr><td align=center bgcolor="#e9e9ea" height=30 colspan=4>Выплат нет...</td></tr>';
}else{
	foreach($refs as $ColName=>$CellValue)
	{
		
		if($CellValue['status']==0)
		{
			$status='<span style="color:red;" color=red>В ожидании</span>';
		}else{
			$status='<span style="color:green;" color=green>Проведена</span>';
		}
		$tb.='<tr>
		<td align=center bgcolor="#e9e9ea" height=30><b>$ '.number_format($CellValue['summ'],2,'.',' ').'</b></td>
		
		<td align=center bgcolor="#e9e9ea" height=30><b>'.date('M-d-Y h:i:s A', $CellValue['date']).'</b></td>
		<td align=center bgcolor="#e9e9ea" height=30><b>'.$CellValue['purse'].'</b></td>
		<td align=center bgcolor="#e9e9ea" height=30><b>'.$status.'</b></td>
		</tr>';
	
	
	}



}
	
	$view->set('paidouts', $tb);
$content=$view->load('paidouts.tpl');
?>