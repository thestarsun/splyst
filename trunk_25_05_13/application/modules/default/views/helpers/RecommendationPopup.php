<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vstymkovskyi
 * Date: 13.02.13
 * Time: 11:25
 * To change this template use File | Settings | File Templates.
 */
class Zend_View_Helper_RecommendationPopup extends Zend_View_Helper_Placeholder_Container_Standalone {

    public function Recommendation($array){
        $output = '';        
        
        $output .= $this->create_recommendation($array);
        $arr['html'] = $output;
        $arr['par'] = 0;
        if(!empty($_SESSION['user_id'])) $arr['par'] = 1;
        return json_encode($arr);
    }

    private function create_recommendation($recommendation){
        $output = '';
        $output .= '<div class="original">';
        $output .= '<h3 class="experience_popup_title">'.$recommendation['title'].'</h3>';
        $output .= '<div class="experience_popup_left" style="position: relative;">';
        
            if (($recommendation['old_rec'] == 1) || empty($recommendation['old_rec'])):
                    if (!empty($recommendation['thumbnail'])):
                        $output .= '<img class="images" alt="" src="'.$recommendation['thumbnail'].'">';
                    endif;
                        $output .= $recommendation['description'];
                    $output .= '<div class ="news_more_btn_div">';
                        $output .= '<a class="news_more_btn" href="/default/fblinks/news?id='.$recommendation['id'].'" target="_blank"> See more ...</a>';
                    $output .= '</div>';
                    $output .= '<div class="show_comm_btn active"></div>';
            elseif ($recommendation['old_rec'] == 2 || empty($recommendation['old_rec'])):
                    $output .= '<img class="images" alt="" src="'.$recommendation['url'].'">';
                    $output .= '<div class="show_comm_btn active"></div>';
//                    $output .= '<div class="experience-item_btns hide_comment" style="width: 100%; text-align: center;">';
//                        $output .= '<a href="javascript:void(0)" class="experience_btns resplyst" onclick="show_recom_btns_click(1,'.$recommendation['id'].')">Splyse</a>';
//                        $output .= '<a href="javascript:void(0)" class="experience_btns share" onclick="share_content(1, '.$recommendation['id'].')">Share</a>';
//                    $output .= '</div>';
               
            elseif ($recommendation['old_rec'] == 3):
                    $output .= $this->create_video($recommendation['url']);
                    $output .= '<div class="show_comm_btn active"></div>';
//                    $output .= '<div class="experience-item_btns hide_comment" style="width: 100%; text-align: center;">';
//                        $output .= '<a href="javascript:void(0)" class="experience_btns resplyst" onclick="show_recom_btns_click(1,'.$recommendation['id'].')">Splyse</a>';
//                        $output .= '<a href="javascript:void(0)" class="experience_btns share" onclick="share_content(1, '.$recommendation['id'].')">Share</a>';
//                    $output .= '</div>';
            endif;
        
        $output .= '</div>';
//        $output .= '<div style="width: 100%; text-align: center;" class="experience-item_btns hide_comment">';
//            $output .= '<a href="javascript:void(0)" class="experience_btns resplyst" onclick="show_recom_btns_click(1,'.$recommendation['id'].')">Splyse</a>';
//            $output .= '<a href="javascript:void(0)" class="experience_btns share" onclick="share_content(1, '.$recommendation['id'].')">Share</a>';
//        $output .= '</div>';
        
        $output .= '<div class="experience_popup_right">';
//            $output .= '<h3 class="experience_popup_title show_comment" style="display: none;">'.$recommendation['title'].'</h3>';
            $output .= '<div class="popup_comment_wrap_ajax">';
            $output .= '</div>';
            if(!empty($_SESSION['user_id'])):
                $output .= '<div class="popup_form form-item" style="margin: 5px;">';
                    $output .= '<img src="'.$_SESSION['user_pic'].'" class = "comment_img"/>';
                    $output .= '<textarea name="comment_text" class="form-textarea" placeholder="Comment"></textarea>';
                $output .= '</div>';

//            $output .= '<div class="experience-item_btns show_comment" style="display: none;">';
//                $output .= '<a href="javascript:void(0)" class="experience_btns resplyst" onclick="show_recom_btns_click(1,'.$recommendation['id'].')">Splyse</a>';
//                $output .= '<a href="javascript:void(0)" class="experience_btns share" onclick="share_content(1, '.$recommendation['id'].')">Share</a>';
//            $output .= '</div>';
                $output .= '<div  class="">';
                    $output .= '<input class="add_comm" type="button" onclick="scan()" value="Comment" data="'.$recommendation['id'].'">';
                $output .= '</div>';
            endif;
        $output .= '</div>';
            
        $output .= '</div>';

        return $output;
    }

    private function create_video($data){
        $output = '<object width="516" height="315">';
            $output .= '<param name="movie" value="http://www.youtube.com/v/'.$data.'?amp;version=3" />';
            $output .= '<param name="allowFullScreen" value="true" />';
            $output .= '<param name="allowscriptaccess" value="always" />';
            $output .= '<embed src="http://www.youtube.com/v/'.$data.'?version=3" type="application/x-shockwave-flash" width="516" height="315" allowscriptaccess="always" allowfullscreen="true"></embed>';
        $output .= '</object>';

        return $output;
    }

}
