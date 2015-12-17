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
appdb.social = {};
appdb.social.share = {};
appdb.social.share.Handler = appdb.ExtendClass(appdb.View,"appdb.social.share.Handler", function(o){
	this.options = {
		name: o.name,
		parent: o.parent,
		container: $(o.container),
		data: o.data,
		url: o.url ||"",
		dlg: null,
		entityType: o.entityType || "Software"
	};
	this.preShare = function(){
		if( this.options.dlg !== null ){
			if( typeof this.options.dlg.close === "function"){
				this.options.dlg.close();
			}
			this.options.dlg = null;
		}
		return true;
	};
	this.share = function(){
		if( this.preShare() === true){
			this.onShare();
		}
		setTimeout((function(self){
			return function(){
				self.postShare();
			};
		})(this),0);
	};
	this.onShare = function(){
		//to be overriden
	};
	this.postShare = function(){
		//to be overriden
	};
	this.onError = function(err){
		//to be overriden
	};
	this._init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
	};
	this._init();
	
});
appdb.social.share.HandlerFacebook = appdb.ExtendClass(appdb.social.share.Handler,"appdb.social.share.HandlerFacebook", function(o){
	this.options = $.extend(this.options,{
		name: o.name || "facebook"
	});
	this.onShare = function(){
		var d = this.options.data || {};
		d = d.application || d;
		var href = this.options.url || location.href; 
		var title = d.name || "Share in facebook";
		href = href.replace(/^https\:/,"http:");
		var url = location.protocol+'//www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(href);
		this.options.dlg = window.open(url,title,'width=626,height=436');		
	};
});

appdb.social.share.HandlerLinkedin= appdb.ExtendClass(appdb.social.share.Handler,"appdb.social.share.HandlerLinkedin", function(o){
	this.options = $.extend(this.options,{
		name: o.name || "linkedin"
	});
	this.onShare = function(){
		var d = this.options.data || {};
		d = d.application || d;
		var href = this.options.url || location.href; 
		var title = d.name || "Share in LinkedIn";
		href = href.replace(/^https\:/,"http:");
		var url = location.protocol+'//www.linkedin.com/cws/share?url='+encodeURIComponent(href);
		this.options.dlg = window.open(url,title,'width=626,height=436');		
	};
});
appdb.social.share.HandlerTwitter= appdb.ExtendClass(appdb.social.share.Handler,"appdb.social.share.HandlerTwitter", function(o){
	this.options = $.extend(this.options,{
		name: o.name || "twitter"
	});
	this.onShare = function(){
		var d = this.options.data || {};
		d = d.application || d;
		var href = this.options.url || location.href; 
		var title = d.name || "Share in Twitter";
		href = href.replace(/^https\:/,"http:");
		var entity = ($.trim(this.options.entityType).toLowerCase() === "vappliance")?"Virtual Appliance":"Software";
		var url = location.protocol+'//twitter.com/intent/tweet?text='+encodeURIComponent(entity + " " + d.name + " | EGI Applications Database") + '&url='+encodeURIComponent(href)+"&original_referer=" + encodeURIComponent("http://" + location.hostname);
		this.options.dlg = window.open(url,title,'width=626,height=436');		
	};
});
appdb.social.share.HandlerGoogleplus= appdb.ExtendClass(appdb.social.share.Handler,"appdb.social.share.HandlerGoogleplus", function(o){
	this.options = $.extend(this.options,{
		name: o.name || "googleplus"
	});
	this.onShare = function(){
		var d = this.options.data || {};
		d = d.application || d;
		var href = this.options.url || location.href; 
		var title = d.name || "Share in Google+";
		href = href.replace(/^https\:/,"http:");
		var url = location.protocol+'//plus.google.com/share?url='+encodeURIComponent(href);
		this.options.dlg = window.open(url,title,'width=626,height=436');		
	};
});
appdb.social.share.ListToolbox = appdb.ExtendClass(appdb.View, "appdb.social.share.ListToolbox", function(o){
	this.options = {
		container: $(o.container),
		handlers: [],
		data: o.data || appdb.pages.application.currentData(),
		entityType: $.trim(o.entityType).toLowerCase() || "Software"
	};
	this.addHandler = function(link){
		var name = $(link).data("name") || "";
		if( $.trim(name) === ""){
			return;
		}
		var uname = name;
		if( uname.length > 0 ){
			uname = uname[0].toUpperCase() + uname.substr(1);
		}
		var handler = appdb.FindNS("appdb.social.share.Handler" + uname);
		if( !handler ){
			return;
		}
		var handlerOptions = {
			data: this.options.data,
			entityType: this.options.entityType,
			parent: this
		};
		switch($.trim(this.options.entityType).toLowerCase()){
			case "person":
				handlerOptions.url = appdb.config.endpoint.base +"store/person/" + this.options.data.cname;
				break;
			case "vo":
				handlerOptions.url = appdb.config.endpoint.base +"store/vo/" + this.options.data.cname;
				break;
			case "vappliance":
			case "virtual appliance":
				handlerOptions.url = appdb.config.endpoint.base +"store/vappliance/" + this.options.data.cname;
				break;
			case "application":
			case "software":
			default:
				handlerOptions.url = appdb.config.endpoint.base +"store/software/" + this.options.data.cname;
				break;
		}
		this.options.handlers.push(new handler(handlerOptions));
	};
	this.getHandler = function(name){
		var res = null;
		$.each(this.options.handlers, function(i,e){
			if( res === null &&e.options.name === name ){
				res = e;
			}
		});
		return res;
	};
	this.render = function(){
		this.initContainer();
		$(this.dom).removeClass("hidden");
		$(this.dom).find("a.social.share").unbind("click").bind("click",(function(self){
			return function(ev){
				ev.preventDefault();
				var h = self.getHandler($(this).data("name"));
				if( h !== null ){
					h.share();
				}
				return false;
			};
		})(this));
	};
	this.initContainer = function(){
		$.each(this.options.handlers, function(i, e){
			e.reset();
		});
		this.options.handlers = [];
		$(this.dom).find("a.social.share").each((function(self){
			return function(i,e){
				self.addHandler(e);
			};
		})(this));
	};
	this._init = function(){
		this.dom = this.options.container;
		if( this.options.data && this.options.data.datatype && this.options.data.type === "entry"){
			this.options.data = this.options.data[this.options.data.datatype];
		}
		this.initContainer();
	};
	this._init();
});