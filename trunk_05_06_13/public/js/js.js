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
var temp_recommendation_item = null;
var check_recommendation = false;

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
            $("#splyse_recom_id").val($(obj).attr('data'));            
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
    jQuery(".invite-item").live('mouseenter', function(){
        jQuery(this).children('.invite-item_btns').show();
    }).live('mouseleave', function(){
        jQuery(this).children('.invite-item_btns').hide();
    });
}

function show_recom_btns_click(popups, id){
    var parent = jQuery("#recommendation_item_"+id);
    temp_recommendation_item = parent;
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
        }else if(event.target.className == 'recommendation_title_likeIcons upLike'){
            return false;
        }else if(event.target.className == 'recommendation_title_likeIcons downLike'){
            return false;
        }else if(event.target.className == 'recommendation_title_likeIcons upLike_dark'){
            return false;
        }else if(event.target.className == 'recommendation_title_likeIcons downLike_dark'){
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
            if(temp_recommendation_item){
                var title = temp_recommendation_item.find('.recommendation_title').text();
                temp_recommendation_item = null;
            }else
                var title = $(el).find('.exp_title_item_name').text();
        }
        var url = $('#url_spl_btn').val();
        var text = $('#text_spl_btn').val();
        var img = $('#img_spl_btn').val();
        var recom_id = $("#splyse_recom_id").val();
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
        $.post('/default/ajax/updatesplysecount',{'recom_id': recom_id}, function(resp){
            if(resp.success){
                $("#recommendation_item_"+recom_id).find(".info_counters.first").text(resp.splyses+' splyses');
                if(resp.likes != 0) $("#recommendation_item_"+recom_id).find('.info_counters_delimeter').removeClass('hidden');
            }
        });
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
    $(".invite_btns.fb").live("click", function(){
        to = $(this).parent().parent().attr("id").substring(3, $(this).parent().parent().attr("id").length);
        FB.ui({
            method: 'send',
            name: 'Join me on Splyst!',
            link: 'http://195.177.237.145/',
            picture: 'http://195.177.237.145/img/logo.png',
            caption: 'splyst.com',
            description: 'Here you can create your own experience and share it with friend.',
            to: to
        },function(response) {});
    });
    $(".invite_btns.sp").live("click", function(){
        to = $(this).parent().parent().attr("id").substring(3, $(this).parent().parent().attr("id").length);
        show_popup('invite_to_splyst_fr');
    });
    $('#invite_to_splyst_fr').live('click', function(){
        $.post('/default/collaborator/sendinvite', {to:to}, function(){
            close_popup();
        }, 'json');
    })
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

function popup_link(){
    if(window.location.search.match(/link_id=.*/)){
        link_id = JSON.stringify(window.location.search.match(/link_id=.*/)).split('=')[1].split('"')[0];
        $.post('/default/ajax/getlink', {link_id: link_id}, function(resp){
            resp = JSON.parse(resp);
            if(resp.par == 0){
                $("#left-sidebar, #footer").hide();
                $('#conteiner').css({
                'background': 'url("/img/anonimus_screenshot.png") no-repeat left 65px',
                'min-height':$('#conteiner').height()+80+'px'
                 });
                $("#conteiner").css('padding', 0);
                $('.top_panel').show();
                $('#container_popup').css({'top':'130px'});;                              
            }
            $("#content_popup").html(resp.html);
            $("#splyst_overlay, #container_popup").show();
            $("#content_popup .popup_comment_wrap").mCustomScrollbar({
                scrollInertia: 4
            });
            getComments();
            addComments();
        }, 'html');
    }
}

function rightPopupPanel(milk){
        $('.hide_comment').hide();
        $('.show_comment').show();
        $('.show_comm_btn').live('click',function(){
            if(milk == 0){
                $('.hide_comment').show();
                $(this).removeClass('active');
                $('.show_comment').removeClass('active').hide();
                $('.experience_popup_right').hide('Clip');
                milk = 1
            }else{
                $('.hide_comment').hide();
                $(this).addClass('active');
                $('.show_comment').show();
                $('.experience_popup_right').show('Clip');
                milk = 0;
            }
        });        
}

function addComments(){
    $('.add_comm').live('click', function(){
            data = $(this).attr('data');
            ths = $(this).parent().parent();
            $(this).parent().parent().find('textarea[name=comment_text]').each(function(){
                commemt = $(this).val();
            });
            if(commemt != ''){
                $.post('/default/ajax/addcomment',{link_id:data,text:commemt}, function(resp){
                    
                    $(ths).find(".popup_comment_wrap_ajax").empty().append(resp);
                    $(".popup_comment_wrap").mCustomScrollbar({
                        scrollInertia: 4,
                        advanced:{
                            updateOnContentResize: true
                        }
                    });
                    $(".popup_comment_wrap").mCustomScrollbar("scrollTo","bottom");
                }, 'html');
            }
        });
}

function getComments(){
        link_id = JSON.stringify(window.location.search.match(/link_id=.*/)).split('=')[1].split('"')[0];
        $.post('/default/ajax/getcommentslink', {link_id: link_id}, function(resp){
            resp = JSON.parse(resp);
            if(resp.panel == 'off'){
                $('.experience_popup_right').hide();
                $('.show_comm_btn').removeClass('active');
                rightPopupPanel(1);
            } else rightPopupPanel(0);
            $(".popup_comment_wrap_ajax").empty().append(resp.html);             
            $("#splyst_overlay, #container_popup").show();
            $("#content_popup .popup_comment_wrap").mCustomScrollbar({
                scrollInertia: 4
            });
        }, 'html');     
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
    });
    $('.experience_popup_link').click(function(){
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
    var comment_width = 360;
    var news_content_width = window.innerWidth-70;
    var temp_news_content_width = false;
    jQuery("#news_page_wrapper").css('width', news_content_width);
    jQuery(window).bind('resize',function(){
        news_content_width = window.innerWidth-70;
        if(jQuery("#show_news_comment").hasClass('active')){
            news_content_width = window.innerWidth-70-comment_width;
            temp_news_content_width = window.innerWidth-70;
            jQuery("#news_page_wrapper").css('width', news_content_width);
        }else{
            temp_news_content_width = false;
            jQuery("#news_page_wrapper").css('width', news_content_width);
        }
    });
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
        if(temp_news_content_width)
            news_content_width = temp_news_content_width;

        jQuery("#show_news_comment_li").toggleClass('active');
        jQuery(this).toggleClass('active');
        jQuery('#news_page_wrapper').stop().animate({
            width: news_content_width - comment_width
        });
        jQuery('#news_comment_wrapper').stop().animate({
            width: comment_width
        });
        jQuery("#news_comments").removeClass('hidden');
    },function() {
        if(temp_news_content_width)
            news_content_width = temp_news_content_width;

        jQuery("#show_news_comment_li").toggleClass('active');
        jQuery(this).toggleClass('active');
        jQuery('#news_page_wrapper').stop().animate({
            width: news_content_width
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
                    error = 'Your email has already been added to our system.';
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

function guest_invate(){
        $('#request_guest').click(function(){
            $('#error_guest').hide();
            if($('#guest-email').val() == '')
                $('#error_guest').empty().append('Fill the email field!').show();
            else if(ValidEmailAddress($('#guest-email').val())){
                $.post('/default/mail/invite', {'email':$('#guest-email').val()}, function(){}, 'json');
                close_popup();
                show_popup('guest_resp');
                setTimeout(" close_popup()", 4000);
            }else{
                $('#error_guest').empty().append('Incorrect email address.').show();
            }
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
            $container.css('height', result.counter*60);
            $container.find('.mCSB_container').css('height', result.counter*60);
            $container.mCustomScrollbar('update');
        }else if(result.success && !result.data[0]){
            $container.find('.mCSB_container').empty().html('You have no notifications.').css('height', '18px');
            $container.addClass('empty_notifications');
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
            window.location.assign('/default/index/invite');
        }
    }, 'json');
}

function manage_experience(conteiner_id, parent_class){
    $('.delBookmark').click(function(){
        del_bok_id = $(this).closest('.'+parent_class).find('input.add_comm').attr('data');
        $('#id_exp_fordel').empty();
        $('#id_bookmark_fordel').empty().html(del_bok_id);
        show_popup('delete_bookmark');
    });
    $('.delExperience').click(function(){
        del_exp_id = $(this).attr('exp_ID');
        $('#id_bookmark_fordel').empty();
        $('#id_exp_fordel').empty().html(del_exp_id);
        show_popup('delete_bookmark');
    });
    $('#delBookmarkBtn').live('click',function(){
        idBookmarkRemove = $('#id_bookmark_fordel').text();
        idExpRemove = $('#id_exp_fordel').text();
        $.post('/default/ajax/deletebookmark', {id_book:idBookmarkRemove, id_exp:idExpRemove}, function(resp){
            if(resp.success == 1){
                var parent = $('body').find('input.add_comm[data="'+idBookmarkRemove+'"]').closest('.'+parent_class);
                $("#"+conteiner_id).isotope('remove', parent);
            }else if (resp.success == 2)
                $('body').find('.delExperience[exp_id="'+idExpRemove+'"]').closest('.dashboard-item').remove();
        }, 'json');
        jQuery("#close_popup").trigger('click');
    });
}

function manage_experience_settings(item_class){
    jQuery("."+item_class).mouseenter(function(){
        jQuery(this).find('.experience_setting_wrapper').show();
    }).mouseleave(function(){
        jQuery(this).find('.experience_setting_wrapper').hide();
        if(jQuery(this).find('.contextual-links-trigger').hasClass('active')){
            jQuery(this).find('.contextual-links-trigger').trigger('click');
        }
    });

    jQuery(".contextual-links-trigger").toggle(function(){
        jQuery(this).parent().parent().unbind('click');
        jQuery(this).toggleClass('active').parent().toggleClass('active');
        jQuery(this).next().slideToggle('fast');
    }, function(){
        jQuery(this).toggleClass('active').parent().toggleClass('active');
        jQuery(this).next().slideToggle('fast');
        jQuery(this).parent().parent().bind('click', function(){
            var elem = jQuery(this).find('.original').clone();
            jQuery("#content_popup").html(elem);
            jQuery("#splyst_overlay, #container_popup").show();
            jQuery("#content_popup .popup_comment_wrap").mCustomScrollbar({
                scrollInertia: 4
            });
        });
    });
}

function notification_actions(){
    $('#confirm_invite').live('click', function(){
        $.post('/default/collaborator/confirmfriends', {data: $('#confirm_invite').attr('data-val')}, function(){
            close_popup();
        }, 'json');
    });
    $('#reject_invite').live('click', function(){
        $.post('/default/collaborator/rejectfriends', {data: $('#confirm_invite').attr('data-val')}, function(){
            close_popup();
        }, 'json');
    });

    jQuery(".notification_list_a").live('click', function(event){
        event.preventDefault();
        var href= jQuery(this).attr('href');
        var data = jQuery(this).attr('data-val');
        if(!jQuery(this).hasClass('confirm_invite')){
            $.post('/default/ajax/deletenotification', {data:data}, function(resp){
                if(resp.success){
                    jQuery(this).parent().remove();
                    window.location.assign(href);
                }
            }, 'json');
        }else{
            var data = data.split(',');
            $('#friend_name').text(data[2]);
            $('#confirm_invite').attr('data-val', data);
            show_popup('confirm_invite');
        }
    });
}


function confirm_invite(name, id){
    $('#friend_name').text(name);
    $('#confirm_invite').attr('userId', id);
    $(this).parent().remove();
    show_popup('confirm_invite');
}

function recommendation_thumbs(el, action, id, tags){
//    if(!jQuery(el).hasClass('active')){
        var parent = jQuery(el).parent();
        var recom = jQuery('#recommendation_item_'+id);
        jQuery.post('/default/ajax/recommendationthumbs', {thumbs_action: action, id: id, tags: tags}, function(resp){
            if(resp.success){
                if(action == 'down'){
                    $("#recommendation_blocks").isotope('remove', jQuery("#recommendation_item_"+id).remove());
                }   
                    recom.find(".upLike").remove();                    
                    recom.find(".downLike").remove();
                    recom.find(".upLike_dark").remove();
                    recom.find(".downLike_dark").remove();
                    parent.find(".recommendation_btns.thumbs_down").remove();
                    parent.find(".recommendation_btns.thumbs_up").remove();
                    jQuery("#recommendation_item_"+id).find(".recommendation_btns.thumbs_up").remove();
                    jQuery("#recommendation_item_"+id).find(".recommendation_btns.thumbs_down").remove();
                    if((resp.likes != 0) && (resp.dislikes != 0)){
                        parent.parent().find('.info_counters_delimeterLikes').removeClass('hidden');
                        jQuery("#recommendation_item_"+id).find('.info_counters_delimeterLikes').removeClass('hidden');
                    }
                    if(resp.likes != 0){
                        parent.parent().find(".info_counters.last").text(resp.likes+' likes');
                        jQuery("#recommendation_item_"+id).find(".info_counters.last").text(resp.likes+' likes');
                    }
                    if(resp.dislikes != 0){
                        parent.parent().find(".info_counters.lastDislike").text(resp.dislikes+' dislikes');
                        jQuery("#recommendation_item_"+id).find(".info_counters.lastDislike").text(resp.dislikes+' dislikes');
                    }                
                    if(resp.splyses != 0){
                        parent.parent().find('.info_counters_delimeter').removeClass('hidden');
                        jQuery("#recommendation_item_"+id).find('.info_counters_delimeter').removeClass('hidden');
                    }
            }
        }, 'json');
//    }
}

function check_recommendations(){
    setTimeout(function(){
        if(!check_recommendation){
            jQuery.post('/default/ajax/checkrecommendations', {}, function(resp){
                if(resp.success){
                    check_recommendation = true;
                    window.location.reload();
                };
            });
        }
    }, 2000);
}