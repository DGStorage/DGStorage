<?php
	//Designed for PHP5
	//Thanks for boxfish education's help
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
				$array=file($GLOBALS["DGSTORAGE"]["Name"]."/conf.dgb");
				foreach($array as &$config)
				{
					if(strpos($config,"Version:2")!==False)
					{
						$correctVersion=True;
					}
				}
				if($correctVersion==False)
				{
					return False;
				}
				$array=file($GLOBALS["DGSTORAGE"]["Name"]."/index/index.dgi");
				foreach($array as &$line)
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
		
		
		public function add($key,$content,$prop=array())
		{
			$key=str_replace("\n","",(string)$key);
			$key=urlencode($key);
			$operationCollection='';
			if($key=='')
			{
				return False;
			}
			if($this->array_count($GLOBALS["DGSTORAGE"]["CollectionCache"])==0)
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
			else
			{
				if($GLOBALS["DGSTORAGE"]["LastCollection"]!='')
				{
					$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$GLOBALS["DGSTORAGE"]["LastCollection"].'/index/index.dgi');
						$i=0;
						foreach($collIndex as &$line)
						{
							if($line!='')
							{
								$i++;
							}
						}
						if($i<$GLOBALS["DGSTORAGE"]["SINGLECOLLECTIONLIMIT"])
						{
							$operationCollection=$GLOBALS["DGSTORAGE"]["LastCollection"];
						}
						else
						{
							$operationCollection=$this->findavailablecoll(True);
						}
					fclose($collIndex);
				}
				else
				{
					$operationCollection=$this->findavailablecoll(True);
				}
			}
			$GLOBALS["DGSTORAGE"]["LastCollection"]=$operationCollection;
			$uid='';
			$collIndex=fopen($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$operationCollection.'/index/index.dgi','a');
				$collIndexR=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$operationCollection.'/index/index.dgi');
				$i=0;
				foreach($collIndexR as &$line)
				{
					if($line!='' && $line!="\n")
					{
						$i++;
					}
				}
				$uid=$this->uuid();
				if($i==0)
				{
					fwrite($collIndex,(string)$uid.','.(string)$key);
				}
				else
				{
					fwrite($collIndex,"\n".(string)$uid.','.(string)$key);
				}
				fclose($collIndex);
			$storage=fopen($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$operationCollection.'/'.(string)$uid.'.dgs','a');
				fwrite($storage,(string)$content);
				fclose($storage);
			if($this->array_count($prop)!=0)
			{
				$storageProp=fopen($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$operationCollection.'/'.(string)$uid.'.dgp','a');
					foreach($prop as $prop=>&$propItem)
					{
						$prop=urlencode((string)$prop);
						$propItem=urlencode((string)$propItem);
						fwrite($storageProp,(string)$prop.':'.(string)$propItem."\n");
					}
					fclose($storageProp);
			}
			return $uid;
		}
		
		public function get($key,$limit=-1,$skip=0)
		{
			return $this->finditemviakey($key,$limit,$skip);
		}
		
		public function fetch($limit=5,$skip=0)
		{
			return $this->finditemviakey('$all',$limit,$skip);
		}
		
		public function search($keyword,$cache=False)
		{
			$res=array();
			foreach($GLOBALS["DGSTORAGE"]["CollectionCache"] as &$collection)
			{
				$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/index/index.dgi');
					foreach($collIndex as &$line)
					{
						str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							$storage=file_get_contents($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
								if(strpos($storage,(string)$keyword)!==False)
								{
									array_push($res,$this->finditemviauid($split[0],(string)$collection));
								}
								fclose($storage);
						}
					}
			}
			return $res;
		}
		
		public function put($uid,$content)
		{
			foreach($GLOBALS["DGSTORAGE"]["CollectionCache"] as &$collection)
			{
				$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/index/index.dgi');
					$findStatus=False;
					foreach($collIndex as &$line)
					{
						$line=str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							if($split[0]==$uid)
							{
								$storage=fopen($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgs','w');
									fwrite($storage,$content);
									fclose($storage);
								$findStatus=True;
							}
						}
						if($findStatus==True)
						{
							break;
						}
					}
				if($findStatus==True)
				{
					break;
				}
				else
				{
					return False;
				}
			}
			return True;
		}
		
		public function remove($uid)
		{
			$findStatus=False;
			foreach($GLOBALS["DGSTORAGE"]["CollectionCache"] as &$line)
			{
				$line=str_replace("\n","",$line);
				$itemList=array();
				$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$line.'/index/index.dgi');
					foreach($collIndex as &$row)
					{
						$row=str_replace("\n","",$row);
						$split=explode(",",$row);
						if($split[0]==$uid)
						{
							unlink($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$line.'/'.(string)$uid.'.dgs');
							if(is_file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$line.'/'.(string)$uid.'.dgp'))
							{
								unlink($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$line.'/'.(string)$uid.'.dgp');
							}
							$findStatus=True;
						}
						else
						{
							array_push($itemList,$row);
						}
					}
					if($findStatus==True)
					{
						$collIndex=fopen($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$line.'/index/index.dgi','w');
							$string='';
							foreach($itemList as &$item)
							{
								$string=(string)$string.(string)$item."\n";
							}
							fwrite($collIndex,$string);
							fclose($collIndex);
						$i=0;
						$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$line.'/index/index.dgi');
							foreach($collIndex as &$line)
							{
								$line=str_replace("\n","",$line);
								if($line!='')
								{
									$i++;
								}
							}
						if($i==0)
						{
							$this->removecoll((string)$line);
						}
						break;
					}
					fclose($collIndex);
			}
			if($findStatus==False)
			{
				return False;
			}
			return True;
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
		
		protected function removecoll($coll)
		{
			unlink($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$coll.'/index/index.dgi');
			rmdir($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$coll.'/index');
			rmdir($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$coll);
			unset($GLOBALS["DGSTORAGE"]["CollectionCache"][array_search((string)$coll,$GLOBALS["DGSTORAGE"]["CollectionCache"])]);
			$collCache=array();
			$index=file($GLOBALS["DGSTORAGE"]["Name"].'/index/index.dgi');
				foreach($index as &$line)
				{
					$line=str_replace("\n","",$line);
					if($line!=(string)$coll)
					{
						array_push($collCache,$line);
					}
				}
			$index=fopen($GLOBALS["DGSTORAGE"]["Name"].'/index/index.dgi','w');
				if($this->array_count($collCache)!=0)
				{
					foreach($collCache as &$collection)
					{
						fwrite($index,(string)$collection."\n");
					}
				}
				fclose($index);
			return True;
		}
		
		protected function findavailablecoll($createNewColl=False)
		{
			$searchRange=$GLOBALS["DGSTORAGE"]["SEARCHRANGE"];
			if ($searchRange!='' || $searchRange!=NULL)
			{
				$searchRange=-1-(int)$searchRange;
			}
			else
			{
				$searchRange=0;
			}
			$targetCollection=array_slice($GLOBALS["DGSTORAGE"]["CollectionCache"],$searchRange,NULL,False);
			foreach($targetCollection as &$collection)
			{
				$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/index/index.dgi');
					$i=0;
					foreach($collIndex as &$line)
					{
						if($line!='')
						{
							$i++;
						}
					}
					if($i<$GLOBALS["DGSTORAGE"]["SINGLECOLLECTIONLIMIT"])
					{
						return $collection;
					}
					else
					{
						continue;
					}
			}
			if($createNewColl==True)
			{
				$this->createcoll(((int)$GLOBALS["DGSTORAGE"]["LastCollection"])+1);
				return ((int)$GLOBALS["DGSTORAGE"]["LastCollection"])+1;
			}
			else
			{
				return False;
			}
		}
		
		protected function finditemviakey($key,$limit,$skip)
		{
			$limit=(int)$limit;
			$skip=(int)$skip;
			if($skip<0)
			{
				$skip=0;
			}
			$res=array();
			if($limit==0)
			{
				return $res;
			}
			elseif($limit<0 || $limit==NULL)
			{
				$limit=-1;
			}
			if($key!='$all')
			{
				$key=urlencode((string)$key);
			}
			$s=0;
			$i=0;
			$res=array();
			if($key=='$all')
			{
				foreach($GLOBALS["DGSTORAGE"]["CollectionCache"] as &$collection)
				{
					$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/index/index.dgi');
						foreach($collIndex as &$line)
						{
							if($s>=$skip)
							{
								if($i<=$limit && $limit!=-1)
								{
									$line=str_replace("\n","",$line);
									if($line!='')
									{
										$split=explode(",",$line);
										$storage=fopen($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
											$prop=$this->getprop($split[0],$collection);
											array_push($res,array("uid"=>(string)$split[0],"key"=>(string)$split[1],"content"=>(string)file_get_contents($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs'),"prop"=>$prop));
											fclose($storage);
									}
									$i++;
								}
								elseif($limit==-1)
								{
									$line=str_replace("\n","",$line);
									if(line!='')
									{
										$split=explode(",",$line);
										$storage=fopen($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
											$prop=$this->getprop($split[0],$collection);
											array_push($res,array("uid"=>(string)$split[0],"key"=>(string)$split[1],"content"=>(string)file_get_contents($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs'),"prop"=>$prop));
											fclose($storage);
									}
									$i++;
								}
								else
								{
									break;
								}
							}
							else
							{
								$s++;
							}
						}
				}
			}
			else
			{
				foreach($GLOBALS["DGSTORAGE"]["CollectionCache"] as &$collection)
				{
					$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/index/index.dgi');
						foreach($collIndex as &$line)
						{
							if($s>=$skip)
							{
								if($i<=$limit && $limit!=-1)
								{
									$line=str_replace("\n","",$line);
									if($line!='')
									{
										$split=explode(",",$line);
										if($split[1]==$key)
										{
											$storage=fopen($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
												$prop=$this->getprop($split[0],$collection);
												array_push($res,array("uid"=>(string)$split[0],"key"=>(string)$split[1],"content"=>(string)file_get_contents($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs'),"prop"=>$prop));
												fclose($storage);
										}
									}
									$i++;
								}
								elseif($limit==-1)
								{
									$line=str_replace("\n","",$line);
									if($line!='')
									{
										$split=explode(",",$line);
										if($split[1]==$key)
										{
											$storage=fopen($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
												$prop=$this->getprop($split[0],$collection);
												array_push($res,array("uid"=>(string)$split[0],"key"=>(string)$split[1],"content"=>(string)file_get_contents($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs'),"prop"=>$prop));
												fclose($storage);
										}
									}
									$i++;
								}
								else
								{
									break;
								}
							}
							else
							{
								$s++;
							}
						}
				}
			}
			return $res;
		}
		
		protected function finditemviauid($uid,$coll=NULL)
		{
			$res=array();
			if($coll==NULL)
			{
				foreach($GLOBALS["DGSTORAGE"]["Name"] as &$collection)
				{
					$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/index/index.dgi');
						foreach($collIndex as &$line)
						{
							$line=str_replace("\n","",$line);
							if($line!='')
							{
								$split=explode(",",$line);
								if($split[0]==(string)$uid)
								{
									$storage=file_get_contents($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
										$res["uid"]=(string)$split[0];
										$res["key"]=(string)$split[1];
										$res["content"]=($storage);
										$res["prop"]=$this->getprop($split[0],$collection);
										fclose($storage);
										return $res;
								}
							}
						}
				}
				return $res;
			}
			else
			{
				$collIndex=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$coll.'/index/index.dgi');
					foreach($collIndex as &$line)
					{
						$line=str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							if($split[0]==(string)$uid)
							{
								$storage=file_get_contents($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$coll.'/'.(string)$uid.'.dgs');
									$res["uid"]=(string)$split[0];
									$res["key"]=(string)$split[1];
									$res["content"]=($storage);
									$res["prop"]=$this->getprop($split[0],$colln);
									fclose($storage);
									return $res;
							}
						}
					
					}
					return $res;
			}
		}
		
		protected function getprop($uid,$coll=NULL)
		{
			$res=array();
			if($coll==NULL)
			{
				foreach($GLOBALS["DGSTORAGE"]["CollectionCache"] as &$collection)
				{
					if(is_file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp'))
					{
						$f=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp');
							foreach($f as &$line)
							{
								$line=str_replace("\n","",$line);
								if($line!='')
								{
									$split=explode(":",$line);
									$res[$split[0]]=$split[1];
								}
							return $res;
							}
					}
					else
					{
						return $res;
					}
				}
			}
			else
			{
				if(is_file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$coll.'/'.(string)$uid.'.dgp'))
				{
					$f=file($GLOBALS["DGSTORAGE"]["Name"].'/'.(string)$coll.'/'.(string)$uid.'.dgp');
						foreach($f as &$line)
						{
							$line=str_replace("\n","",$line);
							if($line!='')
							{
								$split=explode(":",$line);
								$res[$split[0]]=$split[1];
							}
						}
						return $res;
					fclose($f);
				}
				else
				{
					return $res;
				}
			}
		}
		
	
	}
	
	/*$a=new DGStorage();
	//$a->create('test');
	$a->select('ddd');
	var_dump($a->get(15));
	*/
?>
