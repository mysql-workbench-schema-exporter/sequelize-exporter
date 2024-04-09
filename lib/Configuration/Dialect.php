<?php

/*
 * The MIT License
 *
 * Copyright (c) 2024 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Sequelize\Configuration;

use MwbExporter\Configuration\Configuration;

/**
 * Sequelize database dialect used for model definition. Some database dialect
 * can produce different model definition, choose the right dialect to target
 * for model generation.
 *
 * @author Toha <tohenk@yahoo.com>
 * @config databaseDialect
 * @label Database dialect
 */
class Dialect extends Configuration
{
    public const NONE = 'none';
    public const DB2 = 'db2';
    public const IBMI = 'ibmi';
    public const MARIADB = 'mariadb';
    public const MSSQL = 'mssql';
    public const MYSQL = 'mysql';
    public const POSTGRES = 'postgres';
    public const SNOWFLAKE = 'snowflake';
    public const SQLITE = 'sqlite';

    protected function initialize()
    {
        $this->category = 'sequelizeConfiguration';
        $this->defaultValue = static::NONE;
        $this->choices = [
            static::NONE,
            static::DB2,
            static::IBMI,
            static::MARIADB,
            static::MSSQL,
            static::MYSQL,
            static::POSTGRES,
            static::SNOWFLAKE,
            static::SQLITE,
        ];
    }
}
