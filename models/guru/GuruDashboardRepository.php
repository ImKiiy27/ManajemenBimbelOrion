<?php
// ============================================================
// models/guru/GuruDashboardRepository.php
// Query dashboard guru (roadmap)
// ============================================================

require_once __DIR__ . '/../../config/database.php';

class GuruDashboardRepository
{
  private PDO $db;

  public function __construct(?PDO $db = null)
  {
    $this->db = $db ?? getDB();
  }
}

