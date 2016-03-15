var _ = require('lodash');
var Const = require('../const.js');
var Util = require('../lib/Utils');
var Settings = require("../lib/Settings");
var WebAPIManager = require('../lib/WebAPIManager');
var UrlGenerator = require('../lib/UrlGenerator');

var RoomUserModel = function () {
};

RoomUserModel.prototype.connection = null;
RoomUserModel.prototype.init = function(connection) {
    this.connection = connection;
    return this.connection;
};

RoomUserModel.prototype.createNewRoomUser = function (data, callBack) {
    //console.log("RoomUserModel.js:19", data);
    var query = "INSERT INTO chat_room_users SET ?";
    var newRoomUser = {
        room_user_id: data.roomUserId,
        room_id: data.room_id,
        user_id: data.user_id,
        created_at: Util.now()
    };

    this.connection.query(query, newRoomUser, function (error, results, fields) {
        if (error) {
            console.error('RoomUserModel.js:30 - error', error);
        }

        data.id = results.insertId;
        callBack(error, data);
    });
};

RoomUserModel.prototype.findRoomUserByRoomIdAndUserId = function (roomID, userID, callBack) {
    var query = "SELECT * FROM chat_room_users WHERE ? ";
    var where = {
        room_id:roomID,
        user_id:userID //new RegExp("^" + userId + "$", "g")
    };

    this.connection.query(query, where, function (error, results, fields) {
        if (error) {
            console.error('RoomUserModel.js:47 - error', error);
        }

        //console.log('RoomUserModel.js:50 - ', results[0].username);
        callBack(false, results[0]);
    });
};

RoomUserModel.prototype.findRoomUsersByRoomId = function (roomID, callBack) {
    //console.log("RoomUserModel.js:56", UrlGenerator.getRoomUsersByRoomID(roomId));
    var query = "SELECT * FROM chat_room_users WHERE room_id = ? ";
    this.connection.query(query, roomID, function (error, results, fields) {
        if (error) {
            console.error('RoomUserModel.js:47 - error', error);
        }

        //console.log('RoomUserModel.js:80 - ', results[0]);
        callBack(false, results);
    });
};

RoomUserModel.prototype.findRoomUsersByUserId = function (userID, callBack) {
    var query = "SELECT * FROM chat_room_users WHERE user_id = ? ";
    this.connection.query(query, userID, function (error, results, fields) {
        if (error) {
            console.error('RoomUserModel.js:47 - error', error);
        }

        //console.log('RoomUserModel.js:80 - ', results[0]);
        callBack(false, results);
    });
};

module["exports"] = new RoomUserModel();