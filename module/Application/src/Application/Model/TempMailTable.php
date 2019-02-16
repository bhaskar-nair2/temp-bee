<?php

namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Sql\Sql;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class TempMailTable extends AbstractTableGateway {

    protected $table = 'temp_mail';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }

    public function insertTempMailDetails($to,$subject,$message,$fromMail,$fromName) {
        $data = array(
            'message' => $message,
            'from_mail' => $fromMail,
            'to_email' => $to,
            'subject' => $subject,
            'from_full_name' => $fromName
        );
        $this->insert($data);
        return $this->lastInsertValue;
    }
    
    public function deleteTempMail($id){
        $this->delete(array('temp_id = '.$id));
    }
    
    public function updateTempMailStatus($id){
        
        $data = array('status' => 'not-sent');
        $this->update($data,array('temp_id='.$id));
    }
}
