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
appdb.views = appdb.views || {};
appdb.views.ui = appdb.views.ui || {};
appdb.views.ui.mixins = appdb.views.ui.mixins || {};
appdb.views.ui.editors = appdb.views.ui.editors || {};
appdb.views.ui.viewers = appdb.views.ui.viewers || {};
appdb.views.ui.validators = {};
appdb.views.ui.hooks = {};
appdb.views.ui.hooks.validation = {};
appdb.views.ui.hooks.view = {};

appdb.views.ui.equalArrays = function(a,b){
	if( a.length !== b.length ) return false;
	for(var i=0; i<a.length; i+=1){
		var va = a[i];
		var vb = b[i];
		var t = typeof va;
		
		if( typeof b[i] !== type ){
			return false;
		}
		if( ["string", "number", "boolean"].indexOf(t) > -1 && a[i] !== b[i] ){
			return false; 
		}
		if( t === "object" && !appdb.views.ui.equalObjects(va,vb)){
			return false;
		}
	}
	return true;
};
appdb.views.ui.equalObjects = function(a,b){
	var equal = appdb.views.ui.equalObjects;
	function check(a, b) {
        for (var attr in a) {
            if (a.hasOwnProperty(attr) && b.hasOwnProperty(attr)) {
                if (a[attr] != b[attr]) {
                    switch (a[attr].constructor) {
                        case Object:
                            return equal(a[attr], b[attr]);
                        case Function:
                            if (a[attr].toString() !== b[attr].toString()) {
                                return false;
                            }
                            break;
                        default:
                            return false;
                    }
                }
            } else {
                return false;
            }
        }
        return true;
    };
    return check(a, b) && check(b, a);
};
appdb.views.ui.datafilter = {};
appdb.views.ui.datafilter.operations = (function(){
	var ops = {
		"=": function eq(a,b){
			return $.trim(a) === $.trim(b);
		},
		"!=": function neq(a,b){
			return $.trim(a) !== $.trim(b);
		},
		">=": function geq(a,b){
			return parseInt(a) >= parseInt(b);
		},
		"<=": function leq(a,b){
			return parseInt(a) <= parseInt(b);
		},
		">": function gt(a,b){
			return parseInt(a) > parseInt(b);
		},
		"<": function lt(a,b){
			return parseInt(a) < parseInt(b);
		}
	};
	var _getOperations = function(){
		var r = [];
		for(var i in ops ){
			if( ops.hasOwnProperty(i) ) r.push(i);
		}
		return r;
	};
	var _eval = function(op, a, b){
		return ops[op](a,b);
	};
	return {
		getOperations: _getOperations,
		eval: _eval
	};
})();
appdb.views.ui.datafilter.parse = function(s){
	var selectors = [];
	var ops = appdb.views.ui.datafilter.operations.getOperations();
	
	var op = $.grep(ops,function(o){
		return ( s.indexOf(o) > -1 );
	});
	op = (op.length>0)?op[0]:"=";

	var ee = s.split(op);
	var key = "";
	var val = "";
	if( ee.length > 1 && $.trim(ee[0])){
		key = $.trim(ee[0]);
	}
	if( ee.length > 1 ){
		val = $.trim(ee[1]) || $.trim(ee[0]);
	}
	if( key || val ){
		selectors.push({ key: key, op: op, val: val });
	}
	return ( selectors.length>0)?selectors:null;
};
appdb.views.ui.datafilter.evalselector = function(data,s){
	var d = ($.trim(s.key))?appdb.FindData(data,s.key):data;
	if( !d ) return false;
	if( s.op ){
		return appdb.views.ui.datafilter.operations.eval(s.op,d,s.val);
	}
	return false;
};
appdb.views.ui.datafilter.eval = function(data,selectors){
	data = data || [];
	data = $.isArray(data)?data:[data];
	selectors = selectors || [];
	selectors = $.isArray(selectors)?selectors:[selectors];
	var results = [], slen = selectors.length;
	$.each(data, function(i,e){
		var valids = $.grep(selectors, function(s){
			return appdb.views.ui.datafilter.evalselector(e,s);
		});
		if( valids.length === slen ){
			results.push(e);
		}
	});
	return (results.length>0)?results:null;
};
appdb.views.ui.getHooks = function(name,options){
	var basens = "appdb.views.ui.hooks.";
	var hooks = [];
	
	if( typeof name === "string" ){
		name = name.split(",");	
	}
	if( $.isArray(name) ){
		options = options || {};
		$.each(name, function(i,e){
			if( $.trim(e) !== "" ){
				var m = appdb.FindNS(basens + e);
				if( m ){
					hooks.push(new m(options));
				}
			}
		});
	}
	return hooks;
};
appdb.views.ui.HookHandler = appdb.DefineClass("appdb.views.ui.HookHandler", function(o){
	this.options = {
		parent: o.parent || null,
		hooks: [],
		instances: []
	};
	this.clear = function(){
		this.reset();
		this.options.hooks = [];
	};
	this.reset = function(){
		$.each(this.options.instances, function(i,e){
			if( e && e.reset ){
				e.reset();
			}
			e = null;
		});
		this.options.instances = [];
	};
	this.start = function(view){
		this.reset();
		this.options.instances = appdb.views.ui.getHooks(this.options.hooks,{parent:view});
		$.each(this.options.instances, function(i,e){
			e.register(view);
			e.render();
		});
	};
	this.register = function(names){
		if( typeof names === "undefined" ){
			return;
		} else if( typeof names === "string" ){
			names = $.trim(names);
			names = (names==="")?[]:names.split(",");
		}
		
		this.options.hooks = appdb.utils.UniqueOrderedArray(this.options.hooks.concat(names));		
	};
	this._init = function(){
		this.parent = this.options.parent;
		if( typeof o.hooks === "string" ){
			this.register(o.hooks);
		}
	};
	this._init();
});
appdb.views.ui.getValidatorsByProps = function(props){
	var basens = appdb.views.ui.validators;
	var res = [];
	var vobj = null;
	
	for(var i in basens){
		if( basens.hasOwnProperty(i) ){
			vobj = basens[i];
			if( "canUse" in vobj ){
				if( vobj.canUse(props) ){
					res.push(vobj);
				}
			} else if ( "attribute" in vobj && $.trim(vobj.attribute).toLowerCase() in props){
				res.push(vobj);
			}
		}
	}
	//Load external validators
	var checkers = ( "check" in props && $.trim(props.check).length > 0 )?props.check.split(","):[];
	if( checkers.length  > 0 ){
		$.each(checkers, function(i,e){
			var ch = appdb.FindNS("appdb.views.ui.validators." + e);
			if( !ch ){
				ch = appdb.FindNS(e);
			}
			if( ch ){
				res.push(ch);
			}
		});
	}
	return res;
};
appdb.views.ui.ValidatorHandler = appdb.DefineClass("appdb.views.ui.ValidatorHandler", function(o){
	this.options = {
		parent: o.parent || null,
		props: o.props || {},
		validators: [],
		instances: [],
		isvalid: true,
		running: []
	};
	this.getByName = function(name){
		name = $.trim(name);
		var res = $.grep(this.options.instances, function(e){
			return e.getName() === name || e.getFullName() === name;
		});
		return (res.length > 0)?res[0]:null;
	};
	this.clear = function(){
		this.reset();
		this.validators = [];
	};
	this.reset = function(){
		this.options.isvalid = true;
		$.each(this.options.instances, function(i,e){
			if( e && e.reset ) e.reset();
			e = null;
		});
		this.options.instances = [];
	};
	
	this.cancelValidations = function(){
		$.each(this.options.instances, function(i,e){
			if(e.cancel) e.cancel();
		});
	};
	this.getValidationResponseHandler = function(cb){
		return (function(self,cb){ 
			return function(v,instance){
				self.onValidationResponse(v,instance,cb);
			};
		})(this,cb);
	};
	this.onValidationResponse = function(v,instance,cb){
		if( v !== true ){
			this.cancelValidations();
		}
		cb(v,instance);
	};
	this.validate = function(v,cb,startcb,endcb){
		var isvalid = true;
		var results = [];
		var validationCallback = (function(self,results,cb,endcb){
			return function(v,instance){
				results.push(v);
				if( self.options.instances.length === results.length || (instance && instance.isValid()===false)){
					cb(v,instance);
				}
				if(typeof startcb === "function"){
					endcb(v,instance);
				}
			};
		})(this,results,cb,endcb);
		
		this.options.instances = this.options.instances || [];
		this.options.instances = $.isArray(this.options.instances)?this.options.instances:[this.options.instances];
		$.each(this.options.instances, (function(self,startcb){ 
			return function(i,e){
				if( !isvalid ) return;
				if(typeof startcb === "function"){
					startcb(v,e);
				}
				e.validate(v,self.getValidationResponseHandler(validationCallback));
				isvalid = e.isValid();
			};
		})(this,startcb));
	};
	this.isValid = function(){
		return this.options.isvalid;
	};
	this.getErrors = function(){
		var res = [];
		$.each(this.options.instances, function(i,e){
			if( e.isValid() === false ){
				res.push(e.getError());
			}
		});
		return res;
	};
	this.start = function(){
		this.reset();
		var res = [];
		$.each(this.options.validators, (function(self){
			return function(i,e){
				res.push(new e({props: self.options.props, attribute: e.attribute, parent: self.options.parent}));
			};
		})(this));
		this.options.instances = res;
	};
	this.register = function(validators){
		validators = validators || [];
		var res = this.options.validators.concat([]);
		$.each(validators, (function(self){
			return function(i,e){
				var exists = $.grep(res, function(v){
					return v === e;
				});
				if( exists.length === 0 ){
					res.push(e);
				}
			};
		})(this));
		this.options.validators = res.concat([]);
	};
	this.load = function(props){
		props = props || this.options.props || {};
		this.options.props = props;
		this.register(appdb.views.ui.getValidatorsByProps(this.options.props));
	};
	
	this._init = function(){
		this.parent = this.options.parent || null;	
	};
});
appdb.views.ui.getEditor = function(el,options){
	options = options || {};
	
	var editorname = $.trim($(el).data("editor") || "textbox").toLowerCase();
	var editor = null;
	for(var i in appdb.views.ui.editors){
		if( appdb.views.ui.editors.hasOwnProperty(i) && $.trim(i).toLowerCase() === editorname ){
			editor = appdb.views.ui.editors[i];
			break;
		}
	}
	
	if( !editor ) return null;
	
	var ce = $.trim($(el).data("canedit"));
	if( ce ){
		if( $.inArray(ce.toLowerCase(), ["true","1"]) > -1 ){
			ce = true;
		}else if($.inArray(ce.toLowerCase(), ["false","0"]) > -1) {
			ce = false;
		}else{
			ce = appdb.FindNS(ce);
			if( typeof ce === "function" ){
				ce = ce();
			}
		}
		
		if( ce === false ){
			return null;
		}
	}
	
	var data = { 
		container: options.container || el,
		parent: options.parent || null,
		props: $.extend(true, $(el).data(), options.props || {})
	};
	
	return new editor(data);
};
appdb.views.ui.getViewer = function(el,options){
	options = options || {};
	
	var viewername = $.trim($(el).data("ui") || "text").toLowerCase();
	var viewer = null;
	for(var i in appdb.views.ui.viewers){
		if( appdb.views.ui.viewers.hasOwnProperty(i) && $.trim(i).toLowerCase() === viewername ){
			viewer = appdb.views.ui.viewers[i];
			break;
		}
	}
	if( !viewer ){
		viewer = appdb.FindNS("appdb.views.ui.viewers." + viewername);
	}
	
	if( !viewer ) return null;
	
	var data = { 
		container: options.container || el,
		parent: options.parent || null,
		props: $.extend(true, $(el).data(), options.props || {})
	};
	
	return new viewer(data);
};
appdb.views.ui.loadMixin = function(o, mixin){
	o = o || {};
	var mo = (typeof mixin !== 'string')?mixin:null;
	
	if( !mo ) {
		if( mixin.indexOf('.') > 0 ) {
			mo = appdb.FindNS(mixin);
			if( !mo ) {
				mo = appdb.FindNS("appdb.views.ui.mixins." + mixin);
			}
		} else if( appdb.views.ui.mixins[mixin] ){
			mo = appdb.views.ui.mixins[mixin];
		}
		if( !mo ) return;
	}
	
	$.extend(true, o, mixin);
};
appdb.views.ui.validators.generic = appdb.DefineClass("appdb.views.ui.validators.generic",function(o){
	this.options = {
		parent: o.parent || null,
		attribute: o.attribute || "",
		props: o.props || {},
		errorMessage: o.errorMessage || "invalid value",
		isValid: true,
		lastError: {},
		state: "ready",
		previousValue: null,
		defaultName: "Value"
	};
	this.getDefaultName = function(){
		var p = this.options.parent;
		var name = this.options.defaultName;
		if( p && p.getProp  ){
			name = p.getProp("name") || name;
		}
		return name;
	};
	this.getState = function(){
		return this.options.state;
	};
	this.setState = function(state){
		this.options.state = $.trim(state).toLowerCase();
	};
	this.getProp = function(name){
		name = $.trim(name);
		if( name === "" ){
			return $.extend(true,{},this.options.props);
		}
		return appdb.FindData(this.options.props, name);
	};
	this.isValid = function(){
		return this.options.isValid;
	};
	this.getErrorMessage = function(v){ return this.options.errorMessage; };
	this.getDataValue = function(){ return this.options.value;	};
	this.getName = function(){
		this._initNames();
		return this.options.name;
	};
	this.getFullName = function(){
		this._initNames();
		return this.options.fullname;
	};
	this.getValue = function(){
		return this.getProp(this.options.attribute);
	};
	this.onValidate = function(){
		return true;
	};
	this.getError = function(){
		return this.options.lastError || {};
	};
	this.preCancel = function(){
		//safely overridden
	};
	this.cancel = function(){
		this.preCancel();
		this.setState("ready");
		this.postCancel();
	};
	this.postCancel= function(){
		//safely overridden
	};
	this.canMakeCall = function(){
		return true;
	};
	this.validate = function(v,cb){
		this.options.value = v;
		if( this.canMakeCall() === false ) {
			this.cancel();
			return true;
		}
		this.options.previousValue = v;
		var res = false;
		this.options.isValid = true;
		this.options.lastError = {};
		
		this.options.isValid = this.onValidate(v);
		if( this.options.isValid === false ) {
			this.options.lastError =  {
				parent: this.options.parent,
				value: this.options.value,
				message: ( this.getErrorMessage(v) || this.options.errorMessage ),
				type: this.getName()
			};
			res = this.getError();
		}else{
			res = true;
		}
		
		if( typeof cb === "function" ){
			cb(res,this);
		}
		
		return res;
	};
	this._initNames = function(){
		this.options.fullname = this._type_.getName();
		var name = ""+this.options.fullname;
		if( this.options.fullname.indexOf(".") > -1 ){
			name = this.options.fullname.split(".");
			this.options.name = name[name.length-1];
		}
		this._initNames = function(){};
	};
});
appdb.views.ui.validators.ExternalValidator = appdb.ExtendClass(appdb.views.ui.validators.generic, "appdb.views.ui.validators.ExternalValidator", function(o){
	this.preCall = function(){
		//safely overridden	
	};
	this.doExternalCall = function(cb){
		this.preCall();
		this.externalCall(cb);
		this.postCall();
	};
	this.externalCall = function(cb){
		//safely overridden
		//used to initialize an ajax request
	};
	this.postCall = function(v){
		//safely overridden	
	};
	this.validate = function(v,cb){
		this.options.value = v;
		
		if( this.canMakeCall() === false ) {
			this.cancel();
			return cb(this.isValid(),this);
		}
		this.options.isValid = false;
		this.options.lastError = {};
		this.setState("validating");
		var res = this.doExternalCall((function(self,cb){
			return function(invalids,instance){
				self.options.isValid = (typeof invalids === "boolean")?invalids:false;
				self.options.previousValue = self.options.value;
				if( self.options.isValid === false ) {
					self.options.lastError =  {
						parent: self.options.parent,
						value: self.options.value,
						message: ( self.getErrorMessage(v) || self.options.errorMessage ),
						type: self.getName()
					};
					res = self.getError();
				}else{
					res = true;
				}
				cb(res,self);
			};
		})(this,cb));
	};
});
appdb.views.ui.validators.AjaxValidator = appdb.ExtendClass(appdb.views.ui.validators.ExternalValidator, "appdb.views.ui.validators.AjaxValidator", function(o){
	this.getAjaxData = function(){
		//safely overridden
	};
	this.preCancel = function(){
		if( this.options.xhr && this.options.xhr.done !== true){
			this.options.xhr.onreadystatechange = null;
			this.options.xhr.abort();
			this.options.xhr = null;
		}
	};
	this.setState = function(state){
		this.options.state = $.trim(state).toLowerCase();
	};
	this.preCall = function(){
		this.preCancel();
	};
	this.parseResponse = function(response){
		return response;
	};
	this.onSuccess = function(d,cb){
		//safely overridden
	};
	this.onError = function(d,cb){
		//safely overridden
	};
	this.preResponse = function(){
		this.setState("ready");
	};
	this.postResponse = function(){
		//safely overridden
	};
	this.externalCall = function(cb){
		var a = this.getAjaxData();
		a.success = (function(self,cb){
			return function(d){
				if( self.getState() === "ready") return;
				self.preResponse();
				self.onSuccess(d,cb);
				self.postResponse();
				self.postCall(d,cb);
			};
		})(this,cb);
		a.error = (function(self,cb){
			return function(d){
				if( d.statusText === "abort" && d.status === 0 ){
					return;
				}
				if( self.getState() === "ready") return;
				self.preResponse();
				self.onError(d,cb);
				self.postResponse();
				self.postCall(d,cb);
			};
		})(this,cb);
		a.done = (function(self){
			return function(){
				if(self.options.xhr) self.options.xhr.done = true;
			};
		})(this);
		this.options.xhr = $.ajax(a);
	};
});
appdb.views.ui.validators.required =  appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.required", function(o){
	this.getErrorMessage = function(v){
		return this.getDefaultName() + " is required";
	};
	this.onValidate = function(v){
		return ( typeof v === "undefined" || v <= 0 || $.trim(v) === "" || $.isEmptyObject(v))?false:true;
	 };
},{ attribute: "required", canUse: function(props){
		return $.trim(props.required)==="true";
}});
appdb.views.ui.validators.regex = appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.regex", function(o){
	this.getErrorMessage = function(v){
		return $.trim(this.getProp("regexmessage")) || ( "Invalid " + this.getDefaultName()) ;
	};
	this.onValidate = function(v){
		return (new RegExp(this.getValue(), "g")).text(v);
	};
},{attribute:"regex", 
	predefined: {
		ruleRegex: /^(.+?)\[(.+)\]$/,
		numericRegex: /^[0-9]+$/,
		integerRegex: /^\-?[0-9]+$/,
		decimalRegex: /^\-?[0-9]*\.?[0-9]+$/,
		emailRegex: /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
		alphaRegex: /^[a-z]+$/i,
		alphaNumericRegex: /^[a-z0-9]+$/i,
		alphaDashRegex: /^[a-z0-9_\-]+$/i,
		naturalRegex: /^[0-9]+$/i,
		naturalNoZeroRegex: /^[1-9][0-9]*$/i,
		ipRegex: /^((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){3}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})$/i,
		base64Regex: /[^a-zA-Z0-9\/\+=]/i,
		numericDashRegex: /^[\d\-\s]+$/,
		urlRegex: /^((http|https|ftp|ftps):\/\/(\w+:{0,1}\w*@)?(\S+)|)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?$/
	}
});
appdb.views.ui.validators.datatype = appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.datatype", function(o){
	this.getErrorMessage = function(v){
		return this.getDefaultName() + " must be a valid " + this.getProp("datatype");
	};
	this.onValidate = function(v){
		return appdb.views.ui.validators.regex.predefined[this.getValue()+"Regex"].test(v);
	 };
},{ attribute: "datatype", canUse: function(props){
	return (props.datatype +"Regex") in appdb.views.ui.validators.regex.predefined;
}});
appdb.views.ui.validators.min = appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.min", function(o){
	this.getErrorMessage = function(v){
		 var isnumber = !isNaN(parseFloat(v)) && isFinite(v);
		 if( isnumber === true && ["numeric","integer","decimal"].indexOf(this.getProp("datatype")) > -1 ){
			 return this.getDefaultName() + " must be greater or equal to " + (this.getValue() || "0") + " digits long";
		 }else {
			 return this.getDefaultName() + " must be at least " + this.getValue() + " characters long";
		 }
	 };
	this.onValidate = function(v){
		return (this.getValue() <= $.trim(v).length)?true:false;
	};
},{attribute: "min", canUse: function(props){ 
	return appdb.utils.isNumber(props.min) && (props.min<<0)>0; 
}});
appdb.views.ui.validators.max = appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.max", function(o){
	this.getErrorMessage = function(v){
		var t = ( appdb.utils.isNumber(v) && ["numeric","integer","decimal"].indexOf(this.getProp("datatype")) > -1)?"digits":"characters";
		return this.getDefaultName() + " must not exceed " + this.getValue() + " " + t;
	 };
	 this.onValidate = function(v){
		return (this.getValue() > $.trim(v).length)?true:false;
	 };
},{attribute: "max", canUse: function(props){
	return appdb.utils.isNumber(props.max)
		&& (props.max<<0)>0;
}});
appdb.views.ui.validators.rangelength = appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.max", function(o){
	this.getRange = function(){
		var r = (this.getValue()+":").split(":");
		r[0] = r[0] << 0;
		r[1] = r[1] << 0;
		r.sort();
		r.shift();
		return r;
	};
	this.getErrorMessage = function(v){
		var r = this.getRange();
		var t = ( appdb.utils.isNumber(v) && ["numeric","integer","decimal"].indexOf(this.getProp("datatype")) > -1)?"digits":"characters";
		if( r[0] === r[1] ){
			return this.getDefaultName() + " must be exactly" + r[0] + " " + t + " long";
		}
		return this.getDefaultName() + " must range between " + r[0] + " and " + r[1] + " " + t;
	 };
	this.onValidate = function(v){
		var l = $.trim(v).length;
		var r = this.getRange();
		return ( l >= r[0] && l <= r[1] )?true:false;
	 };
},{ attribute:"rangelength",canUse: function(props){
	return /^[0-9]+(:[0-9]+)?/.test($.trim(props.rangelength));
}});
appdb.views.ui.validators.range = appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.max", function(o){
	this.getRange = function(){
		var r = (this.getValue()+":").split(":");
		r[0] = r[0] << 0;
		r[1] = r[1] << 0;
		r.sort();
		r.shift();
		return r;
	};
	this.getErrorMessage = function(v){
		var r = this.getRange();
		return this.getDefaultName() + " must range between " + r[0] + " and " + r[1];
	 };
	this.onValidate = function(v){
		var l = v << 0;
		var r = this.getRange();
		return ( l >= r[0] && l <= r[1] )?true:false;
	 };
},{ attribute:"range",canUse: function(props){
	return /^[0-9]+(:[0-9]+)?/.test($.trim(props.range)) && ["numeric","integer","decimal"].indexOf(props.datatype) > -1;
}});
appdb.views.ui.validators.date = appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.date", function(o){
	this.options.defaultName = "Date";
	this.getErrorMessage = function(v){
		return this.getDefaultName() + " format should be: yy-MM-dd";
	};
	this.onValidate = function(v){
		if( $.trim(v) !== "" ) {
			return ( $.trim((new Date(v)).toUTCString()).toLowerCase() === "invalid date")?false:true;
		}
		return true;
	};
},{attribute:"date"});
appdb.views.ui.validators.futuredate = appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.futuredate", function(o){
	this.getErrorMessage = function(v){
		return this.getDefaultName() + " must refer to future date";
	};
	this.onValidate = function(v){
		if( $.trim(v) !== "" ){
			var dt = new Date(v);
			var nowdate = new Date();
			if( dt <= nowdate){
				return false;
			}
		}
		return true;
	};
},{attribute: "date", canUse: function(props){ return $.trim(props.date).toLowerCase()==="future"; }});
appdb.views.ui.validators.pastdate = appdb.ExtendClass(appdb.views.ui.validators.generic,"appdb.views.ui.validators.pastdate", function(o){
	this.getErrorMessage = function(v){
		return this.getDefaultName() + " must refer to past date";
	};
	this.onValidate = function(v){
		if( $.trim(v) !== "" ){
			var dt = new Date(v);
			var nowdate = new Date();
			if( dt >= nowdate){
				return false;
			}
		}
		return true;
	};
},{attribute: "date", canUse: function(props){ return $.trim(props.date).toLowerCase()==="past"; }});
appdb.views.ui.hooks.Generic = appdb.ExtendClass(appdb.View,"appdb.views.ui.hooks.Generic", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		view: o.view || null
	};
	this.reset = function(){
		this.options.view.unsubscribeAll(this);
		this.unsubscribeAll();
		$(this.dom).empty();
	};
	this.render = function(){
		
	};
	this.register = function(view){
		this.options.view = view || this.options.view || {};
		this._init();
	};
	this._initContainer = function(){
		
	};
	this._initSettings = function(){
		
	};
	this._init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._initSettings();
		this._initContainer();
	};
});
appdb.views.ui.hooks.view.displayon = appdb.ExtendClass(appdb.views.ui.hooks.Generic, "appdb.views.ui.view.displayon", function(o){
	this.render = function(){
		if( this.canDisplay() ){
			$(this.parent.dom).addClass("showonedit");
			$(this.options.ancestor).addClass("showonedit");
		}else{
			$(this.parent.dom).removeClass("showonedit");
			$(this.options.ancestor).removeClass("showonedit");
		}
	};
	this.canDisplay = function(){
		var d = this.options.view.getData();
		if( this.options.checkkey !== this.parent.getProp('bind')){
			var dd = appdb.FindNS(this.options.checkkey);
			if( typeof dd === "function" && !dd() ){
				return false;
			}
			d = appdb.FindData(d,this.options.checkkey);
		}
		
		if( typeof d === "function" && !d() ){
			return false;
		}
		
		if( !d || ($.isArray(d) && d.length === 0) || $.isEmptyObject(d)){
			return true;
		}
		
		var val = (typeof this.options.checkvalue === "function")?this.options.checkvalue():this.options.checkvalue;
		if( val && $.trim(d) !== $.trim(val) ){
			return true;
		}
		return false;
	};
	this._initSettings = function(){
		var hideparent = $(this.parent.dom).closest("[data-hideonempty='"+this.parent.getProp('bind')+"']");
		if( hideparent.length > 0  ){
			this.options.ancestor = hideparent;
		}
		
		this.options.checkkey = this.parent.getProp("displayon") || this.parent.getProp('bind');
		this.options.checkvalue = this.parent.getProp("displayon-value");
	};
});
appdb.views.ui.hooks.validation.status = appdb.ExtendClass(appdb.views.ui.hooks.Generic,"appdb.views.ui.hooks.validation.status", function(o){
	this.onValidation = function(){
		var val = this.options.view.getData();
		$(this.dom).find(".current").text(val.length);
		if( this.options.max < val.length ){
			$(this.dom).parent().addClass("invalid-status");
		}else{
			$(this.dom).parent().removeClass("invalid-status");
		}
	};
	this.render = function(){
		$(this.dom).find(".total").text(this.options.max);
		this.options.view.subscribe({event: "validation", callback: function(v){
			this.onValidation();
		},caller: this});
		this.onValidation();
	};
	this._initSettings = function(){
		this.options.max = this.options.view.getProp("max");
		if(!this.options.max && this.options.view.getProp("rangelength")){
			
		}
	};
	this._initContainer = function(){
		$(this.options.view.dom).children(".validationstatus").remove();
		this.dom = $('<span class="validationstatus">using <span class="current"></span> of <span class="total"></span> characters</span>');
		$(this.options.view.dom).append(this.dom);
	};
});
appdb.views.ui.hooks.validation.displayerror = appdb.ExtendClass(appdb.views.ui.hooks.Generic,"appdb.views.ui.hooks.validation.displayerror", function(o){
	this.onValidation = function(d){
		if( d && d.length > 0 ){
			$(this.dom).parent().addClass("invalid-message");
			$(this.dom).find(".validationerrormessage").text(d[0].message);
		}else{
			$(this.dom).parent().removeClass("invalid-message");
		}
	};
	this.render = function(d){
		this.options.view.subscribe({event: "validation", callback: function(v){
			this.onValidation(v);
		},caller: this});
	};
	this._initSettings = function(){
		
	};
	this._initContainer = function(){
		$(this.options.view.dom).children(".validationmessage").remove();
		this.dom = $('<div class="validationmessage"><img src="/images/vappliance/warning.png" alt=""><div class="validationerrormessage"><span>Value is required</span></div></div>');
		$(this.options.view.dom).append(this.dom);
	};
});
appdb.views.ui.hooks.validation.filesize = appdb.ExtendClass(appdb.views.ui.hooks.Generic,"appdb.views.ui.hooks.validation.filesize", function(o){
	this.onValidation = function(d){
		var validator = this.options.view.validators.getByName("required");
		if( validator && !validator.isValid() ){
			$(this.dom).parent().addClass("invalid-message");
			$(this.dom).find(".validationerrormessage").text(validator.getErrorMessage());
		}else{
			$(this.dom).parent().removeClass("invalid-message");
		}
	};
	this.render = function(d){
		this.options.view.subscribe({event: "validation", callback: function(v){
			this.onValidation();
		},caller: this});
	};
	this._initSettings = function(){
		
	};
	this._initContainer = function(){
		$(this.options.view.dom).children(".validationmessage").remove();
		this.dom = $('<div class="validationmessage"><img src="/images/vappliance/warning.png" alt=""><div class="validationerrormessage"><span>Value is required</span></div></div>');
		$(this.options.view.dom).append(this.dom);
	};
});
appdb.views.ui.mixins = {};
appdb.views.ui.mixins.Collection = appdb.DefineClass("appdb.views.ui.mixins.Collection", function(o){
	this.loadItemProps = function(){
		var props = this.getProp();
		var res = {};
		for(var i in props){
			if( props.hasOwnProperty(i) && i.length>6 && i.substr(0,5) === 'item-'){
				res[i.substr(5)] = props[i];
			}
		}
		this.options.itemprops = res;
		return this.options.itemprops;
	};
	this.getItemProps = function(){
		if( !this.options.itemprops )
		{
			this.loadItemProps();
		}
		
		return this.options.itemprops;
	};
	this.getItemDataWrapper = function(d){
		var data = {};
		data[this.getProp('bind')] = d;
		return data;
	};
	this.appendItemDomProps = function(el){
		var ip = this.getItemProps();
		for(var i in ip){
			if( ip.hasOwnProperty(i) ){
				$(el).attr('data-' + i,ip[i]);
			}
		}
		return el;
	};
	this.createUI = function(el, data, func){
		var item = null;
		var dom = $(el).find('.appdb-ui-collection-item');
		
		this.appendItemDomProps(dom);
		item = func(dom, {parent: this});
		if( !item ) return;
		
		if( typeof data !== 'undefined') {
			item.bind( this.getItemDataWrapper(data) );
		}
		
		return item;
	};
	
	this.createViewerUI = function(el, data){
		return this.createUI(el, data, appdb.views.ui.getViewer);
	};
	
	this.createEditorUI = function(el, data){
		return this.createUI(el, data, appdb.views.ui.getEditor);
	};
	
	this.initCollectionView = function(data){
		var html = $('<div></div>').html(this.options.innerHTML);
		
		if( $(html).find('.collection-section.header').length ) {
			$(this.dom).prepend($(html).find('.collection-section.header'));
		}
		
		if( $(html).find('.collection-section.footer').length ) {
			$(this.dom).append($(html).find('.collection-section.footer'));
		}
	};
	
	this.renderEmpty = function(){
		if( $(this.options.dom.list).children('li').length === 0 ) {
			$(this.dom).addClass('empty').prepend($('<div></div>').html(this.options.innerHTML).find('.empty-collection-container'));
			if( $.trim($(this.dom).attr('placeholder')) !== '' ) {
				var ph = $(this.dom).find('.empty-collection-container.empty-message, .empty-collection-container .empty-message');
				if( $.trim($(ph).text()) === '' ){
					$(ph).html($.trim($(this.dom).attr('placeholder')));
				}
			}
		} else {
			$(this.dom).removeClass('empty').find('.empty-collection-container').remove();
		}
	};
	
});
appdb.views.ui.Generic = appdb.ExtendClass(appdb.View,"appdb.views.ui.Generic",function(o){
	this.options = $.extend(true,{},o);
	this.hooks = new appdb.views.ui.HookHandler({parent: this, hooks:o.hooks||[]});
	this.resetDom = function(){
		this.options.editorcontainer = null;
		if( !this.options.innerHTML ) {
			if( !$(this.dom).data('innerHTML') ) {
				$(this.dom).data('innerHTML',$.trim($(this.dom).html()) );
			}
			this.options.innerHTML = $(this.dom).data('innerHTML');
		}
		$(this.dom).empty();
		this.initEditorContainer();
	};
	this.initEditorContainer = function(){
		if( $(this.dom).find(".uieditorcontainer").length === 0 ){
			$(this.dom).append("<div class='uieditorcontainer'></div>");
			this.options.editorcontainer = $(this.dom).find(".uieditorcontainer");
		}
	};
	this.reset = function(){
		this.unsubscribeAll();
		this.hooks.reset();
		this.resetDom();
		this._initContainer();
		this._initProperties();
	};
	this.startHooks = function(){
		this.hooks.register(this.options.props.hooks);
		this.hooks.start(this);
	};
	this.preRender = function(d){
		d = d || this.options.data;
		this.options.data = d;
	};
	this.doRender = function(){
		this.reset();
		this.startHooks();
		this.preRender();
		this.publish({event: "prerender", value: this});
		this.render();
		this.publish({event: "render", value: this});
		this.postRender();
		this.publish({event: "postrender", value: this});
	};
	this.getDisplayData = function(){
		var d = this.getData();
		if( d && d.val && d.val() ){
			return d.val();
		} else if( !d || $.isEmptyObject(d)){
			d = "<span class='empty'>" + $.trim(this.getProp("empty")) + "</span>";
		}
		return d;
	};
	this.postRender = function(){
		//safely overriddeen
	};
	this.render = function(){
		$(this.dom).html(this.getDisplayData());
		//safely overriddeen
	};
	this.getProp = function(name){
		name = $.trim(name);
		if( name === "" ){
			return $.extend(true,{},this.options.props);
		}
		return appdb.FindData(this.options.props, name);
	};
	this.getData = function(){
		return this.options.data;
	};
	this.bindSelection = function(data){
		if( !this.getProp("selectors") ) return data;
		data = appdb.views.ui.datafilter.eval(data,this.getProp("selectors"));
		return (data && data.length>0)?data[0]:null;
	};
	this.bind = function(data, render){
		this._initProperties();
		
		render = (typeof render === "boolean")?render:true;
		data = data || {};
		var bindpath = this.getProp("bind");
		if( bindpath ){
			data = appdb.FindData(data, bindpath);
		}
		if( typeof data === "function" ){
			data = data();
		}
		if( data ){
			data = this.bindSelection(data);
		}
		this.options.originalData = data;
		this.options.data = this.options.originalData;
		this.postBind();
		
		if( render ){
			this.doRender();
		}
	};
	this.postBind = function(){
		//safely overridden
	};
	this.getDataSource = function(){
		var ds = (this.options.props.source)?appdb.FindNS(this.options.props.source):null;
		return (typeof ds === "function")?ds():ds;
	};
	this._initBindSelectors = function(){
		var bs = this.getProp("bind-select");
		if( $.trim(bs) === "" ) return null;
		return appdb.views.ui.datafilter.parse(bs);
	};
	this._initContainer = function(){
		//safely overridden
	};
	this.initUserProperties = function(){
		//safely overridden
	};
	this._initProperties = function(){
		var props = this.options.props || {};
		props.name = $.trim(props.name) || $.trim(props.bind) || "";
		props.bind = $.trim(props.bind) || $.trim(props.name) || "";
		props.source = $.trim(props.source) || "";
		props.max = $.trim(props.max) << 0;
		props.min = $.trim(props.min) << 0;
		props.required = ( $.trim(props.required).toLowerCase() === "true" )?true:false;
		props.placeholder = $.trim(props.placeholder) || $.trim($(this.dom).attr("placeholder")) || "";
		props.ui = $.trim(props.ui) || "text";
		props.hooks = props.hooks || props["use"] || "";
		
		//Editor specific properties
		props.editor = {name: $.trim(props.editor) || "textbox"};
		props.editor.hooks = props.editor.hooks || props["editor-use"] || "";
		
		this.options.props = props;
		this.options.props.selectors = this._initBindSelectors();
		this.initUserProperties();
		this._initProperies = function(){};
	};
	this._init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
});
appdb.views.ui.viewers.Text = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.Text",function(o){});
appdb.views.ui.viewers.Boolean = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.Boolean",function(o){
	this.getDisplayData = function(){
		var d = this.getData();
		var v = $.trim(this.getProp("default")) || "false";
		if( $.trim(d) === "true" ) v = "true";
		return $.trim(this.getProp(v+"value")) || v;
	};
});
appdb.views.ui.viewers.Date = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.Date",function(o){
	this.getDisplayData = function(){
		var d = $.trim(this.getData());
		if( !d ){
			d = "<span class='empty'>" + $.trim(this.getProp("empty")) + "</span>";
		}else{
			d = d.split("T")[0];
		}
		return d;
	};
});
appdb.views.ui.viewers.Link = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.Link",function(o){
	this.getDefaultData = function(linktype){
		linktype = linktype || $.trim(this.getProp("link-type")) || "";
		this.options.data = this.options.data|| [];
		this.options.data = $.isArray(this.options.data)?this.options.data:[this.options.data];
		var res = $.grep(this.options.data, function(e){
			return ($.trim(e["type"])===linktype);
		});
		
		if( res.length>0 ){
			res = res[0];
			if( res && res.val){
				if( $.trim(res.val()) === ""){
					return null;
				}
				return res;
			}
		}
		return this.options.data.length > 0?this.options.data[o]:null;
	};
	this.getData = function(){
		this.options.data = this.options.data|| [];
		var d = $.isArray(this.options.data)?this.options.data:[this.options.data];
		return this.getDefaultData();
	};
	this.getDisplayData = function(){
		var data = this.getData();
		var url = (data && data.val)?data.val():data;
		if( $.trim(url) === "") return "";
		var urltype = $.trim((data)?data["type"]:"").toLowerCase();
		var urltypedisplay = $.trim(this.getProp("link-typedisplay")) || urltype ||"page";
		var title = $(this.dom).attr("title") || "Click to visit the " + urltypedisplay;
		var el = $("<a target='_blank'></a>");
		$(el).attr("href", url).attr("title", title);
		if( urltype ){
			$(el).append($("<img src='/images/"+urltype+".png' alt=''/>"));
		}
		var span = $("<span></span>");
		$(span).text(urltype || url);
		$(el).append(span);
		return el;
	};
});
appdb.views.ui.viewers.Url = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.Url",function(o){
	this.getData = function(){
		if(!this.options.data || $.isEmptyObject(this.options.data) ){
			return "";
		}else if( typeof this.options.data.val === "function" ){
			return this.options.data.val();
		}
		return this.options.data;
	};
	this.getDisplayData = function(){
		var url = this.getData();
		var el = $("<a target='_blank'></a>");
		$(el).attr("href", url).attr("title", "Click to visit the url");
		$(el).text( url );
		return el;
	};
});
appdb.views.ui.viewers.Organization = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.Organization",function(o){
	this.getDisplayData = function(){
		$(this.dom).find(".uieditorcontainer").remove();
		var d = this.getData();
		if( !d ) return;
		var org = $("<span></span>"), shortname, name, isos = [];
		if( d.url && d.url["type"]==="website" && d.url.val){
			org = $("<a title='Click to visit organization' target='_blank'></a>");
			$(org).attr("href",d.url.val());
		}
		var shortname = $.trim(d.shortname);
		var name = $.trim(d.name);
		if( name.toLowerCase() === shortname.toLowerCase() ){
			shortname = "";
		}else{
			shortname = $("<span class='shortname'></span>").text(shortname);
			$(org).append(shortname);
		}
		name = $("<span class='name'></span>").text(name);
		$(org).append(name);
		$(this.dom).append(org);

		if( d.country && d.country.isocode ){
			isos = d.country.isocode.split("/");
			var flagsdom = $("<div class='flags'></div>");
			for (var i = 0; i < isos.length; i += 1) {
				$(flagsdom).append($("<img src='/images/flags/" + $.trim(isos[i]).toLowerCase() + ".png' border='0' />").attr("title",d.country.val()));
			}
			$(this.dom).prepend(flagsdom);
		}		
	};
});
appdb.views.ui.viewers.Site = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.Site",function(o){
	this.getDisplayData = function(){
		var d = this.getData();
		if( !d ) return;
		var site = $("<span></span>"), shortname, name, isos = [];
		var shortname = $.trim(d.name);
		var name = $.trim(d.officialname);
		
		site = $("<a title='Click to view site details' target='_blank'></a>");
		$(site).attr("href", appdb.config.endpoint.base + "store/site/" + shortname.toLowerCase());
		
		
		if( name.toLowerCase() === shortname.toLowerCase() ){
			shortname = "";
		}else{
			shortname = $("<span class='shortname'></span>").text(shortname);
			$(site).append(shortname);
		}
		name = $("<span class='name'></span>").text(name);
		$(site).append(name);
		$(this.dom).append(site);

		if( d.country && d.country.isocode ){
			isos = d.country.isocode.split("/");
			var flagsdom = $("<span class='flags'></span>");
			for (var i = 0; i < isos.length; i += 1) {
				$(flagsdom).append($("<img src='/images/flags/" + $.trim(isos[i]).toLowerCase() + ".png' border='0' />").attr("title",d.country.val()));
			}
			$(site).prepend(flagsdom);
		}		
	};
});

appdb.views.ui.viewers.Collection = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.viewers.Collection", function(o){
	this.options = $.extend(true,{ itemprops: null, items: [] },o);
	
	this.addItem = function(e){
		var dom = $(this.options.templates.item).clone(true);
		var li = $('<li></li>');
		$(li).append(dom);
		
		var item = this.createViewerUI(li, e);
		if( !item ){
			return null;
		}
		
		this.options.items.push(item);
		return li;
		
	};
	this.getDisplayData = function(){
		var d = this.getData();
		if( !d ) return;
		d = $.isArray(d)?d:[d];
		
		this.initCollectionView(d);
		
		$.each(d,(function(self){
			return function(i, e) {
				var li = self.addItem(e);
				if( li !== null ) {
					self.options.dom.list.append(li);
				}
			};
		})(this));
		this.renderEmpty();
	};
	
	this._initContainer = function(){
		var html = $('<div></div>').html(this.options.innerHTML);
		if( !this.options.templates ) {
			this.options.templates = {
				item: $(html).find('.appdb-ui-collection-item-template').html()
			};
			$(this.dom).empty();
			$(this.dom).append('<ul></ul>');
			this.options.dom = {
				list: $(this.dom).children('ul')
			};
		}		
	};
	
	appdb.views.ui.loadMixin(this,new appdb.views.ui.mixins.Collection());
});
appdb.views.ui.editors.Generic = appdb.ExtendClass(appdb.views.ui.Generic,"appdb.views.ui.editors.Generic",function(o){
	this.options = $.extend(true,{},o);
	this.options.isvalid = true;
	this.validators = new appdb.views.ui.ValidatorHandler({parent: this});
	this.reset = function(){
		this.unsubscribeAll();
		this.hooks.reset();
		this.resetDom();
		this._initContainer();
		this._initProperties();
		this.validators.load(this.getProp());
		this.validators.start();
		this.onValueChange();
	};
	this.startHooks = function(){
		this.hooks.register(this.options.props.editor.hooks);
		this.hooks.start(this);
	};
	this.getDisplayData = function(){
		return this.getData();
	};
	this.getValidationData = function(){
		return this.getData();
	};
	this.postBind = function(){
		var dt = this.options.props.datatype || "";
		if( $.isArray(this.options.originalData) ) {
			this.options.data = appdb.utils.extendArray(this.options.originalData);
			this.options.props.datatype = dt || "list";
		} else {
			switch( typeof this.options.originalData ){
				case "boolean":
					this.options.data = !!this.options.originalData;
					this.options.props.datatype = dt || "boolean";
					break;
				case "number":
					this.options.data = 0 + this.options.originalData;
					this.options.props.datatype = dt || "number";
					break;
				case "string":
					this.options.data = "" + this.options.originalData;
					this.options.props.datatype = dt || "string";
					break;
				case "object":
					this.options.data = $.extend(true,{},this.options.originalData);
					this.options.props.datatype = dt || "set";
					break;
			}
		}
	};
	this.getDataSource = function(){
		var ds = (this.options.props.source)?appdb.FindNS(this.options.props.source):null;
		ds = (typeof ds === "function")?ds():ds;
		ds = ds || [];
		return $.isArray(ds)?ds:[ds];
	};
	this.getDataSourceExclusions = function(){
		var excls = this.getProp("exclusions");
		var exclsource = null;
		if( !excls ){
			excls = [];
			exclsource = $.trim(this.getProp("exclude"));
			if( exclsource && exclsource.length > 0 ){
				//check if is wrapped as array [...]
				if( exclsource[0] === "[" && exclsource[exclsource.length -1] === "]"){
					exclsource = exclsource.slice(0,1);
					exclsource = exclsource.slice(exclsource.length-1,1);
				}
				exclsource = exclsource.split(",");
				
				$.each(exclsource, function(i,e){
					if( e && e.length > 0 ){
						//check fior functions
						if( e.length > 3 && e[e.length-1] === ")" && e[e.length-2] === "(" ) {
							var func = appdb.FindNS(e.slice(0,e.length-2 ));
							func = (typeof func === "function")?func():null;
							if( func ){
								excls = excls.concat( ($.isArray(func))?func:[func] );
							}
						} else if( e[0] === "{" && e[e.length-1] === "}") { //check if object
							excls.push(JSON.parse(e));
						} else if( isNaN(parseInt(e)) === false || isNaN(parseFloat(e)) === false) { //check if number
							excls.push(e);
						} else if( e.indexOf(".") ){
							var o = appdb.FindData(e);
							o = (typeof o === "function")?o():o;
							if( o ){
								excls = excls.concat( ($.isArray(o)?o:[o]) );
							}
						} else {
							excls.push(e);
						}
					}
				});
			}
			this.options.props.exclusions = excls;
		}
		return excls;
	};
	this.hasChanges = function(){
		switch(this.options.props.datatype){
			case "array":
				return appdb.views.ui.equalArrays(this.options.originalData,this.options.data);
			case "object":
				return appdb.views.ui.equalObjects(this.options.originalData,this.options.data);
			case "boolean":
			case "string":
			case "number":
			default:
				return this.options.data !== this.options.originalData;
		}
//		return this.options.originalData !== this.options.data;
	};
	this.isValid = function(){
		return this.options.isvalid;
	};
	this.onValueChange = function(){
		var value = this.getData();
		if( this.options.prevValue !== value ){
			this.publish({event: "changed", value: value});
			var name = $.trim( this.getProp('name') || this.getProp('bind') );
			if( name ){
				this.publish({event: "changed:" + name, value: this});
			}
			this.options.prevValue = value;
		}
		this.publish({event: "validating", value: true});
				
		this.validators.validate(this.getValidationData(),
		/*result callback*/(function(self){ 
			return function(invalids){
				var isvalid = (invalids === true)?true:false;
				if( isvalid === false ){
					$(self.dom).addClass("invalid");
				}else{
					$(self.dom).removeClass("invalid");
				}
				self.options.isvalid = isvalid;
				invalids = invalids || [];
				invalids = $.isArray(invalids)?invalids:[invalids];
				var _invalids = isvalid || invalids;
				self.publish({event: "validation", value: _invalids});
				self.postValueChange(_invalids);
			};
		})(this),
		/*start callback*/(function(self){ 
			return function(v,instance){
				var ev = "validation:start";
				self.publish({event: ev, value: instance});
				if( instance && instance._type_ ){
					var name = $.trim(instance._type_.getName()).split(".");
					if( name.length > 0 ){
						ev += ":"+name[name.length-1];
						self.publish({event: ev, value: instance});
					}
				}
				
			};
		})(this),
		/*end validation*/(function(self){ 
			return function(v,instance){
				var ev = "validation:end";
				self.publish({event: ev, value: instance});
				if( instance && instance._type_ ){
					var name = $.trim(instance._type_.getName()).split(".");
					if( name.length > 0 ){
						ev += ":"+name[name.length-1];
						self.publish({event: ev, value: instance});
					}
				}
				
			};
		})(this)
		);
	};
	this.postValueChange = function(){
		//safely overridden
	};	
	this.revert = function(){
		this.options.data = this.options.originalData;
	};
	this.getEditedData = function(){
		return appdb.utils.valueToObject(this.getProp("bind"),this.getData(),"val");
	};
	this.getOriginalData = function(){
		return this.options.originalData;
	};
});
appdb.views.ui.editors.TextBox = appdb.ExtendClass(appdb.views.ui.editors.Generic,"appdb.views.ui.editors.TextBox",function(o){
	this.render = function(){
		if( this.options.editor ){
			this.options.editor.destroyRecursive(false);
			this.options.editor = null;
		}
		this.options.editor = new dijit.form.ValidationTextBox({
			name: this.getProp("name"), 
			value: this.getDisplayData(),
			placeHolder : this.options.placeholder || $(this.dom).attr("placeholder"),
			required: this.getProp("required"),
			onFocus  : (function(self){
				return function(v){
					self.options.data = this.get("displayedValue")||"";
					self.onValueChange();
				};
			})(this),
			onBlur  : (function(self){
				return function(v){
					self.options.data = this.get("displayedValue")||"";
					self.onValueChange();
				};
			})(this),
			onChange : (function(self){
				return function(v){
					self.options.data = this.get("displayedValue")||"";
					self.onValueChange();
				};
			})(this),
			onKeyUp: (function(self){
				return function(v){
					self.options.data = this.get("displayedValue")||"";
					self.onValueChange();
				};
			})(this),
			onMouseUp: (function(self){
				return function(v){
					self.options.data = this.get("displayedValue")||"";
					self.onValueChange();
				};
		})(this)}, $(this.options.editorcontainer)[0]);
	};
	this.hooks.register(["validation.displayerror"]);
});
appdb.views.ui.editors.TextArea = appdb.ExtendClass(appdb.views.ui.editors.Generic,"appdb.views.ui.editors.TextArea",function(o){
	this.render = function(){
		var onTextAreaValueChange = (function(self){
			return function(ev){
				self.options.data = $(this).val();
				self.onValueChange();
			};
		})(this);
		
		if( this.options.editor ){
			if(this.options.editor.destroyRecursive){
				this.options.editor.destroyRecursive(false);
			}
			this.options.editor = null;
		}
		this.options.editor = $("<textarea></textarea>")
			.attr("name",this.getProp("name"))
			.attr("placeholder",this.getProp("placeholder"))
			.off("keyup").on("keyup", onTextAreaValueChange)
			.off("focus").on("focus", onTextAreaValueChange)
			.off("blur").on("blur", onTextAreaValueChange);
	
		$(this.options.editor).text(this.getData());
		$(this.dom).prepend(this.options.editor);
	};
	this.hooks.register(["validation.displayerror"]);
});
appdb.views.ui.editors.FileSize = appdb.ExtendClass(appdb.views.ui.editors.TextBox,"appdb.views.ui.editors.FileSize",function(o){
	this.postRender = function(){
		var description = $(this.dom).children(".description");
		if( !description.length ){
			$(this.dom).append("<span class='validationstatus description'><span class='value'></span><span class='unit'></span></span>");
			description = $(this.dom).children(".description");
		}
		this.postValueChange();
	};
	this.postValueChange = function(invalids){
		var description = $(this.dom).children(".description");
		var val = "n/a";
		if( (!invalids || !invalids.length) && appdb.utils.isNumber(this.getData()) === true ){
			val = appdb.utils.formatSizeUnits(this.getData(),true);
		}
		val = val.split(" ");
		var size = val[0];
		var unit1 = ((val.length > 1 && val[1] && val[1]!=="undefined")?val[1]:"");
		var unit2 = ((val.length > 2 && val[2] && val[2]!=="undefined")?val[2]:"");
		if( !unit1 || val[1] === "undefined") size = 'n/a';
		$(description).find(".value").text(size);
		$(description).find(".unit").text(unit1 + " " + unit2 );
	};
	this.hooks.register(["validation.displayerror"]);
});
appdb.views.ui.editors.Url = appdb.ExtendClass(appdb.views.ui.editors.TextBox,"appdb.views.ui.editors.Url",function(o){
	this.getDisplayData = function(){
		var data = (this.options.data && $.isArray(this.options.data) && this.options.data.length > 0)?this.options.data[0]:this.options.data;
		data = ($.isEmptyObject(data)||!data)?"":data;
		var url = (data.val)?data.val():data;
		if( typeof url !== "string" ) return "";
		return url;
	};
	this._getEditedData = this.getEditedData;
	this.getEditedData = function(){
		var bind = this.getProp("bind");
		var urltype = $.trim(this.getProp("url-type"));
		var o = appdb.utils.valueToObject(bind,this.getData(),"val");
		var u = "";
		if( o && o[bind] && typeof o[bind] === "string" ){
			u = $.trim(o[bind]) || "";
		}else if( !o || !o[bind] || typeof o[bind].val !== "function" ){
			u = "";
		}else if( o[bind].val ){
			u = o[bind].val() || "";
		}
		if( urltype ){
			var res = {};
			res[bind] = { "type": urltype, val: (function(v){ return function(){return v;};})(u) };
			return res;
		}
		return o;
	};
	this.getValidationData = function(){
		return this.getDisplayData();
	};
});
appdb.views.ui.editors.OrganizationList = appdb.ExtendClass(appdb.views.ui.editors.Generic,"appdb.views.ui.editors.OrganizationList",function(o){
	this.render = function(){
		if( this.options.editor ){
			this.options.editor.unsubscribeAll();
			this.options.editor.reset();
			this.options.editor = null;
		}
		var cdata = this.getData();
		var data = null;
		if( cdata ){
			var data = {
				entity:{
					id: cdata.id,
					guid: cdata.id,
					organization: cdata
				}
			};
		}
		var dom = ($(this.dom).find(".uieditorcontainer").length)?$(this.dom).find(".uieditorcontainer"):this.dom;
		this.options.editor = new appdb.views.AutoCompleteListOrganization({
			container: dom,
			parent: this,
			selecteddata: data
		});
		this.options.editor.subscribe({ event: "change", callback: function(v){
			this.publish({event: "changed", value: v});
		}, caller: this});
		this.options.editor._init();
	};
	this.getEditedData = function(allownull){
		allownull = (typeof allownull === "boolean")?allownull:false;
		if( this.options.editor ){
			var data = this.options.editor.getSelectedData();
			if( data !== null ){
				return {"organization": {"id": data.id || data.guid}};
			}
		}
		return {"organization": null};
	};
});

appdb.views.ui.editors.combobox = appdb.ExtendClass(appdb.views.ui.editors.Generic,"appdb.views.ui.editors.combobox",function(o){
	this._getDataSource = this.getDataSource;
	this.getDataSource = function(){
		if( !this.options.validdatasource ){
			this.options.validdatasource = [];
			var ds = this._getDataSource();
			if( ds.length > 0 ){
				var excl = this.getDataSourceExclusions();
				if( excl && excl.length > 0 ){
					$.each(ds, (function(self){ 
						return function(i,d){
							var id = $.trim(self.getListItemValue(d));
							var f = $.grep(excl, function(e){
								return $.trim(e) === id;
							});
							if( f.length === 0 ){
								self.options.validdatasource.push(d);
							}
						};
					})(this));
				}else{
					this.options.validdatasource = ds;
				}
			} else {
				this.options.validdatasource = ds;
			}
		}
		return this.options.validdatasource;
	};
	this.getListItemDisplayValue = function(d){
		if( d ){
			if( this.getProp("editor-displayvalue") ) return d[this.getProp("editor-displayvalue")];
			if( this.getProp("editorDisplayvalue") ) return d[this.getProp("editorDisplayvalue")];
			if( d.val && d.val()) return d.val();
			if( d.name ) return d.name;
			if( d.id ) return d.id;
		}
		return "";
	};
	this.getListItemValue = function(d){
		if( $.isPlainObject(d) ){
			if( this.getProp("editor-value") && $.trim(d[this.getProp("editor-value")])) return d[this.getProp("editor-value")];
			if( d.id ) return d.id;
			if( d.val ) return d.val();
		}
		return d;
	};
	this.getSelectedItem = function(){
		var d = $.trim(this.getSelectedValue());
		if( $.trim(d) === "" ) return null;
		var key = this.getProp("editor-value") || "id";
		var item = $.grep(this.getDataSource(), function(e){
			if( e[key] && $.trim(e[key]) === d) return true;
			if( typeof e.val === "function" && $.trim(e.val()) === d ) return true;
			return false;
		});
		
		return ( item.length > 0 )?item[0]:null;
	};
	this.getSelectedValue = function(){
		return this.getListItemValue(this.getData());
	};
	this.getEditedData = function(allownull){
		allownull = (typeof allownull === "boolean")?allownull:false;
		var bind = this.getProp("bind");
		return appdb.utils.valueToObject(bind,this.getSelectedItem(),"val");
	};
	
	this.render = function(){
		if( this.options.editor ){
			this.options.editor.destroyRecursive(false);
			this.options.editor = null;
		}
		var val = this.getSelectedValue();
		if( !val ){
			var v = this.getDataSource() || [];
			v = $.isArray(v)?v:[v];
			if( v.length > 0 ){
				val = v[0];
				val = self.getListItemValue(val);
			}
		}
		var options = $.map(this.getDataSource(), (function(self){ 
			return function(e){
				return { label: self.getListItemDisplayValue(e), value: self.getListItemValue(e) };
			};
		})(this));
		
		this.options.editor = new dijit.form.Select({
			options: options,
			value: val,
			required: this.options.required,
			onChange: (function(self){
				return function(v){
					self.options.data = this.get("value")||"";
					self.onValueChange();
				};
			})(this)
		}, $(this.options.editorcontainer)[0]);
	};
	this.options.props = this.options.props || {};
	this.options.props["editor-value"] = this.getProp("editor-value") || "id";
});

appdb.views.ui.editors.list = appdb.ExtendClass(appdb.views.ui.editors.combobox,"appdb.views.ui.editors.list",function(o){
	this.initEditorContainer = function(){
		if( $(this.dom).find(".uieditorcontainer").length === 0 ){
			var datasource = this.getDataSource();
			var sel = this.getSelectedValue();
			var html = "<select class='uieditorcontainer'>";
			$.each(datasource, (function(self) { 
				return function(i,e){
					var val = self.getListItemValue(e);
					var issel = (sel && val == sel)?"selected":"";
					html += "<option value='" + val + "' " + issel + ">" + self.getListItemDisplayValue(e) + "</option>";
				};
			})(this));
			html += "</select>";
			$(this.dom).append(html);
			this.options.editorcontainer = $(this.dom).find(".uieditorcontainer");
		}
	};
	this.render = function(){
		if( this.options.editor ){
			this.options.editor.destroyRecursive(false);
			this.options.editor = null;
		}
		var val = this.getSelectedValue();
		if( !val ){
			var v = this.getDataSource() || [];
			v = $.isArray(v)?v:[v];
			if( v.length > 0 ){
				val = v[0];
				val = self.getListItemValue(val);
			}
		}
		this.options.editor = new dijit.form.FilteringSelect({
			required: this.getProp("required"),
			value: val,
			placeHolder: this.getProp("placeholder"),
			onChange: (function(self){
				return function(v){
					self.options.data = this.get("value")||"";
					self.onValueChange();
				};
			})(this)
		}, $(this.options.editorcontainer)[0]);
	};
});
appdb.views.ui.editors.booleanlist = appdb.ExtendClass(appdb.views.ui.editors.combobox,"appdb.views.ui.editors.booleanlist",function(o){
	this.getDataSource = function(){
		return ["true","false"];
	};
	this.getDefaultValue = function(){
		return $.trim(this.getProp("default")) || "false";
	};
	this.getListItemDisplayValue = function(d){
		var v = this.getListItemValue(d);
		return $.trim(this.getProp(v+"value")) || v;
	};
	this.getListItemValue = function(d){
		if( $.trim(d) ){
			if( $.trim(d) === "true" ) return "true";
			return "false";
		}
		return this.getDefaultValue();
	};
	this.getSelectedValue = function(){
		return this.getListItemValue(this.getData());
	};
	this.getDisplayData = function(){
		this.getListItemDisplayValue(this.getData());
	};
	this.getEditedData = function(){
		var bind = this.getProp("bind");
		return appdb.utils.valueToObject(bind,this.getSelectedValue(),"val");
	};
});

appdb.views.ui.editors.Collection = appdb.ExtendClass(appdb.views.ui.editors.Generic,"appdb.views.ui.editors.Collection",function(o){
	this.options = $.extend(true,{ itemprops: null, items: [] },o);
	
	this.getItemIdentifier = function(item){
		return (item && item.id)?item.id:item;
	};
	
	this.remove = function(index){
		if( index >= 0 ) {
			if( this.options.items[index].unsubscribeAll ){
				this.options.items[index].unsubscribeAll();
			}
			
			if( this.options.items[index].reset ){
				this.options.items[index].reset();
			}
			
			this.options.items[index] = null;
			this.options.items.splice(index,1);
			
			$($(this.options.dom.list).children('li').get(index)).remove();
		}
		
		$(this.options.dom.list).children("li").each(function(i,e){
			$(e).data('index',i);
		});
		
		this.onValueChange();
		this.renderEmpty();
	};
	
	this.onValueChange = function(){
		var invalids = [];
		
		$.each(this.options.items, function(i,e){
			if( !e.isValid() ){
				invalids.push(e);
			}
		});
		this.options.isvalid = (invalids.length === 0)?true:false;
		this.publish({event: "changed", value: this.getEditedData()});			
	};
	
	this.isValid = function(){
		return this.options.isvalid;
	};
	
	this.addItem = function(e){
		var dom = $(this.options.templates.item).clone(true);
		var li = $('<li></li>');
		$(li).append(dom);
		var item = this.createEditorUI(li, e);
		
		if( !item ){
			return null;
		}
		
		this.options.items.push(item);
		$(li).data('index', this.options.items.length -1 );
		this.bindItemCommands(item, e, li);
		this.onItemValueChanged(this.getItemCurrentData(item),item,li);
		
		return li;
	};
	
	this.getItemCurrentData = function(item){
		var d = item.getEditedData();
		if( d && item.getProp('bind') ){
			d = d[item.getProp('bind')];
		}
		
		return d;
	};
	
	this.onItemValueChanged = function(value, item, dom){
		this.onValueChange();
	};
	
	this.bindItemCommands = function(item, data, el){
		$(el).find('[data-command="collection-item-remove"]').off('click').on('click', (function(self, item, data, dom){ 
			return function(ev){
				ev.preventDefault();
				self.remove( $(dom).data('index') );
				return false;
			};
		})(this, item, data, el));
		
		item.subscribe({event: 'changed', callback: function(v){
			this.onItemValueChanged(v, item, el);
		}, caller: this});
	};
	
	this.bindCommands = function(){	
		$(this.dom).find('[data-command="collection-item-new"]').off('click').on('click',  (function(self){ 
			return function(ev){
				ev.preventDefault();
				var li = self.addItem({});
				if( li ) {
					$(self.options.dom.list).append(li);
				}
				self.renderEmpty();
				return false;
			};
		})(this));
	};
	
	this.render = function(){
		var d = this.getData();
		if( !d ) return;
		d = $.isArray(d)?d:[d];
		
		this.initCollectionView(d);
		this.bindCommands();
		
		$.each(d,(function(self){
			return function(i, e) {
				var li = self.addItem(e);
				if( li !== null ) {
					self.options.dom.list.append(li);
				}
			};
		})(this));
		this.renderEmpty();
	};
	
	this._initContainer = function(){
		var html = $('<div></div>').html(this.options.innerHTML);
		if( !this.options.templates ) {
			this.options.templates = {
				item: $(html).find('.appdb-ui-collection-item-template').html()
			};
			$(this.dom).empty();
			$(this.dom).append('<ul class="collection-list"></ul>');
			this.options.dom = {
				list: $(this.dom).children('ul')
			};
		}	
		this.options.editorcontainer = this.options.dom;
	};
	
	this.getEditedData = function(){
		var d = {};
		var bind = this.getProp('item-bind');
		d[bind] = [];
		$.each(this.options.items, function(i,e){
			var ed = e.getEditedData();
			if( ed && ed[bind] ){
				d[bind].push(ed[bind]);
			}
		});
		
		return d;
	};
	
	appdb.views.ui.loadMixin(this,new appdb.views.ui.mixins.Collection());
});
appdb.views.ui.Templates = (function(){
	var _registry = {};
	function register(name,html){
		_registry[name] = $.trim(html);
	}
	function unregister(name){
		if( _registry[name] ){
			delete _registry[name];
		}
	}
	function get(name){
		return $.trim(_registry[name]);
	};
	function parse(dom, autoremove){
		autoremove = (typeof autoremove === "booelan")?autoremove:true;
		var found = [];
		$(dom).find("[data-template]").each(function(i,e){
			if( $.trim($(e).attr("data-template")) !== "" ){
				parse($(e),autoremove);
				_registry[$.trim($(e).attr("data-template"))] = $(e).html();
				found.push($(e));
			}
		});
		if( autoremove ){
			$(dom).find("[data-template]").remove();
		}	
	}
	return {
		register: register,
		unregister: unregister,
		get: get,
		parse: parse
	};
})();

appdb.views.ui.Databindable = appdb.ExtendClass(appdb.View, "appdb.views.ui.Databindable", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {}
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
	this.getId = function(){
		return (this.options.data)?this.options.data.id:"";
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
});
appdb.views.ui.DataEditable = appdb.ExtendClass(appdb.views.ui.Databindable, "appdb.views.ui.DataEditable", function(o){
	this.options = $.extend(true, this.options, {
		canedit: (typeof o.canedit === "boolean" )?o.canedit:false,
		edimode: (typeof o.edimode === "boolean" )?o.edimode:false,
		isvalid: false,
		editabledom: o.editabledom || this.dom
	});
	this.getData = function(){
		return this.options.data;
	};
	this.isValid = function(){
		return this.options.isvalid;
	};
	this.isEditMode = function(){
		return this.options.editmode;
	};
	this.canEdit = function(){
		return this.options.canedit;
	};
	this.validate = function(){
		if( this.isEditMode() ){
			var isvalid = false;
			var invalids = $.grep(this.getEditables(), function(e){
				return !e.isValid();
			}); 
			isvalid = (invalids.length===0)?true:false;
			
			if( isvalid === true ){
				for(var i in this.subviews){
					if( this.subviews.hasOwnProperty(i) 
						&& typeof this.subviews[i].isValid === "function" 
						&& this.subviews[i].isValid() === false ){
						invalids.push(this.subviews[i]);
					}
				}
			}
			isvalid = (invalids.length===0)?true:false;
			
			this.options.isvalid = isvalid;
			this.publish({event: "validation", value: isvalid || invalids});
			this.postValidate();
		}
	};
	
	this.getEditables = function(el){
		if( !this.options.editors || !this.options.editors.length){
			var elems = $(el || this.options.editabledom).find(".appdb-ui[data-editor]");
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
		var res = [];
		for(var i in this.subviews){
			if( this.subviews.hasOwnProperty(i) 
				&& typeof this.subviews[i].canEdit === "function" 
				&& typeof this.subviews[i].edit === "function" 
				&& this.subviews[i].canEdit() ){
				res.push(this.subviews[i]);
				
			}
		}
		return res;
	};
	this.getEditableItems = function(){
		var res = [];
		if( this.options.items && $.isArray(this.options.items) ){
			for(var i in this.options.items){
				if( this.subviews.hasOwnProperty(i) 
					&& typeof this.subviews[i].canEdit === "function" 
					&& typeof this.subviews[i].edit === "function" 
					&& this.subviews[i].canEdit() ){
					res.push(this.subviews[i]);
				}
			}
		}
		return res;
	};
	
	this.preSave = function(){
		//safely overridden
	};
	this.save = function(){
		this.preSave();
		this.onSave();
		this.postSave();
		//safely overridden
	};
	this.onSave = function(){
		//safely overridden
	};
	this.postSave = function(){
		//safely overridden	
	};
	this.preCancel = function(){
		//safely overridden
	};
	this.cancel = function(){
		this.revertData();	
		$(this.dom).removeClass("editmode");
	};
	this.onCancel = function(){
		//safely overridden	
	};
	this.postCancel = function(){
		//safely overridden
	};
	this.cancel = function(){
		this.resetEditors();
		this.reset();
		this.options.editmode = false;
		this.onCancel();
		this.postCancel();
	};
	this.remove = function(){
		this.preRemove();
		this.onRemove();
		this.postRemove();
		//safely overridden
	};
	this.preRemove = function(){
		//safely overridden
	};
	this.postRemove = function(){
		//safely overridden
	};
	this.preEdit = function(){
		//safely overridden
	};
	this.onEdit = function(el){
		//safely overridden
		$.each(this.getEditables(el), (function(self){
			return function(i,e){
				if( typeof e.bind === "function" ){
					e.bind(self.options.data);
				}
				if( typeof e.edit === "function" ){
					e.edit();
				}
				e.subscribe({event: "validation", callback: function(v){
					this.validate(v);
				}, caller: self});
			};
		})(this));
	};
	this.backupData = function(){
		if( $.isArray(this.options.originalData) ){
			this.options.originalData = appdb.utils.extednArray(this.options.data);
		}else{
			this.options.originalData = $.extend(true,{},this.options.originalData);
		}	
	};
	this.revertData = function(){
		this.options.data = this.options.originalData || ($.isArray(this.options.data)?[]:{});
	};
	this.edit = function(el){
		if( this.canEdit() === true ) {
			this.backupData();
			this.preEdit();
			$(this.dom).addClass("editmode");
			this.options.editmode = true;
			this.resetEditors();
			this.onEdit(el);
			this.postEdit();
			this.publish({event: "modechange", value: this});
			this.validate();
		}
	};
	this.postEdit = function(){
		//safely overridden
	};
	
	this.resetEditors = function(){
		this.options.editors = this.options.editors || [];
		this.options.editors = $.isArray(this.options.editors)?this.options.editors:[this.options.editors];
		$.each( this.options.editors, function(i,e){
			e.reset();
			e = null;
		});
		this.options.editors = [];
	};
	this.postValidate = function(){
		//safely overridden
	};
	this.getChanges = function(){
		var changes = [];
		if( this.isEditMode() ){
			changes = $.grep(this.getEditables(), function(e){
				return (e && e.hasChanges && e.hasChanges());
			});
		}
		return changes;
	};
	this.hasChanges = function(){
		return ( this.getChanges().length > 0 );
	};
	this.getEditedData = function(){
		var data = $.extend(true,{},this.options.data);
		var editables = this.getEditables();
		$.each(editables, function(i,e){
			var ed = e.getEditedData();
			for(var i in ed){
				if( ed.hasOwnProperty(i) ){
					data[i] = ed[i];
				}
			}
		});
		return data;
	};
});
