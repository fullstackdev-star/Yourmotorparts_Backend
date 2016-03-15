var _ = require('lodash');
var Observer = require("node-observer");
var Utils = require("../lib/Utils");
var Const = require("../const");
var SocketHandlerBase = require("./SocketHandlerBase");
var UserModel = require("../Models/UserModel");
var Settings = require("../lib/Settings");
var BridgeManager = require('../lib/BridgeManager');

var SendTypingActionHandler = function(){
};

_.extend(SendTypingActionHandler.prototype,SocketHandlerBase.prototype);

SendTypingActionHandler.prototype.attach = function(io, socket) {
     
    socket.on(Const.emitKeyWordSendTyping, function(param) {

        if(Utils.isEmpty(param.user_id)) {
            console.error('SendTypingActionHandler.js:20 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketTypingNoUserID});
            return;
        }
        
        if(Utils.isEmpty(param.room_id)) {
            console.error('SendTypingActionHandler.js:26 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketTypingNoRoomID});
            return;
        }
        
        if(Utils.isEmpty(param.type)) {
            console.error('SendTypingActionHandler.js:32 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketTypingNoType});
            return;
        }
        
        BridgeManager.hook('typing', param, function(result) {
            if(result == null || result.canSend) {
                UserModel.findUserById(param.user_id, function (err, user) {
                    if(err) {
                        console.error('SendTypingActionHandler.js:41 - ', err);
                        socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketTypingFaild});
                        return;   
                    }
                    
                    param.user = user;
                    io.of(Settings.options.socketNameSpace).in(param.room_id).emit(Const.emitKeyWordSendTyping, param);
                    Observer.send(this, Const.notificationUserTyping, param);
                });
            }
        });
    });

};

module["exports"] = new SendTypingActionHandler();