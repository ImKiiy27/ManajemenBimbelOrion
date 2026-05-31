<?php
// ============================================================
// models/IdCounterModel.php
// Tugasnya: generate ID berurutan lintas tabel
// ============================================================

require_once __DIR__ . '/../config/database.php';

class IdCounterModel {

  private PDO $db;

  public function __construct(?PDO $db = null) {
    $this->db = $db ?? getDB();
  }

  public function generateId(string $tabel, string $prefix): string {
    $manageTxn = !$this->db->inTransaction();
    if ($manageTxn) {
      $this->db->beginTransaction();
    }

    try {
      $stmt = $this->db->prepare("SELECT last_id, prefix FROM id_counter WHERE tabel = ? FOR UPDATE");
      $stmt->execute([$tabel]);
      $row = $stmt->fetch();

      if ($row) {
        $next   = (int)$row['last_id'] + 1;
        $prefix = (string)$row['prefix'];
        $stmt   = $this->db->prepare("UPDATE id_counter SET last_id = ? WHERE tabel = ?");
        $stmt->execute([$next, $tabel]);
      } else {
        $next = 1;
        $stmt = $this->db->prepare("INSERT INTO id_counter (tabel, prefix, last_id) VALUES (?, ?, ?)");
        $stmt->execute([$tabel, $prefix, $next]);
      }

      if ($manageTxn) {
        $this->db->commit();
      }

      return $prefix . str_pad((string)$next, 3, '0', STR_PAD_LEFT);
    } catch (Throwable $e) {
      if ($manageTxn && $this->db->inTransaction()) {
        $this->db->rollBack();
      }
      throw $e;
    }
  }
}
