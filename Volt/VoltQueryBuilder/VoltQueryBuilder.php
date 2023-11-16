<?php

declare(strict_types=1);

/**
 * Library Name: Voltage
 * Author: Celio Natti
 * Version: 1.0.0
 * Year: 2023
 */

namespace celionatti\Voltage\VoltQueryBuilder;

use PDO;
use PDOException;
use celionatti\Voltage\Exceptions\VoltException;

/**
 * ==============================================
 * ==================           =================
 * VoltQueryBuilder Class
 * ==================           =================
 * ==============================================
 */

class VoltQueryBuilder
{
    const STEP_INITIAL = 'initial';
    const STEP_RAW = 'raw';
    const STEP_SELECT = 'select';
    const STEP_WHERE = 'where';
    const STEP_INSERT = 'insert';
    const STEP_UPDATE = 'update';
    const STEP_DELETE = 'delete';
    const STEP_LIMIT = 'limit';
    const STEP_ORDER = 'order';
    const STEP_GROUP = 'group';
    const STEP_OFFSET = 'offset';
    const STEP_JOIN = 'join';
    const STEP_COUNT = 'count';
    const STEP_DISTINCT = 'distinct';
    const STEP_TRUNCATE = 'truncate';
    const STEP_UNION = 'union';
    const STEP_ALIAS = 'alias';
    const STEP_BETWEEN = 'between';
    const STEP_HAVING = 'having';

    protected $connection;
    protected string $table;
    protected $query;
    protected $bindValues = [];
    protected $joinClauses = [];
    private $currentStep = self::STEP_INITIAL;

    public function __construct($connection, string $table)
    {
        if (empty($table)) {
            throw new VoltException('Table name must not be empty.');
        }

        $this->connection = $connection;
        $this->table = $table;
    }

    /**
     * Validate the current step of the query building process.
     *
     * @param array $allowedSteps Allowed steps for the method.
     * @throws VoltException If the current step is not allowed.
     */
    private function validateStep(array $allowedSteps)
    {
        if (!in_array($this->currentStep, $allowedSteps, true)) {
            throw new VoltException("Invalid method order. {$this->currentStep} should come after " . implode(', ', $allowedSteps));
        }
    }

    /**
     * Select columns for the query.
     *
     * @param string|array $columns The columns to select.
     * @return $this
     * @throws \Exception If called in an invalid method order.
     * @throws \InvalidArgumentException If $columns is invalid.
     */
    public function select($columns = '*')
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_RAW]);

        if (!is_array($columns) && !is_string($columns)) {
            throw new \InvalidArgumentException('Invalid argument for SELECT method. Columns must be an array or a comma-separated string.');
        }

        if (is_array($columns)) {
            $columns = implode(', ', $columns);
        }

        $this->query = "SELECT $columns FROM $this->table";
        $this->currentStep = self::STEP_SELECT;

        return $this;
    }

    public function insert(array $data)
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_WHERE, self::STEP_RAW]);

        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid argument for INSERT method. Data array must not be empty.');
        }

        $columns = implode(', ', array_keys($data));
        $values = ':' . implode(', :', array_keys($data));

        $this->query = "INSERT INTO $this->table ($columns) VALUES ($values)";
        $this->bindValues = $data;
        $this->currentStep = self::STEP_INSERT;

        return $this;
    }

    public function update(array $data)
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_WHERE, self::STEP_RAW, self::STEP_SELECT]);

        if (empty($data)) {
            throw new \InvalidArgumentException('Invalid argument for UPDATE method. Data array must not be empty.');
        }

        $set = [];
        foreach ($data as $column => $value) {
            if (!is_string($column) || empty($column)) {
                throw new \InvalidArgumentException('Invalid argument for UPDATE method. Column names must be non-empty strings.');
            }

            $set[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query = "UPDATE $this->table SET " . implode(', ', $set);
        $this->currentStep = self::STEP_UPDATE;

        return $this;
    }

    public function delete()
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_WHERE, self::STEP_RAW, self::STEP_SELECT, self::STEP_LIMIT]);

        $this->query = "DELETE FROM $this->table";
        $this->currentStep = self::STEP_DELETE;

        return $this;
    }

    public function where(array $conditions)
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_RAW, self::STEP_SELECT, self::STEP_UPDATE, self::STEP_DELETE]);

        if (empty($conditions)) {
            throw new \InvalidArgumentException('Invalid argument for WHERE method. Conditions array must not be empty.');
        }

        $where = [];
        foreach ($conditions as $column => $value) {
            if (!is_string($column) || empty($column)) {
                throw new \InvalidArgumentException('Invalid argument for WHERE method. Column names must be non-empty strings.');
            }

            $where[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query .= " WHERE " . implode(' AND ', $where);
        $this->currentStep = self::STEP_WHERE;

        return $this;
    }


    public function orderBy($column, $direction = 'ASC')
    {
        $this->validateStep([self::STEP_RAW, self::STEP_WHERE, self::STEP_SELECT, self::STEP_UPDATE, self::STEP_DELETE, self::STEP_JOIN, self::STEP_GROUP, self::STEP_LIMIT]);

        if (!is_string($column) || empty($column)) {
            throw new \InvalidArgumentException('Invalid argument for ORDER BY method. Column name must be a non-empty string.');
        }

        $this->query .= " ORDER BY $column $direction";
        $this->currentStep = self::STEP_ORDER;

        return $this;
    }

    public function groupBy($column)
    {
        $this->validateStep([self::STEP_RAW, self::STEP_SELECT, self::STEP_WHERE, self::STEP_UPDATE, self::STEP_JOIN, self::STEP_GROUP, self::STEP_LIMIT]);

        if ($this->currentStep !== 'select' && $this->currentStep !== 'where' && $this->currentStep !== 'order' && $this->currentStep !== 'raw') {
            throw new \Exception('Invalid method order. GROUP BY should come after SELECT, WHERE, ORDER BY, or a previous GROUP BY.');
        }

        if (!is_string($column) || empty($column)) {
            throw new \InvalidArgumentException('Invalid argument for GROUP BY method. Column name must be a non-empty string.');
        }

        $this->query .= "GROUP BY $column";
        $this->currentStep = self::STEP_GROUP;

        return $this;
    }

    public function limit($limit)
    {
        $this->validateStep([self::STEP_RAW, self::STEP_SELECT, self::STEP_WHERE, self::STEP_JOIN, self::STEP_GROUP, self::STEP_LIMIT, self::STEP_ORDER]);

        if (!is_numeric($limit) || $limit < 1) {
            throw new \InvalidArgumentException('Invalid argument for LIMIT method. Limit must be a positive numeric value.');
        }

        $this->query .= " LIMIT $limit";
        $this->currentStep = self::STEP_LIMIT;

        return $this;
    }

    public function offset($offset)
    {
        $this->validateStep([self::STEP_RAW, self::STEP_SELECT, self::STEP_WHERE, self::STEP_JOIN, self::STEP_GROUP, self::STEP_LIMIT, self::STEP_ORDER, self::STEP_OFFSET]);

        if (!is_numeric($offset) || $offset < 0) {
            throw new \InvalidArgumentException('Invalid argument for OFFSET method. Offset must be a non-negative numeric value.');
        }

        $this->query .= " OFFSET $offset";
        $this->currentStep = self::STEP_OFFSET;

        return $this;
    }

    /**
     * Execute the query and return the number of affected rows.
     *
     * @return int Number of affected rows.
     * @throws VoltException If a database error occurs.
     */
    public function execute(): int
    {
        try {
            $stm = $this->executeQuery();

            return $stm->rowCount();
        } catch (PDOException $e) {
            throw new VoltException('Error executing query: ' . $e->getMessage());
        }
    }

    public function get($data_type = 'object')
    {
        try {
            $stm = $this->executeQuery();

            if ($data_type === 'object') {
                return $stm->fetchAll(PDO::FETCH_OBJ);
            } elseif ($data_type === 'assoc') {
                return $stm->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return $stm->fetchAll(PDO::FETCH_CLASS);
            }
        } catch (PDOException $e) {
            // Handle database error, e.g., log or throw an exception
            throw new VoltException($e->getMessage());
        }
    }

    protected function executeQuery()
    {
        try {
            $this->query = $this->query . '' . implode(' ', $this->joinClauses);
            $stm = $this->connection->prepare($this->query);

            foreach ($this->bindValues as $param => $value) {
                $stm->bindValue($param, $value);
            }

            $stm->execute();

            return $stm;
        } catch (PDOException $e) {
            // Handle database error, e.g., log or throw an exception
            throw new VoltException($e->getMessage());
        }
    }

    /**
     * Get the generated SQL query.
     *
     * @return string The SQL query.
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    public function join($table, $onClause, $type = 'INNER')
    {
        $this->validateStep([self::STEP_RAW, self::STEP_SELECT, self::STEP_WHERE, self::STEP_JOIN, self::STEP_GROUP, self::STEP_LIMIT, self::STEP_ORDER, self::STEP_COUNT]);

        if (!is_string($table) || empty($table)) {
            throw new VoltException('Invalid argument for JOIN method. Table name must be a non-empty string.');
        }

        if (!is_string($onClause) || empty($onClause)) {
            throw new VoltException('Invalid argument for JOIN method. ON clause must be a non-empty string.');
        }

        if ($type !== 'INNER' && $type !== 'LEFT' && $type !== 'RIGHT' && $type !== 'OUTER') {
            throw new VoltException('Invalid argument for JOIN method. Invalid join type.');
        }

        if (!is_string($table) || !is_string($onClause)) {
            throw new VoltException('Invalid arguments for JOIN method.');
        }

        $this->joinClauses[] = "$type JOIN $table ON $onClause";

        $this->currentStep = self::STEP_JOIN;

        return $this;
    }

    public function leftJoin($table, $onClause)
    {
        return $this->join($table, $onClause, 'LEFT');
    }

    public function rightJoin($table, $onClause)
    {
        return $this->join($table, $onClause, 'RIGHT');
    }

    public function outerJoin($table, $onClause)
    {
        return $this->join($table, $onClause, 'OUTER');
    }

    public function count()
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_RAW, self::STEP_SELECT, self::STEP_WHERE]);

        $this->query = "SELECT COUNT(*) AS count FROM $this->table";
        $this->currentStep = self::STEP_COUNT;

        return $this;
    }

    public function distinct($columns = '*')
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_RAW]);

        if (!is_array($columns)) {
            $columns = [$columns];
        }

        $columns = implode(', ', $columns);
        $this->query = "SELECT DISTINCT $columns FROM $this->table";
        $this->currentStep = self::STEP_DISTINCT;

        return $this;
    }

    public function truncate()
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_RAW]);

        $this->query = "TRUNCATE TABLE $this->table";
        $this->currentStep = self::STEP_TRUNCATE;

        return $this;
    }

    public function union(VoltQueryBuilder ...$queries)
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_RAW]);

        // Store the current query and reset it
        $currentQuery = $this->query;
        $this->query = '';

        $queryStrings = [$currentQuery];
        foreach ($queries as $query) {
            $queryStrings[] = $query->query; // Assuming your query property is called "query"
        }

        $this->query = implode(' UNION ', $queryStrings);
        $this->currentStep = self::STEP_UNION;

        return $this;
    }


    public function rawQuery(string $sql, array $bindValues = [])
    {
        $this->currentStep = self::STEP_INITIAL;

        $this->validateStep([self::STEP_INITIAL, self::STEP_RAW]);
        if ($this->currentStep !== 'initial' && $this->currentStep !== 'raw') {
            throw new VoltException('Invalid method order. Raw query should come before other query building methods.');
        }

        $this->query = $sql;
        $this->bindValues = $bindValues;
        $this->currentStep = self::STEP_RAW;

        return $this;
    }

    public function alias(string $alias)
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_RAW]);

        if ($this->currentStep === 'initial') {
            throw new VoltException('Invalid method order. Alias should come after other query building methods.');
        }

        $this->query .= " AS $alias";

        $this->currentStep = self::STEP_ALIAS;

        return $this;
    }

    public function subquery(VoltQueryBuilder $subquery, string $alias)
    {
        $this->validateStep([self::STEP_INITIAL, self::STEP_RAW]);

        $this->query .= ' (' . $subquery->getQuery() . ') AS ' . $alias;

        $this->currentStep = self::STEP_INITIAL;

        return $this;
    }

    public function between(string $column, $value1, $value2)
    {
        $this->validateStep([self::STEP_SELECT, self::STEP_WHERE, self::STEP_BETWEEN]);

        $this->query .= " AND $column BETWEEN :value1 AND :value2";
        $this->bindValues[':value1'] = $value1;
        $this->bindValues[':value2'] = $value2;

        $this->currentStep = self::STEP_BETWEEN;

        return $this;
    }

    public function having(array $conditions)
    {
        $this->validateStep([self::STEP_GROUP]);

        $having = [];
        foreach ($conditions as $column => $value) {
            $having[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query .= " HAVING " . implode(' AND ', $having);

        $this->currentStep = self::STEP_HAVING;

        return $this;
    }

    /** More */

    /**
     * Specify the join conditions using an array.
     *
     * @param array $joins Associative array of join conditions.
     *                    Example: ['users' => 'posts.user_id = users.id', 'categories' => 'posts.category_id = categories.id']
     * @param string $type Join type (INNER, LEFT, RIGHT, OUTER).
     * @return $this
     * @throws VoltException If called in an invalid method order.
     */
    public function multiJoin(array $joins, string $type = 'INNER')
    {
        $this->validateStep([self::STEP_SELECT, self::STEP_WHERE, self::STEP_ORDER, self::STEP_GROUP, self::STEP_JOIN, self::STEP_RAW]);

        foreach ($joins as $table => $condition) {
            $this->join($table, $condition, $type);
        }

        $this->currentStep = self::STEP_JOIN;

        return $this;
    }

    /**
     * Add a conditional OR clause to the WHERE condition.
     *
     * @param array $conditions Associative array of OR conditions.
     * @return $this
     * @throws VoltException If called in an invalid method order.
     */
    public function orWhere(array $conditions)
    {
        $this->validateStep([self::STEP_WHERE]);
        // if ($this->currentStep !== 'where') {
        //     throw new VoltException('Invalid method order. OR WHERE should come after a WHERE condition.');
        // }

        $orWhere = [];
        foreach ($conditions as $column => $value) {
            $orWhere[] = "$column = :$column";
            $this->bindValues[":$column"] = $value;
        }

        $this->query .= " OR " . implode(' OR ', $orWhere);

        return $this;
    }

    /**
     * Add a subquery condition to the WHERE clause.
     *
     * @param string $column Column name to compare with the subquery.
     * @param VoltQueryBuilder $subquery Subquery instance.
     * @param string $operator Comparison operator (e.g., '=', '>', '<').
     * @return $this
     * @throws VoltException If called in an invalid method order.
     */
    public function whereSubquery(string $column, VoltQueryBuilder $subquery, string $operator = '=')
    {
        $this->validateStep([self::STEP_UPDATE, self::STEP_SELECT, self::STEP_WHERE, self::STEP_DELETE, self::STEP_JOIN, self::STEP_RAW]);

        if ($this->currentStep !== 'update' && $this->currentStep !== 'select' && $this->currentStep !== 'where' && $this->currentStep !== 'delete' && $this->currentStep !== 'count' && $this->currentStep !== 'join' && $this->currentStep !== 'raw') {
            throw new VoltException('Invalid method order. WHERE should come after SELECT, UPDATE, DELETE, or a previous WHERE.');
        }

        $this->query .= " AND $column $operator (" . $subquery->getQuery() . ")";

        return $this;
    }

    // Additional advanced methods can be added here

    /**
     * Execute the query and return a generator for streaming large result sets.
     *
     * @param string $data_type The data type for fetching results ('object', 'assoc', 'class').
     * @param int $chunk_size The number of rows to fetch at a time.
     * @return \Generator
     * @throws VoltException If an error occurs during query execution.
     */
    public function stream($data_type = 'object', $chunk_size = 100): \Generator
    {
        try {
            $stm = $this->executeQuery();

            while ($rows = $stm->fetchAll(PDO::FETCH_OBJ)) {
                foreach ($rows as $row) {
                    yield $row;
                }

                // Fetch the next chunk
                $stm->fetch(PDO::FETCH_OBJ, PDO::FETCH_ORI_NEXT, $chunk_size);
            }
        } catch (PDOException $e) {
            throw new VoltException($e->getMessage());
        }
    }

    // Override or extend existing methods as needed

    /**
     * Execute a raw SQL query with parameters and return the affected row count.
     *
     * @param string $sql The raw SQL query.
     * @param array $bindValues Associative array of bind values.
     * @return int The number of affected rows.
     * @throws VoltException If an error occurs during query execution.
     */
    public function executeRaw(string $sql, array $bindValues = []): int
    {
        try {
            $this->query = $sql;
            $this->bindValues = $bindValues;
            $stm = $this->executeQuery();

            return $stm->rowCount();
        } catch (PDOException $e) {
            throw new VoltException($e->getMessage());
        }
    }
}