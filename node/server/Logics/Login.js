var express = require('express');
var router = express.Router();
var async = require('async');
var formidable = require('formidable');
var fs = require('fs-extra');
var path = require('path');
var mime = require('mime');
var bodyParser = require("body-parser");
var path = require('path');
var _ = require('lodash');

var DatabaseManager = require("../lib/DatabaseManager");
var Utils = require("../lib/Utils");
var SocketAPIHandler = require('../SocketAPI/SocketAPIHandler');
var RoomModel = require("../Models/RoomModel");
var UserModel = require("../Models/UserModel");
var RoomUserModel = require("../Models/RoomUserModel");
var Settings = require("../lib/Settings");
var Const = require("../const");

var LoginLogic = {

    execute : function(param, onSuccess, onError){

        var name = param.name;
        var photo = param.avatar_url;
        var roomID = param.room_id;
        var userID = param.user_id;
          
        if(Utils.isEmpty(name)) {
            if(onError)
                onError(null,Const.resCodeLoginNoName);

            return;
        }
        
        if(Utils.isEmpty(photo)){
            photo = Settings.options.noavatarImg;
        }
        
        if(Utils.isEmpty(roomID)){
            if(onError)
                onError(null,Const.resCodeLoginNoRoomID);

            return;
        }
        
        if(Utils.isEmpty(userID)) {
            if(onError)
                onError(null, Const.resCodeLoginNoUserID);
            
            return;
        }
        
        // create token
        var token = Utils.randomString(24);
        RoomModel.findRoomById(roomID, function (err, room) {
            if(room == null) {
                var newRoom = {
                    name: roomID,
                    avatar_url: photo,
                    creator_id: userID,
                    created_at: Utils.now()
                };

                DatabaseManager.roomModel.createRoom(newRoom, function(err, room) {
                    if(err) {
                        console.log('Login.js', err);

                    } else {
                        console.log('Login.js', "room:" + newRoom.name + "created");
                    }
                });
            }
        });

        onSuccess(token);
    }

};

module["exports"] = LoginLogic;