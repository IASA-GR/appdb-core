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

	var imgarchive; 
	var userAppPrivs;
	var editDocDlg;
	var ratingReport;
	var reportCommentAbuseHtml = '<span style="float: right;" class="commentActions"><span class="reportCommentAbuse"><a href="#" title="Submit a report about this comment">Report abuse</a></span></span>';
	var managedSciCons = null;//TODO: replace with managed code
	var managedAppUrls = null;//TODO: replace with managed code
	var managedAppUrlsEditor = null;//TODO: replace with managed code
	var managedAppCategories = null;//TODO: replace with managed code
	var managedAppCategoriesEditor = null;//TODO replace with managed code
	var managedDisciplines = null;
	var managedDisciplinesEditor = null;//TODO replace with managed code
	var managedCustomMiddlewares = null; //TODO replace with managed code
	
	function loadImageFailback(i, e, a) {
		appdb.debug('Falling back to nominal resource for image ' + i + ' (performance issue)');
		if ( $.browser.msie ) {
			appdb.debug("Load image failback not supported in IE");
			return null;
		}
		if ( typeof a === "undefined" ) a = 'src';
		if ( typeof e === "undefined" ) {
			var data = $.ajax({
				"type": "GET",
				"url": "/images/" + i,
				"async": false,
				"dataType": "image/png"
			}).responseText;
			if (data) {
				var ext = ((/[.]/).exec(i)) && ((/[^.]+$/).exec(i)[0]) || '';
				var tmp = "data:";
				switch(ext.toLowerCase()) {
					case 'gif' :
						tmp += "image/gif;base64,";
						break;
					case 'png' :
						tmp += "image/png;base64,";
						break;
					case 'jpg':
					case 'jpeg':
						tmp += "image/jpeg;base64,";
						break;
				}
				tmp += encodeURI(appdb.utils.base64.encode(data));
				return tmp;
			} else {
				return null;
			}
		} else {
			$(e).attr(a, "/images/" + i);
		}
		return null;
	}
	function loadImage(i, e, a) {
		if ( Left(i, 5) === "data:" ) {
			if ( typeof e === "undefined" ) { 
				return i;
			} else {
				if ( typeof a === "undefined" ) {
					$(e).attr("src", i);
				} else {
					$(e).attr(a, i);
				}
			}
		}
		if ( Left(i, 1) == '/' ) i = i.substr(1);
		if ( Left(i, 7) == 'images/' ) i = i.substr(7);
		if ( typeof imgarchive === "undefined" ) {
			try {
				imgarchive = new ZipLoader(appdb.config.endpoint.base + '/res/zip?f=images/appdbimgs.zip');
			} catch (err) {
				appdb.debug('Could not load image archive');
				return loadImageFailback(i, e, a);
			}
		}
		
		if ( typeof a === "undefined" ) a = 'src';
		try {
			if ( typeof e === "undefined" ) {
				return encodeURI(imgarchive.loadImage(appdb.config.endpoint.base + "/res/zip?f=images/appdbimgs.zip://" + i));
			} else {
				$(e).attr(a, encodeURI(imgarchive.loadImage(appdb.config.endpoint.base + "/res/zip?f=images/appdbimgs.zip://" + i)));
				return null;
			}
		} catch (err) {
			appdb.debug('Could not load image ' + i + ' from image archive');
			return loadImageFailback(i, e, a);
		}
	}

	function showCountry(cid) {
		var name="Country", i, c = regionalData, len = (((c)?c.length:0)||0), id=''+cid;
		for(i=0; i<len; i+=1){
			if(c[i].id===id){
				name = c[i].val();
				break;
			}
		}
		appdb.views.Main.showApplicationCountry({flt: '=country.id:'+cid},{isBaseQuery:true,mainTitle:name,filterDisplay:"Search in "+name+"..."});
	}

	function personImage(p) {
        var ishttp = Boolean(appdb.config.https) && true;
		if ( p.image ) {
            if(ishttp && p.image.substr(4,1)===":") {
				p.image = "https" + p.image.substr(4,p.image.length);
			}else if(p.image.substr(4,1)==="s") {
                p.image= "https:" + p.image.substr(4,p.image.length);
			}
			return p.image+"&req="+encodeURIComponent(p.lastUpdated);
		} else {
            var gender = ""+p.gender;
            gender = gender.toLowerCase();
            switch (p.gender) {
                case "robot":
                    return appdb.config.images.robot;
                    break;
                default:
                    return appdb.config.images.person;
            }
        }
	}

	function getLogoForCategory(p){
		p.category = $.isArray(p.category)?p.category:[p.category];
		for(c = 0; c<p.category.length; c++) {
			if (p.category[c].primary && p.category[c].primary === "true") {
				p.primaryCategory = p.category[c].val().toLowerCase();
				break;
			}
		}
		if( p.primaryCategory ){
			return appdb.config.images[p.primaryCategory];
		}else if( $.trim(p.metatype) !== "" ){
			switch($.trim(p.metatype)){
				case "1":
					return appdb.config.images["virtual appliances"];
				case "2":
					return appdb.config.images["software appliances"];
				case "0":
				default:
					return appdb.config.images.applications;
			}
		}
		return ((p.primaryCategory)?appdb.config.images[p.primaryCategory]:appdb.config.images.applications);
	}
	function appLogo(p) {
        var ishttp = Boolean(appdb.config.https) && true;
		if ( p.logo ) {
            if(ishttp && p.logo.substr(4,1)===":") {
                p.logo = "https" + p.logo.substr(4,p.logo.length);
            } else if(p.logo.substr(4,1)==="s") {
                p.logo = "http:" + p.logo.substr(4,p.logo.length);
            }
			return "/images/applogo/100x100/app-logo-" + p.id + ".jpg?req=" + encodeURIComponent(p.lastUpdated);
			//return p.logo+"&size=1&req="+encodeURIComponent(p.lastUpdated);
		}else {
			if( $.trim(p.metatype) === "2"){
				return appdb.config.images["software appliances"];
			} else if(p.category){
				return getLogoForCategory(p);
			} else if (p.tool == "true") {
				return "/images/tool.png";
			} else {
				return "/images/app.png";
			}
		}
	}

	function hasAppPriv(id) {
		if ( userAppPrivs === null ) return false;
		if ( userAppPrivs === undefined ) return false;
		for(i=0;i<userAppPrivs.length;i++) {
			if ( userAppPrivs[i] == id ) return true;
		}
		return false;
	}
	function reorderApplicationCategories(data){
		var vapps = $.grep(data, function(e){
			return ($.trim(e.parentid) === "34");
		});
		var restapps = $.grep(data, function(e){
			return !($.trim(e.parentid) === "34");
		});
		vapps = vapps.sort(function(a, b){
			var aa = (typeof a.val === "function")?a.val():"";
			var bb = (typeof b.val === "function")?b.val():"";
			if( bb > aa ) return -1;
			if( bb < aa ) return 1;
			return 0;
		});
		restapps = restapps.concat(vapps);
		return restapps;
	};
	var statusData;
	var mwData;
	var countryData;
	var regionalData;
	var voData;
	var proglangData;
	var domainData;
	var subdomainData;
	var peopleData;
	var contactTypeData;
	var contactTypeDataObject;
	var contactTypeDataRaw;
	var roleData;
	var categoriesData;
	var navPaneBuilt = false;

	function buildNavPane() {
		if ( ! navPaneBuilt ) {
			if ( (typeof categoriesData !== "undefined") &&
				 (typeof domainData !== "undefined") &&
				 (typeof roleData !== "undefined")) {
				buildAppNavPane(categoriesData);
				buildPplNavPane(roleData);
				buildVOsNavPane(domainData);
				navPaneBuilt = true;
			} else {
				appdb.debug('Delaying building of navpane till data is fetched');
				setTimeout(function(){buildNavPane();}, 500);
			}
		}
	}

	function buildAppNavPane(cats) {
		return;
	}

	function buildPplNavPane(roles) {
		var li = $("#pplalllink").parent();
		roles = eval('(' + roles + ')');
		if ( roles.ids.length>0 ) {
			for (var i=roles.ids.length-1; i>=0; i--) {
				$('<li><a href="#" onclick="appdb.views.Main.showPeopleByRole(' + String(roles.ids[i]) + ');" data-href="/browse/people/role/'+String(roles.ids[i])+'">' + roles.vals[i] + '</a></li>').insertAfter(li);
			}
		} else appdb.debug('WARNING: no person role data');
		
		var ul = $(".menu.people.groups > .list.groups");
		var groups = appdb.model.StaticList.AccessGroups; 
		var allowedgroups = [-1,-2,-3];//Allow Admins, Managers, NILs
		if( groups ){
			for (var j=0; j<groups.length; j+=1) {
				if( $.inArray( parseInt(groups[j].id), allowedgroups) > -1  ){
					$(ul).append($('<li><a href="#" onclick="appdb.views.Main.showPeopleByGroup(' + groups[j].id + ');" data-href="/browse/people/accessgroup/'+groups[j].id+'">' + groups[j].val() + '</a></li>'));
				}
			}
		}
	}

	function buildVOsNavPane(domains) {
		var li = $("#voalllink");
		domains = eval('(' + domainData + ')');
		if ( domains.ids.length>0 ) {
			for (var i=domains.ids.length-1; i>=0; i--) {
				$('<li><a href="#" onclick="appdb.views.Main.showVOs({flt: \'+=&discipline.id:' + domains.ids[i] + '\'},{mainTitle: \'' + domains.vals[i] + '\',isBaseQuery:true,filterDisplay: \'Search in ' + domains.vals[i] + '...\'});">' + domains.vals[i] + '</a></li>').insertAfter(li);
			}
		} else appdb.debug('WARNING: no discipline data');
	}
	function brokerAvailableRequests(){
		var available = [{
			"id": "dom",
			"resource": "disciplines",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
		},{
			"id": "cat",
			"resource": "applications/categories",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "ord"}]
		},{
			"id": "rol",
			"resource": "people/roles",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "ord"}]
		},{
			"id": "agroups",
			"resource": "accessgroups",
			"method": "GET"
		},{
			"id": "reg",
			"resource": "regional",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
		},{
			"id": "reflppl",
			"resource": "people/filter/reflect",
			"method": "GET"
		},{
			"id": "reflapps",
			"resource": "applications/filter/reflect",
			"method" : "GET"
		},{
			"id": "reflvos",
			"resource": "vos/filter/reflect",
			"method": "GET"
		},{
			"id": "reflsites",
			"resource": "sites/filter/reflect",
			"method": "GET"
		},{
			"id": "swlogistics",
			"resource": "applications/logistics",
			"method": "GET",
			"param": [{name:"flt",val:"&"}]
		},{
			"id": "mws",
			"resource": "middlewares",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
		},{
			"id": "vos",
			"resource": "vos",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
		},{
			"id": "sta",
			"resource": "applications/statuses",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		},{
			"id": "proglangs",
			"resource": "applications/languages",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		},{
			"id": "oses",
			"resource": "applications/oses",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		},{
			"id": "archs",
			"resource": "applications/archs",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		},{
			"id": "hypervs",
			"resource": "applications/hypervisors",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		},{
			"id": "vmiformats",
			"resource": "applications/vmiformats",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		},{
			"id": "licenses",
			"resource": "applications/licenses",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "title"}]
		},{
			"id": "ppl",
			"resource": "people",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
		},{
			"id": "cnt",
			"resource": "people/contacttypes",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "description"}]
		},{
			"id": "cntxformats",
			"resource": "contextualization/formats",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		},{
			"id": "exchformats",
			"resource": "datasets/exchangeformats",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		},{
			"id": "conntypes",
			"resource": "datasets/interfaces",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		},{
			"id": "reltypes",
			"resource": "entity/relationtypes",
			"method": "GET"
		}];
		
		return available;
	}
	function buildBrokerRequest(requests, usecache){
		requests = requests || [];
		requests = $.isArray(requests)?requests:[requests];
		usecache = (typeof usecache === "boolean")?usecache:false;
		var reqs = {cached: {}, requests: requests};
		if( usecache === false ) return reqs;
		var res = [], i, arr = reqs.requests, len = reqs.requests.length;
		for( i = 0; i < len; i += 1 ) {
			var tmp = appdb.utils.localResourceCache.getItem(arr[i].resource);
			if( tmp !== null ){
				if( $.isArray(tmp) === true ){
					$.each(tmp, function(ii,ee){
						if(typeof ee.val !== "function" ){
							ee.val = function(){return ee;};
						}
					});
				}
				reqs.cached[arr[i].id] = tmp;
				res.push(i);
			}
		}
		for(i=(res.length-1); i>=0; i-=1){
			reqs.requests.splice(i,1);
		}
		return reqs;
	}
	function brokerResponseDispatch(id,data,requests){
		data = data || [];
		data = $.isArray(data)?data:[data];
		
		var ids= [], vals = [], j=0, dlen = data.length;
		if ( id === "ppl" ) {
			delete peopleData;
			for (j=0; j< dlen; j++) {
				ids.push(data[j].id);
				vals.push(data[j].val());
			}
			peopleData = JSON.stringify({"ids": ids, "vals": vals}).replace(/'/g,"\\'").replace(/"/g,"'");
		} else if ( id === "cat" ) {
			delete categoriesData;
			appdb.model.StaticList.Categories = data;
			for (j=0; j<dlen; j++) {
				ids.push(data[j].id);
				vals.push(data[j].val());
			}
			categoriesData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
			data = reorderApplicationCategories(data);
			appdb.model.ApplicationCategories.setLocalData({"count":dlen, "datatype":"category","pagelength":dlen,"pageoffset":0,"type":"list","category": data, "version": "1.0"});
			
		} else if ( id === "dom" ) {
			delete domainData;
			appdb.model.StaticList.Disciplines = data;
			appdb.model.StaticList.Subdisciplines = [];
			for (j=0; j<dlen; j++) {
				ids.push(data[j].id);
				vals.push(data[j].val());
			}
			domainData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
		} else if ( id === "vos" ) {
			delete voData;
			appdb.model.StaticList.VOs = data;
			for (j=0; j<dlen; j++) {
				ids.push(data[j].id);
				vals.push(data[j].val());
			}
			voData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
		} else if ( id === "reg" ) {
			delete countryData;
			delete regionalData;
			data = ( data.length > 0 )?data[0]:{"country":[],"regional":[],"provider":[]};
			data["datatype"]="regional";
			data["type"]="list";
			data.country = data.country || [];
			data.regional = data.regional || [];
			data.provider = data.provider || [];
			data.country = $.isArray(data.country)?data.country:[data.country];
			data.regional = $.isArray(data.regional)?data.regional:[data.regional];
			data.provider = $.isArray(data.provider)?data.provider:[data.provider];
			data.count = data.country.length + data.regional.length + data.provider.length;
			appdb.model.StaticList.Countries = data.country;
			for (j=0; j<data.country.length; j++) {
				ids.push(data.country[j].id);
				vals.push( (data.country[j].val)?data.country[j].val():"");
			}
			countryData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
			regionalData = data.country;
			appdb.model.Regional.setLocalData(data);
			populateFlagImages();
		} else if ( id === "mws" ) {
			delete mwData;
			appdb.model.StaticList.Middlewares = data;
			for (j=0; j<dlen; j++) {
				ids.push(data[j].id);
				vals.push(data[j].val());
			}
			mwData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
			appdb.model.Middlewares.setLocalData({count:dlen, "type":"","datatype":"middleware",pagelength:dlen,middleware:data});
		} else if ( id === "sta" ) {
			delete statusData;
			appdb.model.StaticList.Statuses = data;
			for (j=0; j<dlen; j++) {
				ids.push(data[j].id);
				vals.push(data[j].val());
			}
			statusData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
		} else if ( id === "cnt" ) {
			delete contactTypeData;
			delete contactTypeDataObject;
			for (j=0; j<dlen; j++) {
				ids.push(data[j].id);
				vals.push(data[j].type);
			}
			contactTypeDataObject = {"ids": ids, "vals": vals};
			contactTypeData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
		} else if ( id === "rol" ) {
			delete roleData;
			appdb.model.StaticList.Roles = data;
			for (j=0; j<dlen; j++) {
				ids.push(data[j].id);
				vals.push(data[j].type);
			}
			roleData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
		} else if ( id === "reflapps" ) {
			data = (data.length > 0 )?data[0]:{};
			new appdb.utils.filterFields().setLocalList(data,"applications", "person");
			new appdb.utils.filterFields().setLocalList(data,"applications", "application");
		} else if ( id === "reflppl" ) {
			data = (data.length > 0 )?data[0]:{};
			new appdb.utils.filterFields().setLocalList(data,"people", "person");
			new appdb.utils.filterFields().setLocalList(data,"people", "application");
		} else if ( id === "reflvos" ) {
			data = (data.length > 0 )?data[0]:{};
			new appdb.utils.filterFields().setLocalList(data, "vos", "vo");
		} else if ( id === "reflsites" ){
			data = (data.length > 0 )?data[0]:{};
			new appdb.utils.filterFields().setLocalList(data, "sites", "site");
			new appdb.utils.filterFields().setLocalList(data, "sites", "application");
			new appdb.utils.filterFields().setLocalList(data, "sites", "vo");
		} else if ( id === "tags" ) {
			data = (data.length > 0 )?data[0]:{};
			appdb.model.Tags.setLocalData(data);
		}else if ( id === "swlogistics" ) {
			data = (data.length>0)?data[0]:data;
			appdb.model.StaticList.SoftwareLogistics = data;
		}else if( id === "proglangs" ) {
			delete peopleData;
			appdb.model.StaticList.ProgLangs = data;
			for (j=0; j<dlen; j++) {
				ids.push(data[j].id);
				vals.push(data[j].val());
			}
			proglangData = JSON.stringify({"ids": ids, "vals": vals}).replace(/'/g,"\\'").replace(/"/g,"'");
		}else if( id === "oses" ){
			data = ( data.length > 0 )?data[0]:{"os":[],"osfamily":[]};
			appdb.model.StaticList.Oses = data.os || [];
			appdb.model.StaticList.OsFamilies = data.osfamily || [];
		}else if( id === "archs" ){
			appdb.model.StaticList.Archs = data;
		}else if( id === "hypervs" ){
			appdb.model.StaticList.Hypervisors = data;
		}else if( id === "vmiformats" ){
			appdb.model.StaticList.ImageFormats = data;
		}else if( id === "licenses" ){
			appdb.model.StaticList.Licenses = data;
			if( appdb.model.StaticList.Licenses.length > 0 && appdb.model.StaticList.Licenses[0].id == "0" ){
				appdb.model.StaticList.Licenses = appdb.model.StaticList.Licenses.slice(1);
			}
		}else if( id === "agroups" ){
			appdb.model.StaticList.AccessGroups = data || [];
		}else if( id === "vaproviders"){
			appdb.model.StaticList.VAProviders = data || [];
		}else if( id === "reltypes"){
			appdb.model.StaticList.RelationTypes = data || [];
		}else if( id === "cntxformats" ) {
			appdb.model.StaticList.ContextFormats = data || [];
		}else if( id === "exchformats" ) {
			appdb.model.StaticList.ExchangeFormats = data || [];
		}else if( id === "conntypes" ){
			appdb.model.StaticList.Interfaces = data || [];
		}
		
		$.each(requests, function(i,e){
			if( e.id === id ){
				if( id === "reg" ){
					data.country = appdb.utils.transformValData(data.country,false);
					data.provider = appdb.utils.transformValData(data.provider,false);
					data.regional = appdb.utils.transformValData(data.regional,false);
				}else if( id === "oses" ){
					data.os = appdb.utils.transformValData(data.os, false);
					data.osfamily = appdb.utils.transformValData(data.osfamily, false);
				}else if( id === "vaproviders" ){
					return;//do not store them
				}
				
				appdb.utils.localResourceCache.setItem(e.resource,data);
			}
		});
	}
	function refreshAPIData(usecache, callback, async) {
		usecache = (typeof usecache === "boolean")?usecache:false;
		callback = (typeof callback === "function")?callback:function(){};
		async = (typeof async === "boolean")?async:false;
		var broker = new appdb.utils.broker(async);
		var reqs = buildBrokerRequest(brokerAvailableRequests(),usecache);
		
		//load cached
		for(var r in reqs.cached){
			if( reqs.cached.hasOwnProperty(r) === false ) continue;
			if( r === "reg"){
				reqs.cached[r].country = appdb.utils.transformValData(reqs.cached[r].country,true);
				reqs.cached[r].provider = appdb.utils.transformValData(reqs.cached[r].provider,true);
				reqs.cached[r].regional = appdb.utils.transformValData(reqs.cached[r].regional,true);
			}else if ( r === "oses" ){
				reqs.cached[r].os = appdb.utils.transformValData(reqs.cached[r].os, true);
				reqs.cached[r].osfamily = appdb.utils.transformValData(reqs.cached[r].osfamily, true);
			}
			brokerResponseDispatch(r,reqs.cached[r],[]);
		}
		if(reqs.requests.length === 0 && usecache === true){
			callback();
			return;
		}
		//fetch uncached
		
		broker.request(reqs.requests);
		broker.fetch(function(e){
			var i, len = e.reply.length;
			for (i=0; i<len; i+=1) {
				var data, id = e.reply[i].id;
				if( id == "ppl" ) {
					data = e.reply[i].appdb.person;
				} else if ( id === "cat" ) {
					data = e.reply[i].appdb.category;
				} else if ( id === "dom" ) {
					data = e.reply[i].appdb.discipline;
				} else if ( id === "vos" ) {
					data = e.reply[i].appdb.vo;
				} else if ( id === "reg" ) {
					data = e.reply[i].appdb;
				} else if ( id === "mws" ) {
					data = e.reply[i].appdb.middleware;
				} else if ( id === "sta" ) {
					data = e.reply[i].appdb.status;
				} else if ( id === "cnt" ) {
					data = e.reply[i].appdb.contact;
				} else if ( id === "rol" ) {
					data = e.reply[i].appdb.role;
				} else if ( id === "reflapps" ) {
					data = e.reply[i].appdb;
				} else if ( id === "reflppl" ) {
					data = e.reply[i].appdb;
				} else if ( id === "reflvos" ) {
					data = e.reply[i].appdb;
				} else if ( id === "reflsites" ) {
					data = e.reply[i].appdb;
				} else if ( id === "tags" ) {
					data = e.reply[i].appdb;
				} else if ( id === "swlogistics" ) {
					data = e.reply[i].appdb.logistics;
				}else if( id === "proglangs" ) {
					data = e.reply[i].appdb.language;
				}else if( id === "oses" ){
					var oses = e.reply[i].appdb.os || [];
					oses = $.isArray(oses)?oses:[oses];
					var families = e.reply[i].appdb.osfamily || [];
					families = $.isArray(families)?families:[families];
					data = {"os": oses, "osfamily": families};
				}else if( id === "archs" ){
					data = e.reply[i].appdb.arch;
				}else if( id === "hypervs" ){
					data = e.reply[i].appdb.hypervisor;
				}else if( id === "vmiformats" ){
					data = e.reply[i].appdb.format;
				}else if( id === "licenses" ){
					data = e.reply[i].appdb.license;
				}else if( id === "agroups" ){
					data = e.reply[i].appdb.group;
				}else if( id === "vaproviders"){
					data = e.reply[i].appdb.provider;
				}else if( id === "reltypes"){
					data = e.reply[i].appdb.relationtype;
				}else if( id === "cntxformats" ) {
					data = e.reply[i].appdb.format;
				}else if( id === "exchformats" ){
					data = e.reply[i].appdb.exchangeformat;
				}else if( id === "conntypes" ){
					data = e.reply[i].appdb["interface"];
				}
				brokerResponseDispatch(id,data,reqs.requests);
			}
			setTimeout(function(){
				if( userID !== null) appdb.utils.LoadApplicationCategoriesInfo();
				callback();
			},100);
			
		});
	}
	function loadApplicationTags(){
		var broker = new appdb.utils.broker(true);
		broker.request([{
			"id": "tags",
			"resource": "applications/tags",
			"method": "GET"
		}]);
		broker.fetch(function(e){
			if( e && e.reply && e.reply.id === "tags"){
				data = e.reply.appdb || [];
				data = $.isArray(data)?data:[data];
				data = (data.length > 0 )?data[0]:{};
				appdb.model.Tags.setLocalData(data);
			}
		});
	}
	function loadVAProviders(){
		var broker = new appdb.utils.broker(true);
		broker.request([{
			"id": "vaproviders",
			"resource": "va_providers",
			"method": "GET",
			"param": [{"name": "listmode", "val": "details"}]
		}]);
		broker.fetch(function(e){
			if( e && e.reply && e.reply.id === "vaproviders"){
				e.reply.appdb.provider = e.reply.appdb.provider || [];
				appdb.model.StaticList.VAProviders = $.isArray(e.reply.appdb.provider)?e.reply.appdb.provider:[e.reply.appdb.provider];
			}
		});
	}
	(function loadAPIData(){
		var ver = appdb.utils.localCache.getItem("version");
		if( ver !== appdb.config.version){
			appdb.utils.localCache.clear();
		}
		appdb.utils.localCache.setItem("version",appdb.config.version);
		refreshAPIData(!appdb.utils.localResourceCache.isExpired(), function(){
			loadVAProviders();
			loadApplicationTags();
			setTimeout(function(){
				refreshAPIData(false, function(){
					appdb.pages.index.checkCategoriesUpdate();
				},true);
			},100);
		});
		setInterval(function(){refreshAPIData(false, function(){
			appdb.pages.index.checkCategoriesUpdate();
		});},3*60000);
	})();
	function oldaaa_refreshAPIData() {
		var broker = new appdb.utils.broker();
		broker.request({
			"id": "cat",
			"resource": "applications/categories",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "ord"}]
		}).request({
			"id": "dom",
			"resource": "disciplines",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
		}).request({
			"id": "rol",
			"resource": "people/roles",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "ord"}]
		}).request({
			"id": "reg",
			"resource": "regional",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
		}).request({
			"id": "reflppl",
			"resource": "people/filter/reflect",
			"method": "GET"
		}).request({
			"id": "reflapps",
			"resource": "applications/filter/reflect",
			"method" : "GET"
		}).request({
			"id": "reflvos",
			"resource": "vos/filter/reflect",
			"method": "GET"
		}).request({
			"id": "swlogistics",
			"resource": "applications/logistics",
			"method": "GET",
			"param": [{"flt":"&"}]
		}).request({
			"id": "mws",
			"resource": "middlewares",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
		}).request({
			"id": "vos",
			"resource": "vos",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
		}).request({
			"id": "sta",
			"resource": "applications/statuses",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		}).request({
			"id": "cou",
			"resource": "regional",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		}).request({
			"id": "proglangs",
			"resource": "applications/languages",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		}).request({
			"id": "oses",
			"resource": "applications/oses",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		}).request({
			"id": "archs",
			"resource": "applications/archs",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		}).request({
			"id": "hypervs",
			"resource": "applications/hypervisors",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		}).request({
			"id": "vmiformats",
			"resource": "applications/vmiformats",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "id"}]
		}).request({
			"id": "licenses",
			"resource": "applications/licenses",
			"method": "GET",
			"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "title"}]
		}).request({
			"id": "reltypes",
			"resource": "entity/relationtypes",
			"method": "GET"
		});
		if (userID !== null) {
			broker.request({
				"id": "ppl",
				"resource": "people",
				"method": "GET",
				"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "name"}]
			}).request({
				"id": "cnt",
				"resource": "people/contacttypes",
				"method": "GET",
				"param": [{"name": "listmode", "val": "listing"}, {"name": "orderby", "val": "description"}]
			});
		}
		broker.fetch(function(e) {
			var i, j;
			for (i=0; i<e.reply.length; i++) {
				var ids, vals;
				if ( e.reply[i].id === "ppl" ) {
					delete peopleData;
					ids = [];
					vals = [];
					if ( ! $.isArray(e.reply[i].appdb.person) ) e.reply[i].appdb.person = [e.reply[i].appdb.person];
					for (j=0; j<e.reply[i].appdb.person.length; j++) {
						ids.push(e.reply[i].appdb.person[j].id);
						vals.push(e.reply[i].appdb.person[j].val());
					}
					peopleData = JSON.stringify({"ids": ids, "vals": vals}).replace(/'/g,"\\'").replace(/"/g,"'");
				} else if ( e.reply[i].id === "cat" ) {
					delete categoriesData;
					ids = [];
					vals = [];
					if ( ! $.isArray(e.reply[i].appdb.category) ) e.reply[i].appdb.category = [e.reply[i].appdb.category];
					appdb.model.StaticList.Categories = e.reply[i].appdb.category;
					for (j=0; j<e.reply[i].appdb.category.length; j++) {
						ids.push(e.reply[i].appdb.category[j].id);
						vals.push(e.reply[i].appdb.category[j].val());
					}
					categoriesData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
					appdb.model.ApplicationCategories.setLocalData(e.reply[i].appdb);
					appdb.utils.LoadApplicationCategoriesInfo();
				} else if ( e.reply[i].id === "dom" ) {
					delete domainData;
					ids = [];
					vals = [];
					if ( ! $.isArray(e.reply[i].appdb.discipline) ) e.reply[i].appdb.discipline = [e.reply[i].appdb.discipline];
					if ( ! $.isArray(e.reply[i].appdb.subdiscipline) ) e.reply[i].appdb.subdiscipline = [e.reply[i].appdb.subdiscipline];
					appdb.model.StaticList.Disciplines = e.reply[i].appdb.discipline;
					appdb.model.StaticList.Subdisciplines = e.reply[i].appdb.subdiscipline;
					for (j=0; j<e.reply[i].appdb.discipline.length; j++) {
						ids.push(e.reply[i].appdb.discipline[j].id);
						vals.push(e.reply[i].appdb.discipline[j].val());
					}
					domainData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
				} else if ( e.reply[i].id === "vos" ) {
					delete voData;
					ids = [];
					vals = [];
					if ( ! $.isArray(e.reply[i].appdb.vo) ) e.reply[i].appdb.vo = [e.reply[i].appdb.vo];
					appdb.model.StaticList.VOs = e.reply[i].appdb.vo;
					for (j=0; j<e.reply[i].appdb.vo.length; j++) {
						ids.push(e.reply[i].appdb.vo[j].id);
						vals.push(e.reply[i].appdb.vo[j].val());
					}
					voData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
				} else if ( e.reply[i].id === "reg" ) {
					delete countryData;
					delete regionalData;
					ids = [];
					vals = [];
					if ( ! $.isArray(e.reply[i].appdb.country) ) e.reply[i].appdb.country = [e.reply[i].appdb.country];
					for (j=0; j<e.reply[i].appdb.country.length; j++) {
						ids.push(e.reply[i].appdb.country[j].id);
						vals.push(e.reply[i].appdb.country[j].val());
					}
					countryData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
					regionalData = e.reply[i].appdb.country;
					appdb.model.Regional.setLocalData(e.reply[i].appdb);
					populateFlagImages();
				} else if ( e.reply[i].id === "mws" ) {
					delete mwData;
					ids = [];
					vals = [];
					if ( ! $.isArray(e.reply[i].appdb.middleware) ) e.reply[i].appdb.middleware = [e.reply[i].appdb.middleware];
					appdb.model.StaticList.Middlewares = e.reply[i].appdb.middleware;
					for (j=0; j<e.reply[i].appdb.middleware.length; j++) {
						ids.push(e.reply[i].appdb.middleware[j].id);
						vals.push(e.reply[i].appdb.middleware[j].val());
					}
					mwData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
					appdb.model.Middlewares.setLocalData(e.reply[i].appdb);
				} else if ( e.reply[i].id === "sta" ) {
					delete statusData;
					ids = [];
					vals = [];
					if ( ! $.isArray(e.reply[i].appdb.status) ) e.reply[i].appdb.status = [e.reply[i].appdb.status];
					appdb.model.StaticList.Statuses = e.reply[i].appdb.status;
					for (j=0; j<e.reply[i].appdb.status.length; j++) {
						ids.push(e.reply[i].appdb.status[j].id);
						vals.push(e.reply[i].appdb.status[j].val());
					}
					statusData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
				} else if ( e.reply[i].id === "cnt" ) {
					delete contactTypeData;
					delete contactTypeDataObject;
					ids = [];
					vals = [];
					if ( ! $.isArray(e.reply[i].appdb.contact) ) e.reply[i].appdb.contact = [e.reply[i].appdb.contact];
					for (j=0; j<e.reply[i].appdb.contact.length; j++) {
						ids.push(e.reply[i].appdb.contact[j].id);
						vals.push(e.reply[i].appdb.contact[j].type);
					}
					contactTypeDataObject = {"ids": ids, "vals": vals};
					contactTypeData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
				} else if ( e.reply[i].id === "rol" ) {
					delete roleData;
					ids = [];
					vals = [];
					if ( ! $.isArray(e.reply[i].appdb.role) ) e.reply[i].appdb.role = [e.reply[i].appdb.role];
					for (j=0; j<e.reply[i].appdb.role.length; j++) {
						ids.push(e.reply[i].appdb.role[j].id);
						vals.push(e.reply[i].appdb.role[j].type);
					}
					roleData = JSON.stringify({"ids": ids, "vals": vals}).replace(/"/g,"'");
				} else if ( e.reply[i].id === "reflapps" ) {
					new appdb.utils.filterFields().setLocalList(e.reply[i].appdb,"applications", "person");
					new appdb.utils.filterFields().setLocalList(e.reply[i].appdb,"applications", "application");
				} else if ( e.reply[i].id === "reflppl" ) {
					new appdb.utils.filterFields().setLocalList(e.reply[i].appdb,"people", "person");
					new appdb.utils.filterFields().setLocalList(e.reply[i].appdb,"people", "application");
				} else if ( e.reply[i].id === "reflvos" ) {
					new appdb.utils.filterFields().setLocalList(e.reply[i].appdb, "vos", "vo");
				} else if ( e.reply[i].id === "tags" ) {
					appdb.model.Tags.setLocalData(e.reply[i].appdb);
				}else if ( e.reply[i].id === "swlogistics" ) {
					var logs = [];
					if( e.reply[i].appdb && e.reply[i].appdb.logistics){
						logs = e.reply[i].appdb.logistics;
					}
					appdb.model.StaticList.SoftwareLogistics = logs;
				}else if( e.reply[i].id === "cou" ) {
					if ( ! $.isArray(e.reply[i].appdb.country) ) e.reply[i].appdb.country = [e.reply[i].appdb.country];
					appdb.model.StaticList.Countries = e.reply[i].appdb.country;
				}else if( e.reply[i].id === "proglangs" ) {
					if ( ! $.isArray(e.reply[i].appdb.language) ) e.reply[i].appdb.language = [e.reply[i].appdb.language];
					appdb.model.StaticList.ProgLangs = e.reply[i].appdb.language;
					delete peopleData;
					ids = [];
					vals = [];
					for (j=0; j<e.reply[i].appdb.language.length; j++) {
						ids.push(e.reply[i].appdb.language[j].id);
						vals.push(e.reply[i].appdb.language[j].val());
					}
					proglangData = JSON.stringify({"ids": ids, "vals": vals}).replace(/'/g,"\\'").replace(/"/g,"'");
				}else if( e.reply[i].id === "oses" ){
					if ( ! $.isArray(e.reply[i].appdb.os) ) e.reply[i].appdb.os = [e.reply[i].appdb.os];
					appdb.model.StaticList.Oses = e.reply[i].appdb.os;
				}else if( e.reply[i].id === "archs" ){
					if ( ! $.isArray(e.reply[i].appdb.arch) ) e.reply[i].appdb.arch = [e.reply[i].appdb.arch];
					appdb.model.StaticList.Archs = e.reply[i].appdb.arch;
				}else if( e.reply[i].id === "hypervs" ){
					if ( ! $.isArray(e.reply[i].appdb.hypervisor) ) e.reply[i].appdb.hypervisor = [e.reply[i].appdb.hypervisor];
					appdb.model.StaticList.Hypervisors = e.reply[i].appdb.hypervisor;
				}else if( e.reply[i].id === "vmiformats" ){
					if ( ! $.isArray(e.reply[i].appdb.format) ) e.reply[i].appdb.format = [e.reply[i].appdb.format];
					appdb.model.StaticList.ImageFormats = e.reply[i].appdb.format;
				}else if( e.reply[i].id === "licenses" ){
					if ( ! $.isArray(e.reply[i].appdb.license) ) e.reply[i].appdb.license = [e.reply[i].appdb.license];
					appdb.model.StaticList.Licenses = e.reply[i].appdb.license;
					if( appdb.model.StaticList.Licenses.length > 0 && appdb.model.StaticList.Licenses[0].id == "0" ){
						appdb.model.StaticList.Licenses = appdb.model.StaticList.Licenses.slice(1);
					}
				}else if( e.reply[i].id === "vaproviders" ){
					if ( ! $.isArray(e.reply[i].appdb.provider) ) e.reply[i].appdb.provider = [e.reply[i].appdb.provider];
					appdb.model.StaticList.VAProviders = e.reply[i].appdb.provider;
				}else if( e.reply[i].id == "reltypes" ){
					if ( ! $.isArray(e.reply[i].appdb.relationtype) ) e.reply[i].appdb.relationtype = [e.reply[i].appdb.relationtype];
					appdb.model.StaticList.RelationTypes = e.reply[i].appdb.relationtype;
				}
			}
		});
	}

	function closeDialog(){
		if (dijit.byId("detailsdlg"+dialogCount) !== undefined) dijit.byId("detailsdlg"+dialogCount).onCancel();
	}
	
	function onError(e) {
		new appdb.views.ErrorHandler().handle({status: 'Could not save software data', description:'There was an error processing your request, most probably due to a bug, or a network communication problem.<br/>Please try again, or notify support through the <a href="http://helpdesk.egi.eu" target="_blank">EGI Helpdesk</a> if the problem persists, by copying and pasting any text provided in the details',source: {code: e.status, description: e.responseText}});
		$("#cancelsavedetails").click();

	}

// APP INDEX
	function filterApps() {
		var sf = $(":input[name='simpleFilter']:last");
		sf.focus();
		var order="&orderby="+$(".orderby").find(":input").next().val();
		var orderOp="&orderbyOp="+$(".orderbyOp").find(":input").next().val();
		var fuzzy="";
		if ($(':input[name="fuzzySearch"]').attr("checked")) fuzzy = "&fuzzySearch=1";
		ajaxLoad(base+'?flt='+encodeURIComponent($(":input[name='simpleFilter']").val())+fuzzy+order+orderOp, "main");
	}

	function initAppIndexView() {
		ajaxCount++;
		if ($("#subindex")[0].value != "") base=base+"/"+$("#subindex")[0].value;
		navpaneclicks($("#applicationspane")[0]);				
		setTimeout(function(){	
			var sf = $(":input[name='simpleFilter']:last");
			searchbox(sf);
			dojo.connect(dijit.byId(sf.attr("id")), "onKeyPress", function(k) { 
				if (k.keyCode == dojo.keys.ENTER) { 
					filterApps(); 
				}
			});
		}, 100);
		initItemView();
	}

// PPL DETAILS

	function makePplElementEditable() {
		var e = $($.find("#ppl_details"));
		e.find("span.ppl-firstname").addClass("editable").attr("edit_name","firstName").attr("edit_type","text");
		e.find("span.ppl-lastname").addClass("editable").attr("edit_name","lastName").attr("edit_type","text");
		e.find("span.ppl-gender").addClass("editable").attr("edit_name","gender").attr("edit_type","combo").attr("edit_data","{ids: ['male', 'female', 'NULL'], vals: ['Male', 'Female', 'N/A']}");
		e.find("span.ppl-country").addClass("editable").attr("edit_name","countryID").attr("edit_type","combo").attr("edit_data",countryData);
		e.find("span.ppl-contactType").addClass("editable").attr("edit_name","contactType").attr("edit_type","combo").attr("edit_data",contactTypeData).attr("edit_group","true");
		e.find("span.ppl-contact").addClass("editable").attr("edit_name","contact").attr("edit_type","text").attr("edit_group","true");
		e.find("tr.ppl-resourceProvider").hide();

		var h=$($.find("#ppl_details"));
		h.html('<form id="editperson" name="editperson" action="/people/update" method="post">'+h.html()+'</form>');
		editForm("editperson");
	}

// APP DETAILS

	function makeAppElementEditable(e, act) {
		if ( act == 5 ) $(e).find("span.app-name").addClass("editable").attr("edit_name","name").attr("edit_type","text");
		if ( act == 6 ) $(e).find("span.app-desc").addClass("editable").attr("edit_name","description").attr("edit_type","text").attr("edit_required","true").attr("edit_maxlength","200");
		if ( act == 7 ) $(e).find("pre.app-abstract").addClass("editable").attr("edit_name","abstract").attr("edit_type","textarea");
		if ( act == 9 ) $(e).find("span.app-status").addClass("editable").attr("edit_name","statusID").attr("edit_type","combo");
		if ( act == 10 ) $(e).find("span.app-domain").addClass("editable").attr("edit_name","domainID").attr("edit_type","combo").attr("edit_group","true");
		if ( act == 11 ) $(e).find("span.app-subdomain").addClass("editable").attr("edit_name","subdomainID").attr("edit_type","combo").attr("edit_group","true");
		if ( act == 12 ) {
			$(e).find("span.app-country").addClass("editable").attr("edit_name","countryID").attr("edit_type","combo").attr("edit_group","true").attr("edit_onchange","fixCountryFlags");
			$(e).find("span.inherited-country").removeClass("editable");
			$(e).find("span.app-countryFlag").addClass("editable").attr("edit_type","none");
		}
		if ( act == 13 ) {
			$(e).find("span.app-vo").addClass("editable").attr("edit_name","vo").attr("edit_type","combo").attr("edit_group","true");
		}
		
		if ( act == 20 ) { 
			$(e).find("span.app-mw").addClass("editable").attr("edit_name","mw").attr("edit_type","combo").attr("edit_group","true");//.attr("edit_combo","true");
		}
		if ( act == 23 ) $(e).find("span.app-owner").addClass("editable").attr("edit_name","owner").attr("edit_type","combo");
		if( act == 31 ) $(e).find("span.app-proglang").addClass("editable").attr("edit_name","proglangID").attr("edit_type","combo").attr("edit_group","true");
	}	

	function formatDate(d) {
		var subdate;
		if (typeof d === "undefined") {
			subdate = new Date();
		} else {
			subdate = d;
		}
		month = ''+(parseInt(subdate.getMonth())+1);
		if ( month.length == "1" ) month = "0"+month;
		day = ''+subdate.getDate();
		if ( day.length == "1" ) day = "0"+day;
		hours = ''+subdate.getHours();
		if ( hours.length == "1" ) hours = "0"+hours;
		minutes = ''+subdate.getMinutes();
		if ( minutes.length == "1" ) minutes= "0"+minutes;
		subdate = subdate.getFullYear()+'-'+month+'-'+day+' '+hours+':'+minutes;
		return subdate;
	}

	function buildRating(subname,subdate,r,edit) {
		var rating_counter, rating_html = '', ratingspan, submitter;
		var comment;
		var rating;
		if (typeof edit === "undefined") edit = false;
 		for ( rating_counter=1;rating_counter<=5;rating_counter+=1) {
			rating_html += '<img class="ratingstar" data-rating="'+rating_counter+'" style="cursor: pointer; vertical-align: middle" src="/images/star1.png" border="0"/>';
		}
		ratingspan = $('<div><span class="ratingspan">'+rating_html+'</span></div>');
		ratingspan.find("img.ratingstar").each(function(){
			if ( $(this).attr("data-rating") <= r ) $(this).attr("src","/images/star2.png");
		});
		if ( edit ) {
			$(".newrating").empty().remove();
			submitter = '<div class="dijitDialogTitleBar" style="width:99%">'+ratingspan.html()+'<span> Entry added by <a href="/store/person/'+userCName+'" onclick="appdb.views.Main.showPerson({id: '+userID+', cname:\"'+userCName+'\"}, {mainTitle: \''+subname+'\'});">'+subname+'</a>, on '+appdb.utils.FormatISODate(subdate)+'</span>'+reportCommentAbuseHtml+'</div>';
 			comment = '<textarea maxlength="512" dojoType="dijit.form.Textarea" name="comment"></textarea>';
 			rating = $('<div style="padding-bottom: 20px" class="newrating"><div class="dijitDialogTitleBar">'+submitter+'</div><div style="height:20px"></div><div style="padding-left:10px; padding-right:10px; padding-top:10px; padding-bottom: 10px; margin-left: auto; margin-right:auto;border:1px solid grey; width: 90%">'+comment+'</div><div style="height:20px"></div><div style="float:right; margin-right:50px"><a class="submitrating" href="#"><b>Submit</b></a> <a class="cancelrating" href="#"><b>Cancel</b></a></div></div>');
			if ( parseInt(r) != 0 ) rating.attr("data-temprating",r);
			rating.find("img.ratingstar").mouseover(function(){
				var myr = parseInt($(this).attr("data-rating"));
				rating.find("img.ratingstar").each(function(){
					if ( parseInt($(this).attr("data-rating")) <= myr ) {
						$(this).attr("src","/images/star2.png");
					} else {
						$(this).attr("src","/images/star1.png");
					}
				});
			});
			rating.find("img.ratingstar").mouseout(function(){
				var myr = rating.attr("data-temprating");
				rating.find("img.ratingstar").each(function(){
					if ( parseInt($(this).attr("data-rating")) <= myr ) {
						$(this).attr("src","/images/star2.png");
					} else {
						$(this).attr("src","/images/star1.png");
					}
				});
			});
			rating.find("img.ratingstar").click(function(){
				var myr = parseInt($(this).attr("data-rating"));
				rating.attr("data-temprating",myr);
			});
		} else {
			submitter = '<div class="dijitDialogTitleBar" style="width:99%">'+ratingspan.html()+'<span> Entry added by '+subname+', on '+appdb.utils.FormatISODate(subdate)+'</span>'+reportCommentAbuseHtml+'</div>';
			comment = '<i>No comment</i>';
			rating = $('<div class="ratingentry"><div class="dijitDialogTitleBar">'+submitter+'</div><div style="height:20px"></div><div style="padding-left:10px; padding-right:10px; padding-top:10px; padding-bottom: 10px; margin-left: auto; margin-right:auto;border:1px solid grey; width: 90%">'+comment+'</div><div style="height:20px"></div></div>');
		}
		return rating;
	}

	function countStars(e) {
		var r=0;
		e.find("img.ratingstar").each(function(){
			if ( $(this).attr("src") === "/images/star2.png" ) {
				if ( parseInt($(this).attr("data-rating")) > r ) r = parseInt($(this).attr("data-rating"));
			}
		});
		return r;
	}

	function addRating(d, e, r, doSubmit) {
		var comment_text, oldtext;
		var month,day,hours,minutes;
		var rating, voteid;
		var oldrating = null;
		if ( doSubmit === undefined ) doSubmit = false;
		if ( r == 0 ) r = countStars($('div.ratingentry[data-submitterid="'+userID+'"]:first'));
		rating = buildRating(userFullname, formatDate(), r, true);
		$(rating).prependTo(".ratingroot");
		dojo.parser.parse(rating[0]);
		rating.find("a.submitrating").click(function(){
			if ( $(".newrating").length > 0 ) {
				comment_text = $.trim($('<span>'+$(".newrating textarea").val()+'</span>').text().replace(/\n/g,"<br/>"));
			} else {
				if ( userID !== null ) {
					comment_text = $.trim($('<span>'+$('.ratingentry[data-submitterid="'+userID+'"] textarea').val()+'</span>').text().replace(/\n/g,"<br/>"));
					voteid = $('.ratingentry[data-submitterid="'+userID+'"]').attr('data-id');
				}
			}
			if ( typeof $(".newrating").attr("data-temprating") != 'undefined' ) {
				r = $(".newrating").attr("data-temprating");
			}
			if ( (r == 0) && (comment_text == '') ) {
				$('<div title="Warning"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Please enter a rating or a comment first.</p></div>').dialog({
					dialogClass: 'alert',
					autoOpen: true,
					resizable: false,
					height:160,
					modal: true,
					buttons: {
						OK: function() {
							$(this).dialog('close');
						}
					}
				});	
			} else {
				
				var p = $(".newrating textarea").parent();
				if( p.length === 0 ){
					p = $(".ratingentry textarea").parent();
				}
				$(".newrating textarea").remove();
				submitEponymousRating(e, d, r, comment_text, voteid, true);
				oldtext = comment_text;
				if ( comment_text == '' ) comment_text = '<i>No comment</i>';
				p.html(comment_text).addClass("ratingcomment");
				$("a.submitrating").remove();
				$("a.cancelrating").remove();
				$(".newrating").css("padding-bottom","0");
				if (r==0) {
					$(".ratingspan").html('<i>Unrated</i>');
					$(".ratingspan").attr("style",'width:80px;display:inline-block;text-align:center;font-weight:bold;color:gray;');
				} else { 
					$(".ratingspan").find("img.ratingstar").unbind('click');
					$(".ratingspan").find("img.ratingstar").unbind('mouseover');
					$(".ratingspan").find("img.ratingstar").unbind('mouseout');
				}
				$(".ratingspan").removeClass("ratingspan");
				if(oldtext){
					p.html(oldtext);
				} else {
					p.html('<i>No comment</i>');
				}
				rating.find("textarea").remove();
				populateRatingsActions(voteid,d);
			}
			return false;
 		});
		rating.find("a.cancelrating").click(function(){
			if ( $(".newrating").length > 0 ) {
				$(".newrating").empty();
				$('div.ratingentry[data-submitterid="'+userID+'"]:first').show();
			} else {
				var p = rating.find("textarea").parent();
				rating.find("textarea").remove();
				if(oldtext){
					p.html(oldtext);
				} else {
					p.html('<i>No comment</i>');
				}

				p.addClass("ratingcomment");
				$("a.submitrating").remove();
				$("a.cancelrating").remove();
			}
			if( d && d.application && (d.application.ratingCount<<0) === 0 ){
				$("#ratingdiv" + dialogCount).find(".emptycontent").removeClass('hidden');
			}
			populateRatingsActions(voteid,d);
			return false;
		});

		oldrating = $('div.ratingentry[data-submitterid="'+userID+'"]:first');
		if (oldrating !== null) {
			voteid = oldrating.attr("data-id");
			oldrating.hide();
			
			try {
				oldtext = oldrating.find(".ratingcomment").html().replace(/\<br\>/ig,"\n").replace(/\<br\/\>/ig,"\n").replace(/\<br\>\<\/br\>/ig,"\n");
				rating.find("a.submitrating").html("<b>Update</b>");
			} catch (ex) {
				oldtext = 'No comment';
			}
			if ( ($('<span>'+oldtext+'<span>').text() !== 'No comment') &&
				 ($('<span>'+oldtext+'<span>').text() !== 'Comment removed due to abuse report') ) {
				$(".newrating textarea").val(oldtext);
			}
		} else {
			voteid = false;
		}
		if ( doSubmit ) submitEponymousRating(e, d, r, comment_text, voteid, false);
		if (oldrating !== null) {
			$("#navdiv"+dialogCount).tabs("select",5);
			$("#ratingdiv" + dialogCount).find(".emptycontent").addClass('hidden');
			$(".newrating textarea").focus();
			setTimeout(function(){
				var ratingInfoMsg = new dijit.TooltipDialog({
					title: 'Information',
					content: 'You can set or change your rating from here.'
				});
				dijit.popup.open({
					popup: ratingInfoMsg,
					parent: $(".ratingspan")[0],
					around: $(".ratingspan")[0],
					orient: {TL:'BL'}                    
				});
				$(ratingInfoMsg.domNode).hide();
				$(ratingInfoMsg.domNode).fadeIn();
				setTimeout(function(){
					$(ratingInfoMsg.domNode).fadeOut();
					setTimeout(function(){
						dijit.popup.close(ratingInfoMsg);
						delete ratingInfoMsg;
					},5000);
				},3000);
			},50);
		}
	}
	function populateTags(id,e,_d){
		var cont = $(e).find("div.app-tags")[0];
		var v = new appdb.views.TagList({container: $(cont)});
		v.load(_d.application);
		appdb.pages.application.requests.register(v._model, "taglist");
	}
	function populateRatingsActions(id,_d){
		$(".moderateComment a").each(function(){
			$(this).unbind("click").bind("click", function(){
				var cid = $(this).parents(".ratingentry").attr("data-id");
				if ( $(this).text() === "Moderate" ) {
					$.ajax({
						url: '/abuse/moderatecomment',
						data: {
							id: cid,
							moderate: "1"
						},
						success: function(data) {
							try {
								data = JSON.parse(data);
							} catch (ex) {
								errh = new appdb.views.ErrorHandler();
								errh.handle({status:"Server error", description: "An error occured while committing your action.", source: ex});
								return;
							}
							var e = $('.ratingentry[data-id="'+data.id+'"]');
							e.find(".ratingcomment").html('<i>Comment removed due to abuse report</i>');
							e.find(".moderateComment a").html('Unmoderate');
						}
					});
				} else {
					$.ajax({
						url: '/abuse/moderatecomment',
						data: {
							id: cid,
							moderate: "0"
						},
						success: function(data) {
							try {
								appdb.debug(data);
								data = JSON.parse(data);
							} catch (ex) {
								errh = new appdb.views.ErrorHandler();
								errh.handle({status:"Server error", description: "An error occured while committing your action.", source: ex});
								return;
							}
							var e = $('.ratingentry[data-id="'+data.id+'"]');
							var s = appdb.utils.base64.decode(data.comment,true);
							if ( $.trim(s) == '' ) s = '<i>No comment</i>';
							e.find(".ratingcomment").html(s);
							e.find(".moderateComment a").html('Moderate');
						}
					});
				}
			});
		});
		$(".reportCommentAbuse").each(function(){
			$(this).unbind("click").bind("click", function(){
				if ( userID !== null ) {
					var cid = $(this).parents(".ratingentry").attr("data-id");
					appdb.debug('cid: '+cid);
					appdb.views.Main.showReportAbuse({"id": id, type: 'comment',commentId:cid,name:_d.application.name});
				} else {
					$('<div title="Report a Problem"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>If you wish to file a report about a problem regarding a comment, please <a class="login" href="#" style="color:#D96B00;text-decoration:none;">login</a> first using your EGI SSO account.<br/><br/>If you don\'t have an EGI SSO account you can acquire one <a style="color:#D96B00;text-decoration:none;" href="http://www.egi.eu/sso" target="_blank">here</a></p></div>').dialog({
						dialogClass: 'info',
						autoOpen: true,
						resizable: false,
						width:400,
						height:190,
						modal: true,
						buttons: {
							OK: function() {
								$(this).dialog('close');
							}
						}
					});	
				}
			});
		});
	}
	
	function populateRatings(id,e,_d) {
		var root = $('<div class="ratingroot"></div>');
		var ratings, rating, submitter, subname, comment, subdate, d1, d2, rating_html, rating_counter, ratingspan;
		var _endpoint = appdb.config.endpoint.baseapi;
		var dat = new appdb.utils.rest({
			endpoint: appdb.config.endpoint.proxyapi+"?version=" + appdb.config.apiversion + "&resource=applications/"+id+"/ratings",
			async: true
		}).create({},{
			success: function(d) {
				if ( d.rating ) {
					ratings = d.rating;
					if ( ! $.isArray(ratings) ) ratings = [ratings];
					var commentcount = 0;
					for(i=0; i<ratings.length; i+=1) {
						subdate = ratings[i].submittedOn;
						d1 = subdate.split("T");
						if(d1.length>0 && typeof d1[0] === "string"){
							d2 = d1[1].split(":");
							d2 = d2[0]+':'+d2[1];
						}
						if(d1.length>0 && typeof d1[0] === "string"){
							d1 = d1[0].split("-");
						}
						if(d1.length===3){
							d1 = ''+d1[0]+'-'+d1[1]+'-'+d1[2];
						} else {
							d1 = "";
						}
						subdate = d1+' '+d2;
						rating_html = '';
						if ( typeof ratings[i].rating !== "object" ) { 
							for ( rating_counter=1;rating_counter<=5;rating_counter+=1) {
								rating_html += '<img class="ratingstar" data-rating="'+rating_counter+'" style="cursor: pointer; vertical-align: middle" src="/images/star1.png" border="0"/>';
							}
							ratingspan=$("<div><span></span></div>");
							ratingspan.html(rating_html);
							ratingspan.find("img.ratingstar").each(function(){
								if ( $(this).attr("data-rating") <= ratings[i].rating ) $(this).attr("src","/images/star2.png");
							});
						} else {
							ratingspan=$("<div><span style='width:80px;display:inline-block;text-align:center;font-weight:bold;color:gray;'><i>Unrated</i></span></div>");
						}
						if ( ratings[i].submitter.type === "internal" ) {
							subname = ratings[i].submitter.person.firstname+' '+ratings[i].submitter.person.lastname;
							submitter = '<div class="dijitDialogTitleBar" style="width:99%">'+ratingspan.html()+'<span> Entry added by <a href="/store/person/'+ratings[i].submitter.person.cname+'" onclick="appdb.views.Main.showPerson({id: '+ratings[i].submitter.person.id+',cname:\''+ratings[i].submitter.person.cname+'\'}, {mainTitle: \''+subname+'\'});">'+subname+'</a>, on '+appdb.utils.FormatISODate(subdate)+'</span>'+reportCommentAbuseHtml+'</div>';
						} else {
							try {
								subname = ratings[i].submitter.val();
							} catch (ex) {
								subname = '';
							}
							if ( subname != '') {
								if ( ratings[i].submitter.email ) subname = subname + ' ('+ratings[i].submitter.email+')';
								submitter = '<div class="dijitDialogTitleBar" style="width:99%">'+ratingspan.html()+'<span> Entry added by '+subname+', on '+appdb.utils.FormatISODate(subdate)+'</span>'+reportCommentAbuseHtml+'</div>';
							} else {
								submitter = '<div class="dijitDialogTitleBar" style="width:99%">'+ratingspan.html()+'<span> Entry added by <i>an anonymous guest</i>, on '+appdb.utils.FormatISODate(subdate)+'</span>'+reportCommentAbuseHtml+'</div>';
							}
						}
						if (subname != '') {
							if ( ratings[i].comment ) {
								comment = '<span>'+ratings[i].comment+'<span>';
							} else {
								comment = '';
							}
							comment = $(comment.replace(/\<br\/\>/g,"\n")).text().replace(/\n/g,"<br/>");
							if ( comment == '' ) comment = '<i>No comment</i>';
							rating = $('<div class="ratingentry">'+submitter+'<div style="height:20px"></div><div class="ratingcomment" style="padding-left:10px; padding-right:10px; padding-top:10px; padding-bottom: 10px; margin-left: auto; margin-right:auto;border:1px solid grey; width: 90%">'+comment+'</div><br/></div>');
							if (ratings[i].submitter.type === "internal") {
								rating.attr("data-isanon","false");
								rating.attr("data-submitterid",ratings[i].submitter.person.id);
							} else {
								rating.attr("data-isanon","true");
							}
							rating.attr("data-id",ratings[i].id);
							if (ratings[i].moderated == "true") {
								rating.attr("data-moderated","true");
								rating.find(".ratingcomment").html("<i>Comment removed due to abuse report</i>");
							} else {
								rating.attr("data-moderated","false");
								if( ratings[i].submitter.type === "internal" ){
									commentcount += 1;
								}
							}
							rating.appendTo(root);
						}
					}
					$("#ratingdiv"+dialogCount).removeClass("isempty");
					appdb.pages.application.updateRatingCounter(commentcount);
				} else {
					root.html('<div style="padding-left: 10px" class="noratings">No comments or ratings yet...</div>');
					$("#ratingdiv"+dialogCount).addClass("isempty");
					appdb.pages.application.updateRatingCounter(0);
				}
				var leaveComment = $('<div style="padding-left: 10px" class="leavecomment" style="padding-left:10px"><a href="#" class="icontext"><img src="/images/comment.png" alt=""/><span>Leave a comment...</span></a></div><br/>');
				if (userID !== null) {
					leaveComment.find("a").click(function(){
						addRating(_d, e, 0, false);
					});
				} else {
					leaveComment.find("a").click(function(){
						var leaveCommentMsg = $('<div title="Leave comment"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>If you wish to leave a comment, please <b>Sign In</b> first with your account.<br/></p></div>');
						leaveCommentMsg.find("a.login").click(function(){
							leaveCommentMsg.dialog('close');
							login();
						});
						leaveCommentMsg.dialog({
							dialogClass: 'info',
							autoOpen: true,
							resizable: false,
							width:400,
							height:190,
							modal: true,
							buttons: {
								OK: function() {
									$(this).dialog('close');
								}
							}
						});	
					});
				}
				leaveComment.appendTo($("#ratingdiv"+dialogCount));
				if( !d.rating ){
					$(leaveComment).clone(true).appendTo($("#ratingdiv"+dialogCount).find(".emptycontent > .content"));
				}
				
				if ( root.html() == '' ) root.html('<div style="padding-left: 10px" class="noratings">No comments yet...</div>');
				root.appendTo($("#ratingdiv"+dialogCount));
				createRatingsControl(e,_d);
				if ( userIsAdminOrManager ) {
					$(".ratingentry").each(function(){
						var cid = $(this).attr("data-id");
						if ( $(this).attr("data-moderated") == "false" ) {
							$(this).find(".commentActions").prepend('<span class="moderateComment"><a href="#">Moderate</a><span style="padding-left: 5px; padding-right: 5px;" class="reportLinkSeparator">•</span></span>');
						} else {
							$(this).find(".commentActions").prepend('<span class="moderateComment"><a href="#">Unmoderate</a><span style="padding-left: 5px; padding-right: 5px;" class="reportLinkSeparator">•</span></span>');
						}
					});
				}
				populateRatingsActions(id,_d);
			}
		});
		dat.call();
		appdb.pages.application.requests.register({ getXhr: (function(x){ return function(){ return x.getXhr();}; })(dat) },"ratings");
	}

	function separateMultipleItems(e) {
		var i;
		$(e).parent().find(".comboseperator").remove();
		for(i=0; i<e.length - 1; i+=1) {
			$(e.get(i)).after('<span class="comboseperator">•</span>');
		}
	}

	function submitEponymousRating(e, d, r, comment, voteid, doNotify) {
		var verb = '';
		var _data = {};
		if (doNotify === undefined) doNotify = false;
		_data.appid = d.application.id;
		_data.rating = r;
		_data.submitterid = userID;
		if ( voteid !== false ) _data.ratingid = voteid;
		if ( comment !== null) _data.comment = comment;
		$.ajax({
			url: '/apps/addrating',
			data: _data,
			success: function(data) {
				try {
					data = JSON.parse(data);
				} catch (ex) {
					errh = new appdb.views.ErrorHandler();
					errh.handle({status:"Server error", description: "An error occured while committing your action.", source: ex});
					return;
				}
				//refresh rating on the fly
				var appratingspan = $(e).find("span.app-rating");
				appratingspan.empty();
				d.application.rating = data.average;
				if ( (voteid === false) || (typeof voteid === "undefined" ) ) {
					verb = 'submitted';
					d.application.ratingCount=parseInt(d.application.ratingCount)+1;
					appdb.pages.application.updateRatingCounter(d.application.ratingCount);
				} else {
					verb = 'updated';
				}
				$(".noratings").remove();
				$(".newrating").addClass("ratingentry").removeClass("newrating").attr("data-id",data.id).attr("data-submitterid",userID);
				createRatingsControl(e,d);
				ratingReport.load({query: {type:"both",appid:d.application.id}});
				if (doNotify) $('<div title="Information"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Your entry has been '+verb+'.</p></div>').dialog({
					dialogClass: 'info',
					autoOpen: true,
					resizable: false,
					height:160,
					modal: true,
					buttons: {
						OK: function() {
							$(this).dialog('close');
						}
					}
				});	
				$("#ratingdiv" + dialogCount).removeClass("isempty");
			},
			error: function(err) {
				var errh = new appdb.views.ErrorHandler();
				errh.handle({status:"Server error", description: "An error occured while committing your action.", source: err.responseText});
			}
		});
	}

	function submitAnonymousRating(e,d,star,vote) {
		var _data = {};
		var errh, rating, verb;
		_data.appid = d.application.id;
		_data.rating = $(star).attr("data-rating");
		if ( vote !== false ) _data.ratingid = vote;
		$.ajax({
			url: '/apps/addrating',
			data: _data,
			success: function(data) {
				try {
					data = JSON.parse(data);
				} catch (ex) {
					errh = new appdb.views.ErrorHandler();
					errh.handle({status:"Server error", description: "An error occured while committing your action.", source: ex});
					return;
				}
				var ratingstore = getRatingStore();
				ratingstore['app'+d.application.id] = data.id;
				$.cookie('ratings', JSON.stringify(ratingstore), {expires: 360, path: '/'});
				//refresh rating on the fly
				var appratingspan = $(e).find("span.app-rating");
				appratingspan.empty();
				d.application.rating = data.average;
				if ( vote === false ) {
					d.application.ratingCount = parseInt(d.application.ratingCount) + 1;
				}
				rating = buildRating('<i>an anonymous guest</i>', formatDate(), _data.rating);
				createRatingsControl(e,d);
				ratingReport.load({query: {type:"both",appid:d.application.id}});
				if ( vote !== false ) verb = 'updated'; else verb = 'submitted';
				$('<div title="Information"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Your entry has been '+verb+'.</p></div>').dialog({
					dialogClass: 'info',
					autoOpen: true,
					resizable: false,
					height:160,
					modal: true,
					buttons: {
						OK: function() {
							$(this).dialog('close');
						}
					}
				});	
			},
			error: function(err) {
				errh = new appdb.views.ErrorHandler();
				errh.handle({status:"Server error", description: "An error occured while committing your action.", source: err.responseText});
			}
		});
	}

	function getRatingStore() {
		var ratingstore = $.cookie('ratings');
		if ( $.trim(ratingstore) !== '' ) {
			try {
				ratingstore = JSON.parse(ratingstore); 
			} catch(ex) {
				ratingstore = {};
			}
		} else {
			ratingstore = {};
		}
		return ratingstore;
	}

	function ratingToString(rating) {
		var txt = '';
		r = Math.round(rating);
		if (r === 1) {
			txt = 'Poor';
		}else if (r === 2) {
			txt = 'OK';
		} else if (r === 3) {
			txt = 'Good';
		} else if (r === 4) {
			txt = 'Very Good';
		} else if (r === 5) {
			txt = 'Excellent';
		}
		return txt;
	}
	//Casts a number into a string with the given precision
	//If given precision is greater than the precision of the number then the function is padding with 0
	//e.g ratingPrecision(10.34,4) => "10.3400" (padding) , ratingPrecision(10.346,2) => "10.34"
	function ratingPrecision(r,p){
		r = '' + r;
		var res = '' , i, len, sd, s = r.split(".");
		if(s.length===1){
			sd = [];
			for(i=0; i<p; i+=1){
				sd.push("0");
			}
		}else{
			sd = s[1];
		}
		len = sd.length;
		res = s[0]+".";
		for(i=0; i<p; i+=1){
			res += (i<len)?sd[i]:"0";
		}
		return res;
	}

	function revokeRating(e,d) {
		$(".newrating").empty().remove();
		$('<div title="Revoke rating"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You are about to revoke your previous rating and the associated comment (if any). Are you sure you want to proceed?</p></div>').dialog({
			dialogClass: 'alert',
			autoOpen: true,
			resizable: false,
			modal: true,
			buttons: {
				Yes: function() {
					$(this).dialog('close');
					var _data = {};
					var errh;
					_data.appid = d.application.id;
					$.ajax({
						url: '/apps/revokerating',
						data: _data,
						success: function(data) {
							try {
								data = JSON.parse(data);
							} catch (ex) {
								errh = new appdb.views.ErrorHandler();
								errh.handle({status:"Server error", description: "An error occured while revoking your entry.", source: ex});
								return;
							}
							d.application.rating = data.average;
							d.application.ratingCount -= 1;
							var appratingspan = $(e).find("span.app-rating");
							appratingspan.empty();
							ratingReport.load({query: {type:"both",appid:d.application.id}});
							$('div.ratingentry[data-id="'+data.id+'"]').empty().remove();
							if ( userID === null ) {
								var ratingstore = getRatingStore();
								delete ratingstore['app'+d.application.id];
								$.cookie('ratings', JSON.stringify(ratingstore), {expires: 360, path: '/'});
							}
							createRatingsControl(e,d);
							$('<div title="Revoke entry"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Your entry has been successfully revoked.</p></div>').dialog({
								dialogClass: 'info',
								autoOpen: true,
								resizable: false,
								height:160,
								modal: true,
								buttons: {
									OK: function() {
										$(this).dialog('close');
									}
								}
							});	
							if ( $(".ratingroot").html() == '' ) {
								if ( d.application.ratingCount == 0 ) {
									$(".ratingroot").html('<div style="padding-left: 10px" class="noratings">No comments or ratings yet...</div>'); 
								} else {
									$(".ratingroot").html('<div style="padding-left: 10px" class="noratings">No comments yet...</div>'); 
								}
							}
						},
						error: function(err) {
							errh = new appdb.views.ErrorHandler();
							errh.handle({status:"Server error", description: "An error occured while revoking your entry.", source: err.responseText});
						}
					});
				},
				No: function(){
					$(this).dialog('close');
				}
			}
		});	

	}

	function createRatingsControl(e,d) {
		
		function hasSubmitted() {
			var ratingstore = getRatingStore();
			if ( ratingstore['app'+d.application.id] ) {
				return ratingstore['app'+d.application.id];
			} else {
				return false;
			}
		}

		function setRating(r) {
			var dec = 0;
			appratingspan.find("img.ratingstar").each(function(){
				if ( $(this).attr("data-rating") <= Math.floor(r) ) $(this).attr("src","/images/star2.png");
			});
			if ( r > Math.floor(r) ) {
				dec = r - Math.floor(r);
				if ( (dec >= 0.25) && (dec <= 0.75) ) {
					appratingspan.find("img.ratingstar").each(function(){
						if ( $(this).attr("data-rating") == Math.floor(r) + 1 ) $(this).attr("src","/images/star15.png");
					});
				} else if ( dec > 0.75 ) {
					appratingspan.find("img.ratingstar").each(function(){
						if ( $(this).attr("data-rating") == Math.floor(r) + 1) $(this).attr("src","/images/star2.png");
					});
				}
			}
		}

		var cancelSubmit = function() {
			starmouseoutdisabled = false;
			appratingspan.find("img.ratingstar").attr("src","/images/star1.png");
			$(".app-info-votecount").html(votecount_text);
			setRating(rating_num);
		};

		var starmouseoutdisabled = false;
		var appratingspan = $(e).find("span.app-rating");
		if ( ! d.application.rating ) {d.application.rating = 0;}
		var rating_html = '<table cellpadding="0" cellspacing="0" border="0" style="width:100%"><tr><td style="width:80px">';
		var rating_num = Math.round(d.application.rating*100)/100;
		for ( rating_counter=1;rating_counter<=5;rating_counter+=1) {
			rating_html += '<a href="#"><img style="cursor: pointer; vertical-align: middle" class="ratingstar" data-rating="'+rating_counter+'" style="vertical-align: middle" src="/images/star1.png" border="0"/></a>';
		}
		rating_html += '</td><td style="width:16px">';
		var canrevokerating = false;
		if ( ( userID === null ) && ( hasSubmitted() !== false ) ) canrevokerating = true;
		if ( ( userID !== null ) && ( $('div.ratingentry[data-submitterid="'+userID+'"]:first').length>0 ) ) canrevokerating = true;
		if ( canrevokerating ) rating_html += '<span class="revokerating"><a href="#"><img border="0" style="vertical-align: middle" src="/images/cancelicon.png"/></a></span>';
		rating_html += '</td><td><span> <span style="vertical-align: middle" class="app-info-votecount" style="margin-left: 10px">'+((parseFloat(d.application.rating) === 0)?"<i>( unrated</i> ":ratingToString(d.application.rating)+' ( Avg.: '+ratingPrecision(d.application.rating,2)+' by '+d.application.ratingCount+' vote'+((d.application.ratingCount==1)?'':'s'))+')</span></span>';
		rating_html += '</td></tr></table>';
		appratingspan.html(rating_html);
		$(".revokerating").hide();
		appratingspan.find("img.ratingstar").click(function(){
			var _this = $(this);
			var hasSubmittedText;
			if ( userID === null ) {
				if ( hasSubmitted() === false ) {
					hasSubmittedText = '';
				} else {
					hasSubmittedText = '<br/><b>Please note that you have already sumbitted a rating for this software; if you proceed, your previous rating will be replaced</b>.';
				}
				starmouseoutdisabled = true;
				var mustLoginMsg = $('<div title="Rating submission"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>You are about to submit your rating as an anonymous user. If you would also like to acompany your rating with a comment, please <b>Sign In</b> first with your account.<br/><br/>'+hasSubmittedText+'</p></div>');
				mustLoginMsg.find("a.login").click(function(){
					cancelSubmit();
					mustLoginMsg.dialog('close');
					login();
				});
				mustLoginMsg.dialog({
					dialogClass: 'alert',
					autoOpen: true,
					resizable: false,
					height:250,
					width: 400,
					modal: true,
					close: cancelSubmit,
					buttons: {
						OK: function() {
							$(this).dialog('close');
							submitAnonymousRating(e,d,_this, hasSubmitted());
						},
						Cancel: function(){
							$(this).dialog('close');
						}
					}
				});	
			}else {
				addRating(d, e, $(_this).attr("data-rating"), true);
			}
		});
		setRating(rating_num);
		var votecount_text;
		var hideRevokeRating;
		$(".revokerating a").click(function(){
			revokeRating(e,d);
		});
		appratingspan.find("img").mouseover(function(){
			hideRevokeRating = false;
			$(".revokerating").show();
			var r = $(this).attr("data-rating");
			appratingspan.find("img.ratingstar").each(function(){
				if ( $(this).attr("data-rating") <= r ) $(this).attr("src","/images/star2.png"); else $(this).attr("src","/images/star1.png"); 
			});
			var txt;
			if ($(this).parent().parent().hasClass("revokerating")) {
				txt = "Revoke my previous rating and/or comment";
			} else {
				txt = ratingToString(r);
			}
			votecount_text = $(".app-info-votecount").html();
			$(".app-info-votecount").html(txt);
		});
		appratingspan.find("img").mouseout(function(){
			hideRevokeRating = true;
			setTimeout(function(){if (hideRevokeRating) $(".revokerating").fadeOut();},700);			
			if ( ! starmouseoutdisabled ) {
				$(".app-info-votecount").html(votecount_text );
				appratingspan.find("img.ratingstar").attr("src","/images/star1.png");
				setRating(rating_num);
			}
		});
	}

	function showModerationDetails(d) {
		var moddername = '';
		if (d.moderator) moddername = ' by <a href="/store/person/'+d.moderator.cname+'" style="color:#D96B00;text-decoration:none;" onclick="appdb.views.Main.showPerson({id: '+d.moderator.id+', cname:\''+d.moderator.cname+'\'},{mainTitle: \''+d.moderator.firstname+" "+d.moderator.lastname+'\'});">'+d.moderator.firstname + ' '+ d.moderator.lastname+'</a>';
		var modDlg = $('<div title="Moderation Information"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>Software '+d.name+' has been moderated '+moddername+' for the following reason:</p><div style="padding:10px; border: 1px solid grey"><p>'+(d.moderationReason || '<i>No reason specified</i>')+'</p></div></div>');
		modDlg.dialog({
			dialogClass: 'info',
			autoOpen: true,
			resizable: false,
			width:400,
			height:'auto',
			modal: true,
			buttons: {
				Close: function() {
					$(this).dialog('close');
				}
			}
		});	
		
	}

	function showAppModInfo(e,d) {
		e = e || null;
		d = d || {};
		var modrow;
		if ( e === null ) {
			modrow = $($.find(".app-modrow"));
		} else {
			modrow = $(e).find(".app-modrow");
		}
		var moderator = d.application.moderator || '';
		var moderatedOn = d.application.moderatedOn || '';
		var modreason = d.application.moderationReason || '';
		var modtext = '<br/><b>Moderated';
		if ($.trim(moderator) !== '') modtext += ' by <a href="/store/person/'+moderator.cname+'" onclick="appdb.views.Main.showPerson({id: '+moderator.id+',cname:\''+moderator.cname+'\'},{mainTitle: \''+moderator.firstname+" "+moderator.lastname+'\'});">'+moderator.firstname+' '+moderator.lastname+'</a>';
		if ($.trim(moderatedOn) !== '') {
			var moddate = moderatedOn.split("T");
			if(moddate.length>0 && typeof moddate[0] === "string"){
				moddate = moddate[0].split("-");
			}
			if(moddate.length===3){
				moddate = ''+moddate[0]+'-'+moddate[1]+'-'+moddate[2];
			} else {
				moddate = "";
			}
			modtext += ' on '+appdb.utils.FormatISODate(moddate);
		}
		modtext += '</b> <a class="moddetails" href="#"><span style="font-size:8pt"><i>(more...)</i></span></a>';
		modrow.find("td").html(modtext);
		modrow.find("a.moddetails").click(function(){
			showModerationDetails(d.application);
		});
		modrow.show();
	}

    function appHistory(id) {
		var u = '', dat;
        var notification;
		var _endpoint = appdb.config.endpoint.baseapi;
        setTimeout(function(){
            notification = new appdb.utils.notification({
            title: "Software History",
            message: '<img src="/images/ajax-loader-small.gif" alt="" width="12px" height="12px" style="padding:0px;margin:0px;vertical-align: top"  /><span style="font-style: italic;font-size: 12px;vertical-align: super;padding:0px;margin:0px;">Loading...</span>',
            delay: 60000,
            style: "min-width: 240px; text-aling: center"
        });},0);
        if ( userID != null ) {
			u = "?userid="+userID+"&passwd="+$.cookie('scookpass');
		}
        dat = new appdb.utils.rest({
            endpoint: appdb.config.endpoint.proxyapi+"?version=" + appdb.config.apiversion + "&resource=applications/"+id+"/history"+encodeURIComponent(u),//_endpoint+"applications/"+id+"/history"+u,
            async: true
        }).create({},{
            success: function(d) {
                var s = '', by, evt;
                if ( d.history ) {
                    h = d.history;if ( ! $.isArray(h) ) h = [h];
                    for(var i=0; i<h.length; i++) {
                        if ( h[i].userid ) by = ' by <a target="_blank" href="/store/person/'+h[i].username+'" onclick="appdb.views.Main.showPerson({id: '+h[i].userid+', cname:\''+h[i].usercname+'\'},{mainTitle: \''+h[i].username+'\'});">' + h[i].username + (h[i].usercontact != '' ? ' ('+h[i].usercontact+')' : '') + '</a>'; else by = h[i].username + (h[i].usercontact != '' ? ' ('+h[i].usercontact+')' : '');
						evt = h[i].event;
						if ( evt == "update" && h[i].disposition === "rollback" ) evt = "rollback";
						s = s + '<li><a href="#" onclick="appdb.views.Main.showApplication({id: '+id+', histid: \''+h[i].id+'\', histtype: 0});">'+appdb.utils.formatDate(h[i].timestamp) + "</a>: "+ evt + by + 
						'</li>';
                    }
					s = '<div><h3><a style="font-size: 110%; margin-left: -24px;" href="#" onclick="appdb.views.Main.showApplication({id: '+id+', cname: appdb.pages.application.currentCName(), name: appdb.pages.application.currentName()});">View current state</a></h3></div>' + s;
                }
                if ( s === '' ) {
                    s = '<span><b>No history available</b></span>';
                } else {
                    s = '<div class="historylist"><ul>' + s + '</ul></div>';
                }
                var dlg = new dijit.TooltipDialog({
                    'title': 'Software History',
                    'content': s
                });
                setTimeout(function() {
                    notification.close();
                    dijit.popup.open({
                        popup: dlg,
                        parent: $("a.app-history")[0],
                        around: $("a.app-history")[0],
                        orient: {BR: 'TR'}
					});
				}, 500);
            }
        }).call();
	}

	function appHistoryRollback(appid, histid, histtype) {
		var dlg = $('<div title="Rollback s/w state"><p><table><tr><td><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span></td><td>This action will revert the software\'s current state, affecting only data in the <i>information</i> and <i>publication</i> panes. Other metadata such as releases, comments, etc. will not be affected.<br/><br/>Are you sure you want to proceed?</td></tr></table></p></div>').dialog({
			dialogClass: 'alert',
			autoOpen: false,
			resizable: false,
			height:220,
			width:470,
			modal: true,
			buttons: {
				Ok: function() {
					$(this).dialog('close');
					__appHistoryRollback(appid, histid, histtype);
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			}
		});
		dlg.dialog('open');
	}

	function __appHistoryRollback(appid,histid,histtype) {
		var u = '', cid = 0;
		if ( userID != null ) {
			u = "?userid="+userID+"&passwd="+$.cookie('scookpass');
			u += "&cid="+cid+"&src="+reqsrc;
			$("#details").hide();
			showAjaxLoading();
			var dat = new appdb.utils.rest({
				endpoint: appdb.config.endpoint.proxyapi+"?version=" + appdb.config.apiversion + "&resource=applications/" + appid + "/history/" + histid + "/rollback" + encodeURIComponent(u),
				async: true
			}).create({},{
				success: function(d) {
					hideAjaxLoading();
					if ( d.error ) {
						var err = {
							"status": "Cannot rollback software" ,
							"description": d.errordesc
						};
						appdb.utils.RestApiErrorHandler(d, err).show();
					}
					appdb.views.Main.showApplication({"id": appid});	
				},
				error: function(err) {
					hideAjaxLoading();
					(new appdb.views.ErrorHandler()).handle({status: "Cannot rollback software", description: err});
					appdb.views.Main.showApplication({"id": appid});	
				}
			}).call();
		}
	}

	function appHistoryRollback2(appid,histid,histtype) {
		histtype = histtype || '0';
		var histModel = new appdb.model.ApplicationHistory({"id": appid, "histid": histid});
		//Get the history application data. Then post old data as new ones.
		histModel.subscribe({
			"event": "select", 
			"callback": function(d) {
				//Successfully retrieved history data of the application
				showAjaxLoading();
				var st = d.history.oldvalue.application;
				if(histtype==='1'){ //new state
					st = d.history.newvalue.application;
				}
				//Create an application entity and pass the retrieved data
				var appEntity = new appdb.entity.Application(st);
				//Create xml representation of application
				var xml = appdb.utils.EntitySerializer.toXml(appEntity);
				//setup model to update data
				var appModel = new appdb.model.Application();
				appModel.subscribe({"event": "update", "callback": function(d){
					hideAjaxLoading();
					appdb.views.Main.showApplication({"id": d.application.id});	
				}}).subscribe({"event": "error", "callback": function(d){
					hideAjaxLoading();
					var err = {
						"status": "Cannot rollback software" ,
						"description": d.description
					};
					appdb.utils.RestApiErrorHandler(d, err).show();
				}});
				//Do the update
				appModel.update({query: {}, data: {data: encodeURI(xml)}});
			}
		}).subscribe({
			"event": "error", 
			"callback": function(d) {
				hideAjaxLoading();
				var err = {
						"status": "Cannot rollback software" ,
						"description": d.description
					};
				appdb.utils.RestApiErrorHandler(d, err).show();
			}
		});
		//Do get history entry
		histModel.get();
	}
	
	function showAppDelInfo(e,d) {
		e = e || null;
		d = d || {};
		var delrow;
		if ( e === null ) {
			delrow = $($.find(".app-delrow"));
		} else {
			delrow = $(e).find(".app-delrow");
		}
		var deleter = d.application.deleter || '';
		var deletedOn = d.application.deletedOn || '';
		var deltext = '<br/><b>Deleted';
		if (deleter != '') {
			deltext += ' by <a href="/store/person/'+deleter.cname+'" onclick="appdb.views.Main.showPerson({id: '+deleter.id+', cname:\''+deleter.cname+'\'},{mainTitle: \''+deleter.firstname+" "+deleter.lastname+'\'});">'+deleter.firstname+' '+deleter.lastname+'</a>';
		} else {
			deltext += ' by an unknown user ';
		}
		if (deletedOn!= '') {
			var deldate = deletedOn.split("T");
			if(deldate.length>0 && typeof deldate[0] === "string"){
				deldate = deldate[0].split("-");
			}
			if(deldate.length===3){
				deldate = ''+deldate[0]+'-'+deldate[1]+'-'+deldate[2];
			} else {
				deldate = "";
			}
			deltext += ' on '+appdb.utils.FormatISODate(deldate);
		}
		deltext += '</b>';
		delrow.find("td").html(deltext);
		delrow.show();
	}

	function compareHistStates(s1, s2, prop, elem) {
		var changed = false;
		var x1, x2;
		if ( s1.hasOwnProperty(prop) && s2.hasOwnProperty(prop) ) {
			x1 = s1[prop];
			x2 = s2[prop];
			if ( typeof x1 === "object" ) x1 = JSON.stringify(x1);
			if ( typeof x2 === "object" ) x2 = JSON.stringify(x2);
			changed = (x1 !== x2);
			if ( changed ) {
				elem.addClass("hist-changed");
				elem.attr("title", "this property has changed");
				elem.css("width", "auto");
			}
		}
		return changed;
	}
	function populateAppVos(e, d){
		var v;
		$(e).find("span.app-vos span.app-vo").remove();
		if ( d.vo ) {
			$(e).find("span.app-vos").empty();
			v = d.vo;
			if ( ! $.isArray(v)) v = (v)?[v]:[];
			for(i=0;i<v.length;i++) {
				$(e).find("span.app-vos").append('<span class="app-vo"><a href="#" class="voname" data="'+v[i].discipline+'">'+v[i].name.htmlEscape()+'</a></span>');
			}
			separateMultipleItems($(e).find("span.app-vo"));
			setupApplicationVOs(e);
		} else {
			$(e).find("span.app-vos").append(appdb.config.views.all.unspecified);
		}
	}
	function populateAppDataDetails(d,e,id, histid, histtype){
		showAjaxLoading();
		var otherstate = null;
		var recordFound = true;
		if ( typeof histid !== 'undefined' ) {
			$("div.app-subtitle").hide();
			if ( histtype == 0 ) {
				$(e).find("span.app-hist-timestamp").html('state before '+d.history.event+' on '+appdb.utils.formatDate(d.history.timestamp));
				otherstate = d.history.newvalue;
				if ( d.history.oldvalue ) d = d.history.oldvalue;
			} else {
				$(e).find("span.app-hist-timestamp").html('state after '+d.history.event+' on '+appdb.utils.formatDate(d.history.timestamp));
				otherstate = d.history.oldvalue;
				if ( d.history.newvalue ) d = d.history.newvalue;
			}
		}
		if ( d.application ) { // record was found
			id = d.application.id;
			appdb.pages.application.currentData(d);
			appdb.pages.application.SetupNavigationPane();
			if ( otherstate !== null ) {
				compareHistStates(d.application, otherstate.application, "name", $(e).find("span.app-name"));
				compareHistStates(d.application, otherstate.application, "description", $(e).find("span.app-desc"));
				compareHistStates(d.application, otherstate.application, "discipline", $(e).find("span.app-domains"));
				compareHistStates(d.application, otherstate.application, "subdiscipline", $(e).find("span.app-subdomains"));
				compareHistStates(d.application, otherstate.application, "url", $(e).find("span.app-desc"));
				compareHistStates(d.application, otherstate.application, "country", $(e).find("span.app-countries"));
				compareHistStates(d.application, otherstate.application, "vo", $(e).find("span.app-vos"));
				compareHistStates(d.application, otherstate.application, "middleware", $(e).find("span.app-mws"));
				compareHistStates(d.application, otherstate.application, "language", $(e).find("span.app-proglangs"));
				compareHistStates(d.application, otherstate.application, "contact", $(e).find("a.scicontoggler"));
			}
			var isTool = d.application.tool == "true"?true:false;
			if(d.application.category){
				d.application.category = $.isArray(d.application.category)?d.application.category:[d.application.category];
				isTool = (d.application.category.length == 1 &&  d.application.category[0].id == "2")?true:false;
			}
			if (d.application.bookmarked == "true") $($.find("a.app-bm img")).attr("src","/images/star2.png");
			var titlebar;
			if ( detailsStyle == 1) titlebar = $("#detailsdlg"+dialogCount+":last div span"); else titlebar = $(".detailsdlg:last div span");
			if (id===null) titlebar[0].innerHTML = 'New Software';
			if (isTool) {
				if(titlebar.length>0){
					titlebar[0].innerHTML = 'Software Details - <I>'+d.application.name+'</I>';
				}
			} else {
				if(titlebar.length>0){
					titlebar[0].innerHTML = 'Software Details - <I>'+d.application.name+'</I>';
				}
			}
			if (detailsStyle == 0 ) $(titlebar.parent()[0]).hide();
			$(e).find("img.app-logo").attr("src",appLogo(d.application));

			if(d.application.validated == "false" && appdb.config.views.application.displayInvalidatedText === true){
				$("#editRow"+dialogCount + " > td").attr("colspan","2").append('<span class="invalidatedhint"><div><img src="/images/exclam16.png" border="0" alt="" /><span>This software has not been updated during the last '+appdb.config.appValidationPeriod+'.</span></div></span>');
			}
			//Application Metatype 
			$(e).parent().find("input#metatype").val(d.application.metatype);
			//Application categories
			var categories = d.application.category;
			categories = categories || [];
			categories = ($.isArray(categories))?categories:[categories];
			managedAppCategories = categories;
			if(categories.length>0){
				$(e).find("span.softwarecategories").remove();
				$(e).find("span.app-categories").after("<span class='app-softwarecategories'></span>");
				renderAppCategories(d.application);
			}
			separateMultipleItems($(e).find("span.app-category"));

			//Hit count
			if(d.application.hitcount){
				$(e).find("span.app-hitcount").text(" ( " + d.application.hitcount + " hits )");
			}else{
				$(e).find("span.app-hitcount").text("");
			}

			//name
			$(e).find("span.app-name").html(d.application.name.htmlEscape());
			$(e).find("span.app-displayname").html(d.application.name.htmlEscape());
			//description
			$(e).find("span.app-desc").html(d.application.description.replace(/\<\/*\w+ *(\w+(=['"][a-zA-Z0-9:;\-_#! \.]*["']){0,}){0,}\/{0,}\>/g,"").htmlEscape());
			//disciplines
			var disciplines = d.application.discipline;
			managedDisciplines = [];
			if ( ! $.isArray(disciplines) ) disciplines = (disciplines)?[disciplines]:[];
			managedDisciplines = disciplines;
			$(e).find("span.softwaredisciplines").remove();
			$(e).find("span.app-domains").after("<span class='app-softwaredisciplines'></span>");
			renderSoftwareDisciplines(d.application);
			if ( !d.application.discipline ){
				$(e).find("app-softwaredisciplines").append(appdb.config.views.all.unspecified);
			}


			separateMultipleItems($(e).find("span.app-domain"));

			//subdisciplines
			var subdisciplines = d.application.subdiscipline;
			if ( subdisciplines ) {
				if ( ! $.isArray(subdisciplines) ) subdisciplines = (subdisciplines)?[subdisciplines]:[];
				for (i=0; i<subdisciplines.length; i++) {
					if (subdisciplines[i] && subdisciplines[i].val) $(e).find("span.app-subdomains").append($('<span class="app-subdomain"><a href="#" onclick="appQuickLink('+subdisciplines[i].id+',5,{mainTitle : \''+subdisciplines[i].val()+'\'});">'+subdisciplines[i].val()+'</a></span>'));
				}
			} else {
				$(e).find("span.app-subdomains").append(appdb.config.views.all.unspecified);	
			}
			separateMultipleItems($(e).find("span.app-subdomain"));
			//moderated
			if (d.application.moderated) {
				if (d.application.moderated == "true") showAppModInfo(e,d);
			}
			//deleted
			if (d.application.deleted) {
				if (d.application.deleted == "true") showAppDelInfo(e,d);
			}
			//abstract
			var atx = d.application["abstract"]|| "";
			atx = atx.replace(/<BR *> *<\/BR *>/ig,"\n").replace(/<BR *\/>/ig,"\n").replace(/<BR *>/ig,"\n");
			atx = atx.replace(/\<\/*\w+ *(\w+(=['"][a-zA-Z0-9:;\-_#! \.]*["']){0,}){0,}\/{0,}\>/g,"").htmlEscape();
			$(e).find("pre.app-abstract").text(atx);

			//display description (only view)
			if( ($.trim(d.application.description) === $.trim(d.application.name) ) ||
				($.trim(d.application.description) === "" ) ) {
				if($.trim(d.application["abstract"]) === ""){
					$(e).find("span.app-description").html("");
				}else{
					$(e).find("span.app-description").html( atx.substr(0,80) + ( (atx.length>80)?"...":"" ) );
				}
			} else {
				var desc = d.application.description.replace(/<BR *> *<\/BR *>/ig,"\n").replace(/<BR *\/>/ig,"\n").replace(/<BR *>/ig,"\n");
				desc = desc.replace(/\<\/*\w+ *(\w+(=['"][a-zA-Z0-9:;\-_#! \.]*["']){0,}){0,}\/{0,}\>/g,"").htmlEscape();
				if( desc.length > 200 ){
					desc = desc.substr(0,197) + "...";
					$(e).find("span.app-description").html(desc);
				}else{
					$(e).find("span.app-description").html($(e).find("span.app-desc").html());
				}

			}

			//status
			$(e).find("span.app-status").html(d.application.status.val());
			if(managedAppUrls){
				managedAppUrls.destroy();
				managedAppUrls = null;
			}
			if ( d.application.url ) {
				$(e).find("span.app-urls").empty();
				var urls = d.application.url;
				if ( !$.isArray(urls) ) urls = [urls];
				//TODO : replace with managed code
				$(e).find("span.app-urls").append("<div id='applicationurlscontainer' class='hideonedit'></div>");
				if(managedAppUrls){
					managedAppUrls.destroy();
					managedAppUrls = null;
				}
				managedAppUrls = new appdb.views.ApplicationUrls({container : $(e).find("span.app-urls > #applicationurlscontainer").first()});
				managedAppUrls.render(urls);
				$(e).find("span.appurltitle").addClass("hasurls");
			}else{
				$(e).find("span.appurltitle").removeClass("hasurls");
			}
			if ( d.application.country ) {
				$(e).find("span.app-countries").empty();
				var c = d.application.country;
				var inh;
				if (! $.isArray(c)) c = (c)?[c]:[];
				for(i=0;i<c.length;i++) {
					if ( c[i].inherited == "true" ) inh = ' inherited-country'; else inh = '';
					var cflag = '<span class="app-country'+inh+'"><a href="#" data-id="' + c[i].id + '">'+c[i].val()+'</a>'+'</span> <span class="app-countryFlag">';
					var ciso = c[i].isocode.toLowerCase().split("/");
					for (j=0;j<ciso.length;j++) {
						cflag = cflag + '<img border="0" src="/images/flags/'+ciso[j]+'.png" style="vertical-align: middle;border:1px solid #BFBFBF"/> ';
					}
					cflag = cflag + '</span> ';
					$(e).find("span.app-countries").append(cflag);
				}
				separateMultipleItems($(e).find("span.app-countryFlag"));
				setupApplicationCountries(e);
			} else {
				$(e).find("span.app-countries").append(appdb.config.views.all.unspecified);
			}
			populateAppVos(e,d.application);
			if ( d.application.middleware ) {
				$(e).find("span.app-mws").empty();
				v = d.application.middleware;
				if ( ! $.isArray(v)) v = (v)?[v]:[];
				for(i=0;i<v.length;i++) {
					$(e).find("span.app-mws").append('<span class="app-mw'+((v[i].id == 5)?" other":"")+'"><a class="mwname" >'+(v[i].comment || v[i].val()).htmlEscape()+ "</a>" + ((v[i].link)?'<a href="'+v[i].link+'" class="mwhome" target="_blank" title="Visit '+(v[i].val()).htmlEscape()+' web site" class="mwlink"><img src="/images/homepage.png" /></a>':'')+'</span>');
				}
				separateMultipleItems($(e).find("span.app-mw"));
				setupApplicationMiddlewares(e);
			} else {
				$(e).find("span.app-mws").append(appdb.config.views.all.unspecified);
			}

			if( d.application.language ){
				$(e).find("span.app-proglangs").empty();
				v = d.application.language;
				v = $.isArray(v)?v:[v];
				for(i=0; i<v.length; i++){
					$(e).find("span.app-proglangs").append('<span class="app-proglang"><a href="#" data-id="'+v[i].id+'">'+v[i].val().htmlEscape()+'</a></span>');
				}
				separateMultipleItems($(e).find("span.app-proglangs > span.app-proglang"));
				setupApplicationProglangs(e);
			} else {
				$(e).find("span.app-proglangs").append(appdb.config.views.all.unspecified);
			}
			if( appdb.pages.application.isVirtualAppliance() === false ){
				appdb.pages.application.currentSoftwareLicenses(null);
				v = d.application.license || [];
				v = $.isArray(v)?v:[v];
				appdb.pages.application.currentSoftwareLicenses().render(v);
				$(e).find("a[href='#vausage']").parent().addClass("hidden");
				$(e).find("#vausage").addClass("hidden");
			}else{
				$(e).find("span.app-licenses").empty();
				$(e).find("a[href='#swlicenses']").parent().addClass("hidden");
				$(e).find("#swlicenses").addClass("hidden");
			}
			if( appdb.pages.application.isSoftwareAppliance() ){
				$(e).find(".technnicaldetails").addClass("hidden");
				$(e).find("a[href='#vausage']").parent().removeClass("hidden");
				$(e).find("#vausage").removeClass("hidden");
			}

			var d1 = d.application.addedOn.split("T");
			if(d1.length>0 && typeof d1[0] === "string"){
				d1 = d1[0].split("-");
			}
			if(d1.length===3){
				d1 = ''+d1[0]+'-'+d1[1]+'-'+d1[2];
			} else {
				d1 = "";
			}
			$(e).find("span.app-addedon").html(appdb.utils.FormatISODate(d1));
			if ( d.application.addedby ) {
				if ( d.application.addedby.id !== undefined ) {
					$(e).find("span.app-addedby").html('<a href="'+((d.application.addedby.cname)?("/store/person/"+d.application.addedby.cname):'#')+'" onclick="appdb.views.Main.showPerson({id: '+d.application.addedby.id+',cname:\''+d.application.addedby.cname+'\'},{mainTitle: \''+d.application.addedby.firstname+" "+d.application.addedby.lastname+'\'});" target="_blank">'+d.application.addedby.firstname+" "+d.application.addedby.lastname+'</a>');
				}
			}
			if ( d.application.owner ) {
				if ( ! d.application.addedby || d.application.owner.id != d.application.addedby.id ) {
					$(e).find(".app-owner-container").removeClass("hidden");
					if ( d.application.owner.id !== undefined ) {
						$(e).find("span.app-owner").html('<a href="'+((d.application.owner.cname)?("/store/person/"+d.application.owner.cname):'#')+'" onclick="appdb.views.Main.showPerson({id: '+d.application.owner.id+',cname:\''+d.application.owner.cname+'\'},{mainTitle: \''+d.application.owner.firstname+" "+d.application.owner.lastname+'\'});" target="_blank">'+d.application.owner.firstname+" "+d.application.owner.lastname+'</a>');
					}
				} else {
					$(e).find("span.app-owner").html('<a href="'+((d.application.addedby.cname)?("/store/person/"+d.application.addedby.cname):'#')+'" onclick="appdb.views.Main.showPerson({id: '+d.application.addedby.id+',cname:\''+d.application.addedby.cname+'\'},{mainTitle: \''+d.application.addedby.firstname+" "+d.application.addedby.lastname+'\'});" target="_blank">'+d.application.addedby.firstname+" "+d.application.addedby.lastname+'</a>');
				}
				$(e).find("span.app-owner-view").html($(e).find("span.app-owner").html());
			} else {
				$(e).find(".app-owner-container").addClass("hidden");
			}
			if ( d.application.lastUpdated ) {
				d1 = d.application.lastUpdated.split("T");
				if(d1.length>0 && typeof d1[0] === "string"){
					d1 = d1[0].split("-");
				}
				if(d1.length===3){
					d1 = ''+d1[0]+'-'+d1[1]+'-'+d1[2];
				} else {
					d1 = "";
				}
				$(e).find("span.app-lastupdated").html(appdb.utils.FormatISODate(d1));
				if (d.application.validated == "false" && (appdb.config.views.application.displayInvalidatedText !== false)) {
					var canViewValidationBox = (appdb.config.views.application.displayInvalidatedText === true)?true:false;
					canViewValidationBox = canViewValidationBox || ( (appdb.config.views.application.displayInvalidatedText == "owner" && d.application.owner && userID == d.application.owner.id)?true:false );
					canViewValidationBox = canViewValidationBox || userIsAdminOrManager;
					if(canViewValidationBox === true){
						$(e).find(".invalidatedhint").attr("style","display:none");
						$(e).find(".app-extrainfo").append('<span class="smallinvalidatedhint">more than '+appdb.config.appValidationPeriod+' since the last update</span>');
						if(userID !== null && ( (d.application.owner && userID == d.application.owner.id) || userIsAdminOrManager ) ){
							$(e).find(".app-extrainfo span.smallinvalidatedhint").append("<div id='validateApp"+d.application.id+"' ><button /><img src='/images/question_mark.gif' border='0'/></div>");
						setTimeout(function(){
							if(dijit.byId("validateApp"+d.application.id)){
								dijit.byId("validateApp"+d.application.id).destroyRecursive(true);
							}
							var vbtn = new dijit.form.Button({
								label: "Validate",
								onClick: (function(_appid){ 
									return function(){
										var model = new appdb.model.ApplicationValidation();
										model.subscribe( {event: "update", callback: function(v){
												if(v.response && v.response === "success"){
													setTimeout(function(){
														$(e).find(".app-extrainfo span.smallinvalidatedhint").addClass("validation").addClass("success").empty().append("<img src='/images/yes.png' border='0'/><span>success</span>").fadeOut(2000);
													},1);
												}
										}} ).subscribe( {event: "error", callback: function(v) {
											dojo.style(vbtn.domNode, {display: "inline-block"});
											$(vbtn.domNode).parent().find("img.validating").remove();
											(new appdb.views.ErrorHandler()).handle({status: "Could not validate software", description: v.error});
										}} );
										model.update({data: {id: _appid}});
										$(vbtn.domNode).after("<img class='validating' src='/images/ajax-loader-small.gif' border='0' style='width:12px;height:12px' />");
										dojo.style(vbtn.domNode, {display: "none"});
									};
								})(d.application.id)
							},$(e).find(".app-extrainfo span.smallinvalidatedhint > div#validateApp"+d.application.id +" > button")[0]);
							new dijit.Tooltip({
								connectId: [$(e).find(".app-extrainfo span.smallinvalidatedhint #validateApp"+d.application.id + " img")[0]],
								label: "<span class='validateAppButtonDescription'><span style='padding-left:10px;'/>According to our system, your software " + d.application.name + " has not been " +
									"updated during the past "+appdb.config.appValidationPeriod+". It is a policy of EGI " + 
									"to mark such software entries as outdated and demote them in search " + 
									"results, in order to warn users that the information might not be " +
									"up-to-date. <br/><span style='padding-left:10px;'/>In order to remove the outdated mark from your software " +
									"entry, edit the software, performing any needed updates, or press on the 'Validate' " +
									"button, if the data is still valid.</span>",
								position: ["above"],
								showDelay: 200
							});
						},500);
						}
					}
				}
			}
			if ( d.application.owner && d.application.owner.id ) $(e).find("span.app-owner").attr("data-selected", d.application.owner.id);
			if(managedSciCons){
				managedSciCons.destroy();
				managedSciCons = null;
			}
			if(typeof d.application.contact === "undefined"){
				d.application.contact=[];
				$(e).find("#mainscicondiv"+dialogCount +" .mainscicontitle").hide();
			}else{
				d.application.contact = d.application.contact || [];
				d.application.contact = $.isArray(d.application.contact)?d.application.contact:[d.application.contact];
				$(e).find("#mainscicondiv"+dialogCount +" .mainscicontitle .scicontoggler").after("<span style='font-weight:normal'> (" + d.application.contact.length + ")</span>");
			}
			var managedSciConsStickyItems = null;
			if( $("#managedSciConDiv").find(".stickyitem").length > 0 ){
				managedSciConsStickyItems = $("#managedSciConDiv").find(".stickyitem").clone();
			}
			managedSciCons= new appdb.views.RelatedContactList({container : $("#managedSciConDiv"),permissions:d.application.permissions,data:d.application,useToggleButton:true, nodata: "<span>No related contacts specified</span>"});
			managedSciCons.render();
			if( managedSciConsStickyItems !== null ){
				$("#managedSciConDiv").prepend(managedSciConsStickyItems);
			}
			managedSciCons.subscribe({event : "itemselected", callback : function(v){
					var item = v.item;
					if(item._isSelected==true){
						v.item._itemData.toRemove = true;
						$(item.dom).addClass("toberemoved");
					}else{
						$(item.dom).find(".selectedtext:last").text("");
						$(item.dom).removeClass("toberemoved");
						v.item._itemData.toRemove = false;
					}
			},caller : managedSciCons});

			delete userAppPrivs;
			userAppPrivs = null;
			if ( userIsAdminOrManager ) {
				var appmod = $($.find("a.app-mod"));
				if ( d.application.moderated ) {
					if ( d.application.moderated == "true" ) {
						appmod.find("span").text('Unmoderate');
					}
				}
				appmod.click(function(){
					moderateApplication(d.application.id,d.application.moderated);
				});
				appmod.show();
			}
			var perms = d.application.permissions || null;
			if ( perms !== null ) {
				var a;
				var acts = perms.action || null;
				if (acts !== null) {
					userAppPrivs = new Array();
					if (!$.isArray(acts)) {
						a = new Array();
						a.push(acts);
					} else {
						a = acts;
					}
					for (i=0;i<a.length;i++) {
						if (id != 0) {
							if(a[i].id>4 && a[i].id<18){ //Can edit contents of application
								$($.find("a.app-edit")).show();
							}
							var appDeleted = d.application.deleted || false;
							if ( ! appDeleted ) {
								if ( a[i].id == 4 ) $($.find("a.app-del")).show();
							}
						} 
						userAppPrivs.push(a[i].id);
						makeAppElementEditable(e,a[i].id);
					}
				}
			}

			if ( userID !== null ) {
				$($.find("a.app-history")).show();
				if ( (userIsAdminOrManager) || ((d.application.owner && (userID == d.application.owner.id)) || (d.application.addedby && (userID == d.application.addedby.id))) ) {
					$($.find("span.app-rollback")).show();
				} else {
					$($.find("span.app-rollback")).hide();
				}
			}
			setTimeout(function(){
				$(e).find("div.reportAbuse a").click(function(){
					if( $(this).hasClass("helperlink") ) return true;
					if (userID !== null) {
						appdb.views.Main.showReportAbuse({id: d.application.id ,type:'application',name:d.application.name});
					} else {
						var reportAbuseDlg = $('<div title="Report a Problem"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>If you wish to file a report about a problem regarding this software, please <b>Sign In</b> first with your account.<br/></p></div>');
						try{
							reportAbuseDlg.dialog({
								dialogClass: 'info',
								autoOpen: false,
								resizable: false,
								width:400,
								height:190,
								modal: true,
								buttons: {
									OK: function() {
										$(this).dialog('close');
									}
								}
							});	
							setTimeout(function(){
								try{$(reportAbuseDlg).dialog("open");}catch(e){}
								$("body").find(".ui-widget-overlay").css({"width":"100%","height":"100%","position":"fixed"});
							},10);

						} catch(e) {}

						reportAbuseDlg.find("a.login").click(function(){
							reportAbuseDlg.dialog('close');
							login();
						});
					}
				});
				$(e).find("div.requestjoin a").click(function(){
					if( $(this).hasClass("helperlink") ) return true;
					if( userID !== null ) {
						appdb.views.Main.showRequestJoinContacts(d.application);
					}else{
						var reportAbuseDlg = $('<div title="Request to join software\'s contacts"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>If you wish to join as a contact for a software, please <b>Sign In</b> first with your account.<br/></p></div>');
						try{
							reportAbuseDlg.dialog({
								dialogClass: 'info',
								autoOpen: false,
								resizable: false,
								width:400,
								height:190,
								modal: true,
								buttons: {
									OK: function() {
										$(this).dialog('close');
									}
								}
							});	
							setTimeout(function(){
								try{$(reportAbuseDlg).dialog("open");}catch(e){}
								$("body").find(".ui-widget-overlay").css({"width":"100%","height":"100%","position":"fixed"});
							},10);
						} catch(e){}

						reportAbuseDlg.find("a.login").click(function(){
							reportAbuseDlg.dialog('close');
							login();
						});
					}
				});
				$(e).find("div.sendmessage a").unbind("click").bind("click",function(){
					if( $(this).hasClass("helperlink") ) return true;
					if( userID !== null ) {
						appdb.views.Main.showSendMessageToContacts(d.application.id);
					}else{
						var reportAbuseDlg = $('<div title="Send message to software\'s contacts"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>If you wish to send a message to software contacts, please <b>Sign In</b> first with your account.<br/></p></div>');
						try{
							reportAbuseDlg.dialog({
								dialogClass: 'info',
								autoOpen: true,
								resizable: false,
								width:400,
								height:190,
								modal: true,
								buttons: {
									OK: function() {
										$(this).dialog('close');
									}
								}
							});
							setTimeout(function(){
								try{$(reportAbuseDlg).dialog("open");}catch(e){}
								$("body").find(".ui-widget-overlay").css({"width":"100%","height":"100%","position":"fixed"});
							},10);
						} catch(e){}

						reportAbuseDlg.find("a.login").click(function(){
							reportAbuseDlg.dialog('close');
							login();
						});
					}
				});
			},10);
			$("#ratingdiv"+dialogCount).append('<div id="ratingreport" style="width:100%" ></div>');
			ratingReport = new appdb.components.RatingReport({container:$("#ratingreport")});
			ratingReport.load({query: {type:"both",appid:d.application.id}});
			populateTags(id,e,d);
			populateRatings(id,e,d);
			makeAppDocs(d);
			
			if(dijit.byId("ratingdiv"+dialogCount)) dijit.byId("ratingdiv"+dialogCount).resize();
			if ( detailsStyle == 1 ) {
				var ee = $(e).parent().parent().parent().parent();
				ee.css("position","absolute");
				ee.css("top", ( $(window).height() - ee.height() ) / 2+$(window).scrollTop() + "px");
				ee.css("left", ( $(window).width() - ee.width() ) / 2+$(window).scrollLeft() + "px");
			}
			(new appdb.components.EntrySubscription({
			 container: $( "#navdiv"+dialogCount+" .followme" ),
			 entryid: d.application.id,
			 subjecttype: "app-entry",
			 name: d.application.name + " Subscription"
			}));
			$(".relatedapps:last").parent().append();
			if(!(d.application && d.application.relcount && d.application.relcount != "0")){
				$(e).find(".releasedownloadbutton").remove();
			}else{
				$(e).find(".releasedownloadbutton").removeClass("hidden");
			}
			var sidebar = new appdb.views.SideBar({
				container : $("#infodiv"+dialogCount).find(".sidebar:last")[0],
				items : []
			});
			sidebar.render();
			appdb.pages.application.onApplicationLoad();
			delete dat;
			//Abstract hide/show in case of overflow
			setTimeout((function(elem){
				return function(){
					if( Math.ceil($(elem).find("pre.app-abstract").height() / 12 /*line height*/) > 13 /*visible lines*/){
						$(elem).find("pre.app-abstract").parent().addClass("hasmore").append("<a href='#' title='Show more' class='more'><span>show more</span></a>");
						$(elem).find("pre.app-abstract").parent().find('a.more').unbind("click").bind("click", function(ev){
							ev.preventDefault();
							$(this).parent().removeClass("hasmore").addClass("showall");
							return false;
						});
					}
				};
			})(e),10);
			$(e).show();
		} else { //record not found
			$(e).parent().parent().parent().html('<div class="recordnotfound emptycontent"><div class="content"><img alt="" src="/images/error.png" /><span>Record not found or removed.</span></div></div>');
			$(".appviewtoolbar").hide(); 
		}
		setTimeout(hideAjaxLoading,10);
	}
	function populateAppRegistrationDetails(e){
		appdb.pages.application.currentData({application: {id:'0'}});
		appdb.pages.application.SetupNavigationPane();
		
		delete userAppPrivs;
		userAppPrivs = new Array(3,5,6,7,8,9,10,11,12,13,14,15,16,17,20,23,24,26);
		for(i=0;i<userAppPrivs.length;i++) {
			makeAppElementEditable(e,userAppPrivs[i]);
		}
		$( "#editapp"+dialogCount).addClass("editmode");
		$(e).show();
		$($.find("a.app-bm")).hide();
		switch(appdb.pages.Application.currentEntityType()){
			case "virtualappliance":
				$(e).find("img.app-logo").attr("src",appdb.config.images["virtual appliances"]);
				break;
			case "softwareappliance":
				$(e).find("img.app-logo").attr("src",appdb.config.images["software appliances"]);
				break;
			case "software":
			default:
				$(e).find("img.app-logo").attr("src",appdb.config.images["applications"]);
				break;
		}
		$(e).find("span.app-name").text("");
		$(e).find("span.app-desc").text("");
		$(e).find("pre.app-abstract").html(" ");

		$(e).find("span.app-countries").prev().prev().remove();
		$(e).find("span.app-countries").prev().remove();
		$(e).find("span.app-countries").remove();
		var canSetOwner = false;
		if ( ! canSetOwner ) { 
			$(e).find("span.app-addedby").remove();
			$(e).find("span.app-owner").remove();
		} else {
			$(e).find("span.app-addedby").remove();
			$(e).find("span.app-owner").prev().append("Added by ");
			$(e).find("span.app-owner").text(userFullname);
		}
		$("div.reportAbuse").hide();
		setTimeout(hideAjaxLoading,10);
	}
	function populateAppDetails(e,id, histid, histtype,data) {
		var _endpoint = "";
		var u = '', cid = 0;
		$(e).hide();
		$($.find("a.app-mod")).hide();
		$($.find("a.app-del")).hide();
		$($.find("a.app-edit")).hide();
		$($.find("a.app-history")).hide();
		if ( userID != null ) {
			u = "?userid="+userID+"&passwd="+$.cookie('scookpass');
			u += "&cid="+cid+"&src="+reqsrc;
		} else {
			$($.find("a.app-bm")).hide();
			u = "?cid="+cid+"&src="+reqsrc;
		}
		var dat;
		if ( $.inArray($.trim(id),["","0"]) === -1 ) {	// show existing app
			showAjaxLoading();
			if( data ){
				populateAppDataDetails(data,e,id, histid, histtype);
				return;
			}else{
				_endpoint = _endpoint+"applications/"+id;
				if ( typeof histid !== 'undefined' ) _endpoint = _endpoint+"/history/"+histid;
				dat = new appdb.utils.rest({
					endpoint: appdb.config.endpoint.proxyapi+"?version=" + appdb.config.apiversion + "&resource="+_endpoint,
					async: true
				}).create({},{
					success: function(d) {
						showAjaxLoading();
						setTimeout(function() {
							$( "#navdiv"+dialogCount).tabs("select",window.apptabsselect || 0);
						}, 1);
						populateAppDataDetails(d,e,id,histid,histtype);
					},
					error: function(d){
						hideAjaxLoading();
					}
				}).call();
				delete dat;
			}
		} else {	// register new application
			populateAppRegistrationDetails(e);
		}
	}

	function hideRatingTab() {
		var navdiv = $("#navdiv"+dialogCount);
		if(navdiv.length>0){
			$(navdiv).tabs("remove",2);
		}
	}
	function hidePublicationTab(){
		var navdiv = $("#navdiv"+dialogCount);
		if(navdiv.length>0){
			$(navdiv).tabs("remove",1);
		}
	}

	function onAppEdit(entryid, attempts) {
			$('editapp'+dialogCount).addClass("editmode");
			$( "#navdiv"+dialogCount).addClass("editmode").removeClass("viewmode");
			$("#details").addClass("editmode").removeClass("viewmode");
			if(! entryid ){
				$( "#navdiv"+dialogCount).addClass("register");
			}
			$($.find("a.app-edit")).hide();
			$("span.app-hitcount").hide();
			$("span.inherited-country").next().remove();$("span.inherited-country").remove();
			$("#vodiv"+dialogCount).find("span[edit_name='vo']").attr("edit_data",voData);
			$("#mwdiv"+dialogCount).find("span[edit_name='mw']").attr("edit_data",mwData);
			$("#proglangdiv"+dialogCount).find("span[edit_name='proglangID']").attr("edit_data",proglangData);
			$("span[edit_name='statusID']").attr("edit_data",statusData);
			$("span[edit_name='countryID']").attr("edit_data",countryData);
			$("span[edit_name='domainID']").attr("edit_data",domainData);
			$("span[edit_name='owner']").attr("edit_data",peopleData);
			$("span[edit_name='categoryID']").attr("edit_data",categoriesData);
			$("a.relatedapps").click();
			$("div.altRelatedApps").hide();
			$("div.relatedapps").parent().empty().css("border-left","");
			$("div.relatedapps").hide();
			if (hasAppPriv(20)) registrerCustomMiddleware();
			try{
				editForm('editapp'+dialogCount);
			}catch(e){
				attempts = attempts || 1;
				if(!entryid && attempts<3){
					appdb.debug("App Edit Failed....Trying Again!");
					setTimeout(function(){
						attempts +=1;
						onAppEdit(null, attempts);
					},200);
				}
				if(attempts>3){
					appdb.debug("Max App edit attempts reached......");
					return;
				}
				
			}
			
			if ( ! entryid ) { 
				if ( $("#newapphint"+dialogCount)[0] == undefined )
					$("#navdiv"+dialogCount).parent().height("90%");
					$("#navdiv"+dialogCount).height("90%");
					$(".detailsdlgcontent:last").height("90%");
					if( appdb.pages.application.isVirtualAppliance() ){
						$(".detailsdlgcontent:last").prepend("<span id='newapphint"+dialogCount+"' class='newapphintcontainer'><div class='newapphint'><div><b>Hint</b>: <b style='color:#333;'>Manage Images</b> as well as additional information like: Prog. Laguages, Countries, middleware(s), Tags, People etc, will be available for edit after saving...</div></div></span>");
					}else if( appdb.pages.application.isSoftwareAppliance() ){
						$(".detailsdlgcontent:last").prepend("<span id='newapphint"+dialogCount+"' class='newapphintcontainer'><div class='newapphint'><div><b>Hint</b>: <b style='color:#333;'>Contextualization scripts</b> as well as additional information like: Licenses, Countries, Tags, People etc, will be available for edit after saving...</div></div></span>");
					}else{
						$(".detailsdlgcontent:last").prepend("<span id='newapphint"+dialogCount+"' class='newapphintcontainer'><div class='newapphint'><div><b>Hint</b>: additional information like: Prog. Laguages, Countries, middleware(s), Tags, People etc, will be available for edit after saving...</div></div></span>");
					}
					
			}
			$('#regiondiv'+dialogCount).hide();
			$(".ngirow").hide();    
			if (entryid == null || hasAppPriv(26)) editAppCategories((entryid)?false:true);
			if (hasAppPriv(12)) $('#addcountry'+dialogCount).show();
			if (hasAppPriv(13)) editSoftwareDisciplines((entryid)?false:true);
			if (hasAppPriv(13)) $('#addvos'+dialogCount).show();
			if (hasAppPriv(14)) editAppUrls();
			if (hasAppPriv(15)) $('#editdoc'+dialogCount).show();
			if (hasAppPriv(16)) editSciCon();
			if (hasAppPriv(20)) $('#addmws'+dialogCount).show(); 
			if (hasAppPriv(8)) {
				if ( $('#uploadlogo'+dialogCount).length === 0 ) {
					$('#applogo'+dialogCount).parent().html($('#applogo'+dialogCount).parent().html()+'<div id=\'uploadlogo'+dialogCount+'\'>Upload image...</div>');
				}
				$('#applogo'+dialogCount).attr('onmouseover',''); 
				$('#applogo'+dialogCount).attr('onmouseout',''); 
				prepareUpload();
			}
			if( hasAppPriv(31) ){
				$("#addproglangs"+dialogCount).show();
			}
			$(".listhandlercontainer").addClass("editmode");
			//TODO : replace with managed code
			$("span.app-urls:last > .app-url").append("<br/>").show();
			$(".hideonedit").hide();
			$(":input[name='documents']:last").after("<input type='hidden' name='initialDocuments'/>");
			$(":input[name='initialDocuments']:last").val(serializeAppDocs());
			if(! entryid){
				if(managedSciCons !== null){
					managedSciCons.destroy();
					managedSciCons = null;
				}
				if(managedAppUrlsEditor !== null){
					managedAppUrlsEditor.destroy();
					managedAppUrlsEditor = null;
				}
			}
			appdb.utils.DataWatcher.Registry.activate("application");
			initCategoriesChecker();
			renderLists();
			initCharacterCounter();
			appdb.pages.application.onAppEdit();
	}

	function appDocDataToJSON(data) {
		var _intAuthors = [];
		var auths = "<span>"+data[10].toString()+"</span>";
		$(auths).find("a").each(function(e){
			_intAuthors.push([$(this).attr("data-authorid"), $(this).attr("data-authorMain")]);
		});
		var _extAuthors = [];
		$(auths).find("span").each(function(e){
			_extAuthors.push([$(this).attr("data-authorname"), $(this).attr("data-authorMain")]);
		});
		return data2={
			'id': $(data[0][0]).attr("data-docid"),
			'title': $(data[0][0]).text(),
			'url': $(data[0][0]).find("a").attr("href"),
			'type': $(data[1][0]).text(),
			'typeID': $(data[1][0]).attr("data-doctypeid"), 
			'conference': data[2],
			'volume': data[3],
			'pageStart': (data[4].split(' - '))[0],
			'pageEnd': (data[4].split(' - ')[1]),
			'year': data[5],
			'publisher': data[6],
			'isbn': data[7],
			'proceedings': data[8],
			'journal': data[9],
			'intAuthors': _intAuthors,
			'extAuthors': _extAuthors
		};
	}
	function serializeAppDocs(){
	  if ( docgrid && docgrid.model !== undefined ) {
		 var data;
		 var allData='<documents>';
		 for (i=0; i<docgrid.model.count; i=i+1) {
			 data=docgrid.model.getRow(i);
			 var data2 = JSON.stringify(appDocDataToJSON(data));
			 allData=allData+'<document>'+appdb.utils.base64.encode(encodeURI(data2),false)+'</document>';
		 }
		 allData=allData+'</documents>';
		 return allData;
	 } else return 'UNCHANGED';
	}
	function validateApp() {
		var i, found = false, invalid = [], mandatory = {
			"name" : "name",
			"description" : "description",
			"abstract":"abstract",
			"statusID" : "status"
		}, lists = {
			"vo" :  {name: "vo"},
			"countryID" :  {name: "country"},
			"mw" :  {name: "middleware"},
			"lmw" : {name: "custom middleware link"},
			"domainID" : {name: "discipline", required: true, witness: "adddomains"},
			"subdomainID" : {name: "subdiscipline"},
			"owner" : {name : "owner"}
		};
		
		for(i in mandatory){
			if ( $(':input[name="'+i+'"]:last').length !== 0 ) {
				if ($.trim($(':input[name="'+i+'"]:last').val()) === '' ) {
					invalid[invalid.length] = mandatory[i];
				}
			}
		}
		for(i in lists){
			found = false;
			if(lists[i].required && ($('#details :input[name^="'+i+'"]').length === 0 || ($('#details :input[name^="'+i+'"]').length===1 && $($('#details :input[name^="'+i+'"]')[0]).val() == ""))) {
				if ( $("[id^='" + lists[i].witness + "']:visible").length !== 0 ) found = true;
			} 
			if(found == true){
				invalid[invalid.length] = lists[i].name;
			}
		}
		if(managedAppCategoriesEditor !== null){
		 if(managedAppCategoriesEditor.isValid() !== true){
		  invalid[invalid.length] = "Category values";
		 }
		}
		
		if(managedDisciplinesEditor !== null){
		 if(managedDisciplinesEditor.isValid() !== true){
		  invalid[invalid.length] = "Discipline values";
		 }
		}
		var lic = appdb.pages.application.currentSoftwareLicenses();
		if( lic ){
			if( lic.getInvalidItems().length > 0 ){
				invalid[invalid.length] = "License values";
			}
		}		
		
		if(invalid.length>0){
			var html = '<div title="Error"><div><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Please fill the mandatory field'+((invalid.length>1)?'s':'')+'  displayed bellow:</p></div><ul style="padding:0px;margin:0px;margin-left:35px;">';
			for(i=0; i<invalid.length; i+=1){
				html += "<li style='padding:0px;margin:0px;padding-left:5px;'>" + invalid[i] + "</li>";
			}
			html += '</ul></div>';
			$(html).dialog({
				dialogClass: 'alert',
				autoOpen: true,
				resizable: false,
				modal: true,
				buttons: {
					OK: function() {
						$(this).dialog('close');
					}
				}
			});	
			return false;
		}
		$(':input[name="documents"]:last').val(serializeAppDocs());
		$("#details").fadeOut();
		$(".app-mw").each(function(){
			$(this).find(":input:last").val($("<span>"+$(this).find(":input:last").val()+"</span>").text());
		});
		return true;
	}

	function addDoc(data) {
		delete editDocDlg;
		editDocDlg = new dojox.Dialog({
			"id" : "editDocDialog",
			"title": "Edit publication data",
			"style": "width: 60%",
			onCancel : function(){
				this.destroyRecursive(false);
			}
		});
		if ( data !== undefined ) {
			var data2=appDocDataToJSON(data);
			editDocDlg.setHref("/apps/editdoc?data="+encodeURIComponent(JSON.stringify(data2)));
		} else editDocDlg.setHref("/apps/editdoc");
		editDocDlg.show();
		try {
			dojo.disconnect(dijit.byId("detailsdlg"+dialogCount)._modalconnects.pop());
		} catch(e) {}
	}

	function remDoc() {
        if (docgrid.selection.getFirstSelected() >=0 ) docgrid.removeSelectedRows();
	}
	
	function editDoc() {
        var r=docgrid.selection.getFirstSelected();
        if (Number(r) >= 0 ) addDoc(docgrid.model.getRow(r));
	}

	function toggleBookmark(appID){
		if(typeof appID === "undefined"){
			return;
		}
		var verb = ($('#toolbarContainer img[src^="/images/star"]').attr('src').substr(12,1) === "1")?"PUT":"DELETE";
		var bookmarks = appdb.model.ApplicationBookmarks();
		bookmarks.subscribe({"event": "insert", "callback": function(d){
			$('#toolbarContainer .app-bm img:first').remove();
			$('#toolbarContainer img[src^="/images/star"]').show();
			$('#toolbarContainer img[src^="/images/star"]').attr('src','/images/star2.png');
		}}).subscribe({"event": "remove", "callback": function(d){
			$('#toolbarContainer .app-bm img:first').remove();
			$('#toolbarContainer img[src^="/images/star"]').show();
			$('#toolbarContainer img[src^="/images/star"]').attr('src','/images/star1.png');
		}}).subscribe({"event": "error", "callback": function(d){
			//Called upon ajax error of application moderation action. E.g. HTTP 500 - Internal Server Error
			$('#toolbarContainer .app-bm img:first').remove();
			$('#toolbarContainer img[src^="/images/star"]').show();
			var err = {
				"status": "Cannot toggle bookmark",
				"description": d.description
			};
			//Setup error according to rest response type
			if(d && d.response){
				if(typeof d.response === "string" && $.trim(d.response) !== ""){
					err.description = d.response;
				}else if(d.response.error){
					err.description = d.response.errordesc;
				}
			}
			if($.trim(err.description)!==""){
				err.description = err.description.replace(/\</g,"&lt;").replace(/\>/g,"&gt;");
				var m = err.description.match(/^[\w\s]*\./);
				if(m && m.length>0){
					err.description = err.description.replace(/^[\w\s]*\./,"<div><b>" + m[0] + "</b></div><div style='text-align:justify'><p><span style='display:inline-block;width:10px;'>   </span>") + "</p></div>";
				}
			}
			//Display backend error message
			setTimeout(function(){(new appdb.views.ErrorHandler()).handle(err);},0);
		}});
		$('#toolbarContainer img[src^="/images/star"]').hide();
		$("#toolbarContainer .app-bm").prepend('<img src="' + loadImage('ajax-loader-small.gif') + '"/>');
		if(verb === "PUT"){
			var ent = new appdb.entity.ApplicationBookmark();
			ent.id(appID);
			var xml = appdb.utils.EntitySerializer.toXml(ent);
			appdb.debug(xml);
			bookmarks.insert({query: {"id": userID}, data: {data: xml}});
		}else {
			bookmarks.remove({"id": userID, "appid": appID});
		}
	}

	var flagImages = new Array();
	function populateFlagImages() {
		jQuery.support.cors = true;
		var regional = appdb.model.Regional;
		for (i in regional.country) {
			flagImages[''+regional.country[i].id] = regional.country[i].isocode.toLowerCase();
		}
	}

	function fixCountryFlags() {
		$("span[edit_name='countryID']").each(function(){
			var e = $(this).children("div:first");
			var cid=$(this).find(':input:last').val();
			var flags;
			if (flagImages[cid] !== undefined) flags = flagImages[cid].split("/");
			var flag;
			if (flags !== undefined) flag = "/images/flags/"+flags[0]+".png";
			if(flag){
				$(this).next().find('img').css({"display" : "inline-block"}).attr('src',flag);
			}else{
				$(this).next().find('img').css({"display" : "none"}).attr('src','');
			}
			$(this).find(':input:last').prev()[0].setAttribute('value',$(this).find(':input:last').prev().val());
		});
	}

	function _fixCountryFlags() {
		var cid=focusedDijitItem.find(':input:last').val();
		var flags;
		if (flagImages[cid] !== undefined) flags = flagImages[cid].split("/");
		var flag;
		if (flags !== undefined) flag = "/images/flags/"+flags[0]+".png";
		focusedDijitItem.next().find('img').attr('src',flag);
		focusedDijitItem.find(':input:last').prev()[0].setAttribute('value',focusedDijitItem.find(':input:last').prev().val());
	}

	function setURL() {
		var s = focusedDijitItem.find(':input:last').val();
		s = $.trim(s);
		focusedDijitItem.find(':input:last')[0].setAttribute('value',s);
	}

	function editSciCon() {
		$("#mainscicondiv"+dialogCount).show();
		$("#addSciConDiv"+dialogCount).show();
		if(managedSciCons && managedSciCons!=null){
			managedSciCons.EditMode(true);
			$("#mainscicondiv"+dialogCount +" .mainscicontitle").show();
			$(managedSciCons.dom).show();
			$("#editapp"+dialogCount).addClass("editmode");
		}
		
		$("a.scicontoggler").attr("title","").css({"cursor":"default"}).removeAttr("onclick");
	}
	function editAppUrls(){
	 $("#urldiv"+dialogCount).after("<div id='urldivEditor' class='appurls_editor'></div>").hide();
	 if(managedAppUrlsEditor !== null){
	  managedAppUrlsEditor.reset();
	  managedAppUrlsEditor = null;
	 }
	 managedAppUrlsEditor = new appdb.views.ApplicationUrlsEditor({container : $("#urldivEditor")});
	 managedAppUrlsEditor.render(((managedAppUrls)?managedAppUrls._data:[]),"type");
	}
	function renderAppCategories(){
		if(managedAppCategoriesEditor !== null){
			managedAppCategoriesEditor.reset();
			managedAppCategoriesEditor = null;
		}
		var interval = setTimeout((function(attempts){
			return function(){
				attempts -= 1;
				if( attempts <=0 || appdb.model.StaticList.Categories.length > 0 ){
					clearTimeout(interval);
					var conf = appdb.utils.entity.getConfig(appdb.pages.application.currentEntityType());
					var listtype = appdb.views.SoftwareCategoryList;
					var listopts = {
						container: $(".app-softwarecategories:first"), 
						canEdit: false,
						content: conf.content(),
						maxViewLength: 5
					};
					
					if( appdb.pages.application.isVirtualAppliance() ){
						listtype = appdb.views.VApplianceCategoryList;
					} else if( appdb.pages.application.isSoftwareAppliance() ){
						listtype = appdb.views.SWApplianceCategoryList;
					}else{
						listtype =  appdb.views.SoftwareCategoryList;
					}
					managedAppCategoriesEditor = new listtype(listopts);
					managedAppCategoriesEditor.render(((managedAppCategories)?managedAppCategories:[]));
				}else{
					clearTimeout(interval);
				}
			};
		})(30), 100);		
	}
	function getVapplianceCategories(){
		var data = appdb.model.StaticList.Categories || [];
		var res = [];
		data = $.isArray(data)?data:[data];
		res.push({
			id: "34",
			primary: "true",
			selected: true,
			val: function(){
				return "Virtual Appliances";
			}
		});
		$.each(data, function(i, e){
			if( $.trim(e.parentid) === "34" ){
				res.push(e);
			}
		});
		return res;
	}
	function editAppCategories(newApp){
		if(managedAppCategoriesEditor !== null){
			managedAppCategoriesEditor.reset();
			managedAppCategoriesEditor = null;
		}
		var conf = appdb.utils.entity.getConfig(appdb.pages.application.currentEntityType());
		var editoropts = {
			container: $(".app-categories:first"), 
			canEdit: true,
			entityType: appdb.pages.application.currentEntityType(),
			content: conf.content(),
			dataFilter: appdb.utils.entity.getCategoryFilterByType(appdb.pages.application.currentEntityType()),
			maxViewLength: 5
		};
		if( appdb.pages.application.isVirtualAppliance() ){
			managedAppCategoriesEditor = new appdb.views.VApplianceCategoryList(editoropts);
		}else if( appdb.pages.application.isSoftwareAppliance() ){
			managedAppCategoriesEditor = new appdb.views.SWApplianceCategoryList(editoropts);
		}else {
			managedAppCategoriesEditor = new appdb.views.SoftwareCategoryList(editoropts);
		}
		
		var data = [];
		if( newApp ){ //new software
			data = [];
		}else if(managedAppCategories){
			data = managedAppCategories;
		}
		managedAppCategoriesEditor.render(data);
	}
	function renderSoftwareDisciplines(){
		if(managedDisciplinesEditor !== null){
			managedDisciplinesEditor.reset();
			managedDisciplinesEditor = null;
		}
		var interval = setTimeout((function(attempts){
			return function(){
				attempts -= 1;
				if( attempts <=0 || appdb.model.StaticList.Disciplines.length > 0){
						clearTimeout(interval);
						var conf = appdb.utils.entity.getConfig(appdb.pages.application.currentEntityType());
						managedDisciplinesEditor = new appdb.views.SoftwareDisciplineList({
							container: $(".app-softwaredisciplines:first"), 
							canEdit: false,
							content: conf.content(),
							maxViewLength: 5
						});
						managedDisciplinesEditor.render(((managedDisciplines)?managedDisciplines:[]));
				}else{
					clearTimeout(interval);
				}
			};
		})(30), 100);
	}
	function editSoftwareDisciplines(newApp){
		if(managedDisciplinesEditor !== null){
			managedDisciplinesEditor.reset();
			managedDisciplinesEditor = null;
		}
		
		managedDisciplinesEditor = new appdb.views.SoftwareDisciplineList({
				container: $(".app-domains:first"), 
				canEdit: true,
				content: appdb.pages.application.currentContent()
			});
		
		if(newApp){ //new software
			managedDisciplinesEditor.render([]);
		}else{
			managedDisciplinesEditor.render(((managedDisciplines)?managedDisciplines:[]));
		}
	}
	function addSciConOld() {
		var d = new dojox.Dialog({
			"title": "Associate person to software",
			"style": "width: 60%"
		});
		d.setHref('people/ppllist');
		d.show();
		try {
			dojo.disconnect(dijit.byId("detailsdlg"+dialogCount)._modalconnects.pop());
		} catch(e) {}
	}
	function addSciCon() {
		var relcon = new appdb.components.RelatedContacts({excluded:managedSciCons._listData});
		relcon.subscribe({event:"close", callback : function(v){
			if(managedSciCons && managedSciCons != null){
				managedSciCons.addNewContacts(this.views.peopleList.selectedDataItems.get());
				managedSciCons.render();
				managedSciCons.checkForChanges();
			}
		},caller:relcon});
		relcon.load({query:{flt:"",pagelength:12,userid:userID},ext:{}});
	}
	
	function addSciConOld2() {
		var h=$("#SciConDiv"+dialogCount).html();
		h=h+' <span class="editable" edit_type="combo" edit_data="'+ed+'" edit_name="vo" edit_group="true"></span>';
		$("#vodiv"+dialogCount).html(h);
		var e=new editForm('editapp'+dialogCount);
	}
	
	function remSciCon() {
		$('#SciConDiv'+dialogCount).find('input').each(function(x) {
			if ( $(this).attr('checked') != '' ) {
				$(this).parent().parent().remove();
			}
		});
	}

	function onAppUpdate(response,data) {
		if ( data.appID == "0" ) data.appID = '';
       
		try {
			dijit.byId("detailsdlg"+dialogCount).onCancel();
		} catch(e) {}
		if(data.appID===''){
            //from a newly registered application
            appdb.views.Main.showApplication({id: data.appID});
        }else{
            //from an application editing
            appdb.views.Main.refresh();
        }
	}

	function makeAppDocs(appID) {
		setTimeout((function(data){ 
			return function(){
				makeDocs(data,'applications','application');
				var d = dijit.byId("navdiv"+dialogCount) || null;
				if ( d !== null ) d.resize();
			};
		})(appID),1);
	}

	function hideDocGridCols() {
		$("table.dojoxGrid-row-table").find("tr:first").each(function(){
			$($(this).find("th.dojoxGrid-cell").get(3)).hide();
		});
		$("table.dojoxGrid-row-table").find("tr:first").each(function(){
			$($(this).find("td.dojoxGrid-cell").get(3)).hide();
		});
		$("table.dojoxGrid-row-table").find("tr:first").each(function(){
			$($(this).find("th.dojoxGrid-cell").get(0)).hide();
		});
		$("table.dojoxGrid-row-table").find("tr:first").each(function(){
			$($(this).find("td.dojoxGrid-cell").get(0)).hide();
		});
		$("th.dojoxGrid-cell").click(function(){
			setTimeout(function(){hideDocGridCols();},50);
		});
	}



	function makeDocs(appID,u1,u2) {
		var docs = appID[u2].publication || null;
		if( docs || ($.isArray(docs) && docs.length > 0) ){
			$("#docdiv" + dialogCount).removeClass("isempty");
		}else{
			$("#docdiv" + dialogCount).addClass("isempty");
		}
		if ( docgrid == false ) {
			docgrid = new dojox.grid.Grid({height:"100%",autoHeight:true},"docgrid"+dialogCount);
			var data = [];
			if (docs !== null) {
				if (! $.isArray(docs)) {docs = [docs];}
				for (i=0; i<docs.length; i++) {
					var doc = docs[i];
					var pageStart, pageEnd;
					if (doc.startPage == "") {
						pageStart = doc.endPage;
						pageEnd = "";
					} else {
						pageStart = doc.startPage;
						pageEnd = doc.endPage;
					}
					if (pageStart == "0") pageStart = "";
					if (pageEnd == "0") pageEnd = "";
					if (pageStart == pageEnd) pageEnd = "";
					var pages = ""+pageStart;
					if ( pageEnd != "" ) pages+=" - "+pageEnd;
					var title;
					if (doc.url != "") {
						title = "<a href=\""+doc.url+"\" target=\"_blank\">"+doc.title.replace(/'/g,"\\'")+"</a>";
					} else {
						title = doc.title;
					}
					title = "<span data-docid=\""+doc.id+"\">"+title+"</span>";
					var authors = [];
					if (doc.author) {
						if ( ! $.isArray(doc.author)) {doc.author = [doc.author];}
						for (j=0; j<doc.author.length; j++) {
							var author = doc.author[j];
							var authorstr = '';
							if (author.type === "external") {
								if ( typeof author.extAuthor !== "undefined" ) {
									authorstr += " <span data-authorMain=\""+author.main+"\" data-authorname=\""+author.extAuthor.replace(/'/g,"\\'")+"\">"+author.extAuthor.replace(/'/g,"\\'")+"</span>";
								}
							} else {
								authorstr += " <a data-authorMain=\""+author.main+"\" data-authorid=\""+author.person.id+"\" href=\"/store/person/"+author.person.cname+"\" onclick=\"appdb.views.Main.showPerson({id: "+author.person.id+", cname:'"+author.person.cname+"'},{mainTitle: '"+author.person.firstname.replace(/'/g,"\\'")+' '+author.person.lastname.replace(/'/g,"\\'")+"'})\">"+author.person.firstname.replace(/'/g,"\\'")+' '+author.person.lastname.replace(/'/g,"\\'")+"</a>";
							}
							if (authorstr !== '') authors.push(authorstr);
						}
					}
					var doctype = "<span data-doctypeid=\""+doc.type.id+"\">"+doc.type.val()+"</span>";
					data.push([[title], [doctype], doc.conference, doc.volume, pages, doc.year, doc.publisher, doc.isbn, doc.proceedings, doc.journal, authors]);
				}
			}
			var model = new dojox.grid.data.Table(null, data);
			var view = {
			cells: [[
					{name: 'Title', rowSpan: 2, width:'20%'},
					{name: 'Type', rowSpan: 2, width:'5%'},
					{name: 'Conference', width:'20%'},	
					{name: 'Volume', width:'5%'},
					{name: 'Pages', width:'5%'},
					{name: 'Year', width:'3%'},
					{name: 'Publisher', width:'10%'},
					{name: 'ISBN', width:'10%'}                    
				],[
					{name: 'Proceedings'},
					{name: 'Journal', colSpan:3},
					{name: 'Authors', colSpan:2}
				]]
			};
			var structure = [ view ];
			docgrid.setModel(model);
			docgrid.setStructure(structure);
			detailsStyle = 0;
			try{
				if ( detailsStyle == 1 ) {
					dojo.parser.parse(dojo.byId("detailsdlgcontent"+dialogCount));
				} else {
					dojo.parser.parse($(".detailsdlgcontent")[0]);
				}
			}catch(e){
				
			}
			docgrid.render();
		} 
	}

    function prepareUpload2() {
        $('#uploadlogo'+dialogCount).html('<iframe scrolling="no" style="width:110px; height:75px; border:none; overflow:hidden" frameborder="0" src="/apps/uploadframe"></iframe>');
	}
	
	function prepareUpload() {
        $('#uploadlogo'+dialogCount).html('<a href="#" onclick="prepareUpload2();return false;">Change logo</a>');
    }

	function moderateApplication(appID,mstate) {
		var mtext;
		//setup dialog content
		if (mstate == "true") {
			mtext = '<div title="Moderate/Unmoderate Software"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you sure you wish to unmoderate this entry and make it visible to the public?</p></div>';
		} else {
			mtext = '<div title="Moderate/Unmoderate Software"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Please provide a reason for moderating this entry and hidding it from the public:</p><div class="modinput" style="padding:10px; border:1px solid grey"><p><textarea maxlength="512" dojoType="dijit.form.Textarea" name="modreason"></textarea></p></div></div>';
		}
		//Function to handle success of moderation and de-moderation
		var updateModerationDisplay = function(data){
			appmod = $($.find(".app-mod"));
			appmod.unbind('click');
			if ( data.application.moderated == "true" ) {
				//Application is now moderated
				//Toggle html link to unmoderate
				$(".app-mod").find("span").html('Unmoderate');
				$(".app-modrow").find("td").empty();
				appmod.click(function(){
					moderateApplication(appID,"true");
				});
				var modinfo = {};
				modinfo.application = {};
				modinfo.application.moderator = {};
				modinfo.application.moderator.id = data.application.moderator.id;
				modinfo.application.moderator.firstname = data.application.moderator.firstname;
				modinfo.application.moderator.lastname = data.application.moderator.lastname;
				modinfo.application.id = data.application.id;
				modinfo.application.name = data.application.name;
				modinfo.application.moderated = "true";
				modinfo.application.moderatedOn = data.application.moderatedOn;
				modinfo.application.moderationReason = data.application.moderationReason;
				//Display moderation information
				showAppModInfo(null,modinfo);
				
			} else {
				//Application is not moderated anymore
				//Toggle html link to moderate and hide moderation display
				$(".app-mod").find("span").html('Moderate');
				$(".app-modrow").find("td").empty();
				$(".app-modrow").hide();
				appmod.click(function(){
					moderateApplication(appID,"false");
				});
			}
		};
		//Function to properly dispose openned moderation dialog
		var closeModerationDisplay = function(){
			if ( $(".modinput textarea").length > 0 ) {
				try {
					dijit.byNode(dojo.query(".modinput textarea")[0]).destroyRecursive(false);
				} catch (e) {
				}
			}
			if ( $(".modinput").length > 0 ) $(".modinput").remove();
			$(delAppDialog).dialog('close');
		};
		//Init and display moderation dialog
		var delAppDialog = $(mtext).dialog({
			dialogClass: 'alert',
			autoOpen: false,
			resizable: false,
			height:'auto',
			modal: true,
			buttons: {
				OK: function() {
					//Create the appropriate entity to hold the data to be send to the API
					var modEntity = new appdb.entity.ModeratedApplication({id:appID,moderationReason: $('textarea[name="modreason"]').val() || ""});
					//Create a model to send the insert request to the server
					var appModel = new appdb.model.ModeratedApplications();
					//Register the event handlers upon success or failure of the request
					appModel.subscribe({event: "insert", callback:function(d){
						//Called upon success of application moderation
						updateModerationDisplay(d);
					}}).subscribe({event: "remove", callback: function(d) {
						//Called upon success of application de-moderation
						updateModerationDisplay(d);
					}}).subscribe({event: "error", callback: function(d){
						//Called upon ajax error of application moderation action. E.g. HTTP 500 - Internal Server Error
						var err = {
							"status": "Cannot moderate software",
							"description": d.description
						};
						//Called upon ajax error of both application update or insert action. E.g. HTTP 500 - Internal Server Error
						appdb.utils.RestApiErrorHandler(d, err).show();
					}});
					//Send moderation request (PUT) for the application
					if(mstate != "true"){ //if user wants to moderate the application
						appModel.insert({query: {}, data: {data: appdb.utils.EntitySerializer.toXml(modEntity)}});
					}else{
						appModel.remove({id: modEntity.id()});
					}
					//Clean up moderation prompt dialog
					closeModerationDisplay();
				},
				Cancel: closeModerationDisplay
			}
		});
		//Open dialog and parse the dojo components to render.
		delAppDialog.dialog('open');
		if ( $(".modinput").length > 0 ) dojo.parser.parse($(".modinput")[0]);
	}
    
    function deleteProfile(pplID) {
        var apps = new appdb.model.PeopleApplications();
        apps.subscribe({"event": "select", "callback": function(d) {
            var hasApps = false;            
            if ( d ) {
				var userapps = d.application || [];
				userapps = $.isArray(userapps)?userapps:[userapps];
                if ( userapps.length > 0 ) {
                    hasApps = true;
                }
                if ( ! hasApps ) {
    		        $delPplDialog.dialog('open');
                } else {
		            var $cannotDelPplDialog = $('<div title="Delete Profile"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>This person is listed as the owner of one or more software entries. Please <a href="#" class="delPplAppList" style="color:#D96B00;text-decoration:none;">review the list</a>, change the software\'s owner, and try again.</p></div>').dialog({
                        dialogClass: 'alert',
                        autoOpen: false,
                        resizable: false,
                        height:180,
                        width:400,
                        modal: true,
                        buttons: {
                            Close: function() {
                                $(this).dialog('close');
                            }
                        }
                    });
                    $($cannotDelPplDialog).find("a.delPplAppList").click(function(){
                        $cannotDelPplDialog.dialog('close'); 
                        appdb.views.Main.showApplications({flt:'=application.owner:'+pplID+' +application.deleted:false'}, {isBaseQuery:true, filterDisplay:'Search...', mainTitle : 'Software owned by person with ID:'+pplID});
                    });
                    $cannotDelPplDialog.dialog('open');
                }
            }
        }});
        apps.get({"id": pplID, "applicationtype": "owned", "flt": "deleted:false"});
		var $delPplDialog = $('<div title="Delete Profile"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Permanently delete user profile and associated data?</p></div>').dialog({
			dialogClass: 'alert',
			autoOpen: false,
			resizable: false,
			height:160,
			modal: true,
			buttons: {
				Delete: function() {
					var model = new appdb.model.Person();
					model.subscribe({event: "remove", callback: function(d){
						appdb.views.Main.closeCurrentView();
						
					}}).subscribe({event: "error", callback: function(d){
						var pplName = $("#lastNavigationItem").text();
						pplName = ($.trim(pplName)==="")?"profile":pplName;
						var err = {
							"status": "Cannot delete " + pplName
						};
    					//Called upon ajax error of both application update or insert action. E.g. HTTP 500 - Internal Server Error
						appdb.utils.RestApiErrorHandler(d, err).show();
						$(".pplviewtoolbar").show(); 
					}});
					$(this).dialog('close');
					//Send DELETE request
					model.remove({id: pplID});
					$(".pplviewtoolbar").hide();
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			}
		});
    }

    function deleteApplication(appID) {
		var $delAppDialog = $('<div title="Delete Software"><p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Permanently delete software and associated data?</p></div>').dialog({
			dialogClass: 'alert',
			autoOpen: false,
			resizable: false,
			height:160,
			modal: true,
			buttons: {
				Delete: function() {
					var model = new appdb.model.Application();
					model.subscribe({event: "remove", callback: function(d){
						appdb.views.Main.closeCurrentView();
						
					}}).subscribe({event: "error", callback: function(d){
						var appName = $("#lastNavigationItem").text();
						appName = ($.trim(appName)==="")?"software":appName;
						var err = {
							"status": "Cannot delete " + appName,
							"description": d.description
						};
						//Called upon ajax error of both application update or insert action. E.g. HTTP 500 - Internal Server Error
						appdb.utils.RestApiErrorHandler(d, err).show();
						$(".appviewtoolbar").show(); 
					}});
					$(this).dialog('close');
					//Send DELETE request
					model.remove({id: appID});
					$(".appviewtoolbar").hide();
				},
				Cancel: function() {
					$(this).dialog('close');
				}
			}
		});
		$delAppDialog.dialog('open');
	}

    function toggleAbstract(elem) {
        jQuery.fn.center = function () {
            this.css("position","absolute");
            this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
            this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
            return this;
        };
        if ($("#abstractdiv"+dialogCount).is(":visible")) {
            $("#abstractdiv"+dialogCount).fadeOut("fast");
			$(elem).next().fadeIn("fast");
        } else {
            $("#abstractdiv"+dialogCount).fadeIn("fast");
			$(elem).next().fadeOut("fast");
        }
        if (detailsStyle == 1) setTimeout(function(){$("#detailsdlg"+dialogCount).center();},500);
    }

    function toggleSciCon() {
		jQuery.fn.center = function () {
            this.css("position","absolute");
            this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
            this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
            return this;
        };
        if ($("#managedSciConDiv").is(":visible")) {
            $("#managedSciConDiv").fadeOut("fast");
        } else {
            $("#managedSciConDiv").fadeIn("fast");
        }
        if (detailsStyle == 1) setTimeout(function(){$("#detailsdlg"+dialogCount).center();},500);
    }
	function toggleContent(el){
		if( el ){
			var c = $(el).closest(".contentcontainer").find(".content");
			if( $(c).length > 0  ){
				if( $(c).is(":visible") ){
					$(c).fadeOut("fast");
				}else{
					$(c).fadeIn("fast");
				}
			}
		}
	}
	function onToggleBookmark() {
        if ( bmInfoDlg!== undefined ) dijit.popup.close(bmInfoDlg);
        bmInfoDlg = new dijit.TooltipDialog({
            title: 'Information',
            content: 'Item bookmark toggled'
		});
		setTimeout(function(){
			dijit.popup.open({
				popup: bmInfoDlg,
				parent: $('img[src^="/images/star"]')[0],
				around: $('img[src^="/images/star"]')[0],
				orient: {BL:'TL'}                    
			});
			setTimeout('dijit.popup.close(bmInfoDlg)',1000);
		},200);
    }

	function animateRelatedApps() {
		clearInterval(RelAppIntId);
		$("div.relatedapps").each(function() {
			if ($(this).find("li").length > 5) {
				var indx = 0;
				$(this).find("li").each(function(){
					if (indx <= 4) $(this).show(); else $(this).hide();
					indx++;
				});
				RelAppIntId = setInterval((function(e){
					return function() {
						if ( ($(e).find("li").length > 5) && ($(e).find("ul").is(":visible")) ) {
							$(e).find("ul").fadeOut("fast");
							setTimeout((function(e) {
								return function() {
									$(e).find("ul").prepend($(e).find("li:last"));
								}; 
							})(e), 150);
							var ind = 0;
							$(e).find("li").each(function(){
								if (ind <= 4) $(this).show(); else $(this).hide();
								ind++;
							});
							$(e).find("ul").fadeIn();
						}
					}; 
				})(this), 10000);
			} else {
				if ( $(this).find("li").length === 0 ) {
					$("div.relatedapps").parent().empty().css("border-left", "");
				}
			}
		});
	}

	var rebuildCountryData = function() {
		var ed = countryData;
        var h="";
        $("input[name^=countryID]").each(function(index,e){
            var v=$(this).prev().val();
            h=h+' <span class="editable app-country" edit_onchange="fixCountryFlags" edit_type="combo" edit_data="'+ed+'" edit_name="countryID" edit_group="true">'+v+'</span> <span class="editable app-countryFlag" edit_type="none"><img border="0" style="vertical-align:middle;border:1px solid #BFBFBF;" src=""/></span>';
            $(this).remove();
        });
        var ih='';
        $("#countryDiv"+dialogCount).find(".iCountryID").each(function(e){
            ih=ih+'<span class="iCountryID">'+$(this).html()+'</span> <span>'+$(this).next().html()+'</span>';
        });
        h=ih+h;
		$("#countryDiv"+dialogCount).html( h );
    };

	var canAddListItem = function(datatype,elems,def){
		var found = -1;
		def = $.trim(def || '');
		var invalidelem = null;
		$(':input[name^="'+datatype+'"]').each(function(index,elem){
			if($.trim($(elem).val()) === def ){
				found = (found<0)?index:found;
				invalidelem = $(elem);
			}
		});
		if(found>-1 && elems && elems.length>0){
			var ee = invalidelem.parent().parent();
			var prevColor = invalidelem.parent().parent().css("background-color") || "";
			ee.animate({"opacity":"0.5","background-color":"red"},300,function(){setTimeout(function(){ee.animate({"opacity":"1","background-color":prevColor},400);},10);});
		}
		
		return (found<0)?true:false;
	};
	
	var addCountry = function() {
		if(canAddListItem("countryID",$("#countryDiv"+dialogCount).find(".app-country")) === false)	return;
		
        rebuildCountryData();
        var h=$("#countryDiv"+dialogCount).html();
		var ed = countryData;
		h=h + '<span class="editable app-country" edit_onchange="fixCountryFlags" edit_type="combo" edit_data="'+ed+'" edit_name="countryID" edit_group="true">'+appdb.config.defaults.api.country+'</span> <span class="editable app-countryFlag" edit_type="none"><img border="0" style="vertical-align:middle;border:1px solid #BFBFBF;" src=""/></span>';
		$("#countryDiv"+dialogCount).html(h);
		var e=new editForm('editapp'+dialogCount);
		fixCountryFlags();
		separateMultipleItems($("#countryDiv"+dialogCount).find("span.app-countryFlag"));
        renderLists($(".app-countries:last"),remCountry);
	};

	var remCountry = function() {
		if ( focusedDijitItem !== undefined ) {
			if ( focusedDijitItem.attr('edit_name') === 'countryID' ) {
				focusedDijitItem.next(".comboseperator").remove();
				focusedDijitItem.next().remove();
				focusedDijitItem.remove();
				separateMultipleItems($("#countryDiv"+dialogCount).find("span.app-countryFlag"));
			}
		}
	};
	
	function rebuildCategoriesData(){
		resetCategoriesChecker();
		var ed = categoriesData;
		var h='';
		$("input[name^=categoryID]").each(function(e){
            var v=$(this).prev().val();
			h=h+' <span class="editable app-category" edit_type="combo"  edit_data="'+ed+'" edit_name="categoryID" edit_group="true">'+v+'</span>';
        });
        $(".app-categories").html(h);
	}
    function rebuildMWData() {
   		var ed = mwData;
		var h='';
		var els = [];
		$("span.app-mw").each(function(index,elem){
			els.push({dom : elem, isOther: $(elem).hasClass("other")});
		});
		var latest = null, added = null;
		if(els.length === 0 ){
			$("input[name^=mw]").each(function(index,elem){
				var v=$(elem).prev().val();
				$("#mwdiv"+dialogCount).append($('<span class="editable app-mw" edit_type="combo" edit_data="'+ed+'" edit_name="mw" edit_group="true">'+v+'</span>'));
				h=h+' <span class="editable app-mw" edit_type="combo" edit_data="'+ed+'" edit_name="mw" edit_group="true">'+v+'</span>';
			});
			$("#mwdiv"+dialogCount).html(h);
		} else {
			for(var i=0; i<els.length; i+=1){
				if(els[i].isOther){
					added = els[i].dom;
				}else{
					var v=$(els[i].dom).find("input[name=^=mw]:last").prev().val();
					$(els[i].dom).remove();
					added = $('<span class="editable app-mw" edit_type="combo" edit_data="'+ed+'" edit_name="mw" edit_group="true">'+v+'</span>');
				}
				if(latest){
					$(latest).after(added);
				}else{
					$("#mwdiv"+dialogCount).append(added);
				}
				latest = added;
			}
		}
        
    }

    function rebuildDomainData() {
		var ed = domainData;
		var h='';
        $("input[name^=domainID]").each(function(e){
            var v=$(this).prev().val();
			h=h+' <span class="editable app-domain" edit_type="combo"  edit_data="'+ed+'" edit_name="domainID" edit_group="true">'+v+'</span>';
        });
        $(".app-domains").html(h);
    }

    function rebuildSubdomainData() {
		var ed = subdomainData;
		var h='';
        $("input[name^=subdomainID]").each(function(e){
            var v=$(this).prev().val();
			h=h+' <span class="editable app-subdomain" edit_type="combo"  edit_data="'+ed+'" edit_name="subdomainID" edit_group="true">'+v+'</span>';
        });
        $(".app-subdomains").html(h);
    }

    function rebuildVOData() {
		var ed = voData;
		var h='';
        $("input[name^=vo]").each(function(e){
            var v=$(this).prev().val();
			h=h+' <span class="editable app-vo" edit_type="combo"  edit_data="'+ed+'" edit_name="vo" edit_group="true">'+v+'</span>';
        });
        $("#vodiv"+dialogCount).html(h);
    }
	var resetCategoriesChecker = function(){
		$(".app-categories > .app-category > .dijitComboBox ").each(function(i,e){
			if(dijit.byNode($(e)[0])){
				dijit.byNode($(e)[0]).destroyRecursive(true);
			}
		});
	};
	var initCategoriesChecker = function(){
		$(".app-categories > .app-category > .dijitComboBox ").each(function(i,e){
			dojo.connect(dijit.byNode($(e)[0]),"onChange",this,function(k){
				var isTool = false;
				if(!k){
					return;
				}
				if(k=='2'){
					var found = false;
					$("input[name^='categoryID']").each(function(index,elem){
						if($(elem).val()==""){
							return;
						}
						if(index!=i && $(elem).val()!='2'){
							found = true;
						}
					});
					if(found===false){
						if($(".app-logo:last").attr("src")==="/images/app.png"){
							$(".app-logo:last").attr("src","/images/tool.png");
						}
						isTool = true;
					}
				}else if($(".app-logo:last").attr("src")==="/images/tool.png"){
					$(".app-logo:last").attr("src","/images/app.png");
				}
				if(k && $("input[name^='tool']").length>0){
					$("input[name^='tool']").val((isTool && k=="2")?"1":"0");
				}
			});
		});
		if($(".app-categories > .app-category > .dijitComboBox ").length>0){
			$(".app-categories > .app-category > .dijitComboBox ").each(function(i,e){
				if(dijit.byNode($(e)[0]).value!=''){
					dijit.byNode($(e)[0]).onChange(dijit.byNode($(e)[0]).value);
				}
			});
		}else{
			$("input[name^='tool']").val("0");
			if($(".app-logo:last").attr("src")=="/images/tool.png"){
				$(".app-logo:last").attr("src","/images/app.png");
			}
		}
	};
	var setupPrimaryCategory = function(){
		$(".app-categories").remove(".primarycategory");
		var cats = $(".app-categories > .app-category");
		if(cats.length === 0){
			return;
		}
		cats = $(cats).first();
		$(cats).wrap("<span class='primaryCategory' ></span>");
		$(cats).parent().prepend("<span class='primaryCategoryText'><span>Primary</span></span>");
	};
	var renderLists = function(el,rem,except){
         var sel = (el)?$(el):$("#navdiv" + dialogCount);
         //In case of init
         if(typeof el === "undefined"){
          setTimeout(function(){
           renderLists($(".app-domains:last"),remDomain);
           renderLists($(".app-countries:last"),remCountry);
           renderLists($(".app-mws:last"),remMW,"other");
           renderLists($(".app-vos:last"),remVO);
		   renderLists($(".app-proglangs:last"), remProgLang);
          },1);
          
          return;
         }
         
		$(sel).find("span[edit_type='combo'][edit_group='true']").each(function(index,elem){
          if(typeof except === "string" && $(elem).hasClass(except)){
           return;
          }else if($.isFunction(except) && except(elem)){
           return;
          }
          var div = document.createElement("div"), a = document.createElement("a"), span = document.createElement("span");
          $(div).addClass("dijitButtonNode").addClass("listremove").append(a);
          $(span).append("<img alt='remove' border='0' src='/images/cancelicon.png' title='Remove list item'></img>");
          $(a).append(span).attr("href","#").attr("title","Remove item").click(function(e){

            if( rem ){
             focusedDijitItem = $(elem);
             rem();
            }
            e.preventDefault();
            return false;
          });
          $(elem).append(div).mouseover(function(){
           $(div).addClass("hover");
           $(div).addClass("dijitDownArrowButtonHover");
          }).mouseleave(function(){
           $(div).removeClass("hover");
           $(div).removeClass("dijitDownArrowButtonHover");
          });
	 });
	};
	var addCategory = function(){
		if(canAddListItem("categoryID",$(".app-categories").find(".app-category")) === false) return;

		rebuildCategoriesData();
		var h=$(".app-categories").html();
		var ed = categoriesData;
		h=h+' <span class="editable app-category" edit_type="combo" edit_data="'+ed+'" edit_name="categoryID" edit_group="true">'+appdb.config.defaults.api.category+'</span>';
		$(".app-categories").html(h);
		var e=new editForm('editapp'+dialogCount);
		separateMultipleItems($(".app-categories").find("span.app-category"));
		initCategoriesChecker();
		setupPrimaryCategory();
	};
	
	var remCategory = function(){
		if ( focusedDijitItem !== undefined ) {
			var p = focusedDijitItem;
			if ( p.attr('edit_name') === "categoryID" ) {
				p.next(".comboseperator").remove();
				p.remove();
				separateMultipleItems($(".app-categories").find("span.app-category"));
			}
		}
		initCategoriesChecker();		
	};
	
	var addMW = function() {
		if(canAddListItem("mw",$("#mwdiv"+dialogCount).find(".app-mw"),""/*appdb.config.defaults.api.middleware*/) === false) return;

        rebuildMWData();
		var h='';
   		var ed = mwData;
		h=h+' <span class="editable app-mw" edit_type="combo" edit_data="'+ed+'" edit_name="mw" edit_group="true">'+appdb.config.defaults.api.middleware+'</span>';
		if($("#mwdiv"+dialogCount).find(".app-mw:last").length==0){
			$("#mwdiv"+dialogCount).html(h);
		}else{
			$("#mwdiv"+dialogCount).find(".app-mw:last").after($(h));
		}
		
		var e=new editForm('editapp'+dialogCount);
		separateMultipleItems($("#mwdiv"+dialogCount).find("span.app-mw"));
		renderLists($(".app-mws:last"),remMW,"other");
	};
	
	var rebuildProgLangData = function(){
		var ed = proglangData;
		var h='';
		$("input[name^=proglangID]").each(function(e){
            var v=$(this).prev().val();
			h=h+' <span class="editable app-proglang" edit_type="combo"  edit_data="'+ed+'" edit_name="proglangID" edit_group="true">'+v+'</span>';
        });
        $("#proglangdiv"+dialogCount).html(h);
	};
	
	var addProgLang = function(){
		if(canAddListItem("proglangID",$("#proglangdiv"+dialogCount).find(".app-proglang"),"") === false) return;

        rebuildProgLangData();
		var h=$("#proglangdiv"+dialogCount).html();
   		var ed = proglangData;
		h=h+' <span class="editable app-proglang" edit_type="combo" edit_data="'+ed+'" edit_name="proglangID" edit_group="true">'+appdb.config.defaults.api.__all__+'</span>';
		$("#proglangdiv"+dialogCount).html(h);
		
		var e=new editForm('editapp'+dialogCount);
		separateMultipleItems($("#proglangdiv"+dialogCount).find("span.app-proglang"));
		renderLists($(".app-proglangs:last"),remProgLang);
	};
	
	var remProgLang = function(){
		if ( focusedDijitItem !== undefined ) {
			var p = focusedDijitItem;
			if ( p.attr('edit_name') == "proglangID" ) {
				p.next(".comboseperator").remove();
				p.remove();
				separateMultipleItems($(".app-proglangs").find("span.app-proglang"));
			}
		}
	};
	
	var addDomain = function() {
		if(canAddListItem("domain",$(".app-domains").find(".app-domain")) === false) return;

        rebuildDomainData();
		var h=$(".app-domains").html();
   		var ed = domainData;
		h=h+' <span class="editable app-domain" edit_type="combo" edit_data="'+ed+'" edit_name="domainID" edit_group="true">'+appdb.config.defaults.api.discipline+'</span>';
		$(".app-domains").html(h);
		var e=new editForm('editapp'+dialogCount);
		separateMultipleItems($(".app-domains").find("span.app-domain"));
                renderLists($(".app-domains:last"),remDomain);
	};

	var remDomain = function() {
		if ( focusedDijitItem !== undefined ) {
			var p=focusedDijitItem;
			if ( p.attr('edit_name') === "domainID" ) {
				p.next(".comboseperator").remove();
				p.remove();
				separateMultipleItems($(".app-domains").find("span.app-domain"));
			}
		}
	};

	var addSubdomain = function() {
		if(canAddListItem("subdomain",$(".app-subdomains").find(".app-subdomain")) === false) return;

        rebuildSubdomainData();
		var h=$(".app-subdomains").html();
   		var ed = subdomainData;
		h=h+' <span class="editable app-subdomain" edit_type="combo" edit_data="'+ed+'" edit_name="subdomainID" edit_group="true">'+appdb.config.defaults.api.subdiscipline+'</span>';
		$(".app-subdomains").html(h);
		var e=new editForm('editapp'+dialogCount);
		separateMultipleItems($(".app-subdomains").find("span.app-subdomain"));
		renderLists($(".app-subdomains:last"),remSubdomain);
        };
	var remSubdomain = function() {
		if ( focusedDijitItem !== undefined ) {
			var p=focusedDijitItem;
			if ( p.attr('edit_name') === "subdomainID" ) {
				p.next(".comboseperator").remove();
				p.remove();
				separateMultipleItems($(".app-subdomains").find("span.app-subdomain"));
			}
		}	
	};
		
	var addVO = function() {
		if(canAddListItem("vo",$("#vodiv"+dialogCount).find(".app-vo")) === false) return;

        rebuildVOData();
		var h=$("#vodiv"+dialogCount).html();
   		var ed = voData;
		h=h+' <span class="editable app-vo" edit_type="combo" edit_data="'+ed+'" edit_name="vo" edit_group="true">'+appdb.config.defaults.api.vo+'</span>';
		$("#vodiv"+dialogCount).html(h);
		var e=new editForm('editapp'+dialogCount);
		separateMultipleItems($("#vodiv"+dialogCount).find("span.app-vo"));
                renderLists($(".app-vos:last"),remVO);
	};
	
	var remMW = function() {
		if ( focusedDijitItem !== undefined ) {
			var p=focusedDijitItem;
			if ( p.attr('edit_name') === "mw" ) {
				var id = p.find(":input[type='text']:last").val();
				validateItemRemoval("middleware",id,function(){
					p.next(".comboseperator").remove();
					p.remove();
					separateMultipleItems($("#mwdiv"+dialogCount).find("span.app-mw"));
				});
			}
		}
	};	
	var validateItemRemoval = function(type,id,callback){
		type = type.toLowerCase();
		if(managedSciCons && managedSciCons!=null ){
			var items = managedSciCons.filterItems(function(item){
				var i, len, d = item._itemData.contactItem || [];
				if($.isArray(d)===false){
					d = [d];
				}
				len = d.length;
				for(i=0; i<len; i+=1){
					var did = d[i].id;
					if(type === 'middleware'){
						if(did == 5){
							did = d[i].comment || ((d[i].val)?d[i].val():"");
						}else{
							did = d[i].val();
						}
					}
					if(d[i].type.toLowerCase()==type && did==id ){
						if(typeof d[i].toRemove === "undefined" || (d[i].toRemove && d[i].toRemove==false)){
							d[i].toRemoveImplicit = true;
							return true;
						}
					}
				}
				return false;
			});
			if(items.length>0){
				var cont = document.createElement("div"), commands =  document.createElement("div"), cancel = document.createElement("span"), ok = document.createElement("span");
				$(commands).addClass("commands");
				$(cont).addClass("removeentitywarning");
				var msg = "There are contacts which have been set as experts on this " + ((type==='vo')?"virtual organization":type) +" listed bellow.<br/><ul>";
				for(i=0; i<items.length; i+=1){
					msg += "<li>" + items[i]._itemData.firstname + " " +  items[i]._itemData.lastname + "</li>";
				}
				msg += "</ul><br/>Clicking <b>continue</b> will remove the relevant entries saving the software. Click <b>cancel</b> to keep this item instead.";
				$(cont).append(msg);
				$(cont).append(commands);
				$(commands).append(ok).append(cancel);
				new dijit.form.Button({
					label: "Cancel",
					style: "float:right;padding:5px;",
					onClick: function() {
						appdb.views.ContactPointEditor.WarningDialog.hide();
					}
				},cancel);
				new dijit.form.Button({
					label: "Continue",
					style: "float:right;padding:5px;",
					onClick: function() {
						appdb.views.ContactPointEditor.WarningDialog.hide();
						var items = managedSciCons.subviews, len = items.length, i;
						for(i=0; i<len; i+=1){
							if(items[i]._itemData.contactItem){
								appdb.views.RelatedContactList.CheckForChanges(items[i]);
							}
						}
						callback();
					}
				},ok);
				if(appdb.views.ContactPointEditor.WarningDialog != null){
					appdb.views.ContactPointEditor.WarningDialog.hide();
					appdb.views.ContactPointEditor.WarningDialog.destroyRecursive(false);
				}
				appdb.views.ContactPointEditor.WarningDialog = new dijit.Dialog({
					title: "Associated contacts",
					content: cont,
					style: "width: 420px"
				});
				appdb.views.ContactPointEditor.WarningDialog.show();
				return;
			}
		}
		callback();
	};
	var remVO = function() {
		if ( focusedDijitItem !== undefined ) {
			var p=focusedDijitItem;
			if ( p.attr('edit_name') === "vo" ){
				var id = p.find(":input[type='hidden']:last").val();
				validateItemRemoval("vo",id,function(){
					p.next(".comboseperator").remove();
					p.remove();
					separateMultipleItems($("#vodiv"+dialogCount).find("span.app-vo"));
				});
			}
		}
	};

var setupApplicationMiddlewares = (function(){
	var _currentPopup = null;
	var popup = function(dom,data){
		if(_currentPopup!==null){
			_currentPopup.cancel();
			dijit.popup.close(_currentPopup);
			_currentPopup.destroyRecursive(false);
		}
		_currentPopup =  new dijit.TooltipDialog({content : $(data)[0]});
		dijit.popup.open({
			parent : $(dom)[0],
			popup: _currentPopup,
			around : $(dom)[0],
			orient: {'BR':'TR','BL':'TL'}
		});
	};
	return function(e){
		setTimeout(function(){
			$(e).find("span.app-mws span.app-mw a.mwname").unbind("click").bind("click",function(ev){
				var name = $(this).text();
				$(".dijitPopup.dijitTooltipDialogPopup").remove();
				var data = $(document.createElement("div")).addClass("middlewarepopup");
				var items = [
					{image: 'category1.png', title: 'View associated software', content:'software'},
					{image: 'category34.png', title: 'View associated vappliances', content:'vappliance'},
					{image: 'swapp.png', title: 'View associated swappliances', content:'swappliance', hidden: !!!appdb.config.features.swappliance},
					{image: 'homepage.png', title: 'Go to middleware home page', href: $(this).parent().find(".mwhome").attr("href") }
				];
				$.each(items, function(i,e){
					if( e.hidden === true ) return;
					var a = $(document.createElement("a")).unbind("click").html("<img src='/images/"+e.image+"' border='0'/><span>" + e.title + "</span>");
					if( e.href ){
						$(a).attr("href", e.href).attr("target","_blank");
					}else if( e.onClick ){
						$(a).attr("onClick", e.onClick);
					} else {
						$(a).bind("click", (function(item){
							return function(ev){
								ev.preventDefault();
								appQuickLink( name, 1, {mainTitle: name, content: item.content} );
								$(".dijitPopup.dijitTooltipDialogPopup").remove();
								return false;
							};
						})(e));
					}
					$(data).append(a);
				});
				popup(this,data);
				ev.preventDefault();
				return false;
			});
		},1);
	};
})();

var setupApplicationProglangs = (function(){
	var _currentPopup = null;
	var popup = function(dom,data){
		if(_currentPopup!==null){
			_currentPopup.cancel();
			dijit.popup.close(_currentPopup);
			_currentPopup.destroyRecursive(false);
		}
		_currentPopup =  new dijit.TooltipDialog({content : $(data)[0]});
		dijit.popup.open({
			parent : $(dom)[0],
			popup: _currentPopup,
			around : $(dom)[0],
			orient: {'BR':'TR','BL':'TL'}
		});
	};
	return function(e){
		setTimeout(function(){
			$(e).find("span.app-proglangs span.app-proglang > a").unbind("click").bind("click",function(ev){
				var id = $(this).data("id");
				var name = $(this).text();
				$(".dijitPopup.dijitTooltipDialogPopup").remove();
				var data = $(document.createElement("div")).addClass("proglangpopup");
				var items = [
					{image: 'category1.png', title: 'View associated software', content:'software'},
					{image: 'category34.png', title: 'View associated vappliances', content:'vappliance'},
					{image: 'swapp.png', title: 'View associated swappliances', content:'swappliance', hidden: !!!appdb.config.features.swappliance }
				];
				$.each(items, function(i,e){
					if( e.hidden === true ) return;
					var a = $(document.createElement("a")).unbind("click").html("<img src='/images/"+e.image+"' border='0'/><span>" + e.title + "</span>");
					if( e.href ){
						$(a).attr("href", e.href).attr("target","_blank");
					} else if( e.onClick ){
						$(a).attr("onClick", e.onClick);
					} else {
						$(a).bind("click", (function(item){
							return function(ev){
								ev.preventDefault();
								appQuickLink( id, 7, {mainTitle: name, content: item.content} );
								$(".dijitPopup.dijitTooltipDialogPopup").remove();
								return false;
							};
						})(e));
					}
					$(data).append(a);
				});
				popup(this,data);
				ev.preventDefault();
				return false;
			});
		},1);
	};
})();

var setupApplicationCountries = (function(){
	var _currentPopup = null;
	var popup = function(dom,data){
		if(_currentPopup!==null){
			_currentPopup.cancel();
			dijit.popup.close(_currentPopup);
			_currentPopup.destroyRecursive(false);
		}
		_currentPopup =  new dijit.TooltipDialog({content : $(data)[0]});
		dijit.popup.open({
			parent : $(dom)[0],
			popup: _currentPopup,
			around : $(dom)[0],
			orient: {'BR':'TR','BL':'TL'}
		});
	};
	return function(e){
		setTimeout(function(){
			$(e).find("span.app-countries span.app-country > a").unbind("click").bind("click",function(ev){
				var id = $(this).data("id");
				var name = $(this).text();
				$(".dijitPopup.dijitTooltipDialogPopup").remove();
				var data = $(document.createElement("div")).addClass("countrypopup");
				var items = [
					{image: 'category1.png', title: 'View associated software', content:'software'},
					{image: 'category34.png', title: 'View associated vappliances', content:'vappliance'},
					{image: 'swapp.png', title: 'View associated swappliances', content:'swappliance', hidden: !!!appdb.config.features.swappliance}
				];
				$.each(items, function(i,e){
					if( e.hidden === true ) return;
					var a = $(document.createElement("a")).unbind("click").html("<img src='/images/"+e.image+"' border='0'/><span>" + e.title + "</span>");
					if( e.href ){
						$(a).attr("href", e.href).attr("target","_blank");
					} else if( e.onClick ){
						$(a).attr("onClick", e.onClick);
					} else {
						$(a).bind("click", (function(item){
							return function(ev){
								ev.preventDefault();
								appQuickLink( id, 2, {mainTitle: name, content: item.content} );
								$(".dijitPopup.dijitTooltipDialogPopup").remove();
								return false;
							};
						})(e));
					}
					$(data).append(a);
				});
				popup(this,data);
				ev.preventDefault();
				return false;
			});
		},1);
	};
})();

var setupApplicationVOs = (function(){
	var _currentPopup = null;
	var popup = function(dom,data){
		if(_currentPopup!==null){
			_currentPopup.cancel();
			dijit.popup.close(_currentPopup);
			_currentPopup.destroyRecursive(false);
		}
		_currentPopup =  new dijit.TooltipDialog({content : $(data)[0]});
		dijit.popup.open({
			parent : $(dom)[0],
			popup: _currentPopup,
			around : $(dom)[0],
			orient: {'BR':'TR','BL':'TL'}
		});
	};
	return function(e){
		setTimeout(function(){
			$(e).find("span.app-vos span.app-vo a.voname").unbind("click").bind("click",function(ev){
				var id = $(this).text();
				var name = $(this).text();
				$(".dijitPopup.dijitTooltipDialogPopup").remove();
				var data = $(document.createElement("div")).addClass("vopopup");
				var items = [
					{image: 'category1.png', title: 'View associated software', content:'software'},
					{image: 'category34.png', title: 'View associated vappliances', content:'vappliance'},
					{image: 'swapp.png', title: 'View associated swappliances', content:'swappliance', hidden: !!!appdb.config.features.swappliance},
					{image: 'homepage.png', title: 'View Virtual Organization details', onClick: "appdb.views.Main.showVO('"+id+"',{mainTitle: '"+id+"'});"}
				];
				$.each(items, function(i,e){
					if( e.hidden === true ) return;
					var a = $(document.createElement("a")).attr("target","_blank").unbind("click").html("<img src='/images/"+e.image+"' border='0'/><span>" + e.title + "</span>");
					if( e.href ){
						$(a).attr("href", e.href).attr("target","_blank");
					} else if( e.onClick ){
						$(a).attr("onClick", e.onClick);
					} else {
						$(a).bind("click", (function(item){
							return function(ev){
								ev.preventDefault();
								appQuickLink( id, 6, {mainTitle: name, content: item.content} );
								$(".dijitPopup.dijitTooltipDialogPopup").remove();
								return false;
							};
						})(e));
					}
					$(data).append(a);
				});
				popup(this,data);
				ev.preventDefault();
				return false;
			});
		},1);
	};
})();

function leaveMessageUnsigned(){
	var unsignedUserMessage = $('<div title="Leave user a message"><p><span class="ui-icon ui-icon-info" style="float:left; margin:0 7px 20px 0;"></span>If you wish to leave this user a message, please <b>Sign In</b> first with your account.<br/></p></div>');
	try{
		unsignedUserMessage.dialog({
			dialogClass: 'info',
			autoOpen: false,
			resizable: false,
			width:400,
			height:190,
			modal: true,
			buttons: {
				OK: function() {
					$(this).dialog('close');
				}
			}
		});	
		setTimeout(function(){
			try{$(unsignedUserMessage).dialog("open");}catch(e){}
			$("body").find(".ui-widget-overlay").css({"width":"100%","height":"100%","position":"fixed"});
		},10);
	} catch(e){}
}

function registrerCustomMiddleware(){
	var span = document.createElement("span");
	$(span).addClass("addCustomMiddleware");
	$("span.app-mws").after(span);
	if(managedCustomMiddlewares != null){
		managedCustomMiddlewares.unsubscribeAll();
		managedCustomMiddlewares.reset();
	}
	managedCustomMiddlewares = new appdb.views.CustomMiddlewareHandler({container: $(span), listcontainer: $("span.app-mws")});
}

function initCharacterCounter(){
	if( $(".app-desc > .dijitTextBox").length === 0 ) return;
	if( $(".app-desc").next(".maxlength-tip").length === 0 || $(".app-desc").next(".maxlength-tip").find(".usage").length === 0 ) return;
	
	var MAX_CHARS = 200;
	var uc = $(".app-desc").next(".maxlength-tip").find(".usage");
	var cc = dijit.byNode($(".app-desc > .dijitTextBox:last")[0]);
	var counter = function(){
		var cl = cc.get("displayedValue").length;
		if( cl === MAX_CHARS ){
			$(uc).addClass("maxlength").text("reached maximum of " + MAX_CHARS + " characters");
		} else {
			$(uc).text("using " + cl + " of " + MAX_CHARS + " characters");
		}
	};
	dojo.connect(cc, "onKeyUp", function(){
		counter();
	});
	dojo.connect(cc, "onMouseUp", function(){
		counter();
	});
	dojo.connect(cc, "onChange", function(){
		counter();
	});
}

appdb.utils.DataWatcher.Registry.set("application",{items :[
 {selector : function(watcher){
   if(managedAppCategoriesEditor && managedAppCategoriesEditor!==null){
	 return managedAppCategoriesEditor.hasChanges();
   }
   return false;
 }, type : "category", name: "Category Type"},
 {selector : ".app-name > .dijitTextBox" ,type : "name", name : "Software Name"},
 {selector : ".app-desc > .dijitTextBox", type : "description", name:"Software Description"},
 {selector : function(watcher){
   if(managedDisciplinesEditor && managedDisciplinesEditor!==null){
	 return managedDisciplinesEditor.hasChanges();
   }
   return false;
 }, type : "domain", name : "Disciplines"},
 {selector : ".app-subdomain > .dijitComboBox", type : "subdomain", name : "Subdiscipline"},
 {selector : function(watcher){
   if(managedAppUrlsEditor !== null){
	return managedAppUrlsEditor.hasChanges();
   }
   return false;
 }, type : "url",  name : "Software Url"},
 {selector : "textarea[name='abstract'].dijitTextArea", type : "abstract", name : "Software Abstract"},
 {selector : ".app-country > .dijitComboBox", type:"country" , name : "Related Countries"},
 {selector : ".app-vo > .dijitComboBox",type : "vo", name : "Virtual Organization"},
 {selector : ".app-mw > .dijitComboBox",type : "mw", name: "Middleware"},
 {selector : ".app-proglang > .dijitComboBox",  type : "language", name : "Programming languages"},
 {selector : ".app-mw.other input[name^=mw]", type: "mw", name: "Custom Middleware"},
 {selector : ".app-mw.other input[name^=lmw]", type: "lmw", name: "Custom Middleware Link"},
 {selector : ".app-status > .dijitComboBox", type : "status", name : "Status"},
 {selector : ".app-owner> .dijitComboBox", type : "owner" , name :"Owner"},
 {selector : function(watcher){
   if(managedSciCons && managedSciCons!==null){
	 return managedSciCons.hasChanges();
   }
   return false;
 },type : "contacts" , name : "Contacts"},
  {selector : function(watcher){
   if(appdb.pages.application.currentOrganizationRelationList() !==null){
	 return appdb.pages.application.currentOrganizationRelationList().hasChanges();
   }
   return false;
 },type : "organizations" , name : "Organizations"},
 {selector : function(watcher){
   if(appdb.pages.application.currentProjectRelationList() !==null){
	 return appdb.pages.application.currentProjectRelationList().hasChanges();
   }
   return false;
 },type : "projects" , name : "Projects"},
 {selector : function(watcher){
   if(appdb.pages.application.currentSoftwareRelationList() !==null){
	 return appdb.pages.application.currentSoftwareRelationList().hasChanges();
   }
   return false;
 },type : "softwarerelation" , name : "Connected Software"},
  {selector : function(watcher){
   if(appdb.pages.application.currentVapplianceRelationList() !==null){
	 return appdb.pages.application.currentVapplianceRelationList().hasChanges();
   }
   return false;
 },type : "vappliancerelation" , name : "Connected Virtual Appliances"},
  {selector : function(watcher){
   if(appdb.pages.application.currentExternalRelationList() !==null){
	 return appdb.pages.application.currentExternalRelationList().hasChanges();
   }
   return false;
 },type : "externalrelation" , name : "External references"},
 {selector : 
   function(watcher){
	var sd = serializeAppDocs();
	if($(":input[name='initialDocuments']:last").val() === sd || sd === "UNCHANGED"){
	 return false;
	}
	return true;
   
  }, "type" :"docs" , "name" : "Publications"},
  {selector : function(watcher){
	var lic = appdb.pages.application.currentSoftwareLicenses();
	if( !lic ) return false;
	return lic.hasChanges();
 },type : "licenses" , name : "Licenses"}],
 canCheckType : function(type){
  if($(":input[name='tool']").length===0){
   return true;
  }
  var chk = dijit.byNode($(":input[name='tool']").parent()[0]);
  if(typeof chk === "undefined"){
   return true;
  }
  return true;
 }});
appdb.utils.DataWatcher.Registry.set("vappliance",{items :[
	 {selector : ".workingversion > .vappliance-version .property.vaversion-version > .value > .dijitTextBox" ,type : "version", name : "Version"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-expireson > .value > .dijitTextBox :input[type='hidden']" ,type : "expires", name : "Expiration Date"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-title > .value > .dijitTextBox" ,type : "title", name : "Description"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi", type: "vmis", name: "Added/Removed VMI Groups"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vavmi-description > .value > .dijitTextBox", type: "group", name:"Group Title"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion", type: "imagelist", name: "Added/Removed Images"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-version > .value > .dijitTextBox", type: "imageversion", name: "Image Version"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-url > .value > .dijitTextBox", type: "imageurl", name: "Image Location"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-integrity > .value > .dijitCheckBox", type: "imageintegritycheck", name: "Image Integrity Check"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .manualintegrity .property.vmiversion-checksum512 > .value > .dijitTextBox", type: "imagechecksum", name: "Image SHA512 Checksum"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .manualintegrity .property.vmiversion-size > .value > .dijitTextBox", type: "imagesize", name: "Image Size"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-title > .value > .dijitTextBox", type: "imagetitle", name: "Image Title"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-description > .value > textarea", type: "imagedescription", name: "Image Description"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-notes > .value >  textarea", type: "imagenotes", name: "Image Comments"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-os > .value > .dijitComboBox", type: "imageos", name: "Image OS Family"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-osversion > .value > .dijitTextBox", type: "imageosversion", name: "Image OS Version"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-arch > .value > .dijitComboBox", type: "imagearch", name: "Image Architecture"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-format > .value > .dijitComboBox", type: "imageformat", name: "Image File Format"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-mincores > .value > .dijitSelect", type: "imagemincores", name: "Image Minimum Number of Cores"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-recomcores > .value > .dijitSelect", type: "imagereccores", name: "Image Recommended Number of Cores"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-minram > .value > .dijitSelect", type: "imageminram", name: "Image Minimum RAM"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-recomram > .value > .dijitSelect", type: "imagerecram", name: "Image Recommended RAM"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-acctype > .value > .dijitSelect", type: "imageacctype", name: "Image Type of accelerators"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-minacc> .value > .dijitSelect", type: "imageminacc", name: "Image Minimum amount of accelerators"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-recomacc > .value > .dijitSelect", type: "imagerecacc", name: "Image Recommended ammount of accelerators"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .property.vmiversion-hypervisor > .value > .dijitComboBox ", type: "imagehyper", name: "Image Hypervisor"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .trafficrules-component > .ruleslist > li > .dijitSelect", type: "imagetrafficrule", name: "Traffic Rule"},
	 {selector : ".workingversion > .vappliance-version .property.vaversion-vmilist > .property.vaversion-vmis > ul > li > .property.vaversion-vmi .property.vaversion-vmiversionlist > ul > li > .property.vaversion-vmiversion .trafficrules-component > .ruleslist > li > .portrange > ul > li > .portrangeitem > .dijitTextBox", type: "imagetrafficrulerange", name: "Traffic Rule Port Ranges"}
]});
appdb.utils.DataWatcher.Registry.set("swappliance",{items:[
	{selector: ".contextualization-version > .header .context-version-property .value > .dijitTextBox",type: "contextversion", name: "Version"},
	{selector: ".contextualization-version > .header .context-description-property .value > textarea",type: "contextdescription", name: "Description"},
	{selector: ".contextscript-list > .contextscript-item .value.location > .dijitTextBox", type: "location", name:"Location"},
	{selector: ".contextscript-list > .contextscript-item .value.format > .dijitSelect", type: "format", name:"Format"},
	{selector: ".contextscript-list > .contextscript-item .value.description > .dijitTextBox", type: "description", name:"Description"},
	{selector: ".contextscript-list > .contextscript-item .value.notes > textarea", type: "notes", name:"Notes"},
	{selector: ".contextscript-list > .contextscript-item .vappliances-list .vappliance-item .images > .content > .image-item",type: "vappliance", name:"Virtual Appliance" }
]});
appdb.utils.DataWatcher.Registry.set("person",{items :[
 {selector : function(watcher){
	var src = $("#pplimg" + dialogCount).attr("src");
	if(src && src.indexOf("/upload/pplimage")>-1){
		return true;
	}
	return false;
 }, type : "newimage", name : "Profile image"},
 {selector : ":input[name='firstName']:last", type : "firstname", name : "First Name"},
 {selector : ":input[name='lastName']:last", type : "lastname", name : "Last Name"},
 {selector : ":input[name='gender']:last", type : "gender", name : "Gender"},
 {selector : ":input[name='positionTypeID']:last",  type : "positiontypeid" , name : "Role"},
 {selector : ":input[name='institution']:last",  type : "institution" , name : "Institute"},
 {selector : ":input[name='countryID']:last",  type : "countryID", name : "Country"},
 {selector : ":input[name^='contactType']",  type : "contactType", name : "Contact type"},
 {selector : ":input[name^='contact']",  type : "contact", name : "Contact value"},
 {selector : function(watcher){
   if(appdb.pages.Person.currentOrganizationRelationList() !==null){
	 return appdb.pages.Person.currentOrganizationRelationList().hasChanges();
   }
   return false;
 },type : "organizations" , name : "Organizations"},
 {selector : function(watcher){
   if(appdb.pages.Person.currentProjectRelationList() !==null){
	 return appdb.pages.Person.currentProjectRelationList().hasChanges();
   }
   return false;
 },type : "projects" , name : "Projects"}
]});

appdb.utils.DataWatcher.Registry.set("faqs",{items: [
 {selector: function(watcher){
	return appdb.utils.Faq.changedOrdering();
 }, type: "ordering", name : "Faqs ordering"},
 {selector: function(watcher){
	return appdb.utils.Faq.NewFaqHandler.isOpen();
 }, type: "newfaq", name: "Registering new faq item"},
 {selector: function(watcher){
	return ($(".faq.headline > div.dijitTextBox").length>0)?true:false;
 }, type: "editfaq", name: "Editing faq item"}
]});
