<?php

return [
    'class' => 'yii\db\Connection',
    'dsn'      => $_ENV['DBDSN'],
    'username' => $_ENV['DBUSERNAME'],
    'password' => $_ENV['DBPASSWORD'],
    'charset'=>'utf8mb4'

    // Schema cache options (for production environment)
    //'enableSchemaCache' => true,
    //'schemaCacheDuration' => 60,
    //'schemaCache' => 'cache',
];
