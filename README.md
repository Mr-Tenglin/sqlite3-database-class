# sqlite3-database-class
> 这项目可能会没头没尾, 就是写起来给自己通过 `Composer` 加载到项目中使用而已.

## 通过 `Composer` 安装
```
composer require tenglin/sqlite3-database-class
```

## 函数说明
```
public create(table, data) -- 加数据
public update(table, data [, array = [] ] ) -- 更新数据
public delete(table [, array = [] ] ) -- 删除数据
public detail(table [, array = [] ] ) -- 查询单数据
public items(table [, limit = [] [, &callback = [] [, columns = '*' ] ] ] ) -- 查询列表数据
public join(table, condition [, type = 'INNER' ] ) -- 多表连接
public where(prop, value [, operator = '='] ) -- and 查询
public orwhere(prop, value [, operator = '='] ) -- or 查询
public orderby(field [, direction = 'DESC'] ) -- 排序
```

## 使用 sqlite3-database-class
### 接入sqlite3类
```php
require_once('sqlite3db.php');
```
### 调用sqlite3类
```php
$db = new SQLite3DB('./data.db', 'ejcms_');

// 或是

$db = new SQLite3DB();
$db->dbfile = './data.db';
$db->prefix = 'ejcms_';
```
### 加数据
```php
$id = $db->create('user', [
    'name' => 'sqlite'
]);
// INSERT INTO [ejcms_user] (name) VALUES ('sqlite');
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
// INSERT INTO [ejcms_user] (name) VALUES ('sqlite');
// INSERT INTO ......
print_r($ids);
```
### 更新数据
```php
$db->update('user', [
    'name' => 'database'
], ['id' => '1']);
// UPDATE ejcms_user SET [name] = 'database' WHERE id = '1';

// 或是

$db->where('id', [1, 2, 3], 'in');
$db->update('user', [
    'name' => 'database'
]);
// UPDATE ejcms_user SET [name] = 'database' WHERE id in ('1', '2', '3');
```
### 删除数据
```php
$db->delete('user', ['id' => '1']);
// DELETE FROM [ejcms_user] WHERE id = '1';

// 或是

$db->where('id', [1, 2, 3], 'in');
$db->delete('user');
// DELETE FROM [ejcms_user] WHERE id in ('1', '2', '3');
```
### 查询单条数据
```php
$result = $db->detail('user', ['id' => '1']);
// SELECT * FROM [ejcms_user] WHERE id = '1' LIMIT 1;
print_r($result);

// 或是

$db->join('user1 b', 'a.id = b.id', 'left');
$result = $db->detail('user a', ['a.id' => '1']);
// SELECT * FROM [ejcms_user a] left JOIN user1 b ON a.id = b.id WHERE a.id = '1' LIMIT 1;
print_r($result);

// 或是

$tables = [];
$tables['table'] = 'user a';
$tables['join'] = ['table' => 'user1 b', 'condition' => 'a.id = b.id', 'type' => 'left'];
$result = $db->detail($tables, ['a.id' => '1']);
// SELECT * FROM [ejcms_user a] left JOIN user1 b ON a.id = b.id WHERE a.id = '1' LIMIT 1;
print_r($result);
```
### 查询列表
```php
$db->where('cid', 1);
$result = $db->items('user');
// SELECT * FROM [ejcms_user] WHERE cid = '1';
print_r($result);
```

// 或是

```php
$tables = [];
$tables['table'] = 'user a';
$tables['join'] = ['table' => 'user1 b', 'condition' => 'a.id = b.id', 'type' => 'left'];
$db->where('a.cid', 1);
$db->where('a.name', 'sql%', 'like');
$db->orwhere('a.name', 'data');
$db->orderby('a.id', 'desc');
$result = $db->items($tables);
// SELECT * FROM [ejcms_user a] left JOIN user1 b ON a.id = b.id WHERE a.cid = '1' AND a.name like 'sql%' OR a.name = 'data' ORDER BY a.id DESC;
print_r($result);
```
