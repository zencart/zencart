# Laravel Insert On Duplicate Key And Insert Ignore

This package provides macros to run INSERT ... ON DUPLICATE KEY UPDATE and INSERT IGNORE queries on models and pivot tables with Laravel's ORM Eloquent using MySql or MariaDB.

## Installation

Install this package with composer.

```sh
composer require guidocella/eloquent-insert-on-duplicate-key
```

If you don't use Package Auto-Discovery yet add the service provider to your Package Service Providers in `config/app.php`.

```php
InsertOnDuplicateKey\InsertOnDuplicateKeyServiceProvider::class,
```

## Usage

### Models

Call `insertOnDuplicateKey` or `insertIgnore` from a model with the array of data to insert in its table.

```php
$data = [
    ['id' => 1, 'name' => 'name1', 'email' => 'user1@email.com'],
    ['id' => 2, 'name' => 'name2', 'email' => 'user2@email.com'],
];

User::insertOnDuplicateKey($data);

User::insertIgnore($data);
```

#### Customizing the ON DUPLICATE KEY UPDATE clause

##### Update only certain columns

If you want to update only certain columns, pass them as the 2nd argument.

```php
User::insertOnDuplicateKey([
    'id'    => 1,
    'name'  => 'new name',
    'email' => 'foo@gmail.com',
], ['name']);
// The name will be updated but not the email.
```

##### Update with custom values

You can customize the value with which the columns will be updated when a row already exists by passing an associative array.

In the following example, if a user with id = 1 doesn't exist, it will be created with name = 'created user'. If it already exists, it will be updated with name = 'updated user'.

```php
User::insertOnDuplicateKey([
    'id'    => 1,
    'name'  => 'created user',
], ['name' => 'updated user']);
```

The generated SQL is:

```sql
INSERT INTO `users` (`id`, `name`) VALUES (1, "created user") ON DUPLICATE KEY UPDATE `name` = "updated user"
```

You may combine key/value pairs and column names in the 2nd argument to specify the columns to update with a custom literal or expression or with the default `VALUES(column)`. For example:

```php
User::insertOnDuplicateKey([
    'id'       => 1,
    'name'     => 'created user',
    'email'    => 'new@gmail.com',
    'password' => 'secret',
], ['name' => 'updated user', 'email']);
```

will generate

```sql
INSERT INTO `users` (`id`, `name`, `email`, `password`)
VALUES (1, "created user", "new@gmail.com", "secret")
ON DUPLICATE KEY UPDATE `name` = "updated user", `email` = VALUES(`email`)
```

### Pivot tables

Call `attachOnDuplicateKey` and `attachIgnore` from a `BelongsToMany` relation to run the inserts in its pivot table. You can pass the data in any of the formats accepted by `attach`.

```php
$pivotData = [
    1 => ['expires_at' => Carbon::today()],
    2 => ['expires_at' => Carbon::tomorrow()],
];

$user->roles()->attachOnDuplicateKey($pivotData);

$user->roles()->attachIgnore($pivotData);
```
