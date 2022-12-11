<?php
if($_GET){
	$format = $_GET['format'] ?? '';
	$bin = str_pad($_GET['bin'] ?? '', 16, 'x');
	$count = $_GET['count'] ?? 1;
}
else{
	$format = $argv[1] ?? '';
	$count = $argv[2] ?? 1;
}
if(preg_match('#^([\dx]?[0-9x]{15})\|([0-9x]{2})\|((?:[0-9x]{2})?[0-9x]{2})\|([\dx]?[0-9x]{3})$#', $format, $m)){
	$ccs = array();
	for($i=0;$i<$count;$i++){
		$ccs[] = mkcc($m[1]).'|'.mkm($m[2]).'|'.mky($m[3]).'|'.mkcvv($m[4]);
	}
	out(true, $ccs);
}
elseif(preg_match('#^([\dx]?[0-9x]{15})$#', $bin, $m)){
	$ccs = array();
	for($i=0;$i<$count;$i++){
		$ccs[] = mkcc($m[1]).'|'.mkm().'|'.mky().'|'.mkcvv();
	}
	out(true, $ccs);
}
else{
	out(false, '');
}

function out($ok, $data){
	if(isset($_GET['json'])){
		echo json_encode(array('ok' => $ok, 'data' => $data), JSON_PRETTY_PRINT);
	}
	else{
		if(is_array($data)){
			for($i=0;$i<count($data);$i++){
				echo "<input type=\"text\" value=\"{$data[$i]}\" id=\"i{$i}\"><button onclick=\"clipbd('i{$i}')\">copy</button><br>";
			}
			echo '<script>function clipbd(id){var copyText = document.getElementById(id);copyText.select();copyText.setSelectionRange(0, 99999);navigator.clipboard.writeText(copyText.value);}</script>';
		}
		else{
			echo 'error';
		}
	}
	exit;
}
function mkcc($bin){
	$cc = substr($bin, 0, -1);
	while($n = strpos('0'.$cc, "x")){
		$cc[$n-1] = rand(0, 9);
	}
	$calc = 0;
	for($i=0;$i<strlen($cc);$i+=2){
		$t = $cc[$i] * 2;
		$calc += ($t > 9) ? $t - 9 + ($cc[$i+1] ?? 0) : $t + ($cc[$i+1] ?? 0);
	}
	$cc[strlen($cc)] = (10 * (round($calc / 10) + 1) - $calc) % 10;
	return $cc;
}
function mkcvv($cvv = 'xxx'){
	while($n = strpos('0'.$cvv, 'x')){
		$cvv[$n-1] = rand(0, 9);
	}
	return $cvv;
}
function mkm($m = null){
	$m = (strpos('0'.$m, 'x')) ? null : $m;
	return $m ?? str_pad(rand(1, 12), 2, "0", STR_PAD_LEFT);
}
function mky($y = null){
	if(strlen($y) == 4){
		$p =  (strpos('0'.substr($y, 0, 2), 'x') || $y == null) ? substr(date('Y'), 0, 2) : substr($y, 0, 2);
		$s =  (strpos('0'.substr($y, 2, 2), 'x') || $y == null) ? rand(date('y'), date('y')+10) : substr($y, 2, 2);
	}
	else{
		$p = '';
		$s =  (strpos('0'.$y, 'x') || $y == null) ? rand(date('y'), date('y')+10) : $y;
	}
	return $p.$s;
}
?>