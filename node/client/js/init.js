(function(global) {

    var Config = {};

    Config.host = "10.0.0.7";
    Config.apiBaseUrl = "http://" + Config.host + "/YourMotorParts/api/";
    Config.socketUrl = "http://" + Config.host + ":5000/MyChat";
    
    Config.googleMapAPIKey = "";
    Config.defaultContainer = "#spika-container";
    Config.lang = "en";
    Config.showSidebar = true;
    Config.showTitlebar = true;
    Config.useBothSide = false;
    Config.thumbnailHeight = 256;
    
    // Exports ----------------------------------------------
    module["exports"] = Config;

})((this || 0).self || global);
