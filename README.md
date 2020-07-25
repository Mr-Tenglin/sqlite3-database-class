# sqlite3-database-class
> 这项目可能会没头没尾, 就是写起来给自己通过 `Composer` 加载到项目中使用而已.

## 函数说明
```
public create(table, data)
public update(table, data [, array = [] ] )
public delete(table [, array = [] ] )
public detail(table [, array = [] ] )
public items(table [, limit = [] [, &callback = [] [, columns = '*' ] ] ] )
public join(table, condition [, type = 'INNER' ] )
public where(prop, value [, operator = '='] )
public orwhere(prop, value [, operator = '='] )
public orderby(field [, direction = 'DESC'] )
```

## 使用 sqlite3-database-class
### 接入sqlite3类
```php
require_once('sqlite3db.php');
```
### 调用sqlite3类
```php
$db = new SQLite3DB('./data.db');
```
或是
```php
$db = new SQLite3DB();
$db->dbfile = './data.db';
```
### 加数据
```php
$id = $db->create('user', [
    'name' => 'sqlite'
]);
print_r($id);
```
### 批量加数据
```php
$ids = $db->create('user', [
    [
        'name' => 'sqlite'
    ], [
        'name' => 'abc'
    ], [
        'name' => 'sqlite3'
    ],
]);
print_r($ids);
```
### 更新数据
```php
$db->update('user', [
    'name' => 'database'
], ['id' => '1']);
```
或是
```php
$db->where('id', [1, 2, 3], 'in');
$db->update('user', [
    'name' => 'database'
]);
```
### 删除数据
```php
$db->delete('user', ['id' => '1']);
```
或是
```php
$db->where('id', [1, 2, 3], 'in');
$db->delete('user');
```
### 查询单条数据
```php
$db->detail('user', ['id' => '1']);
```
或是
```php
$db->join('user1 b', 'a.id = b.id', 'left');
$result = $db->detail('user a');
print_r($result);
```
或是
```php
$tables = [];
$tables['table'] = 'user a';
$tables['join'] = ['table' => 'user1 b', 'condition' => 'a.id = b.id', 'type' => 'left'];
$result = $db->detail($tables);
print_r($result);
```
### 查询列表
```php
$db->where('cid', 1);
$result = $db->items('user');
print_r($result);
```
