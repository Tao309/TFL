$(document).ready(function() {
    //console.log( "Page is ready by jQuery!" );
    AUTOLOAD.submitForm();
    AUTOLOAD.clickButton();
    AUTOLOAD.requestUpload();
    AUTOLOAD.datetimeELements();
    AUTOLOAD.bbTagsEvent();
    AUTOLOAD.tImage();
    AUTOLOAD.clickWindow();
    AUTOLOAD.removeClosestElement();
});

//AJAX request example
/*
AJAX.send({
	method: 'POST',
	url: '/section/index/test',
	//cache: false,
	data: {
		a: 1,
		b: 2,
		c: 3,
	},
	success: function(r) {
		
	},
	error: function(e) {
		
	}
})
.then(function(r) {
	console.log('Done!', r);
});
*/


eventF.addEvent('load', window, function() {
	//console.log( "Page is ready by JS 1!" );


    setTimeout(function () {
        //console.clear('Clear!');
    }, 800);
});



