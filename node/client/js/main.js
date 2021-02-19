// import libraries
window.$ = window.jQuery = require('jquery');
var Backbone = require('backbone');
Backbone.$ = $;
var Cookies = require('js-cookie');
require('jquery-colorbox');

var _ = require('lodash');
var bootstrap = require('bootstrap-sass');

require('./libs/global.js');
var JSON = require('JSON2');
var LoginUserManager = require('./libs/loginUserManager.js');
var socketIOManager = require('./libs/socketIOManager');
var Config = require('./init.js');
var Const = require('./consts.js');
var UrlGenerator = require('./libs/urlGenerator');
var LocalizationManager = require('./libs/localizationManager.js');
var WebAPIManager = require('./libs/webAPIManager');
var ErrorDialog = require('./Views/Modals/ErrorDialog/ErrorDialog');
var ProcessingDialog = require('./Views/Modals/ProcessingDialog/ProcessingDialog');
var ViewHelpers = require('./libs/viewHelpers.js');
var Settings = require('./libs/Settings');

ViewHelpers.attach();

// app instance (global)
window.app = {
    login:function(userID, name, photo, roomID, callBack){
        socketIOManager.init();
        LocalizationManager.init(Settings.options.lang);
        ProcessingDialog.show();

        WebAPIManager.post(
            UrlGenerator.userLogin(),
            {user_id: userID, name: name, photo: photo, room_id: roomID},
            // success
            function(data) {
                socketIOManager.emit('login', {
                    name : name,
                    photo : photo,
                    room_id : roomID,
                    user_id: userID
                });
    
                LoginUserManager.setLoginUser(name, photo, roomID, data.room_name, userID, data.token);
                var loginInfo = {
                    user_id:userID,
                    name:name,
                    photo:photo,
                    room_id:roomID
                };

                Cookies.set(Const.COOKIE_KEY_LOGIN_INFO, loginInfo);

                if(!_.isUndefined(callBack)){
                    callBack();
                }
            },
            //error
            function(error){
                ProcessingDialog.hide();
            }
        );
    }
};

// disable ajax cache
$.ajaxSetup({
    cache: false
});

// load default language
LocalizationManager.init(Config.lang);

var AppRouter = require('./appRouter.js');

$(function() {
    // Start Backbone history a necessary step for bookmarkable URL's
    Backbone.history.start();
});

window.startSpikaIntoDiv = function() {
    Settings.options = _.merge(Config,window.bootOptions.config);
    Config.defaultContainer = "#" + window.bootOptions.attachTo;
    
    var userID = window.bootOptions.user.id;
    var roomID = window.bootOptions.user.room_id;
    var photo = window.bootOptions.user.photo;
    var name = window.bootOptions.user.name;
    
    app.login(
        userID,
        name,
        photo,
        roomID,
        function(){
            var MainView = require('./Views/Main/MainView.js');
            var view = new MainView({
                'el': Config.defaultContainer
            });
        }
    );

};

