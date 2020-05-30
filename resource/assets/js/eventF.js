function Loadevent() {

}

Loadevent.prototype.addEvent = function(type, el, fn) {
    if(typeof addEventListener !== "undefined") {
        el.addEventListener(type, fn, false);
    } else if(typeof attachEvent !== "undefined") {
        el.attachEvent('on'+type, fn);
    } else {
        el['on'+type] = fn;
    }
};

Loadevent.prototype.removeEvent = function(el, type, fn) {
    if(typeof removeEventListener !== "undefined") {
        el.removeEventListener(type, fn, false);
    } else if(typeof detachEvent !== "undefined") {
        el.detachEvent('on'+type, fn);
    } else {
        el['on'+type] = null;
    }
};

Loadevent.prototype.getTarget = function(e) {
	if(typeof e.target !== 'undefined') {
		return e.target;
	} else {
		return e.srcElement;
	}
};

Loadevent.prototype.preventDefault = function(e) {
	if(typeof e.preventDefault !== 'undefined') {
		e.preventDefault();
	} else {
		e.returnValue = false;
	}
};

const eventF = new Loadevent();