var Settings = require("../lib/Settings");

var SocketAPIHandler = {
    io:null,
    nsp : null,
    init: function(io) {
        this.io = io;
        this.nsp = io.of(Settings.options.socketNameSpace);
        this.nsp.on('connection', function(socket) {
            console.log('socket connected');

            require('./DisconnectActionHandler').attach(io,socket);
            require('./LoginActionHandler').attach(io,socket);
            require('./SendMessageActionHandler').attach(io,socket);
            require('./SendTypingActionHandler').attach(io,socket);
            require('./OpenMessageActionHandler').attach(io,socket);
            require('./DeleteMessageActionHandler').attach(io,socket);
        });
    }
};

module["exports"] = SocketAPIHandler;