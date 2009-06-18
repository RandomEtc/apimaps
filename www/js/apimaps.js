/* this is stuff for talking to points.php and saving article/woeid pairs
   (relies on utils.js too) */

/* apiBase URL should be '.' if you want the same folder */
function APIMaps(apiBase) {
    this.apiBase = apiBase || '.';
}

APIMaps.prototype = {
    
    apiBase: null,
    
    /* successFunc should take one argument, req, and deal with req.responseText */
    addLocationToArticle: function(articleId, woeId, successFunc /*, failFunc */) {
        // TODO: deal with failFunc needs in sendRequest    
        sendRequest(this.apiBase + '/point.php', successFunc, getQueryString({
            action: 'add',
            article: articleId,
            woe: woeId
        }));
    },

    /* successFunc should take one argument, req, and deal with req.responseText */
    removeLocationFromArticle: function(articleId, woeId, successFunc /*, failFunc */) {
        sendRequest(this.apiBase + '/point.php', successFunc, getQueryString({
            action: 'remove',
            article: articleId,
            woe: woeId
        }));
    },

    /* args should have a 'callback' function name, 'count', 'offset' and optional woe id 'woe' */
    getPlaceURL: function(args) {
        args.format = 'js';
        var queryString = getQueryString(args);
        return this.apiBase + '/points.php' + '?' + queryString;
    },
    
    /* articleIds is a string of one or more comma-separated guardian article ids */
    getPlaceSearchURL: function(articleIds, callbackFuncName) {        
        var queryString = getQueryString({
            articles: articleIds,
            format: 'js',
            callback: callbackFuncName
        });
        return this.apiBase + '/points.php' + '?' + queryString;
    }
    
};
