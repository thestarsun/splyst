/**
 * Created with JetBrains PhpStorm.
 * User: vstymkovskyi
 * Date: 22.01.13
 * Time: 10:42
 * To change this template use File | Settings | File Templates.
 */

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