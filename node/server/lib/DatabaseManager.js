var _ = require('lodash');
var init = require('../init.js');
var mysql = require('mysql');
var connection = mysql.createConnection({
    host     : init.dbHost,
    user     : init.dbUser,
    password : init.dbPassword,
    database : init.dbName
});

var DatabaseManager = {
    roomModel:null,
    messageModel:null,
    userModel:null,
    fileModel:null,
    roomUserModel:null,
    messageSeenModel:null,

    init: function(options) {
        this.roomModel = require('../Models/RoomModel');
        this.roomModel.init(connection);

        this.messageModel = require('../Models/MessageModel');
        this.messageModel.init(connection);

        this.userModel = require('../Models/UserModel');
        this.userModel.init(connection);

        this.fileModel = require('../Models/FileModel');
        this.fileModel.init(connection);

        this.roomUserModel = require('../Models/RoomUserModel');
        this.roomUserModel.init(connection);

        this.messageSeenModel = require('../Models/MessageSeenModel');
        this.messageSeenModel.init(connection);
    }
};

module["exports"] = DatabaseManager;