#include<iostream>
#include<string>
using namespace std;
int main()
{
	
}

class DGStorage
{
	public:
	string DGSTORAGE_VERSION='2.1'; // DataCollection Version
	string DGSTORAGE_CHARSET='utf8'; // Default Charset
	int DGSTORAGE_SINGLECOLLECTIONLIMIT=1024; // Determine every collection can put how many datas
	int DGSTORAGE_SEARCHRANGE=3; // Determine when find a avalible collection, how many collection can we find. None stands find all collection.
	int DGSTORAGE_SEARCHINDEXLIMIT=64; // Determine DGStorage can storage how many indexs for quick search.
	int DGSTORAGE_SEARCHCACHELIMIT=32; // Determine DGStorage can storage how many caches for quick responds.
	bool DGSTORAGE_SAFETY=True; // Security settings, True not allowed access database out of the exec path.
	
	string DGSTORAGE_Name=void;
	
	struct CollectionCache=[];
	string LastCollection='';
	struct SearchCache=[];
	
	bool create(string name)
	{
		this->DGSTORAGE_Name=name;
		if(this->DGSTORAGE_SAFETY==true)
		{
			this->DGSTORAGE_Name=urlencode(this->DGSTORAGE_Name);
		}
		
	}
}
//URL 编码
std::string urlencode(std::string encode)
{
   std::string result;
   for(unsigned int i = 0; i< static_cast<unsigned int>(encode.length()); i++)
   {
    char ch = encode[i];
    if(ch == ' ')
    {
     result += '+';
    }else if(ch >= 'A' && ch <= 'Z'){
     result += ch;
    }else if(ch >= 'a' && ch <= 'z'){
     result += ch;
    }else if(ch >= '0' && ch <= '9'){
     result += ch;
    }else if(ch == '-' || ch == '-' || ch == '.' || ch == '!' || ch == '~' || ch == '*' || ch == '\'' || ch == '(' || ch == ')' ){
     result += ch;
    }else{
     result += '%';
     result += iChoo::iconv::char_to_hex(ch);
    }
   }
return result;
}
//URL 解码
std::string urldecode(std::string decode)
{
   std::string result;
   for(unsigned int i = 0; i< static_cast<unsigned int>(decode.length()); i++)
   {
    switch(decode[i])
    {
    case '+':
     result += ' ';
    break;
    case '%':
     if(isxdigit(decode[i + 1]) && isxdigit(decode[i + 2]))
     {
      result += iChoo::iconv::hex_to_char(decode[i+1], decode[i+2]);
      i += 2;
     }else {
      result += '%';
     }
    break;
    default:
     result += decode[i];
    break;
    }
   }
return result;
}
