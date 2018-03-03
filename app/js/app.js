(function(global) {
    var app = {};

    app.login = function() {
        FB.login(app.onLogin, {scope: "publish_actions,user_posts,user_relationships,user_photos,user_location,user_likes,user_status,user_friends,user_about_me,user_relationship_details,user_tagged_places"});
    };

    app.onLogin = function() {
        console.log("logging");

        FB.api("/me", function(response) {
            var userId = response.id;
            var userName = response.name;
            var me = {
                pid: userId,
                name: userName
            };
            var friends = [];

            async.parallel([
                function(callback) {
                    app.getUserInfo("me", function(info) {
                        me.info = info;
                        callback();
                    });
                },
                function(callback) {
                    app.loadFriends(userId, function(users) {
                        friends.push.apply(friends, users);
                        callback();
                    });
                }
            ], function() {
                var data = [app.getMessage(me)];
                friends.forEach(function(friend) {
                    data.push(app.getMessage(friend));
                });

                app.ajax("/backend/", data, function(err, response) {
                    if(err) {
                        console.error(err);
                    } else {
                        var image = new Image();
                        image.src = "data:image/jpg;base64," + app.base64(response.responseText);
                        app.showImage(image);
                    }
                });
            });
        });
    };

    app.getMessage = function(user) {
        return {
            name: user.name,
            gender: user.info.gender,
            image: user.info.image,
        };
    };

    app.getUserInfo = function(userId, callback) {
        FB.api("/" + userId, { fields: "picture.type(square).width(720).height(720),gender" }, function(response) {
            callback({
                image: response.picture.data.url,
                gender: response.gender
            });
        });
    };

    app.loadFriends = function(userId, callback) {
        FB.api("/me/feed/?fields=from&limit=300", function(response) {
            var users = {};

            for(var i = 0; i < response.data.length; ++i) {
                var pname = response.data[i].from.name;
                var pid = response.data[i].from.id;

                users[pid] = { pid: pid, name: pname, count: (users[pid] ? users[pid].count : 0) + 1 };
            }

            var sortedUsers = Object.keys(users).sort(function(a, b) {
                return users[b].count - users[a].count;
            }).filter(function(pid) {
                return pid != userId;
            }).map(function(pid) {
                return users[pid];
            });

            async.map(sortedUsers.slice(0, 3), function(user, callback) {
                app.getUserInfo(user.pid, function(info) {
                    user.info = info;
                    callback(null, user);
                });
            }, function(err, result) {
                if(err) {
                    console.error(err);
                } else {
                    callback(result);
                }
            });
        });
    };

    app.showImage = function(image) {
        var area = document.querySelector("#imageArea");
        area.appendChild(image);
    };

    app.ajax = function(url, data, callback) {
        var xhr = new global.XMLHttpRequest();

        xhr.onload = function() {
            if (xhr.status >= 200 && xhr.status < 400) {
                callback(null, xhr);
            } else {
                callback(new Error("response error " + xhr.status));
            }
        };

        xhr.onerror = function() {
            callback(new Error("error occured"));
        };

        xhr.open("POST", url, true);
        xhr.setRequestHeader("Content-Type", "application/json");
        //xhr.overrideMimeType('text/plain; charset=x-user-defined');
        xhr.send(JSON.stringify({form: data}));
    };

    app.base64 = function(inputStr) {
       var b64 = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
       var outputStr = "";
       var i = 0;

       while (i<inputStr.length){
          var byte1 = inputStr.charCodeAt(i++) & 0xff;
          var byte2 = inputStr.charCodeAt(i++) & 0xff;
          var byte3 = inputStr.charCodeAt(i++) & 0xff;

          var enc1 = byte1 >> 2;
          var enc2 = ((byte1 & 3) << 4) | (byte2 >> 4);

          var enc3, enc4;
          if (isNaN(byte2)){
			enc3 = enc4 = 64;
          } else{
            enc3 = ((byte2 & 15) << 2) | (byte3 >> 6);
            if (isNaN(byte3)){
               enc4 = 64;
            } else {
                enc4 = byte3 & 63;
            }
          }
          outputStr +=  b64.charAt(enc1) + b64.charAt(enc2) + b64.charAt(enc3) + b64.charAt(enc4);
       }
       return outputStr;
    };

    global.app = app;
})(window);
