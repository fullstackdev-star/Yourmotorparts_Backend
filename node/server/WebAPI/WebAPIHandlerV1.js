var express = require('express');
var router = express.Router();
var bodyParser = require("body-parser");
var Settings = require("../lib/Settings");

var WebAPIHandler = {
    init: function(app, express) {
        app.set('port', 5000);
        app.use(Settings.options.urlPrefix, express.static(__dirname + '/../../../public/client'));
        app.use(Settings.options.urlAdminPrefix, express.static(__dirname + '/../../../public/admin'));
        app.use(bodyParser.json());

        // HTTP Api Routes
        router.use("/v1/user/login", require('./LoginHandler'));
        router.use("/v1/user/rooms", require('./RoomsUserListHandler'));
        router.use("/v1/user/room", require('./UserListHandler'));

        WebAPIHandler.router = router;
        app.use(Settings.options.urlPrefix, router);
    }
};

module["exports"] = WebAPIHandler;