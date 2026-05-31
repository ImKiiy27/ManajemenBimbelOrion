<?php
// ============================================================
// models/guru/GuruAbsensiRepository.php
// Query/command absensi untuk guru (roadmap)
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class GuruAbsensiRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }
}

