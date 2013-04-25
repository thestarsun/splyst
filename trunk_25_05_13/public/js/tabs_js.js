/**
 * Created with JetBrains PhpStorm.
 * User: vstymkovskyi
 * Date: 22.01.13
 * Time: 10:42
 * To change this template use File | Settings | File Templates.
 */

function experience_tab_big(){
    jQuery('.experience_tab').click(function(){
        elem = $(this);
        var click_id = elem.attr('id').split('_');
        var exp_id = elem.attr('data-id');
        var id = click_id[1];
        if(!elem.hasClass('already_exist')){
            elem.addClass('already_exist');
            $.post('/default/dashboard/ajaxcontent',{type:id, exp_id: exp_id}, function(resp){
                var contents = jQuery.parseJSON(resp.data);
                var $newItems = jQuery("#contentTemplate_"+resp.type).tmpl(contents);
                jQuery('#tabs_experience_content').isotope('insert', $newItems);
                jQuery('#tabs_experience_content').isotope('reLayout');
            }, 'json');
        }
        jQuery('#tabs_experience_content').isotope({ filter: '.'+id});
        jQuery('.experience_tab').removeClass('active');
        elem.addClass('active');
    });
}
function experience_tab(coneiner){
    jQuery('#'+coneiner+' .experience_tab').click(function(){
        var click_id = jQuery(this).attr('id');
        if(click_id != jQuery('#'+coneiner+' .experience_tab.active').attr('id')){
            jQuery('#'+coneiner+' .experience_tab').removeClass('active');
            jQuery(this).addClass('active');
            jQuery('#'+coneiner+' .tabs_experience_content').removeClass('active');
            jQuery('#'+click_id+'_content').addClass('active');
        }
    });
}
