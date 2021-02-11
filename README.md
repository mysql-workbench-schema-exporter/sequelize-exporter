# README

![Build Status](https://github.com/mysql-workbench-schema-exporter/node-exporter/actions/workflows/continuous-integration.yml/badge.svg)
[![Latest Stable Version](https://poser.pugx.org/mysql-workbench-schema-exporter/node-exporter/v/stable.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/node-exporter)
[![Total Downloads](https://poser.pugx.org/mysql-workbench-schema-exporter/node-exporter/downloads.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/node-exporter) 
[![License](https://poser.pugx.org/mysql-workbench-schema-exporter/node-exporter/license.svg)](https://packagist.org/packages/mysql-workbench-schema-exporter/node-exporter)

This is an exporter to convert [MySQL Workbench](http://www.mysql.com/products/workbench/) Models (\*.mwb) to Nodejs Sequelize Schema.

## Prerequisites

  * PHP 7.2+
  * Composer to install the dependencies

## Installation

```
php composer.phar require --dev mysql-workbench-schema-exporter/node-exporter
```

This will install the exporter and also require [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter).

You then can invoke the CLI script using `vendor/bin/mysql-workbench-schema-export`.

## Formatter Setup Options

Additionally to the [common options](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter#configuring-mysql-workbench-schema-exporter) of mysql-workbench-schema-exporter these options are supported:

### Sequelize 5

Currently, no special options can be configured for Sequelize Model.

### Sequelize 6

  * `useSemicolons`

    Whether or not to add semicolons to ends of lines (standard Eslint compliance).

    Default is `true`.
  
  * `generateAssociationMethod`

    Generate an association method to define associations between models.

    You may instantiate your model this way :

    ```javascript
    const Sequelize = require('sequelize');

    const sequelize = new Sequelize({...});
    const MyModel1 = sequelize.import('./path/to/MyModel');
    const MyModel2 = sequelize.import('./path/to/MyMode2');
    ...

    MyModel1.associate();
    MyModel2.associate();
    ...

    ```
    Default is `false`.

  * `generateForeignKeysFields`

    Whether or not to generate foreign keys fields and indexes. You could want to delegate it to association method (one could experiment relations creation order problems when it is present).

    Default is `true`.

  * `useTimestamps`

    Sets the models `timestamps` property.

    Default is `false`.

## Command Line Interface (CLI)

See documentation for [mysql-workbench-schema-exporter](https://github.com/mysql-workbench-schema-exporter/mysql-workbench-schema-exporter#command-line-interface-cli)

## Nodejs Usage Example

### Sequelize 5

```javascript
const Sequelize = require('sequelize');

const sequelize = new Sequelize({...});
const MyModel = sequelize.import('./path/to/MyModel');

// do something with MyModel
MyModel.findOne({...}).then((res) => {...});
```

### Sequelize 6

```javascript
const Sequelize = require('sequelize');

const sequelize = new Sequelize({...});
const MyModel = require('./path/to/MyModel')(sequelize);

// do something with MyModel
MyModel.findOne({...}).then((res) => {...});
```

## Links

  * [MySQL Workbench](http://wb.mysql.com/)
