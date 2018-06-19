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
if( !window["appdb"] ){
	window["appdb"] = {};
}
appdb.repository = {};
appdb.repository.utils = {};
appdb.repository.ui = {};
appdb.repository.model = {};
appdb.repository.ui.components = {};
appdb.repository.ui.views = {};
appdb.repository.ui.views.files = {};

appdb.repository.ReleaseManager = (function(){
	var _releaseManager = new function(){
		this.newRelease = function(o){
			o = o || {};
			appdb.repository.ui.CreateRelease(swid, function(){});
		};
		this.init = function(){
			this._mediator = new appdb.utils.ObserverMediator(this);
			this.subscribe = this._mediator.subscribe;
			this.publish = this._mediator.publish;
			this.unsubscribe = this._mediator.unsubscribe;
			this.unsubscribeAll = this._mediator.unsubscribeAll;
			this.clearObserver = this._mediator.clearAll;
		};
		this.init();
	};
	
	return _releaseManager;
})();

/*************************************
 ******** UTILITIES SECTION **********
 ************************************/
appdb.repository.utils.GetSupportedOses = function(property){
	var data = appdb.repository.model.Targets.getLocalData();
	var result = [];
	property = $.trim(property);
	if( data && data.target ){
		data.target = $.isArray(data.target)?data.target:[data.target];
		var uniq = {};
		$.each(data.target, function(i, e){
			if( e.os && e.os.name){
				uniq[e.os.name] = jQuery.extend(true, {}, e.os);
			}
		});
		for(var u in uniq){
			if( uniq.hasOwnProperty(u) === false ) continue;
			if( property !== "" ){
				var p = property.split(".");
				var cval = uniq[u];
				$.each(p, function(i, e){
					if( cval && typeof cval[e] !== "undefined" ){
						cval = cval[e];
					}
				});
				result.push(cval);
			}else{
				result.push(uniq[u]);
			}
		}
	}
	return result;
};
appdb.repository.utils.getOsImagePath = function(osname){
	osname = ($.trim(osname)==="")?"generic":$.trim(osname);
	var result = "/images/repository/os_" ;
	var oses = appdb.repository.utils.GetSupportedOses("name");
	if( oses.length === 0 || (oses.length > 0 && $.inArray(osname,oses) === -1) ){
		result += "generic.png";
	} else {
		result += osname + ".png";
	}
	return result;
};

appdb.repository.utils.CreateAjaxSingleton = function(cntx, url, data, timeout){
	return (function(self){
		var _timeout = timeout || 500;
		var _url = url;
		var _xhr = null;
		var _data = data || {};
		var _timer = -1;
		return function(data, before, after){
			if( _timer !== -1 ) clearTimeout(_timer);
			_timer = setTimeout(function(){
				if( _xhr !== null ){
					_xhr.abort();
					_xhr = null;
				}
				if(typeof data === "function"){
					data = data.apply(cntx);
				}
				_data = $.extend(_data,data);
				before.call(self);
				_xhr = $.ajax({
					url: _url,
					data: _data,
					dataType: 'xml',
					success: function(d){
						after.call(self,d);
					},
					error: function(st,err){
						after.call(self,"<respone error='"+err+"'></response>");
					}
				});
				clearTimeout(_timer);
				_timer = -1;
			},_timeout);
		};
	})(cntx);
};
appdb.repository.utils.getFileSize = function(bytes){
	bytes = bytes || 0;
	if (bytes == 0) return '0 bytes';
	var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
};
appdb.repository.utils.FindData = function(data,nsname){
    var ns = data, i, nslen, nsitem=null;
	nsname = nsname || "";
    nsname = nsname.split(".");
    nslen = nsname.length;
    for(i=0; i<nslen; i+=1){
        nsitem = ns[nsname[i]];
        if(typeof nsitem === "undefined"){
            return null;
        }
        ns = nsitem;
    }
    return ns;
};
appdb.repository.ui.NewReleaseForm = appdb.ExtendClass(appdb.Component, "appdb.repository.ui.NewReleaseForm", function(o){
	o = o || {};
	this.options = {};
	this.checkBaseReleaseTag = appdb.repository.utils.CreateAjaxSingleton(this, appdb.config.repository.endpoint.base + "release/validatedisplayversion", {swid: this.options.swid},500);
	this.checkUpdateReleaseTag = function(){};
	this.handleIndicator = function(html){
		var _html = $(this.dom).find(html);
		return function(check, errors){
			check = check || false;
			errors = errors || [];
			if( errors ){
				errors = $.isArray(errors)?errors:[errors];
			}else{
				errors = [];
			}
			$(_html).addClass("hidden").find(".checker, .validation").addClass("hidden");
			if ( check === true ){
				$(_html).removeClass("hidden").find(".checker").removeClass("hidden");
			} else if( errors.length > 0){
				$(_html).removeClass("hidden").find(".validation").removeClass("hidden").find(".value").html(errors[0].value);
			}
		};
	};
	this.validateValue = function(v){
		var res = [];
		if( /^\./g.test(v) || /\.$/g.test(v)) {
			res.push({value:'Value must not start or end with \'<b>.</b>\' character.'});
		}
		if( /[\ \n\t]/g.test(v) ){
			res.push({value:'No white spaces allowed.'});
		}
		if( v.length < 2 || v.length > 20 ){
			res.push({value:'Value must be between 2 to 20 characters long.'});
		}
		if( ! /[A-Za-z0-9]+/g.test(v) ){
			res.push({value:'Value must contain alphanumeric characters.'});
		}
		if( /^[A-Za-z0-9\.\_\-]+$/g.test(v) === false ){
			res.push({value:'Value contains invalid characters. Only . _ - symbols are allowed.'});
		}
		if( res.length === 0){
			res = false;
		}
		return res;
	};
	this.baseSetupForm = function() {
		var self = this;
		var rtagobj = dijit.byId("basereleasetag");
		var rareaobj = dijit.byId("repoarea");
		var currentRtag = "";
		var currentRarea = "";
		var rtagIndicator = self.handleIndicator.call(self, ".base.releasetype .field.displayversion .indication");
		var rareaIndicator = self.handleIndicator.call(self, ".base.releasetype .field.repoarea .indication");
		
		var preValidate = function(displayversion, repoarea){
			var res = {displayversion: [], repoarea: []};
			displayversion = $.trim(displayversion);
			repoarea = $.trim(repoarea);
			
			res.displayversion = self.validateValue(displayversion);
			res.repoarea = self.validateValue(repoarea);

			return res;
		};

		var initCheckBaseRelease = function(fromRepoArea, isExternal){
			isExternal = (typeof isExternal !== "undefined")?isExternal:false;
			fromRepoArea = fromRepoArea || false;
			var rtag = $.trim(rtagobj.get("displayedValue"));
			var rarea = $.trim(rareaobj.get("displayedValue"));
			if( rtag == currentRtag && rarea == currentRarea ){
				return;
			}
			currentRtag = rtag;
			currentRarea = rarea;
			
			self.setSubmit(false);
			
			var errs = preValidate(rtag,rarea);
			if( errs.displayversion.length > 0 || errs.repoarea.length > 0 ){
				rtagIndicator(false,errs.displayversion || []);
				rareaIndicator(false,errs.repoarea || []);
				return;
			} 
			if( rtag == "" || rarea == "") {
				self.setSubmit(false);
				return;
			}

			self.checkBaseReleaseTag(
				(function(releaseTag, repoArea){ //Setup data for checking
					return function(){
						return {
							swid: self.options.swid,
							displayversion: releaseTag,
							repoareaname: repoArea,
							parentid: 0
						};
					};
				})(rtag,rarea),
				function before(){ //Setup UI before check
					if( !fromRepoArea ){
						rtagIndicator(true);
					}
					rareaIndicator(true);
				}, function after(v){ //Handle check results
					var d = appdb.utils.convert.toObject(v);
					rtagIndicator(false);
					rareaIndicator(false);
					
					if( d.error || d.hasErrors){
						if( d.displayversion && d.displayversion.error ){
							d.displayversion.error = $.isArray(d.displayversion.error)?d.displayversion.error:[d.displayversion.error];
							rtagIndicator(false, d.displayversion.error);
						}
						if( d.repoarea && d.repoarea.error ){
							d.repoarea.error = $.isArray(d.repoarea.error)?d.repoarea.error:[d.repoarea.error];
							rareaIndicator(false, d.repoarea.error);
						}
					} else {
						self.options.displayVersion = rtag;
						self.options.parentid = 0;
						self.options.repoareaname = rarea;
						self.setSubmit(true);
					}
				}
			);
		};
		
		dojo.connect(rtagobj, "onMouseUp",function(){initCheckBaseRelease(false);});
		dojo.connect(rtagobj, "onKeyUp",function(){initCheckBaseRelease(false);});
		dojo.connect(rtagobj, "onChange",function(){initCheckBaseRelease(false);});

		dojo.connect(rareaobj, "onMouseUp",function(){initCheckBaseRelease(true);});
		dojo.connect(rareaobj, "onKeyUp",function(){initCheckBaseRelease(true);});
		dojo.connect(rareaobj, "onChange",function(){initCheckBaseRelease(true);});

		$(this.dom).find(".repoareaprefix").text("../" + appdb.pages.application.currentCName() + "/");
		this.setSubmit(false);
		initCheckBaseRelease(true);
	};
	this.updateSetupForm = function(){
		var self = this;
		var rtagobj = dijit.byId("updatereleasetag");
		var parentobj = dijit.byId("associatedrelease");
		var currentRtag = "";
		var currentParentId = "";
		
		var rtagIndicator = self.handleIndicator.call(self, ".update.releasetype .field.updatedisplayversion .indication");
		
		var preValidate = function(displayversion){
			var res = {displayversion: []};
			displayversion = $.trim(displayversion);
			res.displayversion = self.validateValue(displayversion);
			return res;
		};

		var initCheckUpdateRelease = function(){
			var rtag = $.trim(rtagobj.get("displayedValue"));
			var parentid = $.trim(parentobj.get("value"));
			
			if( rtag == currentRtag && parentid == currentParentId ){
				return;
			}
			currentRtag = rtag;
			currentParentId= parentid;
			
			self.setSubmit(false);
			
			if( rtag === "" ||  parentid == "-1"){
				return;
			}

			var errs = preValidate(rtag);
			if( errs.displayversion.length > 0 ){
				rtagIndicator(false,errs.displayversion || []);
				return;
			}

			self.checkBaseReleaseTag(
				(function(releaseTag, parentId){ //Setup data for checking
					return function(){
						return {
							swid: self.options.swid,
							displayversion: releaseTag,
							parentid: parentId
						};
					};
				})(rtag,parentid),
				function before(){ //Setup UI before check
					rtagIndicator(true);
				}, function after(v){ //Handle check results
					var d = appdb.utils.convert.toObject(v);
					rtagIndicator(false);
					
					if( d.error || d.hasErrors){
						if( d.displayversion && d.displayversion.error ){
							d.displayversion.error = $.isArray(d.displayversion.error)?d.displayversion.error:[d.displayversion.error];
							rtagIndicator(false, d.displayversion.error);
						}
					} else {
						self.options.displayVersion = rtag;
						self.options.parentid = parentid;
						self.setSubmit(true);
					}
				}
			);
		};
		dojo.connect(rtagobj, "onMouseUp",function(){initCheckUpdateRelease(false);});
		dojo.connect(rtagobj, "onKeyUp",function(){initCheckUpdateRelease(false);});
		dojo.connect(rtagobj, "onChange",function(){initCheckUpdateRelease(false);});

		dojo.connect(parentobj, "onMouseUp",function(){initCheckUpdateRelease(false);});
		dojo.connect(parentobj, "onKeyUp",function(){initCheckUpdateRelease(false);});
		dojo.connect(parentobj, "onChange",function(){initCheckUpdateRelease(false);});
		
		this.setSubmit(false);
		initCheckUpdateRelease();
	};
	this.setupForm = function() {
		var self = this;
		this.setSubmit(false);
		
		$(this.dom).find("#basereleasetypeselect").off("change").on("change", function(ev){
			if( $(this).is(":checked") ){
				$(this).parent().parent().addClass('selectbase').removeClass('selectupdate').find(".selected").removeClass("selected");
				$(this).parent().addClass("selected");
				self.baseSetupForm();
			}
		});
		
		$(this.dom).find("#updatereleasetypeselect").off("change").on("change", function(ev){
			if( $(this).is(":checked") ){
				$(this).parent().parent().removeClass('selectbase').addClass('selectupdate').find(".selected").removeClass("selected");
				$(this).parent().addClass("selected");
				self.updateSetupForm();
			}
		});	
		
		if( $(this.dom).hasClass("basetype") ){
			$(this.dom).find("#updatereleasetypeselect").prop("disabled", true);
		} else if( $(this.dom).hasClass("updatetype") ) {
			$(this.dom).find("#basereleasetypeselect").prop("disabled", true);
		}
		
		$(this.dom).find(".help").each( function(index, elem) {
			if( $(elem).find(".messagepopup").length > 0 && $(elem).find("img").length > 0) {
				new dijit.Tooltip({
					connectId: $(elem).find("img:first")[0],
					label: "<div class='messagepopupcontainer'>" + $(elem).find(".messagepopup").html() + "</div>"
				});
			}
		});
		
		$(this.dom).find(".footer .actions .cancel").each(function(index, elem){
			dojo.connect(dijit.byNode($(elem)[0]), "onClick", function(){
				self.cancel();
			});
		});
		$(this.dom).find(".footer .actions .submit").each(function(index, elem){
			dojo.connect(dijit.byNode($(elem)[0]), "onClick", function(){
				self.submit();
			});
		});
		
		if( this.options.releaseType == "base" ){
			$(this.dom).find("#basereleasetypeselect").attr("checked",true).trigger("change");
			this.options.title = "Create base / major release for " + appdb.pages.application.currentName();
			this.baseSetupForm();
		} else if ( this.options.releaseType == "update") {
			$(this.dom).find("#updatereleasetypeselect").attr("checked",true).trigger("change");
			this.options.title = "Create an update release for " + appdb.pages.application.currentName();
			if( this.options.parentid ){
				dijit.byNode( $(this.dom).find("#associatedrelease")[0] ).set("value", '' + this.options.parentid ).set('disabled', true);
			}
			this.updateSetupForm();
		} else {
			$(this.dom).find("#basereleasetypeselect").attr("checked",true).trigger("change");
			this.baseSetupForm();
			this.updateSetupForm();
		}
	};
	this.cancel = function() {
		setTimeout((function(self){
			return function(){
				if( self.options.useDialog ){
					appdb.repository.ui.NewReleaseForm.dialog.hide();
					appdb.repository.ui.NewReleaseForm.dialog.destroyRecursive(false);
					appdb.repository.ui.NewReleaseForm.dialog = null;
				}
				$(self.dom).empty();
				self.publish({event: "cancel", value: {}});
			};
		})(this),1);
	};
	this.setSubmit = function(enabled){
		if (typeof enabled !== "boolean" ) {
			return;
		}
		dijit.byNode($(this.dom).find(".footer .submit:last")[0]).set("disabled", !enabled);
	};
	this.submit = function() {
		var self = this;
		this.setSubmit(false);
		this.publish({event: "beforesubmit", value: {}});
		var repo_area_name = self.options.repoareaname;
		if( $.trim(this.options.releaseType) == 'base' ) {
			repo_area_name = '@' + repo_area_name;
		}
		$.ajax({
			url: appdb.config.repository.endpoint.base + "release/newrelease",
			action: "POST",
			type: "POST",
			data: {swid: self.options.swid,
				swname: self.options.swname,
				parentid: self.options.parentid, 
				displayversion: self.options.displayVersion, 
				repoareaname: repo_area_name},
			success: function(v){
				var d = appdb.utils.convert.toObject(v);
				if( d && d.error ){
					alert(d.error);
					self.setSubmit(true);
				}else{
					self.publish({event: "done", value: d});
					self.cancel();
				}
			},
			error: function(v){
				alert(v.responseText);
			}
		});
	};
	this.showDialog = function(){
		if(appdb.repository.ui.NewReleaseForm.dialog){
			appdb.repository.ui.NewReleaseForm.dialog.hide();
			appdb.repository.ui.NewReleaseForm.dialog.destroyRecursive(true);
			appdb.repository.ui.NewReleaseForm.dialog = null;
		}

		appdb.repository.ui.NewReleaseForm.dialog =  new dijit.Dialog({
			title: this.options.title,
			content : $(this.dom)[0],
			onCancel: (function(self){
				return function(){
					self.cancel();
				};
			})(this)
		});
		appdb.repository.ui.NewReleaseForm.dialog.show();
	};
	this.showForm = function(){
		if( this.options.useDialog ){
			this.showDialog();
		}
		$(this.dom).removeClass("hidden");		
	};
	this.setupErrorForm = function(st, err) {
		alert(st + " : " + err);
		this.publish({event: "error", value: {error:err, status: st}});
	};
	this.load = function(d) {
		this.options = {
			swid: d.swid,
			appName: appdb.pages.application.currentName(),
			swname: appdb.pages.application.currentCName(),
			parentid: d.parentid,
			releaseType: $.trim(d.releaseType || "").toLowerCase(),
			releaseTag: "",
			repoTag: "",
			title: "Create new release for " + appdb.pages.application.currentName()
		};
		this.options.useDialog = (o.container)?false:true;
		this.dom = (o.container)?$(o.container):$("<div class='hidden'></div>");
		
		if( !this.options.releaseType && this.options.parentid ){
			this.options.releaseType = "update";
		}
		
		if( this.options.releaseType ){
			if( this.options.releaseType == "base" || this.options.releaseType == "update") {
				
			} else {
				this.options.releaseType = "";
			}
		}
		var self = this;
		$.ajax({
			url: appdb.config.repository.endpoint.base + "release/newrelease",
			data: {swid: this.options.swid, name: this.options.appName},
			success: function(d){
				self.dom = $(d);
				$(self.dom).addClass(self.options.releaseType + "type");
				dojo.parser.parse($(self.dom)[0]);
				self.setupForm();
				self.showForm();
			},
			error: function(st,err){
				self.setupErrorForm();
			}
		});
	};
},{
	dialog : null
});

appdb.repository.ui.Accordion = function(o){
	this.options = {
		dom: $(o.container),
		parent: o.parent,
		onClick: o.onClick || function(){return true;}
	}
	this.setSelected = function(elem){
		$(this.dom).children("li").removeClass("selected").removeClass("expand").addClass("collapse");
		$(elem).addClass("selected").removeClass("collapse");
	};
	this.performSlide = function(elem){
		this.setSelected(elem);
		if( $(this.dom).is(":visible") ){
			$(this.dom).find("li.collapse:not(.expand) ul,li.collapse:not(.expand) .actions").slideUp(100, function(){});
			$(this.dom).find("ul > li ").removeClass("selected");
			$(elem).children("ul, .actions").slideDown(100);
		}else{
			$(this.dom).find("li.collapse:not(.expand) ul,li.collapse:not(.expand) .actions").css({"display":"none"});
			$(this.dom).find("ul > li ").removeClass("selected");
			$(elem).children("ul, .actions").css({"display":"block"});
		}
		if( $(elem).hasClass("updaterelease") ){
			
		}

	};
	this.init = function(){
		this.dom = $(this.options.dom);
		var self = this;
		$(this.dom).find("li").each(function(index, elem){
			var a = $(elem).find("a:first");
			if( a.length == 0 ) return;
			
			$(a).off("click.accordion").on("click.accordion", function(ev){
				ev.preventDefault();
				$(this).parent().siblings().removeClass("selected");
				$(this).parent().addClass("selected");
				if( self.options.onClick.call(self.options.parent,this) !== false){
					self.performSlide(elem);
				}
				return false;
			});
		});
	};
	this.select = function(type, id, performslide){
		performslide = (typeof performslide === "boolean")?performslide:true;
		type = $.trim(type || "repoarea");
		id = $.trim(id || "");
		var self = this;
		$(this.dom).removeClass("repoarea").removeClass("updaterelease").addClass(type);
		if( $(this.dom).find("a." + type).length > 0 ){
			if( typeof id !== "undefined"){
				$(this.dom).find("a." + type ).each(function(i,e){
					if( $.trim($(this).find(".metadata .id").text()) == id ){
						if(type.toLowerCase()!="repoarea"){
							var par = $(this).parent().parent().parent().children("a:first");
							if(performslide !== false){
								self.performSlide(par);
							}
						}else{
							if( performslide !== false){
								self.performSlide($(this).parent());
							}
							
						}
					}
				});
				
			}else{
				$(this.dom).find("li a." + type + ":first").trigger("click.accordion");
			}
		}
	};
	this.init();
};
appdb.repository.ui.ShowVerifyDialog = function(o){
	var _dump = function(){};
	var options = {
		message: o.message || "",
		okcallback: o.onOk || _dump,
		oktext: o.ok || "ok",
		cancelcallback: o.onCancel || _dump,
		canceltext: o.cancel || "cancel",
		css: o.css || "",
		title: o.title || "Verify action"
	};
	var dom = $(document.createElement("div")).addClass("verifydialog");
	var verifymessage = $(document.createElement("div")).addClass("message").append(options.message);
	var okbutton = $(document.createElement("button")).append(options.oktext);
	var cancelbutton = $(document.createElement("button")).append(options.canceltext);
	var okcontainer = $(document.createElement("div")).addClass("action").addClass("ok").append(okbutton);
	var cancelcontainer = $(document.createElement("div")).addClass("action").addClass("cancel").append(cancelbutton);
	var actions = $(document.createElement("div")).addClass("actions").append(okcontainer).append(cancelcontainer);
	var _dialog = new dijit.Dialog({
		title: options.title,
		content: $(dom)[0],
		style: o.css || "max-width:650px;max-height:500px;"
	});
	
	$(dom).append(verifymessage).append(actions);	
	new dijit.form.Button({
		label: options.canceltext,
		onClick: function() {
			setTimeout(function(){
				_dialog.hide();
				_dialog.destroyRecursive(false);
			},10);
			options.cancelcallback();
		}
	},$(cancelbutton)[0]);
	new dijit.form.Button({
		label: options.oktext,
		onClick: function() {
			setTimeout(function(){
				_dialog.hide();
				_dialog.destroyRecursive(false);
			},10);
			options.okcallback();	
		}
	},$(okbutton)[0]);
	
	
	_dialog.show();
};

/******************************
 ***** MODELS SECTION ******
 *****************************/

appdb.repository.model.ProductReleaseProperty = function(opts, ext){
	var _init = function(){
		opt = opts || {};
		ext = ext || {};
		ext.caller = { // Post  array 'ordering' with tyhe new ordering of faqs 
			endpoint : appdb.config.repository.endpoint.base + "release/property?{id}&{*}"
		};
		ext.updateCaller = {
			endpoint : appdb.config.repository.endpoint.base + "release/property?id={id}&name={name}&value={value}",
			action: "POST"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};

appdb.repository.model.RepositoryAreaProperty = function(opts, ext){
	var _init = function(){
		opt = opts || {};
		ext = ext || {};
		ext.caller = { // Post  array 'ordering' with tyhe new ordering of faqs 
			endpoint : appdb.config.repository.endpoint.base + "repositoryarea/property?{id}&{*}"
		};
		ext.updateCaller = {
			endpoint : appdb.config.repository.endpoint.base + "repositoryarea/property?id={id}&name={name}&value={value}",
			action: "POST"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};

appdb.repository.model.RepositoryAreas = function(opts, ext){
	var _init = function(){
		opt = opts || {};
		ext = ext || {};
		ext.caller = {
			endpoint : appdb.config.repository.endpoint.base + "repositoryarea/list?swid={swid}"
		};
		return new appdb.ModelItemClass(opts, ext);
	};
	return _init();
};

appdb.repository.model.Targets = (function(){
	var _model = function(opts, ext){
		var _init = function(){
			opt = opts || {};
			ext = ext || {};
			ext.caller = { // Post  array 'ordering' with tyhe new ordering of faqs 
				endpoint : appdb.config.endpoint.base + "repository/target/list"
			};
			return new appdb.ModelItemClass(opts, ext);
		};
		return _init();
	};
	var _res = new _model();
	setTimeout(function(){
	_res.get();
        _res.getById = function(id){
           var local = this.getLocalData();
           local = local.target || [];
           local = $.isArray(local)?local:[local];
           for(var i=0; i<local.length; i+=1){
               if(local[i].id == id ){
                   return local[i];
               }
           }
           return null;
        };
	},900);
	
	return _res;
})();

appdb.repository.model.Config = (function(){
	var _model = function(opts, ext){
		var _init = function(){
			opt = opts || {};
			ext = ext || {};
			ext.caller = { // Post  array 'ordering' with tyhe new ordering of faqs 
				endpoint : appdb.config.endpoint.base + "repository/config/list"
			};
			ext.asyc = false;
			return new appdb.ModelItemClass(opts, ext);
		};
		return _init();
	};
	var _res = new _model();
	setTimeout(function(){
		_res.get();
	},900);
	return _res;
})();

/******************************
 ****** VIEWS SECTION *******
 *****************************/

appdb.repository.ui.views.ReleaseList = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleaseList", function(o){
	this.options = {
		parent: o.parent || null,
		template: {
			repoareaitem: $(document.createElement("div")),
			releaseitem: $(document.createElement("div")),
			basereleaseitem: $(document.createElement("div"))
		},
		dom: {
			repoarealist: null
		},
		data: []
	}
	this.removeRelease = function(id){
		var deleteseries = false;
		var self = this;
		$.each(this.options.data, function(i,e){
			self.options.data[i].productrelease = self.options.data[i].productrelease || [];
			self.options.data[i].productrelease = $.isArray(self.options.data[i].productrelease)?self.options.data[i].productrelease:[self.options.data[i].productrelease];
			var foundreleaseindex = false;
			$.each(self.options.data[i].productrelease, function(ii,ee){
				if( foundreleaseindex == false && id == ee.id ){
					foundreleaseindex = ii;
				}
			});
			if( foundreleaseindex !== false ){
				self.options.data[i].productrelease.splice(foundreleaseindex,1);
				if( self.options.data[i].productrelease.length == 0 ){
					deleteseries = self.options.data[i].id;
				}
			}
		});
		
		if( deleteseries !== false ){
			this.removeSeries(deleteseries);
			return;
		}
		
		var selection = this.getCurrentSelection();
		var dom = this.findReleaseDom(id);
		$(dom).remove();
		
		if( selection.release == id && selection.series ){
			this.selectSection("repoarea", selection.series);
			this.showRepoArea(selection.series);
		}
	};
	this.removeSeries = function(id){
		if( this.options.data && this.options.data.length > 0 ){
			var foundseriesindex = false;
			$.each(this.options.data, function(i,e){
				if( foundseriesindex == false && id == e.id){
					foundseriesindex = i;
				}
			});
			if( foundseriesindex !== false ){
				this.options.data.splice(foundseriesindex,1);
			}
		}
		var selection = this.getCurrentSelection();
		var dom = this.findRepositoryAreaDom(id);
		$(dom).remove();
		
		if( selection.series == id && this.options.data){
			if( this.options.data.length === 0 ){
				appdb.repository.ui.CurrentReleaseManager.renderEmpty();
			}else{
				this.selectSection("repoarea", this.options.data[0].id);
				this.showRepoArea(this.options.data[0].id);
			}
		}
	};
	this.showRelease = function(id, displayVersion){
		var e = this.findReleaseDom(id);
		var repoid, reponame, reposwname;
		if( $(e).parent().hasClass("selected") === false ){
			var ee = this.findReleaseDom(id);
			repoid = $(ee).data("repoid");
			reponame = $(ee).data("reponame");
			this.subviews.accordion.select("repoarea",repoid);
		}
		reposwname = $(e).data("swname");
		$(e).siblings().removeClass("selected");
		$(e).addClass("selected");
		if( !displayVersion ){
			$(this.dom).find(".updatelist .updaterelease .metadata").each(function(i,e){
				if( $.trim($(e).find(".id").text()) == id ){
					displayVersion = $(e).find(".displayVersion").text();
				}
			});
		}
		var crel = appdb.repository.ui.CurrentReleaseManager.getCurrentRelease();
		if( crel && crel.productrelease && crel.productrelease.id == id ){
			if( appdb.repository.ui.CurrentReleaseManager.options.currentContent ){
				appdb.repository.ui.CurrentReleaseManager.options.currentContent.setupReleaseRoute();
			}
			return;
		}
		this.getReleaseManager().loadRelease(id,displayVersion);
		this.publish({event: "loading", value: {type: "release", id:id, name:displayVersion,swname: reposwname, map:[{type:"repositoryarea", id:repoid, name:reponame, swname: reposwname}]}});
	};
	this.getReleaseByName = function(reponame,releasename){
		releasename = $.trim(releasename).toLowerCase() || "";
		if( releasename === "" ) return null;
		var res = [], repo = this.getRepoAreaByName(reponame);
		
		if( repo == null ) return null;
		repo.productrelease = repo.productrelease || [];
		repo.productrelease = $.isArray(repo.productrelease)?repo.productrelease:[repo.productrelease];
		res = $.grep(repo.productrelease, function(e){
			if( $.trim(e.displayversion).toLowerCase() == releasename ) {
				return true;
			}else{
				return false;
			}
		});
		
		if( res.length === 0 ){
			return null;
		}
		return res[0];
	};
	this.showRepoArea = function(id, reponame){
		var e = this.findRepositoryAreaDom(id);
		$(e).siblings().removeClass("selected");
		$(e).addClass("selected");
		var reposwname = $(e).data("swname");
		if( !reponame ){
			reponame = $(e).find(".repoarea > .metadata .repository").text();
		}
		this.subviews.accordion.select("repoarea",id);
		
		this.getReleaseManager().loadRepositoryArea(id,reponame);
		this.publish({event: "loading", value: {type: "repositoryarea", id:id, name:reponame, swname: reposwname, map:[]}} );
	};
	this.getRepoAreaByName = function(reponame){
		reponame = $.trim(reponame).toLowerCase() || "";
		var res = [];
		if( reponame!=="" && this.options.data && this.options.data.length>0){
			res = $.grep(this.options.data, function(e){
				if($.trim(e.name).toLowerCase() === reponame ){
					return true;
				}else{
					return false;
				}
			});
		}
		if( res.length === 0 ){
			return null;
		}
		return res[0];
	};
	this.onLinkClick = function(elem){
		var e = $(elem).parent();
		var type = $(e).data("type");
		var id = $(e).data("id");
		var repoid = $(e).data("repoid");
		var name = $(e).data("name");
		switch( type ){
			case "repoarea":
				this.showRepoArea(id, name,e);
				this.subviews.accordion.select("repoarea", id, false);
				return true;
			case "release":
				this.showRelease(id, name, repoid, e);
				this.subviews.accordion.select("updaterelease", id, false);
				if( $(e).parent().parent().hasClass("selected") === false ){
					return true;
				}
				return false;
			default:
				return false;
		}
	};
	this.renderRepoareaItem = function(d){
		d = d || {};
		var item = $(this.options.template.repoareaitem).clone(true).removeAttr("class");
		$(item).find(".repoarea .metadata:last .id").append(d.id);
		$(item).find(".repoarea .metadata:last .repository").append("<span>" + d.name + "</span>");
		
		var updatelist = $(item).find(".updatelist");
		$(updatelist).empty();
		if( d.productrelease ){
			d.productrelease = $.isArray(d.productrelease)?d.productrelease:[d.productrelease];
			$.each(d.productrelease, (function(self,list){
				return function(i,e){
					var li = self.renderReleaseItem(e,d);
					$(list).append(li);
				};
			})(this,updatelist));
		}
		$(item).data("id",d.id).data("type","repoarea").data("name",d.name).data("swname", d.swname);
		return item;
	};
	this.renderReleaseItem = function(d,repo){
		d = d  || {};
		var item = (d.parentid=="0")?$(this.options.template.basereleaseitem).clone(true):$(this.options.template.releaseitem).clone(true);
		$(item).removeAttr("class");
		var meta = $(item).find(".metadata");
		$(meta).find(".id").text(d.id);
		$(meta).find(".displayVersion").text(d.displayversion);
		$(item).data("id", d.id).data("repoid", d.repositoryarea.id).data("type","release").data("name", d.displayversion).data("reponame",d.repositoryarea.name).data("swname",repo.swname);
		var state = d.state.name.toLowerCase();
		$(item).find(".releasestatevalue").addClass(state).attr("title","This release is in " + state + " state").text(state[0]);
		return item;
	};
	this.render = function(d){
		$(this.dom).removeClass("hidden");
		$(this.options.dom.repoarealist).empty();
		d = d || [];
		d = $.isArray(d)?d:[d];
		$.each(d,(function(self){
			return  function(i,e){
				var li = self.renderRepoareaItem(e);
				$(self.options.dom.repoarealist).append(li);
			};
		})(this));
		
		this.subviews.accordion = new appdb.repository.ui.Accordion({container: $(this.options.dom.repoarealist), parent: this, onClick: this.onLinkClick});
	};
	this.renderEmpty = function(d){
		$(this.dom).empty().append("<div class='releaselist empty'>No releases found </div>");
	};
	this.hasRepositoryAreaId = function(id){
		var found = false;
		$.each(this.options.data, function(i,e){
			found = (e.id == id)?e:found;
		});
		return found;
	};
	this.findReleaseDom = function(id){
		var found = false;
		$(this.options.dom.repoarealist).find(".updatelist > li").each(function(i,e){
			var did = $(e).data("id");
			found = ($(e).data("id") == id)?$(e):found;
		});
		return found;	
	};
	this.findRepositoryAreaDom = function(id){
		var found = false;
		$(this.options.dom.repoarealist).children("li").each(function(i,e){
			var did = $(e).data("id");
			found = ($(e).data("id") == id)?$(e):found;
		});
		return found;
	};
	this.renderReleaseState = function(releaseid, statename){
		if( $.trim(statename) === "" ) return;
		
		var elem = null;
		$(this.dom).find(".updatelist > li > .updaterelease").each(function(i,e){
			if( elem !== null ) return;
			if( $.trim($(e).find(".metadata > .id").text()) == releaseid ){
				elem = e;
			}
		});
		if( elem ){
			var newstatename = $.trim(statename).toLowerCase();
			$(elem).find(".releasestatevalue:first").removeClass().addClass("releasestatevalue").addClass(newstatename).attr("title","This release is in " + newstatename + " state").text(newstatename[0]);
		}
	};
	this.renderReleaseDisplayVersion = function(releaseid,displayversion){
		if( $.trim(displayversion) === "" ) return;
		
		var elem = null;
		$(this.dom).find(".updatelist > li > .updaterelease").each(function(i,e){
			if( elem !== null ) return;
			if( $.trim($(e).find(".metadata > .id").text()) == releaseid ){
				elem = e;
			}
		});
		if( elem ){
			var newdisplayversion = $.trim(displayversion);
			$(elem).find(".metadata:first > .displayVersion").text(newdisplayversion);
		}
	};
	this.updateRepositoryDataProperty = function(id, property, value){
		var repoindex = -1;
		
		$.each(this.options.data, function(i,e){
			if( repoindex > -1 ) return;
			if( e.id == id){
				repoindex = i;
			}
		});
		
		if( repoindex === -1 ){
			return;
		}
		
		if( this.options.data[repoindex] ){
			if( !this.options.data[repoindex].hasOwnProperty(property) ){
				return;
			}
			this.options.data[repoindex][property] = value;
			if( property === "name" && value ){
				$(this.dom).find(".repoarealist > li > a.repoarea > .metadata").each(function(i,e){
					if( $(e).find(".id").text() == id){
						$(e).find(".repository").empty().append("<span>"+ value + "</span>");
					}
				});
			}
		}
	};
	this.updateReleaseDataProperty = function(id, property, value){
		var repoindex = -1;
		var releaseindex = -1;
		
		$.each(this.options.data, function(i,e){
			if( releaseindex > -1 ) return;
			$.each(e.productrelease, function(ii,ee){
				if( ee.id == id ){
					repoindex = i;
					releaseindex = ii;
				}
			});
		});
		
		if( repoindex === -1 || releaseindex === -1 ){
			return;
		}
		
		if( this.options.data[repoindex] && this.options.data[repoindex].productrelease){
			this.options.data[repoindex].productrelease = this.options.data[repoindex].productrelease || [];
			this.options.data[repoindex].productrelease = $.isArray(this.options.data[repoindex].productrelease)?this.options.data[repoindex].productrelease:[this.options.data[repoindex].productrelease];
			if( this.options.data[repoindex].productrelease.length <= releaseindex ){
				return;
			}
			if( !this.options.data[repoindex].productrelease[releaseindex].hasOwnProperty(property) ){
				return;
			}
			this.options.data[repoindex].productrelease[releaseindex][property] = value;
			if( property === "state" && value && value.name ){
				this.renderReleaseState(this.options.data[repoindex].productrelease[releaseindex].id, value.name);
			}
			if( property === "displayversion" && value ){
				this.renderReleaseDisplayVersion(this.options.data[repoindex].productrelease[releaseindex].id, value);
			}
		}
	};
	this.getReleaseData = function(id){
		var res = false;
		$.each(this.options.data, function(i,e){
			if( res !== false ) return;
			$.each(e.productrelease, function(ii,ee){
				if( res !== false) return;
				if(ee.id == id){
					res = ee;
				}
			});
		});
		return res;
	};
	this.getRepositoryDataIndex = function(id){
		var index = -1;
		$.each(this.options.data, function(i,e){
			if( index !== -1) return;
			if( e.id == id ){
				index = i;
			}
		});
		return index;
	};
	this.getReleaseDataIndex = function(repoindex,id){
		var index = -1;
		if( !this.options.data[repoindex] ) return -1;
		
		$.each(this.options.data[repoindex], function(i,e){
			if( index !== -1) return;
			if( e.id == id ){
				index = i;
			}
		});
		return index;
	};
	this.updateReleaseData = function(d){
		if( this.getReleaseData(d.id) ){
			var repoindex = (d && d.repositoryarea && d.repositoryarea.id)?this.getRepositoryDataIndex(d.repositoryarea.id):-1;
			if( repoindex == -1 ) return;
			var releaseindex = this.getReleaseDataIndex(repoindex,d.id);
			this.options.data[repoindex].productrelease = this.options.data[repoindex].productrelease || [];
			this.options.data[repoindex].productrelease = $.isArray(this.options.data[repoindex].productrelease)?this.options.data[repoindex].productrelease:[this.options.data[repoindex].productrelease];
			this.options.data[repoindex].productrelease[releaseindex] = d;
		}else{
			this.addReleaseData(d);
		}
	};
	this.addReleaseData = function(d){
		d = d.productrelease || d || {};
		d = $.isArray(d)?d[0]:d;
		if( !d.id ) return;
		var repoindex = (d && repositoryarea && repositoryarea.id)?this.getRepositoryDataIndex(d.repositoryarea):-1;
		if( repoindex == -1 ) return;
		
		this.options.data[repoindex].productrelease = this.options.data[repoindex].productrelease ||[];
		this.options.data[repoindex].productrelease = $.isArray(this.options.data[repoindex].productrelease)?this.options.data[repoindex].productrelease:[this.options.data[repoindex].productrelease];
		
		this.options.data[repoindex].productrelease.push(d);
		
	};
	this.addRepositoryData = function(d){
		this.options.data = this.options.data || [];
		d = d || {};
		d.productrelease = d.productrelease || [];
		d.productrelease = $.isArray(d.productrelease)?d.productrelease:[d.productrelease];
		var foundindex = -1;
		for(var i=0; i<this.options.data.length; i+=1){
			if( this.options.data[i].id == d.id ){
				foundindex = i;
				break;
			}
		}
		if( foundindex > -1){
			var repositoryarea = this.filterData({"repositoryarea": d});
			repositoryarea = repositoryarea.repositoryarea || repositoryarea;
			if( repositoryarea.length > 0 ){
				this.options.data[foundindex] = repositoryarea[0];
				return;
			}
		}else{
			this.options.data.push(d);
		}
	};
	this.addRepository = function(d,select){
		select = (typeof select==="boolean")?select:false;
		d = d || [];
		d = (d.repositroyarea)?d.repositroyarea:d;
		d = ($.isArray(d))?d:[d];
		if( d.length > 0) {d = d[0];} else {return;}
		this.addRepositoryData(d);
		var repo = this.hasRepositoryArea(d.id);
		if( repo === false ){
			$(this.options.dom.repoarealist).prepend(this.renderRepoareaItem(d));
		}else{
			repo = this.findRepositoryAreaDom(d.id);
			if( repo ){
				var li = this.renderRepoareaItem(d);
				$(repo).after(li);
				$(repo).remove();
			} else {
				$(this.options.dom.repoarealist).prepend(this.renderRepoareaItem(d));
			}
		}
		this.subviews.accordion = new appdb.repository.ui.Accordion({container: $(this.options.dom.repoarealist), parent: this, onClick: this.onLinkClick});
		if(select){
			this.subviews.accordion.select("updaterelease",d.id);
		}
	};
	this.filterData = function(d){
		var canmanage = appdb.repository.ui.CurrentReleaseManager.canManageReleases();
		var res = $.extend({},d);
		var repos = d.repositoryarea || [];
		repos = $.isArray(repos)?repos:[repos];
		var validrepos = [];
		$.each(repos, function(i,e){
			if( !canmanage ){
				var releases = e.productrelease || [];
				releases = $.isArray(releases)?releases:[releases];
				
				var validrel = $.grep(releases, function(rel){
					return ( $.inArray(rel.state.name.toLowerCase(),["production","candidate"]) > -1 )?true:false;
				});
				
				if( validrel.length === 0 ){
					return;
				}
				e.productrelease = validrel;
			}
			validrepos.push(e);
		});
		
		res.repositoryarea = validrepos;
		return res;
	};
	this.load = function(o){
		if( this._model ){
			this._model.unsubscribeAll(this);
		}
		this._model = new appdb.repository.model.RepositoryAreas();
		this._model.subscribe({event: "select", callback: function(d){
			if( d.error ) return;
			d = this.filterData(d);
			$(this.dom).find(".releaselist").remove();
			this.options.data = d.repositoryarea;
			if( d.repositoryarea.length > 0 ){
				this.render(d.repositoryarea);
			}
			this.publish({event: "load", value:d});
		}, caller: this}).subscribe({event: "error", callback: function(err){
			this.publish({event: "error", value:err});
		},caller: this});
		this._model.get({swid: o.swid});
		appdb.pages.application.requests.register(this._model, "repositoryarea" + appdb.pages.application.currentId());
	};
	this.hasRepositoryArea = function(){
		return ($(this.dom).find(".repoarealist .repoarea").length > 0 )?true:false;
	};
	this.selectSection = function(type, id){
		if( this.subviews.accordion ){
			this.subviews.accordion.select(type,id);
		}
	};
	this.getCurrentSelection = function(){
		var seriesid = $(this.dom).find(".repoarealist > li.selected > a > .metadata .id").text();
		var releaseid = $(this.dom).find(".repoarealist > li.selected > .updatelist > li.selected > a >.metadata .id").text();
		return {"series": seriesid, "release": releaseid};		
	};
	this.getReleaseManager = function(){
		return this._releaseManager;
	};
	this.init = function(){
		this.dom = $(o.container);
		this._releaseManager = o.releaseManager || null;
		this.options.template.repoareaitem = $(this.dom).find(".template.repositoryarealistitem:last").clone(true).removeClass("template");
		this.options.template.releaseitem = $(this.dom).find(".template.releaselistitem:last").clone(true).removeClass("template");
		this.options.template.basereleaseitem = $(this.dom).find(".template.basereleaselistitem:last").clone(true).removeClass("template");
		this.options.dom.repoarealist = $(this.dom).find(".repoarealist.baselist");
		$(this.dom).find(".template").remove();
	};
	this.init();
});
appdb.repository.ui.editors = {};

appdb.repository.ui.editors.longtext = function(o){
	this.value = appdb.utils.SimpleProperty();
	this.getResult = function(){
		if( this.domeditor ){
			return $(this.domeditor).val();
		}
		return this.value();
	};
	this.clearAll =  function(){
		$(this.domeditor).val("");
	};
	this.destroy = function(){
		$(this.dom).remove();
		this.domeditor = null;
	};
	this.render = function(){
		$(this.dom).val(this.value());
		this.domeditor = $(this.dom).tinymce({
			// General options
			theme : "advanced",
			plugins : "paste,autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,advlist",
			// Theme options
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "cut,copy,paste,|,search,replace,|,bullist,numlist,|,outdent,indent,|,undo,redo,|,link,image,hr,|,insertdate,inserttime,|,forecolor,backcolor",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,
			// Example content CSS (should be your site CSS)
			//content_css : "css/content.css",
			// Drop lists for link/image/media/template dialogs
			template_external_list_url : "lists/template_list.js",
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
			media_external_list_url : "lists/media_list.js",
                        paste_auto_cleanup_on_paste : false,
			setup: (function(self){
						return function(ed) {
							ed.onKeyUp.add(function(ed, e) {
							});
							ed.onMouseUp.add(function(ed, e) {
							});
						};
					})(this)
		});
	};
	this.init = function(){
		this.dom = $(o.container);
		this.value( o.data || "");
	};
	this.init();
};

appdb.repository.ui.views.DataProperty = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.DataProperty", function(o){
	this._model = null;
	this.dataModelProperty = null;
	
	this.getDataValue = function(){
		var res = "";
		if( this.dataPath ){
			res = this.dataValue[this.dataPath];
		}else if( typeof this.dataValue.val === "function" ){
			res = this.dataValue.val();
		}
		res = res.replace(/^\<\!\[CDATA\[/,"");
		res = res.replace(/\]\]\>$/,"");
		res = res.replace(/”/g,'"');
		res = res.replace(/’/g,"'");
		return res;
	};
	this.setDataValue = function(value, silent){
		silent = silent || false;
		this.cancel();
		if( typeof value === "string"){
			v = "" + value;
		}else if(typeof value[this.dataPath] !== "undefined"){
			v = value[this.dataPath];
		}else{
			return;
		}
		v = v.replace(/^\<\!\[CDATA\[/,"");
		v = v.replace(/\]\]\>$/,"");
		this.dataValue[this.dataPath] =  v;
		this.render();
		if( !silent ){
			this.publish({event: "changed", value: {datavalue: this.dataValue, datapath: this.dataPath, modelproperty: this.dataModelProperty}});
		}
	};
	this.renderActions = function(){
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() && this.dataEditable == true ) {
			var edit = $("<a class='action edit'><img src='/images/editicon.png' alt=''/><span>edit</span></a>");
			$(edit).off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.edit();
					return false;
				};
			})(this));
			
			var clearall = $("<a class='action save hidden'><img src='/images/stop.png' alt=''/><span>clear</span></a>");
			$(clearall).off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.clearAll();
					return false;
				};
			})(this));
			
			var save = $("<a class='action save hidden'><img src='/images/diskette.gif' alt=''/><span>save</span></a>");
			$(save).off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.save();
					return false;
				};
			})(this));

			var cancel = $("<a class='action cancel hidden'><img src='/images/cancelicon.png' alt=''/><span>cancel</span></a>");
			$(cancel).off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.cancel();
					return false;
				};
			})(this));
			$(this.dom).find(".actions").append(edit).append(clearall).append(save).append(cancel);
		}
		var totop =  $("<a class='action totop'><img src='/images/up_gray.png' alt=''/><span>top</span></a>");
		$(totop).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.totop();
				return false;
			};
		})(this));
		
		$(this.dom).find(".actions").append(totop);
	};
	this.renderEmptyValues = function(){
		$(this.dom).remove(".emptymessage");
		$(this.dom).find(".value.empty").after("<div class='emptymessage'>" + appdb.repository.ui.views.DataProperty.emptymessage + "</div>");
	};
	this.renderEmptyWarning = function(){
		$(this.dom).find(".hideemptymessage").remove();
		if( $(this.dom).hasClass("hideempty") && appdb.repository.ui.CurrentReleaseManager.canManageReleases() && this.dataEditable == true ){
			var div = $(document.createElement("div")).addClass("icontext").addClass("hideemptymessage");
			var img = $(document.createElement("img"));
			var span = $(document.createElement("span"));
			$(img).attr("src","/images/repository/warning.png").attr("alt","");
			$(span).text("This property won't be visible to non-authorized users if left empty");
			
			$(div).append(img).append(span);
			$(this.dom).find(".header").after(div);
		}
	};
	this.renderMandatoryWarning = function(){
		$(this.dom).find(".mandatorymessage").remove();
		if( $(this.dom).hasClass("mandatory") && appdb.repository.ui.CurrentReleaseManager.canManageReleases() && this.dataEditable == true ){
			var div = $(document.createElement("div")).addClass("icontext").addClass("mandatorymessage");
			var img = $(document.createElement("img"));
			var span = $(document.createElement("span"));
			$(img).attr("src","/images/repository/redwarning.png").attr("alt","");
			$(span).text("This property is mandatory for publishing into production");
			
			$(div).append(img).append(span);
			$(this.dom).find(".header").after(div);
		}
	}
	this.renderError = function(d){
		$(this.dom).find(".errormessage").remove();
		$(this.dom).find(".header").after("<div class='errormessage'><img src='/images/stop.png' alt=''></img><span>"+d.error+"</span><a class='close' title='close error message'><img src='/images/closeview.png' alt='' ></img></a></div>");
		setTimeout((function(self){
			return function(){
				$(self.dom).find(".errormessage a.close").off("click").on("click", function(ev){
					ev.preventDefault();
					setTimeout(function(){
						$(self.dom).find(".errormessage").remove();
					},1);
					return false;
				});
			};
		})(this),1);
		$(this.dom).find(".actions .action").removeClass("hidden");
		$(this.dom).find(".actions .action.edit").addClass("hidden");
	};
	this.renderLoading = function(loading, text){
		loading = (typeof loading === "boolean")?loading:false;
		$(this.dom).find(".loader").remove();
		$(this.dom).find(".errormessage").remove();
		if( loading ){
			text = text || "saving";
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).find(".header").after(loader);
			$(this.dom).find(".actions .action").addClass("hidden");
		}
	}
	this.render = function(d){
		d = d || this.dataValue;
		$(this.dom).find(".emptymessage").remove();
		$(this.dom).find(".errormessage").remove();
		$(this.dom).find(".value").removeClass("empty").empty();
		$(this.dom).find(".actions").empty();
		var dval = this.getDataValue();
		
		if( d.hasOwnProperty(this.dataPath) ){
			if($.trim(d[this.dataPath]) === ""){
				$(this.dom).find(".value").addClass("empty");
			}else{
				$(this.dom).find(".value").removeClass("hidden").removeClass("empty");
			}
			$(this.dom).find(".value").empty().append(dval);
			if($.trim(dval)==="" && $(this.dom).hasClass("hideempty") ){
				if(!userID || !appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
					$(this.dom).addClass("hidden");
				}else{
					$(this.dom).removeClass("hidden");
				}
			}
		}
		$(this.dom).addClass( $(this.dom).find(".header:first").text().toLowerCase().replace(/\ /g,"") );
		this.renderEmptyValues(d);
		this.renderActions(d);
		this.renderEmptyWarning();
		this.renderMandatoryWarning();
	};
	this.edit = function(){
		$(this.dom).find(".actions .action").removeClass("hidden");
		$(this.dom).find(".actions .action.edit").addClass("hidden");
		$(this.dom).find(".valueeditor").remove();
		$(this.dom).find(".value").addClass("hidden").after("<textarea class='valueeditor'></textarea>");
		$(this.dom).find(".emptymessage").addClass("hidden");
		this.editor = new appdb.repository.ui.editors[this.dataType]({container: $(this.dom).find(".valueeditor:last"), data: $(this.dom).find(".value").html()});
		this.editor.render();
	};
	this.save = function(){
		var self = this;
                var editorval = $("<div>"+this.editor.getResult()+"</div>");
                $(editorval).find("a").each(function(i,e){
                    $(e).attr("target","_blank");
                });
                editorval = $(editorval).html();
				
		var val = ($.trim($(editorval).text()) !== "")? "<![CDATA[" + (editorval) + "]]>":"";
		this.renderLoading(true);
		
		this._model.unsubscribeAll(this).subscribe({event: "update", callback: function(v){
				self.renderLoading(false);
				if( v.error ){
					self.renderError(v);
				}else{
					self.setDataValue((this.dataModelProperty)?v[this.dataModelProperty]:v);
				}
			},
			caller: this
		}).subscribe({event: "error", callback: function(v){
			self.renderLoading(false);
			self.renderError({error: v.description});
		},caller: this}).update({
			query:{
				id: this.dataValue.id,
				name: this.dataPath,
				value: ""
			},
			data:{ value: encodeURIComponent(val) }
		});
		this.renderEmptyWarning();
	};
	this.clearAll = function(){
		this.editor.clearAll();
		this.renderEmptyWarning();
	};
	this.cancel = function(){
		$(this.dom).find(".actions .action").removeClass("hidden");
		$(this.dom).find(".actions .action.save").addClass("hidden");
		$(this.dom).find(".actions .action.cancel").addClass("hidden");
		$(this.dom).find(".errormessage").remove();
		$(this.dom).find(".emptymessage").remove();
		if( $.trim($(this.dom).find(".value").text() ) === "" ){
			$(this.dom).find(".value").empty().addClass("empty");
		}else{
			$(this.dom).find(".value").removeClass("empty");
		}
		$(this.dom).find(".value").removeClass("hidden");
		this.renderEmptyValues();
		if( this.editor ){
			this.editor.destroy();
		}
	};
	this.totop = function(){
		window.scrollTo(0, 0);
	};
	this._init = function(){
		this.dom = $(o.container);
		this.dataValue = o.data || {};
		this.dataType = $(this.dom).data("type") || "text";
		this.dataType = this.dataType.toLowerCase();
		this.dataPath = $(this.dom).data("path") || "";
		this.dataEditable = o.editable || $(this.dom).data("editable");
		if( typeof this.dataEditable === "undefined")  this.dataEditable = true;
		this.dataModelProperty = o.modelProperty || this.dataModelProperty ;
		if( o.model ){
			if( typeof o.model === "string" && appdb.repository.model[o.model] ){
				this._model = new appdb.repository.model[o.model]();
			}else if( typeof o.model === "string" ){
				var ns = appdb.FindNS(o.model);
				if( ns !== null && typeof ns === "object" ){
					this._model = new ns();
				}
			} else {
				this._model = new o.model();
			}
		}
	};
	this._init();
},{
	emptymessage: "No information provided yet"
});

appdb.repository.ui.views.ReleaseProperty = appdb.ExtendClass(appdb.repository.ui.views.DataProperty, "appdb.repository.ui.views.ReleaseProperty", function(o){
	this._model = new appdb.repository.model.ProductReleaseProperty();
	this.dataModelProperty = "productrelease";
});

appdb.repository.ui.views.DropDownList = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.DropDownList", function(o){
	this.options = {
		parent: o.parent || null,
		dom: {},
		data: o.data || [],
		selectedIndex: -1,
		dump: function(){return false;},
		onRender: null,
		title: o.title || "",
		useTitle: ($.trim(o.title)!=="")?true:false
	};
	this.renderItem = function(d,i){
		var div = $(document.createElement("div")).addClass("dropdownitem");
		var html = this.options.onRender(d);
		if( html !== false ){
			$(div).append(html);
			$(div).data("itemdata",d);
			$(div).data("index",i);
			return div;
		}
		return false;
	};
	this.renderList = function(d){
		var self = this;
		var ul = $(document.createElement("ul")).addClass("hidden");
		
		$.each(d, function(i,e) {
			var li = $(document.createElement("li"));
			var html = self.renderItem(e,i);
			if( html !== false ){
				$(html).data("listindex",i).off("click.dropdownlist").on("click.dropdownlist", function(ev){
					ev.preventDefault();
					self.selectItem($(this).data("index"));
					return false;
				});
				$(li).append(html);
				$(ul).append(li);
			}
		});
		$(ul).off("mouseover").on("mouseover", function(ev){
			$(self.dom).addClass("hover").addClass("expand");
		}).off("mouseout").on("mouseout", function(ev){
			$(self.dom).removeClass("hover");
		});
		return ul;
	};
	this.renderTitle = function(){
		var li = $(document.createElement("li"));
		var div = $(document.createElement("div")).append(this.options.title);
		var html = $(document.createElement("div")).addClass("dropdownitem").addClass("listtitle").append(div);
		$(li).append(html);
		return li;
	};
	this.render = function(d,selectindex){
		d = d || [];
		d = $.isArray(d)?d:[d];
		this.options.data = d || this.options.data;
		selectindex = (typeof selectindex === "undefined")?-1:selectindex;
		var self = this;
		$(this.dom).empty();
		this.options.dom.selectcontainer = $(document.createElement("div")).addClass("selectcontainer");
		this.options.dom.selectaction = $(document.createElement("div")).addClass("dropaction");
		this.options.dom.selecteditem = $(document.createElement("div")).addClass("selecteditem");
		this.options.dom.list = this.renderList(d);
		
		$(this.options.dom.selectcontainer).append(this.options.dom.selectaction);
		$(this.options.dom.selectcontainer).append(this.options.dom.selecteditem);
		$(this.options.dom.selectaction).append("<span>▼</span>");
		
		$(this.dom).append(this.options.dom.selectcontainer);
		$(this.dom).append(this.options.dom.list);
		
		this.selectItem(selectindex);
		
		$(this.options.dom.selecteditem).off("mouseover.dropdownlist").on("mouseover.dropdownlist", function(ev){
			ev.preventDefault();
			$(self.dom).addClass("hover");
			return false;
		}).off("mouseout.dropdownlist").on("mouseout.dropdownlist", function(ev){
			ev.preventDefault();
			$(self.dom).removeClass("hover");
			return false;
		}).off("click.dropdownlist").on("click.dropdownlist", function(ev){
			ev.preventDefault();
			if( $(self.dom).hasClass("expand") ){
				$(self.dom).removeClass("expand");
			}else{
				$(self.dom).addClass("expand");
			}
			return false;
		});
	};
	this.getSelectedIndex = function(){
		return (typeof this.options.selectedIndex === "undefined")?-1:this.options.selectedIndex;
	};
	
	this.selectItem = function(index){
		var isChanged = false;
		if( index == -1 && this.options.useTitle){
			var title = this.renderTitle();
			$(this.options.dom.selecteditem).empty().append($(title).find(".dropdownitem"));
			this.publish({event: "change", value: {}});
			return;
		}else if( index == -1){
			index = 0;
		}
		
		if( this.getSelectedIndex() != index ){
			isChanged = true;
		}
		
		this.options.selectedIndex = index;
		$(this.options.dom.list).remove();
		this.options.dom.list = this.renderList(this.options.data);
		$(this.dom).append(this.options.dom.list);
		
		var item = $(this.options.dom.list).find("li").get(index);
		var dropdownitem = $(item).find(".dropdownitem:first").clone(true);
		var itemdata = $(dropdownitem).data("itemdata");
		$(dropdownitem).off("click.dropdownlist");
		$(this.options.dom.selecteditem).empty().append(dropdownitem);
		$(this.dom).removeClass("expand");
		this.removeItem(index);
		if(isChanged && this.getSelectedItem() !== null){
			this.publish({event: "change", value: itemdata});
		}
	};
	this.removeItem = function(index){
		var lis = $(this.options.dom.list).children("li");
		if( $(lis).length <=index ){
			return;
		}
		$($(lis).get(index)).remove();
	};
	this.filterItems = function(){
		
	};
	this.getSelectedItem = function(){
		return $(this.options.dom.selecteditem).find("div:first").data("itemdata") || null;
	};
	this.setSelectedData = function(data){
		$(this.options.dom.selecteditem).find("div:first").data("itemdata",data);
	};
	this.init = function(){
		this.dom = $(o.container);
		this.options.onRender = o.onRenderItem || this.options.dump;
		$(this.dom).addClass("dropdownlist");
		if( typeof o.css === "string"){
			$(this.dom).addClass(o.css);
		}
		$(this.dom).off("mouseout").on("mouseout", function(ev){
			if( $(this).hasClass("hover") === false ){
				$(this).removeClass("expand");	
			}
		}).off("mouseover").on("mouseover", function(ev){
			$(this).addClass("hover");
		});
	};
	this.init();
},{
	title: "Please select a value"
});
appdb.repository.ui.views.ReleaseContact = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleaseContact", function(o){
	this.options = {
		parent: o.parent || null,
		data: o.data || null,
		title: o.title || "contact",
		name: o.name || "contact",
		email: o.email || "",
		associatedType: $.trim(o.associatedType) || "release",
		contacttype: 1,
		viewonly: false
	};
	this.getRelease = function(){
		return this.options.parent.getRelease();
	};	
	this.getRepoArea = function(){
		return this.options.parent.getRelease();
	};
	this.transformAppDBContactData = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var res = [];
		var uniqs = {};
		
		//find uniques
		$.each(d, function(i,e){
			var email = "";
			var contact = e.contact || [];
			contact = $.isArray(contact)?contact:[contact];
			$.each(contact, function(ii,ee){
				if( $.trim(ee.type)==="e-mail" && $.trim(ee.primary) === 'true'){
					email = ee.val();
				}
			});
			if( email && !uniqs[email] ){
				uniqs[email] = e; 
			}
		});
		var release = {};
		if( this.options.associatedType === "release"){
			release = this.getRelease();
		}else{
			release = this.getRepoArea();
		}
		
		release = release || {};
		//gather uniqs and transform them
		for(var i in uniqs){
			if(uniqs.hasOwnProperty(i)){
				var c = uniqs[i];
				res.push({
					associatedid: release.id,
					associatedtype: this.options.associatedType || 'release',
					externalid: c.id,
					contacttype: this.options.contacttype.id,
					firstname: c.firstname,
					lastname: c.lastname,
					email: i
				});
			}
		}
		return res;
	};
	this.getCurrentContacts = function(){
		return this.options.data || [];
	};
	this.getUniqueContacts = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var uniqs = {};
		var res = [];
		$.each(d, function(i,e){
			if(e.email && !uniqs[e.email]){
				uniqs[e.email] = true;
				res.push(e);
			}
		});	
		return res;
	};
	this.getAvailableContacts = function(data){
		var dd = appdb.pages.application.currentData();
		if( !dd || !dd.application){
			return [];
		}
		dd = dd.application || [];
		var res = (data)?[data]:[];
		var available = [];
		var self = this;
		
		res = res.concat( $.isArray(dd.addedby)?dd.addedby:[dd.addedby]);
		res = res.concat( $.isArray(dd.contact)?dd.contact:[dd.contact] );
		res = res.concat( $.isArray(dd.owner)?dd.owner:[dd.owner] );
		
		res = this.transformAppDBContactData(res);
		res = res.concat(this.getCurrentContacts());
		res = this.getUniqueContacts(res);
		
		$.each(res, function(i,e){
			var found = false;
			$.each(self.options.data, function(ii,ee){
				if(data && $.trim(ee.email) === $.trim(data.email) ) return;
				if(ee.email === e.email) found = true;
			});
			if( !found ){
				available.push(e);
			}
		});
		
		var release = this.getRelease() || {};
		available = available || [];
		available.push({
			customdata: true,
			associatedid: release.id,
			associatedtype: this.options.associatedType,
			externalid: "0",
			contacttype: this.options.contacttype.id,
			firstname: "",
			lastname: "",
			email: ""
		});
		return available;
	};
	this.addContactItem = function(d){
		var dd = $.grep(this.options.data, function(e){
			return true;
		});
		dd.push(d);
		this.options.data = [];
		this.options.data = this.options.data.concat(dd);
		this.render(this.options.data);
	};
	this.saveEditContact = function(){
		var data = this.options.editlist.getSelectedItem();
		if( data === null ){
			appdb.debug("Failed to retrieve selected item data");
			return;
		}
		
		this.renderLoading(true, "adding new contact");
		$.ajax({
			url: appdb.config.repository.endpoint.base + "contacts/add",
			action: "POST",
			type: "POST",
			data: data,
			success: (function(self){
				return function(v){
					var d = appdb.utils.convert.toObject(v);
					self.renderLoading(false);
					if( d.error ){
						self.renderError(d.error);
					}else{
						if( !data.id ){
							data.id = d.id || -1;
						} 
						if(typeof data.contacttype === "number"){
							data.contacttype = {id: data.contacttype};
						}
						self.addContactItem(data);
					}
				};
				})(this),
			error: (function(self){
				return function(err){
					self.renderLoading(false);
				};
			})(this)
		});
	};
	this.cancelEditing = function(){
		$(this.dom).find(".editor").remove();
		this.render();
	};
	this.validateCustomContactData = function(val, type, dom, display){
		var err = [];
		type = type || "email";
		val = val || "";
		
		if( val.indexOf(" ") > -1 ){
			err.push("Value '"+display+"' must not contain empty spaces.");
		}
		if( $.trim(val.replace(/[\ \t]/g,"")) === "" ){
			err.push("All fields are required");
		}
		switch(type){
			case "firstname":
			case "lastname":
				if(val.length > 50 || val.length < 3){
					err.push("Value '"+display+"' must have length from 3 to 50 characters long.");
				}
				break;
			case "email":
				if( !/^\S+@\S+\.\S+$/.test(val) ){
					err.push("Email does not seem valid.");
				}
				break;
		}
		
		err = (err.length > 0)?err[0]:"";
		if( parent && err){
			$(dom).parent().parent().find(".validation:last").addClass("error");
			$(dom).parent().parent().find(".validation:last").html("<span>"+ err + "</span>");
			if( dom ){
				$(dom).addClass("error");
			}
		}else{
			$(dom).parent().parent().find(".validation").removeClass("error").empty();
			if( dom ){
				$(dom).removeClass("error");
			}
		}

		if( $(this.options.editlist.dom).find(".selecteditem input.error").length > 0 ) {
			$(this.options.editlist.dom).find(".action.save").addClass("hidden");
		}else{
			$(this.options.editlist.dom).find(".action.save").removeClass("hidden");
		}
		return err;
	};
	this.addNewContact = function(){
		$(this.dom).find(".editor").remove();
		$(this.dom).find(".emptymessage").remove();
		var editor = $(document.createElement("div")).addClass("editor");
		var self = this;
		var actions = $(document.createElement("div")).addClass("actions");
		var save = $(document.createElement("a")).addClass("action").addClass("save").addClass("icontext");
		var cancel = $(document.createElement("a")).addClass("action").addClass("cancel").addClass("icontext");
		
		$(save).append("<img src='/images/diskette.gif' alt=''/><span>save</span>").off("click").on("click", function(ev){
			ev.preventDefault();
			self.saveEditContact();
			return false;
		});
		
		$(cancel).append("<img src='/images/cancelicon.png' alt=''/><span>cancel</span>").off("click").on("click", function(ev){
			ev.preventDefault();
			self.cancelEditing();
			return false;
		});
		
		
		this.options.editlist = new appdb.repository.ui.views.DropDownList({container: editor, parent: this, onRenderItem: function(d){
				if( d.customdata ){
					return self.renderCustomContact(d);
				}
				return self.renderContactItem(d);
		}});
		this.options.editlist.subscribe({event: "change", callback: function(v){
				$(this.options.editlist.dom).find(".action.save").removeClass("hidden");
				if( v.customdata ){
					$(this.options.editlist.dom).find(".selecteditem .customcontact input").each(function(i,e){
						$(e).focus();
					});
				}
		}, caller: this});
		this.options.editlist.render(this.getAvailableContacts());
		$(actions).append(save).append(cancel);
		$(editor).append(actions);
		$(this.dom).find(".contents").prepend(editor);
	};
	this.getContactTypeId = function(){
		return this.options.contacttype.id || 1;
	};
	this.renderContactItem = function(e){
		var item = $(document.createElement("div")).addClass("contactitem").data("itemdata",e);
		var img = $(document.createElement("img"));
		var email = $(document.createElement("span")).addClass("email");
		$(item).append(img).append("<span class='name'><span class='firstname'>" + e.firstname + "</span><span class='lastname'>" + e.lastname + "</span></span>");
		$(img).attr("src", "/people/getimage?id=" + (e.externalid || -1));
		if( userID ){
			$(email).text(e.email);
		}else{
			email = $(document.createElement("img")).addClass("email");
			$(email).attr("src", e.email).attr("alt","");
		}
		$(item).append(email);
		return item;
	};
	this.renderContacts = function(d,type){
		type = type || "contact";
		var self = this;
		var dom = $(document.createElement("div")).addClass("contents").data("type",type);
		var empty = $(document.createElement("div")).addClass("emptymessage").html("No information provided yet");
		
		var ul = $(document.createElement("ul"));
		
		$.each(d, function(i,e){
			var li = $(document.createElement("li"));
			var item = self.renderContactItem(e);
			$(li).append(item);
			$(ul).append(li);
			if( self.canEditContacts() ){
				self.renderContactActions(item,type);
			}
		});
		
		if($(ul).find("li").length === 0 ){
			$(dom).append(empty);
		}else{
			$(dom).append(ul);
		}
		
		return dom;
	};
	this.renderCustomContact = function(){
		var div = $(document.createElement("div")).addClass("customcontact");
		var self = this;
		var validation = $(document.createElement("div")).addClass("validation");
		var listview = $(document.createElement("div")).addClass("listview");
		$(listview).append("<span>Provide external contact</span>");
		
		var header = $(document.createElement("div")).addClass("header");
		$(header).append("<span>Provide external contact information</span>");
		
		var fnamecont = $(document.createElement("div")).addClass("container").addClass("firstname");
		var fnamefield = $(document.createElement("div")).addClass("field").append("<span>First name:</span>");
		var fnamevalue = $(document.createElement("input")).attr("name","firstname").addClass("value");
		$(fnamecont).append(fnamefield).append(fnamevalue);
		
		var lnamecont = $(document.createElement("div")).addClass("container").addClass("lastname");
		var lnamefield = $(document.createElement("div")).addClass("field").append("<span>Last name:</span>");
		var lnamevalue = $(document.createElement("input")).attr("name","lastname").addClass("value");
		$(lnamecont).append(lnamefield).append(lnamevalue);
		
		var emailcont = $(document.createElement("div")).addClass("container").addClass("email");
		var emailfield = $(document.createElement("div")).addClass("field").append("<span>E-mail:</span>");
		var emailvalue = $(document.createElement("input")).attr("name","email").addClass("value");
		$(emailcont).append(emailfield).append(emailvalue);
		
		$(div).append(listview).append(header).append(fnamecont).append(lnamecont).append(emailcont).append(validation).data("itemdata",{});
		
		$(lnamevalue).off("change").on("change",function(ev){
			ev.preventDefault();
			self.validateCustomContactData($(this).val(),"lastname",this, "last name");
			var d = self.options.editlist.getSelectedItem();
			d.lastname = $(this).val();
			self.options.editlist.setSelectedData(d);
			return false;
		}).off("focus").on("focus", function(ev){
			ev.preventDefault();
			self.validateCustomContactData($(this).val(),"lastname",this, "last name");
			return false;
		}).off("click").on("click",function(ev){
			ev.preventDefault();
			return false;
		}).off("keyup").on("keyup",function(){
			self.validateCustomContactData($(this).val(),"lastname",this, "last name");
		});
		
		$(fnamevalue).off("change").on("change",function(ev){
			ev.preventDefault();
			self.validateCustomContactData($(this).val(),"firstname",this, "first name");
			var d = self.options.editlist.getSelectedItem();
			d.firstname = $(this).val();
			self.options.editlist.setSelectedData(d);
			return false;
		}).off("focus").on("focus", function(ev){
			ev.preventDefault();
			self.validateCustomContactData($(this).val(),"firstname",this, "first name");
			return false;
		}).off("click").on("click",function(ev){
			ev.preventDefault();
			return false;
		}).off("keyup").on("keyup",function(){
			self.validateCustomContactData($(this).val(),"firstname",this, "first name");
		});
		
		$(emailvalue).off("change").on("change",function(ev){
			ev.preventDefault();
			self.validateCustomContactData($(this).val(),"email",this, "email");
			var d = self.options.editlist.getSelectedItem();
			d.email = $(this).val();
			self.options.editlist.setSelectedData(d);
			return false;
		}).off("focus").on("focus", function(ev){
			ev.preventDefault();
			self.validateCustomContactData($(this).val(),"email",this, "email");
			return false;
		}).off("click").on("click",function(ev){
			ev.preventDefault();
			return false;
		}).off("keyup").on("keyup",function(){
			self.validateCustomContactData($(this).val(),"email",this, "email");
		});
		
		return div;
	};
	this.renderContactActions = function(el){
		var d = $(el).data("itemdata") || {};
		var actions = $(document.createElement("div")).addClass("actions");
		var remove = $(document.createElement("a")).addClass("action").addClass("remove");
		
		$(remove).append("<span>remove</span>").off("click").on("click",(function(self,dom,data){
			return function(ev){
				ev.preventDefault();
				self.removeContact(data);
				return false;
			};
		})(this,el,d));
		
		$(actions).append(remove);
		$(el).append(actions);
	};
	this.renderActions = function(el){
		if( this.options.data.length >= appdb.repository.ui.views.ReleaseContact.maxcount ){
			$(el).children(".actions").remove();
			return;
		}
		var actions = $(document.createElement("div")).addClass("actions");
		var add = $(document.createElement("a")).addClass("action");
		$(add).append("<span>add contact</span>").off("click").on("click",(function(self){
			return function(ev){
				ev.preventDefault();
				self.addNewContact();
				return false;
			};
		})(this));
		
		$(actions).append(add);
		$(el).append(actions);
	};
	this.render = function(d){
		if(d && $.isArray(d) === false ){
			if( d.contact ){
				d = d.contact;
			}else if( d.contacttype ){
				d = d;
			}else {
				d = [];
			}
		}
		if( d ){
			d = $.isArray(d)?d:[d];
		}
		
		this.options.data = d || this.options.data || [];
		var self = this;
		
		$(this.dom).empty();
		
		this.options.data = $.grep(this.options.data, function(e){
			if( e.contacttype && e.contacttype.id ){
				return ( e.contacttype.id == self.options.contacttype.id)?true:false;
			}else if( typeof e.contacttype === "number"){
				return ( e.contacttype == self.options.contacttype.id)?true:false;
			}
			return false;
		});
		var genlist = this.renderContacts(this.options.data,"contact");
		
		var gendom = $(document.createElement("div")).addClass("group").addClass("general");
		$(gendom).append("<div class='header'>" + this.options.title + "</div>");
		$(gendom).append(genlist);
		
		$(this.dom).append(gendom);
		
		if( this.canEditContacts() ){
			this.renderActions(gendom, "contact");
		}
		
		$(this.dom).off("click").on("click", function(ev){
			ev.preventDefault();
			return false;
		});
	};
	this.renderLoading = function(enable, text){
		enable = (typeof enable === "boolean")?enable:true;
		$(this.dom).find(".loader").remove();
		$(this.dom).find(".errormessage").remove();
		if( enable ){
			text = text || "loading";
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).find(".group").append(loader);
		}
	};
	this.removeContactItem = function(data){
		var dd = $.grep(this.options.data, function(e){
			return ( e.email !== data.email )?true:false;
		});
		this.options.data = [];
		this.options.data = this.options.data.concat(dd);
		this.render(this.options.data);
	};
	this.removeContact = function(data){
		this.renderLoading(true, "removing contact");
		
		$.ajax({
			url: appdb.config.repository.endpoint.base + "contacts/remove",
			action: "POST",
			type: "POST",
			data: data,
			success: (function(self){
					return function(v){
						var d = appdb.utils.convert.toObject(v);
						self.renderLoading(false);
						if( d.error ){
							self.renderError(d.error);
						}else{
							self.removeContactItem(data);
						}
					};
				})(this),
			error: (function(self){
					return function(err){
						self.renderLoading(false);
					};
				})(this)
		});
	};
	this.canEditContacts = function(){
		return (!this.options.viewonly) && appdb.repository.ui.CurrentReleaseManager.canManageReleases();
	};
	this.initContainer = function(){
		$(this.parentdom).empty();
		$(this.parentdom).append("<div class='relatedcontacts'></div>");
		this.dom = $(this.parentdom).find(".relatedcontacts");
		$(this.dom).addClass(this.options.contacttype.name);
	};
	this.init = function(){
		this.parentdom = $(o.container);
		if( typeof o.contacttype === "number" && o.contacttype){
			this.options.contacttype = { 
				id: o.contacttype,
				name: ($.trim(o.contacttype) === '2')?"technical":"general"
			};
		}else{
			this.options.contacttype = {id: 1, name: "general"};
		}
		switch(this.options.contacttype.id){
			case 2:
				this.options.title = "Technical Contacts";
				break;
			default:
				this.options.title = "General Contacts";
				break;
		}
		this.options.viewonly = o.viewonly || this.options.viewonly;
		this.initContainer();
	};
	this.init();
},{
	maxcount: 2
});
appdb.repository.ui.views.ReleaseContacts = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleaseContacts", function(o){
	this.render = function(d){
		this.subviews.generalcontacts.render(d);
		this.subviews.technicalcontacts.render(d);
	};
	this.getRelease = function(){
		return this.parent.getRelease();
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent;
		this.viewonly = o.viewonly || false;
		this.associatedType = $.trim(o.associatedType) || "release";
		$(this.dom).find(".contactscontainer").append("<div class='contacts'></div>").append("<div class='technicalcontacts'></div>");
		this.subviews.generalcontacts = new appdb.repository.ui.views.ReleaseContact({container: $(this.dom).find(".contactscontainer > .contacts"), parent: this, contacttype: 1, viewonly:this.viewonly, associatedType: this.associatedType});
		this.subviews.technicalcontacts = new appdb.repository.ui.views.ReleaseContact({container: $(this.dom).find(".contactscontainer > .technicalcontacts"), parent: this, contacttype: 2,viewonly:this.viewonly, associatedType: this.associatedType});
	};
	this.init();
});
appdb.repository.ui.views.ReleasePriorityEditor = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleasePriorityEditor", function(o){
	this.options = {
		parent: o.parent || null,
		dom: $(o.container),
		currentPriority: "normal"
	};
	this.getSelectedOptionValue = function(){
		 var el = $(this.dom).find("select:first option:selected");
		 if( $(el).length === 0 ){
			 return this.options.currentPriority;
		 }
		 return $(el).val();
	};
	this.renderLoading = function(loading,text){
		loading = (typeof loading === "boolean")?loading:false;
		$(this.dom).find("select").prop("disabled", true);
		$(this.dom).find(".loader").remove();
		if( loading ){
			$(this.dom).find(".errormessage").remove();
			text = text || "saving";
			var loader = "<div class='loader'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div>";
			$(this.dom).find(".actions").addClass("hidden");
			$(this.dom).append(loader);
		}else{
			$(this.dom).find("select").prop("disabled", false);
		}
	};
	this.cancel = function(){
		this.render({priority: this.options.currentPriority});
	};
	this.renderError = function(d){
		if( typeof d === "string"){
			d = {error: d};
		}
		if( !d || !d.error ){
			d = {error: "Could not save value. Unknown error occured."};
		}
		$(this.dom).parent().find(".errormessage").remove();
		$(this.dom).parent().append("<div class='errormessage'><img src='/images/stop.png' alt=''></img><span>"+d.error+"</span></div>");
		setTimeout((function(self){
			return function(){
				$(self.dom).parent().find(".errormessage").fadeOut(400,function(){
					$(self.dom).parent().find(".errormessage").remove();
				});
			};
		})(this),30000);
		this.cancel();
	};
	this.save = function(){
		var val = this.getSelectedOptionValue();
		this.renderLoading(true, "saving");
		this._model = new appdb.repository.model.ProductReleaseProperty();
		this._model.subscribe({event: "update", callback: function(v){
			this.renderLoading(false);
			if( v.error ){
				this.renderError(v);
			}else{
				this.options.currentPriority = v.priority;
				this.cancel();
			}
		},caller: this}).subscribe({event: "error", callback: function(v){
			this.renderLoading(false);
			this.renderError(v);
		},caller: this});
		this._model.update({
			id: this.options.parent.getRelease().id,
			name: "priority",
			value: val
		});
	};
	this.render = function(d){
		d =d || {};
		d.priority = d.priority || this.options.currentPriority;
		this.options.currentPriority = d.priority;
		
		$(this.dom).find(".actions").addClass("hidden");
		
		var select = $(this.dom).find("select:first");
		$(select).find("option[selected]").prop("selected", false);
		$(select).find("option[value='" + d.priority + "']").prop("selected", true);
		$(select).off("change").on("change", (function(self){
			return function(){
				if( self.getSelectedOptionValue() != self.options.currentPriority){
					$(self.dom).find(".actions").removeClass("hidden");
					$(self.dom).parent().find(".errormessage").remove();
				}else{
					$(self.dom).find(".actions").addClass("hidden");
				}
			};
		})(this));
		
		$(this.dom).find(".actions .cancel").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.cancel();
				return false;
			};
		})(this));
		$(this.dom).find(".actions .save").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.save();
				return false;
			};
		})(this));
		
	};
	this.init = function(){
		this.dom = this.options.dom;
	};
	this.init();
});

appdb.repository.ui.views.ReleaseDocumentation = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleaseDocumentation", function(o){
	this.getActiveProperties = function(){
		var res = 0;
		for(var i in this.subviews){
			if( this.subviews.hasOwnProperty(i) && $(this.subviews[i].dom).hasClass("hidden") === false ){
				res += 1;
			}
		}
		return res;
	};
	this.renderEmptyValues = function(){
		$(this.dom).children("div").remove(".emptymessage");
		$(this.dom).find(".value.empty").after("<div class='emptymessage'>" + appdb.repository.ui.views.ReleaseDocumentation.emptymessage + "</div>");
	};
	this.renderProperties = function(d){
		$(this.dom).find(".property").each((function(self){
			return function(index,elem){
				self.subviews[$(elem).data("path")] = new appdb.repository.ui.views.ReleaseProperty({
					container: $(elem)[0], 
					parent:self, 
					data: d});
			};
		})(this));
		for(var s in this.subviews){
			this.subviews[s].unsubscribeAll(this);
			this.subviews[s].subscribe({event:"changed", callback: function(v){
					this.publish({event: "propertychanged", value: {section: "releasedocumentation", parent: this, value: v}});
			}, caller: this});
			this.subviews[s].render();
		}
	};
	this.renderMiscProperties = function(d){
		var dom = $(this.dom).find(".miscproperties");
		var stateprop = $(dom).find(".releasestate.fieldvalue");
		var priorityprop = $(dom).find(".releasepriority.fieldvalue");
		
		var statename = (d.state && $.trim(d.state.name))?d.state.name:"Unkown";
		var statecontent = $(document.createElement("span")).addClass("releasestatevalue").addClass(statename.toLowerCase()).text(statename);
		$(stateprop).find(".value").empty().append(statecontent);
		
		var priorityname = (d.priority && $.trim(d.priority))?d.priority:"Unknown";
		var prioritycontent = $(document.createElement("span")).addClass("priority").addClass(priorityname).text(priorityname);
		$(priorityprop).find(".value").empty().append(prioritycontent);
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
			$(priorityprop).find(".value").addClass("hidden");
			this.priorityeditor.render(d);
		}
	};
	this.render = function(d){
		$(this.dom).find(".valueeditor").remove();
		this.renderEmptyValues(d);
		this.renderProperties(d);
		this.renderMiscProperties(d);
		this.toc.render();
	};
	this.setPropertyValue = function(name, value, silent){
		silent = silent || false;
		name = name || null;
		value = value || "";
		
		if( !name || !this.subviews[name]){
			return;
		}
		
		this.subviews[name].setDataValue(value,silent);
	};
	this.editPropertyValue = function(propName){
		var prop = $(this.dom).find("." + propName);
		if( $(prop).length === 0 ) return;
		
	};
	this.getRelease = function(){
		return this.parent.getRelease();
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent;
		this.toc = new appdb.repository.ui.views.PropertyTOC({container: $(this.dom).find(".toccontainer"), parent: this});
		this.priorityeditor = new appdb.repository.ui.views.ReleasePriorityEditor({container: $(this.dom).find(".editor"), parent: this});
	};
	this.init();
});
appdb.repository.ui.views.ReleaseRepositories = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleaseRepositories", function(o){
	this.getRelease = function(){
		return this.parent.getRelease();
	};
	this.renderEmptyValues = function(d){
		$(this.dom).children("div").remove(".emptymessage");
		$(this.dom).find(".value.empty").after("<div class='emptymessage'>" + appdb.repository.ui.views.ReleaseDocumentation.emptymessage + "</div>");
	};
	this.renderProperties = function(d){
		$(this.dom).find(".property").each((function(self){
			return function(index,elem){
				self.subviews[$(elem).data("path")] = new appdb.repository.ui.views.ReleaseProperty({
					container: $(elem)[0], 
					parent:self, 
					data: d});
			};
		})(this));
		for(var s in this.subviews){
			this.subviews[s].unsubscribeAll(this);
			this.subviews[s].subscribe({event:"changed", callback: function(v){
					this.publish({event: "propertychanged", value: {section: "releaserepositories", parent: this, value: v}});
			}, caller: this});
			this.subviews[s].render();
		}
	};
	this.setBuildState = function(time){
		if( this.poareleases ){
			this.poareleases.setBuildingProcess(time);
		}
	};
	
	this.renderPoaReleases = function(d){
		this.poareleases.render(d);
	};
	this.setPropertyValue = function(name, value, silent){
		silent = silent || false;
		name = name || null;
		value = value || "";
		
		if( !name || !this.subviews[name]){
			return;
		}
		
		this.subviews[name].setDataValue(value,silent);
	};
	this.render = function(d){
		$(this.dom).find(".valueeditor").remove();
		this.renderEmptyValues(d);
		this.renderProperties(d);
		this.renderPoaReleases(d);
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent || null;
		this.poareleases = new appdb.repository.ui.views.PoaRepositoryList({container: $(this.dom).find(".contents .repositoriescontainer"), parent: this, group: "release"});
	};
	this.init();
});
appdb.repository.ui.views.TargetSelector = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.TargetSelector",function(o){
	this.load = function(){
		this.render(appdb.repository.model.Targets.getLocalData());
	};
	this.reset = function(){
		
	};
	this.render = function(d){
		
	};
	this.init = function(){
		this.dom = $(o.container);
		this.template = $(this.dom).html();
	};
	this.init();
});
appdb.repository.ui.views.TargetItem = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.TargetItem", function(o){
	this.options = {
		parent: o.parent || null,
		data : o.data || {},
		template: o.template || appdb.repository.ui.views.TargetItem.template || $("<div class='target'></div>"),
		uploader: null,
		progressbars: []
	};
	this.reset = function(){
		if( this.uploader ){
			this.uploader.unsubscribeAll(this);
			this.uploader = null;
		}
		$(this.template).remove();
		$(this.dom).empty().remove();
		this.init();
	};
	this.destroy = function(){
		$(this.dom).empty();
		$(this.dom).remove();
		this.publish({event: "destroy", value: this});
	};
	this.getData = function(){
		return this.options.data;
	};
	this.removeAllFiles = function(){
		var fs = this.getSelectedFiles();
		var fsids = [];
		var self = this;
		$.each(fs, function(i,e){
			fsids.push(e.id);
		});
		$.each(fsids, function(i,e){
			self.options.uploader.removeSelectedFile(e);
		});
		setTimeout(function(){
			self.renderSelectedFiles();
		},1);
	};
	this.getSelectedFiles = function(){
		return this.options.uploader.getSelectedFiles() || [];
	};
	this.renderSelectedFiles = function(){
		var f = this.getSelectedFiles();
		var sfiles = $(this.template).parent();
		var template = $(sfiles).find(".template");
		
		$(this.template).find(".action.clearselectedfiles > a").off("click");
		$(this.template).find(".action.clearselectedfiles").addClass("hidden");
		$(this.template).find(".filecount").addClass("hidden").find(".count").text();
		$(this.template).parent().removeClass("hasfiles");
		$(sfiles).find(".selectedfiles > .empty").addClass("hidden");
		$(sfiles).find(".selectedfiles > ul").addClass("hidden");
		
		
		$.each(this.options.progressbars.length, function(i,e){
			if( e.destroy ) e.destroy();
		});
		this.options.progressbars = [];
		
		var i, len = f.length, ul = $(sfiles).find(".selectedfiles > ul");
		//clear list except template
		$(ul).children("li").each(function(index,elem){
			if( $(elem).hasClass("template") ) return;
			$(elem).remove();
		});
		
		for(i=0; i<len; i+=1){
			var cnt = $(template).clone();
			var ext = f[i].name.split('.').pop().toLowerCase();
			$(cnt).removeClass("template").removeClass("hidden");
			switch(ext){
				case "rpm":
				case "deb":
				case "tar":
				case "gz":
				case "tgz":
					ext = ext;
					break;
				default:
					ext = "generic";
					break;
			}
			var name = f[i].name;
			$(cnt).find(".name > .fullname").text(name);
			if( name.length > 65 ){
				name = name.substr(0,62) + "...";
			}
			$(cnt).find(".name > img").attr("src","/images/repository/artifact_"+ext+".png");
			$(cnt).find(".name > span").text(name);
			
			$(cnt).find(".action.remove").attr("title","Remove this file from upload list").off("click").on("click", (function(file, self){
				return function(ev){
					ev.preventDefault();
					self.options.uploader.removeSelectedFile(file.id);
					setTimeout(function(){
						self.renderSelectedFiles();
					},1);
					return false;
				};
			})(f[i], this));
			
			this.options.progressbars.push( new appdb.repository.UploadProgressBar({
					container: $(cnt).find(".progress:last"),
					file: f[i],
					uploader: this.options.uploader
				}) );
			$(ul).append(cnt);
		}
		
		if( f.length > 0 ){
			$(this.template).parent().addClass("hasfiles");
			$(this.template).find(".filecount").removeClass("hidden").find(".count").text(f.length);
			$(this.template).find(".filecount .plurar").text( (f.length>1)?"s":"");
			$(ul).removeClass("hidden");
			$(this.template).find(".action.clearselectedfiles").removeClass("hidden");
				$(this.template).find(".action.clearselectedfiles").attr("title","Clear current upload list").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.removeAllFiles();
					return false;
				};
			})(this));
		}
	};
	this.render = function(d){
		d = d || this.options.data;
		this.options.data = d;
		
		$(this.template).data("target",d);
		$(this.template).find(".data img").attr("src",appdb.repository.utils.getOsImagePath(d.os.name));
		$(this.template).find(".data .id").text(d.id);
		$(this.template).find(".data .osname").text(d.os.displayname + " " + d.os.displayflavor + " / " + d.arch.displayname);
		$(this.template).find(".data .archname").text(d.arch.displayname);
		$(this.template).find(".data .deploymethod").text((d.deploymethod)?d.deploymethod.name:"");
		
		d.os.artifact = d.os.artifact|| [];
		d.os.artifact = $.isArray(d.os.artifact)?d.os.artifact:[d.os.artifact];
		var artifacts = $.isArray(d.os.artifact)?d.os.artifact:[d.os.artifact];
		
		var comment = "Only ";
		for(var i=0; i<artifacts.length; i+=1){
			comment += " <b>" + artifacts[i].type + "</b>,";
		}
		comment = comment.slice(0,comment.length-1) + " allowed";
		
		$(this.template).find(".data .comments").html(comment);
		var container_id = "container_t" + d.id + "_r" + this.options.parent.getReleaseData().id;
		$(this.template).find("div.action.selection:last").attr("id",container_id); //needed by plupload
		
		var uprel = appdb.repository.UploadManager.getReleaseHandler(this.options.parent.getReleaseData());
		if( uprel ){
			this.options.uploader = uprel.getTargetHandler(d,container_id);
			if( this.options.uploader ){
				this.options.uploader.unsubscribeAll(this);
				this.options.uploader.subscribe({event: "addfiles", callback: function(v){
						this.renderSelectedFiles();
						this.options.parent.checkSubmit();
				}, caller: this});
				this.options.uploader.subscribe({event: "removefiles", callback: function(v){
						this.options.parent.checkSubmit();
				}, caller: this});
			}
		}
		
		$(this.template).find(".action.removetarget").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.destroy();
				return false;
			};
		})(this));
		
		$(this.dom).empty();
		$(this.dom).append(this.template);
		//Wait for initialization of uploaders then check if they have files
		setTimeout((function(self){
			return function(){
				self.renderSelectedFiles();
				self.options.parent.checkSubmit();
			};
		})(this),10);
	};
	
	this.init = function(){
		this.dom = $(o.container);
		this.template = $(this.options.template).clone(true);
	};
	this.init();
}, {
	template: '<div class="target"><div class="data"><div class="id hidden"></div><div class="osname"></div><div class="flavor"></div><div class="archname"></div><div class="artifactType"></div></div><div class="action selection"><input type="file" name="files" id="files"><a href="" class="upload"><img src="/images/upload_active.png" alt=""><span>upload</span></a></div></div>'
});
appdb.repository.ui.views.TargetSelectionList = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.TargetSelectionList", function(o){
	this.options = {
		parent: o.parent,
		data: o.data
	};
	this.render = function(d, excludetargetids){
		d = d || appdb.repository.model.Targets.getLocalData() || {};
		
		excludetargetids = excludetargetids || [];
		excludetargetids = $.isArray(excludetargetids)?excludetargetids:[excludetargetids];
		
		if( !d.target ) return;
		d.target = $.isArray(d.target)?d.target:[d.target];
		var usetargets = [];
		$.each(d.target, function(i,e){
			if( $.inArray(e.id, excludetargetids) === -1){
				usetargets.push(e);
			}
		});
		usetargets.sort(this.textSorter(this.getOsDisplay));
		this.selectlist.unsubscribeAll(this);
		this.selectlist.render(usetargets);
		$(this.dom).find(".selecttargetaction").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.publish({event: "add", value: self.selectlist.getSelectedItem()});
				return false;
			};
		})(this));
	};
	this.textSorter = function(getValue){ 
		return function(a, b){
			var aa = getValue(a);
			var bb = getValue(b);

			if( aa > bb ) return -1;
			if( aa < bb ) return 1;
			return 0;
		};
	};
	this.getOsDisplay = function(d) {
		return d.os.displayname + " " + d.os.displayflavor + " / " + d.arch.displayname;
	};
	this.renderItem = function(d){
		var template = $(this.template).clone(true);
		
		$(template).find(".data img").attr("src",appdb.repository.utils.getOsImagePath(d.os.name));
		$(template).find(".data .id").text(d.id);
		$(template).find(".data .osname").text(this.getOsDisplay(d));
		$(template).find(".data .archname").text(d.arch.displayname);
		$(template).find(".data .deploymethod").text((d.deploymethod)?d.deploymethod.name:"");
		
		d.os.artifact = d.os.artifact|| [];
		d.os.artifact = $.isArray(d.os.artifact)?d.os.artifact:[d.os.artifact];
		var artifacts = $.isArray(d.os.artifact)?d.os.artifact:[d.os.artifact];
		
		var comment = "Only ";
		for(var i=0; i<artifacts.length; i+=1){
			comment += " <b>" + artifacts[i].type + "</b>,";
		}
		comment = comment.slice(0,comment.length-1) + " allowed";
		
		$(template).find(".data .comments").html(comment);
		
		return template;
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent;
		this.template = $(this.dom).find(".template").html();
		this.selectlist = new appdb.repository.ui.views.DropDownList({
			container: $(this.dom).find(".selecttarget"), 
			parent: this,
			title: "Please select a platform and then click <b><i>'add'</i></b> ",
			onRenderItem: (function(self){ 
				return function(d){
					return self.renderItem(d);
				};
			})(this)});
	};
	this.init();
});
appdb.repository.ui.views.TargetList = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.TargetList", function(o){
	this.options = {
		parent: o.parent || null,
		state: "init",
		upoader: null,
		uploadInterval: null
	};
	this.checkSubmit = function(){
		var res = $.grep(this.subviews, function(s){
			return ( s.getSelectedFiles().length > 0 )?true:false;
		});
		
		if( $(this.dom).hasClass("uploading") ){
			res = [];
		}
		
		res = (res.length>0)?true:false;
		
		if( res ){
			$(this.dom).parent().find(".actions .action.upload button").removeClass("inactive").prop("disabled", false);
			$(this.dom).parent().find(".actions .action.upload button img:last").attr("src","/images/upload_active.png");
		} else {
			$(this.dom).parent().find(".actions .action.upload button").addClass("inactive").prop("disabled", true);
			$(this.dom).parent().find(".actions .action.upload button img:last").attr("src","/images/upload_inactive.png");
		}
	};
	this.setState = function(state){
		state = state || this.options.state || "init";
		state = state.toLowerCase();
		$(this.dom).removeClass().addClass("targets").addClass(state);
		this.checkSubmit();
		$(this.dom).parent().find(".header > span").addClass("hidden");
		$(this.dom).parent().find(".actions .action.continue").addClass("hidden");
                $(this.dom).parent().find(".actions .action.close").addClass("hidden");
		switch(state){
			case "init":
				$(this.dom).parent().find(".header > span.init").removeClass("hidden");
				$(this.dom).parent().find(".actions .action.upload button").removeClass("hidden");
				$(this.dom).parent().find(".actions .action.cancel button").removeClass("hidden");
				break;
			case "uploading":
				$(this.dom).parent().find(".actions .action.cancel button").addClass("hidden");
				$(this.dom).parent().find(".actions .action.upload button").addClass("hidden");
				$(this.dom).parent().find(".header > span.uploading").removeClass("hidden");
				this.options.uploadInterval = setInterval((function(self){
					return function(){
						if( ! self.options.parent.options.uploader.isRunning() ){
							clearInterval(self.options.uploadInterval);
							self.setState("complete");
						}
					};
				})(this),500);
				break;
			case "complete":
				$(this.dom).addClass("uploading");
				$(this.dom).parent().find(".actions .action.upload button").addClass("hidden");
				$(this.dom).parent().find(".header > span.complete").removeClass("hidden");
                                $(this.dom).parent().find(".actions .action.close").removeClass("hidden");
				$(this.dom).parent().find(".actions .action.continue").removeClass("hidden");
				break;
		}
	};
	this.submitUploading = function(){
		this.setState("uploading");
		$.each(this.subviews, function(i,e){
			var fs = e.options.uploader.getSelectedFiles() || [];
			if( fs.length > 0 ){
				e.options.uploader.start();
			}
		});
	};
	this.submit = function(){
		if($.trim(this.getReleaseData().state.name.toLowerCase()) === 'candidate'){
			appdb.repository.ui.ShowVerifyDialog({
				title: "Uploading files for candidate release",
				message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to upload files in a release that has candidate repositories. If this happens the release will revert to 'unverified' and the candidate repositories will be removed.<br/><br/> If you want to proceed to this action click 'ok', else click 'cancel' to return.</span>",
				onOk: (function(self){
					return function(){
						self.submitUploading();
					};
				})(this)
			});
		}else{
			this.submitUploading();
		}
	};
	this.cancel = function(){
		$.each(this.subviews, function(i,e){
			setTimeout(function(){
				e.removeAllFiles();
				e.destroy();
			},1);
		});
		setTimeout((function(self){
			return function(){
				$(self.parent.dom).find(".action.uploadfile").removeClass("selected");
			};
		})(this),10);
	};
	this.getItemTemplate = function(){
		if( this.itemTemplate ){
			return $(this.itemTemplate).clone(true);
		}
		return undefined;
	};
	this.reset = function(){
		clearInterval(this.options.uploadInterval);
		$.each(this.subviews, function(i,e){
			if(e){
				if( e.reset ){
					e.reset();
				}
				if( e.destroy ){
					e.destroy();
				}
			}
		});
	};
	this.getSelectedTargets = function(){
		var res = [];
		$.each(this.subviews, function(i,e){
			if(e){
				var target = e.getData();
				if(target && target.id){
					res.push(target.id);
				}
			}
		});
		return res;
	};
	this.addItem = function(dom, d){
		var item = new appdb.repository.ui.views.TargetItem({container: dom, data: d, template: this.getItemTemplate(), parent: this});
		item.subscribe({event: "destroy", callback: function(v){
				var found = -1;
				$.each(this.subviews, function(i,e){
					if( e.options && e.options.data && e.options.data.id == v.options.data.id ){
						found = i;
					}
				});
				if( found > -1 ){
					this.subviews = this.subviews.splice(i,1);
				}
				this.parent.renderTargetSelector(undefined,this.getSelectedTargets());
		}, caller: this});
	
		item.render();
		return item;
	};
	this.hasTarget = function(id){
		found = false;
		$.each(this.subviews, function(i,e){
			found = ( e.options && e.options.data && e.options.data.id && e.options.data.id == id)?true:found;
		});
		return found;
	};
	this.getReleaseData = function(){
		return this.options.parent.getReleaseData();
	};
	this.loadPreselectedTargets = function(d){
		var res = [];
		var self = this;
		var rh = appdb.repository.UploadManager.getReleaseHandler(this.getReleaseData());
		if( !rh ) return [];
		$.each(d, function(i,e){
			var containerid = "container_t" + e.id + "_r" + self.getReleaseData().id;
			var dom = $(document.createElement("div")).attr("id",containerid);
			$("body").append(dom);
			var th =rh.getTargetHandler( e,containerid);
			if( th ) {
				setTimeout((function(self,elem,data,targethandler){
					return function(){
						var sf = targethandler.getSelectedFiles();
						if( sf && sf.length > 0 ){
							self.appendTargetItem(data);
						}
						$(elem).remove();
					};
				})(self, dom, e, th),10);
			}
		});
		return res;
	};
	this.appendTargetItem = function(d){
		if( !d ) return;
		if( this.hasTarget(d.id) ) return;
		var found = false;
		var dom = $("<li></li>");
		$.each( this.subviews , function(i,e){
			found = (e.id === d.id)?true:found;
		});
		if( found ) return;
		$(this.dom).append(dom);
		this.subviews.push( this.addItem(dom, d) );
	};
	this.render = function(d){
		this.reset();
		this.setState("init");
		this.options.uploader = this.options.parent.getUploader();
		if( this.options.uploader ) {this.options.uploader.unsubscribeAll(this);}
		
		d = d || appdb.repository.model.Targets.getLocalData() || {};
		if( !d.target ) return;
		d.target = $.isArray(d.target)?d.target:[d.target];
		dtarget = this.loadPreselectedTargets(d.target);
		
		$(this.dom).parent().find(".actions .action.upload button").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.submit();
				return false;
			};
		})(this));
		$(this.dom).parent().find(".actions .action.cancel button").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.cancel();
				return false;
			};
		})(this));
		$(this.dom).parent().find(".actions .action.continue button").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				$.each(self.subviews, function(i,e){
					e.removeAllFiles();
				});
				self.reset();
				self.setState("init");
				self.render(appdb.repository.model.Targets.getLocalData());
				return false;
			};
		})(this));
		$(this.dom).parent().find(".actions .action.close button").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				$.each(self.subviews, function(i,e){
					e.removeAllFiles();
				});
				self.cancel();
				self.reset();
				self.setState("init");
				self.render(appdb.repository.model.Targets.getLocalData());
				return false;
			};
		})(this));
		setTimeout( (function(self){
			return function(){
				if( !self.options.uploader ) return;
				if( self.options.uploader.isComplete() ){
					self.setState("complete");
				}else if( self.options.uploader.isRunning() ){
					self.setState("uploading");
				}else{
					self.setState("init");
				}
			};
		})(this),10);
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent;
		this.subviews = [];
		this.itemTemplate = $(this.dom).find(".targettemplate").html();
		if( $(this.itemTemplate).length > 0 ){
			this.itemTemplate = $(this.itemTemplate).clone(true);
		} else {
			this.itemTemplate = null;
		}
	};
	this.init();
});
appdb.repository.ui.views.Tabular = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.Tabular", function(o){
	this.options = {
		parent: o.parent || null,
		container: $(o.container),
		dom: {
			container: null,
			list: null
		},
		onRenderItem: o.onRenderItem || null,
		onSelectItem: o.onSelectItem || null,
		itemCss: $.trim(o.itemCss),
		itemClass: $.trim(o.itemClass)
	};
	this.addItem = function(d,index){
		if( !d ) return;
		var li = $(document.createElement("li"));
		var a = $(document.createElement("a")).addClass("tabitemaction");
		var content = $(document.createElement("div")).addClass("content");
		
		if( this.options.onRenderItem ){
			$(content).append(this.options.onRenderItem(d));
		}else{
			$(content).append(d);
		}
		
		$(a).attr("href","#").append(content).data("itemdata",d).data("index",index);
		$(a).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				$(self.dom).find("li").removeClass("selected");
				$(this).parent().addClass("selected");
                                window.releases_files_tab_id = $(this).data("itemdata").id;
				self.options.onSelectItem($(this).data("index"), $(this).data("itemdata"));
				return false;
			};
		})(this));
		
		if( this.options.itemCss ){
			$(li).css(this.options.itemCss);
		}
		
		if( this.options.itemClass ){
			$(li).addClass(this.options.itemClass);
		}
		$(li).append(a);
		
		$(this.options.dom.list).append(li);
	};
	this.render = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		this.initContainer();
		d.sort(function(a,b){
			if( b.acronym === "tar" ) return 100;
			if( a.acronym < b.acronym ) return 1;
			if( a.acronym > b.acronym ) return -1;
			return 0;
                });
		$.each(d, (function(self){
			return function(i,e){
				self.addItem(e,i);
			};
		})(this));
		$(this.options.dom.list).find("li:first").addClass("selected");
		
		if( $(this.options.dom.list).children("li").length > 1 ){
			this.clearInterval();
			this.checkRenderScrolling();
			appdb.repository.ui.views.Tabular.checkScrollTimer = setInterval((function(self){
				return function(){
					self.checkRenderScrolling();
				};
			})(this),250);
		}else{
			this.clearInterval();
		}
	};
	this.clearInterval = function(){
		appdb.repository.ui.views.Tabular.clearScrollTimer();
	};
	this.listHiddenWidth = function(){
		var c = $(this.dom).find(".tabcontainer");
		var cw = $(c).width();
		var iw = 0;
		var i = $(c).find(".tablist");
		$.each($(i).children("li"), function(i,e){
			iw += $(e).width();
		});
		iw += 50;
		if( iw > cw ){
			return iw-cw;
		}else{
			return 0;
		}
	};
	this.checkRenderScrolling = function(){
		if( $(this.options.container).is(":visible") ){
			this.renderScrolling();
		}
	};
	this.renderScrolling = function(){
		var diff = this.listHiddenWidth();
		
		var rightscroll = $(this.options.dom.container).find(".rightscrollhandler");
		var leftscroll = $(this.options.dom.container).find(".leftscrollhandler");
		if( $(rightscroll).length === 0){
			$(this.options.dom.container).find("scrollhandler").remove();
			rightscroll = $(document.createElement("div")).addClass("scrollhandler").addClass("rightscrollhandler").append("<span><b>&#9654;</b></span>");
			leftscroll = $(document.createElement("div")).addClass("scrollhandler").addClass("leftscrollhandler").addClass("disabled").append("<span><b>&#9664;</b></span>");
			$(this.options.dom.container).prepend(leftscroll).append(rightscroll);

			(function(self,diff, left, right,list){
				var timer = null;
				var w = $(self.options.dom.list).width();
				var _getLeft = function(){
					return $(self.options.dom.list).position().left;
				};
				$(left).off("mousedown").on("mousedown", function(e){
					if( $(this).hasClass("disabled") ){
						clearInterval(timer);
						return;
					}
					if( e.which === 1 ){
						timer = setInterval(function(){
							var leftoff = _getLeft();
							if( leftoff < (diff+30) ){
								$(right).removeClass("disabled");
							}else{
								$(right).addClass("disabled");
							}
							if( leftoff >= 0 ){
								clearInterval(timer);
								$(left).addClass("disabled");
							}else{
								$(self.options.dom.list).css({"left":(leftoff+2) + "px"});
							}
						},5);
					}else{
						clearInterval(timer);
					}
				}).off("mouseup").on("mouseup", function(e){
					clearInterval(timer);
				}).off("mouseleave").on("mouseleave", function(e){
					clearInterval(timer);
				});

				$(right).off("mousedown").on("mousedown", function(e){
					if( $(this).hasClass("disabled") ){
						clearInterval(timer);
						return;
					}
					if( e.which === 1 ){
						timer = setInterval(function(){
							var leftoff = _getLeft();
							if( leftoff < 0 ){
								$(left).removeClass("disabled");
							}else{
								$(left).addClass("disabled");
							}
							if( (leftoff+(diff+30)) <= 0 ){
								clearInterval(timer);
								$(right).addClass("disabled");
							}else{
								$(self.options.dom.list).css({"left":(leftoff-2) + "px"});
							}
						},5);
					}else{
						clearInterval(timer);
					}
				}).off("mouseup").on("mouseup", function(e){
					clearInterval(timer);
				}).off("mouseleave").on("mouseleave", function(e){
					clearInterval(timer);
				});
			})(this,diff, leftscroll, rightscroll, this.options.dom.list);
		}
		if( diff > 0 ){
			$(this.options.dom.container).addClass("scroll");
		}else{
			$(this.options.dom.container).removeClass("scroll");
		}
	};
	this.selectTabByIndex = function(index){
		var li = $(this.options.dom.list).children("li").get(index || 0);
		var data = $(li).children("a:first").data();
                $(this.dom).find("li").removeClass("selected");
                $(li).addClass("selected");
		this.options.onSelectItem(data.index, data.itemdata);
	};
	this.selectTabById = function(id){
		if( typeof id === "undefined"){
			id = window.releases_files_tab_id;
		}
		var found = false;
		$(this.options.dom.list).children("li").each(function(i,e){
			if( found === false && $(e).children("a").length > 0){
				var data = $($(e).children("a").get(0)).data("itemdata");
				if( data && data.id == id){
					found = i;
				}
			}
		});
		if( found !== false ){
			window.releases_files_tab_id = id;
			this.selectTabByIndex(found);
		}else{
			this.selectTabByIndex(0);
		}
	};
        
	this.initContainer = function(){
		var container = $(document.createElement("div")).addClass("tabcontainer");
		var list = $(document.createElement("ul")).addClass("tablist");
		this.options.dom.list = list;
		this.options.dom.container = container;
		
		$(this.options.dom.container).append(this.options.dom.list);
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.container);
		this.clearInterval();
		
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this.init();
},{
	checkScrollTimer: null,
	clearScrollTimer : function(){
		if( appdb.repository.ui.views.Tabular.checkScrollTimer ){
			clearInterval(appdb.repository.ui.views.Tabular.checkScrollTimer);
			appdb.repository.ui.views.Tabular.checkScrollTimer = null;
		}
	}
});
appdb.repository.SequenceCall = function(){
    this.options = {
      caller: o.caller,
      data: o.data || [],
      results: null,
      index: -1,
      onNextCall: o.onNextCall || null,
      callback: o.callback || null
    };
    this.start = function(){
        this.options.index = 0;
        this.callNext();
    };
    this.callNext = function(){
      if(this.options.index === this.options.callbacks.length-1){
          this.options.complete(session);
      }else{
          this.options.index += 1;
          setTimeout((function(session){
              session.options.callbacks[session.options.index].apply(session);
          })(this),1);
      }
    };
    this.completeCall = function(){
        if(this.options.index <= this.options.callbacks.length){
            this.options.onCompleteCall.apply(this.options.caller,this.options.data);
        }else{
            this.options.callNext();
        }
    };
    
};
appdb.repository.ui.views.FilesToolBar =appdb.ExtendClass(appdb.View,"appdb.repository.ui.views.FilesToolBar", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			save: null,
			discard: null,
			actions: null
		}
	};
	this.applyRemoving = function(data){
		if( data && data.rem && data.rem.ids && data.rem.ids.length > 0){
			var dispatcher = new appdb.repository.dispatcher.RemovePackages({data: data.rem});
			dispatcher.subscribe({event: "load", callback: function(d){
					data.rem.result = d;
					this.parent.setItemsById("remove",data.rem.ids);
					this.applyMarking(data);
					appdb.repository.ui.CurrentReleaseManager.updateReleaseDataProperty(data.rem.releaseid, "state", {name: "unverified", id: 1});
			}, caller: this});
			dispatcher.dispatch();
		}else{
			this.applyMarking(data);
		}
	};
	this.applyMarking = function(data){
		if( data && data.meta && data.meta.ids && data.meta.ids.length > 0){
			var dispatcher = new appdb.repository.dispatcher.MarkMetaPackages({data: data.meta});
			dispatcher.subscribe({event: "load", callback: function(d){
					data.meta.result = d;
					this.parent.setItemsById("meta",data.meta.ids);
					this.applyUnmarking(data);
			}, caller: this});
			dispatcher.dispatch();
		}else{
			this.applyUnmarking(data);
		}
	};
	this.applyUnmarking = function(data){
		if( data && data.dep && data.dep.ids && data.dep.ids.length > 0){
			var dispatcher = new appdb.repository.dispatcher.UnmarkMetaPackages({data: data.dep});
			dispatcher.subscribe({event: "load", callback: function(d){
					data.dep.result = d;
					this.parent.setItemsById("dep",data.dep.ids);
					this.endApplying(data); 
			}, caller: this});
			dispatcher.dispatch();
		}else{
			this.endApplying(data);
		}
	};
	this.startApplying = function(data){
		setTimeout((function(self,d){
			return function(){
				self.applyRemoving(d);
			};
		})(this,data),1);
	};
	this.endApplying = function(d){
		this.setMessage();  
		this.publish({event: "changed", value: {poarelease: d}});
		this.parent.render($.isArray(this.parent.options.data)?this.parent.options.data[0]:this.parent.options.data);
	};
	this.doApplyChanges = function(d){
		var data = this.createDispatchData(d);
		this.setMessage("<span>...applying changes</span>");
		this.publish({event: "changing", value:""});
		this.startApplying(data);
	};
	this.applyChanges = function(d){
		var release = this.parent.getReleaseData() || {};
		if( release.state && $.trim(release.state.name).toLowerCase() === 'candidate' && $.isEmptyObject(d.removal) == false){
			appdb.repository.ui.ShowVerifyDialog({
				title: "Deleting files of candidate release",
				message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to delete files in a release that has candidate repositories. If this happens the release will revert to 'unverified' and the candidate repositories will be removed.<br/><br/> If you want to proceed to this action click 'ok', else click 'cancel' to return.</span>",
				onOk: (function(self,dt){
					return function(){
						self.doApplyChanges(dt);
					};
				})(this,d)
			});
		}else{
			this.doApplyChanges(d);
		}
		
	};
	this.discardChanges = function(){
		this.parent.options.marked = {};
		this.parent.render($.isArray(this.parent.options.data)?this.parent.options.data[0]:this.parent.options.data);
	};
	this.setMessage = function(message,hideactions){
		message = message || false;
		hideactions = ( (typeof hideactions === "boolean" && message)?hideactions:false );
		$(this.dom).find(".message").remove();
		$(this.dom).find(".treeactions").removeClass("hidden");
		if( message ){
			var msg = $("<div class='message icontext'>"+message+"</div>");
			$(this.dom).prepend(msg);
			if( hideactions ){
				$(this.dom).find(".treeactions").addClass("hidden");
			}
		}

	};
	this.createDispatchData = function(data){
		var i, meta = data.metapackage, dep = data.dependency, rem = data.removal;
		var swid = appdb.pages.application.currentId();
		var release = appdb.repository.ui.CurrentReleaseManager.getCurrentRelease();
		var releaseid = release.id || release.productrelease.id;
		var res = {meta:{swid: swid, releaseid: releaseid, ids: []}, dep:{swid: swid, releaseid: releaseid, ids:[]}, rem:{swid: swid, releaseid: releaseid, ids:[]}};

		if( rem ) {
			for(var i in rem){
				if( rem.hasOwnProperty(i) ) res.rem.ids.push(i);
			}
		}

		if( meta ) {
			for(var i in meta){
				if( meta.hasOwnProperty(i) ) res.meta.ids.push(i);
			}
		} 

		if( dep ){
			for(var i in dep){
				if( dep.hasOwnProperty(i) ) res.dep.ids.push(i);
			}
		}
		return res;
	};
	this.render = function(d){
            d = d || {};
            d.removal = d.removal || {};
            d.metapackage = d.metapackage || {};
            $(this.dom).addClass("hidden");
            var remlen = 0;
            for(var i in d.removal){
                if( d.removal.hasOwnProperty(i)) remlen+=1;
            }
            var metalen = 0;
            for(var i in d.metapackage){
                if( d.metapackage.hasOwnProperty(i)) metalen+=1;
            }
            var deplen = 0;
            for(var i in d.dependency){
                if( d.dependency.hasOwnProperty(i)) deplen+=1;
            }
            if( metalen > 0 || remlen > 0 || deplen > 0 ){
                $(this.dom).removeClass("hidden");
            }
            $(this.options.dom.save).off("click").on("click", (function(self,data){
                return function(ev){
                    ev.preventDefault();
                    self.applyChanges(data);
                    return false;
                };
            })(this,d));
			$(this.options.dom.discard).off("click").on("click", (function(self,data){
                return function(ev){
                    ev.preventDefault();
                    self.discardChanges(data);
                    return false;
                };
            })(this,d));
            var release = appdb.repository.ui.CurrentReleaseManager.getCurrentRelease() || {};
            release = release.productrelease || release;
            if( remlen>0 && release.state && $.trim(release.state.name).toLowerCase() === "candidate"){
                this.displayCandidateWarning(true);
            }else{
                this.displayCandidateWarning(false);
            }
            this.setMessage();
	};
	this.displayCandidateWarning = function(display){
		display = (typeof display === "boolean")?display:false;
		$(this.parent.parent.dom).find(".warningmessage.top").remove();
		if( display === true ){
			var html = '<div class="warningmessage top"><div class="contents"><img src="/images/repository/warning.png" alt="">';
			html += '<span>This release is currently published as a candidate. If you remove any file the release will revert to unverified state and the current candidate repositories will be removed.</span>';
			html += '</div></div>';
			$(this.parent.parent.dom).prepend(html);
		}
	};
	this.initContainer = function(){
          var div = $(document.createElement("div")).addClass("treeactions").addClass("actions");
		  var discard = $(document.createElement("a")).addClass("action").addClass("save").addClass("icontext").attr("href","#").attr("title","Click to discard changes of the file tree bellow.");
          var discardimg = $(document.createElement("img")).attr("src","/images/cancelicon.png").attr("alt","");
          var discardspan = $(document.createElement("span")).append("Discard changes");
		  
          var save = $(document.createElement("a")).addClass("action").addClass("save").addClass("icontext").attr("href","#").attr("title","Click to save changes in the file tree bellow.");
          var saveimg = $(document.createElement("img")).attr("src","/images/diskette.gif").attr("alt","");
          var savespan = $(document.createElement("span")).append("save changes");
		  
          $(save).append(saveimg).append(savespan);
          $(discard).append(discardimg).append(discardspan);
		  $(div).append(discard).append(save);
          
		  this.options.dom.discard = discard;
		  this.options.dom.save = save;
          this.options.dom.actions = div;
          $(this.dom).append(div);
        };
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
                this.initContainer();
	};
	this.init();
});
appdb.repository.ui.views.FilesTreeToolbar =appdb.ExtendClass(appdb.View,"appdb.repository.ui.views.FilesTreeToolbar", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			container : $(document.createElement("div")).addClass("filestreetoolbar"),
			archs: $(document.createElement("div")).addClass("archs"),
			marked: $(document.createElement("div")).addClass("markedlist")
		}
	};
	this.renderArchs = function(){
		var archs = this.parent.getAvailableArchitectures();
		archs = archs || [];
		archs = $.isArray(archs)?archs:[archs];
		
		$(this.options.dom.archs).empty();
		if( archs.length > 0 ){
			var ul = $(document.createElement("ul")).addClass("archlist");
			var self = this;
			$.each(archs, function(i,e){
				var li = $(document.createElement("li"));
				var a = $(document.createElement("a"));
				$(a).attr("href","#").attr("title","Click to scroll to architecture");
				$(a).append("<span>"+e.name +"</span>");
				$(a).off("click").on("click", function(ev){
					ev.preventDefault();
					var domitem = $(self.parent.dom).find(".archlink.id_" + e.id + ":first");
					var pos = $(domitem).offset().top;
					pos = parseInt(pos);
					window.scroll(0,pos-70);
					return false;
				});
				$(li).append(a);
				$(ul).append(li);
			});
			$(this.options.dom.archs).append("<span class='archtitle'>Available architectures: </span>").append(ul);
		}
	};
	this.renderMarkedList = function(){
		var marked = this.parent.getMarkedItems();
		var removal = $.extend({},marked["removal"]);
		var metapackages = $.extend({},marked["metapackage"]);
		var depedencies = $.extend({},marked["dependency"]);
		if( removal ){
			if( metapackages ){
				for(var i in metapackages){
					if(metapackages.hasOwnProperty(i)){
						if( removal[i] ){
							delete metapackages[i];
						}
					}
				}
			}
			if(depedencies){
				for(var i in depedencies){
					if(depedencies.hasOwnProperty(i)){
						if( removal[i] ){
							delete depedencies[i];
						}
					}
				}
			}
		}
		var remlen = 0;
		for(var i in removal){
			if(removal.hasOwnProperty(i)){
				remlen += 1;
			}
		}
		var metalen = 0;
		for(var i in metapackages){
			if(metapackages.hasOwnProperty(i)){
				metalen += 1;
			}
		}
		var deplen = 0;
		for(var i in depedencies){
			if(depedencies.hasOwnProperty(i)){
				deplen += 1;
			}
		}
		
		var report = "<span class='report'>";
		if( remlen > 0 ){
			report += "<span class='removal togglebutton checked icontext marked'><span class='marked'><span>" + remlen + "</span><img src='/images/cancelicon.png' alt='' ></img></span></span>";
		}
		if( metalen > 0 ){
			report += "<span class='meta togglebutton checked icontext meta'><span class='meta'>"+ metalen +" m</span></span>";
		}
		if( deplen > 0 ){
			report += "<span class='dep togglebutton checked icontext meta'><span class='dep'>"+ deplen+" u</span></span>";
		}
		report += "</span>";
		
		$(this.options.dom.marked).empty().append(report);
		$(this.options.dom.marked).find(".report .removal").each(function(i,e){
			new dijit.Tooltip({
				connectId: $(e)[0],
				label: "<div class='messagepopupcontainer'>" + remlen + " package" + ((remlen === 1 )?" is":"s are") +" set to be removed.</div>"
			});
		});
		$(this.options.dom.marked).find(".report .meta").each(function(i,e){
			new dijit.Tooltip({
				connectId: $(e)[0],
				label: "<div class='messagepopupcontainer'>" + metalen + " package" + ((metalen === 1 )?" is":"s are") +" set as metapackage.</div>"
			});
		});
		$(this.options.dom.marked).find(".report .dep").each(function(i,e){
			new dijit.Tooltip({
				connectId: $(e)[0],
				label: "<div class='messagepopupcontainer'>" + deplen + " package" + ((deplen === 1 )?" is":"s are") +" unset as metapackage.</div>"
			});
		});
		
	};
	this.render = function(){
		this.renderMarkedList();
		this.renderArchs();
	};
	this.initContainer = function(){
		$(this.dom).empty();
		$(this.options.dom.container).append(this.options.dom.archs).append(this.options.dom.marked);
		$(this.dom).append(this.options.dom.container);
	};
	this.init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.initContainer();
	};
	this.init();
});

appdb.repository.ui.views.Files = appdb.ExtendClass(appdb.View,"appdb.repository.ui.views.Files", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		group: o.group || "release",
		type: o.type || "architecture",
		dom: {
			toolbar : null,
			tabs: null,
			filesview: null,
                        loader: null
		},
		readonly: o.readonly || false,
		filesview: [],
		marked: {}
	};
	this.filterData = function(d){
		
		return d;
	};
	this.transformData = function(d){
		var res = {os: []};
		var data = d.poarelease || [];
		data = $.isArray(data)?data:[data];
		
		//get unique OSes
		$.each(data, function(i,e){
			var found = false;
			e.target.arch.incrementalsupport = e.target.incrementalsupport;
			e.target.arch.cansupport = e.target.cansupport;
			$.each(res.os, function(ii,ee){
				if( found === false && e.target.os.id == ee.id ){
					found = ee;
				}
			});
			if( found === false ){
				var newos = $.extend({}, e.target.os);
				newos.archs = [$.extend({poapackage:[]}, e.target.arch)];
				res.os.push(newos);
			}else{
				//find new archs
				var foundarch = false;
				$.each(found.archs, function(iii,arch){
					if( !foundarch && arch.name == e.target.arch.name){
						foundarch = arch;
					}
				});
				if( foundarch === false ){
					
					found.archs.push($.extend({poapackage:[]}, e.target.arch));
				}
			}
		});
		
		//Collect poapackages for each target
		$.each(res.os, function(i,os){
			$.each(os.archs, function(ii,arch){
				$.each(data, function(iii, poarelease){
					if( $.trim(poarelease.target.os.id) === $.trim(os.id) && $.trim(poarelease.target.arch.id) === $.trim(arch.id) ){
						var packages = poarelease.poapackage || [];
						packages = $.isArray(packages)?packages:[packages];
						res.os[i].archs[ii].repositoryinfo = poarelease.repositoryinfo || {};
						res.os[i].archs[ii].poapackage = res.os[i].archs[ii].poapackage || [];
						res.os[i].archs[ii].poapackage = $.isArray(res.os[i].archs[ii].poapackage)?res.os[i].archs[ii].poapackage:[res.os[i].archs[ii].poapackage];
						res.os[i].archs[ii].poapackage = res.os[i].archs[ii].poapackage.concat(packages);
					}
				});
			});
		});
		return res;
	};
	this.setLoading = function(loading, text){
		loading = (typeof loading === "boolean")?loading:false;
		$(this.dom).find(".loader").remove();
		$(this.dom).find(".errormessage").remove();
		if( loading ){
			text = text || "loading";
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).append(loader);
			$(this.dom).children(".actions .action").addClass("hidden");
		}
	};
    this.getReleaseData = function(){
		var res = null;
		if( !this.options.data ){
			return null;
		}
		if( $.isArray(this.options.data) ){
			res = this.options.data[0];
			if( !res ){
				return null;
			}
		}else{
			res = this.options.data;
		}
		return res;
	};
	this.renderToolbar = function(d){
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() === false ){
			$(this.options.dom.toolbar).addClass("hidden");
			return;
		}
		if( $(this.options.dom.toolbar).hasClass("hidden") ){
			$(this.options.dom.toolbar).removeClass("hidden").find(".action").clearQueue().css({"opacity":"0"}).animate({"opacity":"1"}, 800);
			$(this.options.dom.toolbar).find(".action").clearQueue().css({"outline-style":"solid","outline-color":"#F2B529","outline-width":"8px"}).animate({"outline-color":"#ffffff","outline-width":"0px"}, 1500);
		}
		this.subviews.toolbar.subscribe({event: "changing", callback: function(v){
				this.setLoading(true,"applying changes");
				$(this.parent.dom).addClass("changing");
		}, caller: this}).subscribe({event: "changed", callback: function(v){
				this.setLoading(false);
				$(this.parent.dom).removeClass("changing");
		}, caller: this});
		this.subviews.toolbar.render(this.options.marked);
	};
	this.renderTabs = function(d){
		this.subviews.tabcontainer.render(d);
	};
    this.setupUploader = function(){
          var releaseuploader = (this.parent.getUploader)?this.parent.getUploader():false;
          if( !releaseuploader ) return;
          var uploaders = releaseuploader.getAllTargetHandlers() || [];
          if( uploaders.length === 0) return;
          var i, len = uploaders.length;
          for(i=0; i<len; i+=1){
            uploaders[i].unsubscribeAll(this);
            uploaders[i].subscribe({event: "startupload", callback: function(){
                setTimeout((function(self){
                    return function(){
                        self.subviews.toolbar.setMessage("<span>...uploading files</span>",true);
                    };
                })(this),1);
            }, caller: this}).subscribe({event: "filecomplete", callback: function(v){
                setTimeout((function(self){
                    return function(){
                        if( v && v.response && v.response.upload && $.trim(v.response.upload.result).toLowerCase() === "success" && v.response.upload.poapackage){
                            var poapackage = v.response.upload.poapackage;
                            var targetid = v.response.upload.targetid;
                            self.addPackage(targetid, poapackage);
                        }
                    };
                })(this),1);                              
            }, caller: this}).subscribe({event: "complete", callback: function(v){
                setTimeout((function(self){
					return function(){
						self.render($.isArray(self.options.data)?self.options.data[0]:self.options.data);
						var releasedata = self.parent.getReleaseData();
						if( releasedata && releasedata.state && releasedata.state.id == 3){
							appdb.repository.ui.CurrentReleaseManager.updateReleaseDataProperty(releasedata.id,"state", {name:"Unverified",id:1});
						}
						self.subviews.toolbar.setMessage();
					};
                })(this),1);
            },caller: this});
          }
          if( releaseuploader.isRunning() ){
              this.subviews.toolbar.setMessage("<span>...uploading files</span>",true);
          }else{
              this.subviews.toolbar.setMessage();
          }
        };
    this.addPackage = function(targetid, pckg){
            if(!targetid || !pckg) return;
            var packageid = pckg.id;
            this.options.data = $.isArray(this.options.data)?this.options.data:[this.options.data];
            var optdata = this.options.data;
            var foundindex = false;
            $.each(optdata , function(i,e){
                  var poas = optdata[i].poarelease || [];
                  poas = $.isArray(poas)?poas:[poas];
                  if( foundindex === false ){
                    $.each(poas, function(poaindex,poa){
                          if(foundindex === false && poa.target.id == targetid){
                              foundindex = poaindex;
                        }
                    });
                      
                    if( foundindex !== false ){
                        poas[foundindex].poapackage = poas[foundindex].poapackage || [];
                        poas[foundindex].poapackage = $.isArray(poas[foundindex].poapackage)?poas[foundindex].poapackage:[poas[foundindex].poapackage];
                        var exists = false;
                        $.each(poas[foundindex].poapackage, function(pi, p){
                            if(exists === false && p.id == packageid) {
                                exists = true;
                            }
                        });
                        if(exists === false){
                            poas[foundindex].poapackage.push(pckg);
                        }
                    }
                  }
                  optdata[i].poarelease = poas;
            });
            if( foundindex === false ){
                var newpoa = {poapackage: [pckg], target: appdb.repository.model.Targets.getById(targetid)};
                if( optdata.length === 0 ){
                    optdata.push({poarelease : []});
                }
                optdata[0].poarelease = optdata[0].poarelease ||[];
                optdata[0].poarelease = $.isArray(optdata[0].poarelease)?optdata[0].poarelease:[optdata[0].poarelease];
                optdata[0].poarelease.push(newpoa);
                this.options.data = optdata;
                this.publish({event: "changed", value: this.options.data});
            }
        };
        
	this.render = function(d){
            $(this.dom).find(".warningmessage.top").remove();
            $(this.parent.dom).find(".emptymessage").addClass("hidden");
            this.options.marked = {};
		this.options.data = d || this.options.data || [];
		this.options.data = $.isArray(this.options.data)?this.options.data:[this.options.data];
		//do some clean up
		$.each(this.options.filesviews, function(i,e){
			e.reset();
			e = null;
		});
		this.options.filesviews = [];
		$(this.options.dom.files).empty();
		$(this.options.dom.tabs).empty();
		
		//FIlter data
		var filteredData = this.filterData(d);
		var transformedData = this.transformData(filteredData);
		
		//Start rendering collected data
		this.renderToolbar(d);
		appdb.repository.ui.views.Tabular.clearScrollTimer();
		if(transformedData.os.length > 0 ){
			this.renderTabs(transformedData.os);
			$.each(transformedData.os, (function(self){
				return function(i,e){
					self.addFilesView(e);
				};
			})(this));
			this.subviews.tabcontainer.selectTabById();
		}else{
			$(this.parent.dom).find(".emptymessage").removeClass("hidden");
		}
		this.setupUploader();
	};
	this.showFileTab = function(id){
		$(this.options.dom.files).find(".fileview").addClass("hidden");
		$(this.options.dom.files).find(".fileviewid_" + id).removeClass("hidden");
	};
	this.addMarkedItem = function(group, id, value){
		if( $.trim(group)==="" || $.trim(id) === "" || $.trim(value)==="" ){
			return;
		}
		this.options.marked[group] = this.options.marked[group] || {};
		this.options.marked[group][id] = value;
                this.renderToolbar();
	};
	this.removeMarkedItem = function(group, id){
		if( $.trim(group)==="" || $.trim(id) === "" ){
			return;
		}
		this.options.marked[group] = this.options.marked[group] || {};
		if( this.options.marked[group][id] ){
			delete this.options.marked[group][id];
		}
                this.renderToolbar();
	};
	this.getMarkedItems = function(group){
		if( $.trim(group) !== ""){
			return this.options.marked[group] || null;
		}
		return this.options.marked || null;
	};
    this.setItemsById = function(action, ids){
            this.options.data = $.isArray(this.options.data)?this.options.data:[this.options.data];
            var optdata = this.options.data;
            $.each(optdata , function(i,e){
                var poas = optdata[i].poarelease || [];
                poas = $.isArray(poas)?poas:[poas];
                var poastoremove = [];
                $.each(poas, function(poaindex,poa){
                   var pcks = poa.poapackage || [];
                   pcks = $.isArray(pcks)?pcks:[pcks];
                   var toberemoved = [];
                   $.each(pcks, function(pckindex, pck){
                       $.each(ids, function(idind,id){
                           if( pck.id == id){
                               switch(action){
                                   case "remove":
                                      toberemoved.push(pckindex);
                                       break;
                                   case "meta":
                                       pck.level = "meta";
                                       break;
                                   case "dep":
                                       pck.level = "dep";
                                       break;
                               }
                           }
                       });
                       pcks[pckindex] = pck;
                   });
                   if( toberemoved.length > 0 ){
                       for(var j = toberemoved.length-1; j>=0; j-=1){
                           pcks.splice(toberemoved[j],1);
                       }
                   }
                   poas[poaindex].poapackage = pcks;
                   if(pcks.length === 0){
                       poastoremove.push(poaindex);
                   }
                });
                if( poastoremove.length > 0 ){
                   for(var k = poastoremove.length-1; k>=0; k-=1){
                       poas.splice(poastoremove[k],1);
                   }
               }
                optdata[i].poarelease = poas;
            });
            this.options.data = optdata;
            this.publish({event: "changed", value: this.options.data});
        };
	this.addFilesView = function(d, index){
		var container = $(document.createElement("div")).addClass("fileviewid_"+ d.id).addClass("fileview").addClass("hidden");
		$(this.options.dom.files).append(container);
		var f = new appdb.repository.ui.views.files.OsItem({
			container: container,
			parent: this,
			readonly: this.options.readonly || false
		});
		
		d.archs.sort(function(a,b){
			if( a.name > b.name ) return 10;
			if( a.name > b.name ) return -1;
			return 0;
		});
		f.render(d);
		f.getAvailableArchitectures = (function(archs){
			return function(){
				return archs;
			};
		})(d.archs);
		
		$(f.dom).prepend("<div class='toolbarcontainer'></div>");
		var t = new appdb.repository.ui.views.FilesTreeToolbar({
			container: $(f.dom).find(".toolbarcontainer"),
			parent: f
		});
		setTimeout(function(){
			t.render();
		},1);
		
		f.subscribe({event: "addmark", callback: (function(toolbar){
				return function(v){
					this.addMarkedItem(v.group, v.id, v.value);
					toolbar.render();
                                        this.renderToolbar();
				};
		})(t), caller: this}).subscribe({event:"removemark", callback: (function(toolbar){
			return function(v){
				this.removeMarkedItem(v.group, v.id);	
				toolbar.render();
                                this.renderToolbar();
			};
		})(t), caller: this});
		this.options.filesviews.push(f);
	};
	this.initContainer = function(){
		this.subviews = {};
		var toolbar = $(document.createElement("div")).addClass("toolbar");
		var ostabcontainer = $(document.createElement("div")).addClass("ostabs");
		var filescontainer = $(document.createElement("div")).addClass("filescontainer");
		
		this.options.dom.toolbar = toolbar;
		this.options.dom.files = filescontainer;
		this.options.dom.tabs = ostabcontainer;
		$(this.dom).append(toolbar).append(ostabcontainer).append(filescontainer);
		
		this.options.filesviews = [];
		this.subviews.tabcontainer = new appdb.repository.ui.views.Tabular({
			parent: this,
			container: this.options.dom.tabs,
			onRenderItem: function(d){
				var div = $(document.createElement("div")).addClass("icontext");
				var img =  $(document.createElement("img")).attr("alt","");
				var span =  $(document.createElement("span"));
				var src = appdb.repository.utils.getOsImagePath(d.name);
				$(img).attr("src",src);
				$(span).append(d.displayname + " " + d.displayflavor);
				$(div).append(img).append(span);
				return div;
			},
			onSelectItem: (function(self){
				return function(index, data){
					self.showFileTab(data.id);
				};
			})(this)
		});
                this.subviews.toolbar = new appdb.repository.ui.views.FilesToolBar({
                    container: $(this.options.dom.toolbar),
                    parent: this
                });
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
		
	};
	this.init();
});

appdb.repository.ui.views.ReleaseFiles = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleaseFiles", function(o){
	this.options = {
		parent: o.parent || null,
		uploader: null
	};
	this.setBuildState = function(time){
		if( time == false ){
			$(this.dom).children(".toolbar").removeClass("hidden");
			$(this.dom).find(".building").remove();
		}else{
			$(this.dom).children(".toolbar").addClass("hidden");
			if( $(this.dom).children(".building").length > 0 ){
				$(this.dom).children(".building").find(".timeout").html("[" + time + "]");
			}else{
				var div = $(document.createElement("div")).addClass("icontext").addClass("building");
				var img = $(document.createElement("img")).attr("alt","").attr("src","/images/ajax-loader-trans-orange.gif");
				var span = $(document.createElement("span")).append("<span>...building repositories.</span><span class='timeout'>[" + time + "]</span>");
				var divtip = $(document.createElement("div")).addClass("icontext").addClass("tip");
				var data = this.getReleaseData();
				if( data.state && $.trim(data.state.name).toLowerCase()==="production" ){
					$(divtip).html("You won't be able to download files until the repositories are built.");
				}else{
					$(divtip).html("You won't be able to edit the release files until the repositories are built.");
				}
				$(div).append(img).append(span).append(divtip);
				$(this.dom).prepend(div);
			}
		}
	};
	this.getUploader = function(){
		return this.options.uploader;
	};
	this.renderToolbar = function(canrender,d){
		var iscandidate = ( d && d.state && $.trim(d.state.name).toLowerCase() === "candidate" );
		canrender = (typeof canrender === "boolean")?canrender:true;
		var tb = $(this.dom).find(".toolbar");
		$(tb).removeClass("hidden");
		$(tb).find(".action.uploadfile").removeClass("hidden").removeClass("selected");
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() && canrender) {
			$(tb).find(".action.uploadfile > a").off("click").on("click", function(ev){
				ev.preventDefault();
				$(this).parent().toggleClass("selected");
				return false;
			});
			if(iscandidate){
				$(tb).find(".action.uploadfile .actions .warningmessage").removeClass("hidden");
			}else{
				$(tb).find(".action.uploadfile .actions .warningmessage").addClass("hidden");
			}
		}else{
			$(tb).addClass("hidden");
			$(tb).find(".action.uploadfile").addClass("hidden");
		}
	};
	this.renderTargetSelector = function(d,excludetargets){
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
			this.subviews.targetselector = new appdb.repository.ui.views.TargetSelectionList({
				container: $(this.dom).find(".toolbar .action.uploadfile .targetselection"),
				parent: this
			});
			this.subviews.targetselector.subscribe({event:"add", callback: function(v){
					this.subviews.targetlist.appendTargetItem(v);
					this.subviews.targetselector.render(undefined,this.subviews.targetlist.getSelectedTargets());
			}, caller: this});
			this.subviews.targetselector.render(d,excludetargets);
		}
	};
	this.renderTargetList = function(d){
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
			this.subviews.targetlist = new appdb.repository.ui.views.TargetList( {container: $(this.dom).find(".toolbar .action.uploadfile ul.targets"), parent: this});
		}
	};
	this.renderFiles = function(d){
            this.subviews.files.render(d);
	};
	this.getReleaseData = function(){
		return this.parent.getData();
	};
	this.render = function(d){
                $(this.dom).find(".warningmessage.top").remove();
		d = d || {};
		var canrender = (d.state && $.trim(d.state.name).toLowerCase() !== "production");
		this.renderToolbar(canrender,d);
		
		if( this.options.uploader && this.options.uploader.isRunning() === false ){
			this.options.uploader.unsubscribeAll(this);
			this.options.uploader = null;
		}
		this.options.uploader = this.options.parent.uploadreleasehandler;
		if( canrender ){
			this.renderTargetSelector();
			if( !this.subviews.targetlist ){
				this.renderTargetList(d);
			}
			if( this.subviews.targetlist ){
				this.subviews.targetlist.reset();
				this.subviews.targetlist.render();
			}
		}
		
		this.renderFiles(d);
	};
	this.initFiles = function(){
		if( this.subviews.files ){
			this.subviews.files.unsubscribeAll(this);
			this.subviews.files.reset();
		}
		
		this.subviews.files = new appdb.repository.ui.views.Files({
			container: $(this.dom).find(".filesviewcontainer"),
			parent: this
		});
                this.subviews.files.subscribe({event: "changed", callback: function(v){
                        this.publish({event:"changed", value: v});
                }, caller: this});
	};
	this.initContainer = function(){
		this.renderTargetList();
		this.renderTargetSelector();
		this.initFiles();
	};
	this.init = function(){
		this.dom = $(o.container);
		this.initContainer();
	};
	this.init();
});

appdb.repository.ui.views.RepositoryAreaProperty = appdb.ExtendClass(appdb.repository.ui.views.DataProperty, "appdb.repository.ui.views.RepositoryAreaProperty", function(o){
	this._model = new appdb.repository.model.RepositoryAreaProperty();
	this.dataModelProperty = "repositoryarea";
});

appdb.repository.ui.views.PropertyTOC = function(o){
	this.scrollTo = function(property){
		property = $.trim(property) || "";
		if(property === ""){
			window.scroll(0,0);
			return;
		}
		var self = this;
		var pos = $(self.parent.dom).find(".property."+property).offset().top;
		pos = parseInt(pos);
		window.scroll(0,pos-70);
		$(self.parent.dom).find(".property."+property).stop().removeAttr("style").addClass("highlight").animate(
			{"background-color":"white",
				"borderTopColor":"white",
				"borderLeftColor":"white",
				"borderRightColor":"white",
				"borderBottomColor":"white"
			},4000, 
			(function(elem){
				return function(){
					$(elem).removeAttr("style").removeClass("highlight");
				};
			})($(self.parent.dom).find(".property."+property) ));
	};
	this.render = function(){
		var self = this;
		var toc = $(document.createElement("div")).addClass("wikitoc");
		var header = $(document.createElement("div")).addClass("header");
		var list = $(document.createElement("ul"));
		var allprops = $(this.parent.dom).find(".property > .header");
		var props = [];
		var activeprops = $(this.dom).data("mincount") || 0;
		$(this.dom).find(".wikitoc").remove();
		$(this.dom).append(toc);
		$(toc).append(header).append(list);
		$(header).html("<h3>Contents</h3><span class='hidecontents'>[<a href='#' title=''>hide</a>]</span>");
		
		//Filter out hidden properties
		$(allprops).each(function(i,e){
			if( $(e).parent().hasClass("hideempty") ){
				if( $.trim($(e).parent().find(".value").text()).replace(/\ /g,"") === "" ){
					if(!userID || !appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
						$(e).parent().addClass("hidden");
						return ;
					}
				}else{
					$(e).parent().removeClass("hidden");
				}
			}
			props.push(e);
		});
		
		//Check if properties equal or more than the minimum 
		//count of displayed properties. If not hide and exit.
		if(activeprops > props.length) {
			$(this.dom).addClass("hidden");
			return;
		}else{
			$(this.dom).removeClass("hidden");
		}
		
		$(props).each(function(i,e){
			var li = $(document.createElement("li"));
			var a = $(document.createElement("a"));
			var id = $(e).text().toLowerCase().replace(/\ /g,"");
			$(a).attr("href","#").attr("title",$.trim($(e).text())).attr("id","wikitoc_" + id).html($(e).html());
			$(a).on("click", (function(_id){
				return function(e){
					if( e.which != 2 ) {
						self.scrollTo(_id);
					}
					e.preventDefault();
					return false;
				};
			})(id));
			$(li).append(a);
			$(list).append(li);			
		});
		
		$(this.dom).find(".wikitoc > div.header > span.hidecontents > a").on("click", function(){
			if ( $(self.dom).find(".wikitoc > ul:last").css("display") === "none" ) {
				$(self.dom).find(".wikitoc > ul:last").css({"display":"block"});
				$(this).text("hide");
			} else {
				$(self.dom).find(".wikitoc > ul:last").css({"display":"none"});
				$(this).text("show");
			}
		});
		
		
	};
	this.init = function(){
		this.dom = o.container || null;
		this.parent = o.parent || null;
	};
	this.init();
};

appdb.repository.ui.views.GridList = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.GridList", function(o){
	this.options = {
		parent: o.parent || null,
		data: o.data || [],
		css: o.css || "",
		dump: function(){return true;},
		passthru: function(d){return d;},
		canDisplay : null,
		formatItemData: null,
		defaults : {
			emptyItemText: "-",
			emptyDataText: "No data available"
		}
	};
	this.reset = function(){
		this.initContainer();
	};
	this.createHeaderColumn = function(c){
		var col = $(document.createElement("th"));
		var div = $(document.createElement("div"));
		if(c.css) {
			$(div).addClass(c.css);
		}
		if(c.style){
			$(div).attr("style",c.style);
		}
		$(div).append("<span>"+ (c.displayName || c.name) +"</span>" );
		$(col).append(div);
		return col;
	};
	this.renderHeader = function(d){
		var self = this;
		var row = document.createElement("tr");
		
		$.each(this.options.columns, function(i,col){
			if( col.canDisplay(d) !== false ) {
				if( col.dom ) $(col.dom).remove();
				col.dom = self.createColumnHeader(col);
				$(row).append(col.dom);
			}
			return col;
		});
		
		$(this.header).append(row);
	};
	this.createColumnHeader = function(c){
		var th = document.createElement("th");
		var content = $(document.createElement("div")).addClass("content");
		var val = $(document.createElement("span")).addClass("value");
		$(content).append(val);
		$(th).append(content);
		$(val).append(c.displayName || c.name);
		$(th).addClass("column").addClass(c.name);
		
		if( c.classes ){
			$(th).addClass(c.classes);
		}
		
		if( typeof c.css === "string"){
			$(th).attr("style", c.css)
		}else if( typeof c.style === "object"){
			$(th).css(c.style);
		}
		
		return th;
	};
	this.getColumnWidth = function(index){
		var cols = $(this.header).find("tr > th");
		if(cols.length > index){
			return $($(cols).get(index)).width();
		}
		return 0;
	};
	this.isManagedType = function(t){
		return ( $.inArray(t, ["text","date"]) > -1 )?true:false;
	};
	this.createCell = function(d,col){
		var finder = appdb.repository.utils.FindData;
		var td = document.createElement("td");
		var content = $(document.createElement("div")).addClass("content");
		var val = $(document.createElement("div")).addClass("value");
		var data = finder(d, (col.dataPath || col.name) );
		
		$(content).append(val);
		$(td).addClass("cell").addClass(col.name).append(content);
		
		if( !data ){
			this.renderEmptyItem(td,col);
		}else if(col.type === "list"){
			data = $.isArray(data)?data:[data];
			$(val).append(this.renderList(data,col));
		} else if( appdb.repository.utils.FindData(window, col.type) ) {
			var viewtype = appdb.repository.utils.FindData(window, col.type);
			if( viewtype ){
				var view = new viewtype({container: content});
				view.render(data);
			}
		} else {
			if( col.format ){
				$(val).append( col.format(data) ); 
			}else {
				$(val).append(data); 
			}
			
			if( col.onRender ){
				col.onRender(val,d);
			}
		}
		if( $.trim(col.classes) !== "" ){
			$.each(col.classes.split(" "), function(i,e){
				if( e ){
					$(val).addClass(e);
				}
			});
		}
		return td;
	};
	this.addRow= function(d){
		var self = this;
		var tr = $(document.createElement("tr")).addClass("row");
		
		$.each(this.options.columns, function(i,col){
			if( col.canDisplay(d) !== false ){
				$(tr).append(self.createCell(d,col));
			}
		});
		
		$(this.rows).append(tr);
	};
	this.renderList = function(d,column){
		var ul = document.createElement("ul");
		$.each(d, function(i,e){
			var li = document.createElement("li");
			$(li).append("<span class='value'></span>");
			if( column.onRender ){
				$(li).find(".value").append( column.onRender($(li).find(".value"),e) );
			}else{
				$(li).find(".value").append(e);
			}
			$(ul).append(li);
		});
		return ul;
	};
	this.renderEmptyItem = function(dom,col){
		col = col || {};
		var e = col.empty || this.options.defaults.emptyItemText;
		$(dom).empty().append($("<div class='content emptyitem'><span class='value'>"+e+"</span></div>"));
	};
	this.renderEmptyData = function(){
		var e = this.options.defaults.emptyDataText;
		$(this.content).empty().append($("<div class='emptyItem'>"+e+"</div>"));
	};
	this.render = function(d){
		var self = this;
		this.reset();
		this.renderHeader();
		this.options.data = d || [];
		this.options.data = $.isArray(this.options.data)?this.options.data:[this.options.data];
		
		if( this.options.data.length === 0 ){
			this.renderEmptyData();
		} else {
			$.each(this.options.data, function(i,e){
				if( self.options.canDisplayRowData(e) ){
					self.addRow( self.options.formatItemData(e) );
				}
			});
		}
		if( $(this.rows).find(".row").length === 0 ){
			$(this.header).addClass("hidden");
			$(this.dom).parent().append("<div class='emptymessage'>" + this.options.defaults.emptyDataText + "</div>");
		}
	};
	this.initContainer = function(){
		$(this.dom).empty();
		
		this.grid = $(document.createElement("div")).addClass("gridlist");
		
		this.content = $(document.createElement("table")).addClass("content");
		
		this.header = $(document.createElement("thead")).addClass("header");
		this.rows = $(document.createElement("tbody")).addClass("rows");
		this.footer = $(document.createElement("div")).addClass("footer");
		
		$(this.content).attr("cellspacing","0").attr("cellpadding","0");
		
		$(this.content).append(this.header).append(this.rows);
		$(this.grid).append(this.content).append(this.footer);
		$(this.dom).append(this.grid);
	};
	this.init = function(){
		this.dom = $(o.container);
		this.options.columns = o.columns || [];
		this.options.defaults = $.extend(this.options.defaults, o.defaults);
		this.options.canDisplayRowData = o.canDisplayRowData || this.options.dump;
		this.options.formatItemData = o.formatItemData || this.options.passthru;
		var i, len = this.options.columns.length;
		for(i=0; i<len; i+=1){
			this.options.columns[i].canDisplay = this.options.columns[i].canDisplay || this.options.dump;
		}
	};
	this.init();
});
appdb.repository.ui.views.GridTreeColumn = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.GridTreeColumn", function(o){
	var _dump = function(){return true;};
	var _passthrough = function(v){return v;};
	this.options = {
		parent: o.parent || null,
		name: $.trim(o.name),
		index: o.index || -1,
		displayName: o.displayName || o.name || "",
		onRenderCell:  o.onRenderCell || _passthrough,
		canRenderCell: o.canREnderCell || _dump,
		dataType: $.trim(o.dataType) || "text",
		dataPath: $.trim(o.dataPath),
		cellCss: o.cellCss || "",
		cellClass: $.trim(o.cellClass),
		headerCss: $.trim(o.headerCss),
		headerClass: $.trim(o.headerClass)
	};
	
	this.getName = function(){
		if( this.options.name ){
			return this.options.name;
		}
		return "cell" + this.getIndex();
	};
	
	this.getDisplayName = function(){
		return this.options.displayName;
	};
	
	this.getIndex = function(){
		return this.options.index;
	};
	
	this.getHeaderCellContent = function(){
		var div = $(document.createElement("div")).addClass("gridtreeheadercell");
		var content = $(document.createElement("div")).addClass("content");
		
		$(content).text( this.getDisplayName() );
		$(div).append(content);
		
		if( this.options.headerCss ){
			$(div).css(this.options.headerCss);
		}
		
		if( this.options.headerClass ){
			$(div).addClass(this.options.headerClass);
		}
	};
	
	this.getCellData = function(d){
		var res = d;
		if( $.trim(this.options.dataPath) !== "" ){
			res = appdb.repository.utils.FindData(d, this.options.dataPath);
		}
		return res;
	};
	
	this.getRow = function(){
		return this.parent;
	};
	
	this.getCellContent = function(d,dom){
		d = d || "";
		var value = this.getCellData(d);
		var index = this.getIndex();
		var name = this.getName();
		var cell = (dom || $(document.createElement("div"))).addClass("gridtreecell");
		var content = $(document.createElement("div")).addClass("content");
		
		$(content).html(value);
		content = this.options.onRenderCell.call(this,content, d);
		$(cell).append(content);
		$(cell).data("GridTreeColumn", this);
		
		if( index >= 0 ){
			$(cell).data("index", index);
		}
		
		if( name ){
			$(cell).addClass(name);
		}
		
		if( this.options.cellClass ){
			$(cell).addClass(this.options.cellClass);
		}
		
		if( this.options.cellCss ){
			$(cell).css(this.options.cellCss);
		}
		
		return cell;
	};
});
appdb.repository.ui.views.GridTree = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.GridTree", function(o){
	var _dump = function(){return true;};
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		containsType: $.trim(o.containsType),
		data: o.data || {},
		dataPath: $.trim(o.dataPath),
		childrenDataParth: $.trim(o.childrenDataPath),
		columns: o.columns || [],
		level: o.level || 0,
		rootItem: o.rootItem || null,
		canRender: _dump,
		marked: o.marked || {},
		readonly: o.readonly || false,
		dom : {
			item: null,
			children: null
		},
		actions : {}
	};
	this.reset = function(){
		this.unsubscribeAll();
		$(this.dom).empty();
	};
	this.getTree = function(){
		return this.parent;
	};
	this.toggleChildren = function(){
		if( $(this.options.dom.children).is(":visible")){
			$(this.options.dom.children).slideUp("fast");
		}else{
			$(this.options.dom.children).slideDown("fast");
		}
	};
	this.isReadOnly = function(){
		return (this.options.readonly === true)?true:false;
	};
	this.addAction = function(name, handler){
		if( !name || $.trim(name) === "" ) return;
		if( !handler ) return;
		
		this.options.actions = this.options.actions  || {};
		if( !this.options.actions[name] ){
			this.options.actions[name] = [];
		}
		this.options.actions[name] = $.isArray(this.options.actions[name])?this.options.actions[name]:[this.options.actions[name]];
		
		this.options.actions[name].push(handler);
	};
	this.getAvailableActionsTypes = function(){
		var res = [];
		this.options.actions = this.options.actions || {};
		for(var i in this.options.actions){
			if( this.options.actions.hasOwnProperty(i) ){
				res.push(i);
			}
		}
		return res;
	};
	this.getActionHandlers = function(name){
		if( !name || $.trim(name) === "" ) return [];
		this.options.actions = this.options.actions || {};
		if( !this.options.actions[name] ){
			this.options.actions[name] = [];
		}
		this.options.actions[name] = $.isArray(this.options.actions[name])?this.options.actions[name]:[this.options.actions[name]];
		return this.options.actions[name];
	};
	this.isLeaf = function(){
		return ( this.options.containsType == "" )?true:false;
	};
	this.hasChildren = function(){
		return !this.isLeaf();
	};
	this.isRoot = function(){
		if( !this.parent || typeof this.parent.isLeaf !== "function" || this.options.rootItem == null){
			return true;
		}
		return false;
	};
	this.getRootItem = function(){
		return this.options.rootItem || this;
	};
	this.getColumn = function(name){
		var res = null;
		if( typeof name === "number"){
			res =  this.options.columns[name];
		} else {
			$.each(this.options.columns, function(i,e){
				if( e.name == name ){
					res = e;
				}
			});
		}
		return res;
	};
	this.renderItem = function(d){
		$(this.options.dom.item).empty();
		var self = this;
		if( !this.isRoot() ){
			$.each(this.options.columns, function(i,e){
				var li = $(document.createElement("li"));
				li = e.getCellContent(d,li);
				$(self.options.dom.item).append(li);
			});
		}
		if( this.isRoot() ){
			$(this.options.dom.item).addClass("hidden");
		}
		if( this.options.name ){
			$(this.options.dom.item).addClass(this.options.name);
		}
		return this.options.dom.item;
	};
	
	this.renderChildren = function(d){
		$.each(this.subviews, function(i,e){
			e.reset();
			e = null;
		});
		this.subviews = [];
		$(this.options.dom.children).empty();
		var childType = appdb.FindNS(this.options.containsType, false);
		if( !childType ) return this.options.dom.children;
		var children = this.options.dom.children;
		
		$.each(d, (function(self){
			return function(i,e){
				var li = $(document.createElement("li"));
				var c = new childType({parent: self, container: li, rootItem: self.getRootItem()});
				$(children).append(li);
				c.render(e);
				self.subviews.push(c);
				if( self.isRoot() ){
					$(li).addClass("root");
				}
			};
		})(this));
		if( this.options.name ){
			$(children).addClass(this.options.name);
		}
		this.options.dom.children = children;
		return children;
	};
	this.retrieveData = function(d){
		var res = d || {};
		if( this.options.dataPath ){
			res = appdb.repository.utils.FindData(res, this.options.dataPath);
		}
		return res;
	};
	this.retrieveChildrenData = function(d){
		var res = d || {};
		if( this.options.childrenDataPath ){
			res = appdb.repository.utils.FindData(res, this.options.childrenDataPath);
			
		}
		res = res || [];
		res = $.isArray(res)?res:[res];
		return res;
	};
	this.render = function(d){
		this.options.actions = {};
		this.options.data = d;
		this.renderItem(d);
		var data = this.retrieveChildrenData(d);
		if( this.isRoot() ){
			this.options.rootItem = this;
		}
		if( !this.isLeaf() && data.length > 0){
			this.renderChildren(data);
		}else{
			$(this.options.dom.children).remove();
		}
		
	};
	this.addMarkedItem = function(group,id,value){
		if( $.trim(group)==="" || $.trim(id)==="" || $.trim(value) === "" ){
			return;
		}
		if( this.isRoot() ){
			if( !this.options.marked ){
				this.options.marked = {};
			}
			if( !this.options.marked[group] ){
				this.options.marked[group] = {};
			}
			this.options.marked[group][id] = value;
			this.publish({event: "addmark", value: {group: group, id: id,value: value}});
		}else{
			this.getRootItem().addMarkedItem(group,id,value);
		}
		
	};
	this.removeMarkedItem = function(group,id,value){
		if( $.trim(group)==="" || $.trim(id)===""){
			return;
		}
		if( this.isRoot() ){
			if( !this.options.marked ){
				this.options.marked = {};
			}
			if( !this.options.marked[group] ){
				this.options.marked[group] = {};
			}
			if(this.options.marked[group][id]){
				delete this.options.marked[group][id];
				this.publish({event: "removemark", value: {group: group, id: id}});
			}
		}else{
			this.getRootItem().removeMarkedItem(group,id,value);
		}
	};
	this.getMarkedItems = function(group){
		if( this.isRoot() ){
			if( $.trim(group) !== "" ){
				return this.options.marked[group] || {};
			}else{
				return this.options.marked;
			}
		}else{
			return this.getRootItem().getMarkedItems(group);
		}
	};
	this.initColumns = function(){
		this.options.columns = this.options.columns || [];
		this.options.columns = $.isArray(this.options.columns)?this.options.columns:[this.options.columns];
		var cols = [];
		$.each(this.options.columns, (function(self){
			return function(i,e){
				e.parent = self;
				e.index = i;
				var c = new appdb.repository.ui.views.GridTreeColumn(e);
				cols.push(c);
			};
		})(this));
		this.options.columns = cols;
	};
	this.initContainer = function(){
		this.subviews = [];
		$(this.dom).find(".gridtreerow").remove();
		$(this.dom).find(".gridtreechildren").remove();
		this.initColumns();
		this.options.dom.item = $(document.createElement("ul")).addClass("gridtreerow");
		this.options.dom.children = $(document.createElement("ul")).addClass("gridtreechildren");
		$(this.dom).append(this.options.dom.item).append(this.options.dom.children);
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this.init();
});
appdb.repository.ui.views.ToggleButton = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ToggleButton", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		state: false,
		image: $.trim(o.image),
		text: ($.trim(o.text)!=="")?o.text:"",
		tooltip: ($.trim(o.tooltip)!=="")?o.tooltip:"",
		checkImage: $.trim(o.checkImage),
		checkText: ($.trim(o.checkText)!=="")?o.checkText:"",
		checkTooltip: ($.trim(o.checkTooltip)!=="")?o.checkTooltip:"",
		checkStateName: $.trim(o.checkStateName),
		stateName: $.trim(o.stateName),
		disabled: (typeof o.disabled === "boolean")?o.disabled:false,
		dom: {
			checked: $(document.createElement("div")).addClass("checked").addClass("icontext"),
			unchecked: $(document.createElement("div")).addClass("unchecked").addClass("icontext")
		},
		className: $.trim(o.className)
	};
	this.isChecked = function(){
		return this.options.state;
	};
	this.getStateName = function(){
		if( this.isChecked() ){
			return this.options.checkStateName;
		}
		return this.options.stateName;
	};
	this.setChecked = function(checked){
		if( this.isChecked() !== checked){
			this.options.state = (typeof checked === "boolean")?checked:false;
			this.render(this.options.state);
			this.publish({event:"change", value: checked});
		}
	};
	this.renderChecked = function(){
		$(this.options.dom.checked).empty();
		if( this.options.checkImage ){
			$(this.options.dom.checked).append("<img src='" + this.options.checkImage + "' alt='' />");
		}
		
		if( this.options.checkText ){
			$(this.options.dom.checked).append("<span>" + this.options.checkText + "</span>");
		}
		
		if( this.options.checkTooltip ){
			new dijit.Tooltip({
				connectId: $(this.options.dom.checked)[0],
				label: "<div class='messagepopupcontainer'>" + this.options.checkTooltip + "</div>"
			});
		}
		if( this.options.checkStateName ){
			$(this.options.dom.checked).addClass(this.options.checkStateName);
		}
	};
	this.renderUnchecked = function(){
		$(this.options.dom.unchecked).empty();
		if( this.options.image ){
			$(this.options.dom.unchecked).append("<img src='" + this.options.image + "' alt='' />");
		}
		
		if( this.options.text ){
			$(this.options.dom.unchecked).append("<span>" + this.options.text + "</span>");
		}
		
		if( this.options.tooltip ){
			new dijit.Tooltip({
				connectId: $(this.options.dom.unchecked)[0],
				label: "<div class='messagepopupcontainer'>" + this.options.tooltip + "</div>"
			});
		}
		if( this.options.stateName ){
			$(this.options.dom.unchecked).addClass(this.options.stateName);
		}
	};
	this.render = function(checked){
		$(this.options.dom.container).removeClass("hidden");
		if( checked === true ){
			$(this.options.dom.unchecked).addClass("hidden");
			$(this.options.dom.checked).removeClass("hidden");
		}else{
			$(this.options.dom.checked).addClass("hidden");
			$(this.options.dom.unchecked).removeClass("hidden");
		}
	};
	this.initContainer = function(){
		$(this.dom).empty();
		$(this.dom).removeClass().addClass("togglebutton");
		this.options.dom.container = this.dom;
		
		this.options.dom.checked = $(document.createElement("div")).addClass("checked").addClass("icontext");
		this.options.dom.unchecked = $(document.createElement("div")).addClass("unchecked").addClass("icontext");
		
		$(this.options.dom.container).empty().append(this.options.dom.checked).append(this.options.dom.unchecked);
		this.renderChecked();
		this.renderUnchecked();
		if( this.options.className ){
			$(this.container).addClass(this.options.className);
		}
		if( !this.options.disabled ){
			$(this.options.dom.container).off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.setChecked(!self.options.state);
					return false;
				};
			})(this));
		}
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this.init();
});
appdb.repository.ui.views.files.OsItem = appdb.ExtendClass(appdb.repository.ui.views.GridTree, "appdb.repository.ui.views.files.OsItem" , function(o){
	this.options.containsType = "appdb.repository.ui.views.files.ArchItem";
	this.options.name = "os";
	this.options.childrenDataPath = "archs";
	this.options.columns = [
		{name: "osname", dataPath: "displayname", onRenderCell: function(elem, value, data){
				return elem;
		}}
	
	];
	this.init();
});

appdb.repository.ui.views.files.ArchItem = appdb.ExtendClass(appdb.repository.ui.views.GridTree, "appdb.repository.ui.views.files.ArchItem" , function(o){
	this.options.containsType = "appdb.repository.ui.views.files.PoaPackage";
	this.options.name = "arch";
	this.options.childrenDataPath = "poapackage";
	this.options.columns = [
		{name: "delete", onRenderCell: function(elem, data){
				$(elem).empty();
				if( !appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
					return elem ; 
				}
				
				var container = $(document.createElement("div")).addClass("tooglebuttoncontainer"); 
				$(elem).append(container);
				var tb = new appdb.repository.ui.views.ToggleButton({
					container: container,
					parent: this,
					checkImage: "/images/cancelicon.png",
					checkTooltip: "All packages of this architecture are marked for removal upon save. Click to unmark.",
					checkStateName: "marked",
					image: "/images/cancelicon.png",
					tooltip: "Click to mark all the packages of this architecture for removal. Changes will apply upon save.",
					stateName: "unmarked",
					disabled: !appdb.repository.ui.CurrentReleaseManager.canManageReleases()
				});
				tb.subscribe({event: "change", callback: (function(self,origdata){
						return function(v){
							var sv = self.getRow().subviews || [];
							if( tb.options.implicitChange !== true ){
								$.each(sv, function(i,e){
									var del = e.getActionHandlers("delete");
									$.each(del, function(ii,ee){
										ee.options.implicitChange = true;
										ee.setChecked(v);
									});
								});
							}
							if( tb.getStateName() === "unmarked" ){
								$(self.getRow().dom).find(".gridtreerow.arch").removeClass("action-mark-remove");
							}else{
								$(self.getRow().dom).find(".gridtreerow.arch").addClass("action-mark-remove");
							}
							tb.options.implicitChange = false;
						};
				})(this,data), caller: this});
				
				setTimeout((function(self,button){
					return function(){
						var sv = self.getRow().subviews || [];
						var itemlen = self.getRow().subviews.length;
						$.each(sv, function(i,e){
							var del = e.getActionHandlers("delete");
							$.each(del, function(ii,ee){
								ee.unsubscribeAll(self);
								ee.subscribe({"event": "change", callback: function(v){
										if( ee.options.implicitChange !== true ){
											var rem = this.getRow().getMarkedItems("removal");
											var remlen = 0;
											for(var i in rem){
												if( rem.hasOwnProperty(i) ) {
													remlen += 1;
												}
											}
											if( remlen === 0 ){
												button.options.implicitChange = true;
												button.setChecked(false);
											}else if( remlen >= itemlen){
												button.options.implicitChange = true;
												button.setChecked(true);
											}
										}
										ee.options.implicitChange = false; 
								}, caller: self});
								
							});
						});
					};
				})(this,tb),1);
				
				tb.render(true);
				return elem;
			},
			cellCss: {"width":"17px","":"17px;"}, 
			cellClass: "cellaction delete"
		},{name: "archname", dataPath:"displayname", onRenderCell: function(elem, data){
				var src = "artifact_generic.png";
				if( $.inArray($.trim(data.displayname).toLowerCase(), ["32bit","64bit","nobit"]) > -1 ){
					src = data.displayname + ".png"; 
				}
				src = "/images/repository/" + src;
				var a = $(document.createElement("a")).attr("href","#").addClass("icontext");
				var img = $(document.createElement("img")).attr("src",src).attr("alt","");
				var span = $(document.createElement("span")).attr("src",src).attr("alt","");
				$(a).append(img).append(span);
				$(a).addClass("archlink").addClass("id_" + data.id);
				$(a).off("click").on("click",(function(self){
					return  function(ev){
						ev.preventDefault();
						self.getRow().toggleChildren();
						return false;
					};
				})(this));
				data.poapackage = data.poapackage || [];
				data.poapackage = $.isArray(data.poapackage)?data.poapackage:[data.poapackage];
				var text = "<span>" + data.name + "</span>";
				if( data.poapackage.length == "0" ){
					text += "<span class='archcount'>No files uploaded</span>";
				}else{
					text += "<span class='archcount'>("+data.poapackage.length+" files)</span>";
				}
				$(span).append(text);
				
				//Check if packages under architecture have any metapackage
				var root = this.getRow().options.data || {}, hasMetapackages = (root.cansupport == "metapackage")?false:true;
				if(root.poapackage.length > 0 && root.cansupport == "metapackage"){
					$.each(root.poapackage, function(i,e){
						if( e.level == "meta" ){
							hasMetapackages = true;
						}
					});
				}
				if( hasMetapackages == false ){
					var warning = $(document.createElement("div")).addClass("icontext").addClass("no-metapackage");
					var image = $(document.createElement("img")).attr("alt","").attr("src","/images/repository/warning.png");
					$(warning).append(image).append("<span>No metapackage defined</span>");
					$(a).append(warning);
				}
				return a;
		}},{
			name: "metapackage",
			onRenderCell: function(elem,data){
				var root = this.getRow().options.data || {};
				$(elem).empty();
				if(root.cansupport!=="metapackage"){
					return elem;
				}
				var allSetAsMetapackages = true;
				if(root.poapackage.length > 0){
					$.each(root.poapackage, function(i,e){
						if( e.level !== "meta" ){
							allSetAsMetapackages = false;
						}
					});
				}
				var container = $(document.createElement("div")).addClass("tooglebuttoncontainer"); 
				$(elem).append(container);
				var iseditable = !this.getRow().getRootItem().isReadOnly() && appdb.repository.ui.CurrentReleaseManager.canManageReleases();
				if(!iseditable) return elem;
				
				var tb = new appdb.repository.ui.views.ToggleButton({
					container: container,
					parent: this,
					checkText: "m",
					checkTooltip: "<span>All packages under this architecture are marked as metapackage.<br/>Click to unset them.</span>",
					checkStateName: "meta",
					text: "m",
					tooltip: "<span>Click to set all packages under this architecture as metapackages.",
					stateName: "dep",
					disabled: !iseditable
				});
				tb.subscribe({event: "change", callback: (function(self,origdata){
						return function(v){
							var sv = self.getRow().subviews || [];
							if( tb.options.implicitChange !== true ){
								tb.options.implicitChange = true;
								$.each(sv, function(i,e){
									var del = e.getActionHandlers("metapackage");
									$.each(del, function(ii,ee){
										ee.options.implicitChange = true;
										var metatodep = (ee.options.stateName === "meta" && ee.isChecked());
										var metatometa = (ee.options.stateName === "meta" && ee.isChecked()===false);
										var deptometa = (ee.options.stateName === "dep" && ee.isChecked());
										var deptodep = (ee.options.stateName === "dep" && ee.isChecked() === false);
										if( v == true ){
											if( deptometa || deptodep ){
												ee.setChecked( true );
											}else{
												ee.setChecked( false );
											}
										}else if( v == false ) {
											if( metatodep || metatometa){
												ee.setChecked( true );
											}else{
												ee.setChecked( false );
											}
											
										}
										ee.options.implicitChange = false;
									});
								});
							}
							tb.options.implicitChange = false; 
							if( v == true ){
								$(self.getRow().dom).find(".package").removeClass("action-mark-metapackage");
							}else{
								$(self.getRow().dom).find(".package").addClass("action-mark-dependency");
							}
						};
				})(this,data), caller: this});
				$(elem).off("mouseover").on("mouseover", (function(parent){
					return function(){
						$(parent.dom).addClass("hover");
					};
				})(this.getRow())).off("mouseleave").on("mouseleave", (function(parent){
					return function(){
						$(parent.dom).removeClass("hover");
					};
				})(this.getRow()));
				setTimeout((function(self,button){
					return function(){
						var sv = self.getRow().subviews || [];
						var itemlen = (self.getRow().subviews || []).length;
						$.each(sv, function(i,e){
							var del = e.getActionHandlers("metapackage");
							$.each(del, function(ii,ee){
								ee.unsubscribeAll(self);
								ee.subscribe({"event": "change", callback: function(v){
										if( ee.options.implicitChange !== true ){
											button.options.implicitChange = true;
											
											var met = this.getRow().getMarkedItems("metapackage");
											var metlen = 0;
											for(var i in met){
												if( met.hasOwnProperty(i) ) {
													metlen += 1;
												}
											}
											var dep = this.getRow().getMarkedItems("dependency");
											var deplen = 0;
											for(var i in dep){
												if( dep.hasOwnProperty(i) ) {
													deplen += 1;
												}
											}
											
											if( deplen > 0 ){
												button.setChecked(false);
											}else if( metlen < itemlen ){
												button.setChecked(false);
											}else if( metlen >= itemlen){
												button.setChecked(true);
											}
											button.options.implicitChange = false;
										}
										ee.options.implicitChange = false; 
								}, caller: self});
								
							});
						});
					};
				})(this,tb),1);
				
				tb.render();
				tb.options.implicitChange = true;
				tb.setChecked(allSetAsMetapackages);
				tb.options.implicitChange = false;
				return elem;
			}
		},{
			name: "tip",
			onRenderCell: function(elem,data){
				var root = this.getRow().options.data || {};
				$(elem).empty();
				var iseditable = !this.getRow().getRootItem().isReadOnly() && appdb.repository.ui.CurrentReleaseManager.canManageReleases();
				if(!iseditable) return elem;
				if(root.cansupport!=="metapackage"){
					return elem;
				}
				
				$(elem).append("<span class='tip'>Set / Unset</span>");
				return elem;
			}
		}
	];
	this.init();
});

appdb.repository.ui.views.files.PoaPackage = appdb.ExtendClass(appdb.repository.ui.views.GridTree, "appdb.repository.ui.views.files.PoaPackage" , function(o){
	this.options.name = "package";
	this.options.columns = [
		{
			name: "delete", 
			onRenderCell: function(elem,data){
				$(elem).empty();
				if( !appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
					return elem ; 
				}
				
				var container = $(document.createElement("div")).addClass("tooglebuttoncontainer"); 
				$(elem).append(container);
				var tb = new appdb.repository.ui.views.ToggleButton({
					container: container,
					parent: this,
					checkImage: "/images/cancelicon.png",
					checkTooltip: "This package is marked for removal upon save. Click to unmark.",
					checkStateName: "marked",
					image: "/images/cancelicon.png",
					tooltip: "Click to mark for removal. Changes will apply upon save.",
					stateName: "unmarked",
					disabled: !appdb.repository.ui.CurrentReleaseManager.canManageReleases()
				});
				tb.subscribe({event: "change", callback: (function(self,origdata){
						return function(v){
							if( tb.getStateName() === "unmarked" ){
								$(self.getRow().dom).find(".package").removeClass("action-mark-remove");
								self.getRow().removeMarkedItem("removal", origdata.id);
							}else{
								$(self.getRow().dom).find(".package").addClass("action-mark-remove");
								self.getRow().addMarkedItem("removal", origdata.id, origdata);
							}
						};
				})(this,data), caller: this});
				if( data.level == "dep" ){
					tb.render(false);
				}else{
					tb.render(true);
				}
				this.getRow().addAction("delete",tb);
				return elem;
			}, 
			cellCss: {"width":"17px","":"17px;"}, 
			cellClass: "cellaction delete"
		},{
			name:"filename", 
			dataPath: "filename", 
			onRenderCell: function(elem,data){
				return elem;
		}
		},{
			name: "filesize",
			dataPath: "size",
			onRenderCell: function(elem,data){
				$(elem).text(appdb.repository.utils.getFileSize(data.size));
				return elem;
			},
			cellClass: "filesize"
		},{
			name: "metapackage",
			dataPath: "level",
			onRenderCell: function(elem,data){
				var root = this.getRow().parent.options.data || {};
				$(elem).empty();
				if( root.cansupport !== "metapackage" ){
					return elem;
				}
				var container = $(document.createElement("div")).addClass("tooglebuttoncontainer"); 
				$(elem).append(container);
				var checkTooltip = "";
				var tooltip = "";
				var checkStateName ="", stateName = "";
				var iseditable = !this.getRow().getRootItem().isReadOnly() && appdb.repository.ui.CurrentReleaseManager.canManageReleases();
				if(data.level=="dep"){
					checkTooltip = "<span>This package is marked as metapackage.";
					checkTooltip += (iseditable)?"<br/>Click to unset it.</span>":"";
					tooltip = "<span>This package is not set as metapackage.";
					tooltip += (iseditable)?"<br/>Click to set it.</span>":"";
					checkStateName = "meta";
					stateName= "dep";
				}else{
					checkTooltip = "<span>This package is unmarked as metapackage.";
					checkTooltip += (iseditable)?"<br/>Click to set it.</span>":"";
					tooltip = "<span>This package is set as metapackage.";
					tooltip += (iseditable)?"<br/>Click to unset it.</span>":"";
						checkStateName = "dep";
						stateName = "meta";
				}
				
				var tb = new appdb.repository.ui.views.ToggleButton({
					container: container,
					parent: this,
					checkText: "m",
					checkTooltip: checkTooltip,
					checkStateName: checkStateName,
					text: "m",
					tooltip: tooltip,
					stateName: stateName,
					disabled: !iseditable
				});
				tb.subscribe({event: "change", callback: (function(self,origdata){
						return function(v){
							if( origdata.level == tb.getStateName() ){
								$(self.getRow().dom).find(".package").removeClass("action-mark-" + ((origdata.level=="dep")?"metapackage":"dependency") );
								self.getRow().removeMarkedItem( ((origdata.level=="dep")?"metapackage":"dependency"), origdata.id, origdata);
							}else{
								$(self.getRow().dom).find(".package").addClass("action-mark-" + ((origdata.level=="dep")?"metapackage":"dependency") );
								self.getRow().addMarkedItem( ((origdata.level=="dep")?"metapackage":"dependency"), origdata.id, origdata);
							}
						};
				})(this,data), caller: this});
				if( stateName == data.level ){
					tb.render(false);
				}else{
					tb.render(true);
				}
				this.getRow().addAction("metapackage",tb);
				return elem;
			}
		},{
				name: "download",
				onRenderCell: function(elem,data){
					var root = this.getRow().parent.options.data || {};
					var href = appdb.config.repository.endpoint.base + "storage/download?id=" + data.id;
					var a = $(document.createElement("a")).addClass("icontext").append("<span>download</span>").attr("title","Download package").attr("target","_blank");
					if( root.repositoryinfo && $.trim(root.repositoryinfo.repositoryurl) !== "" ){
						href = root.repositoryinfo.repositoryurl + data.filename;
					}
					href = href.replace(/^https\:/i,"http:");
					$(a).attr("href",href).off("click").on("click", function(ev){
						if( ev.stopPropagation ) ev.stopPropagation();
						if( window.event ) window.event.cancelBubble = true;
					});
					$(elem).empty().append(a);
					return elem;
				},
				cellClass: "cellaction download cellbutton"
		},{
			name: "details",
			onRenderCell: function(elem,data){
				//var details = new appdb.repository.ui.views.DropDownPackageDetailsPanel({container: this.getRow().dom, handler: $(this.getRow().dom).find("ul.gridtreerow"), data: data, parent: this.getRow()});
				var details_params = {container: this.getRow().dom, handler: $(this.getRow().dom).find("ul.gridtreerow"), data: data, parent: this.getRow()};
				var a = $(document.createElement("a")).addClass("icontext").attr("title","Toggle package details");
				$(a).append("<span class='notexpanded' ><span>details</span><span>▼</span></span>").append("<span class='expanded'><span>details</span><img src='/images/closeview.png' alt='' /></span>");
				$(details_params.handler).on("click", (function(params, data){
					return function(ev){
						ev.preventDefault();

						if( typeof $(this).data('loaded') === 'undefined' ){
							var details = new appdb.repository.ui.views.DropDownPackageDetailsPanel(params);
							details.render(data);
							$(this).data('loaded', true);
							setTimeout((function(elem){ return function(){$(elem).trigger('click');};})(this),10);
						}

						return true;
					};
				})(details_params, data));
				$(elem).empty().append(a);

				return elem;
			},
			cellClass: "cellaction details cellbutton"
		}
	];
	this.init();
});
appdb.repository.ui.views.CompactTargetDropDownList = appdb.ExtendClass(appdb.View,"appdb.repository.ui.views.CompactTargetDropDownList", function(o){
	this.options = {
		parent: o.parent || null,
		data: o.data || null,
		compact: $(document.createElement("div")).addClass("compact"),
		context: $(document.createElement("div")).addClass("context")
	};
	this.expand = function(){
		$(".compactdropdownlist.expand").find(".compact .action > div > span").text("▼");
		$(".compactdropdownlist.expand").removeClass("expand").find(".context").slideUp(10);
		$(this.dom).addClass("expand");
		$(this.options.compact).find(".action > div > span").text("▲");
		$(this.options.context).slideDown(100);
		
	};
	this.collapse = function(){
		$(this.dom).removeClass("expand");
		$(this.options.compact).find(".action > div > span").text("▼");
		$(this.options.context).slideUp(100);
	};
	this.toggle = function(){
		if( $(this.dom).hasClass("expand") ){
			this.collapse();
		} else {
			this.expand();
		}
	};
	this.getOsImage = function(name){
		return appdb.repository.utils.getOsImagePath(name);
	};
	this.getArchImage = function(name){
		var src = "";
		if( $.inArray($.trim(name).toLowerCase(), ["32bit","64bit"]) > -1 ){
			src = name + ".png"; 
			src = "/images/repository/" + src;
		}
		return src;
	};
	this.getArtifactImage = function(name){
		var src = "artifact_generic.png";
		if( $.inArray(name,["rpm", "deb", "tgz", "tar", "gz" ] > -1 )){
			src = "artifact_" + name + ".png";
		}
		src = "/images/repository/" + src;
		return src;
	};
	this.renderContextItem = function(d){
		var self = this;
		var div = $(document.createElement("div"));
		var osimg = $(document.createElement("img")).addClass("osimg");
		var os = $(document.createElement("div")).addClass("os");
		var archimg = $(document.createElement("img")).addClass("archimg");
		var arch = $(document.createElement("div")).addClass("arch");
		var artifacts = $(document.createElement("ul")).addClass("artifacts");
		
		
		$(osimg).attr("src",this.getOsImage(d.os.name)).attr("title", d.os.displayname);
		$(os).html("<span class='osname'>" + d.os.displayname + "</span><span class='flavor'>" + d.os.displayflavor + "</span>");
		
		$(div).append(osimg).append(os);
		
		var archimgsrc = this.getArchImage(d.arch.displayname);
		if( archimgsrc ){
			$(archimg).attr("src", archimgsrc).attr("title", d.arch.displayname);
			$(div).append(archimg);
		}
		$(arch).html("<span class='title'>architecture: </span><span class='archname'>"+ d.arch.name + "</span>");
		$(div).append(arch).append(artifacts);
		
		var a = d.os.artifact || [];
		a = $.isArray(a)?a:[a];
		$.each(a, function(i,e){
			var artifact = $(document.createElement("li"));
			var artdiv = $(document.createElement("div")).addClass("icontext");
			var artifactimg = $(document.createElement("img")).addClass("artifactimg");
			var artifactname = $(document.createElement("span")).addClass("artifact");
			
			$(artifactimg).attr("src", self.getArtifactImage(e.type) ).attr("title", e.type);
			$(artifactname).text(e.type);
			$(artdiv).append(artifactimg).append(artifactname);
			$(artifact).append(artdiv);
			$(artifacts).append(artifact);
		});
		
		
		return div;
		
	};
	this.renderContext = function(d){
		this.sortByAcronym(d);
		$(this.options.context).empty().hide();
		var ul = $(document.createElement("ul"));
		var self = this;
		$.each(d, function(i, e){
			var li = $(document.createElement("li"));
			$(li).append( self.renderContextItem(e) );
			$(ul).append(li);
		});
		$(this.options.context).empty().append(ul);
	};
	this.uniqueOsAcronyms = function(d){
		var uniq = {};
		var res = [];
		$.each(d, function(i,e){
			if( !uniq[e.os.acronym] ){
				uniq[e.os.acronym] = e;
			}
		});
		for(var i in uniq){
			if( uniq.hasOwnProperty(i)){
				res.push(uniq[i]);
			}
		}
		return res;
	};
	this.sortByAcronym = function(d){
		d.sort(function(a,b){
			if( b.os.acronym === "tar" ) return 100;
			if( a.os.acronym < b.os.acronym ) return 1;
			if( a.os.acronym > b.os.acronym ) return -1;
			return 0;
		});
	};
	this.renderCompact = function(data){
		var d = this.uniqueOsAcronyms(data);
		this.sortByAcronym(d);
		$(this.options.compact).empty();
		var self = this;
		var list = $(document.createElement("ul"));
		var listcontainer = $(document.createElement("div")).addClass("list");
		var action = $(document.createElement("a")).addClass("action");
		
		var src = "os_generic.png";
		var arch = "";
		
		$.each(d, function(i, e){
			var src = self.getOsImage(e.os.name);
			$(list).append("<li><div class='icontext acronym'><img src='"+src+"' alt=''/>"+arch+"<span>"+e.os.acronym + "</span></div></li>");	
		});
		
		$(action).append("<div><span>▼</span></div>");
		
		$(listcontainer).append(list);
		$(this.options.compact).append(listcontainer).append(action);
		
		$(this.options.compact).off("mouseover").on("mouseover", function(ev){
			$(this).addClass("hover");
		}).off("mouseout").on("mouseout", function(ev){
			$(this).removeClass("hover");
		});
		
		$(this.options.compact).append("<div class='sheet'></div>");
		$(this.options.compact).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.toggle();
				return false;
			};
		})(this));
		appdb.repository.onViewBlur(this, function(){
			this.collapse();
		});
	};
	this.render = function(d){
		d = d || {};
		d = $.isArray(d)?d:[d];
		
		this.renderCompact(d);
		this.renderContext(d);
		$(this.dom).addClass("compactdropdownlist").empty();
		$(this.dom).append(this.options.compact).append(this.options.context);
	};
	this.init = function(){
		this.dom = o.container || null;
	};
	this.init();
});

appdb.repository.ui.views.RepositoryAreaReleaseList = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.RepositoryAreaReleaseList", function(o){
	this.options = {
		parent: o.parent || null,
		data: o.data || []
	};
	this.reset = function(){
		$(this.dom).find(".actions").empty();
		$(this.dom).find(".emptymessage").remove();
	};
	this.render = function(d){
		this.reset();
		this.options.data = d || this.options.data;
		d = this.options.data;
		this.subviews.gridlist.render(d);
		
		var totop =  $("<a class='action totop'><img src='/images/up_gray.png' alt=''/><span>top</span></a>");
		$(totop).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				window.scrollTo(0, 0);
				return false;
			};
		})(this));
		$(this.dom).find(".actions").append(totop);
	};
	this.init = function(){
		this.dom = $(o.container);
		var _canDisplayLoggedinColumn = function(d){
			if( !appdb.repository.ui.CurrentReleaseManager.canManageReleases() ) return false;
			return true;
		};
		var _formatDateTime = function(d){
			return $.trim(d).replace(/\ [0-9]{2}\:[0-9]{2}\:[0-9]{2}$/g,"");	
		};
		this.subviews.gridlist = new appdb.repository.ui.views.GridList({container: $(o.container).find(".reporeleaselist"),
			columns:[
				{name: "displayversion", displayName: "Version",dataPath: "displayversion", type:"text", css: "width:100px;", onRender: function(elem,data){
						var a = $(document.createElement("a")).addClass("releaselink").attr("href","#").attr("title","view relases details").html("<pre>"+data.displayversion+"</pre>");
						$(a).off("click").on("click", (function(d){
							return function(ev){
								ev.preventDefault();
								appdb.repository.ui.CurrentReleaseManager.showRelease(d.id,d.displayversion);
								return false;
							};
						})(data));
						$(elem).empty().append(a);
						if( data.parentid == "0" ){
							$(elem).append("<span>(base)</span>");
						}
						return $(elem);
				}},
				{name: "state", displayName: "State", dataPath: "state.name", type:"text" , empty:"-",css: "width:60px", onRender: function(elem,data){
						$(elem).addClass("releasestatevalue").addClass(data.state.name.toLowerCase());
						return $(elem);
				}},
				{name: "targets", displayName: "Supports", dataPath: "target", type:"appdb.repository.ui.views.CompactTargetDropDownList", empty:"No files uploaded yet",classes: "inline"},
				{name: "priority", displayName: "Priority", dataPath: "priority", type:"text", empty:"-", css:"width:60px"},
				{name: "created", displayName: "Created", dataPath: "created", type:"text", css: "width:140px",  canDisplay: _canDisplayLoggedinColumn},
				{name: "releasedate", displayName: "Release date", dataPath: "releasedate", type:"text", css:"min-width:100px;", empty:"not released yet", format: _formatDateTime}
			], 
			canDisplayRowData: function(d){
				if( $.inArray($.trim(d.state.name).toLowerCase(), ["production","candidate"]) === -1 && !appdb.repository.ui.CurrentReleaseManager.canManageReleases() ) return false;
				return true;
			},
			css: "",
			defaults: {emptyItemText: "-", emptyDataText: "No product releases available yet"},
			parent: this});
	};
	this.init();
});

appdb.repository.ui.views.RepositoryAreaOverview = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.RepositoryAreaOverview", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || null,
		candidatecount: 0
	};
	this.renderEmptyValues = function(){
		$(this.dom).children("div.emptymessage").remove();
		$(this.dom).find(".value.empty").after("<div class='emptymessage'>" + appdb.repository.ui.views.ReleaseDocumentation.emptymessage + "</div>");
	};
	this.renderProperties = function(d){
		$(this.dom).find(".property:not(.noempty)").each((function(self){
			return function(index,elem){
				self.subviews[$(elem).data("path")] = new appdb.repository.ui.views.RepositoryAreaProperty({
					container: $(elem)[0], 
					parent:self, 
					data: d});
			};
		})(this));
		for(var s in this.subviews){
			this.subviews[s].render();
		}
		var self = this;
			
		$(this.dom).find(".property.noedit").each(function(i,e){
			$(this).find(".actions .action.edit").remove();
			$(this).find(".actions .action.totop").remove();
			var totop =  $("<a class='action totop'><img src='/images/up_gray.png' alt=''/><span>top</span></a>");
			$(totop).off("click").on("click", function(ev){
				ev.preventDefault();
				window.scrollTo(0, 0);
				return false;
			});
			$(this).find(".actions").append(totop);
		});
	};
	this.setBuildState = function(time){
		if( this.poareleases ){
			this.poareleases.setBuildingProcess(time);
		}
		if( this.files ){
			this.files.setBuildState(time);
		}
	};
	this.renderCandidateDescription = function(d){
		$(this.dom).find(".description .repoareaname").text(d.name);
		if( this.options.candidatecount > 0 ){
			$(this.dom).find(".candidateexist").removeClass("hidden");
		}else{
			$(this.dom).find(".candidateexist").addClass("hidden");
		}
	};
	this.renderRepositories = function(d,ignorestates){
		var uniq = this.getUniqueRepositories($.extend(true,{},d), ignorestates);
		this.options.data = this.getUniqueRepositories(d, ignorestates, true);
		this.poareleases.render(uniq);
	};
	this.initProductionBuildHandler = function(d){
		appdb.repository.utils.ProductionBuildHandler.reset();
		if( d.lastproductionbuild  ){ 
			var handler = new appdb.repository.utils.ProductionBuildHandler({id: d.id, name: d.name, lastproductionbuild: d.lastproductionbuild});
			handler.unsubscribeAll();
			handler.subscribe({event: "tick", callback: function(v){
				$(this.dom).addClass("buildingrepos");
				this.setBuildState(v);
			}, caller: this}).subscribe({event: "timeout", callback: function(){
				this.setBuildState(false);
				$(this.dom).removeClass("buildingrepos");
			}, caller: this});
			handler.render();
		}else{
			$(this.dom).removeClass("buildingrepos");
			this.setBuildState(false);
		}
	};
	this.render = function(d){
		$(this.dom).find(".valueeditor").remove();
		this.setBuildState(false);
		this.options.candidatecount = 0;
		var ignorestates =["unverified","candidate"];
		d.knownissues = this.collectPropertyValues(d,"knownissues", ignorestates);
		this.renderEmptyValues(d);
		this.renderProperties(d);
		this.releaselist.render(d.productrelease);
		$(this.releaselist.dom).addClass( $(this.releaselist.dom).find(".header:first").text().toLowerCase().replace(/\ /g,"") );
		this.renderRepositories(d,ignorestates);
		this.files.render(this.options.data);
		this.renderCandidateDescription(d);
		this.toc.render();
		this.initProductionBuildHandler(d);
	};
	this.editPropertyValue = function(propName){
		var prop = $(this.dom).find("." + propName);
		if( $(prop).length === 0 ) return;
		
	};
	this.collectPropertyValues = function(d, property, ignorestates){
		ignorestates = ignorestates || [];
		ignorestates = $.isArray(ignorestates)?ignorestates:[ignorestates];
		
		d = d || {};
		d.productrelease = d.productrelease || [];
		d.productrelease = $.isArray(d.productrelease)?d.productrelease:[d.productrelease];
		
		var values = [];
		var res = "";
		$.each(d.productrelease, function(i,e){
			if( $.inArray($.trim(e.state.name).toLowerCase(), ignorestates) === -1 && $.trim(e[property]).replace(/\ /g,"")){
				values.push({id: e.id, name: e.displayversion, value: e[property]})
			}
		});
		if( values.length > 0 ){
			res = "<ul class='propertysummary'>";
			$.each(values,function(i,e){
				res += "<li>";
				res += "<div class='title'><span>Release: </span><pre>" + e.name + "</pre></div>";
				res += "<div class='value'><div>"+e.value.replace(/$\<\!\[CDATA\[/g,"").replace(/\]\]\>/g,"")+"</div></div>";
				res += "</li>";
			});
			res += "</ul>";
		}
		return res;
	};
	this.getTarget = function(id,productrelease){
		productrelease.target = productrelease.target || [];
		productrelease.target = $.isArray(productrelease.target)?productrelease.target:[productrelease.target];
		var res = null;
		$.each(productrelease.target, function(i,e){ 
			if(res===null){
				if( e.id == id ){
					res = e;
				}
			}
		});
		return res;
	};
	this.getUniquePackages = function(packs,allfiles){
		packs = packs || [];
		packs = $.isArray(packs)?packs:[packs];
		allfiles = (typeof allfiles==="boolean")?allfiles:false;
		var uniqs= {};
		var res = [];
		$.each(packs, function(i,e){
			var uname = e.name;
			if( allfiles ){
				uname = e.filename;
			}
			if( uniqs[uname] ){
				if( parseInt(e.versionindex) > parseInt(uniqs[uname].versionindex) ){
					uniqs[uname] = e;
				}
			}else{
				uniqs[uname] = e;
			}
		});
		
		for(var i in uniqs){
			if( !uniqs.hasOwnProperty(i) ) continue;
			res.push(uniqs[i]);
		}
		res.sort(function(a,b){
			var aname = $.trim(a.name);
			var bname = $.trim(b.name);
			if( aname < bname ) return -1;
			if( aname > bname ) return 1;

			var aid = $.trim(a.id) << 0;
			var bid = $.trim(b.id) << 0;
			if( aid < bid ) return 1;
			if( aid > bid ) return -1;
			

			aname = $.trim(a.filename);
			bname = $.trim(b.filename);
			if( aname < bname ) return -1;
			if( aname > bname ) return 1;

			return 0;
		});
		return res;
	};
	this.getUniqueRepositories = function(d,ignorestates,allfiles){
		var uniqreleases = {};
		var uniqtargets = {};
		var self = this;
		var poas = [];
		ignorestates = ignorestates || [];
		ignorestates = $.isArray(ignorestates)?ignorestates:[ignorestates];
		allfiles = (typeof allfiles==="boolean")?allfiles:false;
		d = d || {};
		d.productrelease = d.productrelease || [];
		d.productrelease = $.isArray(d.productrelease)?d.productrelease:[d.productrelease];
		
		$.each(d.productrelease, function(i,e){
			if(e.state && $.trim(e.state.name).toLowerCase() === "candidate" ){
				self.options.candidatecount += 1;
			}
			if( $.inArray($.trim(e.state.name).toLowerCase(), ignorestates) === -1){
				if( !uniqreleases[e.id] ){
					uniqreleases[e.id] = e;
				}
			}
		});
		
		for(var i in uniqreleases){
			if( !uniqreleases.hasOwnProperty(i)) continue;
			if(uniqreleases[i].poarelease){
				uniqreleases[i].poarelease = uniqreleases[i].poarelease || [];
				uniqreleases[i].poarelease = $.isArray(uniqreleases[i].poarelease)?uniqreleases[i].poarelease:[uniqreleases[i].poarelease];
				$.each(uniqreleases[i].poarelease, function(kk,e){
					e.state = $.extend({},uniqreleases[i].state);
					if( !uniqtargets[e.target.id] ){
						e.target = self.getTarget(e.target.id,uniqreleases[i]);
						uniqtargets[e.target.id] = e;
					}else{
						var ee = uniqtargets[e.target.id];
						ee.poapackage = ee.poapackage || [];
						ee.poapackage = $.isArray(ee.poapackage)?ee.poapackage:[ee.poapackage];
						e.poapackage = e.poapackage || [];
						e.poapackage = $.isArray(e.poapackage)?e.poapackage:[e.poapackage];
						ee.poapackage = ee.poapackage.concat(e.poapackage);
						uniqtargets[e.target.id] = ee;
					}
					if(!uniqtargets[e.target.id].state || $.trim(uniqtargets[e.target.id].state.name).toLowerCase() === "unverified"){
						uniqtargets[e.target.id].state = uniqreleases[i].state;
					}
				});
			}
		}
		
		for(var j in uniqtargets){
			if( !uniqtargets.hasOwnProperty(j) ) continue;
			uniqtargets[j].poapackage = self.getUniquePackages(uniqtargets[j].poapackage,allfiles);
			
			poas.push(uniqtargets[j]);
		}
		
		return {poarelease : poas};
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent;
		this.releaselist = new appdb.repository.ui.views.RepositoryAreaReleaseList({container: $(this.dom).find(".property.releaselist")});
		this.toc = new appdb.repository.ui.views.PropertyTOC({container: $(this.dom).find(".toccontainer"), parent: this});
		this.poareleases = new appdb.repository.ui.views.PoaRepositoryList({container: $(this.dom).find(".contents .repositoriescontainer"), parent: this, group:"series"});
		this.files = new appdb.repository.ui.views.RepositoryAreaFiles({container: $(this.dom).find(".contents .files"), parent: this});
	};
	this.init();
},{
	emptymessage: "No information provided yet"
});

appdb.repository.utils.ProductionBuildHandler = appdb.ExtendClass(appdb.View,"appdb.repository.utils.ProductionBuildHandler", function(o){
	this.options = {
		id: o.id,
		group: o.group || "series",
		name: o.name,
		buildtime: (o.lastproductionbuild && o.lastproductionbuild.productiontime)?o.lastproductionbuild.productiontime:undefined,
		servertime: (o.lastproductionbuild && o.lastproductionbuild.backendtime)?o.lastproductionbuild.backendtime:undefined,
		timediff: 0,
		interval: 10,
		disable: false,
		tickinterval: 1000,
		timer: null
	};
	this.checkExistingHandler = function(id){
		var found = false;
		if( o.id ){
			$.each(appdb.repository.utils.ProductionBuildHandler.registry, function(i,e){
				if( found === false && o.id == e.options.id ){
					found = i;
				}
			});
		}
		if( found !== false ){
			return appdb.repository.utils.ProductionBuildHandler.registry[i];
		}
		return null;
	};
	this.getTimeoutInterval = function(){
		var configs = appdb.repository.model.Config.getLocalData();
		var config = configs.config || [];
		config = $.isArray(config)?config:[config];
		var found = false;
		$.each(config, function(i,e){
			if( found === false && e.name === "repo.rsync.timeout"){
				found = e.value;
			}
		});
		return found;
	};
	this.getUTCDateTime = function(){
		var now = this.getBuildDate(this.options.servertime);
		return now;
	};
	this.diffBuildTime = function(){
		var now = this.getUTCDateTime();
		var diff = ((now.getTime() - this.getBuildDate().getTime())/1000);
		if( diff < 0 || diff > parseInt(this.options.interval) ){
			return 0;
		}
		return diff;
	};
	this.getBuildDate = function(datetime){
		var t = (datetime || this.options.buildtime).split(/[- :]/);
		var d = new Date(t[0], t[1]-1, t[2], t[3], t[4], t[5]);
		return d;
	};
	this.getRemainingTime = function(diff){
		diff = diff || 0;
		diff = parseInt(diff);
		var hours   = Math.floor(diff / 3600);
		var minutes = Math.floor((diff - (hours * 3600)) / 60);
		var seconds = diff - (hours * 3600) - (minutes * 60);

		if (hours   < 10) {hours   = "0"+hours;}
		if (minutes < 10) {minutes = "0"+minutes;}
		if (seconds < 10) {seconds = "0"+seconds;}
		var res = "";
		if( minutes > 0 ){
			res += minutes+' minutes ';
		}
		res += seconds + " seconds left";
		return res;
	};
	this.tick = function(diff){
		var remains = this.options.interval - diff;
		if( remains <= 0 ){
			this.options.interval = 0;
		}else{
			this.options.interval -= 1;
		}
		this.publish({event: "tick", value: this.getRemainingTime(remains)});
	};
	this.stop = function(){
		this.options.disable = true;
		this.publish({event: "timeout", value: true});
		this.destroy();
	};
	this.initTimeout = function(timeinterval){
		this.options.timer = setTimeout((function(self){
			return function(){
				self.options.tickinterval = 1000;
				var diff = self.diffBuildTime();
				if(diff && diff > 0 && self.options.disable == false){
					self.initTimeout();
					self.tick(diff);
				}else{
					self.stop();
				}
			};
		})(this),timeinterval || this.options.tickinterval);
	};
	this.render = function(){
		if( typeof this.options.buildtime === "undefined" || this.diffBuildTime() == 0 ){
			this.destroy();
			return;
		}
		this.options.disable = false;
		this.initTimeout(1);
	};
	this.destroy = function(){
		this.reset();
		if( appdb.repository.utils.ProductionBuildHandler.registry ){
			appdb.repository.utils.ProductionBuildHandler.registry.reset();
		}
	};
	this.reset = function(){
		if( this.options.timer ) {
			clearTimeout(this.options.timer);
		}
		this.options.disable = true;
		this.unsubscribeAll();
	};
	this.init = function(){
		this.destroy();
		this.options.disable = false;
		this.options.interval = this.getTimeoutInterval() || this.options.interval;
		this.options.interval = parseInt(this.options.interval);
		appdb.repository.utils.ProductionBuildHandler.registry = this;
	};
	this.init();
},{
	registry: null,
	reset: function(){
		if(appdb.repository.utils.ProductionBuildHandler.registry!== null){
			appdb.repository.utils.ProductionBuildHandler.registry.stop();
		}
		appdb.repository.utils.ProductionBuildHandler.registry = null;
	}
});
appdb.repository.ui.views.RepositoryAreaRepositories = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleaseRepositories", function(o){
	this.options = {
		dom: $(o.container),
		parent: o.parent || null,
		candidatecount: 0
	};
	this.renderCandidateDescription = function(d){
		$(this.dom).find(".description .repoareaname").text(d.name);
		if( this.options.candidatecount > 0 ){
			$(this.dom).find(".candidateexist").removeClass("hidden");
		}else{
			$(this.dom).find(".candidateexist").addClass("hidden");
		}
	};
	this.renderEmptyValues = function(d){
		$(this.dom).children("div").remove(".emptymessage");
		$(this.dom).find(".value.empty").after("<div class='emptymessage'>" + appdb.repository.ui.views.ReleaseDocumentation.emptymessage + "</div>");
	};
	this.renderProperties = function(d){
		$(this.dom).find(".property").each((function(self){
			return function(index,elem){
				self.subviews[$(elem).data("path")] = new appdb.repository.ui.views.RepositoryAreaProperty({
					container: $(elem)[0], 
					parent:self, 
					data: d});
			};
		})(this));
		for(var s in this.subviews){
			this.subviews[s].render();
		}
	};
	this.setBuildState = function(time){
		if( this.poareleases ){
			this.poareleases.setBuildingProcess(time);
		}
	};
	this.renderRepositories = function(d,ignorestates){
		var uniq = this.getUniqueRepositories(d, ignorestates);
		this.options.data = uniq;
		this.poareleases.render(uniq);
	};
	this.render = function(d){
		this.options.candidatecount = 0;
		$(this.dom).find(".valueeditor").remove();
		var ignorestates = ["unverified","candidate"];
		d.knownissues = this.collectPropertyValues(d,"knownissues", ignorestates) ;
		this.renderEmptyValues(d);
		this.renderProperties(d);
		this.renderRepositories(d, ignorestates);
		this.renderCandidateDescription(d);
	};
	this.getTarget = function(id,productrelease){
		productrelease.target = productrelease.target || [];
		productrelease.target = $.isArray(productrelease.target)?productrelease.target:[productrelease.target];
		var res = null;
		$.each(productrelease.target, function(i,e){ 
			if(res===null){
				if( e.id == id ){
					res = e;
				}
			}
		});
		return res;
	};
	this.getUniquePackages = function(packs){
		packs = packs || [];
		packs = $.isArray(packs)?packs:[packs];
		
		var uniqs= {};
		var res = [];
		$.each(packs, function(i,e){
			if( uniqs[e.name] ){
				if( parseInt(e.versionindex) > parseInt(uniqs[e.name].versionindex) ){
					uniqs[e.name] = e;
				}
			}else{
				uniqs[e.name] = e;
			}
		});
		
		for(var i in uniqs){
			if( !uniqs.hasOwnProperty(i) ) continue;
			res.push(uniqs[i]);
		}
		return res;
	};
	this.getUniqueRepositories = function(d,ignorestates){
		var uniqreleases = {};
		var uniqtargets = {};
		var self = this;
		var poas = [];
		ignorestates = ignorestates || [];
		ignorestates = $.isArray(ignorestates)?ignorestates:[ignorestates];
		
		d = d || {};
		d.productrelease = d.productrelease || [];
		d.productrelease = $.isArray(d.productrelease)?d.productrelease:[d.productrelease];
		
		$.each(d.productrelease, function(i,e){
			if(e.state && $.trim(e.state.name).toLowerCase() === "candidate" ){
				self.options.candidatecount += 1;
			}
			if( $.inArray($.trim(e.state.name).toLowerCase(), ignorestates) === -1){
				if( !uniqreleases[e.id] ){
					uniqreleases[e.id] = e;
				}
			}
		});
		
		for(var i in uniqreleases){
			if( !uniqreleases.hasOwnProperty(i)) continue;
			if(uniqreleases[i].poarelease){
				uniqreleases[i].poarelease = uniqreleases[i].poarelease || [];
				uniqreleases[i].poarelease = $.isArray(uniqreleases[i].poarelease)?uniqreleases[i].poarelease:[uniqreleases[i].poarelease];
				$.each(uniqreleases[i].poarelease, function(kk,e){
					e.state = $.extend({},uniqreleases[i].state);
					if( !uniqtargets[e.target.id] ){
						e.target = self.getTarget(e.target.id,uniqreleases[i]);
						uniqtargets[e.target.id] = e;
					}else{
						var ee = uniqtargets[e.target.id];
						ee.poapackage = ee.poapackage || [];
						ee.poapackage = $.isArray(ee.poapackage)?ee.poapackage:[ee.poapackage];
						e.poapackage = e.poapackage || [];
						e.poapackage = $.isArray(e.poapackage)?e.poapackage:[e.poapackage];
						ee.poapackage = ee.poapackage.concat(e.poapackage);
						uniqtargets[e.target.id] = ee;
					}
					if(!uniqtargets[e.target.id].state || $.trim(uniqtargets[e.target.id].state.name).toLowerCase() === "unverified"){
						uniqtargets[e.target.id].state = uniqreleases[i].state;
					}
				});
			}
		}
		
		for(var j in uniqtargets){
			if( !uniqtargets.hasOwnProperty(j) ) continue;
			uniqtargets[j].poapackage = self.getUniquePackages(uniqtargets[j].poapackage);
			
			poas.push(uniqtargets[j]);
		}
		
		return {poarelease : poas};
	};
	this.collectPropertyValues = function(d, property, ignorestates){
		ignorestates = ignorestates || [];
		ignorestates = $.isArray(ignorestates)?ignorestates:[ignorestates];
		
		d = d || {};
		d.productrelease = d.productrelease || [];
		d.productrelease = $.isArray(d.productrelease)?d.productrelease:[d.productrelease];
		
		var values = [];
		var res = "";
		$.each(d.productrelease, function(i,e){
			if( $.inArray($.trim(e.state.name).toLowerCase(), ignorestates) === -1 && $.trim(e[property]).replace(/\ /g,"")){
				values.push({id: e.id, name: e.displayversion, value: e[property]})
			}
		});
		if( values.length > 0 ){
			res = "<ul class='propertysummary'>";
			$.each(values,function(i,e){
				res += "<li>";
				res += "<div class='title'><span>Release: </span><pre>" + e.name + "</pre></div>";
				res += "<div class='value'><div>"+e.value.replace(/$\<\!\[CDATA\[/g,"").replace(/\]\]\>/g,"")+"</div></div>";
				res += "</li>"
			});
			res += "</ul>";
		}
		return res;
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent || null;
		this.poareleases = new appdb.repository.ui.views.PoaRepositoryList({container: $(this.dom).find(".contents .repositoriescontainer"), parent: this, group:"series"});
	};
	this.init();
});
appdb.repository.ui.views.RepositoryAreaFiles = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.RepositoryAreaFiles", function(o){
    this.hasCandidates = function(){
      var d = this.parent.options.data || [];
      d = $.isArray(d)?d:[d];
      var found=false;
      for(var i=0; i<d.length; i+=1){
          var rels = d[i].productrelease;
          rels = rels || [];
          rels = $.isArray(rels)?rels:[rels];
          $.each(rels, function(i,e){
             if(found === false && $.trim(e.state.name).toLowerCase() === "candidate"){
                 found = true;
             }
          });
          if(found){
              return true;
          }
      }
      return false;
    };
	this.setBuildState = function(time){
		if( time == false ){
			$(this.dom).children(".toolbar").removeClass("hidden");
			$(this.dom).find(".building").remove();
		}else{
			$(this.dom).children(".toolbar").addClass("hidden");
			if( $(this.dom).children(".building").length > 0 ){
				$(this.dom).children(".building").find(".timeout").html("[" + time + "]");
			}else{
				var div = $(document.createElement("div")).addClass("icontext").addClass("building");
				var img = $(document.createElement("img")).attr("alt","").attr("src","/images/ajax-loader-trans-orange.gif");
				var span = $(document.createElement("span")).append("<span>...building repositories.</span><span class='timeout'>[" + time + "]</span>");
				var divtip = $(document.createElement("div")).addClass("icontext").addClass("tip");
				$(divtip).html("You won't be able to download files until the repositories are built.");
				$(div).append(img).append(span).append(divtip);
				$(this.dom).prepend(div);
			}
		}
	};
    this.render = function(d){
		var collectpr = [];
		var collect = d || [];
		if( this.hasCandidates() ){
			$(this.dom).find(".candidateexist").removeClass("hidden");
		}else{
			$(this.dom).find(".candidateexist").addClass("hidden");
		}
        this.subviews.files.render(collect);
        $(this.dom).find(".description .repoareaname").text(this.parent.options.data.name);
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent;
		this.subviews.files = new appdb.repository.ui.views.Files({
			container: $(this.dom).find(".filesviewcontainer"),
			parent: this,
			readonly: true
		});
	};
	this.init();
},{
	emptymessage: "No information provided yet"
});
appdb.repository.ui.views.MainContent = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.MainContent", function(o){
	this.setupReleaseRoute = function(section){
		section = (section==="details")?"":section;
		var release = appdb.repository.ui.CurrentReleaseManager.getCurrentRelease();
		var isLoaded = false;
		var r = appdb.routing.FindRoute();
		if( !r || !r.parameters ){
			return;
		}
		if( release ){
			release = release.productrelease || release;
			series = {id: release.repositoryarea.id, name: release.repositoryarea.name};
			isLoaded = (release.displayversion == r.parameters.release && series.name == r.parameters.series);
			r.parameters.releasesection = (!r.parameters.releasesection || r.parameters.releasesection==="details")?"":r.parameters.releasesection;
			if( isLoaded && r.parameters.releasesection !== section ) {
				isLoaded = false;
			}
			if( !isLoaded ){
				appdb.pages.application.updateReleaseSection(series.name, release.displayversion, section, true);
			}
		}
	};
	this.setupSeriesRoute = function(){
		var series = appdb.repository.ui.CurrentReleaseManager.getCurrentSeries();
		var isLoaded = false;
		var r = appdb.routing.FindRoute();
		if( !r || !r.parameters ){
			return;
		}
		if( series ){
			series = series.repositoryarea || series;
			isLoaded = (series.name == r.parameters.series && !r.parameters.release)?true:false;
			if( !isLoaded ){
				appdb.pages.application.updateReleaseSection(series.name,"", undefined, true);
			}
		}
	};
	this.getSelectedSection = function(){
		var sect = $(this.dom).find(".header .navigation ul > li:not(.popup) > a.selected");
		if( $(sect).length === 0 ){
			return "";
		}
		
		return $(sect).data("section") || "";
	};
	this.initNavigation = function(d){
		var self = this;
		if( $(this.dom).find(".header .navigation ul > li:not(.popup) > a").length> 0 ){
			$(this.dom).find(".header .navigation ul > li:not(.popup) > a").off("click.navigationbar").on("click.navigationbar",function(ev){
				ev.preventDefault();
				$(self.dom).find(".header .navigation ul > li > a").removeClass("selected");
				$(this).addClass("selected");
				
				$(self.dom).find(".contents > .sectioncontainer > div").removeClass("selected");
				$(self.dom).find(".contents > .sectioncontainer > div." + $.trim($(this).attr("class").replace("selected","")) + ":last").addClass("selected");
				self.setupReleaseRoute($(this).data("section"));
				return false;
			});
			this.dispatchRoute(d);
		} 
		if($(this.dom).find(".header .navigation ul > li.popup > a").length > 0 ) {
			$(this.dom).find(".header .navigation ul > li.popup > a").off("click.navigationbar").on("click.navigationbar",function(ev){
				ev.preventDefault();
				$(this).toggleClass("selected");
				return false;
			});
			appdb.repository.onViewBlur(this,function(){
				$(this.dom).find(".header .navigation ul > li.popup > a").removeClass("selected");
			});
		}
	};
	this.selectSection = function(section){
		var ssection = section || "";
		section = section || "details";
		ssection = ".header > .navigation > ul > li " + ( (section)?"a."+section:"a:first");
		if( $(this.dom).find(ssection).length > 0 ){
			$(this.dom).find(ssection).trigger("click.navigationbar");
		}else{
			var found = false;
			$(this.dom).find(".header > .navigation > ul > li a").each(function(i,e){
				if( found === false && $(e).data("section") === section ){
					found = e;
				}
			});
			if( found ){
				$(found).trigger("click.navigationbar");
			}
		}
	};
	this.dispatchRoute = function(d){
		if( d && d.content && d.content== "productrelease" && d.datatype=='item' ){
			var r = appdb.routing.FindRoute();
			if( r && r.parameters){
				this.selectSection(r.parameters.releasesection);
			} else {
				return;
			}
		}else if( d && d.content && d.content=== "repositoryarea" && d.datatype==='item' ){
			this.setupSeriesRoute();
		}
	};
	this.cancelLoad = function(){
		if( this.options.xhr && this.options.xhr.abort ){
			this.options.xhr.abort();
			this.options.xhr = null;
		}
	};
	this.setLoading = function(loading, text){
		loading = (typeof loading === "boolean")?loading:false;
		$(this.dom).find(".loader").remove();
		$(this.dom).find(".errormessage").remove();
		if( loading ){
			text = text || "loading";
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).append(loader);
			$(this.dom).children(".actions .action").addClass("hidden");
		}
	};
	this.xhrLoad = function(_url,_data){
		var self = this;
		this.publish({event: "beforeload", value: _data});
		this.cancelLoad();
		this.options.xhr = $.ajax({
			url: _url,
			data: _data,
			dataType: "xml",
			success: function(v){
				var d = appdb.utils.convert.toObject(v);
				if( d.error ){
					self.publish({event: "error", value: d});
					return;
				}
				self.publish({event: "load", value: d});
				if( $.trim(appdb.pages.application.currentSection()) === "releases") {
					self.dispatchRoute(d);
				}
				self.render(d);
			},
			error: function(err){
				self.publish({event: "error", value: {error: err}});
			}
		});
	};
});

appdb.repository.ui.views.RepositoryAreaDetais = appdb.ExtendClass(appdb.repository.ui.views.MainContent, "appdb.repository.ui.views.RepositoryAreaDetais", function(o){
	this.options = {
		parent: o.parent || null,
		data: {}
	};
	this.getData = function(){
		return this.options.data;
	};
	this.getRelease = function(){
		return this.getData();
	};
	this.reset = function(){
		for(var i in this.subviews){
			this.subviews[i].reset();
		}
	};
	this.updateDataProperty = function(name,value){
		this.options.data[name] = value;
	};
	this.initProductionBuildHandler = function(d){
		appdb.repository.utils.ProductionBuildHandler.reset();
		if( d.lastproductionbuild  ){ 
			var handler = new appdb.repository.utils.ProductionBuildHandler({id: d.id, name: d.name, lastproductionbuild: d.lastproductionbuild});
			handler.unsubscribeAll();
			handler.subscribe({event: "tick", callback: function(v){
				$(this.dom).addClass("buildingrepos");
				this.subviews.overview.setBuildState(v);
			}, caller: this}).subscribe({event: "timeout", callback: function(){
				this.subviews.overview.setBuildState(false);
				$(this.dom).removeClass("buildingrepos");
			}, caller: this});
			handler.render();
		}else{
			$(this.dom).removeClass("buildingrepos");
			this.subviews.overview.setBuildState(false);
		}
	};
	this.renderToolbar = function(data){
		var dom = $(this.dom).find(".releaseactions.toolbar");
		$(dom).addClass("hidden").find(".action[data-view]").off("click");
		var viewdata = {series: null, release: null };
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
			if( !data ) return;
			viewdata.series = (appdb.repository.ui.CurrentReleaseManager.getSeriesById(data.repositoryarea.id) || null );
			$(dom).removeClass("hidden").find(".action[data-view]").each((function(self){
				return function(i,e){
					var v = appdb.FindNS("appdb.repository.ui.command." + $(e).data("view"), false);
					if(v){
						var invalidactionclass = "invalid-group-" + $.trim($(e).data("view")).toLowerCase().replace(/\./g,"-");
						if(typeof v.canPerformForData === "function" && v.canPerformForData(viewdata) === false ){
							$(dom).addClass(invalidactionclass);
							return;
						}else{
							$(dom).removeClass(invalidactionclass);
						}
						$(e).removeClass("invalidaction").off("click").on("click", (function(selff){
							return function(ev){
								ev.preventDefault();
								var pgview = $.trim($(this).data("view-page")) || "ConfirmCommand";
								new v({parent: selff,datasource:appdb.repository.ui.CurrentReleaseManager, data: viewdata, currentPage:pgview, hasPrevious: false}).render(viewdata);
								return false;
							};
						})(self));
					}
				};
			})(this));
		}
	};
	this.render = function(d){
		this.initNavigation(d);
		this.options.data = d.repositoryarea || {};
		if( d.lastproductionbuild){
			appdb.repository.ui.CurrentReleaseManager.updateRepositoryDataProperty(d.id, "lastproductionbuild", d.lastproductionbuild);
		}
		this.subviews.overview.render(this.options.data);
		this.subviews.contacts.render(this.options.data);
		this.initProductionBuildHandler(this.options.data);
		this.renderToolbar(d);
	};
	this.load = function(d){
		d = d || {};
		var _data = {view: "data"};
		if( d.id ){
			_data.id = d.id;
		} else if( d.swid ){
			_data.swid = d.swid;
		} else if( d ) {
			_data.id = d;
		} else {
			return;
		}
		
		this.xhrLoad(appdb.config.repository.endpoint.base + "repositoryarea/item", _data);
	};
	this.init = function(){
		this.template = $(o.container).html();
		this.dom = $(o.container);
		this.subviews.overview = new appdb.repository.ui.views.RepositoryAreaOverview({container: $(this.dom).find(".contents .overview"), parent: this});
		this.subviews.contacts = new appdb.repository.ui.views.ReleaseContacts({container: $(this.dom).find(".contact"), parent: this, viewonly: true, associatedType: "area", viewonly: false});
		this.initNavigation();
	};
	this.init();
});

appdb.repository.ui.views.ReleaseDetails = appdb.ExtendClass(appdb.repository.ui.views.MainContent, "appdb.repository.ui.views.ReleaseDetails", function(o){
	this.options = {
		parent: o.parent || null,
		data: {}
	};
	this.uploadreleasehandler = null;
	this.getData = function(){
		return this.options.data;
	};
	this.getRelease = function(){
		return this.getData();
	};
	this.reset = function(){
		for(var i in this.subviews){
			this.subviews[i].reset();
		}
	};
	this.onUnpublish = function(data, state){
		if( !this.options.data || !data || $.trim(this.options.data.id) !== $.trim(data.releaseid) ) return;
        appdb.repository.ui.CurrentReleaseManager.loadRelease(this.options.data.id);	
	};
	this.onPublish = function(data, state){
		if( !this.options.data || !data || $.trim(this.options.data.id) !== $.trim(data.releaseid) ) return;
		appdb.repository.ui.CurrentReleaseManager.loadRelease(this.options.data.id);
	};
	this.initProductionBuildHandler = function(d){
		appdb.repository.utils.ProductionBuildHandler.reset();
		if( d.state && $.inArray($.trim(d.state.name).toLowerCase(),["production","candidate"]) > -1 && d.lastproductionbuild){ 
			var handler = new appdb.repository.utils.ProductionBuildHandler({id: d.id, name: d.displayversion, lastproductionbuild: d.lastproductionbuild, group: "release"});
			handler.unsubscribeAll();
			handler.subscribe({event: "tick", callback: function(v){
				$(this.dom).addClass("buildingrepos");
				this.subviews.repositories.setBuildState(v);	
				this.subviews.files.setBuildState(v);
			}, caller: this}).subscribe({event: "timeout", callback: function(){
				$(this.dom).removeClass("buildingrepos");
				this.subviews.repositories.setBuildState(false);	
				this.subviews.files.setBuildState(false);
			}, caller: this});
			handler.render();
		}else{
			$(this.dom).removeClass("buildingrepos");
			this.subviews.repositories.setBuildState(false);	
			this.subviews.files.setBuildState(false);
		}
	};
    this.updateDataProperty = function(name,value){
            this.options.data[name] = value;
            switch(name){
                case "state":
                    var statename = $.trim(this.options.data.state.name).toLowerCase();
                    $(this.dom).find(".releasestate .releasestatevalue").removeClass().addClass("releasestatevalue").addClass(statename).text(this.options.data.state.name);
                    if(statename === "unverified"){
                        $(this.subviews.files.dom).find(".warningmessage").addClass("hide");
                    }                    
                    break;
            }
        };
		
	this.renderToolbar = function(data){
		var dom = $(this.dom).find(".releaseactions.toolbar");
		$(dom).addClass("hidden").find(".action[data-view]").off("click");
		var viewdata = {series: null, release: null };
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
			if( !data ) return;
			if( data.productrelease && $.trim(data.productrelease.id) !== "" ){
				viewdata.release = (appdb.repository.ui.CurrentReleaseManager.getCurrentRelease() || {} ).productrelease;
			}
			if( data.productrelease && $.trim(data.productrelease.id) !== "" && viewdata.release !== null){
				viewdata.series = (appdb.repository.ui.CurrentReleaseManager.getSeriesByReleaseId(viewdata.release.id) || null );
			}
			$(dom).removeClass("hidden").find(".action[data-view]").each((function(self){
				return function(i,e){
					var v = appdb.FindNS("appdb.repository.ui.command." + $(e).data("view"), false);
					if(v){
						var invalidactionclass = "invalid-group-" + $.trim($(e).data("view")).toLowerCase().replace(/\./g,"-");
						if(typeof v.canPerformForData === "function" && v.canPerformForData(viewdata) === false ){
							$(dom).addClass(invalidactionclass);
							return;
						}else{
							$(dom).removeClass(invalidactionclass);
						}
						$(e).removeClass("invalidaction").off("click").on("click", (function(selff){
							return function(ev){
								ev.preventDefault();
								var pgview = $.trim($(this).data("view-page")) || "ConfirmCommand";
								new v({parent: selff,datasource:appdb.repository.ui.CurrentReleaseManager, data: viewdata, currentPage:pgview, hasPrevious: false}).render(viewdata);
								return false;
							};
						})(self));
					}
				};
			})(this));
		}
	};
    this.renderMiscProperties = function(data){
		var d = data.productrelease || {};
		var dom = $(this.dom).children(".miscproperties");
		var stateprop = $(dom).find(".releasestate.fieldvalue");
		
		var statename = (d.state && $.trim(d.state.name))?d.state.name:"Unkown";
		var statecontent = $(document.createElement("span")).addClass("releasestatevalue").addClass(statename.toLowerCase()).text(statename);
		$(stateprop).find(".value").empty().append(statecontent);
		$(this.dom).removeClass("production").removeClass("unverified").removeClass("candidate").addClass($.trim(d.state.name).toLowerCase());
	};
	this.render = function(d){
		this.initNavigation(d);
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
			this.uploadreleasehandler = appdb.repository.UploadManager.getReleaseHandler(d.productrelease);
		}
		this.options.data = d.productrelease || {};
		this.setupPropertyEvents([this.subviews.documentation,this.subviews.repositories]);
		this.subviews.documentation.render(this.options.data);
		this.subviews.files.render(this.options.data);
		this.subviews.repositories.render(this.options.data);
		this.subviews.contacts.render(this.options.data);
		this.renderToolbar(d);
		this.renderMiscProperties(d);
		$(this.dom).data("itemdata",d);
		this.initProductionBuildHandler(this.options.data);
		
	};
	this.load = function(o){
		var _data = {view: "data"};
		if( o.id ){
			_data.id = o.id;
		} else if( o.swid ){
			_data.swid = o.swid;
		} else if( o ) {
			_data.id = o;
		} else {
			return;
		}
		//Inherited from appdb.repository.ui.MainContent
		this.xhrLoad(appdb.config.repository.endpoint.base + "release/item", _data);
		
	};
	this.setupPropertyEvents = function(views){
		views = views || [];
		views = $.isArray(views)?views:[views];
		
		var _callback = function(v){
			if( !v || !v.parent || !v.parent._type_ || !v.value || !v.value.datapath) return;
			for( var i in this.subviews){
				if( this.subviews[i]._type_.getName() !== v.parent._type_.getName() && 
					typeof this.subviews[i].setPropertyValue !== "undefined"
					) {
					this.subviews[i].setPropertyValue(v.value.datapath,v.value.datavalue,true);
				}
			}
		};
		for(var vi =0; vi<views.length; vi+=1){
			views[vi].unsubscribeAll(this);
			views[vi].subscribe({event: "propertychanged", callback: function(v){
					_callback.call(this,v);
			}, caller: this});
		}
	};
	this.init = function(){
		this.template = $(o.container).html();
		this.dom = $(o.container);
		this.initNavigation();
		this.subviews.documentation = new appdb.repository.ui.views.ReleaseDocumentation({container: $(this.dom).find("div.documentation"), parent: this});
		this.subviews.files = new appdb.repository.ui.views.ReleaseFiles({container: $(this.dom).find("div.files"), parent: this});
		this.subviews.repositories = new appdb.repository.ui.views.ReleaseRepositories({container: $(this.dom).find("div.download"), parent: this});
		this.subviews.contacts = new appdb.repository.ui.views.ReleaseContacts({container: $(this.dom).find(".contact"), parent: this});
		this.subviews.files.subscribe({event: "changed", callback: function(v){
				this.subviews.repositories.render(this.options.data);
		}, caller: this});
	};
	this.init();
});
appdb.repository.ui.views.ReleaseOperations = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleaseOperations", function(o){
	var _popup =  new dijit.TooltipDialog({content : "<span class='notimplemented'>Not implemented yet</span>"});
	this.setNotImplemented = function(){
		$(this.dom).find("a.command").each(function(i,e){
			$(e).off("click").on("click", function(ev){
				ev.preventDefault();
				setTimeout((function(elem){
					return function(){
						dijit.popup.close(_popup);
						dijit.popup.open({
							parent : $(elem)[0],
							popup: _popup,
							around : $(elem)[0],
							x: 100,
							y: 0,
							orient: {"BL":"TL"}
						});
					};
				})(e),10);
				return false;
			});
		});
	};
	this.handleCommands = function(){
		$(this.dom).find("a.command").each((function(self){
			return function(i,e){
				var v = appdb.FindNS($(e).data("view"), false);
				if(v){
					$(e).off("click").on("click", (function(selff){
						return function(ev){
							ev.preventDefault();
							new v({parent: selff,datasource:appdb.repository.ui.CurrentReleaseManager}).render();
							return;
						};
					})(self));
				}
			};
		})(this));
	};
	this.handleLinks = function(){
		this.setNotImplemented();
		
		if( $(this.dom).find(".newrelease > a").length > 0  ){
			$(this.dom).find(".newrelease > a").off("click").on("click",(function(self){ 
				return function(ev){
					ev.preventDefault();
					if( $(this).parent().hasClass("update") ){
						if( $(this).parent().data("parentid") ){
							appdb.repository.ui.CurrentReleaseManager.initNewRelease("update", $(this).parent().data("parentid"));	
						} else {
							appdb.repository.ui.CurrentReleaseManager.initNewRelease("update");
						}
					} else if($(this).parent().hasClass("base") ){
						appdb.repository.ui.CurrentReleaseManager.initNewRelease("base");
					} else {
						appdb.repository.ui.CurrentReleaseManager.initNewRelease();
					}
					return false;
				};
			})(this));
		}
		this.handleCommands();
		appdb.repository.onViewBlur(this, function(){});
	};
	this.render = function(){
		var manager = this.parent.canManageReleases();
		$(this.dom).empty();
		if( !manager ){
			return;
		}
		$(this.dom).append($(this.template).clone(true));
		$(this.dom).removeClass("hidden");
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent;
		this.template= $(this.dom).html();
	};
	this.init();
});

appdb.repository.ui.views.PoaRepositoryItem = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.PoaRepositoryItem", function(o){
	this.options = {
		data: o.data || {},
		dom: {
			os: $(o.container).find(".target .os"),
			arch: $(o.container).find(".target .arch"),
			artifactlist: $(o.container).find(".artifactlist"),
			repodetails: $(o.container).find(".repositoryitemdetails"),
			dropdownaction: $(o.container).find(".dropdownaction"),
			metapackages: $(o.container).find(".metapackagecontainer"),
			repolinks: $(o.container).find(".repositorylinks"),
			repofilecontents: $(o.container).find(".repofilecontents")
		},
		template: {
			artifact: $(o.container).find(".artifactlist .template.artifactitem").clone(true).removeClass("hidden").removeClass("template"),
			metapackageitem: $(o.container).find(".metapackagecontainer .template.metapackageitem").clone(true).removeClass("hidden").removeClass("template")
		}
	};
	
	this.reset = function(){
		$(this.dom).empty();
	};
	this.renderDetails = function(display,elem){
		display = display || false;
		if( display ){
			$(this.options.dom.repodetails).slideDown(200);
			if( elem ){
				$(elem).addClass("expand");
			}
		} else {
			if( elem ){
				$(elem).removeClass("expand");
			}
			$(this.options.dom.repodetails).slideUp(200);
		}
		
	};
	this.renderTarget = function(d){
		var os = d.os || {};
		var arch = d.arch || {};
		var artifacts = os.artifact || [];
		var dom = this.options.dom;
		var tmpl = this.options.template;
		var osimg = appdb.repository.utils.getOsImagePath( ((d && d.os && $.trim(d.os.name)!=="")?d.os.name:"" ) );
		var validtarget = (d && d.os && $.trim(d.os.name)==="generic")?false:true;
		artifacts = $.isArray(artifacts)?artifacts:[artifacts];
		$(this.dom).find(".target > img").attr("src", osimg);
		$(dom.os).find(".name").text(os.displayname || "noname");
		$(dom.os).find(".flavor").text(os.displayflavor || "noflavor");
		if(validtarget){
			$(dom.arch).removeClass("hidden");
			$(dom.arch).find(".name").text(arch.name || "unknown");
		}else{
			$(dom.arch).addClass("hidden");
		}
		
		
		$(dom.artifactlist).empty();
		$.each(artifacts, function(i,e){
			var li = $(document.createElement("li"));
			var item = $(tmpl.artifact).clone(true);
			$(item).find(".name").text(e.type);
			$(dom.artifactlist).append($(li).append(item));
		});
	};
	this.renderDropdownAction = function(d){
		var dda = this.options.dom.dropdownaction;
		var validtarget = (d.target && d.target.os && d.target.os.name==="generic")?false:true;
		if( validtarget ){
			$(dda).removeClass("hidden");
			$(dda).children("a").off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.parent.collapseAll(self);
					self.renderDetails(!$(this).parent().hasClass("expand"),$(this).parent());
					return false;
				};
			})(this));
		}else{
			$(dda).addClass("hidden");
		}
		
	};
	this.renderRepoLinks = function(d){
		var dom = this.options.dom.repolinks;
		var repourl = $(dom).find(".repourl");
		var repofileurl = $(dom).find(".repofileurl");
		$(dom).addClass("hidden");
		var currentState = d.state;
		var validtarget = (d.target && d.target.os && d.target.os.name==="generic")?false:true;
		
		if( !currentState && this.parent.options.data && this.parent.options.data.state && this.parent.options.data.state.name){
			currentState = this.parent.options.data.state;
		}
		if( currentState && currentState.name && $.inArray(currentState.name.toLowerCase(),this.parent.options.excludeStates) == -1 ){
			$(dom).removeClass("hidden");
		}
		
		if(d.repositoryinfo && $.trim(d.repositoryinfo.repositoryurl)!=""){
			$(repourl).removeClass("hidden");
			var href = d.repositoryinfo.repositoryurl || "";
			if( $.trim(d.target.incrementalsupport) === "false" && d.state && $.trim(d.state.name).toLowerCase() === "production"){
				href = href.replace(/[^\/]+\/?$/g,"");
			}
			$(repourl).children("a").attr("href", href).attr("target","_blank");
		}else{
			$(repourl).addClass("hidden");
		}
		
		if( validtarget && d.repositoryinfo && $.trim(d.repositoryinfo.repositoryfileurl)!==""){
			$(repofileurl).removeClass("hidden");
			$(repofileurl).children("a").attr("href", d.repositoryinfo.repositoryfileurl || "").attr("target","_blank");
		}else{
			$(repofileurl).addClass("hidden");
		}
	};
	this.renderRepoFileContents = function(d){
		var c = (d && d.repositoryinfo && d.repositoryinfo.repositoryfilecontents)?d.repositoryinfo.repositoryfilecontents:"";
		//chenck and transform repository file contents
		if( c ){
			$(this.options.dom.repofilecontents).find(".emptymessage").addClass("hidden");
			$(this.options.dom.repofilecontents).find(".contents").removeClass("hidden");
			c = c.replace(/^\<\!\[\[/,"").replace(/\]\]\>$/,"");
		}
		//check if repository file contents are empty
		if($.trim(c).replace(/\ /g,"") !== ""){
			$(this.options.dom.repofilecontents).find(".contents").empty().text(c);
		}else{
			$(this.options.dom.repofilecontents).find(".emptymessage").removeClass("hidden");
			$(this.options.dom.repofilecontents).find(".contents").empty().addClass("hidden");
		}
	};
	this.getUniqueMetapackages = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var uniq = {};
		var res = [];
		$.each(d, function(i, e){
			var u = uniq[e.md5sum];
			if( u ){
				if( $.trim(e.name) === $.trim(u.name) && $.trim(e.version) === $.trim(u.version) ){
					if( parseInt(e.versionindex) < parseInt(u.versionindex) ){
						uniq[e.md5sum] = e;
					}
				}
			}else{
				uniq[e.md5sum] = e;
			}
		});
		for(var u in uniq){
			if( uniq.hasOwnProperty(u) === false ) continue;
			res.push(uniq[u]);
		}
		return res;
	};
	this.renderMetapackages = function(d){
		d = d || {};
		d.poapackage = d.poapackage || [];
		d.poapackage = $.isArray(d.poapackage)?d.poapackage:[d.poapackage];
		var uniq = this.getUniqueMetapackages(d.poapackage);
		this.gridlist.render(uniq);
	};
	this.render = function(d){
		d = d || this.options.data;
		this.renderTarget(d.target);
		this.renderRepoLinks(d);
		this.renderDropdownAction(d);
		if( $.inArray(d.target.os.name,['generic']) > -1 ){
			$(this.options.dom.repodetails).addClass("hidden");
		}else{
			$(this.options.dom.repodetails).removeClass("hidden");
			this.renderMetapackages(d);
			this.renderRepoFileContents(d);
		}
		
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent;
		this.gridlist = new appdb.repository.ui.views.GridList({container: $(this.options.dom.metapackages).find(".metapackagelist"),
			columns:[
				{name: "name", displayName: "Name",dataPath: "name", type:"text", css: "min-width:150px;"},
				{name: "version", displayName: "Version", dataPath: "version", type:"text" , empty:"-",css: "width:60px"},
				{name: "description", displayName: "Description", dataPath: "description", type:"text", css:"min-width:200px;", empty:"no description available", 
					onRender: function(elem,data){
						var desc = data.description;
						desc = $.trim(desc)?desc:"";
						desc = desc.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
						$(elem).empty().append(desc);
						return $(elem);
					}
				}
			], 
			canDisplayRowData: function(d){
				if( $.trim(d.level).toLowerCase() === "dep" ) return false;
				return true;
			},
			css: "gridlist",
			defaults: {emptyItemText: "-", emptyDataText: "No metapackages defined for this repository"},
			parent: this});
			$(this.dom).find(".template").remove();
	};
	this.init();
});
appdb.repository.ui.views.PoaRepositoryList = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.PoaRepositoryList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		dom: {
			repositorylist: null
		},
		template: {
			repositoryitem: null			
		},
		group: o.group || "series",
		excludeStates: ["unverified",'candidate']
	};
	this.reset = function(){
		$.each(this.subviews, function(i,e){
			e.reset();
		});
		this.subviews = [];
		$(this.options.dom.repositorylist).empty();
		$(this.dom).find(".emptymessage").addClass("hidden");
	};
	this.collapseAll = function(exceptItem){
		$.each(this.subviews, function(i,e){
			if(exceptItem !== e ){
				e.renderDetails(false, $(e.dom).find(".dropdownaction"));
			}
		});
	};
	this.addItem = function(d){
		var li = $(document.createElement("li"));
		var itemdom = $(this.options.template.repositoryitem).clone(true);
		$(li).append(itemdom);
		$(this.options.dom.repositorylist).append(li);
		var poaitem = new appdb.repository.ui.views.PoaRepositoryItem({container: $(itemdom), parent:this, data:d});
		this.subviews.push(poaitem);
		poaitem.render();
	};
	this.setBuildingProcess = function(time){
		if( time == false ){
			$(this.dom).find(".repositorylinks").removeClass("hidden");
			$(this.dom).find(".header .building").remove();
		}else{
			$(this.dom).find(".repositorylinks").addClass("hidden");
			if( $(this.dom).find(".header .building").length > 0 ){
				$(this.dom).find(".header .building .timeout").html("[" + time + "]");
			}else{
				var div = $(document.createElement("div")).addClass("icontext").addClass("building");
				var img = $(document.createElement("img")).attr("alt","").attr("src","/images/ajax-loader-trans-orange.gif");
				var span = $(document.createElement("span")).append("<span>...building repositories.</span><span class='timeout'>[" + time + "]</span>");
				$(div).append(img).append(span);
				$(this.dom).children(".header").append(div);
			}
		}
	};
	this.renderEmpty = function(){
		$(this.dom).find(".emptymessage").removeClass("hidden");
	};
	this.render = function(d){
		var _compare = function(a, b) {
			if( $.trim(b.target.os.acronym).toLowerCase() === "tar" ) return 10;
			if( $.trim(a.target.os.acronym).toLowerCase() < $.trim(b.target.os.acronym).toLowerCase() ){
				return 1;
			} 
			if( $.trim(a.target.os.acronym).toLowerCase() > $.trim(b.target.os.acronym).toLowerCase() ) {
				return -1;
			}
			if( $.trim(a.target.arch.name).toLowerCase() < $.trim(b.target.arch.name).toLowerCase() ) { 
				return 1;
			}
			if( $.trim(a.target.arch.name).toLowerCase() > $.trim(b.target.arch.name).toLowerCase() ) { 
				return -1;
			}
			return 0;
		};
		this.options.data = d || this.options.data;
		var poas = d.poarelease || [];
		poas = $.isArray(poas)?poas:[poas];
		poas.sort(_compare);
	
		this.reset();
		if( poas.length === 0 ){
			this.renderEmpty();
		}else{
			$.each(poas, (function(self){
				return function(i,e){
					self.addItem(e);
				};
			})(this));
		}
	};	
	this.loadDomTemplates = function(){
		var tmpl = this.options.template;
		
		tmpl.repositoryitem = $(this.dom).find(".repositorylist .template.repositoryitem").clone(true).removeClass("hidden").removeClass("template");
		$(this.dom).find(".repositorylist .template.repositoryitem").remove();
		
		this.options.template = tmpl;
	};
	this.loadDomContainers = function(){
		var dom = this.options.dom;
		
		dom.repositorylist = $(this.dom).find(".repositorylist");
		$(dom.repositorylist).find(".template").remove();
		
		this.options.dom = dom;
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.loadDomTemplates();
		this.loadDomContainers();
		if( this.options.group === "release"){
			this.options.excludeStates = ["unverified"];
		}
	};
	this.init();
});

appdb.repository.UploadProgressBar = function(o){
	this.options = {
		container: $(o.container),
		file: o.file || null,
		uploader: o.uploader || null,
		dom: {},
		state: "init"
	};
	this.setState = function(state){
		if( this.options.state === "complete") state = "complete";
		$(this.options.dom.reportcontainer).removeClass();
		this.options.state = state || this.options.state || "init";
		$(this.options.dom.reportcontainer).addClass("progressbar").addClass(this.options.state);
	};
	this.checkInitialState = function(){
		if(this.options.file){
			var state = "init";
			switch(this.options.file.status){
				case plupload.QUEUED:
					state = "waiting";
					break;
				case plupload.UPLOADING:
					state = "progress";
					break;
				case plupload.FAILED:
					state = "error";
					break;
				case plupload.DONE:
					state = "complete";
					break;
			}
			this.setState(state);
			this.render(this.options.file);
		}		
	};
	this.renderOverall = function(d){
		switch(this.options.state.toLowerCase()){
			case "progress":
				break;
			case "error":
				break;
			case "complete":
				break;
			case "warning":
				break;
			default:
				break;
		}
	};
	this.renderFile = function(d){
		if( d && d.file && d.file.id !== this.options.file.id ) return;
		switch(this.options.state.toLowerCase()){
			case "waiting":
				this.setBar();
				this.setReport("Waiting ... "  + this.getSize(this.options.file.size));
				break;
			case "progress":
				this.setBar(d.file);
				this.setReport(this.getProgressText(d.file));
				break;
			case "error":
				if( d &&  d.response && d.response.upload && d.response.upload.result==='error'){
					this.setReport(d.response.upload.error || "Unkown error");
				}else{
					this.setReport(d.message);
				}
				this.setBar();
				break;
			case "warning":
				if( d && d.response && d.response.upload && $.trim(d.response.upload.warning)!=''){
					this.setReport(d.response.upload.warning);
				}else{
					this.setState("complete");
					this.setReport("completed");
				}
				break;
			case "complete":
				if( d && d.response && d.response.upload && d.response.upload.result==='error'){
					this.setReport(d.response.upload.error || "Unkown error");
				}else{
					this.setReport("completed");
				}
				break;
			default:
				this.setReport( this.getSize(this.options.file.size) );
				this.setBar();
				break;
		}
	};
	this.render = function(d){
		this.dom.removeClass("hidden");
		if( this.options.file ){
			this.renderFile(d);
		}else{
			this.renderOverall(d);
		}
	};
	this.destroy = function(){
		this.options.uploader.unsubscribeAll(this);
		$(this.dom).html("");
	};
	this.getProgressText = function(file){
		file = file || {};
		var percentage = file.percent || 0;
		var uploaded = file.loaded || "";
		var size = file.size || "";
		if( uploaded ){
			uploaded = this.getSize(uploaded);
		}
		if( size ){
			size = this.getSize(size);
		}
		
		var res = "";
		if( percentage ){
			res = percentage + "%";
		}
		if(uploaded && size){
			res += " [ " + uploaded + " / " + size + " ]";
		}
		return res;
	};
	this.setBar = function(file){
		file = file || {};
		var percentage = file.percent || 0;
		var uploaded = file.loaded || "";
		var size = file.size || "";
		if( uploaded ){
			uploaded = this.getSize(uploaded);
		}
		if( size ){
			size = this.getSize(size);
		}
		
		var cw = $(this.options.dom.reportcontainer).width();
		var p = (cw / 100) * percentage;
		$(this.options.dom.bar).width(p + "px");
		if( percentage >= 100 ) this.setState("complete");
	};
	this.setReport = function(report){
		if(this.options.state === "warning" ){
			report = "<span>completed</span><span class='warning'>" + report + "</span>";
		}
		$(this.options.dom.report).html("<span>" + report + "</span>");
	};
	this.getSize = function(bytes){
		return appdb.repository.utils.getFileSize(bytes);
	};
	this.show = function(){
		$(this.dom).removeClass("hidden");
	};
	this.hide = function(){
		$(this.dom).addClass("hidden");
	};
	this.hasFile = function(d){
		d = d.file || d || "";
		d = d.id || d;
		if( d && this.options.file){
			return (d===this.options.file.id)?true:false;
		}
		return false;
	};
	this.handleEventComplete = function(v){
		if( this.hasFile(v.file.id) && v.response){
			if(v && v.response && v.response.upload && v.response.upload.result==="error"){
				this.setState("error");
			}else if(v && v.response && v.response.upload && v.response.upload.warning){
				this.setState("warning");
			}else{
				this.setState("complete");
			}
			this.render(v);
		}
	};
	this.setUploader = function(up){
		if( this.options.uploader ) {
			this.options.uploader.unsubscribeAll(this);
		}
		if( up ) {
			this.options.uploader = up;
		}
		
		this.options.uploader.subscribe({event: "progress", callback: function(v){
			if(v && v.percent != 100){
				if( this.hasFile(v.file.id) && v.percent != "100"){
					this.setState("progress");
					this.render(v);
				}
			}
		}, caller: this}).subscribe({event: "error", callback: function(v){ 
			if( this.hasFile(v.file.id) ){
				this.setState("error");
				this.render(v);
			} else {
				this.setState("warning");
				this.render(v);
			}
		}, caller: this}).subscribe({event: "complete", callback: function(v){
			if( !this.options.file ){
				this.setState("complete");
				this.render(v);
			}
		}, caller: this}).subscribe({event: "filecomplete", callback: function(v){
			if( this.hasFile(v.file.id) ){
				if(v && v.response && v.response.upload && v.response.upload.result==="error"){
					this.setState("error");
				}else if(v && v.response && v.response.upload && v.response.upload.warning){
					this.setState("warning");
				}else{
					this.setState("complete");
				}
				this.render(v);
			}
		}, caller: this}).subscribe({event: "startupload", callback: function(v){
			if( this.hasFile(v.file.id) ){
				this.setState("waiting");
				this.render(v);
			}
		}, caller: this});
		this.checkInitialState();
	};
	this.initContainer = function(){
		this.options.dom.reportcontainer = $(document.createElement("div")).addClass("progressbar");
		this.options.dom.report = $(document.createElement("div")).addClass("data");
		this.options.dom.bar = $(document.createElement("div")).addClass("bar");
		
		$(this.options.dom.reportcontainer).append(this.options.dom.report).append(this.options.dom.bar);
		$(this.dom).addClass("hidden").append(this.options.dom.reportcontainer);
	};
	this.init = function(){
		this.dom = this.options.container;
		this.initContainer();
		if( this.options.uploader ){
			this.setUploader();
		}
		this.setState("init");
		this.render();
	};
	this.init();
};

appdb.repository.ui.views.ReleaseNavigationBar = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.ReleaseNavigationBar", function(o){
	this.showRelease = function(d){
		this.releaselist.showRelease(d.id,d.name);
	};
	this.showRepositoryArea = function(d){
		this.releaselist.showRepoArea(d.id,d.name);
	};
	this.addSeperator = function(){
		var ul = $(this.dom).find(".navigationbar");
		var li = $(document.createElement("li"));
		var span = $(document.createElement("span"));
		$(span).addClass("seperator").append("<img src='/images/right.gif' alt=''></img>");
		
		$(ul).append($(li).append(span));
	};
	this.getPrefixForItem = function(d){
		if( d && d.type && this.prefixItems[d.type]){
			return this.prefixItems[d.type];
		}
		return "";
	};
	this.addRootItem= function(d){
		var html = "";
		if( typeof this.rootItem === "function" ){
			html = this.rootItem(d);
		}else if ( $.trim(this.rootItem) === "" ){
			return;
		}else {
			html = this.rootItem;
		}
		
		var ul = $(this.dom).find(".navigationbar");
		var li = $(document.createElement("li"));
		$(li).append(html);
		$(ul).append(li);
		this.addSeperator();
	};
	this.addItem = function(d,appendSeperator){
		appendSeperator = appendSeperator || false;
		var ul = $(this.dom).find(".navigationbar");
		var li = $(document.createElement("li"));
		var item = $(document.createElement("a")).attr("href","#").attr("title","click to view " + d.name);
		var prefix = this.getPrefixForItem(d);
		
		if(appendSeperator){
			var funcname = "showRelease";
			switch(d.type){
				case "repositoryarea":
					funcname = "showRepositoryArea"
					break;
				default:
					break;
			}
			
			$(item).append("<span class='value'>" + d.name + "</span>");
			$(item).off("click").on("click", (function(self,fname,data){
				return function(ev){
					ev.preventDefault();
					self[fname].call(self,data);
					return false;
				};
			})(this,funcname,d));
		}else{
			item = $(document.createElement("span")).addClass("value").text(d.name);
		}
		$(li).append(item);
		if(prefix){
				$(li).prepend("<span class='prefix'>" + prefix + "</span>");
			}
		$(ul).append(li);
		if( appendSeperator ){
			this.addSeperator();
		}else{
			$(li).addClass("last");
		}
	};
	this.render = function(){
		if(this.releaselist){
			this.releaselist.unsuscribeAll(this);
		}
		$(this.dom).children("ul").remove();
		$(this.dom).append("<ul></ul>");
		$(this.dom).children("ul").addClass("navigationbar");
		
		this.releaselist = this.parent.views.releaseList;
		this.releaselist.subscribe({event: "loading", callback: function(d){
			$(this.dom).removeClass("hidden");
			$(this.dom).children("ul").remove();
			$(this.dom).append("<ul></ul>");
			$(this.dom).children("ul").addClass("navigationbar");
			this.addRootItem(d);
			if( $.isArray(d.map) && d.map.length > 0){
				$.each(d.map, (function(self){
					return function(i,e){
						self.addItem(e,true);
					};
				})(this));
			}
			this.addItem(d,false);
		}, caller: this});
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent || null;
		this.prefixItems = o.prefixitems || [];
		this.rootItem = o.rootitem || null;
	};
	this.init();
});
appdb.repository.ui.views.PropertyGrid = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.PropertGrid", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		columnscount: o.columnscount || 1,
		properties: o.properties || [],
		emptymessage: o.emptymessage || null,
		dom: {
			list: $(document.createElement("ul"))
		}
	};
	this.getData = function(){
		return this.options.data || {};
	};
	this.getContainer = function(){
		return $(his.options.container);
	};
	this.getWidthPercentage = function(property){
		property = property || {};
		var colspan = property.colspan || 1;
		if( this.options.columnscount > 1 && this.options.columnscount < 100) {
			if( colspan < 1 ){
				return null;
			}
			if( colspan >= this.options.columnscount ){
				return null;
			}
			var w = (100/this.options.columnscount) * colspan;
			return w;
		}
		return null;
	};
	this.renderProperty = function(property, data){
		var elem = $(document.createElement("li"));
		var content = $(document.createElement("div")).addClass("propertycontent");
		var field = $(document.createElement("div")).addClass("field");
		var sep = $(document.createElement("div")).addClass("seperator");
		var val = $(document.createElement("div")).addClass("value");
		
		if( $.trim(property.type).toLowerCase() === "external" ){
			var extType = $.trim(property.typeClass);
			var options = property.options || {};
			if( extType ){
				extType = appdb.FindNS(extType, false);
			}
			if( extType ){
				options = $.extend({parent: this, container: content, data: this.options.data}, options);
				var extInstance = new extType(options);
				extInstance.render(this.options.data);
			}
		}else if( typeof property.onRender === "function" ){
			content = property.onRender.call(this, content, this.getData());
		}else{
			if( $.trim(data) === "" && property.hideonempty === true ){
				return null;
			}
			if( $.trim(property.displayName) !== "" ){
				$(field).html(property.displayName);
				$(sep).text(":");
			}
			if( $.trim(data) === "" ){
				if( typeof property.empty !== "undefined"){
					$(val).html(property.empty);
				}else if( this.options.emptymessage ) {
					$(val).html(this.options.emptymessage);
				}
			} else if( $.trim(property.type).toLowerCase() == "url" ){
				var a = $(document.createElement("a")).attr("target","_blank").attr("titel","Click to open link in new window").attr("href", data).text(data);
				$(val).append(a);
			} else {
				$(val).html(""+ data);
			}
			
			$(content).append(field).append(sep).append(val);
		}
		
		if( $.trim(property.type) !== "" ){
			$(content).addClass($.trim(property.type).toLowerCase());
		}
		
		if( $.trim(property.className) !== "" ){
			$(content).addClass(property.className);
		}
		
		if( property.renderInline === true ){
			$(elem).addClass("renderinline");
		}
		
		var widthpercent = this.getWidthPercentage(property);
		if( widthpercent ){
			$(elem).css({"width": widthpercent + "%"}).addClass("column");
		}
		
		$(elem).append(content);
		return elem;
	};
	this.reset = function(){
		$(this.options.dom.list).empty();
	};
	this.render = function(d){
		this.reset();
		this.options.data = d;
		$.each(this.options.properties, (function(self){
			return function(i,e){
				if( !e || $.trim(e.name) === "" ) return;
				var data = appdb.repository.utils.FindData(d, e.name);
				var prop = self.renderProperty(e,data);
				if(prop !== null){
					$(self.options.dom.list).append(prop);
				}
			};
		})(this));
	};
	this.initContainer = function(){
		$(this.options.dom.list).addClass("propertygrid");
		$(this.dom).append(this.options.dom.list);
	};
	this.init = function(){
		this.parent = this.options.parent;
		this.dom = this.options.container;
		this.initContainer();
	};
	this.init();
});

appdb.repository.ui.views.DropdownPanel = appdb.ExtendClass(appdb.View, "appdb,repository.ui.views.DropdownPanel", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		content: o.content || $(document.createElement("div")).addClass("content"),
		dom: {
			panel: $(document.createElement("div")).addClass("dropdownpanel"),
			handler: o.handler || $($(o.container).children().get(0))
		}
	};
	this.getPanel = function(){
		return $(this.options.dom.panel);
	};
	this.renderContent = function(d){
		return $(this.getPanel()).html("rendered content");
	};
	this.render = function(d){
		this.renderContent(d);
	};
	this.hide = function(){
		$(this.options.dom.panel).slideUp(200, function(){
			$(this.dom).removeClass("expanded");
		});
	};
	this.show = function(){
		$(this.dom).addClass("expanded");
		$(this.options.dom.panel).slideDown(200);
	};
	this.initContainer = function(){
		$(this.dom).find(".dropdownpanel").remove();
		$(this.options.dom.panel).empty().hide();
		$(this.options.dom.handler).off("click").on("click", (function(self){
			return function(ev){
				var others = $(".releasemanager li.expanded > .dropdownpanel");
				if( others.length > 0 ){
					$(others).each(function(i,e){
						$(e).slideUp(200, function(){
							$(this).parent().removeClass("expanded");
							$(this).css({"display":""});
						});
					});
				}
				if( $(self.options.dom.panel).is(":visible") ){
					self.hide();
				}else{
					self.show();
				}
				
			};
		})(this));
		$(this.dom).append(this.options.dom.panel);
	};
	this.init = function(){
		this.parent = this.options.parent;
		this.dom = $(this.options.container);
		this.initContainer();
	};
	this.init();
});
appdb.repository.ui.views.DropDownPackageDetailsPanel = appdb.ExtendClass(appdb.repository.ui.views.DropdownPanel, "appdb.repository.ui.views.PackageDetails", function(o){
	this.options = $.extend(this.options,{propertygrid: null});
	this.renderContent = function(d){
		if( this.options.propertygrid ) {
			this.options.propertygrid.reset();
			this.options.propertygrid = null;
		}
		this.getPanel().empty();
		this.options.propertygrid = new appdb.repository.ui.views.PropertyGrid({
			container: this.getPanel(),
			parent: this,
			data: d,
			columnscount: 6,
			emptymessage: "<span>-</span>",
			properties :[
				{name: "generic", displayName: "generic", type: "external", typeClass: "appdb.repository.ui.views.PropertyGrid", colspan:6, className: "pkgGenericInfo", options:{
						columscount: 3,
						emptymessage: "<span>-</span>",
						properties: [
							{name: "size", displayName: "File size <span class='bytecount'>(bytes)</span>", className: "pkgSize"},
							{name: "md5sum", displayName: "MD5 hash", className: "pkgMd5"},
							{name: "sha1sum", displayName: "SHA1 hash", className: "pkgSha1"}
						]
				}},
				{name: "name", displayName: "Name" , className: "pkgname" , colspan: 6},
				{name: "version", displayName: "Version", className: "pkgversion", colspan: 3, empty: "<span class='empty'>No version available</span>"},
				{name: "release", displayName: "Release", className: "pkgRelease", colspan: 2, empty: "<span class='empty'>No release available</span>"},
				{name: "type", displayName: "Type" , className: "pkgType" , colspan: 1},
				{name: "url", displayName: "Package Url", className: "pkgUrl", type: "url", colspan: 6, hideonempty: true},
				{name: "description", displayName: "Description", className: "pkgDescription", type: "longtext", colspan: 6, empty: "<span class='empty'>No description available</span>"}
			]
		});
		this.options.propertygrid.render(d);
	};
});

appdb.repository.ui.views.RequestPermission = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.RequestPermission", function(o){
	this.show = function(){
		if( $(this.dom).find(".request.releasemanagement").hasClass("pending") ) return;
		appdb.repository.ui.ShowVerifyDialog({
				title: "Requesting release management permissions",
				message: $(this.dom).find(".content").html(),
				css: "requestreleasemanager",
				onOk: (function(self){
					return function(){
						self.execute();
					};
			})(this)
		});
	};
	this.execute = function(){
		this.model.unsubscribeAll();
		this.model.subscribe({event: "insert", callback: function(v){
			if( !v || v.error ){
				this.displayLoading(false);
				this.render();
				//TODO: HANDLE ERROR
			}else if( v ){
				this.displayLoading(false);
				this.setAsPending();
			}
		}, caller: this }).subscribe({event: "beforeinsert", callback: function(v){
			this.displayLoading(true);
		}, caller: this}).subscribe({ event: "error", callback: function(v){
		}, caller: this});
		
		this.model.insert({appid: appdb.repository.ui.CurrentReleaseManager.getAppDBId(), message: ""});
	};
	this.renderNoContact = function(elem){
		var owner = appdb.pages.application.getOwner();
		if( !owner ){
			return;
		}
		$(this.dom).find("a.itemowner").text(owner.firstname + " " + owner.lastname).attr("href","/store/person/"+owner.cname).off("click").on("click", (function(itemowner){
			return function(ev){
				ev.preventDefault();
				appdb.views.Main.showPerson({id:itemowner.id, cname: itemowner.cname},{mainTitle : itemowner.firstname + " " + itemowner.lastname});
				return false;
			};
		})(owner));
		var content = $(this.dom).find(".content > .nocontact").html();
		var _popup = new dijit.TooltipDialog({content : "<span class='nocontact'>" + content + "</span>"});
		dijit.popup.close(_popup);
		setTimeout(function(){
			dijit.popup.open({
				parent : $(elem)[0],
				popup: _popup,
				around : $(elem)[0],
				orient: {"BL":"TL"}
			});
		},10);
	};
	this.getApplicationContacts = function(){
		var contacts = [];
		var appdata = appdb.pages.application.currentData();
		appdata = appdata || {};
		appdata = appdata.application || appdata;
		appdata.contact = appdata.contact || [];
		contacts = $.isArray(appdata.contact)?appdata.contact:[appdata.contact];
		if( appdata.owner ){
			contacts.push(appdata.owner);
		}
		if( appdata.addedby ){
			contacts.push(appdata.addedby);
		}
		return contacts;
	};
	this.isOwner = function(){
		var appdata = appdb.pages.application.currentData();
		appdata = appdata || {};
		appdata = appdata.application || appdata;
		if( appdata.owner && appdata.owner.id == userID ) return true;
		if( appdata.addedby && appdata.addedby.id == userID ) return true;
		
		return false;
	};
	this.isContactPoint = function(){
		if( !userID ) return false;
		var res = false;
		var contacts = this.getApplicationContacts();
		$.each(contacts, function(i,e){
			if( e.id == userID ) res = true;
		}); 
		return res;
	};
	
	this.displayLoading = function(isLoading){
		isLoading = (typeof isLoading === "boolean")?isLoading:false;
		$(this.dom).find(".requesting").remove();
		$(this.dom).children("a").removeClass("hidden");
		if( isLoading ){
			$(this.dom).children("a").addClass("hidden");
			$(this.dom).append("<span class='requesting icontext'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>Sending request...</span>");
		}
	};
	this.setAsPending = function(){
		$(this.dom).find(".requesting").remove();
		$(this.dom).find("a").remove();
		$(this.dom).append('<span href="#" class="icontext"><img src="/images/logout3.png" alt="" /><span>Your request for release management permission is pending...</span></span>');
		$(this.dom).addClass("pending");
	};
	this.render = function(){
		if($(this.dom).hasClass("pending")) {
			$(this.dom).removeClass("hidden");
			return;
		}
		if( appdb.repository.ui.CurrentReleaseManager.canManageReleases() ){
			$(this.dom).addClass("hidden");
			return;
		}else{
			$(this.dom).removeClass("hidden");
		}
		$(this.dom).find(".softawarename").text(appdb.pages.application.currentName());
		if( this.isContactPoint() ){
			$(this.dom).children("a.icontext").off("click").on("click", (function(self){
				return function(ev){
					self.show();
				};
			})(this));
		} else {
			$(this.dom).children("a.icontext").off("click").on("click", (function(self){
				return function(ev){
					self.renderNoContact(this);
				};
			})(this));
		}
	};
	this.init = function(){
		this.parent = o.parent || null;
		this.dom = $(o.container);
		this.model = new appdb.model.ReleaseManagerRequest();
	};
	this.init();
});
/*************************************
 ***** COMPONENTS SECTION *******
 ************************************/
appdb.repository.ui.CurrentReleaseManager = null;
appdb.repository.ui.ReleaseManager = appdb.ExtendClass(appdb.Component,"appdb.repository.ui.components.ReleaseManager", function(o){
	o = o || {};
	this.dom = null;
	this.options = {
		swid: o.id || 0,
		viewMode: (userID===null)?true:false,
		softwareData: o.softwareData || {},
		canAddNew: false,
		releasetree: null,
                currentType: null,
                currentData: null
	};
	this.canManageReleases = function(){
		if( !userID ) return false;
		var privs = appdb.pages.application.currentPermissions();
		if( privs ){
			return privs.canManageApplicationReleases();
		}
		return false;
	};
	this.destroy = function(){
		for(var i in this.views){
			this.views[i].reset();
		}		
	};
	this.getCurrentRelease = function(){
          return (this.options.currentType==="release")?this.options.currentData:null;
    };
	this.getCurrentSeries = function(){
      return (this.options.currentType==="series")?this.options.currentData:null;
	};
	this.getSeriesById = function(id){
		var tree = this.options.releasetree || [];
		tree = $.isArray(tree)?tree:[tree];
		var found = false;
		$.each(tree, function(i,e){
			if( found === false && e.id == id ){
				found = e;
			}
		});
		return found;
	};
	this.getSeriesByReleaseId = function(releaseid){
		var tree = this.options.releasetree || [];
		tree = $.isArray(tree)?tree:[tree];
		var found = false;
		$.each(tree, function(i,e){
			if( found === false ){
				e.productrelease = e.productrelease || [];
				e.productrelease = $.isArray(e.productrelease)?e.productrelease:[e.productrelease];
				$.each(e.productrelease, function(ii,ee){
					if( ee.id == releaseid ){
						found = ee;
					}
				});
				if( found ){
					found = e;
				}
			}
		});
		return found;
	};
	this.getDataSource = function(type){
		type = $.trim(type).toLowerCase();
		var data = null;
		switch(type){
			case "releasetree":
				return this.views.releaseList.options.data;
				break;
			default:
				break;
		}
		return null;
	};
	this.initNewRelease = function(releaseType, parentid){
		releaseType = releaseType || "both";
		var appDBId = this.options.swid;
		(new appdb.repository.ui.NewReleaseForm({caller: this, releaseType: releaseType, parentid: parentid}).subscribe( {event: "error", callback: function(v){
			alert(v);
		}} ).subscribe({event: "done", callback: function(v){
			if(v.error) return;
			if(appdb.repository.ui.CurrentReleaseManager.views.releaseList && appdb.repository.ui.CurrentReleaseManager.views.releaseList.hasRepositoryArea()){
				appdb.repository.ui.CurrentReleaseManager.views.releaseList.addRepository(v.repository.repositoryarea, true);
				appdb.repository.ui.CurrentReleaseManager.views.releaseList.showRelease(v.id);
			}else{ //First release of software
				appdb.repository.ui.CurrentReleaseManager.refresh(function(){
					appdb.repository.ui.CurrentReleaseManager.views.releaseList.showRelease(v.id, v.displayVersion);
					appdb.repository.ui.CurrentReleaseManager.views.releaseList.updateReleaseData(v);
				});
			}
		}, caller: this} ) ).load({swid: appDBId, releaseType: releaseType, parentid: parentid});
	};
	this.getAppDBId = function(){
		return $(this.dom).data("id");
	};
	this.handleLinks = function(){
		var self = this;
		if( $(this.dom).find(".empty .newrelease > a").length > 0  ){
			$(this.dom).find(".empty .newrelease > a").on("click", function(ev){
				ev.preventDefault();
				if( $(this).parent().hasClass("update") ){
					if( $(this).parent().data("parentid") ){
						self.initNewRelease("update", $(this).parent().data("parentid"));	
					} else {
						self.initNewRelease("update");
					}
				} else if($(this).parent().hasClass("base") ){
					self.initNewRelease("base");
				} else {
					self.initNewRelease();
				}
				return false;
			});
		}
	};
	this.showContent = function(c){
		$(this.dom).find(".maincontent").addClass("hidden");
		switch(c){
			case "repositoryarea":
				$(this.dom).find(".maincontent.repoareadetails").removeClass("hidden");
				break;
			case "productrelease":
				$(this.dom).find(".maincontent.releasedetails").removeClass("hidden");
				break;
			default:
				break;
		}
	};
	this.loadContent = function(type,data, name){
		type = type || "";
		var view = null, loadingText = "loading";
		switch(type){
			case "productrelease":
				view = this.views.releaseDetails;
				if( data.swid ){
					loadingText = "Loading latest release details";
				}else{
					loadingText = "Loading " + (name||"") + " release details";
				}
				this.options.currentType = "release";
				break;
			case "repositoryarea":
				view = this.views.repoAreaDetails;
				if( data.swid ){
					loadingText = "Loading latest release details";
				}else{
					loadingText = "Loading " + (name||"") + " series details";
				}
				this.options.currentType = "series";
				break;
		}
		view.unsubscribeAll(this);
		view.subscribe({event: "beforeload", callback: function(){
				view.setLoading(true,loadingText);
				$(this.dom).removeClass("seriesdisplay").removeClass("releasedisplay");
				$(this.dom).addClass(this.options.currentType + "display");
		}, caller: this}).subscribe({event: "load", callback: function(d){
				this.options.currentData = d;
                view.setLoading(false);
		}, caller: this}).subscribe({event: "error", callback: function(){
                view.setLoading(false);
		}, caller: this});
	
		view.load(data);
		this.options.currentContent = view;
		this.showContent(type);
	};
	this.refresh = function(callback){
		setTimeout(function(){
			appdb.pages.application.loadReleaseManager(function(){
				appdb.repository.ui.CurrentReleaseManager.subscribe({event: "init", callback: function(){
					callback();
				}});
			});
		},0);
	};
	this.loadRepositoryArea = function(id, name){
		var data = {};
		if(id){
			data.id = id;
		} else {
			data.swid = appdb.pages.application.currentId();
		}
		this.loadContent("repositoryarea", data, name);
	};
	this.showRepositoryArea = function(id, name){
		this.views.releaseList.showRepoArea(id, name);
	};
	this.loadRelease = function(id,name){
		var data = {};
		if(id){
			data.id = id;
		} else {
			data.swid = appdb.pages.application.currentId();
		}
		this.loadContent("productrelease", data, name);
	};
	this.showRelease = function(id,name){
		this.views.releaseList.showRelease(id, name);
	};
	this.setupOperations = function(){
		this.handleLinks();
		this.views = {};
		this.views.releaseOperations = new appdb.repository.ui.views.ReleaseOperations({container: $(this.dom).find(".releaseoperations"), parent: this});
		this.views.releaseList = new appdb.repository.ui.views.ReleaseList({container: $(this.dom).find(".releaselistcontainer:last .releaselist"), releaseManager: this, parent: this});
		this.views.repoAreaDetails = new appdb.repository.ui.views.RepositoryAreaDetais({container: $(this.dom).find(".repoareadetails"), releaseManager: this, parent: this});
		this.views.releaseDetails = new appdb.repository.ui.views.ReleaseDetails({container: $(this.dom).find(".releasedetails"), releaseManager: this, parent: this});
		this.views.releaseList.unsubscribeAll(this).subscribe({event: "load", callback: function(v){
				$(this.views.releaseList.dom).css({"visibility":"visible"});
				if(this.views.releaseList.hasRepositoryArea()){
					v.repositoryarea = v.repositoryarea || [];
					v.repositoryarea = $.isArray(v.repositoryarea)?v.repositoryarea:[v.repositoryarea];
					if( v.repositoryarea.length > 0 ){
						this.options.releasetree = v.repositoryarea;
						this.dispatchRoute();
					}
					appdb.pages.application.renderSWReleasesDownloadPanel(v);
				} else {
					this.renderEmpty();
				}
		}, caller: this});
		$(this.views.releaseList.dom).css({"visibility":"hidden"});
		this.views.navigation = new appdb.repository.ui.views.ReleaseNavigationBar({container: $(this.dom).find(".releasenavigationbar"), parent: this, prefixitems: {
				"repositoryarea" : "Series::",
				"release" : "Release::"
		}, rootitem : ""});
		this.views.navigation.render();
		this.views.releaseList.load({swid: appdb.pages.application.currentId()});
		this.views.releaseOperations.render();
		if( this.canManageReleases() ){
			$(this.dom).find(".createnewseries-container button").attr("title", "Create base/major release for " + appdb.pages.application.currentName()).off("click").on("click", function(ev){
				ev.preventDefault();
				appdb.repository.ui.CurrentReleaseManager.initNewRelease("base");
				return false;
			});
		} else {
			$(this.dom).find(".createnewseries-container").empty();
		}
		this.views.requestPermissions = new appdb.repository.ui.views.RequestPermission({
			container: $(this.dom).find(".request.releasemanagement"),
			parent: this
		});
		this.views.requestPermissions.render();
	};
	this.renderEmpty = function(){
		$(this.dom).children().addClass("hidden");
		$(this.dom).children(".empty").removeClass("hidden");
	};
	this.updateReleaseDataProperty = function(id, property, value){
		if( !this.views.releaseList || !this.views.releaseList.updateReleaseDataProperty){return;}
		this.views.releaseList.updateReleaseDataProperty(id, property, value);
                if( this.views.releaseDetails.options.data &&  this.views.releaseDetails.options.data.id == id){
                    this.views.releaseDetails.updateDataProperty(property,value);
                }
	};
	this.updateRepositoryDataProperty = function(id, property, value){
		if( !this.views.releaseList || !this.views.releaseList.updateRepositoryDataProperty){return;}
		this.views.releaseList.updateRepositoryDataProperty(id, property, value);
                if(this.views.repoAreaDetails.options.data && this.views.repoAreaDetails.options.data.id== id){
                    this.views.repoAreaDetails.updateDataProperty(property,value);
                }
	};
	this.isRouteLoaded = function(route){
		var cdata = (this.options.currentContent)?this.options.currentContent.options.data:null;
		
		if( !cdata || !( cdata.displayversion || cdata.name ) ){
			return false;
		}
		
		if( cdata.displayversion && route.parameters.release && route.parameters.series ){ //this is a release
			var release = this.views.releaseList.getReleaseByName(route.parameters.series, route.parameters.release);
			if( release && release.id == cdata.id ){
				return true;
			}
		}else if( cdata.name && route.parameters.series && !route.parameters.release){ //this is a series
			var repo = this.views.releaseList.getRepoAreaByName(route.parameters.series);
			if( repo && repo.id == cdata.id ){
				return true;
			}
		}
		
		return false;
	};
	this.selectMainContentSection = function(route){
		var mc = this.options.currentContent;
		if( !mc ) return;
		mc.selectSection(route.parameters.releasesection);
	};
	this.dispatchRoute = function(){
		var r = appdb.routing.FindRoute();
		var repo, release;
		if( !(r && r.parameters && $.trim(r.parameters.section).toLowerCase() === "releases") ){
			if(this.views.releaseList && this.views.releaseList.options.data && this.views.releaseList.options.data.length > 0){ //check if releases panel is already loaded
				return this.showRepositoryArea(this.views.releaseList.options.data[0].id, this.views.releaseList.options.data[0].name);
			}else{
				return false;
			}
		}
		if( this.isRouteLoaded(r) ){
			this.selectMainContentSection(r);
			return false;
		}
		repo = r.parameters.series;
		release = r.parameters.release;
		if( repo ){
			repo = this.views.releaseList.getRepoAreaByName(r.parameters.series);
		}
		
		if( !repo ) {
			if(this.views.releaseList && this.views.releaseList.options.data && this.views.releaseList.options.data.length > 0){ //check if releases panel is already loaded
				return this.showRepositoryArea(this.views.releaseList.options.data[0].id, this.views.releaseList.options.data[0].name);
			}else{
				return false;
			}
		}
		
		if( release ){
			release = this.views.releaseList.getReleaseByName(repo.name, release);
		}
		
		if( !release ){
			return this.showRepositoryArea(repo.id,repo.name);
		}
		
		return this.showRelease(release.id, release.displayversion);
	};
	this.generateCurrentRoute = function(){
		var res = "";
		var ser = this.getCurrentSeries();
		var rel = this.getCurrentRelease();
		rel = (rel)?(rel.productrelease || null):null;
		if( ser ){
			ser = ser.repositoryarea || ser;
			return "/" + ser.name;
		}else if( rel ) {
			res += "/" + $.trim(rel.repositoryarea.name).toLowerCase() + "/" + $.trim(rel.displayversion).toLowerCase();
			if( this.options.currentContent && this.options.currentContent.getSelectedSection && this.options.currentContent.getSelectedSection() ){
				res += "/" + this.options.currentContent.getSelectedSection();
			}
			return res;
		}
		return "";
	};
	this.init = function(){
		if( appdb.repository.ui.CurrentReleaseManager ){
			appdb.repository.ui.CurrentReleaseManager.destroy();
		}
		appdb.repository.ui.CurrentReleaseManager = this;
		this.options.viewMode = !appdb.repository.ui.CurrentReleaseManager.canManageReleases();
		this.options.softwareData = (this.options.softwareData && this.options.softwareData.application)?this.options.softwareData.application:this.options.softwareData;
		this.options.canAddNew = appdb.repository.ui.CurrentReleaseManager.canManageReleases();
		this.dom = $(".releasemanager[data-id=" + this.options.swid + "]");
		this.setupOperations();
		this.publish({event: "init", value: "", caller: this});
		if( this.canManageReleases() ){
			$(this.dom).addClass("authorized");
		}else{
			$(this.dom).removeClass("authorized");
		}
	};
	this.init();
});

appdb.repository.UploadFileHandler = appdb.ExtendClass(appdb.Component, "appdb.repository.UploadFileHandler", function(o){
	this.options = {
		uploader: null,
		parent: o.parent || null,
		container: o.container || null,
		filters : o.filters || [],
		group: o.group || "ungrouped",
		postdata: o.postdata || {}
	};
	this.destroy = function(){
		
	};
	this.start = function(){
		this.options.uploader.start();
		return true;
	};
	this.stop = function(){
		this.options.uploader.stop();
		return true;
	};
	this.getSelectedFiles = function(){
		return (this.options.uploader)?this.options.uploader.files:[];
	}
	this.removeSelectedFile = function(id){
		return this.options.uploader.removeFile(this.options.uploader.getFile(id));
	};
	this.bindUploader = function(){
		var self = this;
		if( this.options.uploader.isBinded == true ) return;
		this.options.uploader.unbind("Init");
		this.options.uploader.bind('Init', function(up, params) {
			appdb.debug("Uploader Inited...");
			self.options.uploader.refresh();
			self.publish({event: "init", value: {parameters: params}});
		});
		this.options.uploader.unbind("FilesAdded");
		this.options.uploader.bind('FilesAdded', function(up, files){
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
		this.options.uploader.unbind("FilesRemoved");
		this.options.uploader.bind('FilesRemoved', function(up, files){
			if(!up.internalFileRemoval){
				self.publish({event: "removefiles", value: files});
			}
		});
		this.options.uploader.unbind("QueueChanged");
		this.options.uploader.bind('QueueChanged', function(up){
			self.publish({event: "changefiles", value: {}});
		});
		this.options.uploader.unbind("BeforeUpload");
		this.options.uploader.bind('BeforeUpload', function(up,file){
			self.publish({event:"startupload", value: {file: file}});
		});
		this.options.uploader.unbind("UploadProgress");
		this.options.uploader.bind('UploadProgress', function(up, file) {
			self.publish({event: "progress", value: {file: file, percent: file.percent}});
		});
		this.options.uploader.unbind("FileUploader");
		this.options.uploader.bind('FileUploaded', function(up, file, info) {
			var res = appdb.utils.convert.toObject(info.response);
			self.publish({event: "filecomplete", value: {file: file, info: info, response: res}});
		});
		this.options.uploader.unbind("UploadComplete");
		this.options.uploader.bind('UploadComplete', function(up, file, info) {
			self.publish({event: "complete", value: {file: file, info: info}});
		});
		this.options.uploader.unbind("Error");
		this.options.uploader.bind('Error', function(up, err) {
			self.publish({event: "error", value: {message: err.message, code: err.code, file: err.file}});
		});
		this.options.uploader.isBinded = true;
	};
	this.initPluploader = function(){
		var self = this;
		this.options.filters = ($.isArray(this.options.filters)?this.options.filters:[this.options.filters]);
		//create trigger for given ids
		this.options.trigger = appdb.repository.UploaderRegistry.makeUploadButton($("#"+this.options.container), this.options.postdata, this.options.group, this.options.filters);
		this.options.uploader = appdb.repository.UploaderRegistry.getUploadObject(this.options.trigger);
		this.options.uploader.bind('Init', function(up, params) {
			appdb.debug("Uploader Inited...");
			self.options.uploader.refresh();
			self.publish({event: "init", value: {parameters: params}});
		});
		this.options.uploader.refresh();
		this.options.uploader.bind('FilesAdded', function(up, files){
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
		this.options.uploader.bind('FilesRemoved', function(up, files){
			if(!up.internalFileRemoval){
				self.publish({event: "removefiles", value: files});
			}
		});
		this.options.uploader.bind('QueueChanged', function(up){
			self.publish({event: "changefiles", value: {}});
		});
		this.options.uploader.bind('BeforeUpload', function(up,file){
			self.publish({event:"startupload", value: {file: file}});
		});
		this.options.uploader.bind('UploadProgress', function(up, file) {
			self.publish({event: "progress", value: {file: file, percent: file.percent}});
		});
		this.options.uploader.bind('FileUploaded', function(up, file, info) {
			var res = appdb.utils.convert.toObject(info.response);
			self.publish({event: "filecomplete", value: {file: file, info: info, response: res}});
		});
		this.options.uploader.bind('UploadComplete', function(up, file, info) {
			self.publish({event: "complete", value: {file: file, info: info}});
		});
		this.options.uploader.bind('Error', function(up, err) {
			self.publish({event: "error", value: {message: err.message, code: err.code, file: err.file}});
		});
	};
	this.init = function(){
		var self = this;
		
		setTimeout(function(){
			self.initPluploader();
		},1);
	};
	this.init();
});
appdb.repository.UploadReleaseTargetHandler = appdb.ExtendClass(appdb.Component, "appdb.repository.UploadReleaseTargetHandler", function(o){
	this.options = {
		id: -1, //Target id if the full target data is not available
		container: null, //id of button for selectiong files
		selectaction: null,
		target: null, //Data from comm_repo_allowed_platform_combinations which will be combined with POA
		dom: null,
		parent : null,
		state: "loaded",
		filehandler: null,
		error: ""
	};
	
	this.destroy = function(){
		this.options.filehandler.unsubscribeAll();
		this.options.filehandler.destroy();
		this.options.filehandler = null;
		this.publish({event: "destroy", value: this});
	};
	this.getState = function(){
		return this.options.state;
	};
	this.setState = function(state){
		this.options.state = $.trim(state).toLowerCase() || this.options.state;
		if( this.options.state === "running" ){
			this.options.parent.setState("running");
		}
	};
	this.getTarget = function(){
		return this.options.target;
	};
	this.getRelease = function(){
		return this.options.parent.getRelease();
	};
	this.getSelectedFiles = function(){
		return (this.options.filehandler)?this.options.filehandler.getSelectedFiles():[];
	};
	this.removeSelectedFile = function(id){
		return this.options.filehandler.removeSelectedFile(id);
	};
	this.start = function(){
		return this.options.filehandler.start();
	};
	this.stop = function(){
		return this.options.filehandler.stop();
	};
	this.init = function(){
		this.options = $.extend(this.options, o);
		
		if( this.options.target === null && this.options.id > -1 ){
			this.options.target = {id: o.id};
		} else if( !this.options.target.id && this.options.id ){
			this.options.target.id = this.options.id;
		} else if( !this.options.target ) {
			appdb.debug("[UPLOADER]: No target given for release: " + this.options.parent.getRelease().id + ". ");
			return null;
		}
		
		var filters = [];
		var t = this.getTarget();
		if( t && t.os && t.os.artifact){
			t.os.artifact = $.isArray(t.os.artifact)?t.os.artifact:[t.os.artifact];
			$.each(t.os.artifact, function(i,e){
				switch( e.type.toLowerCase() ){
					case "tar":
						filters.push({title : "tar files", extensions : "tar"});
						break;
					case "tgz":
						filters.push({title : "tgz files", extensions : "tgz"});
						break;
					case "tar.gz":
						filters.push({title : "gz files", extensions : "gz"});
						break;
					case "rpm":
						filters.push({title : "rpm files", extensions : "rpm"});
						break;
					case "deb":
						filters.push({title : "deb files", extensions : "deb"});
						break;
					default:
						break;
				}
			});
		}
		if(this.options.filehandler) return this;
		this.options.filehandler = new appdb.repository.UploadFileHandler({
			container: this.options.container, 
			parent: this,
			group: "repository",
			postdata: {swid: appdb.pages.application.currentId(), releaseid: this.getRelease().id, targetid: this.getTarget().id},
			filters: filters
		});
		this.options.error = "";
		this.options.filehandler.subscribe({event:"init", callback:function(v){
			this.setState("ready");
			this.publish({event: "init", value: v});
		}, caller: this}).subscribe({event:"addfiles", callback:function(v){
			this.setState("ready");
			this.publish({event: "addfiles", value: v});
		}, caller: this}).subscribe({event:"removefiles", callback:function(v){
			this.setState("ready");
			this.publish({event: "removefiles", value: v});
		}, caller: this}).subscribe({event:"changefiles", callback:function(v){
			this.setState("ready");
			this.publish({event: "changefiles", value: v});
		}, caller: this}).subscribe({event:"progress", callback:function(v){
			this.setState("running");
			this.publish({event: "progress", value: v});
		}, caller: this}).subscribe({event:"filecomplete", callback:function(v){
			this.setState("running");
			this.publish({event: "filecomplete", value: v});
		}, caller: this}).subscribe({event:"complete", callback:function(v){
			this.setState("complete");
			this.publish({event: "complete", value: v});
		}, caller: this}).subscribe({event:"error", callback:function(v){
			this.setState("complete");
			this.options.error = v;
			this.publish({event: "error", value: v});
			appdb.debug("ERROR", v);
		}, caller: this}).subscribe({event:"startupload", callback: function(v){
			this.setState("running");
			this.publish({event: "startupload", value: v});
		}, caller: this});
	
		return this;
	};
	this.init();
});
appdb.repository.UploadReleaseHandler = appdb.ExtendClass(appdb.Component, "appdb.repository.ReleaseUploadHandler", function(o){
	this.options = {
		id: -1, //Release id if the full target data is not available
		release: null,
		parent: null,
		handlers: []
	};
	this.destroy = function(){
		this.publish({event: "destroy", value: this});
	};	
	this.getHandlersByState = function(state){
		state = $.trim(state).toLowerCase();
		return $.grep(this.options.handlers , function(h,i){
			return (h.getState()===state)?true:false;
		});
	};
	this.isRunning = function(){
		return ( this.getHandlersByState("running").length > 0 )?true:false;
	};
	this.isComplete = function(){
		return (	 this.getHandlersByState("running").length ===0 && this.getHandlersByState("complete").length > 0 )?true:false;
	};
	this.hasHandlers = function(){
		return ( this.options.handlers.length > 0 )?true:false;
	};
	this.getRelease = function(){
		return this.options.release;
	};
	this.getAllTargetHandlers = function(){
		return this.options.handlers;
	};
	this.getHandlerIndex = function(target){
		var res = $.grep(this.options.handlers, function(e){
			return (e.getTarget().id === target.getTarget().id)?true:false;
		});
		return ( res.length > 0 )?res[0]:null;
	};
	this.getHandlerForTarget = function(target){
		var tid = -1;
		target = target || {};
		tid = ( typeof target === "number" )?target:(target.id || -1);
		
		var res = $.grep(this.options.handlers, function(e){
			return ( e.getTarget().id === tid );
		});
		
		return ( res.length > 0 )?res[0]:null;
	};
	this.getTargetHandler = function(targetdata,container_element){
		targetdata = targetdata || null;
		if( !targetdata ) {
			appdb.debug("[UploadReleaseHandler]: Cannot create new target handler. No parameters given.");
			return null;
		} else if ( typeof targetdata === "number" ){
			targetdata = {id: targetdata};
		}		
		if( !container_element) {
			appdb.debug("[UploadReleaseHandler]: Cannot create new target handler. No container dom given.");
			return null;
		}
		
		var t = this.getHandlerForTarget(targetdata.id);
		if( !t ) {
			t = new appdb.repository.UploadReleaseTargetHandler({
				container: container_element,
				target: targetdata,
				parent: this
			});
			t.subscribe({event: "destroy", callback: function(target){
				target.unsubscribeAll(this);
				var i = this.getHandlerIndex(target);
				if( !i ) return;
				this.options.handlers.splice(i,1);
			}, caller: this});
			this.options.handlers.push(t);
		}
		
		return t;
	};
	this.init = function(){
		this.options = $.extend(this.options, o);
		
		if( this.options.release === null && this.options.id > -1 ){
			this.options.release = {id: o.id};
		} else if( !this.options.release.id && this.options.id ){
			this.options.release.id = this.options.id;
		} else if ( this.options.release ){
			
		} else {
			appdb.debug("[UploadReleaseHandler]: No release given. options dump: ", this.options);
			return null;
		}
		return this;
	};
	this.init();
});
appdb.repository.UploaderRegistry = (function(){
	var _defaultUploadTriggerGroup = "ungrouped";
	var _defaultUploadTriggerData = {"noid":""};
	var _uploadDomContainer = null;
	var _uploadobjects = {};
	var _overlayUploadTrigger = function(el,trigger){
		var scrollX = 0;
		var scrollY = 0;
		if (window.pageXOffset != null){
			scrollX = window.pageXOffset;
			scrollY = window.pageYOffset;
		}else if(document.compatMode === "CSS1Compat"){
			scrollX = window.document.documentElement.scrollLeft;
			scrollY = window.document.documentElement.scrollTop;
		}else{
			scrollX = window.document.scrollLeft;
			scrollY = window.document.scrollTop;
		}
		var x = $(el).offset().left - scrollX;
		var y = $(el).offset().top  - scrollY;
		var w = $(el).width();
		var h = $(el).height();
		
		$(trigger).addClass("overlaytrigger").css({
			width: w + "px",
			height: h + "px",
			top: y + "px",
			left: x + "px"
		})
	};
	return new function(){
		this.createTriggerId = function(data,group){
			var i, id = group || _defaultUploadTriggerGroup;
			for(i in data){
				if( ! data.hasOwnProperty(i) ) continue;
				id += "_" + i + "_" + data[i];
			}
			return id;
		};
		this.findUploadTrigger = function(data, group){
			data = data || _defaultUploadTriggerData;
			group = group || _defaultUploadTriggerGroup;
			var id = this.createTriggerId(data, group);
			var e = $(_uploadDomContainer).find("#" + id);
			if( $(e).length > 0 ){
				return $(e).data("trigger") || null;
			}
			return null;
		};
		this.getUploadObject = function(trigger){
			if(! _uploadobjects[trigger.containerid] ){
				_uploadobjects[trigger.containerid] = new plupload.Uploader({
					runtimes : 'html5,flash,silverlight,gears,html4,browserplus',//old version of firefox have isues with flash
					browse_button : trigger.actionid,
					container : trigger.containerid,
					multi_selection: true,
					unique_names : false,
					prevent_duplicates: true,
					chunk_size : '500kb',
					max_file_size : '100mb',
					url : appdb.config.repository.endpoint.upload,
					flash_swf_url : '/plupload.flash.swf',
					silverlight_xap_url : '/plupload.silverlight.xap',
					filters : trigger.filters || [],
					multipart_params: trigger.data
				});
				_uploadobjects[trigger.containerid].inited = false;
			}
			return _uploadobjects[trigger.containerid];
		};
		this.getUploadTrigger = function(data,group,filters){
			data = data || _defaultUploadTriggerData;
			group = $.trim(group).toLowerCase() || _defaultUploadTriggerGroup;
			if( $.isArray(data) ){
				data.sort();
				var newdata = {};
				$.each(data, function(i,e){
					newdata["d"+i] = e;
				});
				data = newdata;
			}
			var trigger = this.findUploadTrigger(data,group,filters);
			if( !trigger ){
				var id = this.createTriggerId(data, group);
				var container = $(document.createElement("div")).addClass("uploadcontainer").attr("id",id);
				var action = $(document.createElement("a")).addClass("uploadaction").attr("id","action_" + id);
				$(container).append(action);
				$(_uploadDomContainer).append(container);
				trigger = { 
					dom: container,
					containerid: $(container).attr("id"),
					actionid: $(action).attr("id"),
					filters: filters,
					data: data,
					uploadObj: null
				};
				trigger.uploadObj = this.getUploadObject(trigger);
				trigger.uploadObj.init();
				$(container).data("trigger", trigger);
			}
			
			return trigger;
		};
		this.makeUploadButton = function(element,data,group,filters){
			data = data || _defaultUploadTriggerData;
			group = $.trim(group).toLowerCase() || _defaultUploadTriggerGroup;
			if( !element || $(element).length === 0 ){
				appdb.debug("[UploadReleaseHandler.getUploadTrigger]: Could not retrieve upload trigger. Element not found");
				return false;
			}
			var trigger = this.getUploadTrigger(data, group,filters);
			if( !trigger ){
				appdb.debug("[UploadReleaseHandler.getUploadTrigger]: Could not retrieve upload trigger with id: " + this.createTriggerId(data, group));
				return false;
			}
			$(element).off("mouseover.uploadtrigger").on("mouseover.uploadtrigger", (function(_trigger,self){
				return function(ev){
					_overlayUploadTrigger(this,_trigger.dom);
					self.getUploadObject(_trigger).refresh();
				};
			})(trigger,this));
			$(trigger.dom).off("mouseleave.uploadtrigger").on("mouseleave.uploadtrigger", (function(_trigger,self){
				return function(ev){
					$(_trigger.dom).removeClass("overlaytrigger").css({top:"0px", left:"0px", width:"1px",height:"1px"});
				};
			})(trigger,this));
			
			return trigger;
		};
		this.init = function init(){
			$(document).ready(function(){
				_uploadDomContainer = document.createElement("div");
				$(_uploadDomContainer).addClass("uploadTriggersContainer");
				$("body").append(_uploadDomContainer);
			});
		};
		this.init();
	};
	
})();
appdb.repository.UploadManager = (function(){
	var _uploadDomContainer = $(document.createElement("div"));
	$(_uploadDomContainer).addClass("uploadContainer");
	$("body").append(_uploadDomContainer);
	var _releases = [];
	var _releaseById = function(id){
		var i, len = _releases.length;
		for(i=0; i<len; i+=1){
			if( _releases.getRelease().id === id) {
				return i;
			}
		}
		return -1;
	};
	var _manager = appdb.ExtendClass(appdb.Component, "appdb.repository.UploadManager", function(o){
		this.registry = [];
		this.getHandlersByState = function(state){
			state = $.trim(state).toLowerCase();
			var res = [];
			$.each(_releases, function(i,e){
				res.concat(e.getHandlersByState(state));
			});
			return res;
		};
		this.getRunningHandlers = function(){
			return this.getHandlersByState("running");
		};
		this.getReadyHandlers = function(){
			return this.getHandlersByState("ready");
		};
		this.getCompleteHandlers = function(){
			return this.getHandlersByState("complete");
		};
		this.getUploadContainer = function(releaseid, targetid){
			
		};
		this.getReleaseHandlerById = function(id){
			var res = $.grep( this.registry, function(e){
				return (e.getRelease().id === id )?true:false;
			});
			return ( res.length > 0 )?res[0]:null;
		};
		this.getReleaseHandler = function(releasedata){
			if( !releasedata ){
				appdb.debug("[UploadManager]: Cannot create release upload handler. No parameters given");
				return null;
			}
			if(typeof releasedata === "number"){
				releasedata = {id: releasedata};
			}
			
			var r = this.getReleaseHandlerById(releasedata.id);
			if( !r ){
				r = new appdb.repository.UploadReleaseHandler({
					release: releasedata,
					parent: this
				});

				r.subscribe({event: "destroy", callback: function(release){
						release.unsubscribeAll(this);
				}, caller: this});
				this.registry.push(r);
			}
			
			return r;
		};
		this.init = function(){};
		this.init();
		
	});
	
	return new _manager();
})();

appdb.repository.onViewBlur = (function(){
	var _subscribers = [];
	var _windowfocus = function(ev){
		var _valid = [];
		$.each(_subscribers, function(i,e){
			if( e && e.item && typeof e.callback !== "undefined") {
				e.callback.apply(e.item);
				_valid.push(e);
			}
		});
		_subscribers = [];
		_subscribers = [].concat(_valid);
	};
	var _subscribe = function(subscriber, callback){
		if( arguments.length === 0){
			_windowfocus();
		}
		var found = false;
		$.each(_subscribers, function(i,e){
			if( e && e.item && e.item === subscriber){
				found = e;
			}
		});
		if( found === false ){
			_subscribers.push({item: subscriber, callback: callback});
		}
	};
	$("body").on("click.viewblur", function(ev){
		_windowfocus(ev);
	});
	return _subscribe; 
})();
appdb.repository.ui.views.TextBoxChecker = appdb.ExtendClass(appdb.View, "appdb.repository.ui.views.TextBoxChecker", function(o){
	this.options = {
		dom: $(o.container),
		parent: o.parent || null,
		url: o.url,
		data: o.data,
		maxlength: o.maxlength || 100,
		timeInterval: null,
		timeout: 500,
		xhr: null
	};
	this.showChecking = function(){
		$(this.options.dom.validation).find(".message").addClass("hidden");
		$(this.options.dom.validation).find(".loader").removeClass("hidden");
	};
	this.showMessage = function(m){
		$(this.options.dom.validation).find(".loader").addClass("hidden");
		$(this.options.dom.validation).find(".message").removeClass("hidden").children("span").text(m);
	};
	this.hideChecking = function(){
		$(this.options.dom.validation).find(".loader").addClass("hidden");
	};
	this.hideMessage = function(){
		$(this.options.dom.validation).find(".message").addClass("hidden");
	};
	this.hideValidation = function(){
		this.hideChecking();
		this.hideMessage();
	};
	this.processSuccessMessage = function(d){
		appdb.debug("processing: " ,d);
	};
	this.processErrorMessage = function(d){
		appdb.debug("erroring: " ,d);
	};
	this.checkValue = function(v){
		if( this.options.timeInterval !=  null){
			clearTimeout(this.options.timeInterval);
			this.options.timeInterval = null;
		}
		if( this.options.xhr !== null ){
			if( this.options.xhr.abort ) {
				this.options.xhr.abort();
			}
			xhr = null;
		}
		
		setTimeout((function(self){
			return function(){
				self.showChecking();
				self.options.xhr = $.ajax({
					url: appdb.config.repository.endpoint.base + self.options.url,
					data: self.options.data,
					success: function(d){
						self.hideChecking();
						self.processSuccessMessage(d);
					},
					error: function(d){
						self.hideChecking();
						self.processErrorMessage(d);
					}
				})
			};
		})(this), this.options.timeout);
		
	};
	this.render = function(d){
		d = d || this.options.data || {};
		this.options.data = d || this.options.data;
		var div = $(document.createElement("div")).addClass("textboxchecker");
		
		var textboxcontainer = $(document.createElement("div")).addClass("textbox");
		var textbox = $(document.createElement("input")).attr("type","textbox").attr("maxlength",this.options.maxlength);
		
		var validation = $(document.createElement("div")).addClass("validation").addClass("hidden");
		var loader = $(document.createElement("div")).addClass("loader").addClass("hidden");
		var loaderimg = $(document.createElement("img")).attr("src","/images/ajax-loader-trans-orange.gif").attr("alt","");
		var loadertext = $(document.createElement("span")).text("...checking");
		
		var message = $(document.createElement("div")).addClass("message").addClass("hidden");
		var messageimg = $(document.createElement("img")).attr("src","/images/ajax-loader-trans-orange.gif").attr("alt","");
		var messagetext = $(document.createElement("span")).text("...checking");
		
		$(loader).append(loaderimg).append(loadertext);
		$(message).append(messageimg).append(messagetext);
		$(validation).append(loader).append(message);
		$(textboxcontainer).append(textbox);
		$(div).append(textboxcontainer).append(validation);
		$(this.dom).empty().append(div);
		
		$(textbox).off("change").on("change", (function(self){ 
			return function(ev){
				ev.preventDefault();
				return false;
			};
		})(this)).off("keyup").on("keyup", (function(self){ 
			return function(ev){
				ev.preventDefault();
				return false;
			};
		})(this)).off("mouseup").on("mouseup", (function(self){ 
			return function(ev){
				ev.preventDefault();
				return false;
			};
		})(this));
		
		this.options.dom = {
			validation: validation,
			message: message,
			loader: loader,
			textbox: textbox
		};
	};
	this.init = function(){
		this.dom = $(o.container);
		this.parent = o.parent || null;
	};
	this.init();
});

appdb.repository.commandDispatcher = appdb.ExtendClass(appdb.View,"appdb.repository.commandDispatcher", function(o){
	this.options = {
		params: o.params || {},
		data: o.data || {},
		url: o.url || "dispatch/index"
	};
	this.dispatch = function(){
		var _url = appdb.config.repository.endpoint.base + this.options.url;
		var _data = $.extend(this.options.data,{});
		_data.type = this.options.params.type;
		$.ajax({
			url: _url, 
			type: "POST",
			data: _data,
			success: (function(self){
				return function(d){
					self.publish({event: "load", value: d});
				};
			})(this),
			error: (function(self){
				return function(d){
					self.publish({event: "error", value: d});
				};
			})(this)
		});
	};
});
appdb.repository.dispatcher = {};
appdb.repository.dispatcher.Publish = appdb.ExtendClass(appdb.repository.commandDispatcher, "appdb.repository.dispatcher.Publish", function(o){
	this.options = {
		params: o.params || {"type": "candidate"},
		data: o.data || {},
		url: o.url || "dispatch/publish"
	};
});
appdb.repository.dispatcher.Unpublish = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.Unpublish", function(o){
	this.options = {
		params: o.params || {"type": "candidate"},
		data: o.data || {},
		url: o.url || "dispatch/unpublish"
	};
});
appdb.repository.dispatcher.BuildRepositories = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.BuildRepositories", function(o){
	this.options = {
		params: {},
		data: o.data || {},
		url: o.url || "dispatch/buildrepositories"
	};
});
appdb.repository.dispatcher.BuildRepofiles = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.BuildRepofiles", function(o){
	this.options = {
		params: {},
		data: o.data || {},
		url: o.url || "dispatch/buildrepofiles"
	};
});
appdb.repository.dispatcher.RenameReleaseVersion = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.RenameReleaseVersion", function(o){
	this.options = {
		params: {"type": "release"},
		data: o.data || {},
		url: o.url || "dispatch/rename"
	};
});
appdb.repository.dispatcher.RenameSeriesName = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.RenameSeriesName", function(o){
	this.options = {
		params: {"type": "series"},
		data: o.data || {},
		url: o.url || "dispatch/rename"
	};
});
appdb.repository.dispatcher.RenameSeriesName = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.RenameSeriesName", function(o){
	this.options = {
		params: {"type": "series"},
		data: o.data || {},
		url: o.url || "dispatch/rename"
	};
});
appdb.repository.dispatcher.RemovePackages = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.RemovePackages", function(o){
	this.options = {
		params: {"type": "remove"},
		data: o.data || {},
		url: o.url || "dispatch/packages"
	};
});
appdb.repository.dispatcher.MarkMetaPackages = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.MarkMetaPackages", function(o){
	this.options = {
		params: {"type": "mark"},
		data: o.data || {},
		url: o.url || "dispatch/packages"
	};
});
appdb.repository.dispatcher.UnmarkMetaPackages = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.UnmarkMetaPackages", function(o){
	this.options = {
		params: {"type": "unmark"},
		data: o.data || {},
		url: o.url || "dispatch/packages"
	};
});
appdb.repository.dispatcher.RemoveRelease = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.RemoveRelease", function(o){
	this.options = {
		params: {"type": "release"},
		data: o.data || {},
		url: o.url || "dispatch/remove"
	};
});
appdb.repository.dispatcher.RemoveSeries = appdb.ExtendClass(appdb.repository.commandDispatcher,"appdb.repository.dispatcher.RemoveSeries", function(o){
	this.options = {
		params: {"type": "series"},
		data: o.data || {},
		url: o.url || "dispatch/remove"
	};
});

appdb.repository.ui.DataPageView = appdb.ExtendClass(appdb.View,"appdb.repository.ui.datapage.Page", function(o){
	this.options = {
		dom : $(document.createElement("div")).addClass("datapage"),
		parent: o.parent || null,
		name: o.name,
		data: o.data || {},
		header: o.header || "Select action",
		footer: (o.footer)?$(o.footer):"",
		contents: o.contents || null,
		actions: o.actions || null,
		isRendered: false
	};
	this.isRendered = function(){
		return this.options.isRendered;
	};
	this.reset = function(){
		for(var i in this.subviews){
			if( this.subviews.hasOwnProperty(i) ){
				this.subviews[i].unsubscribeAll(this);
				this.subviews[i].reset();
				this.subviews[i] = null;
			}
		}
		this.subviews = {};
		this.unsubscribeAll();
		$(this.dom).empty();
	};
	this.validate = function(){
		var isValid = false;
		if( this.onValidation ){
			isValid = this.onValidation();
		}
		this.publish({event: "validation", value: isValid});
	};
	this.initContainer = function(){
		var header = $(document.createElement("div")).addClass("header").append(this.options.header);
		var contents = $(document.createElement("div")).addClass("contents").append(o.contents || $(document.createElement("div")).addClass("main"));
		$(this.options.dom).empty().append(header).append(contents);
		$(this.dom).empty().append(this.options.dom);
	};
	this.render = function(){
		this.reset();
		
		if(this.onrender){
			this.onrender();
			this.options.isRendered = true;
		}
		this.validate();
	};
	this.display = function(){
		this.parent.setHeader(this.options.header);
		this.parent.setFooter(this.options.footer);
		if( this.ondisplay){
			this.ondisplay();
		}
	};
	this._init = function(){
		this.dom = $(o.container);
		this.parent = o.parent;
		this.initContainer();
	};
	this._init();
});

appdb.repository.ui.datapage = {};
appdb.repository.ui.datapage.ExecuteCommand = appdb.ExtendClass(appdb.repository.ui.DataPageView,"appdb.repository.ui.datapage.ConfirmCommand",function(o){
	this.options  = $.extend(this.options,{
		header: o.header || "<span/>",
		dispatcher: o.dispatcher || "",
		map: o.map || {},
		params: o.params || {},
		text: o.text || "executing action...",
		events: o.events || {
			onSuccess: function(){},
			onError: function(){}
		}
	});
	this.onrender = function(){
		$(this.dom).empty().append("<div class='main'></div>");
	};
	this.normalizeResponse = function(d){
		//In case of many entries take the first error entry else the first entry
		if(d && d.entry){
			if( $.isArray(d.entry) && d.entry.length > 0){
				var found = false;
				$.each(d.entry, function(i,e){
					if(found === false && e.status && $.trim(e.status).toLowerCase() === "error"){
						found = e;
					}
				});
				if( found !== false ){
					d.entry = found;
				}else{
					d.entry = d.entry[0];
				}
			}
		}
		return d;
	};
	this.onError = function(d){
		appdb.debug("[Execute Command Error]");
		appdb.debug(d);
		this.renderError(d);
		this.options.events.onError(d,this.parent);
	};
	this.onSuccess = function(d){
		appdb.debug("[Execute Command Success]");
		appdb.debug(d);
		this.options.events.onSuccess(d,this.parent);
	};
	this.getMappedData = function(d){
		var res = {};
		for(var i in this.options.map){
			if( this.options.map.hasOwnProperty(i) === false )continue;
			var mapped = appdb.repository.utils.FindData(d, this.options.map[i]);
			res[i] = mapped || i;
		}
		return res;
	};
	this.renderError = function(d){
		$(this.dom).find(".main .executing > img").attr("src","/images/repository/warning.png");
		$(this.dom).find(".main .executing > span").empty().append(d);
	};
	this.dispatch = function(){
		var d = this.parent.getData();
		var map = this.getMappedData(d);
		var dispatcherClass = appdb.FindNS(this.options.dispatcher,false);
		if( !dispatcherClass ){
			appdb.debug("Could not load dispatcher class " + this.options.dispatcher);
			return;
		}
		var dispatcher = new dispatcherClass({parent: this, params: this.options.params, data: map});
		dispatcher.subscribe({event: "load", callback: function(v){
				var d = appdb.utils.convert.toObject(v);
				d = this.normalizeResponse(d);
				if( d && d.entry && $.trim(d.entry.status).toLowerCase() === "success"){
					this.onSuccess(d);
					this.parent.cancel();
				}else if( d && d.entry && $.trim(d.entry.message) !== "" ){
					this.parent.displayActions(true);
					this.onError(d.entry.message);
				}else{
					this.parent.displayActions(true);
					this.onError("Action failed. Unknown error.");
				}
		}, caller: this});
		dispatcher.subscribe({event: "error", callback: function(v){
				this.parent.displayActions(true);
				this.onError(v);
		},caller: this});
		dispatcher.dispatch();
	};
	this.ondisplay = function(){
		var html = "<div class='executing'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>"+this.options.text+"</span></div>";
		$(this.dom).find(".main").empty().append(html);
		this.parent.displayActions(false);
		this.dispatch();
	};
	
});
appdb.repository.ui.datapage.TextInput = appdb.ExtendClass(appdb.repository.ui.DataPageView, "appdb.repository.ui.datapage.TextInput", function(o){
	this.options = $.extend(this.options,{
		header: o.header || "Provide value bellow:",
		commanddisplay: o.commanddisplay || "",
		map: o.map || {},
		url: o.url || "",
		data: {
			from: "",
			to: ""
		}
	});
	this.onValidation = function(){
		var input = $.trim($(this.dom).find(".main input").val());
		if( input === "" || input === this.options.data.from) return false;
		return true;
	};
	this.processValue = function(){
		var input = $.trim($(this.dom).find(".main input").val());
		this.options.data.to = input;
		this.validate();
	};
	this.onrender = function(){
			var main = $(this.options.dom).find(".main");
			this.parent.setHeader(this.options.header);
			var div = $(document.createElement("div")).addClass("textinput").addClass("fiedvalue");
			var span = $(document.createElement("span")).addClass("textinput").addClass("value");
			var input = $(document.createElement("input")).attr("type", "text").attr("maxlength", 20);
			$(span).append(input);
			$(div).append(span);
			$(main).append(div);
			
			$(input).val(this.options.data.to);
			$(input).off("change").on("change", (function(self){
				return function(ev){
					self.processValue();
				};
			})(this)).off("keyup").on("keyup",(function(self){
				return function(ev){
					self.processValue();
				};
			})(this)).off("mouseup").on("mouseup",(function(self){
				return function(ev){
					self.processValue();
				};
			})(this));
			$(this.dom).append(main);
	};
	this.ondisplay = function(){
		var d = this.parent.getData();
		if( this.options.map["from"] ){
			this.options.data.from = appdb.repository.utils.FindData(d,this.options.map["from"]);
		}
		this.options.data.to = $(this.dom).find(".main input").val();
	};
});
appdb.repository.ui.datapage.ConfirmCommand = appdb.ExtendClass(appdb.repository.ui.DataPageView,"appdb.repository.ui.datapage.ConfirmCommand", function(o){
	this.options = $.extend(this.options,{
		header: o.header || "Please confirm action",
		commanddisplay: o.commanddisplay || ""
	});
	this.onValidation = function(){
		return true;
	};
	this.getSeriesName = function(){
		var d = this.parent.getData();
		if( d && d.series ){
			return d.series.name;
		}
		return "-";
	};
	this.getReleaseName = function(){
		var d = this.parent.getData();
		if( d && d.release ){
			return d.release.displayversion;
		}
		return "-";
	};
	this.onrender = function(){
			var main = $(this.options.dom).find(".main");
			this.parent.setHeader(this.options.header);
			$(this.dom).append(main);
	};
	this.ondisplay = function(){
		var html = "<div class='series fieldvalue'><div class='field'>Series:</div><div class='value'>"+this.getSeriesName()+"</div></div><div class='release fieldvalue'><div class='field'>Release:</div><div class='value'>"+this.getReleaseName()+"</div></div>";
		$(this.dom).find(".main").empty().append(html);
		var footer = $(document.createElement("div")).addClass("footer");
		var nexttext = "next";
		if( this.options.actions ){
			$.each(this.options.actions, function(i,e){
				if(e.type === "next" && e.display){
					nexttext = e.display;
				}
			});
		}
		$(footer).empty().append("<div>Click <b>'" + nexttext + "'</b> to proceed, or</div><div>click  <b>'cancel'</b> to close the dialog.</div>");
		$(this.dom).find(".main .footer").remove();
		$(this.dom).find(".main").append(footer);
	};
});
appdb.repository.ui.datapage.ConfirmData = appdb.ExtendClass(appdb.repository.ui.DataPageView,"appdb.repository.ui.datapage.ConfirmData", function(o){
	this.options = $.extend(this.options,{
		header: o.header || "Please confirm action",
		commanddisplay: o.commanddisplay || "",
		datanames: o.datanames || {}
	});
	this.onValidation = function(){
		return true;
	};
	
	this.onrender = function(){
			var main = $(this.options.dom).find(".main");
			this.parent.setHeader(this.options.header);
			$(this.dom).append(main);
	};
	this.getFieldHtml = function(name,data){
		var dataname = name;
		var val = "";
		for(var i in this.options.datanames){
			if( this.options.datanames.hasOwnProperty(i) ){
				if( i == name ){
					if( this.options.datanames[i].name ){
						dataname = this.options.datanames[i].name;
					}else{
						dataname = this.options.datanames[i];
					}
					
					if( this.options.datanames[i].value ){
						val = appdb.repository.utils.FindData(data, this.options.datanames[i].value) || data[i] || "";
					}else{
						val = data[i];
					}
				}
			}
		}
		return "<div class='" + name + " fieldvalue'><div class='field'>"+dataname+":</div><div class='value'>"+val+"</div></div>";
	};
	this.getFieldListHtml = function(){
		var html = "";
		var data = this.parent.getData();
		for(var i in this.options.datanames){
			if( this.options.datanames.hasOwnProperty(i) ){
				html += this.getFieldHtml(i, data);
			}
		}
		return html;
	};
	this.ondisplay = function(){
		var html = this.getFieldListHtml();
		$(this.dom).find(".main").empty().append(html);
		var footer = $(document.createElement("div")).addClass("footer");
		var nexttext = "next";
		if( this.options.actions ){
			$.each(this.options.actions, function(i,e){
				if(e.type === "next" && e.display){
					nexttext = e.display;
				}
			});
		}
		$(footer).empty().append("<div>Click <b>'" + nexttext + "'</b> to proceed, or click  <b>'cancel'</b> to close the dialog.</div>");
		$(this.dom).find(".main .footer").remove();
		$(this.dom).find(".main").append(footer);
	};
});
appdb.repository.ui.datapage.SelectSeries = appdb.ExtendClass(appdb.repository.ui.DataPageView, "appdb.repository.ui.datapage.SelectSeries",function(o){
	this.options = $.extend(this.options,{
			header : o.header || "Please select a release from a series",
			contents: o.contents || null,
			data: o.data || {series: null},
			excludestates: o.excludestates || null,
			filterdata: o.filterData || null
		});
	this.onValidation = function(){
			return (this.options.data && this.options.data.series)?true:false;		
	};
	this.renderSeriesItem = function(d){
		var div = $(document.createElement("div")).addClass("seriesitem");
		var span = $(document.createElement("span"));
		$(span).text(d.name);
		$(div).append(span);
		return div;
	};
	this.getValidReleases = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var res = [];
		if( $.isArray(this.options.excludestates) && this.options.excludestates.length > 0){
			$.each(d, (function(exclude){
				return function(i,e){
					var state = (e && e.state)?$.trim(e.state.name).toLowerCase():"";
					if( $.inArray(state, exclude) === -1 ){
						res.push(e);
					}
				};
			})(this.options.excludestates));
		}else{
			return d;
		}
		return res;
	};
	this.getValidSeries = function(data){
		var res = [];
		$.each(data, (function(self){
			return function(i,e){
				var data = self.getValidReleases(e.productrelease);
				if( $.isArray(data) && data.length>0){
					res.push(e);
				}
			};
		})(this));
		if( typeof this.options.filterdata === "function" ){
			res = this.options.filterdata(res);
		}
		return res;
	};
	this.onrender = function(){
		var main = $(this.options.dom).find(".main");
		var seriesdom = $(document.createElement("div")).addClass("serieslist").addClass("selecttarget");
		$(main).append(seriesdom);
		
		this.subviews.serieslist = new appdb.repository.ui.views.DropDownList({
			container: $(seriesdom),
			parent: this,
			title: "Please select a series",
			onRenderItem: (function(self){
				return function(d){
					return self.renderSeriesItem(d);
				};
			})(this)
		});
		
		this.subviews.serieslist.subscribe({event: "change", callback: function(v){
				if( v && v.id){
					this.options.data.series = v;
				}else{
					this.options.data.series = null;
					this.options.data.release = null;
				}
				this.validate();
		}, caller: this});
		var data = this.parent.getDataSource("releasetree");
		data = this.getValidSeries(data);
		this.subviews.serieslist.render(data);
		$(this.dom).append(main);
		return true;
	};
	this.ondisplay = function(){
		this.validate();
	};
});
appdb.repository.ui.datapage.SelectRelease = appdb.ExtendClass(appdb.repository.ui.DataPageView, "appdb.repository.ui.datapage.SelectRelease",function(o){
	this.options = $.extend(this.options,{
			header : o.header || "Please select a release from a series",
			contents: o.contents || null,
			data: {release: null},
			excludestates: o.excludestates || null,
			filterdata: o.filterData || null
		});
	this.onValidation = function(){
			return (this.options.data && this.options.data.release)?true:false;		
	};
	
	this.renderReleaseItem = function(d, repo){
		var div = $(document.createElement("div")).addClass("releaseitem");
		var span = $(document.createElement("span"));
		$(span).text(d.displayversion);
		$(div).append(span);
		return div;
	};
	this.getValidReleases = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var res = [];
		if( $.isArray(this.options.excludestates) && this.options.excludestates.length > 0){
			$.each(d, (function(exclude){
				return function(i,e){
					var state = (e && e.state)?$.trim(e.state.name).toLowerCase():"";
					if( $.inArray(state, exclude) === -1 ){
						res.push(e);
					}
				};
			})(this.options.excludestates));
		}else{
			return d;
		}
		return res;
	};
	this.onrender = function(){
		var main = $(this.options.dom).find(".main");
		var releasedom = $(document.createElement("div")).addClass("releaselist").addClass("selecttarget");
		
		$(main).append(releasedom);
		
		this.subviews.releaselist = new appdb.repository.ui.views.DropDownList({
			container: $(releasedom),
			parent: this,
			title: "Please select a release",
			onRenderItem: (function(self){ 
				return function(d){
					return self.renderReleaseItem(d);
				};
			})(this)
		});
		
		this.subviews.releaselist.subscribe({event: "change", callback: function(v){
				if( v && v.id ){
					this.options.data.release = v;
				}else{
					this.options.data.release = null;
				}
				this.validate();
		}, caller: this});
		
		$(this.dom).append(main);
		this.renderList();
		
		return true;
	};
	this.renderList = function(){
		var data = this.parent.getData();
		if( !data || !data.series || !data.series.productrelease){
			this.subviews.releaselist.render([]);
			return;
		}
		data = this.getValidReleases(data.series.productrelease);
		if( typeof this.options.filterdata === "function"){
			data = this.options.filterdata(data);
		}
		
		this.subviews.releaselist.render(data);
	};
	this.ondisplay = function(){
		var d = this.parent.getData();
		if( d.series && d.series.productrelease && this.options.data.release && this.options.data.release.id){
			var rid = this.options.data.release.id;
			d.series.productrelease = $.isArray(d.series.productrelease)?d.series.productrelease:[d.series.productrelease];
			var found = false;
			$.each(d.series.productrelease, function(i,e){
				if( e.id == rid){
					found = true;
				}
			});
			if( found === false ){
				this.renderList();
			}
		}else if(!d.series || !this.options.data || !this.options.data.release ){
			this.renderList();
		}
		this.validate();
	};
});
appdb.repository.ui.datapage.SelectSeriesRelease = appdb.ExtendClass(appdb.repository.ui.DataPageView, "appdb.repository.ui.datapage.SelectSeriesRelease",function(o){
	this.options = $.extend(this.options,{
			header : o.header || "Please select a release from a series",
			contents: o.contents || null,
			data: {series: null, release: null},
			excludestates: o.excludestates || null
		});
	this.onValidation = function(){
			return (this.options.data && this.options.data.series && this.options.data.release)?true:false;		
	};
	this.renderSeriesItem = function(d){
		var div = $(document.createElement("div")).addClass("seriesitem");
		var span = $(document.createElement("span"));
		$(span).text(d.name);
		$(div).append(span);
		return div;
	};
	this.renderReleaseItem = function(d, repo){
		var div = $(document.createElement("div")).addClass("releaseitem");
		var span = $(document.createElement("span"));
		$(span).text(d.displayversion);
		$(div).append(span);
		return div;
	};
	this.getValidReleases = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var res = [];
		if( $.isArray(this.options.excludestates) && this.options.excludestates.length > 0){
			$.each(d, (function(exclude){
				return function(i,e){
					var state = (e && e.state)?$.trim(e.state.name).toLowerCase():"";
					if( $.inArray(state, exclude) === -1 ){
						res.push(e);
					}
				};
			})(this.options.excludestates));
		}else{
			return d;
		}
		return res;
	};
	this.getValidSeries = function(data){
		var res = [];
		$.each(data, (function(self){
			return function(i,e){
				var data = self.getValidReleases(e.productrelease);
				if( $.isArray(data) && data.length>0){
					res.push(e);
				}
			};
		})(this));
		return res;
	};
	this.onrender = function(){
		var main = $(this.options.dom).find(".main");
		var seriesdom = $(document.createElement("div")).addClass("serieslist").addClass("selecttarget");
		var releasedom = $(document.createElement("div")).addClass("releaselist").addClass("selecttarget");
		
		$(main).append(seriesdom).append(releasedom);
		
		this.subviews.serieslist = new appdb.repository.ui.views.DropDownList({
			container: $(seriesdom),
			parent: this,
			title: "Please select a series",
			onRenderItem: (function(self){
				return function(d){
					return self.renderSeriesItem(d);
				};
			})(this)
		});
		this.subviews.releaselist = new appdb.repository.ui.views.DropDownList({
			container: $(releasedom),
			parent: this,
			title: "Please select a release",
			onRenderItem: (function(self){ 
				return function(d){
					return self.renderReleaseItem(d);
				};
			})(this)
		});
		
		this.subviews.serieslist.subscribe({event: "change", callback: function(v){
				if( v && v.id){
					this.options.data.series = v;
					this.subviews.releaselist.render(this.getValidReleases(v.productrelease));
				}else{
					this.options.data.series = null;
					this.options.data.release = null;
					this.subviews.releaselist.render([]);
				}
				this.validate();
		}, caller: this});
		this.subviews.releaselist.subscribe({event: "change", callback: function(v){
				if( v && v.id ){
					this.options.data.release = v;
				}else{
					this.options.data.release = null;
				}
				this.validate();
		}, caller: this});
		var data = this.parent.getDataSource("releasetree");
		data = this.getValidSeries(data);
		this.subviews.serieslist.render(data);
		this.subviews.releaselist.render([]);
		
		$(main).find(".dropdownlist").off("click.hideothers").on("click.hideothers", function(ev){
			$(this).siblings(".dropdownlist").removeClass("expand");
		});
		$(this.dom).append(main);
		return true;
	};
});

appdb.repository.ui.CommandView = appdb.ExtendClass(appdb.View,"appdb.repository.ui.CommandView", function(o){
	this.options = {
		dom : o.container || null,
		parent: o.parent || null,
		action: o.name || "",
		type: o.type || "",
		title: o.title || "Action",
		dialog : null,
		useDialog: true,
		data: {},
		pages:[],
		session: {
			currentIndex: 0,
			length: 0
		},
		actions: [],
		currentPage: o.currentPage,
		hasPrevious: (typeof o.hasPrevious === "boolean")?o.hasPrevious:true,
		datasource: o.datasource || function(){return null;}
	};
	this.displayActions = function(display){
		display = (typeof display === "boolean")?display:false;
		if( display === true){
			$(this.dom).find(".actions").removeClass("hidden");
		}else {
			$(this.dom).find(".actions").addClass("hidden");
		}
	};
	this.getData = function(){
		this.collectData();
		return this.options.data;
	};
	this.collectData = function(){
		var data = this.options.data || {};
		$.each(this.subviews, function(i,e){
			if( e.options.data ){
				data = $.extend(data, e.options.data);
			}
		});
		this.options.data = data;
	};
	this.setData = function(d){
		var data = d || {};
		$.each(this.subviews, function(i, e){
			if( e.options ){
				e.options.data = $.extend({},data);
			}
		});
		this.options.data = data;
	};
	this.getDataSource = function(type){
		if( this.options.datasource ){
			return this.options.datasource.getDataSource(type);
		}
		return null;
	};
	this.reset = function(){
		this.options.session = {
			currentIndex: 0,
			length: this.subviews.length
		};
		$.each(this.subviews, function(i,e){
			e.reset();
		});
		$(this.dom).removeClass("hidden");
	};
	this.getCurrentPage = function(){
		return this.subviews[this.options.session.currentIndex];
	};
	this.hideAllPages = function(){
		$.each(this.subviews, function(i,e){
			$(e.dom).addClass("hidden");
		});
	};
	this.setHeader = function(d){
		$(this.dom).children(".header").remove();
		if( d ) {
			var header = $(document.createElement("div")).addClass("header");
			$(header).append(d);
			$(this.dom).prepend(header);
		}
	};
	this.setFooter = function(d){
		$(this.dom).children(".footer").remove();
		if( d ){
			var footer = $(document.createElement("div")).addClass("footer");
			$(footer).append(d);
			$(this.dom).find(".actions").before(footer);
		}
	};
	this.renderCurrentPage = function(page){
		this.hideAllPages();
		var cp = page || this.getCurrentPage();
		if(cp){
			$(cp.dom).removeClass("hidden");
			if( cp.isRendered() == false ){
				cp.render();
			}
			this.renderActions();
			cp.display();
			cp.validate();
		}
		
	};
	this.next = function(){
		if( this.options.session.currentIndex < this.options.session.length){
			this.options.session.currentIndex += 1;
		}
		this.collectData();
		this.renderCurrentPage();
	};
	this.previous = function(){
		if( this.options.session.currentIndex >0){
			this.options.session.currentIndex -= 1;
		}
		this.renderCurrentPage();
	};
	this.cancel = function(){
		this.reset();
		if( this.options.useDialog ) {
			this.options.dialog.hide();
			this.options.dialog.destroyRecursive(false);
			this.options.dialog = null;
		}
		$(this.dom).addClass("hidden");
	};
	this.getIndexOfPage = function(pagename){
		pagename = $.trim(pagename).toLowerCase();
		if( pagename === "" ){
			return 0;
		}
		var cindex = -1;
		$.each(this.subviews, function(i,e){
			if( cindex < 0 && e.options && $.trim(e.options.name).toLowerCase() === pagename){
				cindex = i;
			}
		});
		return (cindex<0)?0:cindex;
	};
	this.renderActions = function(){
		$(this.dom).find(".actions").remove();
		var page = this.getCurrentPage();
		var actions =$(document.createElement("div")).addClass("actions");
		var next = $(document.createElement("button")).addClass("action").addClass("next").text("next");
		var previous = $(document.createElement("button")).addClass("action").addClass("previous").text("previous");
		var cancel = $(document.createElement("button")).addClass("action").addClass("cancel").text("cancel");
		$(actions).append(previous).append(next).append(cancel);
		$(previous).off("click").on("click", (function(self) {
			return function(ev){
				ev.preventDefault();
				self.previous();
				return false;
			};
		})(this));
		$(next).off("click").on("click", (function(self) {
			return function(ev){
				ev.preventDefault();
				self.next();
				return false;
			};
		})(this));
		$(cancel).off("click").on("click", (function(self) {
			return function(ev){
				ev.preventDefault();
				self.cancel();
				return false;
			};
		})(this));
		if( page.options.actions ){
			$.each(page.options.actions, function(i,e){
				if(e.display){
					$(actions).find(".action." + e.type).text(e.display);
				}
			});
		}
		$(this.dom).append(actions);
		if( this.options.session.currentIndex <= 0 ){
			$(this.dom).find(".actions .action.previous").addClass("hidden");
		}else{
			$(this.dom).find(".actions .action.previous").removeClass("hidden");
		}
		if( this.options.hasPrevious === true ){
			$(previous).removeClass("hidden");
		}else{
			$(previous).addClass("hidden");
		}
		
		if( this.options.session.currentIndex == this.options.session.length ){
			$(this.dom).find(".actions .action.next").addClass("hidden");
		}else{
			$(this.dom).find(".actions .action.next").removeClass("hidden");			
		}
	};
	this.isValidPage = function(v){
		if( v === true ){
			$(this.dom).find(".actions .action.next").prop("disabled", false);
		}else{
			$(this.dom).find(".actions .action.next").prop("disabled", true);
		}
	};
	this.render = function(d){
		this.reset();
		$.each(this.options.pages, (function(self){
			return function(i,e){
				if(! appdb.repository.ui.datapage[e.datapage]) {appdb.debug("could not retrieve class " + "appdb.repository.ui.datapage." + e.datapage);return;}
				$(self.dom).append($(document.createElement("div")).addClass("datapageview").addClass(e.datapage.toLowerCase()));
				var options = e;
				options.container = $(self.dom).find(".datapageview." + e.datapage.toLowerCase());
				options.parent = self;
				options.name = e.datapage;
				var view = new appdb.repository.ui.datapage[e.datapage](options);
				view.subscribe({event: "validation", callback: function(v){
						self.isValidPage(v);
				},caller: self});
				self.subviews.push(view);
				return;
			};
		})(this));
		this.options.session.length = this.subviews.length;
		this.displayActions(true);
		this.options.session.currentIndex = this.getIndexOfPage(this.options.currentPage);
		if( typeof d === "object" && $.isEmptyObject(d) === false ){
			this.setData(d);
		}
		this.renderCurrentPage();
		this.show();
	};
	this.show = function(){
		if( this.options.useDialog ){
			if( this.options.dialog ){
				dialog.destroyRecursive(false);
				dialog = null;
			}
			this.options.dialog = new dijit.Dialog({
				title: this.options.title,
				content: $(this.dom)[0],
				style: o.css || "overflow:visible;max-width:500px;max-height:400px;"
			});
			this.options.dialog.show();
		}
	};
	this._preInit = function(){
		if( this.options.useDialog ){
			this.dom = $(document.createElement("div")).addClass("commanddialog");
		}
		this.parent = o.parent || null;
		this.subviews = [];
	};
	this._preInit();
});

appdb.repository.ui.command = {};
appdb.repository.ui.command.publish = {};
appdb.repository.ui.command.publish.candidate = appdb.ExtendClass(appdb.repository.ui.CommandView, "appdb.repository.ui.command.publish.candidate", function(o){
	this.options = $.extend(this.options,{
		name: "publish",
		type: "candidate",
		title: "Publish as candidate",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.publish.candidate.excludedStates.series
		},{
			header: "Please select a release to publish as a candidate: ",
			datapage: "SelectRelease",
			excludestates : appdb.repository.ui.command.publish.candidate.excludedStates.relase
		},{
			datapage: "ConfirmCommand",
			header: "You are about to publish the release bellow as a candidate.",
			actions: [
				{type:"next",display:"publish"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.Publish",
			params: {"type": "candidate"},
			actions: [
				{type:"previous",display:"previous"}
			],
			map: {
				"id": "release.id"
			},
			text: "Please wait while publishing release as a candidate...",
			events: {
				onSuccess: function(d){
					if( d.releaseid ){
						appdb.repository.ui.CurrentReleaseManager.updateReleaseDataProperty(d.releaseid, "state", {name: "candidate", id: 3});
						if( d.lastproductionbuild ){
							appdb.repository.ui.CurrentReleaseManager.updateReleaseDataProperty(d.releaseid, "lastproductionbuild",d.lastproductionbuild);
							var repo = appdb.repository.ui.CurrentReleaseManager.getSeriesByReleaseId(d.releaseid);
							if( repo ){
								appdb.repository.ui.CurrentReleaseManager.updateRepositoryDataProperty(repo.id, "lastproductionbuild",d.lastproductionbuild);
							}
						}
                        if( appdb.repository.ui.CurrentReleaseManager.views.releaseDetails &&
                            appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.options.data &&
                            appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.options.data.id == d.releaseid ){
                           appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.onPublish(d,"candidate");
                        }else{
                            appdb.repository.ui.CurrentReleaseManager.showRelease(d.releaseid);
                        }
					}
				},
				onError: function(d,parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			}
		}]
	});
},{
	excludedStates: {
		series: ["candidate","production"],
		release: ["candidate","production"]
	},
	canPerformForData : function(data){
		data = data || {};
		var release = data.release || null;
		if( release === null ) return false;
		var state = (release && release.state)?$.trim(release.state.name).toLowerCase():"";
		if( $.isArray(this.excludedStates.release) && this.excludedStates.release.length > 0){
			if( $.inArray(state, this.excludedStates.release) > -1 ){
				return false;
			};
		}
		return true;
	}
});
appdb.repository.ui.command.publish.production = appdb.ExtendClass(appdb.repository.ui.CommandView, "appdb.repository.ui.command.publish.production", function(o){
	this.options = $.extend(this.options,{
		name: "publish",
		type: "production",
		title: "Publish into production",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.publish.production.excludedStates.series
		},{
			header: "Please select a release to publish into production: ",
			datapage: "SelectRelease",
			excludestates : appdb.repository.ui.command.publish.production.excludedStates.release
		},{
			datapage: "ConfirmCommand",
			header: "You are about to publish the release bellow into production.",
			actions: [
				{type:"next",display:"publish"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.Publish",
			params: {"type": "production"},
			map: {
				"id": "release.id"
			},
			text: "Please wait while publishing release into production...",
			events: {
				onSuccess: function(d){
					if( d.releaseid ){
						appdb.repository.ui.CurrentReleaseManager.updateReleaseDataProperty(d.releaseid, "state", {name: "production", id: 2});
						if( d.lastproductionbuild ){
							appdb.repository.ui.CurrentReleaseManager.updateReleaseDataProperty(d.releaseid, "lastproductionbuild",d.lastproductionbuild);
							var repo = appdb.repository.ui.CurrentReleaseManager.getSeriesByReleaseId(d.releaseid);
							if( repo ){
								appdb.repository.ui.CurrentReleaseManager.updateRepositoryDataProperty(repo.id, "lastproductionbuild",d.lastproductionbuild);
							}
						}
                        if( appdb.repository.ui.CurrentReleaseManager.views.releaseDetails &&
                            appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.options.data &&
                            appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.options.data.id == d.releaseid ){
                           appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.onPublish(d,"production");
                        }else{
                            appdb.repository.ui.CurrentReleaseManager.showRelease(d.releaseid);
                        }
					}
				},
				onError: function(d, parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			},
			actions: null
		}]
	});
},{
	excludedStates: {
		series: ["production"],
		release: ["production"]
	},
	canPerformForData : function(data){
		data = data || {};
		var release = data.release || null;
		if( release === null ) return false;
		var state = (release && release.state)?$.trim(release.state.name).toLowerCase():"";
		if( $.isArray(this.excludedStates.release) && this.excludedStates.release.length > 0){
			if( $.inArray(state, this.excludedStates.release) > -1 ){
				return false;
			};
		}
		return true;
	}
});

appdb.repository.ui.command.unpublish = {};
appdb.repository.ui.command.unpublish.candidate = appdb.ExtendClass(appdb.repository.ui.CommandView,"appdb.repository.ui.command.unpublish.candidate", function(o){
	this.options = $.extend(this.options,{
		name: "unpublish",
		type: "candidate",
		title: "Un-Publish a candidate",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.unpublish.candidate.excludedStates.series
		},{
			header: "Please select a candidate release to unpublish it: ",
			datapage: "SelectRelease",
			excludestates : appdb.repository.ui.command.unpublish.candidate.excludedStates.release
		},{
			datapage: "ConfirmCommand",
			header: "You are about to unpublish the release bellow from candidate.",
			actions: [
				{type:"next",display:"unpublish"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.Unpublish",
			params: {"type": "candidate"},
			map: {
				"id": "release.id"
			},
			text: "Please wait while removing candidate repository of release...",
			events: {
				onSuccess: function(d){
					if( d.releaseid ){
						appdb.repository.ui.CurrentReleaseManager.updateReleaseDataProperty(d.releaseid, "state", {name: "unverified", id: 1});
						if( appdb.repository.ui.CurrentReleaseManager.views.releaseDetails &&
                            appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.options.data &&
                            appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.options.data.id == d.releaseid ){
                           appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.onUnpublish(d,"candidate");
                        }else{
                            appdb.repository.ui.CurrentReleaseManager.showRelease(d.releaseid);
                        }
					}
				},
				onError: function(d, parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			},
			actions: null
		}]
	});
},{
	excludedStates: {
		series: ["unverified","production"],
		release: ["unverified","production"]
	},
	canPerformForData : function(data){
		data = data || {};
		var release = data.release || null;
		if( release === null ) return false;
		var state = (release && release.state)?$.trim(release.state.name).toLowerCase():"";
		if( $.isArray(this.excludedStates.release) && this.excludedStates.release.length > 0){
			if( $.inArray(state, this.excludedStates.release) > -1 ){
				return false;
			};
		}
		return true;
	}
});
appdb.repository.ui.command.unpublish.production = appdb.ExtendClass(appdb.repository.ui.CommandView,"appdb.repository.ui.command.unpublish.production", function(o){
	this.options = $.extend(this.options,{
		name: "unpublish",
		type: "production",
		title: "Un-Publish a production release",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.unpublish.production.excludedStates.series
		},{
			header: "Please select a candidate release to unpublish it: ",
			datapage: "SelectRelease",
			excludestates : appdb.repository.ui.command.unpublish.production.excludedStates.release
		},{
			datapage: "ConfirmCommand",
			header: "You are about to unpublish the release bellow from production.",
			actions: [
				{type:"next",display:"unpublish"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.Unpublish",
			params: {"type": "production"},
			map: {
				"id": "release.id"
			},
			text: "Please wait while removing production repository of release...",
			events: {
				onSuccess: function(d){
					if( d.releaseid ){
						appdb.repository.ui.CurrentReleaseManager.updateReleaseDataProperty(d.releaseid, "state", {name: "unverified", id: 1});
                        if( appdb.repository.ui.CurrentReleaseManager.views.releaseDetails &&
                            appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.options.data &&
                            appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.options.data.id == d.releaseid ){
                           appdb.repository.ui.CurrentReleaseManager.views.releaseDetails.onUnpublish(d,"production");
                        }else{
                            appdb.repository.ui.CurrentReleaseManager.showRelease(d.releaseid);
                        }
					}
				},
				onError: function(d, parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			},
			actions: null
		}]
	});
},{
	excludedStates: {
		series: ["unverified","candidate"],
		release: ["unverified","candidate"]
	},
	canPerformForData : function(data){
		data = data || {};
		var release = data.release || null;
		if( release === null ) return false;
		var state = (release && release.state)?$.trim(release.state.name).toLowerCase():"";
		if( $.isArray(this.excludedStates.release) && this.excludedStates.release.length > 0){
			if( $.inArray(state, this.excludedStates.release) > -1 ){
				return false;
			};
		}
		return true;
	}
});

appdb.repository.ui.command.rebuild = {};
appdb.repository.ui.command.rebuild.repositories = appdb.ExtendClass(appdb.repository.ui.CommandView,"appdb.repository.ui.command.rebuild.repositories", function(o){
	this.options = $.extend(this.options,{
		name: "build",
		type: "repositories",
		title: "Rebuild repositories",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.rebuild.repositories.excludedStates.series
		},{
			header: "Please select a release to rebuild its repositories: ",
			datapage: "SelectRelease",
			excludestates : appdb.repository.ui.command.rebuild.repositories.excludedStates.release
		},{
			datapage: "ConfirmCommand",
			header: "You are about to rebuild the repositories of the release bellow:",
			actions: [
				{type:"next",display:"rebuild"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.BuildRepositories",
			map: {
				"id": "release.id"
			},
			text: "Please wait while rebuilding release repositories...",
			events: {
				onSuccess: function(d){
					if( d.releaseid ){
						appdb.repository.ui.CurrentReleaseManager.showRelease(d.releaseid);
					}
				},
				onError: function(d, parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			},
			actions: null
		}]
	});
},{
	excludedStates: {
		series: ["unverified"],
		release: ["unverified"]
	},
	canPerformForData : function(data){
		data = data || {};
		var release = data.release || null;
		if( release === null ) return false;
		var state = (release && release.state)?$.trim(release.state.name).toLowerCase():"";
		if( $.isArray(this.excludedStates.release) && this.excludedStates.release.length > 0){
			if( $.inArray(state, this.excludedStates.release) > -1 ){
				return false;
			};
		}
		return true;
	}
});
appdb.repository.ui.command.rebuild.repofiles = appdb.ExtendClass(appdb.repository.ui.CommandView,"appdb.repository.ui.command.rebuild.repofiles", function(o){
	this.options = $.extend(this.options,{
		name: "build",
		type: "repofiles",
		title: "Rebuild repofiles",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.rebuild.repofiles.excludedStates.series
		},{
			header: "Please select a release to rebuild its repofiles: ",
			datapage: "SelectRelease",
			excludestates : appdb.repository.ui.command.rebuild.repofiles.excludedStates.release
		},{
			datapage: "ConfirmCommand",
			header: "You are about to rebuild the repofiles of the release bellow:",
			actions: [
				{type:"next",display:"rebuild"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.BuildRepofiles",
			map: {
				"id": "release.id"
			},
			text: "Please wait while rebuilding release repofiles...",
			events: {
				onSuccess: function(d){
					if( d.releaseid ){
						appdb.repository.ui.CurrentReleaseManager.showRelease(d.releaseid);
					}
				},
				onError: function(d, parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			},
			actions: null
		}]
	});
},{
	excludedStates: {
		series: ["unverified"],
		release: ["unverified"]
	},
	canPerformForData : function(data){
		data = data || {};
		var release = data.release || null;
		if( release === null ) return false;
		var state = (release && release.state)?$.trim(release.state.name).toLowerCase():"";
		if( $.isArray(this.excludedStates.release) && this.excludedStates.release.length > 0){
			if( $.inArray(state, this.excludedStates.release) > -1 ){
				return false;
			};
		}
		return true;
	}
});

appdb.repository.ui.command.rename = {};
appdb.repository.ui.command.rename.ReleaseVersion = appdb.ExtendClass(appdb.repository.ui.CommandView,"appdb.repository.ui.command.rename.ReleaseVersion", function(o){
	this.options = $.extend(this.options,{
		name: "rename",
		type: "releaseversion",
		title: "Rename release version tag",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.rename.ReleaseVersion.excludedStates.series
		},{
			header: "Please select a release to rename its version tag: ",
			datapage: "SelectRelease",
			excludestates : appdb.repository.ui.command.rename.ReleaseVersion.excludedStates.release
		},{
			header: "Type the new version tag of the release bellow: ",
			map: {"from": "release.displayversion"},
			datapage: "TextInput"
		},{
			datapage: "ConfirmData",
			header: "You are about to rename the release version tag:",
			datanames: {
				"series":{name: "Series", value: "series.name"},
				"release": {name: "Release", value: "release.displayversion"},
				"to": {name: "Rename to", value: "to"}
			},
			footer: "<div class='warning'><img src='/images/repository/warning.png' alt='' /><span>The published repositories in this release will no longer be valid, since they are refered with the old display version!</span></div>",
			actions: [
				{type:"next",display:"rename"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.RenameReleaseVersion",
			map: {
				"id": "release.id",
				"to": "to"
			},
			text: "Please wait while renaming release version tag...",
			events: {
				onSuccess: function(d){
					if( d.releaseid && d.to){
						appdb.repository.ui.CurrentReleaseManager.updateReleaseDataProperty(d.releaseid, "displayversion", d.to);
						appdb.repository.ui.CurrentReleaseManager.showRelease(d.releaseid);
					}
				},
				onError: function(d, parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			},
			actions: null
		}]
	});
},{
	excludedStates: {
		series: [],
		release: []
	},
	canPerformForData : function(data){
		data = data || {};
		var release = data.release || null;
		if( release === null ) return false;
		var state = (release && release.state)?$.trim(release.state.name).toLowerCase():"";
		if( $.isArray(this.excludedStates.release) && this.excludedStates.release.length > 0){
			if( $.inArray(state, this.excludedStates.release) > -1 ){
				return false;
			};
		}
		return true;
	}
});
appdb.repository.ui.command.rename.SeriesName = appdb.ExtendClass(appdb.repository.ui.CommandView,"appdb.repository.ui.command.rename.SeriesName", function(o){
	this.options = $.extend(this.options,{
		name: "rename",
		type: "SeriesName",
		title: "Rename series name tag",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.rename.SeriesName.excludedStates.series
		},{
			header: "Type the new name tag of the series bellow: ",
			map: {"from": "series.name"},
			datapage: "TextInput"
		},{
			datapage: "ConfirmData",
			header: "You are about to rename the release version tag:",
			datanames: {
				"series":{name: "Series", value: "series.name"},
				"to": {name: "Rename to", value: "to"}
			},
			footer: "<div class='warning'><img src='/images/repository/warning.png' alt='' /><span>The published repositories under this series will no longer be valid, since they are refered with the old series name!</span></div>",
			actions: [
				{type:"next",display:"rename"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.RenameSeriesName",
			map: {
				"id": "series.id",
				"to": "to"
			},
			text: "Please wait while renaming series name tag...",
			events: {
				onSuccess: function(d){
					if( d.seriesid && d.to){
						appdb.repository.ui.CurrentReleaseManager.updateRepositoryDataProperty(d.seriesid, "name", d.to);
						appdb.repository.ui.CurrentReleaseManager.showRepositoryArea(d.seriesid);
					}
				},
				onError: function(d, parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			},
			actions: null
		}]
	});
},{
	excludedStates: {
		series: [],
		release: []
	},
	canPerformForData : function(data){
		data = data || {};
		var series = data.series || null;
		if( series === null ) return false;
		return true;
	}
});

appdb.repository.ui.command.remove = {};
appdb.repository.ui.command.remove.release = appdb.ExtendClass(appdb.repository.ui.CommandView,"appdb.repository.ui.command.remove.release", function(o){
	this.options = $.extend(this.options,{
		name: "remove",
		type: "release",
		title: "Remove release",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.remove.release.excludedStates.series,
			filterData: function(d){
				var res = [];
				$.each(d, function(i,e){
					
					if(e.productrelease && $.isArray(e.productrelease) && e.productrelease.length > 0){
						e.productrelease = $.isArray(e.productrelease)?e.productrelease:[e.productrelease];
						var hasreleases = $.grep(e.productrelease, function(ee){
							if( ee.parentid == "0" && e.productrelease.length > 1){
								return false;
							}
							if( ee.state && $.trim(ee.state.name).toLowerCase() === "production"){
								return false;
							}
							return true;
						});
						if( hasreleases && hasreleases.length > 0 ){
							res.push(e);
						}
					}
				});
				return res;
			}
		},{
			header: "Please select the release to remove: ",
			datapage: "SelectRelease",
			excludestates : appdb.repository.ui.command.remove.release.excludedStates.release,
			filterData: function(d){
				var res = $.grep(d, function(e){
					if( e.parentid == "0"){
						if(d.length > 1){
							return false;
						}
						if( e.state && $.trim(e.state.name).toLowerCase() === "production"){
							return false;
						}
					}
					return true;
				});
				return res;
			},
			footer: "<div class='warning tip'><img src='/images/repository/warning.png' alt=''/><span>Base releases will not be included in the list above if there are update releases in the selected series.</span></div>"
		},{
			datapage: "ConfirmData",
			header: "You are about to remove a release :",
			datanames: {
				"series":{name: "Series", value: "series.name"},
				"release": {name: "Release", value: "release.displayversion"}
			},
			footer: "<div class='warning'><img src='/images/repository/warning.png' alt='' /><span>The published repositories in this release will be <b>removed</b> as well!<br/>This action <b>CANNOT</b> be reverted.</span></div>",
			actions: [
				{type:"next",display:"remove"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.RemoveRelease",
			map: {
				"id": "release.id"
			},
			text: "Please wait while removing the release...",
			events: {
				onSuccess: function(d){
					if( d && d.releaseid ){
						appdb.repository.ui.CurrentReleaseManager.views.releaseList.removeRelease(d.releaseid);
					}
				},
				onError: function(d, parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			},
			actions: null
		}]
	});
},{
	excludedStates: {
		series: [],
		release: ["production"]
	},
	canPerformForData : function(data){
		data = data || {};
		var release = data.release || null;
		if( release === null ) return false;
		var state = (release && release.state)?$.trim(release.state.name).toLowerCase():"";
		if( $.isArray(this.excludedStates.release) && this.excludedStates.release.length > 0){
			if( $.inArray(state, this.excludedStates.release) > -1 ){
				return false;
			};
		}
		return true;
	}
});

appdb.repository.ui.command.create = {};
appdb.repository.ui.command.create.update = appdb.ExtendClass(appdb.repository.ui.CommandView, "appdb.repository.ui.command.create.update", function(o){
	this.render = function(d){
		this.reset();
		if( typeof d === "object" && $.isEmptyObject(d) === false ){
			this.setData(d);
		}
		this.options.data = this.options.data || {};
		this.options.data.series = this.options.data.series || {};
		this.options.data.series.productrelease = this.options.data.series.productrelease || [];
		this.options.data.series.productrelease = $.isArray(this.options.data.series.productrelease)?this.options.data.series.productrelease:[this.options.data.series.productrelease];
		
		var baseid = $.grep( this.options.data.series.productrelease, function(e){
			return ( $.trim(e.parentid) === "0" );
		});
		
		if( baseid.length > 0 ){
			baseid = baseid[0].id;
		}else{
			baseid = null;
		}
		
		if( !baseid ){
			appdb.repository.ui.CurrentReleaseManager.initNewRelease("update");
		}else{
			appdb.repository.ui.CurrentReleaseManager.initNewRelease("update", baseid);
		}
		
	};
});
appdb.repository.ui.command.create.base = appdb.ExtendClass(appdb.repository.ui.CommandView, "appdb.repository.ui.command.create.base", function(o){
	this.render = function(d){
		this.reset();
		if( typeof d === "object" && $.isEmptyObject(d) === false ){
			this.setData(d);
		}
		appdb.repository.ui.CurrentReleaseManager.initNewRelease("base");
	};
});
appdb.repository.ui.command.remove.series = appdb.ExtendClass(appdb.repository.ui.CommandView,"appdb.repository.ui.command.remove.release", function(o){
	this.options = $.extend(this.options,{
		name: "remove",
		type: "series",
		title: "Remove series",
		pages: [{
			header: "Please select a series: ",
			datapage: "SelectSeries",
			excludestates : appdb.repository.ui.command.remove.series.excludedStates.series
		},{
			datapage: "ConfirmData",
			header: "You are about to remove a series :",
			datanames: {
				"series":{name: "Series", value: "series.name"}
			},
			footer: "<div class='warning'><img src='/images/repository/warning.png' alt='' /><span>The published repositories in this release will be <b>removed</b> as well!<br/>This action <b>CANNOT</b> be reverted.</span></div>",
			actions: [
				{type:"next",display:"remove"}
			]
		},{
			datapage: "ExecuteCommand",
			dispatcher: "appdb.repository.dispatcher.RemoveSeries",
			map: {
				"id": "series.id"
			},
			text: "Please wait while removing the series...",
			events: {
				onSuccess: function(d){
					if( d && d.seriesid ){
						appdb.repository.ui.CurrentReleaseManager.views.releaseList.removeSeries(d.seriesid);
					}
				},
				onError: function(d, parent){
					$(parent.dom).find(".action.previous").removeClass("hidden");
				}
			},
			actions: null
		}]
	});
},{
	excludedStates: {
		series: [],
		release: []
	},
	canPerformForData : function(data){
		data = data || {};
		var series = data.series || null;
		if( series === null ) return false;
		return true;
	}
});
setTimeout(function(){//temporary fix for chrome 29 and dojo <=v.1.6
	if( window.chrome && parseInt(window.navigator.appVersion.match(/Chrome\/(\d+)\./)[1], 10) >=29 ){
		dojo.query = (function(q){
				return function(a1,a2){
					var res = q.apply(this,arguments);
					if( res && res.length > 0 && $.trim(res[0].innerHTML) === "" ){
						if( a2 && a1 === ">"){
							res = [];
							$.each($(a2).children(), function(i,e){
								res.push($(this)[0]);
							});
						}
					}
					return res;
				};
		})(dojo.query);
	}
},2000);
	
