/* listen, this is so completely minimal it's almost not worth bothering 
   (also relies on utils.js) */

function getFlickrPlaceFindURL(query) {
    return getFlickrURL('flickr.places.find', { query: query });
}

function getFlickrURL(method, args) {
    var base = 'http://api.flickr.com/services/rest/'
    args.method = method;
    // FIXME: wrap these functions in an object and take an API key in the Constructor    
    args.api_key = 'f01a2c5fb98b2b625e040440237e9b6f';
    args.format = 'json';
    return base + "?" + getQueryString(args);                
}
