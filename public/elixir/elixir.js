window.appdb = window.appdb || {};
var userAgent = navigator.userAgent.toLowerCase();

// Figure out what browser is being used
$.browser = {
	version: (userAgent.match( /.+(?:rv|it|ra|ie)[\/: ]([\d.]+)/ ) || [])[1],
	safari: /webkit/.test( userAgent ),
	opera: /opera/.test( userAgent ),
	msie: /msie/.test( userAgent ) && !/opera/.test( userAgent ),
	mozilla: /mozilla/.test( userAgent ) && !/(compatible|webkit)/.test( userAgent )
};
String.prototype.soundex = function(p){
	var i, j, l, r, p = isNaN(p) ? 4 : p > 10 ? 10 : p < 4 ? 4 : p,
	m = {BFPV: 1, CGJKQSXZ: 2, DT: 3, L: 4, MN: 5, R: 6},
	r = (s = this.toUpperCase().replace(/[^A-Z]/g, "").split("")).splice(0, 1);
	for(i = -1, l = s.length; ++i < l;)
		for(j in m)
			if(j.indexOf(s[i]) + 1 && r[r.length-1] != m[j] && r.push(m[j]))
				break;
	return r.length > p && (r.length = p), r.join("") + (new Array(p - r.length + 1)).join("0");
};
window.elixir = window.elixir || {};

