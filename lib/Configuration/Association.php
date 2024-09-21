<?php

/*
 * The MIT License
 *
 * Copyright (c) 2023-2024 Toha <tohenk@yahoo.com>
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
 * Generate association method to define associations between models.
 * Each model then has a `associate()` method which can be called
 * to associate the models `Sequelize 6+`.
 *
 * To use the association is described as follows:
 *
 * ```javascript
 * const { Sequelize } = require('sequelize');
 *
 * const sequelize = new Sequelize({...});
 * const MyModel1 = sequelize.import('./path/to/MyModel');
 * const MyModel2 = sequelize.import('./path/to/MyMode2');
 * ...
 *
 * MyModel1.associate();
 * MyModel2.associate();
 * ...
 * ```
 *
 * @author Toha <tohenk@yahoo.com>
 * @config generateAssociationMethod
 * @label Generate association method
 */
class Association extends Configuration
{
    protected function initialize()
    {
        $this->category = 'sequelizeConfiguration';
        $this->defaultValue = false;
    }
}
