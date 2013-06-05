<?php

class Models_ApiRecommendationManager extends App_Zenddb {

    public function getRecommendation($request_token, $limit, $offset, $source_type, $user_experience_id = FALSE, $type = FALSE) {
        $user = $this->checkUserByToken($request_token);
        if ($source_type == 1) {
            $rec_ids_query = 'SELECT rec_id FROM user_rec WHERE user_id = ' . $user['id_tbl_user'];
        } else {
            $link_ids = 'SELECT link_id FROM user_exp_link WHERE user_exp_id = ' . $user_experience_id;
            $tag_ids = 'SELECT tag_id FROM user_link_tags WHERE link_id IN(' . $link_ids . ')';
            $rec_ids_query = 'SELECT id FROM user_rec_tags WHERE user_id = ' . $user['id_tbl_user'] . ' AND tag_id IN (' . $tag_ids . ')';
        }
        $select = 'SELECT type, thumbnail, description, title, url, likes, splyse, dislikes ';
        $select .= 'FROM recommendations';
        $select .= ' WHERE id IN (' . $rec_ids_query . ')';
        if (!empty($type))
            $select .= ' AND type = ' . $type;
        $select .= ' LIMIT ' . $offset . ',' . $limit;
        $recom = $this->db->query($select);
        $result = $recom->fetchAll();

        return $result;
    }

    public function checkUserByToken($request_token) {
        $select1 = $this->db->select()
                ->from(array('u' => 'users'), array('u.id_tbl_user'))
                ->where('u.request_token = "' . $request_token . '"');
        $users = $this->db->fetchRow($select1);
        return $users;
    }

}