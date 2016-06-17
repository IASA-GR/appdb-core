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

/****************************************
 ********   PUBLIC FUNCTIONS     ********
 ***************************************/

/********   FROM INDEX   ********/
var optQueryLen = 15;
function Left(str, n){
	if (n <= 0)
	    return "";
	else if (n > String(str).length)
	    return str;
	else
	    return String(str).substring(0,n);
}

function Right(str, n){
    if (n <= 0)
       return "";
    else if (n > String(str).length)
       return str;
    else {
       var iLen = String(str).length;
       return String(str).substring(iLen, iLen - n);
    }
}

function showHome() {
    if( appdb.pages.loadingStatus === "home"){
		return;
	}
	computeOptQueryLen();
    appdb.views.Main.clearComponents();
	showAjaxLoading();
	$("#mainbody").removeClass("terminal");
	setTimeout(function(){
		showAjaxLoading();
		$("#main").empty().hide();
		$("#details").empty().hide();
		appdb.pages.loadingStatus = "home";
		var homeurl = '/index/home';
		if( appdb.pages.home.isPersonilized() ) homeurl = '/index/customhome';
		$.ajax({url: homeurl, success: function(data,txtStatus) {
			appdb.pages.loadingStatus = "";
			$("#main").html(data);
			appdb.pages.home.init();
			setTimeout(function(){
				$("#navigationmenu .activelistitem").removeClass("activelistitem");
				$("#main").fadeIn("slow");
				hideAjaxLoading();
			},1);
		}});
		appdb.views.Main.selectAccordion("applicationspane"," ");
	},10);
}

function getComputedHeight(theElt){
    return $("#"+theElt).height();
}

function getComputedWidth(theElt){
    return $("#"+theElt).width();
}

function getComputedTop(theElt){
    return $("#"+theElt).offset().top;
}

function getComputedLeft(theElt){
    return $("#"+theElt).offset().left;
}

function checkDetailsStyle() {
	if (detailsStyleAuto) {
		if (document.body.clientHeight < 800) detailsStyle = 0;
	}
}

function computeOptQueryLen(){
	return optQueryLen;
}

function doLogin() {
	$("#loginform").find(':input[name="referrer"]').val(appdb.config.routepermalink || appdb.config.permalink);
	return true;
}

function login(containerid) {
	if ( browser === "__msie" ) {
		alert('This browser is poorly W3C DOM compliant, and therefore support is minimal and limited to anonymous read-only access');
	} else {
			setTimeout(function(){
				dijit.popup.open({
					parent: document.getElementById("logindiv"),		
					popup: loginBox,
					around: document.getElementById("logindiv"),
					orient: {BR:'TR'}
				});
				$(loginBox.domNode).hide();
				$(loginBox.domNode).fadeIn();
				if ( $("input[name=username]")[0] !== undefined ) $("input[name=username]")[0].focus();
			},200);	
		
	}
}

function logout() {
	appdb.utils.checkSession.enable(false);
	appdb.views.Main.logout();
}

function cancelLogin() {
	$('#username').val("");
	dijit.popup.close(loginBox);
	return false;
}

function validateLogin() {
	if ( $('#username').val() === "" ) return false; else return true;
}

function ajaxLoad(url,destination,actions) {
  appdb.views.Main.ajaxLoad(url,destination,actions);
  $(".navigation a[onclick], .menucontainer a[onclick]").each(function(index,elem){
	  if((""+$(elem).attr("onclick")).indexOf("'" + url + "'")>0){
		  $("#panediv a").removeClass("activelistitem");
		  $(elem).addClass("activelistitem");
	  }
  });
}

function ajaxLoad2(url,destination) {			
	detailsListHtml = null;
    $("#details").empty().hide();
	$("#"+destination).hide(); 
	showAjaxLoading();
	$.get(
		url, {}, function(data, textStatus) { 
			$('#'+destination).html(data); 
			hideAjaxLoading(); 
			$("#"+destination).fadeIn("slow");
		}, 'html'
	);
	return false;
}

function getLatestVersion() {
	$.get("/help/latestversion", {}, function(data) {
		if (typeof latestVersion === "undefined") {
			latestVersion = data;
		} else {
			if (latestVersion < data) {
				latestVersion = data; //do not ask again...
				dlg='<div title="New Version Detected" class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-resizable">'+
					   '<div style="height: 200px; min-height: 109px; width: auto;" class="ui-dialog-content ui-widget-content" id="dialog">'+
					      '<p>A new version of the EGI Applications Database is available. Would you like to reload?<br/><b>You will lose any unsaved data if you choose \'Yes\'</b></p>'+
					   '</div>'+
					'</div>';
				$(dlg).dialog({
					autoOpen: true,
					modal: true,
					height: 200,
					width: 400,
					buttons: {
						Yes: function() {
							location.reload();
						},
						No: function() {
							$(this).dialog('close');
						}
					}
				});
			}
		}
	});
}

function selectMenuItem(id){
	if( $(".menucontainer").find("#" + id).length === 0 ) return;
	
	$(".menucontainer > ul > li").each(function(index,elem){
		if($(elem).find("#"+id).length === 0 ) return;
		$(elem).children("a").addClass("activelistitem");
	});
	$("#"+id).addClass("activelistitem");
	appdb.pages.index.updateCategoriesLayout();
	
}
function zoomImage(src,el){
  if( $(el).parent().find(".zoomimage").length > 0 ) {
   if( !$(el).parent().find(".zoomicon:last").data("events") || !$(el).parent().find(".zoomicon:last").data("events").mouseleave ){
	return;
   }else{
	$(el).parent().find(".zoomimage:last").remove();
   }
  }

  $(el).parent().unbind("mouseover").unbind("mouseenter").bind("mouseenter", function(){
	$(this).parent().addClass("hover");
  }).bind("mouseover", function(){
	$(this).parent().addClass("hover");
  }).unbind("mouseleave").unbind("mouseout").bind("mouseleave",function(){
	$(this).removeClass("hover");
  }).bind("mouseout", function(){
	$(this).removeClass("hover");
  });

  var dom = document.createElement("div"), div = document.createElement("div"), zoomback = document.createElement("div");
  $(dom).addClass("zoomimage");
  $(div).addClass("zoomicon").append("<div class='contents'><img src='"+ loadImage("images/search.png") +"' alt=''/></div>").click(function(){
   if(!pplDlg2){
	pplDlg2 = new dijit.Dialog({
	   title: "Image preview",
	   style: "position: absolute;top:0px;left:0px;min-width:150px;min-height:150px;",
	   href: src,
	   onLoad: function(){
		$(pplDlg2.domNode).css({"top":"0px","left":"0px","visibility":"hidden"});
		setTimeout(function(){$(pplDlg2.domNode).css({"visibility":"visible"}).center();},100);
	   }
	});
	pplDlg2.startup();
   }
   pplDlg2.setHref(src);
   pplDlg2.show();
  }).append("<div class='footer'><span>click to zoom</span></div>");
  $(zoomback).addClass("zoomback");
  $(dom).append(zoomback);
  $(dom).append(div);
  $(el).parent().append(dom).addClass("canzoom").addClass("hover");
}
function showLogo(id,el){
 zoomImage('/apps/showlogo?id='+id,el);
}

function showImage(id,el){
 zoomImage('/people/showimage?id='+id,el);
}
function preventDefaultEvent(e){
	var ev = e || window.event;
	ev.cancelBubble = true;
	ev.stopPropagation();
	return false;
};
/**************************************
 ********   PER PAGE FUNCTIONS ********
 *************************************/

appdb.pages = {};
appdb.pages.loadingStatus = "";
appdb.pages.reset = function(){
	for(var i in appdb.pages){
		if( appdb.pages.hasOwnProperty(i)){
			if( appdb.pages[i].reset ){
				appdb.pages[i].reset();
			}
		}
	}
	appdb.pages.index.requests.reset();
};
appdb.pages.index = (function(){
	var page = {};
	page.requests = new appdb.utils.RequestPool("index");
	page.initCodeInbox = function(){
		if(!userID) return;
		window.getUnreadMsgCount = function(){
			$("#unreadCount span.loadingcount").html("<img class='ajaxloader-small-orange' src='/images/ajax-loader-trans-orange.gif' border='0' width='14' height='14' />").css("display","inline-block");
			$("#unreadCount span.unreadcount").css("display","none");
			setTimeout(function(){
				$.ajax({
					type: 'POST',
					url: '/users/getunreadmsgcount',
					success: function(d){
						setMsgCount(d);
					},
					error: function(){
						$("#unreadCount span.loadingcount").empty();
						$("#unreadCount span.unreadcount").css("display","inline-block");
					}
				});	
			},100);  
		};
		

		window.setMsgCount = function(c) {
			$("#unreadCount span.loadingcount").html('');
			$("#unreadCount span.unreadcount").css("display","inline-block");
			$('#unreadCount span.unreadcount').html(c);
		};

		window.delMsg = function(id){
			setTimeout(function(){
				$.ajax({
					type: 'POST',
					url: '/users/delmsg',
					data: {"msgid": id} ,
					error: function(){
					}
				});	
			},100);  
		};
		
		window.readAllMsg = function(){
			setTimeout(function(){
				$.ajax({
					type: 'POST',
					url: '/users/readallmsg',
					success: function(){
						setMsgCount("0");
					},
					error: function(){
						$("#unreadCount span.loadingcount").empty();
						$("#unreadCount span.unreadcount").css("display","inline-block");
					}
				});	
			},100);  
		};
		window.getInbox = function() {
			$("#unreadCount span.loadingcount").html("<img class='ajaxloader-small-orange' src='/images/ajax-loader-trans-orange.gif' border='0' width='14' height='14' />").css("display","inline-block");
			$("#unreadCount span.unreadcount").css("display","none");
			setTimeout(function(){
				$.ajax({
					type: 'POST',
					url: '/users/getinbox',
					success: parseInbox,
					error: function(){
						$("#unreadCount span.loadingcount").empty();
						$("#unreadCount span.unreadcount").css("display","inline-block");
					}
				});	
			},100);  
		};

		window.activeInboxMsg = function(id, hide) {
			$('#inboxtable a').each(function(x){
				if (Left($(this).attr('id'),11) === 'delinboxmsg') {
					if ( $(this).attr('id') !== 'delinboxmsg'+inboxDlgCount+"_"+id ) $(this).remove();
				}
			});
			if(hide && $('#delinboxmsg'+inboxDlgCount+"_"+id).length > 0){
				$('#delinboxmsg'+inboxDlgCount+"_"+id).remove();
				return false;
			}
			if ($("#delinboxmsg"+inboxDlgCount+"_"+id)[0] === undefined) { 
				$('#inboxmsg'+inboxDlgCount+"_"+id+':last').find('td:first').append('<a id="delinboxmsg'+inboxDlgCount+"_"+id+'"  msgid="'+id+'" href="#" style="display:inline-block;width:25px;text-align:center;"><img border="0" src="/images/cancelicon.png"/></a>');
				$('#delinboxmsg'+inboxDlgCount+"_"+id).click(function(e){
					$("#delmsgid").attr("value",$(this).attr("msgid"));
					delMsg($(this).attr("msgid"));
					$('#inboxmsg'+inboxDlgCount+"_"+$(this).attr("msgid")).remove();
					if($("table#inboxtable > tbody > tr").length > 0){
						e.preventDefault();
						return false;
					}
				});
			}
		};

		window.parseInbox = function(xml) {
			$("#unreadCount span.unreadcount").css("display","inline-block");
			$("#unreadCount span.loadingcount").empty();
			var dlgContent = '<form id="inboxform" method="post" onsubmit="return validateLogin();" action="users/login"><div style="width:auto;max-width:750px; max-height:400px; overflow-x: hidden; overflow-y: auto"><table id="inboxtable" style="max-width:95%" cellpadding="5" cellspacing="0">';
			var hasMessages=false;
			$(xml).find("Message").each(function(x) {
				hasMessages = true;
				dlgContent = dlgContent + '<tr id="inboxmsg'+inboxDlgCount+"_"+$(this).find("id").text()+'" onmouseover="activeInboxMsg(\''+$(this).find("id").text()+'\');" ><td style="vertical-align:middle; width:25px;">';
				if (! eval($(this).find("isRead").text())) { /*dlgContent = dlgContent;*/ }
				dlgContent = dlgContent + '</td><td style="vertical-align:middle;">';
				if ($(this).find("senderID")[0] !== undefined) dlgContent = dlgContent + '<a href="#">';
				dlgContent = dlgContent + '<img border="0" title="';
				var senderName;
				var senderImage;
				if ( ($(this).find("senderID")[0] !== undefined) && ($(this).find("senderID").text() !== 'NULL') ) {
					senderName = $(this).find("senderName").text(); 
					senderImage = '/people/getimage?id=' + $(this).find("senderID").text() + '" onclick="appdb.views.Main.showPerson({id: ' + $(this).find("senderID").text() + ',cname: \''+$(this).find("senderCName").text()+'\'}, {mainTitle: \'' + $(this).find("senderName").text().replace(/'/g,"\\'") + '\'})';
				} else {
					senderName = 'System Notification';
					senderImage = '/images/warn50.png';
				}
				dlgContent = dlgContent + senderName + '" width="50px" src="' + senderImage;
				dlgContent = dlgContent + '"/>';
				if ($(this).find("senderID")[0] !== undefined) dlgContent = dlgContent + '</a>';
				var msgText;
				if ($(this).find("senderID")[0] === undefined) msgText = $(this).find("msg").text(); else msgText = $(this).find("msg").text();
				var senton = $(this).find("sentOn").text() || "";
				if(senton){
					senton = senton.split(".")[0];
				}
				dlgContent = dlgContent + '</td><td><span style="white-space:nowrap"><b>'+senderName+', '+senton+'</b></span><br/><div style="max-width:450px; overflow-y:auto; overflow-x:auto;position:relative;"><p>'+msgText+'</p>';
				dlgContent = dlgContent + '<div class="inboxreply"><a href="/store/person/'+$(this).find("senderCName").text()+'" title="Click to reply" onclick="appdb.views.Main.showPerson({id: ' + $(this).find("senderID").text() + ',cname: \''+$(this).find("senderCName").text()+'\'}, {mainTitle: \'' + $(this).find("senderName").text().replace(/'/g,"\\'") + '\', tab:\'replymessage\'});">reply</a></div>';
				dlgContent = dlgContent + '</div></td></tr>';
			});	
			dlgContent = dlgContent + '</table><table width="100%"><tr><td style="text-align:right" colspan="2"><!--<button onclick="hideInbox();" dojotype="dijit.form.Button">Close</button>--></td></tr></table></div></form>';
			if (!hasMessages) {dlgContent = dlgContent + '<p>No messages!</p>';};
			inboxDlg = new dijit.TooltipDialog({
				title: 'Inbox',
				content: dlgContent
			});
			$('#unreadCount span.unreadcount').html('0');
			readAllMsg();
			setTimeout(function(){
				dijit.popup.open({
					parent: document.getElementById("inboxdiv"),
					popup: inboxDlg,
					around: document.getElementById("inboxdiv"),
					orient: {BR:'TR'}
				});
				$('#inboxtable tr:odd').css('background-color','#FFFFB8');	
				$(inboxDlg.domNode).hide();
				$(inboxDlg.domNode).fadeIn();
			},200);
		};

		window.showInbox = function() {
			inboxDlgCount++;
			if (inboxDlg === undefined) getInbox(); else hideInbox();
		};

		window.hideInbox = function() {
			dijit.popup.close(inboxDlg);
			inboxDlg = undefined;
		};
	};
	page.initCodeLogin = function(){
		window.loginBoxContent = '<form onsubmit="return doLogin();" id="loginform" name="loginform" method="post" onsubmit="return validateLogin();" action="' + appdb.config.endpoint.base + 'users/login'+ ((appdb.config.appenv!=='production')?'dev2':'') + '"><table width="100%"><tr><td colspan="3"><span style="font-size:8pt">Please provide your <a target="_blank" href="https://www.egi.eu/sso/">EGI SSO</a> credentials</span><br/><br/></td></tr><tr><td><label for="username">Username:</label></td><td colspan="2" style="text-align:right"><input name="username" id="username" dojoType="dijit.form.TextBox" /></td></tr>' +
		'<tr><td><label for="password">Password:</label></td><td colspan="2" style="text-align: right"><input type="password" name="password" id="password" dojoType="dijit.form.TextBox"/><input type="hidden" name="referrer"/></td></tr>' +
		'<tr><td style="display: none; vertical-align:middle"><label for="rememberme">Remember me</label></td><td style="vertical-align:middle"> <input style="display:none" dojoType="dijit.form.CheckBox" name="rememberme"/></td><td style="text-align: right"><button onclick="cancelLogin();" dojotype="dijit.form.Button">Cancel</button><button type="submit" dojotype="dijit.form.Button">OK</button></td></tr></table></form>';

		window.loginBox = new dijit.TooltipDialog({
			title: 'Login',
			content: loginBoxContent
		});
	};
	page.init = function(){
		appdb.pages.reset();
		page.currentContent("home");
		page.canManageDatasets(false);
		//Init Global Code
		for(var i in page){
			if( page.hasOwnProperty(i) && i.substr(0,8) === "initCode" ){
				page[i]();
			}
		}
		//Init event handlers
		for(i in page){
			if( page.hasOwnProperty(i) && i.substr(0,7) === "onEvent" ){
				page[i]();
			}
		}
		//Run immediate code
		page.immediate();
	};
	page.navigationList = new appdb.utils.SimpleProperty();
	page.navigationListSW = new appdb.utils.SimpleProperty();
	page.navigationListVApps = new appdb.utils.SimpleProperty();
	page.inboxUnreadDispatcher = new appdb.utils.SimpleProperty();
	page.currentContent = new appdb.utils.SimpleProperty();
	page.canManageDatasets = new appdb.utils.SimpleProperty();
	page.getCurrentContent = function(){
		return $.trim($("body .mainnavpanel .selectedcontent ul li.current").data("content")).toLowerCase();
	};
	page.setCurrentContent = function(content, force, metadata){
		content = $.trim(content).toLowerCase() || page.getCurrentContent();
		force = (typeof force === "boolean")?force:true;
		var clscontent = $.trim(content);
		if( clscontent === "swappliance" ){
			clscontent = "vappliance";
		}
		$("body .mainnavpanel ul li.current").removeClass("current");
		if( content === 'people' ){
			content = 'researchers';
		}
		if( content === 'datasets' || content === 'dataset'){
			content='dataset';
			clscontent = 'home';
		}
		if( $.inArray(content, ["home","admin","pages"]) > -1 ){
			$("body .mainnavpanel .selectedcontent ul li[data-contenttype='home']").addClass("current");
			$("body .mainnavpanel .contentlist ul li button[data-contenttype='home']").parent().addClass("current");
		}else{
			$("body .mainnavpanel .selectedcontent ul li[data-contenttype='" + clscontent + "']").addClass("current");
			$("body .mainnavpanel .contentlist ul li button[data-contenttype='" + clscontent + "']").parent().addClass("current");
		}
		
		$("body").attr("data-content",content || 'home');
		
		if($.browser && $.browser.safari){
			$("body").toggleClass("safarifix");
		}
		var metaext = (metadata && metadata.ext )?metadata.ext:{};
		page.currentContent(content);
		if( force ){
			switch(content){
				case "software":
					var ext = $.extend({isBaseQuery:true, mainTitle: 'Most recent software',filterDisplay: 'Search software...', content: 'software'}, metaext);
					appdb.views.Main.showApplications({flt:'+*&application.metatype:0',orderby:'dateadded',orderbyOp:'DESC'},ext);
					break;
				case "vappliance":
					var ext = $.extend({isBaseQuery:true, mainTitle: 'Most recent virtual appliances',filterDisplay: 'Search virtual appliances...', content: 'vappliance'}, metaext);
					appdb.views.Main.showApplications({flt:'+*&application.metatype:1',orderby:'dateadded',orderbyOp:'DESC'},ext);
					break;
				case "swappliance":
					var ext = $.extend({isBaseQuery:true, mainTitle: 'Most recent software appliances',filterDisplay: 'Search software appliances...', content: 'swappliance'}, metaext);
					appdb.views.Main.showApplications({flt:'+*&application.metatype:2',orderby:'dateadded',orderbyOp:'DESC'},ext);
					break;
				case "people":
				case "researchers":
					var ext = $.extend({filterDisplay: 'Search people...',mainTitle:'Newest profiles'}, metaext);
					appdb.views.Main.showPeople({flt : '',orderby:'dateinclusion',orderbyOp:'DESC'},ext);
					break;
				case "datasets":
					appdb.views.Main.showDatasets();
					break;
				case "home":
				default:
					appdb.views.Main.showHome();
					break;
			}
		}
	};
	page.setupContentNavigation = function(){
		$("body .mainnavpanel .contentlist ul li button").unbind("click").bind("click", function(ev){
			ev.preventDefault();
			var ct = $.trim($(this).data("contenttype")).toLowerCase();
			if( ct === "" ){
				appdb.pages.index.setCurrentContent("home",false);
				return false;
			}
			switch( ct ){
				case "vappliance":
				case "swappliance":
					appdb.views.Main.showCloudMarketplace();
					break;
				case "software":
					appdb.views.Main.showSoftwareMarketplace();
					break;
				case "people":
				case "researchers":
					appdb.views.Main.showPeopleMarketplace();
					break;
				case "datasets":
					appdb.views.Main.showDatasets();
					break;
				default:
					page.setCurrentContent($(this).data("contenttype"));
					break;
			}
			return false;
		});
		if( $.trim($("body").data("content")) === ""){
			appdb.pages.index.setCurrentContent("home",false);
		}
		$("body .mainnavpanel .selectedcontent > ul > li a.supporting").unbind("click").bind("click", function(ev){
			ev.stopPropagation();
		});
		$("body .mainnavpanel .selectedcontent > ul > li").unbind("click").bind("click", function(ev){
			ev.preventDefault();
			appdb.pages.index.setCurrentContent("home");
			return false;
		});
		
	};
	page.setupCategoriesLayout = function(){
		$("#navigationmenu .menu.categorylist ul.list.autohide").each( function(i, e){
			var max = parseInt($.trim($(e).css("max-height")))<<0;
			var h = $(e).height();
			if( max > 0 && max <= h ){
				$(e).addClass("init").attr("data-max",max);
			}
		} );
		page.updateCategoriesLayout();
	};
	page.updateCategoriesLayout = function(){
		$("#navigationmenu .menu.categorylist > ul.list.autohide").each(function(i,e){
			if( $.trim( $(e).attr("data-max") ) === ""  || $(e).data("max") < 1 ) return;
			$(e).find("div.autohide-selector").remove();
			var sel = $(e).find(".cell.value.activelistitem");
			var html = $("<div class='autohide-selector'><a class='action' href='#' title='View more categories'>&#x25BC;</a></div>");
			if( $(sel).length > 0 ){
				var o = ($(sel).closest("li").position().top);
				var mh = ($(e).data("max") << 0);
				mh = (mh > 15 )?(mh-30):mh;
				if( mh < o){
					$(html).prepend($(sel).closest("li").clone());
				}
			}
			$(e).append(html);
			$(e).removeClass("init").removeClass("hovered");
			var more = $(html).find(".action");
			$(more).unbind("mouseenter").bind("mouseenter", function(ev){
				$(this).closest("ul.autohide").stop().animate({"max-height": "1000px"}, 1000,"easeOutQuad", function(){
					$(this).addClass("hovered").addClass("expanded");
				}).find("div.autohide-selector").fadeOut();
			});
			$(e).closest(".menucontents").unbind("mouseleave").bind("mouseleave", (function(thisdom){ 
				return function(ev){
					$(thisdom).stop().animate({"max-height": $(thisdom).data("max") + "px"}, 500,"linear", function(){
						$(thisdom).removeClass("hovered").removeClass("expanded").find("div.autohide-selector").fadeIn();
					});
				};
			})(e));
		});
	};
	page.immediate = function(){
		//Inbox Initialization
		if( userID !== null ) {
			if ( setMsgCountId !== undefined ) clearInterval(setMsgCountId);
			getUnreadMsgCount();
			setMsgCountId = setInterval(getUnreadMsgCount,60000);
		}
		page.setupContentNavigation();
		loadImage('ajax-loader-trans-orange.gif', 'img.ajaxloader-small-orange');
		loadImage('appdb-logo.png', 'img.appdb-logo');
		loadImage('EGI-logo_small.png', 'img.egi-logo-small');
		loadImage('logout3.png', 'img.logout');
		loadImage('notificon3.png', 'img.notificon');
		loadImage('pendingrequests.png', 'img.pendingrequestsimg');
		loadImage('preferences.png', 'img.preferencesimg');
		loadImage('manageaccounts.png', 'img.manageaccountsimg');
		loadImage('header_back70.png', 'td.header_back70', 'background');
		loadImage('header_back70b.png', 'td.header_back70b', 'background');
		loadImage('cloud.png', 'img.cloud');
		loadImage('social_facebook.png', 'img.social.facebook');
		loadImage('social_google.png', 'img.social.google');
		loadImage('social_twitter.png', 'img.social.twitter');
		loadImage('social_linkedin.png', 'img.social.linkedin');
		loadImage('browse_statistics.png', 'img.browse_statistics');
		loadImage('category1.gif', 'img.category1');
		loadImage('person.png', 'img.person');

		//Handle backend user errors and warnings.i.e. New account connection error
		page.initUserWarnings();
		//cache build status
		page.setCacheBuildChecker();
		page.toggleCacheLed();
		page.initProfileMenu();
	};
	page.initUserWarnings = function(){
		if( typeof userWarning !== "undefined" ){
			if( $.trim(userWarning.message) !== "" ){
				appdb.utils.ShowNotificationWarning({
					title: userWarning.title || "Warning",
					message: userWarning.message
				});
				delete userWarning.message;
				if( typeof userWarning.title !== "undefined"){
					delete userWarning.title;
				}
			}
		}
		if( typeof userError !== "undefined" ){
			if( $.trim(userError.message) !== "" ){
				appdb.utils.ShowNotificationDialog({
					title: userError.title || "Warning",
					message: userError.message
				});
				delete userError.message;
				if( typeof userError.title !== "undefined"){
					delete userError.title;
				}
			}
		}
	};
	page.initMessages = function(){
		if( appdb.config.views.home.enableMessages !== true || userID === null) return;
		if( page.inboxUnreadDispatcher() !== null ){
			page.inboxUnreadDispatcher().stop();
			page.inboxUnreadDispatcher().destroy();
			page.inboxUnreadDispatcher(null);
		}
		page.inboxUnreadDispatcher(new appdb.components.InboxUnreadDispatcher());
		page.inboxUnreadDispatcher().start();
	};
	page.initProfileMenu = function(){
		if( !userID ) return;
		$("#profilemenu .linkcontent a.profile").each(function(i, e){
			var tab = '';
			if( $(e).hasClass("pendingrequests") ){
				tab = "pendingrequests";
			}else if( $(e).hasClass("preferences") ){
				tab = "preferences";
			}else if( $(e).hasClass("manageaccounts") ){
				tab = "manageaccounts";
			}
			$(e).unbind("click").bind("click", (function(t){
				return function(ev){
					ev.preventDefault();
					appdb.views.Main.showPerson({id: userID, cname: userCName,tab:t}, {mainTitle: userFullname});
					return false;
				};
			})(tab));
		});
		
		loadImage('pendingreqs.png', 'img.pendingreqs');
		loadImage('profile.png', 'img.profile');
		loadImage('userpreferences.png', 'img.preferences');
		loadImage('feedback.png', 'img.feedback');
		$("#profilemenu").removeClass("hidden");
	};
	page.toggleCacheLed = function(){
		if ( typeof page._cacheBuildCheckerInterval === "undefined" ) {
			page.setCacheBuildChecker(true);
		} else {
			$("img.cacheled").attr("src", "/images/close3.png").attr("title", "Search cache monitoring disabled. Click to enable.");
			clearInterval(page._cacheBuildCheckerInterval);
			page._cacheBuildCheckerInterval = undefined;
		}
	};
	page.setCacheBuildChecker = function(enable, interval){
		if( page._cacheBuildCheckerInterval ){
			clearInterval(page._cacheBuildCheckerInterval);
		}
		if ( userIsAdmin !== true ) { //only admins can use cache build checker
			return;
		}
		if( enable === true || ( (appdb.config.checkers && appdb.config.checkers.cacheBuild && appdb.config.checkers.cacheBuild.enable === false )?false:true ) ){ //check explicit call
			interval = (interval<<0) || ( (appdb.config.checkers && appdb.config.checkers.cacheBuild && (appdb.config.checkers.cacheBuild.interval<<0) > 0 )?(appdb.config.checkers.cacheBuild.interval<<0):1000 );
			page._cacheBuildCheckerInterval = setInterval(function(){
				$.ajax({
					type: 'GET',
					url: '/help/cachebuildcount',
					success: function(data) {
						if ( typeof page._cacheBuildCheckerInterval !== "undefined" ) {
							$("img.cacheled").attr("src", "/images/close" + (data > 0 ? "2" : "") + ".png").attr("title", "Search cache is " + (data > 0 ? " rebuilding..." : " ready"));
						}
					},
					error: function(){
					}
				});	
			}, interval);
		}
	};
	page.loadCategoryNavigationLists = function(){
		var swcatsdom = $("#appsalllink").parent().parent();
		var vappcatsdom =  $("#vappscategories");
		if( $(swcatsdom).children("li").length < 2 ){
			if( page.navigationListSW() ){
				var li = $("#appsalllink").parent().clone(true);
				page.navigationListSW().reset();
				page.navigationListSW().unsubscribeAll();
				page.navigationListSW(null);
				$(swcatsdom).append(li);
			}
			page.navigationListSW(new appdb.views.MainCategoriesTreeView({
				container: $("#appsalllink").parent().parent(), 
				dataFilter: appdb.utils.entity.getCategoryFilterByType("software").compare,
				content: "software"}));
			page.navigationListSW().render();
		}
		if( $(vappcatsdom).children("li").length < 2 ){
			if( page.navigationListVApps() ){
				page.navigationListVApps().reset();
				page.navigationListVApps().unsubscribeAll();
				page.navigationListVApps(null);
			}
			page.navigationListVApps(new appdb.views.MainCategoriesTreeView({
				container: $("#vappscategories"),
				treeViewType: appdb.views.VApplianceCategoriesTreeView,
				dataFilter: appdb.utils.entity.getCategoryFilterByType("vappliance").compare, 
				content: "vappliance"
			}));
			page.navigationListVApps().render();
			$("#vappscategories .cell.id > .textvalue:contains('34')").closest(".row.level1").children(".cell.value").children(".textvalue").text("All vAppliances");
		}
		page.setupCategoriesLayout();
	};
	page.onEventReady = function(){
		//Setup Top Menu
		$('document').ready(function(){
			//Set main category navigation component
			setTimeout(function(){
				page.checkCategoriesUpdate();
			},1);
			
			buildNavPane();
			$.fn.menu = function(o){
				o = o || {};
				var opt = $.extend({
					dom : this,
					direction: o.direction || "horizontal",
					showEvent: o.showEvent || "click",
					showEffect: o.showEffect || "slideDown",
					hideEffect: o.hideEffect || "slideUp"
				},o);
				
				var makeMenu = function(){
					var direction, showEvent;
					$(this).data("menuoptions",opt);
					
					switch( (''+opt.direction).toLowerCase() ){
						case "vertical":
							direction = "vertical";
							break;
						case "horizontal":
						default:
							direction = "horizontal";
							break;
					}
					$(this).addClass(direction);
					$(this.selector + " > li > ul").each(function(index, elem){
						if( $(elem).hasClass("horizontal") || $(elem).hasClass("vertical") ){
							return;
						}
						$(elem).addClass( (direction === "horizontal" )?"vertical":"horizontal");
					});
					
					switch( (''+opt.showEvent).toLowerCase() ){
						case "click":
							showEvent = "click";
							break;
						case "hover":
						default:
							showEvent = "mouseover";
							break;
					}
					var ev = showEvent || "mouseover";
					var eventFunc = function(index,elem){
						$(this).parent().unbind("click").unbind("mouseover");
						if($(this).next("ul").length>0){
							$(this).parent().addClass("container");
							$(this).children("span.arrow").remove();
							var span = document.createElement("span");
							$(span).addClass("arrow");
							if( $(this).parent().parent().hasClass("vertical") ) {
								$(span).addClass("right").html("&#x25B6;");
							} else {
								$(span).addClass("down").html("&#x25BC;");
							}
							$(this).append(span);
						}
						var def_ev = def_ev = (document.ontouchmove)?"touchstart":"mouseover";
						var def_ev_end = (document.ontouchend)?"touchend":"mouseleave";
						
						if( showEvent === "click" ){
							def_ev = (document.ontap)?"tap":"click";
							def_ev_end = "";
						}
						
						if($(this).hasClass("expand-onclick")){
							def_ev = (document.ontap)?"tap":"click";
							def_ev_end = "";
						}
						if( showEvent === "mouseover" ){
							$(this).parent().bind(def_ev,function(e){
								e.preventDefault();
								$(this).addClass("persist");
								$(opt.dom).find(".active").each(function(index,elem){
									if($(elem).find(".persist").length === 0){
										$(elem).removeClass("active");
									}
								});
								$(this).removeClass("persist");
								$(this).addClass("active");
								return false;
							});
						
							$(this).find("a + ul").bind(def_ev, function(e){
								if(showEvent === "hover"){
									$(this).parent("li").addClass("active");
								}
								$(this).parent().addClass("hover");
							}).bind(def_ev_end,function(){
									var elem = this;
									setTimeout(function(){
										if($(elem).find(".active")){
											return;
										}
										$(elem).parent().removeClass("hover");	
									},2000);
							
							});
						} else if( showEvent === "click" ){
							$(this).parent().bind(def_ev,function(e){
								if( $(this).hasClass("container") ){
									e.preventDefault();
									if( $(this).hasClass("active") && $(this).parent().hasClass("menu") ){
										$(this).find(".active").removeClass("active hover");
										$(this).removeClass("active hover");
									}else{
										$(this).addClass("persist");
										$(opt.dom).find(".active").each(function(index,elem){
											if($(elem).find(".persist").length === 0){
												$(elem).removeClass("active");
											}
										});
										$(this).removeClass("persist");
										$(this).addClass("active");	
									}
									return false;
								}
								e.stopPropagation();
								setTimeout(function(){
									$("body").find(".menu .active").removeClass("active hover");
								},1);
								return true;
							});
						}
					};
					$(this).find("li > a").each(function(index, elem){
						eventFunc.call($(this),index,elem);
					});
					page.setupCategoriesLayout();
					if( showEvent === "click" ){
						$("body").bind("click", function(ev){
							$(this).find(".menu .active").removeClass("active").removeClass("hover");
						});
					}else{
						$(opt.dom).children("li").bind("mouseleave",function(){
							if($(opt.dom).find(".hover").lenght>0) return;
							$(opt.dom).find(".active").removeClass("active");
						});
					}
					
				};
				makeMenu.call(this);
			};
			$("div.menucontainer > ul.menu").menu({showEvent: "click"});
			$("div.menucontainer.bottom").show();
			
			if ( $("#adminpane ul > ul > li").length === 0 ) {
				$("#adminpane").empty().remove();
			}
			checkDetailsStyle();
			$("#details").hide();
			$(document).click(function(){
				if (newsfilterbox !== undefined) {
					dijit.popup.close(newsfilterbox);
				}
				if (helpDlg !== undefined) { 
					dijit.popup.close(helpDlg);
					helpDlg = undefined;
				}
				if (inboxDlg !== undefined) {
					dijit.popup.close(inboxDlg);
					inboxDlg = undefined;
				}
				$('div [id^="dijit_TooltipDialog"]').each(function(x){
					try {
						if (dijit.byId($(this).attr('id')).title !== "Login")
							dijit.popup.close(dijit.byId($(this).attr('id')));
					}catch(err) {}
				});
			});
			setTimeout(function(){$("#panediv").find("div.dijitTitlePane").each(function() {
				$(this).click(function(){navpaneclicks(this);});
			});},1000);
			if(userID !== null) { 
				setTimeout(function(){
					var reqcount = userRequestCount;
					var pd = $(".profileimage").last();
					var prdlg = null; //pending requests dialog
					var contents = ""; //pending requests html content

					if(reqcount === 0){
						return;
					}
					contents =  "<div class='profilenotification'>";
					contents += "<span class='title'><span class='count'>"+reqcount+"</span> pending request"+((reqcount===1)?"":"s")+"</span>";
					contents += "<span class='instructions'>You can view the requests from your <a href='/store/person/"+userCName+"' onclick='appdb.views.Main.showPerson({id: " + userID + ", cname: \"" + userCName + "\"}, {mainTitle: \""+userFullname+"\"});' title='View your profile'>profile</a> by selecting the tab <a href='/store/person/"+userCName+"' onclick='appdb.views.Main.showPerson({id: " + userID + ", cname: \""+userCName+"\"}, {mainTitle: \""+userFullname+"\", tab:\"pending requests\"});' title='View your profile'>Pending Requests</a></span>";
					contents += "</div>";

					prdlg = new dijit.TooltipDialog({content:contents});
					dijit.popup.open({
						parent: $(pd)[0],
						popup:  prdlg,
						around: $(pd)[0],
						orient: {BR:'TR'},
						onClose : function(){
							prdlg.destroyRecursive(false);
						}
					});
					setTimeout(function(){
						$(prdlg.domNode).animate({opacity:0},500,function(){
							dijit.popup.close(prdlg);
						});
					},15000);
				},1200);
			}
			(function(){
				var t = new appdb.components.Tags({container: $('#tagcloudmenu')}).subscribe({event:"select", callback: function(){
					$("#tagcloudmenu div.dijitSliderImageHandle.dijitSliderImageHandleH.dijitSliderThumbFocused.dojoMoveItem").unbind("mouseup").bind("mouseup",function(e){
						e.preventDefault();
						return false;
					});
				}});
				var tag_timer = setInterval(function(){
					var ld = appdb.model.Tags.getLocalData();
					if( ld ){
						clearInterval( tag_timer );
						t.load(ld);
					}
				},5);
			})();
			$("#navigationmenu.navigation .menucontents .menu").removeClass("hidden");
			$("#navigationmenu.navigation").removeClass("invisible");
			$(".menu li.animate").each(function(index,el){
				setTimeout(function(){
					var elem = $("<li></li>").append($(el).html());
					$(elem).attr("style","position:absolute;display:block;opacity:1;border-radius:3px;background-color:#F8971D;top:0px;left:0px;");
					$(el).before(elem);
					setTimeout(function(){

						$(elem).animate({"opacity" : "0"}, 30000,function(){$(elem).remove();});
					},10);
				},100);
			});
			
			if($("#personTabContainer > css3-container > ul").length > 0 ){
				setTimeout(function(){$("#personTabContainer > css3-container > ul").removeClass("pie_first-child").addClass("pie_first-child");},100);
			}
		});
		
		dojo.addOnLoad(function() {
			$("#panediv div.dijitTitlePaneTitle").append('<img class="egimenuarrow" src=""/>');
			loadImage("egiarrowleft.png",'img.egimenuarrow');
			// Calculate optQueryLen after everything has loaded.
			$("body").load(function(){
				window.onresize();
				setTimeout(function(){
					computeOptQueryLen();
				},1);
			});
			$('.bar1').attr('style', "height: 4px; background: url('" + loadImage('bar1.png') + "') repeat;");
			if( userID !== null ) {
			var pw = $(".menu ul li.profileitem .profilename").last().width();
			$(".menu ul li.profileitem ul.profilemenu").last().css({"min-width":pw+2+"px"});
			}
		});
		page.initSessionChecker();
	};
	page.checkCategoriesUpdate = function(){
		page.loadCategoryNavigationLists();
	};
	page.onEventBeforeUnload = function(){
	};
	page.currentContentName = function(){
		switch(page.currentContent()){
			case "software":
			case "vappliance":
			case "swappliance":
				return appdb.pages.application.currentName();
			case "researchers":
			case "people":
				return appdb.pages.Person.currentFullName();
			default:
				return;
		}
	};
	page.onEventResize = function(){
		window.onresize = function(event) {
			var nh = ($("#mainNavigation").length>0)?$("#mainNavigation")[0].clientHeight:20;
			nh = (nh<20)?20:nh;
			computeOptQueryLen();
			var detailsleft = Math.floor($("#main").offset().left);
			if ( (detailsleft === 0) || (isNaN(detailsleft)) ) detailsleft = Math.floor($("#main").position().left);
			if ( (detailsleft === 0) || (isNaN(detailsleft)) ) detailsleft = parseInt(getComputedWidth("panediv"))+parseInt(getComputedWidth("leftmargintd"))+8;
			if ( detailsleft !== 0 ) $(".detailsback").css("left",detailsleft+'px');
			$(".detailsback").css("top",Math.floor(document.body.clientHeight/1.80)+'px');
			checkDetailsStyle();
			return;
		};
	};
	page.onLoggedOut = function(){
		appdb.utils.checkSession.enable(false);
		appdb.utils.LoggedOutDialog.display();
	};
	page.onLoggedIn = function(){
		if(userID === null){
			appdb.utils.checkSession.enable(false);
			window.location.href = appdb.config.endpoint.base.replace(/http\:\/\//,"https://");
		}
	};
	page.cancelLoggedInRequest = function(){
		appdb.utils.checkSession.cancelCurrentRequest();
	};
	page.initSessionChecker = function(){
		appdb.utils.checkSession.onLoggedIn(page.onLoggedIn);
		if( userID !== null){
			appdb.utils.checkSession.onLoggedOut(page.onLoggedOut);
		}
		appdb.utils.checkSession.enable(true);
	};
	return page;
})();

appdb.pages.home = (function(){
	var page = {};
	page.reset = function(){
		page.customLists(null);
	};
	page.customLists = appdb.utils.SimpleProperty();
	page.currentBanner_recent = new appdb.utils.SimpleProperty();
	page.currentBanner_toprated = new appdb.utils.SimpleProperty();
	page.currentBanner_newest = new appdb.utils.SimpleProperty();
	page.isPersonilized = function(){
		if( appdb.config.views.home && appdb.config.views.home.personalize === true && userID !== null){
			return true;
		}
		return false;
	};
	page.initHome = function(){
		appdb.pages.reset();
		//MSIE FIX
		if( page.isLoaded === true &&  appdb.pages.loadingStatus === 'home'){
			return;
		}
		appdb.pages.index.setCurrentContent("home",false);
		page.isLoaded = true;
		appdb.pages.loadingStatus = '';
		page.immediate();
	};
	
	page.loadBanners = function(usecache,callback){
		usecache = (typeof usecache === "boolean")?usecache:false;
		callback = (typeof callback === "function")?callback:function(){};
		if( page.currentBanner_recent() === null ){
			page.currentBanner_recent(new appdb.components.Banner({container: $("#banner_recent"),autoload:false}));
			page.currentBanner_recent().subscribe({event: "select", callback: function(){
				$(this.dom).parent().parent().removeClass("hidden");
			}}).subscribe({event: "more", callback: function(){
				$("#appsLastUpdatedLink").click();
			}});
		}
		if( page.currentBanner_newest() === null ){
			page.currentBanner_newest(new appdb.components.Banner({container: $("#banner_newest"),autoload:false, popup:'<div class="countrypopup banner">' +
					'<a data-click="#vappsLastUpdatedLink"><img src="/images/category34.png" border="0"><span>View vappliances</span></a>' +
					((appdb.config.features.swappliance)?'<a onClick="appdb.views.Main.showOrderedSWAppliances(\'lastupdated\');"><img src="/images/swapp.png" border="0"><span>View swappliances</span></a>':'') +
					'</div>'}));
			page.currentBanner_newest().subscribe({event: "select", callback: function(){
				$(this.dom).parent().parent().removeClass("hidden");
			}}).subscribe({event: "more", callback: function(){
				$("#appsLastUpdatedLink").click();
			}});
		}
		if( page.currentBanner_toprated() === null ){
			page.currentBanner_toprated(new appdb.components.Banner({container: $("#banner_toprated"),autoload:false, popup:'<div class="countrypopup banner">' +
					'<a data-click="#appsMostVisitedLink"><img src="/images/category1.png" border="0"><span>View software</span></a>' +
					'<a data-click="#vappsMostVisitedLink"><img src="/images/category34.png" border="0"><span>View vappliances</span></a>' +
					((appdb.config.features.swappliance)?'<a onClick="appdb.views.Main.showOrderedSWAppliances(\'hitcount\');"><img src="/images/swapp.png" border="0"><span>View swappliances</span></a>':'') +
					'</div>'}));
			page.currentBanner_toprated().subscribe({event: "select", callback: function(){
				$(this.dom).parent().parent().removeClass("hidden");
			}}).subscribe({event: "more", callback: function(){
				$("#appsMostVisitedLink").click();
			}});
		}
		var recent = page.currentBanner_recent(); 
		var newest = page.currentBanner_newest();
		var toprated = page.currentBanner_toprated();
		
		var getParams = function(q){
			var res = [];
			for(var i in q){
				if( q.hasOwnProperty(i) ){
					res.push({name:i, val: q[i]});
				}
			}
			return res;
		};

		var homebroker = new appdb.utils.broker(true);
		var buildBrokerRequest = function(requests, usecache){
			requests = requests || [];
			requests = $.isArray(requests)?requests:[requests];
			usecache = (typeof usecache === "boolean")?usecache:false;
			var reqs = {cached: {}, requests: requests};
			if( usecache === false ) return reqs;
			var res = [], i, arr = reqs.requests, len = reqs.requests.length;
			for( i = 0; i < len; i += 1 ) {
				var tmp = appdb.utils.localHomeCache.getItem(arr[i].id);
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
		};
		var adjustApplicationData = function(d,tofunction){
			tofunction = (typeof tofunction === "boolean")?tofunction:false;
			d = d || {};
			d.application = d.application || [];
			d.application = $.isArray(d.application)?d.application:[d.application];
			$.each(d.application, function(i, e){
				e.category = e.category || [];
				e.category = $.isArray(e.category)?e.category:[e.category];
				e.category = appdb.utils.transformValData(e.category,tofunction);
				e.discipline = e.discipline || [];
				e.discipline = $.isArray(e.discipline)?e.discipline:[e.discipline];
				e.discipline = appdb.utils.transformValData(e.discipline,tofunction);
			});
			return d;
		};
		var brokerResponseDispatch = function(id,data,requests){
			var d = adjustApplicationData(data, true);
			if ( id === "recent" ) {
				recent.render(d);
			} else if ( id === "newest" ) {
				newest.render(d);
			} else if ( id === "toprated" ) {
				toprated.render(d);
			}
			$.each(requests, function(i,e){
				if( e.id === id ){
					data = adjustApplicationData(d,false);
					appdb.utils.localHomeCache.setItem(id,data);
				}
			});
		};
		var brokerRequests = [{
			"id": "recent",
			"resource": "applications",
			"method": "GET",
			"param": getParams(recent.getDataQuery())
		},{
			"id": "newest",
			"resource": "applications",
			"method": "GET",
			"param": getParams(newest.getDataQuery())
		},{
			"id": "toprated",
			"resource": "applications",
			"method": "GET",
			"param": getParams(toprated.getDataQuery())
		}];
		var reqs = buildBrokerRequest(brokerRequests, usecache);
		//load cached
		for(var r in reqs.cached){
			if( reqs.cached.hasOwnProperty(r) === false ) continue;
			
			brokerResponseDispatch(r,reqs.cached[r],[]);
		}
		if( reqs.requests.length === 0 && usecache === true ){
			callback();
			return;
		}
		homebroker.request(reqs.requests);
		homebroker.fetch(function(e) {
			var i, len = e.reply.length;
			for (i=0; i<len; i++) {
				var data = e.reply[i], id = e.reply[i].id;
				brokerResponseDispatch(id,data.appdb,reqs.requests);
			}
			callback();
		});
	};
	page.immediateHome = function(){
		loadImage('feedback.png', 'img.feedback');
		loadImage('needaccess.png', 'img.needaccess');
		loadImage('register.png', 'img.register');
		loadImage('joinascontact.png', 'img.joinascontact');
		$('#announcediv').addClass("hidden");
		$.ajax({
			url: appdb.config.endpoint.base + '/help/wiki?page=main:about:announcements', 
			success : function(data, textStatus) { 
				$('#announcediv').empty().html("<div class='aboutpage'><div class='aboutheader'><h1>Announcements</h1></div><div class='announcementscontainer'><ul class='announcelist'></ul></div></div>");
				var jdata = $(data);
				$(jdata).find("h3[class^='sectionedit']:lt(3) , h3[class^='sectionedit']:lt(3)+div").each(function(i,e){
					var li = $("<li class='faq'></li>");
					if( $.trim( $(e)[0].tagName ).toLowerCase() === "h3" ){
						var header = $("<span class='header'></span>");
						var html = $.trim($(e).html());
						var spl = html.split(":");
						if( spl.length > 1 ){
							spl[1] = spl[1].replace(/([0-9]+\.[0-9]+\.[0-9]+)/g,"<span class='version'>$1</span>");
							$(header).html("<span class='date'>" + spl[0] + "</span>:" + spl[1]);
						}
						$(li).append(header);
						$('#announcediv .announcementscontainer .announcelist').append(li);
					}else if ( $.trim( $(e)[0].tagName ).toLowerCase() === "div" ){
						$(e).addClass("faqcontent");
						$(e).find("p:first").remove();
						$(e).prepend('<span class="top">Release highlights:</span>');
						$(e).find("p:last").remove();
						$(e).find("hr:last").remove();
						$('#announcediv .announcementscontainer .announcelist > li:last').append(e);
					}
				});
				$('#announcediv').append( $("<div class='more' ></div>").append("<a href='"+appdb.config.endpoint.wiki +"main:about:announcements' target='_blank'>...more</a>") );
				$('#announcediv').removeClass("hidden");
				hideAjaxLoading();
			}
		});

		page.loadBanners(true, function(){
			setTimeout(function(){
				page.loadBanners(false, function(){}, false);
			},1500);
		});
	};
	page.immediateCustom = function(){
		$(".homepage.custom .linkcontent a.profile").each(function(i, e){
			var tab = '';
			if( $(e).hasClass("pendingrequests") ){
				tab = "pending requests";
			}else if( $(e).hasClass("preferences") ){
				tab = "preferences";
			}
			$(e).unbind("click").bind("click", (function(t){
				return function(ev){
					ev.preventDefault();
					appdb.views.Main.showPerson({id: userID, cname: userCName}, {mainTitle: userFullname,tab:t});
					return false;
				};
			})(tab));
		});
		loadImage('profile.png', 'img.profile');
		loadImage('userpreferences.png', 'img.preferences');
		loadImage('pendingreqs.png', 'img.pendingrequests');
		loadImage('feedback.png', 'img.feedback');
		
		page.initUserUIData();
		page.initCustomTabs();
		page.initCustomLists();
		page.initPendingRequests();
	};
	page.initUserUIData = function(){
		$(".homepage.custom .userfirstname").text(userFullname.split(" ")[0]);
	};
	page.initPendingRequests = function(){
		return;//(new appdb.components.UserRequests({container: $(".homepage.custom .pendingrequests.panel")[0]})).load()
	};
	page.initCustomLists = function(){
		page.customLists(null);
		var lists = {
			"software": {},
			"vappliance": {},
			"swappliance": {},
			"vos":{}
		};
		var clist = [];
		var getParams = function(q){
			var res = [];
			for(var i in q){
				if( q.hasOwnProperty(i) ){
					res.push({name:i, val: q[i]});
				}
			}
			return res;
		};
		
		for(var i in lists){
			if( lists.hasOwnProperty(i) === false ) continue;
			$(".homepage.custom > .homeitem-container.tabcontainer.personal > .tabcontent." + i + " > ul.header > li").each((function(index){
				return function(){
					lists[index][$(this).data("tab")] = true;
				};
			})(i));
			
			for(var j in lists[i]){
				if( lists[i].hasOwnProperty(j) === false ) continue;
				$(".homepage.custom > .homeitem-container.tabcontainer.personal > .tabcontent." + i + " > .content." + j).each((function(index,name){
					return function(){
						var item = { id: ( "" + index + "_" + name ) };
						item.view = (new appdb.components.Banner({container: $(this).children(".listcontainer"),haspager:true,autosize:false,autoload:false, id:item.id, modelData: {"id":userID}}).subscribe({event: "select", callback: function(o){
								$(this.dom).parent().parent().removeClass("hidden");
								if( o && o.pager ){
									$(this.dom).closest(".tabcontainer." + this._tabcontainer).children(".header").find("." + this._tabname + " .count").html(o.pager.count);
									if( o.pager.count <= 0){
										$(this.dom).closest(".tabcontainer." + this._tabcontainer).children(".tabcontent." + this._tabname).addClass("nodata");
									}else{
										$(this.dom).closest(".tabcontainer." + this._tabcontainer).children(".tabcontent." + this._tabname).removeClass("nodata");
									}
								}else if( o && $.trim(o.count)==="0" ){
									$(this.dom).closest(".tabcontainer." + this._tabcontainer).children(".header").find("." + this._tabname + " .count").html("0");
									$(this.dom).closest(".tabcontainer." + this._tabcontainer).children(".tabcontent." + this._tabname).addClass("nodata");
								}
							}}).subscribe({event: "more", callback: function(){
							//todo: add event
							}}));
						var isasync = ( ( $.trim($(this).children(".listcontainer").data("async")).toLowerCase() === "false" )?false:true );
						
						item.async = isasync;
						item.view._tabcontainer = index,
						item.view._tabname = name;
						item.request = { 
							"id": item.id, 
							"method": "GET",
							"resource": "people/"+userID+"/" + ((i==="vos")?"vos":"applications") + "/" + name,
							"param": getParams(item.view.getDataQuery())
						};
						clist.push(item);
					};
				})(i,j));
			}
		}
		page.customLists( clist );
		
		//helper function creates request broker and sends requests according to async
		var fetchbroker = function(async, callback){
			async = (typeof async === "boolean")?async:true;
			var homebroker = new appdb.utils.broker(async);
			$.each(clist, function(i,e){
				if( e.async === async ){
					homebroker.request(e.request);
				}
			});
			homebroker.fetch(function(e){
				e.reply = e.reply || [];
				e.reply = $.isArray(e.reply)?e.reply:[e.reply];
				for (var k=0; k<e.reply.length; k+=1) {
					var item = $.grep(clist, (function(r){
						return function(i){
							return (i.id === r.id);
						};
					})(e.reply[k]));
					if( item.length === 0 ) return;
					item[0].view.load(void 0,e.reply[k].appdb);
				}
				if( async === true ){
					if( typeof callback === "function" ){
						setTimeout(function(){ callback(); }, 1);
					}
				}
				hideAjaxLoading();
			});
			if( async === false ){
				if( typeof callback === "function" ){
					setTimeout(function(){ callback(); }, 1);
				}
			}
		};
		page.loadBanners(true, function(){
			setTimeout(function(){
				page.loadBanners(false, function(){}, false);
			},1500);
		});
		//Sync load broker request maked with async=false
		fetchbroker(false, function(){
			//Async load rest of items
			fetchbroker(true);
		});
	};
	page.initCustomTabs = function(){
		$(".homepage.custom .tabcontainer > ul.header > li").unbind("click").bind("click", function(ev){
			ev.preventDefault();
			var tname = $(this).data("tab");
			$(this).closest("ul").children("li").removeClass("current");
			$(this).addClass("current");
			$(this).closest(".tabcontainer").children("div").removeClass("current");
			$(this).closest(".tabcontainer").children("div." + tname).addClass("current");
			return false;
		});
	};
	page.initCustom = function(){
		appdb.pages.reset();
		//MSIE FIX
		if( page.isLoaded === true &&  appdb.pages.loadingStatus === 'home'){
			return;
		}
		if (document.readyState === "complete") { 
			setTimeout(page.immediate,10);
		}else{
			$('document').ready(function(){
				page.immediate();
			});
		}
		page.isLoaded = true;
		appdb.pages.index.setCurrentContent("home",false);
		appdb.pages.loadingStatus = '';
	};
	page.immediate = function(){
		if( page.isPersonilized() ){
			return page.immediateCustom();
		}
		return page.immediateHome();
	};
	page.reset = function(){
		if( page.currentBanner_recent() !== null ){
			page.currentBanner_recent().unsubscribeAll();
			page.currentBanner_recent().destroy();
			page.currentBanner_recent(null);
		}
		if( page.currentBanner_toprated() !== null ){
			page.currentBanner_toprated().unsubscribeAll();
			page.currentBanner_toprated().destroy();
			page.currentBanner_toprated(null);
		}
		if( page.currentBanner_newest() !== null ){
			page.currentBanner_newest().unsubscribeAll();
			page.currentBanner_newest().destroy();
			page.currentBanner_newest(null);
		}
	};
	page.init = function(){
		page.reset();
		if( page.isPersonilized() ){
			return page.initCustom();
		}
		return page.initHome();
	};
	return page;
})();

appdb.pages.application = (function(){
	var page = {};
	
	page.currentId = ( function(){
		var _appid = 0;
		return function(appid){
			if( typeof appid !== "undefined" ) {
				_appid = appid;
			}
			return _appid;
		};
	})();
	
	page.currentDialogCount = ( function() {
		var _dialogCount = -1;
		return function(dcount){
			if( typeof dcount !== "undefined" ) {
				_dialogCount = dcount;
			}
			return _dialogCount;
		};
	})();
	
	page.currentPermalink = ( function(){
		var _permalink = '';
		return function(permalink){
			if( typeof permalink !== "undefined" ){
				_permalink = permalink;
			}
			return _permalink;
		};
	})();
	
	page.currentHistoryId= ( function(){
		var _historyId = '';
		return function(historyId){
			if( typeof historyId !== "undefined" ){
				_historyId = historyId;
			}
			return _historyId;
		};
	})();

	page.currentHistoryType = ( function(){
		var _historyType = '';
		return function(historyType){
			if( typeof historyType !== "undefined" ){
				_historyType = historyType;
			}
			return _historyType;
		};
	})();
	
	page.currentData = (function(){
		var _data = null;
		return function(data){
			if( typeof data !== "undefined" ){
				if( data === null ) {
					_data = null;
				} else if( $.isPlainObject(data) && (data.id || data.application.id)){
					_data = data;
					page.currentId(_data.id || _data.application.id);
					page.currentName(_data.name || _data.application.name);
					page.currentCName(_data.cname || _data.application.cname);
					if( $.inArray($.trim(page.currentId()) ,["0",""]) === -1 ){
						if( $.trim(data.metatype || data.application.metatype) === "1" ){
							page.currentEntityType("virtualappliance");
						}else if($.trim(data.metatype || data.application.metatype) === "2" ){
							page.currentEntityType("softwareappliance");
						}else{
							page.currentEntityType("software");
						}
					}
				}
			}
			return _data;
		};
	})();
	page.currentMetaType = new appdb.utils.SimpleProperty();
	page.currentRouteData = (function(){
		var _data = null;
		return function(data){
			if( typeof data !== "undefined" ){
				if( data === null ) {
					_data = null;
				} else if( typeof data !== "undefined"){
					_data = data;
				}
			}
			return _data;
		};
	})();
	page.currentName = (function(){
		var _name = null;
		return function(name){
			if( typeof name !== "undefined" && name !== null ){
				_name = $.trim(name);
			}
			if( !_name && page.currentData() && page.currentData().name){
					_name = page.currentData().name;
			}
			
			return _name;
		};
	})();
	page.currentCName = (function(){
		var _name = null;
		return function(name){
			if( typeof name !== "undefined" && name !== null ){
				_name = $.trim(name);
			}
			if( !_name && page.currentData() && page.currentData().cname){
					_name = page.currentData().cname;
			}
			
			return _name;
		};
	})();	
	page.currentSection = ( function(){
		var _section = '';
		return function(section){
			if( typeof section !== "undefined" ){
				_section = section;
			}
			return _section;
		};
	})();
	page.currentReleaseSection = ( function(){
		var _section = '';
		return function(section){
			if( typeof section !== "undefined" ){
				_section = section;
			}
			return _section;
		};
	})();
	page.currentPermissions = (function(){
		var _privs = null;
		return function(d){
			if( d === null ){
				_privs = null;
			}else if( _privs === null ){
				var data = page.currentData() || {};
				data = data.application || {};
				data = data.permissions || null;
				if( data !== null ){
					_privs = new appdb.utils.Privileges(data);
				}
			} 
			return _privs;
		};
		
	})();
	page.currentPermissionEditor = new appdb.utils.SimpleProperty();
	page.currentResourceProviderViewer = new appdb.utils.SimpleProperty();
	page.currentEntityType = new appdb.utils.SimpleProperty();
	page.currentVAManager = (function(){
		var _vam = null;
		return function(d){
			if( d === null ){
				if( _vam !== null ){
					_vam._mediator.clearAll();
				}
				_vam = null;
			}else if( _vam === null && appdb.vappliance){
				var data = page.currentData();
				var isPrivate = ( data && data.application && data.application.vappliance && $.trim(data.application.vappliance.imageListsPrivate )==="true");
				var perms = page.currentPermissions();
				var canAccessPrivateData = !isPrivate;
				if( perms ){
					canAccessPrivateData = perms.canManageVirtualAppliance() || perms.canAccessVirtualAppliance();
				}
				_vam = new appdb.vappliance.components.VirtualApplianceProvider({
					container: $( "#navdiv" + page.currentDialogCount() + " #vappliancediv" + page.currentDialogCount()),
					software: appdb.pages.application.currentData(),
					isPrivate: isPrivate,
					canAccessPrivateData: canAccessPrivateData,
					components: [
						{
							container: $("#latestvadiv" + page.currentDialogCount()),
							className: "appdb.vappliance.components.LatestVersion",
							name: "latestversion",
							canEdit: true,
							isPrivate: isPrivate,
							canAccessPrivateData: canAccessPrivateData
						},{
							container: $("#workingvadiv" + page.currentDialogCount()),
							className: "appdb.vappliance.components.WorkingVersion",
							name: "workingversion",
							canEdit: true,
							isPrivate: isPrivate,
							canAccessPrivateData: canAccessPrivateData
						},{
							container: $("#previousvadiv" + page.currentDialogCount()),
							className: "appdb.vappliance.components.PreviousVersions",
							name: "previousversions",
							canEdit: true,
							isPrivate: isPrivate,
							canAccessPrivateData: canAccessPrivateData
						}
					]
				});
			}
			appdb.vappliance.ui.CurrentVAManager = _vam;
			return _vam;
		};
		
	})();
	page.currentContextualizationManager = appdb.utils.SimpleProperty();
	page.currentSocialToolbox = new appdb.utils.SimpleProperty();
	page.currentSoftwareLicenses = (function(){
		var _swlicenses = null;
		return function(d){
			if( d === null ){
				if( _swlicenses !== null ){
					_swlicenses._mediator.clearAll();
					_swlicenses.reset();
				}
				_swlicenses = null;
			}else if( _swlicenses === null && page.isVirtualAppliance() === false ){
				_swlicenses = new appdb.views.LicenseList({
					container: $( "#navdiv" + page.currentDialogCount() + " div.app-licenses")
				});
			}
			return _swlicenses;
		};
		
	})();
	page.currentContactVOsViewer = new appdb.utils.SimpleProperty();
	page.currentRelations = function(){
		var rels = [];
		rels = ((page.currentData() || {}).application || {}).relation;
		rels = rels || [];
		rels = $.isArray(rels)?rels:[rels];
		return rels;
	};
	page.currentOrganizationRelationList = new appdb.utils.SimpleProperty();
	page.currentProjectRelationList = new appdb.utils.SimpleProperty();
	page.currentVapplianceRelationList = new appdb.utils.SimpleProperty();
	page.currentSoftwareRelationList = new appdb.utils.SimpleProperty();
	page.currentExternalRelationList = new appdb.utils.SimpleProperty();
	page.currentEntityRelationGrid = new appdb.utils.SimpleProperty();
	page.currentSuggestedList = new appdb.utils.SimpleProperty();
	page.initProjectRelationEditor = function(editmode){
		editmode = (typeof editmode === "boolean")?editmode:false;
		var dom = $("#navdiv" + dialogCount + " #appprojectrelationsdiv" + dialogCount );
		if(editmode === true ){
			editmode = false;
			var p = appdb.pages.application.currentPermissions();
			if( p && p.canEditRelatedProjects) {
				editmode = p.canEditRelatedProjects();
			}
		}
		if( page.currentProjectRelationList() ){
			page.currentProjectRelationList().reset();
			page.currentProjectRelationList(null);
		}
		var rels = page.currentRelations() || [];
		var projlisttype = appdb.views.RelationListSoftwareProject;
		if( appdb.pages.application.isVirtualAppliance() ){
			projlisttype = appdb.views.RelationListVApplianceProject;
		}else if( appdb.pages.application.isSoftwareAppliance() ){
			projlisttype = appdb.views.RelationListSWApplianceProject;
		}
		var rplist = new projlisttype({
			container: dom,
			parent: this,
			data: rels|| [],
			canedit: editmode,
			editmode: editmode
		});
		rplist.render();
		page.currentProjectRelationList(rplist);
	};
	page.initOrganizationRelationEditor = function(editmode){
		editmode = (typeof editmode === "boolean")?editmode:false;
		var dom = $("#navdiv" + dialogCount + " #apporganizationrelationsdiv" + dialogCount );
		if(editmode === true ){
			editmode = false;
			var p = appdb.pages.application.currentPermissions();
			if( p && p.canEditRelatedOrganizations) {
				editmode = p.canEditRelatedOrganizations();
			}
		}
		if( page.currentOrganizationRelationList() ){
			page.currentOrganizationRelationList().reset();
			page.currentOrganizationRelationList(null);
		}
		var rels = page.currentRelations() || [];
		var orglisttype = appdb.views.RelationListSoftwareOrganization;
		if( appdb.pages.application.isVirtualAppliance() ){
			orglisttype = appdb.views.RelationListVApplianceOrganization;
		}else if( appdb.pages.application.isSoftwareAppliance() ){
			orglisttype = appdb.views.RelationListSWApplianceOrganization;
		}
		var rolist = new orglisttype({
			container: dom,
			parent: this,
			data: rels|| [],
			canedit: true,
			editmode: editmode
		});
		rolist.render();
		page.currentOrganizationRelationList(rolist);
	};
	page.initSoftwareRelationEditor = function(editmode){
		editmode = (typeof editmode === "boolean")?editmode:false;
		var dom = $("#navdiv" + dialogCount + " #appswrelationsdiv" + dialogCount );
		if(editmode === true ){
			editmode = false;
			var p = appdb.pages.application.currentPermissions();
			if( p && p.canEditRelatedSoftware) {
				editmode = p.canEditRelatedSoftware();
			}
		}
		if( page.currentSoftwareRelationList() ){
			page.currentSoftwareRelationList().reset();
			page.currentSoftwareRelationList(null);
		}
		var rels = page.currentRelations() || [];
		var swlisttype = appdb.views.RelationListSoftwareSoftware;
		if( appdb.pages.application.isVirtualAppliance() ){
			swlisttype = appdb.views.RelationListVapplianceSoftware;
		}else if( appdb.pages.application.isSoftwareAppliance() ){
			swlisttype = appdb.views.RelationListSwapplianceSoftware;
		}
		var rplist = new swlisttype({
			container: dom,
			parent: this,
			data: rels|| [],
			canedit: editmode,
			editmode: editmode
		});
		rplist.render();
		page.currentSoftwareRelationList(rplist);
	};
	page.initVApplianceRelationEditor = function(editmode){
		var dom = $("#navdiv" + dialogCount + " #appvappliancerelationsdiv" + dialogCount );
		if( appdb.pages.application.isSoftwareAppliance() ){
			$(dom).addClass("hidden");
			return;
		}
		editmode = (typeof editmode === "boolean")?editmode:false;
		if(editmode === true ){
			editmode = false;
			var p = appdb.pages.application.currentPermissions();
			if( p && p.canEditRelatedVappliances) {
				editmode = p.canEditRelatedVappliances();
			}
		}
		if( page.currentVapplianceRelationList() ){
			page.currentVapplianceRelationList().reset();
			page.currentVapplianceRelationList(null);
		}
		var rels = page.currentRelations() || [];
		var vapplisttype = (appdb.pages.application.isVirtualAppliance())?appdb.views.RelationListVapplianceVappliance:appdb.views.RelationListSoftwareVappliance;
		var rplist = new vapplisttype({
			container: dom,
			parent: this,
			data: rels|| [],
			canedit: editmode,
			editmode: editmode
		});
		rplist.render();
		page.currentVapplianceRelationList(rplist);
	};
	page.initExternalRelationEditor = function(editmode){
		editmode = (typeof editmode === "boolean")?editmode:false;
		var dom = $("#navdiv" + dialogCount + " #appexternalrelationsdiv" + dialogCount );
		if(editmode === true ){
			editmode = false;
			var p = appdb.pages.application.currentPermissions();
			if( p && p.canChangeApplicationDescription) {
				editmode = p.canChangeApplicationDescription();
			}
		}
		if( page.currentExternalRelationList() ){
			page.currentExternalRelationList().reset();
			page.currentExternalRelationList(null);
		}
		var rels = page.currentRelations() || [];
		var vapplisttype = appdb.views.RelationExternalList;
		var subtype = "software";
		if( appdb.pages.application.isVirtualAppliance() ){
			subtype = "vappliance";
		}else if( appdb.pages.application.isSoftwareAppliance() ){
			subtype = "swappliance";
		}
		var rplist = new vapplisttype({
			container: dom,
			parent: this,
			data: rels|| [],
			subjecttype: subtype, 
			allowedtypes: ["software","vappliance","swappliance"],
			canedit: editmode,
			editmode: editmode
		});
		rplist.render();
		page.currentExternalRelationList(rplist);
		if( rplist.options.data && rplist.options.data.length === 0 ){
			$("#navdiv" + dialogCount + " .app-external-relations").addClass("hidden");
		}else{
			$("#navdiv" + dialogCount + " .app-external-relations").removeClass("hidden");
		}
	};
	page.initRelationGrids = function(){
		var dom = $("#navdiv" + dialogCount + " .relationgridcontainer.relationswvappcontainer > .relations" );
		
		if( page.currentEntityRelationGrid() ){
			page.currentEntityRelationGrid().reset();
			page.currentEntityRelationGrid(null);
		}
		var rels = page.currentRelations() || [];
		var rlist = new appdb.views.RelatedEntities({
			container: dom,
			parent: this,
			data: rels|| [],
			allowedtypes: ["software","vappliance","swappliance"],
			direction: "both",
			pagelength: 4
		});
		rlist.render(rels);
		page.currentEntityRelationGrid(rlist);
		$("#navdiv" + dialogCount + " .relationgridcontainer.relationswvappcontainer > .title .count").text("(" + rlist.getEntityCount() + ")" );
	};
	page.loadSuggestedList = function(){
		if( page.currentSuggestedList() ){
			page.currentSuggestedList().reset();
			page.currentSuggestedList(null);
		}
		var slist = new appdb.components.SuggestedItems({
			container: $("#navdiv" + dialogCount + " .suggesteditemscontainer" ),
			id: page.currentId()
		});
		slist.load();
	};
	page.currentContent = function(){
		var res = "software";
		if( page.currentEntityType() === "virtualappliance" ){
			res = "vappliance";
		}
		return res;
	};
	page.isVirtualAppliance = function(){
		return (page.currentEntityType() === "virtualappliance");
	};
	page.isSoftwareAppliance = function(){
		return (page.currentEntityType() === "softwareappliance");
	};
	page.isSoftware = function(){
		return (page.currentEntityType() === "software");
	};
	page.loadReleaseManager = function(callback){
		if( appdb.config.repository ){
			setTimeout(function(){
				$( "#navdiv" + page.currentDialogCount() + " #repositorydiv" + page.currentDialogCount()).empty().append('<div class="loadingreleases"><img src="/images/ajax-loader-small.gif" alt="" width="12px" height="12px" style="padding:0px;margin:0px;vertical-align: top"  /><span style="font-style: italic;font-size: 12px;vertical-align: super;padding:0px;margin:0px;">Loading...</span></div>');
				var xhr = $.ajax({
					url: "/repository/release/manager",
					action: "GET",
					data: {id: page.currentId(), name: page.currentName()},
					success: function(d){
						$( "#navdiv" + page.currentDialogCount() + " #repositorydiv" + page.currentDialogCount() ).empty();
						$( "#navdiv" + page.currentDialogCount() + " #repositorydiv" + page.currentDialogCount() ).append(d);
						if( callback ) callback();
					},
					error: function(st,err){
						$( "#navdiv" + page.currentDialogCount() + " #repositorydiv" + page.currentDialogCount() + " .loadingreleases").remove();
						$( "#navdiv" + page.currentDialogCount() + " #repositorydiv" + page.currentDialogCount() ).html("<div class='releases error'>" + st + "</div>");
						if( callback ) callback();
					}
				});
				page.requests.register({ getXhr: (function(x){
						return function(){ 
							return x;							
						};
				})(xhr)	},"releasemanager" + page.currentId());
			},1);
		}
	};
	page.loadVAManager = function(callback){
		if( !page.currentId() ) return;
		if(appdb.vappliance.ui.CurrentVAVersionSelectionRegister){
			appdb.vappliance.ui.CurrentVAVersionSelectionRegister.clear();
		}
		page.initSectionGroup(".vappliance", function(elem){
			var name = $(elem).data("name");
			if( name === "workingversion" ) return true;
			if( page.isVirtualAppliance() && appdb.vappliance.ui.CurrentVAManager.isEditMode() === true ){
				appdb.vappliance.ui.CurrentVAManager.checkUnsavedData(function(){
					$(elem).trigger("click");
				});
				return false;
			}
			if( appdb.vappliance.ui.CurrentVAVersionSelectionRegister ){
				appdb.vappliance.ui.CurrentVAVersionSelectionRegister.selectionChanged();
			}
			return true;
		});
		var vam = page.currentVAManager();
		vam.unsubscribeAll(page);
		vam.subscribe({event: "load", callback: function(v){
				this.renderVApplianceExternalControls();
		}, caller: page});
		vam.load({id: page.currentId(), software: page.currentData()});
		page.requests.register(vam._model,"vamanager" + page.currentId());
		setTimeout(function(){
			$("body").unbind("click.vamanager").bind("click.vamanager", function(ev){
				$("body").find(".vappliance .popupvalue > .header.selected").removeClass("selected");
				$("body").find(".vappliance .propertypopupvalue > .header.selected").removeClass("selected");
			});
		},1);
		return;
	};
	page.loadContextualizationPanel = function(){
		if( !page.currentId() ) return;
		var canedit = false;
		var p = appdb.pages.application.currentPermissions();
		if( p && p.canManageContextScripts()) {
			canedit = p.canManageContextScripts();
		}
		var cam = page.currentContextualizationManager();
		if( !cam ){
			cam = new appdb.contextualization.components.ContextualizationManager({
				container: $( "#navdiv" + page.currentDialogCount() + " #contextualizationdiv" + page.currentDialogCount()),
				application: appdb.pages.application.currentData(),
				canedit: canedit
			});
			page.currentContextualizationManager(cam);
		}
		var cam = page.currentContextualizationManager();
		cam.unsubscribeAll(page);
		cam.subscribe({event: "load", callback: function(v){
			this.renderContextualizationExternalControls(v);
				//todo: load extenral controls. Eg information tab "download" banner
		}, caller: page}).subscribe({ event: "change", callback: function(v){
			this.renderContextualizationExternalControls(v);
			this.renderResourceProviderViewer(false);
			this.reloadData((function(id,curdata){ 
				return function(v){
					if( v && v.application && v.application.id && $.trim(v.application.id) === id ){
						curdata.application.relation = v.application.relation;
						curdata.application.vo = v.application.vo;
						appdb.pages.application.initRelationGrids();
						populateAppVos($("#infodiv" + appdb.pages.application.currentDialogCount()), curdata.application);
					}
				};
			})(page.currentId(), page.currentData()));
		},caller: page});
		cam.load({id: page.currentId(), software: page.currentData()});
		page.requests.register(cam._model,"swmanager" + page.currentId());
		return;
	};
	page.renderVApplianceExternalControls = function(){
		var v = page.currentVAManager();
		var latest = v.getLatestPublishedVersion();
		latest = latest || null;
		page.renderVApplianceDownloadPanel(latest);
		page.renderContactVosLink("#navdiv" + page.currentDialogCount() + " .vappliancecontainer > .actions .action.contactvos > a");
		$("#navdiv" + page.currentDialogCount() + " .vappliancecontainer > .actions").removeClass("hidden");
	};
	page.renderContextualizationExternalControls = function(data){
		var d = $.extend(true, {}, (data || {}).context );
		d = d || {};
		d.contextscript = d.contextscript || [];
		d.contextscript = $.isArray(d.contextscript)?d.contextscript:[d.contextscript];
		page.renderContextualizationDownloadPanel(d);
	};
	page.getRelativeDate = function(data){
			var calcUTC = function(str){
				var d = str.split("T");
				var t = d[1];
				d = d[0];
				d = d.split("-");
				t = t.split(":");
				if( t.length === 0 ){
					t[0] = 0;
				}
				if( t.length === 1){
					t[1] = 0;
				}
				if( t.length === 2){
					t[2] = 0;
				}
				if( t[2].indexOf(".") > -1 ){
					t[2] = t[2].split(".")[0];
				}
				
				var utc = Date.UTC(parseInt(d[0]),parseInt(d[1])-1,parseInt(d[2]),parseInt(t[0]),parseInt(t[1]),parseInt(t[2]));
				return new Date(utc + (-180*60000));
			};
			if( $.trim(data) !== "" ){
				data = data.replace(" ","T");
				if( data.indexOf(".") > -1 ){
					data  = data.split(".")[0];
				}
				if($.trim(data[data.length-1]).toLowerCase() !== "z"){
					data += "Z";
				}
			}
			if( $.browser.msie ){
				data = data.replace(/-/g, '/');
				data = data.replace("T"," ");
			}
			var utc = new Date(data);

			var ts = countdown(utc, null);
			var text = "";
			if( ts.years > 0 ){
				text = ts.years + " year" + ((ts.year===1)?"":"s");
				if( ts.months !== 0 ){
					text += " and " + ts.months + " month" + ((ts.months===1)?"":"s");
				}
			}else if( ts.months > 0 ){
				text = ts.months + " month" + ((ts.months===1)?"":"s");
				if( ts.days > 0 ){
					text += " and " + ts.days + " day" + ((ts.days===1)?"":"s");
				}
			}else if( ts.days > 0 ){
				text = ts.days + " day" + ((ts.days===1)?"":"s");
				if( ts.hours > 0 ){
					text += " and " + ts.hours + " hour" + ((ts.hours===1)?"":"s");
				}
			}else if( ts.hours > 0 ){
				text = ts.hours + " hour" + ((ts.hours===1)?"":"s");
				if( ts.minutes > 0 ){
					text += " and " + ts.minutes + " minute" +  ((ts.minutes===1)?"":"s");
				}
			}else if( ts.minutes > 0 ){
				text = ts.minutes + " minute" + ((ts.minutes===1)?"":"s");
			}else{
				text = "less than a minute";
			}
			text += " ago";
			return text;
	};
	page.renderContextualizationDownloadPanel = function(data){
		if( data !== null && data.contextscript.length > 0){
			$( "#navdiv"+page.currentDialogCount() ).find(".contextualizationdownload").removeClass("hidden");
		}else{
			$( "#navdiv"+page.currentDialogCount() ).find(".contextualizationdownload").addClass("hidden");
			return;
		}
		data.contextscript.sort(function(a,b){
			var aa = $.trim(a.lastupdatedon).replace(/\-/ig, "").replace(/T/ig,"").replace(/\:/ig,"") << 0;
			var bb = $.trim(b.lastupdatedon).replace(/\-/ig, "").replace(/T/ig,"").replace(/\:/ig,"") << 0;
			if( aa < bb ) return 1;
			if( aa > bb ) return -1;
			return 0;
		});
		var latest = data.contextscript[0].lastupdatedon;
		var reldate = page.getRelativeDate(latest);
		$( "#navdiv"+page.currentDialogCount() ).find(".contextualizationdownload .published .value").html(reldate);
	};
	page.renderVApplianceDownloadPanel = function(data){
		if( data !== null && $.trim(data.enabled) === "true"){
			$( "#navdiv"+page.currentDialogCount() ).find(".vmidownloadbutton").removeClass("hidden");
		}else{
			$( "#navdiv"+page.currentDialogCount() ).find(".vmidownloadbutton").remove();
		}
		var panel = $( "#navdiv"+page.currentDialogCount()).find(".downloadarea.vmidownload");
		$(panel).removeClass("isprivate");
		if( data === false || data === null || !data || $.trim(data.enabled) === "false"){
			$(panel).addClass("hidden");
			return;
		}
		$(panel).removeClass("hidden");
		$(panel).find(".fieldvalue.version > .value").text(data.version);
		data.image = data.image || [];
		data.image = $.isArray(data.image)?data.image:[data.image];
		var hypers = [];
		$.each(data.image, function(i, e){
			e.instance = e.instance || [];
			e.instance = $.isArray(e.instance)?e.instance:[e.instance];
			
			$.each(e.instance, function(ii, ee ){
				if( !ee || !ee.hypervisor || ee.hypervisor.length === 0 ) return;
				var v = $.isArray(ee.hypervisor)?ee.hypervisor[0]:ee.hypervisor;
				if( !v.val ) return;
				hypers.push(v.val());
			});
		});
		hypers = $.grep(hypers, function(v, k){
			return $.inArray(v ,hypers) === k;
		});
		var hps = $("<ul class='hypervisorlist'></ul>");
		if( hypers.length > 3 ){
			var hpli = $("<li></li>");
			var extrali = $("<li class='extra'></li>");
			var moreli = $("<li class='toggler'><span class='more' title='View all available hypervisors'>..more</span><span class='less'>..less</span></li>");
			$(moreli).unbind("click").bind("click", function(ev){
				ev.preventDefault();
				$(this).parent().toggleClass("expand");
				return false;
			});
			$(hpli).text(hypers.slice(0,3).join(" \u2022 "));
			$(extrali).text(" \u2022 " + hypers.slice(3).join(" \u2022 "));
			$(hps).append(hpli).append(extrali).append(moreli);
			
		}else{
			var hpli = $("<li></li>");
			$(hpli).text(hypers.join(" \u2022 "));
			$(hps).append(hpli);
		}
		$(panel).find(".fieldvalue.hypervisors > .value").append( hps );
		$(panel).find(".fieldvalue.provider > .value").html("<a href='" + appdb.config.endpoint.vmcaster + "store/vappliance/" + appdb.pages.application.currentCName() + "/image.list' title='View image list' target='_blank'>image list</a>");
		
		var pval = $(panel).find(".download .published .value");
		if( $(pval).length > 0 && $.trim(data.createdon)!== "" ){
			$(panel).find(".download .published .value").html(page.getRelativeDate(data.createdon));
		}
		if( page.hasPrivacy() === true ){
			if( page.getPrivacy() ){
				$(panel).addClass("isprivate");
				$("#navdiv" + page.currentDialogCount()).find(".privacycounter").removeClass("hidden").html("<img alt='' src='/images/logout3.png'/>");
			}
		}
	};
	page.renderSWReleasesDownloadPanel = function(data){
		var appdata = page.currentData();
		var panel = $( "#navdiv"+page.currentDialogCount()).find(".downloadarea.swdownload");
		if( appdata && appdata.application && appdata.relcount && $.trim(appdata.relcount) !== "0"){
			$(panel).removeClass("hidden");
		}
		$("#navdiv" + page.currentDialogCount()).find(".releasecounter").text("(0)");
		if( data === false || data === null || !data){
			$(panel).addClass("hidden");
			return;
		}
		
		
		data.repositoryarea = data.repositoryarea || [];
		data.repositoryarea = $.isArray(data.repositoryarea)?data.repositoryarea:[data.repositoryarea];
		//Retrieve repositories with at least one release published to production
		var repocopy = JSON.parse(JSON.stringify(data.repositoryarea));
		var productiononly = [];
		var candidateonly = [];
		$.each(repocopy, function(i, e){
			e.productrelease = e.productrelease || [];
			e.productrelease = $.isArray(e.productrelease)?e.productrelease:[e.productrelease];
			//remove unpublished releases
			var candreleases = [];
			var prodreleases = [];
			for(var i=e.productrelease.length-1; i>=0; i-=1){
				if( e.productrelease[i].state && $.trim(e.productrelease[i].state.name).toLowerCase() !== "unverified" ) {
					if( $.trim(e.productrelease[i].state.name).toLowerCase() === "production" ){
						prodreleases.push(e.productrelease[i]);
					}else if($.trim(e.productrelease[i].state.name).toLowerCase() === "candidate"){
						candreleases.push(e.productrelease[i]);
					}
					continue;
				}
				e.productrelease.splice(i,1);
			}
			//sort remaining production releases by release date
			prodreleases.sort(function(a, b){
				var ap = parseInt($.trim( a.releasedate ).replace(/\-/g,"").replace(/\ /g,"").replace(/\:/g,"") || 0);
				var bp = parseInt($.trim( b.releasedate ).replace(/\-/g,"").replace(/\ /g,"").replace(/\:/g,"") || 0);

				return bp - ap;
			});
			//sort remaining candidate releases by release date
			candreleases.sort(function(a, b){
				var ap = parseInt($.trim( a.releasedate ).replace(/\-/g,"").replace(/\ /g,"").replace(/\:/g,"") || 0);
				var bp = parseInt($.trim( b.releasedate ).replace(/\-/g,"").replace(/\ /g,"").replace(/\:/g,"") || 0);

				return bp - ap;
			});
			//Store only most recently published release
			if( prodreleases.length > 0 ){
				e.productrelease = [prodreleases[0]];
				productiononly.push(e);
			}else if( candreleases.length > 0 ){
				e.productrelease = [candreleases[0]];
				candidateonly.push(e);
			}
			
		});
		//sort production series by last production build date
		var lastprodbuild = productiononly.sort(function(a, b){
			var ap = parseInt($.trim( (a.lastproductionbuild || {}).productiontime).replace(/\-/g,"").replace(/\ /g,"").replace(/\:/g,"") || 0);
			var bp = parseInt($.trim( (b.lastproductionbuild || {}).productiontime).replace(/\-/g,"").replace(/\ /g,"").replace(/\:/g,"") || 0);
			return bp - ap;
		});
		//sort candidate series by last production build date
		var lastcandbuild = candidateonly.sort(function(a, b){
			var ap = parseInt($.trim( (a.lastproductionbuild || {}).productiontime).replace(/\-/g,"").replace(/\ /g,"").replace(/\:/g,"") || 0);
			var bp = parseInt($.trim( (b.lastproductionbuild || {}).productiontime).replace(/\-/g,"").replace(/\ /g,"").replace(/\:/g,"") || 0);
			return bp - ap;
		});
		var releasecounter = 0;
		if( lastprodbuild.length > 0 ){
			//build html list
			var html = [];
			$.each(lastprodbuild, function(i, e){
				releasecounter += 1;
				if( i > 2 ) return;
				var text = e.name + "/" + e.productrelease[0].displayversion;
				var itemhtml = "<a href='' title='Click to view this release' data-release='"+text+"'>" + text + "</a>";
				html.push(itemhtml);
			});
			$(panel).find(".fieldvalue.latestreleases").removeClass("hidden");
			$(panel).find(".fieldvalue.latestreleases > .value").html(html.join("<span>, </span>"));
			$(panel).find(".fieldvalue.latestreleases > .value > a").unbind("click").bind("click", function(ev){
				ev.preventDefault();
				appdb.pages.application.showReleases($(this).data("release"));
				return false;
			});
			var latestprodbuild = lastprodbuild[0];
			$(panel).find(".download .published .value").html(page.getRelativeDate(latestprodbuild.lastproductionbuild.productiontime || latestprodbuild.lastupdate || latestprodbuild.productrelease[0].statechanged || latestprodbuild.created));
			$(panel).removeClass("hidden");
		}else if(lastcandbuild.length > 0 ){
			$(panel).find(".fieldvalue.latestreleases").addClass("hidden");
			var latestprodbuild = lastcandbuild[0];
			$(panel).find(".download .published .value").html(page.getRelativeDate(latestprodbuild.lastproductionbuild.productiontime || latestprodbuild.lastupdate || latestprodbuild.productrelease[0].statechanged));
			$(panel).removeClass("hidden");
		}else{
			$(panel).find(".fieldvalue.latestreleases").addClass("hidden");
			$(panel).addClass("hidden");
		}
		//Init sw releases counter
		if( releasecounter > 0 ){
			$("#navdiv" + page.currentDialogCount()).find(".releasecounter").text("("+releasecounter+")");
		}else{
			$("#navdiv" + page.currentDialogCount()).find(".releasecounter").text("(0)");
		}
	};
	page.renderVapplianceIdentifier = function(){
		var data  = ( page.currentData() || {} ).application || {};
		$(dom).find(".app-id").removeClass("hidden");
		if( !data.vappliance || !data.vappliance.identifier) return;
		var dom = $("#navdiv" + page.currentDialogCount() + " #infodiv" + page.currentDialogCount());
		$(dom).find(".app-id").addClass("hidden");
		$(dom).find(".vappliance").remove();
		var innerhtml = '<div class="vappliance"><div class="property fieldnamevalue small compact popupvalue" ><div class="header">';
		innerhtml += '<div class="popup"><div class="field"><span class="title">Identifiers</span><span class="arrow">▼</span></div>';
		innerhtml += '<div class="value"><div class="item"><span class="name">Item ID:</span><span>'+data.id+'</span></div><div class="item"><span class="name">vApp identifier:</span><span>'+data.vappliance.identifier+'</span></div></div>';
		innerhtml += '</div></div></div>';
		innerhtml = $(innerhtml);
		$(innerhtml).find(".property").unbind("click").bind("click",function(ev){
			ev.preventDefault();
			$(this).children(".header").addClass("selected");
			return false;
		});
		$(dom).find(".app-id").after(innerhtml);
	};
	page.reloadData = function(onSuccess, onError){
		onSuccess = (typeof onSuccess === "function")?onSuccess:function(){};
		onError = (typeof onError === "function")?onError:function(){};
		var _x = new appdb.model.Application();
		_x.subscribe({event: "select", callback: function(v){
			if( v && v.error ){
				onError(v);
			} else {
				onSuccess(v);
			}
		}, caller: page});
		_x.get({"id": page.currentId() });
	};
	page.canEditPermissions = function(){
		var perms = page.currentPermissions();
		var canEditPrivs = ( perms && perms !== null && perms.canGrantPrivilege && perms.canGrantPrivilege() && perms.canRevokePrivilege && perms.canRevokePrivilege() );
		return canEditPrivs;
	};
	page.checkPermissions = function(){
		if( page.canEditPermissions() || page.isContactPoint() ){
			$("#navdiv" + page.currentDialogCount() + " #permissionsdiv"+ page.currentDialogCount()).removeClass("hidden");
			$("#navdiv" + page.currentDialogCount() + " a[href='#permissionsdiv"+ page.currentDialogCount()+"']").removeClass("hidden");
			page.renderPermissions();
		}else{
			$("#navdiv" + page.currentDialogCount() + " #permissionsdiv"+ page.currentDialogCount()).addClass("hidden");
			$("#navdiv" + page.currentDialogCount() + " a[href='#permissionsdiv"+ page.currentDialogCount()+"']").addClass("hidden");
		}
		if( page.canEditPermissions() ){	
			$("#navdiv" + page.currentDialogCount() ).addClass("fullcontrol");
		}else{
			$("#navdiv" + page.currentDialogCount() ).removeClass("fullcontrol");
		}
	};
	page.getContactPoints = function(){
		var cdata = page.currentData();
		if( !cdata ) return [];
		var app = cdata.application;
		var res = [];
		app.contact = app.contact || [];
		app.contact = $.isArray(app.contact)?app.contact:[app.contact];
		var uniq = {};
		$.each(app.contact, function(i, e){
				uniq[e.id] = e.id;
		});
		if( app.owner && app.owner.id ){
			uniq[app.owner.id] = app.owner.id;
		}
		if( app.addedby && app.addedby.id ){
			uniq[app.addedby.id] = app.addedby.id;
		}
		for(var u in uniq){
			if( uniq.hasOwnProperty(u) === false) continue;
			res.push(uniq[u]);
		}
		return res;
	};
	page.isContactPoint = function(id){
		id = $.trim(id || userID);
		
		var cps = page.getContactPoints();
		var res = $.grep(cps, function(e){
			return ($.trim(e) === id);
		});
		return (res.length > 0)?true:false;
	};
	page.getActorGroups = function(){
		var groups = {
			contacts: page.getContactPoints()
		};
		
		return groups;
	};
	page.getOwner = function(){
		var cdata = page.currentData();
		if( !cdata ) return null;
		var app = cdata.application;
		if( app.owner && app.owner.id ){
			return app.owner;
		} else if( app.addedby && app.addedby.id ){
			return app.addedby;
		}
		return null;
	};
	page.hasPrivacy = function(){
		return page.isVirtualAppliance();
	};
	page.getPrivacy = function(){
		if( page.isVirtualAppliance() ){
			var d = page.currentData();
			var vapp = (d && d.application && d.application.vappliance)?d.application.vappliance:{};
			return ($.trim(vapp.imageListsPrivate) === "true")?true:false;
		}
		return false;
	};
	page.canEditPrivacy = function(){
		return page.canEditPermissions();
	};
	page.renderPermissions = function(){
		if( page.currentPermissionEditor() !== null ){
			page.currentPermissionEditor().unsubscribeAll();
			page.currentPermissionEditor().destroy();
			page.currentPermissionEditor(null);
		}
		if( page.isVirtualAppliance() ){
			page.currentPermissionEditor(new appdb.components.VAppliancePrivileges({
				container: $("#navdiv" + page.currentDialogCount() + " #permissionsdiv" + page.currentDialogCount() + " .userpermissions"),
				id: page.currentId(),
				entityType: "vappliance",
				owner: page.getOwner(),
				entityData: page.currentData(),
				hasPrivacy: page.hasPrivacy(),
				isPrivate: page.getPrivacy(),
				canEditPrivacy: page.canEditPrivacy()
			}));
		} else if ( page.isSoftwareAppliance() ){
			page.currentPermissionEditor(new appdb.components.VAppliancePrivileges({
				container: $("#navdiv" + page.currentDialogCount() + " #permissionsdiv" + page.currentDialogCount() + " .userpermissions"),
				id: page.currentId(),
				entityType: "swappliance",
				owner: page.getOwner(),
				entityData: page.currentData(),
				hasPrivacy: page.hasPrivacy(),
				isPrivate: page.getPrivacy(),
				canEditPrivacy: page.canEditPrivacy()
			}));
		} else {
			page.currentPermissionEditor(new appdb.components.SoftwarePrivileges({
				container: $("#navdiv" + page.currentDialogCount() + " #permissionsdiv" + page.currentDialogCount() + "  .userpermissions"),
				id: page.currentId(),
				entityType: "software",
				owner: page.getOwner(),
				entityData: page.currentData(),
				hasPrivacy: page.hasPrivacy(),
				isPrivate: page.getPrivacy(),
				canEditPrivacy: page.canEditPrivacy()
			}));
		}	
		page.currentPermissionEditor().load({id:page.currentId()});
		page.requests.register(page.currentPermissionEditor()._model, "permissions"+page.currentId());
		$("#navdiv" + page.currentDialogCount() + " #permissionsdiv" + page.currentDialogCount() + " .reloadpermissions").unbind("click").bind("click", function(ev){
			ev.preventDefault();
			$(this).addClass("hidden");
			appdb.pages.application.reloadPermissions();
			return false;
		});
	};
	page.renderResourceProviderViewer = function(autoselect){
		autoselect = (typeof autoselect === "boolean")?autoselect:true;
		if( page.currentResourceProviderViewer() !== null ){
			page.currentResourceProviderViewer().unsubscribeAll();
			page.currentResourceProviderViewer().destroy();
			page.currentResourceProviderViewer(null);
		}
		if( page.isVirtualAppliance() === false && page.isSoftwareAppliance() === false ) return;
		var appdata = page.currentData() || {};
		var vaprovidercount = ( (appdata.application || {}).vaprovidercount || "0" ) << 0;;
		var swprovidercount = ( (appdata.application || {}).swprovidercount || "0" ) << 0;;
		if( autoselect ){
			if( vaprovidercount > 0 || swprovidercount > 0 ){
				$("#navdiv" + page.currentDialogCount() + " a[href='#vausage']").trigger("click");
			}else{
				if( page.isSoftwareAppliance() ){
					$("#navdiv" + page.currentDialogCount()).find("a[href='#swlicenses']").trigger("click");
				}
			}
		}
		if( page.isSoftwareAppliance() ){
			page.currentResourceProviderViewer(new appdb.components.SwapplianceResourceProviders({
				container: $("#navdiv" + page.currentDialogCount() + " #vausage > .resourceproviders"),
				parent: page
			}));
		}else if(page.isVirtualAppliance()){
			page.currentResourceProviderViewer(new appdb.components.VapplianceResourceProviders({
				container: $("#navdiv" + page.currentDialogCount() + " #vausage > .resourceproviders"),
				parent: page
			}));
		}
		
		page.currentResourceProviderViewer().load();
	};
	page.initContactVOsViewer = function(){
		if( page.currentContactVOsViewer() ){
			page.currentContactVOsViewer().destroy();
			page.currentContactVOsViewer(null);
		}
		var perms = page.currentPermissions();
		if( false === (userID === null || page.isVirtualAppliance() === false || !perms || typeof perms.canManageVirtualAppliance !== "function" || perms.canManageVirtualAppliance() === false) ){
			var d = page.currentData();
			d = d || {};
			d.application = d.application || {};
			if( d.application.vappliance && $.trim(d.application.vappliance.id) !== ""){
				page.currentContactVOsViewer(new appdb.components.ContactVOs({
					parent: page,
					id: page.currentId()
				}));
			}
		}
	};
	page.renderContactVosLink = function(selector){
		selector = selector || "#navdiv" + page.currentDialogCount() + " #appdetailssidebar .contactvos > a";
		var cvolink = $(selector);
		if( page.currentContactVOsViewer() ){
			$(cvolink).removeClass("hidden");
			$(cvolink).unbind("click").bind("click", function(ev){
				if( $(this).hasClass("helperlink") ) return true;
				ev.preventDefault();
				page.currentContactVOsViewer().load();
				return false;
			});
		} else {
			$(cvolink).unbind("click").addClass("hidden");
		}
	};
	page.onPermissionsLoaded = function(){
		$("#navdiv" + page.currentDialogCount() + " #permissionsdiv" + page.currentDialogCount() + " .reloadpermissions").removeClass("hidden");
		appdb.utils.setupWikiLinks($("#navdiv" + page.currentDialogCount()));
	};
	page.reloadPermissions = function(){
		if( page.currentPermissionEditor() === null ){
			page.checkPermissions();
			return;
		}
		page.currentPermissionEditor().load({id:page.currentId()});
	};
	page.updateRatingCounter = function(count){
		count = $.trim(count) || "0";
		$("#navdiv" + page.currentDialogCount()).find(".commentscounter").text("(" + count + ")");
	};
	page.onApplicationLoad = function(){
		page.checkPermissions();
		var perms = page.currentPermissions();
		if( page.isVirtualAppliance() || page.isSoftwareAppliance() ){
			$("#navdiv" + page.currentDialogCount() + " #valatest").removeClass("hidden");
			$("#navdiv" + page.currentDialogCount() + " .app-group > ul > li:first").removeClass("hidden");
			appdb.pages.index.navigationList(appdb.pages.index.navigationListVApps());
			page.renderVapplianceIdentifier();
		}else{
			$("#navdiv" + page.currentDialogCount() + " #valatest").remove();
			$("#navdiv" + page.currentDialogCount() + " .app-group > ul > li:first").remove();
			appdb.pages.index.navigationList(appdb.pages.index.navigationListSW());
		}
		page.initSectionGroup();
		if ( !(page.currentId()<<0) )  {
			setTimeout(function(){
				$(".listhandlercontainer.editmode").show();
				$("#tagsRow" + page.currentDialogCount()).hide();
			},100);
		}else{
			$("#navdiv" + page.currentDialogCount()).removeClass("entitysoftware").removeClass("entityvirtualapplince");
			$("#navdiv" + page.currentDialogCount()).addClass("entity" + page.currentEntityType());
		}
				
		page.SetupNavigationPane();
		$(":input[name='name']:last").mouseup();
		$(".field-app-name span[edit_name='id']").html(page.currentId());
		
		//Initialize mange links in edit actions
		$("#navdiv" + page.currentDialogCount()).find(".app-managereleases").addClass("hidden");
		$("#navdiv" + page.currentDialogCount()).find(".app-vappliance").addClass("hidden");
		$("#navdiv" + page.currentDialogCount()).find(".app-contextualization").addClass("hidden");
		
		//Initialize counters
		$("#navdiv" + page.currentDialogCount()).find(".publicationcounter").text("(0)");
		$("#navdiv" + page.currentDialogCount()).find(".releasecounter").text("(-)");
		$("#navdiv" + page.currentDialogCount()).find(".commentscounter").text("(-)");
		
		var appdata = (appdb.pages.application.currentData())?appdb.pages.application.currentData().application:null;
		if( appdata && (appdata.id<<0)>0 ){
			//Load entity special managers
			if(page.isVirtualAppliance()){
				page.loadVAManager();
			}else if( page.isSoftware()){
				page.loadReleaseManager();
			}else if( page.isSoftwareAppliance()){
				page.loadContextualizationPanel();
			}
			
			//Setup manage edit actions
			if( userID && perms ){
				if( page.isSoftware() && perms.canManageApplicationReleases() ){
					$("#navdiv" + page.currentDialogCount()).find(".app-managereleases").removeClass("hidden");
				}
				if( page.isVirtualAppliance() && perms.canManageVirtualAppliance() ){
					$("#navdiv" + page.currentDialogCount()).find(".app-vappliance").removeClass("hidden");
				}
				if( page.isSoftwareAppliance() && perms.canManageContextScripts() ){
					$("#navdiv" + page.currentDialogCount()).find(".app-contextualization").removeClass("hidden");
				}
			}
			//Microformats
			var permlinkdom = $("#infodiv" + page.currentDialogCount() + " .app-permalink:last .permalink");
			$(permlinkdom).append('<meta itemprop="url" content="' + $(permlinkdom).attr("href") + '" >');
			if( appdata.addedOn ){
				var addedondom = $("#infodiv" + page.currentDialogCount() + " .app-addedon");
				$(addedondom).append('<meta itemprop="dateCreated" content="' + $.datepicker.formatDate('yy-mm-dd',new Date(appdata.addedOn)) + '" >');
			}
			if( appdata.lastUpdated ){
				var lastupdated = $("#infodiv" + page.currentDialogCount() + " .app-lastupdated");
				$(lastupdated).append('<meta itemprop="dateModified" content="' + $.datepicker.formatDate('yy-mm-dd',new Date(appdata.lastUpdated)) + '" >');
			}
			
			//Init publication counter
			appdata.publication = appdata.publication || [];
			appdata.publication = $.isArray(appdata.publication)?appdata.publication:[appdata.publication];
			$("#navdiv" + page.currentDialogCount()).find(".publicationcounter").text("(" + appdata.publication.length + ")");

			//Init comments and rating count
			appdata.ratingCount = $.trim(appdata.ratingCount) || "0";
			appdata.ratingCount = parseInt(appdata.ratingCount);
			if( appdata.ratingCount === 0 ){
				page.updateRatingCounter(0);
			}

			//Init sw release counter
			if( $.trim(appdata.relcount) === "0" ){
				$("#navdiv" + page.currentDialogCount()).find(".releasecounter").text("(0)");
			}
			if( appdb.config.views.application.social === true ){
				if( !page.currentSocialToolbox() ){
					page.currentSocialToolbox(new appdb.social.share.ListToolbox({
						container: $("#.socialtoolbar.listtoolbox"),
						entityType: (page.isVirtualAppliance())?"vappliance":"software",
						data: page.currentData()
					}));
				}
				page.currentSocialToolbox().render();
			}
			
			//Init and render sub views
			page.initContactVOsViewer();
			page.renderContactVosLink();
			page.renderResourceProviderViewer();
			page.initProjectRelationEditor();
			page.initOrganizationRelationEditor();
			page.initRelationGrids();
			page.loadSuggestedList();
			
		}
		//Remove invisibility of view
		setTimeout(function(){
			$("#bookmarkapp" + page.currentDialogCount()+ " > input[name=id]:last").val(page.currentId());
			$("#editapp" + page.currentDialogCount()).attr("callback_data","{appID: '"+page.currentId()+"'}");
			$("span.app-id:last").text("(id:" + page.currentId() + ")");
			page.selectSection();
			$("#navdiv" + page.currentDialogCount()).removeClass("invisible");
		},10);
		
	};
	page.getPrimaryCategory = function(){
		if( !page.currentData() ) {
			return null;
		}
		var app = page.currentData().application.category;
		if( !app ){
			return null;
		}
		app = $.isArray(app)?app:[app];
		var len = app.length, i;
		for(i=0; i<len; i+=1){
			if($.trim(app[i].primary) === "true"){
				return app[i];
			}
		}
		return null;
		
	};
	page.SetupNavigationPane = function(){
		var n, d = page.currentData();
		if( d !== null ){
			var cnf = appdb.utils.entity.getConfig(page.currentEntityType());
			if( cnf && cnf.navigationPanel() ){
				n = new (cnf.navigationPanel())({
					id: page.currentId() || 0,
					name: page.currentName() || '',
					category: page.getPrimaryCategory() || {},
					entityType: page.currentEntityType()
				});
			}
			appdb.views.Main.clearNavigation();
			appdb.views.Main.createNavigationList(n);
			var r = page.getPrimaryCategory();
			r = (r)?r.val():"Software";
			switch(page.currentEntityType()){
				case "softwareappliance":
					appdb.Navigator.setTitle(appdb.Navigator.Registry["SoftwareAppliance"],[{name:page.currentName()}]);
					break;
					break;
				case "virtualappliance":
					appdb.Navigator.setTitle(appdb.Navigator.Registry["VirtualAppliance"],[{name:page.currentName()}]);
					break;
				case "software":
				default:
					appdb.Navigator.setTitle(appdb.Navigator.Registry["Application"],[{name:page.currentName()}]);
					break;
			}
			appdb.views.Main.selectAccordion("applicationspane",r);
			if(appdb.config.routing.useCanonical){
				var perm = appdb.utils.getItemCanonicalUrl(page.currentEntityType(), {id: page.currentId(), name: page.currentName(), cname: page.currentCName()});
				$("#infodiv" + page.currentDialogCount() + " .app-permalink:last .permalink").attr("href","http://" + window.location.host + perm);
			}
		}
	};
	page.selectVapplianceSection = function(section){
		if( page.isVirtualAppliance() && page.currentVAManager() ){
			$(page.currentVAManager().dom).find(".vappliance.groupcontainer > ul > li > a[data-name="+section+"]").trigger("click");
		}		
	};
	page.onEventReady = function(extdata){
		appdb.config.permalink = page.currentPermalink();
		window.apptabselect = 0;
		navpaneclicks($("#applicationspane")[0]);
		$("#addcategory"+dialogCount).hide();
		$("#addmws"+dialogCount).hide();
		$("#adddomains"+dialogCount).hide();
		$("#addsubdomains"+dialogCount).hide();
		$("#addvos"+dialogCount).hide();
		$("#addSciConDiv"+dialogCount).hide();
		$("#addcountry"+dialogCount).hide();
		$("#addproglangs"+dialogCount).hide();
		$("#editdoc"+dialogCount).hide();
		$("#docdiv"+dialogCount).show();
		
		shortcut.add("esc", closeDialog);
		if ( detailsStyle == 0 ) {
            $("#abstractdiv"+dialogCount).css("max-height","");
			$("#mainscicondiv"+dialogCount).css("max-height","");
		}
		$("#toolbarContainer").empty();
		if ( userID !== null ) {
			$(".appviewtoolbar").appendTo($("#toolbarContainer")); 
			$(".appviewtoolbar").show(); 
		} else $(".appviewtoolbar").empty().remove();
		$("#navdiv"+dialogCount).parent().height($("#details").height());
		var undef;
		if( page.currentHistoryId() ){
			if( page.currentHistoryType() ){
				populateAppDetails( $("#infodiv"+page.currentDialogCount()), page.currentId(), ''+ page.currentHistoryId(), ''+page.currentHistoryType(), extdata );
			}else{
				populateAppDetails( $("#infodiv"+page.currentDialogCount()), page.currentId(), ''+ page.currentHistoryId(), undef, extdata );
			}
		} else {
			populateAppDetails( $("#infodiv"+page.currentDialogCount()), page.currentId(),undef,undef,extdata );
		}
	};
	page.userIsContactPoint = function(user){
		user = user || { id: userID };
		if( !isNaN(parseFloat(user)) && isFinite(user) ){
			user = {id: user };
		}else if(typeof user.id === "undefined" ){
			user = { id: userID };
		}
		var d = page.currentData();
		var contacts = [];
		if( d && d.application && d.application.contact ){
			contacts = d.application.contact;
			contacts = $.isArray(contacts)?contacts:[contacts];
		}
		var res = $.grep(contacts, (function(u){
			return function(e){
				return u == e.id;
			};
		})(user.id));
		return (res.length > 0 )?true:false;
	};
	page.selectSection = function(section,update){
		var tab_index = 0;
		if( !section ){
			var r = appdb.routing.FindRoute();
			if( r && r.parameters && r.parameters.section ){
				section = r.parameters.section;
			} else {
				return;
			}
		}
		switch(section.toLowerCase()){
			case "publications":
				tab_index = 1;
				break;
			case "releases":
				tab_index = 2;
				if( appdb.repository.ui.CurrentReleaseManager ){
					appdb.repository.ui.CurrentReleaseManager.dispatchRoute();
				}
				break;
			case "virtualappliance":
			case "vaversion":
				tab_index = 3;
				appdb.vappliance.ui.CurrentVAManager.dispatchRoute();
				break;
			case "versions":
				tab_index = 4;
				break;
			case "comments":
				tab_index = 5;
				break;
			case "permissions":
				tab_index = 6;
				break;
			default:
				tab_index = 0;
				break;
		}
		window.apptabselect = tab_index;
		setTimeout(function(){
			$( "#navdiv" + appdb.pages.application.currentDialogCount() ).tabs("select",tab_index);
		},10);
		
		if( update === true ){
			page.currentSection("information");
			page.updateSection(section.toLowerCase(),true);
		}
		$( "#navdiv" + appdb.pages.application.currentDialogCount() ).tabs("select",tab_index);
	};
	page.updateSection = function(section,internal){
		if( appdb.config.routing.useCanonical == false || !page.currentCName()) return;
		if( typeof section === "undefined" ) section = 0;
		if( typeof section === "number" ){
			switch( section ){
				case 1:
					section = "publications";
					break;
				case 2:
					section = "releases";
					break;
				case 3:
					section = "virtualappliance";
					break;
				case 4:
					section = "versions";
					break;
				case 5:
					section = "comments";
					break;
				case 6:
					section = "permissions";
					break;
				case 0:
					section = "information";
					break;
				default:
					section = "";
					break;
			}
		}
		if( $.trim(page.currentSection()) === $.trim(section) ) return;
		page.currentSection(section);
		if( appdb.config.routing.useCanonical == true && page.currentCName()){
			var sname = page.currentCName(), curl = "/store/software/" + sname, releasesection = page.getReleaseSubSection(), vasection = page.getVASubsection();
			if( page.isVirtualAppliance() ){
				curl = "/store/vappliance/" + sname;
			}else if( page.isSoftwareAppliance() ){
				curl = "/store/swappliance/" + sname;	
			}
			if( $.trim(sname) !== "" ){
				switch(section){
					case "information":
						curl += "";
						break;
					case "publications":
						curl += "/publications";
						break;
					case "comments":
						curl += "/comments";
						break;
					case "permissions":
						curl += "/permissions";
						break;
					case "releases":
						curl += "/releases" + releasesection;
						break;
					case "vaversion":
					case "virtualappliance":
						curl += "/vaversion" + vasection;
						break;
					case "versions":
						curl += "/versions";
						break;
					default:
						curl += "/information";
						break;
				}
				if( curl ) {
					curl = curl.toLowerCase();
					appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data,"Software " + appdb.pages.application.currentName() + " " + section,curl);
					appdb.Navigator.setInternalMode(false);
				}
			}
		}
		
	};
	page.showReleases = function(releaseurl){
		var sname = page.currentCName(), curl = "/store/software/" + sname + "/releases";
		appdb.Navigator.currentHistoryState.data = appdb.Navigator.currentHistoryState.data || {};
		if( $.trim(releaseurl) !== ""){
			releaseurl = $.trim(releaseurl).toLowerCase();
			if( releaseurl[0] !== "/" ){
				releaseurl = "/" + releaseurl;
			}
			curl += releaseurl;
		}
		curl = curl.toLowerCase();
		page.currentSection("releases");
		appdb.Navigator.currentHistoryState.data.releases = {
			release: "",
			releasesection: "",
			series: ""
		};
		appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data,"Software " + appdb.pages.application.currentName() + " releases",curl);
		appdb.Navigator.setInternalMode(false);
		page.selectSection("releases");
		appdb.repository.ui.CurrentReleaseManager.dispatchRoute();
	};
	page.showLatestVAppliance = function(){
		var sname = page.currentCName(), curl = "/store/vappliance/" + sname + "/vaversion/latest";
		appdb.Navigator.currentHistoryState.data = appdb.Navigator.currentHistoryState.data || {};
		curl = curl.toLowerCase();
		page.currentSection("virtualappliance");
		appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data,"Software " + appdb.pages.application.currentName() + " virtual appliance",curl);
		appdb.Navigator.setInternalMode(false);
		page.selectSection("virtualappliance");
	};
	page.showContextualizationScripts = function(){
		var sname = page.currentCName(), curl = "/store/swappliance/" + sname + "/versions";
		appdb.Navigator.currentHistoryState.data = appdb.Navigator.currentHistoryState.data || {};
		curl = curl.toLowerCase();
		page.currentSection("versions");
		appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data,"" + appdb.pages.application.currentName() + " software appliance",curl);
		appdb.Navigator.setInternalMode(false);
		page.selectSection("versions");
	};
	page.getReleaseSubSection = function(){
		var rm = appdb.repository.ui.CurrentReleaseManager;
		var res = "";
		if( rm ){
			res = rm.generateCurrentRoute();
		}
		return res;
	};
	page.getVASubsection = function(){
		var vam = appdb.vappliance.ui.CurrentVAManager;
		var res = "";
		if( vam ){
			res = "" + vam.getSubSection();
		}
		if( $.trim(res) === "/" || $.trim(res) === "") {
			res = "";
		}else{
			res = "/" + res;
		}
		return res;
	};
	page.updateReleaseSection = function(series, release, subsection, internal){
		if( appdb.config.routing.useCanonical == false || !page.currentCName()) return;
		subsection = subsection || "";
		var section = "/" + ((series)?series + "/":"") + ((release)?release + "/":"") + ((subsection)?subsection+"/":"");
		var title = "Software " + appdb.pages.application.currentName() + ((series)?"::" + series:"") + ((release)?"::"+ release:"") + ((subsection)?" " + subsection:"");
		if( page.currentReleaseSection() == section ) return;
		page.currentReleaseSection(section);
		if( appdb.config.routing.useCanonical == true && page.currentCName()){
			var sname = page.currentCName(), curl = "/store/software/" + sname + "/releases" + section;
			if( curl ) {
				curl = curl.toLowerCase();
				appdb.Navigator.setInternalMode(internal);
				appdb.Navigator.currentHistoryState.data = appdb.Navigator.currentHistoryState.data || {};
				appdb.Navigator.currentHistoryState.data.releases = {
					series: series, 
					release: release,
					releasesection: subsection
				};
				appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data,title,curl);
				appdb.Navigator.setInternalMode(false);
			}
		}
	};
	page.initSectionGroup = function(container,onclick){
		container = container || ".app-group";
		if( $("#navdiv" + page.currentDialogCount() + " " + container + " > ul > li").length < 1 ){
			$("#navdiv" + page.currentDialogCount() + " " + container).removeClass("groupcontainer");
			$("#navdiv" + page.currentDialogCount() + " " + container + " > div").removeClass("tabcontent").removeClass("hiddengroup");
			$("#navdiv" + page.currentDialogCount() + " " + container + " > ul").addClass("hidden");
		}else{
			$("#navdiv" + page.currentDialogCount() + " " + container).addClass("groupcontainer");
			$("#navdiv" + page.currentDialogCount() + " " + container + " > div").addClass("hiddengroup").addClass("tabcontent");
			if( $("#navdiv" + page.currentDialogCount() + " " + container + " > ul > li.current > a").length > 0 ){
				var sel = $("#navdiv" + page.currentDialogCount() + " " + container + " > ul > li.current > a").attr("href");
				$("#navdiv" + page.currentDialogCount() + " " + container + " > " + sel).removeClass("hiddengroup");
			}else{
				$("#navdiv" + page.currentDialogCount() + " " + container + " > ul > li:first").addClass("current");
				$("#navdiv" + page.currentDialogCount() + " " + container + " > div:first").removeClass("hiddengroup");
			}
			$( "#navdiv" + page.currentDialogCount() + " " + container + " > ul > li > a").unbind("click").bind("click", function(ev){
				ev.preventDefault();
				if( typeof onclick === "function" ){
					if( onclick(this) === false ) {
						if( typeof (ev.stopImmediatePropagation) === "function" ){
							ev.stopImmediatePropagation();
						}
						return false;
					}
				}
				$("#navdiv" + page.currentDialogCount() + " " + container + " > ul > li").removeClass("current");
				$("#navdiv" + page.currentDialogCount() + " " + container + " > div").addClass("hiddengroup");
				var href = $(this).attr("href");
				$(this).parent().addClass("current");
				$(href).removeClass("hiddengroup");
				
				return false;
			});
		}
	};
	page.immediate = function(){
		docgrid = false;
		window.apptabselect=0;
		
		$( "#navdiv" + page.currentDialogCount() ).tabs();
		$( "#navdiv" + page.currentDialogCount() + " > ul > li > a" ).click(function(e){e.preventDefault();return false;});
		$( "#navdiv" + page.currentDialogCount() ).bind( "tabsselect", function(event, ui) {
			if( page.isVirtualAppliance() && appdb.vappliance.ui.CurrentVAManager.isEditMode() === true && $.trim(window.apptabselect) !== $.trim(ui.index)){
				appdb.vappliance.ui.CurrentVAManager.checkUnsavedData((function(p, e, u){
					return function(){
						$( "#navdiv" + p.currentDialogCount() ).tabs("select",u.index);
					};
				})(page, event, ui));
				return false;
			}
			page.eventTabSelect(event,ui);
			return true;
		});
		$( "#navdiv" + page.currentDialogCount() + " > ul > li > a" ).click(function(e){e.preventDefault();return false;});
		$( "#navdiv" + page.currentDialogCount()).find(".downloadarea").addClass("hidden");
		$( ".detailsdlgcontent div:first" ).css("height","100%");
		setTimeout(function(){
			if( !userID ) {
				return;
			}
			(new appdb.utils.ExpandButton({dom: $("#toolbarContainer"), display: "actions"}));
			if( appdb.config.repository ){
				if( window.apptabselect_panelid === ("repositorydiv" + page.currentDialogCount()) || 
					window.apptabselect_panelid === ("vappliancediv" + page.currentDialogCount()) ||
					window.apptabselect_panelid === ("contextualizationdiv" + page.currentDialogCount())
					){
					$( "#navdiv" + page.currentDialogCount() + " .expandContainer" ).addClass("hidden");
				} else {
					$( "#navdiv" + page.currentDialogCount() + " .expandContainer" ).removeClass("hidden");
				}
			}
		},100);
		
		$("#navdiv" + page.currentDialogCount()).find(".privacycounter").addClass("hidden");
	};
	page.eventTabSelect= function(event, ui){
		if($.trim(ui.index)==='1'){
			setTimeout(function(){
				if( dijit.byId("docgrid" + page.currentDialogCount()) ){
					$(dijit.byId("docgrid" + page.currentDialogCount()).domNode).css({"height":"100%","width":"100%"});
					dijit.byId("docgrid" + page.currentDialogCount()).resize();
				}
			},1);
		}
		window.apptabselect_panelid = ui.panel.id;
		if( typeof window.apptabselect === "undefined" || $.trim(window.apptabselect) !== $.trim(ui.index) ){
			window.apptabselect = ui.index;
			page.updateSection(ui.index);
			if($("#detail").hasClass("editmode") === true ){
				window.apptabselect = (window.apptabselect>1)?0:window.apptabselect;
			}
		}

		if( ui.panel.id === ("repositorydiv" + page.currentDialogCount()) ||  
			ui.panel.id === ("ratingdiv" + page.currentDialogCount()) || 
			ui.panel.id === ("vappliancediv" + page.currentDialogCount()) ||
			ui.panel.id === ("permissionsdiv" + page.currentDialogCount()) ||
			ui.panel.id === ("contextualizationdiv" + page.currentDialogCount())
		){
			$( "#navdiv" + page.currentDialogCount() + " .expandContainer" ).addClass("hidden");
		} else {
			$( "#navdiv" + page.currentDialogCount() + " .expandContainer" ).removeClass("hidden");
		}

		if( $.trim(window.apptabselect) === '2' ){
			if(  page.isVirtualAppliance() ){
				appdb.pages.application.currentSection("virtualappliance");	
			}else{
				appdb.pages.application.currentSection("releases");
			}
		}
		if( appdb.vappliance.ui.CurrentVAVersionSelectionRegister ){
			appdb.vappliance.ui.CurrentVAVersionSelectionRegister.selectionChanged();
		}
	};
	page.onAppEdit = function(){
		$( "#details" ).removeClass("viewmode").addClass("editmode");
		if(page.currentId()<=0){
			$( "#details" ).addClass("register");
		}
		var tab_select = (window.apptabselect > 1 )?0:window.apptabselect;
		$( "#navdiv"+page.currentDialogCount()).tabs("remove", 2);
		if( page.currentId() == '0'){
			tab_select = 0;
			//remove publication,software release/virtual appliance/contextualization, comment, permission tabs
			$( "#navdiv"+page.currentDialogCount()).tabs("remove", 1);
			$( "#navdiv"+page.currentDialogCount()).tabs("remove", 1);
			$( "#navdiv"+page.currentDialogCount()).tabs("remove", 1);
			$( "#navdiv"+page.currentDialogCount()).tabs("remove", 1);
		}else {
			//remove software release/virtual appliance/contextualization, comment, permission tabs
			$( "#navdiv"+page.currentDialogCount()).tabs("remove", 2);
			$( "#navdiv"+page.currentDialogCount()).tabs("remove", 2);
			$( "#navdiv"+page.currentDialogCount()).tabs("remove", 2);
			$( "#navdiv"+page.currentDialogCount()).tabs("remove", 2);
			$( "#navdiv"+page.currentDialogCount()).tabs("remove", 2);
		}
		$( "#navdiv"+page.currentDialogCount()).tabs("select",tab_select);
		
		setTimeout(function(){
			if( appdb.pages.Application.currentId() > 0 ){
				var footbar = $(".software.bottomtoolbar");
				var save = $("#savedetails").clone(true);
				var cancel = $("#cancelsavedetails").clone(true);
				$(footbar).append(save).append(cancel);
				$( "#infodiv"+page.currentDialogCount()).append(footbar);
				if( appdb.pages.application.currentSoftwareLicenses() !== null ){
					appdb.pages.application.currentSoftwareLicenses().edit();
				}
			}
		},1);
		page.initProjectRelationEditor(true);
		page.initOrganizationRelationEditor(true);
		page.initSoftwareRelationEditor(true);
		page.initVApplianceRelationEditor(true);
		page.initExternalRelationEditor(true);
	};
	page.cancelCallback = function(){
		if( page.isVirtualAppliance() || page.isSoftwareAppliance() ){
			appdb.views.Main.showCloudMarketplace();
		}else {
			appdb.views.Main.showSoftwareMarketplace();
		}
		return false;
	};
	page.reload = function(){
		page.requests.reset();
		appdb.routing.Dispatch( (($.browser.msie)?window.location.hash:window.location.pathname), true);
		return false;
	};
	page.requests = new appdb.utils.RequestPool("application");
	page.reset = function(hard){
		hard = (typeof hard === "boolean")?hard:false;
		//Setup data to defaults
		page.requests.reset();
		page.currentId(-1);
		page.currentDialogCount(-1);
		page.currentData(null);
		page.currentPermalink(null);
		page.currentHistoryId(null);
		page.currentHistoryType(null);
		page.currentName("");
		page.currentCName("");
		page.currentPermissions(null);	
		page.currentSoftwareLicenses(null);
		if( page.currentContactVOsViewer() ){
			page.currentContactVOsViewer().destroy();
			page.currentContactVOsViewer(null);
		}
		if( hard === true ){
			$("body #details").empty();
		}
	};
	page.init = function(o){
		var _perms = page.currentPermissions();
		var currentid = page.currentId();
		appdb.pages.reset();
		var data = (o || {}).data;
		//Setup data
		if( typeof o.id === "undefined" || o.id === null ){
			o.id = null;
		}
		page.currentId(o.id || ((data || {}).application || {}).id || 0);
		page.currentDialogCount(o.dialogCount || 0);
		page.currentPermalink(o.permalink || "");
		page.currentHistoryId(o.historyId || null);
		page.currentHistoryType(o.historyType || null);
		page.currentEntityType(o.entityType || "software");
		page.currentVAManager(null);
		page.currentContextualizationManager(null);
		page.currentSocialToolbox(null);
		page.currentSoftwareLicenses(null);
		//Make init calls
		
		$(document).ready((function(extdata,currentid,perms){ 
			return function(){ 
				if( extdata && extdata.application && !extdata.application.permissions && $.trim(currentid) === $.trim(extdata.application.id)){
					if( perms && $.isArray(perms._privs) && perms._privs.length > 0 ){
						extdata.application.permissions = { userid: userID, action: perms._privs };
					}
				}
				page.onEventReady(extdata);
			};
		})(o.data,currentid,_perms));
		page.immediate();
	};
	return page;
})();
appdb.pages.Application = appdb.pages.application;

appdb.pages.Person = (function(){
	var page = {};
	page.currentData = new appdb.utils.SimpleProperty();
	page.currentRole = new appdb.utils.SimpleProperty();
	page.currentId = new appdb.utils.SimpleProperty();
	page.currentFirstName = new appdb.utils.SimpleProperty();
	page.currentLastName = new appdb.utils.SimpleProperty();
	page.currentCName = new appdb.utils.SimpleProperty();
	page.currentCountryId = new appdb.utils.SimpleProperty();
	page.currentCountryName = new appdb.utils.SimpleProperty();
	page.currentFilterDecorator = new appdb.utils.SimpleProperty();
	page.currentFullName = function(){
		var res = "";
		if( page.currentFirstName() ){
			res += page.currentFirstName();
		}
		if( page.currentLastName() ){
			if( res ) res += " ";
			res += page.currentLastName();
		}
		return res;
	};
	page.currentRelations = new appdb.utils.SimpleProperty();
	page.currentSection = new appdb.utils.SimpleProperty();
	page.currentConnectedAccounts = new appdb.utils.SimpleProperty();
	page.currentAccessGroups = new appdb.utils.SimpleProperty();
	page.currentGroupPermissions = new appdb.utils.SimpleProperty();
	page.currentVoMembership = new appdb.utils.SimpleProperty(); //<-- data memberships
	page.currentVoMembershipPanel = new appdb.utils.SimpleProperty(); //<-- UI List
	page.currentGroupEditor = new appdb.utils.SimpleProperty(); //<-- appdb.components
	page.selectSection = function(section){
		var tab_index = 0;
		if( !section ){
			var r = appdb.routing.FindRoute();
			if( r && r.parameters && r.parameters.section ){
				section = r.parameters.section;
			} else {
				return;
			}
		}
		switch(section.toLowerCase()){
			case "publications":
				tab_index = 1;
				break;
			case "preferences":
				tab_index = 2;
				break;
			case "pendingrequests":
				tab_index = 3;
				break;
			case "manageaccounts":
				tab_index = 4;
				break;
			default:
				tab_index = 0;
				break;
		}
		if( window.persontabselect === tab_index ) return;
		window.persontabselect = tab_index;
		$( "#navdiv" + dialogCount ).tabs("select",tab_index);
		$("#ppl_details").tabs("select",tab_index);
	};
	page.updateSection = function(section, subsection){
		var subsection = subsection  || "";
		if( appdb.config.routing.useCanonical == false || !page.currentCName()) return;
		if( typeof section === "undefined" ) section = 0;
		if( typeof section === "number" ){
			switch( section ){
				case 1:
					section = "publications";
					break;
				case 2:
					section = "preferences";
					break;
				case 3:
					section = "pendingrequests";
					break;
				case 4:
					section = "manageaccounts";
					break;
				case 0:
				default:
					section = "information";
					break;
			}
		}
		if( $.trim(page.currentSection()) === $.trim(section) ) return;
		page.currentSection(section);
		if( appdb.config.routing.useCanonical == true && page.currentCName()){
			var sname = page.currentCName(), curl = "/store/person/" + sname, cname = page.currentCName(), title = page.currentFullName();
			if( $.trim(cname) !== "" ){
				switch(section){
					case "information":
						title += " profile";
						break;
					case "publications":
					case "preferences":
					case "pendingrequests":
					case "manageaccounts":
						curl += "/" + section;
						title += " " + section;
						break;
					default:
						title += " profile";
						break;
				}
				if( section === "preferences" ){
					if( subsection === "" ){
						var r = appdb.routing.FindRoute();
						if( r && r.parameters && r.parameters.section && $.trim(r.parameters.subsection)!==""){
							subsection = $.trim(r.parameters.subsection);
						}
					}
					if( subsection !== "" ){
						curl += "/" + subsection;
					}
				}
				if( section === "pendingrequests"){
					title = "Pending requests for " + page.currentFullName();
				}else if (section === "manageaccounts"){
					title = page.currentFullName() + " manage accounts";
				}
				if( curl && $.trim(curl).toLowerCase()!==$.trim(location.pathname).toLowerCase() ) {
					curl = curl.toLowerCase();
					document.title = title;
					appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data, document.title, curl );
					appdb.Navigator.setInternalMode(false);
				}
			}
		}
	};
	page.onPersonLoad = function(){
		page.initSectionGroup();
		var tab_index = 0;
		var r = appdb.routing.FindRoute();
		if( r && r.parameters && r.parameters.section ){
			switch(r.parameters.section.toLowerCase()){
				case "publications":
					tab_index = 1;
					break;
				case "preferences":
					if( userID ){
						tab_index = 2;
					}
					break;
				case "pendingrequests":
					if( userID ){
						tab_index = 3;
					}
					break;
				case "manageaccounts":
					if( userID ){
						tab_index = 4;
					}
					break;
				default:
					break;
			}
		}
		if( $("#ppl_details").length > 0 ){
			$("#ppl_details").tabs("select",tab_index);
		} else {
			$( "#navdiv" + dialogCount ).tabs("select",tab_index);
		}
		page.setupNilsCountryItems();
		page.SetupNavigationPane();
		page.initVoMembershipPanel();
		page.setupApiAccessManagement();
		page.renderAccessGroupEditor();
		page.renderFilterDecorator();
		page.initRelationEditor(false);
	};
	page.showGroupEditor = function(){
		if( page.currentGroupEditor() !== null ){
			page.currentGroupEditor().show();
		}
	};
	page.initSectionGroup = function(container,onclick){
		container = container || ".person-group";
		var navdiv = "#navdiv" + dialogCount;
		if( $(navdiv).length === 0 ){
			navdiv = "#ppl_details";
		}
		container = navdiv + " " + container;
		
		if( $(container + " > ul > li").length < 1 ){
			$(container).removeClass("groupcontainer");
			$(container + " > div").removeClass("tabcontent").removeClass("hiddengroup");
			$(container + " > ul").addClass("hidden");
		}else{
			$(container).addClass("groupcontainer");
			$(container + " > div").addClass("hiddengroup").addClass("tabcontent");
			if( $(container + " > ul > li.current > a").length > 0 ){
				var sel = $(container + " > ul > li.current > a").attr("href");
				$(container + " > " + sel).removeClass("hiddengroup");
			}else{
				$(container + " > ul > li:first").addClass("current");
				$(container + " > div:first").removeClass("hiddengroup");
			}
			$(container + " > ul > li > a").unbind("click").bind("click", function(ev){
				ev.preventDefault();
				if( typeof onclick === "function" ){
					if( onclick(this) === false ) {
						if( typeof (ev.stopImmediatePropagation) === "function" ){
							ev.stopImmediatePropagation();
						}
						return false;
					}
				}
				$(container + " > ul > li").removeClass("current");
				$(container + " > div").addClass("hiddengroup");
				var href = $(this).attr("href");
				$(this).parent().addClass("current");
				$(href).removeClass("hiddengroup");
				
				return false;
			});
		}
		$(".person-group.groupcontainer > ul > li.contactinfo > a").trigger("click");
	};
	page.SetupNavigationPane = function(){
		var n, d = page.currentId();
		if( parseInt(d) !== -1){
			n = appdb.views.NavigationPanePresets.personItem({
				id: page.currentId(),
				fullName: page.currentFullName(),
				role: page.currentRole()
			});
			appdb.views.Main.clearNavigation();
			appdb.views.Main.createNavigationList(n);
			var r = page.currentRole().description;
			r = (r)?(r):"People";
			appdb.views.Main.selectAccordion("peoplepane", r);
			if(appdb.config.routing.useCanonical == true){
				var perm = appdb.utils.getItemCanonicalUrl("person", {id: page.currentId(), cname: page.currentCName(), firstName: page.currentFirstName(), lastName: page.currentLastName()});
				$("#infodiv" + dialogCount + " .permalink").attr("href","http://" + window.location.host +  perm);
				$("#ppl_details_info .permalink").attr("href","http://" + window.location.host +  perm);
			}
		}
	};
	page.setupNilsCountryItems = (function(self){
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
		return function(){
			setTimeout(function(){
				var dom = $("#ppl_details a.nilcountryitems");
				if( userID !== null ){
					dom = $("#navdiv" + dialogCount + " a.nilcountryitems");
				}
				$(dom).unbind("click").bind("click",(function(el){
					return function(ev){
						$(el).data("country-id", appdb.pages.Person.currentCountryId() );
						$(el).data("country-name", appdb.pages.Person.currentCountryName() );
						ev.preventDefault();
						$(".dijitPopup.dijitTooltipDialogPopup").remove();
						var main = $("<div class='middlewarepopup'></div>");
						var sw = $("<a href='#' title='View related software from this country' >View related software items</a>");
						var vapp = $("<a href='#' title='View related virtual appliances from this country' >View related virtual appliances</a>");
						$(main).append(sw).append(vapp);
						$(main).addClass("middlewarepopup").append(sw).append(vapp);
						$(sw).unbind("click").bind("click", function(ev){
							ev.preventDefault();
							appdb.views.Main.showApplications({flt: "+*&application.metatype:0 +=country.id:"+$(el).data("country-id")},{isBaseQuery : true, mainTitle: 'Software of ' + $(el).data("country-name"),filterDisplay : 'Search in software...',content:'software'});
							$(".dijitPopup.dijitTooltipDialogPopup").remove();
							return false;
						});
						$(vapp).unbind("click").bind("click", function(ev){
							ev.preventDefault();
							appdb.views.Main.showApplications({flt: "+*&application.metatype:1 +=country.id:"+$(el).data("country-id")},{isBaseQuery : true, mainTitle: 'Virtual Appliances of ' + $(el).data("country-name"),filterDisplay : 'Search in Virtual Appliances...', content: 'vappliance'});
							$(".dijitPopup.dijitTooltipDialogPopup").remove();
							return false;
						});
						popup(this,main);
						return false;
					};
				})(dom));
			},1);
		};
	})(page);
	page.canEditAccessGroups = function(){
		if( userID === null ) return false;
		
		var sameprofile = false;
		if( $.trim(userID) === $.trim(page.currentId()) ){
			sameprofile = true;
		}
		var perms = page.currentGroupPermissions();
		perms = perms || [];
		perms = $.isArray(perms)?perms:[perms];
		if( perms.length === 0 ) return false;
		var edits = $.grep(perms, function(e){
			if( sameprofile ){
				return (e.canRequest || e.canAdd || e.canRemove );
			}
			return (e.canAdd || e.canRemove );
		});
		return (edits.length >0)?true:false;
	};
	page.updateAccessGroups = function(){
		var groups = $.grep(page.currentAccessGroups(), function(e){
			return ( (e.id > 0) || $.inArray($.trim(e.id), ["-1","-2","-3","-8"]) > -1);
		});
		var txt = [];
		if( groups.length > 0 ) {
			$.each(groups, function(i,e){
				txt.push(e.name);
			});
		}
		$("#navdiv" + dialogCount + " .accessgroupsviewer").html(txt.join(", "));
		page.renderAccessGroupEditor();
	};
	page.updatePendingAccessGroups = function(pending){
		pending = pending || [];
		pending = $.isArray(pending)?pending:[pending];
		if( pending.length === 0 ) return;
		var html = [];
		$("#navdiv" + dialogCount + " .accessgroupsviewer").html('');

		var groups = $.grep(page.currentAccessGroups(), function(e){
			return ( (e.id > 0) || $.inArray($.trim(e.id), ["-1","-2","-3","-8"]) > -1);
		});
		
		if( groups.length > 0 ) {
			$.each(groups, function(i,e){
				html.push(e.name);
			});
		}
		
		$.each(pending, function(i, e){
			html.push("<a title='' href='' class='pendinggroup' >" + e + "</a>");
		});
		
		$("#navdiv" + dialogCount + " .accessgroupsviewer").html(html.join(", "));
		$("#navdiv" + dialogCount + " .accessgroupsviewer").children("a").unbind("click").bind("click",function(ev){
			ev.preventDefault();
			appdb.pages.Person.showGroupEditor();
			return false;
		});
	};
	page.renderAccessGroupEditor = function(){
		$("#navdiv" + dialogCount + " .accessgroupcontainer .accessgrouphelp").attr("href",appdb.config.endpoint.wiki + "main:faq:what_are_the_different_access_groups_for");
		$("#navdiv" + dialogCount + " .accessgroupcontainer").removeClass("canedit");
			if( userID === null || page.canEditAccessGroups() === false ) return false;
		$("#navdiv" + dialogCount + " .accessgroupcontainer").addClass("canedit");
		
		$("#navdiv" + dialogCount + " .accessgroupcontainer").find("a.editgroups").unbind("click").bind("click", function(ev){
			ev.preventDefault();
			page.showGroupEditor();
			return false;
		});
		if( page.currentGroupEditor()!==null){
			page.currentGroupEditor().unsubscribeAll();
			page.currentGroupEditor().destroy();
			page.currentGroupEditor(null);
		}
		var editor = null;
		var editorOpts = {
			container: $("#navdiv" + dialogCount + " .accessgroupcontainer .accessgroupeditor").clone(),
			parent: page,
			profileId: page.currentId(),
			profileAccessGroups: page.currentAccessGroups(),
			groupPermissions: page.currentGroupPermissions()
		};
		
		editor = new appdb.components.AccessGroupEditor(editorOpts);
		page.currentGroupEditor(editor);
		page.currentGroupEditor().load();
	};
	page.renderFilterDecorator = function(){
		if( page.currentFilterDecorator() !== null ) return;
		var fdom = $("#ppl_details .filterdecorator.personitems");
		if( $(fdom).length === 0 ){
			fdom = $("#navdiv" + dialogCount + " .filterdecorator.personitems");
		}
		if( $(fdom).length === 0 ) return;
		
		var fdec = new appdb.views.FilterDecorator({
			container: fdom
		});
		fdec.render();
		
	};
	page.initVoMembershipPanel =  function(){
		var container = null;
		if( $("#ppl_details").length > 0 ){
			container = $("#ppl_details .vomembership");
		} else {
			container = $( "#navdiv" + dialogCount + " .vomembership");
		}
		if( !page.currentVoMembershipPanel() ){
			page.currentVoMembershipPanel(new appdb.views.VOMembershipList({
				container: container,
				parent: page
			}));
		}
		page.currentVoMembershipPanel().render(page.currentVoMembership());
	};
	page.initEditValidation = function(){
		if( appdb.config.routing.useCanonical === false ) return;//enabled only if canonical urls are enabled
		var fname = $("#infodiv" + dialogCount + " span[edit_name='firstName']:last > div.dijitTextBox");
		var lname = $("#infodiv" + dialogCount + " span[edit_name='lastName']:last > div.dijitTextBox");
		var suffix = $("#infodiv" + dialogCount + " span[edit_name='cnamesuffix']:last > div.dijitTextBox");
		suffix = dijit.byNode($(suffix)[0]);
		var saveProfile = $("#navdiv" + dialogCount + " a#savedetails:last");
		var _nameReport = $("#infodiv" + dialogCount + " div.namereport");
		var _namechecking = $("#infodiv" + dialogCount + " .namechecking");
		var _suffixchecking = $("#infodiv" + dialogCount + " .suffixchecking");
		var _footnote = $("#infodiv" + dialogCount + " .namereport .footnote");
		var _sufixReport = $("#infodiv" + dialogCount + " div.canonical");
		var _prefix = $(_sufixReport).find(".prefix:last");
		var _prevFname = "", _prevLname = "";
		
		var validateCname = function(){
			var _fname = $.trim(fname.get('displayedValue'));
			var _lname = $.trim(lname.get('displayedValue'));
			var _suffix = $.trim(suffix.get('displayedValue'));
			var _cname = "";
			
			if(_fname==="" || _lname===""){
				return;
			}else{
				_cname = _fname.replace(/\ /g,".") + "." + _lname.replace(/\ /g,".");
				if(_suffix.replace(/\ /g,"")){
					_cname += "." . _suffix;
				}
				_cname = encodeURI(_cname);
			}
			
			if( _prevFname === _fname && _prevLname === _lname ){
				return;
			}else{
				_prevFname = _fname;
				_prevLname = _lname;
			}
			
			var _url = "/people/nameavailable?cname=" + _cname;
			if( page.currentId() != -1 && page.currentId() != 0){
				_url += "&id=" + page.currentId();
			}
			$(_namechecking).removeClass("hidden");
			$.ajax({
				url: _url,
				success: function(d){
					var res = appdb.utils.convert.toObject(d);
					if(res.error){
						$(_prefix).text( (res.cname)?(res.cname + "."):$(_prefix).text() );
						$(saveProfile).addClass("hidden");
						$(_nameReport).addClass("error");
						$(_sufixReport).removeClass("hidden");
						
					}else{
						$(saveProfile).removeClass("hidden");
						$(_nameReport).removeClass("error");
						suffix.set("value","");
					}
					$(_namechecking).addClass("hidden");
				},
				error: function(s,t){
					$(saveProfile).addClass("hidden");
					$(_namechecking).addClass("hidden");
				}
			});
		
		};
		var validateCnameSuffix = function(val){
			var p = $(_prefix).text();
			var err = $(_footnote).find(".error");
			
			val = val || "";
			val = val.replace(/\ /g,"");
			$(err).text("");
			if( val.match(/^([a-zA-Z0-9])+$/g) == null ){
				$(saveProfile).addClass("hidden");
				$(err).text("Invalid characters used");
				//invalid
				return;
			}
			
			p = p + val;
			var _url = "/people/nameavailable?cname=" + encodeURI(p);
			if( page.currentId() != -1 && page.currentId() != 0){
				_url += "&id=" + page.currentId();
			}
			$(_suffixchecking).removeClass("hidden");
			$.ajax({
				url: _url,
				success: function(d){
					var res = appdb.utils.convert.toObject(d);
					if(res.error){
						$(saveProfile).addClass("hidden");
						$(err).text("This suffix is already taken");
					}else{
						$(saveProfile).removeClass("hidden");
						$(err).text("");
					}
					$(_suffixchecking).addClass("hidden");
				},
				error: function(s,t){
					$(err).text(s);
					$(saveProfile).addClass("hidden");
					$(_suffixchecking).addClass("hidden");
				}
			});
			
		};
		
		var startCNameValidation = (function(){
			var _timer = null;
			return function(val){
				val = val || "";
				if( _timer ) {
					clearTimeout(_timer);
				}
				_timer = setTimeout(function(){ 
					if( val ) {
						validateCnameSuffix(val);
					} else {
						validateCname();
					}
				}, 400);
			};
		})();
		
		if( $(fname).length && $(lname).length ){
			fname = dijit.byNode($(fname)[0]);
			lname = dijit.byNode($(lname)[0]);
			dojo.connect(fname, "onChange", function(){startCNameValidation();} );
			dojo.connect(lname, "onChange", function(){startCNameValidation();} );
			dojo.connect(fname, "onKeyUp", function(){startCNameValidation();} );
			dojo.connect(lname, "onKeyUp", function(){startCNameValidation();} );
			
			dojo.connect(suffix, "onChange", function(){startCNameValidation($.trim(suffix.get('displayedValue')));} );
			dojo.connect(suffix, "onKeyUp", function(){startCNameValidation($.trim(suffix.get('displayedValue')));} );

			if( page.currentId() ){
				validateCname();
			}else{
				$(saveProfile).addClass("hidden");
			}
			
			
		}
	};
	page.initConnectedAccounts = function(){
		if( $.trim(userID) === "" ||  $.trim(userID) !== $.trim( page.currentId() ) ) {
			if( page.currentConnectedAccounts() ){
				page.currentConnectedAccounts().unsubscribeAll();
				page.currentConnectedAccounts().destroy();
				page.currentConnectedAccounts(null);
			}
			return;
		}
		page.currentConnectedAccounts(new appdb.components.UserConnectedAcccounts({
			container: $("#manageaccountsdiv" + dialogCount + " .connectedaccounts"),
			parent: page,
			userid: page.currentId(),
			currentAccount: userCurrentAccount,
			currentAccounts: userCurrentAccounts
		}));
		page.currentConnectedAccounts().load();
	};
	page.scrollToSubsection = function(subsection){
		if( typeof subsection === "undefined" ){
			var r = appdb.routing.FindRoute();
			if( r && r.parameters && r.parameters.section && $.trim(r.parameters.subsection)!==""){
				subsection = $.trim(r.parameters.subsection);
			}
		}
		subsection = $.trim(subsection).toLowerCase();
		if(subsection === ""){
			window.scroll(0,0);
			return;
		}
		if( $( "#navdiv" + dialogCount +" .subsection." + subsection).length === 0 ){
			window.scroll(0,0);
			return;
		}
		var pos =  $( "#navdiv" + dialogCount +" .subsection." + subsection).offset().top;
		pos = parseInt(pos);
		window.scroll(0,pos+90);
		$( "#navdiv" + dialogCount +" .subsection." + subsection).stop().removeAttr("style").addClass("highlight").animate({"background-color":"white","borderTopColor":"white","borderLeftColor":"white","borderRightColor":"white","borderBottomColor":"white"},4000, (function(elem){
		return function(){
			$(elem).removeAttr("style").removeClass("highlight");
		};})("#navdiv" + dialogCount +" .subsection." + subsection));
		
	};
	page.setupApiAccessManagement = function(){
		if( $(".apiaccessmanagement").length === 0 ){
			return;
		}
		$(".apiaccessmanagement > ul > li > a").unbind("click").bind("click", function(ev){
			ev.preventDefault();
			$(".apiaccessmanagement > ul > li > a").removeClass("selected");
			$(".apiaccessmanagement > div").removeClass("selected");
			var selector = $(this).attr("href");
			$(this).addClass("selected");
			$(".apiaccessmanagement > div"+selector).addClass("selected");
			return false;
		});
		setTimeout(function(){
			page.scrollToSubsection();
		},150);
	};
	page.reload = function(){
		appdb.routing.Dispatch( (($.browser.msie)?window.location.hash:window.location.pathname), true);
		return false;
	};
	page.reset = function(){
		//setup defaults
		page.currentData(null);
		page.currentAccessGroups(null);
		page.currentId(-1);
		page.currentRole(-1);
		page.currentCountryId(-1);
		page.currentCountryName('');
		page.currentFirstName('');
		page.currentLastName('');
		page.currentCName('');
		page.currentVoMembership(null);
		page.currentGroupPermissions(null);
		page.currentRelations(null);
		if( page.currentConnectedAccounts() ){
			page.currentConnectedAccounts().unsubscribeAll();
			page.currentConnectedAccounts().destroy();
			page.currentConnectedAccounts(null);
		}
		if( page.currentVoMembershipPanel() ){
			page.currentVoMembershipPanel().unsubscribeAll();
			page.currentVoMembershipPanel().reset();
			page.currentVoMembershipPanel(null);
		}
		if( page.currentFilterDecorator() ){
			page.currentFilterDecorator().unsubscribeAll();
			page.currentFilterDecorator().reset();
			page.currentFilterDecorator(null);
		}
		if( page.currentOrganizationRelationList() ){
			page.currentOrganizationRelationList().unsubscribeAll();
			page.currentOrganizationRelationList().reset();
			page.currentOrganizationRelationList(null);
		}
		if( page.currentProjectRelationList() ){
			page.currentProjectRelationList().unsubscribeAll();
			page.currentProjectRelationList().reset();
			page.currentProjectRelationList(null);
		}
	};
	page.init = function(o){
		appdb.pages.reset();
		//Setup current values
		if( typeof o !== "undefined" ){
			page.currentData(o.data || null);
			page.currentId(o.id || 0);
			page.currentRole(o.role || -1);
			page.currentAccessGroups(o.groups || []);
			page.currentFirstName(o.firstName || '');
			page.currentLastName(o.lastName || '');
			page.currentCName(o.cname || '');
			page.currentVoMembership(o.vomembership || []);
			page.currentCountryId(o.countryid || -1);
			page.currentCountryName(o.countryname || '');
			page.currentGroupPermissions(o.grouppermissions || []);
			if( typeof o.relations === "string" && $.trim(o.relations) !== ""){
				o.relations = appdb.utils.base64.decode(o.relations);
				o.relations = appdb.utils.convert.toObject(o.relations);
				o.relations.relation = o.relations.relation || [];
				o.relations.relation = $.isArray(o.relations.relation)?o.relations.relation:[o.relations.relation];
				page.currentRelations(o.relations.relation);
			}else{
				o.relations = o.relations || [];
				o.relations = $.isArray(o.relations)?o.relations:[o.relations];
				page.currentRelations(o.relations || []);
			}
		}
		page.onPersonLoad();
		setTimeout(function(){
			page.initConnectedAccounts();
		},100);
	};
	page.currentOrganizationRelationList = new appdb.utils.SimpleProperty();
	page.currentProjectRelationList = new appdb.utils.SimpleProperty();
	page.initRelationEditor = function(editmode){
		editmode = (typeof editmode === "boolean")?editmode:true;
		var dom = $("#navdiv" + dialogCount + " .relationlist.person-organization" + ((editmode)?".editor":".viewer"));
		if( $(dom).length === 0 ){
			dom = $("#ppl_details_info .relationlist.person-organization"+((editmode)?".editor":".viewer"));
		}
		if( page.currentOrganizationRelationList() ){
			page.currentOrganizationRelationList().reset();
			page.currentOrganizationRelationList(null);
		}
		var rels = page.currentRelations() || [];
		if( !rels || rels.length === 0 ){
			rels = (page.currentData() || {}).relation;
			rels = rels || [];
			rels = $.isArray(rels)?rels:[rels];
		}
		var rolist = new appdb.views.RelationListOrganization({
			container: dom,
			parent: this,
			subjecttype: "person",
			targettype: "organization",
			verbname: "employee",
			data: rels|| [],
			canedit: true,
			editmode: editmode
		});
		rolist.render();
		page.currentOrganizationRelationList(rolist);
		
		var dom = $("#navdiv" + dialogCount + " .relationlist.person-project" + ((editmode)?".editor":".viewer"));
		if( $(dom).length === 0 ){
			dom = $("#ppl_details_info .relationlist.person-project"+((editmode)?".editor":".viewer"));
		}
		if( page.currentProjectRelationList() ){
			page.currentProjectRelationList().reset();
			page.currentProjectRelationList(null);
		}
		var rels = page.currentRelations() || [];
		if( !rels || rels.length === 0 ){
			rels = (page.currentData() || {}).relation;
			rels = rels || [];
			rels = $.isArray(rels)?rels:[rels];
		}
		var rplist = new appdb.views.RelationListProject({
			container: dom,
			parent: this,
			subjecttype: "person",
			targettype: "project",
			verbname: "participant",
			data: rels|| [],
			canedit: true,
			editmode: editmode
		});
		rplist.render();
		page.currentProjectRelationList(rplist);
		page.initSectionGroup(".entityrelations.groupcontainer");
		setTimeout(function(){
			$(".person-group.groupcontainer > ul > li.relatedorganizationslist > a").trigger("click");
		},10);
	};
	return page;
})();

appdb.pages.vo = (function(){
	/*
	 *  for VOs with VAs: https://<domain>/rest/1.0/vos?flt=%2B*%26application.metatype%3A1
	 *	for VOs with APPLICATIONS: https://<domain>/rest/1.0/vos?flt=%2B*%26application.metatype%3A0
	 */
	var page = {};
	
	page.currentId = new appdb.utils.SimpleProperty();
	page.currentName = new appdb.utils.SimpleProperty();
	page.currentDiscipline = new appdb.utils.SimpleProperty();
	page.currentDisciplines = new appdb.utils.SimpleProperty();
	page.currentDisciplineList = new appdb.utils.SimpleProperty();
	
	page.currentSection = new appdb.utils.SimpleProperty();
	page.currentFilterDecorator = new appdb.utils.SimpleProperty();
	page.currentDialogCount = new appdb.utils.SimpleProperty();
	page.currentImageListManager = new appdb.utils.SimpleProperty();
	page.selectSection = function(section){
		var tab_index = 0;
		if( !section ){
			var r = appdb.routing.FindRoute();
			if( r && r.parameters && r.parameters.section ){
				section = r.parameters.section;
			}else {
				return;
			}
		}
		switch(section.toLowerCase()){
			case "imagelists":
			case "imagelist":
				tab_index = 1;
				break;
			default:
				tab_index = 0;
				break;
		}
		
		window.votabselect = tab_index;
		$( "#navdiv" + dialogCount ).tabs("select",tab_index);
	};
	page.updateSection = function(section){
		if( appdb.config.routing.useCanonical == false || !page.currentName()) return;
		if( typeof section === "undefined" ) section = 0;
		if( typeof section === "number" ){
			switch( section ){
				case 1:
					section = "imagelist";
					break;
				case 0:
				default:
					section = "information";
					break;
			}
		}
		if( page.currentSection() == section ) return;
		page.currentSection(section);
		if( appdb.config.routing.useCanonical == true && page.currentName()){
			var sname = page.currentName(), curl = "/store/vo/" + sname, title = "VO " + sname;
			if( $.trim(sname) !== "" ){
				switch(section){
					case "imagelists":
					case "imagelist":
						curl += "/imagelist";
						title += " image list";
						break;
					default:
						break;
				}
				if( curl ) {
					curl = curl.toLowerCase();
					document.title = title;
					appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data, document.title, curl );
					appdb.Navigator.setInternalMode(false);
				}
			}
		}		
	};
	page.onVoLoad = function(){
		var tab_index = 0;
		var r = appdb.routing.FindRoute();
		if( r && r.parameters && r.parameters.section ){
			switch(r.parameters.section.toLowerCase()){
				case "imagelists":
				case "imagelist":
					tab_index = 1;
					break;
				default:
					break;
			}
		}
		$( "#navdiv" + dialogCount ).tabs("select",tab_index);
		page.renderFilterDecorator();
	};
	page.initSectionGroup = function(container,onclick){
		container = container || ".vo-group";
		var navdiv = "#navdiv" + dialogCount;
		container = navdiv + " " + container;
		
		if( $(container + " > ul > li").length < 1 ){
			$(container).removeClass("groupcontainer");
			$(container + " > div").removeClass("tabcontent").removeClass("hiddengroup");
			$(container + " > ul").addClass("hidden");
		}else{
			$(container).addClass("groupcontainer");
			$(container + " > div").addClass("hiddengroup").addClass("tabcontent");
			if( $(container + " > ul > li.current > a").length > 0 ){
				var sel = $(container + " > ul > li.current > a").attr("href");
				$(container + " > " + sel).removeClass("hiddengroup");
			}else{
				$(container + " > ul > li:first").addClass("current");
				$(container + " > div:first").removeClass("hiddengroup");
			}
			$(container + " > ul > li > a").unbind("click").bind("click", function(ev){
				ev.preventDefault();
				if( typeof onclick === "function" ){
					if( onclick(this) === false ) {
						if( typeof (ev.stopImmediatePropagation) === "function" ){
							ev.stopImmediatePropagation();
						}
						return false;
					}
				}
				$(container + " > ul > li").removeClass("current");
				$(container + " > div").addClass("hiddengroup");
				var href = $(this).attr("href");
				$(this).parent().addClass("current");
				$(container).find(href).removeClass("hiddengroup");
				
				return false;
			});
		}
		$(".vo-group.groupcontainer > ul > li.contactinfo > a").trigger("click");
	};
	page.SetupNavigationPane = function(){
		var n, d = page.currentId();
		if( d !== -1){
			n = appdb.views.NavigationPanePresets.voItem({
				id: page.currentId(),
				name: page.currentName(),
				discipline: page.currentDiscipline()
			});
			appdb.views.Main.clearNavigation();
			appdb.views.Main.createNavigationList(n);
			appdb.views.Main.selectAccordion("vopane","allvos");
		}
	};
	page.renderFilterDecorator = function(){
		if( page.currentFilterDecorator() !== null ) return;
		var fdom = $("#navdiv" + dialogCount + " .filterdecorator");
		if( $(fdom).length === 0 ) return;
		
		var fdec = new appdb.views.FilterDecorator({
			container: fdom
		});
		fdec.render();
	};
	page.reload = function(){
		appdb.routing.Dispatch( (($.browser.msie)?window.location.hash:window.location.pathname), true);
		return false;
	};
	page.loadResources = function(){
		$.ajax({
			url: "/vo/resources",
			type: "GET",
			data: {
				id: '' + appdb.pages.vo.currentName()
			},
			success: function(data, txtstatus) {
				$("#docdiv" + appdb.pages.vo.currentDialogCount()).html(data);
			}
		});
	};
	page.findPerson = function(name){
		name = $.trim(name);
		if( name === "" ) return false;
		name = '+"'+name+'"';
		appdb.views.Main.showPeople({flt: name},{mainTitle: "People",prepend:[]});
		if (dijit.byId("detailsdlg" + appdb.pages.vo.currentDialogCount()) !== undefined) { 
			dijit.byId("detailsdlg" + appdb.pages.vo.currentDialogCount()).onCancel(); 
		}
		return false;
	};
	page.toggleVoDescription = function(){
		var el = $("#infodiv" + appdb.pages.vo.currentDialogCount() + " .vodescription");
		if( $(el).hasClass("more") === false ){
			$(el).find(".more").text("read less");
			$(el).addClass("more");
		}else{
			$(el).find(".more").text("read more");
			$(el).removeClass("more");
		}
	};
	page.immediate = function(){
		$("#addcontactinfo" + page.currentDialogCount()).hide();
		
		var closeDialog = function () {
				if (dijit.byId("detailsdlg" + appdb.pages.vo.currentDialogCount()) !== undefined) { 
					dijit.byId("detailsdlg" + appdb.pages.vo.currentDialogCount()).onCancel(); 
				}
			};
		if ($("#detailsdlg" + page.currentDialogCount() + ":last div span")[0] !== undefined) $("#detailsdlg" + page.currentDialogCount() + ":last div span")[0].innerHTML = 'VO Details - <I>'+page.currentName()+'</I>';
		$( "#navdiv" + appdb.pages.vo.currentDialogCount() ).tabs();
		$( "#navdiv" + appdb.pages.vo.currentDialogCount() ).bind( "tabsselect", function(event, ui) {
			if( window.votabselect != ui.index){
				window.votabselect = ui.index;
				appdb.pages.vo.updateSection(ui.index);
			}
			window.votabselect = ui.index;
		});
		$( ".detailsdlgcontent div:first" ).css("height","100%");
		appdb.views.AppsList.SetupRatings();
		appdb.pages.vo.onVoLoad();
		$( "#navdiv" + appdb.pages.vo.currentDialogCount() ).find(".vmcatcherlink").attr("href",appdb.config.endpoint.vmcaster + "store/vo/" + page.currentName() + "/image.list");
		$( "#navdiv" + appdb.pages.vo.currentDialogCount() ).find(".editvowideimagelist").attr("href",appdb.config.endpoint.base + "store/vo/" + page.currentName() + "/imagelist").unbind("click").bind("click", function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			window.votabselect = 0;
			appdb.pages.vo.currentSection("information");
			$( "#navdiv" + appdb.pages.vo.currentDialogCount() ).tabs("select",1);
			return false;
		});
		shortcut.add("esc", closeDialog);
		$(document).ready(function(){
			appdb.pages.vo.loadResources();
			$(".dijitDialogTitleBar").hide();
			$("#toolbarContainer").empty();
			$('<div style="margin-right: 20px; float: right" >VO data provided by the <a href="https://operations-portal.egi.eu/vo" target="_blank"><img src="/images/opport_favicon.ico" border="0" style="vertical-align: middle" alt="" width="16"/> EGI Operations Portal</a></div>').appendTo($("#toolbarContainer"));
			$("#mainNavigation").show();
			dojo.parser.parse($("#navdiv"+page.currentDialogCount()).parent()[0]);
			setTimeout(function(){
				$("#navdiv" + page.currentDialogCount() + "_tablist").width("100%");
				if ( typeof dijit.byId("navdiv" + page.currentDialogCount()) !== "undefined" ) dijit.byId("navdiv" + page.currentDialogCount()).resize();
			},100);
			if( appdb.config.routing.useCanonical ){
				var permurl = appdb.utils.getItemCanonicalUrl("vo", page.currentName());
				if( permurl ){
					$("a.vopermalink:last").attr('href', permurl);
				}
			}
			if( appdb.config.routing && appdb.config.routing.useCanonical === true){
				$(".voapps .itemcontainer > .item > a.itemlink").each(function(index,elem){
					var content = "software";
					if( $(elem).closest(".itemcontainer").hasClass("vappitem") ){
						content = "virtualappliance";
					} else if ($(elem).closest(".itemcontainer").hasClass("siteitem") ){
						content = "site";
					}
					var surl = appdb.utils.getItemCanonicalUrl(content,$(elem).data("cname"));
					if( surl[0] == "/" ) surl = surl.slice(1);
					$(elem).attr('href', appdb.config.endpoint.base + surl);
				});
			}
			$("#navdiv" + page.currentDialogCount() + " .vodescription").children(".more").unbind("click").bind("click", function(ev){
				ev.preventDefault();
				appdb.pages.vo.toggleVoDescription();
				return false;
			});
			(new appdb.views.Permalink({container:$("<span></span>"),datatype:"vo"})).render({query:''+page.currentName()});
		});
	};
	page.loadImageLists = function(){
		if( $(".voimagelistmanager").length === 0 ) return;
		if( page.currentImageListManager() ){
			page.currentImageListManager().unsubscribeAll();
			page.currentImageListManager().destroy();
			page.currentImageListManager(null);
		}
		var voil = new appdb.components.VoImageListManager({
			container: $(".voimagelistmanager")[0],
			id: page.currentId()
		});
		page.currentImageListManager(voil);
		voil.load();
	};
	page.resetDisciplinesList = function(){
		if( page.currentDisciplineList() ){
			page.currentDisciplineList().unsubscribeAll();
			page.currentDisciplineList().reset();
			page.currentDisciplineList(null);
		}
	};
	page.renderDisciplines = function(){
		page.resetDisciplinesList();
		if( page.currentDisciplines().length === 0 ){
			$(".disciplinesrow .disciplinesvalue .emptyvalue").remove();
			$(".disciplinesrow .disciplinesvalue").append("<span class='emptyvalue'>n/a</span>");
		} else {
			page.currentDisciplineList(new appdb.views.VODisciplineList({
				container: $(".vodisciplines:first"), 
				canEdit: false,
				content: page.currentDisciplines(),
				maxViewLength: Math.min(page.currentDisciplines().length+2, 3)
			}));
			page.currentDisciplineList().render(page.currentDisciplines());
		}
	};
	page.reset = function(){
		//setup defaults
		page.currentId(-1);
		page.currentName('');
		page.currentDiscipline('');
		page.currentDisciplines(null);
		page.currentDialogCount(-1);
		page.resetDisciplinesList();
		if( page.currentFilterDecorator() ){
			page.currentFilterDecorator().unsubscribeAll();
			page.currentFilterDecorator().reset();
			page.currentFilterDecorator(null);
		}
		if( page.currentImageListManager() ){
			page.currentImageListManager().unsubscribeAll();
			page.currentImageListManager().destroy();
			page.currentImageListManager(null);
		}
	};
	page.init = function(o){
		appdb.pages.reset();
		//Setup current values
		if( typeof o !== "undefined" ){
			page.currentId(o.id || 0);
			page.currentName(o.name || '');
			page.currentDiscipline(o.discipline || '');
			page.currentDialogCount(o.dialogCount || dialogCount || 0);
			o.disciplines = o.disciplines || [];
			o.disciplines = $.isArray(o.disciplines)?o.disciplines:[o.disciplines];
			page.currentDisciplines(o.disciplines);
			if( o.id ){
				page.immediate();
				page.SetupNavigationPane();
				page.initSectionGroup();
				page.loadImageLists();
				page.renderDisciplines();
				setTimeout(function(){
					$(".vo-group.groupcontainer > ul > li.contactinfo").removeClass("hiddengroup");
				},100);
			}
		}
		
	};
	return page;
})();

appdb.pages.site = (function(){
	var page = {};
	page.reload = function(){
		appdb.routing.Dispatch( (($.browser.msie)?window.location.hash:window.location.pathname), true);
		return false;
	};
	
	page.currentId = new appdb.utils.SimpleProperty();
	page.currentData = new appdb.utils.SimpleProperty();
	page.currentName = new appdb.utils.SimpleProperty();
	
	page.onSiteLoaded = function(d){
		page.currentData(d);
		page.currentName(d.name);
		page.currentId(d.id);
		if( d.id ){
			page.immediate();
			page.SetupNavigationPane();
			page.initSectionGroup();
		}
	};
	page.immediate = function(){
		$( "#appdb_components_Site #navdiv" ).tabs();
		$( "#appdb_components_Site #navdiv").bind( "tabsselect", function(event, ui) {
			if( window.sitetabselect != ui.index){
				window.sitetabselect = ui.index;
			}
			window.sitetabselect = ui.index;
		});
		$( ".detailsdlgcontent div:first" ).css("height","100%");
	};
	page.initSectionGroup = function(container,onclick){
		container = container || ".site-group";
		var navdiv = "#appdb_components_Site #navdiv";
		container = navdiv + " " + container;
		
		if( $(container + " > ul > li").length < 1 ){
			$(container).removeClass("groupcontainer");
			$(container + " > div").removeClass("tabcontent").removeClass("hiddengroup");
			$(container + " > ul").addClass("hidden");
		}else{
			$(container).addClass("groupcontainer");
			$(container + " > div").addClass("hiddengroup").addClass("tabcontent");
			if( $(container + " > ul > li.current > a").length > 0 ){
				var sel = $(container + " > ul > li.current > a").attr("href");
				$(container + " > " + sel).removeClass("hiddengroup");
			}else{
				$(container + " > ul > li:first").addClass("current");
				$(container + " > div:first").removeClass("hiddengroup");
			}
			$(container + " > ul > li > a").unbind("click").bind("click", function(ev){
				ev.preventDefault();
				if( typeof onclick === "function" ){
					if( onclick(this) === false ) {
						if( typeof (ev.stopImmediatePropagation) === "function" ){
							ev.stopImmediatePropagation();
						}
						return false;
					}
				}
				$(container + " > ul > li").removeClass("current");
				$(container + " > div").addClass("hiddengroup");
				var href = $(this).attr("href");
				$(this).parent().addClass("current");
				$(container).find(href).removeClass("hiddengroup");
				
				return false;
			});
		}
		$(".site-group.groupcontainer > ul > li.contactinfo > a").trigger("click");
	};
	page.SetupNavigationPane = function(){
		n = appdb.views.NavigationPanePresets.siteItem({
			id: page.currentId(),
			name: page.currentName()
		});
		appdb.views.Main.clearNavigation();
		appdb.views.Main.createNavigationList(n);
		appdb.Navigator.setTitle(appdb.Navigator.Registry["Site"],[{name:page.currentName()}]);
	};
	page.reset = function(){
		//setup defaults
		page.currentId(-1);
		page.currentName('');
		page.currentData(null);
	};
	page.init = function(o){
		appdb.pages.reset();
		if( typeof o !== "undefined" ){
			page.currentId(o.id || 0);
			page.currentName(o.currentName || "");
			if( o.id ){
				page.immediate();
				page.SetupNavigationPane();
			}
		}
	};
	return page;
})();

appdb.pages.newprofile = (function(){
	var page = {};
	page.currentIndex = new appdb.utils.SimpleProperty();
	page.currentProfile = new appdb.utils.SimpleProperty();
	page.contactTypes = new appdb.utils.SimpleProperty();
	page.positionTypes = new appdb.utils.SimpleProperty();
	page.countries = new appdb.utils.SimpleProperty();
	page.gender = new appdb.utils.SimpleProperty();
	page.errorDialog = (function(){
		var _dialog = null;
		var _destroy  = function(){
			if( _dialog ){
				_dialog.hide();
				_dialog.destroyRecursive(false);
				_dialog = null;
			}
			return null;
		};
		return function(msg){
			_destroy();
			if( typeof msg === "undefined" ){
				return _dialog;
			}
			if( msg === null ){
				return _destroy();
			}
			_dialog = new dijit.Dialog({
				title: "Invalid profile information",
				content: $(msg)[0],
				style: "width: 300px"
			});
			return _dialog;
		};
	})();
	page.editProfile = function(index){
		index = index || page.currentIndex();
		window.dialogCount = index;
		editForm('editperson' + index );
		page.makeProfileContactsEditable();
		$('#savedetails').unbind("click").bind("click",(function(p){
			return function(ev) {
				ev.preventDefault();
				if( $(this).hasClass("disabled") ) return;
				if ( eval($("#editperson" + p.currentIndex()).attr("onvalidate")) ) {
					showAjaxLoading();
					$("#savedetails").addClass("disabled saving");
					$("#savedetails").closest(".contents.newprofile").addClass("saving");
					appdb.pages.newaccount.enableFeatures(false);
					$.post("/saml/createnewprofile", $("#editperson" + p.currentIndex()).serialize(),p.onCreateProfile);
				}
				return false;
			};
		})(page));
		page.checkExistingName();
		page.initRelationListEditor();
	};
	page.onCreateProfile = function(d){
		$("body").append(d);
		appdb.pages.newaccount.enableFeatures(true);
		$("#savedetails").removeClass("disabled saving");
		$("#savedetails").closest(".contents.newprofile").removeClass("saving");
		hideAjaxLoading();
	};
	page.makeProfileContactsEditable = function(index){
		index = index || page.currentIndex();
		$("#editperson" + index + " .profilecontacts .contactitem").each(function(i, e){
			$(this).find(".removecontact.button").remove();
			$(this).append('<span class="removecontact button icontext" ><img alt="remove" border="0" src="/images/cancelicon.png" title="Remove list item"></span>');
			$(this).find(".removecontact.button").unbind("click").bind("click", function(ev){
				ev.preventDefault();
				var parent = $(this).closest(".profilecontacts");
				$(this).closest(".contactitem").remove();
				if( $(parent).find(".contactitem").length === 0 ){
					$(parent).addClass("empty");
				}else{
					$(parent).removeClass("empty");
				}
				setTimeout(function(){
					page.initValidations();
					page.doValidation();
				},10);
				return false;
			});
			if( $("#editperson" + index + " .profilecontacts .contactitem").length === 0 ){
				$("#editperson" + index + " .profilecontacts").addClass("empty");
			} else {
				$("#editperson" + index + " .profilecontacts").removeClass("empty");
			}
		});
		setTimeout(function(){
			page.initValidations();
			page.doValidation();
		},10);
	};
	page.setRegion = function(index) {
		index = index || page.currentIndex();
	};
	page.getContactDataItemHtml = function(ctype, data, full){
		full = ( (typeof full === "boolean")?full:false );
		var res = "";
		if( full ){
			res = '<div class="contactitem field">';
		}
		res +='<span class="editable name" edit_type="combo" edit_data=\''+page.contactTypes()+'\' edit_style="{\'max-width\': \'130px\'}" edit_onchange="appdb.pages.newprofile.setContactType" edit_name="contactType" edit_group="true" edit_watermark="Contact Type">'+ctype+'</span><span>:</span><span class="editable value" edit_type="text" edit_name="contact" edit_group="true" edit_style="{\'max-width\': \'130px\'}" edit_watermark="Provide contact" edit_required="true">'+data+'</span>';
		if( full ){
			res += "</div>";
		}
		return res;
	};
	page.rebuildContactData = function(index) {
		index = index || page.currentIndex();
		$("#editperson" + index + " .contacts .contactitem").each(function(e){
			var vtype = $(this).find("input[name^=contactType]").parent().children("input:first").val();
			var vdata = $(this).find("input[name^=contact]:last").val();
			$(this).closest(".contactitem.field").html( page.getContactDataItemHtml( vtype, vdata ) );
		});
	};
	page.animateInvalidContactItems = function(){
		var res = [];
		$("#editperson" + index + " .contacts .contactitem").each(function(e){
			var vtype = $.trim( $(this).find("input[name^=contactType]").prev().val() );
			var vdata = $.trim( $(this).find("input[name^=contact]:last").val() );
			if( vtype === "" || vdata === "" ){
				res.push(this);
			}
		});
		return res;
	};
	page.addContactInfo = function(index) {
		index = index || page.currentIndex();
		if( window.event ) window.event.returnValue = false;
		page.rebuildContactData();
		var h = $("#editperson" + index + " .contacts").html();
		h= h + page.getContactDataItemHtml("", "", true);
		$("#editperson" + index + " .contacts").html(h);
		page.makeProfileContactsEditable();
		var e=new editForm('editperson' + index);	
		return false;
	};
	page.displayErrors = function(errs){
		var dom = $(".validations");
		$(dom).empty();
		if( !errs || errs.length === 0 || errs === true) {
			$(dom).addClass("hidden");
			return;
		}
		$(dom).removeClass("hidden");
		
		var err = errs[0];
		if( $.inArray($.trim(err).toLowerCase(), ["first name", "last name", "gender", "role", "institute", "country", "organization"]) > -1 ){
			err = "Please provide a value for <b>" + err + "</b>";
		}
		$(dom).append("<img src='/images/vappliance/redwarning.png' alt=''/><span>" + err + "</span>");
	};
	page.initValidations = function(index){
		index = index || page.currentIndex();
		$("#editperson" + index + " [edit_type] > .dijit").each((function(p){
			return function(i, e){
				dojo.connect(dijit.byNode($(e)[0]), "onChange", null, function(v){
					var name = $.trim($(this.domNode).find(".dijitInputField input").attr("name")).toLowerCase();
					if( $.inArray(name, ["firstname", "lastname"] ) > -1 ){
						p.checkExistingName();
					}
					p.doValidation();
				});
			};
		})(page));		
		$("#editperson" + index + " [edit_type] > .dijit").each(function(i, e){
			dijit.byNode($(e)[0]).focus();
		});
	};
	page.doValidation = function(){
		var errs = page.onValidate(false);
		page.displayErrors(errs);
		if( $.isArray(errs) && errs.length > 0 ){
			page.enableSave(false);
		}else{
			page.enableSave(true);
		}
	};
	page.enableSave = function(enable){
		enable = (typeof enable === "boolean")?enable:true;
		if( enable ){
			$("#maincontent.newprofile .feature.createnewprofile #savedetails").removeClass("disabled");
		}else{
			$("#maincontent.newprofile .feature.createnewprofile #savedetails").addClass("disabled");
		}
	};
	page.checkExistingNameOld = function(index){
		index = index || page.currentIndex();
		var fname = $("#editperson" + index + " input[name='firstName']").val();
		var lname = $("#editperson" + index + " input[name='lastName']").val();
		var dom = $(".profileitem .similarname");
		$(dom).empty();
		
		//check that the same name does not exist, if entry is created by someone else (e.g. a manager)
		var nameexists = 0;
		var fullname = '';
		if( page.checkExistingName.xhr && page.checkExistingName.xhr.abort ) {
			page.checkExistingName.xhr.abort();
		}
		page.checkExistingName.xhr = $.ajax({
			url: '/people/nameexists?fname='+encodeURIComponent($.trim(fname))+'&'+'lname='+encodeURIComponent($.trim(lname)),
			success: function(data,txtStatus) {
				var res = [];
				if ($.trim(data) !== '') {
					data = eval("("+data+")");
					nameexists = $.trim(data.id);
					fullname = data.fullname;
					if (nameexists !== '0' && nameexists !== '-1') {
						fullname = '<a style="color: #D96B00" href="/?p='+appdb.utils.base64.encode('/people/details?id='+nameexists)+'" target="_blank">'+fullname+'</a>';
						res.push("There already exists a person with a similar name: "+fullname+"<br/>We recommend trying to connect with that profile instead of creating a new one.");
					}
				}
				if( res.length > 0 ){
					$(dom).removeClass("hidden").append("<img src='/images/vappliance/warning.png' alt=''/>").append("<span>" + res[0] + "</span>");
				}else{
					$(dom).addClass("hidden");
				}
			}
		});
	};
	page.checkExistingName = function(index){
		index = index || page.currentIndex();
		var fname = $("#editperson" + index + " input[name='firstName']").val();
		var lname = $("#editperson" + index + " input[name='lastName']").val();
		var dom = $(".profileitem .similarname");
		$(dom).empty();
		//check that the same name does not exist, if entry is created by someone else (e.g. a manager)
		if( page.checkExistingName.model && page.checkExistingName.model !== null) {
			page.checkExistingName.model.Obs.clearAll();
			page.checkExistingName.model.Obs.unsubscribeAll(page);
			page.checkExistingName.model.Obs.unsubscribeAll();
			page.checkExistingName.model.destroy();
			page.checkExistingName.model = null;
		}
		$(".profileitem .similarname").addClass("hidden");
		if( $.trim(fname + lname) === "" ){
			return;
		}
		page.checkExistingName.model = new appdb.model.People({flt: "+" + fname + " +" + lname});
		page.checkExistingName.model.subscribe({event: "select", callback: function(v){
				$(".profileitem .similarname").addClass("hidden");
				appdb.pages.connectprofile.clearSearch();
				if( $.trim(v.count) !== "" && $.trim(v.count) !== "0" ){
					appdb.pages.connectprofile.searchProfiles(fname + " " + lname);
					var result = "There are similar profiles found in our system with the name <a href='#' title='View similar profiles' onclick='appdb.pages.newaccount.showRegistrationFeature(\"connecttoprofile\",\"search\");return false;'>" + fname + " " + lname + "</a>.<br/>";
					result += "In case you already have a profile in our system, we recommend connecting you current account with it instead of creating a new one. ";
					$(".profileitem .similarname").removeClass("hidden").append("<img src='/images/vappliance/warning.png' alt=''/>").append("<span>" + result + "</span>");
					$(".profileitem .similarname").removeClass("hidden");
				}else{
					
				}
				
		}, caller: page });
		page.checkExistingName.model.get();
	};
	page.initRelationListEditor = function(){
		editmode = (typeof editmode === "boolean")?editmode:true;
		var dom = $(".fieldvalue.organization > .value");
		var relationtypeid, rt = appdb.utils.RelationsRegistry.getByTriplet("person", "employee", "organization");
		rt = (rt.length>0)?rt[0]:null;
		if( rt === null ){
			console.log("No relation type found");
			return;
		}else{
			relationtypeid = rt.id;
		}
		var rlist = new appdb.views.AutoCompleteListOrganization({
			container: dom,
			parent: this
		});
		rlist._init();
		$(dom).append("<input type='text' id='organization' name='organization' class='hidden' value=''/>");
		rlist.subscribe({event: "change", callback: function(v){
				var val =null;
				
				if( v && typeof v === "object" && v.id ){
					val = { "id": relationtypeid, "targetguid": (v.guid || v.id) };
					val = JSON.stringify(val);
				}
				
				$("#editperson" + this.currentIndex() + " input[name='organization']:first").val(val);
				this.doValidation();
				
		}, caller: page});
	};
	page.onValidate = function(showdialog) {
		showdialog = ( ( typeof showdialog === "boolean" )?showdialog:true );
		var index = page.currentIndex();
		var valid = true;
		var invalidMessages = [];
		var sub=$('#editperson' + index)[0].getElementsByTagName("input");
		var subname,subvalue, err;
		
		for (var i in sub) {
			if (typeof sub[i] === "undefined") continue;
			subname = $.trim(sub[i].name);
			subvalue = $.trim(sub[i].value);
			switch(subname){
				case 'firstName':
					if( subvalue === '' || subname.toLowerCase() === subvalue.toLowerCase()){
						invalidMessages.push( "first name" );
						valid = false;
					}
					break;
				case 'lastName':
					if( subvalue === '' || subname.toLowerCase() === subvalue.toLowerCase()){
						invalidMessages.push( "last name" );
						valid = false;
					}
					break;
				case "countryID":
					if ( subvalue === '0' || subvalue === '' ) {
						invalidMessages.push( "country" );
						valid = false;
					}
					break;
				case "gender":
					if ( subvalue === '0' || subvalue === '' ) {
						invalidMessages.push( "gender" );
						valid = false;
					}
					break;
				case "positiontypeid":
					if ( subvalue === '0' || subvalue === '' ) {
						invalidMessages.push( "role" );
						valid = false;
					}
				default:
					break;
			}
		}
		if(valid === false){
			if( showdialog ){
				err = "<div ><div>Please fill the mandatory fields listed bellow:</div><ul>";
				for(var i =0; i<invalidMessages.length; i+=1){
					err += "<li>"+invalidMessages[i]+"</li>";
				}
				err += "</ul></div>";
				page.errorDialog($(err)).show();
			}
			return invalidMessages;
		}
		
		//check for at least one e-mail address
		if ($("input[name^='contactType']").filter('[value=7]').length === 0) {
			err = "Please enter at least one valid e-mail address in the Contact Information section";
			if( showdialog ){
				page.errorDialog($("<div>" + err + "</div>")).show();
			}
			valid = false;
			return [err];
		} else {
			//check that e-mail addresses are valid
			var errs = [];
			$("input[name^='contactType']").filter('[value=7]').each(function() {
				var n = $(this).attr("name").substr(11);
				var re = new RegExp(/^[-!#$%&'*+/0-9=?A-Z^_a-z{|}~](\.?[-!#$%&'*+/0-9=?A-Z^_a-z{|}~])*@[a-zA-Z0-9](-?[a-zA-Z0-9])*(\.[a-zA-Z](-?[a-zA-Z0-9])*)+$/);
				if (! re.test($("input[name='contact"+n+"']").val())) {
					err = "e-mail address \n`"+$("input[name='contact"+n+"']").val()+"` \nis invalid";
					if( showdialog ){
						page.errorDialog($("<div>" + err + "</div>")).show();
					}
					valid = false;
					errs.push(err);
				} else {
					//check that valid e-mail addresses do not already exist
					var mailexists = 0;
					var mailname = '';
					$.ajax({
						url: '/people/emailexists?email='+encodeURIComponent($.trim($("input[name='contact"+n+"']").val())),
						success: function(data) {
							if (data !== '') {
								data = eval("("+data+")");
								mailexists = data.id;
								mailname = data.fullname;
							}
						},
						async: false
					});
					if (mailexists !== 0 && mailexists !== '-1') {
						var clckev = "appdb.pages.connectprofile.searchProfiles('"+$("input[name='contact"+n+"']").val()+"');appdb.pages.newaccount.showRegistrationFeature('connecttoprofile','search');return false;";
						mailname = '<a style="color: #D96B00" href="#" onclick="'+clckev+'" target="_blank">'+mailname+'</a>';
						err = "e-mail address `" + $("input[name='contact"+n+"']").val() + "` is already in use by "+mailname;
						if( showdialog ){
							page.errorDialog($("<div>" + err + "</div>")).show();
						}
						valid = false;
						errs.push(err);
					}
				}
			});
			if( errs.length > 0 && showdialog === false ){
				return errs;
			}
		}
		
		
		if( errs.length > 0 && showdialog === false ){
			return errs;
		}
		return valid;
	};
	page.reset = function(){
		page.currentIndex(null);
		page.contactTypes(null);
		page.positionTypes(null);
		page.gender({ids: ['male', 'female', 'NULL'], vals: ['Male', 'Female', 'N/A']});
		page.currentProfile(null);
	};
	
	page.init = function(o){
		page.reset();
		page.currentIndex( "0" );
		page.contactTypes( o.contactTypes || {} );
		page.positionTypes( o.positionTypes || {} );
		page.countries( o.countries || {} );
		page.currentProfile( o.profile || {} );
	};
	
	return page;
})();

appdb.pages.connectprofile = (function(){
	var page = {};
	page.currentSuggestedProfileIds = new appdb.utils.SimpleProperty();
	page.currentSelectedProfile = new appdb.utils.SimpleProperty();
	
	
	page.reset = function(){
		page.currentSuggestedProfileIds(null);
		page.currentSelectedProfile(null);
	};
	page.fetchProfiles = function(ids, callback){
		
	};
	
	page.renderProfilesList = function(ids, dom){
		
	};
	
	page.searchProfiles = function(flt, callback){
		$("#profilefilter").val(flt);
		$("#profilefilter").parent().children(".filteraction").trigger("click");
	};
	page.clearSearch = function(){
		$("#profilefilter").val("");
		$("#maincontent.newprofile .connectprofile .profiles.searched .profilelist").empty();
		$("#maincontent.newprofile .connectprofile .profiles.searched").addClass("clear");
	};
	page.renderSuggestedProfiles = function(){
		var dom = $(".connectprofile > .profiles.suggested .profilelist");
		if( $(dom).length === 0 ) return;
		var list = new appdb.views.ProfileList({
			container: dom
		});
		var model = new appdb.model.People();
		model.subscribe({event: "select", callback: function(v){
			if( v && v.person ){
				v.person = $.isArray(v.person)?v.person:[v.person];
				list.render(v.person);
			}
		}});
		var flt = [];
		$.each(page.currentSuggestedProfileIds(), function(i, e){
			flt.push("=id:" + e);
		});
		model.get({flt: flt.join(" ")});
	};
	page.renderSearchedProfiles = function(filter){
		filter = $.trim(filter);
		filter = filter.replace(/\ {2,}/g," "); //reduce whitespace to single whitespace
		filter = filter.replace(/\ /g," +"); //make filter more strict

		var dom = $(".connectprofile > .profiles.searched .profilelist");
		var dompager = $(".connectprofile > .profiles.searched .pager");
		if( $(dom).length === 0 ) return;
		var list = new appdb.views.ProfileList({
			container: dom,
			parent: page
		});
		list.subscribe({event:"connect", callback: function(v){
				
		}, caller: page});
		var pagerview = new appdb.views.PagerPane({
			container: "searchedpager"
		});
		var model = new appdb.model.People({flt: filter});
		var pager = model.getPager();
		model.subscribe({event: "beforeselect", callback: function(v){
			$("#maincontent.newprofile .connectprofile .profiles.searched").removeClass("clear");
			$(".connectprofile").addClass("loading");
		}, caller : page } );
		pagerview.subscribe({event:"next",callback : function(){
            pager.next();
        }, caller : page } );
        pagerview.subscribe({event:"previous",callback : function(){
            pager.previous();
        }, caller : page } );
        pagerview.subscribe({event:"current",callback : function(v){
            pager.current(v);
        }, caller : page } );
		pager.subscribe({event: "pageload", callback: function(v){
			$(".connectprofile").removeClass("loading");
			if( v && v.data ){
				v.data = $.isArray(v.data)?v.data:[v.data];
				if( v.data.length === 0 ){
					$(".connectprofile > .profiles.searched").addClass("empty");
				}else{
					$(".connectprofile > .profiles.searched").removeClass("empty");
					pagerview.render(v.pager);
					list.render(v.data);
				}
				
			}
		},caller: page } );
		pager.current();
	};
	page.initActions = function(){
		$(".connectprofile > .header > .actions > .action").unbind("click").bind("click", (function(p){
			return function(ev){
				ev.preventDefault();
				$(this).parent().children(".action").removeClass("selected");
				$(this).addClass("selected");
				var ref = $(this).attr("href");
				$(".connectprofile > .profiles").removeClass("selected");
				$(".connectprofile > .profiles." + ref).addClass("selected");
				return false;
			};
		})(page));
		$(".connectprofile .filteraction").unbind("click").bind("click", (function(p){
			return function(ev){
				ev.preventDefault();
				if( $(".connectprofile").hasClass("loading") === false ){  
					var v = $(this).parent().children("#profilefilter").val();
					p.renderSearchedProfiles(v);
				}
				return false;
			};
		})(page));
		$(".connectprofile #profilefilter").unbind("keyup").bind("keyup",(function(p){
			return function(ev){
				ev.preventDefault();
				if( ev.keyCode !== 13 ) return false;
				if( $(".connectprofile").hasClass("loading") === false ){  
					var v = $(this).parent().children("#profilefilter").val();
					p.renderSearchedProfiles(v);
				}
				return false;
			};
		})(page));
		if( $(".connectprofile > .header > .actions > .action.suggested").length > 0 ){
			$(".connectprofile > .header > .actions > .action.suggested").trigger("click");
			page.renderSuggestedProfiles();
		}else{
			$(".connectprofile > .header > .actions > .action.search").trigger("click");
		}
		
	};
	
	page.init = function(o){
		page.reset();
		if( o.suggestedProfileIds ){
			o.suggestedProfileIds = $.isArray(o.suggestedProfileIds)?o.suggestedProfileIds:[o.suggestedProfileIds];
			page.currentSuggestedProfileIds(o.suggestedProfileIds);
		}
		page.initActions();
	};
	
	return page;
})();

appdb.pages.newaccount = (function(){
	var page = {};
	page.currentUserAccount = new appdb.utils.SimpleProperty();
	page.currentUserAccountName = new appdb.utils.SimpleProperty();
	page.currentUserAccountStatus = new appdb.utils.SimpleProperty();
	page.currentProfileToConnect = new appdb.utils.SimpleProperty();
	
	page.enableFeatures = function(enable){
		enable = ( typeof enable === "boolean" )?enable:true;
		if( enable ){
			$("#maincontent.newprofile").removeClass("disablefeatures");
		}else{
			$("#maincontent.newprofile").addClass("disablefeatures");
		}
	};
	page.showRegistration = function(){
		$("#maincontent.newprofile").removeClass().addClass("newprofile registration");
	};
	page.showRegistrationFeature = function(feature, tab){
		page.showRegistration();
		feature = $.trim(feature).toLowerCase();
		tab = $.trim(tab).toLowerCase();
		if( feature === "" ){
			$("#maincontent.newprofile .content.registration #profiles > .feature").removeClass("selected");
			return;
		}else if( $("#maincontent.newprofile .content.registration #profiles > .feature." + feature).length > 0 ){
			$("#maincontent.newprofile .content.registration #profiles > .feature." + feature + " > .title").trigger("click");
		}
		if( feature === "connecttoprofile" && tab !== "" ){
			$("#maincontent.newprofile .content.registration #profiles > .feature.connecttoprofile .actions .action." + tab).trigger("click");
		}
	};
	page.showConnectAccount = function(){
		$("#maincontent.newprofile").removeClass().addClass("newprofile connectaccount");
	};
	page.showConnectResponse = function(){
		$("#maincontent.newprofile").removeClass().addClass("newprofile connectresponse");
		$("#maincontent.newprofile .connectresponse .confirmation > .title").trigger("click");
		$("#maincontent.newprofile .connectresponse .confirmation .submitconfirmationcode").unbind("click").bind("click", (function(p){
			return function(ev){
				ev.preventDefault();
				p.submitConfirmationCode();
				return false;
			};
		})(page));
	};
	page.connectprofile = function(data,el){
		page.showConnectAccount();
		$("#maincontent .feature.sendrequest").addClass("selected");
		if( el ){
			$("#maincontent .feature.sendrequest .profilecontainer").empty().append($(el).clone());
		}
		data.contact = data.contact || [];
		data.contact = $.isArray(data.contact)?data.contact:[data.contact];
		var primary = "";
		$.each( data.contact, function(i, e){
			if( primary === "" && e.primary === "true" ){
				if( e["protected"] ){
					primary = "<img src='" + e.val() + "' alt='' />";
				}else{
					primary = "<span>" + e.val() + "</span>";
				}
			}
		});
		$("#maincontent.newprofile .primaryemail").empty().html(primary);
		$("#maincontent.newprofile .feature.sendrequest .sendconfirmationcode").unbind("click").bind("click", (function(p,d){
			return function(ev){
				ev.preventDefault();
				p.sendConfirmationCode(d);
				return false;
			};
		})(page,data));
		page.currentProfileToConnect(data.firstname + " " + data.lastname);
		page.renderProfileNameToConnect();
	};
	page.renderProfileNameToConnect = function(){
		var data = page.currentProfileToConnect();
		$("#maincontent.newprofile .profiletoconnect").text(data);
	};
	page.sendConfirmationCode = function(data){
		$("#maincontent .feature.sendrequest").addClass("saving");
		$.post("/saml/sendconfirmationcode", {id: data.id, accounttype:page.currentUserAccount(), accountname: page.currentUserAccountName() },page.onSendConfirmationCode);
	};
	page.onSendConfirmationCode = function(d){
		$("body").append(d);
		$("#maincontent .feature.sendrequest").removeClass("saving");
		page.enableFeatures(true);
		if( $(d).hasClass("error") === false ){
			page.showConnectResponse();
		}
	};
	page.submitConfirmationCode = function(){
		$("#maincontent .feature.confirmation").addClass("saving");
		var code = $("#maincontent .feature.confirmation input#confirmationcode").val();
		$.post("/saml/submitconfirmationcode", {confirmationcode: code },page.onSendConfirmationCode);
	};
	page.onSubmitConfirmationCode = function(d){
		$("body").append(d);
		$("#maincontent .feature.confirmation").removeClass("saving");
		page.enableFeatures(true);
	};
	page.initCurrentAccount = function(){
		var ua = page.currentUserAccount();
		ua = ua.replace("-sp","");
		var res = "current";
		switch(ua){
			case "egi-sso-ldap":
				res = "EGI SSO";
				break;
			case "x509":
				res = "digital certificate";
				break;
			case "":
				break;
			default:
				appdb.config.accounts.available = appdb.config.accounts.available || [];
				appdb.config.accounts.available = $.isArray(appdb.config.accounts.available)?appdb.config.accounts.available:[appdb.config.accounts.available];
				var name = $.grep(appdb.config.accounts.available, function(e){
					return ( $.trim(e.source).toLowerCase() === $.trim(ua).toLowerCase() );
				});
				if( name.length > 0 ){
					res = name[0].name;
				}else{
					res = ua;
				}
				break;
		}
		
		res += " account";
		$("span.currentuseraccount").text(res);
	};
	page.reset = function(){
		page.currentUserAccount(null);
		page.currentUserAccountName(null);
		page.currentUserAccountStatus(null);
		page.currentProfileToConnect(null);
	};
	page.loadFeatures = function(){
		if( page.currentUserAccountStatus() === "new" ){
			dialogCount = 0;
			$(function(){
				$.ajax({
					url: appdb.config.endpoint.base + "/saml/newprofile",
					success: function(d){
						$("#maincontent.newprofile .createnewprofile .main .contents").html(d);
						$("#maincontent.newprofile .createnewprofile").removeClass("loading");
						$("#maincontent.newprofile .connecttoprofile").removeClass("loading");
						$("#maincontent #profiles").removeClass("loading");
						if( $("#maincontent.newprofile #profiles .options.loading").length > 0 ){
							$("#maincontent.newprofile #profiles").removeClass("loading");
						}				
					}
				});
				$.ajax({
					url: appdb.config.endpoint.base + "/saml/connectableprofiles",
					success: function(d){
						$("#maincontent.newprofile .connecttoprofile .main .contents").html(d);
						$("#maincontent.newprofile .createnewprofile").removeClass("loading");
						$("#maincontent.newprofile .connecttoprofile").removeClass("loading");
						$("#maincontent #profiles").removeClass("loading");
					}
				});
				appdb.pages.newaccount.initFeatures();
			});
		}else{
			//if not new the set as "pendingconnect"
			page.showConnectResponse();
			appdb.pages.newaccount.initFeatures();
		}
	};
	page.initFeatures = function(){
		$("#maincontent.newprofile div.feature .title").live("click", function(ev){
			ev.preventDefault();
			if( $("#maincontent.newprofile").hasClass("disablefeatures") ) return false;
			var sel = $(this).parent().hasClass(".selected");
			if( $(this).parent().hasClass("dialog") ) return;
			$("#maincontent.newprofile div.feature").removeClass("selected");
			if( sel ){
				$(this).parent().removeClass("selected");
			}else{
				$(this).parent().addClass("selected");
			}
			return false;
		});
		$("#maincontent.newprofile .feature.dialog .title").live("click", function(ev){
			ev.preventDefault();
			$("body").children(".notifydialog").remove();
			var dialog = $('<div class="notifydialog cancelregistration display"></div>');
			$(dialog).append('<div class="shade"></div>');
			$(dialog).append('<div class="dialog"></div>');
			$(dialog).children(".dialog").append($(this).parent().children(".main").html());
			$(dialog).children(".dialog").append('<div class="actions"><a class="action close iconttext" title="" onclick="$(this).closest(\'.notifydialog\').remove();" >Close</a></div>');
			$("body").append(dialog);
			return false;
		});
	};
	page.init = function(o){
		appdb.pages.reset();
		if( o.userAccount ){
			page.currentUserAccount($.trim(o.userAccount).toLowerCase());
		}
		if( o.userAccountName ){
			page.currentUserAccountName($.trim(o.userAccountName));
		}
		if( o.userAccountStatus ){
			page.currentUserAccountStatus($.trim(o.userAccountStatus));
		}
		if( o.userProfileNameToConnect ){
			page.currentProfileToConnect(o.userProfileNameToConnect);
		}
		page.loadFeatures();
		page.initCurrentAccount();
		page.renderProfileNameToConnect();
	};
	
	return page;
})();
