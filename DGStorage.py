#!/usr/bin/env python3
__author__='DGideas';
#Release:dreamspark
#This is a free software with the help of Boxfish Education.
import os;
import sys;
try:
	os.chdir(os.path.dirname(sys.argv[0]));
except FileNotFoundError:
	pass;
except OSError:
	pass;

class DGStorage:
	def __init__(self,conf={}):
		self.DGSTORAGE_VERSION='2.0';
		self.DGSTORAGE_CHARSET='utf8';
		self.DGSTORAGE_SINGLECOLLECTIONLIMIT=1024;
		self.DGSTORAGE_SEARCHRANGE=3;
		self.DGSTORAGE_SEARCHINDEXLIMIT=128;
				
		self.Location='';
		self.CollectionCache=[];
		self.LastCollection='';
		self.SearchCache=[];

	def create(self,name):
		import codecs;
		import uuid;
		import urllib.parse;
		import os;
		name=urllib.parse.quote_plus(str(name));
		try:
			os.chdir(str(name));
		except FileNotFoundError:
			os.mkdir(str(name));
			self.Location=name;
			with codecs.open(self.Location+'/conf.dgb','a',self.DGSTORAGE_CHARSET) as conf:
				conf.write(str(uuid.uuid1())+'\n');
				conf.write('Version:'+self.DGSTORAGE_VERSION);
			os.mkdir(self.Location+'/index');
			with codecs.open(self.Location+'/index/index.dgi','a',self.DGSTORAGE_CHARSET) as index:
				pass;
			os.mkdir(self.Location+'/cache');
			os.mkdir(self.Location+'/cache/search');
			os.mkdir(self.Location+'/cache/prop');
			return True;
		else:
			return False;
	
	def select(self,name):
		import urllib.parse;
		import os;
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
					if line.find('Version:2')!=-1:
						correctVersion=True;
				if correctVersion==False:
					return False;
			with open('index/index.dgi') as index:
				for line in index:
					line=line.replace('\n','');
					if line!='' and line!='\n':
						self.CollectionCache.append(str(line));
			return len(self.CollectionCache);
	
	def append(self,content):
		import uuid;
		return self.add(str(uuid.uuid1()),content,{"method":"append"});
	
	def add(self,key,content,prop={}):
		import codecs;
		import uuid;
		import urllib.parse;
		key=str(key).replace('\n','');
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
			if self.LastCollection!='':
				with open(str(self.LastCollection)+'/index/index.dgi') as collIndex:
					i=0;
					for line in collIndex:
						if line!='':
							i+=1;
					if i<self.DGSTORAGE_SINGLECOLLECTIONLIMIT:
						operationCollection=self.LastCollection;
					else:
						operationCollection=self.findavailablecoll(True);
			else:
				operationCollection=self.findavailablecoll(True);
		self.LastCollection=operationCollection;
		uid='';
		with codecs.open(str(operationCollection)+'/index/index.dgi','a','utf8') as collIndex:
			collIndexR=open(str(operationCollection)+'/index/index.dgi');
			i=0;
			for line in collIndexR:
				if line!='' and line!='\n':
					i+=1;
			collIndexR.close();
			uid=uuid.uuid1();
			if i==0:
				collIndex.write(str(uid)+','+str(key));
			else:
				collIndex.write('\n'+str(uid)+','+str(key));
		with codecs.open(str(operationCollection)+'/'+str(uid)+'.dgs','a','utf8') as storage:
			storage.write(str(content));
		if len(prop)!=0:
			with codecs.open(str(operationCollection)+'/'+str(uid)+'.dgp','a','utf8') as storageProp:
				for propItem in prop:
					propItem=urllib.parse.quote_plus(str(propItem));
					prop[propItem]=urllib.parse.quote_plus(str(prop[propItem]));
					storageProp.write(str(propItem)+':'+str(prop[propItem])+'\n');
		return uid;
	
	def index(self,key):
		return self.get(key);
	
	def count(self,key):
		return len(self.get(key));
	
	def get(self,key,limit=-1,skip=0):
		return self.finditemviakey(key,limit,skip);
	
	def fetch(self,limit=5,skip=0):
		return self.finditemviakey('$all',limit,skip);
	
	def remove(self,uid):
		import os;
		import codecs;
		with open('index/index.dgi') as index:
			findStatus=False;
			for line in index:
				line=line.replace('\n','');
				itemList=[];
				with open(str(line)+'/index/index.dgi') as collIndex:
					for row in collIndex:
						row=row.replace('\n','');
						split=row.split(',');
						if split[0]==uid:
							os.remove(str(line)+'/'+str(uid)+'.dgs');
							try:
								os.remove(str(line)+'/'+str(uid)+'.dgp');
							except FileNotFoundError:
								pass;
							findStatus=True;
						else:
							itemList.append(row);
				if findStatus==True:
					with codecs.open(str(line)+'/index/index.dgi','w','utf8') as collIndex:
						string=''
						for item in itemList:
							string=str(string)+str(item)+'\n';
						collIndex.write(string);
					i=0;
					with open(str(line)+'/index/index.dgi') as collIndex:
						for line in collIndex:
							line=line.replace('\n','');
							if line!='':
								i+=1;
					if i==0:
						self.removecoll(str(line));
					break;
			if findStatus==False:
				return False;
		return True;
	
	#Private
	def clche(self,where=''):
		if where=='':
			self.CollectionCache=[];
	
	def createcoll(self,coll):
		import codecs;
		import os;
		try:
			os.mkdir(str(coll));
		except FileExistsError:
			return False;
		else:
			os.mkdir(str(coll)+'/index');
			with codecs.open(str(coll)+'/index/index.dgi','a','utf8') as dgc:
				pass;
			self.CollectionCache.append(str(coll));
			with open('index/index.dgi','a') as index:
				index.write(str(coll)+'\n');
			return True;
	
	def removecoll(self,coll):
		import codecs;
		import os;
		os.remove(str(coll)+'/index/index.dgi');
		os.rmdir(str(coll)+'/index');
		os.rmdir(str(coll));
		self.CollectionCache.remove(str(coll));
		collCache=[];
		with open('index/index.dgi') as index:
			for line in index:
				line=str(line.replace('\n',''));
				if line!=str(coll):
					collCache.append(line);
		with codecs.open('index/index.dgi','w','utf8') as index:
			if len(collCache)!=0:
				for collection in collCache:
					index.write(str(collection)+'\n');
		return True;
	
	def findavailablecoll(self,createNewColl=False):
		searchRange=self.DGSTORAGE_SEARCHRANGE;
		if searchRange!='' or searchRange!=None:
			searchRange=-1-int(searchRange);
		for collection in self.CollectionCache[:searchRange:-1]:
			with open(str(collection)+'/index/index.dgi') as collIndex:
				i=0;
				for line in collIndex:
					if line!='':
						i+=1;
				if i<self.DGSTORAGE_SINGLECOLLECTIONLIMIT:
					return collection;
					break;
				else:
					continue;
		if createNewColl==True:
			self.createcoll(int(self.LastCollection)+1);
			return int(self.LastCollection)+1;
		else:
			return False;
	
	def finditemviakey(self,key,limit,skip):
		limit=int(limit);
		skip=int(skip);
		if skip<0:
			skip=0;
		res=[];
		if limit==0:
			return res;
		elif limit<0 or limit==None:
			limit=-1;
		if key!='$all':
			import urllib.parse;
			key=str(urllib.parse.quote_plus(str(key)));
		s=0;
		i=0;
		res=[];
		if key=='$all':
			for collection in self.CollectionCache:
				with open(str(collection)+'/index/index.dgi') as collIndex:
					for line in collIndex:
						if s>=skip:
							if i<=limit and limit!=-1:
								line=line.replace('\n','');
								if line!='':
									split=line.split(',');
									with open(str(collection)+'/'+str(split[0])+'.dgs') as storage:
										prop=self.getprop(split[0],collection);
										res.append({"uid":str(split[0]),"content":str(storage.read()),"prop":prop});
								i+=1;
							elif limit==-1:
								line=line.replace('\n','');
								if line!='':
									split=line.split(',');
									with open(str(collection)+'/'+str(split[0])+'.dgs') as storage:
										prop=self.getprop(split[0],collection);
										res.append({"uid":str(split[0]),"content":str(storage.read()),"prop":prop});
								i+=1;
							else:
								break;
						else:
							s+=1;
		else:
			for collection in self.CollectionCache:
				with open(str(collection)+'/index/index.dgi') as collIndex:
					for line in collIndex:
						if s>=skip:
							if i<=limit and limit!=-1:
								line=line.replace('\n','');
								if line!='':
									split=line.split(',');
									if split[1]==key:
										with open(str(collection)+'/'+str(split[0])+'.dgs') as storage:
											prop=self.getprop(split[0],collection);
											res.append({"uid":str(split[0]),"content":str(storage.read()),"prop":prop});
								i+=1;
							elif limit==-1:
								line=line.replace('\n','');
								if line!='':
									split=line.split(',');
									if split[1]==key:
										with open(str(collection)+'/'+str(split[0])+'.dgs') as storage:
											prop=self.getprop(split[0],collection);
											res.append({"uid":str(split[0]),"content":str(storage.read()),"prop":prop});
								i+=1;
							else:
								break;
						else:
							s+=1;
		return res;
	
	def getprop(self,uid,coll=None):
		import codecs;
		import urllib.parse;
		res={};
		if coll==None:
			for collection in CollectionCache:
				try:
					open(str(collection)+'/'+str(uid)+'.dgp');
				except FileNotFoundError:
					return res;
				else:
					f=codecs.open(str(collection)+'/'+str(uid)+'.dgp');
					for line in f:
						line=line.replace('\n','');
						if line!='':
							split=line.split(':');
							split[0]=urllib.parse.unquote_plus(str(split[0]));
							split[1]=urllib.parse.unquote_plus(str(split[1]));
							res[split[0]]=split[1];
					return res;
		else:
			try:
				open(str(coll)+'/'+str(uid)+'.dgp');
			except FileNotFoundError:
				return res;
			else:
				f=codecs.open(str(coll)+'/'+str(uid)+'.dgp','r','utf8');
				for line in f:
					line=line.replace('\n','');
					if line!='':
						split=line.split(':');
						split[0]=urllib.parse.unquote_plus(str(split[0]));
						split[1]=urllib.parse.unquote_plus(str(split[1]));
						res[split[0]]=split[1];
				return res;
	
class DGStorageShell(DGStorage):
	def shellAdd(self,key,inFileLocation):
		import codecs;
		with codecs.open(inFileLocation,'r','utf8') as f:
			string='';
			for line in f:
				line=line.replace('\n','');
				string=str(string)+str(line);
				self.add(key,string);
	
	def shellGet(self,key,outFileLocation):
		import codecs;
		import urllib.parse;
		res=self.get(key);
		f=codecs.open(outFileLocation,'w','utf8');
		string='';
		for item in res:
			item['content']=urllib.parse.quote_plus(item['content']);
			string=str(string)+str(item['uid'])+','+str(item['content'])+','+str(item['prop'])+'\n';
		f.write(string);
		f.close();
	
	def shellFetch(self,limit,skip,outFileLocation):
		import codecs;
		import urllib.parse;
		res=self.fetch(limit,skip);
		f=codecs.open(outFileLocation,'w','utf8');
		string='';
		for item in res:
			item['content']=urllib.parse.quote_plus(item['content']);
			string=str(string)+str(item['uid'])+','+str(item['content'])+','+str(item['prop'])+'\n';
		f.write(string);
		f.close();

if __name__ == '__main__':
	try:
		sys.argv[1];
	except IndexError:
		pass;
	else:
		if sys.argv[1]=='add':
			try:
				sys.argv[4];
			except IndexError:
				pass;
			else:
				shellHandle=DGStorageShell();
				shellHandle.select(str(sys.argv[2]));
				if sys.argv[4].find('/')==-1:
					shellHandle.shellAdd(str(sys.argv[3]),'../'+str(sys.argv[4]));
		if sys.argv[1]=='get':
			try:
				sys.argv[4];
			except IndexError:
				pass;
			else:
				shellHandle=DGStorageShell();
				shellHandle.select(str(sys.argv[2]));
				if sys.argv[4].find('/')==-1:
					shellHandle.shellGet(str(sys.argv[3]),'../'+str(sys.argv[4]));
		if sys.argv[1]=='fetch':
			try:
				sys.argv[5];
			except IndexError:
				pass;
			else:
				shellHandle=DGStorageShell();
				shellHandle.select(str(sys.argv[2]));
				if sys.argv[5].find('/')==-1:
					shellHandle.shellFetch(str(sys.argv[3]),str(sys.argv[4]),'../'+str(sys.argv[5]));
