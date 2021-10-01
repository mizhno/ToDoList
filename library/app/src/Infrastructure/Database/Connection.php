<?php

declare(strict_types=1);

namespace Library\Infrastructure\Database;

use Generator;
use PDO;
use phpDocumentor\Reflection\Types\Scalar;

final class Connection
{
    public function __construct(
        private PDO $pdo
    ) {
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    public function delete(string $table, array $where = []): void
    {
        $sql = sprintf('DELETE FROM %s', $table);

        if ($where !== []) {
            $where = array_map(
                function(array $predicate): string {
                    $field = array_key_first($predicate);
                    $value = current($predicate);

                    if (is_array($value)) {
                        return sprintf(
                            '%s IN (%s)',
                            $field,
                            implode(',', array_map(
                                fn(mixed $value): mixed => $this->pdo->quote($value),
                                $value,
                            ))
                        );
                    }

                    return sprintf('%s = %s', $field, $this->pdo->quote($value));
                },
                $where,
            );

            $where = implode(' AND ', $where);
            $where = ' WHERE ' . $where;
            $sql .= $where;
        }

        $this->execute($sql);
    }

    /**
     * @param string[]                              $fields
     * @param array<array-key,array<string,scalar>> $where
     *
     * @return null|array<array-key,scalar>
     */
    public function selectOneRow(string $table, array $fields = [], array $where = []): ?array
    {
        /** @var array<scalar|string>|false $result */
        $result = $this
            ->pdo
            ->query(
                $this->buildSelectSql($table, $fields, $where)
            )->fetch();

        return $result === false ? null : $result;
    }

    /**
     * @param string[]                              $fields
     * @param array<array-key,array<string,scalar>> $where
     */
    public function selectAllRows(string $table, array $fields = [], array $where = []): Generator
    {
        yield from $this
            ->pdo
            ->query(
                $this->buildSelectSql($table, $fields, $where)
            );
    }

    /**
     * @param non-empty-array<array-key,string>            $fields
     * @param list<non-empty-array<null|array-key,scalar>> $values
     */
    public function insert(string $table, array $fields, array $values): void
    {
        $quoteValuesInArray = fn(string $value): string => $this->pdo->quote($value);
        $convertNestedArrayToString = static fn(array $part): string => implode(', ', array_map($quoteValuesInArray, $part));

        $values = array_map(
            $convertNestedArrayToString,
            $values,
        );

        $values = implode('), (', $values);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(',', $fields),
            $values,
        );

        $this->execute($sql);
    }

    public function update(string $table, array $data, array $where): void
    {
        $updates = [];
        foreach ($data as $field => $value) {
            if ($value === null) {
                $updates[] = $field . '=' . 'NULL';

                continue;
            }
            $updates[] = $field . '=' . $this->pdo->quote($value);
        }
        $sql = sprintf('UPDATE %s SET %s', $table, implode(',', $updates));

        $this->buildWhereString($where, $sql);
    }

    /**
     * @param array<string,scalar> $params
     */
    public function query(string $sql, array $params = []): Generator
    {
        $statement = $this->pdo->prepare($sql);

        $statement->execute($this->prependParamKeysWithColon($params));

        yield from $statement;
    }

    public function execute(string $sql, array $params = []): void
    {
        $statement = $this->pdo->prepare($sql);

        $statement->execute($this->prependParamKeysWithColon($params));
    }

    public function buildWhereString(array $where, string $sql): void
    {
        if ($where !== []) {
            $where = array_map(
                fn(array $predicate): string => sprintf(
                    '%s = %s',
                    array_key_first($predicate) ?? '',
                    $this->pdo->quote((string) current($predicate)),
                ),
                $where,
            );

            $where = implode(' AND ', $where);
            $where = ' WHERE ' . $where;
            $sql .= $where;
        }

        $this->execute($sql);
    }

    /**
     * @param string[]                              $fields
     * @param array<array-key,array<string,scalar>> $where
     */
    private function buildSelectSql(string $table, array $fields = [], array $where = []): string
    {
        $sql = sprintf(
            'SELECT %s FROM %s',
            implode(', ', $fields),
            $table,
        );

        if ($where !== []) {
            $where = array_map(
                function(array $predicate): string {
                    $field = array_key_first($predicate);
                    $value = current($predicate);

                    if (is_array($value)) {
                        return sprintf(
                            '%s IN (%s)',
                            $field,
                            implode(',', array_map(
                                fn(mixed $value): mixed => $this->pdo->quote($value),
                                $value,
                            ))
                        );
                    }

                    return sprintf('%s = %s', $field, $this->pdo->quote($value));
                },
                $where,
            );

            $where = implode(' AND ', $where);
            $where = ' WHERE ' . $where;
            $sql .= $where;
        }

        return $sql;
    }

    private function prependParamKeysWithColon(array $params): array
    {
        if ($params === []) {
            return [];
        }

        /** @var string[] $keys */
        $keys = array_keys($params);

        return array_combine(
            array_map(static fn(string $key): string => ':' . $key, $keys),
            array_values($params),
        );
    }
}
