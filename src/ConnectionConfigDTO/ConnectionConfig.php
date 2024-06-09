<?php

namespace QueryBuilder\ConnectionConfigDTO;

class ConnectionConfig
{
    public function __construct(
        public string $type,
        public string $host,
        public string $dbName,
        public string $username,
        public ?string $password = '',
        public ?string $collection = ''
    ) {}
}