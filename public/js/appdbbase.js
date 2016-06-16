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
appdb.components = {};
appdb.Component = appdb.DefineClass("appdb.Component",function(o){
    this._mediator = null;
    this.ErrorHandler = new appdb.views.ErrorHandler();
    this.subscribe = null;
    this.publish = null;
    this.unsubscribe = null;
    this.unsubscribeAll = null;
    this.clearObserver = null;
    this.views = {};
    this._model = null;
    this._title = "";
    this._dom = null;
    this.ext = (o)?(o.ext || {}):{};
    this.getComponentType = function(){
        return this._type_.getName();
    };
    this.getComponentName = function(){
        var n = this.getComponentType();
        n = n.split(".");
        return n[n.length-1];
    };
    this.getComponentTitle = function(){
        return this._componentTitle;
    };
    this.setComponentTitle = function(v){
        this._componentTitle = v;
        return this;
    };
    this.clearEvents = function(){
        var i, v = this.views;
        for(i in v){
            if(v[i].unsubscribeAll){
                v[i].unsubscribeAll(this);
            }
        }
        if(this._model){
            this._model.unsubscribeAll(this);
        }
        return this;
    };
    this.getModel = function(){
        return this._model || null;
    };
    this.setModel = function(v){
        this._model = v;
        return this;
    };
    this.reload = function(o){
        this._model.get(o);
    };
    this.destroy = function(){
        var i, v = this.views;
        for(i in v){
            if(v[i].destroy){
                v[i].destroy();
            }
        }
        if(this._model){
            this._model.destroy();
        }
        this._mediator.clearAll();
        if(this._dom){
            $(this._dom).empty();
        }
        return this;
    };
	this.getClosestParent = function(parenttype){
		parenttype = $.trim(parenttype);
		if( parentytpe && $.isPlainObject(this.options) && this.options.parent ){
			var maxlevel = 30;
			var res = this;
			while(maxlevel>0){
				maxlevel-=1;
				if( !res.options.parent ) return null;
				if( res.options.parent._type_ && res.options.parent._type_.getName ){
					if( res.options.parent._type_.getName() === parenttype ){
						return res.options.parent;
					}
				}
				res = res.options.parent;
			}
		}
		return null;
	};
    this.setState = function(v){};
    this.getState = function(){return null;};
	this.displayType = (function(parent){
		var dt = 'inline';
		return function(v){
			if(typeof v === "string"){
				dt = v;
				return parent;
			}
			return dt;
		};
	})(this);
    this.setViewsParent = function(p){
        p = p || this;
       for(var i in this.views){
           this.views[i].parent = p;
       }
    };
    var _constructor = function(){
            this._mediator = new appdb.utils.ObserverMediator(this);
            this.subscribe = this._mediator.subscribe;
            this.publish = this._mediator.publish;
            this.unsubscribe = this._mediator.unsubscribe;
            this.unsubscribeAll = this._mediator.unsubscribeAll;
            this.clearObserver = this._mediator.clearAll;
            this._componentTitle = this.getComponentName();
        };
    _constructor.call(this);
});
appdb.components.LogisticsSelector = appdb.ExtendClass(appdb.Component, "appdb.components.LogisticsSelector", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		entityType: o.entityType || "software",
		minCount: o.minCount || 1,
		subtype: $.trim(o.subtype).toLowerCase(),
		items: [],
		dom: {
			a: $(document.createElement("a")).attr("href","#"),
			list: $(document.createElement("ul")).addClass("menu").addClass("horizontal").addClass("logistics")
		}
	};
	this.abort = function(){
		if( !this._model ) return;
		var x = (this._model.getXhr)?this._model.getXhr():null;
		if( x && typeof x.abort === "function"){
			appdb.debug("[DEBUG]: Aborting logistics");
			x.abort();
		}
		appdb.debug("[DEBUG]: Aborting logistics");
	};
	this.canAddEntry = function(entryType, entryData){
		entryData = entryData || [];
		entryData = $.isArray(entryData)?entryData:[entryData];
		if( this.options.minCount > entryData.length ){
			return false;
		}
		if( this.getMetaData(entryType, "display") === false ) return false;
		var trf = this.getMetaData(entryType,"transformData");
		if( typeof trf === "function" ){
			var trd = trf(entryData);
			if(trd.length === 0 ){
				return false;
			}
		}
		return true;
	};
	this.addEntry = function(entryType, entryData, opts){
		entryData = entryData || [];
		entryData = $.isArray(entryData)?entryData:[entryData];
		opts = opts || {};
		var dom = opts.dom || $(document.createElement("li")).addClass("logistics").addClass(entryType);
		var entry = new appdb.views.RefinedFilterType({
			container: dom,
			parent: this,
			entityType: entryType,
			data: entryData,
			dataProvider: opts.dataProvider || this.getMetaData(entryType,"dataProvider"),
			minCount: opts.minCount || this.getMetaData(entryType,"minCount"),
			maxCount: opts.maxCount || this.getMetaData(entryType,"maxCount"),
			maxColumns: opts.maxColums || this.getMetaData(entryType,"maxColumns"),
			maxLength: opts.maxLength || this.getMetaData(entryType,"maxLength"),
			displayName: opts.displayName || this.getMetaData(entryType,"displayName"),
			overflow: opts.overflow || this.getMetaData(entryType,"overflow"),
			orderBy: opts.orderBy || this.getMetaData(entryType,"orderBy"),
			getValueId: opts.getValueId || this.getMetaData(entryType,"getValueId"),
			canRenderEntry: opts.canRenderEntry || this.getMetaData(entryType,"canRenderEntry"),
			transformData: opts.transformData || this.getMetaData(entryType,"transformData"),
			css: this.getMetaData(entryType,"css") || opts.css  
			
		});
		this.options.items.push(entry);
		entry.render(entryData);
		entry.subscribe({event: "selected", callback: function(v){
			this.publish({event: "addfilter", value: {type:v.type, value: v.id, source: "system"}});
		},caller: this});
		$(dom).data("name",entryType);
		return dom;
	};
	this.renderLoading = function(enable){
		enable = $.type(enable)?enable:false;
		if( enable === true ){
			$(this.options.dom.list).css({"visibility":"hidden"});
		}else{
			$(this.options.dom.list).css({"visibility":"visible"});
		}
	};
	this.exclude = function(name,data,exclude){
		exclude = exclude || [];
		exclude = $.isArray(exclude)?exclude:[exclude];
		data = data || [];
		data = $.isArray(data)?data:[data];
		var ex = [];
		$.each(exclude, function(i,e){
			if( $.trim(e.type) === $.trim(name) ){
				$.each(data,function(ii,ee){
					if( $.trim(ee.id) === $.trim(e.value) ){
						ex.push(ii);
					}
				});
			}
		});
		if( ex.length > 0 ){
			ex.sort().reverse();
			$.each(ex, function(i,e){
				data.splice(e,1);
			});
		}
		return data;
	};
	this.renderMoreItemsHide = function(data,dom){
		$.each(data, (function(self){
			return function(i, e){
				if( self.canAddEntry(e.entryType, e.value) ){
					var lidom = self.addEntry(e.entryType, e.value, {css: "noalign"});
					$(lidom).addClass("overflowed");
					$(self.options.dom.list).append(lidom);
				}
			};
		})(this));
		
		var a = $(dom).children("a");
		$(a).parent().addClass("overflowaction").siblings("li.overflowed").hide();
		$(a).unbind("click").bind("click", function(ev){
			ev.preventDefault();
			$(this).fadeOut(100,function(sibls){
				return function(){
					$(this).addClass("hidden");
					$(sibls).fadeIn(100);
				};
			}( $(this).parent().siblings("li.overflowed")) );
			return false;
		});
		
		
	};
	this.renderMoreItemsMenu = function(data,dom){
		var ul = $(document.createElement("ul")).addClass("vertical");
		$(dom).append(ul);
		$.each(data, (function(self){
			return function(i,e){
				var entry = self.addEntry(e.entryType, e.value, {
					css: "noalign"
				});
				$(ul).append(entry);
			};
		})(this));
		$(dom).children("ul").children("li").children("a").unbind("click");
	};
	this.renderMoreItems = function(data){
		var li = $(document.createElement("li")).addClass("logistics").addClass("more");
		var a = $(document.createElement("a")).attr("href","#").text(this.getMetaData("more", "displayName")||"more");
		$(a).attr("title",this.getMetaData("more", "title"));
		var moreType = this.getMetaData("more", "displayType") || "";
		$(li).append(a);
		$(this.options.dom.list).append(li);
		
		switch( moreType.toLowerCase() ){
			case "menu":
				this.renderMoreItemsMenu(data,li);
				break;
			case "hide":
			default:
				this.renderMoreItemsHide(data,li);
				break;
		}
	};
	this.getMaxDisplayedItems = function(){
		var result = -1;
		var mdi = appdb.components.LogisticsSelector.maxDisplayItems;
		if ( $.isPlainObject(mdi) ){
			mdi = mdi[this.options.entityType] || mdi["default"];
		} 
		if ( !isNaN(parseFloat(mdi)) && isFinite(mdi) ){
			return mdi << 0;
		}
		return result;
	};
	this.render = function(d,exclude){
		d = d || {};
		$(this.dom).empty();
		$(this.options.dom.list).empty();
		$(this.dom).append(this.options.dom.list);
		var maxDisplayItems = this.getMaxDisplayedItems();
		var moreItems = [];
		for(var i in d){
			if( !d.hasOwnProperty(i) ) return;
			d[i] = this.exclude(i,d[i],exclude);
			if( this.canAddEntry(i, d[i]) ){
				if( maxDisplayItems && $(this.options.dom.list).children("li").length < maxDisplayItems ){
					var dom = this.addEntry(i,d[i]);
					$(this.options.dom.list).append(dom);
				}else{
					moreItems.push({entryType: i, value: d[i]});
				}
				
			}
		}
		if( moreItems.length > 0 ){
			this.renderMoreItems(moreItems);
		}
		$(this.options.dom.list).menu({showEvent: "hover"});
	};
	this.getEntry = function(entityType){
		this.options.items = this.options.items || [];
		this.options.items = $.isArray(this.options.items)?this.options.items:[this.options.items];
		var res = $.grep(this.options.items, function(e){
			return e.options.entityType === entityType;
		});
		return (res.length>0?res[0]:null);
	};
	this.orderBy = function(d){
		var ord = appdb.components.LogisticsSelector.orderBy || null;
		if( !ord ) return d;
		ord = ord[this.options.entityType] || [];
		var res = {};
		$.each(ord, function(i,e){
			if(d[e]){
				res[e] = d[e];
				delete d[e];
			}
		});
		for( var i in d ){
			if( !d.hasOwnProperty(i) ) continue;
			res[i] = d[i];
			delete d[i];
		}
		return res;
	};
	this.load = function(flt,exclude){
		exclude = exclude || [];
		exclude = $.isArray(exclude)?exclude:[exclude];
		if( this._model === null ){
			this.publish({event: "load", value: {} });
			return;
		}
		this.abort();
		this._model.Obs.clearAll();
		this._model.subscribe({event: "beforeselect", callback: function(v){
				this.renderLoading(true);
				this.publish({event: "beforeload", value:true});
		}, caller: this});
		this._model.subscribe({event: "select", callback: function(v){
				if( v ){
					v.logistics = v.logistics || {};
					v.logistics = this.orderBy(v.logistics);
					var validated = [].concat(this.parent.aggregateFilter.getFilters({type:"validated"})).concat(this.parent.aggregateFilter.getFilters({type:"validatedbool"}));
					if( validated.length > 0 ){
						if( v.logistics["validated"] ){
							delete v.logistics.validated;
						}
					}else if(v.logistics && v.logistics.validated){ // remove redundant validated info
						v.logistics.validated = $.isArray(v.logistics.validated)?v.logistics.validated:[v.logistics.validated];
						var vlg = {};
						var newvl = [];
						//group by count
						$.each(v.logistics.validated,function(i,e){
							if( e.text === "true" || e.text === "false" ){
								newvl.push(e);
								return;
							}
							if( typeof vlg[e.count] === "undefined" ){
								vlg[e.count] = [];
							}
							vlg[e.count].push(e);
						});
						//sort group items by id and select first
						var fltvl = [];
						for(var i in vlg){
							if( vlg.hasOwnProperty(i) === false ) continue;
							vlg[i].sort(function(a,b){
								return parseInt(a.id) - parseInt(b.id);
							});
							fltvl.push(vlg[i][0]);
						}
						v.logistics.validated = newvl.concat(fltvl.slice(0)).slice(0);
					}
					this.render(v.logistics,exclude);
				}
				this.renderLoading(false);
				//get and publish only displayed values
				var logs = {};
				for(var i in v.logistics){
					if( !v.logistics.hasOwnProperty(i) )continue;
					if( this.getMetaData(i,"display") ){
						logs[i] = v.logistics[i];
					}
				}				
				this.publish({event: "load", value: logs});
		},caller: this});
		if( $.trim(flt) === "" ){
			flt = "&";
		}else {
			if( $.trim(flt)[$.trim(flt).length-1] === "|"){
				flt += " &";
			}else{
				flt += " | &";
			}
		}
		appdb.debug("[DEBUG] Logistics filtering: " + flt);
		var modelopts = {flt:flt};
		if( $.trim(this.options.subtype) !== "" ){
			switch(this.options.entityType.toLowerCase()){
				case "people":
					modelopts.peopletype = $.trim(this.options.subtype);
					break;
				case "software":
				default:
					modelopts.applicationtype = $.trim(this.options.subtype);
					break;
			}
			
		}
		this._model.get(modelopts);
	};
	this.getDefaultMetaData = function(prop){
		var meta = appdb.components.LogisticsSelector.meta;
		if( typeof meta === "undefined" ) return meta;
		meta = meta["*"];
		if( typeof meta === "undefined" ) return meta;
		
		if( prop ){
			meta = meta[prop];
		}
		
		return meta;
		
	};
	this.getMetaData = function(type,prop){
		var meta = appdb.components.LogisticsSelector.meta;
		if( typeof meta === "undefined" ) return this.getDefaultMetaData(prop);
		meta = meta[this.options.entityType];
		if( typeof meta === "undefined" ) return this.getDefaultMetaData(prop);
		
		if( type ){
			meta = meta[type];
		}
		if( typeof meta === "undefined" ) return this.getDefaultMetaData(prop);
		
		if( prop ){
			meta = meta[prop];
		}
		if( typeof meta === "undefined" ) return this.getDefaultMetaData(prop);
		return meta;
	};
	this.initContainer = function(){
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.list);
	};
	this._init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		switch(this.options.entityType.toLowerCase()){
			case "people":
				if( $.trim(this.options.subtype) === "deleted"){
					this._model= null;
				}else{
					this.options.subtype = "";
					this._model= appdb.model.PeopleLogistics();
				}
				break;
			case "vos":
				this._model= appdb.model.VOsLogistics();
				break;
			case "sites":
				this._model = appdb.model.SitesLogistics();
				break;
			case "software":
			default:
				if( this.options.subtype === "related" ){
					this._model = appdb.model.RelatedSoftwareLogistics({id: this.parent.ext.userQuery.appid});
				}else if( $.trim(this.options.subtype) !== "" && userID !== null ){
					this._model = appdb.model.PeopleApplicationLogistics();
				}else{
					this._model = appdb.model.SoftwareLogistics();
				}
				break;
		}
		this.initContainer();
	};
	this._init();
},{
	orderBy: {
		"software": ["phonebook","validated","discipline", "category", "status", "license", "middleware", "language", "vo", "country"],
		"vappliance": ["phonebook","hypervisor","osfamily","arch","middleware","vo", "validated","discipline", "category", "status", "language",  "country"],
		"people": ["phonebook","role","group","country","discipline","language"],
		"vos" :["phonebook","middleware","discipline","storetype"],
		"sites": ["phonebook","country","osfamily","hypervisor","middleware","supports","hasinstances","vo","discipline","category"]
	},
	maxDisplayItems: {
		"vappliance": 6,
		"sites": 8,
		"default": 5
	},
	meta: {
		"software": {
			"phonebook": {
				displayName: "a-z",
				orderBy: {"text": "asc"},
				minCount: 0,
				maxCount: 7,
				overflow: "split"
			},
			"validated": {
				displayName: "freshness",
				minCount: 1,
				orderBy: {"id": "asc"},
				"dataProvider": function(data){
					var cdv = new appdb.views.ValidatedDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				},
				"getValueId": function(data){
					if( $.inArray($.trim(data.text).toLowerCase(),["updated","outdated"]) > -1 ){
						return data.id;
					}
					return data.text;
				}
			},
			"license":{
				displayName: "license",
				"dataProvider": function(data){
					var cdv = new appdb.views.LicensesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"country":{
				orderBy: {"text":"asc","count":"desc"},
				overflow: "split",
				maxCount: 5,
				maxColumns: 3,
				maxLength: 15,
				css: "leftalign"
			},
			"vo":{
				displayName: "virtual organization",
				maxCount: 10,
				maxColumns: 3,
				overflow: "split",
				maxLength: 23,
				css: "leftalign"
			},
			"language": {
				displayName: "language",
				maxCount: 20,
				maxColumns: 3,
				overflow: "split",
				maxLength: 23
			},
			"os": {
				display: false
			},
			"arch": {
				display: false
			},
			"subdiscipline": {
				display: false
			},
			"discipline": {
				"dataProvider": function(data){
					var cdv = new appdb.views.DisciplinesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"category": {
				"dataProvider": function(data){
					var cdv = new appdb.views.CategoriesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"more": {
				"displayName": "more...",
				"title": "View more filters",
				"displayType": "hide" //hide: Hide the rest of menus with a link(expand menus on click). menu:Add the rest menus into one menu.
			}
		},
		"vappliance": {
			"phonebook": {
				displayName: "a-z",
				orderBy: {"text": "asc"},
				minCount: 0,
				maxCount: 7,
				overflow: "split"
			},
			"validated": {
				displayName: "freshness",
				minCount: 1,
				orderBy: {"id": "asc"},
				"dataProvider": function(data){
					var cdv = new appdb.views.ValidatedDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				},
				"getValueId": function(data){
					if( $.inArray($.trim(data.text).toLowerCase(),["updated","outdated"]) > -1 ){
						return data.id;
					}
					return data.text;
				}
			},
			"license":{
				displayName: "license",
				"dataProvider": function(data){
					var cdv = new appdb.views.LicensesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"country":{
				orderBy: {"text":"asc","count":"desc"},
				overflow: "split",
				maxCount: 5,
				maxColumns: 3,
				maxLength: 15,
				css: "leftalign"
			},
			"vo":{
				displayName: "virtual organization",
				maxCount: 10,
				maxColumns: 3,
				overflow: "split",
				maxLength: 23,
				css: "leftalign"
			},
			"language": {
				displayName: "language",
				maxCount: 20,
				maxColumns: 3,
				overflow: "split",
				maxLength: 23
			},
			"os": {
				display: false
			},
			"arch": {
				display: true
			},
			"subdiscipline": {
				display: false
			},
			"discipline": {
				"dataProvider": function(data){
					var cdv = new appdb.views.DisciplinesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"category": {
				"dataProvider": function(data){
					var cdv = new appdb.views.CategoriesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"status":{
				display: false
			},
			"more": {
				"displayName": "more...",
				"title": "View more filters",
				"displayType": "hide" //hide: Hide the rest of menus with a link(expand menus on click). menu:Add the rest menus into one menu.
			}
		},
		"people":{
			"phonebook": {
				displayName: "a-z",
				orderBy: {"text": "asc"},
				minCount: 0,
				maxCount: 7,
				overflow: "split"
			},
			"country":{
				orderBy: {"text":"asc","count":"desc"},
				overflow: "split",
				maxCount: 5,
				maxColumns: 3,
				maxLength: 15,
				css: "rightalign"
			},
			"discipline": {
				"dataProvider": function(data){
					var cdv = new appdb.views.DisciplinesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"role":{
				displayName: "scientific orientation"
			}
		},
		"vos":{
			"phonebook": {
				displayName: "a-z",
				orderBy: {"text": "asc"},
				minCount: 0,
				maxCount: 7,
				overflow: "split"
			},
			"storetype": {
				displayName: "supported by",
				transformData: function(d){
					d = d || [];
					d = $.isArray(d)?d:[d];
					var data = [];
					$.each(d, function(i,e){
						data.push($.extend({},e));
					});
					var cnt = appdb.pages.index.currentContent();
					switch(cnt){
						case "vappliance":
							data = $.grep(data, function(e){
								return $.trim(e.id) === "2";
							});
							break;
						case "software":
							data = $.grep(data, function(e){
								return $.trim(e.id) === "1";
							});
							break;
					}
					return data;
				},
				maxDisplayItems: 1,
				minCount: 1
			},
			"discipline": {
				"dataProvider": function(data){
					var cdv = new appdb.views.DisciplinesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"scope":{
				display: false
			}
		},
		"sites":{
			"phonebook": {
				displayName: "a-z",
				orderBy: {"text": "asc"},
				minCount: 0,
				maxCount: 7,
				overflow: "split"
			},
			"country":{
				orderBy: {"text":"asc","count":"desc"},
				overflow: "split",
				maxCount: 5,
				maxColumns: 3,
				maxLength: 15,
				css: "leftalign"
			},
			"vo":{
				displayName: "virtual organization",
				maxCount: 10,
				maxColumns: 3,
				overflow: "split",
				maxLength: 23,
				css: "leftalign"
			},
			"os": {
				display: false
			},
			"arch": {
				display: false
			},
			"discipline": {
				"dataProvider": function(data){
					var cdv = new appdb.views.DisciplinesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"category": {
				"dataProvider": function(data){
					var cdv = new appdb.views.CategoriesDataView();
					cdv.load();
					return appdb.utils.MergeTreeLogistics(cdv, data);
				}
			},
			"supports": {
				displayName: "supports",
				transformData: function(d){
					d = d || [];
					d = $.isArray(d)?d:[d];
					var data = [];
					$.each(d, function(i,e){
						data.push($.extend({},e));
					});
					var cnt = appdb.pages.index.currentContent();
					switch(cnt){
						case "vappliance":
							data = $.grep(data, function(e){
								return $.trim(e.id) === "1";
							});
							break;
						case "software":
							data = $.grep(data, function(e){
								return $.trim(e.id) === "0";
							});
						break;
					}
					return data;
				},
				maxDisplayItems: 1,
				minCount: 1
			},
			"hasinstances": {
				displayName: "offers",
				transformData: function(d){
					d = d || [];
					d = $.isArray(d)?d:[d];
					var data = [];
					$.each(d, function(i,e){
						data.push($.extend({},e));
					});
					var cnt = appdb.pages.index.currentContent();
					switch(cnt){
						case "vappliance":
							data = $.grep(data, function(e){
								return $.trim(e.id) === "1";
							});
							break;
					}
					return data;
				},
				maxDisplayItems: 1,
				minCount: 1
			}
		},
		"*": {
			display: true,
			orderBy: {"count":"desc","text":"asc"},
			minCount: 1,
			maxCount: 20,
			overflow: "menu"
		}
	}
});
appdb.components.People = appdb.ExtendClass(appdb.Component,"appdb.components.People",function(o){
    o = o || {};
    this.ext = o.ext || {};
	this.isLoaded = true;
    this._baseQuery = this.ext.baseQuery || null;
    this._getFullQuery = function(v){
        var res;
        if(typeof v === "string"){
            if( this._baseQuery){
                res = '' + v;
                res = v + " " + this._baseQuery.flt;
                return res;
            }else{
                return v;
            }
        }
        return {};
    };
    this._getUserQuery = function(v){
        var res,bqi,bq,f;
        if(v && this._baseQuery && this._baseQuery.flt){
            res = $.extend({},v);
            f = res.flt;
            bq = ''+this._baseQuery.flt;
            bqi = f.indexOf(bq);
            if(bqi>=0){
                f = f.substring(0,bqi);
                if(f[f.length-1]===" "){
                    f = f.substring(0,f.length-1);
                }
            }
            res.flt = f;
            return res;
        }
        return v;
    };
	this.getCurrentData = function(){
		if( !this._model || !this._model.getLocalData() ) return [];
		var m = this._model.getLocalData().person || [];
		m = m || [];
		m = $.isArray(m)?m:[m];
		return m;
	};
    this.render = function(d,p,t){
        var start = (new Date()).getTime(), v = this.views, _dom = this.dom, qvalue=this._model.getQuery();
        var len = ((d)?(typeof d.length==="number")?d.length:len:0);
        var ofs = (p)?(typeof p.pageoffset==="number")?p.pageoffset:ofs:0;
        $(_dom).empty();
        v.peopleList.render(d);
        if(typeof len === "undefined"){
            if(ofs===0){
                v.pagerview.reset();
            }else{
                v.pagerview.render(p);
            }
            v.ordering.reset();
            v.viewbuttons.reset();
        }else if(len===0){
            v.pagerview.reset();
            v.ordering.reset();
            v.viewbuttons.reset();
        }else{
            v.pagerview.render(p);
            v.ordering.render(qvalue);
            v.viewbuttons.render();
        }
        v.filtering.setWatermark(this.ext.filterDisplay);
        v.filtering.setValue(this.aggregateFilter.getUserFilter());
        v.filtering.render();
        v.atom.render(qvalue.flt);
        v._export.setValue(qvalue.flt);
        v._export.render();
        v.resulttimer.render(p.count,t);
		v.permalink.render({query:this.ext.baseQuery || qvalue,ext:this.ext});
		var end = (new Date()).getTime();
        if ( $.trim(appdb.config.appenv) !== 'production' ) {
            v.resulttimer.appendRender(end-start);
        }
		if( $("#ppl_orderby > div").length > 0 ) {$("#ppl_orderby > div")[0].style.overflow = "hidden";}
		$("#ppl_logisticsselector").hide();
		window.scroll(0,0);
		$("#mainbody").removeClass("terminal");
    };
    this.load = function(o){
        this.clearEvents();
        var v = this.views;
		var currentSubtype = "";
		this.isLoaded = true;
        this.ext = o.ext || {};
		if(this.ext.mainTitle){
            this.setComponentTitle(this.ext.mainTitle);
        }
        var _base ;
		this.aggregateFilter.loadFilter( {ext: this.ext} );
        v.filtering.setWatermark(this.ext.filterDisplay);
        if(this.ext.baseQuery && this.ext.baseQuery.flt){
            _base = $.extend({},this.ext.baseQuery);
            this._baseQuery = $.extend({},_base);
            if(o.query && o.query.flt){
				var po = o.query.pageoffset || 0;
				var pl = o.query.pagelength || optQueryLen;
                o.query =  $.extend({},_base);
				o.query.pagelength = o.query.pagelength || pl;
				o.query.pageoffset = o.query.pageoffset || po;
                if(this.ext.userQuery){
                    o.query.flt = this.aggregateFilter.getFullQuery();
                }
            }
        }else{
            this._baseQuery = null;
            this.ext.userQuery = o.query;
        }
		var query = o.query || {};
		if(this.views.ordering && this.views.ordering.getSelected()!==null){
		 if(typeof query.orderby === "undefined" && typeof query.orderbyOp === "undefined"){
		  this.views.ordering.resetSelected();
		 }
		 this.views.ordering.setSelected(query.orderby,query.orderbyOp);
		}
		if( $.trim(query.peopletype) === "" ){
			delete query.peopletype;
		}else{
			if($.trim(query.peopletype) === "deleted"){
				currentSubtype = "deleted";
			}
		}
        this._model = new appdb.model.People(query);
		var logisticsoptions = {
					container: $("#ppl_logisticsselector"),
					parent: this,
					entityType: "people",
					subtype: currentSubtype
				};
		query.flt = query.flt || this.aggregateFilter.getFullQuery();
        if( v.logistics ){
			v.logistics.abort();
			v.logistics._mediator.clearAll();
			v.logistics.destroy();
			v.logistics = null;
		}
		appdb.debug("[DEBUG] People Fetch: " + query.flt);
		v.logistics = new appdb.components.LogisticsSelector(logisticsoptions);
        this._model.subscribe({event:"beforeselect",callback:function(){
                this.views.loading.show();
				this.views.managedFilter.setLoading(true);
            },caller:this});
        this._model.subscribe({event:"error",callback:function(err){
                this.views.loading.hide();
                this.ErrorHandler.handle({status:"Data transfer error",description:"An error occured during software list data transfer",source : err});
            },caller:this});
        v.pager = this._model.getPager().subscribe({event:'pageload',callback:function(d){
			this.render(d.data,d.pager,d.elapsed);
			this.views.loading.hide();
            $(this._dom).show();
			var q = this._model.getQuery();
			if( $.trim(q.flt) ){
				q.flt = this.aggregateFilter.getFullQuery();
			}
			var excludelogistics = this.aggregateFilter.getFilters({source:"system"});
			excludelogistics = excludelogistics.concat(this.aggregateFilter.getFilters({source:"implicit"}));//parsed from base query
			excludelogistics = excludelogistics.concat(this.aggregateFilter.getFilters({source:"user"}));//parsed from user query
			this.views.managedFilter.render();
			this.views.logistics.load(this.aggregateFilter.getFullQuery(),excludelogistics);
			var title = this.views.managedFilter.getPageTitle();
			if(this.isLoaded == false){
			 appdb.Navigator.internalCall(appdb.Navigator.Registry["People"],{query:this._model.getQuery(),ext:this.ext});
			}else{
			 appdb.Navigator.setTitle(appdb.Navigator.Registry["People"],[this._model.getQuery(),this.ext]);
			}
		   this.isLoaded = false;
           this.publish({event:'loaded',value:{}});
		   var uq = this.aggregateFilter.getUserFilter();
			if( uq ){
				this.views.filtering.setValue({flt: uq.value});
			}
			appdb.debug("[DEBUG] People Paging Load: " + q.flt);
        },caller : this});
		
        v.pagerview.subscribe({event:"next",callback : function(){
            this.views.pager.next();
        }, caller : this});
        v.pagerview.subscribe({event:"previous",callback : function(){
            this.views.pager.previous();
        }, caller : this});
        v.pagerview.subscribe({event:"current",callback : function(v){
            this.views.pager.current(v);
        }, caller : this});
        v.ordering.subscribe({event:"order",callback:function(v){
            this._model.get(v);
        },caller : this});
		v.managedFilter.subscribe({event: "changed", callback: function(v){
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
			this.ext.systemQuery = this.aggregateFilter.getSystemQueriesValues();
			var bqflt = this.aggregateFilter.getBaseQueryValue();
			if( $.trim(bqflt) === "" ){
				if( this.ext.baseQuery ){
					this.ext.baseQuery.flt = "";
				}
			} else {
				if( this.ext.baseQuery ){
					this.ext.baseQuery.flt = bqflt;
				}else{
					this.ext.baseQuery = {flt: bqflt};
				}
			}
			v.flt = this.aggregateFilter.getFullQuery();// this._getFullQuery(v.flt);
            this._forceFiltering = false;
            if(this._apptype===null){
				this.views._export.setValue(v.flt);
                this.views._export.render();
			}
			this._model.get({flt: v.flt, pageoffset:0});
		}, caller: this});
		v.managedFilter.subscribe({event:"action", callback: function(v){
			if( v === "display" ){
				if( $("#ppl_logisticsselector").is(":visible") === false ){
					$(this.views.managedFilter.dom).children("a.action.display:first").addClass("expanded");
					$("#ppl_logisticsselector").slideDown("fast");
					$("#ppl_logisticsselector").removeClass("hidden");
				} else {
					$(this.views.managedFilter.dom).children("a.action.display:first").removeClass("expanded");
					$("#ppl_logisticsselector").slideUp("fast");
				}
			}else if ( v === "reset" ){
				this.aggregateFilter.removeFilters({source: "system"});
				this.aggregateFilter.removeFilters({source: "user"});
				delete this.ext.systemQuery;
				this._model.get({flt: this.aggregateFilter.getFullQuery(), pageoffset: 0});
			}
			appdb.debug("[DEBUG] People Filtering Changed: " + v.flt);
		}, caller: this});
        v.filtering.subscribe({event:"filter",callback:function(v){
			this.aggregateFilter.loadUserFilter(v.flt);
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
            v.flt = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
			
            this.views._export.setValue(v.flt);
            this.views._export.render();
			appdb.debug("[DEBUG] People Filter: " + v.flt);
			this._model.get(v);
        },caller: this});
        v.peopleList.subscribe({event:"itemclick",callback:function(data){
            var p ,s = this.views.pager.getCurrentPagingState();
            if(s.pagenumber>0){
                p = {pagelength:s.length,pageoffset: s.offset};
            }
            appdb.views.Main.showPerson({id:data.id, cname: data.cname},{mainTitle : data.firstname + " " + data.lastname,append:true,previousPager:p});
        },caller:this});
		v.pager.current(); 
		v.logistics.subscribe({event: "addfilter", callback: function(v){
			this.aggregateFilter.appendSystemFilter(v);
			this.ext.systemQuery = this.ext.systemQuery || [];
			this.ext.systemQuery = $.isArray(this.ext.systemQuery)?this.ext.systemQuery:[this.ext.systemQuery];
			var qv = this.aggregateFilter.getManagedFilterValue( v );
			if( qv ){
				this.ext.systemQuery.push( appdb.utils.UniqueOrderedArray(qv) );				
			}
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
            var fq = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
            if(this._apptype===null){
				this.views._export.setValue(fq);
                this.views._export.render();
			}
			this._model.get({flt: fq, pageoffset:0});
		},caller: this});
		v.logistics.subscribe({event:"beforeload", callback: function(v){
				this.views.managedFilter.setLoading(true);
		},caller: this});
		v.logistics.subscribe({event: "load", callback: function(v){
				v = v || {};
				var display = false;
				for( var i in v){
					if( !v.hasOwnProperty(i) ) continue;
					v[i] = v[i] || [];
					v[i] = $.isArray(v[i])?v[i]:[v[i]];
					if(v[i].length > 1 ){
						display = true;
						break;
					}
				}
				var l = this.getCurrentData();
				var ul = $.grep(this.aggregateFilter.getFilters({source:"user"}), function(e){
					return (e.type !== "user" );
				});
				var pc = this.views.pager.pageCount();
				this.views.managedFilter.setLoading(false);
				this.views.managedFilter.showAction((l.length >= 2 || ul.length > 0 || pc > 1) && display && pc > 1 );
				this.setManagedFiletringDisplay((l.length >= 2 || ul.length > 0 || pc > 1) && display);

		}, caller: this});
		
    };
	this.setManagedFiletringDisplay = function(display){
		display = ( ($.type(display) === "boolean")?display:false );
		if( display ){
			$(".refinedfiltering").removeClass("hidden");
			$(this.views.managedFilter.dom).children(".action.display").show();
			$("#ppl_logisticsselector").removeClass("hidden");
		}else {
			var flts = this.aggregateFilter.getFilters({source: "system"});
			$(this.views.managedFilter.dom).children(".action.display").hide();
			$("#ppl_logisticsselector").addClass("hidden");
			if( flts.length > 0 ){
				$(".refinedfiltering").removeClass("hidden");
			}else{
				$(".refinedfiltering").addClass("hidden");
			}
		}
	};
    this._init = function(){
        var v = {};
        if(this.ext.mainTitle){
            this.setComponentTitle(this.ext.mainTitle);
        }
        v.peopleList = new appdb.views.PeopleList({container : "ul#pplmainlist"});
        v.pagerview = new appdb.views.MultiPagerPane({container : $("#ppl_main_content").find(".pager")});
        v.filtering = new appdb.views.Filter({
            container : "ppl_filter",
            rich : true,
            watermark : this.ext.filterDisplay
        });
        if(this.ext.userQuery){
            v.filtering.setValue(this.ext.userQuery);
        }
        v.ordering = new appdb.views.Ordering({
            container : "ppl_orderby",
            selected : "rank",
            items : [
                {name : "Relevance" , value : "rank", defaultOperation: "DESC", hideOperation: true},
                {name : "First Name" , value : "firstname", defaultOperation: "ASC"},
                {name : "Last Name" , value : "lastname", defaultOperation: "ASC"},
                {name : "ID" , value : "id", defaultOperation: "ASC"},
                {name : "Date" , value : "dateinclusion", defaultOperation: "DESC"},
                {name : "Unsorted" , value : "unsorted", defaultOperation: "ASC", hideOperation: true}
            ]
        });
        v.resulttimer = new appdb.views.ResultTimer({container : $("#ppl_result_timer")});
        v.loading = new appdb.views.DelayedDisplay($("#ppl_loading"));
        v.viewbuttons = new appdb.views.ListViewMode({container : $("#ppl_viewbuttons"),list : "ul#pplmainlist"});
        v._export = new appdb.views.Export({container : $("#pplexportdiv"), target:'people'});
        v.permalink = new appdb.views.Permalink({container : $("#ppl_permalink"), datatype:"people"});
        v.atom = new appdb.views.NewsFeed({container : $("#ppl_atomfeed"),type:"ppl"});
		this.aggregateFilter = new appdb.utils.FilterAggregator({
			parent: this,
			entityType: "people"
		});
		v.managedFilter = new appdb.views.ManagedFiltering({
			container: $("#ppl_managedfilters"),
			parent: this,
			filter: this.aggregateFilter,
			maxLength: 30
		});
		this.views = v;
        this.setViewsParent(this);
    };
    this._init();
});

appdb.components.Applications = appdb.ExtendClass(appdb.Component,"appdb.components.Applications",function(o){
    o = o || {};
    this._apptype = null;
	this.isLoaded = false;
    this.ext = o.ext || {};
    this._baseQuery = this.ext.baseQuery || null;
	this._hideInvalidApps = false;
	this._validApplicationsSwitch = null;
    this._getFullQuery = function(v){console.log("[DEPRICATED]: Use of appdb.components.Applications._getFullQuery");
        var res;
		this._baseQuery = this._baseQuery || this.ext.baseQuery;
        if(typeof v === "string"){
            if( this._baseQuery){
                res = '' + v;
                res = v + " " + this._baseQuery.flt;
                return res;
            }else{
                return v;
            }
        }
        return {};
    };
    this._getUserQuery = function(v){console.log("[DEPRICATED]: Use of appdb.components.Applications._getUserQuery");
        var res,bqi,bq,f;
        if(v && this._baseQuery && this._baseQuery.flt){
            res = $.extend({},v);
            f = res.flt;
            bq = ''+this._baseQuery.flt;
            bqi = f.indexOf(bq);
            if(bqi>=0){
                f = f.substring(0,bqi);
                if(f[f.length-1]===" "){
                    f = f.substring(0,f.length-1);
                }
            }
            res.flt = f;
            return res;
        }
        return v;
    };
    this._componentTitle = "Applications";
	this.getHideInvalidatedApplications = function(){
		var f = ( ( !this.ext.baseQuery )?"":( this.ext.baseQuery.flt || "" ) );
		this._hideInvalidApps = ($.trim(f).indexOf("application.validated:true")>-1)?true:false;
		return this._hideInvalidApps;
	};
	this.setValidApplicationSwitch = function(hide){
		if(appdb.config.views.applications.canHideInvalidated){
			this._hideInvalidApps = hide;
			this.ext.baseQuery = this._baseQuery || this.ext.baseQuery || {};
			this.ext.baseQuery.flt = this.ext.baseQuery.flt || "";
			var v = this.ext.baseQuery.flt;
			v = v || "";
			if(this._hideInvalidApps && $.trim(v).indexOf("+application.validated:true")===-1){
				v += (($.trim(v)==="")?"":" ") + "+application.validated:true";
			}else if(this._hideInvalidApps==false && $.trim(v).indexOf("+application.validated:true")!==-1){
				v = v.replace(/\+application\.validated\:true/g,"");
			}
			this.ext.baseQuery.flt = v;
		}
	};
	this.setupPagers = function(){
		this.views.pagerview.show();
		this.views.pager.current();
	};
	this.selectedPager = (function(_parent){
		var _v = "numeric";
		return function(v){
			if(typeof v === "string"){
				_v = $.trim(vfILTER.toLowerCase());
				return _parent;
			}
			return _v;
		};
	})(this);
	this.renderPager = function(d){
		this.views.pagerview.render(d);
	};
	this.resetPager = function(){
		this.views.pagerview.reset();
	};
	this.setupValidApplicationSwitch = function(){
		if(appdb.config.views.applications.canHideInvalidated){
			//dispose checkbox and html
			if(this._validApplicationsSwitch!==null){
				this._validApplicationsSwitch.destroy();
			}
			$("#validAppsSwitch").empty().remove();
			//Construct Html and checkbox
			if($("#validAppsSwitch").length===0){
				$("#apps_filter").after("<div id='validAppsSwitch'><span class='validappscheckbox'></span><span class='validappsswitch'>Hide not recently updated items</span></div>");
			}
			this._validApplicationsSwitch = new dijit.form.CheckBox({
				value: "Hide invalidated software",
				checked: this.getHideInvalidatedApplications(),
				onChange: (function(_this){
					return function(b){
						_this._hideInvalidApps = true && b;
						_this.setValidApplicationSwitch(_this._hideInvalidApps);
						this._forceFiltering = true;
					};
				})(this)
			},$("#validAppsSwitch > .validappscheckbox")[0]);
		}
	};
	this.setManagedFiletringDisplay = function(display){
		display = ( ($.type(display) === "boolean")?display:false );
		if( display ){
			$(".refinedfiltering").removeClass("hidden");
			$(this.views.managedFilter.dom).children(".action.display").show();
			$("#apps_logisticsselector").removeClass("hidden");
		}else {
			var flts = this.aggregateFilter.getFilters({source: "system"});
			$(this.views.managedFilter.dom).children(".action.display").hide();
			$("#apps_logisticsselector").addClass("hidden");
			if( flts.length > 0 ){
				$(".refinedfiltering").removeClass("hidden");
			}else{
				$(".refinedfiltering").addClass("hidden");
			}
		}
	};
	this.getCurrentData = function(){
		if( !this._model || !this._model.getLocalData() ) return [];
		var m = this._model.getLocalData().application || this._model.getLocalData().relatedapp || [];
		m = m || [];
		m = $.isArray(m)?m:[m];
		return m;
	};
    this.render = function(d,p,t){
        var start = (new Date()).getTime(), v = this.views, qvalue = this._model.getQuery();
        var len = (d)?(typeof d.length==="number")?d.length:len:0;
        var ofs = (p)?(typeof p.pageoffset==="number")?p.pageoffset:ofs:0;
        $(this._dom).empty();
        v.appsList.render(d,{onRenderItem: this.onRenderItem});
        if(typeof len === "undefined"){
            if(ofs===0){
				v.pagerview.reset();
            }else{
                v.pagerview.render(p);
            }
            v.ordering.reset();
            v.viewbuttons.render();
        } else if(len===0){
			v.pagerview.reset();
            v.ordering.reset();
            v.viewbuttons.reset();
        }else{
            v.pagerview.render(p);
			v.ordering.render(qvalue);
            v.viewbuttons.render();
        }
		
        v.filtering.setWatermark(this.ext.filterDisplay);
        v.filtering.setValue(this.aggregateFilter.getUserFilter());
        v.filtering.render();
        if(this._apptype===null){
            v._export.setValue(qvalue.flt);
            v._export.render();
            v.atom.render({flt : (qvalue.flt || ""), title : this.getComponentTitle() + " news feed"});
            $(".listtoolbox").show();
        }else{
            v._export.destroy();
            v.atom.destroy();
			v.mail.destroy();
            $(".listtoolbox").hide();
        }
        v.resulttimer.render(p.count,t);
        v.permalink.render({query:this.ext.baseQuery || qvalue,ext:this.ext});
        var end = (new Date()).getTime();
        if ( appdb.config.appenv !== 'production' )   v.resulttimer.appendRender(end-start);
		this.setupValidApplicationSwitch();
		if( $("#apps_orderby > div").length > 0 ) {
			$("#apps_orderby > div")[0].style.overflow= 'hidden';
		}
		$("#apps_logisticsselector").hide();
		window.scroll(0,0);
		$("#mainbody").removeClass("terminal");
    };
    this.load = function(d,internalCall){
		appdb.pages.index.cancelLoggedInRequest();
		appdb.pages.application.reset(true);
		this.clearEvents();
		this.onRenderItem = null;
		this.currentData = d;
		d = d || {};
		this.isLoaded = true;
		this.navigationType = "";
        this.ext = d.ext || {};
        var v = this.views;
        var _base ;
		this.aggregateFilter.loadFilter( {ext: this.ext} );
        if(this.ext.mainTitle){
            this.setComponentTitle(this.ext.mainTitle);
        }
		if(this.ext.baseQuery && this.ext.baseQuery.flt){
			_base = $.extend({},this.ext.baseQuery);
			
            this._baseQuery = $.extend({},_base);
			//Normalize query object
            if(d.query && d.query.flt){
				var po = d.query.pageoffset || 0;
				var pl = d.query.pagelength || optQueryLen;
                d.query =  $.extend({},_base);
				d.query.pagelength = d.query.pagelength || pl;
				d.query.pageoffset = d.query.pageoffset || po;
                if(this.ext.userQuery || this.ext.systemQuery){
                    d.query.flt = this.aggregateFilter.getFullQuery();
                }
            }
        }else if( !this.ext.systemQuery ){
			this._baseQuery = null;
			this.ext.userQuery = d.query;
        }
        this._apptype = "o.query.applicationtype";
        var query = d.query || {};
		query.orderby = query.orderby || 'rank';
		query.orderbyOp = query.orderbyOp || ( (query.orderby==='rank')?'DESC':'ASC' );
		if(this.views.ordering && this.views.ordering.getSelected()!==null){
		 if(typeof query.orderby === "undefined" && typeof query.orderbyOp === "undefined"){
			this.views.ordering.resetSelected();
		 }
		 this.views.ordering.setSelected(query.orderby,query.orderbyOp);
		}
		var logosticsoptions = {
					container: $("#apps_logisticsselector"),
					parent: this,
					entityType: $.trim(this.ext.content).toLowerCase() || "software"
				};
		query.flt = query.flt || this.aggregateFilter.getFullQuery();
        switch(query.applicationtype){
			case "related":
				this._model = new appdb.model.RelatedApplications(query,this.ext);
				v.filtering.setWatermark(d.query.applicationtype);
				this.navigationType = "RelatedApps";
				logosticsoptions.subtype = query.applicationtype;
				break;
			case "moderated":
				this.navigationType = ($.trim(this.navigationType)==="")?"Moderated":this.navigationType;
			case "deleted":
                this._model = new appdb.model.Applications(query);
                v.filtering.setWatermark(d.query.applicationtype);
				this.navigationType = ($.trim(this.navigationType)==="")?"Deleted":this.navigationType;
			    break;
			case "followed":
				logosticsoptions.subtype = query.applicationtype;
				query.id = query.id || userID;
				query.flt = query.flt || "";
				this.navigationType = "Followed";
				this.onRenderItem = appdb.components.Applications.onRenderFollowedItem;
            case "bookmarked":
				this.navigationType = ($.trim(this.navigationType)==="")?"Bookmarked":this.navigationType;
				logosticsoptions.subtype = query.applicationtype;
            case "editable":
				this.navigationType = ($.trim(this.navigationType)==="")?"Editable":this.navigationType;
				logosticsoptions.subtype = query.applicationtype;
            case "associated":
				this.navigationType = ($.trim(this.navigationType)==="")?"Associated":this.navigationType;
				logosticsoptions.subtype = query.applicationtype;
            case "owned":
                this._model = new appdb.model.PeopleApplications(query,this.ext);
				logosticsoptions.subtype = query.applicationtype;
                v.filtering.setWatermark(d.query.applicationtype);
				this.navigationType = ($.trim(this.navigationType)==="")?"Owned":this.navigationType;
                break;
            default:
                this._model = new appdb.model.Applications(query);
                v.filtering.setWatermark(this.ext.filterDisplay);
                this._apptype = null;
				this.navigationType = this.ext.navigationType || "Applications";
				delete d.query.applicationtype;
                break;
        }
		if( v.logistics ){
			if( v.logistics._model && v.logistics._model.getXhr){
				var x = v.logistics._model.getXhr();
				if( x && typeof x.abort === "function") {
					x.abort();
				}
			}
			v.logistics._mediator.clearAll();
			v.logistics.destroy();
			v.logistics = null;
		}
		v.logistics = new appdb.components.LogisticsSelector(logosticsoptions);
		appdb.debug("[DEBUG] Applications Fetch: " + query.flt);
        this._model.subscribe({event:"beforeselect",callback:function(){
                this.views.loading.show();
				this.views.logistics.abort();
				this.views.managedFilter.setLoading(true);
				appdb.pages.application.reset(true);
            },caller:this});
        this._model.subscribe({event:"error",callback:function(err){
                this.views.loading.hide();
                this.ErrorHandler.handle({status:"Data transfer error",description:"An error occured during software list data transfer",source : err});
            },caller:this});
        v.pager = this._model.getPager().subscribe({event:'pageload',callback:function(d){
			appdb.pages.application.reset(true);
			this.render(d.data,d.pager,d.elapsed);
            this.views.loading.hide();
			$(this._dom).show();
            if(typeof query.applicationtype === "undefined"){
				this.views.mail.load({flt: (query.flt || ""), title : this.getComponentTitle() + " news"});
			}
			var q = this._model.getQuery();
			if( $.trim(q.flt) ){
				q.flt = this.aggregateFilter.getFullQuery();
			}
			var excludelogistics = this.aggregateFilter.getFilters({source:"system"});
			excludelogistics = excludelogistics.concat(this.aggregateFilter.getFilters({source:"implicit"}));//parsed from base query
			excludelogistics = excludelogistics.concat(this.aggregateFilter.getFilters({source:"user"}));//parsed from user query
			this.views.managedFilter.render();
			this.views.logistics.load(this.aggregateFilter.getFullQuery(),excludelogistics);
			var title = this.views.managedFilter.getPageTitle() || $.trim(this.ext.mainTitle);
			if(this.isLoaded == false){
				appdb.Navigator.internalCall(title || appdb.Navigator.Registry[this.navigationType],{query:q,ext:this.ext});
			} else {
				appdb.Navigator.setTitle(title || appdb.Navigator.Registry[this.navigationType],[q,this.ext]);
			}
			this.isLoaded = false;
            this.publish({event:'loaded',value:d});
			var uq = this.aggregateFilter.getUserFilter();
			if( uq ){
				this.views.filtering.setValue({flt: uq.value});
			}
			appdb.debug("[DEBUG] Application Paging Load: " + query.flt);
        },caller : this});
		v.pagerview.subscribe({event:"next",callback : function(){
             this.views.pager.next();
        }, caller : this});
        v.pagerview.subscribe({event:"previous",callback : function(){
             this.views.pager.previous();
        }, caller : this});
        v.pagerview.subscribe({event:"current",callback : function(v){
			appdb.debug("[DEBUG] Applications Paging Changed: " + v.flt);
            this.views.pager.current(v);
        }, caller : this});
        v.ordering.subscribe({event:"order",callback:function(v){
            this._model.get(v);
        },caller : this});
		v.managedFilter.subscribe({event: "changed", callback: function(v){
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
			this.ext.systemQuery = this.aggregateFilter.getSystemQueriesValues();
			var bqflt = this.aggregateFilter.getBaseQueryValue();
			if( $.trim(bqflt) === "" ){
				if( this.ext.baseQuery ){
					this.ext.baseQuery.flt = "";
				}
			} else {
				if( this.ext.baseQuery ){
					this.ext.baseQuery.flt = bqflt;
				}else{
					this.ext.baseQuery = {flt: bqflt};
				}
			}
			v.flt = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
            if(this._apptype===null){
				this.views._export.setValue(v.flt);
                this.views._export.render();
			}
			appdb.debug("[DEBUG] Applications Filtering Changed: " + v.flt);
			this._model.get({flt: v.flt, pageoffset:0});
		}, caller: this});
		v.managedFilter.subscribe({event:"action", callback: function(v){
			if( $.trim(v) === "display" ){
				if( $("#apps_logisticsselector").is(":visible") === false ){
					$(this.views.managedFilter.dom).children("a.action.display:first").addClass("expanded");
					$("#apps_logisticsselector").slideDown("fast");
					$("#apps_logisticsselector").removeClass("hidden");
				} else {
					$(this.views.managedFilter.dom).children("a.action.display:first").removeClass("expanded");
					$("#apps_logisticsselector").slideUp("fast");
				}
			}else if ( $.trim(v) === "reset" ){
				this.aggregateFilter.removeFilters({source: "system"});
				this.aggregateFilter.removeFilters({source: "user"});
				delete this.ext.systemQuery;
				this._model.get({flt: this.aggregateFilter.getFullQuery(), pageoffset: 0});
			}
		}, caller: this});
        v.filtering.subscribe({event:"filter",callback:function(v){
			this.aggregateFilter.loadUserFilter(v.flt);
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
            v.flt = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
			
            if(this._apptype===null){
				this.views._export.setValue(v.flt);
                this.views._export.render();
			}
			appdb.debug("[DEBUG] Applications Filter: " + v.flt);
			this._model.get(v);
        },caller : this});
		v.appsList.subscribe({event:"itemclick",callback: function(data){
            var p ,s = this.views.pager.getCurrentPagingState();
            if(s.pagenumber>0){
                p = {previousPager: {pagelength:s.length,pageoffset: s.offset}};
            }
			switch( $.trim(data.metatype).toLowerCase() ){
				case "2":
					data.contentType = "softwareappliance";
					appdb.views.Main.showSoftwareAppliance(data,p);
					break;
				case "1":
					data.contentType = "virtualappliance";
					appdb.views.Main.showVirtualAppliance(data,p);
					break;
				case "0":
				default:
					data.contentType = "software";
					appdb.views.Main.showApplication(data,p);
					break;
			}
        },caller:this});
		this.setupPagers();
		v.logistics.subscribe({event: "addfilter", callback: function(v){
			this.aggregateFilter.appendSystemFilter(v);
			this.ext.systemQuery = this.ext.systemQuery || [];
			this.ext.systemQuery = $.isArray(this.ext.systemQuery)?this.ext.systemQuery:[this.ext.systemQuery];
			var qv = this.aggregateFilter.getManagedFilterValue( v );
			if( qv ){
				this.ext.systemQuery.push( appdb.utils.UniqueOrderedArray(qv) );				
			}
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
            var fq = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
            if(this._apptype===null){
				this.views._export.setValue(fq);
                this.views._export.render();
			}
			this._model.get({flt: fq, pageoffset:0});
		},caller: this});
		v.logistics.subscribe({event:"beforeload", callback: function(v){
				this.views.managedFilter.setLoading(true);
		},caller: this});
		v.logistics.subscribe({event: "load", callback: function(v){
				v = v || {};
				var display = false;
				for( var i in v){
					if( !v.hasOwnProperty(i) ) continue;
					v[i] = v[i] || [];
					v[i] = $.isArray(v[i])?v[i]:[v[i]];
					if(v[i].length > 1 ){
						display = true;
						break;
					}
				}
				var l = this.getCurrentData();
				var ul = $.grep(this.aggregateFilter.getFilters({source:"user"}), function(e){
					return (e.type !== "user" );
				});
				var pc = this.views.pager.pageCount();
				this.views.managedFilter.setLoading(false);
				this.views.managedFilter.showAction((l.length >= 2 || ul.length > 0 || pc > 1) && display && pc > 1 );
				this.setManagedFiletringDisplay((l.length >= 2 || ul.length > 0 || pc > 1) && display);

		}, caller: this});
    };
	this._init = function(){
        var v = {};
        v.appsList = new appdb.views.AppsList({container : "ul#appmainlist"});
        v.pagerview = new appdb.views.MultiPagerPane({container: $("#apps_main_content").find(".pager")});
		v.filtering = new appdb.views.Filter({
            container : "apps_filter",
            rich : true,
            watermark : this.ext.filterDisplay
        });
		
        if(this.ext.userQuery){
            v.filtering.setValue(this.ext.userQuery);
        }
        v.ordering = new appdb.views.Ordering({
            container : "apps_orderby",
            selected : "rank",
            items : [
                {name : "Relevance" , value : "rank", defaultOperation:"DESC", hideOperation: true},
                {name : "Name" , value : "name", defaultOperation:"ASC"},
                {name : "Date Added" , value : "dateadded", defaultOperation:"DESC"},
                {name : "ID" , value : "id", defaultOperation:"ASC"},
                {name : "Rating", value : "rating", defaultOperation:"DESC"},
                {name : "Most visited", value: "hitcount", defaultOperation:"DESC"},
				{name : "Last updated", value: "lastupdated", defaultOperation:"DESC"},
                {name : "Unsorted" , value : "unsorted",hideOperation: true}
            ]
        });
        v.resulttimer = new appdb.views.ResultTimer({container:$("#apps_result_time")});
        v.loading = new appdb.views.DelayedDisplay($("#apps_loading"));
        v.viewbuttons = new appdb.views.ListViewMode({container: $("#apps_viewbuttons"),list : "ul#appmainlist"});
        v._export = new appdb.views.Export({container : $("#exportdiv"),target:'apps'});
        v.permalink = new appdb.views.Permalink({container:$("#apps_permalink"),datatype:"apps", contents: "<img src='/images/link.png' alt='' />"});
        v.atom = new appdb.views.NewsFeed({container : $("#apps_atomfeed"),feed:{type:"app",title:"Software news feed",flt:""},customizable:true});
		v.mail = new appdb.components.MailSubscription({container : $("#apps_mailfeed"), subjecttype:"app"});
		this.aggregateFilter = new appdb.utils.FilterAggregator({
			parent: this,
			entityType: "software"
		});
		v.managedFilter = new appdb.views.ManagedFiltering({
			container: $("#apps_managedfilters"),
			parent: this,
			filter: this.aggregateFilter,
			maxLength: 30
		});
		this.views = v;
		this.setViewsParent(this);
    };
	this._init();
},{
	validOptionEnabled : false,
	onRenderFollowedItem: function(elem, data){
		var entrydom = $(document.createElement("div")).addClass("followme");
		$(elem).children(".item").addClass("followed").append(entrydom);
		var entrysubscr = new appdb.components.EntrySubscription({
			container: entrydom ,
			entryid: data.id,
			id: data.id,
			displayhelp:false, 
			defaultstate: "following",
			autoload: false, 
			model: new appdb.model.FollowApplication()
		});
		entrysubscr._subscription.id = data.id;
		entrysubscr.render();
	}
});

appdb.components.VOs = appdb.ExtendClass(appdb.Component,"appdb.components.VOs",function(o){
    o = o || {};
    this.ext = o.ext || {};
	this.isLoaded = false;
    this._baseQuery = this.ext.baseQuery || null;
    this._getFullQuery = function(v){
        var res;
        if(typeof v === "string"){
            if( this._baseQuery){
                res = '' + v;
                res = v + " " + this._baseQuery.flt;
                return res;
            }else{
                return v;
            }
        }
        return v;
    };
    this._getUserQuery = function(v){
        var res,bqi,bq,f;
        if(v && this._baseQuery && this._baseQuery.flt){
            res = $.extend({},v);
            f = res.flt;
            bq = ''+this._baseQuery.flt;
            bqi = f.indexOf(bq);
            if(bqi>=0){
                f = f.substring(0,bqi);
                if(f[f.length-1]===" "){
                    f = f.substring(0,f.length-1);
                }
            }
            res.flt = f;
            return res;
        }
        return v;
    };
	this.getCurrentData = function(){
		if( !this._model || !this._model.getLocalData() ) return [];
		var m = this._model.getLocalData().vo || [];
		m = m || [];
		m = $.isArray(m)?m:[m];
		return m;
	};
    this._componentTitle = "Virtual Organizations";
    this.render = function(d,p,t){
        var start = (new Date()).getTime(), v = this.views, _dom = this.doms, qvalue=this._model.getQuery();
        var len = ((d)?(typeof d.length==="number")?d.length:len:0);
		var ofs = (p)?(typeof p.pageoffset==="number")?p.pageoffset:ofs:0;
		$(_dom).empty();
        v.vosList.render(d);
        if(typeof len === "undefined"){
            if(ofs===0){
                v.pagerview.reset();
                v.viewbuttons.reset();
            }else{
                v.pagerview.render(p);
                v.viewbuttons.render();
            }
        }else if(len===0){
            v.pagerview.reset();
            v.viewbuttons.reset();
        }else{
            v.pagerview.render(p);
            v.viewbuttons.render();
        }
		v.filtering.setWatermark(this.ext.filterDisplay);
		v.filtering.setValue(this.aggregateFilter.getUserFilter());
        v.filtering.render();
        v.resulttimer.render(p.count,t);
		v.permalink.render({query:this.ext.baseQuery || qvalue,ext:this.ext});
		var end = (new Date()).getTime();
        if ( $.trim(appdb.config.appenv) !== 'production' ) {
            v.resulttimer.appendRender(end-start);
        }
		if( $("#vos_orderby > div").length > 0 ) {$("#vos_orderby > div")[0].style.overflow = "hidden";}
		$("#vos_logisticsselector").hide();
		window.scroll(0,0);
		$("#mainbody").removeClass("terminal");
    };
    this.load = function(o){
        var v = this.views;
        this.clearEvents();
		this.isLoaded = true;
        this.ext = o.ext || {};
        if(this.ext.mainTitle){
            this.setComponentTitle(this.ext.mainTitle);
        }
        var _base ;
        this.aggregateFilter.loadFilter( {ext: this.ext} );
		v.filtering.setWatermark(this.ext.filterDisplay);
		
		if(this.ext.baseQuery && this.ext.baseQuery.flt){
			_base = $.extend({},this.ext.baseQuery);
			
            this._baseQuery = $.extend({},_base);
			//Normalize query object
            if(o.query && o.query.flt){
				var po = o.query.pageoffset || 0;
				var pl = o.query.pagelength || optQueryLen;
                o.query =  $.extend({},_base);
				o.query.pagelength = o.query.pagelength || pl;
				o.query.pageoffset = o.query.pageoffset || po;
                if(this.ext.userQuery || this.ext.systemQuery){
                    o.query.flt = this.aggregateFilter.getFullQuery();
                }
            }
        }else if( !this.ext.systemQuery ){
			this._baseQuery = null;
			this.ext.userQuery = o.query;
        }
		var query = o.query || {};
		if(this.views.ordering && this.views.ordering.getSelected()!==null){
		 if(typeof query.orderby === "undefined" && typeof query.orderbyOp === "undefined"){
		  this.views.ordering.resetSelected();
		 }
		 this.views.ordering.setSelected(query.orderby,query.orderbyOp);
		}
		this.views.ordering.render();
		var logisticsoptions = {
			container: $("#vos_logisticsselector"),
			parent: this,
			entityType: "vos",
			subtype: ""
		};
		query.flt = query.flt || this.aggregateFilter.getFullQuery();
        if( v.logistics ){
			v.logistics.abort();
			v.logistics._mediator.clearAll();
			v.logistics.destroy();
			v.logistics = null;
		}
		v.logistics = new appdb.components.LogisticsSelector(logisticsoptions);
		appdb.debug("[DEBUG] VOs Fetch: " + query.flt);
		if( typeof query.name !== "undefined" || $.trim(query.name) === ""){
			delete query.name;
		}
		if( $.trim(query.vomembership) === ""){
			delete query.vomembership;
		}
		if( $.trim(query.vomembership) !== "" ){
			this._model = new appdb.model.PeopleVOs(query);
			this.setManagedFiletringDisplay(false);
		}else{
			this._model = new appdb.model.VOs(query);
		}
        
        this._model.subscribe({event:"beforeselect",callback:function(){
                this.views.loading.show();
				this.views.managedFilter.setLoading(true);
            },caller:this});
        this._model.subscribe({event:"error",callback:function(err){
                this.views.loading.hide();
                this.ErrorHandler.handle({status:"Data transfer error",description:"An error occured during software list data transfer",source : err});
            },caller:this});
        v.pager = this._model.getPager().subscribe({event:'pageload',callback:function(d){
            this.render(d.data,d.pager,d.elapsed);
            this.views.loading.hide();
            $(this._dom).show();
			var q = this._model.getQuery();
			if( $.trim(q.flt) ){
				q.flt = this.aggregateFilter.getFullQuery();
			}
			var excludelogistics = this.aggregateFilter.getFilters({source:"system"});
			excludelogistics = excludelogistics.concat(this.aggregateFilter.getFilters({source:"implicit"}));//parsed from base query
			excludelogistics = excludelogistics.concat(this.aggregateFilter.getFilters({source:"user"}));//parsed from user query
			this.views.managedFilter.render();
			this.views.logistics.load(this.aggregateFilter.getFullQuery(),excludelogistics);
			if(this.isLoaded == false){
			 appdb.Navigator.internalCall(appdb.Navigator.Registry[this.ext.callerName || "VOs"],{query:q,ext:this.ext});
			}else{
			 appdb.Navigator.setTitle(appdb.Navigator.Registry[this.ext.callerName || "VOs"],[q,this.ext]);
			}
		   this.isLoaded = false;
           this.publish({event:'loaded',value:d});
		   var uq = this.aggregateFilter.getUserFilter();
			if( uq ){
				this.views.filtering.setValue({flt: uq.value});
			}
			appdb.debug("[DEBUG] VOs Paging Load: " + q.flt);
        },caller : this});
        v.pager.current();
        v.pagerview.subscribe({event:"next",callback : function(){
            this.views.pager.next();
        }, caller : this});
        v.pagerview.subscribe({event:"previous",callback : function(){
            this.views.pager.previous();
        }, caller : this});
        v.pagerview.subscribe({event:"current",callback : function(v){
            this.views.pager.current(v);
        }, caller : this});
        v.ordering.subscribe({event:"order",callback:function(v){
            this._model.get(v);
        },caller : this});
        v.filtering.subscribe({event:"filter",callback:function(v){
           this.aggregateFilter.loadUserFilter(v.flt);
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
            v.flt = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
			appdb.debug("[DEBUG] VOs Filter: " + v.flt);
			this._model.get(v);
        },caller: this});
        v.vosList.subscribe({event:"itemclick",callback:function(data){
            var p ,s = this.views.pager.getCurrentPagingState();
            if(s.pagenumber>0){
                p = {previousPager:{pagelength:s.length,pageoffset: s.offset}};
            }
            appdb.views.Main.showVO(data.name,p);
        },caller : this});
		v.managedFilter.subscribe({event: "changed", callback: function(v){
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
			this.ext.systemQuery = this.aggregateFilter.getSystemQueriesValues();
			var bqflt = this.aggregateFilter.getBaseQueryValue();
			if( $.trim(bqflt) === "" ){
				if( this.ext.baseQuery ){
					this.ext.baseQuery.flt = "";
				}
			} else {
				if( this.ext.baseQuery ){
					this.ext.baseQuery.flt = bqflt;
				}else{
					this.ext.baseQuery = {flt: bqflt};
				}
			}
			v.flt = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
			this._model.get({flt: v.flt, pageoffset:0});
		}, caller: this});
		v.managedFilter.subscribe({event:"action", callback: function(v){
			if( $.trim(v) === "display" ){
				if( $("#vos_logisticsselector").is(":visible") === false ){
					$(this.views.managedFilter.dom).children("a.action.display:first").addClass("expanded");
					$("#vos_logisticsselector").slideDown("fast");
					$("#vos_logisticsselector").removeClass("hidden");
				} else {
					$(this.views.managedFilter.dom).children("a.action.display:first").removeClass("expanded");
					$("#vos_logisticsselector").slideUp("fast");
				}
			}else if ( $.trim(v) === "reset" ){
				this.aggregateFilter.removeFilters({source: "system"});
				this.aggregateFilter.removeFilters({source: "user"});
				delete this.ext.systemQuery;
				this._model.get({flt: this.aggregateFilter.getFullQuery(), pageoffset: 0});
			}
			appdb.debug("[DEBUG] VOs Filtering Changed: " + v.flt);
		}, caller: this});
		v.logistics.subscribe({event: "addfilter", callback: function(v){
			this.aggregateFilter.appendSystemFilter(v);
			this.ext.systemQuery = this.ext.systemQuery || [];
			this.ext.systemQuery = $.isArray(this.ext.systemQuery)?this.ext.systemQuery:[this.ext.systemQuery];
			var qv = this.aggregateFilter.getManagedFilterValue( v );
			if( qv ){
				this.ext.systemQuery.push( appdb.utils.UniqueOrderedArray(qv) );				
			}
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
            var fq = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
            this._model.get({flt: fq, pageoffset:0});
		},caller: this});
		v.logistics.subscribe({event:"beforeload", callback: function(v){
				this.views.managedFilter.setLoading(true);
		},caller: this});
		v.logistics.subscribe({event: "load", callback: function(v){
				v = v || {};
				var display = false;
				for( var i in v){
					if( !v.hasOwnProperty(i) ) continue;
					v[i] = v[i] || [];
					v[i] = $.isArray(v[i])?v[i]:[v[i]];
					if(v[i].length > 1 ){
						display = true;
						break;
					}
				}
				var l = this.getCurrentData();
				var ul = $.grep(this.aggregateFilter.getFilters({source:"user"}), function(e){
					return (e.type !== "user" );
				});
				var pc = this.views.pager.pageCount();
				this.views.managedFilter.setLoading(false);
				this.views.managedFilter.showAction((l.length >= 2 || ul.length > 0 || pc > 1) && display && pc > 1 );
				this.setManagedFiletringDisplay((l.length >= 2 || ul.length > 0 || pc > 1) && display);

		}, caller: this});
    };
	this.setManagedFiletringDisplay = function(display){
		display = ( ($.type(display) === "boolean")?display:false );
		if( display ){
			$(".refinedfiltering").removeClass("hidden");
			$(this.views.managedFilter.dom).children(".action.display").show();
			$("#vos_logisticsselector").removeClass("hidden");
		}else {
			var flts = this.aggregateFilter.getFilters({source: "system"});
			$(this.views.managedFilter.dom).children(".action.display").hide();
			$("#vos_logisticsselector").addClass("hidden");
			if( flts.length > 0 ){
				$(".refinedfiltering").removeClass("hidden");
			}else{
				$(".refinedfiltering").addClass("hidden");
			}
		}
	};
    this._init = function(){
        var v = {};
        v.vosList = new appdb.views.VOsList({container : "ul#vosmainlist"});
        v.pagerview = new appdb.views.MultiPagerPane({container:$("#vos_main_content").find(".pager")});
        v.filtering = new appdb.views.Filter({
            container : "vos_filter",
            rich : true,
            watermark : this.ext.filterDisplay,
			displayClear : true
        });
        v.ordering = new appdb.views.Ordering({
            container : "vos_orderby",
            selected : "rank",
            items : [
                {name : "Relevance" , value : "rank", defaultOperation: "DESC", hideOperation: true},
                {name : "Name" , value : "name", defaultOperation: "ASC"},
                {name : "ID" , value : "id", defaultOperation: "ASC"},
                {name : "Date" , value : "validated", defaultOperation: "DESC"},
                {name : "Unsorted" , value : "unsorted", defaultOperation: "ASC", hideOperation: true}
            ]
        });
        v.viewbuttons = new appdb.views.ListViewMode({container : $("#vos_viewbuttons"),list : "ul#vosmainlist"});
        v.resulttimer = new appdb.views.ResultTimer({container : $("#vos_result_timer")});
        v.loading = new appdb.views.DelayedDisplay($("#vos_loading"));
		v.permalink = new appdb.views.Permalink({container:$("#vos_permalink"),datatype:"vos"});
		this.aggregateFilter = new appdb.utils.FilterAggregator({
			parent: this,
			entityType: "vos"
		});
		v.managedFilter = new appdb.views.ManagedFiltering({
			container: $("#vos_managedfilters"),
			parent: this,
			filter: this.aggregateFilter,
			maxLength: 30
		});
        this.views = v;
        this.setViewsParent(this);
    };
    this._init();
});

appdb.components.Sites = appdb.ExtendClass(appdb.Component, "appdb.components.Sites", function(o){
	o = o || {};
	this.ext = o.ext || {};
	this._baseQuery = this.ext.baseQuery || null;
	this._componentTitle = "Sites";
	this.getCurrentData = function(){
		if( !this._model || !this._model.getLocalData() ) return [];
		var m = this._model.getLocalData().site || [];
		m = m || [];
		m = $.isArray(m)?m:[m];
		return m;
	};
	this.setManagedFiletringDisplay = function(display){
		display = ( ($.type(display) === "boolean")?display:false );
		if( display ){
			$(".refinedfiltering").removeClass("hidden");
			$(this.views.managedFilter.dom).children(".action.display").show();
			$("#sites_logisticsselector").removeClass("hidden");
		}else {
			var flts = this.aggregateFilter.getFilters({source: "system"});
			$(this.views.managedFilter.dom).children(".action.display").hide();
			$("#sites_logisticsselector").addClass("hidden");
			if( flts.length > 0 ){
				$(".refinedfiltering").removeClass("hidden");
			}else{
				$(".refinedfiltering").addClass("hidden");
			}
		}
	};
	this.render = function(d, p ,t){
		var start = (new Date()).getTime(), v = this.views, _dom = this.doms, qvalue=this._model.getQuery();
        var len = ((d)?(typeof d.length==="number")?d.length:len:0);
		var ofs = (p)?(typeof p.pageoffset==="number")?p.pageoffset:ofs:0;
		$(_dom).empty();
        v.sitesList.render(d);
        if(typeof len === "undefined"){
            if(ofs===0){
                v.pagerview.reset();
                v.viewbuttons.reset();
            }else{
                v.pagerview.render(p);
                v.viewbuttons.render();
            }
        }else if(len===0){
            v.pagerview.reset();
            v.viewbuttons.reset();
        }else{
            v.pagerview.render(p);
            v.viewbuttons.render();
        }
		v.filtering.setWatermark(this.ext.filterDisplay);
		v.filtering.setValue(this.aggregateFilter.getUserFilter());
        v.filtering.render();
        v.resulttimer.render(p.count,t);
		v.permalink.render({query:this.ext.baseQuery || qvalue,ext:this.ext});
		var end = (new Date()).getTime();
        if ( $.trim(appdb.config.appenv).toLowerCase() !== 'production' ) {
            v.resulttimer.appendRender(end-start);
        }
		if( $("#sites_orderby > div").length > 0 ) {$("#sites_orderby > div")[0].style.overflow = "hidden";}
		$("#sites_logisticsselector").hide();
		window.scroll(0,0);
		$("#mainbody").removeClass("terminal");	
	};
	this.load = function(o){
		appdb.pages.reset();
		var v = this.views;
        this.clearEvents();
		this.isLoaded = true;
        this.ext = o.ext || {};
        if(this.ext.mainTitle){
            this.setComponentTitle(this.ext.mainTitle);
        }
        var _base ;
        this.aggregateFilter.loadFilter( {ext: this.ext} );
		v.filtering.setWatermark(this.ext.filterDisplay);
		if(this.ext.baseQuery && this.ext.baseQuery.flt){
			_base = $.extend({},this.ext.baseQuery);
			this._baseQuery = $.extend({},_base);
			//Normalize query object
            if(o.query && o.query.flt){
				var po = o.query.pageoffset || 0;
				var pl = o.query.pagelength || optQueryLen;
                o.query =  $.extend({},_base);
				o.query.pagelength = o.query.pagelength || pl;
				o.query.pageoffset = o.query.pageoffset || po;
                if(this.ext.userQuery || this.ext.systemQuery){
                    o.query.flt = this.aggregateFilter.getFullQuery();
                }
            }
        }else if( !this.ext.systemQuery ){
			this._baseQuery = null;
			this.ext.userQuery = o.query;
        }
		var query = o.query || {};
		if(this.views.ordering && this.views.ordering.getSelected()!==null){
		 if(typeof query.orderby === "undefined" && typeof query.orderbyOp === "undefined"){
		  this.views.ordering.resetSelected();
		 }
		 this.views.ordering.setSelected(query.orderby,query.orderbyOp);
		}
		this.views.ordering.render();
		var logisticsoptions = {
			container: $("#sites_logisticsselector"),
			parent: this,
			entityType: "sites",
			subtype: ""
		};
		query.flt = query.flt || this.aggregateFilter.getFullQuery();
        if( v.logistics ){
			v.logistics.abort();
			v.logistics._mediator.clearAll();
			v.logistics.destroy();
			v.logistics = null;
		}
		v.logistics = new appdb.components.LogisticsSelector(logisticsoptions);
		appdb.debug("[DEBUG] Sites Fetch: " + query.flt);
		if( typeof query.name !== "undefined" || $.trim(query.name) === ""){
			delete query.name;
		}
		this._model = new appdb.model.Sites(query);
		
        this._model.subscribe({event:"beforeselect",callback:function(){
                this.views.loading.show();
				this.views.managedFilter.setLoading(true);
            },caller:this});
        this._model.subscribe({event:"error",callback:function(err){
                this.views.loading.hide();
                this.ErrorHandler.handle({status:"Data transfer error",description:"An error occured during sites list data transfer",source : err});
            },caller:this});
        v.pager = this._model.getPager().subscribe({event:'pageload',callback:function(d){
            this.render(d.data,d.pager,d.elapsed);
            this.views.loading.hide();
            $(this._dom).show();
			var q = this._model.getQuery();
			if( $.trim(q.flt) ){
				q.flt = this.aggregateFilter.getFullQuery();
			}
			var excludelogistics = this.aggregateFilter.getFilters({source:"system"});
			excludelogistics = excludelogistics.concat(this.aggregateFilter.getFilters({source:"implicit"}));//parsed from base query
			excludelogistics = excludelogistics.concat(this.aggregateFilter.getFilters({source:"user"}));//parsed from user query
			this.views.managedFilter.render();
			this.views.logistics.load(this.aggregateFilter.getFullQuery(),excludelogistics);
			if(this.isLoaded === false){
			 appdb.Navigator.internalCall(appdb.Navigator.Registry[this.ext.callerName || "Sites"],{query:q,ext:this.ext});
			}else{
			 appdb.Navigator.setTitle(appdb.Navigator.Registry[this.ext.callerName || "Sites"],[q,this.ext]);
			}
		   this.isLoaded = false;
           this.publish({event:'loaded',value:d});
		   var uq = this.aggregateFilter.getUserFilter();
			if( uq ){
				this.views.filtering.setValue({flt: uq.value});
			}
			appdb.debug("[DEBUG] Sites Paging Load: " + q.flt);
        },caller : this});
        v.pager.current();
        v.pagerview.subscribe({event:"next",callback : function(){
            this.views.pager.next();
        }, caller : this});
        v.pagerview.subscribe({event:"previous",callback : function(){
            this.views.pager.previous();
        }, caller : this});
        v.pagerview.subscribe({event:"current",callback : function(v){
            this.views.pager.current(v);
        }, caller : this});
        v.ordering.subscribe({event:"order",callback:function(v){
            this._model.get(v);
        },caller : this});
        v.filtering.subscribe({event:"filter",callback:function(v){
           this.aggregateFilter.loadUserFilter(v.flt);
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
            v.flt = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
			appdb.debug("[DEBUG] SItes Filter: " + v.flt);
			this._model.get(v);
        },caller: this});
        v.sitesList.subscribe({event:"itemclick",callback:function(data){
			var p ,s = this.views.pager.getCurrentPagingState();
            if(s.pagenumber>0){
                p = {previousPager:{pagelength:s.length,pageoffset: s.offset}};
            }
            appdb.views.Main.showSite(data,p);
        },caller : this});
		v.managedFilter.subscribe({event: "changed", callback: function(v){
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
			this.ext.systemQuery = this.aggregateFilter.getSystemQueriesValues();
			var bqflt = this.aggregateFilter.getBaseQueryValue();
			if( $.trim(bqflt) === "" ){
				if( this.ext.baseQuery ){
					this.ext.baseQuery.flt = "";
				}
			} else {
				if( this.ext.baseQuery ){
					this.ext.baseQuery.flt = bqflt;
				}else{
					this.ext.baseQuery = {flt: bqflt};
				}
			}
			v.flt = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
			this._model.get({flt: v.flt, pageoffset:0});
		}, caller: this});
		v.managedFilter.subscribe({event:"action", callback: function(v){
			if( $.trim(v) === "display" ){
				if( $("#sites_logisticsselector").is(":visible") === false ){
					$(this.views.managedFilter.dom).children("a.action.display:first").addClass("expanded");
					$("#sites_logisticsselector").slideDown("fast");
					$("#sites_logisticsselector").removeClass("hidden");
				} else {
					$(this.views.managedFilter.dom).children("a.action.display:first").removeClass("expanded");
					$("#sites_logisticsselector").slideUp("fast");
				}
			}else if ( $.trim(v) === "reset" ){
				this.aggregateFilter.removeFilters({source: "system"});
				this.aggregateFilter.removeFilters({source: "user"});
				delete this.ext.systemQuery;
				this._model.get({flt: this.aggregateFilter.getFullQuery(), pageoffset: 0});
			}
			appdb.debug("[DEBUG] Sites Filtering Changed: " + v.flt);
		}, caller: this});
		v.logistics.subscribe({event: "addfilter", callback: function(v){
			this.aggregateFilter.appendSystemFilter(v);
			this.ext.systemQuery = this.ext.systemQuery || [];
			this.ext.systemQuery = $.isArray(this.ext.systemQuery)?this.ext.systemQuery:[this.ext.systemQuery];
			var qv = this.aggregateFilter.getManagedFilterValue( v );
			if( qv ){
				this.ext.systemQuery.push( appdb.utils.UniqueOrderedArray(qv) );				
			}
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
            var fq = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
            this._model.get({flt: fq, pageoffset:0});
		},caller: this});
		v.logistics.subscribe({event:"beforeload", callback: function(v){
				this.views.managedFilter.setLoading(true);
		},caller: this});
		v.logistics.subscribe({event: "load", callback: function(v){
				v = v || {};
				var display = false;
				for( var i in v){
					if( !v.hasOwnProperty(i) ) continue;
					v[i] = v[i] || [];
					v[i] = $.isArray(v[i])?v[i]:[v[i]];
					if(v[i].length > 1 ){
						display = true;
						break;
					}
				}
				var l = this.getCurrentData();
				var ul = $.grep(this.aggregateFilter.getFilters({source:"user"}), function(e){
					return (e.type !== "user" );
				});
				var pc = this.views.pager.pageCount();
				this.views.managedFilter.setLoading(false);
				this.views.managedFilter.showAction((l.length >= 2 || ul.length > 0 || pc > 1) && display && pc > 1 );
				this.setManagedFiletringDisplay((l.length >= 2 || ul.length > 0 || pc > 1) && display);

		}, caller: this});
	};
	this._init = function(){
		var v = {};
        v.sitesList = new appdb.views.SitesList({container : "ul#sitesmainlist"});
        v.pagerview = new appdb.views.MultiPagerPane({container:$("#sites_main_content").find(".pager")});
        v.filtering = new appdb.views.Filter({
            container : "sites_filter",
            rich : true,
            watermark : this.ext.filterDisplay,
			displayClear : true,
			type: "sites"
        });
        v.ordering = new appdb.views.Ordering({
            container : "sites_orderby",
            selected : "rank",
            items : [
                {name : "Name" , value : "name", defaultOperation: "ASC"},
                {name : "ID" , value : "id", defaultOperation: "ASC"},
				{name : "Country", value: "countryname", defaultOperation: "ASC"},
                {name : "Unsorted" , value : "unsorted", defaultOperation: "ASC", hideOperation: true}
            ]
        });
        v.viewbuttons = new appdb.views.ListViewMode({container : $("#sites_viewbuttons"),list : "ul#sitesmainlist"});
        v.resulttimer = new appdb.views.ResultTimer({container : $("#sites_result_timer")});
        v.loading = new appdb.views.DelayedDisplay($("#sites_loading"));
		v.permalink = new appdb.views.Permalink({container:$("#sites_permalink"),datatype:"vos"});
		this.aggregateFilter = new appdb.utils.FilterAggregator({
			parent: this,
			entityType: "sites"
		});
		v.managedFilter = new appdb.views.ManagedFiltering({
			container: $("#sites_managedfilters"),
			parent: this,
			filter: this.aggregateFilter,
			maxLength: 30
		});
        this.views = v;
        this.setViewsParent(this);
	};
	this._init();
});

appdb.components.Site = appdb.ExtendClass(appdb.Component, "appdb.components.Site", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: {}
	};
	this.reset = function(){
		
	};
	this.getServices = function(servicetype){
		servicetype = $.trim(servicetype);
		var d = (this.options.data || {}).service || [];
		d = $.isArray(d)?d:[d];
		return $.grep(d, function(e){
			return ( servicetype !== "" && $.trim(e["type"]).toLowerCase() === "occi" );
		});
	};
	this.getOcciImages = function(){
		var servs = this.getServices("occi");
		return appdb.utils.GroupSiteImages(servs);
	};
	this.renderLoading = function(enabled){
		enabled = (typeof enabled === "boolean")?enabled:false;
		hideAjaxLoading();
		if( enabled ){
			$(this.options.container).hide();
			showAjaxLoading();
		}else{
			$(this.options.container).show();
			hideAjaxLoading();
		}
	};
	this.renderSiteLinks = function(d){
		var list = $(this.dom).find(".site-links").empty();
		d.url = d.url || [];
		d.url = $.isArray(d.url)?d.url:[d.url];
		d.url = d.url.sort( function(a,b){
			var aa = a["type"];
			var bb = b["type"];
			if( aa < bb ) return -1;
			if( aa > bb ) return 1;
			return 0;
		});
		$.each( d.url, function(i, e){
			if( e.val ){
				var a = $("<a href='#' title='Open link in new window' target='_blank'></a>");
				var t = e.type[0].toUpperCase() + e.type.slice(1);
				$(a).attr("href", e.val());
				if( e.type === 'portal' ){
					$(a).append("<img src='/images/gocdb.png' alt='' />");
					t = "GOCDB Portal";
				}
				
				$(a).append("<span>"+t+"</span>");
				$(list).append(a);
				$(list).append("<span class='seperator'>|</span>");
			}
		});
		$(list).find(".seperator:last").remove();
	};
	this.renderContacts = function(d){
		var ul = $("<ul class='contactlist'></ul>");
		d.contact = d.contact || [];
		d.contact = $.isArray(d.contact)?d.contact:[d.contact];
		
		d.contact = d.contact.sort( function(a,b){
			var aa = a["type"];
			var bb = b["type"];
			if( aa < bb ) return -1;
			if( aa > bb ) return 1;
			aa = a["contexttype"];
			bb = b["contexttype"];
			if( aa < bb ) return -1;
			if( aa > bb ) return 1;
			return 0;
		});
		$.each(d.contact, function(i,e){
			var li = $("<li></li>");
			switch(e.contacttype){
				case "email":
					$(li).append("<span class='field'><img title='e-mail type' alt='e-mail type' src='/images/contacts/e-mail.png' /><span>"+e.type+":</span></span><span class='value'/>");
					if( e["protected"] ) {
						$(li).find(".value").empty().append("<img title='e-mail' alt='e-mail' src='" + e.val() + "' />");
					} else {
						$(li).find(".value").empty().text(e.val());
					}
					break;
				case"tel":
					$(li).append("<span class='field'><img title='e-mail type' alt='e-mail type' src='/images/contacts/Phone.png' /><span>"+e.type+":</span></span><span class='value'/>");
					$(li).find(".value").empty().text(e.val());
					break;
			}
			$(ul).append(li);
		});
		
		$(this.dom).find("#contactinfo").empty().append(ul);
	};
	this.renderSiteGroup = function(d){
		this.renderContacts(d);
	};
	this.renderMainData = function(d){
		this.dom = $(this.options.container).children("#appdb_components_Site");
		$(this.dom ).find(".site-id").empty().text(d.id);
		$(this.dom ).find(".site-name").empty().text(d.name);
		$(this.dom ).find(".site-officialname").empty().text(d.officialname);
		$(this.dom ).find(".site-country-name").empty();
		if( d.country && d.country.val ) {
			$(this.dom).find(".site-country-name").text(d.country.val());
			$(this.dom).find(".site-country-flag").attr("src", "/images/flags/" + $.trim(d.country.isocode).toLowerCase() + ".png" );
			$(this.dom).find(".site-country-flag").attr("alt", d.country.val() );
		}
		
		$(this.dom).find(".site-description").empty().text($.trim(d.description));
		if( $.trim(d.description) === "" ){
			$(this.dom).find(".site-description-onempty").text("n/a");
		}else{
			$(this.dom).find(".site-description-onempty").empty();
		}
		
		$(this.dom).find(".site-permalink").attr("href", appdb.config.endpoint.base + "store/site/" + d.name);
	};
	this.renderSiteContents = function(d){
		this._initSiteContents();
		var images = this.getOcciImages();
		
		$(this.dom).find(".sitecontents .filterdecorator").addClass("hidden");
		$(this.dom).find(".sitecontents > .emptycontent").removeClass("hidden");
		if( images.length > 0 ){
			$(this.dom).find(".sitecontents > .emptycontent").addClass("hidden");
			$(this.dom).find(".sitecontents .filterdecorator").removeClass("hidden");	
		}
		
		if( this.views.vmitemlist ){
			this.views.vmitemlist.reset();
			this.views.vmitemlist = null;
		}
		if( this.views.vmlistfilter ){
			this.views.vmlistfilter.reset();
			this.views.vmlistfilter = null;
		}
		this.views.vmitemlist = new appdb.views.SiteVMImageList({
			container: $(this.dom).find(".filterdecorator ul.vmitems"),
			parent: this,
			data: images,
			siteData: d
		});
		this.views.vmitemlist.render(images);
		
		if( images && images.length && images.length > 1){
			$(this.dom).find(".vmitemsfilter").removeClass("hidden");
		}else{
			$(this.dom).find(".vmitemsfilter").addClass("hidden");
		}
		
		this.views.vmlistfilter = new appdb.views.SiteVMImageListFilter({
			container: $(this.dom).find(".vmitemsfilter"),
			parent: this,
			data: images,
			vmimagelist: this.views.vmitemlist
		});
		this.views.vmlistfilter.render();
	};
	this.render = function(d){
		this.options.data = d || this.options.data;
		this.renderMainData(d);
		this.renderSiteGroup(d);
		this.renderSiteLinks(d);
		this.renderSiteContents(d);
	};
	this.load = function(d){
		this.renderLoading(true);
		if( this._model ){
			this._model.unsubscribeAll();
			this._model = null;
		}
		this._model = new appdb.model.Site();
		this._model.subscribe({event: "beforeselect", callback: function(v){
		}, caller: this });
		this._model.subscribe({event: "select", callback: function(v){
				this.reset();
				appdb.pages.site.currentData(v.site);
				if( v && !v.error){
					this.render(v.site);
				}else{
					this.renderError(v);
				}
				appdb.pages.site.onSiteLoaded(v.site);
				this.renderLoading(false);
		}, caller: this });
		this._model.get( { "id": "s:" + $.trim(d.query.name).toUpperCase()} );
	};
	this._initSiteContents = function(){
		$(this.dom).find(".sitecontents .filterdecorator .header ul > li > a").unbind("click").bind("click", function( ev ){
				ev.preventDefault();
				$(this).closest("ul").children("li").removeClass("current");
				$(this).parent().addClass("current");
				var fcls = $(this).parent().data("filterclass");
				$(this).closest(".filterdecorator").children("ul").removeClass("current");
				$(this).closest(".filterdecorator").children("ul." + fcls).addClass("current");
				return false;
		});
		$(this.dom).find(".sitecontents .filterdecorator .header ul > li > a:not(.hidden):first").trigger("click");
	};
	this._init = function(){
		this.parent = this.options.parent;
		
		var v = {};
		this.views = v;
	};
	this._init();
});

appdb.components.RelatedContacts = appdb.ExtendClass(appdb.Component,"appdb.components.RelatedContacts",function(o){
    o = o || {};
    this.ext = o.ext || {};
    this._baseQuery = this.ext.baseQuery || null;
    this._getFullQuery = function(v){
        var res;
        if(typeof v === "string"){
            if( this._baseQuery){
                res = '' + v;
                res = v + " " + this._baseQuery.flt;
                return res;
            }else{
                return v;
            }
        }
        return {};
    };
    this._getUserQuery = function(v){
        var res,bqi,bq,f;
        if(v && this._baseQuery && this._baseQuery.flt){
            res = $.extend({},v);
            f = res.flt;
            bq = ''+this._baseQuery.flt;
            bqi = f.indexOf(bq);
            if(bqi>=0){
                f = f.substring(0,bqi);
                if(f[f.length-1]===" "){
                    f = f.substring(0,f.length-1);
                }
            }
            res.flt = f;
            return res;
        }
        return v;
    };
    this.render = function(d,p,t){
        var start = (new Date()).getTime(), v = this.views, _dom = this.dom, qvalue=this._model.getQuery();
        var len = ((d)?(typeof d.length==="number")?d.length:len:0);
        var ofs = (p)?(typeof p.pageoffset==="number")?p.pageoffset:ofs:0;
		
		v.pagerview.reset();
		v.filtering.reset();
		d.permissions = {action : {id: 16}};
		v.peopleList.render(d);
		v.peopleList.EditMode(true);
        if(typeof len === "undefined"){
            if(ofs===0){
                v.pagerview.reset();
            }else if(p){
                v.pagerview.render(p);
            }
        }else if(len===0){
            v.pagerview.reset();
        }else if(p) {
            v.pagerview.render(p);
        }
        v.filtering.setWatermark(this.ext.filterDisplay);
        v.filtering.setValue(this.ext.userQuery);
        v.filtering.render();
		if ( ! o.hideCount ) {
			if ( p ) {
				v.resultCount.render(p.count);
			}
		}
		this.resultCount = (p)?p.count:0;
		if ( this._showExport ) {
			var fltval = v.filtering.getValue();
			if ( fltval !== "" && fltval !== undefined & fltval !== null ) {
				$(v._export.dom).show();
		        v._export.setValue(fltval);
		        v._export.render();	
			} else {
				$(v._export.dom).hide();
			}
		}
    };

    this.load = function(o){
        this.clearEvents();
		if(appdb.components.RelatedContacts.Dialog!==null){
			appdb.components.RelatedContacts.Dialog.hide();
			appdb.components.RelatedContacts.Dialog.destroyRecursive(false);
		}
		if ( ! this._container ) {
			appdb.components.RelatedContacts.Dialog = new dijit.Dialog({
						title: this.ext.mainTitle || "Associate person to software",
						content: $(this.dom)[0],
						style: "width: 730px;height:550px;"
					});
			appdb.components.RelatedContacts.Dialog.show();
		} else {
			$(this._container).empty().append(this.dom);
		}
		var v = this.views;
        this.ext = o.ext || {};
        if(this.ext.mainTitle){
            this.setComponentTitle(this.ext.mainTitle);
        }
        var _base ;
        v.filtering.setWatermark(this.ext.filterDisplay);
        if(this.ext.baseQuery && this.ext.baseQuery.flt){
            _base = $.extend({},this.ext.baseQuery);
            if((_base.flt[0]!=="+") && (_base.flt[0]!=="-")){
                _base.flt = "+" + _base.flt;
            }
            this._baseQuery = $.extend({},_base);
            if(o.query && o.query.flt){
                o.query =  $.extend({},_base);
                if(this.ext.userQuery){
                    o.query.flt = this.ext.userQuery.flt + " " + o.query.flt;
                }
            }
        }else{
            this._baseQuery = null;
            this.ext.userQuery = o.query;
        }
		var query = o.query || {};
		this._model = new appdb.model.People(query);
		this._model.subscribe({event:"beforeselect",callback:function(){
				this.views.loading.show();
			},caller:this});
		this._model.subscribe({event:"error",callback:function(err){
				this.views.loading.hide();
				this.ErrorHandler.handle({status:"Data transfer error",description:"An error occured during software list data transfer",source : err});
			},caller:this});
		v.pager = this._model.getPager().subscribe({event:'pageload',callback:function(d){
			this.render(d.data,d.pager,d.elapsed);
			this.views.loading.hide();
			$(this._dom).show();
		   this.publish({event:'loaded',value:{}});
		},caller : this});
		v.pager.current();
        v.pagerview.subscribe({event:"next",callback : function(){
            this.views.pager.next();
        }, caller : this});
        v.pagerview.subscribe({event:"previous",callback : function(){
            this.views.pager.previous();
        }, caller : this});
        v.pagerview.subscribe({event:"current",callback : function(v){
            this.views.pager.current(v);
        }, caller : this});
        v.filtering.subscribe({event:"filter",callback:function(v){
			var nf;
			nf = this.views.filtering.normalizeFilter(v.flt,{value: "people"});
            this.ext.userQuery = {flt:nf.normalForm};
            v.flt = this._getFullQuery(v.flt);
			nf = this.views.filtering.normalizeFilter(v.flt,{value: "people"});
			v.flt = nf.normalForm;
            if ( this._onClearNull ) {
				if ( v.flt === "" ) v.flt = null;
			}
			if(v.flt !== this._model.getQuery().flt){
                this._model.get(v);
            }
			if (nf.error) this.views.filtering.showFilterError(nf.error);
			if ( this._hideFiltering ) {
				this._doHideFilter(v.flt);
			}
        },caller: this});
        v.peopleList.subscribe({event:"itemselected",callback:function(data){
				this.publish({event: "itemselected", value: data});
        },caller:this});
        v.peopleList.subscribe({event:"itemclick",callback:function(data){
            var p ,s = this.views.pager.getCurrentPagingState();
            if(s.pagenumber>0){
                p = {pagelength:s.length,pageoffset: s.offset};
            }
			data.item.isSelected(!data.item.isSelected());
        },caller:this});
		var fltval = query.flt;
		if (this._hideFiltering) {
			this._doHideFilter(fltval);
		}
		
    };

	this._doHideFilter = function(fltval) {
		var v = this.views;
		$(v.filtering.dom).hide();
		$(this.dom).find(".editfilter").remove();
		if ( (typeof fltval === "undefined") || (fltval === null) ) fltval = "";
		try {
			fltval = fltval.trim();
		} catch (e) {
		}
		$(v.filtering.dom).parent().prepend($('<a href="#">Advanced filter</a>').addClass("editfilter").click((function(_this){
			return function() {
				$(_this.dom).find(".editfilter").hide();
				$(_this.views.filtering.dom).fadeIn();
			};
		})(this)));
	};

	this.closeDialog = function(){
		if(appdb.components.RelatedContacts.Dialog!==null){
			appdb.components.RelatedContacts.Dialog.hide();
			appdb.components.RelatedContacts.Dialog.destroyRecursive(false);
		}
		this.publish({event : "close",value:this});
		this.destroy();
	};
	this._initContainer = function(){
		var header = document.createElement("div"),  main = document.createElement("div"), loading = document.createElement("div"),
			footer = document.createElement("div"), filter = document.createElement("span"), preview = document.createElement("div"),
			pager = document.createElement("span"), list = document.createElement("ul"), actions = document.createElement("div"), 
			resCount = document.createElement("div"), rightSummary = document.createElement("div");
		this.dom = document.createElement("div");
		$(this.dom).addClass("relatedcontactsadder").addClass("viewsearch");
		$(header).addClass("header");
		$(main).addClass("main");
		$(footer).addClass("footer");
		$(filter).addClass("filter").attr("id","filter");
		$(filter).css("width","350px");
		$(rightSummary).addClass("rightSummary");
		$(preview).addClass("preview");
		$(resCount).addClass("resCount");
		this._exportContainer = document.createElement("div");
		$(this._exportContainer).addClass("exportContainer");
		$(loading).addClass("loading");
		$(pager).addClass("pager").attr("id","pager");
		$(list).addClass("list").addClass("itemgrid");
		$(actions).addClass("actions");
		$(header).append(filter).append(loading).append(rightSummary);
		$(rightSummary).append(resCount).append(this._exportContainer).append(preview);
		$(main).append(pager).append(list);
		$(footer).append(actions);
		$(this.dom).append(header).append(main).append(footer);
		$(actions).append("<div class='okbutton'></div>").append("<div class='cancelbutton'></div>");
		if ( typeof this._container === "undefined" ){
			new dijit.form.Button({
				label: "OK",
				style: "float:right;padding:5px;",
				onClick: (function(_this){
					return function() {
						_this.closeDialog();
					};

				})(this)
			},$(actions).find(".okbutton")[0]);
			new dijit.form.Button({
				label: "Cancel",
				style: "float:right;padding:5px;",
				onClick:  (function(_this){
					return function() {
						_this.views.peopleList.selectedDataItems.clear();
						_this.closeDialog();
					};
				})(this)
			},$(actions).find(".cancelbutton")[0]);
		}
		if ($.isEmptyObject(this.perspectives || {}) === false ) {
			$(preview).append("<span ><b>view :  </b></span>");
			var focused = " focused";
			for(var i in this.perspectives) {
				var p = this.perspectives[i];
				var a = $("<a class='"+(p.css || "")+focused+"' href='#' title='"+(p.description || "")+"' >"+p.displayName+"</a><span> | </span>");
				a.click((function(_this,_p){
					return function() {
						_this.focusPerspective(_p);
						_p.callback.apply(_this);
					};
				})(this,p));
				$(preview).append(a);
				
				focused = "";
			}
			$(preview).find("span:last").remove();
		}
	};
	this.hideFilter = function(hide) {
		if (typeof hide === "undefined") hide = true;
		if (hide) {
			$(this.dom).removeClass("viewsearch");
		} else {
			$(this.dom).addClass("viewsearch");
		}
	};
	this.focusPerspective = function(p) {
		$(this.dom).find(".preview a").removeClass("focused");
		if ( p.css ) {
			$(this.dom).find(".preview a."+p.css).addClass("focused");
		}
	};
    this._init = function(){
		if ( o.container ) {
			this._container = $(o.container);
		}
		if ( (o.onClearNull) && (o.onClearNull === true) ) {
			this._onClearNull = true;
		} else {
			this._onClearNull = false;
		}
		if ( (o.hideFiltering) && (o.hideFiltering === true) ) {
			this._hideFiltering = true;
		} else {
			this._hideFiltering = false;
		}
		if ( (o.showExport) && (o.showExport === true) ) {
			this._showExport = true;
		}
        var v = {};
        if(this.ext.mainTitle){
            this.setComponentTitle(this.ext.mainTitle);
        }
		this.perspectives = o.perspectives || appdb.components.RelatedContacts.perspectives;
		this.disableCheckForChanges = o.disableCheckForChanges || false;
		this._initContainer();
   		v.resultCount = new appdb.views.ResultTimer({
			container: $(this.dom).find(".resCount")
		});
		v.peopleList = new appdb.views.RelatedContactList({container : $(this.dom).find("ul.list:last")[0], disableCheckForChanges:this.disableCheckForChanges, permissions : {action : [{id:17}]}, canSetContactPoint : false, excluded : o.excluded, onExclude : o.onExclude || function(li){
				$(li.dom).find(".item a").unbind("click").css({"cursor":"default"}).attr("title","Already associated with the software");
				$(li.dom).css({"cursor":"default"}).find(".item:last").append("<div class='info'>Already associated with the software</div>");
		}});
        v.pagerview = new appdb.views.PagerPane({container : $(this.dom).find(".pager:last")[0]});
        v.filtering = new appdb.views.Filter({
            container : $(this.dom).find(".filter:last")[0],
			width: "290px",
            rich : true,
            watermark : this.ext.filterDisplay
        });
        if(this.ext.userQuery){
            v.filtering.setValue(this.ext.userQuery);
        }
		v.loading = new appdb.views.DelayedDisplay({selector: $(this.dom).find(".loading:last")[0],usedefault:true});
		if ( this._showExport ) {
        	v._export = new appdb.views.Export({container : this._exportContainer, target:'people'});
		}
        this.views = v;
        this.setViewsParent(this);
    };
    this._init();
},{
	"Dialog": null,
	"perspectives": {
		"search": {
			displayName: "search",
			description: "Search for contacts to associate with the software",
			index: 0,
			css: "search",
			callback: function() {
				this.hideFilter(false);
				this._model.get();
			}
		},
		"associated": {
			displayName: "associated",
			description: "View already associated contacts of the software",
			index: 1,
			css: "associated",
			callback: function() {
				this.hideFilter(true);
				this.render(this.views.peopleList.excludedDataItems.get());
			}
		},
		"selected": {
			displayName: "selected",
			description: "Preview selected contacts",
			index: 2,
			css: "selected",
			callback: function() {
				this.hideFilter(true);
				this.render(this.views.peopleList.selectedDataItems.get());
			}
		}
		
	}
});
appdb.components.RelatedEntities = appdb.ExtendClass(appdb.Component, "appdb.components.RelatedEntities", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		content: $.trim(o.content) || "software"
	};
	this.render = function(d,p,t){
        var v = this.views, qvalue = this._model.getQuery();
        var len = (d)?(typeof d.length==="number")?d.length:len:0;
        var ofs = (p)?(typeof p.pageoffset==="number")?p.pageoffset:ofs:0;
        $(this._dom).empty();
        v.appsList.render(d,{onRenderItem: this.onRenderItem});
        if(typeof len === "undefined"){
            if(ofs===0){
				v.pagerview.reset();
            }else{
                v.pagerview.render(p);
            }
            v.ordering.reset();
        } else if(len===0){
			v.pagerview.reset();
            v.ordering.reset();
        }else{
            v.pagerview.render(p);
			v.ordering.render(qvalue);
        }
		
        v.filtering.setWatermark(this.ext.filterDisplay);
        v.filtering.setValue(this.aggregateFilter.getUserFilter());
        v.filtering.render();
        
        if( $("#apps_orderby > div").length > 0 ) {
			$("#apps_orderby > div")[0].style.overflow= 'hidden';
		}
    };
	this.load = function(d){
		this.clearEvents();
		this.onRenderItem = null;
		this.currentData = d;
		d = d || {};
		this.isLoaded = true;
		this.navigationType = "";
        this.ext = d.ext || {};
        var v = this.views;
        var _base ;
		this.aggregateFilter.loadFilter( {ext: this.ext} );
        if(this.ext.mainTitle){
            this.setComponentTitle(this.ext.mainTitle);
        }
		if(this.ext.baseQuery && this.ext.baseQuery.flt){
			_base = $.extend({},this.ext.baseQuery);
			this._baseQuery = $.extend({},_base);
			
            if(d.query && d.query.flt){
				var po = d.query.pageoffset || 0;
				var pl = d.query.pagelength || optQueryLen;
                d.query =  $.extend({},_base);
				d.query.pagelength = d.query.pagelength || pl;
				d.query.pageoffset = d.query.pageoffset || po;
                if(this.ext.userQuery || this.ext.systemQuery){
                    d.query.flt = this.aggregateFilter.getFullQuery();
                }
            }
        }else if( !this.ext.systemQuery ){
			this._baseQuery = null;
			this.ext.userQuery = d.query;
        }
        this._apptype = "o.query.applicationtype";
        var query = d.query || {};
		query.orderby = query.orderby || 'rank';
		query.orderbyOp = query.orderbyOp || ( ($.trim(query.orderby)==='rank')?'DESC':'ASC' );
		if(this.views.ordering && this.views.ordering.getSelected()!==null){
		 if(typeof query.orderby === "undefined" && typeof query.orderbyOp === "undefined"){
			this.views.ordering.resetSelected();
		 }
		 this.views.ordering.setSelected(query.orderby,query.orderbyOp);
		}
		query.flt = query.flt || this.aggregateFilter.getFullQuery();
        switch(query.applicationtype){
			case "related":
				this._model = new appdb.model.RelatedApplications(query,this.ext);
				v.filtering.setWatermark(d.query.applicationtype);
				this.navigationType = "RelatedApps";
				break;
			case "moderated":
				this.navigationType = ($.trim(this.navigationType)==="")?"Moderated":this.navigationType;
			case "deleted":
                this._model = new appdb.model.Applications(query);
                v.filtering.setWatermark(d.query.applicationtype);
				this.navigationType = ($.trim(this.navigationType)==="")?"Deleted":this.navigationType;
			    break;
			case "followed":
				query.id = query.id || userID;
				query.flt = query.flt || "";
				this.navigationType = "Followed";
				this.onRenderItem = appdb.components.Applications.onRenderFollowedItem;
            case "bookmarked":
				this.navigationType = ($.trim(this.navigationType)==="")?"Bookmarked":this.navigationType;
            case "editable":
				this.navigationType = ($.trim(this.navigationType)==="")?"Editable":this.navigationType;
            case "associated":
				this.navigationType = ($.trim(this.navigationType)==="")?"Associated":this.navigationType;
            case "owned":
                this._model = new appdb.model.PeopleApplications(query,this.ext);
                v.filtering.setWatermark(d.query.applicationtype);
				this.navigationType = ($.trim(this.navigationType)==="")?"Owned":this.navigationType;
                break;
            default:
                this._model = new appdb.model.Applications(query);
                v.filtering.setWatermark(this.ext.filterDisplay);
                this._apptype = null;
				this.navigationType = this.ext.navigationType || "Applications";
				delete d.query.applicationtype;
                break;
        }
			
        this._model.subscribe({event:"beforeselect",callback:function(){
				this.views.loading.show();
		},caller:this});
        this._model.subscribe({event:"error",callback:function(err){
                this.views.loading.hide();
                this.ErrorHandler.handle({status:"Data transfer error",description:"An error occured during software list data transfer",source : err});
            },caller:this});
        v.pager = this._model.getPager().subscribe({event:'pageload',callback:function(d){
			this.render(d.data,d.pager,d.elapsed);
            this.views.loading.hide();
			var q = this._model.getQuery();
			if( $.trim(q.flt) ){
				q.flt = this.aggregateFilter.getFullQuery();
			}
			this.isLoaded = false;
            this.publish({event:'loaded',value:d});
			var uq = this.aggregateFilter.getUserFilter();
			if( uq ){
				this.views.filtering.setValue({flt: uq.value});
			}
        },caller : this});
		v.pagerview.subscribe({event:"next",callback : function(){
             this.views.pager.next();
        }, caller : this});
        v.pagerview.subscribe({event:"previous",callback : function(){
             this.views.pager.previous();
        }, caller : this});
        v.pagerview.subscribe({event:"current",callback : function(v){
			this.views.pager.current(v);
        }, caller : this});
        v.ordering.subscribe({event:"order",callback:function(v){
            this._model.get(v);
        },caller : this});
		v.filtering.subscribe({event:"filter",callback:function(v){
			this.aggregateFilter.loadUserFilter(v.flt);
			this.ext.userQuery = {flt: this.aggregateFilter.getUserQueryValue()};
            v.flt = this.aggregateFilter.getFullQuery();
            this._forceFiltering = false;
			this._model.get(v);
        },caller : this});
		v.appsList.subscribe({event:"itemclick",callback: function(data){
            this.publish({event: "select", value: data});
        },caller:this});
		this.views.pagerview.show();
		this.views.pager.current();
	};
	this._initContainer = function(){
		$(this.dom).empty();
		var header = $("<div class='listcontainer-header'></div>");
		var paging = $('<div class="entities_paging" id="#entities_paging" class="pager"></div>');
		var appslist = $('<ul className="itemgrid" class="itemgrid appmainlist" ></ul>');
		$(header).append('<div class="simpleFilter"><span  id="entity_filter" style="white-space: nowrap; margin-left: 0; margin-right: auto"></span></div>');
		$(header).append('<div class="orderby"><div id="entity_orderby" style="white-space: nowrap; vertical-align: middle; "></div></div>');
		$(header).append('<div class="entities_loading" class="loader"><img src="/images/ajax-loader-small.gif" alt="" width="12px" height="12px" /><span >Loading...</span></div>');
		$(this.dom).append(header).append(appslist).append(paging);
		
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._model = new appdb.model.Applications();
		this._initContainer();
		var v = {};
		v.appsList = new appdb.views.AppsList({container : $(this.dom).find("ul.appmainlist"),parent: this});
        v.pagerview = new appdb.views.MultiPagerPane({container: $(this.dom).find(".entities_paging"), parent: this});
		v.filtering = new appdb.views.Filter({
            container : "entity_filter",
            rich : true,
            watermark : this.ext.filterDisplay,
			parent: this
        });
		
        if(this.ext.userQuery){
            v.filtering.setValue(this.ext.userQuery);
        }
        v.ordering = new appdb.views.Ordering({
            container : "entity_orderby",
            selected : "rank",
            items : [
                {name : "Relevance" , value : "rank", defaultOperation:"DESC", hideOperation: true},
                {name : "Name" , value : "name", defaultOperation:"ASC"},
                {name : "Date Added" , value : "dateadded", defaultOperation:"DESC"},
                {name : "ID" , value : "id", defaultOperation:"ASC"},
                {name : "Rating", value : "rating", defaultOperation:"DESC"},
                {name : "Most visited", value: "hitcount", defaultOperation:"DESC"},
				{name : "Last updated", value: "lastupdated", defaultOperation:"DESC"},
                {name : "Unsorted" , value : "unsorted",hideOperation: true}
            ],
			parent: this
        });
		this.aggregateFilter = new appdb.utils.FilterAggregator({
			parent: this,
			entityType: "software"
		});
		v._export = new appdb.views.Export({container : $("#exportdiv"),target:'apps'});
		v.loading = new appdb.views.DelayedDisplay({selector: ".entities_loading", context: $(this.dom)});
		this.views = v;
	};
	this._init();
});
appdb.components.Person = appdb.ExtendClass(appdb.Component,"appdb.components.Person", function(o){
   this.parser = null;
   this.reset = function(){
	   $("#appdb_components_Person").find(".recordnotfound").remove();
       this.views.personInfo.hide();
       if(this.views.pubs){
           this.views.pubs.destroy();
       }
       if(this.views.personInfo){
           this.views.personInfo.destroy();
       }
   };
   this.startEditing = function(d){
        var i,len= this.views.personInfo.subviews.length,s;
        for(i=0;  i<len; i+=1){
            s = this.views.personInfo.subviews[i];
            if(s.startEdit){
                s.startEdit(d);
            }
        }
   };
   this.cancelEditing = function(d){
        var i,len= this.views.personInfo.subviews.length,s;
        for(i=0;  i<len; i+=1){
            s = this.views.personInfo.subviews[i];
            if(s.cancelEdit){
                s.cancelEdit(d);
            }
        }
   };
   this.render = function(d){
        this.reset();
		appdb.pages.Person.currentData(d);
		var start = new Date().getTime(), _this = this;
        if(d.publication && $.isArray(d.publication)===false){
            d.publication = [d.publication];
        }
		d.application = d.application || [];
		d.application = $.isArray(d.application)?d.application:[d.application];
        this.views.personInfo.render(d,function(){
            _this.views.personInfo.show();
			var tc = dijit.byId("personInformationTab");
            tc.resize();
        });
		this.views.pubs.render(d.publication);
		if($(".dijitTabContainer").length===0){
			try{dojo.parser.parse("ppl_details");}catch(e){}
		}
		
        $("#editPerson").click(function(){
            if($(this).text()==="edit"){
                _this.startEditing(d);
                $(this).text("cancel");
            }else{
                _this.cancelEditing(d);
                $(this).text("edit");
            }
        });
        var end = new Date().getTime();
        end = end - start;
        if ( $.trim(appdb.config.appenv) !== 'production' ) {
            $("#timer").text("Rendered in " + end + "ms");
        }
		this.views.permalink.render({query:this._model.getQuery(),ext:this.ext});
		$( "#ppl_details" ).tabs();
		$( "#ppl_details" ).bind( "tabsselect", function(event, ui) {
			if($.trim(ui.index)==='1'){
				$("#ppl_details_pubs").show();
				setTimeout(function(){
					if(dijit.byId("ppl_details_pubs")){
						$(dijit.byId("ppl_details_pubs").domNode).css({"height":"100%","width":"100%"});
								dijit.byId("ppl_details_pubs").resize();
					}
				},1);
			}
			if( $.trim(window.persontabselect) !== $.trim(ui.index)){
				window.persontabselect = ui.index;
				appdb.pages.Person.updateSection(ui.index);
				if($("#ppl_details").hasClass("editmode") === false ){
					window.persontabselect = (window.persontabselect>1)?0:window.persontabselect;
				}
			}
		});
        appdb.views.Main.setNavigationTitle(d.firstname+' '+d.lastname);
		$("#ppl_details #personTabContainer ul li").each(function(i,el){
			if(i===1 && d.gender && $.trim(d.gender) === 'robot'){
				$(el).addClass("hiddentab");
			}else{
				$(el).removeClass("hiddentab");
			}
		});
		d.group = d.group || [];
		d.group = $.isArray(d.group)?d.group:[d.group];
		var accessgroups = $.grep(d.group, (function(self){
			return function(e){
				return self.canDisplayGroup(e.id);
			};
		})(this));
		$("#ppl_details .accessgroupcontainer").removeClass("hasgroups");
		$("#ppl_details .accessgroupcontainer .accessgroups").empty();
		if( accessgroups.length > 0 ){
			var agvals = [];
			$.each(accessgroups, function(i,e){
				agvals.push("<span class='accessgroup' data-id='" + $.trim(e.id) + "' data-payload='" + $.trim(e.payload) + "'>" + e.val() + "</span>");
			});
			$("#ppl_details .accessgroupcontainer").addClass("hasgroups");
			$("#ppl_details .accessgroupcontainer .accessgroups").append( agvals.join("<span class='seperator'>,</span>") );
		}
		$(".nils.software").hide();
		setTimeout((function(self,country){
			return function(){
				var nil = self.inGroup(-3);
				if( nil !== false) { 
					$(".nils.software").show();
				}				
			};
		})(this,d.country.val()),100);
		if( typeof this.views.appslist === "undefined" ){
			this.views.appslist = new appdb.views.AppsList({container: $(this.dom).find("ul.itemgrid"), parent: this});
		}
		this.views.appslist.render(d.application);
		
		appdb.pages.Person.onPersonLoad();
		$(this.dom).find(".canzoom").removeClass("hover");
   };
   this.canDisplayGroup = function(id){
	   return ( $.inArray($.trim(id), ["-1","-2","-3","-8"]) > -1 );
   };
   this.inGroup = function(id, data){
	   var res = false;
	   var d = appdb.pages.Person.currentAccessGroups();
	   res = $.grep(d, function(e){
		   return ( $.trim(e.id) === $.trim(id) );
	   });
	   return (res.length===0)?false:res[0];
   };
   this.renderEmpty = function(){
	var html = '<div class="recordnotfound emptycontent"><div class="content"><img alt="" src="/images/error.png" /><span>Record not found or removed.</span></div></div>';
    this.reset();
	$("#appdb_components_Person").prepend(html);
	appdb.views.Main.clearNavigation();
	appdb.pages.Person.onPersonLoad();
   };
   this.load = function(o){
	   this.ext = o.ext || this.ext;
        this._model = new appdb.model.Person(o.query);
		this._model.subscribe({event: "beforeselect", callback: function(e){
				$("#ppl_details img.personimage").attr("src","/images/ajax-loader-small.gif");
		}, caller: this});
        this._model.subscribe({event:"select",callback:function(e){
                this.publish({event:'loaded',value:{}});
				if(e && e.id){
					e.vo = e.vo || [];
					e.vo = ($.isArray(e.vo)?e.vo:[e.vo]);
					e.group = e.group || [];
					e.group = $.isArray(e.group)?e.group:[e.group];
					$.each(e.group, function(i, ee){
						if( ee.val ){
							ee.name = ee.val();
						}
					});
					appdb.pages.Person.init({
						id: e.id,
						firstName: e.firstname,
						lastName: e.lastname,
						cname: e.cname,
						countryid: e.country.id,
						countryname: e.country.val(),
						role:{
							id: e.role.id,
							description: e.role.type,
							validated: e.role.validated 
						},
						groups: e.group,
						vomembership: e.vo
					});	
				}
				if( e ){
					this.render(e);
				}else{
					this.renderEmpty(e);
				}
                
            },caller:this}).get();
        this._model.subscribe({event:"error",callback:function(e){
            console.log("error at person loading of data",e);
        }});
   };
   this._init = function(){
       var v = {};
       v.personInfo = o.templatepane;
	   this.dom = $(o.container);
       $(o.container).append(v.personInfo.content);
	   v.personInfo.hide();
       v.pubs = new appdb.views.Publications({container: "ppl_details_pubs"});
	   v.permalink = new appdb.views.Permalink({container: $("<span></span>"),datatype : "person"});//used to set appdb.config.permalink, not to be rendered
	   $(this.dom).find("ul.itemgrid").context = $(this.dom).find("ul.itemgrid");
	   this.views = v;
   };
   this._init();
},{
    setPrimaryContact : function(cid,elem){
        if(typeof cid === "undefined" || $.trim(cid)===''){
            return;
        }
        var model = new appdb.model.PrimaryContact();
        if(elem){
            model.subscribe({event : "beforeupdate", callback : function(){
                $(elem).empty().append('<img src="/images/ajax-loader-small.gif" alt="" width="12px" height="12px"  /><span >updating...</span>');
            }});
            model.subscribe({event : "update",callback: function(v){
                if(v.error){
                    appdb.model.PrimaryContact.userPrimaryContact = null;
                    appdb.model.PrimaryContact.userPrimaryContactId = null;
                    $(elem).empty().append("<span>Could not set primary contact. " + v.error + "</span>");
                }else{
                     appdb.model.PrimaryContact.userPrimaryContact = v.val();
                     appdb.model.PrimaryContact.userPrimaryContactId = v.id;
                    $(elem).empty().append("<span>Success</span>");
                }
            }});
            model.subscribe({event : "error", callback : function(){
                $(elem).empty().append("<span>Could not set the primary contact.</span>");
            }});
        }
        model.update({id:cid});
    },
    getPrimaryContact : function(clbck){
         var model = new appdb.model.PrimaryContact();
         model.subscribe({event : "select", callback : function(v){
                 if(v.error){
                    appdb.model.PrimaryContact.userPrimaryContact = null;
                    appdb.model.PrimaryContact.userPrimaryContactId = null;
                 }else{
                     appdb.model.PrimaryContact.userPrimaryContact = v.val();
                     appdb.model.PrimaryContact.userPrimaryContactId = v.id;
                 }
                 if(clbck){
                     clbck(v);
                 }
             }});
         model.get();
    },
    setDefaultNotification : function(enable,args,clbck){
        var model = new appdb.model.MailSubscription();
        model.subscribe({event : "remove", callback : clbck});
        model.subscribe({event : "insert", callback : function(v){
            if(clbck){clbck(v);}
            appdb.model.MailSubscription.defaultNotification = v;
        }});
        model.subscribe({event : "update", callback : clbck});
        model.subscribe({event : "error", callback : clbck});
        if(args && args.flt){
            args.flt =  appdb.utils.base64.encode(args.flt);
        }
        if(typeof enable==="undefined"){
            model.update(args);
        }else if(enable){
            model.insert(args);
        }else{
            model.remove(args);
        }
    },
    getDefaultNotification : function(clbck,args){
        args = appdb.utils.base64.encode(args);
        var model = new appdb.model.MailSubscription();
        model.subscribe({event : "select", callback : function(v){appdb.model.MailSubscription.defaultNotification = v;clbck(v);}});
        model.subscribe({event : "error", callback : clbck});
        model.get({flt:args || '',subjecttype:"app"});
    },
    setRoleNotification : function(enable,args,clbck){
        var model = new appdb.model.RoleMailSubscription();
        model.subscribe({event : "remove", callback : clbck});
        model.subscribe({event : "insert", callback : function(v){
            if(clbck){clbck(v);}
            appdb.model.MailSubscription.roleNotification = v;
        }});
        model.subscribe({event : "error", callback : clbck});
        if(enable){
            model.insert(args);
        }else{
            model.remove(args);
        }
    },
    getRoleNotification : function(clbck,args){
        args = appdb.utils.base64.encode(args);
        var model = new appdb.model.RoleMailSubscription();
        model.subscribe({event : "select", callback : function(v) {appdb.model.MailSubscription.roleNotification = v;clbck(v);}});
        model.subscribe({event : "error", callback : clbck});
        model.get(args);
    },
    setNoDissemination : function(enable,args,clbck){
        var model = new appdb.model.NoDisseminationSubscription();
        model.subscribe({event : "remove", callback : clbck});
        model.subscribe({event : "insert", callback : function(v){
            if(clbck){clbck(v);}
            appdb.model.MailSubscription.noDissemination = v;
        }});
        model.subscribe({event : "error", callback : clbck});
        if(enable){
            model.insert(args);
        }else{
            model.remove(args);
        }
    },
    getNoDissemination: function(clbck,args){
        args = appdb.utils.base64.encode(args);
        var model = new appdb.model.NoDisseminationSubscription();
        model.subscribe({event : "select", callback : function(v) {appdb.model.MailSubscription.noDissemination = v;clbck(v);}});
        model.subscribe({event : "error", callback : clbck});
        model.get(args);
    },
    setOwnedNotification: function(enable,args,clbck){
     var model = new appdb.model.MailSubscription();
     model.subscribe({event : "remove", callback : clbck});
     model.subscribe({event : "update", callback : function(v){
         if(clbck){clbck(v);}
         appdb.model.MailSubscription.ownedNotification.delivery = args.delivery;
     }});
     model.subscribe({event : "insert", callback : function(v){
         if(clbck){clbck(v);}
         appdb.model.MailSubscription.ownedNotification = v;
     }});
     model.subscribe({event : "error", callback : clbck});
     if(enable){
      args = {name:"Owned software subscription",subjecttype:"app",events:31,delivery:args.delivery||4,flt:appdb.utils.base64.encode("=application.owner:"+userID+" id:SYSTAG_OWNER")};
      if(appdb.model.MailSubscription.ownedNotification){
       args.id = appdb.model.MailSubscription.ownedNotification.id;
       model.update(args);
      }else{
       model.insert(args);
      }
     }else{
         if(appdb.model.MailSubscription.ownedNotification){
          model.remove({
           id:appdb.model.MailSubscription.ownedNotification.id,
           pwd:appdb.model.MailSubscription.ownedNotification.unsubscribe_pwd
          });
         }
     }
    },
    getOwnedNotification: function(clbck){
     var args = appdb.utils.base64.encode("=application.owner:"+userID+" id:SYSTAG_OWNER");
     var model = new appdb.model.MailSubscription();
     model.subscribe({event : "select", callback : function(v){
       appdb.model.MailSubscription.ownedNotification = v;
       clbck(v);
      }});
     model.subscribe({event : "error", callback : clbck});
     model.get({flt:args || '',subjecttype:"app"});
    },
    setRelatedNotification: function(enable,args,clbck){
     var model = new appdb.model.MailSubscription();
     model.subscribe({event : "remove", callback : clbck});
     model.subscribe({event : "update", callback : function(v){
         if(clbck){clbck(v);}
         appdb.model.MailSubscription.relatedNotification.delivery = args.delivery;
     }});
     model.subscribe({event : "insert", callback : function(v){
         if(clbck){clbck(v);}
         appdb.model.MailSubscription.relatedNotification = v;
     }});
     model.subscribe({event : "error", callback : clbck});
     if(enable){
      args = {name:"Related software subscription",subjecttype:"app",events:31,delivery:args.delivery||4,flt:appdb.utils.base64.encode("=person.id:"+userID+" id:SYSTAG_RELATED")};
      if(appdb.model.MailSubscription.relatedNotification){
       args.id = appdb.model.MailSubscription.relatedNotification.id;
       model.update(args);
      }else{
       model.insert(args);
      }
     }else{
         if(appdb.model.MailSubscription.relatedNotification){
          model.remove({
           id:appdb.model.MailSubscription.relatedNotification.id,
           pwd:appdb.model.MailSubscription.relatedNotification.unsubscribe_pwd
          });
         }
     }
    },
    getRelatedNotification: function(clbck){
     var args = appdb.utils.base64.encode("=person.id:"+userID+" id:SYSTAG_RELATED");
     var model = new appdb.model.MailSubscription();
     model.subscribe({event : "select", callback : function(v){
       appdb.model.MailSubscription.relatedNotification = v;
       clbck(v);
      }});
     model.subscribe({event : "error", callback : clbck});
     model.get({flt:args || '',subjecttype:"app"});
    },
    setInboxNotification: function(enable,args,clbck){
     var model = new appdb.model.MailSubscription();
     model.subscribe({event : "remove", callback : clbck});
     model.subscribe({event : "insert", callback : function(v){
         if(clbck){clbck(v);}
         appdb.model.MailSubscription.inboxNotification = v;
     }});
     model.subscribe({event : "error", callback : clbck});
     if(enable){
      args = {name:"New inbox message",subjecttype:"inbox",events:31,delivery:2,flt:appdb.utils.base64.encode("id:SYSTAG_INBOX")};
      model.insert(args);
     }else{
         if(appdb.model.MailSubscription.inboxNotification){
          model.remove({
           id:appdb.model.MailSubscription.inboxNotification.id,
           pwd:appdb.model.MailSubscription.inboxNotification.unsubscribe_pwd
          });
		 }
         
     }
    },
    getInboxNotification: function(clbck){
     var args = appdb.utils.base64.encode("id:SYSTAG_INBOX");
     var model = new appdb.model.MailSubscription();
     model.subscribe({event : "select", callback : function(v){
       appdb.model.MailSubscription.inboxNotification = v;
       clbck(v);
      }});	
     model.subscribe({event : "error", callback : clbck});
     model.get({flt:args || '',subjecttype:"inbox"});
    }
});

appdb.components.RatingReport = appdb.ExtendClass(appdb.Component,"appdb.components.RatingReport",function(o){
    o = o || {};
    this.ext = o.ext || {};
    this.reset = function(){
        this.views.chart.reset();
        $(this.dom).empty();
        this._initContainer();
    };
    this.render = function(d){
        this.views.chart.render(d.rating);
		this.views.average.render(d);
    };
    this.destroy = function(){
        this.reset();
        this._model.destroy();
        this.views.chart.destroy();
    };
    this.load = function(q){
        q.query = q.query || q;
        q.query.type = q.query.type || "both";
		if( this._model !== null ){
			if(this._model.unsubscribeAll) this._model.unsubscribeAll();
			if(this._model.destroy) this._model.destroy();
			if( this._model.getXhr) {
				var x = this._model.getXhr();
				if( x && typeof x.abort === "function"){
					x.abort();
				}
			}
		}
        this._model = new appdb.model.RatingReport(q.query);
		this.views.loading.show();
		this._model.subscribe({event:"select",callback:function(e){
                this.publish({event:'loaded',value:{}});
                this.render(e.ratingreport);
				this.views.loading.hide();
            },caller:this}).get();
		appdb.pages.application.requests.register(this._model,"ratingreport");
        this._model.subscribe({event:"error",callback:function(e){
			this.views.loading.hide();
        },caller: this});
    };
    this._appendMenu = function(type){
        var li = $(document.createElement("li"));
        var a = $(document.createElement("a"));
        a.attr("href","#").click(
            (function(t,mq){
                return function(){
                    var q = t._model.getQuery();
                    q.type = mq;
                    t.load({query:q});
                    $(t.dom).find("li").removeClass("selected");
                    $(li).addClass("selected");
                };
            })(this,type));
            var txt = "both";
        switch(type){
            case "external":
                txt = "anonymous";
                break;
            case "internal":
                txt = "authenticated";
                break;
            default:
                txt = "all";
                break;
        }
        $(a).text(txt);
        $(li).append($(a));
        if(type==="both"){
            $(li).addClass("selected");
        }
        return $(li);
    };
	this._appendDecoration = function(){
		var li = $(document.createElement("li"));
		$(li).addClass("ratingreportmenu-decoration").html("<span>&bull;</span>");
		return $(li);
	};
    this._initContainer = function(){
        var div = $(document.createElement("div"));
        var ul = $(document.createElement("ul"));
        $(div).addClass("ratingreportmenu ");
        $(div).append("<span class='ratingreportmenu-title'>View : </span>");
        $(ul).attr("id","reportchartmenu");
        $(ul).append(this._appendMenu("both"));
		$(ul).append(this._appendDecoration());
        $(ul).append(this._appendMenu("internal",true));
        $(ul).append(this._appendDecoration());
		$(ul).append(this._appendMenu("external",true));
        $(div).append($(ul));
		$(this.dom).append("<div id='ratingreportloading' class='ratingreport-loading'></div>");
        $(this.dom).append($(div));
        $(this.dom).append('<center><div id="ratingChart" class="ratingchart" ></div><div id="ratingChartAverage" ></div></center>');
		$(this.dom).append($("<div style='width:100%;height:10px;' ></div><hr style='width: 100%;' /><div style='width:100%;height:8px' ></div>"));
    };
    this._init = function(){
        var v = {};
        if(typeof o.container==="string"){
            this.dom = $(o.container);
        }else{
            this.dom = o.container;
        }
        this._initContainer();
        v.chart = new appdb.views.RatingChart({container : $("#ratingChart")});
		v.average = new appdb.views.RatingAverage({container:$("#ratingChartAverage")});
		v.loading = new appdb.views.DelayedDisplay({selector : "#ratingreportloading",usedefault:true,delay:20});
        this.views = v;
    };
    this._init();
});
appdb.components.ReportAbuse = appdb.ExtendClass(appdb.Component, "appdb.components.ReportAbuse",function(o){
	o = o || {};
	this.ext = o.ext || {};
	this.dom = null;
	this.dialog = null;
	this.events = [];
	this.inited = false;
	this.preInitQueue = [];
	this.reset = function(){
		dojo.forEach(this.events,dojo.disconnect);
		if(this.dialog){
			this.dialog.destroyRecursive(false);
		}
	};
	this.submit = function(d){
		var _this = this;
		$.ajax({
			url : "abuse/submit",
			data : d,
			async : false,
			success : function(){
				_this.dialog.hide();
			},
			error : function(e){
				_this.ErrorHandler.handle({status:"Report error",description:"Report failed to be submitted."});
			}
		});
	};
	this.validateData = function(d){
		var err = null;
		if(d.reason===-1){
			err = "Please select the reason of your report";
		}else if(d.comment===""){
			err = "Please provide a description for your report";
		}else if(d.submitterId===null){
			err = "Only authedicated users can submit a report. Please login and try again";
		}
		if(err!==null){
			this.ErrorHandler.handle({status:"Your report is not valid",description:err,source : null});
			return false;
		}
		return true;
	};
	this.collectData = function(){
		var res = {entryID:((this.ext.type==="application")?this.ext.id:this.ext.commentId),reason:null,comment:"",submitterId:userID,type : this.ext.type};
		res.comment = dijit.byId("reportabuse_comment",this.dialog).getValue();
		res.comment = $.trim(res.comment);
		var r = $(this.dialog.domNode).find("input[name='reportabuse_type_"+this.ext.type+"']:checked");
		res.reason = r.val() || -1;
		return res;
	};
	this.render = function(d){
		if(this.inited===false){
			this.preInitQueue.push({caller:this.render,args:arguments});
			return;
		}
		this.reset();
		this.ext.type = d.type || appdb.components.ReportAbuse.type;
		this.ext.id = d.id || -1;
		this.ext.title = d.title || appdb.components.ReportAbuse.contents[this.ext.type].title;
		this.ext.description = d.description || appdb.components.ReportAbuse.contents[this.ext.type].description;
		if(d.name && d.name.length>=20){
			d.name = d.name.substr(0,17) + "...";
		}
		var title = this.ext.title; 
		if(typeof d.type === "string"){
			switch(d.type){
				case "application":
					title += " (Software: " + d.id + " / "+d.name+")";
					break;
				case "comment":
					this.ext.commentId = d.commentId;
					title += "(Software: " + d.id + " / " + d.name + ", Comment: " + d.commentId + ")";
					break;
			}
		}
		$(this.dom).find("#reportabuse_description").html(this.ext.description);
		$(this.dom).find(".reportabuse-types").css({display:"none"});
		$(this.dom).find("#reportabuse_types_"+this.ext.type+"").first().css({display:"block"});
		
		$(this.dom).find("#reportabuse_title").hide();
		this.dialog = new dijit.Dialog({
			title : title,
			content : $(this.dom).html(),
			style : "width:600px;"
		});
		this.dialog.show();
		this.dialog.resize();
		//set title icon
		dojo.query(".dijitDialogTitle",this.dialog.domNode)[0].innerHTML = '<img style="vertical-align: middle; padding-right: 3px;" border="0" src="/images/stop.png" width="16px"/>'+title;
		this.events[this.events.length] = dojo.connect(dijit.byId("reportabuse_cancel"),'onClick',this,(function(t){
			return function(){
				t.dialog.hide();
			};
		})(this),this);
		this.events[this.events.length] = dojo.connect(dijit.byId("reportabuse_submit"),'onClick',this,(function(t){
			return function(){
				var data = t.collectData();
				if(t.validateData(data)){
					t.submit(data);
				}
			};
		})(this),this);
	};
	this.load = function(q){
		q = q || {};
		this.ext = (q.ext)?$.extend(this.ext,q.ext):$.extend(this.ext,q);
		
	};
	this._init = function(){
		var _subinit = (function(t){
			return function(container){
				if(typeof container==="undefined"){
					t.dom = $(document.createElement("div"));
				}else if(typeof container==="string"){
					t.dom = $(container);
				}else{
					t.dom = container;
				}
				
				t.inited = true;
				for(var i=0; i<t.preInitQueue.length; i+=1){
					t.preInitQueue[i].caller.apply(t,t.preInitQueue[i].args);
				}
			};})(this);
		appdb.ViewLoader.getComponentView(this._type_.getName(),{
			success : function(d){
				_subinit($(d.cache));
			},
			error : function(){
				_subinit();
			}
		});
	};
	this._init();
},{
	description : "",
	title : "Report a problem",
	type : "application",
	contents : {
		application : {
			title : "Report a problem on the content ",
			description : "You are about to report a problem about the contents of this software. Please select a reason and provide a description of the problem. After the submition a report will be sent to be reviewed for validity and further processing."
		},
		comment : {
			title : "Report abuse ",
			description : "You are about to report a problem about a user comment on this software. Please select a reason and provide a description of the problem. After the submition a report will be sent to be reviewed for validity and further processing."
		}
	}
});

appdb.components.ContactVOs = appdb.ExtendClass(appdb.Component, "appdb.components.ContactVOs", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		id: ($.trim(o.id)===""?appdb.pages.Application.currentId():o.id),
		selection : {notificationtype: "", vos:[], subject:"", message:""},
		enableModes: o.enableModes,
		availableModes: [],
		state: "init",
		currentMode: "",
		loadedForm: ""
	};
	this.getMessage = function(){
		var message = "";
		if( this.views.message && typeof this.views.message.get === "function" ){
			message = $.trim( this.views.message.get("displayedValue") );
		}
		return message;
	};
	this.getSubject = function(){
		var subject = "";
		if( this.views.subject && typeof this.views.subject.get === "function" ){
			subject = $.trim( this.views.subject.get("displayedValue") );
		}
		return subject;
	};
	this.getVOs = function(){
		var vos = [];
		$(this.dom).find(".selectedvocontacts .contactitem").each((function(self){
			return function(i, e){
				var id = ( $.trim( $(e).data("id") ) << 0 );
				if( id > 0 ){
					vos.push( id );
				}
			};
		})(this));
		return vos;
	};
	this.getNotificationType = function(){
		var curMode = $.trim(this.options.currentMode).toLowerCase();
		if( $.inArray( curMode, ["init",""]) >= 0 ) {
			return "";
		}
		return curMode;
	};
	this.renderLoading = function(enable, text, classes){
		enable = (typeof enable === "boolean" )?enable:false;
		classes = $.trim(classes);
		text = $.trim(text) || "Loading";
		text = "..." + text;
		$(this.dom).children(".actionloader").remove();
		if( enable ){
			$(this.dom).append("<div class='actionloader "+classes+"'><div class='shader'/><div class='message icontext'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>"+text+"</span></div></div>");
		}
	};
	this.checkExclusion = function(){
		var data = appdb.pages.application.currentData();
		data.application = data.application || {};
		data.application.vo = data.application.vo || [];
		data.application.vo = $.isArray(data.application.vo)?data.application.vo :[data.application.vo];
		var canExclude = ( data.application.vo.length > 0 );
		if( canExclude ){
			$(this.dom).find(".selectcontacttype-container .value ul li[data-action='exclude']").removeClass("hidden");
		}else{
			$(this.dom).find(".selectcontacttype-container .value ul li[data-action='exclude']").addClass("hidden");
		}
		
	};
	this.show = function(){
		if(appdb.components.ContactVOs.Dialog!=null){
			appdb.components.ContactVOs.Dialog.hide();
			appdb.components.ContactVOs.Dialog.destroyRecursive(false);
		}
		appdb.components.ContactVOs.Dialog = new dijit.Dialog({
			title: appdb.components.ContactVOs.Title + " " + appdb.pages.application.currentName(),
			content: $(this.dom)[0],
			onCancel: (function(self){ 
				return function(){
					self.close();
				};
			})(this)
		});
		appdb.components.ContactVOs.Dialog.show();	
	};
	this.hide = function(){
		if(appdb.components.ContactVOs.Dialog !== null){
			appdb.components.ContactVOs.Dialog.hide();
			appdb.components.ContactVOs.Dialog.destroyRecursive(false);
			appdb.components.ContactVOs.Dialog = null;
		}
	};
	this.close = function(){
		this.unselectAll();
		this.options.selection = {notificationtype: "", vos:[], subject:"", message:""};
		this.hide();
	};
	this.checkSelectedVOs = function(){
		var vos = this.getVOs();
		var isvalid = false;
		if( vos.length === 0 ){
			$(this.dom).find(".contactvos-container").addClass("nocontactvo");
			$(this.dom).find(".contactvos-container .selectedvocontacts").addClass("dijitError");
			isvalid = false;
		}else{
			$(this.dom).find(".contactvos-container").removeClass("nocontactvo");
			$(this.dom).find(".contactvos-container .selectedvocontacts").removeClass("dijitError");
			isvalid = true;
		}
		if( $(this.dom).find(".selectedvocontacts ul > li > .contactitem").length > 0 ){
			if( $(this.dom).find(".selectedvocontacts ul > li.selectvo").length === 0 ){
				var li = $("<li class='selectvo'></li>");
				$(li).html($(this.dom).find(".selectedvocontacts span.selectvo").html());
				$(this.dom).find(".selectedvocontacts ul").append(li);	
			}
			$(this.dom).find(".selectedvocontacts > .selectvo").addClass("hidden");
		}else{
			$(this.dom).find(".selectedvocontacts ul > li.selectvo").remove();
			$(this.dom).find(".selectedvocontacts span.selectvo").removeClass("hidden");
		}
		return isvalid;
	};
	this.validateNotificationType = function(){
		return (this.getNotificationType()==="")?false:true;
	};
	this.validateMessage = function(){
		var isvalid = false;
		var text = this.getMessage();
		if( text === "" ) {
			isvalid = true;
		}
		
		var maxcount = $(this.dom).find(".message-container").data("maxcount");
		if( (maxcount << 0) <= 1 ){
			isvalid = true;
		}
		
		var validator = $(this.dom).find(".message-container .validationmessage");
		$(validator).find(".currentcount").text(text.length);
		$(validator).find(".maxcount").text(maxcount);
		if( this.options.currentMode === "generic" && text.length === 0){
			$(this.dom).find(".message-container").addClass("invalid");
			$(this.dom).find(".message-container .dijitTextBox").addClass("dijitError");
			isvalid = false;
		}else if( text.length === 0 || text.length < maxcount ){
			$(this.dom).find(".message-container").removeClass("invalid");
			$(this.dom).find(".message-container .dijitTextBox").removeClass("dijitError");
			isvalid = true;
		}else{
			$(this.dom).find(".message-container").addClass("invalid");
			$(this.dom).find(".message-container .dijitTextBox").addClass("dijitError");
			isvalid = false;
		}
		if( isvalid ){
			$(this.dom).find(".contactvos-container").removeClass("nomessage");
		}else{
			$(this.dom).find(".contactvos-container").addClass("nomessage");
		}
		return isvalid;
	};
	this.validateSubject = function(){
		var isvalid = false;
		var text = this.getSubject();
		if( text === "" ) {
			isvalid = true;
		}
		
		var maxcount = $(this.dom).find(".subject-container").data("maxcount");
		if( (maxcount << 0) <= 1 ){
			isvalid = true;
		}
		
		var validator = $(this.dom).find(".subject-container .validationmessage");
		$(validator).find(".currentcount").text(text.length);
		$(validator).find(".maxcount").text(maxcount);
		if( this.options.currentMode === "generic" && text.length === 0){
			$(this.dom).find(".subject-container").addClass("invalid");
			$(this.dom).find(".subject-container .dijitTextBox").addClass("dijitError");
			isvalid = false;
		}else if( text.length === 0 || text.length < maxcount ){
			$(this.dom).find(".subject-container").removeClass("invalid");
			$(this.dom).find(".subject-container .dijitTextBox").removeClass("dijitError");
			isvalid = true;
		}else{
			$(this.dom).find(".subject-container").addClass("invalid");
			$(this.dom).find(".subject-container .dijitTextBox").addClass("dijitError");
			isvalid = false;	
		}
		if( isvalid ){
			$(this.dom).find(".contactvos-container").removeClass("nosubject");
		}else{
			$(this.dom).find(".contactvos-container").addClass("nosubject");
		}
		return isvalid;
	};
	this.validate = function(){
		this.checkExclusion();
		var validnotification = this.validateNotificationType();
		var validselvos = this.checkSelectedVOs(); 
		var validmessage = this.validateMessage();
		var validsubject = this.validateSubject();
		var valid =  validnotification && validselvos &&  validmessage && validsubject;
		
		this.options.selection.notificationtype = ( validnotification === true )?this.getNotificationType():"";
		this.options.selection.vos = ( validselvos === true )?this.getVOs():[];
		this.options.selection.message = ( validmessage === true )?this.getMessage():"";
		this.options.selection.subject = ( validsubject === true )?this.getSubject():"";
		
		if( !valid || this.options.mode === "init"){
			$(this.dom).find(".footer .action.send").unbind("click").removeClass("btn-primary").addClass("btn-disabled").attr("disabled", "disabled");
			$(this.dom).find(".footer .action.preview").addClass("hidden");
			return false;
		}
		$(this.dom).find(".footer .action.send").addClass("btn-primary").removeClass("btn-disabled").removeAttr("disabled").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.sendMessage();
				return false;
			};
		})(this));
		$(this.dom).find(".footer .action.preview").removeClass("hidden");
		return true;
	};
	this.render = function(contacttype){
		$(this.dom).empty().append(this.options.loadedForm);
		$(this.dom).find(".dijitError").removeClass("dijitError");
		this.setupContactTypes();
		if( this.views.subject ){
			this.views.subject.destroyRecursive(false);
			this.views.subject = null;
			$(this.dom).find(".subject-container .value").empty().append("<div />");
		}
		if( this.views.message ){
			this.views.message.destroyRecursive(false);
			this.views.message = null;
			$(this.dom).find(".message-container .value").empty().append("<div />");
		}
		
		this.views.subject = new dijit.form.TextBox({
			id:"vo_subject_" + this.options.id, 
			name: "usersubject", 
			value:"", 
			placeHolder:"Type a subject",
			style: "width:665px;",
			onChange: (function(self){
				return function(v){
					self.validate(v);
				};
			})(this),
			onKeyUp: (function(self){
				return function(v){
					self.validate(v);
				};
			})(this)
		},$(this.dom).find(".subject-container .value > div")[0]);
		this.views.message = new dijit.form.SimpleTextarea({
			id:"vo_message_" + this.options.id, 
			name:"usermessage", 
			value: "", 
			placeHolder:"Type your messsage here",
			style: "width:665px; height:360px;min-height:365px;max-height:365px;",
			onChange: (function(self){
				return function(v){
					self.validate();
				};
			})(this),
			onKeyUp: (function(self){
				return function(v){
					self.validate();
				};
			})(this)
		},$(this.dom).find(".message-container .value > div")[0]);
		this.setMode("init");
		$(this.dom).find(".vocontactlist ul > li .contactitem").each((function(self){
			return function(i, e){
				var chk = $("<input type='checkbox' />");
				$(e).prepend(chk);
			};
		})(this));
		$(this.dom).find(".vocontactlist ul > li .contactitem").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.stopPropagation();
				if( $(this).find("input").is(":checked") === false ){
					self.selectItem($(this).data("id"));
				}else {
					self.unselectItem($(this).data("id"));
				}
				return true;
			};
		})(this));
		$(this.dom).find(".vocontactlist ul > li .contactitem input").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.stopPropagation();				
				if( $(this).is(":checked") ){
					self.selectItem($(this).parent().data("id"));
				} else {
					self.unselectItem($(this).parent().data("id"));
				}
			};	
		})(this));
		
		this.initActions();
		if( this.options.availableModes.length === 1 ){
			this.selectContactType(this.options.availableModes[0]);
		}else if( $.trim(contacttype) !== "" ){
			this.selectContactType(contacttype);
		}
		this.validate();
	};
	this.setupContactTypes = function(){
		var availablemodes = [];
		$(this.dom).find(".selectcontacttype-container ul > li").each(function(i,e){
			availablemodes.push($(e).data("action"));
		});
		if( typeof this.options.enableModes === "boolean" && this.options.enableModes === false){
			availablemodes = ["generic"];
		} else if ( typeof this.options.enableModes === "undefined" || this.options.enableModes === null || this.options.enableModes === true){
			$(this.dom).find(".selectcontacttype-container ul > li").each(function(i, e){
				var action = $.trim($(e).data("action"));
				availablemodes.push(action);
			});
		} else {
			this.options.enableModes = this.options.enableModes || [];
			this.options.enableModes = $.isArray(this.options.enableModes)?this.options.enableModes:[this.options.enableModes];
			if( this.options.enableModes.length > 0 ){
				availablemodes = this.options.enableModes;
			}
		}
		$.unique(availablemodes);
		if( availablemodes.length > 1 && $.inArray("init",availablemodes) === -1){
			availablemodes.push("init");
		}
		var toberemoved = [];
		$(this.dom).find(".selectcontacttype-container ul > li").each(function(i, e){
			var action = $.trim($(e).data("action"));
			if( action !== "init" && $.inArray(action, availablemodes) < 0 ){
				toberemoved.push(e);
			}
		});
		$.each(toberemoved, function(i,e){
			$(e).remove();
		});
		this.options.availableModes = availablemodes;
	};
	this.selectItem = function(id){
		if( $(this.dom).find(".selectedvocontacts ul > li .contactitem[data-id='" + id + "']").length > 0 ){
			return;
		}
		var item = $(this.dom).find(".vocontactlist ul > li .contactitem[data-id='" + id + "']");
		if( $(item).length === 0 ){
			return;
		}
		var li = $("<li></li>");
		var additem = $(item).clone();
		var actionclose = $("<span class='action close' title='Remove vo from selected contacts'><span>x</span></span>");
		$(actionclose).unbind("click").bind("click", (function(self, itemid){
			return function(ev){
				ev.preventDefault();
				self.unselectItem(itemid);
				return false;
			};
		})(this,id));
		$(additem).find(".selection, input").remove();
		$(additem).append(actionclose);
		
		$(li).append(additem);
		$(this.dom).find(".selectedvocontacts ul").append(li);
		$(this.dom).find(".vocontactlist ul > li .contactitem[data-id='"+id+"'] input").each(function(i,e){
			$(this).closest("li").addClass("selected");
			$(this).attr("checked", true);
		});
		this.validate();
		this.updateFilters();
	};
	
	this.unselectItem = function(id){
		$(this.dom).find(".selectedvocontacts ul > li .contactitem[data-id='" + id + "']").parent().remove();
		$(this.dom).find(".vocontactlist ul > li .contactitem[data-id='"+id+"'] input").each(function(i,e){
			$(this).closest("li").removeClass("selected");
			$(this).attr("checked", false);
		});
		this.validate();
		this.updateFilters();
	};
	this.selectContactType = function(contacttype){
		contacttype =  $.trim(contacttype) || "";
		if( contacttype === "" ){
			return ;
		}
		var ctype = $(this.dom).find(".selectcontacttype-container ul > li[data-action='" + contacttype + "']");
		if( $(ctype).length === 0 ){
			return;
		}
		var selected = $(this.dom).find(".selectcontacttype-container .selectedcontacttype");
		$(selected).empty().append( $(ctype).children(".contacttype").clone() );
		if( contacttype !== this.options.currentMode ){
			this.setMode(contacttype);
		}
		$(ctype).closest(".dropdownoptions-container").removeClass("active");
	};
	this.selectAll = function(){
		$(this.dom).find(".vocontactlist > ul > li").each((function(self){
			return function(i, e){
				if( $(e).hasClass("filteredout") === false ){
					self.selectItem($(e).children(".contactitem").data("id"));
				}
			};
		})(this));
		this.updateFilters();
	};
	this.clearAll = function(){
		$(this.dom).find(".vocontactlist > ul > li").each((function(self){
			return function(i, e){
				if( $(e).hasClass("filteredout") === false ){
					self.unselectItem($(e).children(".contactitem").data("id"));
				}
			};
		})(this));
		this.updateFilters();
	};
	this.initMenus = function(){
		$(this.dom).find(".dropdownoptions-container").removeClass("active");
		$(this.dom).unbind("click").bind("click", function(ev){
			$(this).find(".dropdownoptions-container").removeClass("active");
		});
		$(this.dom).find(".dropdownoptions-handler").unbind("click").bind("click",(function(self){
			return function(ev){
				ev.preventDefault();
				var isactive = $(this).closest(".dropdownoptions-container").hasClass("active");
				$(self.dom).find(".dropdownoptions-container").removeClass("active");
				if( !isactive ){
					$(this).closest(".dropdownoptions-container").addClass("active");
				}
				return false;
			};
		})(this));
	};
	
	this.unselectAll = function(){
		$(this.dom).find(".selectedvocontacts ul > li .contactitem").parent().remove();
		$(this.dom).find(".vocontactlist ul > li .contactitem input").each(function(i,e){
			$(this).closest("li").removeClass("selected");
			$(this).attr("checked", false);
		});
		this.validate();
		this.updateFilters();
	};
	this.getFilterItems = function(filter){
		filter = ($.trim(filter) || "none").toLowerCase();
		
		switch(filter){
			case "selected":
				return $(this.dom).find(".vocontactlist ul > li.selected");
			case "endorsed":
				return $.grep($(this.dom).find(".vocontactlist ul > li"),function(e){
					var i = $(e).children(".contactitem");
					return ($(i).data("endorsed") === true );
				});
			case "outdated":
				return $(this.dom).find(".vocontactlist ul > li > .contactitem").grep(function(e){
					return !($(e).data("endorsed") === false || $(e).data("updated") === true);
				});
			case "none":
			default:
				return $(this.dom).find(".vocontactlist ul > li");
		}
	};
	this.filter = function(filter){
		filter = $.trim(filter) || "none";
		$(this.dom).find(".vocontactlist").removeClass(function (index, css) {
			return (css.match (/\bfilterby-\S+/g) || []).join(' ');
		}).addClass("filterby-" + filter).removeClass("empty");
		
		var lis = this.getFilterItems(filter);
		$(this.dom).find(".vocontactlist ul li").addClass("filteredout");
		$(lis).removeClass("filteredout");
		if( $(lis).length === 0 ){
			$(this.dom).find(".vocontactlist").addClass("empty");
		}
	};
	this.updateFilters = function(){
		$(this.dom).find(".vocontactlist-container .actions > li button.action").each((function(self){
			return function(i, e){
				var flt = $(e).data("filter") || "none";
				var lis = self.getFilterItems(flt);
				$(e).find(".count").text("("+ $(lis).length+")");
			};
		})(this));
	};
	this.setMode = function(mode){
		mode = $.trim(mode).toLowerCase();
		if( this.options.availableModes.length === 1 ){
			mode = this.options.availableModes[0];
		}
		if( $.trim(mode) === "" ){
			$(this.dom).find(".contactvos-container").addClass("mode-init");
			return;
		}
		
		if(this.options.currentMode !== mode ){
			this.options.currentMode = mode;
			$(this.dom).find(".contactvos-container").removeClass(function (index, css) {
				return (css.match (/\bmode-\S+/g) || []).join(' ');
			});
			$(this.dom).find(".contactvos-container").addClass("mode-" + mode);
			$(this.dom).find(".contactvos-container").removeClass("nocontactvo");
		}
		this.unselectAll();
		$(this.dom).find(".vocontact-filter ul > li").removeClass("hidden");
		$(this.dom).find(".vocontactlist > ul > li").removeClass("filteredout hidden");
		$(this.dom).find(".vocontact-filter ul > li").removeClass("hidden");
		$(this.dom).find(".selectcontacttype-container").find(".selectedcontacttype").removeClass("dijitError");
		switch(mode){
			case "suggest":
				$(this.dom).find(".vocontact-filter ul > li > button[data-filter='false']").trigger("click");
				break;
			case "newversion":
				$(this.dom).find(".vocontactlist > ul > li > .contactitem[data-endorsed='true']").each((function(self){
					return function(i, e){
						var id = $.trim($(this).data("id"));
						if( id !== "" ){
							$(this).parent().removeClass("filteredout hidden");
							self.selectItem(id);
						}
					};
				})(this));
				break;
			case "exclude":
				$(this.dom).find(".vocontactlist > ul > li").addClass("filteredout");
				$(this.dom).find(".vocontact-filter ul > li > button").parent().addClass("hidden");
				$(this.dom).find(".vocontact-filter ul > li > button[data-filter='endorsed']").parent().removeClass("hidden");
				$(this.dom).find(".vocontact-filter ul > li > button[data-filter='endorsed']").trigger("click");
				$(this.dom).find(".vocontactlist > ul > li > .contactitem[data-endorsed='true']").each((function(self){
					return function(i, e){
						var id = $.trim($(this).data("id"));
						if( id !== "" ){
							$(this).parent().removeClass("filteredout hidden");
							self.selectItem(id);
						}
					};
				})(this));
				break;
			case "generic":
				$(this.dom).find(".vocontact-filter ul > li > button[data-filter='false']").trigger("click");
				break;
			case "init":
				var empty = $(this.dom).find(".selectcontacttype-container ul > li[data-action='init']");
				$(this.dom).find(".selectcontacttype-container").find(".selectedcontacttype").empty().append($(empty).children(".contacttype").clone());
				$(this.dom).find(".selectcontacttype-container").find(".selectedcontacttype").addClass("dijitError");
				break;
		}
	};
	this.renderRecipientList = function(vo, v){
		vo = $.trim(vo);
		v = v || {};
		v.vo = v.vo || [];
		v.vo = $.isArray(v.vo)?v.vo:[v.vo];
		var vocontacts = $.grep(v.vo, function(e){
			return ( $.trim(e.name) === vo );
		});
		var contacts = [];
		if( vocontacts.length > 0 ){
			contacts = vocontacts[0].contact || [];
			contacts = $.isArray(contacts)?contacts:[contacts];
		} 
		$(this.dom).find(".contactvos-content.previewer .recipients .value").empty();
		$(this.dom).find(".contactvos-content.previewer .recipients").addClass("hidden").removeClass("dropdownoptions-container");
		if( vo === "" || contacts.length === 0 ){
			return;
		}
		var dom = $(this.dom).find(".contactvos-content.previewer .recipients .value").addClass("dropdownoptions-container");
		var header = $("<span class='contactselector dropdownoptions-header dropdownoptions-handler'></span>");
		var list = $("<ul class='dropdownoptions-menu'></ul>");
		$.each(contacts, function(i, e){
			var cnt = $("<li class='contact'></li>");
			var name = $("<span class='name'></span>");
			var email = $("<span class='email'></span>");
			var role = $("<span class='role'></span>");
			$(name).text(e.name);
			$(email).text("<" + e.email + ">");
			$(role).text(e.role);
			$(cnt).append(name).append(email).append(role);
			$(header).append($(name).clone()).append($(email).clone()).append($(role).clone()).append("<span class='seperator'>,</span>");
			$(list).append(cnt);
		});
		$(header).find(".seperator").last().remove();
		$(dom).append(header).append(list);
		$(this.dom).find(".contactvos-content.previewer .recipients").removeClass("hidden");
		this.initMenus();
	};
	this.renderPreviewer = function(v){
		$(this.dom).find(".contactvos-content").addClass("hidden");
		$(this.dom).find(".contactvos-content.previewer").removeClass("hidden").removeClass("error");
		$(this.dom).find(".contactvos-content.previewer .actions .action.back").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				$(self.dom).find(".contactvos-content").addClass("hidden");
				$(self.dom).find(".contactvos-content.composer").removeClass("hidden");
				return false;
			};
		})(this));
		if( v.error ){
			$(this.dom).find(".contactvos-content.previewer").addClass("error");
			$(this.dom).find(".contactvos-content.previewer .main .messagebody").text("[ERROR]: " + v.error );
			return;
		} else {
			$(this.dom).find(".contactvos-content.previewer .voselector").empty();
			var selected = $("<select></select>");
			$(this.dom).find(".selectedvocontacts ul > li .contactitem").each(function(i, e){
				$(selected).append("<option value='" + $(e).data("name") + "' >" + $(e).data("name") + "</option>");
			});
			$(this.dom).find(".contactvos-content.previewer .voselector").append(selected);
			$(selected).unbind("change").bind("change", (function(self,msg, contacts){
				return function(){
					var m = ""+msg;
					m = m.replace(/\{\{vo\.name\}\}/gi, $(this).val());
					$(self.dom).find(".contactvos-content.previewer .main .messagebody").text( m );
					self.renderRecipientList($(this).val(), contacts);
				};
			})(this,$.trim(v.message), v.vorecipients ));
			$(selected).find("option:first").attr("selected","selected");
			$(selected).trigger("change");
			
			v.from = v.from || {};
			v.from.name = $.trim(v.from.name);
			v.from.email = $.trim(v.from.email);
			var fromuser = $("<span class='username'></span>");
			var fromemail = $("<span class='useremail'></span>");
			$(fromuser).text(v.from.name);
			$(fromemail).text("<"+v.from.email+">");
			$(this.dom).find(".contactvos-content.previewer .sender .value").empty().append(fromuser).append(fromemail);
		}
	};
	this.renderResults = function(v){
		v = v || {};
		$(this.dom).find(".contactvos-content").addClass("hidden");
		$(this.dom).find(".contactvos-content.result").removeClass("hidden");
		$(this.dom).find(".contactvos-content.result .state").children().addClass("hidden");
		$(this.dom).find(".contactvos-content.result .actions .action.retry").unbind("click").bind("click", (function(self, err){
			return function(ev){
				ev.preventDefault();
				if( $.trim(err) === "" ){
					self.setMode("init");
					self.load();
				}
				$(self.dom).find(".contactvos-content").addClass("hidden");
				$(self.dom).find(".contactvos-content.composer").removeClass("hidden");
				return false;
			};
		})(this, v.error));
		$(this.dom).find(".contactvos-content.result .actions .action.close").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.close();
				return false;
			};
		})(this));
		if( v.error ){
			$(this.dom).find(".contactvos-content.result .state .error").removeClass("hidden");
			$(this.dom).find(".contactvos-content.result .state .error > span").text("[ERROR]: " + v.error );
			return;
		}else{
			$(this.dom).find(".contactvos-content.result .state .success").removeClass("hidden");
		}
	};
	this.loadPreviewer = function(){
		this.renderLoading(true, "Loading message preview");
		var _model = new appdb.model.ContactVos();
		var data = $.extend({},this.options.selection);
		data.message = encodeURIComponent(data.message);
		data.subject = encodeURIComponent(data.subject);
		data.preview = true;
		var vos = data.vos;
		delete data.vos;
		data["vos"] = JSON.stringify(vos);
		_model.subscribe({event:"update", callback: function(v){
				this.renderLoading(false);
				this.renderPreviewer(v);
		}, caller: this});
		_model.subscribe({event:"error", callback: function(v){
				this.renderLoading(false);
				v = v || {};
				v.error = v.error || "Unknown error occured";
				appdb.debug(v);
		}, caller: this});
		_model.update({query:{id: appdb.pages.application.currentId()}, data: data});
	};
	this.sendMessage = function(){
		if( this.validate() === false ){
			return;
		}
		this.renderLoading(true, "Sending message");
		var _model = new appdb.model.ContactVos();
		var data = $.extend({},this.options.selection);
		data.message = encodeURIComponent(data.message);
		data.subject = encodeURIComponent(data.subject);
		data.preview = false;
		var vos = data.vos;
		delete data.vos;
		data["vos"] = JSON.stringify(vos);
		_model.subscribe({event:"update", callback: function(v){
				this.renderLoading(false);
				this.renderResults(v);
		}, caller: this});
		_model.subscribe({event:"error", callback: function(v){
				this.renderLoading(false);
				v = v || {};
				v.error = v.error || "Unknown error occured";
				appdb.debug(v);
		}, caller: this});
		_model.update({query:{id: appdb.pages.application.currentId()}, data: data});
	};
	this.initActions = function(){
		this.initMenus();
		$(this.dom).find(".footer .action.cancel").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.close();
				return false;
			};
		})(this));
		$(this.dom).find(".footer .action.preview").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.loadPreviewer();
				return false;
			};
		})(this));
		$(this.dom).find(".selectcontacttype-container ul > li").unbind("click").bind("click",(function(self){
			return function(ev){
				ev.preventDefault();
				var action = $.trim($(this).data("action")).toLowerCase();
				self.selectContactType(action);
				return false;
			};
		})(this));
		$(this.dom).find(".vocontact-filter .actions .action").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				$(this).closest(".actions").find(".selected").removeClass("selected");
				$(this).closest(".vocontact-filter").find(".listactions .selectall").addClass("hidden");
				var filter = $.trim( $(this).data("filter") );
				if( filter === "" || filter === "false" ){
					self.filter("none");
				}else{
					self.filter(filter);
				}
				if( $(this).data("allowactions") === true ){
					$(this).closest(".vocontact-filter").find(".listactions .selectall").removeClass("hidden");
				}
				$(this).closest("li").addClass("selected");
				return false;
			};
		})(this));
		$(this.dom).find(".vocontact-filter .listactions .action").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("selectall") ) {
					self.selectAll();
				}else if( $(this).hasClass("clearall") ) {
					self.clearAll();
				}
				return false;
			};
		})(this));
	};
	this.load = function(contacttype){
		this.options.state = "init";
		this.options.currentMode = "";
		this.options.loadedForm = "";
		if( this.options.state !== "loaded" ){
			this.renderLoading(true, "Loading form");
			$.ajax({
				url: appdb.config.endpoint.base + "apps/contactvos",
				data: {id: this.options.id },
				success: (function(self){
					return function(d){
						self.options.loadedForm = d;
						self.renderLoading(false);
						self.options.state = "loaded";
						self.show();
						self.render(contacttype);
					};
				})(this),
				error: (function(self){
					return function(){
						self.renderLoading(false);
						self.options.state = "init";
					};
				})(this)
			});
		}else{
			this.show();
			this.render(contacttype);
		}
	};
	this._initContainer = function(){
		
	};
	this._init = function(){
		this.dom = $(this.options.container);
		if( $(this.dom).length === 0 ){
			this.dom = $("<div class='contactvos-component'></div>");
		}
		this.parent = this.options.parent;
	};
	this._init();
},{
	Dialog: null,
	Title: "Notify VOs for virtual appliance "
});

appdb.components.LinkStatuses = appdb.ExtendClass(appdb.Component, "appdb.components.LinkStatsuses", function(o){
	this.destroy = function(){
		$("input#linkStatuses_quicksearch").unbind("keyup");
		$(this.dom).empty();
	};
	this.render = function(){
		if($.fn.quicksearch){
			$("input#linkStatuses_quicksearch").quicksearch("table#linkStatuses_results tbody tr");
			$("input#linkStatuses_quicksearch").bind("keyup",function(){
				setTimeout(function(){
					var elem = document.getElementById("linkStatuses_body");
					if (elem.clientHeight === elem.scrollHeight)
						$("table#linkStatuses_tableheader").css("padding-right","0px");
					else
						$("table#linkStatuses_tableheader").css("padding-right","15px");
				},100);
			});
			$("table#linkStatuses_tableheader thead tr th").mouseup(function(){
				var th = $($("table#linkStatuses_results thead tr th").get($(this).index()));
				var t = this;
				setTimeout(function(){
					if($(th).hasClass("headerSortUp")){
						$(t).removeClass("headerSortDown");
						$(t).addClass("headerSortUp");
					}else if($(th).hasClass("headerSortDown")){
						$(t).removeClass("headerSortUp");
						$(t).addClass("headerSortDown");
					}else{
						$(t).removeClass("headerSortUp");
						$(t).removeClass("headerSortDown");
					}
				},100);
				$(th).trigger('click');
			});
		}else{
			$("#linkStatuses_form").hide();
		}
	};
	this.load = function(){
		this.publish({event:"loaded",value:{}});
		this.render();
	};
	this._init = function(){
		var v = {};
        if(typeof o.container==="string"){
            this.dom = $(o.container);
        }else{
            this.dom = o.container;
        }
        this.views = v;
	};
	this._init();
});

appdb.components.DisseminationLog = appdb.ExtendClass(appdb.Component, "appdb.components.DisseminationLog", function(o){
	this.destroy = function() {
        this.views.pagerview.destroy();
		$(this.dom).empty();
	};
	
	this.reset = function() {
		this.destroy();
		this._first = false;
		this._init();
		this.load();
	};
	
	this._init = function() {
        if ( o.pageOffset ) this._ofs = o.pageOffset;
		if ( ! this._order ) {
			this._order = o.order || 'senton';
		}
        if ( ! this._orderOp ) {
            this._orderOp = o.orderOp || 'DESC';
        }
        this._model = new appdb.model.Dissemination({"pagelength": 10, "pageoffset": 0, "orderby": this._order, "orderbyOp": this._orderOp});
		this._loading = $(document.createElement("div"));
		this._loading.html('<img src="/images/ajax-loader-small.gif" alt="" width="12px" height="12px" style="padding:0px;margin:0px;vertical-align: top"  /><span style="font-style: italic;font-size: 12px;vertical-align: super;padding:0px;margin:0px;">Loading...</span>');
		if (typeof o.container==="string") {
			this.dom = $(o.container);
		} else {
			this.dom = o.container;
		}
		this.dom.append(this._loading);
	};

	this._createHeaderItem = function(name, field) {
		var h;
		h = '<div style="display: inline-block; width: 40%">'+name+'</div>';
		h = '<div style="display: inline-block; width: 40%">'+name+'</div>'; 
		var item = $(document.createElement("div"));
		item.css("cursor","pointer");
		if ( field === this._order.substr(0,field.length) ) {
			if ( this._orderOp === "DESC" ) {
				h = h + ' ' + '<div style="display: inline-block; width: 40%; text-align: right">&#9660</div>'; 
			} else {
				h = h + ' ' + '<div style="display: inline-block; width: 40%; text-align: right">&#9650</div>'; 
			}

		} 
		item.html(h);
		item.click((function(_this) {
			return function() {
                _this._order = field;
				if ($.trim(_this._orderOp) === "DESC") {
					_this._orderOp = "ASC";
				} else {
					_this._orderOp = "DESC";
				}
				_this._first = false;
				_this.reset();
			};
		})(this));
		return item;
	};

	this._createHeader = function() {
		var header = $(document.createElement("li"));
		header.addClass("header");
		header.append(this._createHeaderItem("Date","senton"));
		header.append(this._createHeaderItem("From","composerid"));
		header.append(this._createHeaderItem("Subject","subject"));
		header.append(this._createHeaderItem("Message","message"));
		return header;
	};

	this.render = function(data, pager) {
		var i, datum, ul, li, s, msg;
		ul = $(document.createElement("ul"));
		ul.addClass("disseminationlog");
        $(this.dom).find("ul").empty().remove();
        $(this.dom).append('<div style="text-align: center; margin-left: auto; margin-right: auto; width: auto" class="disseminationlog_paging"></div>');
		$(this.dom).append(ul);
		this._header = this._createHeader();
		ul.append(this._header);
        if ( ! this.views.pagerview ) {
            this.views.pagerview = new appdb.views.PagerPane({container : $("div.disseminationlog_paging")});
            this.views.pagerview.subscribe({"event": "next", "callback":  function(){
		        this._loading.show();
                this.pager.next();
            }, caller : this});
            this.views.pagerview.subscribe({"event": "previous", "callback": function(){
		        this._loading.show();
                this.pager.previous();
            }, caller : this});
            this.views.pagerview.subscribe({"event": "current", "callback": function(v){
		        this._loading.show();
                this.pager.current(v);
            }, caller : this});
        }
        $("div.disseminationlog_paging").empty();
		this.views.pagerview.id = "dissemination_log";
        this.views.pagerview.render(pager);
        this.data = data;
		if ( this.data ) {
			this.data = ( $.isArray(this.data) ? this.data : [this.data] );
			for ( i=0; i < this.data.length; i+=1 ) {
				s = '';
				datum = this.data[i];
				li = $(document.createElement("li"));
				if (i % 2) li.addClass("odd");
				li.attr("data-index",i);
				s = s + '<div>'+appdb.utils.formatDate(datum.sentOn)+'</div>';
				s = s + '<div><a class="composer" href="#">'+datum.composer.firstname+' '+datum.composer.lastname+'</a></div>';
				s = s + '<div>'+datum.subject+'</div>';
				msg = $(datum.message).text();
				if ( msg.length > 20 ) msg = msg.substr(0,20)+"\u2026";
				s = s + '<div>'+msg+'</div>';
				li.html(s);
				ul.append(li);
				li.click((function(_this, _parent){
					return function() {
						var dt = new appdb.components.DisseminationTool();
						var index = _this.attr("data-index");
						dt.preview(_parent.data[index], false);
					};
				})(li, this));
				li.find("a.composer").click((function(_this, _parent){
                    return function() {
						var index = _this.attr("data-index");
    					appdb.views.Main.showPerson({id: _parent.data[index].composer.id, cname:_parent.data[index].composer.cname},{mainTitle: ""}); 
	    				window.event.cancelBubble = true;
		    			return false;
                    };
				})(li, this));
			}
		}
	};
	this.load = function() {
        this.pager = this._model.getPager();
        this.pager.subscribe({"event": "pageload", "callback": function(d){
            this.render(d.data, d.pager);
			this._loading.hide();
        }, "caller": this});
        if ( ! this._first ) {
            this._first = true;
            this.pager.current();
        }
	};
	this._init();
});
 

appdb.components.DisseminationTool = appdb.ExtendClass(appdb.Component, "appdb.components.DisseminationTool", function(o){
	this.destroy = function(){
		$(this.dom).empty();
	};

	this.reset = function() {
		this.destroy();
		this._init();
	};

	this._buildPreview = function(adr, cnt, sub, msg, flt, checkRecipientCount) {
		var dlg;
		if (typeof adr === "undefined") adr = this._addresses(flt, checkRecipientCount).join(", ");
		cnt = '<div>'+
			'<b>To:</b> &lt;undisclosed-recipients&gt;<br/>'+
			'<div><table cellspacing="0" cellpadding="0"><tr><td><div style="display: inline-block"><b>BCC:</b> </div></td><td><div style="display: inline-block; max-height:100px; overflow-y: auto">'+adr+'</div></td></tr></table></div>'+
			'</div>'+
			'<div style="overflow-y: hidden">'+
			'<b>From:</b> EGI Applications Database<br/>'+
			'<b>Subject:</b> '+sub+
			'</div><hr/>'+
			'<div style="min-height: 500px; max-height:500px; overflow-y: auto">'+msg+'</div>';
		dlg = new dojox.Dialog({
			"title": "Dissemination message preview",
			"style": "width: 60%",
			"content": cnt
		}).show();
	};
		
	this.preview = function(data, checkRecipientCount) {
		var adr, msg, cnt, sub, flt, notification;
		if ( typeof checkRecipientCount === "undefined" ) checkRecipientCount = true;
		if ( typeof data !== "undefined" ) { 
			sub = data.subject;
			msg = data.message;
			flt = data.filter;
		} else {
			sub = this.dom.find('.dissemination_subject :input').val();
			msg = $('textarea.tinymce').val();
		}
		if (sub.trim() === "") {
        	this.ErrorHandler.handle({status:"Preview message",description:"Please provide a message subject first"});
		} else if ($(msg).text().trim() === "") {
        	this.ErrorHandler.handle({status:"Preview message",description:"Please provide a message body first"});
		} else {
			notification = new appdb.utils.notification({
				title: "Dissemination message preview",
				message: '<img src="/images/ajax-loader-small.gif" alt="" width="12px" height="12px" style="padding:0px;margin:0px;vertical-align: top"  /><span style="font-style: italic;font-size: 12px;vertical-align: super;padding:0px;margin:0px;">Loading...</span>',
				delay: 60000,
				style: "min-width: 240px; text-aling: center"
			});
			setTimeout((function(_this){
				return function(){
					if (checkRecipientCount && _this._recipientCount() === 0) adr = "&lt;no recipients&gt;";
					_this._buildPreview(adr, cnt, sub, msg, flt, checkRecipientCount);
					notification.close();
				};
			})(this), 500);
		}
	};

	this._onSend = function(onlyToMe) {
		if ( onlyToMe === true ) {
			new appdb.utils.notification({
				title: "Notification",
				message: "A preview message has been sent to your primary e-mail account!",
				delay: 5000
			});		
		} else {
			new appdb.utils.notification({
				title: "Notification",
				message: "Your message has been sent!",
				delay: 5000
			});
			$('textarea.tinymce').val("");
			this.dom.find('.dissemination_subject :input').val("");
			this.views.log.reset();
			$('div.tabs:last').tabs('select', 2);
		}
	};

	this._recipientCount = function() {
		try {
			return this.views.researchers.resultCount-this.views.researchers.views.peopleList.selectedDataItems.get().length;
		} catch (e) {
			return 0;
		}
	};

	this._doSend = function(onlyToMe) {
		$.ajax({
			type: 'POST',
			url: '/news/senddissemination',
			data: {
				subject: this.dom.find('.dissemination_subject :input').val(),
				message: $('textarea.tinymce').val(),
				textmessage: $($('textarea.tinymce').val()).text(),
				flt: this.getFilter(),
				onlytome: onlyToMe
			},
			success: (function(_this){
				return function() {
					_this._onSend(onlyToMe);
				};
			})(this),
			error: function() {
        		this.ErrorHandler.handle({status:"Send message",description:"Message sending failed, please retry later."});
			}
		});
	};

	this.send = function(onlyToMe) {
		var sub, msg;
		if (typeof onlyToMe === "undefined") onlyToMe = false;
		sub = this.dom.find('.dissemination_subject :input').val();
		msg = $('textarea.tinymce').val();
		if (sub.trim() === "") {
        	this.ErrorHandler.handle({status:"Send message",description:"Please provide a message subject first"});
		} else if ($(msg).text().trim() === "") {
        	this.ErrorHandler.handle({status:"Send message",description:"Please provide a message body first"});
		} else if ((!onlyToMe) && (this._recipientCount() === 0)) {
        	this.ErrorHandler.handle({status:"Send message",description:"Please select at least one recipient first"});
		} else {
			if (!onlyToMe) {
				$('<div title="Send message"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You are about to send a message to <b>'+(this._recipientCount())+'</b> recipients. Are you sure you want to proceed?</p></div>').dialog({
					dialogClass: 'alert',
					autoOpen: true,
					resizable: false,
					modal: true,
					buttons: {
						Yes: (function(_this) {
							return function() {
								$(this).dialog('close');
								_this._doSend(onlyToMe);
							};
						})(this),
						No: function() {
							$(this).dialog('close');
						}
					}
				});
			} else {
				this._doSend(onlyToMe);
			}
		}
	};

	this.getFilter = function() {
		var ex, flt, items, i;
		flt = this.views.researchers.views.filtering.getValue();
		items = this.views.researchers.views.peopleList.selectedDataItems.get();
		for(i = 0; i < items.length; i += 1) {
			ex = items[i];
			flt = flt + " -=person.id:" + ex.id;
		}
        flt = "-=person.nodissemination:true "+flt;
		return flt;
	};
	
	this._addresses = function(flt, checkRecipientCount){
		var c = null, u = "", dat, i, j, cont, s = [];
		if (typeof checkRecipientCount === "undefined") checkRecipientCount = true;
		if (checkRecipientCount && this._recipientCount() === 0) return [];

		if ( typeof flt === "undefined" ) {
			flt = this.getFilter();
		}
		if ( userID !== null ) {
			u = "&userid="+userID+"&passwd="+$.cookie('scookpass');
		}
		var d = new appdb.utils.rest({
			endpoint: appdb.config.endpoint.proxyapi+"?version=" + appdb.config.apiversion + "&resource=people"+encodeURIComponent("?flt="+encodeURIComponent(flt.replace(/”/g, '"'))+u),
			async: false
		}).create().call();
		if ( d ) {			
			if ( d.person ) {
				dat = d.person;
				if ( ! $.isArray(dat) ) dat = [dat];
				for (i=0; i<dat.length; i++) {
					c = null;
					if ( dat[i].contact ) {
						cont = dat[i].contact;
						if ( ! $.isArray(cont) ) cont = [cont];
						for (j=0; j<cont.length; j++) {
							if (cont[j].type === "e-mail") {
								if (cont[j].primary) {
									if ( cont[j].val ) c = cont[j].val();
									break;
								}else {
									if (c === null) c = cont[j].val();
								}
							}
						}
						if ( c !== null ) s.push(c);
					}
				}
			} else {
				alert('Error: Could not retrieve list of recipients');
			}
		} else {
			alert('Error: Could not retrieve list of recipients');
		}
		return s;
	};

	this.render = function(){
		this.views.builder.render();
		$('textarea.tinymce').tinymce({
			// General options
			theme : "advanced",
			plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",

			// Theme options
			theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "cut,copy,paste,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,image,hr,|,insertdate,inserttime,|,forecolor,backcolor",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,
			// Drop lists for link/image/media/template dialogs
			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js",
			convert_urls: false,
			relative_urls: false,
			remove_script_host: true
		});
		var buttons = this.dom.find("#dissemination_message .buttons")[0];
		var pbutton = document.createElement("div");
		var smbutton = document.createElement("div");
		var sbutton = document.createElement("div");
		$(buttons).append(pbutton).append(smbutton).append(sbutton);
		this._preview = new dijit.form.Button({
			label: "Preview", 
			title: "Open a preview in the browser",
			onClick: (function(_this){
				return function(){
					_this.preview();
				};
			})(this)}, pbutton);
		this._sendToSelf = new dijit.form.Button({
			label: "Send to Me", 
			title: "Send a preview message to yourself", 
			onClick: (function(_this){
				return function() {
					_this.send(true);
				};
			})(this)}, smbutton);
		this._send = new dijit.form.Button({
			label: "Send", 
			title: "Send the message to all selected recipients",
			onClick: (function(_this){
				return function() {
					_this.send();
				};
			})(this)}, sbutton);
		

		dojo.parser.parse($("#disseminationform")[0]);
	};

	this.load = function(){
		this.publish({event:"loaded",value:{}});
		this.render();
	};

	this._onFilter = function(v) {
		var i, d = new appdb.utils.rest({
			endpoint: appdb.config.endpoint.proxyapi+"?version=" + appdb.config.apiversion + "&resource=people/filter/normalize%3Fflt="+encodeURIComponent(encodeURIComponent(v.flt)),//appdb.config.endpoint.baseapi+'people/filter/normalize?flt='+encodeURIComponent(v.flt),
			async: false
		}).create().call();
		if ( d.filter ) {
			this.views.builder.load(d.filter);
		}
	};

	this._init = function(){
		var v = {};
		if ( ! o ) return; 
		if (typeof o.container==="string") {
			this.dom = $(o.container);
		} else {
			this.dom = o.container;
		}
		v.researchers = new appdb.components.RelatedContacts({
			container: $("div#dissemination_result")[0],
			disableCheckForChanges: true,
			onClearNull: true,
			hideFiltering: true,
			showExport: true,
			perspectives : (function(){
				var p = $.extend({},appdb.components.RelatedContacts.perspectives);
				delete p.associated;
				p.selected.displayName = "excluded";
				return p;
			})()
		});
		v.researchers.subscribe({event:"close", callback: function(v){
		
		}, caller: this});
		v.researchers.subscribe({event : "itemselected", callback : function(v){
			var item = v.item;
			if(item._isSelected == true){
				v.item._itemData.toRemove = true;
				$(item.dom).addClass("toberemoved");
				$(item.dom).find(".selectedtext:last").text("excluded");
			}else{
				$(item.dom).find(".selectedtext:last").text("");
				$(item.dom).removeClass("toberemoved");
				v.item._itemData.toRemove = false;
			}
		},caller : v.researchers});
		v.researchers.views.filtering.subscribe({event: "filter", callback: (function(_this) {
			return function(v) {
				_this._onFilter(v);
			};
		})(this), caller: this});
		v.researchers.load({query:{flt:null, pagelength: 12, userid: userID}, ext:{
            baseQuery: {
                flt: "-=person.nodissemination:true"
            }
        }});

		// FIXME: hide perspectives until switching bug is fixed
		$(v.researchers.dom).find(".preview").hide();

		v.builder = new appdb.views.FilterBuilder({container: $("#dissemination_filter_builder")[0], listtype:appdb.views.FilterBuilderItem, type: "person"});
		v.builder.parent = this;
		v.log = new appdb.components.DisseminationLog({
			container: $("#dissemination_log")
		});
		v.builder.subscribe({event: "onOK", callback: (function(_this){
			return function(e){
				_this.views.researchers.load({query: {flt: e, pagelength: 12, userid: userID}, ext:{
                    userQuery: {
                        flt: e
                    },
                    baseQuery: {
                        flt: "-=person.nodissemination:true"
                    }
                }});
			};
		})(this)});
		v.builder.subscribe({event: "onClear", callback: (function(_this){
			return function(e){
				_this.views.researchers.views.peopleList.selectedDataItems.clear();
				_this.views.researchers.load({query:{flt:null, pagelength: 12, userid: userID}, ext:{
                    baseQuery: {
                        flt: "-=person.nodissemination:true"
                    }
                }});
			};
		})(this)});
		v.log.load();
		this.views = v;
	};

	this._init();
});

appdb.components.Tags = appdb.ExtendClass(appdb.Component,"appdb.components.Tags",function(o){
	this._slider = null;
	this.destroy = function(){
		if(this._slider){
			this._slider.destroyRecursive(false);
		}
		for(var i in this.views){
			this.views[i].reset();
		}
		this._model.destroy();
		$(this.dom).empty();
	};
	this._adjustItems = function(cnt){
        cnt = cnt || -1;
        if(cnt===-1){
            cnt = parseInt(this._slider.attr("value"));
        }
		var d = {};
		d = $.extend(d,this._model.getLocalData());
		d.tag = this._model.getLocalData().tag.slice(0);
		if($.isArray(d.tag)===false){
			d.tag = [d.tag];
		}
		if(d.tag.length===cnt-1){
			return;
		}
		if(d.tag.length>cnt-1){
			d.tag = d.tag.slice(0,cnt-1);
		}
		this.views.cloud.render(d);
		this.render = this._adjustItems;
	};
	this._renderSlider = function(){
		var c = this._model.getLocalData();
		var max = appdb.components.Tags.maxVisibleItems;
		if($.isArray(c.tag)===false){
			c.tag = [c.tag];
		}
		if(c.tag.length<=max){		
			max = c.tag.length;
		}		
		this._slider = new dijit.form.HorizontalSlider({
            name: "slider",
            value: max-1,
            minimum: 1,
            maximum: max,
			discreteValues : max-2,
            intermediateChanges: false,
            style:"width:95%;text-align:center;height:100%;",
            onChange: (function(_this){
				return function(value) {
						value = Math.round(value);
						_this._adjustItems(value);
					};
            })(this)
        },
        $(this.dom).find(".tagcloud-slider").get(0));
        $(this.dom).find(".dijitSliderIncrementIconH , .dijitSliderDecrementIconH").css({"margin-top":"0px","margin-bottom":"0px"});
	};
	this.render = function(d){
		if(d.tag){
			if($.isArray(d.tag) === false){
				d.tag = [d.tag];
			}
			if(d.tag.length>appdb.components.Tags.maxVisibleItems-1){
				d.tag = d.tag.slice(0,appdb.components.Tags.maxVisibleItems-1);
			}
		}
		this._renderSlider();
		this.views.cloud.render(d);
	};
	this.load = function(d){
		this._model = appdb.model.Tags;
		this._model.unsubscribeAll(this);
		this._model.subscribe({event:"select",callback:function(e){
                this.publish({event:'loaded',value:{}});
				this.render(e);
                                if(this._slider){
                                        if($.browser.msie){
                                                this._slider.attr("value",((e.tag.length<100)?e.tag.length:99));
                                        }else{
                                                this._slider.attr("value",((e.tag.length<appdb.components.Tags.maxVisibleItems)?e.tag.length:appdb.components.Tags.maxVisibleItems));
                                        }
                                }
            },caller:this});
        this._model.subscribe({event:"error",callback:function(e){
            console.log("ERROR[Tags|model]:",e);
        }});
		if( d ){
			this._model.setLocalData(d);
			this._model.Obs.publish({event: "select", value: d});
		} else {
			this._model.get();
		}
	};
	this._initContainer = function(){
		if($(this.dom).length===0){
			return;
		}
		var tl = document.createElement("div"), header = document.createElement("div");
		if(this._title!==null){
			$(header).addClass("tagcloud-header").html("<span>"+(this.title || "Tag cloud")+"</span>");
			$(this.dom).append($(header));
		}
		$(tl).addClass("tagcloud-list");
		$(this.dom).append("<div class='tagcloud-slider' title='Slide to change the count of visible tags.'></div>").append($(tl));
	};
	this._init = function(){
		var v = {};
		if(typeof o.container==="string"){
			this.dom = $(o.container);
		}else{
			this.dom = o.container;
		}
		this._title = o.title || (o.title===null)?null:"Tag cloud";
		this._initContainer();
		v.cloud = new appdb.views.TagCloud({container:$(this.dom).find(".tagcloud-list").get(0)});
		this.views = v;
	};
	this._init();
},{
	maxVisibleItems : 100
});
appdb.components.MainSearch = appdb.ExtendClass(appdb.Component,"appdb.views.MainSearch",function(o){
    this._cloud = null;
    this._tagdialog = null;
    this.reset = function(){

    };
    this.render = function(){
        this.views.filter.unsubscribeAll();
        this.views.filter.subscribe({event:"filter",callback:(function(_this){
                return function(v){
					delete v.target.args.baseQuery;
					delete v.target.args.systemQuery;
					v.target.args.userQuery = v.target.args.userQuery || {};
					v.target.args.userQuery.flt = $.trim(v.query.flt);
					switch($.trim(v.target.args.content)){
						case "vappliance":
							v.target.args.baseQuery = v.target.args.baseQuery || {};
							v.target.args.baseQuery.flt = "+*&application.metatype:1 " + $.trim(v.target.args.baseQuery.flt);
							break;
						case "software":
							v.target.args.baseQuery = v.target.args.baseQuery || {};
							v.target.args.baseQuery.flt = "+*&application.metatype:0 " + $.trim(v.target.args.baseQuery.flt);
							break;
						case "":
						default:
							break;
					}
					v.target.callback(v.query,v.target.args);
                    this.setValue("");
                };
        })(this),caller: this.views.filter}).subscribe({event:"target",callback:function(v){
            if(v.value==="apps"){
                $(".mainsearch-tags").css({visibility: "visible"});
            }else{
                $(".mainsearch-tags").css({visibility: "hidden"});
            }
			this.views.filter._field = "flt";
        },caller:this}).render();
    };
    this.load = function(){
    };
    this._initContainer = function(){
        var main = document.createElement("div"), body = document.createElement("div"),footer = document.createElement("div"), a = document.createElement("a");
        this._cloud = document.createElement("div") ;
        $(main).addClass("mainsearch");
        $(body).addClass("mainsearch-body");
        $(footer).addClass("mainsearch-footer");

        $(body).append("<span class='mainsearch-filter' id='mainsearch-filter'></span>");
        $(main).append($(body));
        $(a).attr("href","#").addClass("mainsearch-tags").css({visibility:"hidden"});
        $(footer).append($(a));
        $(this._cloud).addClass("mainsearch-tagcloud").css({"width":"200px"});
        $(a).click((function(_this){
               return function(){
                    setTimeout(function(){
                        dijit.popup.open({
                            parent : $(a)[0],
                            popup: _this._tagdialog,
                            around :$(a)[0],
                            orient: {'BR':'TR','BL':'TL'}
                        });
                        _this.views.cloud.render();
                        $(_this._cloud).click(function(event) {
                            var src = event.srcElement?event.srcElement:event.target;
                            if(src && src.tagName.toLowerCase()==="a"){
                                return true;
                            }
                            return false;
                        });
                    },10);
                };
        })(this)).text("Show tag cloud");
        
        $(main).append($(footer));
        $(this.dom).append($(main));
    };
    this._init = function(){
        var v = {};
		if(typeof o.container==="string"){
			this.dom = $(o.container);
		}else{
			this.dom = o.container;
		}
		this._initContainer();
        v.filter = new appdb.views.ExtendedFilter({
            container : "mainsearch-filter",
            rich : false,
            watermark : "Search...",
            displayClear : false,
            height:10,
            targets:[
                {text:"Software",value:"apps", image: loadImage(appdb.config.images.applications), title:"Search software",callback : appdb.views.Main.showApplications,args : {mainTitle : "Software",prepend:[], content: "software"}},
				{text:"Virtual Appliances",value:"vapps", image: loadImage(appdb.config.images["virtual appliances"]), title:"Search virtual appliances",callback : appdb.views.Main.showApplications,args : {mainTitle : "Virtual Appliance",prepend:[], content:"vappliance"}},
				{text:"People",value:"people", image: loadImage(appdb.config.images.person), title:"Search people",callback : appdb.views.Main.showPeople, args : {mainTitle: "People",prepend:[]}}
            ],
            seperator : "<div style='height:1px;'></div><span style='display:inline-block;padding-top:5px;padding-left:5px;padding-right:5px;'>in</span>"
        });
        v.cloud = new appdb.components.Tags({container:$(this._cloud)});
        this.views = v;
        this.views.cloud.subscribe({event : "loaded",callback:function(){
            this._tagdialog =  new dijit.TooltipDialog({content : $(this._cloud).get(0)});
        },caller : this});
    };
    this._init();
});

appdb.components.TabContainer = appdb.ExtendClass(appdb.Component,"appdb.components.TabContainer",function(o){
    this.reset = function(){
		
    };
	this._tabs = [];
	this.showTab = function(index){
		index = parseInt(index) || 0;
		var h = $(this._tabs[index].handler);
		var b = $(this._tabs[index].body);
		$(this.dom).find("thead > tr > th > ul > li").removeClass("tab-selected");
		$(h).addClass("tab-selected");

		$(this.dom).find("tbody:first > tr").hide();
		$(b).show();
	};
    this.render = function(){
        $(this.dom).addClass("tabcontainer");
		var ul = $(this.dom).find("thead tr th ul")[0];
		$(ul).addClass("tab-bar");
		$(ul).find("li").each((function(_this){
			return function(index,elem){
				$(this).addClass("tab");
				$(this).find("span").addClass("tab-text");
				_this._tabs[_this._tabs.length] = {handler : this};
			};
		})(this));
		$(ul).find("li").each((function(_this){
			return function(index,elem){
				$(this).click(function(){
					_this.showTab(index);
				});
			};
		})(this));
		$(this.dom).find("tbody:first > tr").each((function(_this){
			return function(index,elem){
				_this._tabs[index]["body"] = $(elem);
				$(elem).hide();
			};
		})(this));
		this.showTab(0);
		
    };
    this.load = function(d){
        
    };
    this._initContainer = function(){
        
    };
    this._init = function(){
        var v = {};
		if(typeof o.container==="string"){
			this.dom = $(o.container);
		}else{
			this.dom = o.container;
		}
		this._initContainer();
        this.views = v;
    };
    this._init();
});

appdb.components.DataList = appdb.ExtendClass(appdb.Component,"appdb.components.DataList", function(o){
	this.reset = function(){
		$(this.dom).empty();
		this._initContainer();
	};
	this.render = function(d,col){
		col = col || 0;
		if(col===0){this.reset();}
		var ul = $($(this.dom).find(".datalist-list").get(col));
		var w = $(ul).css("width");
		var i, len = d.length;
		for(i=0; i<len; i+=1){
			var li = document.createElement("li"), v = null;
			var a = document.createElement("a");
			$(li).addClass("datalist-item").css("width",w);
			v = d[i][this._modelvalue];
			if(typeof v === "undefined"){
				v = d[i].val();
			}
			$(a).attr("href","#").attr("title","").text(v);
			$(li).append($(a));
			if(this._events){
				for(var e in this._events){
					$(a)[e]((function(_this,item,elem){
						return function(){
							_this._events[e](item,elem);
						};
					})(this,d[i],$(li)));
				}
			}
			$(ul).append($(li));
		}
	};
	this.load = function(q){
		var m = appdb.FindNS("appdb.model."+this._modelname, false);
		if(typeof m === "undefined"){
			console.log("ERROR[DataList]:could not find model " + this._modelname);
			return;
		}
		this._model = m;
		this._model.subscribe({event : "select", callback: function(v){
			v = v[this._modelfield];
			if($.isArray(v)===false){
				v = [v];
			}
			var sl = Math.floor(v.length/this._columns);
			var s = 0;
			for(var i=0; i<this._columns; i+=1){
				var vp = v.slice(s,sl+s+1);
				this.render(vp,i);
				s += sl+1;
			}
		},caller:this});
		this._model.subscribe({event:"error",callback: function(e){
				console.log("ERROR[DataList]:An error occured while loading datalist data.",this);
		},caller : this});
	this._model.get();
	};
	this._initContainer = function(){
		$(this.dom).addClass("datalist");
		var w = ""+Math.floor(100/this._columns);
		for(var i=0; i<this._columns; i+=1){
			var ul = document.createElement("ul");
			$(ul).addClass("datalist-list").css("width",w+"%");
			$(this.dom).append($(ul));
		}
	};
	this._init = function(){
		if(typeof o.container==="string"){
			this.dom = $(o.container);
		}else{
			this.dom = o.container;
		}
		
		this._modelname = o.model;
		this._modelfield = o.field;
		this._modelvalue = o.value;
		this._events = o.events;
		this._columns = o.columns || 1;
		this._initContainer();
	};
	this._init();
});
appdb.components.MailSubscription = appdb.ExtendClass(appdb.Component,"appdb.components.MailSubscription",function(o){
    var NewsEventType = appdb.components.MailSubscription.NewsEventType, NewsDeliveryType = appdb.components.MailSubscription.NewsDeliveryType;
    this._subscription = undefined;
    this._dialog = undefined;
    this.events = [
        {value:NewsEventType.E_INSERT, name:"New Entries",selected:true},
        {value:NewsEventType.E_UPDATE, name:"updates",selected:true},
        {value:NewsEventType.E_INSERT_COMMENT, name:"New comments",selected:false},
        {value:NewsEventType.E_INSERT_CONTACT, name:"New contacts",selected:true}];
    this.deliveries = [
        {value:NewsDeliveryType.D_DAILY_DIGEST, name:"Daily",selected:false},
        {value:NewsDeliveryType.D_WEEKLY_DIGEST, name:"Weekly",selected:true},
        {value:NewsDeliveryType.D_MONTHLY_DIGEST, name:"Monthly",selected:false}];
    this.reset = function(){
        $(this.dom).empty();
	};
	this.render = function(d){
        var i, doc = document, res = doc.createElement("div"), desc = doc.createElement("span"), title = doc.createElement("span"), format=doc.createElement("span"),
            actions=doc.createElement("span"), commands=doc.createElement("span"), progress = doc.createElement("div"), message = doc.createElement("span"), img = doc.createElement("img");
        var eventTypes = NewsEventType.split(this._subscription.events),
            deliveryTypes = NewsDeliveryType.split(this._subscription.delivery);
		$(res).addClass("customsubscribe");
		$(desc).addClass("description");
        $(desc).append("Subscriptions will be send to <i><b>"+appdb.model.PrimaryContact.userPrimaryContact+"</b></i>. To change your primary e-mail contact visit the preferences section of your <a href='/store/person/"+userCName+"' onclick='appdb.views.Main.showPerson({id:userID,cname:userCName});'>profile</a>.");

        //Title rendering
		$(title).append("<span class='title'>Title:</span>").
            append("<input type='text'  />").
            addClass("title");
        (new dijit.form.TextBox({
            value : this._subscription.name,
            "class" : "title",
            onChange : (function(_this){
                    return function(val){
                        _this._subscription.name = val;
                    };
                })(this)
            },$(title).find("input")[0]));

        //Format list rendering
        $(format).append("<span class='title'>Delivery:</span>").addClass("delivery");
        for(i=0; i<this.deliveries.length; i+=1){
            $(format).append("<span class='checkoption'><input type='checkbox' value='"+this.deliveries[i].value+"'   /><span>"+this.deliveries[i].name+"</span></span>");
        }
        $(format).find("span.checkoption > input").each((function(_this,dt){
            return function(index,elem){
              (new dijit.form.CheckBox({
                    checked : dt[$(elem).val()],
                    onChange : (function(val){
                        return function(v){
                            _this._subscription.delivery = ((v)?(_this._subscription.delivery | val):(_this._subscription.delivery ^ val));
                        };
                    })($(this).val())
                },$(this)[0]));
            };
        })(this,deliveryTypes));

        //Actions list rendering
		$(actions).append("<span class='title'>Notify me for:</span>").addClass("events");
        for(i=0; i<this.events.length; i+=1){
            $(actions).append("<span class='checkoption'><input type='checkbox' value='"+this.events[i].value+"' checked  /><span>"+this.events[i].name+"</span></span>");
        }
        $(actions).find("span.checkoption > input").each((function(_this,et){
            return function(index,elem){
                (new dijit.form.CheckBox({
                    checked : et[$(elem).val()],
                    onChange : (function(val){
                        return function(v){
                           _this._subscription.events = ((v)?(_this._subscription.events | val):(_this._subscription.events ^ val));
                        };
                    })($(this).val())
                },$(this)[0]));
            };
        })(this,eventTypes));

        //Commands rendering
        $(commands).addClass("commands");
        if(this._subscription.id){//Already subscribed. The actions will be Update and Unsubscribe
            $(commands).append("<span class='command mailfeed' ><a href='' alt='' title='Click to update the current mail notification settings' target='_blank' id='mailsubscriptionupdate'>Update</a></span>").
            append("<span class='command mailfeed' ><a href='' alt='' title='Click to unsubscribe' target='_blank' id='mailsubscriptionremove'>Unsubscribe</a></span>");
            $(commands).find("#mailsubscriptionupdate").click((function(_this){
                return function(){
                    if(_this._subscription.id){
                        _this._model.update(_this._subscription);
                    }else{
                        console.log("Invalid action UPDATE for mail notification service. No id provided");
                    }
                };
            })(this));
            $(commands).find("#mailsubscriptionremove").click((function(_this){
                return function(){
                    if(_this._subscription.id){
                        _this._model.remove({id:_this._subscription.id,pwd:_this._subscription.unsubscribe_pwd});
                    }else{
                        console.log("Invalid action REMOVE for mail notification service. No id provided");
                    }
                };
            })(this));
        }else{
            $(commands).append("<span class='command mailfeed' ><a href='#' alt='' title='Click to subscribe to mail notification service for this list of items'  id='mailsubscriptioninsert' >Subscribe</a></span>");
            $(commands).find("#mailsubscriptioninsert").click((function(_this){
                return function(){
                    if(_this._subscription.id){
                        console.log("Invalid action INSERT for mail notification service. Subscription already exists id:" + _this._subscription.id);
                    }else{
                        _this._model.insert(_this._subscription);
                    }
                };
            })(this));
        }

        $(message).addClass("message").append("Loading...");
        $(img).attr("src","/images/ajax-loader-small.gif").attr("alt","").attr("border","0");
        $(progress).append(img).append(message).addClass("progress").css({"display":"none"});
        
        $(res).append(title).append(format).append(actions).append(progress).append(commands).append(desc);
        this._dialog = $(res)[0];

        $(this._dialog).click(function(event) {
            var src = event.srcElement?event.srcElement:event.target;
            if(src && src.tagName.toLowerCase()==="a"){
                return false;
            }
            return false;
        });
        
        setTimeout((function(_this){
            _this._TooltipDialog = new dijit.TooltipDialog({content:_this._dialog});
            return function(){
                dijit.popup.open({
                    parent: $(_this.dom)[0],
                    popup:  _this._TooltipDialog,
                    around: $(_this.dom)[0],
                    orient: {BR:'TR'},
                    onClose : function(){
                        _this._TooltipDialog.destroyRecursive(false);
                    }
                });
            };
        })(this),10);
	};
    this._renderMessage = function(msg){
        setTimeout((function(_this,message){
            return function(){
                dijit.popup.open({
                    parent: $(_this.dom)[0],
                    popup:  new dijit.TooltipDialog({content:"<div>"+message+"</div>"}),
                    around: $(_this.dom)[0],
                    orient: {BR:'TR'}
                });
            };
        })(this,msg),10);
    };
    this._inProgress = {
        show : function(o,msg){
            var p = $($(o._dialog).find("div.progress")[0]), m = $($(p).find("span.message")[0]);
            var c = $($(o._dialog).find(".commands")[0]);
            $(m).empty().append(msg || "loading...");
            $(p).css({display : "inline-block"});
            $(c).css({display : "none"});
        },
        hide : function(o){
            $($(o._dialog).find("div.progress")[0]).css({display: "none"});
            var c = $($(o._dialog).find(".commands")[0]);
            $(c).css({display : "inline-block"});
        }
    };
    this.load = function(q,persist){
        if(typeof q === "string"){
            q = {flt:'',title:q};
        }
        q = q || {};
        
        this._model = new appdb.model.MailSubscription();
        this._model.subscribe({event:"select",callback : function(v){
            if(v.id){
                this._subscription = {
                    id : v.id,
                    name : v.name,
                    events : v.events,
                    delivery : v.delivery,
                    flt : appdb.utils.base64.encode(q.flt),
                    subjecttype : "app",
                    unsubscribe_pwd : v.unsubscribe_pwd
                };
            }else{
                this._subscription = {
                    name : q.title,
                    events : NewsEventType.E_INSERT | NewsEventType.E_UPDATE,
                    delivery : NewsDeliveryType.D_WEEKLY_DIGEST ,
                    flt : appdb.utils.base64.encode(q.flt),
                    subjecttype : "app"
                }; 
            }
             if(this._TooltipDialog && persist){
                    dijit.popup.close( this._TooltipDialog);
                    setTimeout((function(_this){
                        _this.render();
                    })(this),10);
                }else{
                    dijit.popup.close( this._TooltipDialog);
                }
                this._initContainer();
            
        } , caller : this});
        this._model.subscribe({event : "beforeinsert", callback : function(){
                this._inProgress.show(this,"Subscribing to mail notification service");
            //TODO : prepare UI for SUBSCRIBE Action
        }, caller : this});
        this._model.subscribe({event : "insert", callback : function(){
                this._inProgress.hide(this);
                this._model.get({flt:this._subscription.flt},true);
            //TODO: Set UI for subscribed mail notification
        }, caller : this});
        this._model.subscribe({event : "beforeupdate", callback : function(){
            //TODO: prepare UI for update action
            this._inProgress.show(this,"Updating current subscription");
        }, caller : this});
        this._model.subscribe({event : "update", callback : function(){
            //TODO: set new updated data
            this._inProgress.hide(this);
            this._model.get({flt:this._subscription.flt},true);
        }, caller : this});
        this._model.subscribe({event : "beforeremove", callback : function(){
            //Prepare UI for UNSUBSCRIPTION
            this._inProgress.show(this,"Unsubscribing from mail notification service");
        }, caller : this});
        this._model.subscribe({event : "remove", callback : function(){
            this._inProgress.hide(this);
            this._model.get({flt:this._subscription.flt},true);
        }, caller : this});
        if(userID===null){
            this._initContainer();
        }else{
            this._model.get({flt:appdb.utils.base64.encode(q.flt),subjecttype:this.subjecttype});
        }
	};
	this._initContainer = function(){
        this.reset();
        var doc = document, div = doc.createElement("div"), a = doc.createElement("a"),
            img = doc.createElement("img"), title ="Click to subscribe to the mail notification service for the current list" ;
        $(div).addClass("mailnotification");
        $(img).attr("border","0").attr("alt","").attr("src",appdb.components.MailSubscription.images.subscribe);

        if(this._subscription && this._subscription.id){
            title = "Click to customize your subscription to the mail notification services for the current list";
            $(img).attr("src",appdb.components.MailSubscription.images.customize);
        }
        $(a).attr("href","#").attr("title",title).click((function(_this){
            return function(){
                if(userID!==null){
                    if( $.trim(appdb.model.PrimaryContact.userPrimaryContactId) === ""){
                        _this._renderMessage("<div style='width:415px;'><div style='padding:3px;'><b>No e-mail contact found in your profile.</b></div><div style='padding:3px;'>At least one e-mail contact is needed in order to subscribe to the e-mail notification service. Visit your profile by clicking  <a href='/store/person/"+userCName+"' onclick='appdb.views.Main.showPerson({id:userID,cname:userCName});'>here</a> to add or edit your contacts.</div><div style='padding:3px;'>In case you wish to add more than one e-mail contacts the system will set as the primary one the first one added.To set a different primary e-mail contact visit the preferences section of your profile.</div></div>");
                    }else{
                        _this.render();
                    }
                }else{
                    _this._renderMessage('<div style="width:420px;">If you wish to subscribe to the applications database e-mail notification service, please <b>Sing In</b> first with your account.</div>');
                }
            };
        }(this)));

        $(a).append(img);
        $(div).append(a);
        $(this.dom).append(div);
	};
	this._init = function(){
		if(typeof o.container==="string"){
			this.dom = $(o.container);
		}else{
			this.dom = o.container;
		}
		this.subjecttype = (o.subjecttype)?""+o.subjecttype:"app";
	};
	this._init();
},{
    NewsEventType : {E_INSERT : 1, E_UPDATE : 2, E_DELETE : 4,  E_INSERT_COMMENT : 8, E_INSERT_CONTACT : 16 , 
        has : function(val,en){return (( val & en ) == en );}, //Checks whether an enumerated variable has the given enumeration
        split : function(val){ //Takes an enumerated variable and split it into an array.
            var res = {}, en = appdb.components.MailSubscription.NewsEventType;
            for(var e in en){
                if(typeof e === "function"){
                    continue;
                }
                res[en[e]] = en.has(val,en[e]);
            }
            return res;
    }},
    NewsDeliveryType : {D_NO_DIGEST : 1, D_DAILY_DIGEST : 2, D_WEEKLY_DIGEST : 4, D_MONTHLY_DIGEST : 8, 
        has : function(val,en){return (( val & en ) == en );},//Checks whether an enumerated variable has the given enumeration
        split : function(val){ //Takes an enumerated variable and split it into an array.
            var res = {}, en = appdb.components.MailSubscription.NewsDeliveryType;
            for(var e in en){
                if(typeof e === "function"){
                    continue;
                }
                res[en[e]] = en.has(val,en[e]);
            }
            return res;
    }},
    images : {
        subscribe : "/images/email.png",
        customize : "/images/email_notify.png"
    }
});
appdb.components.EntrySubscription = appdb.ExtendClass(appdb.Component,"appdb.components.EntrySubscription",function(o){
 var NewsEventType = appdb.components.EntrySubscription.NewsEventType, NewsDeliveryType = appdb.components.EntrySubscription.NewsDeliveryType;
 this.getDeliveryTypes = function(){
   return NewsDeliveryType.split(this._subscription.delivery);
 };
 this.getEventTypes = function(){
  return NewsEventType.split(this._subscription.events);
 };
 this._subscription={};
 this.addDelivery = function(dv){
  this._subscription.events = (this._subscription.delivery | dv);
 };
 this.removeDelivery = function(d){
  this._subscription.events = (this._subscription.delivery ^ dv);
 };
 this.addEvent = function(ev){
  this._subscription.events = (this._subscription.events | ev);
 };
 this.removeEvent = function(ev){
  this._subscription.events = (this._subscription.events ^ ev);
 };
 this.initModel = function(){
	this._model = this._model || new appdb.model.MailSubscription();
	this._model.unsubscribeAll();
	this._model.subscribe({event:"beforeselect",callback : function(v){

	}, caller: this});
	this._model.subscribe({event:"select",callback : function(v){
		this._autoLoad = true;
		if(v.id){
				this._subscription = {
					id : v.id,
					name : v.name,
					events : v.events,
					delivery : v.delivery,
					flt : this._subscription.flt,
					subjecttype : this._subscription.subjecttype,
					unsubscribe_pwd : v.unsubscribe_pwd
				};
		}else{
		this._subscription = {
			name : this._subscription.name,
			events : this._subscription.events,
			delivery : this._subscription.delivery ,
			flt : this._subscription.flt,
			subjecttype : this._subscription.subjecttype
		};
		}
		this.render();
	} , caller : this});
	this._model.subscribe({event : "beforeinsert", callback : function(){
		this.render("subscribing");
		//TODO : prepare UI for SUBSCRIBE Action
	}, caller : this});
	this._model.subscribe({event : "insert", callback : function(){
		this._model.get({flt:appdb.utils.base64.encode(this._subscription.flt),subjecttype:this._subscription.subjecttype},true);
		//TODO: Set UI for subscribed mail notification
	}, caller : this});
	this._model.subscribe({event : "beforeupdate", callback : function(){
		//TODO: prepare UI for update action
		this.render("updating");
	}, caller : this});
	this._model.subscribe({event : "update", callback : function(){
		//TODO: set new updated data
		this._model.get({flt:appdb.utils.base64.encode(this._subscription.flt) ,subjecttype:this._subscription.subjecttype},true);
	}, caller : this});
	this._model.subscribe({event : "beforeremove", callback : function(){
		//Prepare UI for UNSUBSCRIPTION
		this.render("unsubscribing");
	}, caller : this});
	this._model.subscribe({event : "remove", callback : function(){
		this._model.get({flt:appdb.utils.base64.encode(this._subscription.flt),subjecttype:this._subscription.subjecttype},true);
	}, caller : this});
 };
 this.load = function(q){
  if(typeof q === "string"){
      q = {flt:'',title:q};
  }
  q = q || {};

  if(userID===null){
      this._initContainer();
  }else{
      this._model.get({flt:appdb.utils.base64.encode(this._subscription.flt),subjecttype: this._subscription.subjecttype});
	  if( $.trim(this._subscription.subjecttype).toLowerCase() === "app-entry" ){
		  appdb.pages.application.requests.register(this._model,"following");
	  }
  }
 };
 this.currentState = undefined;
 this.render = function(state){
  var a = $(this.dom).find("a:first");
  this.currentState = state;
  switch(state){
   case "subscribing":
    $(a).removeClass("unsubscribing").removeClass("updating").addClass("subscribing");
    $(a).empty().append("<span class='contents'><img src='/images/ajax-loader-small.gif' border='0'></img><span>follow</span></span>");
    $(a).append("<span class='alternative'><img src='/images/ajax-loader-small.gif' border='0'></img><span>follow</span></span>");
    return;
   case "unsubscribing":
    $(a).removeClass("subscribing").removeClass("updating").addClass("unsubscribing");
    $(a).empty().append("<span class='contents'><img src='/images/ajax-loader-small.gif' border='0'></img><span>unfollow</span></span>");
    $(a).append("<span class='alternative'><img src='/images/ajax-loader-small.gif' border='0'></img><span>unfollow</span></span>");
    return;
   case "updating":
    $(a).removeClass("subscribing").removeClass("unsubscribing").addClass("updating");
    $(a).empty().append("<span class='contents'><img src='/images/ajax-loader-small.gif' border='0'></img><span>updating...</span></span>");
    $(a).append("<span class='alternative'><img src='/images/ajax-loader-small.gif' border='0'></img><span>updating...</span></span>");
    return;
   default:
    $(a).removeClass("subscribing").removeClass("unsubscribing").removeClass("updating").removeClass("following").removeClass("follow");
    if(this._subscription.id || (this._defaultState === "following" && this._autoLoad == false)){
     $(a).addClass("following");
     $(a).empty().append("<span class='contents'><img src='/images/following.png' border='0'></img><span>following</span></span>");
     $(a).append("<span class='alternative'><img src='/images/unfollow.png' border='0'></img><span>unfollow</span></span></span>");
    }else{
     $(a).addClass("follow");
     $(a).empty().append("<span class='contents'><img src='/images/follow.png' border='0'></img><span>follow</span></span>");
     $(a).append("<span class='alternative'><img src='/images/follow.png' border='0'></img><span>follow</span></span></span>");
    }
    break;
  }
  
 };
 this._renderMessage = function(msg){
     setTimeout((function(_this,message){
	 return function(){
	     dijit.popup.open({
		 parent: $(_this.dom)[0],
		 popup:  new dijit.TooltipDialog({content:"<div>"+message+"</div>"}),
		 around: $(_this.dom)[0],
		 orient: {BR:'TR'}
	     });
	 };
     })(this,msg),10);
 };
 this.canUseService = function(showDialogs){
  showDialogs = (typeof showDialogs === "undefined")?true:showDialogs;
  if(userID!==null){
   if( $.trim(appdb.model.PrimaryContact.userPrimaryContactId) === ""){
    if( showDialogs === true ){
     this._renderMessage("<div style='width:415px;'><div style='padding:3px;'><b>No e-mail contact found in your profile.</b></div><div style='padding:3px;'>At least one e-mail contact is needed in order to subscribe to the e-mail notification service. Visit your profile by clicking  <a href='/store/person/"+userCName+"' onclick='appdb.views.Main.showPerson({id:userID,cname:userCName});'>here</a> to add or edit your contacts.</div><div style='padding:3px;'>In case you wish to add more than one e-mail contacts the system will set as the primary one the first one added.To set a different primary e-mail contact visit the preferences section of your profile.</div></div>");
     return false;
    }
       
   }else{
       return true;
   }
  }else{
    if(showDialogs === true ){
     this._renderMessage('<div style="width:420px;">If you wish to subscribe to the applications database e-mail notification service, please <b>Sign In</b> first with your account.</div>');
    }
    return false;
  }
  return true;
 };
 this._initContainer = function(){
  var a = document.createElement("a");
  $(this.dom).append(a);
  if( this.displayHelp === true ){
	$(this.dom).after("<a href='#' class='followhelp'><img src='/images/question_mark.gif'></img></a>");
	$(this.dom).parent().find(".followhelp").click(function(){
		setTimeout((function(_this){
			return function(){
				dijit.popup.open({
				parent : $(_this)[0],
				popup: new dijit.TooltipDialog({content:"<div style='width:300px;'><span>By using the 'follow' function you will receive a daily e-mail notification summarizing the activity (if any) of the software item</span></div>"}),
				around :$(_this)[0],
				orient: {'BR':'TR'}
				});
				};
			})(this),10);
			return false;
	});
  }
  $(a).attr("href","#").attr("title","").click((function(_this){
       return function(e){
	   if(! _this.canUseService(true) ) {
	    return false;
	   }
	   if(_this.currentState){
	    return false;
	   }
	   if(_this._subscription.id){
	     _this._model.remove({id:_this._subscription.id,pwd:_this._subscription.unsubscribe_pwd});
	   }else{
	    var ext = $.extend({},_this._subscription);
		ext.entryid = _this.entryid;
	    ext.flt = appdb.utils.base64.encode(ext.flt);
	    _this._model.insert(ext);
	   }
	   e.preventDefault();
	   return false;
       };
   }(this)));
  this.initModel();
  if(this.canUseService(false) && this._autoLoad === true){
   this.load();
  }else{
   this.render();
  }
  
 };
 this.init = function(){
  this.dom = $(o.container);
  this.entryid = parseInt(o.entryid);
  this.displayHelp = ( (typeof(o.displayhelp) === "boolean")?o.displayhelp:true );
  this._model = o.model;
  this._autoLoad = ( (typeof(o.autoload) === "boolean")?o.autoload:true );
  this._defaultState = o.defaultstate;
  
  this._subscription.subjecttype = o.subjecttype || "app-entry";
  this._subscription.name = o.name || "Software entry subscription";
  this._subscription.events = NewsEventType.E_DELETE | NewsEventType.E_UPDATE | NewsEventType.E_INSERT_COMMENT | NewsEventType.E_INSERT_CONTACT;
  this._subscription.delivery = NewsDeliveryType.D_DAILY_DIGEST;
  if(!this.entryid){
   appdb.debug("No entry id given for entry subscription.The component will not render");
   this.load = function(){};
   this.render = function(){};
   return;
  }else{
   switch(this._subscription.subjecttype){
    case "person-entry":
     this._subscription.flt = "=person.id:"+this.entryid+" id:SYSTAG_FOLLOW";
     break;
    case "app-entry":
    default:
     this._subscription.flt = "=application.id:"+this.entryid+" id:SYSTAG_FOLLOW";
     break;
   }
  }
  this._initContainer();
 };
 this.init();
},{
    NewsEventType : {E_INSERT : 1, E_UPDATE : 2, E_DELETE : 4,  E_INSERT_COMMENT : 8, E_INSERT_CONTACT : 16 ,
        has : function(val,en){return (( val & en ) == en );}, //Checks whether an enumerated variable has the given enumeration
        split : function(val){ //Takes an enumerated variable and split it into an array.
            var res = {}, en = appdb.components.MailSubscription.NewsEventType;
            for(var e in en){
                if(typeof e === "function"){
                    continue;
                }
                res[en[e]] = en.has(val,en[e]);
            }
            return res;
    }},
    NewsDeliveryType : {D_NO_DIGEST : 1, D_DAILY_DIGEST : 2, D_WEEKLY_DIGEST : 4, D_MONTHLY_DIGEST : 8,
        has : function(val,en){return (( val & en ) == en );},//Checks whether an enumerated variable has the given enumeration
        split : function(val){ //Takes an enumerated variable and split it into an array.
            var res = {}, en = appdb.components.MailSubscription.NewsDeliveryType;
            for(var e in en){
                if(typeof e === "function"){
                    continue;
                }
                res[en[e]] = en.has(val,en[e]);
            }
            return res;
    }}
  });

appdb.components.NameAvailability = appdb.ExtendClass(appdb.Component,"appdb.components.NameAvailability",function(o){
	this.input = undefined;
	this.originalname = undefined;
	this.ctime = -1;
	this.reset = function(){
		if(this.input){
			$(this.input).unbind("keyup").unbind("mouseup").unbind("change");
		}
		$(this.dom).empty();
	};
	this.render = function(d){
		this._initContainer();
	};
	this.load = function(q){
		if(typeof q === "string"){
			q = {name:q,appid:this.appid};
		}
		q = q || {};
		if(typeof q.name === "undefined"){
			q.name = '';
		}
		if(typeof q.appid === "undefined"){
			q.appid = this.appid;
		}
		this._model.get(q);
	};
	this._validating = function(){
		$(this.dom).find(".validatordisplay").hide();
		$(this.dom).find(".validatorresult").hide();
		$(this.dom).find(".validatorprogress").show();
	};
	this._validationError = function(error){
		$(this.dom).find(".validatorprogress").hide();
		$(this.dom).find(".validatordisplay").show();
		$(this.dom).find(".validatorresult").show();
		$($(this.dom).find(".validatorresult").find("span")[0]).empty().append(error);
        $($(this.dom).find(".validatorresult")[0]).removeClass("valid").addClass("invalid");
		this.publish({event:"error",value : {name : $(this.input).val(),"error" : error}});
	};
    this._showDescription = function(elem,data){
        setTimeout(function(){
              dijit.popup.open({
                parent: elem,
                popup:  new dijit.TooltipDialog({content:"<div class='validator_popup'>"+ data + "</div>"}),
                around: elem,
                orient: {BR:'TR'}
            });
        },10);
    };
	this._validationResult = function(reason,isvalid,description){
		isvalid = (typeof isvalid ==="undefined")?true:isvalid;
		$(this.dom).find(".validatorprogress").hide();
		$(this.dom).find(".validatordisplay").show();
		$(this.dom).find(".validatorresult").show();
		$($(this.dom).find(".validatorresult").find("span")[0]).empty();
		if(isvalid===true){
			if(description){
                $($(this.dom).find(".validatorresult")[0]).removeClass("invalid").removeClass("valid").addClass("valid_warning");
                $($(this.dom).find(".validatordescription")[0]).attr("src","/images/question_mark.gif").unbind('click').click((function(_this){
                    return function(){
                        _this._showDescription($(_this.dom).find(".validatordescription")[0],description);
                    };
                })(this)).css("display","inline-block");
                $($(this.dom).find(".validatorresult").find("span")[0]).empty().append("Warn : "+reason);
            }else{
                $($(this.dom).find(".validatorresult")[0]).removeClass("invalid").removeClass("valid_warning").addClass("valid");
                 $($(this.dom).find(".validatorresult").find("span")[0]).empty().append(reason);
                $($(this.dom).find(".validatordescription")[0]).hide();
            }
			this.publish({event:"valid",value : {name : $(this.input).val()}});
		}else{
			$($(this.dom).find(".validatorresult")[0]).removeClass("valid").removeClass("valid_warning").addClass("invalid");
            if(description){
                $($(this.dom).find(".validatordescription")[0]).attr("src","/images/question_mark.gif").unbind('click').click((function(_this){
                    return function(){
                        _this._showDescription($(_this.dom).find(".validatordescription")[0],description);
                    };
                })(this)).css("display","inline-block");
                $($(this.dom).find(".validatorresult").find("span")[0]).empty().append(reason);
            }else{
                $($(this.dom).find(".validatordescription")[0]).hide();
            }
			this.publish({event:"invalid",value : {name : $(this.input).val(), "reason" : reason}});
		}
	};
	this._prevalidate = function(){
		var n = $(this.input).val();
		if($.trim(n)===''){
			this._validationResult("Empty name",false,"The software name must not be empty");
		}else if($.trim(this.originalname)==="" || $.trim(n.toLowerCase())!==$.trim(this.originalname.toLowerCase())){
			setTimeout((function(_this,_name){
				_this.ctime++;
				var tcount = _this.ctime;
				return function(){
					if(_this.ctime>tcount){
						return;
					}
					_this.ctime=-1;
					_this._validating(_name);
					_this.load({name:_name});
				};
			})(this,n),300);
		}else{
			$(this.dom).find(".validatorresult").hide();
		}
	};
	this._initContainer = function(){
		this.reset();
		var div = document.createElement("div"), result=document.createElement("div"), 
			display = document.createElement("div"), a = document.createElement("a"),
			progress = document.createElement("div"), img = document.createElement("img"),
            description = document.createElement("img");
		$(div).addClass("namevalidator");
		$(progress).addClass("validatorprogress");
		$(display).addClass("validatordisplay");
        $(description).addClass("validatordescription").attr("src","/images/question_mark.gif").attr("title","Click to view description").hide();
		$(result).addClass("validatorresult").append("<span></span>").append(description);
        
        $(display).append($(a));
		$(a).attr("href","#").attr("title","Click to check if the name you typed is available for use or another software has already claimed it.").
			append("Check name availability").
			click((function(_this){
				return function(){
					_this._prevalidate();
				};
			})(this)).hide();
		$(img).attr("src","/images/ajax-loader-small.gif").attr("border","0");
		var sp = document.createElement("span");
		$(sp).text("checking");
		$(progress).empty().append(img).append(sp).hide();
		

		$(div).append(display).append(progress).append(result);
		$(this.dom).append(div);
		$(this.input).keyup((function(_this){
			return function(){
				_this._prevalidate();
			};
		})(this));
		$(this.input).change((function(_this){
			return function(){
				_this._prevalidate();
			};
		})(this));
		$(this.input).mouseup((function(_this){
			return function(){
				_this._prevalidate();
			};
		})(this));
	};
	this._init = function(){
		if(typeof o.container==="string"){
			this.dom = $(o.container);
		}else{
			this.dom = o.container;
		}
		this.input = o.input;
		this.originalname = $(this.input).val();
		if($.trim(this.originalname)===''){
			this.originalname=null;
		}
		this.appid = o.appid || "";
		this._initContainer();
		this._model = new appdb.model.NameAvailability();
		this._model.subscribe({event : "select", callback : function(v){
				if(this.ctime>-1){
					return;
				}
				if($.trim($(this.input).val())===''){
					this._prevalidate();
				}else if(v.error){
					this._validationResult(v.error,false,v.reason);
				}else if(v.warning){
					this._validationResult("Available",true,v.warning);
				}else{
                    this._validationResult("Available",true);
                }
		}, caller : this});
	};
	this._init();
});

appdb.components.RelatedApplications = appdb.ExtendClass(appdb.Component,"appdb.components.RelatedApplications",function(o){
	this._data = [];
	this._timer = null;
	this._createItem = function(d){
		d = d || {};
		d = d.application;
		
		var doc = document, a, img, span, span1,div, logo, 
			ishttps =  Boolean(appdb.config.https) && true, 
			prot, title = '';
			
		title = d.name;
		//Set UI if user can retrieve moderated applications
		if(d.moderated){
			if (d.moderated === "true") {
				$(div).addClass("moderated");
				title = "This software has been moderated.";
				$(div).attr("title",title);
			}
		}
		//Set UI if user can retrieve deleted applications
		if(d.deleted){
			if (d.deleted === "true") {
				$(div).addClass("deleted");
				title = "This software has been deleted.";
				$(div	).attr("title",title);
			}
		}
		
		div = doc.createElement("div");
		$(div).addClass("item");
		if(d.category){
			d.category = $.isArray(d.category)?d.category:[d.category];
			for(var c = 0; c<d.category.length; c++) {
				if (d.category[c].primary && d.category[c].primary === "true") {
					d.primaryCategory = d.category[c].val().toLowerCase();
					d.primaryCategoryId = d.category[c].id;
					break;
				}
			}
			if( parseInt(d.primaryCategoryId) === 34){
				d.contentType = "vappliance";
			}else{
				d.contentType = "software";
			}
		}
		
		a = $(doc.createElement("a")).attr("title",title);
		$(a).addClass("itemlink");
		$(a).click((function(_this,data){
			return function(){
				 if(_this.parent && _this.parent.views.pager){
					_this.publish({event:"itemclick",value : data});
				 }else if( d.contentType === "vappliance" ) {
					 appdb.views.Main.showVirtualAppliance(data);
				 }else{
					appdb.views.Main.showApplication(data);
				 }
			};
		})(this,d));
		logo =  d.logo;
		if(logo){
			prot = logo.substr(4,5);
			if(ishttps===true && prot === ":"){
				logo = "https" + logo.substr(4,logo.length);
			}else if(prot==="https"){
				logo = "http:" + logo.substr(5,logo.length);
			}
		}
		img = $(doc.createElement("img"));
		$(img).attr("src",((logo)?"/apps/getlogo?req="+encodeURI(d.lastUpdated)+"&id="+d.id: ((d.primaryCategory)?appdb.config.images[d.primaryCategory]:((d.tool==="true")?(appdb.config.images.tools||appdb.config.images.tool):(appdb.config.images.applications||appdb.config.images.application))))).
			addClass("itemimage");
		$(a).append(img);
		span = $(doc.createElement("span")).append(d.name.substring(0,45)+(d.name.length>45?'...':'')).addClass("itemname");
		span1 = $(doc.createElement("span")).append(unescape(d.description.substring(0,80))+((d.description.length>80)?'...':'')).addClass("itemsorttext");
		
		$(a).append(span).
			append(span1);
		$(div).append($(a));
		if(d.url){
			u = ($.isArray(d.url))?d.url:[d.url];
		}
		$(div).append($(a));
		return div;
	};
	this._createPager = function(){
		var div = document.createElement("div"), 
			previous = document.createElement("div"), aprev = document.createElement("a"), 
			anext = document.createElement("a"), next = document.createElement("div"), 
			counter =  document.createElement("div");
		
		$(previous).addClass("previous").append(aprev);
		$(next).addClass("next").append(anext);
		$(counter).addClass("counter");
		$(div).addClass("pager").append(previous).append(counter).append(next);
		
		$(counter).append("<span>"+(this.currentPage+1)+ " <b>-</b> " + this._data.length+"</span>");
		
		//Check if "Previous" button should be rendered
		if(this.currentPage>0){
			$(aprev).attr("href","").append("<span>&#x25C0;</span>").bind("click", (function(_this){
				return function(){
					setTimeout(function(){
						$(_this.dom).find("ul:last").animate({opacity:0.2},200,function(){
							_this.render((_this.currentPage-1) || 0);
						});
					},1);
					return false;
				};
			}(this)));
		}else{
			$(aprev).addClass("disabled");
		}
		
		//Check if "Next" button should be rendered
		if(this.currentPage<(this._data.length-1)){
			$(anext).attr("href","").append("<span>&#x25B6;</span>").bind("click",(function(_this){ 
				return function(){
					setTimeout(function(){
						$(_this.dom).find("ul:last").animate({opacity:0.2},200,function(){
							_this.render(_this.currentPage+1);
						});
					},1);
					return false;
				};
			})(this));
		}else{
			$(anext).addClass("disabled");
		}
		
		return div;
	};
	this.render = function(page){
		page = (typeof page === "undefined")?this.currentPage:page;
		this.currentPage = page;
		if(page> this._data.length) page = 0;
		
		var d = this._data[page] || [], i, ul = document.createElement("ul"), li, len = d.length;
		if(this.listClass) $(ul).addClass(this.listClass);
		
		//If empty display empty message
		if(len===0){
			this._displayEmpty();
			return;
		}
		
		//Set current page
		for(i=0; i<this._data.length; i+=1){
			this._data[i].__currentPage = false;
			if(i==page-1) this._data[i].__currentPage = true;			
		}
		//Render items of page
		for(i = 0; i<len; i+=1){
			li = document.createElement("li");
			if(this.itemClass) $(li).addClass(this.itemClass);
			$(li).append(this._createItem(d[i]));
			$(ul).append(li);
		}
		//If set preserveHeight=true then add dummy items.
		if(this.preserveHeight && len<this.pageLength && this._data.length>1){
			for(i=0; i<(this.pageLength-len); i+=1){
				li = document.createElement("li");
				$(li).addClass("dummy");
				$(ul).append(li);
			}
		}
		$(ul).css("opacity",0.2);
		$(this.dom).empty().append(ul);
		$(ul).animate({opacity:1},200);
		
		//Add link "Show All" if possible
		if(this._data && this._data.length>0 && this.canShowAll){
			var show = document.createElement("div"),
			a = document.createElement("a");
			$(a).attr("href","#").attr("title","Click to view the list of all related software").append("<span>Show all</span>").click((function(_this){
				return function(){
					appdb.views.Main.showRelatedApps({appid:_this._data[0][0].parentid},{mainTitle:'Suggested',appname:_this._data[0][0].parentname});
					return false;
				};
			})(this));
			$(show).addClass("showall").append(a);
			$(this.dom).append(show);
		}
		//Add pager if needed
		if(this._data.length>1){
			$(this.dom).append(this._createPager());
		}
		
		this._startInterval();
	};
	//Group retrieved data into sub lists of pagelength
	this._pagifyData = function(d){
		d = d || [];
		if(d.application){
			d = ($.isArray(d.application))?d:[d];
		}
		this._data = [];
		if(!this.hasPager){
			this._data = [d];
			return;
		}
		for(var i=0; i<d.length; i+=this.pageLength){
			if(d.length>i){
				this._data[this._data.length] = d.slice(i,this.pageLength+i);
			}
		}
		
	};
	this._startInterval = function(){
		//Iniate timer when set pageInterval>=1 (secs) and pages>1 and timer not set
		if(this.pageInterval && this.pageInterval>=1 && this._timer === null && this._data.length>1){
			$(this.dom).unbind("mouseover.timer");
			$(this.dom).bind("mouseover.timer",(function(_this){
				return function(){
					if(_this._timer){
						clearInterval(_this._timer);
						_this._timer = null;
					}
				};
			})(this));
			
			this._timer = setInterval((function(_this){
				return function(){
					if( _this._timer ) {
						//if current pages is the last one then render the first page
						if(_this.currentPage==(_this._data.length-1)){
							_this.render(0);
						}else{
							_this.render(_this.currentPage+1);
						}
					}
				};
			})(this),(this.pageInterval*1000));
		}
	};
	this._displayLoading = function(isVisible){
		if(this.loaderHtml && isVisible){
			$(this.dom).empty().append(this.loaderHtml);
		}else if(!isVisible){
			$(this.dom).find(".loader:last").remove();
		}
	};
	this._displayError = function(err){
	};
	this._displayEmpty = function(){
		var div = document.createElement("div");
		if(this.emptyClass) $(div).addClass(this.emptyClass);
		if(this.emptyHtml) $(div).append(this.emptyHtml);
		$(this.dom).empty().append(div);
	};
	this.load = function(id){
		this._model.subscribe({event: "beforeselect", callback: function(v) {
				this._displayLoading(true);
		}, caller: this}).subscribe({
			event: "select", callback: function(v) {
				this._displayLoading(false);
				this._loadedData = v;
				this._pagifyData(v.relatedapp);
				this.render();
				this.publish({event:"load",value: (v.relatedapp || [])});
		}, caller: this}).subscribe({
			event:"error", callback: function(v){
				this._displayLoading(false);
				this._displayError(v);
		}, caller: this});
		this._model.get({appid:id});
		appdb.pages.application.requests.register(this._model,"relatedapps");
	};
	this._init = function(){
		o = o || {};
		
		//Setup container
		if(typeof o.container==="string"){
			this.dom = $(o.container);
		}else{
			this.dom = o.container;
		}
		
		//loading user options and defaults
		this.currentPage = 0;
		this.pageCount = 1;
		this.pageLength = o.pageLength || appdb.components.RelatedApplications.pageLength;
		this.hasPager = o.hasPager || appdb.components.RelatedApplications;
		this.hasInterval = o.hashInterval || appdb.components.RelatedApplications.hasInterval;
		this.itemClass = o.itemClass || appdb.components.RelatedApplications.itemClass;
		this.listClass = o.listClass || appdb.components.RelatedApplications.listClass;
		this.errorClass = o.errorClass || appdb.components.RelatedApplications.errorClass;
		this.emptyClass = o.emptyClass || appdb.components.RelatedApplications.emptyClass;
		this.preserveHeight = o.preserveHeight || appdb.components.RelatedApplications.preserveHeight;
		this.errorHtml = o.errorHtml || appdb.components.RelatedApplications.errorHtml;
		this.emptyHtml = o.emptyHtml || appdb.components.RelatedApplications.emptyHtml;
		this.loaderHtml = o.loaderHtml || appdb.components.RelatedApplications.loaderHtml;
		this.pageInterval = o.pageInterval || appdb.components.RelatedApplications.pageInterval;
		this.usageInterval = o.usageInterval || appdb.components.RelatedApplications.usageInterval;
		this.application = o.application || {};
		this.canShowAll = o.canShowAll || appdb.components.RelatedApplications.canShowAll;
		//Model RelatedApplications
		this._model = new appdb.model.RelatedApplications();
	};
	this._init();
},{
	hasPager : true, // use small pager on bottom of the control
	canShowAll : true,
	pageLength : 4, // count of items to be displayed
	pageInterval : 3, // auto paging after secs. If 0 then fetaure is disabled
	itemClass : "", // classname of each item in the list
	listClass : "itemgrid",// the class name of the list
	errorClass : "error", // the class name of the error message
	emptyClass : "empty", // the class name of the empty data message
	preserveHeight : true, // If true it appends dummy items to preserve items' count=pageLenth
	errorHtml : "<span></span>", //TODO: Html Error message to duisplay when an error occur (possible on server side)
	emptyHtml : "<span>No suggestions for this item</span>",
	loaderHtml : "<div class='loader'>Loading...</div>" //Html to display while loading data from server
});

appdb.components.UserRequests = appdb.ExtendClass(appdb.Component, "appdb.components.UserRequests", function(o){
	this.render = function(d){
		this.views.requestList.render(d);
		this._renderEmpty(this.views.requestList.isEmpty());
	};
	this._renderEmpty = function(display){
		if(display){
			$(this.dom).find(".main .empty").show();
		}else{
			$(this.dom).find(".main .empty").hide();
		}
	};
	this.load = function(){
		this.clearEvents();
		this._model = new appdb.model.UserRequests();
		this._model.subscribe({event: "beforeselect", callback: function(v){
		}, caller: this});
		this._model.subscribe({event: "select", callback: function(v){
				this.render(v.userrequest);
		}, caller: this});
		this._model.subscribe({event: "beforeupdate", callback: function(v){
				var status = ((v.query.stateid==2)?"accept":"reject");
				this.views.requestList.setStatus(v.query.requestid,status);
		}, caller: this});
		this._model.subscribe({event: "update", callback: function(v){
			var status = ((v.state==2)?"accepted":((v.state==3)?"rejected":"error"));
			this.views.requestList.setStatus(v.id,status);
		},caller: this});
		this.views.requestList.clearObserver();
		this.views.requestList.subscribe({event: "accept", callback: function(req){
			this._model.update({requestid: req.id, stateid:2});
		}, caller: this}).subscribe({event: "reject", callback: function(req){
			this._model.update({requestid: req.id, stateid:3});
		}, caller: this}).subscribe({event: "changed", callback: function(v){
			this._renderEmpty(this.views.requestList.isEmpty());
		}, caller: this});
		this._model.get();
	};
	this._initContainer = function(){
		
	};
	this._init = function(){
		var v = {};
		
		//Setup container
		if(typeof o.container==="string"){
			this.dom = $(o.container);
		}else{
			this.dom = o.container;
		}
		$(this.dom).find(".main").append(appdb.components.UserRequests.Empty);
		v.requestList = new appdb.views.UserRequests({container: $(this.dom).find(".main .list")[0]});
		this.views = v;
	};
	this._init();
},{
	MessageDialog: null,
	Empty : "<div class='empty'>There are no pending requests</div>"
});

appdb.components.RequestJoinContacts = appdb.ExtendClass(appdb.Component, "appdb.components.RequestJoinContacts", function(o){
	this.currentAppId = -1;
	this.currentApplication = null;
	this._checkMessageLength = function(val){
		if(appdb.components.RequestJoinContacts.MaxMessageLength){
			var len = parseInt(appdb.components.RequestJoinContacts.MaxMessageLength);
			if(len>0){
				return val.length<=len;
			}
		}
		return true;
	};
	this._validateMessage = function(msg){
		this._displayError(false);
		if($.trim(msg)===""){
			return true;
		}
		if(this._checkMessageLength(msg)=== false){
			this._displayError("<img src='/images/stop.png' alt='' border='0'/><span>Message should be less than " + appdb.components.RequestJoinContacts.MaxMessageLength + " characters long</span");
			return false;
		}
		return true;
	};
	this._displayError = function(err){
		var e = $(this.dom).find(".footer>.actions>.errormessage");
		if(e.length>0){
			$(e).empty();
		}
		if(e.length === 0){
			return;
		}
		
		if(!err){
			$(e).css({"display":"none"});
		}else{
			$(e).css({"display":"inline-block"}).append(err);
		}
		return;
	};
	this._renderActions = function(){
		var div = document.createElement("div"), submit = document.createElement("div"), cancel = document.createElement("div"), error = document.createElement("span");
		$(this.dom).find(".footer").empty();
		$($(this.dom).find(".footer")[0]).append($(div).addClass("actions"));
		$(div).append($(error).addClass("errormessage")).append($(submit).addClass("action")).append($(cancel).addClass("action"));
		
		new dijit.form.Button({
			label: "Submit",
			onClick: (function(_this){
				return function(){
					var msg = $(_this.dom).find(".main .message textarea:last").val();
					msg = msg || "";
					if(_this._validateMessage(msg) === false){
						return;
					}
					if($.trim(msg)!==""){
						msg = appdb.utils.base64.encode(msg);
					}
					_this._model.insert({appid:_this.currentAppId, message: msg});
				};
			})(this)
		},submit);
		
		new dijit.form.Button({
			label: "Cancel",
			onClick: function(){
				if( appdb.components.RequestJoinContacts.Dialog ){
					appdb.components.RequestJoinContacts.Dialog.hide();
				}
			}
		},cancel);
	};
	this._renderRecipients = function(){
		var ul = $(this.dom).find(".main .recipients ul:last")[0];
		var li = null, div = null, label = null, 
			rs = this.recipients, len = ((rs)?(rs.length ||0):0), i;
		
		//Clear previous rendering if any
		$(ul).find("li div").each(function(i,el){
			if( dijit.byNode(el) ){
				dijit.byNode(el).destroyRecursive(false);
			}
		});
		$(ul).empty();
		
		for(i=0; i<len; i+=1){
			li = document.createElement("li");
			div = document.createElement("div");
			label = document.createElement("label");
			$(label).attr("for",rs[i].name).text(rs[i].display);
			$(ul).append($(li).append(div).append(label));			
		}
	};
	this.render = function(status){
		switch(status){
			case "fetching":
				$(this.dom).addClass("fetchingstatus").find(".status").empty().append(appdb.components.RequestJoinContacts.Messages.FetchingStatus);
				break;
			case "unauthorized":
				$(this.dom).addClass("fetchingstatus").find(".status").empty().append(appdb.components.RequestJoinContacts.Messages.FetchingStatus);
				break;
			case "joined":
				$(this.dom).addClass("fetchingstatus").find(".status").empty().append(appdb.components.RequestJoinContacts.Messages.AlreadyJoined);
				break;
			case "pending":
				$(this.dom).addClass("fetchingstatus").find(".status").empty().append(appdb.components.RequestJoinContacts.Messages.PendingRequest);
				break;
			case "submit":
				$(this.dom).addClass("fetchingstatus").find(".status").empty().append(appdb.components.RequestJoinContacts.Messages.SubmitRequest);
				break;
			case "success":
				$(this.dom).addClass("fetchingstatus").find(".status").empty().append(appdb.components.RequestJoinContacts.Messages.SubmitSuccess);
				break;
			case "error":
				$(this.dom).addClass("fetchingstatus").find(".status").empty().append(appdb.components.RequestJoinContacts.Messages.Error);
				break;
			default:
				$(this.dom).removeClass("fetchingstatus").find(".status").empty();
				$(this.dom).find("textarea").val("").unbind("keydown").unbind("change").bind("keydown",(function(_this){ 
					return function(){
						_this._validateMessage($(this).val());
					};
				})(this)).bind("change",(function(_this){ 
					return function(){
						_this._validateMessage($(this).val());
					};
				})(this));
				this._renderActions();
				this._displayError(false);
				break;
		}
		return this;
	};
	this.show = function(){
		if(this.renderInline === false){
			if($.trim(appdb.components.RequestJoinContacts.Dialog)!==''){
				appdb.components.RequestJoinContacts.Dialog.hide();
				appdb.components.RequestJoinContacts.Dialog.destroyRecursive(false);
			}
			if ( ! this._container ) {
					appdb.components.RequestJoinContacts.Dialog = new dijit.Dialog({
					title: appdb.components.RequestJoinContacts.Title + " " + this.currentApplication.name,
					content: $(this.dom)[0]
				});
				appdb.components.RequestJoinContacts.Dialog.show();
			} else {
				$(this._container).empty().append(this.dom);
			}	
		}
	};
	this.load = function(app){
		if(typeof app === "object"){
			this.currentAppId = app.id;
			this.currentApplication = app;
		}else{
			this.currentAppId = app;
		}
		this.clearEvents();
		this._model = new appdb.model.JoinContacts();
		this._model.subscribe({event: "beforeselect", callback: function(v){
				this.render("fetching").show();
		}, caller: this});
		this._model.subscribe({event: "select", callback: function(v){
				var response = ((v)?v.response:"") || "";
				this.render(response);
		}, caller: this});
		this._model.subscribe({event: "beforeinsert", callback: function(v){
				this.render("submit");
		}, caller: this});
		this._model.subscribe({event: "insert", callback: function(v){
				this.render("success");
		}, caller: this});
		this._model.subscribe({event: "error", callback: function(v){
				
		}, caller: this});
		this._model.get({appid: this.currentAppId});
	};
	this._initContainer = function(){
		var status = document.createElement("div"), header = document.createElement("div"), main = document.createElement("div"), 
			footer = document.createElement("div"), headerText = document.createElement("div"),
			recipients = document.createElement("div"), recipientsTitle = document.createElement("div"),
			message = document.createElement("div"), messageTitle = document.createElement("div");
		
		$(headerText).addClass("description").append(appdb.components.RequestJoinContacts.Description);
		$(recipientsTitle).addClass("title").append(appdb.components.RequestJoinContacts.RecipientsDescription);
		$(recipients).addClass("recipients").append(recipientsTitle).append(document.createElement("ul"));
		$(messageTitle).addClass("title").append(appdb.components.RequestJoinContacts.MessageDescription);
		$(message).addClass("message").append(messageTitle).append(document.createElement("textarea")).append(((appdb.components.RequestJoinContacts.MaxMessageLength)?" <span class='descriptor' >limit " + appdb.components.RequestJoinContacts.MaxMessageLength + " characters</span>":""));
		
		$(this.dom).addClass("joinapplication").addClass("fetchingstatus").append(
			$(status).addClass("status")).append(
			$(header).addClass("header").append(
			headerText)).append(
			$(main).addClass("main").append(recipients).append(message)).append(
			$(footer).addClass("footer"));
		this._renderRecipients();
	};
	this._init = function(){
		o = o || {};
		
		//Setup container
		if(typeof o.container==="string"){
			this.dom = $(o.container);
			this.renderInline = true;
		}else{
			this.dom = document.createElement("div");
			this.renderInline = false;
		}
		
		this.messages = $.extend((o.messages || {
			FetchingStatus: "",
			Unauthorized: "",
			PendingRequest: "",
			AlreadyJoined: "",
			SendingRequest: ""}),appdb.components.RequestJoinContacts.Messages);
		this.recipients = (o.recipients || appdb.components.RequestJoinContacts.Recipients);
		this._initContainer();
	};
	this._init();
},{
	Dialog: null,
	Recipients : [
		{name: "owner", display: "Software entry owner", value: "owner", selected: true, enabled: false},
		{name: "manager", display: "Management team", value: "manager", selected: true, enabled: false},
		{name: "admin", display: "Administrators", value: "admin", selected: true, enabled: false}
	],
	Messages : {
		FetchingStatus: "<img src='/images/ajax-loader-small.gif' alt=''/><span>Loading...</span>",
		Unauthorized: "",
		PendingRequest: "You have already submitted a request to join this software team.",
		AlreadyJoined: "You are already in the software contacts.",
		SubmitRequest: "<img src='/images/ajax-loader-small.gif' alt=''/><span>Sending your request...</span>",
		SubmitSuccess: "Your request has been submitted."
	},
	Title: "Request to join as a contact to the software team",
	Description: "<span>Send a request to join as a contact to the software team. Your request will be sent to the associated people listed bellow. Please, feel free to type a message to the receivers of your request.</span>",
	RecipientsDescription: "<span>You request will be sent to:</span>",
	MaxMessageLength : 500,
	MessageDescription: "<span>Optional message to send with the request</span>"
});

appdb.components.SendMessageToContacts = appdb.ExtendClass(appdb.Component, "appdb.components.SendMessageToContacts", function(o){
	this.render = function(d){
		this.views.recipients.clearObserver();
		this.views.recipients.render(d);
		this.views.recipients.subscribe( {event: "select", callback: function(v){
				if(v.data){
					this._renderStatus();
				}
		}, caller: this});
		this._renderActions();
		
		$($(this.dom).find(".main .content .message textarea:last")[0]).unbind("keyup").bind("keyup",(function(_this){
			return function(){
				_this._validate();
			};
		})(this)).unbind("mouseup").bind("mouseup",(function(_this){
			return function(){
				_this._validate();
			};
		})(this)).val("");
		
	};
	this.show = function(d){
		if(this.renderInline === false){
			if($.trim(appdb.components.SendMessageToContacts.Dialog)!==''){
				appdb.components.SendMessageToContacts.Dialog.hide();
				appdb.components.SendMessageToContacts.Dialog.destroyRecursive(false);
			}
			if ( ! this._container ) {
				appdb.components.SendMessageToContacts.Dialog = new dijit.Dialog({
					title: appdb.components.SendMessageToContacts.Title,
					content: $(this.dom)[0],
					onCancel : (function(_this){
						return function(){
							_this.close();
						};
					})(this)
				});
				appdb.components.SendMessageToContacts.Dialog.show();
			} else {
				$(this._container).empty().append(this.dom);
			}
		}
		this.render(d);
	};
	this.close = function(){
		if($.trim(appdb.components.SendMessageToContacts.Dialog)!==''){
			appdb.components.SendMessageToContacts.Dialog.hide();
			appdb.components.SendMessageToContacts.Dialog.destroyRecursive(false);
			$("body").unbind("mouseup.dropdowngroup");
		}
	};
	this.load = function(id){
		if(this._model){
			this._model.destroy();
		}
		this.postInit();
		
		this._model = new appdb.model.AppplicationMessages();
		
		this._model.subscribe({event: "beforeselect", callback: function(v){
				this._renderStatus("fetching");
				$(this.dom).find(".footer .actions").hide();
		}, caller: this} );
		this._model.subscribe( {event: "select", callback: function(v){
				$(this.dom).find(".footer .actions").show();
				this._renderStatus();
				this.show(v.group);
		}, caller: this} );
		this._model.subscribe( {event: "beforeinsert", callback: function(v) {
				this._renderStatus("sending");
				$(this.dom).find(".footer .actions").hide();
		}, caller: this} );
		this._model.subscribe( {event: "insert", callback: function(v) { 
				if(v.error){
					return;
				}
				this._renderStatus("success");
		}, caller: this} );
		this._model.subscribe( {event: "error", callback: function(v){
				this._renderStatus("error",v.error);
				$(this.dom).find(".footer .actions").show();
		}, caller: this} );
		$(this.dom).find(".header, .main,.footer").show();
		$(this.dom).find(".sendmessagesuccess").remove();
		this._model.get({appid:id});
		
	};
	this._validate = function(){
		//Process message
		var msg = $.trim($(this.dom).find(".main .message textarea").val());
		if( msg === "" ){
			this._renderStatus("validation", "You must provide a message to send");
			$(this.dom).find(".limitdescription .used,.limitdescription .usedtext").css({"display":"none"});
			$(this.dom).find(".limitdescription .inittext").css({"display":"inline"});
			return false;
		}else if( msg.length > appdb.components.SendMessageToContacts.MaxMessageLength ) {
			this._renderStatus("validation", "Your message must be less than " + appdb.components.SendMessageToContacts.MaxMessageLength + " characters");
			$(this.dom).find(".limitdescription .used,.limitdescription .usedtext").css({"display":"inline"});
			$(this.dom).find(".limitdescription .used").text(appdb.components.SendMessageToContacts.MaxMessageLength);
			$(this.dom).find(".limitdescription .inittext").css({"display":"none"});
			return false;
		}
		$(this.dom).find(".limitdescription .used,.limitdescription .usedtext").css({"display":"inline"});
		$(this.dom).find(".limitdescription .used").text(msg.length);
		$(this.dom).find(".limitdescription .inittext").css({"display":"none"});
		
		//Process recipient
		if(!this.views.recipients.selected.get()){
			this._renderStatus("validation","You need to select a recipient first");
			return false;
		}
		
		this._renderStatus();
		return true;
	};
	this._sendMessage = function(){
		if( this._validate() ) {
			var msg = $(this.dom).find(".main .content .message textarea:last").val();
			this._model.insert({
				appid: this._model.getQuery().appid, 
				recipientid: this.views.recipients.selected.get().id, 
				message: appdb.utils.base64.encode(msg)
			});
		}
	};
	this._renderActions = function(){
		var actions = $(this.dom).find(".footer .actions:last")[0] , submit = document.createElement("div"), 
			cancel = document.createElement("div");
		if($(this.dom).find(".footer .actions .submit:last").length > 0 && dijit.byNode($(this.dom).find(".footer .actions .submit:last")[0])) {
			dijit.byNode($(this.dom).find(".footer .actions .submit:last")[0]).destroyRecursive(false);
		}
		if($(this.dom).find(".footer .actions .cancel:last").length > 0 && dijit.byNode($(this.dom).find(".footer .actions .cancel:last")[0])) {
			dijit.byNode($(this.dom).find(".footer .actions .cancel:last")[0]).destroyRecursive(false);
		}
		$(actions).append($(submit).addClass("submit")).append($(cancel).addClass("cancel"));
		new dijit.form.Button({
			label: "submit",
			onClick: (function(_this){
				return function(){
					_this._sendMessage();
				};
			})(this)
		},submit);
		
		new dijit.form.Button({
			label: "cancel",
			onClick: (function(_this){
				return function(){
					_this.close();
				};
			})(this)
		},cancel);
	};
	this._renderStatus = function(st,txt){
		var stdom = $(this.dom).find(".footer .status:last")[0];
		switch(st){
			case "fetching":
				$(stdom).empty().append(appdb.components.SendMessageToContacts.Messages.FetchingStatus);
				break;
			case "sending":
				$(stdom).empty().append(appdb.components.SendMessageToContacts.Messages.SendingMessage);
				break;
			case "validation":
				$(stdom).empty().append("<img src='/images/stop.png' alt='' border='0' /><span>"+txt+"</span>");
				break;
			case "error":
				$(stdom).empty().append("<img src='/images/error.png' alt='' border='0' /><span>"+txt+"</span>");
				break;
			case "success":
				$(this.dom).find(".header,.main,.footer").hide();
				$(this.dom).append(appdb.components.SendMessageToContacts.Messages.Success);
				this.show();
				break;
			default:
				$(stdom).empty();
				break;
		}
	};
	this._initContainer = function(){
		var header = document.createElement("div"), main = document.createElement("div"), 
			footer = document.createElement("div"), headerText = document.createElement("div"),
			recipients = document.createElement("div"), recipientTitle = document.createElement("div"),
			recipientList = document.createElement("div"), message = document.createElement("div"),
			messageTitle = document.createElement("div"), messageText = document.createElement("textarea"),
			status = document.createElement("div"), content = document.createElement("div"),
			actions = document.createElement("div");
		
		$(headerText).addClass("description").append(appdb.components.SendMessageToContacts.Description);
		
		$(recipients).addClass("recipients").
			append($(recipientTitle).addClass("title").append(appdb.components.SendMessageToContacts.RecipientTitle)).
			append($(recipientList).addClass("dropdowngroup"));
		
		$(message).addClass("message").
			append($(messageTitle).addClass("title").append(appdb.components.SendMessageToContacts.MessageTitle)).
			append($(messageText).addClass("text"));
		$(content).addClass("content").append(recipients).append(message);
		
		$(footer).append($(status).addClass("status")).append($(actions).addClass("actions"));
		
		$(messageTitle).find(".limit").append(appdb.components.SendMessageToContacts.MaxMessageLength);
		
		$(this.dom).addClass("sendmessagedialog").addClass("fetchingstatus").append(
			$(header).addClass("header").append(headerText)).append(
			$(main).addClass("main").append(content)).append($(footer).addClass("footer"));
		
		$(this.dom).find(".limitdescription .used, .limitdescription .usedtext").css({"display":"none"});
	};
	this.postInit = function(){
		$(this.dom).empty();
		this.messages = $.extend((o.messages || {
			FetchingStatus: "",
			Unauthorized: "",
			SendingMessage: ""}),appdb.components.RequestJoinContacts.messages);
		this._initContainer();
		
		this.views.recipients = new appdb.views.DropDownGroup({container:$(this.dom).find(".main .content .recipients .dropdowngroup:last")[0], itemType:"people"});
	};
	this._init = function(){
		o = o || {};
		
		//Setup container
		if(typeof o.container==="string"){
			this.dom = $(o.container);
			this.renderInline = true;
		}else{
			this.dom = document.createElement("div");
			this.renderInline = false;
		}
		this.postInit();
	};
	this._init();
},{
	Dialog: null,
	Messages: {
		FetchingStatus: "<img src='/images/ajax-loader-small.gif' alt='' border='0' /><span>Loading recipients list...<span>",
		Unauthorized: "",
		SendingMessage: "<img src='/images/ajax-loader-small.gif' alt='' border='0' /><span>Sending message...</span>",
		Success: "<span class='sendmessagesuccess' style='display: block;font-size: 16px;font-weight: bold;color: gray;text-align: center;height: 100px;width: 250px;margin: auto;top:25px;position: relative;'><img src='/images/yes.png' alt='' border='0' /><span >Your message has been sent</span></span>"
	},
	Title: "Send message to the software contacts",
	Description: "From this dialog you can select a related software contact from the list bellow and send a message to him/her.",
	RecipientTitle: "<span>Recipient:</span>",
	MessageTitle: "<span>Message:</span><span class='limitdescription'><span class='inittext'>limit </span><span class='used'></span><span class='usedtext'> of </span><span class='limit'></span><span > characters</span><span class='usedtext'> used</span></span>",
	MaxMessageLength: 2000
});

appdb.components.APIKeyHandler = appdb.ExtendClass(appdb.Component, "appdb.components.APIKeyHandler", function(o){
	this.loading = function(state){
		state = state || false;
		if(state){
			$(this.dom).find(".apikeylistcontainer").addClass("loading");
		}else{
			$(this.dom).find(".apikeylistcontainer").removeClass("loading");
		}
	};
	this.setupUsage = function(){
		var canuse = true;
		if(appdb.components.APIKeyHandler.canUse){
			if($.isFunction(appdb.components.APIKeyHandler.canUse)){
				canuse = appdb.components.APIKeyHandler.canUse();
			}else if(appdb.components.APIKeyHandler.canUse === false){
				canuse = false;
			}
		}
		if(canuse!==true){
			$(this.dom).find(".apikeylistcontainer").empty().append($(canuse));
			this.render = this.load = function(){};
		}
	};
	this.render = function(d){
		this.loading(false);
		var data = d.apikey || [];
		this.views.apikeylist.render(data);
		this.views.apikeylist.unsubscribeAll(this);
		this.views.apikeylist.
			subscribe({
				"event": "change",
				"callback": function(d){
					var nets = [], len = d.netfilter.length;
					for(var i=0; i<len; i+=1){
						nets.push(encodeURIComponent(d.netfilter[i].value));
					}
					this.loading(true);
					this._model.update({query:{"keyid":d.id}, data:  {data: JSON.stringify({"netfilters": nets})}});
				},	
				"caller": this
			}).
			subscribe({
				"event": "delete",
				"callback": function(d){
					this.loading(true);
					this._model.remove({"keyid": d.id});
				},
				"caller": this
			}).
			subscribe({
				"event": "generate",
				"callback": function(d){
					this.loading(true);
					this._model.insert({query: {}, data: {data: ""}});
				},
				"caller": this
			}).subscribe({
				"event": "insertuser",
				"callback": function(d){
					this.loading(true);
					this._modelAuthentication.insert({query: {}, data: {data: {key: d.key, pwd: d.pwd, name: d.name}}});
				},
				"caller": this
			}).subscribe({"event": "changepassword", "callback": function(d){
				this.loading(true);
				this._modelAuthentication.update({query:{}, data: {data: JSON.stringify(d)}});
				},"caller": this
			}).subscribe({"event": "requestpermisions", "callback": function(d){
				this.loading(true);
				this._modelAuthentication.update({query:{}, data: {data: JSON.stringify(d)}});
				},"caller": this
			}).subscribe({"event": "changename", "callback": function(d){
				this.loading(true);
				this._modelAuthentication.update({query:{}, data: {data: JSON.stringify(d)}});
				},"caller": this
			});
			$(this.dom).find(".apikeylistcontainer").append($("<div class='ajaxloader'></div>")).append($("<div class='loader'><div><img border='0' src='/images/ajax-loader-small.gif' /><div>Sending request...</div></div>"));
	};
	this.load = function(){
		if(this._model){
			this._model.unsubscribeAll(this);
			this._model.destroy();
		}
		if(this._modelAuthentication){
			this._modelAuthentication.unsubscribeAll(this);
			this._modelAuthentication.destroy();
		}
		this._model = new appdb.model.ApiKeyList();
		this._model.
			subscribe({
				"event": "select", 
				"callback": function(d){
					this.loading(false);
					this.render(d);
				}, 
				"caller": this}).
			subscribe({
				"event": "error",
				"callback": function(d){
					this.loading(false);
					var err = {
						"status": "An error occured",
						"description": d.error
					};
					//Setup error according to rest response type
					if(d && d.response){
						if(typeof d.response === "string" && $.trim(d.response) !== ""){
							err.description = d.response;
						}else if(d.response.error){
							err.description = d.response.error;
						}
					}
					//Display backend error message
					setTimeout(function(){(new appdb.views.ErrorHandler()).handle(err);},0);
				},
				"caller": this
			}).
			subscribe({
				"event": "insert",
				"callback": function(d){
					this.loading(false);
					this.render(d);
				},
				"caller": this
			}).
			subscribe({
				"event": "update", 
				"callback": function(d){
					this.loading(false);
					this.render(d);
				},
				"caller": this
			}).
			subscribe({
				"event": "remove",
				"callback": function(d){
					this.loading(false);
					this.render(d);
				},
				"caller": this
			});
			this._model.get();
			
			this._modelAuthentication = new appdb.model.ApiKeyAuthentication();
			this._modelAuthentication.subscribe({
				"event": "insert",
				"callback": function(d){
					this.loading(false);
					this.render(d);
				},
				"caller": this
			}).subscribe({
				"event": "error",
				"callback": function(d){
					this.loading(false);
					var err = {
						"status": "An error occured",
						"description": d.error
					};
					//Setup error according to rest response type
					if(d && d.response){
						if(typeof d.response === "string" && $.trim(d.response) !== ""){
							err.description = d.response;
						}else if(d.response.error){
							err.description = d.response.error;
						}
					}
					//Display backend error message
					setTimeout(function(){(new appdb.views.ErrorHandler()).handle(err);},0);
				},
				"caller": this
			}).subscribe({"event": "update", "callback": function(d){
				this.loading(false);
				this.render(d);
				},"caller": this
			});
	};
	this._initContainer = function(){
		var div = document.createElement("div");
		$(div).addClass("apikeylistcontainer");
		$(this.dom).append(div);
		this.setupUsage();
	};
	this._init = function(){
		var v = {};
		if(o && o.container){
			this.dom = $(o.container);
		}else{
			this.dom = $("#webapikeyhandler");
		}
		
		this._initContainer();
		v.apikeylist = new appdb.views.ApiKeyList({container: $(this.dom).find(".apikeylistcontainer:last")});
		this.views = v;
	};
	this._init();
},{
	canUse: function(){
		if(appdb.model.PrimaryContact.userPrimaryContact===null){
			return "<div class='nocontact'>You must set your primary e-mail contact in order to generate an API key.</div>";
		}
		return true;
	}
});
appdb.components.AccessTokenHandler = appdb.ExtendClass(appdb.Component, "appdb.components.AccessTokenHandler", function(o){
	this.options = {
		container: $(o.container),
		parent: null
	};
	this.loading = function(state){
		state = state || false;
		if(state){
			$(this.dom).find(".accesstokenlistcontainer").addClass("loading");
		}else{
			$(this.dom).find(".accesstokenlistcontainer").removeClass("loading");
		}
	};
	this.setupUsage = function(){
		var canuse = true;
		if(appdb.components.AccessTokenHandler.canUse){
			if($.isFunction(appdb.components.AccessTokenHandler.canUse)){
				canuse = appdb.components.AccessTokenHandler.canUse();
			}else if(appdb.components.AccessTokenHandler.canUse === false){
				canuse = false;
			}
		}
		if(canuse!==true){
			$(this.dom).find(".apikeylistcontainer").empty().append($(canuse));
			this.render = this.load = function(){};
		}
	};
	this.render = function(d){
		this.loading(false);
		if( this.handleError(d) ){
			return;
		}
		var data = d.accesstoken || [];
		data = $.isArray(data)?data:[data];
		
		this.views.accesstokenlist.render(data);
		this.views.accesstokenlist.unsubscribeAll(this);
		this.views.accesstokenlist.
			subscribe({
				"event": "change",
				"callback": function(d){
					var nets = [], len = d.netfilter.length;
					for(var i=0; i<len; i+=1){
						nets.push(encodeURIComponent(d.netfilter[i].value));
					}
					this.loading(true);
					this._model.update({query:{"tokenid":d.id}, data:  {data: JSON.stringify({"netfilters": nets})}});
				},	
				"caller": this
			}).
			subscribe({
				"event": "delete",
				"callback": function(d){
					this.loading(true);
					this._model.remove({"tokenid": d.id});
				},
				"caller": this
			}).
			subscribe({
				"event": "generate",
				"callback": function(d){
					this.loading(true);
					this._model.insert({query: {}, data: {data: ""}});
				},
				"caller": this
			});
			$(this.dom).find(".accesstokenlistcontainer").append($("<div class='ajaxloader'></div>")).append($("<div class='loader'><div><img border='0' src='/images/ajax-loader-small.gif' /><div>Sending request...</div></div>"));
	};
	this.handleError = function(d){
		if( !d.error && !(d.response && d.response.error) ){
			//No error occured
			return false;
		}
		var err = {
			"status": "An error occured",
			"description": d.error
		};
		//Setup error according to rest response type
		if(d && d.response){
			if(typeof d.response === "string" && $.trim(d.response) !== ""){
				err.description = d.response;
			}else if(d.response.error){
				err.description = d.response.error;
			}
		}
		//Display backend error message
		setTimeout(function(){(new appdb.views.ErrorHandler()).handle(err);},0);
		return true;
	};
	this.load = function(){
		if(this._model){
			this._model.unsubscribeAll(this);
			this._model.destroy();
		}
		this._model = appdb.model.AccessTokenList();
		this._model.
			subscribe({
				"event": "select", 
				"callback": function(d){
					this.loading(false);
					this.render(d);
				}, 
				"caller": this}).
			subscribe({
				"event": "error",
				"callback": function(d){
					this.loading(false);
					this.handleError(d);
				},
				"caller": this
			}).
			subscribe({
				"event": "insert",
				"callback": function(d){
					this.loading(false);
					this.render(d);
				},
				"caller": this
			}).
			subscribe({
				"event": "update", 
				"callback": function(d){
					this.loading(false);
					this.render(d);
				},
				"caller": this
			}).
			subscribe({
				"event": "remove",
				"callback": function(d){
					this.loading(false);
					this.render(d);
				},
				"caller": this
			});
			this._model.get();
	};
	this._initContainer = function(){
		var div = document.createElement("div");
		$(div).addClass("accesstokenlistcontainer");
		$(this.dom).append(div);
		this.setupUsage();
	};
	this._init = function(){
		this.dom = this.options.container;
		this._initContainer();
		this.views.accesstokenlist = new appdb.views.AccessTokenList({container: $(this.dom).find(".accesstokenlistcontainer:last"), parent: this});
	};
	this._init();
},{
	canUse: function(){
		if(appdb.model.PrimaryContact.userPrimaryContact === null){
			return "<div class='nocontact'>You must set your primary e-mail contact in order to generate an API key.</div>";
		}
		return true;
	}
});
appdb.components.Banner = appdb.ExtendClass(appdb.Component, "appdb.components.Banner", function(o){
	this.popupHtml = o.popup || null;
	this.dimensions = {cols: 4, rows: 4};
	this.currentData = [];
	this.ordering = {orderby: "rank", op: "DESC"};
	this.filter = "";
	this.viewType = "AppsList";
	this.modelType = "application";
	this.subtype = "";
	this.modelData = {}; 
	this.calcMaxWidth = function(){
		var w = $(this.dom).width();
		w = Math.ceil(w/this.dimensions.cols);
		return (w-15)+"px";
	};
	this.doPopup = function(){
		var data = $(this.popupHtml).clone();
		$(data).children("a").unbind("click").bind("click", function(ev){
			var el = $(this).data("click");
			if( $.trim(el) !== "" ){
				ev.preventDefault();
				$(el).click();
				return false;
			}
		});
		if( appdb.components.Banner.popup !== null){
			appdb.components.Banner.popup.cancel();
			dijit.popup.close(appdb.components.Banner.popup);
			appdb.components.Banner.popup.destroyRecursive(false);
		}
		appdb.components.Banner.popup =  new dijit.TooltipDialog({content : $(data)[0]});
		dijit.popup.open({
			parent : $(this.dom).children("a.more")[0],
			popup: appdb.components.Banner.popup,
			around : $(this.dom).children("a.more")[0],
			orient: {'BR':'TR','BL':'TL'}
		});
	};
	this.renderLoading = function(loading,text){
		loading = (typeof loading === "boolean")?loading:false;
		$(this.dom).children(".loader").remove();
		if( loading ){
			text = text || "...loading";
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).append(loader);
		}
	};
	this.renderAutoSize = function(d){
		this.views.list = [];
		var r, c, rl = this.dimensions.rows, cl = this.dimensions.cols;
		var data = d[this.modelType], li = null, dd = [], index=0;
		var maxwidth = this.calcMaxWidth();
		for(r = 0; r<rl; r+=1){
			li = document.createElement("li");
			$(li).addClass("row").append("<ul class='itemgrid'></ul>");
			$(this.dom).find(".bannerlist:last").append(li);
			this.views.list[r] = new appdb.views[this.viewType]({container: $(li).find("ul:last")[0]});
			dd = [];
			for(c=0; c < cl; c+=1){
				if(data.length === index){
					break;
				}
				dd.push(data[index]);
				index+=1;
			}
			this.views.list[r].render(dd);
		}
		$(this.dom).find(".row ul li.itemcontainer").css({"max-width":maxwidth});
	};
	this.renderPaged = function(d){
		this.views.list = [];
		var data = d[this.modelType], li = null;
		li = document.createElement("li");
		$(li).addClass("row").append("<ul class='itemgrid'></ul>");
		$(this.dom).find(".bannerlist:last").append(li);
		this.views.list.push(new appdb.views[this.viewType]({container: $(li).find("ul:last")[0]}));
		this.views.list[0].render(data);
		
	};
	this.hasSameData = function(d){
		d = d || {};
		var data = d[this.modelType] || [];
		data = $.isArray(data)?data:[data];
		if( data.length !== this.currentData.length ){
			this.currentData = data;
			return false;
		}
		var res = true;
		for(var i = 0; i<this.currentData.length; i+=1){
			if( (this.currentData[i].id && data[i].id ) && ( $.trim(this.currentData[i].id) === $.trim(data[i].id))){
				res = true;
			}else{
				this.currentData = data;
				return false;
				
			}
		}
		return res;
	};
	this.render = function(d,p){
		if(this.hasSameData(d) === true ) return;
		
		this._initContainer();
		$.each(this.views.list || [], function(i,e){
			e.reset();
			e = null;
		});
		
		var data = d[this.modelType];
		var datacount = d.count || 0;
		if( !data ){
			data = [];
		}
		this.currentData = data;
		if( this.autosize === true ){
			this.renderAutoSize(d);
		}else{
			this.renderPaged(d);
		}
		var ca = $(this.dom).children("a.more");
		if( $(ca).length > 0 ) {
			$(ca).unbind("click").bind("click",(function(_this){
				return function(){
					if( _this.popupHtml !== null ){
						setTimeout(function(){
							_this.doPopup();
						},1);
					}else{
						_this.publish({"event":"more"});
					}
				};
			})(this));
		}
		if( this.autosize === false ){
			if( !p ){
				var dq = this.getDataQuery();
				var pl = parseInt(dq.pagelength);
				datacount = parseInt(datacount);
				var pc = Math.ceil(datacount/pl);
				pc = (pc <= 1)?1:pc;
				p = {count: datacount,
					hasNext: true,
					length: pl,
					offset: 0,
					pageCount: pc,
					pageNumber: 0
				};
				this.views.pager.setPagingData(p);
			}
			this.renderPager(p,d);
			this.renderLoading(false);
		}
		this.publish({event:"select", value:{data:d,pager:p}});
	};
	this.renderPager = function(d,data){
		if( this.haspager === false) return;
		if( this.views.pagerview.inited !== true ) {
			this.views.pagerview.subscribe({event:"next",callback : function(){
				 this.views.pager.next();
			}, caller : this});
			this.views.pagerview.subscribe({event:"previous",callback : function(){
				 this.views.pager.previous();
			}, caller : this});
			this.views.pagerview.subscribe({event:"current",callback : function(v){
				this.views.pager.current(v);
			}, caller : this});
			this.views.pagerview.inited = true;
		}
		
		this.views.pagerview.render(d);
		if( d.pageCount > 1){
			$(this.dom).addClass("haspages");
		}else{
			$(this.dom).removeClass("haspages");
		}
	};
	this.load = function(q,d){
		q = q || this.getDataQuery();
		switch(this.type){
			case "people":
				this.views.viewType = "PeopleList";
				this.modelType = "person";
				this._model = new appdb.model.People(q);
				break;
			case "vos":
				this.type = "vo";
				this.views.viewType = "VOsList";
				this.viewType = "VOsList";
				this.modelType = "vo";
				if( $.trim(this.subtype) === "" ){
					this._model = new appdb.model.VOs(q,{action:"GET",async: true});
				}else{
					q.membership = this.subtype;
					this._model = new appdb.model.PeopleVOs(q,{action:"GET",async:true});
				}
				break;
			case "vappliance":
			case "software":
			default:
				this.type = "software";
				this.views.viewType = "AppsList";
				this.modelType = "application";
				if( $.trim(this.subtype) === "" ){
					this._model = new appdb.model.Applications(q,{action:"GET",async: true});
				}else{
					q.applicationtype = this.subtype;
					this._model = new appdb.model.PeopleApplications(q,{action:"GET",async:true});
				}
				break;
		}
		
        this._model.subscribe( {event: "beforeselect", callback: function(){
				this.renderLoading(true);
		}, caller: this} );
        this._model.subscribe( {event: "error", callback: function(err){
		}, caller: this} );
		this._model.subscribe({event: "select", callback: function(d){
		}, caller: this});
		if( this.views.pager ){
			if( typeof this.views.pager === "function" ){
				this.views.pager.reset();
			}
			this.views.pager = null;
		}
		this.views.pager = this._model.getPager().subscribe({event:'pageload',callback:function(d){
				var dd ={};
				dd[this.modelType] = d.data;
				this.render(dd,d.pager);
		},caller:this});
		if(typeof d === "undefined" ){
			this.renderLoading(true,"...Loading");
			this.views.pager.current();
		}else{
			this.render(d);
			this.publish({event: "select", value: d});
		}
	};
	this.getDataQuery = function(){
		var dq = $.extend(this.modelData,{flt: this.filter, pagelength: optQueryLen, pageoffset: 0});
		if( $.trim(this.ordering.orderby) !== "none"){
			dq.orderby = this.ordering.orderby;
			dq.orderbyOp = this.ordering.op;
		}
		if( this.autosize === true ){
			dq.pagelength = (this.dimensions.rows * this.dimensions.cols);
		}
		return dq;
	};
	
	this._initContainer = function(){
		$.each(this.views.list || [], function(i,e){
			e.reset();
			e = null;
		});
		this.views.list = [];
		$(this.dom).children(".bannerlist").remove();
		$(this.dom).children(".pager").remove();
		var list = document.createElement("ul");
		$(list).addClass("bannerlist");
		$(this.dom).addClass("banner").addClass(this.type).append(list);
		if( this.haspager === true ){
			var pagerview = $(document.createElement("div")).addClass("pager");
			$(pagerview).attr("id",this.id+"_pager");
			$(this.dom).append(pagerview);
			this.views.pagerview = new appdb.views.PagerPane({container: $(pagerview).attr("id")});
		}
	};
	this._init = function(){
		this.dom = $(o.container);
		this.id = o.id || "";
		this.type = $.trim(o.type).toLowerCase() || $.trim($(this.dom).data("type")).toLowerCase() || "software";
		this.subtype = $.trim(o.subtype).toLowerCase() || $.trim($(this.dom).data("subtype")).toLowerCase() || "";
		this.dimensions.rows = parseInt($(this.dom).data("rows"),10) || this.dimensions.rows;
		this.dimensions.cols = parseInt($(this.dom).data("cols"),10) || this.dimensions.cols;
		this.modelData = (o.modelData)?o.modelData:{};
		this.ordering.orderby = $(this.dom).data("orderby") || this.ordering.orderby;
		this.ordering.op = $(this.dom).data("orderbyop") || this.ordering.op;
		this.autosize =  $.trim(o.autosize).toLowerCase() || $.trim($(this.dom).data("autosize")).toLowerCase() || "true";
		if( this.autosize === "false" ){
			this.autosize = false;
		}else if( this.autosize === "true" ) {
			this.autosize = true;
		}else{
			this.autosize = true;
		}
		this.haspager =  $.trim(o.haspager).toLowerCase() || $.trim($(this.dom).data("haspager")).toLowerCase() || "false";
		if( this.haspager === "false" ){
			this.haspager = false;
		}else if( this.haspager === "true" ) {
			this.haspager = true;
		}else{
			this.haspager = false;
		}
		this.filter = $(this.dom).data("filter") || this.filter;
		if( $(this.dom).data("autoload") === true && o.autoload !== false){
			this.load();
		}
	};
	this._init();
},{
	popup: null
});

appdb.components.UserConnectedAcccounts = appdb.ExtendClass(appdb.Component, "appdb.components.UserConnectedAcccounts", function(o){
	o = o || {};
	this.options = {
		container: o.container,
		parent: o.parent,
		userid: o.userid || null,
		currentAccount: o.currentAccount || {},
		userAccounts: o.currentAccounts || []
	};
	this.markCurrentAccount = function(){
		var cid = $.trim(this.options.currentAccount.id);
		$.each(this.options.userAccounts, function(i, e){
			if( $.trim(e.id) === cid ){
				e.current = true;
			}else{
				e.current = false;
			}
		});
	};
	this.render = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		this.options.usersAccounts = d;
		this.markCurrentAccount();
		this.views.AccountList.render(d);
	};
	this.load = function(){
		this.render(userCurrentAccounts);
	};

	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.views = {};
		this.views.AccountList = new appdb.views.ConnectedAccountTypeList({
			container: $(this.dom).find(".useraccounts:first"),
			parent: this
		});
	};
	this._init();
});

appdb.components.InboxUnreadDispatcher = appdb.ExtendClass(appdb.Component, "appdb.components.InboxUnreadDispatcher", function(o){
	o = o || {};
	this.options = {
		interval: (o.interval || 10000),
		parameters: {folder:"inbox",unread: true,length: (o.pagelength || 20)},
		timer: null
	};
	this.start = function(){
		this.stop();
		this.options.timer = setTimeout((function(self){
			return function(){
				self.load();
			};
		})(this), this.options.interval);
	};
	this.stop = function(){
		if( this.options.timer !== null ){
			clearTimeout(this.options.timer);
			this.options.timer = null;
		}
	};
	this.load = function(){
		this.stop();
		this._model.get(this.options.parameters);
	};
	this.initModel = function(){
		if( this._model ){
			this._model.unsubscribeAll();
			this._model.destroy();
			this._model = null;
		}
		this._model = new appdb.model.UserInbox();
		this._model.subscribe({event: "beforeselect", callback: function(v){
			this.publish({event: "checking", value: true});
		}, caller: this})
		.subscribe({event: "select", callback: function(v){
			v = v || {};
			v.messages = v.messages || {count: 0};
			v.messages.message = v.messages.message || [];
			v.messages.message = $.isArray(v.messages.message)?v.messages.message:[v.messages.message];
			this.publish({event: "unread", value: v.messages});
			this.start();
		}, caller: this});
	};
	this._init = function(){
		this.initModel();
	};
	this._init();
});
appdb.components.EntityPrivileges = appdb.ExtendClass(appdb.Component, "appdb.components.EntityPrivileges", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		id: o.id || -1,
		modelType: null,
		hasPrivacy: (typeof o.hasPrivacy === "boolean")?o.hasPrivacy:false,
		isPrivate: (typeof o.isPrivate === "boolean")?o.isPrivate:false,
		canEditPrivacy: (typeof o.canEditPrivacy === "boolean")?o.canEditPrivacy:false,
		owner: o.owner || null,
		entityType: o.entityType || "sofware",
		entityData: o.entityData || {},
		groups: [],
		privs: [],
		privacy: null,
		permsStateCounter: 0
	};
	this.getEntityType = function(){
		return this.options.entityType;
	};
	this.getSystemActors = function(){
		var system = $(this.options.privs, function(e){
			return ( $.trim( e.getActorGroupName() ).toLowerCase()  === "system" );
		});
		return (system.length>0)?system[0]:null;
	};
	this.isPrivate = function(){
		return this.options.isPrivate;
	};
	this.hasPrivacy = function(){
		return this.options.hasPrivacy;
	};
	this.getEntityData = function(){
		var res = this.options.entityData.application || this.options.entityData || {};
		return res;
	};
	this.renderLoading = function(enabled, text, classes ){
		enabled = (typeof enabled === "boolean" )?enabled:false;
		classes = $.trim(classes);
		text = $.trim(text) || "Disconnecting Account";
		text = "..." + text;
		$(this.dom).children(".actionloader").remove();
		if( enabled ){
			$(this.dom).append("<div class='actionloader "+classes+"'><div class='shader'/><div class='message icontext'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>"+text+"</span></div></div>");
		}
	};
	this.isOwner = function(id){
		if( !this.options.owner || !this.options.owner.id ) return false;
		if( $.trim(this.options.owner.id) === $.trim(id) ) return true;
		return false;
	};
	this.getSystemPermissions = function(actors){
		actors = actors || [];
		var res = [];
		$.each(actors, (function(self){
			return function(i,e){
				if( !e.action ) return;
				e.action = $.isArray(e.action)?e.action:[e.action];
				var pg = new appdb.utils.PermissionGroups(e.action, self.options.entityType);
				if( (pg.isSystemGroup() === true || pg.isPartialGroup() === true) && pg.isExplicitGroup() === false && pg.getRules(["-8","-4"]).length > 0 ){
					res.push(e.id);
				}
			};
		})(this));
		return res;
	};
	this.getExplicitPermissions = function(actors,exclude){
		exclude = exclude || [];
		exclude = $.isArray(exclude)?exclude:[exclude];
		actors = actors || [];
		var res = [];
		$.each(actors, (function(self){
			return function(i, e){
				if( !e.action ) return;
				e.action = $.isArray(e.action)?e.action:[e.action];
				var pg = new appdb.utils.PermissionGroups(e.action, self.options.entityType);
				var toBeExcluded = ($.grep(exclude, function(ex){
					return $.trim(ex) === $.trim(e.id);
				}).length > 0)?true:false;
				if( pg.isExplicitGroup() === true && toBeExcluded === false){
					res.push(e.id);
				}
			};
		})(this));
		return res;
	};
	this.getPrivilegeGroup = function(name){
		var res = [];
		$.each(this.options.privs, function(i, e){
			if( $.trim(e.getActorGroupName()).toLowerCase() === $.trim(name).toLowerCase() ){
				res.push(e);
			}
		});
		return res;
	};
	this.getPrivilegesFromGroup = function(name, filter){
		filter = (typeof filter === "function")?filter:function(v){return true;};
		var res = [];
		var grp = this.getPrivilegeGroup(name);
		if( grp.length === 0 ) return res;
		var uniq = {};
		$.each(grp, function(i, e){
			$.each(e.getAllPrivileges(), function(ii, ee){
				if(!uniq[ee.id] && filter(ee) ){
					uniq[ee.id] = ee;
				}
			});
		});
		for(var i in uniq){
			if( uniq.hasOwnProperty(i) === false ) continue;
			res.push(uniq[i]);
		}
		return res;
	};
	this.createNewActorEntry = function(actor){
		var res =  {
			id: actor.id,
			suid: actor.guid,
			type: "ppl",
			firstname: actor.firstname,
			lastname: actor.lastname,
			name: actor.firstname + " " + actor.lastname,
			cname: actor.cname,
			isNewUser: true,
			action: appdb.utils.PermissionActions.getNewActionGroup()
		};
		return res;
	};
	this.setupPrivs = function(data){
		data = data || {};
		data.actor = data.actor || [];
		data.actor = $.isArray(data.actor)?data.actor:[data.actor];
		$.each(data.actor, function(i, e){
			e.action = e.action || [];
			e.action = $.isArray(e.action)?e.action:[e.action];
		});
		this.options.privs = null;
		var actorgroups = {};
		if( $.inArray(this.options.entityType, ["software", "vappliance","swappliance"]) > -1 ){
			actorgroups = appdb.pages.application.getActorGroups();
			actorgroups.system = this.getSystemPermissions(data.actor);
			actorgroups.explicit = this.getExplicitPermissions(data.actor, actorgroups.contacts);
			actorgroups.explicit.displayName = "Explicit Users";
		}
		var allprivs = new appdb.utils.EntityPrivileges({actor: data.actor, entityType: this.options.entityType});
		var res = [];
		for(var i in actorgroups){
			if( actorgroups.hasOwnProperty(i) === false ) continue;
			res.push(allprivs.createActorGroupPrivileges(i,actorgroups[i],[],actorgroups[i].displayName));
		}
		res.push(allprivs.getActorGroup("rest"));
		this.options.privs = res;
		return res;
	};
	this.onPrivacyChange = function(v){
		$(this.dom).find(".permissionscontainer").removeClass("disabled");
		var haschanges = v.hasChanges();
		var views = [];
		for(var i in this.views){
			if( this.views.hasOwnProperty(i) === false ) continue;
			views.push(this.views[i]);
		}
		$.each(views, function(i, e){
			if( haschanges ){
				e.disable(true);
				$(e.dom).closest(".permissionscontainer").addClass("disabled");
			}else{
				e.disable(false);
				$(e.dom).closest(".permissionscontainer").removeClass("disabled");
			}
		});
	};
	this.onChange = function(v){
		$(this.dom).find(".permissionscontainer").removeClass("changed").removeClass("disabled").removeClass("hasnewuser");
		var haschanges = v.hasChanges();
		var hasnewusers = ( v.getNewUsers().length > 0 );
		var views = [];
		for(var i in this.views){
			if( this.views.hasOwnProperty(i) === false ) continue;
			views.push(this.views[i]);
		}
		$.each(views, function(i, e){
			if( e === v ){
				e.disable(false);
				if( haschanges ){
					$(e.dom).closest(".permissionscontainer").addClass("changed").removeClass("disabled");
				}
				if( hasnewusers ){
					$(e.dom).closest(".permissionscontainer").addClass("hasnewuser");
				}
			}else{
				if( haschanges ){
					e.disable(true);
					$(e.dom).closest(".permissionscontainer").addClass("disabled");
				}else{
					e.disable(false);
					$(e.dom).closest(".permissionscontainer").removeClass("disabled");
				}
				
			}
		});
		
		if( this.options.hasPrivacy === true && this.options.privacy){
			if(  haschanges === true ){
				this.options.privacy.disable(true);
				$(this.options.privacy.dom).addClass("disabled");
			}else{
				this.options.privacy.disable(false);
				$(this.options.privacy.dom).removeClass("disabled");
			}
		}
		
	};
	this.onNewUser = function(v){
		
	};
	this.save = function(source){
		var changes = this.collectDataChanges(source);
		var js = JSON.stringify(changes);
		this._model.update({query: {}, data: {data: encodeURIComponent(js)}});
	};
	this.getImplicitUsers = function(){
		var uniq = {};
		var perms = [];
		$.each(this.getPrivilegesFromGroup("contacts"), function(i, e){
			if( !uniq[e.id] ) {
				e.permissionGroup = "contacts";
				e.fullname = e.name;
				uniq[e.id] = e;
			}
		});
		$.each(this.getPrivilegesFromGroup("explicit"), function(i, e){
			if( !uniq[e.id] ) {
				e.permissionGroup = "explicit";
				e.fullname = e.name;
				uniq[e.id] = e;
			}
		});
		$.each(this.getPrivilegesFromGroup("system", function(v){
				return v.permissionGroups.canFullControl();
			}), function(i, e){
					if( !uniq[e.id] ) {
						e.permissionGroup = "system";
						e.fullname = e.name;
						uniq[e.id] = e;
					}
		});
		for(var i in uniq){
			if( uniq.hasOwnProperty(i) === false ) continue;
			perms.push(uniq[i]);
		}
		
		return perms;
	};
	this.loadImplicitUsers = function(callback){
		callback = (typeof callback === "function")?callback:function(){};
		var users = this.getImplicitUsers();
		var fillUserPerms = function(persons){
			persons = persons || [];
			persons = $.isArray(persons)?persons:[persons];
			
			$.each(persons, function(ii, p){
				var id = $.trim(p.id);
				$.each(users, function(i, e){
					if( $.trim(e.id) === id ){
						p.associationType = e.permissionGroup;
						switch(e.permissionGroup){
							case "system":
								p.associationDescription = "System user with full control";
								p.associationOrder = 0;
								break;
							case "contacts":
								p.associationDescription = "Already associated as a contact";
								p.associationOrder = 1;
								break;
							case "explicit":
								p.associationDescription = "Already added explicitly";
								p.associationOrder = 2;
								break;
						}
					}
				});
			});
			persons.sort(function(a,b){
				var aa = a.associationOrder;
				var bb = b.associationOrder;
				return bb-aa;
			});
 		};
		var flts = [];
		$.each(users, function(i,e){
			flts.push("=person.id:" + e.id);
		});
		
		var _ppl = new appdb.model.People( { flt: flts.join(" ") } );
		_ppl.subscribe({event: "beforeselect", callback: function(){
				this.renderLoading(true, "Fetching people");
		}, caller: this});
		_ppl.subscribe({event: "select", callback: function(v){
				this.renderLoading(false);
				var ulist = v.person || [];
				ulist = $.isArray(ulist)?ulist:[ulist];
				fillUserPerms(ulist);
				callback(ulist);
		}, caller: this});
		_ppl.get();
		return _ppl;
	};
	this.addExplicitUser = function(){
		this.loadImplicitUsers((function(self){
			return function(users){
				var relcon = new appdb.components.RelatedContacts({excluded:users, onExclude: function(li){
					var d = li._itemData;
					var assocDesc = "Already in list";
					if( d.associationDescription ){
						assocDesc = d.associationDescription;
					}else{
						var u = $.grep(users, function(e){
							return ( $.trim(e.id) === $.trim(d.id) && $.trim(e.associationDescription) !== "" );
						});
						if( u.length > 0 ){
							assocDesc = $.trim(u[0].associationDescription);
						}
					}
					$(li.dom).find(".item a").unbind("click").css({"cursor":"default"}).attr("title",assocDesc);
					$(li.dom).css({"cursor":"default"}).find(".item:last").append("<div class='info'>" + assocDesc + "</div>");
				},ext:{mainTitle: "Include users in the explicit permissions list"}});
				relcon.subscribe({event:"close", callback : function(v){
						var items = v.views.peopleList.selectedDataItems.get();
						this.setNewExplicitUsers(items);
				},caller:self});
				relcon.load({query:{flt:"",pagelength:12,userid:userID},ext:{}});
			};
		})(this));
	};
	this.getUserFromGroup = function(groupname){
		
	};
	this.setNewExplicitUsers = function(users){
		users = users || [];
		users = $.isArray(users)?users:[users];
		var uniq = {};
		var expgroup = this.getPrivilegeGroup("explicit");
		if( expgroup === null || expgroup.length === 0) return;
		var ex = expgroup[0];
		var sysgroup = this.views.systemgroups.getItemByName("system");
		if( sysgroup !== null ){
			var sysprivs = sysgroup.options.group.getAllPrivileges();
			
			//First find users also in system list without full control
			var existingusers = $.grep(sysprivs, function(e){
				var res = $.grep(users, function(ee){
					if( ee.id !== e.id && !uniq[ee.id] && ee.isNewUser !== true){
						uniq[ee.id] = ee;
					}
					return ($.trim(ee.id) === $.trim(e.id));
				});
				return res.length > 0;
			});
		}
		var newusers = [];
		for(var i in uniq){
			if( uniq.hasOwnProperty(i) === false) continue;
			var u = uniq[i];
			if(typeof u.permissionGroups === "undefined" ){
				newusers.push(this.createNewActorEntry(u));
			}else{
				newusers.push(u);
			}
		}
		if( newusers.length > 0 ){
			$.each(newusers, function(i, e){
				ex.addActor(e);
			});
		}
		setTimeout( (function(self){
			return function(){
				self.views.explicitgroups.render(self.getPrivilegeGroup("explicit"));
				self.onChange(self.views.explicitgroups);
			};
		})(this),5);		
	};
	this.removeNewExplicitUsers = function(){
		var expgroup = this.getPrivilegeGroup("explicit");
		if( expgroup === null || expgroup.length === 0) return;
		var ex = expgroup[0];
		var newsuids = [];
		ex._privs = ex._privs || [];
		ex._privs = $.isArray(ex._privs)?ex._privs:[ex._privs];
		
		$.each(ex._privs, function(i, e){
			if( e.isNewUser === true ){
				newsuids.push(e.suid);
			}
		});
		
		$.each(newsuids, function(i, e){
			ex.removeActor(e);
		});
	};
	this.onAction = function(v){
		var action = v.action;
		var source = v.source;
		if( action === "cancel" ){
			if( source.renderWarning ){
				source.renderWarning(false);
			}
			source.revertChanges();
			source.removeNewUsers();
			this.removeNewExplicitUsers();
			source.onChange();
		}else if( action === "save" ){
			this.save(source);
		}else if( action === "addnew" ){
			this.addExplicitUser();
		}else if( action === "removenewusers" ){
			source.removeNewUsers();
			this.removeNewExplicitUsers();
		}
	};
	this.savePrivacy = function(source){
		var changes = source.getChanges();
		if( changes && typeof changes.isprivate === "boolean" ){
			var js = JSON.stringify( { "state": ( (changes.isprivate)?"private":"public" ) } );
			var _privacyModel = new appdb.model.VAppliancePrivacyState();
			_privacyModel.subscribe({event: "beforeupdate", callback: function(v){
					this.renderLoading(true, "...Updating privacy");
			}, caller: this});
			_privacyModel.subscribe({event: "update", callback: function(v){
					this.renderLoading(false);
					if( v.error || v.errormessage){
							this.renderError({
								title: "Could not update privacy",
								message: "Could not update privacy due to the following error:",
								error: v.errormessage
						});
					}else{
						this.options.hasPrivacy = true;
						this.options.privacy.options.hasPrivacy = true;
						this.options.isPrivate = ($.trim(v.state).toLowerCase()==="private");
						this.options.privacy.options.isPrivate = ($.trim(v.state).toLowerCase()==="private");
						setTimeout(function(){
							appdb.views.Main.refresh();
						},1);
					}
			}, caller: this});
			_privacyModel.update({query: {entityid: this.options.id}, data: {data: encodeURIComponent(js)}});
		}else{
			this.render();
		}
	};
	this.onPrivacyAction = function(v){
		var action = v.action;
		var source = v.source;
		if( action === "cancel" ){
			source.revertChanges();
			this.onPrivacyChange(source);
		}else if( action === "save" ){
			this.savePrivacy(source);
		}
	};
	this.getUserActionsBySuid = function(suid){
		suid = $.trim(suid);
		var res = [];
		if (suid === "" ) return [];
		$.each(this.options.privs, function(i, e){
			var us = e.getBySuid(suid);
			var u = (us.length > 0)?us[0]:null;
			if( u === null ) return;
			u.action = u.action || [];
			u.action = $.isArray(u.action)?u.action:u.action;
			res = u.action;
		});
		return $.unique(res).sort();
	};
	this.removeGlobalPrivileges = function(suid, actions){
		var res = [];
		actions = actions || [];
		actions = $.isArray(actions)?actions:[actions];
		var ua = this.getUserActionsBySuid(suid);
		if( ua.length === 0 ) return actions; //could not find user. Must be newly added(explicit), so no global actions to remove.
		var acts = $.grep(ua, function(e){
			return !(e.global && $.trim(e.global) === "true");
		});
		var mactions = [];
		$.each(actions, function(i,e){
			mactions.push(parseInt(e));
		});
		var res = [];
		$.each(acts, function(i, e){
			if( $.inArray(parseInt(e.id), mactions) > -1 ){
				res.push(e.id);
			}
		});
		return res;
	};
	this.collectDataChanges = function(source) {
		var pobj = new appdb.utils.PermissionGroups();
		var res = { targetid: this.options.id, privs: [] };
		var changes = source.getChanges();
		var suid = {};
		$.each(changes, function(i, e){
			$.each(e.changes, function(ii, ee){
				if( !suid[ee.suid] ) suid[ee.suid] = { suid: ee.suid, grant: [], revoke: [] };
				suid[ee.suid].grant = $.unique( suid[ee.suid].grant.concat(ee.grant) );
				suid[ee.suid].revoke = $.unique( suid[ee.suid].revoke.concat(ee.revoke) );
			});
		});
		for(var s in suid ){
			if( suid.hasOwnProperty(s) === false ) continue;
			var privs = { grant: [], revoke: [] };
			var cEntityType = this.options.entityType;
			$.each(suid[s].grant, function(i, e){
				var aids = [];
				//in case of fullcontrol check entity type to not 
				//grant deticated permissions for other entities
				if( $.trim(e).toLowerCase() === "fullcontrol" ){
					var excluded = [];
						switch(cEntityType){
							case "software":
								excluded = ["vaversions","accessvaversions"];
								break;
							case "vappliance":
								excluded = ["releases"];
								break;
							default:
								break;
						}
						aids = pobj["can" + e].getActionIds(false, excluded);
					}else{
						aids = pobj["can" + e].getActionIds(false); 
					}
				privs.grant = privs.grant.concat( aids );
			});
			$.each(suid[s].revoke, (function(self){ 
				return function(i, e){
					var aids = [];
					if( $.trim(e).toLowerCase() === "fullcontrol" ){
						aids = pobj["can" + e].getActionIds(true);
					}else{
						aids = pobj["can" + e].getActionIds(false); 
					}
					privs.revoke = privs.revoke.concat( self.removeGlobalPrivileges(s, aids) );
				};
			})(this));
			suid[s].grant = $.unique( privs.grant ).sort();
			suid[s].revoke = $.unique( privs.revoke ).sort();
			res.privs.push(suid[s]);
		}
		
		return res;
	};
	this.canEditPrivileges = function(){
		var perms = appdb.pages.application.currentPermissions();
		if( !perms ) return false;
		return perms.canGrantPrivilege();
	};
	this.renderPrivacy = function(){
		this.options.privacy.render();
	};
	this.render = function(d){
		if( this.canEditPrivileges() ){
			$(this.dom).addClass("caneditprivs");
		}else{
			$(this.dom).removeClass("caneditprivs");
		}
		this.views.contactgroups.unsubscribeAll();
		this.views.systemgroups.unsubscribeAll();
		this.views.contactgroups.render(this.getPrivilegeGroup("contacts"));
		this.views.contactgroups.onChange();
		this.views.explicitgroups.render(this.getPrivilegeGroup("explicit"));
		this.views.systemgroups.render(this.getPrivilegeGroup("system"));
		
		if( this.options.hasPrivacy === true ){
			this.renderPrivacy();
		}
	};
	this.renderError = function(v){
		var err = v.error || "Unknown error occured";
		var title = v.title ||	"Could not proceed";
		var message = v.message || "Could not proceed with action due to the following error:";
		message = message + "<br/>" + err;
		appdb.utils.ShowNotificationWarning({title: title, message: message});
	};
	this.load = function(o){
		this._model.destroy();
		this._model = new this.options.modelType(o);
		this._model.subscribe({event: "beforeselect", callback: function(v){
			if( this.options.permsStateCounter <= 3 ){
				this.renderLoading(true, "Loading privileges");
			}else{
				this.renderLoading(true, "Loading privileges<div class='smalldescription'>this might take a while</div>","delayed");
			}
		}, caller: this}).subscribe({event: "beforeupdate", callback: function(v){
			this.renderLoading(true, "Updating privileges");
		}, caller: this}).subscribe({event: "select", callback: function(v){
			this.options.permsStateCounter += 1;
			if( v.error ){
				this.renderLoading(false);
				this.renderError({
					title: "Could not retrieve privileges",
					message: "Could not retrieve privilege information due to the following error:",
					error: v.error
				});
			}else if( $.inArray($.trim(v.permsState), ["","0"]) === -1){
				console.log("Trying " + this.options.permsStateCounter);
				setTimeout((function(self){
					return function(){
						if( self._model ){
							self._model.get();
						}
					};
				})(this),300);
				return;
			}else{
				this.renderLoading(false);
				this.setupPrivs(v);
			}
			this.options.permsStateCounter = 0;
			this.render(this.options.privs);
			appdb.pages.application.onPermissionsLoaded();
		}, caller: this}).subscribe({event: "update", callback: function(v){
			this.options.permsStateCounter = 0;
			this.renderLoading(false);
			if( v.error || v.errormessage){
				this.renderError({
					title: "Could not update privileges",
					message: "Could not update privileges due to the following error:",
					error: v.errormessage
				});
			}else{
				this._model.get();
			}
		}, caller: this});
		this._model.get();
	};
	this._initContainer = function(){
		
	};
	this._init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._model = new this.options.modelType({id: this.options.id});
		this.views.systemgroups = new appdb.views.PermissionGroupList({
			container: $(this.dom).find(".systemcontainer"),
			parent: this,
			entityType: this.options.entityType
		});
		this.views.contactgroups = new appdb.views.PermissionGroupList({
			container: $(this.dom).find(".contactcontainer"),
			parent: this,
			entityType: this.options.entityType
		});
		this.views.contactgroups.subscribe({event: "changed", callback: this.onChange, caller: this});
		this.views.contactgroups.subscribe({event: "action", callback: this.onAction, caller: this});
		this.views.explicitgroups = new appdb.views.PermissionGroupList({
			container: $(this.dom).find(".explicitcontainer"),
			parent: this,
			entityType: this.options.entityType,
			emptyGroupWarning: "Users with no granted permissions will be removed from this list upon save."
		});
		this.views.explicitgroups.subscribe({event: "changed", callback: this.onChange, caller: this});
		this.views.explicitgroups.subscribe({event: "action", callback: this.onAction, caller: this});
		if( this.options.hasPrivacy === true ){
			$(this.dom).find(".section.privacy").removeClass("hidden");
			this.options.privacy = new appdb.views.EntityPrivacy({
				container: $(this.dom).find(".section.privacy > .permissionscontainer"),
				parent: this,
				isPrivate: this.options.isPrivate,
				editable: this.options.canEditPrivacy
			});
			this.options.privacy.subscribe({event: "action", callback: this.onPrivacyAction, caller: this});
			this.options.privacy.subscribe({event: "changed", callback: this.onPrivacyChange, caller: this});
		}else{
			$(this.dom).find(".section.privacy").addClass("hidden");
		}
		if( this.isPrivate() ){
			$(this.dom).addClass("isprivate");
		}else{
			$(this.dom).removeClass("isprivate");
		}
	};
});
appdb.components.SoftwarePrivileges = appdb.ExtendClass(appdb.components.EntityPrivileges, "appdb.components.SoftwarePrivileges", function(o){
	this.options = $.extend(this.options, {modelType: appdb.model.SoftwarePrivileges}, true);
	this._init();
});

appdb.components.VAppliancePrivileges = appdb.ExtendClass(appdb.components.EntityPrivileges, "appdb.components.VAppliancePrivileges", function(o){
	this.options = $.extend(this.options, {modelType: appdb.model.VAppliancePrivileges}, true);
	this._init();
});
appdb.components.SWAppliancePrivileges = appdb.ExtendClass(appdb.components.EntityPrivileges, "appdb.components.SWAppliancePrivileges", function(o){
	this.options = $.extend(this.options, {modelType: appdb.model.VAppliancePrivileges}, true);
	this._init();
});
appdb.components.AccessGroupEditor = appdb.ExtendClass(appdb.Component, "appdb.components.AccessGroupEditor", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		profileId: o.profileId || null,
		profileAccessGroups: o.profileAccessGroups || [],
		groupPermissions: o.groupPermissions || [],
		changes: {},
		dom: {
			list: $("<div class='currentgroups'></div>")
			
		}
	};
	this.profileInAccessGroup = function(groupid){
		groupid = $.trim(groupid);
		if( groupid === "") return false;
		var res = $.grep(this.options.profileAccessGroups, function(e){
			return ( $.trim(e.id) === groupid );
		});
		return ( res.length > 0 );
	};
	
	this.clearDialog = function(){
		if( appdb.components.AccessGroupEditor.dialog !== null ){
			appdb.components.AccessGroupEditor.dialog.hide();
			appdb.components.AccessGroupEditor.dialog.destroyRecursive(false);
			appdb.components.AccessGroupEditor.dialog = null;
		}
	};
	this.show = function(){
		this.clearDialog();
		appdb.components.AccessGroupEditor.dialog = new dijit.Dialog({
			title: appdb.components.AccessGroupEditor.title,
			content: $(this.dom)[0],
			onCancel : (function(_this){
				return function(){
					_this.cancel();
				};
			})(this)
		});
		$(appdb.components.AccessGroupEditor.dialog.domNode).addClass("accessgroupcontainer");
		appdb.components.AccessGroupEditor.dialog.show(); 
	};
	this.revertChanges = function(){
		this.options.changes = {};
		$(this.dom).find("button.toggled").removeClass("toggled");
		$(this.dom).removeClass("changed");
		this.warnUser(false);
	};
	this.close = function(){
		this.clearDialog();
	};
	this.cancel = function(){
		this.revertChanges();
		this.close();
	};
	this.renderError = function(enabled, text, classes){
		enabled = (typeof enabled === "boolean")?enabled:false;
		text = $.trim(text) || "Unkown error occured";
		classes = $.trim(classes);
		$(this.dom).children(".actionloader").remove();
		if( enabled ){
			$(this.dom).append("<div class='actionloader "+classes+"' ><div class='shader'/><div class='message icontext' style='padding-bottom:30px;'><img src='/images/vappliance/redwarning.png' alt=''/><span>"+text+"</span><button class='btn btn-compact btn-primary'>close</button></div></div>");
			$(this.dom).children(".actionloader").find("button").css({"display": "block","position": "absolute","right": "5px","padding": "0px 2px","margin": "0px"}).unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.renderError(false);
					return false;
				};
			})(this));
		}
	};
	this.submit = function(){
		var d = {data: this.collectData() };
		this.renderLoading(true,"saving changes");
		$.ajax({
			url: appdb.config.endpoint.base + "people/accessgroups",
			type: "POST",
			data: d,
			success: (function(self){
				return function(d, textStatus, jqXHR){
					var data = JSON.parse(d);
					if( $.trim(data)==="" || data.error ){
						self.renderError(true, (data.errormessage || "Unknown error occured") );
					}else{
						$.get(appdb.config.endpoint.base + "people/accessgroups?id="+self.options.profileId, function(d){
							var data = JSON.parse(d);
							if( data.grouppermissions ){
								appdb.pages.Person.currentGroupPermissions(data.grouppermissions);
							}
							if( data.accessgroups ){
								appdb.pages.Person.currentAccessGroups(data.accessgroups);
							}
							self.renderLoading(false);
							self.close();
							appdb.pages.Person.updateAccessGroups();
						});
					}
				};
			})(this),
			error: (function(self){ 
				return function(jqXHR, textStatus, errorThrown){
					self.renderLoading(false);
					alert("Error:" + textStatus);
				};
			})(this)
		});
	};
	
	this.makeSubmit = function(){
		this.submit();
	};
	this.collectData = function(){
		//create collection of {"id":<userid>,actions:{ "<action>": [<id>, <id>, ...], "<action>": [<id>, <id>, ...], ... } }
		var d = { "id": this.options.profileId, "actions": {} };
		for( var i in this.options.changes ){
			if( this.options.changes.hasOwnProperty(i) === false )continue;
			var id = $.trim(i);
			var actname = this.options.changes[id];
			if( typeof d.actions[actname] === "undefined" ){
				d.actions[actname] = [];
			}
			d.actions[actname].push( id );
		}
		return d;
	};
	this.hasChanges = function(){
		var count = 0;
		for(var i in this.options.changes){
			if( this.options.changes.hasOwnProperty(i) === false ) continue;
			count+=1;
		}
		return (count > 0 );
	};
	this.renderLoading = function(enabled,text, classes){
		enabled = (typeof enabled === "boolean")?enabled:false;
		text = $.trim(text) || "Loading";
		text = "..." + text;
		classes = $.trim(classes);
		$(this.dom).children(".actionloader").remove();
		if( enabled ){
			$(this.dom).append("<div class='actionloader "+classes+"'><div class='shader'/><div class='message icontext'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>"+text+"</span></div></div>");
		}
	};
	this.onChange = function(item){
		if( this.hasChanges() === true){
			$(this.dom).addClass("changed");
		}else{
			$(this.dom).removeClass("changed");
		}
	};
	this.getPendingRequests = function(){
		var data = this.options.groupPermissions || [];
		var res = [];
		$.each(data, function(i, e){
			if( $.trim(e.canAcceptReject) === "true" && $.trim(e.hasRequest) !== "false" ){
				res.push(e.name);
			}
		});
		return res;
	};
	this.addAction = function(item){
		var sameprofile = ( $.trim(this.options.profileId) === $.trim(userID) );
		var td = $("<td class='action'></td>");
		var action = $("<div class='action'></div>");
		var ingroup = this.profileInAccessGroup(item.id);
		var button = $("<button class='btn btn-compact"+((ingroup)?" ingroup":"")+((item.canRemove)?" canremove":"")+((item.canAdd)?" canadd":"")+ ((item.canRequest)?" canrequest":"") +"'></button>");
		var button2 = null;
		$(button).data("ingroup",ingroup);
		if( ingroup && item.canRemove ){
			$(button).data("action","exclude").addClass("toremove").append(sameprofile ? "<span>leave this group</span>" : "<span>remove from group</span>");
		}else if( !ingroup ){
			if( item.hasRequest !== false && item.canAcceptReject ){
				$(button).data("action","accept").addClass("toadd").append("<span>accept</span>");
				button2 = $("<button class='btn btn-compact"+((ingroup)?" ingroup":"")+((item.canRemove)?" canremove":"")+((item.canAdd)?" canadd":"")+ ((item.canRequest)?" canrequest":"") +"'></button>");
				$(button2).data("action","reject").addClass("toremove").append("<span>reject</span>");
			}else if( item.canAdd ){
				$(button).data("action","include").addClass("toadd").append(sameprofile ? "<span>join this group</span>" : "<span>add to group</span>");
			}
		}
		
		$(button).unbind("click").bind("click", (function(self,data, toggle){
			return function(ev){
				ev.preventDefault();
				delete self.options.changes[item.id];
				if( $(this).hasClass("toggled") ){
					$(this).removeClass("toggled");
				}else{
					$(this).addClass("toggled");
					if( toggle !== null ){
						$(toggle).removeClass("toggled");
					}
					self.options.changes[item.id] = $(this).data("action");
				}
				
				self.onChange(data);
				return false;
			};
		})(this, item, button2));
		if( button2 !== null ){
			$(button2).unbind("click").bind("click", (function(self,data, toggle){
				return function(ev){
					ev.preventDefault();
					if( $(this).hasClass("toggled") ){
						$(this).removeClass("toggled");
						delete self.options.changes[item.id];
					}else{
						$(this).addClass("toggled");
						if( toggle !== null ){
							$(toggle).removeClass("toggled");
						}
						self.options.changes[item.id] = $(this).data("action");
					}
					self.onChange(data);
					return false;
				};
			})(this, item, button));
		}
		$(action).append(button);
		if( button2 !== null ){
			$(action).append(button2);
			$(action).addClass("pendingrequest")
			$(action).prepend("<div class='message'>pending user request...</div>");
		}
		$(td).append(action);
		return td;
	};
	this.warnUser = function(enabled, text, classes){
		enabled = (typeof enabled === "boolean")?enabled:false;
		text = $.trim(text) || "Unkown error occured";
		classes = $.trim(classes);
		$(this.dom).find(".footer > .warning").remove();
		if( enabled ){
			$(this.dom).find(".footer").append("<div class='warning "+classes+"' ><div class='message icontext'><img src='/images/vappliance/redwarning.png' alt=''/><span>"+text+"</span></div></div>");
		}
	};
	this.addActionRequest = function(item){
		var td = $("<td class='action'></td>");
		var action = $("<div class='action'></div>");
		var ingroup = this.profileInAccessGroup(item.id);
		var sameprofile = ( $.trim(this.options.profileId) === $.trim(userID) );
		var button = $("<button class='btn btn-compact"+((ingroup)?" ingroup":"")+((item.canRemove)?" canremove":"")+((item.canAdd)?" canadd":"")+ ((item.canRequest)?" canrequest":"") +"'></button>");
		$(button).data("ingroup",ingroup);
		if( item.hasRequest !== false ){
			$(button).data("action","cancel").addClass("tocancel").append("<span>cancel request</span>");
			$(action).addClass("pendingrequest").append("<div class='message'>you have a pending request...</div>");
		}else if( ingroup && item.canRemove ){
			$(button).data("action","exclude").addClass("toremove").append("<span>" + ((sameprofile)?"leave this group":"remove from group") + "</span>");
		} else if( !ingroup && item.canAdd ){
			$(button).data("action","include").addClass("toadd").append(sameprofile ? "<span>join this group</span>" : "<span>add to group</span>");
		} else {
			$(button).data("action","request").addClass("torequest").append("<span>join this group</span>");
		}
		
		$(button).unbind("click").bind("click", (function(self,data){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("toggled") ){
					$(this).removeClass("toggled");
					delete self.options.changes[item.id];
				}else{
					$(this).addClass("toggled");
					self.options.changes[item.id] = $(this).data("action");
				}
				self.onChange(data);
				if( $(this).data("action") === "exclude" && sameprofile ){
					if( $(this).hasClass("toggled") ){
						self.warnUser(true, "By leaving an access group, you loose all of the associated privileges of this group.");
					}else{
						self.warnUser(false);
					}
				}
				return false;
			};
		})(this, item));
		$(action).append(button);
		$(td).append(action);
		return td;
	};
	this.addGroup = function(item){
		if( !item.canRemove && !item.canAdd && !item.canRequest) return null;
		if( this.profileInAccessGroup(item.id) && !item.canRemove) return null;
		var tr = $("<tr ></tr>");
		var ingroup = this.profileInAccessGroup(item.id);
		var groupname = item.name;
		if(item.id === "-3" ){
			groupname += " of " + appdb.pages.Person.currentCountryName();
		}
		var groupname = $("<td class='accessgroup name'><span >" + groupname + "</span></td>");
		$(tr).append(groupname);
		if( item.canRequest === true || $.trim(userID) === $.trim(this.options.profileId) ){
			$(tr).append(this.addActionRequest(item));
		}else{
			$(tr).append(this.addAction(item));
		}
		if( ingroup ){
			$(tr).addClass("ingroup");
		}
		return tr;
	};
	this.setupPermissions = function(d){
		
	};
	this.render = function(){
		var data = this.options.groupPermissions || [];
		var tbl = $(this.dom).find(".currentgroups > table > tbody");
		$(tbl).empty();
		$.each(data, (function(self){
			return function(i, e){
				var tr = null;
				tr = self.addGroup(e);
				if( tr !== null ){
					$(tbl).append(tr);
				}
			};
		})(this));
		var pending = this.getPendingRequests();
		appdb.pages.Person.updatePendingAccessGroups(pending);
	};
	this.load = function(){
		this.render();
	};
	this._initActions = function(){
		$(this.dom).find(".action.save").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.makeSubmit();
				return false;
			};
		})(this));
		$(this.dom).find(".action.cancel").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.cancel();
				return false;
			};
		})(this));
		$(this.dom).find(".action.request").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.makeRequest();
				return false;
			};
		})(this));
	};
	this._initColumns = function(){
		var header = $(this.dom).find(".currentgroups > table > thead > tr");
		var colAccessGroup = $("<td class='column-accessgroup'><span class='accessgroup'>Acess group</span></td>");
		var colAction = $("<td class='column-action'><span class='action'>Actions</span></td>");
		$(header).append(colAccessGroup).append(colAction);
	};
	this._initContainer = function(){
		if( $(this.dom).find(".currentgroups").length === 0 ){
			$(this.dom).find(".main").append($(this.options.dom.list).clone());
		}
		$(this.dom).find(".currentgroups").append("<table><thead><tr></tr></thead><tbody></tbody></table>");
		this._initColumns();
	};
	this._init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._initContainer();
		this._initActions();
	};
	this._init();
},{
	dialog: null,
	title: "Access group editor"
});

appdb.components.UserMessages = appdb.ExtendClass(appdb.Component, "appdb.components.UserMessages", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		folders: {
			"inbox": o.inbox || false,
			"outbox": o.outbox || false
		},
		dom: {
			folders: $(o.container).find(".folders").clone(),
			lists: [],
			content: $(o.container).find(".content").clone()
		}
	};
	this.render = function(){
		
	};
	this.load = function(){
		
	};
	this.initContainer = function(){
		
		if( $(this.options.dom.folders).length === 0 ){
			this.options.dom.folders = $("<div class='folders'></div>");
			$(this.dom).append(this.options.dom.folders);
			
		}
		if( $(this.options.dom.inboxlist).length === 0 ){
			this.options.dom.inboxlist = $("<div class='inboxlist'></div>");
			$(this.dom).append(this.options.dom.content);
		}
		if( $(this.options.dom.outboxlist).length === 0 ){
			this.options.dom.outboxlist = $("<div class='outboxlist'></div>");
			$(this.dom).append(this.options.dom.outboxlist);
		}
		if( $(this.options.dom.content).length === 0 ){
			this.options.dom.content = $("<div class='content'></div>");
			$(this.dom).append(this.options.dom.content);
		}
	
	};
	this._init = function(){
		
	};
	this._init();
	
});

appdb.components.VoImageListManager = appdb.ExtendClass(appdb.Component, "appdb.components.VoImageListManager", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		broker: null,
		vodata: null,
		vapps: [],
		id: $.trim(o.id) || -1
	};
	this.render = function(d){
		var vapps = this.getAvailableVAsWithSWapps();
		var vodata = this.getVoData();
		var deletedvas = this.getDeletedPublishedVas();
		var swapps = this.getSwAppliances();
		this.views.imagelist.render(vapps, vodata, deletedvas, swapps);
	};
	this.getVoData = function(){
		return this.options.vodata || null;
	};
	this.getSwAppliances = function(){
		return this.options.swapps || null;
	};
	this.findSwappliances = function(vappid){
		vappid = $.trim(vappid);
		var u = {};
		$.each(appdb.model.StaticList.SwapplianceReport, function(i,e){
			if( $.trim(e.id) === vappid ){
				e.application = e.application || [];
				e.application = $.isArray(e.application)?e.application:[e.application];
				$.each(e.application, function(ii,ee){
					if( !u[ee.id] ) {
						u[ee.id] = ee;
						u[ee.id].versionid = e.versionid;
					} 
				});
			}
		});
		var res = [];
		for(var i in u){
			if( u.hasOwnProperty(i) === false) continue;
			res.push(u[i]);
		}
		return res;
	};
	this.getAvailableVAsWithSWapps = function(){
		var vapps = this.getAvailableVAs();
		$.each(vapps, (function(self){ 
			return function(i,e){
				e.swappliance = self.findSwappliances(e.id);
			};
		})(this));
		return vapps;
	};
	this.getAvailableVAs = function(){
		return this.options.vapps || [];
	};
	this.getAllAvailableSWAppliances = function(swapps){
		var u = {};
		var res = [];
		$.each(swapps, function(i,e){
			e.application = e.application ||[];
			e.application = $.isArray(e.application)?e.application:[e.application];
			$.each(e.application, function(ii,ee){
				if( !u[ee.id] ) u[ee.id] = ee;
			});
		});
		for(var i in u){
			if( u.hasOwnProperty(i) ){
				res.push(u[i]);
			}
		}
		return res;
	};
	this.getDeletedPublishedVas = function(){
		var pa = this.getImageList("published");
		if( !pa ){
			return [];
		}
		var dels = {};
		
		pa.image = pa.image || [];
		pa.image = $.isArray(pa.image)?pa.image:[pa.image];
		
		$.each(pa.image, function(i, img){
			if( $.trim(img.app_state).toLowerCase() === 'deleted' ){
				dels[img.appid] = dels[img.appid] || { appid: img.appid, name: img.name, cname: img.cname, va_version: img.va_version, va_versionid: img.va_versionid, instances:[]};
				dels[img.appid].instances.push(img.vmiinstanceid);
			}
		});
		var res = [];
		for(var i in dels){
			if( dels.hasOwnProperty(i) === false ) continue;
			res.push(dels[i]);
		}
		return res;
	};
	this.getImageList = function(state){
		state = $.trim(state).toLowerCase() || "published";
		var vodata = this.getVoData();
		var res = $.grep(vodata.imagelist, function(e){
			return ( $.trim(e.state).toLowerCase() === state );
		});
		return ( res.length > 0 )?res[0]:null;
	};
	this.getPublishedImageList = function(){
		return this.getImageList("published");
	};
	this.getDraftImageList = function(){
		return this.getImageList("draft");
	};
	//Extract information in a VAppliance level instead of image level that the vo data provides.
	//Update available vappliance entries with relations to the current vo image list(locally)
	this.extractVApps = function(){
		var lists = [
			this.getPublishedImageList(),
			this.getDraftImageList()
		];
		var res = [];
		$.each( lists, (function(self){
			return function(i, e){
				res.push( self.groupVApps(e) );
			};
		})(this));
		this.options.vodata.imagelist = res;
		this.mergeDraftToAvailableVApps();
	};
	//Update available vappliance data with related vo image list information
	this.mergeDraftToAvailableVApps = function(){
		var draft = this.getDraftImageList();
		var pub = this.getPublishedImageList();
		var vapps = this.getAvailableVAs();
		
		if( draft === null ) {
			draft = {imagelist:[], vapps:{}};
		}
		if( pub === null ){
			pub = {imagelist:[], vapps:{}};
		}
		draft.vapps = draft.vapps || {};
		pub.vapps = pub.vapps || {};
		
		var res = [];
		
		$.each(vapps, function(i, e){
			var curid = $.trim(e.id);
			var da = draft.vapps[curid];
			var pa = pub.vapps[curid];
			var curlist = (da)?da:pa;
			e.appliance = e.appliance || {};
			var appid = $.trim(e.id);
			var verid = $.trim(e.appliance.versionid) || "-1"; 
			//Init voimagelist object for vappliance entry
			e.voimagelist = {};
			e.voimagelist.images = [];
			e.voimagelist.isPublished = (pa)?true:false;
			e.voimagelist.isRemoved = (pa && !da)?true:false;
			e.voimagelist.isAdded = (!pa && da)?true:false;
			e.voimagelist.isOutdated = false; 
			//default image states for unassociated vappliance
			e.voimagelist.hasPublished = true; //always true
			e.voimagelist.hasDrafts = false;
			e.voimagelist.hasObsolete = false;
			e.voimagelist.hasDeleted = false;
			e.voimagelist.hasUnknown = false;
			e.voimagelist.isOutdated = false;
			e.voimagelist.wasOutdated = false;
			//Select existing images states from either draft or published image list, if any exist.
			var curlist = (da)?da:pa;
			if( curlist ){ 
				e.voimagelist.images = curlist.images;
				e.voimagelist.hasPublished = curlist.hasPublished;
				e.voimagelist.hasDrafts = curlist.hasDrafts;
				e.voimagelist.hasObsolete = curlist.hasObsolete;
				e.voimagelist.hasDeleted = curlist.hasDeleted;
				e.voimagelist.hasUnknown = curlist.hasUnknown;
			}
			
			//There is draft and published imagelist for this VO
			//Check if published images are identical to draft version 
			//to set if vappliance version as outdated
			if( da && pa && e.voimagelist.isRemoved === false ){
				
				//Collect not published images (deleted, obsolete etc) from current list
				var pimages = $.grep(curlist.images, function(img){
					return ( $.trim(img.state).toLowerCase() !== "up-to-date" );
				});
				//If at least one not published then set image list as outdated
				if( pimages.length > 0 ){
					e.voimagelist.isOutdated = true;
				}
				//Check if it used to be outdated
				if( pa ){
					var identical = true;
					if( pa.images.length !== da.images.length ){
						identical = false;
					}else if( pa.hasObsolete === true ){
						identical = false;
					}else{
						$.each(pa.images, function(ii,ee){
							if( identical !== true ) return;
							var found = $.grep(da.images, function(eee){
								return ( $.trim(eee.state) === $.trim(ee.state) );
							});
							if( found.length === 0) {
								identical = false;
							}else if( found.length !== da.images.length){
								identical = false;
							}
						});
					}
					e.voimagelist.wasOutdated = !identical;
				}
			}else if( pa && pa.hasObsolete === true){
				e.voimagelist.wasOutdated = true;
			}
			
			if( e.voimagelist.wasOutdated && pa){
				var previousversions = {};
				$.each(pa.images, function(iv, img){
					previousversions[$.trim(img.va_versionid)] = $.trim(img.va_version);
				});
				e.voimagelist.previousVersions = previousversions;
			}
			res.push(e);
		});
		this.options.vapps = res;
	};
	//Create vapp property with grouped images based on the vappliance container
	this.groupVApps = function(imagelist){
		imagelist = imagelist || "published";
		if( typeof imagelist === "string" ){
			imagelist = this.getImageList(imagelist);
		}
		imagelist = imagelist || {};
		imagelist.image = imagelist.image || [];
		imagelist.image = $.isArray(imagelist.image)?imagelist.image:[imagelist.image];
		
		var vapps = {};
		$.each( imagelist.image, function(i, e){
			if( typeof vapps[e.appid] === "undefined" ){
				vapps[e.appid] = {
					"appid": e.appid,
					"name": e.name,
					"cname": e.cname,
					"hasPublished": false,
					"hasDrafts": false,
					"hasObsolete": false,
					"hasDeleted": false,
					"hasUnknown": false
				};
			}
			switch($.trim(e.state).toLowerCase()){
				case "draft":
					vapps[e.appid].hasDrafts = true;
					break;
				case "up-to-date":
					vapps[e.appid].hasPublished = true;
					break;
				case "obsolete":
					vapps[e.appid].hasObsolete = true;
					break;
				case "deleted":
					vapps[e.appid].hasDeleted = true;
					break;
				case "unknown":
				default:
					vapps[e.appid].hasUnknown = true;
					break;
					
			}
			vapps[e.appid].images = vapps[e.appid].images || [];
			vapps[e.appid].images.push({
				"id": e.id,
				"state": e.state,
				"guid": e.guid,
				"va_versionid": e.va_versionid,
				"va_version": e.va_version,
				"vmiinstanceid": e.vmiinstanceid
			});
		});
		
		imagelist.vapps = vapps;
		return imagelist;
	};
	this._RequestAction = function(d, callback){
		callback = (typeof callback === "function" )?callback: function(){};
		d = d || {};
		d = $.extend( true, { "void": this.options.id }, d );
		_model = new appdb.model.VOWideImageList();
		_model.subscribe({event: "update", callback: function(v){
				this.renderLoading(false);
			callback(v);
		}, caller: this});
		_model.update({query: {}, data: d});
	};
	this.addVappliance = function(vappid, callback){
		this._RequestAction({ "action":"add", "vappid": vappid }, callback ); 
	};
	this.removeVappliance = function(vappid, callback){
		this._RequestAction({ "action":"remove", "vappid": vappid }, callback ); 
	};
	this.updateVappliance = function(vappid, callback){
		this._RequestAction({ "action":"update", "vappid": vappid }, callback ); 
	};
	this.publishImageList = function(callback){
		this.renderLoading(true, "Publishing changes");
		this._RequestAction({ "action":"publish" }, callback ); 
	};
	this.revertChanges = function(callback){
		this.renderLoading(true, "Reverting changes");
		this._RequestAction({ "action":"revertchanges" }, callback ); 
	};
	this.onAction = function(action, data, callback){
		action = $.trim(action).toLowerCase();
		switch(action){
			case "add":
				this.addVappliance(data.id, callback);
				break;
			case "remove":
				this.removeVappliance(data.id, callback);
				break;
			case "update":
				this.updateVappliance(data.id, callback);
				break;
			case "publish":
				this.publishImageList(callback);
				break;
			case "revertchanges":
				this.revertChanges(callback);
				break;
			default: 
				callback({error: "Invalid action specified"});
				break;
		}
	};
	this.renderLoading = function(enabled, text, classes){
		enabled = (typeof enabled === "boolean")?enabled:false;
		text = $.trim(text) || "Loading";
		text = "..." + text;
		classes = $.trim(classes);
		$(this.dom).children(".actionloader").remove();
		if( enabled ){
			$(this.dom).append("<div class='actionloader "+classes+"'><div class='shader'/><div class='message icontext'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>"+text+"</span></div></div>");
		}
	};
	this.onRowAdded = function(o){
		var data = o.data || {};
		data.data = data.data || {};
		var dom = $(o.dom);
		var imglst = data.data.voimagelist || {};
		if( imglst.isPublished ){
			$(dom).attr("data-published", true);
		}
		if( imglst.isRemoved ){
			$(dom).attr("data-removed", true);
		}
		if( imglst.isAdded ){
			$(dom).attr("data-added", true);
		}
		if( imglst.hasObsolete || imglst.hasDeleted ){
			$(dom).attr("data-hasobsolete", true);
		}
		if( imglst.isOutdated ){
			$(dom).attr("data-outdated", true);
		}
	};
	this.load = function(d){
		if( typeof d !== "undefined" ){
			d = d || [];
			d = $.isArray(d)?d:[d];
			this.options.data = d;
			this.render(d);
			return;
		}
		var vappsrequest = {
			"id": "vapps",
			"resource": "applications",
			"method": "GET",
			"param": [{"name":"flt", "val":"+*&application.metatype:1 | +=application.published:true"}, {"name":"pagelength","val":"10000"}, {"name": "pageOffset", "val": "0" }]
		};
		var swappsrequest = {
			"id": "swapps",
			"resource": "applications/swappliance/report",
			"method": "GET",
			"param": []
		};
		var vorequest = {
			"id": "vo",
			"resource": "vos/" + $.trim( this.options.id || -1) ,
			"method": "GET",
			"param": []
		};
		var brokerreqs = [vorequest, vappsrequest, swappsrequest];
		this.options.broker = new appdb.utils.broker(true);
		this.options.broker.request(brokerreqs);
		this.renderLoading(true, "Loading image list");
		this.options.broker.fetch(( function(self){
			return function(e){
				var i, len = e.reply.length;
				self.options.vapps = [];
				self.options.vodata = {};
				self.options.swapps = [];
				for (i=0; i<len; i+=1) {
					var id = $.trim(e.reply[i].id).toLowerCase();
					switch( id ){
						case "vapps":
							self.options.vapps = e.reply[i].appdb.application;
							break;
						case "vo":
							self.options.vodata = e.reply[i].appdb.vo || null;
							break;
						case "swapps":
							self.options.swapps = e.reply[i].appdb.application;
							break;
						default: 
							break;
					}
				}
				self.renderLoading(false);
				self.options.vapps = self.options.vapps || [];
				self.options.vapps = $.isArray(self.options.vapps)?self.options.vapps:[self.options.vapps];
				self.options.vodata.imagelist = self.options.vodata.imagelist || [];
				self.options.vodata.imagelist = $.isArray(self.options.vodata.imagelist)?self.options.vodata.imagelist:[self.options.vodata.imagelist];
				self.options.swapps = self.options.swapps || [];
				self.options.swapps = $.isArray(self.options.swapps)?self.options.swapps:[self.options.swapps];
				appdb.model.StaticList.SwapplianceReport = self.options.swapps;
				appdb.model.StaticList.SwapplianceReportUnique = self.getAllAvailableSWAppliances(self.options.swapps);
				self.extractVApps();
				self.render();
			};
		})(this));
	};
	this._initContainer = function(){
		
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent || null;
		this.views.imagelist = new appdb.views.VoImageList({
			container: $(this.dom).children(".voimagelistcontainer"),
			parent: this
		});
		this.views.imagelist.subscribe({ event: "rowadded", callback: function(v){ this.onRowAdded(v); }, caller: this });
		this._initContainer();
	};
	this._init();
});
appdb.components.VapplianceResourceProviders = appdb.ExtendClass(appdb.Component, "appdb.components.VapplianceResourceProviders", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		images: [],
		availableVos: null,
		selectedVoId: "-",
		vos: [],
		dom: {
			list: $("<ul></ul>"),
			vos: null
		}
	};
	this.getImagesByVoId = function(id){
		id = (typeof id === "undefined")?"-":($.trim(id) || "-");
		var imgs = {};
		$.each(this.getProvidersByVoId(id), function(i, e){
			$.each(e.images, function(ii,ee){
				imgs[ee.vmiinstanceid] = ee;
			});
		});
		var res = [];
		for(var i in imgs){
			if( imgs.hasOwnProperty(i) === false ) continue;
			res.push(imgs[i]);
		}
		return res;
	};
	this.loadAvailableVos = function(){
		var appdata = appdb.pages.Application.currentData() || {};
		var app = appdata.application || appdata;
		var vos = [];
		if( app.vo ){
			app.vo = app.vo || [];
			app.vo = $.isArray(app.vo)?app.vo:[app.vo];
			var foundVOs = {};
			$.each(app.vo, function(i,e){
				foundVOs[e.id] = true;
				vos.push($.extend(true,{},e));
			});
			$.each(this.options.vos, function(i,e){
				if(typeof foundVOs[e.id] === 'undefined' && $.trim(e.name)!== '') {
					vos.push({id: e.id, name: e.name});
				}
			});
		}
		vos.sort(function(a,b){
			var aa = $.trim(a.name);
			var bb = $.trim(b.name);
			if( aa < bb ) return -1;
			if( aa > bb ) return 1;
			return 0;
		});
		if( this.getProvidersByVoId("-").length > 0 ){
			vos.push(this.getEmptyVoData());
		}
		var res = [];
		for( var i=0;  i< vos.length; i+=1){
			vos[i].count = this.getImagesByVoId(vos[i].id).length;
			if( vos[i].count > 0 ){
				res.push(vos[i]);
			}
		}
		
		this.options.availableVos = res;
	};
	this.getAvailableVos = function(){
		if( this.options.availableVos === null ){
			this.loadAvailableVos();
		}
		return this.options.availableVos || [];
	};
	this.getEmptyVoData = function(){
		return {
			alias: "none",
			discipline: "other",
			id: "-",
			name: "<none>",
			status: "none"
		};
	};
	this.getVoData = function(id){
		id = (typeof id === "undefined")?"-":($.trim(id) || "-");
		var res = $.grep(this.getAvailableVos(), function(e){
			return e.id === id;
		});
		return (res.length === 0)?null:res[0];
	};
	this.getProvider = function(id){
		id = $.trim(id);
		var res = $.grep((appdb.model.StaticList.VAProviders || []), function(e){
			return $.trim(e.id) === id;
		});
		return (res.length === 0 )?null:res[0];
	};
	this.groupProviderImages = function(providers){
		providers = providers || [];
		providers = $.isArray(providers)?providers:[providers];
		$.each(providers, function(ii,provider){
			provider.images = provider.images || [];
			provider.images = $.isArray(provider.images)?provider.images:[provider.images];
			var res = {};
			var result = [];
			$.each(provider.images, function(i, e){
				var goodid = e.goodid || e.vmiinstanceid;
				if( !goodid ) return;
				e.templates = e.templates || [];
				e.templates = $.isArray(e.templates)?e.templates:[e.templates];
				if( !res[goodid] ){
					res[goodid] = $.extend(true,{}, e);
					res[goodid].items = [];
					res[goodid].template = [];
				}
				res[goodid].template = res[goodid].template.concat(appdb.utils.extendArray( e.templates ));
				res[goodid].items = res[goodid].items.concat( appdb.utils.extendArray([{templates: e.templates, occid: e.occi_id, endpointurl: e.occi_endpoint_url}]) );
			});

			//group duplicates
			for(var i in res ){
				if( res.hasOwnProperty(i) === false ) continue;
				res[i].template = appdb.utils.GroupSiteTemplates(res[i].template);
				res[i].templates = appdb.utils.extendArray(res[i].template);
			}
			for(var i in res ){
				if( res.hasOwnProperty(i) === false ) continue;
				result.push(res[i]);
			}
			provider.images = result;
		});
		
		return providers;
	};
	this.groupProvidersBySite = function(providers){
		if( appdb.config.features.groupvaprovidertemplates === false ){
			return providers;
		}
		providers = providers || [];
		providers = $.isArray(providers)?providers:[providers];
		var uniq = {};
		$.each(providers, function(i,e){
			e.images = e.images || [];
			e.images = $.isArray(e.images)?e.images:[e.images];
			
			$.each(e.images, function(ii,ee){
				ee.occi_endpoint_url = e.endpoint_url;
				ee.instances = [];
			});
			if( !uniq[e.name] ){
				uniq[e.name] = $.extend(true, {}, e);
			}else{
				uniq[e.name].images = uniq[e.name].images.concat(appdb.utils.extendArray(e.images));
			}
		});
		var res = [];
		for(var i in uniq){
			if( uniq.hasOwnProperty(i) === false ) continue;
			res.push(uniq[i]);
		} 
		
		return this.groupProviderImages(res);
	};
	this.getProvidersByVoId = function(id){
		id = ( (typeof id === "undefined")?"-":($.trim(id) || "-") );
		this.options.vos = this.options.vos || [];
		this.options.vos = $.isArray(this.options.vos )?this.options.vos:[this.options.vos];
		var vo = $.grep(this.options.vos, function(e){
			return $.trim(e.id) === id;
		});
		if( vo.length === 0 ){
			return [];
		}
		vo[0].providers = vo[0].providers || [];
		vo[0].providers = $.isArray(vo[0].providers)?vo[0].providers:[vo[0].providers];
		
		return this.groupProvidersBySite(vo[0].providers);
	};
	this.transformData = function(d){
		d = d || this.options.images || [];
		d = $.isArray(d)?d:[d];
		this.options.images = $.grep(d, function(e){
			return ($.trim(e.enabled)!=="false");
		});		
		var vos = {};
		
		$.each(this.options.images,(function(self){ 
			return  function(i, e){
				e.provider = e.provider || [];
				e.provider = $.isArray(e.provider)?e.provider:[e.provider];
				$.each(e.provider, function(ii, ee){
					if( ee.in_production === "false" ) return;
					var pvoid = (typeof ee["void"] ==="undefined")?"-":$.trim(ee["void"]);
					var pvoname = (typeof ee["voname"] === "undefined")?"":$.trim(ee["voname"]);
					vos[pvoid] = vos[pvoid] || {id:pvoid, name: pvoname, providers:{}};
					var pdata = self.getProvider(ee.provider_id);
					var imgs = (pdata && vos[pvoid].providers[pdata.id] && vos[pvoid].providers[pdata.id].images )?vos[pvoid].providers[pdata.id].images:{};
					if( pdata !== null ){
						vos[pvoid].providers = vos[pvoid].providers || {};
						vos[pvoid].providers[pdata.id] = $.extend(true,{},pdata);
						if( vos[pvoid].providers[pdata.id].template ){
							delete vos[pvoid].providers[pdata.id].template;
						}
						if( vos[pvoid].providers[pdata.id].image ){
							delete vos[pvoid].providers[pdata.id].image;
						}
						vos[pvoid].providers[pdata.id].images = vos[pvoid].providers[pdata.id].images || imgs;
						vos[pvoid].providers[pdata.id].images[e.vmiinstanceid] = vos[pvoid].providers[pdata.id].images[e.vmiinstanceid] || $.extend(true,{},e);
						vos[pvoid].providers[pdata.id].images[e.vmiinstanceid].occi_id = ee.occi_id;
						var hyper = vos[pvoid].providers[pdata.id].images[e.vmiinstanceid].hypervisor;
						hyper = hyper || [];
						hyper = $.isArray(hyper)?hypers:[hyper];
						var hyperstr = [];
						$.each(hyper, function(h, hyp){
							if( typeof hyp.val === "function"){
								var hv = $.trim(hyp.val());
								if( hv !== "" ){
									hyperstr.push(hv);
								}
							}
						});
						vos[pvoid].providers[pdata.id].images[e.vmiinstanceid].hypervisors = hyperstr.join(",");
						pdata.template = pdata.template || [];
						pdata.template = $.isArray(pdata.template)?pdata.template:[pdata.template];
						vos[pvoid].providers[pdata.id].images[e.vmiinstanceid].templates = [];
						//assure unique templates
						var utemp = {};
						$.each(pdata.template, function(ti, te){
							if( !utemp[te.resource_id] && $.trim(te.resource_id) !== "" ){
								utemp[te.resource_id] = te;
							}
						});
						for(var t in utemp){
							if( utemp.hasOwnProperty(t) === false ) continue;
							vos[pvoid].providers[pdata.id].images[e.vmiinstanceid].templates.push(utemp[t]);
						}
					}
				});
			};
		})(this));
		
		//Arrayify data
		var res = [];
		for(var v in vos){
			if( vos.hasOwnProperty(v) === false ) continue;
			var vo = vos[v];
			var voprov = [];
			
			for(var p in vo.providers){
				if( vo.providers.hasOwnProperty(p) === false ) continue;
				var prov = vo.providers[p];
				var images = [];
				
				for(var i in prov.images){
					if( prov.images.hasOwnProperty(i) === false) continue;
					images.push(prov.images[i]);
				}
				
				prov.images = images;
				voprov.push(prov);
			}
			
			vo.providers = voprov;
			res.push(vo);
		}
		this.options.vos = res;
	};
	this.renderAvailableVos = function(){
		var preselect = this.options.selectedVoId;
		var avos = this.getAvailableVos();
		var selprovs = [];
		
		var sel = $(this.dom).find(".voselector");
		if( $(sel).length > 0 ){
			if($.trim($(sel).data("preselect")) !== ""){
				preselect = $.trim($(sel).data("preselect")) || this.options.selectedVoId;
			}
			if( $.trim(preselect)!=="-" ){
				selprovs = this.getProvidersByVoId(preselect);
				if( selprovs.length === 0 ){
					preselect = this.options.selectedVoId;
				}
			}
			if( $.trim(preselect)==="-" && avos.length > 0 ){
				preselect = avos[0].id;
			}
			selprovs = this.getProvidersByVoId(preselect);
			if( selprovs.length === 0 ){
				preselect = this.options.selectedVoId;
			}
		}
		
		this.options.selectedVoId = preselect;
		
		$(sel).empty();
		var html = $("<select></select>");
		
		$.each(avos, function(i, e){
			var opt = $("<option value=''></option>");
			$(opt).attr("value",e.id);
			if( $.trim(e.id) === preselect ){
				$(opt).attr("selected");
			}
			var inner = $("<div class='vooption icontext'><img src='' alt=''/><span class='name'></span><span class='details'></span></div>");
			$(inner).find("span.name").text(e.name);
			$(inner).find("img").attr("src", "/vo/getlogo?name="+encodeURI(e.name)+"&vid="+ (e.id<<0) +"&id=" + e.discipline);
			$(inner).find("span.details").text(e.count + " image" + ((e.count>1)?"s":"") +" available");
			$(opt).append(inner);
			$(html).append(opt);
		});
		$(sel).append(html);
		
		if( this.options.dom.vos !== null ){
			this.options.dom.vos.destroyRecursive(false);
			this.options.dom.vos = null;
		}
		this.options.dom.vos = new dijit.form.Select({
			name: "availablevos",
			value: preselect,
			onChange: (function(self){
				return function(v){
					self.renderSelection(v);
				};
			})(this)
		},$(html)[0]);
	};
	this.renderSelection = function(v){
		v = v || this.options.selectedVoId;
		this.options.selectedVoId = v;
		this.renderVoDetailsLink(v);
		this.renderSiteList(v);
	};
	this.renderVoDetailsLink = function(v){
		var vo = this.getVoData(v);
		if( $.trim(v) === "-" || !vo || $.trim(vo.name) === "" ){
			$(this.dom).find(".vodetailslink").addClass("hidden");
			return;
		}
		$(this.dom).find(".vodetailslink > a").attr("href", appdb.config.endpoint.base + "store/vo/" + $.trim(vo.name));
		$(this.dom).find(".vodetailslink").removeClass("hidden");
	};
	this.renderSiteList = function(vo_id){
		this.views.sitelist.render(this.getProvidersByVoId(vo_id));
	};
	this.render = function(){
		if( this.getAvailableVos().length === 0 ){
			$(this.dom).addClass("empty");
		}else{
			$(this.dom).removeClass("empty");
		}
		if( this.getAvailableVos().length > 0 ){
			this.renderAvailableVos();
			this.renderSelection(this.options.selectedVoId);
		}
	};
	this.renderInlineDialog = function(enabled, html, title, classes){
		enabled = (typeof enabled === "boolean")?enabled:false;
		title = $.trim(title);
		classes = $.trim(classes);
		$(this.dom).children(".inlinedialog").remove();
		if( enabled ){
			var dialog = $("<div class='inlinedialog "+classes+"'><div class='shader'/><div class='dialog'><div class='header'></div><div class='message'></div><div class='footer'><div class='action close btn btn-primary btn-compact'>close</div></div></div></div>");
			$(dialog).find(".header").append(title);
			if( title === "" ){
				$(dialog).find(".header").addClass("hidden");
			}
			$(dialog).find(".message").append(html);
			$(dialog).find(".action.close, .shader").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.renderInlineDialog(false);
					return false;
				};
			})(this));
			$(this.dom).append(dialog);
		}
	};
	this.renderLoading = function(enabled, text, classes){
		enabled = (typeof enabled === "boolean")?enabled:false;
		text = $.trim(text) || "Loading";
		text = "..." + text;
		classes = $.trim(classes);
		$(this.dom).children(".actionloader").remove();
		if( enabled ){
			$(this.dom).append("<div class='actionloader "+classes+"'><div class='shader'/><div class='message icontext'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>"+text+"</span></div></div>");
		}
	};
	this.load = function(){
		this.load.attempts = ( (typeof this.load.attempts === "undefined")?200:this.load.attempts ) << 0;
		if( appdb.model.StaticList.VAProviders.length === 0 && this.load.attempts > 0){
			this.load.attempts -= 1;
			this.renderLoading(true, "Loading sites");
			setTimeout((function(self){
				return function(){
					if( self && $(self.dom).length > 0 ){
						self.load();
					}
				};
			})(this),100);
			return;
		}
		this.load.attempts = 10;
		if( this._model ){
			this._model.unsubscribeAll();
			this._model = null;
		}
		this._model = new appdb.model.VapplianceSites({id: appdb.pages.application.currentId()});
		this._model.subscribe({event: "beforeselect", callback: function(v){
			this.renderLoading(true, "Loading sites");
		}, caller: this }).subscribe({event: "select", callback: function(v){
			this.transformData(v);
			this.renderLoading(false);
			this.render();	
		}, caller: this });
		this._model.get();
	};
	this._initContainer = function(){
		if( $(this.dom).find(".list").length === 0 ){
			$(this.dom).append($(this.options.dom.list));
		}else{
			this.options.dom.list = $(this.dom).find(".list");
		}
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
		this.views.sitelist = new appdb.views.VapplianceResourceProvidersList({
			container: $(this.options.dom.list),
			parent: this
		});
	};
	this._init();
});
appdb.components.SwapplianceResourceProviders = appdb.ExtendClass(appdb.components.VapplianceResourceProviders, "appdb.components.SwapplianceResourceProviders", function(o){
	this.load = function(){
		this.load.attempts = ( (typeof this.load.attempts === "undefined")?200:this.load.attempts ) << 0;
		if( appdb.model.StaticList.VAProviders.length === 0 && this.load.attempts > 0){
			this.load.attempts -= 1;
			setTimeout((function(self){
				return function(){
					if( self && $(self.dom).length > 0 ){
						self.load();
					}
				};
			})(this),100);
			return;
		}
		this.load.attempts = 10;
		if( this._model ){
			this._model.unsubscribeAll();
			this._model = null;
		}
		this._model = new appdb.model.SwapplianceSites({id: appdb.pages.application.currentId()});
		this._model.subscribe({event: "beforeselect", callback: function(v){
			this.renderLoading(true, "Loading sites");
		}, caller: this }).subscribe({event: "select", callback: function(v){
			this.transformData(v);
			this.renderLoading(false);
			this.render();	
		}, caller: this });
		this._model.get();
	};
	this.getStatePanel = function(state){
		state = $.trim(state);
		var panel = $(this.options.statepanels).find(".app-state-panel." + state);
		if( $(panel).length > 0 ){
			return $(panel).clone(true);
		}
		return null;
	};
	this._parentInitContainer = this._initContainer;
	this._initContainer = function(){
		$(this.dom).append($(this.options.statepanels).clone(true));
		this._parentInitContainer();
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.statepanels = $(this.dom).find(".app-state-panels").clone(true);
		$(this.dom).find(".app-state-panels").remove();
		this._initContainer();
		$(this.dom).addClass("swappcontents");
		this.views.sitelist = new appdb.views.SwapplianceResourceProvidersList({
			container: $(this.options.dom.list),
			parent: this,
			itemid: appdb.pages.application.currentId()
		});
	};
	this._init();
});

appdb.components.SuggestedItems = appdb.ExtendClass(appdb.Component, "appdb.components.SuggestedItems", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		itemid: o.id,
		data: []
	};
	this.getEntityCount = function(){
		return this.views.list.getEntityCount();
	};
	this.render = function(){
		this.views.list.render(this.options.data);
		$(this.dom).children(".title").find(".count").text("(" + this.getEntityCount() + ")");
		if( this.getEntityCount() === 0 ){
			$(this.dom).addClass("hidden");
		}else{
			$(this.dom).removeClass("hidden");
		}	
	};
	this.renderLoading = function(enabled){
		enabled = (typeof enabled === "boolean")?enabled:true;
		$(this.dom).find(".content .relations .loader").remove();
		if( enabled ){
			$(this.dom).find(".content .relations").append("<div class='loader'><img src='/images/ajax-loader-trans-orange.gif' alt=''/></div>");
		}
	};
	this.load = function(){
		this._model.subscribe({event: "beforeselect", callback: function(v) {
		this.renderLoading();
		}, caller: this}).subscribe({
			event: "select", callback: function(v) {
				v.relatedapp = v.relatedapp || [];
				this.options.data = $.isArray(v.relatedapp)?v.relatedapp:[v.relatedapp];
				this.renderLoading(false);
				this.render();
		}, caller: this}).subscribe({
			event:"error", callback: function(v){
				this.renderLoading(false);
		}, caller: this});
		this._model.get({appid:this.options.itemid,pagelength:20,pageoffset:0});
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._model = new appdb.model.RelatedApplications();
		this.views = {};
		this.views.list = new appdb.views.SuggestedList({
			container: $(this.dom).find(".relations"),
			parent: this
		});
		
	};
	this._init();
});
appdb.ViewLoader = (function(){
    var _v = {};
    var _shouldLoad = function(o){
        var lo = o.loadOn || "always";
		var ret = true;
        switch(lo){
            case "absent":
                //if the view is displayed and is cached it shouldn't be loaded
                if($(o.selector).length>0 && o.cache!==null){
                    ret = false;
                }else{
                    ret = true;
                }
                break;
            case "always":
                ret = true;
                break;
            case "nocache":
                ret = (o.cache===null)?true:false;
                break;
            default:
                ret = true;
                break;
        }
		return ret;
    };
    var _loadView = function(o){
        var v = o.view, c = o.callbacks , a = {
            url : appdb.config.endpoint.base+  v.url,
            type : 'GET',
            success : function(d){
               v.cache = d;
               if(v.isTemplate===true){
                   v.templatepane = new appdb.TemplatePane({content: v.cache,parent:o});
                   v.cache = v.templatepane.content;
               }
               if(c){
                   c.success(v);
               }
            },
            error : function(d,s){
               v.cache = null;
               if(c){
                   c.error(d);
               }
            },
            async : (c)?true:false
        };
        
        $.ajax(a);
        return v.cache;
    };
    var _getCached = function(o){
        var c = o.callbacks;
        if(typeof c === "undefined"){
            return o.view;
        }else{
            if(c.success){
                c.success(o.view);
            }
        }
        return null;
    };
	var _reloadComponent = function(o){
		return (o && o.reloadComponent === true);
	};
    var _getView = function(o){
        return {html:((_shouldLoad(o.view)===true)?_loadView(o):_getCached(o)),reloadComponent: _reloadComponent(o), isTemplate: ((o.view.isTemplate)?o.view.isTemplate:false),templatepane:o.view.templatepane};
    };
    var _clearCache = function(n){
        var i, v = null;
        if(typeof n !== "undefined"){
            if(typeof n === "string"){
                v = [n];
            }else if(typeof n === "array"){
                v = n;
            }
        }        
        if(v===null){
            for(i in _v){
                _v[i].cache = null;
            }
        }else{
            for(i=0; i<v.length; i+=1){
                _v[v].cache = null;
            }
        }
    };
    var _getComponentView = function(type,c){
        for(var i in _v){
            if(_v[i].component===type){
                return _getView({view: _v[i],callbacks :c});
            }
        }
        return null;
    };
    var _init = function(){
        _v["people"] = {loadOn : "nocache", url : "people?flt=role.id:-1", cache : null, selector : "#ppl_main_content",component:"appdb.components.People"};
        _v["apps"] = {loadOn:"nocache", url : "apps?flt=" , cache : null, selector : "#apps_main_content",component:"appdb.components.Applications"};
        _v["vos"] = {loadOn:"nocache", url : "vo/index" , cache : null, selector : "#vos_main_content",component:"appdb.components.VOs"};
		_v["sites"] = {loadOn: "nocache",url:"sites/index",cache:null, selector : "#sites_main_content",component:"appdb.components.Sites"};
		_v["site"] = {loadOn: "nocache",url:"sites/details",cache:null, selector : "#appdb_components_Site",component:"appdb.components.Site"};
		_v["datasets"] = {loadOn: "nocache",url:"datasets/index",cache:null,selector: "#datasets_main_content", component:"appdb.components.Datasets"};
		_v["dataset"] = {loadOn: "always",reloadComponent: true, url:"datasets/details",cache:null,selector: "#appdb_component_Dataset", component:"appdb.components.Dataset"};
        _v["persondetails"] = {loadOn:"nocache",url:"people/details2",cache : null,selector : "#ppl_details",component:"appdb.components.Person",isTemplate : true, templatepane : null};
		_v["reportabuse"] = {loadOn:"nocahce",url: "abuse/report",cache:null,component:"appdb.components.ReportAbuse"};
		_v["linkstatuses"] = {loadOn: "always",url:"news/linkstatus",cache:null,component:"appdb.components.LinkStatuses"};
		_v["dissemination"] = {loadOn: "always",url:"news/dissemination",cache:null,component:"appdb.components.DisseminationTool"};
    };
    _init();
    var _getPeople = function(c){
        return _getView({view : _v.people, callbacks : c});
    };
    var _getApplications = function(c){
        return _getView({view : _v.apps, callbacks : c});
    };
    var _getVOs = function(c){
        return _getView({view : _v.vos, callbacks : c});
    };
    return {
      clearCache : _clearCache,
      getComponentView : _getComponentView,
      getPeople : _getPeople,
      getApplications : _getApplications,
      getVOs : _getVOs
    };
})();
appdb.views.NavigationPane = appdb.ExtendClass(appdb.View,"appdb.views.NavigationPane",function(o){
    this.id = "mainNavigation";
    this.dom = $("#"+this.id);
    this.data = null;
    this._sep = null;
    this._timer = null;
    this._close = null;
    this._toolbar = null;
    this._constructor = function(){
       o = o || {};
        if(typeof o.container !== "undefined"){
            this.dom = $(o.container);
            this.id = $(this.dom).attr("id");
			$(this.dom).css({"display":"none"});
        }
    };
    this.reset = function(){
        this.data = [];
        $(this.dom).empty();
        if(this._sep){
            $(this._sep).empty().remove();
        }
        this._sep = $(document.createElement("span")).css({"padding-right":"3px","padding-left":"3px",display:"inline-block"}).text(">");
        if(this._timer){
            $(this._timer).empty().remove();
        }
         if(appdb.config.appenv!=="production"){
            this._timer = $('<span id="timer" style="float:right;padding-right:5px;" ></span>');
        }else{
            this._timer = $('<span id="timer" style="display:none"></span>');
        }
        if(this._close!==null){
            $(this._close).empty();
        }
        this._close = $(document.createElement("div"));
        $(this._close).attr("style","float:right;margin:0px;padding:0px;vertical-align:top;display:inline-block;position:relative;top:-3px;");
        $(this._close).append('<a href="#" title="click to close current page"><img src="/images/closeview.png" border="0" style="vertical-align:top;top:-3px;padding:0px;margin:0px;" width="18" height="18" alt="close"></img></a>');
        
        $(this.dom).append(this._timer);
    };
    this.destroy = function(){
        this.reset();
        if($(this.dom).length>0){
            $(this.dom).attr("style","display:none;").empty();
        }
        this.dom = null;
    };
    this.render = function(d){
        this.reset();
        d = d || [];
        
        if($.isArray(d)===false){
            d = [d];
        }
        var i , len = d.length,t;
        for(i=0; i<len; i+=1){
            t = d[i];
			if((i+1)===len){
                this.appendCurrentItem(t);
            }else{
                this.appendItem(t);
            }
            if((i+1)<len){
                this.appendSeperator();
            }
            if(i===(len-2)){
				$(this._close).find("a").first().unbind("click").bind("click",(function(c){
                    return function(){
                        if(c.self && c.callerArgs.length===1){
                            c.callerArgs[c.callerArgs.length] = c.self;
                        }
                        c.caller.apply(null,c.callerArgs);
                    };
                })(t));
            }
            
        }
        if(d.length>0){
			$(this.dom).append($(this._toolbar));
        }
		this.data = d;
		if( $(this.dom).css("display") !== "block" ) {
			$(this.dom).css({"height":"0px","display":"none"});
			setTimeout((function(_this){return function(){$(_this.dom).css("display","block").animate({"height":"18px"}, (($.browser.msie)?800:600));};})(this),1);
		}
		
		$(this.dom).find("a[title='Home']").addClass("home");
    };
    this.appendCurrentItem = function(d){
        var txt = (d.text || ""),title = txt,islist = false;
		islist = (d.self && d.self.isList)?true:false;
        if(txt.length>=55){
            txt = txt.substring(0,52) + "...";
        }
		if(islist){
			$(this._close).hide();
		}else{
			$(this._close).show();
		}
		$(this.dom).append("<span id='lastNavigationItem' title='"+title+"' >"+txt+"</span>");
    };
    this.appendItem = function(d){
        this.data[this.data.length] = d;
        var a = $(document.createElement("a")),txt = d.text,title = txt;
        if(txt.length>=55){
            txt = txt.substring(0,52) + "...";
        }
        $(a).attr("title",title).css({"cursor":"pointer"});
		if($.trim(d.icon) !== "") {
			$(a).append("<img src='"+d.icon+"' border='0' class='navigationimage'></img>");
		}
		$(a).append("<span>" + (txt || "title") + "</span>");
		if(d.caller){
			$(a).click(function(){
                if(d.self && d.callerArgs.length===1){
                    d.callerArgs[d.callerArgs.length] = d.self;
                }
                d.caller.apply(null,d.callerArgs);
            });
		}else if(!$(a).attr("href")){
			$(a).attr("href","#!");
		}
        
        $(this.dom).append($(a));
    };
    this.appendSeperator = function(){
        $(this.dom).append($(this._sep).clone());
    };
    this.clearToolbar = function(){
        $("#toolbarContainer").first().empty();
    };
    this.hide = function(){
       if($(this.dom).length>0){
		   if($(this.dom).css("display") === "block"){
			   $(this.dom).animate({"height": "0px"}, (($.browser.msie)?800:600),function(){ 
				   $(this).css("display","none");
			   });
		   }else{
			   $(this.dom).css("display","none");
		   }
           
       }
    };
    this.getLastItemData = function(){
        if(this.data){
            return (this.data.length)?this.data[this.data.length-1]:null;
        }
        return null;
    };
	this.getPreviousData = function(){
		if(!this.data || this.data.length <= 1){
			return "";
		}
		return this.data[this.data.length-2];
	};
    this.setLastItemTitle = function(title){
         if(title.length>=55){
            title = title.substring(0,52) + "...";
        }
        $(this.dom).find("#lastNavigationItem").first().text(title);
		if(this.data){
		 var dd = this.data[this.data.length-1];
		 if(dd.componentType==="appdb.components.Application"){
		  document.title = appdb.Navigator.Registry["Application"].title({"name":title});
		 }else if(dd.componentType==="appdb.components.Person"){
		  document.title = appdb.Navigator.Registry["Person"].title({},{"mainTitle":title});
		 }
		}
    };
    this.closeCurrentItem = function(){
        $(this._close).find("a").click();
    };
    this._constructor();
});

appdb.views.NavigationPanePresets = new function(){
	this.softwareItem = function(d){
		var e = {};
		
		e.mainTitle = "Register New Software";
		if( d.entityType === "virtualappliance" ){
			e.mainTitle = "Register New Virtual Appliance";
			e.prepend = [];
		}else{
			e.prepend = [{
				componentType : "appdb.components.Applications",
				mainTitle : "Software",
				filterDisplay : "Search software...",
				componentCaller : appdb.views.Main.showApplications,
				isList : true,
				content: "software",
				componentArgs : [{flt:"+*&application.metatype:0"},{isBaseQuery : true, mainTitle: "Software", filterDisplay : 'Search software...'}]
			}];
		}
		
		if( parseInt(d.id) > 0 ){
			e.mainTitle = d.name;
			e.prepend.push({
				componentType : "appdb.components.Applications",
				mainTitle : ((d.category && d.category.val)?d.category.val():'uncategorized'),
				componentCaller : appdb.views.Main.showApplications,
				isList : true,
				componentArgs : [{flt:'+*&application.metatype:0 +=category.id:' + ((d.category && d.category.id)?d.category.id:'')},{isBaseQuery : true, mainTitle: ((d.category && d.category.val)?d.category.val():''), filterDisplay : 'Search in '+((d.category && d.category.val)?d.category.val():'')+'...'}]
			});
			
		}
		
        e.append = false;
        e.componentType = "appdb.components.Application";
        e.componentCaller = showDetails;
        e.componentArgs = ["apps/details?id="+d.id];
		e.isList = false;
		return e;
	};
	this.vapplianceItem = function(d){
		var e = {};
		
		e.mainTitle = "Register New Software";
		if( d.entityType === "virtualappliance" ){
			e.mainTitle = "Register New Virtual Appliance";
			e.prepend = [];
		}else{
			e.prepend = [{
				componentType : "appdb.components.Applications",
				mainTitle : "Virtual Appliances",
				filterDisplay : "Search virtual appliances...",
				componentCaller : appdb.views.Main.showVirtualAppliances,
				isList : true,
				content: "vappliance",
				componentArgs : [{flt:"+*&application.metatype:1"},{isBaseQuery : true, mainTitle:"Virtual Appliances", filterDisplay: "Search virtual appliances", content:"vappliance"}]
			}];
		}
		
		if( parseInt(d.id) > 0 && parseInt(d.id) !== 34){
			e.mainTitle = d.name;
			e.prepend.push({
				componentType : "appdb.components.Applications",
				mainTitle : ((d.category && d.category.val)?d.category.val():''),
				componentCaller : appdb.views.Main.showVirtualAppliances,
				isList : true,
				componentArgs : [{flt:'+*&application.metatype:1 +=category.id:' + ((d.category && d.category.id)?d.category.id:'')},{isBaseQuery : true, mainTitle: ((d.category && d.category.val)?d.category.val():''), filterDisplay : 'Search in '+((d.category && d.category.val)?d.category.val():'')+'...', content: "vappliance"}]
			});
			
		}
		
        e.append = false;
        e.componentType = "appdb.components.Application";
        e.componentCaller = showDetails;
        e.componentArgs = ["apps/details?id="+d.id];
		e.isList = false;
		return e;
	};
	this.swapplianceItem = function(d){
		var e = {};
		
		if( !d.id || (d.id << 0) <= 0 ){
			e.mainTitle = "Register New Software Appliance";
			e.prepend = [];
		}else{
			e.prepend = [{
				componentType : "appdb.components.Applications",
				mainTitle : "Software Appliances",
				filterDisplay : "Search software appliances...",
				componentCaller : appdb.views.Main.showCloudSoftwareAppliances,
				isList : true,
				content: "swappliance",
				componentArgs : [{flt:"+*&application.metatype:2"},{isBaseQuery : true, mainTitle:"Software Appliances", filterDisplay: "Search software appliances", content:"swappliance"}]
			}];
		}
		
		if( parseInt(d.id) > 0 ){
			e.mainTitle = d.name;			
		}
		
        e.append = false;
        e.componentType = "appdb.components.Application";
        e.componentCaller = showDetails;
        e.componentArgs = ["apps/details?id="+d.id];
		e.isList = false;
		return e;
	};
	this.personItem = function(d){
		var e = {};
		
		e.mainTitle = "Register New User";
		e.prepend = [{
			componentType : "appdb.components.People",
			mainTitle : "People",
			isBaseQuery : false,
			filterDisplay : "Search people...",
			componentCaller: appdb.views.Main.showPeople,
			componentArgs:[{pagelength:optQueryLen,flt:""}]
		}];
		
		if( parseInt(d.id) > 0 ) {
			e.mainTitle = d.fullName;
			e.prepend.push({
				componentType : "appdb.components.People",
				mainTitle : ((d.role && d.role.description)?d.role.description:''),
				isBaseQuery : false,
				filterDisplay : "Search in " + ((d.role && d.role.description)?d.role.description:'') + "...",
				componentCaller: appdb.views.Main.showPeople,
				componentArgs:[{pagelength:optQueryLen,flt:"+=person.roleid:"+((d.role && d.role.id)?d.role.id:'')},{isBaseQuery : true, mainTitle: ((d.role && d.role.description)?d.role.description:''), filterDisplay : 'Search in '+((d.role && d.role.description)?d.role.description:'')+'...'}]
			});
		}
		
		e.append = false;
		e.componentType = "appdb.components.Person";
		e.componentCaller = appdb.views.Main.showPerson;
        e.componentArgs = [{id:d.id}];
		e.isList = false;
		
		return e;
	};
	this.voItem = function(d){
		var e = {};
		e.prepend = [];
		var disc = (d && ($.inArray($.trim(d.discipline).toLowerCase(),["","unknown"])===-1))?d.discipline:"";
		if( disc.length > 25 ){
			disc = disc.substr(0,22) + "...";
		}
		e.mainTitle = "Virtual Organizations > " + ((d && disc)?disc + " > ":"") + d.name;
		e.append = false;
		e.componentCaller = appdb.views.Main.showVO;
        e.componentArgs = [d.name];
		e.isList = false;
		return e;
	};
	this.siteItem = function(d){
		var e = {};
		e.prepend = [];
		e.prepend.push({
			componentType : "appdb.components.Sites",
			mainTitle : "Sites/Resource Providers",
			componentCaller : appdb.views.Main.showCloudSites,
			isList : true,
			componentArgs : [],
			content: "vappliance"
		});
		e.mainTitle = d.officialname || d.name;
		e.append = true;
		e.componentCaller = appdb.views.Main.showSite;
        e.componentArgs = [d.id];
		e.isList = false;
		e.content = "vappliance";
		return e;
	};
	this.datasetItem = function(d){
		var e = {};
		d = d || {};
		e.prepend = [];
		if( $.inArray($.trim(d.id), ["","0"])>-1){
			e.mainTitle = "Register New Dataset";
		}else{
			e.mainTitle = d.officialname || d.name;
			e.prepend.push({ componentType : "appdb.components.Datasets",
				mainTitle : "Datasets",
				isBaseQuery : false,
				filterDisplay : "Search datasets...",
				isList : true,
				content: "dataset",
				componentCaller: appdb.views.Main.showDatasets,
				componentArgs:[{pagelength:optQueryLen,flt:""}],
				href: "/browse/datasets"
			});
		}
		e.append = true;
		e.componentCaller = appdb.views.Main.showDataset;
        e.componentArgs = [d.id];
		e.isList = false;
		e.content = "dataset";
		return e;
	};
	appdb.config.entities.software.navigationpanel = this.softwareItem;
	appdb.config.entities.vappliance.navigationpanel = this.vapplianceItem;
	appdb.config.entities.swappliance.navigationpanel = this.swapplianceItem;
	appdb.config.entities.dataset.navigationpanel = this.datasetItem;
};

appdb.views.Main = (function(){
    var _navpane =null, _navData = [], _currentState = {},_onRefresh = false,_currentPaging = {length:0,offset:0};
    $(document).ready(function(){
        _navpane = new appdb.views.NavigationPane({container:"#mainNavigation"});
    });
    var _currentComponent = null, _container = null,_currentContent = null, _loaded = {};
    var _appendNavigation = function(e){
        if(typeof e.mainTitle === "undefined"){
			if ( _currentComponent !== null ) e.mainTitle = _currentComponent.getComponentTitle();
        }
        _navData[_navData.length] = {text: e.mainTitle , caller : e.componentCaller,callerArgs:e.componentArgs,componentType:e.componentType,self:e};
		if(e.icon){
			_navData[_navData.length-1].icon = e.icon;
		}
        _navpane.render(_navData);
    };
    var _createNavigationList = function(e){
		if(_onRefresh){
			_onRefresh=false;
			return;
		}
		e = e || {};
		if(e.prepend){
			if($.isArray(e.prepend)===false){
				e.prepend = [e.prepend];
			}
		}else{
			e.prepend = [];
		}

		if(e.prepend.length>0 && _navData.length>0 &&
			_navData[_navData.length-1].componentType === e.prepend[e.prepend.length-1].componentType &&
			_navData[_navData.length-1].componentType !== e.componentType ){
            if(e.previousPager &&
                e.previousPager.pageoffset &&
                _navData[_navData.length-1].callerArgs.length>0){
                _navData[_navData.length-1].callerArgs[0].pageoffset = e.previousPager.pageoffset;
            }
            
		  _appendNavigation(e);
		   return;
		}
		_clearNavigation();
		if(_navData.length===0){
			_navData[_navData.length] = {text: "Home", title: "Return to home page", caller : appdb.views.Main.showHome,callerArgs:[]};
		}
		if(e.prepend){
			for(var i=0; i<e.prepend.length; i+=1){
                if(e.prepend[i].isTerminal){
                    break;
                }
				_appendNavigation(e.prepend[i]);
			}
		}
		_appendNavigation(e);
    };
    var _clearNavigation = function(){
        _navData = [];
        _navpane.reset();
    };
    var _subContainer = function(typename,contents){
        var tn = typename.replace(/\./g,"_"),res;
        var tni = "#"+tn;
        if($("#main").children(tni).length>0){
            return $(tni);
        }else{
            res = $(document.createElement("div"));
            res.attr("id",tn);
            res.attr("dojoType","dijit.layout.ContentPane" );
            res.css({"width":"100%","height":"100%"});
            $("#main").append(res);
            if(contents){
                $(res).append(contents);
            }
        }
        return res;
    };
    var _setViewContent = function(d){
        $("#details").empty().hide();
        var sc = null, bc = null;
        if(_currentContent===null || (d && d.reloadComponent === true) ){
            $("#main").empty();
            sc = _subContainer(d.componentType);
            $(sc).append(d.cache);
            $(sc).show();
        }else{
            sc = _subContainer(_currentContent.componentType);
            if(_currentContent.componentType!==d.componentType){
                if(_currentComponent){
                    _currentComponent.destroy();
                }
                bc = _subContainer(d.componentType,d.cache);
                $(sc).hide();
                $(bc).show();
            }
        }
        _currentContent = d;
    };
    var _getContainer = function(){
        if(_container===null){
            _container = $("#main");
        }
        return _container;
    };
    var _startLoading = function(t){
		$("#main").addClass("loading");
        var iscached = (_currentComponent)?true:false;
        if($("#details").is(":visible")){
            $("#main").hide();
            showAjaxLoading();
        }
        if(_currentComponent){
           if(_currentComponent.getComponentType()!==t){
                _resetComponent();
                iscached = false;
            }
        }
        $("#details").empty().hide();
        return iscached;
    };
    var _endLoading = function(){
       hideAjaxLoading();
	   $("#main").removeClass("loading");
       $("#main").show();
    };
    var _resetComponent = function(){
        _currentComponent.destroy();
        _currentComponent = null;
    };
    var _getComponent = function(type){
        var arr = type.split("."),len = arr.length,i,res = window;
        for(i=0; i<len; i+=1){
            res = res[arr[i]];
        }
        return res;
    };
    var _parseQuery = function(q){
        if(typeof q === "undefined"){
            return {};
        }
        if($.isPlainObject(q)){
            return q;
        }
        if($.trim(q)===""){
            return {};
        }
        var res = "{", ql = q.split("&"), ol = [],i;
        for(i=0; i<ql.length; i+=1){
            ol = ql[i].split("=");
            if(ol.length===2){
                res += ol[0] + ":" + ((ol[1]==='null')?"''":"'"+ol[1]+"'") + ",";
            }
        }
        res = res.substring(0,res.length-1);
        res += "}";
        res = eval("("+res+")");
        return res;
    };
    var _showComponent = function(o,e){
        var iscached = _startLoading(e.componentType || ""),c = _getComponent(e.componentType || "");
        
        var d = appdb.ViewLoader.getComponentView(e.componentType,{
            success : function(d){
				_endLoading();
                d.componentType = e.componentType;
                
                if(!iscached || d.reloadComponent === true){
                    if(_currentComponent && _currentComponent.destroy && _currentComponent.getComponentType() !== e.componentType){
                        if( typeof _currentComponent.reset === "function" ) { _currentComponent.reset(); }
                        if( typeof _currentComponent.destroy === "function" ) { _currentComponent.destroy(); }
                    }
                    _setViewContent(d);
                    if(d.isTemplate){
                        d.templatepane.parentContent = _subContainer(e.componentType);
                        _currentComponent = new c({content:d.cache,container:_subContainer(e.componentType),contents:d.cache,templatepane:d.templatepane,ext:e});
                    }else{
                        _currentComponent = new c({content:d.cache,container:_getContainer(),ext:e});
                    }
                }else{
                    _setViewContent(d);
                }
                if(e.mainTitle){
                    _currentComponent.setComponentTitle(e.mainTitle);
                }
                _currentComponent.subscribe({event:'loaded',callback: function(){
                    _createNavigationList(e);
                    _endLoading();
                    this.unsubscribeAll(_currentComponent);
                    window.onresize();
                },caller:_currentComponent}).
                clearEvents().
                load({query:o,ext:e});
            },
            error : _endLoading
        });
    };
    var _showDeletedPeople = function(o,e){
		_currentState = {callback : _showDeletedPeople,query:o,ext:e};
		o = o || {userid:userID, id:userID};
        e = e || {mainTitle:'Deleted',content:'researchers'};
        e.subType = "deleted";
        if(typeof e.componentCaller === "undefined"){
            e.componentCaller = appdb.views.Main.showDeletedPeople;
        }
		
		e.href = e.href || '/browse/people/deleted';
        _showPeople(o,e);
		_selectActiveLinks(e.href);
    };
	var _showPeople = function(o,e){
		_currentState = {callback : _showPeople,query:o,ext:e};
        o = o || {flt : ''};
		e = e || {filterDisplay: 'Search...',mainTitle:'People', content: 'researchers'};
        if(userID){
            o.userid = userID;
        }
		if( $.trim(o.orderby) === "" ){
			o.orderby = "rank";
			o.orderbyOp = "DESC";
		}
		o.peopletype = e.subType || "";
        o.pagelength = optQueryLen;
        e.append = false;
        e.componentCaller = e.componentCaller || appdb.views.Main.showPeople;
        e.mainTitle = e.mainTitle || ((o.flt)?"Filtered":"People");
        if(o.flt!==""){
            e.prepend = e.prepend ||  {
                componentType : "appdb.components.People",
                mainTitle : "People",
                isBaseQuery : false,
                userQuery : '',
                filterDisplay : "Search people...",
				isList : true,
                componentCaller: appdb.views.Main.showPeople,
                componentArgs:[{pagelength:optQueryLen,flt:""}]
            };
            if($.isArray(e.prepend)){
                for(var i=0; i<e.prepend.length; i+=1){
                    e.prepend[i].componentCaller =  e.prepend[i].componentCaller || appdb.views.Main.showPeople; //if from permalink
                }
            }else{
				e.prepend = [e.prepend];
			}
        }
        e.componentType = e.componentType || "appdb.components.People";
        e.filterDisplay = e.filterDisplay || "Search people...";
		var f = "";
        if(e.isBaseQuery){
			f = ((e.baseQuery)?$.trim(e.baseQuery.flt):"");
            e.baseQuery = o;
			if( f ){
				e.baseQuery.flt = f;
			}
        }else if(!e.userQuery && (!e.systemQuery || !e.systemQuery.length > 0)){
			f = ((e.userQuery)?$.trim(e.userQuery.flt):"");
            e.userQuery = o;
			if( f ){
				e.userQuery.flt = f;
			}
		}
        e.componentArgs =   [o];
		e.isList = true;
        _showComponent(o,e);
		appdb.views.Main.selectAccordion("peoplepane",e.mainTitle);
		appdb.pages.index.setCurrentContent("researchers",false);
		_selectActiveLinks(e.href || '/browse/people');
	};
	var _showPeopleByRole = function(roleid, e){
		roleid = $.trim(roleid);
		var o = {flt:'+=person.roleid:' + roleid};
		var rolename = "";
		$.each(appdb.model.StaticList.Roles, function(i, e){
			if( $.trim(e.id) === $.trim(roleid) ){
				rolename = e["type"];
			}
		});
		rolename = $.trim(rolename) || "profiles";
		var ext = {isBaseQuery : true,filterDisplay: 'Search in ' + rolename + '...',mainTitle: rolename };
		e = e || {};
		var ext = $.extend(ext, e);
		ext.href = '/browse/people/role/' + roleid;
		_showPeople(o, ext);
		_selectActiveLinks(ext.href);
	};
	var _showPeopleByGroup = function(groupid, e){
		groupid = $.trim(groupid);
		var o = {flt:'+=accessgroup.id:' + groupid};
		var groupname = "";
		$.each(appdb.model.StaticList.AccessGroups, function(i, e){
			if( typeof e.val === "function" && $.trim(e.id) === $.trim(groupid) ){
				groupname = e.val();
			}
		});
		groupname = $.trim(groupname) || "profiles";
		var ext = {isBaseQuery : true,filterDisplay: 'Search in ' + groupname + '...',mainTitle: groupname };
		e = e || {};
		var ext = $.extend(ext, e);
		ext.href = '/browse/people/accessgroup/' + groupid;
		_showPeople(o, ext);
		_selectActiveLinks(ext.href);
	};
    var _showPerson = function(o,e){
		var i;
		_currentState = {callback : _showPerson,query:o,ext:e};
        e = e || {};
        o = _parseQuery(o);
        if(userID){
            o.userid = userID;
        }
		if ( userID !== null ) {
            if(o.id==0){
				setTimeout(function(){showPplDetails('/people/details?id='+o.id+'&userid='+userID, true);},1);
            }else{
				var tab = ($.trim(e.tab))?"&tab=" + encodeURI(e.tab.toLowerCase()):"";
                setTimeout(function(){showDetails('/people/details?id='+o.id+'&userid='+userID+tab, '');},1);
            }
			_selectActiveLinks("/store/person/");
        } else {
			e.componentCaller = e.componentCaller || appdb.views.Main.showPerson;
			e.componentType = e.componentType || "appdb.components.Person";
			e.componentArgs = [o];
            _showComponent(o,e);
        }
		appdb.pages.index.setCurrentContent("researchers",false);
		return false;
    };
	var _showEverything = function(o,e) {
		_showApplications(o,e);
		_showPeople(o,e);
		_showVOs(o,e);
	};
    var _showApplications = function(o,e){
		_currentState = {callback : _showApplications,query:o,ext:e};
        e = e || {};
        o.applicationtype = e.subType || "applications";
        if(optQueryLen<=0){
            window.onresize();
        }
        if(userID){
            o.userid = userID;
        }
		e.content = $.trim(e.content).toLowerCase() || "software";
		var precompcaller = appdb.views.Main.showApplications;
		if( e.content === "vappliance"){
			precompcaller = appdb.views.Main.showVirtualAppliances;
		}else if( e.content === "swappliance" ){
			precompcaller = appdb.views.Main.showSoftwareAppliances;
		}
		var baseq = "+*&application.metatype:0";
		if( $.trim(e.content).toLowerCase() === "vappliance" ){
			baseq = "+*&application.metatype:1";
		}else if($.trim(e.content).toLowerCase() === "swappliance"){
			baseq = "+*&application.metatype:2";
		}
        o.pagelength = optQueryLen;
        e.append = e.append || false;
        e.componentCaller = e.componentCaller || precompcaller;
        e.mainTitle = e.mainTitle || ((o.flt)?"Filtered":"Software");
        e.componentType = e.componentType || "appdb.components.Applications";
        e.filterDisplay = e.filterDisplay || "";
		
        if($.trim(o.flt)!==""){
			if( o.flt.replace(/\ /g,"").indexOf("+*&application.metatype:1") > -1){
				e.prepend = e.prepend || [];
			}else if($.trim(o.flt).toLowerCase().replace(/\ /g,"") === "+*&application.metatype:0"){
				e.prepend = [];
			}else{
				if($.trim(e.content) === "vappliance"){
					e.prepend =  e.prepend || [{
						componentType : "appdb.components.Applications",
						mainTitle : "Virtual Appliances",
						isBaseQuery : true,
						filterDisplay : "Search virtual appliances...",
						isList : true,
						content: "vappliance",
						componentCaller: appdb.views.Main.showVirtualAppliances,
						componentArgs:[{pagelength:optQueryLen,flt:""},{content:"vappliance"}],
						href: "/browse/cloud/vappliances/category/34"
					}];
				}else if ($.trim(e.content) === "swappliance"){
					e.prepend =  e.prepend || [{
						componentType : "appdb.components.Applications",
						mainTitle : "Software Appliances",
						isBaseQuery : true,
						filterDisplay : "Search software appliances...",
						isList : true,
						content: "swappliance",
						componentCaller: appdb.views.Main.showSoftwareAppliances,
						componentArgs:[{pagelength:optQueryLen,flt:""},{content:"swappliance"}],
						href: "/browse/swappliances/category/34"
					}];
				}else{
					e.prepend = e.prepend || [{
						componentType : "appdb.components.Applications",
						mainTitle : "Software",
						isBaseQuery : false,
						filterDisplay : "Search software...",
						isList : true,
						content: "software",
						componentCaller: appdb.views.Main.showApplications,
						componentArgs:[{pagelength:optQueryLen,flt:""}],
						href: "/browse/software"
					}];
				}
			}
			if($.isArray(e.prepend)){
                for(var i=0; i<e.prepend.length; i+=1){
					e.prepend[i].componentCaller =  e.prepend[i].componentCaller || precompcaller; //if from permalink
					e.prepend[i].content = e.content;
                }
            }
        }else{
			o.flt = baseq;
			e.isBaseQuery = true;
		}
		var f = "";
        if(e.isBaseQuery){
			f = ((e.baseQuery)?$.trim(e.baseQuery.flt):"");
            e.baseQuery = o;
			if( f ){
				e.baseQuery.flt = f;
			}
			if( $.trim(e.baseQuery.flt).toLowerCase() !== $.trim(baseq).toLowerCase() ){
				e.baseQuery.flt = baseq + " " + $.trim(e.baseQuery.flt);
			}
        }else if(!e.userQuery && (!e.systemQuery || !e.systemQuery.length > 0)){
			f = ((e.userQuery)?$.trim(e.userQuery.flt):"");
            e.userQuery = o;
			if( f ){
				e.userQuery.flt = f;
			}
			e.baseQuery = { flt : baseq,  pagelength: optQueryLen };
		}
		e.isList = (typeof e.isList === "boolean")?e.isList:true;
        e.componentArgs =  [o];
        _showComponent(o,e);
		var mt = e.mainTitle || "Software";
		if(mt.length>4 && mt.substr(0,4) === "tag:"){
			mt = "Software";
		} else if(mt.length>8 && mt.substr(0,8)==="Related:") {
			mt = "Software";
		}
        appdb.views.Main.selectAccordion("applicationspane",mt);
		if( e.content === "vappliance" ){
			appdb.pages.index.setCurrentContent("vappliance",false);
		} else if( e.content === "swappliance" ){
			appdb.pages.index.setCurrentContent("swappliance",false);
		} else {
			appdb.pages.index.setCurrentContent("software",false);
		}
		var href = e.href;
		if( !href ){
			href = '/browse/';
			if($.trim(e.content)==='vappliance'){
				href += 'cloud/vappliances';
			}else if($.trim(e.content)==='swappliance'){
				href += 'cloud/swappliances';
			}else{
				href += 'software';
			}
		}
		_selectActiveLinks(e.href || '/browse/' + (($.trim(e.content)==='vappliance')?'cloud/vappliances':'software') + '');
    };
	var _showOrderedSoftware = function(order, e){
		var orderdata = $.trim(order).split(" ");
		var orderby = "relevance";
		orderbyop = "DESC";
		if( orderdata.length > 0 ){
			orderby = orderdata[0];
		}
		if( orderdata.length > 1 ){
			orderbyop = orderdata[1];
		}
		var o = {flt:'+*&application.metatype:0', orderby: orderby, orderbyOp: orderbyop};
		var ext = {isBaseQuery: true, mainTitle: 'Software', filterDisplay: "Search in software...", content: "software", componentCaller: appdb.views.Main.showOrderedSoftware};
		ext.href = "/browse/software";
		switch( orderby ){
			case "rating":
				ext.mainTitle = "Top rated software";
				ext.href = "/browse/software/toprated";
				break;
			case "dateadded":
				ext.mainTitle = "Most recent software";
				ext.href = "/browse/software/newest";
				break;
			case "hitcount":
				ext.mainTitle = "Most visited software";
				ext.href = "/browse/software/mostvisited";
				break;
			case "lastupdated":
				ext.mainTitle = "Recently updated software";
				ext.href = "/browse/software/recentlyupdated";
				break;
			default:
				break;
		}
		ext = $.extend(ext,e);

		_showApplications(o, ext);
		_selectActiveLinks(ext.href);
		return false;
	};
	var _showVirtualAppliances = function(o,e){
		o = o || {};
		o.flt = $.trim(o.flt).replace(/\+\=category\.id\:34/g,"").replace(/\+\=\&category\.id\:34/g,"");
		
		e = e || {};
		e.content = "vappliance";
		e.isBaseQuery = ( $.trim(o.flt) === "" )?true:(e.isBaseQuery || false);
		e.isList = (typeof e.isList === "boolean")?e.isList:true;
		e.append = e.append || false;
		e.componentCaller = e.componentCaller || appdb.views.Main.showVirtualAppliances;
        e.mainTitle = e.mainTitle || ((o.flt)?"Filtered":"Virtual Appliances");
        e.componentType = e.componentType || "appdb.components.Applications";
        e.filterDisplay = e.filterDisplay || "Search virtual appliances...";
		e.href = e.href || "/browse/cloud/vappliances/category/34";
		o.flt = ( $.trim(o.flt) === "" )?"+*&application.metatype:1":$.trim(o.flt);
		_showApplications(o,e);
	};
	var _showOrderedSWAppliances = function(order, e){
		var orderdata = $.trim(order).split(" ");
		var orderby = "relevance";
		orderbyop = "DESC";
		if( orderdata.length > 0 ){
			orderby = orderdata[0];
		}
		if( orderdata.length > 1 ){
			orderbyop = orderdata[1];
		}
		var o = {flt:'+*&application.metatype:2', orderby: orderby, orderbyOp: orderbyop};
		var ext = {isBaseQuery: true, mainTitle: 'Software Appliances', filterDisplay: "Search in software appliances...", content: "swappliance", componentCaller: appdb.views.Main.showOrderedSWAppliances};
		ext.href = "/browse/swappliances";
		switch( orderby ){
			case "rating":
				ext.mainTitle = "Top rated software appliances";
				ext.href = "/browse/swappliances/toprated";
				break;
			case "dateadded":
				ext.mainTitle = "Most recent software appliances";
				ext.href = "/browse/swappliances/newest";
				break;
			case "hitcount":
				ext.mainTitle = "Most visited software appliances";
				ext.href = "/browse/swappliances/mostvisited";
				break;
			case "lastupdated":
				ext.mainTitle = "Recently updated software appliances";
				ext.href = "/browse/swappliances/recentlyupdated";
				break;
			default:
				break;
		}
		ext = $.extend(ext,e);

		_showSoftwareAppliances(o, ext);
		_selectActiveLinks(ext.href);
		return false;
	};
	var _showSoftwareAppliances = function(o,e){
		o = o || {};
		o.flt = $.trim(o.flt);
		
		e = e || {};
		e.content = "swappliance";
		e.isBaseQuery = ( $.trim(o.flt) === "" )?true:(e.isBaseQuery || false);
		e.isList = (typeof e.isList === "boolean")?e.isList:true;
		e.append = e.append || false;
		e.componentCaller = e.componentCaller || appdb.views.Main.showSoftwareAppliances;
        e.mainTitle = e.mainTitle || ((o.flt)?"Filtered":"Software Appliances");
        e.componentType = e.componentType || "appdb.components.Applications";
        e.filterDisplay = e.filterDisplay || "Search software appliances...";
		e.href = e.href || "/browse/swappliances/cloud";
		o.flt = ( $.trim(o.flt) === "" )?"+*&application.metatype:2":$.trim(o.flt);
		_showApplications(o,e);
	};
	var _showCloudSoftwareAppliances = function(){
		var swappprepend = {
			componentType : "appdb.components.Applications",
			mainTitle : "Cloud Marketplace",
			isBaseQuery : false,
			filterDisplay : "Search software appliances...",
			isList : true,
			content: "swappliance",
			componentCaller: appdb.views.Main.showCloudMarketplace,
			componentArgs:[{pagelength:optQueryLen,flt:""}],
			href: "/browse/cloud"
		};
		_showSoftwareAppliances({},{filterDisplay:'Search software appliances...',mainTitle : 'Software Appliances', content: 'swappliance',prepend: swappprepend, callerName: "CloudSoftwareAppliances", href: '/browse/swappliances/cloud'});
	};
	var _showOrderedVAppliances = function(order, e){
		var orderdata = $.trim(order).split(" ");
		var orderby = "relevance";
		orderbyop = "DESC";
		if( orderdata.length > 0 ){
			orderby = orderdata[0];
		}
		if( orderdata.length > 1 ){
			orderbyop = orderdata[1];
		}
		var o = {flt:'+*&application.metatype:1', orderby: orderby, orderbyOp: orderbyop};
		var ext = {isBaseQuery: true, mainTitle: 'Virtual Appliances', filterDisplay: "Search in virtual appliances...", content: "vappliance"};
		ext.href = "/browse/cloud/vappliances";
		switch( orderby ){
			case "rating":
				ext.mainTitle = "Top rated virtual appliances";
				ext.href = "/browse/cloud/vappliances/toprated";
				break;
			case "dateadded":
				ext.mainTitle = "Most recent virtual appliances";
				ext.href = "/browse/cloud/vappliances/newest";
				break;
			case "hitcount":
				ext.mainTitle = "Most visited virtual appliances";
				ext.href = "/browse/cloud/vappliances/mostvisited";
				break;
			case "lastupdated":
				ext.mainTitle = "Recently updated virtual appliances";
				ext.href = "/browse/cloud/vappliances/recentlyupdated";
				break;
			default:
				break;
		}
		ext = $.extend(ext,e);
		_showApplications(o, ext);
		_selectActiveLinks(ext.href);
		return false;
	};
	var _showOrderedPeople = function(order, e){
		var orderdata = $.trim(order).split(" ");
		var orderby = "relevance";
		orderbyop = "DESC";
		if( orderdata.length > 0 ){
			orderby = orderdata[0];
		}
		if( orderdata.length > 1 ){
			orderbyop = orderdata[1];
		}
		var o = {flt:'', orderby: orderby, orderbyOp: orderbyop};
		
		var ext = {filterDisplay: 'Search people...', mainTitle:'People Registry', content: "researchers"};
		ext.href = "/browse/cloud/vappliances";
		switch( orderby ){
			case "dateinclusion":
				ext.mainTitle = "Newest profiles";
				ext.href = "/browse/people/newest";
				break;
			case "lastupdated":
				ext.mainTitle = "Recently updated profiles";
				ext.href = "/browse/people/recentlyupdated";
				break;
			default:
				break;
		}
		ext = $.extend(ext,e);
		_showPeople(o, ext);
		_selectActiveLinks(ext.href);
		return false;
	};
	var _showVirtualAppliance = function(o,e,data){
		o.entitytype = "virtualappliance";
		_showAppDetails(o,e,data);
	};
	var _showSoftwareAppliance = function(o,e,data){
		o.entitytype = "softwareappliance";
		_showAppDetails(o,e,data);
	};
	var _getFlatCategories = function(cat){
		var res = [];
		cat.children = cat.children || [];
		cat.children = $.isArray(cat.children)?cat.children:[cat.children];
		res.push({value: cat.value, id: cat.id});
		$.each(cat.children, function(i, e){
			res = res.concat(_getFlatCategories(e));
		});
		
		return res;
	};
	var _showCategories = function(o, e){
		var id = -1;
		var content = (e && $.trim(e.content).toLowerCase()!=="")?$.trim(e.content).toLowerCase():"software";
		if( $.isPlainObject(o) === false ){
			id = o;
			o = {flt: "+=category.id:" + o};
		}else{
			id = o.flt.replace( /^\D+/g, '');
		}
		
		
		if( content === "software" ){
			e.prepend =  e.prepend || [{
			   componentType : "appdb.components.Applications",
			   mainTitle : "Software",
			   isBaseQuery : true,
			   filterDisplay : "Search software...",
			   isList : true,
			   componentCaller: appdb.views.Main.showApplications,
			   componentArgs:[{pagelength:optQueryLen,flt:"+*&application.metatype:0"}]
		   }];
	   }else if( content === "vappliance") {
		  e.prepend = [];
	   } else if ( content === "swappliance" ){
		  e.prepend =  e.prepend || [{
			   componentType : "appdb.components.Applications",
			   mainTitle : "Software Appliances",
			   isBaseQuery : true,
			   filterDisplay : "Search software appliacnes...",
			   isList : true,
			   componentCaller: appdb.views.Main.showSoftwareAppliances,
			   componentArgs:[{pagelength:optQueryLen,flt:"+*&application.metatype:2"}]
		   }];
	   }
		if($.isArray(e.prepend)){
			for(var i=0; i<e.prepend.length; i+=1){
				e.prepend[i].componentCaller =  e.prepend[i].componentCaller || appdb.views.Main.showApplications; //if from permalink
			}
		}else{
			e.prepend = [e.prepend];
		}
		var cdv = new appdb.views.CategoriesDataView();
		cdv.load(appdb.model.ApplicationCategories.getLocalData());
		var sdv = cdv.getSubDataViewByIds([""+id]);
		var td = sdv.transformData();
		var cats = (td.length > 0 )?_getFlatCategories(td[0]):[];
		if( cats.length > 0 ){
			cats = cats.splice(0, cats.length -1 );
		}
		$.each(cats, function(index, cat){
			e.prepend.push({
				componentType : "appdb.components.Applications",
				mainTitle : cat.value,
				isBaseQuery : true,
				filterDisplay : "Search " + cat.value,
				isList : true,
				componentCaller: appdb.views.Main.showCategories,
				componentArgs:[{pagelength:optQueryLen,flt:"+=category.id:" + cat.id}]
			});
		});
		e.componentCaller = appdb.views.Main.showCategories;
		e.componentArgs = [{pagelength:optQueryLen,flt:o.flt}];
		e.navigationType = "Categories";
		e.href = e.href || '/browse/' + (($.trim(e.content)==='vappliance')?'cloud/vappliances':'software') + '/category/' + id;
		_showApplications(o, e);
		_selectActiveLinks(e.href);
	};
	var _showVAppCategories = function(o, e){
		var id = -1;
		if( $.isPlainObject(o) === false ){
			id = o;
			o = {flt: "+=category.id:" + o};
		}else{
			id = o.flt.replace( /^\D+/g, '');
		}
		if( e && $.trim(e.content).toLowerCase() === "vappliance" && $.trim(id)!=="34" ){
			e.prepend =  e.prepend || [{
			   componentType : "appdb.components.Applications",
			   mainTitle : "Virtual Appliances",
			   isBaseQuery : true,
			   filterDisplay : "Search virtual appliances...",
			   isList : true,
			   content: "vappliance",
			   componentCaller: appdb.views.Main.showVirtualAppliances,
			   componentArgs:[{pagelength:optQueryLen,flt:""},{content:"vappliance"}]
		   }];
	   }else{
		   e.prepend = [];
	   }
		if($.isArray(e.prepend)){
			for(var i=0; i<e.prepend.length; i+=1){
				e.prepend[i].componentCaller =  e.prepend[i].componentCaller || appdb.views.Main.showVirtualAppliances; //if from permalink
			}
		}else{
			e.prepend = [e.prepend];
		}
		e.componentCaller = appdb.views.Main.showVAppCategories;
		e.componentArgs = [{pagelength:optQueryLen,flt:o.flt}];
		e.navigationType = "Categories";
		e.href = e.href || '/browse/cloud/vappliances/category/' + id;
		_showVirtualAppliances(o, e);
		_selectActiveLinks(e.href);
	};
    var _showDiscipline = function(o,e){
		if( $.isPlainObject(o) === false ){
			o = {flt: "+=discipline.id:" + o};
		}
		_currentState = {callback : _showDiscipline,query:o,ext:e};
        e = e || {};
        e.append = false;
        e.componentCaller = appdb.views.Main.showDiscipline;
        e.mainTitle = e.mainTitle || "Discipline";
        if(o.flt!==""){
            e.prepend = e.prepend || [{
                componentType : "appdb.components.Applications",
                mainTitle : "Software",
                isBaseQuery : false,
                filterDisplay : "Search software...",
                componentCaller: appdb.views.Main.showApplications,
                componentArgs:[{pagelength:optQueryLen,flt:""}]
            }];
            if($.isArray(e.prepend)){
                for(var i=0; i<e.prepend.length; i+=1){
                    e.prepend[i].componentCaller = e.prepend[i].componentCaller || appdb.views.Main.showApplications; //if from permalink
                }
            }
        }
        e.componentType = e.componentType || "appdb.components.Applications";
        e.filterDisplay = e.filterDisplay || "Search in discipline...";
        if(e.isBaseQuery){
            e.baseQuery = o;
        }
        e.componentArgs = [o];
        _showApplications(o,e);
        appdb.views.Main.selectAccordion("applicationspane","Software");
		_selectActiveLinks('/browse/none');
    };
    var _showSubdiscipline = function(o,e){
		_currentState = {callback : _showSubdiscipline,query:o,ext:e};
        e = e || {};
        e.append = false;
        e.componentCaller = appdb.views.Main.showSubdiscipline;
        e.mainTitle = e.mainTitle || "Subdiscipline";
        if(o.flt!==""){
            e.prepend = e.prepend || [{
                componentType : "appdb.components.Applications",
                mainTitle : "Software",
                isBaseQuery : false,
				isList : true,
                filterDisplay : "Search software...",
                componentCaller: appdb.views.Main.showApplications,
                componentArgs:[{pagelength:optQueryLen,flt:""}]
            }];
            if($.isArray(e.prepend)){
                for(var i=0; i<e.prepend.length; i+=1){
                    e.prepend[i].componentCaller = e.prepend[i].componentCaller || appdb.views.Main.showApplications; //if from permalink
                }
            }
        }
        e.componentType = e.componentType || "appdb.components.Applications";
        e.filterDisplay = e.filterDisplay || "Search in subdiscipline...";
        if(e.isBaseQuery){
            e.baseQuery = o;
        }
        e.componentArgs = [o];
        _showApplications(o,e);
        appdb.views.Main.selectAccordion("applicationspane","Software");
		_selectActiveLinks('/browse/none');
    };
    var _showApplicationCountry = function(o,e){
		_currentState = {callback : _showApplicationCountry,query:o,ext:e};
        e = e || {};
        e.append = false;
        e.componentCaller = appdb.views.Main.showDiscipline;
        e.mainTitle = e.mainTitle || "Country";
        if(o.flt!==""){
            e.prepend = e.prepend || [{
                componentType : "appdb.components.Applications",
                mainTitle : "Software",
                isBaseQuery : false,
				isList : true,
                filterDisplay : "Search software...",
                componentCaller: appdb.views.Main.showApplications,
                componentArgs:[{pagelength:optQueryLen,flt:""}]
            }];
            if($.isArray(e.prepend)){
                for(var i=0; i<e.prepend.length; i+=1){
                    e.prepend[i].componentCaller = e.prepend[i].componentCaller || appdb.views.Main.showApplications; //if from permalink
                }
            }
        }
        e.componentType = e.componentType || "appdb.components.Applications";
        e.filterDisplay = e.filterDisplay || "Search in country...";
        if(e.isBaseQuery){
            e.baseQuery = o;
        }
        e.componentArgs = [o];
        _showApplications(o,e);
        appdb.views.Main.selectAccordion("applicationspane","Software");
		_selectActiveLinks('/browse/none');
    };
    var _showApplicationMiddleware = function(o,e){
		_currentState = {callback : _showApplicationMiddleware,query:o,ext:e};
        e = e || {};
        e.append = false;
        e.componentCaller = appdb.views.Main.showDiscipline;
        e.mainTitle = e.mainTitle || "Middleware";
        if(o.flt!==""){
            e.prepend = e.prepend || [{
                componentType : "appdb.components.Applications",
                mainTitle : "Software",
                isBaseQuery : false,
                filterDisplay : "Search software...",
				isList : true,
                componentCaller: appdb.views.Main.showApplications,
                componentArgs:[{pagelength:optQueryLen,flt:""}]
            }];
            if($.isArray(e.prepend)){
                for(var i=0; i<e.prepend.length; i+=1){
                    e.prepend[i].componentCaller = e.prepend[i].componentCaller || appdb.views.Main.showApplications; //if from permalink
                }
            }
        }
        e.componentType = e.componentType || "appdb.components.Applications";
        e.filterDisplay = e.filterDisplay || "Search in middleware...";
        if(e.isBaseQuery){
            e.baseQuery = o;
        }
        e.componentArgs = [o];
        _showApplications(o,e);
        appdb.views.Main.selectAccordion("applicationspane","Software");
		_selectActiveLinks('/browse/none');
    };
	var _showApplicationVO = function(o,e){
		e.filterDisplay = e.filterDisplay || "Search with vo...";
		_showApplications(o,e);
	};
	var _showAppSubItems = function(subtype, o, e){
		var sname = $.trim(subtype);
		if( sname === "" ) return;
		sname = "show" + sname[0].toUpperCase() + sname.slice(1);
		var callerfunc = appdb.views.Main[sname];
		if( typeof callerfunc !== "function" )return;
		o = o || {};
		e = e || {};
		o.id = o.id || userID;
		e.subType = subtype;
		e.isBaseQuery = (typeof e.isBaseQuery === "boolean")?e.isBaseQuery:true;
		e.componentCaller = e.componentCaller || callerfunc;
		var conf = appdb.utils.entity.getConfigList(o.entitytype || e.content, o,e);
		e.mainTitle = $.trim(e.mainTitle);
		if( $.trim(e.mainTitle) === "" && e.subType.length > 1){
			e.mainTitle = e.subType[0].toUpperCase() + e.subType.slice(1) + " "  +conf.name();
		}
		e.filterDisplay = conf.filterDisplay();
		e.href = conf.href();
		_currentState = {callback : callerfunc,query:o,ext:e};
		_showApplications(o,e);
		_selectActiveLinks(e.href);
	};
    var _showOwned = function(o,e){
		_showAppSubItems("owned", o, e);
    };
    var _showAssociated = function(o,e){
        _showAppSubItems("associated", o, e);
    };
    var _showRelatedApps = function(o,e){
		_currentState = {callback : _showRelatedApps,query:o,ext:e};
        e = e || {};
        e.subType = "related";
        e.prepend = e.prepend || [{
                componentType : "appdb.components.Applications",
                mainTitle : "Software",
                isBaseQuery : false,
                filterDisplay : "Search software...",
                componentCaller: appdb.views.Main.showApplications,
                componentArgs:[{pagelength:optQueryLen,flt:""}]
            }];
        e.componentCaller = e.componentCaller || appdb.views.Main.showRelatedApps;
        e.mainTitle = "Suggested: " + e.appname ;
		var conf = appdb.utils.entity.getConfigList(o.entitytype || o.content, o,e);
		e.filterDisplay = conf.filterDisplay();
		e.href = conf.href();
        _showApplications(o,e);
		appdb.views.Main.selectAccordion("applicationspane","Software");
		_selectActiveLinks(conf.href());
    };
    var _showModerated = function(o,e){
		_showAppSubItems("moderated", o, e);
    };
    var _showDeleted = function(o,e){
		_showAppSubItems("deleted", o, e);
    };
    var _showBookmarked = function(o,e){
		_showAppSubItems("bookmarked", o, e);
    };
	var _showFollowed = function(o,e){
		_showAppSubItems("followed", o, e);
	};
    var _showEditable = function(o,e){
		
        _showAppSubItems("editable", o, e);
    };
	var _showAppDetails = function(o,e,data){
		_currentState = {callback : _showAppDetails,query:o,ext:e};
		if ( o.id == 0 ) {
			showAppDetails("apps/details?id="+o.id, true, undefined, o.entitytype);
		} else {
			var tab = (e && $.trim(e.tab))?"&tab=" + encodeURI(e.tab.toLowerCase()):"";
			var dispatcher;
					
			if( data ) { 
				dispatcher = { 
					func: appdb.pages.application.init, 
					args: [{
						id: data.id || null,
						dialogCount: dialogCount,
						permalink: appdb.config.endpoint.base + 'store/' + appdb.pages.index.currentContent() + "/" + data.cname,
						historyId: $.trim(o.histid),
						historyType: $.trim(o.histtype),
						entityType: appdb.pages.index.currentContent(),
						data: data
					}]
				};
			}
			showDetails("apps/details?id="+o.id+tab,'',o.histid,o.histtype,o.entitytype, dispatcher);
		}		
		var conf = appdb.utils.entity.getConfig(o.entitytype || o.content, o,e);
		appdb.pages.index.setCurrentContent(conf.content(),false);
		if( o.id == 0 ){
			_selectActiveLinks(conf.href());
		}
    };
    var _showHome = function(callglobal){
		_navpane.hide();
		showHome();
		appdb.views.Main.selectAccordion("applicationpane","Software");
    };
    var _showNgis = function(sq,e){
		_currentState = {callback : _showNgis,query:sq,ext:e};
        var q = (typeof sq === "number")?"?eu="+sq:"";
        e = e || {};
        e.mainTitle = e.mainTitle || sq;
        if ( e.flt ) {
			if ( q !== '' ) q = q+"&"; else q = "?";
			q = q+'filter='+encodeURIComponent(e.flt);
		}
        if(typeof sq === "number"){
            e.prepend = {
                componentType : "appdb.components.ResourceProviders",
                mainTitle : "Resource Providers",
                componentCaller : appdb.views.Main.showNgis,
				isList : true,
                componentArgs : []
            };
        }
        e.componentType = "appdb.components.ResourceProviders";
        e.mainTitle =  (typeof sq !== "number")?"Resource Providers":((sq===1)?"NGIs":"EIROs");
        e.componentCaller = appdb.views.Main.showNgis;
        e.componentArgs = [sq];
		e.isList = true;
        _clearComponents();
        _createNavigationList(e);
        ajaxLoad("/ngi"+q,"main", false);
    };
    var _showNgi = function(o,e){
		_currentState = {callback : _showNgi,query:o,ext:e};
        e = e || {};
        e.prepend = {
            componentType : "appdb.components.ResourceProviders",
                mainTitle : "Resource Providers",
				isList : true,
                componentCaller : appdb.views.Main.showNgis,
                componentArgs : []
        };
        e.componentCaller = _showNgi;
        e.componentArgs =[{id:o.id}];
        _createNavigationList(e);
        showNGIDetails("/ngi/details?id="+o.id);
    };
    var _showVOs = function(o,e){
		_currentState = {callback : _showVOs,query:o,ext:e};
        e = e || {};
        o = o || {};
		if( $.trim(o.orderby) === "" ){
			o.orderby = "rank";
			o.orderbyOp = "DESC";
		}
        e.mainTitle = e.mainTitle || 'Virtual Organizations';
		e.content = ($.trim(e.content))?$.trim(e.content):($.trim(appdb.pages.application.currentContent()).toLowerCase() || "home");
		o.vomembership = e.subType || "";
        o.pagelength = optQueryLen;
         if($.trim(o.flt) || o.name || typeof o.domain !== "undefined"){
            e.prepend = e.prepend ||  {
                componentType : "appdb.components.VOs",
                mainTitle : "Virtual Organizations",
                isBaseQuery : false,
				userQuery : '',
				isList : true,
                filterDisplay : "Search VOs...",
                componentCaller: appdb.views.Main.showVOs,
                componentArgs:[{pagelength:optQueryLen,flt:""}]
            };
            if($.isArray(e.prepend)){
                for(var i=0; i<e.prepend.length; i+=1){
                    e.prepend[i].componentCaller =  e.prepend[i].componentCaller || appdb.views.Main.showVOs; //if from permalink
                }
            }
        }
        if(typeof o.name === "undefined"){
            o.name="";
        }
		e.isList = true;
        e.componentType = "appdb.components.VOs";
        e.componentCaller = appdb.views.Main.showVOs,
        e.componentArgs = [o];
        e.filterDisplay = e.filterDisplay || "Search VOs...";
		var f = "";
        if(e.isBaseQuery){
			f = ((e.baseQuery)?$.trim(e.baseQuery.flt):"");
            e.baseQuery = o;
			if( f ){
				e.baseQuery.flt = f;
			}
        }else if(!e.userQuery && (!e.systemQuery || !e.systemQuery.length > 0)){
			f = ((e.userQuery)?$.trim(e.userQuery.flt):"");
            e.userQuery = o;
			if( f ){
				e.userQuery.flt = f;
			}
		}
        
        _createNavigationList(e);
		_showComponent(o,e);
		_selectActiveLinks(e.href || '/browse/vos');
        if( e.content === "vappliance" ){
			appdb.pages.index.setCurrentContent("vappliance",false);
		}else if(e.content === "software"){
			appdb.pages.index.setCurrentContent("software",false);
		}else{
			appdb.pages.index.setCurrentContent("home",false);
		}
    };
	var _showSwVOs = function(){
		_showVOs({flt:''},{filterDisplay:'Search VOs...',mainTitle : 'Supported VOs',systemQuery: ['+=&vo.storetype:1'], content: 'software',prepend:{
			componentType : "appdb.components.Applications",
			mainTitle : "Software Marketplace",
			isBaseQuery : false,
			filterDisplay : "Search software...",
			isList : true,
			content: "software",
			componentCaller: appdb.views.Main.showSoftwareMarketplace,
			componentArgs:[{pagelength:optQueryLen,flt:""}],
			href: "/browse/software"
		}, callerName: "SwVOs", href: '/browse/vos/software'});
	};
	var _showCloudVOs = function(){
		_showVOs({flt:''},{filterDisplay:'Search VOs...',mainTitle : 'Supported VOs', systemQuery: ['+=&vo.storetype:2'], content: 'vappliance',prepend: {
			componentType : "appdb.components.Applications",
			mainTitle : "Cloud Marketplace",
			isBaseQuery : false,
			filterDisplay : "Search virtual appliances...",
			isList : true,
			content: "vappliance",
			componentCaller: appdb.views.Main.showCloudMarketplace,
			componentArgs:[{pagelength:optQueryLen,flt:""}],
			href: "/browse/cloud"
		}, callerName: "CloudVOs", href: '/browse/vos/cloud'});
	};
	var _showVOMembershipItems = function(subtype, o, e){
		o = o || {};
		e = e || {};
        var sname = $.trim(subtype);
		if( sname === "" ) return;
		sname = "showVo" + sname[0].toUpperCase() + sname.slice(1);
		var callerfunc = appdb.views.Main[sname];
		if( typeof callerfunc !== "function" )return;
		_currentState = {callback : callerfunc,query:o,ext:e};
		
		o.id = $.trim(o.id) || userID;
		o.userid = $.trim(o.id);
		e.isBaseQuery = (typeof e.isBaseQuery === "boolean")?e.isBaseQuery:true;
        e.subType = $.trim(subtype) || $.trim(e.subType);
		e.componentCaller = e.componentCaller || callerfunc;
		e.mainTitle = $.trim(e.mainTitle) || (e.subType[0].toUpperCase() + e.subType.slice(1) + " in VOs");
        e.href = e.href || '/browse/vos/membership/' + e.subType;
        _showVOs(o,e);
		_selectActiveLinks(e.href);
	};
	var _showVoManager = function(o,e){
		_showVOMembershipItems("manager", o, e);
	};
	var _showVoDeputy = function(o,e){
		_showVOMembershipItems("deputy", o, e);
	};
	var _showVoExpert = function(o,e){
		_showVOMembershipItems("expert", o, e);
	};
	var _showVoShifter = function(o,e){
		_showVOMembershipItems("shifter", o, e);
	};
	var _showVoMember = function(o,e){
		_showVOMembershipItems("member", o, e);
	};
    var _showVO = function(o,e){
		_currentState = {callback : _showVO,query:o,ext:e};
        showVODetails("vo/details?id="+o,"main");
		var conf = appdb.utils.entity.getConfig((e)?e.content:e,o,e);
		appdb.pages.index.setCurrentContent(conf.content(),false);
		_selectActiveLinks(conf.href());
    };
    var _clearComponents = function(){
        if(_currentComponent){
            _currentComponent.destroy();
        }
        _currentComponent = null;
        _container = null;
        _currentContent = null;
       
        $("#main").empty();
    };
    var _setLastNavigationTitle = function(title){
        _navpane.setLastItemTitle(title);
    };
	var _refresh = function(){
		_onRefresh = true;
		_currentState.callback(_currentState.query,_currentState.ext);
	};
    var _selectAccordion = function(id,title,attempts){return;
    };
	var _selectActiveLinks = function(selector, attempts){
		selector = $.trim(selector).toLowerCase();
		if( selector === "") return;
		attempts = attempts || 20;
		setTimeout(function(){
			if( $("#navigationmenu .menu.software > ul.list li > .row").length === 0 && attempts > 0){
				attempts -= 1;
				_selectActiveLinks(selector, attempts);
				return;
			}
			$("body .activelistitem").removeClass("activelistitem");
			$("a[href='" + selector + "']").addClass("activelistitem");
			$("body > .mainheader a[href='" + selector + "']").closest("ul").siblings("a").addClass("activelistitem").closest("ul").siblings("a").addClass("activelistitem");
			$("a[data-href='" + selector + "']").addClass("activelistitem");
			$("body > .mainheader a[data-href='" + selector + "']").closest("ul").siblings("a").addClass("activelistitem").closest("ul").siblings("a").addClass("activelistitem");
			
			
			setTimeout(function(){
				var elem = $("#navigationmenu .activelistitem");
				if( $("#navigationmenu").is(":visible") ){
					if( $(elem).hasClass("cell") ){
						//Check if subcategory to expand
						if( $(elem).parent().hasClass("level2") ||  $(elem).parent().hasClass("level3")){
							$(elem).closest(".row.level1").children(".expand").children(".expandhandler:not(.expanded), .expandhandler.collapsed").trigger("click");
						}else if( $(elem).parent().hasClass("level1") === false ){
							$("#navigationmenu .menu.software > ul.list > li .row.level1").children(".expand").children(".expandhandler.expanded").trigger("click");
						}else if($(elem).parent().hasClass("level1") && $(elem).parent().children(".expand").length > 0 ){
							//do nothing
						}else{
							$("#navigationmenu .menu.software > ul.list > li .row.level1").children(".expand").children(".expandhandler").removeClass("expanded").addClass("collapsed");
							$("#navigationmenu .menu.software > ul.list > li .row.level1").children(".children").hide();
						}
					}else{
						$("#navigationmenu .menu.software > ul.list > li .row.level1").children(".expand").children(".expandhandler").removeClass("expanded").addClass("collapsed");
							$("#navigationmenu .menu.software > ul.list > li .row.level1").children(".children").hide();
					}
				}
				appdb.pages.index.updateCategoriesLayout();
			},1);
		},100);
	};
	var _showReportAbuse = (function(){
		var ra = null;
		return function(userdata){
			if(ra===null){
				ra = new appdb.components.ReportAbuse();
			}
			ra.render(userdata);
	};})();
	var _showRequestJoinContacts = (function(){
		var rjc = null;
		return function(appid){
			rjc = new appdb.components.RequestJoinContacts();
			rjc.load(appid);
		};
	})();
	var _showSendMessageToContacts = (function(){
		var smc = null;
		return function(appid) {
			if( smc === null ) {
				smc = new appdb.components.SendMessageToContacts();
			}
			smc.load(appid);
		};
	})();
	var _showLinkStatuses = function(o,e){
		_currentState = {callback : _showLinkStatuses,query:o,ext:e};
        e = e || {};
        o = o || {};
        e.mainTitle = e.mainTitle || 'Broken links report';
        if(typeof o.name === "undefined"){
            o.name="";
        }
		e.isList = true;
        e.componentType = "appdb.components.LinkStatuses";
        e.componentCaller = appdb.views.Main.showLinkStatuses;
        e.componentArgs = [o];
		
        _clearComponents();
        _createNavigationList(e);
		_showComponent(o,e);
        appdb.views.Main.selectAccordion("adminpane",e.mainTitle);
		if( e && $.trim(e.content) !== "" ||  $.trim(e.content) !== "current"){
			appdb.pages.index.setCurrentContent($.trim(e.content),false);
		}
		_selectActiveLinks('/pages/admin/brokenlinks');
	};
	var _showDisseminationTool = function(o,e){
		_currentState = {callback : _showLinkStatuses,query:o,ext:e};
        e = e || {};
        o = o || {};
        e.mainTitle = e.mainTitle || 'Dissemination Tool';
        if(typeof o.name === "undefined"){
            o.name="";
        }
		e.isList = true;
        e.componentType = "appdb.components.DisseminationTool";
        e.componentCaller = appdb.views.Main.showDisseminationTool;
        e.componentArgs =   [o];

        _clearComponents();
        _createNavigationList(e);
		_showComponent(o,e);
		appdb.views.Main.selectAccordion("adminpane",e.mainTitle);
		if( e && $.trim(e.content) !== "" ||  $.trim(e.content) !== "current"){
			appdb.pages.index.setCurrentContent($.trim(e.content),false);
		}
		_selectActiveLinks('/pages/admin/disseminationtool');
	};
	var _showActivityReport = function(o,e){
		_currentState = {callback : _showActivityReport,query:o,ext:e};
        e = e || {};
        e.prepend = [];
		e.mainTitle = e.mainTitle || "Activity Report";
        e.componentType = "activityreport";
		e.componentCaller = _showActivityReport;
        e.componentArgs =[{}];
		appdb.views.Main.clearComponents();
        _createNavigationList(e);
        _ajaxLoad('/news/report','main');
		appdb.views.Main.selectAccordion("adminpane", e.mainTitle);
		if( e && $.trim(e.content) !== "" ||  $.trim(e.content) !== "current"){
			appdb.pages.index.setCurrentContent($.trim(e.content),false);
		}
		_selectActiveLinks('/pages/admin/activityreport');
	};
	var _showPage = function(type,e){
		var title = "";
		type = type || "about";
		if( typeof e === "undefined") {
			return;
		}
		if(typeof e === "string" && $.trim(e) !== ""){
			e = {mainTitle: "", url: e};
		} else if ( !e.url ){
			return;
		} 
		
		if( !e.mainTitle ) {
			title = e.url;
			if(title.indexOf("/")>-1){
				title = title.split("/");
				title = title[title.length-1];
			}
			e.mainTitle = title || type;
		}
		if( e && $.trim(e.content) !== "" && $.trim(e.content).toLowerCase() !=="current"){
			appdb.pages.index.setCurrentContent($.trim(e.content).toLowerCase(), false);
		}
		_currentState = {callback : _showPage,query:{},ext:e};
        e = e || {};
        e.prepend = [];
        e.componentCaller = _showPage;
        e.componentArgs =[{},e];
		e.type = type;
		_clearComponents();
        _createNavigationList(e);
		if(e.callback){
			_ajaxLoad(e.url,'main', e.callback);
		}else{
			_ajaxLoad(e.url,'main');
		}
		var selectedUrl = e.url;
		selectedUrl = selectedUrl.replace(/\/[0-9]+$/g,"");
		
        $("#panediv a[onclick]").each(function(index,elem){
			var onclk = ""+$(elem).attr("onclick");
			if( onclk.indexOf("'" + selectedUrl + "'")>0 || onclk.indexOf('"' + selectedUrl + '"')>0){
				$("#panediv a").removeClass("activelistitem");
				$(elem).addClass("activelistitem");
			}
		});
		
		switch(type.toLowerCase()){
			case "about":
				appdb.views.Main.selectAccordion("helppane",e.mainTitle); 
				break;
			case "statistics":
				appdb.views.Main.selectAccordion("statisticspane");
				break;
			default:
				break;
		}
		switch($.trim(e.url).toLowerCase()){
			case "pplstats/perposition":
				e.href = '/pages/statistics/people/position';
				break;
			case "pplstats/percountry":
				e.href = '/pages/statistics/people/country';
				break;
			case "appstats/percategory?content=vappliance":
				e.href = '/pages/statistics/vappliance/category';
				break;
			case "appstats/perdiscipline?content=vappliance":
				e.href = '/pages/statistics/vappliance/discipline';
				break;
			case "appstats/percategory":
				e.href = '/pages/statistics/software/category';
				break;
			case "appstats/perdiscipline":
				e.href = '/pages/statistics/software/discipline';
				break;
			case "appstats/pervo":
				e.href = '/pages/statistics/software/vo';
				break;
			case "/index/feedback":
				e.href = '/pages/contact/feedback';
				break;
		}
		_selectActiveLinks(e.href);
	};
	var _showSoftwareMarketplace = function(){
		appdb.pages.index.setCurrentContent("software",false);
		_showApplications({flt:'+*&application.metatype:0',orderby:'dateadded',orderbyOp:'DESC'},{isBaseQuery:true, mainTitle: 'Software Marketplace',filterDisplay: 'Search software...', content: 'software'});
		_selectActiveLinks('/browse/software/newest');
		return false;
	};
	var _showCloudMarketplace = function(){
		appdb.pages.index.setCurrentContent("vappliance",false);
		_showApplications({flt:'+*&application.metatype:1',orderby:'dateadded',orderbyOp:'DESC'},{isBaseQuery:true, mainTitle: 'Cloud Marketplace',filterDisplay: 'Search virtual appliances...', content: 'vappliance'});
		_selectActiveLinks('/browse/cloud/vappliances/newest');
		return false;
	};
	var _showPeopleMarketplace = function(){
		appdb.pages.index.setCurrentContent("researchers",false);
		_showPeople({flt : '',orderby:'dateinclusion',orderbyOp:'DESC'},{filterDisplay: 'Search people...',mainTitle:'People Registry', content: 'people'});
		_selectActiveLinks('/browse/people/newest');
		return false;
	};
	var _showSites = function(o,e){
		_currentState = {callback : _showVOs,query:o,ext:e};
        e = e || {};
        o = o || {};
		if( $.trim(o.orderby) === "" ){
			o.orderby = "name";
			o.orderbyOp = "ASC";
		}
        e.mainTitle = e.mainTitle || 'Sites';
		e.content = ($.trim(e.content))?$.trim(e.content):($.trim(appdb.pages.index.currentContent()).toLowerCase() || "home");
		o.pagelength = optQueryLen;
         if($.trim(o.flt) || o.name ){
            e.prepend = e.prepend ||  {
                componentType : "appdb.components.Sites",
                mainTitle : "Sites",
                isBaseQuery : false,
				userQuery : '',
				isList : true,
                filterDisplay : "Search Sites/Resource Providers...",
                componentCaller: (e.content === "vappliance")?appdb.views.Main.showCloudSites:appdb.view.Main.showSites,
                componentArgs:[{pagelength:optQueryLen,flt:""}]
            };
            if($.isArray(e.prepend)){
                for(var i=0; i<e.prepend.length; i+=1){
                    e.prepend[i].componentCaller =  e.prepend[i].componentCaller || appdb.views.Main.showSites; //if from permalink
                }
            }
        }
        e.isList = true;
        e.componentType = "appdb.components.Sites";
        e.componentCaller = appdb.views.Main.showSites,
        e.componentArgs = [o];
		e.filterDisplay = e.filterDisplay || "Search Sites/Resource Providers...";
		var f = "";
        if(e.isBaseQuery){
			f = ((e.baseQuery)?$.trim(e.baseQuery.flt):"");
            e.baseQuery = o;
			if( f ){
				e.baseQuery.flt = f;
			}
        }else if(!e.userQuery && (!e.systemQuery || !e.systemQuery.length > 0)){
			f = ((e.userQuery)?$.trim(e.userQuery.flt):"");
            e.userQuery = o;
			if( f ){
				e.userQuery.flt = f;
			}
		}
        
        _createNavigationList(e);
		_showComponent(o,e);
		_selectActiveLinks(e.href || '/browse/sites');
        if( e.content === "vappliance" ){
			appdb.pages.index.setCurrentContent("vappliance",false);
		}else if(e.content === "software"){
			appdb.pages.index.setCurrentContent("software",false);
		}else{
			appdb.pages.index.setCurrentContent("home",false);
		}
	};
	var _showCloudSites = function(){
		var siteprepend = {
			componentType : "appdb.components.Applications",
			mainTitle : "Cloud Marketplace",
			isBaseQuery : false,
			filterDisplay : "Search sites/Resource Providers...",
			isList : true,
			content: "vappliance",
			componentCaller: appdb.views.Main.showCloudMarketplace,
			componentArgs:[{pagelength:optQueryLen,flt:""}],
			href: "/browse/cloud"
		};
		_showSites({flt:''},{filterDisplay:'Search Sites/Resource Providers...',mainTitle : 'Supported OCCI Sites', systemQuery: ['+=&site.supports:1'], content: 'vappliance',prepend: siteprepend, callerName: "CloudSites", href: '/browse/sites/cloud'});
	};
	var _showSite = function(o,e){
		_currentState = {callback : _showSite,query:o,ext:e};
        e = e || {};
        o = o || {};
		e.componentCaller = e.componentCaller || appdb.views.Main.showSite;
		e.componentType = e.componentType || "appdb.components.Site";
		e.componentArgs = [o];
		e.content = "vappliance";
        _showComponent(o,e);
        appdb.pages.index.setCurrentContent("vappliance",false);
		return false;
	};
	var _showDatasets = function(o,e){
		_currentState = {callback : _showDatasets,query:o,ext:e};
        e = e || {};
        o = o || {};
		if( $.trim(o.orderby) === "" ){
			o.orderby = "name";
			o.orderbyOp = "ASC";
		}
        e.mainTitle = e.mainTitle || 'Datasets';
		e.content = ($.trim(e.content))?$.trim(e.content):($.trim(appdb.pages.index.currentContent()).toLowerCase() || "home");
		o.pagelength = optQueryLen;
         if($.trim(o.flt) || o.name ){
            e.prepend = e.prepend || { componentType : "appdb.components.Datasets",
				mainTitle : "Datasets",
				isBaseQuery : false,
				filterDisplay : "Search datasets...",
				isList : true,
				content: "dataset",
				componentCaller: appdb.views.Main.showDatasets,
				componentArgs:[{pagelength:optQueryLen,flt:""}],
				href: "/browse/datasets"
			};
            if($.isArray(e.prepend)){
                for(var i=0; i<e.prepend.length; i+=1){
                    e.prepend[i].componentCaller =  e.prepend[i].componentCaller || appdb.views.Main.showDatasets; //if from permalink
                }
            }
        }
        e.isList = true;
        e.componentType = "appdb.components.Datasets";
        e.componentCaller = appdb.views.Main.showDatasets,
        e.componentArgs = [o];
		e.filterDisplay = e.filterDisplay || "Search Datasets...";
		var f = "";
        if(e.isBaseQuery){
			f = ((e.baseQuery)?$.trim(e.baseQuery.flt):"");
            e.baseQuery = o;
			if( f ){
				e.baseQuery.flt = f;
			}
        }else if(!e.userQuery && (!e.systemQuery || !e.systemQuery.length > 0)){
			f = ((e.userQuery)?$.trim(e.userQuery.flt):"");
            e.userQuery = o;
			if( f ){
				e.userQuery.flt = f;
			}
		}
        
        _createNavigationList(e);
		_showComponent(o,e);
		_selectActiveLinks(e.href || '/browse/datasets');
        appdb.pages.index.setCurrentContent("datasets",false);
	};
	var _showDataset = function(o,e){
		_currentState = {callback : _showDataset,query:o,ext:e};
        e = e || {};
        o = o || {};
		e.componentCaller = e.componentCaller || appdb.views.Main.showDataset;
		e.componentType = e.componentType || "appdb.components.Dataset";
		e.componentArgs = [o];
		e.content = "dataset";
		_showComponent(o,e);
		appdb.pages.index.setCurrentContent("dataset",false);
        return false;
	};
    var _closeCurrentView = function(){
	   $("#toolbarContainer").empty();
        _navpane.closeCurrentItem();
    };
	var _ajaxLoad = function(url,destination,actions){
	 detailsListHtml = null;
	 $("#details").empty().hide();
	 $("#"+destination).hide();
	 showAjaxLoading();
	 if(url && url.length>1 && url[0]!=="/"){
		 url = "/" + url;
	 }
	 $.get(
		 url, {}, function(data, textStatus) {
			 $('#'+destination).html(data);
			 hideAjaxLoading();
			 $("#"+destination).fadeIn("slow");
			 if (actions!==undefined) eval(actions);
	}, 'html');
	 return false;
	};
	var _logout = function(){
	 window.location = userLogoutUrl || '/users/logout';
	};
	
	var _isTerminalItem = (function(){
		var _val = false;
		return function(val){
			if( typeof val === "boolean"){
				_val = val;
				if( _val ) {
					$("#maincontent table#mainbody").addClass("terminal");
				}else{
					$("#maincontent table#mainbody").removeClass("terminal");
				}
			}
			return _val;
		};
	})();
    var retobject =  {
        showHome : _showHome,
        showPeople : _showPeople,
		showPeopleByRole: _showPeopleByRole,
		showPeopleByGroup: _showPeopleByGroup,
		showOrderedPeople: _showOrderedPeople,
        showDeletedPeople : _showDeletedPeople,
        showEverything: _showEverything,
        showApplications : _showApplications,
		showOrderedSoftware: _showOrderedSoftware,
		showCategories: _showCategories,
		showVAppCategories: _showVAppCategories,
        showRelatedApps: _showRelatedApps,
        showModerated: _showModerated,
        showDeleted: _showDeleted,
        showBookmarked : _showBookmarked,
		showFollowed: _showFollowed,
        showEditable : _showEditable,
        showOwned : _showOwned,
        showAssociated : _showAssociated,
        showPerson : _showPerson,
        showApplication : _showAppDetails,
		showVirtualAppliance : _showVirtualAppliance,
		showVirtualAppliances: _showVirtualAppliances,
		showOrderedVAppliances: _showOrderedVAppliances,
		showSoftwareAppliance: _showSoftwareAppliance,
		showSoftwareAppliances: _showSoftwareAppliances,
		showCloudSoftwareAppliances: _showCloudSoftwareAppliances,
		showOrderedSWAppliances: _showOrderedSWAppliances,
        showNgis : _showNgis,
        showNgi : _showNgi,
        showVOs : _showVOs,
		showCloudVOs: _showCloudVOs,
		showSwVOs: _showSwVOs,
		showVoManager: _showVoManager,
		showVoDeputy: _showVoDeputy,
		showVoExpert: _showVoExpert,
		showVoShifter: _showVoShifter,
		showVoMember: _showVoMember,
        showVO : _showVO,
        showDiscipline : _showDiscipline,
        showSubdiscipline : _showSubdiscipline,
        showApplicationCountry : _showApplicationCountry,
        showApplicationMiddleware : _showApplicationMiddleware,
		showApplicationVO : _showApplicationVO,
		showReportAbuse : _showReportAbuse,
		showRequestJoinContacts : _showRequestJoinContacts,
		showSendMessageToContacts : _showSendMessageToContacts,
		showLinkStatuses : _showLinkStatuses,
		showDisseminationTool: _showDisseminationTool,
		showSoftwareMarketplace: _showSoftwareMarketplace,
		showCloudMarketplace: _showCloudMarketplace,
		showPeopleMarketplace: _showPeopleMarketplace,
		showActivityReport : _showActivityReport,
		showSite: _showSite,
		showSites: _showSites,
		showCloudSites: _showCloudSites,
		showDatasets: _showDatasets,
		showDataset: _showDataset,
        clearComponents : _clearComponents,
        setNavigationTitle : _setLastNavigationTitle,
		showPage: _showPage,
		clearNavigation: _clearNavigation,
		createNavigationList: _createNavigationList,
        closeCurrentView : _closeCurrentView,
		refresh : _refresh,
		ajaxLoad : _ajaxLoad,
		logout : _logout,
		selectAccordion : _selectAccordion
    };

	for(var i in retobject){
	 if(retobject.hasOwnProperty(i)){
	  retobject[i] = (function(f,fname){
	   var metadata = appdb.Navigator.Registry[(fname.substr(0,4)=="show")?fname.substr(4):fname];
	   var historyEvent = ( !appdb.config.routing.useHash && typeof history.pushState !== "undefined")?"popstate":"hashchange";
	   return function(){
		
		var argv = arguments;
		var ev = appdb.utils.CancelEventTrigger(arguments.callee);
		appdb.utils.DataWatcher.Registry.checkActiveWatcherAsync({notify:true,onClose : function(){
			if( appdb.config.expandTerminalItems === true ){
				if($.inArray(fname.toLowerCase(),["showapplication","showperson","showvo","showsendmessagetocontacts","showrequestjoincontacts","showreportabuse","showvirtualappliance", "showsoftwareappliance","showsite","showdataset"]) > -1){
					appdb.views.Main.isTerminalItem(true);
				} else if( fname.substr(0,4)==="show" ) {
					appdb.pages.Application.reset();
					appdb.pages.Person.reset();
					appdb.pages.vo.reset();
					appdb.pages.site.reset();
					appdb.views.Main.isTerminalItem(false);
				}
			}
			f.apply(null,argv);
			window.scroll(0,0);
			var evtype = (ev && ev.type)?ev.type:"";
			evtype = (evtype === historyEvent)?false:true;
			if( metadata ){
				appdb.Navigator.handlePermalink(metadata, (argv.length > 0)?argv:[_currentState.query,_currentState.ext]);
			}
			appdb.config.routepermalink = window.location.href;			
		}});
	   };})(retobject[i],i);
	 }
	}
	retobject["isTerminalItem"] = _isTerminalItem;
	return retobject;
})();

$(document).ready(function(){
	appdb.Navigation.init();
	setTimeout(function(){
		var mainsearch = $("div#mainsearch");
		if(mainsearch.length > 0){
			mainsearch = new appdb.components.MainSearch({container:$("#mainsearch")});
			mainsearch.render();
		}
		if(userID!==null){
			appdb.components.Person.getPrimaryContact();
		}	
	},1);

});
$( window ).unload(function(){
	window.unloading = true;
});
$(window).bind('beforeunload', function(){
	window.unloading = true;
});
