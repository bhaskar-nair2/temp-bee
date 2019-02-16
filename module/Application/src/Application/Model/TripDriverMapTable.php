<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;


class TripDriverMapTable extends AbstractTableGateway {

    protected $table = 'trip_driver_map';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
}
?>
