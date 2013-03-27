<?php
/**
 * Created by JetBrains PhpStorm.
 * User: vstymkovskyi
 * Date: 13.02.13
 * Time: 11:25
 * To change this template use File | Settings | File Templates.
 */
class Zend_View_Helper_Recommendation extends Zend_View_Helper_Placeholder_Container_Standalone {

    public function Recommendation($array){
        $output = '';
        if(!empty($array)){
            //create temp array width picture or video
            $tempNews  = array();
            foreach($array as $key=>$item){
                if($item['type'] != 1)
                    $tempNews[$key] = $item;
            }
            foreach($array as $key=>&$item){
                if($key == 0){
                    if( ($item['type'] == 1) && !empty($tempNews) ){
                        $tempItem = $item;
                        $keys = array_keys($tempNews);
                        $item = $tempNews[$keys[0]];
                        $array[$keys[0]] = $tempItem;
                    }
                }elseif($key == 5){
                    if( ($item['type'] == 1) && !empty($tempNews) ){
                        $tempItem = $item;
                        $keys = array_keys($tempNews);
                        $item = $tempNews[$keys[1]];
                        $array[$keys[1]] = $tempItem;
                    }
                }
            }
            //creating output
            foreach($array as $key=>&$item){
                $class = '';
                if($key == 0 || $key == 5){
                    $class = ' big_size';
                }elseif(($key == 1) || ($key == 2) || ($key == 8) || ($key == 9)){
                    $class = ' little_size';
                }

                $type = 'news';
                if($item['type'] == 2){
                    $type = 'image';
                }elseif($item['type'] == 3){
                    $type = 'video';
                }
                $output .= $this->create_recommendation($item, $type, $class);
            }
        }

        return $output;
    }

    private function create_recommendation($recommendation, $type, $class){
        $output = '';
        $output .= '<li id="recommendation_item_'.$recommendation['id'].'" data ='.$recommendation['id'].' class="recommendation_li" name="'.$type.'">';
            if($type =='news'&& !empty($recommendation['thumbnail'])):
                $output .= '<div class="recommendation-item images">';
            else:
                $output .= '<div class="recommendation-item '.$type.$class.'">';
            endif;
            if($type =='news'&& empty($recommendation['thumbnail'])):
                $output .= '';
            else:
                $output .= '<div class="recommendation-item_title">';
                    $output .= '<div class="recommendation_title">'.$recommendation['title'].'</div>';
                    if($type == 'image') $type = 'picture';
                    $output .= '<span class="recommendation_title_type">'.ucfirst($type).'</span>';
                $output .= '</div>';
            endif;

            if ($recommendation['type'] == 1):
                $output .= '<div class="hide_info_tmb hidden">'.$recommendation['thumbnail'].'</div>';
                $output .= '<div class="hide_info_text hidden">'.$recommendation['description'] . '</div>';
                $output .= '<div class="hide_info_url hidden">'.$recommendation['url'].'</div>';
                $output .= '<div class="original" name="'.$recommendation['title'].'">';
                    if (!empty($recommendation['thumbnail'])):
                        $output .= '<img id="media_block_content_img" src="'.$recommendation['thumbnail'].'" class="media_block_content_img"  />';
                    endif;
                        $output .= $recommendation['description'];
                    $output .= '<div class ="news_more_btn_div">';
                        $output .= '<a class="news_more_btn" href="/default/link/news_page?id='.$recommendation['id'].'" target="_blank"> See more ...</a>';
                    $output .= '</div>';
                    $output .= '<div class="recommendation-item_btns visible">';
                        $output .= '<a href="javascript:void(0)" class="recommendation_btns splyst" onclick="show_recom_btns_click(1,'.$recommendation['id'].')">Splyse</a>';
                        $output .= '<a href="javascript:void(0)" class="recommendation_btns share" onclick="share_content(1, '.$recommendation['id'].')">Share</a>';
                    $output .= '</div>';
                $output .= '</div>';
                if (!empty($recommendation['thumbnail'])):
                    $output .= '<div class="recommendation-item_content">';
                        $output .= '<img src="'.$recommendation['thumbnail'].'" onerror="this.src=\'/img/error_image.png\'" />';
                else:
                    $output .= '<div class="recommendation-item_content news_without_pic">';
                    $output .= $recommendation['title'];
                    $output .= '<span class="recommendation_title_type">'.ucfirst($type).'</span>';
                endif;
                $output .= '</div>';
            elseif ($recommendation['type'] == 2):
                $output .= '<div class="hide_info_tmb hidden">'.$recommendation['url'].'</div>';
                $output .= '<div class="hide_info_text hidden"></div>';
                $output .= '<div class="hide_info_url hidden"></div>';
                $output .= '<div class="original" name="'.$recommendation['title'].'">';
                    $output .= '<img src="'.$recommendation['url'].'" onerror="this.src=\'/img/error_image.png\'" />';
                    $output .= '<div class="recommendation-item_btns visible">';
                        $output .= '<a href="javascript:void(0)" class="recommendation_btns splyst" onclick="show_recom_btns_click(1,'.$recommendation['id'].')">Splyse</a>';
                        $output .= '<a href="javascript:void(0)" class="recommendation_btns share" onclick="share_content(1, '.$recommendation['id'].')">Share</a>';
                    $output .= '</div>';
                $output .= '</div>';
                $output .= '<div class="recommendation-item_content"><img src="'.$recommendation['url'].'" onerror="this.src=\'/img/error_image.png\'" /></div>';
            else:
                $output .= '<div class="hide_info_text hidden"></div>';
                $output .= '<div class="hide_info_url hidden"></div>';
                $output .= '<div class="hide_info_tmb hidden">'.$recommendation['thumbnail'].'</div>';
                $output .= '<div class="original" name="'.$recommendation['title'].'">';
                    $output .= $this->create_video($recommendation['url']);
                    $output .= '<div class="recommendation-item_btns visible">';
                        $output .= '<a href="javascript:void(0)" class="recommendation_btns splyst" onclick="show_recom_btns_click(1,'.$recommendation['id'].')">Splyse</a>';
                        $output .= '<a href="javascript:void(0)" class="recommendation_btns share" onclick="share_content(1, '.$recommendation['id'].')">Share</a>';
                    $output .= '</div>';
                $output .= '</div>';
                $output .= '<div class="recommendation-item_content">';
                    $output .= '<span class="recommendation_video_play"></span>';
                    $output .= '<img src="'.$recommendation['thumbnail'].'" />';
                $output .= '</div>';
            endif;
                $output .= '<div class="recommendation-item_btns item_btns_block">';
                    $output .= '<a href="javascript:void(0)" class="recommendation_btns splyst" onclick="show_recom_btns_click(0,'.$recommendation['id'].')">Splyse</a>';
                    $output .= '<a href="javascript:void(0)" class="recommendation_btns share" onclick="share_content(0, '.$recommendation['id'].')">Share</a>';
                $output .= '</div>';
            $output .= '</div>';
        $output .= '</li>';

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
