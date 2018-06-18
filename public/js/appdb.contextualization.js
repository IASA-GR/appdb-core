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
appdb.contextualization = {
	model: { Contextualization: appdb.model.Contextualization },
	components: {},
	ui: { helpers: { viewers: {}, macros: {} }, views: {} },
	utils: {},
	validators: {},
	manager: null
};
appdb.contextualization.utils.dataToString = function(data){
	var t = data || "";
	if( typeof data === "string" ){
		t = $.trim(data);
	}else if(typeof data === "function" ){
		t = $.trim(data());
	}else if( data && typeof data.val === "function"){
		t = $.trim(data.val());
	}
	return t;
};
appdb.contextualization.utils.isValidUrl = function(value){
	return /(ftps|ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i.test($.trim(value));
};
appdb.contextualization.ui.ShowVerifyDialog = appdb.utils.ShowVerifyDialog;
appdb.contextualization.ui.helpers.TextAreaEditor = function(dom,parent){
	this.dom = $(dom);
	this.domNode = $(dom)[0];
	this.parent = parent;
	this.destroyRecursive = function(){
		$(this.dom).empty();
	};
	this.onValueChange = function(){
		if( this.parent.onValueChange ){
			this.parent.onValueChange();
		}
	};
	this.render = function(){
		$(this.dom).off("keyup").on("keyup", (function(self){
			return function(ev){
				ev.preventDefault();
				self.onValueChange();
				return false;
			};
		})(this)).text(parent.getDataValue());
	};
	this.get = function(name){
		name = $.trim(name).toLowerCase();
		switch(name){
			case "value":
			default:
				return  $(this.dom).val();
		}
	};
	this.set = function(name,val){
		name = $.trim(name).toLowerCase();
		switch(name){
			case "value":
			default:
				$(this.dom).val(val);
		}
	};
	this.focusNode = $(this.dom)[0];
	this.focus = function(){
		if( this.dom && $(this.dom).length > 0  && $(this.dom).is(":visible") ){
			$(this.dom).focus();
		}
	};
	this.render();

};
appdb.contextualization.ui.helpers.DataViewer = function(dom,data,context){
	var t = appdb.contextualization.utils.dataToString(data);
	$(dom).text(t);
};
appdb.contextualization.ui.helpers.getSelectHtmlOptions = function(ds, selid){
	ds = ds || [];
	ds = $.isArray(ds)?ds:[ds];
	selid = $.trim(selid);
	return $.map(ds, function(e){
		var opt = $("<option></option>").attr("value", e.id).text( (e.val)?e.val():e.name);
		if( selid === $.trim(e.id) ){
			$(opt).prop("selected", true);
		}
		return opt[0];
	});
};
appdb.contextualization.ui.helpers.executeMacros = function(dom,data){
	dom = dom || this.dom;
	if( $(dom).is(':parent') === false ) return;
	var mcs = appdb.contextualization.ui.helpers.macros;

	for(var m in mcs){
		if( mcs.hasOwnProperty(m) === false )continue;
		$(dom).find("[data-"+m+"]").each( (function(macro){
			return function(i,e){
				var props = $(e).data();
				if( $.trim(props[macro]) !== "") {
					mcs[m](this,data);
				}
			};
		})(m));
	}
};
appdb.contextualization.ui.helpers.macros = {
	displayon: function(dom,data){
		var d = appdb.FindData(data,$(dom).data("displayon"));
		if( !d ){
			$(dom).addClass("displayon_hidden");
		}else{
			$(dom).removeClass("displayon_hidden");
		}
	},
	onempty: function(dom,data){
		var path = $.trim($(dom).data()["path"]);
		var d = (path)?data[path]:"";
		if( !path || !d || $.trim(d) === "" || ($.isArray(d) && (d.length ===0 || !d[0])) || $.isEmptyObject(d)){
			$(dom).empty().html("<span class='emptydatamessage'>" + $(dom).data("onempty") + "</span>");
		}
	},
	datetime: function(dom,data,context){
		var t = appdb.contextualization.utils.dataToString(data);
		t = t.split("T");
		$(dom).text(t[0]);
	}
};
appdb.contextualization.utils.copyObjectArray = function(d){
	d = d || [];
	d = $.isArray(d)?d:[d];
	var res = [];
	$.each(d, function(i,e){
		res.push($.extend(true,{},e));
	});
	return res;
};
appdb.contextualization.utils.GetVapplianceImages = appdb.ExtendClass(appdb.View, "appdb.contextualization.utils.GetVapplianceImages", function(){
	this._model = null;
	this.reset = function(){
		this.unsubscribeAll();
	};
	this.orderImages = function(a,b){
		var aa = a.os.val();
		var bb = b.os.val();
		if( aa > bb ) return 1;
		if( aa < bb ) return -1;

		aa = a.arch.val();
		bb = b.arch.val();
		if( aa > bb ) return 1;
		if( aa < bb ) return -1;

		aa = a.hypervisor.val();
		bb = b.hypervisor.val();
		if( aa > bb ) return 1;
		if( aa < bb ) return -1;

		return 0;
	};
	this.getVersionImages = function(version){
		version = version || {};
		version.image = version.image || [];
		version.image = $.isArray(version.image)?version.image:[version.image];
		var instances = [];

		$.each(version.image, function(i,e){
			e.instance = e.instance || [];
			e.instance = $.isArray(e.instance)?e.instance:[e.instance];
			instances = instances.concat(e.instance);
		});
		instances.sort(this.orderImages);
		return instances;
	};
	this.getLatestVersion = function(data){
		var versions = (data && data.appliance && data.appliance.instance )?data.appliance.instance:[];
		versions = $.isArray(versions)?versions:[versions];
		var res = $.grep(versions, function(e){
			return $.trim(e.published) === "true" && $.trim(e.archived) === "false";
		});

		return ( res.length > 0 )?res[0]:null;
	};
	this.transformData = function(data){
		data = data || {};
		var ver = this.getLatestVersion(data);
		var images = this.getVersionImages(ver);
		this.publish({event: "select", value: images});
	};
	this.load = function(vappid, async){
		async = (typeof async === "boolean")?async:false;
		if( this._model ){
			this._model.destroy();
			this._model = null;
		}

		this._model = new appdb.model.VirtualAppliance({id:vappid});
		this._model.subscribe({event: "select", callback: function(v){
				this.transformData(v);
		},caller: this});
		this._model.get({id:vappid});
	};
});
appdb.contextualization.ui.views.SelectVirtualAppliance = appdb.ExtendClass(appdb.View, "appdb.contextualization.ui.views.SelectVirtualAppliance", function(o){
	this.options = {
		list: null
	};
	this.onSelect = function(v){
		var data = {
			name: v.name,
			cname: v.cname,
			id: v.id,
			image: [],
			imagelistsprivate: (v.appliance && v.appliance.imagelistisprivate)?"true":"false"};
		if( v.vappliance ){
			data.latestversion = { id: v.vappliance.versionid, version: v.vappliance.version };
		}
		this.publish({event:"select", value: data});
	};
	this.show = function(){
		this.hide();
		var dom = $("<div class='relatedentities'></div>");
		appdb.contextualization.ui.views.SelectVirtualAppliance.dialog = new dijit.Dialog({
			title: "Associate Virtual Appliance",
			content: $(dom)[0],
			style: "width: 830px;height:550px;"
		});
		this.options.list =  new appdb.components.RelatedEntities({
			container: $(dom),
			parent: this
		});
		this.options.list.subscribe({event: "select", callback: function(v){
				this.onSelect(v);
				this.hide();
		}, caller: this});
		appdb.contextualization.ui.views.SelectVirtualAppliance.dialog.show();
		this.options.list.load({query:{flt: "+*&application.metatype:1 -=application.published:NULL",pagelength:12},ext:{baseQuery:{flt:"+*&application.metatype:1 -=application.published:NULL"},isBaseQuery:true,content:"vappliance"}});
	};
	this.hide = function(){
		if( this.options.list ){
			this.options.list.unsubscribeAll();
			this.options.list.destroy();
			this.options.list = null;
		}
		var dialog = appdb.contextualization.ui.views.SelectVirtualAppliance.dialog;
		if( dialog ){
			dialog.hide();
			dialog.destroyRecursive();
			$("body").find(".relatedentities").closest(".dijitDialog").remove();
			appdb.contextualization.ui.views.SelectVirtualAppliance.dialog = null;
		}
	};
	this._init = function(){
		this.dom = $("<div class='relatedentities'></div>");

	};
	this._init();
},{dialog: null});
appdb.contextualization.ui.helpers.viewers.datetime = function(dom,data,context){
	var t = appdb.contextualization.utils.dataToString(data);
	t = t.split("T");
	$(dom).text(t[0]);
};
appdb.contextualization.ui.helpers.viewers.personlink = function(dom,v,context){
	if( !v || (v.id) << 0 === 0 || $.trim(v)===""){
		$(dom).empty();
		return;
	}
	var l = appdb.config.endpoint.base + "store/person/" + v.cname;
	var h = $("<span><a href='"+l+"' target='_blank' title='View person details' class='personcardlink'><span>"+v.firstname+" " + v.lastname + "</span></a></span>");
	var b = "<div class='popup personcard'>";
	b += "<a href='" + l + "' title='Click to view profile'>";
	b += "<img src='" + appdb.config.endpoint.base + "people/getimage?id="+v.id+"' alt='' />";
	b += "<span class='fullname'><span class='firstname'>" + v.firstname + "</span><span class='lastname'>"+v.lastname+"</span></span>";
	b += "<span class='role "+ ((v.role.validated==='true')?"validated":"") +"'>" + v.role.type + "</span>";
	b += "<span class='institute'>" + v.institute + "</span>";
	b += "<span class='onhover'><span>Click to view profile</span></span>";
	b += "</a>";
	b += "</div>";
	b = $(b);
	$(dom).append(h);
	$(b).find("a").off("click").on("click", (function(data){
		return function(ev){
			ev.preventDefault();
			appdb.views.Main.showPerson({id:data.id, cname:data.cname},{mainTitle: data.firstname + " " + data.lastname});
			return false;
		};
	})(v));
	$(h).find("a.personcardlink:first").off("click").on("click",(function(content){
		return function(ev){
			ev.preventDefault();
			var pu =  new dijit.TooltipDialog({content : content});
			dijit.popup.open({
				parent : $(this)[0],
				popup: pu,
				around : $(this)[0],
				orient: {'TL':'BL','TR':'BR'}
			});
			return false;
		};
	})(b));

};
appdb.contextualization.ui.helpers.viewers.vappliancelink = function(dom,v,context){
	if( !v || $.trim(v.cname) ===""){
		$(dom).empty();
		return;
	}
	var l = appdb.config.endpoint.base + "store/vappliance/" + v.cname;
	var h = $("<a href='"+l+"' target='_blank' title='View virtual appliance details' ><img src='" + appdb.config.endpoint.base + "apps/getlogo?id="+v.id+"' alt/><span>" + v.name + "</span></a><span class='arrow'>▼</span><span class='imagecount'><span class='count'></span><span>image</span><span class='plural'>s</span></span>");
	$(dom).append(h);
	v.image = v.image || [];
	v.image = $.isArray(v.image)?v.image:[v.image];
	$(h).find(".count").text(v.image.length);
	if( v.image.length > 1 ){
		$(h).parent().find(".imagecount").addClass("plural");
	}
	$(h).find("a").off("click").on("click", (function(data){
		return function(ev){
			ev.preventDefault();
			$("body").find(".dijitPopup.dijitTooltipDialogPopup").remove();
			appdb.views.Main.showVirtualAppliance({id:data.id, cname:data.cname});
			return false;
		};
	})(v));
};
appdb.contextualization.ui.helpers.viewers.link = function(dom,v,context){
	var inner = $.trim($(dom).html() || $(dom).text());
	if( inner === "" ){
		if( context && $.trim(context.name)!==""){
			$(dom).text($.trim(context.name));
		}else{
			$(dom).text(v);
		}
	}
	$(dom).attr("href", v).prop("disabled", false).off("click").on("click", function(ev){
		ev.stopPropagation();
		return true;
	});

};
appdb.contextualization.ui.helpers.viewers.postSaveContextScriptMessage = function(dom, v, context){
	$(dom).removeClass("hasmessage").find(".postsavemessage").remove();
	if( appdb.contextualization.manager.versionUpdated() === true ) return;

	var div = $("<div class='postsavemessage icontext'><img src='/images/vappliance/warning.png' alt=''/><span class='message'></span><a title='close this message' class='closemessage' href='#'>x</a></div>");
	$(div).find("span").html("It is suggested to update the version field. <a href='#' class='editversion' title='Edit version'>edit now</a>");
	$(dom).addClass("hasmessage").prepend(div);
	var bannerInterval = setTimeout((function(self){
		return function(){
			if( $(self).length > 0  ){
				$(self).closest(".contextscript-item").removeClass("hasmessage").find(".postsavemessage").remove();
			}
		};
	})(div),10000);
	$(div).find(".closemessage").off("click").on("click", (function(interval){
		return function(ev){
			ev.preventDefault();
			clearTimeout(interval);
			setTimeout((function(self){
				return function(){
					$(self).closest(".contextscript-list").find(".contextscript-item").removeClass("hasmessage").find(".postsavemessage").remove();
				};
			})(this),1);
			return false;
		};
	})(bannerInterval));
	$(div).find(".editversion").off("click").on("click",function(ev){
			ev.preventDefault();
			window.scroll(0,0);
			$(this).closest(".contextualization-version.contextualization").find(".context-version-property .action.edit").trigger("click");
			$(this).closest(".contextscript-list").find(".contextscript-item").removeClass("hasmessage").find(".closemessage").trigger("click");
			return false;
	});
};
appdb.contextualization.ui.DataBinder = appdb.ExtendClass(appdb.View, "appdb.contextualization.ui.views.DataBinder", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		props: $(o.container).data(),
		editable: false,
		tagname: "div",
		datahandler: (typeof o.datahandler === "object" || typeof o.datahandler === "function")?o.datahandler:null,
		datacontext: o.datacontext || {},
		dataid: $.trim(o.dataid) || "",
		datasource: null,
		required: false,
		postValueChange: []
	};
	this.isEditable = function(){
		return this.options.editable;
	};
	this.getTagName = function(){
		return this.options.tagname;
	};
	this.getDataHandler = function(){
		return this.options.datahandler;
	};
	this.getDataSource = function(){
		return this.options.datasource;
	};
	this.reset = function(){
		this.unsubscribeAll();
		if( this.options.editor ){
			this.options.editor.destroyRecursive();
			this.options.editor = null;
		}
		$(this.dom).empty();
	};
	this.hasChanges = function(){
		return this.getDataValue() !== this.getEditData();
	};
	this.getEditData = function(){
		return (this.options.editor)?this.options.editor.get("value"):this.getDataValue();
	};
	this.getDataPath = function(){
		return $.trim(this.options.props.path);
	};
	this.getData = function(){
		return this.options.datacontext;
	};
	this.getDataValue = function(onlypath){
		onlypath = (typeof onlypath === "boolean")?onlypath:false;
		var d = this.options.datacontext;

		if( this.options.props.path !== "_"){
			d = appdb.FindData(this.options.datacontext,this.options.props.path);
		}
		if( onlypath ){
			return d;
		}

		if( typeof d === "string" ){
			return d;
		}
		if( d && typeof d.val === "function" ){
			return d.val();
		}

		if( d && $.trim(d.id) !== ""){
			return d.id;
		}

		if( d && typeof d.val !== "undefined" ){
			return d.val;
		}

		return d;
	};
	this.onValueChange = function(){
		$.each(this.options.postValueChange, (function(self){
			return function(i,e){
				e.apply(self);
			};
		})(this));
		this.parent.onChange(this.options.props.path,this.getDataValue(),this.getEditData(),this);
		this.publish({event: "change", value: this.getEditData(), caller: this });
	};
	this.bind = function(){
		var dom = $(this.dom);
		var d = this.getDataValue();
		var datahandler = this.getDataHandler();

		if( datahandler ){
			datahandler(dom, this.getDataValue(true), this.options.datacontext);
			return;
		}

		var tn = this.getTagName();

		if( this.isEditable() === false ){
			$(dom).prop("disabled", true);
		}else{
			$(dom).prop("disabled", false);
		}

		if( $.inArray(tn, ["select","input","textarea"]) > -1 ){
			this.initEditor(this.getDataValue());
			return;
		}else{
			$(dom).empty().text(d);
		}
	};
	this.rebind = function(data){
		this.options.datacontext = data || this.options.datacontext;
		this.on();
		if( this.options.editor ){
			this.options.editor.set("value",this.getDataValue());
		}
	};
	this.setEditorMaxlengthStatus = function(){
		if( !this.options.editor ) return;
		var dom = $(this.options.editor.domNode).parent();
		if( $(dom).find(".editorstatus").length === 0 ){
			$(dom).append("<span class='editorstatus'></span>");
		}
		var len = this.getEditData().length, maxlen = $(this.dom).attr("maxlength");

		var html = "using <span class='current'>" + len + "</span> of <span class='total'>" + maxlen + "</span> characters";
		if( len > maxlen ){
			$(dom).addClass("invalid");
		}else{
			$(dom).removeClass("invalid");
		}
		$(dom).find(".editorstatus").empty().append(html);
	};
	this.initEditor = function(val){
		if( this.options.editor ){
			this.options.editor.destroyRecursive(false);
			this.options.editor = null;
		}
		var tn = this.getTagName();
		var dtype = $(this.dom).attr("type") || "text";
		switch(tn + ":" + dtype){
			case "input:url":
				this.options.editor = new dijit.form.ValidationTextBox({
					value: val,
					placeHolder: $(this.dom).attr("placeholder"),
					required: this.options.required,
					invalidMessage: "Must be a valid URL",
					onChange : (function(self){
						return function(v){
							self.onValueChange(v);
						};
					})(this)
				}, $(this.dom)[0]);
				this.options.editor.validator = function(value,constraints){
					return appdb.contextualization.utils.isValidUrl(value);
				};
				break;
			case "input:text":
				this.options.editor = new dijit.form.ValidationTextBox({
					value: val,
					placeHolder: $(this.dom).attr("placeholder"),
					required: this.options.required,
					onKeyUp:(function(self){
						return function(v){
							self.onValueChange(v);
						};
					})(this),
					onChange : (function(self){
						return function(v){
							self.onValueChange(v);
						};
					})(this)
				}, $(this.dom)[0]);
				break;
			case "select:select-one":
			case "select:text":
				var options = $.map(this.getDataSource(), function(e){
					return { label: (e.val)?e.val():e.name, value: e.id };
				});
				if( !val ){
					var v = this.getDataSource() || [];
					v = $.isArray(v)?v:[v];
					if( v.length > 0 ){
						val = v[0];
						val = val.id;
					}
				}
				this.options.editor = new dijit.form.Select({
					options: options,
					value: val,
					required: this.options.required,
					onChange: (function(self){
						return function(v){
							self.onValueChange();
						};
					})(this)
				}, $(this.dom)[0]);
				break;
			case "textarea:textarea":
				this.options.editor = new appdb.contextualization.ui.helpers.TextAreaEditor($(this.dom),this);
				break;
		}

		var maxlen = $(this.dom).attr("maxlength")<<0;
		if( maxlen > 0 ){
			this.setEditorMaxlengthStatus();
			this.options.postValueChange.push(this.setEditorMaxlengthStatus);
		}
		if( this.options.editor && this.options.editor.focusNode && $(this.options.editor.focusNode).length > 0 ){
			if( this.dom && $(this.dom).length > 0  && $(this.dom).is(":visible") ){
				$(this.options.editor.focusNode).focus();
			}
		}
		setTimeout((function(self){
			return function(){
				if( self.options.editor && self.options.editor.focus ){
					try{
						self.options.editor.focus();
					}catch(e){
						//do nothing. IE8 stuff
					}
				}
			};
		})(this), 1);
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.editable = ($(this.dom).data("editable") === true)?true:false;
		if( $(this.dom).length > 0 ){
			this.options.tagname = $.trim($(this.dom)[0].tagName).toLowerCase() || "div";
		}
		if( $(this.dom).length > 0 ){
			var attrs = {};
			$(this.dom[0].attributes).each(function() { attrs[this.nodeName] = this.nodeValue; });
			this.options.props = $.extend(attrs,this.options.props);
		}
		if( this.options.datahandler === null && this.options.props["type"] ){
			this.options.datahandler = appdb.FindNS("appdb.contextualization.ui.helpers.viewers." + this.options.props["type"]);
		}
		if( $.trim(this.options.props.source) !== "" ){
			this.options.datasource = appdb.FindNS(this.options.props.source);
		}
		this.options.required = $(this.dom).attr("required");
	};
	this._init();

});
appdb.contextualization.ui.views.DataBindable = appdb.ExtendClass(appdb.View, "appdb.contextualization.ui.views.DataBindable", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		items: [],
		isarray: (typeof o.isarray === "boolean")?o.isarray:true,
		originaldata: o.data,
		data: [],
		databinders: [],
		index: ((o.index<<0)>=0)?o.index<<0:-1,
		template: $.trim( o.template || $(o.container).data("usetemplate") )
	};
	this.onChange = function(typename,olddata,newdata, source){
		//override
	};
	this.sortData = function(){
		if( $.isArray(this.options.data) ){
			this.options.data.sort(function(a,b){
				var aa = $.trim((a)?a.id:"")<<0;
				var bb = $.trim((b)?b.id:"")<<0;
				if( aa > bb ) return -1;
				if( aa < bb ) return 1;
				return 0;
			});
		}
	};
	this.setData = function(data){
		if( typeof data === "undefined"){
			return;
		} else if(data === null) {
			this.options.data = this.options.isArray?[]:{};
			this.options.originaldata = this.options.isArray?[]:{};
		} else {
			if( this.options.isarray ){
				this.options.originaldata = data || [];
				this.options.originaldata = $.isArray(this.options.originaldata)?this.options.originaldata:[this.options.originaldata];
				this.options.data = appdb.contextualization.utils.copyObjectArray(this.options.originaldata);
				this.sortData(this.options.data);
			}else{
				this.options.originaldata = data || {};
				if( $.isArray(this.options.originaldata) ){
					this.options.originaldata = ( this.options.originaldata.length > 0  )?this.options.originaldata[0]:{};
				}
				this.options.data = $.extend(true,{},this.options.originaldata);
			}
		}
	};
	this.revertChanges = function(){
		this.setData(this.options.originaldata);
		this.reset();
		this.render();
	};
	this.reset = function(){
		this.subviews = this.subviews || {};
		for(var i in this.subviews){
			if( this.subviews.hasOwnProperty(i) === false || !this.subviews[i]) continue;
			if( typeof this.subviews[i].reset === "function" ){
				this.subviews[i].reset();
			}
			if( typeof this.subviews[i].unsubscribeAll === "function" ){
				this.subviews[i].unsubscribeAll();
			}
			this.subviews[i] = null;
		}
		this.subviews = {};
		if( this.options.items ){
			$.each(this.options.items, function(i,e){
				if( e.unsubscribeAll ){
					e.unsubscribeAll();
				}
				if( e.reset ) {
					e.reset();
				}
				e = null;
			});
			this.options.items = [];
		}

		this._initContainer();
		this._initViews();
	};
	this.dataBind = function(data, dom){
		dom = dom || $(this.dom);
		data = data || this.options.data || {};
		$(dom).find("[data-path]").each((function(self){
			return function(i,e){
				if($(e).data("usetemplate") ) return;
				var db = new appdb.contextualization.ui.DataBinder({container: e, parent: self, datacontext: data, dataid: data.id});
				self.options.databinders.push(db);
				db.on();
			};
		})(this));
		appdb.contextualization.ui.helpers.executeMacros(dom,data);
	};
	this.postRender = function(){

	};
	this.render = function(data){
		this.setData(data);
		this.dataBind();
		this.postRender();
	};
	this._initContainer = function(){
		if( this.options.template ){
			var tem = appdb.contextualization.manager.getTemplate(this.options.template);
			if( $(tem).length !== 0 ){
				$(this.dom).empty().append( $(tem).html() );
			}
		}
	};
	this._initViews = function(){

	};
	this._setDomState = function(newstate, dom){
		dom = dom || this.dom;
		if( $(dom).length === 0 ) return;
		var states = $.grep($(dom)[0].className.split(" "), function(v, i){
			return v.indexOf('state-') === 0;
		}).join();
		$(dom).removeClass(states).addClass(newstate);
	};
	this.setUIState = function(state,dom,payload){
		var el = $(dom || this.dom);
		if( el.length > 0 ){
			state = $.trim(state).toLowerCase();
			var newstate = (state && state !== "false")?"state-"+state:"";
			this.options.currentState = state;
			this._setDomState(newstate);
		}
		var p = this.getClosestParent("appdb.contextualization.ui.views.Contextualization");
		if( p && p.dom){
			this._setDomState(newstate, p.dom);
		}
		this.onUIState(payload);
	};
	this.onUIState = function(payload){
		this.renderLoading(false);
		switch($.trim(this.options.currentState)){
			case "saving":
				this.renderLoading(true, payload || "Saving pair");
				break;
			case "removing":
				this.renderLoading(true, payload || "Removing pair");
				break;
			case "updating":
				this.renderLoading(true,payload || "Updating virtual appliance");
				break;
			case "updatingurl":
				this.renderLoading(true,payload || "Updating URL information");
				break;
		}
	};
	this.renderLoading = function(loading,text){
		loading = (typeof loading === "boolean")?loading:false;
		$(this.dom).children(".loader").remove();
		if( loading ){
			text = text || "loading";
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).append(loader);
		}
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.setData(o.data);
		this.reset();
	};
	this._init();
});
appdb.contextualization.ui.views.ContextScriptVapplianceImageItem = appdb.ExtendClass(appdb.contextualization.ui.views.DataBindable,"appdb.contextualization.ui.views.ContextScriptVapplianceImageImageList", function(o){

});
appdb.contextualization.ui.views.ContextScriptVapplianceImageList = appdb.ExtendClass(appdb.contextualization.ui.views.DataBindable, "appdb.contextualization.ui.views.ContextScriptVapplianceImageList", function(o){
	this.addItem = function(data){
		this.options.items = this.options.items || [];
		this.options.items = $.isArray(this.options.items)?this.options.items:[this.options.items];
		var dom = $("<div class='image-item'></div>");
		var item = new appdb.contextualization.ui.views.ContextScriptVapplianceImageItem({
			container: dom,
			parent: this,
			isarray: false,
			data: data,
			template: "contextscript-image-item"
		});
		this.options.items.push(item);
		item.render(data);
		return dom;
	};
	this.render = function(data){
		this.reset();
		this.setData(data);
		this.dataBind();
		$.each(this.options.data, (function(self){
			return function(i,e){
				var div = self.addItem(e);
				if( div !== null ){
					$(self.dom).append(div);
				}
			};
		})(this));
	};
	this._initViews = function(){
		this.subviews = {};

		this.subviews.imageitem = appdb.contextualization.ui.views.ContextScriptVapplianceImageList({
			container: $(this.dom).find(".images"),
			parent: this,
			data: this.options.data.image
		});
	};
});
appdb.contextualization.ui.views.ContextScriptVapplianceItem = appdb.ExtendClass(appdb.contextualization.ui.views.DataBindable, "appdb.contextualization.ui.views.ContextScriptVapplianceItem", function(o){
	this.onRemove = function(){
		this.publish({event: "remove", value: this});
	};
	this.postRender = function(){
		$(this.dom).find(".vappliancelink").on("click", function(ev){
				ev.preventDefault();
				var par = $(this).closest(".vappliance-item");
				var imgs = $(par).children(".images");
				if($(par).hasClass("toggled")){
					$(imgs).slideUp(function(){
						par.removeClass("toggled");
					});
				}else{
					$(par).toggleClass("toggled");
					$(imgs).slideDown(function(){
						par.addClass("toggled");
					});
				}
				return false;
		});
		$(this.dom).find(".action.removecurrentvappliance").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.onRemove();
				return false;
			};
		})(this));

		var versionlink = this.getVapplianceLink();
		if( versionlink ){
			$(this.dom).find(".images .footer > a").removeClass("hidden").attr("href",versionlink);
		}else{
			$(this.dom).find(".images .footer > a").addClass("hidden");
		}

		$(this.dom).removeClass("outdated expired deleted moderated");
		if( this.renderDeleted() ) return;
		if( this.renderModerated() ) return;
		this.renderOutdated();
		this.renderExpired();
	};
	this.getVapplianceLink = function(){
		var link;
		var versions = this.getImageVersions();
		if( versions.length > 0 ){
			link = appdb.config.endpoint.base + "store/vappliance/" + this.options.data.cname + "/vaversion/";
			if( versions[0].archived ){
				link += "previous/" + versions[0].id;
			}else{
				link += "latest";
			}
		}
		return link;
	};
	this.getImageVersions = function(){
		var images = this.options.data.image || [];
		images = $.isArray(images)?images:[images];
		var uniq = {};
		$.each(images, function(i,e){
			if( !uniq[$.trim(e.versionid)] ){
				uniq[$.trim(e.versionid)] = {
					id: e.versionid,
					archived: ($.trim(e.archived) === "true")?true:false,
					enabled: ($.trim(e.enabled) === "true")?true:false
				};
			}
		});
		var res = [];
		for(var i in uniq){
			if( uniq.hasOwnProperty(i) === false ) continue;
			res.push(uniq[i]);
		}
		//get by archived in case of outdated version
		//so the user can select the first archived
		//and decide if it needs updating
		res.sort(function(a,b){
			var aa = (a.archived===true)?10:5;
			var bb = (b.archived===true)?10:5;
			if( aa > bb ) return 1;
			if( aa < bb ) return -1;
			return 0;
		});
		return res;
	};
	this.renderOutdated = function(){
		var outdated = this.getOutdatedImages();
		if( outdated.length > 0 ){
			$(this.dom).addClass("outdated");
			var latest = this.getLatestVersion();
			$(this.dom).find(".outdatedpanel a.newversionlink").attr("href",appdb.config.endpoint.base + "store/vappliance/"+this.options.data.cname+"/vaversion/latest" ).text(latest.version).off("click").on("click", function(ev){
				ev.stopPropagation();
			});
			$(this.dom).find("button.updatecurrentvappliance").off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					ev.stopPropagation();
					self.updateVersion();
					return false;
				};
			})(this));
			return true;
		}else{
			$(this.dom).removeClass("outdated");
			return false;
		}

	};
	this.renderExpired = function(){
		var latest = this.getLatestVersion();
		var current = (this.options.data || {});
		if( latest && $.trim(latest.isexpired) === "true" || $.trim(current.isexpired) === "true"){
			$(this.dom).addClass("expired");
			var expired = $("<div class='expiredvappliance'>expired</div>");
			$(this.dom).find("[data-type='vappliancelink']").append(expired).append($(this.dom).find(".expiredpanel"));
			return true;
		}else{
			$(this.dom).removeClass("expired");
			return false;
		}
	};
	this.renderDeleted = function(){
		if( this.options.data && $.trim(this.options.data.deleted) === "true" ){
			$(this.dom).addClass("deleted");
			var deleted = $("<div class='deletedvappliance'>deleted</div>");
			$(this.dom).find("[data-type='vappliancelink']").append(deleted).append($(this.dom).find(".deletedpanel"));
			return true;
		}else{
			$(this.dom).removeClass("deleted");
			return false;
		}
	};
	this.renderModerated = function(){
		if( this.options.data && $.trim(this.options.data.moderated) === "true" ){
			$(this.dom).addClass("moderated");
			var moderated = $("<div class='moderatedvappliance'>moderated</div>");
			$(this.dom).find("[data-type='vappliancelink']").append(moderated).append($(this.dom).find(".moderatedpanel"));
			return true;
		}else{
			$(this.dom).removeClass("moderated");
			return false;
		}
	};
	this.getLatestVersion = function(){
		var latest = this.options.data.latestversion || {};
		if( latest && (latest.id<<0) > 0 ){
			return latest;
		}
		return null;
	};
	this.getOutdatedImages = function(){
		var data = this.options.data.image;
		var latest = this.getLatestVersion();
		var outdated = [];
		if( latest ){
			latest.id = $.trim(latest.id);
			data = data || [];
			data = $.isArray(data)?data:[data];
			outdated = $.grep(data, function(e){
				return latest.id !== $.trim(e.versionid);
			});
		}
		return outdated;

	};
	this.updateVersion = function(){
		var p =  this.getClosestParent("appdb.contextualization.ui.views.ContextScriptItem");
		if( p ){
			p.updateVersion(this);
		}
	};
	this._initViews = function(){
		this.subviews = {};

		this.subviews.imagelist = new appdb.contextualization.ui.views.ContextScriptVapplianceImageList({
			container: $(this.dom).find(".images > .content"),
			parent: this,
			data: this.options.data.image
		});
	};
	this.render = function(data){
		this.reset();
		this.setData(data);
		this.dataBind();
		this.subviews.imagelist.render();
		this.postRender();
	};
});
appdb.contextualization.ui.views.ContextScriptVapplianceList = appdb.ExtendClass(appdb.contextualization.ui.views.DataBindable, "appdb.contextualization.ui.views.ContextScriptVapplianceList", function(o){
	this.getData = function(){
		return this.options.data;
	};
	this.renderAddDescription = function(enable){
		enable = (typeof enable === "boolean")?enable:true;
		$(this.dom).find(".addvappliance.description").css("visibility","hidden");
		if( enable ){
			$(this.dom).find(".addvappliance.description").css("visibility",null);
		}
	};
	this.renderLoadingItem = function(enable, text){
		enable = (typeof enable === "boolean")?enable:false;
		text = $.trim(text) || "...Loading data";
		$(this.dom).find(".list .vappliance-item.loading").remove();
		if(enable){
			$(this.dom).find(".list").append("<div class='vappliance-item loading'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>" + text + "</span></div>");
		}

	};
	this.isValid = function(){
		return this.options.items.length > 0 ;
	};
	this.onChange = function(){
		var p =  this.getClosestParent("appdb.contextualization.ui.views.ContextScriptItem");
		if( p ){
			p.onChange("application",this.options.originaldata, this.options.data);
		}
	};
	this.remove = function(index){
		index = index << 0;
		if( index >= 0 && this.options.data.length > index ){
			this.options.data.splice(index,1);
			$(this.options.items[index].dom).remove();
			this.options.items[index].reset();
			this.options.items[index] = null;
			this.options.items.splice(index,1);
		}
		this.onChange();
		this.render();
	};
	this.addVapplianceImages = function(data){
		this.renderLoadingItem(true,"Loading " + data.name + " information");
		this.renderAddDescription(false);
		data = data || {};
		data.image = data.image || [];
		data.image = $.isArray(data.image)?data.image:[data.image];
		var selector = new appdb.contextualization.utils.GetVapplianceImages();
		selector.subscribe({event: "select", callback: function(v){
			v = v || [];
			v = $.isArray(v)?v:[v];
			data.image = data.image.concat(v);
			selector.reset();
			setTimeout(function(){
				selector = null;
			},1);
			this.onAddVappliance(data);
		}, caller: this }).load(data.id);
	};
	this.onAddVappliance= function(data){
		this.renderLoadingItem(false);
		this.renderAddDescription(true);
		data = data || {};
		data.image = data.image || [];
		data.image = $.isArray(data.image)?data.image:[data.image];
		this.options.data.push(data);
		this.reset();
		this.onChange();
		this.render();
	};
	this.addvappliance = function(){
		var selector = new appdb.contextualization.ui.views.SelectVirtualAppliance();
		selector.subscribe({event:"select", callback: function(v){
			this.addVapplianceImages(v);
			selector.hide();
			setTimeout(function(){
				selector = null;
			},1);
		},caller: this});
		selector.show();
	};
	this.addItem = function(data){
		var dom = $("<div class='vappliance-item'></div>");
		var item = new appdb.contextualization.ui.views.ContextScriptVapplianceItem({
			container: $(dom),
			parent: this,
			isarray: false,
			data: data || {},
			template: "contextscipt-vappliance-list-item",
			index: this.options.items.length
		});
		item.subscribe({event: "remove", callback: function(v){
				this.remove(v.options.index);
		}, caller: this});
		this.options.items.push(item);
		item.render(data);
		return dom;
	};
	this.render = function(data){
		this.reset();
		this.setData(data);
		var list = $(this.dom).find(".list");
		$(list).empty();
		$.each(this.options.data, (function(self){
			return function(i,e){
				var div = self.addItem(e);
				if( div !== null ){
					$(list).append(div);
				}
			};
		})(this));
		this.postRender();
	};
	this.postRender = function(){
		if( this.options.items.length > 0 ){
			$(this.dom).closest(".contextscript-item").addClass("hasvappliances");
		}else{
			$(this.dom).closest(".contextscript-item").removeClass("hasvappliances");
		}
		$(this.dom).find(".action.addvappliance").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.addvappliance();
				return false;
			};
		})(this));
	};
});

appdb.contextualization.ui.views.ContextScriptItem = appdb.ExtendClass(appdb.contextualization.ui.views.DataBindable, "appdb.contextualization.ui.views.ContextScriptItem", function(o){
	this.isValid = function(isvalid){

	};
	this.render = function(data){
		this.reset();
		this.setData(data);
		this.dataBind();
		this.postRender();
		this.validate();
	};
	this.renderValidationErrors = function(errors){
		if( errors !== true ){
			appdb.debug(errors);
		}
	};
	this.validate = function(){
		var valid = this.isValid();
		if( valid === true ){
			$(this.dom).removeClass("invalid").find(".action.save").prop("disabled", false);
		}else{
			$(this.dom).addClass("invalid").find(".action.save").prop("disabled", true);
		}
		this.renderValidationErrors(valid);
	};
	this.isValid = function(){
		var d = this.options.data;
		var errors = [];
		if( !d.application || d.application.length === 0 ){
			errors.push("No virtual appliance is associated with this contextualization script");
		}
		if( $.trim(d.title).length > 150 ){
			errors.push("Title must not exceed 150 characters");
		}
		if( $.trim(d.description).length > 5000 ){
			errors.push("Notes must not exceed 5000 characters");
		}
		if( $.trim(d.url).length === 0 ){
			errors.push("Location is mandatory");
		}
		if( appdb.contextualization.utils.isValidUrl(d.url) === false){
			errors.push("Location must be a valid URL");
		}
		return ( errors.length === 0 )?true:errors;
	};
	this.onChange = function(path,olddata, newdata, source){
		switch(path){
			case "application":
				this.options.data.application = newdata;
				break;
			default:
				appdb.FindData(this.options.data,path,newdata);
				break;
		}
		this.validate();
	};
	this.getVappliances = function(){
		var apps = [], d = this.options.data || {};
		if( d ){
			apps = d.application || [];
			apps = $.isArray(apps)?apps:[apps];
		}
		return apps;
	};
	this._initActions = function(){
		var toolbar = $(this.dom).find(".toolbar:first");
		$.each(["edit","save","cancel","remove"], (function(self){
			return function(i,e){
				$(toolbar).find(".action." + e).off("click").on("click", function(ev){
					ev.preventDefault();
					self[e]();
					return false;
				});
			};
		})(this));
		var apps = this.getVappliances();
		if( apps.length > 0 ){
			$(this.dom).addClass("hasvappliances");
		}else{
			$(this.dom).removeClass("hasvappliances");
		}
		//Handle url information check
		$(this.dom).find(".toolbar > .checkurl").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.checkUrl();
				return false;
			};
		})(this));
	};
	this.getData = function(){
		return this.options.data;
	};
	this.isNewItem = function(){
		return ( $.isEmptyObject(this.options.data) || (this.options.data.id << 0)<=0 );
	};
	this.edit = function(){
		$(this.dom).addClass("editmode").removeClass("hasmessage").find(".postsavemessage").remove();
		appdb.contextualization.manager.trackChanges(this.getId(), true);

		this.initContextScriptEditor();
	};
	this.initContextScriptEditor = function(){
		if( this.options.contextscripteditor ){
			this.options.contextscripteditor.unsubscribeAll();
			this.options.contextscripteditor.reset();
			this.options.contextscripteditor = null;
		}
		this.options.contextscripteditor = appdb.views.ui.getEditor($(this.dom).find(".appdb-ui.script-location"), { parent: this  });
		if(this.options.contextscripteditor){
			this.options.contextscripteditor.options.useformats = false;
			this.options.contextscripteditor.options.autoclose = true;
			this.options.contextscripteditor.subscribe({event: 'changed', callback: function(v){
				this.options.data.url = v.url;
				this.validate();
			}, caller: this });
			this.options.contextscripteditor.on(this.getData());
		}
	};
	this.getId = function(){
		if( this.isNewItem() ){
			return "index" + this.options.index;
		} else {
			return this.options.data.id;
		}
	};
	this.renderUrlValidator = function(dom){
		if( this.options.urlvalidator ){
			return;
		}
		this.options.urlvalidator = new appdb.views.UrlValidator({
			container: $(this.dom).find(".location .urlvalidator"),
			parent: this,
			url: $.trim(this.options.data.url),
			returnMime: "text",
			ongetvalue: (function(self){
				return function(){
					return $.trim(self.options.data.url);
				};
			})(this)
		});
		this.options.urlvalidator.subscribe({event: "validation", callback: function(v){
		}, caller: this});
	};
	this.requestUpdateVersion = function(item){
		this.setUIState("updating");
		var contextualization = this.getClosestParent("appdb.contextualization.components.Contextualization");
		if( contextualization !== null ){
			contextualization.updateContextScriptImages([item.options.data.id], this.getData(), (function(self){
				return function(data){
					self.onSaveSuccess(data);
				};
			})(this), (function(self){
				return function(data){
					self.onSaveError(data);
				};
			})(this));
		}else{
			this.onSaveError({error: "Could not find contextualization component"});
		}
	};
	this.confirmUpdate = function(item){
		this.setUIState("tobeupdated");
		appdb.contextualization.ui.ShowVerifyDialog({
			title: "Contextualization Script Virtual Appliance version update",
			message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to update the referenced virtual appliance version of a contextualization script.<br/>Are you sure you want to proceed?</span>",
			onOk: (function(self,data){
				return function(){
					self.requestUpdateVersion(data);
				};
			})(this,item),
			onCancel: (function(self,data){
				return function(){
					self.setUIState(false);
				};
			})(this,item)
		});
	};
	this.updateVersion = function(item){
		this.confirmUpdate(item);
	};
	this.requestUpdateUrlInfo = function(item){
		$(this.dom).find(".downloadcontainer").removeClass("sticky hovered");
		this.setUIState("updatingurl");
		var contextualization = this.getClosestParent("appdb.contextualization.components.Contextualization");
		if( contextualization !== null ){
			contextualization.updateContextScriptUrlInfo(this.getData(), (function(self){
				return function(data){
					self.onUpdateUrlSuccess(data);
				};
			})(this), (function(self){
				return function(data){
					self.onUpdateUrlError(data);
				};
			})(this));
		}else{
			this.onUpdateUrlSuccess({error: "Could not find contextualization component"});
		}
	};
	this.onUpdateUrlSuccess = function(data){
		this.onSaveSuccess(data);
		$(this.dom).find(".downloadcontainer").addClass("sticky hovered");
	};
	this.onUpdateUrlError = function(data){
		this.onSaveError(data);
		$(this.dom).find(".downloadcontainer").addClass("sticky hovered");
	};
	this.onRemoveSuccess = function(data, item){
		$(this.dom).removeClass("editmode");
	};
	this.onRemoveError = function(data, item){
		this.revertChanges();
		$(this.dom).removeClass("editmode");
		this.setUIState(false);
	};
	this.onSaveSuccess = function(data){
		appdb.contextualization.manager.trackChanges(this.getId(), false);
		if( data.context ){
			data.context = data.context || {};
		}
		this.setData(data);
		$(this.dom).removeClass("editmode");
		this.render(data);
		this.setUIState();
		appdb.contextualization.ui.helpers.viewers.postSaveContextScriptMessage(this.dom);
	};
	this.onSaveError = function(data){
		this.setUIState(false);
	};
	this.save = function(){
		this.setUIState("saving");
		var contextualization = this.getClosestParent("appdb.contextualization.components.Contextualization");
		if( contextualization !== null ){
			contextualization.saveContextScript(this.getData(), (function(self){
				return function(data){
					self.onSaveSuccess(data);
				};
			})(this), (function(self){
				return function(data){
					self.onSaveError(data);
				};
			})(this));
		}else{
			this.onSaveError({error: "Could not find contextualization component"});
		}
	};
	this.cancel = function(){
		this.revertChanges();
		if( this.isNewItem() ){
			this.remove();
		}
		$(this.dom).removeClass("editmode").removeClass("hasmessage");
		appdb.contextualization.manager.trackChanges(this.getId(), false);
	};
	this.remove = function(){
		this.parent.removeItem(this);
	};
	this.checkUrl = function(){
		this.requestUpdateUrlInfo(this);
	};
	this.removevappliance = function(){
		var vappitem;
		if( vappitem ){
			this.options.data.application = [];
			vappitem.reset();
			vappitem.render();
			this.postRender();
		}
	};
	this.postRender = function(){
		var d = this.getData() || {};
		if( appdb.utils.isLocalDomainUrl(d.url) ){
			$(this.dom).find("a.pairurl").attr('href', appdb.config.endpoint.base+"store/swapp/"+this.options.data.relationid+"/script").off('click').on('click',function(ev){
				ev.stopPropagation();
				return true;
			});
			$(this.dom).find("a.pairurl").closest('.alert').removeClass('hidden');
			new dijit.Tooltip({
				connectId: [$(this.dom).find("img.info")[0]],
				label: $(this.dom).find("img.info").siblings('.info-message').html()
			});
		}
		if( appdb.contextualization.manager.canEdit() ){
			this._initActions();
		}
		if( this.subviews.vappliancelist ){
			this.subviews.vappliancelist.reset();
			this.subviews.vappliancelist = null;
		}
		this.subviews.vappliancelist = new appdb.contextualization.ui.views.ContextScriptVapplianceList({
			container: $(this.dom).find(".vappliances-list"),
			template: "contextscipt-vappliance-list",
			parent: this
		});
		this.subviews.vappliancelist.subscribe({event: "removevappliance", callback: function(v){

		}, caller: this});
		this.subviews.vappliancelist.render(this.options.data.application);
		if( appdb.contextualization.manager && appdb.contextualization.manager.canEdit && appdb.contextualization.manager.canEdit() === true ){
			$(this.dom).addClass("canedit");
			this.renderUrlValidator();
		}else{
			$(this.dom).removeClass("canedit");
		}
	};
	this._initViews = function(){
		this.subviews = {};
		this.subviews.vappliancelist = new appdb.contextualization.ui.views.ContextScriptVapplianceList({
			container: $(this.dom).find(".vappliances-list"),
			parent: this,
			template: "contextscipt-vappliance-list",
			data: (this.options.data || {}).application
		});
	};
});

appdb.contextualization.ui.views.ContextScriptList = appdb.ExtendClass(appdb.contextualization.ui.views.DataBindable, "appdb.contextualization.ui.views.ContextScriptList", function(o){
	this.addItem = function(data){
		this.options.items = this.options.items || [];
		this.options.items = $.isArray(this.options.items)?this.options.items:[this.options.items];
		var dom = $("<div class='contextscript-item'></div>");
		var item = new appdb.contextualization.ui.views.ContextScriptItem({
			container: dom,
			parent: this,
			isarray: false,
			data: data,
			index: this.options.items.length,
			template: "contextscript-item"
		});
		this.options.items.push(item);
		item.render(data);
		this.renderEmptyList();
		return dom;
	};

	this.render = function(data){
		this.reset();
		this.setData(data);
		var list = $(this.dom).find(".contextscript-list");
		$(list).empty();
		this.dataBind();
		$.each(this.options.data, (function(self){
			return function(i,e){
				var div = self.addItem(e);
				if( div !== null ){
					$(list).append(div);
				}
			};
		})(this));
		this.renderEmptyList();
		this.postRender();
	};
	this.postRender = function(){
		$(this.dom).find(".toolbar .action.new").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.addNew();
				return false;
			};
		})(this));
		$.each(this.options.items, (function(self){
			return function(i,e){
				e.subscribe({event: "removeitem", callback: function(v){
					this.renderEmptyList();
				}, caller:self});
				e.subscribe({event: "additem", callback: function(v){
					this.renderEmptyList();
				}, caller:self});
			};
		})(this));
	};
	this.renderEmptyList = function(){
		var templs = this.options.items;
		if( templs.length === 0 ){
			$(this.dom).addClass("isempty");
		}else{
			$(this.dom).removeClass("isempty");
		}
	};
	this.addNew = function(){
		var formats = appdb.model.StaticList.ContextFormats || [];
		var newformat = (formats.length > 0 )?$.extend(true,{},formats[0]):{ id: "1" };
		var dom = this.addItem({url:"",title:"",description:"",format: newformat, application:[]});
		$(this.dom).find(".contextscript-list").append(dom);
		var item = this.options.items[this.options.items.length-1];
		item.edit();
	};
	this.getDataIndexById = function(data){
		var index = -1;
		$.each(this.options.originaldata, function(i,e){
			if( index < 0 && e === data ){
				index = i;
			}
		});
		return index;
	};
	this.getItemIndex = function(item){
		var index = -1;
		$.each(this.options.items, function(i,e){
			if( index < 0 && e === item ){
				index = i;
			}
		});
		return index;
	};

	this.confirmRemoval = function(item){
		item.setUIState("toberemoved");
		appdb.contextualization.ui.ShowVerifyDialog({
			title: "Contextualization script / vAppliance pair removal",
			message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to remove a pair from the list.<br/>Are you sure you want to proceed?</span>",
			onOk: (function(self,data){
				return function(){
					self.requestRemoval(data);
				};
			})(this,item),
			onCancel: (function(self,data){
				return function(){
					data.setUIState(false);
				};
			})(this,item)
		});
	};
	this.removeItem = function(item){
		if( item.isNewItem() ){
			return this.doRemoveItem(item);
		}
		this.confirmRemoval(item);
	};
	this.onRemoveSuccess = function(data, item){
		item.onRemoveSuccess(data);
		this.doRemoveItem(item);
		item.setUIState(false);
	};
	this.onRemoveError = function(data, item){
		item.onRemoveError(data);
		item.setUIState(false);
	};
	this.requestRemoval = function(item){
		item.setUIState("removing");
		var contextualization = this.getClosestParent("appdb.contextualization.components.Contextualization");
		var data = item.getData();
		if( contextualization !== null ){
			contextualization.removeContextScript(data, (function(self,item){
				return function(data){
					self.onRemoveSuccess(data,item);
				};
			})(this,item), (function(self,item){
				return function(data){
					self.onRemoveError(data,item);
				};
			})(this,item));
		}else{
			this.onRemoveError({error: "Could not find contextualization component"});
		}
	};
	this.doRemoveItem = function(item){
		if( !item ) return;
		var i = item.options.index;

		if( i > -1 ){
			this.options.data.splice(i,1);
			item.reset();
			$(item.dom).remove();
			this.options.items.splice(i,1);
		}
		this.renderEmptyList();
	};
});
appdb.contextualization.ui.views.ContextualizationVersion = appdb.ExtendClass(appdb.contextualization.ui.views.DataBindable, "appdb.contextualization.ui.views.ContextualizationVersion", function(o){
	this.getData = function(){
		this.options.data = this.options.data || {};
		var res = { id: this.options.data.id, version: this.options.data.version };
		return res;
	};
	this.getId = function(){
		return "version_" + (this.options.data.id || "newcontext");
	};
	this.renderLoading = function(loading,text){
		loading = (typeof loading === "boolean")?loading:false;
		$(this.dom).find(".actions").children(".loader").remove();
		if( loading ){
			text = text || "loading";
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).append(loader);

		}
	};
	this.renderValidationErrors = function(errors){
		if( errors !== true ){
			appdb.debug(errors);
		}
	};
	this.renderActions = function(){
		$(this.dom).find(".action.edit").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.edit();
				return false;
			};
		})(this));
		$(this.dom).find(".action.save").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.save();
				return false;
			};
		})(this));
		$(this.dom).find(".action.cancel").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.cancel();
				return false;
			};
		})(this));
	};
	this.postRender = function(){
		this.renderActions();
	};
	this.render = function(data){
		this.reset();
		this.setData(data);
		this.dataBind();
		this.postRender();
		this.validate();
	};
	this.validate = function(){
		var valid = this.isValid();
		if( valid === true ){
			$(this.dom).removeClass("invalid").find(".action.save").prop("disabled", false);
		}else{
			$(this.dom).addClass("invalid").find(".action.save").prop("disabled", true);
		}
		this.renderValidationErrors(valid);
	};
	this.isValid = function(){
		var d = this.options.data;
		var errors = [];
		if( $.trim(d.version).length === 0 ){
			errors.push("Version value is required");
		}
		if( $.trim(d.version).length > 150 ){
			errors.push("Version must not exceed 150 characters");
		}
		return ( errors.length > 0 )?errors:true;
	};
	this.validate = function(){
		var valid = this.isValid();
		if( valid === true ){
			$(this.dom).removeClass("invalid").find(".action.save").prop("disabled", false);
		}else{
			$(this.dom).addClass("invalid").find(".action.save").prop("disabled", true);
		}
		this.renderValidationErrors(valid);
	};
	this.onChange = function(path,olddata, newdata, source){
		switch(path){
			case "version":
				this.options.data.version = newdata;
				break;
			default:
				appdb.FindData(this.options.data,path,newdata);
				break;
		}
		this.validate();
	};
	this.edit = function(){
		$(this.dom).addClass("editmode");
		$(this.dom).closest(".contextualization-version ").find(".contextscript-list .contextscript-item").removeClass("hasmessage").find(".postsavemessage .closemessage:first").trigger("click");
		this.validate();
		appdb.contextualization.manager.trackChanges(this.getId(), true);
	};
	this.save = function(){
		this.setUIState("saving",null,"saving version");
		var contextualization = this.getClosestParent("appdb.contextualization.components.Contextualization");
		if( contextualization !== null ){
			contextualization.saveContextualizationMetadata(this.getData(), (function(self){
				return function(data){
					self.onSaveSuccess(data);
				};
			})(this), (function(self){
				return function(data){
					self.onSaveError(data);
				};
			})(this));
		}else{
			this.onSaveError({error: "Could not find contextualization component"});
		}
	};
	this.onSaveSuccess = function(data){
		appdb.contextualization.manager.trackChanges(this.getId(), false);
		if( data.context ){
			data.context = data.context || {};
		}
		this.setData(data);
		$(this.dom).removeClass("editmode");
		this.render(data);
		this.setUIState();
		appdb.contextualization.manager.versionUpdated(true);
	};
	this.onSaveError = function(data){
		this.setUIState(false);
	};
	this.cancel = function(){
		$(this.dom).removeClass("editmode");
		this.revertChanges();
		appdb.contextualization.manager.trackChanges(this.getId(), false);
	};
});
appdb.contextualization.ui.views.ContextualizationDescription = appdb.ExtendClass(appdb.contextualization.ui.views.ContextualizationVersion, "appdb.contextualization.ui.views.ContextualizationDescription", function(o){
	this.getData = function(){
		this.options.data = this.options.data || {};
		var res = { id: this.options.data.id, description: this.options.data.description };
		return res;
	};
	this.getId = function(){
		return "description_" + (this.options.data.id || "newcontext");
	};
	this.isValid = function(){
		var d = this.options.data;
		var errors = [];
		if( $.trim(d.description).length > 5000 ){
			errors.push("Description must not exceed 5000 characters");
		}
		return ( errors.length > 0 )?errors:true;
	};
	this.validate = function(){
		var valid = this.isValid();
		if( valid === true ){
			$(this.dom).removeClass("invalid").find(".action.save").prop("disabled", false);
		}else{
			$(this.dom).addClass("invalid").find(".action.save").prop("disabled", true);
		}
		this.renderValidationErrors(valid);
	};
	this.onChange = function(path,olddata, newdata, source){
		switch(path){
			case "description":
				this.options.data.description = newdata;
				break;
			default:
				appdb.FindData(this.options.data,path,newdata);
				break;
		}
		this.validate();
	};
	this.edit = function(){
		$(this.dom).addClass("editmode");
		this.validate();
		appdb.contextualization.manager.trackChanges(this.getId(), true);
	};
	this.save = function(){
		this.setUIState("saving", null, "saving description");
		var contextualization = this.getClosestParent("appdb.contextualization.components.Contextualization");
		if( contextualization !== null ){
			contextualization.saveContextualizationMetadata(this.getData(), (function(self){
				return function(data){
					self.onSaveSuccess(data);
				};
			})(this), (function(self){
				return function(data){
					self.onSaveError(data);
				};
			})(this));
		}else{
			this.onSaveError({error: "Could not find contextualization component"});
		}
	};
	this.onSaveSuccess = function(data){
		appdb.contextualization.manager.trackChanges(this.getId(), false);
		if( data.context ){
			data.context = data.context || {};
		}
		this.setData(data);
		$(this.dom).removeClass("editmode");
		this.render(data);
		this.setUIState();
	};
	this.onSaveError = function(data){
		this.setUIState(false);
	};
	this.cancel = function(){
		$(this.dom).removeClass("editmode");
		this.revertChanges();
		appdb.contextualization.manager.trackChanges(this.getId(), false);
	};
	this.overflowUI = function(){
		var $content = $(this.dom).find(".value.hideonedit");
		if( $content.length > 0 ){
		var visibleHeight = $content[0].clientHeight;
		var actualHide = $content[0].scrollHeight - 1;
		if (actualHide > visibleHeight){
			return true;
		}}
		return false;
	};
	this.renderShowMore = function(){
		if( this.overflowUI() === false ) return;
		var showmore = $("<div class='show-more'>...read more</div>");
		$(showmore).off("click").on("click", function(ev){
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
		$(this.dom).find(".value.hideonedit").append(showmore);
	};
	this.postRender = function(){
		this.renderActions();
		if($(this.dom).find(".value.hideonedit .emptydatamessage").length === 0 ){
			var txt = $(this.dom).find(".value.hideonedit").text();
			txt = $.trim(txt).replace(/\</g,"&lt;").replace(/\>/g,"&gt;").replace(/\n/g,"<br/>");
			$(this.dom).find(".value.hideonedit").html(txt);
			this.renderShowMore();
		}
	};
});
appdb.contextualization.ui.views.Contextualization = appdb.ExtendClass(appdb.contextualization.ui.views.DataBindable, "appdb.contextualization.ui.views.Contextualization", function(o){
	this.renderActions = function(){

	};
	this.postRender = function(data){
		this.renderActions();
		this._initHelperActions();
	};
	this.render = function(data){
		this.reset();
		if( appdb.contextualization.manager.canEdit() ){
			$(this.dom).addClass("canedit");
		}else{
			$(this.dom).removeClass("canedit");
		}
		this.setData(data);
		this.dataBind();
		this.subviews.version.render(data);
		this.subviews.description.render(data);
		this.subviews.list.render(data.contextscript);
		this.postRender();
	};
	this._initViews = function(){
		this.subviews = {};
		this.subviews.list = new appdb.contextualization.ui.views.ContextScriptList({
			container: $(this.dom).find(".contextscript-list-container"),
			parent: this,
			data: this.options.data.contextscript,
			template: "contextscript-list"
		});
		this.subviews.description = new appdb.contextualization.ui.views.ContextualizationDescription({
			container: $(this.dom).find(".context-description-property"),
			parent: this,
			data: this.options.data,
			isarray: false,
			template: "contextualization-description"
		});
		this.subviews.version = new appdb.contextualization.ui.views.ContextualizationVersion({
			container: $(this.dom).find(".context-version-property"),
			parent: this,
			data: this.options.data,
			isarray: false,
			template: "contextualization-version"
		});
	};
	this._initHelperActions = function(){
		$(this.dom).find(".fieldvalue.popup").off("mouseover").on("mouseover", function(ev){
			var hadsticky = $(this).hasClass("sticky");
			$("body").find(".popup").removeClass("hovered").removeClass("sticky");
			$(this).addClass("hovered");
			if( hadsticky ){
				$(this).addClass("sticky");
			}
			ev.preventDefault();
			return false;
		}).off("click").on("click", function(ev){
			$("body").find(".popup.sticky").removeClass("sticky");
			$(this).toggleClass("sticky");
			ev.preventDefault();
			return false;
		});
		$(this.dom).off("mouseover").on("mouseover", function(ev){
			$(this).find(".popup").removeClass("hovered");
		});
		$("body").off("click").on("click", function(ev){
			$(this).find(".popup").removeClass("sticky").removeClass("hovered");
		});
	};
});

appdb.contextualization.components.Contextualization = appdb.ExtendClass(appdb.Component, "appdb.contextualization.components.Contextualization", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		updatedversion: false
	};
	this.updateDates = function(data){
		data = data || this.options.data;
		var lastupdatedon = (data && $.trim(data.lastupdatedon))?$.trim(data.lastupdatedon):"";
		if( lastupdatedon ){
			var dom = $(this.dom).find(".fieldvalue > .value[data-path='lastupdatedon']");
			appdb.contextualization.ui.helpers.macros.datetime(dom,lastupdatedon);
		}
	};
	this.retrieveNewContextScripts = function(data){
		data = data || {};
		data.contextscript = data.contextscript || [];
		data.contextscript = $.isArray(data.contextscript)?data.contextscript:[data.contextscript];
		this.options.data.contextscript = this.options.data.contextscript || [];
		this.options.data.contextscript = $.isArray(this.options.data.contextscript)?this.options.data.contextscript:[this.options.data.contextscript];
		var ids = $.map(this.options.data.contextscript, function(e){
			return $.trim(e.id);
		});
		var res = $.grep(data.contextscript, function(e){
			return ( $.inArray($.trim(e.id), ids) === -1 );
		});
		if( res.length > 0 ){
			$.each(res, (function(self){
				return function(i,e){
					self.options.data.contextscript.push(e);
				};
			})(this));
		}
		return res;
	};
	this.getDataItemIndex = function(id,data){
		var index = -1;
		id = $.trim(id);
		data = data || this.options.data;
		data.contextscript = data.contextscript || [];
		data.contextscript = $.isArray(data.contextscript)?data.contextscript:[data.contextscript];
		$.each(data.contextscript, function(i,e){
			if( index < 0 && $.trim(e.id) === id){
				index = i;
			}
		});
		return index;
	};
	this.removeContextSciptDataItem = function(id){
		var index = this.getDataItemIndex(id);
		if( index > -1 ){
			this.options.data.contextscript.splice(index,1);
		}
	};
	this.replaceContextScriptDataItem = function(id,data){
		id = $.trim(id);
		var res = {};
		var index = this.getDataItemIndex(id);
		var newindex = this.getDataItemIndex(id,data);
		if( index > -1 && newindex > -1){
			this.options.data.contextscript[index] = data.contextscript[newindex];
			res = this.options.data.contextscript[index];
		}
		return res;
	};
	this.replaceContextScriptMetadata = function(id,data){
		data = data || {};
		this.options.data = this.options.data || {};

		if( typeof data.description !== "undefined" ){
			this.options.data.description = $.trim(data.description);
		}

		if( typeof data.version !== "undefined" ){
			this.options.data.version = $.trim(data.version);
		}
		return this.options.data;
	};
	this.getContextScriptXml = function(data){
		var mapper = new appdb.utils.EntityEditMapper.Contextualization();
		var parentdata = this.getData();
		mapper.UpdateEntity({id: parentdata.id, contextscript: [data] });
		var xml = appdb.utils.EntitySerializer.excludeElements([]).toXml(mapper.entity);
		return xml;
	};
	this.getContextScriptMetadataXml = function(data){
		var mapper = new appdb.utils.EntityEditMapper.Contextualization();
		var parentdata = this.getData();
		mapper.UpdateEntity({id: parentdata.id, version: data.version, description: data.description });
		var xml = appdb.utils.EntitySerializer.excludeElements([]).toXml(mapper.entity);
		console.log(xml);
		return xml;
	};
	this.removeContextScript = function(data, onSuccess, onError){
		var _model = new appdb.model.Contextualization();
		data.id = (data.id << 0);
		_model.subscribe({event: "remove", callback: (function(data){
			return function(d){
				if( d && d.error ){

				}else{
					this.removeContextSciptDataItem(data.id);
					this.updateDates(data);
					onSuccess(d);
					this.publish({event: "change", value: this.options.data});
				}
			};
			}(data)),caller: this}).subscribe({event: "error", callback: function(d){
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot save contexualization script",
					"description": d.error
				});
				onError(d);
			}
		}, caller: this});
		_model.remove({"id": appdb.contextualization.manager.getApplication().id, "scriptid": data.id});
	};
	this.saveContextScript = function(data, onSuccess, onError){
		var _model = new appdb.model.Contextualization();
		data.id = (data.id << 0);
		var xml = this.getContextScriptXml(data);
		_model.subscribe({event: "update", callback:(function(data){
			return function(d){
				if( d && d.error ){

				}else{
					var newdata = this.replaceContextScriptDataItem(data.id,d.context || {});
					this.updateDates(data);
					onSuccess(newdata);
					this.publish({event: "change", value: this.options.data});
				}
			};
			})(data), caller: this}).subscribe({event: "insert", callback: (function(data){
			return function(d){
				if( d && d.error ){

				}else{
					var newdata = this.retrieveNewContextScripts( (d)?d.context:{} );
					if( $.isArray(newdata) ){
						if(newdata.length > 0 ){
							onSuccess(newdata[0]);
						}else{
							onSuccess({});
						}
					}else{
						onSuccess(newdata);
					}
					this.publish({event: "change", value: this.options.data});
				}
			};
		})(data), caller: this}).subscribe({event: "error", callback: function(d){
			if(d && d.error){
				var err = ($.trim(d.error).toLowerCase()!=="backend error")?d.error + "<br/>":"";
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot save contexualization script",
					"description": (err) + d.errordesc
				});
				onError(d);
			}
		}, caller: this});
		if( data.id > 0 ){//update
			_model.update({query: {id: appdb.contextualization.manager.getApplication().id, contextscriptid: data.id }, data: {data: encodeURIComponent(xml)}});
		} else { //insert
			_model.insert({query: {id: appdb.contextualization.manager.getApplication().id}, data: {data: xml}});
		}
	};
	this.updateContextScriptImages = function(appids, data, onSuccess, onError){
		var _model = new appdb.model.ContextualizationScript();
		data.id = (data.id << 0);
		var xml = this.getContextScriptXml(data);
		_model.subscribe({event: "update", callback:(function(data){
			return function(d){
				if( d && d.error ){

				}else{
					var newdata = this.replaceContextScriptDataItem(data.id,{contextscript: d.contextscript || {} });
					this.updateDates(data);
					onSuccess(newdata);
					this.publish({event: "change", value: this.options.data});
				}
			};
			})(data), caller: this}).subscribe({event: "error", callback: function(d){
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot save contexualization script",
					"description": d.error + "<br/>" + d.errordesc
				});
				onError(d);
			}
		}, caller: this});
		if( data.id > 0 ){//update
			_model.update({query: {id: appdb.contextualization.manager.getApplication().id, scriptid: data.id, act: "updateimages", ids: appids }, data: {data: ""/*encodeURIComponent(xml)*/}});
		} else { //insert
		}
	};
	this.updateContextScriptUrlInfo = function(data,onSuccess, onError){
		var _model = new appdb.model.ContextualizationScript();
		data.id = (data.id << 0);
		var xml = this.getContextScriptXml(data);
		_model.subscribe({event: "update", callback:(function(data){
			return function(d){
				if( d && d.error ){

				}else{
					var newdata = this.replaceContextScriptDataItem(data.id,{contextscript: d.contextscript || {} });
					this.updateDates(data);
					onSuccess(newdata);
					this.publish({event: "change", value: this.options.data});
				}
			};
			})(data), caller: this}).subscribe({event: "error", callback: function(d){
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot update location data",
					"description": d.error + "<br/>" + d.errordesc
				});
				onError(d);
			}
		}, caller: this});
		_model.update({query: {id: appdb.contextualization.manager.getApplication().id, scriptid: data.id, act: "updateurl"}, data: {data: ""}});
	};
	this.saveContextualizationMetadata = function(data, onSuccess, onError){
		var _model = new appdb.model.ContextualizationMetadata();
		data.id = (data.id<< 0 );
		var xml = this.getContextScriptMetadataXml(data);
		_model.subscribe({event: "update", callback:(function(data){
			return function(d){
				if( d && d.error ){

				}else{
					d.context = d.context || {};
					var newdata = this.replaceContextScriptMetadata(data.id,{ version: d.context.version, description:d.context.description });
					this.updateDates(data);
					onSuccess(newdata);
					this.publish({event: "change", value: this.options.data});
				}
			};
			})(data), caller: this}).subscribe({event: "error", callback: function(d){
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot update data",
					"description": d.error + "<br/>" + d.errordesc
				});
				onError(d);
			}
		}, caller: this}).subscribe({event: "error", callback: function(d){
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot update data",
					"description": d.error + "<br/>" + d.errordesc
				});
				onError(d);
			}
		}, caller: this});
		_model.update({query: {id: appdb.contextualization.manager.getApplication().id}, data: {data: encodeURIComponent(xml)}});
	};
	this.isEditMode = function(){
		return this.options.permissions.editmode;
	};
	this.versionUpdated = function(val){
		if( typeof val === "boolean" ){
			this.options.updateversion = val;
		}
		return this.options.updateversion;
	};
	this.hasChanges = function(){
		return false;
	};
	this.getData = function(){
		return this.options.data;
	};
	this.render = function(data){
		data = data || this.options.data || {};
		this.options.data = data;

		$(this.dom).removeClass("isempty");
		if( $.isEmptyObject(this.options.data) ){
			$(this.dom).addClass("isempty");
			return;
		}
		this.views.contextualization.render(this.options.data);
		if( appdb.contextualization.manager.canEdit() === false ){
			$(this.dom).find(".editoritem").remove();
		}
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.views.contextualization = new appdb.contextualization.ui.views.Contextualization({
			container: $(this.dom).children(".contextualization-version"),
			parent: this,
			isarray: false,
			data: this.options.data[0],
			template: "contextualization"
		});
		this.options.data = $.isArray(this.options.data)?this.options.data:[this.options.data];
		this.options.updateversion = false;
	};
	this._init();
	if( this !== window ){
		appdb.contextualization.components.Contextualization.Current = this;
	}
});
appdb.contextualization.components.ContextualizationManager = appdb.ExtendClass(appdb.Component, "appdb.contextualization.components.ContextualizationManager", function(o){
	this.options = {
		container: $(o.container),
		application: o.application || null,
		templateroot: $(".contextualization-templates"),
		templates: {},
		data: {},
		permissions:{
			canedit: ( (typeof o.canedit === "boolean")?o.canedit:false )
		},
		tracker: {}
	};
	this.getApplication = function(){
		return this.options.application || {};
	};
	this.updateContextScript = function(item){
		var data = item.getData();

	};
	this.loadTemplates = function(){
		$(this.options.templateroot).find("[data-template]").each((function(self){
			return function(i,e){
				var name = $(this).data("template");
				if( $.trim(name) !==  "" && !self.options.templates[name] ){
					self.options.templates[name] = $(this).clone();
				}
			};
		})(this));
	};
	this.getTemplate = function(name){
		return $(this.options.templates[name]).clone(true);
	};
	this.canEdit = function(){
		return this.options.permissions.canedit;
	};
	this.getComponent = function(){
		return this.views.contextualization;
	};
	this.renderLoading = function(enable){
		enable = (typeof enable === "boolean")?enable:true;
		$(this.dom).removeClass("isloading");
		if( enable ){
			$(this.dom).addClass("isloading");
		}
	};
	this.isEditMode = function(){
		return this.options.editMode;
	};
	this.render = function(data){
		this.options.data = data || this.options.data || {};
		if( this.canEdit() && !this.options.data.context ){
			this.options.data.context = {contextscript: []};
		}
		this.views.contextualization.render(this.options.data.context);
	};
	this.versionUpdated = function(val){
		if( this.views.contextualization ){
			return this.views.contextualization.versionUpdated(val);
		}
		return true;
	};
	this.isTrackingChanges = function(){
		var istracking = false;
		for(var i in this.options.tracker){
			if( this.options.tracker.hasOwnProperty(i) && this.options.tracker[i] === true){
				istracking = true;
				break;
			}
		}
		return istracking;
	};
	this.trackChanges = function(id, track){
		id = $.trim(id);
		if( id === "" ){
			return false;
		}
		var wastracking = this.isTrackingChanges();
		if( typeof track === "undefined"){
			return (this.options.tracker[id])?true:false;
		}else{
			track = (typeof track === "boolean" )?track:false;
			if( track === true ){
				this.options.tracker[id] = true;
			}else{
				if( this.options.tracker[id] ){
					delete this.options.tracker[id];
				}
			}
		}
		var istracking = this.isTrackingChanges();
		if( istracking !== wastracking ){
			if( wastracking ){
				appdb.utils.DataWatcher.Registry.deactivate("swappliance");
			} else {
				appdb.utils.DataWatcher.Registry.activate("swappliance");
			}
		}
	};
	this.load = function(){
		this._model = new appdb.contextualization.model.Contextualization();
		this._model.subscribe({event: "beforeselect", callback: function(v){
				this.renderLoading(true);
		}, caller: this}).subscribe({event: "select", callback: function(v){
			this.renderLoading(false);
			console.time("context");
			this.render(v);
			console.timeEnd("context");
			this.publish({event: "load", value: v});
		}, caller: this}).subscribe({event: "beforeupdate", callback: function(v){

		}, caller: this}).subscribe({event: "update", callback: function(v){

		}, caller: this}).subscribe({event: "beforedelete", callback: function(v){

		}, caller: this}).subscribe({event: "delete", callback: function(v){

		}, caller: this});
		this._model.get({id: this.options.application.id});
	};
	this._initData = function(){
		if( this.options.application && this.options.application.application ){
			this.options.application = this.options.application.application;
			if( $.isArray(this.options.application) ){
				if( this.options.application.length > 0 ){
					this.options.application = this.options.application[0];
				}else{
					this.options.application = null;
				}
			}
		}
	};
	this._initContainer = function(){

	};
	this._initTemplates = function(){
		this.loadTemplates();
	};
	this._initViews = function(){
		this.views.contextualization = new appdb.contextualization.components.Contextualization({
			container: $(this.dom).find(".contextualization-container"),
			parent: this
		});
		this.views.contextualization.subscribe({event:"change", callback: function(data){
				this.publish({event: "change", value: {context: data}});
		}, caller: this});
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this._model = new appdb.contextualization.model.Contextualization();
		this._initData();
		this._initTemplates();
		this._initViews();
	};
	if( this !== window ){
		if( appdb.contextualization.manager ){
			appdb.contextualization.manager.unsubscribeAll();
			appdb.contextualization.manager.destroy();
			appdb.contextualization.manager = null;
		}
		appdb.contextualization.manager = this;
	}
	this._init();
});

appdb.components.FileUploader = appdb.ExtendClass(appdb.Component, "appdb.components.FileUploader", function(o){
	this.options = {
		parent: o.parent || null,
		container: o.container || null,
		guid: o.guid || appdb.utils.guid(),
		data: o.data || {},
		url: $.trim(o.url),
		uploader: null
	};

	this.reset = function(){
		this.destroy();
	};

	this.destroy = function(){
		$(this.dom).find("#" + this.getUploaderDomId()).remove();
		if( this.options.uploader ) {
			this.options.uploader.destroy();
		}
	};

	this.hide = function(dohide) {
		dohide = (typeof dohide === 'boolean')?dohide:true;
		if( this.options.uploader ){
			setTimeout( (function(id, dohide){
				return function(){
					var display = '';
					
					if( dohide ) {
						display = 'none';
					}
					
					if( $("body").find('#' + id + "_html5_container").length > 0 ) {
						$("body").find('#' + id + "_html5_container")[0].style = display;
					}
					
					if( $("body").find('#' + id + "_flash_container").length > 0 ) {
						$("body").find('#' + id + "_flash_container")[0].style.display = display;
						$("body").find('#' + id + "_flash_container")[0].style.top = "";	
					}
					
					if( $("body").find('#' + id + "_silverlight_container").length > 0 ) {
						$("body").find('#' + id + "_silverlight_container")[0].style.display = display;
						$("body").find('#' + id + "_silverlight_container")[0].style.top = "";	
					}
				};
			})(this.options.uploader.id,dohide),10);			
		}
	};

	this.getUploaderDomId = function(){
		return "uploader_file_" + this.options.guid;
	};
	this.getSelectedFiles = function(){
		return (this.options.uploader)?this.options.uploader.files:[];
	};
	this.getInputElement = function(){
		var id = "uploader_file_" + this.options.guid;
		var el = $(this.dom).find("input#" + id);
		if( el.length === 0 ) {
			el = $("<input type='file' id='" + id + "' name='" + id + "' class='upload_handler' />");
			$(this.getPseudoElement()).find('input').remove();
			$(this.getPseudoElement()).append(el);
		}

		return el[0];
	};
	this.getPseudoElement = function(){
		var el = $(this.dom).find(".pseudo_uploader");
		if( el.length === 0 ) {
			return null;
		}

		return el[0];
	};
	this.setParam = function(name,val){
		this.options.uploader.settings.multipart_params[name] = val;
	};
	this.renderInputElement = function(){
		var pseudo = this.getPseudoElement();
		var input = this.getInputElement();
		$(input).off('mouseup').on('mouseup', (function(self){
			return function(){
				self.publish({event: "browse", value: {}});
			};
		})(this));
	};

	this.bindDomEvents = function() {
		var el = this.getInputElement();

		$(el).off('mousenter').on('mouseenter', (function(self){
			return function(ev){
				console.log('refreshing....');
				self.options.uploader.refresh();
			};
		})(this));
	};

	this.bindUploaderEvents = function() {
		var self = this;
		this.options.uploader.on('Init', function(up, params) {
			appdb.debug("Uploader Inited...");
			self.publish({event: "init", value: {parameters: params}});
		});
		this.options.uploader.refresh();
		this.options.uploader.on('FilesAdded', function(up, files){
			appdb.debug("Added files: ", files);
			//prevent duplicates
			var filenames = {};
			var toberemoved = {};
			//collect duplicates
			$.each( up.files, function(i,e){
				if( filenames[e.name] ){
					toberemoved[e.name] = filenames[e.name];
				}
				filenames[e.name] = e;
			});

			//remove duplicates. internalremoval is used as a mutex in FilesRemoved event
			up.internalFileRemoval = true;
			for(var i in toberemoved ){
				up.removeFile(toberemoved[i]);
			}
			up.internalFileRemoval = false;

			self.publish({event: "addfiles", value: files});
			up.refresh();
		});
		this.options.uploader.on('FilesRemoved', function(up, files){
			self.publish({event: "removefiles", value: files});
		});
		this.options.uploader.on('QueueChanged', function(up){
			self.publish({event: "changefiles", value: {}});
		});
		this.options.uploader.on('BeforeUpload', function(up,file){
			self.publish({event:"startupload", value: {file: file}});
		});
		this.options.uploader.on('UploadProgress', function(up, file) {
			self.publish({event: "progress", value: {file: file, percent: file.percent}});
		});
		this.options.uploader.on('FileUploaded', function(up, file, info) {
			info.response = JSON.parse(info.response);
			self.publish({event: "filecomplete", value: {file: file, info: info, response: info.response}});
		});
		this.options.uploader.on('UploadComplete', function(up, file, info) {
			self.publish({event: "complete", value: {file: file, info: info}});
		});
		this.options.uploader.on('Error', function(up, err) {
			appdb.debug(err);
			self.publish({event: "error", value: {message: err.message, code: err.code, file: err.file}});
		});
	};

	this.initUploader = function(){
		this.options.uploader = new plupload.Uploader({
			runtimes : 'html5,flash,silverlight,gears,html4,browserplus',//old version of firefox have isues with flash
			browse_button : $(this.getInputElement()).attr('id'),
			multi_selection: false,
			unique_names : true,
			prevent_duplicates: true,
			chunk_size : '500kb',
			max_file_size : '50mb',
			url : this.options.url,
			flash_swf_url : '/plupload.flash.swf',
			silverlight_xap_url : '/plupload.silverlight.xap',
			multipart_params: {
				guid: this.options.guid
			},
			filters: {
				mime_types : "text/plain"
			}
		});

		this.bindUploaderEvents();
		this.options.uploader.init();
		this.bindDomEvents();
		this.hide(false);
	};
	this.start = function(){
		if( this.options.uploader ){
			this.options.uploader.start();
		}
	};
	this.render = function(){
		this.renderInputElement();
		setTimeout((function(self){
			return function(){
				self.initUploader();
			};
		})(this),10);
	};

	this._init = function(){
		this.parent = this.options.parent;
		this.dom = $(this.options.container);
	};

	this._init();
});

appdb.components.ContextScriptEditor = appdb.ExtendClass(appdb.views.ui.DataEditable, "appdb.components.ContextScriptEditor", function(o){
	this.options = {
		container: o.container,
		parent: o.parent || null,
		group: o.group || "",
		data: o.data || {},
		canedit: true,
		editmode: true,
		file_uploader: null,
		autoclose: ( (typeof o.autoclose === "boolean")?o.autoclose:true ),
		useformats: ( (typeof o.useformats === 'boolean')?o.useformats:false ),
		format: o.format || -1
	};
	
	this.useFormatList = function(v){
		if( typeof v !== 'undefined' ) {
			v = (typeof v === 'boolean')?v:false;
			this.options.useformat = v;
		}
		var result = (this.options.useformats === true);
		if( result ){
			$(this.dom).addClass("use-formats");
		} else {
			$(this.dom).removeClass("use-formats");
		}
		return (this.options.useformats === true);
	};
	
	this.reset = function(){
		if( this.options.file_uploader ) {
			this.options.file_uploader.destroy();
			this.options.file_uploader = null;
		}
	};
	
	this.getEditor = function(name){
		name = $.trim(name);
		var editable = $.grep( this.getEditables(), function(e){
			return ( e.getProp('name') === name );
		});

		return ( editable.length > 0 )?editable[0]:null;
	};

	this.show = function() {

		this.close();
		appdb.components.ContextScriptEditor.Dialog = new dijit.Dialog({
			title: appdb.components.ContextScriptEditor.Title,
			content: $(this.dom)[0],
			onHide: (function(self){
				return function(){
					if(self.options.file_uploader){
						self.options.file_uploader.destroy();
					}
				};
			})(this)
		});
		this.render();

		appdb.components.ContextScriptEditor.Dialog.show();
		appdb.components.ContextScriptEditor.Dialog.startup();
	};

	this.autoclose = function(){
		if( this.options.autoclose === true ){
			this.close();
		}
	};
	
	this.close = function() {
		if( appdb.components.ContextScriptEditor.Dialog ){
			appdb.components.ContextScriptEditor.Dialog.hide();
			appdb.components.ContextScriptEditor.Dialog.destroyRecursive(false);
			appdb.components.ContextScriptEditor.Dialog = null;
		}
	};

	this.applySelection = function() {
		return this['apply_' + this.getSelectedType()]();
	};

	this.apply_file = function(){
		this.renderLoading(true, "uploading");
		this.options.file_uploader.start();
	};

	this.apply_url = function(){
		var data = this.getEditedData();
		
		if( data ){
			this.renderLoading(true, 'Uploading');
			$.post(appdb.config.endpoint.base + 'storage/url',
			{ guid: this.options.data.id, url: data.url },
			(function(self){
				return function(v, status){
					self.renderLoading(false);
					v = JSON.parse(v);
					if( v.error ){
						self.renderError(v.error);
					}else{
						self.publish({event: 'result', value: { "type": "url", "name": $.trim(v.name), "url": $.trim(appdb.config.endpoint.drafts) + v.url.slice(1), "md5": v.md5, "size": v.size , "format": self.getSelectedFormat() } });
						self.autoclose();
					}
				};
			})(this));
		}
	};

	this.apply_script = function(){
		
		var data = this.getEditedData();
		if( data ){
			this.renderLoading(true, 'uploading');
			$.post(appdb.config.endpoint.base + 'storage/code',
				{ name: data.name, guid: this.options.data.id, code: data.code },
				(function(self){
					return function(v,status){
						self.renderLoading(false);
						v = JSON.parse(v);
						if( v.error ){
							self.renderError(v.error);
						}else{
							self.publish({event: 'result', value: { "type": "script", "name": $.trim(data.name), "url": $.trim(appdb.config.endpoint.drafts) + v.url.slice(1), "code": $.trim(data.code), "md5": v.md5, "size": v.size, "format": self.getSelectedFormat() } });
							self.autoclose();
						}

					};
				})(this)
			);
		}
	};

	this.getSelectedType = function(){
		return this.options.selectedType;
	};

	this.validate_file = function(files){
		if( !this.options.file_uploader ) return false;
		var state = 0;
		if( this.options.file_uploader && this.options.file_uploader.options.uploader ){
			state = this.options.file_uploader.options.uploader.state;
		}
		return ( this.options.file_uploader.getSelectedFiles().length > 0 && state !== 2 );
	};

	this.validate_script = function(){
		var urlEditor = this.getEditor('content');
		if( !urlEditor ) return true;

		return urlEditor.isValid();
	};

	this.validate_url = function(){
		var urlEditor = this.getEditor('url');
		if( !urlEditor ) return true;

		return urlEditor.isValid();
	};

	this.validate = function(){
		if( !this.getSelectedType() ){
			return true;
		}
		var valid = this["validate_" + this.getSelectedType()]();
		$(this.dom).find(".commands .apply").addClass('btn-disabled disabled').prop('disabled', true);
		if( valid ){
			$(this.dom).find(".commands .apply").removeClass('btn-disabled disabled').prop('disabled', false);
		}
		return true;
	};

	this.init_content_file = function(){
		this.reset();
		$(this.dom).find('.select-file').removeClass('uploading completed error');

		var filedom = $(this.dom).find('.select-file');
		var sfile = $(this.dom).find('.selected-file');
		var percent = $(this.dom).find('.select-file .progressbar .percent');
		var bar = $(this.dom).find('.progressbar .bar');
		var errmsg = $(this.dom).find('.error-message');

		$(sfile).text( $(sfile).data('empty'));

		this.options.file_uploader = new appdb.components.FileUploader({
			parent: this,
			container: $(appdb.components.ContextScriptEditor.Dialog.containerNode).find('#cscript-uploader-container'),
			url: '/storage/upload',
			data: {
				parentid: this.options.data.id
			}
		});
		this.options.file_uploader.subscribe({event: 'startupload', callback: function(v){
			this.renderLoading(true, 'uploading');
			this.options.file_uploader.hide();
			this.validate();
		}, caller: this});
		this.options.file_uploader.subscribe({event: 'addfiles', callback: function(v){
			var file = (v.length>0)?v[0]:null;
			this.renderLoading(false);
			$(filedom).removeClass('error');
			if( file ){
				$(sfile).text(file.name);
				this.options.file_uploader.setParam('filename',file.name);
			} else {
				$(sfile).text( $(sfile).data('empty'));
			}
			$(bar).css({'width': '0px'});
			setTimeout((function(self){
				return function(){
					self.validate();
				};
			})(this),1);
		}, caller: this});

		this.options.file_uploader.subscribe({event: 'removefiles', callback: function(v){
			this.validate();
		}, caller: this});

		this.options.file_uploader.subscribe({event: 'progress', callback: function(v){
			if( v && v.percent >= 100 ) return;
			$(filedom).addClass('uploading');
			$(percent).text(v.percent + "%");
			$(bar).css({'width': ""+v.percent + "%"});
			this.validate();
		}, caller: this});

		this.options.file_uploader.subscribe({event: 'filecomplete', callback: function(v){
			$(filedom).removeClass('uploading').removeClass('error');
			this.renderLoading(false);
			this.options.file_uploader.hide(false);
			if( v.response && !v.response.error){
				$(filedom).addClass('completed');
				this.publish({event: 'result', value: { "type": "url", "name": $.trim(v.file.name), "url": $.trim(appdb.config.endpoint.drafts) + v.response.url.slice(1), "md5": v.md5, "size": v.size, "format": this.getSelectedFormat() } });
				this.autoclose();
			}
			if( !v.response || v.response.error){
				var message = v.response.error.message;
				this.renderError(message);
				this.initContent();
			}
		}, caller: this});

		this.options.file_uploader.subscribe({event: 'error', callback: function(v){
			var message = v.message;
			this.renderError(message);
			$(errmsg).text(message);
			$(filedom).addClass('error');
			this.options.file_uploader.hide(false);
			this.initContent();
		}, caller: this});

		this.options.file_uploader.render();
		this.options.file_uploader.hide(false);
		this.validate();
	};

	this.init_content_url = function(){
		this.reset();
		var urlEditor = this.getEditor('url');
		if( !urlEditor ) return;

		urlEditor.subscribe({event: 'changed', callback: function(v){
			return this.validate(v);
		}, caller: this});
	};

	this.init_content_script = function(){
		this.reset();var urlEditor = this.getEditor('content');
		if( !urlEditor ) return;

		urlEditor.subscribe({event: 'changed', callback: function(v){
			return this.validate(v);
		}, caller: this});
	};

	this.initContent = function(){
		setTimeout((function(self){
			return function(){
				self["init_content_" + self.getSelectedType()]();
			};
		})(this), 100);
	};

	this.selectType = function(v, forceselector){
		if( forceselector === true)
		{
			this.options.selector.set('value',v);
			return;
		}
		$(this.dom).find(".content").removeClass('active');
		$(this.dom).find(".content[data-select='" + v + "']").addClass('active');
		this.options.selectedType = v;
		
		this.validate();
		this.initContent();
		this.renderError(false);
		this.renderLoading(false);
	};
	
	this.getSelectedFormat = function(){
		if( this.options.formatlist ){
			return { "id": this.options.formatlist.get('value'), "name": this.options.formatlist.get('displayedValue') };
		}
		return null;
	};
	
	this.setSelectedFormat = function(id){
		if( this.options.formatlist ){
			return this.options.formatlist.set('value', id);
		}
	};
	
	this.preloadCode = function(){
		var data = this.getData() || "";
		var url = $.trim( (typeof data === 'string')?data:data.url );
		
		if( url === "" ) {
			return;
		}
		var normalizeUrl = function(url){
			if( appdb.config.https === true ){
				url = url.replace('http://', 'https://');
			} else {
				url = url.replace('https://', 'http://');
			}
			return url;
		};
		
		var renderCodeLoading = (function(self){ 
			return function(enable, text){
				enable = (typeof enable === 'boolean')?enable:true;
				text = $.trim(text) || 'loading';

				$(self.dom).find('.content.script').removeClass('loading');
				if( enable ){
					$(self.dom).find('.content.script').addClass('loading');
					$(self.dom).find('.content.script .code.loader .value').text('...'+text);
				}
			};
		})(this);
		renderCodeLoading(true);
		
		var xhr = $.ajax({
			url: normalizeUrl(url),
			type: 'GET',
			success: (function(self) { 
				return function(v, status){
					renderCodeLoading(false);
					var uieditor = self.getEditor('content');
					if( uieditor ){
						self.options.data.code = v;
						uieditor.on(self.options.data);
					}
				};
			})(this),
			error: function(err,status){ 
				renderCodeLoading(false); 
				console.log('[CONTEXTSCRIPT EDITOR]: ' + status);
			}
		});
		$(this.dom).find('.content.script .code.loader .cancel-loader').off('click').on('click',(function(xhr){
			return function(ev){
				ev.preventDefault();
				if( xhr && xhr.abort && xhr.readyState > 0 && xhr.readyState < 4  ){
					renderCodeLoading(false);
					xhr.abort();
				}
				return false;
			};
		})(xhr));
	};
	
	this.renderFormatList = function(){
		var dom = $(this.dom).find('select.contextscript-editor-formatlist');
		$(dom).empty();
		$.each(appdb.model.StaticList.ContextFormats, function(i,e){
			var opt = $("<option></option>");
			$(opt).attr('value', e.id);
			$(opt).text(e.name);
			$(dom).append(opt);
		});
		this.options.formatlist = new dijit.form.Select({
			placeHolder: $(dom).attr('placeholder'),
			style: "width: 100%"
		}, dom[0]);
		this.setSelectedFormat(this.options.format);
	};
	
	this.renderSelector = function(){
		var dom = $(this.dom).find('select.contextscript-editor-type');
		this.options.selector = new dijit.form.Select({
			placeHolder: $(dom).attr('placeholder'),
			style: "width: 100%",
			onChange: (function(self){
				return function(v){
					self.selectType(v);
				};
			})(this)
		}, dom[0]);
	};

	this.renderCommands = function(){
		$(this.dom).find(".commands .command.cancel").off("cancel").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.close();
				return false;
			};
		})(this));

		$(this.dom).find(".commands .command.apply").off("cancel").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.applySelection();
				return false;
			};
		})(this));
	};

	this.renderError = function(error){
		$(this.dom).removeClass('error loading');
		if( error === false ){
			return;
		}
		console.log(error);
		$(this.dom).addClass('error').find(".content-error > .value").text(error);
	};
	
	this.renderLoading = function(enable, text){
		$(this.dom).removeClass('error loading');
		enable = (typeof enable === 'boolean')?enable:true;
		text = $.trim(text) || 'loading';
		
		if( enable === true ){
			$(this.dom).addClass('loading').find('.loader .value').text("..." + text);
		}
	};
	
	this.render = function() {
		this.renderSelector();
		this.renderCommands();
		this.renderFormatList();
		this.on();
		this.edit(this.dom);
		this.selectType('script', true);
		this.useFormatList();
		this.renderError(false);
		this.renderLoading(false);
		this.preloadCode();
	};

	this._init = function() {
		this.parent = this.options.parent;
		this.dom = this.options.container || $("body").find('[data-template="contextscript-script-editor"]').find(".contextscript-editor-container").clone();
	};

	this._init();
},{
	Dialog: null,
	Title: "Upload contextualization script"
});

appdb.views.ui.editors.ContextScript = appdb.ExtendClass(appdb.views.ui.editors.Generic,"appdb.views.ui.editors.ContextScript",function(o){
	this.options = $.extend(true,{
		autoclose: ((typeof o.autoclose === 'boolean')?o.autoclose:true),
		useformats: ((typeof o.useformats === 'boolean')?o.useformats:false),
		preselectFormat: null
	}, this.options);
	
	this.showCode = function(){

	};

	this.closeDialog = function(){
		if( this.options.contextscripteditor ){
			this.options.contextscripteditor.close();
		}
	};

	this.getParentData = function(){
		return this.parent.getData();
	};

	this.getEditedData = function(){
		return {'url': this.options.data };
	};
	
	this.setFormat = function(v){
		var id = v || -1;
		if( $.isPlainObject(v) ){
			id = +v.id || -1;
		}
		
		if( typeof id !== 'number' || id <= 0 ) {
			id = -1;
		}
		
		this.options.format = id;
	};
	
	this.getFormat = function(){
		var id = +this.options.format || -1;
		
		if( typeof id !== 'number' || id <= 0 ) {
			id = -1;
		}
		
		return id;
	};
	this.getName = function(){
		if( $.trim(this.options.name) === "" && this.parent && typeof this.parent.getData === 'function'){
			var d = this.parent.getData();
			this.options.name = $.trim(d.name);
		}

		return this.options.name;
	};

	this.closeDialog = function(){
		if( this.options.contextscripteditor ){
			this.options.contextscripteditor.close();
		}
	};
	this.renderError = function(error){
		if( this.options.contextscripteditor ){
			this.options.contextscripteditor.renderError(error);
		}
	};
	this.renderLoading = function(enable, text){
		if( this.options.contextscripteditor ){
			this.options.contextscripteditor.renderLoading(enable, text);
		}
	};
	this.showDialog = function(){
		if( this.options.contextscripteditor ){
			this.options.contextscripteditor.close();
			this.options.contextscripteditor.unsubscribeAll(this);
			this.options.contextscripteditor.destroy();
			this.options.contextscripteditor = null;
		}
		var data = this.getData();
		var d = {
			"name": $.trim(this.getName()),
			"url": $.trim(data),
			"code": $.trim("")
		};
		this.options.contextscripteditor = new appdb.components.ContextScriptEditor({
			parent: this,
			data: d,
			useformats: this.options.useformats,
			autoclose: this.options.autoclose,
			format: this.getFormat()
		});
		this.options.contextscripteditor.subscribe({event: 'result', callback: function(v){
			if( $.trim(v.url) !== ''){
				this.options.data = $.trim(v.url);
				this.options.name = $.trim(v.name);
			}
			console.log(v);
			this.render();
			this.onValueChange(v);
		}, caller: this });
		this.options.contextscripteditor.show();
	};
	this.isValid = function(){
		var d = this.getEditedData();
		return  ( $.trim(d.url)!=='' )?true:false;
	};
	this.checkViewCode = function() {
		var d = this.getData();
		var view = $(this.dom).find(".command.view");
		$(view).removeClass('disabled').prop('disabled', false);
		if( $.trim(d) === "" ) {
			$(view).addClass('disabled').prop('disabled', true);
		}
	};
	this.renderPopup = function(){
		var d = this.getData();
		if( $.trim(d) === '' ) {
			$(this.dom).append(this.options.templates.emptymessage);
			return;
		}
		var a = $('<a class="url" target="_blank"></a>');
		var name = $.trim(this.getName()) || $.trim(d);
		if( name.length > 50 ){
			nam = name.slice(0,45) + "...";
		}
		$(a).attr('href',$.trim(d));
		$(a).text(name);
		$(this.dom).append(a);
	};

	this.render = function(){
		this.reset();
		this.renderPopup();

		var upload = this.options.templates.upload.clone();
		$(upload).off('click').on('click', (function(self){
			return function(ev){
				self.showDialog();
			};
		})(this));

		$(this.dom).append(upload);

		var view = this.options.templates.view.clone();
		$(view).off('click').on('click', (function(self){
			return function(ev){
				self.showCode();
			};
		})(this));

		$(this.dom).append(view);
		this.checkViewCode();
	};

	this.onValueChange = function(v){
		this.publish({event: "changed", value: this.getEditedData()});
		if( v && $.trim(v.url)!== ''){
			this.publish({event: "result", value: v || this.getData()});
		}
	};

	this.getDisplayData = function(){
		var v = this.getData();
		
	};

	this._initContainer = function(){
		var html = $('<div></div>').html(this.options.innerHTML);
		if( !this.options.templates ) {
			this.options.templates = {
				emptymessage: $(html).find('.empty-message').html(),
				details: $(html).find('.script-details').html(),
				upload: $(html).find('.command.upload').clone(),
				view: $(html).find('.command.view').clone()
			};
			$(this.dom).empty();
		}
	};
});
