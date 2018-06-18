<?php
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
?><?php
if((array_key_exists("image", $_GET)) && ($_GET["image"]==="email")){
    header('PRAGMA: NO-CACHE');
    header('CACHE-CONTROL: NO-CACHE');
    header('Content-type: image/png');
    echo web_get_contents('http://appdb.egi.eu/texttoimage/?msg=appdb-support@hellasgrid.gr');
} else {
    include "AppdbAPI.php";
$api = new AppDBrestAPIHelper();
$vos = $api->VOs();
$vos = $vos->vo();
$regional = $api->Regional();

$countries = $regional->country();
$mids = $api->Middlewares();
$mids = $mids->middleware();
$tags = $api->Tags();
$tags = $tags->tag();
$api2 = new AppDBrestAPIHelper('1.0');
$disciplines = $api2->Desciplines();
$disc = $disciplines->discipline();
$categories = $api->categories();
$categories = $categories->category();

function getHierarchyEntry($entry, $data,$depth = 0){
	$id = $entry->attr("id");
	$val = ("".$entry);
	$pid = $entry->attr("parentid");
	$res = array("id"=>$id, "value"=> $val, "parentid"=> $pid, "depth"=>$depth, "children"=>array());
	foreach( $data as $d ){
		$cpid = $d->attr("parentid");
		if( $cpid == $id ){
			$res["children"][] = getHierarchyEntry($d,$data,($depth+1));
		}
	}
	return $res;
}
function getFlatHierarchy($entry){
	$id = $entry["id"];
	$val = $entry["value"];
	$indentval = $val;
	$pid = $entry["parentid"];
	$depth = $entry["depth"];
	$children = $entry["children"];
	$res = array();
	
	if($depth > 0){
		$indent = "";
		for($i=0; $i<$depth; $i+=1){
			$indent.="?";
		}
		$indentval = $indent . $val;
	}
	$res[] = array("id"=>$id, "value"=> $val, "parentid"=> $pid, "depth"=>$depth, "indentvalue"=>$indentval );
	if( is_array($children) && count($children) > 0 ){
		foreach($children as $e){
			if( $e ){
				$newres = getFlatHierarchy($e);
				foreach($newres as $n){
					$res[]= $n;
				}
			}
		}
	}
	return $res;
}
function getHierarchyValues($data){
	$entries = array();
	$res = array();
	foreach($data as $d){
		if( !$d->attr("parentid") ){
			$entries[] = getHierarchyEntry($d, $data);
		}
	}
	foreach($entries as $e){
		$newres = getFlatHierarchy($e);
		foreach($newres as $n){
			$res[]= $n;
		}
	}
	return $res;
}	
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
		<meta http-equiv="X-UA-Compatible" content="IE=8" />
        <link rel="stylesheet" type="text/css" href="resources/css/default.css"  media="screen" />
        <link rel="stylesheet" type="text/css" href="resources/css/screen.css?v=<?php echo exec("cat ". APPLICATION_PATH . "/../VERSION");?>" media="screen"/>
		<style type="text/css">
			html, body{width: 100%;
				height: 100%;
				padding: 0px;
				margin: 0px;
				background-color: whiteSmoke;
				background-image: none;
				font-family: "Arial","Sans-serif" !important;
				font-size: 9pt;
			}
			.sitebanner {
				height:135px;
				padding: 0px;
				background-color: whiteSmoke;
				border: 1px solid #CCC;
				border-radius: 4px 4px 4px 4px;
				box-shadow: 2px 3px 7px gray;
				margin: 0px;
				padding-bottom: 3px;
				padding-top: 3px;
				position: relative;
				top: 7px;
				max-width: 1024px;
				width:1024px;
				text-align: left;
				margin-bottom: 15px;
			}
			.sitebanner > .topmenubar{
				max-height: 40px;
				height: 40px;
				min-height: 40px;
				background: none;
				background-color: #333;
				box-shadow: 0 2px 6px #333;
				position: fixed;
				width: 100%;
				left: 0px;
				right: 0px;
				top: 0px;
				z-index: 950;
			}
			.sitebanner > .main{
				height: 110px;
				max-width: 1024px;
				margin: 0 auto;
				position: relative;
				top: 0px;
				z-index: 2000;
			}
			.sitebanner > .main > .logo{
				display: block;
				width: auto;
				top: 13px;
				left: 4px;
				position: absolute;
				margin: 3px;
			}
			.sitebanner > .main > .logo > img{
				width: 80px;
				height: 80px;
				vertical-align: middle;
			}
			.sitebanner > .main > .logo > span{
				width: 400px;
				height: 60px;
				font-family: "Open Sans", Arial;
				font-size: 24px;
				color: #333;
				display: inline-block;
				position: relative;
				vertical-align: middle;
				padding: 5px;
				top: 10px;
			}
			.sitebanner > .main > .logo > span > span{
				letter-spacing: 4px;
				color: #5C84FA;
				font-size: 22px;
			}
			.sitebanner > .main > .logo > span > .subtext{
				font-size: 16px;
				color: #0F4FF3;
				letter-spacing: 0px;
				vertical-align: top;
				font-family: "Open Sans",Arial;
				font-weight: normal;
			}
			.sitebanner > .main > .contents {
				position: absolute;
				top: 0px;
				right: 0px;
				padding: 0px;
				margin: 0px;
				top: 0px;
				list-style: none;
				vertical-align: top;
			}
			.sitebanner > .main > .contents > li {
				display: inline-block;
				position: relative;
				width: 150px;
				height: 90px;
				margin: 5px;
				text-align: center;
				vertical-align: bottom;
				border-radius: 3px;
			}
			.sitebanner > .main > .contents > li.software{
				background-color: #0E72A2;
			}
			.sitebanner > .main > .contents > li.vappliance{
				background-color: #094461;
			}
			.sitebanner > .main > .contents > li.researchers{
				background-color: #405C69;
			}
			.sitebanner > .main > .contents > li > span{
				display: block;
				width: 100%;
				padding: 1px;
				position: absolute;
				left: 6px;
				right: 6px;
				bottom: 1px;
				color: whiteSmoke;
				display: inline-block;
				margin: 0 auto;
				text-align: left;
				font-family: "Open Sans",Arial;
				font-size: 18px;
				white-space: pre-wrap;
				vertical-align: bottom;
			}
			.toolbar{
				width:1024px;
			}
			.treeitems span{
				color: #005CE5;
			}
			#mainTable{
				width: 1024px;
				min-width: 1024px;
				max-width: 1024px;
				box-shadow: 0 0 50px #E0E0E0;
			}
			#mainTable tbody{
			}
		</style>
		<script type="text/javascript" src="resources/scripts/jquery-1.5.2.min.js"></script>
		<script type="text/javascript" src="resources/scripts/jquery-ui.min.js"></script>
         <script type="text/javascript" src="resources/scripts/ui.spinner.min.js"></script>
		 <script type="text/javascript">
		  (function( $ ) {
			  $.widget( "ui.combobox", {
				  _create: function() {
					  var self = this,
						  select = this.element.hide(),
						  selected = select.children( ":selected" ),
						  value = selected.val() ? selected.text() : "";
					  var input = this.input = $( "<input>" )
						  .insertAfter( select )
						  .val( value )
						  .autocomplete({
							  delay: 0,
							  minLength: 0,
							  source: function( request, response ) {
								  var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
								  $(input).removeClass("invalidInputData");
								  response( select.children( "option" ).map(function() {
									  var text = $( this ).text();
									  if ( this.value && ( !request.term || matcher.test(text) ) )
										  return {
											  label: text.replace(
												  new RegExp(
													  "(?![^&;]+;)(?!<[^<>]*)(" +
													  $.ui.autocomplete.escapeRegex(request.term) +
													  ")(?![^<>]*>)(?![^&;]+;)", "gi"
												  ), "<strong>$1</strong>" ),
											  value: text.replace(/\?/g,''),
											  parentid: $(this).data("parentid"),
											  id: $(this).val(),
											  option: this
										  };
								  }) );
							  },
							  select: function( event, ui ) {
								  ui.item.option.selected = true;
								  self._trigger( "selected", event, {
									  item: ui.item.option
								  });
							  },
							  change: function( event, ui ) {
								  if ( !ui.item ) {
									  var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "$", "i" ),
										  valid = false;
									  select.children( "option" ).each(function() {
										  if ( $( this ).text().match( matcher ) ) {
											  this.selected = valid = true;
											  return false;
										  }
									  });
									  if ( !valid ) {
										  // remove invalid value, as it didn't match anything
										  $( this ).val( "" );
										  select.children(":selected").prop("selected", false);
										  select.val( "-1" );
										  input.data( "autocomplete" ).term = "";
										  return false;
									  }
								  }
								  $(input).removeClass("invalidInputData");
							  }
						  })
						  .addClass( "ui-widget ui-widget-content ui-corner-left" );

					  input.data( "autocomplete" )._renderItem = function( ul, item ) {
						  var label = item.label || "";
						  var txt = label.replace(/\?/g,"");
						  var len = label.match(/\?/g);
						  if( len ){
							  len = len.length;
						  }else{
							  len = 0;
						  }
						  label = "";
						  for(var i=0; i<len; i+=1){
							  label += "<span style='padding-left:10px;display: inline-box;'></span>";
						  }
						  label += "<span style='display: inline-block;white-space:nowrap;overflow:hidden;padding:0;margin:0;text-align:left;'>" + txt + "</span>";
						  label = "<div style='overflow:hidden;;white-space:nowrap;width: 100%;padding:0;margin:0;text-align:left;'>" + label + "</div>";
						  var _crawlitems = function(dom,id){
							  $.each($(dom).children("li"), function(i,e){
									if( ($(e).data("parentid")<<0) == (id<<0) ){
										$(e).addClass("treeitems");
										_crawlitems(dom,$(e).data("id"));
									}
								});
						  };
						  return $( "<li data-parentid='"+item.parentid+"' data-id='"+item.id+"'></li>" )
							  .data( "item.autocomplete", item )
							  .bind("mousemove", function(ev){
								$(".treeitems").removeClass("treeitems");
								var id = $(this).data("id");
								$(this).addClass("treeitems");
								_crawlitems($(this).parent(),id);
								
							}).bind("mouseleave", function(ev){
								$(".treeitems").removeClass("treeitems");
							})
							.append( "<a>" + label + "</a>" )
							.appendTo( ul );
					  };
					  input.bind("keyup",function(){
						  var txt = $(input).val(), found = false;
						  if(txt==""){
							  found = true;
							  $(input).removeClass("invalidInputData");
							  return;
						  }else{
							  select.children("option").each(function(){
								  if($(this).text().toLowerCase()===txt.toLowerCase()){
									  found=true;
									  return false;
								  }
							  });
						  }
						  if(found ){
							  $(input).removeClass("invalidInputData");
						  } else {
							  $(input).addClass("invalidInputData");
						  }

					  }).bind("blur", function(){
						  $(input).removeClass("invalidInputData");
					  });

					  this.button = $( "<button type='button'>&nbsp;</button>" )
						  .attr( "tabIndex", -1 )
						  .attr( "title", "Show All Items" )
						  .insertAfter( input )
						  .button({
							  icons: {
								  primary: "ui-icon-triangle-1-s"
							  },
							  text: false
						  })
						  .removeClass( "ui-corner-all" )
						  .addClass( "ui-corner-right ui-button-icon" )
						  .click(function() {
							  // close if already visible
							  if ( input.autocomplete( "widget" ).is( ":visible" ) ) {
								  input.autocomplete( "close" );
								  return;
							  }

							  // work around a bug (likely same cause as #5265)
							  $( this ).blur();

							  // pass empty string as value to search for, displaying all results
							  input.autocomplete( "search", "" );
							  input.focus();
						  });
				  },

				  destroy: function() {
					  this.input.remove();
					  this.button.remove();
					  this.element.show();
					  $.Widget.prototype.destroy.call( this );
				  }
			  });
		  })( jQuery );
		  
		  </script>
        <script type="text/javascript">
          var domain="<?php echo "http://".$_SERVER['APPLICATION_UI_HOSTNAME']."/"; ?>";
          var time = null;
          function sortlists(elems) {
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
          }
          $(function() {
                sortlists(["listVos","listCountries","listMiddlewares","listTags"]);
                $("#mainTable").show();
                $("#txtWidth").spinner({min:2,max:5000 });
                $("#txtHeight").spinner({min:2,max:5000 });
                $("#txtLength").spinner({min:2,max:300 });
				$("select.searchdroplist").parent().addClass("ui-widget");
				$("select.searchdroplist").combobox();
                
		$("#tabs").tabs().addClass('ui-tabs-vertical ui-helper-clearfix');
		$("#tabs li").removeClass('ui-corner-top').addClass('ui-corner-left');
                $("#aboutLink").trigger("click");
                $("#aboutLink").click(function(){
                    $("#preview").hide();
                    screenHeight = $(window).height();
					var headoff = ($(".head").offset())?$(".head").offset().top:0;
                     var fixedheight = screenHeight-$("#toolbarrow").outerHeight()-$(".head").outerHeight()-headoff-$("#footer").outerHeight()-15;
                     $("#container").height(fixedheight);
                });
                
                $("#basicLink").click(function(){
                    $("#preview").hide();
                    screenHeight = $(window).height();
					var headoff = ($(".head").offset())?$(".head").offset().top:0;
                     var fixedheight = screenHeight-$("#toolbarrow").outerHeight()-$(".head").outerHeight()-headoff-$("#footer").outerHeight()-15;
                     $("#container").height(fixedheight);
                });

                $("#filteringLink").click(function(){
                    $("#preview").hide();
                    screenHeight = $(window).height();
					var headoff = ($(".head").offset())?$(".head").offset().top:0;
                     var fixedheight = screenHeight-$("#toolbarrow").outerHeight()-$(".head").outerHeight()-headoff-$("#footer").outerHeight()-15;
                     $("#container").height(fixedheight);
                });
                 $("#generateLink").click(function(){
                    $("#preview").show();
                });
                $("a[href='#tabGenerate']").click(function(){createFrameTag();});
                $('#tabs').removeClass('ui-corner-all');


	});
        $(window).resize(function(){
            screenHeight = $(window).height();
			var headoff = ($(".head").offset())?$(".head").offset().top:0;
                 var fixedheight = screenHeight-$("#toolbarrow").outerHeight()-$(".head").outerHeight()-headoff-$("#footer").outerHeight()-15;
                 if($("#container").height()< fixedheight){
                    $("#container").height(fixedheight);
                 }
        });
        

        function createFrameTag(){
            var u = createUrl();
			var ishttps = ($.trim(location.protocol).toLowerCase() === "https:")?true:false;
			if( ishttps === true ){
				u = u.replace(/^http\:/,"https:");
			}else{
				u = u.replace(/^https\:/,"http:");
			}
            var res = "<iframe frameborder='0' src='"+u+"' width='"+getWidth()+"' height='"+getHeight()+"' ><center>Your browser does not support the iframe element.</center></iframe>";
            document.getElementById("frameTag").value = res;
            res ="<iframe frameborder='0' src='"+u+"' width='"+getWidth()+"' height='"+getHeight()+"' style='visibility:hidden' onload='frameloaded(this);' ><center>Your browser does not support the iframe element.</center></iframe>";
            $("#timer").hide();
            $("#loader").show();
            time = new Date();
            document.getElementById("preview").innerHTML = res;
          
            $("#container").height( $("#preview").outerHeight(true)+25);
            $("#container").width($("#mainTable").width());
            screenHeight = $(window).height();
			var headoff = ($(".head").offset())?$(".head").offset().top:0;
                 var fixedheight = screenHeight-$("#toolbarrow").outerHeight()-$(".head").outerHeight()-headoff-$("#footer").outerHeight()-15;
                 if($("#container").height()< fixedheight){
                    $("#container").height(fixedheight);
                 }
                 
        }
        function displayTimer(){
            var newtime = new Date();
            var res = newtime.getTime() - time.getTime();
            $("#loader").hide();
            $("#timer").show();
            $("#timer").text("Finished at " + (res/1000) + " sec");
        }
        function frameloaded(f){
            displayTimer();
            f.style.visibility = 'visible';
        }
        function getWidth(){
			var w = document.getElementById("txtWidth").value;
			return w;
		}
	function getHeight(){
			var h = document.getElementById("txtHeight").value;
			return h;
		}
        function createUrl(){
            var name = document.getElementById("txtName").value,
            description = document.getElementById("txtDescription").value,
            abstracttxt = document.getElementById("txtAbstract").value,
             vo = document.getElementById("listVos").options[document.getElementById("listVos").selectedIndex].text,
            country =document.getElementById("listCountries").options[document.getElementById("listCountries").selectedIndex].text,
            discipline = document.getElementById("listDisciplines").value,
            title = document.getElementById("txtTitle").value,
            pagelength = document.getElementById("txtLength").value,
            pageoffset = document.getElementById("txtOffset").value,
            enablesearch = document.getElementById("showSearch").checked,
            viewName = document.getElementById("viewName").value,
            middleware = document.getElementById("listMiddlewares").value,
			tag = document.getElementById("listTags").value,
			category = document.getElementById("listCategories").value;

            var res = domain+"gadgets/gadgets.php?op=appdb.applications.list",
            oppars = "",vpars="";

            if(name!==""){
                oppars += ";name:"+escape(name);
            }
            if(description!==""){
                oppars +=";description:"+escape(description);
            }
            if(abstracttxt!==""){
                oppars += ";abstract:"+escape(abstracttxt);
            }
            if(vo!==''){
                oppars += ";vo:"+escape(vo);
            }
            if(country!==""){
                oppars += ";country:" + escape(country);
            }
            if(discipline!=="-1"){
                oppars += ";discipline:" + discipline;
            }
            if(middleware!=="-1"){
                oppars += ";middleware:"+middleware;
            }
			if(tag !== "-1"){
				oppars += ";tag:"+tag;
			}
			if(category !== "-1"){
				oppars += ";category:"+category;
			}
            if(pagelength!=="0" & pagelength!==""){
                if(isNaN(pagelength)){
                    alert("Page length must be a number");
                    return;
                }
                if(pagelength<=0){
                    alert("Page length must be greater than  zero");
					return;
                }
                oppars += ";pagelength:" + pagelength;
            }else{
				oppars += ";pagelength:5";
			}
            if(pageoffset!=="0" & pageoffset!==""){
                if(isNaN(pageoffset)){
                    alert("Page offset should be a number");
                    return;
                }
				if(pageoffset<0){
                    alert("Page offset must be greater than  zero");
					return;
                }
                oppars += ";pageoffset:" + pageoffset;
            }else{
				oppars += ";pageoffset:0";
			}
            if(oppars.length>0){
                oppars = oppars.substring(1);
            }

            if(title!==""){
                vpars += ";title:"+ escape(title);
            }
            if(enablesearch){
                vpars += ";search:true";
            }else{
                vpars += ";search:false";
            }
            if(vpars.length>0){
                vpars = vpars.substring(1);
            }
            if(oppars!==""){
                res += "&oppars=" + oppars;
            }
            if(vpars!==""){
                res += "&vpars=" + vpars;
            }
            if(viewName!==""){
                res += "&vname=" + viewName;
            }

            return res;
        }
        </script>
    </head>
    <body>
        <center >
            <table id="mainTable" style="display:none;text-align: center;padding:0px;margin:0px;overflow:auto;" border="0" cellpadding="0" cellspacing="0" >
            <thead>
                <tr>
					<td align="center" style="background-color:white;">
						<div class="sitebanner">
							<div class="main">
								<a class="logo" href="<?php echo $this->server; ?>" target="_blank" title="Visit AppDB portal"><img src="<?php echo $this->server; ?>/images/appdblogo.png" alt=""/><span>Applications <span>Database</span><br/><span class="subtext">Software solutions for research communities</span></span></a>
								<ul class="contents">
									<li class="software" ><span>Software<br/>Marketplace</span></li>
									<li class="vappliance"><span>Cloud<br/>Marketplace</span></li>
									<li class="researchers"><span>People</span></li>
								</ul>
							</div>
						</div>
                    </td>
                </tr>
            </thead>
            <tbody>
                <tr id="toolbarrow" >
                    <td >
                        <div id="tabs" class="toolbar" style="height:195px;padding-bottom: 0px;border-width:0px;" >
                            <table cellpadding="0" cellspacing="0" style="padding:0px;margin:0px;width:100%">
                                <tr>
                                    <td style="padding-right:10px;margin:0px;">
                                        <ul>
                                                <li><a id="aboutLink" href="#tabAbout">About</a></li>
                                                <li><a id="basicLink" href="#tabBasic">Basic</a></li>
                                                <li><a id="filteringLink" href="#tabFilter">Filtering Criteria</a></li>
                                                <li><a id="generateLink" href="#tabGenerate"> Preview & Generate</a></li>
                                        </ul>
                                    </td>
                                    <td >
                                        <div id="tabAbout"  style="padding-right:20px;">
                                            <h2><p>Welcome to the AppDB Gadget editor</p></h2><br />
                                            <p>This is an online configuration tool, developed to assist the creation of custom instances of the
<a href="http://appdb.egi.eu/" target="_blank" title="Applications Database">Applications Database</a> embeddable Web-Gadget.
Please follow the steps bellow to create a custom instance, or click directly on the <i style="color:#000000">Preview & generate</i> tab on the left of the page, in order to view or use the default instance of the gadget.</p>
<br />
<ul style="padding-left:20px;">
    <li><b>Step 1 : </b>Configure the <i style="color:#000000">basic</i> presentation of the gadget</li>
    <li><b>Step 2 : </b>  (optional) Define the <i style="color:#000000">filtering criteria</i> in order to display a subset of the software registered in the AppDB service.</li>
    <li><b>Step 3 : </b> <i style="color:#000000">Preview & generate</i> your gadget instance with the given configuration</li>
    <li><b>Step 4 : </b> Copy the one-line code generated and paste it into your web page or portal.</li>
</ul>
<br />
<p><i><b>A few words about the gadget: </b></i>The AppDB Web-Gadget is freely offered
to communities, institutions, or even individual scientists, and 
provides data visualization for the 
<a href="https://wiki.egi.eu/wiki/TNA3.4_Technical_Services#Applications_Database" target="_blank" title="EGI wiki page" class="linkButton">AppDB web API </a> result sets,
paging capabilities, and user-defined search operations. The gadget is 
constructed in such a way, as to provide high usability to external web 
portals, and can be configured to display specific information from the 
Applications Database, without any change to the structure of the host site.
</p>
<br />
<p><i><b>Note:</b></i> In case you are interested in deploying the AppDB
Web-Gadget into your portal, you are kindly advised to create a 
communication link with our team by sending an email to <img style="vertical-align: middle" src="http://appdb.egi.eu/gadgets/editor?image=email" alt="email" />. This way, it will be possible for us
to inform you on future enhancements, bug fixes, as well as scheduled downtime 
periods of the AppDB service.</p>
                                        </div>
                                        <div id="tabBasic" >
                                            <div class="tool toolBasic">
                                                <table width="100%"  cellspacing="5" cellpadding="5" style="table-layout: fixed">
                                                    <tr valign="middle" ><td style=" padding-bottom: 10px" colspan="3"><span style="font-size: 7pt">Before filling out the parameters below, please have a look at the notes provided <a style="font-size: 7pt" href="https://wiki.egi.eu/wiki/AppDB_Gadget_Editor#Basic_parameters" target="_blank">here</a></span></td></tr>
                                                    <tr valign="middle" >
                                                        <td style="width:120px;vertical-align: middle;"><label for="viewName">Display type:</label></td>
                                                        <td style="width:250px;" >
                                                            <select id="viewName" style="width:160px;" >
                                                              <option value="simplelist" selected="selected">Simple list</option>
                                                              <option value="list" >Detailed list</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    <tr valign="middle">
                                                        <td ><label for="showSearch" >Display search:</label></td>
                                                        <td style="width:10px"><input id="showSearch" type="checkbox" checked="checked" /></td>
                                                    </tr>
                                                     <tr valign="middle">
                                                         <td >
                                                            <label >Dimensions (W x H):</label>
                                                        </td>
                                                        <td >
                                                            <input id="txtWidth" type="text" value="650" style="width:66px;" />
                                                             <span ><b> X </b></span>
                                                              <input id="txtHeight" type="text" value="500" style="width:66px"/>
                                                              <span>pixels</span>
                                                        </td>
                                                    </tr>
                                                    <tr valign="middle">
                                                        <td ><label for="txtTitle" >Title:</label></td>
                                                        <td colspan="3" >
                                                        <input id="txtTitle" type="text" style="width:500px" value="Applications Database" />
                                                        </td>
                                                    </tr>
                                                    <tr valign="middle">
                                                          <td>
                                                            <label for="txtLength">Items per page:</label>
                                                          </td>
                                                          <td>
                                                            <input id="txtLength" type="text" value="5" style="width:66px"/>
                                                          </td>
                                                      </tr>
                                                      <tr style="visibility: hidden;">
                                                          <td><label for="txtOffset">Page Offset</label></td>
                                                          <td>
                                                              <input id="txtOffset" type="text" value="0" />
                                                          </td>
                                                      </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div id="tabFilter">
                                            <div class="tool toolFilter">
                                                <table width="100%"  cellspacing="5" style="table-layout: fixed">
                                                    <tr>
                                                        <td style="width:80px;vertical-align: middle;"></td>
                                                        <td style="width:250px;"></td>
                                                        <td style="width:117px;vertical-align: middle;"></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr><td colspan="4" style="padding-bottom: 10px"><span style="font-size:7pt">Before selecting the appropriate criteria below, please have a look at the notes provided <a style="font-size: 7pt" href="https://wiki.egi.eu/wiki/AppDB_Gadget_Editor#Filtering_criteria" target="_blank">here</a></span></td></tr>
                                                  <tr>
                                                      <td style="width:80px;vertical-align: middle;" valign="middle">
                                                          <label for="txtName">Name</label>
                                                      </td>
                                                      <td style="width:100%" colspan="3">
                                                          <input id="txtName" type="text" style="width:90%" />
                                                      </td>
                                                  </tr>
                                                  <tr>
                                                      <td valign="middle">
                                                        <label for="txtDescription">Description</label>
                                                      </td>
                                                      <td colspan="3">
                                                        <input id="txtDescription" type="text"  style="width:90%" />
                                                      </td>
                                                  </tr>
                                                  <tr>
                                                      <td valign="middle">
                                                          <label for="txtAbstract">Abstract</label>
                                                      </td>
                                                      <td colspan="3">
                                                        <input id="txtAbstract" type="text" style="width:90%"/>
                                                      </td>
                                                  </tr>
                                                  <tr>
                                                      <td style="vertical-align: middle;" valign="middle">
                                                        <label for="listVos" >VO</label>
                                                      </td>
                                                      <td style="width:250px;">
                                                          <select id="listVos" style="width:245px;" class="searchdroplist">
                                                              <option value="-1"> </option>
                                                              <?php
                                                                foreach($vos as $v){
                                                                    echo "<option value='".$v->attr("id")."'>".$v->attr("name")."</option>";
                                                                }
                                                              ?>
                                                          </select>
                                                      </td>
                                                      <td style="vertical-align: middle;" valign="middle"><label for=listDisciplines" >Discipline</label></td>
                                                      <td>
                                                          <select id="listDisciplines" style="width:245px;" class="searchdroplist">
                                                                <option value="-1"></option>
                                                              <?php
																	$flat_disc = getHierarchyValues($disc);
																	foreach($flat_disc as $v){
																		echo "<option value='".$v["id"]."' data-parentid='".$v["parentid"]."'>".$v["indentvalue"]."</option>";
																	}
                                                                  ?>
                                                          </select>
                                                      </td>
                                                  </tr>
                                                  <tr>
                                                      <td style="vertical-align: middle;" valign="middle">
                                                          <label for=listCountries" >Country</label>
                                                      </td>
                                                      <td style="width:250px;">
                                                          <select id="listCountries" style="width:245px;" class="searchdroplist">
                                                              <option value="-1"> </option>
                                                              <?php
                                                                foreach($countries as $v){
                                                                    echo "<option value='".$v->attr("id")."'>".$v."</option>";
                                                                }
                                                              ?>
                                                          </select>
                                                      </td>
                                                      <td style="vertical-align:middle;" valign="middle">Category</td>
													   <td>
														   <select id="listCategories" style="width:245px;" class="searchdroplist">
															   <option value="-1" selected="selected" />
															   <?php
															   $flat_categories = getHierarchyValues($categories);
															   foreach($flat_categories as $v){
																	echo "<option value='".$v["id"]."' data-parentid='".$v["parentid"]."'>".$v["indentvalue"]."</option>";
																}
															   ?>
														   </select>
													   </td>
                                                  </tr>
                                                   <tr>
                                                       <td style="vertical-align: middle;" valign="middle">Middleware</td>
                                                        <td >
                                                                <select id="listMiddlewares" style="width:245px;" class="searchdroplist">
                                                                        <option value="-1" selected="selected" />
                                                                       <?php
                                                                        foreach($mids as $v){
                                                                            echo "<option value='".$v->attr("id")."'>".$v."</option>";
                                                                        }
                                                                      ?>
                                                                </select>
                                                        </td>
														<td style="vertical-align: middle;" valign="middle">Tags</td>
                                                        <td >
                                                                <select id="listTags" style="width:245px;" class="searchdroplist">
                                                                        <option value="-1" selected="selected" />
                                                                       <?php
                                                                        foreach($tags as $v){
                                                                            echo "<option value='".$v."'>".$v."</option>";
                                                                        }
                                                                      ?>
                                                                </select>
                                                        </td>
                                                   </tr>
												   <tr>
													   
												   </tr>
                                                </table>
                                            </div>
                                        </div>
                                        <div id="tabGenerate" style="padding:0px;">
                                            <div>
                                            <table cellpadding="0" cellspacing="0" style="width:100%;">
                                                <tr>
                                                    <td>
                                                        <span style="padding-bottom: 1px;">Copy the HTML code bellow and paste it into your page (more details can be found <a href="https://wiki.egi.eu/wiki/AppDB_Gadget_Editor#Integration" target="_blank">here</a>)</span>
                                                    </td>
                                                    <td style="float:right;text-align: right;">
                                                        <div id="loader" >Loading...</div>
                                                        <div id="timer" ></div>
                                                    </td>
                                                </tr>
                                            </table>
                                            </div>
                                            <hr />
                                            <div class="tool toolMisc">
                                                <textarea id="frameTag" cols="100" rows="5" style="width:98%;" ></textarea>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                    </div>
                    </td>
                </tr>
                <tr >
                    <td >
                        <center>
                        <div id="container">
                            
                                <div id="preview">
                                    <iframe id="frameObj" style="visibility:hidden;" width="700px" height="500px" src="" frameborder="1" ></iframe>
                                </div>
                            
                        </div>
                        </center>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        <div id="footer" >
                            <center><a target="_blank" href="http://www.iasa.gr/" style="text-decoration: none;">&copy; Institute of Accelerating Systems and Applications</a>, 2009-<?php echo date("Y");?>, Athens, Greece</center>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
            </center>
    </body>
</html>
<?php } ?>
