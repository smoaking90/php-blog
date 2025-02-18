<?php

namespace Core;

use PDO;

class Model{
    protected static $table;

    public static function all(): array{
        $db = App::get('database');
        $results = $db->query("SELECT * FROM " . static::$table)->fetchAll(PDO::FETCH_ASSOC);
        return array_map([static::class, 'createFromArray'], $results);
    }

    public static function find(mixed $id): static | null{
        $db = App::get('database');
        $result = $db->query("SELECT * FROM " . static::$table . " WHERE id = ?", [$id])
        ->fetch(PDO::FETCH_ASSOC);
        return $result ? static::createFromArray($result) : null;
    }

    public static function create(array $data): static {
        $db = App::get('database');
        // get column names from data
        $columns = implode(', ', array_keys($data));
        // -> ?, ?, ?, ? for count of array
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        // -> id, title, created_at, content
        $sql = "INSERT INTO " . static::$table . " ($columns) VALUES ($placeholders)";
        $db->query($sql, array_values($data));
        return static::find($db->lastInsertId());
    }

    protected static function createFromArray(array $data): static{
        $model = new static();
        foreach($data as $key => $value){
            if(property_exists($model, $key)){
                $model->$key = $value;
            }
        }
        return $model;
    }
}