<?php
namespace QueryBuilder\Builders;

use QueryBuilder\QueryBuilderInterface;
use QueryBuilder\ConnectionConfigDTO\ConnectionConfig;
use MongoDB\Client as Client;
use Exception;

class MongoQueryBuilder implements QueryBuilderInterface
{
    private Client $client;
    private $collection;
    private array $query = [];
    private array $options = [];
    private string $dbName;

    public function __construct(ConnectionConfig $config)
    {
        $this->client = new Client("mongodb://{$config->host}", [
            'username' => $config->username,
            'password' => $config->password,
            'db' => $config->dbName
        ]);
        $this->collection = $this->client->{$config->dbName}->{$config->collection};
        $this->dbName = $config->dbName;
    }

    public function select($columns = []): MongoQueryBuilder
    {
        if (!empty($columns)) {
            $this->options['projection'] = array_fill_keys($columns, 1);
        }
        return $this;
    }

    public function from($table): MongoQueryBuilder
    {
        $this->collection = $this->client->selectCollection($this->dbName, $table);
        return $this;
    }

    public function where($column, $operator, $value): MongoQueryBuilder
    {
        $this->query[$column] = [$operator => $value];
        return $this;
    }

    public function andWhere($column, $operator, $value): MongoQueryBuilder
    {
        if (!isset($this->query['$and'])) {
            $this->query['$and'] = [];
        }
        $this->query['$and'][] = [$column => [$operator => $value]];
        return $this;
    }

    public function orWhere($column, $operator, $value): MongoQueryBuilder
    {
        if (!isset($this->query['$or'])) {
            $this->query['$or'] = [];
        }
        $this->query['$or'][] = [$column => [$operator => $value]];
        return $this;
    }

    public function orderBy($column, $direction = 'ASC'): MongoQueryBuilder
    {
        $this->options['sort'] = [$column => ($direction === 'ASC' ? 1 : -1)];
        return $this;
    }

    public function limit($limit): MongoQueryBuilder
    {
        $this->options['limit'] = $limit;
        return $this;
    }

    public function insert($table, $data): MongoQueryBuilder
    {
        $this->collection->insertOne($data);
        return $this;
    }

    public function update($table, $data): MongoQueryBuilder
    {
        $this->collection->updateMany($this->query, ['$set' => $data]);
        return $this;
    }

    public function delete($table): MongoQueryBuilder
    {
        $this->collection->deleteMany($this->query);
        return $this;
    }

    public function join($table, $column1, $operator, $column2): MongoQueryBuilder {
        throw new Exception("Join operation is not supported for MongoDB.");
        return $this;
    }

    public function createIndex($table, $columns, $options = []): MongoQueryBuilder
    {
        $keys = [];
        foreach ($columns as $column => $order) {
            $keys[$column] = ($order === 'ASC') ? 1 : -1;
        }
        $this->collection->createIndex($keys, $options);
        return $this;
    }

    public function execute()
    {
        // Not applicable for MongoDB as operations are executed immediately
        return true;
    }

    public function get()
    {
        return $this->collection->find($this->query, $this->options)->toArray();
    }
}