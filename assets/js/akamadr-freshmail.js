(function($) {

    $( document ).ready(function() {
        //$( ".spinner" ).hide();
    });


    

     
    

    $('.js-af-form').on( 'submit', function( e ) {
        e.preventDefault();

        var $this = $(this),
            $submit = $this.find('.freshmail_submit');

        $submit.addClass('loading');
            
        jQuery.ajax({
            type:"POST",
            url: af_object.ajaxurl,
            data: {
                'action' : 'af_submit_subscriber',
                'freshmail_email' : $this.find('.js-af-mail').val()
            },
            success:function(response){
                if ( response.success ) {
                   // console.log( response );
                   // $( ".spinner" ).show();
                    $( "#newsletter__succes" ).html( response.data.popup );
                    $('#ModalNewsletter').modal('hide');
                    $('#ModalCenter').modal('show');
                    $( ".newsletter-notyfication" ).html( "<span class='newsletter_succes'>" + response.data.message + "</div>" );
                   // $( ".spinner" ).hide();
                    
                } else {
                    $( ".newsletter-notyfication" ).html( "<span class='newsletter_error'>" + response.data.message + "</div>" );
                }

                $submit.removeClass('loading');
            },
            error: function(errorThrown){
                //$( ".spinner" ).show();
                alert(errorThrown);
                //$( ".spinner" ).hide();
            } 
        });
    });

})(jQuery)