(function(global) {
    "use strict";

    var os = require('os');
    var ifaces = os.networkInterfaces();
    var localIPAddress = '';
    Object.keys(ifaces).forEach(function (ifname) {
        var alias = 0;
        ifaces[ifname].forEach(function (iface) {
            if ('IPv4' == iface.family && iface.internal == false) {
                if (alias >= 1) {
                    // this single interface has multiple ipv4 addresses
                    console.log("init.js:29 - " + ifname + ':' + alias, iface.address);
                } else {
                    // this interface has only one ipv4 adress
                    console.log("init.js:32 - " + ifname, iface.address);
                }
                ++alias;

                if(iface.address.indexOf("10.0.0.") > -1) {
                    localIPAddress = iface.address.replace(/\s/g, '');
                    console.log("init.js:38 - ", localIPAddress);
                }
            }
        });
    });

    // Class ------------------------------------------------
    var Config = {};

    Config.host = localIPAddress;
    Config.port = 5000;
    Config.urlPrefix = '/YourMotorParts';
    Config.urlAdminPrefix = Config.urlPrefix + '/admin';
    Config.socketNameSpace = '/MyChat';
    Config.apiBaseUrl = "http://" + Config.host + Config.urlPrefix + "/api/";

    Config.assetsPath = "http://" + Config.host + Config.urlPrefix + "/assets/uploads/";
    Config.avatarPath = Config.assetsPath + "profile/photo/";
    Config.roomAvatarPath = Config.assetsPath + "chat_room/";

    Config.imageDownloadURL = "http://" + Config.host + "/:" + Config.port + Config.urlPrefix + "/media/images/";
    Config.noavatarImg = "http://" + Config.host + ":" + Config.port + Config.urlPrefix + "/img/noavatar.png";
    Config.emoticonImgUrl = "http://" + Config.host + ":" + Config.port + Config.urlPrefix + "/img/resources/emoticons/";
    Config.categoryImgUrl = "http://" + Config.host + ":" + Config.port + Config.urlPrefix + "/img/resources/categories/";

    Config.chatDatabaseUrl = "mongodb://localhost/my_chat";
    Config.dbCollectionPrefix = "mc_";
    
    Config.uploadDir = 'public/uploads/';
    Config.sendAttendanceMessage = false;
    Config.emoticonImgDir = 'public/img/resources/emoticons/';
    Config.categorieImgDir = 'public/img/resources/categories/';
    
    Config.stickerBaseURL = 'http://' + Config.host;
    Config.stickerAPI = Config.stickerBaseURL + '/api/v2/stickers/56e005b1695213295419f5df';

    Config.dbHost = "localhost";
    Config.dbName = "your_motor_parts";
    Config.dbUser = "root";
    Config.dbPassword = "";
    
    // Exports ----------------------------------------------
    module["exports"] = Config;

})((this || 0).self || global);
