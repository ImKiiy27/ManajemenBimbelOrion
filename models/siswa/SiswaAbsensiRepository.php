<?php
// ============================================================
// models/siswa/SiswaAbsensiRepository.php
// Query absensi siswa (roadmap)
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class SiswaAbsensiRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }
}

