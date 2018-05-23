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
var appdb = appdb || {};
appdb.views = {};
appdb.View = appdb.DefineClass("appdb.views.View", function(o) {
	this._mediator = null;
	this.subviews = [];
	this.dom = null;
	this.id = "";
	this.idPostfix = "";
	this.subscribe = null;
	this.publish = null;
	this.unsubscribe = null;
	this.unsubscribeAll = null;
	this.clearObserver = null;
	this.parent = null;
	this.clearSubviews = function() {
		var i, len;
		if (this.subviews && this.subviews.length > 0) {
			len = this.subviews.length;
			for (i = 0; i < len; i += 1) {
				this.subviews[i].destroy();
				this.subviews[i] = null;
			}
			this.subviews = [];
		}
	};
	this.destroy = function() {
		this.clearSubviews();
		this.reset();
		this.unsubscribeAll();
	};
	this.reset = function() {
		this.clearSubviews();
		$(this.dom).empty();
	};
	this.setContainer = function(cont) {
		this.dom = cont;
		return this;
	};
	this.getClosestParent = function(parenttype){
		parenttype = $.trim(parenttype);
		if( parenttype && $.isPlainObject(this.options) && this.options.parent ){
			var maxlevel = 30;
			var res = this;
			while(maxlevel>0){
				maxlevel-=1;
				if( !res.options.parent ) return null;
				if( res.options.parent._type_ && res.options.parent._type_.getName ){
					if( res.options.parent._type_.getName() === parenttype ){
						return res.options.parent;
					}
				}
				res = res.options.parent;
			}
		}
		return null;
	};
	this.getParentHierarchy = function(){
		var h = [];
		h.push(this._type_.getName());
		if( $.isPlainObject(this.options) && this.options.parent ){
			var res = this.options.parent;
			while(res!==null){
				if( !res.options.parent ) break;
				if( res.options.parent._type_ && res.options.parent._type_.getName ){
					h.push(res.options.parent._type_.getName());
				}
				res = res.options.parent;
			}
		}
		return h;
	};
	var _constructor = function() {
		o = o || {};
		if (typeof o.container === "string") {
			this.id = o.container;
			if ($(this.id).length > 0) {
				this.dom = $($(this.id)[0]);
			}
		} else {
			this.id = $(o.container).attr("id");
			this.dom = $(o.container);
		}
		this._mediator = new appdb.utils.ObserverMediator(this);
		this.subscribe = this._mediator.subscribe;
		this.publish = this._mediator.publish;
		this.unsubscribe = this._mediator.unsubscribe;
		this.unsubscribeAll = this._mediator.unsubscribeAll;
		this.clearObserver = this._mediator.clearAll;
		this.parent = o.parent || null;
	};
	_constructor.call(this);
});

appdb.views.DelayedDisplay = function(o) {
	var _dom = null, _delay = 200, _visible = false, _useDefault = false;
	var _init = function() {
		if (typeof o === "string") {
			_dom = $(o);
			return;
		} else {
			o = o || {};
			if( o.context ){
				_dom = $(o.context).find(o.selector);
			}else{
				_dom = $(o.selector);
			}
			
			_delay = o.delay || 200;
			_useDefault = o.usedefault || false;
		}
		if (_useDefault) {
			$(_dom).append('<img src="/images/ajax-loader-small.gif" alt="" width="12px" height="12px" style="padding:0px;margin:0px;vertical-align: top"  /><span style="font-style: italic;font-size: 12px;vertical-align: super;padding:0px;margin:0px;">Loading...</span>');
		}
	};
	_init();
	return {
		show: function() {
			_visible = true;
			setTimeout(function() {
				if (_visible === true) {
					$(_dom).css("visibility", "visible");
				}
			}, _delay);
		},
		hide: function() {
			_visible = false;
			$(_dom).css("visibility", "hidden");
		}
	};
};
appdb.views.ListViewMode = function(o) {
	var _container = "", _id = "", _list = null, _mode = 1, _dom = null;
	this.reset = function() {
		$(_dom).find("a").unbind("click");
		$(_dom).empty();
	};
	this.render = function() {
		var that = this;
		this.reset();
		var img1 = $("<a href='#'></a>").click(function() {
			that.setViewMode(1);
		}).append("<img id='" + _id + "gridviewimg' border='0' title='Grid view' alt='grid view' src='/images/gridview_s.png' ></img>").css("padding", "5px"),
		img2 = $("<a href='#'></a>").click(function() {
			that.setViewMode(2);
		}).
				append("<img id='" + _id + "listviewimg' border='0' title='List view' alt='list view' src='/images/listview.png' ></img>");
		$(_dom).empty().append(img1).append(img2);
		this.setViewMode(_mode);
	};
	this.setViewMode = function(m,remember) {
		remember = (typeof remember === "booelan")?remember:true;
		var newm = m;
		if (m == 2) {
			$(_list).addClass("itemlist");
			$(_list).removeClass("itemgrid");
			$(_list).attr("classname", "itemlist");
			$("#" + _id + "gridviewimg").attr("src", "/images/gridview.png");
			$("#" + _id + "listviewimg").attr("src", "/images/listview_s.png");
		} else {
			$(_list).addClass("itemgrid");
			$(_list).removeClass("itemlist");
			$(_list).attr("classname", "itemgrid");
			$("#" + _id + "gridviewimg").attr("src", "/images/gridview_s.png");
			$("#" + _id + "listviewimg").attr("src", "/images/listview.png");
		}
		if( remember && !o.selection){
			appdb.views.ListViewMode.previousSelection = newm;
		} else {
			o.selection = false;
		}
		
		_mode = m;
	};
	this.getViewMode = function() {
		return _mode;
	};
	this.getDom = function() {
		return $(_container);
	};
	var _init = function() {
		_container = o.container || "";
		_dom = $(_container);
		_list = $(o.list);
		_id = $(_container).attr("id");
		appdb.views.ListViewMode.previousSelection = appdb.views.ListViewMode.previousSelection || 1;
		_mode = o.selection || appdb.views.ListViewMode.previousSelection;
	};
	_init();
};
appdb.views.PagerPane = appdb.ExtendClass(appdb.View, "appdb.views.PagerPane", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null
	};
	this._createPageItem = function(pg, page) {
		var res = {};
		if (isNaN(page)) {
			if (page === "prev") {
				res["type"] = "previous";
				res["iscurrent"] = ((pg.pageNumber === 0)) ? true : false;
			} else if (page === "next") {
				res["type"] = "next";
				res["iscurrent"] = ((pg.pageNumber + 1) === pg.pageCount) ? true : false;
			} else {
				res["type"] = "sep";
				res["iscurrent"] = false;
			}
			res["index"] = -1;
		} else {
			res["type"] = "page";
			res["index"] = page;
			res["iscurrent"] = ((pg.pageNumber + 1) === page) ? true : false;
		}
		return res;
	};
	this._createPager = function(pg, sides, center) {
		var res = [];
		if (pg.pageCount === 1) {
			return res;
		}
		if (pg.pageCount <= ((sides * 2) + center)) {
			res = this._createFullPager(pg);
		} else {
			res = this._createSplitPager(pg, sides, center);
		}
		return res;
	};
	this._createFullPager = function(pg) {
		var i, len = pg.pageCount, res = [], pgitem = this._createPageItem;
		res[res.length] = pgitem(pg, "prev");
		for (i = 1; i <= len; i += 1) {
			res[res.length] = pgitem(pg, i);
		}
		res[res.length] = pgitem(pg, "next");
		return res;
	};
	this._createSplitPager = function(pg, sides, center) {
		sides = sides || 2;
		center = center || 2;
		var i, len = pg.pageCount, pgnum = pg.pageNumber, res = [], left = sides, right = sides,
				sepleft = false, sepright = false, centerLen = 0, _pgitem = this._createPageItem;
		res[res.length] = _pgitem(pg, "prev");
		if ((pgnum) < (sides + center - 1)) {
			left = (sides * 2) + center;
			sepright = true;
			center = 0;
		} else if ((pgnum + sides + center - 1) >= len) {
			right = (sides * 2) + center;
			sepleft = true;
			center = 0;
		} else {
			sepright = sepleft = true;
		}
		centerLen = (pgnum + Math.floor(center / 2) + 1);
		//left side buttons
		for (i = 1; i <= left; i += 1) {
			res[res.length] = _pgitem(pg, i);
		}
		//left seperator
		if (sepleft === true) {
			res[res.length] = _pgitem(pg, "sep");
		}
		//center buttons
		if (center > 0) {
			for (i = (pgnum - Math.floor(center / 2) + 1); i <= centerLen; i += 1) {
				res[res.length] = _pgitem(pg, i);
			}
		}
		//right seperator
		if (sepright === true) {
			res[res.length] = _pgitem(pg, "sep");
		}
		//right side buttons
		for (i = (len - right + 1); i <= len; i += 1) {
			res[res.length] = _pgitem(pg, i);
		}
		res[res.length] = _pgitem(pg, "next");
		return res;
	};
	this.reset = function() {
		var pl = dijit.byId(this.id + this.idPostfix);
		if (pl) {
			pl.destroyRecursive(false);
		}
		if (this._pagerModel) {
			this._pagerModel.unsubscribeAll(this);
		}
	};
	this.render = function(pger) {
		var bt, ui, i = 0, bts = this._createPager(pger, 2, 2), len = bts.length, layout = null, _this = this;
		this.reset();
		
		if (bts.length === 0) {
			return;
		}
		layout = new dijit.layout.LayoutContainer({
			id: this.id + this.idPostfix,
			style: "margin-left: auto; margin-right: auto; display:block; overflow-x:auto;"
		});
		while (len--) {
			bt = bts[i++];
			switch (bt.type) {
				case "previous":
					ui = new dijit.form.Button({
						label: "<div style='width:20px;'>&lt;</div>",
						style: "font-family:Arial,sans-serif;font-size:12px;font-weight:400;color:#454545;padding:1px;margin:0px;", 
						disabled: bt.iscurrent,
						onClick: function() {
							_this.publish({event: "previous", value: ""});
						}
					});
					break;
				case "next":
					ui = new dijit.form.Button({
						label: "<div style='width:20px;'>&gt;</div>",
						style: "font-family:Arial,sans-serif;font-size:12px;font-weight:400;color:#454545;padding:1px;margin:0px;",
						disabled: bt.iscurrent,
						onClick: function() {
							_this.publish({event: "next", value: ""});
						}
					});
					break;
				case "sep":
					ui = new dijit.layout.ContentPane({
						content: "...",
						style: "display:inline-block;width:20px;font-weight:bold;text-align:center;"
					});
					break;
				case "page":
					ui = new dijit.form.Button({
						label: "<div style='width:20px'>" + bt.index + "</div>",
						style: "font-family:Arial,sans-serif;font-size:12px;font-weight:400;color:#454545;padding:1px;margin:0px;", //style : "font-family:Arial,sans-serif;font-size:12px;font-weight:400;color:#454545;padding:2px;",
						disabled: bt.iscurrent,
						onClick: (function(p) {
							return function() {
								_this.publish({event: "current", value: (p - 1)});
							};
						})(bt.index)
					});
					break;
			}
			layout.addChild(ui);
		}
		if ($(this.dom).length > 0) {
			layout.placeAt($(this.dom)[0]);
		}
	};
	this.hide = function() {
		$(this.dom).hide();
	};
	this.show = function() {
		$(this.dom).show();
	};
	this.destroy = function() {
		this.clearObserver();
		this.reset();
		$(this.dom).empty();
	};
	this._init = function() {
		if ($(this.options.container).length === 0) {
			this.options.container = $("#" + this.id);
		}
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};

	this._init();
});
appdb.views.MultiPagerPane = appdb.ExtendClass(appdb.View, "appdb.views.MultiPagerPane", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || []
	};
	this.reset = function() {
		$.each(this.subviews, (function(self) {
			return function(i, e) {
				e.reset();
			};
		})(this));
	};
	this.render = function(p) {
		$.each(this.subviews, (function(self, data) {
			return function(i, e) {
				e.render(data);
			};
		})(this, p));
	};
	this.subscribe = function(o) {
		$.each(this.subviews, (function(self) {
			return function(i, e) {
				e.subscribe(o);
			};
		})(this));
	};
	this.unsubscribe = function(p) {
		$.each(this.subviews, (function(self, data) {
			return function(i, e) {
				e.unsubscribe(data);
			};
		})(this, p));
	};
	this.unsubscribeAll = function(p) {
		$.each(this.subviews, (function(self, data) {
			return function(i, e) {
				e.unsubscribeAll(data);
			};
		})(this, p));
	};
	this.hide = function() {
		$.each(this.subviews, (function(self) {
			return function(i, e) {
				e.hide();
			};
		})(this));
	};
	this.show = function() {
		$.each(this.subviews, (function(self) {
			return function(i, e) {
				e.show();
			};
		})(this));
	};
	this.destroy = function() {
		$.each(this.subviews, (function(self) {
			return function(i, e) {
				e.destroy();
			};
		})(this));
	};

	this._initPagers = function() {
		if ($.isArray(this.subviews) === true && this.subviews.length > 0) {
			this.reset();
		}
		this.subviews = [];
		$(this.dom).each((function(self) {
			return function(i, e) {
				self.subviews.push(new appdb.views.PagerPane({container: $(e)}));
			};
		})(this));
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initPagers();
	};
	this._init();
});
appdb.views.AlphanumericPagerPane = appdb.ExtendClass(appdb.View, "appdb.views.PagerPane", function(o) {
	this._pager = null;
	this._createPagerItem = function(pg, page) {
		var res = {};
		if (page) {
			res["type"] = "page";
			res["index"] = page.value;
			res["count"] = page.count;
			res["iscurrent"] = ((pg.currentSelection() === page.value) ? true : false);
		}
		return res;
	};
	this._findItemWithValue = function(l, v, p) {
		var i, len = l.length;
		for (i = 0; i < len; i += 1) {
			if (v.toLowerCase() === l[i].value.toLowerCase()) {
				return l[i];
			}
		}
		return {"type": "page", "value": v, "count": "0", "iscurrent": ((p.currentSelection() === v) ? true : false)};
	};
	this._createFullPager = function(pg, pger) {
		var i, res = [], pgitem = this._createPagerItem;

		res[res.length] = pgitem(pger, this._findItemWithValue(pg.item, "0-9", pger));
		var vals = "abcdefghijklmnopqrstuvwxyz", len = vals.length;
		for (i = 0; i < len; i += 1) {
			res[res.length] = pgitem(pger, this._findItemWithValue(pg.item, vals[i], pger));
		}
		res[res.length] = pgitem(pger, this._findItemWithValue(pg.item, "...", pger));
		return res;
	};
	this._createSplitPager = this._createFullPager;
	this._createPager = this._createFullPager;
	this.render = function(d, pger) {
		var bt, ui, i = 0, bts = this._createPager(d, this.parent), len = bts.length, layout = null, _this = this, first = null;
		this.reset();
		if (bts.length === 0) {
			return;
		}
		layout = new dijit.layout.LayoutContainer({
			id: this.id + this.idPostfix,
			style: " display:block; overflow-x:auto;"
		}, $("#" + this.id)[0]);
		while (len--) {
			bt = bts[i++];
			ui = new dijit.form.Button({
				label: "<div style='width:18px;padding-left:0px;margin-left:-3px;font-weight:bold;'>" + (bt.index || "...").toUpperCase() + "</div>",
				style: "font-family:Arial,sans-serif;font-size:10px;font-weight:400;color:#454545;overflow:visible;padding:1px;margin:0px;width:22px;text-align:left;",
				disabled: (bt["count"] == 0) ? true : false,
				onClick: (function(p) {
					return function(v) {
						$(this.domNode).parent().find(".selectedpage").removeClass("selectedpage");
						$(this.domNode).addClass("selectedpage");
						_this.publish({event: "current", value: p});
					};
				})(bt.index)
			});
			first = (bt.count != 0 && first == null && bt["index"] !== "0-9") ? ui : first;
			layout.addChild(ui);
		}
		if (first) {
			if (first)
				setTimeout(function() {
					first.onClick();
				}, 1);
		}
	};
	this.destroy = function() {
		this.clearObserver();
		this.reset();
		$(this.id).empty();
	};
	this.reset = function() {
		var pl = dijit.byId(this.id + this.idPostfix);
		if (pl) {
			pl.destroyRecursive(false);
		}
		$(this.dom).empty();
	};
});
appdb.views.Ordering = appdb.ExtendClass(appdb.View, "appdb.views.Ordering", function(o) {
	var opt = {}, _dstore = null, _this = this;
	this._selected = {value: null, name: null, order: "ASC"};
	this._operations = [];
	var _nameByValue = function(v) {
		var i, items = opt.items || [], len = items.length;
		for (i = 0; i < len; i += 1) {
			if ($.isArray(items[i].value)) {
				if (items[i].value[0] === v) {
					return items[i].name[0];
				}
			} else {
				if (items[i].value === v) {
					return items[i].name;
				}
			}
		}
		return null;
	};
	this.isSelectedOrderByOperationHidden = function() {
		var i, arr = this._operations, len = arr.length;
		for (i = 0; i < len; i += 1) {
			if (arr[i].hideOperation && arr[i].value && arr[i].value == this._selected.value) {
				return true;
			}
		}
		return false;
	};
	this.getSelected = function() {
		if (this._selected.value === null) {
			return null;
		}
		return {"orderby": this._selected.value, "orderbyOp": this._selected.order + ((this._selected.operation) ? " " + this._selected.operation : "")};
	};
	this.setSelected = function(value, order) {
		this._selected.value = value || this._selected.value;
		this._selected.order = order || this._selected.order;
		this._selected.name = _nameByValue(this._selected.value);
		this._selected.hideOrderByOperation = this.isSelectedOrderByOperationHidden(value || this._selected.value);
	};
	this.resetSelected = function() {
		this._selected = {value: opt.selected, name: _nameByValue(opt.selected), order: "ASC"};
	};
	this._constructor = function() {
		opt = o || {};
		this.id = opt.container;
		this.idPostfix = "orderinglist";
		opt.items = opt.items || [];
		opt.items = ($.isArray(opt.items) ? opt.items : [opt.items]);
		this._operations = opt.items.slice(0);
		_dstore = new dojo.data.ItemFileReadStore({
			data: {label: "name", id: "value", items: opt.items}
		});
		this._selected.value = opt.selected;
		if (this._selected.value) {
			this._selected.name = _nameByValue(this._selected.value);
		}
	};
	this.supportOrdering = function(v) {
		var i, arr = this._operations, len = arr.length;
		for (i = 0; i < len; i += 1) {
			if (arr[i].value && arr[i].value == this._selected.value) {
				return true;
			}
		}
		return false;
	};
	this.reset = function() {
		var pl = dijit.byId(this.id + this.idPostfix);
		if (pl) {
			pl.destroyRecursive(false);
		}
	};
	this._setOperations = function(sel) {
		var store = new dojo.data.ItemFileReadStore({
			data: {label: "name", items: [{name: "Ascending", value: "ASC"}, {name: "Descending", value: "DESC"}]}
		});
		if (sel && sel.operation) {
			store = new dojo.data.ItemFileReadStore({
				data: {label: "name", items: [{name: "Ascending", value: "ASC " + sel.operation}, {name: "Descending", value: "DESC " + sel.operation}]}
			});
		}
		return store;
	};
	this.render = function(d) {
		var layout = null, ui = null, uiop = null;
		this.reset();
		d = d || {};
		if ($("#" + this.id).length > 0)
			this.dom = $("#" + this.id)[0];
		if (!this.supportOrdering(d.orderby || this._selected.value)) {
			return;
		}
		this._selected.value = d.orderby || this._selected.value;
		this._selected.order = d.orderbyOp || this._selected.order;
		this._selected.hideOrderByOperation = this.isSelectedOrderByOperationHidden();
		layout = new dijit.layout.LayoutContainer({
			id: this.id + this.idPostfix,
			style: "margin-left: auto; margin-right: auto; display:block; width:auto !important; max-width:500px;  overflow-x:auto;"
		});
		ui = new dijit.layout.ContentPane({
			content: "Order By",
			style: "display:inline-block;font-weight:bold;vertical-align:middle;"
		});
		layout.addChild(ui);
		ui = new dijit.form.FilteringSelect({
			required: true,
			store: _dstore,
			searchAttr: "name",
			displayedValue: this._selected.name,
			style: "margin-left: 5px; max-width: 110px",
			onChange: function(v) {
				_this._selected.value = opt.items[v].value[0];
				_this._selected.name = opt.items[v].name[0];
				_this._selected.order = ((opt.items[v].defaultOperation) ? opt.items[v].defaultOperation[0] : undefined) || _this._selected.order || "ASC";
				_this._selected.hideOrderByOperation = _this.isSelectedOrderByOperationHidden();
				if (_this._selected.hideOrderByOperation) {
					uiop.domNode.style.visibility = "hidden";
				} else {
					uiop.domNode.style.visibility = "visible";
				}
				if (opt.items[v].operation) {
					_this._selected.operation = opt.items[v].operation[0];
					uiop.set("displayedValue",(_this._selected.order === "ASC" ? "Ascending" : "Descending"));
					_this.publish({event: "order", value: {"orderby": _this._selected.value, "orderbyOp": _this._selected.order + " " + _this._selected.operation}});
				} else {
					_this._selected.operation = null;
					uiop.set("displayedValue",(_this._selected.order === "ASC" ? "Ascending" : "Descending"));
					_this.publish({event: "order", value: {"orderby": _this._selected.value, "orderbyOp": _this._selected.order}});
				}
			}
		});
		layout.addChild(ui);
		uiop = new dijit.form.FilteringSelect({
			store: this._setOperations(this._selected),
			displayedValue: (this._selected.order === "ASC" ? "Ascending" : "Descending"),
			style: "margin-left: 5px; max-width: 90px;visibility:" + ((this._selected.hideOrderByOperation) ? "hidden" : "visible") + ";",
			onChange: (function(_this) {
				return function(v) {
					_this._selected.order = ((v === 0) ? "ASC" : "DESC");
					if (_this._selected.operation) {
						_this.publish({event: "order", value: {"orderby": _this._selected.value, "orderbyOp": _this._selected.order + " " + _this._selected.operation}});
					} else {
						_this.publish({event: "order", value: {"orderby": _this._selected.value, "orderbyOp": _this._selected.order}});
					}

				};
			})(this)
		});
		layout.addChild(uiop);
		layout.placeAt($(this.dom)[0]/*_container*/);
	};
	this.destroy = function() {
		this.reset();
		this.clearObserver();
	};
	this._constructor();
});
appdb.views.Filter = appdb.ExtendClass(appdb.View, "appdb.views.Filter", function(o) {
	this.opt = {};
	this._field = 'flt';
	this._watermark = appdb.views.Filter.watermarkDefault;
	this._width = null;
	this._isRich = false;
	this._currentValue = "";
	this.txtbox = null;
	this.btclear = null;
	this._domEvents = [];
	this._isFuzzy = 0;
	this.displayClear = false;
	this._autocomplete = undefined;
	this._constructor = function() {
		this.opt = o || {};
		if (this.opt.rich) {
			this._isRich = (this.opt.rich === false) ? false : true;
		}
		if (this.opt.width) {
			this._width = this.opt.width;
		}
		if (this.opt.watermark) {
			this._watermark = this.opt.watermark;
		}
		if (this.opt.field) {
			this._field = this.opt.field;
		}
		if (this.opt.displayClear) {
			this.displayClear = true;
		}
	};
	this.reset = function() {
		var de = this._domEvents;
		dojo.forEach(de, dojo.disconnect);
		var pl = dijit.byId(this.id + this.idPostfix);
		if (pl) {
			this._domEvents = [];
			pl.destroyRecursive(false);
		}
		if (this.txtbox) {
			if ($($(this.txtbox.domNode).find(":input")).attr("data-fixInt") !== undefined) {
				clearInterval($($(this.txtbox.domNode).find(":input")).attr("data-fixInt"));
			}
			this.txtbox.destroyRecursive(false);
			this.txtbox = null;
		}
		if (this.btclear) {
			this.btclear.destroyRecursive(false);
			this.btclear = null;
		}
		return this;
	};
	this.setValue = function(v) {
		v = v || {};
		v[this._field] = v[this._field] || "";
		if (this._isRich) {
			v.fuzzySearch = v.fuzzySearch || 0;
			this._isFuzzy = v.fuzzySearch;
		}
		this._currentValue = v[this._field];
		if (this.txtbox) {
			if (this._currentValue === "") {
				this.txtbox.attr("value", this._watermark);
				this.txtbox.attr("style", {"color": appdb.views.Filter.watermarkColor});
			} else {
				this.txtbox.attr("value", this.clearNormalization(this._currentValue));
			}
		}
		this._onValueChange(this._currentValue);
		return this;
	};
	this.getValue = function() {
		return this._currentValue;
	};
	this.isFuzzy = function(b) {
		if (typeof b === "boolean") {
			this._isFuzzy = (b) ? 1 : 0;
		}
		if (isNaN(this._isFuzzy)) {
			this._isFuzzy = 0;
		}
		return this._isFuzzy;
	};
	this.setWatermark = function(d) {
		if (typeof d === "string") {
			this._watermark = d;
		} else {
			this._watermark = appdb.views.Filter.watermarkDefault;
		}
		return this;
	};
	this.getWatermark = function() {
		return this._watermark;
	};
	this._onValueChange = function(v) {
		if (this.btclear) {
			if ($.trim(v) === "") {
				this.btclear.set('content', '<a href="#" title="Clear filter" style="display:none;" ><img alt="clear filter" src="/images/cancelicon.png" style="vertical-align: middle" border="0" /></a>');
			} else {
				this.btclear.set('content', '<a href="#" title="Clear filter" ><img alt="clear filter" src="/images/cancelicon.png" style="vertical-align: middle" border="0" /></a>');
			}
		}
	};
	this.doClick = function() {
		this.btsearchClick();
	};
	this._createQuery = function(v) {
		var q = {};
		q[this._field] = v || '';
		if (this._isRich) {
			q.fuzzySearch = this.isFuzzy();
		}
		q.pageoffset = 0;
		return q;
	};
	this.getTargetType = function(target) {
		target = target || this._selectedTarget || undefined;
		var type = "";
		if (this.parent) {
			if (this.parent.views) {
				if (this.parent.views._export) {
					type = (this.parent.views._export) ? this.parent.views._export.target : "";
				} else if (this.parent.views.peopleList) {
					type = "people";
				} else if (this.parent.views.vosList) {
					type = "vos";
				} else if (this.parent.views.sitesList) {
					type = "sites";
				}
			}
		}

		if (type === "") {
			if (!!target)
				type = target.value;
			else
				type = "apps";
		}
		
		if (["apps", "applications", "vapps", "vappliances", "vappliance"].indexOf(type) > -1) {
			type = "applications";
		}

		return type;
	};
	
	this.getFilterTargetName = function() {
		var name = this.getTargetType();
		switch(name) {
			case "applications":
				return "application";
			case "people":
				return "person";
			case "vos":
				return "vo";
			case "sites":
				return "site";
			default:
				return "";
		}
	};
	this.normalizeFilter = function(v, target) {
		var _this = this, type = _this.getTargetType(target), ret = {};

		if (v != "" && type != "") {
			var d = new appdb.utils.rest({
				endpoint: appdb.config.endpoint.proxyapi + "?version=" + appdb.config.apiversion + "&resource=" + type + "/filter/normalize%3Fflt=" + encodeURIComponent(encodeURIComponent(v)), //appdb.config.endpoint.baseapi+type+'/filter/normalize?flt='+encodeURIComponent(v),
				async: false
			}).create().call();
			if (d.filter) {
				ret.normalForm = appdb.utils.base64.decode(d.filter.normalForm);
				if (d.filter.error) {
					ret.error = appdb.utils.base64.decode(d.filter.error);
				}
				if (d.filter.field) {
					ret.fields = d.filter.field;
				}
			}
		} else {
			ret.normalForm = "";
			ret.originalForm = "";
		}
		return ret;
	};
	this.doQuery = function(v, target) {
		if (target === undefined)
			target = null;
		q = this._createQuery(v);
		if (target !== null)
			q = {"query": q, "target": target};
		this.publish({event: "filter", value: q});
	};
	this.showFilterError = function(err) {
		var closeErrDial = 'dijit.byId($(this).parents(\'div.dijitDialog\').attr(\'id\')).onCancel();';
		var errHelp = '<br/><br/><a href="#" onclick="' + closeErrDial + 'filterHelp($(\'#egimoto\')[0])">Help</a><span style="margin-left:3px; margin-right:3px;">|</span><a href="#" onclick="' + closeErrDial + '">Close</a>';
		new appdb.utils.notification({
			title: 'Filter expression parsing error',
			message: err + errHelp
		});
	};
	this.clearNormalization = function(value) {
		var target = this.getFilterTargetName();
		
		if(!target) {
			return value;
		}
		
		var rx = new RegExp(target + '\.any\:','ig');
		value = $.trim(value.replace(rx, ' '));
		var matches = value.match(/(\w+\.any\:)/ig);
		if (matches) {
			$.each(matches, function(i, m) {
				value = $.trim(value.replace(m, (m.split('.any:').shift() + ':')));
			});
		}
		
		return value;
	};
	this.render = function() {
		var layout = null, btsearch = null, bthelp = null, fuzzy = null, _this = this;
		this.reset();
		if ($("#" + this.id).length > 0)
			this.dom = $("#" + this.id);
		layout = new dijit.layout.LayoutContainer({
			id: this.id + this.idPostfix,
			style: "white-space: nowrap; margin-left: 0; margin-right: auto"
		});
		this.txtbox = new dijit.form.TextBox({
			id: this.id + this.idPostfix + "simpleFilter",
			value: this.clearNormalization(this._currentValue || ""),
			title: "Keywords search, googlesque syntax applies",
			style: ((this._width !== null) ? "width:" + this._width + ";" : "")
		});
		if (this._currentValue === "" || this._currentValue === this._watermark) {
			this.txtbox.attr("value", this._watermark);
			this.txtbox.attr("style", {"color": appdb.views.Filter.watermarkColor});
		}
		this._domEvents.push(dojo.connect(this.txtbox, "onFocus", this, function(k) {
			if (_this.txtbox.attr("value") == _this._watermark) {
				_this.txtbox.attr("value", "");
				_this.txtbox.attr("style", {"color": "inherit"});
			}
		}, true));
		this._domEvents.push(dojo.connect(this.txtbox, "onBlur", this, function(k) {
			var t = _this.txtbox;
			if (t.attr("value") === "" || t.attr("value") === _this._watermark) {
				t.attr("value", _this._watermark);
				t.attr("style", {"color": appdb.views.Filter.watermarkColor});
			}
		}, true));
		this._domEvents.push(dojo.connect(this.txtbox, "onKeyUp", this, function(e) {
			var v = _this.txtbox.attr("displayedValue"), q, k = (window.event) ? event.keyCode : e.keyCode;
			_this._onValueChange(v);
			if (k == dojo.keys.ENTER) {
				ret = _this.normalizeFilter(v, _this._selectedTarget);
				v = ret.normalForm;
				_this.txtbox.attr("value", this.clearNormalization(v));
				if (ret.error)
					this.showFilterError(ret.error);
				this.doQuery(ret.normalForm, _this._selectedTarget);
			}
		}, true));
		this._domEvents.push(dojo.connect(this.txtbox, "onMouseDown", this, function(k) {
			if (_this.txtbox.attr("value") === _this._watermark) {
				_this.txtbox.attr("value", "");
				_this.txtbox.attr("style", {"color": "inherit"});
			}
		}, true));
		btsearch = new dijit.layout.ContentPane({
			content: '<a href="#" title="Search"><img src="' + loadImage("images/search.png") + '" style="vertical-align: middle" border="0" alt="" /></a>',
			style: 'display:inline;padding:2px;'
		});
		this.btsearchClick = function() {
			var v = _this.txtbox.attr("value"), q;
			if (v === _this._watermark) {
				v = "";
			}
			var ret = this.normalizeFilter(v);
			v = ret.normalForm;
			_this.txtbox.attr("value", _this.clearNormalization(v));
			if (ret.error)
				this.showFilterError(ret.error);
			this.doQuery(v);
		};
		this._domEvents.push(dojo.connect(btsearch, "onClick", this, this.btsearchClick, true));
		layout.addChild(this.txtbox);
		layout.addChild(btsearch);
		if (this._isRich === true || this.displayClear === true) {
			this.btclear = new dijit.layout.ContentPane({
				content: '<a href="#" title="Clear filter" ' + ((this._currentValue === "" || this._currentValue === this._watermark) ? 'style="display:none;"' : '') + ' ><img alt="clear filter" src="/images/cancelicon.png" style="vertical-align: middle" border="0" /></a>',
				style: 'display:inline;'
			});
			this._domEvents.push(dojo.connect(this.btclear, "onClick", this, function() {
				_this.txtbox.attr("value", _this._watermark);
				_this.txtbox.attr("style", {"color": appdb.views.Filter.watermarkColor});
				var val = {fuzzySearch: _this.isFuzzy(), pageoffset: 0};
				val[_this._field] = '';
				_this.publish({event: "filter", value: val});
			}, true));
			layout.addChild(this.btclear);
		}
		if (this._isRich === true) {
			bthelp = new dijit.layout.ContentPane({
				content: '<a href="' + appdb.config.endpoint.wiki + 'main:faq:i_need_to_refine_my_search_results_or_i_want_to_search_for_something_very_specific._is_there_an_advanced_search_feature" class="filtershelp" title="Help on filters" target="_blank"><img alt="help on filters" src="/images/question_mark.gif" style="vertical-align: middle" border="0" /><span>?</span></a>',
				style: 'display:inline;padding:2px;padding-right:5px;'
			});
			fuzzy = new dijit.form.CheckBox({
				onChange: function(v) {
					_this.isFuzzy(v);
				},
				value: "fuzzy",
				title: "Try to match similar sounding keywords, based on the soundex phonetic algorithm"
			});

			fuzzy.attr("checked", this.isFuzzy());
			layout.addChild(bthelp);
			// change display to "inline" in order to unhide fuzzy search
			layout.addChild(new dijit.layout.ContentPane({content: "<span title='Try to match similar sounding keywords, based on the soundex phonetic algorithm'>Fuzzy</span>", style: "display:none;"}));
		}
		this._autocomplete = new appdb.utils.AutoComplete({parent: this, textbox: $(this.txtbox.domNode).find("input:last"), type: ((this.parent.views._export) ? this.parent.views._export.target : ($.trim(this.opt["type"]) || "apps"))});
		layout.placeAt($(this.dom)[0]);
		return this;
	};
	this.destroy = function() {
		this.clearObserver();
		this.reset();
	};
	this._constructor();
}, {
	watermarkColor: "#777",
	watermarkDefault: "Search..."
});
appdb.views.ExtendedFilter = appdb.ExtendClass(appdb.views.Filter, "appdb.views.ExtendedFilter", function(o) {
	this._targets = null;
	this._height = null;
	this._selectedTarget = null;
	this._targetButton = null;
	this._seperator = null;
	this._buttonHeight = 20;
	this._setSelectedTarget = function(v) {
		var change = (this._selectedTarget) ? ((this._selectedTarget.text !== v.text) ? true : false) : true;
		this._selectedTarget = v;
		this._targetButton.attr("label", "<div style='vertical-align:middle;'  title='" + (v.title || v.text) + "'><img src='" + v.image + "' width='" + this._buttonHeight + "px' alt='' ></img><span class='mainsearch-selection'>" + v.text + "</span></div>");
		
		if (change) {
			this.publish({event: "target", value: v});
		}
	};
	this.render = function() {
		this.reset();
		if (this._targetButton !== null) {
			this._targetButton.destoyRecursive(true);
			this._targetButton = null;
		}
		var layout = null, inlayout = null, btsearch = null, bthelp = null, fuzzy = null, _this = this;
		this.reset();
		if ($("#" + this.id).length > 0)
			this.dom = $("#" + this.id)[0];
		layout = new dijit.layout.LayoutContainer({
			id: this.id + this.idPostfix,
			style: "white-space: nowrap; margin-left: 0; margin-right: auto;padding:0px;display:inline-block;"
		});
		var w = ((this._width !== null) ? "width:" + this._width + ";" : ""), h = ((this._height !== null) ? "padding-top:" + this._height + ";padding-bottom:" + this._height + ";" : "");
		inlayout = new dijit.layout.LayoutContainer({
			style: "border:#3D3D3D 0px solid;display:inline-block;background-color:white;display:inline-block;border:1px solid grey"
		});

		this.txtbox = new dijit.form.TextBox({
			id: this.id + this.idPostfix + "simpleFilter",
			value: this.clearNormalization(this._currentValue || ""),
			title: "Keywords search, googlesque syntax applies",
			style: w + h + "border:none;padding-left:3px;"
		});

		if (this._currentValue === "" || this._currentValue === this._watermark) {
			this.txtbox.attr("value", this._watermark);
			this.txtbox.attr("style", {"color": "#3D3D3D"});
		}
		this._domEvents.push(dojo.connect(this.txtbox, "onFocus", this, function(k) {
			if (_this.txtbox.attr("value") == _this._watermark) {
				_this.txtbox.attr("value", "");
				_this.txtbox.attr("style", {"color": "inherit"});
			}
		}, true));
		this._domEvents.push(dojo.connect(this.txtbox, "onBlur", this, function(k) {
			var t = _this.txtbox;
			if (t.attr("value") === "" || t.attr("value") === _this._watermark) {
				t.attr("value", _this._watermark);
				t.attr("style", {"color": "#3D3D3D"});
			}
		}, true));
		this._domEvents.push(dojo.connect(this.txtbox, "onKeyUp", this, function(e) {
			var v = _this.txtbox.attr("displayedValue"), q, k = (window.event) ? event.keyCode : e.keyCode;
			_this._onValueChange(v);
			if (k == dojo.keys.ENTER) {
				var ret = _this.normalizeFilter(v, _this._selectedTarget);
				v = ret.normalForm;
				_this.txtbox.attr("value", v);
				if (ret.error)
					this.showFilterError(ret.error);
				this.doQuery(v, _this._selectedTarget);
			}
		}, true));

		this._domEvents.push(dojo.connect(this.txtbox, "onMouseDown", this, function(k) {
			if (_this.txtbox.attr("value") === _this._watermark) {
				_this.txtbox.attr("value", "");
				_this.txtbox.attr("style", {"color": "inherit"});
			}
		}, true));
		btsearch = new dijit.layout.ContentPane({
			content: '<a href="#" title="Search"><img src="' + loadImage("search.png") + '" style="vertical-align: middle" border="0" alt="" /></a>',
			style: 'display:inline;padding:2px;border:none;'
		});
		this._domEvents.push(dojo.connect(btsearch, "onClick", this, function() {
			var v = _this.txtbox.attr("value"), q;
			if (v === _this._watermark) {
				v = "";
			}
			var ret = _this.normalizeFilter(v, _this._selectedTarget);
			v = ret.normalForm;
			_this.txtbox.attr("value", v);
			if (ret.error)
				this.showFilterError(ret.error);
			this.doQuery(v, _this._selectedTarget);
		}, true));
		inlayout.addChild(this.txtbox);
		inlayout.addChild(btsearch);

		layout.addChild(inlayout);
		if (this._isRich === true || this.displayClear === true) {
			this.btclear = new dijit.layout.ContentPane({
				content: '<a href="#" title="Clear filter" ' + ((this._currentValue === "" || this._currentValue === this._watermark) ? 'style="display:none;"' : '') + ' ><img alt="clear filter" src="/images/cancelicon.png" style="vertical-align: middle" border="0" /></a>',
				style: 'display:inline;'
			});
			this._domEvents.push(dojo.connect(this.btclear, "onClick", this, function() {
				_this.txtbox.attr("value", _this._watermark);
				_this.txtbox.attr("style", {"color": "#3D3D3D"});
				var val = {fuzzySearch: _this.isFuzzy(), pageoffset: 0};
				val[_this._field] = '';
				_this.publish({event: "filter", value: val});
			}, true));
			inlayout.addChild(this.btclear);
		}
		if (this._isRich === true) {
			bthelp = new dijit.layout.ContentPane({
				content: '<a href="#" title="Help on filters" onclick="filterHelp(this);"><img alt="help on filters" src="/images/question_mark.gif" style="vertical-align: middle" border="0" /></a>',
				style: 'display:inline;padding:2px;padding-right:5px;'
			});
			fuzzy = new dijit.form.CheckBox({
				onChange: function(v) {
					_this.isFuzzy(v);
				},
				value: "fuzzy",
				title: "Try to match similar sounding keywords, based on the soundex phonetic algorithm"
			});

			fuzzy.attr("checked", this.isFuzzy());
			layout.addChild(bthelp);
			// change display to "inline" in order to unhide fuzzy search
			layout.addChild(new dijit.layout.ContentPane({content: "<span title='Try to match similar sounding keywords, based on the soundex phonetic algorithm'>Fuzzy</span>", style: "display:none;"}));
		}
		if (this._targets !== null) {
			var menu = new dijit.Menu({
				style: "display:none"
			});
			for (var i in this._targets) {
				this._targets[i].index = parseInt(i);
				menu.addChild(new dijit.MenuItem({
					label: "<div style='margin-left:-20px;vertical-align:middle;'><img src='" + this._targets[i].image + "' width='" + this._buttonHeight + "' alt='' ></img><span style='display:inline-block;vertical-align:top;padding-left:3px;padding-top:3px;'>" + this._targets[i].text + "</span></div>",
					onClick: (function(_this, v) {
						return function() {
							_this._setSelectedTarget(v);
							_this._autocomplete = new appdb.utils.AutoComplete({parent: _this, textbox: $(_this.txtbox.domNode).find("input:last"), type: v});
						};
					})(this, this._targets[i])
				}));
			}
			this._targetButton = new dijit.form.ComboButton({
				label: "",
				dropDown: menu,
				style: "margin:0px;padding:0px;border:#3D3D3D 0px solid;height:" + this._buttonHeight,
				onClick: (function(_this) {
					return function() {
						var ts = _this._targets, s = _this._selectedTarget, index = 0;
						if (s === null) {
							return;
						}
						if (s.index < ts.length - 1) {
							index = s.index + 1;
						}
						_this._setSelectedTarget(ts[index]);
					};
				})(this)
			});
			this._setSelectedTarget(this._targets[0]);
			if (this._separator !== null) {
				layout.addChild(new dijit.layout.ContentPane({content: this._seperator, style: "display:inline-block;padding:0px;vertical-align:top;"}));
			}
			layout.addChild(this._targetButton);
		}
		this._autocomplete = new appdb.utils.AutoComplete({parent: this, textbox: $(this.txtbox.domNode).find("input:last"), type: ((this._targets && this._targets.length > 0) ? this._targets[0] : "apps")});
		layout.placeAt($(this.dom)[0]);
		return this;
	};
	this._constructor = function() {
		if (this.opt.height) {
			if (isNaN(this.opt.height) === false) {
				if (this.opt.height > 0) {
					this._height = Math.round(this.opt.height / 2) + "px";
				}
			}
		}
		if (this.opt.buttonHeight) {
			if (isNaN(this.opt.buttonHeight) === false) {
				if (this.opt.buttonHeight > 0) {
					this._buttonHeight = Math.round(this.opt.buttonHeight) + "px";
				}
			}
		}
		if (this.opt.targets) {
			this._targets = this.opt.targets;
			
			if (this.opt.seperator) {
				this._seperator = this.opt.seperator;
			}
		}
	};
	this._constructor();
});
appdb.views.ResultTimer = appdb.ExtendClass(appdb.View, "appdb.views.ResultTimer", function(o) {
	this.render = function(cnt, msecs) {
		var secs;
		if (typeof msecs !== "undefined") {
			secs = ((msecs) / 1000);
		}
		$(this.dom).empty();
		var div = document.createElement("div");
		$(div).css({"font-size": "11px", "color": "gray"});
		$(div).append("<span style='white-space: nowrap'>" + cnt + " matches" + (typeof secs !== "undefined" ? " in " + secs + "s" : "") + "</span>");
		$(this.dom).append(div);
	};
	this.appendRender = function(msecs) {
		var secs = ((msecs) / 1000);
		if (appdb.config.appenv !== "production") {
			$(this.dom).append("<span style='white-space: nowrap'><div style='font-size:11px;color:gray'>Rendered in " + secs + " s</div></span>");
		}
	};
});
appdb.views.Permalink = appdb.ExtendClass(appdb.View, "appdb.views.Permalink", function(o) {
	this.datatype = "apps";
	this._permalink = "";
	this._constructor = function() {
		if (o.contents) {
			this.contents = $(o.contents).clone(true);
		} else if ($(this.dom).children(":not(.description):first").length !== 0) {
			this.contents = $(this.dom).children(":not(.description):first").clone(true);
		} else {
			this.contents = null;
		}
		this.container = $("<span></span>");
		this.datatype = o.datatype || "apps";
	};
	this.render = function(d, pager) {
		this.reset();
		u = appdb.Navigator.createPermalink(d, this.datatype);
		appdb.Navigator.setPermalink(u);
		this._permalink = u;
		$(this.container).empty().append("<a href='" + appdb.config.permalink + "' target='_blank' title='permalink for this search'>permalink</a>");
		if (this.contents) {
			$(this.container).children("a:first").empty().append(this.contents);
		}
		$(this.dom).append(this.container);
	};
	this.getCurrentPermalink = function() {
		return this._permalink;
	};
	this._constructor();
});
appdb.views.ListItem = appdb.ExtendClass(appdb.View, "appdb.views.ListItem", function(o) {
	this._itemData = null;
	this.getIndex = function() {
		return this._index;
	};
	this._constructor = function() {
		this._itemData = o.itemdata || null;
		this._index = o.index || -1;
	};
	this._constructor();
});
appdb.views.List = appdb.ExtendClass(appdb.View, "appdb.views.List", function(o) {
	this._listtype = null;
	this._listData = [];
	this._nodata = "";
	this.postRender = function() {
	};
	this._constructor = function() {
		if (typeof o.container === "string") {
			this.id = o.container;
			if ($(this.id).length > 0) {
				this.dom = $($(this.id)[0]);
			}
		} else {
			this.id = $(o.container).attr("id");
			this.dom = $(o.container);
		}
		this._listtype = o.listtype || appdb.views.ListItem;
		if (o.nodata) {
			this._nodata = $(o.nodata);
		} else {
			this._nodata = $(document.createElement("span")).text("No records returned with the specified criteria")[0];
		}
		if ($.isFunction(o.onPostRender) === true) {
			this.postRender = o.onPostRender;
		}
	};
	this.setListType = function(v) {
		this._listtype = v;
	};
	this.itemSubscriptions = [];
	this.filterItems = function(filter) {
		var i, d = this.subviews, len = d.length, res = [];
		if (typeof filter === "function") {
			for (i = 0; i < len; i += 1) {
				if (filter(d[i]) == true) {
					res[res.length] = d[i];
				}
			}
			return res;
		} else {
			return d;
		}
	};
	this.onBeforeItemRender = function() {
		return true;
	};
	this.onAfterItemRender = function() {
	};
	this.onItemAction = function(action, item) {
	};
	this.initItemSubscriptions = function(li) {
		var i, len = this.itemSubscriptions.length;
		for (i = 0; i < len; i += 1) {
			li.subscribe(this.itemSubscriptions[i]);
		}
	};
	this.appendItem = function(d) {
		var res = document.createElement("li"), li;
		$(res).addClass("itemcontainer");
		$(this.dom).append(res);
		if (d instanceof this._listtype) {
			li = d;
			li.setContainer(res);
		} else {
			li = new this._listtype({container: $(res)[0], itemdata: d});
			li.parent = this;
		}
		li.parent = this;
		if (this.onBeforeItemRender(li)) {
			this.initItemSubscriptions(li);
			li.render();
			this.onAfterItemRender(li);
			this.subviews[this.subviews.length] = li;
			return li;
		}
		return null;
	};
	this.render = function(d) {
		d = d || this._listData;
		if ($.isArray(d) === false)
			d = [d];
		var len = (d) ? ((typeof d.length !== "undefined") ? d.length : len) : 0, f = $(this.dom)[0];
		this.reset();
		if (len === 0) {
			$(f).append(this._nodata);
		} else {
			for (var i = 0; i < len; i += 1) {
				this.appendItem(d[i]);
			}
		}
		this.postRender();
	};
	this._constructor();
});


appdb.views.FilterBuilderItem = appdb.ExtendClass(appdb.views.ListItem, "appdb.views.FilterBuilderItem", function(o) {
	this._getFilterFields = new appdb.utils.filterFields().getObject;
	
	//Called from destroy. Must override to clear dojo components
	this.reset = function() {
		if (this._more) {
			this._more.destroyRecursive(false);
		}
		if (this._less) {
			this._less.destroyRecursive(false);
		}
		$(this.dom).empty();
	};

	//Initialiaze child controls
	this._init = function() {
		this._entityContainer = document.createElement("div");
		$(this._entityContainer).css("display", "inline-block");
		this._entityNode = document.createElement("div");

		this._fieldContainer = document.createElement("div");
		$(this._fieldContainer).css("display", "inline-block");
		$(this._fieldContainer).css("width", "110px");
		$(this._fieldContainer).addClass("fieldContainer");
		this._fieldNode = document.createElement("div");

		this._modifierContainer = document.createElement("div");
		$(this._modifierContainer).css("display", "inline-block");
		this._modifierNode = document.createElement("div");

		this._operatorContainer = document.createElement("div");
		$(this._operatorContainer).css("display", "inline-block");
		this._operatorNode = document.createElement("div");

		this._valueContainer = document.createElement("div");
		$(this._valueContainer).css("display", "inline-block");
	};

	this.expr = function() {
		var s = "", val, fld, ent;
		val = this._getVal();
		try {
			fld = this._field.store._arrayOfAllItems[this._field.value].name[0];
		} catch (e) {
			console.log(e);
		}
		if (val !== "") {
			val = val.replace(/"/g, '\\"');
			if (val.indexOf(" ") != "-1")
				val = '"' + val + '"';
			try {
				if (this._field.store._arrayOfAllItems[this._field.value].type[0] === "boolean") {
					if ((val === 0) || (val == "0") || (val === "false") || (val === false))
						val = "false";
					else
						val = "true";
				}
			} catch (e) {
			}
			ent = this._entity.store._arrayOfAllItems[this._entity.value].name[0];
			// HACK: map person immediate context to full context when property is "any" (mhaggel)
			if (ent === "person" && fld === "any")
				ent = "any";
			s = ent + "." + fld + ":" + val + "";
			switch (this._operator.store._arrayOfAllItems[this._operator.value].name[0]) {
				case "be":
					s = "=" + s;
					break;
				case "be greater than":
					s = ">" + s;
					break;
				case "be greater than or equal to":
					s = ">=" + s;
					break;
				case "be less than":
					s = "<" + s;
					break;
				case "be less than or equal to":
					s = "<=" + s;
					break;
				case "match regexp":
					s = "~" + s;
					break;
				case "sound like":
					s = "$" + s;
					break;
			}
			if (this._modifier.store._arrayOfAllItems[this._modifier.value].name[0] === "must") {
				s = "+" + s;
			} else if (this._modifier.store._arrayOfAllItems[this._modifier.value].name[0] === "must not") {
				s = "-" + s;
			}
		}

		//HACK: set scope to private by default. This should be configurable via combobox in the builder
		s = "&" + s;

		var filter = new appdb.views.Filter();
		var ret = filter.normalizeFilter(s, {value: "people"});
		if (typeof ret.normalForm !== "undefined") {
			return $.trim(ret.normalForm);
		} else {
			return $.trim(s);
		}
	};

	this._getFieldType = function() {
		var type = "string";
		try {
			type = this._field.store._arrayOfAllItems[this._field.value];
			if (type)
				type = type.type;
			else
				type = "string";
			if ($.isArray(type))
				type = type[0];
			if (typeof type === "undefined")
				type = "string";
		} catch (e) {
			console.log(e);
		}
		return type;
	};

	this._getVal = function() {
		var val = '', dateval;
		if (this._value) {
			try {
				val = this._value.textbox.value;
			} catch (e) {
				val = this._value.value;
			}
			if ((typeof val !== "undefined") && (val !== null)) {
				val = val.toString();
			} else {
				val = "";
			}
			try {
				dateval = $.datepicker.formatDate('yy-mm-dd', ($.datepicker.parseDate('D M dd yy', val)));
			} catch (e) {
			}
			if (typeof dateval !== "undefined")
				val = dateval;
		}
		if (typeof val !== "undefined")
			val = $.trim(val);
		else
			val = "";
		return val;
	};

	this._renderValueSelect = function() {
		var type = this._getFieldType(), val = '';
		if (this._value) {
			val = this._getVal();
			this._value.destroyRecursive(false);
			$(this._valueContainer).empty();
		}
		this._operator.set("disabled", "");
		$(this._operator.domNode).css("color", "black");
		switch (type) {
			case ("datetime"):
				this._value = new dijit.form.DateTextBox({
					"constraints": {
						"datePattern": "yyyy-MM-dd"
					},
					"width": "330px",
					"class": "itemvalue",
					"onKeyUp": (function(_this) {
						return function(e) {
							var k = (window.event) ? event.keyCode : e.keyCode;
							if (k == dojo.keys.ENTER) {
								_this.publish({event: "onOK"});
							}
							return true;
						};
					})(this)
				});
				break;
			case ("boolean"):
				this._operator.set("disabled", "disabled");
				this._setDijitComboValue(this._operator, "be");
				this._value = new dijit.form.FilteringSelect({
					"width": "330px",
					"class": "itemvalue",
					"store": new dojo.data.ItemFileReadStore({
						"data": {
							"label": "property",
							"items": [
								{"name": "false", "id": "false"},
								{"name": "true", "id": "true"}
							]
						}
					}),
					"searchAttr": "name",
					"onKeyUp": (function(_this) {
						return function(e) {
							var k = (window.event) ? event.keyCode : e.keyCode;
							if (k == dojo.keys.ENTER) {
								_this.publish({event: "onOK"});
							}
							return true;
						};
					})(this)
				});
				if (val === "true")
					val = 1;
				if (val === "false")
					val = 0;
				break;
			case ("string"):
			case ("complex"):
			case ("numeric"):
			default:
				this._value = new dijit.form.TextBox({
					"width": "330px",
					"class": "itemvalue",
					"onKeyUp": (function(_this) {
						return function(e) {
							var k = (window.event) ? event.keyCode : e.keyCode;
							if (k == dojo.keys.ENTER) {
								setTimeout(function() {
									_this.publish({event: "onOK"});
								}, 1);
							}
							return true;
						};
					})(this)
				});
				break;
		}
		try {
			if (type === "datetime") {
				this._value.setValue(new Date(val));
			} else {
				this._value.setValue(val);
			}
		} catch (e) {
			console.log(e);
		}
		$(this._valueContainer).append(this._value.domNode);
	};

	this._setDijitComboValue = function(o, v) {
		var i;
		if (o) {
			if (o.store) {
				if (o.store._arrayOfAllItems) {
					for (i = 0; i < o.store._arrayOfAllItems.length; i++) {
						if (o.store._arrayOfAllItems[i].name[0] === v) {
							o.setValue(i);
							return true;
						}
					}
				}
			}
		}
		return false;
	};

	this._renderFieldSelect = function() {
		var type = null, fields, d, i, j, items = [], oldVal, oldValIndex, newVal;
		if (this._field) {
			oldValIndex = this._field.value;
			oldVal = this._field.store._arrayOfAllItems[oldValIndex];
			if (oldVal) {
				if (oldVal.name)
					oldVal = oldVal.name[0];
			}
		}
		type = this._entity.store._arrayOfAllItems[this._entity.value].name[0];
		if ((type === null) || (type === "") || (type === undefined))
			type = o.type;
		// FIXME: properties should be retreived as the 2nd level fields from the filter reflection throught the API 	
		switch (type) {
			case "middleware":
			case "discipline":
			case "category":
				fields = new dojo.data.ItemFileReadStore({
					data: {
						label: "property",
						items: [
							{name: "any", id: "any"},
							{name: "id", id: "id"},
							{name: "name", id: "name"}
						]
					}
				});
				break;
			case "vo":
				fields = new dojo.data.ItemFileReadStore({
					data: {
						label: "property",
						items: [
							{name: "any", id: "any"},
							{name: "id", id: "id"},
							{name: "description", id: "description"},
							{name: "name", id: "name"}
						]
					}
				});
				break;
			case "country":
				fields = new dojo.data.ItemFileReadStore({
					data: {
						label: "property",
						items: [
							{name: "any", id: "any"},
							{name: "id", id: "id"},
							{name: "name", id: "name"},
							{name: "isocode", id: "isocode"}
						]
					}
				});
				break;
			case "any":
				fields = new dojo.data.ItemFileReadStore({
					data: {
						label: "property",
						items: [
							{name: "any", id: "any"}
						]
					}
				});
				break;
			case "application":
				fields = new dojo.data.ItemFileReadStore({
					data: {
						label: "property",
						items: (function() {
							return (new appdb.utils.filterFields()).getObject("people", "application");
						})()
					}
				});
				break;
			case "person":
				fields = new dojo.data.ItemFileReadStore({
					data: {
						label: "property",
						items: (function() {
							return (new appdb.utils.filterFields()).getObject("people", "person");
						})()
					}
				});
				break;
		}
		if (this._field)
			this._field.destroyRecursive(false);
		$(this._fieldContainer).empty();
		this._fieldNode = document.createElement("div");
		$(this._fieldContainer).append(this._fieldNode);
		if (this._field) {
			this._field.destroyRecursive(false);
		}
		this._field = new dijit.form.FilteringSelect({
			"style": "width:110px",
			"class": "fieldvalue",
			"store": fields,
			"searchAttr": "name",
			"onChange": (function(_this) {
				return function() {
					_this._renderValueSelect();
				};
			})(this)
		}, this._fieldNode);
		if (typeof this._field !== "undefined" && this._field.value === "") {
			this._field.setValue(0);
		}
	};

	this.setEntityValue = function(v) {
		this._setDijitComboValue(this._entity, v);
	};

	this.setPropertyValue = function(v) {
		this._setDijitComboValue(this._field, v);
	};

	this.setModifierValue = function(v) {
		this._setDijitComboValue(this._modifier, v);
	};

	this.setOperatorValue = function(v) {
		var val;
		switch (v) {
			case ("="):
				val = "be";
				break;
			case ("<"):
				val = "be less than";
				break;
			case ("<="):
				val = "be less than or equal to";
				break;
			case (">"):
				val = "be greater than";
				break;
			case (">="):
				val = "be greater than or equal to";
				break;
			case ("~"):
				val = "match regexp";
				break;
			case ("$"):
				val = "sound like";
				break;
			default:
				val = "contain";
				break;
		}
		this._setDijitComboValue(this._operator, val);
	};

	this.setKeywordValue = function(v) {
		if (v.substr(0, 1) === '"' && v.substr(-1, 1) === '"')
			v = v.substr(1, v.length - 2);
		this._value.setValue(v);
	};

	this.render = function() {
		var i, modifiers, operators;
		this.reset();
		// FIXME: entities should be retreived as the first level fields from the filter reflection throught the API 	
		switch (o.type) {
			case "application":
			case "person":
				entities = [
					{name: "any", id: "any"},
					{name: "application", id: "application"},
					{name: "country", id: "country"},
					{name: "discipline", id: "discipline"},
					{name: "category", id: "category"},
					{name: "middleware", id: "middleware"},
					{name: "person", id: "person"},
					{name: "vo", id: "vo"}
				];
				break;
		}

		modifiers = [
			{name: "may", id: "may"},
			{name: "must", id: "must"},
			{name: "must not", id: "must not"}
		];

		operators = [
			{name: "contain", id: "contain"},
			{name: "be", id: "be"},
			{name: "be greater than", id: "be greater than"},
			{name: "be greater than or equal to", id: "be greater than or equal to"},
			{name: "be less than", id: "be less than"},
			{name: "be less than or equal to", id: "be less than or equal to"},
			{name: "match regexp", id: "match regexp"},
			{name: "sound like", id: "sound like"}
		];

		if (this._entity)
			this._entity.destroyRecursive(false);
		this._entity = new dijit.form.FilteringSelect({
			style: "width: 110px",
			"class": "entityvalue",
			store: new dojo.data.ItemFileReadStore({
				data: {
					label: "entity",
					items: entities
				}
			}),
			"searchAttr": "name",
			"onChange": (function(_this) {
				return function() {
					_this._renderFieldSelect();
					_this._renderValueSelect();
				};
			})(this)
		}, this._entityNode);
		this._entity.setValue(0);

		if (this._modifier)
			this._modifier.destroyRecursive(false);
		this._modifier = new dijit.form.FilteringSelect({
			style: "width: 95px",
			"class": "modvalue",
			store: new dojo.data.ItemFileReadStore({
				data: {
					label: "modifier",
					items: modifiers
				}
			}),
			"searchAttr": "name"
		}, this._modifierNode);
		this._modifier.setValue(0);

		if (this._operator)
			this._operator.destroyRecursive(false);
		this._operator = new dijit.form.FilteringSelect({
			style: "width: 200px",
			"class": "opvalue",
			store: new dojo.data.ItemFileReadStore({
				data: {
					label: "operator",
					items: operators
				}
			}),
			"searchAttr": "name"
		}, this._operatorNode);
		this._operator.setValue(0);

		if (this._value)
			this._value.destroyRecursive(false);
		this._value = new dijit.form.TextBox({
			"style": "width: 330px",
			"class": "itemvalue",
			"onKeyUp": (function(_this) {
				return function(e) {
					var k = (window.event) ? event.keyCode : e.keyCode;
					if (k == dojo.keys.ENTER) {
						_this.publish({event: "onOK"});
					}
					return true;
				};
			})(this)
		});

		$(this.dom).append(this._entityContainer);
		$(this._entityContainer).empty().append(this._entity.domNode);

		$(this._fieldContainer).append(this._fieldNode);
		$(this.dom).append(this._fieldContainer);

		$(this.dom).append(this._modifierContainer);
		$(this._modifierContainer).empty().append(this._modifier.domNode);

		$(this.dom).append(this._operatorContainer);
		$(this._operatorContainer).empty().append(this._operator.domNode);

		$(this.dom).append(this._valueContainer);
		$(this._valueContainer).empty().append(this._value.domNode);

		this._renderFieldSelect();

		var morebutton = document.createElement("div");
		$(this.dom).append(morebutton);
		this._more = new dijit.form.Button({label: "+", onClick: (function(_this) {
				return function() {
					_this.publish({event: "onMore", value: this._index});
				};
			})(this)}, morebutton);

		var lessbutton = document.createElement("div");
		$(this.dom).append(lessbutton);
		this._less = new dijit.form.Button({label: "-", onClick: (function(_this) {
				return function() {
					_this.publish({event: "onLess", value: _this.getIndex()});
				};
			})(this)}, lessbutton);
		dojo.parser.parse(this.dom);
	};

	this._init();
});

appdb.views.FilterBuilder = appdb.ExtendClass(appdb.views.List, "appdb.views.FilterBuilder", function(o) {
	this.destroy = function() {
		this._clearItems();
		if (this._ok) {
			this._more.destroyRecursive(false);
		}
		if (this._clear) {
			this._less.destroyRecursive(false);
		}
		this.reset();
	};

	this._setDijitComboValue = function(o, v) {
		var i;
		if (o) {
			if (o.store) {
				if (o.store._arrayOfAllItems) {
					for (i = 0; i < o.store._arrayOfAllItems.length; i++) {
						if (o.store._arrayOfAllItems[i].name[0] === v) {
							o.setValue(i);
							return true;
						}
					}
				}
			}
		}
		return false;
	};

	this.load = function(flt) {
		var i, j, item;
		this._clearItems();
		if (flt.field) {
			if (!$.isArray(flt.field))
				flt.field = [flt.field];
			for (i = 0; i < flt.field.length; i++) {
				if (!$.isArray(flt.field[i].field))
					flt.field[i].field = [flt.field[i].field];
				for (j = 0; j < flt.field[i].field.length; j++) {
					item = this._addItem();
					item.setEntityValue(flt.field[i].name);
					setTimeout((function(_this, _item, _val) {
						return function() {
							_item.setPropertyValue(_val);
						};
					}(this, item, flt.field[i].field[j].name)), 1);
					if ((flt.field[i].field[j].required) && (flt.field[i].field[j].required == "true")) {
						if ((flt.field[i].field[j].negated) && (flt.field[i].field[j].negated == "true")) {
							item.setModifierValue("must not");
						} else {
							item.setModifierValue("must");
						}
					}
					if ((flt.field[i].field[j].operator)) {
						item.setOperatorValue(flt.field[i].field[j].operator);
					}
					item.setKeywordValue(appdb.utils.base64.decode(flt.field[i].field[j].val()));
				}
			}
		}
		try {
			if (this._listData[0]._getVal() === "")
				this._removeItem(0);
		} catch (e) {
			console.log(e);
		}
	};


	this._buildFilter = function() {
		var i, s = "";
		for (i = 0; i < this._listData.length; i++) {
			s = s + this._listData[i].expr() + " ";
		}
		return $.trim(s);
	};

	this._createHeader = function() {
		var header;
		header = $(document.createElement("div"));
		header.addClass("disseminationtool header");
		header.append($(document.createElement("div")).attr("style", "display: inline-block; margin-left: 3px; width:110px").html("Context"));
		header.append($(document.createElement("div")).attr("style", "display: inline-block; margin-left: 3px; width:110px").html("Property"));
		header.append($(document.createElement("div")).attr("style", "display: inline-block; margin-left: 3px; width:95px").html("Modifier"));
		header.append($(document.createElement("div")).attr("style", "display: inline-block; margin-left: 3px; width:200px").html("Operator"));
		header.append($(document.createElement("div")).attr("style", "display: inline-block; margin-left: 3px; width:180px").html("Keyword"));
		return header;
	};

	this._createFooter = function() {
		var buttons = $(document.createElement("div"));
		buttons.attr("style", "text-align: right;");
		var okbutton = document.createElement("div");
		var clearbutton = document.createElement("div");
		buttons.append(okbutton);
		buttons.append(clearbutton);
		this._ok = new dijit.form.Button({label: "OK", onClick: (function(_this) {
				return function() {
					_this.publish({event: "onOK", value: _this._buildFilter()});
				};
			})(this)}, okbutton);

		this._clear = new dijit.form.Button({label: "Clear", onClick: (function(_this) {
				return function() {
					_this._clearItems();
					_this.publish({event: "onClear", value: this._index});
				};
			})(this)}, clearbutton);
		return buttons;
	};

	this._createBody = function() {
		var body = $(document.createElement("ul"));
		body.css("margin-left", "-40px");
		body.css("margin-top", "0px");
		body.css("max-height", "128px");
		body.css("overflow-y", "auto");
		return body;
	};

	this._init = function() {
		this._constructor();
		this._header = this._createHeader();
		$(this.dom).append(this._header);
		this._body = this._createBody();
		$(this.dom).append(this._body);
		this._footer = this._createFooter();
		$(this.dom).append(this._footer);
		this.dom = this._body;
		this._addItem();
	};

	this._addItem = function() {
		this._listData[this._listData.length] = new appdb.views.FilterBuilderItem({container: this._body, type: o.type, index: this._listData.length, itemdata: {}}).subscribe({event: "onMore", callback: function() {
				this._addItem();
				$(this.dom).scrollTo("100%", 800, {queue: true});
			}, caller: this}).subscribe({
			event: "onLess", callback: function(v) {
				this._removeItem(v);
			}, caller: this
		}).subscribe({
			event: "onOK", callback: function(v) {
				this.publish({event: "onOK", value: this._buildFilter()});
			}, caller: this
		});

		//This is a base function.
		this.appendItem(this._listData[this._listData.length - 1]);
		//Render lastly appended list item
		this._listData[this._listData.length - 1].render();
		return this._listData[this._listData.length - 1];
	};

	this._removeItem = function(v) {
		try {
			this._listData[v].destroy();
			this._listData.splice(v, 1);
			//reset the indices of the items in the collection
			for (var i = 0; i < this._listData.length; i += 1) {
				this._listData[i]._index = i;
			}
			//Enforce at least one item visible
			if (this._listData.length === 0) {
				this._addItem();
			}
		} catch (e) {
			console.log(e);
		}
	};

	this._clearItems = function() {
		var i;
		for (i = this._listData.length - 1; i >= 0; i--) {
			this._removeItem(i);
		}
	};

	this._init();
});

appdb.views.PeopleListItem = appdb.ExtendClass(appdb.views.ListItem, "appdb.views.PeopleListItem", function(o) {
	this.render = function(d) {
		this._itemData = d || this._itemData;
		d = this._itemData;
		var doc = document, fullname = d.firstname + " " + d.lastname, a, img, div, span, span1, span2, isos, i, flags = "", pimg, ishttps = Boolean(appdb.config.https) && true, prot;
		isos = d.country.isocode.split("/");
		for (i = 0; i < isos.length; i += 1) {
			flags += "<img width='16px' src='/images/flags/" + isos[i].toLowerCase() + ".png' border='0' />";
		}
		flags = "<span class='personflags' >" + flags + "</span>";
		var permlink = appdb.utils.getItemCanonicalUrl("person", d);
		a = $(doc.createElement("a")).attr("href", permlink).attr("target", "_blank").attr("title", fullname).addClass("itemlink").click((function(_this, data) {
			return function() {
				_this.publish({event: "click", value: data});
				return false;
			};
		})(this, d));
		pimg = d.image;

		if (pimg) {
			prot = pimg.substr(4, 1);
			if (ishttps === true && prot === ":") {
				pimg = "https" + pimg.substr(4, pimg.length);
			} else if (prot === "https") {
				pimg = "http:" + pimg.substr(5, pimg.length);
			}
		}
		div = doc.createElement("div");
		$(div).addClass("item");
		img = $(doc.createElement("img")).
				attr("src", ((pimg) ? "/people/getimage?req=" + encodeURI(d.lastUpdated) + "&id=" + d.id : (appdb.config.images.person))).addClass("itemimage");
		span = $(doc.createElement("span")).append(fullname).addClass("itemname");
		if (isos.length > 1) {
			$(span).addClass("flagcount" + isos.length);
		}
		span1 = $("<span></span>").append(unescape(d.role.type) + "<br/>" ).addClass("itemsorttext");
		span2 = $("<span></span>").append(unescape(d.role.type) + "<br/>" + d.country.val() + "<br/>Registered since " + d.registeredOn).addClass("itemlongtext");
		$(a).append(img);
		$(a).append(flags);
		$(a).append(span);
		$(a).append(span1);
		$(a).append(span2);
		if (d.deleted) {
			if (Boolean(d.deleted) == true) {
				deleted = true;
				$(this.dom).addClass("deleted");
				title = "This profile has been deleted.";
				$(a).attr("title", title);
			}
		}
		$(div).append($(a));
		$(this.dom).append(div);
	};
	this.reset = function() {
		$(this.dom).find("div > a").each(function(index, elem) {
			$(this).unbind("click");
		});
		$(this.dom).empty();
	};
});

appdb.views.PeopleList = appdb.ExtendClass(appdb.views.List, "appdb.views.PeopleList", function(o) {
	this._constructor = function() {
		this.setListType(appdb.views.PeopleListItem);
		this.itemSubscriptions = [
			{event: "click", callback: function(data) {
					if (this.parent && this.parent.views.pager) {
						this.publish({event: "itemclick", value: data});
					} else {
						appdb.views.Main.showPerson({id: data.id, cname: data.cname}, {mainTitle: data.firstname + " " + data.lastname, append: true});
					}
				}, caller: this}];
		$(this.dom).addClass("peoplelist");
	};
	this._constructor();
});
appdb.views.RelatedContactListItem = appdb.ExtendClass(appdb.views.ListItem, "appdb.views.RelatedContactListItem", function(o) {
	this.isSelectable = false;
	this.useToggleButton = false;
	this.customSelectable = undefined;
	this._isSelected = false;
	this.canSetContactPoint = false;
	this._selectToggle = null;
	this.isSelected = function(sel) {
		if (this.isSelectable == false) {
			return false;
		}
		if (typeof sel !== "undefined") {
			if (this._isSelected != sel) {
				this._isSelected = sel;
			} else {
				return this._isSelected;
			}
		} else {
			return this._isSelected;
		}
		var c = $(this._selectToggle.domNode);
		if (this._isSelected) {
			this._selectToggle.attr("checked", true);
			$(this._selectToggle.domNode).attr("title", "Click to unselect contact");
			$(c).parent().parent().parent().addClass("selected");
			if (this.useToggleButton == false) {
				$(c).before("<span class='selectedtext'>selected</span>");
			}
		} else {
			this._selectToggle.attr("checked", false);
			$(this._selectToggle.domNode).attr("title", "Click to select contact");
			$(c).parent().parent().parent().removeClass("selected");
			if (this.useToggleButton == false) {
				$(c).parent().find("span:first").remove();
			}
		}
		this.publish({event: "selected", value: this});
		return this._isSelected;
	};
	this.editContactPoint = function() {
		if (this.parent._application == null) {
			alert("cannot find software");
		} else {
			if (this.subviews.length > 0) {
				this.subviews[0].destroy();
				this.subviews[0] = null;
			}
			this.subviews[0] = new appdb.views.ContactPointEditor({container: document.createElement("div"), listitem: this});
			this.subviews[0].render();
		}
	};
	this.renderSelectable = function() {
		if (this.isSelectable) {
			var div = $(this.dom).find(".item:last");
			var span3 = document.createElement("span"), check = document.createElement("span");
			$(span3).addClass("researcherCheck");
			if (this.useToggleButton == true) {
				$(span3).addClass("togglebutton");
			}
			$(span3).append(check);
			$(div).append(span3);
			if (this._selectToggle) {
				this._selectToggle.desctroyRecursive(false);
				this._selectToggle = null;
			}
			if (this.useToggleButton) {
				this._selectToggle = new dijit.form.ToggleButton({
					checked: this.isSelected(),
					showLabel: true,
					iconClass: "dijitCheckBoxIcon",
					label: "remove",
					title: "Set for removal",
					onChange: (function(_this) {
						return function(v) {
							_this.isSelected(v);
							this.attr("title", ((v == true) ? "Un" : "") + "set for removal");
						};
					})(this)
				},
				check);
			} else {
				this._selectToggle = new dijit.form.CheckBox({
					checked: this.isSelected(),
					onChange: (function(_this) {
						return function(v) {
							_this.isSelected(v);
						};
					})(this)
				},
				check);
			}
		}
	};
	this.hasChanges = function() {
		var ci = [], len, i;
		if (this._itemData.isNew && (this._itemData.toRemove == true || this._itemData.toRemoveImplicit == true) === false) {
			return true;
		} else if ((this._itemData.toRemove == true || this._itemData.toRemoveImplicit == true) && (this._itemData.isNew == false)) {
			return true;
		} else if ((this._itemData.toRemove == true || this._itemData.toRemoveImplicit == true) && (this._itemData.isNew == true)) {
			return false;
		}
		if (this._itemData.contactItem) {
			ci = this._itemData.contactItem;
			if ($.isArray(ci) === false) {
				ci = [ci];
			}
		}
		len = ci.length;
		for (i = 0; i < len; i += 1) {
			if (ci[i].isNew || ci[i].toRemove || ci[i].toRemoveImplicit) {
				return true;
			}
		}
		return false;
	};
	this.renderContactPoints = function() {
		var i, len, d = [], div = $(this.dom).find(".item:last"), view = document.createElement("div"), lnk = document.createElement("a"), popupmsg = '';
		if (this._itemData.contactItem) {
			if ($.isArray(this._itemData.contactItem) == false) {
				this._itemData.contactItem = [this._itemData.contactItem];
			}
			d = this._itemData.contactItem;
		}
		len = d.length;

		//VIEW PART
		$(view).addClass("relatedcontactpointview");
		if (d.length > 0) {
			//Build title
			var cptitlesort, cptitle = "";
			for (i = 0; i < len; i += 1) {
				cptitle += (d[i].val) ? d[i].val() : '';
				if (i + 1 < len) {
					cptitle += ", ";
				}
			}
			cptitlesort = cptitle;
			if (cptitlesort.length > 20) {
				cptitlesort = cptitlesort.slice(0, 17) + "...";
			}
			cptitle = $("<span title='" + cptitle + "'>Contact point for " + cptitlesort + "</span>");
			$(view).append(cptitle);
			//build details to show in popup
			var details = "<div class='relatedcontactpointview details' >";
			details += "<span class='title' >" + this._itemData.lastname + " is acting as a contact point for this software and relates with the following entities:</span>";
			var gd = appdb.utils.GroupObjectList(d, "type");
			for (i in gd) {
				details += "<div class='contactpoint' ><b>-</b> " + ((i.toLowerCase() === 'other') ? '' : i + " : ");
				for (var j = 0; j < gd[i].length; j += 1) {
					details += (gd[i][j].val) ? gd[i][j].val() : '';
					if (j + 1 < gd[i].length) {
						details += ", ";
					}
				}
				details += "</div>";
			}
			details += "<div class='contactpointprofile'>Click <a href='/store/person/" + this._itemData.cname + "' title='Go to " + this._itemData.firstname + " " + this._itemData.lastname + " profile' onclick='appdb.views.Main.showPerson({id: " + this._itemData.id + ", cname:\"" + this._itemData.cname + "\"},{mainTitle: \"" + this._itemData.firstname + " " + this._itemData.lastname + "\"});'>here</a> to see " + this._itemData.firstname + " " + this._itemData.lastname + " profile</div>";
			details += "</div>";
			var detailslink = document.createElement("a");
			$(detailslink).attr("href", "#").attr("title", "Click to view contact points").text("details").click((function(__this, msg) {
				return function() {
					var pu = new dijit.TooltipDialog({content: msg});
					setTimeout((function(_this) {
						return function() {
							dijit.popup.open({
								parent: $(_this)[0],
								popup: pu,
								around: $(_this)[0],
								orient: {'TL': 'BL', 'TR': 'BR'}
							});
						};
					})(__this), 1);
				};
			})(detailslink, details));
			$(view).append(detailslink);
		}
		$(div).append(view);
		//END VIEW PART

		//EDIT PART
		if (this.canSetContactPoint) {
			var edit = document.createElement("span"), editlnk = document.createElement("a");
			$(edit).addClass("editcontactpoint");
			$(editlnk).attr("href", "#").attr("title", ((d.length == 0) ? "Set" : "Edit ") + " " + this._itemData.firstname + " " + this._itemData.lastname + "  expertise").text(((d.length == 0) ? "Set expertise" : "Edit expertise"));
			$(editlnk).click((function(_this) {
				return function() {
					_this.editContactPoint();
				};
			})(this));
			$(edit).append(editlnk);
			$(div).append(edit);
		}
		//END EDIT PART
	};
	this.render = function(d) {
		this._itemData = d || this._itemData;
		d = this._itemData;
		var doc = document, fullname = d.firstname + " " + d.lastname, a, img, div, span, span1, span2, isos, i, flags = "", pimg, ishttps = Boolean(appdb.config.https) && true, prot;
		if (d.country && d.country.isocode) {
			isos = d.country.isocode.split("/");
			for (i = 0; i < isos.length; i += 1) {
				flags += "<img width='16px'  src='/images/flags/" + isos[i].toLowerCase() + ".png' border='0' />";
			}
		}
		flags = "<span class='personflags'>" + flags + "</span>";
		fullname = $.trim(fullname).replace(/\</g, "&lt;").replace(/\>/g, "&gt;");
		var permlink = appdb.utils.getItemCanonicalUrl("person", d);
		a = $(doc.createElement("a")).attr("href", (permlink || "#")).attr("title", fullname).addClass("itemlink").click((function(_this, data) {
			return function() {
				if (_this.parent.EditMode()) {
					if (_this.useToggleButton == false) {
						_this.isSelected(!_this.isSelected());
					}
				} else {
					_this.publish({event: "click", value: _this});
				}
				return false;
			};
		})(this, d));
		pimg = d.image;
		if (pimg) {
			prot = pimg.substr(4, 1);
			if (ishttps === true && prot === ":") {
				pimg = "https" + pimg.substr(4, pimg.length);
			} else if (prot === "https") {
				pimg = "http:" + pimg.substr(5, pimg.length);
			}
		}
		div = doc.createElement("div");
		$(div).addClass("item");
		img = $(doc.createElement("img")).
				attr("src", ((pimg) ? "/people/getimage?req=" + encodeURI(d.lastUpdated) + "&id=" + d.id : (appdb.config.images.person))).addClass("itemimage");
		span = $(doc.createElement("span")).append(fullname).addClass("itemname");
		if (isos && isos.length > 1) {
			$(span).addClass("flagcount" + isos.length);
		}
		span1 = $("<span></span>").append(unescape(d.role.type) + "<br/>" ).addClass("itemsorttext");
		span2 = $("<span></span>").append(unescape(d.role.type) + "<br/>" + d.country.val() + "<br/>Registered since " + d.registeredOn).addClass("itemlongtext");
		$(a).append(img);
		$(a).append(flags);
		$(a).append(span);
		$(a).append(span1);
		$(a).append(span2);
		$(div).append($(a));
		$(this.dom).append(div);

		this.renderContactPoints();
		this.renderSelectable();
	};
	this.reset = function() {
		$(this.dom).find("div > a").each(function(index, elem) {
			$(this).unbind("click");
		});
		$(this.dom).empty();
	};
});
appdb.views.RelatedContactList = appdb.ExtendClass(appdb.views.List, "appdb.views.RelatedContactList", function(o) {
	this._permissions = null;
	this._application = null;
	this.canSetContactPoint = true;
	this.excludedDataItems = new appdb.utils.UniqueDataList();
	this.selectedDataItems = new appdb.utils.UniqueDataList();
	this.addNewContacts = function(cnts) {
		cnts = cnts || [];
		cnts = $.isArray(cnts) ? cnts : [cnts];
		var i, len = cnts.length;
		for (i = 0; i < len; i += 1) {
			cnts[i].isNew = true;
			this._listData[this._listData.length] = cnts[i];
		}
	};
	this.setupForm = function(frm) {
		if (typeof frm === "undefined") {
			return;
		}
		var i, j, v = this.subviews, len = v.length, index = 0, inp;
		for (i = 0; i < len; i += 1) {
			if (v[i].isSelected() == false) {
				inp = document.createElement("input");
				$(inp).attr("type", "hidden").attr("name", "scicon" + index).attr("value", v[i]._itemData.id);
				$(frm).append(inp);
				index += 1;
			}
		}
		index = 0;
		for (i = 0; i < len; i += 1) {
			if (v[i].isSelected() == false) {
				var c = v[i]._itemData || {};
				var ca = c.contactItem || [];
				ca = ($.isArray(ca) == true) ? ca : [ca];
				for (j = 0; j < ca.length; j += 1) {
					var ci = ca[j];
					if (ci.nil && ci.nil === "true") {
						continue;
					}
					if (typeof ci.toRemove !== "undefined") {
						if (ci.toRemove == true) {
							continue;
						}
					}
					if (typeof ci.toRemoveImplicit !== "undefined") {
						if (ci.toRemoveImplicit == true) {
							continue;
						}
					}
					inp = document.createElement("input");
					if (ci.id.indexOf(":") > -1) {
						ci.id = ci.id.split(":")[0];
					}
					var jsn = {researcherid: c.id, itemtype: ci.type.toLowerCase(), itemid: ((ci.type.toLowerCase() === 'other') ? '' : ci.id), item: $("<span>" + ci.val() + "</span>").text()};
					$(inp).attr("type", "hidden").attr("name", "cntpnt" + index).attr("value", JSON.stringify(jsn));
					$(frm).append(inp);
					index += 1;
				}
			}
		}
	};
	this._constructor = function() {
		if (o.data) {
			if ($.isArray(o.data)) {
				this._listData = o.data;
			} else if (o.data.contact) {
				if ($.isArray(o.data.contact) === false) {
					this._listData = [o.data.contact];
				} else {
					this._listData = o.data.contact;
				}
				this._application = o.data;
			}
		}
		if (typeof o.canSetContactPoint !== "undefined") {
			this.canSetContactPoint = o.canSetContactPoint;
		}
		if ($.isArray(o.excluded)) {
			this.excludedDataItems = new appdb.utils.UniqueDataList({data: o.excluded});
		}
		if ($.isFunction(o.onExclude)) {
			this.onExclude = o.onExclude;
		}
		if (o.permissions && o.permissions.action) {
			this._permissions = new appdb.utils.Privileges(o.permissions);
			if (this._permissions.canDisassociatePersonFromApplication()) {
				this.onBeforeItemRender = function(li) {
					if (o.useToggleButton) {
						li.useToggleButton = o.useToggleButton;
					}
					li.isSelectable = true;
					li.canSetContactPoint = this.canSetContactPoint;
					return li;
				};
				this.onAfterItemRender = function(li) {
					if (this.selectedDataItems.has(li._itemData)) {
						li.isSelected(true);
					}
					if (this.excludedDataItems.has(li._itemData)) {
						$(li.dom).addClass("excluded");
						if (this.onExclude) {
							this.onExclude(li);
						}
					}
				};
			}
		}
		this.disableCheckForChanges = o.disableCheckForChanges || false;
		this.setListType(appdb.views.RelatedContactListItem);
		this.itemSubscriptions = [
			{event: "click", callback: function(elem) {
					var data = elem._itemData;
					if (this.parent && this.parent.views.pager) {
						this.publish({event: "itemclick", value: {list: this, item: elem}});
					} else {
						appdb.views.Main.showPerson({id: data.id, cname: data.cname}, {mainTitle: data.firstname + " " + data.lastname, append: true});
					}
				}, caller: this},
			{event: "selected", callback: function(elem) {
					if (elem.isSelected()) {
						this.selectedDataItems.add(elem._itemData);
					} else {
						this.selectedDataItems.remove(elem._itemData);
					}
					this.publish({event: "itemselected", value: {list: this, item: elem}});
					this.checkChangeState();
				}, caller: this}
		];
		$(this.dom).addClass("relatedcontacts");
		this.postRender = function() {
			setTimeout((function(_this) {
				return function() {
					if (_this.EditMode()) {
						_this.checkForChanges();
					}
				};
			})(this), 10);
		};
	};
	this.checkChangeState = function() {
		var cnts = this.deltaContacts();
		$(this.dom).parent().removeClass("markremove");
		if (cnts.removed.length > 0) {
			$(this.dom).parent().addClass("markremove");
		}
	};
	this.checkForChanges = function() {
		if (this.disableCheckForChanges) {
			return;
		}
		var i, items = this.subviews || [], len = items.length;
		for (i = 0; i < len; i += 1) {
			if (items[i]._itemData) {
				appdb.views.RelatedContactList.CheckForChanges.apply(this, [items[i]]);
			}
		}
	};
	this.hasChanges = function() {
		if (this.disableCheckForChanges) {
			return false;
		}
		var i, len = this.subviews.length;
		for (i = 0; i < len; i += 1) {
			if (this.subviews[i].hasChanges()) {
				return true;
			}
		}
		return false;
	};
	this.EditMode = (function(_this) {
		var inEdit = false;
		return function(isedit) {
			if (typeof isedit !== "undefined") {
				if (isedit == true) {
					inEdit = true;
					$(_this.dom).addClass("editmode");
				} else {
					inEdit = false;
					$(_this.dom).removeClass("editmode");
				}
			}
			return inEdit;
		};
	})(this);
	this.getCurrentDataItems = function() {
		var res = [];
		$.each(this._listData, function(i, e) {
			res.push(e);
		});
		return res;
	};
	this.deltaContacts = function() {
		var current = this.getCurrentDataItems();
		var result = {added: [], removed: [], rest: []};
		$.each(current, function(i, e) {
			if (e.isNew === true && !e.toRemove) {
				result.added.push(e);
			} else if (e.toRemove === true && !e.isNew) {
				result.removed.push(e);
			} else {
				result.rest.push(e);
			}
		});
		return result;
	};
	this._constructor();
}, {
	ChangesDialog: null,
	CheckForChanges: function(item) {
		var i, len, ischanged = {contactpoints: {value: false, reasons: []}, contact: {value: false, reasons: []}}, changeset = [], data = item._itemData || item, d = data.contactItem || [];
		if ((typeof data.isNew !== "undefined" && data.isNew == true)) {
			if (((typeof data.toRemove !== "undefined" && data.toRemove == true) || (typeof data.toRemoveImplicit != "undefined" && data.toRemoveImplicit == true)) == false) {
				ischanged.contact.value = true;
				ischanged.contact.reasons[ischanged.contact.reasons.length] = "Newly added contact.";
			}
		} else if ((typeof data.toRemove != "undefined" && data.toRemove == true) || (typeof data.toRemoveImplicit !== "undefined" && data.toRemoveImplicit == true)) {
			if ((typeof data.isNew !== "undefined" && data.isNew == true) == false) {
				ischanged.contact.value = true;
				ischanged.contact.reasons[ischanged.contact.reasons.length] = "Remove contact.";
			}
		}

		if ($.isArray(d) === false) {
			d = [d];
		}
		len = d.length;
		for (i = 0; i < len; i += 1) {
			if ((typeof d[i].isNew !== "undefined" && d[i].isNew == true)) {
				if (((typeof d[i].toRemove !== "undefined" && d[i].toRemove == true) || (typeof d[i].toRemoveImplicit !== "undefined" && d[i].toRemoveImplicit == true)) == false) {
					ischanged.contactpoints.value = true;
					break;
				}
			} else if ((typeof d[i].toRemove !== "undefined" && d[i].toRemove == true) || (typeof d[i].toRemoveImplicit !== "undefined" && d[i].toRemoveImplicit == true)) {
				if ((typeof d[i].isNew !== "undefined" && d[i].isNew == true) == false) {
					ischanged.contactpoints.value = true;
					break;
				}
			}
		}
		if (ischanged.contactpoints.value == true) {
			ischanged.contactpoints.reasons[ischanged.contactpoints.reasons.length] = "Changes in expertise";
		}

		changeset = ischanged.contact.reasons.concat(ischanged.contactpoints.reasons);
		if (changeset.length > 0) {
			if (ischanged.contactpoints.value == true) {
				if ($(".contactpointlistcontainer").find(".mustsave").length === 0) {
					$(".contactpointlistcontainer").append("<div class='mustsave' ><img src='/images/diskette.gif' title='' alt=''/><span>The software needs to be saved first for the changes to take effect.</span></div>");
					$(".contactpointlistcontainer").find(".nodata").remove();
				}
			}
			if ($(item.dom).find(".haschanges").length > 0) {
				$(item.dom).find(".haschanges").remove();
			}
			if ($(item.dom).find(".haschanges").length === 0) {
				var span = document.createElement("span");
				$(span).addClass("haschanges").append("<img src='/images/diskette.gif' />");
				$(item.dom).append(span);
				$(span).mouseover(function() {
					if (appdb.views.RelatedContactList.ChangesDialog != null) {
						dijit.popup.close(appdb.views.RelatedContactList.ChangesDialog);
						appdb.views.RelatedContactList.ChangesDialog.destroyRecursive(false);
						appdb.views.RelatedContactList.ChangesDialog = null;
					}
					var msg = "<div style='font-size:10px;width:200px;'>There are changes regarding this contact listed bellow.<ul style='padding-top:4px;padding-bottom:3px;padding-left:14px;'>";
					for (var i = 0; i < changeset.length; i += 1) {
						msg += "<li style='padding-left:2px;margin-left:0px;'>" + changeset[i] + "</li>";
					}
					msg += "</ul><div>changes will take effect after saving the software.</div></div>";
					appdb.views.RelatedContactList.ChangesDialog = new dijit.TooltipDialog({content: msg});
					dijit.popup.open({
						parent: span,
						popup: appdb.views.RelatedContactList.ChangesDialog,
						around: span,
						orient: {'TL': 'BL', 'TR': 'BR'}
					});
				}).mouseleave(function() {
					if (appdb.views.RelatedContactList.ChangesDialog != null) {
						dijit.popup.close(appdb.views.RelatedContactList.ChangesDialog);
						appdb.views.RelatedContactList.ChangesDialog.destroyRecursive(false);
						appdb.views.RelatedContactList.ChangesDialog = null;
					}
				});
			}
		} else {
			$(".contactpointlistcontainer").find(".mustsave").remove();
			if (len === 0 && $(".contactpointlistcontainer").find("table tbody tr.nodata").length === 0 && this._renderNoData) {
				$(".contactpointlistcontainer").find("table tbody").append(this._renderNoData());
			}
			$(item.dom).find(".haschanges").remove();
		}
	}
});

appdb.views.ContactPointEditor = appdb.ExtendClass(appdb.View, "appdb.views.ContactPointEditor", function(o) {
	this.listItem = null;
	this._application = null;
	this._dialog = null;
	this.vos = [];
	this.mws = [];
	this._getAvailableVOs = function() {
		if( appdb.pages.application.isSoftware() === false ) {
			//Only software items have user assigned VOs
			//vappliances and swappliances have dynamic
			//VOs based on the VO wide image lists
			return [];
		}
		var voids = [];
		var res = [];
		var vod = eval("(" + voData + ")");
		$(":input[name^='vo']").each(function() {
			voids[voids.length] = $(this).val();
		});
		for (var j = 0; j < voids.length; j += 1) {
			var voindex = -1;
			for (var i = 0; i < vod.ids.length; i += 1) {
				if (voids[j] == vod.ids[i]) {
					voindex = i;
					break;
				}
			}
			if (voindex > -1) {
				res[res.length] = {id: vod.ids[voindex], name: vod.vals[voindex]};
				if (res.length == voids.length) {
					break;
				}
				voindex = -1;
			}
		}
		return res;
	};
	this._getUnusedVos = function(vos) {
		var u = {}, res = [], cp = ((this.listItem._itemData) ? this.listItem._itemData.contactItem : []) || [];
		if ($.isArray(cp) === false) {
			cp = [cp];
		}
		for (var i = 0; i < vos.length; i += 1) {
			var index = -1;
			for (var j = 0; j < cp.length; j += 1) {
				if (cp[j].type === "vo" && cp[j].id == vos[i].id) {
					index = i;
					break;
				}
			}
			if (index === -1) {
				u[vos[i].id] = vos[i];
			}
		}
		for (i in u) {
			res[res.length] = u[i];
		}
		return res;
	};
	this._getAvailableMiddlewares = function() {
		var mwids = [];
		var res = [];
		var mw = eval("(" + mwData + ")");
		//Check registered
		$(":input[name^='mw']").each(function() {
			if ($.trim($(this).prev().val()) != '' && $(this).prev().val() != appdb.config.defaults.api.middleware) {
				mwids[mwids.length] = $(this).prev().val();
			}
		});
		//Check others
		$(".app-mw.other input[name^='mw']").each(function() {
			if ($.trim($(this).val()) != '' && $(this).val() != appdb.config.defaults.api.middleware) {
				mwids[mwids.length] = $(this).val();
			}
		});
		for (var j = 0; j < mwids.length; j += 1) {
			var mwindex = -1;
			for (var i = 0; i < mw.ids.length; i += 1) {
				if (mwids[j] == mw.vals[i]) {
					mwindex = i;
					break;
				}
			}
			if (mwindex > -1) {
				res[res.length] = {id: mw.ids[mwindex], name: mw.vals[mwindex], comment: ''};
			} else {
				res[res.length] = {id: 5, name: "Other", comment: mwids[j]};
			}
			if (res.length === mwids.length) {
				break;
			}
		}
		return res;
	};
	this._getUnusedMiddlewares = function(mws) {
		var u = {}, res = [], cp = ((this.listItem._itemData) ? this.listItem._itemData.contactItem : []) || [];
		if ($.isArray(cp) == false) {
			cp = [cp];
		}
		for (var i = 0; i < mws.length; i += 1) {
			var index = -1;
			for (var j = 0; j < cp.length; j += 1) {
				
				if (cp[j].type.toLowerCase() === "middleware") {
					if (mws[i].id == 5 && cp[j].id.indexOf(":") > -1 && mws[i].comment && $.trim(mws[i].comment) !== '' && cp[j].id == (mws[i].id + ":" + mws[i].comment)) {
						index = i;
						break;
					} else if (cp[j].id + '' === mws[i].id + '' && $.trim(mws[i].comment) === "") {
						index = i;
						break;
					}
				}
			}
			if (index === -1) {
				if (mws[i].comment) {
					u[mws[i].id + ":" + $.trim(mws[i].comment)] = mws[i];
				} else {
					u[mws[i].id] = mws[i];
				}
			}
		}
		for (i in u) {
			res[res.length] = u[i];
		}
		return res;
	};
	this._isImplicitRemove = function(d) {
		var type = d.type.toLowerCase();
		if (type === "other") {
			return false;
		}
		var i, data = [], len = data.length, index = -1;
		if (type === "middleware") {
			data = this._getAvailableMiddlewares();
			len = data.length;
			for (i = 0; i < len; i += 1) {
				if (d.id == data[i].id) {
					index = i;
					break;
				} else if (data[i].id == 5) {
					if (d.id == data[i].id + ":" + data[i].comment) {
						index = i;
						break;
					}
				}
			}
		} else {
			data = this._getAvailableVOs();
			len = data.length;
			for (i = 0; i < len; i += 1) {
				if (d.id == data[i].id) {
					index = i;
					break;
				}
			}
		}
		if (index > -1) {
			return false;
		}
		return true;
	};
	this._animateTableRow = function(v) {
		var td = null;
		$(".contactpointlistcontainer table tr td.celltype").each(function() {
			if ($(this).next("td.cellvalue").text().toLowerCase() === v.toLowerCase()) {
				td = this;
			}
		});
		if (td != null) {
			var tr = $(td).parent();
			var prevColor = $(td).css("background-color") || "";

			$(tr).animate({"opacity": "0.5", "background-color": "red"}, 300, function() {
				setTimeout(function() {
					$(tr).animate({"opacity": "1", "background-color": prevColor}, 400);
				}, 10);
			});
		}
	};
	this._canAddOtherText = function(v) {
		if ($.trim(v) === '') {
			return false;
		}
		var i, len, data = this.listItem._itemData.contactItem || [];
		if ($.isArray(data) === false) {
			data = [data];
		}
		len = data.length;
		for (i = 0; i < len; i += 1) {
			var vv = data[i].val();
			if ($.trim(vv).toLowerCase() === $.trim(v).toLowerCase()) {
				this._animateTableRow(v);
				return false;
			}
		}
		return true;
	};
	this._renderDataEntry = function(item, container) {
		var dbn = dijit.byId("contactpointlistbutton"), cnt = document.createElement("span"), renderdata = [];
		if (dbn) {
			dbn.destroyRecursive(false);
		}
		$(container).empty();
		$(cnt).addClass(".contactpointdata");
		$(container).append(cnt);

		if (item.type === 'list') {
			var list = item.data;
			for (var i = 0; i < list.length; i += 1) {
				var d = list[i];
				if (item.name === 'Middleware' && d.id == 5 && $.trim(d.comment) != '') {
					renderdata[renderdata.length] = {label: d.comment, value: d.id + ":" + d.comment};
				} else {
					renderdata[renderdata.length] = {label: d.name, value: d.id};
				}
			}
			new dijit.form.Select({
				name: "contactpointlistbutton",
				style: "width:130px;",
				options: renderdata,
				id: "contactpointlistbutton",
				onChange: function(v) {
					jQuery.data($(".contactpointcommand.add:last")[0], "data", {type: item.name, value: v, display: this.getOptions(v).label});
					$(".contactpointcommand.add").css({"display": "inline-block"});
				}
			}, cnt);
		} else {
			new dijit.form.TextBox({
				value: '',
				style: "height:17px;width:140px;vertical-align:middle;",
				placeHolder: "Provide a value...",
				onKeyUp: (function(_this) {
					return function() {
						if (_this._canAddOtherText(this.getDisplayedValue())) {
							jQuery.data($(".contactpointcommand.add:last")[0], "data", {type: item.name, value: this.getDisplayedValue()});
							$(".contactpointcommand.add").css({"display": "inline-block"});
						} else {
							jQuery.data($(".contactpointcommand.add:last")[0], "data", null);
							$(".contactpointcommand.add").hide();
						}
					};
				})(this),
				onChange: (function(_this) {
					return function(v) {
						if (_this._canAddOtherText(v)) {
							jQuery.data($(".contactpointcommand.add:last")[0], "data", {type: item.name, value: v, display: v});
							$(".contactpointcommand.add").css({"display": "inline-block"});
						} else {
							jQuery.data($(".contactpointcommand.add:last")[0], "data", null);
							$(".contactpointcommand.add").hide();
						}
					};
				})(this)}, cnt);
			$(".contactpointcommand.add").hide();
		}

		if (item.type === 'list') {
			jQuery.data($(".contactpointcommand.add:last")[0], "data", {type: item.name, value: renderdata[0].value, display: renderdata[0].label});
		} else {
			jQuery.data($(".contactpointcommand.add:last")[0], "data", '');
		}
		if (item.data) {
			$(".contactpointcommand.add").css({"display": "inline-block"});
		}
	};
	this._renderAdder = function(data) {
		var typebutton, div = document.createElement("div"), types = document.createElement("span"),
				lists = document.createElement("span"), add = document.createElement("span"), button = document.createElement("span");
		this.dom = div;
		$(div).addClass("contactpointadder");
		$(types).addClass("contactpointtypemenu");
		$(lists).addClass("contactpointdata");
		$(add).addClass("contactpointcommand").addClass("add").attr("title", "Add item").css({"display": "inline-block"}).append(button).hide();
		$(div).append(types);
		$(div).append(lists);
		$(div).append(add);

		var typemenu = new dijit.Menu({
			style: "display: none;"
		});
		for (var i in data) {
			if (data[i].type === "list" && data[i].data.length > 0) {
				typemenu.addChild(new dijit.MenuItem({
					label: data[i].name,
					onClick: (function(_this, item) {
						return function() {
							typebutton.attr("label", item.name);
							_this._renderDataEntry(item, lists);
						};
					})(this, data[i])
				}));
			}
		}
		typemenu.addChild(new dijit.MenuItem({
			label: "Custom",
			onClick: (function(_this, item) {
				return function() {
					typebutton.attr("label", "Custom");
					_this._renderDataEntry(item, lists);
				};
			})(this, data[i])
		}));
		typebutton = new dijit.form.DropDownButton({
			label: "Select a subject",
			"class": "WhiteDojoButton",
			name: "contactpointtypebutton",
			dropDown: typemenu
		}, $(types)[0]);

		new dijit.form.Button({
			label: "add",
			onClick: (function(_this) {
				return function() {
					var d = jQuery.data($(".contactpointcommand.add:last")[0], "data");
					var res = null;
					if (d.type === 'Virtual Organization') {
						d.type = "vo";
					}
					if (d) {
						res = {type: d.type, id: d.value, val: (function(val) {
								return function() {
									return val;
								};
							})(d.display)};
					}
					_this._addNewContactPoint(res);
				};
			})(this, data)
		}, button);
		return div;
	};
	this._addNewContactPoint = function(data) {
		if (typeof this.listItem._itemData.contactItem === "undefined") {
			this.listItem._itemData.contactItem = [];
		}
		if ($.isArray(this.listItem._itemData.contactItem) === false) {
			this.listItem._itemData.contactItem = [this.listItem._itemData.contactItem];
		}
		data.isNew = true;
		if (data.type.toLowerCase() === "other") {
			data.val = (function(_data) {
				return function() {
					return _data;
				};
			})(data.id);
			data.id = "0";
		}
		this.listItem._itemData.contactItem[this.listItem._itemData.contactItem.length] = data;

		var pa = $(".contactpointeditor .contactpointadder");
		$(pa).after(this._renderAdder({
			"vo": {name: "Virtual Organization", type: "list", data: this._getUnusedVos(this._getAvailableVOs()), "default": "Select a value..."},
			"mw": {name: "Middleware", type: "list", data: this._getUnusedMiddlewares(this._getAvailableMiddlewares()), "default": "Select a value..."},
			"other": {name: "Other", type: "text", "default": "Select a value..."}
		}));
		$(pa).remove();

		var tbl = $(".contactpointlistcontainer table tbody");
		var tr = this._renderContactPointRow(data);
		$(tbl).append(tr);
	};
	this._removeContactPoint = function(data) {
		var l = this.listItem._itemData.contactItem, len = l.length, i, index = -1;
		for (i = 0; i < len; i += 1) {
			if (l[i].isNew && l[i].isNew == true && l[i].id == data.id) {
				index = i;
				break;
			}
		}
		if (index > -1) {
			this.listItem._itemData.contactItem.splice(index, 1);
		}
		var pa = $(".contactpointeditor .contactpointadder");
		$(pa).after(this._renderAdder({
			"vo": {name: "Virtual Organization", type: "list", data: this._getUnusedVos(this._getAvailableVOs()), "default": "Select a value..."},
			"mw": {name: "Middleware", type: "list", data: this._getUnusedMiddlewares(this._getAvailableMiddlewares()), "default": "Select a value..."},
			"other": {name: "Other", type: "text", "default": "Select a value..."}
		}));
		$(pa).remove();

	};
	this._renderContactPointRow = function(data) {

		var tr = document.createElement("tr"), tdflag = document.createElement("td"), tdtype = document.createElement("td"), tdvalue = document.createElement("td"),
				tdremove = document.createElement("td"), button = document.createElement("span"), dojobutton;
		var type = "Virtual Organization";
		if (data.type.toLowerCase() === "middleware") {
			type = "Middleware";
		} else if (data.type.toLowerCase() === "other") {
			type = "Other";
		}
		var isremoveimplicit = this._isImplicitRemove(data);
		if (data.toRemoveImplicit && isremoveimplicit == false) {
			delete data.toRemoveImplicit;
		} else if (isremoveimplicit == true) {
			data.toRemoveImplicit = true;
		}
		$(tdflag).addClass("cellflag").append("<img class='toadd' src='/images/" + ((data.isNew) ? "close" : "close2") + ".png' title='This item will be " + ((data.isNew) ? "added" : "removed") + " after saving the software.' />");
		$(tdtype).addClass("celltype").append("<div>" + type + "</div>");
		$(tdvalue).addClass("cellvalue").append("<div>" + ((data.val) ? data.val() : '') + "</div>");
		$(tdremove).addClass("cellcommand");
		$(tr).append(tdflag).append(tdtype).append(tdvalue).append(tdremove);
		$(tdremove).append(button);
		if (data.isNew) {
			dojobutton = new dijit.form.Button({
				label: "remove",
				iconClass: "dijitIconDelete",
				onClick: (function(_this, d) {
					return function() {
						_this._removeContactPoint(d);
						$(tr).remove();
						if (_this.listItem._itemData.contactItem.length == 0) {
							$(".contactpointlistcontainer table thead").hide();
						}
						_this._checkForChanges();
					};
				})(this, data)
			}, button);
			if (data.toRemoveImplicit) {
				var msg = "<div class='removeimplicitmessage' >The " + type.toLowerCase() + " '<i><b>" + ((data.val) ? data.val() : '') + "</b></i>' will be removed from this software. All contact expertise of the same type will be removed as well after saving the software. To prevent this go to the software " + type.toLowerCase() + " list and add '<i>" + data.val() + "</i>' again.<div>";
				var pu = new dijit.TooltipDialog({content: msg});
				dojo.connect(dojobutton, "onMouseEnter", this, function() {
					setTimeout((function(_this) {
						pu = new dijit.TooltipDialog({content: msg});
						return function() {
							dijit.popup.open({
								parent: dojobutton.domNode,
								popup: pu,
								around: dojobutton.domNode,
								orient: {'BL': 'TL', 'BR': 'TR'}
							});
						};
					})(this), 1);
				});
				dojo.connect(dojobutton, "onMouseLeave", this, function() {
					if (pu) {
						dijit.popup.close(pu);
						pu.destroyRecursive(false);
					}
				});
				$(tdremove).mouseleave(function() {
					if (pu) {
						dijit.popup.close(pu);
						pu.destroyRecursive(false);
					}

				});
				$(tr).addClass("toremove");
			}//end of explicitremove
		} else {
			dojobutton = new dijit.form.ToggleButton({
				showLabel: true,
				label: "remove",
				iconClass: "dijitCheckBox",
				title: (data.toRemove) ? "This entry is marked to be removed after saving the software." : "Mark this entry to be removed",
				disabled: (data.toRemoveImplicit) ? true : false,
				checked: (data.toRemoveImplicit || data.toRemove) ? true : false,
				onMouseUp: function() {
					if (data.toRemoveImplicit) {
						var msg = "<div class='removeimplicitmessage' >The " + type.toLowerCase() + " '<i><b>" + ((data.val) ? data.val() : '') + "</b></i>' will be removed from this software. All contact expertise of the same type will be removed as well after saving the software. To prevent this go to the software " + type.toLowerCase() + " list and add '<i>" + data.val() + "</i>' again.<div>";
						var pu = new dijit.TooltipDialog({content: msg});
						setTimeout((function(_this) {
							return function() {
								dijit.popup.open({
									parent: _this.domNode,
									popup: pu,
									around: _this.domNode,
									orient: {'BL': 'TL', 'BR': 'TR'}
								});
							};
						})(this), 1);
					}
				},
				onChange: (function(_this) {
					return function(v) {
						if (data.toRemoveImplicit) {
							this.attr("title", "");
							$(this.domNode).find(".dijitCheckBox:first").addClass("dijitCheckBoxChecked").addClass("dijitChecked");
							return false;
						}
						if (v == true) {
							data.toRemove = v;
							this.attr("title", "This entry is marked to be removed after saving the software.");
							$(this.domNode).find(".dijitCheckBox:first").addClass("dijitCheckBoxChecked").addClass("dijitChecked");
							$(tr).addClass("toremove");
						} else {
							delete data.toRemove;
							this.attr("title", "Mark this entry to be removed");
							$(tr).removeClass("toremove");
							$(this.domNode).find(".dijitCheckBox:first").removeClass("dijitCheckBoxChecked").removeClass("dijitChecked");
						}
						_this._checkForChanges();
					};
				})(this)
			}, button);
			if (dojobutton.attr("checked")) {
				$(dojobutton.domNode).find(".dijitCheckBox:first").addClass("dijitCheckBoxChecked").addClass("dijitChecked");
				if (data.toRemoveImplicit) {
					dojobutton.attr("title", "");
				}
			}
			if (data.toRemove == true || data.toRemoveImplicit == true) {
				$(tr).addClass("toremove");
			} else {
				$(tr).removeClass("toremove");
			}
		}
		if (data.isNew == true) {
			$(tr).addClass("toadd");
		}
		this._checkForChanges();
		$(".contactpointlistcontainer table thead").show();
		return tr;
	};
	this._renderNoData = function() {
		var tr = document.createElement("tr"), td = document.createElement("td"), data = this.listItem._itemData;
		$(tr).addClass("nodata");
		$(td).attr("colspan", "4");
		$(td).text("No expertise has been set yet");
		$(tr).append(td);
		return tr;
	};
	this._renderContactPoints = function(data) {
		var list = data.contactItem || [];
		if ($.isArray(list) === false) {
			list = [list];
		}
		var div = document.createElement("div"), table = document.createElement("table"), tbody = document.createElement("tbody"), thead = document.createElement("thead");
		$(div).addClass("contactpointlistcontainer");
		$(table).attr("cellpadding", "0").attr("cellspacing", "0");
		$(table).append(tbody);
		$(div).append(table);
		$(thead).append("<tr><th align='left' colspan='2'>Type</th><th align='left' colspan='2'>Value</th></tr>");
		$(table).append(thead);
		if (list.length > 0) {
			for (var i = 0; i < list.length; i += 1) {
				$(tbody).append(this._renderContactPointRow(list[i]));
			}
		} else {
			$(thead).hide();
			$(tbody).append(this._renderNoData());
		}
		return div;
	};
	this._checkForChanges = (function(_this) {
		return function() {
			appdb.views.RelatedContactList.CheckForChanges.apply(_this, [_this.listItem]);
		};
	})(this);
	this.render = function() {
		var d = this.listItem._itemData;
		var div = document.createElement("div"), title = document.createElement("div"), commands = document.createElement("div");
		if (appdb.views.ContactPointEditor.Dialog != null) {
			appdb.views.ContactPointEditor.Dialog.hide();
			appdb.views.ContactPointEditor.Dialog.destroyRecursive(false);
		}

		$(div).addClass("contactpointeditor");
		$(title).addClass("title");
		$(commands).addClass("contactpointactions");
		$(title).text("" + d.firstname + " " + d.lastname + " will be displayed as an expert regarding this software on the subjects listed bellow:");
		$(div).append(title);

		$(div).append(this._renderAdder({
			"vo": {name: "Virtual Organization", type: "list", data: this._getUnusedVos(this._getAvailableVOs()), "default": "Select a value..."},
			"mw": {name: "Middleware", type: "list", data: this._getUnusedMiddlewares(this._getAvailableMiddlewares()), "default": "Select a value..."},
			"other": {name: "Other", type: "text", "default": "Select a value..."}
		}));

		$(div).append(this._renderContactPoints(d));
		$(div).append(commands);

		new dijit.form.Button({
			label: "Apply",
			style: "float:right;padding:5px;",
			onClick: function() {
				appdb.views.ContactPointEditor.Dialog.hide();
			}
		}, $(commands)[0]);
		this._checkForChanges();
		appdb.views.ContactPointEditor.Dialog = new dijit.Dialog({
			title: "Edit expertise",
			style: "width:470px",
			content: $(div)[0]
		});
		appdb.views.ContactPointEditor.Dialog.show();
		setTimeout((function(_this) {
			return function() {
				_this._checkForChanges();
			};
		})(this), 1);
	};
	this._constructor = function() {
		this.listItem = o.listitem;
		this.vos = this._getUnusedVos(this._getAvailableVOs());
		this.mws = this._getUnusedMiddlewares(this._getAvailableMiddlewares());
	};
	this._constructor();
}, {
	Dialog: null,
	WarningDialog: null
});

appdb.views.AppsList = appdb.ExtendClass(appdb.View, "appdb.views.AppsList", function(o) {
	this._addItem = function(d, index) {
		var doc = document, a, img, span, span1, span2, span3, span4, more, div, div1, i, u = [], res = $(doc.createElement("li")), logo, ishttps = Boolean(appdb.config.https) && true, prot,
				moderated = false, deleted = false, validated = false, title = '', rating = null, ratingcount = null, categories = null, c, descr;
		title = d.name;
		d.description = d.description || "";
		descr = d.description;
		if ($.trim(descr) === "" || $.trim(descr).toLowerCase() === $.trim(d.name).toLowerCase()) {
			if ($.trim(d["abstract"]) !== "") {
				descr = d["abstract"];
			}
		}
		if (d.moderated) {
			if (d.moderated === "true") {
				moderated = true;
				$(res).addClass("moderated");
				title = "This software has been moderated.";
				$(res).attr("title", title);
			}
		}
		if (d.deleted) {
			if (d.deleted === "true") {
				deleted = true;
				$(res).addClass("deleted");
				title = "This software has been deleted.";
				$(res).attr("title", title);
			}
		}
		if (typeof d.validated !== "undefined") {
			if ($.trim(d.validated) === "false") {
				validated = false;
				$(res).addClass("invalidated");
				$(res).attr("title", title);
			} else
				validated = true;
		}
		switch( $.trim(d.metatype).toLowerCase() ){
			case "2":
				d.contentType = "softwareappliance";
				break;
			case "1":
				d.contentType = "virtualappliance";
				break;
			case "0":
			default:
				d.contentType = "software";
				break;
		}
		$(res).addClass("itemcontainer").addClass("switem");
		div = doc.createElement("div");
		$(div).addClass("item");
		if (validated === false && appdb.config.views.applications.displayInvalidatedFlag === true) {
			$(div).addClass("invalidated");
			$(div).append($("<span class='invalidated'></span>").append("<img src='/images/exclam16.png' border='0' alt='' title='This software has not been updated during the last " + appdb.config.appValidationPeriod + ".'/>"));
		}
		var permlink = appdb.utils.getItemCanonicalUrl(d.contentType, d);
		a = $(doc.createElement("a")).attr("title", title).attr("href", permlink).attr("target", "_blank");

		$(a).addClass("itemlink");
		$(a)[0].onclick = (function(_this, data) {
			return function() {
				if (_this.parent && _this.parent.views.pager) {
					_this.publish({event: "itemclick", value: data});
				} else {
					if( data.contentType === "softwareappliance" ){
						appdb.views.Main.showSoftwareAppliance(data);
					} else if (data.contentType === "virtualappliance") {
						appdb.views.Main.showVirtualAppliance(data);
					} else {
						appdb.views.Main.showApplication(data);
					}
				}
				return false;
			};
		})(this, d);
		logo = d.logo;
		if (logo) {
			prot = logo.substr(4, 5);
			if (ishttps === true && prot === ":") {
				logo = "https" + logo.substr(4, logo.length);
			} else if (prot === "https") {
				logo = "http:" + logo.substr(5, logo.length);
			}
		}
		if (d.category) {
			d.category = $.isArray(d.category) ? d.category : [d.category];
			for (c = 0; c < d.category.length; c++) {
				if (d.category[c].primary && d.category[c].primary === "true") {
					d.primaryCategory = d.category[c].val().toLowerCase();
					d.primaryCategoryId = d.category[c].id;
					break;
				}
			}
			if( d.contentType === "virtualappliance"){
				permlink = appdb.utils.getItemCanonicalUrl("virtualappliance", d);
				$(res).removeClass("switem").removeClass("swappitem").addClass("vappitem");
			} else if( d.contentType === "softwareappliance" ){
				permlink = appdb.utils.getItemCanonicalUrl("swappliance", d);
				$(res).removeClass("switem").addClass("swappitem").removeClass("vappitem");
			} else {
				$(res).addClass("switem").removeClass("swappitem").removeClass("vappitem");
			}
			$(a).attr("href", permlink);
		}
		img = $(doc.createElement("img")).addClass("itemimage");
		if( logo ){
			// DNE: USE FAST PROGRESSIVE JPEGs INSTEAD OF SLOW PHP CALL
			//img.attr("src","/apps/getlogo?req=" + encodeURI(d.lastUpdated) + "&id=" + d.id);
			img.attr("src","/images/applogo/55x55/app-logo-" + d.id + ".jpg?req=" + encodeURI(d.lastUpdated));
		}else if( d.contentType === 'softwareappliance'){
			img.attr("src", loadImage(appdb.config.images["software appliances"]));
		}else if( d.primaryCategory ){
			img.attr("src", loadImage(appdb.config.images[d.primaryCategory]) );
		}else{
			img.attr("src", loadImage(appdb.config.images.applications) );
		}
		$(a).append(img);
		d["abstract"] = d["abstract"] || "";
		span = $(doc.createElement("span")).append(d.name.substring(0, 45).replace(/\</g, "&lt;").replace(/\>/g, "&gt;") + (d.name.length > 45 ? '...' : '')).addClass("itemname");
		span1 = $(doc.createElement("span")).append(unescape(descr.substring(0, 80)).replace(/\<\/*\w+ *(\w+(=['"][a-zA-Z0-9:;\-_#! \.]*["']){0,}){0,}\/{0,}\>/g, "").replace(/\</g, "&lt;").replace(/\>/g, "&gt;") + ((descr.length > 80) ? '...' : '')).addClass("itemsorttext");
		span2 = $(doc.createElement("span")).append("<span class='description'>" + unescape(d.description).replace(/\<\/*\w+ *(\w+(=['"][a-zA-Z0-9:;\-_#! \.]*["']){0,}){0,}\/{0,}\>/g, "").replace(/\</g, "&lt;").replace(/\>/g, "&gt;") + "</span>").append(doc.createElement("p")).append(d["abstract"].replace(/\<\/*\w+ *(\w+(=['"][a-zA-Z0-9:;\-_#! \.]*["']){0,}){0,}\/{0,}\>/g, "").replace(/\</g, "&lt;").replace(/\>/g, "&gt;")).addClass("itemlongtext");
		more = $(doc.createElement("span")).addClass("itemmore").append("click for details");
		$(a).append(span).
				append(span1).
				append(span2);
		
		//In case of rating
		d.rating = d.rating || "0";
		if (d.rating) {
			rating = document.createElement("div");
			$(rating).addClass("rating");
			if (parseFloat(d.rating) == 0.0) {
				$(rating).addClass("zero");
			}
			$(a).append(rating);
			ratingcount = document.createElement("div");
			$(ratingcount).addClass("ratingcount").text(parseFloat(d.rating).toFixed(1));
			$(a).append(ratingcount);
			var ratingvotes = document.createElement("div"), ratingView = new appdb.views.StarBox({container: $(rating), style: "width:10px;height:10px;", images: {"full": loadImage("star_full.png"), "half": loadImage("star_half.png"), "empty": loadImage("star_none.png"), "active": loadImage("star_full.png")}});
			ratingView.render(d.rating || "0");
			$(ratingvotes).addClass("ratingvotes").append("<span class='votes'>" + d.ratingCount + "</span><span class='datatype'>vote" + ((d.ratingCount != 1) ? "s" : "") + "</span>");
			var info = document.createElement("div");
			$(a).append($(info).addClass("ratinginfo"));

			if (d.ratingCount) {
				$(a).append(ratingvotes);
			}
			if (d.hitcount) {
				var visitLiteralLPad = ''
			    if( d.hitcount > 100000) {
					visitLiteralLPad = 'style="padding-left: 3px" ';
				}
				if (d.hitcount > 1000000) {
					d.hitcount = Math.round(d.hitcount / 1000000 * 10) / 10 + 'M';
				} else if (d.hitcount > 10000) {
					d.hitcount = Math.round(d.hitcount / 1000) + 'k';
				} else if (d.hitcount > 1000) {
					d.hitcount = Math.round(d.hitcount / 1000 * 10) / 10 + 'k';
				}
				$(info).append("<div class='field'><span class='description'><span class='hits'>" + d.hitcount + "</span><span " + visitLiteralLPad + "class='datatype'>visit" + ((d.hitcount != 1) ? "s" : "") + "</span></span></div>");
			}
			if (parseFloat(d.rating) == 0.0) {
				$(ratingcount).addClass("zero");
				$(ratingvotes).addClass("zero");
			}
		}

		$(a).append(more);
		$(div).append($(a));
		if (d.relcount && d.relcount != "0") {
			var downloadlink = permlink;
			var downloadtitle = "Browse or download software releases";
			var downloaddoc = "Software releases";
			switch(d.metatype){
				case "2":
					downloadlink += "/versions";
					downloadtitle = "Browse or download software appliance contextualization scripts";
					downloaddoc = "Software Appliance versions";
					break;
				case "0":
					downloadlink += "/releases";
					break;
			}
			span3 = $(doc.createElement("span")).append("<a href='#' title='"+downloadtitle+"'><img src='/images/download.png' alt=''/><span>Download</span></a>").addClass("itemrelease");
			$(span3).children("a:first").attr("href", downloadlink).attr("target", "_blank").click((function(doctitle){ 
				return function(ev) {
					ev.preventDefault();
					appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data, doctitle, $(this).attr("href"));
					appdb.routing.Dispatch();
					return false;
				};
			})(downloaddoc));
			$(div).addClass("hasrelease").append($(span3));
		} else if (d.appliance) {
			span3 = $(doc.createElement("span")).append("<a href='#' ><img src='/images/download.png' alt=''/><span title='Browse or download virtual appliances'>Download</span></a>").addClass("itemrelease").addClass("vaversion");
			$(span3).children("a:first").attr("href", "/store/vappliance/" + d.cname + "/vaversion/latest").attr("target", "_blank").click(function(ev) {
				ev.preventDefault();
				appdb.Navigator.pushState(appdb.Navigator.currentHistoryState.data, "Software " + d.name + " virtual appliance", $(this).attr("href"));
				appdb.routing.Dispatch();
				return false;
			});
			var spd = $("<span class='description'></span>");
			if (d.appliance && d.appliance.version) {
				var sver = $("<span class='fieldvalue'><span class='field'>Version:</span><span class='value'>" + $.trim(d.appliance.version).replace(/\</g, "&lt;").replace(/\>/g, "&gt;") + "</span>");
				$(spd).append(sver);
			}
			if ($.trim(d.appliance.imagelistprivate) === "true") {
				$(div).addClass("isprivate");
				$(span3).addClass("isprivate");
				$(span3).children("a").find("IMG:first").attr("src", "/images/logout3.png");
				$(span3).children("a").find("span:first").text("Private");
				$(a).append("<div class='privatedisplay'><img src='/images/logout3.png' alt=''/></div>");
			}
			
			$(span3).children("a:first").append(spd);
			$(span3).children("a:first").append("<span class='glue'></span>");
			$(div).addClass("hasrelease").append($(span3));
		}
		if (d.category) {
			$(div).remove(".categoriescontainer");
			d.category = $.isArray(d.category) ? d.category : [d.category];
			var catcontainer = $(document.createElement("div")).addClass("categoriescontainer");
			var shortcut = $("<a href='#' class='shortcut'>member of<span class='urllistarrow'>\u25bc</span></a>");
			$(catcontainer).append(shortcut);
			setTimeout((function(elem) {
				return function() {
					shortcut.click(function() {
						$(this).parent().addClass("show");
						$(this).parent().mouseleave(function() {
							$(this).removeClass("show");
						});
					});
				};
			})(shortcut), 1);

			categories = $(document.createElement("ul")).addClass("categories");
			$(div).append(catcontainer);
			var clen = d.category.length;
			for (c = 0; c < clen; c += 1) {
				if (d.category[c].parentid)
					continue;
				var cli = $(document.createElement("li")).addClass("category");
				var ca = $(document.createElement("a"));
				var cimg = (appdb.config.images[d.category[c].val().toLowerCase()]) ? appdb.config.images[d.category[c].val().toLowerCase()] : "/images/app.png";
				if (d.category[c].primary == "true") {
					$(cli).addClass("primary");
					$(ca).addClass("primary");
				}

				var cval = d.category[c].val();
				var typefunc = "showApplications";
				if( d.contentType === "softwareappliance"){
					typefunc = "showSoftwareAppliances";
				} else if( d.contentType === "virtualappliance"){
					typefunc = "showVirtualAppliances";
				}
				$(ca).attr("href", "#").
						attr("title", "Click to view software in category " + cval).
						attr("onClick", "appdb.views.Main." + typefunc + "({flt:'+=category.id:" + d.category[c].id + "'},{isBaseQuery:true,mainTitle:'" + cval + "',filterDisplay:'Search in " + cval + "...'});");
				$(cli).append(ca);

				$(ca).append("<img src='" + cimg + "' border='0' />");
				$(ca).append("<span class='name'>" + cval + "</span>");
				$(categories).append(cli);
			}
			$(catcontainer).append(categories);
		}
		if (d.url) {
			u = ($.isArray(d.url)) ? d.url : [d.url];
		}
		
		div1 = $(doc.createElement("div")).addClass("itemurlcontainer");
		if (u && u.length > 0 ) {
			var au = new appdb.views.ApplicationUrls({container: div1});
			this.subviews[this.subviews.length] = au;
			au.render(u);
			$(au.dom).find(".applicationurls:last").prepend('<span class="urlseperator" style="margin-right:4px;"> | </span>');
		} else {
			$(div1).addClass("empty");
		}
		$(div).append(div1);
		$(res).append($(div));
		if (this.ext && typeof this.ext.onRenderItem === "function") {
			this.ext.onRenderItem.call(this, res, d);
		}
		
		d.sitecount = ( ($.trim(d.sitecount)!=="")?(d.sitecount<<0):0 );
		if( d.sitecount > 0 ){
			span4 = $("<div class='supports sites' title='This virtual appliance is supported by "+d.sitecount+" site"+ ((d.sitecount>1)?"s":"") +"'><span class='serviceitem occi hasinstances'><span class='instances'><span class='count'>"+d.sitecount+"</span><span>site"+ ((d.sitecount>1)?"s":"") +"</span></span></span></div>");
			if( d.sitecount > 1 ){
				$(span4).find(".plural").text("s");
			}
			$(a).append(span4);
		}
		return res;
	};
	this.render = function(d, ext) {
		this.ext = ext || null;
		this.reset();
		var i, len = (d) ? ((typeof d.length !== "undefined") ? d.length : len) : 0, f = $(this.dom);
		$(this.dom).empty();
		if (typeof len === "undefined") {
			$(f).append(this._addItem(d)[0]);
		} else if (len === 0) {
			$(f).append($(document.createElement("span")).text("No records returned with the specified criteria")[0]);
		} else {
			for (i = 0; i < len; i += 1) {
				$(f).append(this._addItem(d[i], i)[0]);
			}
		}
		$(this.dom).addClass("apps");
	};
	this.destroy = function() {
		this.reset();
		$(this.dom).find("a").each(function() {
			$(this)[0].onclick = null;
		});
		$(this.dom).empty();
	};
}, {
	SetupRatings: function() {
		setTimeout(function() {
			$("ul.itemgrid li.itemcontainer div.item a.itemlink").each(function(index, elem) {
				var rating = $(elem).find(".rating");
				var ratingcount = null;
				if ($(rating).length == 0) {
					return;
				}
				ratingcount = $(elem).find(".ratingcount");
				(new appdb.views.StarBox({container: $(rating), style: "width:10px;height:10px;", images: {"full": loadImage("star_full.png"), "half": loadImage("star_half.png"), "empty": loadImage("star_none.png"), "active": loadImage("star_full.png")}})).render($(ratingcount).text());
			});
			$("ul.itemgrid li.itemcontainer div.item .categoriescontainer a.shortcut").each(function(index, elem) {
				$(elem).click(function() {
					$(this).parent().addClass("show");
					$(this).parent().mouseleave(function() {
						$(this).removeClass("show");
					});
				});
			});
			$("ul.itemgrid li.itemcontainer div.item .categoriescontainer ul.categories li.category a").each(function(index, elem) {
				var img = appdb.config.images[$(elem).find(".name").text().toLowerCase()];
				if (img) {
					$(elem).find("img").attr("src", img);
				}
			});
		}, 1);
	}
});
appdb.views.VOsList = appdb.ExtendClass(appdb.View, "appdb.views.VOslist", function(o) {
	this._constructor = function() {
		if (typeof o.container === "string") {
			this.id = o.container;
			if ($(this.id).length > 0) {
				this.dom = $($(this.id)[0]);
			}
		} else {
			this.id = $(o.container).attr("id");
			this.dom = $(o.container);
		}
	};
	this._addItem = function(d) {
		var doc = document, a, img, span, span1, span2, span3, div, div1, i, u = [], res = $(doc.createElement("li")), logo, domain = '', desc_s, desc;
		logo = "/images/disciplines/" + d.logoid + ".png";
		if( $.trim(d.name).toLowerCase() === 'eubrazilcc.eu'){
			logo = "/images/vo_eubrazilcc_eu.png";
		}
		if (d.val) {
			desc = d.val();
		}
		if (desc) {
			desc_s = unescape(desc.substring(0, 80)) + ((desc.length > 80) ? '...' : '');
			desc = unescape(desc);
		}
		$(res).addClass("itemcontainer");
		div = $(doc.createElement("div")).addClass("item");
		a = $(doc.createElement("a")).attr("title", d.name).attr("href", appdb.config.endpoint.base + "?#!p=" + appdb.utils.base64.encode("/vo/details?id=" + d.name)).attr("target", "_blank").addClass("itemlink");
		$(a)[0].onclick = (function(_this, data) {
			return function() {
				if (_this.parent && _this.parent.views.pager) {
					_this.publish({event: "itemclick", value: data});
				} else {
					appdb.views.Main.showVO(data.name);
				}
				return false;
			};
		})(this, d);
		img = $(doc.createElement("img")).attr("src", logo).addClass("itemimage");
		span = $(doc.createElement("span")).append(d.name.substring(0, 45).replace(/\</g, "&lt;").replace(/\>/g, "&gt;") + (d.name.length > 45 ? '...' : '')).addClass("itemname");
		span1 = $(doc.createElement("span")).append($.trim(desc_s).replace(/\</g, "&lt;").replace(/\>/g, "&gt;")).addClass("itemsorttext");
		span2 = $(doc.createElement("span")).append($.trim(domain).replace(/\</g, "&lt;").replace(/\>/g, "&gt;")).append("<p></p>").append($.trim(desc).replace(/\</g, "&lt;").replace(/\>/g, "&gt;")).addClass("itemlongtext");

		$(a).append(img).
				append(span).
				append(span1).
				append(span2);

		if (typeof d.member_since !== "undefined") {
			var ms = $.trim(d.member_since).split(" ");
			if (ms.length > 0 && $.trim(ms[0]) !== "") {
				span3 = $(doc.createElement("span")).append("<span class='field'>Member since:</span><span class='value'>" + ms[0] + "</span>").addClass("membersince");
				$(a).append(span3);
			}
		}
		if( typeof d.sourceid !== "undefined" && $.trim(d.sourceid) !== "1" ){
			if( d.name !== 'vo.elixir-europe.org' ) {
				var nonegi = $("<span class='nonegi' title=''></span>").text("non EGI");
				$(nonegi).append("<span>This VO may <b>not</b> be supported by the EGI Infrastructure.</span>");
				$(nonegi).attr("title",'');
				$(a).append(nonegi);
			}
		}
		$(div).append($(a));
		if (d.url) {
			u = ($.isArray(d.url)) ? d.url : [d.url];
		}
		if (u.length > 0) {
			$(div).append($(a));
			div1 = $(doc.createElement("div")).addClass("itemurlcontainer");
			for (i = 0; i < u.length; i += 1) {
				if (u[i].val) {
					$(div1).append($("<a href='" + u[i].val() + "' target='_blank' style='padding-right:5px;'>" + u[i].type + "</a>")).addClass("itemurl");
				}
			}
			$(div).append($(div1));
		}
		$(res).append($(div));
		return res;
	};
	this.render = function(d) {
		var i, len = (d) ? ((typeof d.length !== "undefined") ? d.length : len) : 0, f = $(this.dom).context;
		$(this.dom).empty();
		if (typeof len === "undefined") {
			f.appendChild(this._addItem(d)[0]);
		} else if (len === 0) {
			f.appendChild($(document.createElement("span")).text("No records returned with the specified criteria")[0]);
		} else {
			for (i = 0; i < len; i += 1) {
				f.appendChild(this._addItem(d[i])[0]);
			}
		}
		appdb.utils.animateList();
	};
	this.destroy = function() {
		$(this.dom).find("a").each(function() {
			$(this)[0].onclick = null;
		});
		$(this.dom).empty();
	};
	this._constructor();
});

appdb.views.SitesList = appdb.ExtendClass(appdb.View, "appdb.views.SitesList", function(o) {
	this._constructor = function() {
		if (typeof o.container === "string") {
			this.id = o.container;
			if ($(this.id).length > 0) {
				this.dom = $($(this.id)[0]);
			}
		} else {
			this.id = $(o.container).attr("id");
			this.dom = $(o.container);
		}
	};
	this.getImageCount = function(services){
		services = services || [];
		services = $.isArray(services)?services:[services];
		var images = {};
		var count = 0;
		$.each(services, function(i,e){
			e.image = e.image || [];
			e.image = $.isArray(e.image)?e.image:[e.image];
			$.each(e.image, function(ii,ee){
				if( !images[ee.goodid] ){
					images[ee.goodid] = true;
					count += 1;
				}
			});
		});
		return count;
	};
	this._addItem = function(d) {
		var doc = document, a, img, span, span1, span2, flags = "", div, div1, div2, i, u = [], res = $(doc.createElement("li")), logo, isos = '', desc_s, desc;
		logo = appdb.config.endpoint.base + "images/site.png";
		desc = d.officialname;
		if (desc) {
			desc_s = unescape(desc.substring(0, 80)) + ((desc.length > 80) ? '...' : '');
			desc = unescape(desc);
		}
		$(res).addClass("itemcontainer");
		div = $(doc.createElement("div")).addClass("item").addClass();
		a = $(doc.createElement("a")).attr("title", d.name).attr("href", appdb.config.endpoint.base + "store/site/" + $.trim(d.name).toLowerCase()).attr("target", "_blank").addClass("itemlink");
		$(a)[0].onclick = (function(_this, data) {
			return function() {
				if (_this.parent && _this.parent.views.pager) {
					_this.publish({event: "itemclick", value: data});
				} else {
					appdb.views.Main.showSite(data.name);
				}
				return false;
			};
		})(this, d);
		isos = d.country.isocode.split("/");
		for (i = 0; i < isos.length; i += 1) {
			flags += "<img width='16px' src='/images/flags/" + isos[i].toLowerCase() + ".png' border='0' alt='' title='" + d.country.val() + "'/>";
		}
		flags = "<span class='personflags' >" + flags + "</span>";
		img = $(doc.createElement("img")).attr("src", logo).addClass("itemimage");
		span = $(doc.createElement("span")).append(d.name.substring(0, 45).replace(/\</g, "&lt;").replace(/\>/g, "&gt;") + (d.name.length > 45 ? '...' : '')).addClass("itemname");
		span1 = $(doc.createElement("span")).append($.trim(desc_s).replace(/\</g, "&lt;").replace(/\>/g, "&gt;")).addClass("itemsorttext");
		span2 = $(doc.createElement("span")).append($.trim(desc_s).replace(/\</g, "&lt;").replace(/\>/g, "&gt;")).addClass("itemlongtext");
		$(a).append(img).
				append(flags).
				append(span).
				append(span1).
				append(span2);

		$(div).append($(a));

		d.url = d.url || [];
		d.url = $.isArray(d.url) ? d.url : [d.url];
		if (d.url.length > 0) {
			div1 = $("<div class='itemurlcontainer'></div>");
			var urls = [];
			var purl = "<img src='/images/gocdb.png' alt='' />";
			$.each(d.url, function(i, e) {


				urls.push("<span class='urlitem sitelinks'><a href='" + e.val() + "' title='Open in new window' target='_blank' >" + ((e["type"] === "portal") ? purl : "") + "<span>" + ((e["type"] === "portal") ? "GOCDB Portal" : e["type"]) + "</span></a></span>");
			});
			urls = urls.join("<span class='urlseperator'>|</span>");
			if ($.trim(urls) !== "") {
				$(div1).append(urls);
				$(div).append(div1);
			}
		}

		d.service = d.service || [];
		d.service = $.isArray(d.service) ? d.service : [d.service];
		if (d.service.length > 0) {
			div2 = $("<div class='supports'></div>");
			var servs = $.grep(d.service, function(e) {
				return e["type"] === "occi";
			});
			if (d.service.length > 0) {
				var insts = this.getImageCount(servs);
				$(div2).append("<span class='serviceitem occi'><span class='status'><img src='/images/occi.png' alt=''/><span>occi enabled</span></span><span class='instances'><span class='count'></span><span>images</span></span></span>");
				$(div2).find(".count").text(insts);
				if (insts > 0) {
					$(div2).find('.serviceitem').addClass("hasinstances");
				}
				$(div).append(div2);
			}
		}

		$(res).append($(div));
		return res;
	};
	this.render = function(d) {
		var i, len = (d) ? ((typeof d.length !== "undefined") ? d.length : len) : 0, f = $(this.dom).context;
		$(this.dom).empty();
		if (typeof len === "undefined") {
			f.appendChild(this._addItem(d)[0]);
		} else if (len === 0) {
			f.appendChild($(document.createElement("span")).text("No records returned with the specified criteria")[0]);
		} else {
			for (i = 0; i < len; i += 1) {
				f.appendChild(this._addItem(d[i])[0]);
			}
		}
		appdb.utils.animateList();
	};
	this.destroy = function() {
		$(this.dom).find("a").each(function() {
			$(this)[0].onclick = null;
		});
		$(this.dom).empty();
	};
	this._constructor();
});

appdb.views.DatasetsList = appdb.ExtendClass(appdb.View, "appdb.views.DatasetsList", function(o) {
	this._constructor = function() {
		if (typeof o.container === "string") {
			this.id = o.container;
			if ($(this.id).length > 0) {
				this.dom = $($(this.id)[0]);
			}
		} else {
			this.id = $(o.container).attr("id");
			this.dom = $(o.container);
		}
	};
	this._addItem = function(d) {
		var doc = document, a, img, span, span1, span2, flags = "", div, res = $(doc.createElement("li")), logo, desc_s, desc, version,location;
		logo = appdb.config.endpoint.base + "images/dataset.png";
		desc = d.description;
		if (desc) {
			desc_s = unescape(desc.substring(0, 80)) + ((desc.length > 80) ? '...' : '');
			desc = unescape(desc);
		}
		$(res).addClass("itemcontainer");
		div = $(doc.createElement("div")).addClass("item").addClass();
		a = $(doc.createElement("a")).attr("title", d.name).attr("href", appdb.config.endpoint.base + "store/dataset/" + $.trim(d.guid).toLowerCase()).attr("target", "_blank").addClass("itemlink");
		$(a)[0].onclick = (function(_this, data) {
			return function() {
				if (_this.parent && _this.parent.views.pager) {
					_this.publish({event: "itemclick", value: data});
				} else {
					if( data && data.versionid ){
						appdb.views.Main.showDataset({id: data.guid},{"versionid": $.trim(data.versionid)});
					}else {
						appdb.views.Main.showDataset({id: data.guid});
					}
				}
				return false;
			};
		})(this, d);
		
		img = $(doc.createElement("img")).attr("src", logo).addClass("itemimage");
		span = $(doc.createElement("span")).append(d.name.substring(0, 45).replace(/\</g, "&lt;").replace(/\>/g, "&gt;") + (d.name.length > 45 ? '...' : '')).addClass("itemname");
		span1 = $(doc.createElement("span")).append($.trim(desc_s).replace(/\</g, "&lt;").replace(/\>/g, "&gt;")).addClass("itemsorttext");
		span2 = $(doc.createElement("span")).append($.trim(desc).replace(/\</g, "&lt;").replace(/\>/g, "&gt;")).addClass("itemlongtext");
		
		$(a).append(img).
				append(flags).
				append(span).
				append($(doc.createElement("span")).append(d.name).addClass("itemname itemlongname"));
		
		if( d.versionid && $.trim(d.version) !== "" ){
			version = $(doc.createElement("span")).addClass("itemversion").append("<span>ver:</span>");
			$(version).append($("<span class='value'></span>").text(d.version));
			if( parseInt(d.location_count) > 0 ){
				location = $(doc.createElement("span")).addClass("itemlocation").append("<span>location"+ (parseInt(d.location_count)>1?"s":"") +"</span>");
				$(location).prepend($("<span class='value'></span>").text(d.location_count));
				$(location).prepend($("<span>in</span>"));
			}
			$(a).append(version).append(location);
		}else{
			if( parseInt(d.version_count) > 0 ){
				version = $(doc.createElement("span")).addClass("itemversion").append("<span>version"+ (parseInt(d.version_count)>1?"s":"") +"</span>");
				$(version).prepend($("<span class='value'></span>").text(d.version_count));
			}
			if( parseInt(d.location_count) > 0 ){
				location = $(doc.createElement("span")).addClass("itemlocation").append("<span>location"+ (parseInt(d.location_count)>1?"s":"") +" across </span>");
				$(location).prepend($("<span class='value'></span>").text(d.location_count));
			}
			$(a).append(location).
				append(version);
		}
		$(a).append(span1).
			append(span2);

		$(div).append($(a));
		if( $.isArray(d.ancestry,['derived','primary']) > -1  ){
			var derived = $("<span class='ancestry "+d.ancestry+"' title=''>"+d.ancestry+"</span>");
			
			if( d.ancestry==="derived"){
				var derivedspan = $("<span></span>");
				var title = $("<b class='title'></b>");
				$(title).text(d.parent.name);
				$(derivedspan).append("Derived from ").append(title).append(' dataset<br/><br/>Derived datasets contain data that have been derived from the<br/>primary datasets. The data may be reduced in the sense that<br/>&#8226; only part of the information is kept, or<br/>&#8226; only part of the entries are selected');

				new dijit.Tooltip({
					connectId: [$(derived)[0]],
					label: $(derivedspan)[0].innerHTML
				});
			}
			$(div).append(derived);
		}

		$(res).append($(div));
		return res;
	};
	this.render = function(d) {
		var i, len = (d) ? ((typeof d.length !== "undefined") ? d.length : len) : 0, f = $(this.dom).context;
		$(this.dom).empty();
		if (typeof len === "undefined") {
			f.appendChild(this._addItem(d)[0]);
		} else if (len === 0) {
			f.appendChild($(document.createElement("span")).text("No records returned with the specified criteria")[0]);
		} else {
			for (i = 0; i < len; i += 1) {
				f.appendChild(this._addItem(d[i])[0]);
			}
		}
		appdb.utils.animateList();
	};
	this.destroy = function() {
		$(this.dom).find("a").each(function() {
			$(this)[0].onclick = null;
		});
		$(this.dom).empty();
	};
	this._constructor();
});

appdb.views.Publications = function(o) {
	$.extend(this, new appdb.View(this));
	var _docgrid, _model;
	this.reset = function() {

	};
	this.getGridData = function(d) {
		var j, i, data = [], title, pageStart, pageEnd;
		if (!d) {
			return data;
		}
		if ($.isArray(d) === false) {
			d = [d];
		}
		for (i = 0; i < d.length; i++) {
			var doc = d[i];
			if (doc.startPage == "") {
				pageStart = doc.endPage;
				pageEnd = "";
			} else {
				pageStart = doc.startPage;
				pageEnd = doc.endPage;
			}
			if (pageStart == "0")
				pageStart = "";
			if (pageEnd == "0")
				pageEnd = "";
			if (pageStart == pageEnd)
				pageEnd = "";
			var pages = "" + pageStart;
			if (pageEnd != "")
				pages += " - " + pageEnd;
			if (doc.url != "") {
				title = "<a href=\"" + doc.url + "\" target=\"_blank\">" + doc.title.replace(/'/g, "\\'") + "</a>";
			} else {
				title = doc.title;
			}
			var authors = [];
			for (j = 0; j < doc.author.length; j++) {
				var author = doc.author[j];
				var authorstr;
				if (author.main)
					authorstr = "<b>";
				if (author.type == "external") {
					if (author.extauthor) {
						authorstr += " " + author.extauthor.replace(/'/g, "\\'");
					}
				} else {
					authorstr += " <a href=\"/store/person/" + author.person.cname + "\" onclick=\"appdb.views.Main.showPerson({id:'" + author.person.id + "', cname:'" + author.person.cname + "'},{mainTitle:'" + author.person.firstname + " " + author.person.lastname + "'})\">" + author.person.firstname.replace(/'/g, "\\'") + ' ' + author.person.lastname.replace(/'/g, "\\'") + "</a>";
				}
				if (author.main)
					authorstr += "</b>";
				authors.push(authorstr);
			}
			data.push([/*doc.id,*/ [title], doc.type.val(), /*doc.type.id,*/ doc.conference, doc.volume, pages, doc.year, doc.publisher, doc.isbn, doc.proceedings, doc.journal, authors]);
		}
		return data;
	};
	this.render = function(d) {
		this.reset();

		if (!d || d.length === 0) {
			if ($("#ppl_details_no_pubs").length > 0) {
				$("#" + this.id).empty().append($("#ppl_details_no_pubs").html());
			}
			return;
		}
		var data = this.getGridData(d);
		var p = dijit.byId("personPublications");
		if (p) {
			_docgrid = p;
		} else {
			this._init();
			_docgrid = new dojox.grid.Grid({id: "personPublications", height: "100%", autoHeight: true}, this.id);
		}
		_model = new dojox.grid.data.Table(null, data);
		var view = {
			cells: [[
					{name: 'Title', rowSpan: 2, width: '20%'},
					{name: 'Type', rowSpan: 2, width: '5%'},
					{name: 'Conference', width: '20%'},
					{name: 'Volume', width: '5%'},
					{name: 'Pages', width: '5%'},
					{name: 'Year', width: '3%'},
					{name: 'Publisher', width: '10%'},
					{name: 'ISBN', width: '10%'}
				], [
					{name: 'Proceedings'},
					{name: 'Journal', colSpan: 3},
					{name: 'Authors', colSpan: 2}
				]]
		};
		_docgrid.setModel(_model);
		_docgrid.setStructure([view]);
		_docgrid.startup();
	};
	this.destroy = function() {
		this.clearObserver();
		if (_docgrid) {
			if (_docgrid.destroy) {
				_docgrid.destroyRecursive(false);
			}
			if (_model.destroy) {
				_model.destroyRecursive(true);
			}
			_docgrid = null;
			_model = null;
		}
	};
	this._init = function() {
		var i = document.createElement("div");
		$(i).attr("id", "personPublicationContainer");
		$("#" + o.container).append($(i));
		$("#" + o.container).css({"height": "99%", "width": "99%"});
		this.id = "personPublicationContainer";
		this.container = o.container;
	};
	this._init();
};
appdb.views.PersonImage = function(o) {
	$.extend(this, new appdb.Template(o));
	var _default = appdb.config.images.person;
	this.doRender = function(d) {
		var _img = _default;
		if (d.image) {
			_img = d.image;
		}
		$(this.viewDom).append("<span><img src='" + _img + "' alt=''width='110' height='110'></img></span>");
	};
	this.destroy = function() {
		$(o.dom).empty();
		o.dom = null;
	};
};

appdb.views.RoleValidation = appdb.ExtendClass(appdb.View, "appdb.views.RoleValidation", function(o) {
	var _imgValid = "close.png", _imgInvalid = "close2.png";
	this._constructor = function() {
		if (typeof o.container === "string") {
			this.id = o.container;
			if ($(this.id).length > 0) {
				this.dom = $($(this.id)[0]);
			}
		} else {
			this.id = $(o.container).attr("id");
			this.dom = $(o.container);
		}
	};
	this.render = function(d) {
		var v = d, _img = (v === 'true' ? _imgValid : _imgInvalid), _title = (v === 'true' ? "Role verified" : "Pending role verification");
		$(this.dom).append("<img src='/images/" + _img + "' alt='' title='" + _title + "' border='0'></img>");
	};
	this.destroy = function() {
		$(this.dom).empty();
	};
	this._constructor();
});
appdb.views.Flag = function(o) {
	$.extend(this, new appdb.Template(o));
	this.doRender = function(d) {
		var i, v = this.getDataItem(d).split("/"), _img;
		for (i = 0; i < v.length; i += 1) {
			_img = $("<img src='/images/flags/" + v[i].toLowerCase() + ".png' alt='' border='0'></img>");
			if (o.attr && o.attr.style) {
				_img.attr("style", o.attr.style);
				$(this.dom).removeAttr("style");
			}
			$(this.viewDom).append(_img);
		}
	};
};

appdb.views.Export = appdb.ExtendClass(appdb.View, "appdb.views.Export", function(o) {
	this.target = "";
	this.exportType = null;
	this.querydata = {};
	this.tooltip = null;
	this.form = null;
	this.exportCvs = null;
	this.exportXml = null;
	this.link = null;
	this.imgExport = null;
	this.isExporting = false;
	this.iframe = null;
	this.InProgressTooltip = null;
	this.query = "";
	this._constructor = function() {
		this.target = o.target;
	};
	this.reset = function() {
		if (this.tooltip) {
			this.tooltip.destroy(false);
			this.InProgressTooltip.destroy(false);
			$(this.exportType).remove();
			$(this.exportXml).remove();
			$(this.exportCsv).remove();
			$(this.link).remove();
			$(this.iframe).remove();
			$(this.form).remove();
		}
		$(this.dom).empty();
	};
	this.submit = function(exType) {
		var ifsrc = $(this.form).attr("action") + "?type=" + exType + this.query;
		if (this.isExporting === true) {
			return;
		}
		this.requestSendMessage();
		$(this.link).remove("iframe");
		this.iframe = null;
		this.iframe = $(document.createElement("iframe"));
		this.iframe.attr('src', ifsrc);
		$(this.iframe).hide();
		$(this.link).append(this.iframe);
	};
	this.requestSendMessage = function() {
		var _this = this;
		setTimeout(function() {
			dijit.popup.open({
				parent: $(_this.link)[0],
				popup: _this.InProgressTooltip,
				around: $(_this.link)[0],
				orient: {BR: 'TR'}
			});
			dijit.popup.close(_this.tooltip);
			setTimeout(function() {
				dijit.popup.close(_this.InProgressTooltip);
			}, 10000);
		}, 30);
	};
	this.normalizeQuery = function(q) {
		if (typeof q === "undefined") {
			return "";
		}
		if (typeof q === "object") {
			var res = "&";
			for (var i in q) {
				res += i + "=" + q[i];
			}
			q = res;
		}
		if (typeof q === "string") {
			if (q === "") {
				q = '&flt=';
			} else {
				q = "&flt=" + encodeURIComponent(q);
			}
		}
		return q;
	};
	this.setValue = function(q) {
		this.query = this.normalizeQuery(q);
	};
	this.render = function() {
		this.reset();
		var _this = this;
		this.link = $('<a title="Export results" href="#" ></a>');
		this.imgExport = $('<img border="0" height="29px" src="/images/export.png" alt="export"/></a>');
		this.form = $('<form id="exportapps" name="exportapps" method="GET" action="' + appdb.config.endpoint.base + this.target + '/export"></form>');
		this.exportXml = $('<span style="white-space: nowrap;display:block;"><a title="Machine oriented, full data export" href="#">Export to XML</a> <a style="font-size:80%" target="_blank" href="files/app_xml_export.xsd">[XSD]</a></span>').click(function() {
			_this.submit('xml');
		});
		this.exportCsv = $('<span style="white-space: nowrap"><a title="Human oriented, concise data export" href="#">Export to CSV</a></span>').click(function() {
			_this.submit('csv');
		});
		$(this.form).append(this.exportXml);
		$(this.form).append(this.exportCsv);
		$(this.link).append(this.imgExport);
		$(this.link).append(this.form);
		$(this.link).click(function() {
			dijit.popup.open({
				parent: $(_this.link)[0],
				popup: _this.tooltip,
				around: $(_this.link)[0],
				orient: {BR: 'TR'}
			});
			return false;
		});
		this.tooltip = new dijit.TooltipDialog({
			title: 'Filter',
			content: $(this.form)[0]
		});
		this.InProgressTooltip = new dijit.TooltipDialog({
			title: 'Filter',
			content: "<div style='width:280px'>The export request has been sent.You will be prompted for the generated data file in a while.</div>"
		});
		$(this.dom).append(this.link);
	};
	this._constructor();
});
appdb.views.RatingAverage = appdb.ExtendClass(appdb.View, "appdb.views.RatingAverage", function(o) {
	this.starBox = null;
	this._constructor = function() {
		if (typeof o.container === "string") {
			this.id = o.container;
			if ($(this.id).length > 0) {
				this.dom = $($(this.id)[0]);
			}
		} else {
			this.id = $(o.container).attr("id");
			this.dom = $(o.container);
		}
		this.starBox = new appdb.views.StarBox({container: $("<span></span>"), isInteractive: false});
	};
	this.reset = function() {
		$(this.dom).empty();
	};
	this.render = function(d) {
		this.reset();
		var s, as = 0, doc = document, title = $(doc.createElement("div")), average = $(doc.createElement("div")), descr = $(doc.createElement("div")), votecount = $(doc.createElement("div")), stars = $(doc.createElement("div"));
		$(this.dom).addClass("ratingaverage");
		$(this.dom).attr("itemscope", "");
		$(this.dom).attr("itemprop", "aggregateRating");
		$(this.dom).attr("itemtype", "http://schema.org/AggregateRating");
		$(this.dom).append('<meta itemprop="bestRating" content="5" />');
		$(this.dom).append('<meta itemprop="worstRating" content="0" />');
		//Render Title
		$(title).addClass("ratingaverage-title").text("Average value");
		$(this.dom).append($(title));

		//Render average number
		$(average).addClass("ratingaverage-value").text(d.average || "0");
		$(average).attr("itemprop", "ratingValue");
		$(this.dom).append($(average));

		//Render textual representation of rating
		s = d.average;
		as = ratingToString(s); //this function exists in appdbgui.js file
		$(descr).addClass("ratingaverage-description").text(as || "unrated");
		$(this.dom).append($(descr));

		//Render stars
		$(this.dom).append($(stars));
		this.starBox.setContainer(stars).render(s);

		//Render total votes for specified type of voting
		$(votecount).html("<span class='ratingaverage-votecount'>votes:</span><span itemprop='ratingCount'>" + d.total + "</span>");
		$(this.dom).append($(votecount));
	};
	this.destroy = function() {
		this.reset();
	};
	this._constructor();
});
appdb.views.StarBox = appdb.ExtendClass(appdb.View, "appdb.views.StarBox", function(o) {
	this.starCount = 5;
	this.isInteractive = false;
	this.images = $.extend({}, appdb.views.StarBox.images);
	this.classname = $.extend({}, appdb.views.StarBox.classname);
	this.style = '';
	this._constructor = function() {
		if (o.images) {
			this.images = $.extend(this.images, o.images);
		}
		if (typeof o.classname === "string" && $.trim(o.classname) !== "") {
			for (var i in this.classname) {
				this.classname[i] = o.classname;
			}
		} else if (typeof o.classname === "object") {
			this.classname = $.extend(this.classname, o.classname);
		}
		this.isInteractive = o.isInteractive || this.isInteractive;
		this.starCount = o.starCount || this.starCount;
		this.style = o.style || this.style;
	};
	this.reset = function() {
		if (this.isInteractive) {
			$(this.dom).find("img").unbind("mouseenter").unbind("mouseleave").unbind("click");
		}
		$(this.dom).empty();
	};
	this._calcStarState = function(i, r) {
		var st = 'empty', as = r - i;
		if (i < r) {
			if (as < 0.25) {
				st = 'empty';
			} else if (as <= 0.75) {
				st = 'half';
			} else {
				st = 'full';
			}
		}
		return st;
	};
	this.render = function(d) {
		this.reset();
		d = d || 0;
		d = parseFloat(d);
		var stars = $(document.createElement("div")), as = ratingToString(d), st = '', img = null, act = this.isInteractive;
		$(stars).addClass("starbox");
		if (act) {
			$(stars).addClass("starbox-interactive");
		}
		for (var i = 0; i < 5; i += 1) {
			st = this._calcStarState(i, d);
			img = $("<img src='" + this.images[st] + "' class='" + this.classname[st] + "' " + ((this.style) ? "style='" + this.style + "'" : "") + " border='0'></img>");
			if (act) {
				$(img).mouseenter((function(t, ind) {
					return function() {
						$(t.dom).find("img").each(function(i, e) {
							var st = t._calcStarState(i, ind);
							$(e).attr("src", t.images[st]).attr("class", t.classname[st]);
						});
						t.publish({event: "mousenter", value: {index: ind, elem: $(this)}});
					};
				})(this, (i + 1)));
				$(img).mouseleave((function(t, ind, rating) {
					return function() {
						$(t.dom).find("img").each(function(i, e) {
							var st = t._calcStarState(i, rating);
							$(e).attr("src", t.images[st]).attr("class", t.classname[st]);
						});
						t.publish({event: "mouseleave", value: {index: ind, elem: $(this)}});
					};
				})(this, (i + 1), d));
				$(img).click((function(t, ind) {
					return function() {
						t.publish({event: "click", value: {index: ind, elem: $(this)}});
					};
				})(this, (i + 1)));
			}
			$(stars).append(img);
		}
		$(this.dom).append($(stars));
	};
	this._constructor();
}, {/* statics used for default values */
	images: {
		full: "/images/star2.png",
		half: "/images/star15.png",
		empty: "/images/star1.png",
		active: "/images/star2.png"
	},
	classname: {
		full: 'starbox_full',
		half: 'starbox_half',
		empty: 'starbox_empty',
		active: 'starbox_active'
	}
});
appdb.views.RatingChart = appdb.ExtendClass(appdb.View, "appdb.views.RatingChart", function(o) {
	this.RatingColors = ["#FF6F31", "#FF9F02", "#FFCF02", "#A4CC02", "#88B131"];
	this.RatingBarStyle = {"height": "21px"};
	this._maxBarWidthValue = 0;
	this._maxVoteValue = 0;
	this._getMaxValue = function(d) {
		var i, len = d.length, max = 0, v;
		for (i = 0; i < len; i += 1) {
			v = parseInt(d[i].votes);
			if (v > max) {
				max = parseInt(d[i].votes);
			}
		}
		return max;
	};
	this._createBar = function(val, color) {
		var div = $(document.createElement("div")), bar = $(document.createElement("div")), v = $(document.createElement("div")), w = "100", max = this._maxVoteValue;
		w = (this._maxBarWidthValue * (val / max));
		w = (w < 1) ? 1 : w;
		w = Math.ceil(w);
		$(bar).addClass("ratingchart-bar").css($.extend({"background-color": color, width: w + "%"}, this.RatingBarStyle));
		$(div).append($(bar));
		$(v).addClass("ratingchart-value").html(val);
		$(div).append($(v));
		$(div).addClass("ratingchart-barcontainer");
		return $(div);
	};
	this._createRow = function(d, unrated) {
		unrated = unrated || false; // Indicates that there is no rating given at all, if non given it renders no value
		var tr = $(document.createElement("tr")); //returning row
		var tdl = $(document.createElement("td")); //display star count
		var tdc = $(document.createElement("td")); //display bar

		//Create stars on the left of the row
		var stars = "<span class='ratingchart-rating' title='Rated with " + d.value + ((d.value > 1) ? " stars" : " star") + "'>";
		for (var i = 0; i < d.value; i += 1) {
			stars += "<img src='/images/star2.png' border='0'></img>";
		}
		stars += "</span>";
		$(tdl).addClass("ratingchart-rating-column").html(stars);
		$(tr).append($(tdl));

		//Append the colored bar
		$(tdc).append((unrated) ? $("<div class='ratingchart-rating-unrated'></span>").css(this.RatingBarStyle) : this._createBar(d.votes, this.RatingColors[d.value - 1]));
		$(tdc).css({"border-left": "1px solid black"});
		$(tr).append($(tdc));
		$(tr).addClass("ratingchart-bar-column");
		return $(tr);
	};
	this.render = function(d) {
		this.reset();
		var row, i, len = d.length, table = $(document.createElement("table")), tbody = $(document.createElement("tbody")), isunrated = false;
		if (d && d.length > 0) {
			d = d.reverse();
		}
		this._maxVoteValue = this._getMaxValue(d);
		isunrated = (this._maxVoteValue === 0) ? true : false;
		$(table).attr("cellspacing", "0").attr("cellpadding", "0").css({width: $(this.dom).css("width"), "padding": "10px"});
		$(table).append($(tbody));
		$(this.dom).addClass("ratingchart").append($(table));
		//Calc maximimum width of bar
		var tval = (100 - parseInt(4 * ('' + this._maxVoteValue).length)) - 2;
		var w = ((tval < 1) ? 15 : tval);
		this._maxBarWidthValue = w;
		//In case of no rating given, it display a descriptive message
		if (isunrated) {
			$(tbody).append("<tr><td></td><td></td><th class='ratingchart-unrated' rowspan='" + (len + len) + "'><i>no votes yet</i></th></tr>");
		}
		for (i = 0; i < len; i += 1) {
			row = $(this._createRow(d[i], isunrated));
			$(tbody).append(row);
			if (i < (len - 1)) {
				$(tbody).append("<tr><td></td><td class='ratingchart-seperator' ></td></tr>");
			}
		}
	};
});
appdb.views.CategoryBox = appdb.ExtendClass(appdb.View, "appdb.views.CategoryBox", function(o) {
	this.render = function() {
	};
});
appdb.views.ErrorHandler = function() {
	var dlg = null;
	this.createDetails = function(d) {
		var i, res = "<ul style='list-style:none;border-style:solid;border-width:2px;border-color:gray;padding:5px;height:250px;overflow:auto;'>";
		if ($.isPlainObject(d)) {
			for (i in d) {
				res += "<li><b>" + i + "</b> : " + d[i] + "</li>";
			}
			res += "</ul>";
		} else if ($.isArray(d)) {
			for (i = 0; i < d.length; i += 1) {
				res += "<li>" + d[i] + "</li>";
			}
			res += "</ul>";
		} else {
			res = d;
		}
		return res;
	};
	this.handle = function(err) {
		this.destroy();
		if (window.unloading === true)
			return;
		var body = $("<table style='width:600px;'><tbody></tbody></table>");
		var descr = $("<tr></tr>");
		var detailsCell = $(document.createElement("td"));
		var detailsRow = $(document.createElement("tr"));
		var detailsSource = $(document.createElement("div"));
		var a = $(document.createElement("a"));
		$(descr).append("<td>" + (err.description || "<i>No description</i>") + "<td>");
		$(body).append($(descr));
		if (err.source) {
			$(descr).append("<tr><tr>");
			$(detailsSource).html(this.createDetails(err.source));
			$(detailsSource).css({width: "99%"});
			$(detailsSource).hide();
			$(a).attr("href", "#").attr("alt", "Click to view source error").text("details").click(function() {
				if ($(detailsSource).css("display") === "none") {
					$(detailsSource).css("display", "block");
				} else {
					$(detailsSource).css("display", "none");
				}
			});
			$(a).css("float", "right");
			$(detailsCell).append($(a));
			$(detailsRow).append($(detailsCell));
			$(body).append($(detailsRow));
			$(body).append($(detailsSource));
		}

		dlg = new dijit.Dialog({
			title: err.status || "Error",
			content: $(body)[0]
		});
		dlg.show();
	};
	this.destroy = function() {
		if (dlg) {
			dlg.destroyRecursive(false);
		}
	};
};

appdb.views.TagOptions = appdb.ExtendClass(appdb.View, "appdb.views.TagOptions", function(o) {
	this._options = null;
	this.ErrorHandler = new appdb.views.ErrorHandler();
	this.reset = function() {
		if (this.tagcombo) {
			this.tagcombo.destroyRecursive(false);
		}
		$(this.dom).empty();
	};
	this._setPolicy = function(appid, policy, prevpolicy) {
		var entity = new appdb.entity.ApplictionTagPolicy({"id": appid, "tagPolicy": policy});
		var xml = appdb.utils.EntitySerializer.toXml(entity);
		var m = new appdb.model.ApplicationTagPolicy();
		m.subscribe({event: "update", callback: function(v) {
				this.render(v);
			}, caller: this});
		m.subscribe({event: "error", callback: function(e) {
				this.ErrorHandler.handle({status: "Could not update tag policy", description: (e.error || e.description), source: null});
				this.render({id: appid, tagPolicy: prevpolicy});
			}, caller: this});
		m.update({query: {}, data: {data: encodeURI(xml)}});
	};
	this._renderDialog = function(appid, policy) {
		policy = parseInt(policy) || 0;
		var res = document.createElement("div"), title = document.createElement("div"), form = document.createElement("form"), commit = document.createElement("a");
		$(title).addClass("tagoptions-title").text("Allow tag insertion by:");
		$(form).attr("id", "tagpolicy");
		$(form).append("<input type='radio' name='policy' value='0' " + ((policy == '0') ? "checked='checked'" : "") + ">Only me</input><div></div>");
		$(form).append("<input type='radio' name='policy' value='1' " + ((policy == '1') ? "checked='checked'" : "") + ">Related contacts</input><div></div>");
		$(form).append("<input type='radio' name='policy' value='2' " + ((policy == '2') ? "checked='checked'" : "") + ">Everyone</input><div></div>");
		$(form).find("input[value='" + policy + "']").attr("checked", "checked");
		$(form).find("input").click((function(_this) {
			return function() {
				_this._setPolicy(appid, $(this).val(), policy);
			};
		})(this, appid));
		$(res).attr("id", "tagpolicydialog").addClass("tagoptions-policy");
		$(res).append($(title));
		$(res).append($(form));
		return res;
	};
	this.render = function(d, reset) {
		reset = reset || true;
		if (reset === true) {
			this.reset();
		}
		if ((d.owner !== "internal") && ((userID !== null && ((d.owner && userID === parseInt(d.owner.id)) || (userRole === 5) || (userRole === 7))) === false)) {
			return;
		}
		var tagButton = document.createElement("span");
		$(tagButton).addClass("taglist-add").addClass("editbutton").append("<span>options</span>");
		$(tagButton).attr("id", "tagoptionsbutton");
		var dialog = this._renderDialog(d.id, d.tagPolicy);
		$(tagButton).click(function() {
			setTimeout(function() {
				var d = new dijit.TooltipDialog({content: $(dialog)[0]});
				dijit.popup.open({
					parent: dojo.byId("tagoptionsbutton"),
					popup: d,
					around: dojo.byId("tagoptionsbutton"),
					orient: {'BL': 'TL', 'BR': 'TR'}
				});
			}, 10);
		});
		$(this.dom).append($(tagButton));
	};
});
appdb.views.TagList = appdb.ExtendClass(appdb.View, "appdb.views.TagList", function(o) {
	this._totalCount = -1;
	this._tagOptions = null;
	this._model = null;
	this._toggler = document.createElement("a");
	this._adder = document.createElement("li");
	this.ErrorHandler = new appdb.views.ErrorHandler();
	this.reset = function() {
		$(this._toggler).unbind("click").empty();
		$(this._adder).empty();
		this._toggler = document.createElement("a");
		$(this._toggler).addClass("taglist-toggler").attr("title", "Click to view all associated tags").text("more...").click((function(t) {
			return function() {
				t._toggleList();
				if (t._isExpanded()) {
					$(this).attr("title", "Click to collapse the tag list").text("...less");
				} else {
					$(this).attr("title", "Click to view all associated tags").text("more...");
				}
			};
		})(this));
		if (this._tagOptions) {
			this._tagOptions.destroy();
		}
		$(this.dom).empty();
	};
	this._getTagCloudValue = function(v) {
		if ($.trim(v) === "") {
			return v;
		}
		var i, len = 0, t = appdb.model.Tags.getLocalData().tag, uv = v.toLowerCase(), tv = "";
		
		if (typeof t === "undefined") {
			return v;
		}
		if ($.isArray(t) === false) {
			t = [t];
		}
		len = t.length;
		for (i = 0; i < len; i += 1) {
			tv = t[i].val();
			if ($.trim(tv) !== "") {
				if (uv === tv.toLowerCase()) {
					return tv;
				}
			}
		}
		return v;
	};
	this._refreshTagCloud = function(renderAdder) {
		if (typeof renderAdder === "undefined") {
			renderAdder = true;
		}
		if (renderAdder) {
			this._postponeAdder = true;
		}

		appdb.model.Tags.get();
	};
	this._deleteTag = function(appid, tagid, elem) {
		var model = new appdb.model.ApplicationTags();
		model.subscribe({event: 'beforeremove', callback: function(d) {
				$(elem).addClass("taglist-tag-removing");
				$(elem).find(".taglist-editable").append("<div style='display:inline;' id='taglist-add-loader'><img src='/images/ajax-loader-small.gif' alt='' title='removing tag' width='12px'> </img><span >removing...</span></div>");
			}, caller: this}).subscribe({event: 'remove', callback: function(d) {
				$(elem).remove();
				var tgs = $(this.dom).find("li.taglist-tag");
				if (tgs.length === 0) {
					$(this.dom).find('.taglist-notags').css({"display": "inline-block"});
				}
				$(tgs).each(function(index) {
					if (index < appdb.views.TagList.maxVisibleTags) {
						$(this).removeClass("taglist-tag-hide");
					} else {
						$(this).addClass("taglist-tag-hide");
					}
				});
				if ($(this.dom).find("li.taglist-tag-hide").length > 0) {
					$(this._toggler).css("display", "inline-block").text("more...");
				} else {
					$(this._toggler).css("display", "none");
				}
				this._refreshTagCloud(false);
			}, caller: this}).subscribe({event: 'error', callback: function(d) {
				$(elem).removeClass("taglist-tag-removing");
				$(elem).remove("img");
				$(elem).find("#taglist-add-loader").remove();
				this.ErrorHandler.handle({status: "Tag deletion error", description: d.error || "Could not delete the selected tag", source: (d.error) ? null : d});
			}, caller: this});
		model.remove({"id": appid, "tagid": tagid});
	};
	this._addTag = function(appid, tag, ownerid) {
		var m = new appdb.model.ApplicationTags();
		tag = this._getTagCloudValue(tag);
		m.subscribe({event: "beforeinsert", callback: function() {
				$(this.dom).find("#taglist-add-edit").css("display", "none");
				$(this.dom).find("#taglist-add-edit").parent().append("<div style='display:inline;' id='taglist-add-loader'><img src='/images/ajax-loader-small.gif' alt='' title='adding tag' width='12px'> </img><span >adding...</span></div>");
			}, caller: this});
		m.subscribe({event: "error", callback: function(e) {
				$(this.dom).find("#taglist-add-loader").remove();
				$(this.dom).find("#taglist-add-edit").css("display", "inline-block");
				this.ErrorHandler.handle({status: "Tag insertion error", description: (e.error || e.description), source: null});
			}, caller: this});
		m.subscribe({event: "insert", callback: function(newtagdata) {
				$(this.dom).find("#taglist-add-loader").remove();
				this._closeAdd();
				var newtag = newtagdata.tag || {};
				setTimeout((function(_this, ntag) {
					return function() {
						tag = tag.replace(/\ /, ".");
						m.destroy();
						$(_this.dom).find('.taglist-notags').css({"display": "none"});
						var uitag = _this._createTag({"ownerid": (newtag.ownerid || 0), "system": (newtag.system || false), "id": (newtag.id || ""), "val": (function(ttag) {
								return function() {
									return ttag;
								};
							})(ntag.val())}, '', true, appid);
						$(_this.dom).find("ul.taglist-list").first().append(uitag);
						_this._refreshTagCloud(false);
					};
				})(this, newtag), 50);
			}, caller: this});
		var tagEntity = new appdb.entity.ApplicationTags({"val": (function(t) {
				return function() {
					return t;
				};
			})(tag)});
		var xml = appdb.utils.EntitySerializer.toXml(tagEntity);
		m.insert({query: {"id": appid}, "data": {"data": xml}});
	};
	this._createTag = function(t, cls, canRemove, appid) {
		canRemove = canRemove || false;
		if (canRemove === false && userID !== null && t.ownerid && parseInt(t.ownerid) != 0) {
			if (userID === parseInt(t.ownerid)) {
				canRemove = true;
			}
		}
		var val = (t.val) ? t.val() : t;
		var res = document.createElement("li");
		var dec = document.createElement("div");
		var rem = document.createElement("div");
		var a = document.createElement("a");
		$(a).attr("title", "Search with tag " + val).attr("href", "#").click(function() {
			var content = "software";
			if (appdb.pages.application.currentEntityType() === "virtualappliance") {
				content = "vappliance";
			}else if(appdb.pages.application.currentEntityType() === "softwareappliance"){
				content = "swappliance";
			}
			appdb.views.Main.showApplications({flt: 'tag:"' + val + '"'}, {isBaseQuery: true, filterDisplay: 'Search with tag ' + val + '...', mainTitle: 'tag: ' + val, content: content});
		}).text(((t.system == "true") ? "::" + val : val));

		if (cls) {
			$(res).addClass(cls);
		}
		$(res).addClass("taglist-tag");
		if (t.system == "true") {
			$(res).addClass("taglist-tag-system");
		}
		if (canRemove) {
			$(rem).addClass("taglist-remove").addClass("taglist-remove-hide").attr("title", "Remove tag " + val);
			$(rem).click((function(_this, tag, id, elem) {
				return function() {
					_this._deleteTag(id, t.id, elem);
				};
			})(this, t, appid, res));
			$(rem).append("<img alt='' src='/images/cancelicon.png' width='12' style='vertical-align:top;padding:0px;margin:0px;padding-right:2px;'></img>");
			$(dec).addClass("taglist-editable-hide").append($(a)).append($(rem));
			$(dec).hover(function() {
				$(dec).removeClass("taglist-editable-hide").addClass("taglist-editable");
			}, function() {
				$(dec).removeClass("taglist-editable").addClass("taglist-editable-hide");
			});
			$(res).append($(dec));
		} else {
			$(res).append($('<div class="taglist-editable-hide"></div>').append($(a)).append("<img alt='' src='/images/cancelicon.png' width='12' style='visibility:hidden;vertical-align:top;padding:0px;margin:0px;padding-right:2px;'></img>"));
		}
		return res;
	};
	this._collapseList = function() {
		if (this.totalCount <= 0) {
			return;
		}
		$(this.dom).find(".taglist-tag").each(function(index, el) {
			if (index < appdb.views.TagList.maxVisibleTags) {
				return;
			}
			$(this).addClass("taglist-tag-hide");
		});
	};
	this._expandList = function() {
		if (this.totalCount <= 0) {
			return;
		}
		$(this.dom).find(".taglist-tag").each(function() {
			$(this).removeClass("taglist-tag-hide");
		});
	};
	this._isExpanded = function() {
		return ($(this.dom).find(".taglist-tag-hide").length > 0) ? false : true;
	};
	this._toggleList = function() {
		if (this._isExpanded()) {
			this._collapseList();
		} else {
			this._expandList();
		}
	};
	this._isEditable = function(d) {
		if (userID === null)
			return false;
		var tagpolicy = $.trim(d.tagPolicy) || "0";
		var owner = appdb.pages.application.getOwner() || {};
		var contactids = appdb.pages.application.getContactPoints() || [];
		owner.id = owner.id || null;
		//0: Only me
		//1: Related Contacts
		//2: Everyone
		switch (tagpolicy) {
			case "0":
				if ($.trim(userID) === $.trim(owner.id)) {
					return true;
				}
				break;
			case "1":
				if ($.inArray($.trim(userID), contactids) > -1) {
					return true;
				}
				break;
			case "2":
				return true;
				break;
			default:
				break;
		}
		return false;
	};
	this._closeAdd = function() {
		var elem = $($(this._adder).find("#taglist-add-edit")[0]).parent();
		if ($(this._adder).has("#taglist-add-edit")) {
			var dj = dijit.byId("taglist-add-select");
			if (dj) {
				dj.destroyRecursive(false);
			}
			$(elem).removeClass("taglist-add-edit").addClass("taglist-add").addClass("editbutton");
			$(elem).find("#taglist-add-edit").remove();
			$(elem).find("#taglist-add").css("display", "inline-block");
		}

	};
	this._renderAdd = function(data, elem, tgs) {
		//cleaning
		var appid = data.id;
		if ($(elem).has("#taglist-add-edit")) {
			var dj = dijit.byId("taglist-add-select");
			if (dj) {
				dj.destroyRecursive(false);
			}
			$(elem).find("#taglist-add-edit").empty().remove();
		}
		//declare elements
		var i, ts = appdb.model.Tags.getLocalData().tag, len = ts.length, list = [];
		var doc = document, select = doc.createElement("input"), ok = doc.createElement("button"), cancel = doc.createElement("button"), res = doc.createElement("div");

		$(res).attr("id", "taglist-add-edit");
		$(res).append($("<span></span>").append($(select))).append($("<span></span>").append($(cancel))).append($("<span></span>").append($(ok)));
		//set elements style
		$(select).addClass("taglist-add-select").attr("id", "taglist-add-select");
		$(ok).addClass("taglist-add-ok").addClass("editbutton").attr("disabled", "disabled").attr("title", "Submit new tag").append("<img src='/images/yes_grey.png' alt='' width='10px'></img>");
		$(cancel).addClass("taglist-add-cancel").addClass("editbutton").attr("title", "Cancel").append("<img src='/images/cancelicon.png' alt='' width='10px'></img>");

		ts.sort(function(a, b) {
			var nameA = a.val(), nameB = b.val();
			if (nameA < nameB) { //sort string ascending
				return -1;
			} else if (nameA > nameB) {
				return 1;
			} else {
				return 0;
			}
		});
		//Transform tag json data for dojo itemfilereadstore
		for (i = 0; i < len; i += 1) {
			list[list.length] = {index: i, tag: ts[i].val()};
		}
		$(elem).removeClass("taglist-add").addClass("taglist-add-edit").addClass("editbutton");
		$($(elem).find("#taglist-add").get(0)).css("display", "none");
		$($(elem).find("#taglist-add-edit").get(0)).css("display", "inline-block");
		$(elem).append(res);
		var _this = this;
		var sel = new dijit.form.ComboBox({
			store: new dojo.data.ItemFileReadStore({
				data: {label: "tag", id: "tag", items: list}
			}),
			searchAttr: "tag",
			ignoreCase: true,
			required: false,
			autoComplete: false,
			onChange: function(val) {
				var v = val.toLowerCase();
				var vlst = [];
				$(_this.dom).find("li.taglist-tag").each(function() {
					vlst[vlst.length] = $(this).text().toLowerCase();
				});
				var i, len = vlst.length, found = false;
				if ($.trim(v) !== '') {
					for (i = 0; i < len; i += 1) {
						if (v == vlst[i]) {
							found = true;
							break;
						}
					}
				}
				if (found || $.trim(v) === '') {
					$(ok).attr("disabled", "disabled");
					$($(ok).find("img").get(0)).attr("src", '/images/yes_grey.png').attr("title", "Submit new tag");
					return true;
				}
				$(ok).removeAttr("disabled");
				$($(ok).find("img").get(0)).attr("src", "/images/yes.png").attr("title", "Submit new tag");
				return true;
			},
			onKeyUp: function(e) {
				var v = sel.attr("value").toLowerCase();
				var vlst = [];
				$(_this.dom).find("li.taglist-tag").each(function() {
					vlst[vlst.length] = $(this).text().toLowerCase();
				});
				var i, len = vlst.length, found = false;
				if ($.trim(v) !== '') {
					for (i = 0; i < len; i += 1) {
						if (v == vlst[i]) {
							found = true;
							break;
						}
					}
				}
				if (found || $.trim(v) == '') {
					$(ok).attr("disabled", "disabled");
					$($(ok).find("img").get(0)).attr("src", '/images/yes_grey.png').attr("title", "Submit new tag");
					return true;
				}
				$(ok).removeAttr("disabled");
				$($(ok).find("img").get(0)).attr("src", "/images/yes.png").attr("title", "Submit new tag");
				return true;
			}
		}, $(elem).find("#taglist-add-select").get(0));
		setTimeout(function() {
			sel.focus();
		}, 100);
		//setup actions
		$(ok).click((function(_this, _appid) {
			return function() {
				_this._addTag(_appid, dijit.byId("taglist-add-select").attr('value'), data.owner.id);
				return false;
			};
		})(this, appid));
		$(cancel).click((function(_this) {
			return function() {
				_this._closeAdd.apply(_this);
				return false;
			};
		})(this));

	};
	this.render = function(d, isAdded) {
		this.reset();
		d = d || {};
		isAdded = isAdded || false;
		var tagoptions = null, cc = $("<table cellpadding='0' cellspacing='0' style='padding:0px;margin:0px;'><tbody><tr><td class='taglist-options-cell'></td><td class='taglist-list-cell'></td></tr></tbody></table>"), c = document.createElement("ul"), i, len, t = d.tag || '', isEditable = this._isEditable(d), max = appdb.views.TagList.maxVisibleTags;
		if ($.isArray(t) === false) {
			t = ($.trim(t) === '') ? [] : [t];
		}
		len = t.length;
		$(c).addClass("taglist-list");
		this._totalCount = len;
		$(this.dom).addClass("taglist");
		tagoptions = document.createElement("span");
		if (userID === null || (userID !== null && isEditable === true)) {
			var sp = document.createElement("span");
			$(sp).attr("id", "taglist-add").attr("title", "Click to add a new tag").text("add");
			$(this._adder).addClass("taglist-add").addClass("editbutton").append(sp).click((function(_this, id, canEdit) {
				return function() {
					if (canEdit) {
						if ($(this).find("#taglist-add-edit").length === 0) {
							appdb.model.Tags.unsubscribeAll(_this).subscribe({event: "select", callback: (function(_thisdom) {
									return function() {
										if (!_this._postponeAdder) {
											appdb.model.Tags.unsubscribeAll(_this);
											_this._renderAdd(d, $(_thisdom), t);
										} else {
											delete _this._postponeAdder;
										}
									};
								})(this), caller: _this}).get();
						}
					} else {
						setTimeout(function() {
							var c = $("<div style='width:450px;'>If you wish to add a new tag, please <b>Sign In</b> first with your account.</div>");
							$($(c).find(".login").get(0)).click(function() {
								dijit.popup.close(d);
								login();
							});
							var d = new dijit.TooltipDialog({content: $(c)[0]});
							dijit.popup.open({
								parent: dojo.byId("taglist-add"),
								popup: d,
								around: dojo.byId("taglist-add"),
								orient: {'BL': 'TL', 'BR': 'TR'}
							});
							setTimeout(function() {
								dijit.popup.close(d);
							}, 7000);
						}, 10);
					}
				};
			})(this, d.id, isEditable));
			$(c).append(this._adder);
		}
		//Sort tags, set forst the system tags then the user tags
		if (len > 1) {
			var systemTags = [], userTags = [];
			for (i = 0; i < len; i += 1) {
				if (t[i].system == "true") {
					systemTags[systemTags.length] = t[i];
				} else {
					userTags[userTags.length] = t[i];
				}
			}
			systemTags.sort(function(a, b) {
				var na = a.val().toLowerCase(), nb = b.val().toLowerCase();
				if (na < nb)
					return -1;
				if (na > nb)
					return 1;
				return 0;
			});
			userTags.sort(function(a, b) {
				var na = a.val().toLowerCase(), nb = b.val().toLowerCase();
				if (na < nb)
					return -1;
				if (na > nb)
					return 1;
				return 0;
			});
			t = userTags.concat(systemTags);
		}
		//end of sorting
		var arr = [];
		for (i = 0; i < len; i += 1) {
			var canremove = ((t[i].system == "false" && (t[i].ownerid == userID || (d.owner && d.owner.id == userID) || ((userRole == 5 || userRole == 7)))) ? true : false);
			if (canremove) {
				arr.push("Can remove tag: " + t[i].val());
			}

			$(c).append(this._createTag({"ownerid": t[i].ownerid || 0, "id": (t[i].id || ""), "system": (t[i].system || false), "val": t[i].val}, ((i < max) ? '' : "taglist-tag-hide"), canremove, d.id));
		}
		if (len > max) {
			$(c).append(this._toggler);
			$(this._toggler).css("display", "inline-block");
		}

		if (len === 0) {
			$(c).append("<li class='taglist-notags'><span >" + appdb.views.TagList.emptyMessage + "</span></li>");
		}
		$(cc).find(".taglist-list-cell").append(c);
		$(this.dom).append(cc);
		$(cc).find(".taglist-options-cell").append($(tagoptions));
		if (userID !== null && ((d.owner && userID === parseInt(d.owner.id)) || (userRole === 5))) {
			this._tagOptions = new appdb.views.TagOptions({container: $(tagoptions)});
			this._tagOptions.render(d);
		}
	};
	this.load = function(appdata) {
		if (this._model !== null) {
			if (this._model.unsubscribeAll)
				this._model.unsubscribeAll();
			if (this._model.destroy)
				this._model.destroy();
			this._model = null;
		}
		this._model = new appdb.model.ApplicationTags();
		this._model.subscribe({event: "select", callback: function(tagdata) {
				var tags = tagdata.tag || [];
				tags = $.isArray(tags) ? tags : [tags];
				var apptags = appdata.tag || [];
				apptags = $.isArray(apptags) ? apptags : [apptags];
				var i, len = tags.length, j, jlen = apptags.length;
				var vtag, atag;
				for (i = 0; i < len; i += 1) {
					vtag = (tags[i].val) ? tags[i].val() : "";
					for (j = 0; j < jlen; j += 1) {
						atag = (apptags[j].val) ? apptags[j].val() : "";
						if (atag == vtag) {
							apptags[j].id = tags[i].id;
						}
					}
				}
				appdata.tag = apptags;
				this.render(appdata);
			}, caller: this}).subscribe({event: "error", callback: function(errdata) {

			}, caller: this});
		this._model.get({"id": appdata.id});
	};
}, {
	maxVisibleTags: 5,
	emptyMessage: "No tags available</span>"
});

appdb.views.TagCloud = appdb.ExtendClass(appdb.View, "appdb.views.TagCloud", function(o) {
	this._tagControl = $.fn.tagcloud; //backup
	this._tagsort = $.fn.tsort;//backup
	this.render = function(d) {
		this.reset();
		d = d || {};
		var h = document.createElement("ul"), i, len, t = d.tag || [], v;
		len = t.length;
		$(h).css({"width": "95%", "height": "200px", "position": "static"});
		for (i = 0; i < len; i += 1) {
			if (!t[i] || $.isFunction(t[i].val) == false) {
				continue;
			}
			v = t[i].val();
			$(h).append("<li value='" + t[i].count + "'><a href='#' onmouseover='$(this).data(\"bckcolor\",$(this).css(\"color\"));$(this).css({color:\"#D96B00\"});' onmouseout='$(this).css({color:\"\"+$(this).data(\"bckcolor\")});' title='click to search for " + v + "' onclick='appdb.views.Main.showApplications({flt:\"=tag:\\\"" + v + "\\\"\"},{isBaseQuery:true,filterDisplay:\"Search with tag " + v + "...\",mainTitle : \"tag: " + v + "\"});' >" + v + " </a></li>");
		}
		$(this.dom).append($(h));
		var c = $($(this.dom).find("ul").get(0));
		if (typeof c.tagcloud === "undefined") {
			c.tagcloud = this._tagControl;
		}
		c.tagcloud({type: "list", sizemin: 12, sizemax: 24, colormin: "727070", colormax: "727FFF"});
		var ts = c.find("li");
		if (typeof ts.tsort === "undefined") {
			ts.tsort = this._tagsort;
		}
		$($(this.dom).find("ul")[0]).css("width", "95%");
		ts.tsort();
	};
});
appdb.views.NewsFeed = appdb.ExtendClass(appdb.View, "appdb.views.NewsFeed", function(o) {
	this.newsImage = appdb.views.NewsFeed.icon;
	this.customizable = appdb.views.NewsFeed.customizable || false;
	this.customDialog = null;
	this.feed = {
		type: "app",
		name: "",
		title: "",
		actions: [
			{value: "insert", name: "New Entries", selected: true},
			{value: "update", name: "Updates", selected: true},
			{value: "insertcmm", name: "New comments", selected: false},
			{value: "insertcnt", name: "New contacts", selected: false}],
		flt: "",
		format: appdb.views.NewsFeed.format
	};
	this._constructor = function() {
		if (o.image) {
			this.newsImage = o.image;
		}
		if (typeof o.customizable === "boolean") {
			this.customizable = (Boolean(o.customizable) && true);
		}
		this.feed = $.extend(o.feed, this.feed);
	};
	this._createFeed = function() {
		var domain = appdb.config.endpoint.base, q = "", feed = this.feed, f, actions = [];
		if (domain.substr(0, 6) === "https:") {
			domain = "http:" + domain.substr(6, domain.length);
		}
		domain = domain + "news/" + (feed.format || "atom");
		if (typeof feed.flt === "string") {
			f = appdb.utils.base64.encode(feed.flt);
		}
		for (var i = 0; i < this.feed.actions.length; i += 1) {
			if (this.feed.actions[i].selected === true) {
				actions[actions.length] = this.feed.actions[i].value;
			}
		}
		actions = actions.join(",");
		q = ((feed.type) ? "t=" + feed.type + "&" : "") + ((feed.name) ? "n=" + feed.name + "&" : "") + ((f) ? "f=" + f + "&" : "") + ((actions !== '') ? "a=" + actions + "&" : "") + ((feed.title) ? "title=" + appdb.utils.base64.encode(feed.title) : "");
		if (q.length > 0) {
			if (q[q.length - 1] === "&") {
				q = q.substr(0, q.length - 1);
			}
			domain = domain + "?" + q;
		}
		return domain;
	};
	this._renderCustomFeed = function(v) {
		var i, doc = document, res = doc.createElement("div"), desc = doc.createElement("span"), title = doc.createElement("span"),
				format = doc.createElement("span"), actions = doc.createElement("span"), commands = doc.createElement("span");
		$(res).addClass("customfeed");
		$(desc).addClass("description");
		$(title).append("<span class='title'>Title:</span>").
				append("<input type='text'  />").
				addClass("title");
		(new dijit.form.TextBox({
			value: this.feed.title,
			"class": "title",
			onChange: (function(_this) {
				return function(val) {
					_this.feed.title = val;
					_this._setFeedUrl(_this._createFeed());
				};
			})(this)
		},
		$(title).find("input")[0]));

		$(format).append("<span class='title'>Format:</span>").
				append("<span class='radiooption'><input type='radio' name='feedformat' value='atom'  checked='checked' /><span>atom</span></span>").
				append("<span class='radiooption'><input type='radio' name='feedformat' value='rss'  /><span>rss</span></span>").
				addClass("format");
		$(format).find("span.radiooption > input").each((function(_this) {
			return function(index, elem) {
				(new dijit.form.RadioButton({
					checked: (index === 0),
					onChange: (function(e) {
						return function(v) {
							if (Boolean(v) === true) {
								_this.feed.format = $(e).val();
								_this._setFeedUrl(_this._createFeed());
							}
						};
					})(elem)
				}, $(this)[0]));
			};
		})(this));

		$(actions).append("<span class='title'>News about:</span>").addClass("actions");
		for (i = 0; i < this.feed.actions.length; i += 1) {
			$(actions).append("<span class='checkoption'><input type='checkbox' value='" + this.feed.actions[i].value + "' checked  /><span>" + this.feed.actions[i].name + "</span></span>");
		}
		$(actions).find("span.checkoption > input").each((function(_this) {
			return function(index, elem) {
				(new dijit.form.CheckBox({
					checked: _this.feed.actions[index].selected,
					onChange: (function(e) {
						return function(v) {
							var i, found = -1, acts = _this.feed.actions, name = $(e).val(), val = Boolean(v);
							for (i = 0; i < acts.length; i += 1) {
								if (acts[i].value === name) {
									found = i;
									break;
								}
							}
							if (found >= 0) {
								acts[found].selected = val;
							}
							_this.feed.actions = acts;
							_this._setFeedUrl(_this._createFeed());
						};
					})(elem)
				}, $(this)[0]));
			};
		})(this));

		$(commands).
				append("<span class='command viewfeed' ><a href='" + this._createFeed() + "' alt='' title='View news feed in new window.' target='_blank' >View feed</a></span>").
				addClass("commands");
		$(res).append(desc).append(title).append(format).append(actions).append(commands);
		return res;
	};
	this._setFeedUrl = function(u) {
		var a = $(this.dom).find("a.atomfeed-link");
		if (a.length > 0) {
			a = $(a[0]);
			$(a).attr("href", u);
		}
		if (this.customizable) {
			$($(this.customDialog).find(".viewfeed > a")[0]).attr("href", u);
		}
	};
	this.render = function(d) {
		this.reset();
		if (typeof d === "string") {
			d = {flt: d};
		}
		this.feed = $.extend(this.feed, d);
		var u = this._createFeed();
		var div = document.createElement("div"), a = document.createElement("a"), a2 = document.createElement("a"), span = document.createElement("span"), img = document.createElement("img");
		$(span).append("&#9662;");
		$(a).attr("target", "_blank").attr("href", "#").attr("title", "Open the news feed for the listed items").addClass("atomfeed-link");
		$(img).attr("src", this.newsImage).attr("alt", "").attr("border", "0");
		$(a).append(img);
		if (this.customizable) {
			$(a2).attr("target", "_blank").attr("title", "Customize the news feed").addClass("atomfeed-select").css({visibility: "hidden"}).append(span);
			this.customDialog = this._renderCustomFeed();
			$(this.customDialog).click(function(event) {
				var src = event.srcElement ? event.srcElement : event.target;
				if (src && src.tagName.toLowerCase() === "a") {
					return true;
				}
				return false;
			});
			$(a).click((function(_this, parent) {
				return function() {
					setTimeout(function() {
						var d = new dijit.TooltipDialog({content: $(_this.customDialog)[0]});
						dijit.popup.open({
							parent: parent,
							popup: d,
							around: parent,
							orient: {'BR': 'TR'}
						});
					}, 10);
					return false;
				};
			})(this, $(a)[0]));
		}
		$(div).append(a).addClass("atomfeed");
		if (this.customizable) {
			$(div).append(a2).addClass("customizable");
		}
		$(this.dom).append(div);
	};
	this._constructor();
}, {
	icon: "/images/atom.png",
	format: "atom"
});
appdb.views.ApplicationUrls = appdb.ExtendClass(appdb.View, "appdb.views.ApplicationUrls", function(o) {
	this._seperator = appdb.views.ApplicationUrls.seperator;
	this._data = [];
	this._currentPopup = null;
	this._constructor = function() {
		this._seperator = o.seperator || this._seperator;
	};
	this.reset = function() {
		$(this.dom).find("span > a").each(function() {
			$(this).unbind("click");
		});
		$(this.dom).empty();
	};
	this._showUrlList = function(elem, data) {
		if (this._currentPopup !== null) {
			this._currentPopup.cancel();
			dijit.popup.close(this._currentPopup);
			this._currentPopup.destroyRecursive(false);
		}
		this._currentPopup = new dijit.TooltipDialog({content: $(data)[0]});
		dijit.popup.open({
			parent: elem,
			popup: this._currentPopup,
			around: elem,
			orient: {'BR': 'TR', 'BL': 'TL'}
		});
	};
	this._renderListHtml = function(data) {
		var i, val, sval, res = "";
		for (i = 0; i < data.length; i += 1) {
			if (typeof data[i].val === "undefined") {
				continue;
			}
			val = data[i].val();
			sval = val;
			if (sval.length > 30) {
				sval = sval.slice(0, 27) + "...";
			}
			res += "<li><a href='" + val + "' target='_blank' title='Click to open link in new window\n " + val + "'>" + (data[i].title || sval).htmlEscape() + "</a></li>";
		}
		res = "<div class='urllist'><ul>" + res + "</ul></div>";
		return res;
	};
	this._renderListItem = function(name, data) {
		var htmllist, span = document.createElement("span"), a = document.createElement("a"), arrow = document.createElement("span");
		$(span).addClass("urlitem");
		$(arrow).addClass("urllistarrow").append("&#9660;");
		$(a).attr("href", "#").attr("title", "There are " + data.length + " " + name + " urls. Click to view them.").text(name + " (" + data.length + ")");
		htmllist = this._renderListHtml(data);
		$(a).click((function(_this, elem, html) {
			return function() {
				_this._showUrlList(elem, html);
				return false;
			};
		})(this, $(arrow)[0], htmllist));
		$(a).append(arrow);
		$(span).append(a);
		return span;
	};
	this._renderItem = function(data) {
		var span = document.createElement("span"), a = document.createElement("a");
		$(span).addClass("urlitem");
		$(a).attr("href", data.val()).attr("target", "_blank").attr("title", data.val()).text(data.type);
		$(span).append(a);
		return span;
	};
	this.render = function(d) {
		this.reset();
		this._data = d;
		var i, count = 0, data = appdb.utils.GroupObjectList(d, "type", ["try it", "website", "download", "documentation", "support", "press", "contextualization", "*"]);
		if (data == null) {
			return;
		} else {
			for (i in data) {
				count += 1;
			}
		}
		var container = document.createElement("div");
		$(container).addClass("applicationurls");
		for (i in data) {
			count -= 1;
			$(container).append(this._renderListItem(i, data[i]));
			if (count > 0) {
				var sep = document.createElement("span");
				$(sep).addClass("urlseperator");
				$(sep).text(this._seperator);
				$(container).append(sep);
			}
		}
		$(this.dom).append(container);
	};
	this._constructor();
}, {seperator: " | "});

appdb.views.ApplicationUrlsEditorItem = appdb.ExtendClass(appdb.View, "appdb.views.ApplicationUrlsEditorItem", function(o) {
	this._data = {id: "", type: "", url: "", title: "", index: 0};
	this._index = 0;
	this._types = null;
	this._title = null;
	this._url = null;
	this._remove = null;

	this.getIndex = function() {
		return this._data.index;
	};
	this.getData = function() {
		var d = this._data;
		return {id: d.id, type: d.type, url: d.url, title: d.title};
	};
	this.reset = function() {
		if (this._types !== null) {
			this._types.destroyRecursive(false);
		}
		if (this._title !== null) {
			this._title.destroyRecursive(false);
		}
		if (this._url !== null) {
			this._url.destroyRecursive(false);
		}
		if (this._remove != null) {
			this._remove.destroyRecursive(false);
		}
		$(this.dom).empty();
	};
	this.getValidUrlType = function(t) {
		var v = appdb.views.ApplicationUrlsEditorItem.typestore.data.items;
		var item, len = v.length;
		for (var i = 0; i < len; i += 1) {
			item = v[i].name;
			if ($.isArray(item) == false) {
				item = [item];
				if (item == t) {
					return t;
				}
			}
		}
		return "";
	};
	this.render = function(d, index) {
		this.reset();
		var data = d || {}, i = index || 0, td1 = document.createElement("td"), td2 = document.createElement("td"),
				td3 = document.createElement("td"), td4 = document.createElement("td"), a = document.createElement("a");
		if (typeof data.val === "undefined") {
			if (data.url) {
				data.val = (function(durl) {
					return function() {
						return durl;
					};
				})(data.url);
			} else {
				data.val = function() {
					return "";
				};
			}
		}
		this._data = {id: data.id || "", type: data.type || "", url: data.val() || "", title: data.title || "", index: index || 0};
		$(td1).addClass("appurlitem").addClass("type").append("<span></span>");
		$(td2).addClass("appurlitem").addClass("url").append("<span></span>");
		$(td3).addClass("appurlitem").addClass("title").append("<span></span>");
		$(td4).addClass("appurlitem").addClass("remove").append("<span></span>");
		$(td4).find("span").append(a);
		$(a).attr("href", "#").append("<img style='vertical-align: middle; padding-right: 3px;' alt='remove' border='0' src='/images/cancelicon.png' title='Remove url' />").click((function(_this) {
			return function() {
				setTimeout(function() {
					_this.parent.removeItem(_this);
				}, 1);
			};
		})(this));
		$(this.dom).append(td1).append(td2).append(td3).append(td4);
		this._id = d.id || "";
		this._types = new dijit.form.FilteringSelect({
			name: "combo_url" + i,
			store: new dojo.data.ItemFileReadStore({data: {label: "name", id: "id", items: [{id: "Website", name: "Website"}, {id: "Documentation", name: "Documentation"}, {id: "Download", name: "Download"}, {id: "Support", name: "Support"}, {id: "Multimedia", name: "Multimedia"}, {id: "Try it", name: "Try it"}, {id: "Press", name: "Press"}, {id: "Contextualization", name: "Contextualization"}]}}),
			value: this.getValidUrlType(data.type),
			displayedValue: this.getValidUrlType(data.type),
			searchAttr: "name",
			placeHolder: "please select a type",
			autoComplete: true,
			required: true,
			onChange: (function(_this) {
				return function(v) {
					if (v === "") {
						_this._data.type = "";
					} else if (typeof v !== "undefined") {
						var vv = appdb.views.ApplicationUrlsEditorItem.typestore.data.items[v].name;
						_this._data.type = ($.isArray(vv) ? vv[0] : vv);
					}
				};
			})(this),
			onBlur: (function(_this) {
				return function(v) {
					if (this.value === "") {
						_this._data.type = "";
					} else {
						var vv = appdb.views.ApplicationUrlsEditorItem.typestore.data.items[this.value].name;
						_this._data.type = ($.isArray(vv) ? vv[0] : vv);
					}
				};
			})(this)
		}, $(td1).find("span")[0]);
		setTimeout((function(_this) {
			return function() {
				var v = _this._types.attr("value");
				if (v === "") {
					_this._data.type = v;
				} else {
					var vv = appdb.views.ApplicationUrlsEditorItem.typestore.data.items[v].name;
					_this._data.type = ($.isArray(vv) ? vv[0] : vv);
				}
			};
		})(this), 1);

		this._url = new dijit.form.ValidationTextBox({
			name: "url" + i,
			value: data.val(),
			placeHolder: "please provide a url",
			required: true,
			onChange: (function(_this) {
				return function(v) {
					_this._data.url = v;
				};
			})(this)
		}, $(td2).find("span")[0]);
		this._title = new dijit.form.ComboBox({
			name: "title_url" + i,
			value: data.title || "",
			store: new dojo.data.ItemFileReadStore(appdb.views.ApplicationUrlsEditorItem.titlestore),
			placeHolder: "please provide a title",
			searchAttr: "title",
			maxLength: 50,
			onChange: (function(_this) {
				return function(v) {
					_this._data.title = v;
				};
			})(this)
		}, $(td3).find("span")[0]);
	};
}, {
	typestore: {data: {label: "name", id: "id", items: [{id: "Website", name: "Website"}, {id: "Documentation", name: "Documentation"}, {id: "Download", name: "Download"}, {id: "Support", name: "Support"}, {id: "Multimedia", name: "Multimedia"}, {id: "Try it", name: "Try it"}, {id: "Press", name: "Press"}, {id: "Contextualization", name: "Contextualization"}]}},
	titlestore: {data: {label: "title", id: "title", items: []}}
});

appdb.views.ApplicationUrlsEditor = appdb.ExtendClass(appdb.View, "appdb.views.ApplicationUrlsEditor", function(o) {
	this._initialData = [];
	this._data = [];
	this.getItemById = function(id) {
		var i, len = this.subviews.length;
		for (i = 0; i < len; i += 1) {
			if (this.subviews[i].getData().id === id) {
				return this.subviews[i];
			}
		}
		return null;
	};
	this.canAddNew = function() {
		var i, len = this.subviews.length, item, d;
		for (i = 0; i < len; i += 1) {
			item = this.subviews[i];
			d = item.getData();
			if (d.type === "") {
				return item.dom;
			}
			if (d.url === "") {
				return item.dom;
			}
		}
		return true;
	};
	this._showAsInvalid = function(item) {
		var prevColor = $(item).css("background-color") || "";
		$(item).animate({"opacity": "0.5", "background-color": "red"}, 300, function() {
			setTimeout(function() {
				$(item).animate({"opacity": "1", "background-color": prevColor}, 400);
			}, 10);
		});
	};
	this.getData = function() {
		var i, len = this.subviews.length, res = [];
		for (i = 0; i < len; i += 1) {
			res[res.length] = this.subviews[i].getData();
		}
		return res;
	};
	this.removeItem = function(item) {
		this._data = this.getData();
		if (this._data.length > item.getIndex()) {
			this._data.splice(item.getIndex(), 1);
			this.render(this._data);
		}
	};
	this.addNew = function() {
		var canAdd = this.canAddNew();
		if (canAdd !== true) {
			this._showAsInvalid(canAdd);
			return;
		}
		var tr = document.createElement("tr"), item, tbody = $(this.dom).find("table > tbody")[0];
		$(tbody).append(tr);
		item = new appdb.views.ApplicationUrlsEditorItem({container: tr, parent: this});
		item.render({}, this.subviews.length);
		this.subviews[this.subviews.length] = item;
		this._data[this._data.length] = item.getData();
	};
	this.setupForm = function(frm) {
		if ($(frm).length == 0) {
			return false;
		}
		var i, len = this.subviews.length, item, inp;
		$(frm).remove("input[name^='url']");
		for (i = 0; i < len; i += 1) {
			item = this.subviews[i];
			inp = document.createElement("input");
			$(inp).attr("name", "url" + i).attr("type", "hidden").attr("value", JSON.stringify(item.getData()));
			$(frm).append(inp);
		}
		return true;
	};
	this._setupHeader = function() {
		var thead = document.createElement("thead"), tr, th1, th2, th3;
		tr = document.createElement("tr");
		th1 = document.createElement("th");
		th2 = document.createElement("th");
		th3 = document.createElement("th");
		th4 = document.createElement("th");
		$(tr).append(th1).append(th2).append(th3).append(th4);
		$(thead).append(tr);

		$(th1).append("<span class='appurlsheader type'>" + appdb.views.ApplicationUrlsEditor.header.type + "</span>");
		$(th2).append("<span class='appurlsheader url'>" + appdb.views.ApplicationUrlsEditor.header.url + "</span>");
		$(th3).append("<span class='appurlsheader title'>" + appdb.views.ApplicationUrlsEditor.header.title + "</span>");
		$(th4).append("<span class='appurlsheader actions'></span>");
		return thead;
	};
	this._setupFooter = function() {
		var tfoot = document.createElement("tfoot"), tr = document.createElement("tr"), td = document.createElement("td");
		$(td).addClass("appurls-footer").attr("colspan", "4").append("<span class='appurls-empty'>" + appdb.views.ApplicationUrlsEditor.footer.empty + "</span>").append("<span class='appurls-add'></span>");
		$(tr).append(td);
		$(tfoot).append(tr);
		new dijit.form.Button({
			label: "<i><b>add</b></i>",
			onClick: (function(_this) {
				return function() {
					_this.addNew();
				};
			})(this)
		}, $(td).find("span.appurls-add")[0]);
		return tfoot;
	};

	this.render = function(d, sortby) {
		this.render = this.onrender;
		if ($.isArray(d) === false) {
			d = [d];
		}
		this._initialData = d;
		this.render(d, sortby);
	};
	this.onrender = function(d, sortby) {
		this.reset();
		var table = document.createElement("table"), tbody = document.createElement("tbody"),
				tr, urls = ((sortby) ? appdb.utils.SortObjectList({list: d, property: sortby}) : d), i, len, item;
		$(table).addClass("appurls-editor").attr("cellspacing", "0").attr("cellpadding", "0").append(this._setupHeader()).append(tbody);
		$(this.dom).append(table);

		if ($.isArray(urls) === false) {
			urls = [urls];
		}
		this._data = urls;
		len = urls.length;
		for (i = 0; i < len; i += 1) {
			tr = document.createElement("tr");
			$(tbody).append(tr);
			item = new appdb.views.ApplicationUrlsEditorItem({container: tr, parent: this});
			item.render(urls[i], i);
			this.subviews[this.subviews.length] = item;
		}
		$(table).append(this._setupFooter());
	};
	this.hasChanges = function() {
		//in case of item count change.
		if (this._initialData.length !== this._data.length) {
			return true;
		}
		//If a new item is added
		if (this.getItemById("") !== null) {
			return true;
		}
		var i, len = this._initialData.length;
		for (i = 0; i < len; i += 1) {
			var item = this.getItemById(this._initialData[i].id);
			//Item must be removed
			if (item === null) {
				return true;
			}
			var ditem = item.getData();
			if (typeof this._initialData[i].title === "undefined") {
				this._initialData[i].title = "";
			}
			if (ditem.id != this._initialData[i].id || ditem.type != this._initialData[i].type || ditem.url != ((this._initialData[i].val) ? this._initialData[i].val() : "") || (ditem.title != this._initialData[i].title)) {
				return true;
			}
		}
		return false;
	};
	this._preloadUrlTitles = function() {
		var store = [], e = appdb.model.UsedAppUrlTitles.getLocalData();
		if (e && typeof e.title !== "undefined") {
			for (var i = 0; i < e.title.length; i += 1) {
				store[store.length] = {title: (e.title[i].val) ? e.title[i].val() : ""};
			}
		}
		appdb.views.ApplicationUrlsEditorItem.titlestore.data.items = store;
		appdb.model.UsedAppUrlTitles.get();
	};
	this._preloadUrlTitles();
}, {
	header: {
		title: "Title<i style='color:gray;font-size:10px;font-weight:normal;'> (optional)</i>",
		type: "Type",
		url: "Url"
	},
	footer: {
		empty: "You can provide related urls by clicking the button on the right"
	}
});

appdb.views.ApplicationCategoriesEditorItem = appdb.ExtendClass(appdb.View, "appdb.views.ApplicationCategoriesEditorItem", function(o) {
	this._data = {id: "", primary: ""};
	this._index = 0;
	this._categories = null;
	this._id = null;
	this._primary = null;
	this._remove = null;

	this.getIndex = function() {
		return this._data.index;
	};
	this.getData = function() {
		var d = this._data;
		return {id: d.id, primary: d.primary};
	};
	this.reset = function() {
		if (this._categories !== null) {
			this._categories.destroyRecursive(false);
		}
		if (this._primary !== null) {
			this._primary.destroyRecursive(false);
		}
		if (this._remove != null) {
			this._remove.destroyRecursive(false);
		}
		$(this.dom).empty();
	};

	this.getCategoriesStore = function(initialData) {
		var ld = appdb.model.ApplicationCategories.getLocalData();
		var c = ld.category;
		var res = [];
		c = $.isArray(c) ? c : [c];
		var i, len = c.length;
		for (i = 0; i < len; i += 1) {
			if (this.parent.hasItem(c[i].id, this))
				continue;
			res.push({name: c[i].val(), id: c[i].id});
		}
		return {data: {identifier: "id", label: "name", id: "id", items: res}};
	};
	this.getCategoriesOptions = function(initialData) {
		var ld = appdb.model.ApplicationCategories.getLocalData();
		var c = ld.category;
		var res = [];
		c = $.isArray(c) ? c : [c];
		var i, len = c.length;
		for (i = 0; i < len; i += 1) {
			if (this.parent.hasItem(c[i].id, this))
				continue;
			res.push({label: c[i].val(), value: c[i].id});
		}
		if (res.length > 0) {
			res[0].selected = true;
		}
		return res;
	};
	this.updateCategoryOptions = function() {
		if (typeof this._categories === "undefined")
			return;

		var oldstore = this._categories.getOptions(), store = this.getCategoriesStore(), i = 0;
		len = oldstore.length;
		for (i = 0; i < len; i += 1) {
			if (oldstore[i].value == this._data.id)
				continue;
			this._categories.removeOption(oldstore[i]);
		}
		len = store.data.items.length;
		for (i = 0; i < len; i += 1) {
			if (store.data.items[i].id == this._data.id)
				continue;
			this._categories.addOption(dojo.create("option", {label: store.data.items[i].name, value: store.data.items[i].id}));
		}
		this._categories.startup();
	};
	this.render = function(d, index, initialData) {
		this.reset();
		var data = d || {}, i = index || 0, td1 = document.createElement("td"), td2 = document.createElement("td"),
				td3 = document.createElement("td"), a = document.createElement("a"), tdhelp = document.createElement("td");
		this._data = this._data || {id: d.id};
		var cstore = this.getCategoriesStore(initialData);

		this._data = {id: data.id || "", primary: data.primary || false, index: index || 0};
		$(td1).addClass("appcategoryitem").addClass("primary").append("<span></span>");
		$(tdhelp).addClass("appcategoryitem").addClass("help");
		if (appdb.views.ApplicationCategoriesEditorItem.showPrimaryHelp == true) {
			$(tdhelp).append("<img src='/images/question_mark.gif' border='0'/>");
		}
		$(td2).addClass("appcategoryitem").addClass("category").append("<span></span>");
		$(td3).addClass("appcategoryitem").addClass("remove").append("<span></span>");
		$(td3).find("span").append(a);
		$(a).attr("href", "#").append("<img style='vertical-align: middle; padding-right: 3px;' alt='remove' border='0' src='/images/cancelicon.png' title='Remove category' />").click((function(_this) {
			return function() {
				setTimeout(function() {
					_this.parent.removeItem(_this);
				}, 1);
			};
		})(this));
		$(this.dom).append(td1).append(tdhelp).append(td2).append(td3);
		this._id = d.id || "";
		this._categories = new dijit.form.Select({
			name: "combo_categories" + i,
			style: "width:130px;vertical-align:middle;",
			value: data.id || "-1",
			options: this.getCategoriesOptions(initialData),
			onChange: (function(_this) {
				return function(v) {
					if (v === "") {
						_this._data.id = "-1";
					} else if (typeof v !== "undefined") {
						_this._data.id = v;
					}
					_this.setupLogo();
					if (_this._categories)
						_this.parent.updateItemCategories();
				};
			})(this)
		}, $(td2).find("span")[0]);
		this.updateCategoryOptions();
		setTimeout((function(_this) {
			return function() {
				var v = _this._categories.attr("value");
				if (v === "") {
					_this._data.id = "-1";
				} else {
					_this._data.id = v;
				}
			};
		})(this), 1);

		this._primary = new dijit.form.RadioButton({
			checked: (d.primary == "true") ? true : false,
			style: "width:15px;",
			name: "primary",
			onChange: (function(_this) {
				return function(v) {
					if (v == true) {
						_this.setupHelp();
					}
					_this._data.primary = (v) ? "true" : "false";
					_this.setupLogo();
				};
			})(this)
		}, $(td1).find("span")[0]);
		if (d.primary == "true") {
			this.setupHelp();
			this.setupLogo();
		}
		new dijit.Tooltip({
			connectId: [this._primary.domNode],
			label: "<span class='app-category-primary-tooltip'>" + appdb.views.ApplicationCategoriesEditor.header.primaryTooltip + "</span>",
			position: ["before", "after"]
		});
	};
	this.setupHelp = function() {
		if (appdb.views.ApplicationCategoriesEditorItem.showPrimaryHelp !== true) {
			return;
		}
		$(this.parent.dom).find(".help.show").removeClass("show");
		$(this.dom).find(".help").addClass("show");
		new dijit.Tooltip({
			connectId: [$(this.dom).find(".help")[0]],
			label: "<span>" + appdb.views.ApplicationCategoriesEditorItem.help.primary + "</span>",
			position: ["below", "after"]
		});
	};
	this.setupLogo = function() {
		if (!this._data.primary || this._data.primary == "false") {
			return;
		}
		var name = null, id = this._data.id, c, cats = this.getCategoriesOptions(), clen = cats.length;
		for (c = 0; c < clen; c += 1) {
			if (cats[c].value == id) {
				name = cats[c].label;
				break;
			}
		}
		if (name) {
			var src = $("#infodiv" + dialogCount + " img.app-logo").attr("src");
			if (src.substr(0, 7) === "/images/" || src.substr(0, 8) === "/images/") {
				src = getLogoForCategory({category: [{primary: "true", id: id, val: function() {
								return name;
							}}]});
				$("img.app-logo").attr("src", src);
			}
		}

	};
	this.getValidCategoryId = function(t, c) {
		var i, len = c.length, name;
		for (i = 0; i < len; i += 1) {
			name = ($.isArray(c[i].name)) ? c[i].name[0] : c[i].name;
			if (name.toLowerCase() === t.toLowerCase()) {
				return ($.isArray(c[i].id)) ? c[i].id[0] : c[i].id;
			}
		}
		return -1;
	};
	this.getValidCategoryName = function(t, c) {
		var i, len = c.length;
		for (i = 0; i < len; i += 1) {
			if (c[i].id == t) {
				return c[i].name;
			}
		}
		return "";
	};
}, {
	categoriesstore: {data: {label: "name", id: "id", items: []}},
	showPrimaryHelp: true,
	help: {
		primary: "<div style='width:250px;text-align:justify;'><span style='font-weight: bold; font-style:italic;'>'Primary category'</span> should be the most characteristic category of this software item. <br />It will be used by the system for cases where only one category can be used, such as new feeds, email notifications and other dissemination related activities. <br/><br/><i>It does not restrict the definition of the software in any other way and it can be changed at any later time.</i></div>"
	}
});

appdb.views.ApplicationCategoriesEditor = appdb.ExtendClass(appdb.View, "appdb.views.ApplicationCategoriesEditor", function(o) {
	this._initialData = [];
	this._data = [];
	this._adder = null;
	this.getItemById = function(id) {
		var i, len = this.subviews.length;
		for (i = 0; i < len; i += 1) {
			if (this.subviews[i].getData().id === id) {
				return this.subviews[i];
			}
		}
		return null;
	};
	this.hasItem = function(id, caller) {
		var i, res = this.getData(), len = res.length;
		for (i = 0; i < len; i += 1) {
			if (res[i].id == caller.getData().id)
				continue;
			if (res[i].id == id) {
				return true;
			}
		}
		return false;
	};
	this.isAlreadyUsed = function(id) {
		if (!initialData)
			return false;
		initialData = $.isArray(initialData) ? initialData : [initialData];
		var i, len = initialData.length;
		for (i = 0; i < len; i += 1) {
			if (initialData[i].id == id) {
				return true;
			}
		}
		return false;
	};
	this.updateItemCategories = function() {
		var sb = this.subviews || [], i = 0, len = sb.length;
		for (i = 0; i < len; i += 1) {
			sb[i].updateCategoryOptions();
		}
	};
	this.isValid = function() {
		if (this.subviews.length == 0) {
			return false;
		}
		if (this.getPrimaryItem() == "-1") {
			return false;
		}
		var i, len = this.subviews.length, item, d;
		for (i = 0; i < len; i += 1) {
			item = this.subviews[i];
			d = item.getData();
			if (d.id === "" || d.id == "-1") {
				return false;
			}
		}
		return true;
	};
	this.canAddNew = function() {
		var i, len = this.subviews.length, item, d;
		for (i = 0; i < len; i += 1) {
			item = this.subviews[i];
			d = item.getData();
			if (d.id === "" || d.id == "-1") {
				return item.dom;
			}
		}
		if (appdb.views.ApplicationCategoriesEditorItem.categoriesstore.data.items.length == len) {
			return false;
		}
		return true;
	};
	this._showAsInvalid = function(item) {
		var prevColor = $(item).css("background-color") || "";
		$(item).animate({"opacity": "0.5", "background-color": "red"}, 300, function() {
			setTimeout(function() {
				$(item).animate({"opacity": "1", "background-color": prevColor}, 400);
			}, 10);
		});
	};
	this.getData = function() {
		var i, len = this.subviews.length, res = [];
		for (i = 0; i < len; i += 1) {
			res[res.length] = this.subviews[i].getData();
		}
		return res;
	};
	this.removeItem = function(item) {
		this._data = this.getData();
		var i, len, remPrimary = false;
		if (this._data.length > item.getIndex()) {
			if (this._data[item.getIndex()].primary == "true") {
				remPrimary = true;
			}
			this._data.splice(item.getIndex(), 1);
			if (remPrimary && this._data.length > 0) {
				this._data[0].primary = "true";
			}
			this.render(this._data);
			this.updateItemCategories();
		}
	};
	this.addNew = function() {
		var canAdd = this.canAddNew();
		if (canAdd !== true) {
			this._showAsInvalid(canAdd);
			return;
		}
		var tr = document.createElement("tr"), item, tbody = $(this.dom).find("table > tbody")[0];
		$(tbody).append(tr);
		item = new appdb.views.ApplicationCategoriesEditorItem({container: tr, parent: this});
		if (this.getData().length === 0) {
			item.render({id: "-1", primary: "true"}, this.subviews.length);
		} else {
			item.render({id: "-1", primary: "false"}, this.subviews.length);
		}
		this.subviews[this.subviews.length] = item;
		this._data[this._data.length] = item.getData();
		this.updateItemCategories();
	};
	this.getPrimaryItem = function() {
		var i, data = this.getData(), len = data.length;
		for (i = 0; i < len; i += 1) {
			if (data[i].primary == "true") {
				return i;
			}
		}
		return -1;
	};
	this.setupForm = function(frm) {
		if ($(frm).length == 0) {
			return false;
		}
		$("input[name^='categoryID']").remove();
		var i, len = this.subviews.length, item, inp, views = this.subviews.slice(0);
		var primaryIndex = this.getPrimaryItem();
		inp = document.createElement("input");
		$(inp).attr("name", "categoryID0").attr("type", "hidden").attr("value", views[primaryIndex].getData().id);
		$(frm).append(inp);
		views.splice(primaryIndex, 1);
		len = views.length;
		for (i = 0; i < len; i += 1) {
			item = views[i];
			inp = document.createElement("input");
			$(inp).attr("name", "categoryID" + (i + 1)).attr("type", "hidden").attr("value", item.getData().id);
			$(frm).append(inp);
		}
		$("input[name^='categoryID']").each(function(i, e) {
		});
		return true;
	};
	this._setupHeader = function() {
		var thead = document.createElement("thead"), tr, th1, th2, th3;
		tr = document.createElement("tr");
		th1 = document.createElement("th");
		th2 = document.createElement("th");
		th3 = document.createElement("th");
		$(tr).append(th1).append(th2).append(th3);
		$(thead).append(tr);

		$(th1).append("<span class='appcategoriesheader primary'>" + appdb.views.ApplicationCategoriesEditor.header.primary + "</span>");
		$(th2).append("<span class='appcategoriesheader category'>" + appdb.views.ApplicationCategoriesEditor.header.category + "</span>");
		$(th3).append("<span class='appcategoriesheader actions'></span>");
		return thead;
	};
	this._setupFooter = function() {
		var tfoot = document.createElement("tfoot"), tr = document.createElement("tr"), td = document.createElement("td");
		$(td).addClass("appcategories-footer").attr("colspan", "4").
				append("<span class='appcategories-empty'>" + appdb.views.ApplicationCategoriesEditor.footer.empty + "</span>").
				append("<span class='appcategories-limit' style='display:none'>" + appdb.views.ApplicationCategoriesEditor.footer.limit + "</span>").
				append("<span class='appcategories-add'></span>");
		$(tr).append(td);
		$(tfoot).append(tr);
		this._adder = new dijit.form.Button({
			label: "<i><b>add</b></i>",
			onClick: (function(_this) {
				return function() {
					_this.addNew();
					if (_this.subviews.length > 0) {
						$(".appcategories-empty").hide();
					} else {
						$(".appcategories-empty").show();
					}
					if (_this.subviews.length >= appdb.views.ApplicationCategoriesEditorItem.categoriesstore.data.items.length) {
						$(".appcategories-limit").show();
					} else {
						$(".appcategories-limit").hide();
					}
				};
			})(this)
		}, $(td).find("span.appcategories-add")[0]);
		return tfoot;
	};

	this.render = function(d, sortby) {
		this.render = this.onrender;
		if ($.isArray(d) === false) {
			d = [d];
		}
		this._initialData = d;
		this.render(d, sortby, true);
	};
	this.onrender = function(d, sortby, firsttime) {
		this.reset();
		var table = document.createElement("table"), tbody = document.createElement("tbody"),
				tr, i, len, item;
		$(table).addClass("appcategories-editor").attr("cellspacing", "0").attr("cellpadding", "0").append(this._setupHeader()).append(tbody);
		$(this.dom).append(table);

		this._data = d;
		len = this._data.length;
		for (i = 0; i < len; i += 1) {
			tr = document.createElement("tr");
			$(tbody).append(tr);
			item = new appdb.views.ApplicationCategoriesEditorItem({container: tr, parent: this});
			if (firsttime) {
				item.render(this._data[i], i, d);
			} else {
				item.render(this._data[i], i);
			}

			this.subviews[this.subviews.length] = item;
		}
		$(table).append(this._setupFooter());
		if (len > 0) {
			$(".appcategories-empty").hide();
		} else {
			$(".appcategories-empty").show();
		}
		if (this.subviews.length >= appdb.views.ApplicationCategoriesEditorItem.categoriesstore.data.items.length) {
			this._adder.attr('visible', false);
			$(".appcategories-limit").show();
		} else {
			this._adder.attr('visible', true);
			$(".appcategories-limit").hide();
		}
	};
	this.hasChanges = function() {
		//in case of item count change.
		if (this._initialData.length !== this._data.length) {
			return true;
		}
		//If a new item is added
		if (this.getItemById("") !== null) {
			return true;
		}
		var i, len = this._initialData.length;
		for (i = 0; i < len; i += 1) {
			var item = this.getItemById(this._initialData[i].id);
			//Item must be removed
			if (item === null) {
				return true;
			}
			var ditem = item.getData();
			if (typeof this._initialData[i].id === "undefined") {
				this._initialData[i].id = "";
			}
			if (ditem.id != this._initialData[i].id || ditem.primary != this._initialData[i].primary) {
				return true;
			}
		}
		return false;
	};
	this._preloadCategories = function() {
		var store = [], e = appdb.model.ApplicationCategories.getLocalData();
		if (e && typeof e.category !== "undefined") {
			for (var i = 0; i < e.category.length; i += 1) {
				store[store.length] = {id: e.category[i].id, name: e.category[i].val()};
			}
		}
		appdb.views.ApplicationCategoriesEditorItem.categoriesstore.data.items = store;
	};
	this._preloadCategories();
	this._setPrimary = function(index) {

	};

}, {
	header: {
		primary: "",
		category: "Category",
		primaryTooltip: "Set as primary category"
	},
	footer: {
		empty: "You must provide at least one category for the software",
		limit: "You have added the maximum number of items"
	}
});


appdb.views.SideBar = appdb.ExtendClass(appdb.View, "appdb.views.SideBar", function(o) {
	this.addItem = function(item) {
		var res = document.createElement("div"), title = document.createElement("div"),
				title_text = document.createElement("span"), title_collapse = document.createElement("div"),
				content = document.createElement("div");

		$(title).addClass("title");
		$(title_text).html(item.title || "No Title");
		$(title).append(title_text);
		if (item.canCollapse)
			$(title).append(title_collapse);
		$(res).append(title);

		$(content).addClass("content");
		if (item.css)
			$(content).addClass(item.css);
		if (item.style)
			$(content).attr("style", item.style);
		$(res).append(content);

		$(res).addClass("contentpanel");
		return res;
	};
	this.removeItem = function(index) {

	};
	this.render = function() {
		var i, ul = document.createElement("ul");
		for (i = 0; i < this.items.length; i += 1) {

			var li = document.createElement("li");
			$(li).append(this.addItem(this.items[i]));
			this.items[i].container = $(li).find(".content:last");
			$(ul).append(li);
		}
		$(this.dom).append(ul);
		for (i = 0; i < this.items.length; i += 1) {
			if (this.items[i].render) {
				this.items[i].render(this.items[i].container);
			} else if (this.items[i].html) {
				$(this.items[i].container).empty().append(this.items[i].html);
			}
		}
	};
	this._init = function() {
		this.items = o.items || [];
		this.items = ($.isArray(this.items) === false) ? [this.items] : this.items;

		//Check exiting html content declared inside the container.
		if ($(this.dom).children(".contentpanel").length > 0) {
			$(this.dom).children().each((function(_this) {
				return function(index, elem) {
					var res = {};
					if ($(elem).find("div.title").length > 0 && $(elem).find("div.content").length > 0) {
						if ($(elem).attr("style")) {
							res.style = $(elem).attr("style");
						}
						res.title = $(elem).find(".title:last").html();
						res.render = (function(html) {
							return function(container) {
								$(container).append(html);
							};
						})($(elem).find(".content:last").html());
						_this.items.splice(0, 0, res);
					}
				};
			})(this));
			$(this.dom).empty();
		}


	};
	this._init();
});


appdb.views.UserRequests = appdb.ExtendClass(appdb.View, "appdb.views.UserRequests", function(o) {
	this._renderKeyValue = function(k, v, seperator) {
		seperator = seperator || ":";
		var div = document.createElement("div"), key = document.createElement("div"),
				sep = document.createElement("div"), val = document.createElement("div"),
				li = document.createElement("li");

		$(key).addClass("key").append(k);
		$(val).addClass("value").append(v);
		$(sep).addClass("sep").append(seperator);

		$(div).addClass("keyvalue").append(key).append(sep).append(val);
		$(li).append(div);

		return li;
	};
	this._renderjoinapplication = function(d) {
		var div = document.createElement("div"), header = document.createElement("div"), main = document.createElement("div"), footer = document.createElement("div"),
				ul = document.createElement("ul"), img = document.createElement("img"), title = document.createElement("div"), imgtitle = document.createElement("img"),
				message = document.createElement("div"), actions = document.createElement("div"), accept = document.createElement("div"), reject = document.createElement("div"),
				user = document.createElement("div"), created = document.createElement("span"), country = document.createElement("img"), li, userclick = "appdb.views.Main.showPerson({id:" + d.user.id + ", cname:\"" + d.user.cname + "\"},{mainTitle : \"" + d.user.name + "\"});",
				status = document.createElement("div"), appclick = "appdb.views.Main.showApplication({id:" + d.application.id + ",cname:\"" + d.application.cname + "\"},{mainTitle : \"" + d.application.name + "\"});",
				msg = '', prevmsg = '', prevlink = document.createElement("a");
		var entityType = (($.trim(d.application.isvirtualappliance) === "true") ? "vappliance" : "software");
		var entityName = (($.trim(d.application.isvirtualappliance) === "true") ? "virtual appliance" : "software");
		var entityUrl = appdb.config.endpoint.base + "store/" + entityType + "/" + d.application.cname;
		var userUrl = appdb.config.endpoint.base + "store/person/" + d.user.cname;

		$(title).addClass("title").html("<span><b><a href='" + userUrl + "' onclick='" + userclick + "' title='View profile'>" + d.user.name + "</a></b> wants to join the " + entityName + " <b><a href='" + entityUrl + "' onclick='" + appclick + "' title='View " + entityName + " details'>" + d.application.val() + "</a></b></span>");
		$(imgtitle).addClass("titleimage").attr("src", "/images/sendrequest.png");

		$(header).addClass("header").append(imgtitle).append(title);
		if (d.created) {
			$(created).addClass("created").append(appdb.utils.formatDate(d.created));
			$(header).append(created);
		}

		$(img).attr("src", "/people/getimage?id=" + d.user.id);
		$(country).addClass("flag").attr("src", "/images/flags/" + d.user.country.isocode.toLowerCase() + ".png").attr("alt", "Country : " + d.user.country.val()).attr("title", d.user.country.val());

		$(footer).addClass("footer");
		$(div).addClass("joinapplicationrequest").append(header).append(main).append(footer);

		$(main).addClass("main").append(user);
		$(user).addClass("user").append(img).append(country).append(ul);

		$(ul).append(this._renderKeyValue("Institute", d.user.institution));

		if (d.user.message) {
			msg = appdb.utils.base64.decode(d.user.message);
			prevmsg = '' + msg;
			msg = msg.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, "<br><//br>");
			if (prevmsg.indexOf("\n") > -1 || prevmsg.length > 150) {
				prevmsg = prevmsg.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, " ").replace(/\t/g, " ");
				prevmsg = prevmsg.slice(0, ((prevmsg.length >= 150) ? 199 : prevmsg.length));
				prevmsg = $(document.createElement("div")).attr("id", "previewmessage" + d.id).addClass("previewmessage").append(prevmsg);
				msg = $(document.createElement("div")).attr("id", "fullmessage" + d.id).css("display", "none").addClass("fullmessage").append(msg);
				$(prevlink).addClass("messagedetailslink").attr("href", "").text("Message").click((function(id) {
					return function() {
						$("#fullmessage" + id).toggle();
						$("#previewmessage" + id).toggle();
						return false;
					};
				})(d.id));
				$(message).addClass("message").append(this._renderKeyValue(prevlink, $(document.createElement("span")).append(prevmsg).append(msg)));
			} else {
				$(message).addClass("message").append(this._renderKeyValue("Message", msg));
			}
		}

		$(status).addClass("status");
		$(accept).addClass("action").addClass("accept");
		$(reject).addClass("action").addClass("reject");
		$(actions).addClass("actions").append(status).append(accept).append(reject);

		new dijit.form.Button({
			label: "Accept",
			onClick: (function(_this, request) {
				return function() {
					_this.publish({event: "accept", value: request});
				};
			})(this, d)
		}, accept);

		new dijit.form.Button({
			label: "Reject",
			onClick: (function(_this, request) {
				return function() {
					_this.publish({event: "reject", value: request});
				};
			})(this, d)
		}, reject);

		var warning = $("<div class='warningpanel icontext'><img src='/images/vappliance/warning.png' alt=''/><span>If you accept, the user will be privileged to edit basic information and publication data of this item. You can modify these privileges by visiting the item's <b><a href='" + entityUrl + "/permissions' title='View permissions of this item in a new tab' target='_blank'>permission</a></b> section.</span></div>");

		$(main).append(message).append(warning);
		$(footer).append(actions);
		return div;
	};
	this._renderreleasemanager = function(d) {
		var div = document.createElement("div"), header = document.createElement("div"), main = document.createElement("div"), footer = document.createElement("div"),
				ul = document.createElement("ul"), img = document.createElement("img"), title = document.createElement("div"), imgtitle = document.createElement("img"),
				message = document.createElement("div"), actions = document.createElement("div"), accept = document.createElement("div"), reject = document.createElement("div"),
				user = document.createElement("div"), created = document.createElement("span"), country = document.createElement("img"), li, userclick = "appdb.views.Main.showPerson({id:" + d.user.id + ",cname:\"" + d.user.cname + "\"},{mainTitle : \"" + d.user.name + "\"});",
				status = document.createElement("div"), appclick = "appdb.views.Main.showApplication({id:" + d.application.id + ", cname:\"" + d.application.cname + "\"},{mainTitle : \"" + d.application.name + "\"});",
				msg = '', prevmsg = '', prevlink = document.createElement("a");
		var entityType = (($.trim(d.application.isvirtualappliance) === "true") ? "vappliance" : "software");
		var entityName = (($.trim(d.application.isvirtualappliance) === "true") ? "virtual appliance" : "software");
		var entityUrl = appdb.config.endpoint.base + "store/" + entityType + "/" + d.application.cname;
		var userUrl = appdb.config.endpoint.base + "store/person/" + d.user.cname;

		$(title).addClass("title").html("<span><b><a href='" + userUrl + "' onclick='" + userclick + "' title='View profile'>" + d.user.name + "</a></b> requests for permission to manage the releases of the " + entityName + " <b><a href='" + entityUrl + "' onclick='" + appclick + "' title='View " + entityName + " details'>" + d.application.val() + "</a></b></span>");
		$(imgtitle).addClass("titleimage").attr("src", "/images/sendrequest.png");

		$(header).addClass("header").append(imgtitle).append(title);
		if (d.created) {
			$(created).addClass("created").append(appdb.utils.formatDate(d.created));
			$(header).append(created);
		}

		$(img).attr("src", "/people/getimage?id=" + d.user.id);
		$(country).addClass("flag").attr("src", "/images/flags/" + d.user.country.isocode.toLowerCase() + ".png").attr("alt", "Country : " + d.user.country.val()).attr("title", d.user.country.val());

		$(footer).addClass("footer");
		$(div).addClass("joinapplicationrequest").append(header).append(main).append(footer);

		$(main).addClass("main").append(user);
		$(user).addClass("user").append(img).append(country).append(ul);

		$(ul).append(this._renderKeyValue("Institute", d.user.institution));

		if (d.user.message) {
			msg = appdb.utils.base64.decode(d.user.message);
			prevmsg = '' + msg;
			msg = msg.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, "<br><//br>");
			if (prevmsg.indexOf("\n") > -1 || prevmsg.length > 150) {
				prevmsg = prevmsg.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, " ").replace(/\t/g, " ");
				prevmsg = prevmsg.slice(0, ((prevmsg.length >= 150) ? 199 : prevmsg.length));
				prevmsg = $(document.createElement("div")).attr("id", "previewmessage" + d.id).addClass("previewmessage").append(prevmsg);
				msg = $(document.createElement("div")).attr("id", "fullmessage" + d.id).css("display", "none").addClass("fullmessage").append(msg);
				$(prevlink).addClass("messagedetailslink").attr("href", "").text("Message").click((function(id) {
					return function() {
						$("#fullmessage" + id).toggle();
						$("#previewmessage" + id).toggle();
						return false;
					};
				})(d.id));
				$(message).addClass("message").append(this._renderKeyValue(prevlink, $(document.createElement("span")).append(prevmsg).append(msg)));
			} else {
				$(message).addClass("message").append(this._renderKeyValue("Message", msg));
			}
		}

		$(status).addClass("status");
		$(accept).addClass("action").addClass("accept");
		$(reject).addClass("action").addClass("reject");
		$(actions).addClass("actions").append(status).append(accept).append(reject);

		new dijit.form.Button({
			label: "Accept",
			onClick: (function(_this, request) {
				return function() {
					_this.publish({event: "accept", value: request});
				};
			})(this, d)
		}, accept);

		new dijit.form.Button({
			label: "Reject",
			onClick: (function(_this, request) {
				return function() {
					_this.publish({event: "reject", value: request});
				};
			})(this, d)
		}, reject);


		$(main).append(message);
		$(footer).append(actions);
		return div;
	};
	this._renderaccessgroup = function(d) {
		var div = document.createElement("div"), header = document.createElement("div"), main = document.createElement("div"), footer = document.createElement("div"),
				ul = document.createElement("ul"), img = document.createElement("img"), title = document.createElement("div"), imgtitle = document.createElement("img"),
				message = document.createElement("div"), actions = document.createElement("div"), accept = document.createElement("div"), reject = document.createElement("div"),
				user = document.createElement("div"), created = document.createElement("span"), country = document.createElement("img"), li, userclick = "appdb.views.Main.showPerson({id:" + d.user.id + ",cname:\"" + d.user.cname + "\"},{mainTitle : \"" + d.user.name + "\"});",
				status = document.createElement("div");
		msg = '', prevmsg = '', prevlink = document.createElement("a");
		var userUrl = appdb.config.endpoint.base + "store/person/" + d.user.cname;
		var accessgroupname = "-";
		var accessgroups = $.grep(appdb.model.StaticList.AccessGroups, function(e) {
			return ($.trim(e.suid) === $.trim(d.targetguid));
		});
		if (accessgroups.length > 0) {
			accessgroupname = accessgroups[0].val();
		}
		$(title).addClass("title").html("<span><b><a href='" + userUrl + "' onclick='" + userclick + "' title='View profile'>" + d.user.name + "</a></b> requests to be included in access groups of <b>" + accessgroupname + "</b></span>");
		$(imgtitle).addClass("titleimage").attr("src", "/images/sendrequest.png");

		$(header).addClass("header").append(imgtitle).append(title);
		if (d.created) {
			$(created).addClass("created").append(appdb.utils.formatDate(d.created));
			$(header).append(created);
		}

		$(img).attr("src", "/people/getimage?id=" + d.user.id);
		$(country).addClass("flag").attr("src", "/images/flags/" + d.user.country.isocode.toLowerCase() + ".png").attr("alt", "Country : " + d.user.country.val()).attr("title", d.user.country.val());

		$(footer).addClass("footer");
		$(div).addClass("joinapplicationrequest").append(header).append(main).append(footer);

		$(main).addClass("main").append(user);
		$(user).addClass("user").append(img).append(country).append(ul);

		$(ul).append(this._renderKeyValue("Institute", d.user.institution));

		if (d.user.message) {
			msg = appdb.utils.base64.decode(d.user.message);
			prevmsg = '' + msg;
			msg = msg.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, "<br><//br>");
			if (prevmsg.indexOf("\n") > -1 || prevmsg.length > 150) {
				prevmsg = prevmsg.replace(/\</g, "&lt;").replace(/\>/g, "&gt;").replace(/\n/g, " ").replace(/\t/g, " ");
				prevmsg = prevmsg.slice(0, ((prevmsg.length >= 150) ? 199 : prevmsg.length));
				prevmsg = $(document.createElement("div")).attr("id", "previewmessage" + d.id).addClass("previewmessage").append(prevmsg);
				msg = $(document.createElement("div")).attr("id", "fullmessage" + d.id).css("display", "none").addClass("fullmessage").append(msg);
				$(prevlink).addClass("messagedetailslink").attr("href", "").text("Message").click((function(id) {
					return function() {
						$("#fullmessage" + id).toggle();
						$("#previewmessage" + id).toggle();
						return false;
					};
				})(d.id));
				$(message).addClass("message").append(this._renderKeyValue(prevlink, $(document.createElement("span")).append(prevmsg).append(msg)));
			} else {
				$(message).addClass("message").append(this._renderKeyValue("Message", msg));
			}
		}

		$(status).addClass("status");
		$(accept).addClass("action").addClass("accept");
		$(reject).addClass("action").addClass("reject");
		$(actions).addClass("actions").append(status).append(accept).append(reject);

		new dijit.form.Button({
			label: "Accept",
			onClick: (function(_this, request) {
				return function() {
					_this.publish({event: "accept", value: request});
				};
			})(this, d)
		}, accept);

		new dijit.form.Button({
			label: "Reject",
			onClick: (function(_this, request) {
				return function() {
					_this.publish({event: "reject", value: request});
				};
			})(this, d)
		}, reject);


		$(main).append(message);
		$(footer).append(actions);
		return div;
	};
	this._renderItem = function(d) {
		var div = document.createElement("div");
		$(div).addClass("item");

		if (this["_render" + d.type.val()]) {
			$(div).append(this["_render" + d.type.val()](d));
		}
		return div;
	};
	this.render = function(d) {
		var reqs = d || [], i, len, li;
		reqs = ($.isArray(reqs) ? reqs : [reqs]);
		len = reqs.length;
		for (i = 0; i < len; i += 1) {
			li = document.createElement("li");
			$(li).attr("id", "userrequest" + reqs[i].id).append(this._renderItem(reqs[i]));
			$(this.dom).append(li);
		}
	};
	this.removeItem = function(requestid) {
		var item = $(this.dom).find("li#userrequest" + requestid);
		setTimeout((function(_this) {
			return function() {
				$(item).animate({"opacity": "0.01", "height": "0"}, 500, function() {
					$(this).remove();
					_this.publish({event: "changed", value: {}});
				});
			};
		})(this), 100);

	};
	this.setStatus = function(id, status) {
		var st = appdb.views.UserRequests.Statuses[status] || {display: "", callback: function() {
				$(this.dom).find("#userrequest" + id + " .footer .actions .dijitButton").css({"visibility": "visible"});
			}};
		var stdom = $(this.dom).find("#userrequest" + id + " .footer .actions .status");
		st.callback.apply(this, [id]);
		if (stdom.length > 0) {
			$(stdom).empty().append(st.display);
		}
	};
	this.isEmpty = function() {
		if ($(".pendingrequests .main ul li").length === 0) {
			return true;
		}
		return false;
	};
	this._init = function() {

	};
	this._init();
}, {
	Statuses: {
		"accept": {display: "<img src='/images/ajax-loader-small.gif' alt=''/><span>Accepting user request...</span>", callback: function(id) {
				$(this.dom).find("li#userrequest" + id + " .footer .actions .dijitButton").css({"visibility": "hidden"});
			}},
		"reject": {display: "<img src='/images/ajax-loader-small.gif' alt=''/><span>Rejecting user request...</span>", callback: function(id) {
				$(this.dom).find("li#userrequest" + id + " .footer .actions .dijitButton").css({"visibility": "hidden"});
			}},
		"accepted": {display: "<img src='/images/yes.png' alt=''/><span>User request accepted!</span>", callback: function(id) {
				$(this.dom).find("li#userrequest" + id + " .footer .actions .dijitButton").css({"visibility": "hidden"});
				this.removeItem(id);
			}},
		"rejected": {display: "<img src='/images/yes.png' alt=''/><span>User request rejected!</span>", callback: function(id) {
				$(this.dom).find("li#userrequest" + id + " .footer .actions .dijitButton").css({"visibility": "hidden"});
				this.removeItem(id);
			}},
		"error": {display: "<img src='/images/cancelicon.png' alt=''/><span class='error'>Failed to execute request.</span>", callback: function(id) {
				$(this.dom).find("li#userrequest" + id + " .footer .actions .dijitButton").css({"visibility": "visible"});
			}}
	}
});

appdb.views.peopleGroupList = appdb.ExtendClass(appdb.View, "appdb.views.PeopleGroup", function(o) {
	this._renderItem = function(d) {
		var li = document.createElement("li"), img = document.createElement("img"),
				flag = document.createElement("img"), name = document.createElement("div"),
				institute = document.createElement("div"), a = document.createElement("a"), info = document.createElement("div");

		$(img).addClass("image").attr("src", "/people/getimage?id=" + d.id);
		$(flag).addClass("flag").attr("src", "/images/flags/" + d.countryiso.toLowerCase() + ".png");
		$(name).addClass("name").append(d.firstname + " " + d.lastname);
		$(info).addClass("info").append(name);
		$(a).addClass("itemcontents").append(img).append(flag).append(info);
		$(li).append(a);

		$(a).bind("click", (function(_this, data) {
			return function() {
				_this.publish({event: "select", value: {item: $(this).html(), data: data}});
			};
		})(this, d));
		return li;
	};
	this._renderGroup = function(d) {
		var i, len, li = document.createElement("li"), title = document.createElement("div"), ul = document.createElement("ul"), users = d.user;
		if ($.isArray(users) === false) {
			users = [users];
		}
		len = users.length;
		$(title).addClass("title").append("<span>" + d.name + "</span>");
		$(ul).addClass("items");
		$(li).addClass("groupitem").append(title).append(ul);
		for (i = 0; i < len; i += 1) {
			$(ul).append(this._renderItem(users[i]));
		}
		return li;
	};
	this.render = function(d) {
		d = d || [];
		$(this.dom).find("a").unbind("click");
		$(this.dom).empty();
		var glist = document.createElement("ul"), len = d.length, i;
		$(glist).addClass("grouplist");
		for (i = 0; i < len; i += 1) {
			if (d[i].user) {
				$(glist).append(this._renderGroup(d[i]));
			}
		}
		$(this.dom).empty().append(glist);
	};
	this._init = function() {

	};
	this._init();
});

appdb.views.DropDownGroup = appdb.ExtendClass(appdb.View, "appdb.views.DropDownGroup", function(o) {
	this.itemType = "people";
	this._list = "";
	this.selected = (function(_this) {
		var _item = "", _data = null;
		var reset = function() {
			_item = "";
			_data = null;
		};
		var getter = function() {
			return _data;
		};
		var setter = function(item, data) {
			_item = item;
			_data = data;
			_this._renderSelected(_item);
		};
		return {
			get: getter,
			set: setter,
			reset: reset
		};
	})(this);
	this._renderSelected = function(html) {
		if ($.trim(html) === '') {
			this.renderUnselected();
		} else {
			$(this.dom).find(".itemcontents").html(html);
		}

	};
	this.renderUnselected = function() {
		$(this.dom).find(".itemcontents").html("<span>" + this.unselected + "</span>");
	};
	this.render = function(d) {
		this.selected.reset();
		this.selected.set("", undefined);
		$(this.dom).find(".combo .selection:last").unbind("click");

		$(this.dom).find(".combo .selection:last").click((function(_this) {
			return function() {
				$(_this.dom).parent().toggleClass("active");
				if ($(_this.dom).parent().hasClass("active")) {
					$(_this.dom).find("input").focus();
				} else {
					$(_this.dom).find("input").blur();
				}
			};
		})(this));

		$(this.dom).find("input").unbind("blur").bind("blur", (function(_this) {
			return function() {
				$(_this.dom).parent().removeClass("active");
			};
		})(this));
		$("body").unbind("mouseup.dropdowngroup").bind("mouseup.dropdowngroup", (function(_this) {
			return function(e) {
				$(_this.dom).find("input").blur();
			};
		})(this));
		$(this.dom).parent().removeClass("active");

		this.subviews.group.clearObserver();
		this.subviews.group.subscribe({event: "select", callback: function(v) {
				this.selected.set(v.item, v.data);
				$(this.dom).parent().removeClass("active");
				this.publish({event: "select", value: {data: v.data}});
			}, caller: this});
		if (this.subviews.group) {
			this.subviews.group.render(d);
		}
	};
	this._initContainer = function() {
		$(this.dom).empty();
		var combo = document.createElement("div"), selection = document.createElement("a"),
				button = document.createElement("div"), img = document.createElement("div"),
				list = document.createElement("div"), content = document.createElement("div"),
				input = document.createElement("input");

		$(content).addClass("content").addClass("itemcontents").append("<span>" + this.unselected + "</span>");
		$(button).addClass("select").append($(img).addClass("arrow").append("&#9660;"));
		$(selection).addClass("selection").append(content).append(button);
		$(combo).addClass("combo").append(selection);

		$(list).addClass("list");
		this._list = list;

		$(this.dom).addClass("dropdowngroup").append($(input).attr("type", "hidden")).append(combo).after(list);
	};
	this._init = function() {
		this.unselected = o.unselected || appdb.views.DropDownGroup.Unselected;
		this._initContainer();
		this.itemType = o.itemType || this.itemType;
		this.itemType = this.itemType.toLowerCase() + "GroupList";
		if (appdb.views[this.itemType]) {
			this.subviews.group = new appdb.views[this.itemType]({container: this._list});
		} else {
			console.log("Cannot render group list '" + this.itemType + "'. No such view found.");
		}
		this.reset = (function(_reset) {
			return function() {
				$("body").unbind("mouseup.dropdowngroup");
				_reset();
			};
		})(this.reset);
	};
	this._init();
}, {
	Unselected: "Click to select a recipient..."
});

appdb.views.ApiNetFilterItem = appdb.ExtendClass(appdb.View, "appdb.views.ApiNetFilterItem", function(o) {
	this._data = "";
	this.getData = function() {
		return this._data;
	};
	this.annotate = function(enabled) {
		enabled = enabled || false;
		if (enabled === true) {
			$(this.dom).addClass("annotated");
		} else {
			$(this.dom).removeClass("annotated");
		}
	};
	this.render = function(d) {
		this._data = d.value;
		var div = document.createElement("div"), deletelink = document.createElement("a");
		$(div).addClass("apinetfilteritem");
		$(div).html("<div class='value' >" + this._data + "</div>").append(deletelink);
		$(deletelink).addClass("action").html(appdb.views.ApiNetFilterItem.actions.remove || "<span></span>").click((function(_this) {
			return function(i, el) {
				setTimeout(function() {
					_this.publish({"event": "delete", "value": _this._data});
				}, 0);

			};
		})(this)).hover(function() {
			$(this).parent().toggleClass("hover");
		});
		$(this.dom).empty().append(div);
	};
	appdb.views.ApiNetFilterItem.actions = appdb.views.ApiNetFilterItem.actions || {};
}, {
	actions: {
		remove: "<div class='delete'>remove</div>"
	}
});
appdb.views.ApiNetFilterRegister = appdb.ExtendClass(appdb.View, "appdb.views.ApiNetFilterRegister", function(o) {
	this.ipInput = null;
	this.currentValue = "";
	this.validate = function() {
		var i, ex = appdb.views.ApiNetFilterRegister.validations,
				val = $.trim(this.currentValue), res = undefined, err = "The value you provided is not valid";
		//Check format validity
		for (i in ex) {
			if (ex.hasOwnProperty(i)) {
				if (ex[i].test(val)) {
					res = i;
					break;
				}
			}
		}
		//Check if value is already used for this key
		if (this.parent) {
			var items = this.parent.collectData();
			for (i = 0; i < items.length; i += 1) {
				if ($.trim(items[i].value) === val) {
					var item = this.parent.getItem(val);
					if (item) {
						item.annotate(true);
					}
					res = undefined;
					err = "The value is already in use";
					break;
				} else {
					this.parent.clearItemAnnotations();
				}
			}
		}
		$(this.dom).find(".validation").empty();
		if (typeof res === "undefined" && val != "") {
			$(this.dom).find(".validation").append("<img src='/images/exclam16.png' border='0'/><span>" + err + "</a>");
		}
		return res;
	};
	this.reset = function() {
		if (this.ipInput !== null) {
			this.ipInput.destroyRecursive(false);
			this.ipInput = null;
		}
		$(this.dom).empty();
	};
	this.render = function() {
		var input = document.createElement("div"), add = document.createElement("a");
		$(add).addClass("action").addClass("disabled").append($(appdb.views.ApiNetFilterRegister.actions.add || "<span></span>"));
		$(add).click((function(_this) {
			return function() {
				if ($(this).hasClass("disabled") === false) {
					_this.publish({"event": "insert", "value": _this.currentValue});
				}
			};
		})(this));
		if (appdb.views.ApiNetFilterRegister.labels.description) {
			var description = document.createElement("div");
			$(description).addClass("description").append(appdb.views.ApiNetFilterRegister.labels.description);
			$(this.dom).append($(description));
		}
		$(this.dom).append(input).append(add);

		if (dijit.byId('NewNetFilterInput' + this.index)) {
			dijit.byId('NewNetFilterInput' + this.index).destroyRecursive(false);
		}
		this.ipInput = new dijit.form.TextBox({
			id: "NewNetFilterInput" + this.index,
			value: "",
			title: appdb.views.ApiNetFilterRegister.labels.inputWaterMask || "",
			onChange: (function(_this, addLink) {
				return function(v) {
					_this.currentValue = v;
					if (_this.validate()) {
						$(addLink).removeClass("disabled");
					} else {
						$(addLink).addClass("disabled");
					}
				};
			})(this, add),
			onKeyUp: (function(_this, addLink) {
				return function(e) {
					var v = _this.ipInput.attr("displayedValue"), q, k = (window.event) ? event.keyCode : e.keyCode;
					_this.currentValue = v;
					if (_this.validate()) {
						$(addLink).removeClass("disabled");
					} else {
						$(addLink).addClass("disabled");
					}
				};
			})(this, add)
		}, $(input)[0]);

		if (appdb.views.ApiNetFilterRegister.labels.examples) {
			$(this.dom).find(".viewexamples.hide").removeClass("hide");
			var tip = new dijit.TooltipDialog({
				content: appdb.views.ApiNetFilterRegister.labels.examples
			});

			$(this.dom).find("a.cidrexamples").click(function() {
				if (appdb.views.ApiNetFilterRegister.tips !== null) {
					dijit.popup.close(appdb.views.ApiNetFilterRegister.tips);
				}
				dijit.popup.open({
					popup: tip,
					around: $(this)[0]
				});
				appdb.views.ApiNetFilterRegister.tips = tip;
				return false;
			});
		}
		if (appdb.views.ApiNetFilterRegister.labels.validation) {
			$(this.dom).append(appdb.views.ApiNetFilterRegister.labels.validation);
		}
		if (appdb.views.ApiNetFiltersList.maxitems) {
			var maxfilters = document.createElement("div");
			$(maxfilters).addClass("maxfilters");
			if (appdb.views.ApiNetFiltersList.maxitems == 1) {
				$(maxfilters).append("<span><sup>*You can add <b>one</b> filter per API key</sup></span>");
			} else {
				$(maxfilters).append("<span><sup>*You can add up to <b>" + appdb.views.ApiNetFiltersList.maxitems + "</b> filters per API key</sup></span>");
			}
			$(this.dom).append(maxfilters);
		}

	};
	this._init = function() {
		this.index = (typeof o.index === "undefined") ? 0 : o.index;
		appdb.views.ApiNetFilterRegister.actions = appdb.views.ApiNetFilterRegister.actions || {};
		appdb.views.ApiNetFilterRegister.validations = appdb.views.ApiNetFilterRegister.validations || {};
		appdb.views.ApiNetFilterRegister.labels = appdb.views.ApiNetFilterRegister.labels || {};
	};
	this._init();
}, {
	validations: {
		domainName: /^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/i,
		ipv4: /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/i,
		ipv4CIDR: /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/(\d|[1-2]\d|3[0-2]))$/i,
		ipv6: /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*/i,
		ipv6CIDR: /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*(\/(\d|\d\d|1[0-1]\d|12[0-8]))$/i
	},
	actions: {
		add: "<div class='add'>add</div>"
	},
	labels: {
		inputWaterMask: "Please enter the netfilter value...",
		description: "<span>Although this is optional please consider providing a netfilter in order to restrict access to the current access token. Its value can be a simple IP address, a CIDR formatted address, a (sub)domain name, or a host name. <span class='viewexamples hide'>Click <a class='cidrexamples' href='#'>here</a> for examples.</span></span>",
		validation: "<div class='validation'></div>",
		examples: "<div class='netfilters examples'>The following are some valid netfilter values:<br/><span><a href='http://en.wikipedia.org/wiki/Domain_name' target='_blank'>Domain name</a>:</span>" +
				"<ul><li>mydomain.eu</li><li>subdomain.mydomain.eu</li><li>iasa.gr</li></ul>" +
				"<span><a href='http://en.wikipedia.org/wiki/Cidr' target='_blank'>CIDR</a>:</span>" +
				"<ul><li>195.134.89.0/24</li><li>195.251.54.0/25</li></ul>" +
				"<span><a href='http://en.wikipedia.org/wiki/IP_address' target='_blank'>IP Address</a>:</span>" +
				"<ul><li>195.134.89.11</li><li>195.251.54.93</li></ul>" +
				"</div>"
	},
	tips: null
});
appdb.views.ApiNetFiltersList = appdb.ExtendClass(appdb.View, "appdb.views.ApiNetFilters", function(o) {
	this._data = {"key": null, "netfilters": []};
	this.index = 0;
	this.netFilterAdd = null;
	this.getData = function() {
		return this._data.netfilters;
	};
	this.getKey = function() {
		return this._data.key;
	};
	this.collectData = function() {
		return this._data.netfilters || [];
	};
	this.clearItemAnnotations = function() {
		var i, len = this.subviews.length;
		for (i = 0; i < len; i += 1) {
			if (this.subviews[i].annotate) {
				this.subviews[i].annotate(false);
			}
		}
	};
	this.getItem = function(v) {
		var i, len = this.subviews.length;
		for (i = 0; i < len; i += 1) {
			if (this.subviews[i].annotate) {
				if ($.trim(this.subviews[i].getData()) === $.trim(v)) {
					return this.subviews[i];
				}
			}
		}
		return null;
	};
	this.render = function(d) {
		d = d || [];
		d = $.isArray(d) ? d : [d];
		this._data.netfilters = d || [];
		var i, len = d.length, title = document.createElement("div"),
				ul = document.createElement("ul"), adder = document.createElement("div");

		if (appdb.views.ApiNetFiltersList.labels.title) {
			$(title).addClass("title").html(appdb.views.ApiNetFiltersList.labels.title || "<span></span>");
		}
		$(this.dom).append($(title));
		if (len > 0) {
			$(ul).addClass("apinetfilterlist");
			for (i = 0; i < len; i += 1) {
				var li = document.createElement("li");
				$(ul).append(li);
				this.subviews.push(new appdb.views.ApiNetFilterItem({container: $(li)}));
				this.subviews[i].subscribe({"event": "delete", "callback": function(d) {
						this.publish({"event": "delete", "value": d});
					}, "caller": this});
				this.subviews[i].render(d[i]);
			}
			$(this.dom).append($(ul));
		} else {
			$(title).append($(appdb.views.ApiNetFiltersList.labels.empty || "<span></span>"));
		}
		if (appdb.views.ApiNetFiltersList.maxitems && appdb.views.ApiNetFiltersList.maxitems > len) {
			$(adder).addClass("netfilteradd");
			$(this.dom).append($(adder));
			if (this.netFilterAdd !== null) {
				this.netFilerAdd.unsubscribeAll(this);
				this.netFilerAdd.destroy();
			}
			this.netFilterAdd = new appdb.views.ApiNetFilterRegister({container: $(adder), index: this.index, parent: this});
			this.subviews.push(this.netFilterAdd);
			this.netFilterAdd.subscribe({
				"event": "insert",
				"callback": function(v) {
					this.publish({"event": "insert", "value": v});
				},
				"caller": this
			});
			this.netFilterAdd.render();
		} else if (appdb.views.ApiNetFiltersList.labels.maximum && appdb.views.ApiNetFiltersList.labels.maximum != "1") {
			$(this.dom).append($(appdb.views.ApiNetFiltersList.labels.maximum || "<span></span>"));
		}
	};
	this._init = function() {
		appdb.views.ApiNetFiltersList.labels = appdb.views.ApiNetFiltersList.labels || {};
		appdb.views.ApiNetFiltersList.actions = appdb.views.ApiNetFiltersList.actions || {};
		this.index = (typeof o.index === "undefined") ? 0 : o.index;
	};
	this._init();
}, {
	labels: {
		title: "<div class='label'>Netfilter" + ((appdb.config.api.maxnetfilters > 1) ? "s" : "") + ":</div>",
		empty: "<div class='empty'>There " + ((appdb.config.api.maxnetfilters > 1) ? "are" : "is") + " no netfilter" + ((appdb.config.api.maxnetfilters > 1) ? "s" : "") + " associated with this API key.</div>",
		maximum: "<div class='explanation'>The maximum number of netfilters is used for the specified API key.</div>"
	},
	actions: {
		add: "<div class='action add'>add new</div>"
	},
	maxitems: (appdb.config.api.maxnetfilters || "1")
});
appdb.views.ApiKeySystemUser = appdb.ExtendClass(appdb.View, "appdb.views.ApiKeySystemUser", function(o) {
	this.renderCurrentSystemUser = function(d) {
		var div = document.createElement("div"), username = document.createElement("div"), usernamelabel = document.createElement("div"),
				usernamevalue = document.createElement("div"), displayname = document.createElement("div"),
				displaynamelabel = document.createElement("div"), displaynamevalue = document.createElement("div"),
				actions = document.createElement("div"), changepassword = document.createElement("a"), changename = document.createElement("a"),
				requestpermissions = document.createElement("a"), actioncontainer = document.createElement("div");


		$(usernamelabel).addClass("label").append(appdb.views.ApiKeySystemUser.labels.username);
		$(usernamevalue).addClass("value").append(d.sysusername);
		$(username).addClass("username").append(usernamelabel).append(usernamevalue);

		$(displaynamelabel).addClass("label").append(appdb.views.ApiKeySystemUser.labels.displayname);
		$(displaynamevalue).addClass("value").append(d.sysdisplayname);
		$(displayname).addClass("displayname").append(displaynamelabel).append(displaynamevalue);

		$(changepassword).addClass("action").append(appdb.views.ApiKeySystemUser.actions.changepassword);
		$(changename).addClass("action").append(appdb.views.ApiKeySystemUser.actions.changename);
		$(requestpermissions).addClass("action").append(appdb.views.ApiKeySystemUser.actions.requestpermissions);
		$(actions).addClass("actions").append(changepassword).append(changename).append(requestpermissions);
		$(actioncontainer).addClass("actioncontainer").addClass("hidden");

		$(div).addClass("systemuser").append(username).append(displayname).append(actions).append(actioncontainer);
		$(this.dom).append(div);

		$(changepassword).click((function(_this) {
			return function() {
				_this.changePassword(d);
			};
		})(this));

		$(changename).click((function(_this) {
			return function() {
				_this.changeName(d);
			};
		})(this));

		$(requestpermissions).click((function(_this) {
			return function() {
				_this.requestPermissions(d);
			};
		})(this));

		this.show();
	};
	this.changeName = function(d) {
		var div = document.createElement("div"), name = document.createElement("div"),
				namelabel = document.createElement("div"), nameinput = document.createElement("input"),
				update = document.createElement("a"), cancel = document.createElement("a"),
				actions = document.createElement("div"), title = document.createElement("div"),
				description = document.createElement("div");

		var dialog = $(this.dom).find(".actioncontainer");

		$(title).addClass("title").append("<span>Change system user display name:</span>");
		$(description).addClass("description").append(appdb.views.ApiKeySystemUser.labels.changenamedescription || "<span></span>");
		$(namelabel).addClass("label").append("<span>New display name:</span>");
		$(nameinput).attr("type", "text").val(d.sysdisplayname);
		$(name).addClass("displayname").append(namelabel).append(nameinput);
		$(update).addClass("action").addClass("disabled").append("<span>Update name</span>");
		$(cancel).addClass("action").append("<span>Cancel</span>");
		$(actions).addClass("actions").append(update).append(cancel);

		$(div).addClass("changename").append(title).append(description).append(name).append(actions);

		if (this.newdisplayname) {
			this.newdisplayname.destroyRecursive(false);
			this.newdisplayname = null;
		}
		this.newdisplayname = dijit.form.TextBox({
			onKeyUp: (function(_this) {
				return function() {
					$(update).addClass("disabled");
					if (!_this.newdisplayname) {
						return;
					}
					var v = _this.newdisplayname.attr("displayedValue");
					if ($.trim(v) !== "" && v !== d.sysdisplayname) {
						$(update).removeClass("disabled");
					}
				};
			})(this)
		}, $(nameinput)[0]);

		$(update).click((function(_this) {
			return function() {
				var v = _this.newdisplayname.attr("displayedValue");
				if ($.trim(v) === "" || v == d.sysdisplayname) {
					return false;
				}
				_this.publish({
					"event": "changename",
					"value": {"keyid": d.id, "sysdisplayname": _this.newdisplayname.attr("displayedValue")}
				});
				return false;
			};
		})(this));

		$(cancel).click((function(_this) {
			return function() {
				$(_this.dom).find(".systemuser > .actions").show();
				$(dialog).empty().addClass("hidden");
			};
		})(this));
		$(dialog).empty().append(div);
		$(this.dom).find(".systemuser > .actions").hide();

		this.newdisplayname.set("value", d.sysdisplayname);
		$(dialog).removeClass("hidden");

	};
	this.changePassword = function(d) {
		var div = document.createElement("div"),
				oldp = document.createElement("div"), newp = document.createElement("div"),
				confirmp = document.createElement("div"), actions = document.createElement("div"),
				oldlabel = document.createElement("div"), oldinput = document.createElement("div"),
				newlabel = document.createElement("div"), newinput = document.createElement("div"),
				confirmlabel = document.createElement("div"), confirminput = document.createElement("div"),
				update = document.createElement("a"), cancel = document.createElement("a"), title = document.createElement("div"),
				description = document.createElement("div");

		var dialog = $(this.dom).find(".actioncontainer");

		$(title).addClass("title").append("<span>Change password:</span>");
		$(description).addClass("description").append(appdb.views.ApiKeySystemUser.labels.changepassworddescription || "<span></span>");
		$(oldlabel).addClass("label").append("<span>Old password:</span>");
		$(newlabel).addClass("label").append("<span>New password:</span>");
		$(confirmlabel).addClass("label").append("<span>Confirm new password:</span>");

		$(oldinput).attr("type", "password").val();
		$(newinput).attr("type", "password").val();
		$(confirminput).attr("type", "password").val();

		$(oldp).addClass("field").append(oldlabel).append(oldinput);
		$(newp).addClass("field").append(newlabel).append(newinput);
		$(confirmp).addClass("field").append(confirmlabel).append(confirminput);

		$(update).addClass("action").addClass("disabled").append("<span>Update password</span>");
		$(cancel).addClass("action").append("<span>Cancel</span>");
		$(actions).addClass("actions").append(update).append(cancel);

		$(div).addClass("changepassword").append(title).append(description).append(oldp).append(newp).append(confirmp).append(name).append(actions);

		this.oldpass = dijit.form.ValidationTextBox({
			required: true,
			type: "password",
			placeHolder: "type the old password",
			emptyMessage: "value is required",
			onKeyUp: (function(_this) {
				return function() {
					_this.newpass.validate();
					_this.confirmpass.validate();
					if ($.trim(_this.oldpass.attr("value")) === "") {
						$(update).addClass("disabled");
					}
				};
			})(this)
		}, $(oldinput)[0]);

		this.newpass = dijit.form.ValidationTextBox({
			required: true,
			type: "password",
			placeHolder: "type the password",
			emptyMessage: "value is required",
			invalidMessage: "The two passwords do not match",
			validator: (function(_this) {
				return function() {
					$(update).addClass("disabled");
					if (!_this.confirmpass || !_this.newpass) {
						return false;
					}
					var v2 = _this.confirmpass.attr("value");
					var v1 = _this.newpass.attr("value");
					var reg = /^\w{6,}$/;
					if (v1.indexOf(" ") > -1) {
						_this.newpass.attr("invalidMessage", "No spaces are allowed");
						return false;
					} else if (!reg.test(v1)) {
						_this.newpass.attr("invalidMessage", "Password should be at least 6 characters long");
						return false;
					}

					if (v1 != v2) {
						_this.newpass.attr("invalidMessage", "The two passwords do not match");
						return false;
					}
					if ($.trim(_this.oldpass.attr("value")) !== "") {
						$(update).removeClass("disabled");
					}
					return true;
				};
			})(this),
			onKeyUp: (function(_this) {
				return function() {
					_this.confirmpass.validate();
					_this.newpass.validate();
				};
			})(this)
		}, $(newinput)[0]);

		this.confirmpass = dijit.form.ValidationTextBox({
			required: true,
			type: "password",
			placeHolder: "retype the password",
			invalidMessage: "The two passwords do not match",
			emptyMessage: "value is required",
			validator: (function(_this) {
				return function() {
					$(update).addClass("disabled");
					if (!_this.confirmpass || !_this.newpass) {
						return false;
					}
					var v2 = _this.confirmpass.attr("value");
					var v1 = _this.newpass.attr("value");
					var reg = /^\w{6,}$/;
					if (v2.indexOf(" ") > -1) {
						_this.confirmpass.attr("invalidMessage", "No spaces are allowed");
						return false;
					} else if (!reg.test(v2)) {
						_this.confirmpass.attr("invalidMessage", "Password should be at least 6 characters long");
						return false;
					}

					if (v1 != v2) {
						_this.confirmpass.attr("invalidMessage", "The two passwords do not match");
						return false;
					}
					if ($.trim(_this.oldpass.attr("value")) !== "") {
						$(update).removeClass("disabled");
					}
					return true;
				};
			})(this),
			onKeyUp: (function(_this) {
				return function() {
					_this.confirmpass.validate();
					_this.newpass.validate();
				};
			})(this)
		}, $(confirminput)[0]);

		$(update).click((function(_this) {
			return function() {
				_this.newpass.validate();
				_this.confirmpass.validate();
				if ($.trim(_this.oldpass.attr("value")) === "") {
					$(update).addClass("disabled");
				}
				if ($(update).hasClass("disabled")) {
					return false;
				}
				_this.publish({
					"event": "changepassword",
					"value": {"keyid": d.id, "old": _this.oldpass.attr("value"), "new": _this.newpass.attr("value")}
				});
				return false;
			};
		})(this));

		$(cancel).click((function(_this) {
			return function() {
				$(_this.dom).find(".systemuser > .actions").show();
				$(dialog).empty().addClass("hidden");
			};
		})(this));

		$(dialog).empty().append(div);
		$(this.dom).find(".systemuser > .actions").hide();
		$(update).addClass("disabled");

		$(dialog).removeClass("hidden");
	};
	this.requestPermissions = function(d) {
		var div = document.createElement("div"), description = document.createElement("div"),
				textcontainer = document.createElement("div"), text = document.createElement("textarea"),
				actions = document.createElement("div"), send = document.createElement("a"),
				cancel = document.createElement("a"), title = document.createElement("div");

		var dialog = $(this.dom).find(".actioncontainer");

		$(title).addClass("title").append("<span>Request permissions for system user:</span>");
		$(description).addClass("description").append($(appdb.views.ApiKeySystemUser.labels.requestpermissions));
		$(send).addClass("action").addClass("disabled").append("<span>Send request</span>");
		$(cancel).addClass("action").append("<span>Cancel</span>");
		$(actions).addClass("actions").append(send).append(cancel);
		$(textcontainer).addClass("textcontainer").append(text);

		$(div).addClass("requestpermissions").append(title).append(description).append(textcontainer).append(actions);
		$(dialog).empty().append(div);

		$(text).keyup((function(_this) {
			return function() {
				if ($.trim($(this).val()) === "") {
					$(send).addClass("disabled");
				} else {
					$(send).removeClass("disabled");
				}
			};
		})(this));
		$(send).click((function(_this) {
			return function() {
				var v = $(text).val();
				if ($.trim(v) === "") {
					return false;
				}
				_this.publish({
					"event": "requestpermisions",
					"value": {"keyid": d.id, "msg": appdb.utils.base64.encode(v)}
				});
				return false;
			};
		})(this));

		$(cancel).click((function(_this) {
			return function() {
				$(_this.dom).find(".systemuser > .actions").show();
				$(dialog).empty().addClass("hidden");
			};
		})(this));

		$(this.dom).find(".systemuser > .actions").hide();
		$(dialog).removeClass("hidden");
	};

	this.renderNewSystemUser = function(d) {
		var div = document.createElement("div"), description = document.createElement("div"),
				newform = document.createElement("div"), createnewuser = document.createElement("div"),
				namelabel = document.createElement("div"), name = document.createElement("div"),
				newuseraction = document.createElement("a"), passwd1label = document.createElement("div"),
				passwd2label = document.createElement("div"), passwd1 = document.createElement("div"), passwd2 = document.createElement("div");

		$(newform).addClass("newform");
		$(namelabel).addClass("namelabel").append(appdb.views.ApiKeySystemUser.labels.displayname);
		$(passwd1label).addClass("passwdlabel").append(appdb.views.ApiKeySystemUser.labels.passwd1);
		$(passwd2label).addClass("passwdlabel").append(appdb.views.ApiKeySystemUser.labels.passwd2);
		$(name).addClass("displayname").append("<div></div>");
		$(passwd1).addClass("apikeypassword").append("<div></div>");
		$(passwd2).addClass("apikeypassword").append("<div></div>");
		$(newform).append(namelabel).append(name).append(passwd1label).append(passwd1).append(passwd2label).append(passwd2).append(createnewuser);

		$(description).addClass("description").append(appdb.views.ApiKeySystemUser.labels.description);
		$(newuseraction).addClass("action").append(appdb.views.ApiKeySystemUser.actions.createnewuser);

		$(createnewuser).addClass("createnewuser").append(newuseraction);

		$(div).addClass("newsystemuser").append(description).append(newform);

		$(this.dom).append(div);
		this.displayname = dijit.form.ValidationTextBox({
			required: "true",
			placeHolder: "type the display name of the system user",
			emptyMessage: "value is required",
			onKeyUp: (function(_this) {
				return function() {
					_this.passwd1.validate();
					_this.passwd2.validate();
				};
			})(this)
		}, $(name).find("div:last")[0]);

		this.passwd1 = dijit.form.ValidationTextBox({
			required: true,
			type: "password",
			placeHolder: "type the password",
			emptyMessage: "value is required",
			invalidMessage: "The two passwords do not match",
			validator: (function(_this) {
				return function() {
					$(_this.dom).find(".createnewuser").addClass("disabled");
					if (!_this.passwd2 || !_this.passwd1) {
						return false;
					}
					var v2 = _this.passwd2.attr("value");
					var v1 = _this.passwd1.attr("value");
					var reg = /^\w{6,}$/;
					if (v1.indexOf(" ") > -1) {
						_this.passwd1.attr("invalidMessage", "No spaces are allowed");
						return false;
					} else if (!reg.test(v1)) {
						_this.passwd1.attr("invalidMessage", "Password should be at least 6 characters long");
						return false;
					}

					if (v1 != v2) {
						_this.passwd1.attr("invalidMessage", "The two passwords do not match");
						return false;
					}
					var v = _this.displayname.attr("value");
					if ($.trim(v) !== "") {
						$(_this.dom).find(".createnewuser").removeClass("disabled");
					}
					return true;
				};
			})(this),
			onKeyUp: (function(_this) {
				return function() {
					_this.passwd1.validate();
					_this.passwd2.validate();
				};
			})(this)
		}, $(passwd1).find("div:last")[0]);

		this.passwd2 = dijit.form.ValidationTextBox({
			required: true,
			type: "password",
			placeHolder: "retype the password",
			invalidMessage: "The two passwords do not match",
			emptyMessage: "value is required",
			validator: (function(_this) {
				return function() {
					$(_this.dom).find(".createnewuser").addClass("disabled");
					if (!_this.passwd2 || !_this.passwd1) {
						return false;
					}
					var v2 = _this.passwd2.attr("value");
					var v1 = _this.passwd1.attr("value");
					var reg = /^\w{6,}$/;
					if (v2.indexOf(" ") > -1) {
						_this.passwd2.attr("invalidMessage", "No spaces are allowed");
						return false;
					} else if (!reg.test(v2)) {
						_this.passwd2.attr("invalidMessage", "Password should be at least 6 characters long");
						return false;
					}

					if (v1 != v2) {
						_this.passwd2.attr("invalidMessage", "The two passwords do not match");
						return false;
					}
					var v = _this.displayname.attr("value");
					if ($.trim(v) !== "") {
						$(_this.dom).find(".createnewuser").removeClass("disabled");
					}

					return true;
				};
			})(this),
			onKeyUp: (function(_this) {
				return function() {
					_this.passwd1.validate();
					_this.passwd2.validate();
				};
			})(this)
		}, $(passwd2).find("div:last")[0]);

		$(newuseraction).click((function(_this) {
			return function() {
				if ($(_this.dom).find(".createnewuser").hasClass("disabled")) {
					//Cannot send the request
					return false;
				} else {
					_this.publish({
						"event": "insert",
						"value": {"pwd": _this.passwd2.attr("value"), "key": _this.apikey, "name": _this.displayname.attr("value")}
					});
				}
				return false;
			};
		})(this));

	};
	this.show = function() {
		$(this.dom).find(".newsystemuser").addClass("selected");
	};
	this.hide = function() {
		$(this.dom).find(".newsystemuser").removeClass("selected");
	};
	this.render = function(d) {
		if (d.authmethods == "2") {
			this.renderCurrentSystemUser(d);
		} else {
			this.renderNewSystemUser(d);
		}
	};
	this._initContainer = function() {
		var div = document.createElement("div");
		$(div).addClass();
	};
	this._init = function() {
		this.apikey = o.key || "";
		this._initContainer();
	};
	this._init();
}, {
	labels: {
		description: "<span>To activate this feature, a system user account has to be created by clicking on the button bellow.<br /><span><b>NOTE:</b> Once this feature is activated, it will not be possible to change the authentication method for the current API key, as it will henceforth only be usable with this system account. If you do not wish to proceed, please select instead the <i>EGI SSO</i> option above.</span></span>",
		passwd1: "<span>Enter password:</span>",
		passwd2: "<span>Confirm password:</span>",
		displayname: "<span>Display name:</span>",
		username: "<span>User name:</span>",
		requestpermissions: "<span>If you wish the system user of this API key be granted more permissions, (e.g. to have write access to specific software or to software belonging to a certain country etc.), then you can send us a message describing the type of permissions you need, and we will reply to your request as soon as possible:</span>",
		changepassworddescription: "<span>Please fill the values bellow and click '<i>Update Password</i>' to change the password of the current system user.</span>",
		changenamedescription: "<span>Set a new display name for the system user by filling the field bellow and clicking the button <i>Update name</i>.</span>"
	},
	actions: {
		createnewuser: "<span class='createnewuser'>Create system user</span>",
		changepassword: "<span class='changepassword'>Change password</span>",
		changename: "<span class='changename'>Change name</span>",
		requestpermissions: "<span class='requestpermissions'>Request permissions</span>"
	}
});
appdb.views.ApiKeyAuthentication = appdb.ExtendClass(appdb.View, "appdb.views.ApiKeyAuthentication", function(o) {
	this.renderSystemAuthentication = function(d) {
		var dom = $(this.dom).find(".apikeyauthentication");
		var span = document.createElement("span"), systemlabel = document.createElement("span"),
				choises = document.createElement("div"), choise1 = document.createElement("div"), system = document.createElement("div"),
				choise2 = document.createElement("div"), choise3 = document.createElement("div"), changepassword = document.createElement("div"),
				changename = document.createElement("div"), reqpermissions = document.createElement("div");

		$(choises).addClass("choises");
		$(choise1).addClass("choise").addClass("selected");

		$(systemlabel).addClass("label").append(appdb.views.ApiKeyAuthentication.labels.system);
		$(choise1).append(systemlabel);
		$(choises).append(choise1);
		$(system).addClass("systemusercontainer");
		$(dom).append(span).append(choises).append(system);
		$(dom).addClass("systemauthentication");

		this.subviews.push(new appdb.views.ApiKeySystemUser({container: $(system)[0], key: d.id}));
		this.subviews[0].subscribe({
			"event": "changepassword",
			"callback": function(d) {
				this.publish({
					"event": "changepassword",
					"value": d
				});
			},
			"caller": this
		}).subscribe({
			"event": "changename",
			"callback": function(d) {
				this.publish({
					"event": "changename",
					"value": d
				});
			},
			"caller": this
		}).subscribe({
			"event": "requestpermisions",
			"callback": function(d) {
				this.publish({
					"event": "requestpermisions",
					"value": d
				});
			},
			"caller": this
		});
		this.subviews[0].render(d);
	};
	this.renderSSOAuthentication = function(d) {
		var dom = $(this.dom).find(".apikeyauthentication");
		var span = document.createElement("span"), sso = document.createElement("input"), system = document.createElement("input"),
				ssolabel = document.createElement("span"), systemlabel = document.createElement("span"), ssohelp = document.createElement("a"),
				systemhelp = document.createElement("a"), choises = document.createElement("div"), choise1 = document.createElement("div"),
				choise2 = document.createElement("div"), newsystem = document.createElement("div"), newusertitle = document.createElement("div"),
				ssolink = document.createElement("a"), systemlink = document.createElement("a");

		//Initial view
		$(choises).addClass("choises");
		$(choise1).addClass("choise");
		$(choise2).addClass("choise");

		$(span).addClass("title").text(appdb.views.ApiKeyAuthentication.labels.title);
		$(ssolink).addClass("actionlink").append(appdb.views.ApiKeyAuthentication.labels.sso);
		$(ssolabel).addClass("label").append(ssolink);
		$(ssohelp).addClass("help");
		$(systemlink).addClass("actionlink").append(appdb.views.ApiKeyAuthentication.labels.system);
		$(systemlabel).addClass("label").append(systemlink);
		$(systemhelp).addClass("help");

		$(sso).attr("type", "radio").attr("name", "authmethod" + this.apikey).attr("checked", "checked").val("sso");
		$(system).attr("type", "radio").attr("name", "authmethod" + this.apikey).val("sys");

		$(choise1).append(sso).append($(ssolabel).append($(ssohelp)));
		$(choise2).append(system).append($(systemlabel).append($(systemhelp)));
		$(choises).append(choise1).append(choise2);

		//New system aythentication view
		$(newsystem).addClass("systemusercontainer");

		$(sso).change((function(_this, data, sys) {
			return function() {
				var v = $('input[name=authmethod' + _this.apikey + ']:checked', $(_this.dom)).val();
				if (v === "sys") {
					_this.subviews[0].show();
				} else {
					_this.subviews[0].hide();
					$(sys).parent().removeClass("selected");
				}
			};
		})(this, d, system));

		$(system).change((function(_this, data, sys) {
			return function() {
				var v = $('input[name=authmethod' + _this.apikey + ']:checked', $(_this.dom)).val();
				if (v === "sys") {
					$(sys).parent().addClass("selected");
					_this.subviews[0].show();
				} else {
					$(sys).parent().removeClass("selected");
					_this.subviews[0].hide();
				}
			};
		})(this, d, system));

		$(ssolink).click(function() {
			$(sso).attr("checked", "checked");
			$(sso).change();
		});

		$(systemlink).click(function() {
			$(system).attr("checked", "checked");
			$(system).change();
		});

		$(dom).append(span).append(choises).append(newsystem);

		this.subviews.push(new appdb.views.ApiKeySystemUser({container: $(newsystem)[0], key: d.id}));
		this.subviews[0].subscribe({
			"event": "insert",
			"callback": function(d) {
				this.publish({
					"event": "insertuser",
					"value": d
				});
			},
			"caller": this
		});
		this.subviews[0].render(d);
		this.subviews[0].hide();

		//rednering help tips
		if (appdb.views.ApiKeyAuthentication.help) {
			if (appdb.views.ApiKeyAuthentication.help.external) {
				var systemtip = new dijit.TooltipDialog({
					content: appdb.views.ApiKeyAuthentication.help.external
				});
				$(systemhelp).append("<img src='/images/question_mark.gif' border='0' />").click((function(_this) {
					return function() {
						if (appdb.views.ApiNetFilterRegister.helptip) {
							dijit.popup.close(appdb.views.ApiNetFilterRegister.helptip);
						}
						appdb.views.ApiNetFilterRegister.helptip = systemtip;
						setTimeout((function(_this) {
							return function() {
								dijit.popup.open({
									popup: systemtip, around: $(_this)[0]
								});
							};
						})(this), 0);
					};
				})(this));
			}
			if (appdb.views.ApiKeyAuthentication.help.sso) {
				var ssotip = new dijit.TooltipDialog({
					content: appdb.views.ApiKeyAuthentication.help.sso
				});
				$(ssohelp).append("<img src='/images/question_mark.gif' border='0' />").click((function(_this) {
					return function() {
						if (appdb.views.ApiNetFilterRegister.helptip) {
							dijit.popup.close(appdb.views.ApiNetFilterRegister.helptip);
						}
						appdb.views.ApiNetFilterRegister.helptip = ssotip;
						setTimeout((function(_this) {
							return function() {
								dijit.popup.open({
									popup: ssotip, around: $(_this)[0]
								});
							};
						})(this), 0);
					};
				})(this));
			}
		}
	};
	this.render = function(d) {
		var e = appdb.views.ApiKeyAuthentication.APIKeyAuthMethods, auth = d.authmethods;
		if (e.has(auth, e.E_SSO) && e.has(auth, e.E_SYSTEM)) {
			//TODO: when mixed authorization methods are implemented
			if (appdb.config.appenv !== "production")
				console.log('TODO: when mixed authorization methods are implemented');
		} else if (e.has(auth, e.E_SSO)) {
			this.renderSSOAuthentication(d);
		} else if (e.has(auth, e.E_SYSTEM)) {
			this.renderSystemAuthentication(d);
		}

	};
	this._initContainer = function() {
		var div = document.createElement("div");
		$(div).addClass("apikeyauthentication");
		$(this.dom).append(div);
	};
	this._init = function() {
		this.apikey = o.apikey || "";
		this.apikeyid = o.apikeyid || "";
		appdb.views.ApiKeyAuthentication.labels = appdb.views.ApiKeyAuthentication.labels || {};
		appdb.views.ApiKeyAuthentication.actions = appdb.views.ApiKeyAuthentication.actions || {};

		this._initContainer();
	};

	this._init();
}, {
	labels: {
		title: "API Authentication Method:",
		sso: "<span>EGI SSO</span>",
		system: "<span>Local Authentication</span>",
		newuser: "<span>Create system user:</span>"
	},
	help: {
		sso: "<span class='authhelp'>Select this option if you plan to forward EGI SSO user credentials in order to authenticate API calls. Users who do not own an EGI SSO account, and who are not willing to create one, will not be able to take advantage of authoritative API calls from within your application.</span>",
		external: "<span class='authhelp'>Select this option if you would like to use your own/local identity repository for authenticating your users. In such a case, a system account should be created, which you may use as a catch-all authenticator to act on behalf of your users for authoritative API calls.</span>"
	},
	APIKeyAuthMethods: {
		E_NONE: 0,
		E_SSO: 1,
		E_SYSTEM: 2,
		has: function(val, enumeration) {
			return ((val & enumeration) == enumeration);
		}
	}
});
appdb.views.ApiKeyItem = appdb.ExtendClass(appdb.View, "appdb.views.ApiKeyItem", function(o) {
	this._index = -1;
	this._data = {"id": null, "key": null, "authmethods": null, "onwerid": null, "createdon": null, "netfilter": []};
	this.getData = function() {
		return this._data;
	};
	this.getIndex = function() {
		return this.index;
	};
	this.formatDate = function(d) {
		var i = d.split(" ");
		return i[0];
	};
	this.collectData = function() {
		var res = $.extend(this._data, {});
		res.netfilter = [];
		var netfilter = this.subviews[0].collectData() || [];
		if (netfilter.length > 0) {
			for (var i = 0; i < netfilter.length; i += 1) {
				res.netfilter.push({"value": netfilter[i].value});
			}
		}
		return res;
	};
	this.removeKey = function(data, dom) {
		if (!appdb.views.ApiKeyItem.labels || !appdb.views.ApiKeyItem.labels.confirmdeletion) {
			this.publish({"event": "delete", "value": data});
			return;
		}
		var conf = document.createElement("div"), actions = document.createElement("div"),
				remove = document.createElement("a"), cancel = document.createElement("a");

		$(remove).addClass("action").append("<span>delete</span>");
		$(cancel).addClass("action").append("<span>cancel</span>");
		$(actions).addClass("actions").append(remove).append(cancel);
		$(conf).addClass("confirmdelete").append($(appdb.views.ApiKeyItem.labels.confirmdeletion)).append(actions);
		var confirm = new dijit.Dialog({
			title: "Confirm API Key deletion",
			content: $(conf)[0],
			onCancel: (function(self) {
				return function() {
					$(self.dom).closest(".apikeylistcontainer").removeClass("loading");
				};
			})(this)
		});
		$(this.dom).closest(".apikeylistcontainer").addClass("loading");
		confirm.show();

		$(remove).click((function(_this) {
			return function() {
				$(_this.dom).closest(".apikeylistcontainer").removeClass("loading");
				_this.publish({"event": "delete", "value": data});
				confirm.hide();
				confirm.destroyRecursive(false);
				confimr = null;
			};
		})(this));

		$(cancel).click((function(_this) {
			return function() {
				$(_this.dom).closest(".apikeylistcontainer").removeClass("loading");
				confirm.hide();
				confirm.destroyRecursive(false);
				confimr = null;
			};
		})(this));
	};
	this.renderAuthentication = function(d) {
		var a = new appdb.views.ApiKeyAuthentication({container: $(this.dom).find(".authenticationcontainer")[0], "apikey": d.key});
		a.subscribe({
			"event": "insertuser",
			"callback": function(d) {
				this.publish({
					"event": "insertuser",
					"value": d
				});
			},
			"caller": this
		}).subscribe({
			"event": "changepassword",
			"callback": function(d) {
				this.publish({
					"event": "changepassword",
					"value": d
				});
			},
			"caller": this
		}).subscribe({
			"event": "changename",
			"callback": function(d) {
				this.publish({
					"event": "changename",
					"value": d
				});
			},
			"caller": this
		}).subscribe({
			"event": "requestpermisions",
			"callback": function(d) {
				this.publish({
					"event": "requestpermisions",
					"value": d
				});
			},
			"caller": this
		});
		this.subviews.push(a);
		a.render(d);
	};
	this.render = function(d) {
		d = d || {};
		//load new data
		this._data.id = d.id || null;
		this._data.key = d.key || null;
		this._data.authmethods = d.authmethods || null;
		this._data.ownerid = d.ownerid || null;
		this._data.createon = d.createdon || null;
		this._data.netfilter = d.netfilter || [];

		//declare html elements
		var div = document.createElement("div"), key = document.createElement("div"),
				deletelink = document.createElement("a"), netfilters = document.createElement("div"),
				authentication = document.createElement("div"), createdon = document.createElement("div");

		//setup classes and values for elements
		$(deletelink).addClass("action").addClass("delete").append(appdb.views.ApiKeyItem.actions.remove || "<span>remove</span>");
		$(key).addClass("apikey").append(appdb.views.ApiKeyItem.labels.apikey || "<span></span>").append("<div class='value'>" + this._data.key + "</div>").append(deletelink);
		$(netfilters).addClass("netfilters");
		$(authentication).addClass("authenticationcontainer");
		$(createdon).addClass("createdon").append(appdb.views.ApiKeyItem.labels.createdon || "<span></span>").append("<div class='value'>" + this.formatDate(this._data.createon) + "</div>");

		//Construct main element
		$(div).addClass("apikeyitem").
				append($(key)).
				append($(netfilters)).
				append($(authentication)).
				append($(createdon));

		//Setup event handler for api key removal
		$(deletelink).click((function(_this) {
			return function(i, el) {
				_this.removeKey(_this._data, this);
				return false;
			};
		})(this));

		//Initialize Api net filter list for the current web api key
		if (this.subviews.length > 0) {
			this.subviews[0].destroy();
		}
		this.subviews.push(new appdb.views.ApiNetFiltersList({container: netfilters, index: this._index}));
		this.subviews[0].render(this._data.netfilter);
		this.subviews[0].subscribe({"event": "delete", "callback": function(d) {
				var alldata = this.collectData();
				var i, len = alldata.netfilter.length, newfilters = [];
				for (i = 0; i < len; i += 1) {
					if (alldata.netfilter[i].value !== d) {
						newfilters.push({"value": alldata.netfilter[i].value});
					}
				}
				alldata.netfilter = newfilters;
				setTimeout((function(_this) {
					_this.publish({"event": "change", "value": alldata});
				})(this), 0);
			}, "caller": this}).subscribe({
			"event": "insert",
			"callback": function(v) {
				var alldata = $.extend(this.collectData(), {}, true);
				alldata.netfilter.push({"value": v});
				this.publish({"event": "change", "value": alldata});
			},
			"caller": this
		});
		$(this.dom).append(div);
		this.renderAuthentication(d);
	};
	this._init = function() {
		this._index = (typeof o.index === "undefined") ? -1 : o.index;
		if (!appdb.views.ApiKeyItem.labels) {
			appdb.views.ApiKeyItem.labels = {};
		}
		if (!appdb.views.ApiKeyItem.actions) {
			appdb.views.ApiKeyItem.actions = {};
		}
		if ((typeof o.hideAuthentication !== "undefined" && o.hideAuthentication == false) == true) {
			this.renderAuthentication = function() {
			};
		}
	};
	this._init();
}, {
	labels: {
		apikey: "<div class='label'>API Key:</div>",
		createdon: "<div class='label'>Created:</div>",
		confirmdeletion: "<div>Are you sure you want to delete this API key? Any external application using this API key will not have write access with the AppDB API.</div>"
	},
	actions: {
		remove: "<div>remove</div>"
	}
});

appdb.views.ApiKeyList = appdb.ExtendClass(appdb.View, "appdb.views.ApiKeyList", function(o) {
	this.clearSubviews = function() {
		var i, s = this.subviews, len = s.length;
		for (i = 0; i < len; i += 1) {
			s[i].unsubscribeAll(this);
			s[i].destroy();
		}
		this.subviews = [];
	};
	this.createItem = function(d, index) {
		var li = document.createElement("li");
		var item = new appdb.views.ApiKeyItem({"container": $(li), "index": index});
		this.subviews.push(item);
		return li;
	};
	this.render = function(d) {
		this.clearSubviews();
		this.reset();
		this._initContainer();
		d = d || [];
		d = $.isArray(d) ? d : [d];
		var i, len = d.length, ul = $(this.dom).find(".apikeylist:last"), adderContainer = document.createElement("div"),
				adder = document.createElement("a"), adderinfo = document.createElement("div");

		for (i = 0; i < len; i += 1) {
			$(ul).append(this.createItem(d[i], i));
		}
		len = this.subviews.length;
		for (i = 0; i < len; i += 1) {
			this.subviews[i].render(d[i], i);
			this.subviews[i].
					subscribe({"event": "change", "callback": function(d) {
							setTimeout((function(_this) {
								return function() {
									_this.publish({"event": "change", "value": d});
								};
							})(this), 0);
						}, "caller": this}).
					subscribe({"event": "delete", "callback": function(d) {
							setTimeout((function(_this) {
								return function() {
									_this.publish({"event": "delete", "value": d});
								};
							})(this), 0);
						}, "caller": this}).
					subscribe({"event": "insertuser", "callback": function(d) {
							this.publish({"event": "insertuser", "value": d});
						}, "caller": this}).
					subscribe({"event": "changepassword", "callback": function(d) {
							this.publish({"event": "changepassword", "value": d});
						}, "caller": this}).
					subscribe({"event": "changename", "callback": function(d) {
							this.publish({"event": "changename", "value": d});
						}, "caller": this}).
					subscribe({"event": "requestpermisions", "callback": function(d) {
							this.publish({"event": "requestpermisions", "value": d});
						}, "caller": this});
		}
		if (appdb.views.ApiKeyList.maxitems && appdb.views.ApiKeyList.maxitems > len) {
			if (appdb.views.ApiKeyList.actions.add) {
				$(adderContainer).addClass("addercontainer");
				$(adderinfo).addClass("adderinfo").append(appdb.views.ApiKeyList.labels.info || "<span></span>");
				$(adder).addClass("action").append(appdb.views.ApiKeyList.actions.add).click((function(_this) {
					return function() {
						_this.publish({"event": "generate", "value": ""});
					};
				})(this));
				$(adderContainer).append(adder).append(adderinfo);
				var remains = appdb.config.api.maxkeys || 1;
				remains = remains - len;
				$(adderinfo).find(".apikeysleft").empty().text(remains);
				$(this.dom).append(adderContainer);
			}
		} else if (appdb.views.ApiKeyList.maxitems && appdb.views.ApiKeyList.maxitems > 1 && appdb.views.ApiKeyList.maxitems <= len) {
			$(this.dom).append($(appdb.views.ApiKeyList.labels.maximum || "<span></span>"));
		}
	};
	this._init = function() {
		this.subviews = [];
		this._initContainer();
		appdb.views.ApiKeyList.actions = appdb.views.ApiKeyList.actions || {};
		appdb.views.ApiKeyList.labels = appdb.views.ApiKeyList.labels || {};
	};
	this._initContainer = function() {
		var ul = document.createElement("ul");
		$(ul).addClass("apikeylist");
		$(this.dom).append(ul);
	};
	this._init();
}, {
	actions: {
		add: "<div class='addkey'>Generate new API key</div>"
	},
	labels: {
		maximum: "<div class='explanation'>You cannot generate more API keys.</div>",
		noprimarycontact: "<div></div>",
		info: "<span class='apikeyinfotext'><div>You can generate  " + ((!appdb.config.api.maxkeys || appdb.config.api.maxkeys == 1) ? "<span class='maxapikeynumber'>1</span>" : "up to <span class='maxapikeynumber'>" + appdb.config.api.maxkeys) + "</span> API key" + ((appdb.config.api.maxkeys == "1") ? "" : "s") + ".<span class='info remaining'><span class='apikeysleft'></span> more remaining.</span></span>"
	},
	maxitems: (appdb.config.api.maxkeys || "1")
});

appdb.views.TokenNetFilterItem = appdb.ExtendClass(appdb.View, "appdb.views.TokenNetFilterItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {}
	};
	this.getData = function() {
		return this.options.data;
	};
	this.annotate = function(enabled) {
		enabled = enabled || false;
		if (enabled === true) {
			$(this.dom).addClass("annotated");
		} else {
			$(this.dom).removeClass("annotated");
		}
	};
	this.render = function(d) {
		this._data = d.value;
		var div = document.createElement("div"), deletelink = document.createElement("a");
		$(div).addClass("netfilteritem");
		$(div).html("<div class='value' >" + this._data + "</div>").append(deletelink);
		$(deletelink).addClass("action").html(appdb.views.TokenNetFilterItem.actions.remove || "<span></span>").click((function(self) {
			return function(ev) {
				ev.preventDefault();
				setTimeout(function() {
					self.publish({"event": "delete", "value": self.getData()});
				}, 0);
				return false;
			};
		})(this)).hover(function() {
			$(this).parent().toggleClass("hover");
		});
		$(this.dom).empty().append(div);
	};
	appdb.views.TokenNetFilterItem.actions = appdb.views.TokenNetFilterItem.actions || {};
}, {
	actions: {
		remove: "<div class='delete'>remove</div>"
	}
});
appdb.views.TokenNetFilterRegister = appdb.ExtendClass(appdb.View, "appdb.views.TokenNetFilterRegister", function(o) {
	this.ipInput = null;
	this.currentValue = "";
	this.validate = function() {
		var i, ex = appdb.views.ApiNetFilterRegister.validations,
				val = $.trim(this.currentValue), res = undefined, err = "The value you provided is not valid";
		//Check format validity
		for (i in ex) {
			if (ex.hasOwnProperty(i)) {
				if (ex[i].test(val)) {
					res = i;
					break;
				}
			}
		}
		//Check if value is already used for this key
		if (this.parent) {
			var items = this.parent.collectData();
			for (i = 0; i < items.length; i += 1) {
				if ($.trim(items[i].value) === val) {
					var item = this.parent.getItem(val);
					if (item) {
						item.annotate(true);
					}
					res = undefined;
					err = "The value is already in use";
					break;
				} else {
					this.parent.clearItemAnnotations();
				}
			}
		}
		$(this.dom).find(".validation").empty();
		if (typeof res === "undefined" && val !== "") {
			$(this.dom).find(".validation").append("<img src='/images/exclam16.png' border='0'/><span>" + err + "</a>");
		}
		return res;
	};
	this.reset = function() {
		if (this.ipInput !== null) {
			this.ipInput.destroyRecursive(false);
			this.ipInput = null;
		}
		$(this.dom).empty();
	};
	this.render = function() {
		var input = document.createElement("div"), add = document.createElement("a");
		$(add).addClass("action").addClass("disabled").append($(appdb.views.TokenNetFilterRegister.actions.add || "<span></span>"));
		$(add).click((function(self) {
			return function(ev) {
				ev.preventDefault();
				if ($(this).hasClass("disabled") === false) {
					self.publish({"event": "insert", "value": self.currentValue});
				}
				return false;
			};
		})(this));
		if (appdb.views.TokenNetFilterRegister.labels.description) {
			var description = document.createElement("div");
			$(description).addClass("description").append(appdb.views.TokenNetFilterRegister.labels.description);
			$(this.dom).append($(description));
		}
		$(this.dom).append(input).append(add);

		if (this.ipInput) {
			this.ipInput.destroyRecursive(false);
			this.ipInput = null;
		}
		this.ipInput = new dijit.form.TextBox({
			value: "",
			title: appdb.views.TokenNetFilterRegister.labels.inputWaterMask || "",
			onChange: (function(_this, addLink) {
				return function(v) {
					_this.currentValue = v;
					if (_this.validate()) {
						$(addLink).removeClass("disabled");
					} else {
						$(addLink).addClass("disabled");
					}
				};
			})(this, add),
			onKeyUp: (function(_this, addLink) {
				return function(e) {
					var v = _this.ipInput.attr("displayedValue");
					_this.currentValue = v;
					if (_this.validate()) {
						$(addLink).removeClass("disabled");
					} else {
						$(addLink).addClass("disabled");
					}
				};
			})(this, add)
		}, $(input)[0]);

		if (appdb.views.TokenNetFilterRegister.labels.examples) {
			$(this.dom).find(".viewexamples.hide").removeClass("hide");
			var tip = new dijit.TooltipDialog({
				content: appdb.views.TokenNetFilterRegister.labels.examples
			});
			$(this.dom).find("a.cidrexamples").click(function() {
				if (appdb.views.TokenNetFilterRegister.tips !== null) {
					dijit.popup.close(appdb.views.TokenNetFilterRegister.tips);
				}
				dijit.popup.open({
					popup: tip,
					around: $(this)[0]
				});
				appdb.views.TokenNetFilterRegister.tips = tip;
				return false;
			});
		}
		if (appdb.views.TokenNetFilterRegister.labels.validation) {
			$(this.dom).append(appdb.views.TokenNetFilterRegister.labels.validation);
		}
		if (appdb.views.TokenNetFiltersList.maxitems) {
			var maxfilters = document.createElement("div");
			$(maxfilters).addClass("maxfilters");
			if ($.trim(appdb.views.TokenNetFiltersList.maxitems) === "1") {
				$(maxfilters).append("<span><sup>*You can add <b>one</b> filter per access token</sup></span>");
			} else {
				$(maxfilters).append("<span><sup>*You can add up to <b>" + appdb.views.TokenNetFiltersList.maxitems + "</b> filters per access token</sup></span>");
			}
			$(this.dom).append(maxfilters);
		}

	};
	this._init = function() {
		this.index = (typeof o.index === "undefined") ? 0 : o.index;
		appdb.views.TokenNetFilterRegister.actions = appdb.views.TokenNetFilterRegister.actions || {};
		appdb.views.TokenNetFilterRegister.validations = appdb.views.TokenNetFilterRegister.validations || {};
		appdb.views.TokenNetFilterRegister.labels = appdb.views.TokenNetFilterRegister.labels || {};
	};
	this._init();
}, {
	validations: {
		domainName: /^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/i,
		ipv4: /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/i,
		ipv4CIDR: /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/(\d|[1-2]\d|3[0-2]))$/i,
		ipv6: /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*/i,
		ipv6CIDR: /^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*(\/(\d|\d\d|1[0-1]\d|12[0-8]))$/i
	},
	actions: {
		add: "<div class='add'>add</div>"
	},
	labels: {
		inputWaterMask: "Please enter the netfilter value...",
		description: "<span>Although this is optional please consider providing a netfilter in order to restrict access to the current access token. Its value can be a simple IP address, a CIDR formatted address, a (sub)domain name, or a host name. <span class='viewexamples hide'>Click <a class='cidrexamples' href='#'>here</a> for examples.</span></span>",
		validation: "<div class='validation'></div>",
		examples: "<div class='netfilters examples'>The following are some valid netfilter values:<br/><span><a href='http://en.wikipedia.org/wiki/Domain_name' target='_blank'>Domain name</a>:</span>" +
				"<ul><li>mydomain.eu</li><li>subdomain.mydomain.eu</li><li>iasa.gr</li></ul>" +
				"<span><a href='http://en.wikipedia.org/wiki/Cidr' target='_blank'>CIDR</a>:</span>" +
				"<ul><li>195.134.89.0/24</li><li>195.251.54.0/25</li></ul>" +
				"<span><a href='http://en.wikipedia.org/wiki/IP_address' target='_blank'>IP Address</a>:</span>" +
				"<ul><li>195.134.89.11</li><li>195.251.54.93</li></ul>" +
				"</div>"
	},
	tips: null
});
appdb.views.TokenNetFiltersList = appdb.ExtendClass(appdb.View, "appdb.views.TokenNetFilters", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {"id": null, "netfilters": []}
	};
	this.index = 0;
	this.netFilterAdd = null;
	this.getData = function() {
		return this.options.data.netfilters;
	};
	this.getId = function() {
		return this.options.data.id;
	};
	this.collectData = function() {
		return this.options.data.netfilters || [];
	};
	this.clearItemAnnotations = function() {
		this.subviews = this.subviews || [];
		this.subviews = $.isArray(this.subviews) ? this.subviews : [this.subviews];
		$.each(this.subviews, function(i, e) {
			if (e.annotate) {
				e.annotate(false);
			}
		});
	};
	this.getItem = function(v) {
		this.subviews = this.subviews || [];
		this.subviews = $.isArray(this.subviews) ? this.subviews : [this.subviews];
		$res = $.grep(this.subviews, (function(self) {
			return function(e) {
				if (e.annotate) {
					if ($.trim(e.getData()) === $.trim(v)) {
						return e;
					}
				}
			};
		})(this));
		return (res.length > 0) ? res[0] : null;
	};
	this.render = function(d) {
		if (d) {
			d = d || [];
			d = $.isArray(d) ? d : [d];
			this.options.data.netfilters = d;
		}
		d = this.options.data.netfilters;

		var len = d.length, title = document.createElement("div"),
				ul = document.createElement("ul"), adder = document.createElement("div");

		if (appdb.views.TokenNetFiltersList.labels.title) {
			$(title).addClass("title").html(appdb.views.TokenNetFiltersList.labels.title || "<span></span>");
		}
		$(this.dom).append($(title));
		if (d.length > 0) {
			$(ul).addClass("apinetfilterlist");
			$.each(d, (function(self) {
				return function(i, e) {
					var li = document.createElement("li");
					$(ul).append(li);
					var item = new appdb.views.TokenNetFilterItem({container: $(li), parent: this, data: e});
					item.subscribe({"event": "delete", "callback": function(d) {
							this.publish({"event": "delete", "value": d});
						}, "caller": self});
					self.subviews.push(item);
					item.render(e);
				};
			})(this));
			$(this.dom).append($(ul));
		} else {
			$(title).append($(appdb.views.TokenNetFiltersList.labels.empty || "<span></span>"));
		}
		if (appdb.views.TokenNetFiltersList.maxitems && appdb.views.TokenNetFiltersList.maxitems > len) {
			$(adder).addClass("netfilteradd");
			$(this.dom).append($(adder));
			if (this.netFilterAdd !== null) {
				this.netFilerAdd.unsubscribeAll(this);
				this.netFilerAdd.destroy();
			}
			this.netFilterAdd = new appdb.views.TokenNetFilterRegister({container: $(adder), index: this.index, parent: this});
			this.subviews.push(this.netFilterAdd);
			this.netFilterAdd.subscribe({
				"event": "insert",
				"callback": function(v) {
					this.publish({"event": "insert", "value": v});
				},
				"caller": this
			});
			this.netFilterAdd.render();
		} else if (appdb.views.TokenNetFiltersList.labels.maximum && $.trim(appdb.views.TokenNetFiltersList.labels.maximum) !== "1") {
			$(this.dom).append($(appdb.views.TokenNetFiltersList.labels.maximum || "<span></span>"));
		}
	};
	this._init = function() {
		appdb.views.TokenNetFiltersList.labels = appdb.views.TokenNetFiltersList.labels || {};
		appdb.views.TokenNetFiltersList.actions = appdb.views.TokenNetFiltersList.actions || {};
		this.index = (typeof o.index === "undefined") ? 0 : o.index;
	};
	this._init();
}, {
	labels: {
		title: "<div class='label'>Netfilter" + ((appdb.config.api.maxnetfilters > 1) ? "s" : "") + ":</div>",
		empty: "<div class='empty'>There " + ((appdb.config.api.maxnetfilters > 1) ? "are" : "is") + " no netfilter" + ((appdb.config.api.maxnetfilters > 1) ? "s" : "") + " associated with this access token.</div>",
		maximum: "<div class='explanation'>The maximum number of netfilters is used for the specific access token.</div>"
	},
	actions: {
		add: "<div class='action add'>add new</div>"
	},
	maxitems: (appdb.config.api.maxnetfilters || "1")
});


appdb.views.AccessTokenListItem = appdb.ExtendClass(appdb.View, "appdb.views.AccessTokenListItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {}
	};
	this.collectData = function() {
		var res = $.extend(this._data, {});
		res.netfilter = [];
		var netfilter = this.subviews[0].collectData() || [];
		if (netfilter.length > 0) {
			for (var i = 0; i < netfilter.length; i += 1) {
				res.netfilter.push({"value": netfilter[i].value});
			}
		}
		res.id = this.options.data.id;
		return res;
	};
	this.formatDate = function(d) {
		var i = d.split(" ");
		return i[0];
	};
	this.removeToken = function(data) {
		if (!appdb.views.AccessTokenListItem.labels || !appdb.views.AccessTokenListItem.labels.confirmdeletion) {
			this.publish({"event": "delete", "value": data});
			return;
		}
		var conf = document.createElement("div"), actions = document.createElement("div"),
				remove = document.createElement("a"), cancel = document.createElement("a");

		$(remove).addClass("action").append("<span>delete</span>");
		$(cancel).addClass("action").append("<span>cancel</span>");
		$(actions).addClass("actions").append(remove).append(cancel);
		$(conf).addClass("confirmdelete").append($(appdb.views.AccessTokenListItem.labels.confirmdeletion)).append(actions);
		var confirm = new dijit.Dialog({
			title: "Confirm personal access token deletion",
			content: $(conf)[0],
			onCancel: function() {
				$(".accesstokenlistcontainer").removeClass("loading");
			}
		});
		$(this.dom).closest(".accesstokenlistcontainer").addClass("loading");
		confirm.show();

		$(remove).click((function(_this) {
			return function() {
				$(_this.parent.dom).removeClass("loading");
				_this.publish({"event": "delete", "value": data});
				confirm.hide();
				confirm.destroyRecursive(false);
				confimr = null;
			};
		})(this));

		$(cancel).click((function(_this) {
			return function() {
				$(".accesstokenlistcontainer").removeClass("loading");
				confirm.hide();
				confirm.destroyRecursive(false);
				confimr = null;
			};
		})(this));
	};
	this.render = function(d) {
		if (d) {
			d = d || {};
			this.options.data = d;
		}
		d = this.options.data;

		//load new data
		d.id = d.id || null;
		d.token = d.token || null;
		d.addedby = d.addedby || null;
		d.createdon = d.createdon || null;
		d.netfilter = d.netfilter || [];
		d.netfilter = $.isArray(d.netfilter) ? d.netfilter : [d.netfilter];

		//declare html elements
		var div = document.createElement("div"), token = document.createElement("div"),
				deletelink = document.createElement("a"), netfilters = document.createElement("div"),
				createdon = document.createElement("div");

		//setup classes and values for elements
		$(deletelink).addClass("action").addClass("delete").append(appdb.views.AccessTokenListItem.actions.remove || "<span>remove</span>");
		$(token).addClass("apikey").addClass("header").append(appdb.views.AccessTokenListItem.labels.accesstoken || "<span></span>").append("<div class='value'>" + d.token + "</div>").append(deletelink);
		$(netfilters).addClass("netfilters");
		$(createdon).addClass("createdon").append(appdb.views.AccessTokenListItem.labels.createdon || "<span></span>").append("<div class='value'>" + this.formatDate(d.createdon) + "</div>");

		//Construct main element
		$(div).addClass("accesstokenitem").addClass("listitem").
				append($(token)).
				append($(netfilters)).
				append($(createdon));

		//Setup event handler for api key removal
		$(deletelink).click((function(self, data) {
			return function(ev) {
				ev.preventDefault();
				self.removeToken(data, this);
				return false;
			};
		})(this, d));

		//Initialize Api net filter list for the current web api key
		if (this.subviews.length > 0) {
			this.subviews[0].destroy();
		}
		this.subviews.push(new appdb.views.TokenNetFiltersList({container: netfilters, index: this._index, data: d}));
		this.subviews[0].render(d.netfilter);
		this.subviews[0].subscribe({"event": "delete", "callback": function(d) {
				var alldata = this.collectData();
				var i, len = alldata.netfilter.length, newfilters = [];
				for (i = 0; i < len; i += 1) {
					if ($.trim(alldata.netfilter[i].value) !== $.trim(d.value)) {
						newfilters.push({"value": alldata.netfilter[i].value});
					}
				}
				alldata.netfilter = newfilters;
				setTimeout((function(_this) {
					_this.publish({"event": "change", "value": alldata});
				})(this), 0);
			}, "caller": this}).subscribe({
			"event": "insert",
			"callback": function(v) {
				var alldata = $.extend(this.collectData(), {}, true);
				alldata.netfilter.push({"value": v});
				this.publish({"event": "change", "value": alldata});
			},
			"caller": this
		});
		$(this.dom).append(div);
	};
	this._initContainer = function() {

	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
}, {
	labels: {
		accesstoken: "<div class='label'>Personal Access Token:</div>",
		createdon: "<div class='label'>Created:</div>",
		confirmdeletion: "<div>Are you sure you want to delete this personal access token? You won't be able to access AppDB API using this token anymore.</div>"
	},
	actions: {
		remove: "<div>remove</div>"
	}
});

appdb.views.AccessTokenList = appdb.ExtendClass(appdb.View, "appdb.views.AccessTokenList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null
	};
	this.clearSubviews = function() {
		this.subviews = this.subviews || [];
		this.subviews = $.isArray(this.subviews) ? this.subviews : [this.subviews];
		$.each(this.subviews, function(i, e) {
			e.unsubscribeAll();
			e.reset();
		});
		this.subviews = [];
	};
	this.addItem = function(itemdata) {
		var li = document.createElement("li");
		var item = new appdb.views.AccessTokenListItem({container: $(li), parent: this, data: itemdata});
		item.subscribe({"event": "change", "callback": function(d) {
				setTimeout((function(_this) {
					return function() {
						_this.publish({"event": "change", "value": d});
					};
				})(this), 0);
			}, "caller": this}).
				subscribe({"event": "delete", "callback": function(d) {
						setTimeout((function(_this) {
							return function() {
								_this.publish({"event": "delete", "value": d});
							};
						})(this), 0);
					}, "caller": this});
		this.subviews.push(item);
		item.render(itemdata);
		return li;
	};
	this.renderGenerateButton = function(d) {
		var len = d.length, adderContainer = document.createElement("div"), adder = document.createElement("a"), adderinfo = document.createElement("div");
		if (appdb.views.AccessTokenList.maxitems && appdb.views.AccessTokenList.maxitems > len) {
			if (appdb.views.AccessTokenList.actions.add) {
				$(adderContainer).addClass("addercontainer");
				$(adderinfo).addClass("adderinfo").append(appdb.views.AccessTokenList.labels.info || "<span></span>");
				$(adder).addClass("action").append(appdb.views.AccessTokenList.actions.add).click((function(_this) {
					return function() {
						_this.publish({"event": "generate", "value": ""});
					};
				})(this));
				$(adderContainer).append(adder).append(adderinfo);
				var remains = appdb.config.api.maxkeys || 1;
				remains = remains - len;
				$(adderinfo).find(".accesstokenleft").empty().text(remains);
				$(this.dom).append(adderContainer);
			}
		} else if (appdb.views.AccessTokenList.maxitems && appdb.views.AccessTokenList.maxitems > 1 && appdb.views.AccessTokenList.maxitems <= len) {
			$(this.dom).append($(appdb.views.AccessTokenList.labels.maximum || "<span></span>"));
		}
	};
	this.render = function(d) {
		d = d || [];
		d = $.isArray(d) ? d : [d];
		this.clearSubviews();
		this.reset();
		this._initContainer();
		this.renderGenerateButton(d);

		var ul = $(this.dom).find("ul.accesstokenlist");
		$(ul).empty();
		$.each(d, (function(self) {
			return function(i, e) {
				var li = self.addItem(e, i);
				if (li !== null) {
					$(ul).append(li);
				}
			};
		})(this));
	};
	this._initContainer = function() {
		$(this.dom).append("<ul class='accesstokenlist list'></ul>");
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.subviews = this.subviews || [];
		this.subviews = $.isArray(this.subviews) ? this.subviews : [this.subviews];
	};
	this._init();
}, {
	actions: {
		add: "<div class='addkey'>Generate new Personal Access Token</div>"
	},
	labels: {
		maximum: "<div class='explanation'>You cannot generate more Access Tokens.</div>",
		noprimarycontact: "<div></div>",
		info: "<span class='apikeyinfotext'><div>You can generate  " + ((!appdb.config.api.maxkeys || appdb.config.api.maxkeys === 1) ? "<span class='maxaccesstokennumber'>1</span>" : "up to <span class='maxaccesstokennumber'>" + appdb.config.api.maxkeys) + "</span> Access Token" + ((appdb.config.api.maxkeys == "1") ? "" : "s") + ".<span class='info remaining'><span class='accesstokenleft'></span> more remaining.</span></span>"
	},
	maxitems: (appdb.config.api.maxkeys || "1")
});
appdb.views.CustomMiddlewareForm = appdb.ExtendClass(appdb.View, "appdb.views.CustomMiddlewareForm", function(o) {
	this._name = "";
	this._link = "";
	this._nameinput = null;
	this._linkinput = null;
	this.close = function() {
		if (appdb.views.CustomMiddlewareForm.dialog) {
			appdb.views.CustomMiddlewareForm.dialog.hide();
		}
	};
	this.render = function(d) {
		if (appdb.views.CustomMiddlewareForm.dialog != null) {
			this.unsubscribeAll();
			appdb.views.CustomMiddlewareForm.dialog.hide();
			appdb.views.CustomMiddlewareForm.dialog.destroyRecursive(false);
			$(this.dom).empty().remove();
			this._initContainer();
		}
		if (d) {
			this._nameinput.setAttribute("value", d.name || "");
			this._linkinput.setAttribute("value", d.link || "");
		}

		appdb.views.CustomMiddlewareForm.dialog = new dijit.Dialog({
			title: appdb.views.CustomMiddlewareForm.text.title || "Insert unregistered middleware",
			content: $(this.dom)[0],
			onLoad: (function(_this) {
				return function() {
					setTimeout(function() {
						_this._validate();
					}, 1);
				};
			})(this),
			onClose: (function(_this) {
				return function() {
					_this.unsubscribeAll();
				};
			})(this)
		});
		appdb.views.CustomMiddlewareForm.dialog.show();
		this._validate();
	};
	this._validate = function() {
		var name = this._nameinput.attr("displayedValue");
		var link = this._linkinput.attr("displayedValue");
		res = this._validateName(name);
		if (res === true) {
			res = this._validateLink(link);
		}
		if (res !== true) {
			$(this.dom).find(".validation").html(res);
		} else {
			$(this.dom).find(".validation").html("<span></span>");
		}
		return res;
	};
	this._validateLink = function(n) {
		if ($.trim(n) === "" || $.trim(n).toLowerCase().replace(/^(https|http|htt|ht|h|ftps|ftp|ft|f)/, "").replace(/^:/, "").replace(/^\/{1,2}/, "") === "") {
			return "You need to provide a valid web link";
		}
		return true;
	};
	this._validateName = function(n) {
		var i, len, dm = appdb.model.Middlewares.getLocalData() || {middleware: []};
		dm = $.isArray(dm.middleware) ? dm.middleware : [dm.middleware];
		len = dm.length;
		if ($.trim(n) === "") {
			return "You need to provide a name for the middleware";
		}
		if (n.length > 50) {
			return "The name is too long";
		}
		if ($.trim(n).toLowerCase() === "other") {
			return "Please provide a valid name for the middleware";
		}
		for (i = 0; i < len; i += 1) {
			if ($.trim(dm[i].val()).toLowerCase() === $.trim(n).toLowerCase()) {
				return "Middleware <i>" + dm[i].val() + "</i> already exists";
			}
		}
		return true;
	};
	this._initContainer = function() {
		var div = document.createElement("div"), header = document.createElement("div"), headertext = document.createElement("span"),
				content = document.createElement("div"), name = document.createElement("div"), nametext = document.createElement("span"),
				nameinput = document.createElement("input"), link = document.createElement("div"), linktext = document.createElement("span"),
				linkinput = document.createElement("input"), footer = document.createElement("div"), actions = document.createElement("div"),
				ok = document.createElement("button"), cancel = document.createElement("button"), valid = document.createElement("span");

		$(div).addClass("middlewareform");
		$(header).addClass("header").append($(headertext).text(appdb.views.CustomMiddlewareForm.text.header || ""));
		$(name).addClass("name").append($(nametext).text(appdb.views.CustomMiddlewareForm.text.name || "")).append($(nameinput).attr("type", "text"));
		$(link).addClass("link").append($(linktext).text(appdb.views.CustomMiddlewareForm.text.link || "")).append($(linkinput).attr("type", "text"));
		$(content).addClass("content").append(name).append(link);
		$(actions).append().append(ok).append(cancel);

		$(footer).addClass("footer").append($(valid).addClass("validation")).append(actions);

		var okbutton = new dijit.form.Button({
			label: "ok",
			disabled: true,
			onClick: (function(_this) {
				return function() {
					_this.publish({event: "ok", value: {name: _this._name, link: _this._link}});
				};
			})(this)
		}, $(ok)[0]);

		var cancelbutton = new dijit.form.Button({
			label: "cancel",
			disabled: false,
			onClick: function() {
				appdb.views.CustomMiddlewareForm.dialog.hide();
			}
		}, $(cancel)[0]);

		this._nameinput = new dijit.form.TextBox({
			value: this._name || "",
			title: "Middleware name",
			onKeyUp: (function(caller) {
				return function(v) {
					caller._name = $.trim(caller._nameinput.attr("displayedValue"));
					caller._link = $.trim(caller._linkinput.attr("displayedValue"));
					var disable = ($.trim(caller._nameinput.attr("displayedValue")) !== "" && $.trim(caller._link).toLowerCase().replace(/^(https|http|htt|ht|h|ftps|ftp|ft|f)/, "").replace(/^:/, "").replace(/^\/{1,2}/, "") !== "");

					var isValid = caller._validate();
					if (isValid !== true) {
						disable = false;
					}
					okbutton.setAttribute("disabled", !disable);
				};
			})(this)
		}, $(nameinput)[0]);
		this._linkinput = new dijit.form.TextBox({
			value: this._link || "",
			title: "Middleware Web Link",
			onKeyUp: (function(caller) {
				return function(v) {
					caller._name = $.trim(caller._nameinput.attr("displayedValue"));
					caller._link = $.trim(caller._linkinput.attr("displayedValue"));
					var disable = ($.trim(caller._nameinput.attr("displayedValue")) !== "" && $.trim(caller._link).toLowerCase().replace(/^(https|http|htt|ht|h|ftps|ftp|ft|f)/, "").replace(/^:/, "").replace(/^\/{1,2}/, "") !== "");

					var isValid = caller._validate();
					if (isValid !== true) {
						disable = false;
					}
					okbutton.setAttribute("disabled", !disable);
				};
			})(this)
		}, $(linkinput)[0]);
		$(div).append(header).append(content).append(footer);

		this.dom = div;
	};
	this._init = function() {
		o = o || {};
		this._name = o.name || "";
		this._link = o.link || "";
		this._initContainer();
	};
	this._init();
}, {
	text: {
		title: "Register custom middleware",
		header: "Please provide the name of the middleware and the link to its official web site. This middleware will be associated only with the current software and will not be included in the middlewares list.",
		name: "Name: ",
		link: "Link: "
	},
	dialog: null
});
appdb.views.CustomMiddleware = appdb.ExtendClass(appdb.View, "appdb.views.CustomMiddleware", function(o) {
	this._data = {name: "", link: ""};
	this.EditForm = new appdb.views.CustomMiddlewareForm();
	this.container = null;
	this.name = function(v) {
		if (typeof v === "undefined") {
			return this._data.name || "";
		}
		this._data.name = v;
		$(this.dom).find(":input[name^=mw]:last").val(this._data.name);
		return this;
	};
	this.link = function(v) {
		if (typeof v === "undefined") {
			return this._data.link || "";
		}
		this._data.link = v;
		var r = new RegExp(/^(https?|ftps?):\/\//);
		if (r.test(this._data.link) === false) {
			this._data.link = "http://" + encodeURIComponent(this._data.link);
		}
		$(this.dom).find(":input[name^=lmw]:last").val(this._data.link);
		return this;
	};
	this.render = function(d) {
		if (d) {
			this.name(d.name || this._data.name);
			this.link(d.link || this._data.link);
		}
		if (this.container) {
			$(this.container).empty();
		}
		var div = this.container || document.createElement("div"), name = document.createElement("div"), namelink = document.createElement("a"),
				edit = document.createElement("div"), remove = document.createElement("div"), editlink = document.createElement("a"),
				editimg = document.createElement("img"), removelink = document.createElement("a"), removeimg = document.createElement("img"),
				nameinput = document.createElement("input"), linkinput = document.createElement("input");

		$(div).addClass("custommiddleware");
		$(name).addClass("name").append($(namelink).attr("href", this.link()).attr("target", "_blank").text(this.name()));
		$(edit).addClass("edit").append($(editlink).attr("title", "Edit custom middleware").click((function(caller) {
			return function() {
				setTimeout(function() {
					var frm = new appdb.views.CustomMiddlewareForm(caller._data);
					frm.subscribe({event: "ok", callback: function(d) {
							frm.unsubscribeAll();
							frm.close();
							setTimeout(function() {
								caller.render(d);
							}, 1);
						}, caller: caller});
					frm.render();
				}, 1);
				return false;
			};
		})(this)).append($(editimg).attr("src", "/images/editicon.png")));

		$(remove).addClass("remove").append($(removelink).attr("title", "Remove custom middleware").click((function(caller) {
			return function() {
				caller.publish({"event": "remove", value: caller.index});
				return false;
			};
		})(this)).append($(removeimg).attr("src", "/images/cancelicon.png")));

		$(nameinput).attr("type", "hidden").attr("name", "mw" + this.index).attr("value", this.name());
		$(linkinput).attr("type", "hidden").attr("name", "lmw" + this.index).attr("value", this.link());

		$(div).append(name).append(edit).append(remove).append(nameinput).append(linkinput);
		if (!this.container) {
			this.container = div;
			$(this.dom).prepend(div);
		}
		$(div).mouseover(function() {
			$(this).addClass("hover");
		}).mouseleave(function() {
			$(this).removeClass("hover");
		});

	};
	this._init = function() {
		this.index = o.index || 0;
		this._data = o.data || {name: $(this.dom).find(".mwname").text() || "", link: $(this.dom).find(".mwhome").attr("href") || ""};
	};
	this._init();
});

appdb.views.CustomMiddlewareHandler = appdb.ExtendClass(appdb.View, "appdb.views.CustomMiddlewareHandler", function(o) {
	this.container = o.listcontainer || $("span.app-mws");
	this.addCustomMiddleware = function(d) {
		var span = document.createElement("span");
		$(span).addClass("app-mw").addClass("other");
		$(this.container).append(span);
		this.subviews.push(new appdb.views.CustomMiddleware({container: span, data: d, index: this.subviews.length}));
		this.subviews[this.subviews.length - 1].subscribe({event: "remove", callback: function(d) {
				setTimeout((function(_this) {
					return function() {
						validateItemRemoval("middleware", _this.subviews[d].name(), function() {
							var dom = $(_this.subviews[d].dom);
							_this.subviews[d].unsubscribeAll(_this);
							_this.subviews[d].reset();
							_this.subviews[d].destroy();
							$(dom).remove();
						});
					};
				})(this), 1);
			}, caller: this});
		this.subviews[this.subviews.length - 1].render();
		$(this.container).find(".unspecified").remove();
	};
	this._render = function() {

	};
	this.validateValues = function() {

	};
	this.setupCustomMiddlewaresForm = function() {

	};
	this.editCustomMiddlewares = function() {
		var dwm = appdb.model.Middlewares.getLocalData() || {middleware: []}, i, j;
		dwm = dwm.middleware;
		dwm = $.isArray(dwm) ? dwm : [dwm];
		var len = dwm.length, len2 = 0, res = [];

		$(this.container).find("span.app-mw.other").each(function(index, elem) {
			res.push({"dom": $(elem), "name": $(elem).find(".mwname").text(), "link": $(elem).find(".mwhome").attr("href") || ""});
		});

		if (res.length === 0) {
			return;
		}
		len = res.length;
		for (i = 0; i < len; i += 1) {
			$(res[i].dom).removeClass("editable").empty();
			this.subviews.push(new appdb.views.CustomMiddleware({"container": res[i].dom, "data": {"name": res[i].name, "link": res[i].link}, index: this.subviews.length}));
			this.subviews[this.subviews.length - 1].subscribe({event: "remove", callback: function(d) {
					setTimeout((function(_this) {
						return function() {
							validateItemRemoval("middleware", _this.subviews[d].name(), function() {
								var dom = $(_this.subviews[d].dom);
								_this.subviews[d].unsubscribeAll(_this);
								_this.subviews[d].reset();
								_this.subviews[d].destroy();
								$(dom).remove();
							});

						};
					})(this), 1);
				}, caller: this});
			this.subviews[this.subviews.length - 1].render();
		}
	};
	this._initContainer = function() {
		this.editCustomMiddlewares();
		var span = document.createElement("span"), a = document.createElement("a");
		$(a).html(appdb.views.CustomMiddlewareHandler.text.handler || "<span>New</span>").click((function(caller) {
			return function() {
				var frm = new appdb.views.CustomMiddlewareForm({name: "", link: "http://"});
				frm.subscribe({event: "ok", callback: function(d) {
						if ($.trim(d.name) === "" && ($.trim(d.link).toLowerCase().replace(/^[(http|https|ftp|ftps)]\:?\/?\/?/) === "")) {
							return;
						}
						setTimeout(function() {
							frm.unsubscribeAll(caller);
							frm.close();
							caller.addCustomMiddleware(d);
						}, 1);
					}, caller: caller});
				frm.render();
			};
		})(this));
		$(span).addClass("custommiddlewarehandler").append(a);
		$(this.dom).append(span);
	};
	this._init = function() {
		this.container = o.listcontainer || this.container;
		this._initContainer();
	};
	this._init();
}, {
	text: {
		handler: "<img src='/images/add.png' border='0'></img><span>Register new<span>"
	}
});

appdb.views.TristateCheckbox = appdb.ExtendClass(appdb.View, "appdb.views.TristateCheckbox", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		states: {
			checked: (o.states && o.states.checked) ? o.states.checked : ((appdb.views.TristateCheckbox.states.checked) ? appdb.views.TristateCheckbox.states.checked : null),
			unchecked: (o.states && o.states.unchecked) ? o.states.unchecked : ((appdb.views.TristateCheckbox.states.unchecked) ? appdb.views.TristateCheckbox.states.unchecked : null),
			somechecked: (o.states && o.states.somechecked) ? o.states.somechecked : ((appdb.views.TristateCheckbox.states.somechecked) ? appdb.views.TristateCheckbox.states.somechecked : null)
		},
		label: o.label || appdb.views.TristateCheckbox.label || "",
		currentState: 1,
		previousState: 1,
		statesid: {
			checked: 0,
			unchecked: 1,
			somechecked: 2
		},
		dom: $(document.createElement("div")).addClass("tristate")
	};
	this.getState = function() {
		switch (this.options.currentState) {
			case this.options.statesid.checked:
				return true;
			case this.options.statesid.unchecked:
				return false;
			default:
				return null;
		}
	};
	this.getStateCss = function(state) {
		state = (typeof state === "undefined") ? this.options.currentState : state;
		switch (state) {
			case this.options.statesid.checked:
				return "checked";
			case this.options.statesid.unchecked:
				return "unchecked";
			default:
				return "somechecked";
		}
	};
	this.setLabel = function(label) {
		if (label) {
			if (typeof label === "function") {
				label = lable.apply(this);
			}
			this.options.label = label;
		}
		$(this.dom).find(".label").html(this.options.label);
	};
	this.setChecked = function() {
		this.setState(this.options.statesid.checked);
	};
	this.setUnchecked = function() {
		this.setState(this.options.statesid.unchecked);
	};
	this.setSomeChecked = function() {
		this.setState(this.options.statesid.somechecked);
	};
	this.setState = function(state) {
		state = (typeof state === "undefined") ? 1 : state;
		var changed = (state === this.options.previousState);

		if (changed === true) {
			this.options.previousState = this.options.currentState;
		}
		this.options.currentState = state;
		$(this.options.dom).removeClass("checked").removeClass("unchecked").removeClass("somechecked");
		$(this.options.dom).addClass(this.getStateCss());
		if (changed === true) {
			this.publish({event: "changed", value: this.options.currentState});
		}
	};
	this.render = function(state) {
		this.setState(state);
		$(this.dom).find(".tristate").unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				if ($(this).hasClass("checked")) {
					self.setUnchecked();
				} else if ($(this).hasClass("unchecked")) {
					self.setChecked();
				} else {
					self.setSomeChecked();
				}
				return false;
			};
		})(this));
	};
	this.initContainer = function() {
		this.options.dom = $(document.createElement("div")).addClass("tristate").addClass(this.getStateCss(this.options.statesid.unchecked));
		$(this.options.dom).append($(document.createElement("div")).addClass("state").addClass(this.getStateCss(this.options.statesid.checked))).
				append($(document.createElement("div")).addClass("state").addClass(this.getStateCss(this.options.statesid.unchecked))).
				append($(document.createElement("div")).addClass("state").addClass(this.getStateCss(this.options.statesid.somechecked)));
		if (this.options.label) {
			$(this.options.dom).append(this.options.label);
		}
		$(this.options.dom).find(".checked").html(this.options.states.checked);
		$(this.options.dom).find(".unchecked").html(this.options.states.unchecked);
		$(this.options.dom).find(".somechecked").html(this.options.states.somechecked);

		$(this.dom).append(this.options.dom);
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
}, {
	states: {
		checked: "<img class='content' src='/images/yes.png' alt='' />"
	},
	label: "<span class='label'></span>"
});
/*
 *Component to load hierarchical data (id->parentid) and 
 *provide functionality for querung and handling it.
 */
appdb.views.TreeDataView = appdb.ExtendClass(appdb.View, "appdb.views.TreeDataView", function(o) {
	o = o || {};
	this.options = {
		propertyParentid: o.propertyParentid || "parentid",
		propertyItemName: o.propertyItemName || "item",
		propertyItemId: o.propertyItemId || "id",
		name: o.name || "item",
		children: o.chidlren || [],
		data: o.data || {},
		id: o.id,
		parentid: o.parentid,
		parent: o.parent || null,
		datalevel: o.datalevel || null,
		level: o.level || 0,
		dataFilter: o.dataFilter
	};
	this.sortChildren = function() {
		/* to be overriden*/
		this.options.children = this.options.children || [];
		if (this.options.children.length > 0) {
			this.options.children.sort(function(a, b) {
				var aa = (a.options && a.options.data && a.options.data.data && typeof a.options.data.data.order !== "undefined") ? a.options.data.data.order : "0";
				var bb = (b.options && b.options.data && b.options.data.data && typeof b.options.data.data.order !== "undefined") ? b.options.data.data.order : "0";

				if ((bb << 0) > (aa << 0))
					return -1;
				if ((bb << 0) < (aa << 0))
					return 1;
				return 0;
			});
		}
	};
	this.getSubDataViewByIds = function(ids, subdata) {
		ids = ids || [];
		ids = $.isArray(ids) ? ids : [ids];
		var root = this.getRoot();
		var sub = subdata || {};
		var self = this;
		var idname = this.options.propertyItemId;
		var pidname = this.options.propertyParentId;
		//collect items in ids
		$.each(root.options.data, function(i, e) {
			if (e[idname] && !sub[e[idname]] && $.inArray(e[idname], ids) !== -1) {
				sub[e[idname]] = e;
				if (e[pidname] && !sub[e[pidname]]) {//check for parents
					var parent = self.getSubDataViewByIds([e[pidname]], sub);
					sub[e[pidname]] = parent[e[pidname]];
				}
			}
		});
		if (subdata) {
			return sub;
		}
		var res = [];
		for (var i in sub) {
			if (sub.hasOwnProperty(i)) {
				res.push(sub[i]);
			}
		}
		var objTreeType = appdb.FindNS(this._type_.getName()) || appdb.views.TreeDataViewItem;
		var objTreeInst = new objTreeType();
		var objData = {};
		objData[this.options.propertyItemName] = res;
		objTreeInst.load(objData);
		return objTreeInst;
	};
	this.getParentId = function() {
		return this.options.parentid;
	};
	this.getData = function() {
		return this.options.data;
	};
	this.getChildren = function() {
		return this.options.children;
	};
	this.isLeaf = function() {
		return !this.getChildren();
	};
	this.hasChildren = function() {
		return (this.getChildren().length > 0) ? true : false;
	};
	this.getParent = function() {
		return this.options.parent || null;
	};
	this.getId = function() {
		return this.options.id;
	};
	this.getChildById = function(id, deep) {
		deep = (typeof deep === "boolean") ? deep : true;
		var res = null;
		$.each(this.getChildren(), function(i, e) {
			if (!res) {
				if (e.getId() == id) {
					res = e;
				} else if (e.hasChildren() && deep === true) {
					res = e.getChildById(id, true);
				}
			}
		});
		return res;
	};
	this.updateChild = function(item) {
		if ($.isPlainObject(item) === false || isNaN(Math.floor(item.id)) === true)
			return null;
		var child = this.getChildById(item.id);
		if (!child)
			return null;

		child.options.data = $.extend(child.options.data, item, true);
		return child;
	};
	this.getRoot = function() {
		if (!(this.options.parent && this.options.parent.getRoot)) {
			return this;
		}
		return this.parent.getRoot();
	};
	this.addChild = function(item) {
		var objTreeType = appdb.FindNS(this._type_.getName()) || appdb.views.TreeDataViewItem;
		if (objTreeType) {
			var newtree = new objTreeType({
				propertyParentid: this.options.propertyParentid,
				propertyItemName: this.options.propertyItemName,
				propertyItemId: this.options.propertyItemId,
				parent: this,
				root: this.getRoot(),
				parentid: this.getId(),
				name: this.options.propertyItemName,
				data: item,
				datalevel: this.options.datalevel || null,
				level: this.options.level + 1,
				id: item.id
			});
			newtree.load();
			this.options.children.push(newtree);
		}
	};
	this.removeItemById = function(id) {
		var index = -1;
		$.each(this.options.chidlren, function(i, e) {
			if (index === -1 && e.getId() == id) {
				index = i;
			}
		});
		if (index > -1) {
			this.options.children.splice(index, 1);
		} else {
			$.each(this.options.chidlren, function(i, e) {
				e.removeItemById(id);
			});
		}
	};
	this.isRoot = function() {
		return (this.options.parent === null);
	};
	this.getDataByParentId = function(id) {
		var root = this.getRoot();
		var rootdata = root.options.data || [];
		var self = this;
		return $.grep(rootdata, function(e) {
			if (e[self.options.propertyParentid] == id)
				return true;
			return false;
		});
	};
	this.getDefaultDataStore = function() {
		//in case of root and no data is given 
		//the object will try to use the default data store
		//This function is safe to override
		return null;
	};
	this.load = function(d) {
		var self = this;
		if (this.isRoot()) {
			if (typeof d === "undefined") {
				d = this.getDefaultDataStore();
			}
			d = d || {};
			d = d[this.options.propertyItemName] || [];
			d = $.isArray(d) ? d : [d];
			this.options.data = d.slice(0);
		}
		if (this.isRoot() && typeof this.options.dataFilter === "function") {
			this.options.data = this.options.dataFilter(this.options.data);
		}
		var localdata = this.getDataByParentId(this.options.id);
		$.each(localdata, function(i, e) {
			self.addChild(e);
		});
		this.sortChildren();
		return this.options.children || [];
	};
	this.transformData = function(level) {
		level = level || 0;
		var children = [];
		if (this.options.datalevel == null || level + 1 <= this.options.datalevel) {
			$.each(this.getChildren(), function(i, e) {
				children.push(e.transformData(level + 1));
			});
		}
		var res = {};
		if (this.isRoot()) {
			res = children;
		} else {
			res = {
				id: this.getId(),
				value: this.getData().val(),
				order: this.getData().order || 0,
				parentid: this.getParentId(),
				children: children,
				data: this.getData(),
				level: level
			};
		}
		return res;
	};
	this._init = function() {
		this.parent = this.options.parent;
	};
	this._init();
});
/*
 *Extension of appdb.views.TreeDataView class.
 *Sets sepecific metadata to load category data list.
 */
appdb.views.CategoriesDataView = appdb.ExtendClass(appdb.views.TreeDataView, "appdb.views.CategoriesDataView", function(o) {
	this.options = $.extend(this.options, {
		propertyParentId: "parentid",
		propertyItemName: "category",
		propertyItemId: "id",
		name: "category"
	});
	this.getDefaultDataStore = function() {
		return $.extend(true, {}, appdb.model.ApplicationCategories.getLocalData());
	};
	this.sortChildren = function() {
		this.options.children = this.options.children || [];
		if (this.options.children.length > 0) {
			this.options.children.sort(function(a, b) {
				var aa = (a.options && a.options.data && a.options.data.data && typeof a.options.data.data.order !== "undefined") ? a.options.data.data.order : "0";
				var bb = (b.options && b.options.data && b.options.data.data && typeof b.options.data.data.order !== "undefined") ? b.options.data.data.order : "0";

				if (bb > aa)
					return -1;
				if (bb < aa)
					return 1;
				return 0;
			});
		}
	};
});
/*
 *Extension of appdb.views.TreeDataView class.
 *Sets sepecific metadata to load discipline data list.
 */
appdb.views.DisciplinesDataView = appdb.ExtendClass(appdb.views.TreeDataView, "appdb.views.DisciplinesDataView", function(o) {
	this.options = $.extend(this.options, {
		propertyParentId: "parentid",
		propertyItemName: "discipline",
		propertyItemId: "id",
		name: "discipline"
	});
	this.getDefaultDataStore = function() {
		return $.extend(true, {}, {discipline: appdb.model.StaticList.Disciplines});
	};
});
/*
 *Extension of appdb.views.TreeDataView class.
 *Sets sepecific metadata to load validated data list.
 */
appdb.views.ValidatedDataView = appdb.ExtendClass(appdb.views.TreeDataView, "appdb.views.ValidatedDataView", function(o) {
	this.options = $.extend(this.options, {
		propertyParentId: "parentid",
		propertyItemName: "validated",
		propertyItemId: "id",
		name: "validated"
	});
	this.getDefaultDataStore = function() {
		var data = [
			{id: "1", text: "true", parentid: null, val: function() {
					return "updated";
				}},
			{id: "2", text: "false", parentid: null, val: function() {
					return "outdated";
				}},
			{id: "3", text: "6 months", parentid: "1", val: function() {
					return "6 months";
				}},
			{id: "4", text: "1 year", parentid: "1", val: function() {
					return "1 year";
				}},
			{id: "5", text: "2 years", parentid: "1", val: function() {
					return "2 years";
				}},
			{id: "6", text: "3 years", parentid: "1", val: function() {
					return "3 years";
				}}
		];
		return $.extend(true, {}, {validated: data});
	};
});
/*
 *Extension of appdb.views.TreeDataView class.
 *Sets sepecific metadata to load validated data list.
 */
appdb.views.LicensesDataView = appdb.ExtendClass(appdb.views.TreeDataView, "appdb.views.LicensesDataView", function(o) {
	this.options = $.extend(this.options, {
		propertyParentId: "parentid",
		propertyItemName: "license",
		propertyItemId: "id",
		name: "license"
	});
	this.getDefaultDataStore = function() {
		var data = [].concat(appdb.model.StaticList.Licenses);
		var d = [];
		var grps = {};
		$.each(data, function(i, e) {
			if (!grps[e.group] && e.group !== "osi") {
				grps[e.group] = [e];
			}
			var item = $.extend(true, {}, e);
			if (item.group !== "osi") {
				item.parentid = e.group;
			} else {
				item.parentid = null;
			}
			item.val = function() {
				return this.title;
			};
			d.push(item);
		});
		for (var i in grps) {
			if (grps.hasOwnProperty(i) === false)
				continue;
			var pd = {id: i, text: i, parentid: null};
			if (i === "cc") {
				pd.val = function() {
					return "Creative Commons";
				};
			} else {
				pd.val = function() {
					return this.id;
				};
			}
			d.push(pd);
		}
		return $.extend(true, {}, {license: d});
	};
});
/*
 *UI component to render a cell of a appdb.views.SimpleTreeRow object.
 *Provides ad-hoc functionality for rendering and event handling.
 */
appdb.views.SimpleTreeCell = appdb.ExtendClass(appdb.View, "appdb.views.SimpleTreeCell", function(o) {
	this.options = {
		dataType: o.dataType || "text",
		name: o.name || null,
		parent: o.parent || null,
		onRenderCell: o.onRenderCell || null,
		onClick: o.onClick || null
	};
	this.getRow = function() {
		return this.options.parent;
	};
	this.render = function(d, container) {
		var value = "";
		var div = $(document.createElement((this.options.dataType === "link") ? "a" : "div")).addClass("cell");
		//$(container).append(div);
		if (this.options.name) {
			$(div).addClass(this.options.name);
		}
		if (this.options.name) {
			value = d[this.options.name];
			if (typeof value === "function") {
				value = value();
			}
		}
		switch (this.options.dataType) {
			case "link":
				value = $(document.createElement("span")).addClass("textvalue").html(value);
				$(div).append(value);
				if (typeof this.options.onClick === "function") {
					$(div).unbind("click").bind("click", (function(self, data) {
						return function(ev) {
							ev.preventDefault();
							self.options.onClick.call(self, data, this);
							return false;
						};
					})(this, d));
				}
				break;
			case "text":
			default:
				value = $(document.createElement("span")).addClass("textvalue").html(value);
				$(div).append(value);
				break;
		}
		if (typeof this.options.onRenderCell === "function") {
			div = this.options.onRenderCell.call(this, d, div);
		}
		return $(div);
	};
});
/*
 *UI component to render anf handle data of a appdb.views.SimpleTree item.
 *Provides ad-hoc functionality for rendering and event handling.
 */
appdb.views.SimpleTreeRow = appdb.ExtendClass(appdb.View, "appdb.views.SimpleTreeRow", function(o) {
	this.options = {
		parent: o.parent || null,
		root: o.root || null,
		container: $(o.container),
		dataview: o.dataview || null,
		datalevel: o.datalevel || null,
		columns: o.columns || [],
		cells: [],
		rows: [],
		css: o.css || [],
		actions: o.actions || [],
		hideChildren: o.hideChildren,
		canDisplayItem: o.canDisplayItem || function() {
			return true;
		},
		content: o.content || "software"
	};
	this.expand = function(collapseother) {
		collapseother = (typeof collapseother === "boolean") ? collapseother : true;
		var container = $(this.dom).children(".row:first").find("ul.children:first");
		var elem = $(this.dom).children(".row:first").find(".expandhandler:first");
		var self = this;
		if (!$(elem).hasClass("expanded")) {
			$(container).slideDown(100, function() {
				$(elem).removeClass("collapsed").addClass("expanded");
			});
		}
		if (this.options.data.level == 1 && collapseother === true) {
			$(this.getRoot().dom).find(".row").each(function(i, e) {
				if ($(e).children(".cell.id").text() !== self.options.data.id && $(e).children(".cell.expand").children("a.expanded")) {
					$(e).children(".cell.expand").children("a.expanded").trigger("click");
				}
			});
		}
	};
	this.collapse = function() {
		var elem = $(this.dom).children(".row:first").find(".expandhandler:first");
		if (!$(elem).hasClass("collapsed")) {
			var container = $(this.dom).children(".row:first").find("ul.children:first");
			$(container).slideUp(100, function() {
				$(elem).removeClass("expanded").addClass("collapsed");
			});
		}
	};
	this.toggle = function() {
		var children = this.getChildren();
		if (children && children.length > 0) {
			var elem = $(this.dom).children(".row:first").find(".expandhandler:first");
			if ($(elem).hasClass("collapsed")) {
				this.expand();
			} else {
				this.collapse();
			}
		}
	};
	this.showChildrenOnly = function() {
		$(this.dom).children(".row:first").children(".value").hide();
		this.expand();
	};
	this.getAction = function(name) {
		var act = null;
		$.each(this.options.actions, function(i, e) {
			if (act == null && e.name == name) {
				act = e;
			}
		});
		if (act) {
			return act;
		} else {
			return {exec: function() {
				}};
		}
	};
	this.getRoot = function() {
		return this.options.root;
	};
	this.getDataView = function() {
		return this.options.dataview;
	};
	this.getParent = function() {
		return this.options.parent;
	};
	this.getChildren = function() {
		return this.options.rows;
	};
	this.renderChildren = function(d, el) {
		var self = this;
		if ($.isArray(d.children) && d.children.length > 0) {
			var ul = $(document.createElement("ul")).addClass("children");
			$.each(d.children, function(i, e) {
				if (self.options.canDisplayItem.call(self, e) == false)
					return;
				var li = $(document.createElement("li"));
				$(ul).append(li);
				var row = new appdb.views.SimpleTreeRow({
					columns: self.options.columns,
					dataview: e,
					datalevel: self.options.datalevel || null,
					container: $(li),
					parent: self,
					root: self.options.root,
					css: self.options.css,
					actions: self.options.actions,
					content: self.options.content,
					hideChildren: self.options.hideChildren
				});
				row.render(e);
				self.options.rows.push(row);
			});
			//check if children should be auto collapsed
			if (this.options.hideChildren === true) {
				this.collapse();
				$(ul).hide();
			}
			return ($(ul).children("li").length > 0) ? ul : null;
		}
		return null;
	};
	this.render = function(d) {
		d = d || this.options.data || {};
		this.options.data = d;
		if (this.options.canDisplayItem.call(this, this.options.data) == false) {
			return null;
		}
		var div = $(document.createElement("div")).addClass("row");
		$(this.dom).append(div);

		//Add child rows
		var children = this.renderChildren(d, div);
		if (children !== null) {
			$(div).append(children);
		}
		$.each(this.options.cells, function(i, e) {
			$(div).prepend(e.render(d, div));
		});

		//Add user defined css classes
		var css = (typeof this.options.css === "function") ? this.options.css.call(this, d, div) : this.options.css;
		css = $.isArray(css) ? css : [css];
		$.each(css, function(i, e) {
			$(div).addClass(e);
		});

		//render tree lines
		if (this.isRoot() == false) {
			$(div).prepend($(document.createElement("div")).addClass("cell treeline").append($(document.createElement("div")).addClass("treeline")));
		}

		//add expand row link
		if (children && $(children).children("li").length > 0) {
			var expandcell = $(document.createElement("div")).addClass("cell expand");
			var a = $(document.createElement("a")).addClass("expandhandler").addClass("collapsed");
			$(a).append("<span class='expand'>+</span><span class='collapse'>-</span>").addClass("collapsed");
			$(a).unbind("click").bind("click", (function(self, data) {
				return function() {
					self.toggle();
				};
			})(this, d));
			$(expandcell).append(a);
			$(div).prepend(expandcell);
		}

		return div;
	};
	this.isRoot = function() {
		if (this.options.data && this.options.data.parentid) {
			return false;
		}
		return true;
	};
	this.initColumns = function() {
		this.options.columns = this.options.columns || [];
		var cols = [];
		var self = this;
		$.each(this.options.columns, function(i, e) {
			var d = $.extend(e, {type: "text", parent: self});
			var c = new appdb.views.SimpleTreeCell(d);
			cols.push(c);
		});
		this.options.cells = cols;
	};
	this.initActions = function() {
		var res = [];
		$.each(this.options.actions, (function(self) {
			return  function(i, e) {
				var act = $.extend({}, e);
				act.exec = (function(context, action) {
					return function() {
						action.onExec.apply(context, arguments);
					};
				})(self, self.options.actions[i]);
				res.push(act);
			};
		})(this));
		this.options.actions = res;
	};
	this._init = function() {
		this.parent = this.options.parent;
		this.dom = $(this.options.container);
		this.initColumns();
		this.initActions();
	};
	this._init();
});

/*
 *UI component used or extended in order to display a 
 *tree view of appdb.views.TreeDataView objects
 */
appdb.views.SimpleTree = appdb.ExtendClass(appdb.View, "appdb.views.SimpleTree", function(o) {
	o = o || {};
	this.options = $.extend({
		container: o.container || null,
		parent: o.parent || null,
		datatype: o.datatype || appdb.views.TreeDataView,
		datalevel: o.datalevel || null,
		dataview: null,
		data: o.data || null,
		columns: o.columns || [],
		rows: [],
		css: o.css || [],
		rowCss: o.rowCss || [],
		rowActions: o.rowActions || [],
		hideChildren: (typeof o.hideChildren === "boolean") ? o.hideChildren : true,
		hideSingleRoot: (typeof o.hideSingleRoot === "boolean") ? o.hideSingleRoot : false,
		canDisplayItem: o.canDisplayItem || function() {
			return true;
		},
		content: o.content || "software"
	}, o || {});
	this.getDataView = function() {
		return this.options.dataview;
	};
	this.onRenderRow = function(elem, d) {
		var row = new appdb.views.SimpleTreeRow({
			columns: this.options.columns,
			dataview: d,
			datalevel: this.options.datalevel || null,
			container: $(elem),
			parent: this,
			root: this,
			css: this.options.rowCss,
			actions: this.options.rowActions,
			hideChildren: this.options.hideChildren,
			canDisplayItem: this.options.canDisplayItem,
			content: this.options.content
		});

		row.render(d);
		this.options.rows.push(row);
	};
	this.render = function(d, transform) {
		transform = (typeof transform === "boolean") ? transform : true;
		$(this.dom).addClass("rendering");
		d = d || this.options.data || [];
		this.options.data = d;
		var self = this;
		var view = this.options.data;
		if (transform === true) {
			this.getDataView().load(this.options.data, 2);
			view = this.getDataView().transformData();
		}
		$.each(view, function(i, e) {
			if (typeof self.options.canDisplayItem !== "function" || self.options.canDisplayItem.call(self, e) == true) {
				var li = $(document.createElement("li"));
				self.onRenderRow(li, e);
				$(self.dom).append(li);
			}
		});
		if (this.options.rows.length == 1 && this.options.hideSingleRoot) {
			this.options.rows[0].showChildrenOnly();
		}
		$(this.dom).removeClass("rendering");
	};
});

/*
 *Extension of appdb.views.SimpleTree class.
 *Loads and renders a tree view of categories.
 */
appdb.views.CategoriesTreeView = appdb.ExtendClass(appdb.views.SimpleTree, "appdb.views.CategoriesTreeView", function(o) {
	this.options = $.extend(this.options, {
		content: o.content || "software",
		datatype: o.datatype || appdb.views.CategoriesDataView,
		rowActions: [],
		columns: o.columns || [
			{name: "id"},
			{name: "value", onDataItem: function(d) {
					return (d.val) ? d.val() : d;
				}, dataType: "link", onClick: function(d, elem) {
					this.getRow().expand();
				}, onRenderCell: function(d, elem) {
					var r = ((this.getRow() || {}).options || {} ).content || "software";					
					$(elem).attr("onClick", "appdb.views.Main.showCategories(" + d.id + ",{isBaseQuery : true, mainTitle: '" + d.value + "',filterDisplay : 'Search in " + d.value + "...', content: '" + r + "'});");
					$(elem).attr("data-href", "/browse/software/category/" + d.id);
					return elem;
				}}
		],
		css: function(d, elem) {
			var css = [];
			if (d.level) {
				css.push("level" + d.level);
			}
			return css;
		},
		rowCss: function(d, elem) {
			var css = [];
			if (d.level) {
				css.push("level" + d.level);
			}
			return css;
		},
		canDisplayItem: o.canDisplayItem || null
	});

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.dataview = new this.options.datatype({datalevel: o.datalevel || 2});
	};
	this._init();
});
appdb.views.VApplianceCategoriesTreeView = appdb.ExtendClass(appdb.views.SimpleTree, "appdb.views.VApplianceCategoriesTreeView", function(o) {
	this.options = $.extend(this.options, {
		datatype: o.datatype || appdb.views.CategoriesDataView,
		rowActions: [],
		columns: o.columns || [
			{name: "id"},
			{name: "value", onDataItem: function(d) {
					return (d.val) ? d.val() : d;
				}, dataType: "link", onClick: function(d, elem) {
					this.getRow().expand();
				}, onRenderCell: function(d, elem) {
					var r = ((this.getRow() || {}).options || {} ).content || "vappliance";
					$(elem).attr("onClick", "appdb.views.Main.showVAppCategories(" + d.id + ",{isBaseQuery : true, mainTitle: '" + d.value + "',filterDisplay : 'Search in " + d.value + "...', content: '"+r+"'});");
					$(elem).attr("data-href", "/browse/cloud/vappliances/category/" + d.id);
					return elem;
				}}
		],
		css: function(d, elem) {
			var css = [];
			if (d.level) {
				css.push("level" + d.level);
			}
			return css;
		},
		rowCss: function(d, elem) {
			var css = [];
			if (d.level) {
				css.push("level" + d.level);
			}
			return css;
		},
		canDisplayItem: o.canDisplayItem || null
	});

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.dataview = new this.options.datatype({datalevel: o.datalevel || 2});
	};
	this._init();
});
/*
 *Extension of appdb.views.SimpleTree class.
 *Loads and renders a tree view of disciplines.
 */
appdb.views.DisciplinesTreeView = appdb.ExtendClass(appdb.views.SimpleTree, "appdb.views.DisciplinesTreeView", function(o) {
	this.options = $.extend(this.options, {
		datatype: o.datatype || appdb.views.DisciplinesDataView,
		rowActions: [],
		columns: o.columns || [
			{name: "id"},
			{name: "value", onDataItem: function(d) {
					return (d.val) ? d.val() : d;
				}, dataType: "link", onClick: function(d, elem) {
					this.getRow().expand();
				}, onRenderCell: function(d, elem) {
					$(elem).attr("onClick", "appdb.views.Main.showDiscipline(" + d.id + ",{isBaseQuery : true, mainTitle: '" + d.value + "',filterDisplay : 'Search in " + d.value + "...'});");
					return elem;
				}}
		],
		css: function(d, elem) {
			var css = [];
			if (d.level) {
				css.push("level" + d.level);
			}
			return css;
		},
		rowCss: function(d, elem) {
			var css = [];
			if (d.level) {
				css.push("level" + d.level);
			}
			return css;
		},
		canDisplayItem: o.canDisplayItem || null
	});

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.dataview = new this.options.datatype({datalevel: o.datalevel || 2});
	};
	this._init();
});
/*
 *Used by appdb.views.SoftwareCategoryList to render each category entry
 */
appdb.views.SoftwareCategoryEntry = appdb.ExtendClass(appdb.View, "appdb.views.SoftwareCategoryEntry", function(o) {
	this.options = {
		parent: o.parent || null,
		container: $(o.container),
		data: o.data || null,
		content: o.content || "software"
	};
	this.reset = function() {
		$(this.dom).empty();
		if (this.subviews.tree) {
			this.subviews.tree.reset();
			this.subviews.tree = null;
		}
	};
	this.renderTree = function() {
		if (this.subviews.tree) {
			this.subviews.tree.reset();
			this.subviews.tree = null;
		}
		$(this.dom).find(".appcategorychildren").append("<ul class='list'></ul>");
		this.subviews.tree = new appdb.views.CategoriesTreeView({
			container: $(this.dom).find(".appcategorychildren > ul.list"),
			hideSingleRoot: true,
			hideChildren: false,
			datalevel: 5,
			content: this.options.content,
			parent: this
		});
		this.subviews.tree.render([this.options.data], false);
	};
	this.onUserAction = function(d) {
		var isvapp = (d && $.trim(d.id) === "34") ? true : false;
		if (isvapp === false) {
			isvapp = (d && $.trim(d.parentid) === "34") ? true : false;
		}
		appdb.views.Main.showApplications({flt: '+=category.id:' + d.id}, {isBaseQuery: true, mainTitle: d.value, filterDisplay: 'Search in ' + d.value + '...', content: this.options.content, href: '/browse/' + ((isvapp) ? 'cloud/vappliances' : 'software') + '/category/' + d.id});
	};
	this.render = function(d) {
		this.reset();
		d = d || this.options.data || {};
		this.options.data = d;
		var div = $(document.createElement("div")).addClass("appcategoryentry").addClass("treedataentry");
		var children = $(document.createElement("div")).addClass("appcategorychildren").addClass("treedatachildren");
		var arrow = $(document.createElement("span")).addClass("arrow").html(appdb.views.SoftwareCategoryEntry.arrow);

		var a = $(document.createElement("a")).attr("title", "Browse items in this category").attr("href", "#").addClass("value");
		$(a).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				var data = self.options.data;
				self.onUserAction(data);
				return false;
			};
		})(this)).html("<span class='datavalue'>" + d.value + "</span>");

		$(div).append(a);
		if (this.options.data.children && this.options.data.children.length > 0) {
			$(a).append(arrow);
			$(div).append(children);
			$(children).unbind("mouseover").bind("mouseover", function() {
				$(this).parent().addClass("hover");
			}).unbind("mouseleave").bind("mouseleave", function() {
				$(this).parent().removeClass("hover");
			});
			$(div).addClass("haschildren");
		}
		if (d.data && d.data.primary == "true") {
			$(div).addClass("primary");
		}
		$(div).data("itemdata", d.data);
		$(this.dom).append(div);
		if (this.options.data.children && this.options.data.children.length > 0) {
			this.renderTree();
			if ($(this.dom).find(".treedatachildren > ul.list").children("li").length > 0) {
				$(this.dom).find(".treedatachildren > ul.list").prepend("<li class='treedatachildrenwidthfix'>" + $(a).clone().html() + "</li>");
			}
		}
		this.postRender();
	};
	this.postRender = function(){};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
	};
}, {
	arrow: "▼"
});
/*
 *Renders a set list of categories. It uses a appdb.views.CategoriesDataView object 
 *in order to display posible hierarchy of categories. It can initialize a 
 *appdb.views.SoftwareCategoriesEditor object for editing this set of categories.
 *It also provides functions for setting up the edited values for the post data 
 *of software editing (hasChanges, isValid, setupForm).
 */
appdb.views.SoftwareCategoryList = appdb.ExtendClass(appdb.View, "appdb.views.SoftwareCategoryList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			list: $(document.createElement("ul")).addClass("appcategorylist").addClass("treedatalist")
		},
		data: o.data || [],
		initialData: null,
		initialPrimaryId: -1,
		transformedData: [],
		entityType: $.trim(o.entityType),
		dataFilter: o.dataFilter,
		dataview: new appdb.views.CategoriesDataView(),
		editor: null,
		editorType: o.editorType || appdb.views.SoftwareCategoriesEditor,
		permissions: o.permissions || appdb.utils.Privileges([]),
		canEdit: o.canEdit || false,
		content: o.content || "software",
		maxViewLength: (o.maxViewLength << 0)
	};
	this.hasChanges = function() {
		var hasChanges = false;
		//check if added items
		$.each(this.options.data, (function(self) {
			return function(i, e) {
				if (hasChanges)
					return;
				if (!self.options.initialData[e.id]) {
					hasChanges = true;
				} else if (e.primary == "true" && e.id !== self.options.initialPrimaryId) { //check if primary is changed
					hasChanges = true;
				}
			};
		})(this));
		if (!hasChanges) {
			//check if removed items
			var initlen = 0;
			for (var i in this.options.initialData) {
				if (this.options.initialData.hasOwnProperty(i)) {
					initlen += 1;
				}
			}
			if (initlen !== this.options.data.length) {
				hasChanges = true;
			}
		}

		return hasChanges;
	};
	this.isValid = function() {
		if (!this.options.data || this.options.data.length == 0) {
			return "At least one category must be set";
		}
		var prim = this.getPrimaryIndex();
		if (prim == -1) {
			return "No category is set as primary";
		}
		return true;
	};
	this.renderInvalidData = function(text) {
		$(this.dom).find(".invalidmessage").remove();
		if (text === true)
			return;
		var invalid = $(document.createElement("div")).addClass("invalidmessage");
		var content = $(document.createElement("div")).addClass("content");
		var span = $(document.createElement("span"));
		var img = $(document.createElement("img")).attr("src", "/images/repository/warning.png").attr("alt", "");
		$(span).html(text || "Invalid data");

		$(content).append(img).append(span);
		$(invalid).append(content);
		$(this.dom).find(".editcategorylist").after(invalid);
	};
	this.getPrimaryIndex = function() {
		var primary = -1;
		$.each(this.options.data, function(i, e) {
			if (primary === -1 && e.primary === "true") {
				primary = i;
			}
		});
		return primary;
	};
	this.setupInitialData = function(d) {
		d = d || this.options.data;
		if (this.options.initialData)
			return;
		this.options.initialData = {};
		$.each(this.options.data, (function(self) {
			return function(i, e) {
				if (e && e.id) {
					self.options.initialData[e.id] = self.options.initialData[e.id] || $.extend({}, e);
					if (e.primary == "true") {
						self.options.initialPrimaryId = e.id;
					}
				}
			};
		})(this));
	};
	this.getPrimaryId = function(){
		var pindex = this.getPrimaryIndex();
		if( pindex > -1 ){
			return (this.options.data.slice(0)[this.getPrimaryIndex()]).id;
		}
		return -1;
	};
	this.getIds = function(){
		
	};
	this.getSetupFormData = function(primaryindex){
		primaryindex = primaryindex || this.getPrimaryIndex() || 0;
		var d = this.options.data.slice(0);
		d.splice( primaryindex, 1);
		return d;
	};
	this.setupForm = function(frm) {
		if ($(frm).length === 0) {
			return false;
		}
		var i, len, item, inp, views;
		$("input[name^='categoryID']").remove();
		inp = document.createElement("input");
		$(inp).attr("name", "categoryID0").attr("type", "hidden").attr("value", this.getPrimaryId());
		$(frm).append(inp);
		views = this.getSetupFormData();
		len = views.length;
		for (i = 0; i < len; i += 1) {
			item = views[i];
			inp = document.createElement("input");
			$(inp).attr("name", "categoryID" + (i + 1)).attr("type", "hidden").attr("value", item.id);
			$(frm).append(inp);
		}
		return true;
	};
	this.loadTransformedData = function(){
		var ids = $.map(this.options.data, function(e) {
			return e.id;
		});
		this.options.subDataView = this.options.dataview.getSubDataViewByIds(ids);
		this.options.transformedData = this.options.subDataView.transformData();
		return this.getTransformedData();
	};
	this.getTransformedData = function(){
		return this.options.transformedData;
	};
	this.reset = function() {
		$.each(this.subviews, function(i, e) {
			e.unsubscribeAll();
			e.reset();
			e = null;
		});
		this.subviews = [];
		if (this.options.editor) {
			this.options.editor.unsubscribeAll(this);
			this.options.editor.reset();
		}
		this.unsubscribeAll();
		this.initContainer();
	};
	this.addSeperator = function() {
		var li = $(document.createElement("li")).addClass("seperator");
		var span = $(document.createElement("span")).html(appdb.views.SoftwareCategoryList.seperator);
		$(li).append(span);
		return li;
	};
	this.addItem = function(d) {
		var li = $(document.createElement("li"));
		var entry = new appdb.views.SoftwareCategoryEntry({
			container: li,
			parent: this,
			data: d,
			content: this.options.content
		});
		this.subviews.push(entry);
		return li;
	};
	this.showEditor = function() {
		if (!this.options.editor) {
			editor = new this.options.editorType({
				parent: self,
				data: $.extend(this.options.data, {}),
				dataFilter: this.options.dataFilter,
				entityType: this.options.entityType
			});
			editor.subscribe({event: "result", callback: function(v) {
					this.onEditorResult(v);
				}, caller: this});
			editor.subscribe({event: "validation", callback: function(v) {
					this.publish({event: "validation", value: v});
				}, caller: this});
		}
		editor.render($.extend(this.options.data, {}));

	};
	this.renderEdit = function() {
		$(this.dom).find(".editcategorylist").remove();
		if (this.options.canEdit == false)
			return;

		var editaction = $(document.createElement("a")).addClass("editcategorylist").addClass("editbutton").attr("title", "Add or modify " + this.options.entityType + " categories").html("<span>add/modify</span>");
		$(editaction).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.showEditor();
				return false;
			};
		})(this));
		$(this.dom).prepend(editaction);
	};
	this.onEditorResult = function(v) {
		this.render(v);
	};
	this.renderViewMore = function() {
		$(this.options.dom.list).children(".toggler").remove();
		if (!this.options.maxViewLength || this.options.maxViewLength === 0) {
			return;
		}
		var list = $(this.options.dom.list);
		var lis = $(list).children("li").not(".seperator");
		if ($(lis).length <= this.options.maxViewLength) {
			return;
		}
		$(lis).each((function(max) {
			return function(i, e) {
				if (i > max) {
					$(e).addClass("extra");
					var n = $(e).next();
					if ($(n).hasClass("seperator")) {
						$(n).addClass("extra");
					}
				} else if (i === max) {
					var n = $(e).next();
					if ($(n).hasClass("seperator")) {
						$(n).addClass("extra");
					}
				}
			};
		})(this.options.maxViewLength));
		var more = $("<li class='toggler'><span class='more' title='View all available entries'>..more</span><span class='less' title='View less entries'>...less</span></li>");
		$(more).unbind("click").bind("click", function(ev) {
			ev.preventDefault();
			$(this).parent().toggleClass("expand");
			return false;
		});
		$(this.options.dom.list).append(more);
	};
	this.render = function(d) {
		this.reset();
		d = d || this.options.data || [];
		d = $.isArray(d) ? d : [d];
		var self = this;
		this.options.data = d || this.options.data;
		this.setupInitialData();
		this.options.dataview.load(appdb.model.ApplicationCategories.getLocalData());
		var primaryid = this.getPrimaryId();
		var ids = $.map(this.options.data, function(e) {
			return e.id;
		});
		this.options.subDataView = this.options.dataview.getSubDataViewByIds(ids);
		this.options.transformedData = this.loadTransformedData();
		$.each(this.options.transformedData, function(i, e) {
			if (e.id == primaryid) {
				e.data = e.data || {id: e.id, val: (function(v) {
						return function() {
							return v;
						};
					})(e.value), primary: "true"};
				e.data.primary = "true";
			} else if (e.data.primary) {
				delete e.data.primary;
			}
			$(self.options.dom.list).append(self.addItem(e));
			$(self.options.dom.list).append(self.addSeperator());
		});
		$.each(this.subviews, function(i, e) {
			e.render();
		});
		$(self.options.dom.list).find("li.seperator:last").remove();
		this.renderEdit();
		var isvalid = this.isValid();
		if (isvalid !== true) {
			this.renderInvalidData(isvalid);
		}
		this.renderViewMore();
	};
	this.initContainer = function() {
		$(this.options.dom.list).empty();
		this.options.dom.list = $(document.createElement("ul")).addClass("appcategorylist").addClass("treedatalist");
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.list);
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
}, {
	seperator: "•"
});
appdb.views.VApplianceCategoryList = appdb.ExtendClass(appdb.views.SoftwareCategoryList, "appdb.views.VApplianceCategoryList", function(o) {
	this.options.rootcatid = "34";
	this._initvapp = function() {
		$(this.dom).addClass("isvappliance");
		this.options.editorType = appdb.views.VapplianceCategoriesEditor;
	};
	this.getPrimaryId = function(){
		return this.options.rootcatid;
	};
	this.getSetupFormData = function(primaryindex){
		primaryindex = primaryindex || this.getPrimaryIndex() || 0;
		var d = this.options.data.slice(0);
		d.splice( primaryindex, 1);
		return d;
	};
	this.getTransformedData = function(){
		if (this.options.transformedData.length > 0) {
			this.options.transformedData[0].children = this.options.transformedData[0].children || [];
			this.options.transformedData[0].children = $.isArray(this.options.transformedData[0].children) ? this.options.transformedData[0].children : [this.options.transformedData[0].children];
			this.options.transformedData = this.options.transformedData[0].children;
		}
		return this.options.transformedData;
	};
	this.onEditorResult = function(v) {
		v = v || [];
		v = $.isArray(v) ? v : [v];
		var res = $.grep(v, (function(rootcatid){
			return function(e) {
				return ($.trim(e.parentid) === rootcatid);
			};
		})(this.options.rootcatid));
		var primary = $.grep(v, (function(rootcatid){
			return function(e) {
				return ($.trim(e.id) === rootcatid);};
		})(this.options.rootcatid));
		if (res.length === 0) {
			this.render([]);
			return;
		}
		if (primary.length === 0) {
			this.render([]);
			return;
		}
		primary[0].primary = "true";
		res = res.concat(primary);
		this.render(res);
	};
	this._initvapp();
});
appdb.views.SWApplianceCategoryList = appdb.ExtendClass(appdb.views.VApplianceCategoryList, "appdb.views.SWApplianceCategoryList", function(o) {
	this.options.rootcat = appdb.utils.entity.getCategoryByName("software appliances");
	this.options.rootcatid = this.options.rootcat.id;
	this.options.content = 'swappliance';
	this.getPrimaryId = function(){
		return this.options.rootcat.id;
	};
	this.getSetupFormData = function(primaryindex){
		primaryindex = primaryindex || this.getPrimaryIndex() || 0;
		var d = this.options.data.slice(0);
		d.splice( primaryindex, 1);
		return d;
	};
	this.onEditorResult = function(v){
		v = v || [];
		v = $.isArray(v) ? v : [v];
		var res = $.grep(v, (function(rootcatid){
			return function(e) {
				return ($.trim(e.parentid) === rootcatid);
			};
		})(this.options.rootcatid));
		var primary = $.grep(v, (function(rootcatid){
			return function(e) {
				return ($.trim(e.id) === rootcatid);};
		})(this.options.rootcatid));
		if (res.length === 0) {
			this.render([]);
			return;
		}
		if (primary.length === 0) {
			this.render([]);
			return;
		}
		primary[0].primary = "true";
		this.render(v);
	};
	this.getTransformedData = function(){
		if (this.options.transformedData.length > 0) {
			this.options.transformedData[0].children = this.options.transformedData[0].children || [];
			this.options.transformedData[0].children = $.isArray(this.options.transformedData[0].children) ? this.options.transformedData[0].children : [this.options.transformedData[0].children];
			this.options.transformedData = this.options.transformedData[0].children;
		}
		return this.options.transformedData;
	};
	this._initvapp = function() {
		$(this.dom).addClass("isswappliacne").addClass("isvappliance");
		this.options.editorType = appdb.views.SwapplianceCategoriesEditor;
	};
	this._initvapp();
});
/*
 *Used by appdb.views.SoftwareDisciplineList to render each discipline entry
 */
appdb.views.SoftwareDisciplineEntry = appdb.ExtendClass(appdb.View, "appdb.views.SoftwareDisciplineEntry", function(o) {
	this.options = {
		parent: o.parent || null,
		container: $(o.container),
		data: o.data || null,
		dataview: o.dataview || null,
		content: o.content || "software"
	};
	this.reset = function() {
		$(this.dom).empty();
		if (this.subviews.tree) {
			this.subviews.tree.reset();
			this.subviews.tree = null;
		}
	};
	this.onUserAction = function(d) {
		appdb.views.Main.showApplications({flt: '+=discipline.id:' + d.id}, {isBaseQuery: false, content: this.options.content});
	};
	this.renderTree = function() {
		var dom = $(this.dom).find(".appdisciplinechildren");
		var ul = $(document.createElement("ul")).addClass("list");
		$(dom).empty();
		$(dom).append(ul);
		var parent = this.options.dataview.getParent();
		while (parent && parent.getParent()) {
			var sep = $(document.createElement("li"));
			$(sep).append("<span>&#9650;</span>");
			var li = $(document.createElement("li"));
			var data = parent.getData();
			if ($.isArray(data) && data.length > 0)
				data = data[0];
			var a = $(document.createElement("a")).attr("href", "#").unbind("click").bind("click", (function(self, d) {
				return function(ev) {
					ev.preventDefault();
					self.onUserAction(d);
					return false;
				};
			})(this, data));
			$(a).text(data.val());
			$(li).append(a);
			$(ul).append("<li><span>&#9650;</span></li>").append(li);
			parent = parent.getParent();
		}
		if ($(dom).children("ul").children("li").length === 0) {
			$(dom).children("ul").remove();
		}
	};
	this.postRender = function(){
		
	};
	this.render = function(dv) {
		this.reset();
		dv = dv || this.options.dataview;
		this.options.dataview = dv;
		var d = this.options.dataview.getData();
		this.options.data = d || this.options.data;

		var div = $(document.createElement("div")).addClass("appdisciplineentry").addClass("treedataentry");
		var children = $(document.createElement("div")).addClass("appdisciplinechildren").addClass("treedatachildren");
		var arrow = $(document.createElement("span")).addClass("arrow").html(appdb.views.SoftwareDisciplineEntry.arrow);

		var a = $(document.createElement("a")).attr("title", "Browse software in this discipline").attr("href", "#").addClass("value");
		$(a).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				var data = self.options.data;
				self.onUserAction(data);
				return false;
			};
		})(this)).html("<span class='datavalue'>" + d.val() + "</span>");

		$(div).append(a);
		if (this.options.dataview.getParent() && this.options.dataview.getParent().getParent()) {
			$(a).append(arrow);
			$(div).append(children);
			$(children).unbind("mouseover").bind("mouseover", function() {
				$(this).parent().addClass("hover");
			}).unbind("mouseleave").bind("mouseleave", function() {
				$(this).parent().removeClass("hover");
			});
			$(div).addClass("haschildren");
		}
		$(div).data("itemdata", d.data);
		$(this.dom).append(div);
		
		this.renderTree();
		if ($(this.dom).find(".treedatachildren > ul.list").children("li").length > 0) {
			$(this.dom).find(".treedatachildren > ul.list").prepend("<li class='treedatachildrenwidthfix'>" + $(a).clone().html() + "</li>");
		}
		this.postRender();
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
	};
}, {
	arrow: "▼"
});
/*
 *Renders a set list of disciplines. It uses a appdb.views.DisciplinesDataView object 
 *in order to display posible hierarchy of categories. It can initialize a 
 *appdb.views.SoftwareDisciplinesEditor object for editing this set of disciplines.
 *It also provides functions for setting up the edited values for the post data 
 *of software editing (hasChanges, isValid, setupForm).
 */
appdb.views.SoftwareDisciplineList = appdb.ExtendClass(appdb.View, "appdb.views.SoftwareDisciplineList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			list: $(document.createElement("ul")).addClass("appdisciplinelist").addClass("treedatalist")
		},
		data: o.data || [],
		initialData: null,
		initialPrimaryId: -1,
		transformedData: [],
		dataview: new appdb.views.DisciplinesDataView(),
		editor: null,
		permissions: o.permissions || appdb.utils.Privileges([]),
		canEdit: o.canEdit || false,
		maxDisplayLength: (o.maxDisplayLength << 0),
		content: o.content || "software",
		maxViewLength: (o.maxViewLength << 0)
	};
	this.hasChanges = function() {
		var hasChanges = false;
		//check if added items
		$.each(this.options.data, (function(self) {
			return function(i, e) {
				if (hasChanges)
					return;
				if (!self.options.initialData[e.id]) {
					hasChanges = true;
				}
			};
		})(this));
		if (!hasChanges) {
			//check if removed items
			var initlen = 0;
			for (var i in this.options.initialData) {
				if (this.options.initialData.hasOwnProperty(i)) {
					initlen += 1;
				}
			}
			if (initlen !== this.options.data.length) {
				hasChanges = true;
			}
		}

		return hasChanges;
	};
	this.getEditedData = function(){
		var res = {};
		res[this.getProp("bind")]= this.options.data;
		return res;
	};
	this.getProp = function(name){
		switch($.trim(name).toLowerCase()){
			case "bind":
			case "name":
				return "discipline";
			default: 
				return null;
		}
	};
	this.isValid = function() {
		if (!this.options.data || this.options.data.length == 0) {
			return "At least one discipline must be set";
		}
		return true;
	};
	this.renderInvalidData = function(text) {
		$(this.dom).find(".invalidmessage").remove();
		if (text === true)
			return;
		var invalid = $(document.createElement("div")).addClass("invalidmessage");
		var content = $(document.createElement("div")).addClass("content");
		var span = $(document.createElement("span"));
		var img = $(document.createElement("img")).attr("src", "/images/repository/warning.png").attr("alt", "");
		$(span).html(text || "Invalid data");

		$(content).append(img).append(span);
		$(invalid).append(content);
		$(this.dom).find(".editdisciplinelist").after(invalid);
	};
	this.setupInitialData = function(d) {
		d = d || this.options.data;
		if (this.options.initialData)
			return;
		this.options.initialData = {};
		$.each(this.options.data, (function(self) {
			return function(i, e) {
				if (e && e.id) {
					self.options.initialData[e.id] = self.options.initialData[e.id] || $.extend({}, e);
					if (e.primary == "true") {
						self.options.initialPrimaryId = e.id;
					}
				}
			};
		})(this));
	};
	this.setupForm = function(frm) {
		if ($(frm).length === 0) {
			return false;
		}
		$("input[name^='domainID']").remove();
		var i, len = this.options.data.length, item, inp, views = this.options.data.slice(0);
		len = views.length;
		for (i = 0; i < len; i += 1) {
			item = views[i];
			inp = document.createElement("input");
			$(inp).attr("name", "domainID" + (i + 1)).attr("type", "hidden").attr("value", item.id);
			$(frm).append(inp);
		}
		return true;
	};
	this.reset = function() {
		$.each(this.subviews, function(i, e) {
			e.unsubscribeAll();
			e.reset();
			e = null;
		});
		this.subviews = [];
		if (this.options.editor) {
			this.options.editor.unsubscribeAll(this);
			this.options.editor.reset();
		}
		this.unsubscribeAll();
		this.initContainer();
	};
	this.addSeperator = function() {
		var li = $(document.createElement("li")).addClass("seperator");
		var span = $(document.createElement("span")).html(appdb.views.SoftwareDisciplineList.seperator);
		$(li).append(span);
		return li;
	};
	this.addItem = function(d) {
		var li = $(document.createElement("li"));
		var entry = new appdb.views.SoftwareDisciplineEntry({
			container: li,
			parent: this,
			dataview: d,
			content: this.options.content
		});
		this.subviews.push(entry);
		return li;
	};
	this.showEditor = function() {
		if (!this.options.editor) {
			editor = new appdb.views.SoftwareDisciplinesEditor({
				parent: self,
				data: $.extend(this.options.data, {}),
				maxDisplayLength: (this.options.maxDisplayLength || appdb.views.SoftwareDisciplineList.maxDisplayLength || 40)
			});
			editor.subscribe({event: "result", callback: function(v) {
					this.render(v);
				}, caller: this});
			editor.subscribe({event: "validation", callback: function(v) {
					this.publish({event: "validation", value: v});
				}, caller: this});
			editor.subscribe({event: "close", callback: function(v){
					this.publish({event:"close", value: v});
			},caller: this});
		}
		editor.render($.extend(this.options.data, {}));

	};
	this.renderEdit = function() {
		$(this.dom).find(".editdisciplinelist").remove();
		if (this.options.canEdit == false)
			return;

		var editaction = $(document.createElement("a")).addClass("editdisciplinelist").addClass("editbutton").attr("title", "Add or modify software disciplines").html("<span>add/modify</span>");
		$(editaction).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.showEditor();
				return false;
			};
		})(this));
		$(this.dom).prepend(editaction);
	};

	this.getTerminalItems = function(item) {
		var children = [];
		if (item) {
			children = item.getChildren();
		}
		var res = [];
		$.each(children, (function(self) {
			return function(i, e) {
				var ch = e.getChildren() || [];
				if (ch.length === 0) {
					res.push(e);
				} else {
					res = res.concat(self.getTerminalItems(e || []));
				}
			};
		})(this));
		if (res.length === 0) {
			res.push(item);
		}
		return res;
	};
	this.renderViewMore = function() {
		$(this.options.dom.list).children(".toggler").remove();
		if (!this.options.maxViewLength || this.options.maxViewLength === 0) {
			return;
		}
		var list = $(this.options.dom.list);
		var lis = $(list).children("li").not(".seperator");
		if ($(lis).length <= this.options.maxViewLength) {
			return;
		}
		$(lis).each((function(max) {
			return function(i, e) {
				if (i > max) {
					$(e).addClass("extra");
					var n = $(e).next();
					if ($(n).hasClass("seperator")) {
						$(n).addClass("extra");
					}
				} else if (i === max) {
					var n = $(e).next();
					if ($(n).hasClass("seperator")) {
						$(n).addClass("extra");
					}
				}
			};
		})(this.options.maxViewLength));
		var more = $("<li class='toggler'><span class='more' title='View all available disciplines'>..more</span><span class='less' title='View less disciplines'>...less</span></li>");
		$(more).unbind("click").bind("click", function(ev) {
			ev.preventDefault();
			$(this).parent().toggleClass("expand");
			return false;
		});
		$(this.options.dom.list).append(more);
	};
	this.render = function(d) {
		this.reset();
		d = d || this.options.data || [];
		d = $.isArray(d) ? d : [d];
		var self = this;
		this.options.data = d || this.options.data;
		this.setupInitialData();
		this.options.dataview.load({discipline: appdb.model.StaticList.Disciplines});
		var ids = [];
		$.each(this.options.data, function(i, e) {
			ids.push(e.id);
		});
		this.options.subDataView = this.options.dataview.getSubDataViewByIds(ids);
		this.options.transformedData = this.options.subDataView.transformData();
		var displaydata = [];
		$.each(this.options.subDataView.getChildren(), (function(self) {
			return function(i, e) {
				displaydata = displaydata.concat(self.getTerminalItems(e));
			};
		})(this));

		$.each(displaydata, function(i, e) {
			$(self.options.dom.list).append(self.addItem(e));
			$(self.options.dom.list).append(self.addSeperator());
		});
		$.each(this.subviews, function(i, e) {
			e.render();
		});
		$(self.options.dom.list).find("li.seperator:last").remove();
		this.renderEdit();
		var isvalid = this.isValid();
		if (isvalid !== true) {
			this.renderInvalidData(isvalid);
		}
		this.renderViewMore();
	};
	this.initContainer = function() {
		$(this.options.dom.list).empty();
		this.options.dom.list = $(document.createElement("ul")).addClass("appdisciplinelist").addClass("treedatalist");
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.list);
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
}, {
	seperator: "•",
	maxDisplayLength: 40
});

appdb.views.VODisciplineEntry = appdb.ExtendClass(appdb.views.SoftwareDisciplineEntry, "appdb.views.VODisciplineEntry", function(o) {
	this.renderTree = function() {
		var dom = $(this.dom).find(".appdisciplinechildren");
		var ul = $(document.createElement("ul")).addClass("list");
		$(dom).empty();
		$(dom).append(ul);
		var parent = this.options.dataview.getParent();
		while (parent && parent.getParent()) {
			var sep = $(document.createElement("li"));
			$(sep).append("<span>&#9650;</span>");
			var li = $(document.createElement("li"));
			var data = parent.getData();
			if ($.isArray(data) && data.length > 0)
				data = data[0];
			var a = $(document.createElement("span")).addClass("textvalue").unbind("click").bind("click", (function(self, d) {
				return function(ev) {
					ev.preventDefault();
					return false;
				};
			})(this, data));
			$(a).text(data.val());
			$(li).append(a);
			$(ul).append("<li class='arrow'><span>&#9650;</span></li>").append(li);
			parent = parent.getParent();
		}
		if ($(dom).children("ul").children("li").length === 0) {
			$(dom).children("ul").remove();
		}
	};
	this.render = function(dv) {
		this.reset();
		dv = dv || this.options.dataview;
		this.options.dataview = dv;
		var d = this.options.dataview.getData();
		this.options.data = d || this.options.data;

		var div = $(document.createElement("div")).addClass("appdisciplineentry").addClass("treedataentry");
		var children = $(document.createElement("div")).addClass("appdisciplinechildren").addClass("treedatachildren");
		var arrow = $(document.createElement("span")).addClass("arrow").html(appdb.views.VODisciplineEntry.arrow);

		var span = $(document.createElement("span")).addClass("value");
		$(span).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				return false;
			};
		})(this)).html("<span class='datavalue'>" + d.val() + "</span>");

		$(div).append(span);
		if (this.options.dataview.getParent() && this.options.dataview.getParent().getParent()) {
			$(span).append(arrow);
			$(div).append(children);
			$(children).unbind("mouseover").bind("mouseover", function() {
				$(this).parent().addClass("hover");
			}).unbind("mouseleave").bind("mouseleave", function() {
				$(this).parent().removeClass("hover");
			});
			$(div).addClass("haschildren");
		}
		$(div).data("itemdata", d.data);
		$(this.dom).append(div);
		this.renderTree();
		if ($(this.dom).find(".treedatachildren > ul.list").children("li").length > 0) {
			$(this.dom).find(".treedatachildren > ul.list").prepend("<li class='treedatachildrenwidthfix'>" + $(span).clone().html() + "</li>");
		}
	};
}, {
	seperator: "•",
	maxDisplayLength: 40,
	arrow: "▼"
});
appdb.views.VODisciplineList = appdb.ExtendClass(appdb.views.SoftwareDisciplineList, "appdb.views.VODisciplineList", function(o) {
	this.addItem = function(d) {
		var li = $(document.createElement("li"));
		var entry = new appdb.views.VODisciplineEntry({
			container: li,
			parent: this,
			dataview: d,
			content: this.options.content
		});
		this.subviews.push(entry);
		return li;
	};
});
appdb.views.DatasetDisciplineEntry = appdb.ExtendClass(appdb.views.SoftwareDisciplineEntry, "appdb.views.DatasetDisciplineEntry", function(o) {
	this.onUserAction = function(d) {
		appdb.views.Main.showDatasets({flt: '+=discipline.id:' + d.id}, {isBaseQuery: false, content: this.options.content});
	};
	this.postRender = function(){
		$(this.dom).find("a").attr("title","Browse datasets in this discipline");
	};
	
}, {
	seperator: "•",
	maxDisplayLength: 40,
	arrow: "▼"
});
appdb.views.DatasetDisciplineList = appdb.ExtendClass(appdb.views.SoftwareDisciplineList, "appdb.views.DatasetDisciplineList", function(o) {
	this.addItem = function(d) {
		var li = $(document.createElement("li"));
		var entry = new appdb.views.DatasetDisciplineEntry({
			container: li,
			parent: this,
			dataview: d,
			content: this.options.content
		});
		this.subviews.push(entry);
		return li;
	};
});
//For future use. Not completed yet.
appdb.views.SelectCategoriesTreeView = appdb.ExtendClass(appdb.views.CategoriesTreeView, "appdb.views.SelectCategoriesTreeView", function(o) {
	this.options = $.extend(this.options, {
		datatype: o.datatype || appdb.views.CategoriesDataView,
		rowActions: [],
		columns: o.columns || [
			{name: "select", onRenderCell: function(d, elem) {
					var trichk = new appdb.views.TristateCheckbox({
						container: elem,
						state: false,
						label: "<span/>"
					});

					trichk.render();
					trichk.subscribe({event: "changed", callback: (function(data) {
							return function(s) {
								if (s == 0) {
									this.getRow().getRoot().selected(data);
								} else {
									this.getRow().getRoot().unselected(data);
								}

							};
						})(d), caller: this});
					return $(elem);
				}},
			{name: "id"},
			{name: "value", onDataItem: function(d) {
					return (d.val) ? d.val() : d;
				}, dataType: "link", onClick: function(d, elem) {
					appdb.views.Main.showApplications({flt: '+=category.id:' + d.id}, {isBaseQuery: true, mainTitle: d.value, filterDisplay: 'Search in ' + d.value + '...'});
					this.getRow().expand();
				}}
		],
		css: function(d, elem) {
			var css = [];
			if (d.level) {
				css.push("level" + d.level);
			}
			return css;
		},
		rowCss: function(d, elem) {
			var css = [];
			if (d.level) {
				css.push("level" + d.level);
			}
			return css;
		}
	}, true);
	this.selected = function(data) {
	};
	this.unselected = function(data) {
	};
});
//For future use. Not completed yet.
appdb.views.SoftwareCategoriesTreeEditor = appdb.ExtendClass(appdb.View, "appdb.views.SoftwareCategoriesTreeEditor", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		selectFullPath: typeof (o.useFullPath === "boolean") ? o.selectFullPath : true,
		dom: {
			availablecontainer: $(document.createElement("div")).addClass("availablecontainer"),
			actionscontainer: $(document.createElement("div")).addClass("actionscontainer"),
			selectedcontainer: $(document.createElement("div")).addClass("selectedcontainer")
		}
	};
	this.isValid = function() {
		return true;
	};
	this.reset = function() {
		var sv = ["availableCategories", "selectedCategories"];
		var self = this;
		$.each(sv, function(i, e) {
			if (self.subviews[e]) {
				self.subviews[e].unsubscribeAll();
				self.subviews[e].reset();
				self.subviews[e] = null;
			}
		});
	};
	this.render = function(categories) {
		this.subviews.availableCategories = new appdb.views.SelectCategoriesTreeView({
			parent: this,
			container: $(this.options.dom.availablecontainer).children("ul.list:first"),
			hideSingleRoot: false,
			hideChildren: true,
			datalevel: 50
		});
		this.subviews.availableCategories.render(appdb.model.ApplicationCategories.getLocalData());
	};
	this.initContainer = function() {
		$(this.dom).empty();
		$(this.options.dom.availablecontainer).append($(document.createElement("ul")).addClass("list"));
		$(this.dom).append(this.options.dom.availablecontainer).
				append(this.options.dom.actionscontainer).
				append(this.options.dom.selectedcontainer);

	};
	this._init = function() {
		this.parent = this.options.parent;
		this.dom = $(o.container);
		this.initContainer();
	};
	this._init();
});

/*
 *Loads and transforms data to a appdb.views.TreeDataView object and 
 *renders a display with selection functionality. Mostly made to be 
 *extended by other UI components that provide the display.
 */
appdb.views.TreeListEditor = appdb.ExtendClass(appdb.View, "appdb.views.TreeListEditor", function(o) {
	this.options = {
		parent: o.parent || null,
		container: ((o.container) ? $(o.container) : $(document.createElement("div")).addClass("item")),
		dom: {
			item: $(document.createElement("div")).addClass("itemdata"),
			children: $(document.createElement("div")).addClass("children")
		},
		infoProviderModel: o.infoProviderModel || null, //an appdb.model.* to collect posible info for this item
		name: o.name || null,
		children: [],
		data: o.data || [], //full available data
		dataType: o.dataType || appdb.views.TreeDataView,
		dataLevel: o.dataLevel || 100,
		dataView: o.dataView || null, //organized fully available data
		level: o.level || 0,
		dataStore: [],
		maxDisplayLength: (o.maxDisplayLength << 0) || -1,
		root: null,
		dataFilter: o.dataFilter,
		entityType: $.trim(o.entityType)
	};
	this.isRoot = function() {
		if (this.options.level === 0) {
			return true;
		}
		return false;
	};
	this.getRoot = function() {
		if (this.isRoot()) {
			return this;
		}
		return this.parent.getRoot();
	};
	this.createNewChildType = function(d) {
		var objTreeType = appdb.FindNS(this._type_.getName()) || appdb.views.TreeListEditor;
		var objInst = new objTreeType(d);
		return objInst;
	};
	this.getDataView = function() {
		if (this.isRoot()) {
			return this.loadDataView();
		}
		var root = this.getRoot();
		return (root) ? root.getDataView() : null;
	};
	this.getId = function() {
		if (this.options.data && this.options.data.id) {
			return this.options.data.id;
		}
		return null;
	};
	this.getData = function() {
		return (this.options.data) ? this.options.data : null;
	};
	this.getItemById = function(id) {
		if (!id)
			return null;
		if (this.getId() == id) {
			return this;
		}
		var item = null;
		$.each(this.options.children, function(i, e) {
			if (item == null) {
				if (e.getId() == id) {
					item = e;
				} else {
					item = e.getItemById(id);
				}
			}
		});
		return item;
	};
	this.getInfo = function(infotype) {
		infotype = infotype || "text";
		if ($.inArray(infotype, ["text", "url"]) === -1) {//prefer text
			infotype = "text";
		}

		if (!this.options.infoProviderModel || typeof this.options.infoProviderModel.getLocalDataItemById !== "function") {
			return null;
		}

		var infodata = this.options.infoProviderModel.getLocalDataItemById(this.getId());
		if (!infodata)
			return null;
		infodata = infodata || [];
		infodata = $.isArray(infodata) ? infodata : [infodata];
		if (infodata.length == 0)
			return null;

		infodata = infodata[0];
		infodata = infodata.info || [];
		infodata = $.isArray(infodata) ? infodata : [infodata];
		$.each(infodata, function(i, e) {
			e.type = e.type || "";
		});

		infodata = $.grep(infodata, function(e) {
			return (e.type == infotype);
		});

		if (infodata.length > 0) {
			infodata = infodata[0];
			if (typeof infodata.val === "function") {
				return infodata.val();
			}
		}
		return null;
	};
	this.selectItems = function(items) {
		var root = this.getRoot();

		items = items || [];
		items = $.isArray(items) ? items : [items];
		$.each(items, function(i, e) {
			var item = root.getItemById(e.id);
			if (item !== null) {
				item.setSelected(true);
			}
		});
	};
	this.setSelected = function(selected) {
		selected = (typeof selected === "boolean") ? selected : false;
		this.options.data.selected = selected;
		if (this.isRoot() == false) {
			this.options.data.data.selected = selected;
			if (selected) {
				this.parent.setSelected(true);
			} else {
				//unselect children
				$.each(this.options.children, function(i, e) {
					e.setSelected(false);
				});
				this.parent.renderData(true);
			}
		}
		if (selected) {
			this.publish({event: "selected", value: true});
		} else {
			this.publish({event: "unselected", value: false});
		}
		this.renderData(true);
	};
	this.getSelectedDataList = function() {
		var items = [];
		if (this.isRoot() === false && this.options.data.data.selected == true) {
			items.push($.extend({}, this.options.data.data));
		}
		$.each(this.options.children, function(i, e) {
			items = items.concat(e.getSelectedDataList());
		});
		return items;
	};
	this.addChild = function(d) {
		var item = $(document.createElement("div")).addClass("item");
		$(this.options.dom.children).append(item);
		this.options.children = this.options.children || [];
		var child = this.createNewChildType({
			parent: this,
			container: $(item),
			data: d,
			level: this.options.level + 1,
			maxDisplayLength: this.options.maxDisplayLength
		});
		this.options.children.push(child);
		child.render();
	};
	this.renderData = function(update) {
		update = (typeof update === "boolean") ? update : false;
		var val = $(document.createElement("span"));
		var v = "" + this.options.data.value;
		if (this.options.maxDisplayLength > 3 && this.options.data.value.length > this.options.maxDisplayLength) {
			v = v.substr(0, this.options.maxDisplayLength - 3) + "...";
		}
		$(val).html(v);
		$(this.options.dom.item).empty().append(val);
	};
	this.renderChildren = function(update) {
		update = (typeof update === "boolean") ? update : false;
		if (update) {
			$.each(this.options.children, function(i, e) {
				e.render(true);
			});
		} else {
			$(this.options.dom.children).empty();
			$.each(this.options.data.children, (function(self) {
				return function(i, e) {
					self.addChild(e);
				};
			})(this));
		}
	};
	this.reset = function() {
		$(this.options.dom.item).empty();
		$(this.options.dom.children).empty();
	};
	this.render = function(update) {
		update = (typeof update === "boolean") ? update : false;
		if (!this.isRoot()) {
			this.renderData(update);
		}
		this.renderChildren(update);
		this.postRender(update);
	};
	this.postRender = function() {
		//to be overriden
	};
	this.load = function(d) {
		if (this.isRoot()) {
			if (!this.options.dataView) {
				this.loadDataView(d);
			}
			this.options.data = {children: this.options.dataView};
		}
		this.render();
	};
	this.sortDataView = function() {
		var sorting = function(a, b) {
			if (a.children && a.children.length > 0) {
				a.children.sort(sorting);
			}
			if (b.children && b.children.length > 0) {
				b.children.sort(sorting);
			}
			var aa = (a.order << 0) || 0;
			var bb = (b.order << 0) || 0;
			if (bb < aa)
				return 1;
			if (bb > aa)
				return -1;

			return 0;
		};
		this.options.dataView.sort(sorting);
	};
	this.loadDataView = function(d) {
		if (this.isRoot() && $.isArray(this.options.dataView) === false || this.options.dataView.length == 0) {
			this.options.dataView = new this.options.dataType({
				datalevel: this.options.dataLevel || 2,
				dataFilter: this.options.dataFilter
			});
			this.options.dataView.load();
			if ($.isArray(d)) {
				$.each(d, (function(self) {
					return function(i, e) {
						self.options.dataView.updateChild(e);
					};
				})(this));
			}
			this.options.dataView = this.options.dataView.transformData();
			this.sortDataView();
		}
		return this.options.dataView;
	};
	this.initContainer = function() {
		this.options.dom.item = $(document.createElement("div")).addClass("itemdata").addClass("level" + this.options.level);
		this.options.dom.children = $(document.createElement("div")).addClass("children");
		$(this.dom).append(this.options.dom.item).append(this.options.dom.children);
		$(this.dom).addClass("treelisteditor");
		if (this.options.name !== null) {
			$(this.dom).addClass(this.options.name);
		}
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
});
/*
 *Extends appdb.views.TreeListEditor
 *UI component to load and display a appdb.views.TreeDataView 
 *object. Used by TreeDataView editors.
 */
appdb.views.GenericTreeListSelector = appdb.ExtendClass(appdb.views.TreeListEditor, "appdb.views.GenericTreeListSelector", function(o) {
	this.options = $.extend(this.options, {
		dataType: o.dataType || appdb.views.TreeDataView,
		dataView: o.dataView,
		emptyMessage: o.emptyMessage || "No further discrimination for this item",
		removalMessage: o.removeMessage || "The item contains selected subitems which will be removed as well.",
		maxDisplayLength: (o.maxDisplayLength << 0) || -1
	});
	this.getSelectedChildren = function() {
		return $.grep(this.options.data.children, function(e) {
			return (e.data && e.data.selected == true);
		});
	};
	this.getSelectedSelectorChildren = function() {
		return $.grep(this.options.children, function(e) {
			return (e.options.data && e.options.data.data && e.options.data.data.selected == true);
		});
	};
	this.renderSelection = function(update) {
		update = (typeof update === "boolean") ? update : false;
		var span = (update) ? $(this.options.dom.item).children(".selection").empty().removeClass().addClass("selection") : $(document.createElement("span")).addClass("selection");
		var unselected = $(document.createElement("span")).addClass("unselected").append("<img src='/images/yes_grey.png' alt='' class='popupable' title='Click to unselect this item' />");
		var selected = $(document.createElement("span")).addClass("selected").append("<img src='/images/yes.png' alt='' class='popupable' title='Click to select this item'/>");
		var addnew = $(document.createElement("span")).addClass("addnew").append("<img src='/images/addnew.png' alt='' class='popupable' title='Click to select this item' />");
		var remove = $(document.createElement("span")).addClass("remove").append("<img src='/images/cancelicon.png' alt='' class='popupable' title='Click to unselect this item'/>");
		$(span).append(unselected).append(selected).append(addnew).append(remove);
		if (this.isRoot() == false) {
			if (this.options.data.data.selected == true || this.options.data.selected == true) {
				$(span).removeClass("unselected").addClass("selected");
				$(this.options.dom.item).removeClass("unselected").addClass("selected");
			} else {
				$(span).removeClass("selected").addClass("unselected");
				$(this.options.dom.item).removeClass("selected").addClass("unselected");
			}
		}

		$(addnew).unbind("click.selection").bind("click.selection", (function(self) {
			return function(ev) {
				self.closePopups();
				self.setSelected(true);
				self.getRoot().publish({event: "additem", value: self});
			};
		})(this));

		$(remove).unbind("click.selection").bind("click.selection", (function(self) {
			return function(ev) {
				self.closePopups();
				var sel = self.getSelectedChildren();
				if (sel.length > 0) {
					self.renderRemovalPrompt();
				} else {
					self.setSelected(false);
					self.getRoot().publish({event: "removeitem", value: self});
				}
			};
		})(this));
		this.closePopups();
		$(span).children("span").find("img[title]").each(function(i, e) {
			if ($.trim($(e).attr("title")) !== "") {
				new dijit.Tooltip({
					connectId: $(e)[0],
					label: $(e).attr("title"),
					showDelay: 1
				});
				$(e).removeAttr("title");
			}
		});
		return span;
	};
	this.closePopups = function() {
		$("body").children(".dijitTooltip").remove();
	};
	this.renderValue = function(update) {
		update = (typeof update === "boolean") ? update : false;
		var span = (update) ? $(this.options.dom.item).children(".value").empty() : $(document.createElement("span")).addClass("value");
		var v = "" + this.options.data.value;
		if (this.options.maxDisplayLength > 3 && v.length > this.options.maxDisplayLength) {
			v = v.substr(0, this.options.maxDisplayLength - 3) + "...";
		}
		$(span).html(v);
		return span;
	};
	this.renderCount = function(update) {
		update = (typeof update === "boolean") ? update : false;
		var selectedChildren = this.getSelectedChildren();
		var span = (update) ? $(this.options.dom.item).children(".counting").empty() : $(document.createElement("span"));
		var selected = $(document.createElement("span")).addClass("selected").text(selectedChildren.length);
		var arrow = $(document.createElement("span")).addClass("arrow").text("▼");

		$(span).removeClass().addClass("counting");
		$(span).append(selected);

		if (this.options.level == 2) {
			$(arrow).text("▶");
		}
		if (this.options.data.children.length > 0) {
			$(span).addClass("haschildren").append(arrow);
		}
		return span;
	};
	this.renderEmpty = function() {
		$(this.dom).children(".children").children(".empty").remove();
		if (this.options.data.children.length > 0)
			return;
		var empty = $(document.createElement("div")).addClass("empty");
		var span = $(document.createElement("span")).addClass("emptymessage");
		var message = this.options.emptyMessage || appdb.views.GenericTreeListSelector.emptyMessage || "";
		$(span).html(message);
		$(empty).append(span);
		$(this.dom).children(".children").append(empty);
	};
	this.removeRemovalPrompt = function() {
		$(this.dom).removeClass("prompting");
		$(this.dom).find(".children").find(".prompt.removal").remove();
		$(this.dom).find(".children").find(".prompt.sheet").remove();
		this.closePopups();
	};
	this.renderRemovalPrompt = function() {
		var cntx = (this.options.level == 2) ? this.parent : this;
		$(cntx.dom).find(".children").find(".prompt.removal").remove();
		$(cntx.dom).find(".children").find(".prompt.sheet").remove();
		var prompt = $(document.createElement("div")).addClass("prompt").addClass("removal");
		var content = $(document.createElement("div")).addClass("content");
		var message = $(document.createElement("span")).addClass("message").html(cntx.options.removalMessage);
		var actions = $(document.createElement("div")).addClass("actions");
		var ok = $(document.createElement("a")).addClass("ok").addClass("action").append("<span>remove</span>");
		var cancel = $(document.createElement("a")).addClass("cancel").addClass("action").append("<span>cancel</span>");

		$(actions).append(ok).append(cancel);
		$(content).append(message).append(actions);
		$(prompt).append(content);

		$(cancel).unbind("click").bind("click", (function(self) {
			return function(ev) {
				setTimeout(function() {
					self.removeRemovalPrompt();
				}, 10);
			};
		})(cntx));

		$(ok).unbind("click").bind("click", (function(self, item) {
			return function(ev) {
				setTimeout(function() {
					item.closePopups();
					item.setSelected(false);
					self.getRoot().publish({event: "removeitem", value: item});
					self.removeRemovalPrompt();
				}, 10);
			};
		})(cntx, this));

		$(cntx.dom).find(".children").append(prompt).append("<div class='prompt sheet'></div>");
		$(cntx.dom).addClass("prompting");
		$(cntx.options.dom.item).trigger("click");
	};
	this.renderInfo = function(update) {
		if (update === true)
			return;
		var istext = true;
		var info = this.getInfo("text");
		var infodom = $(document.createElement("a")).addClass("infobutton").attr("href", "").attr("title", "more information").attr("target", "_blank").text("?");
		var infocontainer = $(document.createElement("div")).addClass("info");
		var infocontent = $(document.createElement("div")).addClass("content");

		$(infocontainer).append(infocontent);

		if (!info) {
			istext = false;
			info = this.getInfo("url");
		}
		if (!info) {
			return;
		}

		if (istext) {
			$(infocontent).html(info);
			$(infodom).unbind("click").bind("click", function(ev) {
				ev.preventDefault();
				return false;
			});
			new dijit.Tooltip({
				connectId: $(infodom)[0],
				label: $(infocontainer).html(),
				showDelay: 1
			});
			$(infodom).removeAttr("title");
		} else {
			$(infodom).attr("href", info);
		}

		$(this.dom).append(infodom);
	};
	this.defaultDataRendering = function(update) {
		update = (typeof update === "boolean") ? update : false;
		var selection = this.renderSelection(update);
		var value = this.renderValue(update);
		var counting = null;

		if (this.options.data.children.length > 0) {
			counting = this.renderCount(update);
		}

		if (update === false) {
			$(this.options.dom.item).append(selection).append(value).append(counting);
		}

		$(this.options.dom.item).unbind("click.itemdata").bind("click.itemdata", (function(self) {
			return function(ev) {
				$(this).parent().siblings().removeClass("clicked");
				$(this).parent().siblings().find(".clicked").removeClass("clicked");
				$(this).parent().addClass("clicked");
				self.getRoot().publish({event: "click", value: self});
			};
		})(this));

		if (this.options.data.selected || (this.options.data.data && this.options.data.data.selected)) {
			$(this.options.dom.item).addClass("selected");
		} else {
			$(this.options.dom.item).removeClass("selected");
		}

		if (this.options.data.children.length > 0) {
			$(this.dom).addClass("haschildren");
		} else {
			$(this.dom).removeClass("haschildren");
		}
		this.renderInfo(update);
		$(this.dom).attr("data-id", this.getId());
		this.closePopups();
	};
	this.renderData = function(update) {
		this.defaultDataRendering(update);
	};
	this.postRender = function(update) {
		update = (typeof update === "boolean") ? update : false;
		$(this.dom).addClass("level" + this.options.level);
		this.renderEmpty();
		if (this.isRoot()) {
			var categdom = $(this.dom).children('div.children');
			var parentDivs = $(categdom).find('.item > div.children');
			if (update == false) {
				$(parentDivs).hide().parent().parent().removeClass("prompting");
			}
			this.closePopups();
			$(categdom).find('div.itemdata.level1').unbind("click.accordion").bind("click.accordion", function(ev) {
				ev.preventDefault();
				if ($(this).parent().hasClass("expanded")) {
					return false;
				}
				parentDivs.slideUp(function() {
					$(this).parent().removeClass("expanded").removeClass("expanding").parent().parent().removeClass("prompting");
				});
				$(this).parent().addClass("expanding").children("div.children").clearQueue().slideDown(function() {
					$(this).parent().removeClass("expanding").addClass("expanded");
				});
				return false;
			});
		}
	};
});
/*
 *Extension of appdb.views.GenericTreeListSelector class. It extends
 *functionality to set/unset a primary category item. The parent object 
 *must provide a appdb.views.CategoriesDataView in order to function
 *as expected. 
 */
appdb.views.CategoriesTreeListSelector = appdb.ExtendClass(appdb.views.GenericTreeListSelector, "appdb.views.CategoriesTreeListSelector", function(o) {
	this.options = $.extend(this.options, {
		showPrimary: (typeof (o.showPrimary) === "boolean" ? o.showPrimary : false),
		infoProviderModel: appdb.model.CategoryInfo
	});
	this.canHavePrimary = function() {
		return (this.options.level === 1 && this.parent.options.showPrimary === true);
	};
	this.canSetPrimary = function() {
		return (this.canHavePrimary() === true && this.options.data.data.selected === true);
	};
	this.isPrimary = function() {
		return (this.options.data.data.primary == "true");
	};
	this.getPrimaryItem = function() {
		var root = this.getRoot();
		var children = root.options.children;
		var found = null;
		$.each(children, function(i, e) {
			if (!found && e.isPrimary() && e.options.data.data.selected == true) {
				found = e;
			}
		});
		return found;
	};
	this.setAsPrimary = function(isprimary) {
		isprimary = (typeof isprimary === "boolean") ? isprimary : true;

		if (this.canHavePrimary() == false)
			return;
		if (isprimary === true && this.isPrimary()) {
			return;
		}
		if (isprimary === false && !this.isPrimary()) {
			return;
		}
		if (isprimary === false) {
			delete this.options.data.data.primary;
			if (!this.getPrimaryItem()) {
				var other = this.getRoot().getSelectedSelectorChildren();
				if (other.length > 0) {
					other[0].options.data.data.primary = "true";
					other[0].renderData(true);
				}
			}
		} else {
			var elems = this.getRoot().options.children;
			$.each(elems, function(i, e) {
				delete e.options.data.data.primary;
				e.renderData(true);
			});
			this.options.data.data.primary = "true";
		}
		this.renderData(true);
		this.getRoot().publish({event: "propertychanged", value: {name: "primary", value: isprimary, item: this}});
	};
	this.renderPrimary = function(update) {
		$(this.options.dom.item).children("a.primary").remove();
		if (!this.canHavePrimary())
			return;

		var primary = $(document.createElement("a")).addClass("primary");
		var primaryselection = $(document.createElement("span")).addClass("primaryselection");
		var span = $(document.createElement("span"));

		$(span).append(primaryselection);
		$(primary).append(span);
		$(this.options.dom.item).prepend(primary);

		$(primary).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				if (self.isPrimary() == false) {
					self.setAsPrimary(true);
				}
				return false;
			};
		})(this));
		if (this.isPrimary() == true) {
			$(this.dom).addClass("isprimary");
		} else {
			$(this.dom).removeClass("isprimary");
		}
		this.getRoot().unsubscribeAll(this);

		this.getRoot().subscribe({event: "removeitem", callback: function(v) {
				if (v.options.level == 1) {
					v.setAsPrimary(false);
					delete v.options.data.data.primary;
				}
			}, caller: this});
		this.getRoot().subscribe({event: "additem", callback: function(v) {
				//check if no primary item exists and set this item
				if (v.options.level != 1)
					return;
				if (!this.getPrimaryItem()) {
					v.setAsPrimary(true);
				} else {
					v.setAsPrimary(false);
				}
			}, caller: this});


		new dijit.Tooltip({
			connectId: $(primaryselection)[0],
			label: appdb.views.CategoriesTreeListSelector.primaryMessage,
			showDelay: 1,
			position: ["before"]
		});

	};
	this.renderData = function(update) {
		update = (typeof update === "boolean") ? update : false;
		this.defaultDataRendering(update);
		this.renderPrimary(update);
	};
}, {
	primaryMessage: "<div style='width:250px;text-align:justify;'><div style='padding:5px 0px;' class='actiontype'>Set/unset as primary category.</div><span style='font-style:italic;'>'Primary category'</span> should be the most characteristic category of this software item. <br />It will be used by the system for cases where only one category can be used, such as new feeds, email notifications and other dissemination related activities. <br/><br/><i>It does not restrict the definition of the software in any other way and it can be changed at any later time.</i></div>"
});
/*
 *UI component to select provided appdb.views.TreeDataView 
 *items up to 3 levels of depth. Consists of two appdb.views.GenericTreeListSelector
 *items. The main item selectes the two first levels of data view items and the 
 *second the third level if there is one.
 */
appdb.views.GenericTreeListEditor = appdb.ExtendClass(appdb.View, "appdb.views.GenericTreeListEditor", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dataType: o.dataType || appdb.views.TreeDataView,
		treeListSelectorType: o.treeListSelectorType || appdb.views.GenericTreeListSelector,
		selectFullPath: typeof (o.useFullPath === "boolean") ? o.selectFullPath : true,
		infoProviderModel: o.infoProviderModel || null,
		onDisplayValue: o.onDisplayValue,
		dom: {
			availablecontainer: $(document.createElement("div")).addClass("availablecontainer"),
			actionscontainer: $(document.createElement("div")).addClass("actionscontainer"),
			extraemptycontainer: $(document.createElement("div")).addClass("extraemptycontainer"),
			extracontainer: $(document.createElement("div")).addClass("extracontainer"),
			selectedcontainer: $(document.createElement("div")).addClass("selectedcontainer"),
			emptymessage: $(document.createElement("div")).addClass("emptymessage").append(o.emptymessage || "<span></span>"),
			childrenmessage: $(document.createElement("div")).addClass("message").append(o.childrenmessage || "<span></span>")
		},
		maxDisplayLength: (o.maxDisplayLength << 0),
		entityType: $.trim(o.entityType),
		dataFilter: o.dataFilter
	};
	this.getSelectedDataList = function() {
		if (!this.subviews.availableItems)
			return [];
		return this.subviews.availableItems.getSelectedDataList();
	};
	this.isValid = function() {
		return true;
	};
	this.reset = function() {
		var sv = ["availableItems", "selectedItems"];
		var self = this;
		$.each(sv, function(i, e) {
			if (self.subviews[e]) {
				self.subviews[e].unsubscribeAll();
				self.subviews[e].reset();
				self.subviews[e] = null;
			}
		});
	};
	this.initExtraList = function(d) {
		if (this.subviews.selectedItems) {
			this.subviews.selectedItems.reset();
			$(this.subviews.selectedItems.dom).empty();
			$(this.subviews.selectedItems.dom).removeClass().addClass("selectedcontainer");
			this.subviews.selectedItems = null;
		}
		$(this.options.selectedcontainer).empty();
		$(this.options.dom.extraemptycontainer).find(".emptymessage").addClass("hidden");
		$(this.options.dom.extraemptycontainer).find(".message").addClass("hidden");
		var extradata = (d && d.getData) ? d.getData().children : [];
		if ($.isArray(extradata) && extradata.length > 0 && d.options.level == 2) {
			this.subviews.selectedItems = new this.options.treeListSelectorType({
				container: this.options.dom.selectedcontainer,
				dataView: extradata,
				dataLevel: 1,
				name: "extratreelisteditor",
				dataType: this.options.dataType,
				onDisplayValue: this.options.onDisplayValue,
				maxDisplayLength: this.options.maxDisplayLength
			});
			this.subviews.selectedItems.load();
			this.subviews.selectedItems.render();
			$(this.options.dom.extraemptycontainer).find(".message").removeClass("hidden");
		} else {
			$(this.options.dom.extraemptycontainer).find(".emptymessage").removeClass("hidden");
		}
	};
	this.bindExtraList = function() {
		if (this.subviews.selectedItems) {
			this.subviews.selectedItems.subscribe({event: "additem", callback: function(v) {
					var item = this.subviews.availableItems.getItemById(v.options.data.id);
					if (item) {
						item.setSelected(true);
						item.parent.renderData(true);
					}
					this.publish({event: "additem", value: v});
				}, caller: this});
			this.subviews.selectedItems.subscribe({event: "removeitem", callback: function(v) {
					var item = this.subviews.availableItems.getItemById(v.options.data.id);
					item.setSelected(false);
					item.parent.renderData(true);
					this.publish({event: "removeitem", value: v});
				}, caller: this});
		}
	};
	this.renderExtraList = function(d) {
		this.initExtraList(d);
		this.bindExtraList();
	};
	this.bindMainList = function() {
		this.subviews.availableItems.subscribe({event: "click", callback: function(v) {
				this.renderExtraList(v);
				this.publish({event: "click", value: v});
			}, caller: this});
		this.subviews.availableItems.subscribe({event: "removeitem", callback: function(v) {
				this.renderExtraList(v);
				this.publish({event: "removeitem", value: v});
				this.publish({event: "propertychanged", value: {name: "item", value: false, item: v}});
			}, caller: this});
		this.subviews.availableItems.subscribe({event: "additem", callback: function(v) {
				this.renderExtraList(v);
				this.publish({event: "additem", value: v});
				this.publish({event: "propertychanged", value: {name: "item", value: true, item: v}});
			}, caller: this});
		this.subviews.availableItems.subscribe({event: "propertychanged", callback: function(v) {
				this.publish({event: "propertychanged", value: v});
			}, caller: this});
	};
	this.initMainList = function(d) {
		d = this.setupSelectedItems(d);
		this.subviews.availableItems = new this.options.treeListSelectorType({
			container: this.options.dom.availablecontainer,
			name: "availabletreelisteditor",
			dataLevel: 10,
			dataType: this.options.dataType || appdb.views.TreeDataView,
			onDisplayValue: this.options.onDisplayValue,
			maxDisplayLength: this.options.maxDisplayLength,
			entityType: this.options.entityType,
			dataFilter: this.options.dataFilter
		});
		this.subviews.availableItems.load(d);
	};
	this.renderMainList = function(d) {
		this.initMainList(d);
		this.bindMainList();
		this.renderExtraList();
	};
	this.setupSelectedItems = function(sels) {
		sels = sels || [];
		sels = $.isArray(sels) ? sels : [sels];
		var dv = new this.options.dataType();
		dv.load();
		var ids = [];
		$.each(sels, function(i, e) {
			ids.push(e.id);
		});
		dv = dv.getSubDataViewByIds(ids);
		sels = dv.options.data || sels;
		ids = [];
		$.each(sels, function(i, e) {
			e.selected = true;
			ids.push(e);
		});
		return ids;
	};
	this.render = function(selecteditems) {
		selecteditems = this.setupSelectedItems(selecteditems);
		this.renderMainList(selecteditems);
		this.postRender();
	};
	this.postRender = function() {
		//to be overriden
	};
	this.initContainer = function() {
		$(this.dom).empty();
		$(this.options.dom.extraemptycontainer).append(this.options.dom.emptymessage).append(this.options.dom.childrenmessage);
		$(this.dom).append(this.options.dom.availablecontainer).
				append(this.options.dom.extraemptycontainer).
				append(this.options.dom.selectedcontainer);

	};
	this._init = function() {
		this.parent = this.options.parent;
		this.dom = $(o.container);
		this.initContainer();
	};
	this._init();
});
/*
 *Extension of GenricTreeListEditor class. Replaces the 
 *main list with appdb.views.CategoriesTreeListSelector
 *in order to load primary property functionality.
 *It takes a appdb.views.CategoriesDataView as a data source.
 */
appdb.views.SoftwareCategoriesTreeListEditor = appdb.ExtendClass(appdb.views.GenericTreeListEditor, "appdb.views.SoftwareCategoriesTreeListEditor", function(o) {
	this.options = $.extend(this.options, {
		dataType: appdb.views.CategoriesDataView,
		treeListSelectorType: appdb.views.CategoriesTreeListSelector,
		onDisplayValue: o.onDisplayValue
	});
	this.isValid = function() {
		var sel = this.getSelectedDataList();
		if (sel.length === 0) {
			return "At least one category must be selected";
		}
		var prim = this.getPrimaryCategory();
		if (!prim) {
			return "No category is set as primary";
		}
		return true;
	};
	this.getPrimaryCategory = function() {
		return this.subviews.availableItems.getPrimaryItem();
	};
	this.removeUnselectedPrimaries = function(d) {
		$.each(d, function(i, e) {
			if (d[i].primary == "true" && !d[i].selected) {
				delete d[i].primary;
			}
		});
		return d;
	};
	this.initMainList = function(d) {
		d = this.removeUnselectedPrimaries(d);
		this.subviews.availableItems = new this.options.treeListSelectorType({
			container: this.options.dom.availablecontainer,
			name: "availabletreelisteditor",
			dataLevel: 10,
			dataType: this.options.dataType || appdb.views.TreeDataView,
			showPrimary: true,
			onDisplayValue: this.options.onDisplayValue,
			dataFilter: this.options.dataFilter
		});
		this.subviews.availableItems.load(d);
		var self = this;
		$.each(this.subviews.availableItems.options.data.children, function(i, e) {
			if (self.subviews.availableItems.options.data.children[i].data && e.data.primary == "true" && !self.subviews.availableItems.options.data.children[i].data.selected) {
				delete self.subviews.availableItems.options.data.children[i].data.primary;
			}
		});
	};
});
appdb.views.VApplianceCategoriesTreeListEditor = appdb.ExtendClass(appdb.views.SoftwareCategoriesTreeListEditor, "appdb.views.VApplianceCategoriesTreeListEditor", function(o) {
	this.isValid = function() {
		var sel = this.getSelectedDataList();
		if (sel.length === 0) {
			return "At least one category must be selected";
		} else {
			var found = $.grep(sel, function(e) {
				return ($.trim(e.parentid) === "34");
			});
			if (found.length === 0) {
				return "At least one category must be selected";
			}
		}
		var prim = this.getPrimaryCategory();
		if (!prim) {
			return "No category is set as primary";
		}
		return true;
	};
	this.postRender = function() {
		$(this.dom).find('div.itemdata.level1').unbind("click.accordion").bind("click.accordion", function(ev) {
			ev.preventDefault();
			return false;
		});
	};
});
appdb.views.SWApplianceCategoriesTreeListEditor = appdb.ExtendClass(appdb.views.VApplianceCategoriesTreeListEditor, "appdb.views.SWApplianceCategoriesTreeListEditor", function(o) {
	this.postRender = function() {
		$(this.dom).find('div.itemdata.level1').unbind("click.accordion").bind("click.accordion", function(ev) {
			ev.preventDefault();
			return false;
		});
	};
});
/*
 *Extension of GenricTreeListEditor class. Replaces the 
 *main list with appdb.views.DisciplinesTreeListSelector
 *It takes a appdb.views.DisciplinesDataView as a data source.
 */
appdb.views.SoftwareDisciplinesTreeListEditor = appdb.ExtendClass(appdb.views.GenericTreeListEditor, "appdb.views.SoftwareDisciplinesTreeListEditor", function(o) {
	this.options = $.extend(this.options, {
		dataType: appdb.views.DisciplinesDataView,
		treeListSelectorType: appdb.views.GenericTreeListSelector
	});
	this.isValid = function() {
		var sel = this.getSelectedDataList();
		if (sel.length === 0) {
			return "At least one discipline must be selected";
		}
		return true;
	};
});

/*
 *Generic dialog to edit a software's tree data.
 */
appdb.views.SoftwareGenericTreeDataEditor = new appdb.ExtendClass(appdb.View, "appdb.views.SoftwareGenericTreeDataEditor", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			main: $(document.createElement("div")).addClass("main").addClass("softwaretreedataeditor").addClass("treedataeditor"),
			header: $(document.createElement("div")).addClass("header").html("<span>Add / modify your software:</span>"),
			content: $(document.createElement("div")).addClass("content").attr("id", "swtreedatadivEditor").addClass("swtreedata_editor"),
			link: $(document.createElement("a")).addClass("action").addClass("edittreedata").html("<span>edit</span>"),
			actions: $(document.createElement("div")).addClass("actions"),
			actionApply: $(document.createElement("a")).addClass("action").addClass("apply").addClass("editbutton").html("<span>ok</span>"),
			actionCancel: $(document.createElement("a")).addClass("action").addClass("cancel").addClass("editbutton").html("<span>cancel</span>")
		},
		EditorType: appdb.views.GenericTreeListEditor,
		Editor: null,
		useDialog: (typeof (o.useDialog) === "boolean") ? o.useDialog : true,
		onDisplayValue: o.onDisplayValue,
		data: o.data || [],
		maxDisplayLength: (o.maxDisplayLength << 0) || -1
	};
	this.getSelectedDataItems = function() {
		if (!this.options.Editor)
			return [];
		return this.options.Editor.getSelectedDataList();
	};
	this.setData = function(data) {
		data = data || [];
		this.options.data = data;
	};
	this.getData = function() {
		return this.options.data;
	};
	this.closeDialog = function() {
		if (this.getStatic().Dialog != null) {
			this.getStatic().Dialog.hide();
			this.getStatic().Dialog.destroyRecursive(false);
		}
		this.publish({event: "close", value: this.options.Editor.isValid()});
		
	};
	this.isDialogOpen = function() {
		return (this.getStatic().Dialog != null);
	};
	this.renderInvalidData = function(text) {
		$(this.options.dom.main).find(".invalidmessage").remove();
		if (text === true)
			return;
		var invalid = $(document.createElement("div")).addClass("invalidmessage");
		var content = $(document.createElement("div")).addClass("content");
		var span = $(document.createElement("span"));
		var img = $(document.createElement("img")).attr("src", "/images/repository/warning.png").attr("alt", "");
		$(span).html(text || "Invalid data");

		$(content).append(img).append(span);
		$(invalid).append(content);
		$(this.options.dom.main).find(".actions").prepend(invalid);
	};
	this.renderArrow = function(v) {
		$(this.options.dom.content).find(".arrow-left").remove();
		if (v && v.getRoot() && v.options.level == 2 && v.options.children.length > 0) {
			var y = $(v.dom).offset().top;
			var py = $(v.getRoot().dom).offset().top;
			y = (y - py) + (($(v.dom).height() / 2) - parseInt($(v.getRoot().dom).css("padding-top")));
			var arrow = $("<div class='arrow-left'></div>");
			$(arrow).css({"top": y + "px", "right": "0px"});
			$(this.options.dom.content).append(arrow);
		}

	};
	this.bindEditor = function() {
		if (this.options.Editor) {
			this.options.Editor.subscribe({event: "propertychanged", callback: function(v) {
					var isValid = this.options.Editor.isValid();
					this.renderInvalidData(isValid);
					this.publish({event: "validation", value: isValid});
				}, caller: this});
			this.options.Editor.subscribe({event: "click", callback: function(v) {
					this.renderArrow(v);
				}, caller: this});
		}
	};
	this.initEditor = function() {
		if (this.options.Editor) {
			this.options.Editor.reset();
			this.options.Editor = null;
		}
		this.options.Editor = new this.options.EditorType({
			container: $(this.options.dom.content),
			parent: this,
			showPrimary: true,
			emptymessage: this.getStatic().extralist.emptymessage,
			childrenmessage: this.getStatic().extralist.childrenmessage,
			onDisplayValue: this.options.onDisplayValue,
			maxDisplayLength: this.options.maxDisplayLength,
			entityType: $.trim(o.entityType),
			dataFilter: o.dataFilter
		});
	};
	this.renderEditor = function() {
		this.initEditor();
		this.bindEditor();

	};
	this.showDialog = function() {
		this.getStatic().Dialog = new dijit.Dialog({
			title: this.getStatic().title || "Edit Software",
			style: this.getStatic().style || "width:570px;height: 500px;",
			content: $(this.options.dom.main)[0]
		});
		this.getStatic().Dialog.show();
		this.options.Editor.render(this.getData());
	};
	this.onRender = function() {
	};
	this.render = function(dataitems) {
		if ($.isArray(dataitems)) {
			this.setData(dataitems);
		}
		this.renderEditor();
		this.showDialog();
		var isValid = this.options.Editor.isValid();
		this.renderInvalidData(isValid);
		this.onRender();
	};
	this.initContainer = function() {
		$(this.options.dom.actions).append(this.options.dom.actionApply).append(this.options.dom.actionCancel);
		$(this.options.dom.main).append(this.options.dom.header).append(this.options.dom.content).append(this.options.dom.actions);

		$(this.options.dom.actionCancel).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.closeDialog();
				self.publish({event: "cancel", value: true});
				return false;
			};
		})(this));
		$(this.options.dom.actionApply).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.closeDialog();
				var selcats = self.getSelectedDataItems();
				self.publish({event: "result", value: selcats});
				return false;
			};
		})(this));
	};
	this.getStatic = (function(self) {
		var _static = null;
		return function() {
			if (!_static) {
				_static = appdb.FindNS(self._type_.getName());
			}
			return _static;
		};
	})(this);
	this.init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.initContainer();
	};
	this.init();
}, {
	Dialog: null,
	extralist: {
		emptymessage: '<div class="help"></div>',
		childrenmessage: '<span class="title">Select sub-item:</span>'
	}
});
/*
 *Dialog to edit a software's categories. Handled by
 *appdb.views.SoftwareCategoryList object of each 
 *software entry upon edit. Loads and renders a
 *appdb.views.SoftwareCategoriesTreeListEditor object
 *and procides user's selection and/or changes to its
 *parent object.
 */
appdb.views.SoftwareCategoriesEditor = new appdb.ExtendClass(appdb.views.SoftwareGenericTreeDataEditor, "appdb.views.SoftwareCategoriesEditor", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			main: $(document.createElement("div")).addClass("main").addClass("softwarecategorieseditor").addClass("treedataeditor"),
			header: $(document.createElement("div")).addClass("header").html("<span>Add / modify your software item's categories:</span>"),
			content: $(document.createElement("div")).addClass("content").attr("id", "swcategoriesdivEditor").addClass("swcategories_editor"),
			link: $(document.createElement("a")).addClass("action").addClass("editcategories").html("<span>edit</span>"),
			actions: $(document.createElement("div")).addClass("actions"),
			actionApply: $(document.createElement("a")).addClass("action").addClass("apply").addClass("editbutton").html("<span>ok</span>"),
			actionCancel: $(document.createElement("a")).addClass("action").addClass("cancel").addClass("editbutton").html("<span>cancel</span>")
		},
		EditorType: appdb.views.SoftwareCategoriesTreeListEditor,
		Editor: null,
		entityType: $.trim(o.entityType),
		dataFilter: o.dataFilter,
		useDialog: (typeof (o.useDialog) === "boolean") ? o.useDialog : true,
		onDisplayValue: o.onDisplayValue,
		data: o.data || [],
		maxDisplayLength: (o.maxDisplayLength << 0) || -1
	};
	this.onRender = function() {
		$(this.options.Editor.options.dom.extraemptycontainer).find("a.popup.primary").unbind("mouseover").bind("mouseover", function(ev) {
			if ($(this).hasClass("popupinited") === false) {
				var html = $("<div>" + appdb.views.CategoriesTreeListSelector.primaryMessage + "</div>");
				$(html).find(".actiontype").remove();
				new dijit.Tooltip({
					connectId: $(this)[0],
					label: $(html).html(),
					showDelay: 1,
					position: ["before"]
				});
				$(this).addClass("popupinited");
			}
		});
	};
	this.init();
}, {
	Dialog: null,
	title: "Edit Software Categories",
	style: "width:570px;height:530px;",
	extralist: {
		emptymessage: '<div class="help"><div class="title"><img src="/images/help.png" alt=""><span>Help</span></div><ul><li><span>You may associate <img src="/images/addnew.png" alt=""> your software item with one or more categories at any level (1st, 2nd or 3rd). </span></li><li><span>If you select a second level category, that implies to inherit the first level parent as well. The same imples to the third level. </span></li><li><span>if you unselect <img src="/images/cancelicon.png" alt=""> a higher level category, its children will also be unselected. </span></li><li><span>One of the selected, 1st level categories (Applications, Tools,...) should be set as the <a href="#" class="primary popup">primary</a> one.</span></li></ul></div>',
		childrenmessage: '<span class="title">Select sub-category:</span>'
	}
});
appdb.views.VapplianceCategoriesEditor = new appdb.ExtendClass(appdb.views.SoftwareGenericTreeDataEditor, "appdb.views.VapplianceCategoriesEditor", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			main: $(document.createElement("div")).addClass("main").addClass("softwarecategorieseditor").addClass("treedataeditor"),
			header: $(document.createElement("div")).addClass("header").html("<span>Add / modify your virtual appliance item's categories:</span>"),
			content: $(document.createElement("div")).addClass("content").attr("id", "swcategoriesdivEditor").addClass("swcategories_editor").addClass("isvappliance"),
			link: $(document.createElement("a")).addClass("action").addClass("editcategories").html("<span>edit</span>"),
			actions: $(document.createElement("div")).addClass("actions"),
			actionApply: $(document.createElement("a")).addClass("action").addClass("apply").addClass("editbutton").html("<span>ok</span>"),
			actionCancel: $(document.createElement("a")).addClass("action").addClass("cancel").addClass("editbutton").html("<span>cancel</span>")
		},
		EditorType: appdb.views.VApplianceCategoriesTreeListEditor,
		Editor: null,
		entityType: $.trim(o.entityType),
		dataFilter: o.dataFilter,
		useDialog: (typeof (o.useDialog) === "boolean") ? o.useDialog : true,
		onDisplayValue: o.onDisplayValue,
		data: o.data || [],
		maxDisplayLength: (o.maxDisplayLength << 0) || -1
	};
	this.init();
}, {
	Dialog: null,
	title: "Edit Virtual Appliance Categories",
	style: "width:570px;height:530px;",
	extralist: {
		emptymessage: '<div class="help"><div class="title"><img src="/images/help.png" alt=""><span>Help</span></div><ul><li><span>At least one virtual appliance sub categories must be selected by clicking <img src="/images/addnew.png" alt=""/> in the panel on the left.<br/><br/>Click <img src="/images/cancelicon.png" alt="" /> to unselect a sub category.</span></li></ul></div>',
		childrenmessage: '<span class="title">Select sub-category:</span>'
	}
});
appdb.views.SwapplianceCategoriesEditor = new appdb.ExtendClass(appdb.views.SoftwareGenericTreeDataEditor, "appdb.views.SwapplianceCategoriesEditor", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			main: $(document.createElement("div")).addClass("main").addClass("softwarecategorieseditor").addClass("treedataeditor"),
			header: $(document.createElement("div")).addClass("header").html("<span>Add / modify your software appliance item's categories:</span>"),
			content: $(document.createElement("div")).addClass("content").attr("id", "swcategoriesdivEditor").addClass("swcategories_editor").addClass("isswappliance"),
			link: $(document.createElement("a")).addClass("action").addClass("editcategories").html("<span>edit</span>"),
			actions: $(document.createElement("div")).addClass("actions"),
			actionApply: $(document.createElement("a")).addClass("action").addClass("apply").addClass("editbutton").html("<span>ok</span>"),
			actionCancel: $(document.createElement("a")).addClass("action").addClass("cancel").addClass("editbutton").html("<span>cancel</span>")
		},
		EditorType: appdb.views.SWApplianceCategoriesTreeListEditor,
		Editor: null,
		entityType: $.trim(o.entityType),
		dataFilter: o.dataFilter,
		useDialog: (typeof (o.useDialog) === "boolean") ? o.useDialog : true,
		onDisplayValue: o.onDisplayValue,
		data: o.data || [],
		maxDisplayLength: (o.maxDisplayLength << 0) || -1
	};
	this.init();
}, {
	Dialog: null,
	title: "Edit Software Appliance Categories",
	style: "width:570px;height:530px;",
	extralist: {
		emptymessage: '<div class="help"><div class="title"><img src="/images/help.png" alt=""><span>Help</span></div><ul><li><span>At least one software appliance sub categories must be selected by clicking <img src="/images/addnew.png" alt=""/> in the panel on the left.<br/><br/>Click <img src="/images/cancelicon.png" alt="" /> to unselect a sub category.</span></li></ul></div>',
		childrenmessage: '<span class="title">Select sub-category:</span>'
	}
});
appdb.views.SoftwareDisciplinesEditor = new appdb.ExtendClass(appdb.views.SoftwareGenericTreeDataEditor, "appdb.views.SoftwareDisciplinesEditor", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			main: $(document.createElement("div")).addClass("main").addClass("softwaredisciplineseditor").addClass("treedataeditor"),
			header: $(document.createElement("div")).addClass("header").html("<span>Add / modify your software item's disciplines:</span>"),
			content: $(document.createElement("div")).addClass("content").attr("id", "swdisciplinesdivEditor").addClass("swdisciplines_editor"),
			link: $(document.createElement("a")).addClass("action").addClass("editdisciplines").html("<span>edit</span>"),
			actions: $(document.createElement("div")).addClass("actions"),
			actionApply: $(document.createElement("a")).addClass("action").addClass("apply").addClass("editbutton").html("<span>ok</span>"),
			actionCancel: $(document.createElement("a")).addClass("action").addClass("cancel").addClass("editbutton").html("<span>cancel</span>")
		},
		EditorType: appdb.views.SoftwareDisciplinesTreeListEditor,
		Editor: null,
		useDialog: (typeof (o.useDialog) === "boolean") ? o.useDialog : true,
		onDisplayValue: o.onDisplayValue,
		data: o.data || [],
		maxDisplayLength: (o.maxDisplayLength << 0) || -1
	};
	this.init();
}, {
	Dialog: null,
	title: "Edit Software Disciplines",
	style: "width:715px;height:650px;",
	extralist: {
		emptymessage: '<div class="help"><div class="title"><img src="/images/help.png" alt=""><span>Help</span></div><ul><li><span>You may associate <img src="/images/addnew.png" alt=""> your software item with one or more disciplines at any level (1st, 2nd or 3rd). </span></li><li><span>If you select a second level discipline, that implies to inherit the first level parent as well. The same imples to the third level. </span></li><li><span>if you unselect <img src="/images/cancelicon.png" alt=""> a higher level discipline, its children will also be unselected. </span></li></ul></div>',
		childrenmessage: '<span class="title">Select sub-discipline:</span>'
	}
});
/*
 * This is the Gategory Tree View used for navigation
 * on the left side of the Index page. It is used 
 * by the broker request in the appdbgui.js.
 */
appdb.views.MainCategoriesTreeView = new appdb.ExtendClass(appdb.View, "appdb.views.MainCategoriesTreeView", function(o) {
	o = o || {};
	this.options = {
		container: ((o.container) ? $(o.container) : $("#appsalllink").parent().parent()),
		treeViewType: o.treeViewType || appdb.views.CategoriesTreeView,
		dataFilter: ((typeof o.dataFilter === "function") ? o.dataFilter : function() {
			return true;
		}),
		content: o.content || "software"
	};
	this.currentData = new appdb.utils.SimpleProperty();
	this.loadData = function(d) {
		var local = appdb.model.StaticList.SoftwareLogistics;//appdb.model.SoftwareLogistics.getLocalData();
		this.currentData(appdb.model.ApplicationCategories.getLocalData());
		local.category = local.category || [];
		local.category = $.isArray(local.category) ? local.category : [local.category];
		if (!d || d.length == 0) {
			return false;
		}

		var allcats = local.category;
		var found = false;
		$.each(d, function(i, e) {
			if ($.grep(allcats, function(ee) {
				return (e.id == ee.id);
			}).length === 0) {
				allcats.push({count: "1", id: e.id, text: e.val()});
				found = true;
			}
		});

		if (found === true) {
			local.category = allcats;
			appdb.model.StaticList.SoftwareLogistics = local;
		}
		return found;
	};
	/*d: new categories data to append.*/
	this.update = function(d) {
		d = d || [];
		d = $.isArray(d) ? d : [d];
		var mustrender = this.loadData(d);
		if (mustrender === true) {
			if (this.subviews.treeview) {
				this.subviews.treeview.unsubscribeAll(this);
				$(this.subviews.treeview.dom).find(".row.level1").each(function(i, e) {
					$(e).parent().remove();
				});
				this.subviews.treeview = null;
			}
			this.render();
		}
	};
	this.render = function() {
		this.loadData();
		if (!this.subviews.treeview) {
			this.subviews.treeview = new this.options.treeViewType({container: this.dom, canDisplayItem: (function(self) {
					return function(data) {
						var logs = appdb.model.StaticList.SoftwareLogistics;
						logs = (logs && logs.category) ? logs.category : [];
						logs = $.isArray(logs) ? logs : [logs];
						if (logs.length == 0)
							return false;
						var found = $.grep(logs, (function(d) {
							return function(e) {
								return (e.id == d.id && e.count != "0");
							};
						})(data));
						return (found.length > 0) ? self.options.dataFilter(data) : false;
					};
				})(this), content: this.options.content});
		}
		this.subviews.treeview.render(this.currentData());
	};
	this._init = function() {
		this.dom = this.options.container;
	};
	this._init();
});


appdb.views.ManagedFilterEntry = appdb.ExtendClass(appdb.View, "appdb.views.ManagedFilterEntry", function(o) {
	this.options = {
		parent: o.parent || null,
		container: $(o.container),
		filterEntry: o.filterEntry || {},
		entityType: o.entityType || null,
		filter: o.filter || null,
		isClosable: o.isClosable,
		maxLength: (typeof (o.maxLength << 0) === "number") ? (o.maxLength << 0) : 0, //Display name maximum characters
		dom: {
			close: $(document.createElement("a")).addClass("action").addClass("close"),
			typecontainer: $(document.createElement("span")).addClass("type"),
			contenttype: $(document.createElement("span")).addClass("contenttype"),
			contentvalue: $(document.createElement("span")).addClass("contentvalue")
		}
	};
	this.isClosable = function() {
		return ($.type(this.options.isClosable) === "boolean") ? this.options.isClosable : true;
	};
	this.getFilterEntry = function() {
		return this.options.filterEntry || {};
	};
	this.getValue = function() {
		return this.getFilterEntry().value || "";
	};
	this.getDisplayValue = function() {
		if (appdb.views.ManagedFilterEntry.displayText && appdb.views.ManagedFilterEntry.displayText[this.getFilterEntry().type]) {
			var txt = appdb.views.ManagedFilterEntry.displayText[this.getFilterEntry().type](this.getFilterEntry().value);
			if (txt) {
				return txt;
			}
		}
		return appdb.utils.GetDataValueByID(this.getEntityType(), this.getFilterEntry().type, this.getValue());
	};
	this.getType = function() {
		return this.getFilterEntry().type;
	};
	this.getEntityType = function() {
		return this.options.entityType;
	};
	this.getTypeDisplayName = function(typename) {
		var names = appdb.views.ManagedFilterEntry.displayName;
		if (!names)
			return typename;
		if (!names[typename])
			return typename;
		return names[typename];
	};
	this.getPageTitle = function() {
		if ($.inArray(this.options.filterEntry.source, ["user", "system"]) === -1) {
			return null;
		}
		var res = this.options.entityType + " search for " + this.getDisplayValue() + " " + (this.getTypeDisplayName(this.getType()) || this.getType());
		return res.toLowerCase();
	};
	this.render = function() {
		$(this.dom).empty();
		$(this.dom).removeClass();
		$(this.dom).addClass("managedfilterentry");
		$(this.dom).addClass(this.getEntityType());

		if (this.isClosable()) {
			$(this.dom).addClass("closable");
		} else {
			$(this.dom).removeClass("closable");
		}
		var displayName = "" + this.getDisplayValue();
		$(this.dom).attr("title", displayName);
		if (displayName) {
			if (this.options.maxLength > 4 && displayName.length > this.options.maxLength) {
				displayName = displayName.substr(0, this.options.maxLength - 3) + "...";
			}
		} else {
			displayName = "Unknown";
		}
		$(this.options.dom.contenttype).empty().html(this.getTypeDisplayName(this.getType()) + ":");
		$(this.options.dom.contentvalue).empty().html(displayName);
		$(this.dom).append(this.options.dom.contenttype).append(this.options.dom.contentvalue);
		$(this.options.dom.close).append("<img src='/images/closeview.png' alt='' />");

		if (this.isClosable()) {
			$(this.dom).addClass("closable").append(this.options.dom.close);
			$(this.options.dom.close).unbind("click").bind("click", (function(self) {
				return function(ev) {
					ev.preventDefault();
					self.publish({event: "remove", value: self.getFilterEntry()});
					return false;
				};
			})(this));
		}


	};
	this._init = function() {
		this.parent = this.options.parent;
		this.dom = $(this.options.container);
	};
	this._init();
}, {
	displayName: {
		"countryq": "country",
		"mwq": "middleware",
		"categoryq": "category",
		"disciplineq": "discipline",
		"voq": "vo",
		"validated": "freshness",
		"validatedbool": "freshness",
		"role": "scientific orientation",
		"storetype": "supported by",
		"supports": "supports",
		"hasinstances": "with registered"
	},
	displayText: {
		"validated": function(text) {
			if ($.inArray($.trim(text), ["false", "2", "outdated"]) > -1) {
				return "outdated";
			} else if ($.inArray($.trim(text), ["true", "1", "updated"]) > -1) {
				return "updated";
			}

			return "within the last " + text.replace("1 year", "year");
		},
		"validatedbool": function(text) {
			if ($.inArray($.trim(text), ["false", "2", "outdated"]) > -1) {
				return "outdated";
			} else if ($.inArray($.trim(text), ["true", "1", "updated"]) > -1) {
				return "updated";
			}
			return "within the last " + text.replace("1 year", "year");
		},
		"storetype": function(text) {
			if ($.inArray($.trim(text).toLowerCase(), ["2", "virtual appliances"]) > -1) {
				return "Cloud Marketplace";
			}
			return "Software Marketplace";
		},
		"supports": function(text) {
			if ($.inArray($.trim(text).toLowerCase(), ["1", "occi"]) > -1) {
				return "Cloud Marketplace";
			}
			return "None";
		},
		"hasinstances": function(text) {
			if ($.inArray($.trim(text).toLowerCase(), ["1", "virtual images"]) > -1) {
				return "Virtual Images";
			}
			return "None";
		}
	}
});

appdb.views.ManagedFiltering = appdb.ExtendClass(appdb.View, "appdb.views.ManagedFiltering", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		currentFilter: o.filter || null,
		maxLength: (typeof (o.maxLength << 0) === "number") ? (o.maxLength << 0) : 0, //Display name maximum characters
		dom: {
			title: null,
			list: null,
			action: null,
			reset: null
		},
		entries: []
	};
	this.reset = function() {
		this._mediator.clearAll();
	};
	this.showAction = function(display) {
		display = (($.type(display) === "boolean") ? display : false);
		if (display) {
			$(this.dom).children("a.action.display").show();
		} else {
			$(this.dom).children("a.action.display").hide();
		}
	};
	this.getManagedFilter = function() {
		return this.options.currentFilter;
	};
	this.addEntry = function(f) {
		var div = $(document.createElement("div"));
		var entry = new appdb.views.ManagedFilterEntry({
			parent: this,
			container: div,
			filterEntry: $.extend(true, {}, f),
			entityType: this.getManagedFilter().getEntityType(),
			maxLength: this.options.maxLength
		});
		this.options.entries.push(entry);
		entry.render();
		entry.subscribe({event: "remove", callback: function(v) {
				this.getManagedFilter().removeFilters(v);
				this.publish({event: "changed", value: v});
			}, caller: this});
		return div;
	};
	this.getPageTitle = function() {
		if (this.options.entries.length !== 1) {
			return null;
		}
		return this.options.entries[0].getPageTitle();
	};
	this.setLoading = function(loading) {
		loading = (typeof loading === "boolean") ? loading : false;
		if (!loading) {
			$(this.dom).children("span.loading").remove();
			$(this.dom).children("a.action.display").show();
		} else {
			$(this.dom).children(".loading").remove();
			$(this.dom).append("<span class='loading'><img src='/images/ajax-loader-trans-orange.gif' alt=''/></span>");
			$(this.dom).children("a.action.display").hide();
		}
	};
	this.render = function(filter) {
		$.each(this.options.entries, function(i, e) {
			e.reset();
			e = null;
		});
		this.options.entries = [];
		$(this.dom).empty();
		if (this.options.dom.title) {
			$(this.dom).append($(this.options.dom.title).clone(true));
		}
		$(this.options.dom.list).empty();
		$(this.dom).append(this.options.dom.list);
		if (this.options.dom.actiondisplay) {
			$(this.dom).append($(this.options.dom.actiondisplay).clone(true));
			$(this.dom).children("a.action.display").hide();
			$(this.dom).children("a.action.display:first").unbind("click").bind("click", (function(self) {
				return function(ev) {
					ev.preventDefault();
					self.publish({event: "action", value: "display"});
					return false;
				};
			})(this));
		}
		if (this.options.dom.actionreset) {
			$(this.dom).append($(this.options.dom.actionreset).clone(true));
			$(this.dom).children("a.action.reset:first").unbind("click").bind("click", (function(self) {
				return function(ev) {
					ev.preventDefault();
					self.publish({event: "action", value: "reset"});
					return false;
				};
			})(this));
		}
		filter = filter || this.getManagedFilter();
		this.options.currentFilter = filter || this.options.currentFilter;

		var fs = filter.getFilters({source: "user"});
		var sfs = filter.getFilters({source: "system"});
		fs = fs.concat(sfs);
		$.each(fs, (function(self) {
			return function(i, e) {
				if (e.type === "user")
					return;
				var entry = self.addEntry(e);
				if (entry) {
					var li = $(document.createElement("li"));
					$(li).append(entry);
					$(self.options.dom.list).append(li);
				}
			};
		})(this));

		if (fs.length > 0) {
			$(this.dom).addClass("hasitems");
			$(this.dom).children("a.action.reset").removeClass("hidden");
		} else {
			$(this.dom).removeClass("hasitems");
			$(this.dom).children("a.action.reset").addClass("hidden");
		}

	};
	this._initContainer = function() {
		if ($(this.dom).children(".title")) {
			this.options.dom.title = $(this.dom).children(".title:first").clone(true);
		} else {
			this.options.dom.title = null;
		}
		if ($(this.dom).children("ul")) {
			this.options.dom.list = $(this.dom).children("ul:first").clone(true);
		} else {
			this.options.dom.list = $(document.createElement("ul"));
		}
		if ($(this.dom).children("a.action.display")) {
			this.options.dom.actiondisplay = $(this.dom).children("a.action.display:first").clone(true);
		} else {
			this.options.dom.actiondisplay = null;
		}
		if ($(this.dom).children("a.action.reset")) {
			this.options.dom.actionreset = $(this.dom).children("a.action.reset:first").clone(true);
		} else {
			this.options.dom.actionreset = null;
		}
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
});

appdb.views.RefinedFilterType = appdb.ExtendClass(appdb.View, "appdb.views.RefinedFilterType", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		ischild: o.isChild || false,
		entityType: o.entityType || "generic",
		headless: o.headless || false, //in case we want to render only the body and not the link
		minCount: (typeof (o.minCount << 0) === "number") ? (o.minCount << 0) : 1,
		maxCount: (typeof (o.maxCount << 0) === "number") ? (o.maxCount << 0) : 1000,
		maxColumns: (typeof (o.maxColumns << 0) === "number") ? (o.maxColumns << 0) : -1,
		maxLength: (typeof (o.maxLength << 0) === "number") ? (o.maxLength << 0) : 0, //Display name maximum characters
		dom: {
			a: $(document.createElement("a")).attr("href", "#"),
			list: $(document.createElement("ul")).addClass("vertical")
		},
		displayName: o.displayName || o.entityType || "",
		canRenderEntry: (typeof o.canRenderEntry === "function") ? o.canRenderEntry : false,
		dataProvider: (typeof o.dataProvider === "function") ? o.dataProvider : false,
		getValueId: (typeof o.getValueId === "function") ? o.getValueId : false,
		orderBy: o.orderBy,
		css: $.trim(o.css),
		currentData: [],
		transformData: o.transformData,
		overflow: o.overflow || "menu" //menu:Creates "more" submenu, split: splits the menu in columns, none: do not display overflowed data
	};
	this.transformData = function(d) {
		if (typeof this.options.transformData === "function") {
			return this.options.transformData(d);
		}
		return d;
	};
	this.canRenderEntry = function(d) {
		if ((d.count << "0") < this.options.minCount)
			return false;
		if (this.options.canRenderEntry) {
			return this.options.canRenderEntry(d);
		}
		return true;
	};
	this.provideData = function(d) {
		if (this.options.dataProvider) {
			return this.options.dataProvider(d);
		}
		return d;
	};
	this.addEntry = function(d) {
		var dom = $(document.createElement("a")).addClass("logisticentry");
		var name = $(document.createElement("span")).addClass("name");
		var count = $(document.createElement("span")).addClass("count");
		$(dom).append(count).append(name);
		if (!d.count) {
			$(dom).addClass("nocount");
		}
		$(dom).unbind("click").bind("click", (function(self, data) {
			return function(ev) {
				ev.preventDefault();
				if (data.count) {
					data.type = self.options.entityType;
					if (self.options.getValueId) {
						data.id = self.options.getValueId(data);
					}
					self.publish({event: "selected", value: data});
				}
				return false;
			};
		})(((this.options.isChild) ? this.parent : this), d));
		var displayName = "" + d.text;
		if (appdb.views.ManagedFilterEntry.displayText && appdb.views.ManagedFilterEntry.displayText[this.options.entityType]) {
			var txt = appdb.views.ManagedFilterEntry.displayText[this.options.entityType](d.text);
			if (txt) {
				displayName = txt;
			}
		}
		$(dom).attr("title", displayName);
		if (displayName) {
			if (this.options.maxLength > 4 && displayName.length > this.options.maxLength) {
				displayName = d.text.substr(0, this.options.maxLength - 3) + "...";
			}
		} else {
			displayName = "Unknown";
		}
		$(name).html(displayName);
		$(count).html(d.count || "<span style='display:inline-block;height:10px;width:10px;'></span>");
		return dom;
	};
	this.orderEntries = function(d) {
		if (!this.options.orderBy)
			return d;
		this.options.orderBy = (($.isEmptyObject(this.options.orderBy)) ? {"count": "desc", "text": "asc"} : this.options.orderBy);

		var _order = (function(orderby) {
			return function(a, b) {
				for (var i in orderby) {
					if (!orderby.hasOwnProperty(i))
						continue;
					a[i] = ((a[i] << 0) || a[i]);
					b[i] = ((b[i] << 0) || b[i]);
					if ($.trim(orderby[i]).toLowerCase() === "desc") {
						if (a[i] > b[i])
							return -1;
						if (a[i] < b[i])
							return 1;
					} else {
						if (a[i] > b[i])
							return 1;
						if (a[i] < b[i])
							return -1;
					}
				}
				return 0;
			};
		})(this.options.orderBy);

		return d.sort(_order);
	};
	this.renderOverflowMenu = function(d) {
		$(this.dom).append(this.options.dom.list);
	};
	this.addHeadlessMenu = function(d, dom) {
		d = d || [];
		d = $.isArray(d) ? d : [d];
		var ul = $(document.createElement("ul"));
		$.each(d, (function(self) {
			return function(i, e) {
				var entrydom = self.addEntry(e);
				if (entrydom) {
					var li = $(document.createElement("li"));
					$(li).append(entrydom);
					if ($.isArray(e.children) && e.children.length > 0) {
						self.doRender(li, e.children);
					}
					$(ul).append(li);
				}
			};
		})(this));
		$(dom).append(ul);
	};
	this.getColumnedData = function(d) {
		var res = [];
		var i, j, temparray, chunk = this.options.maxCount;
		if (this.options.maxColumns > 0) {
			chunk = Math.ceil(d.length / this.options.maxColumns);
		}
		for (i = 0, j = d.length; i < j; i += chunk) {
			temparray = d.slice(i, i + chunk);
			res.push(temparray);
		}
		return res;
	};
	this.renderOverflowSplit = function(dom, d) {
		var ul = $(document.createElement("ul")).addClass("headless");
		var i, cols = this.getColumnedData(d);
		for (i = 0; i < cols.length; i += 1) {
			var li = $(document.createElement("li"));
			this.addHeadlessMenu(cols[i], li);
			$(ul).append(li);
		}
		$(ul).addClass("headless").addClass("horizontal");
		$(dom).append(ul);
	};
	this.renderOverflowAll = function(dom, d) {
		var ul = $(document.createElement("ul")).addClass("vertical");
		$.each(d, (function(self) {
			return function(i, e) {
				var li = $(document.createElement("li"));
				var entrydom = self.addEntry(e, li);
				if (entrydom) {
					$(li).append(entrydom);
					if ($.isArray(e.children) && e.children.length > 0) {
						self.doRender(li, e.children, {overflow: "none", css: self.options.css});
					}
					$(ul).append(li);
				}
			};
		})(this));
		if ($(ul).children("li").length > 0) {
			$(dom).append(ul);
		}
	};
	this.doRender = function(dom, d, opts) {
		opts = opts || {};
		opts = $.extend(true, this.options, opts);
		if (d.length > opts.maxCount) {
			switch (this.options.overflow) {
				case "split":
					this.renderOverflowSplit(dom, d);
					return;
				case "menu":
					this.renderOverflowMenu(dom, d);
					return;
				case "hidden":
					d = d.slice(this.options.maxCount);
					break;
				default:
					break;
			}
		}
		this.renderOverflowAll(dom, d);
		$(dom).children("ul.vertical:empty").remove();
	};
	this.determineMenuAlingment = function(dom) {
		var w = Math.max(document.documentElement.clientWidth, window.innerWidth || 0);
		var h = Math.max(document.documentElement.clientHeight, window.innerHeight || 0);
		var calcIsRightSided = function(element) {
			var xPosition = 0;
			var yPosition = 0;

			while (element) {
				xPosition += (element.offsetLeft - element.scrollLeft + element.clientLeft);
				yPosition += (element.offsetTop - element.scrollTop + element.clientTop);
				element = element.offsetParent;
			}

			if ((w / 2) < xPosition) {
				return true;
			}
			return false;

		};

		if (calcIsRightSided(dom) === true) {
			$(dom).addClass("rightsided").children("ul").addClass("leftalign");
		} else {
			$(dom).removeClass("rightsided").children("ul").removeClass("leftalign");
		}


	};
	this.render = function(d, dom) {
		if (!dom) {
			$(this.dom).empty();
			dom = this.dom;
		}
		d = d || [];
		d = $.isArray(d) ? d : [d];
		if (d) {
			d = this.transformData(d);
		}
		this.options.currentData = (typeof d !== "undefined") ? d : this.options.currentData;
		$(dom).append($(document.createElement("a")).attr("href", "#").removeClass().addClass(this.options.entityType).html(this.options.displayName));

		d = $.grep(d, (function(self) {
			return function(e) {
				return self.canRenderEntry(e);
			};
		})(this));


		d = this.provideData(d);
		d = this.orderEntries(d);
		this.doRender(dom, d);
		if (this.options.css) {
			$(dom).children("ul").addClass(this.options.css);
		}
		$(dom).unbind("mousemove").bind("mousemove", (function(self) {
			return function(ev) {
				self.determineMenuAlingment($(this)[0]);
				$(this).unbind("mousemove").children("ul").removeClass("hidden");
			};
		})(this)).children("ul").addClass("hidden");
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
});

appdb.views.LicenseListItem = appdb.ExtendClass(appdb.View, "appdb.views.LicenseListItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data,
		originalData: {},
		isvalidcomments: true,
		isvalidid: true,
		editmode: o.editmode || false,
		editor: {select: null, comments: null},
		maxCommentSize: 2000
	};
	this.edit = function() {
		this.options.editmode = true;
		$(this.dom).addClass("edit");
		this.render();
	};
	this.cancel = function() {
		this.options.editmode = false;
		$(this.dom).removeClass("edit");
		this.options.data = $.extend(true, {}, this.options.originalData);
		this.render();
	};
	this.getData = function() {
		return this.options.data;
	};
	this.isValid = function() {
		return (this.options.isvalidcomments && this.options.isvalidid);
	};
	this.validateId = function() {
		if (!this.options.data.id || parseInt(this.options.data.id) < 0) {
			this.options.isvalidid = false;
		} else {
			this.options.isvalidid = true;
		}
	};
	this.validateComments = function() {
		this.options.data.comment = this.options.data.comment || "";
		this.options.isvalidcomments = ((this.options.data.comment.length > this.options.maxCommentSize) ? false : true);
		if (this.options.isvalidcomments === false) {
			$(this.dom).find(".comments").addClass("invalid");
		} else {
			$(this.dom).find(".comments").removeClass("invalid");
		}
	};
	this.hasChanges = function() {
		return (JSON.stringify(this.options.data) !== JSON.stringify(this.options.originalData));
	};
	this.renderViewer = function() {
		var d = this.options.data;
		$(this.dom).find(".title").text(d.title);
		$(this.dom).find(".name").text("(" + $.trim(d.name).toUpperCase() + ")");
		$(this.dom).find(".comments").text();
		if ($.trim(d.comment) !== "") {
			$(this.dom).find(".comments").text(d.comment);
			$(this.dom).addClass("hascomments");
		} else {
			$(this.dom).find(".comments").text("Not specified");
		}
	};
	this.renderLicenseContentLink = function(id) {
		id = id || -1;
		var lic = appdb.views.LicenseListItem.getLicenseById(id);
		if (lic === null || $.trim(lic.url) === "") {
			$(this.dom).find("a.licencelink").addClass("hidden").attr("href", "#");
		} else {
			$(this.dom).find("a.licencelink").removeClass("hidden").attr("href", lic.url);
		}
	};
	this.onValueChange = function() {
		var id = this.options.editor.select.get("value");
		var comments = $.trim(this.options.editor.comments.get("displayedValue"));
		this.options.data.id = id;
		this.options.data.comment = comments.replace(/\</g, "&lt;").replace(/\>/g, "&gt;");
		this.validateId();
		this.validateComments();
		this.renderValidationStatus();
		this.renderLicenseContentLink(id);
		this.publish({event: "change", value: this});
	};
	this.renderEditorLicenseList = function() {
		if (this.options.editor.select !== null) {
			this.options.editor.select.destroyRecursive(false);
			this.options.editor.select = null;
		}
		appdb.views.LicenseListItem.loadLicensesSelect($(this.dom).find(".header > .value"), this.options.data.id, this.parent.getDataItems());
		this.options.editor.select = new dijit.form.FilteringSelect({
			"style": "width:350px",
			"required": true,
			invalidMessage: "You must select a predifined licence from the list.<br/>If you cannot find the desired license you can register <br/>a new one from the link on the left side of this section.",
			onChange: (function(self) {
				return function(v) {
					self.onValueChange();
				};
			})(this)
		}, $(this.dom).find(".header > .value > select")[0]);
		if (typeof this.options.data.id === "undefined" || this.options.data.id < 0) {
			this.options.editor.select.set("value", "-1");
			this.options.editor.select.set("displayedValue", "");
		}
	};
	this.renderValidationStatus = function() {
		if (!this.options.editor.comments)
			return;
		if ($(this.dom).find(".comments > .validationstatus").length === 0) {
			$(this.dom).find(".comments").append("<span class='validationstatus'>Using <span class='count'></span> of <span>" + this.options.maxCommentSize + "</span> characters</span>");
		}
		var vs = $(this.dom).find(".comments > .validationstatus");
		$(vs).find(".count").text(this.options.data.comment.length);

	};
	this.renderEditorComments = function() {
		if (this.options.editor.comments !== null) {
			this.options.editor.comments.destroyRecursive(false);
			this.options.editor.comments = null;
		}
		$(this.dom).find(".comments").empty();
		$(this.dom).find(".comments").append("<textarea></textarea>");
		this.options.editor.comments = dijit.form.Textarea({
			value: $.trim(this.options.data.comment),
			style: "width: 660px;max-width: 660px;padding-bottom:10px;",
			onChange: (function(self) {
				return function(v) {
					self.onValueChange();
				};
			})(this),
			onKeyUp: (function(self) {
				return function(v) {
					self.onValueChange();
				};
			})(this),
			onMouseUp: (function(self) {
				return function(v) {
					self.onValueChange();
				};
			})(this)
		}, $(this.dom).find(".comments > textarea")[0]);
	};
	this.checkComments = function() {

	};
	this.renderEditor = function() {
		this.renderEditorLicenseList();
		this.renderEditorComments();
		this.renderValidationStatus();
		this.renderLicenseContentLink(this.options.data.id);
	};
	this.render = function() {
		if (this.options.editmode === true) {
			this.renderEditor();
		} else {
			this.renderViewer();
		}
		this.renderActions();
		this.initPopup(this);
	};
	this.initPopup = (function() {
		var _currentPopup = null;
		var popup = function(dom, data) {
			if (_currentPopup !== null) {
				_currentPopup.cancel();
				dijit.popup.close(_currentPopup);
				_currentPopup.destroyRecursive(false);
			}
			_currentPopup = new dijit.TooltipDialog({content: $(data)[0]});
			dijit.popup.open({
				parent: $(dom)[0],
				popup: _currentPopup,
				around: $(dom)[0],
				orient: {'BR': 'TR', 'BL': 'TL'}
			});
		};
		return function(e) {
			$(e.dom).find(".header .title").unbind("click").bind("click", (function(self) {
				return function(ev) {
					var d = self.getData();
					$(".dijitPopup.dijitTooltipDialogPopup").remove();
					var data = $(document.createElement("div"));
					var assoc = $(document.createElement("a")).attr("href", "#").attr("title", "View related software items");
					var link = $(document.createElement("a"));
					$(link).attr("href", d.url);
					$(link).attr("target", "_blank");
					$(link).attr("title", "View license");
					$(assoc).unbind("click").bind("click", function(ev) {
						appdb.views.Main.showApplications({flt: "+=&license.id:" + d.id});
						$(".dijitPopup.dijitTooltipDialogPopup").remove();
						ev.preventDefault();
						return false;
					});
					$(assoc).html("<img src='/images/category1.png' border='0'/><span>View associated software</span>");
					$(link).html("<img src='/images/homepage.png' border='0'/><span>View license</span>");
					$(data).addClass("middlewarepopup").append(assoc);
					if( !!appdb.config.features.swappliance ){
						var assoc2 = $(document.createElement("a")).attr("href", "#").attr("title", "View related software appliance items");
						$(assoc2).unbind("click").bind("click", function(ev) {
							appdb.views.Main.showSoftwareAppliances({flt: "+=&license.id:" + d.id},{content: "swappliance"});
							$(".dijitPopup.dijitTooltipDialogPopup").remove();
							ev.preventDefault();
							return false;
						});
						$(assoc2).html("<img src='/images/swapp.png' border='0'/><span>View associated software appliances</span>");
						$(data).append(assoc2);
					}
					$(data).append(link);
					popup(this, data);
					ev.preventDefault();
					return false;
				};
			})(e));
		};
	})();
	this.renderActions = function() {
		$(this.dom).find(".actions a.action").each((function(self) {
			return function(i, e) {
				if ($(e).hasClass("remove")) {
					$(e).unbind("click").bind("click", function(ev) {
						ev.preventDefault();
						self.publish({event: "remove", value: self});
						return false;
					});
				}
			};
		})(this));

	};
	this.initContainer = function() {

	};
	this._init = function() {
		this.parent = this.options.parent;
		this.dom = this.options.container;
		if (!this.options.data || $.isEmptyObject(this.options.data)) {
			this.options.data = {id: -1, comment: "", title: "", link: ""};
		}
		if ($.trim(this.options.data.id) === "") {
			this.options.data.id = 0;
		}
		this.options.data.title = $.trim(this.options.data.title);
		this.options.data.name = $.trim(this.options.data.name);
		this.options.data.url = $.trim(this.options.data.url);
		this.options.data.comment = $.trim(this.options.data.comment);
		this.options.originalData = $.extend(true, {}, this.options.data);
	};
	this._init();
}, {
	loadLicensesSelect: function(dom, selectid, exclude) {
		selectid = selectid || "-1";
		exclude = exclude || [];
		exclude = $.isArray(exclude) ? exclude : [exclude];
		if ($(dom).length === 0)
			return;
		var html = $("<select></select>");
		$.each(appdb.model.StaticList.Licenses, function(i, e) {
			var excl = $.grep(exclude, function(ex) {
				return (ex.id !== selectid && ex.id == e.id);
			});
			if (excl.length > 0)
				return;
			var o = $("<option></option>");
			$(o).attr("value", e.id).text(e.title);
			if (selectid === e.id) {
				$(o).attr("selected", "selected");
			}
			$(html).append(o);
		});
		$(dom).empty().append(html);
	},
	getLicenseById: function(id) {
		var res = $.grep(appdb.model.StaticList.Licenses, function(e) {
			return e.id == id;
		});
		return (res.length > 0) ? res[0] : null;
	},
	getLicenceByTitle: function(v) {
		var chk = $.trim(v).replace(/[\ \.\-\(\)\_\"\'\,\/]/g, "").toLowerCase();
		var res = $.grep(appdb.model.StaticList.Licenses, function(e) {
			return $.trim(e.title).replace(/[\ \.\-\(\)\_\"\'\,\/]/g, "").toLowerCase() === chk;
		});
		return (res.length > 0) ? res[0] : null;
	},
	getLicenseByLink: function(v) {
		var chk = $.trim(v).replace(/\ /g, "").replace(/http(s)?\:\/\//g, "").toLowerCase();
		var res = $.grep(appdb.model.StaticList.Licenses, function(e) {
			return $.trim(e.url).replace(/\ /g, "").replace(/http(s)?\:\/\//g, "").toLowerCase() === chk;
		});
		return (res.length > 0) ? res[0] : null;
	}
});
appdb.views.CustomLicenseListItem = appdb.ExtendClass(appdb.views.LicenseListItem, "appdb.views.CustomLicenseListItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data,
		originalData: {},
		isvalidcomments: true,
		isvalidid: true,
		isvalidtitle: true,
		isvalidlink: true,
		editmode: o.editmode || false,
		editor: {title: null, link: null, comments: null},
		maxCommentSize: 2000,
		maxTitleSize: 80,
		focuseditem: null
	};

	this.isValid = function() {
		return (this.options.isvalidcomments && this.options.isvalidid && this.options.isvalidlink);
	};
	this.setFocused = function(el) {
		this.options.editor.title.isfocused = false;
		this.options.editor.link.isfocused = false;
		this.options.editor.title.isfocused = false;
		if (el) {
			el.isfocused = true;
			this.options.focuseditem = ev;
		}
	};
	this.renderViewer = function() {
		var d = this.options.data;
		$(this.dom).find(".title").text(d.title);
		$(this.dom).find(".name").text();
		$(this.dom).find(".comments").text();
		$(this.dom).find(".link").text();

		if ($.trim(d.comment) !== "") {
			$(this.dom).find(".comments").text(d.comment);
			$(this.dom).addClass("hascomments");
		} else {
			$(this.dom).find(".comments").text("Not specified");
		}
		if ($.trim(d.link) !== "") {
			$(this.dom).find(".link").attr("href", d.link);
		}
	};
	this.validateId = function() {
		return true;
	};
	this.validateTitle = function(el) {
		this.options.data.title = this.options.data.title || "";
		this.options.isvalidtitle = ((this.options.data.comment.length > this.options.maxTitleSize || this.options.data.comment.length === 0) ? false : true);
		if (this.options.isvalidtitle === false) {
			$(this.dom).find(".title").addClass("invalid");
		} else {
			$(this.dom).find(".title").removeClass("invalid");
		}
		if (el === this.options.editor.title) {
			var item = appdb.views.LicenseListItem.getLicenceByTitle(this.options.data.title);
			if (item !== null) {
				$(this.dom).find(".titlefield.value .warning a").attr("href", item.url).text(item.title);
				$(this.dom).find(".titlefield.value").addClass("warning");
			} else {
				$(this.dom).find(".titlefield.value").removeClass("warning");
			}
		} else {
			$(this.dom).find(".titlefield.value").removeClass("warning");
		}
	};
	this.validateLink = function(el) {
		this.options.data.url = this.options.data.url || "";
		this.options.isvalidlink = ((this.options.data.url.length === 0) ? false : true);
		if (this.options.isvalidlink === false) {
			$(this.dom).find(".link").addClass("invalid");
		} else {
			$(this.dom).find(".link").removeClass("invalid");
		}
		if (el === this.options.editor.link) {
			var item = appdb.views.LicenseListItem.getLicenseByLink(this.options.data.url);
			if (item !== null) {
				$(this.dom).find(".linkfield.value .warning a").attr("href", item.url).text(item.title);
				$(this.dom).find(".linkfield.value").addClass("warning");
			} else {
				$(this.dom).find(".linkfield.value").removeClass("warning");
			}
		} else {
			$(this.dom).find(".linkfield.value").removeClass("warning");
		}
	};
	this.onValueChange = function(el) {
		var comments = (this.options.editor && this.options.editor.comments)?$.trim(this.options.editor.comments.get("displayedValue")):"";
		var title = (this.options.editor && this.options.editor.title)?$.trim(this.options.editor.title.get("displayedValue")):"";
		var link = (this.options.editor && this.options.editor.link)?$.trim(this.options.editor.link.get("displayedValue")):"";
		this.options.data.id = 0;

		this.options.data.comment = comments.replace(/\</g, "&lt;").replace(/\>/g, "&gt;");
		this.options.data.title = title.replace(/\</g, "&lt;").replace(/\>/g, "&gt;");
		this.options.data.url = link;
		this.validateId();
		this.validateComments(el);
		this.validateTitle(el);
		this.validateLink(el);
		this.renderValidationStatus();
		this.publish({event: "change", value: this});
	};

	this.renderValidationStatus = function() {
		if (!this.options.editor.comments)
			return;
		if ($(this.dom).find(".comments > .validationstatus").length === 0) {
			$(this.dom).find(".comments").append("<span class='validationstatus'>Using <span class='count'></span> of <span>" + this.options.maxCommentSize + "</span> characters</span>");
		}
		var vs = $(this.dom).find(".comments > .validationstatus");
		$(vs).find(".count").text(this.options.data.comment.length);

	};
	this.lostFocus = function(el) {
		if ($(el.domNode).parent().find(".warning.hover").length > 0) {
			return false;
		} else {
			$(this.dom).find(".value.warning").removeClass("warning");
		}
	};
	this.renderEditorLicenseTitle = function() {
		if (this.options.editor.title !== null) {
			this.options.editor.title.destroyRecursive(false);
			this.options.editor.title = null;
		}
		this.options.editor.title = new dijit.form.ValidationTextBox({
			value: $.trim(this.options.data.title),
			required: true,
			validator: (function(self) {
				return function(value) {
					var v = $.trim(value);
					var res = (v.length < self.options.maxTitleSize && v.length > 0) ? true : false;
					self.options.isvalidtitle = res;
					return res;
				};
			})(this),
			emptyMessage: "License name is required",
			invalidMessage: "License name must not exceed " + this.options.maxTitleSize + " characters",
			onKeyUp: (function(self) {
				return function(v) {
					self.onValueChange(this);
				};
			})(this),
			onMouseUp: (function(self) {
				return function(v) {
					self.onValueChange(this);
				};
			})(this),
			onFocus: (function(self) {
				return function(v) {
					self.onValueChange(this);
				};
			})(this),
			onBlur: (function(self) {
				return function(v) {
					self.lostFocus(this);
				};
			})(this),
			onChange: (function(self) {
				return function(v) {
					self.onValueChange(this);
				};
			})(this)
		}, $(this.dom).find(".title")[0]);
		if ($.trim(this.options.data.title) === "") {
			this.options.editor.title.set("displayedValue", "");
			this.options.editor.title.set("value", "");
			this.options.editor.title.focus();
			$(this.options.editor.title.domNode).find("input").trigger("blur");
		}
	};
	this.renderEditorLicenseLink = function() {
		if (this.options.editor.link !== null) {
			this.options.editor.link.destroyRecursive(false);
			this.options.editor.link = null;
		}
		this.options.editor.link = new dijit.form.ValidationTextBox({
			value: $.trim(this.options.data.url),
			required: true,
			validator: (function(self) {
				return function(value) {
					var v = $.trim(value);
					var res = (/^(http|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:/~+#-]*[\w@?^=%&amp;\/~+#-])?$/i.test(v) && v.length > 0);
					self.options.isvalidlink = res;
					return res;
				};
			})(this),
			emptyMessage: "License Link is required",
			invalidMessage: "Must be a valid URL",
			onKeyUp: (function(self) {
				return function(v) {
					self.onValueChange(this);
				};
			})(this),
			onMouseUp: (function(self) {
				return function(v) {
					self.onValueChange(this);
				};
			})(this),
			onFocus: (function(self) {
				return function(v) {
					self.onValueChange(this);
				};
			})(this),
			onBlur: (function(self) {
				return function(v) {
					self.lostFocus(this);
				};
			})(this),
			onChange: (function(self) {
				return function(v) {
					self.onValueChange(this);
				};
			})(this)
		}, $(this.dom).find(".link")[0]);
		if ($.trim(this.options.data.url) === "") {
			this.options.editor.link.set("displayedValue", "");
			this.options.editor.link.set("value", "1");
			this.options.editor.link.set("value", "");
		}
	};
	this.renderEditor = function() {
		this.renderEditorLicenseTitle();
		this.renderEditorLicenseLink();
		this.renderEditorComments();
		this.onValueChange();
		this.renderValidationStatus();
		$(this.dom).find(".value .warning").unbind("mouseover").bind("mouseover", function(ev) {
			$(ev.target).addClass("hover");
		}).unbind("mouseleave").bind("mouseleave", function(ev) {
			$(ev.target).removeClass("hover");
		});
	};
	this.render = function() {
		if (this.options.editmode === true) {
			this.renderEditor();
		} else {
			this.renderViewer();
		}
		this.renderActions();
		this.initPopup(this);
	};
	this.initPopup = (function() {
		var _currentPopup = null;
		var popup = function(dom, data) {
			if (_currentPopup !== null) {
				_currentPopup.cancel();
				dijit.popup.close(_currentPopup);
				_currentPopup.destroyRecursive(false);
			}
			_currentPopup = new dijit.TooltipDialog({content: $(data)[0]});
			dijit.popup.open({
				parent: $(dom)[0],
				popup: _currentPopup,
				around: $(dom)[0],
				orient: {'BR': 'TR', 'BL': 'TL'}
			});
		};
		return function(e) {
			$(e.dom).find(".header .title").unbind("click").bind("click", (function(self) {
				return function(ev) {
					var d = self.getData();
					$(".dijitPopup.dijitTooltipDialogPopup").remove();
					var data = $(document.createElement("div"));
					var assoc = $(document.createElement("a")).attr("href", "#").attr("title", "View related items");
					;
					var link = $(document.createElement("a"));
					$(link).attr("href", d.url);
					$(link).attr("target", "_blank");
					$(link).attr("title", "View license");
					$(assoc).unbind("click").bind("click", function(ev) {
						appdb.views.Main.showApplications({flt: "+=&license.id:" + d.id});
						$(".dijitPopup.dijitTooltipDialogPopup").remove();
						ev.preventDefault();
						return false;
					});
					$(assoc).html("<img src='/images/search.png' border='0'/><span>View custom licensed software</span>");
					$(link).html("<img src='/images/homepage.png' border='0'/><span>View license</span>");
					$(data).addClass("middlewarepopup").append(assoc).append(link);
					popup(this, data);
					ev.preventDefault();
					return false;
				};
			})(e));
		};
	})();

	this._init();
});
appdb.views.LicenseList = appdb.ExtendClass(appdb.View, "appdb.views.LicenseList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		editmode: o.editmode || false,
		dom: {
			list: $(document.createElement("ul")),
			itemtemplate: $(o.container).children(".itemtemplate:first").clone(),
			emptytemplate: $(o.container).children(".emptytemplate:first").clone(),
			addertemplate: $(o.container).children(".addertemplate:first").clone(),
			customaddertemplate: $(o.container).children(".customaddertemplate:first").clone(),
			customitemtemplate: $(o.container).children(".customitemtemplate:first").clone()
		}
	};
	this.getDataItems = function() {
		var res = [];
		$.each(this.subviews, function(i, e) {
			res.push(e.getData());
		});
		return res;
	};
	this.canEdit = function() {
		var perms = appdb.pages.application.currentPermissions();
		if (perms !== null) {
			return perms.canChangeApplicationLicenses();
		}
		return null;
	};
	this.edit = function() {
		if (this.canEdit() === false)
			return;
		this.options.editmode = true;
		$(this.dom).addClass("editmode");
		this.render();
		$(this.dom).append(this.options.dom.addertemplate);
		$(this.options.dom.addertemplate).children("a").unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.newItem();
				return false;
			};
		})(this));
		$(this.dom).append(this.options.dom.customaddertemplate);
		$(this.options.dom.customaddertemplate).children("a").unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.registerNew();
				return false;
			};
		})(this));
		$(this.dom).parent().find("a.customlicenseadder").unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.registerNew();
				return false;
			};
		})(this));
	};
	this.hasChanges = function() {
		var d = this.options.data || [];
		if (d.length !== this.subviews.length)
			return true;

		var res = $.grep(this.subviews, function(e) {
			return e.hasChanges();
		});
		return ((res.length > 0) ? true : false);
	};
	this.setupForm = function(frm) {
		if ($(frm).length === 0) {
			return false;
		}
		var i, len = this.subviews.length, item, inp;
		$(frm).remove("input[name^='license']");
		for (i = 0; i < len; i += 1) {
			item = this.subviews[i];
			inp = document.createElement("input");
			$(inp).attr("name", "license" + i).attr("type", "hidden").attr("value", JSON.stringify(item.getData()));
			$(frm).append(inp);
		}
		return true;
	};
	this.cancel = function() {
		this.options.editmode = false;
		$(this.dom).removeClass("editmode");
		this.render();
	};
	this.getInvalidItems = function() {
		var res = $.grep(this.subviews, function(e) {
			return (e.isValid() === false);
		});
		return res;
	};
	this.animateInvalidItems = function() {
		var items = this.getInvalidItems();
		$.each(items, function(i, e) {
			$.each($(e.dom).children(), function(ii, ee) {
				var eeprevColor = $(ee).css("background-color") || "";
				$(ee).animate({"opacity": "0.5", "background-color": "red"}, 300, function() {
					setTimeout(function() {
						$(ee).animate({"opacity": "1", "background-color": eeprevColor}, 400);
					}, 10);
				});
			});
		});
	};
	this.registerNew = function() {
		var iv = this.getInvalidItems();
		if (iv.length > 0) {
			this.animateInvalidItems();
		} else {
			this.newItem(true);
		}
	};
	this.newItem = function(iscustom) {
		iscustom = (typeof iscustom === "boolean") ? iscustom : false;
		var empty = this.getInvalidItems();
		if (empty.length > 0) {
			this.animateInvalidItems();
		} else if (iscustom) {
			this.addItem({id: 0, title: "", comment: "", url: ""});
		} else {
			this.addItem({id: -1});
		}
	};
	this.validityAction = function() {
	};
	this.isValid = function(){
		return (this.getInvalidItems().length === 0);
	};
	this.addItem = function(item) {
		item = item || {};
		if ($.isEmptyObject(item) === false && typeof item.id === "undefined") {
			item.id = 0;
		} else if (item.id === "0") {
			item.id = 0;
		}
		var li = $(document.createElement("li"));
		var dom = $(((item.id === 0) ? this.options.dom.customitemtemplate : this.options.dom.itemtemplate)).clone();
		$(li).append(dom);
		$(this.options.dom.list).append(li);
		var objType = ((item.id === 0) ? appdb.views.CustomLicenseListItem : appdb.views.LicenseListItem);
		var obj = new objType({
			container: dom,
			parent: this,
			data: item,
			editmode: this.options.editmode
		});
		this.subviews.push(obj);
		obj.subscribe({event: "change", callback: function(v) {
				this.options.isvalid = (this.getInvalidItems().length > 0);
				this.validityAction();
				this.publish({event: "validation", value: v.isValid() || this.getInvalidItems()});
			}, caller: this});
		obj.subscribe({event: "remove", callback: function(v) {
				this.removeItem(v);
			}, caller: this});
		obj.render();
		this.renderEmpty();
		if( item && parseInt(item.id) > 0 ){
			this.publish({event: "validation", value: obj.isValid() || this.getInvalidItems()});
		}
	};
	this.removeItem = function(item) {
		if (typeof item === "undefined" || typeof item.dom === "undefined")
			return;
		var svindex = -1;
		$.each(this.subviews, function(i, e) {
			if (svindex < 0 && e === item) {
				svindex = i;
			}
		});
		if (svindex < 0)
			return;
		var dom = $(item.dom).parent();
		item.unsubscribeAll();
		item.reset();
		item = null;
		this.subviews.splice(svindex, 1);
		$(dom).remove();
		this.renderEmpty();
		this.publish({event: "validation", value: this.isValid()});
	};

	this.renderList = function(d) {
		$.each(d, (function(self) {
			return function(i, e) {
				e.index = i;
				self.addItem(e);
			};
		})(this));
	};
	this.renderEmpty = function() {
		if (this.subviews.length > 0) {
			$(this.dom).find(".empty").remove();
			$(this.dom).removeClass("empty");
		} else {
			$(this.dom).append(this.options.dom.emptytemplate);
			$(this.dom).addClass("empty");
		}
	};
	this.render = function(d) {
		this.reset();
		d = d || this.options.data || [];
		d = $.isArray(d) ? d : [d];
		this.options.data = d;
		$(this.options.dom.list).empty();
		this.renderList(d);
		this.renderEmpty();
	};
	this.reset = function() {
		$.each(this.subviews, function(i, e) {
			e.unsubscribeAll();
			e.reset();
			e = null;
		});
		this.subviews = [];
	};
	this.getEditedData = function(){
		var res = {};
		res[this.getProp("bind")] = this.getDataItems();
		return res;
	};
	this.getProp = function(name){
		switch($.trim(name).toLowerCase()){
			case "bind":
				return "license";
			default: 
				return null;
		}
	};
	this.initContainer = function() {
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.list);
		$(this.options.dom.list).empty();
		$(this.options.dom.itemtemplate).removeClass("hidden").removeClass("itemtemplate").addClass("item");
		$(this.options.dom.customitemtemplate).removeClass("hidden").removeClass("customitemtemplate").addClass("item");
		if ($(this.options.dom.emptytemplate).length === 0) {
			this.options.dom.emptytemplate = $("<div class='empty'>No licenses specified</div>");
		}
		$(this.options.dom.emptytemplate).removeClass("hidden").addClass("empty");
	};
	this._init = function() {
		this.parent = this.options.parent;
		this.dom = this.options.container;
		this.options.data = this.options.data || [];
		this.options.data = $.isArray(this.options.data) ? this.options.data : [this.options.data];
		this.initContainer();
	};
	this._init();
});

appdb.views.ConnectedAccountsItem = appdb.ExtendClass(appdb.View, "appdb.views.ConnectedAccountsItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		meta: o.meta || {},
		dom: {
			content: $("<div class='content'></div>"),
			actions: $("<div class='actions'></div>")
		}
	};
	this.renderActions = function() {
		$(this.options.dom.actions).empty();
		var a = $("<a href='#' title='Disconnect this account from your profile' class='action disconnect' ><img src='/images/closeview.png' alt=''/><span>Disconnect</span></a>");
		if (this.options.data.current === true) {
			a = $("<span href='#' title='This is the current signed in account' class='action currentaccount' ><img src='/images/yes_grey.png' alt=''/><span>Current account</span></a>");
		} else {
			$(a).unbind("click").bind("click", (function(self) {
				return function(ev) {
					ev.preventDefault();
					self.parent.parent.disconnectAccount(self.options.data, self.options.meta);
					return false;
				};
			})(this));
		}
		$(this.options.dom.actions).append(a);
	};
	this.getIdpTrace = function(traces) {
		traces = ((typeof traces === 'string') ? traces.split('\n') : traces);
		traces = ($.isArray(traces) ? traces : [traces]);
		traces = $.grep(traces, function(t) { return $.trim(t) !== ''; });
		var trace = '';
		var spmap = appdb.config.accounts.egiaai.idptraces || {};

		if (traces.length > 1 && spmap[traces[0]] === 'egi-aai') {
			trace = $.trim(traces[traces.length -1]);
		} else if (traces.length > 0) {
			trace = $.trim(traces[0]);
		}
		
		if (trace !== '' && $.trim(spmap[trace]) !== '') {
			trace = $.trim(spmap[trace]);
		}
		
		return trace;
	};
	this.renderContent = function() {
		var d = this.options.data;
		var name = $("<span class='name'></span>");
		var uid = $("<span class='uid'></span>");
		var trace = this.getIdpTrace(d.idptrace || '');
		if(trace !== '' && $.trim(d.source).toLowerCase() === "egi-aai") {
			trace = ' (' + trace + ')';
		} else {
			trace = '';
		}

		$(uid).text($.trim(d.uid) + trace);
		$(name).text($.trim(d.name));
		if ($.trim(d.name) !== "" && this.options.meta.displayName === true) {
			if ($.trim(d.source).toLowerCase() === "edugain") {
				$(uid).text(" (" + $(uid).text() + ")");
			} else {
				$(uid).text(trace);
			}
		} else {
			$(name).text("");
		}

		$(this.options.dom.content).append(name).append(uid);
	};
	this.render = function() {
		this._initContainer();
		this.renderContent();
		this.renderActions();
	};
	this._initContainer = function() {
		$(this.dom).empty().addClass("connectedaccountitem");
		if (this.options.data.current === true) {
			$(this.dom).addClass("current");
		} else {
			$(this.dom).removeClass("current");
		}
		$(this.options.dom.content).empty();
		$(this.options.dom.actions).empty();
		$(this.dom).append(this.options.dom.content).append(this.options.dom.actions);
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
});

appdb.views.ConnectedAccountTypeListItem = appdb.ExtendClass(appdb.View, "appdb.views.ConnectedAccountTypeListItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		accountType: o.accountType,
		meta: o.meta,
		data: o.data || [],
		dom: {
			header: $("<div class='header'></div>"),
			actions: $("<div class='actions'></div>"),
			list: $("<ul class='accountitemlist'></ul>"),
			footer: $("<div class='footer'></div>")
		}
	};
	this.clearItems = function() {
		$.each(this.subviews, function(i, e) {
			e.unsubscribeAll();
			e.reset();
			e = null;
		});
		this.subviews = [];
		$(this.options.dom.list).empty();
		this._initContainer();
	};
	this.reset = function() {
		this.unsubscribeAll();
		this.clearItems();
	};
	this.isEmpty = function() {
		return (this.options.data.length === 0);
	};
	this.appendItem = function(d) {
		var li = $("<li></li>");
		var div = $("<div></div>");
		var view = new appdb.views.ConnectedAccountsItem({
			container: div,
			parent: this,
			meta: this.options.meta,
			data: d
		});
		$(li).append(div);
		$(this.options.dom.list).append(li);
		this.subviews.push(view);
		view.render();
	};
	this.renderHeader = function() {
		$(this.options.dom.header).empty();
		var title = $("<div class='title'></div>");
		var img = $("<img alt=''></img>");
		$(img).attr("src", this.options.meta.image);
		var name = $("<span></span>");
		$(name).text(this.options.meta.name);

		$(title).append(img).append(name);
		$(this.options.dom.header).append(title);
	};
	this.renderFooter = function() {

	};
	this.renderList = function() {
		$.each(this.options.data, (function(self) {
			return function(i, e) {
				self.appendItem(e);
			};
		})(this));
	};
	this.renderEmpty = function() {
		$(this.dom).removeClass("empty");
		$(this.dom).children(".empty").remove();
		if (this.isEmpty()) {
			$(this.dom).addClass("empty");
			$(this.dom).append("<div class='empty'><div class='message'>No connected " + this.options.meta.name + " accounts found.</div></div>");
		}
	};
	this.canRenderActions = function() {
		return (
			this.options.meta &&
			this.options.meta.source === 'egi-aai' &&
			appdb.config.deploy.instance === 'production' &&
			window.userCurrentAccount &&
			window.userCurrentAccount.source === 'egi-aai'
		);
	};
	this.renderActions = function() {
		if (this.canRenderActions() === false) {
			return;
		}
		$(this.options.dom.actions).empty();
		if (this.isEmpty() === false && ($.trim(samlLoginSourceType).toLowerCase() === "x509" || this.options.meta.id === "x509-sp"))
			return;
                if (this.options.meta.canAdd === true) {
                    var connect = $("<a href='#' title='Connect to a new " + this.options.meta.name + " account' class='action icontext'><img src='/images/addnew.png' alt=''/><span>Add account</span></a>");
                    $(connect).unbind("click").bind("click", (function(self) {
                        return function(ev) {
                            ev.preventDefault();
                            self.parent.connectAccount(self.options.meta);
                            return false;
                        };
                    })(this));
                    $(this.options.dom.actions).append(connect);
                }
	};
        this.canRender = function() {
	   this.options.meta = this.options.meta || {};
           return (
		(
		   this.isEmpty() &&
		   this.options.meta.canAdd !== true &&
		   this.options.meta.alwaysVisible !== true
		) === false
	    );
        };
	this.render = function() {
	    this.clearItems();

	    if (this.canRender()) {
		this.renderHeader();
		this.renderList();
		this.renderFooter();
		this.renderEmpty();
		this.renderActions();
	    } else {
		$(this.dom).empty();
	    }
	};
	this._initContainer = function() {
		$(this.dom).addClass("accounttypeitem");
		$(this.dom).empty();
		this.options.dom.header = $("<div class='header'></div>");
		this.options.dom.list = $("<ul class='accountitemlist'></ul>");
		this.options.dom.footer = $("<div class='footer'></div>");
		this.options.dom.actions = $("<div class='actions'></div>");

		$(this.dom).append(this.options.dom.header).append(this.options.dom.actions).append(this.options.dom.list).append(this.options.dom.footer);
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
});
appdb.views.ConnectedAccountTypeList = appdb.ExtendClass(appdb.View, "appdb.views.ConnectedAccountTypeList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			list: $("<ul></ul>").addClass("useraccountslist")
		},
		accountTypes: {},
		supported: appdb.views.ConnectedAccountTypeList.getSupportedAccountTypes() || []
	};
	this.clearItems = function() {
		$.each(this.subviews, function(i, e) {
			e.unsubscribeAll();
			e.reset();
			e = null;
		});
		this.subviews = [];
		$(this.options.dom.list).empty();
	};
	this.reset = function() {
		this.unsubscribeAll();
		this.clearItems();
	};
	this.appendType = function(accountType) {
		var li = $("<li></li>");
		var div = $("<div></div>");
		var view = new appdb.views.ConnectedAccountTypeListItem({
			container: div,
			parent: this,
			meta: accountType.meta,
			data: accountType.data
		});
		$(li).append(div);
		$(this.options.dom.list).append(li);
		this.subviews.push(view);
		view.render();
	};
	this.renderProcess = function(enable, text) {
		enable = (typeof enable === "boolean") ? enable : false;
		text = $.trim(text) || "Disconnecting Account";
		text = "..." + text;
		$(this.dom).children(".removing").remove();
		if (enable) {
			$(this.dom).append("<div class='removing'><div class='shader'/><div class='message icontext'><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span>" + text + "</span></div></div>");
		}
	};
	this.doDisconnectAccount = function(accountitem, accounttype) {
		this.renderProcess(true);
		var xhr = $.ajax({
			url: appdb.config.endpoint.base.replace("http://", "https://") + "/saml/disconnectaccount",
			"type": "POST",
			data: {id: accountitem.id},
			dataType: "json",
			success: (function(self) {
				return function(d) {
					self.renderProcess(false);
					if (d.error) {
						appdb.utils.ShowNotificationDialog({
							title: "Could not disconnect account",
							message: "<br/><pre>" + d.error + "</pre><br/><br/>"
						});
						return;
					} else {
						appdb.utils.ShowNotificationDialog({
							title: "Successful " + accounttype.name + " account disconnection",
							message: "Your " + accounttype.name + " account " + (accountitem.name || accountitem.uid) + " is now disconnected from your EGI Applications Database profile."
						});
						userCurrentAccounts = d;
						appdb.pages.Person.initConnectedAccounts();
					}
				};
			})(this),
			error: (function(self) {
				return function(jsxhr, textStatus, errorThrown) {
					self.renderProcess(false);
					appdb.utils.ShowNotificationDialog({
						title: "Could not disconnect account",
						message: "An error occured during the account disconnection:<br/><br/><pre>" + errorThrown + "</pre><br/><br/>"
					});
				};
			})(this)
		});
	};
	this.disconnectAccount = function(accountitem, accounttype) {
		this.renderProcess();
		appdb.utils.ShowNotificationDialog({
			title: "<span style='vertical-align: middle;'><span>Disconnecting </span><img src='" + accounttype.image + "' alt='' style='padding: 0px 5px;width:16px;height:16px;border:none;vertical-align:middle;'/><span>" + accounttype.name + " account</span></span>",
			message: "By disconnecting this account from your profile you won't be able to use it to sign in the EGI Applications Database.<br/><br/>You can however repeat the connection process by signing in again with the same " + accounttype.name + " account<br/><br/>",
			action: "<span class='editbutton' style='padding: 5px;'><img src='/images/stop.png' alt='' style='width:15px;height:15px;border:none;padding-right: 4px;vertical-align:middle;'/>Disconnect</span>",
			actionTitle: "Click to disconnect this " + $.trim(accounttype.name).toLowerCase() + " account from your profile",
			close: "cancel",
			callback: (function(self, uadata, uatype) {
				return function(c) {
					if (c === "Disconnect") {
						self.doDisconnectAccount(uadata, uatype);
					}
				};
			})(this, accountitem, accounttype)
		});
		return;
	};
	this.doConnectAccount = function(accounttype) {
		this.renderProcess(true, "Connecting " + accounttype.name);
		setTimeout(function() {
		    window.location.href = accounttype.connectUrl || (appdb.config.endpoint.base + "/saml/connect?source=" + accounttype.id);
		}, 400);
	};
	this.getConnectMessage = function(accounttype) {
		accounttype = accounttype || {};
		accounttype.connectMessage = $.trim(accounttype.connectMessage);
		return $.trim(accounttype.connectMessage);
	};
	this.connectAccount = function(accounttype) {
		this.renderProcess();
		var cmessage = "In order to connect a new account to your profile you will be redirected to " + accounttype.name + " portal for authentication.<br/><br/>" + this.getConnectMessage(accounttype) + "<br/>";
		if ($.trim(accounttype.id).toLowerCase() === "x509-sp") {
			cmessage = "In order to connect a new digital certificate to your profile, you need to import the certificate to your browser.<br/><br/> If your browser has many digital certificates, you will be prompted to select one to connect.";
		}
		if($.trim(accounttype.id).toLowerCase() === 'egi-aai-sp') {
		    cmessage = "The connection/linking of a new account has been changed and is now managed by <i>COmanage</i>.<br/><br/>Please, click the <i>Connect</i> button bellow to be redirected to the COmanage. If you need further instructions please click <a style='font-size:15px;' href='https://wiki.egi.eu/wiki/AAI_usage_guide#Linking_Additional_Organisational.2FSocial_Identities_to_your_EGI_Account' target='_blank'>here</a>.";
		}
		appdb.utils.ShowNotificationDialog({
			title: "<span style='vertical-align: middle;'><span>Connecting to a new </span><img src='" + accounttype.image + "' alt='' style='padding: 0px 5px;width:16px;height:16px;border:none;vertical-align:middle;'/><span>" + accounttype.name + " account</span></span>",
			message: cmessage,
			action: "<span class='editbutton' style='padding: 5px;'><img src='/images/addnew.png' alt='' style='width:15px;height:15px;border:none;padding-right: 4px;vertical-align:middle;'/>Connect</span>",
			actionTitle: "Click to connect a " + $.trim(accounttype.name).toLowerCase() + " account to your profile",
			close: "cancel",
			callback: (function(self, uatype) {
				return function(c) {
					if (c === "Connect") {
						self.doConnectAccount(uatype);
					}
				};
			})(this, accounttype)
		});
		return;
	};
	this.groupItemsByType = function(d) {
		this.options.accountTypes = {};
		$.each(this.options.supported, (function(self) {
			return function(i, e) {
				self.options.accountTypes[e.source] = {meta: e, data: []};
				self.options.accountTypes[e.source].data = $.grep(d, function(item) {
					return (item.source === e.source);
				});
			};
		})(this));

	};
	this.render = function(d) {
		this.clearItems();
		this.groupItemsByType(d);
		for (var i in this.options.accountTypes) {
			if (this.options.accountTypes.hasOwnProperty(i) === false)
				continue;
			this.appendType(this.options.accountTypes[i]);
		}

	};
	this._initContainer = function() {
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.list);
		$(this.options.dom.list).empty();
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
}, {
	getSupportedAccountTypes: function() {
		if (appdb.config.accounts) {
			appdb.config.accounts.available = appdb.config.accounts.available || [];
			appdb.config.accounts.available = $.isArray(appdb.config.accounts.available) ? appdb.config.accounts.available : [appdb.config.accounts.available];
			return appdb.config.accounts.available;
		}
		return [];
	}
});

appdb.views.UserMessageDetails = appdb.ExtendClass(appdb.View, "appdb.views.UserMessageDetails", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			header: $("<div class='header'></div>"),
			content: $("<div class='content'></div>"),
			actions: $("<div class='actions'></div>")
		}
	};
	this.initActions = function() {

	};
	this.render = function(d) {

	};
	this.initContainer = function() {

	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
});

appdb.views.UserMessageListItem = appdb.ExtendClass(appdb.View, "appdb.views.UserMessageListItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			actions: $("<div class='actions'></div>")
		}
	};
	this.initActions = function() {

	};
	this.render = function() {

	};
	this.initContainer = function() {

	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
});

appdb.views.UserMessagesList = appdb.ExtendClass(appdb.View, "appdb.views.UserMessages", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		hasPaging: ((typeof o.hasPaging === "boolean") ? o.hasPaging : $(o.container).data("haspaging")),
		hasSearch: ((typeof o.hasSearch === "boolean") ? o.hasSearch : $(o.container).data("hassearch")),
		itemTemplate: $(o.container).children(".itemtemplate"),
		data: [],
		paging: {}
	};
	this.initActions = function() {

	};
	this.addItem = function(item) {

	};
	this.render = function(d, p) {
		d = d || [];
		d = $.isArray(d) ? d : [d];
		p = p || {};

	};
	this.initContainer = function() {
		var itemtmpl = $(this.dom).children(".itemtemplate");
		if ($(itemtmpl).length > 0) {
			this.options.itemTemplate = $(itemtmpl).clone();
			$(this.dom).children(".itemtemplate").remove();
		} else {

		}
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
});

appdb.views.ProfileListItem = appdb.ExtendClass(appdb.views.ListItem, "appdb.views.ProfileListItem", function(o) {
	this.render = function(d) {
		this._itemData = d || this._itemData;
		d = this._itemData;
		var doc = document, fullname = d.firstname + " " + d.lastname, a, img, div, span, span1, span2, contacts, connect, isos, i, flags = "", pimg, ishttps = Boolean(appdb.config.https) && true, prot;
		isos = d.country.isocode.split("/");
		for (i = 0; i < isos.length; i += 1) {
			flags += "<img width='16px' src='/images/flags/" + isos[i].toLowerCase() + ".png' border='0' />";
		}
		flags = "<span class='personflags' >" + flags + "</span>";
		var permlink = appdb.utils.getItemCanonicalUrl("person", d);
		a = $(doc.createElement("a")).attr("href", permlink).attr("target", "_blank").attr("title", fullname).addClass("itemlink").click((function(_this, data) {
			return function() {
				_this.publish({event: "click", value: data});
				return false;
			};
		})(this, d));
		pimg = d.image;

		if (pimg) {
			prot = pimg.substr(4, 1);
			if (ishttps === true && prot === ":") {
				pimg = "https" + pimg.substr(4, pimg.length);
			} else if (prot === "https") {
				pimg = "http:" + pimg.substr(5, pimg.length);
			}
		}
		div = doc.createElement("div");
		$(div).addClass("item");
		img = $(doc.createElement("img")).
				attr("src", ((pimg) ? "/people/getimage?req=" + encodeURI(d.lastUpdated) + "&id=" + d.id : (appdb.config.images.person))).addClass("itemimage");
		span = $(doc.createElement("span")).append(fullname).addClass("itemname");
		if (isos.length > 1) {
			$(span).addClass("flagcount" + isos.length);
		}
		span1 = $("<span></span>").append(unescape(d.role.type) + "<br/>" ).addClass("itemsorttext");
		span2 = $("<span></span>").append(unescape(d.role.type) + "<br/>" + d.country.val() + "<br/>Registered since " + d.registeredOn).addClass("itemlongtext");
		$(a).append(img);
		$(a).append(flags);
		$(a).append(span);
		$(a).append(span1);
		$(a).append(span2);
		if (d.deleted) {
			if (Boolean(d.deleted) == true) {
				deleted = true;
				$(this.dom).addClass("deleted");
				title = "This profile has been deleted.";
				$(a).attr("title", title);
			}
		}
		contacts = $("<ul class='contacts'></ul>");
		d.contact = d.contact || [];
		d.contact = $.isArray(d.contact) ? d.contact : [d.contact];
		$.each(d.contact, function(i, e) {
			var cont = $("<li></li>");
			var contitem = $("<div class='contact'></div>");
			$(contitem).append("<span class='type'>" + e.type + "</span>");
			if (e["protected"] === "true") {
				$(contitem).append("<img class='data' src='" + e.val() + "' alt=''/></span>");
			} else {
				$(contitem).append("<span class='data'>" + e.data + "</span>");
			}
			if (e.primary === "true") {
				$(contitem).addClass("primary");
			}
			$(cont).append(contitem);
			$(contacts).append(cont);
		});
		if (d.contact.length === 0) {
			$(contacts).addClass("empty");
		}
		$(a).append(contacts);
		$(div).append($(a));
		connect = $("<a class='action icontext connectaction' href='' title='Connect to this profile'><span class='shade'></span><span class='text'>click to connect</span></a>");
		$(connect).unbind("click").bind("click", (function(self, profiledata) {
			return function(ev) {
				ev.preventDefault();
				self.publish({event: "connect", value: {data: profiledata, el: $(this).closest(".item")[0]}});
				return false;
			};
		})(this, d));
		$(div).append(connect);
		$(div).attr("data-id", d.id);
		$(this.dom).append(div);
	};
	this.reset = function() {
		$(this.dom).find("div > a").each(function(index, elem) {
			$(this).unbind("click");
		});
		$(this.dom).empty();
	};
});

appdb.views.ProfileList = appdb.ExtendClass(appdb.views.List, "appdb.views.ProfileList", function(o) {
	this._constructor = function() {
		this.setListType(appdb.views.ProfileListItem);
		this.itemSubscriptions = [
			{event: "click", callback: function(data) {
					this.publish({event: "connect", value: data.data});
					appdb.pages.newaccount.connectprofile(data.data);
				}, caller: this},
			{event: "connect", callback: function(data) {
					appdb.pages.newaccount.connectprofile(data.data, data.el);
				}, caller: this}];
		$(this.dom).addClass("peoplelist");
	};
	this._constructor();
});

appdb.views.VOMembershipList = appdb.ExtendClass(appdb.View, "appdb.views.VOMembershipList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			list: $("<ul class='vomembership'></ul>"),
			empty: $('<div class="emptycontent" ><div class="content"><img src="/images/exclam16.png"><span>No membership found.</span></div></div>')
		},
		data: []
	};
	this.transformData = function(d) {
		var member = d || [];
		member = $.isArray(member) ? member : [member];

		var res = {};
		$.each(member, function(i, e) {
			if (!res[e.id]) {
				res[e.id] = {id: e.id, name: e.name, discipline: e.discipline, member_since: e.member_since, roles: []};
			}
			if (res[e.id].roles.indexOf(e.role) === -1) {
				res[e.id].roles.push(e.role);
			}
		});
		var memberlist = [];
		for (var i in res) {
			if (res.hasOwnProperty(i) === false)
				continue;
			memberlist.push(res[i]);
		}
		memberlist.sort(function(a, b) {
			var an = $.trim(a.name).toLowerCase(), ab = $.trim(b.name).toLowerCase();
			if (an > ab)
				return 1;
			if (an < ab)
				return -1;
			return 0;
		});
		return memberlist;
	};
	this.getVOMemberDisplayName = function(name) {
		var res = name || "member";
		res = $.trim(res).toLowerCase();
		res = res.replace(/^vo\ /, "VO ");
		return $.trim(res);
	};
	this.addFlatMemberList = function(d) {
		d = d || [];
		d = $.isArray(d) ? d : [d];

		if (d.length === 0) {
			d.push("member");
		}
		var ul = $("<ul></ul>");
		$.each(d, (function(self) {
			return function(i, e) {
				var li = $("<li class='voroleitem'>" + self.getVOMemberDisplayName(e) + "</li>");
				$(ul).append(li);
				var lisep = $("<li class='seperator'><span>\u2022</span></li>");
				$(ul).append(lisep);
			};
		}(this)));

		$(ul).find("li.seperator:last").remove();
		return ul;
	};
	this.addFlatItem = function(d) {
		var li = $("<li></li>");

		var voname = $("<div class='voname'></div>");
		var volink = $('<a href="#" class="voname icontext" data="' + d.discipline + '" title="' + d.discipline + '"><img src="' + ((d.discipline) ? "/vo/getlogo?name="+encodeURI(d.name)+"&vid="+ (d.id<<0) +"&id=" + encodeURI(d.discipline) : "/images/homepage.png") + '" alt=""/><span>' + d.name.htmlEscape() + '</span></a>');
		$(volink).unbind("click").bind("click", (function(voname, vodiscipline) {
			return function(ev) {
				ev.preventDefault();
				var img = "/images/homepage";
				if (vodiscipline) {
					img = "/vo/getlogo?name="+encodeURI(d.name)+"&vid="+ (d.id<<0) +"&id=" + encodeURI(vodiscipline);
				}
				var popup = $("<div class='vopopup'></div>");
				var related = $('<a href="#" onclick="appQuickLink(\'' + voname + '\',6,{mainTitle:\'' + voname + '\',isList:false, content:\'software\'});" class="vorelated"><img src="/images/category1.png" border="0"></img><span>View associated software</span></a>');
				var related2 = $('<a href="#" onclick="appQuickLink(\'' + voname + '\',6,{mainTitle:\'' + voname + '\',isList:false, content:\'vappliance\'});" class="vorelated"><img src="/images/category34.png" border="0"></img><span>View associated vappliances</span></a>');
				var details = $('<a href="#" onclick="appdb.views.Main.showVO(\'' + voname + '\',{mainTitle: \'' + voname + '\'});" target="_blank" title="View ' + (voname).htmlEscape() + ' details" class="vodetails"></a>');
				$(related).html("<img src='/images/search.png' border='0'></img><span>View associated software</span>");
				$(details).html("<img src='" + img + "' border='0'></img><span>View Virtual Organization details</span>");
				$(popup).append(related).append(related2);
				if( !!appdb.config.features.swappliance ){		
					var related3 = $('<a href="#" onclick="appQuickLink(\'' + voname + '\',6,{mainTitle:\'' + voname + '\',isList:false, content:\'swappliance\'});" class="vorelated"><img src="/images/swapp.png" border="0"></img><span>View associated swappliance</span></a>');
					$(popup).append(related3);
				}
				$(popup).append(details);
				if (appdb.views.VOMembershipList.popup !== null) {
					dijit.popup.close(appdb.views.VOMembershipList.popup);
					appdb.views.VOMembershipList.popup.destroyRecursive(false);
					appdb.views.VOMembershipList.popup = null;
				}
				appdb.views.VOMembershipList.popup = new dijit.TooltipDialog({content: $(popup)[0]});
				dijit.popup.open({
					parent: $(this)[0],
					popup: appdb.views.VOMembershipList.popup,
					around: $(this)[0],
					orient: {'BR': 'TR', 'BL': 'TL'}
				});
				return false;
			};
		})(d.name, d.discipline));
		$(voname).append(volink);

		var vomembers = $("<div class='vomembertypes'></div>");
		$(vomembers).append(this.addFlatMemberList(d.roles));

		$(li).append(voname).append(vomembers);
		return li;
	};
	this.renderFlatList = function() {
		var ul = $(this.dom).find("ul.vomembership");
		var data = this.options.data;

		$.each(data, (function(self, dom) {
			return function(i, e) {
				var li = self.addFlatItem(e);
				$(dom).append(li);
			};
		})(this, ul));

	};
	this.renderEmpty = function() {
		$(this.dom).empty();
		var empty = $(this.options.dom.empty).clone();
		$(empty).css("display", "block");
		$(this.dom).append(empty);
	};
	this.render = function(d) {
		this.initContainer();
		this.options.data = this.transformData(d);
		if (this.options.data.length === 0) {
			this.renderEmpty();
		} else {
			this.renderFlatList();
		}
	};
	this.initContainer = function() {
		$(this.dom).empty();
		$(this.dom).append($(this.options.dom.list).clone());
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
	};
	this._init();
}, {
	popup: null
});

appdb.views.GenericCheckbox = appdb.ExtendClass(appdb.View, "appdb.views.GenericCheckbox", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		name: $.trim(o.name),
		wasChecked: false,
		checked: false,
		enabled: (typeof o.enabled === "boolean") ? o.enabled : false,
		userChecked: false
	};
	this.resetData = function(userdata) {
		userdata = (typeof userdata === "boolean") ? userdata : false;
		if (userdata === true && this.options.userAction) {
			this.setChecked(this.options.userSelection);
		} else {
			this.setChecked(this.options.wasChecked);
			this.options.userAction = false;
		}
		this.setEnabled(true);
	};
	this.setEnabled = function(enabled) {
		enabled = (typeof enabled === "boolean") ? enabled : true;
		this.options.enabled = enabled;
		if (enabled) {
			$(this.dom).find(".boolbox").removeClass("disabled");
		} else {
			$(this.dom).find(".boolbox").addClass("disabled");
		}
	};
	this.setChecked = function(checked, useraction) {
		checked = (typeof checked === "boolean") ? checked : true;
		useraction = (typeof useraction === "boolean") ? useraction : false;
		this.options.checked = checked;
		if (checked) {
			$(this.dom).find(".boolbox").addClass("checked");
			$(this.dom).find(".boolbox input").addClass("checked").attr("checked", true);
		} else {
			$(this.dom).find(".boolbox").removeClass("checked");
			$(this.dom).find(".boolbox input").removeClass("checked").removeAttr("checked");
		}
		if (useraction === true) {
			this.options.userAction = true;
			this.options.userSelection = checked;
		}
		if (this.hasChanges()) {
			$(this.dom).addClass("changed");
		} else {
			$(this.dom).removeClass("changed");
		}
	};
	this.isEnabled = function() {
		return this.options.enabled;
	};
	this.isChecked = function() {
		return this.options.checked;
	};
	this.getName = function() {
		return this.options.name;
	};
	this.hasChanges = function() {
		return this.options.checked !== this.options.wasChecked;
	};
	this.swapCheck = function() {
		this.options.checked = !this.options.checked;
		this.setChecked(this.options.checked);
	};
	this.render = function() {
		var checked = this.options.checked;
		var enabled = this.options.enabled;
		var name = this.options.name;
		var chkbox = $('<div class="boolbox"><input type="checkbox" value="" name="' + name + '" /><label for="' + name + '"></label></div>');
		if ($(this.dom).children(".boolbox").length > 0) {
			chkbox = $(this.dom).children(".boolbox");
		} else {
			$(this.dom).empty().append(chkbox);
		}
		$(chkbox).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				if (self.options.enabled) {
					self.swapCheck();
					self.options.userAction = true;
					self.options.userSelection = self.isChecked();
					self.publish({event: "checked", value: self});
				}
				return false;
			};
		})(this));
		this.setEnabled(enabled);
		this.setChecked(checked);
		this.publish({event: "render", value: this});
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.userAction = true;
		this.options.userSelection = this.isChecked();
		this.options.checked = (typeof o.checked === "boolean") ? o.checked : false;
		this.options.wasChecked = (typeof o.checked === "boolean") ? o.checked : false;
	};
	this._init();
});
appdb.views.PrivilegeGroupCheckbox = appdb.ExtendClass(appdb.views.GenericCheckbox, "appdb.views.PrivilegeGroupCheckbox", function(o) {
	this.resetData = function(userdata) {
		userdata = (typeof userdata === "boolean") ? userdata : false;
		if (userdata === true && this.options.userAction) {
			this.setChecked(this.options.userSelection);
		} else {
			this.setChecked(this.options.wasChecked);
			this.options.userAction = false;
		}
		this.setEnabled((this.options.wasEnabled === "undefined") ? this.options.privilege.isEditable() : this.options.wasEnabled);
	};
	this._initPrivs = function() {
		this.options.userAction = true;
		this.options.privilege = o.privilege;
		this.options.checked = (typeof o.checked === "boolean") ? o.checked : this.options.privilege();
		this.options.wasChecked = (typeof o.checked === "boolean") ? o.checked : this.options.privilege();
		this.options.userSelection = this.isChecked();
		this.options.enabled = (typeof o.editable === "boolean") ? o.editable : this.options.privilege.isEditable();
		this.options.wasEnabled = this.options.enabled;
		this.options.name = $.trim(this.options.privilege.toString()).toLowerCase().replace(/\s/g, "");
	};
	this._initPrivs();
});

appdb.views.ActorPrivilegeGroup = appdb.ExtendClass(appdb.View, "appdb.views.ActorPrivilegeGroup", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		group: o.group || {},
		dom: {
			list: $("<ul class='actorlistrow'></ul>")
		},
		checkGroups: {},
		state: "idle",
		isRemovable: typeof (o.removable === "boolean") ? o.removable : false,
		checkRemoval: null
	};
	// Empty functions to be overriden
	this.onCheckGroupChange = function() {
		//to be overriden
	};
	this.getAvailableGroups = function() {
		//to be overriden
		return [];
	};
	this.renderList = function() {
		//to be overriden
	};
	this.renderColumns = function() {
		//to be overriden
	};
	this.isValidActor = function() {
		return true;
	};
	//Rest of Implementation
	this.isPrivate = function() {
		return this.parent.isPrivate();
	};
	this.getEntityType = function() {
		return this.parent.getEntityType();
	};
	this.reset = function() {
		for (var i in this.options.checkGroups) {
			if (this.options.checkGroups.hasOwnProperty(i) === false)
				continue;
			if (this.options.checkGroups[i] !== null) {
				this.options.checkGroups[i].unsubscribeAll();
				this.options.checkGroups[i].reset();
				$(this.options.checkGroups[i].dom).empty();
				this.options.checkGroups[i] = null;
				delete this.options.checkGroups[i];
			}
		}
		$(this.dom).empty();
	};
	this.revertChanges = function(userdata) {
		userdata = (typeof userdata === "boolean") ? userdata : false;
		this.options.state = "revertingchanges";
		var manvers = null;
		for (var i in this.options.checkGroups) {
			if (this.options.checkGroups.hasOwnProperty(i) === false)
				continue;
			if (!this.options.checkGroups[i])
				continue;
			this.options.checkGroups[i].resetData(userdata);
			if ($.trim(this.options.checkGroups[i].options.privilege).toLowerCase() === "fullcontrol") {
				this.onFullControl(this.options.checkGroups[i]);
			} else if ($.trim(this.options.checkGroups[i].options.privilege).toLowerCase() === "manageversions") {
				manvers = this.options.checkGroups[i];
			}
		}
		if (manvers !== null) {
			this.onManageVersion(manvers);
		}
		if (this.options.checkRemoval !== null) {
			this.options.checkRemoval.resetData(false);
			$(this.dom).removeClass("toberemoved");
		}
		this.options.state = "idle";
	};
	this.getCheckGroup = function(groupname) {
		var cname = $.trim(groupname).toLowerCase() + this.options.group.id;
		for (var i in this.options.checkGroups) {
			if (this.options.checkGroups.hasOwnProperty(i) === false)
				continue;
			if (i === cname)
				return this.options.checkGroups[i];
		}
		return null;
	};
	this.getUserGroupType = function(exclude) {
		exclude = exclude || [];
		exclude = $.isArray(exclude) ? exclude : [exclude];
		if (this.isPartialActor())
			return {id: "-1000", name: "Power User"};

		var permgroups = this.getPermissionGroup();
		var rules = permgroups.getRules();
		var groupname = null;
		var systemrules = appdb.config.permissions.rules || [];
		$.each(rules, function(i, e) {
			if ($.inArray($.trim(e), exclude) > -1)
				return; //exclude owner and contact rules
			if (groupname !== null)
				return;
			var foundrules = $.grep(systemrules, function(ee) {
				return ($.trim(e) === $.trim(ee.id) || ($.trim(e)[0] !== "-" && ee.id === "-1000"));
			});
			if (foundrules.length > 0) {
				groupname = foundrules[0];
			}
		});
		if (groupname === null)
			return  {id: "0", name: "Unknown", actors: "", description: "Could not retrieve actor group"};

		return groupname;

	};
	this.getUserGroupHtml = function(exclude) {
		var grp = this.getUserGroupType(exclude);
		return "<span class='usergroup " + $.trim(grp["name"]).toLowerCase().replace(/\s/g, "") + "'>" + $.trim(grp["name"]) + "</span>";
	};
	this.isUserGroup = function(id) {
		var permgroups = this.getPermissionGroup();
		var rules = permgroups.getRules();
		var found = $.grep(rules, function(e) {
			return $.trim(e) === $.trim(id);
		});
		return (found.length > 0);
	};
	this.getPermissionGroup = function() {
		return this.options.group.permissionGroups;
	};
	this.isSystemActor = function() {
		return this.getPermissionGroup().isSystemGroup();
	};
	this.isPartialActor = function() {
		return this.getPermissionGroup().isPartialGroup();
	};
	this.hasChanges = function() {
		for (var i in this.options.checkGroups) {
			if (this.options.checkGroups.hasOwnProperty(i) === false)
				continue;
			if (this.options.checkGroups[i].hasChanges() === true)
				return true;
		}
		return false;
	};
	this.isNewUser = function() {
		if (this.options.group && this.options.group.isNewUser === true) {
			return true;
		}
		return false;
	};
	this.setGroupsCheckValue = function(value, groupnames) {
		value = (typeof value === "boolean") ? value : false;
		groupnames = groupnames || [];
		groupnames = $.isArray(groupnames) ? groupnames : [groupnames];

		if (groupnames.length === 0) {
			groupnames = this.getAvailableGroups();
		}

		$.each(groupnames, (function(self) {
			return function(i, e) {
				var grp = self.getCheckGroup(e);
				if (grp === null)
					return;
				grp.setChecked(value, true);
			};
		})(this));
	};
	this.getGroupsCheckValues = function() {
		var groupnames = this.getAvailableGroups();
		var res = {};
		$.each(groupnames, (function(self) {
			return function(i, e) {
				var grp = self.getCheckGroup(e);
				if (grp === null)
					return;
				res[e] = grp.isChecked();
			};
		})(this));
		return res;
	};
	this.getChanges = function() {
		var changes = {id: this.options.group.id, suid: this.options.group.suid, grant: [], revoke: []};
		for (var i in this.options.checkGroups) {
			if (this.options.checkGroups.hasOwnProperty(i) === false || this.options.checkGroups[i].hasChanges() === false || this.options.checkGroups[i].options.privilege.isEditable() === false)
				continue;
			if (this.options.checkGroups[i].isChecked()) {
				changes.grant.push(this.options.checkGroups[i].options.privilege.toString());
			} else {
				changes.revoke.push(this.options.checkGroups[i].options.privilege.toString());
			}
		}
		return changes;
	};
	this.onCheckGroupChange = function() {

	};
	this.addCheckGroup = function(name, priv, dom, ext) {
		if (this.options.checkGroups && this.options.checkGroups[name]) {
			this.options.checkGroups[name].reset();
		}
		var p_iseditable = (priv.isEditable && priv.isEditable()) ? true : false;
		if (this.isSuperGroupActor()) {
			p_iseditable = false;
		} else {
			p_iseditable = (typeof ext.editable === "boolean") ? ext.editable : p_iseditable;
		}
		var chkopts = {container: dom, parent: this, name: name, privilege: priv, editable: p_iseditable};
		if (typeof ext.checked === "boolean" && ext.checked === true) {
			chkopts.checked = true;
		}
		this.options.checkGroups[name] = new appdb.views.PrivilegeGroupCheckbox(chkopts);
		return this.options.checkGroups[name];
	};
	this.canEditPrivileges = function() {
		var perms = appdb.pages.application.currentPermissions();
		if (perms) {
			return perms.canGrantPrivilege();
		}
		return false;
	};
	this.isSuperGroupActor = function() {
		var issuper = (this.isOwner() || this.isAddedBy() || this.isUserGroup("-4") || this.isUserGroup("-3") || this.isUserGroup("-9") || this.isUserGroup("-1"));
		return issuper;
	};
	this.getUserGroup = function() {

	};
	this.onChange = function() {
		if (this.options.state !== "idle")
			return;
		this.publish({event: "changed", value: this});
	};
	this.createGroupItem = function(name, subscriptions, ext) {
		var permgroups = this.getPermissionGroup();
		name = $.trim(name);
		subscriptions = subscriptions || [];
		subscriptions = $.isArray(subscriptions) ? subscriptions : [subscriptions];
		ext = ext || {};
		if (name === "")
			return null;
		var cname = name.toLowerCase();
		var p = permgroups["can" + name];
		var p_iseditable = (p.isEditable && p.isEditable()) ? true : false;

		if (this.canEditPrivileges() === false) {
			p_iseditable = false;
			ext.editable = p_iseditable;
		} else if (this.isOwner() || this.isAddedBy() || this.isUserGroup("-4")) {
			p_iseditable = false;
		} else {
			p_iseditable = (typeof ext.editable === "boolean") ? ext.editable : p_iseditable;
		}
		var item = $("<li class='groupedprivilege " + cname + ((p_iseditable === true) ? " editable" : "") + (p() ? " granted" : " notgranted") + "'></li>");
		var chk = this.addCheckGroup(cname + this.options.group.id, p, item, ext);
		$.each(subscriptions, function(i, e) {
			chk.subscribe(e);
		});
		chk.subscribe({event: "checked", callback: this.onChange, caller: this});
		chk.render();
		return item;
	};
	this.getAvailableGroups = function() {
		return [];
	};
	this.getEditableGroups = function() {
		var permgroups = this.getPermissionGroup();
		var groups = this.getAvailableGroups();
		var res = $.grep(groups, function(e) {
			var p = permgroups["can" + e];
			return p.isEditable();
		});
		return res;
	};
	this.createRemovable = function() {
		var editable = this.getEditableGroups();
		var li = $('<li class="groupedprivilege remove"></li>');
		if (editable.length > 0 && this.options.isRemovable === true) {
			this.options.checkRemoval = new appdb.views.GenericCheckbox({container: li, parent: this, name: "removal" + this.options.group.id, enabled: true});
			this.options.checkRemoval.render();
			this.options.checkRemoval.subscribe({event: "checked", callback: function(v) {
					if (v.isChecked()) {
						for (var i in this.options.checkGroups) {
							if (this.options.checkGroups.hasOwnProperty(i) === false)
								continue;
							if (this.options.checkGroups[i].isChecked()) {
								this.options.checkGroups[i].setChecked(false);
								this.options.checkGroups[i].setEnabled(false);
							}
							this.options.checkGroups[i].setEnabled(false);
						}
						$(this.dom).addClass("toberemoved");
					} else {
						for (var i in this.options.checkGroups) {
							if (this.options.checkGroups.hasOwnProperty(i) === false || this.options.checkGroups[i].isEnabled() === false)
								continue;
							this.options.checkGroups[i].resetData(true);
						}
						this.revertChanges(true);
					}
					setTimeout((function(self) {
						return function() {
							self.publish({event: "changed", value: self});
						};
					})(this), 10);
				}, caller: this});
		}
		return li;
	};
	this.onFullControl = function(v) {
		var manvers = null;
		if (v.isEnabled()) {
			for (var i in this.options.checkGroups) {
				if (this.options.checkGroups.hasOwnProperty(i) === false)
					continue;
				if (this.options.checkGroups[i].getName() === v.getName())
					continue;
				if ($.trim(this.options.checkGroups[i].options.privilege).toLowerCase() === "manageversions")
					manvers = this.options.checkGroups[i];
				if (v.isChecked()) {
					this.options.checkGroups[i].setChecked(true);
					this.options.checkGroups[i].setEnabled(false);
				} else {
					this.options.checkGroups[i].resetData(true);
				}
			}
		}
		if (manvers !== null) {
			this.onManageVersion(manvers);
		}
	};
	this.onManageVersion = function(v) {
		if (v.isEnabled()) {
			for (var i in this.options.checkGroups) {
				if (this.options.checkGroups.hasOwnProperty(i) === false)
					continue;
				if ($.trim(this.options.checkGroups[i].getName()) !== "accessversions")
					continue;
				if (v.isChecked()) {
					this.options.checkGroups[i].setChecked(true);
					this.options.checkGroups[i].setEnabled(false);
				} else {
					this.options.checkGroups[i].resetData(true);
				}
			}
		}
	};
	this.getEntityData = function() {
		return this.parent.getEntityData();
	};
	this.isAddedBy = function() {
		var d = this.getEntityData();
		if (d && d.addedby && $.trim(d.addedby.id) === $.trim(this.options.group.id)) {
			return true;
		}
		return false;
	};
	this.isOwner = function() {
		var d = this.getEntityData();
		if (d && d.owner && $.trim(d.owner.id) === $.trim(this.options.group.id)) {
			return true;
		}
		return false;
	};
	this.renderHeader = function() {
		var groupname = "";

		$(this.dom).children("ul").append(this.createRemovable());

		if (this.isUserGroup("-4")) {
			if (this.isAddedBy()) {
				groupname = "<span class='grouptype'>(submitter)</span>";
			} else if (this.isOwner()) {
				groupname = "<span class='grouptype'>(owner)</span>";
			}
		} else if (this.isSuperGroupActor()) {
			groupname = "<span class='grouptype'>(system user)</span>";
		}
		var name = $("<li class='groupedprivilege user'><span>" + this.options.group.name + groupname + "</span></li>");
		$(this.dom).children("ul").append(name);
	};
	this.render = function(d) {
		this._initContainer();
		if (typeof d !== "undefined") {
			this.options.group = d;
		}
		if (this.options.isRemovable === true) {
			$(this.dom).addClass("removable");
		}
		this.renderHeader();
		this.renderColumns();
		this.renderList();
		this.publish({event: "render", value: this});
	};
	this._initContainer = function() {
		this.reset();
		$.each(this.subviews, function(i, e) {
			if (e.unsubscribeAll)
				e.unsubscribeAll();
			if (e.reset)
				e.reset();
			e = null;
		});
		this.subviews = [];
		$(this.dom).empty();
		$(this.dom).append($(this.options.dom.list).clone());
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
	};
	this._init();
});
appdb.views.SoftwareActorPrivilegeGroup = appdb.ExtendClass(appdb.views.ActorPrivilegeGroup, "appdb.views.SoftwareActorPrivilegeGroup", function() {
	this.renderList = function() {
		$(this.dom).find(".actorlistrow").append(this.createGroupItem("EditMetaData")).
				append(this.createGroupItem("ManageReleases")).
				append(this.createGroupItem("FullControl", [
					{event: "render", callback: this.onFullControl, caller: this},
					{event: "checked", callback: this.onFullControl, caller: this}
				]));
	};
	this.getAvailableGroups = function() {
		return ["EditMetaData", "ManageReleases", "FullControl"];
	};
}, {
	renderColumns: function(el) {
		$(el).append("<li class='groupedprivilege remove'><span class='complex'></span></li>");
		$(el).append("<li class='groupedprivilege user'><span class='complex'><span >User</span></span></li>");
		$(el).append("<li class='groupedprivilege editmetadata'><span class='complex'><span class='title'>Information & Publications</span><span class='privitem edit'><span>edit</span></span></span></li>");
		$(el).append("<li class='groupedprivilege managereleases'><span class='complex'><span class='title'>Software Releases</span><span class='privitem manage'><span>manage</span></span></span></li>");
		$(el).append("<li class='groupedprivilege fullcontrol privitem'><span>Full Control</span></li>");
	},
	renderHelpMessages: function(el, parent) {
		var entityType = parent.getEntityType();
		var types = [
			{name: "metadata", sel: ".privitem.edit", list: appdb.utils.PermissionActions.getByGroupName("metadata", entityType), wiki: "software.permissions.editmetadata"},
			{name: "releases", sel: ".privitem.manage", list: appdb.utils.PermissionActions.getByGroupName("releases", entityType), wiki: "software.permissions.managereleases"},
			{name: "full", sel: ".privitem.fullcontrol", list: appdb.utils.PermissionActions.getByGroupName("full", entityType), wiki: "software.permissions.fullcontrol"}
		];
		$.each(types, function(i, e) {
			var names = [];
			$.each(e.list, function(ii, ee) {
				if (ee.hidden === true)
					return;
				names.push(ee.name.replace(/change software/gi, "Change").replace(/Manage software VAs/gi, "Manage Virtual Appliance"));
			});
			e.actionNames = $.unique(names);
			e.actionNames.sort();
		});
		$.each(types, function(i, e) {
			var ul = $("<ul></ul>");
			$.each(e.actionNames, function(ii, ee) {
				$(ul).append("<li>" + ee + "</li>");
			});
			var wiki = $("<li><a class='wiki-link icontext' data-wiki-link='" + appdb.config.wiki[e.wiki] + "' href='/' target='_blank' title='Learn more about this set of permissions'><span>...learn more</span></a></li>");
			$(ul).append(wiki);
			e.htmllist = ul;
		});
		$.each(types, function(i, e) {
			var dom = $(el).find(e.sel);
			if ($(dom).length === 0)
				return;
			if ($(dom).children(".popup.help").length > 0) {
				$(dom).children(".popup.help").remove();
			}
			var html = $("<div class='popup help'></div>");
			var header = $("<div class='header'>?</div>");
			var message = $("<div class='content'></div>");
			$(html).append(header).append(message);
			$(message).append(e.htmllist);
			$(dom).append(html);
		});
		appdb.utils.setupWikiLinks(el);
	}
});
appdb.views.VApplianceActorPrivilegeGroup = appdb.ExtendClass(appdb.views.ActorPrivilegeGroup, "appdb.views.VApplianceActorPrivilegeGroup", function() {
	this.renderList = function() {
		var isPrivate = this.parent.isPrivate() || false;
		$(this.dom).find(".actorlistrow").append(this.createGroupItem("EditMetaData")).
				append(this.createGroupItem("AccessVersions", [], {editable: isPrivate, checked: !isPrivate})).
				append(this.createGroupItem("ManageVersions", [
					{event: "render", callback: this.onManageVersion, caller: this},
					{event: "checked", callback: this.onManageVersion, caller: this}
				])).
				append(this.createGroupItem("FullControl", [
					{event: "render", callback: this.onFullControl, caller: this},
					{event: "checked", callback: this.onFullControl, caller: this}
				]));
	};
	this.getAvailableGroups = function() {
		return ["EditMetaData", "AccessVersions", "ManageVersions", "FullControl"];
	};
}, {
	renderColumns: function(el) {
		$(el).append("<li class='groupedprivilege remove'><span></span></li>");
		$(el).append("<li class='groupedprivilege user'><span class='complex'><span >User</span></span></li>");
		$(el).append("<li class='groupedprivilege editmetadata'><span class='complex'><span class='title'>Information & Publications</span><span class='privitem edit'><span>edit</span></span></span></li>");
		$(el).append("<li class='groupedprivilege manageversions'><span class='complex'><span class='title'>Virtual Appliance Versions</span><span class='privitem accessversions'><span>access private data</span></span><span class='privitem manageversions'><span>manage</span></span></span></li>");
		$(el).append("<li class='groupedprivilege fullcontrol privitem'><span>Full control</span></li>");
	},
	renderHelpMessages: function(el, parent) {
		var entityType = parent.getEntityType();
		var types = [
			{name: "metadata", sel: ".privitem.edit", list: appdb.utils.PermissionActions.getByGroupName("metadata", entityType), wiki: "vappliance.permissions.editmetadata"},
			{name: "accessvaversions", sel: ".privitem.accessversions", list: appdb.utils.PermissionActions.getByGroupName("accessvaversions", entityType), wiki: "vappliance.permissions.accessversions"},
			{name: "vaversions", sel: ".privitem.manageversions", list: appdb.utils.PermissionActions.getByGroupName("vaversions", entityType), wiki: "vappliance.permissions.manageversions"},
			{name: "full", sel: ".privitem.fullcontrol", list: appdb.utils.PermissionActions.getByGroupName("full", entityType), wiki: "vappliance.permissions.fullcontrol"}
		];

		$.each(types, function(i, e) {
			var names = [];
			$.each(e.list, function(ii, ee) {
				names.push(ee.name.replace(/change software/gi, "Change").replace(/Manage software VAs/gi, "Manage Virtual Appliance"));
			});
			e.actionNames = $.unique(names);
			e.actionNames.sort();
		});
		$.each(types, function(i, e) {
			var ul = $("<ul></ul>");
			$.each(e.actionNames, function(ii, ee) {
				$(ul).append("<li>" + ee + "</li>");
			});
			var wiki = $("<li><a class='wiki-link icontext' data-wiki-link='" + appdb.config.wiki[e.wiki] + "' href='/' target='_blank' title='Learn more about this set of permissions'><span>...learn more</span></a></li>");
			$(ul).append(wiki);
			e.htmllist = ul;
		});
		$.each(types, function(i, e) {
			var dom = $(el).find(e.sel);
			if ($(dom).length === 0)
				return;
			if ($(dom).children(".popup.help").length > 0) {
				$(dom).children(".popup.help").remove();
			}
			var html = $("<div class='popup help'></div>");
			var header = $("<div class='header'>?</div>");
			var message = $("<div class='content'></div>");
			$(html).append(header).append(message);
			$(message).append(e.htmllist);
			$(dom).append(html);
		});
		appdb.utils.setupWikiLinks(el);
	}
});
appdb.views.SWApplianceActorPrivilegeGroup = appdb.ExtendClass(appdb.views.ActorPrivilegeGroup, "appdb.views.SWApplianceActorPrivilegeGroup", function() {
	this.renderList = function() {
		$(this.dom).find(".actorlistrow").append(this.createGroupItem("EditMetaData")).
				append(this.createGroupItem("ManageContextScripts")).
				append(this.createGroupItem("FullControl", [
					{event: "render", callback: this.onFullControl, caller: this},
					{event: "checked", callback: this.onFullControl, caller: this}
				]));
	};
	this.getAvailableGroups = function() {
		return ["EditMetaData", "ManageContextScripts", "FullControl"];
	};
}, {
	renderColumns: function(el) {
		$(el).append("<li class='groupedprivilege remove'><span class='complex'></span></li>");
		$(el).append("<li class='groupedprivilege user'><span class='complex'><span >User</span></span></li>");
		$(el).append("<li class='groupedprivilege editmetadata'><span class='complex'><span class='title'>Information & Publications</span><span class='privitem edit'><span>edit</span></span></span></li>");
		$(el).append("<li class='groupedprivilege managecontextscripts'><span class='complex'><span class='title'>Context Scripts</span><span class='privitem manage'><span>manage</span></span></span></li>");
		$(el).append("<li class='groupedprivilege fullcontrol privitem'><span>Full Control</span></li>");
	},
	renderHelpMessages: function(el, parent) {
		var entityType = parent.getEntityType();
		var types = [
			{name: "metadata", sel: ".privitem.edit", list: appdb.utils.PermissionActions.getByGroupName("metadata", entityType), wiki: "swappliance.permissions.editmetadata"},
			{name: "contextscripts", sel: ".privitem.manage", list: appdb.utils.PermissionActions.getByGroupName("contextscripts", entityType), wiki: "swappliance.permissions.managecontextscripts"},
			{name: "full", sel: ".privitem.fullcontrol", list: appdb.utils.PermissionActions.getByGroupName("full", entityType), wiki: "swappliance.permissions.fullcontrol"}
		];
		$.each(types, function(i, e) {
			var names = [];
			$.each(e.list, function(ii, ee) {
				if (ee.hidden === true)
					return;
				names.push(ee.name.replace(/change software/gi, "Change").replace(/Manage software VAs/gi, "Manage Virtual Appliance"));
			});
			e.actionNames = $.unique(names);
			e.actionNames.sort();
		});
		$.each(types, function(i, e) {
			var ul = $("<ul></ul>");
			$.each(e.actionNames, function(ii, ee) {
				$(ul).append("<li>" + ee + "</li>");
			});
			var wiki = $("<li><a class='wiki-link icontext' data-wiki-link='" + appdb.config.wiki[e.wiki] + "' href='/' target='_blank' title='Learn more about this set of permissions'><span>...learn more</span></a></li>");
			$(ul).append(wiki);
			e.htmllist = ul;
		});
		$.each(types, function(i, e) {
			var dom = $(el).find(e.sel);
			if ($(dom).length === 0)
				return;
			if ($(dom).children(".popup.help").length > 0) {
				$(dom).children(".popup.help").remove();
			}
			var html = $("<div class='popup help'></div>");
			var header = $("<div class='header'>?</div>");
			var message = $("<div class='content'></div>");
			$(html).append(header).append(message);
			$(message).append(e.htmllist);
			$(dom).append(html);
		});
		appdb.utils.setupWikiLinks(el);
	}
});
appdb.views.SystemActorPrivilegeGroup = appdb.ExtendClass(appdb.views.ActorPrivilegeGroup, "appdb.views.SystemActorPrivilegeGroup", function(o) {
	this.hasChanges = function() {
		return false;
	};
	this.getChanges = function() {
		return {id: this.options.group.id, suid: this.options.group.suid, grant: [], revoke: []};
	};
	this.getAccessType = function() {
		var el = $("<span></span>");
		var usergrouptype = this.getUserGroupType(["-1", "-2", "-3", "-4", "-5", "-6", "-7", "-8", "-9"]);

		var permgroups = this.getPermissionGroup();
		var fullcontrol = permgroups.canFullControl();
		var editmetadata = permgroups.canEditMetaData();
		var managereleases = permgroups.canManageReleases();
		var manageversions = permgroups.canManageVersions();
		var special = this.getAccessPermissions();
		if (fullcontrol === true) {
			$(el).append("<span class='fullcontrol'>Full Control</span>");
		} else if (usergrouptype.id !== "-1000" && $.trim(special) === "") {
			if (editmetadata) {
				$(el).append("<span class='editmetadata'>Edit Metadata</span>");
			}
			if (managereleases && this.getEntityType() === "software") {
				$(el).append("<span class='managereleases'>Manage Releases</span>");
			}
			if (manageversions && this.getEntityType() === "vappliance") {
				$(el).append("<span class='manageversions'>Manage Versions</span>");
			}
		} else if (usergrouptype.id === "-1000" && $.trim(special) !== "") {
			$(el).append(this.getAccessPermissions());
		}

		return $(el).html();
	};
	this.getAccessPermissions = function() {
		var permgroups = this.getPermissionGroup();

		var privs = permgroups.getExplicitPrivileges();
		var el = $("<span></span>");
		$.each(privs, function(i, e) {
			if (e() === true) {
				$(el).append("<span class='privilege " + $.trim(e.toString()).toLowerCase().replace(/\s/g, "") + "'>" + $.trim(e.toString()).replace(/software/g, '') + "</span>");
			}
		});
		if ($(el).children("span").length > 0) {
			return $(el).html();
		}
		return "";

	};
	this.isValidActor = function() {
		return !(this.isAddedBy() || this.isOwner());
	};
	this.renderList = function() {
		var group = $("<li class='groupedprivilege usergroup'>" + this.getUserGroupHtml(["-4", "-8"]) + "</li>");
		var access = $("<li class='groupedprivilege accesstype'>" + this.getAccessType() + "</li>");

		$(this.dom).find(".actorlistrow").append(group).append(access);
	};
}, {
	renderColumns: function(el) {
		$(el).append("<li class='groupedprivilege user'><span>User</span></li>");
		$(el).append("<li class='groupedprivilege usergroup'><span>Group</span></li>");
		$(el).append("<li class='groupedprivilege accesstype'><span>Access Types</span></li>");
	}
});
appdb.views.PermissionGroupListItem = appdb.ExtendClass(appdb.View, "appdb.views.PermissionGroupListItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		actorPrivilegeType: null,
		group: o.group, //object<appdb.utils.EntityPermissions>
		dom: {
			header: $("<div class='header'></div>"),
			content: $("<div class='content'></div>"),
			columns: $("<ul class='actorlist columns'></ul>"),
			list: $("<ul class='actorlist datalist'></ul>"),
			empty: $('<div class="emptycontent"><div class="content"><img src="/images/exclam16.png"><span>No users defined</span></div></div>')
		},
		state: "idle",
		removable: (typeof o.removable === "boolean") ? o.removable : false
	};
	this.getSystemActors = function() {
		return this.parent.getSystemActors();
	};
	this.getEntityType = function() {
		return this.parent.getEntityType();
	};
	this.isPrivate = function() {
		return this.parent.isPrivate();
	};
	this.getName = function() {
		return this.options.group.getActorGroupName();
	};
	this.getEntityData = function() {
		return this.parent.getEntityData();
	};
	this.getEmptyPermissionActors = function() {
		var res = [];
		$.each(this.subviews, function(i, e) {
			var chks = e.getGroupsCheckValues();
			var isempty = true;
			for (var c in chks) {
				if (chks.hasOwnProperty(c) === false)
					continue;
				if (chks[c] === true) {
					isempty = false;
				}
			}
			if (isempty === true) {
				res.push(e);
			}
		});
		return res;
	};
	this.hasChanges = function() {
		var changed = $.grep(this.subviews, function(e) {
			return e.hasChanges();
		});
		return (changed.length > 0);
	};
	this.getChanges = function() {
		if (this.hasChanges() === false) {
			return [];
		}
		var changes = [];
		$.each(this.subviews, function(i, e) {
			if (e.hasChanges() === false)
				return;
			changes.push(e.getChanges());
		});
		return changes;
	};
	this.revertChanges = function() {
		this.options.state = "revertingchanges";
		$.each(this.subviews, function(i, e) {
			e.revertChanges();
		});
		this.options.state = "idle";
	};
	this.getNewUsers = function() {
		var res = [];
		if (!this.subviews || this.subviews.length === 0)
			return [];
		$.each(this.subviews, function(i, e) {
			var group = e.options.group;
			if (!group || group.isNewUser !== true)
				return;
			res.push(group);
		});
		return res;
	};
	this.removeNewUsers = function() {
		var rem = [];
		$.each(this.subviews, function(i, e) {
			if (e.isNewUser() === false)
				return;
			rem.push(i);
		});
		if (rem.length === 0)
			return;
		for (var i = rem.length - 1; i >= 0; i -= 1) {
			this.subviews[rem[i]].unsubscribeAll();
			this.subviews[rem[i]].reset();
			this.subviews[rem[i]] = null;
			this.subviews.splice(rem[i], 1);
		}
		if (this.subviews.length === 0) {
			this.renderEmpty(true);
		}
	};
	this.onChange = function() {
		if (this.options.state !== "idle")
			return;
		this.publish({event: "changed", value: this});
	};
	this.onItemRender = function(source) {
		var group = source.options.group;
		if (!group || group.isNewUser !== true)
			return;
		source.setGroupsCheckValue(true, "EditMetaData");

		if (this.isPrivate() === false) {
			source.setGroupsCheckValue(true, "AccessVersions");
		}
		this.publish({event: "changed", value: this});
	};
	this.appendItem = function(item) {
		if (!item)
			return;
		this.renderEmpty(false);
		var ul = $(this.dom).find("ul.actorlist.datalist");
		var li = this.addItem(item);
		$(ul).append(li);
	};
	this.addItem = function(item) {
		var li = $("<li class='actorprivilegegroup'></li>");

		var gitmeopts = {container: li, parent: this, group: item, removable: this.options.removable};
		var gitem = new this.options.actorPrivilegeType(gitmeopts);
		if (gitem.isValidActor() === false) {
			gitem.reset();
			gitem = null;
			return null;
		}
		gitem.subscribe({event: "changed", callback: this.onChange, caller: this});
		gitem.subscribe({event: "render", callback: this.onItemRender, caller: this});
		gitem.render();
		this.subviews.push(gitem);
		return li;
	};
	this.orderListItemsBy = function(list, ord) {
		ord = ord || [];
		ord = $.isArray(ord) ? ord : [ord];
		var ordList = [];
		$.each(ord, function(i, e) {
			if ((i + 1) < ord.length) {
				ordList.push({value: ord[i], hasNext: true, next: i + 1});
			} else {
				ordList.push({value: ord[i], hasNext: false});
			}
		});
		var cmpFunc = function(a, b, o) {
			var aa = $(a).find("ul > li.groupedprivilege." + o.value).text();
			var bb = $(b).find("ul > li.groupedprivilege." + o.value).text();
			if (aa < bb)
				return -1;
			if (aa > bb)
				return 1;
			if (o.hasNext === true) {
				return cmpFunc(a, b, ordList[o.next]);
			}
			return 0;
		};
		var ordFunc = function(o) {
			list.sort(function(a, b) {
				return cmpFunc(a, b, o);
			});
		};
		if (ordList.length > 0) {
			ordFunc(ordList[0]);
		}
	};
	this.renderList = function() {
		var ul = $(this.dom).find("ul.actorlist.datalist");
		var group = this.options.group;
		var data = group.getAllPrivileges();
		var doms = [];
		$.each(data, (function(self, dom) {
			return function(i, e) {
				if (!e || e !== null) {
					var li = self.addItem(e);
					if (li !== null) {
						doms.push(li);
					}
				}
			};
		})(this, ul));
		if ($.inArray(this.options.group.getActorGroupName(), ["contacts", "explicit"]) === -1) {
			this.orderListItemsBy(doms, ["usergroup", "user"]);
		}
		$.each(doms, function(i, e) {
			$(ul).append(e);
		});
	};
	this.renderEmpty = function(isempty) {
		isempty = (typeof isempty === "boolean") ? isempty : true;
		$(this.dom).removeClass("empty").find(".actorlist.datalist .emptycontent").remove();

		if (isempty) {
			var empty = $(this.options.dom.empty).clone();
			$(empty).css("display", "block");
			$(this.dom).find(".actorlist.datalist").append(empty);
			$(this.dom).addClass("empty");
		}
	};
	this.renderHeader = function() {
		var dname = this.options.group.getActorGroupName();
		if ($.trim(this.options.group.displayName) !== "") {
			dname = this.options.group.displayName;
		}
		if (dname.length > 0) {
			dname = dname[0].toUpperCase() + dname.slice(1);
		}
		var name = $("<div class='groupname'>" + dname + "</div>");
		$(this.dom).children(".header").empty().append(name);
	};
	this.renderColumns = function() {
		if (this.options.actorPrivilegeType.renderColumns) {
			var li = $("<li class='actorprivilegegroup columns'></li>");
			if (this.options.removable === true) {
				$(li).addClass("removable");
			}
			var ul = $("<ul class='actorlistrow'></ul>");
			$(li).append(ul);

			$(this.dom).find("ul.actorlist.columns").prepend(li);
			this.options.actorPrivilegeType.renderColumns(ul);
			if (this.options.actorPrivilegeType.renderHelpMessages) {
				this.options.actorPrivilegeType.renderHelpMessages(ul, this);
			}
		}
	};
	this.render = function(d) {
		this._initContainer();
		if (typeof d !== "undefined") {
			this.options.group = d;
		}
		this.renderHeader();
		this.renderColumns();
		this.renderList();
		if ((this.options.group.getAllPrivileges() || []).length === 0) {
			this.renderEmpty(true);
		} else {
			this.renderEmpty(false);
		}
	};
	this._initContainer = function() {
		$.each(this.subviews, function(i, e) {
			if (e.unsubscribeAll)
				e.unsubscribeAll();
			if (e.reset)
				e.reset();
			e = null;
		});
		this.subviews = [];
		$(this.dom).empty();
		$(this.dom).append($(this.options.dom.header).clone());
		$(this.dom).append($(this.options.dom.content).clone());
		$(this.dom).children(".content").append($(this.options.dom.columns).clone());
		$(this.dom).children(".content").append($(this.options.dom.list).clone());
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		var actorgroupname = this.options.group.getActorGroupName();
		if ($.inArray($.trim(actorgroupname).toLowerCase(), ["contacts", "explicit"]) > -1) {
			switch (this.options.group.entityType) {
				case "vappliance":
					this.options.actorPrivilegeType = appdb.views.VApplianceActorPrivilegeGroup;
					break;
				case "swappliance":
					this.options.actorPrivilegeType = appdb.views.SWApplianceActorPrivilegeGroup;
					break;
				case "software":
				default:
					this.options.actorPrivilegeType = appdb.views.SoftwareActorPrivilegeGroup;
					break;
			}
		} else {
			this.options.actorPrivilegeType = appdb.views.SystemActorPrivilegeGroup;
		}
	};
	this._init();
});
appdb.views.PermissionGroupList = appdb.ExtendClass(appdb.View, "appdb.views.PermissionGroupList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		groups: o.groups || [], //array<appdb.utils.EntityPermissions>
		dom: {
			list: $("<ul class='permissiongrouplist'></ul>"),
			empty: $('<div class="emptycontent" ><div class="content"><img src="/images/exclam16.png"><span>No permission group found.</span></div></div>')
		},
		removableItems: false,
		state: "idle",
		emptyGroupWarning: o.emptyGroupWarning || "",
		disableMessage: o.disableMessage || "There are pending changes which need to be saved first, in order to edit this list."
	};
	this.getSystemActors = function() {
		return this.parent.getSystemActors();
	};
	this.getEntityType = function() {
		return this.parent.getEntityType();
	};
	this.isPrivate = function() {
		return this.parent.isPrivate();
	};
	this.getEntityData = function() {
		return this.parent.getEntityData();
	};
	this.getNewUsers = function() {
		var newusers = [];
		var uniq = {};
		$.each(this.subviews, function(i, e) {
			var nus = e.getNewUsers();
			if (nus.length === 0)
				return;
			$.each(nus, function(ii, ee) {
				uniq[ee.suid] = ee;
			});
		});
		for (var u in uniq) {
			if (uniq.hasOwnProperty(u) === false)
				continue;
			newusers.push(uniq[u]);
		}
		return newusers;
	};
	this.hasChanges = function() {
		var changed = $.grep(this.subviews, function(e) {
			return e.hasChanges();
		});
		return (changed.length > 0);
	};
	this.getChanges = function() {
		var changes = [];
		$.each(this.subviews, function(i, e) {
			if (e.hasChanges()) {
				changes.push({group: e.options.group.getActorGroupName(), changes: e.getChanges()});
			}
		});
		return changes;
	};
	this.revertChanges = function() {
		this.options.state = "revertingchanges";
		$.each(this.subviews, function(i, e) {
			e.revertChanges();
		});
		this.options.state = "idle";
	};
	this.removeNewUsers = function() {
		$.each(this.subviews, function(i, e) {
			e.removeNewUsers();
		});
	};
	this.onChange = function(v) {
		if (this.options.state !== "idle")
			return;
		if (this.hasChanges()) {
			$(this.dom).addClass("changed");
		} else {
			$(this.dom).removeClass("changed");
		}
		if ($.trim(this.options.emptyGroupWarning) !== "") {
			var hasemptyactors = false;

			$.each(this.subviews, (function(self) {
				return function(i, e) {
					if (e.getEmptyPermissionActors().length > 0) {
						hasemptyactors = true;
					}
				};
			})(this));

			if (hasemptyactors === true) {
				this.renderWarning(true, this.options.emptyGroupWarning);
			} else {
				this.renderWarning(false);
			}
		}
		this.publish({event: "changed", value: this});
	};
	this.getItemByName = function(name) {
		name = $.trim(name).toLowerCase();
		if (name === "")
			return null;
		var res = $.grep(this.subviews, function(e) {
			return (e.getName() === name);
		});
		return (res.length > 0) ? res[0] : null;
	};
	this.addItem = function(item) {
		var li = $("<li></li>");
		var div = $("<div class='permissiongrouplistitem'></div>");
		$(div).addClass(item.getActorGroupName());
		$(li).append(div);
		var gitem = new appdb.views.PermissionGroupListItem({container: div, parent: this, group: item, removable: this.options.removableItems});
		gitem.subscribe({event: "changed", callback: this.onChange, caller: this});
		gitem.render();
		this.subviews.push(gitem);
		return li;
	};
	this.renderList = function() {
		var ul = $(this.dom).find("ul.permissiongrouplist");
		var data = this.options.groups || [];

		$.each(data, (function(self, dom) {
			return function(i, e) {
				if (!e || e !== null) {
					var li = self.addItem(e);
					if ($(li).find(".permissiongrouplistitem > .content > ul.actorlist > li").length > 0) {
						$(dom).append(li);
					}
				}
			};
		})(this, ul));
	};
	this.renderWarning = function(enable, text) {
		enable = (typeof enable === "boolean") ? enable : true;
		text = text || "";
		$(this.dom).removeClass("showwarning").find(".warning .message").empty();
		if (enable === true) {
			$(this.dom).addClass("showwarning").find(".warning .message").html(text);
		}
	};
	this.renderEmpty = function(isempty) {
		isempty = (typeof isempty === "boolean") ? isempty : true;
		$(this.dom).removeClass("empty").find(".content > .emptycontent").remove();

		if (isempty) {
			var empty = $(this.options.dom.empty).clone();
			$(empty).css("display", "block");
			$(this.dom).find(".content").append(empty);
			$(this.dom).addClass("empty");
		}
	};
	this.disable = function(isdisabled) {
		isdisabled = (typeof isdisabled === "boolean") ? isdisabled : true;
		$(this.dom).children(".disablepanel").find(".warning .message").html(this.options.disableMessage);
	};
	this.reRender = function(d) {
		//to append and sync users
	};
	this.render = function(d) {
		this._initContainer();
		if (typeof d !== "undefined") {
			d = d || [];
			d = $.isArray(d) ? d : [d];
			this.options.groups = d;
		}
		this.renderList();
		if (this.options.groups.length === 0) {
			this.renderEmpty(true);
		} else {
			this.renderEmpty(false);

		}
	};
	this._initActions = function() {
		if ($(this.dom).find(".actions").length > 0) {
			$(this.dom).find(".actions > .action[data-action]").unbind("click").bind("click", (function(self) {
				return function(ev) {
					ev.preventDefault();
					self.publish({event: "action", value: {action: $(this).data("action"), source: self}});
					return false;
				};
			})(this));
		}
	};
	this._initContainer = function() {
		this.renderWarning(false);
		$.each(this.subviews, function(i, e) {
			e.unsubscribeAll();
			e.reset();
			$(e.dom).empty();
			e = null;
		});
		this.subviews = [];
		$(this.dom).find(".content").empty();
		$(this.dom).find(".content").append($(this.options.dom.list).clone());
		this._initActions();
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.options.groups = $.isArray(this.options.groups) ? this.options.groups : t[this.options.groups];
		this.options.removableItems = (typeof o.removable === "boolean") ? o.removable : ($(this.dom).find(".permissiongrouplist").data("removable") || false);
	};
	this._init();
});

appdb.views.EntityPrivacy = appdb.ExtendClass(appdb.View, "appdb.views.EntityPrivacy", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		privacy: null,
		state: "idle",
		isPrivate: (typeof o.isPrivate === "boolean") ? o.isPrivate : false,
		canEditPrivacy: (typeof o.editable === "boolean") ? o.editable : false,
		disableMessage: o.disableMessage || "There are pending changes which need to be saved first, in order to edit privacy property."
	};
	this.disable = function(isdisabled) {
		isdisabled = (typeof isdisabled === "boolean") ? isdisabled : true;
		$(this.dom).children(".disablepanel").find(".warning .message").html(this.options.disableMessage);
	};
	this.revertChanges = function() {
		this.options.state = "revertingchanges";
		this.options.privacy.resetData();
		this.options.state = "idle";
	};
	this.hasChanges = function() {
		return this.options.privacy.hasChanges();
	};
	this.getChanges = function() {
		return {"isprivate": this.options.privacy.isChecked()};
	};
	this.onChecked = function(v) {
		if (this.hasChanges()) {
			$(this.dom).addClass("changed");
		} else {
			$(this.dom).removeClass("changed");
		}
		this.publish({event: "changed", value: this});
	};
	this.onChange = function() {

	};
	this._initActions = function() {
		if ($(this.dom).find(".actions").length > 0) {
			$(this.dom).find(".actions > .action[data-action]").unbind("click").bind("click", (function(self) {
				return function(ev) {
					ev.preventDefault();
					self.publish({event: "action", value: {action: $(this).data("action"), source: self}});
					return false;
				};
			})(this));
		}

	};
	this.render = function() {
		this.disable(false);
		if (this.options.privacy) {
			this.options.privacy.unsubscribeAll();
			this.options.privacy = null;
		}
		this.options.privacy = new appdb.views.GenericCheckbox({
			container: $(this.dom),
			parent: this,
			checked: this.options.isPrivate,
			enabled: this.options.canEditPrivacy
		});
		this.options.privacy.subscribe({event: "checked", callback: this.onChecked, caller: this});
		this.options.privacy.render();

	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._initActions();
	};
	this._init();
});

appdb.views.FilterDecorator = appdb.ExtendClass(appdb.View, "appdb.views.FilterDecorator", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			filters: $(o.container).find(".filter > li"),
			list: $(o.container).find(".filteredlist"),
			pager: $(o.container).find(".filterpager")
		},
		pagelength: $.trim($(o.container).data("pagelength")) || null
	};
	this.getCount = function(filterclass) {
		if( !appdb.config.features.swappliance ){
			$(this.dom).find(".swappitem").remove();
			return $(this.dom).find("." + filterclass ).not('.swappitem').length;
		}
		return $(this.dom).find("." + filterclass).length;
	};
	this.getPageNumber = function(filterclass) {
		filterclass = $.trim(filterclass);
		if (filterclass === "")
			return 1;
		var filter = this.getFilter(filterclass);
		if (filter === null)
			return 1;
		var res = $(filter).data("pagenumber");
		if ($.trim(res) === "")
			return 1;
		return parseInt(res);
	};
	this.hasNextPage = function(filterclass) {
		if (this.hasPaging() === false)
			return;
		var items = this.getAllItems(filterclass);
		var pagenumber = this.getPageNumber(filterclass);
		var pagelength = this.getPageLength();
		var pageditems = pagenumber * pagelength;
		if (pageditems < items.length)
			return true;
		return false;
	};
	this.getNextPage = function(filterclass) {
		if (this.hasPaging() === false)
			return;
		filterclass = $.trim(filterclass);
		if (filterclass === "")
			return;
		var filter = this.getFilter(filterclass);
		if (filter === null)
			return;
		var pagenumber = $(filter).data("pagenumber");
		if ($.trim(pagenumber) === "") {
			pagenumber = 1;
		}
		var pagelength = this.getPageLength();
		if (pagelength === null || pagelength <= 0)
			return;
		pagenumber += 1;
		$(filter).data("pagenumber", pagenumber);
		this.renderPager(filterclass);
	};
	this.getFilter = function(filterclass) {
		var filters = $.grep(this.options.dom.filters, function(e) {
			return ($.trim($(e).data("filterclass")) === $.trim(filterclass));
		});
		return (filters.length > 0) ? filters[0] : null;
	};
	this.getAllFilters = function() {
		return $.grep(this.options.dom.filters, function(e) {
			return ($.trim($(e).data("filterclass")) !== "");
		});
	};
	this.getPageLength = function() {
		return this.options.pagelength;
	};
	this.hasPaging = function() {
		return (this.options.pagelength !== null && parseInt($.trim(this.options.pagelength)) > 0);
	};
	this.selectFilter = function(el) {
		$(this.options.dom.filters).removeClass("current");
		$(el).addClass("current");
		var filterclass = $(el).data("filterclass");
		this.renderPager(filterclass);
	};
	this.getAllItems = function(classname) {
		classname = $.trim(classname);
		var res = null;
		var allfilter = $.grep($(this.options.dom.filters), function(e) {
			return $(e).hasClass("all");
		});
		if (allfilter.length > 0) {
			res = $(allfilter[0]).data("filterclass") || null;
		}

		if (res === null) {
			if (classname !== "") {
				return $(this.options.dom.list).find("li." + classname + ":first").siblings();
			} else {
				return $(this.options.dom.list).find("li:first").siblings();
			}

		}
		if (classname !== "") {
			return $(this.options.dom.list).find("." + res + "." + classname);
		}
		return $(this.options.dom.list).find("." + res);
	};
	this.showItems = function(filterclass, itemcount) {
		itemcount = parseInt($.trim(itemcount)) || -1;
		if (isNaN(itemcount)) {
			itemcount = -1;
		}
		var count = this.getCount(filterclass);
		var plength = this.options.pagelength;
		var allitems = this.getAllItems();
		$(allitems).hide();
		if (itemcount > 0) {
			var items = $(this.options.dom.list).find("." + filterclass).slice(0, itemcount);
			$.each(items, function(i, e) {
				$(e).show();
			});
		} else {
			$(this.options.dom.list).find("." + filterclass).show();
		}
	};
	this.renderPager = function(filterclass) {
		$(this.dom).removeClass("haspaging");
		var filter = this.getFilter(filterclass);
		if (filter === null || this.hasPaging() === false || this.hasNextPage(filterclass) === false) {
			this.showItems(filterclass);
			return;
		}

		$(this.dom).addClass("haspaging");
		var itemcount = this.getPageLength();
		var pagenumber = this.getPageNumber(filterclass);
		if (pagenumber > 0) {
			itemcount = parseInt(itemcount) * parseInt(pagenumber);
		}
		this.showItems(filterclass, itemcount);
		$(this.dom).find(".filterpager > .action").unbind("click").bind("click", (function(self, fc) {
			return function(ev) {
				ev.preventDefault();
				self.getNextPage(fc);
				return false;
			};
		})(this, filterclass));
	};
	this._initActions = function() {
		$(this.options.dom.filters).children().unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.selectFilter($(this).parent());
				return false;
			};
		})(this));
	};
	this.render = function() {
		if ($(this.options.dom.list).children("li").length === 0) {
			$(this.dom).addClass("empty");
		} else {
			$(this.dom).removeClass("empty");
		}
		$(this.options.dom.filters).each((function(self) {
			return function(i, e) {
				var fc = $.trim($(this).data("filterclass"));
				if (fc === "")
					return;
				var count = self.getCount(fc);
				$(this).children().find(".counter").text("(" + count + ")");
				$(this).data("itemcount", count);
				$(this).removeClass("current");
			};
		})(this));
		this.renderFilterSelectors();
		this.selectCurrent();
	};
	this.renderFilterSelectors = function() {
		var all = $.grep($(this.options.dom.filters), function(e) {
			return ($(e).hasClass("all") && $.trim($(e).data("filterclass")) !== "");
		});
		var filters = this.getAllFilters();
		var counters = [];
		$.each(filters, function(i, e) {
			$(e).removeClass("hidden");
			$(e).prev(".seperator").removeClass("hidden");
			if ($(e)[0] === $(all)[0])
				return;
			if (parseInt($(e).data("itemcount")) > 0) {
				counters.push(e);
			} else {
				$(e).addClass("hidden");
				$(e).prev(".seperator").addClass("hidden");
			}
		});

		if (counters.length > 1) {
			$(all).removeClass("hidden");
			$(all).addClass("current");
		} else if (counters.length === 1) {
			$(all).addClass("hidden");
			$(counters[0]).addClass("current");
			$(this.options.dom.filters).next(".seperator").addClass("hidden");
		} else {
			$(this.options.dom.filters).next(".seperator").addClass("hidden");
		}

	};
	this.selectCurrent = function() {
		var filters = $.grep(this.options.dom.filters, function(e) {
			return $(e).hasClass("current");
		});
		if (filters.length > 0) {
			this.selectFilter(filters[0]);
		}
	};
	this._initContainer = function() {
		this._initActions();
	};
	this._init = function() {
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
});
appdb.views.SecantReport = appdb.ExtendClass(appdb.View, "appdb.views.SecantReport", function(o) {
	this.options ={
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {}
	};
	this.render = function(data) {
		var d = (data.report_data || {}).SECANT || null;
		if(!d) return null;
		var logs = (d.LOG || {}).CHECK || null;
		if (!logs) return null;
		var table = $('<table ><tbody></tbody></table>');
		var tbody = $(table).find('tbody');
		$.each(logs || [], function(i, log) {
		   var imgSrc = '';
		   switch(log.OUTCOME) {
		       case "NA":
		       case "WARNING":
			   imgSrc = '/images/vappliance/warning.png';
			   break;
		       case "OK":
			   imgSrc = '/images/tick.png';
			   break;
		       default:
			   imgSrc = '/images/vappliance/redwarning.png';
			   break;
		   }
		   var td1 = $('<td></td>').append($('<img></img>').attr('src', imgSrc).attr('title', log.OUTCOME));
		   var td2 = $('<td></td>').append($('<div class="test_id"></div>').append(log.TEST_ID).append('<span class="version">v'+log.VERSION+'</span>'))
			   .append($('<div class="description"></div>').append(log.DESCRIPTION));
		   if ($.trim(log.DETAILS)) {
		       $(td2).append($('<div class="details"></div>').append(log.DETAILS));
		   }

		   $(tbody).append($('<tr></tr>').append(td1).append(td2));
		});
	};

	this._init = function() {
	    this.dom = $(this.options.container);
	    this.parent = this.options.parent;
	};
	this._init();
},{
    stateMap: {
	'queued': { state: 'queued',  status: 'pending', img: '/images/ajax-loader-trans-orange.gif', text: "Ongoing security check for <span class='datavalue vaversion' data-path='vaversion_type'></span> version <span class='datavalue vaversion' data-path='vaversion'></span>", content: "<span class='datavalue vaversion capitilize' data-path='vaversion_type'></span> version <span class='datavalue vaversion' data-path='vaversion'></span> of VAppliance <span class='datavalue app_name' data-path='app_name'></span> is currently being checked for security issues" },
	'sent': { state: 'sent',  status: 'pending', img: '/images/ajax-loader-trans-orange.gif', text: "Ongoing security check for <span class='datavalue vaversion' data-path='vaversion_type'></span> version <span class='datavalue vaversion' data-path='vaversion'></span>", content: "<span class='datavalue vaversion capitilize' data-path='vaversion_type'></span> version <span class='datavalue vaversion' data-path='vaversion'></span> of VAppliance <span class='datavalue app_name' data-path='app_name'></span> is currently being checked for security issues"  },
	'aborted': { state: 'aborted', status: 'aborted', img: '/images/vappliance/redwarning.png', text: "Security check for <span class='datavalue vaversion' data-path='vaversion_type'></span> version <span class='datavalue vaversion' data-path='vaversion'></span> of VAppliance <span class='datavalue app_name' data-path='app_name'></span>  was <b>aborted</b>"},
	'closed_OK': { state: 'closed', status: 'success', img: '/images/tick.png', text: "<span class='datavalue vaversion capitilize' data-path='vaversion_type'></span> version <span class='datavalue vaversion' data-path='vaversion'></span> of VAppliance <span class='datavalue app_name' data-path='app_name'></span> passed <b>ALL</b> security checks"},
	'closed_WARNING': { state: 'closed', status: 'warning', img: '/images/vappliance/warning.png', text: "<span class='datavalue vaversion capitilize' data-path='vaversion_type'></span> version <span class='datavalue vaversion' data-path='vaversion'></span> of VAppliance <span class='datavalue app_name' data-path='app_name'></span> passed <b>SOME</b> security checks"},
	'closed_ERROR': { state: 'closed', status: 'error', img: '/images/vappliance/redwarning.png', text: "<span class='datavalue vaversion capitilize' data-path='vaversion_type'></span> version <span class='datavalue vaversion' data-path='vaversion'></span> of VAppliance <span class='datavalue app_name' data-path='app_name'></span>  <b>failed</b> to pass security checks"}
    },
    databindHtmlReportData: function(data, html) {
	    $(html).find('[data-path]').each(function(i, e) {
		var val = $.trim(data[$(e).data('path')]);
		if ($(e).hasClass('capitilize') && val) {
		    val = val[0].toUpperCase() + val.slice(1);
		}
		$(e).text(val);
	    });
	    return $(html);
    },
    getHtmlReportContent: function(report, container) {
	    container = container || $('<span></span>');
	    var state = appdb.views.SecantReport.getReportStatusData(report);
	    if (!state) return null;

	    return appdb.views.SecantReport.databindHtmlReportData(report, $(container).append(state.content || state.text));
    },
    getReportStatusData: function(report) {
	var mapper = appdb.views.SecantReport.stateMap || {};
	var state = null;
	if (report) {
		var stateKey = report.state;

		if (stateKey === 'closed') {
		    stateKey = stateKey + '_' + ('' + report.report_outcome).toUpperCase();
		}

		state = mapper[stateKey];
	}
	return (state) ? state : null;
    },
    createBadge: function(report, config) {
	var dom = null;
	var state = appdb.views.SecantReport.getReportStatusData(report);

	config = config || {};
	config.displayVersion = (typeof config.displayVersion === 'boolean') ? config.displayVersion : false;
	config.displayHeader = (typeof config.displayHeader === 'boolean') ? config.displayHeader : true;
	config.headerText = (typeof config.headerText === 'string') ? $.trim(config.headerText) || 'SECANT': 'SECANT'; 
	config.size = (typeof config.size === 'string') ? $.trim(config.size) : '';

	if (state) {
		dom = $('<a class="secant badge"></a>').addClass(state.status);

		if (config.size) {
			$(dom).addClass(config.size);
		}

		if(config.displayHeader === true) {
			$(dom).append($('<span class="header"></span>').text(config.headerText));
		}

		if (config.displayVersion === true) {
			$(dom).append($('<span class="version"></span>').text(report.vaversion_type || report.vaversion));
		}

		$(dom).append($('<span class="status"></span>').text(state.status));
		$(dom).prepend($('<img></img>').attr('src', state.img));
		var title = state.content || state.text;
		if (title) {
			title = appdb.views.SecantReport.databindHtmlReportData(report, $('<span></span>').append(title )).text();
			$(dom).attr('title', title);
		}

		if (config.displayHeader === false && config.displayVersion === false) {
			$(dom).addClass('single');
		}
	}

	return dom;
    },
    createReportTable: function(data) {
	    var d = (data.report_data || {}).SECANT || null;
	    if(!d) return null;
	    var logs = (d.LOG || {}).CHECK || null;
	    if (!logs) return null;
	    var table = $('<table class="report-data"><tbody></tbody></table>');
	    var tbody = $(table).find('tbody');
	    $.each(logs || [], function(i, log) {
	       var imgSrc = '';
	       switch(log.OUTCOME) {
		   case "NA":
		   case "WARNING":
		       imgSrc = '/images/vappliance/warning.png';
		       break;
		   case "OK":
		       imgSrc = '/images/tick.png';
		       break;
		   default:
		       imgSrc = '/images/vappliance/redwarning.png';
		       break;
	       }
	       var td1 = $('<td></td>').append($('<img></img>').attr('src', imgSrc).attr('title', log.OUTCOME));
	       var td2 = $('<td></td>').append($('<div class="test_id"></div>').append(log.TEST_ID).append('<span class="version">v'+log.VERSION+'</span>'))
		       .append($('<div class="description"></div>').append(log.DESCRIPTION));
	       if ($.trim(log.DETAILS)) {
		   $(td2).append($('<div class="details"></div>').append(log.DETAILS));
	       }

	       $(tbody).append($('<tr></tr>').append(td1).append(td2));
	    });
	    return table;
    },
    getDiff: function(a, b) {
	    var ar = ((((a || {}).report_data || {}).SECANT || {}).LOG || {}).CHECK || [];
	    var br = ((((b || {}).report_data || {}).SECANT || {}).LOG || {}).CHECK || [];
	    ar = $.isArray(ar) ? ar : [ar];
	    br = $.isArray(br) ? br : [br];

	    var diff = {};
	    differ = function(i, r) {
		    diff[r.TEST_ID] = diff[r.TEST_ID] || {};
		    r.vaversion= this.vaversion;
		    diff[r.TEST_ID][this.vaversion] = r;
	    };
	    $.each(ar, differ.bind(a));
	    $.each(br, differ.bind(b));

	    return {
		    versions: [a.vaversion, b.vaversion],
		    diff: diff
	    };
    },
    getDiffTable: function(reporta, reportb) {
	    var comparison = appdb.views.SecantReport.getDiff(reporta, reportb);
	    var table = $('<table class="diff-data"><tbody></tbody></table>');
	    var thead = $('<thead><tr><th></th><th>' + comparison.versions[0] + '</th><th>' + comparison.versions[1] + '</th></tr></thead>')
	    var tbody = $('<tbody></tbody>');
	    $(table).append(thead).append(tbody);
	    var diffKeys = Object.keys(comparison.diff);
	    $.each(diffKeys, function(i, diffKey) {
		    var diff = comparison.diff[diffKey];
		    var td1 = $('<td></td>').append(diffKey);
		    var row = $('<tr></tr>').append(td1);
		    $(row).append(td1);
		    var outcomes = {};
		    $.each(comparison.versions, function(ii, verKey) {
			    var ver = diff[verKey];
			    if (!ver) {
				    $(row).append('<td>-</td>');
				    return;
			    }
			    outcomes[ver.OUTCOME] = true;

			    var imgSrc = '';
			    switch(ver.OUTCOME) {
				    case "NA":
				    case "WARNING":
					imgSrc = '/images/vappliance/warning.png';
					break;
				    case "OK":
					imgSrc = '/images/tick.png';
					break;
				    default:
					imgSrc = '/images/vappliance/redwarning.png';
					break;
			    }

			    var div = $('<div class="outcome"></div>').append($('<img></img>').attr('src', imgSrc).attr('title', ver.DETAILS || ver.OUTCOME));
			    $(row).append($('<td></td>').append(div));
		    });
		    if (Object.keys(outcomes).length > 1) {
			$(row).addClass('hasdiff');
		    }
		    $(tbody).append(row);
	    });
	    return table;
    },
    getComparableReport: function(report, data) {
	    if (!data || !data.secant || data.secant.length < 2) {
		    return null;
	    }

	    var secantStatus = appdb.views.SecantReport.getReportStatusData(report);

	    if (['pending', 'aborted'].indexOf(secantStatus.status) > -1 || data.secant.length < 2) {
		    return null;
	    }
	    var comparables = [];
	    $.each(data.secant, function(i, s) {
		    if ($.trim(s.vmiinstance_id) !== $.trim(report.vmiinstance_id) && ['queued', 'sent'].indexOf($.trim(s.state).toLowerCase()) === -1 && s.vaversion_type === 'current') {
			comparables.push(s);
		    }
	    });

	    return (comparables.length > 0) ? comparables[0] : null;
    },
    renderSecantDetails: function(report, data) {
	    var secantStatus = appdb.views.SecantReport.getReportStatusData(report);
	    var header = appdb.views.SecantReport.getHtmlReportContent(report, $('<div class="header"></div>'));
	    var content = $('<div class="content"></div>');
	    var dialog = $('<div class="secant_dialog"></div>').append(header);

	    if (['pending', 'aborted'].indexOf(secantStatus.status) === -1) {
		var comparableReport = appdb.views.SecantReport.getComparableReport(report, data);
		var secantStatus = $('<div class="secant_status report"></div>');
		$(secantStatus).append(appdb.views.SecantReport.createReportTable(report));
		$(content).append(secantStatus);
		if (comparableReport) {
		    $(secantStatus).addClass('candiff');
		    var diffHandler = $('<div class="viewas"></div>');
		    var viewReportText = 'Back to <b>' +  report.vaversion_type + '</b>(' + comparableReport.vaversion + ') version security report';
		    var viewReport = $('<a class="view-report"></a>').append(viewReportText).unbind('click').bind('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			$(this).closest('.secant_status').removeClass('diff').addClass('report');
			return false;
		    });
		    var diffText = 'Diff security report with <b>' + comparableReport.vaversion_type + '</b>(' + comparableReport.vaversion + ') version';
		    var viewDiff = $('<a class="view-diff"></a>').append(diffText).unbind('click').bind('click', function(ev) {
			ev.preventDefault();
			ev.stopPropagation();
			$(this).closest('.secant_status').removeClass('report').addClass('diff');
			return false;
		    });
		    $(diffHandler).append(viewDiff).append(viewReport);
		    $(secantStatus).prepend(diffHandler);

		    $(secantStatus).append( appdb.views.SecantReport.getDiffTable(report, comparableReport));
		}
		$(dialog).append(content);
	    }
	    var date = report.closedon || report.senton || report.queuedon || null;
	    if (date) {
		    var footer = $('<div class="footer"></div>');
		    var reportState = (report.closedon) ? ' completed on' : (report.senton ? ' request sent on' : ' request queued on');
		    $(footer).append('Security report' + reportState + ' <span class="value date"></span>');
		    $(footer).find('span.value.date').text(date.split('.')[0]);
		    $(dialog).append(footer);
	    }
	    return dialog;
	}
});

appdb.views.VoSecantReport = appdb.ExtendClass(appdb.View, "appdb.views.VoSecantReport", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		reports : [],
		popupProvider: o.popupProvider,
		vaversionTypes: o.vaversionTypes || [],
		allowVersionDisplay: (typeof o.allowVersionDisplay === 'boolean') ? o.allowVersionDisplay : true
	};

	this.shouldDisplayVersions = function(data) {
	    if (this.options.allowVersionDisplay === false) return;
	    if (data.secant.length > 1) {
		return true;
	    }
	    var vowideimagelist_id = data.secant[0].vowideimagelist_id;

	    if (!vowideimagelist_id) {
		return false;
	    }

	    return true;
	};

	this.getReports = function() {
	    return this.options.reports || [];
	}

	this.render = function(data) {
	    data = data  || this.options.data;
	    this.options.data = data || this.options.data;
	    $(this.dom).find('.secant.badge').remove();
	    if (!data.secant || data.secant.length === 0) {
		return null;
	    }
	    this.options.reports = this.options.reports || [];
	    this.options.reports = $.isArray(this.options.reports) ? this.options.reports : [this.options.reports];

	    if (data.secant.length > 0) {
		$.each(data.secant, function(i, s) {
			if (this.options.vaversionTypes && this.options.vaversionTypes.length > 0 && this.options.vaversionTypes.indexOf(s.vaversion_type) === -1) {
			    return;
			}
			var badge = appdb.views.SecantReport.createBadge(s, {headerText: 'LATEST VERSION CHECK', displayVersion: this.shouldDisplayVersions(data)});
			if (badge) {
				$(badge).unbind('click').bind('click', (function(self, content) {
				    return function(ev) {
					ev.preventDefault();
					var popupProvider = (self.options.popupProvider && 'popup' in self.options.popupProvider) ? self.options.popupProvider : appdb.views.VoSecantReport;
					if (popupProvider.popup) {
						popupProvider.popup.destroyRecursive(false);
						popupProvider.popup = null;
					}
					if (content) {
					    popupProvider.popup = new dijit.TooltipDialog({content: $(content)[0]});
					    dijit.popup.open({
						    parent: $(this)[0],
						    popup: popupProvider.popup,
						    around: $(ev.target)[0],
						    orient: {'BL': 'TR'}
					    });
					}
					return false;
				    };
				}(this, appdb.views.SecantReport.renderSecantDetails(s, data))));
				$(this.dom).append(badge);

				this.options.reports.push(s);
			}
		}.bind(this));
	    }
	};

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
}, {
    popup: null
});
appdb.views.VoImageListItem = appdb.ExtendClass(appdb.View, "appdb.views.VoImageListItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		cols: o.cols || [],
		renderer: null
	};
	this.getStateData = function() {
		return (this.options.data && this.options.data.data && this.options.data.data.voimagelist) ? this.options.data.data.voimagelist : {};
	};
	this.getId = function() {
		return ($.trim((this.options.data || {}).id || "-1") << 0);
	};
	this.setupRenderer = function(dom, col, data) {
		if (typeof col.cellRenderer === "object" && $.trim(co.cellRenderer.name) !== "") {
			var obj = appdb.FindNS(this.options.cellRenderer.name, false);
			var objOptions = $.extend(true, {container: $(dom), col: col, data: data, parent: this}, (col.cellRenderer.options || {}));
			col.renderer = new obj(objOptions);
		}
		return null;
	};
	this.onAction = function(v) {
		this.publish({event: "action", value: {action: v.action, data: this.options.data}});
	};
	this.addCell = function(d, col) {
		d = d || {};
		col = col || {};
		var td = $("<td></td>");
		var cell = $("<div class='customcellwrap'></div>");
		if ($.trim(col.name) in d) {
			var dname = d[col.displayName || col.name];
			if (typeof col.getValues === "function") {
				dname = (col.getValues(this) || []).join(",");
			}
			var sp = $("<span class='value'></span>");
			$(sp).text(dname);
			$(td).empty();
			if (col.renderer) {
				var html = col.renderer(td, this, col, this.options.data);
				if (html) {
					$(cell).append(html);
					$(td).append(cell).addClass("customcell");
				}
			}
			$(td).append(sp);
		} else {
			return null;
		}
		if ($.trim(col.name) !== "") {
			$(td).attr("data-name", col.name);
			if (col.display === false) {
				$(td).addClass("hidden");
			}
		}
		return td;
	};
	this.render = function(d) {
		$(this.dom).empty().removeAttr("data-published").removeAttr("data-outdated");
		if (d) {
			d = d || {};
			this.options.data = d;
		}
		var vodata = this.options.data.data.voimagelist || {};
		if (vodata.isAdded && vodata.isPublished === false) {
			$(this.dom).addClass("action-added");
		} else {
			$(this.dom).removeClass("action-added");
		}
		if ((vodata.wasOutdated === true && vodata.isOutdated === false) || (vodata.wasOutdated === true && vodata.isAdded === true)) {
			$(this.dom).addClass("action-updated");
		} else {
			$(this.dom).removeClass("action-updated");
		}
		if (vodata.isRemoved && vodata.isPublished === true) {
			$(this.dom).addClass("action-removed");
		} else {
			$(this.dom).removeClass("action-removed");
		}
		$(this.dom).removeClass('has-newimage');
		$.each(this.options.cols, (function(self, data) {
			return function(i, e) {
				var td = self.addCell(data, e);
				if (td === null) {
					td = $("<td></td>");
				}
				$(self.dom).append(td);
			};
		})(this, this.options.data));

		if (this.options.data.isExpired === true) {
			$(this.dom).addClass("isexpired");
			if (vodata.isPublished === true) {
				if (vodata.isOutdated === true) {
					var verlink = "<a title='View newer version in new window' class='icontext newversion' href='" + appdb.config.endpoint.base + "store/vappliance/" + this.options.data.data.cname + "/vaversion/latest' target='_blank'>" + this.options.data.version + "</a>";
					this.renderWarning(true, "Outdated and expired", "A newer version, ver:" + verlink + " was published, but it has already <b>expired</b>. <br/>It is recommended to remove it from the VO wide image list.");
				} else {
					this.renderWarning(true, "Virtual Appliance has expired", "This virtual appliance has expired. <br/>It is recommended to remove it from the VO wide image list.");
				}
			} else {
				$(this.dom).addClass("hidden");
			}
		} else {
			$(this.dom).removeClass("isexpired");
		}
		if ($(this.dom).hasClass("action-removed")) {
			this.renderWarning(true, "Marked to be removed", "The images of this virtual appliance are marked to be removed from this image list, <b>upon publishing</b>.");
		} else if ($(this.dom).hasClass("action-updated")) {
			this.renderWarning(true, "Marked to be updated", "The image list will be updated with the latest version of this virtual appliance, <b>upon publishing</b>.");
		} else if ($(this.dom).hasClass("action-added")) {
			this.renderWarning(true, "Marked to be added", "The images of this virtual appliance will be added in the vo wide image list, <b>upon publishing</b>");
		} else if (vodata.isOutdated === true && this.options.data.isExpired === false) {
			var verlink = "<a title='View new version in new window' class='icontext newversion' href='" + appdb.config.endpoint.base + "store/vappliance/" + this.options.data.data.cname + "/vaversion/latest' target='_blank'>" + this.options.data.version + "</a>";
			$(this.dom).addClass('has-newimage');
			this.renderWarning(true, "New image version available"/*"Outdated images"*/, "A new version, ver:" + verlink + ", is available. It is recomended to update. <br/>In such case, the action will take affect <b>upon publishing</b>.");
		}

		if (this.options.data.secant && this.options.data.secant.length > 0) {
		    this.renderSecant(this.options.data);
		}
	};
	this.createWarningLink = function(text, errortext) {
		text = $.trim(text) || "An error occured";
		errortext = $.trim(errortext) || "Unknown error occured";
		var a = $("<a href='#' title='' class='icontext warningmessage'><img src='/images/vappliance/warning.png' alt=''/><span></span></a>");
		$(a).find("span").text(text);
		var content = $("<div class='voimagelistaction warning'></div>");
		$(content).html(errortext);
		$(a).unbind("click").bind("click", (function(self, content) {
			return function(ev) {
				ev.preventDefault();
				if (appdb.views.VoImageListItem.popup) {
					appdb.views.VoImageListItem.popup.destroyRecursive(false);
					appdb.views.VoImageListItem.popup = null;
				}
				appdb.views.VoImageListItem.popup = new dijit.TooltipDialog({content: $(content)[0]});
				dijit.popup.open({
					parent: $(this)[0],
					popup: appdb.views.VoImageListItem.popup,
					around: $(this)[0],
					orient: {'BL': 'TL', 'BR': 'TR'}
				});
				return false;
			};
		})(this, content));
		return a;
	};
	this.renderWarning = function(enable, error, content) {
		enable = (typeof enable === "boolean") ? enable : false;
		var errortext = $.trim(error) ? error : "Warning";
		var d = $(this.dom).find("td[data-name='warnings'] .value");
		$(d).find(".warningmessage").remove();
		if (enable === true) {
			$(d).append(this.createWarningLink(errortext, content));
		}
	};
	this.renderCurrentSecant = function(data) {
		$(this.dom).find("td[data-name='name'] > .customcellwrap").remove('.secant.badge');
		var secants = (data || {}).secant || [];
		secants = $.isArray(secants) ? secants : [secants];
		var currentSecant = null;
		$.each(secants, function(i,s) {
		    if (s.vaversion_type === 'current') {
			currentSecant = s;
		    }
		});

		if (!currentSecant) {
		    return;
		}

		var badge = appdb.views.SecantReport.createBadge(currentSecant, {displayHeader: true, headerText: 'CURRENT VERSION CHECK', size: ''});
		if (badge) {
			$(badge).unbind('click').bind('click', (function(self, content) {
			    return function(ev) {
				ev.preventDefault();
				if (appdb.views.VoImageListItem.popup) {
					appdb.views.VoImageListItem.popup.destroyRecursive(false);
					appdb.views.VoImageListItem.popup = null;
				}
				if (content) {
				    appdb.views.VoImageListItem.popup = new dijit.TooltipDialog({content: $(content)[0]});
				    dijit.popup.open({
					    parent: $(this)[0],
					    popup: appdb.views.VoImageListItem.popup,
					    around: $(ev.target)[0],
					    orient: {'BR': 'TL'}//{'TR': 'BL'}
				    });
				}
				return false;
			    };
			}(this, appdb.views.SecantReport.renderSecantDetails(currentSecant))));
			$(this.dom).find("td[data-name='name']  > .customcellwrap").append(badge);
		}
	};
	this.renderSecant = function(data) {
		data = data || {};
		var dom = $(this.dom).find("td[data-name='warnings'] .value");
		var secant = new appdb.views.VoSecantReport({
			container: $(dom),
			parent: this,
			popupProvider: appdb.views.VoImageListItem,
			vaversionTypes: ['latest'],
			allowVersionDisplay: false
		});
		secant.render(data);
		if (secant.getReports().length > 0) {
		    $(this.dom).addClass('has-latestreport');
		} else {
		    $(this.dom).removeClass('has-latestreport');
		}
		this.renderCurrentSecant(data);
	};
	this.isAdded = function() {
		return (this.getStateData().isAdded || false) && (this.getStateData().isPublished === false);
	};
	this.isRemoved = function() {
		return (this.getStateData().isRemoved || false) && (this.getStateData().isPublished === true);
	};
	this.isUpdated = function() {
		return (this.getStateData().wasOutdated === true) && (this.getStateData().isOutdated === false) && (this.getStateData().isRemoved === false);
	};
	this.isExpired = function() {
		return (this.options.data && this.options.data.isExpired === true);
	};
	this._initContainer = function() {

	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();

}, {
	popup: null,
	popupCurrentSecant: null,
	popupLatestSecant: null,
	popupswwapp: null,
	popupswwappinerval: -1
});
appdb.views.VoImageListItemCell = {};
appdb.views.VoImageListItemCell.BooleanValue = function(dom, row, col, data) {
	var _render = function() {
		var html = (col.onFalseHtml) ? col.onFalseHtml : "<span>true</span>";
		if (data[col.name] === true) {
			html = (col.onTrueHtml) ? col.onTrueHtml : "<span>true</span>";
		}
		return $(html);
	};
	return _render();
};
appdb.views.VoImageListItemCell.Expandable = function(dom, row, col, data, maxheight) {
	var _checkExpand = function(e) {
		setTimeout(function() {
			var el = $(e).closest("tr"), current = $(e).closest(".customcellwrap");
			$(el).find("td .expandable").each(function(i, e) {
				if ($(this).parent()[0] === $(current)[0])
					return;
				var h = Math.ceil($(this).find(".values").height());
				var ph = Math.ceil($(this).closest("td").height());
				if (h < ph) {
					$(this).find(".toggler").addClass("hidden");
				} else {
					$(this).find(".toggler").removeClass("hidden");
				}
			});
		}, 50);
	};
	var _checkMaxHeight = function(expand) {
		expand = (typeof expand === "boolean") ? expand : false;
		var tr = $(dom).closest("tr");
		setTimeout(function() {
			var max = -1;
			if (expand) {
				$(tr).find("td.expand .expandable").each(function(i, e) {
					if (max <= $(this).height()) {
						max = $(this).height();
					}
				});
				if (max >= 0) {
					$(tr).find("td.expand .expandable").css("min-height", max + "px");
				}
			} else {
				$(tr).find("td .expandable").css("min-height", "").addClass("collapsed");
			}
		}, 10);
	};
	var _render = function() {

		var html = $("<div class='expandable collapsed'></div>");
		var values = $("<div class='values'></div>");
		var toggle = $("<div class='toggler'><div class='more'>more</div><div class='less'>less</div></div>");
		$(html).append(values).append(toggle);
		var vals = $("<div></div>");
		$.each($.trim(data[col.name]).split(","), function(i, e) {
			var v = $("<div><span class='val'></span></div>");
			$(v).find(".val").text(e);
			$(v).find(".val").append("<span class='seperator'>,</span>");
			$(vals).append($(v).find(".val"));
		});
		$(vals).find(".seperator:last").remove();
		$(values).html($(vals).html());
		$(toggle).unbind("click").bind("click", (function(height) {
			return function(ev) {
				ev.preventDefault();
				if ($(this).parent().hasClass("collapsed")) {
					$(this).parent().removeClass("collapsed");
					$(this).closest("td").addClass("expand");
					$(this).closest("tr").find("td .expandable").each(function(ii, ee) {
						$(ee).removeClass("collapsed").closest("td").addClass("expand");
					});
					_checkMaxHeight(true);
				} else {
					$(this).parent().addClass("collapsed");
					$(this).closest("td").removeClass("expand");
					$(this).closest("td").removeClass("expand");
					$(this).closest("tr").find("td .expandable").each(function(ii, ee) {
						$(ee).addClass("collapsed").closest("td").removeClass("expand");
					});
					_checkMaxHeight(false);
				}
				return false;
			};
		})(((col.maxheight << 0) || 25)));
		_checkExpand($(toggle));
		_checkExpand($(toggle));
		return $(html);
	};
	return _render();
};
appdb.views.VoImageListItemCell.VapplianceInfo = function(dom, row, col, data) {
	var _render = function() {
		var d = (data || {}).data || {};
		d.appliance = d.appliance || {};
		d.voimagelist = d.voimagelist || {};
		d.voimagelist.images = d.voimagelist.images || [];

		var html = $("<div class='vappinfo'></div>");
		var name = $("<div class='name fieldvalue'><div class='value'></div></div>");
		var version = $("<div class='version fieldvalue'><div class='field'>ver.</div><div class='seperator'>:</div><div class='value'></div></div>");

		var imagecount = $("<div class='imagecount fieldvalue'><div class='field'>Images</div><div class='seperator'>:</div><div class='value'></div></div>");
		var link = $("<a href='' title='Open virtual appliance in new window' target='_blank' class='icontext'><img src='' alt=''/><span></span></a>");
		var verlink = $("<a href='' title='View virtual appliance latest version in new window' target='_blank' class='icontext'><span></span></a>");

		var logosrc = ((d.logo) ? "/apps/getlogo?req=" + encodeURI(d.lastUpdated) + "&id=" + d.id : loadImage(appdb.config.images["virtual appliances"]));

		$(link).find("span").text(d.name);
		$(link).attr('href', appdb.config.endpoint.base + "store/vappliance/" + d.cname);
		$(link).find("img").attr("src", logosrc);
		if ($.trim(d.appliance.imagelistprivate) === "true") {
			$(link).prepend("<img src='/images/logout3.png' class='privacy' alt='' title='Private Virtual Appliance'/>");
		}
		$(verlink).find("span").text(d.appliance.version);
		$(verlink).attr('href', appdb.config.endpoint.base + "store/vappliance/" + d.cname + "/vaversion/latest");

		$(name).find(".value").append(link);
		$(version).find(".value").append(verlink);
		$(html).append(name);
		if ($.isEmptyObject((d.voimagelist.previousVersions || {}))) {
			$(html).append(version);
		} else {
			var prevversion = $("<div class='prevversion fieldvalue'><div class='field'>ver.</div><div class='seperator'>:</div><div class='value'></div></div>");

			var prevval = [];
			for (var o in d.voimagelist.previousVersions) {
				if (d.voimagelist.previousVersions.hasOwnProperty(o) === false)
					continue;
				prevval.push("<a href='" + appdb.config.endpoint.base + "store/vappliance/" + d.cname + "/vaversion/previous/" + o + "' title='View virtual appliance published version in new window' target='_blank' class='icontext'><span>" + d.voimagelist.previousVersions[o] + "</span></a>");
			}
			$(prevversion).find(".value").html(prevval.join("<span class='seperator'>,</span>"));
			$(html).append(prevversion);
		}
		$(imagecount).find(".value").text(d.voimagelist.images.length);
		$(html).append(imagecount);

		return $(html);
	};
	return _render();
};
appdb.views.VoImageListItemCell.SWappSupport = function(dom, row, col, data) {
	var _checkExpand = function(e) {
		setTimeout(function() {
			var el = $(e).closest("tr"), current = $(e).closest(".customcellwrap");
			$(el).find("td .expandable").each(function(i, e) {
				if ($(this).parent()[0] === $(current)[0])
					return;
				var h = Math.ceil($(this).find(".values").height());
				var ph = Math.ceil($(this).closest("td").height());
				if (h < ph) {
					$(this).find(".toggler").addClass("hidden");
				} else {
					$(this).find(".toggler").removeClass("hidden");
				}
			});
		}, 50);
	};
	var _checkMaxHeight = function(expand) {
		expand = (typeof expand === "boolean") ? expand : false;
		var tr = $(dom).closest("tr");
		setTimeout(function() {
			var max = -1;
			if (expand) {
				$(tr).find("td.expand .expandable").each(function(i, e) {
					if (max <= $(this).height()) {
						max = $(this).height();
					}
				});
				if (max >= 0) {
					$(tr).find("td.expand .expandable").css("min-height", max + "px");
				}
			} else {
				$(tr).find("td .expandable").css("min-height", "").addClass("collapsed");
			}
		}, 10);
	};
	var _checkVersions = function(swapp, data){
		data = data || {};
		data.data = data.data || {};
		var vapp = data.data.appliance || {};
		
		swapp = swapp || {};
		if( $.trim(swapp.versionid) === $.trim(vapp.versionid) ){
			return null;
		}
		
		var warning = $("<span class='warning icontext' title=''><img src='/images/vappliance/warning.png' alt=''/><span class='swappwarningdetails hidden'>Although the software appliance uses this virtual appliance, it does not reference its current version.</span></span>");
		return $(warning);
		
	};
	var _renderItem = function(item,data){
		var v = $("<div><span class='val'></span></div>");
		var name = $.trim(item.name).substr(0,25);
		var a = $("<a title='View software appliance details' target='_blank'></a>");
		var details = $("<span class='swappcard icontext'><img src='/apps/getlogo?id=" + item.id + " alt=''><span></span></span>");
		$(details).find("span").text(name);
		var warning = _checkVersions(item,data);
		if( warning ){
			$(v).find(".val").append(warning);
		}
		$(a).attr("href", appdb.config.endpoint.base + "store/swappliance/" + item.cname).append(details);
		$(a).unbind("click").bind("click", (function(d){
			return function(ev){
				ev.preventDefault();
				appdb.views.Main.showSoftwareAppliance({id: d.id, cname:d.cname});
				return false;
			};
		})(item));
		$(v).find(".val").append(a);
		$(v).find(".val").append("<span class='seperator'>,</span>");
		return $(v).find(".val");
	};
	var _render = function() {
		var html = $("<div class='expandable collapsed'></div>");
		var values = $("<div class='values'></div>");
		var toggle = $("<div class='toggler'><div class='more'>more</div><div class='less'>less</div></div>");
		$(html).append(values).append(toggle);
		var vals = $("<div></div>");
		$.each(data[col.name] || [], (function(d){
			return function(i, e) {
				$(vals).append( _renderItem(e,d) );
			};
		})(data));
		$(vals).find(".seperator:last").remove();
		$(values).html($(vals).html());
		$(toggle).unbind("click").bind("click", (function(height) {
			return function(ev) {
				ev.preventDefault();
				if ($(this).parent().hasClass("collapsed")) {
					$(this).parent().removeClass("collapsed");
					$(this).closest("td").addClass("expand");
					$(this).closest("tr").find("td .expandable").each(function(ii, ee) {
						$(ee).removeClass("collapsed").closest("td").addClass("expand");
					});
					_checkMaxHeight(true);
				} else {
					$(this).parent().addClass("collapsed");
					$(this).closest("td").removeClass("expand");
					$(this).closest("td").removeClass("expand");
					$(this).closest("tr").find("td .expandable").each(function(ii, ee) {
						$(ee).addClass("collapsed").closest("td").removeClass("expand");
					});
					_checkMaxHeight(false);
				}
				return false;
			};
		})(((col.maxheight << 0) || 25)));
		_checkExpand($(toggle));
		_checkExpand($(toggle));
		$(html).find(".warning").unbind("mouseenter").bind("mouseenter", function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			if(appdb.views.VoImageListItem.popupswwapp !== null){
				dijit.popup.close(appdb.views.VoImageListItem.popupswwapp);
			}
			var content = $(this).find(".swappwarningdetails").clone().removeClass("hidden");
			appdb.views.VoImageListItem.popupswwapp = new dijit.TooltipDialog({content: $(content)[0]});
			dijit.popup.open({
				parent: $(this)[0],
				popup: appdb.views.VoImageListItem.popupswwapp,
				around: $(this)[0],
				orient: {'BR': 'TR', 'BL': 'TL'}
			});
			$(content).unbind("mouseenter").bind("mouseenter", function(ev){
				clearTimeout(appdb.views.VoImageListItem.popupswwappinerval);
			}).unbind("mouseleave").bind("mouseleave", function(ev){
				clearTimeout(appdb.views.VoImageListItem.popupswwappinerval);
				appdb.views.VoImageListItem.popupswwappinerval = setTimeout(function(){
					dijit.popup.close(appdb.views.VoImageListItem.popupswwapp);
				},500);
			}).unbind("click").bind("click", function(ev){
				ev.stopPropagation();
				ev.preventDefault();
				return false;
			});
			return false;
		}).unbind("mouseleave").bind("mouseleave", function(ev){
			clearTimeout(appdb.views.VoImageListItem.popupswwappinerval);
			appdb.views.VoImageListItem.popupswwappinerval = setTimeout(function(){
				dijit.popup.close(appdb.views.VoImageListItem.popupswwapp);
			},500);
		});
		return $(html);
	};
	return _render();
};
appdb.views.VoImageListItemCell.ActionList = function(dom, row, col, data) {
	var acts = data.actions || [];
	var html = $("<div class='actions'></div>");
	var _renderButton = function(e, act) {
		var button = $("<button class='btn'></button>");
		$(button).text(act.displayName);
		switch (act.action) {
			case "remove":
				$(button).addClass("btn-danger");
				break;
			case "add":
				$(button).addClass("btn-primary");
				break;
			case "update":
				$(button).addClass("btn-warning");
				break;
			default:
				break;
		}
		$(button).attr("data-action", act.action);
		$(e).append(button);
		return button;
	};
	if (acts.length === 0)
		return $(html);

	if (acts.length > 0) {
		$.each(acts, function(i, e) {
			_renderButton(html, e);
		});
	}

	$(html).find("button").unbind("click").bind("click", (function(parent) {
		return function(ev) {
			ev.preventDefault();
			parent.onAction({action: $(this).data("action")});
			return false;
		};
	})(row));
	return $(html);
};
appdb.views.VoImageList = appdb.ExtendClass(appdb.View, "appdb.views.VoImageList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		canEdit: o.canEdit || false,
		dom: {
			headercontainer: $("<div class='header'></div>"),
			bodycontainer: $("<div class='body'></div>"),
			header: $("<table></table>"),
			body: $("<table></table>"),
			topheader: $("<div class='topheader'></div>")
		},
		data: o.data || [],
		deletedvas: o.deletedvas || [],
		listState: null,
		quickSearches: [],
		running: {"add": [], "remove": [], "update": []},
		cols: [
			{name: "published", columnName: "Published", searchTip: "Search by state...", searchRender: function() {
					var html = $("<select><option value=''></option></select>");
					var states = {
						"true": "yes",
						"false": "no"
					};
					for (var i in states) {
						if (states.hasOwnProperty(i) === false)
							continue;
						$(html).append("<option value='" + i + "'>" + states[i] + "</option>");
					}
					$(html).bind("change", function() {
						var v = $(this).val();
						$(this).parent().find("input").attr("value", v);
						$(this).parent().find("input").val(v);
						$(this).parent().find("input").trigger("keyup");
					});
					return $(html);
				}, renderer: appdb.views.VoImageListItemCell.BooleanValue, onFalseHtml: "<div class='publishedviewer icontext'><img src='/images/yes_grey.png' alt'/></div>", onTrueHtml: "<div class='publishedviewer published icontext'><img src='/images/yes.png' alt'/><img class='isexpired' src='/images/history.png' alt='' title='This virtual appliance has expired'/></div>"},
			{name: "id", columnName: "ID", display: false},
			{name: "name", columnName: "Name", searchTip: "Search...", renderer: appdb.views.VoImageListItemCell.VapplianceInfo},
			{name: "version", columnName: "Version", display: false},
			{name: "swappliance",display: (appdb.config.features.swappliance), columnName: "Required by <br>Software Appliances<a href='https://wiki.appdb.egi.eu/main:faq:supported_software_appliances' target='_blank'  title='Read more about software appliances'><img src='/images/question_mark.gif' alt=''></a>", searchTip: "Search...", getValues: function(obj){
					var res = [];
					$.each(obj.options.data.swappliance, function(i,e){
						res.push(e.name);
					});
					return res;
					
			},renderer: appdb.views.VoImageListItemCell.SWappSupport, searchRender: function() {
					var html = $("<select><option value=''></option></select>");
					var uniq = appdb.model.StaticList.SwapplianceReportUnique;
					$.each(uniq, function(i,e){
						$(html).append("<option value='" + e.name + "'>" + e.name + "</option>");
					});
					$(html).bind("change", function() {
						var v = $(this).val();
						$(this).parent().find("input").attr("value", v);
						$(this).parent().find("input").val(v);
						$(this).parent().find("input").trigger("keyup");
					});
					return $(html);
				}},
			{name: "hypervisors", columnName: "Hypervisors", display: (!appdb.config.features.swappliance), searchTip: "Search...", renderer: appdb.views.VoImageListItemCell.Expandable},
			{name: "oses", columnName: "Oses", searchTip: "Search (eg ubuntu, windows ) ...", renderer: appdb.views.VoImageListItemCell.Expandable, maxheight: "25"},
			{name: "archs", columnName: "Achitectures", display: (!appdb.config.features.swappliance), searchTip: "Search (eg x86_64, Alpha, RISK ) ...", renderer: appdb.views.VoImageListItemCell.Expandable, maxheight: "25"},
			{name: "actions", columnName: "Allowed<br/>Actions", searchRender: function() {
					var html = $("<select><option value=''></option></select>");
					var states = {
						"update": "update",
						"add": "add",
						"remove": "remove"
					};
					for (var i in states) {
						if (states.hasOwnProperty(i) === false)
							continue;
						$(html).append("<option value='" + i + "'>" + states[i] + "</option>");
					}
					$(html).bind("change", function() {
						var v = $(this).val();
						$(this).parent().find("input").attr("value", v);
						$(this).parent().find("input").val(v);
						$(this).parent().find("input").trigger("keyup");
					});
					return $(html);
				}, renderer: appdb.views.VoImageListItemCell.ActionList, getValues: function(obj) {
					var acts = obj.options.data.actions || [];
					var res = [];
					$.each(acts, function(i, e) {
						res.push(e.action);
					});
					return res;
				}},
			{name: "warnings", columnName: "Messages", searchTip: "Search (eg marked to be added)...", renderCell: function(obj, col) {
					return "<div class='warningviewer icontext'><span class='value'>" + obj.options.data[col.name] + "</span></div>";
				}},
			{name: "states", columnName: "States", display: false, getValues: function(obj) {
					var dom = obj.dom;
					var states = [];
					if ($(dom).hasClass("action-added")) {
						states.push("added");
					}
					if ($(dom).hasClass("action-removed")) {
						states.push("removed");
					} else if ($(dom).hasClass("action-updated")) {
						states.push("updated");
					}
					return states;
				}}
		]
	};
	this.getColumns = function() {
		return this.options.cols || [];
	};
	this.reset = function() {
		this.unsubscribeAll();
		if (this.options.listState) {
			this.options.listState.unsubscribeAll();
			this.options.listState.reset();
			this.options.listState = null;
		}
		this.subviews = this.subviews || [];
		this.subviews = $.isArray(this.subviews) ? this.subviews : [this.subviews];
		$.each(this.subviews, function(i, e) {
			if (e) {
				if (e.reset)
					e.reset();
				e = null;
			}
		});
		this.subviews = [];
		$(window).unbind("scroll.stickyvo");
	};
	this.createErrorLink = function(text, errortext) {
		text = $.trim(text) || "An error occured";
		errortext = $.trim(errortext) || "Unknown error occured";
		var a = $("<a href='#' title='' class='icontext errormessage'><img src='/images/vappliance/redwarning.png' alt=''/><span></span></a>");
		$(a).find("span").text(text);
		var content = $("<div class='voimagelistaction error'></div>");
		$(content).text(errortext);
		$(a).unbind("click").bind("click", (function(self, content) {
			return function(ev) {
				ev.preventDefault();
				if (appdb.views.VoImageList.popup) {
					appdb.views.VoImageList.popup.destroyRecursive(false);
					appdb.views.VoImageList.popup = null;
				}
				appdb.views.VoImageList.popup = new dijit.TooltipDialog({content: $(content)[0]});
				dijit.popup.open({
					parent: $(this)[0],
					popup: appdb.views.VoImageList.popup,
					around: $(this)[0],
					orient: {'BR': 'TR', 'BL': 'TL'}
				});
				return false;
			};
		})(this, content));
		return a;
	};
	this.renderError = function(enable, error, action, data) {
		enable = (typeof enable === "boolean") ? enable : false;
		action = $.trim(action).toLowerCase();
		var d = null;
		var id = null;
		var tr;
		id = data.id;
		tr = $(this.dom).find("#r" + id);
		$(tr).removeClass("onaction").removeAttr("data-onaction");
		d = $(this.dom).find("tr#r" + data.id + " td[data-name='warnings'] .value");
		$(d).find(".errormessage").remove();
		if (enable) {
			$(tr).addClass("onaction").attr("data-onaction", action);
		}
		if (enable === true) {
			var errortext = "An error occured";
			switch (action) {
				case "update":
					errortext = "Could not update";
					break;
				case "add":
					errortext = "Could not add";
					break;
				case "remove":
					errortext = "Could not remove";
					break;
				default:
					return;
			}
			$(d).append(this.createErrorLink(errortext, error));
		}
	};
	this.createWarningLink = function(text, errortext) {
		text = $.trim(text) || "An error occured";
		errortext = $.trim(errortext) || "Unknown error occured";
		var a = $("<a href='#' title='' class='icontext warningmessage'><img src='/images/vappliance/warning.png' alt=''/><span></span></a>");
		$(a).find("span").text(text);
		var content = $("<div class='voimagelistaction warning'></div>");
		$(content).html(errortext);
		$(a).unbind("click").bind("click", (function(self, content) {
			return function(ev) {
				ev.preventDefault();
				if (appdb.views.VoImageListItem.popup) {
					appdb.views.VoImageListItem.popup.destroyRecursive(false);
					appdb.views.VoImageListItem.popup = null;
				}
				appdb.views.VoImageListItem.popup = new dijit.TooltipDialog({content: $(content)[0]});
				dijit.popup.open({
					parent: $(this)[0],
					popup: appdb.views.VoImageListItem.popup,
					around: $(this)[0],
					orient: {'BL': 'TL', 'BR': 'TR'}
				});
				return false;
			};
		})(this, content));
		return a;
	};
	this.renderWarning = function(enable, data, warning, message) {
		enable = (typeof enable === "boolean") ? enable : false;
		var d = null;
		var id = null;
		var tr;
		id = data.id;
		tr = $(this.dom).find("#r" + id);
		$(tr).removeClass("onaction").removeAttr("data-onaction");
		d = $(this.dom).find("tr#r" + data.id + " td[data-name='warnings'] .value");
		$(d).find(".warningmessage").remove();
		if (enable) {
			$(tr).addClass("onaction").attr("data-onaction", action);
		}
		if (enable === true) {
			$(d).append(this.createWarningLink(warning, message));
		}
	};
	this.renderAction = function(enable, action, data) {
		enable = (typeof enable === "boolean") ? enable : false;
		action = $.trim(action).toLowerCase();
		var d = null;
		var id = null;
		var tr;
		if (action !== "publishing") {
			id = data.id;
			tr = $(this.dom).find("#r" + id);
			$(tr).removeClass("onaction").removeAttr("data-onaction");
			d = $(this.dom).find("tr#r" + data.id + " td[data-name='actions'] .customcellwrap");
			$(d).find(".loader").remove();
			if (enable) {
				$(tr).addClass("onaction").attr("data-onaction", action);
			}
		} else {
			d = $(this.dom).find(".customcellwrap");
			$(d).removeClass("publishing");
		}
		if (enable === true) {
			var actiontext = "Processing";
			switch (action) {
				case "update":
					actiontext = "Updating";
					break;
				case "add":
					actiontext = "Adding";
					break;
				case "remove":
					actiontext = "Removing";
					break;
				case "publishing":
					actiontext = "Publishing Image list";
					$(d).addClass("publishing");
					break;
			}
			$(d).append("<div class='loader'><div class='sheet'></div><div class='messagecontainer icontext'><img src='/images/ajax-loader-trans-orange.gif' alt='' /><span class='message'>" + actiontext + "</span></div></div>");
		}
	};
	this.getTableData = function(d) {
		var res = [];

		$.each(d, function(i, e) {
			var row = {
				id: e.id,
				name: e.name,
				isExpired: (e.appliance && $.trim(e.appliance.expired) === "true") ? true : false,
				expiresOn: (e.appliance && $.trim(e.appliance.expireson) !== "") ? e.appliance.expireson : "",
				hypervisors: [],
				oses: [],
				archs: [],
				secant: (e.secant || [])
			};
			e.voimagelist = e.voimagelist || {};
			e.voimagelist.images = e.voimagelist.images || {};
			e.appliance = e.appliance || {};
			e.appliance.os = e.appliance.os || [];
			e.appliance.os = $.isArray(e.appliance.os) ? e.appliance.os : [e.appliance.os];
			e.appliance.arch = e.appliance.arch || [];
			e.appliance.arch = $.isArray(e.appliance.arch) ? e.appliance.arch : [e.appliance.arch];
			e.appliance.hypervisor = e.appliance.hypervisor || [];
			e.appliance.hypervisor = $.isArray(e.appliance.hypervisor) ? e.appliance.hypervisor : [e.appliance.hypervisor];

			row.version = $.trim(e.appliance.version);
			$.each(e.appliance.os, function(ii, ee) {
				row.oses.push((ee.val) ? ee.val() : ee);
			});
			row.oses = $.unique(row.oses);
			row.oses.sort(function(a, b) {
				if (a === "Other")
					return 1000;
				if (a < b)
					return -1;
				if (a > b)
					return 1;
				return 0;
			});
			row.oses = row.oses.join(",");

			$.each(e.appliance.arch, function(ii, ee) {
				row.archs.push((ee.val) ? ee.val() : ee);
			});
			row.archs = row.archs.join(",");

			$.each(e.appliance.hypervisor, function(ii, ee) {
				row.hypervisors.push((ee.val) ? ee.val() : ee);
			});
			row.hypervisors = row.hypervisors.join(",");
			row.published = (e.voimagelist.isPublished === true);
			row.warnings = "";
			row.warnings = (row.warnings === "" && e.voimagelist.isOutdated) ? "" : "";
			row.states = "";
			row.actions = [];
			if (e.voimagelist.isOutdated && row.isExpired !== true) {
				row.actions.push({action: "update", displayName: "update"});
			}

			if ((e.voimagelist.isPublished && !e.voimagelist.isRemoved) || (!e.voimagelist.isPublished && e.voimagelist.isAdded)) {
				row.actions.push({action: "remove", displayName: "remove"});
			} else if (row.isExpired !== true) {
				row.actions.push({action: "add", displayName: "add"});
			}
			e.isExpired = false;
			if ($.trim(e.expireson) !== "") {
				var exp = new Date(e.expireson);
				if (Date.now() > exp) {
					e.isExpired = true;
				}
			}
			row.swappliance = e.swappliance;
			row.data = e;
			res.push(row);
		});
		return res;
	};
	this.getRunningRequests = function() {
		return this.options.running;
	};
	this.getRunningRequest = function(action, id) {
		var runs = this.getRunningRequests();
		if (!runs[action]) {
			return null;
		}
		var run = runs[action];
		run = run || [];
		run = $.isArray(run) ? run : [run];
		var res = $.grep(run, function(i, e) {
			return ($.trim(e.id) === $.trim(id));
		});
		return (res.length > 0) ? res[0] : null;
	};
	this.getRunningRequestIndex = function(action, id) {
		var runs = this.getRunningRequests();
		if (!runs[action]) {
			return null;
		}
		var run = runs[action];
		run = run || [];
		run = $.isArray(run) ? run : [run];
		var index = -1;
		$.each(run, function(i, e) {
			if (index < 0 && $.trim(e.id) === $.trim(id)) {
				index = i;
			}
		});
		return index;
	};
	this.addRunningRequest = function(action, data) {
		if (!this.options.running[action])
			return;
		this.options.running[action].push(data);
	};
	this.removeRunningRequest = function(action, id) {
		if (!this.options.running[action])
			return;
		var req = this.getRunningRequestIndex(action, id);
		if (req < 0)
			return;
		this.options.running[action].splice(req, 1);
	};
	this.getRunningCount = function() {
		var count = 0;
		var run = this.getRunningRequests();
		for (var i in run) {
			if (run.hasOwnProperty(i) === false)
				continue;
			count += run[i].length;
		}
		return count;
	};
	this.afterItemAction = function(action, data) {
		var id = data.id;
		d = data.data || {};
		switch (action) {
			case "update":
				d.voimagelist.isOutdated = false;
				d.voimagelist.isRemoved = false;
				d.voimagelist.wasOutdated = true;
				break;
			case "add":
				d.voimagelist.isOutdated = false;
				d.voimagelist.isRemoved = false;
				d.voimagelist.isAdded = true;
				break;
			case "remove":
				d.voimagelist.isOutdated = false;
				d.voimagelist.isRemoved = true;
				d.voimagelist.isAdded = false;
				break;
			case "publish":
			case "revertchanges":
				appdb.pages.vo.reload();
				return;
		}
		this.updateItem(d);
		this.options.listState.updateValues();
		$.each(this.options.quickSearches, function(i, e) {
			e.cache();
		});
		$(this.options.dom.body).trigger('update');
	};
	this.onItemAction = function(v) {
		this.renderAction(true, v.action, v.data);
		this.addRunningRequest(v.action, v.data);
		this.options.listState.updateRunningStates();
		this.parent.onAction(v.action, v.data, (function(self, obj) {
			return function(v) {
				v = v || {};
				self.removeRunningRequest(obj.action, obj.data.id);
				self.renderAction(false, obj.action, obj.data);
				if (v.error) {
					self.renderError(true, v.error, obj.action, obj.data);
					return;
				}
				self.renderError(false, "", obj.action, obj.data);
				self.afterItemAction(obj.action, obj.data);
				self.options.listState.updateRunningStates();
			};
		})(this, v));
	};
	this.updateItem = function(data) {
		var id = $.trim(data.id);
		var d = (this.getTableData([data]) || []);
		if (d.length === 0)
			return;

		var item = $.grep(this.subviews, function(e) {
			return (e.getId && $.trim(e.getId()) === id);
		});
		if (item.length === 0) {
			return;
		}
		item[0].render(d[0]);
	};

	this.updateSecantReportsForVappliance = function(vappid, reports) {
	    var item = $.grep(this.subviews, function(e) {
		    return (e.getId && $.trim(e.getId()) === $.trim(vappid));
	    });
	    item = (item.length > 0) ? item[0] : null;

	    if (item) {
		    var itemSecantReports = (((item.options || {}).data || {}).secant || []);

		    $.each(reports, function(ii, report) {
			for(var i = 0; i < itemSecantReports.length; i++) {
				if ($.trim(report.report_id) === $.trim(itemSecantReports[i].report_id)) {
				        itemSecantReports[i] = report;
				}
			}
		    });

		    item.options.data.secant = itemSecantReports;
		    item.renderSecant(item.options.data);
	    }
	};

	this.addItem = function(d) {
		d = d || {};
		if ($.trim(d.id) === "")
			return null;
		var tr = $("<tr id='r" + $.trim(d.id) + "'></tr>");
		var item = new appdb.views.VoImageListItem({
			container: tr,
			parent: this,
			data: d,
			cols: this.getColumns()
		});
		this.subviews.push(item);
		item.render();
		item.subscribe({event: "action", callback: function(v) {
				this.onItemAction(v);
			}, caller: this});
		this.publish({event: "rowadded", value: {data: d, dom: tr}});
		return tr;
	};
	this.renderDraftListState = function() {
		var dom = $("<div class='liststate'></div>");

		$(this.options.dom.topheader).find(".liststate").remove();
		$(this.options.dom.topheader).append(dom);
		if (this.options.listState) {
			this.options.listState.reset();
			this.options.listState = null;
		}
		this.options.listState = new appdb.views.VoImageListState({
			container: $(dom),
			parent: this
		});
		this.options.listState.subscribe({event: "publish", callback: function(v) {
				if (this.options.deletedvas.length > 0) {
					this.renderDeletedVAList("publish");
				} else {
					this.onPublish();
				}
			}, caller: this}).subscribe({event: "revertchanges", callback: function(v) {
				this.onRevertChanges();
			}, caller: this});
		this.options.listState.render();
		setTimeout((function(self) {
			return function() {
				if (self.options.listState !== null) {
					self.options.listState.checkPublish();
				}
			};
		}(this)), 1);

	};
	this.renderPublishedImageListBanner = function() {
		$(this.options.dom.topheader).find(".voimagelistbannercontainer").remove();
		var dom = $("<div class='voimagelistbannercontainer'></div>");
		$(this.options.dom.topheader).append(dom);
		var obj = new appdb.views.VoImageListBanner({container: dom, parent: this, canRender: this.hasPublishedList()});
		obj.render();
	};
	this.renderLegend = function() {
		$(this.options.dom.topheader).find(".voimagelistlegendcontainer").remove();
		var dom = $("<div class='voimagelistlegendcontainer'></div>");
		$(this.options.dom.topheader).append(dom);
		var obj = new appdb.views.VoImageListLegend({
			container: dom,
			parent: this
		});
		obj.render();
		obj.subscribe({event: "search", callback: function(name) {
				$(this.options.dom.header).find("tbody td.search-states input").val($.trim(name));
				$(this.options.dom.header).find("tbody td.search-states input").trigger("keyup");
			}, caller: this});
	};
	this.renderDeletedVAList = function(displaypublish) {
		displaypublish = $.trim((typeof displaypublish === "string") ? displaypublish : "");
		var canrepublish = false;
		var canpublish = false;
		if ($.trim(displaypublish).toLowerCase() === "publish") {
			canpublish = true;
		} else if ($.trim(displaypublish).toLowerCase() === "republish") {
			canrepublish = true;
		}
		if (displaypublish.length > 1) {
			displaypublish = displaypublish[0].toUpperCase() + displaypublish.substr(1);
		}
		var dels = this.options.deletedvas;
		if (appdb.views.VoImageList.dialog_deleted) {
			appdb.views.VoImageList.dialog_deleted.hide();
			appdb.views.VoImageList.dialog_deleted.destroyRecursive(false);
			appdb.views.VoImageList.dialog_deleted = null;
		}
		dels = dels || [];
		dels = $.isArray(dels) ? dels : [dels];

		var html = $("<div class='deletedvalist-container'><div class='header'></div><div class='main'><ul class='title'></ul><ul class='values'></ul></div><div class='footer'><div class='actions'><button class='btn btn-warning btn-compact action close' title='Close this dialog'>Close</button></div></div></div>");
		$(html).find(".header").text("The following virtual appliances are deleted by one of their users (owners, va managers etc):");
		$(html).find(".main").append("<div>These virtual appliances will be <b>excluded</b> from the image list upon publishing." + ((canrepublish) ? "<br/>It is recommended to republish the image list." : "") + "</div>");
		var ultitle = $(html).find(".main ul.title");
		$(ultitle).append("<li class='title'><span class='name'>Name</span><span class='version'>version</span><span class='images'># images</span></li>");
		var ul = $(html).find(".main ul.values");
		$.each(dels, function(i, e) {
			var li = $("<li></li>");
			var normname = $.trim(e.name).split("-DELETED-")[0];
			$(li).append("<span class='name'>" + normname + "</span>");
			$(li).append("<span class='version'>" + e.va_version + "</span>");
			$(li).append("<span class='images'>" + e.instances.length + "</span>");
			$(ul).append(li);
		});
		$(html).find(".footer .action.close").unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				appdb.views.VoImageList.dialog_deleted.hide();
				appdb.views.VoImageList.dialog_deleted.destroyRecursive(false);
				appdb.views.VoImageList.dialog_deleted = null;
				return false;
			};
		})(this));
		if (canrepublish || canpublish) {
			var publishhtml = $("<button class='btn btn-primary btn-compact action publish' title='" + ((canrepublish) ? "Republish image list, excluding deleted VAs" : "Publish changes") + "'>" + displaypublish + "</button>");
			$(html).find(".footer .actions").append(publishhtml);
			$(publishhtml).unbind("click").bind("click", (function(self) {
				return function(ev) {
					ev.preventDefault();
					self.onPublish();
					if (appdb.views.VoImageList.dialog_deleted) {
						appdb.views.VoImageList.dialog_deleted.hide();
						appdb.views.VoImageList.dialog_deleted.destroyRecursive(false);
						appdb.views.VoImageList.dialog_deleted = null;
					}
					return false;
				};
			})(this));
		}
		appdb.views.VoImageList.dialog_deleted = new dijit.Dialog({
			title: "Deleted virtual appliances",
			style: "width:470px",
			content: $(html)[0]
		});
		appdb.views.VoImageList.dialog_deleted.show();
	};
	this.renderDeletedVas = function(dels) {
		var dels = this.options.deletedvas;
		dels = dels || [];
		dels = $.isArray(dels) ? dels : [dels];
		$(this.options.dom.topheader).find(".deletedvas-container").remove();
		var plural = "s";
		if (dels.length === 0) {
			return;
		} else if (dels.length === 1) {
			plural = "";
		}
		var html = $('<div class="deletedvas-container"><div class="deletedvas"><span class="count"></span><span>published VA' + plural + ' will be excluded due to user deletion</span></div><a href="' + appdb.config.endpoint.wiki + '" target="_blank" title="View explanation" class="icontext action learnmore">learn more</a><button class="btn btn-warning btn-compact action view">view VA' + plural + '</button></div>');
		$(html).find(".count").text(dels.length);
		$(this.options.dom.topheader).append(html);
		$(html).find(".action.view").unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.renderDeletedVAList("republish");
				return false;
			};
		})(this));
	};
	this.onPublish = function() {
		this.onItemAction({action: "publish", data: this.options.data});
	};
	this.onRevertChanges = function() {
		this.onItemAction({action: "revertchanges", data: this.options.data});
	};
	this.getAdded = function() {
		return $.grep(this.subviews, function(e) {
			return e.isAdded() || false;
		});
	};
	this.getRemoved = function() {
		return $.grep(this.subviews, function(e) {
			return e.isRemoved() || false;
		});
	};
	this.getUpdated = function() {
		return $.grep(this.subviews, function(e) {
			return e.isUpdated() || false;
		});
	};
	this.getDeleted = function() {
		this.options.deletedvas = this.options.deletedvas || [];
		this.options.deletedvas = $.isArray(this.options.deletedvas) ? this.options.deletedvas : [this.options.deletedvas];
		return this.options.deletedvas;
	};
	this.renderTable = function() {
		var headertable = $(this.options.dom.header);
		var header = $("<thead></thead>");
		$(headertable).empty().append(header);

		var tr = $("<tr></tr>");

		$.each(this.options.cols, function(i, e) {
			var th = $("<th></th>");
			var div = $("<div></div>");
			$(th).attr("data-name", $.trim(e.name));
			$(th).attr("SCOPE", "col");
			if (e.display === false) {
				$(th).addClass("hidden");
			}
			$(div).html($.trim(e.columnName || e.name));
			$(div).find("a").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.stopPropagation();
				};
			})(this));
			$(th).append(div);
			$(tr).append(th);

		});
		$(header).append(tr);

		$(this.options.dom.body).empty().append($(header).clone());
		$(this.options.dom.body).append("<tbody></tbody>");
		$(headertable).append("<tbody></tbody>");
	};
	this.renderSearchable = function() {
		var tr = $("<tr></tr>");
		this.options.quickSearches = [];
		$(this.options.cols).each((function(self, row) {
			return function(i, e) {
				var td = $("<td class='searchable'></td>");
				$(td).addClass("search-" + e.name);
				var inp = $("<input type='text' value='' />");
				if (e.searchable !== false) {
					if ($.trim(e.searchTip) !== "") {
						$(inp).attr("placeholder", $.trim(e.searchTip));
					}
					$(td).append(inp);
					var qs = $(inp).quicksearch($(self.options.dom.body).find("tbody tr"), {
						selector: "td[data-name='" + e.name + "'] > .value",
						noResults: "#novoimagelistresults",
						show: (function(col) {
							return function() {
								var d = $.trim($(this).data("hide-names")).split(" ");
								var name = $.trim(col.name);
								var newd = $.grep(d, function(ee) {
									return (name !== $.trim(ee));
								});
								$(this).data("hide-names", newd.join(" "));
								if (newd.length === 0) {
									$(this).removeClass("hidden");
								}
							};
						})(e),
						hide: (function(col) {
							return function() {
								var d = $.trim($(this).data("hide-names")).split(" ");
								var name = $.trim(col.name);
								var newd = $.grep(d, function(ee) {
									return (name !== $.trim(ee));
								});
								newd.push(name);
								$(this).data("hide-names", newd.join(" "));
								$(this).addClass("hidden");
							};
						})(e),
						testQuery: function(query, txt, _row) {
							txt = $.trim(txt);
							for (var i = 0; i < query.length; i += 1) {
								if (txt.indexOf(query[i]) === -1) {
									return false;
								}
							}
							return true;
						}
					});
					self.options.quickSearches.push(qs);
					if (typeof e.searchRender === "function") {
						$(inp).addClass("hidden");
						$(td).append(e.searchRender());
					}
					if (e.display === false) {
						$(td).addClass("hidden");
					}
				}
				$(row).append(td);
			};
		})(this, tr));

		$(this.options.dom.header).find("tbody").empty();
		$(this.options.dom.header).find("tbody").append(tr);
		var colspan = 0;
		$(this.options.dom.header).find("tbody tr:first td").each(function(i, e) {
			if ($(e).hasClass("hidden") === false)
				colspan += 1;
		});
		$(this.options.dom.body).find("tbody").prepend("<tr id='novoimagelistresults' style='display:none;'><td colspan='" + colspan + "'><div class='emptycontent'><span>No results</span></div></td></tr>");
	};
	this.renderSortable = function() {
		$(this.options.dom.header).tablesorter();
		$(this.options.dom.body).tablesorter();
		$(this.options.dom.header).find("thead tr th").mouseup((function(self) {
			return function(ev) {
				ev.preventDefault();
				ev.stopPropagation();
				var th = $($(self.options.dom.body).find("thead tr th").get($(this).index()));
				var t = this;
				setTimeout(function() {
					if ($(th).hasClass("headerSortUp")) {
						$(t).removeClass("headerSortDown");
						$(t).addClass("headerSortUp");
					} else if ($(th).hasClass("headerSortDown")) {
						$(t).removeClass("headerSortUp");
						$(t).addClass("headerSortDown");
					} else {
						$(t).removeClass("headerSortUp");
						$(t).removeClass("headerSortDown");
					}
				}, 100);
				$(th).trigger('click');
				return false;
			};
		})(this));
	};
	this.getStickyScroll = function(stickoffset) {
		stickoffset = ($.trim(stickoffset).replace(/px/g, "") << 0) || 0;
		return (function(self, stickyoffset) {
			var parent = $(self).parent();
			var y = 0;
			var width = "";
			var height = "";
			var _setup = function() {
				var paroffset = $(parent).offset();
				y = paroffset.top - $(window).scrollTop();
				width = $(parent).width() + "px";
				height = $(parent).height() + "px";
				$(parent).css("width", width).css("height", height);
				$(self).css("width", width);
			};
			var _scroll = function() {
				var yy = Math.max(0, y - $(this).scrollTop());
				if (yy >= stickyoffset) {
					$(self).removeClass("stickytop");
					$(parent).removeClass("hasstickychildren");
				} else {
					$(self).addClass("stickytop").css("top", stickyoffset + "px");
					$(parent).addClass("hasstickychildren");
				}
			};
			var _lazy = function() {
				if ($(parent).is(":visible")) {
					_setup();
					_scroll();
					_func = _scroll;
				}
			};
			var _func = _lazy;
			return function() {
				_func();
			};
		})(this.options.dom.headercontainer, stickoffset);
	};
	this.renderHeaderFixed = function() {
		if ($.browser.msie)
			return;
		$(window).unbind("scroll.stickyvo").bind("scroll.stickyvo", this.getStickyScroll(40));
	};
	this.hasPublishedList = function() {
		var d = this.parent.getPublishedImageList();
		return (!d) ? false : true;
	};
	this.render = function(d, vodata, deletedvas,swapps) {
		this.reset();
		if (typeof d !== "undefined") {
			d = d || [];
			d = $.isArray(d) ? d : [d];
			this.options.data = d;
		}
		this.renderTable();
		this.options.deletedvas = deletedvas || [];
		this.options.deletedvas = $.isArray(this.options.deletedvas) ? this.options.deletedvas : [this.options.deletedvas];
		this.options.data = this.options.data || [];
		this.options.data = $.isArray(this.options.data) ? this.options.data : [this.options.data];
		this.options.tableData = this.getTableData(this.options.data);
		$.each(this.options.tableData, (function(self) {
			return function(i, e) {
				var tr = self.addItem(e, i);
				if (tr !== null) {
					$(self.options.dom.body).children("tbody").append(tr);
				}
			};
		}(this)));
		this.renderSortable();
		this.renderSearchable();
		this.renderHeaderFixed();
		this.renderPublishedImageListBanner();
		this.renderLegend();
		this.renderDeletedVas();
		this.renderDraftListState();
	};

	this._initContainer = function() {
		$(this.options.dom.headercontainer).append(this.options.dom.topheader);
		$(this.options.dom.headercontainer).append(this.options.dom.header);
		$(this.options.dom.bodycontainer).append(this.options.dom.body);
		$(this.options.dom.header).attr("cellpadding", "0");
		$(this.options.dom.header).attr("cellspacing", "0");
		$(this.options.dom.header).addClass("tablesorter");
		$(this.options.dom.body).attr("cellpadding", "0");
		$(this.options.dom.body).attr("cellspacing", "0");
		$(this.options.dom.body).addClass("tablesorter");
		$(this.dom).append(this.options.dom.headercontainer).append(this.options.dom.bodycontainer);
	};

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.subviews = [];
		this._initContainer();
	};
	this._init();
}, {
	dialog_deleted: null,
	popup: null
});

appdb.views.VoImageListState = appdb.ExtendClass(appdb.View, "appdb.views.VoImageListState", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			header: $("<div class='header'></div>"),
			main: $("<div class='main'></div>"),
			footer: $("<div class='footer'></div>"),
			actions: $("<div class='actions'></div>"),
			runningmessage: $("<div class='message icontext'><img src='/images/history.png' alt=''/><span>Processing requests...</span></div>"),
			states: $("<ul></ul>")
		}
	};
	this.getState = function(state) {
		state = $.trim(state).toLowerCase();
		var res = [];
		if (this.parent === null || state === "")
			return res;
		var fnname = "";
		switch (state) {
			case "added":
				fnname = "getAdded";
				break;
			case "removed":
				fnname = "getRemoved";
				break;
			case "updated":
				fnname = "getUpdated";
				break;
			case "deleted":
				fnname = "getDeleted";
				break;
			default:
				return [];
		}
		if (typeof this.parent[fnname] === "function") {
			res = this.parent[fnname]();
			res = res || [];
			res = $.isArray(res) ? res : [res];
		}
		return res;
	};
	this.renderValueField = function(name, value, displayName) {
		name = $.trim(name);
		value = ($.trim(value) << 0);
		displayName = $.trim(displayName);

		var html = $("<div class='fieldvalue'><div class='field'></div><img src='/images/ajax-loader-trans-orange.gif' alt=''/><div class='value'></div></div>");
		$(html).find(".field").text(displayName || name);
		$(html).find(".value").text(value || 0);
		return $(html);
	};
	this.renderAdded = function(count) {
		count = ($.trim(count) << 0);
		var html = $("<li data-state='added'></li>");
		$(html).append(this.renderValueField("added", count, "Added"));
		return $(html);
	};
	this.renderRemoved = function(count) {
		count = ($.trim(count) << 0);
		var html = $("<li data-state='removed'></li>");
		$(html).append(this.renderValueField("removed", count, "Removed"));
		return $(html);
	};
	this.renderUpdated = function(count) {
		count = ($.trim(count) << 0);
		var html = $("<li data-state='updated'></li>");
		$(html).append(this.renderValueField("updated", count, "Updated"));
		return $(html);
	};
	this.renderDeleted = function(count) {
		count = ($.trim(count) << 0);
		var html = $("<li data-state='deleted'></li>");
		$(html).append(this.renderValueField("deleted", count, "Deleted"));
		return $(html);
	};
	this.renderPublishing = function(enable) {
		enable = (typeof enable === "boolean") ? enable : false;
		$(this.dom).removeClass("publishing");
		if (enable) {
			$(this.dom).addClass("publishing");
		}
	};
	this.onPublish = function() {
		this.publish({event: "publish", value: this});
	};
	this.onRevertChanges = function() {
		this.publish({event: "revertchanges", value: this});
	};
	this.checkPublish = function() {
		var changescount = this.getState("added").length + this.getState("removed").length + this.getState("updated").length;
		var canpublish = (((changescount + this.getState("deleted").length) > 0) ? true : false);
		if (canpublish === true) {
			$(this.options.dom.actions).find(".action.publish").removeClass("btn-disabled disabled").addClass("btn-primary").removeAttr("disabled");
		} else {
			$(this.options.dom.actions).find(".action.publish").addClass("btn-disabled disabled").removeClass("btn-primary").attr("disabled", "disabled");
		}
		if (changescount > 0) {
			$(this.options.dom.actions).find(".action.revert").removeClass("btn-disabled disabled").addClass("btn-warning").removeAttr("disabled");
		} else {
			$(this.options.dom.actions).find(".action.revert").addClass("btn-disabled disabled").removeClass("btn-warning").attr("disabled", "disabled");
		}
		return (canpublish > 0);
	};
	this.renderActions = function() {
		$(this.options.dom.actions).empty();
		var revertchanges = $("<button class='action btn btn-warning btn-compact revert' title='Revert changes'>Revert changes</button>");
		var publishaction = $("<button class='action btn btn-primary btn-compact publish' title='Publish changes'>Publish changes</button>");

		$(revertchanges).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				if (self.checkPublish()) {
					self.onRevertChanges();
				}
				return false;
			};
		})(this));
		$(publishaction).unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				if (self.checkPublish()) {
					self.onPublish();
				}
				return false;
			};
		})(this));
		$(this.options.dom.actions).append(revertchanges).append(publishaction);
		if (this.getState("deleted").length > 0) {
			var viewdeleted = $("<button class='action btn btn-danger btn-compact viewdeleted' title='View deleted VAs'>View deleted VAs</button>");
			$(viewdeleted).unbind("click").bind("click", (function(self) {
				return function(ev) {
					ev.preventDefault();
					self.parent.renderDeletedVAList();
					return false;
				};
			})(this));
			$(this.options.dom.actions).prepend(viewdeleted);
		}
		this.checkPublish();
	};
	this.reset = function() {
		this.unsubscribeAll();
		$(this.dom).empty();
		this.options.dom.states = $("<ul></ul>");
		$(this.options.dom.main).append(this.options.dom.states);
		this._initContainer();
	};
	this.getRunningValues = function() {
		var running = this.parent.getRunningRequests();
		return running || [];
	};
	this.getRunningCount = function() {
		return this.parent.getRunningCount();
	};
	this.updateRunningStates = function() {
		var runs = this.getRunningValues();
		var runcount = this.getRunningCount();
		if (runcount > 0) {
			$(this.dom).addClass("running");
		} else {
			$(this.dom).removeClass("running");
		}

		for (var i in runs) {
			if (runs.hasOwnProperty(i) === false)
				continue;
			var cls = "running-" + i;
			if (runs[i].length > 0) {
				$(this.dom).addClass(cls);
			} else {
				$(this.dom).removeClass(cls);
			}
		}
	};
	this.updateValue = function(state) {
		var el = $(this.options.dom.states).find("[data-state='" + state + "']");
		var val = this.getState(state).length;
		$(el).find(".value").text(val);
		if (val > 0) {
			$(el).addClass("hasvalue");
		} else {
			$(el).removeClass("hasvalue");
		}
		this.updateRunningStates();
	};
	this.updateValues = function(state) {
		this.checkPublish();
		state = $.trim(state).toLowerCase();
		if (state !== "") {
			this.updateValue(state);
		} else {
			this.updateValue("added");
			this.updateValue("removed");
			this.updateValue("updated");
			this.updateValue("deleted");
		}
	};
	this.render = function() {
		this.updateValues();
		this.renderActions();
		this.checkPublish();
	};
	this._initContainer = function() {
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.header).append(this.options.dom.main).append(this.options.dom.footer);
		$(this.options.dom.footer).append(this.options.dom.actions).append(this.options.dom.runningmessage);
		$(this.options.dom.states).append(this.renderAdded(this.getState("added").length));
		$(this.options.dom.states).append(this.renderRemoved(this.getState("removed").length));
		$(this.options.dom.states).append(this.renderUpdated(this.getState("updated").length));
		$(this.options.dom.states).append(this.renderDeleted(this.getState("deleted").length));
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.reset();
	};
	this._init();
}, {
	popup: null
});

appdb.views.VoImageListBanner = appdb.ExtendClass(appdb.View, "appdb.views.VoImageListBanner", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		canRender: ((typeof o.canRender === "boolean") ? o.canRender : true),
		renderer: o.renderer || null
	};
	this.getData = function() {
		return (this.options.data || {});
	};
	this.canDisplay = function() {
		var res = false;
		if (typeof this.options.canRender === "string") {
			if ($.inArray($.trim(this.options.canRender).toLowerCase(), ["true", "1"]) > -1) {
				res = true;
			}
		} else if (typeof this.options.canRender === "function") {
			res = this.options.canRender(this.getData()) || false;
		} else if (typeof this.options.canRender === "boolean") {
			res = this.options.canRender;
		}
		return res;
	};
	this.reset = function() {
		this.unsubscribeAll();
		$(this.dom).empty();
	};
	this.getRenderer = function() {
		if (!this.options.renderer)
			return this.renderSimple;
		if (typeof this.options.renderer === "string" && $.trim(this.options.renderer) !== "")
			return this.renderHtml;
		if (typeof this.options.renderer === "function")
			return this.options.renderer;
		return this.options.renderSimple;
	};
	this.renderEmpty = function() {
		var html = $('<div class="voimagelistbanner empty"><span>No published VO image list available yet.</span></div>');
		$(this.dom).append(html);
	};
	this.renderHtml = function(html) {
		html = html || this.options.renderer;
		if (!html)
			return;
		$(this.dom).append($(html));
	};
	this.renderSimple = function() {
		var html = $('<div class="voimagelistbanner simple"><span><a href="' + appdb.config.endpoint.vmcaster + 'store/vo/' + appdb.pages.vo.currentName() + '/image.list" title="Click to open published vo wide image list" class="icontext imagelistlink" target="_blank"><img src="/images/cloudmp_128.png" alt=""/><span>View published image list</span></a></span></div>');
		$(this.dom).append(html);
	};
	this.renderSmallPanel = function() {
		var html = '<div class="voimagelistbanner smallpanel">' +
				'<a href="' + appdb.config.endpoint.vmcaster + 'store/vo/' + appdb.pages.vo.currentName() + '/image.list" title="Click to open published vo wide image list" class="icontext imagelistlink" target="_blank">' +
				'<img src="/images/export.png" alt="">' +
				'<span>Published VO wide Image List</span>' +
				'<span class="generatedby">compatible with</span>' +
				'</a>' +
				'<a href="http://github.com/hepix-virtualisation/vmcatcher" class="vmcatcherlink" title="Visit vmcatcher github repository" target="_blank">vmcatcher</a>' +
				'</div>';
		$(this.dom).append($(html));
	};
	this.render = function() {
		this.reset();
		if (this.canDisplay() === false) {
			this.renderEmpty();
			return;
		}
		var renderer = this.getRenderer();
		if (renderer) {
			renderer.apply(this);
		}
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = null;
	};
	this._init();
});

appdb.views.VoImageListLegend = appdb.ExtendClass(appdb.View, "appdb.views.VoImageListLegend", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {
			list: $("<ul></ul>")
		},
		states: {
			"added": {text: "to be added", key: "added"},
			"removed": {text: "to be removed", key: "removed"},
			"updated": {text: "to be updated", key: "updated"}}
	};
	this.getSelected = function() {

	};
	this.getStateData = function(name) {
		var states = this.options.states || {};
		var name = $.trim(name).toLowerCase() || "";
		if (name === "")
			return null;
		if (!states[name])
			return null;
		return states[name];
	};
	this.getStateHtml = function(name) {
		var state = this.getStateData(name);
		if (!state)
			return null;
		var html = $("<li class='state-" + name + "'></li>");
		var chk = $("<input type='checkbox' />");
		var color = $("<div class='statecolor'></div>");
		var label = $("<div class='statelabel'></div>");
		$(label).text(state.text);
		$(html).append(chk).append(color).append(label);
		$(chk).unbind("click").bind("click", (function(self, stateobj) {
			return function(ev) {
				var ischecked = $(this).attr("checked");
				$(self.dom).find(":input").removeAttr("checked");
				setTimeout((function(el, checked) {
					if (checked === true) {
						$(el).attr("checked", "checked");
					} else {
						$(el).removeAttr("checked");
					}
				})(this, ischecked), 5);
				if (ischecked) {
					self.searchState(stateobj);
				} else {
					self.searchState();
				}
			};
		})(this, state));
		return $(html);

	};
	this.searchState = function(name) {
		var state = null;
		var key = "";
		if (typeof name === "string") {
			state = this.getStateData(name);
		} else if (name && name.key) {
			state = $.extend({}, name);
		}
		if (state) {
			key = state.key;
		}
		this.publish({event: "search", value: key});
	};
	this.reset = function() {
		this.unsubscribeAll();
		this._initContainer();
	};
	this.render = function() {
		this.reset();
		for (var i in this.options.states) {
			if (this.options.states.hasOwnProperty(i) === false)
				continue;
			var li = this.getStateHtml(i);
			if (li) {
				$(this.options.dom.list).append(li);
			}
		}
	};
	this._initContainer = function() {
		$(this.dom).empty();
		this.options.dom.list = $("<ul></ul>");
		$(this.dom).append(this.options.dom.list);
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.states = $.extend(true, this.options.states, (o.states || {}));
		this._initContainer();
	};
	this._init();
});


appdb.views.VapplianceResourceProvidersList = appdb.ExtendClass(appdb.View, "appdb.views.VapplianceResourceProvidersList", function(o) {
	/*Allowed is an array of policies to filter context scripts. Policies are: 
	 * "swappliance": Allow contextscripts provided by software appliances 
	 * "contexstscript": Allow contextscripts provided by images (explicit by users)
	 * "owned": Allow contextscripts owned by the current item. Current item is 
	 *			defined by its id in the 'this.options.itemid' variable.
	 *			Used in the case of Software Appliance usage, where only the 
	 *			contextscripts defined in the same software appliance will be 
	 *			displayed. NOTE: Will be ignored if no item id is procided
	 */
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		allowed: ($.isArray(o.allowed))?o.allowed:["contextscript"],
		itemid: $.trim(o.itemid)
	};

	this.reset = function() {
		this.unsubscribeAll();
		$(this.dom).empty();
	};
	this.showElementIds = function(el, contextscript) {
		var template = $(el).closest("li.va-template").data("template");
		var image = $(el).closest("li.va-image").data("image");
		var site = $(el).closest("li.va-site").data("site");

		var template_id = (template) ? $.trim(template.resource_name) : "";
		var occi_id = (image) ? $.trim(image.occi_id) : "";
		var endpoint_url = (site) ? $.trim(site.endpoint_url) : "";

		var endpoint_url_html = $("<div class='fieldvalue endpoint'><div class='field'>Site endpoint:</div><div class='value'></div></div>");
		$(endpoint_url_html).find(".value").text(endpoint_url);
		var template_id_html = $("<div class='fieldvalue templateid'><div class='field'>Template ID:</div><div class='value'></div></div>");
		$(template_id_html).find(".value").text(template_id);
		var occi_id_html = $("<div class='fieldvalue occi_id'><div class='field'>OCCI ID:</div><div class='value'></div></div>");
		$(occi_id_html).find(".value").text(occi_id);

		var usageids = $("<div class='usageids'></div>");
		var fieldvalues = $("<div class='fieldvalues'></div>");
		$(fieldvalues).append(endpoint_url_html).append(template_id_html).append(occi_id_html);
		
		if (contextscript ){
			if( $.isArray(contextscript) === false && $.trim(contextscript.url)) {
				var contextscript_html = $("<div class='fieldvalue contextscript_url'><div class='field'>Contextualization script:</div><div class='value'></div></div>");
				var contextscript_url = $("<a href='#' title='Download contextualization script' target='_blank'></a>");
				$(contextscript_url).attr("href", $.trim(contextscript.url)).text($.trim(contextscript.url));
				$(contextscript_html).find(".value").empty().append(contextscript_url);
				$(fieldvalues).append(contextscript_html);
			}else{
				var contextscript_html = $("<div class='fieldvalue contextscript_url'><div class='field'>Contextualization scripts:</div><div class='value'></div></div>");
				$.each(contextscript, function(ii,ee){
					var contextscript_url = $("<a href='#' title='Download contextualization script' target='_blank'></a>");
					$(contextscript_url).attr("href", $.trim(ee.url)).text($.trim(ee.url));
					$(contextscript_html).find(".value").append(contextscript_url).append("<br/>");
				});
				$(fieldvalues).append(contextscript_html);
			}
		}
		$(fieldvalues).find(".fieldvalue .value").each(function(i, e) {
			if ($.trim($(e).text()) === "") {
				$(e).addClass("empty").text("not available yet");
			}
		});
		var imageid = $("<div class='imageid'></div>");
		$(imageid).text("Image: ver." + image.vmiinstance_version + " - " + image.os.val() + " " + image.os.version + " / " + image.arch.val() + " / " + image.hypervisors);

		$(usageids).append(imageid).append(fieldvalues);

		if (this.parent.renderInlineDialog) {
			this.parent.renderInlineDialog(true, usageids, "<span>" + site.name + " IDs</span>");
		}
	};
	this.getAvailableContextScripts = function(cntxtscripts){
		contextscripts = cntxtscripts ||[];
		contextscripts = $.isArray(contextscripts)?contextscripts:[contextscripts];
		var res = $.grep(contextscripts, (function(allowed){ 
			return function(e){
				if( !e ) return false;
				if( e.application && e.application.id ){
					if($.inArray("swappliance",allowed) > -1){
						return true;
					}else{
						return false;
					}
				}
				if( $.inArray("contextscript",allowed) > -1 ) return true;
				return false;
			};
		})(this.options.allowed));
		
		if( $.inArray("owned",this.options.allowed) > -1 && this.options.itemid ){
			res = $.grep(res, (function(itemid){
				return function(e){
					return ( e && e.application && $.trim(e.application.id) === itemid );
				};
			})(this.options.itemid));
		}
		
		return res;
	};
	this.renderContextScripts = function(dom,contextscripts){
		contextscripts = contextscripts ||[];
		contextscripts = $.isArray(contextscripts)?contextscripts:[contextscripts];
		var download = $("<a href='#' target='_blank' class='downloadcscript' title='Download contextualization script'>Download</a>");
		$(download).unbind("click").bind("click", function(ev){
			ev.stopPropagation();
			return true;
		});
		
		if( contextscripts.length === 0  ){
			
		}else if(contextscripts.length === 1){
			$(download).attr("href",contextscripts[0].url).text(contextscripts[0].url);
			$(dom).append(download);
		}else{
			var select = $("<select class='refcontextscripts'></select>");
			$.each(contextscripts, function(i,e){
				var option = $("<option></option>").text($.trim(e.url));
				$(option).attr("value",e.id);
				$(select).append(option);
			});
			$(select).find("options:first").attr("selected","selected");
			$(download).attr("href",contextscripts[0].url);
			$(dom).append(select).append(download);
			
			new dijit.form.Select/*ComboBox*/({
				onChange: (function(dom){
					return function(v){
						console.log(v);
						var urls = $.grep(this.getOptions(), function(e){
							return $.trim(e.value) === v;
						});
						if( urls.length > 0 ){
							$(dom).find("a.downloadcscript").attr("href",urls[0].label);
						}
					};
				})(dom)
			}, $(select)[0]);
			$(dom).addClass("hasmany");
		}
	};
	this.renderTemplateItemList  = function(grouphash,data){
		var ul = $("<ul class='groupfieldvalues'></ul>");
		$.each(data.items, function(ii, item){
			var templates = appdb.utils.findGroupTemplatesByHash(grouphash, item.templates);
			$.each(templates, function(i,templ){
				var li = $("<li class='fieldvalues'></li>");
				var endpoint_url_html = $("<div class='fieldvalue endpoint'><div class='field'>Site endpoint:</div><div class='value'></div></div>");
				$(endpoint_url_html).find(".value").text(item.endpointurl);
				var template_id_html = $("<div class='fieldvalue templateid'><div class='field'>Template ID:</div><div class='value'></div></div>");
				$(template_id_html).find(".value").text(templ.resource_name);
				var occi_id_html = $("<div class='fieldvalue occi_id'><div class='field'>OCCI ID:</div><div class='value'></div></div>");
				$(occi_id_html).find(".value").text(item.occid);
				$(li).append(endpoint_url_html).append(template_id_html).append(occi_id_html);
				$(ul).append(li);
			});
		});
		$(ul).find(".fieldvalue .value").each(function(i, e) {
			if ($.trim($(e).text()) === "") {
				$(e).addClass("empty").text("not available yet");
			}
		});
		$(ul).find(".fieldvalues:first").addClass("current");
		if($(ul).children("li").length > 1 ){
			$(ul).addClass("haspaging");
		}
		return appdb.utils.pagifyHTMLList(ul);		
	};
	this.showElementIdsGrouped = function(el, contextscript, instance) {
		var template = $(el).closest("li.va-template").data("template");
		var image = instance;
		var site = $(el).closest("li.va-site").data("site");
		var listhtmllist = this.renderTemplateItemList(template.group_hash, instance);
		var usageids = $("<div class='usageids'></div>");
		
		var imageid = $("<div class='imageid'></div>");
		$(imageid).text("Image: ver." + image.vmiinstance_version + " - " + image.os.val() + " " + image.os.version + " / " + image.arch.val() + " / " + image.hypervisor.val());
		
		var temp = $("<div class='usagetemplate'></div>");
                var td = $(el).closest(".va-template.va-template-data");

                $(temp).append("<span>Memory: <b>" + $(td).find(".template-cell.memory > span").text() + "</b></span><span>Disk: <b>" + $(td).find(".template-cell.disk > span").text()  + "</b></span><span>CPUs: <b>" + $(td).find(".template-cell.cpus > span").text() + "</b></span><span> In\Out: <b>" + $(td).find(".template-cell.connectivity > span").text() + "</b></span><span>OS: <b>" + $(td).find(".template-cell.osfamily > span").text() +"</b></span>");

		$(usageids).append(imageid).append(temp).append(listhtmllist);
		var availablecontextscripts = this.getAvailableContextScripts(contextscript);
		if ( availablecontextscripts.length > 0 ) {
			var contextscript_html = $("<div class='fieldvalues contextscript_url'><div class='fieldvalue contextscript_url'><div class='field'>Contextualization script:</div><div class='value'></div></div></div>");
			this.renderContextScripts($(contextscript_html).find(".fieldvalue > .value"), availablecontextscripts);
			$(usageids).append(contextscript_html);
		}
		
		if (this.parent.renderInlineDialog) {
			this.parent.renderInlineDialog(true, usageids, "<span>" + site.name + " IDs</span>", "sitecontents");
		}
		
		$(usageids).find(".contextscript_url .value.hasmany").closest(".fieldvalue").addClass("hasmany");
		if( $(usageids).find(".pagifier").length > 0 ){
			$(usageids).addClass("hasmany");
		}
	};
	this.showContextScript = function(cscript, el) {
		var d = cscript || {};
		var template = $(el).closest("li.va-template").data("template");
		var image = $(el).closest("li.va-image").data("image");
		var site = $(el).closest("li.va-site").data("site");

		var dom = $("<div class='contextsrciptdetails usageids'></div>");
		var fieldvalues = $("<div class='fieldvalues'></div>");
		var url = $("<div class='fieldvalue url'><div class='field'>Download:</div><div class='value'></div></div>");
		var hash = $("<div class='fieldvalue hash'><div class='field'></div><div class='value'></div></div>");
		var fsize = $("<div class='fieldvalue size'><span class='field'>Size:</span><span class='value'></span></div>");
		var urllink = $("<a href='#' title='Download script' target='_blank'></a>");

		$(urllink).attr('href', d.url).text($.trim(d.url));
		$(url).find(".value").append(urllink);
		$(hash).find(".field").text(d.checksum.hashtype + ":");
		$(hash).find(".value").text(d.checksum.val());
		$(fsize).find(".value").text(d.size + " bytes");
		$(fieldvalues).empty().append(url).append(hash).append(fsize);

		var imageid = $("<div class='imageid'></div>");
		$(imageid).text("Image: ver." + image.vmiinstance_version + " - " + image.os.val() + " " + image.os.version + " / " + image.arch.val() + " / " + image.hypervisors);

		$(dom).append(imageid).append(fieldvalues);
		if (this.parent.renderInlineDialog) {
			this.parent.renderInlineDialog(true, dom, "<span>" + site.name + " Contextualization Script</span>");
		}
		return dom;
	};
	this.renderEmptyTemplate = function(dom) {
		$(dom).append("<div class='template-empty'><span class='message'>No templates provided</span></div><div class='template-cell action'><div class='getids' title='View related IDs'>get IDs</div></div>");
		$(dom).find(".getids").unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.showElementIds(this);
				return false;
			};
		})(this));
	};
	this.renderTemplate = function(dom, d, data) {
		d = d || {};
		var memory = $("<div class='template-cell memory'></div>");
		var cpus = $("<div class='template-cell cpus'></div>");
		var connectivity = $("<div class='template-cell connectivity'></div>");
		var osfamily = $("<div class='template-cell osfamily'></div>");
		var action = $("<div class='template-cell action'><div class='getids' title='View related IDs'></div><div class='getcontextscript hidden' title='View contextualization script'></div></div>");
		var memorytext = ($.trim(d.main_memory_size) === "" ? "-" : $.trim(d.main_memory_size));
		var cpustext = ($.trim(d.logical_cpus) === "" ? "-" : $.trim(d.logical_cpus)) + "/" + ($.trim(d.physical_cpus) === "" ? "-" : $.trim(d.physical_cpus));
		var connectivitytext = ($.trim(d.connectivity_in).toUpperCase() === "FALSE" ? "no" : ($.trim(d.connectivity_in).toUpperCase() === "TRUE") ? "yes" : ($.trim(d.connectivity_in) || "-")) + "/" + ($.trim(d.connectivity_out).toUpperCase() === "FALSE" ? "no" : ($.trim(d.connectivity_out).toUpperCase() === "TRUE") ? "yes" : ($.trim(d.connectivity_out) || "-"));
		var osfamilytext = ($.trim(d.os_family) === "" ? "-" : $.trim(d.os_family));
	
		var disk = $("<div class='template-cell disk'></div>");
                var disktext = $("<span></span>").text($.trim(d.disc_size));
                var diskSize = parseInt($.trim(d.disc_size) || "-1");
                if (isNaN(diskSize) === false) {
                  switch(diskSize) {
                    case -1:
                      disktext = $("<span></span>").append("<i>n/a</i>");
                      break;
                    case 0:
                      disktext = $("<span></span>").append("<i>any</i>");
                      break;
                    default:
                      disktext = $("<span></span>").text(diskSize + " GB");
                      break;
                  }
                }
                $(disk).append("<span></span>").append(disktext);	
		$(memory).append($("<span></span>").text(memorytext));
		$(cpus).append($("<span></span>").text(cpustext));
		$(connectivity).append($("<span></span>").text(connectivitytext));
		$(osfamily).append($("<span></span>").text(osfamilytext));
		if ($.trim(d.noaction) !== "true") {
			$(action).find(".getids").text("get IDs");
		} else {
			$(action).empty();
		}
		$(dom).append(memory).append(disk).append(cpus).append(connectivity).append(osfamily).append(action);
		$(action).find(".getids").unbind("click").bind("click", (function(self, cscript, instance) {
			return function(ev) {
				ev.preventDefault();
				if( appdb.config.features.groupvaprovidertemplates ){
					self.showElementIdsGrouped(this, cscript, instance);
				}else{
					self.showElementIds(this, cscript, instance);
				}
				return false;
			};
		})(this, data.contextscript, data));
	};
	this.renderTemplates = function(dom, data) {
		var d = data.templates || [];
		var ul = $("<ul class='cancollapse'></ul>");
		var liheader = $("<li class='va-template va-template-header'></li>");
		this.renderTemplate(liheader, {main_memory_size: "Memory", disc_size: "Disk", logical_cpus: "Logical", physical_cpus: "/Physical CPUs", connectivity_in: "Connectivity In", connectivity_out: "/Out", os_family: "OS Family", noaction: "true"}, data);
		$(ul).append(liheader);
		if (d.length > 0) {
			$.each(d, (function(self, container) {
				return function(i, e) {
					var l = $("<li class='va-template va-template-data'></li>");
					$(l).data("template", e);
					self.renderTemplate(l, e, data);
					$(container).append(l);
				};
			})(this, ul));
		} else {
			var l = $("<li class='va-template va-template-data empty'></li>");
			this.renderEmptyTemplate(l);
			$(ul).append(l);
		}
		
		$(dom).append(ul);
	};

	this.renderImage = function(dom, d) {
		var li = $("<li class='va-image expandable'></li>");
		var img = $("<div class='image handler'></div>");
		var version = $("<div class='version fieldvalue'><div class='field'></div><div class='value'></div></div>");
		var os = $("<div class='os'></div>");
		var arch = $("<div class='arch'></div>");
		var hypervisors = $("<div class='hypervisors'></div>");
		var permalink = $("<div class='permalink' ><a href='' title='View image details in new window' target='_blank'><span>Image</span></a>:</div>");
		var sep = "<span class='seperator'>/</span>";
		$(version).find(".value").text("ver." + d.vmiinstance_version);
		$(os).text(d.os.val() + " " + d.os.version);
		$(arch).text(d.arch.val());
		$(hypervisors).text(d.hypervisors);
		if (typeof d.vmiinstanceid !== "undefined" && typeof d.identifier !== "undefined" && $.trim(d.vmiinstanceid) !== "" && $.trim(d.identifier) !== "") {
			$(permalink).find("a").attr("href", appdb.config.endpoint.base + "store/vm/image/" + $.trim(d.identifier) + ":" + $.trim(d.vmiinstanceid)).unbind("click").bind("click", function(ev) {
				ev.stopPropagation();
			});
			$(img).append(permalink);
		}
		$(img).append(version).append(os).append(sep).append(arch).append(sep).append(hypervisors);
		$(li).append(img);
		$(li).data("image", d);
		d.templates = d.templates || [];
		this.renderTemplates(li, d);
		$(dom).append(li);
	};

	this.renderSite = function(dom, d) {
		var li = $("<li class='va-site expandable'></li>");
		var site = $("<div class='site handler'></div>");
		var name = $("<div class='name fieldvalue'><a class='field permalink' href='' title='View site details in new window' target='_blank'><span>Site:</span></a><div class='value'></div></div>");
		var country = $("<div class='country'></div>");

		var ul = $("<ul class='cancollapse'></ul>");

		$(name).find(".value").text($.trim(d.name).toUpperCase());
		$(name).find("a").attr("href",appdb.config.endpoint.base + "store/site/" + $.trim(d.name).toUpperCase()).unbind("click").bind("click", function(ev) {
			ev.stopPropagation();
		});
		$(site).append(name);
		$(site).attr("data-id", d.id);
		$(site).data("vos", d.vos);
		if ($.trim(d.in_production) === "true") {
			$(site).addClass("in_production");
		}
		if ($.trim(d.node_monitored) === "true") {
			$(site).addClass("node_monitored");
		}
		if ($.trim(d.beta) === "true") {
			$(site).addClass("beta");
		}
		if (d.country && typeof d.country.val === "function") {
			$(country).text(d.country.val());
			if ($.trim(d.country.id) !== "") {
				$(country).attr("data-id", d.country.id);
			}
			if ($.trim(d.country.isocode) !== "") {
				$(country).attr("data-isocode", d.country.isocode);
				$(country).text("(" + $.trim(d.country.isocode).toUpperCase() + ")");
			}
			$(site).append(country);
		}
		$(li).append(site);
		$(li).append(ul);
		$(li).data("site", d);

		d.images = d.images || [];
		d.images = $.isArray(d.images) ? d.images : [d.images];
		$.each(d.images, (function(self, container) {
			return function(i, e) {
				self.renderImage(container, e);
			};
		})(this, ul));
		$(dom).append(li);
	};
	this.initAccordion = function() {
		$(this.dom).find(".expandable").each((function(self) {
			return function(i, e) {
				$(this).children(".handler:first").unbind("click").bind("click", (function(self) {
					return function(ev) {
						var list = $(this).siblings(".cancollapse");

						if ($(this).parent().hasClass("expanded") === true) {
							$(list).slideUp("fast", function() {
								$(this).find(".expandable > .cancollapse").hide();
								$(this).closest("li").removeClass("expanded");
							});
						} else {
							$(list).closest("li").siblings(".expanded").children(".cancollapse").slideUp("fast", function() {
								$(this).closest("li").removeClass("expanded");
							});
							$(list).find(".cancollapse").hide();
							$(list).find(".expandable").removeClass("expanded");
							$(list).slideDown("fast", function() {
								$(this).closest("li").addClass("expanded");
							});

						}
					};
				})(self));
			};
		})(this));
		$(this.dom).find(".expandable > .cancollapse").hide();
	};
	this.render = function(d) {
		$(this.dom).fadeOut(200, (function(self) {
			return function() {
				self.doRender(d);
			};
		})(this));
	};
	this.doRender = function(d) {
		this.reset();
		d = d || [];
		d = $.isArray(d) ? d : [d];
		$.each(d, (function(self) {
			return function(i, e) {
				self.renderSite(self.dom, e);
			};
		})(this));

		$(this.dom).fadeIn(200);
		this.initAccordion();
	};

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
});
appdb.views.SwapplianceResourceProvidersList = appdb.ExtendClass(appdb.views.VapplianceResourceProvidersList, "appdb.views.SwapplianceResourceProvidersList", function(o) {
	this.options.allowed = ["swappliance","owned"];
	this.transform = function(d){
		var vapps = {};
		$.each(d, function(i,e){
			$.each(e.images, function(ii,img){
				if( !vapps[img.application.id] ){
					vapps[img.application.id] = img.application;
				}
				vapps[img.application.id].siteids = vapps[img.application.id].siteids || {}; 
				if( !vapps[img.application.id].siteids[e.id] ){
					var tmpsite = $.extend(true,{},e);
					delete tmpsite.images;
					vapps[img.application.id].siteids[e.id] = tmpsite;
				}
				var tmpimg = $.extend(true,{},img);
				delete tmpimg.application;
				vapps[img.application.id].siteids[e.id].images = vapps[img.application.id].siteids[e.id].images || [];
				vapps[img.application.id].siteids[e.id].images.push(tmpimg);
			});
		});
		for(var i in vapps){
			if( vapps.hasOwnProperty(i) === false ) continue;
			vapps[i].site = [];
			for(var j in vapps[i].siteids){
				if( vapps[i].siteids.hasOwnProperty(j) === false ) continue;
				vapps[i].site.push(vapps[i].siteids[j]);
			}
			delete vapps[i].siteids;
		}
		var res = [];
		for(var i in vapps){
			if( vapps.hasOwnProperty(i) === false ) continue;
			res.push(vapps[i]);
		}
		return res;
	};
	this.doRender = function(v){
		this.reset();
		$(this.dom).addClass("content");
		var d = this.transform(v);
		d = d || [];
		d = $.isArray(d) ? d : [d];
		$.each(d, (function(self) {
			return function(i, e) {
				self.renderVappliance(self.dom, e);
			};
		})(this));
		$(this.dom).fadeIn(200);
		this.initAccordion();
	};
	this.renderState = function(dom,d){
		$(dom).removeClass("deleted moderated");
		if( !d || !d.id ) return;
		var data = $.extend(true,{} , d);
		var statename = "";
		var state = $("<div class='app-state'></div>");
		if( $.trim(data.deleted) === "true" ){
			statename = "deleted";
			
		}else if( $.trim(data.moderated) === "true" ){
			statename = "moderated";
		}else if( $.trim(d.isexpired) === "true" ){
			statename = "expired";
		}
		if( statename !== "" ){
			$(state).addClass(statename).text(statename);
			$(dom).addClass("state-" + statename);
			var p = this.getClosestParent("appdb.components.SwapplianceResourceProviders");
			if( p !== null ){
				$(state).append(p.getStatePanel(statename));
			}
			$(dom).find(".app-name").prepend(state);
		}
		if( statename === "moderated" || statename === "deleted" ){
			if( !userIsAdminOrManager ){
				$(dom).find(".app-name .permalink").hide();
			}else{
				$(dom).find(".app-name .permalink").show();
			}
		}
	};
	this.renderVappliance = function(dom, d){
		var li = $("<li class='va-vappliance expandable'></li>");
		var img = $("<img alt'' class='app-logo'/>").attr("src", appdb.config.endpoint.base + "apps/getlogo?id=" + d.id);
		var vapp = $("<div class='vappliance handler'></div>");
		var name = $("<div class='app-name fieldvalue'><a class='field permalink' href='' title='View vappliance details in new window' target='_blank'><span>vappliance:</span></a><div class='value'></div></div>");
		var ul = $("<ul class='cancollapse'></ul>");

		$(name).find(".value").text($.trim(d.name));
		$(name).find("a").attr("href",appdb.config.endpoint.base + "store/vappliance/" + $.trim(d.cname)).unbind("click").bind("click", function(ev) {
			ev.stopPropagation();
		});
		$(vapp).append(name);
		$(vapp).attr("data-id", d.id);
		$(name).prepend(img);
		$(li).append(vapp);
		$(li).append(ul);
		$(li).data("vappliance", d);

		d.site = d.site || [];
		d.site = $.isArray(d.site) ? d.site : [d.site];
		$.each(d.site, (function(self, container) {
			return function(i, e) {
				self.renderSite(container, e);
			};
		})(this, ul));
		$(dom).append(li);
		this.renderState(li,d);
	};
});

appdb.views.SiteVMUsageItem = appdb.ExtendClass(appdb.View, "appdb.views.SiteVMUsageItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		siteData: o.siteData || {},
		instanceData: o.instanceData || {},
		allowed: ($.isArray(o.allowed))?o.allowed:["contextscript"],
		itemid: $.trim(o.itemid)
	};
	this.getAvailableContextScripts = function(cntxtscripts){
		contextscripts = cntxtscripts ||[];
		contextscripts = $.isArray(contextscripts)?contextscripts:[contextscripts];
		var res = $.grep(contextscripts, (function(allowed){ 
			return function(e){
				if( !e ) return false;
				if( e.application && e.application.id ){
					if($.inArray("swappliance",allowed) > -1){
						return true;
					}else{
						return false;
					}
				}
				if( $.inArray("contextscript",allowed) > -1 ) return true;
				return false;
			};
		})(this.options.allowed));
		
		if( $.inArray("owned",this.options.allowed) > -1 && this.options.itemid ){
			res = $.grep(res, (function(itemid){
				return function(e){
					return ( e && e.application && $.trim(e.application.id) === itemid );
				};
			})(this.options.itemid));
		}
		
		return res;
	};
	this.renderContextScripts = function(dom,contextscripts){
		var download = $("<a href='#' target='_blank' class='downloadcscript' title='Download contextualization script'>Download</a>");
		$(download).unbind("click").bind("click", function(ev){
			ev.stopPropagation();
			return true;
		});
		if( contextscripts.length === 0  ){
			
		}else if(contextscripts.length === 1){
			$(download).attr("href",contextscripts[0].url).text(contextscripts[0].url);
			$(dom).append(download);
		}else{
			var select = $("<select class='refcontextscripts'></select>");
			$.each(contextscripts, function(i,e){
				var option = $("<option></option>").text($.trim(e.url));
				$(option).attr("value",e.id);
				$(select).append(option);
			});
			$(select).find("options:first").attr("selected","selected");
			$(download).attr("href",contextscripts[0].url);
			$(dom).append(select).append(download);
			
			new dijit.form.Select/*ComboBox*/({
				onChange: (function(dom){
					return function(v){
						console.log(v);
						var urls = $.grep(this.getOptions(), function(e){
							return $.trim(e.value) === v;
						});
						if( urls.length > 0 ){
							$(dom).find("a.downloadcscript").attr("href",urls[0].label);
						}
					};
				})(dom)
			}, $(select)[0]);
			$(dom).addClass("hasmany");
		}
	};
	this.showElementIds = function(el, contextscript) {
		var template = $(el).closest("li.va-template").data("template");
		var image = this.options.data;
		var site = this.options.siteData;

		var template_id = (template) ? $.trim(template.resource_name) : "";
		var occi_id = (image) ? $.trim(this.options.instanceData.id) : "";
		var endpoint_url = (this.options.instanceData && this.options.instanceData.occi_endpoint_url)?this.options.instanceData.occi_endpoint_url:"";

		var endpoint_url_html = $("<div class='fieldvalue endpoint'><div class='field'>Site endpoint:</div><div class='value'></div></div>");
		$(endpoint_url_html).find(".value").text(endpoint_url);
		var template_id_html = $("<div class='fieldvalue templateid'><div class='field'>Template ID:</div><div class='value'></div></div>");
		$(template_id_html).find(".value").text(template_id);
		var occi_id_html = $("<div class='fieldvalue occi_id'><div class='field'>OCCI ID:</div><div class='value'></div></div>");
		$(occi_id_html).find(".value").text(occi_id);

		var usageids = $("<div class='usageids'></div>");
		var fieldvalues = $("<div class='fieldvalues'></div>");
		$(fieldvalues).append(endpoint_url_html).append(template_id_html).append(occi_id_html);
		if (contextscript && $.trim(contextscript.url)) {
			var contextscript_html = $("<div class='fieldvalue contextscript_url'><div class='field'>Contextualization script:</div><div class='value'></div></div>");
			var contextscript_url = $("<a href='#' title='Download contextualization script' target='_blank'></a>");
			$(contextscript_url).attr("href", $.trim(contextscript.url)).text($.trim(contextscript.url));
			$(contextscript_html).find(".value").empty().append(contextscript_url);
			$(fieldvalues).append(contextscript_html);
		}
		$(fieldvalues).find(".fieldvalue .value").each(function(i, e) {
			if ($.trim($(e).text()) === "") {
				$(e).addClass("empty").text("not available yet");
			}
		});
		var imageid = $("<div class='imageid'></div>");
		$(imageid).text("Image: ver." + image.version + " - " + image.os.val() + " " + image.os.version + " / " + image.arch.val() + " / " + image.hypervisor.val());

		$(usageids).append(imageid).append(fieldvalues);

		if (this.parent.renderInlineDialog) {
			var title = image.application.name + " IDs";
			if (this.options.instanceData.voimageid) {
				title += " endorsed by " + this.options.instanceData.vo.name;
			}
			this.parent.renderInlineDialog(true, usageids, "<span>" + title + "</span>", "sitecontents");
		}
	};
	this.renderTemplateItemList  = function(grouphash,data){
		var ul = $("<ul class='groupfieldvalues'></ul>");
		$.each(data.items, function(ii, item){
			var templates = appdb.utils.findGroupTemplatesByHash(grouphash, item.templates.concat(data.template));
			$.each(templates, function(i,templ){
				templ.occi_endpoint_url = templ.occi_endpoint_url || [];
				templ.occi_endpoint_url = $.isArray(templ.occi_endpoint_url)?templ.occi_endpoint_url:[templ.occi_endpoint_url];
				if(  $.inArray($.trim(item.endpointurl), templ.occi_endpoint_url) === -1 ) return;
				var li = $("<li class='fieldvalues'></li>");
				var endpoint_url_html = $("<div class='fieldvalue endpoint'><div class='field'>Site endpoint:</div><div class='value'></div></div>");
				$(endpoint_url_html).find(".value").text(item.endpointurl);
				var template_id_html = $("<div class='fieldvalue templateid'><div class='field'>Template ID:</div><div class='value'></div></div>");
				$(template_id_html).find(".value").text(templ.resource_name);
				var occi_id_html = $("<div class='fieldvalue occi_id'><div class='field'>OCCI ID:</div><div class='value'></div></div>");
				$(occi_id_html).find(".value").text(item.occid);
				$(li).append(endpoint_url_html).append(template_id_html).append(occi_id_html);
				$(ul).append(li);
			});
		});
		$(ul).find(".fieldvalue .value").each(function(i, e) {
			if ($.trim($(e).text()) === "") {
				$(e).addClass("empty").text("not available yet");
			}
		});
		$(ul).find(".fieldvalues:first").addClass("current");
		if($(ul).children("li").length > 1 ){
			$(ul).addClass("haspaging");
		}
		return appdb.utils.pagifyHTMLList(ul);	
	};
	this.showElementIdsGrouped = function(el, contextscript, instance) {
		var template = $(el).closest("li.va-template").data("template");
		var image = this.options.data;
		var listhtmllist = this.renderTemplateItemList(template.group_hash, instance);
		var usageids = $("<div class='usageids'></div>");
		
		var imageid = $("<div class='imageid'></div>");
		$(imageid).text("Image: ver." + image.version + " - " + image.os.val() + " " + image.os.version + " / " + image.arch.val() + " / " + image.hypervisor.val());
		
		var temp = $("<div class='usagetemplate'></div>");
		var td = $(el).closest(".va-template.va-template-data");
		
		$(temp).append("<span>Memory: <b>" + $(td).find(".template-cell.memory > span").text() + "</b></span><span>Disk: <b>" + $(td).find(".template-cell.disk > span").text()  + "</b></span><span>CPUs: <b>" + $(td).find(".template-cell.cpus > span").text() + "</b></span><span> In\Out: <b>" + $(td).find(".template-cell.connectivity > span").text() + "</b></span><span>OS: <b>" + $(td).find(".template-cell.osfamily > span").text() +"</b></span>");
		
		$(usageids).append(imageid).append(temp).append(listhtmllist);
		var contextscripts = this.getAvailableContextScripts(contextscript);
		if ( contextscripts.length > 0 ) {
			var contextscript_html = $("<div class='fieldvalues contextscript_url'><div class='fieldvalue contextscript_url'><div class='field'>Contextualization script:</div><div class='value'></div></div></div>");
			this.renderContextScripts($(contextscript_html).find(".fieldvalue > .value"), contextscripts);
			$(usageids).append(contextscript_html);
		}
		if (this.parent.renderInlineDialog) {
			var title = image.application.name + " IDs";
			if (this.options.instanceData.voimageid) {
				title += " endorsed by " + this.options.instanceData.vo.name;
			}
			this.parent.renderInlineDialog(true, usageids, "<span>" + title + "</span>", "sitecontents");
		}
		
		$(usageids).find(".contextscript_url .value.hasmany").closest(".fieldvalue").addClass("hasmany");
		if( $(usageids).find(".pagifier").length > 0 ){
			$(usageids).addClass("hasmany");
		}
	};
	this.renderEmptyTemplate = function(dom) {
		$(dom).append("<div class='template-empty'><span class='message'>No templates provided</span></div><div class='template-cell action'><div class='getids' title='View related IDs'>get IDs</div></div>");
		$(dom).find(".getids").unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.showElementIds(this);
				return false;
			};
		})(this));
	};
	this.renderTemplate = function(dom, d, data,instance) {
		d = d || {};
		var memory = $("<div class='template-cell memory'></div>");
		var cpus = $("<div class='template-cell cpus'></div>");
		var connectivity = $("<div class='template-cell connectivity'></div>");
		var osfamily = $("<div class='template-cell osfamily'></div>");
		var action = $("<div class='template-cell action'><div class='getids' title='View related IDs'></div><div class='getcontextscript hidden' title='View contextualization script'></div></div>");
		var memorytext = ($.trim(d.main_memory_size) === "" ? "-" : $.trim(d.main_memory_size));
		var cpustext = ($.trim(d.logical_cpus) === "" ? "-" : $.trim(d.logical_cpus)) + "/" + ($.trim(d.physical_cpus) === "" ? "-" : $.trim(d.physical_cpus));
		var connectivitytext = ($.trim(d.connectivity_in).toUpperCase() === "FALSE" ? "no" : ($.trim(d.connectivity_in).toUpperCase() === "TRUE") ? "yes" : ($.trim(d.connectivity_in) || "-")) + "/" + ($.trim(d.connectivity_out).toUpperCase() === "FALSE" ? "no" : ($.trim(d.connectivity_out).toUpperCase() === "TRUE") ? "yes" : ($.trim(d.connectivity_out) || "-"));
		var osfamilytext = ($.trim(d.os_family) === "" ? "-" : $.trim(d.os_family));
		
		var disk = $("<div class='template-cell disk'></div>");
		var disktext = $("<span></span>").text($.trim(d.disc_size));
		var diskSize = parseInt($.trim(d.disc_size) || "-1");
		if (isNaN(diskSize) === false) {
		  switch(diskSize) {
		    case -1:
		      disktext = $("<span></span>").append("<i>n/a</i>");
		      break;
		    case 0:
		      disktext = $("<span></span>").append("<i>any</i>");
		      break;
		    default:
		      disktext = $("<span></span>").text(diskSize + " GB");
		      break;
		  }
		}
		$(disk).append("<span></span>").append(disktext);
		$(memory).append($("<span></span>").text(memorytext));
		$(cpus).append($("<span></span>").text(cpustext));
		$(connectivity).append($("<span></span>").text(connectivitytext));
		$(osfamily).append($("<span></span>").text(osfamilytext));
		if ($.trim(d.noaction) !== "true") {
			$(action).find(".getids").text("get IDs");
		} else {
			$(action).empty();
		}
		$(dom).append(memory).append(disk).append(cpus).append(connectivity).append(osfamily).append(action);
		$(action).find(".getids").unbind("click").bind("click", (function(self, cscript, instance) {
			return function(ev) {
				ev.preventDefault();
				if( appdb.config.features.groupvaprovidertemplates ){
					self.showElementIdsGrouped(this, cscript, instance);
				}else{
					self.showElementIds(this, cscript, instance);
				}
				
				return false;
			};
		})(this, data.contextscript,instance));
	};
	this.renderTemplates = function(dom, data,instance) {
		var d = this.options.instanceData.template || [];
		var ul = $("<ul class='cancollapse'></ul>");
		var liheader = $("<li class='va-template va-template-header'></li>");
		this.renderTemplate(liheader, {main_memory_size: "Memory", disc_size: "Disk", logical_cpus: "Logical", physical_cpus: "/Physical CPUs", connectivity_in: "Connectivity In", connectivity_out: "/Out", os_family: "OS Family", noaction: "true"}, data);
		$(ul).append(liheader);
		if (d.length > 0) {
			$.each(d, (function(self, container) {
				return function(i, e) {
					var l = $("<li class='va-template va-template-data'></li>");
					$(l).data("template", e);
					self.renderTemplate(l, e, data,instance);
					$(container).append(l);
				};
			})(this, ul));
		} else {
			var l = $("<li class='va-template va-template-data empty'></li>");
			this.renderEmptyTemplate(l);
			$(ul).append(l);
		}
		$(dom).append(ul);
	};
	this.transformData = function(d) {
		d.template = d.template || [];
		d.template = $.isArray(d.template) ? d.template : [d.template];
		return d;

	};
	this.render = function(d, instance) {
		d = d || {};
		this.options.data = d || this.options.data;
		this.options.instanceData = instance || this.options.instanceData || {};
		var data = this.transformData($.extend(true, {}, this.options.data));
		$(this.dom).children(".header").remove();
		$(this.dom).append($("<div class='header'>Select a template and get the rOCCI ids</div>"));
		this.renderTemplates(this.dom, data, instance);
	};

	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
});

appdb.views.SiteVMUsageList = appdb.ExtendClass(appdb.View, "appdb.views.SiteVMUsageList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		siteData: o.siteData || {},
		usageitem: null
	};
	this.renderInlineDialog = function(enabled, html, title, classes) {
		if (this.parent.renderInlineDialog) {
			this.parent.renderInlineDialog(enabled, html, title, classes);
		}
	};
	this.renderUsageItem = function(data) {
		if (this.options.usageitem) {
			this.options.usageitem.reset();
			this.options.usageitem = null;
		}
		this.options.usageitem = new appdb.views.SiteVMUsageItem({
			container: $(this.dom).find(".usageitem"),
			parent: this,
			data: this.options.data,
			siteData: this.options.siteData
		});
		this.options.usageitem.render(this.options.data, data);
	};

	this.addItem = function(d) {
		var li = $("<li></li>");
		var a = $("<a href='#' title='Select to view usage'><span class='name'></span></a>");

		$(li).append(a);
		$(a).find(".name").text((d.vo) ? d.vo.name : "none");
		$(a).unbind("click").bind("click", (function(self, data) {
			return function(ev) {
				ev.preventDefault();
				$(this).closest("ul").find(".current").removeClass("current");
				$(this).addClass("current");
				self.renderUsageItem(data);
				return false;
			};
		})(this, d));

		return li;
	};
	this.orderEndorsements = function() {
		this.options.data = this.options.data || {};
		this.options.data.instances = this.options.data.instances || [];
		this.options.data.instances.sort(function(a, b) {
			if (!a.voimageid) {
				return -1;
			}
			if (!b.voimageid) {
				return 1;
			}
			var an = a.vo.name;
			var bn = b.vo.name;
			if (an > bn)
				return -1;
			if (an < bn)
				return 1;
			return 0;
		});
	};
	this.render = function(d) {
		d = d || {};
		this.options.data = d || this.options.data;
		$(this.dom).find("ul.usagelist").empty();
		this.orderEndorsements();
		$.each(this.options.data.instances, (function(self, dom) {
			return function(i, e) {
				var li = self.addItem(e);
				if (li) {
					$(dom).append(li);
				}
			};
		})(this, $(this.dom).find("ul.usagelist")));

		$(this.dom).find("ul.usagelist > li:first > a").trigger("click");
	};
	this._initContainer = function() {
		$(this.dom).empty();
		$(this.dom).append("<div class='title'>Endorsed by VO:</div><ul class='usagelist'></ul><div class='usageitem va-image'></div>");
		$(this.dom).addClass("resourceproviders");
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
});
appdb.views.SiteVMImageListFilter = appdb.ExtendClass(appdb.View, "appdb.views.SiteVMImageListFilter", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		vmimagelist: o.vmimagelist || null,
		data: o.data || [],
		siteData: o.siteData || {},
		filters: [
			{name: "Endorsed by VOs",
				getValueObjects: function(d) {
					var vos = {};
					$.each(d, function(i, e) {
						e.instances = e.instances || [];
						e.instances = $.isArray(e.instances) ? e.instances : [e.instances];
						var occis = $.grep(e.instances, function(ee) {
							return (ee.voimageid) ? true : false;
						});
						$.each(occis, function(ii, ee) {
							vos[ee.vo.id] = $.extend(true, {}, ee.vo);
						});
						var noneoccis = $.grep(e.instances, function(ee) {
							return (ee.voimageid) ? false : true;
						});
						if( noneoccis.length > 0 ) {
							vos["<none>"] = {name:"<none>"};
						}
					});
					var res = [];
					for (var i in vos) {
						if (vos.hasOwnProperty(i) === false)
							continue;
						res.push({id: vos[i].name, val: vos[i].name});
					}
					return res;
				},
				filterOut: function(id, d) {
					return $.grep(d, function(e) {
						e.instances = e.instances || [];
						e.instances = $.isArray(e.instances) ? e.instances : [e.instances];
						var occis = $.grep(e.instances, function(ee) {
							return ((ee.voimageid && ee.vo && ee.vo.name === id) || ($.trim(ee.voimageid)=== "" && id === '<none>') )?true:false;
						});
						return (occis.length > 0) ? true : false;
					});
				}
			}, {
				name: "OSes",
				getValueObjects: function(d) {
					var oses = {};
					$.each(d, function(i, e) {
						if (e.os && e.os.id) {
							oses[e.os.id] = $.extend(true, {}, e.os);
						}
					});
					var res = [];
					for (var i in oses) {
						if (oses.hasOwnProperty(i) === false)
							continue;
						res.push({id: oses[i].val(), val: oses[i].val()});
					}
					return res;
				},
				filterOut: function(id, d) {
					return $.grep(d, function(e) {
						return (e.os && e.os.val && e.os.val() === id) ? true : false;
					});
				}
			}, {
				name: "Hypervisors",
				getValueObjects: function(d) {
					var hyps = {};
					$.each(d, function(i, e) {
						if (e.hypervisor && e.hypervisor.id) {
							hyps[e.hypervisor.id] = $.extend(true, {}, e.hypervisor);
						}
						;
					});
					var res = [];
					for (var i in hyps) {
						if (hyps.hasOwnProperty(i) === false)
							continue;
						res.push({id: hyps[i].val(), val: hyps[i].val()});
					}
					return res;
				},
				filterOut: function(id, d) {
					return $.grep(d, function(e) {
						return (e.hypervisor && e.hypervisor.val() === id) ? true : false;
					});
				}
			},
			{
				name: "Virtual Appliances",
				getValueObjects: function(d) {
					var vapps = {};
					$.each(d, function(i, e) {
						if (e.application && e.application.id) {
							vapps[e.application.id] = $.extend(true, {}, e.application);
						}
					});
					var res = [];
					for (var i in vapps) {
						if (vapps.hasOwnProperty(i) === false)
							continue;
						res.push({id: vapps[i].name, val: vapps[i].name});
					}
					return res;
				},
				filterOut: function(id, d) {
					return $.grep(d, function(e) {
						return (e.application && e.application.name && e.application.name === id) ? true : false;
					});
				}
			}
		]
	};
	this.getFilteredData = function(filters) {
		filters = filters || this.options.filters;
		filters = $.isArray(filters)?filters:[filters];
		var res = this.options.data.slice(0);
		$.each(filters, (function(self) {
			return function(i, e) {
				if (e.userValue) {
					res = e.filterOut(e.userValue, res);
				}
			};
		})(this));
		return res;
	};
	this.filter = function(filter) {
		var fd = this.getFilteredData();
		if (this.options.vmimagelist) {
			this.options.vmimagelist.render(fd);
			this.renderCount(fd);
		}
		$.each(this.options.filters, (function(self) {
			return function(i, e) {
				if (filter && e.name === filter.name) return;
				self.updateFilter(e, fd, e.userValue);
			};
		})(this));
		this.validateFilters(filter);		
	};
	this.getFilterValues = function(filter){
		var flts = $.grep(this.options.filters, function(e) {
			if (filter && e.name === filter.name) return false;
			return (e.userValue)?true:false;
		});
		return this.getFilteredData(flts);
	};
	this.clearFilters = function(flt) {
		if( !flt ){
			this.render();
			this.filter();
		}else{
			delete flt.userValue;
			this.filter(flt);
		}
	};
	this.updateFilter = function(filter, data) {
		var fd = filter.getValueObjects(this.getFilterValues(filter));
		fd.sort(function(a,b){
			var an = a.val;
			var bn = b.val;
			
			if( an < bn) return -1;
			if( an > bn) return 1;
			
			return 0;
		});
		var listdom = $("<select></select>");
		$.each(fd, function(i, e) {
			var opt = $("<option value=''></option>");
			$(opt).text(e.val).val(e.id);
			$(listdom).append(opt);
		});
		filter.itemCount = fd.length;
		//clear previous dojo comboboxes
		if (filter.combobox) {
			filter.combobox.destroyRecursive(false);
			filter.combobox = null;
		}

		$(filter.dom).find(".value").append(listdom);
		filter.currentValue = filter.userValue;
		
		
		$(filter.dom).removeClass("single").removeClass("empty");
		
		if ($(listdom).find("option").length > 1) {
			$(listdom).prepend("<options value=' '></option>");
		} else if ($(listdom).find("option").length === 0) {
			$(filter.dom).addClass("empty");
		} else {
			$(filter.dom).addClass("single");
			$(listdom).children("option:first").attr("selected", "selected");
			filter.currentValue = fd[0].val;
		}

		filter.combobox = new dijit.form.FilteringSelect({
			placeHolder: $.trim(filter.name).toLowerCase(),
			style: "min-width: 80px;",
			required: false,
			value: filter.currentValue,
			onChange: (function(self, flt, data) {
				return function(v) {
					if( data.length <= 1) return;
					if ($.trim(v) === "") {
						delete filter.userValue;
						$(flt.dom).removeClass("hasselection");
					} else {
						filter.userValue = v;
						$(flt.dom).addClass("hasselection");
					}
					self.filter(flt);
				};
			})(this, filter, fd)
		}, $(filter.dom).find("select")[0]);

	};
	this.addFilter = function(filter) {
		if (filter.dom) {
			$(filter.dom).remove();
			delete filter.dom;
		}
		if (typeof filter.userValue !== "undefined") {
			delete filter.userValue;
		}
		
		filter.dom = $("<li><div class='filter'><div class='field'></div><div class='value'></div><a href='#' class='action clear' title='Clear current filter'>x</a></div></li>");
		$(filter.dom).find(".field").text(filter.name + ":");
		$(filter.dom).find(".action.clear").unbind("click").bind("click", (function(self, flt) {
			return function(ev) {
				ev.preventDefault();
				delete flt.userValue;
				self.clearFilters(flt);
				if( flt.combobox ){
					flt.combobox.focus();
				}
				return false;
			};
		})(this, filter));
		this.updateFilter(filter, this.options.data);
	};
	this.validateFilters = function(filter){
		$.each(this.options.filters, function(i, e){
			if( e.combobox.get("value") !== e.userValue ){
				e.combobox.set("value", e.userValue );
				e.combobox.set("displayedValue", (e.itemCount === 1)?e.currentValue || e.userValue:'' );
			}
			e.combobox.set("disabled", (e.itemCount === 1) );
			if( e.itemCount === 0 ){
				$(e.dom).hide();
			}else{
				$(e.dom).show();
			}
		});
	};
	this.renderFilters = function() {
		$(this.dom).find(".filters > ul").empty();
		$.each(this.options.filters, (function(self) {
			return function(i, e) {
				self.addFilter(e);
				$(self.dom).find(".filters > ul").append(e.dom);
			};
		})(this));
	};
	this.renderCount = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		$(this.dom).find(".results").removeClass("single");
		$(this.dom).find(".results > .count").text(d.length);
		if( d.length === 1 ){
			$(this.dom).find(".results").addClass("single");
		}
	};
	this.render = function(d) {
		this.renderFilters();
		
		$(this.dom).find(".action.clearall").unbind("click").bind("click", (function(self) {
			return function(ev) {
				ev.preventDefault();
				self.clearFilters();
				return false;
			};
		})(this));
		
		this.filter();
	};
	this._initContainer = function() {
		$(this.dom).empty();
		$(this.dom).append("<div class='title'>Filter By:</div>");
		$(this.dom).append("<div class='filters'><ul></ul></div>");
		$(this.dom).append("<a href='#' class='clearall action' title='clear all filters'>clear all</a>");
		$(this.dom).append("<div class='results'><span class='count'></span><span>image<span class='plural'>s</span></span></a>");
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
});
appdb.views.SiteVMImageListItem = appdb.ExtendClass(appdb.View, "appdb.views.SiteVMImageListItem", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		siteData: o.siteData || {},
		usage: null
	};
	this.renderInlineDialog = function(enabled, html, title, classes) {
		enabled = (typeof enabled === "boolean") ? enabled : false;
		title = $.trim(title);
		classes = $.trim(classes);
		$(this.dom).children(".inlinedialog").remove();
		if (enabled) {
			var dialog = $("<div class='inlinedialog " + classes + "'><div class='shader'/><div class='dialog'><div class='header'></div><div class='message'></div><div class='footer'><div class='action close btn btn-primary btn-compact'>close</div></div></div></div>");
			$(dialog).find(".header").append(title);
			if (title === "") {
				$(dialog).find(".header").addClass("hidden");
			}
			$(dialog).find(".message").append(html);
			$(dialog).unbind("click").bind("click", function(ev) {
				ev.preventDefault();
				return false;
			});
			$(dialog).find(".action.close, .shader").unbind("click").bind("click", (function(self) {
				return function(ev) {
					ev.preventDefault();
					self.renderInlineDialog(false);
					return false;
				};
			})(this));
			$(this.dom).append(dialog);
		}
	};
	this.renderUsage = function(d) {
		if (this.options.usage) {
			this.options.usage.reset();
			this.options.usage = null;
		}
		$(this.dom).find(".usage").remove();
		$(this.dom).append("<div class='usage'></div>");
		this.options.usage = new appdb.views.SiteVMUsageList({
			container: $(this.dom).find(".usage"),
			parent: this,
			data: d,
			siteData: this.options.siteData
		});
		this.options.usage.render(d);
	};
	this.renderActions = function(d) {
		var actions = $("<div class='actions'></div>");
		var usage = $("<a class='action usage richbutton' href='#'><img src='/images/occi.png' alt=''><div class='content'><span>Use it</span></div></a>");
		var details = $("<a class='action details' href='#' target='_blank' title='View image details'><span>view image details</span></a>");
		var download = $("<a class='action download richbutton' title='Download image' href='#' target='_blank'>" +
				"<img class='hideonprivate' src='/images/download.png' alt=''><img class='showonprivate' src='/images/logout3.png' alt=''>" +
				"<div class='content'><span class='hideonprivate'>Download</span><span class='showonprivate'>Private</span>" +
				"</div></a>");

		$(usage).unbind("click").bind("click", (function(self, data) {
			return function(ev) {
				if ($(self.dom).hasClass("expanded")) {
					$(self.dom).removeClass("expanded");
				} else {
					$(self.parent.dom).find(".expanded").removeClass("expanded");
					$(self.dom).addClass("expanded");
				}
			};
		})(this, d));
		$(details).attr("href", d.mpuri).unbind("click");
		
		$(download).attr("href", ($.trim(d["private"]) === 'true') ? d.mpuri : d.url);
		$(actions).append(download).append(usage);
		$(this.dom).find(".actions").remove();
		$(this.dom).append(actions);
		$(this.dom).find(".app-image-info").after(details);
	};
	this.renderDeletedModerated = function(){
		$(this.dom).removeClass("deleted moderated");
		if( !this.options.data || !this.options.data.application ) return;
		var data = this.options.data.application;
		var statename = "";
		var state = $("<div class='app-state'></div>");
		if( $.trim(data.deleted) === "true" ){
			statename = "deleted";
			
		}else if( $.trim(data.moderated) === "true" ){
			statename = "moderated";
		}else if( $.trim(this.options.data.isexpired) === "true" ){
			statename = "expired";
		}
		if( statename !== "" ){
			$(state).addClass(statename).text(statename);
			$(this.dom).addClass("state-" + statename);
			var p = this.getClosestParent("appdb.views.SiteVMImageList");
			if( p !== null ){
				$(state).append(p.getStatePanel(statename));
			}
			$(this.dom).find(".app-name").prepend(state);
		}
		
	};
	this.render = function(d) {
		d = d || this.options.data || {};
		this.options.data = d;
		var applogo = $("<img src='" + appdb.config.endpoint.base + "apps/getlogo?id=" + d.application.id + "' class='app-logo' />");
		var appname = $("<div class='app-name'></div>");
		var applink = $("<a href='#' target='_blank' title='View item' ></a>");
		var imageinfo = $("<div class='app-image-info'></div>");
		var instances = $("<div class='app-image-instances'>" + d.instances.length + "</div>");
		$(applink).attr("href", appdb.config.endpoint.base + "store/vappliance/" + d.application.cname).unbind("click").bind("click", (function(data) {
			return function(ev) {
				ev.preventDefault();
				appdb.views.Main.showVirtualAppliance({id: data.application.id, cname: data.application.cname});
				return false;
			};
		})(d));
		$(applink).text(d.application.name);
		$(appname).append(applink);
		$(imageinfo).text(d.version + " " + d.os.val() + " " + d.os.version + " / " + d.arch.val() + " / " + d.hypervisor.val());
		$(this.dom).append(applogo).append(appname).append(imageinfo).append(instances);
		if ($.trim(d["private"]) === 'true') {
			$(this.dom).addClass("isprivate");
		} else {
			$(this.dom).removeClass("isprivate");
		}
		this.renderDeletedModerated();
		this.renderUsage(d);
		this.renderActions(d);
		$(this.dom).attr("title", d.application.name + " VM image");
	};
	this._initContainer = function() {

	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
});
appdb.views.SiteVMImageList = appdb.ExtendClass(appdb.View, "appdb.views.SiteVMImageList", function(o) {
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		items: [],
		data: o.data || [],
		siteData: o.siteData || {}
	};
	this.reset = function() {
		this.unsubscribeAll();
		$(this.dom).empty();
		$.each(this.options.items, function(i, e) {
			e.reset();
			e = null;
		});
		this.options.items = [];
		this._initContainer();
	};
	this.addItem = function(d) {
		var li = $("<li></li>");
		var image = new appdb.views.SiteVMImageListItem({
			container: li,
			parent: this,
			data: d,
			siteData: this.options.siteData
		});

		this.options.items.push(image);
		image.render();

		return li;
	};
	this.render = function(d) {
		d = d || [];
		d = $.isArray(d) ? d : [d];
		this.reset();
		d.sort(function(a, b){
			//order by vappliance name
			var an = $.trim( (a.application)?a.application.name:'' ).toLowerCase();
			var bn = $.trim( (b.application)?b.application.name:'' ).toLowerCase();
			
			if( an < bn ) return -1;
			if( an > bn ) return 1;
			
			//else order by os
			var an = $.trim( (a.os && typeof a.os.val === 'function')?a.os.val():'' ).toLowerCase();
			var bn = $.trim( (b.os && typeof b.os.val === 'function')?b.os.val():'' ).toLowerCase();
			
			if( an < bn ) return -1;
			if( an > bn ) return 1;
			
			//else order by os version
			var an = $.trim( (a.os && a.os.version)?a.os.version:'' ).toLowerCase();
			var bn = $.trim( (b.os && b.os.version)?b.os.version:'' ).toLowerCase();
			
			if( an < bn ) return -1;
			if( an > bn ) return 1;			
			
			//else order by arch
			var an = $.trim( (a.arch && typeof a.arch.val === 'function')?a.arch.val():'' ).toLowerCase();
			var bn = $.trim( (b.arch && typeof b.arch.val === 'function')?b.arch.val():'' ).toLowerCase();
			
			if( an < bn ) return -1;
			if( an > bn ) return 1;
			
			//else order by hypervisor
			var an = $.trim( (a.hypervisor && typeof a.hypervisor.val === 'function')?a.hypervisor.val():'' ).toLowerCase();
			var bn = $.trim( (b.hypervisor && typeof b.hypervisor.val === 'function')?b.hypervisor.val():'' ).toLowerCase();
			
			if( an < bn ) return -1;
			if( an > bn ) return 1;
			
			return 0;
		});
		$.each(d, (function(self) {
			return function(i, e) {
				var li = self.addItem(e);
				if (li !== null) {
					$(self.dom).append(li);
				}
			};
		})(this));
		$(this.dom).attr("title", this.options.siteData.name + " VM image list");
	};
	this._initContainer = function() {
		$(this.dom).append($(this.options.statepanels).clone(true));
		$(this.dom).unbind("click").bind("click", (function(self) {
			return function(ev) {
				self.options.items = self.options.items || [];
				self.options.items = $.isArray(self.options.items) ? self.options.items : [self.options.items];
				$.each(self.options.items, function(i, e) {
					e.renderInlineDialog(false);
				});
			};
		})(this));
	};
	this.getStatePanel = function(state){
		state = $.trim(state);
		var panel = $(this.options.statepanels).find(".app-state-panel." + state);
		if( $(panel).length > 0 ){
			return $(panel).clone(true);
		}
		return null;
	};
	this._init = function() {
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.statepanels = $(this.dom).find(".app-state-panels").clone(true);
		$(this.dom).find(".app-state-panels").remove();
		this._initContainer();
	};
	this._init();

});


appdb.views.AutoCompleteList = appdb.ExtendClass(appdb.View, "appdb.views.AutoCompleteList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		model: o.model || null,
		prefilter: o.prefilter || "",
		postfilter: o.postfilter || "",
		maxcount: o.maxcount || 150,
		itemRenderer: o.itemrenderer,
		excludeids: o.excludeids || [],
		dom: {
			input: o.inputel,
			list: o.listel,
			emptylist: o.emptylistel
		},
		throttle: o.throttle || 500,
		minlength: o.minlength || 3,
		currentsearch: "",
		preselectedindex: -1,
		selecteddata: null,
		messages: {}
	};
	
	this.renderItem = function(dom,data){
		//override
	};
	
	this.renderSelectedItem = function(data){
		//override
	};
	
	this.getSelectedData = function(){
		return this.options.selecteddata;
	};
	
	this.getFilter = function(){
		return $(this.options.dom.input).val();
	};
	
	this.getModelOptions = function(){
		return {
			search: this.getFilter() + this.options.prefilter,
			limit: this.options.maxcount
		};
	};
	
	this.focus = function(enable){
		enable = (typeof enable === "boolean")?enable:true;
		if( enable ){
			$(this.dom).addClass("focused");
		}else{
			$(this.dom).removeClass("focused");
		}
	};
	this.sortListItems = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		d.sort(function(a,b){
			a.property = a.property || [];
			a.property = $.isArray(a.property)?a.property:[a.property];
			b.property = b.property || [];
			b.property = $.isArray(b.property)?b.property:[b.property];
			var aa = "", bb = "";
			$.each(a.property, function(i,e){
				aa += $.trim((e.val)?e.val():"").toLowerCase() + " ";
			});
			$.each(b.property, function(i,e){
				bb += $.trim((e.val)?e.val():"").toLowerCase() + " ";
			});
			aa = $.trim(aa);
			bb = $.trim(bb);
			if( aa > bb ) return 1;
			if( aa < bb ) return -1;
			return 0;
		});
	};
	this.renderList = function(d){
		d = d || [];
		d = $.isArray(d)?d:[d];
		$(this.dom).addClass("isempty");
		$(this.options.dom.list).empty();
		if( d.length > 0 ){
			this.sortListItems(d);
			$.each(d, (function(self){
				return function(i, e){
					var li = $("<li></li>");
					var item = self.renderItem(li, e);
					if( item ){
						$(li).append(item);
						$(li).data("item",e);
						$(self.options.dom.list).append(li);
					}
				};
			})(this));
			$(this.options.dom.list).children("li").unbind("click").bind("click", (function(self){
				return function(ev){
					ev.preventDefault();
					ev.stopPropagation();
					self.selectItem($(this).data("item"));
					return false;
				};
			})(this));
			$(this.dom).removeClass("isempty");
			$(this.options.dom.list).parent().removeClass("hidden");
			
		} else {
			$(this.dom).addClass("isempty");
		}
	};
	
	this.preselectIndex = function(index){
		index = parseInt(index);
		index = (index >=0 )?index:0;
		$(this.options.dom.list).find(".preselected").removeClass("preselected");
		var pre = $(this.options.dom.list).children().get(index);
		if( $(pre).length > 0){
			$(pre).addClass("preselected");
			this.options.preselectedindex = index;
		}else{
			this.options.preselectedindex = -1;
		}
	};
	
	this.preselectNextIndex = function(){
		var index = this.options.preselectedindex;
		if( index <= -1 ){
			index = 0;
		}else if( index < $(this.options.dom.list).children("li").length -1){
			index += 1;
		}
		this.preselectIndex(index);
	};
	
	this.preselectPreviousIndex = function(){
		var index = this.options.preselectedindex;
		if( index > 0 ){
			index -= 1;
		}else if( index >= $(this.options.dom.list).children("li").length -1){
			index = $(this.options.dom.list).children("li").length -1;
		}
		this.preselectIndex(index);
	};
	
	this.selectCurrentIndex = function(){
		var item = $(this.options.dom.list).children("li").get(this.options.preselectedindex);
		if( $(item).length > 0 ){
			this.selectItem($(item).data("item"));
		}
	};
	
	this.clearSelection = function(){
		var changed = false;
		if( this.options.selecteddata !== null ){
			changed = true;
		}
		this.options.selecteddata = null;
		$(this.dom).removeClass("selected");
		if( changed ){
			this.publish({event: "change", value: null });
		}
	};
	this.setSelectedItemData = function(data){
		this.options.selecteddata = data;
	};
	this.selectItem = function(data){
		this.setSelectedItemData(data);
		this.renderSelectedItem();
		$(this.dom).addClass("selected");
		$(this.options.dom.input).trigger("blur");
		this.publish({event: "change", value: data});
		this.options.currentsearch = this.getFilter();
		$(this.dom).children(".results").addClass("hidden");
		setTimeout((function(dom){
			return function(){
				$(dom).removeClass("hidden");
			};
		})(this.dom),5);
	};
	
	this.renderLoading = function(enable){
		enable = (typeof enable === "boolean")?enable:true;
		$(this.dom).removeClass("loading");
		if( enable ){
			$(this.dom).addClass("loading");
		}
	};
	
	this.doLoad = function(){
		this.load();
		this.doLoad = appdb.utils.throttle((function(self){return function(){ self.load(); };})(this),this.options.throttle);
	};
	
	this.search = function(){
		this.doload();
	};
	
	this.cancel = function(){
		if( this.options.xhr ){
			this.options.xhr.abort();
		}
		this.renderLoading(false);
	};
	
	this.load = function(){
		this.cancel();
		this._model.get(this.getModelOptions());
		this.options.xhr = this._model.getXhr();
	};
	
	this.transformData = function(data){
		if( appdb.utils.harvester[this.options.datatype]){
			return appdb.utils.harvester[this.options.datatype].fromRelation(data);
		}
		if( typeof data === "object" || !$.isEmptyObject(data)){
			return $.extend(data || {});
		}
		return null;
	};
	
	this._initModel = function(){
		this._model = new this.options.model();
		this._model.subscribe({event: "beforeselect", callback: function(v){
			this.renderLoading(true);	
		}, caller: this}).subscribe({event: "select", callback: function(v){
			this.renderLoading(false);
			this.renderList(v.record || []);
		}, caller: this}).subscribe({event: "error", callback: function(v){
			this.renderLoading(false);
		}, caller: this});
	};
	
	this._initEvents = function(){
		$(this.options.dom.input).unbind("keyup").bind("keyup", (function(self){
			return function(ev){
				ev.preventDefault();
				var keycode = (ev.keyCode ? ev.keyCode : ev.which);
				if( $.inArray(keycode,[38,40,13,27]) === -1 ){
					if( self.options.currentsearch === self.getFilter() ){
						return false;
					}
					self.cancel();
					if( self.getFilter().length < self.options.minlength ){
						self.clearSelection();
						self.renderList([]);
						$(self.dom).removeClass("isempty").addClass("nofilter");
					}else if( self.options.currentsearch !== self.getFilter() ){
						self.clearSelection();
						self.options.currentsearch = self.getFilter();
						$(self.dom).children(".results").addClass("hidden");
						self.doLoad();
						$(self.dom).removeClass("isempty").removeClass("nofilter");
					}
				}else{
					switch(keycode){
						case 38: //up
							self.preselectPreviousIndex();
							break;
						case 40://down
							self.preselectNextIndex();
							break;
						case 13: //enter
							self.selectCurrentIndex();
							$(self.options.dom.input).trigger("blur");
							ev.stopPropagation();
							break;
						case 27://esc
							self.options.preselectedindex = -1;
							$(self.options.dom.input).trigger("blur");
							ev.stopPropagation();
							break;
					}
				}
				return false;
			};
		})(this));
		$(this.options.dom.input).unbind("keypress").bind("keypress", function(ev){
			var keycode = (ev.keyCode ? ev.keyCode : ev.which);
			if( keycode === 13 ){
				ev.stopPropagation();
			}
			return true;
		});
		$(this.options.dom.input).unbind("change").bind("change", (function(self){
			return function(ev){
				ev.preventDefault();
				return false;
			};
		})(this));
		$(this.options.dom.input).unbind("focus").bind("focus", (function(self){
			return function(ev){
				self.focus(true);
			};
		})(this));
		$(this.options.dom.input).unbind("blur").bind("blur", (function(self){
			return function(ev){
				self.focus(false);
			};
		})(this));
	};
	
	this._initContainer = function(){
		if( !this.options.dom.input ){
			if( $(this.dom).find("input.autocomplete").length === 0 ){
				this.options.dom.input = $("<input type='text' value=''/>");
				if( $(this.dom).children(".filter").length === 0 ){
					$(this.dom).append("<div class='filter'></div>");
				}
				$(this.dom).children(".filter").prepend(this.options.dom.input);
			}else{
				this.options.dom.input = $(this.dom).find("input.autocomplete");
			}
		}
		$(this.options.dom.input).addClass('autocomplete');
		
		if( !this.options.dom.list ){
			if( $(this.dom).find("ul.results").length === 0 ){
				this.options.dom.list = $("<ul></ul>");
				if( $(this.dom).children(".results.menu").length === 0 ){
					$(this.dom).append("<div class='results menu'></div>");
				}
				$(this.dom).children(".results.menu").append(this.options.dom.list);
			}else{
				this.options.dom.list = $(this.dom).find("ul.results");
			}
		}
		$(this.options.dom.list).addClass("results");
		
		if( !this.options.dom.emptylist ){
			if( $(this.dom).find(".emptylist").length === 0 ){
				this.options.dom.emptylist = $('<div class="emptylist menu"><div class="emptycontent"><div class="content"><img src="/images/exclam16.png" alt="" /><span>No items found.</span></div></div></div>');
				if( this.options.messages && $.trim(this.options.messages.emptylist) !== "" ){
					$(this.options.emptylist).find(".content > span").text(this.options.messages.emptylist);
				}
				$(this.dom).append(this.options.dom.emptylist);
			}else{
				this.options.emptylist = $(this.dom).children(".emptylist");
			}
		}
		$(this.options.dom.emptylist).addClass("menu");
		
		if( !this.options.dom.emptyfilter ){
			if( $(this.dom).find(".emptyfilter").length === 0 ){
				this.options.dom.emptyfilter = $('<div class="emptyfilter menu"><div class="emptycontent"><div class="content"><img src="/images/exclam16.png" alt="" /><span>You need to type at least ' + this.options.minlength + ' characters to searh for items.</span></div></div></div>');
				if( this.options.messages && $.trim(this.options.messages.emptyfilter) !== "" ){
					$(this.options.emptyfilter).find(".content > span").text(this.options.messages.emptyfilter);
				}
				$(this.dom).append(this.options.dom.emptyfilter);
			}else{
				this.options.dom.emptyfilter = $(this.dom).children(".emptyfilter");
			}
		}
		$(this.options.dom.emptyfilter).addClass("menu");
		
		
		if( !this.options.dom.loader ){
			if( $(this.dom).find(".loader").length === 0 ){
				this.options.dom.loader = $('<div class="loader menu"><img class="header" src="/images/ajax-loader-trans-orange.gif" alt=""/><div class="loadingcontent emptycontent"><div class="content"><img src="/images/ajax-loader-trans-orange.gif"><span>...Searching</span></div></div></div>');
				if( this.options.messages && $.trim(this.options.messages.loader) !== "" ){
					$(this.options.loader).find(".content > span").text(this.options.messages.loader);
				}
				$(this.dom).append(this.options.dom.loader);
			}else{
				this.options.dom.loader = $(this.dom).children(".loader");
			}
		}
		$(this.options.dom.loader).addClass("menu");
		
		$(this.dom).addClass("autocompletelist").addClass("nofilter");
		$(this.dom).find(".menu.results").prepend('<div class="header">powered by <a href="http://www.openaire.eu/" target="_blank" tabindex="-1">OpenAire</a></div>');
		
	};
	this._postInit = function(){
		//override
	};
	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.excludeids = this.options.excludeids || [];
		this.options.excludeids = $.isArray(this.options.excludeids)?this.options.excludeids:[this.options.excludeids];
		this.options.minlength = (parseInt(this.options.minlength) > 0 )?this.options.minlength:3;
		this.options.maxcount = (parseInt(this.options.maxcount) > 0 )?this.options.maxcount:20;
		if( typeof this.options.itemRenderer === 'function' ){
			this.renderItem = this.options.itemRenderer;
		}
		this.options.selecteddata = this.transformData(o.selecteddata);
		this._initContainer();
		this._initModel();
		if( this.options.selecteddata ){
			this.selectItem(this.options.selecteddata);
		}
		this._initEvents();
		this._postInit();
	};
});

appdb.views.AutoCompleteListOrganization = appdb.ExtendClass(appdb.views.AutoCompleteList, "appdb.views.AutoCompleteListOrganization", function(o){
	this.options = $.extend(true,this.options,{
		model: appdb.model.Harvester.Organizations,
		datatype: "organization",
		messages: {
			emptylist: "No organizations found.",
			emptyfilter: "You need to type at least " + (this.options.minlength || 3) + " characters to searh for organizations.",
			loader: "...Searching for organizations"
		}
	});
	this.sortListItems = function(d){
		appdb.utils.harvester.organization.sortList(d);
	};
	this.renderItem = function(dom, data){
		data = data || {};
		data.property = data.property || [];
		var el = $("<div class='organization'><img class='property country_iso' src='' border='0' /><div class='property legalname'></div></div>");
		var sname = "";
		var name = "";
		$.each(data.property, function(i,e){
			if( !e.val ) return;
			switch( e.name ){
				case "country_iso":
					$(el).find(".country_iso").attr("src","/images/flags/" + $.trim(e.val()).toLowerCase() + ".png");
					break;
				case "legashortlname":
					sname = e.val();
					break;
				case "legalname":
					name += e.val();
					break;
			}
		});
		if(sname!== ""){
			var sspan = $("<span class='shortname'></span>");
			$(sspan).text(sname);
			$(el).find(".property.legalname").append(sspan).append("<span>|</span>");
		}
		$(el).find(".property.legalname").append($("<span></span>").text(name));
		
		return el;
	};
	this.renderSelectedItem = function(){
		var data = $.extend(true,{},this.options.selecteddata);
		data.property = data.property || [];
		var stitle = "";
		var title = "<none>";
		$.each(data.property, function(i, e){ 
			if( e.val ){
				if( e.name === 'legalname' ){
					title = e.val();
				} else if (e.name === 'legashortlname' && e.val && $.trim(e.val()) !== ""){
					stitle = e.val() + " | ";
				}
			}
		});
		$(this.options.dom.input).data("item",data).val(stitle + title);
	};
	
});
appdb.views.AutoCompleteListProject = appdb.ExtendClass(appdb.views.AutoCompleteList, "appdb.views.AutoCompleteListProject", function(o){
	this.options = $.extend(true,this.options,{
		model: appdb.model.Harvester.Projects,
		datatype: "project",
		messages: {
			emptylist: "No projects found.",
			emptyfilter: "You need to type at least " + (this.options.minlength || 3) + " characters to searh for projects.",
			loader: "...Searching for projects"
		}
	});
	this.sortListItems = function(d){
		appdb.utils.harvester.project.sortList(d);
	};
	this.renderItem = function(dom, data){
		data = data || {};
		data.property = data.property || [];
		var el = $("<div class='project'><span class='property title'></span></div>");
		var sname = "";
		var name = "";
		$.each(data.property, function(i,e){
			if( !e.val ) return;
			switch( e.name ){
				case "acronym":
					sname = e.val();
					break;
				case "title":
					name += e.val();
					break;
			}
		});
		if(sname!== ""){
			var sspan = $("<span class='acronym'></span>");
			$(sspan).text(sname);
			$(el).find(".property.title").append(sspan).append("<span>|</span>");
		}
		$(el).find(".property.title").append($("<span></span>").text(name));
		
		return el;
	};
	this.renderSelectedItem = function(){
		var data = $.extend(true,{},this.options.selecteddata);
		data.property = data.property || [];
		var stitle = "";
		var title = "<none>";
		$.each(data.property, function(i, e){ 
			if( e.val ){
				if( e.name === 'title' ){
					title = e.val();
				} else if (e.name === 'acronym' && e.val && $.trim(e.val()) !== ""){
					stitle = e.val() + " | ";
				}
			}
		});
		$(this.options.dom.input).data("item",data).val(stitle + title);
	};
	
});
appdb.views.AutoCompleteListSoftware = appdb.ExtendClass(appdb.views.AutoCompleteList, "appdb.views.AutoCompleteListSoftware", function(o){
	this.options = $.extend(true,this.options,{
		model: appdb.model.Harvester.Software,
		prefilter: "+*&application.metatype:0",
		postfilter: "-=application.id:"+appdb.pages.application.currentId(),
		datatype: "software",
		messages: {
			emptylist: "No software found.",
			emptyfilter: "You need to type at least " + (this.options.minlength || 3) + " characters to searh for software.",
			loader: "...Searching for software"
		}
	});
	this.getModelOptions = function(){
		return {
			flt: this.options.prefilter + " "  + this.getFilter() +  " " + this.options.postfilter,
			pagelength: this.options.maxcount,
			orderby: "rank",
			orderbyOp: "DESC"
		};
	};
	this.sortListItems = function(d){
		appdb.utils.harvester.software.sortList(d);
	};
	this.getLogoSrc = function(id){
		if( $.trim(id) === "" ){
			if( this.options.datatype === 'software' ){
				return appdb.config.images.applications;
			}else if( this.options.datatype === 'vappliance' ){
				return appdb.config.images["virtual appliances"];
			}
		}else{
			return "/apps/getlogo?id=" + id;
		}
	};
	this.renderItem = function(dom, data){
		data = data || {};
		var el = $("<div class='organization'><img class='property logo' src='' border='0' /><div class='property description'></div></div>");
		var sname = $.trim(data.name);
		var name = $.trim(data.description);
		var sspan = $("<span class='name'></span>");
		$(sspan).text(sname);
		$(el).find(".logo").attr("src",this.getLogoSrc(data.id));
		$(sspan).text(sname);
		$(el).find(".property.description").append(sspan).append("<span>|</span>");
		$(el).find(".property.description").append($("<span></span>").text(name));
		
		return el;
	};
	this.renderSelectedItem = function(){
		var data = $.extend(true,{},this.options.selecteddata);
		if( data.cname ){
			data = appdb.utils.harvester[this.options.datatype].fromRelation({entity: { "type": this.options.datatype, guid:data.guid, application:data}});
		}
		data.property = data.property || [];
		var stitle = "";
		var title = "";
		$.each(data.property, function(i, e){ 
			if( e.val ){
				if( e.name === 'name' ){
					stitle = e.val();
				} else if (e.name === 'description' && e.val && $.trim(e.val()) !== ""){
					title = e.val();
				}
			}
		});
		
		$(this.options.dom.input).data("item",data).val(stitle + ((title!=="")?" | " + title:"") );
	};
	this._initModel = function(){
		this._model = new this.options.model();
		this._model.subscribe({event: "beforeselect", callback: function(v){
			this.renderLoading(true);	
		}, caller: this}).subscribe({event: "select", callback: function(v){
			this.renderLoading(false);
			this.renderList(v.application || []);
		}, caller: this}).subscribe({event: "error", callback: function(v){
			this.renderLoading(false);
		}, caller: this});
	};
	this._postInit = function(){
		$(this.dom).find(".menu.results > .header").text("");
	};
});
appdb.views.AutoCompleteListVappliance = appdb.ExtendClass(appdb.views.AutoCompleteListSoftware, "appdb.views.AutoCompleteListVappliance", function(o){
	this.options = $.extend(true,this.options,{
		model: appdb.model.Harvester.Vappliance,
		datatype: "vappliance",
		prefilter: "+*&application.metatype:1",
		postfilter: "-=application.id:"+appdb.pages.application.currentId(),
		messages: {
			emptylist: "No virtual appliance found.",
			emptyfilter: "You need to type at least " + (this.options.minlength || 3) + " characters to searh for virtual appliance.",
			loader: "...Searching for virtual appliances"
		}
	});
});
appdb.views.AutoCompleteListSwappliance = appdb.ExtendClass(appdb.views.AutoCompleteListSoftware, "appdb.views.AutoCompleteListSwappliance", function(o){
	this.options = $.extend(true,this.options,{
		model: appdb.model.Harvester.Vappliance,
		datatype: "swappliance",
		prefilter: "+*&application.metatype:2",
		postfilter: "-=application.id:"+appdb.pages.application.currentId(),
		messages: {
			emptylist: "No software appliance found.",
			emptyfilter: "You need to type at least " + (this.options.minlength || 3) + " characters to searh for software appliance.",
			loader: "...Searching for software appliances"
		}
	});
});
appdb.views.RelationListItem = appdb.ExtendClass(appdb.View, "appdb.views.RelationListItem", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		targettype: o.targettype,
		subjecttype: o.subjecttype,
		verbname: o.verbname,
		reverse: (typeof o.reverse === "boolean")?o.reverse:false,
		data: o.data || {},
		canedit: (typeof o.canedit === "boolean")?o.canedit:false,
		editmode: (typeof o.editmode === "boolean")?o.editmode:false,
		currentData: $.extend(true, (o.data || {}), {}),
		editors: {
			targets: null,
			verbs: null
		},
		isvalid: false
	};
	
	this.reset = function(){
		if( this.options.editors.verbs ){
			this.options.editors.verbs.destroyRecursive(false);
			this.options.editors.verbs = null;
		}
		if( this.options.editors.targets ){
			this.options.editors.targets.reset();
			this.options.editors.targets= null;
		}
		$(this.dom).empty();
	};
	this.isReversed = function(){
		return this.options.reverse;
	};
	this.validate = function(){
		var valid = true;
		var editortype = (this.isReversed())?this.options.editors.subjects:this.options.editors.targets;
		
		var sd = editortype.getSelectedData();
		
		var vid = this.options.editors.verbs.get('value'); //this is the relation type id and NOT the verb id
		if( !sd ){
			valid = false;
		}
		if( valid ){
			$(this.dom).removeClass("invalid");
		}else{
			$(this.dom).addClass("invalid");
		}
		this.options.isvalid = valid;
		
		if( valid ){
			this.options.data = {
				id: vid
			};
			if( this.isReversed() ){
				delete this.options.data.targetguid;
				this.options.data.subjectguid = ( (sd.guid)?sd.guid:(sd.id || null) );
			}else{
				delete this.options.data.subjectguid;
				this.options.data.targetguid = ( (sd.guid)?sd.guid:(sd.id || null) );
			}
		}else{
			this.options.data = null;
		}
		return valid;
	};
	this.isValid = function(){
		return this.options.isvalid;
	};
	this.canEdit = function(){
		return this.options.canedit;
	};
	
	this.isEditMode = function(){
		return this.options.editmode;
	};
	
	this.edit = function(enable){
		enable = (typeof enable === "boolean")?enable:false;
		if( enable ){
			this.options.editmode = true;
			this.render();
		}
	};
	
	this.getData = function(){
		return this.options.data;
	};
	
	this.render = function(d){
		d = this.options.data || {};
		this.options.data = d;
		this.reset();
		this._initContainer();
		$(this.dom).removeClass("reversed").find(".relationtarget,.relationsubject").removeClass("currentrelation");
		
		if( this.isReversed() ){
			$(this.dom).addClass("reversed");
			$(this.dom).find(".relationsubject").addClass("currentrelation");
		}else{
			$(this.dom).find(".relationtarget").addClass("currentrelation");
		}
		
		if( this.isEditMode() && this.canEdit() ){
			if( this.isReversed() ){
				this.renderTarget();
				this.editSubject();
			}else{
				this.renderSubject();
				this.editTarget();
			}
			this.editVerb();
			this.renderActions();
			this.validate();
		}else{
			this.renderSubject();
			this.renderVerb();
			this.renderTarget();
		}
		
	};
	this.getTargetType = function(){
		return $.trim(this.options.targettype).toLowerCase();
	};
	this.getSubjectType = function(){
		return $.trim(this.options.subjecttype).toLowerCase();
	};
	this.getVerbOfRelation = function(rel){
		return (this.options.reverse)?rel.reverseverb:rel.directverb;
	};
	this.renderCurrentEntity = function(){
		//to be overriden
	};
	this.renderVerb = function(){
		var verbs = appdb.utils.RelationsRegistry.getVerbsBySubjectTargetPairs(this.options.subjecttype,this.options.targettype);
		var selverb = $.grep(appdb.utils.RelationsRegistry.getVerbsBySubjectTargetPairs(this.options.subjecttype,this.options.targettype), (function(v){
			return function(e){
				return $.trim(e.verb).toLowerCase() === v;
			};
		})($.trim(this.options.data.verbname).toLowerCase()));
		if( selverb.length === 0 ){
			selverb = this.options.data.verbname;
		}else{
			selverb = selverb[0];
			selverb = selverb.directverb;
		}
		$(this.dom).find(".relationverb").text(selverb);
	};
	this.renderTarget = function(){
		if( !this.isReversed()){
			this.renderCurrentEntity();
		}else{
			$(this.dom).find(".relationtarget").empty().append(this.options.data.target);
		}
	};
	this.renderSubject = function(){
		if( this.isReversed()){
			this.renderCurrentEntity();
		}else{
			$(this.dom).find(".relationsubject").text(this.getSubjectType());
		}
	};
	
	this.editSubject = function(){
		var s = this.getSubjectType();
		s = s[0].toUpperCase() + s.slice(1);
		var editor = appdb.views["AutoCompleteList" + s];
		if( !editor ){
			return;
		}
		
		this.options.editors.subjects = new editor({
			container: $(this.dom).find(".relationsubject"),
			parent: this,
			selecteddata: this.options.data
		});
		this.options.editors.subjects._init();
		this.options.editors.subjects.subscribe({event: "change", callback: function(v){
				this.validate();
				this.publish({event: "change", value: this});
		}, caller: this});
	};
	this.editVerb = function(){
		var verbs = appdb.utils.RelationsRegistry.getVerbsBySubjectTargetPairs(this.options.subjecttype,this.options.targettype);
		var selverb = $.trim((this.options.data || {}).verbname) || this.options.verbname;
		var html = "<select>";
		$.each(verbs, (function(self){
			return function(i,e){
				html += "<option "+ ((selverb && e.verb===selverb)?"selected='selected'":"" )+" value='"+e.id+"'>"+self.getVerbOfRelation(e)+"</option>";
			};
		})(this));
		html += "</select>";
		$(this.dom).find(".relationverb").append(html);
		this.options.editors.verbs = new dijit.form.Select({
			onChange: (function(self){
				return function(v){
					self.validate();
					self.publish({event: "change", value: self});
				};
			})(this)
		},$(this.dom).find(".relationverb > select")[0]);
		if( verbs.length === 1 ){
			this.options.editors.verbs.set('disabled', true);
		}
	};
	this.editTarget = function(){
		var s = this.getTargetType();
		s = s[0].toUpperCase() + s.slice(1);
		var editor = appdb.views["AutoCompleteList" + s];
		if( !editor ){
			return;
		}
		
		this.options.editors.targets = new editor({
			container: $(this.dom).find(".relationtarget"),
			parent: this,
			selecteddata: this.options.data
		});
		this.options.editors.targets._init();
		this.options.editors.targets.subscribe({event: "change", callback: function(v){
				this.validate();
				this.publish({event: "change", value: this});
		}, caller: this});
	};
	this.renderActions = function(){
		$(this.dom).find(".actions").remove();
		if( this.isEditMode() && this.canEdit() ){
			$(this.dom).find(".relationtype").append("<div class='actions'></div>");
			var actions = $(this.dom).find(".actions");
			var remove = $("<button type='button' class='btn btn-danger btn-compact action delete' title='remove relation' tabindex='-1'>X</button>");
			$(remove).unbind("click").bind("click",(function(self){
				return function(ev){
					ev.preventDefault();
					self.publish({event:"remove", value: self});
					return false;
				};
			})(this)).unbind("keyup").bind("keyup", function(ev){
				ev.peventDefault();
				ev.stopPropagation();
				return false;
			});
			$(actions).append(remove);
		}
	};
	this.renderEdit = function(){
		this.options.currentData = $.extend(true, (this.options.data || {}), {});
		this._initContainer();
		this.editVerb();
		this.editTarget();
		this._initEvents();
	};
	this.showPopupDialog = function(enable){
		enable = (typeof enable === 'boolean')?enable:true;
		if( enable && this.parent && this.parent.options.items && this.parent.options.items.length > 0 ){
			$.each(this.parent.options.items, (function(self){
				return function(i,e){
					if( e !== self && e.options.popupdialog && e.showPopupDialog ){
						e.showPopupDialog(false);
					}
				};
			})(this));
		}
		if( this.options.popupdialog ){
			dijit.popup.close(this.options.popupdialog);
			this.options.popupdialog.destroyRecursive(false);
			this.options.popupdialog = null;
		}
		if( enable ){
			this.options.popupdialog = new dijit.TooltipDialog({
				content: this.getEntityDetailsHtml(),
				onMouseLeave: (function(self){
					return function(){
						dijit.popup.close(self.options.popupdialog);
					};
				})(this)
			});
			dijit.popup.open({
				popup: this.options.popupdialog,
				around: $(this.dom).find(".currentrelation")[0]
			});
		}
	};
	
	this.getRelationDescription = function(verb, sname){
		var rel = appdb.utils.RelationsRegistry.getByTriplet(this.options.subjecttype,verb,this.options.targettype) || null;
		if( rel ){
			rel = rel[0];
			sname = ($.trim(sname) || ("this " + ( (this.options.reverse)?rel.subjecttype:rel.targettype ) ) );
			var ename = ( appdb.pages.index.currentContentName() || ( "this " + ( (this.options.reverse)?rel.targettype:rel.subjecttype ) ) );
			if( this.options.reverse ){
				var tense = "is";
				if( rel.reverseverb.substr(0,3).toLowerCase() === "has" ){
					tense = "";
				}
				return ename + " " + tense + " " + rel.reverseverb + " " + sname;
			}
			return ename + " " + rel.directverb + " " + sname;
		}
		return null;
	};
	this._initContainer = function(){
		$(this.dom).empty();
		if( this.isReversed() ){
			$(this.dom).append("<div class='relationtype'><div class='relationtarget' /><div class='relationverb' /><div class='relationsubject' /></div>");
		}else{
			$(this.dom).append("<div class='relationtype'><div class='relationsubject' /><div class='relationverb' /><div class='relationtarget' /></div>");
		}
		
	};
	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	
	this._init();
});
appdb.views.RelationListItemSoftware = appdb.ExtendClass(appdb.views.RelationListItem, "appdb.views.RelationListItemSoftware", function(o){
	this.renderCurrentEntity = function(){
		var dom = $(this.dom).find(".currentrelation");
		$(dom).empty().append(this.getEntityDetailsHtml());
	};
	this.getEntityDetailsHtml = function(){
		var seltype = this.options.targettype;
		if(this.isReversed()){
			seltype = this.options.subjecttype;
		}
		var d = ((this.options.data || {}).entity || {}).application || {};
		var verbname = $.trim((this.options.data || {}).verbname);
		var dom = $("<div class='relationentitylistitem "+seltype+"'></div>");
		var fv1 = $("<div class='fieldvalue name '><div class='field'>Name:</div><div class='value'></div></div>");
		var fv2 = $("<div class='fieldvalue description '><div class='field'>Description:</div><div class='value'></div></div>");
		var ws = $("<div class='website'><a href='#' target='_blank' ></a></div>");
		$(fv1).find(".value").text($.trim(d.name) || "" );
		$(fv2).find(".value").text($.trim(d.val()) || "" );
		$(dom).append(fv1).append(fv2);
		if( $.trim(d.cname) !== ""){
			var u = $.trim(d.cname);
			u = appdb.config.endpoint.base + "store/" + seltype +"/" + u;
			$(ws).children("a").attr('href', u).attr('title', "View details of "+seltype+" " + d.name).text("details");
			$(dom).append(ws);
		}
		$(dom).prepend("<img src='' alt='' />");
		$(dom).children("img").attr("src", "/apps/getlogo?id=" + $.trim(d.id));
		
		if( verbname !== "" ){
			var v = ($.isArray(this.options.data.groupverbs))?this.options.data.groupverbs:[verbname];
			v.sort();
			var vdom = $("<span class='relations'></span>");
			$.each(v, (function(self,sname){ return function(i,e){
				$(vdom).append( $("<span class='relation'></span>").attr("tooltip", self.getRelationDescription(e,sname)).text(e) );
			};})(this,d.name));
			
			$(dom).append(vdom);
		}
		return $(dom)[0];
	};
});
appdb.views.RelationListItemOrganization = appdb.ExtendClass(appdb.views.RelationListItem, "appdb.views.RelationListItemOrganization", function(o){
	this.renderCurrentEntity = function(){
		var d = ((this.options.data || {}).entity || {}).organization || {};
		var dom = $(this.dom).find(".currentrelation");
		var sname = $("<span class='shortname'></span>");
		var lname = $("<span class='name'></span>");
		var legalname = $.trim(d["name"]);
		var legalshortname = $.trim(d.shortname);
		
		$(lname).text(legalname);
		
		if( legalshortname === "" ){
			legalshortname = "" + legalname.substr(0,16);
			if( legalname.length > 16 ){
				legalshortname += "...";
			}
		}else if( legalshortname.length > 16 ){
			legalshortname = "" + legalshortname.substr(0,16) + "...";
		}
		
		$(sname).text(legalshortname);
		$(dom).append(sname).append(lname);
		
		$(dom).unbind("click").bind("click", (function(self){
			return function(ev){
				setTimeout(function(){
					self.showPopupDialog(true);
				},10);
			};
		})(this));
	};
	
	this.getEntityDetailsHtml = function(){
		var d = ((this.options.data || {}).entity || {}).organization || {};
		var verbname = $.trim((this.options.data || {}).verbname);
		var dom = $("<div class='relationentitylistitem organization'></div>");
		var fv1 = $("<div class='fieldvalue legalshortname'><div class='field'>Name:</div><div class='value'></div></div>");
		var fv2 = $("<div class='fieldvalue legalname'><div class='field'>Title:</div><div class='value'></div></div>");
		var ws = $("<div class='website'><a href='#' target='_blank' ></a></div>");
		$(fv1).find(".value").text($.trim(d.shortname) || "" );
		$(fv2).find(".value").text($.trim(d.name) || "" );
		$(dom).append(fv1).append(fv2);
		if( d.url && d.url.type === 'website' && d.url.val && $.trim(d.url.val()) !== ""){
			var u = $.trim(d.url.val());
			if (!/^(f|ht)tps?:\/\//i.test(u)) {
				u = "http://" + u;
			}
			$(ws).children("a").attr('href', u).attr('title', "Visit organization's web site").text("website");
		}else{
			$(ws).empty();
		}
		$(dom).append(ws);
		if( d.country && d.country.val && $.trim(d.country.isocode) !== "" ){
			$(dom).prepend("<img src='' alt='' />");
			$(dom).children("img").attr("src", "/images/flags/" + $.trim(d.country.isocode).toLowerCase() + ".png");
		}
		if( verbname !== "" ){
			var v = ($.isArray(this.options.data.groupverbs))?this.options.data.groupverbs:[verbname];
			v.sort();
			var vdom = $("<span class='relations'></span>");
			$.each(v, (function(self,sname){ return function(i,e){
				$(vdom).append( $("<span class='relation'></span>").attr("tooltip", self.getRelationDescription(e,sname)).text(e) );
			};})(this,d.shortname));
			
			$(dom).append(vdom);
		}
		if( $.trim(d.sourceid)==='2' ){
			$(dom).append('<div class="datasource"><span>powered by </span><a href="http://www.openaire.eu/" target="_blank" tabindex="-1"><img src="/images/openaire.ico" alt="" /><span>OpenAire</span></a></div>');
		}
		return $(dom)[0];
	};
});
appdb.views.RelationListItemProject = appdb.ExtendClass(appdb.views.RelationListItem, "appdb.views.RelationListItemProject", function(o){
	this.renderCurrentEntity = function(){
		var d = {};
		if( this.options.reverse ){
			d = ((this.options.data || {}).entity || {}).project || {};
		}else{
			d = ((this.options.data || {}).entity || {}).project || {};
		}
		var dom = $(this.dom).find(".currentrelation");
		var sname = $("<span class='acronym shortname'></span>");
		var lname = $("<span class='title name'></span>");
		var title = $.trim(d["title"]);
		var acronym = $.trim(d.acronym);
		
		$(lname).text(title);
		
		if( acronym === "" ){
			acronym = "" + title.substr(0,15);
			if( title.length > 15 ){
				acronym += "...";
			}
		}
		$(sname).text(acronym);
		$(dom).append(sname).append(lname);
		
		$(dom).unbind("click").bind("click", (function(self){
			return function(ev){
				setTimeout(function(){
					self.showPopupDialog(true);
				},10);
			};
		})(this));
	};
	
	this.getEntityDetailsHtml = function(){
		var d = {};
		if( this.options.reverse ){
			d = ((this.options.data || {}).entity || {}).project || {};
		}else{
			d = ((this.options.data || {}).entity || {}).project || {};
		}
		var verbname = $.trim((this.options.data || {}).verbname);
		var dom = $("<div class='relationentitylistitem project'></div>");
		var fv1 = $("<div class='fieldvalue acronym'><div class='field'>Acronym:</div><div class='value'></div></div>");
		var fv2 = $("<div class='fieldvalue title'><div class='field'>Title:</div><div class='value'></div></div>");
		var fv3 = $("<div class='fieldvalue duration'><div class='field'>Duration:</div><div class='value'></div></div>");
		var ws = $("<div class='website'><a href='#' target='_blank' ></a></div>");
		$(fv1).find(".value").text($.trim(d.acronym) || "" );
		$(fv2).find(".value").text($.trim(d.title) || "" );
		$(dom).append(fv1).append(fv2);
		
		if( $.trim(d.startdate) !== ""  && $.trim(d.enddate)  ){
			$(fv3).find(".value").text( $.trim(d.startdate) + " - "  + $.trim(d.enddate) );
		}else{
			$(fv3).empty();
		}
		$(dom).append(fv3);
		if( d.url && d.url.type === 'website' && d.url.val && $.trim(d.url.val()) !== ""){
			var u = $.trim(d.url.val());
			if (!/^(f|ht)tps?:\/\//i.test(u)) {
				u = "http://" + u;
			}
			$(ws).children("a").attr('href', u).attr('title', "Visit projects's web site").text("website");
			$(dom).append(ws);
		}
		
		if( verbname !== "" ){
			var v = ($.isArray(this.options.data.groupverbs))?this.options.data.groupverbs:[verbname];
			v.sort();
			var vdom = $("<span class='relations'></span>");
			$.each(v, (function(self,sname){ return function(i,e){
				$(vdom).append( $("<span class='relation'></span>").attr("tooltip", self.getRelationDescription(e,sname)).text(e) );
			};})(this,d.acronym));
			
			$(dom).append(vdom);
		}
		if( $.trim(d.sourceid)==='2' ){
			$(dom).append('<div class="datasource"><span>powered by </span><a href="http://www.openaire.eu/" target="_blank" tabindex="-1"><img src="/images/openaire.ico" alt="" /><span>OpenAire</span></a></div>');
		}
		return $(dom)[0];
	};
});
appdb.views.RelationListItemSoftwareProject = appdb.ExtendClass(appdb.views.RelationListItemProject, "appdb.views.RelationListItemSoftwareProject", function(o){
	this.renderCurrentEntity = function(){
		var dom = $(this.dom).find(".currentrelation");
		$(dom).empty().append(this.getEntityDetailsHtml());
	};
});
appdb.views.RelationListItemVApplianceProject = appdb.ExtendClass(appdb.views.RelationListItemSoftwareProject, "appdb.views.RelationListItemVApplianceProject", function(o){});
appdb.views.RelationListItemSWApplianceProject = appdb.ExtendClass(appdb.views.RelationListItemSoftwareProject, "appdb.views.RelationListItemSWApplianceProject", function(o){});
appdb.views.RelationListItemSoftwareOrganization = appdb.ExtendClass(appdb.views.RelationListItemOrganization, "appdb.views.RelationListItemSoftwareOrganization", function(o){
	this.renderCurrentEntity = function(){
		var dom = $(this.dom).find(".currentrelation");
		$(dom).empty().append(this.getEntityDetailsHtml());
	};
});
appdb.views.RelationListItemVApplianceOrganization = appdb.ExtendClass(appdb.views.RelationListItemSoftwareOrganization, "appdb.views.RelationListItemVApplianceOrganization", function(o){});
appdb.views.RelationListItemSWApplianceOrganization = appdb.ExtendClass(appdb.views.RelationListItemSoftwareOrganization, "appdb.views.RelationListItemSWApplianceOrganization", function(o){});
appdb.views.RelationListItemSoftwareSoftware = appdb.ExtendClass(appdb.views.RelationListItemSoftware, "appdb.views.RelationListItemSoftwareSoftware", function(o){});
appdb.views.RelationListItemSoftwareVappliance = appdb.ExtendClass(appdb.views.RelationListItemSoftware, "appdb.views.RelationListItemSoftwareVappliance", function(o){});
appdb.views.RelationListItemVapplianceSoftware = appdb.ExtendClass(appdb.views.RelationListItemSoftware, "appdb.views.RelationListItemVapplianceSoftware", function(o){});
appdb.views.RelationListItemVapplianceVappliance = appdb.ExtendClass(appdb.views.RelationListItemSoftware, "appdb.views.RelationListItemVapplianceVAppliance", function(o){});
appdb.views.RelationListItemSwapplianceSoftware = appdb.ExtendClass(appdb.views.RelationListItemSoftware, "appdb.views.RelationListItemSwapplianceSoftware", function(o){});
appdb.views.RelationListItemSwapplianceVappliance = appdb.ExtendClass(appdb.views.RelationListItemSoftware, "appdb.views.RelationListItemSwapplianceVappliance", function(o){});

appdb.views.RelationList = appdb.ExtendClass(appdb.View, "appdb.views.RelationList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: [],
		items: [],
		dom: {
			list: o.listel,
			actionadd: o.actionaddel
		},
		targettype: o.targettype,
		subjecttype: o.subjecttype,
		verbname: o.verbname,
		reverse: (typeof o.reverse === "boolean")?o.reverse:false,
		canedit: (typeof o.canedit === "boolean")?o.canedit:false,
		editmode: (typeof o.editmode === "boolean")?o.editmode:false,
		defaultdata: o.defaultdata || null,
		isvalid: false,
		itemtype: o.itemtype || null,
		messages: {
			emptylist: "No relations found"
		}
	};
	this.reset = function(){
		this.options.items = this.options.items || [];
		this.options.items = $.isArray(this.options.items)?this.options.items:[this.options.items];
		$.each(this.options.items, function(i,e){
			e.reset();
			e = null;
		});
		this.options.items = [];
		$(this.options.dom.list).empty();
		this._initContainer();
	};
	
	this.canEdit = function(){
		return this.options.canedit;
	};
	this.isEditMode = function(){
		return this.options.editmode;
	};
	this.edit = function(enable){
		enable = (typeof enable === "boolean")?enable:false;
		if( enable ){
			this.options.editmode = true;
			this.render();
		}
	};
	this.validate = function(){
		this.options.items = this.options.items || [];
		this.options.items = $.isArray(this.options.items)?this.options.items:[this.options.items];
		var invalids = $.grep(this.options.items, function(e){
			return !e.isValid();
		});
		this.options.isvalid = ( invalids.length === 0 );
		
		if( this.options.isvalid ){
			$(this.dom).removeClass("invalid");
			$(this.options.dom.actionadd).removeClass("disabled").removeAttr("disabled");
		}else{
			$(this.dom).addClass("invalid");
			$(this.options.dom.actionadd).addClass("disabled").attr("disabled","disabled");
		}
	};
	this.isValid = function(){
		return this.options.isvalid;
	};
	this.getData = function(){
		return this.options.data;
	};
	this.hasChanges = function() {
		var d = this.options.data || [];
		if (d.length !== this.options.items.length)
			return true;

		var res = $.grep(this.options.items, function(e) {
			return e.hasChanges();
		});
		return ((res.length > 0) ? true : false);
	};
	this.getItemData = function(item){
		var d = item.getData();
		return d;
	};
	this.setupForm = function(frm) {
		if ($(frm).length === 0) {
			return false;
		}
		var i, len = this.options.items.length, item, inp;
		$(frm).remove("input[name^='relation']");
		for (i = 0; i < len; i += 1) {
			item = this.options.items[i];
			if( !item.getData() ) continue;
			inp = document.createElement("input");
			$(inp).attr("name", "relation" + i).attr("type", "hidden").attr("value", JSON.stringify(this.getItemData(item)));
			$(frm).append(inp);
		}
		return true;
	};
	this.hasChanges = function(){
		if( this.options.items.length !== this.options.data.length ){
			return true;
		}
		var current = {};
		var hasnew = false;
		$.each(this.options.items, function(i,e){
			var s = e.getData();
			if( s !== null ){
				//if targetguid is a number then it corresponds to a record id
				//which means that there is a new item in the list
				if( s.targetguid === ( "" + (s.targetguid <<0) ) ){
					hasnew = true;
				}
				current[s.id + ":" +s.targetguid] = s;
			}			
		});
		
		if( hasnew === true ){
			return true;
		}
		
		var newitems = $.grep(this.options.data, function(e){
			return ( e && e.target && e.target.guid && !current[e.relationtypeid + ":" + e.target.guid] );
		});
		
		return ( newitems.length > 0 )?true:false;
	};
	this.extractType = function(typename, data){
		typename = $.trim(typename).toLowerCase() || "subject";
		if( data && data.entity && $.trim(data.entity["type"]) !== "" ){
			if( $.trim(data.reversed).toLowerCase() === "true" && typename === "subject"){
				return $.trim(data.entity["type"]).toLowerCase();
			}else if ($.trim(data.reversed) === "" && typename === "subject"){
				return appdb.pages.index.currentContent();
			}else if( $.trim(data.reversed) === "" && typename === "target"){
				return $.trim(data.entity["type"]).toLowerCase();
			}else if($.trim(data.reversed).toLowerCase() === "true" && typename === "target"){
				return appdb.pages.index.currentContent();
			}
		}
	};
	
	this.addItem = function(data){
		var li = $("<li></li>");
		var item = new this.options.itemtype({
			container: li,
			parent: this,
			data: data,
			editmode: this.options.editmode,
			canedit: this.options.canedit,
			targettype: this.options.targettype || this.extractType("target",data) ,
			subjecttype: this.options.subjecttype || this.extractType("subject",data) ,
			verbname: this.options.verbname,
			reverse: this.options.reverse
		});
		item.subscribe({event: "remove", callback: function(v){
				this.removeItem(v);
		}, caller: this});
		item.subscribe({event: "change", callback: function(v){
				this.validate();
		}, caller: this});
		item.render();
		this.options.items.push(item);
		$(li).data("index", this.options.items.length - 1);
		this.validate();
		return li;
	};
	
	this.addNewItem = function(){
		var newitem = this.addItem(this.options.defaultdata || {});
		if( newitem ){
			$(this.options.dom.list).append(newitem);
		}
	};
	
	this.removeItem = function(item){
		var index = parseInt($(item.dom).data("index"));
		if( index >= 0 && this.options.items.length > index ){
			this.options.items[index].reset();
			this.options.items[index] = null;
			this.options.items.splice(index,1);
		}
		$(item.dom).remove();
		$.each(this.options.items, function(i,e){
			if( $(e.dom).length > 0 ){
				$(e.dom).data("index", i);
			}
		});
		this.renderEmpty();
		this.validate();
	};
	
	this.renderEmpty = function(){
		if( $(this.options.dom.list).find("li").length === 0 ){
			$(this.dom).addClass("isempty");
		}else{
			$(this.dom).removeClass("isempty");
		}
	};
	
	this.render = function(d){
		d = d || this.options.data || [];
		d = $.isArray(d)?d:[d];
		this.options.data = d;
		this.reset();
		$.each(this.options.data, (function(self){
			return function(i, e){
				var li = self.addItem(e);
				if( li ){
					$(self.options.dom.list).append(li);
				}
			};
		})(this));
		this.renderEmpty();
		this.validate();
		if( this.canEdit() ){
			$(this.dom).addClass("canedit");
		}else{
			$(this.dom).removeClass("canedit");
		}
		if( this.isEditMode() ){
			$(this.dom).addClass("editmode").removeClass("viewmode");
		}else{
			$(this.dom).removeClass("editmode").addClass("viewmode");
		}
	};
	
	this.filterTargetData = function(data){
		data = data || [];
		data = $.isArray(data)?data:[data];
		var res = [];
		var _curdata = $.map(data, function(e){
			return $.extend(true,{}, e);
		});
		_curdata = $.grep(_curdata, (function(isrev){
			return function(e){
				if( isrev === true && $.trim(e.reversed) === "true" ){
					return true;
				}else if( isrev === false && $.trim(e.reversed) === ""){
					return true;
				}
				return false;
			};
		})(this.options.reverse));
		var filteredtype = $.trim( (this.options.reverse)?this.options.subjecttype:this.options.targettype ).toLowerCase();
		var subjected =  $.grep(_curdata, function(e){
			if(  filteredtype === "" ){ 
				return true;
			}else if(typeof e.entity === "object" && $.trim(e.entity["type"]).toLowerCase() === filteredtype){
				return true;
			}
			return false;
		});
		
		res = $.grep(subjected, function(e){
			if(  filteredtype === "" ){ 
				return true;
			}else if(typeof e.entity === "object" && $.trim(e.entity["type"]).toLowerCase() === filteredtype){
				return true;
			}
			return false;
		});
		if( this.isEditMode() === false ){
			var uniq = {};
			//group by entities
			$.each(res, function(i,e){
				if( !uniq[e.entity.guid] ){
					uniq[e.entity.guid] = e;
					uniq[e.entity.guid].groupverbs = []; 
				}
				uniq[e.entity.guid].groupverbs.push(e.verbname);
			});
			res = [];
			for(var i in uniq){
				if( !uniq.hasOwnProperty(i) ) continue;
				res.push(uniq[i]);
			}
		}
		this.sortData(res);
		return res;
	};
	this.sortData = function(d){
		var obj =  appdb.utils.harvester[(this.options.reverse)?this.options.subjecttype:this.options.targettype];
		if( obj && obj.sortData ){
			obj.sortData(d);
		}
		//safe to override
	};
	this._initItemTypeClass = function(){
		if( this.options.itemtype === null ){
			var tartype = $.trim((this.options.reverse)?this.options.subjecttype:this.options.targettype).toLowerCase();
			if( tartype.length > 0 ){
				tartype = tartype[0].toUpperCase() + tartype.slice(1);
			}
			if( !this.options.itemtype ){
				this.options.itemtype = appdb.views["RelationListItem" + tartype];
			}
			if( !this.options.itemtype ){
				this.options.itemtype = appdb.views.RelationListItem;
			}
		}
	};
	this._initEvents = function(){
		$(this.options.dom.actionadd).unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( self.isEditMode() && self.canEdit() && self.isValid()){
					self.addNewItem();
					self.renderEmpty();
				}
				return false;
			};
		})(this));
	};
	this._initActions = function(){
		if( !this.options.dom.actionadd ){
			this.options.dom.actionadd = $("<button type='button' class='btn btn-success btn-compact action add' title='add new relation' tabindex='-1'>add</button>");
			$(this.dom).append(this.options.dom.actionadd);
		}else{
			this.options.dom.actionadd = $(this.dom).children("button.action.add")
		}
	};
	this._initContainer = function(){
		
		$(this.dom).empty();
		this.options.data = this.filterTargetData(o.data);
		this._initItemTypeClass();
		
		if( !this.options.dom.list ){
			this.options.dom.list = $("<ul class='relations'></ul>");
			$(this.dom).prepend(this.options.dom.list);
		}
		
		this._initActions();
		
		$(this.dom).prepend("<div class='emptylist'></div>");
		$(this.dom).find(".emptylist").append(this.options.messages.emptylist || "");
		this._initEvents();
	};
	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
	};
	this._init();
});
appdb.views.RelationListOrganization = appdb.ExtendClass(appdb.views.RelationList, "appdb.views.RelationListOrganization", function(o){
	this.options = $.extend(true, this.options, {messages:{emptylist: "No related organizations"}});
});
appdb.views.RelationListProject = appdb.ExtendClass(appdb.views.RelationList, "appdb.views.RelationListProject", function(o){
	this.options = $.extend(true, this.options, {messages:{emptylist: "No related projects"}});
});
appdb.views.RelationListSoftwareProject = appdb.ExtendClass(appdb.views.RelationListProject, "appdb.views.RelationListSoftwareProject", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "project"),targettype:(o.targettype || "software"),reverse:true, itemtype: appdb.views.RelationListItemSoftwareProject});
});
appdb.views.RelationListSoftwareOrganization = appdb.ExtendClass(appdb.views.RelationListOrganization, "appdb.views.RelationListSoftwareOrganization", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "organization"),targettype:(o.targettype || "software"),reverse:true, itemtype: appdb.views.RelationListItemSoftwareOrganization});
});
appdb.views.RelationListVApplianceProject = appdb.ExtendClass(appdb.views.RelationListProject, "appdb.views.RelationListVApplianceProject", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "project"),targettype:(o.targettype || "vappliance"),reverse:true, itemtype: appdb.views.RelationListItemVApplianceProject});
});
appdb.views.RelationListVApplianceOrganization = appdb.ExtendClass(appdb.views.RelationListOrganization, "appdb.views.RelationListVApplianceOrganization", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "organization"),targettype:(o.targettype || "vappliance"),reverse:true, itemtype: appdb.views.RelationListItemVApplianceOrganization});
});

appdb.views.RelationListSWApplianceProject = appdb.ExtendClass(appdb.views.RelationListProject, "appdb.views.RelationListSWApplianceProject", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "project"),targettype:(o.targettype || "swappliance"),reverse:true, itemtype: appdb.views.RelationListItemSWApplianceProject});
});
appdb.views.RelationListSWApplianceOrganization = appdb.ExtendClass(appdb.views.RelationListOrganization, "appdb.views.RelationListSWApplianceOrganization", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "organization"),targettype:(o.targettype || "swappliance"),reverse:true, itemtype: appdb.views.RelationListItemSWApplianceOrganization});
});
appdb.views.RelationListSWApplianceVappliance = appdb.ExtendClass(appdb.views.RelationListOrganization, "appdb.views.RelationListSWApplianceVappliance", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "swappliance"),targettype:(o.targettype || "vappliance"),reverse:false, itemtype: appdb.views.RelationListItemSWApplianceVappliance});
});
appdb.views.RelationExternalListItem = appdb.ExtendClass(appdb.View, "appdb.views.RelationExternalListItem", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {},
		ishidden: false,
		canedit: (typeof o.canedit === "boolean")?o.canedit:false,
		editmode:(typeof o.editmode === "boolean")?o.editmode:false
	};
	this.reset = function(){
		this.unsubscribeAll();
		this._initContainer();
	};
	this.isChanged = function(){
		if( this.options.ishidden === this.isHidden() ){
			return false;
		}
		return true;
	};
	this.getEntityType = function(){
		return $.trim( ( (this.options.data || {} ).entity || {} )["type"] );
	};
	this.getEntity = function(){
		var etype = this.getEntityType();
		if( $.inArray(etype,["software","vappliance","swappliance"]) > -1 ){
			return this.options.data.entity["application"];
		}
		return this.options.data.entity[etype];
	};
	this.getData = function(){
		var res =  $.extend({},this.options.data);
		if( typeof res.hidden === "undefined" ){
			res.hidden = "false";
		}
		return res;
	};
	this.canEdit = function(){
		return this.options.canedit;
	};
	this.isEditMode = function(){
		return this.options.editmode;
	};
	this.getVerbName = function(){
		var reltype = appdb.utils.RelationsRegistry.getRelationTypeByID(this.options.data.relationtypeid);
		if( !reltype ) return "unknown";
		return $.trim(reltype.reverseverb);
	};
	this.getDefaultHtml = function(){
		return "";
	};
	this.getSoftwareHtml = function(){
		var d = this.getEntity();
		var etype = this.getEntityType();
		var img = $("<img src='/apps/getLogo?id=" +d.id + "' alt=''/>");
		var displayname = d.name;
		var description  = (d.description) || ((d.val)?d.val():"") ;
		if( $.trim(description) !== "" ){
			displayname += " | " + description;
		}
		var name = $("<span class='name'></span>").text(displayname);
		var html = $("<a class='relationentitylistitem " + etype + "' href='/store/"+etype+"/"+d.cname+"' target='_blank'></a>").append(img).append(name);
		return html;
	};
	this.getVapplianceHtml = function(){
		return this.getSoftwareHtml();
	};
	this.getSwapplianceHtml = function(){
		return this.getSoftwareHtml();
	};
	this.getSubjectHtml = function(){
		var etype = this.getEntityType();
		var func =  "get" + etype[0].toUpperCase() + etype.slice(1) + "Html";
		if( typeof this[func] === "function" ){
			return this[func].apply(this);
		}
		return this.getDefaultHtml();		
	};
	this.isHidden = function(){
		return ( $.trim(this.options.data.hidden) === "true" );
	};
	this.toggleVisibility = function(){
		if( this.isHidden() ){
			delete this.options.data.hidden;
		}else{
			this.options.data.hidden = "true";
		}
		this.onVisibilityChange();
	};
	this.onVisibilityChange = function(){
		if( this.isHidden() ){
			$(this.dom).addClass("ishidden").find("button.action").removeClass("allow").addClass("btn-warning");
		}else{
			$(this.dom).removeClass("ishidden").find("button.action").addClass("allow").removeClass("btn-warning");
		}
	};
	this.getAction = function(){
		var button = $("<button type='button' class='action btn' title='toggle external relation visibility' ></button>");
		var hidden = $("<span class='hide-relation'>visible</span>");
		var visible = $("<span class='view-relation'>hidden</span>");
		$(button).append(hidden).append(visible);
		$(button).unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.toggleVisibility();
				return false;
			};
		})(this));
		setTimeout((function(self){
			return function(){
				self.onVisibilityChange();
			};
		})(this), 1);
		
		return button;
	};
	
	this.render = function(){
		this.reset();
		var div = $("<div class='relationtype'></div>");
		var verbdiv = $("<div class='relationverb'></div>").text(this.getVerbName());
		var subjecttypediv = $("<div class='relationsubjecttype'></div>").text(this.getEntityType());
		var subjectdiv = $("<div class='relationsubject'></div>").append(this.getSubjectHtml());
		$(div).append(verbdiv).append(subjecttypediv).append(subjectdiv);
		if( this.isEditMode() && this.canEdit() ){
			$(div).append(this.getAction());
		}
		$(this.dom).append(div);
	};
	
	this._initContainer = function(){
		$(this.dom).empty();
	};
	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
		if( $.trim(this.options.data.hidden) === "true" ){
			this.options.ishidden = true;
		}else{
			this.options.ishidden = false;
		}
	};
	this._init();
});
appdb.views.RelationExternalList = appdb.ExtendClass(appdb.View, "appdb.views.RelationExternalList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		allowedtypes: o.allowedtypes || [],
		canedit: (typeof o.canedit === "boolean")?o.canedit:false,
		editmode:(typeof o.editmode === "boolean")?o.editmode:false,
		items: []
	};
	this.reset = function(){
		this.unsubscribeAll();
		$.each(this.options.items, function(i, e){
			e.reset();
			e = null;
		});
		this.options.items = [];
		this._initContainer();
	};
	this.getItemData = function(item){
		var d = item.getData();
		if( !d ) return {};
		
		return d;
	};
	this.hasChanges = function(){
		var res = $.grep(this.options.items, function(e){
			return e.isChanged();
		});
		return (res.length > 0 );
	};
	this.setupForm = function(frm) {
		if ($(frm).length === 0) {
			return false;
		}
		var i, len = this.options.items.length, item, inp;
		$(frm).remove("input[name^='relation']");
		for (i = 0; i < len; i += 1) {
			item = this.options.items[i];
			if( !item.getData() || !item.isChanged() ) continue;
			inp = document.createElement("input");
			$(inp).attr("name", "extrelation" + i).attr("type", "hidden").attr("value", JSON.stringify(this.getItemData(item)));
			$(frm).append(inp);
		}
		return true;
	};
	this.addItem = function(d){
		var li = $("<li></li>");
		var item = new appdb.views.RelationExternalListItem({
			container: li,
			parent: this,
			canedit: this.options.canedit,
			editmode: this.options.editmode,
			data: d
		});
		this.options.items.push(item);
		item.render();
		return li;
	};
	this.render = function(d){
		this.reset();
		if( d ){
			d = $.isArray(d)?d:[d];
			this.options.data = this.filterData(d);
		}
		var ul = $(this.dom).children("ul");
		$.each(this.options.data, (function(self){
			return function(i, e){
				var li = self.addItem(e);
				if( li !== null ){
					$(ul).append(li);
				}
			};
		})(this));
	};
	this.filterData = function(d){
		var res = $.grep(d, function(d){
			return ($.trim(d.reversed) === "true");
		});
		
		if( this.options.allowedtypes.length > 0 ){
			res = $.grep(res, (function(self){
				return function(e){
					return ( $.inArray(e.entity["type"], self.options.allowedtypes ) > -1 );
				};
			})(this));
		}
		
		return res;
	};
	this._initContainer = function(){
		$(this.dom).empty();
		$(this.dom).append("<ul class='relations external'></ul>");
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this.options.data = this.options.data || [];
		this.options.data = $.isArray(this.options.data)?this.options.data:[this.options.data];
		this.options.data = this.filterData(this.options.data);
		this._initContainer();
	};
	this._init();
});
appdb.views.RelationListSoftwareSoftware = appdb.ExtendClass(appdb.views.RelationList, "appdb.views.RelationListSoftwareSoftware", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "software"),targettype:(o.targettype || "software"),reverse:false, itemtype: appdb.views.RelationListItemSoftwareSoftware});
});
appdb.views.RelationListSoftwareVappliance = appdb.ExtendClass(appdb.views.RelationList, "appdb.views.RelationListSoftwareVappliance", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "software"),targettype:(o.targettype || "vappliance"),reverse:false, itemtype: appdb.views.RelationListItemSoftwareVappliance});
});
appdb.views.RelationListVapplianceSoftware = appdb.ExtendClass(appdb.views.RelationList, "appdb.views.RelationListVapplianceSoftware", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "vappliance"),targettype:(o.targettype || "software"),reverse:false, itemtype: appdb.views.RelationListItemSoftwareSoftware});
});
appdb.views.RelationListVapplianceVappliance = appdb.ExtendClass(appdb.views.RelationList, "appdb.views.RelationListVApplianceVappliance", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "vappliance"),targettype:(o.targettype || "vappliance"),reverse:false, itemtype: appdb.views.RelationListItemSoftwareVappliance});
});
appdb.views.RelationListSwapplianceSoftware = appdb.ExtendClass(appdb.views.RelationList, "appdb.views.RelationListSwapplianceSoftware", function(o){
	this.options = $.extend(true, this.options, {subjecttype: (o.subjecttype || "swappliance"),targettype:(o.targettype || "software"),reverse:false, itemtype: appdb.views.RelationListItemSwapplianceSoftware});
});

appdb.views.RelatedEntitiesListItem = appdb.ExtendClass(appdb.View, "appdb.views.RelatedEntitiesListItem", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || {}
	};
	this.getRelationDescription = function(r,d){
		var stype, ttype;
		if( r.issubjecttype ){
			stype = this.options.data.entitytype;
			ttype = appdb.pages.index.currentContent();
		}else{
			ttype = this.options.data.entitytype;
			stype = appdb.pages.index.currentContent();
		}
		var rel = appdb.utils.RelationsRegistry.getByTriplet(stype,r.verbname,ttype) || null;
		if( rel ){
			rel = rel[0];
			var v = rel.directverb;
			if( r.issubjecttype ){
				ename = appdb.pages.index.currentContentName() || ("this " + rel.subjecttype);
				sname = this.options.data.name;
				v = "is " + rel.reverseverb;
				return ename + " " + v + " " + sname;
			}
			sname = appdb.pages.index.currentContentName() || ("this " + rel.targettype);
			ename = this.options.data.name;
			return sname + " " + v + " " + ename;			
		}
		return "";
	};
	this.getRelationVerbName = function(r){
		var stype, ttype;
		if( r.issubjecttype ){
			stype = this.options.data.entitytype;
			ttype = appdb.pages.index.currentContent();
		}else{
			ttype = this.options.data.entitytype;
			stype = appdb.pages.index.currentContent();
		}
		var rel = appdb.utils.RelationsRegistry.getByTriplet(stype,r.verbname,ttype) || null;
		if( rel ){
			rel = rel[0];
			if( r.issubjecttype ){
				return rel.reverseverb;
			}else{
				return rel.directverb;
			}
			
		}
		return e.verbname;
	};
	this.getRelationsHtml = function(){
		var rels = this.options.data.relations || [];
		var dom = $("<span class='relations'></span>");
		$.each(rels, (function(self){
			return function(i,e){
				
				var r = $("<span class='relation'></span>").attr("tooltip", self.getRelationDescription(e)).text(self.getRelationVerbName(e));
				$(dom).append(r);
			};
		})(this));
		return dom;
	};
	this.renderSoftware = function(){
		var d = (this.options.data || {});
		var seltype = this.options.data.entitytype;
		var dom = $("<div class='relationentitylistitem relationtype "+seltype+"'></div>");
		var fv1 = $("<div class='fieldvalue name '><div class='field'>Name:</div><div class='value'></div></div>");
		var fv2 = $("<div class='fieldvalue description '><div class='field'>Description:</div><div class='value'></div></div>");
		var ws = $("<div class='website'><a href='#' target='_blank' ></a></div>");
		$(fv1).find(".value").text($.trim(d.name) || "" );
		$(fv2).find(".value").text((d.val)?$.trim(d.val()):"");
		$(dom).append(fv1).append(fv2);
		if( $.trim(d.cname) !== ""){
			var u = $.trim(d.cname);
			u = appdb.config.endpoint.base + "store/" + seltype +"/" + u;
			$(ws).children("a").attr('href', u).attr('title', "View details of "+seltype+" " + d.name).text("");
			$(dom).append(ws);
			$(ws).unbind("click").bind("click", (function(data,entitytype){ 
				return function(ev){
					ev.preventDefault();
					if(entitytype === 'vappliance'){
						appdb.views.Main.showVirtualAppliance({id: data.id,cname:data.cname},{mainTitle : data.name });
					}else if(entitytype === 'software'){
						appdb.views.Main.showApplication({id: data.id,cname:data.cname},{mainTitle : data.name });	
					}else if(entitytype === 'swappliance'){
						appdb.views.Main.showSoftwareAppliance({id: data.id,cname:data.cname},{mainTitle : data.name });	
					}
					return false;
				};
			})(d,seltype));
		}
		$(dom).prepend("<img src='/apps/getlogo?id=" + $.trim(d.id) + "' alt='' />");
		$(dom).append(this.getRelationsHtml());
		return $(dom)[0];
	};
	this.renderVappliance = function(){
		return this.renderSoftware();
	};
	this.renderSwappliance = function(){
		return this.renderSoftware();
	};
	this.renderProject = function(){
		
	};
	this.renderOrganization = function(){
		
	};
	this.renderDefault = function(){
		
	};
	this.getRenderer = function(typename){
		typename = $.trim(typename).toLowerCase();
		var r = null;
		if( typename !== "" && typename.length >= 2){
			typename = typename[0].toUpperCase() + typename.substr(1);
			r = this["render" + typename];
			
		}
		if( typeof r !== "function" ){
			return this.renderDefault;
		}else{
			return r;
		}
	
	};
	this.render = function(){
		var renderer = this.getRenderer(this.options.data.entitytype);
		$(this.dom).empty().append(renderer.apply(this));
	};
	
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = o.parent;
	};
	
	this._init();
});
appdb.views.RelatedEntitiesList = appdb.ExtendClass(appdb.View, "appdb.views.RelatedEntitiesList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		itemtype: appdb.views.RelatedEntitiesListItem,
		allowedtypes: o.allowedtypes || [],
		alloweddirection: o.alloweddirection || "both",
		items: []
	};
	this.reset = function(){
		this.unsubscribeAll();
		$.each(this.options.items || [], function(i,e){
			if( e && e.reset ){
				e.unsubscribeAll();
				e.reset();
			}
			e = null;
		});
		this.options.items = [];
		$(this.dom).empty();
	};
	this.addItem = function(data){
		var li = $("<li></li>");
		
		var item = new appdb.views.RelatedEntitiesListItem({
			container: li,
			parent: this,
			data: data
		});
		this.options.items.push(item);
		item.render();
		return li;
	};
	this.render = function(data){
		this.reset();
		if( data ){
			this.options.data = data;
		}
		$.each(this.options.data, (function(self){
			return function(i,e){
				var li = self.addItem(e);
				if( li !== null ){
					$(self.dom).append(li);
				}
			};
		})(this));
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = $(this.options.parent);
	};
	this._init();
});

appdb.views.RelatedEntities = appdb.ExtendClass(appdb.View,"appdb.views.RelatedEntities", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: o.data || [],
		itemsdata: [],
		allowedtypes: o.allowedtypes || [],
		direction: $.trim(o.direction) || "both",
		allowedverbs: o.allowedverbs || [],
		dom: {
			filter: $("<div class='filtercontainer'></div>"),
			list: $("<ul class='relations'></ul>"),
			pager: $("<div class='pager'></div>")
		},
		pagelength: o.pagelength,
		pageoffset: 0,
		pagenumber: 1,
		datapages: [],
		filters: {
			data: {
				types: [],
				verbs: []
			},
			allowedtypes: [],
			allowedverbs: [],
			direction: $.trim(o.direction) || "both",
			verbs: "Relations",
			types: "Entity Types"
		}
	};
	this.sortData = function(data){
		data.sort(function(a,b){
			var aa = a.addedon || "";
			var bb = b.addedon || "";
			if( aa > bb ) return 1;
			if( aa < bb ) return -1;
			return 0;
		});
	};
	this.nextPage = function(){
		if( this.hasNextPage() ){
			this.currentPage(this.getPageNumber()+1);
		}
	};
	this.previousPage = function(){
		if( this.hasPreviousPage() ){
			this.currentPage(this.getPageNumber()-1);
		}
	};
	this.currentPage = function(pn){
		if( this.options.datapages.length >= pn ){
			this.renderList(this.options.datapages[pn-1]);
		}else{
			this.renderList([]);
		}
	};
	this.hasNextPage = function(){
		var pn = this.getPageNumber(), pc = this.pageCount();
		return (pc > 1 && pn < pc );
	};
	this.hasPreviousPage = function(){
		return (this.pageCount() > 1 && this.getPageNumber() > 1);
	};
	this.getPageNumber = function(){
		return this.options.pagenumber || 1;
	};
	this.getPageLength = function(){
		return this.options.pagelength || 1;
	};
	this.getEntityCount = function(){
		return (this.options.itemsdata || []).length;
	};
	this.pageCount = function(){
		return Math.ceil( this.getEntityCount() / this.getPageLength() );
	};
	this.renderPager = function(){
		$(this.options.dom.pager).empty();
		if( this.pageCount() <= 1 ){
			$(this.options.dom.pager).addClass("hidden");
			return;
		}
		var ul = $("<ul></ul>"), pg;
		var prv = $("<li class='previous page'><span>Previous</span></li>");
		var nxt = $("<li class='next page'><span>Next</span></li>");
		
		$(ul).append(prv);
		
		for( var i = 0; i< this.pageCount(); i+=1){
			pg = $("<li class='page number'><span>"+(i+1)+"</span></li>");
			$(pg).data("pagenumber", i+1);
			if( i === 0){
				$(pg).addClass("current");
			}
			$(ul).append(pg);
		}
		$(ul).children("li").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("current") ) return false;
				$(this).siblings().removeClass("current");
				if( $(this).hasClass("previous") ){
					self.previousPage();
				}else if( $(this).hasClass("next") ){
					self.nextPage();
				}else{
					self.currentPage( ($(this).data("pagenumber") << 0) );
					$(this).addClass("current");
				}
				return false;
			};
		})(this));
		$(ul).append(nxt);
		$(this.options.dom.pager).append(ul).removeClass("hidden");
		this.options.pageoffset = 0;
		this.options.pagenumber= 1;
	};
	this.setFilter = function(filter, enabled){
		enabled = (typeof enabled === "boolean" )?enabled:true;
		if( filter.enabled === enabled ){
			return filter;
		}
		
		var af = this.options.filters["allowed" + filter.group] || [];
		if( enabled && $.inArray(filter.name, af) === -1 ){
			af.push(filter.name);
		}else if( enabled === false && $.inArray(filter.name, af) > -1 ){
			af.splice($.inArray(filter.name, af), 1);
		}
		this.options.filters["allowed" + filter.group] = af;
		filter.enabled = enabled;
		this.options.itemsdata = this.filterData(this.options.data, this.options.filters);
		this.updateFilterLogistics();
		this.renderPager();
		this.currentPage(1);
		
		return filter;
	};
	this.updateFilterLogistics = function(){
		this.extractFilterLogistics();
		var flts = this.options.filters.data;
		var dom = $(this.options.dom.filter);
		var itemcount = this.options.itemsdata.length;
		$(dom).find("li[data-filter-group]").removeClass("disabled").find("input").removeAttr("disabled");
		for(var f in flts){
			if( flts.hasOwnProperty(f) === false ) continue;
			$.each(flts[f], function(ii,ee){
				var li = $(dom).find("li[data-filter-group='"+ee.group+"']");
				if( ee.currentCount === 0 || ee.currentCount >= itemcount){
					$(li).each(function(i,e){
						if( $(this).attr("filter-name") === ee.name ){
							if($(this).find("input").is(":checked") === true ){
								$(this).addClass("disabled").find("input").attr("disabled","disabled");
							}
						}
					});
				}else{
					$(li).each(function(i,e){
						if( $(this).attr("filter-name") === ee.name ){
							$(this).removeClass("disabled").find("input").removeAttr("disabled");
						}
					});
				}
			});
			
		}
		
	};
	this.addFilter = function(name, d){
		var li = $("<li class='filtertype'></li>");
		var ul = $("<ul></ul>");
		$(li).append("<div>"+this.options.filters[name]+"</div>");
		$(li).append(ul);
				
		$.each(d, function(self){
			return function(i,e){
				var e = d[i];
				var l = $("<li></li>");
				var c = $("<input type='checkbox' checked='checked' />");
				$(l).append(c).append("<label>"+e.name+" (" + e.count + ")</label>");
				$(l).attr("data-filter-group",e.group).attr("filter-name",e.name);
				$(c).data("filter", e).unbind("change").bind("change",(function(self){
					return function(ev){
						$(this).data("filter", self.setFilter($(this).data("filter"), $(this).is(":checked")));
					};
				})(self));
				$(l).bind("click", function(ev){
					ev.preventDefault();
					ev.stopPropagation();
					if( $(this).hasClass("disabled") && $(this).find("input").is(":checked") === false){
						return;
					}
					var inp = $(this).find("input");
					if(inp.is(":checked")){
						$(inp).removeAttr("checked");
					}else{
						$(inp).attr("checked","checked");
					}
					$(inp).trigger("change");
					return false;
				});
				$(ul).append(l);
			};
		}(this));
		return li;
		
	};
	this.renderFilterList = function(){
		if( this.options.itemsdata.length <= this.options.pagelength){
			return;
		}
		var div = $("<div class='header'>Filters</div>");
		var menu = $("<div class='menucontents'></div>");
		var ul = $("<ul class='filters'></ul>"), li;
		for(var f in this.options.filters.data){
			if(this.options.filters.data.hasOwnProperty(f) === false) continue;
			li = this.addFilter(f, this.options.filters.data[f]);
			if( li !== null ){
				$(ul).append(li);
			}
		}
		$(menu).append(ul);
		$(this.options.dom.filter).empty().append(div).append(menu);
	};
	this.renderEmptyList = function(){
		$(this.dom).closest(".relationgridcontainer").addClass("hidden");
	};
	this.renderList = function(data){
		if( this.subviews.list ){
			this.subviews.list.reset();
			this.subviews.list = null;
		}
		this.subviews.list = new appdb.views.RelatedEntitiesList({
			container: this.options.dom.list,
			parent: this,
			data: data
		});
		this.subviews.list.render(data);
	};
	this.render = function(data){
		if( data ){
			data = data || [];
			data = $.isArray(data)?data:[data];
			this.options.itemsdata = this.filterData(data);
			this.extractFilterData();
		}
		if( this.options.itemsdata.length > 0 ){
			this.renderFilterList();
			this.renderPager();
			this.currentPage(1);
		}else{
			this.renderEmptyList();
		}
	};
	this.extractFilterLogistics = function(){
		var flts = this.options.filters.data;
		for(var flt in flts){
			if( flts.hasOwnProperty(flt) === false ) continue;
			var f = flts[flt];
			$.each(f, (function(self){
				return function(i,e){
					var fname = "filterBy" + e.group[0].toUpperCase() + e.group.slice(1);
					if( typeof self[fname] === "function" ){
						var fd = self[fname]([e.name], self.options.itemsdata );
						self.options.filters.data[flt][i].currentCount = fd.length;
					}
				};
			})(this));
		}
	};
	this.extractFilterData = function(){
		var verbs = {};
		var types = {};
		$.each(this.options.itemsdata, function(ii,ee){
			$.each(ee.relations, function(i,e){
				var v = appdb.utils.RelationsRegistry.getRelationTypeByID(e.relationtypeid);
				var verbtype = (e.reversed === true)?"reverseverb":"directverb";
				verbs[v[verbtype]] = (verbs[v[verbtype]])?verbs[v[verbtype]]+1:1;
			});
		});
		this.options.filters.data.verbs = [];
		this.options.filters.allowedverbs = [];
		for(var i in verbs ){
			if( verbs.hasOwnProperty(i) === false )continue;
			this.options.filters.data.verbs.push({name: i, count: verbs[i], enabled: true, group: "verbs", currentCount: verbs[i]});
			this.options.filters.allowedverbs.push(i);
		}
		
		$.each(this.options.itemsdata, function(i,e){
			if( e.entitytype ){ 
				types[e.entitytype] = (types[e.entitytype])?types[e.entitytype]+1:1;
			}
		});
		this.options.filters.data.types = [];
		this.options.filters.allowedtypes = [];
		for(var i in types ){
			if( types.hasOwnProperty(i) === false )continue;
			this.options.filters.data.types.push({name: i, count: types[i], enabled: true, group: "types", currentCount: types[i]});
			this.options.filters.allowedtypes.push(i);
		}
	};
	this.extractDataPages = function(data){
		data = data || this.options.itemsdata || [];
		data = $.isArray(data)?data:[data];
		this.options.datapages = [];
		var offset = 0, plen = this.getPageLength();
		for(var i=1; i<=this.pageCount(); i+=1){
			this.options.datapages.push(data.slice(offset, i*plen));
			offset = i*plen;
		}
	};
	this.transformData = function(data){
		data = data || this.options.data ||[];
		var grp = {};
		$.each(data, function(i, e){
			var t = (e && e.entity)?$.trim(e.entity["type"]):"";
			var rt = ( $.inArray(t,["software","vappliance","swappliance"])>-1)?"application":t;
			var k, o = (t!=="")?e.entity[rt]:null;
			
			if( !o ) return;
			
			k = o.id + ":" + t;
			if( !grp[k] ){
				o.entitytype = t;
				o.relations = [];
				grp[k] = o;
			}
			var r = {id: e.id, relationtypeid: e.relationtypeid, verbname: e.verbname, guid: e.guid,reversed: (($.trim(e.reversed)==="true")?true:false), addedon: e.addedon, issubjecttype: ($.trim(e.reversed) === "true") };
			grp[k].relations.push( r );
			grp[k].dataindex = i;
		});
		this.options.itemsdata = [];
		for(var i in grp){
			if( grp.hasOwnProperty(i) === false ) continue;
			this.options.itemsdata.push(grp[i]);
		}
		return this.options.itemsdata;
	};
	this.filterByDirection = function(direction, d){
		d = d ||[];
		d = $.isArray(d)?d:[d];
		
		if( $.trim(direction) !== "both" ){
			return $.grep(d, function(e){
				if($.trim(e.reversed).toLowerCase() === "true" && direction === "reverse"  ){
					return true;
				}else if($.trim(e.reversed) === "" && direction === "direct" ){
					return true;
				}
				return false;
			});
		}
		return d;
	};
	this.filterByVerbs = function(verbs, d){
		verbs = verbs || [];
		verbs = $.isArray(verbs)?verbs:[];
		d = d ||[];
		d = $.isArray(d)?d:[d];
		var rels = appdb.model.StaticList.RelationTypes || [];
		rels = $.isArray(rels)?rels:[rels];
		var res = $.grep(d, function(ee){
			var found = $.grep(rels, function(r){
				var foundrel = $.grep(ee.relations, function(er){
					if( $.trim(er.relationtypeid) === $.trim(r.id) ){
						if( $.trim(er.reversed) === "true" && $.inArray(r.reverseverb, verbs) > -1 ){
							return true;
						}else if($.trim(er.reversed) === "false" && $.inArray(r.directverb, verbs) > -1){
							return true;
						}
					}
				});
				return ( foundrel.length > 0 );
			});
			return ( found.length > 0 );
		});
		return res;
	};
	this.filterByTypes = function(types, d){
		types = types || [];
		types = $.isArray(types)?types:[];
		d = d ||[];
		d = $.isArray(d)?d:[d];
		return $.grep(d, function(ee){
			return  ( ee.entitytype && $.inArray(ee.entitytype, types) > -1 );
		});
	};
	this.filterData = function(d, opts){
		opts = opts || this.options;
		
		//copy data array
		var data = $.map(d, function(e, i){
			return $.extend(true, {}, e);
		});
		
		//filter hidden
		data = $.grep(data, function(e){
			return ( $.trim(e.hidden) !== "true" );
		});
		
		//filter direction
		if( opts.direction !== "both" ){
			data = this.filterByDirection(opts.direction, data);
		}
		
		this.sortData(data);
		data = this.transformData(data);		
		
		//filter types
		if( opts.allowedtypes.length > 0 ){
			data = this.filterByTypes(opts.allowedtypes, data);
		}
		
		//filter verbs
		if( opts.allowedverbs.length > 0 ){
			data = this.filterByVerbs(opts.allowedverbs, data);
		}
		
		
		this.extractDataPages(data);
		return data;
	};
	this._initContainer = function(){
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.filter);
		$(this.dom).append(this.options.dom.list);
		$(this.dom).append(this.options.dom.pager);
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		if( this.options.data && this.options.data.length && this.options.data.length > 0 ){
			this.options.itemsdata = this.filterData(o.data);
		}
		this._initContainer();
	};
	this._init();
});


appdb.views.SuggestedList = appdb.ExtendClass(appdb.View, "appdb.views.SuggestedList", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		data: [],
		pagelength: o.pagelength || 4,
		pageoffset: 0,
		pagenumber: 1,
		datapages: [],
		dom: {
			list: $("<ul class='relations'></ul>"),
			pager: $("<div class='pager'></div>")
		}
	};
	this.nextPage = function(){
		if( this.hasNextPage() ){
			this.currentPage(this.getPageNumber()+1);
		}
	};
	this.previousPage = function(){
		if( this.hasPreviousPage() ){
			this.currentPage(this.getPageNumber()-1);
		}
	};
	this.currentPage = function(pn){
		if( this.options.datapages.length >= pn ){
			this.renderList(this.options.datapages[pn-1]);
		}else{
			this.renderList([]);
		}
	};
	this.hasNextPage = function(){
		var pn = this.getPageNumber(), pc = this.pageCount();
		return (pc > 1 && pn < pc );
	};
	this.hasPreviousPage = function(){
		return (this.pageCount() > 1 && this.getPageNumber() > 1);
	};
	this.getPageNumber = function(){
		return this.options.pagenumber || 1;
	};
	this.getPageLength = function(){
		return this.options.pagelength || 1;
	};
	this.getEntityCount = function(){
		return (this.options.data || []).length;
	};
	this.pageCount = function(){
		return Math.ceil( this.getEntityCount() / this.getPageLength() );
	};
	this.renderPager = function(){
		$(this.options.dom.pager).empty();
		if( this.pageCount() <= 1 ){
			$(this.options.dom.pager).addClass("hidden");
			return;
		}
		var ul = $("<ul></ul>"), pg;
		var prv = $("<li class='previous page'><span>Previous</span></li>");
		var nxt = $("<li class='next page'><span>Next</span></li>");
		
		$(ul).append(prv);
		
		for( var i = 0; i< this.pageCount(); i+=1){
			pg = $("<li class='page number'><span>"+(i+1)+"</span></li>");
			$(pg).data("pagenumber", i+1);
			if( i === 0){
				$(pg).addClass("current");
			}
			$(ul).append(pg);
		}
		$(ul).children("li").unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				if( $(this).hasClass("current") ) return false;
				$(this).siblings().removeClass("current");
				if( $(this).hasClass("previous") ){
					self.previousPage();
				}else if( $(this).hasClass("next") ){
					self.nextPage();
				}else{
					self.currentPage( ($(this).data("pagenumber") << 0) );
					$(this).addClass("current");
				}
				return false;
			};
		})(this));
		$(ul).append(nxt);
		$(this.options.dom.pager).append(ul).removeClass("hidden");
		this.options.pageoffset = 0;
		this.options.pagenumber= 1;
	};
	this.addItem = function(data){
		var li = $("<li></li>");
		var d = (data || {}).application;
		d.category = d.category || [];
		d.category = $.isArray(d.category)?d.category:[d.category];
		var catids = $.map(d.category, function(e){
			return e.parentid;
		});
		var seltype = ($.inArray("34", catids)>-1)?"vappliance":"software";
		var dom = $("<div class='relationentitylistitem relationtype '></div>");
		var fv1 = $("<div class='fieldvalue name '><div class='field'>Name:</div><div class='value'></div></div>");
		var fv2 = $("<div class='fieldvalue description '><div class='field'>Description:</div><div class='value'></div></div>");
		var ws = $("<div class='website'><a href='#' target='_blank' ></a></div>");
		$(fv1).find(".value").text($.trim(d.name) || "" );
		$(fv2).find(".value").text($.trim(d.description) || "");
		$(dom).append(fv1).append(fv2);
		if( $.trim(d.cname) !== ""){
			var u = $.trim(d.cname);
			u = appdb.config.endpoint.base + "store/" + seltype +"/" + u;
			$(ws).children("a").attr('href', u).attr('title', "View details of "+seltype+" " + d.name).text("");
			$(dom).append(ws);
			$(ws).unbind("click").bind("click", (function(data,entitytype){ 
				return function(ev){
					ev.preventDefault();
					if(entitytype === 'vappliance'){
						appdb.views.Main.showVirtualAppliance({id: data.id,cname:data.cname},{mainTitle : data.name });
					}else if(entitytype === 'software'){
						appdb.views.Main.showApplication({id: data.id,cname:data.cname},{mainTitle : data.name });	
					}
					return false;
				};
			})(d,seltype));
		}
		$(dom).prepend("<img src='/apps/getlogo?id=" + $.trim(d.id) + "' alt='' />");
		$(li).append(dom);
		return li;
	};
	this.renderList = function(d){
		$(this.options.dom.list).empty();
		$.each(d, (function(self){
			return function(i,e){
				var li = self.addItem(e);
				if( li !== null ){
					$(self.options.dom.list).append(li);
				}
			};
		})(this));
	};
	this.render = function(d){
		if( typeof d !== "undefined"){
			this.options.data = $.isArray(d||[])?d:[d];
		}
		this._initContainer();
		this.extractDataPages();
		this.renderPager();
		this.currentPage(1);
	};
	this.extractDataPages = function(){
		this.options.datapages = [];
		var offset = 0, plen = this.getPageLength();
		for(var i=1; i<=this.pageCount(); i+=1){
			this.options.datapages.push(this.options.data.slice(offset, i*plen));
			offset = i*plen;
		}
	};
	this._initContainer = function(){
		$(this.dom).empty();
		$(this.dom).append(this.options.dom.list);
		$(this.dom).append(this.options.dom.pager);
	};
	this._init = function(){
		this.dom = $(this.options.container);
		this.parent = this.options.parent;
		this._initContainer();
	};
	this._init();
});

appdb.views.UrlValidator = appdb.ExtendClass(appdb.View, "appdb.views.UrlValidator", function(o){
	this.options = {
		container: $(o.container),
		parent: o.parent || null,
		dom: {},
		currentUrl: o.url || "",
		model: new appdb.ModelItemClass({}, {
			caller: {
				endpoint: appdb.config.endpoint.base + "apps/checkurl?url={url}&mime={mime}" 
			}
		}),
		currentStatus: "",
		currentMessage: "",
		checkingUrl: "",
		onGetValueDelegate: (typeof o.ongetvalue === "function")?o.ongetvalue:null,
		returnMime: ( (typeof o.returnMime !== "undefined")?$.trim(o.returnMime):"binary")
	};
	this.setStatus = function(status, message){
		status = status || this.options.currentStatus || "init";
		status = $.trim(status).toLowerCase();
		var dom = this.options.dom;
		$(dom).removeClass("hasresult checking error success init");
		if( typeof message === "undefined" ){
			message = this.options.currentMessage;
		}
		if( message.length > 0 ){
			message[0] = message[0].toUpperCase();
		}
		switch(status){
			case "checking":
				$(dom).addClass(status);
				$(dom.action).addClass("hidden");
				$(dom.status).removeClass("hidden");
				$(dom.result).addClass("hidden");
				break;
			case "error":
				$(dom).addClass(status);
				$(dom.action).removeClass("hidden");
				$(dom.status).addClass("hidden");
				$(dom.result).removeClass("hidden").empty();
				if( $.trim(message) !== "" ){
					$(dom.result).html("<img src='/images/vappliance/warning.png' alt=''/><span class='error'>"+message+"</span>");
				}
				this.publish({event: "validation", value: {isValid: false, error: message}});
				break;
			case "success":
				$(dom).addClass(status);
				$(dom.action).removeClass("hidden");
				$(dom.status).addClass("hidden");
				$(dom.result).removeClass("hidden");
				$(dom.result).html("<img src='/images/tick.png' alt=''/><span class='success'>" + (message || "available")+ "</span>");
				this.publish({event: "validation", value: {isValid: true}});
				break;
			case "warning":
				$(dom).addClass(status);
				$(dom.action).removeClass("hidden");
				$(dom.status).addClass("hidden");
				$(dom.result).removeClass("hidden");
				$(dom.result).html("<img src='/images/tick_warning.png' alt=''/><span class='success'>" + (message || "available")+ "</span>");
				this.publish({event: "validation", value: {isValid: true}});
				break;
			case "init":
			default:
				$(dom.action).removeClass("hidden");
				$(dom.status).addClass("hidden");
				$(dom.result).removeClass("hidden").empty();
				break;
		}
		$(dom).addClass(status);
		this.options.currentStatus = status;
		this.options.currentMessage = message;
	};
	this.check = function(url){
		url = url || ((this.options.onGetValueDelegate)?this.options.onGetValueDelegate():this.parent.getDisplayValue());
		if( $.trim(url) === "" ){
			this.setStatus("init");
			return ;
		}
		this.options.checkingUrl = $.trim(url);
		this.setStatus("checking");
		this.options.model.get({url: appdb.utils.base64.encode(url), mime: this.options.returnMime});
	};
	this.displayMessage = function(display){
		display = (typeof display === "boolean" )?display: false;
		if( this.options.currentStatus === "error" || this.options.currentStatus === "success"){
			if( display ){
				$(this.options.dom.result).removeClass("hidden");
			}else{
				$(this.options.dom.result).addClass("hidden");
			}
		}
	};
	this.show = function(){
		$(this.options.dom.container).show();
	};
	this.hide = function(){
		$(this.options.dom.container).hide();
	};
	this.initContainer = function(){
		this.options.dom.container = $("<div class='urlvalidator'></div>");
		this.options.dom.action = $("<a href='' title='Check url availability' class='validate icontext popupmessage' ><img src='/images/refresh.png' alt=''/><span>Check Url availability</span></a>");
		this.options.dom.status = $("<div class='status icontext hidden popupmessage' ><img src='/images/ajax-loader-trans-orange.gif' alt=''/><span class='message'>...checking</span></div>");
		this.options.dom.result = $("<div class='result icontext hidden popupmessage' ></div>");
		$(this.options.dom.container).append(this.options.dom.action).append(this.options.dom.status).append(this.options.dom.result);
		$(this.dom).append(this.options.dom.container);
		$(this.options.dom.action).unbind("click").bind("click", (function(self){
			return function(ev){
				ev.preventDefault();
				self.check();
				return false;
			};
		})(this));
	};
	this.init = function(){
		this.dom = this.options.container;
		this.parent = this.options.parent;
		this.initContainer();
		this.options.model.subscribe({event: "select", callback: function(v){
				this.options.currentUrl = this.options.checkingUrl;
				if( v.result ){
					v.result = $.trim(v.result).toLowerCase();
					this.setStatus(v.result, v.message);
				}else{
					this.setStatus("init");
				}
		},caller: this});
	};
	this.init();
});

