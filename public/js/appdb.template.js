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
appdb.config.templates = {
    parser : {
        dataitem : new RegExp(/\{[^\{\}]*\}/g)
    }
};
appdb.TemplateManager = (function(){
    return new function(){
    this._factories = [];
    this.NSTemplate = "http://appdb.egi.eu/0.1/Template";
    this.NSTemplateAttribute = "http://appdb.egi.eu/0.1/Template/Properties";
	this.NSTemplateEvent = "http://appdb.egi.eu/0.1/Template/Events";
    this.getFactoryIndex = function(name){
        var i, c = this._factories, len = c.length;
        for(i=0; i<len; i+=1){
            if(c[i].name===name){
                return i;
            }
        }
        return -1;
    };
    this.count = function(){
        return this._factories.length;
    };
    this.getFactory = function(name){
        var i = this.getFactoryIndex(name);
        if(i<0){
            return null;
        }
        return this._factories[i].template;
    };
    this.validateControl = function(o){
        if(typeof o === "undefined"){
            console.log("Tried to register a template control with no data given");
            return false;
        }
        if(typeof o.name !== "string"){
            console.log("Cannot register a template control without a name");
            return false;
        }
        if((o.template instanceof appdb.TemplateFactory)===false){
            console.log("The template control must inherit TemplateFactory type");
            return false;
        }
        return true;
    };
    this.register = function(o,replace){
        if(!this.validateControl(o)){
            return false;
        }
        replace = replace || false;
        var i = this.getFactoryIndex(o.name);
        if(i>=0 && replace){
            this._factories[i] = o;
        }else{
            this._factories[this._factories.length] = o;
        }
        return true;
    };
};})();

appdb.TemplateProperty = appdb.DefineClass("appdb.TemplateProperty",function(o){
    o = o || {};
    this.name = o.name || "";
    this.dataType = o.dataType || "text";
    this.owner = o.owner || null;
    this.defaultValue = o.defaultValue || null;
    this._value = o.value || this.defaultValue;
    this.type = o.type || null;
    this.typeInitArgs = o.typeInitArgs || null;
    this.isSet = o.isSet || false;
    this.getter = function(){
        return this._value;
    };
    this._objectSetter = function(v){
        var ob = this._value;
        if(typeof v !== "object"){
            if(ob.setStringValue){
                ob.setStringValue(v);
            }
        }else{
            ob.setter(v);
        }
    };
    this.setter = function(v){
        this._value = v;
        return this.owner;
    };
    
    this._initProperty = function(){
        var ob = null, oba = {}, tia = this.typeInitArgs;
        if(this.type!==null){
            this.setter = this._objectSetter;  
        }
        if(this.isSet===true){
            this.value = o.value;
        }
    };
    this._initProperty();
});
appdb.TemplateControl = appdb.DefineClass("appdb.TemplateControl",function(o){
    this._dataitemExp = appdb.config.templates.parser.dataitem;
    this.attributes = {};
    this.properties = {};
    this.propertyData = {};
	this.events = {};
	this.eventData = {};
    this.controls = [];
    this.body = null;
    this.content = null;
    this.parent = null;
    this._dataValue = null;
    this._defaultDataValue = null;
    this.reset = function(){
        var attributes = $.map(this.attributes, function(item) {
            return item.name;
          });
        var c = $(this.content);
        $.each(attributes, function(i, item) {
            c.removeAttr(item);
        });
    };
    this.setStringValue = function(v){
        this._defaultDataValue = v;
    };
    this.getter = function(name){
        if(this.properties[name]){
            return this.properties[name].getter();
        }
        console.log("Property '"+name+"' does not exist");
        return null;
    };
    this.setter = function(name,val){
        if(this.properties[name]){
            return this.properties[name].setter(val);
        }
        console.log("Property '"+name+"' does not exist");
        return this;
    };
    this.getId = function(){
      return this.attributes.id || "";
    };
    this.findData = function(d,item){
        if(typeof item === "undefined"){
           return d;
        }
        if(typeof item === "string" && item.indexOf(".")>0){
            item = item.split(".");
        }
        if(typeof item === "string"){
            if(item===""){
                return d;
            }else if(item==="$"){
                if(d.val){
                    return d.val();
                }
                return "";
            }else{
                return d[item];
            }
        }
        var res = d;
        for(var i=0; i<item.length; i+=1){
            if(item[i]==="$"){
                return (res.val)?res.val():"";
            }
            if(res[item[i]]){
                res = res[item[i]];
            }else{
                return "";
            }
        }
        return res;
    };
    this.parseData = function(d,c){
        var _dataitemExp = this._dataitemExp, _unique = function(ar){
           var a = [];
            var l = ar.length;
            for(var i=0; i<l; i++) {
              for(var j=i+1; j<l; j++) {
                if (ar[i] === ar[j])
                  j = ++i;
              }
              a.push(ar[i]);
            }
            return a;
        };
        var a,m=[],md='',di='',j;
        try{
            a = decodeURIComponent(c);
        }catch(err){
            a=c;
        }
        
        m = a.match(_dataitemExp);
        if(m!==null){
            m = _unique(m);
            for(j=0; j<m.length; j+=1){
                md=m[j];
                md = md.substr(1,md.length-2);
                if(md.indexOf(".")<0){
                    md = [md];
                }else{
                    md = md.split(".");
                }
                di = this.findData(d,md);
                if(typeof di === "string"){
                    var r = m[j];
                    a = a.replace(r,di);
                }else{
                    a = di;
                }
                
            }
        }
        return a;
    };
    this.dataBind = function(d){
        var i , atrs = this.attributes, p=null, res = {},props;
        for(i in atrs){
            res[i] = this.parseData(d,atrs[i]);
        }
        for(i in res){
            $(this.content).attr(i,res[i]);
        }
        props = this.properties;
        for(i in props){
            if(props[i].isSet===false){
                continue;
            }
            p = this.getter(i);
            if(props[i].dataType!=="object"){
                p = (p!==null)?this.parseData(d,p):props[i].defaultValue;
                if(props[i].dataType==="bool" && typeof p !=="undefined"){
                    p = ((p==="true")?true:false);
                }
                this.propertyData[i] = p || props[i].defaultValue;
            }else{
                if(props[i].dataType==="bool"){
                    this.propertyData[i] = ((p==="true")?true:false);
                }else{
                    this.propertyData[i] = p;
                }
            }
        }
    };
	this.clearEvents = function(){
		var i, ed = this.eventData;
		if(this.content){
			for(i in ed){
				$(this.content).unbind(i);
			}
		}
		this.eventData = {};
	};
	this.setEvents = function(d){
		this.clearEvents();
		var i, e = this.events, f;
		for(i in e){
			if(e[i].isSet===true){
				f = this.parseData(d,e[i].value || e[i].defaultValue);
				this.eventData[i] = new Function(f);
				$(this.content).unbind(i);
				$(this.content).bind(i,this.eventData[i]);
			}
		}
	};
    this.preRender = function(d){
		this.clearEvents();
        return true;
    };
    this.doRender = function(d){
        if(this.body && this.content!==$(this.body)[0]){
            var cl = $(this.body).clone(true);
            $(this.content).empty();
            $(this.content).append(cl);
        }
    };
    this.postRender = function(d){
		this.setEvents(d);
		$(this.content).show();
        this.propertyData = {};
    };
    this.renderChildren = function(d){
        var i, len = this.controls.length;
        for(i=0; i<len; i+=1){
            this.controls[i].render(d);
        }
    };
    this.render = function(d){
        this.reset();
        this.dataBind(d);
        if(this.preRender(d)){
            this.renderChildren(d);
            this.doRender(d);
            this.postRender(d);
        }
    };
    this._initProperties = function(props){
        var i, p, np;
        this.properties = {};
        for(i in props){
            p = props[i];
            p.owner = this;
            np = new appdb.TemplateProperty(p);
            this.properties[i] = np;
        }
    };
	
    this._init = function(){
        this.attributes = o.attributes || {};
		this.events = o.events || {};
        this._initProperties(o.properties||{});
        if(this.attributes.contenttype){
            this.content = document.createElement(this.attributes.contenttype);
            delete this.attributes.contenttype;
        }else if(o.contentType){
            this.content = document.createElement(o.contentType);
        }else{
            this.content = o.content || document.createElement("span");
        }
        this.controls = o.controls || [];
        
        this.body = o.body;
        this.templateContent = o.templateContent;
    };
    this._init();
});
appdb.templates = {};
appdb.templates.DataItem = appdb.ExtendClass(appdb.TemplateControl,"appdb.templates.DataItem",function(o){
    this.preRender = function(d){
        if(this.propertyData.datamember){
            this.propertyData.datamember = this.findData(d,this.propertyData.datamember);
            if(this.propertyData.datamember){
                
            }else if(this.properties.empty.isSet===true){
                var e = this.properties.empty.getter();
                e.render(d);
                $(this.content).empty().append($(e.content));
                return false;
            }
        }
        this.body = this.templateBody;
        return true;
    };
    this.doRender = function(d){
        var p = this.propertyData.datamember;
		var t = this.propertyData.transform;
		if(t && t!==''){
			t = appdb.FindNS(t, false);
			if(typeof t === "function"){
				p = t(p);
			}
		}
        if(this.body.length!==0){
            $(this.content).empty().append($(this.body));
        }else if(typeof p === "string" || typeof p === "number"){
            $(this.content).empty().html(p);
        }
    };
    this.templateBody = this.body;
});
appdb.templates.Link = appdb.ExtendClass(appdb.TemplateControl, "appdb.templates.Link", function(o){
    this.doRender = function(d){
        var p = this.propertyData;
        $(this.content).attr("href",p.href).attr("target",p.target);
    };
});
appdb.templates.Image = appdb.ExtendClass(appdb.TemplateControl, "appdb.tempates.Image", function(o){
    this.preRender = function(d){
        var p = this.propertyData;
        if(this.properties.renderon.isSet){
            if(p.renderon){
                return true;
            }else{
                return false;
            }
        }
        return true;
    };
    this.doRender = function(d){
        this.reset();
        $(this.content).hide();
        var p = this.propertyData,_this=this;

        if(p.title){
            $(this.content).attr("title",p.title);
        }
        if(p.alt){
            $(this.content).attr("alt",p.alt);
        }
        if(p.src){
            if(p.loader){
                $(this.content).attr("src",p.loader);
                setTimeout(function(){
                    $(_this.content).attr("src",p.src);
                },1);
            }else{
                $(this.content).attr("src",p.src);
            }
            p.loader=null;
        }
        $(this.content).show();
    };
});
appdb.templates.Enum = appdb.ExtendClass(appdb.templates.DataItem,"appdb.templates.Enum",function(o){
    this.enumData = [];
    this.getEnumValue = function(d,kk,vv){
       d = d || "";
       kk = kk || "key";
       vv = vv || "name";
       var v = this.enumData ,len = v.length,i;
		if(d.nil == "true"){//added for compatibility with rest api
			d = "";   
		}
	   for(i=0; i<len; i+=1){
           if(v[i][kk]===d){
               return v[i][vv];
           }
       }
	   return null;
    };
    this.doRender = function(d){
       var p = this.propertyData, keyvalue = p.keyname, valuename = p.valuename, v = p.datamember;
       v = this.getEnumValue(v,keyvalue,valuename);
       $(this.content).empty().append(v);
    };
    this._afterInit = function(){
        var o, p = this.properties.provider.getter();
        if(p!==null){
            o = appdb.FindNS(p);
            this.enumData = o.get();
        }
    };
    this._afterInit();
});
appdb.templates.Repeat = appdb.ExtendClass(appdb.TemplateControl,"appdb.templates.Repeat",function(o){
    this.preRender = function(d){
        var list = this.propertyData.list,spl=this.propertyData.splitter;
         if(typeof list === "string"){
            list = this.findData(d,list);
        }
        if(list){
            if($.isArray(list)===false){
                list = [list];
            }
            if(list.length===1 && typeof list[0] === "string" && spl){
                list = list[0].split(spl);
            }
        }
        if(typeof list === "undefined" || list.length===0){
            if(this.properties.empty.isSet===true){
                var e = this.properties.empty.getter();
                e.render(d);
                $(this.content).empty().append($(e.content));
            }
            return false;
        }
        this.propertyData.list = list;
        return true;
    };
    this.doRender = function(d){
        var p = this.propertyData,list = p.list , i, len = list.length, header = p.header, footer=p.footer, item = p.item,sep = p.seperator,l,tolower = p.tolower,cont;
        cont = this.propertyData.container || this.content;
        cont = $(cont);
        $(this.content).empty();
        
        if(header){
            header.render(d);
            $(this.content).append($(header.content));
        }
        for(i=0; i<len; i+=1){
            l = list[i];
            if(typeof l === "string" && tolower===true){
                l = l.toLowerCase();
            }
            d["_"] = l;
            item.render(d);
            var ht = $(item.content).clone(true);
            $(cont).append($(ht));
            if(sep && i!==(len-1)){
                sep.render(d);
                $(cont).append($(sep.content).clone());
            }
        }
        if(this.propertyData.container){
            $(this.content).append(cont);
        }
        if(footer){
         footer.render(d);
         $(this.content).append($(footer.content));
        }
    };

});
appdb.templates.View = appdb.ExtendClass(appdb.TemplateControl,"appdb.templates.View",function(o){
    this.view = null;
    this.viewData = null;
    this.preRender = function(d){
        var p;
        if(this.view!==null){

            this.view.destroy();
            this.view = null;
        }
        p = this.propertyData.type;
            p = appdb.FindNS(p);
            if(p===null){
                return false;
            }
        this.view = new p({content: this.content,container:this.content});
        p = this.propertyData.data;
        if(p){
            this.viewData = this.findData(d,p);
        }else{
            this.viewData = d;
        }
        
        return true;
    };
    this.doRender = function(d){
        this.view.render(this.viewData);
    };
});
appdb.templates.Case = appdb.ExtendClass(appdb.TemplateControl, "appdb.templates.Case", function(o){
    this.preRender = function(d){

    };
    this.doRender = function(d){

    };
});
appdb.templates.Switch = appdb.ExtendClass(appdb.TemplateControl,"appdb.templates.Switch",function(o){
    this.preRender = function(d){
        var i=(cases.length-1),cases = this.propertyData["case"].getter();
        
    };
    this.doRender = function(d){

    };
});
appdb.TemplateAction = appdb.DefineClass("appdb.TemplateAction", function(o){
    this.name = o.name || "";
    this.owner = o.owner || null;
    this.exec = function(){

    };
    this._init = function(){

    };
    this._init();
});

appdb.TemplateFactory = appdb.DefineClass("appdb.TemplateFactory",function(o){
    this.type = appdb.TemplateControl;
    this.properties = {};
	this.events = {};
    this.attributes = {};
    this.controls = {};
    this.getName = function(){
        return "";
    };
    this.hasBody = true;
    this.contentType = "span";
    this._initDefaultProperties = function(){
        this.properties = {
            id:{dataType: "text",defaultValue:""},
            visible:{dataType:"bool",defaultValue:true},
            runat : {dataType :"text",defaultValue:"client"},
            contentType : {dataType : "text",defaultValue : null}			
        };
    };
	this._initDefaultEvents = function(){
		this.events = {
			click : {defaultValue:"return true;"},
			mouseover : {defaultValue:"retrun true;"},
			mouseleave : {defaultValue:"return true;"}
		};
	};
    this._initDefaultControls = function(){
        this.controls = {};
    };
    this._extendProperties = function(props){
        var i,res = {},p;
        for(i in this.properties){
            res[i] = $.extend({},this.properties[i]);
            res[i].isSet = false;
            p = (props[i])?props[i]:null;
            if(p){
                res[i].name = i;
                res[i].value = p;
                res[i].isSet = true;
            }
        }
        return res;
    };
	this._extendEvents = function(evs){
		var i,res = {},p;
        for(i in this.events){
            res[i] = $.extend({},this.events[i]);
            res[i].isSet = false;
            p = (evs[i])?evs[i]:null;
            if(p){
                res[i].name = i;
                res[i].value = p;
                res[i].isSet = true;
            }
        }
        return res;
	};
    this.beforeCreate = function(co){
        return co;
    };
    this.afterCreate = function(inst){
        return inst;
    };
    this.doCreate = function(co){
        co.attributes = $.extend(co.attributes ,this.attributes || {});
        co.properties = this._extendProperties(co.properties || {});
		co.events = this._extendEvents(co.events || {});
        return new this.type(co);
    };
    this.create = function(co){
        var res = null;
        co = this.beforeCreate(co);
        res = this.doCreate(co);
        res = this.afterCreate(res);
        return res;
    };
    this._init = function(){
        o = o || {};
        this.type = o.type || appdb.TemplateControl;
        this._initDefaultProperties();
		this._initDefaultEvents();
        this._initDefaultControls();
    };
    this._init();
});
appdb.templatefactories = {};
appdb.templatefactories.DataItemFactory = appdb.ExtendClass(appdb.TemplateFactory, "appdb.templatefactories.DataItemFactory", function(o){
    this._initDefaultProperties = function(){
        this.properties = $.extend(this.properties, {
            datamember : {dataType : "text" , defaultValue : ""},
            empty : {dataType: "object", defaultValue: "", type : "content"},
			transform : {dataType: "text",defaultValue : ""}
        });
    };
    this._init();
});
appdb.templatefactories.LinkFactory = appdb.ExtendClass(appdb.TemplateFactory,"appdb.templatefactories.LinkFactory",function(o){
    this._initDefaultProperties = function(){
        this.properties = $.extend(this.properties, {
            href : {dataType :"text", defaultValue: "#"},
            target : {dataType : "text", defaultValue: "_blank"},
            click : {dataType : "text", defaultValue: ""}
        });
    };
    this.contentType = "a";
    this._init();
});
appdb.templatefactories.ImageFactory = appdb.ExtendClass(appdb.TemplateFactory,"appdb.templatefactories.ImageFactory",function(o){
    this._initDefaultProperties = function(){
       this.properties = $.extend(this.properties,{
          src : {dataType : "text" , defaultValue : null},
          alt : {dataType : "text" , defaultValue : ""},
          title : {dataType : "text" , defaultValue : ""},
          loader : {dataType : "text" , defaultValue : ""},
          renderon : {dataType : "boolean", defaultValue : false}
       });
    };
    this.contentType = "img";
    this._init();
});
appdb.templatefactories.EnumFactory = appdb.ExtendClass(appdb.templatefactories.DataItemFactory,"appdb.templatefactories.EnumFactory",function(o){
   this._initDefaultProperties = function(){
       this.properties = $.extend(this.properties,{
          provider : {dataType : "text" , defaultValue : "#"},
          nullValue : {dataType : "text", defaultValue : ""},
          keyname : {dataType : "text", defaultValue : ""},
          valuename : {dataType : "text", defaultValue : ""}
       });
    };
    this._init();
});
appdb.templatefactories.RepeatFactory = appdb.ExtendClass(appdb.TemplateFactory,"appdb.templatefactories.RepeatFactory",function(o){
    this._initDefaultProperties = function(){
      this.properties = $.extend(this.properties,{
         list : {dataType : "array", defaultValue:[]},
         header : {dataType : "object", defaultValue : "", type : "content"},
         footer : {dataType : "object", defaultValue : "", type : "content"},
         item : {dataType : "object", defaultValue : "", type : "content"},
         container : {dataType : "text",defaultValue : ""},
         empty : {dataType: "object", defaultValue: "", type : "content"},
         seperator : {dataType : "object", defaultValue : "", type : "content"},
         splitter : {dataType : "text"},
         tolower : {dataType : "bool",defaultValue : false}
      });
    };
    this._init();
});
appdb.templatefactories.CaseFactory = appdb.ExtendClass(appdb.TemplateFactory,"appdb.templatefactories.CaseFactory",function(o){
    this._initDefaultProperties = function(){
      this.properties = $.extend(this.properties,{
         "value" : {dataType : "text", defaultValue:""}
      });
    };
    this._init();
});
appdb.templatefactories.SwitchFactory = appdb.ExtendClass(appdb.TemplateFactory,"appdb.templatefactories.SwitchFactory",function(o){
    this._initDefaultProperties = function(){
      this.properties = $.extend(this.properties,{
         "case" : {dataType : "array", defaultValue:[],type : "case"},
         "default" : {dataType : "object", defaultValue : "", type : "content"},
         tolower : {dataType : "bool",defaultValue : false}
      });
    };
    this._init();
});
appdb.templatefactories.ViewFactory = appdb.ExtendClass(appdb.TemplateFactory,"appdb.templatefactories.ViewFactory",function(o){
    this._initDefaultProperties = function(){
      this.properties = $.extend(this.properties,{
         "type" : {dataType : "text", defaultValue:""},
         "data" : {dataType : "text" ,defaultValue:null}
      });
    };
    this._init();
});
appdb.TemplateManager.register({name : "content", template : new appdb.TemplateFactory({type : appdb.TemplateControl})});
appdb.TemplateManager.register({name : "data", template : new appdb.templatefactories.DataItemFactory({type : appdb.templates.DataItem})});
appdb.TemplateManager.register({name : "link", template : new appdb.templatefactories.LinkFactory({type : appdb.templates.Link})});
appdb.TemplateManager.register({name : "image", template : new appdb.templatefactories.ImageFactory({type : appdb.templates.Image})});
appdb.TemplateManager.register({name : "enum", template : new appdb.templatefactories.EnumFactory({type : appdb.templates.Enum})});
appdb.TemplateManager.register({name : "repeat", template : new appdb.templatefactories.RepeatFactory({type : appdb.templates.Repeat})});
appdb.TemplateManager.register({name : "view", template : new appdb.templatefactories.ViewFactory({type : appdb.templates.View})});
appdb.TemplateManager.register({name : "case", template : new appdb.templatefactories.CaseFactory({type : appdb.templates.Case})});
appdb.TemplateManager.register({name : "switch", template : new appdb.templatefactories.SwitchFactory({type : appdb.templates.Switch})});

appdb.TemplateParser = function(o){
    this.content = null;
    this.container = null;
    this.nscontrol = o.nscontrol || "";
    this.nsproperty = o.nsproperty || "";
	this.nsevent = o.nsevent || "";
    this.subtractNS = function(p,ns){
        var name = p.nodeName.toLowerCase();
        if(p.prefix || p.scopeName){
            if((p.prefix || p.scopeName)===ns){
                return name;
            }
        }else if(name.indexOf(ns+":")>-1){
            return name.substring(ns.length+1,name.length);
        }
        return null;
    };
	this.collectEvents = function(e,evs){
		var i, ps = e.attributes, len = ps.length, p, pname, res = {}, ns = this.nsevent,f,prop,fc;
        for(i=0; i<len; i+=1){
            p = ps[i];
            pname = this.subtractNS(p,ns);
            if(pname!==null){
                prop = evs[pname];
                if(prop){
                    res[pname] = p.nodeValue;
                }
            }
        }
        //remove properties from tag
        for(i in res){
            e.removeAttribute(ns+":"+i);
        }
        return res;
	};
    this.collectProperties = function(e,props){
        var i, ps = e.attributes, len = ps.length, p, pname, res = {}, ns = this.nsproperty,f,prop,fc;
        for(i=0; i<len; i+=1){
            p = ps[i];
            pname = this.subtractNS(p,ns);
            if(pname!==null){
                prop = props[pname];
                if(prop){
                    if(prop.type){
                        f = appdb.TemplateManager.getFactory(prop.type);
                        fc = $(document.createElement(f.contentType));
                        if(p.nodeValue && p.nodeValue[0]==="#"){
                            $(fc).html($(this.content).find(p.nodeValue).first().html());
                        }else{
                            $(fc).html(p.nodeValue);
                        }
                        res[pname] = f.create({attributes : {}, properties : {}, controls : [] , content: fc, body:fc});
                    }else{
                        res[pname] = p.nodeValue;
                    }
                }
            }
        }
        //remove properties from tag
        for(i in res){
            e.removeAttribute(ns+":"+i);
        }
        return res;
    };
    this.collectNestedProperties = function(c,props){
        var i, e, ns = this.nsproperty, p, res = {}, mem;
        for(i in props){
            e = $(c).find(ns+"\\:" + i);
            if(e.length===0){
                continue;
            }
            e = e[0];
            mem = $(e).clone(true);
            $(e).detach();
            p = this.parseControl(mem.context,props[i].type);
            if(p!==null){
             res[i] = p;
            }
        }
        return res;
    };
    this.collectAttributes = function(e){
        var i, at = e.attributes, len = at.length, a, res = {};
        for(i=0; i<len; i+=1){
            a = at[i];
            res[a.nodeName] = a.nodeValue;
        }
        return res;
    };

    this.parseControl = function(e,name){
        var i, a, evs, p, np, ch, b = "", c, ns = this.nscontrol, f, tp, res , ct , mem ,h;
        f = appdb.TemplateManager.getFactory(name);
        if(f===null){
            return null;
        }
        p = this.collectProperties(e,f.properties);
        a = this.collectAttributes(e);
		evs = this.collectEvents(e,f.events);
        np = this.collectNestedProperties(e, f.properties);
        for(i in np){
            p[i] = np[i];
        }
        ct = document.createElement(f.contentType);
        $(ct).insertBefore($(e));
        h = $(e).html();
        $(ct).html(h);
        $(e).detach();
        mem = $(ct).clone(true);
        if($.trim(h)!==""){
            tp = new appdb.TemplatePane({content : mem, nscontrol: ns, nsproperty : this.nsproperty});
            ch = tp.controls;
            b = mem;
        }
        ch = ch || [];
        return f.create({attributes : a, properties : p, events : evs, controls : ch , body : b , content : ct});
    };
    this.run = function(cont){
        var e, cnt, res = [], name, nsp = this.nsproperty, nsc = this.nscontrol, c = cont || $(this.content);
        while(true){
            e = $(c).find("["+nsp+"\\:runat='client']");
            if(e.length===0){
                break;
            }
            e = e[0];
            name = this.subtractNS(e,nsc);
            cnt = this.parseControl(e,name);
            if(cnt!==null){
                res[res.length] = cnt;
            }else{
				$(e).remove();
			}
        }
        return {controls: res, content : c};
    };
    this._init = function(){
        this.content = o.content || document;
        this.nscontrol = o.nscontrol;
        this.nsproperty = o.nsproperty;
        if(typeof this.nscontrol === "undefined" || typeof this.nsproperty === "undefined"){
            var ns = appdb.findTemplateprefix(this.content);
            this.nscontrol = this.nscontrol || ns.control;
            this.nsproperty = this.nsproperty || ns.property;
        }
    };
    this._init();
};
appdb.findTemplateprefix = function(container){
    var i,
    nscontrol,
    nsproperty,
	nsevent,
    atrs = $(container)[0].attributes,
    len = atrs.length,
    a=null,
    dt = appdb.TemplateManager.NSTemplate,
    da = appdb.TemplateManager.NSTemplateAttribute,
	de = appdb.TemplateManager.NSTemplateEvent;
    for(i=0; i<len; i+=1){
        a = atrs[i];
        if(a.nodeName.length<6){
            continue;
        }
        if(a.nodeName.substring(0,6)==="xmlns:"){
            if(a.nodeValue===dt){
                nscontrol = a.nodeName.substring(6,a.nodeName.length);
                continue;
            }
            if(a.nodeValue===da){
                nsproperty = a.nodeName.substring(6,a.nodeName.length);
                continue;
            }
			if(a.nodeValue===de){
                nsevent = a.nodeName.substring(6,a.nodeName.length);
                continue;
            }
        }
    }
    return  {control : nscontrol , property : nsproperty, event : nsevent};
};
appdb.TemplatePane = function(o){
    this.parser = null;
    this.container = null;
    this.content = null;
    this.originalContent = null;
    this.controls = [];
    this.nsproperty = null;
	this.nsevent = null;
    this.nscontrol = null;
    this.destroy = function(){
       
    };
    this.render = function(d,onrender){
        if(this.parentContent){
            $(this.parentContent).css({visibility:"hidden"});
        }
        this.hide();
        
        var _this = this;
        var i, len = _this.controls.length;
            for(i=0; i<len; i+=1){
                _this.controls[i].render(d);
            }

        setTimeout(function(){
            if(_this.parentContent){
                $(_this.parentContent).css({visibility:"visible"});
            }
            if(onrender){
                onrender();
            }
        },1);
    };
    this.show = function(){
        $(this.content).css({visibility:"visible"});
    };
    this.hide = function(){
        $(this.content).css({visibility:"hidden"});
    };
    this._init = function(){
        this.container = o.container;
        this.originalContent = $(o.content).clone();
        this.content = o.content;
        this.nsproperty = o.nsproperty;
        this.nscontrol = o.nscontrol;
		this.nsevent = o.nsevent;
        if(typeof this.nscontrol === "undefined" || typeof this.nsproperty === "undefined"){
            var ns = appdb.findTemplateprefix(this.content);
            this.nscontrol = this.nscontrol || ns.control;
            this.nsproperty = this.nsproperty || ns.property;
			this.nsevent = this.nsevent || ns.event;
        }
        if(o.partialId){
            this.parser = new appdb.TemplateParser({content : $(this.content).find("#"+o.partialId).first(), nscontrol : this.nscontrol ,nsproperty:this.nsproperty, nsevent : this.nsevent});
        }else{
            this.parser = new appdb.TemplateParser({content : $(this.content), nscontrol : this.nscontrol ,nsproperty:this.nsproperty, nsevent : this.nsevent});
        }
        var res = this.parser.run();
        this.controls = res.controls;
        this.content = res.content;
    };
    this._init();
    
};