// define global functions here
var Config = require('../init');

// MyChat Selector
window.SS = function(sel){
    var selector = Config.defaultContainer + " " + sel;
    return $(selector);
};