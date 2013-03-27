//function addExpirience(){
//    $('#addExp').show();
//}

function saveExp(){
    if($('input[name=title]').val()!=""){
        $.post('/default/ajax/experience', {title:$('input[name=title]').val()}, function(resp){
            if(resp.success == 'true')
                window.location.assign('/default/dashboard/index/');

        }, 'json')
    }
}
function scan(){
    $('#url_error>span').text('');
    $('#url_error').hide();
    var url_link = $('#url_link').val();
    if(url_link != ''){
        $('#splyst_popup').show();
        if( (url_link.search('http://') != -1) || (url_link.search('https://') != -1) ){
            show_spiner();
            //       $.mobile.loading('show',{text:'Scaning site...',textVisible:true});
            $.post('/default/ajax/scanimg', {url:$('#url_link').val()}, function(resp){
                $('#scan_btn').after(resp);
                $('#scan_btn').hide();
                $('#splyst_popup').show();
//           $.mobile.loading('hide');
                show_spiner_close();
            }, 'html').error(function() {
                    $('#url_error>span').text('The url is not correct!');
                    $('#url_error').show();
            });
        }else{
            $('#url_error>span').text('The url is not correct!');
            $('#url_error').show();
        }
    }else{
        $('#url_error>span').text('Fill both fields please!');
        $('#url_error').show();
    }
}

//function getscreenshot(){
//    console.log('getscreenshot');
//    $('#scr_btn').hide();
//    show_spiner();
////    $.mobile.loading('show',{text:'Generating Screenshot...',textVisible:true});
//    $.get('http://www.uglymongrel.com/takeScreenshot.php','url='+$('#url_link').val(),function(data){
//        console.log('go');
//        $('#img_scr_src').attr('src',data.fileUrl);
//        $('#img_scr_div').show();
//
//        show_spiner_close();
//    },'jsonp');
//
//}

function savedefexpajax(){
    $('#category-form').submit();
}

function getContent(type, amount, linkID){
    $('#experience_'+type+linkID+'_content .more_link').hide();
    $('#experience_'+type+linkID+'_content .block_loader').show();

    $.post('/default/link/content',{amount:amount, id:linkID, type: type}, function(resp){
        $('#experience_'+type+linkID+'_content .block_loader').hide();
        $('#experience_'+type+linkID+'_content').empty().append(resp);
    },'html');
//    if(type == 'img'){
//        $.post('/default/link/images',{amount:amount, id:linkID}, function(resp){
//            $('#experience_images'+linkID+'_content').empty().append(resp);
//        },'html');
//    }else if(type == 'video'){
//        $.post('/default/link/videos',{amount:amount, id:linkID}, function(resp){
//            $('#experience_video'+linkID+'_content').empty().append(resp);
//        },'html');
//    }else if(type == 'news'){
//        $.post('/default/link/news',{amount:amount, id:linkID}, function(resp){
//            $('#experience_news'+linkID+'_content').empty().append(resp);
//        },'html');
//    }else if(type == 'wiki'){
//		$('#experience_wiki'+linkID+'_content').empty().append('<img width="48" height="48" src="/img/wiki-loader.gif" style="margin-left: 85px;">');
//        $.post('/default/link/wiki',{amount:amount, id:linkID}, function(resp){
//            $('#experience_wiki'+linkID+'_content').empty().append(resp);
//        },'html');
//    }
}

$(document).ready(function(){
   $('#url_title, #url_link').focus(function(){
       $('#url_error').hide();
   });
   $('#save_exp_link').live('click', function(){
       show_spiner();
       $.post('/default/ajax/savelink', 
       {title:$('#url_title').val(), 
           url:$('#url_link').val(), 
           img:$('input[name=img_link]:checked').next().children().attr('src'),
           comment:$('#comment').val(),
           id:window.location.search.split('=')[1]},
       function(result){
           if(result.success == 'true')
               window.location.reload();

           show_spiner_close();
       }, 'json')
   });
})

