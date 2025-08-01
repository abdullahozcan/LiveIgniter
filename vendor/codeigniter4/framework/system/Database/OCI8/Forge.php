<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\OCI8;

use CodeIgniter\Database\Forge as BaseForge;

/**
 * Forge for OCI8
 */
class Forge extends BaseForge
{
    /**
     * DROP INDEX statement
     *
     * @var string
     */
    protected $dropIndexStr = 'DROP INDEX %s';

    /**
     * CREATE DATABASE statement
     *
     * @var false
     */
    protected $createDatabaseStr = false;

    /**
     * CREATE TABLE IF statement
     *
     * @var false
     *
     * @deprecated This is no longer used.
     */
    protected $createTableIfStr = false;

    /**
     * DROP TABLE IF EXISTS statement
     *
     * @var false
     */
    protected $dropTableIfStr = false;

    /**
     * DROP DATABASE statement
     *
     * @var false
     */
    protected $dropDatabaseStr = false;

    /**
     * UNSIGNED support
     *
     * @var array|bool
     */
    protected $unsigned = false;

    /**
     * NULL value representation in CREATE/ALTER TABLE statements
     *
     * @var string
     */
    protected $null = 'NULL';

    /**
     * RENAME TABLE statement
     *
     * @var string
     */
    protected $renameTableStr = 'ALTER TABLE %s RENAME TO %s';

    /**
     * DROP CONSTRAINT statement
     *
     * @var string
     */
    protected $dropConstraintStr = 'ALTER TABLE %s DROP CONSTRAINT %s';

    /**
     * Foreign Key Allowed Actions
     *
     * @var array
     */
    protected $fkAllowActions = ['CASCADE', 'SET NULL', 'NO ACTION'];

    /**
     * ALTER TABLE
     *
     * @param string       $alterType       ALTER type
     * @param string       $table           Table name
     * @param array|string $processedFields Processed column definitions
     *                                      or column names to DROP
     *
     * @return ($alterType is 'DROP' ? string : list<string>)
     */
    protected function _alterTable(string $alterType, string $table, $processedFields)
    {
        $sql = 'ALTER TABLE ' . $this->db->escapeIdentifiers($table);

        if ($alterType === 'DROP') {
            $columnNamesToDrop = $processedFields;

            $fields = array_map(
                fn ($field) => $this->db->escapeIdentifiers(trim($field)),
                is_string($columnNamesToDrop) ? explode(',', $columnNamesToDrop) : $columnNamesToDrop,
            );

            return $sql . ' DROP (' . implode(',', $fields) . ') CASCADE CONSTRAINT INVALIDATE';
        }

        if ($alterType === 'CHANGE') {
            $alterType = 'MODIFY';
        }

        $nullableMap = array_column($this->db->getFieldData($table), 'nullable', 'name');
        $sqls        = [];

        for ($i = 0, $c = count($processedFields); $i < $c; $i++) {
            if ($alterType === 'MODIFY') {
                // If a null constraint is added to a column with a null constraint,
                // ORA-01451 will occur,
                // so add null constraint is used only when it is different from the current null constraint.
                // If a not null constraint is added to a column with a not null constraint,
                // ORA-01442 will occur.
                $wantToAddNull   = ! str_contains($processedFields[$i]['null'], ' NOT');
                $currentNullable = $nullableMap[$processedFields[$i]['name']];

                if ($wantToAddNull && $currentNullable === true) {
                    $processedFields[$i]['null'] = '';
                } elseif ($processedFields[$i]['null'] === '' && $currentNullable === false) {
                    // Nullable by default
                    $processedFields[$i]['null'] = ' NULL';
                } elseif ($wantToAddNull === false && $currentNullable === false) {
                    $processedFields[$i]['null'] = '';
                }
            }

            if ($processedFields[$i]['_literal'] !== false) {
                $processedFields[$i] = "\n\t" . $processedFields[$i]['_literal'];
            } else {
                $processedFields[$i]['_literal'] = "\n\t" . $this->_processColumn($processedFields[$i]);

                if (! empty($processedFields[$i]['comment'])) {
                    $sqls[] = 'COMMENT ON COLUMN '
                        . $this->db->escapeIdentifiers($table) . '.' . $this->db->escapeIdentifiers($processedFields[$i]['name'])
                        . ' IS ' . $processedFields[$i]['comment'];
                }

                if ($alterType === 'MODIFY' && ! empty($processedFields[$i]['new_name'])) {
                    $sqls[] = $sql . ' RENAME COLUMN ' . $this->db->escapeIdentifiers($processedFields[$i]['name'])
                        . ' TO ' . $this->db->escapeIdentifiers($processedFields[$i]['new_name']);
                }

                $processedFields[$i] = "\n\t" . $processedFields[$i]['_literal'];
            }
        }

        $sql .= ' ' . $alterType . ' ';
        $sql .= count($processedFields) === 1
                ? $processedFields[0]
                : '(' . implode(',', $processedFields) . ')';

        // RENAME COLUMN must be executed after MODIFY
        array_unshift($sqls, $sql);

        return $sqls;
    }

    /**
     * Field attribute AUTO_INCREMENT
     *
     * @return void
     */
    protected function _attributeAutoIncrement(array &$attributes, array &$field)
    {
        if (! empty($attributes['AUTO_INCREMENT']) && $attributes['AUTO_INCREMENT'] === true
            && str_contains(strtolower($field['type']), 'number')
            && version_compare($this->db->getVersion(), '12.1', '>=')
        ) {
            $field['auto_increment'] = ' GENERATED BY DEFAULT ON NULL AS IDENTITY';
        }
    }

    /**
     * Process column
     */
    protected function _processColumn(array $processedField): string
    {
        $constraint = '';
        // @todo: can't cover multi pattern when set type.
        if ($processedField['type'] === 'VARCHAR2' && str_starts_with($processedField['length'], "('")) {
            $constraint = ' CHECK(' . $this->db->escapeIdentifiers($processedField['name'])
                . ' IN ' . $processedField['length'] . ')';

            $processedField['length'] = '(' . max(array_map(mb_strlen(...), explode("','", mb_substr($processedField['length'], 2, -2)))) . ')' . $constraint;
        } elseif (isset($this->primaryKeys['fields']) && count($this->primaryKeys['fields']) === 1 && $processedField['name'] === $this->primaryKeys['fields'][0]) {
            $processedField['unique'] = '';
        }

        return $this->db->escapeIdentifiers($processedField['name'])
           . ' ' . $processedField['type'] . $processedField['length']
           . $processedField['unsigned']
           . $processedField['default']
           . $processedField['auto_increment']
           . $processedField['null']
           . $processedField['unique'];
    }

    /**
     * Performs a data type mapping between different databases.
     *
     * @return void
     */
    protected function _attributeType(array &$attributes)
    {
        // Reset field lengths for data types that don't support it
        // Usually overridden by drivers
        switch (strtoupper($attributes['TYPE'])) {
            case 'TINYINT':
                $attributes['CONSTRAINT'] ??= 3;

                // no break
            case 'SMALLINT':
                $attributes['CONSTRAINT'] ??= 5;

                // no break
            case 'MEDIUMINT':
                $attributes['CONSTRAINT'] ??= 7;

                // no break
            case 'INT':
            case 'INTEGER':
                $attributes['CONSTRAINT'] ??= 11;

                // no break
            case 'BIGINT':
                $attributes['CONSTRAINT'] ??= 19;

                // no break
            case 'NUMERIC':
                $attributes['TYPE'] = 'NUMBER';

                return;

            case 'BOOLEAN':
                $attributes['TYPE']       = 'NUMBER';
                $attributes['CONSTRAINT'] = 1;
                $attributes['UNSIGNED']   = true;

                return;

            case 'DOUBLE':
                $attributes['TYPE'] = 'FLOAT';
                $attributes['CONSTRAINT'] ??= 126;

                return;

            case 'DATETIME':
            case 'TIME':
                $attributes['TYPE'] = 'DATE';

                return;

            case 'SET':
            case 'ENUM':
            case 'VARCHAR':
                $attributes['CONSTRAINT'] ??= 255;

                // no break
            case 'TEXT':
            case 'MEDIUMTEXT':
                $attributes['CONSTRAINT'] ??= 4000;
                $attributes['TYPE'] = 'VARCHAR2';
        }
    }

    /**
     * Generates a platform-specific DROP TABLE string
     *
     * @return bool|string
     */
    protected function _dropTable(string $table, bool $ifExists, bool $cascade)
    {
        $sql = parent::_dropTable($table, $ifExists, $cascade);

        if ($sql !== true && $cascade) {
            $sql .= ' CASCADE CONSTRAINTS PURGE';
        } elseif ($sql !== true) {
            $sql .= ' PURGE';
        }

        return $sql;
    }

    /**
     * Constructs sql to check if key is a constraint.
     */
    protected function _dropKeyAsConstraint(string $table, string $constraintName): string
    {
        return "SELECT constraint_name FROM all_constraints WHERE table_name = '"
            . trim($table, '"') . "' AND index_name = '"
            . trim($constraintName, '"') . "'";
    }
}
