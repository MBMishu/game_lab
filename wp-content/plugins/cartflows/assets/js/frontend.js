(function($){

	// Remove css when oceanwp theme is enabled.
	var remove_oceanwp_custom_style = function(){
		if( 'OceanWP' == cartflows.current_theme ){
			var style = document.getElementById("oceanwp-style-css");
			if( null != style ){
				style.remove();
			}
		}
	}

	$(document).ready(function($) {
		
		remove_oceanwp_custom_style();
		
		$(document).on( 'click', 'a[href*="wcf-next-step"]', function(e) {
			
			e.preventDefault();

			if( 'undefined' !== typeof cartflows.is_pb_preview && '1' == cartflows.is_pb_preview ) {
				e.stopPropagation();
				return;
			}

			window.location.href = cartflows.next_step; 

			return false;
		});
	});
})(jQuery);