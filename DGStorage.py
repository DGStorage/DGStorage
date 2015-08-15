#!/usr/bin/env python3
__author__='DGideas';
#Release:dreamspark
#Thanks Boxfish Education for support
import os;
import sys;
try:
	os.chdir(os.path.dirname(sys.argv[0]));
except FileNotFoundError:
	pass;
except OSError:
	pass;

class DGStorage:
	def __init__(self):
		self.DGSTORAGE_VERSION='2.0';
		self.DGSTORAGE_CHARSET='utf8';
		self.DGSTORAGE_SINGLECOLLECTIONLIMIT=1024;
		
		self.Location='';
		self.CollectionCache=[];
	
	def create(self,name):
		import codecs;
		import uuid;
		import urllib.parse;
		import os;
		import sys;
		
		name=urllib.parse.quote_plus(str(name));
		try:
			os.chdir(str(name));
		except FileNotFoundError:
			os.mkdir(str(name));
			self.Location=name;
			with codecs.open('conf.dgb','a',self.DGSTORAGE_CHARSET) as conf:
				conf.write(uuid.uuid1()+'\n');
				conf.write('version:'+self.DGSTORAGE_VERSION);
			os.mkdir('index');
			with codecs.open(self.Location+'/index.dgi','a',self.DGSTORAGE_CHARSET) as index:
				pass;
			return True;
		else:
			return False;
	
	def select(self,name):
		import codecs;
		import uuid;
		import urllib.parse;
		import os;
		import sys;
		name=urllib.parse.quote_plus(str(name));
		try:
			os.chdir(str(name));
		except FileNotFoundError:
			return False;
		else:
			self.Location=name;
			with open('conf.dgb') as conf:
				correctVersion=False;
				for line in conf:
					if line.find('version:2')!=-1:
						correctVersion=True;
				if correctVersion==False:
					return False;
			with open(self.Location+'/index.dgi') as index:
				for line in index:
					line=line.replace('\n','');
					if line!='' and line!='\n':
						self.CollectionCache.append(line);
			return True;
	
	def add(self,key,content,prop={}):
		import codecs;
		import uuid;
		import urllib.parse;
		import os;
		import sys;
		
		key.replace('\n','');
		key=urllib.parse.quote_plus(str(key));
		operationCollection=''
		if key=='':
			return False;
		if len(self.CollectionCache)==0:
			if (self.createcoll(0)):
				operationCollection=0;
			else:
				return False;
		else:
			lastCollection='';
			for collection in self.CollectionCache:
				lastCollection=collection;
				with open(self.Location+'/'+str(collection)+'/index/index.dgc') as collIndex:
					i=0;
					for line in collIndex:
						if line!='':
							i+=1;
					if i<self.DGSTORAGE_SINGLECOLLECTIONLIMIT:
						operationCollection=collection;
						break;
					else:
						continue;
			if operationCollection=='':
				self.createcoll(int(lastCollection)+1);
				operationCollection=int(lastCollection)+1;
		uid='';
		with codecs.open(self.Location+'/'+str(operationCollection)+'/index/index.dgc','a','utf8') as collindex:
			collindexR=open(self.Location+'/'+str(operationCollection)+'/index/index.dgc');
			i=0;
			for line in collindexR:
				if line!='' and line!='\n':
					i+=1;
			collindexR.close();
			uid=uuid.uuid1();
			if i==0:
				collindex.write(str(uid));
			else:
				collindex.write('\n'+str(uid));
		with codecs.open(self.Location+'/'+str(operationCollection)+'/'+str(uid)+'.dgs','a','utf8') as storage:
			storage.write(content);
		with codecs.open(self.Location+'/'+str(operationCollection)+'/'+str(uid)+'.dgp','a','utf8') as storageProp:
			for propItem in prop:
				storageProp.write(str(propItem)+'\n');
		return True;
					
	
	#Private
	def clche(self,where=''):
		if where='':
			self.CollectionCache=[];
	
	def createcoll(self,coll):
		try:
			os.mkdir(str(coll));
		except FileExistsError:
			return False;
		else:
			os.mkdir(str(coll)+'/index');
			with codecs.open(str(coll)+'/index/index.dgc','a','utf8') as dgc:
				pass;
			self.CollectionCache.append(coll);
			with open(self.Location+'/index/index.dgi','a') as index:
				index.write(coll+'\n');
			return True;
	
	def removecoll(self,coll):
		