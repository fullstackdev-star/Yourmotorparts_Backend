var express = require('express');
var router = express.Router();
var _ = require('lodash');
var RequestHandlerBase = require("./RequestHandlerBase");
var UsersManager = require("../lib/UsersManager");
var Utils = require("../lib/Utils");
var Const = require("../const");
var tokenChecker = require('../lib/Auth');
var WebAPIManager = require('../lib/WebAPIManager');
var UrlGenerator = require('../lib/UrlGenerator');
var RoomUserModel = require("../Models/RoomUserModel");

var RoomsUserListHandler = function () {
};

_.extend(RoomsUserListHandler.prototype, RequestHandlerBase.prototype);

RoomsUserListHandler.prototype.attach = function (router) {
    var self = this;

    /**
     * @api {get} /user/list/:roomID  Get List of Users in room
     * @apiName Get User List
     * @apiGroup WebAPI
     * @apiDescription Get list of users who are currently in the room

     * @apiParam {String} roomID ID of the room
     *
     *
     * @apiSuccessExample Success-Response:
     {
       "code": 1,
       "data": [
         {
           "userID": "test",
           "name": "test",
           "photo": "http://localhost:8080/img/noavatar.png",
           "roomID": "test",
           "socketID": "Znw8kW-ulKXBMoVAAAAB"
         },
         {
           "userID": "test2",
           "name": "test2",
           "photo": "http://localhost:8080/img/noavatar.png",
           "roomID": "test",
           "socketID": "xIBEwT0swJwjcI2hAAAC"
         }
       ]
     }
     */

    router.get('/:userID', tokenChecker, function (request, response) {
        var userID = request.params.userID;

        RoomUserModel.findRoomUsersByUserId(userID, function (error, data) {
            if(error) {
                console.error('RoomsUserListHandler.js:56 - error', error);
                return;
            }

            var usersAry = [];
            _.forEach(data, function(row, key) {
                var roomID = row.room_id;
                var roomUsers = UsersManager.getUsers(roomID);
                usersAry.push({
                    room_user : row,
                    users : Utils.stripPrivacyParamsFromArray(roomUsers)
                });
            });

            self.successResponse(response,
                Const.responsecodeSucceed,
                usersAry);
        });
    });

};

new RoomsUserListHandler().attach(router);
module["exports"] = router;
