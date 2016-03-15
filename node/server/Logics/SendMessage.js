
var _ = require('lodash');

var UsersManager = require("../lib/UsersManager");
var DatabaseManager = require("../lib/DatabaseManager");
var Utils = require("../lib/Utils");
var Const = require("../const");
var UserModel = require("../Models/UserModel");
var MessageModel = require("../Models/MessageModel");
var Settings = require("../lib/Settings");
var Observer = require("node-observer");

var SocketAPIHandler = require('../SocketAPI/SocketAPIHandler');

var SendMessage = {
    execute : function(userID, param, callback) {

        if(Utils.isEmpty(userID)) {
            console.error('SendMessage.js:19 - ', param);
            callback(Const.emitKeyWordSocketError, {code:Const.resCodeSocketSendMessageNoUserID});
            return;
        }

        if(Utils.isEmpty(param.room_id)) {
            console.error('SendMessage.js:25 - ', param);
            callback(Const.emitKeyWordSocketError, {code:Const.resCodeSocketSendMessageNoRoomID});
            return;
        }

        if(Utils.isEmpty(param.message)) {
            console.error('SendMessage.js:31 - ', param);
            callback(Const.emitKeyWordSocketError, {code:Const.resCodeSocketSendMessageNoMessage});
            return;
        }

        if(Utils.isEmpty(param.type)) {
            console.error('SendMessage.js:37 - ', param);
            callback(Const.emitKeyWordSocketError, {code:Const.resCodeSocketSendMessageNoType});
            return;
        }

        var newMessage = {
            user_id: userID,
            room_id: param.room_id,
            local_id: param.local_id?param.local_id:'',
            message:  param.message,
            type: param.type,
            created_at: Utils.now()
        };

        //console.log('SendMessage.js:30 - ', newMessage);
        // save to database
        MessageModel.saveNewMessage(newMessage, function(err, message) {
            if(err) {
                console.log('sendMessage.js:55 - err', err);
                callback(err, message);
                return;
            }

            if(!message) {
                console.log('sendMessage.js:61 - err', message);
                callback(Const.resCodeSocketSaveMessageFail, message);
                return;
            }

            if(param.type==2 && Utils.isEmpty(message.file)) {
                console.log('sendMessage.js:68 - err', message);
                callback(Const.resCodeSocketSendMessageNoFile, message);
                return;
            }

            //console.log('sendMessage.js:72 - message', message);
            MessageModel.populateMessages(message, function (err, data) {
                if(err) {
                    console.log('sendMessage.js:75 - err', err);
                    callback(err, data);
                    return;
                }

                var messageObj = data[0];
                //console.log('sendMessage.js:81 - message', messageObj);

                if(!Utils.isEmpty(param.local_id))
                    messageObj.local_id = param.local_id;

                console.log('sendMessage.js:53 - ', param);
                SocketAPIHandler.io.of(Settings.options.socketNameSpace).in(param.room_id).emit(Const.emitKeyWordNewMessage, data[0]);
                Observer.send(this, Const.notificationSendMessage, data[0]);

                // send push message to offline users
                var roomUsers = UsersManager.getUsers(param.room_id);
                console.log('sendMessage.js:99 - roomUsers', roomUsers);
                if(roomUsers) {
                    var userIDs = "";
                    roomUsers.forEach(function (roomUser) {
                        if(userIDs.length > 0) {
                            userIDs += "," + roomUser.user_id;
                        } else {
                            userIDs = roomUser.user_id;
                        }
                    });
                    if(userIDs.length > 0) {
                        var onlineUsersAndMessage = {
                            messageId: message.id,
                            roomUserIDs: userIDs
                        };
                        MessageModel.sendPushToOfflineUsers(onlineUsersAndMessage, function (err, data) {
                            //console.log('SendMessage.js:84 - data', data);
                        });
                    }
                }

                callback(err, message);
            });
        });
    }
};

module["exports"] = SendMessage;