<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Zend\Config\Writer\PhpArray;
use Application\Model\EventLogTable;
use Application\Service\CommonService;

class CymExtraTariffDetailsTable extends AbstractTableGateway {

    protected $table = 'cym_extra_tariff_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
}
?>
