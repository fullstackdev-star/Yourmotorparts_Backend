var _ = require('lodash');
var WebAPIManager = require('../lib/WebAPIManager');
var UrlGenerator = require('../lib/UrlGenerator');
var UserModel = require('./UserModel');
var MessageSeenModel = require("./MessageSeenModel");
var FileModel = require("./FileModel");
var Util = require('../lib/Utils');

var MessageModel = function() {
};

MessageModel.prototype.connection = null;
MessageModel.prototype.init = function(connection) {
    this.connection = connection;
    return this.connection;
};

MessageModel.prototype.saveNewMessage = function(message, callback) {
    //console.log("MessageModel.js:18", message);
    var query = "INSERT INTO chat_messages SET ?";
    var self = this;
    this.connection.query(query, message, function (error, results, fields) {
        if (error) {
            console.error('MessageModel.js:24 - error', error);
            callback(error, null);

        } else {
            message.id = results.insertId;
            /*var model = new MessageModel();
            model.connection = self.connection;
            model.findMessageById(message.id, callback);*/
            self.findMessageById(message.id, callback)
        }
    });
};

MessageModel.prototype.sendPushToOfflineUsers = function(message, callBack) {
    //console.log("MessageModel.js:32", UrlGenerator.sendPushToOfflineUsers());
    //console.log("MessageModel.js:33", message);
    WebAPIManager.post(
        UrlGenerator.sendPushToOfflineUsers(),
        message,
        // success
        function(data) {
            callBack(false, data);
        },
        //error
        function(error) {
            if(error) {
                console.error('MessageModel.js:52 - error', error);
            }
            callBack(error, null);
        }
    );
};

MessageModel.prototype.findMessageById = function(id, callBack) {
    var query = "SELECT * FROM chat_messages WHERE id = " + id;
    //console.log("MessageModel.js:59 - ", query);
    this.connection.query(query, function (error, results, fields) {
        if (error) {
            console.error('MessageModel.js:63 - error', error);
            callBack(error, null);

        } else {
            //console.log('UserModel.js:23 - ', results[0].username);
            var message = results[0];
            if(message.type == 2) {
                FileModel.findFileById(message.message, function(error1, file) {
                    if(error1) {
                        console.error('MessageModel.js:72 - error', error1);
                        callBack(error, null);

                    } else {
                        if(file) {
                            if (file.thumb_id > 0) {
                                FileModel.findFileById(file.thumb_id, function (error2, file1) {
                                    if (error1) {
                                        console.error('MessageModel.js:80 - error', error2);
                                        message.file = {file: file}
                                        callBack(error, message);

                                    } else {
                                        message.file = {file: file, thumb: file1};
                                        console.log('MessageModel.js:85 - message', message);
                                        callBack(false, message);
                                    }
                                });
                            } else {
                                message.file = {file: file};
                                console.log('MessageModel.js:91 - message', message);
                                callBack(false, message);
                            }
                        } else {
                            callBack(false, message);
                        }
                    }
                });
            } else {
                callBack(false, message);
            }
        }
    });
};

MessageModel.prototype.deleteMessageById = function(id, callBack) {
    var query = "UPDATE chat_messages SET message_status = ?, deleted_at = ? WHERE id = ? ";
    this.connection.query(query, [0, Util.now(), id], function (error, results, fields) {
        if (error) {
            console.error('MessageModel.js:113 - error', error);
            callBack(error, null);
            return;
        }

        //console.log('MessageModel.js:107 - ', results[0]);
        callBack(error, results[0]);
    });
};

MessageModel.prototype.populateMessages = function(messages, callBack) {
    if(!_.isArray(messages)) {
        messages = [messages];
    }

    // collect ids
    var ids = [];
    messages.forEach(function(row) {
        // get users for seenBy too
        _.forEach(row.seenBy, function(row2) {
            ids.push(row2.user_id);
        });

        if(_.size(ids) == 0 || _.indexOf(ids, row.user_id) == -1) {
            ids.push(row.user_id);
        }
    });

    if(ids.length > 0) {
        //console.log("MessageModel.js:147", ids);
        UserModel.findUsersByIds(ids, function(err, users) {
            var resultAry = [];
            _.forEach(messages, function(messageElement, messageIndex, messagesEntity){
                var obj = messageElement;
                _.forEach(users, function(userElement, userIndex) {
                    // replace user to userObj
                    if(messageElement.user_id.toString() == userElement.id.toString()){
                        //console.log("MessageModel.js:155", userElement);
                        obj.user = userElement
                    }
                });

                var seenByAry = [];
                // replace seenby.user to userObj
                _.forEach(messageElement.seenBy, function(seenByRow) {
                    _.forEach(users, function(userElement,userIndex) {
                        // replace user to userObj
                        if(seenByRow.user_id.toString() == userElement.id.toString()){
                            seenByAry.push({
                                user_id:user.id,
                                user:userElement,
                                created_at:seenByRow.created_at,
                                seen_status:1
                            });
                        }
                    });
                });

                obj.seenBy = seenByAry;
                resultAry.push(obj);
            });

            callBack(err, resultAry);
        });

    } else {
        callBack(null, messages);
    }

};

module["exports"] = new MessageModel();