<?php
namespace QueryBuilder\Builders;

use QueryBuilder\QueryBuilderInterface;
use QueryBuilder\ConnectionConfigDTO\ConnectionConfig;
use PDO;
use Exception;

class RelationalQueryBuilder implements QueryBuilderInterface
{
    protected PDO $pdo;
    protected string $query;
    protected array $bindings = [];
    protected string $whereClause = '';
    protected string $orderByClause = '';
    protected string $limitClause = '';

    public function __construct(ConnectionConfig $config) {
        $dsn = $this->getDsn($config);
        $this->pdo = new PDO($dsn, $config->username, $config->password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * @param ConnectionConfig $config
     * @return string
     * @throws Exception
     */
    private function getDsn(ConnectionConfig $config): string {
        switch ($config->type) {
            case 'mysql':
            case 'mariadb':
                return "mysql:host={$config->host};dbname={$config->dbName}";
            case 'pgsql':
                return "pgsql:host={$config->host};dbname={$config->dbName}";
            case 'sqlite':
                return "sqlite:{$config->dbName}";
            case 'sqlsrv':
                return "sqlsrv:Server={$config->host};Database={$config->dbName}";
            case 'oracle':
                return "oci:dbname={$config->host}/{$config->dbName}";
            default:
                throw new Exception("Unsupported database type: {$config->type}");
        }
    }

    public function select($columns = '*'): RelationalQueryBuilder
    {
        $columns = is_array($columns) ? implode(', ', $columns) : $columns;
        $this->query = "SELECT $columns";
        return $this;
    }

    public function from($table): RelationalQueryBuilder
    {
        $this->query .= " FROM $table";
        return $this;
    }

    public function where($column, $operator, $value): RelationalQueryBuilder
    {
        $this->whereClause = " WHERE $column $operator ?";
        $this->bindings[] = $value;
        return $this;
    }

    public function andWhere($column, $operator, $value): RelationalQueryBuilder
    {
        if (empty($this->whereClause)) {
            $this->where($column, $operator, $value);
        } else {
            $this->whereClause .= " AND $column $operator ?";
            $this->bindings[] = $value;
        }
        return $this;
    }

    public function orWhere($column, $operator, $value): RelationalQueryBuilder
    {
        if (empty($this->whereClause)) {
            $this->where($column, $operator, $value);
        } else {
            $this->whereClause .= " OR $column $operator ?";
            $this->bindings[] = $value;
        }
        return $this;
    }

    public function orderBy($column, $direction = 'ASC'): RelationalQueryBuilder
    {
        $this->orderByClause = " ORDER BY $column $direction";
        return $this;
    }

    public function limit($limit): RelationalQueryBuilder
    {
        $this->limitClause .= " LIMIT $limit";
        return $this;
    }

    public function insert($table, $data): RelationalQueryBuilder
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->bindings = array_values($data);
        return $this;
    }

    public function update($table, $data): RelationalQueryBuilder
    {
        $setClause = implode(', ', array_map(function($key) {
            return "$key = ?";
        }, array_keys($data)));
        $this->query = "UPDATE $table SET $setClause" . $this->whereClause;
        $this->bindings = array_merge(array_values($data), $this->bindings);
        return $this;
    }

    public function delete($table): RelationalQueryBuilder
    {
        $this->query = "DELETE FROM $table" . $this->whereClause;
        return $this;
    }

    public function join($table, $column1, $operator, $column2): RelationalQueryBuilder
    {
        $this->query .= " JOIN $table ON $column1 $operator $column2";
        return $this;
    }

    public function createIndex($table, $columns, $options = []): RelationalQueryBuilder
    {
        $columns = is_array($columns) ? implode(', ', $columns) : $columns;
        $indexName = isset($options['name']) ? $options['name'] : "idx_" . implode('_', $columns);
        $this->query = "CREATE INDEX $indexName ON $table ($columns)";
        return $this;
    }

    public function execute(): bool
    {
        $this->query .= $this->whereClause . $this->orderByClause . $this->limitClause;
        $stmt = $this->pdo->prepare($this->query);
        return $stmt->execute($this->bindings);
    }

    public function get(): ?array
    {
        $this->query .= $this->whereClause . $this->orderByClause . $this->limitClause;
        $stmt = $this->pdo->prepare($this->query);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}