<?php
namespace QueryBuilder;

interface QueryBuilderInterface
{
    public function select($columns): self;
    public function from($table): self;
    public function where($column, $operator, $value): self;
    public function andWhere($column, $operator, $value): self;
    public function orWhere($column, $operator, $value): self;
    public function orderBy($column, $direction): self;
    public function limit($limit): self;
    public function insert($table, $data): self;
    public function update($table, $data): self;
    public function delete($table): self;
    public function join($table, $column1, $operator, $column2): self;
    public function createIndex($table, $columns, $options = []): self;
    public function execute();
    public function get();
}