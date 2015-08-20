<?php
	//Designed for PHP5
	//Thanks for boxfish education's help
	function DGContent($action="fetch",$datacluster="db",$value="",$outfile="out.php")
	{
		exec("python DGStorage.py".$action." ".$datacluster." ".$value." ".$outfile);
		$res=file_get_contents($outfile);
		$res=explode("\n",$res);
		foreach ($res as &$item)
		{
			$array=explode(",",$item,3);
			return urldecode($array[1]);
		}
	}
	function DGProp($action="fetch",$datacluster="db",$value="",$outfile="out.php")
	{
		exec("python DGStorage.py".$action." ".$datacluster." ".$value." ".$outfile);
		$res=file_get_contents($outfile);
		$res=explode("\n",$res);
		foreach ($res as &$item)
		{
			$array=explode(",",$item,3);
			$array[2]=str_replace("'",'"',$array[2]);
			$array[2]=str_replace('"{','{',$array[2]);
			$array[2]=str_replace('}"','}',$array[2]);
			return json_decode($array[2]);
		}
	}
	
	class DGStorage
	{	
		function __construct()
		{
			$GLOBALS["DGSTORAGE"]=array();
			$GLOBALS["DGSTORAGE"]["VERSION"]='2.1'; // DataCollection Version
			$GLOBALS["DGSTORAGE"]["CHARSET"]='utf8'; // Default Charset
			$GLOBALS["DGSTORAGE"]["SINGLECOLLECTIONLIMIT"]=1024; // Determine every collection can put how many datas
			$GLOBALS["DGSTORAGE"]["SEARCHRANGE"]=3; // Determine when find a avalible collection, how many collection can we find. None stands find all collection.
			$GLOBALS["DGSTORAGE"]["SEARCHINDEXLIMIT"]=64; // Determine DGStorage can storage how many indexs for quick search.
			$GLOBALS["DGSTORAGE"]["SEARCHCACHELIMIT"]=32; // Determine DGStorage can storage how many caches for quick responds.
			$GLOBALS["DGSTORAGE"]["SAFETY"]=True; // Security settings, True not allowed access database out of the exec path.
			
			$GLOBALS["DGSTORAGE"]["Name"]=NULL;
			
			$GLOBALS["DGSTORAGE"]["CollectionCache"]=array();
			$GLOBALS["DGSTORAGE"]["LastCollection"]=NULL;
			$GLOBALS["DGSTORAGE"]["SearchCache"]=array();
			
			chdir(dirname(__FILE__));
			$GLOBALS["DGSTORAGE"]["Name"]=dirname (__FILE__);
			ini_set ("max_execution_time",3600);
		}
		
		public function create($name)
		{
			$GLOBALS["DGSTORAGE"]["Name"]=(string)$name;
			if($GLOBALS["DGSTORAGE"]["SAFETY"]==True)
			{
				$GLOBALS["DGSTORAGE"]["Name"]=urlencode($GLOBALS["DGSTORAGE"]["Name"]);
			}
			if(is_dir($GLOBALS["DGSTORAGE"]["Name"])){
				return False;
			}
			mkdir($GLOBALS["DGSTORAGE"]["Name"]);
			$conf=fopen($GLOBALS["DGSTORAGE"]["Name"]."/conf.dgb","a");
				fwrite($conf,$this->uuid()."\n");
				fwrite($conf,"Version:2.1");
				fclose($conf);
			mkdir($GLOBALS["DGSTORAGE"]["Name"]."/index");
			$index=fopen($GLOBALS["DGSTORAGE"]["Name"]."/index/index.dgi","a");
				fclose($index);
			mkdir($GLOBALS["DGSTORAGE"]["Name"]."/cache");
			mkdir($GLOBALS["DGSTORAGE"]["Name"]."/cache/search");
			mkdir($GLOBALS["DGSTORAGE"]["Name"]."/cache/prop");
			return True;
		}
		
		public function select($name)
		{
			$GLOBALS["DGSTORAGE"]["Name"]=(string)$name;
			if($GLOBALS["DGSTORAGE"]["SAFETY"]==True)
			{
				$GLOBALS["DGSTORAGE"]["Name"]=urlencode($GLOBALS["DGSTORAGE"]["Name"]);
			}
			if(is_dir($GLOBALS["DGSTORAGE"]["Name"]))
			{
				$correctVersion=False;
				foreach(file($GLOBALS["DGSTORAGE"]["Name"]."/conf.dgb") as &$config)
				{
					if(strpos($config,"Version:2")==False)
					{
						return False;
					}
				}
				foreach(file($GLOBALS["DGSTORAGE"]["Name"]."/index/index.dgi") as &$line)
				{
					$line=str_replace("\n","",$line);
					if($line!='')
					{
						array_push($GLOBALS["DGSTORAGE"]["CollectionCache"],(string)$line);
					}
				}
				
			}
			else
			{
				return False;
			}
		}
		
		public function add($key,$content,$prop=NULL)
		{
			$key=str_replace("\n","",$key);
			$key=urlencode($key);
			$operationCollection=NULL;
			if($key=='')
			{
				return False;
			}
			if(array_count($GLOBALS["DGSTORAGE"]["CollectionCache"])==0)
			{
				if($this->createcoll(0))
				{
					$operationCollection=0;
				}
				else
				{
					return False;
				}
			}
		}
		
		protected function uuid(){
			if (function_exists('com_create_guid')){ 
				return com_create_guid();
			}else{
				mt_srand((double)microtime()*10000);
				$charid = strtoupper(md5(uniqid(rand(), true)));
				$hyphen = chr(45);
				$uuid = substr($charid, 0, 8).$hyphen
						.substr($charid, 8, 4).$hyphen
						.substr($charid,12, 4).$hyphen
						.substr($charid,16, 4).$hyphen
						.substr($charid,20,12);
				$uuid=strtolower($uuid);
				return $uuid;
			}
		}
		
		protected function array_count($ary)
		{
			$i=0;
			foreach($ary as &$element)
			{
				$i++;
			}
			return $i;
		}
		
		protected function createcoll($coll)
		{
			if(!is_dir($GLOBALS["DGSTORAGE"]["Name"]."/".(string)$coll))
			{
				mkdir($GLOBALS["DGSTORAGE"]["Name"]."/".(string)$coll);
				mkdir($GLOBALS["DGSTORAGE"]["Name"]."/".(string)$coll."/index");
				$dgc=fopen($GLOBALS["DGSTORAGE"]["Name"]."/".(string)$coll."/index/index.dgi","a");
				fclose($dgc);
				array_push($GLOBALS["DGSTORAGE"]["CollectionCache"],(string)$coll);
				$index=fopen($GLOBALS["DGSTORAGE"]["Name"]."/index/index.dgi","a");
					fwrite($index,(string)$coll."\n");
					fclose($index);
				return True;
			}
		}
		
	}
	$a=new DGStorage();
	$a->create('test');
	$a->select('test');
?>