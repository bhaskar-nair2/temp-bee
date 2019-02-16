<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;


class BranchTable extends AbstractTableGateway {

    protected $table = 'branches';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
}
?>
