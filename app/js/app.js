(function(global) {
    var app = {};

    app.login = function() {
        FB.login(app.onLogin, {scope: "publish_actions"});
    };

    app.onLogin = function() {
        console.log("logging");
    };

    global.app = app;
})(window);
