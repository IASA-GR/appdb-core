/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


window.elixir = window.elixir || {};
window.elixir.view = (function(window,document,$,_){
	var _usedOwlSubClasses = [];
	
	function sortByLabel(a,b){
		var aa = _.trim(a.label);
		var bb = _.trim(b.label);
		if(aa < bb ) return -1;
		if(aa > bb ) return 1;
		return 0;
	}
	function sortDisciplineName(a,b){
		a = a || {};
		b = b || {};
		a.discipline = a.discipline || [];
		a.discipline = _.isArray(a.discipline)?a.discipline:[a.discipline];
		b.discipline = b.discipline || [];
		b.discipline = _.isArray(b.discipline)?b.discipline:[b.discipline];
		
		var aa = _.trim(a.name);
		var bb = _.trim(b.name);
		if( a.discipline.length > 0 && b.discipline.length === 0 ) return -1;
		if( a.discipline.length === 0 && b.discipline.length > 0 ) return 1;
		if(aa < bb ) return -1;
		if(aa > bb ) return 1;
		
		return 0;
	}
	function updateTopicForDiscipline(d,el){
		$(el).closest("tr").find(".actions").addClass("updating");
		$(el).closest("table").addClass("updating");
		elixir.data.setData(d, function(error, v){
			$(el).closest("tr").find(".actions").removeClass("updating");
			$(el).closest("table").removeClass("updating");
			if( error ){
				alert(error);
			}else{
				renderTopicMappings($(el).closest("table"));
				renderDisciplineMappings($("#disciplinesTable"));
			}
		});
	}
	function checkEmptyTables(table){
		if( $(table).find("tr.selectedchildren,tr.haschildren,tr.selectedtopic,tr.hasvalue").length === 0 ){
			$(table).parent().addClass("isempty");
		}else{
			$(table).parent().removeClass("isempty");
		}
	}
	function toggleViewSelected(el,table){
		if( $(el).find("input").prop("checked") ){
			$(el).find("input").prop("checked",false);
			$(table).removeClass("viewselected");
			$(table).parent().removeClass("viewselected");
			$(table).parent().removeClass("isempty");
		}else{
			$(el).find("input").prop("checked",true);
			$(table).addClass("viewselected");
			$(table).parent().addClass("viewselected");
			$(table).find("tr.selectedchildren,tr.haschildren").each(function(i,e){
				$(table).treetable("expandNode", $(this).attr("data-tt-id"));
			});
			checkEmptyTables(table);
		}
	}
	function viewSelectedHandler(dom, table){
		$(dom).unbind("mouseup").bind("mouseup", function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			toggleViewSelected(this,table);
			return false;
		}).unbind("click").bind("click", function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			return false;
		});
		if( $(dom).find("input").prop("checked") ){
			toggleViewSelected(dom,table);
		}
		setTimeout(function(){
			checkEmptyTables(table);
		},10);
		
	}
	function viewUsageHandler(dom, table){
		$(dom).unbind("mouseup").bind("mouseup", function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			if( $(this).find("input").is(":checked") ){
				$(this).find("input").prop("checked",false);
				$(table).removeClass("viewusage");
			}else{
				$(this).find("input").prop("checked",true);
				$(table).addClass("viewusage");
			}
			return false;
		}).unbind("click").bind("click", function(ev){
			ev.preventDefault();
			ev.stopPropagation();
			return false;
		});
	}
	function renderTopicsDialog(discipline){
		var dom = $("body > .dialog > .body");
		var topics = $(dom).find(".treecontainer");
		
		$(dom).find(".header .title").empty().html("Topics for discipline <b>" + discipline.name + "</b>");
		$(topics).find("table").remove();
		_renderTopics(topics);
		
		$(dom).find(".actions button.close").unbind("click").bind("click", function(ev){
			ev.preventDefault();
			$("body").removeClass("showdialog");
			return false;
		});
		$("body").addClass("showdialog");
		
		viewSelectedHandler($(dom).find(".toolbar .filter"), $(dom).find("table"));
		$(dom).find("table").attr("data-disciplineid", discipline.id);
		setTimeout(function(){
			$("body > .dialog > .body .topicsView > table").treetable({ expandable: true });
			renderTopicMappings($(topics).find("table"));
		},1);
	}
	
	function renderTopicDisciplineItem(o){
		var li = $("<li></li>");
		var id = $("<span class='id'></span>").text(_.trim(o.id));
		var label = $("<span class='label'></span>").text(o.name);
		$(li).append(id).append(label);
		return li;
	}
	function renderTopicsDisciplines(o){
		var dom = $("<div class='editlistcontainer'></div>");
		var ul = $("<ul></ul>");
		var topics = elixir.data.getDisciplinesForTopicUri(o.uri);
		_.forEach(topics || [], function(v){
			$(ul).append(renderTopicDisciplineItem(v));
		});
		$(dom).append(ul);
		return dom;
	}
	function renderTopicRow(o){
		o = o || {};
		var tr = $("<tr></tr>");
		var tdowl = $("<td></td>").addClass("topic").addClass("treecell");
		var report = $("<td></td>").addClass("report").addClass("reportcell");
		var tddisc = $("<td></td>").addClass("discipline").addClass("listcell");
		var action = $("<td></td>").addClass("actions").addClass("actioncell");
		
		$(tr).attr("data-tt-id", o.id);
		$(tr).attr("data-tt-parent-id", o.rootid);
		$(tr).attr("data-topicid",o.topicid);
		$(tr).attr("data-topicparentid",o.topicid);
		$(tr).attr("data-uri",o.uri);
		$(tr).attr("data-topiclabel",o.label);
		
		$(tdowl).append($("<span class='label'></span>").text(o.label));
		$(tddisc).append(renderTopicsDisciplines(o));
		$(report).append("<span class='topics local' title='Count of explicit related topics'><span class='value'></span></span>");
		$(report).append("<span class='topics children' title='Count of related topics registered for child nodes.'><span class='value'></span></span>");
		
		var btnselect = $("<button type='button' class='btn btn-primary select'>select</button>");
		var btnunselect = $("<button type='button' class='btn btn-warning unselect'>unselect</button>");
		var loader =  $("<div class='sending'>...updating</div>");
		$(btnselect).unbind("click").bind("click",(function(topic){
			return function(ev){
				ev.preventDefault();
				var discid = $(this).closest(".treetable").data("disciplineid");
				var topicuri = $(this).closest("tr").data("uri");
				var topicid = topicuri.replace("http://edamontology.org/topic_","");
				var label = $(this).closest("tr").find("td.treecell > .label").text();
				var data = {
					topic_id: topicid,
					topic_uri: topicuri,
					topic_label: label,
					discipline_id: discid,
					action: "add"
				};
				console.log(JSON.stringify(data));
				updateTopicForDiscipline(data, this);
				return false;
			};
		})(o));
		$(btnunselect).unbind("click").bind("click",(function(topic){
			return function(ev){
				ev.preventDefault();
				var discid = $(this).closest(".treetable").data("disciplineid");
				var topicuri = $(this).closest("tr").data("uri");
				var topicid = topicuri.replace("http://edamontology.org/topic_","");
				var label = $(this).closest("tr").find("td.treecell > .label").text();
				var data = {
					topic_id: topicid,
					topic_uri: topicuri,
					topic_label: label,
					discipline_id: discid,
					action: "remove"
				};
				console.log(JSON.stringify(data));
				updateTopicForDiscipline(data,this);
				return false;
			};
		})(o));
		$(action).append(btnselect).append(btnunselect).append(loader);
		$(tr).append(tdowl).append(report).append(tddisc).append(action);
		
		return tr;
	}
	function renderTopicsHTML(pid, ppid){
		var res = [];
		var data = (elixir.data.getTopicsByParentId(pid) || []).sort(sortByLabel);

		_.forEach(data, function(v){
			var args = {
				topicid: v.about.replace("http://edamontology.org/topic_",""),
				parentid: pid,
				rootid: _usedOwlSubClasses.join(":"),
				id: _usedOwlSubClasses.join(":") + ":" + v.about.replace("http://edamontology.org/topic_",""),
				uri: _.trim(v.about),
				label: _.trim(v.label)
			};
			if( _usedOwlSubClasses.length === 0 && args.id && args.id[0] === ":"){
				args.id = args.id.substr(1);
			}
			_usedOwlSubClasses.push(args.topicid);
			res = res.concat([renderTopicRow(args)]);
			res = res.concat(renderTopicsHTML(args.topicid,pid));
			_usedOwlSubClasses.pop();
		});
		return res;
	}
	function renderTopicMappings(el){
		var disciplineid = _.trim($(el).data("disciplineid"));
		var report = elixir.data.generateReport("topics",disciplineid);
		
		_.forEach(report, function(v){
			var id = _.trim(v.about).replace("http://edamontology.org/topic_","");
			var tr = $(el).find("[data-topicid='" + id + "']");
			var e = $(tr).children("td.reportcell");
			var loc = $(e).find(".topics.local");
			var chs = $(e).find(".topics.children");
			$(loc).find(".value").text(v.discipline.local);
			$(chs).find(".value").text(v.discipline.children);
			if( (v.discipline.local << 0) > 0 ){
				$(loc).addClass("hasvalue");
				$(tr).addClass("hasvalue");
			}else{
				$(loc).removeClass("hasvalue");
				$(tr).removeClass("hasvalue");
			}
			if( (v.discipline.children << 0) > 0 ){
				$(chs).addClass("hasvalue");
				$(tr).addClass("selectedchildren");
			}else{
				$(chs).removeClass("hasvalue");
				$(tr).removeClass("selectedchildren");
			}
			$(tr).find("td.discipline.listcell").empty().append(renderTopicsDisciplines({uri:v.about}));
			$(tr).removeClass("selectedtopic");
			if(disciplineid){
				var isselected = false;
				$(tr).find("td.discipline.listcell ul li .id").each(function(i,ee){
					if( $(this).text() === $.trim(disciplineid) ){
						isselected = true;
					}
				});
				if( isselected ){
					$(tr).addClass("selectedtopic");
				}
			}
		});
		setTimeout(function(){
			checkEmptyTables(el);
		},10);
		
	}
	function renderDisciplineTopicItem(o){
		var li = $("<li></li>");
		var id = $("<span class='id'></span>").text(_.trim(o.about).replace("http://edamontology.org/topic_",""));
		var label = $("<span class='label'></span>").text(o.label);
		$(li).append(id).append(label);
		return li;
	}
	function renderDisciplineTopics(o){
		var dom = $("<div class='editlistcontainer'></div>");
		var ul = $("<ul></ul>");
		var topics = elixir.data.getTopicsByDisciplineId(o.id);
		_.forEach(topics || [], function(v){
			$(ul).append(renderDisciplineTopicItem(v));
		});
		$(dom).append(ul);
		return dom;
	}
	function renderDisciplineRow(o){
		o = o || {};
		var tr = $("<tr></tr>");
		var tdowl = $("<td></td>").addClass("topic").addClass("listcell");
		var report = $("<td></td>").addClass("report").addClass("reportcell");
		var usage = $("<td></td>").addClass("usage").addClass("usagecell");
		var tddisc = $("<td></td>").addClass("discipline").addClass("treecell");
		var action = $("<td></td>").addClass("actions").addClass("actioncell");
		
		$(tr).attr("data-tt-id", o.id);
		$(tr).attr("data-tt-parent-id", o.parentid);
		$(tr).attr("data-name",o.name);
		
		$(tddisc).append($("<span></span>").text(o.name));
		$(tdowl).append(renderDisciplineTopics(o));
		$(report).append("<span class='topics local' title='Count of explicit related topics'><span class='value'></span></span>");
		$(report).append("<span class='topics children' title='Count of related topics registered for child nodes.'><span class='value'></span></span>");
		$(usage).append("<span class='usage local' title='explicit usage of discipline'><span class='value'></span></span>");
		var button = $("<button type='button' class='btn btn-primary'>edit</button>");
		$(button).unbind("click").bind("click",(function(discipline){
			return function(ev){
				ev.preventDefault();
				if($("body").hasClass(".showdialog") === false ){
					renderTopicsDialog(discipline);
				}
				return false;
			};
		})(o));
		$(action).append(button);
		$(tr).append(tddisc).append(usage).append(report).append(tdowl).append(action);
		
		return tr;
	}
	function renderDisciplineHTML(o,pid){
		var res = [];
		var data = (o || elixir.data.getData("disciplines") || []).sort(sortDisciplineName);
		
		_.forEach(data, function(v){
			var args = {
				id: v.id,
				parentid: pid,
				name: _.trim(v.name)
			};
			res = res.concat([renderDisciplineRow(args)]);
			res = res.concat(renderDisciplineHTML(v.discipline || [], v.id));
		});
		return res;
	}
	
	function renderDisciplineMappings(el){
		var report = elixir.data.generateReport("disciplines");
		_.forEach(report, function(v){
			var tr = $(el).find("[data-tt-id='" + v.id + "']");
			var e = $(tr).children("td.reportcell");
			var u = $(tr).children("td.usagecell");
			var loc = $(e).find(".topics.local");
			var chs = $(e).find(".topics.children");
			var usageloc = $(u).find(".usage.local");
			var usagechs = $(u).find(".usage.children");
			
			$(loc).find(".value").text(v.topic.local);
			$(chs).find(".value").text(v.topic.children);
			
			$(usageloc).find(".value").text(v.swusage.local);
			
			if( (v.topic.local << 0) > 0 ){
				$(loc).addClass("hasvalue");
				$(tr).addClass("hasvalue");
			}else{
				$(loc).removeClass("hasvalue");
				$(tr).removeClass("hasvalue");
			}
			if( (v.topic.children << 0) > 0 ){
				$(chs).addClass("hasvalue");
				$(tr).addClass("selectedchildren");
			}else{
				$(chs).removeClass("hasvalue");
				$(tr).removeClass("selectedchildren");
			}
			if( (v.swusage.local << 0) > 0 ){
				$(usageloc).addClass("hasvalue");
			}else{
				$(usageloc).removeClass("hasvalue");
			}
			
			$(tr).find("td.topic.listcell").empty().append(renderDisciplineTopics({id:v.id}));
		});
		setTimeout(function(){
			checkEmptyTables(el);
		},10);
	}
		
	function _renderDisciplines(el){
		var table = $("<table id='disciplinesTable'><thead><tr><th>Discipline</th><th class='usage'>Usage</th><th class='report'>report</th><th>Topics</th><th>action</th></tr></thead><tbody></tbody></table>");
		$(table).find("tbody").append(renderDisciplineHTML());
		renderDisciplineMappings(table);
		$(el).prepend(table);
		setTimeout(function(){
			$("#disciplinesTable").treetable({ expandable: true });
			viewSelectedHandler($("body .filter-selected-disciplines"),table);
			viewUsageHandler($("body .filter-discipline-usage"),table);
		},100);
	}
	
	function _renderTopics(el){
		_usedOwlSubClasses = [];
		var table = $("<table id='topicsTable'><thead><tr><th>Topics</th><th class='report'>report</th><th>Disciplines</th><th>action</th></tr></thead></thead><tbody></tbody></table>");
		$(table).find("tbody").append(renderTopicsHTML("0003"));
		$(el).prepend(table);
		setTimeout(function(){
			$("#topicsTable").treetable({ expandable: true });
		},100);
	}
	
	return {
		renderDisciplines: _renderDisciplines,
		renderTopics: _renderTopics
		
	};
})(window,document,jQuery,_);