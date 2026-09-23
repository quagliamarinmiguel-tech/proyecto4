console.log('[CEPROCA][main.js] archivo cargado');

$(document).ready(function(){
	console.log('[CEPROCA][main.js] DOM listo', {
		submenus: $('.nav-btn-submenu').length,
		navLateralButtons: $('.show-nav-lateral').length,
		navLateral: $('.nav-lateral').length,
		pageContent: $('.page-content').length
	});

	/*  Show/Hidden Submenus */
	$('.nav-btn-submenu').on('click', function(e){
		e.preventDefault();
		var SubMenu=$(this).next('ul');
		var iconBtn=$(this).children('.fa-chevron-down');
		console.log('[CEPROCA][main.js] clic en submenu', {
			texto: $(this).text().trim(),
			submenuEncontrado: SubMenu.length,
			iconoEncontrado: iconBtn.length
		});
		if(SubMenu.hasClass('show-nav-lateral-submenu')){
			$(this).removeClass('active');
			iconBtn.removeClass('fa-rotate-180');
			SubMenu.removeClass('show-nav-lateral-submenu');
		}else{
			$(this).addClass('active');
			iconBtn.addClass('fa-rotate-180');
			SubMenu.addClass('show-nav-lateral-submenu');
		}
	});

	/*  Show/Hidden Nav Lateral */
	$('.show-nav-lateral').on('click', function(e){
		e.preventDefault();
		var NavLateral=$('.nav-lateral');
		var PageConten=$('.page-content');
		console.log('[CEPROCA][main.js] clic en mostrar nav lateral', {
			navLateralEncontrado: NavLateral.length,
			pageContentEncontrado: PageConten.length
		});
		if(NavLateral.hasClass('active')){
			NavLateral.removeClass('active');
			PageConten.removeClass('active');
		}else{
			NavLateral.addClass('active');
			PageConten.addClass('active');
		}
	});
    
});
(function($){
    $(window).on("load",function(){
        console.log('[CEPROCA][main.js] ventana cargada', {
			jqueryDisponible: typeof $ === 'function',
			scrollbarDisponible: typeof $.fn.mCustomScrollbar === 'function'
		});

        if (typeof $.fn.mCustomScrollbar !== 'function') {
			console.error('[CEPROCA][main.js] falta jquery.mCustomScrollbar');
			return;
		}

        $(".nav-lateral-content").mCustomScrollbar({
        	theme:"light-thin",
        	scrollbarPosition: "inside",
        	autoHideScrollbar: true,
        	scrollButtons: {enable: true}
        });
        $(".page-content").mCustomScrollbar({
        	theme:"dark-thin",
        	scrollbarPosition: "inside",
        	autoHideScrollbar: true,
        	scrollButtons: {enable: true}
        });
		console.log('[CEPROCA][main.js] scrollbars inicializados');
    });
})(jQuery);

$(function(){
	console.log('[CEPROCA][main.js] popovers encontrados', $('[data-toggle="popover"]').length);
	if (typeof $.fn.popover === 'function') {
		$('[data-toggle="popover"]').popover();
	} else {
		console.error('[CEPROCA][main.js] falta Bootstrap popover');
	}
});