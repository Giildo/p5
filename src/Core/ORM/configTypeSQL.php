<?php

$numeric = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double', 'real', 'bit', 'boolean', 'serial'];
$date = ['date', 'datetime', 'timestamp', 'time', 'year'];
$string = ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'binary', 'varbinary', 'tinyblob', 'mediumblob', 'blob', 'longblob', 'enum', 'set'];
$spatial = ['geometry', 'point', 'linestring', 'polygon', 'multipoint', 'multilinestring', 'multipolygon', 'geometrycollection'];

return [
    'SQL.types' => array_merge($numeric, $date, $string, $spatial)
];