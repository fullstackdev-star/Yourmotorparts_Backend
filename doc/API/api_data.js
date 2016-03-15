define({ "api": [
  {
    "type": "get",
    "url": "/user/list/:roomID",
    "title": "Get List of Users in room",
    "name": "Get_User_List",
    "group": "WebAPI",
    "description": "<p>Get list of users who are currently in the room</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "roomID",
            "description": "<p>ID of the room</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": 1,\n  \"data\": [\n    {\n      \"userID\": \"test\",\n      \"name\": \"test\",\n      \"photo\": \"http://localhost:8080/img/noavatar.png\",\n      \"roomID\": \"test\",\n      \"socketID\": \"Znw8kW-ulKXBMoVAAAAB\"\n    },\n    {\n      \"userID\": \"test2\",\n      \"name\": \"test2\",\n      \"photo\": \"http://localhost:8080/img/noavatar.png\",\n      \"roomID\": \"test\",\n      \"socketID\": \"xIBEwT0swJwjcI2hAAAC\"\n    }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "node/server/WebAPI/RoomsUserListHandler.js",
    "groupTitle": "WebAPI"
  },
  {
    "type": "get",
    "url": "/user/list/:roomID",
    "title": "Get List of Users in room",
    "name": "Get_User_List",
    "group": "WebAPI",
    "description": "<p>Get list of users who are currently in the room</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "String",
            "optional": false,
            "field": "roomID",
            "description": "<p>ID of the room</p>"
          }
        ]
      }
    },
    "success": {
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n  \"code\": 1,\n  \"data\": [\n    {\n      \"userID\": \"test\",\n      \"name\": \"test\",\n      \"photo\": \"http://localhost:8080/img/noavatar.png\",\n      \"roomID\": \"test\",\n      \"socketID\": \"Znw8kW-ulKXBMoVAAAAB\"\n    },\n    {\n      \"userID\": \"test2\",\n      \"name\": \"test2\",\n      \"photo\": \"http://localhost:8080/img/noavatar.png\",\n      \"roomID\": \"test\",\n      \"socketID\": \"xIBEwT0swJwjcI2hAAAC\"\n    }\n  ]\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "node/server/WebAPI/UserListHandler.js",
    "groupTitle": "WebAPI"
  },
  {
    "type": "post",
    "url": "/user/login",
    "title": "Get api token",
    "name": "Login",
    "group": "WebAPI",
    "description": "<p>Login to the room specified in request, and get token for the room.</p>",
    "parameter": {
      "fields": {
        "Parameter": [
          {
            "group": "Parameter",
            "type": "name",
            "optional": false,
            "field": "Users",
            "description": "<p>Name</p>"
          },
          {
            "group": "Parameter",
            "type": "photo",
            "optional": false,
            "field": "URL",
            "description": "<p>of avatar image</p>"
          },
          {
            "group": "Parameter",
            "type": "roomID",
            "optional": false,
            "field": "Room",
            "description": "<p>Name to login</p>"
          },
          {
            "group": "Parameter",
            "type": "userID",
            "optional": false,
            "field": "User",
            "description": "<p>'s Unique ID</p>"
          }
        ]
      }
    },
    "success": {
      "fields": {
        "Success 200": [
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "Token",
            "description": ""
          },
          {
            "group": "Success 200",
            "type": "String",
            "optional": false,
            "field": "User",
            "description": "<p>Model of loginned user</p>"
          }
        ]
      },
      "examples": [
        {
          "title": "Success-Response:",
          "content": "{\n\tcode: 1,\n\tdata: {\n\t\ttoken: 'FPzdinKSETyXrx0zoxZVYoVt',\n\t\tuser: {\n\t\t\t_id: '564b128a94b8f880877eb47f',\n\t\t\tuserID: 'test',\n\t\t\tname: 'test',\n\t\t\tavatarURL: 'test',\n\t\t\ttoken: 'zJd0rlkS6OWk4mBUDTL5Eg5U',\n\t\t\tcreated: 1447760522576,\n\t\t\t__v: 0\n\t\t}\n\t}\n}",
          "type": "json"
        }
      ]
    },
    "version": "0.0.0",
    "filename": "node/server/WebAPI/LoginHandler.js",
    "groupTitle": "WebAPI"
  }
] });
