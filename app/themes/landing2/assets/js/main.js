(function(window, $, undefined){
	'use strict';

    // Manage external links
    $('a[rel=external]').attr('target', 'blank');

    var options = {
        errorElement: 'span',
        errorPlacement: function (error, element) {
            console.log(element);
            error.appendTo(element.closest('li'));
        }
    };

    // Init modal
    $('#contact-form-bottom').iziModal();

    $('.trigger').click(function(e) {
        e.preventDefault();
        // Display modal
        $('#contact-form-bottom').iziModal('open');
        // Attach validation
        $('.contact-form').each(function() {
            $(this).validate(options);
        });

    });

})(window, jQuery);
