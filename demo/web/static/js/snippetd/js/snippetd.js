/*jslint es5: true, white: true ,plusplus: true,nomen: true, sloppy: true */
/*globals angular,$ */

/**
* SNIPPETD
* Manage Code snippets in the browser ! 
* @author MPARAISO <mparaiso@online.fr>
* @version 0.0.1
*
*/

var app = angular.module('snippetd', ['ngSanitize',"storage"]);

// FR : Configuration des routes
app.config(['$routeProvider','$locationProvider', function ($routeProvider,$locationProvider) {
    $routeProvider.when('/app/snippets/create', {
        templateUrl: 'snippet-edit.html',
        controller: "SnippetCreateCtrl"
    }).when('/app/snippets/edit/:snippetId', {
        templateUrl: 'snippet-edit.html',
        controller: "SnippetEditCtrl"
    }).when('/app/snippets', {
        templateUrl: 'snippet-list.html',
        controller: 'SnippetListCtrl'
    })
    .when("/app/options",{
        templateUrl:"options.html",
        controller: "OptionsCtrl"
    })
    .otherwise({
        redirectTo: "/app/snippets"
    });
    ///$locationProvider.html5Mode(true);
}]);

app.filter("def", function () {
    return function (input, _default) {
        var out = input;
        if (typeof(input)==="undefined" || input===null) {
            out = _default;
        }
        return out;
    };
});
