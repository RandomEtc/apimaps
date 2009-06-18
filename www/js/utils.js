/* this is a file full of functions that should
   probably be replaced by a battle-hardened 
   library such as jquery */
   
/* sorry. */

/************* Number Formats *************/

function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

/************* Dates *************/

/* only gets the day part from a guardian API date-time e.g. "2009-03-02T00:00:00" */
function parseDate(dateString) {
    return new Date(Date.UTC(parseInt(dateString.slice(0,4)),
                             parseInt(dateString.slice(5,7))-1, // month goes from 0-11!
                             parseInt(dateString.slice(8,10))));    
}

/* returns "Dayname dd Monthname yyyy": */
function getPrettyDate(date) {
    var days = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];
    var months = [ 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ];
    return [ days[date.getUTCDay()], date.getUTCDate(), months[date.getUTCMonth()], date.getUTCFullYear() ].join(' ');
}

/************* Query Strings *************/

/* turns each property of an object into an http querystring thing=value&other=worthless */
function getQueryString(args) {
    var argStrings = [];
    for (var arg in args) {
        argStrings.push(arg + "=" + escape(args[arg]));
    }
    return argStrings.join("&")            
}

/* looks through location.search and tries to find name=foo and return foo */
function getRequestParameter(name) {
    if (name && document.location.search.length > 0) {
        var params = document.location.search.slice(1).split("&");
        if (params && params.length > 0) {
            for (var i = 0; i < params.length; i++) {
                var parts = params[i].split("=");
                if (parts.length == 2 && parts[0] == name) {
                    return unescape(parts[1]).replace(/\+/g,' ');
                }
            }
        }
    }
    return null;
}

/************* JSON *************/

/* all this does is add a script tag with src=url, the rest is up to you */
function doTheJSON(url) {
    var script = document.createElement('script');
    script.src = url;
    document.getElementsByTagName('head')[0].appendChild(script);
}

/************* XMLHTTPRequest *************/

/* quirksmode.org provides these XMLHTTPRequest helpers
   NB:- callback should look at req.responseText for the reply */
function sendRequest(url,callback,postData) {
    var req = createXMLHTTPObject();
    if (!req) return;
    var method = (postData) ? "POST" : "GET";
    req.open(method,url,true);
    req.setRequestHeader('User-Agent','XMLHTTP/1.0');
    if (postData) {
        req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
    }
    req.onreadystatechange = function () {
        if (req.readyState != 4) return;
        if (req.status != 200 && req.status != 304) {
            // TODO: deal with these errors with a separate callback?
            showDebugMessage('HTTP error ' + req.status);
            return;
        }
        callback(req);
    }
    if (req.readyState == 4) return;
    req.send(postData);
}

var XMLHttpFactories = [
    function () {return new XMLHttpRequest()},
    function () {return new ActiveXObject("Msxml2.XMLHTTP")},
    function () {return new ActiveXObject("Msxml3.XMLHTTP")},
    function () {return new ActiveXObject("Microsoft.XMLHTTP")}
];

function createXMLHTTPObject() {
    var xmlhttp = false;
    for (var i=0;i<XMLHttpFactories.length;i++) {
        try {
            xmlhttp = XMLHttpFactories[i]();
        }
        catch (e) {
            continue;
        }
        break;
    }
    return xmlhttp;
}
