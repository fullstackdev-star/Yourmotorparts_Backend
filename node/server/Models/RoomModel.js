var _ = require('lodash');
var Util = require('../lib/Utils');

var RoomModel = function () {
};

RoomModel.prototype.connection = null;
RoomModel.prototype.init = function(connection) {
    this.connection = connection;
    return this.connection;
};

RoomModel.prototype.findRoomById = function (id, callBack) {
    //console.log("RoomModel.js:14", id);
    var query = "SELECT * FROM chat_rooms WHERE id = " + id;
    this.connection.query(query, function (error, results, fields) {
        if (error) {
            console.error('RoomModel.js:18 - error', error);
            callBack(error, results);
            return;
        }

        var room = results[0];
        if(room.avatar_url && !_.includes(room.avatar_url, "http://")) {
            room.avatar_url = Settings.options.roomAvatarPath + room.avatar_url;
        }
        callBack(error, room);
    });
};

RoomModel.prototype.createRoom = function (roomData, callBack) {
    //console.log("RoomModel.js:25", roomData);
    var query = "INSERT INTO chat_rooms SET ?";
    this.connection.query(query, roomData, function (error, results, fields) {
        if (error) {
            console.error('RoomModel.js:29 - error', error);
            callBack(error, null);
            return;
        }

        roomData.id = results.insertId;
        if(roomData.avatar_url && !_.includes(roomData.avatar_url, "http://")) {
            roomData.avatar_url = Settings.options.roomAvatarPath + roomData.avatar_url;
        }
        callBack(error, roomData);
    });
};

RoomModel.prototype.updateRoom = function (roomData, callBack) {
    //console.log("RoomModel.js:39", roomData);
    var query = "UPDATE SET ? FROM chat_rooms WHERE id = ? ";
    var post = {
        name: roomData.name,
        avatar_url: roomData.avatar_url?roomData.avatar_url:""
    };

    this.connection.query(query, [post, roomData.room_id], function (error, results, fields) {
        if (error) {
            console.error('UserModel.js:47 - error', error);
            callBack(error, null);
            return;
        }

        roomData.id = results.insertId;
        if(roomData.avatar_url && !_.includes(roomData.avatar_url, "http://")) {
            roomData.avatar_url = Settings.options.roomAvatarPath + roomData.avatar_url;
        }
        callBack(error, roomData);
    });
};

module["exports"] = new RoomModel();