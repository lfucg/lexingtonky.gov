jQuery.noConflict();
jQuery(document).ready(function(){
jQuery(".toggle_1").click(function(){
jQuery(".socialbtn").toggle();
});
jQuery(".socialbtn a").click(function(){
jQuery(this).parent(".socialbtn").css("display","none");
});	
})

