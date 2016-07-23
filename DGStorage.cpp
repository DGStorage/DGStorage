// DGStorage 2.4
// @DGideas dgideas@outlook.com

#include <iostream>
#include <vector>
#include <string>
#include <algorithm>
#include <map>
#include <cstdlib>
#include <cstring>

using namespace std;

class DGStorage
{
	private:
		string            VERSION;
		string            CHARSET;
		int               SINGLECOLLECTIONLIMIT;
		int               SEARCHRANGE;
		int               SEARCHINDEXLIMIT;
		int               SEARCHCACHELIMIT;
		int               PROPCACHELIMIT;
		bool              SAFETY;
		int               OS;
	private:
		string            Name;
		string            TimeStamp;
		vector<string>    CollectionCache;
		string            LastCollection;
		vector<string>    SearchCache;
	public:
		struct DGSObject;
		DGStorage();
		bool              create (string);
		bool              select (string);
		bool              add (string, string, vector<map<string, string> >);
		DGSObject         get (string, int, unsigned int);
		DGSObject         fetch (int, unsigned int);
		DGSObject         uid (string);
		vector<DGSObject> search (string, bool);
		DGSObject         pervious (string);
		DGSObject         following (string);
		DGSObject         sort (string, string, int, unsigned int);
		bool              put (string, string);
		bool              setprop (string, string, string);
		bool              removeprop (string, string);
		bool              remove (string);
		bool              zip (string);
		bool              unzip (string);
	protected:
		void              clche (string);
		bool              createcoll (string);
		bool              removecoll (string);
		string            findavailablecoll (bool);
		DGSObject         finditemviakey (string, int, unsigned int);
		DGSObject         finditemviauid (string, string);
		DGSObject         getprop (string, string);
		bool              uptmp ();
	protected:
		string            urlencode(string);
		bool              mkdir(string);
		char*             strtochar(string);
		char*             strlcat(char*, const char*);
	public:
		struct DGSObject
		{
			string name;
		};
};

DGStorage::DGStorage()
{
	this->VERSION               = "2.4";
	this->CHARSET               = "utf-8";
	this->SINGLECOLLECTIONLIMIT = 1024;
	this->SEARCHRANGE           = 3;
	this->SEARCHINDEXLIMIT      = 32;
	this->SEARCHCACHELIMIT      = 32;
	this->PROPCACHELIMIT        = 32;
	this->SAFETY                = true;
	this->Name                  = "";
	this->TimeStamp             = "";
	vector<string> nullArray;
	this->CollectionCache       = nullArray;
	this->LastCollection        = "";
	this->SearchCache           = nullArray;
	if (system("ver") == 0)
	{
		this->OS = 0;
	}
	else
	{
		this->OS = 1;
	}
}

bool DGStorage::create(string name)
{
	this->Name = name;
	if (this->SAFETY)
	{
		this->Name = this->urlencode(this->Name);
	}
	
}

string DGStorage::urlencode(string raw_string)
{
	// TODO
	return raw_string;
}

bool DGStorage::mkdir(string dir)
{
	char* dir_char = this->strtochar(dir, 10);
	switch (this->OS)
	{
		case 0:
			break;
		case 1:
			break;
	}
	int status = system(dir);
	return !status;
}

char* DGStorage::strtochar(string raw_string, int offset=0)
{
	int char_array_size = raw_string.size() + offset + 1;
	char* res = new char[char_array_size];
	char* res_begin = res;
	for (int i=0; i<char_array_size; i++)
	{
		res[i] = '\0';
	}
	for (string::iterator raw_string_i = raw_string.begin();
		raw_string_i < raw_string.end();
		raw_string_i++)
	{
		*res = *raw_string_i;
		res++;
	}
	return res_begin;
}

char* DGStorage::strlcat(char* source, const char* somechars)
{
	int offsetLength = strlen(somechars);
	
}

int main(int argc, char* argv[])
{
	
}
