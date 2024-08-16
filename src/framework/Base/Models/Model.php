<?php

namespace Framework\Models;

use Framework\CDatabase;
use Framework\Validators\ModelValidator;

abstract class Model
{
    protected array $data;
    protected array $fillable = [];
    protected static string $table;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public static function getById(int $id): self
    {
        $query = "SELECT * FROM " . static::$table . " WHERE id = :id";
        $stmt = CDatabase::getInstanse()->connection->prepare($query);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return new static($data);
    }

    public static function getBy(array $criteria): array
    {
        $query = "SELECT * FROM " . static::$table . " WHERE ";
        $conditions = [];
        $params = [];

        foreach ($criteria as $key => $value) {
            $conditions[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        $query .= implode(" AND ", $conditions);
        $stmt = CDatabase::getInstanse()->connection->prepare($query);
        $stmt->execute($params);
        $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return new static($data);
    }

    public static function create(array $data): self
    {
        $fields = array_keys($data);
        $placeholders = array_map(function ($field) {
            return ":{$field}";
        }, $fields);
        $query = "INSERT INTO " . static::$table . " (" . implode(',', $fields) . ") 
        VALUES (" . implode(',', $placeholders) . ")";
        $stmt = CDatabase::getInstanse()->connection->prepare($query);
        $res = $stmt->execute($data);

        if (!$res) {
            return null;
        }

        $data['id'] = CDatabase::getInstanse()->connection->lastInsertId();

        return new static($data);
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];

        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }

        $query = "UPDATE " . static::$table . " SET " . implode(',', $fields) . " WHERE id = :id";
        $data['id'] = $id;
        $stmt = CDatabase::getInstanse()->connection->prepare($query);

        return $stmt->execute($data);
    }

    public function save(): bool
    {
        $id = $this->getId();

        if ($id) {
            return self::update($this->getId(), $this->data);
        }

        return self::create($this->data);
    }

    public function delete(): bool
    {
        return static::deleteExec($this->getID());
    }

    public static function deleteIternal(int $id): bool
    {
        return static::deleteExec($id);
    }

    protected static function deleteExec(int $id): bool
    {
        $query = "DELETE FROM " . static::$table . " WHERE id = :id";
        $stmt = CDatabase::getInstanse()->connection->prepare($query);

        return $stmt->execute(['id' => $id]);
    }

    public function __call(string $name, array $args)
    {
        if (strpos($name, 'get') !== 0) {
            return null;
        }

        $fieldName = strtolower(substr($name, 3));

        return $this->data[$fieldName] ?? null;
    }

    public function __get($name)
    {
        return $this->data[$name] ?? null;
    }

    public function __set($name, $value)
    {
        if (!in_array($name, $this->fillable)) {
            return;
        }

        $this->data[$name] = $value;
    }
}
