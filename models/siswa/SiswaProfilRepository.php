<?php
// ============================================================
// models/siswa/SiswaProfilRepository.php
// Query profil siswa (roadmap)
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class SiswaProfilRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }
}

