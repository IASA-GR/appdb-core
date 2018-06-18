var gadgets = {};
gadgets.config = {};
gadgets.state = {};
if(typeof(gadgets.appdb)==="undefined"){
    gadgets.appdb = {};
}
if(typeof(gadgets.appdb.applications)==="undefined"){
    gadgets.appdb.applications = {};
}
gadgets.update = function(pars,p,usebase){
        var q = gadgets.utils.buildOperationsQuery(pars,p,usebase);
       $("#ajaxloader").show();
        $.ajax({url:q,success:function(data){
                $(data).children().each(function(){
                    var p = $(this);
                    var id = p.attr("id");
                    var h = p.html();
                    document.getElementById(id).innerHTML = h;
                });
                if($("#ajaxloader").is(":visible")){
                    $("#ajaxloader").hide();
                }
                $(".listContainer").scrollTop(0);
            },error:function(){
				$("#ajaxloader").hide();
			}});
};
gadgets.search = function(pars,part,overide){
     if(typeof(overide)==="undefined"){
        overide = {};
    }
    gadgets.utils.buildSearchQuery(pars);
    if(gadgets.config.query.length>0){
         $("#searchlink").addClass("activesearch");
         $("#clearquerylink").show();
    }else{
         $("#searchlink").removeClass("activesearch");
         $("#clearquerylink").hide();
    }
    gadgets.update(overide, part);
   
}
gadgets.utils = {};
gadgets.utils.showLoader = function(){
    $("#ajaxloader").show();
};
gadgets.utils.container = {
    operationToObject : function(){
        var s = gadgets.config.op.split(".");
        var o = {};
       if(s.length>0){
           o["module"] = s[0];
       }
       if(s.length>1){
           o["gadget"] = s[1];
       }
       if(s.length>2){
           o["action"] = s[2];
       }
       return o;
    },
    getModule : function(){
        var o = operationToObject();
        var s = "m_"+o.module+"."+o.gadget+"."+o.action;
        return {id : s, dom: document.getElementById(s)};
    },
    getGadget : function(){
        var o = operationToObject();
        var s = "g_"+o.module+"."+o.gadget+"."+o.action;
        return {id : s, dom: document.getElementById(s)};
    },
    getView : function(){
        var o = operationToObject();
        var s = "v_"+o.module+"."+o.gadget+"."+o.action;
        return {id : s, dom: document.getElementById(s)};
    },
    getPart : function(name){
        var o = operationToObject();
        var s = "v_"+o.module+"."+o.gadget+"."+name;
        return {id : s, dom: document.getElementById(s)};
    }
};
gadgets.utils.buildSearchQuery = function(pars,part,overide){
    var base = {};
    var added = {};
    var found = false;
    if(typeof(overide)==="undefined"){
        overide = [];
    }
    for(var b in gadgets.config.oppars){
        base[b] = gadgets.config.oppars[b];
    }
    for(var p in pars){
        if(pars[p]===null){
            continue;
        }
        for(var i in base){
            if(p===i){
                found=true;
                break;
            }
        }
        if(found===false){
            added[p] = pars[p];
        }
        found=false;
    }
    gadgets.config.query =  gadgets.utils.toQueryArrayString(added);
};
gadgets.utils.buildOperationsQuery = function(pars,part){
    var gc = gadgets.config;
    var q ="";
    q =  gc.base+"?op=" + gc.op;
    if(typeof gc.oppars !=="undefined"){
            gc.oppars =gadgets.utils.updateobjects(gc.oppars,pars);
    }else{
        gc.oppars = pars;
    }
    q = q+"&oppars=" + gadgets.utils.toQueryArrayString(gc.oppars);
    if(typeof(gadgets.config.query)!=="undefined"){
        q = q + ";" + gadgets.config.query;
    }
    if(typeof part !== "undefined"){
        gc.vpart = part;
    }else{
        if(gadgets.config.vname!==null){
            gc.vpart = gadgets.config.vname+';pager';
        }else{
             gc.vpart = 'simplelist;pager';
        }
       
    }
    if(gc.vpart!==null){
        q = q +"&vpart=" +gc.vpart;
    }
    if(gc.vpars!==null){
        if(gc.vpars.length>0){
            q=q+"&vpars="+gadgets.utils.toQueryArrayString(gc.vpars);
        }
    }
    if(gc.op_response!==null){
        q=q+"&op_response="+gc.op_response;
    }
    return q+gadgets.utils.BuildViewQuery();
};
gadgets.utils.BuildViewQuery = function(){
    var res = "";
    if(gadgets.config.vname!==null){
        res = res + "&vname="+gadgets.config.vname;
    }

    return res;
};
gadgets.utils.toQueryArrayString = function(obj){
    var res = "", i;
    for(i in obj){
        if(obj[i]===null){
            continue;
        }
        res += i + ":" + obj[i] + ";";
    }
    res=res.slice(0, res.length-1);
    return res;
};
gadgets.utils.updateobjects = function(src,obj){
    for(var n in obj){
        if(obj[n]===null){
            continue;
        }
        src[n] = obj[n];
    }
    return src;
};
gadgets.utils.ui={
    resizeContainer : function(){
        var hwin = $(window).height();
        var htop = $(".docktop").height();
        var hbot= $(".dockbottom").height();
        var hmain = hwin - (hbot*2)-htop+hbot;
        $(".listContainer").height(hmain+"px");
    }
};
   

gadgets.appdb.applications.toggleSearch = function(){
    if($(".searchPanel").is(":visible")){
         $(".searchPanel").hide();
         $("#searchlink").show();
    }else{
         $(".searchPanel").show();
         $("#searchlink").hide();
    }
    gadgets.utils.ui.resizeContainer();
};
gadgets.appdb.applications.search = function(parts){
    var trim = function(s){
        if(typeof(s)==="undefined"){
            s="";
        }
        var l=0;var r=s.length -1;
        while(l < s.length && s[l] == ' ')
        {l++;}
        while(r > l && s[r] == ' ')
        {r-=1;}
        return s.substring(l, r+1);
    };
    var notused = function(v){
        var test = gadgets.state.appdb.applications.list.parameters;
        for(var i in test){
            if(i===v){
                return false;
            }
        }
        return true;
    };
    var name = $("#searchName").val(),
    description = $("#searchDescription").val(),
    abstracttext = $("#searchAbstract").val(),
    country = $("#p_search_countries").val(),
    discipline = $("#p_search_disciplines").val(),
    subdescipline = $("#p_search_subdesciplines").val(),
    vo = $("#p_search_vos").val(),
	tag = $("#p_search_tags").val(),
	category = $("#p_search_categories").val();
	
    var res = {};
    if(trim(name)!==""){
        if(notused("name")){
            res["name"]=encodeURI(name);
        }
    }else{
        res["name"] = null;
    }
    if(trim(description)!==""){
        if(notused("description")){
            res["description"]= encodeURI(description);
        }
    }else{
        res["description"] = null;
    }
    if(trim(abstracttext)!==""){
        if(notused("abstract")){
            res["abstract"]=encodeURI(abstracttext);
        }
    }else{
        res["abstract"] = null;
    }
    if(country>-1){
        if(notused("country")){
            res["country"]=country;
        }
    }else{
        res["country"] = null;
    }
    if(discipline>-1){
        if(notused("discipline")){
            res["discipline"]= discipline;
        }
    }else{
        res["discipline"] =null;
    }
    if(subdescipline>-1){
        if(notused("subdiscipline")){
            res["subdiscipline"]=subdescipline;
        }
    }else{
        res["subdiscipline"] = null;
    }
    if(vo>-1){
        if(notused("vo")){
            res["vo"]=vo;
        }
    }else{
        res["vo"] = null;
    }
	if(tag!=-1){
        if(notused("tag")){
            res["tag"]=tag;
        }
    }else{
        res["tag"] = null;
    }
	if(category!="-1"){
		if(notused("category")){
            res["category"]=category;
        }
	}else{
		res["category"]=null;
	}
	gadgets.search(res, parts,{pageoffset:0});
};
gadgets.appdb.applications.clearSearch = function(){
    if($("#searchName").is(":disabled")===false){$("#searchName").val("");}
    if($("#searchDescription").is(":disabled")===false){$("#searchDescription").val("");}
    if($("#searchAbstract").is(":disabled")===false){$("#searchAbstract").val("");}
	
    if($("#p_appdb_applications_countries > input").is(":disabled")===false){$("#p_appdb_applications_countries input.ui-autocomplete-input").val("");$("#p_search_countries").val(-1);}
    if($("#p_appdb_applications_disciplines > input").is(":disabled")===false){$("#p_appdb_applications_disciplines input.ui-autocomplete-input").val("");$("#p_search_disciplines").val(-1);}
    if($("#p_appdb_applications_subdesciplines > input").is(":disabled")===false){$("#p_appdb_applications_subdesciplines input.ui-autocomplete-input").val("");$("#p_search_subdesciplines").val(-1);}
    if($("#p_appdb_applications_vos > input").is(":disabled")===false){$("#p_appdb_applications_vos input.ui-autocomplete-input").val("");$("#p_search_vos").val(-1);}
    if($("#p_appdb_applications_categories > input").is(":disabled")===false){$("#p_appdb_applications_categories input.ui-autocomplete-input").val("");$("#p_search_categories").val(-1);}
    
	if($("#p_appdb_applications_tags > input").is(":disabled")===false){$("#p_appdb_applications_tags input.ui-autocomplete-input").val("");$("#p_search_tags").val(-1);}

    gadgets.config.query="";
};
gadgets.utils.ui.showtext = function(e,id){
    gadgets.utils.ui.showabstract(id);
};
gadgets.utils.ui.hidelistitem = function(id){
    $("#appcontacts_"+id).css({display:'none'});
    $("#appcountries_"+id).css({display:'none'});
    $("#appabstract_"+id).css({display:'none'});
    $("#appmain_"+id).css({display:'none'});
    $("#appurllist_"+id).css({display:'none'});
    $("#appvos_"+id).css({display:'none'});
};
gadgets.utils.ui.selecttab = function(name,id){

$("#itemfooter_"+id + " > td.itemselected").removeClass("itemselected");
$("#itemfooter_"+id).children("td.#tab_"+name).addClass("itemselected");
};
gadgets.utils.ui.showmain=function(id){
    gadgets.utils.ui.loadApplication(id);
    gadgets.utils.ui.hidelistitem(id);
    $("#appmain_"+id).show();
    gadgets.utils.ui.selecttab('main',id);
};
gadgets.utils.ui.showlinks = function(id){
    gadgets.utils.ui.loadApplication(id);
    gadgets.utils.ui.hidelistitem(id);
    $("#appurllist_"+id).show();
    gadgets.utils.ui.selecttab('links',id);
};
gadgets.utils.ui.showabstract = function(id){
    gadgets.utils.ui.loadApplication(id);
    gadgets.utils.ui.hidelistitem(id);
    $("#appabstract_"+id).show();
    gadgets.utils.ui.selecttab('abstract', id);
};
gadgets.utils.ui.showcontacts = function(id){
    gadgets.utils.ui.loadApplication(id);
    gadgets.utils.ui.hidelistitem(id);

    $("#appcontacts_"+id).show();
    gadgets.utils.ui.selecttab('contacts',id);
};
gadgets.utils.ui.showcountries = function(id){
    gadgets.utils.ui.loadApplication(id);
    gadgets.utils.ui.hidelistitem(id);

    $("#appcountries_"+id).show();
    gadgets.utils.ui.selecttab('countries',id);
};
gadgets.utils.ui.showvos = function(id){
    gadgets.utils.ui.loadApplication(id);
    gadgets.utils.ui.hidelistitem(id);
    $("#appvos_"+id).show();
    gadgets.utils.ui.selecttab('vos',id);
};
gadgets.utils.ui.selectpage = function(e,len,parts){
    var v = e.value;
    var off = (v-1)*len;
    if(typeof parts === 'undefined'){
        gadgets.update({pagelength:len,pageoffset:off},gadgets.config.vname+';pager');
    }else{
        gadgets.update({pagelength:len,pageoffset:off},parts);
    }
    
};
gadgets.appdb.applications.revertToBaseQuery = function(){
        gadgets.appdb.applications.clearSearch();
        $("#clearquerylink").hide();
        gadgets.appdb.applications.search();
};
gadgets.appdb.applications.disableSearchItems = function(){
	var usedListItems = (function(){
		var items = [];
		return new function(){
			this.add = function(item) {
				items.push(item);
			};
			this.disable = function(){
				setTimeout(function(){
					var i,len=items.length;
					for(i=0; i<len; i+=1){
						$(items[i].select + " > input").val(($.isFunction(items[i].value)?items[i].value():items[i].value));
						$(items[i].select + " > input").prop("disabled", true);
						$(items[i].select + " > button").prop("disabled", true);
					}
				},1);
			};
		};
	})();
    var setToBase = function(p){
        for(var i in gadgets.config.oppars){
            if(i===p){
                return true;
            }
        }
        return false;
    };
    var getValueFromText = function(o,v){
        var res = -1;
        var i = parseInt(v);
        
       $(o).find("option").each(function(){
           if((''+i)=='NaN'){
            if($(this).text().toLowerCase()===v.toLowerCase()){
                res = $(this).val();
            }
           }else{
               if($(this).val()===v){
                   res = $(this).val();
               }
           }
       });
       return res;
    };
    if(setToBase("name")){
        $("#searchName").val(gadgets.config.oppars.name).prop("disabled", true);
    }
    if(setToBase("description")){
        $("#searchDescription").val(gadgets.config.oppars.description).prop("disabled", true);
    }
     if(setToBase("abstract")){
        $("#searchAbstract").val(gadgets.config.oppars['abstract']).prop("disabled", true);
    }
     if(setToBase("vo")){
        $("#p_search_vos").val(getValueFromText($("#p_search_vos"),gadgets.config.oppars.vo)).prop("disabled", true);
		usedListItems.add({select:"#p_appdb_applications_vos",value:function(){return $("#p_search_vos > option:selected").text();}});
    }
    if(setToBase("country")){
        $("#p_search_countries").val(getValueFromText($("#p_search_countries"),gadgets.config.oppars.country)).prop("disabled", true);
		usedListItems.add({select:"#p_appdb_applications_countries",value:function(){return $("#p_search_countries > option:selected").text();}});
    }
    if(setToBase("discipline")){
        $("#p_search_disciplines").val(gadgets.config.oppars.discipline).prop("disabled", true);
		usedListItems.add({select:"#p_appdb_applications_disciplines",value: function(){return $("#p_search_disciplines > option:selected").text().replace(/^[\?]+/,"");}});
    }
    if(setToBase("subdiscipline")){
        $("#p_search_subdesciplines").val(gadgets.config.oppars.subdiscipline).prop("disabled", true);
		usedListItems.add({select:"#p_appdb_applications_subdisciplines",value:function(){return $("#p_search_subdesciplines > option:selected").text();}});
    }
	if(setToBase("tag")){
		$("#p_search_tags").val(gadgets.config.oppars.tag).prop("disabled", true);
		usedListItems.add({select:"#p_appdb_applications_tags",value:function(){return $("#p_search_tags > option:selected").text();}});
	}
	if(setToBase("category")){
		$("#p_search_categories").val(gadgets.config.oppars.category).prop("disabled", true);
		usedListItems.add({select:"#p_appdb_applications_categories",value:function(){return $("#p_search_categories > option:selected").text().replace(/^[\?]+/,"");}});
	}
	usedListItems.disable();
};
gadgets.utils.ui.loadApplication = function (appid){
    var u  = gadgets.config.base;
    u+="?op=appdb.applications.id&vpart=data&oppars=id:"+appid;
    if($("#apploaded_"+appid).length>0){
       return ;
    }
    $.get(u,function(data){
        $(data).children("div#p_appdb_applications_data").children("table").children("tbody").children().each(function(){
             var p = $(this);
             var id = p.attr("id");
             var h = p.html();
             $("#"+id).html(h);
             $("#"+id).append("<div id='apploaded_"+appid+"' style='display:none'/>");
        });
    });
};

gadgets.utils.ui.ApplicationDetails = {};
gadgets.utils.ui.ApplicationDetails.load = function(appid){
    var u  = gadgets.config.base;
    var det;
    u+="?op=appdb.applications.id&vpart=details&oppars=id:"+appid;
    if($("#appdetails_"+appid).length>0){
       gadgets.utils.ui.ApplicationDetails.show(appid);
       return ;
    }else{
        det = $("<div id='appdetails_"+appid+"' style='display:none'></div>");
    }
    $("#ajaxloader").show();
    $.get(u,function(data){
            $("#ajaxloader").hide();
            $(det).append($(data).children("div#p_appdb_applications_details").html());
            $(".listContainer").append(det);
            gadgets.utils.ui.resizeContainer();
            gadgets.utils.ui.ApplicationDetails.show(appid);
        });
        
};
gadgets.utils.ui.ApplicationDetails.show = function(appid){
     if($(".searchPanel").is(":visible")){
         $(".searchPanel").hide();
     }
     $("#searchlink").hide();
    $("#clearquerylink").hide();
    $("ul.listpager").children("li").each(function(){$(this).css("visibility",'hidden');});

    gadgets.utils.ui.ApplicationDetails.showAnimation($("#appdetails_"+appid));
};
gadgets.utils.ui.ApplicationDetails.hide = function(id){
    $("ul.listpager").children("li").each(function(){$(this).css("visibility",'visible');});
   $("#searchlink").show();
   if(typeof gadgets.config.query!=='undefined' && gadgets.config.query!==''){
       $("#clearquerylink").show();
   }
   gadgets.utils.ui.ApplicationDetails.hideAnimation($("#appdetails_"+id));
};
gadgets.utils.ui.ApplicationDetails.scrollState = 0;
gadgets.utils.ui.ApplicationDetails.backLeft = null;
gadgets.utils.ui.ApplicationDetails.showAnimation = function(obj){
    gadgets.utils.ui.resizeContainer();
    gadgets.utils.ui.ApplicationDetails.scrollState = $(".listContainer").scrollTop();
    $("#p_appdb_applications_simplelist").hide();
    $(obj).show();
     var lc = $(".listContainer");
     lc.scrollTop(0);
     var bb =$("div.backButton");
     var hh = Math.round((lc.height()/2));
     if( gadgets.utils.ui.ApplicationDetails.backLeft===null){
         gadgets.utils.ui.ApplicationDetails.backLeft = $(".detailsBack").offset().left;
     }
     
     $(bb).css({display:'block',position:'absolute',top:hh+'px',left:gadgets.utils.ui.ApplicationDetails.backLeft,'z-index':'100000'});
     lc.scroll(function(){});
};
gadgets.utils.ui.ApplicationDetails.hideAnimation = function(obj){
     $("#p_appdb_applications_simplelist").show();
     $(".listContainer").scrollTop(gadgets.utils.ui.ApplicationDetails.scrollState);
     gadgets.utils.ui.ApplicationDetails.scrollState = 0;
     $(obj).hide();
     $(".listContainer").scroll(function(){});
};
gadgets.utils.ui.ApplicationDetails.backOver = function(obj){
    $(obj).addClass('backHover');
};
gadgets.utils.ui.ApplicationDetails.backOut = function(obj){
    $(obj).removeClass('backHover');
};
gadgets.utils.ui.ApplicationDetails.toggleContacts = function(id){
    $('#appDetailContact_'+id).toggle();
};
gadgets.utils.ui.ApplicationDetails.toggleAbstract = function(id){
    $('#appDetailAbstract_'+id).toggle();
};
gadgets.utils.ui.ApplicationDetails.togglePub = function(id){
    $('#appDetailPub_'+id).toggle();
};
gadgets.appdb.applications.showHelp = function(){
    var help = $("#helpContent");
    help.width($(".listContainer").width()-2);
    help.height($(".listContainer").height());
    if($(".searchPanel").is(":visible")){
         $(".searchPanel").hide();
    }
    $("#searchlink").hide();
    $("#clearquerylink").hide();
    $("ul.listpager").children("li").each(function(){$(this).css("visibility",'hidden');});
    $("#helpContent").height($(".listContainer").height());
    $(".listContainer").hide();
    $("#helpContent").show();
    $("#helpDocs").height($("#helpContent").height()-$("#helpHeader").height());
    $("#helpDocs").children().each(function(){
        $(this).height($("#helpDocs").height()-5);
    });

};
gadgets.appdb.applications.hideHelp = function(){
    $("ul.listpager").children("li").each(function(){$(this).css("visibility",'visible');});
    $("#searchlink").show();
    if(typeof gadgets.config.query!=='undefined' && gadgets.config.query!==''){
       $("#clearquerylink").show();
    }
    gadgets.appdb.applications.helpRegister($("#helpAboutButton"));

    $("#helpContent").hide();
    $(".listContainer").show();
};
gadgets.appdb.applications.helpItem = function(name){
    return function(o){
        $("#helpDocs").children().each(function(){
           $(this).hide();
        });
        $("#"+name).show();
        $(".selectedButton").each(function(){
           $(this).removeClass("selectedButton");
        });
        $(o).addClass("selectedButton");
    };
};
gadgets.appdb.applications.helpRegister = gadgets.appdb.applications.helpItem('helpRegister');
gadgets.appdb.applications.helpAbout = gadgets.appdb.applications.helpItem('helpAbout');
gadgets.appdb.applications.helpContact = gadgets.appdb.applications.helpItem('helpContact');
gadgets.appdb.applications.helpChangelog =gadgets.appdb.applications.helpItem('helpChangelog');
gadgets.utils.ui.sortSelections =function (elems) {
    var selects = [], elem = null, tmp = null, op=null, i, e, s;
    if(typeof elems === "undefined"){
        selects = $("select");
    }else if(typeof elems === "string"){
        if($("select"+elems).length!==0){
            selects[0] =  $("select"+elems)[0];
        }
    }else{
        for (s in elems){
            if($("select#"+elems[s]).length!==0){
                selects[selects.length] = $("select#"+elems[s])[0];
            }
        }
    }
    for (e =0; e<selects.length; e+=1){
        elem = selects[e];
        tmp = new Array();
        for (i=0;i<elem.options.length;i++) {
                tmp[i] = new Array();
                tmp[i][0] = elem.options[i].text;
                tmp[i][1] = elem.options[i].value;
        }
        tmp.sort(function(x,y){
          var a = String(x).toUpperCase();
          var b = String(y).toUpperCase();
          if (a > b)
             return 1;
          if (a < b)
             return -1;
          return 0; 
        });
        while (elem.options.length > 0) {
            elem.options[0] = null;
        }
        for (i=0;i<tmp.length;i++) {
                op = new Option(tmp[i][0], tmp[i][1]);
                elem.options[i] = op;
        }
    }
};
$(document).ready(function(){
    $(".view-container").show();
    $(".searchPanel").hide();
    gadgets.utils.ui.resizeContainer();
    
    gadgets.utils.ui.sortSelections(["p_search_countries","p_search_vos","p_search_tags"]);
    gadgets.appdb.applications.disableSearchItems();
});
$(window).resize(function(){
    gadgets.utils.ui.resizeContainer();
});
