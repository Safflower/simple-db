# Simple DB

It's simple database management system for PHP.

Simple DB can be controlled by methods of PHP class without using SQL.


## How to use?

- Make a Instance Variable (require)
``` php
# Just make a instance variable
$db = new Safflower\SimpleDB();
# Make instance variable and set a database path
$db = new Safflower\SimpleDB(__DIR__.'/@database');
```

- Set a Database Path (require)
``` php
# Set a database path
$db->setBasePath(__DIR__.'/@database');
```

- Check the Database Path
``` php
# Check the database path if it's exists
$isValid = $db->checkBasePath();
```

- Create a Table
``` php
# Create a table with not column
$db->createTable('users');
# Create a table with one column
$db->createTable('users', 'no');
# Create a table with three columns
$db->createTable('users', ['no', 'username', 'password']);
```

- Drop the Table
``` php
# Drop the table
$db->dropTable('users');
```

- Truncate the Table
``` php
# Truncate the table (This method is faster than deleteRow)
$db->truncateTable('users');
```

- Insert a Row
``` php
# Insert a row
$db->insertRow('users', ['no' => '1', 'username' => 'admin', 'password' => '12345']);
```

- Select Rows
``` php
# Select rows with all columns
$db->selectRow('users');
# Select rows with one column
$db->selectRow('users', 'username');
# Select rows with three columns
$db->selectRow('users', ['no', 'username', 'password']);
# Filter in callback function and select rows with three columns
$db->selectRow('users', ['no', 'username', 'password'], 'callback');
# Filter in callback function and limit count of result and select rows with three columns
$db->selectRow('users', ['no', 'username', 'password'], 'callback', 1);
```

- Delete Rows
``` php
# Delete all rows
$db->deleteRow('users');
# Filter in callback function and delete rows
$db->deleteRow('users', 'callback');
# Filter in callback function and limit count of result and delete rows
$db->deleteRow('users', 'callback', 1);
```

- Update Rows
``` php
# Update rows
$db->updateRow('users', ['password' => '12345']);
# Filter in callback function and update rows
$db->updateRow('users', ['password' => '12345'], 'callback');
# Filter in callback function and limit count of result and update rows
$db->updateRow('users', ['password' => '12345'], 'callback', 1);
```
