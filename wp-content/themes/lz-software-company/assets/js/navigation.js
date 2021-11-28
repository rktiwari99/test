/* global LZSoftwareCompanyScreenReaderText */
/**
 * Theme functions file.
 *
 * Contains handlers for navigation and widget area.
 */

jQuery(function($){
 	"use strict";
   	jQuery('.main-menu-navigation > ul').superfish({
		delay:       500,
		animation:   {opacity:'show',height:'show'},  
		speed:       'fast'
   	});
});

function lz_software_company_open() {
	window.mobileMenu=true;
	document.getElementById("sidelong-menu").style.width = "100%";
}
function lz_software_company_close() {
	window.mobileMenu=false;
	document.getElementById("sidelong-menu").style.width = "0";
}
	window.currentfocus=null;
  	lz_software_company_checkfocusdElement();
	var body = document.querySelector('body');
	body.addEventListener('keyup', lz_software_company_check_tab_press);
	var gotoHome = false;
	var gotoClose = false;
	window.mobileMenu=false;
 	function lz_software_company_checkfocusdElement(){
	 	if(window.currentfocus=document.activeElement.className){
		 	window.currentfocus=document.activeElement.className;
	 	}
 	}
	function lz_software_company_check_tab_press(e) {
		"use strict";
		// pick passed event or global event object if passed one is empty
		e = e || event;
		var activeElement;

		if(window.innerWidth < 999){
		if (e.keyCode == 9) {
			if(window.mobileMenu){
			if (!e.shiftKey) {
				if(gotoHome) {
					jQuery( ".main-menu-navigation ul:first li:first a:first-child" ).focus();
				}
			}
			if (jQuery("a.closebtn.responsive-menu").is(":focus")) {
				gotoHome = true;
			} else {
				gotoHome = false;
			}

		}else{

			if(window.currentfocus=="mobiletoggle"){
				jQuery( "" ).focus();
			}
			}
		}
		}
		if (e.shiftKey && e.keyCode == 9) {
		if(window.innerWidth < 999){
			if(window.currentfocus=="header-search"){
				jQuery(".mobiletoggle").focus();
			}else{
				if(window.mobileMenu){
				if(gotoClose){
					jQuery("a.closebtn.responsive-menu").focus();
				}
				if (jQuery( ".main-menu-navigation ul:first li:first a:first-child" ).is(":focus")) {
					gotoClose = true;
				} else {
					gotoClose = false;
				}
			
			}else{

			if(window.mobileMenu){
			}
			}

			}
		}
		}
	 	lz_software_company_checkfocusdElement();
	}