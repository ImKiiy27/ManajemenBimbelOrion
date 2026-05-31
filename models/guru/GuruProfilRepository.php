<?php
// ============================================================
// models/guru/GuruProfilRepository.php
// Query profil guru (roadmap)
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class GuruProfilRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }
}

