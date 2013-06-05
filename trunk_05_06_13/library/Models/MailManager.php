<?php

class Models_MailManager extends App_Zenddb {
    
    public function getEmails(){
        $select = $this->db->select()
                ->from(array('t' => 'invite_emails'))
                ->order('id desc');
        $result = $this->db->fetchAll($select);
        return $result;
    }
    
    public function deleteEmail($id){
        $this->db->delete('invite_emails', 'id ='.$id);
    }
    public function setSendMail($id){
        $this->db->update('invite_emails', array('sent' =>'1'),'id = '.$id );
    }
    
    public function getUserByEmail($email){
        $select = $this->db->select()
                ->from(array('t' => 'invite_emails'))
                ->where('email ="'.$email.'" and sent = 1');
        return $this->db->fetchOne($select);
    }
    public function updateEmail($id, $val){
        $this->db->update('invite_emails', array('sent'=> $val), 'id = '. $id);
    }
    public function addEmail($val){
        $this->db->insert('invite_emails', array('email' =>$val));
    }
    public function getEmail($email){
        $select = $this->db->select()
                ->from(array('t' => 'invite_emails'), array('id'))
                ->where('email = "'. $email.'"');
        return $this->db->fetchOne($select);
    }
    
    public function check_inv_email($email){
        $select = $this->db->select()
            ->from(array('t' => 'invite_emails'))
            ->where('t.email = ?', $email);
        $result = $this->db->fetchRow($select);

        return $result;
    }

    public function save_inv_email($email){
        $data = array("email"=>$email);
        $this->db->insert('invite_emails', $data);

        return $this->db->lastInsertId();
    }
        
}
