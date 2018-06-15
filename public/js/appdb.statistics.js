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
appdb.statistics = {};
appdb.statistics.charts = {};
appdb.statistics.software = {};
appdb.statistics.vappliance = {};
appdb.statistics.data = {};
appdb.statistics.data.software = {};
appdb.statistics.data.vappliance = {};
appdb.statistics.people = {};
appdb.statistics.TreeNavigationBar = appdb.ExtendClass(appdb.View, "appdb.statistics.TreeNavigationBar", function(o){
	this.options = {
		parent: o.parent || null,
		container: $(o.container),
		entries: o.entries || [],
		useSeperator: (typeof o.useSeperator === "boolean")?o.useSeperator:true,
		dom: {
			container: null,
			list: null
		}
	};
	this.addEntry = function(d){
		var li = $(document.createElement("li"));
		var dom = $(document.createElement("div")).addClass("entry");
		var a = $(document.createElement("a")).attr("href","#").attr("title", "Search for items");
		if( d.isClickable ){
			$(li).addClass("clickable");
		}
		$(a).off("click").on("click", (function(self,data){
			return function(ev){
				ev.preventDefault();
				if( data.isClickable ){
					self.publish({event: "click", value: data});
				}
				return false;
			};
		})(this,d));
		$(a).append(d.display);
		$(dom).append(a);
		$(li).append(dom);
		return li;
	};
	this.addSeperator = function(){
		var li = $(document.createElement("li"));
		var dom = $(document.createElement("div")).addClass("seperator");
		$(dom).append("<span class='arrow'>▶</span>");
		$(li).append(dom);
		return li;
	};
	this.render = function(d){
		this.initContainer();
		d = d || this.options.entries || [];
		d = $.isArray(d)?d:[d];
		$.each(this.options.entries, (function(self){
			return function(i,e){
				$(self.options.dom.list).append(self.addEntry(e));
				if( self.options.useSeperator === true &&  i< self.options.entries.length-1){
					$(self.options.dom.list).append(self.addSeperator(e));
				}
			};
		})(this));
	};
	this.initContainer = function(){
		$(this.dom).empty();
		this.options.dom.container = $(document.createElement("div")).addClass("treenavigationbar");
		this.options.dom.list = $(document.createElement("ul"));
		$(this.options.dom.container).append(this.options.dom.list);
		$(this.dom).append(this.options.dom.container);
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
	};
});
appdb.statistics.DataProvider = appdb.ExtendClass(appdb.View, "appdb.statistics.DataProvider", function(o){
	this.options = {
		filter: "",
		currentData: null
	};
	this.getData = function(){
		return this.options.currentData;
	};
	this.transformData = function(d){
		return d;
	};
	this.load = function(filter){
		filter = filter || this.options.filter;
		if( this.options.initData !== null || ($.isArray(this.options.initData) && this.options.initData.length > 0 ) ){
			this.options.currentData = this.transformData(this.options.initData);
			delete this.options.initData;
			this.options.initData = null;
			this.publish({event: "select", value: this.options.currentData});
			return;
		}
		if( !this.options.modelType && !this.model ){
			return;
		}
		if( !this.model ){
			this.model = new this.options.modelType();
		}
		this.model.unsubscribeAll();
		this.model.subscribe({event: "beforeselect", callback:function(v){
			this.publish({event: "beforeselect", value:true});
		}, caller: this});
		this.model.subscribe({event: "select", callback: function(v){
			this.options.currentData = this.transformData(v);
			this.publish({event: "select", value: this.options.currentData});
		}, caller:this});
		this.model.get({flt:filter});
	};
});
appdb.statistics.data.software.Category = appdb.ExtendClass(appdb.statistics.DataProvider,"appdb.statistics.data.software.Category", function(o){
	o = o || {};
	this.options = $.extend(true, this.options,{
		entityType: "software",
		groupType: "category",
		filter: o.filter || "+*&application.metatype:0",
		initData: appdb.model.StaticList.SoftwareLogistics,
		modelType: appdb.model.SoftwareLogistics
	});
	this.transformData = function(d){
		if( !d.category ) return [];
		var data = $.isArray(d.category)?d.category:[d.category];
		var fdata = $.grep(data, function(e){
			return ($.trim(e.id) !== "34" && $.trim(e.parentid)!=="34");
		});
		var cdv = new appdb.views.CategoriesDataView();
		cdv.load();
		var res =  appdb.utils.MergeTreeLogistics(cdv, fdata, true);
		res = $.grep(res, function(e){
			return ( $.trim(e.text).toLowerCase() !== 'software appliances' );
		});
		return res;
	};
});
appdb.statistics.data.software.Discipline = appdb.ExtendClass(appdb.statistics.DataProvider,"appdb.statistics.data.software.Discipline", function(o){
	o = o || {};
	this.options = $.extend(true, this.options,{
		entityType: "software",
		groupType: "discipline",
		filter: o.filter || "+*&application.metatype:0",
		initData: null,
		modelType: appdb.model.SoftwareLogistics
	});
	this.transformData = function(d){
		var data = [];
		if( d && d.logistics && d.logistics.discipline ) data = d.logistics.discipline;
		if( d && d.discipline ) data = d.discipline;
		data = data || [];
		data = $.isArray(data)?data:[data];
		if( data.length === 0 ) return [];
		var cdv = new appdb.views.DisciplinesDataView();
		cdv.load();
		var res =  appdb.utils.MergeTreeLogistics(cdv, data, true);
		return res;
	};
});
appdb.statistics.data.vappliance.Category = appdb.ExtendClass(appdb.statistics.DataProvider,"appdb.statistics.data.vappliance.Category", function(o){
	o = o || {};
	this.options = $.extend(true, this.options,{
		entityType: "vappliance",
		groupType: "category",
		filter: o.filter || "+*&application.metatype:1",
		initData: appdb.model.StaticList.SoftwareLogistics,
		modelType: appdb.model.SoftwareLogistics
	});
	this.transformData = function(d){
		if( !d.category ) return [];
		var data = $.isArray(d.category)?d.category:[d.category];
		var cdv = new appdb.views.CategoriesDataView();
		cdv.load();
		var fdata = [];
		$.each(cdv.options.data, function(i, e){
			if( $.trim(e.id) === "34" || $.trim(e.parentid)==="34" ){
				$.each(data, function(ii,ee){
					if($.trim(e.id) === $.trim(ee.id)){
						fdata.push(ee);
					}
				});
			}
		});
		var res =  appdb.utils.MergeTreeLogistics(cdv, fdata, true);
		res = $.isArray(res)?res:[];
		if(res.length === 0 || !res[0].children) return [];
		res[0].children = $.isArray(res[0].children)?res[0].children:[res[0].children];
		var result = [];
		$.each(res[0].children, function(i,e){
			if( parseInt(e.id) >= 0 ){
				result.push(e);
			}
		});
		return result;
	};
});
appdb.statistics.data.vappliance.Discipline = appdb.ExtendClass(appdb.statistics.DataProvider,"appdb.statistics.data.vappliance.Discipline", function(o){
	o = o || {};
	this.options = $.extend(true, this.options,{
		entityType: "vappliance",
		groupType: "discipline",
		filter: o.filter || "+*&application.metatype:1",
		initData: null,
		modelType: appdb.model.SoftwareLogistics
	});
	this.transformData = function(d){
		var data = [];
		if( d && d.logistics && d.logistics.discipline ) data = d.logistics.discipline;
		if( d && d.discipline ) data = d.discipline;
		data = data || [];
		data = $.isArray(data)?data:[data];
		if( data.length === 0 ) return [];
		var cdv = new appdb.views.DisciplinesDataView();
		cdv.load();
		var res =  appdb.utils.MergeTreeLogistics(cdv, data, true);
		return res;
	};
});
appdb.statistics.GetStatisticsModel = function(o){
	var options = {
		entityType: $.trim(o.entityType).toLowerCase() || "software",
		groupType: $.trim(o.groupType).toLowerCase() || "category",
		flt: o.flt || ""
	};
	if( options.groupType.length > 0 ){
		options.groupType = options.groupType.replace(/\b[a-z]/,function(letter){return letter.toUpperCase();});
	}
	var obj = appdb.FindNS("appdb.statistics.data." + options.entityType + "." + options.groupType);
	if( !obj ){
		return false;
	}
	return obj;
	
};
appdb.statistics.GetContainer = function(o){
    
};
appdb.statistics.GetChart = function(o){
	o = o || {};
	var options  = {
		container: o.container,
		entityType: o.entityType || "software",
		groupType: o.groupType || "",
		chartType: o.chartType || "appdb.statistics.charts.Sunburst",
		model: o.model || null
	};
	
	var chartType = appdb.FindNS(options.chartType);
	if(!chartType){
		options.chartType = options.chartType.replace(/\b[a-z]/,function(letter){return letter.toUpperCase();});
		chartType = appdb.FindNS("appdb.statistics.charts." + options.chartType);
	}
	if( !chartType ){
		return false;
	}
	
	if( !options.data ){
		options.modelType = appdb.statistics.GetStatisticsModel(options);
	}
	var chart = new chartType(options);
	return chart;
};
appdb.statistics.GenericContainer = appdb.ExtendClass(appdb.View, "appdb.statistics.GenericContainer", function(o){
   this.options = {
            container: o.container,
            entityType: o.entityType || "",
            groupType: o.groupType || "",
            chartType: o.chartType || "",
            model: o.model || null,
			extdata: null,
			csv: {
				
			}
    };
	this.getExtData = function(){
		return this.options.extdata;
	};
	this.getTotalCount = function(){
		return 0;
	};
	this.setupEmptyItems = function(){
		if( this.getExtData() && this.getExtData().emptyCount > 0 ){
			$(this.dom).find(".emptyitemswarning").removeClass("hidden");
			$(this.dom).find(".emptyitemswarning .entitytype").text(this.options.groupType);
			$(this.dom).find(".emptyitemswarning a").off("click").on("click", (function(self){
				return function(ev){
					ev.preventDefault();
					self.viewEmptyItems();
					return false;
				};
			})(this));
		}
	};
	this.viewEmptyItems = function(){
		appdb.views.Main.showApplications({flt:'=&'+this.options.groupType+'.name:"NULL"'},{isBaseQuery:true,mainTitle: 'without ' + this.options.groupType});
	};
    this.getChartType = function(chartType){
        chartType =chartType || "pie";
        switch($.trim(chartType).toLowerCase()){
            case "pie":
                return appdb.statistics.GetChart({
						parent: this,
						container: $(this.dom).find(".chartcontainer:first"),
                        entityType: this.options.entityType,
                        groupType: this.options.groupType,
                        chartType: "sunburst"
                });
                break;
            case "bars":
                return appdb.statistics.GetChart({
						parent: this,
						container: $(this.dom).find(".chartcontainer:first"),
						entityType: this.options.entityType,
                        groupType: this.options.groupType,
                        chartType: "partition"
                });
                break;
        }
        return;
    };
    this.buildExportImageForm = function(){
      var chartobject = "app";
      switch($.trim(this.options.entityType.toLowerCase())){
          case "people":
              chartobject = "ppl";
              break;
          case "software":
          default:
              break;
                  
      }
      var form = '<form id="exportimage" name="exportimage" method="post" action="/'+chartobject+'stats/exportimage">' +
	'<input type="text" style="display: none;" id="imgtype" name="type"/>' +
	'<input type="text" style="display: none;" id="svgdata" name="svgdata"/>' +
        '</form>';
        $(this.dom).append(form);
    };
    this.buildExportDataForm = function(){
        var chartobject = "app";
        switch($.trim(this.options.entityType.toLowerCase())){
            case "people":
                chartobject = "ppl";
                break;
            case "software":
            default:
                break;

        }
        var form = '<form id="exportdata" name="exportdata" method="post" action="/'+chartobject+'stats/exportobjectdata">'+
			'<input type="text" style="display: none;" id="datatype" name="type"/>'+
			'<input type="text" style="display: none;" id="entitytype" name="entitytype"/>'+
			'<input type="text" style="display: none;" id="data" name="data" />'+
			'<input type="text" style="display: none;" id="labels" name="labels" />'+
        '</form>';
        $(this.dom).append(form);
    };
    this.exportImageAs = function(exportType){
		var html = $(this.dom).find(".chartcontainer").clone();
		$(html).find("image.zoomimage").remove();
		if( $.browser.msie ){
			var h = $(this.dom).find(".chartcontainer").clone();
			$(h).children("div:first").remove();
			$(h).find("image.zoomimage").remove();
			$(h).find("svg:first").attr("style","display:block");
			$(h).find("text").css({
				"display":"block",
				"color":"black",
				"font-family": "Arial",
				"font-style": "normal",
				"font-variant": "normal",
				"font-weight": "normal"
			}).each(function(i,e){
				$(this).text($(this).attr("text"));
			});
			html = $(h).find("svg:first")[0].outerHTML;
			html = html.replace(/(id|class)=([0-9A-Za-z_.-]+)/g,'$1="$2"');
		}else{
			html = $(html).html();
		}
		$("#svgdata").val(html);
        $("#imgtype").val($.trim(exportType).toLowerCase());
		this.closeExport();
        $("#exportimage").submit();
    };
	this.makeDataExportable  = function(d){
		d = d  || [];
		d = $.isArray(d)?d:[d];
		var res = [];
		var parseChild = function(c){
			var item = {};
			item.id = c.id || -1;
			item.text = c.text || "";
			item.text = item.text.replace(/[\,\;\-]/g," ");
			if( c.parent && (c.parent.id<<0) !== -1 && (c.parent.id<<0) !== 0){
				item.parent = {text:c.parent.text||"<none>"};
				item.parent.text = item.parent.text.replace(/[\,\;\-]/g," ");
			}
			item.count = c.count || "0";
			if( c.children ){
				c.children = $.isArray(c.children)?c.children:[c.children];
				item.children = [];
				$.each(c.children, function(ii,ee){
					if( (ee.id<<0) !== -1 ){
						item.children.push(parseChild(ee));
					}
				});
			}
			return item;
		};
		$.each(d, function(i,e){
			if((e.id<<0) !== -1){
				res.push(parseChild(e));
			}
			
		});
		
		return res;
	};
    this.exportData = function(chartData,seriesLabels){
		var data = this.makeDataExportable(this.options.chartObj.options.model.getData());
		var txtData = JSON.stringify(data);
        $("#data").val(txtData);
        $("#labels").val(seriesLabels||"");
        $("#datatype").val("csv");
		$("#entitytype").val(this.options.groupType);
		this.closeExport();
		$("#exportdata").submit();
    };
	this.closeExport = function(){
		setTimeout((function(self){
			return function(){
				if ( self.options.exportDlg ) {
					dijit.popup.close(self.options.exportDlg);
					self.options.exportDlg = null;
					$("#exportstatisticsmenu").remove();
				}	
			};
		})(this),10);
	};
    this.toggleExport = function(){
        if ( this.options.exportDlg ) {
            dijit.popup.close(this.options.exportDlg);
            this.options.exportDlg = null;
            $("#exportstatisticsmenu").remove();
        }
        var dom = '<table id="exportstatisticsmenu"><tr><td>'+
                                '<a title="Export to SVG (Ctrl+Alt+S)" href="#" class="exportsvg"><!--<img src="/images/svg.png" border="0" width="30px"/>--><span style="white-space: nowrap">Image to SVG</span></a>'+
                        '</td></tr><tr><td>'+
                                '<a title="Export to PNG (Ctrl+Alt+P)" href="#" class="exportpng"><!--<img src="/images/png.png" border="0" width="30px"/>--><span style="white-space: nowrap">Image to PNG</span></a>'+
                        '</td></tr><tr><td></td></tr>'+
                        '<tr><td>'+
                                '<a title="Export to CSV (Ctrl+Alt+C)" href="#" class="exportcsv"><!--<img src="/images/csv.png" border="0" width="30px"/>--><span style="white-space: nowrap">Data to CSV</span></a>'+
                        '</td></tr>'+
                        '</table>';

        this.options.exportDlg = new dijit.TooltipDialog({
                title: 'Export',
                content: $(dom)[0]
        });
        setTimeout((function(self){
            return function(){
                dijit.popup.open({
                        parent: $("#exportimg")[0],
                        popup: self.options.exportDlg,
                        around: $("#exportimg")[0],
                        orient: {BR:'TR'}
                });
                $("#exportstatisticsmenu").find(".exportsvg").off("click").on("click", (function(self){
                    return function(ev){
                        ev.preventDefault();
                        self.exportImageAs("svg");
						return false;
                    };
                })(self));
                $("#exportstatisticsmenu").find(".exportpng").off("click").on("click", (function(self){
                    return function(ev){
                        ev.preventDefault();
                        self.exportImageAs("png");
                        return false;
                    };
                })(self));
                $("#exportstatisticsmenu").find(".exportcsv").off("click").on("click", (function(self){
                    return function(ev){
                        ev.preventDefault();
                        self.exportData("png");
                        return false;
                    };
                })(self));
            };
        })(this),250);

    };
    this.setupListBox = function(){
        var listbox = $(this.dom).find(".listtoolbox.statistics");
        if( $(listbox).length === 0 ) return;
        if( $(listbox).find(".action.charttype.pie").length > 0 ){
            $(listbox).find(".action.charttype.pie").off("click").on("click", (function(self){
                return function(ev){
                    ev.preventDefault();
                    self.options.chartObj = self.getChartType();
                    self.load();
                    return false;
                };
            })(this));
        }
        if( $(listbox).find(".action.charttype.bar").length > 0 ){
            $(listbox).find(".action.charttype.bar").off("click").on("click", (function(self){
                return function(ev){
                    ev.preventDefault();
                    self.options.chartObj = self.getChartType("bars");
                    self.load();
                    return false;
                };
            })(this));
        }
        if( $(listbox).find(".action.export").length > 0 ){
            $(listbox).find(".action.export").off("click").on("click", (function(self){
                return function(ev){
                    ev.preventDefault();
                    self.toggleExport();
                    return false;
                };
            })(this));
        }
        $(listbox).find(".exporthelp").attr("href", appdb.config.endpoint.wiki + "main:faq:how_can_i_manipulate_an_appdb_statistics_chart").attr("target","_blank");
    };
	this.bindChartObject = function(){
		
	};
    this.load = function(){
        if( !this.options.chartObj ){
            this.options.chartObj = this.getChartType();
			this.bindChartObject();
        }
        if( this.options.chartObj ){
			this.preLoad();
			this.options.chartObj.load();
        }
    };
	this.preLoad = function(){
		var itemCount = $.trim($(this.dom).find(".itemcount").text());
		var emptyCount = $.trim($(this.dom).find(".emptycount").text());
		this.options.extdata = this.options.extdata || {};
		if( itemCount !== ""  ){
			this.options.extdata.itemCount = (itemCount<<0);
		}else{
			this.options.extdata.itemCount = this.getTotalCount();
		}
		this.options.extdata.emptyCount = (emptyCount<<0) || 0;
		this.setupEmptyItems();
		return;
	};
    this.initContainer = function(){
        this.buildExportImageForm();
        this.buildExportDataForm();
        this.setupListBox();
    };
    this.init = function(){
      this.dom = $(this.options.container);
      this.initContainer();
    };
    this.init();
});
appdb.statistics.software.category = appdb.ExtendClass(appdb.statistics.GenericContainer,"appdb.statistics.software.category", function(o){
    this.options = $.extend(true,this.options,{
            container: o.container,
            entityType: o.entityType || "software",
            groupType: o.groupType || "category",
            chartType: o.chartType || "appdb.statistics.charts.Sunburst",
            model: o.model || null
    });
	this.onNavigationClick = function(v){
		var data = this.options.navigation.options.entries;
		var sq = [];
		sq.push("+*&application.metatype:0");
		$.each(data,function(i,e){
			if( e.id > 0 ){
				sq.push("+=&category.id:"+e.id);
			}
		});
		appdb.views.Main.showApplications({flt: ""},{filterDisplay:'Search software...',mainTitle : 'Software',systemQuery:sq});	
	};
	this.buildTreeNavigation = function(d){
		if( this.options.navigation ){
			this.options.navigation._mediator.clearAll();
			this.options.navigation.reset();
			this.options.navigation = null;
		}
		var entries = [];
		var data = $.extend({},d);
		entries.push({
			name: "search",
			id: -1,
			display: "<span class='searchall'><img src='/images/search.png' alt=''/><span>browse</span></span>",
			isClickable: true
		});
		while(data !== null){
			if( data.id ){
				entries.push({
					name: data.text,
					id: data.id,
					display: data.text
				});
			}
			data = data.parent || null;
		}
		if( entries.length >= 2){
			entries[1].display = entries[1].display + '<a class="action zoomout" title="Zoom out"><img src="/images/closeview.png" alt=""></a>';
		}
		entries.push({
				name: "root",
				id: -1,
				display: "All categories"
			});
		entries.reverse();
		
		this.options.navigation = new appdb.statistics.TreeNavigationBar({
			parent: this,
			container:$(this.dom).find(".chartnavigation"),
			useSeperator: false,
			entries: entries
		});
		this.options.navigation.subscribe({event: "click", callback: function(v){
				this.onNavigationClick(v);
		}, caller: this});
		this.options.navigation.render();
		$(this.dom).find(".chartnavigation").prepend("<span class='chartnavigationtitle'>Filters:</span>");
		$(this.dom).find(".chartnavigation a.action.zoomout").off("click").on("click", (function(self){
			return function(ev){
			ev.preventDefault();
			self.options.chartObj.zoomOut();
			return false;
			};
		})(this));
	};
	this.bindChartObject = function(){
		this.options.chartObj._mediator.clearAll();
		this.options.chartObj.subscribe({event: "loaded", callback: function(v){
				this.buildTreeNavigation(v);
		}, caller: this});
		this.options.chartObj.subscribe({event: "zoom", callback: function(v){
				this.buildTreeNavigation(v);
		}, caller: this});
	};
	this.getTotalCount = function(){
		this._model =  new appdb.model.ApplicationsListing({},{async:false});
		var data = this._model.get();
		return data.length;	
	};
});
appdb.statistics.vappliance.category = appdb.ExtendClass(appdb.statistics.software.category,"appdb.statistics.vappliance.category", function(o){
    this.options = $.extend(true,this.options,{
            container: o.container,
            entityType: o.entityType || "vappliance",
            groupType: o.groupType || "category",
            chartType: o.chartType || "appdb.statistics.charts.Sunburst",
            model: o.model || null
    });
	this.onNavigationClick = function(v){
		var data = this.options.navigation.options.entries;
		var sq = [];
		sq.push("+*&application.metatype:1");
		$.each(data,function(i,e){
			if( e.id > 0 ){
				sq.push("+=&category.id:"+e.id);
			}
		});
		appdb.views.Main.showVirtualAppliances({flt: ""},{filterDisplay:'Search virtual appliances...',mainTitle : 'Virtual Appliances',systemQuery:sq});	
	};
	this.getTotalCount = function(){
		var data = appdb.model.StaticList.SoftwareLogistics.category || [];
		data = $.isArray(data)?data:[data];
		if( data.length === 0 ) return 0;
		var cdv = new appdb.views.CategoriesDataView();
		cdv.load();
		var fdata = [];
		$.each(cdv.options.data, function(i, e){
			if( $.trim(e.id) === "34" || $.trim(e.parentid)==="34" ){
				$.each(data, function(ii,ee){
					if($.trim(e.id) === $.trim(ee.id)){
						fdata.push(ee);
					}
				});
			}
		});
		var res =  appdb.utils.MergeTreeLogistics(cdv, fdata, true);
		res = $.isArray(res)?res:[];
		if( res.length === 0 || !res[0] || !res[0].children) return 0;
		res[0].children = res[0].children || [];
		res[0].children = $.isArray(res[0].children)?res[0].children:[res[0].children];
		if( res[0].children.length === 0 ) return 0;
		var count = 0;
		$.each(res[0].children, function(i,e){
			if( e.count && parseInt(e.id) > 0 ){
				count += parseInt(e.count);
			}
		});
		
		return count;
	};
	this.preLoad = function(){
		var itemCount = this.getTotalCount();
		var emptyCount = 0;
		this.options.extdata = this.options.extdata || {};
		if( itemCount !== ""  ){
			this.options.extdata.itemCount = (itemCount<<0);
		}else{
			this.options.extdata.itemCount = this.getTotalCount();
		}
		this.options.extdata.emptyCount = (emptyCount<<0) || 0;
		this.setupEmptyItems();
		return;
	};
});
appdb.statistics.software.discipline = appdb.ExtendClass(appdb.statistics.GenericContainer,"appdb.statistics.software.discipline", function(o){
    this.options = $.extend(true,this.options,{
            container: o.container,
            entityType: o.enityType || "software",
            groupType: o.groupType || "discipline",
            chartType: o.chartType || "appdb.statistics.charts.Sunburst",
            model: o.model || null
    });
    this.onNavigationClick = function(v){
		var data = this.options.navigation.options.entries;
		var sq = [];
		sq.push("+*&application.metatype:0");
		$.each(data,function(i,e){
			if( e.id > 0 ){
				sq.push("+=&discipline.id:"+e.id);
			}
		});
		appdb.views.Main.showApplications({flt:""},{filterDisplay:'Search software...',mainTitle : 'Software',systemQuery:sq});
	};
    this.buildTreeNavigation = function(d){
		if( this.options.navigation ){
			this.options.navigation._mediator.clearAll();
			this.options.navigation.reset();
			this.options.navigation = null;
		}
		var entries = [];
		var data = $.extend({},d);
		entries.push({
			name: "search",
			id: -1,
			display: "<span class='searchall'><img src='/images/search.png' alt=''/><span>browse</span></span>",
			isClickable: true
		});
		while(data !== null){
			if( data.id ){
				entries.push({
					name: data.text,
					id: data.id,
					display: data.text
				});
			}
			data = data.parent || null;
		}
		if( entries.length >= 2){
			entries[1].display = entries[1].display + '<a class="action zoomout" title="Zoom out"><img src="/images/closeview.png" alt=""></a>';
		}
		entries.push({
				name: "root",
				id: -1,
				display: "All disciplines"
			});
		entries.reverse();
		
		this.options.navigation = new appdb.statistics.TreeNavigationBar({
			parent: this,
			container:$(this.dom).find(".chartnavigation"),
			useSeperator: false,
			entries: entries
		});
		this.options.navigation.subscribe({event: "click", callback: function(v){
				this.onNavigationClick(v);
		}, caller: this});
		this.options.navigation.render();
		$(this.dom).find(".chartnavigation").prepend("<span class='chartnavigationtitle'>Filters:</span>");
		$(this.dom).find(".chartnavigation a.action.zoomout").off("click").on("click", (function(self){
			return function(ev){
			ev.preventDefault();
			self.options.chartObj.zoomOut();
			return false;
			};
		})(this));
	};
	this.bindChartObject = function(){
		this.options.chartObj._mediator.clearAll();
		this.options.chartObj.subscribe({event: "loaded", callback: function(v){
				this.buildTreeNavigation(v);
		}, caller: this});
		this.options.chartObj.subscribe({event: "zoom", callback: function(v){
				this.buildTreeNavigation(v);
		}, caller: this});
	};
	this.getTotalCount = function(){
		this._model =  new appdb.model.ApplicationsListing({flt:"+*&application.metatype:0"},{async:false});
		var data = this._model.get();
		return data.length;
	};

	this.preLoad = function(){
		var itemCount = "";
		var emptyCount = 0;
		this.options.extdata = this.options.extdata || {};
		if( itemCount !== ""  ){
			this.options.extdata.itemCount = (itemCount<<0);
		}else{
			this.options.extdata.itemCount = this.getTotalCount();
		}
		this.options.extdata.emptyCount = (emptyCount<<0) || 0;
		this.setupEmptyItems();
		return;
	};
});
appdb.statistics.vappliance.discipline = appdb.ExtendClass(appdb.statistics.software.discipline,"appdb.statistics.vappliance.discipline", function(o){
	 this.options = $.extend(true,this.options,{
            container: o.container,
            entityType: o.enityType || "vappliance",
            groupType: o.groupType || "discipline",
            chartType: o.chartType || "appdb.statistics.charts.Sunburst",
            model: o.model || null
    });
	this.onNavigationClick = function(v){
		var data = this.options.navigation.options.entries;
		var sq = [];
		sq.push("+*&application.metatype:1");
		$.each(data,function(i,e){
			if( e.id > 0 ){
				sq.push("+=&discipline.id:"+e.id);
			}
		});
		appdb.views.Main.showVirtualAppliances({flt: ""},{filterDisplay:'Search virtual appliances...',mainTitle : 'Virtual Appliances',systemQuery:sq});
	};
	this.getTotalCount = function(){
		this._model =  new appdb.model.ApplicationsListing({flt:"+*&application.metatype:1"},{async:false});
		var data = this._model.get();
		data.application = data.application || [];
		data.application = $.isArray(data.application)?data.application:[data.application];
		return data.application.length;
	};
	this.preLoad = function(){
		var itemCount = "";
		var emptyCount = 0;
		this.options.extdata = this.options.extdata || {};
		if( itemCount !== ""  ){
			this.options.extdata.itemCount = (itemCount<<0);
		}else{
			this.options.extdata.itemCount = this.getTotalCount();
		}
		this.options.extdata.emptyCount = (emptyCount<<0) || 0;
		this.setupEmptyItems();
		return;
	};
});
appdb.statistics.charts.Sunburst = appdb.ExtendClass(appdb.View, "appdb.statistics.charts.Sunburst", function(o){
	this.options = $.extend(true,{
		width: 1,
		height: 1,
		radius: 1,
		x: -1,
		y: -1,
		color: null,
		dom: {
			svg: null,
			path: null,
			arc: null,
			labels: null,
			partition: null,
			lines: null,
			labelright: null,
			labelleft: null
		},
		totalCount: -1,
		depth: 0,
		overflow: "resizeView"//[resizeView]: Adjusts svg to display more information. [resizeText]: Resize label size for each quadrant to fit svg size
	},o);
	
	this.options.domevents = {
		click_path: null,
		mouseover_path: null,
		mouseleave_path: null,
		mouseover_label: null,
		mouseleave_label: null
	};
	this.zoomOut = function(data){
		data = data || this.options.selectedData;
		var p = (data.parent)?data.parent.id:undefined;
		this.options.domevents.click_path.call(d3.select("#path"+p),data.parent);
		if( data.parent && !data.parent.id){
			this.options.dom.svg.select(".zoomimage").remove();
		}
	};
	this.renderZoomOut = function(display,d){
		this.options.dom.svg.select(".zoomimage").remove();
		if( display === true && this.options.dom.svg.select(".zoomimage")){
			var c = this.options.dom.arc.centroid(d),
				x = c[0],
				y = c[1],
				h = Math.sqrt(x*x + y*y);
			this.options.dom.svg.append('svg:image')
				.classed("zoomimage",true)
                .attr('xlink:href', '/images/zoomout.png')
                .attr('x', -15)
                .attr('y', -15)
                .attr('width', 30)
                .attr('height', 30)
				.on("click", (function(self,data){
					return function(){
						self.zoomOut(data);
					};
				})(this,d));
			
		}
	};
	this.options.domevents.click_path = (function(self){
		var _currentDepth = 0;
		return function(d) {
			if( !d.id && self.options.depth == 0 ){
				return;
			}
			_currentDepth = d.depth;
			self.options.depth = _currentDepth;
			self.options.selectedData = $.extend({},d);
			self.options.dom.path.classed("selected",false);
			self.options.dom.svg.selectAll("path.labelline").remove();
			self.options.dom.svg.selectAll("text.label").remove();
			self.renderSelectedLabel(d);
			self.options.dom.path.each(function(d){
				var dpth = d3.select(this).attr("data-depth");
				if(dpth >= (_currentDepth+1)  ){
					d3.select(this).classed("selected",true).attr("fill-opacity","1.0");
				}
			}).transition().duration(750)
			.attrTween("d", self.arcTween(d))
			.style("fill-opacity", function(d){
				if( d3.select(this).classed("selected") || d.depth <= _currentDepth){
					return "1.0";
				}
				return "0.08";
			}).each("end",(function(){
				var _once = function(context,dd){
					context.renderLabels(dd);
					context.options.dom.path.style("fill-opacity", function(){
						return 1.0;
					});
					self.renderPopup(this,false);
					self.renderZoomOut( (dd.depth >  0), dd );
				};
				return function(){
					_once(self,d);
					_once = function(){};
				};
			})());
			self.publish({event: "zoom", value: d});
		};
	})(this);	
	this.options.domevents.mouseover_path = (function(self){ 
		return function(d){
			self.options.dom.path.style("fill-opacity","0.1");
			d3.select(this).style("fill-opacity","1.0");
			if( d.children ){
				$.each(d.children, (function(p){
					return function(i,e){
						d3.select(p).style("fill-opacity","1.0");
					};
				})(this));
			}
			
			self.options.dom.path.each(function(data){
				if( data.depth <= (self.options.depth+1) || data.depth <= 1 ){
					d3.select(this).style("fill-opacity","1.0");
				}else if( data.parent && data.parent.id == d.id){
					d3.select(this).style("fill-opacity","1.0");
				}else if( d.parent && data.id == d.parent.id){
					d3.select(this).style("fill-opacity","1.0");
				}
			});
			if( self.options.dom.labels ){
				self.options.dom.labels.style("font-weight","normal");
			}
			self.options.dom.svg.select("#label"+d.id).style("font-weight","bold");
            self.options.dom.svg.select("#line"+d.id).style("stroke","black");
			self.renderPopup(this,d);
			return true;
		};
	})(this);
	this.options.domevents.mouseleave_path = (function(self){ 
		return function(d){
			if( self.options.dom.labels ){
				self.options.dom.labels.style("font-weight","normal");
			}
            self.options.dom.lines.style("stroke","#bbb");
			self.options.dom.path.style("fill-opacity","0.1");
			self.options.dom.path.each(function(data){
				if(data.depth <= (self.options.depth+1) ){
					d3.select(this).style("fill-opacity","1.0");
				}
			});
			self.renderPopup(this,false);
			return true;
		};
	})(this);

	this.options.domevents.mouseover_label = (function(self){
		return function(d){
			self.options.dom.labels.style("font-weight","normal");
			self.options.dom.lines.style("stroke","#bbb");
			self.options.dom.path.style("stroke","#fff");
			d3.select("#line"+d.id).style("stroke","black");
			d3.select(this).style("font-weight","bold");
			self.renderPopup(this,this.__objData__);
			return true;
		};
	})(this);

	this.options.domevents.mouseleave_label = (function(self){
		return function(d){
			d3.select(this).style("font-weight","normal");
			self.options.dom.lines.style("stroke","#bbb");
			self.options.dom.path.style("stroke","#fff");
			self.options.dom.path.style("stroke-width","1.5");
			self.renderPopup(this,false);
			return true;
		};
	})(this);
	
	this.fill_path = (function(self){
		return function(d,i) { 
			if( d.id == -1 || (d.count<<0) === 0 ){
				return "#fff";
			}
			return self.options.color( (d.depth*10) + i ); 
		};
	})(this);
	// Interpolate the scales!
	this.arcTween = (function(self){
		return function(d) {
			var xd = d3.interpolate(self.options.x.domain(), [d.x, d.x + d.dx]),
				yd = d3.interpolate(self.options.y.domain(), [d.y, 1]),
				yr = d3.interpolate(self.options.y.range(), [d.y ? 20 : 0, self.options.radius]);
			return function(d, i) {
				return i
					? function(t) { 
						if (t==1) {self.options.x.domain(xd(1));self.options.y.domain(yd(1)).range(yr(1));}
						return self.options.dom.arc(d); 
					}
					: function(t) { 
						self.options.x.domain(xd(t)); 
						self.options.y.domain(yd(t)).range(yr(t)); 
						return self.options.dom.arc(d); 
					};
			};
		};
	})(this);
	this.transform_label = (function(self){
		return function(item,leftlabelindex,rightlabelindex){
			leftlabelindex  =  -200;
			rightlabelindex =  -200;
			return function(d,i){
				if((d.parent && d.parent.text != item.text) || d.id == -1 || !d.id || d.count == 0 || d.depth > (item.depth+1) || d.depth < item.depth ) return null;
				var c = self.options.dom.arc.centroid(d),
				x = c[0],
				y = c[1],
				h = Math.sqrt(x*x + y*y);// pythagorean theorem for hypotenuse
				var res = "";
				if( x <= 0 ){
					if( y <= 0 ){
						res = "translate(" + (-(350)) +  ',' + (leftlabelindex) +  ")"; 
					}else{
						res =  "translate(" + (-(350)) +  ',' + (-leftlabelindex) +  ")"; 
						
					}
					leftlabelindex -= 16;
				}else{
					if( y < 0 ){
						res =  "translate(" + ((250)) +  ',' + (rightlabelindex) +  ")"; 
					}else{
						res =  "translate(" + ((250)) +  ',' + (-rightlabelindex) +  ")"; 
					}
					rightlabelindex  -= 16;
				}
				return res;
			};
		};
	})(this);
	
	this.getPopupHtml = function(d){
		var cpopup = $(document.createElement("div")).addClass("chartpopup");
		var title = $(document.createElement("div")).addClass("field").addClass("title");
		var count = $(document.createElement("div")).addClass("field").addClass("count");
		var footer = $(document.createElement("div")).addClass("footer");
		
		$(title).append("<span class='name'>Name:</span><span class='value'>" + d.text + "</span>");
		$(count).append("<span class='name'>Count:</span><span class='value'>" + d.count + "</span>");
		$(cpopup).append(title).append(count);
		$(footer).append("<div class='zoom'>Click to zoom</div></span>");
		$(cpopup).append(footer);
		if( d.children && d.children.length > 0 ){
			var desc = d.children.length;
			$.each(d.children, function(i,e){
				if( e.count == 0 || !e.text || d.id == -1) desc-=1;
			});
			$(cpopup).append(footer);
		}
		$(cpopup).css({"z-index":"1000","position":"absolute"});
		$(cpopup).hide();
		return cpopup;
	};
	this.renderPopup = function(dom,d){
		var data = $.extend({},d);
		$("body").find(".chartpopup").remove();
		$(dom).off("mousemove.popup");
		if( d === false || data.id == -1 || data.depth == 0){
			return;
		}
		var cpopup = this.getPopupHtml(data);
		$("body").append(cpopup);
		$(dom).on("mousemove.popup", function(ev){
			$(cpopup).css({top:ev.pageY+15, left: ev.pageX});
			if( $(cpopup).is(":visible") === false ){
				$(cpopup).fadeIn(500);
			}
		});
	};
	this.renderLabels = (function(self){
		return function(item){
			item = item || {};
			var hasChildren = ( item && $.isArray(item.children) && item.children.length > 0 );
			var fontsize = 16;
			var leftupindex = -200;
			var leftupsize = 16;
			var leftdownindex = 0;
			var leftdownsize = 16;
			var rightupindex = -200;
			var rightupsize = 16;
			var rightdownindex = 0;
			var rightdownsize = 16;
			var leftupcount = 0;
			var leftdowncount = 0;
			var rightupcount = 0;
			var rightdowncount = 0;
			var calculateCount = function(){
				$.each(item.children, function(i,e){
					if((e.parent && e.parent.text != item.text) || e.id == -1 || !e.id || e.count == 0 || e.depth > (item.depth+1) || e.depth < item.depth ) return null;
					var c = self.options.dom.arc.centroid(e),
					x = c[0],
					y = c[1];
					if(x<=0 && y<=0){
						leftupcount +=1;
						e.direction = "leftup";
					}else if(x<=0 && y>0){
						leftdowncount +=1;
						e.direction = "leftdown";
					}else if( x>0 && y<=0){
						rightupcount += 1;
						e.direction = "rightup";
					}else{
						rightdowncount += 1;
						e.direction = "rightdown";
					}
				});
			};
			var resizeText = function(){
				var half = self.options.height / 2 ;
				if( ((leftupcount * fontsize)) >= half ){
					leftupindex = -5;
					leftupsize =(half/leftupcount)+1;
				}else{
					leftupindex = -Math.abs(((leftupcount * fontsize)+50) - half);
				}
				if( ((leftdowncount * fontsize)) >= half ){
					leftdownindex = +5;
					leftdownsize = (half/leftdowncount)+1;
				}else{
					leftdownindex = Math.abs(((leftdowncount * fontsize)+50) - half);
				}
				if( ((rightdowncount * fontsize)) >= half ){
					rightdownindex = +5;
					rightdownsize = (half/rightdowncount)+1;
				}else{
					rightdownindex = Math.abs(((rightdowncount * fontsize)+50) - half);
				}
				if( ((rightupcount * fontsize)) >= half ){
					rightupindex = -5;
					rightupsize = (half/rightupcount)+1;
				}else{
					rightupindex = -Math.abs(((rightupcount * fontsize)+50) - half);
				}
			};
			var resizeView = function(item){
				var half = self.options.height / 2 ;
				var up = 200;
				var down = 10;
				var newheight = half;
				var newheighttranslate = (self.options.height / 2);
				up = Math.max(up,( (leftupcount*fontsize)+200) );
				up = Math.max(up, ( (rightupcount*fontsize)+200) );
				down = Math.max(down ,( (leftdowncount*fontsize)+10) );
				down = Math.max(down, ((rightdowncount*fontsize)+10) );
				if( up > half ){
					newheight += ((up + 50) );
					newheighttranslate = newheight ;
				}else{
					newheight = self.options.height;
				}
				if( down > half ){
					newheight += ((down + 50) );
				}
				
				d3.select($(self.dom)[0]).select("svg")
					.attr("height",Math.max(newheight,self.options.height));
					
				self.options.dom.svg
					.transition()
					.duration(300)
					.attr("transform", "translate(" + self.options.width / 2 + "," + ( (Math.max(newheight,half) / 2 )+30) + ")")
					.each("end", function(){
						//IE8 FIX: Adds an invalid div in body that distorts layout
						if( $.browser.msie ){
							var st = $("body>div:first-child").attr("style");
							if( /clip/i.test(st) ){
								$("body>div:first-child").css({"display":"none"});
							}
						}
					});
				
			};
			if( hasChildren ) {
				calculateCount();
			}
			if( hasChildren && $.trim(self.options.overflow).toLowerCase() === "resizetext"){
				resizeText(item);
			}else if(hasChildren && $.trim(self.options.overflow).toLowerCase() === "resizeview"){
				if( (leftupcount*leftupsize) > (self.options.height /2) ){
					leftupindex = -10;
					
				}
			}
			self.options.dom.svg.selectAll("path.labelline").remove();
			self.options.dom.svg.selectAll("g.label").remove();
			self.options.dom.labels = self.options.dom.svg.selectAll("text.label")
				.data(self.options.dom.partition.nodes)
				.enter().append("text")
				.attr("class", "label")
				.attr("id", function(d){
					this.__objData__ = $.extend({},d);
					return "label" + d.id;
				})
				.attr("dataid", function(d){
					return d.id;
				})
				.style("display", function(d){
					if((d.parent && d.parent.text != item.text) || d.id == -1 || !d.id || d.count == 0 || d.depth > (item.depth+1) || d.depth < item.depth ) return "none";
					return null;
				})
				.attr("font-size",function(d){
					switch(d.direction){
						case "leftup":
							return leftupsize-4;
						case "leftdown":
							return leftdownsize-4;
						case "rightdown":
							return rightdownsize-4;
						case "rightup":
							return rightupsize-4;
						default:
							return 12;
					}
				})
				.attr("x", function(d){
					if((d.parent && d.parent.text != item.text) || d.id == -1 || !d.id || d.count == 0 || d.depth > (item.depth+1) || d.depth < item.depth ) return null;
					var c = self.options.dom.arc.centroid(d),
					x = c[0],
					y = c[1],
					h = Math.sqrt(x*x + y*y);
					if(x<=0){
						return "-" + (self.options.radius+180);
					}
					return "" + (self.options.radius+180);
				})
				.attr("y", function(d){
					if((d.parent && d.parent.text != item.text) || d.id == -1 || !d.id || d.count == 0 || d.depth > (item.depth+1) || d.depth < item.depth ) return null;
					var c = self.options.dom.arc.centroid(d),
					x = c[0],
					y = c[1],
					h = Math.sqrt(x*x + y*y);
					if(x<=0){
						if( y<=0 ){
							leftupindex -= (leftupsize );
							return leftupindex;
						}else{
							leftdownindex -= (leftdownsize+(leftdownsize/100));
							if(y>leftdownindex){
								leftdownindex = y;
								return y;
							}
							return leftdownindex;
						}
					}else{
						if( y<=0 ){
							rightupindex -= (rightupsize + (rightupsize/100));
							return rightupindex;
						}else{
							rightdownindex += (rightdownsize + (rightdownsize / 100));
							if(y>rightdownindex){
								rightdownindex = y;
								return y;
							}
							return rightdownindex;
						}
					}
				})
				.on("mouseover", (function(self){
					return function(d){
						var item = d3.select("#path"+d3.select(this).attr("dataid"));
						if( item && item[0] && item[0][0]){
							self.options.domevents.mouseover_label.call(this,item[0][0].__data__);
						}
					};					
				})(this))
				.on("mouseleave",(function(self){
					return function(d){
						var item = d3.select("#path"+d3.select(this).attr("dataid"));
						if( item && item[0] && item[0][0]){
							self.options.domevents.mouseleave_label.call(this,item[0][0].__data__);
						}
					};					
				})(this))
				.on("click",(function(self){
					return function(d){
						var item = d3.select("#path"+d3.select(this).attr("dataid"));
						if( item && item[0] && item[0][0]){
							self.options.domevents.click_path.call(item[0][0],item[0][0].__data__);
						}
					};
				})(this))
				.attr("text-anchor", function(d){
					if((d.parent && d.parent.text != item.text) || d.id == -1 || !d.id || d.count == 0 || d.depth > (item.depth+1) || d.depth < item.depth ) return null;
					var c = self.options.dom.arc.centroid(d),
					x = c[0],
					y = c[1];
					if(x<=0){
						return "start";
					}
					return "end";
				});
				self.options.dom.labels.append("tspan").text(function(d){
					var text = ((d.parent && d.parent.text != item.text) || d.id == -1 || !d.id || d.count == 0 || d.depth > (item.depth+1) || d.depth < item.depth )?" ":d.text;
					return text;
				});
				self.options.dom.labels.append("tspan")
					.attr('fill','#666')
					.attr('font-size',12).text(function(d){ 
					var text = ((d.parent && d.parent.text != item.text) || d.id == -1 || !d.id || d.count == 0 || d.depth > (item.depth+1) || d.depth < item.depth )?"":d.text;
					if( text !== "" ){
						return " (" + d.count + ") ";
					}
					return text;
				});
				
				if( $.trim(self.options.overflow).toLowerCase() === "resizeview"){
					resizeView(item);
				}
				setTimeout(function(){
					self.renderLines(item);
				},100);
		};
	})(this);
	this.calculateLinePath = (function(self){
		return function(item){
			item = item || {};
			return function(d){
				var res = "";
				if((d.parent && d.parent.text != item.text) || d.id == -1 || !d.id || d.count == 0 || d.depth > (item.depth+1) || d.depth < item.depth ) return null;
                var c = self.options.dom.arc.centroid(d),
                cx = c[0],
                cy = c[1],
                ch = Math.sqrt(cx*cx + cy*cy);
				var path = {};
				var label = {};
				var label_padding = 10;
				
				if( $.browser.msie ){
					//ie8 fix
					var rnode = self.options.dom.svg.select("#label"+ d.id)[0][0].raphaelNode;
					rnode.shape.style.display = "inline";
					rnode.W = rnode.shape.scrollWidth;
					rnode.H = rnode.shape.scrollHeight;
					label = {
						x: $("#label" + d.id).attr("x")<<0,
						y: $("#label" + d.id).attr("y")<<0,
						height: rnode.H << 0,
						width: rnode.W<< 0
					};
				}else{
					label =$("#label"+ d.id)[0].getBBox();
				}
				
				if( $.browser.msie ) {
					//ie8 fix
					var pathw = self.options.dom.svg.select("#label"+ d.id)[0][0].raphaelNode[0].getBoundingClientRect();
					path = {
						width: pathw.right - pathw.left,
						height: pathw.bottom - pathw.top
					};
				}else{
					path =$("#path"+ d.id)[0].getBoundingClientRect();
				}
				var x = (label.x<0)?(-(self.options.radius+180) + label.width + label_padding):( (self.options.radius+180) - label.width - label_padding);
				var y = label.y;
				if( $.browser.msie ){
					y += (label.height /4 );
				}else{
					y += (label.height /2 );
				}
				
				res = "M" + x + " " + y;
                if( label.x>= 0 ){
					res += " L" +  (cx+(path.width/2)) + " "+ y;
				}else {
					res += " L" +  (cx-(path.width/2)) + " "+ y;    
                }
				res += " L" + (cx) + "," + cy;   
				return res;
			};
		};
	})(this);
	this.renderLines = (function(self){
		return function(item){
			self.options.dom.svg.selectAll("path.labelline").remove();
			self.options.dom.lines = self.options.dom.svg.selectAll("path.labelline")
				.data(self.options.dom.partition.nodes)
				.enter().append("path")
				.attr("class", "labelline")
				.attr("id", function(d){
					this.__objData__ = $.extend({},d);
					return "line" + d.id;
				})
				.attr("dataid", function(d){
					return d.id;
				})
				.attr("display", function(d){
					if((d.parent && d.parent.text != item.text) || d.id == -1 || !d.id || d.count == 0 || d.depth > (item.depth+1) || d.depth < item.depth ) return "none";
					return null;
				})
				.attr("d", self.calculateLinePath(item))
				.style("stroke","#bbb")
				.style("stroke-width","0.5")
				.style("fill","none")
				.on("click",(function(self){
					return function(d){
						var item = d3.select("#path"+d3.select(this).attr("dataid"));
						if( item && item[0] && item[0][0]){
							 self.options.domevents.click_path.call(item[0][0],item[0][0].__data__);
						}
					};					
				})(this))
				.on("mouseover", (function(self){
					return function(d){
						var item = d3.select("#path"+d3.select(this).attr("dataid"));
						if( item && item[0] && item[0][0]){
							 self.options.domevents.mouseover_label.call(this,item[0][0].__data__);
						}
					};					
				})(this))
				.on("mouseleave",(function(self){
					return function(d){
						var item = d3.select("#path"+d3.select(this).attr("dataid"));
						if( item && item[0] && item[0][0]){
							  self.options.domevents.mouseleave_label.call(this,item[0][0].__data__);
						}
					};					
				})(this));
		};	
	})(this);
	this.renderSelectedLabel = function(d){
		if( this.options.selectedLabel ){
			d3.selectAll(".selectedlabel").remove(); 
			this.options.selectedLabel = null;
		}
		if( !d.id ) return;
		var path = d3.select("#path" + d.id);
		this.options.selectedLabel = this.options.dom.svg
			.append("text")
			.attr("id", "selectedlabel"+d.id)
			.attr("class","selectedlabel")
			.attr('x', 0)
            .attr('y', 40)
			.style("text-anchor", "middle")
			.style("fill-opacity",0.01)
            .text(d.text).transition().duration(200).style("fill-opacity",1.0);
	};
	this.preRender = function(data){
		this.options.width = 800,
		this.options.height = 540,
		this.options.radius = Math.min(this.options.width, this.options.height) / 2.5;

		this.options.x = d3.scale.linear().range([0, 2 * Math.PI]);

		this.options.y = d3.scale.sqrt().range([0,this.options.radius]);

		this.options.color = d3.scale.category20c();
		
		this.options.dom.svg = d3.select($(this.dom)[0]).append("svg")
			.attr("width", this.options.width)
			.attr("height", this.options.height)
			.append("g")
			.attr("transform", "translate(" + this.options.width / 2 + "," + (this.options.height / 2 + 10) + ")");
		this.options.dom.partition = d3.layout.partition().value((function(total){
			return function(d) {
				total = total<<0;
				var c = (d.count<<0);
				return Math.abs((total/100) * c);
			};
		})(this.options.totalCount));

		this.options.dom.arc = (function(self){
			return d3.svg.arc()
				.startAngle(function(d) {return Math.max(0, Math.min(2 * Math.PI, self.options.x(d.x)));})
				.endAngle(function(d) {return Math.max(0, Math.min(2 * Math.PI, self.options.x(d.x + d.dx)));})
				.innerRadius(function(d) {return Math.max(0, self.options.y(d.y));})
				.outerRadius(function(d) {return Math.max(0, self.options.y(d.y + d.dy));});
		})(this);
		
		var rootdata = {};
		
		this.options.dom.path = this.options.dom.svg.data([{text:"root", children:data}]).selectAll("path")
			.data(this.options.dom.partition.nodes)
			.enter().append("path")
			.attr("d", this.options.dom.arc)
			.attr("id", function(d){
				if( !d.parent ) rootdata = d;
				d.__pathobj__ = this;
				return "path"+d.id;
			})
			.attr("dataid", function(d){
				return d.id;
			})
			.attr("data-depth", function(d){
				return d.depth;
			})
			.attr("data-parentid", function(d){
				return (d.parent)?d.parentid:0;
			})
			.style("fill-opacity", function(d){
				if( d.depth <= 1){
					return 1.0;
				}
				if( (d.count<<0) === 0 || d.id == -1){
					return 0.01;
				}
				if( d.depth !== 1 ){
					return 0.1;
				}
				return 1.0;
			})
			.style("display", function(d){
				return (d.id==-1 || (d.count && (d.count<<0) === 0))?"none":null;
			})
			.style("fill", this.fill_path)
			.style("stroke","#fff")
			.style("stroke-width","1.5")
			.on("click", this.options.domevents.click_path)
			.on("mouseover", (function(self){
					return function(d){
						var item = d3.select("#path"+d3.select(this).attr("dataid"));
						if( item && item[0] && item[0][0]){
							 self.options.domevents.mouseover_path.call(this,item[0][0].__data__);
						}
					};					
				})(this))
			.on("mouseleave", (function(self){
					return function(d){
						var item = d3.select("#path"+d3.select(this).attr("dataid"));
						if( item && item[0] && item[0][0]){
							 self.options.domevents.mouseleave_path.call(this,item[0][0].__data__);
						}
					};					
				})(this));
			
		setTimeout((function(self,rd){
			return function(){
				self.renderLabels(rd);
				self.publish({event: "loaded", value: rd});
			};
		})(this,rootdata),100);
	};
	this.render = function(d){
		$(this.dom).empty();
		this.preRender(d);
	};
	this.load = function(filter){
		this.options.filter = filter || this.options.filter;
		if( this.parent && this.parent.getExtData){
			this.options.totalCount = this.parent.getExtData().itemCount;
			this.options.totalCount = this.options.totalCount.count || null;
		}
		
		if( !this.options.model ){
			return;
		}
		this.options.model.unsubscribeAll();
		this.options.model.subscribe({event: "beforeselect", callback: function(v){
			$(this.dom).empty().append("<div class='loader'><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>...loading graph</span></div></div>");
		}, caller: this});
		this.options.model.subscribe({event: "select", callback: function(v){
				this.render(v);
		}, caller: this});
		this.options.model.load(this.options.filter);
	};
	this.init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		if( this.options.modelType ){
			this.options.model = new this.options.modelType();
		}
	};
	this.init();
});


appdb.statistics.charts.Partition = appdb.ExtendClass(appdb.View, "appdb.statistics.charts.Partition", function(o){
    this.options = $.extend(true,{
            width: 1,
            height: 1,
            radius: 1,
            x: -1,
            y: -1,
            color: null,
            dom: {
                svg: null,
                path: null,
                arc: null,
                laebls: null,
                partition: null,
                labelright: null,
                labelleft: null
            }
    },o);
    this.preRender = function(d){
        this.options.width = 850,
		this.options.height = 600,
        this.options.x = d3.scale.linear().range([0, this.options.width]),
        this.options.y = d3.scale.linear().range([0, this.options.heigth]);

        var vis = d3.select($(this.dom)[0]).append("div")
            .attr("class", "chart")
            .style("width", this.options.width + "px")
            .style("height", this.options.height + "px")
            .append("svg:svg")
            .attr("width", this.options.width)
            .attr("height", this.options.height);

        var partition = d3.layout.partition()
            .value(function(d) {return d.count;});
        var root = {text:"root", children:data};
        var g = vis.data([{text:"root", children:data}]).selectAll("g")
              .data(partition.nodes(root))
              .enter().append("svg:g")
              .attr("transform", (function(self){
                    return function(d) { 
                        return "translate(" + self.options.x(d.y) + "," + self.options.y(d.x) + ")";
                    }; 
                })(this))
                .on("click", click);

		var kx = this.options.width / 1,
			ky = this.options.height / 1;

		g.append("svg:rect")
			.attr("width", 1 * kx)
			.attr("height", function(d) {return d.dx * ky;})
			.attr("class", function(d) {return d.children ? "parent" : "child";});

		g.append("svg:text")
			.attr("transform", transform)
			.attr("dy", ".35em")
			.style("opacity", function(d) {return d.dx * ky > 12 ? 1 : 0;})
			.text(function(d) {return d.name;});

		d3.select(window)
			.on("click", function() {click(root);});

		var click = (function(self){
			return function(d) {
			if (!d.children) return;

			kx = (d.y ? this.options.width - 40 : self.options.width) / (1 - d.y);
			ky = self.options.height / d.dx;
			self.options.x.domain([d.y, 1]).range([d.y ? 40 : 0, self.options.width]);
			self.options.y.domain([d.x, d.x + d.dx]);

			var t = g.transition()
				.duration(d3.event.altKey ? 7500 : 750)
				.attr("transform", function(d) {return "translate(" + self.options.x(d.y) + "," + self.options.y(d.x) + ")";});

			t.select("rect")
				.attr("width", d.dy * kx)
				.attr("height", function(d) {return d.dx * ky;});

			t.select("text")
				.attr("transform", transform)
				.style("opacity", function(d) {return d.dx * ky > 12 ? 1 : 0;});

			d3.event.stopPropagation();
			};
		})(this);

		function transform(d) {
			return "translate(8," + d.dx * ky / 2 + ")";
		}
    };
    this.render = function(d){
            $(this.dom).empty();
            this.preRender(d);
    };
    this.load = function(filter){
		this.options.filter = filter || this.options.filter;
		if( !this.options.model ){
				return;
		}
		this.options.model.unsubscribeAll();
		this.options.model.subscribe({event: "beforeselect", callback: function(v){
			$(this.dom).empty().append("<div class='loader'><div class='loadmessage'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span>...loading graph</span></div></div>");
		}, caller: this});
		this.options.model.subscribe({event: "select", callback: function(v){
						this.render(v);
		}, caller: this});
		this.options.model.load(this.options.filter);
    };
    this.init = function(){
		this.dom = $(this.options.container);
		if( this.options.modelType ){
				this.options.model = new this.options.modelType();
		}
    };
    this.init();
});