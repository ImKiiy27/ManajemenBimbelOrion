<?php
// ============================================================
// models/siswa/SiswaNilaiRepository.php
// Query nilai siswa (roadmap)
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class SiswaNilaiRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }
}

