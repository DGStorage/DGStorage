<?php
    function CallShell($action=fetch,$datacluster=db,$value=""){
    //exec("python DGStorage.py fetch textstat 20 0 out.php");
    exec("python DGStorage.py".$action." ".$datacluster." ".$value);
					$res=file_get_contents('out.php');
					$res=explode("\n",$res);
					foreach ($res as &$item){
						$array=str_getcsv($item);
						
					}
    }
?>
