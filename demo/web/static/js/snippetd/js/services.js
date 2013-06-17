/*jslint es5: true, white: true ,plusplus: true,nomen: true, sloppy: true */
/*globals angular,$ */

var app = angular.module("snippetd");

app.factory("Options",function(Storage,$window,$log){
    var backgrounds,themes,selectedTheme,selectedBackground,options;
    $($window).on("beforeunload",function(e){
        Storage.save("selectedTheme",options.selectedTheme);
        Storage.save("selectedBackground",options.selectedBackground);
    });
    themes = [
        {id:1,value:"default"},{id:2,value:"monokai"},
        {id:3,value:"arta"},{id:4,value:"idea"},
        {id:5,value:"magula"},{id:6,value:"sunburst"},
        {id:7,value:"dark"},{id:8,value:"github"},
        {id:9,value:"tomorrow"},{id:10,"value":"far"}
    ];
    backgrounds=[{id:1,value:"white"},{id:2,value:"black"}];
    options={
        search:{},
        themes:themes,
        backgrounds:backgrounds,
        getDefaultBg:function(){
            var r,bg = Storage.get("selectedBackground");
            if(bg && bg.id ){
                r = this.getBgById(bg.id);
            }else{
                r = this.backgrounds[0];
            }
            return r;
        },
        getDefaultTheme:function(){
            var r,theme = Storage.get("selectedTheme");
            if(theme && theme.id ){
                r = this.getThemeById(theme.id);
            }else{
                r = this.themes[0];
            }
            return r;
        },
        getThemeById:function(id){
            var i;
            for(i=0;i<this.themes.length;i++){
                if(this.themes[i].id===+id){
                    return this.themes[i];
                }
            }
        },
        getBgById:function(id){
            var i ;
            for(i=0;i<this.backgrounds.length;i++){
                if(this.backgrounds[i].id===+id){
                    return this.backgrounds[i];
                }
            }
        }
    };
    options.selectedTheme= options.getDefaultTheme();
    options.selectedBackground= options.getDefaultBg();
    return options;
});
app.factory("UtilService", function () {
    /**
    * @name UtilService
    */
    var UtilService = {
        /** FR : cree un id unique */
        makeId: function () {
            return Date.now();
        },
        /* EN : creates a date string */
        makeDate: function () {
            return (new Date()).toISOString();
        },
        /** EN : get a date object from a date string */
        parseDate: function (dateString) {
            return new Date(dateString);
        }
    };
    return UtilService;
});
app.factory("ModelService", function () {
    return {
        config: {},
        defaults: {
            defaultSnippet: {
                id: null,
                title: 'Snippet title',
                description: 'Snippet Descrition',
                content: 'Snippet Content',
                tags: [],
                category: null
            },
            defaultCategory: {
                id: 0,
                name: "Default"
            }
        },
        data: {
            snippets: [],
            categories: []
        }
    };
});
app.factory('SnippetService', function (UtilService, ModelService,Options,Storage,$window,Prettify) {
    /**
    * @name SnippetService
    */
    var SnippetService,snippets;
    $($window).on("beforeunload",function(){
        Storage.save("snippets",SnippetService.snippets);
        return;
    });
    snippets = Storage.get("snippets")||[];
    SnippetService = {
        snippets:snippets,
        filterCategory : function(item){
            var predicat=true;
            if(Options.search.category){
                predicat =  item.category_id === +Options.search.category.id;
            }
            return predicat;
        },
        getById: function (id, copy) {
            var snippet = null, i;
            if (typeof(copy) === "undefined"){copy = true;}
            for (i = 0; i < this.snippets.length; i++) {
                if (id == this.snippets[i].id) {
                    if (copy === true) {
                        snippet = angular.extend({}, this.snippets[i]);
                    } else {
                        snippet = this.snippets[i];
                    }
                }
            }
            return snippet;
        },
        save: function (snippet) {
            snippet.updated_at = new Date();
            if(!snippet.created_at){
                snippet.created_at = new Date();
            }
            if (typeof(snippet.id)!=="undefined" && snippet.id!==null) {
                var _snippet = this.getById(snippet.id,false);
                if (_snippet !== null) {
                    angular.extend(_snippet, snippet);
                    _snippet.prettyContent = Prettify.print(_snippet.content);
                    return _snippet;
                }
            } else {
                snippet.id = UtilService.makeId();
                this.snippets.push(snippet);
                snippet.prettyContent = Prettify.print(snippet.content);
            }
            return snippet;
        },
        "new": function () {
            return angular.extend({}, ModelService.defaults.defaultSnippet);
        },
        remove: function (snippet) {
            var s = this.getById(snippet.id, false);
            this.snippets.splice(this.indexOf(s), 1);
            return s;
        }
    };
    return SnippetService;
});
app.factory('CategoryService', function () {
    return {
        getById: function (id) {
            var i;
            for (i = 0; i < this.categories.length; i++) {
                if(this.categories[i].id === id){
                    return this.categories[i];
                }
            }
        },
        categories: (function(){
            return ['Other','Bash','C#','C','C++','CSS','Diff','HTML','HTTP','Ini','JSON','Java','Javascript','PHP','Perl','Python','Ruby','Scala','SQL',"Go","ActionScript","Haskell","Erlang","Apache","Lisp", "Visual Basic", "Haxe"]
            .sort( function(a,b) {
                if(a>b){return 1;}if(a<b){return -1;}return 0;
            }
                 ).map(
                 function(value,index){
                     return {id:index,name:value};
                 });
        }())
    };
});
app.factory('Export',function($window,$log){
    return {
        /* export des données */
        doExport:function(data){
            var blob,url;
            blob = new $window.Blob([angular.toJson(data)],{type:"application/json"});
            url = $window.URL.createObjectURL(blob);
            $window.open(url);
        }
    };
});
