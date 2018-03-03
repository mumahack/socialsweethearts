(function(global) {
    var app = {};
    var $area = document.querySelector("#imageArea");
    var $button = document.querySelector("#login");
    var $loader = document.querySelector("#loader");
    var $info = document.querySelector("#loader span");
    var $share = document.querySelector("#share");
    var $result = document.querySelector("#result");

    app.login = function() {
        FB.login(app.onLogin, {scope: "publish_actions,user_posts,user_photos"});
    };

    app.onLogin = function() {
        console.log("logging");
        $button.style.display = "none";
        $loader.style.display = "block";
        $info.innerHTML = "Loading your profile...";

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
                    $info.innerHTML = "Receiving your best friends...";
                    app.loadFriends(userId, function(users) {
                        friends.push.apply(friends, users);
                        callback();
                    });
                }
            ], function() {
                $info.innerHTML = "Generating poster...";
                var data = [app.getMessage(me)];
                friends.forEach(function(friend) {
                    data.push(app.getMessage(friend));
                });

                app.ajax("/backend/", data, function(err, response) {
                    if(err) {
                        console.error(err);
                    } else {
                        app.showImage(response.responseText);
                    }
                });
            });
        });
    };

    app.getMessage = function(user) {
        return {
            name: user.name.split(" ")[0],
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
        $area.src = image;
        $result.style.display = "block";
        $loader.style.display = "none";

        $share.addEventListener("click", function() {
            FB.ui({
                method: 'share',
                href: image,
            }, function(response){
                console.log(arguments);
            });
        });
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
        xhr.overrideMimeType('text/plain; charset=x-user-defined');
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
