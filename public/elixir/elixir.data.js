window.elixir = window.elixir || {};
window.elixir.data = (function(window, document,$,_){
	var _events = { "dataloaded": [] };
	var _data = { edam: [], disciplines: [], mappings: [] };
	var _exclude = { edam: [
			//"http://edamontology.org/topic_3071",
			//"http://edamontology.org/topic_3361"
	] };
	function _emit(name, data){
		if( name in _events && _events[name] && "length" in _events[name]){
			_.forEach(_events[name], function(e){
				e(data);
			});
		}
	}
	function _registerListener(name, fn, fn2){
		if( typeof fn === "function" && _.trim(name) ){
			_events[name] = _events[name] || [];
			_events[name] = _.isArray(_events[name])?_events[name]:[_events[name]];
			if( !_.find(_events[name],function(v){ return v === fn; }) ){
				_events[name].push(fn);
			}
		} else if( fn === false && _.trim(name) ){
			_events[name] = _events[name] || [];
			_events[name] = _.isArray(_events[name])?_events[name]:[_events[name]];
			if( typeof fn2 === "function" ){
				_events[name] = _.without(_events[name],fn2);
			}else{
				_events[name] = [];
			}
		}
	}
	function parseRawData(name){
		return function(d){
			
			if( name === 'disciplines'){//346
				_data[name] = appdb.utils.convert.toObject(d);
				_data[name] = _data[name]["discipline"] || [];
				_data[name] = _.isArray(_data[name])?_data[name]:[_data[name]];
			} else if( name === "edam" ){//286
				_data[name] = d || [];
				_data[name] = _.isArray(_data[name])?_data[name]:[_data[name]];
			} else if( name === "mappings" ){
				_data[name] = appdb.utils.convert.toObject(d);
				_data[name] = _data[name]["map"] || [];
				_data[name] = _.isArray(_data[name])?_data[name]:[_data[name]];
			} else if( name === "logistics" ){
				_data[name] = appdb.utils.convert.toObject(d);
				_data[name] = _data[name]["logistics"];
				_data[name].discipline = _data[name].discipline || [];
				_data[name].discipline = _.isArray(_data[name].discipline)?_data[name].discipline:[_data[name].discipline];
			}
		};
	}
	function _loadData(){
		return $.get("EDAM_1.9.json").then( function(d){ 
			parseRawData("edam")(d); 
			return $.get("https://appdb.egi.eu/rest/1.0/disciplines/hierarchical");
		}).then(function(d){
			parseRawData("disciplines")(d);
			return $.get("https://"+elixir.config.host+"/elixir/topicsmap?xml");
		}).then(function(d){
			parseRawData("mappings")(d);
			return $.get("https://appdb.egi.eu/rest/1.0/applications/logistics");
		}).then(function(d){
			parseRawData("logistics")(d);
			_emit("dataloaded", _data);
		});
	}
	function _getData(name){
		if( _.trim(name) ){
			if( _data[name] ){
				return _data[name] || [];
			}
		} else {
			return _data;
		}
	}
	function getTopicDetails(uri){
		uri = _.trim(uri);
		var val = "";
		if( uri << 0 > 0){
			val = "http://edamontology.org/topic_" + uri;
		}else{
			val = uri;
		}
		var res = _.find(_getData("edam"), function(v){
			return v.about === val;
		});
		
		return res;
	}
	function getDisciplineDetails(id){
		id = _.trim(id) << 0;
		var res = _.find(_getData("disciplines"), function(v){
			return _.trim(v.id)<<0 === id;
		});
		return res;
	}
	function getDisciplinesForTopicUri(uri,id){
		uri = _.trim(uri);
		id = _.trim(id) || "";
		if( !uri ) return;
		var topics = _.filter(_getData("mappings"), function(v){
			return ( v.topic && _.trim(v.topic.uri) === uri);
		});
		if( topics.length === 0 ) return;
		var result = { topic: getTopicDetails(uri),discipline: [] };
		if( topics[0].discipline ){
			_.forEach(topics, function(v){
				if( id && _.trim(v.discipline.id) === id){
					result.discipline.push(v.discipline);
				}else if( id === "" ){
					result.discipline.push(v.discipline);
				}
			});
		}
		return result.discipline;
	}
	function getDisciplineUsageById(id){
		id = _.trim(id);
		var res = _.filter(_getData("logistics").discipline, function(v){
			return v.id === id;
		});
		return (res.length === 0 )?0:(res[0].count<<0);
	}
	function getTopicsByDisciplineId(id){
		id = _.trim(id);
		if( !id ) return;
		var mappings = _getData("mappings") || [];
		var disciplines = _.filter(mappings, function(v){
			return ( v.discipline && _.trim(v.discipline.id) === id);
		});
		if( disciplines.length === 0 ) return;
		var result = { discipline: getDisciplineDetails(id), topic: [] };
		if( disciplines[0].topic ){
			_.forEach(disciplines, function(v){
				result.topic.push(getTopicDetails(v.topic.uri));
			});
		}
		return result.topic;
	}
	function getTopicsByParentId(pid){
		pid = _.trim(pid);
		function hasPid(v,id){
			id = _.trim(id);
			v.subClassOf = v.subClassOf || [];
			v.subClassOf = _.isArray(v.subClassOf)?v.subClassOf:[v.subClassOf];
			if( v.subClassOf.length === 0 && !pid ){
				return true;
			}else if( v.subClassOf.length > 0 ){
				var res = _.find(v.subClassOf, function(s){
					return s.resource.replace("http://edamontology.org/topic_","") === _.trim(id); 
				});
				return (!!res);
			}else if( !pid ){
				return true;
			}
			return false;
		}
		return _.filter(_getData("edam"), function(v){
			return hasPid(v,pid);
		});
	}
	
	function generateDisciplinesReport(disciplines,p,payload){
		var data = (disciplines || _getData("disciplines") || []);
		var result = {};
		_.forEach(data, function(v){
			var topics = getTopicsByDisciplineId(v.id);
			topics = topics || [];
			result[v.id] = result[v.id] || {id: v.id, parentid: (p)?p.id:null, parent:p, topic:{ local: 0, children: 0}, swusage: { local: 0, children: 0} };
			result[v.id].topic.local += topics.length;
			result[v.id].swusage.local += getDisciplineUsageById(v.id);
			
			v.discipline = v.discipline || [];
			v.discipline = _.isArray(v.discipline)?v.discipline:[v.discipline];
			
			if( v.discipline.length > 0 ){
				var crep = generateDisciplinesReport(v.discipline,result[v.id],payload);
				for(var i in crep){
					if( crep.hasOwnProperty(i) ){
						result[crep[i].id] = crep[i];
					}
				}
			}
			if( result[v.id].parent ){
				result[v.id].parent.topic.children += result[v.id].topic.local + result[v.id].topic.children;
				result[v.id].parent.swusage.children += result[v.id].swusage.local ;
			}
			
		});
		return result;
	}
	function generateTopicsReport(topics,p,payload){
		var data = (topics || _getData("edam") || []);
		var result = {};
		_.forEach(data, function(v){
			var disciplines = getDisciplinesForTopicUri(v.about,payload);
			disciplines = disciplines || [];
			result[v.about] = result[v.about] || {about: v.about, parentabout: (p)?p.about:null, parent:p, discipline:{ local: 0, children: 0}, swusage: { local: 0, children: 0} };
			result[v.about].discipline.local += disciplines.length;
			var id = _.trim(v.about).replace("http://edamontology.org/topic_","");
			
			v.topic = getTopicsByParentId(id);
			
			if( v.topic.length > 0 ){
				var crep = generateTopicsReport(v.topic,result[v.about],payload);
				for(var i in crep){
					if( crep.hasOwnProperty(i) ){
						result[crep[i].about] = crep[i];
					}
				}
			}
			if( result[v.about].parent ){
				result[v.about].parent.discipline.children += result[v.about].discipline.local + result[v.about].discipline.children;
			}
			
		});
		return result;
	}
	function generateReport(name,payload){
		name = (_.trim(name) || "").toLowerCase();
		var report = {};
		
		switch(name){
			case "disciplines":
				report = generateDisciplinesReport(undefined,undefined,payload);
				break;
			case "topics":
				report = generateTopicsReport(undefined,undefined,payload);
				break;
		}
		
		var result = [];
		for( var i in report ){
			if( report.hasOwnProperty(i) ){
				result.push( report[i] );
			}
		}
		return result;
	}
	function setData(d, cb){
		cb = cb || function(){};
		$.ajax({
			type: "POST",
			url: "https://" + elixir.config.host + "/elixir/topicsmap",
			data: d,
			success: function(data){
				var dd = appdb.utils.convert.toObject(data);
				if( dd.error ){
					cb(dd.error);
				}else{
					parseRawData("mappings")(data);
					cb(null, _getData("mappings"));
				}
			},
			error: function(xhr, status, error){
				cb(eval("(" + xhr.responseText + ")"));
			}
		});
	}
	return {
		on: _registerListener,
		loadData: _loadData,
		getData: _getData,
		getTopicDetails: getTopicDetails,
		getDisciplineDetails: getDisciplineDetails,
		getDisciplinesForTopicUri: getDisciplinesForTopicUri,
		getTopicsByDisciplineId: getTopicsByDisciplineId,
		getTopicsByParentId: getTopicsByParentId,
		generateReport: generateReport,
		setData: setData
	};
})(window,document,jQuery,_);
