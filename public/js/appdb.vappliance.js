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
appdb.vappliance = {};
appdb.vappliance.components = {};
appdb.vappliance.model = {};
appdb.vappliance.utils = {};
appdb.vappliance.utils.sortNetworkTrafficRules = function(d) {
	d = d || [];
	d = $.isArray(d) ? d : [d];

	//Sort by direction
	d.sort(function(a, b) {
	    //Sort by direction
	    if (a.direction > b.direction) return 1;
	    if (a.direction < b.direction) return -1;

	    //Sort be protocols
	    if (a.protocols > b.protocols) return 1;
	    if (a.protocols < b.protocols) return -1;

	    //Sort by ports ranges
	    if (a.port_ranges > b.port_ranges) return 1;
	    if (a.port_ranges < b.port_ranges) return -1;

	    return 0;
	});

	return d;
};
appdb.vappliance.utils.formatSizeUnits = function(bytes,displayunit){
	displayunit = (typeof displayunit === "boolean")?displayunit:true;
	var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
	if (bytes === 0) return '0 Bytes';
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	return Math.round(bytes / Math.pow(1024, i), 2) + ((displayunit===true)?' ' + sizes[i]:'');
};
appdb.vappliance.utils.normalization = {};
appdb.vappliance.utils.normalization.ram = function(v) {
    var val = parseInt(v);
    return appdb.config.normalization.vappliance.vmi.ram[$.trim(val)] || $.trim(val);
};
appdb.vappliance.utils.normalization.viewram = function(v, prop) {
    var val = parseInt(v);
    var res = v;
    if (appdb.config.normalization.vappliance.vmi.ram[$.trim(val)]) {
	return res + ' bytes';
    }
    if (prop.options.dataSource) {
	var res = null;
	var ds = prop.options.dataSource || [];
	ds = $.isArray(ds) ? ds : [ds];
	$.each(ds, function(i, d){
	   if (res === null && typeof d.id !== 'undefined' && d.id == v) {
	       res = (typeof d.val === 'function') ? d.val() : d.id;
	   }
	});
    }
    if (res === null || res === 'None') {
	return 0;
    }
    return res;
};
appdb.vappliance.utils.getInfiniteDate = function(){
	return "2500-01-01";
};
appdb.vappliance.utils.getDateDiff = function(dt1, dt2, humanize) {
	humanize = (typeof humanize === 'boolean') ? humanize : false;
	dt1 = (dt1) ? new Date(dt1) : new Date();
	dt2 = (dt2) ? new Date(dt2) : new Date();
	dt1 = new Date((dt1.toISOString().split('T')[0]) + ' 23:59');
	dt2 = new Date((dt2.toISOString().split('T')[0]) + ' 23:59');

	/*
	 * If the dates are equal, return the 'empty' object
	 */
	if (dt1 == dt2) return (humanize) ? 'today' : {years: 0, months: 0, days: 0};

	/*
	 * ensure dt2 > dt1
	 */
	if (dt1 > dt2)
	{
		var dtmp = dt2;
		dt2 = dt1;
		dt1 = dtmp;
	}

	dt1 = (dt1.toISOString().split('T')[0]) + ' 23:59:59';
	dt2 = (dt2.toISOString().split('T')[0]) + ' 23:59:59';

	var mdt1 = moment(dt1, 'YYYY-MM-DD HH:mm:ss');
	var mdt2 = moment(dt2, 'YYYY-MM-DD HH:mm:ss').add(1, 'day');
	var diff = moment.duration(mdt2.diff(mdt1));

	if (humanize) {
	    return diff.humanize();
	}

	var ret = {
		years: diff.years(),
		months: diff.months(),
		days: diff.days()
	};

};
appdb.vappliance.utils.getLexicalDateFromDiff = function(diffObject) {
	var diff = diffObject || {};
	var months = diff["months"] || 0;
	var days = diff["days"] || 0;
	var years = diff["years"] || 0;
	var message = "";
	if (months > 0) {
		message += months+ ' month' + ((months > 1) ? "s" : "");
		if (days > 0) {
			message += " and ";
		}
	}

	if (days > 0) {
		 message += days + " day" + ((days > 1) ? "s" : "");
	}

	if (days === 0 && months === 0) {
	     if (years === 0) {
		     message = 'today';
	     } else {
		     message = '12 months';
	     }
	} else if (years > 0) {
	    message = years + ' year' + ((years > 1) ? 's' : '');
	}

	return message;
};
appdb.vappliance.utils.getLexicalDateDiff = function (dt1, dt2) {
	return appdb.vappliance.utils.getDateDiff(dt1, dt2, true);
};
appdb.vappliance.utils.getDateAfterMonths = function(months) {
	var futureMonth = moment(new Date()).add(months, 'M');
	var d = futureMonth.toDate();
	return d.toISOString().split('T')[0];
};
appdb.vappliance.utils.getExpirationPresets = function(currentDate) {
	currentDate = currentDate ? $.trim(currentDate).split('T')[0] : null;
	var months = [3, 6, 9, 12];
	var presets = [];
	var found = false;

	$.each(months, function(index, val) {
		var id = appdb.vappliance.utils.getDateAfterMonths(val);
		var item = {
			id: id,
			val: function() {
				return id;
			},
			displayValue: function() {
				return ''  + val + ' months from now (' + id + ')';
			},
			selected: (!found && id === currentDate)
		};
		found = found || item.selected || false;
		presets.push(item);
	});

	if (!found && currentDate && $.trim(currentDate).indexOf(appdb.vappliance.utils.getInfiniteDate()) === -1) {
		//var dateDiff = appdb.vappliance.utils.getLexicalDateDiff(currentDate);
		presets = [{
			id: currentDate,
			val: function() { return currentDate; },
			displayValue: function() { return "<span>Keep previous set (" + currentDate + ")</span>"; },
			selected: true
		}].concat(presets);
	}

	return presets;
};
appdb.vappliance.model.VirtualAppliance = appdb.model.VirtualAppliance;
appdb.vappliance.FindData = appdb.FindData;
appdb.vappliance.validators = {};
appdb.vappliance.validators.generic = appdb.DefineClass("appdb.vappliance.validators.generic",function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		value: o.value || "",
		errorMessage: o.errorMessage || "",
		lastError: {},
		isValid: true,
		canValidate: ( (typeof o.canValidate === "function")?o.canValidate:true )
	};
	this.isValid = function(){
		this.options.isValid();
	};
	this.getDataType = function(){
		var p = this.options.parent;
		if( p.options && p.options.dataType ){
			return p.options.dataType;
		}
		return "text";
	};
	this.setupError = function(v){
		var vtype = this._type_.getName().split(".");
		vtype = vtype[vtype.length-1];
		this.options.lastError =  {
			parent: this.parent,
			dom: this.dom,
			value: v,
			message: ( this.getErrorMessage(v) || this.options.errorMessage ),
			type: vtype
		};
		return this.options.lastError;
	};
	this.getErrorMessage = function(v){
		return;
	};
	this.getError = function(){
		this.setupError();
		return this.options.lastError;
	};
	this.getValue = function(){
		return this.parent.getDisplayValue();
	};
	this.onValidate = function(){
		return true;
	};
	this.canValidate = function(){
		return this.options.canValidate();
	};
	this.reset = function(){
		this.options.isValid = true;
		this.options.lastError = {};
	};
	this.validate = function(v){
		this.reset();
		if( this.canValidate() === false ){
			return true;
		}
		var res = this.onValidate();
		this.options.isValid = res;
		if( res === false ) {
			this.setupError(v);
			res = this.getError();
		}
		return res;
	};
	this._init = function(){
		this.parent = this.options.parent;
		this.dom = $(this.options.container);
		if( this.options.canValidate !== true ){
			this.canValidate = this.options.canValidate;
		}
	};
	this._init();
});
appdb.vappliance.validators.maxsize = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.maxsize", function(o){
	 this.getErrorMessage = function(v){
		 v = (typeof v !== "undefined")?v:this.getValue();
		 var msg = "";
		 var isnumber = !isNaN(parseFloat(v)) && isFinite(v);
		 if( isnumber === true && this.getDataType() === "number"){
			 msg = "Value must be less or equal to " + (this.options.value || "0");
		 }else if( typeof v === "string" ){
			 msg = "Value must be up to " + this.options.value + " characters long";
		 }
		return msg;
	 };
	 this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		var isnumber = !isNaN(parseFloat(v)) && isFinite(v);
		if(isnumber === true && this.getDataType() === "number"){
			if( parseFloat(v) > parseFloat(this.options.value||"0") ){
				return false;
			}
		}else if( v.length && $.isArray(v) === false ){
			if( v.length > this.options.value ){
				return false;
			}
		}else if ($.trim(v) === "" ){
			return true;
		}else if( v > this.options.value ){
			return false;
		}
		return true;
	 };
});
appdb.vappliance.validators.minsize = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.minsize", function(o){
	this.getErrorMessage = function(v){
		 var msg = "";
		 v = (typeof v !== "undefined")?v:this.getValue();
		 var isnumber = !isNaN(parseFloat(v)) && isFinite(v);
		 if( isnumber === true && this.getDataType() === "number"){
			 msg = "Value must be greater or equal to " + (this.options.value || "0");
		 }else if( typeof v === "string" ){
			 msg = "Value must be at least " + this.options.value + " characters long";
		 }
		return msg;
	 };
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		var isnumber = !isNaN(parseFloat(v)) && isFinite(v);
		if(isnumber === true && this.getDataType() === "number"){
			if( parseFloat(v) < parseFloat(this.options.value||"0") ){
				return false;
			}
		}else if( v.length && $.isArray(v) === false ){
			if( v.length < this.options.value ){
				return false;
			}
		}else if( $.trim(v) === "" ){
			 return true;
		}else if( v < this.options.value ){
			return false;
		}
		return true;
	};
});
appdb.vappliance.validators.datatype = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.datatype", function(o){
	this.getErrorMessage = function(v){
		switch( this.options.value ){
			case "text":
				return "Value must be alphanumeric";
			case "number":
				return "Value must be a number";
			case "array":
				return "Value must be an array";
			case "object":
				return "Value must be an object";
			default:
				return "Invalid value type";
		}
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		if( $.trim(v) === "" ){
			return true;
		}
		switch( this.options.value ){
			case "text":
				return (typeof v === "string");
			case "number":
				return !isNaN(parseFloat(v)) && isFinite(v);
			case "array":
				return $.isArray(v);
			case "object":
				return $.isPlainObject(v);
			default:
				return true;
		}
	 };
});
appdb.vappliance.validators.isrequired = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.isrequired", function(o){
	this.getErrorMessage = function(v){
		return "Value is required";
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		if( typeof v === "undefined" || v <= 0 || $.trim(v) === "" ){
			return false;
		}
		return true;
	 };
});
appdb.vappliance.validators.regex = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.regex", function(o){
	this.getErrorMessage = function(v){
		return $.trim($(this.dom).data("regexmessage")) || "Invalid value";
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		var rx = new RegExp(this.options.value, "g");
		return rx.test(v);
	};
});
appdb.vappliance.validators.url = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.url", function(o){
	this.getErrorMessage = function(v){
		return "Value is not a valid URL";
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		var rx =  /(ftps|ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i;
		return rx.test(v);
	};
});
appdb.vappliance.validators.optionalurl = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.optionalurl", function(o){
	this.getErrorMessage = function(v){
		return "Value is not a valid URL";
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		if( $.trim(v) === "" ){
			return true;
		}
		var rx =  /(ftps|ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i;
		return rx.test(v);
	};
});
appdb.vappliance.validators.listitem = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.listitem", function(o){
	this.getErrorMessage = function(v){
		return "Value is not a valid";
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		var list = this.options.value || [];
		list = $.isArray(list)?list:[list];
		var found = [];
		var value = $.trim(v).toLowerCase();
		if( list.length > 0 && v.length > 0 ){
			found = $.grep(list, function(e){
				return (value === $.trim(e.val()).toLowerCase() ); 
			});
		}
		return (found.length>0)?true:false;
	};
});
appdb.vappliance.validators.uniquegroup = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.uniquegroup", function(o){
	this.getErrorMessage = function(v){
		return "Group title is already used by another VMI";
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		var val = $.trim(v).toLowerCase();
		if( val === "") return true;
		var me = this.parent.parent; // ./property/valuehandler/validator
		var container = me.parent; // ./properycontainer/property/valuehandler/validator
		var maincontainer = me.parent.parent; // ./VApplianceVMIList/VApplianceVMIItem/Property
		var dpath = me.options.dataPath;
		
		var subcontainers = maincontainer.getSubContainers() || [];
		subcontainers = $.grep(subcontainers, function(e){
			return ( $(e.dom).get(0) !== $(container.dom).get(0) );
		});
		var res = true;		
		$.each(subcontainers , function(i, e){
			var p = e.getPropertyByDataPath(dpath);
			if( res === true && p && p.options.handler ){
				if( $.trim(p.options.handler.options.dataCurrentValue).toLowerCase() === val ){
					res  = false;
				}
			}
		});
		return res;
	};
});
appdb.vappliance.validators.uniqueurl = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.uniqueurl", function(o){
	this.getErrorMessage = function(v){
		return "Url is already used by another VMI Instance";
	};
	this.onValidateImages = function(val){
		var used = false;
		var me = this.parent.parent;
		$("body .vappliance.workingversion .vaversion-vmilist .vaversion-vmiversionlist .vmiversion-url").each(function(i,e){
			if( $(me.dom)[0] !== $(e)[0] ){
				var d = $(e).find(".value > .dijit");
				if( $(d).length > 0 ){
					d = $(d)[0];
					var dc = dijit.byNode(d);
					if( dc ){
						var v = $.trim(dc.get("displayedValue")).toLowerCase();
						if( v === $.trim(val).toLowerCase() ){
							used = true;
						}
					}
				}
			}
		});
		return !used;
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		var val = $.trim(v).toLowerCase();
		if( val === "") return true;
		return this.onValidateImages(val);
	};
});
appdb.vappliance.validators.uniqueurlhypervisor = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.uniqueurlhypervisor", function(o){
	this.getErrorMessage = function(v){
		if( this.parent.parent.options.dataPath === "hypervisor"){
			return "URL with this hypervisor is used in another VMI Instance";
		}else{
			return "Hypervisor with this URL is used in another VMI Instance";
		}
	};
	this.getHypervisorValue = function(me, focus){
		focus = (typeof focus === "boolean")?focus:false;
		var d = $(me).closest(".vaversion-vmiversion").find(".vmiversion-hypervisor .value > .dijit");
		var res = false;
		if( $(d).length > 0 ){
			var dc = dijit.byNode($(d)[0]);
			if( dc ){
				res = $.trim(dc.get("value")).toLowerCase();
			}
		}
		if( focus && dc){
			dc.onChange();
		}
		return res;
	};
	this.sameHypervisor = function(hypervisor, el2){
		var hv2 = this.getHypervisorValue(el2);
		if( hv2 === hypervisor){
			return true;
		}
		return false;
	};
	this.getUrlValue = function(me, focus){
		focus = (typeof focus === "boolean")?focus:false;
		var d = $(me).closest(".vaversion-vmiversion").find(".vmiversion-url .value > .dijit");
		var res = false;
		if( $(d).length > 0 ){
			var dc = dijit.byNode($(d)[0]);
			if( dc ){
				res = $.trim(dc.get("displayedValue")).toLowerCase();
			}
		}
		if( focus && dc){
			dc.onChange();
		}
		return res;
	};
	this.sameUrl = function(url, el2){
		var url2 = this.getUrlValue(el2);
		if( url2 === url){
			return true;
		}
		return false;
	};
	this.onValidateImages = function(val){
		var used = false;
		var me = this.parent.parent;
		var medom = $(me.dom)[0];
		var mehyper = this.getHypervisorValue(medom);
		$("body .vappliance.workingversion .vaversion-vmilist .vaversion-vmiversionlist .vmiversion-url").each((function(self){ 
			return function(i,e){
				if( medom !== $(e)[0] && used === false){
					var d = $(e).find(".value > .dijit");
					if( $(d).length > 0 ){
						d = $(d)[0];
						var dc = dijit.byNode(d);
						if( dc ){
							var v = $.trim(dc.get("displayedValue")).toLowerCase();
							if( v === $.trim(val).toLowerCase() && mehyper !== false){
								used = self.sameHypervisor(mehyper, e);
							}
						}
					}
				}
			};
		})(this));
		return !used;
	};
	this.onValidateHypervisors = function(val){
		var used = false;
		var me = this.parent.parent;
		var medom = $(me.dom)[0];
		var meurl = this.getUrlValue(medom);
		$("body .vappliance.workingversion .vaversion-vmilist .vaversion-vmiversionlist .vmiversion-hypervisor").each((function(self){ 
			return function(i,e){
				if( medom !== $(e)[0] && used === false){
					var d = $(e).find(".value > .dijit");
					if( $(d).length > 0 ){
						d = $(d)[0];
						var dc = dijit.byNode(d);
						if( dc ){
							var v = $.trim(dc.get("displayedValue")).toLowerCase();
							if( v === $.trim(val).toLowerCase() && meurl !== false){
								used = self.sameUrl(meurl, e);
							}
						}
					}
				}
			};
		})(this));
		return !used;
	};
	this.onValidate = function(v){
		var res = true;
		v = (typeof v !== "undefined")?v:this.getValue();
		var val = $.trim(v).toLowerCase();
		if( val === "") return true;
		if( this.parent.parent.options.dataPath === "hypervisor"){
			res = this.onValidateHypervisors(val);
		}else{
			res = this.onValidateImages(val);
		}
		return res;
	};
});
appdb.vappliance.validators.date = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.date", function(o){
	this.getErrorMessage = function(v){
		return "Date format should be: yy-MM-dd";
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		if( $.trim(v) === "" ) {
			if( this.parent && this.parent.dom ){
				if( $(this.parent.dom).closest(".property").hasClass("mandatory") === false ){
					return true;
				}
			}
		}
		var dt = new Date(v);
		if( $.trim(dt.toUTCString()).toLowerCase() === "invalid date"){
			return false;
		}
		return true;
	};
});
appdb.vappliance.validators.futuredate = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.futuredate", function(o){
	this.getErrorMessage = function(v){
		return "Value must refer to future date";
	};
	this.onValidate = function(v){
		v = (typeof v !== "undefined")?v:this.getValue();
		if( $.trim(v) === "" ) {
			if( this.parent && this.parent.dom ){
				if( $(this.parent.dom).closest(".property").hasClass("mandatory") === false ){
					return true;
				}
			}
		}
		var dt = new Date(v);
		var nowdate = new Date();
		if( dt <= nowdate){
			return false;
		}
		return true;
	};
});
appdb.vappliance.validators.vaversion = appdb.ExtendClass(appdb.vappliance.validators.generic,"appdb.vappliance.validators.vaversion", function(o){
	this.options.currentvaversions = null;
	this.getErrorMessage = function(v){
		return "This value is already used in a previous va version";
	};
	this.loadVaversions = function(){
		(function(self){
			$.ajax({
				url: "/apps/vapplianceusedversions",
				data: { appid: appdb.vappliance.ui.CurrentVAManager.getSoftwareData().application.id },
				async: false,
				success: function(res){
					self.options.currentvaversions = [];
					if( res && res.result && res.result.success === true ) {
						self.options.currentvaversions = res.result.versions;
					}
				},
				error: function(){
					self.options.currentvaversions = [];
				}
			});
		})(this);
		
	};
	this.getCurrentVersions = function(){
		if( this.options.currentvaversions === null ) {
			this.loadVaversions();
		}
		return this.options.currentvaversions;
	};
	this.onValidate = function(v){
		if( $.inArray($.trim( this.getValue() ), this.getCurrentVersions() ) === -1 ) {
			return true;
		}
		
		return false;
	};
});
appdb.vappliance.databinders = {};

appdb.vappliance.databinders.defaultImageTitle = function(target, source){
	var _getPattern = function(store, previous){
		var prefix = "";
		previous = (typeof previous === "boolean")?previous:false;
		if( previous === true ){
			prefix = "prev.";
		}
		var res = "Image for " + appdb.pages.application.currentName();
		var info = [];
		if( typeof store[prefix + "os"] !== "undefined" && $.trim(store[prefix + "os"]) !== "" ){
			info.push(store[prefix + "os"]);
		}
		if( typeof store[prefix + "os.version"] !== "undefined" && $.trim(store[prefix + "os.version"]) !== "" ){
			var os = store[prefix + "os"];
			var osver  = store[prefix + "os.version"];
			if( $.trim(os) !== ""){
				if( osver.toLowerCase().indexOf(os.toLowerCase()) === 0 ){
					osver = $.trim(osver.substr(os.length));
				}
			}
			info.push(osver);
		}
		if( typeof store[prefix + "hypervisor"] !== "undefined" && $.trim(store[prefix + "hypervisor"]) !== "" ){
			info.push(store[prefix + "hypervisor"]);
		}
		if( info.length !== 0){
			info = " [" + info.join("/") + "]";
		}
		
		return res += info;
	};
	var _collectStore = function(target){
		var parent = target.parent;
		var props = parent.options.props;
		var store = target.getDataBindStore();
		for(var i in props){
			if( !props.hasOwnProperty(i) || props[i] === target || !target.hasDataBinds(props[i].getDataPath()) || props[i].getDataPath() !== i) continue;
			var v = props[i].options.handler.getDisplayValue();
			if( v && $.inArray($.trim(v).toLowerCase(),["undefined","-1"]) === -1 ){
				if( typeof v.val === "function"){
					store[props[i].getDataPath()] = v.val();
				}else{
					store[props[i].getDataPath()] = v;
				}
			}
		}
		return store;
	};
	if( !target.options.handler || !target.options.handler.editor ) return;
	var store = target.getDataBindStore();
	var targetValue = $.trim(target.options.handler.getDisplayValue());
	var patterncollect = _getPattern(_collectStore(target));
	var pattern = _getPattern(target.getDataBindStore());
	if( typeof store["__original_value__"] === "undefined"){
		store["__original_value__"] = target.getDataValue();
	}
	
	//First time
	if( !store["__init__"] ){
		store["__init__"] = true;
		if( $.trim(targetValue) === "" || $.trim(targetValue) === $.trim(patterncollect)){ //stored value came from pattern
			store["_user_defined__"] = false;
			target.options.handler.editor.set("displayedValue", patterncollect);
		}else {
			store["_user_defined__"] = true;
		}
		return;
	}else if( target === source ){
		if( $.trim(targetValue) === "" ){ //user deleted value
			store["__user_defined__"] = false;
		}else if( $.trim(targetValue) === $.trim(patterncollect) || $.trim(targetValue) === $.trim(pattern)){ //in case of same pattern nothing to do
			store["__user_defined__"] = false;
			return;
		}else{ //user just changed something
			store["__user_defined__"] = true;
		}
	}
	
	if(store["__user_defined__"] === false){ //event occured by external databinded properties
		setTimeout(function(){
			store["prev." + source.getDataPath()] = store[source.getDataPath()];
			store[source.getDataPath()] = source.options.handler.editor.get("displayedValue");
			var pattern = _getPattern(store);
			store["__prevpattern__"] = pattern;
			target.options.handler.editor.set("displayedValue", pattern);
			store["__user_defined__"] = false;
			target.setDataBindStore(store);
		},1);
	}
	
};
appdb.vappliance.VAVersionValidatorRegister = appdb.DefineClass("appdb.vappliance.ValidatorRegistry", function(o){
	this.options = {
		vaversion: null,
		properties: []
	};
	this.onError = function(errs){
		errs = errs || this.getErrors();
		if( this.options.vaversion && this.options.vaversion.onValidationError ){
			this.options.vaversion.onValidationError( errs );
		}
	};
	this.getErrors = function(){
		var errs = [];
		var res = $.grep(this.options.properties, function(e){
			return ( e.isValid() === false );
		});
		if( res.length > 0 ){
			$.each(res, function(i, e){
				errs.push(e);
			});
		}
		return errs;
	};
	this.isValid = function(){
		var errs = this.getErrors();
		return ( errs.length === 0 );
	};
	this.check = function(){
		var errs = this.getErrors();
		this.onError(errs);
	};
	this.register = function(property){
		if ( typeof property === "undefined" ) return;
		this.unregister(property);
		this.options.properties.push(property);
	};
	this.unregister = function(property){
		var found = -1;
		$.each(this.options.properties, function(i,e){
			if( e === property ){
				found = i;
			}
		});
		if( found > -1 ){
			this.options.properties.splice(found,1);
		}
	};
	this.resetRegister = function(full){
		full = (typeof full === "boolean")?full:true;
		$.each(this.options.properties, function(i, e){
			if( e ){
				e.reset(full);
			}
		});
		this.options.properties = [];
		this.options.vaversion = null;
	};
	this.initRegister = function(obj){
		this.resetRegister();
		if( obj ){
			this.options.vaversion = obj;
		}
	};
});
/*
 *Fires event to subscribers when a va version is selected
 */
appdb.vappliance.VAVersionSelectionRegister = appdb.DefineClass("appdb.vappliance.VAVersionSelectionRegister", function(o){
	this.subscribers = [];
	this.register = function(subscriber){
		this.subscribers.push(subscriber);
	};
	this.unregister = function(subscriber){
		var index = -1;
		$.each(this.subscribers, function(i, e){
			if( index < 0 && e === subscriber ){
				index = i;
			} 
		});
		if( index > -1 ){
			this.subscribers.splice(index,1);
		}
	};
	this.selectionChanged = function(d){
		$.each(this.subscribers, function(i,e){
			if( e && typeof e.reRender === "function" ){
				e.reRender(d);
			} 
		});
	};
	this.clear = function(){
		this.subscribers = [];
	};
});
appdb.vappliance.ui = {};
appdb.vappliance.ui.editors = {};
appdb.vappliance.ui.views = {};
appdb.vappliance.ui.CurrentVAManager = null;
appdb.vappliance.ui.CurrentVAVersionValidatorRegister = new appdb.vappliance.VAVersionValidatorRegister();
appdb.vappliance.ui.CurrentVAVersionSelectionRegister = new appdb.vappliance.VAVersionSelectionRegister();
appdb.vappliance.ui.ShowVerifyDialog = appdb.utils.ShowVerifyDialog;
appdb.vappliance.ui.views.VersionSelector = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.VersionSelector", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		selectedData: o.selectedData || null,
		selectedIndex: o.selectedIndex || -1,
		dom: {
			container: null,
			previous: null,
			next: null,
			list: null,
			item: null,
			selectedItemTemplate: null,
			selectedItem: null,
			count: null,
			emptylist: null,
			notfound: null
		}
	};
	this.orderData = function(){
		this.options.data = this.options.data || [];
		this.options.data = $.isArray(this.options.data)?this.options.data:[this.options.data];
		this.options.data.sort(function(a,b){
			if( $.trim(a.archivedon) === "" ) a.archivedon = a.createdon;
			if( $.trim(b.archivedon) === "" ) b.archivedon = b.createdon;
			var ad = a.archivedon.replace(/[\-T\:]/g,"");
			var bd = b.archivedon.replace(/[\-T\:]/g,"");
			ad = ad.split(".")[0];
			bd = bd.split(".")[0];
			ad = parseInt(ad);
			bd = parseInt(bd);
			return bd-ad;
		});
	};
	this.preRender = function(){
		return true;
	};
	this.doRender = function(){
		if( !this.options.data || !this.options.data.length > 0 ){
			$(this.options.dom.container).addClass("empty");
		}else{
			$(this.options.dom.container).removeClass("empty");
		}
		this.orderData();
		this.renderList();
	};
	this.postRender = function(){
		//to be overriden
	};
	this.renderNotFound = function(){
		if( !this.options.dom.notfound ) return;
		$(this.options.dom.list).find("li").removeClass("selected");
		$(this.options.dom.selectedItem).empty().append($(this.options.dom.notfound.clone(true)));
	};
	this.renderPaging = function(){
		if( this.hasNext() ){
			$(this.options.dom.next).removeClass("disabled").attr("title","View next version");
		}else{
			$(this.options.dom.next).addClass("disabled").attr("title","Reached last version");
		}
		$(this.options.dom.next).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("disabled") === false ){
					self.next();
				}
				return false;
			};
		})(this));
		
		if( this.hasPrevious() ){
			$(this.options.dom.previous).removeClass("disabled").attr("title","View previous version");
		}else{
			$(this.options.dom.previous).addClass("disabled").attr("title","Viewing first version");
		}
		$(this.options.dom.previous).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("disabled") === false ){
					self.previous();
				}
				return false;
			};
		})(this));
	};
	this.render = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		this.options.data = d.slice(0);
		if( this.preRender() !== false ){
			this.doRender();
		}
		this.postRender();
	};
	this.renderList = function(){
		$(this.options.dom.list).empty();
		$.each(this.options.data, (function(self){
			return function(i, e){
				var dom = self.renderItem(e);
				if( dom !== false ){
					var li = $("<li class='"+e.status+"' data-id='"+e.id+"' data-index='"+i+"' data-status='"+e.status+"'></li>");
					if( e.published ){
						$(li).addClass("published");
					}
					if( e.enabled === "false" ){
						$(li).addClass("disabled");
					}
					$(li).append(dom);
					$(li).off("click").on("click", (function(selfy){
						return function(ev){
							ev.preventDefault();
							selfy.selectIndex($(this).data("index"));
							return false;
						};
					})(self));
					$(self.options.dom.list).append(li);
				}
			};
		})(this));
	};
	this.renderSelectedItem = function(){
		var data = this.options.selectedData;
		$(this.options.dom.list).find("li").removeClass("selected");
		if( !data ){
			this.renderNotFound();
			return;
		}
		$(this.options.dom.list).find("li[data-id='"+data.id+"']").addClass("selected");
		
		var dom = this.renderItem(data);
		$(this.options.dom.selectedItem).empty().append($(dom).html());
		return dom;
	};
	this.renderItem = function(data){
		var dom = $(this.options.dom.item).clone(true);
		$(dom).find(".id > .value").text(data.id);
		$(dom).find(".version > .value").text(data.version);
		var createdon = ""+data.createdon;
		createdon = createdon.split(".")[0].replace(/T/," ");
		$(dom).find(".published > .value").text(createdon);
		
		var archivedon = ""+data.archivedon;
		if( $.trim(data.archivedon) === "" ){
			archivedon = ""+data.createdon;
		}
		archivedon = archivedon.split(".")[0].replace(/T/," ");
		$(dom).find(".archived > .value").text(archivedon);
		return dom;
	};
	this.selectedIndex = function(){
		return this.options.selectedIndex;
	};
	this.selectIndex = function(index){
		index = ( ( !isNaN(parseFloat(index)) && isFinite(index) )?(index<<0):-1 );
		if( index >= this.options.data.length ){
			index = this.options.data.length-1;
			if( index < 0 ){
				index = 0;
			}
		}
		this.options.selectedIndex = index;
		this.options.selectedData = this.options.data[index];
		this.renderSelectedItem();
		this.renderPaging();
		this.publish({event: "select", value: {
			index: this.selectedIndex(),
			data: this.options.data[this.selectedIndex()]
		}});
	};
	
	this.selectId = function(id){
		id = ( ( !isNaN(parseFloat(id)) && isFinite(id) )?(id<<0):-1 );
		var index = null;
		$.each( this.options.data, function(i, e){
			if( index === null && (id<<0) === (e.id << 0) ){
				index = i;
			}
		});
		if( index === null){
			this.renderNotFound();
			return false;
		}
		this.selectIndex(index);
		return true;
	};
	this.count = function(){
		return this.options.data.length || 0;
	};
	this.hasNext = function(){
		return ((this.selectedIndex()+1) < this.count() );
	};
	this.hasPrevious = function(){
		return (this.selectedIndex() > 0 );
	};
	this.next = function(){
		if( this.hasNext() ){
			this.selectIndex(this.selectedIndex() + 1);
		}
	};
	this.previous = function(){
		if( this.hasPrevious() ){
			this.selectIndex(this.selectedIndex() - 1);
		}
	};
	this.current = function(){
		return this.options.data[this.selectedIndex()];
	};
	
	this.initContainer = function(){
		if( this.options.dom.selectedContainer ) return;
		var container = $(this.dom).find(".versionselector");
		if( $(container).length > 0 ){
			this.options.dom.container = $(container).clone(true);
		}else{
			this.options.dom.container = $("<div class='versionselector'></div>");
		}
		
		var next = $(this.options.dom.container).find(".next.paging");
		if( $(next).length > 0 ){
			this.options.dom.next = $(next).clone(true);
		}else{
			this.options.dom.next = $("<a class='next paging'>next</a>");
		}
		
		var prev = $(this.options.dom.container).find(".previous.paging");
		if( $(prev).length > 0 ){
			this.options.dom.previous = $(prev).clone(true);
		}else{
			this.options.dom.next = $("<a class='previous paging'>previous</a>");
		}
		
		var count = $(this.options.dom.container).find(".count.paging");
		if( $(count).length > 0 ){
			this.options.dom.count = $(count).clone(true);
		}else{
			this.options.dom.count = $("<div class='count paging'></div>");
		}
		
		var list = $(this.options.dom.container).find("ul.versions");
		if( $(list).length > 0 ){
			this.options.dom.list = $(list).clone(true);
		}else{
			this.options.dom.list = $("<ul class='versions'></ul>");
		}
		
		var item = $(this.options.dom.container).find(".item");
		if( $(item).length > 0 ){
			this.options.dom.item = $(item).clone(true);
		}else{
			this.options.dom.item = $("<div class='item'></div>");
		}
		
		var selitem = $(this.options.dom.container).find(".selecteditem");
		if( $(selitem).length > 0 ){
			this.options.dom.selectedItem = $(selitem).clone(true);
		}else{
			this.options.dom.selectedItem = $(this.options.dom.item).clone(true);
		}
		$(this.options.dom.selectedItem).addClass("selecteditem").addClass("item");
		
		var emptylist = $(this.options.dom.container).find(".emptylist");
		if( $(emptylist).length > 0 ){
			this.options.dom.emptylist = $(emptylist).clone(true);
		}else{
			this.options.dom.emptylist = $("<div class='emptylist'>No versions</div>");
		}
		
		var notfound = $(this.options.dom.container).find(".notfound");
		if( $(notfound).length > 0 ){
			this.options.dom.notfound = $(notfound).clone(true);
		}else{
			this.options.dom.notfound = $("<div class='notfound'>Select to view an archived version</div>");
		}
		
		this.options.dom.selectedContainer = $("<div class='content'></div>");
		$(this.options.dom.selectedContainer).append(this.options.dom.selectedItem)
			.append(this.options.dom.list);
		$(this.dom).empty();
		$(this.options.dom.container).empty();
		$(this.options.dom.container).append(this.options.dom.previous)
			.append(this.options.dom.selectedContainer)
			.append(this.options.dom.emptylist)
			.append(this.options.dom.next)
			.append(this.options.dom.count);
		$(this.dom).append(this.options.dom.container);
	};
	this.init = function(){
		this.parent = this.options.parent;
		this.dom = this.options.container;
		this.initContainer();
	};
	this.init();
	
});
appdb.vappliance.components.VirtualApplianceProvider  = appdb.ExtendClass(appdb.Component,"appdb.vappliance.components.VirtualApplianceProvider", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		components: o.components || [],
		software: o.software,
		currentData: null,
		templates: $(o.container).find(".vappliance-templates").clone(true),
		currentSection: "",
		selectedVersion: "",
		editMode: false,
		editObject: null,
		isPrivate: (typeof o.isPrivate === "boolean")?o.isPrivate:true,
		canAccessPrivateData: (typeof o.canAccessPrivateData === "boolean")?o.canAccessPrivateData:false
	};
	this.isPrivate = function(){
		return this.options.isPrivate;
	};
	this.canAccessPrivateData = function(){
		return this.options.canAccessMetaData;
	};
	this.getSWId = function(){
		var res = ( this.options.currentData )?this.options.currentData.appid:this.options.currentData;
		if( !res ){
			res = appdb.pages.application.currentId();
		}
		return res;
	};
	this.getName = function(){
		return ( this.options.currentData )?this.options.currentData.name:this.options.currentData;
	};
	this.getId = function(){
		return ( this.options.currentData )?this.options.currentData.id:this.options.currentData;
	};
	this.getTemplate = function(name){
		var html;
		var cont = $(this.options.templates);
		if( $(cont).length === 0 ) return html;
		
		var temp = $(cont).find("[data-template='"+name+"']");
		if( $(temp).length === 0 ) return html;
		
		html = $(temp).html();
		return html;
	};
	this.isEditMode = function(){
		return this.options.editMode;
	};
	this.hasPublishedVersions = function(){
		var d = this.options.currentData || {};
		d.instance = d.instance || [];
		d.instance = $.isArray(d.instance)?d.instance:[d.instance];
		var res = $.grep(d.instance, function(e){
			return ( ( e.published && $.trim(e.published).toLowerCase() === "true" ) );
		});
		return (res.length > 0);
	};
	this.getLatestPublishedVersion = function(){
		var d = this.options.currentData || {};
		d.instance = d.instance || [];
		d.instance = $.isArray(d.instance)?d.instance:[d.instance];
		var res = $.grep(d.instance, function(e){
			return ( ( e.published && $.trim(e.published).toLowerCase() === "true" ) && (e.archived && $.trim(e.archived).toLowerCase() === "false") );
		});
		return ( res.length > 0 )?res[0]:null;
	};
	this.getContainer = function(name){
		var comp = $.grep(this.options.components, function(e){
			return (e.obj && e.name === name);
		});
		return (comp.length>0)?comp[0].obj:null;
	};
	this.renderContainer = function(name, data, autoselect){
		var component = null;
		$.each( this.options.components, function(i, e){
			if( component == null && e.name == name){
				component = e.obj;
			}
		});
		if( component !== null ){
			component.load(data);
			if( autoselect === true ){
				this.selectTab(name,true);
			}
		}
	};
	this.render = function(d){
		var data = d || {};
		$(this.dom).children(".vappliance").removeClass("hidden");
		$.each(this.options.components, (function(self){
			return function(i, e){
				if( !e.obj ){
					var cls = appdb.FindNS(e.className);
					if( cls ){
						var opts = {
							container: e.container, 
							parent: self,
							name: e.name,
							editable: (e.canEdit === true)?self.canEdit():false,
							isPrivate: (typeof e.isPrivate === "boolean")?e.isPrivate:false,
							canAccessPrivateData: (typeof e.canAccessPrivateData === "boolean")?e.canAccessPrivateData:true
						};
						var obj = new cls(opts);
						e.obj = obj;
					}
				}
				e.obj._mediator.clearAll();
				e.obj.subscribe({event:"empty", callback: function(o){
				}, caller: self})
				.subscribe({event: "norender", callback: function(o){
						this.hideView(o);
				}, caller: self})
				.subscribe({event: "dorender", callback: function(o){
						this.showView(o);
				}, caller: self});
				e.obj.load(d);
			};
		})(this));
		var isAllEmpty = true;
		$.each(this.options.components, function(i,e){
			if( e.obj && e.obj.canRender && e.obj.canRender() === true ){
				isAllEmpty = false;
			}
		});
		if( isAllEmpty ){
			this.renderEmpty();
		}
		$(this.dom).find(".vappliance.groupcontainer > ul > li > a").off("click.route").on("click.route", (function(self){
			return function(ev){
				var name = $(this).data("name") || "";
				
				var cname = "";
				switch(name.toLowerCase()){
					case "workingversion":
						cname = "working";
						break;
					case "latestversion":
						cname = "latest";
						break;
					case "previousversions":
					case "previousversion":
						cname = "previous";
						break;
					default:
						break;
				}
				self.options.currentSection = "" + cname;
				if( name !== self.options.selectedVersion){
					if( cname === "previous"){
						var vid = $("#vappliancediv" + appdb.pages.application.currentDialogCount() + " > .vappliance.groupcontainer > ul > li.previousversions > a").attr("data-vaid");
						if( $.trim(vid) !== "" ){
							self.options.currentSection = cname + "/" + parseInt($.trim(vid));
						}
					}
					self.selectTab(name);
					appdb.pages.application.currentSection("information");
					appdb.pages.application.updateSection("virtualappliance", true);	
				}
				self.options.selectedVersion = name;
			};
		})(this));
		if( !this.getLatestPublishedVersion() ){
			if( this.canEdit() ){
				this.selectTab("workingversion",false);
			}
		}
		
		if( $(this.dom).find(".reloadappliance").length > 0 ){
			$(this.dom).find(".reloadappliance").off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.reload();
					return false;
				};
			})(this));
		}
	};
	this.getSubSection = function(){
		return this.options.currentSection;
	};
	this.dispatchRoute = function(){
		var r = appdb.routing.FindRoute();
		var section, vasection, vaid, cls = "";
		if( !r || !r.parameters || !r.parameters.section || r.parameters.imageguid ){
			var crd = appdb.pages.application.currentRouteData();
			if( crd && crd.parameters && crd.parameters.section === "vaversion"){
				r = crd;
			}	
		}
		if( r && r.parameters && r.parameters.section ){
			section = r.parameters.section;
			if( section === "vaversion" ){
				vasection = r.parameters.vasection;
				vaid = r.parameters.vaid;
			}
		}
		if( vasection === this.options.selectedVersion) {
			if( !(vasection === "previous" && this.options.selectedId !== vaid) ){
				return;
			}
		}
		
		if( vasection){
			cls = "latestversion";
			switch($.trim(vasection).toLowerCase()){
				case "latest":
					cls = "latestversion";
					break;
				case "working":
					cls = "workingversion";
					break;
				case "previous":
					cls = "previousversions";
					break;
				default:
					cls = "";
					break;
			}
		}else{
			var res = $.grep(this.options.components, function(e){
				return (e.obj && e.obj.canRender && e.obj.canRender() );
			});
			if( res.length > 0 ){
				cls = res[0].obj.getName();
			}else{
				cls = "";
			}
		}
		this.options.selectedVersion = $.trim(vasection).toLowerCase();
		this.options.selectedId = vaid;
		if( cls ){
			this.selectTab(cls);
			if(cls === "previousversions" && vaid){
				var cont = this.getContainer("previousversions");
				if( cont !== null ){
						cont.selectItemById(vaid);
					}
				}
		}else{
			this.selectTab(0);
		}
	};
	this.selectTab = function(name, updateroute){
		name = (typeof name.getName === "function")?name.getName:name;
		updateroute = (typeof updateroute === "boolean")?updateroute:false;
		
		var selDiv, selLi;
		if( typeof name === "number"){
			selLi = $(this.dom).children(".vappliance").find("ul > li").get(name);
			selDiv = $(selLi).children("a:first").attr("href");
		}else{
			selLi = $(this.dom).children(".vappliance").find("ul > li." + name);
			selDiv = $(selLi).children("a:first").attr("href");
		}
		
		var ditem = $(this.dom).children(".vappliance").find("ul > li." + name);
		if( ditem.length === 0  || $(ditem).hasClass("hidden") ){
			return;
		}
		$(this.dom).children(".vappliance").find("ul > li").removeClass("current");
		$(this.dom).children(".vappliance").children("div").addClass("hiddengroup");
		
		$(selLi).addClass("current");
		$(selDiv).removeClass("hiddengroup");
		var crd = appdb.pages.application.currentRouteData();
		if( crd && crd.parameters && crd.parameters.section === "vaversion" && crd.parameters.imageguid ){
			switch(name){
				case "latestversion":
					if( crd.parameters.vasection !== "latest"){
						crd.parameters.vasection = "latest";
						delete crd.parameters.imageguid;
					}
					break;
				case "workingversion":
					if( crd.parameters.vasection !== "working" ){
						crd.parameters.vasection = "working";
						delete crd.parameters.imageguid;
					}
					break;
				case "previousversion":
					if( crd.parameters.vasection === "previous" ){
						crd.parameters.vasection = "previous";
						delete crd.parameters.imageguid;
					}
					break;
				default:
					break;
			}
			appdb.pages.application.currentRouteData(crd);
		}
		appdb.vappliance.ui.CurrentVAVersionSelectionRegister.selectionChanged(name);
	};
	this.hideView = function(view){
		if( !view ) return;
		var name = (view.getName)?view.getName():view.getName;
		if( !name ) return;
		$(this.dom).children(".vappliance").find("ul > li." + name + ",div." + name).addClass("hidden");
	};
	this.showView = function(view){
		if( !view ) return;
		var name = (view.getName)?view.getName():view.getName;
		if( !name ) return;
		$(this.dom).children(".vappliance").find("ul > li." + name + ",div." + name).removeClass("hidden");
	};
	this.getSoftwareData = function(){
		return this.options.software;
	};
	this.showLoading = function(display){
		display = (typeof display === "boolean")?display:false;
		$(this.dom).find(".loader.emptycontent").hide();
		if( display ){
			$(this.dom).find(".loader.emptycontent").removeClass("hidden").show();
		}
	};
	this.getOwner = function(){
		var app = this.getSoftwareData();
		var d = app.application || app;
		if( d.owner ){
			return d.owner;
		}
		return d.addedby;
	};
	this.canEdit = function(){
		if( !userID ) return false;
		var perms = appdb.pages.application.currentPermissions();

		if( perms ){
			return perms.canManageVirtualAppliance() || false;
		}
		if( appdb.pages.application.userIsContactPoint(userID) ){
			return true;
		}
		return false;
	};
	this.createNewVersion = function(){
		this.views.vmversion.options.data = {instance: {id: "",version:"",notes:"",image:[]}};
		this.edit();
	};
	this.renderEmpty = function(){
		this.showLoading(false);
		$(this.dom).children(".vappliance").addClass("hidden");
		$(this.dom).children(".noappliance.emptycontent").removeClass("canEdit").removeClass("hidden").show();
		$(this.dom).children(".noappliance.emptycontent").find(".actions").hide();
		if( this.canEdit() === true ){
			$(this.dom).children(".noappliance.emptycontent").addClass("canEdit").find(".actions").show();
			$(this.dom).children(".noappliance.emptycontent").find(".actions > .action.createupdate").addClass("hidden");
			$(this.dom).children(".noappliance.emptycontent").find(".actions > .action.createnew").addClass("hidden");
			var lpv = this.getLatestPublishedVersion();
			if( lpv !== null ){
				$(this.dom).children(".noappliance.emptycontent").find(".actions > .action.createupdate").removeClass("hidden");	
			}else{
				$(this.dom).children(".noappliance.emptycontent").find(".actions > .action.createnew").removeClass("hidden");
			}
			$(this.dom).children(".noappliance.emptycontent").find(".actions > .action.createnew").off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.createNewVersion();
					return false;
				};
			})(this));
		}
	};
	this.reload = function(id){
		this.checkUnsavedData((function(self,vid){
			return function(){
				var undef;
				appdb.pages.application.checkUserAccessTokens(function() {
				    self.load(undef, id);
				});
			};
		})(this,id));
	};
	this.load = function(d,vid){
		vid = $.trim(vid);
		appdb.vappliance.ui.CurrentVAVersionSelectionRegister.clear();
		if( typeof d === "undefined" ){
			d = {id: this.getSWId() };
		}
		if( $.isPlainObject(d) === false){
			d = {id: d};
		}
		if( this._model ){
			this._model.unsubscribeAll();
			this._model.destroy();
			this._model = null;
		}
		this._model = new appdb.model.VirtualAppliance();
		this._model.subscribe({event: "beforeselect", callback: function(v){
				$(this.dom).find(".reloadappliance").addClass("hidden");
				$(this.dom).children("div").addClass("hidden");
				this.showLoading(true);
		}, caller: this});
		this._model.subscribe({event: "select", callback: function(v){
				if( v && v.appliance ){
					this.options.currentData = v.appliance;
					this.options.currentData.instance = this.options.currentData.instance || [];
					this.options.currentData.instance = $.isArray(this.options.currentData.instance)?this.options.currentData.instance:[this.options.currentData.instance];
				}else{
					this.options.currentData = undefined;
				}
				var postSelect = function() {
				    this.showLoading(false);
				    this.render(this.options.currentData);
				    if(vid!==""){
					    this.selectVersionById(vid);
				    }
				    $(this.dom).find(".reloadappliance").removeClass("hidden");
				    this.initContextualizationRegistry();
				    this.loadCDHandler(d);
				    this.publish({event: "load", value: this});
				}.bind(this);

				this.checkLatestImageAccessibility(this.options.currentData, postSelect);
		}, caller: this});
		this._model.get(d);
	};
	this.checkLatestImageAccessibility = function(data, cb) {
	    var shouldCheck = false;
	    var statusPerms = appdb.config.views.application.checkVMIAccessibilityViewStatusPermissions || {"error": "all", "warning": "all"};

	    if (appdb.config.views.application.checkVMIAccessibility === true) {
		var cd = data || {};
		var latest = null;

		if (cd.instance) {
		    $.each(cd.instance, function(index, i) {
		       if (latest === null && i.published === 'true' && i.archived === 'false') {
			   latest = i;
		       }
		    });
		}

		if (latest && latest.image && latest.image.instance && latest.image.instance.url) {
		    shouldCheck = true;
		}
	    }

	    if (shouldCheck) {
		$('.vappliance-accessibility').removeClass('error warning');
		$.get(appdb.config.endpoint.base + 'apps/isvmiaccessible?id=' + appdb.pages.Application.currentId(), function(data) {
		    if (data && data.status !== 'ok') {
			var perms = statusPerms[data.status] || "all";
			var canView = userIsAdmin;

			switch(perms) {
			    case "owner":
				canView = canView || appdb.pages.application.currentUserIsOwner();
				break;
			    case "contacts":
				canView = canView  || appdb.pages.application.isContactPoint() || appdb.pages.application.currentUserIsOwner();
				break;
			    case "none":
				break;
			    case "authenticated":
				canView = (!!userID) ? true : false;
				break;
			    case "all":
			    default:
				canView = true;
				break;
			}

			if (canView) {
			    $('.vappliance-accessibility').find('.response').text(data.message);
			    $('.vappliance-accessibility').addClass(data.status);
			}
		    }
		    cb();
		});
	    } else {
		cb();
	    }
	};
	this.getCDData = function(data) {
	    data = data || {};

	    if (data.cdFlowId) {
		return data;
	    }
	    if (data.software) {
		data = (data.software || {}).application || {};
	    }

	    data.cd = data.cd || {};
	    data.cd.paused = ($.trim(data.cd.paused) === 'true') ? true : false;
	    data.cd.enabled = ($.trim(data.cd.enabled) === 'true') ? true : false;

	    return data.cd;
	};
	this.loadCDHandler = function(application, options) {
		options = options || {};
		var page = appdb.pages.application;
		if (appdb.config.features.cd === false) {
			return;
		}
		application = application || {};
		 //var data = ((vappliancedata.appliance || {}).cd) || ((vappliancedata.cdFlowId) ? vappliancedata : {});
		var data = this.getCDData(application);//((application.cd) || ((application.cdFlowId) ? application : {}));
		var owner = (page.getOwner() ||{});
		var userPerms = page.currentPermissions();
		var isContact = page.isContactPoint();
		var hasAccessToken = (typeof options.hasAccessToken !== 'undefined' && options.hasAccessToken !== null) ? options.hasAccessToken : userHasPersonalAccessTokens;
		var hasPermissions = (typeof options.canManageVA !== 'undefined') ? options.canManageVA : null;
		if (hasPermissions === null) {
		    if (page.currentPermissions() !== null){
			hasPermissions = page.currentPermissions().canManageVirtualAppliance();
		    } else {
			hasPermissions = false;
		    }
		}

		var canManageVA = (hasPermissions && hasAccessToken);
		var canView = canManageVA || isContact || userIsAdminOrManager || false;
		var canHandle = canManageVA || false;
		var handler = $("#navdiv" + page.currentDialogCount() + " .action.cd_select");

		var _setupForm = function(data) {
			$(handler).removeClass('loading').removeClass('enabling').removeClass('disabling');
			if (canView) {
			    $(handler).removeClass('hidden');
			    if ($.trim(data) && $.trim(data.enabled) === 'true') {
				    $(handler).addClass('enabled');
			    } else {
				    $(handler).removeClass('enabled');
			    }

			    if (hasAccessToken === false) {
				$(handler).addClass('no-token');
			    } else {
				$(handler).removeClass('no-token');
			    }

			    if (hasPermissions === false) {
				    $(handler).addClass('no-permissions');
			    } else {
				    $(handler).removeClass('no-permissions');
			    }

			    if(canHandle) {
				    $(handler).addClass('canHandle');
			    } else {
				    $(handler).removeClass('canHandle');
			    }

			    var ownerLink = $('<a href="'+ appdb.config.endpoint.base+ 'store/person/'+owner.cname+'" title="Click to view owner\'s profile" target="_blank"></a>').text(owner.firstname + ' ' + owner.lastname);
			    $(handler).find('.footer .message .owner').empty().append(ownerLink);
		     } else {
			    $(handler).addClass('hidden');
		     }
		};

		_setupForm(data);
		$(handler).find('.userpreferences').attr('href', appdb.config.endpoint.base + 'store/person/' + userCName + '/preferences').attr('target', '_blank');
		$(handler).find('button.cd_action_enable').off('click').on('click', function() {
			$(handler).addClass('loading').addClass('enabling').removeClass('disabling');

			$.ajax({
			    "type": 'POST',
			    url: appdb.config.endpoint.base + 'apps/cd?appid=' + page.currentId(),
			    data: JSON.stringify({
				    _action: 'update',
				    enabled: true,
				    paused: true
			    }),
			    success: function(cddata, textStatus, jqXHR) {
				    $(handler).removeClass('loading').removeClass('enabling').removeClass('disabling');
				    _setupForm(cddata);
				    appdb.pages.Application.reload();
			    }.bind(this),
			    error: function( jqXHR, textStatus, errorThrown) {
				    $(handler).removeClass('loading').removeClass('enabling').removeClass('disabling');
				    _setupForm(application);
				    var errText = errorThrown + ' (' + textStatus + ')';
				    try { resp = JSON.parse(jqXHR.responseText); errText = resp.error || errText;} catch(e) {}
				    var err = new appdb.views.ErrorHandler();
				    err.handle({
					    "status": "Could not enable continuous delivery",
					    "description": errText
				    });
				    if (errText.indexOf('Access Denied.') > -1 && errText.indexOf('permissions') > -1) {
					appdb.pages.application.reload();
				    } else {
					appdb.pages.application.checkUserAccessTokens(function() {
					    this.loadCDHandler(application);
					}.bind(this));
				    }
			    }.bind(this)
			});
		}.bind(this));

		$(handler).find('button.cd_action_disable').off('click').on('click', function() {
			$(handler).addClass('loading').addClass('disabling').removeClass('enabling');
			$.ajax({
				"type": 'POST',
				url: appdb.config.endpoint.base + 'apps/cd?appid=' + page.currentId(),
				data: JSON.stringify({
					_action: 'update',
					enabled: false,
					paused: true
				}),
				success: function(cddata, textStatus, jqXHR) {
					$(handler).removeClass('loading').removeClass('enabling').removeClass('disabling');
					_setupForm(cddata);
					appdb.pages.Application.reload();
				}.bind(this),
				error: function( jqXHR, textStatus, errorThrown) {
					$(handler).removeClass('loading').removeClass('enabling').removeClass('disabling');
					 _setupForm(application);
					var errText = errorThrown + ' (' + textStatus + ')';
					try { resp = JSON.parse(jqXHR.responseText); errText = resp.error || errText;} catch(e) {}
					var err = new appdb.views.ErrorHandler();
					err.handle({
						"status": "Could not enable continuous delivery",
						"description": errText
					});
				}
			});
		}.bind(this));
	};
	this.getVersionById = function(id){
		id = $.trim(id);
		if( id === "" ) return null;
		var d = this.options.currentData || {};
		d.instance = d.instance || [];
		d.instance = $.isArray(d.instance)?d.instance:[d.instance];
		var res = $.grep(d.instance, function(e){
			return $.trim(e.id) === id;
		});
		return (res.length===0)?null:res[0];
	};
	this.selectVersionById = function(id){
		var ver = this.getVersionById(id);
		if( !ver ) return;
		if( $.trim(ver.published) === "true" && $.trim(ver.archived) === "false"){
			$(this.dom).children(".groupcontainer").find("ul > li.latestversion > a").trigger("click");
		}else if($.trim(ver.published) === "true" && $.trim(ver.archived) === "true"){
			$(this.dom).children(".groupcontainer").find("ul > li.previousversions > a").trigger("click");
		}else{
			$(this.dom).children(".groupcontainer").find("ul > li.workingversion > a").trigger("click");
		}
	};
	this.getCurrentModel = function(){
		return this._model;
	};
	this.setEditMode = function(enable,obj){
		obj = obj || null;
		enable = (typeof enable === "boolean")?enable:false;
		if( enable === this.options.editMode ) return;
		if( enable === true ){
			appdb.utils.DataWatcher.Registry.activate("vappliance");
			this.options.editObject = obj;
		}else{
			appdb.utils.DataWatcher.Registry.deactivate("vappliance");
			if( obj !== null && typeof obj.cancel === "function" ){
				obj.cancel();
			}
			this.options.editObject = null;
		}
		
		this.options.editMode = enable;
	};
	this.checkUnsavedData = function(callback){
		if( this.isEditMode() === false ){
			if( typeof callback === "function" ){
				callback();
			}
			return false;
		}
		if( appdb.utils.DataWatcher.Registry.checkActiveWatcher() === true ){
			appdb.utils.DataWatcher.Registry.checkActiveWatcherAsync({notify:true,onClose: (function(self,c){
					return function(){
						self.setEditMode(false,self.options.editObject);
						c(); 
					};
			})(this,callback)});
			return true;
		}else{
			this.setEditMode(false,this.options.editObject);
			callback();
			return false;
		}
	};
	this.initContextualizationRegistry = function(){
		if( this.options.contextualizationRegistry ){
			this.options.contextualizationRegistry.reset();
			this.options.contextualizationRegistry = null;
		}
		this.options.contextualizationRegistry = new appdb.vappliance.components.ContextualizationScriptRegistry({
			vappliance: this.options.currentData
		});		
	};
	this.getContextualizationRegistry = function(){
		return this.options.contextualizationRegistry;
	};
	this._initContainer = function(){
		$(this.dom).find(".vappliance-templates").remove();
		if( this.options.isPrivate === false ){
			this.options.canAccessPrivateData = true;
		}
		if( this.options.isPrivate === true){
			$(this.dom).removeClass("ispublic").addClass("isprivate");
		}else{
			$(this.dom).removeClass("isprivate").addClass("ispublic");
		}
		
		if( this.options.canAccessPrivateData === true){
			$(this.dom).addClass("canaccessprivate");
		}else{
			$(this.dom).removeClass("canaccessprivate");
		}
		$(this.dom).addClass("vaprovider");
	};
	this._init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.options.components = $.isArray(this.options.components)?this.options.components:[this.options.components];
		this._initContainer();
		this.initContextualizationRegistry();
	};
	this._init();
});

appdb.vappliance.components.VAppliance = appdb.ExtendClass(appdb.Component,"appdb.vappliance.components.VAppliance", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		name: $.trim(o.name),
		data: null,
		template: "",
		isEditable: (typeof o.editable === "boolean")?o.editable:false,
		inEditMode: false,
		isPrivate: (typeof o.isPrivate === "boolean")?o.isPrivate:false,
		canAccessPrivateData: (typeof o.canAccessPrivateData === "boolean")?o.canAccessPrivateData:true
	};
	this.isPrivate = function(){
		return this.options.isPrivate;
	};
	this.canAccessPrivateData = function(){
		return this.options.canAccessPrivateData;
	};
	this.setEditMode = function(enable){
		enable = (typeof enable === "boolean")?enable:false;
		$(this.dom).removeClass("editmode");
		$(this.dom).addClass("viewmode");
		this.options.inEditMode = enable;
		if( enable ){
			$(this.dom).addClass("editmode");
			$(this.dom).removeClass("viewmode");
		}
	};
	this.getEditMode = function(){
		return this.options.inEditMode;
	};
	this.renderLoading = function(loading,text){
		loading = (typeof loading === "boolean")?loading:false;
		$(this.dom).find(".vappliance-version").children(".loader").remove();
		if( loading ){
			text = text || "saving";
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).find(".vappliance-version").append(loader);
		}
	};
	this.renderVerification = function(){
		//override from working version
	};
	this.canPublish = function(){
		if( this.options.data === null ) return false;
		if( this.isPublished() ) return false;
		if( this.isArchived() ) return false;
		if( this.hasImageInstances() === false ) return false;
		if( this.isUnderVerification() ) return false;
		if( this.needsVerification() ) return false;
		var status = this.getStatus();
		if( $.inArray(status, ["verified","init","ready"]) < 0 ) return false;
		return true;
	};
	this.hasImageInstances = function(){
		var instances = this.getAllVMIInstances();
		return (instances.length === 0 )?false:true;
	};
	this.needsVerification = function(){
		if( this.options.data === null ) return false;
		if( this.isPublished() ) return false;
		if( this.isArchived() ) return false;
		if( this.hasImageInstances() === false ) return false;
		if( this.isUnderVerification() ) return false;
		var status = this.getStatus();
		if( !this.VMIInstanceWithIntegrityCheck() || $.inArray(status, ["init","canceled","failed"]) < 0 ) return false;
		return true;
	};
	this.isUnderVerification = function(){
		return ( $.inArray(this.getStatus(),["verifing","verifingpublish"]) > -1 ); 
	};
	this.getAllVMIInstances = function(){
		var res = [];
		var images = this.options.data.image || [];
		images = $.isArray(images)?images:[images];
		
		$.each(images, function(i, e){
			var instances = e.instance || [];
			instances = $.isArray(instances)?instances:[instances];
			$.each(instances, function(ii, ee){
				res.push(ee);
			});
		});
		return res;
	};
	this.VMIInstanceWithIntegrityCheck = function(){
		var instances = this.getAllVMIInstances();
		var res = $.grep(instances, function(e){
			return (e.integrity === "true" || e.integrity === true );
		});
		
		return ( res.length > 0 && $.inArray(this.getStatus(),["verified","ready"]) < 0 )?true:false;
	};
	this.isPublished = function(){
		var val = (this.options.data !== null && $.trim(this.options.data.published).toLowerCase() )|| "false";
		return  (val==="true")?true:false;
	};
	this.getStatus = function(){
		if( this.options.data === null ) return "init";
		return ( $.trim(this.options.data.status).toLowerCase() || "init" );
	};
	this.isEnabled = function(){
		if ( !this.options.data ){
			return true;
		}
		var val = $.trim(this.options.data.enabled).toLowerCase() || "false";
		return  (val==="true")?true:false;
	};
	this.setEnabled = function(enabled){
		enabled = (typeof enabled === "boolean")?enabled:true;
		this.options.data.enabled = enabled;
	};
	this.isArchived = function(){
		var val = (this.options.data !== null && $.trim(this.options.data.archived).toLowerCase() ) || "false";
		return  (val==="true")?true:false;
	};
	this.showToolbar = function(display){
		display = (typeof display === "boolean")?display:true;
		$(this.dom).children(".toolbar").addClass("hidden");
		if( display ){
			$(this.dom).children(".toolbar").removeClass("hidden");
		}
	};
	this.getData = function(){
		var res = {};
		if( this.views.vaversion ){
			res = this.views.vaversion.getData();
		}
		return res;
	};
	this.canRender = function(){
		if( !this.options.data ){
			return false;
		}
		return true;
	};
	this.isEmpty = function(){
		if( !this.options.data || ($.isArray(this.options.data) && this.options.data.length === 0)){
			return true;
		}
		return false;
	};
	this.canEdit = function(){
		if( userID === null ) return false;
		var perms = appdb.pages.application.currentPermissions();
		return this.options.isEditable && perms && perms.canManageVirtualAppliance();
	};
	this.getName = function(){
		return this.options.name;
	};
	this.render = function(d){
		d = d || this.options.data;
		this.views.vaversion.render(d);
		this.postRender();
	};
	this.preRender = function(d){
		d = d || this.options.data;
		if( !this.canRender() ){
			this.publish({event:"norender", value: this});
			return false;
		}else{
			this.publish({event:"dorender", value: this});
		}
		if( this.isEmpty() ){
			this.publish({event:"empty", value: this});
		}
		
		if( this.canEdit() ){
			$(this.dom).addClass("canedit");
		}else{
			$(this.dom).removeClass("canedit");
		}
		this.setEditMode(false);
		return true;
	};
	this.postRender = function(){
		if( this.isPublished() ){
			$(this.dom).find(".imagelistlink").attr("href", appdb.config.endpoint.vmcaster + "store/vappliance/" + appdb.pages.application.currentCName() + "/image.list");
		}else{
			$(this.dom).find(".imagelistlink").attr("href", appdb.config.endpoint.vmcaster + "store/vappliance/" + appdb.pages.application.currentCName() + "/unpublished/image.list");
		}
		if( this.isPrivate() ){
			$(this.dom).find(".imagelistlink > img").attr("src","/images/logout3.png");
		}
		$(this.dom).removeClass("canpublish");
		if( this.canPublish() ){
			$(this.dom).addClass("canpublish");
		}
		
		$(this.dom).removeClass("needverification");
		if( this.needsVerification() ){
			$(this.dom).addClass("needverification");
		}
		
		$(this.dom).removeClass("verifing");
		if( this.isUnderVerification() ){
			$(this.dom).addClass("verifing");
		}else{
			$(this.dom).children(".toolbar").find(".action.verifing").remove();
		}
		
		$(this.dom).removeClass("published");
		if( this.isPublished() ){
			$(this.dom).addClass("published");
		}
		$(this.dom).removeClass("enabled");
		$(this.dom).removeClass("disabled");
		if( this.isEnabled() === true ){
			$(this.dom).addClass("enabled");
		}else{
			$(this.dom).addClass("disabled");
		}
		
		$(this.dom).removeClass("canedit");
		if( this.canEdit() === true){
			$(this.dom).addClass("canedit");	
		}
		this.selectGuid();
		appdb.vappliance.ui.CurrentVAVersionSelectionRegister.register(this);
	};
	this.selectGuid = function(){
		var crd = appdb.pages.application.currentRouteData();
		if( crd && crd.parameters && crd.parameters.section === "vaversion" && crd.parameters.imageguid ){
			if($.trim(this.options.name).toLowerCase() === "previousversions" || $.trim(this.options.name).toLowerCase() === "latestversion" ){
				if( $(this.dom).hasClass("hiddengroup") === false ){
					setTimeout((function(self,d,name){
						return function(){
							var cdata = self.options.data || {};
							cdata = ( $.isArray(cdata) && cdata.length > 0 )?cdata[0]:cdata;
							
							$(self.dom).find(".property.vaversion-identifier > .value").each(function(i,e){
								if( $.trim($(this).text()) === d.parameters.imageguid ){
									var closest = $(this).closest(".vaversion-vmiversion.property");
									$(closest).addClass("selectedguid");
									var top = $(closest).offset().top;
									var threshold = 0;
									if(name === "previousversions"){
										threshold = 80;
									}
									if( top > threshold ){
										top = top - threshold;
									}else{
										top = 0;
									}
									window.scrollTo(0,top);
								}
							});
						};
					})(this,crd,$.trim(this.options.name)), 700);
				}
			}
		}else{
			$(this.dom).find(".selectedguid").removeClass("selectedguid");
		}
	};
	this.reRender = function(){
		this.selectGuid();
	};
	this.filterData = function(d){
		return d;
	};
	this.load = function(d,status){
		this.options.unfilteredData = Object.assign({}, d || {});
		this.options.data = this.filterData(d);
		if( $.trim(status) !==  "" && this.options.data){
			switch(status){
				case "verify":
					this.options.data.status = "verifing";
					break;
				case "verifypublish":
					this.options.data.status = "verifingpublish";
					break;
			}
		}
		if( this.preRender() !== false ){
			this.render(this.options.data);
		}		
	};
	this.initContainer = function(){
		if( !this.parent ) return;
		var temp = this.parent.getTemplate($(this.dom).data("usetemplate"));
		if( !temp ) return;
		temp = $(temp);
		$(this.dom).append(temp);
		this.options.template = temp;
		$(this.dom).addClass("vappliance");
	};
	this._postInit = function(){
		//to be overriden
	};
	this._init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
		this.views.vaversion = new appdb.vappliance.ui.views.VApplianceVersion({
			container: $(this.options.template),
			parent: this,
			editable: this.canEdit(),
			data: this.options.data
		});
	};
	this._init();
});

appdb.vappliance.components.LatestVersion = appdb.ExtendClass(appdb.vappliance.components.VAppliance, "appdb.vappliance.components.LatestVersion", function(o){
	this.canRender = function(){
		return ( this.options.data !== null && this.options.data.published === "true" && this.options.data.archived === "false");
	};
	this.filterData = function(d){
		d = d || {};
		d.instance = d.instance || [];
		d.instance = $.isArray(d.instance)?d.instance:[d.instance];
		var canedit = this.canEdit();
		var v = $.grep(d.instance, function(e){
			if( e.published === "true" && e.archived==="false" ){
				if( e.enabled === "false"){
					return canedit;
				}else{
					return true;
				}
			}
		});
		return (v.length>0)?v[0]:null;
	};
	this.disableVersion = function(){
		var data = $.extend(this.options.data,{}, true);
		if( !data || data.enabled == "false" ){
			return;
		}
		data.enabled = "false";
		this.saveVersion(data);
	};
	this.enableVersion = function(){
		var data = $.extend(this.options.data,{}, true);
		if( !data || data.enabled == "true" ){
			return;
		}
		data.enabled = "true";
		this.saveVersion(data);
	};
	this.postSave = function(data){
		if( data ){
			this.load(data.appliance);
		}
		this.renderToolbar(true);
		this.renderLoading(false);
		if( data && data.appliance ){
			appdb.pages.application.renderVApplianceDownloadPanel(this.filterData(data.appliance));
		}
		
	};
	this.saveVersion = function(data){
		var loadingtext = ( data.enabled === "true" )?"Enabling published version":"Disabling published version";
		this.renderToolbar(false);
		this.renderLoading(true,loadingtext);
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.resetRegister();
		var colData = {id: ( this.parent.getId() || "" ), appid:this.parent.getSWId(), instance:[data]};
		var modelOpts = {id: colData.appid, vaid: colData.id};
		var mapper = new appdb.utils.EntityEditMapper.VirtualAppliance();
		appdb.debug(colData);
		mapper.UpdateEntity(colData);
		var xml = appdb.utils.EntitySerializer.excludeElements([]).toXml(mapper.entity);
		appdb.debug("Sending :",xml);
		
		if( this.model ){
			this.model.unsubscribeAll();
			this.model.destroy();
			this.model = null;
		}
		
		this.model = new appdb.model.VirtualAppliance(modelOpts);
		this.model.subscribe({event: "update", callback:function(d){
			//Called upon success of an virtual appliance update
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot " +  ((data.enabled === true)?"enable":"disable") + " virtual appliance version ",
					"description": d.errordesc
				});
				this.postSave(undefined);
			}else{
				this.postSave(d);
			}			
		}, caller: this}).subscribe({event: "error", callback: function(d){
			//Called upon ajax error of both virtual appliance update or insert action. E.g. HTTP 500 - Internal Server Error
		}, caller: this});

		//Determine type of request and perform it.
		this.model.update({query: modelOpts, data: {data: encodeURIComponent(xml)}});	
	};

	this.renderToolbar = function(display){
		$(this.dom).find(".toolbar > .actions > .action.disable").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.disableVersion();
				return false;
			};
		})(this));
		
		$(this.dom).find(".toolbar > .actions > .action.enable").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.enableVersion();
				return false;
			};
		})(this));
		display = ( typeof display === "boolean" )?display:true;
		if( display === true ){
			$(this.dom).children(".toolbar").removeClass("hidden");
		}else{
			$(this.dom).children(".toolbar").addClass("hidden");
		}
		$(this.dom).find(".toolbar > .actions > .action > .description").off("click").on("click", function(ev){
			ev.preventDefault();
			return false;
		});
	};
	this.render = function(d){
		d = d || this.options.data;
		this.views.vaversion.render(d);
		this.postRender();
		this.renderToolbar(true);
	};
});

appdb.vappliance.components.PreviousVersions = appdb.ExtendClass(appdb.vappliance.components.VAppliance, "appdb.vappliance.components.PreviousVersions", function(o){
	this.filterData = function(d){
		d = d || {};
		d.instance = d.instance || [];
		d.instance = $.isArray(d.instance)?d.instance:[d.instance];
		var res = $.grep(d.instance, function(e){
			return e.archived === "true";
		});
		return (res.length>0)?res:null;
	};
	this.renderItem = function(item){
		$(this.dom).children(".archivedversion").fadeOut("fast", (function(self){
			return function(){
				self.resetVersion();
				if( !item) return;
				self.options.archivedVersion = new appdb.vappliance.components.VAppliance({
					parent: self.parent,
					container: $(self.dom).children(".archivedversion"),
					editable: false
				});
				self.options.archivedVersion.render(item);
				$(this).fadeIn("fast", function(){
					$(this).removeAttr("style");
				});
			};
		})(this));
	};
	this.resetVersion = function(){
		if( !!this.options.archivedVersion ){
			this.options.archivedVersion.destroy();
			this.options.archivedVersion = null;
		}
		
		var cont = $(this.dom).children(".archivedversion");
		$(cont).empty();
		$(this.dom).removeClass("empty");
	};
	this.renderEmpty = function(display){
		display = (typeof display === "boolean")?display:true;
		$(this.dom).removeClass("empty");
		if( display === true ){
			$(this.dom).addClass("empty");
			this.options.selector.renderNotFound();
		}
	};
	this.selectItemById = function(id){
		var d = $.grep(this.options.data, function(e){
			return ( (id<<0) === (e.id<<0) );
		});
		if( d.length === 0 ){
			this.renderEmpty(true);
			return;
		}
		this.options.selector.selectId(id);
	};
	this.updateRouteSection = function(id){
		if( appdb.vappliance.ui.CurrentVAManager.options.selectedVersion !== "previousversion" && appdb.vappliance.ui.CurrentVAManager.options.selectedVersion !== "previous")return;
		var title = "Virtual appliance archived version " + (id || appdb.vappliance.ui.CurrentVAManager.options.selectedId); 
		var sname = appdb.pages.application.currentCName(), curl = "/store/vappliance/" + sname + "/vaversion/previous/" + (id || appdb.vappliance.ui.CurrentVAManager.options.selectedId);
		if( curl ) {
			curl = curl.toLowerCase();
			appdb.Navigator.setInternalMode(true);
			appdb.Navigator.currentHistoryState.data = appdb.Navigator.currentHistoryState.data || {};
			appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data,title,curl);
			appdb.Navigator.setInternalMode(false);
		}
			
	};
	this.render = function(d){
		d = d || this.options.data;
		this.renderEmpty(false);
		if( !this.options.selector ){
			this.options.selector = new appdb.vappliance.ui.views.VersionSelector({
				container: $(this.dom).children(".header"),
				parent: this,
				data: d
			});
		}
		this.options.selector.subscribe({ event: "select", callback: function(v){
				this.renderItem(v.data);
				$("#vappliancediv" + appdb.pages.application.currentDialogCount() + " > .vappliance.groupcontainer > ul > li.previousversions > a").attr("data-vaid",v.data.id);
				if((appdb.vappliance.ui.CurrentVAManager.options.selectedId<<0) !== (v.data.id<<0) ){
					appdb.vappliance.ui.CurrentVAManager.options.selectedId = v.data.id;
					this.updateRouteSection();
				}
				
		}, caller: this});
		this.options.selector.render(d);
		$(this.dom).addClass("vappliance");
		this.resetVersion();
		if( (appdb.vappliance.ui.CurrentVAManager.options.selectedVersion === "previousversion" || appdb.vappliance.ui.CurrentVAManager.options.selectedVersion === "previous") && appdb.vappliance.ui.CurrentVAManager.options.selectedId ){
			this.selectItemById(appdb.vappliance.ui.CurrentVAManager.options.selectedId);
		}else{
			this.options.selector.selectIndex(0);
		}
		var sel = this.options.selector.current();
		if( sel ){
			if( (sel.id<<0) !== (appdb.vappliance.ui.CurrentVAManager.options.selectedId<<0)){
				this.updateRouteSection(sel.id);
			}
		}
		this.postRender();
	};
	
});

appdb.vappliance.components.WorkingVersion = appdb.ExtendClass(appdb.vappliance.components.VAppliance, "appdb.vappliance.components.WorkingVersion", function(o){
	this.filterData = function(d){
		d = d || {};
		d.instance = d.instance || [];
		d.instance = $.isArray(d.instance)?d.instance:[d.instance];
		var res = $.grep(d.instance, function(e){
			return e.published === "false" && e.archived==="false";
		});
		return (res.length>0)?res[0]:null;
	};
	this.canRender = function(){
		return this.canEdit();
	};
	this.IsEmpty = function(){
		if( this.canEdit() === false ) return true;
		if( !this.options.data || ($.isArray(this.options.data) && this.options.data.length === 0) ){
			return true;
		}
		return false;
	};
	this.collectData = function(){
		if( !this.options.data ){
			this.options.data = {id:  ""};
		}
		var res = {id: ( this.parent.getId() || "" ), appid:this.parent.getSWId(), instance:[this.getData()]};
		res.instance[0].status = 'init';
		return res;
	};
	this.postSave = function(d,action){
		this.renderLoading(false);
		appdb.vappliance.ui.CurrentVAManager.setEditMode(false);
		if(typeof d !== "undefined"){
			this.setEditMode(false);
			var data = d.appliance || d;
			data = data || {};
			if( this.parent && this.parent.options && !this.parent.options.currentData ){
				//in case of first version, set appropriate information to parent 
				if( data.id ){
					this.parent.options.currentData = {id: data.id, appid: data.appid };
				}
			}
			data.instance = data.instance || [];
			data.instance = $.isArray(data.instance)?data.instance:[data.instance];
			var thisid = this.options.data.id;
			var thisinst = null;
			if( data.instance.length > 0 ){
				thisinst = $.grep(data.instance, function(e){
					return (e.id === thisid);
				});
				if( thisinst.length > 0 ){
					thisinst = thisinst[0];
				}
			}
			
			if( (thisinst !== null && thisinst.published === "true" ) ){
				appdb.pages.application.renderVApplianceDownloadPanel(thisinst);
				appdb.vappliance.ui.CurrentVAManager.renderContainer("latestversion", data, true);
				appdb.vappliance.ui.CurrentVAManager.reload();
			}
			this.load(d.appliance || d, action);
		}else{
			//something went wrong
			this.showToolbar(true);
			this.setEditMode(true);
			this.edit();
		}
	};
	this.save = function(){
		this.showToolbar(false);
		this.renderLoading(true, "saving new version");
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.resetRegister(false);
		this.views.vaversion.save();
		this.saveData();
	};
	this.edit = function(){
		this.setEditMode(true);
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.initRegister(this);
		this.views.vaversion.edit();
		appdb.vappliance.ui.CurrentVAManager.setEditMode(true,this);
	};
	this.onCancelClick = function(){
		appdb.vappliance.ui.CurrentVAManager.checkUnsavedData((function(self){
			return function(){
				appdb.vappliance.ui.CurrentVAManager.setEditMode(false,self);
			};
		})(this));		
	};
	this.updateContextScripts = function(){
		var registry = appdb.vappliance.ui.CurrentVAManager.getContextualizationRegistry();
		if( registry ){
			registry.clearByRefPrefix("idx_");
		}
		if( !this.isEmpty() && this.options.data && $.trim(this.options.data.id) !== "" && this.options.data.image && $.isArray(this.options.data.image) && this.options.data.image.length > 0 ){
			$.each(this.views.vaversion.options.data.image, function(imgi, img){
				if( !$.isArray(img.instance) || img.instance.length === 0 ){
					return;
				}
				$.each(img.instance, function(i,e){
					if( $.isArray(e.contextscript) ){
						if( e.contextscript.length > 0 ){
							e.contextscript = e.contextscript[0];
						}else{
							delete e.contextscript;
						}
					}
					if( e.contextscript ){
						var script = registry.getScript(e.contextscript);
						if( script === null ){
							//if script no longer available
							//check if replaced for this ref vmi
							var refscript = registry.getByRef(e.id);
							if( refscript.length >0 ){
								e.contextscript = $.extend(true,{},refscript[0]);
								delete e.contextscript.refs;
							}else {
								//script removed and is not replaced 
								delete e.contextscript;
							}
						}else{
							//Check if script is still registered 
							//to vmi instance. If not remove.
							if( registry.isRegisteredWith(script, e.id) === false ){
								delete e.contextscript;
							}
						}
					}else{
						//check if a new script is registered 
						//for this vmoi instance
						var refscript = registry.getByRef(e.id);
						if( refscript.length >0 ){
							e.contextscript = $.extend(true,{},refscript[0]);
							delete e.contextscript.refs;
						}
					}
				});
			});
		}
		
	};
	this.cancel = function(){
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.resetRegister();
		appdb.utils.DataWatcher.Registry.deactivate("vappliance");
		this.updateContextScripts();
		this.setEditMode(false);
		this.views.vaversion.cancel();
		if( this.isEmpty() || !this.options.data || $.trim(this.options.data.id) === ""){
			this.views.vaversion.options.data = null;
			this.renderEmpty(true);
		}
	};
	this.cancelCheck = function(){
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.resetRegister();
		this.views.vaversion.save();
		var colData = this.collectData();
		var instance = ($.isArray(colData.instance)?colData.instance:[colData.instance]);
		if( instance.length >0 ){
			instance[0].status = "init";
		}
		this.saveData(colData, "cancelcheck");
	};
	this.publishVersion = function(verify){
		verify = (typeof verify === "boolean" )?verify:false;
		this.showToolbar(false);
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.resetRegister();
		this.views.vaversion.save();
		var colData = this.collectData();
		var instance = ($.isArray(colData.instance)?colData.instance:[colData.instance]);
		if( instance.length >0 ){
			instance[0].published = true;
			if( verify === true ){
				instance[0].status = 'verifypublish';
			}else{
				instance[0].status = 'init';
			}
		}
		if( verify === true ){
			this.saveData(colData,"verifypublish");
		}else{
			this.saveData(colData,"publish");
		}
	};
	this.verifyVersion = function(){
		this.showToolbar(false);
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.resetRegister();
		this.views.vaversion.save();
		var colData = this.collectData();
		var instance = ($.isArray(colData.instance)?colData.instance:[colData.instance]);
		if( instance.length >0 ){
			instance[0].status = 'verify';
		}
		this.saveData(colData,"verify");
	};
	this.postDelete = function(success){
		success = (typeof success === "boolean")?success:true;
		this.renderLoading(false);
		this.showToolbar(true);
		if( success ){
			this.parent.reload();
		}
	};
	this._deleteVersion = function(){
		this.showToolbar(false);
		this.renderLoading(true, "Deleting version");
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.resetRegister();
		var colData = this.collectData();
		var vid = this.views.vaversion.getData().id;
		var modelOpts = {id: colData.appid, vaid: colData.id, versionid: vid};
		if( this.model ){
			this.model.unsubscribeAll();
			this.model.destroy();
			this.model = null;
		}
		this.model = new appdb.model.VirtualAppliance(modelOpts);
		this.model.subscribe({event: "remove", callback:function(d){
			//Called upon success of an virtual appliance update
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot delete virtual appliance version ",
					"description": d.errordesc
				});
				this.postDelete(false);
			}else{
				this.postDelete(true);
			}			
		}, caller: this}).subscribe({event: "error", callback: function(d){
			this.postDelete(false);
		}, caller: this});
	
		//Determine type of request and perform it.
		this.model.remove(modelOpts);
	};
	this.deleteVersion = function(){
		appdb.vappliance.ui.ShowVerifyDialog({
				title: "Virtual Appliance Version Removal",
				message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to delete this version and all related images. Are you sure you want to procced?</span>",
				onOk: (function(self){
					return function(){
						self._deleteVersion();
					};
				})(this)
			});
	};
	this.disableSave = function(disable){
		disable = ( typeof disable === "boolean" )?disable:true;
		var savedom = $(this.dom).find(".toolbar > .actions > .action.save");
		if( disable === true ){
			$(savedom).addClass("disabled");
			$(savedom).find("img").attr("src", "/images/vappliance/warning.png");
		} else {
			$(savedom).removeClass("disabled");
			$(savedom).find("img").attr("src", "/images/diskette.gif");
		}
	};
	this.renderToolbar = function(display){
		$(this.dom).find(".toolbar > .actions > .action.delete").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.deleteVersion();
				return false;
			};
		})(this));
		
		$(this.dom).find(".toolbar > .actions > .action.edit").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.edit();
				return false;
			};
		})(this));
		
		$(this.dom).find(".toolbar > .actions > .action.publish").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.publishVersion();
				return false;
			};
		})(this));
		
		$(this.dom).find(".toolbar > .actions > .action.verify").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.publishVersion(true);
				return false;
			};
		})(this));
		
		$(this.dom).find(".toolbar > .actions > .action.preverify").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.verifyVersion();
				return false;
			};
		})(this));
		$(this.dom).find(".toolbar > .actions > .action.save").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("disabled") === false ){
					self.save(true);
				}
				return false;
			};
		})(this));
		$(this.dom).find(".toolbar > .actions > .action.cancel").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.onCancelClick();
				return false;
			};
		})(this));
		if( typeof(display) === "boolean" ){
			this.showToolbar(display);
		}
		$(this.dom).find(".toolbar > .actions > .action > .description").off("click").on("click", function(ev){
			ev.preventDefault();
			return false;
		});
	};
	this.renderVerification = function(){
		$(this.dom).children(".toolbar").find(".actions .verifing").remove();
		var div = $("<div class='action verifing'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span class='message'>Integrity check is running for this version.</span><span><a class='action cancelcheck' title='' >cancel check</a></span><span class='viewprogress'>view progress</span><div class='integritystatuscontainer'></div></div>");
		$(div).find("a").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.cancelCheck();
				return false;
			};
		})(this));
		$(this.dom).children(".toolbar").find(".actions").append(div);
	};
	this.createNew = function(){
		//Load application abstract or description to prefill va version notes
		var app = (appdb.pages.application.currentData() || {} ).application;
		var _abstract = $.trim(app["abstract"]);
		var descr = $.trim(app.description);
		var cnotes = "";
		if( _abstract.length >= descr.length ){
			cnotes = _abstract;
		}else if(descr.length > 0 ){
			cnotes = descr;
		}
		this.options.data = {notes:cnotes, instance: {id: "",version:"",notes:"",image:[]}};
		this.renderEmpty(false);
		//create new empty group with new empty image
		if( this.views.vaversion ){
			var vmilist = this.views.vaversion.getVmiList();
			if( vmilist ){
				vmilist.addNewItem();
				if( vmilist.subviews && vmilist.subviews.length > 0 ){
					var vmiInstanceList = vmilist.subviews[0];
					if(vmiInstanceList && vmiInstanceList.subviews.vmiversionList){
						vmiInstanceList.subviews.vmiversionList.addNewItem();
					}
				}
			}
		}
		this.edit();
	};
	this.createUpdate = function(){
		var undef;
		var lpv = $.extend(true,{},this.parent.getLatestPublishedVersion());
		var registry = appdb.vappliance.ui.CurrentVAManager.getContextualizationRegistry();
		if( lpv ){
			lpv.id = undef;
			lpv.published = "false";
			lpv.archived = "false";
			lpv.enabled = "true";
			lpv.status = "init";
			lpv.version = undef;
			lpv.createdon = undef;
			lpv.expireson = undef;
			lpv.image = $.isArray(lpv.image)?lpv.image:[lpv.image];
			$.each(lpv.image, function(i,e){
				e.id = undef;
				$.each( e.instance, function(ii,ee){
					var oldid = "" + ee.id;
					ee.parentinstance = oldid;
					ee.id = undef;
					ee.flavourid = undef;
					ee.addedby = undef;
					ee.addedon = undef;
					ee.integrity = "true";
					if( registry ){
						if( $.isArray(ee.contextscript) ){
							if( ee.contextscript.length > 0 ){
								ee.contextscript = ee.contextscript[0];
							}else{
								delete ee.contextscript;
							}
						}
						if( ee.contextscript ){
							//check if contextscript is removed from latest version
							//if not get current script as it might be replaced
							//with new value
							var scripts = registry.getByRef(oldid);
							if( scripts.length > 0 ){
								ee.contextscript = $.extend(true, {}, scripts[0] );
							}else {
								delete ee.contextscript;
							}
						} else {
							//Check if a new contextscript is added in latest version
							var newscripts = registry.getByRef(oldid);
							if( newscripts.length > 0 ){
								ee.contextscript = $.extend(true, {}, newscripts[0] );
							}
						}
						if( $.isArray(ee.contextscript) ){
							if( ee.contextscript.length > 0 ){
								ee.contextscript = ee.contextscript[0];
							}else{
								delete ee.contextscript;
							}
						}
						if( ee.contextscript && ee.contextscript.refs ){
							delete ee.contextscript.refs;
						}
					}else{
						delete ee.contextscript;
					}
					if( appdb.config.features.singleVMIPolicy === true ) {
						ee.prevUrl = ee.url;
					}
				});
			});
			this.options.data = $.extend({},lpv);
			this.renderEmpty(false);
			this.edit();
		}
	};
	this.renderEmpty = function(display){
		display = (typeof display === "boolean")?display:false;
		$(this.dom).children(".emptycontent").removeClass("canEdit").hide();
		if( display ){
			$(this.dom).find(".vappliance-version").addClass("hidden");
			this.showToolbar(false);
			$(this.dom).children(".emptycontent").show();
			if( this.canEdit() ){
				$(this.dom).children(".emptycontent").addClass("canEdit");
				$(this.dom).children(".emptycontent").find(".actions > .action.createupdate").addClass("hidden");
				$(this.dom).children(".emptycontent").find(".actions > .action.createnew").addClass("hidden");
				var lpv = this.parent.getLatestPublishedVersion();
				if( lpv !== null ){
					$(this.dom).children(".emptycontent").find(".actions > .action.createupdate").removeClass("hidden");	
				}else{
					$(this.dom).children(".emptycontent").find(".actions > .action.createnew").removeClass("hidden");	
				}
				$(this.dom).children(".emptycontent").find(".action.createnew").off("click").on("click", (function(self){
					return function(ev){
						ev.preventDefault();
						self.createNew();
						return false;
					};
				})(this));
				$(this.dom).children(".emptycontent").find(".action.createupdate").off("click").on("click", (function(self){
					return function(ev){
						ev.preventDefault();
						self.createUpdate();
						return false;
					};
				})(this));
			}
		}else{
			$(this.dom).find(".vappliance-version").removeClass("hidden");
			this.views.vaversion.render(this.options.data);
			this.renderToolbar(true);
		}
	};
	this.saveData = function(data, action){
		action = action || "save";
		var xml = "";
		var colData = data || this.collectData();
		var modelOpts = {id: colData.appid, vaid: colData.id};
		var mapper = new appdb.utils.EntityEditMapper.VirtualAppliance();
		appdb.debug(colData);
		mapper.UpdateEntity(colData);
		xml = appdb.utils.EntitySerializer.excludeElements([]).toXml(mapper.entity);
		appdb.debug("Sending :",xml);
		
		if( this.model ){
			this.model.unsubscribeAll();
			this.model.destroy();
			this.model = null;
		}
		if( action === "publish"){
			this.renderLoading(true, "Publishing version");
		} else if( action === "cancelcheck" ) {
			this.renderLoading(true, "Canceling integrity check");
		} else if( action === "verifypublish" ){
			this.renderLoading(true, "Verifing and publishing version");
		} else if( action === "verify" ){
			this.renderLoading(true, "Verifing version");
		} else {
			this.renderLoading(true, "Saving version");
		}
		//Determine type of request and perform it.
		var isinsert = false;
		if( !(colData.instance && colData.instance[0].id && colData.instance[0].id > 0) ){
			isinsert = true;
		}
		
		this.model = new appdb.model.VirtualAppliance(modelOpts);
		this.model.subscribe({event: "update", callback:function(d){
			//Called upon success of an virtual appliance update
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot update virtual appliance version ",
					"description": d.errordesc
				});
				this.postSave(undefined,action);
			}else{
				this.postSave(d, action);
			}			
		}, caller: this}).subscribe({event: "insert", callback:function(d){
			//Called upon success of a newly virtual appliance
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": "Cannot register new virtual appliance version ", 
					"description": d.errordesc
				});
				this.postSave();
			}else{
				this.postSave(d);
			}
		}, caller: this}).subscribe({event: "error", callback: function(d){
			//Called upon HTTP error when inserting a new virtual appliance or updating an old one
			var d = (d && d.response) ? d.response : d;
			if(d && d.error){
				(new appdb.views.ErrorHandler()).handle({
					"status": ((isinsert) ? "Cannot register new virtual appliance version " : "Cannot update virtual appliance version "), 
					"description": d.errordesc
				});
				this.postSave();
			}else{
				this.postSave(d);
			}
		}, caller: this});

		//if(mapper.entity.id()==='0' || !mapper.entity.id() ){
		if( isinsert === true ){
			this.model.insert({query: modelOpts, data: {data: xml}});	
		}else{
			this.model.update({query: modelOpts, data: {data: encodeURIComponent(xml)}});	
		}
	};
	this.isCDEnabled = function() {
	    var cd = ((appdb.pages.application.currentData() || {}).application || {}).cd || {};
	    return ($.trim(cd.enabled) === 'true');
	};
	this.render = function(d){
		d = d || this.options.data;
		this.options.data = d || this.options.data;
		appdb.vappliance.components.WorkingVersion.versionInstance = null;
		if (this.isCDEnabled()) {
		    $(this.dom).children('.toolbar').remove();
		    $(this.dom).children('.noworkingversion').remove();
		    $(this.dom).children('.vappliance-version').remove();

		    $(this.dom).closest('.vappliance.groupcontainer').children('ul').find('.vappliance-workingversion-tab-text').text('Continuous Delivery');
		    this.options.cd = new appdb.vappliance.components.CDVersion({
			parent: this.options.parent,
			container: $(this.dom).children('.cdversion'),
			data: this.options.unfilteredData
		    });
		    this.options.cd.render();
		    return;
		}
		if( this.isEmpty() ){
			this.renderEmpty(true);
		}else{
			this.renderEmpty(false);
			appdb.vappliance.components.WorkingVersion.versionInstance = this.views.vaversion;
		}
		this.postRender();
		if( this.isUnderVerification() ){
			this.renderVerification();
		}else{
			$(this.dom).removeClass("verifing");
		}
		if( this.options.integrityChecker ){
			this.options.integrityChecker.destroy();
			this.options.integrityChecker = null;
		}
		this.options.integrityChecker = new appdb.vappliance.components.IntegrityChecker({
			parent: this,
			container: this.dom,
			data: d
		});
		this.options.integrityChecker.load();
	};
	this.onValidationError = function(errs){
		errs = errs || [];
		this.disableSave( ( errs.length > 0 ) );
	};
});

appdb.vappliance.components.CDVersion = appdb.ExtendClass(appdb.vappliance.components.VAppliance, "appdb.vappliance.components.CDVersion", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data,
		cddata: {},
		cdLogSize: 5,
		allowedCdLogSizes: [5, 10, 20, 50, 100],
		actionState: null,
		runningInstanceId: null,
		userExpandedSettings: null,
		userHadPermissions: null,
		userHadAccessToken: null 
	};
	this.hasPermissions = function() {
		var defaultPublisher = this.getDefaultPublisher() || {};
		var status = defaultPublisher.status || {};

		return status.canManageVA || false;
	};
	this.hasAccessToken = function() {
	    var defaultPublisher = this.getDefaultPublisher() || {};
		var status = defaultPublisher.status || {};

		return status.hasAccessToken || false;
	};
	this.canRun = function() {
		var d = this.options.cddata || {};
		return ($.trim(d.url) !== '' && $.trim(d.defaultActorId) !== '');
	};
	this.getDefaultPublisher = function() {
		var actors = ((this.options.cddata || {}).AvailableActorStatuses || []);
		actors = actors  || [];
		actors = $.isArray(actors) ? actors: [actors];
		var found = null;

		$.each(actors, function(i, actor) {
			if (!found && $.trim(actor.id) === $.trim(userID)) {
			    found = actor;
			}
		});

		return found;
	};
	this.isRunning = function() {
		var d = this.options.cddata || {};
		var running = $.trim((d.runningInstance || {}).id || '');
		return (running !== '' || d.start === 'running');
	};
	this.showContents = function(enable) {
		enable = (typeof enable === 'boolean') ? enable : true;
		if (enable) {
			$(this.dom).find('.cd-version-contents').removeClass('hidden');
		} else {
			$(this.dom).find('.cd-version-contents').addClass('hidden');
		}
	};
	this.saveSetupData = function() {
		var url = $.trim($(this.dom).find('.cdversion-setup input[data-path="url"]').val());
		var defaultActor = this.getDefaultPublisher();
		var defaultActorId = (defaultActor || {}).id;
		var _renderSetupError = function(enable, error) {
			enable = (typeof enable === 'boolean') ? enable : true;
			var dom = $(this.dom).find('.cdversion-setup > .updateerror');
			if (enable) {
				$(dom).find('.message').text(error);
				$(dom).removeClass('hidden');
			} else {
				$(dom).addClass('hidden');
			}
		}.bind(this);
		_renderSetupError(false);
		var btn = $(this.dom).find('.cdversion-setup button.cdversion-save-setup');
		$(btn).addClass('disabled').prop('disabled', true).text('Updating');
		$.ajax({
			url: this.getCDUrl(),
			"type": 'POST',
			data: JSON.stringify({
				'_action': 'update',
				url: url,
				defaultActorId: defaultActorId
			}),
			success: function(cddata, textStatus, jqXHR) {
				this.renderLoadingData(false);
				$(btn).removeClass('disabled').prop('disabled', false).text('Update');
				cddata = cddata || {};
				if ($.trim(cddata.error)) {
					_renderSetupError(true, cddata.error);
				} else {
					appdb.pages.Application.reload();

				}
			}.bind(this),
			error: function( jqXHR, textStatus, errorThrown) {
				$(btn).removeClass('disabled').prop('disabled', false).text('Update');
				this.renderLoadingData(false);
				var errText = errorThrown + ' (' + textStatus + ')';
				try { resp = JSON.parse(jqXHR.responseText); errText = resp.error || errText;} catch(e) {}
				_renderSetupError(true, errText);
			}.bind(this)
		})
	}
	this.validateSetupData = function() {
		var btn = $(this.dom).find('.cdversion-setup button.cdversion-save-setup');
		var input = $(this.dom).find('.cdversion-setup input[data-path="url"]');
		var d = (this.options.cddata || {})
		var invalid = false;
		var warning = [];
		var error = [];

		if ($.trim($(input).val()) === '') {
			invalid = true;
			error.push('Description file URL must not be blank');
		}
		if (invalid === false && /^http(s){0,1}\:\/\//i.test($.trim($(input).val())) === false) {
		    invalid = true;
		    error.push('Description file URL is not valid');
		}
		if (d.AvailableActorStatuses && $.isArray(d.AvailableActorStatuses) && d.AvailableActorStatuses.length > 0) {
			var current = null;
			$.each(d.AvailableActorStatuses, function(i, actor) {
				if (!current && actor.id === userID) {
					current = actor;
				}
			});

			if (current && current.status) {
				if (current.status.canManageVA === false) {
					error.push('You need permissions to manage this virtual appliance');
				}
				if (current.status.hasAccessToken === false) {
					error.push('You do not own a <b>personal</b> access token. You can create one from your profile at the end of the <a href="'+appdb.config.endpoint.base + 'store/person/' + current.cname + '/preferences" target="_blank" title="Click to open your profile preferences page">preferences</a> tab.');
				}
			}
		}

		if (d.DefaultActor && d.DefaultActor.id && $.trim(d.DefaultActor.id) !== $.trim(userID)) {
			warning.push('Currently the default publisher is user <a href="'+appdb.config.endpoint.base + 'store/person/' + d.DefaultActor.cname + '" target="_blank" title="Click to view user profile">'+d.DefaultActor.firstname + ' ' + d.DefaultActor.lastname +'</a>. If you update the settings you will become the default publisher. <a class="wiki-link" href="'+ appdb.config.endpoint.wiki + 'main:faq:cd_how_to_replace_default_publisher" target="_blank">Learn more.</a>');
		}

		if (invalid === false && /\.(xml|json|yaml|yml)$/i.test($.trim($(input).val())) === false) {
		    warning.push('It seems that the provided description file URL does not point to a supported file type (JSON, XML or YAML). <br/>You can ignore this message if the content of the URL is of the supported types.')
		}

		invalid = invalid || (error.length > 0);

		if (invalid !== false) {
			$(btn).addClass('disabled').prop('disabled', true).off('click');
		} else {
			$(btn).removeClass('disabled').prop('disabled', false).off('click').on('click', function() {
				this.saveSetupData();
			}.bind(this));
		}

		if (error.length) {
		    $(this.dom).find('.cdversion-setup .error').removeClass('hidden').find('.message').html(error.join('<br/>'));
		} else {
		    $(this.dom).find('.cdversion-setup .error').addClass('hidden').find('.message').empty();
		}

		if (warning.length) {
		    var wrnDom = $(this.dom).find('.cdversion-setup .warning');
		    if (warning.length > 1) {
			$(wrnDom).removeClass('single');
		    } else {
			$(wrnDom).addClass('single');
		    }

		    $(wrnDom).removeClass('hidden').find('.message').empty().append($('<ul></ul>').append('<li>' + warning.join('</li><li>') + '</li>'));
		} else {
		    $(this.dom).find('.cdversion-setup .warning').addClass('hidden').find('.message').empty();
		}
	};
	this.getOwner = function() {
		var owner = null;
		if (userIsAdmin) {
			owner = {
				id: userID,
				cname: userCName,
				firstname: userFullname.split(' ')[0],
				lastname: userFullname.split(' ')[1]
			};
		} else  {
			owner = appdb.pages.Application.getOwner() || {};
		}
		return owner || {};
	};
	this.getCdTaskInstances = function(data) {
		var d = data || ((this.options.cddata || {}).runningInstance || {});

		d.CdTaskInstances = d.CdTaskInstances || [];
		d.CdTaskInstances = $.isArray(d.CdTaskInstances) ? d.CdTaskInstances : [d.CdTaskInstances];

		d.CdTaskInstances.sort(function(a, b) {
			return (a.ord > b.ord) ? -1 : 1;
		})

		return d.CdTaskInstances;
	};
	this.cancelRunning = function() {
		var btnCancel = $(this.dom).find('.cdversion-cancel-running');
		$(btnCancel).addClass('loading').addClass('disabled').prop('disabled', true).text('CANCELING');
		$.ajax({
			url: this.getCDUrl(),
			"type": 'POST',
			data: JSON.stringify({
				'_action': 'cancel',
				'reason': 'Canceled by user (ID: ' + userID + ')'
			}),
			success: function(cddata, textStatus, jqXHR) {
				$(btnCancel).removeClass('disabled').prop('disabled', false).text('CANCEL');
				cddata = cddata || {};
				if ($.trim(cddata.error)) {
					var err = new appdb.views.ErrorHandler();
					err.handle({
						"status": "Could not cancel continuous delivery process",
						"description": cddata.error
					});
					$(btnCancel).removeClass('loading').removeClass('disabled').prop('disabled', false).text('CANCEL');
				} else {
					appdb.pages.Application.reload();
				}
			}.bind(this),
			error: function( jqXHR, textStatus, errorThrown) {
			    $(btnCancel).removeClass('loading').removeClass('disabled').prop('disabled', false).text('CANCEL');
			    var errText = errorThrown + ' (' + textStatus + ')';
			    try { resp = JSON.parse(jqXHR.responseText); errText = resp.error || errText;} catch(e) {}
			    var err = new appdb.views.ErrorHandler();
			    err.handle({
				    "status": "Could not cancel continuous delivery process",
				    "description": errText
			    });
		    }.bind(this)
	    });
	};
	this.forceCheck = function() {
		var bt = $(this.dom).find('.cdversion-force-check');
		var btPause = $(this.dom).find('.cd-action-pause');
		$(bt).addClass('disabled').prop('disabled', true).text('CHECKING');
		$(btPause).addClass('disabled').prop('disabled', true);
		$.ajax({
		    url: this.getCDUrl(),
		    "type": 'POST',
		    data: JSON.stringify({
			    '_action': 'start'
		    }),
		    success: function(cddata, textStatus, jqXHR) {
			    $(bt).text('FORCE CHECK');
			    cddata = cddata || {};
			    if ($.trim(cddata.error)) {
				    var err = new appdb.views.ErrorHandler();
				    err.handle({
					    "status": "Could not start continuous delivery process",
					    "description": cddata.error
				    });
			    } else {
				    this.load();
			    }
		    }.bind(this),
		    error: function( jqXHR, textStatus, errorThrown) {
			    $(bt).removeClass('disabled').prop('disabled', false).text('FORCE CHECK');
			    var errText = errorThrown + ' (' + textStatus + ')';
			    try { resp = JSON.parse(jqXHR.responseText); errText = resp.error || errText;} catch(e) {}
			    var err = new appdb.views.ErrorHandler();
			    err.handle({
				    "status": "Could not start continuous delivery process",
				    "description": errText
			    });
		    }.bind(this)
	    });
	};
	this.pause = function () {
		var btnPause = $(this.dom).find('button.cd-action-pause');
		$(btnPause).addClass('loading').addClass('disabled').prop('disabled', true).text('PAUSING');
		$.ajax({
			url: this.getCDUrl(),
			"type": 'POST',
			data: JSON.stringify({
				'_action': 'update',
				paused: true
			}),
			success: function(cddata, textStatus, jqXHR) {
				cddata = cddata || {};
				if ($.trim(cddata.error)) {
					var err = new appdb.views.ErrorHandler();
					err.handle({
						"status": "Could not resume continuous delivery process",
						"description": cddata.error
					});
					$(btnPause).removeClass('loading').removeClass('disabled').prop('disabled', false).text('PAUSE');
				} else {
					appdb.pages.Application.reload();
				}
			}.bind(this),
			error: function( jqXHR, textStatus, errorThrown) {
				$(btnPause).removeClass('loading').removeClass('disabled').prop('disabled', false).text('PAUSE');
				var errText = errorThrown + ' (' + textStatus + ')';
				try { resp = JSON.parse(jqXHR.responseText); errText = resp.error || errText;} catch(e) {}
				var err = new appdb.views.ErrorHandler();
				err.handle({
					"status": "Could not resume continuous delivery process",
					"description": errText
				});
			}.bind(this)
		});
	};
	this.resume = function() {
		var btnResume = $(this.dom).find('button.cd-action-resume');
		$(btnResume).addClass('loading').addClass('disabled').prop('disabled', true).text('RESUMING');
		$.ajax({
			url: this.getCDUrl(),
			"type": 'POST',
			data: JSON.stringify({
				'_action': 'update',
				paused: false
			}),
			success: function(cddata, textStatus, jqXHR) {
				cddata = cddata || {};
				if ($.trim(cddata.error)) {
					var err = new appdb.views.ErrorHandler();
					err.handle({
						"status": "Could not resume continuous delivery process",
						"description": cddata.error
					});
					$(btnResume).removeClass('loading').removeClass('disabled').prop('disabled', false).text('RESUME');
				} else {
					appdb.pages.Application.reload();
				}
			}.bind(this),
			error: function( jqXHR, textStatus, errorThrown) {
				$(btnResume).removeClass('loading').removeClass('disabled').prop('disabled', false).text('RESUME');
				this.renderLoadingData(false);
				var errText = errorThrown + ' (' + textStatus + ')';
				try { resp = JSON.parse(jqXHR.responseText); errText = resp.error || errText;} catch(e) {}
				var err = new appdb.views.ErrorHandler();
				err.handle({
					"status": "Could not resume continuous delivery process",
					"description": errText
				});
			}.bind(this)
		});
	}
	this.renderSetup = function(enable) {
		var dom = $(this.dom).find('.cdversion-setup');
		var urlInput = $(dom).find('input[data-path="url"]');
		var defautlActorHtml = $(dom).find('.value[data-path="defautlActorId"]');
		var owner = this.getDefaultPublisher();
		var d = this.options.cddata || {};
		var old = this.options.oldcddata || null;

		enable = (typeof enable === 'boolean') ? enable : true;

		if (enable) {
			if (!old || $.trim(d.url) !== $.trim(old.url)) {
			    $(urlInput).val($.trim(d.url));
			}
			$(urlInput).off('keyup').on('keyup', function() {
			        this.validateSetupData();
			}.bind(this));
			if (owner) {
				if ($.trim($(defautlActorHtml).find('a[data-actorid]').data('actorid')) !== $.trim(owner.id)) {
				    var a = $('<a target="_blank"></a>')
						.attr('data-actorid', $.trim(owner.id))
						.attr('href', appdb.config.endpoint.base + 'store/person/' + owner.cname)
						.attr('title', 'Click to view profile in a new tab')
						.append('<img src="/people/getimage?id='+owner.id+'" style="width:24px;height:24px;padding-right: 10px;vertical-align: middle;" /><span style="vertical-align: middle;font-size: 14px;">' + owner.firstname + ' ' + owner.lastname + '</span>');
				    $(defautlActorHtml).empty().append(a);
				}
			} else {
				$(defautlActorHtml).empty().append('<div style="color:red;">No owner found for this virtual appliance</div>');
			}
			$(dom).removeClass('hidden');
			$(this.dom).children('.cdversion-main').addClass('hidden');
			this.validateSetupData();
		} else {
		    $(dom).addClass('hidden');
		}

		if (parseInt(d.defaultActorId) > 0) {
		    $(this.dom).children('.cdversion-main').removeClass('hidden');
		}
		this.initSettingsExpanse();
	};
	this.checkSettingsExpanse = function() {
		var d = this.options.cddata || {};
		var dom = $(this.dom).find('.cdversion-setup');
		if (!d.defaultActorId || !$.trim(d.url)) {
		    $(dom).find('.header .collapse .icon').addClass('hidden');
		} else {
		    $(dom).find('.header .collapse .icon').removeClass('hidden');
		}
		if (this.options.userExpandedSettings === true || !d.defaultActorId || !$.trim(d.url)) {
			$(dom).find('.header .collapse').addClass('expanded');
			$(dom).removeClass('collapsed');
		} else {
			$(dom).find('.header .collapse').removeClass('expanded');
			$(dom).addClass('collapsed');
		}
	};
	this.initSettingsExpanse = function() {
		var dom = $(this.dom).find('.cdversion-setup');
		var d = this.options.cddata || {};
		if (!d.defaultActorId && !$.trim(d.url) && d.paused) {
		    this.options.userExpandedSettings = true;
		    this.checkSettingsExpanse();
		    $(dom).find('.header .collapse .icon').remove();
		    return;
		}
		if ($(dom).find('.header .collapse').hasClass('inited') === false) {
			this.options.userExpandedSettings = (this.options.userExpandedSettings === null && !d.defaultActorId && !$.trim(d.url) && d.paused) ? true : this.options.userExpandedSettings;
			$(dom).find('.header .collapse').addClass('inited').off('click').on('click', function(ev) {
				this.options.userExpandedSettings = !this.options.userExpandedSettings;
				this.checkSettingsExpanse();
			}.bind(this));
		}
		this.checkSettingsExpanse();
	};
	this.renderLoadingData = function(enable, text) {
		enable = (typeof enable === 'boolean') ? enable : true;
		if (enable) {
			$(this.dom).addClass('loading');
			if ($.trim(text) ) {
				$(this.dom).find('.cdversion-loading .message').text(text);
			} else {
				$(this.dom).find('.cdversion-loading .message').text('Loading CD information');
			}
			$(this.dom).find('.cdversion-loading').removeClass('hidden');
		} else {
			$(this.dom).removeClass('loading');
			$(this.dom).find('.cdversion-loading').addClass('hidden');
		}
	};
	this.renderErrorLoading = function(enable, error) {
		enable = (typeof enable === 'boolean') ? enable : true;
		error = error || {title: 'Could not load information', message: 'Unknown error occured'};
		if (typeof error === 'string') {
			error = { message: error };
		}
		if (!error.message) {
			error.message = 'Unknown error occured';
		}
		if (!error.title) {
			error.title = 'Could not load information';
		}

		if (enable) {
			$(this.dom).find('.cdversion-error .title').html($.trim(error.title));
			$(this.dom).find('.cdversion-error .message').html($.trim(error.message));
			$(this.dom).addClass('error');
			$(this.dom).find('.cdversion-error').removeClass('hidden');
		} else {
			$(this.dom).removeClass('error');
			$(this.dom).find('.cdversion-error').addClass('hidden');
		}
	};
	this.renderMetaData = function() {
	    var d = this.options.cddata || {};
	    var dom = $(this.dom).children('.cdversion-main');
	    var instanceDom = $(dom).find('.cdversion-instance');

	    if (this.isRunning()) {
		    $(instanceDom).addClass('state-running');
	    } else {
		    $(instanceDom).removeClass('state-running');
	    }

	    this.renderRunningInstance();
	    this.renderLastInstance();

	    var url = $.trim(d.url) || '';
	    var pausedUrl = $(this.dom).find('.paused-url .value a');
	    if (url !== $(pausedUrl).attr('href')) {
		$(this.dom).find('.paused-url .value a').off('click').attr('href', url).text(url || '<none>');
		if (!url) {
		    $(this.dom).find('.paused-url .value a').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			return false;
		    });
		}
	    }
	};
	this.getUserLink = function(user) {
	    user = user || {};

	    return $('<a class="userlink" target="_blank" title="Click to view profile in new tab"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + user.cname).text(user.firstname + ' ' + user.lastname);
	};
	this.renderNoProcessPanel = function() {
		var d = (this.options.cddata || {});
		var defaultActorStatus = (d.DefaultActorStatus || {});
		var error = $('<span></span>').append('No continuous delivery checks will be performed until the following issues are resolved:<br/>');
		var ulErrors = $('<ul></ul>');
		if ($.trim(d.lastIterationOn)) {
		    if ($.trim(d.url) === '') {
			    $(ulErrors).append(
				    $('<li></li>').append('Description file URL must not be blank. Please, provide a valid URL in the <b>settings</b> section.')
			    );
		    }

		    if ($.trim(d.defaultActorId) === '' || parseInt(d.defaultActorId) <= 0) {
			    $(ulErrors).append(
				    $('<li></li>').append('No publisher is set. You can become the default publisher in the <b>settings</b> section.')
			    );
		    }
		}
		if (this.options.userHasPermissions) {
			if (defaultActorStatus.exists === false) {
				var actorHtml = (d.DefaultActor && $.trim(d.DefaultActor.cname)) ? this.getUserLink(d.DefaultActor) : ' (ID: ' + d.defaultActorId + ')';
				$(ulErrors).append(
					$('<li></li>').append('Current default publisher ' )
						.append(actorHtml)
						.append(' does not exist.')
						.append($('<a class="wiki-link" target="_blank">Learn more.</a>').attr('href', appdb.config.endpoint.wiki + 'main:faq:cd_default_publisher_does_not_exist_anymore'))
				);
			}else {
				if (defaultActorStatus.hasAccessToken === false) {
					$(ulErrors).append(
						$('<li></li>').append('Current default publisher ' )
							.append(this.getUserLink(d.DefaultActor))
							.append(' does not own a personal access token anymore.')
							.append($('<a class="wiki-link" target="_blank">Learn more.</a>').attr('href', appdb.config.endpoint.wiki + 'main:faq:cd_default_publisher_revoked_access_token'))
					);
				}

				if (defaultActorStatus.canManageVA === false) {
					$(ulErrors).append(
						$('<li></li>').append('Current default publisher ' )
							.append(this.getUserLink(d.DefaultActor))
							.append(' does not have permissions to publish new virtual appliance versions.')
							.append($('<a class="wiki-link" target="_blank">Learn more.</a>').attr('href', appdb.config.endpoint.wiki + 'main:faq:cd_default_publisher_revoked_permissions'))
					);
				}
			}
		}

		var errorStr = $(ulErrors).html();
		if (this.options.previousStatusErrorStr === errorStr) {
			return;
		} else {
			this.options.previousStatusErrorStr = errorStr;
		}

		if ($(ulErrors).children().length > 0) {
			$(error).append(ulErrors);
			$(this.dom).find('.cdversion-noprocess .exterror').removeClass('hidden').find('.message').empty().append($(error).clone());
			$(this.dom).find('.cdversion-noprocess .cdversion-force-check').prop('disabled', true).addClass('disabled');
			$(this.dom).find('.cdversion-paused-process .exterror').removeClass('hidden').find('.message').empty().append($(error).clone());
			$(this.dom).find('.cdversion-paused-process .cd-action-resume').prop('disabled', true).addClass('disabled');
			$(this.dom).find('.onstatuserror').removeClass('hidden').find('.message').empty().append($(error).clone());
		} else {
			$(this.dom).find('.cdversion-noprocess .exterror').addClass('hidden').find('.message').empty();
			$(this.dom).find('.cdversion-noprocess .cdversion-force-check').prop('disabled', false).removeClass('disabled');
			$(this.dom).find('.cdversion-paused-process .exterror').addClass('hidden').find('.message').empty();
			$(this.dom).find('.cdversion-paused-process .cd-action-resume').prop('disabled', false).removeClass('disabled');
			$(this.dom).find('.onstatuserror').addClass('hidden').find('.message').empty();
		}
	};
	this.renderRunningInstance =  function(data) {
	    data = data || this.options.cddata || {};
	    var runningInstance = data.runningInstance || {};
	    if ($.trim(runningInstance.id) !== '') {
		this.renderInstance($(this.dom).find('.cdversion-running-instance .cdinstance'), runningInstance);
	    }
	};
	this.renderLastInstance = function(data) {
	    data = data || this.options.cddata || {};
	    var runningInstance = data.runningInstance || {};
	    var lastInstance = data.lastInstance || {};
	    var container = $(this.dom).find('.cdversion-lastinstance');
	    var dom = $(container).find('.last-cdinstance');
	    if ($.trim(runningInstance.id) === '' && $.trim(lastInstance.id) !== '') {
		    $(container).removeClass('hidden');
		    if ($.trim(lastInstance.id) !== $(dom).data('id')) {
			$(dom).data('id', $.trim(lastInstance.id));
			this.renderInstance(dom, lastInstance);
		    }
	    } else {
		    $(container).addClass('hidden');
	    }

	    if (lastInstance && lastInstance.state) {
		    if (lastInstance.state === 'failed') {
			    $(container).addClass('failed').removeClass('success').removeClass('canceled');
			    $(container).find('.cdversion-lastinstance-result').addClass('failed').removeClass('success').removeClass('canceled').text('FAILED');
		    } else if (lastInstance.state === 'completed') {
			    $(container).addClass('success').removeClass('canceled').removeClass('failed');
			    $(container).find('.cdversion-lastinstance-result').addClass('success').removeClass('canceled').removeClass('failed').text('SUCCESS');
		    } else if (lastInstance.state === 'canceled') {
			    $(container).addClass('canceled').removeClass('success').removeClass('failed');
			    $(container).find('.cdversion-lastinstance-result').addClass('canceled').removeClass('success').removeClass('failed').text('CANCELED');
		    } else {
			    $(container).removeClass('success').removeClass('failed').removeClass('canceled');
			    $(container).find('.cdversion-lastinstance-result').removeClass('success').removeClass('failed').removeClass('canceled').text('');
		    }
	    }
	};
	this.renderInstance = function(container, d) {
		var inst = d || {};
		var metadom = $(container).find('.cdinstance-metadata ');
		var actorLink = '-';
		if (inst.DefaultActor && inst.DefaultActor.id) {
			actorLink = $('<a target="_blank"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + inst.DefaultActor.cname).attr('title', 'Click to view profile in a new tab');
			$(actorLink).append('<img src="/people/getimage?id=' + inst.DefaultActor.id + '" style="width:24px;height:24px;padding-right: 10px;vertical-align: middle;"/>');
			$(actorLink).append($('<span style="vertical-align: middle;font-size: 14px;"></span>').text(inst.DefaultActor.name));
		}

		$(metadom).find('.fieldvalue.completedon> .value').empty().append(this.formatDate(inst.completedOn));
		$(metadom).find('.fieldvalue.triggeredby > .value').empty().append(inst.RelatedTriggerType.name);
		$(metadom).find('.fieldvalue.startedon > .value').text(this.formatDate(inst.startedOn));
		$(metadom).find('.fieldvalue.startedby > .value').empty().append(actorLink);

		var percentage = Math.round((inst.progressVal / inst.progressMax) * 100);
		result = $('<div class="progressbar" title="Overall progress of continuous delivery process." style="display: block;position: relative;padding-top: 4px;color: black;"><span class="bar" style="display: block; width: '+percentage+'%;"></span><span class="percent" style="position: relative;z-index: 10;">'+percentage+'% ('+inst.progressVal+' of '+inst.progressMax+' steps)</span></div>');
		$(metadom).find('.fieldvalue.progress > .value').html(result);


		this.renderTaskInstanceList(container, d);
	};
	this.renderTaskInstanceList = function(container, data) {
		var tasks = this.getCdTaskInstances(data);
		var prevTasksDom = $(container).find('.previous-tasks');

		if (tasks.length === 0) {
			$(prevTasksDom).addClass('empty');
		} else {
			$(prevTasksDom).removeClass('empty');
		}

		var prevTasksTable = $(prevTasksDom).find('.cdtaskinstance-list');
		var prevTaskBody = $(prevTasksTable).find('tbody').empty();

		$.each(tasks, function (index, task) {
			var tr = $('<tr></tr>');
			var stateDom = null;
			var result = $.trim(task.result);
			switch(task.state) {
			    case 'failed':
				stateDom = $('<img src="/images/cancelicon.png" title="The task failed to complete."/>');
				break;
			    case 'canceled':
				stateDom = $('<img src="/images/stop2.png" title="The task was canceled."/>');
				break;
			    case 'completed':
			    case 'success':
				stateDom = $('<img src="/images/yes.png" title="The task completed successfully."/>');
				break;
			    case 'running':
				stateDom = $('<img src="/images/ajax-loader-trans-orange.gif" title="The task is still running."/>');
				break;

			}

			if (task.CdTask.cname === 'appdb.cd.task.integrity.check') {
				if (task.state==='completed') {
					var tmpresult = result.split(',');
					result = $('<div></div>');
					$.each(tmpresult, function(index, prop) {
					    var tmp = prop.split(':');
					    var field = $.trim(tmp[0]) + ':';
					    var value = '-';
					    if (tmp.length > 1) {
						    value = $.trim(tmp[1]);
					    }
					    var d = $('<div class="fieldvalue"></div>');
					    $(d).append($('<div class="field"></div>').text(field));
					    $(d).append($('<div class="value"></div>').text(value));

					    $(result).append(d);
					});
				} else if (task.state === 'running') {
					var percentage = Math.round((task.progressVal / task.progressMax) * 100);
					result = $('<div class="progressbar" title="Downloading and calculating checksum of VM image file." style="display: block;position: relative;padding-top: 4px;color: black;"><span class="bar" style="display: block; width: '+percentage+'%;"></span><span class="percent" style="position: relative;z-index: 10;">'+percentage+'%</span></div>');
				}
			} else if (task.CdTask.cname === 'appdb.cd.task.publishvaversion.publish') {
				if (task.state==='completed') {
				    var tmpresult = {};
				    try { tmpresult = JSON.parse(result); } catch(e) {tmpresult = {};}
				    if (!tmpresult.version) {
					    result = $('<div>Successfully published new virtual appliance version.</div>');
				    } else {
					    result = $('<div></div>').append('Successfully published new virtual appliance version ').append($('<b></b>').text(tmpresult.version)).append('.');
				    }
				}
			}

			$(tr).append($('<td class="img"></td>').append(stateDom));
			$(tr).append($('<td class="name"></td>').append($("<span></span>").attr('title', task.CdTask.description).append(task.CdTask.name)));
			$(tr).append($('<td class="startedon"></td>').append(this.formatDate(task.startedOn)));
			$(tr).append($('<td class="state"></td>').append(task.state));
			$(tr).append($('<td class="result"></td>').append(result));

			$(prevTaskBody).append(tr);
		}.bind(this));
	};
	this.formatDate = function(d) {
		if (!$.trim(d)) return '-';
		var date = new Date($.trim(d));
		var months = (date.getMonth()+1);
		var days = date.getDate();
		var hours = date.getHours();
		var mins = date.getMinutes();
		var secs = date.getSeconds();
		months = ((months < 10) ? '0' : '') + months;
		days = ((days < 10) ? '0' : '') + days;
		hours = ((hours < 10) ? '0' : '') + hours;
		mins =  ((mins < 10) ? '0' : '') + mins;
		secs = ((secs < 10) ? '0' : '') + secs;

		return date.getFullYear()+'-' + months + '-'+ days + ' ' + hours + ':' + mins + ':' + secs;
	};
	this.renderLogSizeHandler = function(){
		var allowedCdLogSizes = (this.options.allowedCdLogSizes || []).join(',|,').split(',');
		var sizeLinks = $(this.dom).find('.cdversion-logs .page-size > ul').empty();

		$.each(allowedCdLogSizes, function(i, s) {
			var li = $('<li></li>').text(s);
			if (s === '|') {
			    sizeLinks.push($(li).addClass('sep'));
			} else {
			    if ($.trim($(li).text()) === $.trim(this.options.cdLogSize)) {
				    $(li).addClass('selected');
			    } else {
				    $(li).removeClass('selected');
			    }

			    $(li).off('click').on('click', function(el) {
				    this.options.cdLogSize = parseInt($(el.target).text());
				    $(sizeLinks).find('li').removeClass('selected');
				    $(el.target).addClass('selected');
			    }.bind(this));
			}
			$(sizeLinks).append(li);
		}.bind(this));
	};
	/*this.renderRawLogs = function () {
		var container = $(this.dom).find('.cdversion-logs');
		var dom = $(container).find('.cdversion-log-list');
		var d = this.options.cddata || {};
		var logs = d.CdLogs || [];
		var oldLogs = (this.options.oldcddata || {}).CdLogs || [];
		var logIds = [];
		var oldLogIds = [];

		if (logs.length === 0) {
			$(container).addClass('hidden');
		} else {
			$(container).removeClass('hidden');
		}

		$.each(logs, function(i, log) {
			logIds.push(log.id);
		});
		$.each(oldLogs, function(i, log){
			oldLogIds.push(log.id);
		})

		if (oldLogIds.join(',') === logIds.join(',')) {
			return;
		}

		$(dom).empty();

		$.each(logs, function(i, log) {
			var li = $('<li></li>').attr('data-id', log.id).attr('data-action', log.action);
			var div = $('<div class="item"></div>');
			var summary = $('<div></div>').addClass('summary');
			var meta = $('<div></div>').addClass('meta');
			var actor = null;

			if (log.Actor && log.Actor.id) {
				actor = $('<a class="actor" target="_blank"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + log.Actor.cname).append($('<span class="actor-name"></span>').text(log.Actor.firstname + ' ' + log.Actor.lastname));
				$(meta).append($('<span class="user"></span>').append('by user').append(actor).append(', '));
			}

			$(meta).append('at ').append($('<span class="createdon"></span>').text(this.formatDate(log.createdOn)));

			var action = $('<span class="ui label tiny action"></span>');
			var name = $('<span class="name"></span>');
			var description = $('<span class="description"></div>');

			if (log.action === 'cd-prop-change') {
				if (log.subject === 'paused') {
					if ($.trim(log.payload) === 'true') {
						$(action).addClass('canceled').append($('<span></span>').text('PAUSED'));
						$(name).text('Continuous delivery checks were paused.')
					} else {
						$(action).addClass('completed').append($('<span></span>').text('RESUMED'));
						$(name).text('Continuous delivery checks were resumed from previous pause.')
					}
				} else if (log.subject === 'enabled') {
					if ($.trim(log.payload) === 'true') {
						$(action).addClass('primary').append($('<span></span>').text('ENABLED'));
						$(name).text('Continuous delivery was enabled.')
					} else {
						$(action).append($('<span></span>').text('DISABLED'));
						$(name).text('Continuous delivery was disabled.')
					}
				} else {
					$(action).addClass('primary').append($('<span></span>').text('PROPERTY'));
					var comments = {};
					if ($.trim(log.comments)) {
						try {
							comments = JSON.parse(log.comments);
						} catch(e) {comments = {};}
					}

					if (log.subject === 'defaultActorId') {
						var newActorVal = $('<span class="new-value"></span>').text(log.payload);
						var prevActorVal = $('<span class="prev-value"></span>').text(log.payload);
						if (comments.to && $.trim(comments.to.cname)) {
							var newActorLink = $('<a target="_blank" title="Click to view profile in a new tab"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + comments.to.cname).text(comments.to.firstname + ' ' + comments.to.lastname + ' (ID: '+comments.to.id+')');
							$(newActorVal).text('').append(newActorLink);
						} else if (comments.to && $.trim(comments.to.id)) {
							$(newActorVal).text('ID: ' + comments.to.id)
						} else {
							newActorVal = null;
						}

						if (comments.from && $.trim(comments.from.cname) ) {
							var prevActorLink = $('<a target="_blank" title="Click to view profile in a new tab"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + comments.from.cname).text(comments.from.firstname + ' ' + comments.from.lastname + ' (ID: '+comments.from.id+')');
							$(prevActorVal).text('').append(prevActorLink);
						} else if (comments.from && $.trim(comments.from.id)) {
							$(prevActorVal).text('ID: ' + comments.from.id);
						} else {
							prevActorVal = null;
						}

						if (newActorVal && prevActorVal) {
							$(name).append('Updated <span class="property-name">Default Publisher</span> to ').append(newActorVal).append(' from ').append(prevActorVal);
						} else if (newActorVal && !prevActorVal) {
							$(name).append('Updated <span class="property-name">Default Publisher</span> to ').append(newActorVal);
						} else if (!newActorVal && prevActorVal) {
							$(name).append('Updated <span class="property-name">Default Publisher</span> from ').append(prevActorVal).append(' to unknown publisher.');
						} else {
							$(name).append('Updated <span class="property-name">Default Publisher</span>. No information regarding previous or new publisher.');
						}
						$(description).empty();
					} else {
						$(name).append('Updated <span class="property-name">' + log.subject + '</span> to ')
						       .append($('<span class="new-value"></span>').text(log.payload));
						if ($.trim(comments.from)) {
							$(name).append(' from ').append($('<span class="old-value"></span>').text(comments.from));
						}
					}
				}
			} else if ($.trim(log.cdInstanceId) !== '') {
				if (log.action === 'canceled') {
					$(action).addClass($.trim(log.action).toLowerCase()).text($.trim(log.action).toUpperCase());
					$(name).append('Continous Delivery process was canceled. ');
					//if (log.Actor && log.Actor.id) {
						$(name).append($.trim(log.payload));
					//}
				} else if (['failed', 'task-failed', 'error'].indexOf(log.action) > -1) {
					$(action).addClass($.trim(log.action).toLowerCase()).text($.trim(log.action).toUpperCase());
					$(name).append('Continous Delivery process failed to complete. ');
					$(name).append($.trim(log.payload));
				} else {
					$(action).addClass($.trim(log.action).toLowerCase()).text($.trim(log.action).toUpperCase());
					var payload = {};
					try {
						payload = JSON.parse(log.payload);
					} catch(e) {payload = {}}
					$(name).append('Successfully published new Virtual Appliance version ').append($('<span class="version"></span>').text(payload.version));
					if (payload.publishedBy && $.trim(payload.publishedBy.id)) {
						$(name).append(' as user ');
						$(name).append($('<a target="_blank" title="Click to view profile in new tab"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + payload.publishedBy.cname).text(payload.publishedBy.firstname + ' ' + payload.publishedBy.lastname));
					}
					$(name).append('.');
				}
			}
			$(summary).append(action).append(name);
			if ($(description).text() !== '') {
				$(summary).append(description);
			}

			$(div).append(summary).append(meta);
			$(li).append(div);
			$(dom).append(li);
		}.bind(this));
		this.renderLogSizeHandler();
	};*/
	this.renderLogs = function () {
		var container = $(this.dom).find('.cdversion-logs');
		var dom = $(container).find('.cdversion-log-list');
		var d = this.options.cddata || {};
		var logs = d.CdLogPartitioned || d.CdLogs || [];
		var olddata = (this.options.oldcddata || {});
		var oldLogs = olddata.CdLogPartitioned || olddata.CdLogs || [];
		var logIds = [];
		var oldLogIds = [];

		if (logs.length === 0) {
			$(container).addClass('hidden');
		} else {
			$(container).removeClass('hidden');
		}

		$.each(logs, function(i, log) {
			logIds.push(log.partitionId || log.id);
		});

		$.each(oldLogs, function(i, log){
			oldLogIds.push(log.partitionId || log.id);
		})

		if (oldLogIds.join(',') === logIds.join(',')) {
			return;
		}

		$(dom).empty();

		$.each(logs, function(i, log) {
			log.Actor = log.CdLogPartitionActor || log.Actor;
			log.id = log.partitionId || log.id;
			log.createdOn = log.maxCreatedOn || log.createdOn;
			log.cdInstanceId =  log.cdInstanceIds || log.cdInstanceId

			var li = $('<li></li>').attr('data-id', log.id).attr('data-action', log.action);
			var div = $('<div class="item"></div>');
			var summary = $('<div></div>').addClass('summary');
			var meta = $('<div></div>').addClass('meta');
			var actor = null;

			if (log.Actor && log.Actor.id) {
				actor = $('<a class="actor" target="_blank"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + log.Actor.cname).append($('<span class="actor-name"></span>').text(log.Actor.firstname + ' ' + log.Actor.lastname));
				$(meta).append($('<span class="user"></span>').append('by user').append(actor).append(', '));
			}

			$(meta).append('at ').append($('<span class="createdon"></span>').text(this.formatDate(log.createdOn)));
			if (log.partitionId && log.cnt > 1) {
			    var repeated = $('<span class="repeated">Message repeated <span class="count"></span> times since <span class="since"></span></span>');
			    $(repeated).find('.count').text(log.cnt);
			    $(repeated).find('.since').text(this.formatDate(log.minCreatedOn));
			    $(meta).append('<span class="sep">-</span>').append(repeated);
			}

			var action = $('<span class="ui label tiny action"></span>');
			var name = $('<span class="name"></span>');
			var description = $('<span class="description"></div>');

			if (log.action === 'cd-prop-change') {
				if (log.subject === 'paused') {
					if ($.trim(log.payload) === 'true') {
						$(action).addClass('canceled').append($('<span></span>').text('PAUSED'));
						$(name).text('Continuous delivery checks were paused.')
					} else {
						$(action).addClass('completed').append($('<span></span>').text('RESUMED'));
						$(name).text('Continuous delivery checks were resumed from previous pause.')
					}
				} else if (log.subject === 'enabled') {
					if ($.trim(log.payload) === 'true') {
						$(action).addClass('primary').append($('<span></span>').text('ENABLED'));
						$(name).text('Continuous delivery was enabled.')
					} else {
						$(action).append($('<span></span>').text('DISABLED'));
						$(name).text('Continuous delivery was disabled.')
					}
				} else {
					$(action).addClass('primary').append($('<span></span>').text('PROPERTY'));
					var comments = {};
					if ($.trim(log.comments)) {
						try {
							comments = JSON.parse(log.comments);
						} catch(e) {comments = {};}
					}

					if (log.subject === 'defaultActorId') {
						var newActorVal = $('<span class="new-value"></span>').text(log.payload);
						var prevActorVal = $('<span class="prev-value"></span>').text(log.payload);
						if (comments.to && $.trim(comments.to.cname)) {
							var newActorLink = $('<a target="_blank" title="Click to view profile in a new tab"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + comments.to.cname).text(comments.to.firstname + ' ' + comments.to.lastname + ' (ID: '+comments.to.id+')');
							$(newActorVal).text('').append(newActorLink);
						} else if (comments.to && $.trim(comments.to.id)) {
							$(newActorVal).text('ID: ' + comments.to.id)
						} else {
							newActorVal = null;
						}

						if (comments.from && $.trim(comments.from.cname) ) {
							var prevActorLink = $('<a target="_blank" title="Click to view profile in a new tab"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + comments.from.cname).text(comments.from.firstname + ' ' + comments.from.lastname + ' (ID: '+comments.from.id+')');
							$(prevActorVal).text('').append(prevActorLink);
						} else if (comments.from && $.trim(comments.from.id)) {
							$(prevActorVal).text('ID: ' + comments.from.id);
						} else {
							prevActorVal = null;
						}

						if (newActorVal && prevActorVal) {
							$(name).append('Updated <span class="property-name">Default Publisher</span> to ').append(newActorVal).append(' from ').append(prevActorVal);
						} else if (newActorVal && !prevActorVal) {
							$(name).append('Updated <span class="property-name">Default Publisher</span> to ').append(newActorVal);
						} else if (!newActorVal && prevActorVal) {
							$(name).append('Updated <span class="property-name">Default Publisher</span> from ').append(prevActorVal).append(' to unknown publisher.');
						} else {
							$(name).append('Updated <span class="property-name">Default Publisher</span>. No information regarding previous or new publisher.');
						}
						$(description).empty();
					} else {
						$(name).append('Updated <span class="property-name">' + log.subject + '</span> to ')
						       .append($('<span class="new-value"></span>').text(log.payload));
						if ($.trim(comments.from)) {
							$(name).append(' from ').append($('<span class="old-value"></span>').text(comments.from));
						}
					}
				}
			} else if ($.trim(log.cdInstanceId) !== '') {
				if (log.action === 'canceled') {
					$(action).addClass($.trim(log.action).toLowerCase()).text($.trim(log.action).toUpperCase());
					$(name).append('Continous Delivery process was canceled. ');
					//if (log.Actor && log.Actor.id) {
						$(name).append($.trim(log.payload));
					//}
				} else if (['failed', 'task-failed', 'error'].indexOf(log.action) > -1) {
					$(action).addClass($.trim(log.action).toLowerCase()).text($.trim(log.action).toUpperCase());
					$(name).append('Continous Delivery process failed to complete. ');
					$(name).append($.trim(log.payload));
				} else {
					$(action).addClass($.trim(log.action).toLowerCase()).text($.trim(log.action).toUpperCase());
					var payload = {};
					try {
						payload = JSON.parse(log.payload);
					} catch(e) {payload = {}}
					$(name).append('Successfully published new Virtual Appliance version ').append($('<span class="version"></span>').text(payload.version));
					if (payload.publishedBy && $.trim(payload.publishedBy.id)) {
						$(name).append(' as user ');
						$(name).append($('<a target="_blank" title="Click to view profile in new tab"></a>').attr('href', appdb.config.endpoint.base + 'store/person/' + payload.publishedBy.cname).text(payload.publishedBy.firstname + ' ' + payload.publishedBy.lastname));
					}
					$(name).append('.');
				}
			}
			$(summary).append(action).append(name);
			if ($(description).text() !== '') {
				$(summary).append(description);
			}

			$(div).append(summary).append(meta);
			$(li).append(div);
			$(dom).append(li);
		}.bind(this));
		this.renderLogSizeHandler();
	};
	this.renderActions = function() {
		var dom = $(this.dom).find('.cdversion-action');
		var d = this.options.cddata || {};
		var publisher = this.getDefaultPublisher();
		var publisherStatus = (publisher || {}).status || {};
		var currentPublisherStatus = (d.DefaultActorStatus || {});
		var btnPause = $(this.dom).find('button.cd-action-pause');
		var btnResume = $(this.dom).find('button.cd-action-resume');
		var btnForceCheck = $(this.dom).find('.cdversion-force-check');
		var btnCancel = $(this.dom).find('.cdversion-cancel-running');
		var resumedPanelError = $(this.dom).find('.cdversion-noprocess.panel > .error');
		var pausedPanelError = $(this.dom).find('.cdversion-paused-process.panel > .error');

		if ($.trim(d.paused) === "true") {
			$(btnPause).addClass('hidden');
			$(btnResume).removeClass('hidden');
		} else {
			$(btnPause).removeClass('hidden');
			$(btnResume).addClass('hidden');
		}

		if (publisherStatus.hasAccessToken !== true) {
			$(btnPause).off('click').addClass('disabled').prop('disabled', true);
			$(btnCancel).off('click').addClass('disabled').prop('disabled', true);

			var resumedError = 'You need to own a <b>personal</b> access token in order to <b>pause</b>/<b>force check</b> continuous delivery. You can create one from your profile at the end of the <a href="'+appdb.config.endpoint.base + 'store/person/' + publisher.cname + '/preferences" target="_blank" title="Click to open your profile preferences page">preferences</a> tab.';
			var pausedError = 'You need to own a <b>personal</b> access token in order to <b>resume</b> continuous delivery. You can create one from your profile at the end of the <a href="'+appdb.config.endpoint.base + 'store/person/' + publisher.cname + '/preferences" target="_blank" title="Click to open your profile preferences page">preferences</a> tab.';

			$(resumedPanelError).removeClass('hidden').find('.message').empty().append(resumedError);
			$(pausedPanelError).removeClass('hidden').find('.message').empty().append(pausedError);
			return;
		} else {
			if (currentPublisherStatus.hasAccessToken !== true || currentPublisherStatus.canManageVA !== true) {
			    //$(btnPause).off('click').addClass('disabled').prop('disabled', true);
			    $(btnCancel).off('click').addClass('disabled').prop('disabled', true);
			} else {
			    $(btnCancel).off('click').removeClass('disabled').prop('disabled', false);
			}
			$(resumedPanelError).addClass('hidden').find('.message').empty();
			$(pausedPanelError).addClass('hidden').find('.message').empty();
		}

		if (publisherStatus.hasAccessToken !== true || $.trim(d.url) === '' || parseInt(d.defaultActorId) <= 0 || currentPublisherStatus.hasAccessToken !== true || currentPublisherStatus.canManageVA !== true) {
		    $(btnResume).off('click').addClass('disabled').prop('disabled', true);
		    $(btnForceCheck).off('click').addClass('disabled').prop('disabled', true);
		} else {
		    $(btnResume).off('click').removeClass('disabled').prop('disabled', false);
		    $(btnForceCheck).off('click').removeClass('disabled').prop('disabled', false);
		    $(btnPause).off('click').removeClass('disabled').prop('disabled', false);
		}

		if (!$(btnPause).hasClass('loading')) {
			$(btnPause).off('click').on('click', function() {
				this.pause();
			}.bind(this));
		}

		if (!$(btnResume).hasClass('loading')) {
			$(btnResume).off('click').on('click', function() {
				this.resume();
			}.bind(this));
		}

		if (!$(btnForceCheck).hasClass('loading')) {
			$(btnForceCheck).off('click').on('click', function(){
				this.forceCheck();
			}.bind(this));
		}

		if (!$(btnCancel).hasClass('loading')) {
			$(btnCancel).off('click').on('click', function(){
				this.cancelRunning();
			}.bind(this));
		}
	};
	this.renderNextCheck = function() {
	    var d = (this.options.cddata || {});
	    var nextIterationOn = d.nextIterationOn || null;

	    if (nextIterationOn) {
		    var time = this.formatDate(nextIterationOn);
		    $(this.dom).find('.scheduled-check .scheduled-check-on').text(time);
		    $(this.dom).find('.scheduled-check').removeClass('hidden');
	    } else {
		    $(this.dom).find('.scheduled-check').addClass('hidden');
	    }
	};
	this.setWarningDueToFailedAttempts = function() {
	    var pausedPanelWarning = $(this.dom).find('.cdversion-paused-process-warning');
	    var d = (this.options.cddata || {});
	    var allowedFailedAttempts = -1;

	    if (d.paused === true && d.failedAttempts > 0 && $.trim(d.lastAttemptError) !== '') {
		try {
		    allowedFailedAttempts = parseInt(d.CdConfig.service.cd.failedAttempts.maximum.value);
		} catch (e) { allowedFailedAttempts = -1; }
	    }

	    if (allowedFailedAttempts > 0 && d.failedAttempts >= allowedFailedAttempts) {
		if ($(pausedPanelWarning).hasClass('hidden')) {
		    $(pausedPanelWarning).removeClass('hidden');
		}
		var lasterror = $(pausedPanelWarning).removeClass('hidden').find('.lasterror').text();
		if ($.trim(lasterror) !== $.trim(d.lastAttemptError)) {
		    $(pausedPanelWarning).find('.header > .message').find('.failedtimes').text(d.failedAttempts);
		    $(pausedPanelWarning).find('.content').empty().append('Last reported error:<span class="lasterror"></span>').find('.lasterror').text(d.lastAttemptError);
		    $(pausedPanelWarning).find('.learnmore').attr('href', appdb.config.endpoint.wiki + 'main:faq:cd_why_cd_paused_due_to_failed_attempts');
		}
	    } else {
		$(pausedPanelWarning).addClass('hidden').find('.content').empty();
	    }
	};
	this.doRender = function() {
		var d = this.options.cddata || {};

		if ($.trim(d.paused) === 'true') {
			$(this.dom).addClass('paused').removeClass('resumed');
		} else {
			$(this.dom).removeClass('paused').addClass('resumed');
		}

		this.options.userHasPermissions = this.hasPermissions();
		this.options.userHasAccessToken = this.hasAccessToken();
		if (this.options.userHadPermissions === !this.options.userHasPermissions || this.options.userHadAccessToken !== this.options.userHasAccessToken) {
			appdb.vappliance.ui.CurrentVAManager.loadCDHandler(d, {canManageVA: this.options.userHasPermissions, hasAccessToken: this.options.userHasAccessToken});
		}
		this.options.userHadPermissions = this.options.userHasPermissions;
		this.options.userHadAccessToken = this.options.userHasAccessToken;

		if (this.options.userHasPermissions === false) {
			this.renderErrorLoading(true, {title: 'No access to continuous delivery functionality.', message: 'It seems your permissions to manage this virtual appliance are revoked.'});
		} else {
			this.renderSetup(true);
			this.renderMetaData();
			this.renderLogs();
			this.renderActions();
		}
		this.renderNoProcessPanel();
		this.setWarningDueToFailedAttempts();
		this.renderNextCheck();
		this.showContents(true);
	};
	this.checkForNewVAVersion = function() {
		var d = this.options.cddata || {};
		var lastInstance = d.lastInstance || {};
		var runningInstance = d.runningInstance || {};
		if ($.trim(lastInstance.id) && $.trim(lastInstance.id) === $.trim(this.options.runningInstanceId) && $.trim(lastInstance.state) === 'completed') {
			this.options.runningInstanceId = null;
			return true;
		} else if ($.trim(runningInstance.id)) {
			this.options.runningInstanceId = $.trim(runningInstance.id);
		}
		return false;
	};
	this.getCDUrl = function() {
	    return appdb.config.endpoint.base + 'apps/cd?appid=' + appdb.pages.application.currentId() + '&cdLogSize=' + this.options.cdLogSize;
	};
	this.load = function(cb) {
	    $(this.dom).removeClass('error');
	    this.showContents(false);
	    this.renderErrorLoading(false);
	    this.renderLoadingData(true);
	    appdb.vappliance.components.CDVersion.stopMonitor(this);
	    $.ajax({
		    "type": 'GET',
		    url: this.getCDUrl(),
		    success: function(cddata, textStatus, jqXHR) {
			    this.renderLoadingData(false);
			    cddata = cddata || {};
			    if ($.trim(cddata.error)) {
				  this.renderErrorLoading(true, {title: "Could not load continuous delivery information", message: cddata.error});
			    } else {
				  this.onLoadComplete(cddata);
			    }
		    }.bind(this),
		    error: function( jqXHR, textStatus, errorThrown) {
			this.renderLoadingData(false);
			var errText = errorThrown + ' (' + textStatus + ')';
			try { resp = JSON.parse(jqXHR.responseText); errText = resp.error || errText;} catch(e) {}
			this.renderErrorLoading(true, {title: "Could not load continuous delivery information", message: errText});
			appdb.vappliance.components.CDVersion.startMonitor(this);
		    }.bind(this)
	    });
	};
	this.onLoadComplete = function(cddata) {
		this.renderErrorLoading(false);
		appdb.vappliance.components.CDVersion.stopMonitor();
		$(this.dom).find('.onloaderror').addClass('hidden').empty();
		if (this.options.cddata && this.options.cddata.id) {
			this.options.oldcddata = this.options.cddata || {};
		}
		this.options.cddata = cddata || {};
		if (this.checkForNewVAVersion()) {
			this.options.runningInstanceId = null;
			appdb.pages.Application.reload();
			return;
		}
		this.doRender(this.options.cddata);
		appdb.vappliance.components.CDVersion.startMonitor(this);
	};
	this.onLoadError = function(err) {
		$(this.dom).find('.onloaderror').removeClass('hidden').empty().append(err);
	};
	this.render = function() {
		$(this.dom).removeClass('hidden');
		this.load();
	};
	this._init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		appdb.vappliance.components.CDVersion.stopMonitor();
	};
	this._init();
}, {
    monitor: {
	    state: 'idle',
	    timer: null,
	    xhr: null,
	    interval: 2000
    },
    stopMonitor: function() {
	    if (appdb.vappliance.components.CDVersion.monitor.timer) {
		    clearTimeout(appdb.vappliance.components.CDVersion.monitor.timer);
	    }
	    if (appdb.vappliance.components.CDVersion.monitor.xhr && appdb.vappliance.components.CDVersion.monitor.xhr.abort) {
		    appdb.vappliance.components.CDVersion.monitor.xhr.abort();
		    appdb.vappliance.components.CDVersion.monitor.xhr = null;
	    }
    },
    isMonitoring: function() {
	return (appdb.vappliance.components.CDVersion.monitor.state !== 'idle' || appdb.vappliance.components.CDVersion.monitor.xhr);
    },
    startMonitor: function(cdversion) {
	    //appdb.vappliance.components.CDVersion.stopMonitor();
	    if (appdb.vappliance.components.CDVersion.isMonitoring()) {
		return;
	    }
	    function _scheduleCheck() {
		    if (appdb.vappliance.components.CDVersion.monitor.state === 'idle') {
			    appdb.vappliance.components.CDVersion.monitor.timer = setTimeout(_check, appdb.vappliance.components.CDVersion.monitor.interval);
		    }
	    }
	    function _check() {
		    appdb.vappliance.components.CDVersion.monitor.state = 'running';
		    appdb.vappliance.components.CDVersion.monitor.xhr = $.ajax({
			"type": 'GET',
			url: cdversion.getCDUrl(),
			success: function(cddata, textStatus, jqXHR) {
				appdb.vappliance.components.CDVersion.monitor.state = 'idle';
				cddata = cddata || {};
				if ($.trim(cddata.error)) {
				      cdversion.onLoadError(cddata.error);
				} else {
				      cdversion.onLoadComplete(cddata);
				}
				appdb.vappliance.components.CDVersion.monitor.state = 'idle';
				if (appdb.vappliance.components.CDVersion.monitor.xhr) {
				    _scheduleCheck();
				}
			},
			error: function( jqXHR, textStatus, errorThrown) {
				var errText = errorThrown + ' (' + textStatus + ')';
				try { resp = JSON.parse(jqXHR.responseText); errText = resp.error || errText;} catch(e) {}
				cdversion.onLoadError(errText);
				appdb.vappliance.components.CDVersion.monitor.state = 'idle';
				clearTimeout(appdb.vappliance.components.CDVersion.monitor.timer);
				appdb.vappliance.components.CDVersion.monitor.state = 'idle';
				appdb.vappliance.components.CDVersion.monitor.xhr = null;
				_scheduleCheck();
			}
		    });
	    }
	    _scheduleCheck();
    }
});

appdb.vappliance.ui.views.DataValueHandler = appdb.DefineClass("appdb.vappliance.ui.views.DataValueHandler", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dataValue: o.dataValue,
		dataCurrentValue: o.dataValue,
		dataType: o.dataType || "text",
		dataSource: o.dataSource || null,
		dataNormalize: o.dataNormalize || null,
		isMandatory: o.mandatory || false,
		validators: o.validators || [],
		editor: null,
		isValid: true,
		canValidate: true,
		constraints: {
			isRequired: false,
			maxSize: false,
			minSize: false,
			dataType: "text",
			regex: false,
			unique: false
		}
	};
	//to be overriden by handlers
	this.canValidate = function(){
		return this.options.canValidate;
	};
	this.initDefaultConstraints = function(){
		if( $(this.parent.dom).data("validate") === false ){
			this.options.canValidate = false;
		}
		this.options.constraints =  {
			isRequired: $(this.parent.dom).hasClass("mandatory"),
			maxSize: ((typeof $(this.parent.dom).data("maxsize")!== "undefined")?$(this.parent.dom).data("maxsize"):this.options.constraints.maxSize),
			minSize: ((typeof $(this.parent.dom).data("minsize")!=="undefined")?$(this.parent.dom).data("minsize"):this.options.constraints.minSize),
			dataType: $(this.parent.dom).data("datatype") || this.options.constraints.dataType,
			regex: $(this.parent.dom).data("regex") || this.options.constraints.regex,
			unique: $(this.parent.dom).data("unique") || this.options.constraints.unique
		};
		this.initConstraints();
		this.initValidators();
	};
	//to be overriden by handlers
	this.initConstraints = function(){
		//called by initDefaultConstraints
	};
	//Creates a validation list for the editor based on property constraints
	this.initValidators = function(){
		var validations = [];
		var consts = this.options.constraints || {};
		var opts = {container: this.dom, parent: this, canValidate: (function(self){return function(){return self.canValidate();};})(this)};
		for(var i in consts){
			if( !consts.hasOwnProperty(i) ) continue;
			if( typeof consts[i] === "undefined" || consts[i] === false ) continue; 
			var name = $.trim(i).toLowerCase();
			var obj;
			if( name === "unique" ){
				var uname = $.trim(name + this.parent.options.dataPath).toLowerCase();
				obj = appdb.vappliance.validators[uname] || appdb.FindNS(uname);
				if( !obj ){
					continue;
				}
			}else{
				obj = appdb.vappliance.validators[name] || appdb.FindNS(name);
			}
			
			if( obj ){
				opts.value = consts[i];
				validations.push( new obj(opts) );
			}
		}
		this.options.validators = this.options.validators || [];
		this.options.validators = [];
		this.options.validators = validations;
	};
	//Returns previous validation result
	this.isValid = function(){
		return this.options.isValid;
	};
	this.resetValidationMessage = function(){
		$(this.dom).children(".validationmessage").remove();
	};
	this.preRenderValidationMessage = function(){
		this.resetValidationMessage();
		var vm = $("<div class='validationmessage hidden' ><img src='/images/vappliance/warning.png' alt='' /><div class='validationerrormessage'></div></div>");
		$(this.dom).append(vm);
	};
	this.onRenderValidationMessage = function(errs){
		this.preRenderValidationMessage();
		errs = errs || [];
		errs = $.isArray(errs)?errs:[errs];
		var err;
		if( errs.length > 0 ){
			err = errs[0];
		}
		this.renderValidationMessage(err);
	};
	this.renderValidationMessage = function(err){
		$(this.dom).children(".validationmessage").children(".validationerrormessage").append("<span>" + err.message + "</span>");
		$(this.dom).children(".validationmessage").removeClass("hidden");
	};
	//Initialize validation html
	this.preValidate = function(v){
		return true;
	};
	//Validates and sets corresponding html
	this.onValidate = function(v, isrevalidation){
		isrevalidation = (typeof isrevalidation === "boolean")?isrevalidation:false;
		v = v || this.options.dataCurrentValue;
		var errors = this.validate(v, isrevalidation);
		var revalidate = $.trim($(this.parent.dom).data("revalidate"));
		if( revalidate !== "" && isrevalidation === false ){
			if( revalidate in this.parent.parent.options.props ){
				var revalidationobj = this.parent.parent.options.props[revalidate];
				revalidationobj.options.handler.onValidate(undefined, true);
			}
		}
		if( errors.length === 0 ){
			$(this.dom).removeClass("invalid");
			$(this.dom).removeClass("isempty");
			this.options.isValid = true;
			this.resetValidationMessage();
		}else{
			$(this.dom).addClass("invalid");
			this.options.isValid = false;
			var isempty = false; 
			$.each(errors, (function(self){
				return function(i, e){
					if( e.type === "isrequired"){
						isempty = true;
					}
				};
			})(this));
			if(isempty){
				$(this.dom).addClass("isempty");
			}else{
				$(this.dom).removeClass("isempty");
			}
			this.onRenderValidationMessage(errors);
		}
		this.parent.publish({event: "valuechanged", value: { property: this.parent, handler: this, value: this.options.currentValue, oldvalue: this.options.dataValue} });
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
	};
	//returns a validation error list
	this.validate = function(){
		if( !this.editor ){
			return [];
		}
		var errors = [];
		$.each(this.options.validators, function(i,e){
			var err = e.validate();
			if( err !== true ){
				errors.push(err);
			}
		});
		this.options.validationerrors = errors;
		return errors;
	};
	this.onValueChange = function(v){
		v = v || this.editor.get("displayedValue");
		v = $.trim(v).replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		this.options.dataCurrentValue = v;
		this.onValidate();
	};
	//to be overriden by handlers
	this.setupEditorStatus = function(){};
	//Set class invalid in value editor container 
	this.displayAsInvalid = function(display){
		display = (typeof display === "boolean")?display:true;
		if( display ){
			$(this.dom).addClass("invalid");
		}else{
			$(this.dom).removeClass("invalid");	
		}
	};
	//Set class focused in value editor container
	this.displayAsFocused = function(focused){
		focused = (typeof focused === "boolean")?focused:true;
		if( focused === true ){
			$(this.dom).addClass("focused");
		}else{
			$(this.dom).removeClass("focused");
		}
	};
	this.setFocus = function(focused){
		focused = ( typeof focused === "boolean" )?focused:false;
		this.parent.setFocus(focused);
		this.displayAsFocused(focused);
	};
	this.displayEditorStatus = function(display){
		display = (typeof display === "boolean")?display:true;
		if( display === true ){
			$(this.dom).children(".editorstatus").removeClass("hidden");
		}else{
			$(this.dom).children(".editorstatus").addClass("hidden");
		}
	};
	this.canEdit = function(){
		return ( userID && appdb.vappliance.ui.CurrentVAManager.canEdit() && this.parent.canEdit() );
	};
	this.isEmpty = function(){
		if( typeof this.options.dataCurrentValue === "string" && $.trim(this.options.dataCurrentValue) === "" ){
			return true;
		}else if($.isArray(this.options.dataCurrentValue) && this.options.dataCurrentValue.length === 0 ){
			return true;
		}else if( typeof this.options.dataCurrentValue === "object" && $.isEmptyObject(this.options.dataCurrentValue) ){
			return true;
		}
		return false;
	};
	this.getDisplayValue = function(){
		var displayValue = "" + this.options.dataCurrentValue;
		if( $.isPlainObject(this.options.dataCurrentValue) ){
			if(this.options.dataCurrentValue.val){
				displayValue = this.options.dataCurrentValue.val();
			}else{
				displayValue = "";
			}
		}
		return displayValue;
	};
	this.renderEditor = function(dom){
		if( $.isArray(this.options.dataSource) ){
			var selopts = [];
			$.each(this.options.dataSource, function(i,e){
				selopts.push({
					label: "<span class='icontext'><img src='/images/vappliance/hyper/" + $.trim(e.val()).toLowerCase().replace(/[\-\_]/g,"") + ".png' alt=''/><span>" + e.val() + "</span></div>",
					value: e.id
				});
			});
			this.editor = new dijit.form.Select({
				options: selopts,
				onFocus: (function(self){
					return function(){
						self.parent.setFocus(true);
						self.onValueChange();
					};
				})(this),
				onBlur:  (function(self){
					return function(){
						self.parent.setFocus(false);
						self.onValueChange();
					};
				})(this),
				onChange: (function(self){
					return function(v){
						self.parent.setFocus(true);
						self.onValueChange(v);
					};
				})(this)
			}, dom);
		} else {
			switch($.trim(this.options.dataType).toLowerCase()){
				case "longtext":
					this.editor = new dijit.form.SimpleTextarea({
						value: this.getDisplayValue(),
						placeHolder: "Provide a value",
						cols: 65,
						rows: 3,
						onFocus: (function(self){
							return function(){
								self.parent.setFocus(true);
								self.onValueChange();
							};
						})(this),
						onBlur:  (function(self){
							return function(){
								self.parent.setFocus(false);
								self.onValueChange();
							};
						})(this),
						onChange: (function(self){
							return function(v){
								self.parent.setFocus(true);
								self.onValueChange(v);
							};
						})(this),
						onKeyUp: (function(self){
							return function(v){
								self.setFocus(true);
								self.onValueChange();
							};
						})(this),
						onMouseUp: (function(self){
							return function(v){
								self.setFocus(true);
								self.onValueChange();
							};
						})(this)}, dom);
					break;
				case "list":
				case "text":
				default:
					this.editor = new dijit.form.ValidationTextBox({
						value: this.getDisplayValue(),
						placeHolder : "Provide a value...",
						required: $(this.parent.dom).hasClass("mandatory"),
						onFocus: (function(self){
							return function(){
								self.parent.setFocus(true);
								self.onValueChange();
							};
						})(this),
						onBlur:  (function(self){
							return function(){
								self.parent.setFocus(false);
								self.onValueChange();
							};
						})(this),
						onChange : (function(self){
							return function(v){
								self.parent.setFocus(true);
								self.onValueChange(v);
							};
						})(this),
						onKeyUp: (function(self){
							return function(v){
								self.setFocus(true);
								self.onValueChange();
							};
						})(this),
						onMouseUp: (function(self){
							return function(v){
								self.setFocus(true);
								self.onValueChange();
							};
						})(this)}, dom);
					break;
			}
		}
	};
	this.renderViewer = function(){
		var displayValue = "" + this.options.dataCurrentValue;
		if( $.isPlainObject(this.options.dataCurrentValue) ){
			if( typeof this.options.dataCurrentValue.val === "function"){
				displayValue = this.options.dataCurrentValue.val();
			}else if(typeof this.options.dataCurrentValue.val !== "undefined"){
				displayValue = this.options.dataCurrentValue.val;
			}else{
				displayValue = "";
			}
		}
		$(this.dom).append(this.normalizeValue(displayValue));
	};
	this.preRenderEditor = function(){
		this.initDefaultConstraints();
		var cont = $(this.dom).children(".editcontainer").clone(true);
		$(this.dom).empty().append(cont);
		
		if( $(cont).length === 0){
			$(this.dom).append("<div class='editcontainer'></div>");
		}
		$(this.dom).append("<div class='editorstatus hidden'></div>");
		if( this.options.isMandatory === true ){
			if( $(this.dom).children(".mandatorymessage").length === 0 ){ 
				var html = '<span class="mandatorymessage">' + (this.parent.options.dataPlaceHolder || this.editor.get("placeHolder")) + '</span>';
				$(this.dom).append(html);
				$(this.dom).children(".mandatorymessage").off("click").on("click", (function(self){
					return function(ev){
						ev.preventDefault();
						self.editor.focus();
						return false;
					};
				})(this));
			}
		}else{
			$(this.dom).children(".mandatorymessage").remove();
		}
	};
	this.postRenderEditor = function(){
		//to be overriden by handlers
	};
	this.renderEdit = function(){
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.register(this);
		this.preRenderEditor();
		this.renderEditor($(this.dom).children(".editcontainer")[0]);
		setTimeout((function(self){
			return function(){
				self.postRenderEditor();
			};
		})(this), 1);
	};
	this.renderView = function(){
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.unregister(this);
		this.reset();
		$(this.dom).removeClass("empty").empty();
		this.renderViewer();
		setTimeout((function(self){
			return function(){
				self.postRenderViewer();
			};
		})(this), 1);
	};
	this.postRenderViewer = function(){
		//to be overriden by handlers
	};
	this.reset = function(full){
		full = (typeof full === "boolean")?full:true;
		if( full === false ){
			this.renderView();
			return;
		}
		$(this.options.validators, function(i,e){
			e.reset();
		});
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.unregister(this);
		appdb.vappliance.ui.CurrentVAVersionSelectionRegister.unregister(this);
		if( this.editor ){
			this.editor.destroyRecursive(false);
			this.editor = null;
		}
		$(this.dom).empty();
	};
	this.hasChanges = function(){
		if( this.options.currentDataValue === this.options.dataValue ){
			return false;
		}
		return true;
	};
	this.edit = function(){
		if(  this.canEdit() ){
			this.renderEdit();
		}else{
			this.renderView();
		}
	};
	this.cancel = function(){
		this.options.dataCurrentValue = this.options.dataValue;
		this.renderView();
	};
	this.save = function(){
		this.options.dataValue = this.options.dataCurrentValue;
	};
	this.render = function(){
		this.renderView();
	};
	this.normalizeValue = function(v) {
	    if (typeof this.options.dataNormalize === 'function') {
		return this.options.dataNormalize(v, this);
	    }
	    return v;
	};
	this.getValue = function(){
		return this.options.dataCurrentValue;
	};
	this.setValue = function(d){
		if( typeof d === "string" ){
			d = d.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		}
		this.options.dataCurrentValue = this.normalizeValue(d);
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		if( typeof(this.options.dataSource) === "string" ){
			var ds = appdb.FindNS(this.options.dataSource);
			if( ds ){
				this.options.dataSource = ds;
			}
		}
		if (typeof(this.options.dataNormalize) === "string") {
			var ds = appdb.FindNS(this.options.dataNormalize);
			if( ds ){
			    this.options.dataNormalize = ds;
			}
		}
	};
	this._init();
});
appdb.vappliance.ui.views.UrlValidator = appdb.views.UrlValidator;
appdb.vappliance.ui.views.DataValueHandlerText = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerText", function(o){
	this.setEditorStatus = function(s){
		s = s || {};
		$(this.dom).children(".editorstatus").find(".validation .current").html(s.current);
		if( s.haserror ){
			this.displayAsInvalid(true);
			$(this.dom).children(".editorstatus").find(".validation").addClass("error");
		}else{
			this.displayAsInvalid(false);
			$(this.dom).children(".editorstatus").find(".validation").removeClass("error");
		}
		this.displayEditorStatus(true);
	};
	this.setupEditorStatus = function(){
		var html = "<span class='editorstatus'>using <span class='current'>" + this.options.dataCurrentValue.length + "</span> of <span class='total'>" + this.options.constraints.maxSize + "</span> characters</span>";
		$(this.dom).children(".editorstatus").append(html);
	};
	this.renderValidationMessage = function(err){
		if( !err.message ){
			err.message = "Invalid Value";
		}
		$(this.dom).children(".validationmessage").addClass("tooltip");
		$(this.dom).children(".validationmessage").append("<div class='arrow'></div>");
		$(this.dom).children(".validationmessage").children(".validationerrormessage").append("<span>" + err.message + "</span>");
		$(this.dom).children(".validationmessage").removeClass("hidden");
	};
	this.renderValidationError = function(err){
		if( this.options.tooltip  ){
			this.options.tooltip.destroyRecursive(false);
			this.options.tooltip = null;
		}
		$(this.dom).children(".validationmessage").remove();
		if( err ){
			var vm = $("<a class='validationmessage' title='' href='#'><img src='/images/vappliance/warning.png' alt='' /><div class='validationerrormessage hidden'><span>" + err.message + "</span></div></a>");
			$(this.dom).append(vm);
			
		}
	};
	this.onValueChange = function(v){
		v = v || ((this.editor)?this.editor.get("displayedValue"):'');
		v = v.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		this.options.dataCurrentValue = v;
		this.onValidate();
	};
	this.renderEditor = function(dom){
		var val = this.getDisplayValue();
		val = val.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		var style = "";
		if( this.canValidate() && this.options.constraints.maxSize > 10 && this.options.constraints.maxSize <= 59){
			var p = (this.options.constraints.maxSize >57)?57:this.options.constraints.maxSize;
			style = "min-width:" + (p * 8) + "px";
		}
		this.editor = new dijit.form.ValidationTextBox({
			value: val,
			onFocus: (function(self){
				return function(){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.setFocus(false);
					self.onValueChange();
				};
			})(this),
			onChange : (function(self){
				return function(v){
					self.setFocus(true);
					self.onValueChange(v);
				};
			})(this),
			onKeyUp: (function(self){
				return function(v){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			onMouseUp: (function(self){
				return function(v){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			style: style
		}, dom);
	};
	this.postRenderEditor = function(){
		this.onValueChange();
	};
	this.renderMaxSizeView = function(){
		if( $(this.parent.dom).hasClass("editmode") === true ) return;
		var ms = $(this.parent.dom).data("displaymaxsize");
		if( $.trim(ms) === "" ) return;
		ms = parseInt(ms);
		if( ms <= 3 ) return;
		$(this.dom).children(".fullvalue").remove();
		var h = $.trim($(this.dom).text());
		if( h.length <= ms ) return;
		
		$(this.dom).empty();
		$(this.dom).append(h.slice(0,ms-3) + "...");
		$(this.dom).append("<div class='fullvalue'>"+h+"</div>");
	};
	this.postRenderViewer = function(){
		this.renderMaxSizeView();
	};
});
appdb.vappliance.ui.views.DataValueHandlerNumber = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerNumber", function(o){
	this.setEditorStatus = function(s){
		s = s || {};
		$(this.dom).children(".editorstatus").find(".validation .current").html(s.current);
		if( s.haserror ){
			this.displayAsInvalid(true);
			$(this.dom).children(".editorstatus").find(".validation").addClass("error");
		}else{
			this.displayAsInvalid(false);
			$(this.dom).children(".editorstatus").find(".validation").removeClass("error");
		}
		this.displayEditorStatus(true);
	};
	this.setupEditorStatus = function(){
		var html = "<span class='editorstatus'>using <span class='current'>" + this.options.dataCurrentValue.length + "</span> of <span class='total'>" + this.options.constraints.maxSize + "</span> characters</span>";
		$(this.dom).children(".editorstatus").append(html);
	};
	this.renderValidationMessage = function(err){
		if( !err.message ){
			err.message = "Invalid Value";
		}
		$(this.dom).children(".validationmessage").addClass("tooltip");
		$(this.dom).children(".validationmessage").append("<div class='arrow'></div>");
		$(this.dom).children(".validationmessage").children(".validationerrormessage").append("<span>" + err.message + "</span>");
		$(this.dom).children(".validationmessage").removeClass("hidden");
	};
	this.renderValidationError = function(err){
		if( this.options.tooltip  ){
			this.options.tooltip.destroyRecursive(false);
			this.options.tooltip = null;
		}
		$(this.dom).children(".validationmessage").remove();
		if( err ){
			var vm = $("<a class='validationmessage' title='' href='#'><img src='/images/vappliance/warning.png' alt='' /><div class='validationerrormessage hidden'><span>" + err.message + "</span></div></a>");
			$(this.dom).append(vm);
			
		}
	};
	this.postRenderEditor = function(){
		this.options.dataType = "number";
		this.setupEditorStatus();
		this.onValueChange();
	};
});
appdb.vappliance.ui.views.DataValueHandlerLongtext = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerLongtext", function(o){
	this.initConstraints = function(){
		if( this.canValidate() ){
			this.options.constraints = {
				isRequired: this.options.constraints.isRequired || false,
				maxSize: this.options.constraints.maxSize || 1000
			};
		}
	};
	this.setEditorStatus = function(s){
		s = s || {};
		$(this.dom).children(".editorstatus").find(".validation .current").html(s.current);
		if( s.haserror ){
			this.displayAsInvalid(true);
			$(this.dom).children(".editorstatus").find(".validation").addClass("error");
		}else {
			if(this.isValid() === true){
				this.displayAsInvalid(false);
			}
			$(this.dom).children(".editorstatus").find(".validation").removeClass("error");
		}
		this.displayEditorStatus(true);
	};
	this.setupEditorStatus = function(){
		var html = "<span class='validation'>using <span class='current'>" + this.options.dataCurrentValue.length + "</span> of <span class='total'>" + this.options.constraints.maxSize + "</span> characters</span>";
		$(this.dom).children(".editorstatus").append(html);
	};
	this.onValueChange = function(v){
		v = v || this.editor.get("displayedValue");
		v = $.trim(v.replace(/\<br\ class\=\'systemline\'\/\>/gm,"/n").replace(/\</g,"&lt;").replace(/\>/g,"&gt;"));
		this.options.dataCurrentValue = $.trim(v);
		this.onValidate();
		var error = (v.length > this.options.constraints.maxSize || v.length < this.options.constraints.minSize)?true:false;
		this.setEditorStatus({current: v.length , haserror: error});
	};
	this.renderEditor = function(dom){
		this.clearMoreButton();
		var v = this.getDisplayValue();
		v = $.trim(v.replace(/\<br\ class\=\'systemline\'\/\>/gm,"/n").replace(/\</g,"&lt;").replace(/\>/g,"&gt;"));
		this.editor = new dijit.form.SimpleTextarea({
			value: v,
			placeHolder: "Provide a value",
			cols: 65,
			rows: 3,
			onFocus: (function(self){
				return function(){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.setFocus(false);
					self.onValueChange();
				};
			})(this),
			onChange: (function(self){
				return function(v){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			onKeyUp: (function(self){
				return function(){
					self.onValueChange();
				};
			})(this)
		}, dom);
	};
	this.postRenderEditor = function(){
		this.setupEditorStatus();
		this.onValueChange();
	};
	this.reRender = function(){
		setTimeout((function(self){
			return function(){
				self.renderMoreButton();	
			};
		})(this),1);
	};
	this.initMoreButton = function(){
		this.clearMoreButton();
		if( $(this.parent.dom).hasClass("editmode") ) return;
		var autocollapse = $(this.parent.dom).hasClass("autocollapse");
		var togglemore = $(this.parent.dom).hasClass("togglemore");
		if(  autocollapse === false && togglemore === false) return;
		var textdata = $("<div class='textdata'></div>");
		var morebutton = $("<div class='morebutton'></div>");
		$(textdata).append($(this.dom).html());
		$(this.dom).empty();
		$(this.dom).append(textdata).append(morebutton);
		if( togglemore ){
			appdb.vappliance.ui.CurrentVAVersionSelectionRegister.register(this);
			$(this.dom).parent().addClass("autocollapse");
			$(morebutton).append("<span>...more</span>");
			$(morebutton).off("click").on("click", function(ev){
				ev.preventDefault();
				$(this).parent().parent().toggleClass("expanded");
				if( $(this).parent().parent().hasClass("expanded") ){
					$(this).children("span").text("...less");
				}else{
					$(this).children("span").text("...more");
				}
				return false;
			});
		}else {
			$(morebutton).append("<span>.....</span>");
		}
		this.renderMoreButton();
	};
	this.clearMoreButton = function(){
		$(this.parent.dom).removeClass("expanded overflowed");
		if( $(this.dom).children(".morebutton").length > 0 ){
			var html = $(this.dom).children(".textdata").html();
			$(this.dom).empty();
			$(this.dom).html(html);
			appdb.vappliance.ui.CurrentVAVersionSelectionRegister.unregister(this);
		}
	};
	this.renderMoreButton = function(){
		if($(this.dom).children(".morebutton").length > 0){
			var tdh = $(this.dom).children(".textdata").height();
			var domh = $(this.dom).height();
			if( tdh > domh){
				$(this.dom).parent().addClass("overflowed");
			}else{
				$(this.dom).parent().removeClass("overflowed");
			}
		}
	};
	this.renderViewer = function(){
		var cv = "" + this.options.dataCurrentValue;
		cv = cv.replace(/(\r\n|\n|\r)/gm,"<br class='systemline'/>");
		$(this.dom).empty().html(cv);
		this.initMoreButton(cv);
	};
	this.postRenderViewer = function(){
		this.initMoreButton();
	};
});
appdb.vappliance.ui.views.DataValueHandlerBinarysize = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler, "appdb.vappliance.ui.views.DataValueHandlerBinarySize", function(o){
	this.renderViewer = function(){
		var cv = "" + this.options.dataCurrentValue;
		cv = cv.replace(/(\r\n|\n|\r)/gm,"");
		cv = appdb.vappliance.utils.formatSizeUnits(parseInt(cv), true);
		$(this.dom).empty().html(cv);
	};
});
appdb.vappliance.ui.views.DataValueHandlerList = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerList", function(o){
	this.renderEditor = function(dom){
		var selid = ( ( $.isPlainObject(this.options.dataCurrentValue) && this.options.dataCurrentValue.id )?this.options.dataCurrentValue.id:-1 );
		if( $.isArray(this.options.dataSource) ){
			var selobj = null;
			var selopts = [];
			$.each(this.options.dataSource, function(i,e){
				var val = e.val();
				val = val.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
				selopts.push({
					label: "<span>" + val + "</span>",
					value: e.id,
					selected: ( (selid>-1)?(e.id === selid):(i===0) )
				});
				if( selid === -1 || selopts[selopts.length - 1].selected === true){
					selid = selopts[selopts.length - 1].value;
					selobj = e;
				}
			});
			this.options.dataCurrentValue = selobj;
			this.editor = new dijit.form.Select({
				options: selopts,
				onFocus: (function(self){
					return function(){
						self.parent.setFocus(true);
					};
				})(this),
				onBlur:  (function(self){
					return function(){
						self.parent.setFocus(false);
					};
				})(this),
				onChange: (function(self){
					return function(v){
						var res = v;
						if( self.options.dataSource ){
							$.each(self.options.dataSource, function(i,e){
								if( e.id === res ){
									res = e;
								}
							});
						}
						self.options.dataCurrentValue = res;
						self.onValidate();
						self.parent.setFocus(true);
					};
				})(this)
			}, dom);
		}
	};
	this.postRenderEditor = function(){
		this.onValueChange();
	};
});
appdb.vappliance.ui.views.DataValueHandlerValuelist = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerValuelist", function(o){
	this.getTypedValue = function(v) {
	    var dataType = ((this.options.constraints || {}).dataType || 'string');
	    switch(dataType) {
		case 'number':
		    return parseInt(v);
		case 'float':
		    return parseFloat(v);
		case 'string':
		default:
		    return $.trim(v);
	    }
	};
	this.onValueChange = function(v){
		this.parent.options.currentValue = v;
		this.onValidate(v);
	};
	this.renderEditor = function(dom){
		var selid = '';
		if ($.isPlainObject(this.options.dataCurrentValue)) {
		    if (this.options.dataCurrentValue.id) {
			selid = this.options.dataCurrentValue.id;
		    }
		} else {
		    selid = $.trim(this.options.dataCurrentValue);
		}
		selid = this.normalizeValue(selid);
		if( $.isArray(this.options.dataSource) ){
			var selobj = null;
			var selopts = [];
			$.each(this.options.dataSource, function(i,val){
				var value = ($.isPlainObject(val)) ? val.id : val;
				var displayValue= ($.isPlainObject(val) && typeof val.val === 'function') ? val.val() : value; 
				val = $.trim('' + value).replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
				selopts.push({
					label: "<span>" + displayValue + "</span>",
					value: value,
					selected: ( (selid !== '')?(value === selid):(i === 0) )
				});
				if( selid === '' || selopts[selopts.length - 1].selected === true){
					selid = selopts[selopts.length - 1].value;
					selobj = value;
				}
			}.bind(this));
			this.options.dataCurrentValue = selobj;
			this.editor = new dijit.form.Select({
				options: selopts,
				onFocus: (function(self){
					return function(){
						self.parent.setFocus(true);
					};
				})(this),
				onBlur:  (function(self){
					return function(){
						self.parent.setFocus(false);
					};
				})(this),
				onChange: (function(self){
					return function(v){
						var res = $.trim('' + v);
						self.options.dataCurrentValue = res;
						self.onValueChange(self.options.dataCurrentValue);
						self.parent.setFocus(true);
					};
				})(this)
			}, dom);
			this.options.dataCurrentValue = selobj;
			this.editor.set('displayValue', this.options.dataCurrentValue);
		}
	};
	this.postRenderEditor = function(){
		this.onValueChange(this.options.dataCurrentValue);
	};
});
appdb.vappliance.ui.views.DataValueHandlerFilterlist = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerFilterlist", function(o){
	this.initConstraints = function(){
		this.options.constraints = {
			isRequired: this.options.constraints.isRequired || false,
			listitem: this.options.dataSource || undefined,
			uniqueurlhypervisor: ( ( $.trim( $(this.parent.dom).data("validator") ).toLowerCase() === "uniqueurlhypervisor")?true:false )
		};
	};
	this.onValueChange = function(v){
		v = v || this.options.dataCurrentValue;
		
		if( $.trim(v) === "" ){
			v =  this.editor.get("displayedValue");
			v = v.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
			this.options.dataCurrentValue = v;
		}else{
			this.options.dataCurrentValue = v;
		}
		this.onValidate();
		if( $.trim(this.editor.get("displayedValue")) === "" ){
			$(this.dom).addClass("isempty");
		}else{
			$(this.dom).removeClass("isempty");
		}
	};
	this.setCurrentValueById = function(id){
		var res;
		var source = this.options.dataSource;
		if( source ){
			$.each(source, function(i,e){
				if( e.id === id ){
					res = e;
				}
			});
		}
		this.options.dataCurrentValue = res;
	};
	this.renderValidationMessage = function(err){
		if( !err.message ){
			err.message = "Invalid Value";
		}
		$(this.dom).children(".validationmessage").addClass("tooltip");
		$(this.dom).children(".validationmessage").append("<div class='arrow'></div>");
		$(this.dom).children(".validationmessage").children(".validationerrormessage").append("<span>" + err.message + "</span>");
		$(this.dom).children(".validationmessage").removeClass("hidden");
	};
	this.renderOptionItem = function(e, selected){
		var val = e.val();
		val = val.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		return "<option value='" + e.id + "' " + ((selected===true)?"selected":"") + ">" + val + "</option>";
	};
	this.renderSelect = function(){
		var select = $("<select></select>");
		var selid = ( ( $.isPlainObject(this.options.dataCurrentValue) && this.options.dataCurrentValue.id )?this.options.dataCurrentValue.id:-1 );
		var selobj;
		
		if( $.isArray(this.options.dataSource) === false ) {
			return select;
		}
		
		$(select).append("<option value='-1' ></option>");
		var preselectid = $(this.parent.dom).data("selectid") || null;
		$.each(this.options.dataSource, (function(self){
			return function(i,e){
				var selected = ( (selid>-1)?(e.id === selid):( ($.trim(e.id) === $.trim(preselectid)) || false ) );
				var val = e.val();
				val = val.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
				$(select).append( self.renderOptionItem(e,selected) );
				if( selected === true ){
					selid = e.id;
					selobj = e;
				}
			};
		})(this));
		this.options.dataCurrentValue = selobj;
		return select;
	};
	this.renderEditor = function(dom){
		if( $.isArray(this.options.dataSource) ){
			$(dom).after( this.renderSelect() );
			this.editor = new dijit.form.FilteringSelect({
				autoComplete: true,
				onFocus: (function(self){
					return function(){
						self.setFocus(true);
						self.setCurrentValueById(this.get("value"));
						self.onValueChange();
					};
				})(this),
				onBlur:  (function(self){
					return function(){
						self.setFocus(false);
						self.setCurrentValueById(this.get("value"));
						self.onValueChange();
					};
				})(this),
				onChange: (function(self){
					return function(v){
						self.setCurrentValueById(v);
						self.onValueChange();
					};
				})(this),
				onKeyUp: (function(self){
					return function(v){
						self.setFocus(true);
						self.setCurrentValueById(this.get("value"));
						self.onValueChange();
					};
				})(this),
				onMouseUp: (function(self){
					return function(v){
						self.setFocus(true);
						self.setCurrentValueById(this.get("value"));
						self.onValueChange();
					};
				})(this)
			}, $(this.dom).children("select").get(0));
			$(dom).remove();
			this.editor.validate = function(){};
		}
	};
	this.postRenderEditor = function(){
		this.onValueChange();
	};
	this.renderMaxSizeView = function(){
		if( $(this.parent.dom).hasClass("editmode") === true ) return;
		var ms = $(this.parent.dom).data("displaymaxsize");
		if( $.trim(ms) === "" ) return;
		ms = parseInt(ms);
		if( ms <= 3 ) return;
		$(this.dom).children(".fullvalue").remove();
		var h = $.trim($(this.dom).text());
		if( h.length <= ms ) return;
		
		$(this.dom).empty();
		$(this.dom).append(h.slice(0,ms-3) + "...");
		$(this.dom).append("<div class='fullvalue'>"+h+"</div>");
	};
	this.postRenderViewer = function(){
		this.renderMaxSizeView();
	};
});
appdb.vappliance.ui.views.DataValueHandlerChecksum = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerText,"appdb.vappliance.ui.views.DataValueHandlerChecksum", function(o){
	this.canValidate = function(){
		var integrity = this.parent.parent.options.props.integrity.options.handler.getDisplayValue();
		if( integrity === "false"){
			return true;
		}
		return false;
	};
	this.renderViewer = function(dom){
		var vals = this.options.dataValue;
		vals = vals || [];
		vals = $.isArray(vals)?vals:[vals];
		var v = "<div class='checksums'>";
		$.each(vals, function(i,e){
			var value = (typeof e.val === "function")?e.val():"";
			value = value.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
			var hash = (typeof e.hash === "string")?e.hash:"sha512";
			v += "<div class='checksum'><span class='hash'>"+hash+":</span><span class='value'>"+value+"</span></div>";
		});
		v += "<div class='checksum'><span class='hash'>Size:</span><span class='value'>"+($.trim(this.parent.options.data.size) || "0") +" bytes</span></div>";
		v += "</div>";
		$(this.dom).append(v);
	};
	this.onValueChange = function(v){
		v = v || this.editor.get("displayedValue");
		v = v.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		this.options.dataCurrentValue = v;
		this.onValidate();
		this.checkChanges();
	};
	this.checkChanges = function(){
		var v = (typeof this.options.dataValue.val === "function")?this.options.dataValue.val():this.options.dataValue;
		if( v !== this.options.dataCurrentValue ){
			$(this.parent.parent.dom).addClass("checksumchanged");
		}else{
			$(this.parent.parent.dom).removeClass("checksumchanged");
		}
	};
	this.renderEditor = function(dom){
		this.editor = new dijit.form.ValidationTextBox({
			value: this.getDisplayValue(),
			onFocus: (function(self){
				return function(){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.setFocus(false);
					self.onValueChange();
				};
			})(this),
			onChange : (function(self){
				return function(v){
					v = v.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
					self.onValueChange(v);
					self.setValue({hash:"sha512", val: function(){return v;}});
				};
			})(this)
		}, dom);
	};
});
appdb.vappliance.ui.views.DataValueHandlerFilesize = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerNumber,"appdb.vappliance.ui.views.DataValueHandlerFilesize", function(o){
	this.canValidate = function(){
		var integrity = this.parent.parent.options.props.integrity.options.handler.getDisplayValue();
		if( integrity === "false"){
			return true;
		}
		return false;
	};
	this.onValueChange = function(v){
		v = v || this.editor.get("displayedValue");
		v = v.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		this.options.dataCurrentValue = v;
		if( $.trim(v) === "0" ){
			this.editor.set("value","");
			return;
		}
		this.onValidate();
		this.checkChanges();
	};
	this.checkChanges = function(){
		if( this.options.dataValue !== this.options.dataCurrentValue ){
			$(this.parent.parent.dom).addClass("sizechanged");
		}else{
			$(this.parent.parent.dom).removeClass("sizechanged");
		}
	};
	this.getDisplayValue = function(){
		var displayValue = "" + this.options.dataCurrentValue;
		if( $.isPlainObject(this.options.dataCurrentValue) ){
			if(this.options.dataCurrentValue.val){
				displayValue = this.options.dataCurrentValue.val();
			}else{
				displayValue = "";
			}
		}
		if( displayValue == "0" ){
			return "";
		}
		return displayValue;
	};
});
appdb.vappliance.ui.views.DataValueHandlerImagelist = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerImagelist", function(o){
	this.renderEditor = function(dom){
		var selid = ( ( $.isPlainObject(this.options.dataCurrentValue) && this.options.dataCurrentValue.id )?this.options.dataCurrentValue.id:-1 );
		if( $.isArray(this.options.dataSource) ){
			var selobj = null;
			var selopts = [];
			$.each(this.options.dataSource, function(i,e){
				selopts.push({
					label: "<span class='hyperv icontext'><img src='/images/vappliance/hyper/" + $.trim(e.val()).toLowerCase().replace(/[\-\_]/g,"") + ".png' alt='"+e.val()+"'/></span>",
					value: e.id,
					selected: ( (selid>-1)?(e.id === selid):false )
				});
				if( selid === -1 || selopts[selopts.length - 1].selected === true){
					selid = selopts[selopts.length - 1].value;
					selobj = e;
				}
			});
			this.options.dataCurrentValue = selobj;
			this.editor = new dijit.form.Select({
				options: selopts,
				onFocus: (function(self){
					return function(){
						self.parent.setFocus(true);
					};
				})(this),
				onBlur:  (function(self){
					return function(){
						self.parent.setFocus(false);
					};
				})(this),
				onChange: (function(self){
					return function(v){
						var res = v;
						if( self.options.dataSource ){
							$.each(self.options.dataSource, function(i,e){
								if( e.id === res ){
									res = e;
								}
							});
						}
						self.options.dataCurrentValue = res;
						self.onValidate();
						self.parent.setFocus(true);
					};
				})(this)
			}, dom);
		}
	};
	this.renderViewer = function(dom){
		var v = this.getDisplayValue();
		if( $.trim(v) === "" ) return; 
		v = "<span class='icontext'><img src='/images/vappliance/hyper/" + $.trim(v).toLowerCase().replace(/[\-\_]/g,"") + ".png' alt='"+v+"'/></div>";
		$(this.dom).append(v);
	};
	this.postRenderEditor = function(){
		this.onValueChange();
	};
});renderValidation = 
appdb.vappliance.ui.views.DataValueHandlerImagefilterlist = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerFilterlist,"appdb.vappliance.ui.views.DataValueHandlerImagefilterlist", function(o){
	this.renderViewer = function(dom){
		var v = this.getDisplayValue();
		if( $.trim(v) === "" ) return; 
		v = "<span class='icontext'><img src='/images/vappliance/hyper/" + $.trim(v).toLowerCase().replace(/[\-\_]/g,"") + ".png' alt='"+v+"'/></div>";
		$(this.dom).append(v);
	};
	this.postRenderEditor = function(){
		this.onValueChange();
	};
});
appdb.vappliance.ui.views.DataValueHandlerPerson = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerPerson", function(o){
	this.renderViewer = function(dom){
		var v = this.getValue();
		if( $.trim(v) === "" ) return;
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
		$(this.dom).append(h);
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
});
appdb.vappliance.ui.views.DataValueHandlerUrl = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerText,"appdb.vappliance.ui.views.DataValueHandlerUrl", function(o){
	this.initConstraints = function(){
		this.options.constraints = $.extend(this.options.constraints,{
				url: false,
				uniqueurlhypervisor: ( ( $.trim( $(this.parent.dom).data("validator") ).toLowerCase() === "uniqueurlhypervisor")?true:false )
		});
	};
	this.renderUrlValidator = function(dom){
		if( this.options.urlvalidator ){
			return;
		}
		this.options.urlvalidator = new appdb.vappliance.ui.views.UrlValidator({
			container: dom,
			parent: this,
			url: this.options.dataValue || "",
			returnMime: "binary"
		});
		this.options.urlvalidator.subscribe({event: "validation", callback: function(v){
				if( v.isValid ){
					this.options.validValue = true;
				}else{
					$(this.editor.domNode).addClass("dijitValidationTextBoxError dijitError");
					this.options.validValue = false;
				}
				this.displayInvalidValue();
		}, caller: this});
	};
	this.onValueChange = function(v){
		v = v || this.editor.get("displayedValue");
		v = v.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		this.options.dataCurrentValue = v;
		this.onValidate();
		if( $.trim(v) === $.trim(this.options.urlvalidator.options.currentUrl) ){
			this.options.urlvalidator.displayMessage(true);
		}else{
			this.options.urlvalidator.displayMessage(false);
		}
		
		if( $.trim(v) === ""){
			this.options.urlvalidator.hide();
		}else{
			this.options.urlvalidator.show();
		}
	};
	this.displayInvalidValue = function(invalid){
		invalid = invalid || ( ( typeof this.options.validValue === "boolean")?!this.options.validValue:false );
		if( invalid ){
			$(this.editor.domNode).addClass("dijitValidationTextBoxError dijitError");
		}else{
			$(this.editor.domNode).removeClass("dijitValidationTextBoxError dijitError");
		}
	};
	this.renderEditor = function(dom){
		this.editor = new dijit.form.ValidationTextBox({
			value: this.getDisplayValue(),
			onFocus: (function(self){
				return function(){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.setFocus(false);
					self.onValueChange();
				};
			})(this),
			onChange : (function(self){
				return function(v){
					self.onValueChange(v);
					self.checkChecksum(self.options.dataCurrentValue);
				};
			})(this),
			onKeyUp: (function(self){
				return function(v){
					self.onValueChange();
					self.checkChecksum(self.options.dataCurrentValue);
				};
			})(this),
			onMouseUp: (function(self){
				return function(v){
					self.onValueChange();
				};
			})(this)
		}, dom);
		this.renderUrlValidator(this.dom);
		setTimeout((function(self){
			return function(){
				self.initChecksumChecker();
			};
		})(this),1);		
	};
	this.postRenderEditor = function(){
		if( appdb.config.features.singleVMIPolicy === true ) {
			this.renderPreviousUrl();
		}
		this.onValueChange();
	};
	this.isPrivateVersion = function() {
		return $(this.parent.parent.dom).closest('.workingversion').closest('.vappliancecontainer').hasClass('isprivate');
	};
	this.setIntegrityCheck = function(v) {
		var obj = dijit.byNode($(this.parent.parent.dom).find(".vmiversion-integrity > .value > .dijit")[0]);
		if ( !obj ) {
			return "";
		}
		if (this.isPrivateVersion() === false) {
			if (v === false && this.options.integritySelected === false) {
			    obj.set("checked", false);
			} else {
			    obj.set('checked', true);
			}
		}
	}
	this.checksumValue = function(v){
		var obj = dijit.byNode($(this.parent.parent.dom).find(".vmiversion-checksum512 > .value > .dijit")[0]);
		if( !obj ){
			return "";
		}
		var val = $.trim(obj.get("displayedValue"));
		if( val !== "" && val !== this.options.checksum ){
			return val;
		}
		if( typeof v !== "undefined"){
			obj.set("value",v);
		}
		return obj.get("displayedValue");
	};
	this.sizeValue = function(v){
		var obj = dijit.byNode($(this.parent.parent.dom).find(".vmiversion-size > .value > .dijit")[0]);
		if( !obj ){
			return "";
		}
		if( typeof v !== "undefined"){
			var val = $.trim(obj.get("displayedValue"));
			if( val !== "" && val !== this.options.filesize ){
				return val;
			}
			obj.set("value",v);
		}
		return obj.get("displayedValue");
	};
	this.ovfurl = function(v){
		var obj = dijit.byNode($(this.parent.parent.dom).find(".vmiversion-ovfurl > .value > .dijit")[0]);
		if( !obj ){
			return "";
		}
		if( typeof v !== "undefined"){
			var val = $.trim(obj.get("displayedValue"));
			if( val !== "" && val !== this.options.ovfurl ){
				return val;
			}
			obj.set("value",v);
		}
		return obj.get("displayedValue");
	};
	this.renderPreviousUrl = function(){
		var dom = $(this.dom).closest('.property').find('.previouslocation');
		var par = this.parent.getClosestParent('appdb.vappliance.ui.views.VApplianceVMIVersionItem');
		var data = (par && par.getData)?par.getData():{};
		$(dom).off('click');
		if( $.trim(data.prevUrl) && this.editor ) {
			this.editor.setValue("");
			$(dom).removeClass('hidden');
			$(dom).on('click', (function(self, prevUrl){
				return function(ev){
					ev.preventDefault();
					self.editor.setValue(prevUrl);
					return false;
				};
			})(this, $.trim(data.prevUrl)) );
		} else {
			$(dom).addClass('hidden');
		}
	};
	this.isIntegrityCheckSelected = function() {
	    var obj = dijit.byNode($(this.parent.parent.dom).find(".vmiversion-integrity > .value > .dijit")[0]);
	    if (!obj) return false;
	    return obj.get('checked') || false;
	}
	this.initChecksumChecker = function(){
		this.options.checksum = this.checksumValue();
		this.options.filesize = this.sizeValue();
		this.options.ovfurl = this.ovfurl();
		this.options.integritySelected = this.isIntegrityCheckSelected();
	};
	this.locationChanged = function(changed){
		changed = (typeof changed === "boolean")?changed:true;
		if(changed === true ){
			$(this.parent.parent.dom).addClass("locationchanged");
		}else{
			$(this.parent.parent.dom).removeClass("locationchanged");
		}
	};
	this.checkChecksum = function(v){
		if( v !== this.options.dataValue ){
			this.checksumValue("");	
			this.sizeValue("");
			this.ovfurl("");
			this.setIntegrityCheck(true);
			this.locationChanged(true);
		}else{
			this.checksumValue(this.options.checksum);
			this.sizeValue(this.options.filesize);
			this.ovfurl(this.options.ovfurl);
			this.setIntegrityCheck(false);
			this.locationChanged(false);
		}
	};
});
appdb.vappliance.ui.views.DataValueHandlerGenericurl = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerText,"appdb.vappliance.ui.views.DataValueHandlerGenericurl", function(o){
	this.initConstraints = function(){
		if( $(this.options.parent.dom).hasClass("mandatory") ){
			this.options.constraints = $.extend(this.options.constraints,{
				url: true
			});
		}else{
			this.options.constraints = $.extend(this.options.constraints,{
				optionalurl: true
			});
		}
	};
	this.renderUrlValidator = function(dom){
		if( this.options.urlvalidator ){
			return;
		}
		this.options.urlvalidator = new appdb.vappliance.ui.views.UrlValidator({
			container: dom,
			parent: this,
			url: this.options.dataValue || "",
			returnMime: ""
		});
		this.options.urlvalidator.subscribe({event: "validation", callback: function(v){
				if( v.isValid ){
					this.options.validValue = true;
				}else{
					$(this.editor.domNode).addClass("dijitValidationTextBoxError dijitError");
					this.options.validValue = false;
				}
				this.displayInvalidValue();
		}, caller: this});
	};
	this.onValueChange = function(v){
		v = v || this.editor.get("displayedValue");
		v = v.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		this.options.dataCurrentValue = v;
		this.onValidate();
		if( $.trim(v) === $.trim(this.options.urlvalidator.options.currentUrl) ){
			this.options.urlvalidator.displayMessage(true);
		}else{
			this.options.urlvalidator.displayMessage(false);
		}
		
		if( $.trim(v) === ""){
			this.options.urlvalidator.hide();
		}else{
			this.options.urlvalidator.show();
		}
	};
	this.displayInvalidValue = function(invalid){
		invalid = invalid || ( ( typeof this.options.validValue === "boolean")?!this.options.validValue:false );
		if( invalid ){
			$(this.editor.domNode).addClass("dijitValidationTextBoxError dijitError");
		}else{
			$(this.editor.domNode).removeClass("dijitValidationTextBoxError dijitError");
		}
	};
	this.renderEditor = function(dom){
		this.editor = new dijit.form.ValidationTextBox({
			value: this.getDisplayValue(),
			onFocus: (function(self){
				return function(){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.setFocus(false);
					self.onValueChange();
				};
			})(this),
			onChange : (function(self){
				return function(v){
					self.onValueChange(v);
				};
			})(this),
			onKeyUp: (function(self){
				return function(v){
					self.onValueChange();
				};
			})(this),
			onMouseUp: (function(self){
				return function(v){
					self.onValueChange();
				};
			})(this)
		}, dom);
		this.renderUrlValidator(this.dom);
	};
	this.postRenderEditor = function(){
		this.onValueChange();
	};	
});
appdb.vappliance.ui.views.DataValueHandlerIntegritycheck = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerIntegritycheck",function(o){
	this.onValueChange = function(v){
		var val = ""+v;
		val = val.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		this.setValue(v);
		if( val === "true" ){
			$(this.parent.dom).find(".manualintegrity").addClass("hidden");
			$(this.parent.parent.dom).addClass("autointegrity").removeClass("nointegrity");
		}else{
			$(this.parent.dom).find(".manualintegrity").removeClass("hidden");
			$(this.parent.parent.dom).removeClass("autointegrity").addClass("nointegrity");;
		}
		this.parent.parent.options.props["size"].options.handler.onValidate();
		this.parent.parent.options.props["checksum"].options.handler.onValidate();
	};
	this.save = function(){
		if( this.options.dataCurrentValue === false){
			this.options.dataCurrentValue = "false";
		}
	};
	this.renderEditor = function(dom){
		var descr = "<span class='icontext'>" + $(this.dom).attr("title") + "</span>";
		var isChecked = this.options.dataValue;
		if( typeof isChecked === "string" ){
			isChecked = isChecked.toLowerCase();
			isChecked = (isChecked === "true")?true:false;
		}else if( typeof isChecked !== "boolean" ) {
			isChecked = true;
		}
		
		this.editor = new dijit.form.CheckBox({
			label: descr,
			checked: isChecked,
			onChange: (function(self){
				return function(v){
					self.onValueChange(v);
				};
			})(this)
		},dom);
		$(this.editor.domNode).after(descr);
		this.onValueChange(isChecked);
	};
});
appdb.vappliance.ui.views.DataValueHandlerPresetdate = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler, "appdb.vappliance.ui.views.DataValueHandlerPresetdate", function(o){
    this.initConstraints = function(){
	    this.options.constraints = {
		    isRequired: true
	    };
    };
    this.onValueChange = function(v){
	    v = v || this.editor.get("value");
	    this.options.dataCurrentValue = v;
	    this.onValidate(v.id);
    };
    this.getValue = function(){
	    var val = this.options.dataCurrentValue;
	    if (val && val.id) {
		return val.id;
	    }
	    return val;
    };
    this.getInfiniteDate = function(){
	    return "2500-01-01";
    };
    this.renderValidationMessage = function(err){
	    if( !err.message ){
		    err.message = "Invalid Value";
	    }
	    $(this.dom).children(".validationmessage").addClass("tooltip");
	    $(this.dom).children(".validationmessage").append("<div class='arrow'></div>");
	    $(this.dom).children(".validationmessage").children(".validationerrormessage").append("<span>" + err.message + "</span>");
	    $(this.dom).children(".validationmessage").removeClass("hidden");
    };
    this.renderValidationError = function(err){
	    if( this.options.tooltip  ){
		    this.options.tooltip.destroyRecursive(false);
		    this.options.tooltip = null;
	    }
	    $(this.dom).children(".validationmessage").remove();
	    if( err ){
		    var vm = $("<a class='validationmessage' title='' href='#'><img src='/images/vappliance/warning.png' alt='' /><div class='validationerrormessage hidden'><span>" + err.message + "</span></div></a>");
		    $(this.dom).append(vm);

	    }
    };
    this.renderViewer = function(){
	    var displayValue = "" + this.options.dataCurrentValue;
	    var futureDate = new Date('2499-01-01');
	    var hasExpired = false;
	    if( $.isPlainObject(this.options.dataCurrentValue) && this.options.dataCurrentValue.val){
		    displayValue = this.options.dataCurrentValue.val();
	    }
	    displayValue = displayValue.split(".");
	    displayValue = displayValue[0];
	    var valueDate = new Date(displayValue);
	    if( $.trim(displayValue) === "" || $.trim(displayValue).indexOf(appdb.vappliance.utils.getInfiniteDate()) > -1 ){
		    displayValue = "<span class='infinite'>Infinite</span>";
	    } else if (valueDate >= futureDate) {
		    displayValue = "" + displayValue.split('T')[0] ;
	    } else {
		    var valDate = new Date(displayValue.split('T')[0]);
		    var nowDate = (new Date()).toISOString();
		    nowDate = new Date(nowDate.split('T')[0]);
		    if (valDate > nowDate) {
			   displayValue = "" + appdb.vappliance.utils.getLexicalDateDiff(valDate, nowDate) + " from now (" + displayValue.split('T')[0] + ")";
		    } else if (valDate === nowDate) {
			    displayValue = " today (" + displayValue.split('T')[0] + ")";
			    hasExpired = true;
		    } else {
			    displayValue = " has expired (" + displayValue.split('T')[0] + ")";
			    hasExpired = true;
		    }
	    }
	    $(this.dom).append(displayValue);
	    if (hasExpired) {
		    $(this.dom).addClass('has-expired');
	    } else {
		    $(this.dom).removeClass('has-expired');
	    }
    };
    this.renderEditor = function(dom){
	    var selid = ( ( $.isPlainObject(this.options.dataCurrentValue) && this.options.dataCurrentValue.id )?this.options.dataCurrentValue.id: this.options.dataCurrentValue) || null;

	    if (!this.options.dataSource || ($.isArray(this.options.dataSource) && this.options.dataSource.length === 0)) {
		this.options.dataSource = appdb.vappliance.utils.getExpirationPresets(selid)
	    }

	    if( $(this.editor).length > 0 ){
		    $(this.editor).remove();
	    }

	    var html = "<select class='dijitTextBox groupedselect'>";

	    html += "<option value='' " + ( ( selid===-1 )?"selected":"" ) + "></option>";

	    $.each(this.options.dataSource, function(i, e){
		var val = e.val();
		var displayVal = ((e.displayValue) ? e.displayValue() : val);
		var selected = ((e.selected) ? " selected": "");
		html += "<option value='" + val + "'" + selected + ">" + displayVal + "</option>";
	    });

	    html += "</select>";

	    this.editor = $(html);
	    this.editor.destroyRecursive = (function(self){
		    return function(){
			    $(this).empty();
			    $(this).remove();
		    };
	    })(this.editor);
	    $(dom).empty().append(this.editor);

	    $(this.editor).focusin( (function(self){
		    return function(){
			    self.parent.setFocus(true);
		    };
	    })(this) ).focusout(  (function(self){
		    return function(){
			    self.parent.setFocus(false);
		    };
	    })(this) ).off("change").on("change", (function(self){
		    return function(v){
			    var res = $(this).val();
			    if( self.options.dataSource ){
				    $.each(self.options.dataSource, function(i,e){
					    if( e.id === res ){
						    res = e;
					    }
				    });
			    }
			    self.options.dataCurrentValue = res;
			    self.onValidate();
			    self.parent.setFocus(true);
		    };
	    })(this) );
	    this.editor.get = (function(self){
		    return function(name){
			    name = $.trim(name).toLowerCase();
			    switch(name){
				    case "displayedvalue":
					    return $(self.editor).find(":selected").text();
				    case "value":
					    return $(self.editor).find(":selected").value();
				    default:
					    return undefined;
			    }
		    };
	    })(this);
	    if ($.isArray(this.options.dataSource) && this.options.dataSource.length === 0) {
		    this.setFocus(true);
		    this.options.dataCurrentValue = {id: '', val: function() { return ''; }, displayValue: function() {return ''; }};
		    this.setFocus(false);
	    }
	    setTimeout(this.onValidate.bind(this), 1);
    };
    this.postRenderEditor = function(){
	    this.onValidate();
    };
});
appdb.vappliance.ui.views.DataValueHandlerDatetime = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler,"appdb.vappliance.ui.views.DataValueHandlerDatetime", function(o){
	this.getInfiniteDate = function(){
		return "2500-01-01";
	};
	this.initConstraints = function(){
		if( this.canValidate() ){
			this.options.constraints.date = true;
			if( $.trim($(this.parent.dom).data("daterefersto")) === "future" ){
				this.options.constraints.futuredate = true;
			}else{
				this.options.constraints.futuredate = false;
			}
		}
	};
	this.renderValidationMessage = function(err){
		if( !err.message ){
			err.message = "Invalid Value";
		}
		$(this.dom).children(".validationmessage").addClass("tooltip");
		$(this.dom).children(".validationmessage").append("<div class='arrow'></div>");
		$(this.dom).children(".validationmessage").children(".validationerrormessage").append("<span>" + err.message + "</span>");
		$(this.dom).children(".validationmessage").removeClass("hidden");
	};
	this.renderValidationError = function(err){
		if( this.options.tooltip  ){
			this.options.tooltip.destroyRecursive(false);
			this.options.tooltip = null;
		}
		$(this.dom).children(".validationmessage").remove();
		if( err ){
			var vm = $("<a class='validationmessage' title='' href='#'><img src='/images/vappliance/warning.png' alt='' /><div class='validationerrormessage hidden'><span>" + err.message + "</span></div></a>");
			$(this.dom).append(vm);
			
		}
	};
	this.renderViewer = function(){
		var displayValue = "" + this.options.dataCurrentValue;
		if( $.isPlainObject(this.options.dataCurrentValue) && this.options.dataCurrentValue.val){
			displayValue = this.options.dataCurrentValue.val();
		}
		displayValue = displayValue.split(".");
		displayValue = displayValue[0];
		if( $.trim(displayValue) === "" || $.trim(displayValue).indexOf(this.getInfiniteDate()) > -1 ){
			displayValue = "<span class='infinite'>Infinite</span>";
		}
		$(this.dom).append(displayValue);
	};
	
	this.getIsoDateTime = function(v){
		if( !v ){
			return v;
		}
		var dt = "";
		if( typeof v === "string"){
			dt = v;
		}else{
			dt = v.toISOString();
		}
		dt = dt.split("T")[0];
		
		var now = new Date(new Date().getTime()).toISOString();
		now = now.split("T")[1];
		
		dt += "T" +now;
		return dt;
	};
	this.onValueChange = function(v){
		v = v || this.editor.get("displayedValue");
		v = this.getIsoDateTime(v);
		v = v.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
		this.options.dataCurrentValue = v;
		this.onValidate();		
	};
	this.getISODate = function(v){
		v = v || this.options.dataCurrentValue || "";
		if( $.trim(v) === "" || $.trim(v).indexOf(this.getInfiniteDate()) > -1){
			return "";
		}
		
		if( typeof v === "string" ){
			return new Date(v);
		}
		return v;
	};
	this.renderEditor = function(dom){
		if( this.editor ){
			this.editor.destroyRecursive(false);
			this.editor = null;
		}
		this.editor = new dijit.form.DateTextBox({
			value: this.getISODate(this.options.dataCurrentValue),
			required: false,
			placeHolder: "Infinite",
			constraints: { datePattern : 'yyyy-MM-dd' },
			promptMessage:"",
			invalidMessage:"",
			onFocus: (function(self){
				return function(){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.setFocus(false);
					self.onValueChange();
				};
			})(this),
			onChange: (function(self){
				return function(v){
					self.setFocus(true);
					self.onValueChange();
				};
			})(this),
			onKeyUp: (function(self){
				return function(){
					self.onValueChange();
				};
			})(this)
		}, dom);
		this.editor.validator = function(){ return true; };
	};
	this.postRenderEditor = function(){
		this.onValueChange();
	};
});
appdb.vappliance.ui.views.DataValueHandlerGrouplist = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandler, "appdb.vappliance.ui.views.DataValueHandlerGrouplist", function(o){
	this.renderValidationMessage = function(err){
		if( !err.message ){
			err.message = "Invalid Value";
		}
		$(this.dom).children(".validationmessage").addClass("tooltip");
		$(this.dom).children(".validationmessage").append("<div class='arrow'></div>");
		$(this.dom).children(".validationmessage").children(".validationerrormessage").append("<span>" + err.message + "</span>");
		$(this.dom).children(".validationmessage").removeClass("hidden");
	};
	this.renderValidationError = function(err){
		if( this.options.tooltip  ){
			this.options.tooltip.destroyRecursive(false);
			this.options.tooltip = null;
		}
		$(this.dom).children(".validationmessage").remove();
		if( err ){
			var vm = $("<a class='validationmessage' title='' href='#'><img src='/images/vappliance/warning.png' alt='' /><div class='validationerrormessage hidden'><span>" + err.message + "</span></div></a>");
			$(this.dom).append(vm);
			
		}
	};
	this.getGroupMember = function(){
		return $(this.parent.dom).data("group-member");
	};
	this.getGroupSource = function(){
		var src = $(this.parent.dom).data("group-source");
		if( $.trim(src) === "" ) return [];
		var obj = appdb.FindNS(src, false);
		obj = obj || [];
		obj = $.isArray(obj)?obj:[obj];
		obj = obj.sort(this.orderGroupSource);
		return obj;
	};
	this.orderGroupSource = function(a, b){
		var aa = (a.val)?a.val():"";
		var bb = (b.val)?b.val():"";
		if( aa > bb ) return 1;
		if( aa < bb ) return -1;
		return 0;
	};
	this.getGroupedData = function(){
		var gmember = this.getGroupMember();
		var gsource = this.getGroupSource();
		var source = this.options.dataSource || [];
		var res = [];
		
		$.each(gsource, function(i, e){
			e.children = [];
			$.each(source, function(ii, ee){
				if( ee.hasOwnProperty(gmember) && $.trim(ee[gmember]) === $.trim(e.id) ){
					e.children.push(ee);
				};
			});
			res.push(e);
		});
		return res;
	};
	this.renderEditor = function(dom){
		var selid = ( ( $.isPlainObject(this.options.dataCurrentValue) && this.options.dataCurrentValue.id )?this.options.dataCurrentValue.id:-1 );
		if( $.isArray(this.options.dataSource) ){
			if( $(this.editor).length > 0 ){
				$(this.editor).remove();
			}
			var selobj = null;
			var groups = this.getGroupedData();
			var html = "<select class='dijitTextBox groupedselect'>";
			html += "<option value='-1' " + ( ( selid===-1 )?"selected":"" ) + "></option>";
			$.each(groups, function(i, e){
				html += "<optgroup label='" + e.val() + "' data-id='" + e.id + "'>";
				$.each(e.children, function(ii, ee){
					if( selid !== -1 && $.trim(ee.id) === $.trim(selid) ){
						html += "<option value='" + ee.id + "' selected>" + ee.val() + "</option>";
					}else{
						html += "<option value='" + ee.id + "'>" + ee.val() + "</option>";	
					}
					
				});
				html += "</optgroup>";
			});
			html += "</select>";
			
			this.editor = $(html);
			this.editor.destroyRecursive = (function(self){
				return function(){
					$(this).empty();
					$(this).remove();
				};
			})(this.editor);
			$(dom).empty().append(this.editor);
			
			$(this.editor).focusin( (function(self){
				return function(){
					self.parent.setFocus(true);
				};
			})(this) ).focusout(  (function(self){
				return function(){
					self.parent.setFocus(false);
				};
			})(this) ).off("change").on("change", (function(self){
				return function(v){
					var res = $(this).val();
					if( self.options.dataSource ){
						$.each(self.options.dataSource, function(i,e){
							if( e.id === res ){
								res = e;
							}
						});
					}
					self.options.dataCurrentValue = res;
					self.onValidate();
					self.parent.setFocus(true);
				};
			})(this) );
			this.editor.get = (function(self){
				return function(name){
					name = $.trim(name).toLowerCase();
					switch(name){
						case "displayedvalue":
							return $(self.editor).find(":selected").text();
						default:
							return undefined;
					}
				};
			})(this);
			this.onValidate();
		}
	};
});
appdb.vappliance.ui.views.DataValueHandlerOstext = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerText, "appdb.vappliance.ui.views.DataValueHandlerOstext", function(o){
	this.postRenderViewer = function(){
		if( this.options.dataCurrentValue && typeof this.options.dataCurrentValue.val === "function" && $.trim(this.options.dataCurrentValue.val()).toLowerCase() === "other" ){
			var curId = $.trim(this.options.dataCurrentValue.id);
			var curOs = $.grep(appdb.model.StaticList.Oses, function(e){
				return ( $.trim(e.id) === curId );
			});
			curOs = ( curOs.length > 0 )?curOs[0]:null;
			if( curOs === null || !curOs.familyid ) return;
			var curFamId = $.trim(curOs.familyid);
			var curFam = $.grep(appdb.model.StaticList.OsFamilies, function(e){
				return ( curFamId === $.trim(e.id) );
			});
			curFam = ( curFam.length > 0 )?curFam[0]:null;
			if( curFam === null || !curFam.id || typeof curFam.val !== "function" ) return;
			$(this.dom).empty().html(curFam.val() + "/Other");
		}
		if( this.renderMaxSizeView ) {
			this.renderMaxSizeView();
		}
	};
});
appdb.vappliance.ui.views.DataValueHandlerVaversion = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerText, "appdb.vappliance.ui.views.DataValueHandlerVaversion", function(o){
	this.initConstraints = function(){
		this.options.constraints.vaversion = true;
	};
});
appdb.vappliance.ui.views.DataValueHandlerVmiversion = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerText, "appdb.vappliance.ui.views.DataValueHandlerVmiversion", function(o){
	this.getVAVersionVersion = function(){
		var vappversion = this.parent.getClosestParent('appdb.vappliance.ui.views.VApplianceVersion');
		if( vappversion && vappversion.options && vappversion.options.data ) {
			return $.trim(vappversion.options.data.version);
		}
		
		return null;
	};
	this.postRenderViewer = function(){
		
		$(this.dom).text( this.getValue() );
		var verid = this.getVAVersionVersion();
		if( verid && $.trim(verid) === $.trim(this.getValue()) ) {
			$(this.dom).closest('.property').addClass('hidden');
		} else if( this.renderMaxSizeView ) {
			this.renderMaxSizeView();
		}
		
	};
	this.renderEditor = function(){
		$(this.dom).closest('.property').addClass('hidden');
	};
});
appdb.vappliance.ui.views.DataValueHandlerVmidescription = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerLongtext, "appdb.vappliance.ui.views.DataValueHandlerVmidescription", function(o){
	this.getVAVersionDescription = function(){
		var vappversion = this.parent.getClosestParent('appdb.vappliance.ui.views.VApplianceVersion');
		if( vappversion && vappversion.options && vappversion.options.currentData ) {
			return $.trim(vappversion.options.currentData.notes);
		}
		
		return null;
	};
	this.postRenderViewer = function(){
		$(this.dom).text( this.getValue() );
		
		var verdesc = this.getVAVersionDescription();
		if( $.trim(verdesc) === '' || $.trim(this.getValue()) === '' || $.trim(verdesc) === $.trim(this.getValue()) ) {
			$(this.dom).closest('.property').addClass('hidden');
		} else if( this.renderMaxSizeView ) {
			this.renderMaxSizeView();
		}
		
	};
	this.renderEditor = function(){
		$(this.dom).closest('.property').addClass('hidden');
	};
});
appdb.vappliance.ui.views.DataValueHandlerAcceleratorstype = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerList, "appdb.vappliance.ui.views.DataValueHandlerAcceleratorstype", function(o) {
	this.renderEditor = function(dom){
		var selid = ( ( !$.isPlainObject(this.options.dataCurrentValue) && typeof this.options.dataCurrentValue !== 'undefined' )?this.options.dataCurrentValue:-1 );
		if( $.isArray(this.options.dataSource) ){
			var selobj = null;
			var selopts = [];
			$.each(this.options.dataSource, function(i,e){
				var val = e.val();
				val = val.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
				selopts.push({
					label: "<span>" + val + "</span>",
					value: e.id,
					selected: (('' + e.id) === ('' + selid))
				});
				if( ('' + e.id) === ('' + selid) ){
					selid = selopts[selopts.length - 1].value;
					selobj = e;
				}
			});
			this.options.dataCurrentValue = selobj;
			this.editor = new dijit.form.Select({
				options: selopts,
				onFocus: (function(self){
					return function(){
						self.parent.setFocus(true);
					};
				})(this),
				onBlur:  (function(self){
					return function(){
						self.parent.setFocus(false);
					};
				})(this),
				onChange: (function(self){
					return function(v){
						var res = v;
						if( self.options.dataSource ){
							$.each(self.options.dataSource, function(i,e){
								if( e.id === res ){
									res = e;
								}
							});
						}
						self.options.dataCurrentValue = res;
						self.onValueChange(self.options.dataCurrentValue);
						self.parent.setFocus(true);
					};
				})(this)
			}, dom);
			this.onValueChange();
		}
	};
	this.onValueChange = function(v){
		v = v || this.options.dataCurrentValue;
		if( $.isPlainObject(v) && 'id' in v) {
		    v = v.id;
		}
		if( $.trim(v) === "" ){
			v =  this.editor.get("value");
			v = v.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
			this.options.dataCurrentValue = v;
		}else{
			this.options.dataCurrentValue = v;
		}
		this.parent.options.currentValue = v;
		this.onValidate(v);
		var siblings = $(this.dom).closest('.fieldvalueset').find("[data-path^='accelerators.']").not("[data-path='accelerators.type']");
		if ($.trim(v) === '-1') {
		    $(siblings).addClass('disabled');
		    $(siblings).find('.value > .dijit').each(function(index, el) {
			dijit.byNode(el).set('disabled', true);
		    });
		} else {
		    $(siblings).removeClass('disabled');
		    $(siblings).find('.value > .dijit').each(function(index, el) {
			dijit.byNode(el).set('disabled', false);
		    });
		}

		if( $.trim(this.editor.get("value")) === "" ){
			$(this.dom).addClass("isempty");
		}else{
			$(this.dom).removeClass("isempty");
		}
	};
	this.postRenderEditor = function(){
		this.onValueChange(this.options.dataCurrentValue);
	};
});
appdb.vappliance.ui.views.DataValueHandlerTrafficrules = appdb.ExtendClass(appdb.vappliance.ui.views.DataValueHandlerText,"appdb.vappliance.ui.views.DataValueHandlerTrafficrules", function(o){
	this.getDataArray = function() {
		var vals = ((this.parent.options.data || {}).network_traffic || []);
		vals = [].concat($.isArray(vals)?vals:[vals]);
		vals = appdb.vappliance.utils.sortNetworkTrafficRules(vals);
		return vals;
	}
	this.filterByDirection = function(direction, data) {
		var vals = data || this.getDataArray();
		var result = [];
		$.each(vals, function(i,v) {
		    if (v.direction === direction) {
			 result.push(v);
		    }
		});
		return result;
	};
	this.getPortRange = function(d) {
		var ds = d.split(':');
		var res = {
			from: (ds.length > 0) ? ds[0] : ''
		};

		res.from = res.from || '';

		if (ds.length > 1) {
			res.to = ds[1] || '';
		} else {
			res.to = '' + res.from ;
		}

		return res;
	}
	this.renderTable = function(data, title) {
		var table = $('<table></table>');
		var thead = $('<thead><tr><th colspan="3" class="title">' + title + '</th></tr><tr><th rowspan="1" class="protocol">Protocol</th><th>From</th><th>To</th></tr></thead>');
		var tbody = $('<tbody></tbody>');

		$.each(data, function(i, v) {
			var tr = $('<tr></tr>');
			var ranges = v.port_range.split(';');
			var trClass = ( ( (i % 2) === 0 ) ? 'even' : 'odd' );
			$(tr).addClass(trClass).append( $('<td rowspan="'+ ranges.length +'"></td>').addClass('protocol').addClass('first').text(v.protocols) );
			$(tbody).append( tr );
			if (v.protocols === 'ICMP') {
				$(tr).append( $('<td colspan="2"></td>') );
			} else {
				$.each(ranges, function(i, rangeData) {
					var range = this.getPortRange(rangeData);
					if (i === 0) {
					    $(tr).append( $('<td></td>').addClass('first').text(range.from) );
					    $(tr).append( $('<td></td>').addClass('first').text(range.to) );
					} else {
					    var tr2 = $('<tr></tr>').addClass(trClass);
					    $(tr2).append( $('<td></td>').text(range.from) );
					    $(tr2).append( $('<td></td>').text(range.to) );
					    $(tbody).append(tr2);
					}
				}.bind(this));
			}
		}.bind(this));

		$(table).append( thead );
		$(table).append( tbody );

		var container = $('<div class="table-container"></div>');
		var containerHeader = $('<div class="table-container-header"></div>');
		var containerBody = $('<div class="table-container-body"></div>');
		var sudoTable = $('<table></table>');

		sudoTable.append($(table).children('thead').clone());
		$(container).append(containerHeader).append(containerBody);
		$(containerHeader).append(sudoTable);
		$(containerBody).append(table);

		return container;
	};
	this.getUIData = function() {
		var data = this.getDataArray();
		var meta = {
			direction: $.trim($(this.dom).parent().data('direction')) || '',
			data: []
		};
		meta.title = $.trim($(this.dom).parent().data('title')) || meta.direction;

		if (!meta.direction) {
			meta = [
				{direction: 'inbound', title: 'Incoming', data: this.filterByDirection('inbound', data)},
				{direction: 'outbound', title: 'Outgoing', data: this.filterByDirection('outbound', data)}
			];
		} else {
			meta.data = this.filterByDirection(meta.direction, data);
		}

		return $.isArray(meta) ? meta : [meta];
	};
	this.renderViewer = function(dom){
		var uiData = this.getUIData()
		var isEmpty = true;

		var v = $("<div class='trafficrules viewer'>");
		$.each(uiData, function(i, d) {
			if (d.data.length > 0 ) {
				isEmpty = false;
			}
			$(v).append( this.renderTable(d.data, d.title) );
		}.bind(this));

		if (!isEmpty) {
			$(this.dom).append(v);
		} else {
			$(this.dom).append($('<div class="emptymessage">No information provided yet</div>'));
		}
	};
});
appdb.vappliance.ui.views.DataProperty = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.DataProperty", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		dataValue: o.dataValue || $(o.container).data("value") || "" ,
		dataList: o.dataList || $(o.container).data("list") || false,
		dataType: $(o.container).data("type") || "text",
		dataPath: $(o.container).data("path") || "",
		dataEditable: (typeof o.editable==="boolean")?o.editable:($(o.container).data("editable") || false),
		dataSource: o.dataSource || $(o.container).data("source"),
		dataNormalize: o.dataNormalize || $(o.container).data("normalize"),
		dataModelProperty: o.dataModelProperty || $(o.container).data("modelproperty"),
		dataMandatory: (typeof o.mandatory === "boolean")?o.mandatory:($(o.container).data("mandatory") || $(o.container).hasClass("mandatory") ),
		dataValidation: o.dataValidation || $(o.container).data("validation") || false,
		dataModel: o.dataModel || $(o.container).data("model"),
		dataPlaceHolder: o.dataPlaceHolder || $(o.container).data("placeHolder") || "Provide a value...",
		dataName: o.dataName || $(o.container).data("name"),
		defaultValue: $(o.container).data("default"),
		emptyValue: "no information provided yet",
		dataBinder: $(o.container).data("binder") || null,
		dataBinds: $(o.container).data("bind-properties") || [],
		dataBindStore: {},
		currentValue: null,
		handler: null
	};
	this.getDataBindStore = function(){
		return this.options.dataBindStore;
	};
	this.setDataBindStore = function(dbstore){
		if( dbstore ){
			this.options.dataBindStore = dbstore;
		}else{
			this.options.dataBindStore = {};
		}
	};
	this.getDataPath = function(){
		return this.options.dataPath;
	};
	this.hasDataBinds = function(path){
		if( !this.options.dataBinder ) return false;
		if( path === this.getDataPath()) return true;
		if( this.options.dataBinds.length > 0 ){
			if( !path ) return true;
			path = $.trim(path);
			var dp = $.grep(this.options.dataBinds, function(e){
				return path === e;
			});
			return !(dp.length === 0);
		}
		return false;
	};
	this.dataBind = function(prop){
		this.options.dataBinder(this,prop);
	};
	this.reset = function(){
		if( this.options.dataEditable === true ){
			this.options.handler.reset();
		}
	};
	this.hasFocus = function(){
		return $(".vappliance.editmode").hasClass("focused");
	};
	this.setFocus = function(focus){
		focus = (typeof focus === "boolean")?focus:false;
		$(".vappliance.editmode .focused").removeClass("focused");
		this.parent.setFocus(focus);
	};
	this.isEditMode = function(){
		return $(this.dom).hasClass("editmode");
	};
	this.getDataValue = function(){
		return this.options.currentValue;
	};
	this.setDataValue = function(v){
		if( v ){
			this.options.currentValue = v;
		}
		return this;
	};
	this.edit = function(){
		$(this.dom).addClass("editmode");
		
		if( this.isList() ){
			$.each(this.options.handler, function(i,e){
				e.edit();
			});
		} else {
			this.options.handler.edit();
		}
		this.options.editMode = true;
	};
	this.save = function(){
		$(this.dom).removeClass("editmode");
		if( this.isList() ){
			this.options.currentValue = [];
			$.each(this.options.handler, (function(self){
				return function(i,e){
					e.save(); 
					self.options.currentValue.push(e.getValue());
				};
			})(this));
		} else {
			this.options.handler.save();
			this.options.currentValue = this.options.handler.getValue();
		}
	};
	this.cancel = function(){
		$(this.dom).removeClass("editmode");
		if( this.isList() ){
			$.each(this.options.handler, function(i,e){
				e.cancel();
			});
		} else {
			this.options.handler.cancel();
		}
		this.options.editMode = false;
	};
	this.renderPopups = function(){
		if( $(this.dom).hasClass("compact") ){
			$(this.dom).find(".descriptionmessage,.mandatorymessage ").each(function(i,e){
				var cont = $(e).find("span");
				if( $(cont).length > 0 ){
					cont = $(cont)[0];
				}else{
					return;
				}
				new dijit.Tooltip({
					connectId: $(e)[0],
					label: "<span>" + $(cont).text() + "</span>",
					position: "after"
				});
			});
		}
		var cid = $(this.dom).children(".header:first");
		var isempty = $(this.dom).children(".value:first").hasClass("empty");
		var val = $(this.dom).children(".value:first").html();
		if( $(cid).length === 0 ) return;
		var header = $(cid).html(); 
		if(header && header.indexOf("<")>-1 && $(header).hasClass("popup") ){
			val = $(header).find(".value > span:first").html();
			header = $(header).find(".field > .title:first").html();
		}
		$(this.dom).find(".popup").remove();
		if( $(this.dom).hasClass("popupvalue") ){
			if( isempty ){
				val = "<div class='emptymessage'>" + $(this.dom).children(".emptymessage:first").html() + "</div>";
				$(this.dom).children(".emptymessage:first").remove();
			}
			$(cid).html("<div class='popup'><div class='field'><span class='title'>" + header + "</span><span class='arrow'>▼</span></div><div class='value'><span>" + val + "</span></div>");
			$(cid).off("click").on("click", function(ev){
				ev.preventDefault();
				$("body .vappliance .property.popupvalue > .header.selected").removeClass("selected");
				$("body .vappliance .propertypopupvalue > .header.selected").removeClass("selected");
				$(this).addClass("selected");
				return false;
			});
		}
	};	
	this.renderEmptyValues = function(){
		$(this.dom).children("div.emptymessage").remove();
		$(this.dom).children(".value.empty").after("<div class='emptymessage'>" + this.options.emptyValue + "</div>");
	};	
	this.renderMandatory = function(display, text){
		display = (typeof display === "boolean")?display: false;
		text = $.trim(text);
		var classname = "mandatorymessage";
		var image = "/images/vappliance/redwarning.png";
		if( $(this.dom).children("."+classname).length > 0 && text === ""){
			text = $(this.dom).children("."+classname+" > span").html();
		}
		$(this.dom).children("."+classname).remove();
		if( display === true ){
			$(this.dom).append("<div class='icontext "+classname+"'><img src='"+image+"' alt='' /><span>"+text+"</span></div>");
		}
	};
	this.renderError = function(display, d){
		display = (typeof display === "boolean")?display:false;
		d = d || {};
		$(this.dom).find(".errormessage").remove();
		if( display === true ){
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
		}
	};
	this.renderHelp = function(){
		$(this.dom).find(".helppopup").remove();
		var helpmessages = $(this.dom).find(".helpmessage");
		$(helpmessages).addClass("hidden");
		$.each(helpmessages, function(i, e){
			var h = $("<a href='#' class='helppopup' title='' tab-index='-1'><img src='/images/question_mark.gif' alt=''/></a>");
			var help = $(e).html();
			var node = $(h)[0];
			$(e).after(h);
			var tt = new dijit.Tooltip({
				connectId: node,
				label: "<div class='vapphelppopupmessage'>" + help + "</div>",
				position: "above"
			});
		});
	};
	this.getCurrentValue = function(){
		
	};
	this.setCurrentValue = function(d){
		
	};
	this.renderValidation = function(d){
		
	};
	this.preRender = function(){
		return true;
	};
	this.isList = function(){
		return ( this.options.dataList !== false );
	};
	this.renderPropertyHandler = function(dom,data){
		dom = dom || $(this.dom).children(".value");
		var handler = null;
		var typename = $.trim(this.options.dataType).toLowerCase();
		var typeopts = $.extend({}, this.options);
		typeopts = $.extend(typeopts,{
			parent: this,
			container: dom,
			mandatory: $(this.dom).hasClass("mandatory"),
			dataValue: data || this.options.currentValue
		});
		
		if( typename.length > 0 ){
			typename = typename[0].toUpperCase() + typename.substr(1);
		}
		var typecls = appdb.FindNS("appdb.vappliance.ui.views.DataValueHandler" + typename);
		if( !typecls ){
			handler = new appdb.vappliance.ui.views.DataValueHandler(typeopts);	
		}else{
			handler = new typecls(typeopts);
		}
		handler.render();
		return handler;
	};
	this.renderList = function(d){
		var dom = $(this.dom).children(".value");
		$(dom).empty();
		var ul = $(document.createElement("ul"));
		this.options.handler = this.options.handler || [];
		this.options.handler = $.isArray(this.options.handler)?this.options.handler:[this.options.handler];
		$.each(this.options.handler, function(i,e){
			e.reset();
			e = null;
		});
		this.options.handler = [];
		$.each(this.options.currentValue, (function(self, list){
			return function(i, e){
				var li = $(document.createElement("li"));
				var handler = self.renderPropertyHandler(li, e);
				self.options.handler.push(handler);
				$(list).append(li);
			};
		})(this,ul));
		$(dom).append(ul);
	};
	this.onRender = function(d){
		d = d || this.options.currentData;
		this.renderEmptyValues(false);
		this.renderMandatory(false);
		this.renderError(false);
		if( this.isList() ){
			this.options.currentValue = $.isArray(this.options.currentValue)?this.options.currentValue:[this.options.currentValue];
			this.renderList();
		}else{
			if( $.isArray(this.options.currentValue) ){
				if( this.options.currentValue.length === 0 ){
					this.options.currentValue = {};
				}else{
					this.options.currentValue = this.options.currentValue[0];
				}
			}
			this.options.handler = this.renderPropertyHandler();
		}
		
		if( !this.options.currentValue || this.options.currentValue.length === 0){
			$(this.dom).children(".value").addClass("empty");
		}else{
			$(this.dom).children(".value").removeClass("empty");
		}
	};
	this.render = function(d){
		d = d || this.options.data;
		this.options.data = d;
		
		this.options.currentData = d;
		this._retrieveData(d);
		if( this.preRender(d) === false){
			return;
		}
		this.onRender(d);
		this.postRender(d);
	};
	this.postRender = function(d){
		this.renderEmptyValues(true,d);
		this.renderMandatory();
		this.renderPopups(true);
		this.renderHelp();
	};
	this._retrieveData = function(d){
		this.options.data = d || this.options.data;
		if( $.type(this.options.dataPath) === "function" ){
			this.options.dataValue = this.options.dataPath(this.options.data);
		}else if( $.type(this.options.dataPath) === "string" ) {
			this.options.dataValue = appdb.vappliance.FindData(this.options.data,this.options.dataPath);
			if( this.options.dataValue === null ){
				if( typeof this.options.defaultValue === "undefined" ){
					this.options.dataValue = "";
				}else{
					this.options.dataValue = this.options.defaultValue;
				}	
			}
		}else if( $.type(this.options.dataValue) === "undefined") {
			this.options.dataValue = "";
		}
		
		
		if( $.isArray(this.options.dataValue) ){
			this.options.currentValue = this.options.dataValue.slice(0);
		}else if( $.isPlainObject(this.options.dataValue) ){
			this.options.currentValue = $.extend({},this.options.dataValue);
		}else if( $.type(this.options.dataValue) === "number" ){
			this.options.currentValue = this.options.dataValue;
		}else{
			this.options.currentValue = ""+this.options.dataValue;
		}
	};
	this.canEdit = function(){
		return (this.options.dataEditable !== false);
	};
	this.hasChanges = function(){
		if( typeof(this.options.currentData) !== "undefined"  && this.options.currentData !== this.options.data){
			return true;
		}
		return false;
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		if( $(this.dom).children(".emptyvalue").length > 0 ){
			this.options.emptyValue = $(this.dom).children(".emptyvalue").html();
			$(this.dom).children(".emptyvalue").remove();
		}
		this.options.dataBinds = $.trim(this.options.dataBinds).replace(/\ /g,"");
		if( this.options.dataBinds !== "" ){
			this.options.dataBinds = this.options.dataBinds.split(",");
		}else{
			this.options.dataBinds = [];
		}
		
		if( $.trim(this.options.dataBinder) !== "" && this.options.dataBinder !== null ){
			this.options.dataBinder = appdb.FindNS("appdb.vappliance.databinders."+this.options.dataBinder, false);
			if( typeof this.options.dataBinder !== "function"){
				this.options.dataBinder = null;
			}
		}
	};
	this._init();
});

appdb.vappliance.ui.views.DataPropertyContainer = appdb.ExtendClass(appdb.View,"appdb.vappliance.ui.views.DataPropertyContainer", function(){
	this.options = {};
	this.reset = function(full){
		full = (typeof full === "boolean")?full:true;
		if( full === false ){
			return;
		}
		if( this.options.props ){
			$.each(this.options.props, function(i,e){
				e.reset();
			});
		}
		for(var v in this.subviews){
			if( !this.subviews.hasOwnProperty(v) ) continue;
			$(this.subviews[v].dom).empty();
			this.subviews[v].reset();
			this.subviews[v] = null;
		}
		this.subviews = [];
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.unregister(this);
	};
	this.canEdit = function(){
		return (typeof this.options.isEditable === "boolean")?this.options.isEditable:false;
	};
	this.isEditMode = function(){
		return this.options.editmode;
	};
	this.edit = function(){
		for(var i in this.options.props){
			if( !this.options.props.hasOwnProperty(i) ) continue;
			this.options.props[i].edit();
		}
		for(var v in this.subviews){
			if( !this.subviews.hasOwnProperty(v) ) continue;
			this.subviews[v].edit(true);
		}
		this.options.editmode = true;
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.register(this);
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
		this.postEdit();
		this.unsubscribeAll();
		setTimeout((function(self){
			return function(){
				self.initializeSubscriptions();
			};
		})(this),10);
	};
	this.postEdit = function(){
		
	};
	this.initializeSubscriptions = function(){
		props = this.options.props;
		for(var i in props){
			if( !props.hasOwnProperty(i) ) continue;
			props[i].unsubscribeAll(this);
			props[i].subscribe({event: "valuechanged", callback: function(v){
				if( typeof v.value === "undefined" && v.oldValue === "" ) return;
				this.dispatchDataBind(v);
				this.onPropertyValueChange(v);
			}, caller: this});
		}
	};
	this.dispatchDataBind = function(v){
		v = v || {};
		var property = v.property;
		if( !property || !property.getDataPath) return;
		var props = this.options.props;
		var dbpath = property.getDataPath();
		for(var i in props){
			if( !props.hasOwnProperty(i) || !props[i].hasDataBinds(dbpath)) continue;
			props[i].dataBind(property);
		}
	};
	this.isValid = function(){
		return true;
	};
	this.onPropertyValueChange = function(v) {
	    //to be overriden
	};
	this.onValidationError = function(){
		
	};
	this.cancel = function(){
		for(var i in this.options.props){
			if( !this.options.props.hasOwnProperty(i) ) continue;
			this.options.props[i].cancel();
		}
		for(var v in this.subviews){
			if( !this.subviews.hasOwnProperty(v) ) continue;
			this.subviews[v].cancel();
		}	
		$.each(this.subviews, function(i,e){
			e.cancel();
		});
		this.options.editmode = false;
		this.render(this.options.data);
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.unregister(this);
		this.postCancel();
	};
	this.postCancel = function(){
		
	};
	this.save = function(){
		for(var i in this.options.props){
			if( !this.options.props.hasOwnProperty(i) ) continue;
			this.options.props[i].save();
			if( this.options.props[i].options.dataPath.indexOf(".") > -1 ){
				appdb.vappliance.FindData(this.options.data, this.options.props[i].options.dataPath, this.options.props[i].getDataValue());
			}else{
				this.options.data[i] = this.options.props[i].getDataValue();
			}
		}
		for(var v in this.subviews){
			if( !this.subviews.hasOwnProperty(v) ) continue;
			this.subviews[v].save();
			this.options.data[v] = this.subviews[v].getData();
		}	
		if( $.isArray(this.options.data) ){
			this.options.data = [];
			$.each(this.subviews, (function(self){
			return function(i,e){
					e.save();
				};
			})(this));
			$.each(this.subviews, (function(self){
				return function(i,e){
					self.options.data.push(e.getData());
				};
			})(this));
		}
		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.unregister(this);
		this.postSave();
	};
	this.postSave = function(){
		
	};
	this.setFocus = function(focus){
		focus = (typeof focus === "boolean")?focus:false;
		if( focus === true ){
			$(this.dom).addClass("focused");
		}else{
			if($(this.dom).find(".focused, .dijitFocused").length === 0 ){
				$(this.dom).removeClass("focused");
			}
		}
		if( this.parent && this.parent.setFocus){
			this.parent.setFocus(focus);
		}
	};
	this.setupFocus = function(){
		$(this.dom).off("keyup").on("keyup", (function(self){
			return function(){
				self.setFocus(true);
			};
		})(this)).off("mousemove").on("mousemove", (function(self){
			return function(){
				self.setFocus(true);
			};
		})(this)).off("mouseleave").on("mouseleave", (function(self){
			return function(){
				self.setFocus(false);
			};
		})(this));
	};
	this.getPropertyData = function(){
		var props = this.options.props || {};
		var res = {};
		for( var p in props){
			if( !props.hasOwnProperty(p) ) continue;
			if( props[p] && props[p].getDataValue ){
				var pp = p.replace(/\./g,"");
				res[pp] = props[p].getDataValue();
			}
		}
		return res;
	};
	this.hasPropertyChanges = function(){
		var res = false;
		var props = this.options.props || {};
		for(var p in props ){
			if( props.hasOwnProperty(p) && res === false){
				res = props[p].hasChanges();
			}
			if( res === true ) break;
		}
		return res;
	};
	this.getData = function(){
		return this.getPropertyData();
	};
	this.hasChanges = function(){
		return this.hasPropertyChanges();
	};
	this.postRenderProperties = function(){
		
	};
	this.renderProperties = function(d){
		if( !this.options ){
			this.options = {};
		}
		if( !this.options.props ){
			this.options.props = {};
		
			$(this.dom).find(".property:not(.noempty)").each((function(self){
				return function(index,elem){
					if( $(elem).data("path") ){
						var k = $(elem).data("name") || $(elem).data("path");
						self.options.props[k] = new appdb.vappliance.ui.views.DataProperty({
							container: $(elem)[0], 
							parent:self, 
							data: d});
					}
				};
			})(this));
		}
		for(var s in this.options.props){
			this.options.props[s].render(d);
		}
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
		this.setupFocus();
		this.postRenderProperties();
		this.getPropertyByDataPath = function(dpath){
			var res = [];
			for(var i in this.options.props){
				if( !this.options.props.hasOwnProperty(i)) continue;
				var p = this.options.props[i];
				if( p.options && p.options.dataPath && p.options.dataPath == dpath){
					res.push(p);
				}
			}
			return (res.length > 0 )?res[0]:undefined;
		};
	};
});
appdb.vappliance.ui.views.VApplianceVersion = appdb.ExtendClass(appdb.vappliance.ui.views.DataPropertyContainer,"appdb.vappliance.ui.views.VApplianceVersion", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data ,
		currentData: $.extend({},(o.data || {})),
		isEditable: (typeof o.editable === "boolean")?o.editable:false
	};
	this.getData = function(){
		var d = this.options.data || {};
		var list = this.subviews.vmiList;
		var props = this.getPropertyData();
		var res = {
			id: d.id,
			version: props.version || d.version,
			identifier: d.identifier,
			notes: props.notes || d.notes,
			enabled: props.enabled || d.enabled,
			archived: props.archived || d.archived,
			status: props.status || d.status,
			published: props.published || d.published,
			expireson: props.expireson || d.expireson,
			image: ( (!list)?[]:list.getData() )
		};
		if( $.trim(res.expireson) === "" ){
			res.expireson = appdb.vappliance.utils.getDateAfterMonths(12)//"2500-01-01";
		} else {
		    var in12motnhs = new Date(appdb.vappliance.utils.getDateAfterMonths(12));
			var expiresOnDate = new Date(res.expireson);
			if (expiresOnDate > in12motnhs) {
				res.expireson = appdb.vappliance.utils.getDateAfterMonths(12);
			}
		}
		return res;
	};
	this.renderVMIList = function(d){
		d.image = d.image || [];
		d.image = $.isArray(d.image)?d.image:[d.image];
		if( this.subviews.vmiList ){
			this.subviews.vmiList._mediator.clearAll();
			this.subviews.vmiList.reset();
			this.subviews.vmiList = null;
		}
		this.subviews.vmiList = new appdb.vappliance.ui.views.VApplianceVMIList({
			container: $(this.dom).find(".vaversion-vmis:first"),
			parent: this,
			editable: this.canEdit(),
			data: d.image
		});
		this.subviews.vmiList.render(d.image);
		if( d.image.length > 1 ){
			$(this.dom).find(".vaversion-vmilist").removeClass("singlegroup");
		}else{
			$(this.dom).find(".vaversion-vmilist").addClass("singlegroup");
		}
	};
	this.getVmiList = function(){
		return this.subviews.vmiList;
	};
	this.render = function(d){
		this.options.data = d || this.options.data;
		this.options.currentData = $.extend({},this.options.data);
		$(this.dom).find(".valueeditor").remove();
		this.renderProperties(this.options.currentData);
		this.renderVMIList(this.options.currentData);
	};
	this.postEdit = function(){
		var lpv = appdb.vappliance.ui.CurrentVAManager.getLatestPublishedVersion();
		$(this.dom).find(".previousversionproperty").addClass("hidden");
		if( lpv === null ) {
			return;
		}
		$(this.dom).find(".previousversionproperty").each((function(latest){
			return function(i,e){
				var name = $.trim($(this).data("name")).toLowerCase();
				var val = latest[name];
				var hasExpired = false;
				if( val ){
					if( $.inArray(name, ["expireson","createdon"]) > -1 ){
						val = val.split("T")[0];
					}
					if (name === "expireson") {
					    if( val === "2500-01-01" ){
						    val = " will never expire (Infinite)";
					    } else {
						var valDate = new Date(val);
						var nowDate = (new Date()).toISOString();
						nowDate = new Date(nowDate.split('T')[0]);
						if (valDate > nowDate) {
						    if (valDate > (new Date('2100-01-01'))) {
							    val = " will expire on " + val;
						    } else {
							    val = " will expire in " + appdb.vappliance.utils.getLexicalDateDiff(valDate, nowDate) + " (" + val + ")";
						    }
						} else if (valDate === nowDate) {
						    val = " expires today (" + val + ")";
						    hasExpired = true;
						} else {
						    val = " has expired (" + val + ")";
						    hasExpired = true;
						}
					    }

					    if (hasExpired) {
						$(this).find(".value").closest('[data-name="expireson"]').addClass('has-expired');
					    } else {
						$(this).find(".value").closest('[data-name="expireson"]').removeClass('has-expired');
					    }
					}
					$(this).find(".value").text(val);
					$(this).removeClass("hidden");
				}
			};
		})(lpv));
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
},{
	emptymessage: "No information provided yet"
});


appdb.vappliance.ui.views.VApplianceVMIVersionItem = appdb.ExtendClass(appdb.vappliance.ui.views.DataPropertyContainer, "appdb.vappliance.ui.views.VApplianceVMIVersionItem", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		index: o.index || -1,
		validRanges: {
		    'ram': true,
		    'cores': true,
		    'accelerators': true
		},
		networkTraffic: null,
		isEditable: ( (typeof o.editable === "boolean")?o.editable:false )
	};
	this.getData = function(){
		var d = this.options.data;
		var props = this.getPropertyData();
		var integrity = "";
		if(typeof props.integrity === "boolean"){
			integrity = ""+props.integrity;
		}else if(typeof props.integrity === "string" && (props.integrity === "false" || props.integrity === "true")){
			integrity = props.integrity;
		}else if(typeof d.integrity === "boolean"){
			integrity = ""+d.integrity;
		}else if(typeof d.integrity === "string" && (d.integrity === "false" || d.integrity === "true")){
			integrity = d.integrity;
		}else{
			integrity = ""+true;
		}
		var chk = props.checksum || d.checksum || "";
		if(typeof chk === "string" ){
			chk = { hash:"sha512", val: (function(cc){ return function() {return cc; }; })(chk)};
		}
		var rammin = (props.ramminimum && props.ramminimum.id) ? props.ramminimum.id : (props.ramminimum || "0");
		var ramrecommended = (props.ramrecommended && props.ramrecommended.id) ? props.ramrecommended.id : (props.ramrecommended || "0");
		var coresmin = (props.coresminimum && props.coresminimum.id) ? props.coresminimum.id : (props.coresminimum || "0");
		var coresrecommended = (props.coresrecommended && props.coresrecommended.id) ? props.coresrecommended.id : (props.coresrecommended || "0");
		var ovfurl = $.trim(props.ovfurl) || "";
		var accelerators = {type: props.acceleratorstype || 'None', minimum: (props.accminimum || "-1"), recommended: (props.accrecommended || "-1")};
		if (!accelerators || accelerators.type === 'None' || $.trim(accelerators.type) === '-1') {
		    accelerators = null;
		}
		var network_traffic = [].concat(this.options.networkTraffic.harvestData());
		if (network_traffic.length === 0) {
		    network_traffic = null;
		}

		var res = {
			id: props.id || d.id,
			version: props.version || d.version,
			arch: props.arch || d.arch,
			os: props.os || d.os,
			url: $.trim(props.url || d.url),
			format: props.format || d.format,
			osversion: props.osversion || d.osversion,
			hypervisor: props.hypervisor || d.hypervisor,
			title: props.title || d.title,
			description: props.description || d.description || "",
			notes: props.notes || d.notes || "",
			checksum: chk,
			identifier: d.identifier || "",
			size: d.size || 0,
			addedon: d.addedon || "",
			addedby: d.addedby,
			integrity: integrity,
			network_traffic: network_traffic,
			accelerators: accelerators,
			ram: {minimum: rammin , recommended: ramrecommended},
			cores: {minimum: coresmin, recommended: coresrecommended },
			ovf: { url: ovfurl }
		};
		if (res.accelerators === null) {
		    res.accelerators = null;
		}
		if( appdb.config.features.singleVMIPolicy === true ) {
			if( $.trim(d.prevUrl) !== "" ) {
				res.prevUrl = $.trim(d.prevUrl);
			}
		}
		if( !!this.options.contextitem && typeof this.options.contextitem.getData === "function" ){
			var cs = this.options.contextitem.getData();
			if( cs && $.isEmptyObject(cs) === false ){
				res.contextscript = cs;
				//Remove cscript entry if this is a new unsaved va version update,
				//the current vmi instance did not had any cscript associated and
				//the cscript url is empty. (empty cscript urls are for deletion 
				//of existing cscript entries asssociated with existing vmiinstances)
				if( !d.contextscript && !d.id && $.trim(res.contextscript.url)==='' ) {
					delete res.contextscript;
				}
			}
		}
		return res;
	};
	this.isPrivate = function(){
		return ($.trim(this.options.data.isprivate) === "true");
	};
	this.isProtected = function(){
		return ($.trim(this.options.data["protected"]) === "true");
	};
	this.initActions = function(){
		$(this.dom).children(".actions").children(".action.remove").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.parent.removeItem(self);
				return false;
			};
		})(this));
		this.renderDownloadButton();
	};
	this.isValid = function() {
	    var ranges = this.options.validRanges || {};
	    var validRanges =  ranges.ram && ranges.cores && ranges.accelerators;
	    if (validRanges === true) {
		return (this.options.networkTraffic.isValid() === true);
	    }
	    return validRanges;
	};
	this.validateRanges = function(name) {
	    var isValid = true;
	    var data = this.getData() || {};
	    var d = Object.assign({minimum: 0, recommended: 0}, data[name] || {});
	    if (name === 'ram') {
		d.minimum = appdb.vappliance.utils.normalization.ram(d.minimum);
		d.recommended = appdb.vappliance.utils.normalization.ram(d.recommended);
	    }

	    d.minimum = parseInt(d.minimum || 0);
	    d.recommended = parseInt(d.recommended || 0);

	    isValid = (d.minimum <=0 || d.recommended <= 0 || d.minimum <= d.recommended);
	    if (name === 'accelerators'){
		if(!d.type) {
		    isValid = true;
		} else {
		    isValid = (d.minimum <= d.recommended);
		}
	    }

	    $(this.dom).find('.fieldvalueset.' + name + '-valueset').toggleClass('invalid', !isValid);

	    return isValid;
	};
	this.onPropertyValueChange = function(v) {
	    var prop = v.property || {options: {}};
	    var propName = (prop.options || {}).dataPath || null;
	    switch(propName) {
		case 'ram.minimum':
		case 'ram.recommended':
		case 'cores.minimum':
		case 'cores.recommended':
		case 'accelerators.type':
		case 'accelerators.minimum':
		case 'accelerators.recommended':
		    this.options.validRanges.ram = this.validateRanges('ram');
		    this.options.validRanges.cores = this.validateRanges('cores');
		    this.options.validRanges.accelerators = this.validateRanges('accelerators');
		    appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
		    break;
		case 'trafficRules':
		    appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
		    break;
		default:
		    break;
	    }
	};
	this.renderDownloadButton = function(){
		$(this.dom).find(".downloadbutton > .format").addClass("hidden");
		$(this.dom).find(".downloadbutton").parent().removeClass("isprivate");
		if( this.isPrivate() && this.isProtected() ){
			$(this.dom).find(".downloadbutton").parent().addClass("isprivate");
			$(this.dom).find(".downloadbutton").addClass("isprivate").attr("href","").attr("title","This image is private").off("click").on("click", function(ev){
				ev.preventDefault();
				return false;
			});
			$(this.dom).find(".downloadbutton > div.icontext > img").attr("src","/images/logout3.png");
			$(this.dom).find(".downloadbutton > div.icontext > img + span").text("Private");
		} else if( $.trim(this.options.data.url) !== "" ){
			$(this.dom).find(".downloadbutton").attr("href",this.options.data.url);
		}else{
			$(this.dom).find(".downloadbutton").attr("href","");
		}
		if( $.trim(this.options.data.format) !== "" ){
			$(this.dom).find(".downloadbutton .format > .value").removeClass("hidden").text(this.options.data.format);
		}
	};
	this.renderDetails = function(){
		var viewcoresminimum = $(this.options.container).find("div[data-name='viewcoresminimum'] > .value");
		var viewcoresrecommended = $(this.options.container).find("div[data-name='viewcoresrecommended'] > .value");
		var viewramminimum = $(this.options.container).find("div[data-name='viewramminimum'] > .value");
		var viewramrecommended = $(this.options.container).find("div[data-name='viewramrecommended'] > .value");
		var viewacctype = $(this.options.container).find("div[data-name='viewacctype'] > .value");
		var viewaccmin = $(this.options.container).find("div[data-name='viewaccminimum'] > .value");
		var viewaccrec = $(this.options.container).find("div[data-name='viewaccrecommended'] > .value");
		if( $.trim($(viewramminimum).html()) === "0" || $.trim($(viewramminimum).html()) === ""){
			$(viewramminimum).html("<span class='notavailable'>n/a</span>");
			$(viewramminimum).siblings(".unit").remove();
		}
		if( $.trim($(viewramrecommended).html()) === "0" || $.trim($(viewramrecommended).html()) === ""){
			$(viewramrecommended).html("<span class='notavailable'>n/a</span>");
			$(viewramrecommended).siblings(".unit").remove();
		}
		if( $.trim($(viewcoresminimum).html()) === "0" || $.trim($(viewcoresminimum).html()) === ""){
			$(viewcoresminimum).html("<span class='notavailable'>n/a</span>");
			$(viewcoresminimum).siblings(".unit").remove();
		}
		if( $.trim($(viewcoresrecommended).html()) === "0" || $.trim($(viewcoresrecommended).html()) === ""){
			$(viewcoresrecommended).html("<span class='notavailable'>n/a</span>");
			$(viewcoresrecommended).siblings(".unit").remove();
		}
		if( $.trim($(viewacctype).html()) === "None" || $.trim($(viewacctype).html()) === ""){
			$(viewacctype).closest('.accelerators.group').find('[data-path="accelerators.type"] .header').remove();
			$(viewacctype).closest('.accelerators.group').find('[data-path="accelerators.minimum"]').remove();
			$(viewacctype).closest('.accelerators.group').find('[data-path="accelerators.recommended"]').remove();
		} else {
			if( $.trim($(viewaccmin).html()) === "-1" || $.trim($(viewaccmin).html()) === ""){
				$(viewaccmin).html("<span class='notavailable'>n/a</span>");
				$(viewaccmin).siblings(".unit").remove();
			}
			if( $.trim($(viewaccrec).html()) === "-1" || $.trim($(viewaccrec).html()) === ""){
				$(viewaccrec).html("<span class='notavailable'>n/a</span>");
				$(viewaccrec).siblings(".unit").remove();
			}
		}
		$(this.dom).removeClass("isprotected isprivate");
		if( this.isPrivate() ){
			$(this.dom).addClass("isprivate");
			if( this.isProtected() ){
				$(this.dom).addClass("isprotected");
			}
		}
	};
	this.renderIntegrityStatus = function(){
		if( !this.options.data || !this.options.data.integritycheck)return;
		if( !this.options.data.integrity || this.options.data.integrity === "false" ){
			$(this.dom).removeClass("autointegrity");
		}else{
			$(this.dom).addClass("autointegrity");
		}
		var dom = $(this.dom).find(".integritycheck");
		var status = this.options.data.integritycheck.status;
		var message = (this.options.data.integritycheck.val)?this.options.data.integritycheck.val():"Unknown error";
		$(this.dom).removeClass("integritysuccess").removeClass("integrityerror").removeClass("integrityunchecked").removeClass("integritywarning");
		switch(status){
			case "error":
				$(this.dom).addClass("integrityerror");
				$(dom).children(".message").find(".error > .details .errormessage").empty().append(message);
				break;
			case "success":
				$(this.dom).addClass("integritysuccess");
				break;
			case "unchecked":
				$(this.dom).addClass("integrityunchecked");
				break;
			case "warning":
				$(this.dom).addClass("integritywarning");
				$(dom).children(".message").find(".warning > .details .errormessage").empty().append(message);
			default:
				if( this.options.data.autointegrity === "true" ){
					$(this.dom).addClass("integrityunchecked");
				} 
				break;
		}
	};
	this.renderExports = function(){
		var dom = $(this.dom).find(".exports");
		
		var ubase = appdb.config.endpoint.base;
		ubase = ubase.replace(/^http\:\/\//,"https://");
		ubase += "store/vm/image/" + this.options.data.identifier + ":" + this.options.data.id;
		$(dom).find(".xml").attr("href",ubase +"/xml?strict");
		$(dom).find(".json").attr("href",ubase +"/json?strict");
	};
	this.renderMore = function(){
		if( $(this.dom).children(".showmore").length > 0 ){
			$(this.dom).children(".showmore").children(".more").off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					$(self.dom).addClass("detailsview");
					return false;
				};
			})(this));
			$(this.dom).children(".showmore").children(".less").off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					$(self.dom).removeClass("detailsview");
					return false;
				};
			})(this));
		}
	};
	this.postRenderProperties = function(){
		this.renderDownloadButton();
		this.renderDetails();
		this.renderIntegrityStatus();
		this.renderExports();
		this.renderMore();
	};
	this.renderAccessDenied = function(el){
		var html = $("<span class='icontext alert-protected'><img src='/images/logout3.png' alt=''/><span>No permissions to view this information.</span></span>");
		$(el).each(function(i, e){
			$(e).empty();
			$(e).append($(html).clone());
		});
	};
	this.renderPrivacy = function(){
		$(this.dom).children(".ribbon.privacy").remove();
		
		if( this.isPrivate() ){
			var privribbon = $("<div class='privacy ribbon' title='This image is private'><img src='/images/logout3.png' alt=''/></div>");
			var privribbondescr = $("<div class='ribbondescription'></div>");
			var description = "This image is marked as private.";
			if( this.isProtected() ){
				description += " You need special permissions to view some of the information.";
			}
			$(privribbondescr).html("<span>"+description+"</span>");
			$(this.dom).append($(privribbon).clone()).append($(privribbondescr).clone());
		}
		if( this.isProtected() ){
			this.renderAccessDenied($(this.dom).find(".property[data-path='url'] > .value, .property[data-path='url'] > .header > .popup > .value"));
			this.renderAccessDenied($(this.dom).find(".property[data-path='checksum'] > .value, .property[data-path='checksum'] > .header > .popup > .value"));
			this.renderAccessDenied($(this.dom).find(".property[data-path='ovf.url'] > .value, .property[data-path='ovf.url'] > .header > .popup > .value"));
			$(this.dom).find(".property[data-path='size'] > *").empty();
		}
	};
	this.renderAdvancedPanel = function(){
		var panel = $(this.dom).find(".advancedpanel");
		var toggler = $(panel).find(".toggler");
		$(toggler).off("click").on("click", function(ev){
			ev.preventDefault();
			//$(this).closest(".advancedpanel").toggleClass("expand");
			return false;
		});
	};
	this.filterNetworkTraffic = function(direction, data) {
		data = data || this.options.data || {};
		var rules = data.network_traffic || [];
		rules = Array.isArray(rules) ? rules : [rules];
		var direction = ('' + direction).toLowerCase();

		if ($.trim(direction) !== '') {
			return rules.filter(function(item) {
			        return (item.direction === direction || direction === '*');
			});
		}

		return rules;
	};
	this.renderNetworkTraffic = function() {
		if (this.options.networkTraffic) {
			this.options.networkTraffic.reset();
			this.options.networkTraffic = null;
		}
		this.options.networkTraffic = new appdb.vappliance.ui.views.VMITrafficRulesEditor({
			container: $(this.dom).find('.trafficrules-component').first(),
			parent: this,
			editable: this.canEdit(),
			direction: 'inbound',
			data: this.filterNetworkTraffic('*', this.options.data),
			itemTemplate: $(this.dom).find('.trafficrule-item').clone(true)
		});
		this.options.networkTraffic.render();

		appdb.vappliance.ui.CurrentVAVersionValidatorRegister.register({isValid: function() {return this.isValid(); }.bind(this.options.networkTraffic), reset: function() { }.bind(this.options.networkTraffic)});
	};
	this.render = function(d){
		$(this.dom).attr("data-id",d.id);
		this.renderProperties(d);
		this.renderContextualization();
		this.initActions();
		this.renderPrivacy();
		this.renderAdvancedPanel();
		this.renderNetworkTraffic();
		setTimeout((function(self){
			return function(){
				self.initializeSubscriptions();
			};
		})(this),10);
	};
	this.initContainer = function(){
		
	};
	this.renderContextualization = function(){
		var script, cntxs = this.options.data.contextscript || [];
		cntxs = $.isArray(cntxs)?cntxs:[cntxs];
		if( cntxs.length > 0 ){
			script = cntxs[0];
		}
		if( this.options.contextitem ){
			this.options.contextitem.reset();
			this.options.contextitem = null;
		}
		this.options.contextitem = new appdb.vappliance.ui.views.ContextualizationScript({
			container: $(this.dom).find(".vmiversion-cntxscripts > .value"),
			parent: this,
			data: script,
			vmiinstance: this.options.data,
			index: this.options.index
		});
		this.options.contextitem.render();
	};	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
});

appdb.vappliance.ui.views.VApplianceVMIVersionList = appdb.ExtendClass(appdb.vappliance.ui.views.DataPropertyContainer, "appdb.vappliance.ui.views.VApplianceVMIVersionList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		isEditable: ( (typeof o.editable === "boolean")?o.editable:false ),
		itemTemplate:  $(document.createElement("div")).addClass("vmiversionitem"),
		useSingleItem: ( (appdb.config.features.singleVMIPolicy===true)?true:false ),
		items: [],
		importer: null,
		dom:{
			list: $(document.createElement("ul"))
		}
	};
	this.getSubContainers = function(){
		return this.subviews;
	};
	this.getData = function(){
		var res = [];
		if( $.isArray(this.subviews) ){
			$.each(this.subviews, function(i,e){
				res.push(e.getData());
			});
		}
		return res;
	};
	this.addItem = function(d){
		var cont = $(this.options.itemTemplate).clone(true);
		var item = new appdb.vappliance.ui.views.VApplianceVMIVersionItem({
			container: cont,
			parent: this,
			editable: this.canEdit(),
			data: d,
			index: this.subviews.length
		});
		this.subviews.push(item);
		item.render(d);
		if( this.isEditMode() ){
			item.edit();
			if( d.isNew === true ){
				item.setFocus(true);
			}
			appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
		}
		return cont;
	};
	this._deleteItem = function(item){
		var index = -1;
		var registry = appdb.vappliance.ui.CurrentVAManager.getContextualizationRegistry();
		$.each(this.subviews, function(i, e){
			if( e === item ){
				index = i;
			}
		});
		if( index > -1 ){
			if( registry && this.subviews[index].options.data && $.trim(this.subviews[index].options.data.id)!==""){
				registry.clearByRef(this.subviews[index].options.data.id);
			}
			this.subviews[index].reset();
			this.subviews.splice(index,1);
			var p = $(item.dom).parent();
			$(p).remove();
			this.checkEmpty();
			appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
		}
	};
	this.isValid = function(){
		return ( $(this.options.dom.list).children("li").length > 0 );
	};
	this.removeItem = function(item){
		if( item.options.data.isNew === true ){
			this._deleteItem(item);
		}else{
			appdb.vappliance.ui.ShowVerifyDialog({
				title: "Virtual Machine Image removal",
				message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to remove this virtual machine image. Are you sure you want to procced?</span>",
				onOk: (function(self,data){
					return function(){
						self._deleteItem(data);
					};
				})(this,item)
			});
		}
	};
	this.replaceCurrentItem = function(item){
		item = item || {};
		var newitem = $.extend({},item);
		var currentItem = (this.options.data.length>0)?this.options.data[0]:null;
		var currentItemData = currentItem || {};
		
		newitem.index = 0;
		newitem.id = currentItemData.id || "";
		newitem.identifier = currentItemData.identifier || "";
		newitem.integrity = "true";
		delete newitem.publishedon;
		delete newitem.addedon;
		delete newitem.addedby;
		delete newitem.integritycheck;
		delete newitem.isprivate;
		delete newitem['protected'];
		delete newitem.enabled;
		
		this.removeAllItems();
		
		var li = $(document.createElement("li"));
		var div = this.addItem(newitem);
		$(li).append(div);
		$(this.options.dom.list).prepend(li);
		this.checkEmpty();
	};
	this.addNewItem = function(item){
		if( this.options.useSingleItem === true ) {
			return this.replaceCurrentItem(item);
		}
		item = item || {};
		var newitem = $.extend({},item);
		newitem.isNew = true;
		newitem.index = this.options.items.length+1;
		newitem.integrity = "true";
		delete newitem.publishedon;
		delete newitem.addedon;
		delete newitem.addedby;
		delete newitem.integritycheck;
		delete newitem.isprivate;
		delete newitem['protected'];
		delete newitem.enabled;
		
		var li = $(document.createElement("li"));
		var div = this.addItem(newitem);
		$(li).append(div);
		$(this.options.dom.list).prepend(li);
		this.checkEmpty();
	};
	this.postEdit = function(){
		setTimeout((function(self){
			return function(){
				$(self.dom).parent().children(".actions").children(".action.new").stop().clearQueue().removeAttr("style").css({"background-color":"#FF8A4C","outline-width":"10px","outline-color":"#FF8A4C","outline-style":"solid","opacity":"0.5"}).animate({"outline-width":"0px","opacity":"1"}, 1000, "linear", function(){
					$(this).animate({"background-color":"#ffffff"},3000,"linear",function(){
						$(this).removeAttr("style");
					});
				}).on("mousemove.animate",function(ev){
					$(this).stop().clearQueue();
					$(this).removeAttr("style");
					$(this).off("mousemove.animate");
				});
			};
		})(this),10);
	};
	this.initActions = function(){
		$(this.dom).parent().children(".actions").children(".action.new").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.addNewItem();
				return false;
			};
		})(this));
		$(this.dom).parent().children(".actions").children(".action.clearall").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("disabled") === false ){
					self.clearAll();
				}
				return false;
			};
		})(this));
		$(this.dom).parent().children(".actions").children(".action.import").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("disabled") === false ){
					self.importImages();
				}
				return false;
			};
		})(this));
		$(this.dom).parent().find(".action").attr("tabIndex",-1);
		this.renderHelp($(this.dom).parent().children(".actions"));
	};
	this.removeAllItems = function(){
		while(this.subviews.length > 0 ){
			this._deleteItem(this.subviews[0]);
		}
	};
	this.importImages = function(){
		if( this.options.importer !== null ){
			this.options.importer.unsubscribeAll();
			this.options.importer.destroy();
			this.options.importer = null;
		}
		this.options.importer = new appdb.vappliance.components.UnusedImages({
			parent: this,
			data: this.options.data,
			strictmode: ( (appdb.config.features.singleVMIPolicy===true)?false:true ),
			multiselect: ( (appdb.config.features.singleVMIPolicy===true)?false:true )
		});
		this.options.importer.load();
	};
	this.clearAll = function(){
		var hasOld = $.grep(this.subviews, function(e){
			if( e ){
				return (e.options.data.isNew === true)?false:true;
			}
			return false;
		});
		
		if( hasOld.length === 0 ){
			this.removeAllItems();
		}else{
			appdb.vappliance.ui.ShowVerifyDialog({
				title: "Remove All Images from Group",
				message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to remove all images of this group. Are you sure you want to procced?</span>",
				onOk: (function(self){
					return function(){
						self.removeAllItems();
					};
				})(this)
			});
		}
	};
	this.checkEmpty = function(){
		$(this.dom).children(".emptylist").remove();
		if($(this.options.dom.list).children("li").length === 0){
			$(this.dom).append("<div class='emptylist icontext mandatory'><img src='/images/vappliance/warning.png' alt=''/><span>No images defined yet.</span></div>");
			$(this.dom).parent().children(".actions").children(".action.clearall").addClass("disabled");
		}else{
			$(this.dom).parent().children(".actions").children(".action.clearall").removeClass("disabled");
		}
	};
	this.renderHelp = function(dom){
		$(dom).find(".helppopup").remove();
		var helpmessages = $(dom).find(".helpmessage");
		$(helpmessages).addClass("hidden");
		$.each(helpmessages, function(i, e){
			var h = $("<a href='#' class='helppopup' title='' tab-index='-1'><img src='/images/question_mark.gif' alt=''/></a>");
			var help = $(e).html();
			var node = $(h)[0];
			$(e).after(h);
			var tt = new dijit.Tooltip({
				connectId: node,
				label: "<div class='vapphelppopupmessage long'>" + help + "</div>",
				position: "above"
			});
		});
	};
	this.render = function(d){
		this.reset();
		var instances = [];
		for(var v in this.subviews){
			if( !this.subviews.hasOwnProperty(v) ) continue;
			this.subviews[v].reset();
			this.subviews[v] = null;
		}
		this.subviews = [];
		if( d && $.isArray(d) === false ){
			this.renderProperties(d);
			instances = d.instance || [];
			instances = $.isArray(instances)?instances:[instances];
		}else{
			instances = d || [];
			instances = $.isArray(instances)?instances:[instances];
		}
		if( instances.length > 0 ){
			$.each(instances, (function(self){
				return function(i,e){
					var item = self.addItem(e);
					if( item ){
						var li = $(document.createElement("li"));
						$(li).append(item);
						$(self.options.dom.list).append(li);
					}
				};
			})(this));
		}
		this.checkEmpty();
		this.initActions();
	};
	this.initContainer = function(){
		var tempname = $.trim( $(this.dom).data("usetemplate") );
		if( tempname !== "" ){
			var tempdom = appdb.vappliance.ui.CurrentVAManager.getTemplate(tempname);
			if( $(tempdom).length > 0 ){
				this.options.itemTemplate = $(tempdom).clone(true);
			}
		}
		$(this.dom).append(this.options.dom.list);
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.subviews = [];
		this.initContainer();
	};
	this._init();
});


appdb.vappliance.ui.views.VApplianceVMIItem = appdb.ExtendClass(appdb.vappliance.ui.views.DataPropertyContainer, "appdb.vappliance.ui.views.VApplianceVMIItem", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		isEditable: ( (typeof o.editable === "boolean")?o.editable:false ),
		vmiversionList: null
	};
	this.getData = function(){
		var props = this.getPropertyData();
		var d = this.options.data;
		var list = this.subviews.vmiversionList;
		var res = {
			id: props.id || d.id,
			description: props.description || d.description,
			group: props.group || d.group,
			notes: props.notes || d.notes,
			instance: ( !list)?[]:list.getData()
		};
		return res;
	};
	this.renderVMIVersionList = function(d){
		d.instance = d.instance || [];
		d.instance = $.isArray(d.instance)?d.instance:[d.instance];
		if( this.subviews.vmiversionList ){
			this.subviews.vmiversionList._mediator.clearAll();
			this.subviews.vmiversionList.reset();
			this.subviews.vmiversionList = null;
		}
		this.subviews.vmiversionList = new appdb.vappliance.ui.views.VApplianceVMIVersionList({
			container: $(this.dom).find(".vaversion-vmiversionlist:first"),
			parent: this,
			editable: this.canEdit(),
			data: d.instance
		});
		this.subviews.vmiversionList.render(d.instance);
	};
	this.initActions = function(){
		$(this.dom).children(".actions").children(".action.remove").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.parent.removeItem(self);
				return false;
			};
		})(this));
	};
	this.render = function(d){
		$(this.dom).data("inst", this);
		this.renderProperties(d);
		this.renderVMIVersionList(d);
		this.initActions();
	};
	this.initContainer = function(){
		
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
});

appdb.vappliance.ui.views.VApplianceVMIList = appdb.ExtendClass(appdb.vappliance.ui.views.DataPropertyContainer, "appdb.vappliance.ui.views.VApplianceVMIList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		currentData: [],
		isEditable: ( (typeof o.editable === "boolean")?o.editable:false ),
		listContainer: $(document.createElement("div")).addClass("vmilist"),
		itemTemplate: $(document.createElement("div")).addClass("vmiitem"),
		items: [],
		dom: {
			list: $(document.createElement("ul"))
		}
	};
	this.getSubContainers = function(){
		return this.subviews;
	};
	this.getData = function(){
		var res = [];
		if( $.isArray(this.subviews) ){
			$.each(this.subviews, (function(self){
				return function(i,e){
					res.push(e.getData());
				};
			})(this));
		}
		return res;
	};
	this.initActions = function(){
		$(this.dom).parent().children(".actions").children(".action.new").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.addNewItem({});
				return false;
			};
		})(this));
		$(this.dom).parent().children(".actions").children(".action.clearall").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("disabled") === false ){
					self.clearAll({});
				}
				return false;
			};
		})(this));
		$(this.dom).parent().find(".action").attr("tabIndex","-1");
	};
	this.clearAll = function(){
		var hasOld = $.grep(this.subviews, function(e){
			if( e ){
				return (e.options.data.isNew === true)?false:true;
			}
			return false;
		});
		
		if( hasOld.length === 0 ){
			this.removeAllItems();
		}else{
			appdb.vappliance.ui.ShowVerifyDialog({
				title: "Remove all VMI Groups",
				message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to remove all VMI groups and all of the images of this version. Are you sure you want to procced?</span>",
				onOk: (function(self){
					return function(){
						self.removeAllItems();
					};
				})(this)
			});
		}
	};
	this.removeAllItems = function(){
		while(this.subviews.length > 0 ){
			this._deleteItem(this.subviews[0]);
		}
	};
	this.addNewItem = function(item){
		item = item || {};
		var newitem = $.extend({},item);
		newitem.isNew = true;
		newitem.index = this.options.items.length+1;
		var li = $(document.createElement("li"));
		var groupnum = $(this.dom).children("ul").children("li").length;
		newitem.group = 'General group' + ((groupnum>0)?" (" + groupnum + ")":"");
		
		var div = this.addItem(newitem);
		$(li).append(div);
		$(this.options.dom.list).prepend(li);
		this.checkEmpty();
	};
	this.clearItems = function(){
		$.each(this.options.items, function(i,e){
			if( e ){
				e._mediator.clearAll();
				e.reset();
				e = null;
			} 
		});
		$(this.options.dom.list).empty();
		this.options.dom.list = $(document.createElement("ul"));
		this.options.items = [];
	};
	this.addItem = function(d){
		var cont = $(this.options.itemTemplate).clone(true);
		var item = new appdb.vappliance.ui.views.VApplianceVMIItem({
			container: cont,
			parent: this,
			editable: this.canEdit(),
			data: d
		});
		this.subviews.push(item);
		item.render(d);
		if( this.isEditMode() ){
			item.edit();
			if( d.isNew === true ){
				item.setFocus(true);
			}
			appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
		}
		return cont;
	};
	this._deleteItem = function(item){
		var index = -1;
		$.each(this.subviews, function(i, e){
			if( e === item ){
				index = i;
			}
		});
		if( index > -1 ){
			appdb.vappliance.ui.CurrentVAVersionValidatorRegister.unregister(this.subviews[index]);
			this.subviews[index].reset();
			this.subviews.splice(index,1);
			$(item.dom).parent().addClass("toberemoved");
			$(this.dom).children("ul").children("li.toberemoved").remove();
			this.checkEmpty();
			appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
		}
	};
	this.removeItem = function(item){
		if( item.options.data.isNew === true ){
			this._deleteItem(item);
		}else{
			appdb.vappliance.ui.ShowVerifyDialog({
				title: "VMI Group removal",
				message: "<img src='/images/repository/warning.png' alt=''/><span>You are about to remove this VMI group and all of the images defined under it. Are you sure you want to procced?</span>",
				onOk: (function(self,data){
					return function(){
						self._deleteItem(data);
					};
				})(this,item)
			});
		}
	};
	this.checkEmpty = function(){
		$(this.dom).children(".emptylist").remove();
		if($(this.options.dom.list).children("li").length === 0){
			$(this.dom).append("<div class='emptylist icontext mandatory'><img src='/images/vappliance/warning.png' alt=''/><span>No VMI Groups defined yet.</span></div>");
			$(this.dom).parent().children(".actions").children(".action.clearall").addClass("disabled");
		}else{
			$(this.dom).parent().children(".actions").children(".action.clearall").removeClass("disabled");
		}
	};
	this.hasChanges = function(){
		return false;
	};
	this.render = function(d){
		this.reset();
		$(this.dom).children("ul").remove();
		this.options.dom.list = $(document.createElement("ul"));
		$(this.dom).append(this.options.dom.list);
		var images = [];
		if( $.isArray(d) === false ){
			this.renderEmptyValues(d);
			this.renderProperties(d);	
			images = d.image || [];
			images = $.isArray(images)?images:[images];
		}else{
			images = d || [];
			images = $.isArray(images)?images:[images];
		}
		
		$.each(images, (function(self){
			return function(i,e){
				var item = self.addItem(e);
				if( item ){
					var li = $(document.createElement("li"));
					$(li).append(item);
					$(self.options.dom.list).append(li);
				}
			};
		})(this));
		this.initActions();
		this.checkEmpty();
		setTimeout(function(){
			appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
		},10);
	};
	this.isValid = function(){
		return ( $(this.dom).children("ul").children("li").length > 0 );
	};
	this.postEdit = function(){
		setTimeout((function(self){
			return function(){
				$(self.dom).parent().children(".actions").children(".action.new").stop().clearQueue().removeAttr("style").css({"background-color":"#FF8A4C","outline-width":"10px","outline-color":"#FF8A4C","outline-style":"solid","opacity":"0.5"}).animate({"outline-width":"0px","opacity":"1"}, 1000, "linear", function(){
					$(this).animate({"background-color":"#ffffff"},3000,"linear",function(){
						$(this).removeAttr("style");
					});
				}).on("mousemove.animate",function(ev){
					$(this).stop().clearQueue();
					$(this).removeAttr("style");
					$(this).off("mousemove.animate");
				});
			};
		})(this),10);
	};
	this.initContainer = function(){
		var tempname = $.trim( $(this.dom).data("usetemplate") );
		if( tempname !== "" ){
			var tempdom = appdb.vappliance.ui.CurrentVAManager.getTemplate(tempname);
			if( $(tempdom).length > 0 ){
				this.options.itemTemplate = $(tempdom).clone(true);
			}
		}
		$(this.dom).append(this.options.dom.list);
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.data = $.isArray(this.options.data)?this.options.data:[this.options.data];
		this.subviews = [];
		this.initContainer();
	};
	this._init();
});

appdb.vappliance.components.IntegrityChecker = appdb.ExtendClass(appdb.Component,"appdb.vappliance.components.IntegrityChecker", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data,
		images: [],
		dom: {
			status: $("<div class='integritystatus'></div>")
		}
	};
	this.renderError = function(err){
		console.log(err);
		err = {
			"status": "Cannot proceed with integrity check",
			"description": err
		};
		var errdialog = new appdb.views.ErrorHandler();
		errdialog.handle(err);
	};
	this.render = function(data){
		this.views.status.render(data);
		var images = (data && data.images)?data.images:[];
		images = images || [];
		images = $.isArray(images)?images:[images];
		$.each(images, (function(self){
			return function(i,e){
				var img = self.options.images[e.id];
				if( img ){
					img.render(e);
				}
			};
		})(this));
		if( data.status === "running" ){
			setTimeout((function(self){
				return function(){
					self.load();
				};
			})(this),1500);
		}else if(data.status !== "running" && $(this.parent.dom).hasClass("verifing")){
			$(this.parent.dom).find(".toolbar .actions .action.verifing a").remove();
			if(data.status === "error"){
				$(this.parent.dom).find(".toolbar .actions .action.verifing > img").attr("src","/images/vappliance/redwarning.png");
				$(this.parent.dom).find(".toolbar .actions .action.verifing > .message").text("Integrity check was not successful");
				this.renderError($.trim(data.message) || "Integrity check was not successful");
			}else if(data.status === "success" ){
				$(this.parent.dom).find(".toolbar .actions .action.verifing > img").attr("src","/images/tick.png");
				$(this.parent.dom).find(".toolbar .actions .action.verifing > .message").text("Integrity check was successful");
			}else if(data.status === "canceled" ){
				$(this.parent.dom).find(".toolbar .actions .action.verifing > img").attr("src","/images/vappliance/warning.png");
				$(this.parent.dom).find(".toolbar .actions .action.verifing > .message").text("Integrity check was canceled by the user");
			}
			setTimeout((function(self,data){
				return function(){
					if(data.status === "success" && data.published === "true"){
						$(appdb.vappliance.ui.CurrentVAManager.dom).children(".groupcontainer").find("ul > li.latestversion > a").trigger("click");
					}
					appdb.vappliance.ui.CurrentVAManager.reload(data.id);
				};
			})(this,data),3000);
		}
	};
	this.load = function(){
		if( !this.options.data )return;
		$.ajax({
			url: appdb.config.endpoint.base + "apps/integritycheck",
			data: {versionid: this.options.data.id },
			dataType: "json",
			success: (function(self){
				return function(d){
					self.render(d);
				};
			})(this),
			error: (function(self){
				return function(err){
					self.renderError(err);
				};
			})(this)
		});
	};
	this.initContainer = function(){
		$(this.dom).children(".integritystatus").remove();
		if( $(this.dom).find(".integritystatuscontainer").length > 0 ){
			$(this.dom).find(".integritystatuscontainer").prepend(this.options.dom.status);
		}else{
			$(this.dom).prepend(this.options.dom.status);
		}
	};
	this.initViews = function(){
		this.views.status = new appdb.vappliance.ui.views.IntegrityCheckerStatus({
			parent: this,
			container: $(this.options.dom.status)
		});
		$.each($(this.dom).find(".vaversion-vmiversionlist > ul > li > .vaversion-vmiversion"),(function(self){
			return function(i,e){
				self.options.images[$(e).data("id")] = new appdb.vappliance.ui.views.ImageIntegrityCheckerStatus({
					container: $(e),
					parent: self
				});
				//create ImageIntegrityCheckerStatus 
			};
		})(this));
	};
	this.init = function(){
		this.parent = this.options.parent;
		this.dom = this.options.container;
		this.initContainer();
		this.initViews();
	};
	this.init();
});

appdb.vappliance.ui.views.IntegrityCheckerStatus = appdb.ExtendClass(appdb.View,"appdb.vappliance.ui.views.IntegrityCheckerStatus", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		prevStatus: null,
		dom: {
			title: $("<div class='title'></div>"),
			status: $("<div class='status'></div>"),
			message: $("<div class='message'></div>"),
			list: $("<ul ></ul>")
		}
	};
	//get item's status from starting integrity check
	this.getItemStatus = function(d){
		var id = d.id;
		var res = {status: "", message: ""};
		var el = $(this.parent.dom).find("ul > li > .vaversion-vmiversion[data-id='"+id+"']");
		if( el.length > 0 ){
			if( $(el).find(".vmiversion-integritystatus").length > 0 ){
				res.status = $.trim($(el).find(".vmiversion-integritystatus .value").text()).toLowerCase();
				res.message = $.trim($(el).find(".vmiversion-integritymessage .value").text()).toLowerCase();
			}
			if( $.inArray(res.status,["error","failed","canceled","n/a","n\\a"]) > -1 ) {
				return res;
			}
		}
		return null;
	};
	this.formatSizeUnits = appdb.vappliance.utils.formatSizeUnits;
	this.renderProgress = function(d,dom){
		if( !d || !d.process || !d.process.downloaded || d.process.downloaded === "0" || d.process.downloaded === 0) return;
		
		var process = $(dom).find(".result > .process");
		var downloaded = $(process).children(".downloaded");
		var current = $(downloaded).children(".current");
		var size = $(downloaded).children(".size");
		var bar = $(process).children(".bar");
		var percentage = $(downloaded).children(".percentage");
		
		$(current).text(this.formatSizeUnits(d.process.downloaded,false));
		$(size).text(this.formatSizeUnits(d.process.size));
		$(percentage).text(d.process.percentage + "%");
		$(bar).css({"width":d.process.percentage + "%"});
	};
	this.renderStatus = function(d,dom){
		var status = $(dom).find(".result > .status");
		var message = $(dom).find(".result > .message");
		if( d.displayStatus || d.status ){
			$(status).html(d.displayStatus || d.status );
		}else{
			$(status).html("");
		}
		
		if( d.message && d.status !== "success"){
			$(message).html(d.message);
		}else{
			$(message).html("");
		}
	};
	this.renderTitle = function(d,dom){
		var title = $("<div class='title'></div>");
		var group = $("<span class='group'></span>");
		var version = $("<span class='version'></span>");
		var url = $("<span class='url'></span>");
		
		var p = $(this.parent.dom).find("ul > li > .vaversion-vmiversion[data-id='"+d.id+"']");
		if( $(p).length === 0 ) return;
		
		var propversion = $(p).find(".property.vmiversion-version > .value");
		if( $(propversion).length > 0 ){
			$(version).text($(propversion).text());
		}
		
		var propurl = $(p).find(".property.vmiversion-url > .value");
		if( $(propurl).length > 0 ){
			$(url).text($(propurl).text());
		}
		
		var propgroup = $(p).closest(".vaversion-vmi.property").find(".content > .property.vavmi-description > .value");
		if( $(propgroup).length > 0 ){
			$(group).text($(propgroup).text());
		}
		
		$(title).append(version)
				.append("<span class='id'>["+d.id+"]</span>")
				.append(url);
		$(dom).append(title);
		$(url).off("mouseup").on("mouseup", function(ev){
			ev.preventDefault();
			return false;
		});
	};
	this.renderItem = function(d,ul){
		var li = $(ul).children("li[data-id='"+d.id+"']");
		if( $(li).length === 0 ){
			li = $("<li data-id='" + d.id + "'></li>");
			$(this.options.dom.list).append(li);
		}
		var div = $(li).children(".result");
		if( $(div).length === 0 ) {
			div = $("<div class='result'></div>");
			$(li).append(div);
		}
		var title = $(div).children(".title");
		if( $(title).length === 0 ){
			this.renderTitle(d,div);
		}
		var statuspanel = $(div).children(".status");
		if( $(statuspanel).length === 0 ){
			statuspanel = $("<div class='status'></div>");
			$(div).append(statuspanel);
		}
		var process = $(div).children(".process");
		if( $(process).length === 0 ){
			process = $("<div class='process'><div class='bar'></div><div class='downloaded'><div class='current'></div><div class='seperator'>/</div><div class='size'></div><div class='percentage'></div></div></div>");
			$(div).append(process);
		}
		var message = $(div).children(".message");
		if( $(message).length === 0 ){
			message = $("<div class='message'></div>");
			$(div).append(message);
		}
		
		//In case something went wrong with url validation 
		//use tht information instead of image integrity status, 
		//since status it might have previously cached values.
		var prevstatus = this.getItemStatus(d);
		if( prevstatus !== null ){
			d.status = prevstatus.status;
			d.message = prevstatus.message;
		}
		if( d.status === "running" && d.process && (d.process.percentage<<0) === 100 ){
			d.status = "checksuming";
		}else if(d.status === "running" && d.process && (d.process.percentage<<0) < 100){
			d.status = "downloading";
		}
		$(li).removeClass("checksuming downloading success warning failed canceled");
		switch(d.status){
			case "checksuming":
				d.displayStatus = "<span class='icontext'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>Calculating checksum</span></span>";
				break;
			case "downloading":
				d.displayStatus = "<span class='icontext'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>Downloading image</span></span>";
				if(d.process){
					this.renderProgress(d,li);
				}
				break;
			case "success":
				d.displayStatus = "<span class='icontext'><img src='/images/tick.png' alt=''/><span>Successfully checked</span></span>";
				break;
			case "warning":
				break;
			case "failed":
				d.displayStatus = "<span class='icontext'><img src='/images/vappliance/redwarning.png' alt=''/><span>Image failed check</span></span>";
				break;
			case "canceled":
				d.displayStatus = "<span class='icontext'><img src='/images/vappliance/warning.png' alt=''/><span>Image check was canceled by user</span></span>";
				break;
			case "error":
				d.displayStatus = "<span class='icontext'><img src='/images/vappliance/redwarning.png' alt=''/><span>An error occured</span></span>";
				break;
			default:
				break;
		}
		if( this.options.prevStatus === null || $.trim(this.options.prevStatus.status) === "" || this.options.prevStatus !== d.status ){
			this.renderStatus(d,li);
		}
		this.options.prevStatus = d.status;
		$(li).addClass(d.status);
		$(div).on("click", (function(id,dom){
			return function(ev){
				ev.preventDefault();
				var obj = $(dom).find("div[data-id='"+id+"']");
				if( obj.length === 0 ) return false;
				var top = $(obj).offset().top;
				if( top > 80 ){
					top = top - 80;
				}else{
					top = 0;
				}
				$(obj).parent().css({"outline-width":"10px","outline-color":"#ffff88","outline-style":"solid"}).animate({"outline-width": "0px"}, 5000,function(){
					$(this).removeAttr("style");
				});
				window.scrollTo(0,top);
				return false;
			};
		})(d.id,this.parent.dom));
		this.options.firstRender = false;
		
	};
	this.getOrderedImages = function(imgs){
		var result = [];
		$(this.parent.dom).find(".vaversion-vmiversion.property").each(function(i,e){
			var found = false;
			$.each(imgs, function(ii,ee){
				if( found === true )return;
				if( (""+$(e).data("id")) === (""+ee.id) ){
					found = true;
					result.push(ee);
				}
			});
		});
		return result;
	};
	this.render = function(data){
		var ximages = data.images || [];
		ximages = $.isArray(ximages)?ximages:[ximages];
		this.initContainer();
		var images = this.getOrderedImages(ximages);
		
		$.each(images, (function(self){
			return function(i,e){
				self.renderItem(e,self.options.dom.list);				
			};
		})(this));
	};
	this.initContainer = function(){
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.title);
		$(this.dom).append(this.options.dom.status);
		$(this.dom).append(this.options.dom.message);
		$(this.dom).append(this.options.dom.list);
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this.init();
});

appdb.vappliance.ui.views.ImageIntegrityCheckerStatus = appdb.ExtendClass(appdb.View,"appdb.vappliance.ui.views.ImageIntegrityCheckerStatus", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			container: $('<div class="imageintegrityprocess"></div>'),
			content: $('<div class="content"></div>'),
			title: $('<div class="title">Integrity Check</div>'),
			status: $('<div class="status">Starting...</div>'),
			progress: $('<div class="progress"></div>')
		}
	};
	this.render = function(d){
		d = d || {};
		var process = d.process || {};
		$(this.options.dom.container).removeClass("running success failed checksuming downloading");
		switch(d.status){
			case "running":
			case "downloading":
			case "checksuming":
				if( process.percentage < 100){
					$(this.options.dom.status).html("Downloading");
					$(this.options.dom.progress).html(process.percentage + " %");
				}else{
					$(this.options.dom.status).html("Calculating Image Checksum");
					$(this.options.dom.progress).html("");
				}
				$(this.options.dom.container).addClass("running");
				break;
			case "success":
				$(this.options.dom.status).html("Image successfully checked");
				$(this.options.dom.progress).html("");
				$(this.options.dom.container).addClass("success");
				break;
			default:
				$(this.options.dom.status).html("Image failed");
				if( $(this.dom).find(".vmiversion-integritymessage > .value") ){
					$(this.options.dom.progress).html( $(this.dom).find(".vmiversion-integritymessage > .value").html() );
				}
				$(this.options.dom.progress).html(d.message);
				$(this.options.dom.container).addClass("failed");
				break;
		}
		
	};
	this.initContainer = function(){
		$(this.dom).find(".imageintegrityprocess").remove();
		$(this.options.dom.content).append(this.options.dom.title).append(this.options.dom.status).append(this.options.dom.progress);
		$(this.options.dom.container).append(this.options.dom.content).append('<div class="sheet"></div>');
		$(this.dom).prepend(this.options.dom.container);
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this.init();
});

appdb.vappliance.ui.views.UnusedImageListItem = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.UnusedImageListItem", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		selected: false,
		dom: {
			container: null,
			header: null,
			content: null,
			footer: null,
			selector: null
		}
	};
	this.getFullData = function(){
		return this.options.data;
	};
	this.getData = function(){
		var d = $.extend(true, {}, this.options.data);
		return d;
	};
	this.isSelected = function(){
		return this.options.selected;
	};
	this._createPropertyDom = function(name,value, header){
		header = (typeof header === "undefined")?name:header;
		return $("<div class='"+name+" property'><div class='header'>"+header+":</div><div class='value'>"+value+"</div></div>");
	};
	this.selectItem = function(select){
		select = (typeof select === "boolean")?select:!this.options.selected;
		var changed = (this.options.selected === select)?false:true;
		this.options.selected = select;
		if( changed === true ){
			$(this.dom).toggleClass("selected");
			this.publish({event: "change", value: this.options.selected});
		}
	};
	this.renderPopups = function(){
		$.each($(this.dom).find(".property.popupvalue"), function(i,e){
			var cid = $(this).children(".header:first");
			var val = $(this).children(".value:first").html();
			if( $(cid).length === 0 ) return;
			var header = $(cid).html(); 
			if(header && header.indexOf("<")>-1 && $(header).hasClass("popup") ){
				val = $(header).find(".value > span:first").html();
				header = $(header).find(".field > .title:first").html();
			}
			$(this).find(".popup").remove();
			if( $(this).hasClass("popupvalue") ){
				$(cid).html("<div class='popup'><div class='field'><span class='title'>" + header + "</span><span class='arrow'>▼</span></div><div class='value'><span>" + val + "</span></div>");
				$(cid).off("click").on("click", function(ev){
					ev.preventDefault();
					$("body .vappliance .property.popupvalue > .header.selected").removeClass("selected");
					$("body .vappliance .propertypopupvalue > .header.selected").removeClass("selected");
					$(this).addClass("selected");
					return false;
				});
			}
		});
	};
	this.render = function(d){
		d = d || this.options.data || {};
		var addedon = $.trim(d.addedon).split("T");
		addedon = addedon[0] + ((addedon.length > 1)?" " + addedon[1]:"");
		addedon = addedon.split(".")[0];
		var publishedon = $.trim(d.publishedon).split("T");
		publishedon = publishedon[0] + ((publishedon.length > 1)?" " + publishedon[1]:"");
		publishedon = publishedon.split(".")[0];
		$(this.dom).empty();
		var version = this._createPropertyDom("version",d.version);
		var identifier = this._createPropertyDom("identifier",d.identifier).addClass("popupvalue");
		var location = this._createPropertyDom("location",d.url).addClass("popupvalue");
		var addedon = this._createPropertyDom("addedon",addedon, "added on").addClass("popupvalue");
		var publishedon = this._createPropertyDom("publishedon",publishedon, "published on").addClass("popupvalue");
		var title = this._createPropertyDom("title",d.title);
		var os = this._createPropertyDom("os",d.os.val());
		var osver = this._createPropertyDom("osversion",d.os.version,"os version");
		var arch = this._createPropertyDom("arch",d.arch.val(), "architecture");
		var hyper = this._createPropertyDom("hypervisor", d.hypervisor.val());

		$(this.options.dom.header).empty().append(version).append(identifier).append(location).append(addedon).append(publishedon);
		$(this.options.dom.content).empty().append(title);
		$(this.options.dom.footer).empty().append(os).append(osver).append(arch).append(hyper);
		$(this.dom).empty().append(this.options.dom.container);
		$(this.options.dom.container).off("click").on("click", (function(self){
			return function(ev){
				self.selectItem();
			};
		})(this));
		this.renderPopups();
	};
	this.initContainer = function(){
		$(this.dom).empty();
		this.options.dom.container = $("<div class='unusedimage'></div>");
		this.options.dom.header = $("<div class='header'></div>");
		this.options.dom.content = $("<div class='content'></div>");
		this.options.dom.footer = $("<div class='footer'></div>");
		this.options.dom.selector = $("<div class='selector'><div class='box'><div class='tick'></div></div></div>");
		$(this.options.dom.container).append(this.options.dom.selector).append(this.options.dom.header).append(this.options.dom.content).append(this.options.dom.footer);
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this.init();
});
appdb.vappliance.ui.views.UnusedImageList = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.UnusedImageList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		multiselect: (typeof o.multiselect === 'boolean')?o.multiselect:true,
		items: [],
		dom: {
			header: $("<div class='header'></div>"),
			toolbar: $("<div class='toolbar'></div>"),
			selectcount: $("<div class='selectedcount'><span class='count'></span><span>items selected</span></div>"),
			actions: $("<div class='actions'></div>"),
			list: $("<ul class='unusedimagelist'></ul>"),
			empty: $("<div class='emptycontent'><div class='content'><img src='/images/exclam16.png' alt=''/><span>There are no unused images to select.</span></div></div>")
		}
	};
	this.getSelectedItems = function(){
		return $.grep(this.options.items, function(e){
			return e.isSelected();
		});
	};
	this.getSelectedData = function(){
		var sitems = this.getSelectedItems();
		var result = [];
		$.each(sitems, function(i,e){
			result.push(e.getData());
		});
		return result;
	};
	this.clearAllItems = function(){
		$.each(this.options.items, (function(self){ 
			return function(i,e){
				e.unsubscribeAll();
				e.reset();
				e = null;
			}; 
		})(this));
		this.options.items = [];
		$(this.options.dom.list).empty();
	};
	this.renderEmpty = function(isempty){
		isempty = (typeof isempty === "boolean")?isempty:true;
		$(this.dom).removeClass("empty");
		$(this.parent.options.dom.dialog).removeClass("empty");
		if( isempty === true ){
			$(this.dom).addClass("empty");
			$(this.parent.options.dom.dialog).addClass("empty");
		}
	};
	this.render = function(d){
		d = d || this.options.data;
		d = d || [];
		d = $.isArray(d)?d:[d];
		this.options.data = d;
		this.clearAllItems();
		this.renderEmpty((d.length === 0));
		$.each(d, (function(self){
			return function(i, e){
				var li = $("<li></li>");
				$(self.options.dom.list).append(li);
				var item = new appdb.vappliance.ui.views.UnusedImageListItem({
					container: $(li),
					parent: self,
					data: e
				});
				item.render();
				item.subscribe({event: "change", callback: (function(item){ return function(){
					this.selectionChanged(item);
				};})(item),caller:self});
				self.options.items.push(item);
			};
		})(this));
		this.renderToolbar();
	};
	this.selectAll = function(){
		$.each(this.options.items, function(i,e){
			e.selectItem(true);
		});
	};
	this.unselectAll = function(){
		$.each(this.options.items, function(i,e){
			e.selectItem(false);
		});
	};
	this.unselectAllExcept = function(item){
		$.each(this.options.items, function(i,e){
			if( item !== e ) {
				e.selectItem(false);
			}
		});
	};
	this.viewSelected = function(){
		$(this.dom).addClass("viewselected");
	};
	this.viewAll = function(){
		$(this.dom).removeClass("viewselected");
	};
	this.selectionChanged = function(item){
		if( this.options.multiselect ) {
			this.renderToolbar();
		} else if( item.isSelected() ) {
			this.unselectAllExcept(item);
		}
	};
	
	this.renderToolbar = function(){
		var selectedItems = this.getSelectedItems();
		$(this.options.dom.selectcount).find(".count").html(selectedItems.length);
		if( selectedItems.length > 0 ){
			$(this.dom).addClass("selected");
		}else{
			$(this.dom).removeClass("selected");
		}
		if( selectedItems.length === this.options.items.length){
			$(this.dom).addClass("selectedall");
		}else{
			$(this.dom).removeClass("selectedall");
		}
		if( this.options.items.length > 0 ){
			$(this.dom).addClass("hasitems");
		}else{
			$(this.dom).removeClass("hasitems");
		}

	};
	this.initToolbar = function(){
		var actSelectAll = $("<a href='#' title='Select all items of the list' class='selectall icontext action'><span>select all</span></a>");
		var actUnselectAll = $("<a href='#' title='Unselect all items of the list' class='unselectall icontext action'><span>unselect all</span></a>");
		var actViewSelected = $("<a href='#' title='View only selected items' class='viewselected icontext action'><span>view selected</span></a>");
		var actViewAll = $("<a href='#' title='View only selected items' class='viewall icontext action'><span>view all</span></a>");
		$(this.options.dom.actions).empty();
		$(this.options.dom.actions).append(actSelectAll).append(actUnselectAll).append(actViewSelected).append(actViewAll);
		$(actViewSelected).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.viewSelected();
				return false;
			};
		})(this));
		$(actViewAll).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.viewAll();
				return false;
			};
		})(this));
		$(actSelectAll).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.selectAll();
				return false;
			};
		})(this));
		$(actUnselectAll).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.unselectAll();
				return false;
			};
		})(this));
		
	};
	this.initContainer = function(){
		if( $(this.dom).children(".header").length > 0 ){
			this.options.dom.header = $(this.dom).children(".header");
		}else{
			this.options.dom.header.empty().append(appdb.vappliance.ui.views.UnusedImageList.messages.header);
			$(this.dom).append(this.options.dom.header);
		}
		if( $(this.dom).children(".emptycontent").length > 0 ){
			this.options.dom.empty = $(this.dom).children(".emptycontent");
		}else{
			$(this.dom).append(this.options.dom.empty);
		}
		if( $(this.dom).find(".toolbar").length > 0 ){
			this.options.dom.toolbar = $(this.dom).find(".toolbar");
		}else{
			$(this.dom).append(this.options.dom.toolbar);
		}
		if( $(this.options.dom.toolbar).find(".selectedcount").length > 0 ){
			this.options.dom.selectcount = $(this.options.dom.toolbar).find(".selectedcount");
		}else{
			$(this.options.dom.toolbar).append(this.options.dom.selectcount);
		}
		if( $(this.options.dom.toolbar).find(".actions").length > 0 ){
			this.options.dom.actions = $(this.options.dom.toolbar).find(".actions");
		}else{
			$(this.options.dom.toolbar).append(this.options.dom.actions);
		}
		if( $(this.dom).find("ul.unusedimagelist").length > 0 ){
			this.options.dom.list = $(this.dom).find("ul.unusedimagelist");
		}else{
			$(this.dom).append(this.options.dom.list);
		}
		$(this.options.dom.list).empty();
		this.initToolbar();
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this.init();
},{
	messages: {
		header: "<div class='title'>From this dialog you are able to reuse images published in the past. With this feature, you will be able to import an image published in the past and,</div>"+
				"<ul><li>1. use it 'as is' in the new Virtual Appliance version (use-case: <b>republishing an image</b>)</li>"+
				"<li>2. or, use it as a starting point/base for describing an image update (use-case: <b>image update</b>)</li></ul>"
	}
});
appdb.vappliance.components.UnusedImages = appdb.ExtendClass(appdb.Component, "appdb.vappliance.components.UnusedImages", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || null,
		currentIndentifiers: {},
		multiselect: (typeof o.multiselect === 'boolean')?o.multiselect:true,
		strictmode: (typeof o.strictmode === 'boolean')?o.strictmode:true,
		dialog: null,
		unusedlist: null,
		dom:{
			loading: $("<div class='loading hidden'><span>loading</span></div>"),
			dialog: $("<div class='unsusedimagedialog vappliance'></div>"),
			list: $("<div class='list'></div>")
		}
	};
	this.getLocalData = function(){
		var m = appdb.vappliance.ui.CurrentVAManager.getModel();
		if( !m ) return null;
		var l = m.getLocalData();
		if( !l || !l.appliance ) return null;
		var vapp = l.appliance;
		if( !vapp.instance ) return null;
		vapp.instance = vapp.instance || [];
		vapp.instance = $.isArray(vapp.instance)?vapp.instance:[vapp.instance];
		return $.extend(true,{},vapp.instance);
	};
	this.show = function(){
		this.cancel();
		var okbutton = $(document.createElement("button")).append("<div class='icontext'><span>select</span></div>");
		var cancelbutton = $(document.createElement("button")).append("<div class='icontext'><span>cancel</span></div>");
		var okcontainer = $(document.createElement("div")).addClass("action").addClass("ok").append(okbutton);
		var cancelcontainer = $(document.createElement("div")).addClass("action").addClass("cancel").append(cancelbutton);
		var actions = $(document.createElement("div")).addClass("actions").append(okcontainer).append(cancelcontainer);
		
		$(this.options.dom.dialog).addClass(( (this.options.multiselect===true)?"multiselect":"singleselect" ));
		this.options.dialog = new dijit.Dialog({
			title: "Reuse images",
			content: $(this.options.dom.dialog)[0],
			style: o.css || "max-width:830px;width:830px;max-height:500px;height:470px;"
		});

		$(this.options.dom.dialog).append(this.options.dom.list).append(actions);	
		new dijit.form.Button({
			label: "cancel",
			onClick: (function(self){
				return function() {
					self.cancel();
				};
			})(this)
		},$(cancelbutton)[0]);
		new dijit.form.Button({
			label: "ok",
			onClick: (function(self){
				return function() {
					self.save();
				};
			})(this)
		},$(okbutton)[0]);
		this.options.dialog.show();
	};
	this.save = function(){
		var seldata = this.options.unusedlist.getSelectedData();
		$.each( seldata, (function(self){
			return function(i,e){
				self.parent.addNewItem(e);
			};
		})(this));
		this.cancel();
	};
	this.cancel = function(){
		if( this.options.dialog !== null ){
			this.options.dialog.hide();
			this.options.dialog.destroyRecursive(false);
			this.options.dialog = null;
		}
	};
	this.render = function(d){
		var data = this.filterData(d);
		if( this.options.unusedlist !== null ){
			this.options.unusedlist.unsubscribeAll();
			this.options.unusedlist.reset();
			this.options.unusedlist = null;
		}
		this.options.unusedlist = new appdb.vappliance.ui.views.UnusedImageList({
			container: $(this.options.dom.list),
			parent: this,
			data: data,
			multiselect: this.options.multiselect
		});
		this.options.unusedlist.render(data);
	};
	this.filterDataStrict = function(d){
		d = d  || [];
		var res = [];
		var include = {};
		var exclude = {};
		var local = {};
		var currents = this.options.currentIndentifiers;
		var wv = appdb.vappliance.ui.CurrentVAManager.getContainer("workingversion");
		var doms = $(wv.dom).find(".property.vaversion-identifier.popupvalue .header .value");
		$.each(doms, function(i,e){
			exclude[$(this).text()] = true;
		});
		var locald = this.options.data || [];
		locald = $.isArray(locald)?locald:[locald];
		$.each(locald, function(i,e){
			local[e.identifier] = e;
		});
		
		$.each(d, function(i,e){
			//set unpublished as excluded images
			e.image = e.image || [];
			e.image = $.isArray(e.image)?e.image:[e.image];
			$.each(e.image, function(ii,img){
					img.instance = img.instance || [];
					img.instance = $.isArray(img.instance)?img.instance:[img.instance];
					$.each(img.instance, function(iii,inst){
						if( $.trim(inst.identifier) !== "" ){
							if( exclude[inst.identifier] ) return;
							if( include[inst.identifier] ){
								var inc = parseInt(include[inst.identifier].addedon.replace(/[\-T\:]/g,"").split(",")[0]);
								var loc = parseInt(inst.addedon.replace(/[\-T\:]/g,"").split(",")[0]);
								if( inc > loc ) return;
							}
							inst.id = undefined;
							var imginst = local[inst.identifier] || inst;
							imginst.addedon = imginst.addedon || "2113-10-14T12:00:00";
							imginst.publishedon = e.createdon;
							include[imginst.identifier] = imginst;	
						}
					});
			});
		});
		for(var i in include){
			if( !include.hasOwnProperty(i) )continue;
			res.push(include[i]);
		}
		res.sort(function(a,b){
			var an = ($.trim(a.id)!=="")?parseInt(a.id):0;
			var bn = ($.trim(b.id)!=="")?parseInt(b.id):0;
			return bn-an;
		});
		return res;
	};
	this.getItemUniqueId = function(item){
		var id = item.identifier;
		if( this.options.strictmode === true ){
			return id;
		}
		var h = (item.checksum && item.checksum.val)?item.checksum.val():(id+'nochecksum');
		return h;
	};
	this.filterDataLoose = function(d){
		d = d  || [];
		var res = [];
		var include = {};
		var local = {};
		var uniqueId = (function(self){ return function(item){ return self.getItemUniqueId(item); }; })(this);
		
		//collect current image instances
		$.each(d, function(i,e){
			if( $.trim(e.published) === "true" ) return;
			e.image = e.image || [];
			e.image = $.isArray(e.image)?e.image:[e.image];
			$.each(e.image, function(ii, img) {
				img.instance = img.instance || [];
				img.instance = $.isArray(img.instance)?img.instance:[img.instance];
				$.each(img.instance, function(iii,inst){
					local[uniqueId(inst)] = true;
				});
			});
		});
		
		$.each(d, function(i,e){
			//set unpublished as excluded images
			if( $.trim(e.published) === "false" ) return;
			e.image = e.image || [];
			e.image = $.isArray(e.image)?e.image:[e.image];
			$.each(e.image, function(ii,img){
					img.instance = img.instance || [];
					img.instance = $.isArray(img.instance)?img.instance:[img.instance];
					$.each(img.instance, function(iii,inst){
						if( local[uniqueId(inst)] ) return;
						//in case of same checksums take recent one
						if( include[uniqueId(inst)] ){
								var inc = parseInt(include[uniqueId(inst)].addedon.replace(/[\-T\:]/g,"").split(",")[0]);
								var loc = parseInt(inst.addedon.replace(/[\-T\:]/g,"").split(",")[0]);
								if( inc > loc ) return;
							}
						if( $.trim(inst.identifier) !== "" ){
							inst.addedon = inst.addedon || "2113-10-14T12:00:00";
							inst.publishedon = e.createdon;
							include[uniqueId(inst)] = inst;	
						}
					});
			});
		});
		for(var i in include){
			if( !include.hasOwnProperty(i) )continue;
			res.push(include[i]);
		}
		res.sort(function(a,b){
			var an = ($.trim(a.id)!=="")?parseInt(a.id):0;
			var bn = ($.trim(b.id)!=="")?parseInt(b.id):0;
			return bn-an;
		});
		return res;
	};
	this.filterData = function(d){
		if( appdb.config.features.singleVMIPolicy === true ) {
			return this.filterDataLoose(d);
		} else {
			return this.filterDataStrict(d);
		}
	};
	this.renderLoading = function(loading, text){
		text = ($.trim(text)==="")?"...Loading data":text;
		loading = (typeof loading === "boolean")?loading:false;
		$(this.options.dom.dialog).children(".loader").remove();
		if( loading ){
			var loader = "<div class='loader'><div class='sheet'></div><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.options.dom.dialog).append(loader);
		}
		
	};
	this.markCurrentImages = function(){
		var wvd = appdb.vappliance.ui.CurrentVAManager.getContainer("workingversion").options.data;
		wvd.image = wvd.image || [];
		wvd.image = $.isArray(wvd.image)?wvd.image:[wvd.image];
		var currents = {};
		$.each(wvd.image, function(i,e){
			e.instance = e.instance || [];
			e.instance = $.isArray(e.instance)?e.instance:[e.instance];
			$.each(e.instance, function(ii,ee){
				currents[ee.identifier] = true;
			});
		});
		this.options.currentIndentifiers = currents;
		
	};
	this.doLoad = function(d){
		//TODO: fetch vappliance data like vamanager
	};
	this.load = function(d){
		this.show();
		this.renderLoading(true);
		var ld = this.getLocalData();
		if( ld === null ){
			this.render([]);
		}else{
			this.markCurrentImages(ld);
			this.render(ld);
		}
		this.renderLoading(false);
	};
	this.initContainer = function(){
		$(this.options.dom.dialog).empty();
		$(this.options.dom.dialog).append(this.options.dom.list);
		
	};
	this.init = function(){
		this.parent = this.options.parent;
		this.dom = this.options.container;
		this.initContainer();
	};
	this.init();
});


/*
 * All contextualization scripts of a vappliance are gathered here
 */
appdb.vappliance.components.ContextualizationScriptRegistry = appdb.ExtendClass(appdb.Component, "appdb.vappliance.components.ContextualizationScriptRegistry", function(o){
	this.options = {
		vappliance: o.vappliance || {},
		scripts: []
	};
	
	this.reset = function(){
		this.options.scripts = [];
	};
	
	this.exists = function(d){
		if( this.getById() ){
			return true;
		}
		return false;
	};
	
	this.getIndexOf = function(d){
		var url = $.trim( d.url || d || "" );
		var index = -1;
		if( url !== "") {
			$.each(this.options.scripts, function(i, e){
				if( index < 0 && $.trim(e.url) === url ){
					index = i;
				}
			});
		}
		return index;
	};
	this.getScript = function(d){
		var index = this.getIndexOf(d);
		
		if( index >= 0 ){
			return this.options.scripts[index];
		}
		
		return null;
	};
	this.getAllScripts = function(){
		return this.options.scripts;
	};
	this.isRegisteredWith = function(d, refid){
		var script = this.getScript(d);
		var isref = false;
		if( script ){
			$.each( script.refs, function(i, e){
				if( !isref && $.trim(e) === $.trim(refid) ){
					isref = true;
				}
			});
		}
		return isref;
	};
	this.registeredWithVMIs = function(d){
		var script = this.getScript(d);
		return script.refs || [];
	};
	this.register = function(d, refid){
		var script = this.getScript(d) || {};
		script.refs = script.refs || [];
		script.refs = $.isArray(script.refs)?script.refs:[script.refs];
		if( script.url ){
			if( $.trim(refid) && !this.isRegisteredWith( script, refid ) ){
				script.refs.push(refid);
			}
		} else {
			script = $.extend(true, {}, d);
			script.refs = [];
			if( refid ){
				script.refs.push(refid);
			}
			this.options.scripts.push(script);
		}
	};
	
	this.unregister = function(d, vmiinstance){
		var script = this.getScript(d);
		var index = this.getIndexOf( script );
		if( index > -1 ){
			if( this.options.scripts[index].refs && this.options.scripts[index].refs.length > 0 ){
				var vmiindex = $.inArray(vmiinstance, this.options.scripts[index].refs);
				if( vmiindex > -1 ){
					this.options.scripts[index].refs.splice(vmiindex,1);
				}
			}
			if( !this.options.scripts[index].refs || (this.options.scripts[index].refs && this.options.scripts[index].refs.length === 0) ){
				this.options.scripts.splice(index,1);	
			}
		}
	};
	
	this.clearByRefPrefix = function(prefix){
		if( !prefix ) {
			return;
		}
		
		var items = {};
		$.each(this.options.scripts, function(i, e){
			$.each( e.refs, function(ii, ee){
				if( ee.indexOf(prefix) === 0 ){
					if( !items[ee] ){
						items[ee] = [];
					}
					items[ee].push(e);
				}
			});
		});
		for(var i in items){
			if( items.hasOwnProperty(i) ){
				$.each(items[i], (function(self){
					return function(i, e){
						self.unregister(e, i);
					};
				})(this));
			}
		}
	};
	this.clearByRef = function(refid){
		if( !refid ){
			return;
		}
		var scripts = this.getByRef(refid);
		if( scripts.length > 0 ){
			$.each(scripts, (function(self){
				return function(i,e){
					self.unregister(e, refid);
				};
			})(this));
		}
	};
	this.getById = function(id){
		id = id << 0;
		return $.grep( this.options.scripts, function( e ){
			return ((e.id<<0) === id );
		});
	};
	
	this.getByName = function(name){
		name = $.trim(name);
		return $.grep( this.options.scripts, function( e ){
			return ($.trim(e.name) === name );
		});
	};
	
	this.getByFormatId = function(formatid){
		formatid = formatid << 0;
		return $.grep( this.options.scripts, function( e ){
			return (e.format && ($.trim(e.format.id)<<0) === formatid );
		});
	};
	
	this.getByUrl = function(url){
		url = $.trim(url);
		return $.grep( this.options.scripts, function( e ){
			return ($.trim(e.url) === url );
		});
	};
	
	this.getByRef = function( refid ){
		refid  = $.trim(refid);
		if( refid === "" ){
			return [];
		}
		return $.grep( this.options.scripts, function( e ){
			return ( $.inArray(refid, e.refs) > -1 );
		});
	};
	this.autoload = function(){
		var va = this.options.vappliance, self = this;
		if( va && va.instance ){
			$.each( va.instance, function(i, e){
				e.image = e.image || [];
				e.image = $.isArray(e.image)?e.image:[e.image];
				$.each(e.image, function(ii, ee){
					ee.instance = ee.instance || [];
					ee.instance = $.isArray(ee.instance)?ee.instance:[ee.instance];
					$.each(ee.instance, function(iii,eee){
						eee.contextscript = eee.contextscript || [];
						eee.contextscript = $.isArray(eee.contextscript)?eee.contextscript:[eee.contextscript];
						if( eee.contextscript.length > 0 ){
							$.each(eee.contextscript, function(c,script){
								self.register( script, eee.id );
							});
						}
					});
				});
			});
		}
	};
	
	this._init = function(){
		this.reset();
		this.autoload();
	};
	
	this._init();
	
});

appdb.vappliance.ui.views.ContextualizationScript = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.ContextualizationScript", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		vmiinstance: o.vmiinstance,
		index: o.index || -1
	};
	this.reset = function(){
		this.unsubscribeAll();
	};
	this.getData = function(){
		return this.options.data;
	};
	this.onUpdateUrl = function(cs){
		cs = cs || {};
		cs.url = decodeURIComponent(cs.url);
		var reg = appdb.vappliance.ui.CurrentVAManager.getContextualizationRegistry();
		if( reg ){
			var vmiid = this.options.vmiinstance.id;
			if( !vmiid && this.options.index ){
				vmiid = "idx_" + this.options.index;
			}
			//check if this is replace
			if( reg.getScript(this.options.data) !== null && reg.getByRef(vmiid).length > 0 ){
				reg.unregister(this.options.data, vmiid);
			}
			reg.register(cs, vmiid);
		}
		this.render(cs);
	};
	this.onSave = function(v){
		if( !!this._model ){
			if( this._model.unsubscribeAll ) {
				this._model.unsubscribeAll();
			}
			if( this._model.destroy ) {
				this._model.destroy();
			}
			if( this._model.getXhr ) {
				var x = this._model.getXhr();
				if( x && typeof x.abort === "function"){
					x.abort();
				}
			}
			this._model = null;
		}
		
		this._model = new appdb.model.VMIContextualizationScript();
		this._model.subscribe({ event: "beforeupdate", callback: function(v){
			if( this.options.editor ){
				this.options.editor.renderLoading(true, 'updating');
			}
		}, caller: this});
		this._model.subscribe({event: "update", callback: function(v){
			if( v.error ){
				if( this.options.editor ){
					this.options.editor.renderLoading(false);
					this.options.editor.renderError(v.error);
				}
			}else{
				this.onUpdateUrl(v.contextscript);
				if( this.options.editor ){
					this.options.editor.renderLoading(false);
					this.options.editor.closeDialog();
				}
			}
		}, caller: this});
		this._model.subscribe({event:"error", callback: function(v){
			if( this.options.editor ){
				this.options.editor.renderError(v);
			}
		}, caller: this});
		
		this._model.update({query:{
			act: "set",
			id: this.options.vmiinstance.id,
			url: encodeURIComponent(v.url),
			appid: appdb.pages.Application.currentId(),
			formatid: v.format.id
		}});
	};
	this.edit = function(v){
		if( this.options.editor ){
			  this.options.editor.unsubscribeAll();
			  this.options.editor.reset();
			  this.options.editor = null;
		  }
		  this.options.editor = appdb.views.ui.getEditor($(this.dom).siblings(".appdb-ui.vmi-contextscript-location"), { parent: this  });
		  if(this.options.editor){
			  this.options.editor.options.useformats = true;
			  this.options.editor.options.autoclose = false;
			  this.options.editor.subscribe({event: 'result', callback: function(v){
					if( v && $.isPlainObject(v) ){
						var cs = {
							id: this.options.data.id,
							relationid: this.options.data.relationid,
							url: v.url,
							checksum: {
								hashtype: "md5",
								val: (function(hash){ return function(){ return hash; };})(v.md5)
							},
							format: v.format,
							name: v.name,
							size: v.size
						};
						this.onSave(cs);
					}
			  }, caller: this });
			  if( $.isEmptyObject( this.getData() ) ){
				  this.options.data = {
					  url: ""
				  };
			  }
			  this.options.editor.setFormat(this.getData().format);
			  this.options.editor.bind(this.getData());
			  this.options.editor.showDialog();	
		  }		
	};
	this.remove = function(){
		if( this.options.remover ){
			this.options.remover.unsubscribeAll();
			this.options.remover.reset();
			this.options.remover = null;
		}
		this.options.remover = new appdb.vappliance.ui.views.ContextualizationScriptRemover({
			parent: this,
			data: this.options.data,
			vmiinstance: this.options.vmiinstance
		});
		this.options.remover.subscribe({ event: "removeurl", callback: function(v){
				var reg = appdb.vappliance.ui.CurrentVAManager.getContextualizationRegistry();
				if( reg ){
					var vmiid = this.options.vmiinstance.id;
					if( !vmiid && this.options.index ){
						vmiid = "idx_" + this.options.index;
					}
					reg.unregister(v, vmiid);
				}
				this.options.data = {};
				this.render();				
		}, caller: this});
		this.options.remover.render();
	};
	this.renderFormat = function(d){
		var dom = $("<span class='format'></span>");
		if( d.format && d.format.id ){
			$(dom).text(d.format.name);
		}else{
			$(dom).addClass("unknown").text("unknown");
		}
		return dom;
	};
	this.initPopups = function(dom){
		var cid = $(dom).children(".header:first");
		var isempty = $(dom).children(".value:first").hasClass("empty");
		var val = $(dom).children(".value:first").html();
		if( $(cid).length === 0 ) return;
		var header = $(cid).html(); 
		if(header && header.indexOf("<")>-1 && $(header).hasClass("popup") ){
			val = $(header).find(".value > span:first").html();
			header = $(header).find(".field > .title:first").html();
		}
		$(dom).find(".popup").remove();
		if( $(dom).hasClass("popupvalue") ){
			if( isempty ){
				val = "<div class='emptymessage'>" + $(this.dom).children(".emptymessage:first").html() + "</div>";
				$(dom).children(".emptymessage:first").remove();
			}
			$(cid).html("<div class='popup'><div class='field'><span class='title'>" + header + "</span><span class='arrow'>▼</span></div><div class='value'><span>" + val + "</span></div>");
			$(cid).off("click").on("click", function(ev){
				ev.preventDefault();
				$("body .vappliance .property.popupvalue > .header.selected").removeClass("selected");
				$("body .vappliance .propertypopupvalue > .header.selected").removeClass("selected");
				$(this).addClass("selected");
				return false;
			});
			$(cid).find("a").off("click").on("click", function(ev){
				ev.stopPropagation();
				return true;
			});
		}
	};
	this.renderPopup = function(d){
		var formatname = (d && d.format && d.format.val)?d.format.val():null;
		formatname = formatname || ((d && d.format && d.format.name)?d.format.name:'Cloud Init');
		var popup = $("<div class='property popupvalue compact'></div>");
		$(popup).append("<div class='header'>" + formatname + "</div>");
		$(popup).append("<div class='value'></div>");
		$(popup).find(".value").append(this.renderScriptDetails());
		return popup;
	};
	this.renderScriptDetails = function(){
		var d = this.options.data;
		var dom = $("<div class='contextsrciptdetails'></div>");
		var url = $("<div class='fieldvalue url'><span class='field'>Download:</span><span class='value'></span></div>");
		var hash = $("<div class='fieldvalue hash'><span class='field'></span><span class='value'></span></div>");
		var fsize = $("<div class='fieldvalue size'><span class='field'>Size:</span><span class='value'></span></div>");
		var permalink = $("<div class='value alert alert-info' >Use <a href='#' target='_blank' class='permascriptlink'>this link</a> to get the latest script for this image <img src='/images/question_mark.gif' alt='' class='info' /></div>");
		var permalinkinfo = $(this.dom).find('.info-message').clone();
		$(permalink).find('a').attr('href', appdb.config.endpoint.base+"store/vmi/"+this.parent.options.data.identifier+"/script");
		var urllink = $("<a href='#' title='Download script' target='_blank'></a>");
		var urlData = ('' + (d.url || ''));
		if (urlData.slice(0,4) === 'http' && window.location.protocol === 'https:' && urlData.slice(0,5) !== 'https') {
		    urlData = 'https:' + urlData.slice(5);
		}
		$(urllink).attr('href',urlData).text($.trim(d.name) || $.trim(urlData) );
		$(url).find(".value").append(urllink);
		$(hash).find(".field").text(d.checksum.hashtype + ":");
		$(hash).find(".value").text(d.checksum.val());
		$(fsize).find(".value").text(d.size + " bytes");
		$(dom).empty().append(url).append(hash).append(fsize);
		
		if( appdb.utils.isLocalDomainUrl(d.url) ){
			$(dom).append(permalink).append(permalinkinfo);
		}
		
		return dom;
	};
	this.renderUrl = function(d){
		var dom = $("<a href='#' target='_blank'></a>");
		var name = $("<span class='name'></span>");
		
		$(name).text(d.name);
		$(dom).append(name);
		$(dom).attr("title", d.description || d.name );
		$(dom).attr("href", d.url);
		return dom;
	};
	
	this.renderEdit = function(d){
		var edit = $("<button class='btn btn-compact btn-primary'><span>edit</span></button>");
		$(edit).off("click").on("click", (function(self, data){
			return function(ev){
				ev.preventDefault();
				self.edit(data);
				return false;
			};
		})(this, d));
		return edit;
	};
	this.renderRemove = function(d){
		var remove = $("<button class='btn btn-compact btn-danger'><span>remove</span></button>");
		$(remove).off("click").on("click", (function(self, data){
			return function(ev){
				ev.preventDefault();
				self.remove(data);
				return false;
			};
		})(this, d));
		
		return remove;
	};
	this.renderEmpty = function(){
		return  $('<div class="emptymessage">no information provided yet</div>');
	};
	this.render = function(d){
		this.options.data = d || this.options.data;
		var dom = $("<div class='contextualizationscript'></div>");
		if( this.options.data && !$.isEmptyObject(this.options.data) ){
			$(dom).append(this.renderPopup(this.options.data));
		} else {
			$(dom).append(this.renderEmpty());
		}
		if( appdb.vappliance.ui.CurrentVAManager.canEdit() ){
			$(dom).append( this.renderEdit(d) );
			if( this.options.data && $.trim(this.options.data.url) !== "" ){
				$(dom).append( this.renderRemove(d) );
			}
		}
		$(this.dom).empty().append(dom);	
		this.initPopups( $(this.dom).find(".popupvalue"));
		
		if( $(this.dom).find(".contextualizationscript img.info").length > 0 ){
			new dijit.Tooltip({
				connectId: [$(this.dom).find(".contextualizationscript img.info")[0]],
				label: $(this.dom).find(".contextualizationscript .info-message").html()
			});
		}
	};
	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
});
appdb.vappliance.ui.views.ContextualizationScriptEditorList = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.ContextualizationScriptList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		editor: null,
		url: $.trim(o.url),
		dom: {
			select: $("<select></select>")
		},
		selectedValue: null,
		isValid: false
	};
	this.resetEditor = function(){
		if( this.options.editor ){
			this.options.editor.destroyRecursive(false);
			this.options.editor = null;
		}
		if( $(this.options.dom.select).length > 0 ){
			$(this.options.dom.select).empty().remove();
		}
		this.options.dom.select = $("<select></select>");
	};
	this.reset = function(){
		this.unsubscribeAll();
		this.resetEditor();
		$(this.dom).empty();
	};
	
	this.addItem = function(d){
		var dom = $("<option></option>");
		if( !d || !$.isPlainObject(d) ) return null;
		
		$(dom).attr("value",d.url).text(d.url);
		if( d.url === this.options.url ){
			$(dom).prop("selected", true);
		}
		return dom;
	};
	this.isValidUrl = function(u){
		var rx =  /(ftps|ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i;
		return rx.test(u);
	};
	this.onValueChange = function(){
		var v = this.options.editor.get("displayedValue");
		var s = this.options.registry.getByUrl(v);
		if( s.length > 0 ){
			this.options.selectedValue = s[0];
			this.publish({ event: "change", value: {error: false, isNew: false, value: this.options.selectedValue } } );
		} else if( $.trim(v)!=="" ) {
			if( this.isValidUrl(v) ){
				this.publish({ event: "change", value: {error: false, isNew: true, value: v } } );
			}else{
				this.options.selectedValue = null;
				this.publish({ event: "change", value: {error: "invalidvalue", isNew:false, value: v } } );
			}
		} else {
			this.options.selectedValue = null;
			this.publish({ event: "change", value: {error: "invalidvalue", isNew:false, value: "" } } );
		}
	};
	this.getCurrentValue = function(){
		return this.options.editor.get("displayedValue");
	};
	this.render = function(d){
		this.reset();
		this.options.data = d || this.options.data;
		this.options.data = $.isArray( this.options.data )?this.options.data:[this.options.data];
		$.each(this.options.data, (function(self){
			return function(i, e){
				var opt = self.addItem(e);
				if( opt !== null ) {
					$(self.options.dom.select).append(opt);
				}
			};
		})(this));
		$(this.dom).empty().append(this.options.dom.select);
		
		this.options.editor = new dijit.form.ComboBox({
				autoComplete: true,
				value: this.options.url,
				placeHolder: "Please provide the url here",
				style: "width:100%;",
				onChange: (function(self){
					return function(v){
						self.onValueChange();
					};
				})(this),
				onKeyUp: (function(self){
					return function(v){
						self.onValueChange();
					};
				})(this),
				onMouseUp: (function(self){
					return function(v){
						self.onValueChange();
					};
				})(this)
			}, $(this.options.dom.select).get(0));	
			
			setTimeout( (function(self){
				return function(){
					self.onValueChange();
				};
			})(this),10);
	};
	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.data = this.data || [];
		this.data = $.isArray(this.data)?this.data:[this.data];
		this.options.registry = appdb.vappliance.ui.CurrentVAManager.getContextualizationRegistry();
		this.reset();
	};
	
	this._init();
});

appdb.vappliance.ui.views.ContextualizationScriptEditor = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.ContextualizationScriptEditor", function(o){
	this.options = {
		container: $("<div class='contextalizationeditor'></div>"),
		parent: o.parent || null,
		data: o.data || {},
		vmiinstance: o.vmiinstance,
		selectedUrl: null,
		selectedFormat: null,
		dom: {
			header: $("<div class='header'></div>"),
			format: $("<div class='contextformat fieldvalue'></div>"),
			list: $("<div class='contextscriptlist'></div>"),
			script: $("<div class='contextscriptdata'></div>"),
			validator: $("<div class='validator'></div>"),
			actions: $("<div class='actions'></div>")
		}
	};
	this.resetList = function(){
		if( this.options.scriptlist ){
			this.options.scriptlist.unsubscribeAll(this);
			this.options.scriptlist.reset();
			this.options.scriptlist = null;
		}
		$(this.options.dom.list).empty();
	};
	this.reset = function(){
		this.unsubscribeAll();
		this.resetDialog();
		$(this.dom).empty();
		this._initContainer();
	};
	this.resetDialog = function(){
		if( appdb.vappliance.ui.views.ContextualizationScriptEditor.dialog ){
			appdb.vappliance.ui.views.ContextualizationScriptEditor.dialog.hide();
			appdb.vappliance.ui.views.ContextualizationScriptEditor.dialog.destroyRecursive(false);
			appdb.vappliance.ui.views.ContextualizationScriptEditor.dialog = null;
		}
	};
	this.getParentUrl = function(){
		if( typeof this.options.data === "string" ){
			return $.trim(this.options.data);
		}
		return $.trim(this.options.data.url);
	};
	this.show = function(){
		this.resetDialog();
		appdb.vappliance.ui.views.ContextualizationScriptEditor.dialog = new dijit.Dialog({
			title: "Contextualization script editor",
			content: $(this.dom)[0],
			style: o.css || "max-width:830px;width:830px;max-height:300px;height:300px;",
			onCancel: (function(self){
				return function(){
					self.onCancel();
				};
			})(this)
		});
		appdb.vappliance.ui.views.ContextualizationScriptEditor.dialog.show();
	};
	this.close = function(){
		this.resetDialog();
	};
	this.onCancel = function(){
		this.close();
	};
	this.onError = function(err){
		this.renderValidationError(true, err);
	};
	this.onSave = function(){
		if( this._model !== null ){
			if(this._model.unsubscribeAll) this._model.unsubscribeAll();
			if(this._model.destroy) this._model.destroy();
			if( this._model.getXhr) {
				var x = this._model.getXhr();
				if( x && typeof x.abort === "function"){
					x.abort();
				}
			}
			this._model = null;
		}
		this._model = new appdb.model.VMIContextualizationScript();
		this._model.subscribe({ event: "beforeupdate", callback: function(v){
			this.renderLoading(true,"...calculating");
		}, caller: this}).subscribe({event: "update", callback: function(v){
			this.renderLoading(false);
			if( v.error ){
				this.onError(v.error);
			}else{
				this.publish({event: "updateurl", value: v});
				this.close();
			}
		}, caller: this}).subscribe({event:"error", callback: function(v){
			
		}, caller: this});
		var selurl = "";
		if( typeof this.options.selectedUrl !== "undefined" ){
			if( typeof this.options.selectedUrl === "string"){
				selurl = this.options.selectedUrl;
			}else{
				selurl = this.options.selectedUrl.url;
			}
		}
		this._model.update({query:{
			act: "set",
			id: this.options.vmiinstance.id,
			url: encodeURIComponent(selurl),
			appid: appdb.pages.Application.currentId(),
			formatid: this.options.selectedFormat
		}});
		
	};
	this.onClose = function(){
		this.close();
	};
	this.renderScriptData = function(){
		 var d = this.options.selectedUrl;
		 $(this.options.dom.script).empty().removeClass("hasdata");
		 
		 if( d && typeof d !== "string" ){
			this.options.selectedFormat = ((d.format)?d.format.id:1);
			var url = $("<div class='fieldvalue url'><span class='field'>Url:</span><span class='value'></span></div>");
			var hash = $("<div class='fieldvalue hash'><span class='field'></span><span class='value'></span></div>");
			var fsize = $("<div class='fieldvalue size'><span class='field'>Size:</span><span class='value'></span></div>");
			$(url).find(".value").text(d.url);
			$(hash).find(".field").text(d.checksum.hashtype + ":");
			$(hash).find(".value").text(d.checksum.val());
			$(fsize).find(".value").text(d.size + " bytes");
			$(this.options.dom.script).empty().addClass("hasdata").append(url).append(hash).append(fsize);
			
			$(this.options.dom.format).find('option').prop('selected', false);
			$(this.options.dom.format).find('option[value="'+ this.options.selectedFormat  +'"]').prop('selected', true);
			
		 }
	};
	this.renderValidationError = function(enable, err){
		enable = (typeof enable === "boolean")?enable:false;
		err = err || "Unknown error occured";
		$(this.dom).find(".validator").empty();
		if( enable ){
			var message = $("<div class='message icontext'><img src='/images/vappliance/redwarning.png' alt=''/><span></span></div>");
			$(message).find("span").text(err);
			$(this.dom).find(".validator").append(message);
		}
	};
	this.renderActions = function(){
		$(this.options.dom.actions).empty();
		var save = $("<button class='btn btn-primary btn-compact save'>Save</button>");
		var cancel = $("<button class='btn btn-danger btn-compact cancel'>Cancel</button>");
		$(this.options.dom.actions).empty().append(save).append(cancel);
		
		$(save).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.onSave();
				return false;
			};
		})(this));
		
		$(cancel).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.onClose();
				return false;
			};
		})(this));
	};
	this.onValueChange = function(v){
		this.renderValidationError(false);
		if( v && !v.error && v.value !== this.getParentUrl() ){
			if( v.value !== this.getParentUrl() && $.trim(v.value) !== ""){
				$(this.options.dom.actions).find(".save").addClass("btn-primary").removeClass("disabled").prop("disabled", false);
				this.options.selectedUrl = v.value;
			}
			this.renderScriptData();
			return;
		}else if( v && v.error ){
			if( v.error === 'invalidvalue'){
				v.error = "The value you provided is not a valid URL";
				this.renderValidationError(true, v.error);
			}
		}
		$(this.options.dom.actions).find(".save").removeClass("btn-primary").addClass("btn-disabled").prop("disabled", true);
		this.options.selectedUrl = null;
		this.renderScriptData();
	};
	this.renderRegisteredScriptList = function(){
		var registry = appdb.vappliance.ui.CurrentVAManager.getContextualizationRegistry();
		this.resetList();
		this.options.scriptlist = new appdb.vappliance.ui.views.ContextualizationScriptEditorList({
			container: $(this.options.dom.list),
			parent: this,
			data: registry.getAllScripts(),
			url: this.getParentUrl()
		});
		this.options.scriptlist.subscribe({ event: "change", callback: function(v){
				this.onValueChange(v);
		}, caller: this});
		this.options.scriptlist.render(registry.getAllScripts());
	};
	this.renderHeader = function(){
		var text = "Please provide the url of the contextualization script you wish to link with the image";
		var registry = appdb.vappliance.ui.CurrentVAManager.getContextualizationRegistry();
		if( registry && registry.getAllScripts && registry.getAllScripts().length > 0 ){
			text += " or select one from the list, already registered for this virtual appliance."
		}else {
			text += ".";
		}
		$(this.options.dom.header).text(text);
	};
	this.renderFormat = function(){
		var select = $('<select></select>');
		$(this.options.dom.format).empty().append("<div class='field'>Format:</span>");
		$(this.options.dom.format).append("<div class='value'></div>");
		var d = appdb.model.StaticList.ContextFormats || [];
		$.each(d, function(i,e){
			$(select).append('<option value="' + e.id + '">' + e.name + '</option>');
		});
		$(this.options.dom.format).find('.value').append(select);
		$(select).off('change').on('change', (function(self){
			return function(ev){
				self.options.selectedFormat = $(select).val();
			};
		})(this));
	};
	this.renderLoading = function (enable, text ){
		enable = (typeof enable === "boolean")?enable:false;
		$(this.dom).find(".actionloader").remove();
		if( enable ){
			text = text || "saving";
			var loader = "<div class='actionloader'><div class='shader'></div><div class='message icontext'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).append(loader);
		}
	};
	this.render = function(){
		this.renderHeader();
		this.renderFormat();
		this.renderRegisteredScriptList();
		this.renderActions();
		this.show();
	};
	this._initContainer = function(){
		$(this.options.dom.header).empty();
		$(this.options.dom.list).empty();
		$(this.options.dom.script).empty();
		$(this.options.dom.actions).empty();
		$(this.options.dom.validator).empty();
		$(this.options.dom.format).empty();
		$(this.dom).append(this.options.dom.header);
		$(this.dom).append(this.options.dom.format);
		$(this.dom).append(this.options.dom.list);
		$(this.dom).append(this.options.dom.script);
		$(this.dom).append(this.options.dom.validator);
		$(this.dom).append(this.options.dom.actions);		
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._model = null;
		this._initContainer();
	};
	this._init();
},{
	dialog: null
});

appdb.vappliance.ui.views.ContextualizationScriptRemover = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.ContextualizationScriptRemover", function(o){
	this.options = {
		container: $("<div class='contextalizationeditor'></div>"),
		parent: o.parent || null,
		data: o.data || {},
		vmiinstance: o.vmiinstance,
		dom: {
			header: $("<div class='header'></div>"),
			main: $("<div class='main'></div>"),
			actions: $("<div class='actions'></div>"),
			validator: $("<div class='validator'></div>")
		}
	};
	this.resetDialog = function(){
		if( appdb.vappliance.ui.views.ContextualizationScriptRemover.dialog ){
			appdb.vappliance.ui.views.ContextualizationScriptRemover.dialog.hide();
			appdb.vappliance.ui.views.ContextualizationScriptRemover.dialog.destroyRecursive(false);
			appdb.vappliance.ui.views.ContextualizationScriptRemover.dialog = null;
		}
	};
	this.reset = function(){
		this.unsubscribeAll();
		this.resetDialog();
		$(this.dom).empty();
		this._initContainer();
	};
	this.show = function(){
		this.resetDialog();
		appdb.vappliance.ui.views.ContextualizationScriptRemover.dialog = new dijit.Dialog({
			title: "Remove contextualization script",
			content: $(this.dom)[0],
			style: o.css || "max-width:550px;width:550px;max-height:250px;height:250px;",
			onCancel: (function(self){
				return function(){
					self.close();
				};
			})(this)
		});
		appdb.vappliance.ui.views.ContextualizationScriptRemover.dialog.show();
	};
	this.close = function(){
		this.resetDialog();
	};
	this.onError = function(err){
		this.renderError(true, err);
	};
	this.onRemove = function(){
		if( this._model !== null ){
			if(this._model.unsubscribeAll) this._model.unsubscribeAll();
			if(this._model.destroy) this._model.destroy();
			if( this._model.getXhr) {
				var x = this._model.getXhr();
				if( x && typeof x.abort === "function"){
					x.abort();
				}
			}
			this._model = null;
		}
		this._model = new appdb.model.VMIContextualizationScript();
		this._model.subscribe({ event: "beforeremove", callback: function(v){
			this.renderLoading(true,"...removing");
		}, caller: this}).subscribe({event: "remove", callback: function(v){
			this.renderLoading(false);
			if( v.error ){
				this.onError(v.error);
			}else{
				this.publish({event: "removeurl", value: this.options.data });
				this.close();
			}
		}, caller: this}).subscribe({event:"error", callback: function(v){
			
		}, caller: this});
		
		this._model.remove({
			id: this.options.vmiinstance.id,
			url: encodeURIComponent($.trim(this.options.data.url)),
			appid: appdb.pages.Application.currentId()
		});	
	};
	this.renderLoading = function (enable, text ){
		enable = (typeof enable === "boolean")?enable:false;
		$(this.dom).find(".actionloader").remove();
		if( enable ){
			text = text || "saving";
			var loader = "<div class='actionloader'><div class='shader'></div><div class='message icontext'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>" + text + "</span></div></div>";
			$(this.dom).append(loader);
		}
	};
	this.renderHeader = function(){
		$(this.options.dom.header).text("Are you sure you want to remove the following contextualization script?");
	};
	this.renderMain = function(){
		var d = this.options.data;
		var format = $("<div class='fieldvalue url'><span class='field'>Format:</span><span class='value'></span></div>");
		var url = $("<div class='fieldvalue url'><span class='field'>Url:</span><span class='value'></span></div>");
		var hash = $("<div class='fieldvalue hash'><span class='field'></span><span class='value'></span></div>");
		var fsize = $("<div class='fieldvalue size'><span class='field'>Size:</span><span class='value'></span></div>");
		var formatname = (d && d.format && d.format.val)?d.format.val():null;
		formatname = formatname || ((d && d.format && d.format.name)?d.format.name:'Cloud Init');
		$(format).find('.value').text( formatname );
		$(url).find(".value").text(d.url);
		$(hash).find(".field").text(d.checksum.hashtype + ":");
		$(hash).find(".value").text(d.checksum.val());
		$(fsize).find(".value").text(d.size + " bytes");
		$(this.options.dom.main).empty().append(url).append(format).append(hash).append(fsize);
	};
	this.renderActions = function(){
		$(this.options.dom.actions).empty();
		var remove = $("<button class='btn btn-danger btn-compact remove'>Remove</button>");
		var cancel = $("<button class='btn btn-primary btn-compact cancel'>Cancel</button>");
		$(this.options.dom.actions).empty().append(remove).append(cancel);
		
		$(remove).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.onRemove();
				return false;
			};
		})(this));

		$(cancel).off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.close();
				return false;
			};
		})(this));
	};
	this.render = function(){
		this.renderHeader();
		this.renderMain();
		this.renderActions();
		this.show();
	};
	this.renderError = function(enable, err){
		enable = (typeof enable === "boolean")?enable:false;
		err = err || "Unknown error occured";
		$(this.dom).find(".validator").empty();
		if( enable ){
			var message = $("<div class='message icontext'><img src='/images/vappliance/redwarning.png' alt=''/><span></span></div>");
			$(message).find("span").text(err);
			$(this.dom).find(".validator").append(message);
		}
	};
	this._initContainer = function(){
		$(this.options.dom.header).empty();
		$(this.options.dom.main).empty();
		$(this.options.dom.validator).empty();
		$(this.options.dom.actions).empty();
		$(this.dom).append(this.options.dom.header);
		$(this.dom).append(this.options.dom.main);
		$(this.dom).append(this.options.dom.validator);
		$(this.dom).append(this.options.dom.actions);
	};
	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._model = null;
		this._initContainer();
	};
	this._init();
},{
	dialog: null
});

appdb.vappliance.ui.views.PortRangeListItem = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.PortRangeListItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		canRemove: (typeof o.canRemove === 'boolean') ? o.canRemove : true,
		editor: {
			from: null,
			to: null,
			error: null
		}
	};

	this.enable = function(enable) {
	    enable = (typeof enable === 'boolean') ? enable : true;
	    this.options.editor.from.set('disabled', !enable);
	    this.options.editor.to.set('disabled', !enable);
	};

	this.canRemove = function(removable) {
	    if (typeof removable !== 'undefined') {
		this.options.canRemove = ((typeof removable === 'boolean') ? removable : true);
		$(this.dom).find('.action.remove').toggleClass('hidden', !removable);
	    }
	    return this.options.canRemove;
	};

	this.getData = function() {
		var d = Object.assign({}, this.options.data);

		if ($.trim(d.to) === '') {
		    d.to = '' + d.from;
		}

		if ($.trim(d.from) && $.trim(d.to)) {
		    return $.trim('' + d.from + ':' + d.to);
		}

		return '';
	};

	this.validate = function() {
		var from = $.trim('' + this.options.data.from);
		var to = $.trim('' + this.options.data.to);
		var intTest = /^\d+$/;
		var typeErrMsg = 'Port values must be positive integers between 1 and 65535';
		var rangeERRmSG = 'Second port value must be equal or less than first port value';

		if (!from && !to) {
		    return typeErrMsg;//'No ports given';
		}

		if ((from && !intTest.test(from)) || (to && !intTest.test(to))) {
		    return typeErrMsg;
		}

		var fromNum = parseInt(from || 0);
		var toNum = parseInt(to || 0);

		if (from && to && fromNum > toNum) {
		    return rangeERRmSG;
		}

		if (fromNum > 65535 || toNum > 65535) {
		    return typeErrMsg;
		}

		if ((from && fromNum <=0) || (to && toNum <= 0)) {
		    return typeErrMsg;
		}

		if (this.parent.portRangeExists(this)) {
		    return 'Port range is already defined for this rule';
		}

		return true;
	};

	this.isValid = function() {
		$(this.dom).find('.portrangeitem .validationmessage.tooltip').remove();
		var isValid = this.validate();
		if (isValid !== true) {
		    $(this.dom).find('.portrangeitem').append('<div class="validationmessage tooltip"><img src="/images/vappliance/warning.png" alt=""><div class="validationerrormessage"><span>'+isValid+'</span></div><div class="arrow"></div></div>')
		}
		return (isValid===true);
	};

	this.onValueChange = function() {
		this.options.data.from = $.trim(this.options.editor.from.get('displayedValue'));
		this.options.data.to = $.trim(this.options.editor.to.get('displayedValue'));
		this.parent.onValueChange(this);
	};
	this.setFocused = function(isFocused) {
	    isFocused = (typeof isFocused === 'boolean') ? isFocused : true;
	    $(this.dom).find('.portrangeitem').toggleClass('focused', isFocused);
	};
	this.render = function() {
		var dom = $('<div class="portrangeitem"><div class="from"></div><div class="sep">:</div><div class="to"></div><a class="action remove btn btn-danger btn-compact hidden">X</a></div>');
		$(this.dom).empty();
		$(this.dom).append(dom);

		this.options.editor.from = new dijit.form.ValidationTextBox({
			value: this.options.data.from,
			placeHolder: 'from',
			onFocus: (function(self){
				return function(){
					self.setFocused(true);
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.setFocused(false);
					self.onValueChange();
				};
			})(this),
			onChange : (function(self){
				return function(v){
					self.onValueChange(v);
				};
			})(this),
			onKeyUp: (function(self){
				return function(v){
					self.onValueChange();
				};
			})(this),
			onMouseUp: (function(self){
				return function(v){
					self.onValueChange();
				};
			})(this)
		}, $(dom).find('.from')[0]);
		this.options.editor.to = new dijit.form.ValidationTextBox({
			value: this.options.data.to,
			placeHolder: 'to',
			onFocus: (function(self){
				return function(){
					self.setFocused(true);
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.setFocused(false);
					self.onValueChange();
				};
			})(this),
			onChange : (function(self){
				return function(v){
					self.onValueChange(v);
				};
			})(this),
			onKeyUp: (function(self){
				return function(v){
					self.onValueChange();
				};
			})(this),
			onMouseUp: (function(self){
				return function(v){
					self.onValueChange();
				};
			})(this)
		}, $(dom).find('.to')[0]);

		$(dom).find('.action.remove').off('click').on('click', function(ev) {
		    ev.preventDefault();
		    ev.stopPropagation();
		    this.parent.removeItem(this);
		    return false;
		}.bind(this));

		setTimeout(function() {
		    this.onValueChange();
		}.bind(this),1);
	};

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		if (typeof this.options.data === 'string') {
			var tmp = this.options.data.split(':');
			this.options.data = {
				from: tmp[0],
				to: (tmp.length > 1) ? tmp[1] : tmp[0]
			};
		}
	};
	this._init();
});
appdb.vappliance.ui.views.PortRangeList = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.PortRangeList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		items: [],
		isValid: true,
		dom: {
		    list: $('<ul></ul>'),
		    footer: $('<div class="footer"></div>')
		}
	};

	this.enable = function(enable) {
	    enable = (typeof enable === 'boolean') ? enable : true;
	    $.each(this.options.items, function(i,v) {
		v.enable(enable);
	    });
	    $(this.dom).toggleClass('disabled', !enable);
	};

	this.getData = function() {
	    var ranges = [];

	    $.each(this.options.items, function(i, v) {
		    var d = v.getData();
		    if (d) {
			    ranges.push(v.getData());
		    }
	    });

	    if (ranges.length === 0) {
		    return '';
	    }

	    return ranges.join(';');
	};

	this.validate = function() {
	    this.options.isValid = this.isValid();
	};

	this.portRangeExists = function(item) {
	    var exists = false;

	    $.each(this.options.items, function(i, v) {
		exists = ((exists === false && v !== item && v.getData() === item.getData()) ? true : false);
	    });

	    return exists;
	};

	this.isValid = function() {
		var isValid = true;

		$.each(this.options.items, function(i, v) {
		    var itemIsValid = (v.isValid) ? v.isValid() : true;
		    isValid = (isValid === true && v.isValid) ? itemIsValid : isValid;
		    $(v.dom).find('.portrangeitem').toggleClass('isinvalid', (itemIsValid !== true));
		});

		return isValid;
	};

	this.refreshUI = function() {
	    if (this.options.items.length === 0) {
		this.addItem();
	    }

	    if (this.options.items.length === 1) {
		this.options.items[0].canRemove(false);
	    } else {
		$.each(this.options.items, function(i, v) {
			v.canRemove(true);
		});
	    }

	    $(this.dom).toggleClass('invalid', !this.options.isValid);
	};

	this.onValueChange = function() {
		this.validate();
		this.refreshUI();
		this.parent.onValueChange(this);
	};

	this.addItem = function(d, index, removable) {
		d = d || "";
		var li = $('<li></li>');
		index = (index >= 0) ? index : $(this.options.dom.list).children('li').length;
		$(this.options.dom.list).append(li);

		var item = new appdb.vappliance.ui.views.PortRangeListItem({
			container: li,
			parent: this,
			index: index,
			canRemove: removable,
			data: d
		});

		item.render();

		this.options.items.push(item);
		this.onValueChange();
		return li;
	};

	this.removeItem = function(item) {
	   var foundIndex = -1;

	   $.each(this.options.items, function(i, v) {
	       foundIndex = (foundIndex === -1 && item === v) ? i : foundIndex;
	   });

	   if (foundIndex > -1) {
	       this.options.items[foundIndex].reset();
	       $(this.options.items[foundIndex].dom).remove();
	       this.options.items[foundIndex] = null;
	       this.options.items.splice(foundIndex, 1);
	       this.onValueChange();
	   }
	};

	this.renderFooter = function() {
		var actions = $('<div class="actions"></div>');
		var addNew = $('<a class="action add" title="Add new port range">add new port range</a>');
		var errormessage = $('<div class="errormessage hidden"></div>');
		$(actions).append(addNew);
		$(this.options.dom.footer).empty();
		$(this.options.dom.footer).append(errormessage).append(actions);
		$(addNew).off('click').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			if ($(this.dom).hasClass('invalid') === false) {
				this.addItem();
			}
			return false;
		}.bind(this));
	};

	this.render = function() {
		this.reset();
		this.options.data = $.isArray(this.options.data) ? this.options.data : [this.options.data];
		var hasMany = (this.options.data.length > 1);
		$(this.dom).empty();
		$(this.dom).append('<span class="header">Ports:</span>');
		$(this.options.dom.list).empty();
		$(this.dom).append(this.options.dom.list);
		$(this.dom).append(this.options.dom.footer);

		$.each(this.options.data, function(i, v) {
			this.addItem(v, i, (hasMany && i > 0));
		}.bind(this));

		this.renderFooter();
		this.refreshUI();
	};

	this.initContainer = function() {

	};

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
});
appdb.vappliance.ui.views.VMITrafficRulesItem = appdb.ExtendClass(appdb.vappliance.ui.views.DataPropertyContainer, "appdb.vappliance.ui.views.VMITrafficRulesItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		isEditable: ( (typeof o.editable === "boolean")?o.editable:false ),
		dom: {
		   direction: null,
		   protocols: null,
		   portranges: null
		},
		editor: {
		    direction: null,
		    protocols: null,
		    portranges: null
		}
	};

	this.validate = function() {

	};

	this.isValid = function() {
		if (this.options.data.protocols !== 'ICMP') {
			return this.options.editor.portranges.isValid();
		}
		return true;
	};

	this.onValueChange = function(item) {
		this.validate();
		var d = this.getData();
		this.options.editor.portranges.enable((d.protocols !== 'ICMP'));
		this.parent.onValueChange(this);
	};

	this.getData = function(){
		var d =  {
			direction: this.options.editor.direction.get('value'),
			protocols: this.options.editor.protocols.get('value'),
			ip_range: '0.0.0.0/0',
			port_range: this.options.editor.portranges.getData()
		};

		if (d.protocols === 'ICMP') {
		    d.port_range = '';
		} else if ($.trim(d.port_range) === '') {
		    d = {};
		}

		return d;
	};
	this.initActions = function(){
		$(this.dom).children(".actions").children(".action.remove").off("click").on("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.parent.removeItem(self);
				return false;
			};
		})(this));
	};
	this.renderDirections = function() {
		var dom = $("<div></div>");
		var directions = [];
		var selectedValue = $.trim(this.options.data.direction).toLowerCase();

		$(this.dom).append(dom);

		$.each([{id: 'inbound', name: 'Incoming'}, {id: 'outbound', name: 'Outgoing'}], function(i, v) {
			directions.push({
				label: "<span>" + v.name + "</span>",
				value: v.id,
				selected: ( (selectedValue !== '')?(v.id === selectedValue):(i === 0) )
			});
		});

		this.options.editor.direction = new dijit.form.Select({
			options: directions,
			onFocus: (function(self){
				return function(){
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.onValueChange();
				};
			})(this),
			onChange: (function(self){
				return function(v){
					var res = $.trim('' + v.id);
					self.options.data.direction = res;
					self.onValueChange();
				};
			})(this)
		}, $(dom)[0]);
	};

	this.renderProtocols = function() {
		var dom = $("<div></div>");
		var protocols = [];
		var selectedValue = this.options.data.protocols.split(' ');

		if (selectedValue.length === 0) {
			selectedValue = '';
		} else {
			selectedValue = selectedValue[0];
		}

		$(this.dom).append(dom);

		$.each(appdb.model.StaticList.VMNetworkProtocols, function(i, v) {
			protocols.push({
				label: "<span>" + v + "</span>",
				value: v,
				selected: ( (selectedValue !== '')?(v === selectedValue):(i === 0) )
			});
		});

		this.options.editor.protocols = new dijit.form.Select({
			options: protocols,
			onFocus: (function(self){
				return function(){
					self.onValueChange();
				};
			})(this),
			onBlur:  (function(self){
				return function(){
					self.onValueChange();
				};
			})(this),
			onChange: (function(self){
				return function(v){
					var res = $.trim('' + v);
					self.options.data.protocols = res;
					self.onValueChange();
				};
			})(this)
		}, $(dom)[0]);
 	};

	this.renderPortRanges = function() {
		var dom = $('<div class="fielditem portrange"></div>');
		var portRanges = this.options.data.port_range || [];
		if(typeof this.options.data.port_range === 'string') {
		    portRanges = $.trim(this.options.data.port_range).split(';');
		}
		$(this.dom).append(dom);
		this.options.editor.portranges = new appdb.vappliance.ui.views.PortRangeList({
			container: dom,
			parent: this,
			data: portRanges
		});
		this.options.editor.portranges.render();
	};

	this.renderActions = function() {
		var dom = $('<div class="fielditem actions"></div>');
		var remove = $('<a class="action remove btn btn-danger btn-compact">X</div>');
		$(dom).append(remove);
		$(this.dom).append(dom);
	};

	this.render = function(d){
		$(this.dom).data("inst", this);
		$(this.dom).empty();
		this.renderActions();
		this.renderDirections();
		this.renderProtocols();
		this.renderPortRanges();
		this.initActions();
		this.validate();
		var d = this.getData();
		this.options.editor.portranges.enable((d.protocols !== 'ICMP'));
		setTimeout(function(){
			appdb.vappliance.ui.CurrentVAVersionValidatorRegister.check();
		},10);
	};

	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
});
appdb.vappliance.ui.views.VMITrafficRulesEditor = appdb.ExtendClass(appdb.View, "appdb.vappliance.ui.views.VMITrafficRulesEditor", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent,
		isEditable: ( (typeof o.editable === "boolean")?o.editable:false ),
		items: [],
		itemTemplate: o.itemTemplate,
		direction: o.direction || 'inbound',
		data: o.data || [],
		dom: {
			emptyMessage: $('<div class="emptymessage">No network traffic rules defined.</div>'),
			list: $('<ul class="ruleslist"></ul>'),
			footer: $('<div class="footer"><div class="warning icontext"><img src="/images/vappliance/warning.png" /><span>Some of the provided rules are invalid.</span></div><div class="actions inline-block"><a class="action add btn btn-primary btn-compact">add new rule</a></div></div>')
		}
	};
	this.harvestData = function() {
		var data = [];
		$.each(this.options.items, function(i, v) {
			var d = v.getData();
			if (!!d.protocols) {
				data.push(d);
			}
		});

		this.options.data = data;
		return this.options.data;
	};

	this.getData = function() {
		return this.options.data;
	};

	this.hasChanges = function() {
		var hasChanges = false;

		$.each(this.options.items, function(i,v) {
		       hasChanges = (!hasChanges && v.hasChanges && v.hasChanges());
		});

		return hasChanges;
	};

	this.onValueChange = function(item) {
		var isValid = this.validate();
		$(this.dom).find('.action.add').each(function(i, el) {
			$(el).toggleClass('disabled', !isValid);
		});
		$(this.dom).toggleClass('invalid', !isValid);
		this.harvestData();
		this.parent.onPropertyValueChange({property: {options: { dataPath: 'trafficRules'}}});
		this.refreshUI();
	};
	this.isValid = function() {
	    var isValid = true;

	    $.each(this.options.items, function(i,v) {
		   isValid = (!!isValid && v.isValid && v.isValid());
	    });

	    return isValid;
	};

	this.validate = function() {
	    return this.isValid();
	};
	this.removeItem = function(item) {
		var foundIndex = -1;

		$.each(this.options.items, function(i, v) {
			foundIndex = (foundIndex === -1 && v === item) ? i : foundIndex;
		});

		if (foundIndex > -1) {
			item.reset();
			$(item.dom).remove();
			item = null;
			this.options.items.splice(foundIndex, 1);
			this.onValueChange();
		}
	};

	this.addItem = function(d, index) {
		if (!d) {
			d = { direction: o.direction || '', protocols: 'TCP', ip_range: '', port_range: '' };
		}
		var li = $('<li></li>');
		$(this.options.dom.list).append(li);
		var item = new appdb.vappliance.ui.views.VMITrafficRulesItem({
			container: li,
			data: d,
			parent: this,
			index: index
		});
		this.options.items.push(item);
		item.render();
		this.onValueChange();
	};

	this.refreshUI = function() {
		var isEmpty = (this.options.items.length === 0);
		this.renderFooter(isEmpty);
		if (isEmpty) {
			$(this.dom).find('.emptymessage').removeClass('hidden');
		} else {
			$(this.dom).find('.emptymessage').addClass('hidden');
		}
	};
	this.renderActions = function(container) {};
	this.renderFooter = function(isEmpty) {
	    isEmpty = (typeof isEmpty === 'boolean') ? isEmpty : false;
	    $(this.options.dom.footer).children('.actions').find('.action.add').off('click').on('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			if ($(ev.target).hasClass('disabled') === false) {
				this.addItem();
				this.refreshUI();
			}
			return false;
		}.bind(this));
	};
	this.sortData = function(d) {
	    return appdb.vappliance.utils.sortNetworkTrafficRules(d);
	};
	this.render = function(d) {
		this.reset();
		d = d || this.options.data;
		d = $.isArray(d) ? d : [d];
		this.sortData(d);
		$(this.dom).append(this.options.dom.emptyMessage);
		$(this.options.dom.list).empty();
		$(this.dom).find('.ruleslist').empty();
		$(this.dom).append(this.options.dom.list);
		$.each((this.options.data || []), function(i, v) {
			this.addItem(v, i);
		}.bind(this));

		$(this.dom).append(this.options.dom.footer);
		this.refreshUI();
	};

	this.initContainer = function() {
		var tempname = $.trim( $(this.dom).data("usetemplate") );

		if( tempname !== "" ){
			var tempdom = appdb.vappliance.ui.CurrentVAManager.getTemplate(tempname);
			if( $(tempdom).length > 0 ){
				this.options.itemTemplate = $(tempdom).clone(true);
			}
		}

		$(this.dom).append(this.options.dom.list);
	};

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.subviews = [];
		this.initContainer();
	};
	this._init();
});
