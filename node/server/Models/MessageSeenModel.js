var _ = require('lodash');
var Utils = require('../lib/Utils');
var Settings = require("../lib/Settings");

var MessageSeenModel = function() {
};

MessageSeenModel.prototype.connection = null;
MessageSeenModel.prototype.init = function(connection) {
    this.connection = connection;
    return this.connection;
};

MessageSeenModel.prototype.addSeenBy = function(userID, messageID, callBack) {
    //console.log("MessageSeenModel.js:17", {   userID: userID,  messageID: messageID  });
    var query = "INSERT INTO chat_message_seens SET ?";
    var post = {
        user_id: userID,
        message_id: messageID,
        created_at: Utils.now()
    };

    this.connection.query(query, post, function (error, results, fields) {
        if (error) {
            console.error('MessageSeenModel.js:27 - error', error);
        }

        //console.log('MessageSeenModel.js:30 - ', results.insertId);
        callBack(error, results.insertId);
    });
};

MessageSeenModel.prototype.findById = function(seenByID, callBack) {
    //console.log("MessageSeenModel.js:34", {   seenByID: seenByID  });
    var query = "SELECT * FROM chat_message_seens WHERE id = " + id;
    this.connection.query(query, function (error, results, fields) {
        if (error) {
            console.error('MessageSeenModel.js:38 - error', error);
        }

        //console.log('MessageSeenModel.js:41 - ', results[0]);
        callBack(error, results[0]);
    });
};

module["exports"] = new MessageSeenModel();