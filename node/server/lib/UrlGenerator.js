var Settings = require('./Settings');
var CONST = require("../const");

(function(global) {
    var UrlGenerator = {
        getUser: function(user_id) {
            return Settings.options.apiBaseUrl + CONST.URL_GET_USER + '/' + user_id;
        },
        getUsersByIDs: function () {
            return Settings.options.apiBaseUrl + CONST.URL_POST_USERS_BY_IDS;
        },
        createRoom: function() {
            return Settings.options.apiBaseUrl + CONST.URL_POST_CREATE_ROOM;
        },
        updateRoom: function(){
            return Settings.options.apiBaseUrl + CONST.URL_POST_UPDATE_ROOM;
        },
        getRoom: function(room_id){
            return Settings.options.apiBaseUrl + CONST.URL_GET_ROOM + '/' + room_id;
        },
        getRoomsByIDs: function () {
            return Settings.options.apiBaseUrl + CONST.URL_POST_ROOMS_BY_IDS;
        },
        getRoomsByUserID: function (user_id) {
            return Settings.options.apiBaseUrl + CONST.URL_GET_ROOMS_BY_USERID + '/' + user_id;
        },
        getRoomUserByRoomUserID: function (room_user_id) {
            return Settings.options.apiBaseUrl + CONST.URL_GET_ROOM_USER_BY_ROOM_USER_ID + '/' + room_user_id;
        },
        createRoomUser: function () {
            return Settings.options.apiBaseUrl + CONST.URL_POST_CREATE_ROOM_USER;
        },
        getRoomUserByRoomIDandUserID: function () {
            return Settings.options.apiBaseUrl + CONST.URL_POST_ROOM_USER_BY_ROOM_ID_AND_USER_ID;
        },
        getRoomUsersByRoomID: function () {
            return Settings.options.apiBaseUrl + CONST.URL_GET_ROOM_USERS_BY_ROOM_ID;
        },
        getRoomUsersByUserID: function () {
            return Settings.options.apiBaseUrl + CONST.URL_GET_ROOM_USERS_BY_USER_ID;
        },
        getRoomUsersByRoomIDs: function () {
            return Settings.options.apiBaseUrl + CONST.URL_POST_ROOM_USERS_BY_ROOM_IDS;
        },
        getRoomUsersByUserIDs: function () {
            return Settings.options.apiBaseUrl + CONST.URL_POST_ROOM_USERS_BY_USER_IDS;
        },

        saveNewMessage: function(){
            return Settings.options.apiBaseUrl + CONST.URL_POST_SAVE_NEW_MESSAGE;
        },
        sendPushToOfflineUsers: function () {
            return Settings.options.apiBaseUrl + CONST.URL_POST_SEND_PUSH_TO_OFFLINE_USERS;
        },
        getMessage: function(message_id){
            return Settings.options.apiBaseUrl + CONST.URL_GET_MESSAGE + '/' + message_id;
        },
        getRoomMessages: function(){
            return Settings.options.apiBaseUrl + CONST.URL_POST_ROOM_MESSAGE;
        },
        addSeenMessageBy: function(){
            return Settings.options.apiBaseUrl + CONST.URL_POST_ADD_SEEN_MESSAGE_BY;
        },
        deleteMessage: function(){
            return Settings.options.apiBaseUrl + CONST.URL_POST_DELETE_MESSAGE;
        },

        userList: function(room_id){
            return Settings.options.apiBaseUrl + CONST.URL_API_USERS + '/' + room_id;
        },
        messageList: function(room_id, last_message_id){
            return Settings.options.apiBaseUrl + CONST.URL_API_PAST_MESSAGE + '/' + room_id + '/' + last_message_id;
        },
        uploadFile: function(){
            return Settings.options.apiBaseUrl + CONST.URL_API_UPLOAD_FILE;
        },
        downloadFile: function(fileID){
            return Settings.options.apiBaseUrl + CONST.URL_API_DOWNLOAD_FILE + '/' + fileID;
        },
        sendFile: function(){
            return Settings.options.apiBaseUrl + CONST.URL_API_SEND_FILE;
        },
        stickerList: function(){
            return Settings.options.apiBaseUrl + CONST.URL_API_STICKER_LIST;
        }
    };

    // Exports ----------------------------------------------
    module["exports"] = UrlGenerator;

})((this || 0).self || global);