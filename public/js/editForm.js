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
function imposeMaxLength(Object, MaxLen)
{
  return (Object.value.length <= MaxLen);
}

function trimAll(sString) {
	if (sString === null) sString = '';
	while (sString.substring(0,1) === ' ') {
		sString = sString.substring(1, sString.length);
	}
	while (sString.substring(sString.length-1, sString.length) === ' ') {
		sString = sString.substring(0,sString.length-1);
	}
	return sString;
}

function editForm(formName) {

	function findIndex(edit_name) {
		var edit_index=0;
		$("#" + formName + " *[name^='" + edit_name + "']").each(function(x) {
			var i = $(this).attr('name').substring(edit_name.length);
			if ( Number(i) >= Number(edit_index) ) edit_index = Number(i)+1;
		});
		return edit_index;
	}
	
	function editElement(el) {
		var disabled=( ( eval(el.attr('edit_disabled')) )?' disabled="disabled"' : '');
		if(eval(el.attr('edit_isempty'))) el.html('');
		switch (el.attr('edit_type')) {
			case 'checkbox':
				var edit_name=el.attr('edit_name');
				if ( eval(el.attr('edit_checked')) ) var checked=' checked="checked"'; else var checked='';
				el.html('<input name="'+edit_name+'" type="checkbox" dojoType="dijit.form.CheckBox" value="true"'+checked+disabled+'/>');
				break;
			case 'image':
				break;
			case 'hidden':
				el.html('<input style="display:none" name="'+el.attr('edit_name')+'" type="text" dojoType="dijit.form.TextBox" value="'+el.html()+'"/>');
				break;
			case 'link':
				var edit_name=el.attr('edit_name');
				if ( eval(el.attr('edit_group')) ) edit_name = edit_name + findIndex(edit_name);
				el.html('<span>'+el.text()+':</span> <input name="'+edit_name+'" type="text" dojoType="dijit.form.TextBox" value="'+el.find('a').attr('href')+'"/>');
				break;
			case 'combolink':
				if ( eval(el.attr('edit_combo_free')) ) var comboType='ComboBox'; else var comboType='FilteringSelect';
				var selected="";
				var edit_name=el.attr('edit_name');
				if ( eval(el.attr('edit_group')) ) edit_name = edit_name + findIndex(edit_name);
				var edit_name2='combo_'+edit_name;
				if ( (el.attr('edit_onchange') != '') && (el.attr('edit_onchange') !== undefined) ) var changeEvent='onkeypress="'+el.attr('edit_onchange')+'" onchange="'+el.attr('edit_onchange')+'" '; else var changeEvent='';
				var combo='<select name="'+edit_name2+'" '+changeEvent+'dojoType="dijit.form.'+comboType+'" autocomplete="true">';
				var foundText=false;
				var dat=eval("("+el.attr('edit_data')+")");
				if (dat !== undefined) {
					ids=dat.ids;
					vals=dat.vals;
					for (var i=0; i<ids.length; i=i+1) {
						if ( trimAll(vals[i]) === trimAll(el.text()) ) {
							foundText=true;
							selected='selected="selected"';
						} else {
							selected='';
						}
						if (comboType === 'ComboBox') 
							combo+='<option '+selected+' value="'+vals[i]+'">'+vals[i]+'</option>';
						else
							combo+='<option '+selected+' value="'+ids[i]+'">'+vals[i]+'</option>';
					}
				}
                if (comboType==='ComboBox') if (!foundText) if (el.text()!=='') combo+='<option selected="selected" value="'+el.text()+'">'+el.text()+'</option>';
				combo+='</select>';
				el.html('<span>'+combo+':</span> <input name="'+edit_name+'" type="text" dojoType="dijit.form.TextBox" value="'+el.find('a').attr('href')+'"/>');
				break;
			case 'text':
				var watermark  = ( el.attr('edit_watermark'))?" placeHolder='" + el.attr("edit_watermark") + "' ":"";
				var maxlength = ( ( el.attr('edit_maxlength') != "" )?' maxlength="'+el.attr('edit_maxlength')+'"' : '' );
				var required = ( ( el.attr('edit_required') == "true" )?' required="true" ' : '' );
				var edit_name=el.attr('edit_name');
				var controlType = ( ( required == '')?"dijit.form.TextBox":"dijit.form.ValidationTextBox" );
				if ( eval(el.attr('edit_group')) ) edit_name = edit_name + findIndex(edit_name);
				var html=el.html();
				if ( el.find('a').text() != '' ) html = el.find('a').text();
				el.html('<input name="'+edit_name+'" type="text" dojoType="'+ controlType +'"'+maxlength+' ' + required + watermark + ' value="'+html+'"/>');
				break;
			case 'textarea':
				if ( el.attr('edit_maxlength') != "" ) var maxlength = ' onkeypress="return imposeMaxLength(this, '+el.attr('edit_maxlength')+');"'; else var maxlength = '';
				el.html('<textarea name="'+el.attr('edit_name')+'" rows="10" dojoType="dijit.form.SimpleTextarea"'+ maxlength +' style="width:99%;max-width:950px;">'+el.html()+'</textarea>');
				break;
			case 'combo':
				var watermark  = ( el.attr('edit_watermark'))?" placeHolder='" + el.attr("edit_watermark") + "' ":"";
				var comboType = ( ( eval(el.attr('edit_combo_free')) )?'ComboBox':'FilteringSelect');
				var selected = "";
				var edit_name = el.attr('edit_name');
				edit_name += (( eval(el.attr('edit_group')) )?findIndex(edit_name):'');
				var changeEvent = ( (el.attr('edit_onchange') != '') && (el.attr('edit_onchange') !== undefined) )?'onkeypress="'+el.attr('edit_onchange')+'" onchange="'+el.attr('edit_onchange')+'" ':'';
				var s='<select name="'+edit_name+'" '+changeEvent+'dojoType="dijit.form.'+comboType+'" autocomplete="true" ' + watermark;
				var o = ''; //options
				var dat = eval("("+el.attr('edit_data')+")");
				ids=dat.ids;
				vals=dat.vals; 
				var foundText=false;
				var defval = trimAll(el.text());
				if(defval=='' && (dat.selected || dat.selected==null)){
					defval = trimAll(getDefaultDataText(dat));
				}
				for (var i=0; i<ids.length; i=i+1) {
					if ( (el.attr("data-selected") == '' && trimAll(vals[i]) == defval) || ( el.attr("data-selected") != '' && ids[i] == el.attr("data-selected") ) ) {
						foundText=true;
						selected='selected="selected"';
					} else {
						selected='';
					}
					if ( comboType === 'ComboBox' )
						o+='<option '+selected+' value="'+vals[i]+'">'+vals[i]+'</option>';
					else
						o+='<option '+selected+' value="'+ids[i]+'">'+vals[i]+'</option>';
				}
                if ( comboType === 'ComboBox' && foundText == false && el.text() != ''){
					foundText = true;
					s+='displayedValue="' + el.text()+ '" ';
					o+='<option selected="selected" value="'+el.text()+'">'+el.text()+'</option>';
				}
				if( foundText == false && (defval!='' || defval==null)) {
					s+='displayedValue="' + ((defval==null)?'':defval) + '" ';
				}
				s = s +">"+ o + '</select>';
				el.html(s);
				break;
		}
		el.removeClass('editable');
        el.on("click", function(){
            focusedDijitItem = el;
        });
		if ( el.attr('edit_style') !== undefined ) {el.find("*:first").css(eval("("+el.attr('edit_style')+")"));};
        try {
    		dojo.parser.parse(el[0]);
        } catch(err) {
        };
	}	
	var f=$('#'+formName)[0];
	var jb;
	
	var getDefaultDataText = function(d){
		var found = false;
		if(d.selected==null){
			return null;
		}else if(typeof d.selected === "string"){
			return d.selected;
		}else if(d.selected.id){
			for(var i =0; i<d.ids.length; i+=1){
				if(d.ids[i] == d.selected.id){
					found = i;
					break;
				}
			}
			if(found === false){
				return (d.vals.length>0)?d.vals[0]:'';
			}
			return d.vals[found];
		}else if(d.selected.text){
			return d.selected.text;
		}
		return (d.vals.length>0)?d.vals[0]:'';
	};
	var onContactsChanged = function(contacts,app){
		if( contacts.added.length === 0 ) return;
		
		var apptitle = (appdb.pages.application.isVirtualAppliance())?"Virtual Appliance ":"Software ";
		apptitle += (app && app.application && app.application.name)?app.application.name:"item";
		var opts = {
			title: "Contact list has changed",
			message: "It seems that new contacts have been added for " + apptitle + ".<br/> By default, newly added contacts are privileged to edit the information and publication section of this item.<br/><br/> You can modify their privileges by clicking the <b>edit permissions</b> button below.",
			action: "Edit permissions",
			callback: function(action){
				if( $.trim(action).toLowerCase().replace(/\s/g,"")==="editpermissions" ){
					appdb.pages.application.selectSection("permissions", true);
				}
			}
		};
		(function(dialogopts){
			var timer = null;
			var tries = 50;
			var retry = function(){
				tries -= 1;
				timer = setTimeout(function(){
					check();
				},200);
			};
			var check = function(){
				if( timer !== null ){
					clearTimeout(timer);
				}
				if( $.trim($("#details").hasClass("loading")) === true && tries > 0){
					retry();
				}else{
					appdb.utils.ShowNotificationDialog(dialogopts);
					$("body .notifydialog a.action.editpermissions").addClass("btn-primary");
				}
			};
			retry();
		})(opts);
	};
	var onSaveApplication = function(frmelem){
		//No need for the application to be watched for changes.
		//Disable the current data watcher which watches the editing application
		appdb.utils.DataWatcher.Registry.deactivate(appdb.utils.DataWatcher.Registry.getActiveName());
		//The application data collection resides in 'input' tags of the form. 
		//Due to portal transition to more managed data handling the Scientific contacts, 
		//application urls and application categories data are handled by javascript views(namespace: appdb.views).
		//To be compatible with the previous data collection these views provide the 'setupForm' method which
		//creates and appends appropriate input tags in the form. Then the Application Mapper type is used to 
		//collect the updated or newly inserted data in order to produce the xml representation of the application.
		if(managedSciCons && managedSciCons != null){
			managedSciCons.setupForm(frmelem);
		}
		if(managedAppUrlsEditor && managedAppUrlsEditor != null){
			managedAppUrlsEditor.setupForm(frmelem);
		}
		if(managedAppCategoriesEditor && managedAppCategoriesEditor != null){
			managedAppCategoriesEditor.setupForm(frmelem);
		}
		if(managedDisciplinesEditor && managedDisciplinesEditor != null){
			managedDisciplinesEditor.setupForm(frmelem);
		}
		if( appdb.pages.application.currentSoftwareLicenses() ){
			appdb.pages.application.currentSoftwareLicenses().setupForm(frmelem);
		}
		if( appdb.pages.application.currentOrganizationRelationList() ){
			appdb.pages.application.currentOrganizationRelationList().setupForm(frmelem);
		}
		if( appdb.pages.application.currentProjectRelationList() ){
			appdb.pages.application.currentProjectRelationList().setupForm(frmelem);
		}
		if( appdb.pages.application.currentSoftwareRelationList() ){
			appdb.pages.application.currentSoftwareRelationList().setupForm(frmelem);
		}		
		if( appdb.pages.application.currentVapplianceRelationList() ){
			appdb.pages.application.currentVapplianceRelationList().setupForm(frmelem);
		}
		if( appdb.pages.application.currentExternalRelationList() ){
			appdb.pages.application.currentExternalRelationList().setupForm(frmelem);
		}
		
		//Create an application mapper and retrieved an application entity 
		//with the newly created or updated values.
		var mapper = new appdb.utils.EntityEditMapper.Application();
		mapper.UpdateEntity(frmelem);
		//Create an XML representation of the application entity by using
		//the EntitySerializer object and passing to it the application entity
		var xml = appdb.utils.EntitySerializer.excludeElements(["tag"]).toXml(mapper.entity);
		appdb.debug("Sending :",xml);
		
		jb.hide();
		showAjaxLoading(undefined,"Saving...");
		
		var categorystats = (managedAppCategoriesEditor && managedAppCategoriesEditor.options && managedAppCategoriesEditor.options.data )?managedAppCategoriesEditor.options.data:null;
		//Create a model to send the update/insert request to the server
		//Register the event handlers upon success or failure of the request
		var appModel = new appdb.model.Application();
		var cont = "software";
		var contname = "Software";
		var contfunc = appdb.views.Main.showApplication;
		if( appdb.pages.application.isVirtualAppliance() ){
			cont = "vappliance";
			contname = "Virtual Appliance";
			contfunc = appdb.views.Main.showVirtualAppliance;
		}else if( appdb.pages.application.isSoftwareAppliance() ){
			cont = "swappliance";
			contname = "Software Appliance";
			contfunc = appdb.views.Main.showSoftwareAppliance;
		}
		appModel.subscribe({event: "update", callback:function(d){
				//Called upon success of an application update
				hideAjaxLoading();
				if( d && d.error ){
					contfunc({id:mapper.entity.id(), name: d.application.name, cname: d.application.cname},{ content: cont });
				}else{
					contfunc({id:mapper.entity.id(), name: d.application.name, cname: d.application.cname},{ content: cont },d);
				}
				
				if(managedSciCons && managedSciCons !== null){
					onContactsChanged(managedSciCons.deltaContacts(),d);
				}
				if( categorystats ){
					appdb.pages.index.navigationList().update(categorystats);
				}
			}}).subscribe({event: "insert", callback:function(d){
				//Called upon success of a newly application registration
				hideAjaxLoading();
				if(d && d.application){
					contfunc({id: d.application.id, name:d.application.name, cname: d.application.cname}, { content: cont},d);
				}else if(d && d.error){
					(new appdb.views.ErrorHandler()).handle({
						"status": "Cannot save " + mapper.entity.name(), 
						"description": d.error
					});
					//appdb.views.Main.showApplication({id: 0},{mainTitle: 'Register New ' + ((cont==="vappliance")?"Virtual Appliance":"Software"), content: cont});
				}
			}}).subscribe({event: "error", callback: function(d){
				//Called upon ajax error of both application update or insert action. E.g. HTTP 500 - Internal Server Error
				hideAjaxLoading();
				var isInsertAction = (mapper.entity.id())?false:true;
				var err = {
					"status": "Cannot " + ((isInsertAction===true)?"register new":"update") + " " + mapper.entity.name(),
					"description": d.description
				};
				appdb.utils.RestApiErrorHandler(d, err, mapper.entity.name() + contname.toLowerCase() ).show();
				//Init application callback data if the error occurred upon registration or update action of an application
				var appData = {query:{},ext:{}};
				if(d.response && d.response.errornum=="2"){
					appdb.views.Main.closeCurrentView();
				}else{
					if(isInsertAction == true){
						appData.query.id = 0;
						appData.ext.mainTitle = 'Register New ' + contname;
						appData.ext.content = cont;
					}else{
						appData.query.id = mapper.entity.id();
						appData.query.name = mapper.entity.name();
					}
					//Set portal to render application
					//appdb.views.Main.showApplication(appData.query,appData.ext);
					contfunc(appData.query,appData.ext);
				}
				
			}});
		//Use the appr$('#'+formName)[0]opriate action to send the request
		//When registering a new application the entity will have id=0
		//else the user is updating an existing application.
		if(mapper.entity.id()=='0' || !mapper.entity.id() ){
			appModel.insert({query: {}, data: {data: xml}});	
		}else{
			appModel.update({query: {}, data: {data: encodeURIComponent(xml)}});	
		}
	};
	var onSavePerson = function(frmelem){
		//Create an application mapper and retrieved an application entity 
		//with the newly created or updated values.
		var mapper = new appdb.utils.EntityEditMapper.Person();
		
		if( appdb.pages.Person.currentOrganizationRelationList() ){
			appdb.pages.Person.currentOrganizationRelationList().setupForm(frmelem);
		}
		if( appdb.pages.Person.currentProjectRelationList() ){
			appdb.pages.Person.currentProjectRelationList().setupForm(frmelem);
		}
		
		mapper.UpdateEntity(frmelem);
		//Create an XML representation of the application entity by using
		//the EntitySerializer object and passing to it the application entity
		var xml = appdb.utils.EntitySerializer.excludeElements(["application"]).toXml(mapper.entity);
		//No need for the application to be watched for changes.
		//Disable the current data watcher which watches the editing application
		//appdb.utils.DataWatcher.Registry.deactivate(appdb.utils.DataWatcher.Registry.getActiveName());
		appdb.debug("Sending :",xml);
		jb.hide();
		showAjaxLoading(undefined,"Saving...");

		//Create a model to send the update/insert request to the server
		//Register the event handlers upon success or failure of the request
		var personModel = new appdb.model.Person();
		personModel.subscribe({event: "update", callback:function(d){
				if(typeof d === "undefined") { return; }
				//Called upon success of an application update
				appdb.utils.DataWatcher.Registry.deactivate(appdb.utils.DataWatcher.Registry.getActiveName());
				hideAjaxLoading();
				appdb.views.Main.showPerson({id:mapper.entity.id(), cname: d.cname},{mainTitle: mapper.entity.firstname() + " " + mapper.entity.lastname()});
			}}).subscribe({event: "insert", callback:function(d){
				if(typeof d === "undefined") { return; }
				//Called upon success of a newly application registration
				appdb.utils.DataWatcher.Registry.deactivate(appdb.utils.DataWatcher.Registry.getActiveName());
				hideAjaxLoading();
				if(d && d.id){
					appdb.views.Main.showPerson({"id": d.id, "cname":d.cname, "userid":userID},{"mainTitle": d.firstname + " " + d.lastname});
				}else if(d && d.error){
					(new appdb.views.ErrorHandler()).handle({
						"status": "Cannot save " + mapper.entity.firstname() + " " + mapper.entity.lastname() + " profile", 
						"description": d.error
					});
					appdb.views.Main.showPerson({"id": 0}, {"mainTitle": 'Register New User'});
				}
			}}).subscribe({event: "error", callback: function(d){
				//Called upon ajax error of both application update or insert action. E.g. HTTP 500 - Internal Server Error
				hideAjaxLoading();
				var isInsertAction = (mapper.entity.id())?false:true;
				var err = {
					"status": "Cannot " + ((isInsertAction===true)?"register new":"update") + " " + mapper.entity.firstname() + " " + mapper.entity.lastname() + " profile",
					"description": d.description
				};
				//Setup error according to rest response type
				appdb.utils.RestApiErrorHandler(d, err, mapper.entity.firstname() + " " + mapper.entity.lastname() + " profile").show();
				//Init application callback data if the error occurred upon registration or update action of an application
				//show save button again
				jb.show();
				setTimeout(function(){
					$("#details").show();
				},500);
			}});
		//Use the appr$('#'+formName)[0]opriate action to send the request
		//When registering a new application the entity will have id=0
		//else the user is updating an existing application.
		if(mapper.entity.id()==0){
			personModel.insert({query: {}, data: {data: xml}});	
		}else{
			personModel.update({query: {}, data: {data: encodeURI(xml)}});	
		}
	};
	//Handles the save action. Validates data if there is a validation callback
	//and calls appropriate functions according to the editing type (application or person)
	var onSave = function () {
		if( (f.getAttribute("onvalidate") != '') && ($('#'+formName)[0].getAttribute("onvalidate") !== null) ) {
			var validatorName = $.trim(f.getAttribute("onvalidate")).replace(/\(\)\;{0,1}/, '');
			var validatorDelegate = window[validatorName] || function(cb) {cb(false);};
			validatorDelegate(function(result) {
			    if ( result ) {
				    if(formName.indexOf('editapp')>-1){
					    onSaveApplication($('#'+formName)[0]);
				    } else if( formName.indexOf('editperson')>-1){
					    onSavePerson($('#'+formName)[0]);
				    }
			    }
			});
		}
	};

	if ( typeof f !== "undefined" ) {
		if ($('#'+formName).find('*[name=save]')[0] === undefined) {
			if ( $("#savedetails").length == 0 ) {
				var jbc = $('<a id="cancelsavedetails" style="vertical-align: middle; padding-right: 5px;" href="#"><img height="16px" style="vertical-align:middle; padding-right: 3px" src="/images/stop.png" border="0"/>Cancel</a>');
				jb = $('<a id="savedetails" style="vertical-align: middle; padding-right: 5px;" href="#"><img height="16px" style="vertical-align:middle; padding-right: 3px" src="/images/diskette.gif" border="0"/>Save</a>');
				jb.attr("align","right");
				jb.attr("callback",f.callback);
				jb.attr("callback_data",f.callback_data);
				jbc.prependTo($("#toolbarContainer div:first"));
				jb.prependTo($("#toolbarContainer div:first"));
				jb.on("click", onSave);
				jbc.on("click", function(){
					eval(f.getAttribute("cancelcallback"));
				});
			}
		};
		$('#'+formName+' .editable').each(function(i,e){
			editElement($(this));
		});
		
		separateMultipleItems($("#urldiv"+dialogCount).find("span.app-url"));
		separateMultipleItems($("#vodiv"+dialogCount).find("span.app-vo"));
		separateMultipleItems($("#mwdiv"+dialogCount).find("span.app-mw"));
		separateMultipleItems($("#countryDiv"+dialogCount).find("span.app-countryFlag"));

		var anv = $("#appnamevalidator");
		var appname = $($("#"+formName).find("input[name='name']")[0]);
		if(anv.length>0){
			$(anv).css("display","inline-block");
			var na = (new appdb.components.NameAvailability({container : anv,input:appname,appid:$("#appid"+dialogCount).text()}));
            na.subscribe({event : "valid",callback:function(v){
                $("#savedetails").show();
            }});
            na.subscribe({event : "invalid",callback: function(v){
                $("#savedetails").hide();
            }});
		}
		$("span.mandatoryflag").css("display","inline-block");
	};
}
