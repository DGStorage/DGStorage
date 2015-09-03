<?php
	//Designed for PHP5
	//Document:https://github.com/DGideas/DGStorage/wiki
	class DGStorage
	{	
		protected $DGSTORAGE=array();
		
		function __construct()
		{
			$this->DGSTORAGE["VERSION"]='2.2';
			$this->DGSTORAGE["CHARSET"]='utf8';
			$this->DGSTORAGE["SINGLECOLLECTIONLIMIT"]=1024;
			$this->DGSTORAGE["SEARCHRANGE"]=3;
			$this->DGSTORAGE["SEARCHINDEXLIMIT"]=32;
			$this->DGSTORAGE["SEARCHCACHELIMIT"]=32;
			$this->DGSTORAGE["PROPCACHELIMIT"]=32;
			$this->DGSTORAGE["SAFETY"]=True;
			
			$this->DGSTORAGE["Name"]=NULL;
			$this->DGSTORAGE["TimeStamp"]='';
			
			$this->DGSTORAGE["CollectionCache"]=array();
			$this->DGSTORAGE["LastCollection"]=NULL;
			$this->DGSTORAGE["SearchCache"]=array();
			
			chdir(dirname(__FILE__));
			$this->DGSTORAGE["Name"]=dirname (__FILE__);
			ini_set ("max_execution_time",3600);
		}
		
		public function create($name)
		{
			$this->DGSTORAGE["Name"]=(string)$name;
			if($this->DGSTORAGE["SAFETY"]==True)
			{
				$this->DGSTORAGE["Name"]=urlencode($this->DGSTORAGE["Name"]);
			}
			if(is_dir($this->DGSTORAGE["Name"])){
				return False;
			}
			mkdir($this->DGSTORAGE["Name"]);
			chmod($this->DGSTORAGE["Name"]."/",0777);
			$conf=fopen($this->DGSTORAGE["Name"]."/conf.dgb","a");
				fwrite($conf,$this->uuid()."\n");
				fwrite($conf,"Version:2.1");
				fclose($conf);
			mkdir($this->DGSTORAGE["Name"]."/index");
			$index=fopen($this->DGSTORAGE["Name"]."/index/index.dgi","a");
				fclose($index);
			mkdir($this->DGSTORAGE["Name"]."/cache");
			chmod($this->DGSTORAGE["Name"]."/cache",0777);
			mkdir($this->DGSTORAGE["Name"]."/cache/search");
			chmod($this->DGSTORAGE["Name"]."/cache/search",0777);
			mkdir($this->DGSTORAGE["Name"]."/cache/prop");
			chmod($this->DGSTORAGE["Name"]."/cache/prop",0777);
			$this->uptmp();
			return True;
		}
		
		
		public function select($name)
		{
			$this->DGSTORAGE["Name"]=(string)$name;
			if($this->DGSTORAGE["SAFETY"]==True)
			{
				$this->DGSTORAGE["Name"]=urlencode($this->DGSTORAGE["Name"]);
			}
			if(is_dir($this->DGSTORAGE["Name"]))
			{
				$correctVersion=False;
				$array=file($this->DGSTORAGE["Name"]."/conf.dgb");
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
				$array=file($this->DGSTORAGE["Name"]."/index/index.dgi");
				foreach($array as &$line)
				{
					$line=str_replace("\n","",$line);
					if($line!='')
					{
						array_push($this->DGSTORAGE["CollectionCache"],(string)$line);
					}
				}
				$this->DGSTORAGE["TimeStamp"]=file_get_contents($this->DGSTORAGE["Name"].'/cache/time.dgb');
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
			if($this->array_count($this->DGSTORAGE["CollectionCache"])==0)
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
				if($this->DGSTORAGE["LastCollection"]!='')
				{
					$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$this->DGSTORAGE["LastCollection"].'/index/index.dgi');
						$i=0;
						foreach($collIndex as &$line)
						{
							if($line!='')
							{
								$i++;
							}
						}
						if($i<$this->DGSTORAGE["SINGLECOLLECTIONLIMIT"])
						{
							$operationCollection=$this->DGSTORAGE["LastCollection"];
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
			$this->DGSTORAGE["LastCollection"]=$operationCollection;
			$uid='';
			$collIndex=fopen($this->DGSTORAGE["Name"].'/'.(string)$operationCollection.'/index/index.dgi','a');
				$collIndexR=file($this->DGSTORAGE["Name"].'/'.(string)$operationCollection.'/index/index.dgi');
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
			$storage=fopen($this->DGSTORAGE["Name"].'/'.(string)$operationCollection.'/'.(string)$uid.'.dgs','a');
				fwrite($storage,(string)$content);
				fclose($storage);
				chmod($this->DGSTORAGE["Name"].'/'.(string)$operationCollection.'/'.(string)$uid.'.dgs',0777);
			if($this->array_count($prop)!=0)
			{
				$storageProp=fopen($this->DGSTORAGE["Name"].'/'.(string)$operationCollection.'/'.(string)$uid.'.dgp','a');
					foreach($prop as $prop=>&$propItem)
					{
						$prop=urlencode((string)$prop);
						$propItem=urlencode((string)$propItem);
						fwrite($storageProp,(string)$prop.':'.(string)$propItem."\n");
					}
					fclose($storageProp);
					chmod($this->DGSTORAGE["Name"].'/'.(string)$operationCollection.'/'.(string)$uid.'.dgp',0777);
			}
			$this->uptmp();
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
		
		public function uid($uid)
		{
			return $this->finditemviauid($uid);
		}
		
		public function search($keyword,$cache=False)
		{
			$res=array();
			foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
			{
				$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
					foreach($collIndex as &$line)
					{
						str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							$storage=file_get_contents($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
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
		
		public function pervious($uid)
		{
			$pervious='';
			foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
			{
				$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
					foreach($collIndex as &$line)
					{
						$line=str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							if($split[0]==$uid)
							{
								if($pervious!='')
								{
									return $pervious;
								}
								else
								{
									$pop=$this->DGSTORAGE["CollectionCache"];
									$pop=array_pop($pop);
									$lastColl=file($this->DGSTORAGE["Name"].'/'.(string)$pop.'/index/index.dgi');
										$lastUid='';
										foreach($lastColl as &$line)
										{
											$line=str_replace("\n","",$line);
											if(line!='')
											{
												$lastUid=$line;
											}
										}
										$splitRes=explode(",",$line);
										return $splitRes[0];
								}
							}
							else
							{
								$pervious=$split[0];
							}
						}
					}
			}
			return False;
		}
		
		public function following($uid)
		{
			$follow='';
			$find=False;
			foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
			{
				$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
					foreach($collIndex as &$line)
					{
						$line=str_replace("\n","",$line);
						if(line!='')
						{
							$split=explode(",",$line);
							if($split[0]==$uid)
							{
								$find=True;
							}
							else
							{
								if($find==True)
								{
									return $split[0];
								}
							}
						}
					}
			}
			$firstColl=file($this->DGSTORAGE["Name"].'/'.(string)($this->DGSTORAGE["CollectionCache"][0]).'/index/index.dgi');
				foreach($firstColl as &$line)
				{
					if($line!='')
					{
						$split=explode(",",$line);
						return $split[0];
					}
				}
		}
		
		public function sort($propItem,$order="ASC",$limit=5,$skip=0)
		{
			$propItem=(string)$propItem;
			$propItem=urlencode($propItem);
			$sortArray=array();
			$res=array();
			if($skip<0)
			{
				$skip=0;
			}
			if($limit==0)
			{
				return $res;
			}
			elseif($limit<0 || $limit==NULL)
			{
				$limit=-1;
			}
			if(is_file($this->DGSTORAGE["Name"].'/cache/prop/'.$propItem.'_'.$order.'.dgb'))
			{
				$cacheTimeStamp=file_get_contents($this->DGSTORAGE["Name"].'/cache/prop/'.$propItem.'_'.$order.'.dgb');
					if($cacheTimeStamp!=$this->DGSTORAGE["TimeStamp"])
					{
						unlink($this->DGSTORAGE["Name"].'/cache/prop/'.$propItem.'_'.$order.'.dgb');
						unlink($this->DGSTORAGE["Name"].'/cache/prop/'.$propItem.'_'.$order.'.dgc');
						return $this->sort($propItem,$order,$limit,$skip);
					}
				$cacheObject=file($this->DGSTORAGE["Name"].'/cache/prop/'.$propItem.'_'.$order.'.dgc');
					foreach($cacheObject as &$line)
					{
						$line=str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							array_push($res,array("uid"=>$split[0],"propValue"=>$split[1]));
						}
					}
					if($limit==-1)
					{
						return array_slice($res,$skip);
					}
					else
					{
						return array_slice($res,$skip,$limit);
					}
			}
			else
			{
				foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
				{
					$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
						foreach($collIndex as &$line)
						{
							$line=str_replace("\n","",$line);
							if($line!='')
							{
								$split=explode(",",$line);
								$prop=$this->getprop($split[0],$collection);
								if($prop[$propItem]===NULL)
								{
									continue;
								}
								else
								{
									if($order!="WORD")
									{
										$sortArray[$split[0]]=(float)$prop[$propItem];
										
									}
									else
									{
										$sortArray[$split[0]]=(string)$prop[$propItem];
									}
								}
							}
						}
				}
				if($order=="WORD")
				{
					asort($sortArray);
				}
				elseif($order=="ASC")
				{
					asort($sortArray);
				}
				elseif($order=="DESC")
				{
					arsort($sortArray);
				}
				else
				{
					return False;
				}
				foreach($sortArray as $key=>&$element)
				{
					array_push($res,array("uid"=>$key,"propValue"=>$element));
				}
				if(is_file($this->DGSTORAGE["Name"].'/cache/prop/index.dgi'))
				{
					$cacheIndex=file($this->DGSTORAGE["Name"].'/cache/prop/index.dgi');
						$count=0;
						foreach($cacheIndex as &$line)
						{
							$line=str_replace("\n","",$line);
							if($line!='')
							{
								$count++;
							}
						}
						if($count>=$this->DGSTORAGE["PROPCACHELIMIT"])
						{
							if($limit==-1)
							{
								return array_slice($res,$skip);
							}
							else
							{
								return array_slice($res,$skip,$limit);
							}
						}
				}
				$cacheTimeStamp=fopen($this->DGSTORAGE["Name"].'/cache/prop/'.$propItem.'_'.$order.'.dgb','a');
					fwrite($cacheTimeStamp,$this->DGSTORAGE["TimeStamp"]);
					fclose($cacheTimeStamp);
				$cacheObject=fopen($this->DGSTORAGE["Name"].'/cache/prop/'.$propItem.'_'.$order.'.dgc','a');
					foreach($res as &$element)
					{
						fwrite($cacheObject,$element["uid"].','.$element["propValue"]."\n");
					}
					fclose($cacheObject);
				$cacheIndex=fopen($this->DGSTORAGE["Name"].'/cache/prop/index.dgi','a');
					fwrite($cacheIndex,$propItem.'_'.$order."\n");
					fclose($cacheIndex);
				if($limit==-1)
				{
					return array_slice($res,$skip);
				}
				else
				{
					return array_slice($res,$skip,$limit);
				}
			}
		}
		
		public function put($uid,$content)
		{
			foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
			{
				$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
					$findStatus=False;
					foreach($collIndex as &$line)
					{
						$line=str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							if($split[0]==$uid)
							{
								$storage=fopen($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgs','w');
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
					continue;
				}
			}
			if($findStatus==True)
			{
				$this->uptmp();
				return True;
			}
			else
			{
				return False;
			}
		}
		
		public function setprop($uid,$propItem,$propValue)
		{
			$propItem=urlencode((string)$propItem);
			$propValue=urlencode((string)$propValue);
			foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
			{
				$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
					foreach($collIndex as &$line)
					{
						$line=str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							if($split[0]==$uid)
							{
								if(is_file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp'))
								{
									$propList=array();
									$storageProp=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp');
										foreach($storageProp as &$line)
										{
											$line=str_replace("\n","",$line);
											if($line!='')
											{
												$split=explode(":",$line);
												if($split[0]!=$propItem)
												{
													$propList[$split[0]]=$split[1];
												}
											}
										}
										$propList[$propItem]=$propValue;
									$storageProp=fopen($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp','w');
										foreach($propList as $key=>&$propElement)
										{
											fwrite($storageProp,$key.':'.$propElement."\n");
										}
										fclose($storageProp);
									return True;
								}
								else
								{
									$storageProp=fopen($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp','a');
										fwrite($storageProp,$key.':'.$propElement."\n");
										fclose($storageProp);
									return True;
								}
							}
						}
					}
			}
			return False;
		}
		
		public function removeprop($uid,$propItem)
		{
			$propItem=urlencode((string)$propItem);
			foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
			{
				$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
					foreach($collIndex as &$line)
					{
						$line=str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							if($split[0]==$uid)
							{
								if(is_file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp'))
								{
									$propList=array();
									$storageProp=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp');
										foreach($storageProp as &$line)
										{
											$line=str_replace("\n","",$line);
											if($line!='')
											{
												$split=explode(":",$line);
												if($split[0]!=$propItem)
												{
													$propList[$split[0]]=$split[1];
												}
											}
										}
									if($this->array_count($propList)>0)
									{
										$storageProp=fopen($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp','w');
											foreach($propList as $key=>&$propElement)
											{
												fwrite($storageProp,$key.':'.$propElement."\n");
											}
											fclose($storageProp);
										return True;
									}
									else
									{
										unlink($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp');
										return True;
									}
								}
								else
								{
									return False;
								}
							}
						}
					}
			}
			return False;
		}
		
		public function remove($uid)
		{
			$findStatus=False;
			foreach($this->DGSTORAGE["CollectionCache"] as &$line)
			{
				$line=str_replace("\n","",$line);
				$itemList=array();
				$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$line.'/index/index.dgi');
					foreach($collIndex as &$row)
					{
						$row=str_replace("\n","",$row);
						$split=explode(",",$row);
						if($split[0]==$uid)
						{
							unlink($this->DGSTORAGE["Name"].'/'.(string)$line.'/'.(string)$uid.'.dgs');
							if(is_file($this->DGSTORAGE["Name"].'/'.(string)$line.'/'.(string)$uid.'.dgp'))
							{
								unlink($this->DGSTORAGE["Name"].'/'.(string)$line.'/'.(string)$uid.'.dgp');
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
						$collIndex=fopen($this->DGSTORAGE["Name"].'/'.(string)$line.'/index/index.dgi','w');
							$string='';
							foreach($itemList as &$item)
							{
								$string=(string)$string.(string)$item."\n";
							}
							fwrite($collIndex,$string);
							fclose($collIndex);
						$i=0;
						$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$line.'/index/index.dgi');
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
			$this->uptmp();
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
			if(!is_dir($this->DGSTORAGE["Name"]."/".(string)$coll))
			{
				mkdir($this->DGSTORAGE["Name"]."/".(string)$coll);
				chmod($this->DGSTORAGE["Name"]."/".(string)$coll.'/',0777);
				mkdir($this->DGSTORAGE["Name"]."/".(string)$coll."/index");
				chmod($this->DGSTORAGE["Name"]."/".(string)$coll."/index/",0777);
				$dgc=fopen($this->DGSTORAGE["Name"]."/".(string)$coll."/index/index.dgi","a");
					fclose($dgc);
				array_push($this->DGSTORAGE["CollectionCache"],(string)$coll);
				$index=fopen($this->DGSTORAGE["Name"]."/index/index.dgi","a");
					fwrite($index,(string)$coll."\n");
					fclose($index);
				return True;
			}
		}
		
		protected function removecoll($coll)
		{
			unlink($this->DGSTORAGE["Name"].'/'.(string)$coll.'/index/index.dgi');
			rmdir($this->DGSTORAGE["Name"].'/'.(string)$coll.'/index');
			rmdir($this->DGSTORAGE["Name"].'/'.(string)$coll);
			unset($this->DGSTORAGE["CollectionCache"][array_search((string)$coll,$this->DGSTORAGE["CollectionCache"])]);
			$collCache=array();
			$index=file($this->DGSTORAGE["Name"].'/index/index.dgi');
				foreach($index as &$line)
				{
					$line=str_replace("\n","",$line);
					if($line!=(string)$coll)
					{
						array_push($collCache,$line);
					}
				}
			$index=fopen($this->DGSTORAGE["Name"].'/index/index.dgi','w');
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
			$searchRange=$this->DGSTORAGE["SEARCHRANGE"];
			if ($searchRange!='' || $searchRange!=NULL)
			{
				$searchRange=-1-(int)$searchRange;
			}
			else
			{
				$searchRange=0;
			}
			$targetCollection=array_slice($this->DGSTORAGE["CollectionCache"],$searchRange,NULL,False);
			foreach($targetCollection as &$collection)
			{
				$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
					$i=0;
					foreach($collIndex as &$line)
					{
						if($line!='')
						{
							$i++;
						}
					}
					if($i<$this->DGSTORAGE["SINGLECOLLECTIONLIMIT"])
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
				$this->createcoll(((int)$this->DGSTORAGE["LastCollection"])+1);
				return ((int)$this->DGSTORAGE["LastCollection"])+1;
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
				foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
				{
					$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
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
										$storage=fopen($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
											$prop=$this->getprop($split[0],$collection);
											array_push($res,array("uid"=>(string)$split[0],"key"=>(string)$split[1],"content"=>(string)file_get_contents($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs'),"prop"=>$prop));
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
										$storage=fopen($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
											$prop=$this->getprop($split[0],$collection);
											array_push($res,array("uid"=>(string)$split[0],"key"=>(string)$split[1],"content"=>(string)file_get_contents($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs'),"prop"=>$prop));
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
				foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
				{
					$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
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
											$storage=fopen($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
												$prop=$this->getprop($split[0],$collection);
												array_push($res,array("uid"=>(string)$split[0],"key"=>(string)$split[1],"content"=>(string)file_get_contents($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs'),"prop"=>$prop));
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
											$storage=fopen($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
												$prop=$this->getprop($split[0],$collection);
												array_push($res,array("uid"=>(string)$split[0],"key"=>(string)$split[1],"content"=>(string)file_get_contents($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs'),"prop"=>$prop));
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
				foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
				{
					$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/index/index.dgi');
						foreach($collIndex as &$line)
						{
							$line=str_replace("\n","",$line);
							if($line!='')
							{
								$split=explode(",",$line);
								if($split[0]==(string)$uid)
								{
									$storage=file_get_contents($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$split[0].'.dgs');
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
				$collIndex=file($this->DGSTORAGE["Name"].'/'.(string)$coll.'/index/index.dgi');
					foreach($collIndex as &$line)
					{
						$line=str_replace("\n","",$line);
						if($line!='')
						{
							$split=explode(",",$line);
							if($split[0]==(string)$uid)
							{
								$storage=file_get_contents($this->DGSTORAGE["Name"].'/'.(string)$coll.'/'.(string)$uid.'.dgs');
									$res["uid"]=(string)$split[0];
									$res["key"]=(string)$split[1];
									$res["content"]=($storage);
									$res["prop"]=$this->getprop($split[0],$coll);
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
				foreach($this->DGSTORAGE["CollectionCache"] as &$collection)
				{
					if(is_file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp'))
					{
						$f=file($this->DGSTORAGE["Name"].'/'.(string)$collection.'/'.(string)$uid.'.dgp');
							foreach($f as &$line)
							{
								$line=str_replace("\n","",$line);
								if($line!='')
								{
									$split=explode(":",$line);
									$res[urldecode($split[0])]=urldecode($split[1]);
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
				if(is_file($this->DGSTORAGE["Name"].'/'.(string)$coll.'/'.(string)$uid.'.dgp'))
				{
					$f=file($this->DGSTORAGE["Name"].'/'.(string)$coll.'/'.(string)$uid.'.dgp');
						foreach($f as &$line)
						{
							$line=str_replace("\n","",$line);
							if($line!='')
							{
								$split=explode(":",$line);
								$res[urldecode($split[0])]=urldecode($split[1]);
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
		
		protected function uptmp()
		{
			$timeStamp=fopen($this->DGSTORAGE["Name"].'/cache/time.dgb','w');
				$sts=$this->uuid();
				fwrite($timeStamp,$sts);
				$this->DGSTORAGE["TimeStamp"]=$sts;
				fclose($timeStamp);
			return True;
		}
	
	}

?>
