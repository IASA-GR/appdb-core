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
appdb = appdb || {};
appdb.views = appdb.views || {};
appdb.views.ui = appdb.views.ui || {};
appdb.views.ui.editors = appdb.views.ui.editors || {};
appdb.views.ui.hooks = appdb.views.ui.hooks || {};
appdb.views.ui.hooks.dataset = appdb.views.ui.hooks.dataset || {};
appdb.views.ui.validators.dataset = appdb.views.ui.validators.dataset || {};
appdb.components = appdb.components || {};
appdb.pages = appdb.pages || {};
appdb.datasets = {};
appdb.datasets.views = {};
appdb.datasets.components = {};
appdb.datasets.autoInit = (function(){
	function init(){
		if( appdb.model.StaticList.SiteList && appdb.model.StaticList.SiteList.length === 0 ){
			var model = new appdb.model.Sites();
			model.subscribe({event: "select", callback: function(v){
					if( v ){
						if( !v.error ){
							v.site = v.site || [];
							v.site = $.isArray(v.site)?v.site:[v.site];
							appdb.model.StaticList.SiteList = v.site;
						}
					}
				}
			});
			model.get();
		}
		if( appdb.model.StaticList.DatasetList && appdb.model.StaticList.DatasetList.length === 0 ){
			var model = new appdb.model.Datasets();
			model.subscribe({event: "select", callback: function(v){
					if( v ){
						if( !v.error ){
							v.dataset = v.dataset || [];
							v.dataset = $.isArray(v.dataset)?v.dataset:[v.dataset];
							appdb.model.StaticList.DatasetList = v.dataset;
						}
					}
				}
			});
			model.get({"listmode":"listing"});
		}
	}
	return init;
})();
appdb.views.DatasetListTypeSelector = appdb.ExtendClass(appdb.View, "appdb.views.DatasetListTypeSelector", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: { list: $("<select><option value='bydataset' selected='selected'>By dataset</option><option value='byversion'>By Version</option></select>") },
		data: $.trim(o.data) || "bydataset"
	};
	this.reset = function(){
		if( this.options.editor ){
			this.options.editor.destroyRecursive(false);
			this.options.editor = null;
		}
		$(this.dom).find("select").remove();
		if( $(this.dom).find(".value").length ){
			$(this.dom).find(".value").append(this.options.dom.list);
		}else{
			$(this.dom).append(this.options.dom.list);
		}
	};
	this.isFlatType = function(isflattype){
		if( typeof isflattype === "boolean"){
			this.options.data = (isflattype)?"byversion":"bydataset";
		}else{
			switch($.trim(this.options.data)){
				case "bydataset":
					return false;
				case "byversion":
				default:
					return true;
			}
		}
	};
	this.onValueChange = function(){
		this.publish({event: "change", value:(this.isFlatType()?true:false)});
	};
	this.render = function(){
		this.reset();
		var selected = "bydataset";
		if( this.isFlatType() ){
			selected = "byversion";
		}
		
		this.options.editor = new dijit.form.Select({
			value: selected,
			onChange: (function(self){
				return function(v){
					self.options.data = this.get("value")||"";
					self.onValueChange();
				};
			})(this)
		}, $(this.dom).find("select")[0]);
	};
	this._initContainer = function(){
		if( $(this.dom).find(".value select").length > 0 ){
			this.options.dom.list = $($(this.dom).find("select")[0]).clone(true);
		}
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
	};
});
appdb.components.Datasets = appdb.ExtendClass(appdb.Component, "appdb.components.Datasets", function(o){
	o = o || {};
	this.ext = o.ext || {};
	this._baseQuery = this.ext.baseQuery || null;
	this._componentTitle = "Datasets";
	this.getCurrentData = function(){
		if( !this._model || !this._model.getLocalData() ) return [];
		var m = this._model.getLocalData().dataset || [];
		m = m || [];
		m = $.isArray(m)?m:[m];
		return m;
	};
	this.setManagedFiletringDisplay = function(display){
		display = ( ($.type(display) === "boolean")?display:false );
		if( display ){
			$(".refinedfiltering").removeClass("hidden");
			$(this.views.managedFilter.dom).children(".action.display").show();
			$("#datasets_logisticsselector").removeClass("hidden");
		}else {
			var flts = this.aggregateFilter.getFilters({source: "system"});
			$(this.views.managedFilter.dom).children(".action.display").hide();
			$("#datasets_logisticsselector").addClass("hidden");
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
        v.datasetsList.render(d);
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
		if( $("#datasets_orderby > div").length > 0 ) {$("#datasets_orderby > div")[0].style.overflow = "hidden";}
		$("#datasets_logisticsselector").hide();
		window.scroll(0,0);
		$("#mainbody").removeClass("terminal");	
		v.listtypeselector.render();
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
			container: $("#datasets_logisticsselector"),
			parent: this,
			entityType: "datasets",
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
		appdb.debug("[DEBUG] Datasets Fetch: " + query.flt);
		if( typeof query.name !== "undefined" || $.trim(query.name) === ""){
			delete query.name;
		}
		if( query && typeof query.flat !== "undfined" && $.trim(query.flat) === "true" ){
			v.listtypeselector.isFlatType(true);
		}else{
			v.listtypeselector.isFlatType(false);
		}
		this._model = new appdb.model.Datasets(query);
		
        this._model.subscribe({event:"beforeselect",callback:function(){
                this.views.loading.show();
				this.views.managedFilter.setLoading(true);
            },caller:this});
        this._model.subscribe({event:"error",callback:function(err){
                this.views.loading.hide();
                this.ErrorHandler.handle({status:"Data transfer error",description:"An error occured during dataset list data transfer",source : err});
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
			 appdb.Navigator.internalCall(appdb.Navigator.Registry[this.ext.callerName || "Datasets"],{query:q,ext:this.ext});
			}else{
			 appdb.Navigator.setTitle(appdb.Navigator.Registry[this.ext.callerName || "Datasets"],[q,this.ext]);
			}
		   this.isLoaded = false;
           this.publish({event:'loaded',value:d});
		   var uq = this.aggregateFilter.getUserFilter();
			if( uq ){
				this.views.filtering.setValue({flt: uq.value});
			}
			appdb.debug("[DEBUG] Datasets Paging Load: " + q.flt);
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
			appdb.debug("[DEBUG] Datasets Filter: " + v.flt);
			this._model.get(v);
        },caller: this});
        v.datasetsList.subscribe({event:"itemclick",callback:function(data){
			var p ={},s = this.views.pager.getCurrentPagingState();
			var curl = ("/store/dataset/" + data.guid).toLowerCase();
            if(s.pagenumber>0){
                p = {previousPager:{pagelength:s.length,pageoffset: s.offset}};
            }
			if(data && data.versionid){
				p.versionid = $.trim(data.versionid);
				curl += ("/version/" + p.versionid).toLowerCase();
				delete data.versionid;
			}
			appdb.Navigator.navigate(curl,$.extend(true,data,{title: "Dataset " + data.name}));
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
			if( v === "display" ){
				if( $("#datasets_logisticsselector").is(":visible") === false ){
					$(this.views.managedFilter.dom).children("a.action.display:first").addClass("expanded");
					$("#datasets_logisticsselector").slideDown("fast");
					$("#datasets_logisticsselector").removeClass("hidden");
				} else {
					$(this.views.managedFilter.dom).children("a.action.display:first").removeClass("expanded");
					$("#datasets_logisticsselector").slideUp("fast");
				}
			}else if ( v === "reset" ){
				this.aggregateFilter.removeFilters({source: "system"});
				this.aggregateFilter.removeFilters({source: "user"});
				delete this.ext.systemQuery;
				this._model.get({flt: this.aggregateFilter.getFullQuery(), pageoffset: 0});
			}
			appdb.debug("[DEBUG] Datasets Filtering Changed: " + v.flt);
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
		v.listtypeselector.subscribe({event: "change", callback: function(v){
				var d = this._model.getQuery();
				if( v === true ){
					d.flat = "true";
				}else if( typeof d.flat !== "undefined"){
					delete d.flat;
				}
				this._model.get(d);
		},caller: this});
	};
	this._init = function(){
		var v = {};
        v.datasetsList = new appdb.views.DatasetsList({container : "ul#datasetsmainlist"});
        v.pagerview = new appdb.views.MultiPagerPane({container:$("#datasets_main_content").find(".pager")});
        v.filtering = new appdb.views.Filter({
            container : "datasets_filter",
            rich : true,
            watermark : this.ext.filterDisplay,
			displayClear : true,
			type: "datasets"
        });
        v.ordering = new appdb.views.Ordering({
            container : "datasets_orderby",
            selected : "rank",
            items : [
                {name : "Name" , value : "name", defaultOperation: "ASC"},
                {name : "ID" , value : "id", defaultOperation: "ASC"},
				{name : "Unsorted" , value : "unsorted", defaultOperation: "ASC", hideOperation: true}
            ]
        });
        v.viewbuttons = new appdb.views.ListViewMode({container : $("#datasets_viewbuttons"),list : "ul#datasetsmainlist", selection: 2});
        v.resulttimer = new appdb.views.ResultTimer({container : $("#datasets_result_timer")});
        v.loading = new appdb.views.DelayedDisplay($("#datasets_loading"));
		v.permalink = new appdb.views.Permalink({container:$("#datasets_permalink"),datatype:"vos"});
		v.listtypeselector = new appdb.views.DatasetListTypeSelector({
			container: $(".datasetlisttypeselector"),
			parent: this
		});
		this.aggregateFilter = new appdb.utils.FilterAggregator({
			parent: this,
			entityType: "datasets"
		});
		v.managedFilter = new appdb.views.ManagedFiltering({
			container: $("#datasets_managedfilters"),
			parent: this,
			filter: this.aggregateFilter,
			maxLength: 30
		});
        this.views = v;
        this.setViewsParent(this);
	};
	this._init();
});
appdb.components.Dataset = appdb.ExtendClass(appdb.Component, "appdb.components.Dataset", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: {},
		editmode: false,
		permissions: null
	};
	this.reset = function(){
		
	};
	this.isEditMode = function(){
		return (typeof this.options.editmode === "boolean")?this.options.editmode:false;
	};
	this.setEditMode = function(mode){
		mode = (typeof mode === "boolean")?mode:false;
		this.options.editmode = mode;
		if( this.options.editmode ){
			$(this.dom).find(".entitycontent").removeClass("viewmode").addClass("editmode");
		}else{
			$(this.dom).find(".entitycontent").removeClass("editmode").addClass("viewmode");
		}
		return this;
	};
	this.canEdit = function(){
		return appdb.pages.index.canManageDatasets();
	};
	this.getEditorsByPropertyValue = function(prop,val){
		prop = $.trim(prop);
		if( !prop ) return [];
		
		var editors = (this.options.editors || []).concat([this.views.licenselist,this.views.disciplines]);
		return $.grep(editors, function(e){
			if( e && e.getProp ){
				var p = e.getProp(prop);
				if( p && typeof val !== "undefined"){
					return p === val; 
				}
				return true;
			}
			return false;
		});
	};
	this.isValid = function(){
		var editors = (this.options.editors || []).concat([this.views.licenselist,this.views.disciplines]);
		
		var invalids = $.grep(editors, (function(self){ 
			return function(e){
				if(e && e.getProp("bind") === "discipline" && self.options.data ){
					if( self.options.data.parent ){
						return false;
					}
					var p = self.getEditorsByPropertyValue('bind','parent');
					p = (p.length > 0)?p[0]:null;
					if( p ){
						p = p.getEditedData() || {};
						if( p.parent ){
							return false;
						}
					}
				}
				return (e && typeof e.isValid === "function" && e.isValid() !== true);
			};
		})(this));
		
		return ( invalids.length === 0 )?true:invalids;
	};
	this.onValidation = function(v){
		var dom = $(this.dom).find(".contentcontainer");
		if( v === true && this.isValid() === true ){
			$(this.dom).find(".contentcontainer").removeClass("invalid").find(".command-toolbar [data-action='save']").removeClass("disabled");
		}else {
			$(this.dom).find(".contentcontainer").addClass("invalid").find(".command-toolbar [data-action='save']").addClass("disabled");
		}
		
		$(dom).removeClass("hassetparent");
		if( this.options.data && !this.options.data.parent  ){
			var e = this.getEditorsByPropertyValue('bind','parent');
			e = (e.length > 0)?e[0]:null;
			if( e ){
				e = e.getEditedData();
				if( e && e.parent ){
					$(dom).addClass("hassetparent");
				}
			}
		}
	};
	this.moderate = function(){
		
	};
	this.bookmark = function(){
		
	};
	this.history = function(){
		
	};
	this.remove = function(){
		appdb.utils.ShowVerifyDialog({
			title: "Remove dataset",
			message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to permanently delete this dataset and its associated data. <br/>Are you sure you want to procced?</span>",
			onOk: (function(self){
				return function(){
					self._model.remove({id: self.options.data.id});
				};
			})(this)
		});	
	};
	this.getNormalizedData = function(data){
		//just apply some rules.E.g. if this is a newly 
		//derived datasets, do not send disciplines etc.
		if( data && data.parent && data.discipline ){
			delete data.discipline;
		}
		return data;
	};
	this.save = function(){
		if( this.isValid() === false ) return;
		var data = this.getNormalizedData(this.getEditedData());
		var mapper = new appdb.utils.EntityEditMapper.Dataset();
		mapper.UpdateEntity(data);
		//Create an XML representation of the application entity by using
		//the EntitySerializer object and passing to it the application entity
		var xml = appdb.utils.EntitySerializer.toXml(mapper.entity);
		appdb.debug(xml);
		
		if( $.trim(data.id)<<0 > 0 ){
			this._model.update({query: {id: data.id}, data: {data: encodeURIComponent(xml)}});	
		}else{
			this._model.insert({query: {}, data: {data: xml}});	
		}
	};	
	this.postSave = function(d){
		if( $.trim(this.options.data.id)<<0 > 0 ){
			appdb.pages.dataset.reload();
		}else {
			this.reset();
			d = d || {};
			d.dataset = d.dataset || d;
			appdb.pages.dataset.reset();
			appdb.Navigator.navigate("/store/dataset/" + d.dataset.guid,$.extend(true,d.dataset,{title: "Dataset " + d.name}));
		}
	};
	this.postRemove = function(){
		setTimeout(function(){
			appdb.pages.dataset.reset();
			appdb.Navigator.navigate("/browse/datasets",{title: "Datasets"});
		},1);
		
	};
	this.cancel = function(){
		this.setEditMode(false);
		if( this.options.data && this.options.data.id << 0 === 0){ //is new dataset
			appdb.Navigator.navigate("/browse/datasets",{title: "Datasets"});
		}else{
			setTimeout((function(self){
				return function(){
					self.render();
				};
			})(this),1);
		}
	};
	this.getEditables = function(el){
		if( !this.options.editors || !this.options.editors.length){
			var elems = $(el || this.options.editabledom).find(".dataset-data-group .appdb-ui[data-editor]");
			$.each(elems, (function(self){
				return function(i,e){
					var editor = appdb.views.ui.getEditor($(e), { parent: self });
					if( editor ){
						self.options.editors.push(editor);
					}
				};
			})(this));
		}
		return this.options.editors.concat(this.getEditableViews()).concat(this.getEditableItems());
	};
	this.getEditableViews = function(){
		var res = [this.views.licenselist];
		
		if( this.isEditorExcluded('discipline') === false ){
			res.push(this.views.disciplines);
		}
	
		return res;
	};
	this.getEditableItems = function(){
		return[];
	};
	this.getEditedData = function(){
		var data = $.extend(true,{},this.options.data);
		var editables = this.getEditables();
		var avgroups = {};
		$.each(editables, function(i,e){
			var ed = e.getEditedData();
			var group = e.getProp("group") || null;
			for(var i in ed){
				if( ed.hasOwnProperty(i) ){
					if( i == group ){
						if( !avgroups[group] ){
							avgroups[group] = true;
							data[i] = [];
						}else{
							data[i] = data[i] || [];
							data[i] = $.isArray(data[i])?data[i]:[data[i]];
						}
						if( ed[i] ){
							data[i].push(ed[i]);
						}
					}else{
						data[i] = ed[i];
					}
				}
			}
		});
		return data;
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
	this.renderMainData = function(){
		var d = this.options.data;
		this.bind($(this.dom).find(".dataset-main-contents,.derived-datasets-list-container"));
		$(this.dom).find(".dataset-permalink").attr("href", appdb.config.endpoint.base + "store/dataset/" + d.guid);
	};
	this.renderAdditionalInfo = function(){
		var d = (this.options.data || {}).addedby || {};
		if( d.id ){
			var name = $.trim(d.firstname+" "+d.lastname)?d.firstname+" "+d.lastname:"";
			name = name || (d.val)?d.val():d.cname;
			$(this.dom).find("span.dataset-addedby").html('<a href="'+((d.cname)?("/store/person/"+d.cname):'#')+'" onclick="appdb.views.Main.showPerson({id: '+d.id+',cname:\''+d.cname+'\'},{mainTitle: \''+name+'\'});" target="_blank">'+name+'</a>');	
		}
		var d1 = ((this.options.data || {}).addedon || "").split("T");
		if(d1.length>0 && typeof d1[0] === "string"){
			d1 = d1[0].split("-");
		}
		if(d1.length===3){
			d1 = ''+d1[0]+'-'+d1[1]+'-'+d1[2];
		} else {
			d1 = "";
		}
		$(this.dom).find("span.dataset-addedon").html(appdb.utils.FormatISODate(d1));
	};
	this.renderLocations = function(){
		if( this.views.locationslist ){
			this.views.locationslist.reset();
			this.views.locationslist.unsubscribeAll();
			this.views.locationslist = null;
		}
		var d = (this.options.data || {}).location || [];
		d = $.isArray(d)?d:[d];
		$(this.dom).find(".dataset-locations").removeClass("empty");
		if( d.length > 0 ){
			this.views.locationslist = new appdb.views.DatasetLocationList({
				container: $(this.dom).find(".dataset-locations-list-container"),
				parent: this,
				data: d
			});
			this.views.locationslist.render();
		}else{
			$(this.dom).find(".dataset-locations").addClass("empty");
		}
	};
	this.renderDatasetInfo = function(){
		if( this.views.datasetinfo ){
			this.views.datasetinfo.reset();
			this.views.datasetinfo.unsubscribeAll();
			this.views.datasetinfo = null;
		}
		var d = (this.options.data || {}) || {};
		
		var dom = $(this.dom).find(".dataset-info");
		this.views.datasetinfo = new appdb.datasets.components.DatasetInfo({
			container: dom,
			parent: this,
			data: d.version,
			canedit: this.canEdit(),
			dataset: this.options.data || {}
		});
		this.views.datasetinfo.load({id: this.options.data.id});
	};
	this.renderLicenses = function(d){
		var v = (d || this.options.data || {}).license || [];
		v = $.isArray(v)?v:[v];
		
		if( this.views.licenselist ){
			this.options.licensestemplates = $.extend(true, {}, this.views.licenselist.options.dom);
			this.views.licenselist.reset();
			this.views.licenselist.unsubscribeAll();
			this.views.licenselist = null;
			if( $(this.dom).find(".dataset-licenses").length > 0 && $(this.dom).find(".dataset-licenses").children().length > 0 && appdb.components.Dataset.templates && appdb.components.Dataset.templates.licenses ){
				$(this.dom).find(".dataset-licenses").empty().append(appdb.components.Dataset.templates.licenses);
				$(this.dom).find(".dataset-licenses").removeClass("editmode");
			}
		} else {
			appdb.components.Dataset.templates = appdb.components.Dataset.templates || {};
			appdb.components.Dataset.templates.licenses = appdb.components.Dataset.templates.licenses || $(this.dom).find(".dataset-licenses").html() || "";
		}
		
		this.views.licenselist = new appdb.views.LicenseList({
			container: $(this.dom).find(".dataset-licenses")
		});
		this.views.licenselist.subscribe({event: "validation", callback: function(v){
			this.onValidation(v);
		},caller: this});
		this.views.licenselist.render(v);
	};
	this.renderDisciplines = function(d){
		var v = (d || this.options.data || {}).discipline || [];
		v = $.isArray(v)?v:[v];
		if( this.views.disciplines ){
			this.views.disciplines.reset();
			this.views.disciplines.unsubscribeAll();
			this.views.disciplines = null;
		} 
		
		this.views.disciplines = new appdb.views.DatasetDisciplineList({
			container: $(this.dom).find(".datasetdisciplines:first"), 
			canEdit: ( this.options.editmode && !this.isEditorExcluded('discipline') ),
			content: "dataset",
			maxViewLength: 5
		});
		this.views.disciplines.subscribe({event: "validation", callback: function(v){
			this.onValidation(v);
		},caller: this});
		this.views.disciplines.subscribe({event: "close", callback: function(v){
				setTimeout((function(self){
					return function(){
						self.onValidation(v);	
					};
				})(this),1);
			
		},caller:this});
		this.views.disciplines.render(v);
	};
	this.renderToolbar = function(){
		var ct = $(this.dom).find(".command-toolbar");
		if( !this.canEdit() ){
			$(ct).remove();
		} else {
			$(this.dom).find(".entitycontent").addClass("canedit");
			$(ct).removeClass("hidden");
			$(ct).find("[data-action='edit']").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.setEditMode(true);
					self.render();
					return false;
				};
			})(this));
			$(ct).find("[data-action='save']").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					if( $(this).hasClass("disabled") === false && self.isValid()){
						self.save();
					}
					return false;
				};
			})(this));
			$(ct).find("[data-action='cancel']").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.cancel();
					return false;
				};
			})(this));
			$(ct).find("[data-action='delete']").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.remove();
					return false;
				};
			})(this));
			$(ct).find("[data-action='moderate']").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.moderate();
					return false;
				};
			})(this));
			$(ct).find("[data-action='history']").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.showHistory();
					return false;
				};
			})(this));
			$(ct).find("[data-action='bookmark']").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.bookmark();
					return false;
				};
			})(this));
		}
		
	};
	this.renderParentLinks = function(){
		var parent = appdb.components.Dataset.parent();
		if( parent ) {
			$(this.dom).find(".parent-dataset-link").each((function(self){
				return function(i,e){
					$(e).empty();
					var a = $("<a title='' />");
					$(a).attr("href", appdb.config.endpoint.base + "store/dataset/" + parent.guid);
					$(a).attr("title", "View dataset details");
					$(a).text(parent.name);
					$(a).unbind("click").bind("click", (function(parent) {
						return function(ev){
							if( ev.which !== 1 )return;
							ev.preventDefault();
							appdb.Navigator.navigate($(this).attr('href'));
							return false;
						};
					})(parent));
					$(e).append(a);
				}; 
			})(this));
		}
	};
	this.resetViewers = function(){
		this.options.viewers = this.options.viewers || [];
		this.options.viewers = $.isArray(this.options.viewers)?this.options.viewers:[this.options.viewers];
		$.each( this.options.viewers, function(i,e){
			e.reset();
			e = null;
		});
		this.options.viewers = [];
	};
	this.bind = function(el){
		this.resetViewers();
		var elems = $(el || this.dom).find(".appdb-ui[data-bind]");
		$.each(elems, (function(self){
			return function(i,e){
				var editor = appdb.views.ui.getViewer($(e), { parent: self });
				if( editor ){
					self.options.viewers.push(editor);
				}
			};
		})(this));
		
		$.each(this.options.viewers, (function(self){
			return function(i,e){
				e.bind(self.options.data);	
			};
		})(this));
	};
	this.resetEditors = function(){
		$.each( this.options.editors || [], function(i,e){
			e.reset();
			e = null;
		});
		this.options.editors = [];
	};
	this.getExcludedEditorsBinds = function(){
		var res = [];
		if( this.options.data && this.options.data.parent ){
			res.push('discipline');
		}
		return res;
	};
	this.isEditorExcluded = function(bindname){
		bindname = $.trim(bindname);
		var exc = this.getExcludedEditorsBinds();
		return ( $.inArray(bindname, exc) > -1 );
	};
	this.renderEditors = function(excludedbinds){
		this.resetEditors();
		excludedbinds = excludedbinds || this.getExcludedEditorsBinds();
		excludedbinds = $.isArray(excludedbinds)?excludedbinds:[excludedbinds];
		
		var elems = $(this.dom).find(".dataset-data-group .appdb-ui[data-editor]");
		$.each(elems, (function(self,exclude){
			return function(i,e){
				var editor = appdb.views.ui.getEditor($(e), { parent: self });
				if( editor 
					&& ( exclude.length === 0 
						|| $.inArray(editor.getProp('bind'), exclude ) === -1 
				) ){
					self.options.editors.push(editor);	
				}
			};
		})(this, excludedbinds));
		
		$.each(this.options.editors, (function(self){
			return function(i,e){
				e.subscribe({event: "validation:start", callback: function(v){
					this.onValidation(v.isValid() || {parent: v.options.parent, message: "Validating"});
				}, caller: self});
				e.subscribe({event: "validation:end", callback: function(v){
					this.onValidation(v.isValid() || v.getError());
				}, caller: self});
				e.subscribe({event: "changed:parent", callback: function(v){
					this.onValidation(v.isValid() || v.getError());
				}, caller: self});
			
				e.bind(self.options.data);	
			};
		})(this));
		if( this.views.licenselist ){
			this.views.licenselist.edit();
		}
		
	};
	this.render = function(d){
		this.options.data = d || this.options.data;
		if( this.options.data.ancestry === "derived" ){
			$(this.dom).find(".contentcontainer").addClass("isderived");
			this.renderParentLinks();
		}else{
			$(this.dom).find(".contentcontainer").removeClass("isderived");
		}
		
		if( this.options.data.derived ){
			$(this.dom).find(".contentcontainer").addClass("hasderived");
		}else{
			$(this.dom).find(".contentcontainer").removeClass("hasderived");
		}
		
		if( appdb.components.Dataset.parentversions().length === 0 ){
			$(this.dom).find(".contentcontainer").addClass("noparentversions");
		}else{
			$(this.dom).find(".contentcontainer").removeClass("noparentversions");
		}
		
		this.renderToolbar();
		this.renderMainData();
		this.renderAdditionalInfo();
		this.renderLicenses();
		this.renderDisciplines();
		this.renderDatasetInfo();
		
		if( this.isEditMode() ){
			this.renderEditors();
			if( this.options.data.id << 0 <= 0 ){
				$(this.dom).find(".newapphintcontainer").removeClass("hidden");
			}else{
				$(this.dom).find(".newapphintcontainer").addClass("hidden");
			}
		}
	};
	this.renderError = function(){
		
	};
	this.getNewDataset = function(){
		return {
			id: 0,
			name: "",
			description: "",
			url: { type: "homepage", val: function(){return ""; }},
			discipline: [],
			category: { id:0,val: function(){ return "Life Sciences";} },
			license: []
		};
	};
	this.load = function(d,e){
		var isnewdataset = ($.inArray($.trim(d.query.id),["","0"])>0)?true:false;
		if( this._model ){
			this._model.unsubscribeAll();
			this._model = null;
		}
		this._model = new appdb.model.Dataset();
		this._model.subscribe({event: "beforeselect", callback: function(v){
				this.renderLoading(true);
		}, caller: this });
		this._model.subscribe({event: "select", callback: function(v){
				this.reset();
				appdb.pages.dataset.currentData(v.dataset);
				if( v && !v.error){
					this.render(v.dataset);
				}else{
					this.renderError(v);
				}
				appdb.pages.dataset.onDatasetLoaded(v.dataset);
				this.renderLoading(false);
		}, caller: this });
		this._model.subscribe({event: "beforeupdate", callback: function(v){
				this.renderLoading(true);
		},caller: this});
		this._model.subscribe({event: "update", callback: function(v){
				this.renderLoading(false);
				if( typeof v.error === "undefined" ){
					this.postSave(v);
				}
		},caller: this});
		this._model.subscribe({event: "beforeinsert", callback: function(v){
				this.renderLoading(true);
		},caller: this});
		this._model.subscribe({event: "insert", callback: function(v){
				this.renderLoading(false);
				if( v && !v.error){
					this.postSave(v);
				}else{
					this.renderError(v);
				}
		},caller: this});
		this._model.subscribe({event: "beforeremove", callback: function(v){
				this.renderLoading(true);
		},caller: this});
		this._model.subscribe({event: "remove", callback: function(v){
				this.renderLoading(false);
				if( typeof v.error === "undefined" ){
					this.postRemove(v);
				}
		},caller: this});
		this._model.subscribe({event: "error", callback: function(v){
			(new appdb.views.ErrorHandler()).handle({
				"status": "Cannot modify dataset", 
				"description": v.error + (($.trim(v.errordesc))?"<br/>"+v.errordesc:"")
			});
		},caller: this});
		if( isnewdataset ){
			appdb.pages.dataset.init(this.getNewDataset());
			this.setEditMode(true);
			this.render(this.getNewDataset());			
		}else{
			this._model.get( { "id": $.trim(d.query.id).toUpperCase()} );
		}		
	};
	this._init = function(){
		this.dom = $(this.options.container);
		appdb.views.ui.Templates.parse(this.dom);
		$(this.dom).find(".entitycontent").addClass("entitydataset");
		this.parent = this.options.parent;
		
		var v = {};
		this.views = v;
	};
	
	this._init();
},{
	nonparentids: function(){
		var res = null;
		var d = appdb.pages.dataset.currentData();
		if( d ){
			res = [d.id];
			
			d.derived = d.derived || [];
			d.derived = $.isArray(d.derived)?d.derived:[d.derived];
			
			$.each(d.derived, function(i,e){
				if( e && e.id ){
					res.push(e.id);
				}
			});
			return res;
		}
		return null;
	},
	availableparents: function(){
		var list = appdb.model.StaticList.DatasetList || [];
		return $.grep(list, function(e){
			return (e && e.ancestry==="primary");
		});
	},
	canEditParent: function(){
		var res = true;
		var d = appdb.pages.dataset.currentData();
		if( d && d.parent && d.parent.id){
			res = false;
		}
		
		return res;
	},
	parentversions: function(){
		var d = appdb.pages.dataset.currentData();
		if( d && d.parent){
			d.parent.version = d.parent.version ||[];
			d.parent.version = $.isArray(d.parent.version)?d.parent.version:[d.parent.version];
			return d.parent.version;
		}
		return [];
	},
	parent: function(){
		var d = appdb.pages.dataset.currentData();
		return ( d && d.parent)?d.parent:null;
	},
	cansetderived: function(){
		return (appdb.components.Dataset.parentversions.length > 0 );
	}
});

appdb.pages.dataset = (function(){
	var page = {};
	page.currentId = new appdb.utils.SimpleProperty();
	page.currentData = new appdb.utils.SimpleProperty();
	page.currentName = new appdb.utils.SimpleProperty();
	page.currentGuid = new appdb.utils.SimpleProperty();
	page.currentDialogCount = new appdb.utils.SimpleProperty();
	page.currentDisciplineList = new appdb.utils.SimpleProperty();
	page.currentDisciplines = new appdb.utils.SimpleProperty();
	
	page.currentEntityType = function(){
		return "dataset";
	};
	page.reload = function(){
		appdb.routing.Dispatch( (($.browser.msie)?window.location.hash:window.location.pathname), true);
		return false;
	};
	page.resetDisciplinesList = function(){
		
	};
	page.renderDisciplines = function(){
		
	};
	page.onDatasetLoaded = function(d){
		if(!d) return;
		page.currentData(d);
		page.currentName(d.name);
		page.currentId(d.id);
		page.currentGuid(d.guid);
		
		var disc = (d || {}).discipline || [];
		disc = $.isArray(disc)?disc:[disc];
		page.currentDisciplines(disc);
		page.renderDisciplines();
		
		if( d.id ){
			page.immediate();
			page.SetupNavigationPane();
			page.initSectionGroup();
		}
	};
	page.getPrimaryCategory = function(){
		return { val: function(){ return "Dataset";} };
	};
	page.SetupNavigationPane = function(){
		n = appdb.views.NavigationPanePresets.datasetItem({
			id: page.currentId(),
			name: page.currentName()
		});
		appdb.views.Main.clearNavigation();
		appdb.views.Main.createNavigationList(n);
		appdb.Navigator.setTitle(appdb.Navigator.Registry["Dataset"],[{name:page.currentName(),id:page.currentId()}]);
	};	
	page.initSectionGroup = function(container,onclick){
		container = container || ".dataset-group";
		if( $("#navdiv " + container + " > ul > li").length < 1 ){
			$("#navdiv " + container).removeClass("groupcontainer");
			$("#navdiv " + container + " > div").removeClass("tabcontent").removeClass("hiddengroup");
			$("#navdiv " + container + " > ul").addClass("hidden");
		}else{
			$("#navdiv " + container).addClass("groupcontainer");
			$("#navdiv " + container + " > div").addClass("hiddengroup").addClass("tabcontent");
			if( $("#navdiv " + container + " > ul > li.current > a").length > 0 ){
				var sel = $("#navdiv " + container + " > ul > li.current > a").attr("href");
				$("#navdiv " + container + " > " + sel).removeClass("hiddengroup");
			}else{
				$("#navdiv " + container + " > ul > li:first").addClass("current");
				$("#navdiv " + container + " > div:first").removeClass("hiddengroup");
			}
			$( "#navdiv " + container + " > ul > li > a").unbind("click").bind("click", function(ev){
				ev.preventDefault();
				if( typeof onclick === "function" ){
					if( onclick(this) === false ) {
						if( typeof (ev.stopImmediatePropagation) === "function" ){
							ev.stopImmediatePropagation();
						}
						return false;
					}
				}
				$("#navdiv " + container + " > ul > li").removeClass("current");
				$("#navdiv " + container + " > div").addClass("hiddengroup");
				var href = $(this).attr("href");
				$(this).parent().addClass("current");
				$(href).removeClass("hiddengroup");
				
				return false;
			});
		}
	};
	page.immediate = function(){
		$( "#appdb_components_Dataset #navdiv" ).tabs();
		$( "#appdb_components_Dataset #navdiv").bind( "tabsactivate", function(event, ui) {
			if( $.trim(window.datasettabselect) !== $.trim(ui.newTab.index())){
				window.datasettabselect = ui.newTab.index();
			}
			window.sitetabselect = ui.newTab.index();
		});
		$( ".detailsdlgcontent div:first" ).css("height","100%");
	};
	page.reset = function(){
		page.currentId(-1);
		page.currentName('');
		page.currentGuid('');
		page.currentData(null);
		
	};
	page.init = function(o){
		appdb.pages.reset();
		if( typeof o !== "undefined" ){
			page.currentId(o.id || 0);
			page.currentName(o.name || "");
			page.currentGuid(o.guid || "");
			page.currentDialogCount(dialogCount);
			page.immediate();
			page.SetupNavigationPane();
			if( o.id << 0 > 0 ){
				page.initSectionGroup();
			}
		}
	};
	return page;
})();
appdb.datasets.views.DatasetVersionListItem = appdb.ExtendClass(appdb.View, "appdb.datasets.views.DatasetVersionListItem", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {}
	};
	this.getData = function(){
		return this.options.data || {};
	};
	this.getId = function(){
		return this.getData().id;
	};
	this.render = function(){
		$(this.dom).find(".id > .value").text(this.options.data.id);
		$(this.dom).find(".version > .value").text(this.options.data.version);
		$(this.dom).unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.publish({event: "select", value: self});
				return false;
			};
		})(this));
	};
	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
});
appdb.datasets.views.DatasetVersionList = appdb.ExtendClass(appdb.View, "appdb.datasets.views.DatasetVersionList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		dom: {},
		items: [],
		canedit: (typeof o.canedit === "boolean")?o.canedit:false,
		editmode: (typeof o.edimode === "boolean")?o.editmode:false
	};
	this.reset = function(){
		$.each(this.options.items, function(i,e){
			e.reset();
			e.unsubscribeAll();
			e = null;
		});
		this.options.items = [];
		$(this.dom).children("li").remove();
	};
	this.canEdit = function(){
		return this.options.canedit;
	};
	this.getItems = function(){
		return this.options.items|| [];
	};
	this.getVersions = function(){
		return this.options.data || [];
	};
	this.getVersionIndexById = function(id){
		var index = -1;
		$.each(this.options.items, function(i,e){
			if( index === -1 && e.getId() === id ){
				index = i;
			}
		});
		return index;
	};
	this.getVersionById = function(id){
		var res = $.grep(this.options.items, function(e){
			return e.getId()==id;
		});
		return ( res.length > 0 )?res[0]:null;
	};
	this.selectVersionById = function(versionid){
		var ver = this.getVersionById(versionid);
		if( ver ){
			$(this.dom).find("li.selected").removeClass("selected");
			$(ver.dom).addClass("selected");
			this.publish({event: "select", value:ver });
		}
	};
	this.removeVersionById = function(versionid){
		var index = this.getVersionIndexById(versionid);
		if( index > -1 ){
			this.options.items[index].unsubscribeAll();
			this.options.items[index].reset();
			$(this.options.items[index].dom).remove();
			this.options.items[index] = null;
			this.options.items.splice(index,1);
		}
	};
	this.addVersionItem = function(d){
		var li = $("<li></li>");
		
		var dom = appdb.views.ui.Templates.get("dataset.versions.item");
		$(li).append(dom);
		var item = new appdb.datasets.views.DatasetVersionListItem({
			container: li,
			parent: this,
			data: d
		});
		
		this.options.items.push(item);
		
		item.subscribe({event: "select", callback: function(version){
			if( !this.parent.isEditMode() ){
				this.selectVersionById(version.getId());
			}
		}, caller: this});
		
		item.subscribe({event: "remove", callback: function(version){
			this.publish({event: "remove", value:version });
		}, caller: this});
		
		item.render();
		
		return item;
	};
	this.addNewVersionItem = function(d){
		var item = this.addVersionItem(d);
		$(this.dom).prepend(item.dom);
		this.publish({event: "add", value: item});
		return item;
	};
	this.render = function(d){
		this.reset();
		if( d ){
			d = d || this.options.data || [];
			this.options.data = $.isArray(d)?d:[d];
		}
		$.each(this.options.data, (function(self){ 
			return function(i,e){
				var version = self.addVersionItem(e);
				if( version ){
					$(self.dom).append(version.dom);
				}
			};
		})(this));
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
});

appdb.datasets.views.DatasetLocationListItem = appdb.ExtendClass(appdb.views.ui.DataEditable, "appdb.datasets.views.DatasetLocationListItem", function(o){
	this.overflowUI = function(el){
		var $content = $(el).find(".value");
		if( $content.length > 0 ){
		var visibleHeight = $content[0].clientHeight;
		var actualHide = $content[0].scrollHeight - 1;
		if (actualHide > visibleHeight){
			return true;
		}}
		return false;
	};
	this.renderShowMore = function(el){
		if( this.overflowUI(el) === false ) return;
		var showmore = $("<div class='show-more'>...read more</div>");
		$(showmore).unbind("click").bind("click", function(ev){
			ev.preventDefault();
			if( $(this).parent().hasClass("viewall") ){
				$(this).text("...read more");
				$(this).parent().removeClass("viewall");
			}else{
				$(this).text("...read less");
				$(this).parent().addClass("viewall");
			}
			return false;
		});
		$(el).find(".value").append(showmore);
	};
	this.render = function(){
		var d = this.options.data || {};
		d.site = d.site || [];
		d.site = $.isArray(d.site)?d.site:[d.site];
		d.organization = d.organization || [];
		d.organization = $.isArray(d.organization)?d.organization:[d.organization];
		
		var ismaster = ($.trim(d.master).toLowerCase() === "true")?true:false;
		if( ismaster ){
			$(this.dom).removeClass("replica");
		}else{
			$(this.dom).addClass("replica");
		}
		this.bind();
		$(this.dom).find("button.remove").unbind("click").bind("click",(function(self){
			return function(ev){
				ev.preventDefault();
				setTimeout(function(){
					self.publish({event: "remove", value: self});
				},1);
				return false;
			};
		})(this));
	};
	this.postRender = function(){
		if( this.isEditMode() ){
			$(this.dom).find("input,select,textarea").unbind("focus").bind("focus", (function(parent){ 
				return function(){
					$(parent).addClass("focused");
				};
			})($(this.dom).parent())).unbind("blur").bind("blur", (function(parent){ 
				return function(){
					setTimeout(function(){
						if( $(parent).find(".dijitFocused").length === 0 && $(parent).find("*:focus").length === 0 ){
							$(parent).removeClass("focused");
						}
					},0);					
				};
			})($(this.dom).parent()));
		}else{
			this.renderShowMore($(this.dom).find(".ds-location-notes-container"));
		}
	};
	this.preEdit = function(){
		console.log("editing location item");
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
});
appdb.datasets.views.DatasetLocationList = appdb.ExtendClass(appdb.views.ui.DataEditable, "appdb.datasets.views.DatasetLocationList", function(o){
	this.options = $.extend(true, this.options, {
		dom: {
			list: null,
			item: null
		},
		items: []
	});
	this.reset = function(){
		$.each(this.options.items, function(i,e){
			e.reset();
			e.unsubscribeAll();
			e = null;
		});
		this.options.items = [];
		this._initContainer();
	};
	this.getItemIndex = function(item){
		var index = -1;
		if( item ){
			$.each(this.options.items, function(i,e){
				if( index === -1 && e === item){
					index = i;
				}
			});
		}
		return index;
	};
	this.removeItem = function(item){
		var index = this.getItemIndex(item);
		if( index > -1 && this.options.items.length > index){
			$(item.dom).parent().remove();
			item.unsubscribeAll();
			item.reset();
			item = null;
			this.options.items.splice(index,1);
			this.checkEmpty();
			this.validate();
		}
	};
	this.checkEmpty = function(){
		$(this.dom).removeClass("empty");
		if( this.options.items.length === 0 ){
			$(this.dom).addClass("empty");
		}
	};
	this.addNew = function(){
		var newdata = {
			id: "new"+(new Date()).getTime(),
			master: "false",
			uri: "",
			notes:""
		};
		var li = this.addItem(newdata);
		$(this.options.dom.list).prepend(li);
		this.checkEmpty();
	};
	this.addItem = function(d){
		var li = $("<li></li>");
		var dom = $(appdb.views.ui.Templates.get("dataset.versions.item.locations.item"));
		$(li).append(dom);
		var item = new appdb.datasets.views.DatasetLocationListItem({
			container: dom,
			parent: this,
			data: d,
			canedit: this.canEdit()
		});
		this.options.items.push(item);
		item.render();
		item.subscribe({event: "remove", callback: function(v){
			this.removeItem(v);
		}, caller: this});
		item.subscribe({event: "validation", callback: function(invalids){
			if( invalids === true ){
				this.options.isvalid = true;
			}else{
				this.options.isvalid = false;
			}
			this.publish({event: "validation", value: invalids});
		}, caller: this});
		if( this.isEditMode() ){
			item.edit();
		}
		return li;
	};
	this.getEditables = function(el){
		return this.options.items || [];
	};
	this.getEditedData = function(){
		var res = [];
		$.each(this.getEditables(), function(i,e){
			res.push(e.getEditedData());
		});
		return {"location": res};
	};
	this.render = function(d){
		this.reset();
		d = d || this.options.data || [];
		d = $.isArray(d)?d:[d];
		
		$.each(d, (function(self){
			return function(i,e){
				var li = self.addItem(e);
				if( li ){
					$(self.options.dom.list).append(li);
				}
			};
		})(this));
		$(this.dom).find("button.addnew").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.addNew();
				return false;
			};
		})(this));
		this.checkEmpty();
		setTimeout( (function(self){ return function(){ self.postRender();};})(this), 10);
	};
	this.postRender = function(){
		$.each(this.options.items, (function(self){
			return function(i,e){
				e.postRender();
			};
		})(this));	
	};
	this._initContainer = function(){		
		$(this.dom).removeClass("editmode");
		$(this.dom).find("ul").remove();
		this.options.dom.list = $("<ul class='dataset-location-list'></ul>");
		$(this.dom).append(this.options.dom.list);
	};
	this._init = function(){
		this.parent = this.options.parent;
		this.dom = $(this.options.container);
		this._initContainer();
	};
	this._init();
});
appdb.datasets.views.DatasetVersionDetails = new appdb.ExtendClass(appdb.views.ui.DataEditable,"appdb.datasets.views.DatasetVersionDetails", function(o){
	this.options = $.extend(true,this.options,{
		templates: {
			dom: o.template || $("<div></div>"),
			locations: null,
			empty: o.emptytemplate || $('<div class="empty">No locations specified</div>')
		},
		views: {
			locationlist: null
		},
		editabledom: $(this.dom).find(".version-info")
	});
	this.isNewVersion = function(){
		return ($.trim(this.options.data.id).substr(0,3) === "new");
	};
	this.onSave = function(){
		this.publish({event: "save", value: this});
	};
	this.postSave = function(v){
		setTimeout((function(self){ return function(){self.cancel();};})(this),1);
	};
	this.onCancel = function(){
		$(this.dom).removeClass("editmode");
		this.subviews.locationlist.cancel();
		
		this.publish({event: "modechange", value: this});
		if( this.isNewVersion() ){
			this.publish({event: "remove", value: this});
		}
	};
	this.onRemove = function(v){
		appdb.utils.ShowVerifyDialog({
			title: "Dataset Version Removal",
			message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to delete this version and all related locations. Are you sure you want to procced?</span>",
			onOk: (function(self){
				return function(){
					self.publish({event: "remove", value: self});
				};
			})(this)
		});
	};
	this.postValidate = function(){
		if( !this.isValid() ){
			$(this.dom).children(".toolbar").find("button.save").addClass("btn-disabled").attr("disabled","disabled");
		}else{
			$(this.dom).children(".toolbar").find("button.save").removeClass("btn-disabled").removeAttr("disabled");
		}
	};
	this.renderToolbar = function(){
		var toolbar = $(this.dom).children(".toolbar");
		if( !this.canEdit() ){
			$(this.dom).removeClass("canedit");
			return;
		}
		$(this.dom).addClass("canedit");
		
		$(toolbar).find("button.edit").unbind("click").bind("click",(function(self){
			return function(ev){
				ev.preventDefault();
				self.edit($(self.dom).find(".version-info"));
				return false;
			};
		})(this));
		
		$(toolbar).find("button.remove").unbind("click").bind("click",(function(self){
			return function(ev){
				ev.preventDefault();
				self.remove();
				return false;
			};
		})(this));
		
		$(toolbar).find("button.save").unbind("click").bind("click",(function(self){
			return function(ev){
				ev.preventDefault();
				self.save();
				return false;
			};
		})(this));
			
		$(toolbar).find("button.cancel").unbind("click").bind("click",(function(self){
			return function(ev){
				ev.preventDefault();
				self.cancel();
				return false;
			};
		})(this));
	};
	this.reset = function(){
		for(var i in this.subviews ){
			if( this.subviews.hasOwnProperty(i) && this.subviews.reset ){
				this.subviews[i].reset();
			}
		}
	};
	this.render = function(d){
		d = d || this.options.data || {};
		d.location = d.location || [];
		d.location = $.isArray(d.location)?d.location:[d.location];
		this.options.data = d;
		if( this.subviews.locationlist ){
			this.subviews.locationlist.reset();
			this.subviews.locationlist = null;
		}
		this.subviews.locationlist = new appdb.datasets.views.DatasetLocationList({
			container: $(this.dom).find(".dataset-locations-list"),
			parent: this,
			data: this.options.data.location,
			canedit: this.canEdit()
		});
		this.subviews.locationlist.subscribe({event: "validation", callback: function(invalids){
			this.validate();
		}, caller: this});
		this.subviews.locationlist.render(d.location);
		
		this.bind($(this.dom).find(".version-info"));
		this.renderToolbar();
		if( this.options.data && this.options.data.parent_version && this.options.data.parent_version.id ){
			$(this.dom).removeClass("noderivedversion");
		}else{
			$(this.dom).addClass("noderivedversion");
		}
	};
	this.toXML = function(datasetid){
		var data = this.getEditedData();
		var mapper = new appdb.utils.EntityEditMapper.DatasetVersion();
		data.datasetid = datasetid;
		mapper.UpdateEntity(data);
		var xml = $.trim(appdb.utils.EntitySerializer.toXml(mapper.entity));
		return xml;
	};
	this._initContainer = function(){
		
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.data = this.options.data || {};
		this.options.data.location = this.options.data.location || [];
		this.options.data.location = $.isArray(this.options.data.location)?this.options.data.location:[this.options.data.location];
		this._initContainer();
	};
	this._init();
});

appdb.datasets.components.DatasetInfo = new appdb.ExtendClass(appdb.Component, "appdb.datasets.components.DatasetInfo", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {},
		templates: {},
		data: o.data || {},
		dataset: o.dataset || {},
		selectedid: o.selectedid || appdb.datasets.components.DatasetInfo.preselectedid(),
		canedit:(typeof o.canedit === "boolean")?o.canedit:false
	};
	this.canEdit = function(){
		return this.options.canedit;
	};
	this.reset = function(){
		for(var i in this.views ){
			if( this.views.hasOwnProperty(i) && this.views[i].reset ){
				this.views[i].reset();
			}
		}
	};
	this.isEditMode = function(){
		return ( this.views.versiondetails && this.views.versiondetails.isEditMode() );
	};
	this.updateVersionData = function(d){
		var index = -1;
		$.each(this.options.data, function(i,e){
			if( index < 0 && $.trim(e.id) === $.trim(d.id) ){
				index = i;
			}
		});
		if( index > -1 ){
			this.options.data[index] = d;
		}else{
			this.options.data.push(d);
		}
	};
	this.removeVersionData = function(id){
		var index = -1;
		$.each(this.options.data, function(i,e){
			if( index < 0 && $.trim(e.id) === $.trim(id) ){
				index = i;
			}
		});
		if( index > -1 ){
			this.options.data.splice(index,1);
		}
	};
	this.normalizeVersionData = function(d){
		var version = null;
		d.version = d.version || undefined;
		if( d.version && $.isArray(d.version)){
			if( d.version.length > 0 ){
				$.each(d.version, function(i,e){
					if( !version && $.isPlainObject(e) ){
						version = e;
					}
				});
			}
		}else{
			version = d.version;
		}
		d.version = version;
		return d;
	};
	this.saveVersion = function(){
		this.renderLoading(true, (this.views.versiondetails.isNewVersion)?"Saving":"Updating");
		this._model = new appdb.model.DatasetVersion();
		var xml = this.views.versiondetails.toXML(this.options.dataset.id);
		appdb.debug(xml);
		
		this._model.subscribe({event: "update", callback: function(d){
			this.renderLoading(false);
			d = d || {};
			d = this.normalizeVersionData(d);
			if( d.error ){
				
			}else{
				this.updateVersionData(d.version);
				this.views.versionlist.render(this.options.data);
				this.selectVersion(d.version.id);
			}
		}, caller: this});
		this._model.subscribe({event: "insert", callback: function(d){
			this.renderLoading(false);	
			d = d || {};
			d = this.normalizeVersionData(d);
			if( d.error ){
				
			}else{
				this.updateVersionData(d.version);
				this.views.versionlist.render(this.options.data);
				this.selectVersion(d.version.id);
			}
		}, caller: this});
		this._model.subscribe({event: "error", callback: function(d){
			(new appdb.views.ErrorHandler()).handle({
				"status": "Cannot save dataset version", 
				"description": d.error
			});
		},caller: this});
		if( this.views.versiondetails.isNewVersion()  ){
			this._model.insert({query: {id: this.options.dataset.id}, data: {data: xml}});	
		}else{
			this._model.update({query: {id: this.options.dataset.id, versionid:this.views.versiondetails.getId() }, data: {data: encodeURIComponent(xml)}});	
		}
	};
	this.removeVersion = function(item){
		if( this.views.versiondetails.isNewVersion()  ){
			return;
		}
		this._model = new appdb.model.DatasetVersion();
		var data = item.getData();
		
		this._model.subscribe({event: "beforeremove", callback: function(d){
			this.renderLoading(true,"Removing version: " + data.version);
		}, caller: this});
		this._model.subscribe({event: "remove", callback: function(d){
			this.renderLoading(false);
			if( d && d.error ){
				
			}else{
				this.removeVersionData(item.getId());
				this.views.versionlist.render(this.options.data);
				if( !this.views.versionlist.getVersionById(this.options.selectedid) ){
					if( this.views.versionlist.getVersions().length > 0 ){
						this.options.selectedid = this.views.versionlist.getVersions()[0].id;
					}	
				}
				this.selectVersion();
			}
		}, caller: this});
		this._model.subscribe({event: "error", callback: function(d){
			(new appdb.views.ErrorHandler()).handle({
				"status": "Cannot delete dataset version", 
				"description": d.error
			});
		},caller: this});
		this._model.remove({id: this.options.dataset.id, versionid: item.getId() });	
	};
	this.renderVersionDetails = function(id){
		$(this.dom).removeClass("versionnotfound");
		if( this.isEditMode() ) return;
		if( this.views.versiondetails && this.views.versiondetails.isEditMode() ) return;
		var versionlistitem = null;
		var data = null;
		if( parseInt(id) > 0 || typeof id !== "object" ){
			versionlistitem = this.views.versionlist.getVersionById(id || this.options.selectedid);
		}else{
			versionlistitem = id;
		}
		if( this.views.versiondetails && this.views.versiondetails.reset ){
			this.views.versiondetails.unsubscribeAll();
			this.views.versiondetails.reset();
			this.views.versiondetails = null;
		}
		if( versionlistitem ){
			this.views.versiondetails = new appdb.datasets.views.DatasetVersionDetails({
				container: $(this.options.dom.versiondetails),
				parent: this,
				canedit: this.canEdit()
			});
			this.views.versiondetails.subscribe({event: "location.add", callback: function(v){
				this.versionAction("version.location.add",v);
			},caller:this});
			this.views.versiondetails.subscribe({event: "location.remove", callback: function(v){
				this.versionAction("version.location.remove",v);
			},caller:this});
			this.views.versiondetails.subscribe({event: "location.location", callback: function(v){
				this.versionAction("version.location.update",v);
			},caller:this});
			this.views.versiondetails.subscribe({event: "update", callback: function(v){
				this.versionAction("version.update",v);
			},caller:this});
			this.views.versiondetails.subscribe({event: "remove", callback: function(v){
				this.versionAction("version.remove",v);	
			},caller: this});
			this.views.versiondetails.subscribe({event: "modechange", callback: function(v){
				this.onVersionEditMode(v.isEditMode());
			},caller: this});
			this.views.versiondetails.subscribe({event: "save", callback: function(v){
				this.saveVersion();
			},caller: this});
			this.views.versiondetails.render(versionlistitem.getData());
			if( !this.views.versiondetails.isNewVersion() ){
				this.options.selectedid = versionlistitem.getData().id;
			}
		}
	};
	this.versionAction = function(action,item){
		switch(action){
			case "version.remove":
				if( item.isNewVersion()){
					this.views.versionlist.removeVersionById(item.getId());
					this.selectVersion();
				}else{
					this.removeVersion(item);
				}
				break;
		}
		this.renderEmptyVersionList();
	};
	this.onVersionEditMode = function(editmode){
		editmode = (typeof editmode === "boolean")?editmode:false;
		if( editmode ){
			$(this.dom).addClass("version-editmode");
			$(this.dom).find(".group-version-list > .toolbar > button.add").addClass("btn-disabled").attr("disabled","disabled");
		}else{
			$(this.dom).removeClass("version-editmode");
			$(this.dom).find(".group-version-list > .toolbar > button.add").removeClass("btn-disabled").removeAttr("disabled");
		}
		var id = this.views.versiondetails.getId();
		if( isNaN(parseInt(id)) ){
			this.selectVersion();
		}else{
			this.selectVersion(id);
		}
	};
	this.addNewVersion = function(){
		var newitem = this.views.versionlist.addNewVersionItem({
			"id": "new"+ (new Date()).getTime(),
			"datasetid": this.parent.options.data.id,
			"version": "",
			"notes": "",
			"location": []			
		});
		this.views.versionlist.selectVersionById(newitem.getId());
		this.views.versiondetails.edit();
		this.onVersionEditMode(this.views.versiondetails.isEditMode());
	};
	this.selectVersion = function(id){
		$(this.dom).removeClass("versionnotfound");
		if( this.isEditMode() ) return;
		
		if( id ){
			this.options.selectedid = parseInt(id);
		}
		
		if( !(parseInt(this.options.selectedid) > 0) ){
			var data = this.views.versionlist.getVersions();
			if( data.length > 0){
				this.options.selectedid = parseInt(data[0].id);
			}
		}
		
		if( this.views.versionlist.getVersionById(this.options.selectedid) === null ){
			if( this.views.versionlist.getItems().length > 0 ){
				$(this.dom).addClass("versionnotfound");	
			}
		}else{
			this.views.versionlist.selectVersionById(this.options.selectedid);
		}
		
		this.renderEmptyVersionList();
	};
	this.renderEmptyVersionList = function(){
		if( this.views.versionlist.getItems().length === 0 ){
			$(this.dom).addClass("empty");
		}else{
			$(this.dom).removeClass("empty");
		}
	};
	this.render = function(d){
		d = d || this.options.data || [];
		this.options.data = $.isArray(d)?d:[d];
		this.reset();
		this.views.versionlist.render(this.options.data);
		this.selectVersion();
		$(this.dom).removeClass("init");
		$(this.dom).find(".group-version-list > .toolbar > button.add").unbind("click");
		$(this.dom).find(".emptycontainer button.init").unbind("click");
		if( this.canEdit() ){
			$(this.dom).addClass("canedit");
			$(this.dom).find(".group-version-list").addClass("canedit");
			$(this.dom).find(".group-version-list > .toolbar > button.add").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.addNewVersion();
					return false;
				};
			})(this));
			$(this.dom).find(".emptycontainer button.init").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.addNewVersion();
					return false;
				};
			})(this));
		}else{
			$(this.dom).removeClass("canedit");
			$(this.dom).find(".group-version-list").removeClass("canedit");
		}
		this.renderEmptyVersionList();
	};
	this.renderLoading = function(loading,text){
		loading = (typeof loading === "boolean")?loading:false;
		$(this.dom).removeClass("loading").children(".loader").remove();
		if( loading ){
			$(this.dom).addClass("loading").
			text = text || "loading";
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).append(loader);
		}
	};
	this.load = function(d){
		if( this._model ){
			this._model.unsubscribeAll();
			this._model = null;
		}
		this._model = new appdb.model.DatasetVersions();
		this._model.subscribe({event: "beforeselect", callback: function(v){
				this.renderLoading(true,"Fetching versions");
		},caller:this});
		this._model.subscribe({event: "select", callback: function(v){
				this.renderLoading(false);
				if( v && v.error ){
					alert(v.error);
				}else{
					v = v || {};
					v.version = v.version || [];
					v.version = $.isArray(v.version)?v.version:[v.version];
					v.version = $.grep(v.version, function(e){
						return (typeof e !== "string");
					});
					this.render(v.version);
				}
		},caller:this});
		this._model.get({id: d.id});
	};
	this._initContainer = function(){
		this.options.dom.versionlist = $(this.dom).find(".group-version-list ul.versions");
		this.options.dom.versiondetails = $(this.dom).find(".main .version-container");
	};
	this._initSubViews = function(){
		if( this.views.versionlist && this.views.versionlist.reset ){
			this.views.versionlist.unsubscribeAll();
			this.views.versionlist.reset();
			this.views.versionlist = null;
		}
		this.views.versionlist = new appdb.datasets.views.DatasetVersionList({
			container: $(this.options.dom.versionlist),
			parent: this,
			canedit: this.canEdit()
		});
		this.views.versionlist.subscribe({event: "add", callback: function(v){
			this.versionAction("version.add",v);
		}, caller:this });
		this.views.versionlist.subscribe({event: "remove", callback: function(v){
			this.versionAction("version.remove",v);	
		}, caller:this });
		this.views.versionlist.subscribe({event: "select", callback: function(v){
			this.renderVersionDetails(v);
		}, caller:this });
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
		this._initSubViews();
	};
	this._init();
},{
	preselectedid: function(){
		var route = appdb.routing.FindRoute(window.location.pathname);
		if( route && route.parameters && route.parameters.versionid ){
			return parseInt(route.parameters.versionid);
		}
		return -1;
	}
});

appdb.views.ui.viewers.dataset = appdb.views.ui.viewers.dataset || {};
appdb.views.ui.viewers.dataset.parentlink = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.dataset.parentlink", function(o){
	this.getDisplayData = function(){
		var d = this.getData();
		if( !d ){
			d = this.getDataSource();
		}
		var el = $("<div class='fieldvalue'></div>");
		var field = $("<div class='field'>Derived from</div>");
		var arrow = $("<div class='field arrow'>&#9658;</div>");
		var a = $("<a class='value parentdataset' href='' title='' ></a>");
		if( d ){
			$(a).attr("href", appdb.config.endpoint.base + "store/dataset/" + d.guid);
			$(a).attr("title", "View dataset details");
			$(a).text(d.name);
		}
		$(el).append(field).append(arrow).append(a);
		return $("<span></span").append(el).html();
	};
	this.postRender = function(){
		var d = this.getData();
		if( !d ){
			d = this.getDataSource();
		}
		$(this.dom).find("a.value.parentdataset").unbind("click").bind("click", (function(d) {
			return function(ev){
				if( ev.which !== 1 )return;
				ev.preventDefault();
				appdb.Navigator.navigate($(this).attr('href'));
				return false;
			};
		})(d));
	};
});
appdb.views.ui.viewers.dataset.deriveddatasets = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.dataset.deriveddatasets", function(o){
	this.addItem = function(d){
		var li = $("<li class='itemcontainer'></li>");
		var div = $("<div class='item'></div>");
		var name = $("<span class='name itemname'></span>");
		var img = $("<img class='itemimage' src='/images/dataset.png' alt=''/>");
		var meta = $("<span class='meta'></span>");
		
		var a = $("<a title='View dataset details' class='itemlink'></a>");
		$(a).attr("href",appdb.config.endpoint.base + "store/dataset/" + d.guid);
		$(name).text(d.val());
		$(a).append(img).append(name);
		$(a).unbind("click").bind("click", (function(d) {
			return function(ev){
				if( ev.which !== 1 )return;
				ev.preventDefault();
				appdb.Navigator.navigate($(this).attr('href'));
				return false;
			};
		})(d));
		if( parseInt(d.version_count) > 0 ){
			var version = $("<span></span>").addClass("itemversion").append("<span>version"+ (parseInt(d.version_count)>1?"s":"") +"</span>");
			$(version).prepend($("<span class='value'></span>").text(d.version_count));
			$(meta).append(version);
		}
		if( parseInt(d.location_count) > 0 ){
			var location = $("<span></span>").addClass("itemlocation").append("<span>location"+ (parseInt(d.location_count)>1?"s":"") +" across </span>");
			$(location).prepend($("<span class='value'></span>").text(d.location_count));
			$(meta).append(location);
		}
		$(a).append(meta);
		$(div).append(a);
		$(li).append(div);
		$(this.dom).append(li);
		
	};
	this.render = function(){
		var d= this.getData();
		d = d || [];
		d = $.isArray(d)?d:[d];
		$.each(d, (function(self){
			return function(i,e){
				self.addItem(e);
			};
		})(this));
	};
});
appdb.views.ui.viewers.dataset.derivedfrom = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.dataset.derivedfrom", function(o){
	this.getDisplayData = function(){
		var d = this.getData();
		var parent = appdb.components.Dataset.parent();
		if( !parent ){
			return null;
		}
		var el = $("<div class='fieldvalue'></div>");
		var dataset = $("<a class='value dataset' href='' title='' ></a>");
		var sep = $("<span>&#9658;</span>");
		var version = $("<a class='value version' href='' title='' ></a>");
		if( d ){
			$(dataset).attr("href", appdb.config.endpoint.base + "store/dataset/" + parent.guid );
			$(dataset).attr("title", "View dataset details");
			$(dataset).text(d.dataset_name);
			
			$(version).attr("href", appdb.config.endpoint.base + "store/dataset/" + parent.guid + "/version/" + d.id);
			
			$(version).attr("title", "View dataset version details");
			$(version).text(d.version);
		}
		$(el).append(dataset).append(sep).append(version);
		return $("<span></span").append(el).html();
	};
	this.postRender = function(){
		var d = this.getData();
		var parent = appdb.components.Dataset.parent();
		$(this.dom).find("a.value.dataset").unbind("click").bind("click", (function(parent) {
			return function(ev){
				if( ev.which !== 1 )return;
				ev.preventDefault();
				appdb.Navigator.navigate($(this).attr('href'));
				return false;
			};
		})(parent));
		$(this.dom).find("a.value.version").unbind("click").bind("click", (function(parent,d) {
			return function(ev){
				if( ev.which !== 1 )return;
				ev.preventDefault();
				appdb.Navigator.navigate($(this).attr('href'));
				return false;
			};
		})(parent,d));
	};
});
appdb.views.ui.validators.dataset.checkname = appdb.ExtendClass(appdb.views.ui.validators.AjaxValidator, "appdb.views.ui.validators.dataset.checkname", function(o){
	this.getAjaxData = function(){
		return  {
			url: appdb.config.endpoint.base + "datasets/nameavailable",
			data: {
				n: this.options.value,
				id: appdb.pages.dataset.currentId()
			}
		};
	};
	this.canMakeCall = function(){
		return (this.options.previousValue !== this.options.value);
	};
	this.getErrorMessage = function(){
		return "name is already in use";
	};
	this.onSuccess = function(v,cb){
		var d = appdb.utils.convert.toObject(v);
		if( d && !d.error ){
			cb(true,this);
		}else{
			cb(false,this);
		}
	};
	this.onError = function(d,cb){
		cb(d,this);
	};
});
appdb.views.ui.hooks.dataset.editversion = appdb.ExtendClass(appdb.views.ui.hooks.Generic,"appdb.views.ui.hooks.dataset.editversion", function(o){
	this.onValidation = function(d){
		var d = $.trim(this.parent.getDisplayData()) || this.options.emptyval;
		$(this.options.versiondom).html(d);
	};
	this.reset = function(){
		var dom = $(this.dom).closest(".dataset-info").find(".list .versions .selected .dataset-version-header");
		$(dom).find(".editvalue.fieldvalue").remove();
		this.options.view.unsubscribeAll(this);
		this.unsubscribeAll();
		$(this.dom).empty();
	};
	this.render = function(d){
		this.options.view.subscribe({event: "validation", callback: function(v){
			this.onValidation();
		},caller: this});
		this.onValidation();
	};
	this._initSettings = function(){
		
	};
	this._initContainer = function(){
		$(this.options.view.dom).children(".validationmessage").remove();
		this.dom = $('<div class="validationmessage"><img src="/images/vappliance/warning.png" alt=""><div class="validationerrormessage"><span>Value is required</span></div></div>');
		$(this.options.view.dom).append(this.dom);
		
		this.options.versiondom = $("<div class='editvalue fieldvalue'></div>");
		var dom = $(this.dom).closest(".dataset-info").find(".list .versions .selected .dataset-version-header");
		$(dom).find(".editvalue.fieldvalue").remove();
		$(dom).append(this.options.versiondom);
		var data = this.parent.parent.options.data;
		var defemptyval = "n/a";
		if( data && $.trim(data.id).substr(0,3) === "new" ){
			defemptyval = "<new version>";
		}
		this.options.emptyval = $.trim(this.parent.getProp("empty")) || defemptyval;
		this.options.emptyval = $("<span class='empty'></span>").text(this.options.emptyval);
		
	};
});
appdb.views.ui.hooks.dataset.namestatus = appdb.ExtendClass(appdb.views.ui.hooks.Generic,"appdb.views.ui.hooks.dataset.namestatus", function(o){
	this.options.intervalValue = 20000;
	this.options.interval = null;
	this.onValidationStart = function(v){
		$(this.dom).removeClass("hidden");
		$(this.dom).find("span").text("...checking name availability");
		$(this.dom).find("img").attr("src","/images/ajax-loader-small.gif");
	};
	this.hideResult = function(force){
		force = (typeof force === "boolean")?force:false;
		if( this.options.interval ){
			clearTimeout(this.options.interval);
		}
		if( force ){
			$(this.dom).addClass("hidden");
			return;
		}
		this.options.interval = setTimeout((function(self){
			return function(){
				$(self.dom).addClass("hidden");
			};
		})(this), this.options.intervalValue || 20000);
	};
	this.onValidationEnd = function(v){
		$(this.dom).addClass("hidden");		
	};
	this.reset = function(){
		this.options.view.unsubscribeAll(this);
		this.unsubscribeAll();
		$(this.dom).empty();
		$(this.dom).closest(".dataset-info").find(".namestatus").remove();
	};
	this.render = function(d){
		this.options.view.subscribe({event: "validation:start:checkname", callback: function(v){
			this.onValidationStart(v);
		},caller: this});
		this.options.view.subscribe({event: "validation:end:checkname", callback: function(v){
			this.onValidationEnd(v);
		},caller: this});
		this.options.view.subscribe({event: "validating", callback: function(v){
			this.hideResult(true);
		},caller:this});
	};
	this._initSettings = function(){
		
	};
	this._initContainer = function(){
		$(this.options.view.dom).children(".namestatus").remove();
		this.dom = $('<div class="namestatus hidden"><img src="/images/ajax-loader-small.gif" alt=""><span>...checking name availability</span></div>');
		$(this.options.view.dom).append(this.dom);
	};
});


appdb.datasets.autoInit();
