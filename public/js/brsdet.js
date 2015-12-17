// Browser Detection Javascript
// copyright 1 February 2003, by Stephen Chapman, Felgall Pty Ltd

// You have permission to copy and use this javascript provided that
// the content of the script is not changed in any way.

function whichBrs() {
	var agt=navigator.userAgent.toLowerCase();
	if (agt.indexOf("opera") !== -1) return 'Opera';
	if (agt.indexOf("staroffice") !== -1) return 'Star Office';
	if (agt.indexOf("webtv") !== -1) return 'WebTV';
	if (agt.indexOf("beonex") !== -1) return 'Beonex';
	if (agt.indexOf("chimera") !== -1) return 'Chimera';
	if (agt.indexOf("netpositive") !== -1) return 'NetPositive';
	if (agt.indexOf("phoenix") !== -1) return 'Phoenix';
	if (agt.indexOf("firefox") !== -1) return 'Firefox';
	if (agt.indexOf("safari") !== -1) return 'Safari';
	if (agt.indexOf("skipstone") !== -1) return 'SkipStone';
	if (agt.indexOf("msie") !== -1) return 'Internet Explorer';
	if (agt.indexOf("netscape") !== -1) return 'Netscape';
	if (agt.indexOf("mozilla/5.0") !== -1) return 'Mozilla';
	if (agt.indexOf('\/') !== -1) {
		if (agt.substr(0,agt.indexOf('\/')) !== 'mozilla') {
			return navigator.userAgent.substr(0,agt.indexOf('\/'));
		} else 
			return 'Netscape';
		} else 
			if (agt.indexOf(' ') !== -1) return navigator.userAgent.substr(0,agt.indexOf(' '));
	else 
		return navigator.userAgent;
}

function is_ie() {
	if (whichBrs() === "Internet Explorer")
		return true;
	else
		return false;
}
