<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2023 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Node;

use MwbExporter\Configuration\Indentation as IndentationConfiguration;
use MwbExporter\Formatter\Formatter as BaseFormatter;
use MwbExporter\Formatter\Node\Configuration\PackageName as PackageNameConfiguration;
use MwbExporter\Formatter\Node\Configuration\TableProp as TablePropConfiguration;

abstract class Formatter extends BaseFormatter
{
    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\Formatter::init()
     */
    protected function init()
    {
        parent::init();
        $this->getConfigurations()
            ->add(new PackageNameConfiguration())
            ->add(new TablePropConfiguration())
            ->merge([IndentationConfiguration::class => 4], true)
        ;
    }

    public function getVersion()
    {
        return 'dev';
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\Formatter::createDatatypeConverter()
     */
    protected function createDatatypeConverter()
    {
        return new DatatypeConverter();
    }

    public function getFileExtension()
    {
        return 'js';
    }

    /**
     * Get common table property.
     *
     * @return array
     */
    public function getTableProp()
    {
        $prop = [
            'timestamps' => false,
            'underscored' => false,
            'syncOnAssociation' => false
        ];
        if (is_readable($filename = $this->getConfig(TablePropConfiguration::class)->getValue())) {
            if ($commonProp = json_decode(file_get_contents($filename), true)) {
                $prop = array_merge($prop, $commonProp);
            }
        }

        return $prop;
    }

    public static function getDocDir()
    {
        return __DIR__.'/../docs';
    }

    /**
     * Get configuration scope.
     *
     * @return string
     */
    public static function getScope()
    {
        return 'Sequelize Global';
    }
}
