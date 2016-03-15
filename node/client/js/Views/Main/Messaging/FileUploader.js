var _ = require('lodash');
var CONST = require('../../../consts');
var U = require('../../../libs/utils.js');
var LoginUserManager = require('../../../libs/loginUserManager.js');
var Message = require('../../../Models/message.js');
var WebAPIManager = require('../../../libs/webAPIManager');
var UrlGenerator = require('../../../libs/urlGenerator');
var socketIOManager = require('../../../libs/socketIOManager');
var browser = require('bowser');
var FileUploadDialog = require('../../Modals/FileUpload/FileUploadDialog');

function FileUploader(options){
    this.parentView = options.view;
}

FileUploader.prototype.parentView = null;

FileUploader.prototype.handleClick = function() {
    if (browser.msie && browser.version < 10) {
        var self = this;
                       
        var params = {
            user_id:LoginUserManager.user.get('id'),
            token:LoginUserManager.user.get('token'),
            local_id:'',
            room_id:LoginUserManager.room_id,
            type:CONST.MESSAGE_TYPE_FILE
        };
        
        FileUploadDialog.show(UrlGenerator.sendFile(), params, function(){
            self.startUploadingFile();
        });
    
    } else {
        SS('#file-input').click();
    }
};

// receives event object for change event for file input
FileUploader.prototype.startUploadingFile = function(event){
    //this.uploadFileIFrame();
    if (browser.msie && browser.version < 10) {
        this.uploadFileIFrame();
        return;
        
    } else {
        files = event.target.files;
        if(files.length > 0){
            this.uploadFileHTML5(files[0]);
        }
    }

};

FileUploader.prototype.uploadFileIFrame = function(){
    document.getElementById('upload-form').target = 'fileupload-dialog-iframe';
    document.getElementById('upload-form').submit();
};

FileUploader.prototype.uploadFileHTML5 = function(file){

    var tempID = '_' + U.getRandomString();
    var self = this;
    
    // insert file upload message
    var message = Message.modelByResult({
        id: tempID,
        local_id: tempID,
        user_id: LoginUserManager.user.get('id'),
        message: 'file uploading 0%',
        type: CONST.MESSAGE_TYPE_FILE_UPLOADIND,
        created_at: U.now(),
        user: LoginUserManager.user.attributes
    });

    if ('name' in file) {
        message.set('filename',file.name);
    }
    else {
        message.set('filename',file.fileName);
    }

    if ('size' in file) {
        message.set('size',Math.floor(file.size / 1024 / 1024 * 100) / 100); // MB
    }
    else {
        message.set('size',Math.floor(file.size / 1024 / 1024 * 100) / 100); // MB
    }
                   
    message.set('uploadProgress',0);
    message.set('isUploading',1);
    
    this.parentView.insertTempMessage(true,message);
    this.parentView.scrollToBottom();
    
    WebAPIManager.fileUpload(
        UrlGenerator.uploadFile(),
        file,

        // progress
        function(progress){
            message.set('uploadProgress', Math.floor(progress * 100));
            self.parentView.updateMessage(message);
        },
                    
        // success
        function(data) {
            console.log('FileUploader.js:108 - ', data);
            socketIOManager.emit(CONST.EMIT_KEYWORD_SEND_MESSAGE, {
                message: data.file.id,
                room_id: LoginUserManager.room_id,
                user_id: LoginUserManager.user.get('id'),
                file: data,
                type: CONST.MESSAGE_TYPE_FILE,
                local_id: tempID,
                attributes: {
                    client: "web"
                }
            });

            self.parentView.updateMessage(message);
        },
        
        //error
        function(error){
            
        }
    );
};


module.exports = FileUploader;


    