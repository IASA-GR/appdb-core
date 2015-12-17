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
var exportDlg;
var appDlg2;
var pplDlg2;
var searchDlg;
var customFilter;
var exportForm;
var list = null;

var _SyntacticHelpMsg;
function SyntacticHelpMsg() {
	if ( typeof _SyntacticHelpMsg === "undefined" ) {
		$.ajax({
			url: "/help/faqa?id=12",
			dataType: 'html',
			async: false,
			success: function(d) {
				_SyntacticHelpMsg = '<div style="max-height:400px; overflow-y: scroll">' + d + '</div>';
			}
		});
	};
	return _SyntacticHelpMsg;
};

function navpaneclicks2 (e) {
	$(e).find(".egimenuarrow").attr('src','/images/egiarrowdown.png');
	if (dijit.byId($(this).attr("id")) !== undefined) if ( ! dijit.byId($(e).attr("id")).open ) dijit.byId($(e).attr("id")).toggle();
	var s = $(e);
	$("#panediv").find("div.dijitTitlePane").each(function() {
		if ( $(this).attr("id") != s.attr("id") ) {
			if (dijit.byId($(this).attr("id")) !== undefined) if ((dijit.byId($(this).attr("id")).open) && ($(this).attr("id") != "newspane")) dijit.byId($(this).attr("id")).toggle();
			$(this).find(".egimenuarrow").attr('src','/images/egiarrowleft.png');
		};
	});
}
function navpaneclicks (e){
	var id  = ((typeof e === 'object')?$(e).attr("id"):e);
	var a = dijit.byId(id),n;
	if(a){
		if(a.open){
			dojo.forEach( dojo.query("div#panediv > .dijitTitlePane"),function(node){
				n=dijit.byNode(node);
				if(n && n.open){
					n.toggle();
				}
			});
		}else{return;}
		a.toggle();
	}
}
function appQuickLinkOld(e,t) {
	var i;
	switch(t) {
		case 1:
			i = '"middleware.name:'+e+'"';
			break;
		case 2:
			i = 'country.id:'+e+'';
			break;
		case 4:
		   	i = 'discipline.id:'+e+'';
			break;
		case 5:
		   	i = 'subdiscipline.id:'+e+'';
			break;
	}
	appdb.views.Main.showApplications({flt: i});
}

function appQuickLink(e,t,ext,entityType){
    var i, m = appdb.views.Main;
    ext = ext || {};
    ext.isBaseQuery = true;
	entityType = $.trim(entityType) || "";
	var entityFilter = "";
	if( entityType === "vappliance"){
		entityFilter += " +=category.id:34";
	}else if(entityType === "software"){
		entityFilter += " -=category.id:34";
	}
    switch(t) {
            case 1: //middleware
                    i = '"&=middleware.name:'+e+'"' + entityFilter;
					ext.isBaseQuery = false;
					delete ext.mainTitle;
					ext.append = false;
                    m.showApplications({flt: i},ext);
                    break;
            case 2: //country
                    i = '+=&country.id:'+e+'' + entityFilter;
					ext.isBaseQuery = false;
					delete ext.mainTitle;
					ext.isList = false;
                    m.showApplications({flt: i},ext);
                    break;
            case 4: //discipline
                    i = '+=&discipline.id:'+e+'' + entityFilter;
					ext.isBaseQuery = false;
					delete ext.mainTitle;
					ext.append = false;
                    m.showApplications({flt: i},ext);
                    break;
            case 5: //subdiscipline
                    i = '&=subdiscipline.id:'+e+'' + entityFilter;
                    m.showSubdiscipline({flt: i},ext);
                    break;
			case 6: //VOs
					delete ext.mainTitle;
					i = '&=vo.name:'+e+'' + entityFilter;
					ext.isBaseQuery = false;
					ext.append = false;
                    m.showApplications({flt: i},ext);
					break;
			case 7: //programming languages
					i = '+=&application.language:'+e+'' + entityFilter;
					ext.isBaseQuery = false;
					delete ext.mainTitle;
					ext.isList = false;
					m.showApplications({flt: i},ext);
					break;					
				
    }
}

function closeDialog() {
	shortcut.remove("esc");
}

function showFlash() {
	if (detailsStyle == 1) if ($("#detailsdlg"+dialogCount)[0] !== undefined) dijit.byId("detailsdlg"+dialogCount).destroyRecursive();
	if (RelAppIntId) clearInterval(RelAppIntId);
}

function goDetailsBack() {
	if ($.trim(detailsListHtml) === '') {
			$("#details").empty();
			$("#details").hide();
			showHome();
			$("#main").fadeIn("fast");
    } else if (detailsListHtml !== null) {
			$("#details").empty();
			$("#details").hide();
			$("#main").fadeIn("fast");
			animateList();
	}
    detailsListHtml = null;
}

function setDetailsDisplayStyle(style) {
    if (style !== null) {
		detailsStyleAuto = false;
        detailsStyle = style;
    } else {
		detailsStyleAuto = true;
		checkDetailsStyle();
	}
}

function showNGIDetails(url) {
	showDetails(url,'Resource Provider Details');
}

function showVODetails(url) {
	showDetails(url, 'VO Details');
}

function showAppDetails2(url) {
	detailsStyle = 0;
	var qs=url.split('?')[1];
	var parms=qs.split('&');
	var _id;
	for(i=0;i<parms.length;i++){
		if ( parms[i].split("=")[0] === 'id' ) {
			_id = parms[i].split("=")[1];
			break;
		}
	}
	appdb.views.Main.showApplication({id: _id});
}

function showAppDetails(url, edit, histid, entitytype) {
	detailsStyle = 0;
	$('#details').addClass("editmode").addClass("register").removeClass("viewmode");
	showDetails(url,'Software Details', histid, undefined, entitytype);
	if (edit) {
		$("#infodiv"+dialogCount).ready(function(){
			appdb.utils.ExecuteOnLoad(function(){
				$(".app-status").text("In Production");
				$('#details').removeClass("editmode").removeClass("register").addClass("viewmode");
				onAppEdit(null);
				$("#docdiv"+dialogCount).hide();
				appdb.pages.application.onApplicationLoad();
			},{
				id : "RegisterNewApplication",
				time:800,
				checker:function(){return (typeof appdb.pages.application.onApplicationLoad !== "undefined")?true:false;}
			});
		});
	}
}

function showPplDetails(url, edit) {
	detailsStyle = 0;
	showDetails(url,'Person Profile');
	if (edit) {
		if(typeof onEdit === "function"){
			onEdit = undefined;
		}
		$("#infodiv"+dialogCount).ready( function(){
			var onEdit_loader = function(){
				if(typeof onEdit  === "function"){
					onEdit();
					$("#docdiv"+dialogCount).hide();
				}else{
					setTimeout(onEdit_loader,100);
				}
			};
			onEdit_loader();
		});
	}
}

function showNGIDetails2(url) {
	detailsStyle = 0;
	var qs=url.split('?')[1];
	var parms=qs.split('&');
	var _id;
	for(i=0;i<parms.length;i++){
		if ( parms[i].split("=")[0] === 'id' ) {
			_id = parms[i].split("=")[1];
			break;
		}
	}
	appdb.views.Main.showNgi({id: _id}, {mainTitle: ' '});
}

function showVODetails2(url) {
	detailsStyle = 0;
	var qs=url.split('?')[1];
	var parms=qs.split('&');
	var _id;
	for(i=0;i<parms.length;i++){
		if ( parms[i].split("=")[0] === 'id' ) {
			_id = parms[i].split("=")[1];
			break;
		}
	}
	appdb.views.Main.showVO(_id, {mainTitle: ' '});
}

function showPplDetails2(url) {
	detailsStyle = 0;
	var qs=url.split('?')[1];
	var parms=qs.split('&');
	var _id;
	for(i=0;i<parms.length;i++){
		if ( parms[i].split("=")[0] === 'id' ) {
			_id = parms[i].split("=")[1];
			break;
		}
	}
	appdb.views.Main.showPerson({id: _id}, {mainTitle: ' '});
}

function showDetails(url, title, histid, histtype, entitytype,dispatcher) {
	$("#details").removeClass("editmode").removeClass("viewmode").removeClass("register");//clear form previous calls
	detailsStyle = 0;
	if (detailsStyle == 1) if ($("#detailsdlg"+dialogCount)[0] !== undefined) dijit.byId("detailsdlg"+dialogCount).onCancel();
	$('div [id^="dijit_TooltipDialog"]').each(function(x){
		try {
			dijit.popup.close(dijit.byId($(this).attr('id')));
		} catch(err) {
		}
	});
	dialogCount++;
	url+="&dc="+dialogCount;
	if ( detailsStyle == 1 ) {
		appDlg = new dojox.Dialog({
			title: "Software Details",
			style: "width: 80%",
			onCancel: showFlash,
			id: "detailsdlg"+dialogCount,
	        href: url
		});
		$('.dijitDialogPaneContent:last')[0].setAttribute("id","detailsdlgcontent"+dialogCount);
		appDlg.show();
	} else {
		detailsListHtml = $("#main").html();
     	$("#main").hide();
        var params={};
        if ( typeof histid !== 'undefined' ) params.histid = histid;
        if ( typeof histtype !== 'undefined' ) params.histtype = histtype;
		if ( typeof entitytype !== 'undefined' ) params.entitytype = entitytype;
		$("#details").addClass("loading").fadeOut("slow");
		showAjaxLoading();
		var xhr = $.get(
    	    appdb.config.endpoint.base+url, params, function(data, textStatus) { 
				$('#details').removeClass("loading");
				var injecttop = '';
				var injectbottom = '';
				/*If dispatcher is given then set global autoinit to false
				 *so that the autoinited code won't run (e.g application.pages.application.init({...});
				 *and runs given dispatcher instead. This is used in case application data already 
				 *given and a custom dispatcher( e.g. appdb.pages.application.init) is constructed 
				 *
				 */
				if( dispatcher && typeof dispatcher.func === "function"){
					injecttop = '<script type="text/javascript">window.autoinit = false;</script>';
					injectbottom = '<script type="text/javascript">window.autoinit = true;</script>';
				}
				hideAjaxLoading();
				$('#details').html('<div class="detailsdlg" style="height: 100%;"><div class="dijitDialogTitleBar" style="display:none;"><span class="dijitDialogTitle"></span></div><div class="detailsdlgcontent" style="height:100%;">' + injecttop + data + injectbottom +'</div></div>'); 
				if( dispatcher && typeof dispatcher.func === "function" ){
					if( dispatcher.args && dispatcher.args.length === 1 && typeof dispatcher.args[0].dialogCount !== "undefined" ){
						dispatcher.args[0].dialogCount = dialogCount;
					}
					dispatcher.func.apply(null,dispatcher.args);
				}
				$("#details").fadeIn("slow");
			}, 'html'
		);
		if( $.trim(url).toLowerCase().indexOf("apps/details?id=") > -1 ){
			var model = {
				getXhr: (function(x){
					return function(){
						return x;
					};
				})(xhr)
			};
			appdb.pages.application.requests.register(model,"application");
		}
	}
}

jQuery.fn.center = function () {
	this.css("position","absolute");
	this.css("top", ( $(window).height() - this.height() ) / 2+$(window).scrollTop() + "px");
	this.css("left", ( $(window).width() - this.width() ) / 2+$(window).scrollLeft() + "px");
	return this;
};

function showLogo(id,elem) {
	$(elem).css("z-index","888");
	if(!appDlg2 || appDlg2.get("userid")!=id){
		appDlg2 = new dijit.Dialog({
			title: "Logo preview",
			style: "width: auto; position: absolute",
			"class": "nonModal",
			onLoad: function(){
				setTimeout(function(){$(appDlg2.domNode).center();},100);
			},
			onShow: function(){
				setTimeout(function(){
					$(".dijitDialogUnderlay.nonModal_underlay").parent().css({"display":"none"});
					$(appDlg2.domNode).center();
				},10);
				this.set("visible",true);
			},
			onHide: function(){
				this.set("visible",false);
			},
			onMouseOver: function(){
				this.set("hover",true);
			},
			onMouseLeave: function(){
				this.set("hover",false);
			}
		});
		appDlg2.set("appid",id);
		appDlg2.setHref('/apps/showlogo?id='+id);
		appDlg2.startup();
	}
	appDlg2.show();
	$(".dijitDialogUnderlay.nonModal_underlay").parent().css({"display":"none"});
	setTimeout(function(){
		$(appDlg2.domNode).center();
		$(elem).unbind("mouseleave").bind("mouseleave", function(){
			setTimeout(function(){
				if(appDlg2 && !appDlg2.get("hover")){
					appDlg2.hide();
				}
			},100);
		});
	},10);
}

function showImage(id,elem) {
	$(elem).css("z-index","888");
	if(!pplDlg2 || pplDlg2.get("userid")!=id){
		pplDlg2 = new dijit.Dialog({
			title: "Image preview",
			style: "width: auto;position: absolute;",
			"class": "nonModal",
			onLoad: function(){
				setTimeout(function(){$(pplDlg2.domNode).center();},100);
			},
			onShow: function(){
				setTimeout(function(){
					$(".dijitDialogUnderlay.nonModal_underlay").parent().css({"display":"none"});
					$(pplDlg2.domNode).center();
				},10);
				this.set("visible",true);
			},
			onHide: function(){
				this.set("visible",false);
			},
			onMouseOver: function(){
				this.set("hover",true);
			},
			onMouseLeave: function(){
				this.set("hover",false);
			}
		});
		pplDlg2.set("userid",id);
		pplDlg2.setHref('people/showimage?id='+id);
		pplDlg2.startup();
	} 
	pplDlg2.show();
	$(".dijitDialogUnderlay.nonModal_underlay").parent().css({"display":"none"});
	setTimeout(function(){
		$(pplDlg2.domNode).center();
		$(elem).unbind("mouseleave").bind("mouseleave", function(){
			setTimeout(function(){
				if(pplDlg2 && pplDlg2.get("visible") && !pplDlg2.get("hover")){
					pplDlg2.hide();
				}
			},100);
			return false;
		});	
	},10);
	return false;
}

function gotoPage(p) {
	$("#main").fadeOut(); 
	var len = Number($("#length")[0].value);
	var ofs=(p-1)*(len+1);
	var base = $("#viewtype")[0].value;
	var args = '';	
	if (base === "app") {
		base="apps";
		if ($("#subindex")[0].value != "") base=base+"/"+$("#subindex")[0].value;
	}
	if (base === "ppl") base="people";
	if (base === "vos") {
		if ( voCriteria.name != '' ) args = args + '&name='+voCriteria.name;
		if ( voCriteria.domainID != '' ) args = args + '&domain='+voCriteria.domainID;
	}
	ajaxLoad(base+'?gid=&ofs='+ofs+'&len='+len+args,"main");
}

function prevPage() {
	var currentPage = Number($("#currentPage")[0].value);
	gotoPage(currentPage);
}

function nextPage() {
	var currentPage = Number($("#currentPage")[0].value);
	gotoPage(currentPage+2);
}

function setView(mode) {
        var gv = document.getElementById("gridviewimg"), lv = document.getElementById("listviewimg"); //fixes ie8 bug
	if (mode == 2) {
		document.getElementById($("#viewtype")[0].value+"mainlist").setAttribute("class","mainlist2");
		document.getElementById($("#viewtype")[0].value+"mainlist").setAttribute("className","mainlist2");
                if(gv){
                    gv.setAttribute("src","/images/gridview.png");
                }
		if(lv){
                    lv.setAttribute("src","/images/listview_s.png");
                }
		$("#"+$("#viewtype")[0].value+"mainlist li:odd").css('background-color','#FFFFE8');
	} else {
		document.getElementById($("#viewtype")[0].value+"mainlist").setAttribute("class","mainlist");
		document.getElementById($("#viewtype")[0].value+"mainlist").setAttribute("className","mainlist");
                if(gv){
                    gv.setAttribute("src","/images/gridview_s.png");
                }
		if(lv){
                    lv.setAttribute("src","/images/listview.png");
                }
		$("#"+$("#viewtype")[0].value+"mainlist li:odd").css('background-color','white');
	}
	appViewMode = mode;
}

function animClose(id,selected) {
	if (selected) {
		document.getElementById(id).setAttribute("src","/images/close_s.png");
	} else document.getElementById(id).setAttribute("src","/images/close_s.png");
}

function animateList() {
	if ( $.browser.msie ) {
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
					a = dojo.anim(img, props.i, 175)
				});
				dojo.connect(n, "onmouseleave", function(e){
					a && a.stop();
					a = dojo.anim(img, props.o, 175, null, null, 75)
				});
			});
		}
	}

}

function filterHelp(p) {
	setTimeout(function(){
		var dlg = new dijit.TooltipDialog({
			title: "Filter Help",
			height: '400px',
			onCancel: showFlash,
			content: SyntacticHelpMsg()
		});
		dijit.popup.open({
			popup: dlg,
			parent: p,
			around: p,
			orient: {BL:'TL'}                    
		})
	},500);
}

function searchbox(sf) {
	if (sf.val() === "Search...") {
		sf.css("color","#CCCCCC");
	}
	dojo.connect(dijit.byId(sf.attr("id")), "onFocus", function(k) {
		if (sf.val() === "Search...") {
			sf.val('');
			sf.css("color","inherit");
		}
	});
	dojo.connect(dijit.byId(sf.attr("id")), "onBlur", function(k) {
		sf.val($.trim(sf.val()));
		if (sf.val() == '') {
			sf.val('Search...');
			sf.css("color","#CCCCCC");
		}
	});
}

function initItemView() {
	if ( $("#viewtype")[0] !== undefined ) {
		if ( $("#viewtype")[0].value === "app" ) exportForm = '<form id="exportapps" name="exportapps" method="GET" action="apps/export">';
		if ( $("#viewtype")[0].value === "ppl" ) exportForm = '<form id="exportppl" name="exportppl" method="GET" action="people/export">';
	}
	delete exportDlg;
	exportDlg = new dijit.TooltipDialog({
			title: 'Filter',
			content:  exportForm +
					'<input type="text" id="exportType" name="type" style="display:none"/>'+
					'<span style="white-space: nowrap"><a title="Machine oriented, full data export" href="#" onclick="exportApps(\'xml\');">Export to XML</a></span><br/>' +
					'<span style="white-space: nowrap"><a title="Human oriented, concise data export" href="#" onclick="exportApps(\'csv\');">Export to CSV</a></span>' +
					'</form>'
	});
	animateList();
	if ( $("#viewtype")[0] !== undefined ) {
		if ( ($("#viewtype")[0].value === "app" ) || ($("#viewtype")[0].value === "ppl" ) ) {
			var exportApps = function(type) {
				document.getElementById("exportType").value = type;
				var base = $("#viewtype")[0].value;
				if ( base === "app" ) document.getElementById("exportapps").submit();
				if ( base === "ppl" ) document.getElementById("exportppl").submit();
				exportDlg && dijit.popup.close(exportDlg); 
			};
				
			var exportButton = new dijit.form.DropDownButton({
					label: 'Export',
					dropDown: exportDlg
			});
		}
	}
	if ( list != null ) {
		if ( $("#viewtype")[0] !== undefined ) {
			if ( ($("#viewtype")[0].value === "app" ) || ($("#viewtype")[0].value === "ppl" ) ) {
				customFilter = function () {
					dojo.query("table",searchDlg.domNode)[0].innerHTML = customFilterContents();
					dojo.parser.parse(dojo.query("table",searchDlg.domNode)[0]);
				};
				searchDlg = new dijit.TooltipDialog({
					title: 'Filter',
					content: '<table>' +
					customFilterContents() +
					'</table>'
				});
				var searchButton = new dijit.form.DropDownButton({
					id: "searchButton"+ajaxCount,
					label: "Filter",
					dropDown: searchDlg
				});
				detailsHistory = new Array();
				if (document.getElementById('exportdiv') != undefined) {
					exportButton.domNode.style.width='15px';
					document.getElementById('exportdiv').appendChild(exportButton.domNode);
					exportButton.domNode.style.display='none';
					exportDlg.domNode.style.display='none';
				}
			}
			setView(appViewMode);
		}
		dojo.parser.parse(dojo.byId("main"));
	}
}

dojo.addOnLoad(function(){initItemView();});
