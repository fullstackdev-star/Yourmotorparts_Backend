var express = require('express');
var router = express.Router();
var _ = require('lodash');
var RequestHandlerBase = require("./RequestHandlerBase");
var UsersManager = require("../lib/UsersManager");
var Utils = require("../lib/Utils");
var Const = require("../const");
var tokenChecker = require('../lib/Auth');
var UserModel = require('../Models/UserModel')

var UserListHandler = function () {
};

_.extend(UserListHandler.prototype, RequestHandlerBase.prototype);

UserListHandler.prototype.attach = function (router) {
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

    router.get('/:roomID', tokenChecker, function (request, response) {
        var roomID = request.params.roomID;

        if (_.isEmpty(roomID)) {
            self.successResponse(response, Const.resCodeUserListNoRoomID);
            return;
        }

        var roomUsers = UsersManager.getUsers(roomID);
        if(_.size(roomUsers)>0) {
            var userIds = [];
            _.forEach(roomUsers, function (row, key) {
                userIds.push(row.user_id);
            });
            UserModel.findUsersByIds(userIds, function (err, data) {
                self.successResponse(response,
                    Const.responsecodeSucceed,
                    Utils.stripPrivacyParamsFromArray(data));
            });

        } else {
            self.successResponse(response,
                Const.responsecodeSucceed,
                Utils.stripPrivacyParamsFromArray(roomUsers));
        }
    });
};

new UserListHandler().attach(router);
module["exports"] = router;
