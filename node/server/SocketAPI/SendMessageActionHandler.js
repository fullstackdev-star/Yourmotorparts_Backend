var _ = require('lodash');

var DatabaseManager = require("../lib/DatabaseManager");
var Utils = require("../lib/Utils");
var Const = require("../const");
var SocketHandlerBase = require("./SocketHandlerBase");
var SocketAPIHandler = require('../SocketAPI/SocketAPIHandler');

var SendMessageActionHandler = function(){};
var SendMessageLogic = require('../Logics/SendMessage');
var BridgeManager = require('../lib/BridgeManager');

_.extend(SendMessageActionHandler.prototype,SocketHandlerBase.prototype);

SendMessageActionHandler.prototype.attach = function(io,socket) {

    socket.on(Const.emitKeyWordSendMessage, function(param) {

        if(Utils.isEmpty(param.room_id)) {
            console.error('SendMessageActionHandler.js:21 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketSendMessageNoRoomID});
            return;
        }

        if(Utils.isEmpty(param.user_id)) {
            console.error('LoginActionHandler.js:27 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketSendMessageNoUserID});
            return;
        }

        if(Utils.isEmpty(param.type)) {
            console.error('SendMessageActionHandler.js:33 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketSendMessageNoType});
            return;
        }
                        
        if(param.type == Const.messageTypeText && Utils.isEmpty(param.message)) {
            console.error('SendMessageActionHandler.js:39 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketSendMessageNoMessage});
            return;
        }

        if(param.type == Const.messageTypeLocation && (
                                        Utils.isEmpty(param.location) ||
                                        Utils.isEmpty(param.location.lat) ||
                                        Utils.isEmpty(param.location.lng))) {

            console.error('SendMessageActionHandler.js:49 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketSendMessageNoLocation});
            return;
        }

        BridgeManager.hook(Const.emitKeyWordSendMessage, param, function(result) {
            if(result == null || result.canSend) {
                var userID = param.user_id;
                SendMessageLogic.execute(userID, param, function(err, message) {
                    //console.log('SendMessageActionHandler.js:58 - ', message);
                    if(err) {
                        console.log('SendMessageActionHandler.js:60 - ', err);
                        socket.emit(Const.emitKeyWordSocketError, {code: Const.resCodeSocketSendMessageFail});
                    }
                });
            }
        });
    });

};

module["exports"] = new SendMessageActionHandler();