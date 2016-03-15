var _ = require('lodash');
var SocketHandlerBase = require("./SocketHandlerBase");
var Observer = require("node-observer");
var Utils = require("../lib/Utils");
var Const = require("../const");
var UserModel = require("../Models/UserModel");
var MessageModel = require("../Models/MessageModel");
var MessageSeenModel = require("../Models/MessageSeenModel");
var async = require("async");
var Settings = require("../lib/Settings");

var OpenMessageActionHandler = function(){
};

_.extend(OpenMessageActionHandler.prototype, SocketHandlerBase.prototype);

OpenMessageActionHandler.prototype.attach = function(io, socket) {

    socket.on(Const.emitKeyWordOpenMessage, function(param) {

        if(Utils.isEmpty(param.user_id)) {
            console.error('OpenMessageActionHandler.js:22 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketOpenMessageNoUserID});
            return;
        }
        
        if(Utils.isEmpty(param.message_ids)) {
            console.error('OpenMessageActionHandler.js:28 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketOpenMessageNoMessageID});
            return;
        }
        
        if(!_.isArray(param.message_ids)) {
            console.error('OpenMessageActionHandler.js:34 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketOpenMessageNoMessageID});
            return;
        }
        
        var updatedMessages = [];
        UserModel.findUserById(param.user_id, function (err, user) {
            if(err) {
                console.error('OpenMessageActionHandler.js:42 - ', param);
                socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketOpenMessageNoUserID});
                return;
            }

            async.forEach(param.message_ids, function (messageID, callback) {
                MessageModel.findMessageById(messageID, function(err, message) {
                    if(err) {
                        console.error('OpenMessageActionHandler.js:44 - ', err);
                        socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketUnknownError});
                        return;
                    }

                    if(!message) {
                        console.error('OpenMessageActionHandler.js:50 - ', param);
                        socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketUnknownError});
                        return;
                    }

                    var listOfUsers = [];
                    _.forEach(message.seenBy, function(seenObj) {
                        if(!_.isNull(seenObj.user_id) && !_.isUndefined(seenObj.user_id)) {
                            listOfUsers.push(seenObj.user_id.toString());
                        }
                    });

                    if(_.indexOf(listOfUsers, user.id.toString()) == -1) {
                        MessageSeenModel.addSeenBy(user.id, message.id, function(err, messageUpdated) {
                            if(err) {
                                console.error('OpenMessageActionHandler.js:71 ', err);
                            }

                            MessageModel.findMessageById(message.id, function (err, message1) {
                                if(err) {
                                    console.error('OpenMessageActionHandler.js:76 ', err);
                                    return;
                                }

                                if(message1) {
                                    updatedMessages.push(message1);
                                }

                                callback(err);
                            });
                        });
                    }
                });

            }, function(err) {
                if(err) {
                    console.error('OpenMessageActionHandler.js:92 ', err);
                    socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketUnknownError});
                    return;
                }

                MessageModel.populateMessages(updatedMessages, function (err, messages) {
                    if(err) {
                        console.error('OpenMessageActionHandler.js:99 ', err);
                        socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketUnknownError});
                        return;
                    }

                    if(messages.length > 0) {
                        // send updated messages
                        io.of(Settings.options.socketNameSpace).in(messages[0].room_id).emit(Const.emitKeyWordMessageUpdated, messages);
                        Observer.send(this, Const.notificationMessageChanges, messages);
                    }
                });
            });
        });
    });

};

module["exports"] = new OpenMessageActionHandler();