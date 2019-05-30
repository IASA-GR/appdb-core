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
jQuery.support.cors = true;
$(function(){
	jQuery.fn.filteringAutocomplete = $.fn.autocomplete;

	$.fn.tabs = (function(oldtabs) {
		return function(d, i){
			if (d === "remove" && i > -1) {
				// Remove the tab
				var tab = $(this).find(".ui-tabs-nav li:eq(" + i + ")" ).remove();
				// Find the id of the associated panel
				var panelId = tab.attr("aria-controls");
				// Remove the panel
				$("#" + panelId).remove();
				// Refresh the tabs widget
				return oldtabs.apply(this, ["refresh"]);
			} else {
				return oldtabs.apply(this, arguments);
			}
		}
	})($.fn.tabs);
});
window.userID = window.userID || null;
String.prototype.htmlEscape = function() {
    return $('<div/>').text(this.toString()).html();
};
appdb.debug = function(){
	if(appdb.config.appenv!=='production' && (typeof console !== "undefined" && typeof console.log !== "undefined") ){
		try{
			if( $.browser.msie === true ){
				//IE cannot wrap console logging
				console.log(Array.prototype.slice.call(arguments));
			}else{
				var log = Function.prototype.bind.call(console.log, console);
				log.apply(console, Array.prototype.slice.call(arguments));
			}
		}catch(e){
			
		}
	}
};
appdb.FindNS = function(nsname,create){
    create = create || false;
    var ns = window, i, nslen, nsitem=null;
	nsname = nsname || "";
    nsname = nsname.split(".");
    nslen = nsname.length;
    for(i=0; i<nslen; i+=1){
        nsitem = ns[nsname[i]];
        if(typeof nsitem === "undefined"){
            if(create===false){
                return null;
            }
            ns[nsname[i]] = function(){};
            nsitem = ns[nsname[i]];
        }
        ns = nsitem;
    }
    return ns;
};
appdb.FindData = function(data,nsname, value){
    var ns = data, i, nslen, nsitem=ns;
	nsname = nsname || "";
    nsname = nsname.split(".");
    nslen = nsname.length;
    for(i=0; i<nslen; i+=1){
		if( (i+1)===nslen ){
			if( typeof value !== "undefined" ){
				nsitem[nsname[i]] = value;
				return nsitem;
			}
		}
        if(typeof ns[nsname[i]] === "undefined" ){
			return null;
        }
		nsitem = ns[nsname[i]];
        ns = nsitem;
    }
    return ns;
};
appdb.DefineClass= function(name,obj,statics){
  var f = appdb.FindNS(name,true);
  f = function(){
      this._type_ = new appdb.Reflection({typename:name,base :null});
      obj.apply(this,arguments);
  };
  f.prototype.constructor = f;
  if(statics){
      for(var i in statics){
          f[i] = statics[i];
      }
  }
  return f;
};
appdb.Reflection = function(o){
    var name = o.typename || "", base = o.basetype || null;
    this.getName = function(){
        return name;
    };
    this.getBase = function(){
        return base;
    };
};
appdb.ExtendClass = function(base,name,obj,statics){
  var  basetype = (typeof base === "string")?appdb.FindNS(base, false):base;
  name = ''+name || "";
  if(basetype === null){
      alert("Could not extend object."+base+" not found");
      return null;
  }
  f = appdb.FindNS(name, true);
  var f = function(){
      base.apply(this,arguments);
      this._type_ = new appdb.Reflection({typename:name,basetype :this._type_});
      obj.apply(this,arguments);
      
  };
  f.prototype = base.prototype;
  f.prototype._super = base;
  f.prototype.constructor = f;
  statics = statics || {};
  for(var i in statics){
      f[i] = statics[i];
  }
  return f;
};
appdb.InheritView = function(target){
  if(target){
    var obj = new appdb.views.View(target), i;
    for(i in obj){
        target[i] = obj[i];
    }
  }
};
appdb.InheritTemplate = function(target,options){
  if(target){
    var obj = new appdb.Template(options), i;
    for(i in obj){
        target[i] = obj[i];
    }
  }
};
appdb.utils = {};

/*
 *Lookups for a variable in an object. 
 *Parameters are:
 * o   : the object to lookup
 * name: the path name of the variable to lookup
 * Usage examples:
 *  appdb.utils.lookupObject({var1: {var2: {var3: 'this is my value' }}},'var1.var2.var3');
 *will return 'this is my value'.
 *  appdb.utils.lookupObject(window,'appdb.utils.lookupObject');
 *will return this function.
 *  appdb.utils.lookupObject({var1: {var2: ['one','two']}},'var1.var2.1');
 *will return 'two'
 *  appdb.utils.lookupObject({var1: {var2: 'hello'}}, "var1...var2');
 *will ignore multiple dots and return 'hello'
 */
appdb.utils.lookupObject = function(o,name){
	var n = $.trim(name);
	if(n===""){
		return o;
	}
	n = (n.indexOf(".")>-1)?n.split("."):[n];
	var res = o[n[0]];
	var i, len = n.length, nv;
	if(len===1){
		return res;
	}
	for(i = 1; i<len; i+=1){
		nv = n[i];
		if(res.hasOwnProperty(nv) && $.trim(nv) !== ""){
			res = res[nv];
		}
	}
	return res;
};
appdb.utils.convert = (function(){
    var _localName = ($.browser.msie )?"baseName":"localName",_text = ($.browser.msie )?"text":"textContent";
    var _removeWhitespace = function(xml){
        var node = xml.documentElement,i;
        var whitespace = /^\s+$/;
        for (i=0; i < node.childNodes.length; i++){
            var current = node.childNodes[i];
            if (current.nodeType == 3 && whitespace.test(current.nodeValue)) {
                // that is, if it's a whitespace text node
                node.removeChild(current);
                i--;
            }
        }
        return xml;
    };
    var _toObject = function(o){
        if($.isXMLDoc(o)===false){
            o = $.parseXML(o);
        }
		return _objectify(o.documentElement || o);
    };
    var _objectify = function(el){
       var e = {}, attr = [],cn = [],tmpval = null,at=null,alen = 0,clen=0,i,c,ln, otmp = null;
       if(el.nodeType===3){//Text Node
           return el.nodeValue;
       }
       attr = el.attributes;
       alen = attr.length;
       for(i=0; i<alen; i+=1){
            at = attr[i];
            if(at.prefix==='xmlns'){
                continue;
            }
			if(at[_localName] === "nil" && $.trim(at.value).toLowerCase()==="true"){
				return null;
			}
            e[at[_localName]] = at.value;
       }
       cn = el.childNodes;
       clen = cn.length;
       for(i=0; i<clen; i+=1){
           c = cn[i];
           ln = c[_localName];
           if(c.nodeType===1){
			   if(c.childNodes.length>0 && c.childNodes[0].nodeType===3){
				   if( typeof c.normalize === "function" ){
					   c.normalize();
				   }
			   }
               if(c.childNodes.length===1 && c.childNodes[0].nodeType===3){
                  if(c.attributes.length===0 || (c.attributes.length===1 && c.attributes[0].prefix==='xmlns')){
					e[ln] = c.childNodes[0][_text];
					continue;
                  }
               }
               if(e[ln]){
                   if((e[ln] instanceof Array)===false){
                       tmpval = e[ln];
                       e[ln] = [];
                       e[ln].push(tmpval);
                   }
				   otmp = _objectify(c);
				   if(otmp!==null){
					   e[ln].push(otmp);
				   }
               }else{
				   otmp = _objectify(c);
				   if(otmp!==null){e[ln] = otmp;}
               }
            }
           if(c.nodeType===3){//Text Content
               ln = el[_localName];
               if(el.attributes.length>0 && ((el.attributes.length===1 && el.attributes[0].prefix==='xmlns' )===false)){
                   tmpval = c[_text];
                   e["val"] = function(){return tmpval;};
               }else{
                if(e[ln]){
                    if(e[ln] instanceof Array===false){
                        tmpval = e[ln];
                        e[ln] = [];
                        e[ln].push(tmpval);
                    }
                    e[ln].push(c[_text]);
                }else{
                    e[ln] = c[_text];
                }
               }
           }           
       }
       if($.isEmptyObject(e)){
           return '';
       }
       return e;
    };
    return {
        toObject : _toObject
    };
})();

appdb.utils.base64 = (function(){
	/**
	*  Base64 encode / decode
	*  Author: http://www.webtoolkit.info/
	**/
	var Base64 = {
	  // private property
	  _keyStr : "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
	  // public method for encoding
	  encode : function (input) {
	    var output = "";
	    var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
	    var i = 0;
	    
	    if ($.isPlainObject(input) ) {
		input = JSON.stringify(input);
	    }
	    
	    input = Base64._utf8_encode(input);

	    while (i < input.length) {
		chr1 = input.charCodeAt(i++);
		chr2 = input.charCodeAt(i++);
		chr3 = input.charCodeAt(i++);
		enc1 = chr1 >> 2;
		enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		enc4 = chr3 & 63;

		if (isNaN(chr2)) {
		    enc3 = enc4 = 64;
		} else if (isNaN(chr3)) {
		    enc4 = 64;
		}

		output = output +
		Base64._keyStr.charAt(enc1) + Base64._keyStr.charAt(enc2) +
		Base64._keyStr.charAt(enc3) + Base64._keyStr.charAt(enc4);
	    }
	    return output;
	  },
	  // public method for decoding
	  decode : function (input) {
	    var output = "";
	    var chr1, chr2, chr3;
	    var enc1, enc2, enc3, enc4;
	    var i = 0;

	    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

	    while (i < input.length) {
		enc1 = Base64._keyStr.indexOf(input.charAt(i++));
		enc2 = Base64._keyStr.indexOf(input.charAt(i++));
		enc3 = Base64._keyStr.indexOf(input.charAt(i++));
		enc4 = Base64._keyStr.indexOf(input.charAt(i++));
		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;
		output = output + String.fromCharCode(chr1);

		if (enc3 != 64) {
		    output = output + String.fromCharCode(chr2);
		}

		if (enc4 != 64) {
		    output = output + String.fromCharCode(chr3);
		}
	    }
	    output = Base64._utf8_decode(output);

	    return output;
	  },
	  // private method for UTF-8 encoding
	  _utf8_encode : function (string) {
	    string = string.replace(/\r\n/g,"\n");
	    var utftext = "";

	    for (var n = 0; n < string.length; n++) {
		var c = string.charCodeAt(n);
		if (c < 128) {
		    utftext += String.fromCharCode(c);
		}
		else if((c > 127) && (c < 2048)) {
		    utftext += String.fromCharCode((c >> 6) | 192);
		    utftext += String.fromCharCode((c & 63) | 128);
		}
		else {
		    utftext += String.fromCharCode((c >> 12) | 224);
		    utftext += String.fromCharCode(((c >> 6) & 63) | 128);
		    utftext += String.fromCharCode((c & 63) | 128);
		}
	    }
	    return utftext;
	  },
	  // private method for UTF-8 decoding
	  _utf8_decode : function (utftext) {
	    var string = "";
	    var i = 0;
	    var c = c1 = c2 = 0;

	    while ( i < utftext.length ) {
		c = utftext.charCodeAt(i);
		if (c < 128) {
		    string += String.fromCharCode(c);
		    i++;
		}
		else if((c > 191) && (c < 224)) {
		    c2 = utftext.charCodeAt(i+1);
		    string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
		    i += 2;
		}
		else {
		    c2 = utftext.charCodeAt(i+1);
		    c3 = utftext.charCodeAt(i+2);
		    string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
		    i += 3;
		}
	    }
	    return string;
	  }
	};

	return {
	    encode: Base64.encode,
	    decode: Base64.decode
	};
})();

appdb.utils.noFunc = function(){};

appdb.utils.identFunc = function(d){return d;};   

appdb.utils.notification  = function(options) {
	this.close = function() {
		if ( this._dlg ) this._dlg.onCancel();
	};
	this._init = function() {
		options = options || {};
		var delay = options.delay || 5000;
		var dlg = new dojox.Dialog({
			"title": options.title || "Notification",
			"style": options.style || "min-width: 100px",
			"content": options.message || "Message",
			onCancel : function(){
				this.destroyRecursive(false);
			}
		});
		dlg.show();
		setTimeout(function(){
			$(dlg.domNode).fadeOut();
		}, delay - 1500);
		setTimeout(function(){
			dlg.onCancel();
		}, delay);
		this._dlg = dlg;
	};
	this._init();
};

appdb.utils.filterFields = (function(){
	var _resources = {"object": {}, "list": {}};
	return function() {
		this.setLocalList = function(d,base,field){
			var i, j, items = [];
			if ( d ) {
				if ( d.filter ) {
					if ( d.filter.field ) {
						if ( ! $.isArray(d.filter.field) ) d.filter.field = [d.filter.field];
						for (i = 0; i < d.filter.field.length; i += 1) {
							if ( d.filter.field[i].name === field ) {
								d = d.filter.field[i];
								if ( ! $.isArray(d.field) ) d.field = [d.field];
								for (j = 0; j < d.field.length; j += 1) {
									items.push(d.field[j].name);
								}
								break;
							}
						}
					}
				}
			}
			if ( items.length === 0 ) items = ["any"];
			this._filterFields = items;
			_resources["list"][(base || "") + "$" + (field || "")] =  items;
		};
		this.getLocalList = function(base, field){
			var _local = _resources["list"][(base || "") + "$" + (field || "")];
			if(_local && $.isArray(_local)){
				return _local;
			}
			return;
		}
		this.setLocalObject = function(d,base,field){
			var i, j, items = [];
			if ( d ) {
				if ( d.filter ) {
					if ( d.filter.field ) {
						if ( ! $.isArray(d.filter.field) ) d.filter.field = [d.filter.field];
						for (i = 0; i < d.filter.field.length; i += 1) {
							if ( d.filter.field[i].name === field ) {
								d = d.filter.field[i];
								if ( ! $.isArray(d.field) ) d.field = [d.field];
								for (j = 0; j < d.field.length; j += 1) {
									items.push({"name": d.field[j].name, "id": d.field[j].name, "type": d.field[j].type});
								}
								break;
							}
						}
					}
				}
			}
			if ( items.length === 0 ) items = ["any"];
			this._filterFields = items;
			_resources["object"][(base || "") + "$" + (field || "")] =  items;
		};
		this.getLocalObject = function(base, field){
			var _local = _resources["object"][(base || "") + "$" + (field || "")];
			if(_local && $.isArray(_local)){
				return _local;
			}
			return;
		}
		this.getObject = function(base, field) {
			var _local = this.getLocalObject(base, field);
			if(_local){
				return _local;
			}
			var d;
			d = new appdb.utils.rest({
				endpoint: appdb.config.endpoint.proxyapi+"?version=" + appdb.config.apiversion + "&resource="+base+"/filter/reflect",
				async: false
			}).create({}).call();
			this.setLocalObject(d,base,field);
			
			return _resources["object"][(base || "") + "$" + (field || "")];
		};

		this.getList = function(base, field){
			var _local = this.getLocalList(base, field);
			if(_local){
				return _local;
			}
			var d;
			d = new appdb.utils.rest({
				endpoint: appdb.config.endpoint.proxyapi+"?version=" + appdb.config.apiversion + "&resource="+base+"/filter/reflect",
				async: false
			}).create({}).call();
			this.setLocalList(d,base,field);
			return _resources["list"][(base || "") + "$" + (field || "")];
		};
	};
})();

appdb.utils.formatDate = function(d) {
	var date, time;
	date = $.datepicker.formatDate('D, d M yy',new Date(d));
	time = d.split("T")[1].split(".")[0];
	time = time.split(":")[0] + ":" + time.split(":")[1];
	return date+', '+time;
};

appdb.utils.broker = function(async) {
	var _requests = [];
	var _res = [];
	var _async = (typeof async === "boolean")?async:false;
	var _request = function(e) {
		if( $.isArray(e) ){
			_requests = _requests.concat(e);
		}else{
			_requests.push(e);	
		}
		return this;
	};

	var _fetch = function(_success, _error) {
		var xml = '';
		var q;
		for (var i = 0; i < _requests.length; i++) {
			q = _requests[i];
			xml += '<appdb:request id="' + q.id + '"';
			xml += ' method="' + q.method + '" resource="' + q.resource + '"';
			if ( q.username ) xml += ' username="' + q.username + '"'; 
			if ( q.userid ) xml += ' userid="' + q.userid + '"'; 
			if ( q.passwd ) xml += ' passwd="' + q.passwd + '"'; 
			if ( q.apikey ) xml += ' apikey="' + q.apikey + '"'; 
			xml += '>';
			if ( q.param ) {
				if ( ! $.isArray(q.param) ) q.param = [q.param];
				for (var j = 0; j < q.param.length; j++) {
					xml += '<appdb:param name="' + q.param[j].name + '">' + String(q.param[j].val).htmlEscape() + '</appdb:param>';
				}
			}
			xml += '</appdb:request>';
		}
		xml = '<appdb:broker xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:appdb="http://appdb.egi.eu/api/' + appdb.config.apiversion + '/appdb">' + xml + '</appdb:broker>';
		var xhr = {
			type: 'POST',
			url: appdb.config.endpoint.proxyapi,
			async: _async,
			data: "version=" + appdb.config.apiversion + "&resource=broker&data="+encodeURIComponent(xml),
			dataType: "xml",
		    success: function(d, s, o) {
				var res = appdb.utils.convert.toObject(d);
				if ( typeof _success !== "undefined" ) _success(res);
			},
			error: function(d, s, o) {
				if ( typeof _error !== "undefined" ) _error(res);
			}
		};
		return $.ajax(xhr);
	};

	return {
		request: _request,
		fetch: _fetch
	};
};

appdb.utils.rest = function(options){
   var _options =  {
       action : 'GET',
       endpoint : '',
	   authorization : {
		   type : appdb.utils.rest.authorization.types.none,
		   mode : appdb.utils.rest.authorization.modes.never
	   },
           iscrossdomain : true
   };
   var _setupCallbacks = function(c){
        var res = {
            async:true,
            success : appdb.utils.noFunc,
            error : appdb.utils.noFunc
        };
        if(typeof c === 'undefined'){
            res.async = false;
        }else if($.isFunction(c)){
            res.success = c;
            res.error = c;
        }else if($.isPlainObject(c)){
            if(c.success){
                res.success = c.success;
            }
            if(c.error){
                res.error = c.error;
            }
        }
        return res;
   };
   var _setupData = function(p){
        var res = {query : {}, data : {}};
        res.query = ((p)?(p.query||p):{});
        res.data = ((p)?(p.data||{}):{});
        return res;
   };
   var _makeDataString = function(p){
        var res = "";
        for(var i in p){
            res += i + "=" +p[i] + "&";
        }
        return res.substring(0,res.length-1);
   };
   var _parseParams = function(p){
       var addr = _options.endpoint, unused = {}, i =0;
       //Check for unused data in url
       for(i in p){
		   if(i==="userid" || i==="passwd"){
			   continue;
		   }
            if(addr.indexOf("{"+i+"}")===-1){
				unused[i] = encodeURIComponent(p[i]);
            }
       }
       //Replace used data in url
       for(i in p){
           while(addr.indexOf("{"+i+"}")>-1){
                addr = addr.replace("{"+i+"}",encodeURIComponent(p[i]));
           }
       }
       //If url template has * then create query string
       //with the unused data and append to url
       if(addr.indexOf("{*}")!==-1){
           i = encodeURIComponent(_makeDataString(unused));
           addr = addr.replace("{*}",i);
       }else if(addr.indexOf("{#}")!==-1){
		   i = _makeDataString(unused);
           addr = addr.replace("{#}",i);
	   }
	   return addr;
   };
   var _create = function(p,clbcks){
       return new function(){
           var ajx = {};
           var params = {};
           var res = null;
		   var currentxhr = null;
           var _init = function(){
                var c = _setupCallbacks(clbcks), d = _setupData(p);
                params = d;
                ajx.url = _parseParams(d.query);
                ajx.type = _options.action;
				ajx.dataType = "xml";
                if(ajx.type !== "GET" && ajx.type !== "DELETE"){
                    ajx.data = _makeDataString(d.data);
                }
                ajx.async = c.async;
                ajx.success = function(d,s,o){
					if( (o && o.isAborted === true) ||  (ajx && ajx.isAborted === true)){
						o.isAborted = false;
						ajx.isAborted = false;
						return;
					}
                    res = appdb.utils.convert.toObject(d);
                    if(ajx.__timestart__){
                        res["__time_elapsed__"] = new Date().getTime() - ajx.__timestart__;
                        delete ajx.__timestart__;
                    }
                    c.success(res);
                };
                ajx.error = function(jqXHR, textStatus, errorThrown){
                    res = {status:textStatus,description:errorThrown};
					if( (jqXHR && jqXHR.isAborted === true) ||  (ajx && ajx.isAborted === true)){
						jqXHR.isAborted = false;
						ajx.isAborted = false;
						return;
					}
					if(jqXHR){
						if( jqXHR.isAborted === true ){ 
							jqXHR.isAborted = false; 
							appdb.debug("Aborted!!! READYSTATE: " + jqXHR.readyState);
							return; 
						}
						
						if(jqXHR.responseText){
							res["responseText"] = jqXHR.responseText;
						}
						if(jqXHR.responseXML){
							res["responseXML"] = jqXHR.responseXML;
							try{
								res["response"] = appdb.utils.convert.toObject(jqXHR.responseXML);
							}catch(e){
								res["response"] = jqXHR.responseText || "";
							}							
						}
					}
                    if(ajx.__timestart__){
                        delete ajx.__timestart__;
                    }
					c.error(res);
                };
                ajx.withCredentials = true;
				ajx.isAborted = false;
           };
           var _reparse = function(p,a){
               var res = a;
                if(p.data){
                    p.data = $.extend(params.data,p.data);
					res.data = (a.type==="PUT")?p.data.data:_makeDataString(p.data);
                }
                if(p.query){
                    p.query = $.extend(params.query,p.query);
                    res.url = _parseParams(p.query);
                }
                return res;
           };
           var _call = function(p){
               var a = ajx;
                res = null;
                if(typeof p !== "undefined"){
                    a = _reparse(p,ajx);
                }
                a.__timestart__ = new Date().getTime();
				if(_options.authorization.mode.canUseAuthorization()){
					ajx = _options.authorization.type.setCredentials(ajx);
				}
                
				res = $.ajax(a);
				if( a.async ){
					currentxhr = res;
				}else{
					currentxhr = null;
				}
				res = (a.async)?null:appdb.utils.convert.toObject(res.responseXML);
                
                return res;
           };
		   var _getXhr = function(){
			   return currentxhr;
		   };
           var _getQuery = function(){
                return p.query;
           };
           var _getData = function(){
                return p.data;
           };
           _init();
           return {
               call:_call,
               getQuery : _getQuery,
               getData : _getData,
			   getXhr: _getXhr
            };
       };
   };
   var _init = function(o){
       _options =$.extend(_options,o);
   };
   _init(options);
   return {
       create : _create
   };
};

appdb.utils.rest.authorization = {
	types : {
		none : {
			setCredentials : function(o){
				return o;
			}
		},
		query : {
			setCredentials : function(o){
				var u = o.url;
				if(u.substr(0,5)==="http:"){
					u = "https" + u.substr(4,u.length);
				}
				o.url = u;
				return o;
			}
		}
	},
	modes : {
		never : {
			canUseAuthorization : function(o){
				return false;
			}
		},
		always : {
			canUseAuthorization : function(o){
				return true;
			}
		},
		authonly : {
			canUseAuthorization : function(o){
				return (userID!==null);
			}
		}
	}
};

appdb.utils.LocalDataStore = function(options){
     var _options =  {
       localData : []
   };
   var _setupCallbacks = function(c){
        var res = {
            async:true,
            success : appdb.utils.noFunc,
            error : appdb.utils.noFunc
        };
        if(typeof c === 'undefined'){
            res.async = false;
        }else if($.isFunction(c)){
            res.success = c;
            res.error = c;
        }else if($.isPlainObject(c)){
            if(c.success){
                res.success = c.success;
            }
            if(c.error){
                res.error = c.error;
            }
        }
        return res;
   };
   var _setupData = function(p){
        var res = {query : {}, data : {}};
        res.query = ((p)?(p.query||p):{});
        res.data = ((p)?(p.data||{}):{});
        return res;
   };
   var _makeDataString = function(p){
        var res = "";
        for(var i in p){
            res += i + "=" +p[i] + "&";
        }
        return res.substring(0,res.length-1);
   };
   var _parseParams = function(p){
       var addr = _options.endpoint, unused = {}, i =0;
       if(typeof addr === "undefined"){
           return "";
       }
       //Check for unused data in url
       for(i in p){
            if(addr.indexOf("{"+i+"}")===-1){
                unused[i] = p[i];
            }
       }
       //Replace used data in url
       for(i in p){
           while(addr.indexOf("{"+i+"}")>-1){
                addr = addr.replace("{"+i+"}",p[i]);
           }
       }
       //If url template has * then create query string
       //with the unused data and append to url
       if(addr.indexOf("{*}")!==-1){
           i = _makeDataString(unused);
           addr = addr.replace("{*}",i);
       }
       return addr;
   };
   var _create = function(p,clbcks){
       return new function(){
           var ajx = {};
           var params = {};
           var res = null;
           var _init = function(){
                var c = _setupCallbacks(clbcks), d = _setupData(p);
                params = d;
                ajx.url = _parseParams(d.query);
                ajx.type = _options.action;
                if(ajx.type !== "GET" && ajx.type !== "DELETE"){
                    ajx.data = _makeDataString(d.data);
                }
                ajx.async = c.async;
                ajx.success = function(d,s,o){
                    res = appdb.utils.convert.toObject(d);
                    if(ajx.__timestart__){
                        res["__time_elapsed__"] = new Date().getTime() - ajx.__timestart__;
                        delete ajx.__timestart__;
                    }
                    c.success(res);
                };
                ajx.error = function(jqXHR, textStatus, errorThrown){
                    res = {status:textStatus,description:errorThrown};
                    if(ajx.__timestart__){
                        res["__time_elapsed__"] = new Date().getTime() - ajx.__timestart__;
                        delete ajx.__timestart__;
                    }
                    c.error(res);
                };
                ajx.crossDomain = true;
                ajx.xhrFields= {withCredentials: true};
                ajx.xhr = function(){
                    var xhr = new XMLHttpRequest();
                    if ("withCredentials" in xhr){
                        return xhr;
                    } else if (typeof XDomainRequest !== "undefined" && $.browser.msie && $.browser.version == "8.0"){
                        xhr = new XDomainRequest();
                        xhr.onload = function() {
                          ajx.success(xhr.responseText);
                        };
                        xhr.onerror = function(){
                            ajx.error(xhr,"Error","An error occured on cross domain request");
                        };
                        return xhr;
                    }
                    return xhr;
                };
           };
           var _reparse = function(p,a){
               var res = a;
                if(p.data){
                    p.data = $.extend(params.data,p.data);
                    res.data = _makeDataString(p.data);
                }
                if(p.query){
                    p.query = $.extend(params.query,p.query);
                    res.url = _parseParams(p.query);
                }
                return res;
           };
           var _call = function(p){
               var a = ajx;
                res = null;
                if(typeof p !== "undefined"){
                    a = _reparse(p,ajx);
                }
                a.__timestart__ = new Date().getTime();
                res = _options.localData;
                if(a.async){
                    a.success(res);
                }
                return res;
           };
           var _getQuery = function(){
                return p.query;
           };
           var _getData = function(){
                return p.data;
           };
           _init();
           return {
               call:_call,
               getQuery : _getQuery,
               getData : _getData
            };
       };
   };
   var _init = function(o){
       _options =$.extend(_options,o);
   };
   _init(options);
   return {
       create : _create
   };
};

appdb.utils.ObserverMediator = function(p){
    var events = {}, _batch = [], _storeEvents = false, _parent = p || null;this.getRaw = function(){return events;};
    this.subscribe = function(o){
        o.caller = o.caller || _parent;
        events = events || {};
        events[o.event] = events[o.event] || [];
        events[o.event].push({callback:o.callback,caller : o.caller});
        return p;
    };
    this.unsubscribe = function(o){
        var i,e = events[o.event] || [],len = e.length;
        o.caller = o.caller || _parent;
        for(i=0; i<len; i+=1){
            if(e[i].caller){
                if(e[i].caller===o.caller){
                    events[o.event].splice(i,1);
                    break;
                }
            }
        }
        return p;
    };
    this.unsubscribeAll = function(clr){
        var i, e = events ;
        clr = clr || _parent;
        for(i in e){
           this.unsubscribe({event: i,caller : clr});
        }
        return p;
    };
    this.publish = function(o){
        if(_storeEvents){
            _batch[_batch.lengh] = o;
        }
        var i , clbcks = ((o.event && events[o.event])?events[o.event]:[]), args = o.value,len = clbcks.length,caller;
        if(clbcks){
           for(i=0; i<len; i+=1){
			   if(clbcks[i]){
				caller = clbcks[i].caller || _parent;
				clbcks[i].callback.apply(caller,[args]);
			   }
           }
        }
    };
    this.clearAll = function(){
        events = {};
        return p;
    };
    this.suspend = function(storeEvents){
        _storeEvents = storeEvents || false;
    };
    this.resume = function(publishEvents){
        publishEvents = publishEvents || false;
        if(publishEvents===false){
            _batch = [];
        }
        for(var i =0; i<_batch.length; i+=1){
            _publish(_batch[i]);
        }
    };
};

appdb.utils.Pager = function(opts){
    opts = opts || {};
    var _mediator = new appdb.utils.ObserverMediator();
    var _data = null;
    var o = {
        length : (1+(1*optQueryLen)) || 10,
        offset : 0,
        count : -1,
        model : opts.model,
        lengthProperty : 'pagelength',
        offsetProperty : 'pageoffset',
        countProperty : 'count'
    };
	this.setPagingData = function(pd){
		o.length = pd.length || o.length;
		o.offset = pd.offset || o.offset;
		o.count = pd.count || o.count;
	};
    this.next = function(){
        var pn = this.pageNumber();
        if(pn<this.pageCount()-1){
            this.current(pn+1);
        }
    };
    this.hasNext = function(){
        var pn = this.pageNumber();
        if(pn<this.pageCount()-1){
            return true;
        }
        return false;
    };
    this.previous = function(){
         var pn = this.pageNumber();
        if(pn>0){
            this.current(pn-1);
        }
    };
    this.current = function(n){
        var pn = 0;
        if(typeof n !== "undefined"){
            _gotoPage(n);
            return this;
        }
        pn = this.pageNumber();
        if(pn<1){
            _gotoPage(0);
            return this;
        }else {
            _gotoPage(this.pageNumber());
            return this;
        }
    };
    this.count = function(n){
        return opts.count;
    };
    this.length = function(n){
        if(n){
            opts.length = n;
            return this;
        }
        return opts.length;
    };
    this.offset = function(n){
        if(n){
            opts.offset = n;
            return this;
        }
        return opts.offset;
    };
    this.getCurrentPagingState = function(){
       return {length : o.length,offset:o.offset,count:o.count,pagenumber : this.pageNumber(),pagecount : this.pageCount(),hasnext: this.hasNext};
    };
    this.pageNumber = function(){
        if(o.count==0){
            return 0;
        }
        return Math.floor(o.offset/(o.length));
    };
    this.pageCount = function(){
        return Math.ceil((o.count)/(o.length));
    };
    var _onSelect = function(d){
        _data = d;
        if(typeof _data.error === "string"){
            _mediator.publish({event : 'error' , value : _data});
            return;
        }
        o.length = 1*(d[o.lengthProperty]);
        o.offset = 1*d[o.offsetProperty];
        o.count = 1*d[o.countProperty];

        var ev = {
            pager : {
                length : 0+o.length,
                offset : 0+o.offset,
                count : 0+o.count,
                pageNumber : 0+this.pageNumber(),
                pageCount : 0+this.pageCount(),
                hasNext : this.hasNext()
            },
            data : null
        };
        ev.elapsed = _data.__time_elapsed__ || -1;
        if(o.modelProperty){
		   var props = [];
		   if(o.modelProperty.indexOf(".")>-1){
			   props = o.modelProperty.split(".");
		   }else{
			   props = [o.modelProperty];
		   }
		   var datares = _data[props[0]];
		   for(var i=1; i<props.length; i+=1){
			 if($.isArray(datares)===true){
				 for(var j=0; j<datares.length; j+=1){
					datares[j]= datares[j][props[i]];
				 }
			 }else{
				 datares = datares[props[i]];
			 }
		   }
		   ev.data = datares;

           if(typeof ev.data === "undefined"){
               ev.data = [];
           }
        }else{
            ev.data = _data;
        }
        _mediator.publish({event : 'pageload', value : ev});
    };
    var _onError = function(d){
        _mediator.publish({event : 'error' , value : d});
    };
    var _gotoPage = function(n){
        appdb.pages.index.requests.cancel("paging");
        appdb.pages.index.requests.register(o.model, "paging");
        _calcPageOffset(n);
        o.model.get(_buildPageQuery(o.length,o.offset));
    };
    var _calcPageOffset = function(n){
        n = parseInt(n);
      o.offset=(n*(o.length));
    };
    var _buildPageQuery = function(len,ofs){
        var res = {};
        res[o.lengthProperty] = len;
        res[o.offsetProperty] = ofs;
        return res;
    };
    this.subscribe = function(e){
        _mediator.subscribe(e);
        return this;
    };
    this.unsubscribe = function(e){
       _mediator.unsubscribe(e);
       return this;
    };
    this.unsubscribeAll = function(e){
        _mediator.unsubscribeAll(e);
        return this;
    };
    this.destroy = function(){
        o.model.unsubscribeAll(this);
        _mediator.clearAll();
    };
    var _init = function(){
       o = $.extend(o,opts);
    };
    _init();
    o.model.subscribe({event : 'select',callback: _onSelect,caller : this}).subscribe({event : 'error',callback:_onError,caller : this});
};

appdb.utils.animateList = function(){
    if ( $.browser.msie === false ) {
        if ($("#viewtype")[0] !== undefined) {
            if ($("#viewtype")[0].value === "app" ) list = dojo.query("#appmainlist li");
            if ($("#viewtype")[0].value === "ppl" ) list = dojo.query("#pplmainlist li");
            if ($("#viewtype")[0].value === "vos" ) list = dojo.query("#vosmainlist li");
            if ($("#viewtype")[0].value === "ngi" ) list = dojo.query("#ngimainlist li");
        }
        if ( list != null ) {
            var props = {
                i: {width:96, height:96, top:-16, left:-102},
                o: {width:64, height:64, top:0, left:-80}
            };
            list.forEach(function(n){
                var img = dojo.query("img", n)[0], a;
                dojo.connect(n, "onmouseenter", function(e){
                        a && a.stop();
                        a = dojo.anim(img, props.i, 175);
                });
                dojo.connect(n, "onmouseleave", function(e){
                        a && a.stop();
                        a = dojo.anim(img, props.o, 175, null, null, 75);
                });
            });
        }
    }
};
/*
 * Change the ISO Date format YYYY-MM-DD to DD-m-YYYY
 */
appdb.utils.FormatISODate = function(dt){
	if(typeof dt !== "string"){
		return dt;
	}
	var d = dt.split("-"), months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"], time='';
	if(d.length<3){
		return dt;
	}
	if(d[2].indexOf(" ")>-1){
		var tmp = d[2].split(" ");
		d[2] = tmp[0];
		time = tmp[1];
	}
	if(d[1][0]==="0"){
		d[1] = d[1].slice(1,2);
	}
	var res = d[2] + "-" + months[parseInt(d[1])-1] + "-" + d[0] + ((time!='')?" "+time:"");
	return res;
};

appdb.utils.ToggleFaq = function(anchor){
	if(typeof anchor === "number"){
		ajaxLoad("/help/faq","main","toggleFAQ($('#faq"+anchor+"'));");
	}else{
		ajaxLoad("/help/faq","main");
	}
};

appdb.utils.Property = function(o){
	o = o || {};
	if(typeof o.parent === "undefined" || o.parent === null){
		return ;
	}
	return (function(o,undefined){
		var _val = o.value || undefined, _isProtected = o.isProtected || false;
		return function(v){
			if(v && (_isProtected && o.parent!==arguments.callee.caller)===false){ 
				_val = v;
			}
			return _val;
		};
	})(o);
};
/*
 * Executes a function when loading is finished. Loading is determined by a provided function.
 * parameters:
 * f : function to execute once loading is complete. If no function given, exits.
 * ext : object with extended parameters.
 * ext.caller : the object that made the call. It is passed in the provided function as 'this'. Default: null
 * ext.args : array of arguments for the executed method. Default: empty array
 * ext.checker : function to check whether the loading is complete. Return true on load, false otherwise. DEfault : no checking
 * ext.time : time interval to run checker. Default: 1000ms
 * ext.id : Unique id for the type of request. If given it will cancel any following requests with the same id until it completes
 * ext.count : Maximum time of checks before exiting. Default : 20
 */
appdb.utils.ExecuteOnLoad = function(f,ext){
	var o = ext || {};
	if($.isFunction(f) === false){
		return;
	}
	if(typeof o.caller !== "object"){
		o.caller = null;
	}
	if(typeof o.args !== "undefined"){
		if($.isArray(o.args) === false){
			o.args = [o.args];
		}
	}else{
		o.args = [];
	}
	if(typeof o.time === "string"){
		o.time = parseInt(o.time);
	}
	if(typeof o.time !== "number"){
		o.time = 1000;
	}
	if(typeof o.count === "string"){
		o.count = parseInt(o.count);
	}
	if(typeof o.count !== "number"){
		o.count = 20;
	}
	if($.isFunction(o.checker) === false ){
		o.checker = function(){return true;};
	}
	if(typeof o.id !== "undefined"){ //check if another request of the same type is running
		appdb.utils.ExecuteOnLoad.States =  appdb.utils.ExecuteOnLoad.States || {};
		if(appdb.utils.ExecuteOnLoad.States[o.id]){
			return;
		}
		appdb.utils.ExecuteOnLoad.States[o.id] = true;
	}
	var exec_callback = function(){};
	exec_callback = (function(caller,obj){
		return function(){
			if(obj.checker.apply(obj.caller)){//if content is loaded execute callback function
				setTimeout(function(){caller.apply(obj.caller,obj.args);},1);
				if(obj.id && appdb.utils.ExecuteOnLoad.States[obj.id]){ // if id is given, clear running flag for others to make requests.
					appdb.utils.ExecuteOnLoad.States[obj.id] = false;
					delete appdb.utils.ExecuteOnLoad.States[obj.id];
				}
			}else{
				if(obj.count===0){//if exceeded time of tries exit;
					return;
				}
				obj.count-=1;
				setTimeout(exec_callback,obj.time);
			}
		};
	})(f,o);
	setTimeout(exec_callback,o.time);//initial call
};
/*
 * Groups an object list according to value of the given property 'p'
 * for each object contained in the given list 'a'. If an object don't have
 * the property, it won't be included in return object.
 */
appdb.utils.GroupObjectList= function(a,p,order){
	if(typeof p !== "string"){
		return null;
	}
	if($.isArray(a)===false){
		return null;
	}
	if(order === true){
		a.sort(function(v1,v2){
			if(v1[p] < v2[p] ){
				return -1;
			}
			if(v1[p] > v2[p]){
				return 1;
			}
			return 0;
		});
	}
	var i,j, len = a.length,res = {};
	if ( $.isArray(order)){
		var inArray = function(arr,val){
			var i=0;len = arr.length;
			for(i=0; i<len; i+=1){
				if( arr[i].toLowerCase() === val.toLowerCase() ){
					return true;
				}
			}
			return false;
		};
		for(j=0; j<order.length; j+=1){
			for(i=0; i<len; i+=1){
				if( a[i] && typeof a[i][p] === "string" ){
					if( order[j].toLowerCase() === a[i][p].toLowerCase() || (order[j] === "*" && inArray(order,a[i][p])===false) ){
						res[a[i][p]] = res[a[i][p]] || [];
						res[a[i][p]][res[a[i][p]].length] = a[i];
					}
				}
			}
		}
	}else{
		for(i=0; i<len; i+=1){
			if(a[i] && typeof a[i][p] === "string"){
				res[a[i][p]] = res[a[i][p]] || [];
				res[a[i][p]][res[a[i][p]].length] = a[i];
			}
		}
	}
	
	return res;
};

/*
 *Creates and returns an array handler of unique objects.
 *Options object schema:
 *data : [Array] Optional. An array of objects.Defaults to an empty array.
 *property : [String] Optional. The property which identifies the uniqness of
 *					  each object in the array. Defaults to 'id'.
 */
appdb.utils.UniqueDataList = function(o){
	this._constructor = function(){
		o = o || {};
		var items = (o.data || []), p = (o.property || "id");
		var _add = function(e){
				if(_indexOf(e)<0){
					items[items.length] = e;
				}
		},
		_remove = function(e){
			var index = _indexOf(e);
			if(index>-1){
				items.splice(index,1);
			}
		},
		_indexOf = function(e){
			for(var i =0; i<items.length; i+=1){
				if(items[i][p] == e[p]){
					return i;
				}
			}
			return -1;
		},
		_get = function(){
			return items;
		};
		_clear  = function(){
			items = [];
		};
		return {
			add : _add,
			remove : _remove,
			get : _get,
			clear : _clear,
			has : function(e){return (_indexOf(e)>-1)?true:false;}
		};
	};
	return this._constructor();
};
appdb.utils.UniqueOrderedArray = function(arr){
	arr = arr || [];
	arr = $.isArray(arr)?arr:[arr];
	if( arr.length < 2) return arr;
	var uniq = {};
	var res = [];
	$.each(arr, function(i,e){
			if( uniq[e] ) return;
			res.push(e);
			uniq[e] = true;
	});
	return res;
};
/*
 *Appdb priveleges wrapper object. It provides a set of function to check
 *if a user has permissions. The options object is the 'action' object provided
 *by the server json response.5
 */
appdb.utils.PermissionActions = (function(){
	var _actions = {
		"GrantPrivilege": {id:1, group:["full"], name: "Grant/Revoke privilege" },
		"RevokePrivilege": {id:1, group:["full"], name: "Grant/Revoke privilege" },
		"InsertApplication": {id:3, group:[], name: "Insert software" },
		"DeleteApplication": {id:4, group:[], name: "Delete software" },
		"ChangeApplicationName": {id:5, group: ["full", "metadata"], name: "Change software name" },
		"ChangeApplicationDescription": {id:6, group:["full", "metadata"], name: "Change software description" },
		"ChangeApplicationAbstract": {id:7, group:["full", "metadata"], name: "Change software abstract" },
		"ChangeApplicationLogo": {id:8, group:["full", "metadata"], name: "Change software logo"},
		"ChangeApplicationStatus": {id:9, group:["full", "metadata"], name: "Change software status" }, 
		"ChangeApplicationDiscipline": {id:10, group:["full", "metadata"], name: "Change software discipline" },
		"ChangeApplicationSubdiscipline": {id:11, group:["full", "metadata"], name: "Change software subdiscipline" , hidden: true},
		"ChangeApplicationCountry": {id:12, group:["full", "metadata"], name: "Change software country" },
		"ChangeApplicationVO": {id:13, group:["full", "metadata"], name: "Change software VO" },
		"ChangeApplicationURLs": {id:14, group:["full", "metadata"], name: "Change software URLs" },
		"ChangeApplicationDocuments": {id:15, group:["full", "metadata"], name: "Change software documents" },
		"AssociatePersonToApplication": {id:16, group:["full"], name: "Associate person to software" },
		"DisassociatePersonFromApplication": {id:17, group:["full"], name: "Disassociate person from software" },
		"ChangeApplicationMiddleware": {id:20, group:["full", "metadata"], name: "Change software middleware" },
		"EditUserProfile": {id:21, group:[], name: "Edit person's profile" },
		"BulkReadSensitiveData": {id:22, group:[], name: "Bulk read sensitive data" },
		"GrantOwnership": {id:23, group:[], name: "Grant ownership" },
		"ChangeApplicationCategory": {id:26, group:["full", "metadata"], name: "Change software category" },
		"UsesDissemination": {id:28, group:[], name: "Use dissemination" },
		"EditFaq": {id:29, group:[], name: "Edit FAQs" },
		"ManageApplicationReleases": {id:30, group:["full", "releases"], entity:["software"], name: "Manage software releases" },
		"ChangeApplicationProgrammingLanguage": {id:31, group:["full", "metadata"], name: "Change software language" },
		"ManageVirtualAppliance": {id:32, group:["full", "vaversions"], entity:["vappliance"],name: "Manage software VAs" },
		"ChangeApplicationLicenses": {id:33, group:["full", "metadata"],name: "Change software licenses" },
		"AccessVirtualAppliance": {id: 34, group:["full", "accessvaversions"],entity:["vappliance"], name:"Access Virtual Appliance private data"},
		"EditRelatedProjects":{id:40, group:["full","metadata"], name: "Change relations to projects"},
		"EditRelatedOrganizations":{id:41, group:["full","metadata"], name: "Change relations to organizations"},
		"EditRelatedSoftware":{id:42, group:["full","metadata"], name: "Change relations to software"},
		"EditRelatedVappliances": {id:43, group:["full","metadata"], name: "Change relations to vappliances"},
		"ManageContextScripts": {id:44, group: ["full","contextscripts"], name: "Manage contextualization scripts"}
	};
	var _getActions = function(){
		return $.extend(true,{},_actions);
	};
	var _getActionGroups = function(){
		var res = {};
		for(var i in _actions){
			if( _actions.hasOwnProperty(i) === false ) continue;
			res["can"+i] = _actions[i].group || [];
		}
		return $.extend(true,{},res);
	};
	var _getActionIds = function(){
		var res = {};
		for(var i in _actions){
			if( _actions.hasOwnProperty(i) === false ) continue;
			res[i] = _actions[i].id || 0;
		}
		return $.extend(true,{},res);	
	};
	var _getNewActionGroup = function(groupnames){
		groupnames = groupnames || [];
		groupnames = $.isArray(groupnames)?groupnames:[groupnames];
		var res = [];
		var uniq = {};
		
		for( var i in _actions ){
			if( _actions.hasOwnProperty(i) === false) continue;
			_actions[i].group = _actions[i].group || [];
			if( _actions[i].group.length === 0 ) continue;
			
			if( groupnames.length > 0 ){
				var found = $.grep( ( _actions[i].group || [] ) , function(e){
					return $.inArray(e,groupnames);
				});
				if( found.length > 0 ){
					uniq[i] = _actions[i];
				}
			}else{
				uniq[i] = _actions[i];
			}
		}
		
		for( var u in uniq ){
			if( uniq.hasOwnProperty(u) === false ) continue;
			res.push( {
				rules: "",
				system: "false",
				global: "false",
				canModify: "true",
				state: "notgranted",
				id: $.trim(uniq[u].id),
				val: (function(v){
					return function(){
						return v;
					};
				})(uniq[u].name)
			} );
		}
		return res;
	};
	var _getGroupsById = function(id){
		for(var i in _actions){
			if( _actions.hasOwnProperty(i) === false ) continue;
			if( _actions[i].id == id ) {
				return _actions[i].group || [];
			}
		}
		return [];
	};
	var _getByGroupName = function(groupname, entitytype){
		groupname = $.trim(groupname).toLowerCase();
		entitytype = $.trim(entitytype).toLowerCase();
		var res = [];
		if( groupname === "" ) return res;
		
		for(var i in _actions){
			if( _actions.hasOwnProperty(i) === false ) continue;
			var a = _actions[i];
			if( $.isArray(a.group) && $.inArray(groupname, a.group) > -1 ){
				if( entitytype !== "" && $.isArray(a.entity) ){
					if( $.inArray(entitytype, a.entity) > -1 ) {
						res.push(a);
					}
				}else{
					res.push(a);
				}
			}
		}
		return res;
	};
	var _specifiedEntitiesById = function(id){
		for(var i in _actions){
			if( _actions.hasOwnProperty(i) === false ) continue;
			if( _actions[i].id == id ) {
				return _actions[i].entity || [];
			}
		}
		return [];
	};
	return {
		getActions: _getActions,
		getActionGroups: _getActionGroups,
		getActionIds: _getActionIds,
		getNewActionGroup: _getNewActionGroup,
		getGroupsById: _getGroupsById,
		specifiedEntitiesById: _specifiedEntitiesById,
		getByGroupName: _getByGroupName
	};
})();
appdb.utils.Privileges = function(o, entityType){
	this._privs = [];
	var _actionIds = appdb.utils.PermissionActions.getActionIds();/*{
		"GrantPrivilege": 1,
		"RevokePrivilege": 1,
		"InsertApplication": 3,
		"DeleteApplication": 4,
		"ChangeApplicationName": 5,
		"ChangeApplicationDescription": 6,
		"ChangeApplicationAbstract": 7,
		"ChangeApplicationLogo": 8,
		"ChangeApplicationStatus": 9, 
		"ChangeApplicationDiscipline": 10,
		"ChangeApplicationSubdiscipline": 11,
		"ChangeApplicationCountry": 12,
		"ChangeApplicationVO": 13,
		"ChangeApplicationURLs": 14,
		"ChangeApplicationDocuments": 15,
		"AssociatePersonToApplication": 16,
		"DisassociatePersonFromApplication": 17,
		"ChangeApplicationMiddleware": 20,
		"EditUserProfile": 21,
		"BulkReadSensitiveData": 22,
		"GrantOwnership": 23,
		"ChangeApplicationCategory": 26,
		"UsesDissemination": 28,
		"EditFaq": 29,
		"ManageApplicationReleases": 30,
		"ChangeApplicationProgrammingLanguage": 31,
		"ManageVirtualAppliance": 32,
		"ChangeApplicationLicenses": 33
	};*/
	this.entityType = entityType || "software";
	this._constructor = function(){
		o = o || {};
		if($.isArray(o)){
			if(o.length>0 && o[0].id){
				this._privs = o;
			}else{
				for(var i=0; i<o.length; i+=1){
					this._privs.push({id: o[i]});
				}
			}
		}
		
		if(o.action && $.isArray(o.action)){
			this._privs = o.action;
		}
		(function(self){
			for(var a in _actionIds){
				if( _actionIds.hasOwnProperty(a) === false ) continue;
				self["can" + a] = (function(id){
					return function(){
						var p = self.getPrivilegeById(id);
						if( p === null ) return false;
						if( p && p !== null && typeof p.state === "undefined") return true;
						if( p.state === "granted" ) return true;
						return false;
					};
				})(_actionIds[a]);
				self["can" + a].isEditable = (function(id){
					return function(){
						return self.isEditable(id);
					};
				})(_actionIds[a]);
				self["can" + a].isSystem = (function(id){
					return function(){
						return self.isSystemPrivilege(id);
					};
				})(_actionIds[a]);
				self["can" + a].isRevoked = (function(id){
					return function(){
						return self.isRevoked(id);
					};
				})(_actionIds[a]);				
				self["can" + a].isGlobal = (function(id){
					return function(){
						return self.isGlobal(id);
					};
				})(_actionIds[a]);
				self["can" + a].getRules = (function(id){
					return function(){
						return self.getRules(id);
					};
				})(_actionIds[a]);
				self["can" + a].toString = (function(id){
					return function(){
						var p = self.getPrivilegeById(id);
						if( p && p.val && typeof p.val === "function" ) return p.val();
						return "";
					};
				})(_actionIds[a]);
				self["can" + a].isExplicit = (function(id){
					return function(){
						return self.isExplicit(id);
					};
				})(_actionIds[a]);
				self["can" + a].isPowerUser = (function(id){
					return function(){
						return self.isPowerUser(id);
					};
				})(_actionIds[a]);
				self["can" + a].getId = (function(id){
					return function(){
						return id;
					};
				})(_actionIds[a]);
				self["can" + a].specifiedFor = (function(id){
					return function(){
						return appdb.utils.PermissionActions.specifiedEntitiesById(id);
					};
				})(_actionIds[a]);
				self["can" + a].canBeUsed = (function(id){
					return function(){
						if( $.trim(self.entityType) === "") return true;
						var entities = this.specifiedFor();
						if( entities.length === 0) return true;
						return ($.inArray(self.entityType, entities) > -1);
					};
				})(_actionIds[a]);
			}
		})(this);
		this._privs = this._privs || [];
		this._privs = $.isArray(this._privs)?this._privs:[this._privs];
	};
	this.getPrivilegeById = function(id){
		var pid = $.trim(id);
		var i, p = this._privs, len = p.length;
		var found = [];
		for(i=0; i<len; i+=1){
			if($.trim(p[i].id) === pid ){
				found.push(p[i]);
			}
		}
		if( found.length === 1 ){
			return found[0];
		}else if ( found.length > 1 ){
			var uniq = {};
			$.each(found, function(i,e){
				var r = $.trim(e.rules).split(",");
				$.each(r, function(ii,ee){
					uniq[ee] = true;
				});
			});
			var rules = [];
			for(var i in uniq){
				if( uniq.hasOwnProperty(i) === false ) continue;
				rules.push(i);
			}
			found[0].rules = rules.join(",");
			return found[0];
		}
		return null;
	};
	this.hasPrivilegeById = function(id){
		var priv = this.getPrivilegeById(id);
		return (priv !== null);
	};
	this.getSystemPrivileges = function(){
		return $.grep(this._privs, function(e){
			return ($.trim(e.system).toLowerCase() === "true");
		});
	};
	this.getEditablePrivileges = function(id){
		return $.grep(this._privs, function(e){
			return ($.trim(e.canModify).toLowerCase() === "true");
		});
	};
	this.isSystemPrivilege = function(id){
		var priv = this.getPrivilegeById(id) ;
		if( priv !== null && $.trim(priv.system).toLowerCase() === "true" ) return true;
		return false;
	};
	this.isEditable = function(id){
		var priv = this.getPrivilegeById(id) ;
		if( priv !== null && $.trim(priv.canModify).toLowerCase() === "true" ) return true;
		return false;
	};
	this.isRevoked = function(id){
		var priv = this.getPrivilegeById(id) ;
		if( priv !== null && $.trim(priv.state).toLowerCase() === "revoked" ) {
			return  $.trim(priv.revokedBy) || true;
		}
		return false;
	};
	this.getRules = function(id){
		var priv = this.getPrivilegeById(id);
		if( priv !== null && $.trim(priv.rules) !== "" ){
			return $.trim(priv.rules).split(",");
		}
		return [];
	};
	this.isExplicit = function(id){
		var rules = this.getRules(id);
		var expl = $.grep(rules, function(e){
			return e.indexOf("-") === -1;
		});
		return ( expl.length > 0 );
	};
	this.isGlobal = function(id){
		var priv = this.getPrivilegeById(id);
		if( priv !== null && $.trim(priv.global) === "true" ){
			return true;
		}
		return false;
	};
	this.isPowerUser = function(id){
		var priv = this.getPrivilegeById(id);
		return ( priv !== null && $.trim(priv.system) === "false" && $.trim(priv.global) === "true" );
	};
	this.canBeUsedByCurrentEntity = function(id){
		if( $.trim(this.entityType) === "" ) return true;
		var priv = this.getPrivilegeById(id);
		
	};
	this._constructor();
};
/*
 * Group Privileges (edit metadata, manage versions, manage release, full control etc).
 * Wraps user privileges list and deducts group privileges. Used by mostly by
 * entity appdb.utils.EntityPrivileges.
 * Parameters: privilege.action array from API.
 */
appdb.utils.PermissionGroups = function(o, entityType){
	var _privs = new appdb.utils.Privileges(o, entityType);
	this.entityType = entityType || "software";
	this.software = {};
	this.vappliance = {};
	var _groupActions = appdb.utils.PermissionActions.getActionGroups();/*{
		"canGrantPrivilege": ["full"],
		"canRevokePrivilege": ["full"],
		"canInsertApplication": [],
		"canDeleteApplication": ["full"],
		"canChangeApplicationName": ["full", "metadata"],
		"canChangeApplicationDescription": ["full", "metadata"],
		"canChangeApplicationAbstract": ["full", "metadata"],
		"canChangeApplicationLogo": ["full", "metadata"],
		"canChangeApplicationStatus": ["full", "metadata"],
		"canChangeApplicationDiscipline": ["full", "metadata"],
		"canChangeApplicationSubdiscipline": ["full", "metadata"],
		"canChangeApplicationCountry": ["full", "metadata"],
		"canChangeApplicationVO": ["full", "metadata"],
		"canChangeApplicationURLs": ["full", "metadata"],
		"canChangeApplicationDocuments": ["full", "metadata"],
		"canAssociatePersonToApplication": ["full"],
		"canDisassociatePersonFromApplication": ["full"],
		"canChangeApplicationMiddleware": ["full", "metadata"],
		"canEditUserProfile": [],
		"canBulkReadSensitiveData": [],
		"canGrantOwnership": [],
		"canUsesDissemination": [],
		"canEditFaq": [],
		"canManageApplicationReleases": ["full", "releases"],
		"canChangeApplicationProgrammingLanguage": ["full", "metadata"],
		"canManageVirtualAppliance": ["full", "vaversions"],
		"canChangeApplicationLicenses": ["full", "metadata"]
	};*/
	var _groups = [
		{ name: "EditMetaData", key: "metadata" },
		{ name: "ManageReleases", key: "releases" },
		{ name: "ManageContextScripts", key: "contextscripts"},
		{ name: "ManageVersions", key: "vaversions" },
		{ name: "AccessVersions", key: "accessvaversions" },
		{ name: "FullControl", key: "full" }
	];
	this._constructor = function(){
		var _groupActions = {};
		for(var p in appdb.utils.PermissionActions)
		var len = _groups.length;
		for(var i = 0; i<len; i+=1){	
			(function(cntx,group){
				var funcName = "can" + group.name;
				cntx[funcName] = function(){
					return _hasGroupPermission(group.key);
				};
				cntx[funcName].isPartial = function(){
					return _isPartial(group.key);
				};
				cntx[funcName].isEditable = function(){
					return _isEditable(group.key);
				};
				cntx[funcName].isSystem = function(){
					return _isSystem(group.key);
				};
				cntx[funcName].isExplicit = function(){
					return _isExplicit(group.key);
				};
				cntx[funcName].isGlobal = function(){
					return _isGlobal(group.key);
				};
				cntx[funcName].getList = function(){
					return _getFlatPermissionsObject(group.key);
				};
				cntx[funcName].getRules = function(){
					return _getRules(group.key);
				};
				cntx[funcName].getExplicitPrivileges = function(){
					return _getExplicitPrivileges(group.key);
				};
				cntx[funcName].getActionIds = function(explicit, exclude){
					explicit = ( typeof explicit === "boolean" )?explicit:false;
					exclude = exclude || [];
					exclude = $.isArray(exclude)?exclude:[exclude];
					return _getActionIds(group.key, explicit,exclude);
				};
				cntx[funcName].toString = function(){
					return group.name;
				};
			})(this,_groups[i]);
		}
	};
	var _getGroupActions = function(groupname, explicit){
		groupname = $.trim(groupname).toLowerCase();
		explicit = ( typeof explicit === "boolean" )?explicit:false;
		if( !groupname ) return [];
		var acts = {};
		
		//Collect which privileges are needed for the given group
		for(var ga in _groupActions ){
			if( _groupActions.hasOwnProperty(ga) === false ) continue;
			if( explicit === true ){ //must be the only one
				if( (_groupActions[ga] || []).length === 1 && _groupActions[ga][0] === groupname){
					acts[ga] = true;
				}
			}else{//can coexist
				$.each( (_groupActions[ga] || []) , function(i, e){
					if( $.trim(e).toLowerCase() === groupname ){
						acts[ga] = true;
					}
				});
			}
		}
		var res = [];
		//Check if user has all of the privileges for this group
		for(var i in acts){
			if( acts.hasOwnProperty(i) === false ) continue;
			 res.push(i);
		}
		return res;
	};
	var _hasGroupPermission = function(groupname){
		var gacts = _getGroupActions(groupname), len = gacts.length;
		//Check if user has all of the privileges for this group
		for(var i=0; i<len; i+=1){
			if( _privs[gacts[i]].canBeUsed() ){
				if( _privs[gacts[i]] && _privs[gacts[i]]() === false ) return false;
			}
		}
		return true;
	};
	var _isEditable = function(groupname){
		var gacts = _getGroupActions(groupname), len = gacts.length;
		//Check if user can edit all of the privileges for this group
		for(var i=0; i<len; i+=1){
			if( _privs[gacts[i]] && _privs[gacts[i]].isEditable && _privs[gacts[i]].isEditable() === false ) return false;
		}
		return true;
	};
	var _isSystem = function(groupname){
		var gacts = _getGroupActions(groupname), len = gacts.length;
		//Check if group's privileges are marked as system
		for(var i=0; i<len; i+=1){
			if( _privs[gacts[i]] && _privs[gacts[i]].isSystem && _privs[gacts[i]].isSystem() === true ) return true;
		}
		return false;
	};
	var _isGlobal = function(groupname){
		var gacts = _getGroupActions(groupname), len = gacts.length;
		//Check if group's privileges are marked as system
		for(var i=0; i<len; i+=1){
			if( _privs[gacts[i]] && _privs[gacts[i]].isGlobal && _privs[gacts[i]].isGlobal() === true ) return true;
		}
		return false;
	};
	var _isPartial = function(groupname){
		var gacts = _getGroupActions(groupname), len = gacts.length;
		var tmp = null;
		//Check if group's privileges are marked as system
		for(var i=0; i<len; i+=1){
			if( _privs[gacts[i]] ){
				if( tmp === null ){
					tmp = _privs[gacts[i]]();
				}else if( tmp !== _privs[gacts[i]]() ){
					return true;
				}
			}
		}
		return false;
	};
	
	var _isExplicit = function(groupname){
		var gacts = _getGroupActions(groupname), len = gacts.length;
		var uniq = {};
		//Check if group's privileges are marked as explicit(global === false, system = false)
		for(var i=0; i<len; i+=1){
			if( _privs[gacts[i]] && _privs[gacts[i]].isSystem && _privs[gacts[i]].isGlobal ){
				uniq[gacts[i]] = _privs[gacts[i]].isExplicit() ||  _privs[gacts[i]].isPowerUser();
			}
		}
		for(var i in uniq){
			if( uniq.hasOwnProperty(i) === false ) continue;
			if( uniq[i] === false ){
				return false;
			}
		}
		return true;
	};
	var _getExplicitPrivileges = function(groupname){
		var gacts = _getGroupActions(groupname), len = gacts.length;
		var res = [];
		for(var i=0; i<len; i+=1){
			if( _privs[gacts[i]] && _privs[gacts[i]].isExplicit() ){
				res.push(_privs[gacts[i]]);
			}
		}
		return res;
	};
	var _getActionIds = function(groupname,explicit,exclude){
		explicit = ( typeof explicit === "boolean" )?explicit:false;
		var gacts = _getGroupActions(groupname,explicit), len = gacts.length;
		var res = [];
		for(var i=0; i<len; i+=1){
			if( _privs[gacts[i]] && _privs[gacts[i]].getId ){
				var cid = _privs[gacts[i]].getId();
				var grps = appdb.utils.PermissionActions.getGroupsById(cid);
				var excluded = $.grep(exclude, function(ex){
					return ($.inArray(ex, grps)>-1);
				});
				if( excluded.length === 0 ){
					res.push(cid);
				}
			}
		}
		return res;
	};
	var _getFlatPermissionsObject = function(groupname){
		var gacts = _getGroupActions(groupname), len = gacts.length;
		var res = {};
		for(var i=0; i<len; i+=1){
			res[gacts[i]] = false;
			if( _privs[gacts[i]] && _privs[gacts[i]]() === true){
				res[gacts[i]] = true;
			}
		}
		return res;
	};
	var _getRules = function(groupname){
		var gacts = _getGroupActions(groupname), len = gacts.length;
		var acts = {};
		for( var i=0; i<len; i+=1 ){
			if( _privs[gacts[i]] && _privs[gacts[i]].getRules ){
				var r = _privs[gacts[i]].getRules();
				$.each(r, function(i, e){
					if( typeof acts[e] === "undefined" ) acts[e] = 0;
					acts[e] = acts[e] + 1;
				});				
			}	
		}
		var actsarr = [];
		for(var a in acts){
			if( acts.hasOwnProperty(a) === false ) continue;
			actsarr.push(a);
		}
		return actsarr;
	};
	this.getPrivileges = function(){
		return _privs;
	};
	
	this.isSystemGroup = function(){
		return (this.canEditMetaData.isSystem() && (this.canManageReleases.isSystem() || this.canManageVersions.isSystem() || this.canAccessVersions.isSystem()) && this.canFullControl.isSystem());
	};
	this.isPartialGroup = function(){
		return (this.canEditMetaData.isPartial() ||(this.canManageReleases.isPartial() || this.canManageVersions.isPartial() || this.canAccessVersions.isPartial()) || this.canFullControl.isPartial());
	};
	this.isExplicitGroup = function(){
		return (this.canEditMetaData.isExplicit() ||(this.canManageReleases.isExplicit() || this.canManageVersions.isExplicit() || this.canAccessVersions.isExplicit()) || this.canFullControl.isExplicit());
	};
	this.getExplicitPrivileges = function(){
		var res = this.canEditMetaData.getExplicitPrivileges();
		res = res.concat( this.canManageReleases.getExplicitPrivileges());
		res = res.concat( this.canManageVersions.getExplicitPrivileges());
		res = res.concat( this.canAccessVersions.getExplicitPrivileges());
		res = res.concat( this.canFullControl.getExplicitPrivileges());
		var uniq = {};
		$.each(res, function(i, e){
			if( typeof uniq[""+e] === "undefined") {
				uniq[""+e] = e;
			}
		});
		var result = [];
		for(var i in uniq){
			if( uniq.hasOwnProperty(i) === false ) continue;
			result.push(uniq[i]);
		}
		return result;
	};
	this.getPowerUserPrivileges = function(){
		
	};
	this.getRules = function(exclude){
		exclude = exclude || [];
		exclude = $.isArray(exclude)?exclude:[exclude];
		var rules = this.canEditMetaData.getRules();
		rules = rules.concat( this.canManageVersions.getRules() );
		rules = rules.concat( this.canAccessVersions.getRules() );
		rules = rules.concat( this.canManageReleases.getRules() );
		rules = rules.concat( this.canFullControl.getRules() );
		var sumrules = {};
		$.each(rules, function(i, e){
			sumrules[e] = (typeof sumrules[e] === "undefined")?0:sumrules[e];
			sumrules[e] = sumrules[e] + 1;
		});
		
		var sumrulesarr = [];
		for(var i in sumrules){
			if( sumrules.hasOwnProperty(i) === false ) continue;
			sumrulesarr.push({ name: i, sum: sumrules[i] });
		}
		sumrulesarr.sort(function(a,b){
			return b.sum-a.sum;
		});
		var res = [];
		$.each( sumrulesarr, function(i, e){
			res.push(e.name);
		});
		if( res.length > 0 && exclude.length > 0 ){
			return $.grep(res, function(e){
				return ( $.inArray($.trim(e), exclude) === -1);
			});			
		}
		return res;
	};
	this._constructor();
};
/*
 * Wrapper object of resource privileges. Provides functions for quering 
 * privileges for specific resource.
 * Parameter: privileges array from API <resource>privileges call.
 */
appdb.utils.EntityPrivileges = function(o){
	this._privs = [];
	this._actorGroups = {};
	this.entityType = o.entityType || "software";
	this._constructor = function(){
		var data = $.extend(o,{},true);
		if( $.isArray(data) ) data = {actor: data};
		data = data || {actor: []};
		data.groupName = data.groupName || "all";
		data.actor = data.actor || [];
		data.actor = $.isArray(data.actor)?data.actor:[data.actor];
		$.each(data.actor, (function(self){ 
			return function(i, e){
				self.addActor(e);
			};
		})(this));
		this._actorGroupName = data.groupName;
	};
	this.addActor = function(actor){
		var full = $.trim(actor.name).split(" ");
		actor.firstname = actor.firstname || ( (full.length > 0)?full[0]:"" );
		actor.lastname = actor.lastname || ( (full.length > 1)?full[1]:"" );
		actor.action = actor.action || [];
		actor.action = $.isArray(actor.action)?actor.action:[actor.action];
		actor.permissionGroups = new appdb.utils.PermissionGroups(actor.action,this.entityType);
		this._privs.push(actor);
	};
	this.removeActor = function(suid){
		suid = $.trim(suid);
		if( suid === "" ) return;
		var index = -1;
		$.each(this._privs, function(i, e){
			if( $.trim(e.suid) === suid ) index = i;
		});
		if( index > -1 ) {
			this._privs.splice(index,1);
		}
	};
	this.getAllPrivileges = function(){
		return this._privs;
	};
	this.getGroupByActorId = function(id){
		for(var g in this._actorGroups){
			if( this._actorGroups.hasOwnProperty(g) === false || g === "rest") continue;
			if( this._actorGroups[g].getById(id).length > 0 ) return this._actorGroups[g];
		}
		return null;
	};
	this.createActorGroupPrivileges = function(name, ids, types, displayName){
		name = $.trim(name);
		if( name === "" ) return null;
		ids = ids || [];
		ids = $.isArray(ids)?ids:[ids];
		types = types || [];
		types = $.isArray(types)?types:[types];
		uniq = {};
		$.each(this._privs, (function(self){
			return function(i, e){
				var tp = $.trim(e["type"]).toLowerCase();
				var id = $.trim(e.id);
				var hastype = ( (types.length===0)?true:false );
				var hasid = false;
				if( $.inArray(tp, types) > -1) hastype = true;
				if( $.inArray(id, ids) > -1 ) hasid = true;
				if(hastype === true && hasid === true){
					uniq[id+":"+tp] = $.extend(e,{});
				}
			};
		})(this));
		var result = [];
		for(var u in uniq){
			if( uniq.hasOwnProperty(u) === false ) continue;
			result.push(uniq[u]);
		}
		if(this._actorGroups[name]) delete this._actorGroups[name];
		this._actorGroups[name] = new appdb.utils.EntityPrivileges({ "actor": result, groupName: name, entityType: this.entityType });
		this.updateRestActorGroups(result);
		if( displayName ){
			this._actorGroups[name].displayName = displayName;
		}
		return this._actorGroups[name];
	};
	this.updateRestActorGroups = function(newlyadded){
		newlyadded = newlyadded || [];
		newlyadded = $.isArray(newlyadded)?newlyadded:[newlyadded];
		
		if( !this._actorGroups["rest"] ){
			this._actorGroups["rest"] = new appdb.utils.EntityPrivileges({ "actor": $.extend(this.getAllPrivileges(), {}), groupName: "rest", entityType: this.entityType });
		}
		var rest = (this._actorGroups["rest"].getAllPrivileges() || []).concat([]);
		var toberemoved = [];
		$.each(rest, function(i,e){
			$.each(newlyadded, function(ii,ee){
				if( ee.id == e.id ){
					toberemoved.push(i);
				}
			});
		});
		for(var i=toberemoved.length-1; i>=0; i-=1){
			rest.splice(toberemoved[i],1);
		}
		this._actorGroups["rest"] = new appdb.utils.EntityPrivileges({ "actor": rest, groupName: "rest", entityType: this.entityType });
	};
	this.getActorGroup = function(name){
		name = $.trim(name);
		return (name !== "" )?this._actorGroups[name]:this._actorGroups;
	};
	this.getActorGroupName = function(){
		return this._actorGroupName || "all";
	};
	this.getByType = function(privtype){
		privtype = $.trim(privtype).toLowerCase();
		if( privtype === "" ) return [];
		return $.grep(this._privs, function(e){
			return (e["type"] === privtype);
		});
	};
	this.getById = function(id){
		id = $.trim(id).toLowerCase();
		if( id === "" ) return [];
		return $.grep(this._privs, function(e){
			return ( $.trim(e.id) === id);
		});
	};
	this.getBySuid = function(suid){
		suid = $.trim(suid).toLowerCase();
		if( suid === "" ) return [];
		return $.grep(this._privs, function(e){
			return ( $.trim(e.suid) === suid);
		});
	};
	this.getByName = function(name){
		name = $.trim(name).toLowerCase();
		if( name === "" ) return this._privs;
		var full = name.replace(/\s{2,}/g," ").split(" ");
		var uniq = {};
		$.each(full, function(i, e){
			var len = e.length;
			$.each(this._privs, function(ii, ee){
				if( ($.trim(ee.firstName).toLowerCase().substr(0,len) === e) ||
					($.trim(ee.lastName).toLowerCase().substr(0,len) === e) ){
					uniq[ee.id] = ee;
				}
			});
		});
		var result = [];
		for(var u in uniq){
			if( uniq.hasOwnProperty(u) === false ) continue;
			result.push(uniq[u]);
		}
		return result;
	};
	
	this._constructor();
};

/*
 *Sorts a list of objects based on a given property.
 *If no property name is given it returns the list as is.
 *If no list or argument object is given it returns an empty array.
 *Parameters of argument object are:
 *list [array]: the array to be sorted. If object is given it returns an array contianing only that object.
 *property [string] : The name of the property of the list to be sorted by.
 *orderAsc [boolean] : Default is true. If false it sorts the list in a desceding fashion.
 */
appdb.utils.SortObjectList = function(o){
 var opt = o || {}, func = null, list = [], getter;
 if(typeof o.list === "undefined") {
  return [];
 }
 if(typeof o.orderAsc === "string"){
  o.orderAsc = (o.orderAsc.toLowerCase()==="true")?true:false;
 }else if(typeof o.orderAsc !== "boolean"){
  o.orderAsc = true;
 }

 if($.isArray(o.list) === false){
  o.list = [o.list];
 }
 if(typeof o.property !== "string"){
  return o.list;
 }
 list = list.concat(o.list);

 if(o.orderAsc === true){
  func = function(a,b){
   var na = getter(a).toLowerCase(), nb = getter(b).toLowerCase();
   if ( na < nb ) return -1;
   if ( na > nb ) return 1;
   return 0;
  };
 }else{
  func = function(a,b){
   var na = getter(a).toLowerCase(), nb = getter(b).toLowerCase();
   if ( na < nb ) return 1;
   if ( na > nb ) return -1;
   return 0;
  };
 }
 getter = function(obj){
  if($.isFunction(obj[o.property]) === true){
   return obj[o.property]();
  }
  return obj[o.property];
 };
 return list.sort(func);
};

/*
 * Used by appdb.utils.DataWatcher as an item to watch for changes
 */
appdb.utils.DataWatcherItem = function(o){
	this.container = $("body");
	this.selector = undefined;
	this.state = [];
	this.currentState = [];
	this.type = "";
	this.name = "";
	this.connections = {dojo:[],jquery:[]};
	this.eventHandler = {onChange:function(){}, onReset:function(){}};
	/*
	 *Reloads the current state from the html input elements.
	 *Used by 'hasChanges' function.
	 */
	this.updateCurrentState = function(){
		if($.isFunction(this.selector)){
		 return;
		}
		var els = [], res = [];
		els = $(this.selector);
		var i, len = els.length;
		for(i=0; i<len; i+=1){
			if(dijit.byNode(els[i])){
				res[res.length] = dijit.byNode(els[i]).attr("value");
			}else{
				res[res.length] = $(els[i]).val();
			}
		}
		this.currentState = res;
	};
	/*
	 *Checks if the given value is the same in the initial state.
	 *Used by 'isStateChanged' function.
	 */
	this.inState = function(v){
		var i,st = this.state, len = st.length;
		for(i=0; i < len; i+=1) {
			if(st[i] == v) {
				return true;
			}
		}
		return false;
	};
	/*
	 *Checks for differences between the initial and the current state.
	 *Used by 'hasChanges' function.
	 */
	this.isStateChanged = function(){
		var i, st = this.currentState, len = st.length;
		if(len!==this.state.length){
			return true;
		}
		for(i=0; i<len; i+=1){
			if(this.inState(st[i])===false){
				return true;
			}
		}
		return false;
	};
	/*
	 *Checks if the watched element has changed its initial state.
	 *This function is also used by the 'hasChanges' function of the
	 *parent DataWatcher object.
	 */
	this.hasChanges = function(){
		this.updateCurrentState();
		return this.isStateChanged();
	};
	/*
	 *Stop the item from receiving events.
	 */
	this.stop = function(){
		this.unbindConnections();
		this.eventhandler = {onChange:function(){}, onReset:function(){}};
	};
	/*
	 *Unbinds 'onChange' events from dojo or html input elements,
	 *only if an html selector is given in the constructor options
	 */
	this.unbindConnections = function(){
	 var i;
	 for(i=0; i<this.connections.dojo.length; i+=1){
	  dojo.disconnect(this.connections.dojo[i].element);
	 }
	 this.connections.dojo = [];
	 for(i=0; i<this.connections.jquery.length; i+=1){
	  $(this.connections.jquery[i].element).off(this.connections.jquery[i].event,(this.connections.jquery[i].isHandler)?this.onListChangeDelegate:this.onChangeDelegate);
	 }
	 this.connections.jquery = [];
	};

	/*This function is called uppon onChange event occurs from an input html element.
	 *It won't be used at all if the selector is a function.
	*/
	this.onChangeDelegate = (function(_this){
	 return function(){
	  if(_this.hasChanges()){
	   _this.eventHandler.onChange({oldstate : _this.state, newstate: _this.currentState, extras : {type : _this.type, name : _this.name}});
	  }else{
	   _this.eventHandler.onReset({type : _this.type, name : _this.name});
	  }
	 };
	})(this);

	/*
	 * Binds the 'onChange' event of the html input elements or in case
	 * the 'selector' option is a function overrides the 'hasChanges' function
	 * with the 'selector' function.
	 */
	this.init = function(ev){
	 this.eventHandler = ev || {onChange : function(){}, onReset : function(){}};
	 
	 if($.isFunction(this.selector)){
	  this.hasChanges = this.selector;
	 }else{
	  var i, tst = $(this.selector);
	  if(tst.length>0){
		for(i=0; i<tst.length; i+=1){
		 if(dijit.byNode(tst[i])){
		   this.connections.dojo[this.connections.dojo.length] = {event : "onChange", element : dojo.connect(dijit.byNode(tst[i]),"onChange",this.onChangeDelegate)};
		 }else{
		  this.connections.jquery[this.connections.jquery.length] = {event : "change", element : tst[i]};
		  $(tst[i]).on("change",this.onChangeDelegate);
		 }
		}
	  }
	 }
	};
	this.getExtras = function(){
	 return {type : this.type, name : this.name};
	};
	this._constructor = function(){
		if(typeof o.container === "string"){
			this.container = $(o.container);
		}else if (typeof o.container === "object"){
			this.container = o.container;
		}
		
		if(typeof o.selector !== "undefined"){
		 this.selector = o.selector;
		}
		
		if(typeof o.name === "string"){
		 this.name = o.name;
		}

		if(typeof o.type === "string"){
		 this.type = o.type;
		}
		
		this.updateCurrentState();
		this.state = this.currentState;

		return {
			start : (function(_this){return function(){_this.start();};})(this),
			stop : (function(_this){return function(){_this.stop();};})(this),
			hasChanges : (function(_this){return  function(){return _this.hasChanges();};})(this),
			getExtras :(function(_this){return  function(){return _this.getExtras();};})(this),
			getName : (function(_this){return  function(){return _this.name;};})(this),
			getType : (function(_this){return  function(){return _this.type;};})(this),
			init : (function(_this){return  function(ev){_this.init(ev);};})(this)
		};
	};
	return this._constructor();
};
/*
 *Holds the initial state of a collection of input elements and watches
 *for changes in their values. To check if changes occured it provides
 *the function 'hasChanges'. It options object schema is given bellow:
 *items : list of items to be watched
 *	selector : [String] The html einput element selector.
 *			   [Function] A function that returns 'true' if an element
 *						  has changes in its initial value.
 *	handlers : [Object] Used if the watched element behaves as a list and
 *						provides the selectors for the elements responsible for
 *						insertion or deletion of the items of the list.
 *			add	  : [String] The selector of the html element used for
 *							 inserting a new element to be watched.
 *			remove: [String] The selector of the html element used for
 *							 inserting a new element to be watched.
 *	type  : [String] The type of the element. Used for identification of item.
 *	name  : [String] The display name of the type of the element.
 */
appdb.utils.DataWatcher = function(o){
	this.items = [];
	this.canCheckType = function(){return true;};
	this.onReset = function(o){
	};
	this.onChange = function(o){
	};
	this.initWatchers = function(){
		var i, w = this.items, len = w.length;
		for(i=0; i<len; i+=1){
			w[i].init({onChange:this.onChange, onReset:this.onReset});
		}
	};
	this.stopWatchers = function(){
		var i, w = this.items, len = w.length;
		for(i=0; i<len; i+=1){
		  w[i].stop();
		}
	};
	this.addWatchItem = function(item){
		if(item instanceof appdb.utils.DataWatcherItem ){
			this.items[this.items.length] = item;
		}else if(typeof item === "object"){
			this.items[this.items.length] = new appdb.utils.DataWatcherItem(item);
		}
	};
	this.start = function(){
		this.stop();
		this.initWatchers();
	};
	this.stop = function(){
		this.stopWatchers();
	};
	this.hasChanges = function(){
		var i, items = this.items, len = items.length;
		for(i=0; i<len; i+=1){
		  if(this.canCheckType(items[i].getType())){
		   if(items[i].hasChanges()){
			return true;
		   }
		  }
		}
		return false;
	};
	this.getChangedItemNames = function(){
	 var i, items = this.items, len = items.length, res = [];
	 for(i=0; i<len; i+=1){
	  if(this.canCheckType(items[i].getType())){
		 if(items[i].hasChanges() ){
		  var extras = items[i].getExtras();
		  var n = extras.name || extras.type;
		  if(n){
		   res[res.length] = n;
		  }
		 }
	  }
	 }
	 return res;
	};
	this.rebuild = function(witems){
		witems = witems || [];
		witems = ($.isArray(witems)?witems:[witems]);
		this.stopWatchers();
		this.items = [];
		for(var i=0; i<witems.length; i+=1){
			this.addWatchItem(witems[i]);
		}
	};
	this.clear = function(){
		this.stopWatchers();
		this.items = [];
	};
	this._constructor = function(){
		o = o || {};
		o.items = o.items || [];
		o.items = ($.isArray(o.items)?o.items:[o.items]);
		if($.isFunction(o.canCheckType)){
		 this.canCheckType = o.canCheckType;
		}
		this.rebuild(o.items);
		return {
			rebuild : (function(_this,watchitems){return function(){_this.rebuild(watchitems);};})(this,o.items),
			start : (function(_this){return function(){_this.start();};})(this),
			stop : (function(_this){return function(){_this.stop();};})(this),
			hasChanges : (function(_this){return  function(){return _this.hasChanges();};})(this),
			getChangedItemNames : (function(_this){return  function(){return _this.getChangedItemNames();};})(this),
			clear : (function(_this){return  function(){_this.clear();};})(this)
		};
	};
	return this._constructor();
};

appdb.utils.DataWatcher.Registry = (function(){
 return new function(){
  this.watchers = {};
  this.set = function(name,watcheroptions){
   if(this.get(name)){
	return;
   }
   this.watchers[name] = {options : watcheroptions, instance : undefined};
  };
  this.unset = function(name){
   var w = this.get(name);
   if(w){
	if(w.instance){
	 this.deactivate(name);
	}
	delete this.watchers[name];
   }
  };
  this.get = function(name){
   if(name && typeof name === "string"){
	 return this.watchers[name];
   }
   return undefined;
  };
  this.activate = function(name){
   var w = this.get(name);
   if(w){
	var a = this.getActiveName();
	if(a){
	 this.deactivate(a);
	}
	if(typeof w.instance === "undefined"){
	 w.instance = new appdb.utils.DataWatcher(w.options);
	}
	 w.instance.start();
	return true;
   }
   return false;
  };
  this.deactivate = function(name){
   var w = this.get(name);
   if(w && w.instance){
	w.instance.stop();
	w.instance.clear();
	w.instance = null;
	delete w.instance;
   }
  };
  this.getActive = function(){
   var name = this.getActiveName();
   if(name){
	return this.get(name);
   }
   return undefined;
  };
  this.getActiveName = function(){
   for(var i in this.watchers){
	if(this.watchers.hasOwnProperty(i)){
	 if(this.watchers[i].instance){
	  return i;
	 }
	}
   }
   return undefined;
  };
  this.checkActiveWatcher = function(){
   var w = this.getActive();
   if(w && w.instance){
	return w.instance.hasChanges();
   }
   return false;
  };
  this.checkActiveWatcherAsync = function(o){
   o = o || {};
   var opt = $.extend({
	notify : true,
	onCancel : function(){},
	onClose : function(){}
   },o);
   if(typeof this.getActiveName() === "undefined"){
	opt.onClose();
	return false;
   }
   var res = this.checkActiveWatcher();
   if(res === false){
	this.deactivate(this.getActiveName());
	opt.onClose();
	return false;
   }
   if(opt.notify){
	var changeditems = this.getActive().instance.getChangedItemNames();
	changeditems = (changeditems.length>0)?changeditems:undefined;
	appdb.utils.DataWatcher.Notification.show({data : changeditems, name: this.getActiveName(), onClose : opt.onClose,onCancel:opt.onCancel});
   }
   return true;
  };
 };
})();

appdb.utils.DataWatcher.Notification = (function(){
 return new function(){
  this._dialog = null;
  this.show = function(o){
   this.close();
   o = o || {};
   var opt = $.extend({onClose: function(){}, onCancel : function(){}},o);
   var data = opt.data;
   
   var con = "<div class='unsaved'><div class='title'>You are about to close the editing form while there are unsaved changes";
   if($.isArray(data)){
	con += " regarding the items listed bellow:<div><ul>";
	for(var i=0; i<data.length; i+=1){
	 con += "<li>" + data[i] + "</li>";
	}
	con += "</ul></div>";
   }else{
	con += ".";
   }

   con += "<div class='option selection'></div>";
   con += "<div class='option'>Click <b>Close</b> to close the editing form and <span style='font-weight:bold;color:#e75252;'>discard</span> the changes.</div>";
   con += "<div class='actions'><span class='save'></span><span class='close'></span><span class='cancel'></span></div>";
   con += "</div>";
   con = $(con);
   
   new dijit.form.Button({
	label : "Close",
	onClick : function(){
	 setTimeout(function(){
	  appdb.utils.DataWatcher.Notification.close();
	  appdb.utils.DataWatcher.Registry.deactivate(appdb.utils.DataWatcher.Registry.getActiveName());
	  opt.onClose();
	 },1);
	}
   },$(con).find("div.actions > span.close:first")[0]);
	if( o.name === "vappliance" || o.name === "swappliance"){
		$(con).find(".option.selection").html("Click <b>Cancel</b> to return to editing.");
		new dijit.form.Button({
			label : "Cancel",
			onClick : function(){
				setTimeout(function(){
					appdb.utils.DataWatcher.Notification.close();
					opt.onCancel();
				},1);
			}
		},$(con).find("div.actions > span.cancel:first")[0]);
	}else if(o.name !== "faqs"){
		$(con).find(".option.selection").html("Click <b>Save</b> to save the changes.");
		new dijit.form.Button({
			label : "Save",
			onClick : function(){
				setTimeout(function(){
					appdb.utils.DataWatcher.Notification.close();
					opt.onCancel();
					$("#savedetails").trigger("click");
				},1);
			}
		},$(con).find("div.actions > span.save:first")[0]);
   }else{
	$(con).find(".option.selection").html("Click <b>Cancel</b> to return to editing.");
	new dijit.form.Button({
		label : "Cancel",
		onClick : function(){
			setTimeout(function(){
				appdb.utils.DataWatcher.Notification.close();
				opt.onCancel();
			},1);
		}
	},$(con).find("div.actions > span.cancel:first")[0]);
   }
   
   this._dialog = new dijit.Dialog({
			title: "Unsaved changes",
			style : "width:470px",
			content: $(con)[0]
		});
   this._dialog.show();
  };
  this.close = function(){
   if(this._dialog && this._dialog !== null){
	this._dialog.hide();
	this._dialog.destroyRecursive(false);
	this._dialog = null;
   }
  };
 };
})();

appdb.utils.UrlQueryJson = function(queryString){
  if (queryString.charAt(0) === '?') queryString = queryString.substring(1);
  if (queryString.length > 0){
    queryString = queryString.replace(/\+/g, ' ');
    var queryComponents = queryString.split(/[&;]/g);
    for (var index = 0; index < queryComponents.length; index ++){
      var keyValuePair = queryComponents[index].split('=');
      var key = decodeURIComponent(keyValuePair[0]);
      var value = keyValuePair.length > 1?decodeURIComponent(keyValuePair[1]):'';
      this[key] = value;
   }
  }
 };
/*
 *Helper function to parse the hash value of url.
 *Used by the appdb.utils.Navigator object.
*/
appdb.utils.UrlHashParse = function(hash){
 
 //Check if hash is given.Defaults to current url's hash value else returns []
 hash = hash || window.location.hash;
  if(!hash || hash.length < 3){
	 return [];
  }else if(hash[0] === "#"){
	hash = hash.slice(1,hash.length);
  }
  
  //Check validity of hash value
  var isValidHash = (hash.length>2 && hash[0] === "!" && hash[1] === "/");
  if(!isValidHash ) {
   return [];
  }else{
   hash = hash.slice(2,hash.length);
   hash = hash.split("/");
   if(hash.length === 0){
	return [];
   }
  }

  //Start processing hash value
  var res = [], i , len = hash.length, queryindex = hash[len-1].indexOf("?"), query;
  //Get query string if any
  if(queryindex>-1){
   var qlen = (hash[len-1].length-queryindex)-1;
   query = hash[len-1].slice(queryindex);
   hash[len-1] = hash[len-1].slice(0,queryindex);
  }
  for(i=0; i<len; i+=1){
   if(hash[i]){
	if(isNaN(hash[i])){
	 res[res.length] = {type:"item", value:hash[i]};
	}else{
	 res[res.length] = {type:"value", value:hash[i]}
	}
   }
  }
  if(query){
   res[res.length] = {type:"query", value:new appdb.utils.UrlQueryJson(query)};
  }
  return res;
};

/*
 *Helper function to cancel the event from which a function was invoked.
 *Takes as a parameter the callee (Arguments.callee) object of a function.
 *Used by hash url navigation mechanism to cancel a hash change if needed.
 */
appdb.utils.CancelEventTrigger = function(c){
 var limit = 100, call = null,ev ;
 while(c.caller && limit!==0){
  call=c.caller;
  c = c.caller;
  limit-=1;
 }
 if(call && call.arguments){
  if(call.arguments.length===1){
   ev = call.arguments[0];
  }
 }
 ev = ev || window.event;
 if(ev){
   if (ev.preventDefault) {
   ev.preventDefault();
  } else {
   ev.returnValue = false;
  }
 }
 return ev;	
};

/*
 * Navigation mechanism based the url hash value.
 * It enables the browser history feature.
 */
appdb.Navigator = (function(){
 return new function(){
  this.hashDelegateImpl = function(){};
  this.setHashImpl = function(){};
  this.handleHashImpl = function(){};

  this.hashDelegate = (function(_this){
   return function(event){
	_this.hashDelegateImpl(event);
   };
  })(this);

  this.handleHash = (function(_this){
   return function(f,argv){
	_this.handleHashImpl(f,argv);
   };
  })(this);

  this.setHash = (function(_this){
   return function(h,checkLink){
	_this.setHashImpl(h,checkLink);
   };
  })(this);

  this.onWindowLoadDelegate = (function(_this){
   return function(event){
   if(window.location.hash === ""){
	appdb.views.Main.showHome();
   }else{
	_this.hashDelegate(event);
   }
   };
  })(this);

  this.init = function(){
   this.setInternalMode(false);
   $(document).on("click", "a[href='#']", function() {
	   return false;
   });
   this.inited = true;
   //Check if the window.load event is already triggered
   if (document.readyState && document.readyState === "complete") {
	   this.onWindowLoadDelegate();
   } else {   
	   $(window).on('load', this.onWindowLoadDelegate);
   }
   setTimeout((function(_this){ 
	   return function(){
		   $(window).hashchange(_this.hashDelegate);
	   };
   })(this),1);
  };
  this.sanitizeHash = function(){
   var hash = window.location.hash;
   if(hash){
	if(hash.length>0 && hash[0]==="#"){
	 hash = hash.substr(1);
	}
	if(hash.length>0 && hash[0]==="!"){
	 hash = hash.substr(1);
	}
	if(hash.substr(0,2)==="p="){
	 hash = hash.substr(2);
	}
	return $.trim(hash);
   }
   return "";
  }
  this.setTitle = function(f,argv){
   f = f || appdb.Navigator.Registry["Home"];
   var title = "";
   if(typeof f === "string"){
	title = f;
   }else if($.isFunction(f.title)){
	title = f.title.apply(null,argv);
   }else if(typeof f.title === "string"){
	title = (f.datatype +":"+f.type+" => " + appdb.config.permalinkraw);
   }
   document.title = title;
  };
  this.notFound = function(){
	  if( appdb.config.routing.useCanonical ){
		appdb.routing.Dispatch();
	  } else {
		appdb.views.Main.showHome();
	  }

  };
  this.executePermalink = function(p){
   var _escape = function(v){
	 if(v){
	  return v.replace(/[\x00-\x1F]/g,"");
	 }
	 return v;
	};
	var item;
	if ( p != "" ) {
	 if ( p === "home" ){
	   appdb.views.Main.showHome();
	 } else if ( p === "reports" ) {
	  if ( ( userID) && ( ((userRole == 5) || (userRole = 7)) ) ) {
	   if ($("#reportslink")[0] !== undefined) {
		$("#reportslink").trigger("click");
	   } else {
		this.notFound();
	   }
	  } else {
		this.notFound();
	  }
	 } else if ( p === "dissemination" ) {
	   if ( ( userID) && ( ((userRole == 5) || (userRole == 7)) ) ) {
		if ($("#disseminationlink")[0] !== undefined) $("#disseminationlink").trigger("click"); else this.notFound();
	   } else {
		   this.notFound();
	   }
	 } else if ( p.substr(0,6) === "about:" ) {
		 item = _escape(p.substr(6));
		 if ($("#help"+item+"link")[0] !== undefined) $("#help"+item+"link").trigger("click"); else this.notFound();
	 } else if ( p === "contact:feedback" ) {
		 appdb.views.Main.showPage('feedback',{mainTitle:'Contact us',url:'/index/feedback'});
	 } else if ( p.substr(0,5) === "apps:" ) {
		 item = _escape(p.substr(5));
		 if ($("#apps"+item+"link")[0] !== undefined) $("#apps"+item+"link").trigger("click"); else this.notFound();
	 } else if ( p.substr(0,7) === "people:" ) {
		 item=_escape(p.substr(7));
		 if ($("#ppl"+item+"link")[0] !== undefined) $("#ppl"+item+"link").trigger("click"); else this.notFound();
	 } else {
		 var pp = appdb.utils.base64.decode(p);
		 pp = pp.replace(/[\x00-\x1F]/g,"");
		 if(pp[0]==="{"){
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
			   case "/people":
				   j = appdb.views.Main.showPeople;
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
			   default:
				   j = appdb.views.Main.showPage;
				   var params = JSON.parse(pp);
				   j(params.type, params);
				   return;
				   break;
		   }
		   j(JSON.parse(query || "{}"), JSON.parse(ext || "{}"));
		 } else {
		  p = pp;
		   if( p === "home") {
			 appdb.views.Main.showHome();
		   } else if ( p.substr(0,10) === '/apps?flt=' ) {
			 appdb.views.Main.showApplications($.trim(p.substr(0,6)));
		   } else if ( p.substr(0,12) === '/people?flt=' ) {
			 appdb.views.Main.showPeople($.trim(p.substr(0,8)));
		   } else if ( p.substr(0,13) === '/apps/details' ) {
			 showAppDetails2(p);
			 if (detailsStyle == 1) this.notFound();
		   } else if ( p.substr(0,15) === '/people/details' ) {
			 showPplDetails2(p);
			 if (detailsStyle == 1) this.notFound();
		   } else if ( p.substr(0,11) === '/vo/details' ) {
			 showVODetails2(_escape(p));
			 if (detailsStyle == 1) this.notFound();
		   } else if ( p.substr(0,12) === '/ngi/details' ) {
			 showNGIDetails2(_escape(p));
			 if (detailsStyle == 1) this.notFound();
		   } else if (p === "/news/report") {
			 appdb.views.Main.showActivityReport();
		   } else if (p === "dissemination") {
			 appdb.views.Main.showDisseminationTool();
		   } else if(p==="/help/announcements"){
			  $("#helpannouncelink").trigger("click");
		   } else if( p.substr(0,6) === "/help/" || p.substr(0,9) === "appstats/" || p.substr(0,9) === "pplstats/" || p.substr(0,10)==="/changelog" ) {
			 var acts = appdb.utils.Faq.getActions(p);
			 ajaxLoad(p,'main',acts);
		   }
		   this.notFound();
		 }
	 }
	}else {
	  this.notFound();
	}
  };

  this.setInternalMode = function(act){
   if(act === true){
	this.hashDelegateImpl = this.inactiveHashDelegate;
	this.handleHashImpl= this.inactiveHandleHash;
	this.setHashImpl = this.setHashInternal;
   }else{
	this.hashDelegateImpl = this.activeHashDelegate;
	this.handleHashImpl = this.activeHandleHash;
	this.setHashImpl = this.setHashExternal;
   }
  };
  this.setRawPermalink = function(p){
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
  this.setPermalink = function(p){
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
  this.createPermalink = function(d,datatype){
   datatype = datatype || "apps";
   var u,s,p;
   if(typeof d === "undefined" || d===null){
	   d = {flt:''};
   }else if(typeof d === "string"){
	  if(d === ""){
	   u = appdb.config.endpoint.base+"#";
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
  this.internalCall = function(f,d){
   if(this.inited === true){
	var permalink = appdb.Navigator.createPermalink(d,f.datatype);
	this.setInternalMode(true);
	this.setPermalink(permalink);
	this.setRawPermalink(permalink);
	this.setHash(permalink,false);
	this.setTitle(f,[d.query,d.ext]);
	this.setInternalMode(false);
   }
  };
  
  this.activeHashDelegate = function(event){
	event= window.event || event;
	var hash = this.sanitizeHash();
	if(hash===""){
	 appdb.views.Main.showHome();
	 return true;
	}else if(hash==appdb.config.permalinkraw){
	 appdb.utils.CancelEventTrigger(arguments.callee);
	 return false;
	}else {
	   appdb.Navigator.executePermalink(hash,true);
	}
	return true;
  };
  this.inactiveHashDelegate = function(){
   this.setInternalMode(false);
  };
  this.setHashInternal = function(h){
   appdb.Navigator.setPermalink(h);
   appdb.Navigator.setRawPermalink(h);
   this.setHashExternal(h,false);
   this.setInternalMode(false);
  };
  this.setHashExternal = function(h,checkLink){
   if(!h){
	return;
   }   
   if(h.substr(0,4)==="http"){
	h = h.slice(appdb.config.endpoint.base.length);
   }
   if(h && h[0]==="?"){
	h = h.substr(1);
   }
   if(h.substr(0,1)==="#"){
	h = h.substr(1);
   }
   if(h.substr(0,2)==="p="){
	h = "!" + h;
   }
   if(checkLink){
	if("!p="+appdb.config.permalinkraw!==h && appdb.config.permalinkraw!=h){
	 return;
	}
   }
   window.location.hash = h;
  };
  
  this.inactiveHandleHash = function(f,argv){
   this.setInternalMode(false);
  };
  this.activeHandleHash = function(f,argv){
	var perm = "";
	if(f.datatype){
	  switch(f.type){
	   case "list":
		perm = this.createPermalink((f.permalink)?f.permalink.apply(null,argv):{query:argv[0],ext:argv[1]},f.datatype);
		break;
	   case "item":
		perm = this.createPermalink((f.permalink)?f.permalink.apply(null,argv):argv[0],f.datatype);
		break;
	   case "static":
		perm = this.createPermalink((f.permalink)?f.permalink.apply(null,argv):argv[0],f.datatype);
		break;
	   default:
		perm = this.createPermalink((f.permalink)?f.permalink.apply(null,argv):argv[0]);
		break;
	  }
	  this.setPermalink(perm);
	  this.setRawPermalink(perm);
	  if(appdb.config.permalinkraw==="#"){
	   this.setHash(appdb.config.permalinkraw,false);
	  }else{
	   this.setHash("!p="+appdb.config.permalinkraw,false);
	  }
	  this.setTitle(f,argv);
	}
  };
  this.setInternalMode(true);
 };
})();
appdb.Navigator.Helpers = (function(){
 return new function(){
  this.ApplicationTitle = function(d,prefix){
   var res = prefix || "Software", page = 0, flt = "", o = d.query || {}, e = d.ext || {};
   flt = (e && e.baseQuery)?e.baseQuery.flt:o.flt;
   flt = $.trim(flt);
   flt = flt.replace(/\"/g,"");
   if(flt!="" && flt[0]==="+"){
	flt = flt.substr(1);
   }
   if(typeof prefix === "undefined"){
	if(flt==""){
		res = e.mainTitle || "Software";
	}else if(flt.substr(0,13)==="=category.id:" || flt.substr(0,14)==="+=category.id:") {
		res = e.mainTitle;
	}else if(flt.substr(0,17)==="=middleware.name:"){
	 res = "Software " + ((e && e.mainTitle)?"using " + e.mainTitle:"per middleware");
	}else if(flt.substr(0,12)==="=country.id:"){
	 res = "Software " + ((e && e.mainTitle)?"in " + e.mainTitle:"per country");
	}else if(flt.substr(0,15)==='=discipline.id:' || flt.substr(0,16) === '+=discipline.id' ){
	 res = "Software " + ((e && e.mainTitle)?"under " + e.mainTitle:"per discipline");
	}else if(flt.substr(0,18)==="=subdiscipline.id:"){
	 res = "Software " + ((e && e.mainTitle)?"using " + e.mainTitle:"per subdiscipline");
	}else if(flt.substr(0,4)==="=vo:" || flt.substr(0,10)=="&=vo.name:"){
	 res = "Software " + ((e && e.mainTitle)?"with VO " + e.mainTitle:"per VO");
	}
   }
   if(e && !e.baseQuery && e.userQuery && e.userQuery.flt && e.userQuery.flt!=""){
	var uq = e.userQuery.flt;
	uq = (uq.length>25)?uq.substr(0,22)+"...":uq;
	res = res + " search \"" + uq + "\"";
   }
   if(o.pageoffset && o.pagelength){
	page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
	res = res + " page " + page;
   }
   return res;
  };
  this.CategoriesTitle = function(d, prefix){
	var res = prefix || "Category", o = d.query, e = d.ext;
	if( e.mainTitle ){
		res += " " + e.mainTitle;
	}
	
	if(o.pageoffset && o.pagelength){
		page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
		res = res + " page " + page;
	}
	return res;
  };
  this.PeopleTitle = function(d){
   var o = d.query || {},e= d.ext || {};
   var res = (o && e && o.flt)?e.mainTitle:"People list";
   flt = (e && e.baseQuery)?e.baseQuery.flt:o.flt;
   flt = $.trim(flt);
   flt = flt.replace(/\"/g,"");
   if(flt!="" && flt[0]==="+"){
	flt = flt.substr(1);
   }
   if(flt!=""){
	if(flt.substr(0,12)==="=country.id:"){
	 res = "People in " + e.mainTitle;
	}else if(flt.substr(0,9)==="=role.id:" || flt.substr(0,15)==="=person.roleid:"){
	 res = e.mainTitle;
	}
   }
   if(e && e.userQuery && e.userQuery.flt && e.userQuery.flt!=""){
	var uq = e.userQuery.flt;
	uq = (uq.length>25)?uq.substr(0,22)+"...":uq;
	res = res + " search \"" + uq + "\"";
   }
   if(o.pageoffset && o.pagelength){
	page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
	res = res + " page " + page;
   }
   return res;
  };
 };
})();

appdb.Navigator.Registry = {
	 "Home" :  {datatype : "home", type:"static", title : function(){
	   return "EGI Application Database";}, permalink:function(){return "";}},
	 "People" :  {datatype : "people" , type : "list",
	  title : function(o,e){
	   return appdb.Navigator.Helpers.PeopleTitle({query : o, ext:e});
	 }},
	 "Person" : (function(id){
	  if(id){
	   return {datatype : "people", type : "item", permalink : function(o,e){return "/people/details?id=" + o.id;},title : function(o,e){
		 return e.mainTitle + " profile";
	   }};
	  }
	  return {datatype : "person", type : "item", permalink : function(o){return "/people/details?id=" + o.id;},title : function(o,e){
		return e.mainTitle + " profile";
	  }};
	 })(userID),
	"Everything" :  {datatype : "apps" , type : "list"},
	"Applications" :  {datatype : "apps" , type : "list" ,
	 title : function(o,e){
	  return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e});
	}},
	"VirtualAppliances": {datatype : "apps" , type : "list" ,
	 title : function(o,e){
	  return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e});
	}},
	"OrderedSoftware" :  {datatype : "apps/ordered" , type : "list" },
	"OrderedVAppliances" :  {datatype : "vapps/ordered" , type : "list" },
	"OrderedSWAppliances" :  {datatype : "swapps/ordered" , type : "list" },
	"OrderedPeople": {datatype : "people/ordered" , type : "list" },
	"PeopleByRole": {datatype: "people/byrole", type: "list"},
	"PeopleByGroup": {datatype: "people/bygroup", type: "list"},
	"Categories" : {datatype : "cats" , type : "list" ,
	 title : function(o,e){
	  return appdb.Navigator.Helpers.CategoriesTitle({query:o,ext:e});
	}},
	"VAppCategories" : {datatype : "cats" , type : "list" ,
	 title : function(o,e){
	  return appdb.Navigator.Helpers.CategoriesTitle({query:o,ext:e});
	}},
	"RelatedApps" :  {datatype : "apps" , type : "list", title : function(o,e) {return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e},"Suggested Software");}},
	"Moderated" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e},"Moderated Software");}},
	"Deleted" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e},"Deleted Software");}},
	"Bookmarked" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e},"Bookmarked Software");}},
	"Editable" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e},"Editable Software");}},
	"Owned" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e},"Owned Software");}},
	"Associated" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e},"Associated Software");}},
	"Followed" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e},"Following Software");}},
	"Application" :  {datatype : "app" , type : "item", permalink : function(o){
			return "/apps/details?id=" + o.id;
		}, title : function(o,e){
	  if( (o.id=="0" || !o.id) && !o["name"]){
	   return "Register New Software";
	  }
	  if(o["name"]){
	   return "Software " + o.name;
	  }else{
	   return "Software id:" + o.id;
	  }
	 }
	},
	"VirtualAppliance" :  {datatype : "vapp" , type : "item", permalink : function(o){
			return "/apps/details?id=" + o.id;
		}, title : function(o,e){
	  if( (o.id=="0" || !o.id) && !o["name"]){
	   return "Register New Virtual Appliance";
	  }
	  if(o["name"]){
	   return "Virtual Appliance " + o.name;
	  }else{
	   return "Virtual Appliance id:" + o.id;
	  }
	 }
	},"vaversion" :  {datatype : "vapp" , type : "item", permalink : function(o){
			return "/apps/details?id=" + o.id;
		}, title : function(o,e){
	  if( (o.id=="0" || !o.id) && !o["name"]){
	   return "Register New Virtual Appliance";
	  }
	  if(o["name"]){
	   return "Virtual Appliance " + o.name;
	  }else{
	   return "Virtual Appliance id:" + o.id;
	  }
	 }
	},"SoftwareAppliance" :  {datatype : "swapp" , type : "item", permalink : function(o){
			return "/apps/details?id=" + o.id;
		}, title : function(o,e){
	  if( (o.id=="0" || !o.id) && !o["name"]){
	   return "Register New Software Appliance";
	  }
	  if(o["name"]){
	   return "Software Appliance " + o.name;
	  }else{
	   return "Software Appliance id:" + o.id;
	  }
	 }
	},
	"Ngis" :  {datatype : "ngis" , type : "list"},
	"Ngi" :  {datatype : "ngi" , type : "item"},
	"VOs" :  {datatype : "vos" , type : "list", permalink : function(o,e){
	  return (o)?{query:o,ext:e}:'{"url":"/vos"}';
	}, title : function(o,e){
	 var res = (e && $.trim(e.mainTitle) !== "Virtual Organizations")?"VOs " + e.mainTitle:"Virtual Organizations";
	 if(o && $.trim(o.name)!==""){
	  var n = $.trim(o.name);
	  n = (n.length>25)?n.substr(0,22)+"...":n;
	  res = res + " search \"" + n + "\"";
	 }
	 if(o && o.pageoffset && o.pagelength){
	  page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
	  res = res + " page " + page;
	 }
	 return res;
	}},
	"SwVOs":  {datatype : "vos" , type : "marketplace", permalink : function(o,e){
			return "/browse/vos/software";
		}, title : function(o,e){
		var res = "Software supported VOs";
		if(o && $.trim(o.name)!==""){
		 var n = $.trim(o.name);
		 n = (n.length>25)?n.substr(0,22)+"...":n;
		 res = res + " search \"" + n + "\"";
		}
		if(o && o.pageoffset && o.pagelength){
		 page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
		 res = res + " page " + page;
		}
		return res;
	}},
	"CloudVOs":  {datatype : "vos" , type : "marketplace", permalink : function(o,e){
		return "/browse/vos/cloud";
	  }, title : function(o,e){
	   var res = "Cloud supported VOs";
	   if(o && $.trim(o.name)!==""){
		var n = $.trim(o.name);
		n = (n.length>25)?n.substr(0,22)+"...":n;
		res = res + " search \"" + n + "\"";
	   }
	   if(o && o.pageoffset && o.pagelength){
		page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
		res = res + " page " + page;
	   }
	   return res;
	}},
	"CloudSoftwareAppliances":  {datatype : "swappliances" , type : "marketplace", permalink : function(o,e){
		return "/browse/swappliances/cloud";
	  }, title : function(o,e){
	   var res = "Software Appliances";
	   if(o && $.trim(o.name)!==""){
		var n = $.trim(o.name);
		n = (n.length>25)?n.substr(0,22)+"...":n;
		res = res + " search \"" + n + "\"";
	   }
	   if(o && o.pageoffset && o.pagelength){
		page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
		res = res + " page " + page;
	   }
	   return res;
	}},
	"Sites" :  {datatype : "sites" , type : "list", permalink : function(o,e){
	  return (o)?{query:o,ext:e}:'{"url":"/sites"}';
	}, title : function(o,e){
	 var res = (e && $.trim(e.mainTitle) !== "Sites")?"Sites " + e.mainTitle:"Sites";
	 if(o && $.trim(o.name)!==""){
	  var n = $.trim(o.name);
	  n = (n.length>25)?n.substr(0,22)+"...":n;
	  res = res + " search \"" + n + "\"";
	 }
	 if(o && o.pageoffset && o.pagelength){
	  page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
	  res = res + " page " + page;
	 }
	 return res;
	}},
	"Site":{ datatype: "site", type:"item", permalink: function(o, e){ return {query:o,ext:e};},title: function(o,e){ return "Site " + o.name;}},
	"Datasets" :  {datatype : "datasets" , type : "marketplace", permalink : function(o,e){
	  return "/browse/datasets";
	}, title : function(o,e){
	 var res = (e && $.trim(e.mainTitle) !== "Datasets")?"Datasets " + e.mainTitle:"Datasets";
	 if(o && $.trim(o.name)!==""){
	  var n = $.trim(o.name);
	  n = (n.length>25)?n.substr(0,22)+"...":n;
	  res = res + " search \"" + n + "\"";
	 }
	 if(o && o.pageoffset && o.pagelength){
	  page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
	  res = res + " page " + page;
	 }
	 return res;
	}},
	"Dataset":{ datatype: "dataset", type:"item", permalink: function(o, e){ return {query:o,ext:e};},title: function(o,e){ return ($.trim(o.id)<<0 > 0)?("Dataset " + o.name):"Register New Dataset";}},
	"CloudSites":  {datatype : "sites" , type : "marketplace", permalink : function(o,e){
		return "/browse/sites/cloud";
	  }, title : function(o,e){
	   var res = "Cloud supported Sites";
	   if(o && $.trim(o.name)!==""){
		var n = $.trim(o.name);
		n = (n.length>25)?n.substr(0,22)+"...":n;
		res = res + " search \"" + n + "\"";
	   }
	   if(o && o.pageoffset && o.pagelength){
		page = Math.round(parseInt(o.pageoffset)/parseInt(o.pagelength)) + 1;
		res = res + " page " + page;
	   }
	   return res;
	}},
	"VoManager": {datatype : "vos" , type : "list", permalink : function(o,e){return (o)?{query:o,ext:e}:'{"url":"/vos"}';}},
	"VoDeputy": {datatype : "vos" , type : "list", permalink : function(o,e){return (o)?{query:o,ext:e}:'{"url":"/vos"}';}},
	"VoExpert": {datatype : "vos" , type : "list", permalink : function(o,e){return (o)?{query:o,ext:e}:'{"url":"/vos"}';}},
	"VoShifter": {datatype : "vos" , type : "list", permalink : function(o,e){return (o)?{query:o,ext:e}:'{"url":"/vos"}';}},
	"VoMember": {datatype : "vos" , type : "list", permalink : function(o,e){return (o)?{query:o,ext:e}:'{"url":"/vos"}';}},
	"VO" :  {datatype : "vo" , type : "item", permalink : function(o){return "/vo/details?id="+o;}, title : function(o,e){
	  return (o)?"VO " + o:"VO Item";
	}},
	"Discipline" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e});}},
	"Subdiscipline" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e});}},
	"ApplicationCountry" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e});}},
	"ApplicationMiddleware" :  {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e});}},
	"ApplicationVO" : {datatype : "apps" , type : "list", title : function(o,e){return appdb.Navigator.Helpers.ApplicationTitle({query:o,ext:e});}},
	"Page" : {datatype: "mixed", type: "ajax", title: function(o,e){return e.mainTitle;},permalink : function(o,e){return e;}},
	"SoftwareMarketplace": {datatype: "apps", type: "marketplace", title: function(o,e){ e = e || {}; return e.mainTitle || "Software Marketplace"; }, permalink: function(){ return "/browse/software"; } },
	"CloudMarketplace": {datatype: "apps", type: "marketplace", title: function(o,e){ e = e || {}; return e.mainTitle || "Cloud Marketplace"; }, permalink: function(){ return "/browse/cloud"; } },
	"PeopleMarketplace":{datatype: "apps", type: "marketplace", title: function(o,e){ e = e || {}; return e.mainTitle || "People Registry"; }, permalink: function(){ return "/browse/people"; } },
	"ajaxLoad" :  {datatype:"mixed", type : "ajax" , title : function(o,e){
	  if(e==="main"){
	   switch(o){
		case "/help/usage":
		 return "EGI AppDB Usage";
		case "/help/announcements":
		 return "EGI AppDB Announcements";
		case "/help/faq":
		 return "EGI AppDB Faq";
		case "/help/credits":
		 return "EGI AppDB Credits";
		case "/changelog":
		 return "EGI AppDB Changelog";
		case "/changelog/features":
		 return "EGI AppDB features";
		case "appstats/perdomain":
		case "appstats/perdomain?ct=Pie":
		case "appstats/perdomain?ct=Bars":
		 return "Software statistics per discipline" + ((o.length>18)?" " + o.substr(18+4):"");
		case "appstats/persubdomain":
		case "appstats/persubdomain?ct=Pie":
		case "appstats/persubdomain?ct=Bars":
		 return "Software statistics per subdiscipline" + ((o.length>21)?" " + o.substr(21+4):"");
		case "appstats/percountry":
		case "appstats/percountry?ct=Pie":
		case "appstats/percountry?ct=Bars":
		 return "Software statistics per country" + ((o.length>19)?" " + o.substr(19+4):"");
		case "appstats/pervo":
		case "appstats/pervo?ct=Pie":
		case "appstats/pervo?ct=Bars":
		 return "Software statistics per virtual organization" + ((o.length>14)?" " + o.substr(14+4):"");
		case "pplstats/percountry":
		case "pplstats/percountry?ct=Pie":
		case "pplstats/percountry?ct=Bars":
		 return "People statistics per country" + ((o.length>19)?" " + o.substr(19+4):"");
		case "pplstats/perposition":
		case "pplstats/perposition?ct=Pie":
		case "pplstats/perposition?ct=Bars":
		 return "People statistics per position" + ((o.length>20)?" " + o.substr(20+4):"");
		case "/news/report"://activity report
		 return "AppDB activity report";
		default :
		 return "Appdb";
	   }
	  }
	  return "mixed";
	}},
	"ActivityReport" :  {datatype : "activityreport", type : "item" ,permalink : function(){return "/news/report";},title : function(){return "Activity Report";}},
	"DisseminationTool" :  {datatype : "disseminationtool", type : "item" ,permalink : function(){return "dissemination";},title : function(){return "Dissemination Tool";}}
};

appdb.EntityFieldSchema = function(o){
	var opts = $.extend({},o);
	
	return {
		createDataWrapper: function(data){
			var edw = new appdb.EntityDataWrapper(o,data);
			if(data){
				edw(data[o.name]);
			}
			return edw;
		},
		getName: function(){
			return o.name;
		},
		canRef: function(){
			return (o.canRef)?true:false;
		},
		ref: function(){
			return (o.ref)?o.ref:"";
		},
		isList: function(){
			return (o.isList)?true:false;
		},
		isExternalRef : function(){
			return (o.isExternalRef)?true:false;
		},
		isNullable: function(){
			return (o.isNullable)?true:false;
		},
		getType: function(){
			return o.type || "string";
		},
		getOwner: function(){
			return o.owner;
		},
		getFieldPrefix: function(){
			return o.fieldprefix || "";
		},
		canIgnore : function(){
			return o.canIgnore || false;
		}
	};
};
appdb.EntityDataWrapper = function(o,d){
	this.opts = $.extend({},o);
	this._initRef = function(){
		this.opts.value = appdb.EntityTypes.getEntityTypeByName(this.opts.getRef()).createInstance(d[this.opts.name]);
		return this.opts.value;
	};
	this._simpleGetter = function(){
		if(typeof this.opts.value === "undefined"){
			if(this.opts.ref){
				return this._initRef();
			}
			return undefined;
		}
		return this.opts.value;
	};
	this._simpleSetter = function(v){
		this.opts.value = v;
	};
	this._getterList = function(){
		if(this.opts.value === null){
			return null;
		}
		if(typeof this.opts.value === "undefined"){
			return [];
		}
		this.opts.value = $.isArray(this.opts.value)?this.opts.value:[this.opts.value];
		return this.opts.value;
	};
	this._setterList = function(v){
		if(v===null){ //explicit null set indicates list deletion
			this.opts.value = null;
			return;
		} else if(v){
			v = ($.isArray(v))?v:[v];
		} else{
			v = [];
		}
		if(v.length===0){
			return;
		}
		if(this.opts.ref){
			this.opts.value = [];
			for(var i =0; i<v.length; i+=1){
				this.opts.value[i] = appdb.EntityTypes.getEntityTypeByName(this.opts.getRef(v[i])).createInstance(v[i]);
			}
		}else{
			this.opts.value = v;
		}		
	};
	this._setterRef = function(v){
		this.opts.value = appdb.EntityTypes.getEntityTypeByName(this.opts.getRef(v)).createInstance(v);
	};
	this._getter = this._simpleGetter;
	this._setter = this._simpleSetter;
	
	
	this._init = function(){
		if($.isFunction(this.opts.ref)){
			this.opts.getRef = function(d){
				return this.ref(d);
			};
		}else{
			this.opts.getRef = function(){
				return this.ref;
			};
		}
		if(this.opts.isList){
			this._getter = this._getterList;
			this._setter = this._setterList;
		}else if(this.opts.ref){
			this._setter = this._setterRef;
		}
		this._handler = (function(wrapper,options,data){
			return function(v){
				if(arguments.length > 0){
					return wrapper._setter(v);
				}
				return wrapper._getter();
			};
		})(this,this.opts,d);
	};
	this._init();
	return this._handler;
};
appdb.EntityType = function(o){
	this.opts = $.extend({schema:[],extended:{}},o);
	var _fields = [];
	var _init = function(){
		var sc = this.opts.schema;
		for(var i in sc){
			sc[i].owner = this;
			_fields[i] = new appdb.EntityFieldSchema(sc[i]);
		}
	};
	_init.call(this);
	this.getEntityName = function(){
		return this.opts.name;
	};
	this.getName = function(){
		var name = this.getExtData("displayName") || this.opts.name;
		return ($.isFunction(name))?name(this):name;
	};
	this.getExtData = function(name){
		var n = $.trim(name);
		if(n){
			return appdb.utils.lookupObject(this.opts.extended,n);
		}
		return this.opts.extended;
	};
	this.getFields = function(){
		return _fields;
	};
	this.getField = function(name){
		for(var i=0; i<_fields.length; i+=1){
			if(_fields[i].getName()===name){
				return _fields[i];
			}
		}
		return undefined;
	};
	this.getFieldValue = function(name,variable){
		var f = this.getField(name);
		if(f){
			return f[variable];
		}
		return undefined;
	};
	this.createInstance = function(d){
		var meta = this;
		var inst = function(){
				this._meta_ = meta;
				this._data_ = d || null;
				for(var i in _fields){
					this[_fields[i].getName()] = _fields[i].createDataWrapper(d || {});
				}
				
				if(!this["value"] && d && $.isFunction(d.val)){
					this["value"] = d.val;
				}
			};
		return new inst();
	};
};
appdb.EntityTypes = (function(){
	var _registry = {};
	return {
		getEntityTypeByName: function(name){
			if(name){
				return _registry[name];
			}
			return undefined;
		},
		getRegistry : function(){
			return _registry;
		},
		define: function(name,schema,external,typens){
			if(!name){
				return false;
			}
			schema = schema || {};
			external = external || {};
			_registry[name] = new appdb.EntityType({"name":name,"schema":schema,"extended":external});
			
			return function entityConstructor(d){
				this._type_ = _registry[name];
				return this._type_.createInstance(d || {});
			};
		},
		extend: function(ext,name,schema,external,typens){
			if(!name){
				return false;
			}
			var base = this.getEntityTypeByName(ext);
			var bschema = base.opts.schema;
			var tmpschema = [],schobj = {}, bschobj = {};
			var i, len = schema.length;
			for(i=0; i<len; i+=1){
				schobj[schema[i].name] = schema[i]; 
			}
			len = bschema.length;
			for(i=0; i<len; i+=1){
				bschobj[bschema[i].name] = bschema[i]; 
			}
			schobj = $.extend(bschobj,schobj);
			
			for(i in schobj){
				if(schobj.hasOwnProperty(i)){
					tmpschema.push(schobj[i]);
				}
			}
			schema = $.extend(base.opts.schema,(schema || {}),true);
			external = $.extend(base.opts.extended,(external || {}),true);
			_registry[name] = new appdb.EntityType({"name":name,"schema":tmpschema,"extended":external});
			
			return function entityConstructor(d){
				this._type_ = _registry[name];
				return this._type_.createInstance(d || {});
			};
		}
	};
})();

appdb.entity = {};
appdb.entity.Discipline = appdb.EntityTypes.define("discipline",[
	{name: "id", canRef: true}
], {});
appdb.entity.Subdiscipline = appdb.EntityTypes.define("subdiscipline",[
	{name: "id", canRef: true}
], {});
appdb.entity.Middleware = appdb.EntityTypes.define("middleware",[
	{name: "id", canRef: true},
	{name: "link", canRef: true},
	{name: "comment", canRef: true}
], {});
appdb.entity.ApplicationStatus = appdb.EntityTypes.define("status",[
	{name: "id", canRef: true}
], {});
appdb.entity.ApplicationTags = appdb.EntityTypes.define("applicationTags", [
	{name: "id", canRef: true},
	{name: "ownerid", canRef: true},
	{name: "system", canRef: true}
], {displayName: "tag"});
appdb.entity.ApplictionTagPolicy = appdb.EntityTypes.define("applicationTagPolicy", [
	{name: "id", canRef: true},
	{name: "tagPolicy", canRef:true}
], {displayName: "application"});
appdb.entity.ApplicationUrl = appdb.EntityTypes.define("url",[
	{name: "id", canRef: true},
	{name: "type", canRef: true},
	{name: "title", canRef: true}
],{});
appdb.entity.Vo = appdb.EntityTypes.define("vo",[
	{name: "id", canRef: true}, 
	{name: "name", canRef: true}, 
	{name: "discipline", canRef: true}
], {});
appdb.entity.ProgLanguage = appdb.EntityTypes.define("proglang",[
	{name: "id", canRef: true}
], {});
appdb.entity.Country = appdb.EntityTypes.define("country",[
	{name: "id", canRef: true},
	{name: "isocode", canRef: true}, 
	{name: "regionid",canRef: true}
], {});
appdb.entity.Contact = appdb.EntityTypes.define("contact", [
	{name: "id", canRef: true}, 
	{name: "type", canRef: true}, 
	{name: "primary", canRef:true}
], {});
appdb.entity.Role = appdb.EntityTypes.define("role", [
	{name: "id", canRef: true}, 
	{name: "type", canRef:true}, 
	{name: "validated", canRef:true}
], {});
appdb.entity.Relation = appdb.EntityTypes.define("relation", [
	{name: "id", canRef: true},
	{name: "targetguid", canRef:true},
	{name: "subjectguid", canRef: true},
	{name: "hidden", canRef: true}
], {});
appdb.entity.ExtRelation = appdb.EntityTypes.define("extrelation", [
	{name: "id", canRef: true},
	{name: "hidden", canRef: true}
], {});
appdb.entity.Person = appdb.EntityTypes.define("person",[
	{name: "id", canRef: true},
	{name: "cname", canRef: true},
	{name: "firstname"},
	{name: "lastname"},
	{name: "institute", canIgnore: true},
	{name: "country", ref: "country", isExternalRef: true},
	{name: "permalink", canIgnore: true},
	{name: "application", isList: true, ref: "application", isExternalRef: true, canRef:false},
	{name: "image", canIgnore: true},
	{name: "role", ref: "role"},
	{name: "contact", isList: true, ref: "contact"},
	{name: "relation", isList: true, ref: "relation"}
], {});
appdb.entity.PublicationType = appdb.EntityTypes.define("publicationType",[
	{name: "id", canRef: true}
], {});
appdb.entity.PublicationAuthor = appdb.EntityTypes.define("author",[
	{name: "main", canRef: true},
	{name: "type", canRef: true},
	{name: "person", canRef: true, ref: "person", isExternalRef: true}
],{});
appdb.entity.PublicationExtAuthor = appdb.EntityTypes.define("extAuthor",[
	{name: "main", canRef: true},
	{name: "type", canRef: true},
	{name: "extAuthor", canRef: true}
],{displayName: "author"});
appdb.entity.Publication = appdb.EntityTypes.define("publication",[
	{name: "id", canRef: true},
	{name: "title", canRef: true},
	{name: "url", canRef: true},
	{name: "conference", canRef: true},
	{name: "proceedings", canRef: true},
	{name: "isbn", canRef: true},
	{name: "startPage", canRef: true},
	{name: "endPage", canRef: true},
	{name: "volume", canRef: true},
	{name: "publisher", canRef: true},
	{name: "journal", canRef: true},
	{name: "year", canRef: true},
	{name: "type", ref: "publicationType", canRef: true},
	{name: "author", isList: true, ref: function(data){return (data.type==="external")?"extAuthor":"author";}, canRef: true}
],{});
appdb.entity.ApplicationContactItem = appdb.EntityTypes.define("contactItem",[
	{name: "id", canRef: true},
	{name: "type", canRef: true}
],{});
appdb.entity.ApplicationContact = appdb.EntityTypes.extend("person","applicationContact",[
	{name: "contactItem", canRef: true, isList: true, ref: "contactItem", isExternalRef: true}
],{});
appdb.entity.category = appdb.EntityTypes.define("category",[
	{name: "id", canRef: true},
	{name: "primary", canRef: true}
]);
appdb.entity.ApplicationLicense = appdb.EntityTypes.define("license",[
	{name: "id", canRef: true},
	{name: "name", canRef: true},
	{name: "group", canRef: true},
	{name: "title", canRef: true},
	{name: "url", canRef: true},
	{name: "comment", canRef: true}
]);
appdb.entity.ApplicationBookmark = appdb.EntityTypes.define("applicationBookmark", [
	{name: "id", canRef: true}
], {displayName:"application"});
appdb.entity.Application = appdb.EntityTypes.define("application",[
	{name: "id", canRef: true},
	{name: "tool", type: "boolean", canRef: true},
	{name: "ratingCount", type: "number", canRef: true},
	{name: "popularity", type: "number", canRef: true},
	{name: "hitcount", type: "number", canRef: true},
	{name: "tagPolicy", type: "number", canRef: true},
	{name: "metatype", type: "number", canRef: true},
	{name: "category", isList: true,  ref: "category"},
	{name: "name"},
	{name: "description"},
	{name: "abstract"},
	{name: "owner", ref: "person"},
	{name: "addedby", ref: "person"},
	{name: "discipline", isList: true, ref: "discipline",isExternalRef: true},
	{name: "subdiscipline", isList: true, ref: "subdiscipline",isExternalRef: true},
	{name: "middleware", isList: true, ref: "middleware", isExternalRef: true},
	{name: "language", isList: true, ref: "proglang", isExternalRef: false},
	{name: "status", ref: "status"},
	{name: "contact", isList: true, ref: "applicationContact"},
	{name: "url", isList: true, ref: "url"},
	{name: "tag", isList: true, ref: "applicationTags"},
	{name: "permalink", canRef: false, isList: false},
	{name: "country", isList: true, ref: "country", isExternalRef: true},
	{name: "vo", isList: true, ref: "vo", isExternalRef: true},
	{name: "publication", isList: true, ref: "publication", isExternalRef: true},
	{name: "license", isList: true, canRef: true, ref: "license", isExternalRef: true, fieldprefix: "application"},
	{name: "logo"},
	{name: "relation", isList: true, ref: "relation"},
	{name: "extrelation",isList: true, ref: "extrelation"}
], {});
appdb.entity.ModeratedApplication = appdb.EntityTypes.define("applicationModerated",[
	{name: "id", canRef: true},
	{name: "moderationReason", canRef: true}
],{displayName: "application"});

appdb.entity.VirtualApplianceOs = appdb.EntityTypes.define("virtualapplianceos",[
	{name:"id", canRef: true},
	{name:"version", canRef: true}
], {displayName:"os"});
appdb.entity.VirtualApplianceArch = appdb.EntityTypes.define("virtualappliancearch",[
	{name:"id", canRef: true}
], {displayName:"arch"});
appdb.entity.VirtualApplianceChecksum = appdb.EntityTypes.define("virtualappliancechecksum",[
	{name:"id", canRef: true},
	{name:"hash", canRef: true}
], {displayName:"checksum"});
appdb.entity.VirtualApplianceFormat = appdb.EntityTypes.define("virtualapplianceformat",[
	{name:"id", canRef: true}
], {displayName:"format"});
appdb.entity.VirtualApplianceHypervisor = appdb.EntityTypes.define("virtualappliancehypervisor",[
	{name:"id", canRef: true}
], {displayName:"format"});
appdb.entity.VirtualApplianceRam = appdb.EntityTypes.define("virtualapplianceram",[
	{name:"id", canRef: true},
	{name: "minimum", canRef:true},
	{name: "recommended", canRef:true}
], {displayName:"ram"});
appdb.entity.VirtualApplianceCores = appdb.EntityTypes.define("virtualappliancecores",[
	{name:"id", canRef: true},
	{name: "minimum", canRef:true},
	{name: "recommended", canRef:true}
], {displayName:"cores"});
appdb.entity.VirtualApplianceAccelerators = appdb.EntityTypes.define("virtualapplianceaccelerators",[
	{name:"type", canRef: true},
	{name: "minimum", canRef:true},
	{name: "recommended", canRef:true}
], {displayName:"accelerators"});
appdb.entity.VirtualApplianceTrafficRules = appdb.EntityTypes.define("virtualappliancetrafficrules",[
	{name:"direction", canRef: true},
	{name: "protocols", canRef:true},
	{name: "ip_range", canRef:true},
	{name: "port_range", canRef:true}
], {displayName:"network_traffic"});
appdb.entity.VirtualApplianceOvf = appdb.EntityTypes.define("virtualapplianceovf",[
	{name:"id", canRef: true},
	{name: "url", canRef:true}
],{displayName:"ovf"});
appdb.entity.VirtualApplianceContextScriptChecksum = appdb.EntityTypes.define("virtualappliancecontextscriptchecksum",[
	{name: "hashtype", canRef: true }
],{displayName:"checksum"});
appdb.entity.VirtualApplianceContextScriptFormat = appdb.EntityTypes.define("virtualappliancecontextscriptformat", [
	{name: "id", canRef: true}
],{displayName:"format"});
appdb.entity.VirtualApplianceContextScript = appdb.EntityTypes.define("virtualappliancecontextscript",[
	{name: "id", canRef: true},
	{name: "url", canRef: true},
	{name: "name", canRef: true},
	{name: "title", canRef: true},
	{name: "size", canRef: true},
	{name: "format", canRef: true, ref: "virtualappliancecontextscriptformat"},
	{name: "checksum", canRef: true, ref: "virtualappliancecontextscriptchecksum"}
],{displayName: "contextscript"});
appdb.entity.VirtualApplianceImageInstance = appdb.EntityTypes.define("virtualapplianceimageinstance",[
	{name:"id", canRef: true},
	{name:"version", canRef: true},
	{name:"identifier",canRef:true},
	{name:"title",canRef:true},
	{name:"description",canRef:true},
	{name:"notes", canRef: true},
	{name:"arch", ref: "virtualappliancearch", canRef: true},
	{name:"os", ref: "virtualapplianceos", canRef: true},
	{name:"format", canRef: true},
	{name:"hypervisor", ref: "virtualappliancehypervisor", canRef: true},
	{name:"url", canRef: true},
	{name:"integrity", canRef: true},
	{name:"checksum", ref:"virtualappliancechecksum", canRef:true},
	{name:"size", canRef: true},
	{name:"ram", ref:"virtualapplianceram", canRef:true},
	{name:"cores", ref:"virtualappliancecores", canRef:true},
	{name:"accelerators", ref: "virtualapplianceaccelerators", canRef:true, isNullable: true},
	{name:"network_traffic", ref: "virtualappliancetrafficrules", canRef:true, isList:true, isNullable: true},
	{name:"ovf", ref:"virtualapplianceovf", canRef:true},
	{name:"contextscript", ref:"virtualappliancecontextscript", canRef: true}	
], {displayName: "instance"});
appdb.entity.VirtualApplianceImage = appdb.EntityTypes.define("virtualapplianceimage",[
	{name:"id", canRef: true},
	{name:"version", canRef: true},
	{name:"notes", canRef: true},
	{name:"group", canRef: true},
	{name:"instance", ref: "virtualapplianceimageinstance", isList: true, canRef: true}
], {displayName: "image"});
appdb.entity.VirtualApplianceInstance = appdb.EntityTypes.define("virtualapplianceinstance",[
		{name:"id", canRef:true},
		{name:"version", canRef:true},
		{name:"notes", canRef:true},
		{name:"published", canRef:true},
		{name:"enabled", canRef: true},
		{name:"archived", canRef: true},
		{name:"status", canRef: true},
		{name:"expireson", canRef: true},
		{name:"identifier",canRef:true},
		{name:"image", ref:"virtualapplianceimage", isList: true, canRef: true}
], {displayName: "instance"});
appdb.entity.VirtualAppliance = appdb.EntityTypes.define("virtualappliance",[
	{name:"id", canRef: true},
	{name:"appid", canRef: true},
	{name:"name", canRef:true},
	{name:"instance", isList: true, ref: "virtualapplianceinstance"}
],{displayName:"appliance", ns:["virtualization"]});
appdb.entity.ContextScriptFormat = appdb.EntityTypes.define("contextscriptformat",[
	{name:"id", canRef: true}
], {displayName:"format"});
appdb.entity.ContextScriptVAppliance = appdb.EntityTypes.define("contextscriptvappliance", [
	{name: "id", canRef: true}
],{displayName:"application", ns:["application"]});
appdb.entity.ContextScript = appdb.EntityTypes.define("contextscript",[
	{name: "id", canRef:true},
	{name: "url", canRef:true},
	{name: "title", canRef:true},
	{name: "description", canRef: true},
	{name: "format", canRef:true, ref: "contextscriptformat"},
	{name: "application", canRef: true, isList: true, ref: "contextscriptvappliance"}
],{displayName:"contextscript"});
appdb.entity.Contextualization = appdb.EntityTypes.define("contextualization",[
	{name:"id", canRef: true},
	{name:"version", canRef: true},
	{name:"description", canRef: true},
	{name:"contextscript", isList:true, canRef: true, ref: "contextscript"}
],{displayName:"context",ns:["contextualization"]});
appdb.entity.DatasetVersionLocationInterface = appdb.EntityTypes.define("datasetversionlocationinterface",[
	{name:"id", canRef: true}
],{displayName:"interface",ns:["dataset"]});
appdb.entity.DatasetVersionLocationExchnageformat = appdb.EntityTypes.define("datasetversionlocationexchangeformat",[
	{name:"id", canRef: true}
],{displayName:"exchange_format",ns:["dataset"]});
appdb.entity.DatasetVersionLocationSite = appdb.EntityTypes.define("datasetversionlocationsite",[
	{name:"id", canRef: true}
],{
	displayName:"site",ns:["site"]
});
appdb.entity.DatasetVersionLocationOrganization = appdb.EntityTypes.define("datasetversionlocationorganization",[
	{name:"id", canRef: true}
],{
	displayName:"organization",ns:["dataset"]
});
appdb.entity.DatasetVersionLocation = appdb.EntityTypes.define("datasetversionlocation",[
	{name:"id", canRef: true},
	{name:"master", canRef: true},
	{name:"uri", canRef: true},
	{name: "exchange_format", canRef: true, ref: "datasetversionlocationexchangeformat"},
	{name: "interface", canRef: true, ref: "datasetversionlocationinterface"},
	{name: "site",  isList: true, isExternalRef: true, ref: "datasetversionlocationsite", fieldprefix: "appdb"},
	{name: "organization", isList: true, isExternalRef: true, ref: "datasetversionlocationorganization", fieldprefix: "dataset"},
	{name: "notes", canRef: true}
],{displayName:"location",ns:["dataset"]});
appdb.entity.DatasetVersionParentVersion = appdb.EntityTypes.define("datasetversionparentversion",[
	{name:"id", canRef:true }
],{displayName:"parent_version", ns:["dataset"]});
appdb.entity.DatasetVersion = appdb.EntityTypes.define("datasetversion",[
	{name:"id", canRef: true},
	{name:"datasetid", canRef: true},
	{name:"parent_version", canRef: true, isExternalRef: true, ref: "datasetversionparentversion", isList: true},
	{name:"version", canRef: true},
	{name:"notes", canRef: true},
	{name:"location", isList: true, isExternalRef: true, ref: "datasetversionlocation"}
],{displayName:"version",ns:["dataset"]});
appdb.entity.DatasetUrl = appdb.EntityTypes.define("dataseturl",[
	{name: "type", canRef: true}
],{displayName:"url", ns:["dataset"]});
appdb.entity.DatasetParent = appdb.EntityTypes.define("datasetparent",[
	{name: "id", canRef: true}
],{displayName:"parent", ns:["dataset"]});
appdb.entity.Dataset = appdb.EntityTypes.define("dataset",[
	{name: "id", canRef: true},
	{name: "name", canRef: true},
	{name: "parent", canRef: true, isExternalRef: true, ref: "datasetparent", isList: true},
	{name: "description", canRef: true},
	{name: "url", isList: true, canRef: true, ref: "dataseturl",isExternalRef:true, fieldprefix: "dataset"},
	{name: "category", canRef: true,  fieldprefix: "dataset"},
	{name: "license", isList: true, canRef: true, ref: "license", isExternalRef: true, fieldprefix: "dataset"},
	{name: "discipline", isList: true, canRef: true, ref: "discipline", isExternalRef: true, fieldprefix: "discipline"}
],{});
appdb.utils.EntitySerializer = (function(defaultApiVersion){
	defaultApiVersion = defaultApiVersion || "1.0";
	var serializerType = function(){
		this._entitiesXMLTemplate = {
			"appdb": {
				namespace: "http://appdb.egi.eu/api/{:version}/appdb",
				uses: ["appdb","xs","xsi","history","dissemination","rating","application","person","vo","publication","regional","discipline","middleware","license","contextualization","dataset"]
			},
			"applicationModerated" : {
				namespace: "http://appdb.egi.eu/api/{:version}/application",
				attributes : ["id"]
			},
			"applicationBookmark": {
				namespace: "http://appdb.egi.eu/api/{:version}/application",
				attributes: ["id"]
			},
			"application": {
				namespace: "http://appdb.egi.eu/api/{:version}/application",
				uses: ["application","person","vo","publication","regional","xsi"],
				attributes: ["id","tool","ratingCount","popularity","hitcount","tagPolicy","metatype"]
			},
			"applicationTagPolicy": {
				namespace: "http://appdb.egi.eu/api/{:version}/application",
				uses: ["application","person","vo","publication","regional","xsi"],
				attributes: ["id","tagPolicy"]
			},
			"applicationContact": {
				namespace: "http://appdb.egi.eu/api/{:version}/application", 
				attributes: ["id"]
			},
			"contactItem": {
				namespace: "http://appdb.egi.eu/api/{:version}/application", 
				attributes: ["id","type"]
			},
			"publication": {
				namespace: "http://appdb.egi.eu/api/{:version}/publication", 
				attributes: ["id"]
			},
			"extAuthor": {
				namespace: "http://appdb.egi.eu/api/{:version}/publication", 
				attributes: ["main","type"]
			},
			"author": {			
				namespace: "http://appdb.egi.eu/api/{:version}/publication", 
				attributes: ["main", "type"]
			},
			"publicationType": {
				namespace: "http://appdb.egi.eu/api/{:version}/publication", 
				attributes: ["id"]
			},
			"person": {
				namespace: "http://appdb.egi.eu/api/{:version}/person", 
				uses: ["application","vo","publication","regional","xsi"],
				attributes: ["id","cname"]
			},
			"role": {
				namespace: "http://appdb.egi.eu/api/{:version}/person",
				attributes: ["id","type","validated"]
			},
			"contact": {
				namespace: "http://appdb.egi.eu/api/{:version}/person",
				attributes: ["id","type","primary","protected"]
			},
			"relation": {
				namespace: "http://appdb.egi.eu/api/{:version}/entity",
				attributes: ["id","targetguid", "subjectguid","hidden"]
			},
			"extrelation":{
				namespace: "http://appdb.egi.eu/api/{:version}/entity",
				attributes: ["id","hidden"]
			},
			"country": {
				namespace: "http://appdb.egi.eu/api/{:version}/regional", 
				attributes :["id","isocode","regionid"]
			},
			"vo": {
				namespace: "http://appdb.egi.eu/api/{:version}/vo",
				attributes: ["id","name","discipline"]
			},
			"url": {
				namespace: "http://appdb.egi.eu/api/{:version}/application", 
				attributes: ["id","type","title"]
			},
			"applicationTags": {
				namespace: "http://appdb.egi.eu/api/{:version}/application", 
				attributes: ["id","system","ownerid"]
			},
			"status": {
				namespace: "http://appdb.egi.eu/api/{:version}/application", 
				attributes: ["id"]
			},
			"middleware": {
				namespace: "http://appdb.egi.eu/api/{:version}/middleware",
				attributes: ["id","link", "comment"]
			},
			"proglang": {
				namespace: "http://appdb.egi.eu/api/{:version}/application", 
				attributes: ["id"]
			},
			"subdiscipline": {
				namespace: "http://appdb.egi.eu/api/{:version}/discipline",
				attributes: ["id"]
			},
			"discipline": {
				namespace: "http://appdb.egi.eu/api/{:version}/discipline",
				attributes: ["id"]
			},
			"category": {
				namespace: "http://appdb.egi.eu/api/{:version}/application",
				attributes: ["id", "primary"]
			},
			"license": {
				namespace: "http://appdb.egi.eu/api/{:version}/license",
				attributes: ["id", "name", "group"]
			},
			"virtualappliance": {
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				uses: ["virtualapplianceinstance"],
				inlinexmlns: true,
				attributes: ["id", "appid", "name"]
			},
			"virtualapplianceimage":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				uses: ["virtualapplianceimageinstance"],
				attributes: ["id"]
			},
			"virtualapplianceinstance":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				uses: ["virtualapplianceimage"],
				attributes: ["id", "version", "published", "enabled", "archived", "status","expireson"]
			},
			"virtualapplianceimageinstance":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				uses: ["virtualapplianceos", "virtualapplianceformat","virtualappliancechecksum","virtualappliancehypervisor"],
				attributes: ["id", "version", "integrity"]
			},
			"virtualapplianceos": {
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: [ "id", "version" ]
			},
			"virtualappliancearch":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["id"]
			},
			"virtualapplianceformat":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["id"]
			},
			"virtualappliancechecksum":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["hash"]
			},
			"virtualappliancehypervisor":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["id" ]
			},
			"virtualappliancecores":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["id","minimum", "recommended"]
			},
			"virtualapplianceram":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["id","minimum", "recommended"]
			},
			"virtualapplianceaccelerators":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				uses: ["xsi"],
				attributes: ["type","minimum", "recommended"]
			},
			"virtualappliancetrafficrules": {
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				uses: ["xsi"],
				attributes: ["direction", "protocols", "ip_range", "port_range"]
			},
			"virtualapplianceovf":{
				namespace: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["id","url"]
			},
			"virtualappliancecontextscript":{
				name: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["id"]
			},
			"virtualappliancecontextscriptchecksum":{
				name: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["hashtype"]
			},
			"virtualappliancecontextscriptformat":{
				name: "http://appdb.egi.eu/api/{:version}/virtualization",
				attributes: ["id"]
			},
			"contextscriptformat":{
				namespace: "http://appdb.egi.eu/api/1.0/contextualization",
				attributes: ["id"]
			},
			"contextscriptvappliance":{
				namespace: "http://appdb.egi.eu/api/{:version}/application",
				attributes: ["id"]
			},
			"contextscript":{
				namespace: "http://appdb.egi.eu/api/1.0/contextualization",
				uses: ["contextscriptformat","contextscriptvappliance"],
				attributes: ["id"]
			},
			"contextualization":{
				namespace: "http://appdb.egi.eu/api/1.0/contextualization",
				uses: ["contextscript"],
				attributes: ["id"]
			},
			"dataset":{
				namespace: "http://appdb.egi.eu/api/1.0/dataset",
				uses: ["dataset"],
				attributes: ["id", "category"]
			},
			"datasetversion":{
				namespace: "http://appdb.egi.eu/api/1.0/dataset",
				uses: ["dataset"],
				attributes: ["id","version","datasetid"]
			},
			"datasetversionlocation":{
				namespace: "http://appdb.egi.eu/api/1.0/dataset",
				attributes: ["id","master"]
			},
			"datasetversionlocationinterface": {
				namespace: "http://appdb.egi.eu/api/1.0/dataset",
				attributes: ["id"]
			},
			"datasetversionlocationexchangeformat": {
				namespace: "http://appdb.egi.eu/api/1.0/dataset",
				attributes: ["id"]
			},
			"datasetversionlocationsite": {
				namespace: "http://appdb.egi.eu/api/1.0/dataset",
				attributes: ["id"]
			},
			"datasetversionlocationorganization": {
				namespace: "http://appdb.egi.eu/api/1.0/organization",
				attributes: ["id"]
			},
			"dataseturl":{
				namespace: "http://appdb.egi.eu/api/1.0/dataset",
				attributes: ["type"]
			},
			"datasetparent":{
				namespace: "http://appdb.egi.eu/api/1.0/dataset",
				attributes: ["id"]
			},
			"datasetversionparentversion":{
				namespace: "http://appdb.egi.eu/api/1.0/dataset",
				attributes: ["id"]
			}
		};
		/**
		 *Creates the xml serializer instructions based on the api version used
		 *Parameters: 
		 *obj: the serializer object to set the new version
		 **/
		this._updateXMLEntities = function(){
			var entxml = $.extend(this._entitiesXMLTemplate,{},true);
			var version = this.currentVersion();
			this.entitiesXml = {};
			for(var i in entxml){
				this.entitiesXml[i] = {};
				this.entitiesXml[i].namespace = (""+entxml[i].namespace).replace("{:version}",version);
				if(entxml[i].inlinexmlns ) this.entitiesXml[i].inlinexmlns = entxml[i].inlinexmlns;
				if(entxml[i].attributes){
					this.entitiesXml[i].attributes = entxml[i].attributes.slice(0);
				}
				if(entxml[i].uses){
					this.entitiesXml[i].uses = entxml[i].uses.slice(0);
				}
			}
		};
		/**
		 *Setter/getter of the current api version to be used.
		 *If 'null' value is gievn then the property is reset to the default version
		 */
		this.currentVersion =(function(_this,_currentVersion){
			return function(v){
				if(typeof v === "undefined"){
					return _currentVersion;
				} else if( v === null ){
					_currentVersion = ('' + defaultApiVersion);
					_this._updateXMLEntities(_this);
				} else {
					_currentVersion = v;
					_this._updateXMLEntities(_this);
				}
				return _this;
			};
		})(this,(''+defaultApiVersion));
		
		this.namespaces = (function(opts){
			nss = nss | {};
			var res = {}, sh = opts["shared"] || [], nss = opts["namespaces"] || {};
			for(var i in nss){
				if(nss.hasOwnProperty(i) && $.isArray(nss[i])){
					res["v"+i] = $.extend({"sh":sh},{"nn":nss[i]},true)["nn"];
					for(var s=0; s<sh.length; s+=1){
						if(sh[s].ns){
							var n = sh[s].ns;
							if(n.indexOf("{:version}")>-1){
								n = n.replace("{:version}",i);
							}
							res["v"+i].push({"prefix": sh[s].prefix, "ns": n}); 
						}
					}
				}
			}
			res["default"] = "v" + defaultApiVersion;
			return res;
		})({"shared": [
				{prefix: "appdb", ns: "http://appdb.egi.eu/api/{:version}/appdb"},
				{prefix: "xs", ns: "http://www.w3.org/2001/XMLSchema"},
				{prefix: "xsi", ns: "http://www.w3.org/2001/XMLSchema-instance"},
				{prefix: "history", ns:"http://appdb.egi.eu/api/{:version}/history"},
				{prefix: "dissemination", ns: "http://appdb.egi.eu/api/{:version}/dissemination"},
				{prefix: "rating", ns: "http://appdb.egi.eu/api/{:version}/rating"},
				{prefix: "application", ns: "http://appdb.egi.eu/api/{:version}/application"},
				{prefix: "person", ns: "http://appdb.egi.eu/api/{:version}/person"},
				{prefix: "vo", ns: "http://appdb.egi.eu/api/{:version}/vo"},
				{prefix: "publication", ns: "http://appdb.egi.eu/api/{:version}/publication"},
				{prefix: "regional", ns: "http://appdb.egi.eu/api/{:version}/regional"},
				{prefix: "license", ns: "http://appdb.egi.eu/api/{:version}/license"},
				{prefix: "contextualization", ns: "http://appdb.egi.eu/api/{:version}/contextualization"},
				{prefix: "dataset", ns: "http://appdb.egi.eu/api/{:version}/dataset"}
			], namespaces: {"0.2": [], "1.0": [{prefix: "discipline", ns: "http://appdb.egi.eu/api/1.0/discipline"},{prefix:"middleware", ns:"http://appdb.egi.eu/api/1.0/middleware"},{prefix: "virtualization", ns: "http://appdb.egi.eu/api/1.0/virtualization"}]}});
		
		this.entitiesXml = {};
		this._getNamespaceByEntityName = function(ename,entityname){
			var e = this.entitiesXml[ename] || this.entitiesXml[entityname];
			return e.namespace;
		};
		this._getPrefixByEntityName = function(ename){
			var e = this.entitiesXml[ename];
			if(e){
				return this._getPrefixByNs(e.namespace);
			}
			return "";
		};
		this._getPrefixByNs = function(ns){
			var version = this.currentVersion(), nss = this.namespaces["v" + version] || this.namespaces[this.namespaces["default"]], len = nss.length;
			for(var i=0; i<len; i+=1){
				if(ns==nss[i].ns){
					return nss[i].prefix;
				}
			}
			return "";
		};
		this._getAttributesByEntityName = function(ename){
			var e = this.entitiesXml[ename];
			if(e && e.attributes){
				return e.attributes;
			}
			return [];
		};
		this._removeFieldsByName = function(from,fields){
			from = from || [];
			fields = fields || [];
			if(from.length===0){
				return [];
			}
			if(fields.length===0){
				return from;
			}
			var indexes = [];
			var fnames = {};
			for(var i=0; i<fields.length; i+=1){
				fnames[fields[i]]=fields[i];
			}
			for(i=0; i<from.length; i+=1){
				var f = from[i];
				var fname = f.getName();
				if(!fnames[fname]){
					indexes.push(from[i]);
				}
			}
			return indexes;
		};
		this._getReferableFields = function(fields){
			fields = fields || [];
			fields = $.isArray(fields)?fields:[fields];
			var refs = [];
			for(var i = 0; i<fields.length; i+=1){
				if(fields[i].canRef()){
					refs.push(fields[i]);
				}
			}	
			return refs;
		};
		this._XmlnsToString = function(xns){
			xns = xns || [];
			var res = "", i, len = xns.length;
			for(i=0; i<len; i+=1){
				res += ' xmlns:' + xns[i].prefix + '="' + xns[i].ns + '" ';
			}
			return res;
		};
		this._getNamespaceByPrefix = function(p){
			var i, len, ns, version = this.currentVersion();
			if(p){
				ns = this.namespaces["v"+version] || this.namespaces[this.namespaces["default"]];
				len = ns.length;
				for(i=0; i<len; i+=1){
					if(ns[i].prefix === p){
						return ns[i];
					}
				}
			}
			return null;
		};
		this._getInlineXmlnsByEntityMetaData = function(m){
			var e = this.entitiesXml[m.entityName], res = '';
			if(e && e.inlinexmlns === true && m.prefix && m.ns){
				res = ' xmlns:' + m.prefix + '="' + m.ns + '" ';
			}
			return res;
		};
		this._getXlnsForEntity= function(ename){
			var i, len, n, e = this.entitiesXml[ename], res = [];
			if(e && e.uses){
				len = e.uses.length;
				for(i=0; i<len; i+=1){
					n = this._getNamespaceByPrefix(e.uses[i]);
					if(n){
						res.push(n);
					}
				}
			}
			return res;
		};
		this._getMetaDataFromEntity = function(entity,prefix,tagName){
			var res = {}, meta;

			meta = entity._meta_;
			res.entity =  entity;
			res.entityName = meta.getEntityName();
			res.ns = this._getNamespaceByEntityName(meta.getEntityName());
			res.tagName = tagName || meta.getName();
			res.attributes = this._getAttributesByEntityName(meta.getEntityName());
			res.fields = this._removeFieldsByName(meta.getFields(),res.attributes);
			res.prefix = prefix || this._getPrefixByNs(res.ns);
			res.inlinexmlns = this._getInlineXmlnsByEntityMetaData(res);
			if(typeof prefix === "undefined" && typeof tagName === "undefined"){
				res.namespaces = this._XmlnsToString(this._getXlnsForEntity(meta.getEntityName()));
			}else{
				res.namespaces = "";
			}
			return res;
		};
		
		this.currentLevel = -1;
		this.tab = function(){
			var res = "";
			return res;
		};
		this.simpleField = function(name, prefix, value){
			if(this.canSerializeElement(name)===false){
				return "";
			}
			this.currentLevel +=1;
			var v = "",res = "";
			if(value && $.isFunction(value[name])){
				v = value[name]();
			}else if(typeof value === "undefined"){
				return "";
			}else {
				v = value || "";
			}
			v = $.isArray(v)?v:[v];
			if(v.length===0){
				res += this.tab() + "<" + prefix + ":" + name + "/>";
			}else{
				for(var i=0; i<v.length; i+=1){
					if($.trim(v[i]) === ""){
						res += this.tab() + "<" + prefix + ":" + name + "/>";
					}else{
						res += this.tab() + "<" + prefix + ":" + name + ">" + ( isNaN(v[i]) ? v[i].htmlEscape() : v[i]) + "</" + prefix + ":" + name + ">";
					}
				}
			}
			this.currentLevel -=1;
			return res;
		};
		this.toXmlNull = function(field,entity){
			var meta = (field.isExternalRef())?appdb.EntityTypes.getEntityTypeByName(field.ref()):entity._meta_;
			var ns = this._getNamespaceByEntityName(meta.getName(),meta.getEntityName());
			var p = field.getFieldPrefix() || this._getPrefixByNs(ns);
			var t = (field.isExternalRef())?meta.getName():field.getName();
			if(this.canSerializeElement(t)===false){
				return "";
			}
			return '<' + p + ':' + t +' xsi:nil="true" />';
		};
		this.canSerializeElement = function(name){
			var elems = this.excludeElements();
			var i, len = elems.length;
			for(i=0; i<len; i+=1){
				if(elems[i]==name){
					return false;
				}
			}
			return true;
		};
		this.excludeElements = (function(_this){
			var elems = [];
			return function(v){
				if(typeof v === "undefined"){
					return elems;
				}
				v = v || [];
				v = ($.isArray(v)?v:[v]);
				elems = v;
				return _this;
			};
		})(this);
		this.toXml = function(o,ext){
			var x = o || [];
			ext = ext || {};
			if(ext.excludeElements){
				ext.excludeElements = ($.isArray(ext.excludeElements)?ext.excludeElements:[ext.excludeElements]);
				this.excludeElements(ext.excludeElements);
			}
			if(ext.version){
				this.currentVersion(ext.version);
			}
			x = $.isArray(x)?x:[x];
			var i, len = x.length;
			var nss = this._XmlnsToString(this._getXlnsForEntity("appdb"));
			var res = '<appdb:appdb '+nss+' host="'+ appdb.config.endpoint.base +'" apihost="'+appdb.config.endpoint.baseapi+'" version="'+this.currentVersion()+'">';
			for(i=0; i<len; i+=1){
				res += this.toXmlElement(x[i]);
			}
			res += '</appdb:appdb>';
			this.excludeElements([]);
			this.currentVersion(null);
			return res;
		};
		this.toXmlElement = function(o, _prefix, _tagName, _fieldprefix){
			this.currentLevel +=1;
			var hasAttributes = false;
			var opts = o;
			if($.isPlainObject(o)===false){
				opts = this._getMetaDataFromEntity(o,_prefix,_tagName);
			}
			var prefix = opts.prefix;
			var tagName = opts.tagName;
			var tag = (_fieldprefix || prefix ) + ":" + tagName;
			var entity = opts.entity;
			var attrs = opts.attributes;
			var fields = opts.fields;
			var f,i,j, len = attrs.length, res = "<" + tag;
			if(this.canSerializeElement(tagName)===false){
				return "";
			}
			_parentprefix = "";
			res += opts.inlinexmlns + opts.namespaces; 
			//Render attributes
			for(i=0; i<len; i+=1){
				var av = null;
				if(entity[attrs[i]]){
					av = entity[attrs[i]]();
				}
				if(av){
					av = String(av).replace(/\"/g,"\u201d");
					res +=  ' '  + attrs[i] + '="' + (isNaN(av) ? av.toString().htmlEscape() : av) + '"';
					hasAttributes = true;
				}
			}
			//if the current call is a reference,
			//get the fields which canRef()===true
			if(_prefix && _tagName){ 
				fields = this._getReferableFields(fields);
			}

			len = fields.length;
			var inner = "";
			if(len > 0){
				for(i=0; i<len; i+=1){
					f = fields[i];
					var fname = f.getName();
					if(f.ref()){
						var vals = entity[f.getName()]();
						vals =(vals)?(($.isArray(vals))?vals:[vals]):[];
						if(vals.length>0){
							for(j=0; j<vals.length; j+=1){
								var val = vals[j];
								var p = (f.isExternalRef())?this._getPrefixByEntityName(val._meta_.getName()):prefix;
								var t = (f.isExternalRef())?val._meta_.getName():f.getName();
								if (val._data_ === null && f.isNullable()) {
								    inner += "\n"+ this.toXmlNull(f,entity);
								} else {
								    inner +=  "\n" + this.toXmlElement(val, p, t, (f.getFieldPrefix() || undefined));
								}

							}
						}else if(entity[f.getName()]()===null && (f.isList() || f.isNullable())){
							inner += this.tab() + "\t"+ this.toXmlNull(f,entity);
						}
					}else{
						var entval = entity[fname]();
						if(f.canIgnore()===true && entval==null){
							//do nothing
						} else if(entval==null && (f.isList() || f.isNullable())){
							inner += "\n" + this.toXmlNull(f,entity);
						} else {
							inner += "\n" + this.simpleField(fname,prefix,entval) ;
						}
						
					}
				}
				if(f.ref()){
					inner = "\n" + inner + "\n"+this.tab();
				}
			}else if($.isFunction(entity["value"]) && $.trim(entity["value"]())!==""){
				inner = entity["value"]();
				if ( isNaN(inner) ) inner = inner.htmlEscape();
			}
			if($.trim(inner)===""){
				inner = (entity["value"])?entity["value"]():"";
		                if ( isNaN(inner) ) inner = inner.htmlEscape();
				if(hasAttributes){
					if($.trim(inner)===""){
						res += "/>";
					}else{
						res += ">" + inner + "</" + tag + ">";
					}
				}else{
					res = "";
				}				
			}else{
				res += ">" + inner + "</" + tag + ">";
			}
			res = this.tab() + res;
			this.currentLevel -=1;
			return res;
		};
		this._updateXMLEntities();
	};
	return new serializerType();
})(appdb.config.apiversion);

appdb.utils.EditRegistry = (function(){
	var _registry = {
		"applicationData":undefined,
		"applicationEntity":undefined,
		"personData":undefined,
		"personEntity":undefined
	};
	function editRegistry(){
		
	}
	editRegistry.prototype.constructor = editRegistry;
	editRegistry.prototype.applicationData = function(v){
		if(v){
			if($.isPlainObject(v)){
				_registry.applicationData = v;
				_registry.applicationEntity = new appdb.entity.Application(v);
			}
		}
		return _registry.applicationData;
	};
	editRegistry.prototype.applicationEntity = function(v){
		if(v){
			if($.isPlainObject(v)){
				_registry.applicationData = v;
				_registry.applicationEntity = new appdb.entity.Application(v);
			}else{
				_registry.applicationEntity = v;
			}
		}
		return _registry.applicationEntity;
	};
	editRegistry.prototype.hasApplication = function(){
		return (_registry.applicationData)?true:false;
	};
	editRegistry.prototype.clearApplication = function(){
		_registry.applicationData = undefined;
		_registry.applicationEntity = undefined;
	};
	editRegistry.prototype.personData = function (v){
		if(v){
			if($.isPlainObject(v)){
				_registry.personData =v;
				_registry.personEntity = new appdb.entity.Application(v);
			}
		}
		return _registry.personData;
	};
	editRegistry.prototype.personEntity = function(v){
		if(v){
			if($.isPlainObject(v)){
				_registry.personData = v;
				_registry.personEntity = new appdb.entity.Application(v);
			}else{
				_registry.personEntity = v;
			}
		}
		return _registry.personEntity;
	};
	editRegistry.prototype.hasPerson = function(){
		return (_registry.personData)?true:false;
	};
	editRegistry.prototype.clearPerson = function(){
		_registry.personData = undefined;
		_registry.personEntity = undefined;
	};	
	editRegistry.prototype.clearAll = function(){
		this.clearPerson();
		this.clearApplication();
	};
	return new editRegistry();
})();
appdb.utils.EntityEditMapper = {};
appdb.utils.EntityEditMapper.Application = function(entity){
	this.entity = entity || new appdb.entity.Application();
	this.privs = new appdb.utils.Privileges(userAppPrivs);
	this.availableFields = {};
	this.fixMiddleware = function(v,index){
		var d = (appdb.model.Middlewares.getLocalData() || {middleware:[]}).middleware, len = d.length, i;
		var val = $.trim((v || "")).toLowerCase();
		if(val == ""){
			return false;
		}
		for(i=0; i<len; i+=1){
			if( $.trim(v) !== $.trim(d[i].id) ) continue;
			if($.trim(d[i].id)=="5"){
				return {id:"5", comment: val, link: d[i].link, val: "Other"};
			}
			if(val === $.trim((d[i].val()).toLowerCase()) || val == d[i].id){
				return {id: d[i].id, val: d[i].val()};
			}
		}
		
		return {val: v};
	};
	this.getMiddlewareLink = function(index){
		return $(":input[name^='lmw" + (index) + "']:last").val() || "";
	};
	this.SetListValue = function(obj,val){
		var o = obj();
		if(o === null || val !== null){
			obj(val);
		}else if(val === null){
			obj([]);
		}
	};
	this.setName = function(v){
		if(!this.privs.canChangeApplicationName()) return;
		if(v!==null){
			this.entity["name"](v);
		}
	};
	this.setProglangID = function(v){
		if(!this.privs.canChangeApplicationProgrammingLanguage()) return;
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				v[i] = {"id" : v[i]};
			}
		}
		this.entity.language(v);		
	};
	this.setCategoryID = function(v){
		if(!this.privs.canChangeApplicationCategory()) return;
		var i, len, j;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			j=0;
			if(v.length>0){
				var primary = v[0];
				v[0] = {"id": v[0], "primary": "true"};
				j = 1;
			}
			for(i=j; i<len; i+=1){
				v[i] = {"id" : v[i]};
			}
		}
		this.entity.category(v);
	};
	this.setDomainID = function(v){
		if(!this.privs.canChangeApplicationDiscipline()) return;
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				v[i] = {"id" : v[i]};
			}
		}
		this.entity.discipline(v);		
	};
	this.setMw = function(v){
		if(!this.privs.canChangeApplicationMiddleware()) return;
		var i, len, fixed, link;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				if($.isPlainObject(v[i])){
					fixed = {id: "5", comment: v[i].name, link: v[i].link, val: "Other"};
				}else{
					fixed = this.fixMiddleware(v[i],i);
				}
				if(fixed !== false){
					if (fixed.id == '5') {
						v[i] = {'id':'5', "val": function(){return 'Other';}};
						if (fixed.link) {
							v[i].link = fixed.link;
						}
						if (fixed.comment) {
							v[i].comment = fixed.comment;
						}
					} else {
						v[i] = {"val": (function(val){return function(){return val;};})(fixed.val)};
						if(fixed.link){
							v[i].link = fixed.link;
						}
						v[i].id = fixed.id;
					}
				}
			}
		}
		this.entity.middleware(v);
	};
	this.setVo = function(v){
		if(!this.privs.canChangeApplicationVO()) return;
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				v[i] = {"id" : v[i]};
			}
		}
		this.entity.vo(v);
	};
	this.setUrl = function(v){
		if(!this.privs.canChangeApplicationURLs()) return;
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				if(v[i].length>0 && v[i][0]=="{"){
					v[i] = JSON.parse(v[i]);
					v[i].val = (function(_v){return function(){return _v;}})(v[i].url);
				}
			}
		}
		this.entity.url(v);
	};
	this.setLicense = function(v){
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				if(v[i].length>0 && v[i][0]==="{"){
					v[i] = JSON.parse(v[i]);
					v[i].val = (function(_v){return function(){return _v;};})(v[i].url);
				}
			}
		}
		this.entity.license(v);
	};
	this.setCountryID = function(v){
		if(!this.privs.canChangeApplicationCountry()) return;
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				v[i] = {"id" : v[i]};
			}
		}
		this.entity.country(v);
	};
	this.setDocuments = function(val){
		if(!this.privs.canChangeApplicationDocuments()) return;
		var docCount = 0;
		var pubs = [];
		var selector = "document";
		var lv = ""+val;
		if($.browser.msie){
			lv = lv.replace(/documents/gi,"div").replace(/document/gi,"span");
			selector = "span";
		}
		$(lv).find(selector).each(function(index,elem){
			docCount++;
			var j, v = {}, d = decodeURIComponent(appdb.utils.base64.decode($(elem).html()));
			d = JSON.parse(d);
			v.id = d.id || "";
			v.title = d.title || "";
			v.url = d.url || "";
			v.conference = d.conference || "";
			v.proceedings = d.proceedings || "";
			v.isbn = d.isbn || "";
			v.startPage = d.pageStart || "";
			v.endPage = d.pageEnd || "";
			v.year = d.year || "";
			v.publisher = d.publisher || "";
			v.volume = d.volume || "";
			v.isbn = d.isbn || "";
			v.journal = d.journal || "";
			if(d.typeID){
				v.type = {"id": d.typeID, val: (function(v){return function(){return v;};})(d.type)};
			}
			v.author = [];
			if(d.intAuthors.length > 0){
				a = d.intAuthors;
				for(j=0; j<a.length; j+=1){
					v.author.push({
						"main": a[j][1],
						"type": "internal",
						"person": {"id": a[j][0]}
					});
				}
			}
			if(d.extAuthors.length > 0){
				a = d.extAuthors;
				for(j=0; j<a.length; j+=1){
					v.author.push({
						"main": a[j][1],
						"type": "external",
						"extAuthor": a[j][0]
					});
				}
			}
			pubs.push(v);
		});
		if(pubs.length>0){
			this.entity.publication(pubs);
		} else{
			this.entity.publication(null);
		}
	};
	this.setCntpnt = function(v){
		var i, j, len, cnts = {};
		var contacts = this.entity.contact();
		contacts = $.isArray(contacts)?contacts:[contacts];
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				cnts[v[i].researcherid] = cnts[v[i].researcherid] || [];
				cnts[v[i].researcherid].push({
							"id": v[i].itemid, 
							"type": v[i].itemtype, 
							"val": (function(val){return function(){return val;};})(v[i].item)
						});
			}
			len = contacts.length;
			
			for(i=0; i<len; i+=1){
				j = contacts[i].id();
				if(cnts[j]){
					contacts[i].contactItem(cnts[j]);
				}
			}
		}
	};
	this.setScicon = function(v){
		if(!this.privs.canAssociatePersonToApplication()) return;
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				v[i] = {"id":v[i]};
			}
		}
		this.entity.contact(v);
	};
	this.setId = function(v){
		if(v!==null){
			this.entity.id(v);
		}
	};
	this.setDescription = function(v){
		if(!this.privs.canChangeApplicationDescription()) return;
		if(v!==null){
			this.entity.description(v);
		}
	};
	this.setAbstract = function(v){
		if(!this.privs.canChangeApplicationAbstract()) return;
		if(v!==null){
			this.entity["abstract"](v);
		}
	};
	this.setNewimage = function(v){
		if(!this.privs.canChangeApplicationLogo()) return;
		if(v!==null){
			this.entity.logo(v);
		}
	};
	this.setStatusID = function(v){
		if(!this.privs.canChangeApplicationStatus()) return;
		if(v!== null){
			v = {"id" : v};
		}
		this.entity.status(v);
	};
	this.setOwner = function(v){
		if(!this.privs.canGrantOwnership()) return;
		if(v!== null){
			v = {"id" : v};
		}
		this.entity.owner(v);
	};
	this.setAddedBy = function(v){
		if(v!== null){
			v = {"id" : v};
		}
		this.entity.addedby(v);
	};
	this.setRelation = function(v){
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				v[i] = JSON.parse(v[i]);
			}
		}
		this.entity.relation(v);
	};
	this.setExtrelation = function(v){
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				v[i] = JSON.parse(v[i]);
			}
		}
		this.entity.extrelation(v);
	};
	this.setMetatype = function(v){
		if(v!==null){
			this.entity["metatype"](v);
		}
	};
	this.resetAvailableFields = function(){
		var i;
		for(i in this){
			if(this.hasOwnProperty(i)){
				if(i.substring(0, 3)==="set"){
					this.availableFields[i] = true;
				}
			}
		}
	};
	this.loadFromForm = function(frmelem){
		var arr = [];
		var mapcontext = {};		
		//Retrieve input elements
		$(frmelem).find("input").each(function(index,elem){
			arr.push(elem);
		});
		
		//retrieve textarea elements (for abstract field)
		$(frmelem).find("textarea").each(function(index,elem){
			arr.push(elem);
		});

		//Debug message of retrieved items
		$(arr).each(function(index,elem){
			var name = $(elem).attr("name");
			if(name){
				appdb.debug( name + " : " + $(elem).val());
			}
		});
		
		//parse _dom settings and fill array of functions with values
		$(arr).each((function(_this){
			return function(index,elem){
				var name = $(elem).attr("name");
				if(!name){
					return;
				}
				var nameIndex = name.match(/[0-9]+/);
				name = name.replace(/[0-9]/g,'');

				name = name[0].toUpperCase() + name.substr(1);

				if($.isFunction(_this["set" + name])){
					if(nameIndex){
						mapcontext["set"+name] = mapcontext["set"+name] || [];
						if(name.toLowerCase() === "mw" && $(elem).parent().find("input[name^='lmw']").length!==0){
							mapcontext["set"+name].push({name: $(elem).val(), link: $(elem).parent().find("input[name^='lmw']:last").val()});
						}else{
							mapcontext["set"+name].push($(elem).val());
						}
						
					}else{
						mapcontext["set"+name] = $(elem).val();
					}
					
				}
			};
		})(this));
		
		return mapcontext;
	};
	this.UpdateEntity = function(frmelem){
		var i, v = this.loadFromForm(frmelem), contactItems = [];
		for(i in v){
			if(v.hasOwnProperty(i)){
				if(i==="setCntpnt"){
					for(var j=0; j<v[i].length; j+=1){
						contactItems.push(JSON.parse(v[i][j]));
					}
				}else{
					this[i](v[i]);
				}
				this.availableFields[i] = false;
			}
		}
		if(contactItems.length>0 && this.availableFields["setCntpnt"]===false){
			this.setCntpnt(contactItems);
		}
		//set null to the rest of the fields
		for(i in this.availableFields){
			if(this.availableFields.hasOwnProperty(i)){
				if(this.availableFields[i]===true){
					this[i](null);
				}
			}
		}
		//reset available fields
		this.resetAvailableFields();
	};
	this.resetAvailableFields();
};

appdb.utils.EntityEditMapper.Person = function(entity){
	this.entity = entity || new appdb.entity.Person();
	this.formElement = undefined;
	this.isChanged = function(name){
		if($.trim(name) === ""){
			return false;
		}
		var ch = appdb.utils.DataWatcher.Registry.getActive().instance.getChangedItemNames();
		var hasChanged = false;
		if(ch !== false){
			for(var i=0; i<ch.length; i+=1){
				if(ch[i].toLowerCase() === name.toLowerCase()){
					hasChanged = true;
				}
			}
		}
		return hasChanged;
	};
	this.getContactTypeID = function(v){
		var cdi = contactTypeDataObject.ids;
		cdi = cdi || [];
		cdi = $.isArray(cdi)?cdi:[cdi];
		var i, len = cdi.length;
		for(i=0; i<len; i+=1){
			if(cdi[i] == v){
				return contactTypeDataObject.vals[i];
			}
		}
		return undefined;
	};
	this.setId = function(v){
		if(v!==null){
			this.entity.id(v);
		}
	};
	this.setCnamesuffix = function(v){
		if( appdb.config.routing.useCanonical === true ){
			if(v!==null || $.trim(v)!==""){
				var p = $(this.formElement).find(".prefix");
				if( $(p).length > 0 && $.trim($(p).text()) !== "" ){
					v = $.trim($(p).text() + v);
					this.entity.cname(v);
				}
			}
		}
	};
	this.setFirstName = function(v){
		if(v!==null){
			this.entity["firstname"](v);
		}
	};
	this.setLastName = function(v){
		if(v!==null){
			this.entity["lastname"](v);
		}
	};
	this.setPositionTypeID = function(v){
		if(this.isChanged("role")===false){
		}
		var data = {"id": v};
		var canAutoValidate = (userRole == 5 || userRole == 7);
		var noValidationNeeded = (v == 3 || v ==4);
		if(canAutoValidate || noValidationNeeded){
			data["validated"] = true;
		}
		if(v!==null){
			this.entity["role"](data);
		}
	};
	this.setInstitution = function(v){
		if(this.isChanged("institute")===false){
		}
		if(v!==null){
			this.entity["institute"](v);
		}
	};
	this.setCountryID = function(v){
		if(v!==null){
			this.entity["country"]({"id": v});
		}
	};
	this.setContactType = function(v){
		var i, j, len, cnts = {};
		var contacts = this.entity.contact();
		contacts = $.isArray(contacts)?contacts:[contacts];
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				cnts[v[i]] = cnts[v[i].researcherid] || [];
				cnts[v[i]].push({
							"id": v[i].itemid, 
							"type": v[i].itemtype, 
							"val": (function(val){return function(){return val;};})(v[i].item)
						});
			}
			len = contacts.length;
			
			for(i=0; i<len; i+=1){
				j = contacts[i].id();
				if(cnts[j]){
					contacts[i].contactItem(cnts[j]);
				}
			}
		}
	};
	this.setContact = function(v){
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				v[i] = {
					"type":  this.getContactTypeID($(this.formElement).find("input[name='contactType"+i+"']:last").val()),
					"val": (function(d){return function(){return d;};})(v[i])
				};
			}
		}
		this.entity.contact(v);
	};
	this.setNewimage = function(v){
		if(this.isChanged("profile image")===false){
			return;
		}
		this.entity.image(v);
	};
	this.setRelation = function(v){
		var i, len;
		if(v!== null){
			v = $.isArray(v)?v:[v];
			len = v.length;
			for(i=0; i<len; i+=1){
				v[i] = JSON.parse(v[i]);
			}
		}
		this.entity.relation(v);
	};
	this.loadFromForm = function(frmelem){
		var arr = [];
		var mapcontext = {};
		//Retrieve input elements
		$(frmelem).find("input").each(function(index,elem){
			arr.push(elem);
		});
		
		//retrieve textarea elements (for abstract field)
		$(frmelem).find("textarea").each(function(index,elem){
			arr.push(elem);
		});

		//Debug message of retrieved items
		$(arr).each(function(index,elem){
			var name = $(elem).attr("name");
			if(name){
				appdb.debug( name + " : " + $(elem).val());
			}
		});
		
		//parse _dom settings and fill array of functions with values
		$(arr).each((function(_this){
			return function(index,elem){
				var name = $.trim($(elem).attr("name"));
				var nameIndex = name.match(/[0-9]+/);
				if(!name){
					return;
				}
				name = name.replace(/[0-9]/g,'');

				name = name[0].toUpperCase() + name.substr(1);

				if($.isFunction(_this["set" + name])){
					if(nameIndex){
						mapcontext["set"+name] = mapcontext["set"+name] || [];
						mapcontext["set"+name].push($(elem).val());
					}else{
						mapcontext["set"+name] = $(elem).val();
					}
					
				}
			};
		})(this));
		return mapcontext;
	};
	this.UpdateEntity = function(frmelem){
		var i, v = this.loadFromForm(frmelem);
		this.formElement = frmelem;
		for(i in v){
			if(v.hasOwnProperty(i)){
				this[i](v[i]);
			}
		}
	};
	
};

appdb.utils.EntityEditMapper.VirtualAppliance = function(entity){
	this.entity = entity || new appdb.entity.VirtualAppliance();
	this.getInstances = function(v){
		v = v || [];
		v = $.isArray(v)?v:[v];
		var res = [];
		$.each(v, (function(self){
			return function(i, e){
				e.image = e.image || [];
				e.image = $.isArray(e.image)?e.image:[e.images];
				res.push( e ) ;
			};
		})(this));
		this.entity.instance( res );
	};
	this.UpdateEntity = function(d){
		this.entity.id( d.id );
		this.entity.appid( d.appid );
		this.entity.name( d.name );
		this.getInstances(d.instance);
		
		return this.entity;
	};
};
appdb.utils.EntityEditMapper.ContextScript = function(entity){
	this.entity = entity || new appdb.entity.ContextScript();
	this.getVappliances = function( v ){
		v = v || [];
		v = $.isArray(v)?v:[v];
		var res = [];
		$.each(v, (function(self){
			return function(i, e){
				e.application = e.application || [];
				e.contextscript = $.isArray(e.application)?e.application:[e.application];
				res.push( e ) ;
			};
		})(this));
		this.entity.application( res );
	};
	this.UpdateEntity = function(d){
		this.entity.id( d.id );
		this.entity.url( d.url );
		this.entity.title( d.title );
		this.entity.description( d.description );
		this.entity.format( d.format );
		this.getVappliances( d.application );
		return this.entity;
	};
};
appdb.utils.EntityEditMapper.Contextualization = function(entity){
	this.entity = entity || new appdb.entity.Contextualization();
	this.getContextScripts = function(v){
		v = v || [];
		v = $.isArray(v)?v:[v];
		var res = [];
		$.each(v, (function(self){
			return function(i, e){
				e.contextscript = e.contextscript || [];
				e.contextscript = $.isArray(e.contextscript)?e.contextscript:[e.contextscript];
				res.push( e ) ;
			};
		})(this));
		this.entity.contextscript( res );
	};
	this.UpdateEntity = function(d){
		this.entity.id( d.id );
		
		if( typeof d.version !== "undefined" && d.version !== null ){
			this.entity.version( $.trim(d.version) );
		}
		
		if( typeof d.description !== "undefined" && d.description !== null ){
			this.entity.description( $.trim(d.description) );
		}
		
		this.getContextScripts(d.contextscript);
		
		return this.entity;
	};
};
appdb.utils.EntityEditMapper.Dataset = function(entity){
	this.entity = entity || new appdb.entity.Dataset();
	this.getDisciplines = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var ids = [];
		$.each(d, function(i,e){
			if(e && $.trim(e.id) !== "" && e.id << 0 > 0){
				ids.push({id: $.trim(e.id)});
			}
		});
		this.entity.discipline(ids);
	};
	this.getLicenses = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var items = [];
		if(d.length > 0){
			$.each(d, function(i,e){
				e.val = (function(_v){return function(){return _v;};})(e.url);
				items.push(e);
			});
		}
		this.entity.license(items);
	};
	this.getUrls = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var items = [];
		
		$.each(d, function(i,e){
			if( e && !$.isEmptyObject(e)){
				items.push(e);
			}
		});
		
		if( items.length > 0 ) {
			this.entity.url(items);
		} else {
			this.entity.url(null);
		}
	};
	this.getCategory = function(d){
		if( d ){
			if( $.isPlainObject(d) && typeof d.val === "function" ){
				this.entity.category(d.val());
			}else if( typeof d === "string" ){
				this.entity.category(d);
			}
		}
	};
	this.getParent = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		this.entity.parent((d.length === 0)?undefined:d);
		
	};
	this.UpdateEntity = function(d){
		d = d || {};
		this.entity.id( d.id );
		this.entity.name( $.trim(d.name) );
		this.entity.description( $.trim(d.description) );
		this.getParent(d.parent);
		this.getDisciplines(d.discipline);
		this.getLicenses(d.license);
		this.getUrls(d.url);
		this.getCategory(d.category || "Life Sciences");
		return this.entity;
	};
};
appdb.utils.EntityEditMapper.DatasetVersion = function(entity){
	this.entity = entity || new appdb.entity.DatasetVersion();
	this.getLocations = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		var items = [];
		$.each(d, function(i,e){
			if(e && $.trim(e.uri) !== "" && e.exchange_format && $.trim(e.exchange_format.id)<<0 > 0 && e["interface"] && $.trim(e["interface"].id)<<0 > 0){
				if( isNaN(parseInt(e.id)) ){
					e.id = "";
				}
				if( e.site !== null ){
					e.site = e.site || [];
					e.site = $.isArray(e.site)?e.site:[e.site];
					if( e.site.length === 0 ){
						e.site = null;
					}
				}
				if( e.organization !== null ){
					e.organization = e.organization || [];
					e.organization = $.isArray(e.organization)?e.organization:[e.organization];
					if( e.organization.length === 0 ){
						e.organization = null;
					}
				}
				items.push(e);
			}
		});
		this.entity.location((items.length===0)?null:items);
	};
	this.getParentVersion = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		
		if( d.length === 0 ){
			this.entity.parent_version(null);
		}else{
			this.entity.parent_version([d[0]]);
		}
		
	};
	this.UpdateEntity = function(d){
		d = d || {};
		if( isNaN(parseInt(d.id)) ){
			d.id = "";
		}
		this.entity.id( d.id );
		this.entity.datasetid(d.datasetid);
		this.entity.version( $.trim(d.version) );
		this.entity.notes( $.trim(d.notes) );
		this.getParentVersion( d.parent_version );
		this.getLocations(d.location);
		return this.entity;
	};
};
/**
 * This is a generic rest API error handler. Parses the error status and description to 
 * return an appropriate error display. Internally it uses the appdb.views.ErrorHandler
 * parameters:
 *	d: the data object returned on ajax error from the rest call
 *	errObj: a user defined error object to extend the default error object
 *	itemname: a refference name for the resource
 */
appdb.utils.RestApiErrorHandler = function(d,errObj,itemname){
	d = d || {};
	errObj = errObj || {
		"status": "An error occured",
		"description": d.description || "Unknown error" 
	};
	var transformErrorDescription = function(errordesc) {
	    errordesc = $.trim(errordesc || '');

	    if (errordesc && errordesc.indexOf('DEBUG DATA:') > -1) {
		    errordesc = errordesc.split('DEBUG DATA:');
		    if (errordesc.length > 1) {
			    return errordesc[0] + '<div class="debug-data"><span>Report Data:</span><div class="value">' + errordesc[1] + '</div></div>';
		    }
	    }

	    return errordesc;
	};
	var _init = function(){
		var err = {
			"status": errObj.status,
			"description": errObj.description
		};
		//Setup error according to rest response type
		if(d && d.response){
			if(typeof d.response === "string" && $.trim(d.response) !== ""){
				err.description = d.response;
			}else if(d.response.error){
				err.description = d.response.errordesc;
			}
		}
        if ( d.errornum ) d.response = d;
		d.response = d.response || {};
		switch(d.response.errornum){
			case "1": //access denied
				err.description = "You don't have access rights for this action";
				break;
			case "2": //item not found
				err.description = "The " + (itemname || "item") + " cannot be found.";
				break;
			case "3": //invalid representation
				err.description = "The data representation is not correct";
				break;
			case "4": //invalid method
				err.description = "The specific action cannot be applied to " + (itemname || "here");
				break;
			case "5": //invalid resource
				err.description = "The type of " + (itemname || " the item requested") + " cannot be found";
				break;
			case "6":
			default://backend error
				err.description =d.response.errordesc || d.response.error || d.description ||"";
				if($.trim(err.description)!==""){
					err.description = err.description.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
					var m = err.description.match(/^[\w\s]*\./);
					if(m && m.length>0){
						err.description = err.description.replace(/^[\w\s]*\./,"<div><b>" + m[0] + "</b></div><div style='text-align:justify'><p><span style='display:inline-block;width:10px;'>   </span>") + "</p></div>";
					}
				}
				break;
		}
		
		errObj["status"] = err["status"];
		errObj["description"] = transformErrorDescription(err["description"]);
	};
	var _dialog = new appdb.views.ErrorHandler();
	
	var _show = function(){
		_dialog.handle(errObj);
	};
	var _errorObject = function(d){
		if(typeof d === "undefined"){
			return errObj;
		}
		errObj.status = d.status || errObj.status;
		errObj.description = d.description || errObj.description;
		return _returnObject; 
	};
	var _returnObject = {
		show: _show,
		errorObject: _errorObject
	};
	_init();
	return _returnObject;
};

appdb.utils.AutoComplete = appdb.DefineClass("appdb.utils.AutoComplete",function(o){
	this.app_data = [];
	this.ppl_data = [];
	this.vo_data = [];
	this.app_data2 = [];
	this.ppl_data2 = [];
	this.site_data = [];	
	
	this.vo_data2 = "id name description".split(" ");
	this.domain_data2 = "any id name".split(" ");
	this.country_data2 = "any id name isocode".split(" ");
	this.middleware_data2 = "any id name".split(" ");
	this.category_data2 = "any id name".split(" ");
	this.site_data2 = "any id name description country roc subgrid tier".split(" ");
	
	this.concatData = function (adata, bdata, s) {
		var tmp,i;
		tmp = bdata;
		for(i=0; i<bdata.length; i++) {
			tmp[i] = s+"."+tmp[i];
		}
		return adata.concat(tmp);
	};
	this.domAutoComplete = null;
	this.appAutoComplete = function(e) {
		var adata = new Array();
		adata = this.concatData(adata, this.app_data.slice(0), "application");
		adata = this.concatData(adata, this.ppl_data2.slice(0), "person");
		adata = this.concatData(adata, this.domain_data2.slice(0), "discipline");
		adata = this.concatData(adata, this.domain_data2.slice(0), "subdiscipline");
		adata = this.concatData(adata, this.vo_data2.slice(0), "vo");
		adata = this.concatData(adata, this.country_data2.slice(0), "country");
		adata = this.concatData(adata, this.middleware_data2.slice(0), "middleware");
		adata = this.concatData(adata, this.category_data2.slice(0), "category");
		adata = this.concatData(adata, ["any"], "any");
		this.autoComplete(e, adata);
	};
	this.pplAutoComplete = function(e){
		var adata = new Array();
		adata = this.concatData(adata, this.ppl_data.slice(0), "person");
		adata = this.concatData(adata, this.app_data2.slice(0), "application");
		adata = this.concatData(adata, this.domain_data2.slice(0),"discipline");
		adata = this.concatData(adata, this.domain_data2.slice(0), "subdiscipline");
		adata = this.concatData(adata, this.vo_data2.slice(0), "vo");
		adata = this.concatData(adata, this.country_data2.slice(0), "country");
		adata = this.concatData(adata, this.middleware_data2.slice(0), "middleware");
		adata = this.concatData(adata, this.category_data2.slice(0), "category");
		adata = this.concatData(adata, ["any"], "any");
		this.autoComplete(e, adata);
	};
	this.vosAutoComplete = function(e){
		var adata = new Array();
		adata = this.concatData(adata, this.vo_data.slice(0), "vo");
		adata = this.concatData(adata, this.app_data2.slice(0), "application");
		adata = this.concatData(adata, this.ppl_data.slice(0), "person");
		adata = this.concatData(adata, this.domain_data2.slice(0),"discipline");
		adata = this.concatData(adata, this.domain_data2.slice(0), "subdiscipline");
		adata = this.concatData(adata, this.country_data2.slice(0), "country");
		adata = this.concatData(adata, this.middleware_data2.slice(0), "middleware");
		adata = this.concatData(adata, this.category_data2.slice(0), "category");
		adata = this.concatData(adata, ["any"], "any");
		this.autoComplete(e, adata);
	};
	this.sitesAutocomplete = function(e){
		var adata = new Array();
		adata = this.concatData(adata, this.site_data.slice(0), "site");
		adata = this.concatData(adata, this.vo_data2.slice(0), "vo");
		adata = this.concatData(adata, this.app_data2.slice(0), "application");
		adata = this.concatData(adata, this.category_data2.slice(0), "category");
		adata = this.concatData(adata, this.domain_data2.slice(0),"discipline");
		adata = this.concatData(adata, this.middleware_data2.slice(0),"middleware");
		adata = this.concatData(adata, this.country_data2.slice(0), "country");
		adata = this.concatData(adata, ["any"], "any");
		this.autoComplete(e, adata);
	};
	this.autoComplete = function(e, adata){
		var predata = '$ ~ = + - &lt; &gt; &lt;= &gt;= +~ += +&lt; +&gt; +&lt;= +&gt;= -~ -= -&lt; -&gt; -&lt;= -&gt;='.split(" ");
			var data = [];
			adata = adata || [];
			var i,j, ilen, jlen;
			ilen = adata.length;
			jlen = predata.length;
			for(i=0; i<ilen; i++) {
				data.push(adata[i]);
			}
			for(i=0; i<ilen; i+=1) {
				for(j=0; j<jlen; j+=1) {
					data.push(predata[j]+adata[i]);
				}
			}

			$(e).off("keyup").on("keyup",function(event){
				var txt = $(e).val();
				if(event.which===13 && $.trim(txt).length>0 && $.trim(txt)[$.trim(txt).length-1]===":" && $(".ac_results").is(":visible")===false){
					event.preventDefault();
					return false;
				}
				if(e.createTextRange) {
					var range = this.createTextRange();
					range.move('character', txt.length);
					range.select();
				}
				else {
					if(e.selectionStart) {
						e.focus();
						e.setSelectionRange(txt.length, txt.length);
					}
					else
						e.focus();
				}
				return true;
			});
			$(e).off(($.browser.opera ? "keypress" : "keydown"+".autocompletecustom")).on(($.browser.opera ? "keypress" : "keydown"+".autocompletecustom") ,function(event){
				var txt = $(this).val();
				if(event.which===13 && $(".ac_results").is(":visible")){
					$(e).val(txt);
					$(".ac_results").hide();
					event.preventDefault();
					return false;
				}
			});
			try {
				if(this.domAutoComplete !== null){
					this.domAutoComplete("destroy"); 
				}
				this.domAutoComplete = $(e).filteringAutocomplete(data,{
					source:data,
					multiple: true, 
					multipleSeparator: " ",
					matchContains : true,
					max: data.length,
					scroll: true,
					autoFill: false,
					selectFirst: false,
					formatResult: function(data,pos,n) {
						if(data.length>0 && $.trim(data)[$.trim(data).length-1]!==":"){
							data += ":";
						}
						return data;
					}
				});
			} catch (e) {
				appdb.debug("[autocomplete]:",e);
				this.domAutoComplete = "";
			}
			return this.domAutoComplete;
	};
	this.call = function(){
		this.delegate(this.textbox);
	};
	this._init = function(){
		this.parent = o.parent || {};
		this.textbox = o.textbox || {};
		this.filteringType = o.type || "";
		if ( (this.filteringType === "") || (typeof this.filteringType === "undefined") ) {
			if ( this.parent ) {
				if ( this.parent.views ) {
					if ( this.parent.views._export ) {
						this.filteringType = (this.parent.views._export)?this.parent.views._export.target:"";
					} else if ( this.parent.views.peopleList ) {
						this.filteringType = "people";
					}
				}
			}
		}
		switch(this.filteringType){
			case "apps":
				this.delegate = this.appAutoComplete;
				break;
			case "people":
				this.delegate = this.pplAutoComplete;
				break;
			case "sites":
				this.delegate = this.sitesAutocomplete;
				break;
			case "vos":
			default:
				this.delegate = this.vosAutoComplete;
				break;
		}
		(function(_this){
			var timer = null;
			timer = setInterval(function(){
				if( (new appdb.utils.filterFields().getLocalList("applications", "application")) ){
					clearInterval(timer);
					_this.app_data = new appdb.utils.filterFields().getList("applications", "application");
					_this.ppl_data = new appdb.utils.filterFields().getList("people", "person");
					_this.vo_data = new appdb.utils.filterFields().getList("vos", "vo");
					_this.site_data = new appdb.utils.filterFields().getList("sites", "site");
					_this.app_data2 = new appdb.utils.filterFields().getList("people", "application");
					_this.ppl_data2 = new appdb.utils.filterFields().getList("applications", "person");
					_this.call();
				}
			},2);
		})(this);
		
		return {
			"call" : this.call
		};
	};
	return this._init();
});

appdb.utils.rebuildSearchCache = function() {
	$.ajax({
		url: '/help/rebuildsearchcache', 
		success: function(data) {
			alert(data);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert(errorThrown);
		}
	});
};

appdb.utils.clearSearchCache = function() {
	$.ajax({
		url: '/help/clearsearchcache', 
		success: function(data) {
			alert(data);
		},
		error: function(jqXHR, textStatus, errorThrown) {
			alert(errorThrown);
		}
	});
};

appdb.utils.ExpandButton = function(o){
	o = o || {};
	if(!o.dom) return;
	this.display = o.display || "actions";
	this.dom = $(o.dom);
	this.container = $(document.createElement("div")).addClass("expandButtonContainer");
	this.action = $(document.createElement("a")).addClass("expandButton");
	this.show = function(){
		$(this.action).empty().append("<span>&#9658;</span>");
		$(this.dom).show("slide", {direction: "right"}, (function(_this){ 
			return function(){  
				$(_this.action).removeClass("hidden").addClass("shown");
			};})(this));
	};
	this.hide = function(){
		$(this.dom).hide("slide", {direction: "right"}, (function(_this){ 
			return function(){
				$(_this.action).empty().append("<span>"+(_this.display || "") +"</span>");
				$(_this.dom).find(".expandButtonContainer").removeClass("shown").addClass("hidden").attr("title","show actions");
			};})(this));
	};
	this.init = function(){
		$(this.dom).wrapAll("<div class='expandButtonContainer'></div>");
		$(this.dom).addClass("expandButtonChild");
		this.container = $(this.dom).parent();
		$(this.container).wrapAll("<div class='expandContainer'></div>");
		$(this.container).before(this.action);
		$(this.action).addClass("shown").attr("title","hide actions").append("<span> &#9658; </span>").off("click").on("click", (function(_this){
			return function(){
				if($(_this.dom).is(":visible")){
					_this.hide();
				}else{
					_this.show();
				}	
			};
		})(this));
	};
	this.init();
};

appdb.utils.SimpleProperty = function(){
		this._val = null;
		return (function(self){
			return function(val){
				if( typeof val !== "undefined" ){
					self._val = val;
				}
				return self._val;
			};
		})(this);
	};

appdb.utils.getItemCanonicalUrl = function(type,data,onlyname){
	onlyname = (typeof onlyname === "boolean")?onlyname:false;
	var useCanonical = true;
	var useHash = "#";
	var permalink = "p=";
	if( appdb.config.routing.useCanonical !== true ){
		useCanonical = false;
	}
	if( appdb.config.routing.useHash === false){
		useHash = "";
		permalink = "?" + permalink;
	}
	
	type = type || "";
	type = type.toLowerCase();
	data = data || "";
	if(!data) return false;
	switch(type){
		case "software":
		case "app":
			if( !useCanonical ){
				return appdb.config.endpoint.base + useHash + permalink + appdb.utils.base64.encode("/apps/details?id="+data.id || data);
			}
			if( typeof data !== "string"){
				if( data.cname ){
					data = data.cname;
				}else{
					data = "";
				}
			}
			
			if( onlyname ) return data.toLowerCase();
			if( $.trim(data) === "" ){
				return "/store/register/software";
			}
			return "/store/software/" + data.toLowerCase();
		case "virtualappliance":
		case "vaversion":
		case "vapp":
			if( !useCanonical ){
				return appdb.config.endpoint.base + useHash + permalink + appdb.utils.base64.encode("/apps/details?id="+data.id || data);
			}
			if( typeof data !== "string"){
				if( data.cname ){
					data = data.cname;
				}else{
					data = "";
				}
			}
			if( onlyname ) return data.toLowerCase();
			if( $.trim(data) === "" ){
				return "/store/register/virtualappliance";
			}
			return "/store/vappliance/" + data.toLowerCase();
		case "swapp":
		case "swappliance":
		case "softwareappliance":
			if( !useCanonical ){
				return appdb.config.endpoint.base + useHash + permalink + appdb.utils.base64.encode("/apps/details?id="+data.id || data);
			}
			if( typeof data !== "string"){
				if( data.cname ){
					data = data.cname;
				}else{
					data = "";
				}
			}
			if( onlyname ) return data.toLowerCase();
			if( $.trim(data) === "" ){
				return "/store/register/softwareappliance";
			}
			return "/store/swappliance/" + data.toLowerCase();
		case "person":
		case "people":
			var tab = null;
			if( !useCanonical ){
				return appdb.config.endpoint.base + useHash + permalink + appdb.utils.base64.encode("/people/details?id=" +data.id);
			}
			if( typeof data !== "string"){
				if( data.tab ){
					tab = data.tab;
				}
				if( data.cname ){
					data = data.cname;
				}else if(data.id != '0'){
					data = data.firstName +"."+ data.lastName;
				}else{
					data = "";
				}
			}
			if( onlyname ) return data.toLowerCase();
			
			if( tab === null ){
				return "/store/person/" + data.toLowerCase();
			}else{
				return "/store/person/" + data.toLowerCase() + "/" + tab;
			}
			
			break;
		case "vo":
			if( typeof data !== "string"){
				data = data.name;
			}
			if( onlyname ) return data.toLowerCase();
			return "/store/vo/"+data.toLowerCase();
		case "site":
			if( typeof data !== "string"){
				data = data.name;
			}
			if( onlyname ) return data.toLowerCase();
			return "/store/site/"+data.toLowerCase();
		case "dataset":
			if( $.trim(data) === "" || $.trim(data.id) === "0"){
				return "/store/register/dataset";
			}
			if( typeof data !== "string"){
				data = data.guid || data.id;
			}
			if( onlyname ) return data.toLowerCase();
			return "/store/dataset/"+data.toLowerCase();
		case "mixed":
			if( !data || !data.type ) return false;
			if( data.url && data.url[0] === "/") data.url = data.url.slice(1);
			var url = (data.url || "").split("/");
			var ext = (url.length>2)?"/"+url[2]:"";

			switch(data.type){
				case "about":
				case "changelog":
					if(url.length > 1 ){
						return "/pages/about/" + $.trim(url[1]).toLowerCase() + ext;
					}
					return "/pages/about/" + url[0] + "" + ext;
				case "feedback":
					return "/pages/contact/feedback";
				case "statistics":
					var statentity = ($.trim(data.content)!=="")?$.trim(data.content):"software";
					var stattype = ( (url.length > 1 && url[1])?"/"+url[1]:"" );
					stattype = stattype.replace("&content=vappliance","");
					stattype = stattype.replace("&content=software","");
					stattype = stattype.replace("&content=people","");
					stattype = stattype.split("?ct=");
					statdisplay = (stattype.length>1)?"/" + stattype[1]:"";
					stattype = stattype[0];
					stattype = stattype.split("?")[0];
					switch(stattype){
						case "/perdomain":
							stattype = "/discipline";
							break;
						case "/persubdomain":
							stattype = "/subdiscipline";
							break;
						case "/percategory":
							stattype = "/category";
							break;
						case "/perdiscipline":
							stattype = "/discipline";
							break;
						case "/pervo":
							stattype = "/vo";
							break;
						case "/percountry":
							stattype = "/country";
							break;
						case "/perposition":
							stattype = "/position";
							break;
					}
					if( url[0] === "pplstats") statentity = "people";
					return "/pages/statistics/"+statentity+ stattype + $.trim(statdisplay).toLowerCase();
				default:
					return false;
			}
			break;
		case "activityreport":
			return "/pages/admin/activityreport";
			break;
		case "disseminationtool":
			return "/pages/admin/disseminationtool";
			break;
		default:
			return false;
	}
};

appdb.utils.LoadApplicationCategoriesInfo = function(){
	appdb.model.CategoryInfo.setLocalData([]);
};

appdb.utils.FilterAggregator = appdb.DefineClass("appdb.utils.FilterAggregator", function(o){
	this.options = {
		parent: o.parent || null,
		entityType: o.entityType,
		query: {},
		ext: {},
		filters:o.filters || [],
		pipeFilters: (typeof (o.pipeFilters) === "boolean")?o.pipeFilters:true
	};
	
	this.getEntityType = function(){
		return this.options.entityType || "";
	};
	this.getTemplates = function(){
		var ent = this.getEntityType();
		if( $.trim(ent) === "" ) return {};
		ent = ent.toLowerCase();
		var res = appdb.utils.FilterAggregator.templates[ent] || {};
		return res;
	};
	this.getDataProviders = function(provider){
		provider = provider || "";
		var ent = this.getEntityType();
		if( $.trim(ent) === "" ) return {};
		ent = ent.toLowerCase();
		var res = appdb.utils.FilterAggregator.dataProviders[ent] || {};
		if( provider ){
			if( $.type(res[provider]) === "function" ){
				return res[provider];
			}else{
				return null;
			}
		}
		return res;
	};
	this.getUserFilter = function(){
		var uf = this.getFilters({type: "user"});
		return ( uf.length > 0 )?uf[0]:null;
	};
	this.removeUserFilter = function(){
		var found = -1;
		$.each(this.options.filters, function(i, e){
			if( e.type === "user" ) found = i;
		});
		if( found > -1 ){
			this.options.filters.splice(found, 1);
		}
	};
	this.getSystemFilters = function(){
		return $.grep(this.options.filters, function(e){
			return (e.type !== "user" && e.type.source==="system");
		});
	};
	this.getFilters = function(opts){
		this.options.filters = this.options.filters || [];
		this.options.filters = $.isArray(this.options.filters)?this.options.filters:[this.options.filters];
		opts = opts || {};
		opts.type = opts.type || "";
		opts.value = opts.value || "";
		opts.source = opts.source || "";
		
		return $.grep(this.options.filters, (function(self,type, val,source) {
			return function(e){
				if( (type && e.type !== type) || 
					(val && e.value !== val)  ||
					(source && e.source !== source) ){
					return false;
				}
				return true;
			};
		})(self, opts.type,opts.value,opts.source));
	};
	this.removeFilters = function(opts){
		this.options.filters = this.options.filters || [];
		this.options.filters = $.isArray(this.options.filters)?this.options.filters:[this.options.filters];
		opts = opts || {};
		opts.type = opts.type || "";
		opts.value = opts.value || "";
		opts.source = opts.source || "";
		for(var i=this.options.filters.length-1; i>=0; i-=1){
				var e = this.options.filters[i];
				if( (opts.type && e.type !== opts.type) || 
					(opts.value && e.value !== opts.value)  ||
					(opts.source && e.source !== opts.source) ){
					//do nothing
				}else{
					this.options.filters.splice(i,1);
				}
		}
	};
	/*
	 *Append a user filter.
	 */
	this.appendUserFilter = function(f){
		
		if( $.type(f) === "string" ){
			if( $.trim(f) !== "" ){
				f = {
						type: "user",
						value: $.trim(f),
						source: "user"
				};
			}
		}
		this.removeFilters({type:"user",source:"user"});//ensure there is always only one user query
		if( $.trim(f.value) !== "" ){
			this.options.filters.push(f);
		}
	};
	/*
	 *Append a base filter
	 */
	this.appendBaseFilter = function(f){
		if( !f || $.type(f) === "function") return;
		if( $.type(f) === "string" && $.trim(f) !== ""){
			f = {
					type: "base",
					value: $.trim(f),
					source: "base"
			};
		}
		this.removeFilters({type:"base",source:"base"}); //ensure there is always only one base query
		this.options.filters.push(f);
	};
	/*
	 *Adds a filter entry. In case of string parameter assumes its a base filter entry
	 */
	this.appendSystemFilter = function(f){
		if( !f || $.isEmptyObject(f) ) return;
		
		if( $.isPlainObject(f) && $.trim(f.type) && $.trim(f.value) ){
			var fs = this.removeFilters(f);//ensure there are no duplicate filter entries
			this.options.filters.push(f);
		}
	};
	this.hasSystemFilter = function(){
		var res = $.grep(this.options.filters, function(e){
			return ( e.source === "system" );
		});
		return ( res.length > 0 );
	};
	this.hasUserFilter = function(){
		var res = $.grep(this.options.filters, function(e){
			return ( e.type === "user" );
		});
		return ( res.length > 0 );
	};
	this.loadBaseFilter = function(flt){
		this.appendBaseFilter(flt);
		//load imlicit filters 
		flt = flt || [];
		flt = $.isArray(flt)?flt:[flt];
		$.each(flt, (function(self){
			return function(index, filteritem){
				var parsed = self.parseQuery(filteritem,true);
				$.each(parsed, function(i, e){
					if( e.template ){
						e.filter.source = "implicit";
						self.appendSystemFilter(e.filter);
					}
				});
			};
		})(this));
	};
	this.loadUserFilter = function(flt){
		flt = flt || "";
		this.appendUserFilter(flt);
		var parsed = this.parseQuery(flt);
		$.each(parsed, (function(self){
			return function(i, e){
				e.filter.source = "user";
				self.appendSystemFilter(e.filter);
				//Remove reduntant text values from userquery flt value.
				var rx = new RegExp('' + e.query.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&") + '', "g");
				flt = $.trim(flt.replace(rx,""));
				if( $.trim(flt) === "" ){
					self.removeFilters({type:"user", source:"user"}); //remove user query value since all values are managed
				}else{
					self.appendUserFilter(flt); //update new user query value
				}
			};
		})(this));
	};
	this.loadSystemFilter = function(flt){
		flt = flt || [];
		flt = $.isArray(flt)?flt:[flt];
		$.each(flt, (function(self){
			return function(index, filteritem){
				var parsed = self.parseQuery(filteritem);
				$.each(parsed, function(i, e){
					e.filter.source = "system";
					self.appendSystemFilter(e.filter);
				});
			};
		})(this));
	};
	
	/*
	 *Loads,extracts and builds the filter entries of a request.
	 *Called externally from components with search functionality.
	 */
	this.loadFilter = function(p){
		if( $.type(p) === "string" || $.type(p) === "undefined" ){
			p = this.loadPermalinkFilter(p);
		}
		if( !p ){
			return;
		}
		this.options.filters = [];
		
		var ext = (p.ext)?$.extend(true,{},p.ext):{};
		
		if( ext.baseQuery && $.trim(ext.baseQuery.flt) ){
			this.loadBaseFilter(ext.baseQuery.flt);
		}
		
		//Parse user query for managed filters
		if( ext.userQuery && $.trim(ext.userQuery.flt) ){
			this.loadUserFilter(ext.userQuery.flt);
		}
		
		//Parse for system queries
		if( ext.systemQuery ) { 
			this.loadSystemFilter(ext.systemQuery);
		}
	};
	/*
	 *Returns an object representation of the given or current permalink
	 */
	this.loadPermalinkFilter = function(p){
		p = p || appdb.config.permalinkraw;
		var perm = appdb.utils.base64.decode(p);
		if( !perm ) {
			appdb.debug("Could not decode permalink");
			return null;
		}
		
		perm = JSON.parse(perm);
		return perm;
	};
	/*
	 *Check if a template correpsonds to the given query, thus can be manageable
	 */
	this.matchTemplateQuery = function(query, templ, loose){
		loose = (typeof(loose) === "boolean")?loose:false;
		query  = (query || "");
		if( loose ){
			query = query.replace(/\+\=/g,"+=&").replace(/\+\=\&\&/g,"+=&");
		}
		var reg = templ.replace(/\&/g, "\\&").replace(/\+/g, "\\+").replace(/\=/g,"\\=").replace(/\./g,"\\.").replace(/\"/g,'\\"').replace(/{#}/g, "[0-9]+").replace(/{\$}/g,"[\\S ]+");
		
		var regex = new RegExp(reg);
		if( regex.test(query,"gi") ){
			return regex.exec(query);
		}
		return [];
	};
	/*
	 *Extracts the value of a filter entry based on a filter template.
	 *USed from the parseQuery function to create a filter entry.
	 */
	this.extractValue = function(query,templ,loose){
		var quoted = false;
		loose = (typeof(loose) === "boolean")?loose:false;
		query = (query || "");
		if( loose ){
			query = query.replace(/\+\=/g,"+=&").replace(/\+\=\&\&/g,"+=&");
		}
		var reg = templ.replace(/{#}/,"").replace(/{\$}/,"");
		if( query[0] === '"' || query[0] === "'"){
			query = query.substr(1);
		}
		if( query[query.length-1] === '"' || query[query.length-1] === "'"){
			query = query.substr(0,query.length-1);
		}
		if( reg[0] === '"' || reg[0] === "'"){
			reg = reg.substr(1);
		}
		if( reg[reg.length-1] === '"' || reg[reg.length-1] === "'"){
			reg = reg.substr(0,reg.length-1);
		}
		var val = $.trim(query).replace(reg,"");
		
		return val;
	};
	/*
	 * Generate filter value to be passed to flt property of a rest API list request
	 */
	this.getManagedFilterValue = function(opts){
		if( !opts || $.isPlainObject(opts)===false || $.isEmptyObject(opts) === true || $.trim(opts.type)==="" || $.trim(opts.value) === "") return "";
		var temps = this.getTemplates() || {};
		if( temps[opts.type] ){
			var tv = temps[opts.type] || "";
			var v = opts.value;
			if( tv.indexOf("{$}") > -1 ){ //should replace with data value of item with id
				return $.trim(tv.replace(/\{\$\}/g,appdb.utils.GetDataValueByID(this.getEntityType(), opts.type, v)));
			}else{
				return $.trim(tv.replace(/\{\#\}/g,v));
			}
			
		}
		return "";
	};
	/*
	 * Parses a query string against filter templates.
	 * If loose it replaces operands such as '+=' with '+=&'.
	 * Returns a collection of manageable filter items.
	 */
	this.parseQuery = function(query, loose){
		var temps = this.getTemplates();
		var tq = [];
		var res = [];
		if( !temps ) return null;
		for(var i in temps){
			if( !temps.hasOwnProperty(i) ) continue;
			var t = temps[i];
			tq = this.matchTemplateQuery(query, temps[i], loose);
			$.each(tq, (function(self, tempname, tempval){
				return function(i, e){
					var v = self.extractValue(e, tempval, loose);
					if( v ){
						res.push({
							template: {
								name: tempname,
								value: tempval
							},
							query: e,
							filter: {
								type: tempname,
								value: v
							}
						});
					}
				};
			})(this,i,temps[i]));
		}
		return res;
	};
	/*
	 * Returns the flt value of the base query
	 */
	this.getBaseQueryValue = function(){
		var q = this.getFilters({type:"base", source:"base"});
		return $.trim( ( (q.length > 0)?q[0].value:"" ) );
	};
	/*
	 *Returns all managed filter values generated by the system.
	 */
	this.getSystemQueriesValues = function(){
		var res = [];
		var q = this.getFilters({source:"system"});
		q = q || [];
		q = $.isArray(q)?q:[q];
		
		$.each(q, (function(self){
			return function(i, e){
				res.push(self.getManagedFilterValue(e));
			};
		})(this));
		return res;
	};
	/*
	 * Returns the user defined flt value
	 */
	this.getUserQueryValue = function(){
		var res = [];
		var q = this.getFilters({source:"user"});
		q = q || [];
		q = $.isArray(q)?q:[q];
		
		$.each(q, (function(self){
			return function(i, e){
				if( e.type === "user" ){
					res.push(e.value);
				}else{
					res.push(self.getManagedFilterValue(e));
				}
				
			};
		})(this));
		
		return res.join(" ");
	};
	/*
	 * Concatenates the final filter to be send to the rest API
	 */
	this.getFullQuery = function(){
		var uq = this.getUserQueryValue();
		var bq = this.getBaseQueryValue();
		var sq = this.getSystemQueriesValues();
		
		var res = uq + " " + bq;
		var prefix = ( (this.options.pipeFilters)?" | ":" " );
		res = $.trim(res);
		$.each(sq, function(i, e){
			res += ((res)?prefix:"") + e;
		});
		
		return $.trim(res);
	};
	this.init = function(){
		
	};
	this.init();
},{
	templates :{
		"software": {
			"category": "+=&category.id:{#}",
			"discipline": "+=&discipline.id:{#}",
			"disciplineq": "+=discipline.id:{#}",
			"language": "+=&application.language:{#}",
			"os": "+=&os.id:{#}",
			"vo": "+=&vo.id:{#}",
			"country": "+=&country.id:{#}",
			"middleware": "+=&middleware.id:{#}",
			"status": "+=&application.status:{#}",
			"phonebook": "+=&phonebook:{$}",
			"voq": "&=vo.name:{$}",
			"mwq": '"&=middleware.name:{$}"',
			"countryq": "&=country.id:{#}",
			"categoryq": "+=category.id:{#}",
			"validated": "+=&application.validated:\"{$}\"",
			"validatedbool": "+=application.validated:{$}",
			"license": "+=&license.id:{#}",
			"osfamily": "+=&application.osfamily:{#}",
			"hypervisor": "+=&application.hypervisor:{#}",
			"arch": "+=&application.arch:{#}"
		},
		"vos": {
			"phonebook": "+=&phonebook:{$}",
			"middleware": "+=&middleware.id:{#}",
			"domain":  "+=&vo.domain:\"{$}\"",
			"discipline": "+=&discipline.id:{#}",
			"disciplineq": "+=discipline.id:{#}",
			"scope": "+=&vo.scope:\"{$}\"",
			"storetype": "+=&vo.storetype:{#}"
		},
		"people": {
			"country": "+=&country.id:{#}",
			"phonebook": "+=&phonebook:{$}",
			"discipline": "+=&discipline.id:{#}",
			"proglang": "+=&language.id:{#}",
			"group": "+=&accessgroup.id:{#}",
			"role": "+=&person.roleid:{#}",
			"language": "+=&person.language:{#}"
		},
		"sites": {
			"name": "+=&site.name:{$}",
			"id": "+=&site.id:{#}",
			"os": "+=&os.id:{#}",
			"vo": "+=&vo.id:{#}",
			"country": "+=&country.id:{#}",
			"middleware": "+=&middleware.id:{#}",
			"phonebook": "+=&phonebook:{$}",
			"hypervisor": "+=&application.hypervisor:{#}",
			"arch": "+=&application.arch:{#}",
			"category": "+=&category.id:{#}",
			"discipline": "+=&discipline.id:{#}",
			"supports": "+=&site.supports:{#}",
			"hasinstances": "+=&site.hasinstances:{#}",
			"roc": "+=&site.roc:{$}",
			"subgrid": "+=&site.subgrid:{$}",
			"osfamily": "+=&application.osfamily:{#}"
		}
	}
});

appdb.utils.GetDataValueByID = function(content, type ,id){
	var providers = {
		"software": {
			"category": function(id){
				var v = $.grep(appdb.model.StaticList.Categories, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"categoryq": function(id){
				var v = $.grep(appdb.model.StaticList.Categories, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"middleware": function(id){
				var v = $.grep(appdb.model.StaticList.Middlewares, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"discipline": function(id){
				var v = $.grep(appdb.model.StaticList.Disciplines, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"disciplineq": function(id){
				var v = $.grep(appdb.model.StaticList.Disciplines, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"vo": function(id){
				var v = $.grep(appdb.model.StaticList.VOs, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"country": function(id){
				var v = $.grep(appdb.model.StaticList.Countries, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"countryq": function(id){
				var v = $.grep(appdb.model.StaticList.Countries, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"status": function(id){
				var v = $.grep(appdb.model.StaticList.Statuses, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"phonebook": function(id){
				var alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
				alpha.push("0-9");
				alpha.push("#");
				return alpha[id-1];
			},
			"language": function(id){
				var v = $.grep(appdb.model.StaticList.ProgLangs , function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"arch": function(id){
				var pl = appdb.model.StaticList.SoftwareLogistics.arch || [];
				var res = $.grep(pl, function(e){
					return (e.id == id );
				});
				return (res.length>0)?res[0].text:id;
			},
			"os": function(id){
				var pl = appdb.model.StaticList.SoftwareLogistics.os || [];
				var res = $.grep(pl, function(e){
					return (e.id == id );
				});
				return (res.length>0)?res[0].text:id;
			},
			"validated": function(id){
				var v = $.trim(id).toLowerCase();
				if( v === "1") return "true";
				if( v === "2") return "false";
				if( v === "3") return "6 months";
				if( v === "4") return "1 year";
				if( v === "5") return "2 years";
				if( v === "6") return "3 year";
				return v;
			},
			"validatedbool": function(id){
				var v = $.trim(id).toLowerCase();
				if( v == "1" || v == "true" ) return "true";
				if( v == "2" || v == "false" ) return "false";
				return v;
			},
			"license": function(id){
				if( id == "0" ) return "User Defined";
				var v = $.grep(appdb.model.StaticList.Licenses , function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].title:"";
			},
			"osfamily": function(id){
				var pl = appdb.model.StaticList.OsFamilies || [];
				var res = $.grep(pl, function(e){
					return (e.id == id );
				});
				return (res.length>0)?res[0].val():id;
			},
			"hypervisor": function(id){
				var pl = appdb.model.StaticList.Hypervisors || [];
				var res = $.grep(pl, function(e){
					return (e.id == id );
				});
				return (res.length>0)?res[0].val():id;
			}
		},
		"people":{
			"country": function(id){
				var v = $.grep(appdb.model.StaticList.Countries, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"countryq": function(id){
				var v = $.grep(appdb.model.StaticList.Countries, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"discipline": function(id){
				var v = $.grep(appdb.model.StaticList.Disciplines, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"disciplineq": function(id){
				var v = $.grep(appdb.model.StaticList.Disciplines, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"language": function(id){
				var v = $.grep(appdb.model.StaticList.ProgLangs , function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"group": function(id){
				var v = $.grep(appdb.model.StaticList.AccessGroups , function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"role": function(id){
				var v = $.grep(appdb.model.StaticList.Roles , function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].type:"";
			},
			"phonebook": function(id){
				var alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
				alpha.push("0-9");
				alpha.push("#");
				return alpha[id-1];
			}
		},
		"vos": {
			"discipline": function(id){
				var v = $.grep(appdb.model.StaticList.Disciplines, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"disciplineq": function(id){
				var v = $.grep(appdb.model.StaticList.Disciplines, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"phonebook": function(id){
				var alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
				alpha.push("0-9");
				alpha.push("#");
				return alpha[id-1];
			},
			"scope": function(id){
				var vid = ($.trim(id) << 0);
				if( vid === 1 ){
					return "Global";
				}
				return "none";
			},
			"domain": function(id){
				return id;
			},
			"middleware": function(id){
				var v = $.grep(appdb.model.StaticList.Middlewares, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			}
		},
		"sites": {
			"phonebook": function(id){
				var alpha = "ABCDEFGHIJKLMNOPQRSTUVWXYZ".split("");
				alpha.push("0-9");
				alpha.push("#");
				return alpha[id-1];
			},
			"middleware": function(id){
				var v = $.grep(appdb.model.StaticList.Middlewares, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"discipline": function(id){
				var v = $.grep(appdb.model.StaticList.Disciplines, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"category": function(id){
				var v = $.grep(appdb.model.StaticList.Categories, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"country": function(id){
				var v = $.grep(appdb.model.StaticList.Countries, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"vo": function(id){
				var v = $.grep(appdb.model.StaticList.VOs, function(e){
					return (e.id == id);
				});
				return ( v.length>0 )?v[0].val():"";
			},
			"arch": function(id){
				var pl = appdb.model.StaticList.SoftwareLogistics.arch || [];
				var res = $.grep(pl, function(e){
					return (e.id == id );
				});
				return (res.length>0)?res[0].text:id;
			},
			"os": function(id){
				var pl = appdb.model.StaticList.SoftwareLogistics.os || [];
				var res = $.grep(pl, function(e){
					return (e.id == id );
				});
				return (res.length>0)?res[0].text:id;
			},
			"hypervisor": function(id){
				var pl = appdb.model.StaticList.Hypervisors || [];
				var res = $.grep(pl, function(e){
					return (e.id == id );
				});
				return (res.length>0)?res[0].val():id;
			},
			"osfamily": function(id){
				var pl = appdb.model.StaticList.OsFamilies || [];
				var res = $.grep(pl, function(e){
					return (e.id == id );
				});
				return (res.length>0)?res[0].val():id;
			}
		}
	};
	content = content || "";
	type = type || "";
	id = id || "";
	if( providers[content] && $.type(providers[content][type]) === "function" ){
		return ( providers[content][type](id) || id );
	}
	return id;
};

appdb.utils.MergeTreeLogistics = function(dataview, data, completezeros){
	completezeros = completezeros || false;
	var ids = [];
	$.each(data, function(i,e){
		ids.push(e.id);
	});
	var td = null;
	if( completezeros === true ){
		td = dataview.transformData();
	}else{
		var sdv = dataview.getSubDataViewByIds(ids);
		td = sdv.transformData();
	}
	var _getCount = function(id){
		var res = $.grep(data, function(e){
			return (e.id == id);
		});
		return (res.length>0)?(res[0].count || 0):0;
	};
	var _setCount = function(entries,parent){
		$.each(entries, function(i,e){
			e.count = _getCount(e.id);
			if( e.children && e.children.length > 0 ){
				_setCount(e.children,e);
				if( completezeros === true ){
					var extracount = 0 ;
					$.each(e.children, function(ii,ee){
						extracount += (ee.count << 0);
					});
					e.extracount = extracount;
					if( extracount < e.count ){
						e.children.push({
							id: -1,
							text: "__pseudo__",
							count: e.count - extracount
						});
					}
				}
			}
		});
	};
	var _getLogistics = function(entries){
		var res = [];
		$.each(entries, function(i,e){
			var entry = {};
			if( e.children && e.children.length > 0 ){
				entry.children = _getLogistics(e.children);
			}
			entry.id = e.id;
			entry.count = e.count;
			entry.text = e.value;
			if( completezeros && (entry.count << 0 ) === 0){
				
			}else{
				res.push(entry);
			}
			
		});
		return res;
	};
	_setCount(td);
	
	return _getLogistics(td);
};

appdb.utils.Vm2Appdb = (function(){
	var init = function init(){
		$(document).ready(function(){
			$("form#vmc2appdb #submitxml").off("click").on("click",function(ev){
				ev.preventDefault();
				appdb.utils.Vm2Appdb.submit();
				return false;
			});
			$("form#vmc2appdb #data").off("mouseup").on("mouseup", function(){
				appdb.utils.Vm2Appdb.checkSubmit();
			}).off("keyup").on("keyup", function(){
				appdb.utils.Vm2Appdb.checkSubmit();
			});
			
		});
	};
	var getAppdbId = function(){
		var val = $("form#vmc2appdb #data").val();
		if( $.trim(val) === "" ){
			return "";
		}
		return $(val).find("ad_appid:first").text();
	};
	var checkSubmit = function(){
		var val = $("form#vmc2appdb #data").val();
		if( $.trim(val) === "" ){
			enableSubmit(false);
		}
		var appdbid = getAppdbId();
		if( !appdbid ){
			$("form#vmc2appdb #appdbid").val("");
			enableSubmit(false);
		}else{
			$("form#vmc2appdb #appdbid").val(appdbid);
			enableSubmit(true);
		}
		
	};
	var enableSubmit = function(enable){
		enable = (typeof enable === "boolean")?enable:true;
		if( enable ){
			$("form#vmc2appdb #submitxml").prop("disabled", false);
		}else{
			$("form#vmc2appdb #submitxml").prop("disabled", true);
		}
	};
	var submit = function submit(){
		var appdbid = $("form#vmc2appdb #appdbid").val();
		
		$("form#vmc2appdb .reply").empty().append("<div>making call for appid: " + appdbid + "</div>");
		
		var model = new appdb.model.VirtualAppliance();
		model.subscribe({event:"update", callback: function(d){
				$("form#vmc2appdb .reply").append("<div>Call ended fro appid: " + appdbid + "</div>");
				if(d && d.error){
					$("form#vmc2appdb .reply").append("<div>[ERROR]: " + d.errordesc+ "</div>");
				}else{
					$("form#vmc2appdb .reply").append("<div>SUCCESS</div>");
				}
				setTimeout(function(){
					model.unsubscribeAll();
					model.destroy();
					model = null;
				},1);
		}});
		var modelOpts =  {id: appdbid};
		model.update({query: modelOpts, data: {data: encodeURIComponent($("form#vmc2appdb #data").val())}});
	};
	return {
		init: init,
		submit: submit ,
		checkSubmit: checkSubmit
	};
})();

appdb.utils.checkUserNotification = (function(){
	var _timer = null;
	var _interval = 30000; // twice a minute
	var _enabled = false;
	var _loggedin = function(){};
	var _loggedout = function(){};
	var _xhr = null;
	var _lastMsg = "";
	var _init = function(){
		if( _timer !== null ){clearTimeout(_timer);_timer = null;}
		_timer = setTimeout(function(){
			if( _xhr !== null ) _xhr.abort();
			if( _enabled === false ) {clearTimeout(_timer);_timer = null;return;}
			_xhr = $.ajax({
				url: appdb.config.endpoint.base + "news/notifyusers",
				success: function(d, textStatus, jqXHR){
					_xhr = null;
					if (jqXHR.status == 204) {
						if ( $("#servicemsg").length > 0 ) {
							$("#servicemsg").next().css({'margin': '0 auto'});
							$("#servicemsg")[0].remove();
						};
					} else {
						d = $.trim(d);
						if (d != "") {
							if (_lastMsg != d) {
								_lastMsg = d;
	//							alert(d);
								if ( $("#servicemsg").length == 0 ) {
									$("body > .mainheader").after('<div id="servicemsg">');
									$("#servicemsg").next().css({'margin-top': '40px'});
								}
								$("#servicemsg").css({'position': 'fixed', 'top': '40px', 'height': '40px', 'left': '0px', 'width': '100%', 'line-height': '40px', 'text-align': 'center', 'background-color': '#A00000', 'color': 'white', 'font-size': '16pt', 'font-family': '"Arial", "Sans-serif" !important', 'z-index': 9999});
								$("#servicemsg").html(d);
							};
						};
					};
					_init();
				},
				nocontent: function(){
					_xhr = null;
					if ( $("#servicemsg").length > 0 ) {
						$("#servicemsg").next().css({'margin': '0 auto'});
						$("#servicemsg")[0].remove();
					};
					_init();
				},
				error: function(){
					_xhr = null;
					_init();
				}
			});
		}, _interval);
	};

	var enable = function(v){
		var _start = false;
		if( typeof v === "boolean" ){
			if( v !== _enabled && v === true ){
				_start = true;
			}
			_enabled = v;
		}
		
		if( _start ){
			_init();
		}
		return _enabled;
	};
	var interval = function(v){
		if( typeof v === "number" && v > 1000 ){
			_interval = v;
		}
		return _interval;
	};
	var _cancelCurrentRequest = function(){
		if( _xhr && _xhr !== null && typeof _xhr.abort === "function"){
			_xhr.abort();
		}
	};
	return {
		interval: interval,
		enable: enable,
		cancelCurrentRequest: _cancelCurrentRequest
	};
})();

appdb.utils.checkSession = (function(){
	var _timer = null;
	var _interval = 8000;
	var _enabled = false;
	var _loggedin = function(){};
	var _loggedout = function(){};
	var _xhr = null;
	var _init = function(){
		if( _timer !== null ){clearTimeout(_timer);_timer = null;}
		_timer = setTimeout(function(){
			if( _xhr !== null ) _xhr.abort();
			if( _enabled === false ) {clearTimeout(_timer);_timer = null;return;}
			_xhr = $.ajax({
				url: appdb.config.endpoint.base + "saml/isloggedin",
				success: function(d){
					_xhr = null;
					if( $.trim(d) === "1" ){
						_loggedin();
					}else{
						_loggedout();
					}
					_init();
				},
				error: function(){
					_xhr = null;
					_init();
				}
			});
		}, _interval);
	};

	var enable = function(v){
		var _start = false;
		if( typeof v === "boolean" ){
			if( v !== _enabled && v === true ){
				_start = true;
			}
			_enabled = v;
		}
		
		if( _start ){
			_init();
		}
		return _enabled;
	};
	var interval = function(v){
		if( typeof v === "number" && v > 1000 ){
			_interval = v;
		}
		return _interval;
	};
	var onLoggedIn = function(v){
		if( typeof v === "function" ){
			_loggedin = v;
		}else if( typeof v === "undefined" ){
			_loggedin();
		}
	};
	var onLoggedOut = function(v){
		if( typeof v === "function" ){
			_loggedout = v;
		}else if( typeof v === "undefined" ){
			_loggedout();
		}
	};
	var _cancelCurrentRequest = function(){
		if( _xhr && _xhr !== null && typeof _xhr.abort === "function"){
			_xhr.abort();
		}
	};
	return {
		interval: interval,
		onLoggedIn: onLoggedIn,
		onLoggedOut: onLoggedOut,
		enable: enable,
		cancelCurrentRequest: _cancelCurrentRequest
	};
})();

appdb.utils.LoggedOutDialog = (function(){
	var _interval = 10000;
	var _timer = null;
	var _remaining = 10;
	var renderCountdown = function(){
		$("#signedoutnotify .remainingtime").text(_remaining);
	};
	var refresh = function(){
		window.location.href = appdb.config.endpoint.base;
	};
	var startCountDown = function(){
		if( _timer !== null ){clearTimeout(_timer);_timer = null;}
		_timer = setTimeout(function(){
			_remaining -= 1;
			renderCountdown();
			if( _remaining > 1 ){
				startCountDown();
			}else{
				refresh();
			}
		},1000);
	};
	var countdownEnded = function(){
		refr();
	};
	var render = function(){
		$("#signedoutnotify a.refresh").attr("href", appdb.config.endpoint.base).off("click").on("click", (function(refr){
			return function(ev){
				ev.preventDefault();
				refr();
				return false;
			};
		})(refresh));
		_remaining = Math.ceil( _interval / 1000 );
		renderCountdown();
		startCountDown();
		$("body").addClass("signedout");
	};
	return {
		display: render
	};
})();

appdb.utils.ShowNotificationDialog = function(o){
	o = o || {};
	o.title = o.title || "Notification";
	o.message = o.message || "";
	o.callback = o.callback || function(){};
	o.close = o.close || "close";
	var html = $('<div class="notifydialog display"><div class="shade"></div><div class="dialog"><div class="title">'+o.title+'</div><div class="message">'+o.message+'</div><div class="actions"><a href="" title="Click to close" class="action close" data-name="close">'+o.close+'</a></div></div></div>');
	if( o.action ){
		var cls = $.trim(o.action).toLowerCase().replace(/\s/g,"");
		$(html).find(".actions").prepend('<a href="" title="'+(o.actionTitle || "click to proceed")+'" class="action reload ' + cls + '" >'+o.action+'</a>');
	}
	$(html).find(".actions > a").off("click").on("click", function(ev){
		ev.preventDefault();
		if(typeof o.callback === "function" ){
			o.callback($(this).text());
		};
		setTimeout(function(){
			$("body").children(".notifydialog").remove();
		},1);
		return false;
	});
	$("body").children(".notifydialog").remove();
	$("body").append(html);
		
};

appdb.utils.ShowNotificationWarning = function(o){
	o = o || {};
	o.title = o.title || "Notification";
	o.message = o.message || "";
	o.callback = o.callback || function(){};
	o.close = o.close || "close";
	var html = $('<div class="notifywarning display"><div class="content"><div class="title icontext"><img src="/images/vappliance/warning.png" alt="" /><span>'+o.title+'</span></div><div class="message">'+o.message+'</div><div class="actions"><a href="" title="Click to close" class="action close" data-name="close">'+o.close+'</a></div></div></div>');
	if( o.action ){
		$(html).find(".actions").prepend('<a href="" title="'+(o.actionTitle || "click to proceed")+'" class="action reload" >'+o.action+'</a>');
	}
	$(html).find(".actions > a").off("click").on("click", function(ev){
		ev.preventDefault();
		if(typeof o.callback === "function" ){
			o.callback($(this).text());
		};
		setTimeout(function(){
			$("body").children(".notifywarning").remove();
		},1);
		return false;
	});
	$("body").children(".notifywarning").remove();
	$("body").append(html);
		
};


appdb.utils.localStorage = (function(){
	var _hasStorage = (function supports_html5_storage() {
	  try {
		return 'localStorage' in window && window['localStorage'] !== null;
	  } catch (e) {
		return false;
	  }
	})();
	
	var _dummyStorage = function(){return null;};
	var _localStorage = {
		getFullItem: _dummyStorage,
		getItem: _dummyStorage,
		setItem: _dummyStorage,
		clear: _dummyStorage,
		key: _dummyStorage,
		length: 0,
		removeItem: _dummyStorage		
	};
	if( _hasStorage === true ){
		return window.localStorage;
	}
	return _localStorage;
})();

appdb.utils.localCache = (function(){
	var _localStorage = appdb.utils.localStorage;
	var _prefix = "cache::";
	
	function localCache_getFullItem(name){
		if( !name ) return null;
		var res = _localStorage.getItem(_prefix+name);
		if( res == null ) return res;
		if( res.length > 1 && res[0] === "{" ){
			res = JSON.parse(res);
			if( res.data && res.meta ){
				return res;
			}
			return {data: res, meta: {}};
		}
		return {data: res , meta: {}};
	}
	
	function localCache_getItem(name){
		var res = localCache_getFullItem(name);
		if( res === null ) return res;
		return res.data;
	}
	
	function localCache_getMetaData(name){
		var res = localCache_getFullItem(name);
		if( res === null ) return res;
		return res.meta;
	}
	function localCache_setItem(name, data, meta){
		if( !name ) return;
		data = data || {};
		meta = meta || {};
		meta.addedon = (( new Date() ).toUTCString() );
		try{
			_localStorage.removeItem(_prefix+name);
			_localStorage.setItem(_prefix+name, JSON.stringify({meta: meta, data: data}));
		}catch(e){
			appdb.debug("[localStorage]:" + e.message);
		}
		_localCache.length = localCache_getLength();
	}
	
	function localCache_removeItem(name){
		if( !name ) return;
		if( name.substr(0, _prefix.length ) !== _prefix ){
			name = _prefix + name;
		}
		_localStorage.removeItem(name);
		_localCache.length = localCache_getLength();
	}
	
	function localCache_clear(nameprefix){
		nameprefix = nameprefix || "";
		var prefix = _prefix + nameprefix;
		var i, len = _localStorage.length;
		if( len > 0 ){
			var keys = [];
			for(i = 0; i < len; i+=1){
				var nm = _localStorage.key(i);
				if( nm == prefix || nm.substr(0, prefix.length) === prefix ){
					keys.push(nm);
				}
			}
			for(i = 0; i < keys.length; i+=1){
				_localStorage.removeItem(keys[i]);
			}
		}
	}
	
	function localCache_isExpired(name){
		var days = appdb.config.cache.expires || "1";
		days = parseInt(days);
		if( !name ) return false;
		var res = localCache_getFullItem(name);
		if( res == null ) return true;
		var addedon = (res.meta || {meta: {}}).addedon || undefined;
		if( !addedon ) return false;
		var then = new Date(addedon);
		var today = new Date();
		
		var diffMs = (today-then); // milliseconds between now & Christmas
		var diffDays = Math.round(diffMs / 86400000); // days
		if( diffDays > days ){ 
			return true;
		}
		return false;
	}
	function localCache_getAllKeys(){
		var i, len = _localStorage.length;
		var keys = [];
		if( len > 0 ){
			for(i = 0; i < len; i+=1){
				var nm = _localStorage.key(i);
				if( nm == _prefix || nm.substr(0, _prefix.length) === _prefix ){
					keys.push(nm);
				}
			}
		}
		return keys;
	}
	function localCache_getLength(){
		return localCache_getAllKeys().length;
	}
	
	var _localCache = {
		getItem: localCache_getItem,
		getFullItem: localCache_getFullItem,
		setItem: localCache_setItem,
		getMetaData: localCache_getMetaData,
		removeItem: localCache_removeItem,
		clear: localCache_clear,
		isExpired: localCache_isExpired
	};
	return _localCache;
	
})();
appdb.utils.localDataCache = (function(){
	var _localCache = appdb.utils.localCache;
	var _prefix = "data::";
	function localDataCache_getItem(name){
		if( !name ) return null;
		name = _prefix + name;
		return _localCache.getItem(name);
	}
	function localDataCache_setItem(name, data, meta){
		if( !name ) return;
		name = _prefix + name;
		_localCache.setItem(name, data, meta);
	}
	function localDataCache_getFullItem(name){
		if( !name ) return null;
		name = _prefix + name;
		return _localCache.getFullItem(name);
	}
	function localDataCache_removeItem(name){
		if( !name ) return;
		name = _prefix + name ;
		_localCache.removeItem(name);
	}
	function localDataCache_clear(){
		_localCache.clear(_prefix);
	}
	function localDataCache_isExpired(name){
		if( !name ) return false;
		name = _prefix + name ;
		return _localCache.isExpired(name);
	}
	var _localDataCache = {
		getItem: localDataCache_getItem,
		getFullItem: localDataCache_getFullItem,
		setItem: localDataCache_setItem,
		removeItem: localDataCache_removeItem,
		clear: localDataCache_clear,
		isExpired: localDataCache_isExpired
	};
	return _localDataCache;
})();

appdb.utils.localSingleDataCacheFactory = (function(){
	return function(_prefix){
		_prefix = _prefix || "resources";
		var _localDataCache = appdb.utils.localDataCache;
		var _cache = _localDataCache.getItem(_prefix);
		var _data = null;
		var _meta = null;

		var _reload = function(ifnull){
			ifnull = (typeof ifnull === "boolean")?ifnull:false;
			if( Object.prototype.toString.call(_data) !== "[object Object]") _data = {};	
			if( ifnull === true && _cache!==null && _data!==null ) return;

			_data = _localDataCache.getItem(_prefix);
		};
		function _getItemByName(name){
			_reload(true);
			if( !name || _data === null ) return null;
			for(var i in _data ){
				if(_data.hasOwnProperty(i) === false ) continue;
				if( i === name ) return _data[i];
			}
			return null;
		}
		function transformData(data){
			if(!data) return data;
			if( $.isArray(data) ){
				var res = [];
				$.each(data, function(i,e){
					var item = $.extend({},e);
					if( typeof item.val === "function" ){
						item.val = item.val();
					}
					res.push(item);
				});
				return res;
			}
			return data;
		}
		function _setItemByName(name, data, meta){
			_reload(true);
			if( !name || !data ) return;
			data = appdb.utils.transformValData(data, false);
			if( !_data || _data == null || Object.prototype.toString.call(_data)!== "[object Object]" ) _data={};
			if( !_data[name] ){
				_data[name] = {};
			}
			_data[name].data = _data[name].data || {};
			_data[name].meta = _data[name].meta || {on: (( new Date() ).toUTCString() )};
			_data[name].data = data;
			if( data ) _data[name].data = data;
			if( meta ) meta.on = (( new Date() ).toUTCString() );
			if( meta ) _data[name].meta = meta;
		}
		function _removeItemByName(name){
			_reload(true);
			if( !name ) return;
			if( _data[name] ) delete _data[name];
		}
		function localResourceCache_getFullItem(name){
			_reload(true);
			if( !name ) return _localDataCache.getFullItem(_prefix);
			var item = _getItemByName(name);
			if( item === null ) return null;
			return item;
		}
		function localResourceCache_getItem(name){
			_reload(true);
			var item = null;
			if( !name ) {
				item = _localDataCache.getFullItem(_prefix);
			}else{
				item = _getItemByName(name);
			}
			if( item && item.meta && item.meta.on ){
				if( _isExpired(name,item.meta.on) === true ){
					localResourceCache_removeItem(name);
					return null;
				}
			}
			if( item && item.data ) {
				if( $.isArray(item.data) ){
					item.data = appdb.utils.transformValData(item.data,true);
				}
				return item.data;
			}
			return item;
		}
		function localResourceCache_setItem(name, data, meta){
			_reload(true);
			_setItemByName(name,data,meta);
			_localDataCache.setItem(_prefix, _data);
			_reload();
		}
		function localResourceCache_removeItem(name){
			if( !name ) return _localDataCache.removeItem(_prefix);
			_reload(true);
			_removeItemByName(name);
			_localDataCache.setItem(_prefix, _data);
			_reload();
			return null;
		}
		function localResourceCache_clear(name){
			if(!name) {
				_localDataCache.clear(_prefix);
			}else{
				_localDataCache.setItem(_prefix, {meta: _meta, data: _data});
			}
			_reload();
		}
		function _isExpired(name,on){
			var conf = appdb.config.cache[_prefix] || {items:[]} ;
			var expires = conf.expires || appdb.config.cache.expires || null;
			if( !expires ) return false;
			var itemexp = null;
			for(var i=0;  i< conf.items.length; i+=1){
				var cur = conf.items[i];
				if( typeof cur === "string" ){
					cur = {name: cur, expires: expires};
				}
				if( cur.name === name ){
					itemexp = cur.expires;
					break;
				}
			}

			if( itemexp !== null ){
				var then = new Date(on);
				var today = new Date();

				var diffMs = (today-then); // milliseconds between now & Christmas
				var diffDays = Math.round(diffMs / 86400000); // days
				if( diffDays > itemexp ){ 
					return true;
				}
			}
			return false;
		}
		function localResourceCache_isExpired(name){
			if( !name ) return _localDataCache.isExpired(_prefix);
			_reload(true);

			var item = _getItemByName(name);
			if( item === null ) return true;
			var itemexp = (!item || !item.meta || !item.meta.on)?null:item.meta.on;
			if( itemexp === null) return false;
			return _isExpired(name,itemexp);
		}
		var _localResourceCache = {
			getItem: localResourceCache_getItem,
			setItem: localResourceCache_setItem,
			removeItem: localResourceCache_removeItem,
			clear: localResourceCache_clear,
			isExpired: localResourceCache_isExpired
		};
		_reload();
		return _localResourceCache;
	};
})();
appdb.utils.localResourceCache = appdb.utils.localSingleDataCacheFactory("resources");
appdb.utils.localHomeCache = appdb.utils.localSingleDataCacheFactory("home");

appdb.utils.transformValData = function(data, tofunction){
	if(!data) return data;
	tofunction = (typeof tofunction === "boolean")?tofunction:false;
	if( $.isArray(data) ){
		var res = [];
		if( tofunction === false ){
			$.each(data, function(i,e){
				var item = $.extend({},e);
				if( typeof item.val === "function" ){
					item.val = item.val();
				}
				res.push(item);
			});
		}else{
			$.each(data, function(i,e){
				var item = $.extend({},e);
				if( typeof item.val !== "function" ){
					item.val = (function(v){return function(){return v;};}(item.val));
				}
				res.push(item);
			});
		}
		return res;
	}
	return data;
};

appdb.utils.setupWikiLinks = function(el){
	el = el || "body";
	var domain = $.trim( appdb.config.endpoint.wiki ) || "/";
	$(el).find(".wiki-link").each(function(i, e){
		var link = $.trim( $(e).data("wiki-link") );
		if( link  !== "" ){
			$(e).attr("href",domain + link).attr("target","_blank");
		}
	});
};

appdb.utils.RequestPool = function(poolname){
	this.poolname = $.trim(poolname);
	this.currentRequests = [];
	this.clearRequests = function(){
		var reqs = this.currentRequests || [];
		var newreqs = [];
		$.each(reqs, function(i,e){
			if( !e ) return;
			newreqs.push(e);
		});
		this.currentRequests = newreqs;
		return this.currentRequests;
	};
	this.registerRequest = function(model, name){
		if( !model || typeof model.getXhr !== "function") return;
		var reqs = this.clearRequests();
		var found = false;
		$.each(reqs, function(i, e){
			if( found === false && e === model ) {
				found = true;
			};
		});
		if( found === true ) return;
		if( $.trim(name) !== "" ){
			model.requestName = $.trim(name).toLowerCase();
		}
		reqs.push(model);
		appdb.debug("[DEBUG] registering " + this.getName() + "::" + model.requestName);
		this.currentRequests = reqs;
	};
	this.cancelRequests = function(name){
		name = $.trim(name).toLowerCase();
		var reqs = this.clearRequests() || [];
		var pname = this.getName();
		$.each(reqs, function(i, e){
			if( e && typeof e.getXhr === "function" ){
				var xx = e.getXhr();
				if( xx && typeof xx.abort === "function"){
					if( name !== "" && e.requestName !== name) return;
					appdb.debug("[DEBUG] Aborting..." + pname + "::" + e.requestName || e.getXhr().readyState);
					e.getXhr().isAborted = true;
					e.getXhr().abort();
				}
			}
		});
	};
	this.getName = function(){
		return this.poolname;
	};
	this.reset = function(){
		this.cancelRequests();
		this.clearRequests();
		this.currentRequests = [];
	};
	this.reset();
	
	return (function(self){
		return {
			cancel: function(name){ self.cancelRequests(name); },
			register: function(model,name){ self.registerRequest(model, name); },
			clear: function(){ return self.clearRequests(); },
			getName: function(){ return self.getName(); },
			reset: function(){ self.reset(); }
		};
	})(this);
	
};
appdb.utils.extendArray = function(arr){
	arr = arr || [];
	arr = $.isArray(arr)?arr:[arr];
	var res = [];
	$.each( arr, function(i, e){
		res.push( $.extend(true, {}, e) );
	});
	return res;
};
appdb.utils.GroupSiteImages = function(occiservices, flattenVOs){
	occiservices = occiservices || [];
	occiservices = $.isArray(occiservices)?occiservices:[occiservices];

	function getNoneVO() {
		return {id: '<none>', name: '<none>'};
	}

	function collectImages(servs){
		var res = [];
		$.each(servs, function(i, e){
			e.image = e.image || [];
			e.image = $.isArray(e.image)?e.image:[e.image];
			if( e.image.length > 0 ){
				$.each( e.image , function( ii, ee ){
					var ext = $.extend(true, {}, ee );
					ext.template = e.template;
					ext.occi_endpoint_url = e.occi_endpoint_url;
					ext.occi.mpuri = e.mpuri;
					ext.occi.vo = ext.occi.vo || getNoneVO();
					ext.instances = []; //will be used to group image instances
					res.push(ext);
				});
			}
		});
		return res;
	}
	function groupImages(images){
		var uniq = {};
		$.each(images, function(i,e){
			if( !uniq[e.id] ) {
				uniq[e.id] = $.extend(true, {}, e);
			}else{
				
			}
		});
		
		var res = [];
		for(var i in uniq){
			if( uniq.hasOwnProperty(i) === false ) continue;
			res.push(uniq[i]);
		}
		
		return res;
	};
	function extendArray(arr){
		arr = arr || [];
		arr = $.isArray(arr)?arr:[arr];
		var res = [];
		$.each( arr, function(i, e){
			res.push( $.extend(true, {}, e) );
		});
		return res;
	}
	
	function group(servs){
		var images = collectImages(servs);
		var goodids = groupByGoodId(images);
		var res = [];
		
		for(var i in goodids){
			if( goodids.hasOwnProperty(i) === false ) continue;
			res.push(goodids[i]);
		}

		if (flattenVOs === true) {
		    res = flattenPerVO(res);
		}

		return res;
	}

	function flattenPerVO(groups) {
		var instancevos = {};
		var res = [];

		$.each(groups, function(gi, g) {
			$.each(g.instances, function(ii, inst) {
				var uid = g.id  + '_' + inst.vo.id;
				if (!instancevos[uid]) {
					instancevos[uid] = Object.assign({}, g);
					instancevos[uid].instances = [Object.assign({}, inst)];
				} else {
					instancevos[uid].instances.push(inst);
				}
			});
		});

		for(var i in instancevos) {
			if (instancevos.hasOwnProperty(i) === false) continue;
			res.push(instancevos[i]);
		}

		return res;
	}
	
	function appendOccisToInstancesPerVO(instances, occis) {
		var newInstances = [];

		$.each(occis, function(occiIndex, occi) {
			var foundInstances = $(instances).filter(function(instanceIndex, instance) {
				if (!occi.vo || !occi.vo.id) {
					return (!instance.vo || !instance.vo.id);
				}
				return occi.vo.id === instance.vo.id;
			});

			if (foundInstances.length > 0) {
				$.each(foundInstances, function(foundInstIndex, foundInstance) {
					foundInstance.items = foundInstance.items.concat(extendArray(occi.items));
				});
			} else {
				newInstances = newInstances.concat(extendArray(occi));
			}
		});

		return instances.concat(newInstances);
	}

	function groupByGoodId(images){
		var res = {};
		
		$.each(images, function(i, e){
			if( !e.goodid ) return;
			var uuid = e.goodid;
			if(e.occi && !e.occi.vo) {
				e.occi.vo = getNoneVO();
			}
			if (flattenVOs === true && e.occi && e.occi.vo) {
			    uuid = uuid + '_' + e.occi.vo.id;
			}
			e.template = e.template || [];
			e.template = $.isArray(e.template)?e.template:[e.template];
			e.occi = e.occi || [];
			e.occi = $.isArray(e.occi)?e.occi:[e.occi];
			
			$.each(e.occi, function(ii,ee){
				ee.template = extendArray(e.template);
				ee.mpuri = ""+e.mpuri;
				ee.occi_endpoint_url = (e.occi_endpoint_url && e.occi_endpoint_url.val) ? e.occi_endpoint_url.val() : '' + e.occi_endpoint_url;
				$.each(ee.template, function(ti,t){
					t.occi_endpoint_url = ee.occi_endpoint_url;
				});
			});
			if( appdb.config.features.groupvaprovidertemplates ){
				e.occi = groupOccis(e.occi);
			}
			if( !res[uuid] ){
				e.instances = e.instances.concat( extendArray(e.occi) );
				delete e.occi;
				res[uuid] = e;
			} else {
				if( appdb.config.features.groupvaprovidertemplates ){
					res[uuid].instances = appendOccisToInstancesPerVO(res[uuid].instances, e.occi);
					//res[e.goodid].instances[0].items = res[e.goodid].instances[0].items.concat( extendArray(e.occi[0].items) );
					$.each(e.template, (function(eurl){ return function(i,t) {
						t.occi_endpoint_url = t.occi_endpoint_url || [];
						t.occi_endpoint_url = $.isArray(t.occi_endpoint_url)?t.occi_endpoint_url:[t.occi_endpoint_url];
						if( $.inArray(eurl, t.occi_endpoint_url) === -1 ){
							t.occi_endpoint_url.push(eurl);
						}
						t.occi_endpoint_url = eurl;
						e.template[i] = t;
					};})(e.occi_endpoint_url));
					$.each(res[uuid].instances, function(instanceIndex, instance) {
						res[uuid].instances[instanceIndex].template = instance.template.concat(extendArray(e.template));
					});
					//res[e.goodid].instances[0].template = res[e.goodid].instances[0].template.concat(extendArray(e.template));
				}else {
					res[uuid].instances = res[uuid].instances.concat( extendArray(e.occi) );
				}
			}
		});
		
		//group duplicates
		for(var i in res ){
			if( res.hasOwnProperty(i) === false ) continue;
			var uniq = {};
			$.each(res[i].instances, function(ii, e){
				e.vo = e.vo || getNoneVO();
				uniq[e.providerimageid] = e;
			});
			res[i].instances = [];
			for( var u in uniq ){
				if( uniq.hasOwnProperty(u) === false ) continue;
				uniq[u].template = appdb.utils.GroupSiteTemplates(uniq[u].template);
				res[i].instances.push( uniq[u] );
			}
		}
		return res;
	}
	
	function groupOccis(occis){
		occis = occis || [];
		occis = $.isArray(occis)?occis:[occis];
		
		var uniq = {};
		var res = [];
		$.each(occis, function(i,e){
			e.template = e.template || [];
			e.template = $.isArray(e.template)?e.template:[e.template];
			var hash = "none";
			if( e.vo ){
				hash = "vo" + e.vo.id + "_" + e.voimageid;
			}
			if( !uniq[hash] ){
				uniq[hash] = e;
				uniq[hash].occiids = [e.id];
				uniq[hash].template = e.template;
				uniq[hash].items = [{templates: e.template, occid: e.id, endpointurl: e.occi_endpoint_url}];
			}else{
				uniq[hash].occiids.push(e.id);
				uniq[hash].template = uniq[hash].template.concat(extendArray(e.template));
				uniq[hash].items.push({templates: e.template, occid: e.id, endpointurl: e.occi_endpoint_url});
			}
		});
		
		for(var i in uniq){
			if( uniq.hasOwnProperty(i) === false ) continue;
			res.push(uniq[i]);
		}
		return res;
	}

	flattenVOs = (typeof flattenVOs === 'boolean') ? flattenVOs : false;

	return group(occiservices);
};
appdb.utils.GroupSiteTemplates = function(data){
	var sortHashed = function(a,b){
		var aa = a || {main_memory_size:0,logical_cpus:0,physical_cpus:0,connectivity_in:"FALSE",connectivity_out: "FALSE",os_family:""};
		var bb = b || {main_memory_size:0,logical_cpus:0,physical_cpus:0,connectivity_in:"FALSE",connectivity_out: "FALSE",os_family:""};
		
		if( parseInt(aa.main_memory_size) > parseInt(bb.main_memory_size) ) return -1;
		if( parseInt(aa.main_memory_size) < parseInt(bb.main_memory_size) ) return 1;
		
		if( parseInt(aa.logical_cpus) > parseInt(bb.logical_cpus) ) return -1;
		if( parseInt(aa.logical_cpus) < parseInt(bb.logical_cpus) ) return 1;
		
		if( parseInt(aa.physical_cpus) > parseInt(bb.physical_cpus) ) return -1;
		if( parseInt(aa.physical_cpus) < parseInt(bb.physical_cpus) ) return 1;
		
		var acon = ($.trim(aa.connectivity_in).toUpperCase() === "TRUE")?true:false;
		var bcon = ($.trim(bb.connectivity_in).toUpperCase() === "TRUE")?true:false;
		if( acon > bcon ) return -1;
		if( acon < bcon ) return 1;
		
		acon = ($.trim(aa.connectivity_out).toUpperCase() === "TRUE")?true:false;
		bcon = ($.trim(bb.connectivity_out).toUpperCase() === "TRUE")?true:false;
		if( acon > bcon ) return -1;
		if( acon < bcon ) return 1;
		
		if( aa.os_family > bb.os_family ) return 1;
		if( aa.os_family < bb.os_family ) return -1;
		
		return 0;
	};
	var hashes = {};
		var res = [];
		$.each(data, function(i,e){
			var h = $.trim(e.group_hash);
			if( !hashes[h] ){
				hashes[h] = $.extend(true, {}, e);
				hashes[h].templates = hashes[h].templates || [];
			}
			hashes[h].templates.push( { id: e.resource_id, name: e.resource_name, manager: e.resource_manager, grouphash: h} );
		});
		for(var i in hashes){
			if( hashes.hasOwnProperty(i) === false ) continue;
			res.push(hashes[i]);
		}
		res.sort(sortHashed);
		return res;	
};
appdb.utils.findGroupTemplatesByHash = function(hash, data){
	hash = $.trim(hash);
	data = data || [];
	data = $.isArray(data)?data:[data];
	var uniq = {};
	$.each(data, function(i,e){
		if( e.group_hash === hash && !uniq[e.resource_id] ){
			uniq[e.resource_id] = $.extend(true, {}, e);
		}
	});
	var res = [];
	for(var i in uniq){
		if( uniq.hasOwnProperty(i) === false ) continue;
		res.push(uniq[i]);
	}
	return res;
};
appdb.utils.pagifyHTMLList = function(ul, pagertype){
	if( $(ul).length === 0 || $(ul).children("li").length <= 1 ) return ul;
	$(ul).closest(".usageids").addClass("hasmany");
	$(ul).wrap("<div class='pageview'></div>");
	var pageview = $(ul).parent();
	$(pageview).wrap("<div class='pagifier'></div>");
	var pagifier = $(pageview).parent();
	$(pageview).addClass($.trim(pagertype));
	
	var checkButtons = (function(el){ 
		return function(){
			var index = $(el).data("index");
			if( index === 0 ){
				$(prev).find("button").addClass("btn-disabled").prop("disabled", true);
			}else{
				$(prev).find("button").removeClass("btn-disabled").prop("disabled", false);
			}
			
			if( (index+1) === $(el).children("ul").children("li").length ){
				$(next).find("button").addClass("btn-disabled").prop("disabled", true);
			}else{
				$(next).find("button").removeClass("btn-disabled").prop("disabled", false);
			}
			
			$(el).children("ul").children("li").removeClass("current");
			$($(el).children("ul").children("li")[index]).addClass("current");
			
			$(el).closest(".pagifier").find(".pagelist > li > button").removeClass("btn-primary").addClass("disabled");
			$($(el).closest(".pagifier").find(".pagelist > li")[index]).find("button").addClass("btn-primary").removeClass("disabled");	
		};
	})(pageview);
	
	var prev = $("<div class='prev action'><button type='button' class='btn btn-primary btn-disabled' disabled='disabled'><span></span></button></div>");
	var next = $("<div class='next action'><button type='button' class='btn btn-primary'><span></span></button></div>");
	$(prev).find("button > span").text("<");
	$(prev).find("button").off("click").on("click", function(ev){
		ev.preventDefault();
		if( $(this).attr("disabled") ) return false;
		
		var c = $(pageview).find("li.current");
		var p = $(c).prev("li");
		if( $(p).length === 1 ){
			$(pageview).data("index", $(p).index() );
		}
		checkButtons();
		return false;
	});
	$(next).find("button > span").text(">");
	$(next).find("button").off("click").on("click", function(ev){
		ev.preventDefault();
		if( $(this).attr("disabled") ) return false;
		
		var c = $(pageview).find("li.current");
		var p = $(c).next("li");
		if( $(p).length === 1 ){
			$(pageview).data("index", $(p).index() );
		}
		checkButtons();
		return false;
	});
	$(ul).children("li.current").removeClass("current");
	$(ul).find("li:first").addClass("current");
	$(pageview).data("count",$(ul).children("li").length);
	$(pageview).prepend(prev).append(next);
	
	var pagelist = $("<ul class='pagelist'></ul>");
	$(ul).children("li").each(function(i,e){
		var li = $("<li></li>");
		var button = $("<button type='button' class='btn btn-compact pageitem disabled'></button>");
		$(button).off("click").on("click", (function(index){
			return function(ev){
				ev.preventDefault();
				ev.stopPropagation();
				$(this).closest(".pagifier").find(".pageview").data("index",index);
				checkButtons();
				return false;
			};
		})(i));
		$(li).append(button);
		$(pagelist).append(li);
	});
	$(pagelist).find("button:first").addClass("btn-primary").removeClass("disabled");
	$(pagifier).append(pagelist);
	
	return pagifier;
};
appdb.utils.throttle = function(fn, threshhold, scope) {
	threshhold || (threshhold = 250);
	var last, deferTimer;
	return function () {
		var context = scope || this;
		var now = +new Date, args = arguments;
		if (last && now < last + threshhold) {
			clearTimeout(deferTimer);
			deferTimer = setTimeout(function () {
			  last = now;
			  fn.apply(context, args);
			}, threshhold );
		} else {
			last = now;
			fn.apply(context, args);
		}
	};
};

appdb.utils.RelationsRegistry = (function(){
	return  { 
		getByTargetType: function(v,d){
			return $.grep(d || appdb.model.StaticList.RelationTypes, function(e){
				return $.trim(e.targettype).toLowerCase() === $.trim(v).toLowerCase();
			});
		},
		getBySubjectType: function(v,d){
			return $.grep(d || appdb.model.StaticList.RelationTypes, function(e){
				return $.trim(e.subjecttype).toLowerCase() === $.trim(v).toLowerCase();
			});
		},
		getBySubjectTargetPairs: function(s,t,d){
			return $.grep(appdb.model.StaticList.RelationTypes, function(e){
				return ($.trim(e.subjecttype).toLowerCase() === $.trim(s).toLowerCase()) && ($.trim(e.targettype).toLowerCase() === $.trim(t).toLowerCase());
			});
		},
		getByVerbName: function(v,d){
			return $.grep(d || appdb.model.StaticList.RelationTypes, function(e){
				return $.trim(e.verb).toLowerCase() === $.trim(v).toLowerCase();
			});
		},
		getByTriplet: function(s,v,o,d){
			return this.getByVerbName(v, this.getBySubjectTargetPairs(s,o,d));
		},
		getVerbsBySubjectTargetPairs: function(s,t){
			var p = this.getBySubjectTargetPairs(s,t);
			return $.map(p, function(e){
				return { "id": e.id, "verb": e.verb, "directverb": e.directverb, "reverseverb": e.reverseverb, "actionid": e.actionid };
			});
		},
		getRelationTypeByID: function(id,d){
			var rel = $.grep(d || appdb.model.StaticList.RelationTypes, function(e){
				return $.trim(e.id).toLowerCase() === $.trim(id).toLowerCase();
			});
			return (rel.length)?rel[0]:null;
		},
		getRelationData: function(reltype, data){
			reltype = $.trim(reltype).toLowerCase();
			data = data || {};
			var res = {name: "unknown", guid: "", id: "", type: reltype};
			switch(reltype){
				case "application":
					break;
				case "person":
					break;
				case "vo":
					break;
				case "organization":
					break;
				case "project":
					break;
				default:
					break;
			}
			return res;
		}
	};
})();

appdb.utils.harvester = {};
appdb.utils.harvester.organization = (function(){
	return {
		fromRelation: function(data){
			if( !data || typeof data !== "object" || $.isEmptyObject(data)) return null;
		
			data = $.extend(true,{},data || {});
			if( typeof data.entity === "undefined" || typeof data.entity.guid === "undefined" || $.trim(data.entity.guid) === "" || !data.entity.organization){
				return null;
			}
			var res = {guid: data.entity.guid, property: [
					{name: "legashortlname", val: (function(v){
							return function(){
								return v;
							};
					})(data.entity.organization.shortname) },
					{name: "legalname", val: (function(v){
							return function(){
								return v;
							};
					})(data.entity.organization.name) }
			]};
			return res;
		},
		sortList: function(d){
			d = d || [];
			d = $.isArray(d)?d:[d];
			$.each(d, function(i,e){
				e.property = e.property || [];
				e.property = $.isArray(e.property)?e.property:[e.property];
				var fulltext = ["",""];
				$.each(e.property, function(ii,ee){
					var v = $.trim((ee.val)?ee.val():"");
					if(ee["name"] === "legashortlname"){
						fulltext[0] = v;
					}else if(ee["name"] === "legalname"){
						fulltext[1] = v;
					}
				});
				e.fulltext = fulltext.join(" ");
			});
			d.sort(function(a,b){
				var aa = $.trim(a.fulltext);
				var bb = $.trim(b.fulltext);
				if( aa > bb ) return 1;
				if( aa < bb ) return -1;
				return 0;
			});
		},
		sortData: function(d){
			d = d || [];
			d = $.isArray(d)?d:[d];
			$.each(d, function(i,e){
				if(e.entity && e.entity.organization ){
					e.fulltext = [ ($.trim(e.entity.organization.shortname) || ""),($.trim(e.entity.organization.name) || "")].join(" ");
				}
			});
			d.sort(function(a,b){
				var aa = $.trim(a.fulltext);
				var bb = $.trim(b.fulltext);
				if( aa > bb ) return 1;
				if( aa < bb ) return -1;
				return 0;
			});
		}
	};
})();

appdb.utils.harvester.project = (function(){
	return {
		fromRelation: function(data){
			if( !data || typeof data !== "object" || $.isEmptyObject(data)) return null;
		
			data = $.extend(true,{},data || {});
			if( typeof data.entity === "undefined" || typeof data.entity.guid === "undefined" || $.trim(data.entity.guid) === "" || !data.entity.project){
				return null;
			}
			var res = {guid: data.entity.guid, property: [
					{name: "ga", val: (function(v){
							return function(){
								return v;
							};
					})(data.entity.project.ga) },
					{name: "acronym", val: (function(v){
							return function(){
								return v;
							};
					})(data.entity.project.acronym) },
					{name: "title", val: (function(v){
							return function(){
								return v;
							};
					})(data.entity.project.title) }
			]};
			return res;
		},
		sortList: function(d){
			d = d || [];
			d = $.isArray(d)?d:[d];
			$.each(d, function(i,e){
				e.property = e.property || [];
				e.property = $.isArray(e.property)?e.property:[e.property];
				var fulltext = ["",""];
				$.each(e.property, function(ii,ee){
					var v = $.trim((ee.val)?ee.val():"");
					if(ee["name"] === "acronym"){
						fulltext[0] = v;
					}else if(ee["name"] === "title"){
						fulltext[1] = v;
					}
				});
				e.fulltext = fulltext.join(" ");
			});
			d.sort(function(a,b){
				var aa = $.trim(a.fulltext);
				var bb = $.trim(b.fulltext);
				if( aa > bb ) return 1;
				if( aa < bb ) return -1;
				return 0;
			});
		},
		sortData: function(d){
			d = d || [];
			d = $.isArray(d)?d:[d];
			$.each(d, function(i,e){
				if(e.entity && e.entity.project ){
					e.fulltext = [ ($.trim(e.entity.project.acronym) || ""),($.trim(e.entity.project.title) || "")].join(" ");
				}
			});
			d.sort(function(a,b){
				var aa = $.trim(a.fulltext);
				var bb = $.trim(b.fulltext);
				if( aa > bb ) return 1;
				if( aa < bb ) return -1;
				return 0;
			});
		}
	};
})();

appdb.utils.harvester.software = (function(){
	return {
		fromRelation: function(data){
			if( !data || typeof data !== "object" || $.isEmptyObject(data)) return null;
		
			data = $.extend(true,{},data || {});
			if( typeof data.entity === "undefined" || typeof data.entity.guid === "undefined" || $.trim(data.entity.guid) === "" || !data.entity.application){
				return null;
			}
			var res = {guid: data.entity.guid, property: [
					{name: "name", val: (function(v){
							return function(){
								return v;
							};
					})(data.entity.application.name) },
					{name: "description", val: (function(v){
							return function(){
								return v;
							};
					})(data.entity.application.description) }
			]};
			return res;
		},
		sortList: function(d){
			d = d || [];
			d = $.isArray(d)?d:[d];
			$.each(d, function(i,e){
				e.property = e.property || [];
				e.property = $.isArray(e.property)?e.property:[e.property];
				var fulltext = ["",""];
				$.each(e.property, function(ii,ee){
					var v = $.trim((ee.val)?ee.val():"");
					if(ee["name"] === "name"){
						fulltext[0] = v;
					}else if(ee["description"] === "title"){
						fulltext[1] = v;
					}
				});
				e.fulltext = fulltext.join(" ");
			});
			d.sort(function(a,b){
				var aa = $.trim(a.fulltext);
				var bb = $.trim(b.fulltext);
				if( aa > bb ) return 1;
				if( aa < bb ) return -1;
				return 0;
			});
		},
		sortData: function(d){
			d = d || [];
			d = $.isArray(d)?d:[d];
			$.each(d, function(i,e){
				if(e.entity && e.entity.project ){
					e.fulltext = [ ($.trim(e.entity.application.name) || ""),($.trim(e.entity.application.description) || "")].join(" ");
				}
			});
			d.sort(function(a,b){
				var aa = $.trim(a.fulltext);
				var bb = $.trim(b.fulltext);
				if( aa > bb ) return 1;
				if( aa < bb ) return -1;
				return 0;
			});
		}
	};
})();
appdb.utils.harvester.vappliance = appdb.utils.harvester.software;
appdb.utils.harvester.swappliance = appdb.utils.harvester.software;

appdb.utils.entity = (function(){
	return {
		getConfigList: function(entity,o,e){
			return appdb.utils.entity.getConfig(entity,o,e,true);
		},
		getConfig: function(entity,o,e, list){
			var ents = $.extend(true,{}, appdb.config.entities);
			var c = null;
			entity = $.trim(entity).toLowerCase();
			list = (typeof list === "boolean")?list:false;
			if( $.inArray(entity, ["sw","software","switem"]) > -1 ){
				c = "software"; 
			} else if( $.inArray(entity, ["vapp","vappliance","virtual appliance","virtual appliances","virtualappliance","virtualappliances","vappitem"]) > -1 ){
				c = "vappliance"; 
			} else if( $.inArray(entity, ["swappitem","swappliance","softwareappliance","software appliance","softwareappliances","software appliance"]) > -1 ){
				c = "swappliance"; 
			} else if( $.inArray(entity, ["vo","vos","virtualorganization","virtual organization","virtualorganizations","virtual organizations"]) > -1 ){
				c = "vo"; 
			} else if( $.inArray(entity, ["site","sites"]) > -1 ){
				c = "site";
			} else if( $.inArray(entity, ["dataset","datasets"]) > -1 ){
				c = "dataset";
			}else {
				c = "software";
			}
			c += (list)?"list":"";
			var ent = ents[c] || {};
			
			return new appdb.utils.entity["Config"+ ((list)?"List":"Item")](ent,(o || {}),(e || {}));
		}
	};
})();
appdb.utils.entity.ConfigItem = function(t,o,e){
	this.data = $.extend(true,{},t);
	
	this.name = function(){
		return $.trim(this.data.name);
	};
	
	this.componentType = function(){
		return $.trim(e.componentType) || $.trim(this.data.componenttype);
	};
	
	this.registerTitle = function(){
		return this.data.registertitle;
	};
	this.metaType = function(){
		return this.data.metatype;
	};
	this.entityType = function(){
		return this.data.entitytype;
	};
	this.navigationPanel = function(){
		return this.data.navigationpanel || null;
	};
	this.href = function(){
		if( o && o.id && $.trim(o.id) !== "0" ){
			var h = $.trim(this.data.href.replace(/\{\$cname\}/, $.trim(o.cname || "").toLowerCase())).toLowerCase();
			h = h.replace(/\{\$id\}/, $.trim(o.id || "").toLowerCase()).toLowerCase(); 
			return h;
		}else{
			return "/store/register/" + this.name({});
		}
	};
	
	this.content = function(){
		return $.trim(e.content) || $.trim(this.data.contenttype).toLowerCase();
	};
};
appdb.utils.entity.ConfigList = function(t,o,e){
	this.data = $.extend(true,{},t);

	this.name = function(d){
		return $.trim(this.data.name);
	};
	
	this.componentType = function(d){
		return $.trim(e.componentType) || $.trim(this.data.componenttype);
	};
	
	this.filter = function(){
		return ( $.trim(o || o.flt)===""?this.data.filter:"" );
	};
	
	this.filterDisplay = function(){
		if( $.trim(e.filterDisplay) !== "" ){
			return $.trim(e.filterDisplay);
		}else if($.trim(this.data.filterdisplay) === "" ){
			var subtype = $.trim(e.subType || "");
			if( subtype.length > 0 ){
				subtype += " ";
			}
			return "Search " + subtype + $.trim(this.name()).toLowerCase() + "...";
		}
		return $.trim(this.data.filterdisplay);
	};
	
	this.href = function(){
		var h = $.trim(this.data.href.replace(/\{\$cname\}/, $.trim(o.cname || "").toLowerCase())).toLowerCase();
		h = h.replace(/\{\$id\}/, $.trim(o.id || "").toLowerCase()).toLowerCase(); 
		if( $.trim(e.subType) !== ""){
			h += "/" + $.trim(e.subType).toLowerCase();
		}
		return h;
	};
	
	this.content = function(){
		return $.trim(e.content) || $.trim(this.data.contenttype).toLowerCase();
	};
};
appdb.utils.entity.getCategoryByName = function(catname){
	catname = $.trim(catname).toLowerCase();
	var res = $.grep(appdb.model.StaticList.Categories, function(e){
		return ( e.val )?($.trim(e.val()).toLowerCase()===catname):false;
	});
	return ( res.length > 0 )?res[0]:{id:-1,parentid:-1};
};
appdb.utils.entity.getCategoriesByParentId = function(parentid, onlyids){
	onlyids = (typeof onlyids === "boolean")?onlyids:false;
	parentid = $.trim(parentid);
	var ids = {};
	
	$.each(appdb.model.StaticList.Categories, function(i,e){
		if( $.trim(e.parentid) === parentid || ids[e.parentid] ){
			ids[e.id] = (onlyids)?e.id:e;
		}
	});
	var res = [];
	for(var i in ids){
		if( ids.hasOwnProperty(i) === false ) continue;
		res.push(ids[i]);
	}
	return res;
};
appdb.utils.entity.getCategoriesByType = function(entitytype){
	var filter = appdb.utils.getCategoryFilterByType(entitytype);
	return $.grep(appdb.model.StaticList.Categories, filter);
};
appdb.utils.entity.getCategoryFilterComparator = function(catids,inclusion){
	inclusion = (typeof inclusion === "boolean")?inclusion:false;
	catids = $.isArray(catids || [])?catids:[catids];
	if( inclusion ){
		return (function(ids){
			return function(e){
				return ($.inArray($.trim(e.id), ids) > -1 || $.inArray($.trim(e.parentid), ids) > -1 );
			};})(catids);
	} else {
		return (function(ids){
			return function(e){
					return ( $.inArray($.trim(e.id), ids) < 0 && $.inArray($.trim(e.parentid), ids) < 0 );
			};})(catids);
	}
};
appdb.utils.entity.getCategoryFilterByType = function(entitytype){
	var cnf = appdb.utils.entity.getConfig(entitytype);
	var catids = [];
	var inclusion = true;
	var swappid = appdb.utils.entity.getCategoryByName("software appliances").id+"";
	switch(cnf.content()){
		case "vappliance":
			catids = ["34"];
			break;
		case "swappliance":
			catids = appdb.utils.entity.getCategoriesByParentId(swappid, true);
			catids.unshift(swappid);
			break;
		case "software":
		default:
			catids = [swappid, "34"];
			inclusion = false;
			break;
	}
	var comparator = appdb.utils.entity.getCategoryFilterComparator(catids,inclusion);
	var filter = (function(comp){
		var f = function(d){
			return $.grep(d, comp);
		};
		f.compare = comp;
		return f;
	})(comparator);
	
	return filter;
};

appdb.utils.isEqual = function(o1,o2,cfg,reverse){
	cfg = cfg || {};
	cfg.exclude = cfg.exclude || {};	
	
	//first we check the reference. we don't care if null== undefined        
	if( cfg.strictMode ){
		if( o1 === o2 ) return true;            
	}
	else{
		if( o1 == o2 ) return true;            
	}
	
	if( typeof o1 === "number" || typeof o1 === "string" || typeof o1 === "boolean" || !o1 || 
		typeof o2 === "number" || typeof o2 === "string" || typeof o2 === "boolean" || !o2 ){
			return false;
		} 
	
	if( ((o1 instanceof Array) && !(o2 instanceof Array)) || 
		((o2 instanceof Array) && !(o1 instanceof Array))) return false;
	
	for( var p in o1 ){
		if( cfg.exclude[p] || !o1.hasOwnProperty(p) ) continue;
		if( !isEqual(o1[p],o2[p], cfg  ) ) return false;
	}
	if( !reverse && !cfg.noReverse ){
		reverse = true;
		return isEqual(o2,o1,cfg,reverse);  
	}
	return true;  
};

appdb.utils.ShowVerifyDialog = function(o){
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
		style: o.css || "max-width:650px;max-height:500px;",
		onHide: function() {
			setTimeout(function(){
				_dialog.hide();
				_dialog.destroyRecursive(false);
			},10);
			options.cancelcallback();
		}
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

appdb.utils.formatSizeUnits = function(bytes,displayunit){
	displayunit = (typeof displayunit === "boolean")?displayunit:true;
	var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PT', 'Exa Bytes', 'Zetta Bytes', 'Yotta Bytes', 'Xeno Bytes', 'Shilentno Bytes', 'Domegemegrotte Bytes',"Icose Bytes","Monoicose Byte"];
	if (bytes === 0) return '0 Bytes';
	var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
	return Math.round(bytes / Math.pow(1024, i), 2) + ((displayunit===true)?' ' + sizes[i]:'');
};

appdb.utils.isNumber = function(v){
	return !isNaN(parseFloat(v)) && isFinite(v);
};

appdb.utils.valueToObject = function(name,value,funcnames){
	var i,len,res = {};
	funcnames = funcnames || [];
	funcnames = $.isArray(funcnames)?funcnames:[funcnames];
	var ns = $.trim(name).split(".");
	len = ns.length;
	i = len-1;
	if( len >= 1 ){
		if( $.inArray(ns[i],funcnames) > -1 ){
			res[ns[i]] = (function(v){ return function() { return v;}; })(value);
		}else{
			res[ns[i]] = value;
		}
	}
	if( len >= 2 ){
		for( i=len-2; i>-1; i-=1 ){
			res[ns[i]] = $.extend(true,{},res[ns[i+1]]);
		}
	}
	return res;
};


appdb.utils.guid = function(prefix){
	function s4() {
		return Math.floor((1 + Math.random()) * 0x10000)
		  .toString(16)
		  .substring(1);
	  }
	  var pr = ($.trim(prefix))?prefix+'-':'';
	  return pr + s4() + s4() + '-' + s4() + '-' + s4() + '-' +
		s4() + '-' + s4() + s4() + s4();
};

appdb.utils.isLocalDomainUrl = function(url){
	var ld = ":" + appdb.config.endpoint.base.replace(/^http(s){0,1}\:/,"").toLowerCase();
	var url = ":" + $.trim(url).replace(/^http(s){0,1}\:/,"").toLowerCase();
	
	return ( url.indexOf(ld) === 0 );	
};

appdb.utils.SecantVOImagelistWatcher = function(voId, callback) {
    var _registry = {};
    var _ajx = null;
    var _timeoutInterval = 5000;
    var _timer = null;

    var abortRequest = function() {
	    if (_ajx) {
		    if (_ajx.abort) {
			_ajx.abort();
		    }
	    }
	    _ajx = null;
    };

    var diffResults = function(reports) {
	var diffs = {};

	$.each(reports, function(i, report) {
	    var registredItem = _registry[report.report_id];
	    if (registredItem && registredItem.state !== report.state) {
		report.oldState = registredItem.state;
		diffs[report.app_id]= diffs[report.app_id] || [];
		diffs[report.app_id].push(report);
		_registry[report.report_id] = report;
	    }
	});

	return diffs;
    };

    var dispatchRequest = function(reportIds, cb) {
	abortRequest();
	reportIds = reportIds || [];
	reportIds = $.isArray(reportIds) ? reportIds : [reportIds];

	if (reportIds.length === 0) {
		return cb();
	}

	_ajx = $.get(appdb.config.endpoint.base + "vo/secantreport", {id: voId, format: 'js', reports: reportIds.join(';')}).done(function(data) {
		_ajx = null;
		var secant = (((data || {}).result || {}).report || []);
		var diffs = diffResults(secant);
		cb(diffs);
	    }.bind(this)).fail(function(err) {
		_ajx = null;
		appdb.debug('[ERROR][Secant report watcher]: ', err);
		cb(null);
	    }.bind(this));
    };

    var start = function() {
	if (_timer) {
	    return;
	}

	_timer = setTimeout(function() {
	    if (_timer === null) {
		return;
	    }
	    var reportIds = Object.keys(_registry);
	    dispatchRequest(reportIds, function(diffs) {
		clearTimeout(_timer);
		_timer = null;
		start();
		if (diffs !== null) {
		    callback(diffs);
		}
	    });
	}, _timeoutInterval || 5000);
    }

    var clearAll = function() {
	abortRequest();
	_registry = {};
    };

    var watch = function(vappliance) {
	var secants = (vappliance || {}).secant || [];
	secants = $.isArray(secants) ? secants : [secants];

	$.each(secants, function(i, sec) {
		if (_registry[sec.report_id]) {
		    return;
		}
		_registry[sec.report_id] = sec;
	});
	start();
    };

    var reset = function() {
	abortRequest();
	_registry = {}
	if (_timer) {
	    clearTimeout(_timer);
	    _timer = null;
	}
    };

    return {
	clearAll: clearAll,
	reset: reset,
	watch: watch,
	start: start
    };
}
