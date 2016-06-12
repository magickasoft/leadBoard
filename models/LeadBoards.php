<?php

use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Message;
use Phalcon\Mvc\Model\Validator\Uniqueness;
use Phalcon\Mvc\Model\Validator\InclusionIn;

class LeadBoards extends Model
{
	public $id;

    public $name;

    public $place;

    public $score;

    public $avatar;
	
	public function getSource() {
        return 'leadboards';
    }
	
	public function initialize() {
        
    }
    public function validation() {

    }
}
?>