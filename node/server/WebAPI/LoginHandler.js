var express = require('express');
var router = express.Router();
var _ = require('lodash');
var RequestHandlerBase = require("./RequestHandlerBase");
var Const = require("../const");
var LoginLogic = require("../Logics/Login");

var LoginHandler = function() {
};

_.extend(LoginHandler.prototype, RequestHandlerBase.prototype);

LoginHandler.prototype.attach = function(router) {
    var self = this;

    /**
     * @api {post} /user/login Get api token
     * @apiName Login
     * @apiGroup WebAPI
     * @apiDescription Login to the room specified in request, and get token for the room.

     * @apiParam {name} Users Name
     * @apiParam {photo} URL of avatar image
     * @apiParam {roomID} Room Name to login
     * @apiParam {userID} User's Unique ID
     *
     * @apiSuccess {String} Token
     * @apiSuccess {String} User Model of loginned user
     *     
     * @apiSuccessExample Success-Response:
{
	code: 1,
	data: {
		token: 'FPzdinKSETyXrx0zoxZVYoVt',
		user: {
			_id: '564b128a94b8f880877eb47f',
			userID: 'test',
			name: 'test',
			avatarURL: 'test',
			token: 'zJd0rlkS6OWk4mBUDTL5Eg5U',
			created: 1447760522576,
			__v: 0
		}
	}
}

    */
    router.post('/', function(request,response) {

        LoginLogic.execute(request.body, function(result) {

            self.successResponse(response,Const.responsecodeSucceed, {
                token: result.token,
                user: result.user
            });
            
        }, function(err, code) {
            if(err) {
                self.errorResponse(
                    response,
                    Const.httpCodeSeverError
                );
                
            } else {
                self.successResponse(response, code);
            }
        });
    });

};

new LoginHandler().attach(router);
module["exports"] = router;
