var _ = require('lodash');
var Observer = require("node-observer");

var DatabaseManager = require("../lib/DatabaseManager");
var Utils = require("../lib/Utils");
var Const = require("../const");
var SocketHandlerBase = require("./SocketHandlerBase");
var SocketAPIHandler = require('../SocketAPI/SocketAPIHandler');
var MessageModel = require("../Models/MessageModel");
var Settings = require("../lib/Settings");

var DeleteMessageActionHandler = function(){
    
}

_.extend(DeleteMessageActionHandler.prototype,SocketHandlerBase.prototype);

DeleteMessageActionHandler.prototype.attach = function(io,socket) {

    socket.on(Const.emitKeyWordDeleteMessage, function(param) {
        
        if(Utils.isEmpty(param.user_id)) {
            console.error('DeleteMessageActionHandler.js:24 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketDeleteMessageNoUserID});
            return;
        }

        if(Utils.isEmpty(param.message_id)) {
            console.error('DeleteMessageActionHandler.js:30 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketDeleteMessageNoMessageID});
            return;
        }
        
        MessageModel.deleteMessageById(param.message_id, function(err, result) {
            if(err) {
                console.error('DeleteMessageActionHandler.js:38 - ', err);
                socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketUnknownError});
                return;
            }

            io.of(Settings.options.socketNameSpace).in(param.room_id).emit(Const.emitKeyWordMessageDeleted, param.message_id);
            Observer.send(this, Const.notificationMessageDeleted, param.message_id);
        });
    });

};


module["exports"] = new DeleteMessageActionHandler();