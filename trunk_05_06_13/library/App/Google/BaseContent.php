<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Base_Content
 *
 * @author thestarsun
 */
class App_Google_BaseContent {
    
    public static function factory($name) {
        switch ($name) {
            case "images":
                $object = new \App_Google_Images();
                break;
            case "videos":
                $object = new \App_Google_Videos();
                break;
            case "wikis":
                $object = new \App_Google_Wikis();
                break;
            case "news":
                $object = new \App_Google_News();
                break;
            default:
                $object = false;
                break;
        }
        return $object;
    }
    
}

?>
