/*jslint es5: true, white: true ,plusplus: true,nomen: true, sloppy: true */
/*globals angular */

var app = angular.module("snippetd");

function MainCtrl($scope,SnippetService,CategoryService,Options,$filter) {
    $scope.version = angular.version.full;
    $scope.options = Options;
    $scope.search={};
    $scope.resetFilter=function(){  
        Options.search.category = null;
    };
    $scope.snippetService=SnippetService;
    $scope.categoryService = CategoryService;
    $scope.byCategory = SnippetService.filterCategory;
}

function SnippetListCtrl($scope, $routeParams, SnippetService,CategoryService,Options,$log) {
    Options.search = {};
    $scope.snippetService = SnippetService;
    ///$scope.byCategory = SnippetService.filterCategory;
    $scope.categoryService = CategoryService;
    $scope.selectedSnippet=null;
    $log.info($scope.snippetService.snippets);
}

function SnippetCreateCtrl($scope, $location, ModelService, SnippetService,$log) {
    $scope.snippet = SnippetService['new']();
    $log.info($scope.snippet);
    $scope.action = "Create a snippet.";
    $scope.save = function (snippet) {
        SnippetService.save(snippet);
        $log.info("snippet saved !",snippet);
        $location.path('/app/snippets');
    };
}

function SnippetFormCtrl($scope, $routeParams,CategoryService) {
    $scope.categories = CategoryService.categories;
}

function SnippetEditCtrl($scope, $routeParams,$location,SnippetService) {
    $scope.action = "Edit a snippet.";
    $scope.snippetId = $routeParams.snippetId;
    $scope.snippet =SnippetService.getById($scope.snippetId);
    if($scope.snippet!==null){
        $scope.snippetTitle = $scope.snippet.title;
    }
    $scope.save=function(snippet){
        SnippetService.save(snippet);
        $location.path('/#/snippets/');
    };
}

function SnippetItemCtrl($scope,CategoryService,Options){
    $scope.options = Options;
    $scope.getCategoryById=function(id){
        return CategoryService.getById(id);
    };
}

function OptionsCtrl($scope,Options){
    $scope.options = Options;
}

function MainMenuCtrl($scope,Export,SnippetService){
    $scope.export=function(){
        Export.doExport(SnippetService.snippets);
    };
}

