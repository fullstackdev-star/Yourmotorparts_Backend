var _ = require('lodash');

var UsersManager = {
    rooms:{},

    addUser: function(userID, roomID, token) {
        var user = {
            user_id: userID,
            room_id: roomID,
            token: token,
            socket_id: ''
        };
         
        if(_.isUndefined(this.rooms[roomID])){
            this.rooms[roomID] = {};
        }

        if(_.isEmpty(this.rooms[roomID])){
            this.rooms[roomID] = {
                users:{}
            };
        }
                        
        if(_.isUndefined(this.rooms[roomID].users[userID]))
            this.rooms[roomID].users[userID] = user;
        
        this.rooms[roomID].users[userID] = user;
    },

    removeUser: function(roomID, userID) {
        delete this.rooms[roomID].users[userID];
    },

    getUsers: function(roomID) {
        if(!this.rooms[roomID])
            this.rooms[roomID] = {};

        var users = this.rooms[roomID].users;
                
        // change to array
        var usersAry = [];
        _.forEach(users, function(row, key) {
            usersAry.push(row);
        });
            
        return usersAry;
    },

    getRoomByUserID: function(userID) {
        var roomsAry = [];
        _.forEach(this.rooms, function(room, roomID) {
            _.forEach(room.users, function(user, key) {
                if(user.id == userID)
                    roomsAry.push(roomID);
            });
        });
        return roomsAry;
    },

    pairSocketIDandUserID: function(userID, socketID){
        _.forEach(this.rooms, function(room, roomID) {
            _.forEach(room.users, function(user) {
                if(user.id == userID)
                    user.socket_id = socketID;
            });
        });
    },

    getUserBySocketID: function(socketID) {
        var userResult = null;
        _.forEach(this.rooms, function(room, roomID) {
            _.forEach(room.users, function(user) {
                if(user.socket_id == socketID)
                    userResult = user;
            });
        });
        return userResult;
    },

    getRoomBySocketID: function(socketID) {
        var roomResult = null;
        _.forEach(this.rooms, function(room, roomID) {
            _.forEach(room.users, function(user) {
                if(user.socket_id == socketID)
                    roomResult = roomID;
            });
        });
        return roomResult;
    }
};

module["exports"] = UsersManager;