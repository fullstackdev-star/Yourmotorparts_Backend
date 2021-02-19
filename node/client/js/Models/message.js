var Backbone = require('backbone');
var _ = require('lodash');
var User = require('./user');

(function(global) {
    "use strict;"

    // Class ------------------------------------------------
    var MessageModel = Backbone.Model.extend({
        defaults: {
            id: "",
            local_id: "",
            user_id: "",
            message: "",
            type: 1,
            created_at: 0,
            deleted_at: 0,
            user: null,
            file:{
                file:null,
                thumb:null
            },
            location: {
                    lat: "",
                    lng: ""
            },
            seenBy:[],
            status: 0
        },
        initialize: function(){
    
        },
        toObject:function(){
            var obj = $.extend(true, {}, this.attributes);
            obj.user = obj.user.attributes;
            
            var seenByObjAry = [];
            
            _.forEach(obj.seenBy,function(row){
                seenByObjAry.push({
                    user: row.user.attributes,
                    at : row.at
                });
            });
            
            obj.seenBy = seenByObjAry;
            
            return obj;
        }
    });

    var MessageCollection = Backbone.Collection.extend({
        model: MessageModel,
        comparator : function(model) {
            return model.get('created_at');
        },

        findMessageByID : function(messageID){
            return this.findWhere({ "id": messageID });
        },

        findMessageByLocalID : function(localID) {
            return this.findWhere({ "local_id": localID });
        },

        swap: function(messageOld,messageNew) {
            if(messageOld.get('created_at') == messageNew.get('created_at') ||
                messageOld.get('local_id') == messageNew.get('local_id')){
                this.remove(messageOld);
                this.add(messageNew);
            }
        }
    });
    
    var message = {
        Model:MessageModel,
        Collection:MessageCollection
    };
    
    message.modelByResult = function(obj){
        var model = new MessageModel({
            id: obj.id,
            user_id: obj.user_id,
            local_id: obj.local_id,
            message: obj.message,
            type: obj.type,
            file: obj.file,
            location: obj.location,
            created_at: obj.created_at,
            deleted_at: obj.deleted_at
        });
        
        if(!_.isUndefined(obj.seenBy)){
            var seenByArray = [];
            _.forEach(obj.seenBy, function(seenByRow){
                if(!_.isNull(seenByRow.user) && !_.isUndefined(seenByRow.user)){
                    var userModel = User.modelByResult(seenByRow.user);
                    seenByArray.push({
                        user: userModel,
                        at: seenByRow.at
                    });
                }
            });
            
            model.set('seenBy',seenByArray);
        }
        
        if(!_.isNull(obj.user) && !_.isUndefined(obj.user)){
            var userModel = User.modelByResult(obj.user);
        }
        
        model.set('user',userModel);
        return model;
    };

    message.collectionByResult = function(obj){
        if(!_.isArray(obj))
            return null;
        
        var aryForCollection = [];
        _.each(obj,function(row){
            aryForCollection.push(message.modelByResult(row));

        });
        
        return new MessageCollection(aryForCollection);
    };
    
    module["exports"] = message;

})((this || 0).self || global);
