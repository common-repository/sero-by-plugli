(function( $ ) {
	'use strict';
	$( window ).load(function() {

		$('body .copy-to-clipboard').on('click', function (e) {
	        e.preventDefault();

	        var elem = $(this);
	        var textToCopy = elem.attr('data-clip');

	        copyToClipboard(textToCopy);

	        showToast("text copied to clipboard!");

	    });
			
	});
})( jQuery );

function showToast(str) {
	const style = (el, styles) => {
		for (const s in styles) {
		  el.style[s] = styles[s]
		}
		return el;
	}

    const el = document.createElement('div');
    el.innerHTML = str;
    style(el, {
    	'position': 'fixed',
    	'right': '40px',
    	'top': '40px',
    	'z-index': '99',
    });      
    el.className = ' alert bg-rgba-info';        
    document.body.appendChild(el);
                   
    setTimeout(function(){ document.body.removeChild(el); }, 3000);
};


function copyToClipboard(str) {
    const el = document.createElement('textarea');  // Create a <textarea> element
    el.value = str;                                 // Set its value to the string that you want copied
    el.setAttribute('readonly', '');                // Make it readonly to be tamper-proof
    el.style.position = 'absolute';                 
    el.style.left = '-9999px';                      // Move outside the screen to make it invisible
    document.body.appendChild(el);                  // Append the <textarea> element to the HTML document
    const selected =            
    document.getSelection().rangeCount > 0        // Check if there is any content selected previously
      ? document.getSelection().getRangeAt(0)     // Store selection if found
      : false;                                    // Mark as false to know no selection existed before
    el.select();                                    // Select the <textarea> content
    document.execCommand('copy');                   // Copy - only works as a result of a user action (e.g. click events)
    document.body.removeChild(el);                  // Remove the <textarea> element
    if (selected) {                                 // If a selection existed before copying
        document.getSelection().removeAllRanges();    // Unselect everything on the HTML document
        document.getSelection().addRange(selected);   // Restore the original selection
    }
};

localStorage.setItem('sero-date', new Date().toISOString().slice(0, 10));