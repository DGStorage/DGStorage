<?php
	function DGContent($action=fetch,$datacluster=db,$value="",$outfile="out.php"){
		exec("python DGStorage.py".$action." ".$datacluster." ".$value);
		$res=file_get_contents($outfile);
		$res=explode("\n",$res);
		foreach ($res as &$item){
			$array=explode(",",$item,3);
			return urldecode($array[1]);
	}
    function DGProp($action=fetch,$datacluster=db,$value="",$outfile="out.php"){
				exec("python DGStorage.py".$action." ".$datacluster." ".$value);
				$res=file_get_contents($outfile);
				$res=explode("\n",$res);
				foreach ($res as &$item){
					$array=explode(",",$item,3);
					$array[2]=str_replace("'",'"',$array[2]);
					$array[2]=str_replace('"{','{',$array[2]);
					$array[2]=str_replace('}"','}',$array[2]);
					return json_decode($array[2]);
	}}
?>
