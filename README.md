# Simple DB

Simple Database Management System for PHP (Non SQL)


## How to use?

- Make Instance Variable (require)
``` php
# Just make instance variable
$db = new Safflower\SimpleDB();
# Make instance variable and set a database path
$db = new Safflower\SimpleDB(__DIR__.'/@database');
```

- Set Database Path (require)
``` php
# Set a database path
$db->setBasePath(__DIR__.'/@database');
```

- Create Table
``` php
# Create a table with not column
$db->createTable('users');
# Create a table with one column
$db->createTable('users', 'no');
# Create a table with three columns
$db->createTable('users', ['no', 'username', 'password']);
```

- Drop Table
``` php
# Drop the table
$db->dropTable('users');
```

- Truncate Table
``` php
# Truncate the table
$db->truncateTable('users');
```

- Insert Row
``` php
# Insert a row
$db->insertRow('users', ['no' => '1', 'username' => 'admin', 'password' => '12345']);
```

- Select Row
``` php
# Select rows with all columns
$db->selectRow('users');
# Select rows with one column
$db->selectRow('users', 'username');
# Select rows with three columns
$db->selectRow('users', ['no', 'username', 'password']);
# Filter in callback function and select rows with three columns
$db->selectRow('users', ['no', 'username', 'password'], 'callback');
```

- Delete Row
``` php
# Delete all rows
$db->deleteRow('users');
# Filter in callback function and delete rows
$db->deleteRow('users', 'callback');
```

- Update Row
``` php
# Update rows
$db->updateRow('users', ['password' => '12345']);
# Filter in callback function and update rows
$db->updateRow('users', ['password' => '12345'], 'callback');
```
