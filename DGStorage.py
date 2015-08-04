#!/usr/bin/env python3
__author__='DGideas';
#Release:Pandora
import os;
import sys;
import codecs;
import uuid;
import urllib.parse;

try:
	os.chdir(os.path.dirname(sys.argv[0]));
except FileNotFoundError:
	pass;
except OSError:
	pass;

class DGStorage:
	def __init__(self):
		self.query=[];
		self.database='';
		self.conf={};
		self.coll=[];
		self.optcache={};
		self.keycache=[];
		self.LTkeycache=[]; #键值长期缓存
		self.uidcache=[];
		
	def selectdb(self,database):
		try:
			open(database+'/conf.dgb','r');
		except FileNotFoundError:
			return False;
		else:
			conf=codecs.open(database+'/conf.dgb','r','utf8');
			for line in conf.readlines():
				config=line.split(':');
				self.conf[config[0]]=config[1];
			self.database=database;
		return True;

	def createdb(self,database):
		try:
			open(database+'/conf.dgb','r');
		except FileNotFoundError:
			conf=codecs.open(database+'/conf.dgb','a','utf8');
			os.chdir(database);
			self.database=database
			os.mkdir('index');
			self.createcoll(0);
			conf.write('databaseid:'+str(uuid.uuid1()));
			conf.write('\ndatabaseversion:1.0');
			return True;
		else:
			return False;

	def add(self,key,content):
		self.clche();
		key=urllib.parse.quote_plus(key);
		try:
			codecs.open(self.database+'/index/index.dgi','r','utf8');
		except FileNotFoundError:
			return False;
		else:
			index=codecs.open(self.database+'/index/index.dgi','r','utf8');
			for line in index.readlines():
				line=line.replace('\n',''); #
				self.coll.append(str(line));
			for collection in self.coll:
				try:
					self.optcache['coll'];
				except KeyError:
					try:
						codecs.open(self.database+'/'+collection+'/index/index.dgc','r','utf8');
					except FileNotFoundError:
						return False;
					else:
						docindex=codecs.open(self.database+'/'+collection+'/index/index.dgc','r','utf8');
						if len(docindex.readlines())<1024:
							self.optcache['coll']=collection; #目的集合选择器
				else:
					pass;
				docindex=codecs.open(self.database+'/'+collection+'/index/index.dgc','r','utf8');
				r=docindex.readlines();
				if len(r)==0:
					continue;
				else:
					if r[0].split(',')[1] not in self.LTkeycache: #长期缓存机制
						for line in docindex.readlines():
							self.keycache.append(line.split(',')[1]);
							self.LTkeycache.append(line.split(',')[1]);
			if key not in self.LTkeycache:
				try:
					self.optcache['coll'];
				except KeyError:
					self.LTkeycache.append(key);
					addcollid=int(self.coll[-1])+1;
					self.createcoll(addcollid);
					self.optcache['coll']=str(addcollid);
					uid=uuid.uuid1();
					docindex=codecs.open(self.database+'/'+self.optcache['coll']+'/index/index.dgc','a','utf8');
					docindexr=codecs.open(self.database+'/'+self.optcache['coll']+'/index/index.dgc','r','utf8');
					data=codecs.open(self.database+'/'+self.optcache['coll']+'/'+str(uid)+'.dgs','w','utf8');
					if len(docindexr.readlines())==0:
						docindex.write(str(uid)+','+key);
					else:
						docindex.write('\n'+str(uid)+','+key);
					data.write(content);
					return True;
				else:
					self.LTkeycache.append(key);
					uid=uuid.uuid1();
					docindex=codecs.open(self.database+'/'+self.optcache['coll']+'/index/index.dgc','a','utf8');
					docindexr=codecs.open(self.database+'/'+self.optcache['coll']+'/index/index.dgc','r','utf8');
					data=codecs.open(self.database+'/'+self.optcache['coll']+'/'+str(uid)+'.dgs','w','utf8');
					if len(docindexr.readlines())==0:
						docindex.write(str(uid)+','+key);
					else:
						docindex.write('\n'+str(uid)+','+key);
					data.write(content);
					#垃圾回收
					index.close();
					docindex.close();
					docindexr.close();
					data.close();
					return True;
			else:
				return False;

	def get(self,key):
		self.clche();
		key=urllib.parse.quote_plus(key);
		index=codecs.open(self.database+'/index/index.dgi','r','utf8');
		for line in index.readlines():
			self.coll.append(str(line));
		for collection in self.coll:
			docindex=codecs.open(self.database+'/'+collection+'/index/index.dgc','r','utf8');
			for line in docindex.readlines():
				linesplit=line.split(',');
				if key==linesplit[1]:
					cont=codecs.open(self.database+'/'+collection+'/'+str(linesplit[0])+'.dgs','r','utf8');
					return cont.read();
		return False;
				
##################################################
#以下方法无需在外部调用
	def clche(self):
		self.optcache={};
		self.keycache=[];
		self.uidcache=[];
		self.coll=[];

	def createcoll(self,coll):
		os.chdir(self.database);
		os.mkdir(str(coll));
		os.mkdir(str(coll)+'/index');
		collindex=codecs.open(self.database+'/'+str(coll)+'/index/index.dgc','a','utf8');
		index=codecs.open(self.database+'/index/index.dgi','a','utf8');
		indexr=codecs.open(self.database+'/index/index.dgi','r','utf8');
		if len(indexr.readlines())==0:
			index.write(str(coll));
		else:
			index.write('\n'+str(coll));
		#垃圾回收
		index.close();
		indexr.close();
