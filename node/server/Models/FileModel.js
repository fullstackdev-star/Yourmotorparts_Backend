var _ = require('lodash');
var Settings = require("../lib/Settings");

var FileModel = function() {
};

FileModel.prototype.connection = null;
FileModel.prototype.init = function(connection) {
    this.connection = connection;
    return this.connection;
};

FileModel.prototype.findFileById = function (id, callBack) {
    var query = "SELECT * FROM chat_files WHERE id = " + id;
    console.log("FileModel.js:15 - ", query);
    this.connection.query(query, function (error, results, fields) {
        if (error) {
            console.error('FileModel.js:17 - error ', id);
            callBack(error, null);

        } else {
            console.log('FileModel.js:23 - ', results[0]);
            callBack(false, results[0]);
        }
    });
};

module["exports"] = new FileModel();