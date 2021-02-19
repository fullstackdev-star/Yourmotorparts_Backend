var _ = require('lodash');
var Const = require('../const.js');
var Settings = require("../lib/Settings");
var Utils = require("../lib/Utils");

var UserModel = function () {
};

UserModel.prototype.connection = null;
UserModel.prototype.init = function(connection) {
    this.connection = connection;
    return this.connection;
};

UserModel.prototype.findUserById = function (id, callBack) {
    var query = "SELECT * FROM users WHERE id = " + id;
    this.connection.query(query, function (error, results, fields) {
        if (error) {
            console.error('UserModel.js:19 - error', error);
        }

        //console.log('UserModel.js:22 - ', results[0].username);
        var user = results[0];
        if(user.photo && !_.includes(user.photo, "http://")) {
            user.photo = Settings.options.avatarPath + user.photo;
        }
        Utils.stripPrivacyParams(user);
        callBack(false, user);
    });
};

UserModel.prototype.findUsersByIds = function (aryId, callBack) {
    var userIDs = "";
    //console.log("UserModel.js:29", aryId);
    if(aryId.length==0) {
        callBack(Const.resCodeLoginNoRoomID, {});
        return;
    }

    aryId.forEach(function (userID) {
        if(userIDs.length > 0) {
            userIDs += "," + userID;
        } else {
            userIDs = userID
        }
    });

    //console.log("UserModel.js:43", {ids: userIDs});
    var query = "SELECT * FROM users WHERE id IN (" + userIDs + ")";
    this.connection.query(query, function (error, results, fields) {
        if (error) {
            console.error('UserModel.js:48 - error', error);
        }

        //console.log('UserModel.js:50 - ', results);
        var users = [];
        _.forEach(results, function(row) {
            if(row.photo && !_.includes(row.photo, "http://")) {
                row.photo = Settings.options.avatarPath + row.photo;
            }
            users.push(row);
        });
        Utils.stripPrivacyParamsFromArray(users);
        callBack(false, results);
    });

};

module["exports"] = new UserModel();