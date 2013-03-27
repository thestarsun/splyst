/**
 * Created with JetBrains PhpStorm.
 * User: vstymkovskyi
 * Date: 16.01.13
 * Time: 10:59
 * To change this template use File | Settings | File Templates.
 */
var temp_ajax = 1;
var temp_ajax_people = 1;
var temp_ajax_search = 1;

$(document).ready(function(){
    $('#manageExperienceDone').hide();
    $('#manageBookmarkDone').hide();
    $('#manageExperience').show();
    $('#manageBookmark').show();
    $('.delBookmark').each(function(){$(this).hide()});
    $('.delExperience').each(function(){$(this).hide()});
    $('.experience_block_title').find('h3').each(function(){$(this).addClass('title_pading')});
    $('.experience_block_title').find('h3').each(function(){$(this).removeClass('delExp')});
    $('#manageBookmark').click(function(){
        $('#manageBookmarkDone').show();
        $('#manageBookmark').hide();
        $('.delBookmark').each(function(){$(this).show()});
        $('.experience_block_title').find('h3').each(function(){$(this).addClass('delExp')});
        $('.experience_block_title').find('h3').each(function(){$(this).removeClass('title_pading')});
    });
    $('#manageBookmarkDone').click(function(){
        $('#manageBookmarkDone').hide();
        $('#manageBookmark').show();
        $('.delBookmark').each(function(){$(this).hide()});
        $('.experience_block_title').find('h3').each(function(){$(this).removeClass('delExp')});
        $('.experience_block_title').find('h3').each(function(){$(this).addClass('title_pading')});
    });
    $('#manageExperience').click(function(){
        $('#manageExperienceDone').show();
        $('#manageExperience').hide();
        $('.delExperience').each(function(){$(this).show()});
//        $('.experience_block_title').find('h3').each(function(){$(this).addClass('delExp')});
//        $('.experience_block_title').find('h3').each(function(){$(this).removeClass('title_pading')});
    });
    $('#manageExperienceDone').click(function(){
        $('#manageExperienceDone').hide();
        $('#manageExperience').show();
        $('.delExperience').each(function(){$(this).hide()});
//        $('.experience_block_title').find('h3').each(function(){$(this).removeClass('delExp')});
//        $('.experience_block_title').find('h3').each(function(){$(this).addClass('title_pading')});
    });
    $('.delBookmark').find('img').each(function(){
        $(this).click(function(){
            del_bok_id = $(this).parent().parent().parent().find('input.add_comm').attr('data');
            $('#id_exp_fordel').empty();
            $('#id_bookmark_fordel').empty().html(del_bok_id);
            $("#content_popup1").attr('style','min-height:80px;').html($('#delete_bookmark_popup').html());
            $("#splyst_overlay, #container_popup1").show();
            $("#content_popup1 .popup_comment_wrap").mCustomScrollbar({
                scrollInertia: 4
            });
        });
    })
    $('.delExperience').click(function(){
        del_exp_id = $(this).attr('exp_ID');
            $('#id_bookmark_fordel').empty();
            $('#id_exp_fordel').empty().html(del_exp_id);
            $("#content_popup1").attr('style','min-height:80px;').html($('#delete_bookmark_popup').html());
            $("#splyst_overlay, #container_popup1").show();
            $("#content_popup1 .popup_comment_wrap").mCustomScrollbar({
                scrollInertia: 4
            });
    });
    $('#delBookmarkBtn').live('click',function(){
        idBookmarkRemove = $('#id_bookmark_fordel').text()
        idExpRemove = $('#id_exp_fordel').text()
        $.post('/default/ajax/deletebookmark', {id_book:idBookmarkRemove, id_exp:idExpRemove}, function(resp){
            if(resp.success == 1)
                $('body').find('input.add_comm[data="'+idBookmarkRemove+'"]').parent().parent().parent().parent().remove();
            else if (resp.success == 2)
                $('body').find('.delExperience[exp_id="'+idExpRemove+'"]').parent().remove();
        }, 'json');
        close_content_popup1();
    })
})

function resize_bg(){
    jQuery(window).bind('load resize',function(){
        jQuery('#squeeze-bg').css('height','');
        var content_top = jQuery('#squeeze-bg').offset().top;
        var bottom_top = jQuery('#footer').offset().top;
        var height_block = bottom_top - content_top;
        jQuery('#squeeze-bg').css('height', height_block + 'px' );
    });
}

function show_popup(block, media_type, obj, id){
    if(block == 'recommendation_btn'){
        if(obj){
//            tex = $(obj).find('div.recommendation_title').text();
            $('#img_spl_btn').val($(obj).find('div.hide_info_tmb').text());
            $('#text_spl_btn').val($(obj).find('div.hide_info_text').text());
            $('#url_spl_btn').val($(obj).find('div.hide_info_url').text());
            $('#title_spl_btn').val($(obj).find('div.recommendation_title').text());
        }
    }else if(block == 'addLink'){
        jQuery.post('/default/ajax/cleandir/', {}, function(){
            jQuery("#url_title").focus();
        }, 'json');
    }else if(block == 'login'){
        jQuery("#login-email").focus();
    }else if(block == 'recommendation_btn'){
        jQuery("#addExp_button_title").focus();
    }else if(block == 'addExp'){
        jQuery("#addExp_title").focus();
    }else if(block == 'media'){
        jQuery("#media_form_block").addClass(media_type);
        if(media_type == 'image'){
            var img_height = jQuery("#media_block_content .original").height();
            img_height = img_height - 55;
            if( img_height < 600 || (img_height == 0) ) {
                jQuery("#media_block_content").css({"height":"auto", "width":"auto"});
            }else if(img_height > 850){
                jQuery("#media_block_content img").css("width", "600px");
            }else{
                jQuery("#media_block_content").css("height", "auto");
                jQuery("#media_block_content img").css("height", "auto");
            }
        }else{
            jQuery("#media_block_content").css("height", "auto");
        }
    }else if(block == 'share_btn'){
        $("#share_btn_form_block .block_loader").show();
        $.post('/default/share/getpopup', {id:id}, function(resp){
            $("#share_btn_form_block .block_loader").hide();
            $("#share_btn_block_content").empty().append(resp);
        });
    }else if(block == 'invite'){
        $("#friends_invite_names").tagit({
            availableTags: obj,
            autocomplete: {delay: 0, minLength: 1},
            fieldName: "friends[]",
            allowNewTags:false
        });
        $("#friends_invite_names").tagit("remove_my");
        $.each(obj, function(i,val){
            $("#friends_invite_names").tagit("createTag", val);
        });
    }
    jQuery("#splyst_overlay, #splyst_popup, #"+block+"_form_block").show();

    if(block == 'recommendation_btn'){
        if(!jQuery("#recommendation_exp_title .mCSB_container").length){
            jQuery("#recommendation_exp_title").mCustomScrollbar({
                scrollInertia: 4,
                advanced:{
                    updateOnContentResize: true,
                    autoExpandHorizontalScroll: true
                }
            });
        }
    }
}

function close_popup(){
    if(jQuery("#media_block_content").is(':visible')){
        jQuery("#media_form_block").removeClass().addClass("popup_form");
        jQuery("#media_block_content").empty();
    }else if(jQuery("#addLink_form_block").is(':visible')){
        jQuery("#scan_btn").show();
    }
    jQuery("#splyst_overlay").hide();
    jQuery("#splyst_popup").hide();
    jQuery(".popup_form").hide();
}


function popup_login(){
    var email = $('#login-email').val();
    var pass = $('#login-password').val();

    jQuery.post('/default/ajax/ajaxlogin', {email: email, password: pass}, function(resp){
        if(resp.success == 1){
            if(typeof $.cookie('outsidecontentid') != 'undefined' && typeof $.cookie('outsidecontentlink') != 'undefined'){
                window.location.assign('http://'+location.hostname+'/default/fblinks/'+$.cookie('outsidecontentlink')+'?id='+$.cookie('outsidecontentid'));
                $.removeCookie("outsidecontentlink");
                $.removeCookie("outsidecontentid");
            }else
                window.location.assign('http://'+location.hostname+'/default/recommendation/index');
        }else
            show_login_error(resp.error);
    }, 'json');
}

function forget_password(action){
    if(action == 'send'){
        jQuery('#forget_email').removeClass("error");
        var email = jQuery('#forget_email').val();
        if(ValidEmailAddress(email)){
            show_spiner();
            jQuery.post('/default/ajax/updatepass', {email: email}, function(resp){
                if(resp.success == 1){
                    jQuery("#pass_form_block").hide();
                    jQuery("#mess_from_block").html(resp.result).show();
                    setTimeout(function(){
                        jQuery("#splyst_overlay").hide();
                        jQuery("#splyst_popup").hide();
                        jQuery("#mess_from_block").hide().empty();
                    }, 3000);
                }else{
                    jQuery('#forget_email').addClass("error");
                    show_error("pass", resp.result);
                }

                show_spiner_close();
            }, 'json');
        }else{
            var error = 'Incorrect email address.';
            jQuery('#forget_email').addClass("error");
            show_error("pass", error);
        }
    }else{
        jQuery("#splyst_overlay, #splyst_popup, #pass_form_block").show();
        jQuery("#forget_email").focus();
    }
}

function popup_register(){
    jQuery("#regis-password, #confirm_password").removeClass("error");
    var pass = jQuery("#regis-password").val();
    var pass2 = jQuery("#confirm_password").val();
    if((pass != '') || (pass2 != '')){
        if(pass == pass2){
            show_spiner();
            var data = jQuery("#register-from").serialize();
            jQuery.post('/default/ajax/ajaxregister', {data: data, user_name: $('#regis-user_name').val()}, function(resp){
                if(resp.success == 1) popup_experience();
                else{
                    jQuery("#regis-password, #confirm_password").addClass("error");
                    show_error("register", resp.error);
                }

                show_spiner_close();
            }, 'json');
        }else{
            var error = "The passwords entered are not the same.";
            jQuery("#regis-password, #confirm_password").addClass("error");
            show_error("register", error);
        }
    }else{
        error = "Please enter a password.";
        jQuery("#regis-password, #confirm_password").addClass("error");
        show_error("register", error);
    }
}

function popup_experience(){
    show_spiner();
    jQuery.post('/default/ajax/ajaxexperience', function(resp){
        jQuery("#register_form_block").hide();
        jQuery("#experience-form").html(resp);
        jQuery("#experience_form_block").show();
        show_spiner_close();
    }, 'html');
}

function show_spiner(){
    jQuery("#splyst_popup").css("z-index",4);
    jQuery("#splyst_spiner").show();
}
function show_spiner_close(){
    jQuery("#splyst_spiner").hide();
    jQuery("#splyst_popup").css("z-index",6);
}

function show_media(el, type){
    var elem = $(el).children('.original').clone();
    jQuery("#media_block_title").empty().html(elem.attr("name"));
    jQuery("#media_block_content").html(elem);
    show_popup('media', type);
}

function show_error(div, error){
    jQuery("#"+div+"_form_block .erDiv .invalid").html(error);
    jQuery("#"+div+"_form_block .erDiv").show();
    jQuery('.error').mouseover(function(){
        jQuery(this).parent().next('.erDiv').show();
    });
    jQuery('.error').bind('mouseout focus', function(){
        jQuery(this).parent().next('.erDiv').hide();
    });
}

function ValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}

function show_login_error(error){
    jQuery("#login-email, #login-password").addClass("error");
    jQuery('#err-div_login').html(error).show();
    setTimeout(function(){
        jQuery('#err-div_login').hide().empty();
        jQuery("#login-email, #login-password").removeClass("error");
    }, 2000);
}

function recommendation_page(path, container){
    jQuery("#recommendation-content").mCustomScrollbar("disable");
    if(temp_ajax == 1){
        temp_ajax = 0;
        jQuery.post(path, function(resp){
            var $newItems = jQuery(resp);
            container.isotope( 'insert', $newItems );
            container.isotope('reLayout');
            show_recom_btns();
            show_recom();
            temp_ajax = 1;
        }, 'html');

    }
    jQuery("#recommendation-content").mCustomScrollbar("update");
}

function show_recom_btns(){
    jQuery(".recommendation-item").mouseenter(function(){
        jQuery('.recommendation-item_btns.item_btns_block').hide();
        jQuery(this).children('.recommendation-item_btns.item_btns_block').show();
    }).mouseleave(function(){
        jQuery(this).children('.recommendation-item_btns.item_btns_block').hide();
    });
}

function show_invite_btns(){
    jQuery(".invite-item").mouseenter(function(){
        jQuery(this).children('.invite-item_btns').show();
    }).mouseleave(function(){
        jQuery(this).children('.invite-item_btns').hide();
    });
}

function show_recom_btns_click(popups, id){
    var parent = jQuery("#recommendation_item_"+id);
    if(popups){
        close_popup();
    }
    show_popup('recommendation_btn', '', parent);
}

function show_recom(){
    $('.recommendation_li').bind("click", function(event){
        var type = jQuery(this).attr("name");
        if(event.target.className == 'recommendation_btns splyst'){
            return false;
        }else if(event.target.className == 'recommendation_btns share'){
            return false;
        }
        var elem = jQuery(this).find('.original').clone();
        if(type == 'news'){
            var news_url = elem.find('.news_more_btn').attr('href');
            var elem_title = '<a href="'+news_url+'">'+elem.attr("name")+'</a>';
        }else{
            var elem_title = elem.attr("name");
        }
        jQuery("#media_block_title").empty().html(elem_title);
        jQuery("#media_block_content").html(elem);
        show_popup('media', type);
    });
}

function recom_exp_title(){
    var $container = jQuery("#recommendation_exp_title");
    var $new_name = jQuery("#exp_title_0_name");
    $container.find('.exp_title_item.active').removeClass('active').children('.exp_title_active').hide();
    $new_name.hide().attr('value', '');
    jQuery(".exp_title_item").unbind('click');
    jQuery(".exp_title_item").each(function(i,val){
        jQuery(val).bind('click', function(){
            var current_el = $container.find('.exp_title_item.active');
            if(current_el.attr('id') && val.id == current_el.attr('id')){
                jQuery(val).removeClass('active').children('.exp_title_active').hide();
                if(val.id == 'exp_title_0')
                    $new_name.hide();
            }else{
                $container.find('.exp_title_item.active').removeClass('active').children('.exp_title_active').hide();
                $new_name.hide();
                if(val.id == 'exp_title_0'){
                    $new_name.show().focus();
                }
                jQuery(val).addClass('active').children('.exp_title_active').show();
            }
        });
    });
}

function saveSplystBtn(){
    show_spiner();
    var $container = jQuery("#recommendation_exp_title");
    var el = $container.find('.exp_title_item.active');
    var error = false;
    if(el.attr('id')){
        if(el.attr('id') == 'exp_title_0'){
            var experience_name = 'new';
            var title = $('#exp_title_0_name').val();
        }else{
            var temp_name = $(el).attr("id");
            var experience_name = temp_name.replace('exp_title_', '');
            var title = $('#title_spl_btn').val();
        }
        var url = $('#url_spl_btn').val();
        var text = $('#text_spl_btn').val();
        var img = $('#img_spl_btn').val();
    }else
        error = true;
    if(title != '' && !error){
        $.post('/default/ajax/savesplystbuttonlink', {
            'experience_name': experience_name,
            'title': title,
            'url': url,
            'text': text,
            'img': img
        }, function(resp){
            show_spiner_close();
            close_popup();
            if(resp.success == 'true'){
                if(!resp.new_experience.link){
                    var new_option = jQuery("#experienceTemplate").tmpl(resp.new_experience);
                    jQuery("#recommendation_exp_title .mCSB_container").append(new_option);
                    $container.mCustomScrollbar("update");
                }
            }
            recom_exp_title();
        }, 'json');
    }else{
        show_spiner_close();
        $("#recommendation_form_error").show();
        setTimeout(function(){
            $("#recommendation_form_error").hide();
        }, 2000);
    }
}

function handleImgError(obj, parent, recom){
    if(recom){
        var parent = jQuery(obj).parent().parent().parent();
        jQuery(parent).find("img").attr("src", "/img/error_image.png");
        jQuery(parent).find(".recommendation-item_btns").remove();
        jQuery(parent).unbind('click');
    }else{
        if(parent == 2){
            jQuery(obj).parent().parent().remove();
        }else if(parent == 3){
            jQuery(obj).parent().parent().parent().remove();
        }
    }
}

function share_content(popups, id){
    var parent = jQuery("#recommendation_item_"+id);
    if(popups){
        close_popup();
    }
    show_popup('share_btn', '', parent, id);
}

function see_collaborators(){
    jQuery("#see_collaborators").click(function(){
        jQuery("#collaborators-content").hide();
        if(!jQuery(this).hasClass('exist')){
            jQuery("#collaborators-content2, #block_loader").show();
            jQuery.post('/default/collaborator/index', {all: 1}, function(resp){
                jQuery("#see_collaborators").addClass("exist");
                jQuery("#block_loader").hide();
                jQuery("#collaborators-content2").append(resp);
            }, 'html');
        }else{
            jQuery("#collaborators-content2").show();
        }
        userIds = [];
        jQuery("#collaborators-content .collaborators_block_little").removeClass('checked');
        jQuery("#collaborators-content").find('.checked_box').hide();
    });
}

function load_collaborators(){
    jQuery("#collaborators_all").mCustomScrollbar('disable');
    if(temp_ajax_people == 1){
        temp_ajax_people = 0;
        jQuery.post("/default/collaborator/ajaxcoloborators",{type: "all"},function(resp){
            var users = jQuery.parseJSON(resp.data);
            var $newItems = jQuery("#userTemplate").tmpl(users);
            jQuery('#collaborators-scroll').isotope('insert', $newItems);
            temp_ajax_people = 1;
        }, 'json');
    }
    jQuery("#collaborators_all").mCustomScrollbar('update');
}

function hide_collaborators(){
    jQuery("#hide_collaborators").click(function(){
        jQuery("#collaborators-content2").hide();
        userIds = [];
        jQuery("#collaborators-content2 .collaborators_block_little").removeClass('checked');
        jQuery("#collaborators-content2").find('.checked_box').hide();
        jQuery("#collaborators-content").show();
    });
}

function collaborators_search(name){
    var $container = jQuery('#collaborators-scroll');
    var $container_scroll = jQuery("#collaborators_all");
    var search_val = false;
    if(name == ''){
        search_val = 'all';
    }else if(name.length > 1){
        search_val = name;
    }

    if(search_val){
        if(temp_ajax_search == 1){
            jQuery("#search_loader").show();
            temp_ajax_search = 0;
            //clean users
            $container.isotope('destroy').empty();
            $container_scroll.mCustomScrollbar("destroy");
            jQuery.post("/default/collaborator/ajaxcoloborators",{type: "search", search_val: search_val},function(resp){
                var $newItems = '';
                if(resp.success){
                    var users = jQuery.parseJSON(resp.data);
                    $newItems = jQuery("#userTemplate").tmpl(users);
                }
                jQuery('#collaborators-scroll').append($newItems);
                jQuery('#collaborators-scroll').isotope({
                    itemSelector : '.collaborators_block_little',
                    animationEngine: 'jquery',
                    containerStyle: {position: 'relative'},
                    layoutMode: 'fitColumns'
                });
                temp_ajax_search = 1;
            }, 'json');

            $container_scroll.mCustomScrollbar({
                scrollInertia: 4,
                horizontalScroll: true,
                advanced:{
                    updateOnContentResize: true,
                    autoExpandHorizontalScroll: true
                },
                callbacks:{
                    onTotalScroll: function(){
                        collaborators_search_result(search_val);
                    }
                }
            });
            jQuery("#search_loader").hide();
        }
    }
}
function collaborators_search_result(name){
    if(temp_ajax_search == 1){
        temp_ajax_search = 0;
        jQuery.post("/default/collaborator/ajaxcoloborators",{type: "search", search_val: name, scroll: 1},function(resp){
            if(resp.success){
                var users = jQuery.parseJSON(resp.data);
                var $newItems = jQuery("#userTemplate").tmpl(users);
                jQuery('#collaborators-scroll').isotope('insert', $newItems);
            }
            temp_ajax_search = 1;
        }, 'json');
    }
}


function collaborators_check_user(){
    jQuery(".collaborators_block_little").live("click", function(){
        to = $(this).attr("id").substring(3, $(this).attr("id").length);
        FB.ui({
            method: 'send',
            name: 'Join me on Splyst!',
            link: 'http://195.177.237.145/',
            picture: 'http://195.177.237.145/img/logo.png',
            caption: 'splyst.com',
            description: 'Here you can create your own experience and share it with friend. Also many interesting recommendation waiting for you.',
            to: to
        },function(response) {});
    });
}



function popup_content(){
    jQuery(".experience_popup").click(function(){
        var elem = jQuery(this).find('.original').clone();
        jQuery("#content_popup").html(elem);
        jQuery("#splyst_overlay, #container_popup").show();
        jQuery("#content_popup .popup_comment_wrap").mCustomScrollbar({
            scrollInertia: 4
        });
    });
}

function popup_link_share(){
    if(window.location.search.match(/link=.*/)){
        link_id = JSON.stringify(window.location.search.match(/link=.*/)).split('=')[1].split('"')[0];
        ths = $('input[data="'+link_id+'"]').parent().parent().parent().parent();
        $.post('/default/ajax/getcomments', {link_id: link_id}, function(resp){
            $(".popup_comment_wrap_ajax").empty().append(resp);
            var elem = $(ths).find('.original').clone();
            $("#content_popup").html(elem);
            $("#splyst_overlay, #container_popup").show();
            $("#content_popup .popup_comment_wrap").mCustomScrollbar({
                scrollInertia: 4
            });
        }, 'html');
    }
    $(".experience_popup").click(function(){
        if($('#manageBookmark').is(":visible") == true){
            ths = $(this);
            var link_id = $(this).find('.add_comm').attr('data');
            $.post('/default/ajax/getcomments', {link_id: link_id}, function(resp){
                $(".popup_comment_wrap_ajax").empty().append(resp);
                var elem = $(ths).find('.original').clone();
                $("#content_popup").html(elem);
                $("#splyst_overlay, #container_popup").show();
                $("#content_popup .popup_comment_wrap").mCustomScrollbar({
                    scrollInertia: 4
                });
            }, 'html');
        }
    });
    $('.experience_popup_link').click(function(){
        if($('#manageBookmark').is(":visible") == true){
            ths = $(this).parent();
            var link_id = $(ths).find('.add_comm').attr('data');
            $.post('/default/ajax/getcomments', {link_id: link_id}, function(resp){
                $(".popup_comment_wrap_ajax").empty().append(resp);
                var elem = $(ths).find('.original_rec').clone();
                $("#content_popup").html(elem);
                $("#splyst_overlay, #container_popup").show();
                $("#content_popup .popup_comment_wrap").mCustomScrollbar({
                    scrollInertia: 4
                });
            }, 'html');
        }
    })
}

function close_content_popup(){
    jQuery("#container_popup, #splyst_overlay").hide();
    jQuery("#content_popup").empty();
}
function close_content_popup1(){
    jQuery("#container_popup1, #splyst_overlay").hide();
    jQuery("#content_popup1").empty();
}

function collaborators_slides(id, resize){
    var width = jQuery(window).width();
    var elements = 0;
    var elem_width = 186;
    var items = 3;
    var items2 = 6;
    var container_width = 558;
    var container_width2 = 558;
    if(width > 1100 && width < 1300){
        items = 4;
        items2 = 8;
        container_width = 745;
        container_width2 = 834;
    }else if(width > 1300 && width < 1500){
        items = 5;
        items2 = 9;
        container_width = 931;
        container_width2 = 918;
    }else if(width > 1500){
        items = 5;
        items2 = 9;
        container_width = 931;
        container_width2 = 918;
    }

    if(id == "#collaborators_items"){
        elements = items;
    }else{
        jQuery("#collaborators_items2").css("width", container_width2);
        elements = items2;
        elem_width = 102;
    }
    var scroll = elements;

    if(resize){
        jQuery("#collaborators_items").css("width", container_width);
        jQuery("#collaborators_items2").css("width", container_width2);
    }
    jQuery(id).jcarousel({
        circular: true,
        visible: elements,
        scroll: scroll,
        animation: 'slow',
        itemFallbackDimension: elem_width,
        itemLastInCallback:{
            onBeforeAnimation: ajax_people
        }
    });
}

function ajax_people(carousel, obj, state){
    if(state == carousel.size()){
        if(carousel.container.attr("id") != 'collaborators_items2'){
            jQuery.post("/default/collaborator/ajaxcoloborators",{type: "col"},function(result){
                if(result.data){
                    var users = jQuery.parseJSON(result.data);
                    if(users[1]){
                        var output = '<li class="collaborators_block carousel">';
                        jQuery.each(users, function(i, val){
                            output += '<div class="collab_user">';
                                output += '<div class="collab_user_picture"><img src="'+val.picture.data.url+'" width="160px" height="160px" /></div>';
                                output += '<div class="collab_user_name">'+val.name+'</div>';
                            output += '</div>';
                            if( (i+1)%2 == 0 ){
                                output += '</li>';
                                var old_size = carousel.size();
                                var new_size = old_size + 1; // make room for new item
                                carousel.size(new_size);
                                carousel.add(new_size, output);
                                output = '';
                                if(users[i+1]){
                                    output += '<li class="collaborators_block carousel">';
                                }
                            }
                        });
                    }
                }
            },"json");
        }else{
            jQuery.post("/default/collaborator/ajaxcoloborators",{type: "nocol"},function(result){
                if(result.data){
                    var users = jQuery.parseJSON(result.data);
                    if(users[1]){
                        jQuery.each(users, function(i, val){
                            var output = '<li id="'+val.id+'" class="collaborators_block_little invite-item">';
                                output += '<div class = "hidden invite-item_btns">';
                                    output += '<a href="javascript:void(0)" class="invite_btns">Invite</a>';
                                output += '</div>';
                                output += '<div class="collab_user_little">';
                                    output += '<div class="collab_user_picture_little">';
                                        output += '<img src="'+val.picture.data.url+'" width="80px" height="85px" />';
                                    output += '</div>';
                                    output += '<div class="collab_user_name_little">'+val.name+'</div>';
                                output += '</div>';
                            output += '</li>';
                            var old_size = carousel.size();
                            var new_size = old_size + 1; // make room for new item
                            carousel.size(new_size);
                            carousel.add(new_size, output);
                        });
                    }
                }
            },"json");
        }
    }
}

function news_comment(){
    jQuery("#view_news_page_content").click(function(){
        if(!jQuery(this).hasClass('active')){
            jQuery(this).addClass('active');
            jQuery("#view_news_page_frame_content").removeClass('active');
            jQuery("#news_page_frame_content").hide();
            jQuery("#news_page_content").show();
        }
    });
    jQuery("#view_news_page_frame_content").click(function(){
        if(!jQuery(this).hasClass('disabled')){
            jQuery(this).addClass('active');
            jQuery("#view_news_page_content").removeClass('active');
            jQuery("#news_page_frame_content").show();
            jQuery("#news_page_content").hide();
        }
    });

    jQuery("#show_news_comment_li").click(function(){
        jQuery("#show_news_comment").trigger('click');
    });
    jQuery("#show_news_comment").toggle(function(){
        jQuery("#show_news_comment_li").toggleClass('active');
        jQuery('.show_news_comment').toggleClass('active');
        jQuery('#news_iframe_page').stop().animate({
            width: 1147
        });
        jQuery('#news_page_content').stop().animate({
            width: 892
        });
        jQuery('#news_comment_wrapper').stop().animate({
            width: 360
        });
        jQuery("#news_comments").removeClass('hidden');
    },function() {
        jQuery("#show_news_comment_li").toggleClass('active');
        jQuery('.show_news_comment').toggleClass('active');
        jQuery('#news_iframe_page').stop().animate({
            width: 1590
        });
        jQuery('#news_page_content').stop().animate({
            width: 1250
        });
        jQuery("#news_comment_wrapper").stop().animate({
            width: 0
        });

        jQuery("#news_comments").addClass('hidden');
    });
}

function landing_invite(){
    jQuery("#invite_request").click(function(){
        var email = jQuery("#invite-email").val();
        var error = 'Incorrect email address.';
        if(ValidEmailAddress(email)){
            jQuery.post("/default/ajax/inviteemail",{email: email},function(result){
                if(result.exist){
                    error = 'Your email has already added to database.';
                    jQuery("#landing_invite_error .error_line").empty().html(error);
                    jQuery("#landing_invite_error").show();
                }
                if(result.success){
                    jQuery("#landing_invite_success").show();
                    setTimeout(function(){
                        jQuery("#invite-email").val('');
                        jQuery("#landing_invite_success").hide();
                        close_popup();
                    }, 3000);
                }
            }, 'json');
        }else{
            jQuery("#landing_invite_error .error_line").empty().html(error);
            jQuery("#landing_invite_error").show();
        }
        setTimeout(function(){
            jQuery("#landing_invite_error").hide();
        }, 3000);
    });
}

function showCounter(){
    $.post('/default/recommendation/updatecounter', {}, function(resp){
        $('#recCounter').empty();
        if(resp.count != 0)
            $('#recCounter').append(resp.count).show();
        else
            $('#recCounter').hide();
    }, 'json');
}

function restartCounter(){
    $.post('/default/recommendation/refreshcounter', {}, function(){}, 'json');
}

function addNewRecommendations(){
    recommendation_page('/default/recommendation/updaterecomendations', jQuery("#recommendation_blocks"));
}

function getNotificationsData(){
    var $container = $("#notData");
    $.post("/default/ajax/getnotificationsdata",{}, function(result){
        if(result.success && result.new_messages != 0){
            $("#notification_count").html(result.new_messages).addClass('visible');
            if(!$(".notification_popup").hasClass("visible")){
                $("#notificationMessages").addClass("active");
            }
            if($container.hasClass('empty_notifications')){
                $container.removeClass('empty_notifications');
                $container.find('.mCSB_container').empty();
            }
            $container.find('.mCSB_container').html($("#notificationTemplate").tmpl(result.data));
            if(result.counter < 6 ){
                var current_height = $container.height();
                $container.css('height', current_height+(result.counter*60));
                $container.mCustomScrollbar('update');
            }
        }
    },"json");
}

function login() {
    FB.login(function(response) {
        show_spiner();
        if (response.authResponse) {
            $.post('/default/auth/checkregistration',
                {'accessToken':response.authResponse.accessToken,
                    'userID':response.authResponse.userID
                } , function(response){
                    show_spiner_close();
                    if(response.registration == 'yes'){
                        $("#regis-user_name").val(response.name);
                        $("#regis-email").val(response.email);
                        $("#regis-birthday").val(response.birthday);
                        $("#regis-user_id").val(response.id);
                        $("#regis-fb_id").val(response.fb_user_id);
                        if(response.gender == 1) $("#regis-male").attr('checked', 'checked');
                        else $("#regis-female").attr('checked', 'checked');

                        $('.error').mouseover(function(){
                            $(this).next('.erDiv').show();
                        });
                        $('.error').bind('mouseout focus', function(){
                            $(this).next('.erDiv').hide();
                        });
                        $("#regis-birthday").datepicker({
                            changeMonth: true,
                            changeYear: true,
                            yearRange: "1950:2012"
                        });

                        $("#close_popup, #login_form_block").hide();
                        $("#register_form_block").show();
                    }else
                        window.location.assign('/default/recommendation/index');
                }, 'json');
        }
    }, {scope: 'email, user_likes, read_friendlists, user_birthday'});
}

function logout(){
    $.post('/default/auth/logout', {}, function(response){
        if(response.success =='true'){
            $('#login_box').hide();
            $('#fb_login_btn').show();
            window.location.assign('/default/index/index');
        }
    }, 'json');
}
