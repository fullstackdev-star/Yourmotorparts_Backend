var User = require('../Models/user.js');

/**
 * LoginUserManager
 * 
 * @class
 */ 
(function(global) {
    var LoginUserManager = {
        user : null,
        room_id : null,
        room_name : null,
        id: 0,
        
        /**
         * Set the user after successfully logged in
         * 
         * @method
         * @name LoginUserManager.setLoginUser
         * @param name
         * @param photo
         * @param roomID
         * @param roomName
         * @param id
         * @param token
        */
        setLoginUser: function(name, photo, roomID, roomName, id, token){
            this.user = new User.Model({
                id: id,
                name:name,
                photo:photo,
                token:token
            });
            
            this.room_id = roomID;
            this.room_name = roomName;
        }
    };

    // Exports ----------------------------------------------
    module["exports"] = LoginUserManager;

})((this || 0).self || global);