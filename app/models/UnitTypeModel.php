<?php
// app/models/UnitTypeModel.php

require_once __DIR__ . '/../../config/database.php';

class UnitTypeModel {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll(bool $activeOnly = true): array {
        $sql = "SELECT ut.*, ui.image_path AS primary_image
                FROM unit_types ut
                LEFT JOIN unit_images ui
                  ON ui.unit_type_id = ut.unit_type_id AND ui.is_primary = 1
                WHERE 1=1";
        if ($activeOnly) $sql .= " AND ut.is_active = 1";
        $sql .= " ORDER BY ut.sort_order";

        $rows = $this->db->query($sql)->fetchAll();
        foreach ($rows as &$row) {
            $row['amenities'] = json_decode($row['amenities'], true) ?? [];
        }
        return $rows;
    }

    public function getBySlug($slug) {
        $stmt = $this->db->prepare(
            "SELECT * FROM unit_types WHERE slug = :slug AND is_active = 1"
        );
        $stmt->execute([':slug' => $slug]);
        $row = $stmt->fetch();
        if ($row) {
            $row['amenities'] = json_decode($row['amenities'], true) ?? [];
            $row['images']    = $this->getImages((int)$row['unit_type_id']);
        }
        return $row;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM unit_types WHERE unit_type_id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['amenities'] = json_decode($row['amenities'], true) ?? [];
            $row['images']    = $this->getImages($id);
        }
        return $row;
    }

    public function getImages(int $unitTypeId): array {
        $stmt = $this->db->prepare(
            "SELECT * FROM unit_images WHERE unit_type_id = :uid ORDER BY is_primary DESC, sort_order"
        );
        $stmt->execute([':uid' => $unitTypeId]);
        return $stmt->fetchAll();
    }

    public function update(int $id, array $data): void {
        $stmt = $this->db->prepare(
            "UPDATE unit_types SET name=:name, description=:desc, amenities=:amenities,
                max_guests=:mg, price_per_night=:price, updated_at=NOW()
             WHERE unit_type_id=:id"
        );
        $stmt->execute([
            ':name'      => $data['name'],
            ':desc'      => $data['description'],
            ':amenities' => json_encode($data['amenities']),
            ':mg'        => $data['max_guests'],
            ':price'     => $data['price_per_night'],
            ':id'        => $id,
        ]);
    }
}
