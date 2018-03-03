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
                console.log(me, friends);
            });
        });
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

    app.ajax = function(url, data, callback) {
        var xhr = new XMLHttpRequest();

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
        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
        xhr.send(data);
    };

    global.app = app;
})(window);
