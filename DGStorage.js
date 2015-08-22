//person.js
var num = 0;

exports.Person = function(pName){

   //private

   var pri = {

       pName : "",

       pAge : 0

   }

   //public

   var pub = {

       setName : function(pName){

           pri.pName = pName;

       },

       getName : function(){

           return pri.pName;

       }

   }

//construct code

   pri.pName = pName;



   return pub;

}