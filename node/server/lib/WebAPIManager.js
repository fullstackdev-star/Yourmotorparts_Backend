var CONST = require('../const');
var _ = require('lodash');
var request = require("request");

(function(global) {
   var WebAPIManager = {
       post : function(url, data, onSuccess, onError){
            //console.log("WebAPIManager.js:8 - post:", url);
            request({
                uri: url,
                method: "POST",
                timeout: 10000,
                followRedirect: true,
                maxRedirects: 10,
                json: data
            }, function(error, response, body) {
                if(!error) {
                    //console.log('WebAPIManager.js:18 - body', body);
                    var resObj = {};
                    if(typeof(body) === "object") {
                        resObj = body;
                    } else {
                        console.log('WebAPIManager.js:23 - body type', typeof(body));
                        console.log('WebAPIManager.js:24 - body', body);
                        resObj = JSON.parse(body);
                    }
                    var errorCode = resObj.status;
                    //console.log('WebAPIManager.js:26 - errorCode', errorCode);

                    // server handled error
                    if(errorCode != 1){
                        var message = CONST.ERROR_CODES[errorCode];
                        if(!_.isUndefined(onError)) {
                            console.log('WebAPIManager.js:34', error);
                            onError();
                        }
                    } else {
                        if(!_.isUndefined(onSuccess))
                            onSuccess(resObj.data);
                    }
                } else {
                    console.log('WebAPIManager.js:42', error);
                    if(!_.isUndefined(onError)) {
                        onError();
                    }
                }
            });
        },

       get : function(url, onSuccess, onError) {
           request({
               uri: url,
               method: "GET",
               timeout: 10000,
               followRedirect: true,
               maxRedirects: 10
           }, function(error, response, body) {
               if(!error) {
                   //console.log('WebAPIManager.js:59 - body', body);
                   try {
                       var resObj = JSON.parse(body);
                       var errorCode = resObj.status;
                       // server handled error
                       if(errorCode != 1){
                           console.log('WebAPIManager.js:64 - errorCode', errorCode);
                           var message = CONST.ERROR_CODES[errorCode];
                           console.log('WebAPIManager.js:66 - ', url);
                           console.log('WebAPIManager.js:67 - ', body);
                       }

                       if(errorCode == 1){
                           if(!_.isUndefined(onSuccess))
                               onSuccess(resObj.data);
                       }
                   } catch(err) {
                       console.error('WebAPIManager.js:76 - body', body);
                       if(!_.isUndefined(onError)){
                           onError();
                       }
                   }
               } else {
                   console.log('WebAPIManager.js:75 - ', url);
                   console.log('WebAPIManager.js:76 - ', error);
                   if(!_.isUndefined(onError)){
                       onError();
                   }
               }
           });
       }
    };
 
    // Exports ----------------------------------------------
    module["exports"] = WebAPIManager;

})((this || 0).self || global);