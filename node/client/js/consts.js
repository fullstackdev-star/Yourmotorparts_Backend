var Const = {
    EVENT_ON_MESSAGE: 'event_start_message',
    EVENT_ON_IMAGE: 'event_start_image',
    EVENT_ON_LOGIN: 'event_login',
    EVENT_ON_LOGOUT: 'event_logout',
    EVENT_ON_LOGOUT_NOTIFY: 'event_logout_notify',
    EVENT_ON_LOGIN_NOTIFY: 'event_login_notify',
    EVENT_ON_TYPING: 'event_typing',
    EVENT_ON_MESSAGE_UPDATED: 'event_message_updated',
    EVENT_ON_MESSAGE_DELETED: 'event_message_deleted',
    EVENT_MESSAGE_SELECTED: 'event_message_selected',
    EVENT_ON_GLOBAL_CLICK: 'event_global_click',

    EMIT_KEYWORD_SEND_MESSAGE : "sendMessage",
    EMIT_KEYWORD_NEW_USER : "newUser",
    EMIT_KEYWORD_NEW_MESSAGE : "newMessage",
    EMIT_KEYWORD_SEND_TYPING : "sendTyping",
    EMIT_KEYWORD_OPEN_MESSAGE : "openMessage",
    EMIT_KEYWORD_MESSAGE_UPDATED : "messageUpdated",
    EMIT_KEYWORD_MESSAGE_DELETED : "messageDeleted",
    EMIT_KEYWORD_DELETE_MESSAGE : "deleteMessage",
    EMIT_KEYWORD_USER_LEFT : "userLeft",
    EMIT_KEYWORD_SOCKET_ERROR : "socketError",
    
    URL_LOGIN: "user/node_login",
    URL_API_USERS: "user/list",
    URL_API_PAST_MESSAGE: "chat/message_list",
    URL_API_UPLOAD_FILE: "chat/upload_file",
    URL_API_DOWNLOAD_FILE: "chat/download_file",
    URL_API_SEND_FILE: "chat/message_send_file",
    URL_API_STICKER_LIST: "chat/stickers",

    COOKIE_KEY_LOGIN_INFO: "cookie_login_info",
    
    MESSAGE_TYPE_TEXT : 1,
    MESSAGE_TYPE_FILE : 2,
    MESSAGE_TYPE_LOCATION : 3,
    MESSAGE_TYPE_CONTACT : 4,
    MESSAGE_TYPE_STICKER : 5,
    MESSAGE_TYPE_NEW_USER : 1000,
    MESSAGE_TYPE_USER_LEAVE : 1001,
    MESSAGE_TYPE_FILE_UPLOADIND : 10000,
    MESSAGE_TYPE_TYPING : 10001,
    
    MESSAGE_STATUS_SENDING: 0,
    MESSAGE_STATUS_SENT: 1,
    
    TYPING_OFF: 0,
    TYPING_ON:1,
    
    PAGING_ROW: 50,
    
    ERROR_CODES : {
              1000001 : "Name is not provided.",
              1000002 : "Room ID is not provided.",
              1000003 : "User ID is not provided.",
              1000004 : "Room ID is not provided.",
              1000005 : "Roomo ID is not provided.",
              1000006 : "Last Meesage ID is not provided.",
              1000007 : "File not provided.",
              1000008 : "Room ID not provided.",
              1000009 : "User ID is not provided.",
              1000010 : "Type is not provided.",
              1000011 : "File is not provided.",
              1000012 : "Unknown Error",
              1000013 : "User ID is not provided.",
              1000014 : "Message ID is not provided.",
              1000015 : "Room ID is not provided.",
              1000016 : "User ID is not provided.",
              1000017 : "Type is not provided.",
              1000018 : "Message is not provided.",
              1000019 : "Location is not provided.",
              1000020 : "Failed to send message.",
              1000027 : "Invalid token"
    }
};

module.exports = Const;
