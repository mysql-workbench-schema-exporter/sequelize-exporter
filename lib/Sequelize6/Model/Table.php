<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2020 Toha <tohenk@yahoo.com>
 * Copyright (c) 2013 WitteStier <development@wittestier.nl>
 * Copyright (c) 2021 Marc-Olivier Laux <marco@matlaux.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Node\Sequelize6\Model;

use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Formatter\Node\Sequelize6\Formatter;
use MwbExporter\Writer\WriterInterface;
use MwbExporter\Object\JS;
use MwbExporter\Helper\Comment;
use MwbExporter\Formatter\DatatypeConverterInterface;
use MwbExporter\Model\ForeignKey;
use MwbExporter\Formatter\FormatterInterface;

class Table extends BaseTable
{
    /**
     * Get JSObject.
     *
     * @param mixed $content    Object content
     * @param bool  $multiline  Multiline result
     * @param bool  $raw        Is raw object
     * @return \MwbExporter\Object\JS
     */
    public function getJSObject($content, $multiline = true, $raw = false)
    {
        $indentation = $this->getConfig()->get(Formatter::CFG_USE_TABS) ? "\t" : ' ';
        $indentation = str_repeat($indentation, $this->getConfig()->get(Formatter::CFG_INDENTATION));

        return new JS($content, [
            'multiline' => $multiline,
            'raw' => $raw,
            'indentation' => $indentation,
        ]);
    }

    public function writeTable(WriterInterface $writer)
    {
        switch (true) {
            case $this->isExternal():
                return self::WRITE_EXTERNAL;

            case $this->getConfig()->get(Formatter::CFG_SKIP_M2M_TABLES) && $this->isManyToMany():
                return self::WRITE_M2M;

            default:
                $writer->open($this->getTableFileName());
                $this->writeBody($writer);
                $writer->close();
                return self::WRITE_OK;
        }
    }

    /**
     * Write model body code.
     *
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Node\Sequelize6\Model\Table
     */
    protected function writeBody(WriterInterface $writer)
    {
        $semicolon = $this->getConfig()->get(Formatter::CFG_USE_SEMICOLONS) ? ';' : '';

        $writer
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                if ($_this->getConfig()->get(Formatter::CFG_ADD_COMMENT)) {
                    $writer
                        ->write($_this->getFormatter()->getComment(Comment::FORMAT_JS))
                        ->write('')
                    ;
                }
            })
            ->write("const { DataTypes, Model } = require('sequelize')$semicolon")
            ->write("")
            ->write("class %s extends Model {", $this->getModelName())
            ->write("}")
            ->write("")
            ->write("module.exports = (sequelize%s) => {", $this->getConfig()->get(Formatter::CFG_INJECT_EXTEND_FUNCTION) ? ", extend" : "")
            ->indent()
                ->write("%s.init(%s, %s)$semicolon", $this->getModelName(), $this->asModel(), $this->asOptions())
                ->write("")
                ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($semicolon) {
                    if ($_this->getConfig()->get(Formatter::CFG_GENERATE_ASSOCIATION_METHOD)) {
                        $writer
                            ->write("%s.associate = () => {", $this->getModelName())
                            ->indent()
                                ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                                    $_this->writeASsociations($writer);
                                })
                            ->outdent()
                            ->write("}$semicolon")
                            ->write("")
                        ;
                    }
                })
                ->write("return %s$semicolon", $this->getModelName())
            ->outdent()
            ->write("}")
            ->write("")
        ;

        return $this;
    }

    protected function asOptions()
    {
        $formatter = $this->getFormatter();
        $result = array_merge([
            'sequelize'         => $this->getJSObject('sequelize', false, true),
            'modelName'         => $this->getModelName(),
            'tableName'         => $this->getRawTableName(),
            'indexes'           => count($indexes = $this->getIndexes()) ? $indexes : null,
            'timestamps'        => $this->getConfig()->get(Formatter::CFG_USE_TIMESTAMPS),
            'underscored'       => true,
            'syncOnAssociation' => false
        ], $formatter->getTableProp());

        return $this->getJSObject($result);
    }

    protected function asModel()
    {
        $result = $this->getFields();
        $modelDefinition = $this->getJSObject($result);
        return $this->getConfig()->get(Formatter::CFG_INJECT_EXTEND_FUNCTION)
            ? sprintf("extend(%s)", $modelDefinition)
            : $modelDefinition;
    }

    /**
     * Get model fields.
     *
     * @return array
     */
    protected function getFields()
    {
        $result = [];
        /** @var \MwbExporter\Model\Column $column */
        foreach ($this->getColumns() as $column)
        {
            if (!$this->getConfig()->get(Formatter::CFG_GENERATE_FOREIGN_KEYS_FIELDS) && count($column->getForeignKeys())) {
                continue;
            }

            $type = $this->getFormatter()->getDatatypeConverter()->getType($column);
            if (DatatypeConverterInterface::DATATYPE_ENUM == $column->getColumnType()) {
                $type .= $column->getParameters()->get("datatypeExplicitParams");
            } elseif (DatatypeConverterInterface::DATATYPE_DECIMAL == $column->getColumnType()) {
                $type .= sprintf('(%s, %s)', $column->getParameters()->get('precision'), $column->getParameters()->get('scale'));
            } elseif (($len = $column->getLength()) > 0) {
                $type .= sprintf('(%s)', $len);
            }
            $c = [];
            $c['type'] = $this->getJSObject(sprintf('DataTypes.%s', $type ? $type : 'STRING.BINARY'), true, true);
            
            $c['field'] = $column->getColumnName();
            if ($column->isPrimary()) {
                $c['primaryKey'] = true;
            }
            if ($column->isUnique()) {
                $c['unique'] = true;
            }
            if ($column->isAutoIncrement()) {
                $c['autoIncrement'] = true;
            } elseif ($column->isNotNull()) {
                $c['allowNull'] = false;
            }
            if ($column->getDefaultValue() !== null) {
                if ($type === 'DATE') {
                    $c['defaultValue'] = substr($column->getDefaultValue(), -1) === ')'
                        ? $this->getJSObject(sprintf("sequelize.fn('%s')", substr($column->getDefaultValue(), 0, -2)), false, true)
                        : $this->getJSObject(sprintf("sequelize.literal('%s')", $column->getDefaultValue()), false, true);
                } else if ($type === 'BOOLEAN') {
                    $c['defaultValue'] = (bool) $this->getJSObject($column->getDefaultValue(), true, true);
                } else {
                    $c['defaultValue'] = $this->getJSObject($column->getDefaultValue(), true, true);
                }
            }

            if (count($column->getForeignKeys())) {
                $c['references'] = [];
                /** @var \MwbExporter\Model\ForeignKey $foreignKey */
                foreach ($column->getForeignKeys() as $foreignKey) {
                    $c['references']['model'] = $foreignKey->getReferencedTable()->getModelName();
                    $c['references']['key'] = $this->getNaming($foreignKey->getForeign()->getColumnName());
                    if ($onUpdate = $foreignKey->getParameter('updateRule'))
                    {
                        $c['onUpdate'] = strtoupper($onUpdate);
                    }
                    if ($onDelete = $foreignKey->getParameter('deleteRule'))
                    {
                        $c['onDelete'] = strtoupper($onDelete);
                    }
                }
            }
            $result[$this->getNaming($column->getColumnName())] = $c;
        }

        return $result;
    }

    protected function getIndexes()
    {
        $result = [];
        foreach ($this->getIndices() as $index) {
            $isForeignIndex = array_reduce($index->getColumns(), function ($isForeignIndex, $column) {
                return $isForeignIndex || count($column->getForeignKeys()) > 0;
            }, false);

            // Create foreign index if its essociated field or association is generated
            if ($this->getConfig()->get(Formatter::CFG_GENERATE_FOREIGN_KEYS_FIELDS) ||
                $this->getConfig()->get(Formatter::CFG_GENERATE_ASSOCIATION_METHOD) ||
                !$isForeignIndex) {
                if ($index->isIndex() || $index->isUnique()) {
                    $result[] = [
                        'name' => $index->getName(),
                        'fields' => $this->getJSObject($index->getColumnNames(), false),
                        'unique' => $index->isUnique() ? true : null,
                    ];
                }
            }
        }

        return $result;
    }

    protected function getConstraints() {
        $constraints = array();
        foreach ($this->getAllLocalForeignKeys() as $k => $local) {
            if (!$this->isLocalForeignKeyIgnored($local)) {
                $model = $local->getOwningTable()->getModelName();
                if (!isset($constraints[$model])) {
                    $constraints[$model] = 1;
                } else {
                    $constraints[$model]++;
                }
            }
        }
        foreach ($this->getAllForeignKeys() as $k => $foreign) {
            if (!$this->isForeignKeyIgnored($foreign)) {
                $model = $foreign->getReferencedTable()->getModelName();
                if (!isset($constraints[$model])) {
                    $constraints[$model] = 1;
                } else {
                    $constraints[$model]++;
                }
            }
        }
        return array_map(function ($count) {
            return $count > 1 ? false : true;
        }, $constraints);
    }

    public function extractForeignAlias($foreignColumnName, $targetTableName, $targetColumnName) {
        // remove standard name
        $relatedAlias = preg_replace(
            "/(${targetTableName}_)?${targetColumnName}/",
            '',
            $foreignColumnName
        );

        // clean leading _
        $relatedAlias = preg_replace(
            "/(^_|_\$)/",
            '',
            $relatedAlias
        );
        
        return $relatedAlias;
    }

    protected function writeAssociations(WriterInterface $writer)
    {
        $semicolon = $this->getConfig()->get(Formatter::CFG_USE_SEMICOLONS) ? ';' : '';
        $constraints = $this->getConstraints();

        // 1 <=> N references
        $firstAssociation = true;
        foreach ($this->getAllLocalForeignKeys() as $k => $local) {
            if ($this->isLocalForeignKeyIgnored($local)) {
                $this->getDocument()->addLog(sprintf('  Local relation "%s" was ignored', $local->getOwningTable()->getModelName()));
                continue;
            }

            $targetEntity = $local->getOwningTable()->getModelName();
            $mappedBy = $local->getReferencedTable()->getModelName();
            $referencedTableName = $local->getReferencedTable()->getName();
            $relatedColumnName = $local->getLocal()->getColumnName();
            $foreignColumnName = $local->getForeign()->getColumnName();
            $as = "";

            if ($relatedColumnName) {
                // assumes multiple foreign keys to same model is formatted as "%alias%_(%foreign_table%_)?%foreign_col%" 
                // or "(%foreign_table%_)?%foreign_col%_%alias%"
                $relatedAlias = $this->extractForeignAlias($relatedColumnName, $referencedTableName, $foreignColumnName);

                if ($relatedAlias) {
                    $as = $this->pluralize($this->getNaming(sprintf('%s_%s_%s',
                        $relatedAlias,
                        $mappedBy,
                        $local->getOwningTable()->getModelName()), null, true));
                } else {
                    $as = $this->pluralize($this->getNaming($local->getOwningTable()->getModelName(), null, true));
                }
            } else {
                $as = $this->pluralize($this->getNaming($local->getOwningTable()->getModelName(), null, true));
            }

            if ($as === "" || $as === $local->getOwningTable()->getModelName()) {
                $as = null;
            }

            $options = array(
                'foreignKey'    => array(
                    'name'          => $this->getNaming($local->getLocal()->getColumnName()),
                    'allowNull'     => !$local->getLocal()->isNotNull(),
                ),
                // @see https://github.com/sequelize/sequelize/issues/5158#issuecomment-183051761
                'onUpdate'      => $constraints[$local->getOwningTable()->getModelName()] === false ?null : $local->getParameter('updateRule'),
                // @see https://github.com/sequelize/sequelize/issues/5158#issuecomment-183051761
                'onDelete'      => $constraints[$local->getOwningTable()->getModelName()] === false ?null : $local->getParameter('deleteRule'),
                'targetKey'     => $this->getNaming($local->getForeign()->getColumnName()),
                'as'            => $as,
                // @see https://sequelize.org/master/manual/constraints-and-circularities.html
                'constraints'   => $constraints[$local->getOwningTable()->getModelName()] === false ? false : null
            );

            $associationMethod = null;
            $comment = null;
            $this->getDocument()->addLog(sprintf('  Writing 1 <=> ? relation "%s"', $targetEntity));

            if ($local->isManyToOne()) {
                $this->getDocument()->addLog('  Relation considered as "1 <=> N"');
                $comment = '// 1 <=> N association';
                $associationMethod = 'hasMany';
            } else {
                $this->getDocument()->addLog('  Relation considered as "1 <=> 1"');
                $comment = '// 1 <=> 1 association';
                $associationMethod = 'hasOne';
            }

            $writer
                ->writeIf(!$firstAssociation, '')
                ->write($comment)
                ->write("%s.%s(sequelize.models.%s, %s)$semicolon", 
                    $this->getModelName(),
                    $associationMethod,
                    $local->getOwningTable()->getModelName(),
                    $this->getJSObject($options));

            $firstAssociation = false;
        }

        // N <=> 1 references
        foreach ($this->getAllForeignKeys() as $k => $foreign) {
            if ($this->isForeignKeyIgnored($foreign)) {
                $this->getDocument()->addLog(sprintf('  Foreign relation "%s" was ignored', $foreign->getOwningTable()->getModelName()));
                continue;
            }

            $targetEntity = $foreign->getReferencedTable()->getModelName();
            $targetEntityFQCN = $foreign->getReferencedTable()->getModelName();
            $referencedTableName = $foreign->getReferencedTable()->getName();
            $inversedBy = $foreign->getOwningTable()->getModelName();
            $relatedColumnName = $foreign->getLocal()->getColumnName();
            $as = null;
            $foreignColumnName = $foreign->getForeign()->getColumnName();

            if ($relatedColumnName) {
                // assumes multiple foreign keys to same model is formatted as "%alias%_%foreign_col%" 
                // or "%foreign_col%_%alias%"
                $relatedAlias = $this->extractForeignAlias($relatedColumnName, $referencedTableName, $foreignColumnName);

                if (!$relatedAlias) {
                    $relatedAlias = $foreign->getReferencedTable()->getModelName();
                } else {
                    $relatedAlias = sprintf("%s_%s",
                        $relatedAlias,
                        $foreign->getReferencedTable()->getModelName());
                }

                $as = $this->getNaming($relatedAlias, null, true);

                // If alias is the same as foreign model, don't use it
                if ($as === $foreign->getReferencedTable()->getModelName()) {
                    $as = null;
                }
            }

            $options = array(
                'foreignKey'    => array(
                    'name'          => $this->getNaming($foreign->getLocal()->getColumnName()),
                    'allowNull'     => !$foreign->getLocal()->isNotNull(),
                ),
                // @see https://github.com/sequelize/sequelize/issues/5158#issuecomment-183051761
                'onUpdate'      => $constraints[$foreign->getReferencedTable()->getModelName()] === false ? null : $foreign->getParameter('updateRule'),
                // @see https://github.com/sequelize/sequelize/issues/5158#issuecomment-183051761
                'onUpdate'      => $constraints[$foreign->getReferencedTable()->getModelName()] === false ? null : $foreign->getParameter('deleteRule'),
                'targetKey'     => $this->getNaming($foreign->getForeign()->getColumnName()),
                'as'            => $as,
                // @see https://sequelize.org/master/manual/constraints-and-circularities.html
                'constraints'   => $constraints[$foreign->getReferencedTable()->getModelName()] === false ? false : null
            );

            $associationMethod = 'belongsTo';
            $this->getDocument()->addLog(sprintf('  Writing N <=> ? relation "%s"', $targetEntity));
            
            if ($foreign->isManyToOne()) {
                $this->getDocument()->addLog('  Relation considered as "N <=> 1"');
                $comment = '// N <=> 1 association';
            } else {
                $this->getDocument()->addLog('  Relation considered as "1 <=> 1"');
                $comment = '// 1 <=> 1 association';
            }

            $writer
                ->writeIf(!$firstAssociation, '')
                ->write($comment)
                ->write("%s.%s(sequelize.models.%s, %s)$semicolon", 
                    $this->getModelName(),
                    $associationMethod,
                    $foreign->getReferencedTable()->getModelName(),
                    $this->getJSObject($options));

            $firstAssociation = false;
        }

        // N <=> M associations
        foreach ($this->getTableM2MRelations() as $relation) {
            $this->getDocument()->addLog(sprintf('  Writing setter/getter for N <=> N "%s"', $relation['refTable']->getModelName()));

            $options = array(
                'through'       => $relation['reference']->getOwningTable()->getRawTableName(),
                'foreignKey'    => array(
                    'name'          => $relation['reference']->getLocal()->getColumnName(),
                ),
                'onUpdate'      => $relation['reference']->getParameter('updateRule'),
                'onDelete'      => $relation['reference']->getParameter('deleteRule'),
                'targetKey'     => $this->getNaming($relation['target']->getForeign()->getColumnName()),
                'as'            => $this->pluralize($this->getNaming($relation['refTable']->getModelName(), null, true))
            );

            $writer
                ->writeIf(!$firstAssociation, '')
                ->write('// N <=> M association')
                ->write("%s.belongsToMany(sequelize.models.%s, %s)$semicolon",
                    $this->getModelName(), 
                    $relation['refTable']->getModelName(), 
                    $this->getJSObject($options));

            $firstAssociation = false;
        }
        return $this;
    }

    /**
     * Inject a many to many relation into referenced table.
     *
     * @param \MwbExporter\Model\ForeignKey $fk1
     * @param \MwbExporter\Model\ForeignKey $fk2
     * @return \MwbExporter\Model\Table
     */
    protected function injectManyToMany(ForeignKey $fk1, ForeignKey $fk2)
    {
        $fk1->getReferencedTable()->setManyToManyRelation(array(
            'reference' => $fk1,
            'target'    => $fk2,
            'refTable'  => $fk2->getReferencedTable()));

        return $this;
    }

    /**
     * Format column name as relation to foreign table.
     *
     * @param string $column  The column name
     * @param bool   $code    If true, use result as PHP code or false, use as comment
     * @return string
     */
    public function formatRelatedName($column, $code = true)
    {
        return $code ? sprintf('%s', $column) : sprintf('related by `%s`', $column);
    }

    /**
     * Override inherited getNaming : when camel cased, model aliases have to begin by a upper cased character
     *
     * @param string $name
     * @param string $strategy
     * @param bool $isModel 
     * @return string
     */
    public function getNaming($name, $strategy = null, $isModel = false)
    {
        if (!$strategy) {
            $strategy = $this->getConfig()->get(FormatterInterface::CFG_NAMING_STRATEGY);
            if ($strategy === FormatterInterface::NAMING_CAMEL_CASE && $isModel) {
                $strategy = FormatterInterface::NAMING_PASCAL_CASE;
            }
        }

        return parent::getNaming($name, $strategy);
    }
}