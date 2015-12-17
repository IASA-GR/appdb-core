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

function showAjaxLoading(left,msg){
	if (document.getElementById("ajaxLoading")) return;
	var s;
	if (typeof msg === "undefined") {
		s = 'Loading...';
	} else {
		s = msg;
	}
	if ( typeof left === "undefined" ) left="40%";
	document.body.style.cursor = 'wait';
	l=document.createElement("div");
	l.setAttribute("id","ajaxLoading");
	l.setAttribute("style","position:absolute; left:"+left+"; top:40%; z-index:100000;");
	l.innerHTML = "<table border='0' width='100%' height='100%'><tr><td style='font-weight:bold; color:grey; text-align: center; vertical-align:middle'><img src='/images/ajax-loader.gif' /></td></tr><tr><td style='font-weight:bold; color:grey; text-align: center; vertical-align:middle'>" + s + "</td></tr></table>";
	document.body.appendChild(l);
}

function hideAjaxLoading(){
	if (document.getElementById("ajaxLoading") ) {
        try {
    		document.body.removeChild(document.getElementById("ajaxLoading"));
        } catch(err) {
        }
	}
	document.body.style.cursor = 'default';
}
