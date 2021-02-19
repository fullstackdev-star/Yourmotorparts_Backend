var _ = require('lodash');

var UsersManager = require("../lib/UsersManager");
var DatabaseManager = require("../lib/DatabaseManager");
var Utils = require("../lib/Utils");
var Const = require("../const");
var SocketHandlerBase = require("./SocketHandlerBase");
var UserModel = require("../Models/UserModel");
var Settings = require("../lib/Settings");

var DisconnectActionHandler = function(){
};

_.extend(DisconnectActionHandler.prototype, SocketHandlerBase.prototype);

DisconnectActionHandler.prototype.attach = function(io, socket) {

    socket.on('disconnect', function () {

        var roomID = UsersManager.getRoomBySocketID(socket.id);
        var roomUser = UsersManager.getUserBySocketID(socket.id);
        //console.log('DisconnectActionHandler.js:20 - socket:', socket);
        if(!_.isNull(roomUser)) {
            UsersManager.removeUser(roomID, roomUser.user_id);
            socket.leave(roomID);
            if(Settings.options.sendAttendanceMessage) {
                //save as message
                UserModel.findUserById(roomUser.user_id, function (err, user) {
                    io.of(Settings.options.socketNameSpace).in(roomID).emit(Const.emitKeyWordUserLeft, user);

                    // save to database
                    var newMessage = {
                        user_id: user.id,
                        room_id: roomID,
                        local_id: Utils.randomString(),
                        message:  '',
                        type: Const.messageUserLeave,
                        created_at: Utils.now(),
                        user:user
                    };

                    DatabaseManager.messageModel.saveNewMessage(newMessage, function(err, message) {
                        if(err) {
                            console.log('DisconnectActionHandler.js:41 - err', err);
                            return;
                        }

                        io.of(Settings.options.socketNameSpace).in(roomID).emit(Const.emitKeyWordNewMessage, message);
                    });
                });
            }

        } else {
            console.log('DisconnectActionHandler.js:50 - err', "Non User");
        }
    });

};


module["exports"] = new DisconnectActionHandler();