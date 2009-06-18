// requires utils.js for addCommas, sorry.

/* requires the following html on the page:

    <div id="pagination">
        <p id="page-prev"><a href="#">Previous</a></p>
        <p id="page-next"><a href="#">Next</a></p>
        <p id="page-numbers">...</p>
    </div>

   TODO: create this html with unique ids, so there can be more than one Paginate per page
*/

function Pagination(count, offset, pageChangeCallback) {
    this.count = count;
    this.offset = offset;
    this.pageChangeCallback = pageChangeCallback;
}

Pagination.prototype = {

    count:  10,
    offset: 0,

    pageChangeCallback: null,
    
    // call setTotal to init these:
    total:    0,
    numPages: 0,
    pageNum:  0,
    
    setTotal: function(total) {
        this.total = total;
        this.numPages = Math.ceil(this.total / this.count);
        this.pageNum = 1 + (this.offset / this.count);
    },

    paginate: function () {
    
        var pages = document.getElementById('page-numbers');
        
        while (pages.firstChild) {
            pages.removeChild(pages.firstChild);
        }
        
        if (this.pageNum > 6) {
            var a = document.createElement('a');
            a.appendChild(document.createTextNode('1'));
            a.href = "#1";
            a.onclick = this.getOnPageClick();
            pages.appendChild(a);
            pages.appendChild(document.createTextNode(' ... '));
        }
        
        var startNum = Math.max(1, this.pageNum - 5);
        var endNum = Math.min(this.numPages, this.pageNum + 5);
        for (var i = startNum; i <= endNum; i++) {
            if (i == this.pageNum) {
                var span = document.createElement('span');
                span.appendChild(document.createTextNode(' ' + addCommas(i)));
                pages.appendChild(span);
            }
            else {
                pages.appendChild(document.createTextNode(' '));
                var a = document.createElement('a');
                a.onclick = this.getOnPageClick();
                a.href = "#"+i;
                a.appendChild(document.createTextNode(addCommas(i)));
                pages.appendChild(a);                
            }
        }
        
        if (this.pageNum < this.numPages - 5) {
            pages.appendChild(document.createTextNode(' ... '));
            var a = document.createElement('a');
            a.onclick = this.getOnPageClick();
            a.href = "#"+this.numPages;
            a.appendChild(document.createTextNode(addCommas(this.numPages)));
            pages.appendChild(a);                
        }
    },
    
    falsy: function() {
        return false;
    },

    _onNextClick: null,
    
    getNextClick: function() {
        if (!this._onNextClick) {
            var thePager = this;
            this._onNextClick = function() {
                if (thePager.total && thePager.offset + thePager.count < thePager.total) {
                    thePager.offset += thePager.count;
                    thePager.pageChangeCallback();
                }
                return false;
            }        
        }
        return this._onNextClick;
    },

    _onPrevClick: null,
    
    getPrevClick: function() {
        if (!this._onPrevClick) {
            var thePager = this;
            this._onPrevClick = function() {
                if (thePager.offset > 0) {
                    thePager.offset -= thePager.count;
                    thePager.pageChangeCallback();
                }
                return false;
            }        
        }
        return this._onPrevClick;
    },

    _onPageClick: null,
    
    getOnPageClick: function() {
        if (!this._onPageClick) {
            var thePager = this;        
            this._onPageClick = function(e) {
                if (!e) var e = window.event;
                var a = e.target || e.srcElement;
                a.onclick = thePager.falsy;
                thePager.offset = thePager.count * (parseInt(a.href.split("#")[1]) - 1);
                // TODO: disable all page links in disableNav?
                thePager.pageChangeCallback();
                return false;
            }
        }
        return this._onPageClick;        
    },
    
    disableNav: function() {
        document.getElementById('page-next').onclick = this.falsy;
        document.getElementById('page-prev').onclick = this.falsy;
    },
    
    enableNav: function() {
        if (this.total && (this.offset + this.count < this.total)) {
            document.getElementById('page-next').onclick = this.getNextClick();
            document.getElementById('page-next').style.visibility = 'visible';
        }
        else {
            document.getElementById('page-next').style.visibility = 'hidden';
        }
        if (this.offset > 0) {            
            document.getElementById('page-prev').onclick = this.getPrevClick();
            document.getElementById('page-prev').style.visibility = 'visible';
        }
        else {
            document.getElementById('page-prev').style.visibility = 'hidden';
        }
    }
    
};
