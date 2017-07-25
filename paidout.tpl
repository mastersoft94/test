<center><form method="post" name="spendform">

<table width="655" border="0" cellspacing="0" cellpadding="0">
<tbody><tr>
<td height="39" background="<?=$this->tpldir ?>img/bgtop.jpg" class="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
<tbody><tr>
<td class="top"><div style="padding:5px 0px 5px 15px">Заказ выплаты</div></td>
<td align="right" class="top"><div style="padding:5px 15px 5px 0px">На внутреннем счету $<?=$this->mybalance ?></div></td>
</tr>
</tbody></table>
</td>
</tr>
<tr>
<td align="center" background="<?=$this->tpldir ?>img/bgbot.jpg" style="background-position:bottom"><div style="padding:15px 15px 25px 15px"><table width="93%" border="0" cellspacing="0" cellpadding="0">
<tbody><tr>


</tr>
</tbody></table><br>
Сумма выплаты<br>
<br>
<input type="text" name="amount" value="<?=$this->mybal ?>" class="inpts-dep"></div></td>
</tr>
</tbody></table>
<br>
<br><center><a class='button' onClick='document.spendform.submit();'>Заказать выплату</a>
<br>
<br>
<br>
</center></form><br>
</center>