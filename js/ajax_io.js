(function($){

    function startLoadingAnimation() {

        var imgObj = $(".loading");
        imgObj.show();
        
        var centerY = $(window).scrollTop() + ($(window).height() - imgObj.height())/2;
        var centerX = $(window).scrollLeft() + ($(window).width() - imgObj.width())/2;
        
        imgObj.offset({top:centerY, left:centerX});
    }
    
    function stopLoadingAnimation() 
    {
        $(".loading").hide();
    }
    
    // create payor
    $('#create_payor').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 

        let self = $(this);
        self.prop('disabled', true);

        startLoadingAnimation();

        $.ajax({
            url         : wc_gwpl.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action    : 'create_payor',
                nonce     : wc_gwpl.nonce,
                fieldtext : $('.form_create_payor').serializeArray() 
            },
            success: function( data ) {
                stopLoadingAnimation();
                let message = $('.woocommerce-notices-wrapper');
                message.get(0).scrollIntoView();
                message.html(data.message);
                self.prop('disabled', false);
                
                setTimeout(function() {
					window.location.href;
				}, 3000);
            },
            error: function( data ){
                console.log(data);
            }
        });    
    });

    // update_payor
    $('#update_payor').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 

        let self = $(this);
        self.prop('disabled', true);

        startLoadingAnimation();

        $.ajax({
            url         : wc_gwpl.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action    : 'update_payor',
                nonce     : wc_gwpl.nonce,
                fieldtext : $('.form_update_payor').serializeArray() 
            },
            success: function( data ) {
                stopLoadingAnimation();
                let message = $('.woocommerce-notices-wrapper');
                message.get(0).scrollIntoView();
                message.html(data.message);
                self.prop('disabled', false);
            },
            error: function( data ){
                console.log(data);
            }
        });    
    });

    // update address
    $('#update_address').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 

        let self = $(this);
        self.prop('disabled', true);

        startLoadingAnimation();

        $.ajax({
            url         : wc_gwpl.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action    : 'update_payor',
                nonce     : wc_gwpl.nonce,
                fieldtext : $('.form_update_address').serializeArray() 
            },
            success: function( data ) {
                stopLoadingAnimation();
                let message = $('.woocommerce-notices-wrapper');
                message.get(0).scrollIntoView();
                message.html(data.message);
                self.prop('disabled', false);
                
            },
            error: function( data ){
                console.log(data);
            }
        });    
    });
    
    // delete payor
    $('#delete_payor').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 

        let self = $(this);
        self.prop('disabled', true);

        startLoadingAnimation();

        $.ajax({
            url         : wc_gwpl.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action    : 'delete_payor',
                nonce     : wc_gwpl.nonce,
            },
            success: function( data ) {
                stopLoadingAnimation();
                let message = $('.woocommerce-notices-wrapper');
                message.get(0).scrollIntoView();
                message.html(data.message);
                self.prop('disabled', false);
                
                setTimeout(function() {
					window.location.href;
				}, 3000);
            },
            error: function( data ){
                console.log(data);
            }
        });    
    });

    $('.btn-del-payment').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 

        if ( confirm("Are you sure?") == false) exit();

        let self = $(this);
        self.prop('disabled', true);

        startLoadingAnimation();

        $.ajax({
            url         : wc_gwpl.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action  : 'delete_payment',
                nonce   : wc_gwpl.nonce,
                id      : self.data('id') 
            },
            success: function( data ) {

                stopLoadingAnimation();
                let message = $('.woocommerce-notices-wrapper');
                message.get(0).scrollIntoView();
                message.html(data.message);

                setTimeout(()=>{
                    window.location.reload();
                }, 1000);

                self.prop('disabled', false);
            },
            error: function( data ){
                console.log(data);
            }
        });    
    });

    $('#save_payment').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 

        let self = $(this);
        self.prop('disabled', true);

        startLoadingAnimation();

        $.ajax({
            url         : wc_gwpl.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action    : 'add_payment',
                nonce     : wc_gwpl.nonce,
                fieldtext : $('.form_save_payment').serializeArray() 
            },
            success: function( data ) {

                stopLoadingAnimation();
                let message = $('.woocommerce-notices-wrapper');
                message.get(0).scrollIntoView();
                message.html(data.message);
                self.prop('disabled', false);

            },
            error: function( data ){
                console.log(data);
            }
        });    
    });


    $('.btn-del-subscription').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 

        if ( confirm("Are you sure?") == false) exit();

        let self = $(this);
        self.prop('disabled', true);

        startLoadingAnimation();

        $.ajax({
            url         : wc_gwpl.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action  : 'delete_subscription',
                nonce   : wc_gwpl.nonce,
                id      : self.data('id') 
            },
            success: function( data ) {

                stopLoadingAnimation();
                let message = $('.woocommerce-notices-wrapper');
                message.get(0).scrollIntoView();
                message.html(data.message);

                setTimeout(()=>{
                    window.location.reload();
                }, 1000);

                self.prop('disabled', false);
            },
            error: function( data ){
                console.log(data);
            }
        });    
    });
    

})(jQuery);
