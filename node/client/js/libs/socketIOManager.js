var socket = require('socket.io-client');
var Backbone = require('backbone');
var _ = require('lodash');
var CONST = require('../consts');
var Config = require('../init');
var ErrorDialog = require('../Views/Modals/ErrorDialog/ErrorDialog');

(function(global) {
    var socketIOManager = {
        io : null,
        init:function(){
            this.io = socket.connect(Config.socketUrl);
            this.io.on(CONST.EMIT_KEYWORD_SOCKET_ERROR, function(param) {
                if(param.code) {
                    ErrorDialog.show('Error - socketIOManager:15', CONST.ERROR_CODES[param.code]);
                } else {
                    ErrorDialog.show('Error - socketIOManager:17','Unknown Error');
                }
            });
            
            this.io.on(CONST.EMIT_KEYWORD_NEW_USER, function(param) {
                Backbone.trigger(CONST.EVENT_ON_LOGIN_NOTIFY, param);
                // call listener
                if(!_.isEmpty(window.parent.SpikaAdapter) &&
                    !_.isEmpty(window.parent.SpikaAdapter.listener)){
                    var listener = window.parent.SpikaAdapter.listener;
                    if(_.isFunction(listener.onNewUser))
                        listener.onNewMessage(param);
                }
            });

            this.io.on(CONST.EMIT_KEYWORD_USER_LEFT, function(param){
                Backbone.trigger(CONST.EVENT_ON_LOGOUT_NOTIFY, param);
                // call listener
                if(!_.isEmpty(window.parent.SpikaAdapter) &&
                    !_.isEmpty(window.parent.SpikaAdapter.listener)){
                    var listener = window.parent.SpikaAdapter.listener;
                    if(_.isFunction(listener.onUserLeft))
                        listener.onUserLeft(param);
                }
            });

            this.io.on(CONST.EMIT_KEYWORD_NEW_MESSAGE, function(param) {
                console.log('socketIOManager.js:44 - newMessage', param);
                Backbone.trigger(CONST.EVENT_ON_MESSAGE, param);
                // call listener
                if(!_.isEmpty(window.parent.SpikaAdapter) &&
                    !_.isEmpty(window.parent.SpikaAdapter.listener)){
                    var listener = window.parent.SpikaAdapter.listener;
                    
                    if(_.isFunction(listener.onNewMessage))
                        listener.onNewMessage(param);
                }
            }); 

            this.io.on(CONST.EMIT_KEYWORD_SEND_TYPING, function(param){
                Backbone.trigger(CONST.EVENT_ON_TYPING,param);
                 // call listener
                if(!_.isEmpty(window.parent.SpikaAdapter) &&
                    !_.isEmpty(window.parent.SpikaAdapter.listener)){
                    var listener = window.parent.SpikaAdapter.listener;
                    if(_.isFunction(listener.OnUserTyping))
                        listener.OnUserTyping(param);
                }
           }); 
            
            this.io.on('login', function(param){
                Backbone.trigger(CONST.EVENT_ON_LOGIN, param);
            });

            this.io.on('logout', function(param){
                Backbone.trigger(CONST.EVENT_ON_LOGOUT, param);
            });

            this.io.on(CONST.EMIT_KEYWORD_MESSAGE_UPDATED, function(param){
                Backbone.trigger(CONST.EVENT_ON_MESSAGE_UPDATED, param);
 
                 // call listener
                if(!_.isEmpty(window.parent.SpikaAdapter) &&
                    !_.isEmpty(window.parent.SpikaAdapter.listener)){
                    var listener = window.parent.SpikaAdapter.listener;
                    if(_.isFunction(listener.OnMessageChanges))
                        listener.OnMessageChanges(param);
                }
            });

            this.io.on(CONST.EMIT_KEYWORD_MESSAGE_DELETED, function(param){
                Backbone.trigger(CONST.EVENT_ON_MESSAGE_DELETED, param);

                // call listener
                if(!_.isEmpty(window.parent.SpikaAdapter) &&
                    !_.isEmpty(window.parent.SpikaAdapter.listener)){
                    var listener = window.parent.SpikaAdapter.listener;
                    if(_.isFunction(listener.OnMessageDeleted))
                        listener.OnMessageDeleted(param);
                }
            });
        },
        
        emit:function(command,params){
            var command = arguments[0];
            this.io.emit(command, params);
        }
    };
 
    // Exports ----------------------------------------------
    module["exports"] = socketIOManager;

})((this || 0).self || global);