/**
 * Copyright (C) 2015 IASA - Institute of Accelerating Systems and Applications (http://www.iasa.gr)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and 
 * limitations under the License.
 */
appdb.routing = {};
var addEvent = (function () {
  if (document.addEventListener) {
    return function (el, type, fn) {
      if (el && el.nodeName || el === window) {
        el.addEventListener(type, fn, false);
      } else if (el && el.length) {
        for (var i = 0; i < el.length; i++) {
          addEvent(el[i], type, fn);
        }
      }
    };
  } else {
    return function (el, type, fn) {
      if (el && el.nodeName || el === window) {
        el.attachEvent('on' + type, function () {return fn.call(el, window.event);});
      } else if (el && el.length) {
        for (var i = 0; i < el.length; i++) {
          addEvent(el[i], type, fn);
        }
      }
    };
  }
})();
appdb.Navigation = (function(){
	var ret = {};
	var _escape = function(v){
		if(v){
			return v.replace(/[\x00-\x1F]/g,"");
		}
		return v;
	};
	var _startsWith = function(s1,s2){
		if( !s1 ) return false;
		if( !s2 ) return false;
		
		return ( s1.toLowerCase().indexOf(s2) === 0 )?true:false;
	};
	var _startsWithAny = function(s,a){
		if( !s ) return false;
		if( !a ) return false;
		
		if( $.isArray(a) === false ){
			a = [a];
		}
		
		var i,len =a.length;
		for(i=0; i<len; i+=1){
			if( s.toLowerCase().indexOf( a[i].toLowerCase() ) === 0 ) return a[i]; 
		}
		return false;
	};
	var _urlChar = (function(){
		var hashchr = "#";
		if( appdb.config.routing.useHash === false){
			hashchr = "/";
		}
		return hashchr;
	})();
	ret.internalCall = function(f,d){
		if(this.isInited === true && f){
			var permalink = appdb.Navigator.createPermalink(d,f.datatype) ;
			var title = ret.createTitle(f,[d.query,d.ext]) || document.title;
			ret.setInternalMode(true);
			ret.setPermalink(permalink);
			ret.setRawPermalink(permalink);
			ret.pushState(d.query,title,"/"+( (appdb.config.routing.useHash===false)?"?":"" ) + "p=" + appdb.config.permalinkraw);
			if(appdb.config.routing.useHash!==true){
				ret.setInternalMode(false);
			}
		}
	};
	ret.setPermalink = function(p){
		if(p.substr(0,4)==="http"){
			if(p.substr(0, 5)==="https"){
			p = "http"+p.slice(5,p.length);
			}
		}else{
			p = appdb.config.endpoint.base+"?p="+p;
		}
		if(typeof appdb.config!=="undefined"){
			appdb.config.permalink = p;
		}
	};
	ret.setRawPermalink = function(p){
		var index = -1;
		if(p.substr(0,4)==="http"){
			index = p.indexOf("#!p=");
			if(index>-1){
				p = p.substr(index);
			}
		}
		index = p.indexOf("p=");
		if(index>-1){
			p = p.substr(index+2);
		}else {
			index = p.indexOf("#");
			if(index>-1){
				p = p.substr(index);
			}
		}
		if(typeof appdb.config!=="undefined"){
			appdb.config.permalinkraw = p;
		}
	};
	ret.createPermalink = function(d,datatype){
		datatype = datatype || "apps";
		var u,s,p;
		if(typeof d === "undefined" || d===null){
			d = {flt:''};
		}else if(typeof d === "string"){
			if(d === ""){
				u = appdb.config.endpoint.base;
			}else{
				s = d.replace(/[\x00-\x1F]/g,"");
				p = appdb.utils.base64.encode(s);
				u = appdb.config.endpoint.base+"?p="+p;
			}
		}else if(!d.type) {
			s = {url : "/"+datatype, query: d.query,ext:d.ext};
			s = JSON.stringify(s);
			s = s.replace(/[\x00-\x1F]/g,"");
			p = appdb.utils.base64.encode(s);
			u = appdb.config.endpoint.base+"?p="+p;
		} else {
			s = {url : d.url, mainTitle: d.mainTitle, type: d.type};
			s = JSON.stringify(s);
			s = s.replace(/[\x00-\x1F]/g,"");
			p = appdb.utils.base64.encode(s);
			u = appdb.config.endpoint.base+"?p="+p;
		}
		return u;
	};
	ret.handlePermalink = function(f, argv){
		if( ret.isInternal ) return;
		var perm = "";
		if(f && f.datatype){
			switch(f.type){
				case "list":
					perm = ret.createPermalink((f.permalink)?f.permalink.apply(null,argv):{query:argv[0],ext:argv[1]},f.datatype);
					break;
				case "item":
					if(appdb.config.routing.useCanonical){
						perm = appdb.utils.getItemCanonicalUrl(f.datatype, argv[0]);
					}else{
						perm = ret.createPermalink((f.permalink)?f.permalink.apply(null,argv):argv[0],f.datatype);
					}
					break;
				case "static":
					perm = ret.createPermalink((f.permalink)?f.permalink.apply(null,argv):argv[0],f.datatype);
					break;
				case "ajax":
					perm = appdb.utils.getItemCanonicalUrl(f.datatype, argv[1] );
					break;
				case "marketplace":
					perm = (f && f.permalink)?f.permalink():"Marketplace";
					break;
				default:
					perm = ret.createPermalink((f.permalink)?f.permalink.apply(null,argv):argv[0]);
					break;
			}
			if( perm == false ){
				perm = appdb.utils.getItemCanonicalUrl(f.datatype, argv[0]);
				perm = ret.createPermalink((f.permalink)?f.permalink.apply(null,argv):argv[0],f.datatype);
			}
			ret.setPermalink(perm);
			ret.setRawPermalink(perm);
			if( perm.indexOf(appdb.config.endpoint.base) > -1 ){
				perm = perm.slice(appdb.config.endpoint.base.length);
			}
			if( perm.substr(0,3) === "?p=" ){
				if( appdb.config.routing.useHash ){
					perm = perm.slice(1);
				}
			}
			perm = _urlChar + perm;
			if(perm.substr(0,2) === "//") {
				perm = perm.slice(1);
			}
			
			ret.pushState({"permalink":perm},document.title,perm);
			ret.currentHistoryState = {data: {"permalink":perm},title: document.title,url: perm};
			if( argv.length === 2 && argv[1] && argv[1].isCanonical){
				//nothing to do
			}else if( ret.isInternal !== true) {
				ret.setTitle(f,argv);
			}
			
		}
	};
	ret.notFound = function(){
		//todo: add handler for not found records
	};
	ret.noAccess = function(){
		ret.notFound();
	};
	ret.createTitle = function(f,argv){
		f = f || appdb.Navigator.Registry["Home"];
		var title = "";
		if(typeof f === "string"){
			title = f;
		}else if($.isFunction(f.title)){
			title = f.title.apply(null,argv);
		}else if(typeof f.title === "string"){
			title = (f.datatype +":"+f.type+" => " + appdb.config.permalinkraw);
		}
		return title;
	};
	ret.setTitle = function(f,argv){
		if( f && argv ){
			document.title = ret.createTitle(f, argv);
		}else if( typeof f === "string") {
			document.title = f;
		}
		if( appdb.config.routing.useHash === false ){
			var state = (ret.currentHistoryState && ret.currentHistoryState.data)?ret.currentHistoryState.data:{};
			var url = (ret.currentHistoryState && ret.currentHistoryState.url)?ret.currentHistoryState.url:_urlChar ;
			ret.replaceState(state, document.title, (window.location.pathname + ( window.location.search || "" )));
		}
	};
	ret.executePermalink = function(p){
		var item;
		if ( p === "" ) {
			ret.notFound();
			return;
		}
		var isAdminManager = ( userID && (userRole == 5 || userRole == 7) )? true:false;
		var found = true;
		switch(p){
			case "home":
				appdb.views.Main.showHome();
				break;
			case "reports":
				if (isAdminManager && $("#reportslink")[0] !== undefined) {
					$("#reportslink").trigger("click");
				} else ret.noAccess();
				break;
			case "dissemination":
				if (isAdminManager && $("#disseminationlink")[0] !== undefined) {
					$("#disseminationlink").trigger("click"); 
				} else ret.noAccess();
				break;
			default:
				found = false;
				break;
		}		
		if( found === true ) return;
		
		var sw = _startsWithAny(p, ["about:","contact:feedback","apps:","people:"]);
		if( sw !== false ){
			found = true;
			item = _escape(p.substr(sw.length));
			switch( sw ){
				case "about:":
					if ($("#help"+item+"link")[0] !== undefined) {
						$("#help"+item+"link").trigger("click");
					} else ret.noAccess();
					break;
				case "contact:feedback":
					appdb.views.Main.showPage('feedback',{mainTitle:'Contact us',url:'/index/feedback'});
					break;
				case "apps:":
					if ($("#apps"+item+"link")[0] !== undefined) {
						$("#apps"+item+"link").trigger("click");
					} else ret.noAccess();
					break;
				case "people:":
					if ($("#ppl"+item+"link")[0] !== undefined) {
						$("#ppl"+item+"link").trigger("click"); 
					} else ret.noAccess();
					break;
				default:
					found = false;
					break;
			}
			if( found === true ) return;
		}
		
		var pp = appdb.utils.base64.decode(p);
		pp = pp.replace(/[\x00-\x1F]/g,"");
		if(pp[0]!=="{"){
			p = ""+ pp;
			found = true;
			switch( p ){
				case "home":
					appdb.views.Main.showHome();
					break;
				case "/news/report":
					if( isAdminManager ){
						appdb.views.Main.showActivityReport();
					} else ret.noAccess();
					break;
				case "dissemination":
					if( isAdminManager ){
						appdb.views.Main.showDisseminationTool();
					} else ret.noAccess();
					break;
				case "/help/announcements":
					$("#helpannouncelink").click();
					break;
				default:
					found = false;
					break;
			}
			if( found === true ) return;
			
			sw = _startsWithAny(p, ["/apps?flt=", "/people?flt=", "/apps/details", "/people/details", "/vo/details", "/ngi/details", "/help/", "appstats/", "pplstats/", "/changelog"]);			
			if( sw !== false ){
				found = true;
				switch( sw ){
					case "/apps?flt=":
						appdb.views.Main.showApplications($.trim(p.substr(0,6)));
						break;
					case "/people?flt=":
						appdb.views.Main.showPeople($.trim(p.substr(0,8)));
						break;
					case "/apps/details":
						showAppDetails2(p);
						break;
					case "/people/details":
						showPplDetails2(p);
						break;
					case "/vo/details":
						showVODetails2(_escape(p));
						break;
					case "/ngi/details":
						showNGIDetails2(_escape(p));
						break;
					case "appstats/":
					case "pplstats/":
						ajaxLoad(p,'main',acts);
						break;
					case "/changelog":
					case "/help/":
						var acts = appdb.utils.Faq.getActions(p);
						ajaxLoad(p,'main',acts);
						break;
					default:
						found = false;
						break;
				}
				if( found === true ) return;
			}		
		} else {
			var req = JSON.parse(pp.replace(/[\x00-\x1F]/g,""));
			if(req===null){
				return;
			}
			var j = "";
			var u = req.url;
			var query = req.query; //get the query object
			query = _escape(JSON.stringify(query));
			var ext = req.ext; //the extended properties of the component to be called
			ext = JSON.stringify(ext);
			switch(u){
				case "/apps":
					j = appdb.views.Main.showApplications;
					break;
				case "/apps/ordered":
					j = appdb.views.Main.showOrderedSoftware;
					break;
				case "/vapps/ordered":
					j = appdb.views.Main.showOrderedVAppliances;
					break;
				case "/swapps/ordered":
					j = appdb.views.Main.showOrderedSoftwareAppliances;
				case "/people":
					j = appdb.views.Main.showPeople;
					break;
				case "/people/ordered":
					j = appdb.views.Main.showOrderedPeople;
					break;
				case "/people/byrole":
					j = appdb.views.Main.showPeopleByRole;
					break;
				case "/people/bygroup":
					j = appdb.views.Main.showPeopleByGroup;
					break;
				case "/vos":
					j = appdb.views.Main.showVOs;
					break;
				case "/person":
					j = appdb.views.Main.showPerson;
					break;
				case "/vo":
					j = appdb.views.Main.showVO;
					break;
				case "/cats":
					j = appdb.views.Main.showCategories;
					break;
				case "/sites":
					j = appdb.views.Main.showSites;
					break;
				case "/datasets":
					j = appdb.views.Main.showDatasets;
					break;
				default:
					j = appdb.views.Main.showPage;
					var params = $.parseJSON(pp);
					j(params.type, params);
					return;
			}
			j($.parseJSON(query || "{}"), $.parseJSON(ext || "{}"));
			return;
		}
		ret.notFound();
	};
	ret.Registry = appdb.Navigator.Registry;
	ret.Helpers = appdb.Navigator.Helpers;
	ret.processUrl = function(u){
		u = u || (window.location.pathname + ( window.location.search || "" ));
		appdb.routing.Dispatch(u);
 	};
	ret.isInternal = false;
	ret.setInternalMode = function(i){
		ret.isInternal = i;
		ret.parseHash = (ret.isInternal === false)?ret.parseActiveHash:ret.parseInactiveHash;
	};
	ret.currentHistoryState = {data:null,title:null,url:null};
	ret.pushState = (function(){
		if( appdb.config.routing.useHash === false && history.pushState ){
			return function(state,title,url){
				history.pushState(state || {},title || "", url);
				document.title = title || document.title;
			};
		} else if( typeof appdb.config.routing.useHash === "undefined" || appdb.config.routing.useHash === true ) {
			return function( state, title, url ){
				window.location.hash = url;
				document.title = title || document.title;
				ret.currentHistoryState.data = state;
			};
		} 
		return function(state,title,url){
			document.title = title || document.title;
			ret.currentHistoryState.data = state;
			window.location = (window.location.origin || "") + url;
		};
	})(); 
	ret.replaceState = (function(){
		if( appdb.config.routing.useHash === false && history.pushState ){
			return function(state,title,url){
				history.replaceState(state || {},title || "", url || "");
				document.title = title || document.title;
			};
		} else if( typeof appdb.config.routing.useHash === "undefined" || appdb.config.routing.useHash === true ) {
			return function( state, title, url ){
				ret.setInternalMode(true);
				if( window.location.hash !== url) window.location.hash = url;
				document.title = title || document.title;
				ret.currentHistoryState.data = state;
				ret.setInternalMode(false);
			};
		}
		return function(state,title,url){
			document.title = title;
			ret.currentHistoryState = {data: {"permalink":url},title: title,url:url};
		};
	})();
	ret.getRouteUrl = function(p){
		var u = p || window.location.href;
		var b = window.location.protocol + "//" + window.location.host;
		
		if(u.indexOf(b)>-1){
			u = u.slice(b.length);
		}
		if( u.substr(0,2) === "/#"){
			u = u.slice(1);
		}
		if( u.substr(0,2) === "#?" ){
			u = u.slice(2);
		}
		if( u.substr(0,1) === "#" ){
			u = u.slice(1);
		}
		if( u.substr(0,3) === "/p="){
			u = u.slice(1);
		}
		if( u.substr(0,2) !== "p=" && u.substr(0,1) !== "/"){
			u = "/" + u;
		}
		return u;
	};
	ret.sanitizeHash = function(u){
		var hash = u || window.location.href;
		hash = ret.getRouteUrl(hash);
		if(hash){
			if(hash.length>0 && hash[0]==="#"){
			hash = hash.substr(1);
			}
			if(hash.length>0 && hash[0]==="!"){
			hash = hash.substr(1);
			}
			
			return $.trim(hash);
		}
		return "";
	};
	ret.parseActiveHash = function(event){
		event= window.event || event;
		var hash = ret.sanitizeHash();
		var prev = ret.sanitizeHash(ret.currentHistoryState.url);
		
		if(hash==="" || hash === "/"){
			appdb.views.Main.showHome();
			return true;
		}else if(hash==prev){
			appdb.utils.CancelEventTrigger(arguments.callee);
			return false;
		}else {
			if( hash.slice(0,2) === "p="){
				hash = "?"+hash;
			}
			if(appdb.config.routing.useHash === true && ret.isInternal){
				ret.setInternalMode(false);
			}else{
				appdb.Navigator.processUrl(hash);
				ret.currentHistoryState.url = hash;
			}
		}
		return true;
	};
	ret.parseInactiveHash = function(){ret.parseHash = ret.parseActiveHash;return true;};
	ret.parseHash = ret.parseActiveHash;
	
	
	ret.popstate = function(e){
			e = e || {};
			if( ret.isInited ){
				ret.currentHistoryState = ret.currentHistoryState || {};
				ret.currentHistoryState.data = e.data;
				ret.processUrl();
			}
			ret.isInited = true;
	};
	var setupNavigator = function(){
		$(document).on("click", "a[href='#']", function() {
			return false;
		});
		ret.currentHistoryState = {data: {},title: "",url:""};
		if(appdb.config.routing.useHash !== false){
			ret.isInited = true;
			addEvent(window, "hashchange", ret.parseHash);
			ret.currentHistoryState.url="#";
			setTimeout(function(){ret.parseHash();},1);
		}else{
			ret.isInited = true;
			setTimeout(function(){ret.popstate();},1);
			$(window).on("load",function(){
				setTimeout(function(){
					addEvent(window,"popstate", function(e){
						ret.popstate(e);
					});
				},5);
			});
		}
	};
	ret.isInited = false;
	ret.init = function(){
		var c = appdb.config.routing || {
			useCanonical: true,
			useHash: true
		};
		
		appdb.Navigator = ret;
		setupNavigator();
	};
	ret.navigate = function(url,data){
		data = data || {};
		data.title = data.title || document.title;
		appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data,data.title,url);
		appdb.Navigator.setInternalMode(false);
		appdb.Navigator.processUrl();
	};
	return ret;
})();
appdb.routing.resister = function(regs){
	
};
appdb.routing.resources = {};
appdb.routing.routes = [];
appdb.routing.isCanonical = function(h){
	h = h || window.location.hash;
	
	if(h){
		while( h.length > 0 && (h[0] === "#" || h[0] === "!") ) h =h.slice(1);
	}
	
	if( h.slice(0, 2).toLowerCase() === "p="){
		return false;
	}
	return true;
};
appdb.routing.parseQueryString = function( query ){
	var re = /([^&=]+)=?([^&]*)/g;
	var decodeRE = /\+/g;  // Regex for replacing addition symbol with a space
	var decode = function (str) {return decodeURIComponent( str.replace(decodeRE, " ") );};
	var params = {}, e;
    while ( e = re.exec(query) ) { 
        var k = decode( e[1] ), v = decode( e[2] );
        if (k.substring(k.length - 2) === '[]') {
            k = k.substring(0, k.length - 2);
            (params[k] || (params[k] = [])).push(v);
        }
        else params[k] = v;
    }
    return params;
};
/*
 * Returns true or false if the given hash (h) 
 * matches the given route object (r)
 */
appdb.routing.MatchRoute = function(r, h){
	var ht = h || "", i, len , plen, res = {query:{}, parameters: {}}, hh;
	
	//Check if route is given
	if( !r ) {
		appdb.debug("[appdb.routing.MatchRoute]: No route given for hash '" + h + "'.");
		return false;
	}
	
	//Check for query string
	ht = ht.split("?");
	if( ht.length > 1 ){
		res.query = appdb.routing.parseQueryString( ht[1] );
	}
	ht = ht[0];
	
	//Check if an actual url exists
	ht = (ht.length > 0 && ht[0] === "/")?ht.slice(1):ht;
	ht = ht.split("/");
	len = ht.length;
	plen = r.parser.length;
	if( len === 0 ){
		appdb.debug("[appdb.routing.MatchRoute]: No valid hash given.");
		return false;
	}
	
	
	for(i=0; i<len; i+=1){
		hh = ht[i];
		//If url has more parts check if they are in form of 
		// <name>:<value> and add them as
		//parameters in the return object. If value
		//dows not exist it will default to TRUE
		if( i >= plen ){
			hh = hh.split(":");
			if( $.trim(hh[0]) === "" ) continue; 
			res.parameters[hh[0]] = $.trim(hh[1]) || true;
		}else{
			hh = r.parser[i](hh);
			if( !hh ) return false;
			//Check if object returned append it to 
			//return object as parameters
			if( typeof hh !== "string" ){
				for(var j in hh){
					if( hh.hasOwnProperty(j) ){
						res.parameters[j] = hh[j];
					}
				}
			}
		}		
	}
	
	//parser might have more rules, 
	//such as optional parameters.
	if(i < plen ){
		var tmp;
		len = r.parser.length;
		for(i=i; i<len; i+=1){
			tmp = r.parser[i]();
			if( !tmp ) return false;
		}
	}
	
	//Check if route has declared parameters
	//and add them to the return object
	if( r.parameters ){
		for(i in r.parameters){
			if( r.parameters.hasOwnProperty(i) ){
				res.parameters[i] = r.parameters[i];
			}
		}
	}
	
	return res;
};
appdb.routing.FindRoute = function(h){
	if( appdb.config.routing.useHash ){
		h = h || window.location.hash;
	}else{
		h = h || window.location.pathname + window.location.search;
	}
	
	
	if(h){
		while( h.length > 0 && (h[0] === "#" || h[0] === "!") ) h =h.slice(1);
	}
	
	if(h.length === 0 ){
		h = "/";
	}else{
		if( h[h.length-1] === "/" ){
			h = h.slice(0,h.length-1);
		}
	}
	
	var rs = appdb.routing.routes, len = rs.length, i, isMatch = false;
	for(i=0; i<len; i+=1){
		isMatch = appdb.routing.MatchRoute(rs[i], h);
		if( isMatch !== false ){
			isMatch.route = rs[i];
			break;
		}
	}
	return isMatch;
};
appdb.routing.Dispatch = function( h , force ){
	var isMatch = appdb.routing.FindRoute(h);
	force = (typeof force === "boolean")?force:false;
	if( isMatch ){
			appdb.Navigator.setInternalMode(true);
			appdb.routing.currentMatch = isMatch;
			isMatch.route.action.apply(isMatch.route.resource,[isMatch, force]);
			appdb.Navigator.setInternalMode(false);
			return true;
	}
	appdb.routing.currentMatch = false;
	appdb.debug("No route found");
	return false;
};
appdb.routing.currentMatch = {};
appdb.routing.Parser = {};
/*
 * Operations for individual path parts. 
 */
appdb.routing.PathOperations = {
	"optional": function optional(d){
		var i, len = this.length, res = {};
		for(i=0; i<len; i+=1){
			if( this[i] === d ) {
				res[this[i]] = true;
				return res;
			}
		}
		return true;
	},
	"selection": function(d){
		var i=0, opts = ( this.options || [] ), len = opts.length, res = {};
		for( i=0; i<len; i+=1 ){
			if( opts[i] === d ){
				res[this.name] = opts[i];
				break;
			}
		}
		return res;
	},
	"variable": function variable(d){
		var res = {};
		if( d === "" ) return false;
		res[this.toString()] = d;
		return res;
	},
	"literal" : function literal(d){
		if( this.toString() === d){
			return d;
		}
		return false;
	}
};
/*
 * Creates context for appdb.routing.PathOperations
 */
appdb.routing.Parser.OperationFactory = (function(){
	return function(opname, opcontext){
		return (function(operation,context){
			return function(){
				return operation.apply(context,arguments);
			};
		})(appdb.routing.PathOperations[opname],opcontext);
	};
})();
appdb.routing.Parser.Compiler = (function(){
	var _factory = [
		function optional( d ){
			if ( d.match(/^\<(\w+\:){0,1}[\w+\|]*\>/) ){
				var cntx = {name: "", options:[]};
				if( d.indexOf(":") > 0 ) { //check for named option
					 cntx.name = $.trim(d.split(":")[0]);
					 if(cntx.name && cntx.name[0] === "<"){
						 cntx.name = cntx.name.slice(1);
					 }
				}
				cntx.options = d.toLowerCase().replace(/[\<\>]/g,"").split("|");
				if( cntx.name ){ // if named option use 'selection' operation
					return appdb.routing.Parser.OperationFactory("selection",cntx);
				}else{ // if anonymous option use 'optional' oprtation
					return appdb.routing.Parser.OperationFactory("optional",cntx.options);
				}
			}
			return false;
		},
		function variable( d ){
			if( d.match(/^\:\w+$/) ){
				return appdb.routing.Parser.OperationFactory("variable", d.toLowerCase().replace(":",""));
			}
			return false;
		},
		function literal( d ){
			d = d || "";
			if( d ) {
				return appdb.routing.Parser.OperationFactory("literal",d.toLowerCase());
			}
			return false;
		}];
	
	var _checkPartType = function( d ){
		var i, len = _factory.length, tmp;
		for(i=0; i<len; i+=1) {
			tmp = _factory[i]( d );
			if( tmp ){
				return tmp;
			}
		}
		return false;
	};
	/* Checks and returns the type of functionality 
	 * of each part of the given path.
	 * Parameters:
	 * p: path string or array of path parts
	 * returns array of compiled parts
	 */
	var _compile = function( p ){
		p = p || false;
		var path = [], res = [], i, len, tmp;
		if( !p ) {
			return false;
		}
		
		if( typeof p === "string" ){
			if( p === "/"){
				path = [];
			} else {
				path = p.split("/");
				if( path.length === 0 ){
					return false;
				}	
			}
		}
		
		len = path.length;
		for(i=0; i<len; i+=1){
			tmp = _checkPartType( path[i] );
			if( tmp === false ){
				return false;
			}
			res.push( tmp );
		}
		
		return res;
	};
	return _compile;
	
})();

appdb.routing.addRoutes = function( r ){
	r = r || [];
	if( $.isArray(r) === false ) r = [r];
	
	var i, len = r.length, res = true;
	for(i=0; i < len; i+=1){
		res = appdb.routing.addRoute(r[i]);
		if( res === false ){
			return false;
		}
	}
	return true;
};
appdb.routing.addRoute = function( r ){
	if( $.isPlainObject(r) === false ){
		appdb.debug("[appdb.routing.addRoute]: Not valid route object given");
		return false;
	}
	if( typeof r.path === "undefined" ) {
		appdb.debug("[appdb.routing.addRoute]: Path is missing from route. Assuming default route '*'.");
		r.path = "";
	}
	if( typeof r.resource === "undefined" ) {
		appdb.debug("[appdb.routing.addRoute]: Resource is missing. Assuming default resource 'Default'.");
		r.resource = 'Default';
	}
	if( typeof r.action === "undefined" ) {
		appdb.debug("[appdb.routing.addRoute]: Action is missing. Assuming default action 'default'.");
		r.resource = 'Default';
	}
	
	r.parser = appdb.routing.Parser.Compiler(r.path);
	if( r.parser === false ){
		appdb.debug("[appdb.routing.addRoute]: Route path compiler failed for path: '" + r.path + "'");
		return false;
	}
	
	if( typeof r.resource === "string" ){
		if( r.resource.indexOf(".") !== -1){
			r.resource = appdb.FindNS(r.resource);
		} else {
			r.resource = appdb.routing.resources[r.resource];
		}
	}
	
	if( typeof r.action === "string" ){
		if( typeof r.resource[r.action] === "undefined" ){
			appdb.debug("[appdb.routing.addRoute]: Action is not supported by " + r.resource + ".Adding dummy action.");
			r.action = function(){appdb.debug("Dummy action called");};
		} else {
			r.action = r.resource[r.action];
		}
	}
	
	appdb.routing.routes.push(r);
	return true;
};
appdb.routing.ExecResourceAction = function(resource, action, data, force){
	resource = resource || {};
	force = (typeof force === "boolean")?force:false;
	data = data || {};
	if( typeof action === "function" ){
		appdb.Navigator.setInternalMode(true);
		appdb.routing.currentMatch = data;
		action.apply(resource,[data, force]);
		appdb.Navigator.setInternalMode(false);
		return true;
	}
	return false;
};
appdb.routing.Resource = function(o){
	var _init = function(){
		for(var i in o){
			if( o.hasOwnProperty(i) ){
				this[i] = o[i];
			}
		}
	};
	_init.apply(this);
	return this;
	
};
appdb.routing.resources.Default = new appdb.routing.Resource({
	"index": function(d){
		appdb.pages.reset();
		if(d.query && d.query.p){
			appdb.Navigator.executePermalink(d.query.p);
		} else {
			appdb.views.Main.showHome();
		}
	}
});
appdb.routing.resources.Software = new appdb.routing.Resource({
	details: function(d, force){
		var sname = (d.parameters && typeof d.parameters.name === "string")?d.parameters.name:"", ext = {isCanonical: true};
		var m = appdb.routing.currentMatch, isLoaded = m.route && m.route.resource && (m.route.resource == this) && m.parameters.name.toLowerCase() === appdb.pages.Application.currentCName().toLowerCase();
		cname = sname;
		if( !isLoaded ) appdb.pages.reset();
		if( $.trim(cname) !== "" ){
			appdb.pages.Application.currentRouteData(d);
			if( !force && appdb.pages.Application.currentId() && appdb.pages.Application.currentCName().toLowerCase() === cname.toLowerCase() ){
				d.parameters.section = d.parameters.section || "information";
				appdb.pages.Application.selectSection( d.parameters.section );
				if( !d.parameters.series ){
					appdb.Navigator.setTitle("Software " + cname + " " + d.parameters.section);
				}
			} else {
				var content = (d && d.parameters && $.trim(d.parameters.content) )?$.trim(d.parameters.content).toLowerCase():"software";
				var o = {id: encodeURIComponent("s:" + cname), name: cname};
				var e = {isCanonical: true, content: content };
				var conf = appdb.utils.entity.getConfig(content, o, e);
				appdb.views.Main.showApplication(o,e);
				appdb.Navigator.setTitle(conf.name() + " " + cname);
			}
		}
		return false;
	},
	history: function(d, force){
		var sname = (d.parameters && typeof d.parameters.name === "string")?d.parameters.name:"", ext = {isCanonical: true};
		var m = appdb.routing.currentMatch, isLoaded = m.route && m.route.resource && (m.route.resource == this) && m.parameters.name.toLowerCase() === appdb.pages.Application.currentCName().toLowerCase();
		var histid = (d.parameters && typeof d.parameters.histid === "string") ? d.parameters.histid: "";
		cname = sname;
		if( !isLoaded ) appdb.pages.reset();
		if( $.trim(cname) !== "" ){
			appdb.pages.Application.currentRouteData(d);
			if( !force && appdb.pages.Application.currentId() && appdb.pages.Application.currentCName().toLowerCase() === cname.toLowerCase() ){
				d.parameters.section = d.parameters.section || "information";
				appdb.pages.Application.selectSection( d.parameters.section );
				if( !d.parameters.series ){
					appdb.Navigator.setTitle("Software " + cname + " " + d.parameters.section);
				}
			} else {
				var content = (d && d.parameters && $.trim(d.parameters.content) )?$.trim(d.parameters.content).toLowerCase():"software";
				var o = {id: encodeURIComponent("s:" + cname), name: cname, histid: histid, histtype: 0, entityType: 'software'};
				var e = {isCanonical: true, content: content };
				var conf = appdb.utils.entity.getConfig(content, o, e);
				appdb.views.Main.showApplication(o,e);
				appdb.Navigator.setTitle(conf.name() + " " + cname);
			}
		}
		return false;
	}, 
	vahistory: function(d, force){
		var sname = (d.parameters && typeof d.parameters.name === "string")?d.parameters.name:"", ext = {isCanonical: true};
		var m = appdb.routing.currentMatch, isLoaded = m.route && m.route.resource && (m.route.resource == this) && m.parameters.name.toLowerCase() === appdb.pages.Application.currentCName().toLowerCase();
		var histid = (d.parameters && typeof d.parameters.histid === "string") ? d.parameters.histid: "";
		cname = sname;
		if( !isLoaded ) appdb.pages.reset();
		if( $.trim(cname) !== "" ){
			appdb.pages.Application.currentRouteData(d);
			if( !force && appdb.pages.Application.currentId() && appdb.pages.Application.currentCName().toLowerCase() === cname.toLowerCase() ){
				d.parameters.section = d.parameters.section || "information";
				appdb.pages.Application.selectSection( d.parameters.section );
				if( !d.parameters.series ){
					appdb.Navigator.setTitle("Software " + cname + " " + d.parameters.section);
				}
			} else {
				var content = (d && d.parameters && $.trim(d.parameters.content) )?$.trim(d.parameters.content).toLowerCase():"vappliance";
				var o = {id: encodeURIComponent("s:" + cname), name: cname, histid: histid, histtype: 0, entityType: 'vappliance'};
				var e = {isCanonical: true, content: content };
				var conf = appdb.utils.entity.getConfig(content, o, e);
				appdb.views.Main.showApplication(o,e);
				appdb.Navigator.setTitle(conf.name() + " " + cname);
			}
		}
		return false;
	}, 
	vadetails: function(d, force){
		d.parameters = d.parameters || {};
		d.parameters.section = $.trim(d.parameters.section);
		d.parameters.content = "vappliance";
		this.details(d, force);
	},
	vaimagedetails: function(d, force){
		d.parameters = d.parameters || {};
		d.parameters.section = "vaversion";
		d.query.strict = ((d.query && typeof d.query.strict !== "undefined")?true:false);
		var ajxopts = {guid: d.parameters.imageguid };
		if( d.query.strict ){
			ajxopts["strict"] = "true";
		}
		$.ajax({
			url: appdb.config.endpoint.base + "apps/vappimage",
			type: "GET",
			dataType: "json",
			data: ajxopts,
			success: function(data){
				if( data.result === "success" ){
					d.parameters.name = data.app.cname;
					if( data.version.published === true && data.version.archived === false && data.version.enabled === true ){
						d.parameters.vasection = "latest";
					}else if( data.version.published === true && data.version.archived === true ){
						d.parameters.vasection = "previous";
						d.parameters.vaid = data.version.id;
					}
					if( d.parameters.imageguid.indexOf(":") > -1 ){
						var tmp = d.parameters.imageguid.split(":");
						d.parameters.imageguid = tmp[0];
						d.parameters.imageid = tmp[1];
					}
					d.parameters.vaid = data.version.id;
				}else{
					setTimeout(function(){
						$("#maintd > #main").html("<div class='emptycontent' style='display:block'><div class='content'><img src='/images/exclam16.png' alt=''><span>Could not find the specified image</span></div></div>");
					},2000);					
					d = null;
				}
			},
			async: false
		});
		if( d!== null ){
			d.parameters.content = "vappliance";
			this.details(d, force);
		}
	},
	swappdetails: function(d, force){
		d.parameters = d.parameters || {};
		d.parameters.section = $.trim(d.parameters.section);
		d.parameters.content = "swappliance";
		this.details(d, force);
	},
	registernew : function(){
		if( userID == null || !userCanInsertApplication) {
			appdb.views.Main.showHome();
			return true;
		}
		var _mainTitle = "Register New Software";
		appdb.views.Main.showApplication({id: 0},{mainTitle: _mainTitle, Canonical: true});
		appdb.Navigator.setTitle(_mainTitle);
		return true;
	},
	registersw: function(){
		this.registernew();
	},
	registerva: function(){
		if( userID == null || !userCanInsertApplication) {
			appdb.views.Main.showHome();
			return true;
		}
		var _mainTitle = "Register New Virtual Appliance";
		appdb.views.Main.showVirtualAppliance({id: 0},{mainTitle: _mainTitle, Canonical: true});
		appdb.Navigator.setTitle(_mainTitle);
		return true;
	},
	registerswapp: function(){
		if( userID == null || !userCanInsertApplication) {
			appdb.views.Main.showHome();
			return true;
		}
		var _mainTitle = "Register New Software Appliance";
		appdb.views.Main.showSoftwareAppliance({id: 0},{mainTitle: _mainTitle, Canonical: true});
		appdb.Navigator.setTitle(_mainTitle);
		return true;
	},
	index: function(){},
	discipline: function(){},
	category: function(){}
});

appdb.routing.resources.Person = new appdb.routing.Resource({
	details: function(d, force){
		var sname = (d.parameters && typeof d.parameters.name === "string")?d.parameters.name:"";
		var m = appdb.routing.currentMatch, isLoaded = m.route && m.route.resource && (m.route.resource == this) && m.parameters.name.toLowerCase() === appdb.pages.Person.currentCName().toLowerCase();
		cname = ""+sname.replace(/\./g," ");
		if( !isLoaded ) appdb.pages.reset();
		if( $.trim(cname) !== "" ){
			if( !force && appdb.pages.Person.currentId() && appdb.pages.Person.currentCName().toLowerCase() === sname.toLowerCase() ){
				d.parameters.section = d.parameters.section || "information";
				d.parameters.subsection = d.parameters.subsection || "";
				console.log(d.parameters);
				appdb.pages.Person.selectSection( d.parameters.section, d.parameters.subsection );
			}else{
				appdb.views.Main.showPerson({id: encodeURIComponent("s:" + sname), cname: cname,name:cname},{isCanonical: true});
				if( document.title !== (appdb.pages.Person.currentFirstName() + " " + appdb.pages.Person.currentLastName() + " profile" ) ){
					appdb.Navigator.setTitle(appdb.pages.Person.currentFirstName() + " " + appdb.pages.Person.currentLastName() + " profile");
				}
			}
			
		}
		return false;
	},
	registernew: function(){
		if( userID == null || !userIsAdminOrManager ) {
			appdb.views.Main.showHome();
			return true;
		}
		var _mainTitle = "Register New User";
		appdb.views.Main.showPerson({id: 0}, {mainTitle: _mainTitle});
		appdb.Navigator.setTitle(_mainTitle);
		return true;
	},
	index: function(){},
	role: function(){}
});

appdb.routing.resources.Vo = new appdb.routing.Resource({
	details: function(d, force){
		var name = (d.parameters && typeof d.parameters.name === "string")?d.parameters.name:"";
		var m = appdb.routing.currentMatch, isLoaded = m.route && m.route.resource && (m.route.resource == this) && m.parameters.name.toLowerCase() === appdb.pages.vo.currentName().toLowerCase();
		
		if( !isLoaded ) appdb.pages.reset();
		if( $.trim(name) !== "" ){
			if( !force && appdb.pages.vo.currentId() && appdb.pages.vo.currentName().toLowerCase() === name.toLowerCase() ){
				d.parameters.section = d.parameters.section || "information";
				appdb.pages.vo.selectSection( d.parameters.section );
				appdb.Navigator.setTitle("VO " + appdb.pages.vo.currentName());
			}else{
				appdb.views.Main.showVO(name,{isCanonical: true});
				if( document.title !== ("VO " + appdb.pages.vo.currentName() )){
					appdb.Navigator.setTitle("VO " + appdb.pages.vo.currentName());
				}
			}
			
		}
		return false;
	},
	index: function(){},
	voimagedetails: function(d, force){
		d.parameters = d.parameters || {};
		d.parameters.section = "vaversion";
		d.query.strict = ((d.query && typeof d.query.strict !== "undefined")?true:false);
		var ajxopts = {guid: d.parameters.imageguid };
		if( d.query.strict ){
			ajxopts["strict"] = "true";
		}
		$.ajax({
			url: appdb.config.endpoint.base + "vo/voimage",
			type: "GET",
			dataType: "json",
			data: ajxopts,
			success: function(data){
				if( data.result === "success" ){
					d.parameters.name = data.app.cname;
					if( data.version.published === true && data.version.archived === false && data.version.enabled === true ){
						d.parameters.vasection = "latest";
					}else if( data.version.published === true && data.version.archived === true ){
						d.parameters.vasection = "previous";
						d.parameters.vaid = data.version.id;
					}
					d.parameters.imageguid = data.baseidentifier;
					d.parameters.imageid = data.baseid;
					d.parameters.vaid = data.version.id;
				}else{
					d.parameters.section = "imagelist";
					setTimeout(function(){
						$("#maintd > #main").html("<div class='emptycontent' style='display:block'><div class='content'><img src='/images/exclam16.png' alt=''><span>Could not find the specified image</span></div></div>");
					},2000);					
					d = null;
				}
			},
			async: false
		});
		if( d!== null ){
			d.parameters.content = "vappliance";
			appdb.routing.ExecResourceAction(appdb.routing.resources.Software, appdb.routing.resources.Software.vadetails, d, force );
		}
	}
});

appdb.routing.resources.Site = new appdb.routing.Resource({
	details: function(d, force){
		var name = (d.parameters && typeof d.parameters.name === "string")?d.parameters.name:"";
		var m = appdb.routing.currentMatch, isLoaded = m.route && m.route.resource && (m.route.resource == this) && m.parameters.name.toLowerCase() === appdb.pages.site.currentName().toLowerCase();
		
		if( !isLoaded ) appdb.pages.reset();
		if( $.trim(name) !== "" ){
			if( !force && appdb.pages.site.currentId() && appdb.pages.site.currentName().toLowerCase() === name.toLowerCase() ){
				d.parameters.section = d.parameters.section || "information";
				appdb.Navigator.setTitle("Site " + appdb.pages.site.currentName());
			}else{
				appdb.views.Main.showSite({name: name},{isCanonical: true});
				if( document.title !== ("Site " + appdb.pages.site.currentName() )){
					appdb.Navigator.setTitle("Site " + appdb.pages.site.currentName());
				}
			}
			
		}
		return false;
	}
});

appdb.routing.resources.Dataset = new appdb.routing.Resource({
	details: function(d, force){
		var name = (d.parameters && typeof d.parameters.name === "string")?d.parameters.name:"";
		var versionid = (d.parameters)?$.trim(d.parameters.versionid):"";
		var m = appdb.routing.currentMatch, isLoaded = m.route && m.route.resource && (m.route.resource == this) && m.parameters.name.toLowerCase() === appdb.pages.dataset.currentName().toLowerCase();
		
		if( !isLoaded ) appdb.pages.reset();
		if( $.trim(name) !== "" ){
			if( !force && appdb.pages.dataset.currentId() && appdb.pages.dataset.currentName().toLowerCase() === name.toLowerCase() ){
				d.parameters.section = d.parameters.section || "information";
				appdb.Navigator.setTitle("Dataset " + appdb.pages.dataset.currentName());
			}else{
				appdb.views.Main.showDataset({id: name},{isCanonical: true, versionid:versionid});
				if( document.title !== ("Dataset " + appdb.pages.dataset.currentName() )){
					appdb.Navigator.setTitle("Dataset " + appdb.pages.dataset.currentName());
				}
			}
			
		}
		return false;
	},
	registernew : function(){
		if( userID == null || !appdb.pages.index.canManageDatasets()) {
			appdb.views.Main.showHome();
			return true;
		}
		var _mainTitle = "Register New Dataset";
		appdb.views.Main.showDataset({id: 0},{mainTitle: _mainTitle, Canonical: true});
		appdb.Navigator.setTitle(_mainTitle);
		return true;
	},
	registerdataset: function(){
		this.registernew();
	}
});

appdb.routing.resources.Marketplace = new appdb.routing.Resource({
	software: function(d, force){
		appdb.views.Main.showSoftwareMarketplace();
		return false;
	},
	cloud: function(d, force){
		appdb.views.Main.showCloudMarketplace();
		return false;
	},
	people: function(d, force){
		appdb.views.Main.showPeopleMarketplace();
		return false;
	},
	swvos: function(){
		appdb.views.Main.showSwVOs();
		return false;
	},
	cloudvos: function(){
		appdb.views.Main.showCloudVOs();
		return false;
	},
	cloudsites: function(){
		appdb.views.Main.showCloudSites();
		return false;
	},
	cloudswappliances: function(){
		appdb.views.Main.showCloudSoftwareAppliances();
		return false;
	},
	datasets: function(){
		appdb.views.Main.showDatasets();
		return false;
	}
});

appdb.routing.resources.Permalink = new appdb.routing.Resource({
	index: function(d){
		var p = d.permalink || "";
		if(p){alert(p);}
	}
});

appdb.routing.resources.About = new appdb.routing.Resource({
	index: function(d){
		if( !d || !d.parameters || !d.parameters.item || !this[d.parameters.item]) return false;
		return this[d.parameters.item](d) || true;
	},
	usage: function(d){
		appdb.views.Main.showPage('about',{mainTitle:'About > Usage',url:'/help/usage'});
	},
	announcements: function(d){
		appdb.views.Main.showPage('about',{mainTitle:'About > Announcements',url:'/help/announcements', callback:'$(\'a.oldannounce\').click();$(\'#main\').find(\'h3\').remove();'});
	},
	faq: function(d){
		var data = (d.parameters.data)?"/" + d.parameters.data:"";
		var ext = {mainTitle:'About > Faq',url:'/help/faq'+data};
		if(d.parameters.data){
			ext.callback = "appdb.utils.Faq.scrollTo(" + d.parameters.data + ");";
		}
		appdb.views.Main.showPage('about',ext);
	},
	credits: function(d){
		appdb.views.Main.showPage('about',{mainTitle:'About > Credits',url:'/help/credits'});
	},
	changelog: function(d){
		appdb.views.Main.showPage('about',{mainTitle:'About > Changelog',url:'/changelog'});
	},
	features: function(d){
		appdb.views.Main.showPage('about',{mainTitle:'About > Latest Features',url:'/changelog/features'});
	}
});
appdb.routing.resources.Contact = new appdb.routing.Resource({
	index: function(d){	},
	feedback: function(){
		appdb.views.Main.showPage('feedback',{mainTitle:'Contact us',url:'/index/feedback', content:'pages'});
		window.scroll(0,0);
	}
});
appdb.routing.resources.Admin = new appdb.routing.Resource({
	index: function(d){
		if( !d || !d.parameters || !d.parameters.tool || !this[d.parameters.tool]) return false;
		return this[d.parameters.tool]() || true;
	},
	activityreport: function(d){
		appdb.views.Main.showActivityReport({},{mainTitle : 'Activity Report', content: 'admin'});
	},
	disseminationtool: function(d){
		appdb.views.Main.showDisseminationTool({},{mainTitle : 'Dissemination Tool', content: 'admin'});
	}
});

appdb.routing.resources.Statistics = new appdb.routing.Resource({
	index: function(d){	
		if( !d || !d.parameters || !d.parameters.entity || !d.parameters.group) return false;
		if( !this[d.parameters.entity]) return false;
		if( d.parameters.visual){
			switch($.trim(d.parameters.visual).toLowerCase()){
				case "bar":
				case "bars":
					d.parameters.visual = "bars";
					break;
				case "pie":
					d.parameters.visual = "pie";
					break;
				default: 
					d.parameters.visual = undefined;
					break;
			}
		}
		return this[d.parameters.entity](d) || true;
	},
	vappliance: function(d){
		var group = d.parameters.group;
		var title = "Browse graphically > Virtual Appliances per ";
		switch(group){
			case "discipline":
				group = "perdiscipline";
				title += group;
				break;
			case "subdiscipline":
				group = "persubdomain";
				title += group;
				break;
			case "vo":
				group = "pervo";
				title += " virtual organization";
				break;
			case "category":
				group = "percategory";
				title += " category";
				break;
			default: 
				return false;
		}
		var visual = d.parameters.visual || "";
		if( visual ){
			visual = "&ct=" + visual;
		}
		appdb.views.Main.showPage('statistics',{mainTitle: title,url:'appstats/' + group + "?content=vappliance" + visual, content: 'vappliance'});
		return true;
	},
	software: function(d){
		var group = d.parameters.group;
		var title = "Browse graphically > Software per ";
		switch(group){
			case "discipline":
				group = "perdiscipline";
				title += group;
				break;
			case "subdiscipline":
				group = "persubdomain";
				title += group;
				break;
			case "vo":
				group = "pervo";
				title += " virtual organization";
				break;
			case "category":
				group = "percategory";
				title += " category";
				break;
			default: 
				return false;
		}
		var visual = d.parameters.visual || "";
		if( visual ){
			visual = "?ct=" + visual;
		}
		appdb.views.Main.showPage('statistics',{mainTitle: title,url:'appstats/' + group + visual, content: 'software'});
		return true;
	},
	people: function(d){
		var group = d.parameters.group;
		var title = "Browse graphically > Software per " + group;
		switch(group){
			case "country":
				group = "percountry";
				break;
			case "position":
				group = "perposition";
				break;
			default: 
				return false;
		}
		var visual = d.parameters.visual || "";
		if( visual ){
			visual = "?ct=" + visual;
		}
		appdb.views.Main.showPage('statistics',{mainTitle: title,url:'pplstats/' + group + visual, content: 'researchers'});
		return true;
	}
});

appdb.routing.resources.Saml = new appdb.routing.Resource({
	index: function(d){
		return true;
	}
});
appdb.routing.addRoutes([{
	path: "store/register/software",
	resource: "Software",
	action: "registersw"
},{
	path: "store/register/virtualappliance",
	resource: "Software",
	action: "registerva"
},{
	path: "store/register/softwareappliance",
	resource: "Software",
	action: "registerswapp"
},{
	path: "store/register/dataset",
	resource: "Dataset",
	action: "registerdataset"	
},{
	path: "store/software/virtualappliance",
	resource: "Software",
	action: "vadetails",
	parameters: {
		section: "virtualappliance"
	}
},{
	path: "store/software/:name/vaversion/:vasection/:vaid",
	resource: "Software",
	action: "vadetails",
	parameters: {
		section: "vaversion"
	}
},{
	path: "store/software/:name/vaversion/:vasection",
	resource: "Software",
	action: "vadetails",
	parameters: {
		section: "vaversion"
	}
},{
	path: "store/vappliance/:name/vaversion/:vasection/:vaid",
	resource: "Software",
	action: "vadetails",
	parameters: {
		section: "vaversion"
	}
},{
	path: "store/vappliance/:name/vaversion/:vasection",
	resource: "Software",
	action: "vadetails",
	parameters: {
		section: "vaversion"
	}
},{
	path: "store/vappliance/:name/<section:information|publications|comments|permissions>",
	resource: "Software",
	action: "vadetails"
},{
	path: "store/vappliance/:name",
	resource: "Software",
	action: "vadetails"
},{
	path: "store/vm/image/:imageguid",
	resource: "Software",
	action: "vaimagedetails",
	parameters: {
		section: "vaversion"
	}
},{
	path: "store/vo/image/:imageguid",
	resource: "Vo",
	action: "voimagedetails",
	parameters: {
		section: "vaversion"
	}
},{
	path: "store/software/:name/history/:histid",
	resource: "Software",
	action: "history"
},{
	path: "store/vappliance/:name/history/:histid",
	resource: "Software",
	action: "history"
},{
	path: "store/software/:name/<section:information|publications|releases|comments|permissions>/:series/:release/<releasesection:details|files|repositories>",
	resource: "Software",
	action: "details"
},{
	path: "store/software/:name/<section:releases|releases>/:series",
	resource: "Software",
	action: "details"
},{
	path: "store/software",
	resource: "Software",
	action: "registernew"
},{
	path: "store/swappliance/:name/<section:information|publications|versions|comments|permissions>",
	resource: "Software",
	action: "swappdetails"
},{
	path: "store/swappliance/:name",
	resource: "Software",
	action: "swappdetails"
},{
	path: "store/person/:name/preferences/accessmanagement",
	resource: "Person",
	action: "details",
	parameters: {
		section: "preferences",
		subsection: "accessmanagement"
	}
},
{
	path: "store/person/:name/<section:information|publications|preferences|pendingrequests|manageaccounts>",
	resource: "Person",
	action: "details"
},{
	path: "store/person",
	resource: "Person",
	action: "registernew"
},{
	path: "store/vo/:name/<section:information|statistics|imagelist>",
	resource: "Vo",
	action: "details"
},{
	path: "store/site/:name",
	resource: "Site",
	action: "details"
},{
	path: "browse/category/:name",
	resource: "Software",
	action: "category"
},{
	path: "browse/discipline/:name",
	resource: "Software",
	action: "discipline"
},{
	path: "browse/software/newest",
	resource: "Software",
	action: "index",
	parameters: {"orderby": "newest","orderbyOp":"desc"}
},{
	path: "browse/software/toprated",
	resource: "Software",
	action: "index",
	parameters: {"orderBy":"toprated" ,"orderbyOp":"desc"}
},{
	path: "browse/software/newest",
	resource: "Software",
	action: "index",
	parameters: {"orderby":"newest", "orderbyOp":"desc"}
},{
	path: "pages/about/:item/:data",
	resource: "About",
	action: "index"
},{
	path: "pages/about/:item",
	resource: "About",
	action: "index"
},{
	path: "pages/contact/feedback",
	resource: "Contact",
	action: "feedback"
},{
	path: "pages/admin/:tool",
	resource: "Admin",
	action: "index"
},{
	path: "pages/statistics/:entity/:group/:visual",
	resource: "Statistics",
	action: "index"
},{
	path: "pages/statistics/:entity/:group",
	resource: "Statistics",
	action: "index"
},{
	path: "browse/software",
	resource: "Marketplace",
	action: "software"
},{
	path: "browse/cloud",
	resource: "Marketplace",
	action: "cloud"
},{
	path: "browse/people",
	resource: "Marketplace",
	action: "people"
},{
	path: "browse/vos/software",
	resource: "Marketplace",
	action: "swvos"
},{
	path: "browse/vos/cloud",
	resource: "Marketplace",
	action: "cloudvos"
},{
	path: "browse/sites/cloud",
	resource: "Marketplace",
	action: "cloudsites"
},{
	path: "browse/swappliances/cloud",
	resource: "Marketplace",
	action: "cloudswappliances"
},{
	path: "store/dataset/:name/version/:versionid",
	resource: "Dataset",
	action: "details",
	parameters:{
		"section": "information"
	}
},{
	path: "store/dataset/:name/<section:information|publications>",
	resource: "Dataset",
	action: "details"
},{
	path: "browse/datasets",
	resource: "Marketplace",
	action: "datasets"
},{
	path: "saml",
	resource: "Saml",
	action: "index"
},{
	path: "/",
	resource: "Default",
	action: "index"
}]);
