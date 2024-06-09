<?php

require('vendor/autoload.php');

use QueryBuilder\QueryBuilderFactory;
use QueryBuilder\ConnectionConfigDTO\ConnectionConfig;

$configMySQL = new ConnectionConfig('mysql', '127.0.0.1', 'test', 'root', '');

$db = QueryBuilderFactory::create($configMySQL);
$result = $db->select(['id', 'name'])
             ->from('users')
             ->where('id', '>', 1)
             ->andWhere('name', 'LIKE', '%John%')
             ->orderBy('name', 'ASC')
             ->limit(2)
             ->get();
var_export($result);

//$configMongoDB = new ConnectionConfig('mongodb', '127.0.0.1', 'test', 'root', '', 'users');

//$db = QueryBuilderFactory::create($configMongoDB);
//$result = $db->select(['id', 'name'])
//             ->from('users')
//             ->where('id', '$gt', 1)
//             ->andWhere('name', '$regex', 'John')
//             ->orderBy('name', 'ASC')
//             ->limit(1)
//             ->get();
//var_export($result);