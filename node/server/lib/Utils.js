var _ = require('lodash');

var Utils = {

    randomString: function(len, charSet) {
        charSet = charSet || 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        var randomString = '';
        
        for (var i = 0; i < len; i++) {
        	var randomPoz = Math.floor(Math.random() * charSet.length);
        	randomString += charSet.substring(randomPoz,randomPoz+1);
        }
                
        return randomString;
    },

    isEmpty: function(variable){
        if(_.isUndefined(variable))
            return true;
            
        if(_.isNull(variable))
            return true;
            
        if(_.isString(variable) && _.isEmpty(variable))
            return true;
            
        return false;
    },

    localizeString: function(str){
        return str;
    },

    now: function(){
        return Math.floor(Date.now());
    },

    stripPrivacyParams: function(user) {
        delete user.token;
        delete user.ip_address;
        delete user.password;
        delete user.salt;
        delete user.activation_code;
        delete user.forgotten_password_code;
        delete user.forgotten_password_time;
        delete user.remember_code;
        delete user.last_login;
        delete user.active;

        return user;
    },

    stripPrivacyParamsFromArray: function(users) {
        var result = [];
        var self = this;
        
        _.forEach(users, function(user) {
            result.push(self.stripPrivacyParams(user));
        });

        return result;
    }

};

module["exports"] = Utils;