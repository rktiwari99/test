if ( 'undefined' !== typeof jQuery ) {
	(function ( $ ) {
		$(
			function () {
				function tweakMagicButtonConfig(){
					// change the navigation that appears when users open the magic button:
					var customConfig = {
						navigation: [
							{
								nav_title: "Photos",
								slug: "photos",
								sub_nav: [],
								new_flag: true,
						},
						]
					};
					// default to photos tab when opening the magic button:
					window.location.hash = 'photos';
					window.ElementsReact && window.ElementsReact.elementorMagicButtonConfigSet( customConfig );
					setTimeout( function(){ window.location.hash = 'photos'; }, 1000 );
				}
				elementor.on(
					'preview:loaded',
					function () {
						$( elementor.$previewContents[ 0 ].body ).on( 'click', '.elementor-add-envato-button', tweakMagicButtonConfig );
					}
				);
			}
		);
	})( jQuery );
}
