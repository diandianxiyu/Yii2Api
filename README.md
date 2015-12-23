ApiProject With  Yii 2 
============================



DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources
      data/               contains the sql backup



REQUIREMENTS
------------

The minimum requirement by this project template that your Web server supports PHP 5.4.0.


INSTALLATION
------------

### Git Clone

```
git clone https://github.com/diandianxiyu/Yii2Api.git
```

###  Import database file

```
data/app.sql
```

### Edit config/db.php

```
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=local_app',
    'username' => 'root',
    'password' => 'xxx',
    'charset' => 'utf8',
];


```


