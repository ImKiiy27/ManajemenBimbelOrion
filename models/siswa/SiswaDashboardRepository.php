<?php
// ============================================================
// models/siswa/SiswaDashboardRepository.php
// Query dashboard siswa (roadmap)
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class SiswaDashboardRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }
}

