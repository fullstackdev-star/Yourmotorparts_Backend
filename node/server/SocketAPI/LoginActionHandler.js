var _ = require('lodash');
var SocketHandlerBase = require("./SocketHandlerBase");
var Observer = require("node-observer");
var UsersManager = require("../lib/UsersManager");
var DatabaseManager = require("../lib/DatabaseManager");
var Utils = require("../lib/Utils");
var Const = require("../const");
var UserModel = require("../Models/UserModel");
var Settings = require("../lib/Settings");

var LoginActionHandler = function(){
};

_.extend(LoginActionHandler.prototype,SocketHandlerBase.prototype);

LoginActionHandler.prototype.attach = function(io, socket) {

    socket.on(Const.emitKeyWordLogin, function(param) {

        if(Utils.isEmpty(param.user_id)) {
            console.error('LoginActionHandler.js:21 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketLoginNoUserID});
            return;
        }

        if(Utils.isEmpty(param.room_id)){
            console.error('LoginActionHandler.js:27 - ', param);
            socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketLoginNoRoomID});
            return;
        }

        //save as message
        UserModel.findUserById(param.user_id, function (err, user) {
            //console.log('LoginActionHandler.js:39 ', user);
            if(err) {
                console.error('LoginActionHandler.js:40 - ', err);
                socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketDeleteMessageNoUserID});
                return;
            }

            socket.join(param.room_id);
            io.of(Settings.options.socketNameSpace).in(param.room_id).emit(Const.emitKeyWordNewUser, user);
            Observer.send(this, Const.notificationNewUser, param);
            
            UsersManager.addUser(param.user_id, param.room_id, user.token);
            UsersManager.pairSocketIDandUserID(param.user_id, socket.id);
            if(Settings.options.sendAttendanceMessage) {
                // save to database
                var newMessage = {
                    user_id: param.user_id,
                    room_id: param.room_id,
                    //local_id: param.local_id,
                    message: '',
                    type: Const.messageNewUser,
                    created_at: Utils.now()
                };

                DatabaseManager.messageModel.saveNewMessage(newMessage, function(err, message){
                    if(err) {
                        console.error('LoginActionHandler.js:60 - ', err);
                        socket.emit(Const.emitKeyWordSocketError, {code:Const.resCodeSocketUnknownError});
                        return;
                    }

                    io.of(Settings.options.socketNameSpace).in(param.room_id).emit(Const.emitKeyWordNewMessage, message);
                });
            }
        });
    });
};

module["exports"] = new LoginActionHandler();