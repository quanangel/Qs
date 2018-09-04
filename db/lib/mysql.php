<?php 

namespace Qs\db\lib;

use Qs\db\lib\base;

class mysql extends base {


    public function __construct($options = []) {
        parent::__construct(['sqlname'=>'mysql']);
    }

}