var CONST = require('../consts');
var _ = require('lodash');
var U = require('./utils.js');
var ErrorDialog = require('../Views/Modals/ErrorDialog/ErrorDialog');
var LoginUserManager = require('./loginUserManager.js');
var request = require("request");

(function (global) {
    var webAPIManager = {
        post: function (url, data, onSuccess, onError) {
            console.log("webAPIManager.js:11 - post:", url);
            console.log("webAPIManager.js:12 - data:", data);
            var self = this;
            var header = {};

            if (!_.isNull(LoginUserManager.user)) {
                var token = LoginUserManager.user.get('token');
                if (!_.isEmpty(token)) {
                    header['access-token'] = token;
                }
            }

            request({
                headers: {'content-type': 'application/json'},
                uri: url,
                method: "POST",
                timeout: 10000,
                followRedirect: true,
                maxRedirects: 10,
                json: data
            }, function (error, response, body) {
                //console.log(response);
                if (!error) {
                    if (body) {
                        //console.log('webAPIManager.js:34 - body', body);
                        //console.log('webAPIManager.js:35 - bodyType', typeof(body));
                        var resObj = {};
                        if (typeof(body) === "object") {
                            resObj = body;
                        } else {
                            resObj = JSON.parse(body);
                        }
                        var errorCode = resObj.status;
                        console.log('webAPIManager.js:62 - errorCode', errorCode);

                        // server handled error
                        if (errorCode != 1) {
                            var message = CONST.ERROR_CODES[errorCode];
                            ErrorDialog.show('Error - webAPIManager:49', message);

                            if (!_.isUndefined(onError)) {
                                onError();
                            }
                        }

                        if (errorCode == 1) {
                            if (!_.isUndefined(onSuccess))
                                onSuccess(resObj.data);
                        }
                    } else {
                        if (!_.isUndefined(onError)) {
                            onError();
                        }
                    }
                } else {
                    console.log('webAPIManager.js:64 - error', error);
                    ErrorDialog.show('Network Error - webAPIManager:67', 'Critical Error', function () {
                        ErrorDialog.hide(function () {
                            self.post(url, data, onSuccess, onError);
                        });
                    });

                    if (!_.isUndefined(onError)) {
                        onError();
                    }
                }
            });
        },

        fileUpload: function (url, file, onProgress, onSuccess, onError) {
            console.log("webAPIManager.js:80 - upload:", url);
            var self = this;
            var header = {};

            var data = new FormData();
            data.append('file', file);
            $.ajax({
                type: "POST",
                url: url,
                data: data,
                dataType: 'json',
                contentType: false,
                processData: false,
                headers: header,
                xhr: function () {
                    var xhr = $.ajaxSettings.xhr();
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (onProgress)
                            onProgress(evt.loaded / evt.total);
                    }, false);
                    return xhr;
                },
                success: function (response) {
                    console.log("webAPIManager.js:103 - response", response);
                    var errorCode = response.status;

                    // server handled error
                    if (errorCode != 1) {
                        var message = CONST.ERROR_CODES[errorCode];
                        ErrorDialog.show('Error - webAPIManager:110', message);
                        if (!_.isUndefined(onError)) {
                            onError();
                        }
                    }

                    if (errorCode == 1) {
                        if (!_.isUndefined(onSuccess))
                            onSuccess(response.data);
                    }
                },
                statusCode: {
                    403: function () {
                        console.log("403");
                    }
                },
                error: function (e) {
                    console.log("webAPIManager.js:126 - e", e);
                    ErrorDialog.show('Network Error - webAPIManager:128', 'Critical Error', function () {
                        ErrorDialog.hide(function () {
                            self.post(url, data, onSuccess, onError);
                        });
                    });

                    if (!_.isUndefined(onError)) {
                        onError();
                    }
                }
            });

            /*if(!_.isNull(LoginUserManager.user)){
             var token = LoginUserManager.user.get('token');

             if(!_.isEmpty(token)){
             header['access-token'] = token;
             }
             }

             var data = new FormData();
             data.append('file', file);

             var r = request.post({url:url, formData: data}, function(err, httpResponse, body) {
             /!*if(onProgress)
             onProgress(evt.loaded/evt.total);*!/
             console.log("webAPIManager.js:103 - ", httpResponse);
             if (err) {
             console.log("webAPIManager.js:105 - ", err);
             ErrorDialog.show('Network Error', 'Critical Error', function() {
             ErrorDialog.hide(function() {
             //self.post(url, data, onSuccess, onError);
             });
             });

             if(!_.isUndefined(onError)){
             onError();
             }
             }

             console.log("webAPIManager.js:117 - ", body);
             var errorCode = body.status;

             // server handled error
             if(errorCode != 1){
             var message = CONST.ERROR_CODES[errorCode];
             ErrorDialog.show('Error',message);

             if(!_.isUndefined(onError)){
             onError();
             }
             }

             if(errorCode == 1){
             if(!_.isUndefined(onSuccess))
             onSuccess(response.data);
             }
             console.log('Upload successful!  Server responded with:', body);
             });*/
        },

        get: function (url, onSuccess, onError) {
            console.log("webAPIManager.js:148 - get:", url);
            var header = {};
            if (!_.isNull(LoginUserManager.user)) {
                var token = LoginUserManager.user.get('token');
                if (!_.isEmpty(token)) {
                    header['access-token'] = token;
                }
            }

            request({
                uri: url,
                method: "GET",
                timeout: 10000,
                followRedirect: true,
                maxRedirects: 10
            }, function (error, response, body) {
                //console.log(response);
                if (!error) {
                    if (body) {
                        //console.log('webAPIManager.js:167 - body', body);
                        //console.log('webAPIManager.js:168 - bodyType', typeof(body));
                        try {
                            var resObj = JSON.parse(body);
                            var errorCode = resObj.status;
                            console.log('webAPIManager.js:213 - errorCode', errorCode);
                            // server handled error
                            if (errorCode != 1) {
                                console.log(errorCode);
                                var message = CONST.ERROR_CODES[errorCode];
                                ErrorDialog.show('Error - webAPIManager:219', message);
                            }

                            if (errorCode == 1) {
                                if (!_.isUndefined(onSuccess))
                                    onSuccess(resObj.data);
                            }
                        } catch (err) {
                            console.error('webAPIManager.js:226 - ', url);
                            console.error('webAPIManager.js:227 - error', err);
                            if (!_.isUndefined(onError)) {
                                onError();
                            }
                        }
                    } else {
                        if (!_.isUndefined(onSuccess))
                            onSuccess(error);
                    }
                } else {
                    console.log('webAPIManager.js:187 - error', error);
                    ErrorDialog.show('Network Error - webAPIManager:239', 'Critical Error', function () {
                        ErrorDialog.hide(function () {
                            self.get(url, onSuccess, onError);
                        });
                    });

                    if (!_.isUndefined(onError)) {
                        onError();
                    }
                }
            });
        }
    };

    // Exports ----------------------------------------------
    module["exports"] = webAPIManager;

})((this || 0).self || global);