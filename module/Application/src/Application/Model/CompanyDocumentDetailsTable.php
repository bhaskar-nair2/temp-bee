<?php
namespace Application\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Session\Container;
use Zend\Db\Sql\Expression;
use Application\Service\CommonService;


class CompanyDocumentDetailsTable extends AbstractTableGateway {

    protected $table = 'company_document_details';

    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }
    
    
}
?>
