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
appdb.ModelItemClass = function(opts,ext){
    opts = opts || {};
    ext = ext || {};
    ext.async = ((typeof ext.async === 'boolean'?ext.async:true));
    var _mediator = new appdb.utils.ObserverMediator(), _getCaller = null, _insertCaller = null, _removeCaller = null,_updateCaller = null, _modelProperty= null, _localData = null;
	var _getsuccess = function(d){
		if(d.error){
			_geterror(d);
		}
        if(typeof _modelProperty === "string"){
            d = d[_modelProperty];
        }
		_localData = d;
        _mediator.publish({event : 'select',value : d});
    };
	var _insertsuccess = function(d){
		if(d.error){
			_geterror(d);
		}
		if(typeof _modelProperty === "string"){
            d = d[_modelProperty];
        }
        _mediator.publish({event : 'insert',value : d});
	};
	var _removesuccess = function(d){
		if(d.error){
			_geterror(d);
		}
		if(typeof _modelProperty === "string"){
            d = d[_modelProperty];
        }
        _mediator.publish({event : 'remove',value : d});
	};
	var _updatesuccess = function(d){
		if(d.error){
			_geterror(d);
		}
		if(typeof _modelProperty === "string"){
            d = d[_modelProperty];
        }
        _mediator.publish({event : 'update',value : d});
	};
    var _geterror = function(d){
        _mediator.publish({event : 'error',value : d});
    };
    var _get = function(d){
        var p;
        if(typeof d !== "undefined"){
            if($.isPlainObject(d)){
                p = {query : d};
            }
        }
        _mediator.publish({event:"beforeselect",value : p});
        return _getCaller.call(p);
    };
	var _remove = function(d){
		var p;
		if(typeof d !== "undefined"){
            if($.isPlainObject(d)){
                p = {query : d};
            }
        }
        _mediator.publish({event:"beforeremove",value : p});
        return _removeCaller.call(p);
	};
	var _insert = function(d){
		var p;
		if(typeof d !== "undefined"){
            if($.isPlainObject(d)){
                p = {query : d.query || d};
				if(d.data){
					p.data = d.data;
				}
            }
        }
        _mediator.publish({event:"beforeinsert",value : p});
        return _insertCaller.call(p);
	};
	var _update = function(d){
		var p;
		if(typeof d !== "undefined"){
            if($.isPlainObject(d)){
                p = {query : d.query || d};
				if(d.data){
					p.data = d.data;
				}
            }
        }
        _mediator.publish({event:"beforeupdate",value : p});
        return _updateCaller.call(p);
	};
    var _subscribe = function(s){
        _mediator.subscribe(s);
        return this;
    };
    var _unsubscribe = function(s){
         _mediator.unsubscribe(s);
         return this;
    };
    var _unsubscribeAll = function(c){
        _mediator.unsubscribeAll(c);
         return this;
    };
    var _getQuery = function(){
        return _getCaller.getQuery();
    };
    var _getData = function(){
        return _getCaller.getData();
    };
	var _getLocalData = function(){
		return _localData;
	};
	var _setLocalData = function(d){
		_localData = d;
	};
	var _getLocalDataItemById = function(id){
		if( !id ) return null;
		var local = this.getLocalData();
		var loc = null;
		if(typeof _modelProperty === "string"){
            loc = local[_modelProperty];
        }else{
			loc = local;
		}
		loc = loc || [];
		loc = $.isArray(loc)?loc:[loc];
		
		var found = $.grep(loc, function(e){
			return ( $.trim(e.id) === $.trim(id) );
		});
		return ( found.length > 0 )?found[0]:null;
	};
    var _destroy = function(){
        _mediator.clearAll();
    };
	var _getXhr = function(action){
		if(ext.async===false){
			return null;
		}
		action = $.trim(action).toLowerCase() || "get";
		var clr = _getCaller;
		switch(action){
			case "insert":
			case "put":
				clr = _insertCaller;
				break;
			case "update":
			case "post":
				clr = _updateCaller;
			case "get":
			default:
				clr = _getCaller;
		}
		if( clr && typeof clr.getXhr === "function"){
			return clr.getXhr() || null;
		}
		return null;
	};
    var _init = function(){
        var cl = {
            success : _getsuccess,
            error : _geterror
        };
        ext.caller = $.extend(cl,ext.caller);
        _modelProperty = (ext.modelProperty && (typeof ext.modelProperty === "string"))?ext.modelProperty:null;
        _getCaller = new appdb.utils.rest(ext.caller);
        if(ext.async===false){
            _getCaller = _getCaller.create({query:opts});
        }else{
            _getCaller = _getCaller.create({query:opts},{success : _getsuccess,error : _geterror});
        }

		if(ext.insertCaller){
			_insertCaller = new appdb.utils.rest(ext.insertCaller);
			if(ext.async===false){
				_insertCaller = _insertCaller.create({query:opts});
			}else{
				_insertCaller = _insertCaller.create({query:opts},{success : _insertsuccess,error : _geterror});
			}
		}else{
			_insertCaller = function(){};
		}
		if(ext.removeCaller){
			_removeCaller = new appdb.utils.rest(ext.removeCaller);
			if(ext.async===false){
				_removeCaller = _removeCaller.create({query:opts});
			}else{
				_removeCaller = _removeCaller.create({query:opts},{success : _removesuccess,error : _geterror});
			}
		}else{
			_removeCaller = function(){};
		}
		if(ext.updateCaller){
			_updateCaller = new appdb.utils.rest(ext.updateCaller);
			if(ext.async===false){
				_updateCaller = _updateCaller.create({query:opts});
			}else{
				_updateCaller = _updateCaller.create({query:opts},{success : _updatesuccess,error : _geterror});
			}
		}else{
			_updateCaller = function(){};
		}
    };
    _init();
    return {
        get : _get,
		remove : _remove,
		insert : _insert,
		update : _update,
        getQuery : _getQuery,
        getData : _getData,
		getLocalData : _getLocalData,
		getLocalDataItemById: _getLocalDataItemById,
		getXhr: _getXhr,
		setLocalData: _setLocalData,
        subscribe : _subscribe,
        unsubscribe : _unsubscribe,
        unsubscribeAll : _unsubscribeAll,
        destroy : _destroy
    };
};
appdb.ModelListClass = function(opts,ext){
    opts = opts || {};
    ext = ext || {};
    ext.async = ((typeof ext.async ==='boolean')?ext.async:true);
    var _mediator = new appdb.utils.ObserverMediator(this), _getCaller = null, _pager = null, _localData = null;

    var _getsuccess = function(d){
		_localData = d;
        _mediator.publish({event : 'select',value : d});
    };
    var _geterror = function(d){
        _mediator.publish({event : 'error',value : d});
    };
    this.get = function(d){
        var p;
        if(typeof d !== "undefined"){
            if($.isPlainObject(d)){
                p = {query : d};
            }
        }
        _mediator.publish({event:"beforeselect",value : p});
        var res = _getCaller.call(p);
        return res;
    };
    this.subscribe = function(s){
        _mediator.subscribe(s);
        return this;
    };
    this.unsubscribe = function(s){
         _mediator.unsubscribe(s);
         return this;
    };
    this.unsubscribeAll = function(clr){
        _mediator.unsubscribeAll(clr);
         return this;
    };
    this.getPager = function(){
        if(_pager===null){
            var pageopt = {};
            if(opts.pagelength){
                pageopt["length"] = opts.pagelength || ext.pagelength;
            }
            if(opts.pageoffset){
                pageopt["offset"] = opts.pageoffset;
            }
            pageopt["model"] = this;
            pageopt["modelProperty"] = ext.modelProperty;
            _pager = new appdb.utils.Pager(pageopt);
        }
        return _pager;
    };
    this.getQuery = function(){
        return _getCaller.getQuery();
    };
    this.getData = function(){
        return _getCaller.getData();
    };
	this.getLocalData = function(){
		return _localData;
	};
	this.getLocalDataItemById = function(id){
		if( !id ) return null;
		var local = this.getLocalData();
		var loc = null;
		if(typeof _modelProperty === "string"){
            loc = local[_modelProperty];
        }else{
			loc = local;
		}
		loc = loc || [];
		loc = $.isArray(loc)?loc:[loc];
		
		var found = $.grep(loc, function(e){
			return (  $.trim(e.id) === $.trim(id) );
		});
		return ( found.length > 0 )?found[0]:null;
	};
	this.setLocalData = function(d){
		_localData = d;
	};
    this.destroy = function(){
        _mediator.clearAll();
    };
	this.getXhr = function(action){
		if(ext.async===false){
			return null;
		}
		if( _getCaller && typeof _getCaller.getXhr === "function"){
			return _getCaller.getXhr() || null;
		}
		return null;
	};
    var _init = function(){
        var cl = {
            success : _getsuccess,
            error : _geterror
        };

        if(typeof ext.caller === "undefined" && ext.localData){
             _getCaller = new appdb.utils.LocalDataStore({localData : ext.localData});
        }else{
            ext.caller = $.extend(cl,ext.caller);
             _getCaller = new appdb.utils.rest(ext.caller);
        }
        if(ext.async===false){
            _getCaller = _getCaller.create({query:opts});
        }else{
            _getCaller = _getCaller.create({query:opts},{success : _getsuccess,error : _geterror});
        }
    };
    _init();
    this.Obs = _mediator;
};
appdb.model = {};
appdb.model.Person = function(opts,ext){
    var _init  = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/{id}%3F{*}"
        };
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people",
			action: "POST"
		};
		ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people",
			action: "PUT"
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/{id}",
			action: 'DELETE'
		};
        ext.modelProperty = "person";
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.People = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
		var subtype = "";
		if ( opts.peopletype ) {
			if ( opts.peopletype === "deleted" ) {
				subtype = "/deleted";
				delete opts.peopletype;
			}else{
				subtype = "";
			}
		}
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people" + subtype + "%3F{*}"
        };
        ext.modelProperty = "person";
        return new appdb.ModelListClass(opts,ext);
    };
    return _init();
};
appdb.model.PeopleApplications = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/{id}/applications/{applicationtype}%3F{*}"
        };
        ext.modelProperty = "application";
        return new appdb.ModelListClass(opts,ext);
    };
    return _init();
};
appdb.model.PeopleVOs = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/{id}/vos/{vomembership}%3F{*}"
        };
        ext.modelProperty = "vo";
        return new appdb.ModelListClass(opts,ext);
    };
    return _init();
};
appdb.model.PeopleApplicationLogistics = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/"+userID+"/applications/{applicationtype}/logistics%3F{*}"
        };
        ext.modelProperty = "application";
        return new appdb.ModelListClass(opts,ext);
    };
    return _init();
};
appdb.model.Applications = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
		var subtype = "";
		if ( opts.applicationtype ) {
			if ( opts.applicationtype === "moderated" ) {
				subtype = "/moderated";
				delete opts.applicationtype;
			} else if ( opts.applicationtype === "deleted" ) {
				subtype = "/deleted";
				delete opts.applicationtype;
			}
		}
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications" + subtype + "%3F{*}"
		};
        ext.modelProperty = "application";
        return new appdb.ModelListClass(opts, ext);
    };
    return _init();
};
appdb.model.ApplicationsListing = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
		var subtype = "";
		if ( opts.applicationtype ) {
			if ( opts.applicationtype === "moderated" ) {
				subtype = "/moderated";
				delete opts.applicationtype;
			} else if ( opts.applicationtype === "deleted" ) {
				subtype = "/deleted";
				delete opts.applicationtype;
			}
		}
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications" + subtype + "%3F{*}%26listmode%3Dlisting"
		};
        ext.modelProperty = "application";
        return new appdb.ModelListClass(opts, ext);
    };
    return _init();
};
appdb.model.Application = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications",
			action: 'POST'
			
		};
		ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications",
			action: 'PUT'
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}",
			action: 'DELETE'
		};
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.ApplicationHistory = function(opts, ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/history/{histid}"
        };
        return new appdb.ModelItemClass(opts,ext);
    };
    return _init();
};
appdb.model.ModeratedApplications = function(opts, ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/moderated"
        };
        ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/moderated",
			action: 'PUT'
        };
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/moderated/{id}",
			action: 'DELETE'
		};
        return new appdb.ModelItemClass(opts,ext);
    };
    return _init();
};
appdb.model.RelatedApplications = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
		var subtype = "";
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{appid}/relatedapps%3F{*}"
		};
        ext.modelProperty = "relatedapp.application";
        return new appdb.ModelListClass(opts, ext);
    };
    return _init();
};
appdb.model.FollowedApplications = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/"+userID+"/applications/followed"
		};
        ext.modelProperty = "application";
        return new appdb.ModelListClass(opts, ext);
    };
    return _init();
};
appdb.model.FollowApplication = function(opts,ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint : appdb.config.endpoint.base + "news/getsubscription?flt={flt}&subjecttype={subjecttype}&entryid={entryid}"
		};
		ext.insertCaller = {
			endpoint : appdb.config.endpoint.base + "apps/togglefollow?id={id}&entryid={entryid}"
		};
		ext.removeCaller = {
			endpoint : appdb.config.endpoint.base + "apps/togglefollow?id={id}&entryid={entryid}&src=ui"
		};
		ext.updateCaller = {
			endpoint : appdb.config.endpoint.base + "apps/togglefollow?id={id}&entryid={entryid}"
		};
		return new appdb.ModelItemClass(opts,ext);
	};
	return _init();
};
appdb.model.Genders = {
        get : function(){
            return [{key : "male", name : "Male"},
                {key : "robot", name : "Robot"},
                {key : "female", name : "Female"},
                {key : "", name : "N\\A"}];
        }
    };
appdb.model.Disciplines = function(opts,ext){
	return new function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=disciplines"
		};
		var res = new appdb.ModelListClass(opts,ext);
		res.get();
		return res;
	};
};
appdb.model.Middlewares = (function(opts,ext){
	return new function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=middlewares"
		};
		var res = new appdb.ModelListClass(opts,ext);
		return res;
	};
})();
appdb.model.Regional = (function(){
    var  opts = {}, ext = {},local,_countries = [],_regions=[],_providers=[],
    _getCountries = function(){
        return _countries;
    },
    _getRegions = function(){
        return _regions;
    },
    _getProviders = function(){
        return _providers;
    },
	_setLocalData = function(d){
		if(d.country){
			if($.isArray(d.country)===false){
				d.country = [d.country];
			}
			_countries = d.country;
		}
		if(d.region){
			if($.isArray(d.region)===false){
				d.region = [d.region];
			}
			_regions = d.region;
		}
		if(d.provider){
			if($.isArray(d.provider)===false){
				d.provider = [d.provider];
			}
			_providers = d.provider;
		}
	},
	_refresh = function(){
		local.get();
	},
    _instance = {Countries : {get : _getCountries}, Regions : {get : _getRegions}, Providers: {get : _getProviders}, setLocalData: _setLocalData, get: _refresh},
    _error = function(d){
        console.log("[appdb.model.Regional]:Could not load regional data:"+d.description,d);
    },
    _success=function(d){
		_setLocalData(d);
    };

    ext.caller = {
		endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=regional"
    };
    local = new appdb.ModelListClass(opts, ext);
	local.subscribe({event:'select',callback: _success});
    local.subscribe({event:'error',callback:_error});
	return _instance;
})();
appdb.model.Roles = function(){
    var _roles=[],local,
    _getRoles = function(){
        return _roles;
    },_instance ={get : _getRoles},
    ext = {caller : {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/roles"
	}};
    var _success = function(d){
        _roles = d.role;
        if($.isArray(_roles)===false){
            _roles = [_roles];
        }
        local.destroy();
        local=null;
    };
    var _error = function(d){
        console.log("[appdb.model.Roles]:Could not load roles data:"+d.description,d);
        local.destroy();
        local = null;
    };
    local = new appdb.ModelListClass({}, ext);
    local.subscribe({event:'select',callback:_success});
    local.subscribe({event:'error',callback:_error});
    local.get();
    return _instance;
};
appdb.model.ContactTypes = function(){
    var _ctypes =[],local,
    _getCTypes = function(){
        return _ctypes;
    },_instance = {get : _getCTypes},
    ext = {
		caller:{
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/contacttypes"}
	},
    _success = function(d){
        _ctypes = d.contact;
        if($.isArray(_ctypes)===false){
            _ctypes = [_ctypes];
        }
        local.destroy();
        local = null;
    },
    _error = function(d){
        console.log("[appdb.model.ContactTypes]:Could not load contact types:"+d.description,d);
        local.destroy();
        local = null;
    };
    local = new appdb.ModelListClass({}, ext);
    local.subscribe({event:'select',callback:_success});
    local.subscribe({event:'error',callback:_error});
    local.get();
    return _instance;
};
appdb.model.VOs = function(opts,ext){
  var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=vos%3F{*}"
        };
        ext.modelProperty = "vo";
        return new appdb.ModelListClass(opts,ext);
    };
    return _init();
};
appdb.model.ApplicationRatings = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/ratings"
        };
        ext.modelProperty = "rating";
        return new appdb.ModelListClass(opts,ext);
    };
    return _init();
};
appdb.model.RatingReport = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{appid}/ratingsreport/{type}"
        };
        ext.modelProperty = "ratingreport";
        return new appdb.ModelListClass(opts,ext);
    };
    return _init();
};
appdb.model.Tags = (function(opts,ext){
	return new function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/tags"
		};
		return new appdb.ModelListClass(opts,ext);
	};
})();
appdb.model.ApplicationTags = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/tags"
        };
        ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/tags/{tagid}",
			action: 'DELETE'
        };
        ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/tags",
			action: 'PUT'
        };
        return new appdb.ModelItemClass(opts,ext);
    };
    return _init();
};
appdb.model.ApplicationTagPolicy = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}"
        };
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications",
			action: 'POST'
		};
		ext.modelProperty = "application";
        return new appdb.ModelItemClass(opts,ext);
    };
    return _init();
};
appdb.model.ApplicationBookmarks = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/{id}/applications/bookmarked"
        };
        ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/{id}/applications/bookmarked/{appid}",
			action: 'DELETE'
        };
        ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/{id}/applications/bookmarked",
			action: 'PUT'
        };
        return new appdb.ModelItemClass(opts,ext);
    };
    return _init();
};
appdb.model.MailSubscription = function(opts,ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint : appdb.config.endpoint.base + "news/getsubscription?flt={flt}&subjecttype={subjecttype}"
		};
		ext.insertCaller = {
			endpoint : appdb.config.endpoint.base + "news/subscribe?flt={flt}&name={name}&subjecttype={subjecttype}&events={events}&delivery={delivery}"
		};
		ext.removeCaller = {
			endpoint : appdb.config.endpoint.base + "news/unsubscribe?id={id}&pwd={pwd}&src=ui"
		};
		ext.updateCaller = {
			endpoint : appdb.config.endpoint.base + "news/subscribe?id={id}&flt={flt}&name={name}&subjecttype={subjecttype}&events={events}&delivery={delivery}"
		};
		return new appdb.ModelItemClass(opts,ext);
	};
	return _init();
};
appdb.model.RoleMailSubscription = function(opts,ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint : appdb.config.endpoint.base + "news/getrolesubscription"
		};
		ext.insertCaller = {
			endpoint : appdb.config.endpoint.base + "news/subscribe?flt={flt}&name={name}&subjecttype=ppl&events=96&delivery=1"
		};
		ext.removeCaller = {
			endpoint : appdb.config.endpoint.base + "news/unsubscribe?id={id}&pwd={pwd}&src=ui"
		};
		return new appdb.ModelItemClass(opts,ext);
	};
	return _init();
};
appdb.model.NoDisseminationSubscription = function(opts,ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint : appdb.config.endpoint.base + "people/nodissemination"
		};
		ext.insertCaller = {
			endpoint : appdb.config.endpoint.base + "people/nodissemination?value=false"
		};
		ext.removeCaller = {
			endpoint : appdb.config.endpoint.base + "people/nodissemination?value=true"
		};
		return new appdb.ModelItemClass(opts,ext);
	};
	return _init();
};
appdb.model.MailSubscription.defaultNotification = null;
appdb.model.MailSubscription.roleNotification = null;
appdb.model.MailSubscription.noDissemination = null;
appdb.model.PrimaryContact = function(opts,ext){
    var _init = function(){
        opts = opts || {};
        ext = ext || {};
        ext.updateCaller = {
            endpoint : appdb.config.endpoint.base + "people/primarycontact?act=set&id={id}"
        };
        ext.caller = {
            endpoint : appdb.config.endpoint.base + "people/primarycontact?act=get"
        };
        return new appdb.ModelItemClass(opts,ext);
    };
    return _init();
};
appdb.model.PrimaryContact.userPrimaryContact = null;
appdb.model.PrimaryContact.userPrimaryContactId = null;
appdb.model.NameAvailability = function(opts,ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint : appdb.config.endpoint.base + "apps/nameavailable?n={name}&id={appid}"
		};
		return new appdb.ModelItemClass(opts,ext);
	};
	return _init();
};
appdb.model.UsedAppUrlTitles = (function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = {
   endpoint : appdb.config.endpoint.base + "apps/usedurltitles"
  };
  return new appdb.ModelItemClass(opts, ext);
 };

 var res = _init();
 if(userID !== null){
	 res.get();
 }
 return res;
})();
appdb.model.ApplicationCategories = (function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = {
	  endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/categories"
  };
  ext.modelProperty = "category";
  return new appdb.ModelListClass(opts, ext);
 };
 return _init();
})();
//Sending request to join the logged in user as an application's contact
appdb.model.JoinContacts = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = { //Gets if user is already associated, or has a pending request.
   endpoint : appdb.config.endpoint.base + "apps/joinrequest?id={appid}&state"
  };
  ext.insertCaller = { //Sends a request to associate with an application with an optional message to the reciepients.
   endpoint : appdb.config.endpoint.base + "apps/joinrequest?id={appid}&m={message}"
  };
  return new appdb.ModelItemClass(opts, ext);
 };
 return _init();
};
//Sending request to join the logged in user as an application's contact
appdb.model.ReleaseManagerRequest = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = { //Gets if user is already associated with 
   endpoint : appdb.config.endpoint.base + "apps/requestreleasemanager?id={appid}&state"
  };
  ext.insertCaller = { //Sends a request to associate with an application with an optional message to the reciepients.
   endpoint : appdb.config.endpoint.base + "apps/requestreleasemanager?id={appid}&m={message}"
  };
  return new appdb.ModelItemClass(opts, ext);
 };
 return _init();
};
//Get a list of user requests for the logged in user
appdb.model.UserRequests = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = { // Get a list of user requests for the current logged in user
   endpoint : appdb.config.endpoint.base + "people/userrequests?list"
  };
  ext.updateCaller = { // State can be any id from UserRequestStates db table
   endpoint : appdb.config.endpoint.base + "people/userrequests?id={requestid}&state={stateid}"
  };
  return new appdb.ModelItemClass(opts, ext);
 };
 return _init();
};
appdb.model.Dissemination = function(opts,ext){
 var _init = function(){
        opts = opts || {};
        ext = ext || {};
		ext.caller = {
	  		endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=dissemination%3F{*}"
		};
        ext.modelProperty = "dissemination";
        return new appdb.ModelListClass(opts, ext);
    };
    return _init();
};
appdb.model.AppplicationMessages = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = { // Get a list of user requests for the current logged in user
   endpoint : appdb.config.endpoint.base + "apps/sendmessage?list&id={appid}"
  };
  ext.insertCaller = { // State can be any id from UserRequestStates db table
   endpoint : appdb.config.endpoint.base + "apps/sendmessage?id={appid}&rid={recipientid}&m={message}"
  };
  return new appdb.ModelItemClass(opts, ext);
 };
 return _init();
};
appdb.model.ApiKeyList = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = { // Get a list of API keys generated for the current logged in user
   endpoint : appdb.config.endpoint.base + "people/apikeylist"
  };
  ext.insertCaller = { // Generate a new API key for the current logged in user
   endpoint : appdb.config.endpoint.base + "people/apikeylist",
   action: "PUT"
  };
  ext.updateCaller = { // Send netfilters for given api key for the current user
	  endpoint: appdb.config.endpoint.base + "people/apikeylist?k={keyid}",
	  action: "POST"
  };
  ext.removeCaller = { //Remove an api key
	  endpoint: appdb.config.endpoint.base + "people/apikeylist?k={keyid}",
	  action: "DELETE"
  };
  return new appdb.ModelItemClass(opts, ext);
 };
 return _init();
};
appdb.model.AccessTokenList = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = { // Get a list of API keys generated for the current logged in user
   endpoint : appdb.config.endpoint.base + "people/accesstokenlist"
  };
  ext.insertCaller = { // Generate a new API key for the current logged in user
   endpoint : appdb.config.endpoint.base + "people/accesstokenlist",
   action: "PUT"
  };
  ext.updateCaller = { // Send netfilters for given api key for the current user
	  endpoint: appdb.config.endpoint.base + "people/accesstokenlist?k={tokenid}",
	  action: "POST"
  };
  ext.removeCaller = { //Remove an api key
	  endpoint: appdb.config.endpoint.base + "people/accesstokenlist?k={tokenid}",
	  action: "DELETE"
  };
  return new appdb.ModelItemClass(opts, ext);
 };
 return _init();
};
appdb.model.ApiKeyAuthentication = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = { // Get a list of API keys generated for the current logged in user
   endpoint : appdb.config.endpoint.base + "people/authentication"
  };
  ext.insertCaller = { // Generate a new API key for the current logged in user
   endpoint : appdb.config.endpoint.base + "people/authentication",
   action: "PUT"
  };
  ext.updateCaller = { // Send netfilters for given api key for the current user
	  endpoint: appdb.config.endpoint.base + "people/authentication?k={keyid}",
	  action: "POST"
  };
  return new appdb.ModelItemClass(opts, ext);
 };
 return _init();
};
appdb.model.ApplicationValidation = function(opts,ext){
	var _init = function(){
		opt = opts || {};
		ext = ext || {};
		ext.updateCaller = { // Send netfilters for given api key for the current user
			endpoint: appdb.config.endpoint.base + "apps/validateapp",
			action: "POST"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.AlphanumericReport = function(opts,ext){
	var _init = function(){
		opt = opts || {};
		ext = ext || {};
		opt.type = opt.type || "applications";
		opt.type = opt.type.toLowerCase();
		var cntrl = "";
		switch(opt.type){
			case "people":
				cntrl = "ppl";
				break;
			case "vos":
				cntrl = "vos";
				break;
			default:
				cntrl = "apps";
				break;
		}
		delete opt.type;
		ext.caller = { 
			endpoint: appdb.config.endpoint.base + cntrl + "/alphanumericreport?flt={flt}&subtype={subtype}",
			action: "GET"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.Faq = function(opts, ext){
	var _init = function(){
		opt = opts || {};
		ext = ext || {};
		ext.caller = { // Get a list of user requests for the current logged in user
			endpoint : appdb.config.endpoint.base + "help/editfaq?id={id}"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.base + "help/faq",
			action: "POST"
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.base + "help/faq?id={id}",
			action: "DELETE"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.FaqReorder = function(opts, ext){
	var _init = function(){
		opt = opts || {};
		ext = ext || {};
		ext.updateCaller = { // Post  array 'ordering' with tyhe new ordering of faqs 
			endpoint : appdb.config.endpoint.base + "help/faqreorder",
			action: "POST"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.SoftwareLogistics = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = {
	  endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/logistics%3F{*}"
  };
  ext.modelProperty = "logistics";
  return new appdb.ModelListClass(opts, ext);
 };
 return _init();
};
appdb.model.RelatedSoftwareLogistics = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = {
	  endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/relatedapps/logistics%3F{*}"
  };
  ext.modelProperty = "logistics";
  return new appdb.ModelListClass(opts, ext);
 };
 return _init();
};
appdb.model.PeopleLogistics = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = {
	  endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=people/logistics%3F{*}"
  };
  ext.modelProperty = "logistics";
  return new appdb.ModelListClass(opts, ext);
 };
 return _init();
};
appdb.model.VOsLogistics = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = {
	  endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=vos/logistics%3F{*}"
  };
  ext.modelProperty = "logistics";
  return new appdb.ModelListClass(opts, ext);
 };
 return _init();
};
appdb.model.SitesLogistics = function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = {
	  endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=sites/logistics%3F{*}"
  };
  ext.modelProperty = "logistics";
  return new appdb.ModelListClass(opts, ext);
 };
 return _init();
};
appdb.model.CategoryInfo = (function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = {
	  endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/categories/{id}"
  };
  ext.modelProperty = "category";
  return new appdb.ModelItemClass(opts, ext);
 };
 return _init();
})();
appdb.model.DisciplineInfo = (function(opts,ext){
 var _init = function(){
  opt = opts || {};
  ext = ext || {};
  ext.caller = {
	  endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=disciplines/{id}"
  };
  ext.modelProperty = "discipline";
  return new appdb.ModelItemClass(opts, ext);
 };
 return _init();
})();
appdb.model.StaticList = {
	Categories: [],
	Disciplines:[],
	Subdisciplines: [],
	Middlewares: [],
	VOs: [],
	Countries: [],
	Statuses: [],
	Phonebook: [],
	ProgLangs: [],
	SoftwareLogistics: [],
	PeopleLogistics: [],
	Oses: [],
	OsFamilies: [],
	Archs: [],
	Hypervisors: [],
	ImageFormats: [],
	Licenses: [],
	AccessGroups: [],
	Roles: [],
	VAProviders: [],
	RelationTypes: [],
	ContextFormats: [],
	SwapplianceReport: [],
	SwapplianceReportUnique: [],
	ExchangeFormats: [],
	ConnectionTypes: [],
	SiteList: [],
	DatasetList: [],
	VmiAccelerators: [{id: -1, val: function() { return 'None';}}, {id: 'GPU', val: function() {return 'GPU';}}],
	VMCores: [0,1,2,4,8,16]
};
appdb.model.VirtualAppliance = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/virtualization"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/virtualization",
			action: 'POST'
			
		};
		ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/virtualization",
			action: 'PUT'
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/virtualization/{vaid}/{versionid}",
			action: 'DELETE'
		};
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.UserInbox = function(opts, ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.base + "users/inbox?{#}"
		};
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.VAppliancePrivacyState = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint: appdb.config.endpoint.base + "apps/privacy?id={entityid}"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.base + "apps/privacy?id={entityid}",
			action: 'POST'
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.EntityPrivileges = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		
		ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource={entitytype}/{entityid}/privileges"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.base + "{entitycontroller}/privs?id={entityid}",
			action: 'POST'
			
		};
		ext.insertCaller = {
			endpoint: appdb.config.endpoint.base + "{entitycontroller}/privs?id={entityid}",
			action: 'PUT'
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.base + "{entitycontroller}/privs?id={entityid}",
			action: 'DELETE'
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.SoftwarePrivileges = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		opts.entitytype = "applications";
		opts.entitycontroller = "apps";
		opts.entityid = opts.id;
		delete opts.id;
		return new appdb.model.EntityPrivileges(opts, ext);
	};
	return _init();
};
appdb.model.VAppliancePrivileges = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		opts.entitytype = "applications";
		opts.entitycontroller = "apps";
		opts.entityid = opts.id;
		delete opts.id;
		return new appdb.model.EntityPrivileges(opts, ext);
	};
	return _init();
};
appdb.model.VOWideImageList = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.updateCaller = { // Send netfilters for given api key for the current user
			endpoint: appdb.config.endpoint.base + "vo/imagelist",
			action: "POST"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.VapplianceSites = function(opts, ext){
	var _init = function(){
		opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/virtualization/productionimages"
		};
		ext.modelProperty = "image";
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.ContactVos = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.updateCaller = { // Send netfilters for given api key for the current user
			endpoint: appdb.config.endpoint.base + "apps/contactvos?id={id}",
			action: "POST"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.VMIContextualizationScript = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.updateCaller = { // Send netfilters for given api key for the current user
			endpoint: appdb.config.endpoint.base + "apps/vmicontextscript?vmiid={id}&url={url}&appid={appid}&formatid={formatid}",
			action: "POST"
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.base + "apps/vmicontextscript?vmiid={id}&url={url}&appid={appid}",
			action: "DELETE"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.Site = function(opts, ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=sites/{id}"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=sites",
			action: 'POST'
			
		};
		return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.Sites = function(opts, ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=sites%3F{*}"
		};
        ext.modelProperty = "site";
        return new appdb.ModelListClass(opts, ext);
    };
    return _init();
};
appdb.model.Harvester = {};
appdb.model.Harvester.Projects = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = { 
			endpoint: appdb.config.endpoint.base + "harvest/search?archive=1&search={search}&limit={limit}",
			action: "GET"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.Harvester.Organizations = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = { 
			endpoint: appdb.config.endpoint.base + "harvest/search?archive=3&search={search}&limit={limit}",
			action: "GET"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.Harvester.Persons = function(opts, ext){
	var _init = function(){
		opts = opts || {};
		ext = ext || {};
		ext.caller = { 
			endpoint: appdb.config.endpoint.base + "harvest/search?archive=4&search={search}&limit={limit}",
			action: "GET"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.Harvester.Software = function(opts, ext){
	return new appdb.model.Applications();
};
appdb.model.Harvester.Vappliance = function(opts, ext){
	return new appdb.model.Applications();
};
appdb.model.Contextualization = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization",
			action: 'POST'
			
		};
		ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization",
			action: 'PUT'
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization/{scriptid}",
			action: 'DELETE'
		};
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.ContextualizationMetadata = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization/metadata"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization/metadata",
			action: 'POST'
		};
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.ContextualizationScript = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization/{scriptid}"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization/{scriptid}%3F{*}",
			action: 'POST'
			
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization/{scriptid}",
			action: 'DELETE'
		};
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.SwapplianceSites = function(opts, ext){
	var _init = function(){
		opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=applications/{id}/contextualization/productionimages"
		};
		ext.modelProperty = "image";
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.DSExchangeFormats = function(opts,ext){
	var _init = function(){
		opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/exchangeformats"
		};
		
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.DSConnectionTypes = function(opts,ext){
	var _init = function(){
		opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/connectiontypes"
		};
		
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};
appdb.model.Datasets = function(opts,ext){
	var _init = function(){
		opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets%3F{*}"
		};
		
		ext.modelProperty = "dataset";
        return new appdb.ModelListClass(opts,ext);
	};
	return _init();
};
appdb.model.Dataset = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets",
			action: 'POST'
			
		};
		ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets",
			action: 'PUT'
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}",
			action: 'DELETE'
		};
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.DatasetVersions = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions",
			action: 'POST'
		};
		ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions",
			action: 'PUT'
		};
		
        ext.modelProperty = "version";
        return new appdb.ModelListClass(opts,ext);
    };
    return _init();
};
appdb.model.DatasetVersion = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions/{versionid}"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions",
			action: 'POST'
		};
		ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions",
			action: 'PUT'
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions/{versionid}",
			action: 'DELETE'
		};
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};
appdb.model.DatasetVersionLocations = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions/{versionid}/locations"
		};
		ext.updateCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions/{versionid}/locations",
			action: 'POST'
		};
		ext.insertCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions/{versionid}/locations",
			action: 'PUT'
		};
        ext.modelProperty = "location";
        return new appdb.ModelListClass(opts,ext);
    };
    return _init();
};
appdb.model.DatasetVersionLocation = function(opts,ext){
	var _init = function(){
        opts = opts || {};
        ext = ext || {};
		
        ext.caller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions/{versionid}/locations/{locationid}"
		};
		ext.removeCaller = {
			endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=datasets/{id}/versions/{versionid}/locations/{locationid}",
			action: 'DELETE'
		};
        return new appdb.ModelItemClass(opts, ext);
    };
    return _init();
};