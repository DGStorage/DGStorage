// DGStorage 2.4
// @DGideas dgideas@outlook.com

#include <iostream>
#include <fstream>
#include <vector>
#include <string>
#include <algorithm>
#include <map>
#include <cstdlib>
#include <cstring>

class DGStorage
{
	private:
		std::string              VERSION;
		std::string              CHARSET;
		int                      SINGLECOLLECTIONLIMIT;
		int                      SEARCHRANGE;
		int                      SEARCHINDEXLIMIT;
		int                      SEARCHCACHELIMIT;
		int                      PROPCACHELIMIT;
		bool                     SAFETY;
		int                      OS;
	private:
		std::string              Name;
		std::string              TimeStamp;
		std::vector<std::string> CollectionCache;
		std::string              LastCollection;
		std::vector<std::string> SearchCache;
	public:
		struct DGSObject;
		DGStorage();
		bool                     create (std::string);
		bool                     select (std::string);
		bool                     add (std::string, std::string, std::vector<std::map<std::string, std::string> >);
		DGSObject                get (std::string, int, unsigned int);
		DGSObject                fetch (int, unsigned int);
		DGSObject                uid (std::string);
		std::vector<DGSObject>   search (std::string, bool);
		DGSObject                pervious (std::string);
		DGSObject                following (std::string);
		DGSObject                sort (std::string, std::string, int, unsigned int);
		bool                     put (std::string, std::string);
		bool                     setprop (std::string, std::string, std::string);
		bool                     removeprop (std::string, std::string);
		bool                     remove (std::string);
		bool                     zip (std::string);
		bool                     unzip (std::string);
	protected:
		void                     clche (std::string);
		bool                     createcoll (std::string);
		bool                     removecoll (std::string);
		std::string              findavailablecoll (bool);
		DGSObject                finditemviakey (std::string, int, unsigned int);
		DGSObject                finditemviauid (std::string, std::string);
		DGSObject                getprop (std::string, std::string);
		bool                     uptmp ();
	protected:
		std::string              urlencode(std::string);
		bool                     mkdir(std::string);
		char*                    strtochar(std::string, int);
		char*                    strlcat(char*, const char*);
		char*                    getchar(const char*, const char*);
	public:
		struct DGSObject
		{
			std::string name;
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
	std::vector<std::string> nullArray;
	this->CollectionCache       = nullArray;
	this->LastCollection        = "";
	this->SearchCache           = nullArray;
	if (std::system("ver") == 0)
	{
		this->OS = 0;
	}
	else
	{
		this->OS = 1;
	}
}

bool DGStorage::create(std::string name)
{
	this->Name = name;
	if (this->SAFETY)
	{
		this->Name = this->urlencode(this->Name);
	}
	this->mkdir(this->Name);
	std::fstream conf;
	char* fileLocation = getchar(strtochar(this->Name, 0), "/conf.dgb");
	conf.open(fileLocation, std::fstream::app);
	delete fileLocation;
	conf.close();
}

std::string DGStorage::urlencode(std::string raw_string)
{
	// TODO
	return raw_string;
}

bool DGStorage::mkdir(std::string dir)
{
	char* dir_char = this->strtochar(dir, 10);
	switch (this->OS)
	{
		case 0:
			this->strlcat(dir_char, "md ");
			break;
		case 1:
			this->strlcat(dir_char, "mkdir ");
			break;
	}
	int status = std::system(dir_char);
	delete dir_char;
	return !status;
}

char* DGStorage::strtochar(std::string raw_string, int offset)
{
	int char_array_size = raw_string.size() + offset + 1;
	char* res = new char[char_array_size];
	char* res_begin = res;
	for (int i=0; i<char_array_size; i++)
	{
		res[i] = '\0';
	}
	for (std::string::iterator raw_string_i = raw_string.begin();
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
	int offsetLength = std::strlen(somechars);
	for (int i=std::strlen(source)-1; i>=0; i--)
	{
		source[i+offsetLength] = source[i];
	}
	for (int i=0; i<offsetLength; i++)
	{
		source[i] = somechars[i];
	}
	return source;
}

char* DGStorage::getchar(const char* char1, const char* char2)
{
	int length = 0;
	for (int i=0; i<std::strlen(char1); i++)
	{
		if (char1[i] != '\0')
		{
			length++;
		}
		else
		{
			break;
		}
	}
	for (int i=0; i<std::strlen(char2); i++)
	{
		if (char2[i] != '\0')
		{
			length++;
		}
		else
		{
			break;
		}
	}
	char* res = new char[length+1];
	char* res_begin = res;
	for (int i=0; i<std::strlen(char1); i++)
	{
		if (char1[i] != '\0')
		{
			*res = char1[i];
			res++;
		}
		else
		{
			break;
		}
	}
	for (int i=0; i<std::strlen(char2); i++)
	{
		if (char2[i] != '\0')
		{
			*res = char2[i];
			res++;
		}
		else
		{
			break;
		}
	}
	*res = '\0';
	return res_begin;
}

int main(int argc, char* argv[])
{
	DGStorage a;
	a.create("haha");
	return 0;
}
