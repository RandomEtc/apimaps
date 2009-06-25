/* listen, this is so completely minimal it's almost not worth bothering 
   (also relies on utils.js) */

function getFlickrPlaceFindURL(query) {
    return getFlickrURL('flickr.places.find', { query: query });
}

function getFlickrURL(method, args) {
    var base = 'http://api.flickr.com/services/rest/'
    args.method = method;
    // FIXME: wrap these functions in an object and take an API key in the Constructor    
    args.api_key = 'YOUR KEY HERE';
    args.format = 'json';
    return base + "?" + getQueryString(args);                
}
