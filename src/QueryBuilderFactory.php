<?php
namespace QueryBuilder;

use QueryBuilder\Builders\RelationalQueryBuilder;
use QueryBuilder\Builders\MongoQueryBuilder;
use QueryBuilder\ConnectionConfigDTO\ConnectionConfig;
use Exception;

class QueryBuilderFactory
{
    /**
     * @param ConnectionConfig $config
     * @return QueryBuilderInterface
     * @throws Exception
     */
    public static function create(ConnectionConfig $config): QueryBuilderInterface
    {
        switch ($config->type) {
            case 'mysql':
            case 'pgsql':
            case 'mariadb':
            case 'sqlite':
            case 'sqlsrv':
            case 'oracle':
                return new RelationalQueryBuilder($config);
            case 'mongodb':
                return new MongoQueryBuilder($config);
            // Тут можно добавлять поддержку других базы данных
            default:
                throw new Exception("Unsupported database type");
        }
    }
}