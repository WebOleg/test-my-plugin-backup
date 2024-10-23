function input_test(input) { 
	input.value = input.value.replace(/[^\d,]/g, "");
};

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
            url         : bnaData.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action    : 'create_payor',
                nonce     : bnaData.nonce,
                fieldtext : $('.form_create_payor').serializeArray() 
            },
            success: function( data ) {
                stopLoadingAnimation();
                let message = $('.woocommerce-notices-wrapper');
                message.get(0).scrollIntoView();
                message.html(data.message);
                self.prop('disabled', false);
                
				setTimeout(()=>{
					window.location.reload();
				}, 3000);
            },
            error: function( data ){
                //console.log(data);
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
            url         : bnaData.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action    : 'update_payor',
                nonce     : bnaData.nonce,
                fieldtext : $('.form_update_payor').serializeArray() 
            },
            success: function( data ) {
                stopLoadingAnimation();
                let message = $('.woocommerce-notices-wrapper');
                message.get(0).scrollIntoView();
                message.html(data.message);
                self.prop('disabled', false);
                
                setTimeout(()=>{
					window.location.reload();
				}, 3000);
            },
            error: function( data ){
                //console.log(data);
            }
        });    
    });
    
    // Copy shipping address from billing address
	$('#bna-address-copy_button').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 

        let self = $(this);
        self.prop('disabled', true);

        startLoadingAnimation();

        $.ajax({
            url         : bnaData.url,
            type        : 'POST', 
            dataType    : "json",
            data        : {
                action    : 'copy_billing_address_to_shipping',
                nonce     : bnaData.nonce
            },
            success: function( data ) {
                stopLoadingAnimation();
                
                if( data.success === 'true' ){
					let message = $('.woocommerce-notices-wrapper');
					message.get(0).scrollIntoView();
					message.html(data.message);
				}
				
                self.prop('disabled', false);
                
				if( data.success === 'true' ){
					setTimeout(()=>{
						window.location.reload();
					}, 1000);
				}
            },
            error: function( data ){
                //console.log(data);
            }
        });    
    });
	
    // update address
    //$('#update_address').on('click', function(event){
        //event.stopPropagation();
        //event.preventDefault(); 

        //let self = $(this);
        //self.prop('disabled', true);

        //startLoadingAnimation();

        //$.ajax({
            //url         : bnaData.url,
            //type        : 'POST', 
            //dataType    : "json",
            //data        : {
                //action    : 'update_payor',
                //nonce     : bnaData.nonce,
                //fieldtext : $('.form_update_address').serializeArray() 
            //},
            //success: function( data ) {
                //stopLoadingAnimation();
                //let message = $('.woocommerce-notices-wrapper');
                //message.get(0).scrollIntoView();
                //message.html(data.message);
                //self.prop('disabled', false);
                
            //},
            //error: function( data ){
                //console.log(data);
            //}
        //});    
    //});

    $('.btn-del-payment').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 
		let self = $(this);
        
        let winHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        $('#confirm-header').text( $(this).data('order-question') + ' ' + $(this).data('current-method') + '?');
        $('#confirm-wrapper').css({'display': 'block', 'opacity': '1', 'height': winHeight + 'px'});
        $('#confirm-cancel').on('click', function() {
			$('#confirm-wrapper').css({'display': 'none', 'opacity': '0'});
			return false;
		});
        $('#confirm-ok').on('click', function() {
			$('#confirm-wrapper').css({'display': 'none', 'opacity': '0'});
        
			self.prop('disabled', true);

			startLoadingAnimation();

			$.ajax({
				url         : bnaData.url,
				type        : 'POST', 
				dataType    : "json",
				data        : {
					action  : 'delete_payment',
					nonce   : bnaData.nonce,
					id      : self.data('id') 
				},
				success: function( data ) {

					stopLoadingAnimation();
					let message = $('.woocommerce-notices-wrapper');
					message.get(0).scrollIntoView();
					message.html(data.message);

					setTimeout(()=>{
						window.location.reload();
					}, 3000);

					self.prop('disabled', false);
				},
				error: function( data ){
					//console.log(data);
				}
			});
		});
           
    });
	
    $('#save_payment').on('click', function(event){
        event.stopPropagation();
        event.preventDefault();
        
        $('.bna-input').each(function(i) {
			if ( $(this).val() == 0 ) {
				$(this).addClass('invalid');
			}
		});	
        
        let invalidElements = $('.invalid');
        if (invalidElements.length) {
			return false;
		} else {
			let self = $(this);
			self.prop('disabled', true);

			startLoadingAnimation();

			$.ajax({
				url         : bnaData.url,
				type        : 'POST', 
				dataType    : "json",
				data        : {
					action    : 'add_payment',
					nonce     : bnaData.nonce,
					fieldtext : $('.form_save_payment').serializeArray() 
				},
				success: function( data ) {

					stopLoadingAnimation();
					let message = $('.woocommerce-notices-wrapper');
					message.get(0).scrollIntoView();
					message.html(data.message);
					
					setTimeout(()=>{
						window.location.href=bnaData.paymentMethodsEndpointUrl;
					}, 1000);
					
					self.prop('disabled', false);
					
				},
				error: function( data ){
					//console.log(data);
				}
			}); 
		}  
    });


    $('.btn-del-subscription').on('click', function(event){
        event.stopPropagation();
        event.preventDefault();
        let self = $(this);

        let winHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        $('#confirm-header').text( $(this).data('order-question') + ' #' + $(this).data('order-id') + '?');
        $('#confirm-wrapper').css({'display': 'block', 'opacity': '1', 'height': winHeight + 'px'});
        $('#confirm-cancel').on('click', function() {
			$('#confirm-wrapper').css({'display': 'none', 'opacity': '0'});
			return false;
		});
		$('#confirm-ok').on('click', function() {
			$('#confirm-wrapper').css({'display': 'none', 'opacity': '0'});
        
			self.prop('disabled', true);

			startLoadingAnimation();

			$.ajax({
				url         : bnaData.url,
				type        : 'POST', 
				dataType    : "json",
				data        : {
					action  : 'delete_subscription',
					nonce   : bnaData.nonce,
					id      : self.data('id') 
				},
				success: function( data ) {

					stopLoadingAnimation();
					let message = $('.woocommerce-notices-wrapper');
					message.get(0).scrollIntoView();
					message.html(data.message);

					if( data.success === 'true' ){
						setTimeout(()=>{
							window.location.reload();
						}, 1000);
					}

					self.prop('disabled', false);
				},
				error: function( data ){
					//console.log(data);
				}
			});
        });
            
    });
    
    $('.btn-suspend-subscription').on('click', function(event){
        event.stopPropagation();
        event.preventDefault(); 
        let self = $(this);
        
        let winHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        $('#confirm-header').text( $(this).data('order-question') + ' #' + $(this).data('order-id') + '?');
        $('#confirm-wrapper').css({'display': 'block', 'opacity': '1', 'height': winHeight + 'px'});
        $('#confirm-cancel').on('click', function() {
			$('#confirm-wrapper').css({'display': 'none', 'opacity': '0'});
			return false;
		});
        $('#confirm-ok').on('click', function() {
			$('#confirm-wrapper').css({'display': 'none', 'opacity': '0'});
				
			self.prop('disabled', true);

			startLoadingAnimation();

			$.ajax({
				url         : bnaData.url,
				type        : 'POST', 
				dataType    : "json",
				data        : {
					action  : 'suspend_subscription',
					nonce   : bnaData.nonce,
					id      : self.data('id'),
					suspend : self.data('suspend') 
				},
				success: function( data ) {

					stopLoadingAnimation();
					let message = $('.woocommerce-notices-wrapper');
					message.get(0).scrollIntoView();
					message.html(data.message);
					
					if( data.success === 'true' ){
						setTimeout(()=>{
							window.location.reload();
						}, 1000);
					}

					self.prop('disabled', false);
				},
				error: function( data ){
					//console.log(data);
				}
			});
		});
		   
    });

})(jQuery);
