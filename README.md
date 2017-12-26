# SimpleDB

Simple Database Management System (Non SQL) for PHP


## How to use?

- Make Instance Variable (require)
``` php
$db = new Safflower\SimpleDB();
$db = new Safflower\SimpleDB(__DIR__.'/@database');
```

- Set Database Path (require)
``` php
$db->setBasePath(__DIR__.'/@database');
```

- Create Table
``` php
$db->createTable('users');
$db->createTable('users', 'no');
$db->createTable('users', ['no', 'username', 'password']);
```

- Drop Table
``` php
$db->dropTable('users');
```

- Truncate Table
``` php
$db->truncateTable('users');
```

- Insert Row
``` php
$db->insertRow('users', ['no' => '1', 'username' => 'admin', 'password' => '12345']);
```

- Select Row
``` php
$db->selectRow('users');
$db->selectRow('users', 'username');
$db->selectRow('users', ['no', 'username', 'password']);
$db->selectRow('users', ['no', 'username', 'password'], 'callback');
```

- Delete Row
``` php
$db->deleteRow('users');
$db->deleteRow('users', 'callback');
```

- Update Row
``` php
$db->updateRow('users', ['password' => '12345']);
$db->updateRow('users', ['password' => '12345'], 'callback');
```
