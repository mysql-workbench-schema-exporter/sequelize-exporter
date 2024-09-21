<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2023 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Sequelize\V6\Model;

use MwbExporter\Configuration\Comment as CommentConfiguration;
use MwbExporter\Configuration\Header as HeaderConfiguration;
use MwbExporter\Configuration\Indentation as IndentationConfiguration;
use MwbExporter\Configuration\M2MSkip as M2MSkipConfiguration;
use MwbExporter\Configuration\NamingStrategy as NamingStrategyConfiguration;
use MwbExporter\Formatter\DatatypeConverterInterface;
use MwbExporter\Formatter\Sequelize\Configuration\Association as AssociationConfiguration;
use MwbExporter\Formatter\Sequelize\Configuration\AssociationAlias as AssociationAliasConfiguration;
use MwbExporter\Formatter\Sequelize\Configuration\Extendable as ExtendableConfiguration;
use MwbExporter\Formatter\Sequelize\Configuration\ForeignKey as ForeignKeyConfiguration;
use MwbExporter\Formatter\Sequelize\Configuration\PackageName as PackageNameConfiguration;
use MwbExporter\Formatter\Sequelize\Configuration\SemiColon as SemiColonConfiguration;
use MwbExporter\Helper\Comment;
use MwbExporter\Model\ForeignKey;
use MwbExporter\Model\Table as BaseTable;
use MwbExporter\Writer\WriterInterface;
use NTLAB\Object\JS;

class Table extends BaseTable
{
    protected $attributeFieldKey = 'field';
    protected $referencesModelKey = 'model';
    protected $associationNaming = NamingStrategyConfiguration::PASCAL_CASE;

    /**
     * Get JSObject.
     *
     * @param mixed $content    Object content
     * @param bool  $multiline  Multiline result
     * @param bool  $raw        Is raw object
     * @return \NTLAB\Object\JS
     */
    public function getJSObject($content, $multiline = true, $raw = false)
    {
        /** @var \MwbExporter\Configuration\Indentation $indentation */
        $indentation = $this->getConfig(IndentationConfiguration::class);

        return new JS($content, [
            'indentation' => $indentation->getIndentation(1),
            'inline' => !$multiline,
            'raw' => $raw,
        ]);
    }

    public function writeTable(WriterInterface $writer)
    {
        switch (true) {
            case $this->isExternal():
                return self::WRITE_EXTERNAL;
            case $this->getConfig(M2MSkipConfiguration::class)->getValue() && $this->isManyToMany():
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
     * @return \MwbExporter\Formatter\Sequelize\V6\Model\Table
     */
    protected function writeBody(WriterInterface $writer)
    {
        $extendable = $this->getConfig(ExtendableConfiguration::class)->getValue();
        $packageName = $this->getConfig(PackageNameConfiguration::class)->getValue();
        $modelName = $modelVarName = $this->getModelName();
        /** @var MwbExporter\Formatter\Sequelize\Configuration\SemiColon $semicolon */
        $semicolon = $this->getConfig(SemiColonConfiguration::class);
        $semicolon = $semicolon->getSemiColon();

        $writer
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                /** @var \MwbExporter\Configuration\Header $header */
                $header = $_this->getConfig(HeaderConfiguration::class);
                if ($content = $header->getHeader()) {
                    $writer
                        ->write($_this->getFormatter()->getFormattedComment($content, Comment::FORMAT_JS, null))
                        ->write('')
                    ;
                }
                if ($_this->getConfig(CommentConfiguration::class)->getValue()) {
                    $writer
                        ->write($_this->getFormatter()->getComment(Comment::FORMAT_JS))
                        ->write('')
                    ;
                }
            })
            ->write("const { Sequelize, DataTypes } = require('$packageName')$semicolon")
            ->write("")
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($extendable) {
                if ($extendable) {
                    $writer
                        ->write("/**")
                        ->write(" * A callback to transform model attributes.")
                        ->write(" *")
                        ->write(" * An example of attributes callback:")
                        ->write(" *")
                        ->write(" * ```")
                        ->write(" * function attrCallback(attributes) {")
                        ->write(" *     // do something with attributes")
                        ->write(" *     return attributes;")
                        ->write(" * }")
                        ->write(" * ```")
                        ->write(" *")
                        ->write(" * @callback attrCallback")
                        ->write(" * @param {object} attributes Model attributes")
                        ->write(" * @returns {object}")
                        ->write(" */")
                        ->write("")
                        ->write("/**")
                        ->write(" * A callback to transform model options.")
                        ->write(" *")
                        ->write(" * An example of options callback:")
                        ->write(" *")
                        ->write(" * ```")
                        ->write(" * function optCallback(options) {")
                        ->write(" *     // do something with options")
                        ->write(" *     return options;")
                        ->write(" * }")
                        ->write(" * ```")
                        ->write(" *")
                        ->write(" * @callback optCallback")
                        ->write(" * @param {object} options Model options")
                        ->write(" * @returns {object}")
                        ->write(" */")
                        ->write("")
                    ;
                }
            })
            ->write("/**")
            ->write(" * Define Sequelize model `$modelName`.")
            ->write(" *")
            ->write(" * @param {Sequelize} sequelize Sequelize")
            ->writeIf($extendable, " * @param {attrCallback|null} attrCallback A callback to transform model attributes")
            ->writeIf($extendable, " * @param {optCallback|null} optCallback A callback to transform model options")
            ->write(" */")
            ->write("module.exports = %s => {", $extendable ? "(sequelize, attrCallback = null, optCallback = null)" : "sequelize")
            ->indent()
            ->write("let attributes = %s", $this->asModel())
            ->write("let options = %s", $this->asOptions())
            ->writeIf($extendable, "if (typeof attrCallback === 'function') {")
            ->indent()
            ->writeIf($extendable, "attributes = attrCallback(attributes)$semicolon")
            ->outdent()
            ->writeIf($extendable, "}")
            ->writeIf($extendable, "if (typeof optCallback === 'function') {")
            ->indent()
            ->writeIf($extendable, "options = optCallback(options)$semicolon")
            ->outdent()
            ->writeIf($extendable, "}")
            ->write("")
            ->write("const $modelVarName = sequelize.define('$modelName', attributes, options)$semicolon")
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($modelVarName, $semicolon) {
                if ($_this->getConfig(AssociationConfiguration::class)->getValue()) {
                    $writer
                        ->write("")
                        ->write("$modelVarName.associate = () => {")
                        ->indent()
                        ->writeCallback(function(WriterInterface $writer, Table $_this = null) use ($modelVarName, $semicolon) {
                            $_this->writeASsociations($writer, $modelVarName, $semicolon);
                        })
                        ->outdent()
                        ->write("}")
                        ->write("")
                    ;
                }
            })
            ->write("return $modelVarName$semicolon")
            ->outdent()
            ->write("}")
        ;

        return $this;
    }

    protected function asOptions()
    {
        /** @var \MwbExporter\Formatter\Sequelize\Formatter */
        $formatter = $this->getFormatter();
        $result = array_merge([
            'sequelize' => $this->getJSObject('sequelize', false, true),
            'modelName' => $this->getModelName(),
            'tableName' => $this->getRawTableName(),
            'indexes' => count($indexes = $this->getIndexes()) ? $indexes : null,
        ], $formatter->getTableProp());

        return $this->getJSObject($result);
    }

    protected function asModel()
    {
        $result = $this->getFields();

        return $this->getJSObject($result);
    }

    /**
     * Get model fields.
     *
     * @return array
     */
    protected function getFields()
    {
        $result = [];
        /** @var \MwbExporter\Formatter\Sequelize\V6\Model\Column $column */
        foreach ($this->getColumns() as $column) {
            if (!$this->getConfig(ForeignKeyConfiguration::class)->getValue() && count($column->getForeignKeys())) {
                continue;
            }

            $type = $this->getFormatter()->getDatatypeConverter()->getType($column);
            // convert tinyint(1) to boolean
            if (DatatypeConverterInterface::DATATYPE_TINYINT == $column->getColumnType() && $column->getParameters()->get('precision') == 1) {
                $type = 'BOOLEAN';
            } elseif (DatatypeConverterInterface::DATATYPE_ENUM == $column->getColumnType()) {
                $type .= $column->getParameters()->get("datatypeExplicitParams");
            } elseif (DatatypeConverterInterface::DATATYPE_DECIMAL == $column->getColumnType()) {
                $type .= sprintf('(%s, %s)', $column->getParameters()->get('precision'), $column->getParameters()->get('scale'));
            } elseif (($len = $column->getLength()) > 0) {
                $type .= sprintf('(%s)', $len);
            }
            $c = [];
            $c['type'] = $this->getJSObject(sprintf('DataTypes.%s', $type ? $type : 'STRING.BINARY'), true, true);

            $c[$this->attributeFieldKey] = $column->getColumnName();
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
                } elseif ($type === 'BOOLEAN') {
                    $c['defaultValue'] = (bool) $this->getJSObject($column->getDefaultValue(), false, true)->__toString();
                } else {
                    $c['defaultValue'] = $this->getJSObject($column->getDefaultValue(), true, true);
                }
            }

            if (count($column->getForeignKeys())) {
                $c['references'] = [];
                /** @var \MwbExporter\Model\ForeignKey $foreignKey */
                foreach ($column->getForeignKeys() as $foreignKey) {
                    $c['references'][$this->referencesModelKey] = $foreignKey->getReferencedTable()->getRawTableName();
                    $c['references']['key'] = $this->getNaming($foreignKey->getForeign()->getColumnName());
                    if ($onUpdate = $foreignKey->getParameter('updateRule')) {
                        $c['onUpdate'] = strtoupper($onUpdate);
                    }
                    if ($onDelete = $foreignKey->getParameter('deleteRule')) {
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
            $isForeignIndex = array_reduce($index->getColumns(), function($isForeignIndex, $column) {
                return $isForeignIndex || count($column->getForeignKeys()) > 0;
            }, false);

            // Create foreign index if its essociated field or association is generated
            if ($this->getConfig(ForeignKeyConfiguration::class)->getValue() ||
                $this->getConfig(AssociationConfiguration::class)->getValue() ||
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

    protected function getConstraints()
    {
        $constraints = [];
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

        return array_map(function($count) {
            return $count > 1 ? false : true;
        }, $constraints);
    }

    protected function countFkOwnerReferences($modelName) {
        $count = 0;
        foreach ($this->getAllLocalForeignKeys() as $fk) {
            if ($fk->getOwningTable()->getModelName() === $modelName) {
                $count++;
            }
        }

        return $count;
    }

    protected function writeAssociations(WriterInterface $writer, $varName, $semicolon)
    {
        $constraints = $this->getConstraints();
        $associationAlias = $this->getConfig(AssociationAliasConfiguration::class)->getValue();

        // 1 <=> N references
        if (count($this->getAllLocalForeignKeys())) {
            $this->getDocument()->addLog(sprintf('+ Writing 1 to N association for %s:', $this->getModelName()));
        }
        foreach ($this->getAllLocalForeignKeys() as $fk) {
            if ($this->isLocalForeignKeyIgnored($fk)) {
                $this->getDocument()->addLog(sprintf('  Local relation "%s" was ignored', $fk->getOwningTable()->getModelName()));
                continue;
            }

            $targetEntity = $fk->getOwningTable()->getModelName();
            $relatedColumnName = $fk->getLocal()->getColumnName();

            $count = $this->countFkOwnerReferences($targetEntity);
            if ($count > 1) {
                // generate association as OtherModelByColumnName
                $as = $this->pluralize($this->getNaming(sprintf('%s_related_by_%s', $targetEntity, $relatedColumnName), $this->associationNaming, true));
            } else {
                $as = $this->pluralize($targetEntity);
            }
            // skip alias if same as target entity
            if ($as === $targetEntity && !$associationAlias) {
                $as = null;
            }

            if ($count > 1) {
                $inverse = $this->getNaming(sprintf('%s_related_by_%s', $this->getModelName(), $relatedColumnName), $this->associationNaming, true);
            } else if (in_array($this->getModelName(), $fk->getOwningTable()->getColumns()->getColumnNames())) {
                $inverse = $this->getNaming(sprintf('%s_fk', $this->getModelName()), $this->associationNaming, true);
            } else {
                $inverse = $associationAlias ? $this->getModelName() : null;
            }

            if (!($options = $this->getAssociationOne($fk, $as, $inverse, $constraints[$targetEntity]))) {
                continue;
            }

            if ($fk->isManyToOne()) {
                $associationMethod = 'hasMany';
                $associationType = '1 <=> N';
            } else {
                $associationMethod = 'hasOne';
                $associationType = '1 <=> 1';
            }

            $this->getDocument()->addLog(sprintf('  Writing %s relation of %s as %s', $associationType, $targetEntity, null !== $as ? $as : 'is'));

            $writer
                ->write("// $associationType")
                ->write(
                    "%s.%s(sequelize.models.%s, %s)$semicolon",
                    $varName,
                    $associationMethod,
                    $targetEntity,
                    $this->getJSObject($options)
                );
        }

        // N <=> 1 references
        if (count($this->getAllForeignKeys())) {
            $this->getDocument()->addLog(sprintf('+ Writing N to 1 association for %s:', $this->getModelName()));
        }
        foreach ($this->getAllForeignKeys() as $fk) {
            if ($this->isForeignKeyIgnored($fk)) {
                $this->getDocument()->addLog(sprintf('  Foreign relation "%s" was ignored', $fk->getOwningTable()->getModelName()));
                continue;
            }

            /** @var \MwbExporter\Formatter\Sequelize\V6\Model\Table $refTable */
            $refTable = $fk->getReferencedTable();
            $targetEntity = $refTable->getModelName();
            $relatedColumnName = $fk->getLocal()->getColumnName();

            $count = $refTable->countFkOwnerReferences($this->getModelName());
            if ($count > 1) {
                $as = $this->getNaming(sprintf('%s_related_by_%s', $targetEntity, $relatedColumnName), $this->associationNaming, true);
            } else if (in_array($targetEntity, $fk->getOwningTable()->getColumns()->getColumnNames())) {
                $as = $this->getNaming(sprintf('%s_fk', $targetEntity), $this->associationNaming, true);
            } else {
                $as = $targetEntity;
            }

            // If alias is the same as foreign model, don't use it
            if ($as === $targetEntity && !$associationAlias) {
                $as = null;
            }

            if (!($options = $this->getAssociationMany($fk, $as, $constraints[$targetEntity]))) {
                continue;
            }

            $associationMethod = 'belongsTo';
            if ($fk->isManyToOne()) {
                $associationType = 'N <=> 1';
            } else {
                $associationType = '1 <=> 1';
            }

            $this->getDocument()->addLog(sprintf('  Writing %s relation of %s as %s', $associationType, $targetEntity, null !== $as ? $as : 'is'));

            $writer
                ->write("// $associationType")
                ->write(
                    "%s.%s(sequelize.models.%s, %s)$semicolon",
                    $varName,
                    $associationMethod,
                    $targetEntity,
                    $this->getJSObject($options)
                );
        }

        // N <=> N associations
        if (count($this->getTableM2MRelations())) {
            $this->getDocument()->addLog(sprintf('+ Writing N to N association for %s:', $this->getModelName()));
        }
        foreach ($this->getTableM2MRelations() as $relation) {
            $this->getDocument()->addLog(sprintf('  Writing setter/getter for N <=> N "%s"', $relation['refTable']->getModelName()));

            if (!($options = $this->getAssociationThrough($relation))) {
                continue;
            }

            $writer
                ->write('// N <=> N')
                ->write(
                    "%s.belongsToMany(sequelize.models.%s, %s)$semicolon",
                    $varName,
                    $relation['refTable']->getModelName(),
                    $this->getJSObject($options)
                );
        }

        return $this;
    }

    protected function getAssociationOne($fk, $as, $inverse = null, $constrained = null)
    {
        return [
            'foreignKey' => [
                'name' => $this->getNaming($fk->getLocal()->getColumnName()),
                'field' => $fk->getLocal()->getColumnName(),
                'allowNull' => !$fk->getLocal()->isNotNull(),
            ],
            'onUpdate' => !$constrained ? null : $fk->getParameter('updateRule'),
            'onDelete' => !$constrained ? null : $fk->getParameter('deleteRule'),
            'targetKey' => $this->getNaming($fk->getForeign()->getColumnName()),
            'as' => $as,
            'constraints' => !$constrained ? false : null,
        ];
    }

    protected function getAssociationMany($fk, $as, $constrained = null)
    {
        return [
            'foreignKey' => [
                'name' => $this->getNaming($fk->getLocal()->getColumnName()),
                'field' => $fk->getLocal()->getColumnName(),
                'allowNull' => !$fk->getLocal()->isNotNull(),
            ],
            'onUpdate' => !$constrained ? null : $fk->getParameter('updateRule'),
            'onDelete' => !$constrained ? null : $fk->getParameter('deleteRule'),
            'targetKey' => $this->getNaming($fk->getForeign()->getColumnName()),
            'as' => $as,
            'constraints' => !$constrained ? false : null,
        ];
    }

    protected function getAssociationThrough($relation)
    {
        return [
            'through' => $relation['reference']->getOwningTable()->getRawTableName(),
            'foreignKey' => [
                'name' => $relation['reference']->getLocal()->getColumnName(),
            ],
            'onUpdate' => $relation['reference']->getParameter('updateRule'),
            'onDelete' => $relation['reference']->getParameter('deleteRule'),
            'targetKey' => $this->getNaming($relation['target']->getForeign()->getColumnName()),
            'as' => $this->pluralize($this->getNaming($relation['refTable']->getModelName(), $this->associationNaming, true)),
        ];
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
        $fk1->getReferencedTable()->setManyToManyRelation([
            'reference' => $fk1,
            'target' => $fk2,
            'refTable' => $fk2->getReferencedTable(),
        ]);

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
            if ($strategy === NamingStrategyConfiguration::CAMEL_CASE && $isModel) {
                $strategy = NamingStrategyConfiguration::PASCAL_CASE;
            }
        }

        return parent::getNaming($name, $strategy);
    }
}
