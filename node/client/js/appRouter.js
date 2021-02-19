/**
 * Created by Administrator on 6/16/2017.
 */
var _ = require('lodash');
var Backbone = require('backbone');
Backbone.$ = $;
var Settings = require('./libs/Settings');
var Config = require('./init.js');
var U = require('./libs/utils.js');
var Cookies = require('js-cookie');
// add some dummy functions to pass IE8
U.ie8Fix();
var LoginUserManager = require('./libs/loginUserManager.js');
var Const = require('./consts.js');

// setting up router
var AppRouter = Backbone.Router.extend({
    routes: {
        "login": "loginRoute",
        "colors": "colorsRoute",
        "main": "mainRoute",
        "admin": "adminRoute",
        "*actions": "defaultRoute"
    }
});

// Initiate the router
var app_router = new AppRouter;
app_router.on('route:defaultRoute', function(actions) {
    var queryInfo = U.getURLQuery();
    Settings.options = Config;

    if(!_.isEmpty(queryInfo.params)) {
        var bootOptions = JSON.parse(queryInfo.params);
        var user = bootOptions.user;
        Settings.options = _.merge(Config, bootOptions.config);

        if(!_.isEmpty(user) &&
            !_.isEmpty(user.id) &&
            !_.isEmpty(user.name) &&
            !_.isEmpty(user.room_id)) {

            app.login(
                user.id,
                user.name,
                user.photo,
                user.room_id,
                function() {
                    var MainView = require('./Views/Main/MainView.js');
                    var view = new MainView({
                        'el': Config.defaultContainer
                    });
                }
            );
        }
    } else {
        U.goPage('login');
    }
});

app_router.on('route:loginRoute', function(actions) {
    if(_.isEmpty(Settings.options))
        Settings.options = Config;

    var LoginView = require('./Views/Login/LoginView.js');
    var view = new LoginView({
        'el': Config.defaultContainer
    });
});

app_router.on('route:colorsRoute', function(actions) {
    var ColorsView = require('./Views/Colors/ColorsView.js');
    var view = new ColorsView({
        'el': Config.defaultContainer
    });
});

app_router.on('route:mainRoute', function(actions) {
    if(_.isEmpty(Settings.options))
        Settings.options = Config;

    if(_.isNull(LoginUserManager.user)) {
        var loginInfo = Cookies.getJSON(Const.COOKIE_KEY_LOGIN_INFO);

        if(_.isUndefined(loginInfo))
            U.goPage('login');

        else {
            app.login(
                loginInfo.user_id,
                loginInfo.name,
                loginInfo.photo,
                loginInfo.room_id,
                function(){
                    var MainView = require('./Views/Main/MainView.js');
                    var view = new MainView({
                        'el': Config.defaultContainer
                    });
                }
            );
        }
    } else {
        var MainView = require('./Views/Main/MainView.js');
        var view = new MainView({
            'el': Config.defaultContainer
        });
    }
});

app_router.on('route:adminRoute', function(actions) {
    if(_.isEmpty(Settings.options))
        Settings.options = Config;

    if(_.isNull(LoginUserManager.user)){
        var loginInfo = Cookies.getJSON(Const.COOKIE_KEY_LOGIN_INFO);

        if(_.isUndefined(loginInfo))
            U.goPage('login');

        else {
            app.login(
                loginInfo.user_id,
                loginInfo.name,
                loginInfo.photo,
                loginInfo.room_id,
                function(){
                    var MainView = require('./Views/Main/MainView.js');
                    var view = new MainView({
                        'el': Config.defaultContainer
                    });
                }
            );
        }
    } else {
        var MainView = require('./Views/Main/MainView.js');
        var view = new MainView({
            'el': Config.defaultContainer
        });
    }
});

module["exports"] = AppRouter;
