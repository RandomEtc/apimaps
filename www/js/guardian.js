/* functions in here help you build urls to call the Guardian API 
   ...it somewhat relies on utils.js (for getQueryString)
   ...but probably shouldn't. */

function GuardianAPI(apiBase, apiKey) {
    this.apiBase = apiBase;
    this.apiKey = apiKey;
}

GuardianAPI.prototype = {

    apiBase: null,
    apiKey: null,

    /* args should definitely include a callback function name */
    getAPIContentURL: function(id, args) {
        return this.getAPIURL('/content/item/'+id, args);
    },
    
    /* args should definitely include a callback function name */
    getAPISearchURL: function(args) {
        return this.getAPIURL('/content/search', args);
    },
    
    /* args should definitely include a callback function name */
    getAPIURL: function(path, args) {
        args['api_key'] = this.apiKey;
        args['format'] = 'json';
        return this.apiBase + path + "?" + getQueryString(args);;
    }
    
};