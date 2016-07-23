// DGStorage 2.4
// @DGideas dgideas@outlook.com

#include <iostream>
#include <vector>
#include <string>
#include <algorithm>
#include <map>

using namespace std;

class DGStorage
{
	public:
		struct DGSObject;
		DGStorage();
		bool				create (string);
		bool				select (string);
		bool				add (string, string, vector<map<string, string> >);
		DGSObject			get (string, int, unsigned int);
		DGSObject			fetch (int, unsigned int);
		DGSObject			uid (string);
		vector<DGSObject>	search (string, bool);
		DGSObject			pervious (string);
		DGSObject			following (string);
		DGSObject			sort (string, string, int, unsigned int);
		bool				put (string, string);
		bool				setprop (string, string, string);
		bool				removeprop (string, string);
		bool				remove (string);
		bool				zip (string);
		bool				unzip (string);
	protected:
		void				clche (string);
		bool				createcoll (string);
		bool				removecoll (string);
		string				findavailablecoll (bool);
		DGSObject			finditemviakey (string, int, unsigned int);
		DGSObject			finditemviauid (string, string);
		DGSObject			getprop (string, string);
		bool				uptmp ();
	public:
		struct DGSObject
		{
			string name;
		};
};

int main(int argc, char* argv[])
{
	
}
