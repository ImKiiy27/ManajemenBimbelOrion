<?php
// ============================================================
// models/nilai/NilaiModel.php
// Compatibility Facade untuk nilai
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';
require_once __DIR__ . '/NilaiQueryService.php';
require_once __DIR__ . '/NilaiCommandService.php';

class NilaiModel {

  private NilaiQueryService $queryService;
  private NilaiCommandService $commandService;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null) {
    $db = $db ?? getDB();
    $idCounterModel = $idCounterModel ?? new IdCounterModel($db);

    $this->queryService = new NilaiQueryService($db);
    $this->commandService = new NilaiCommandService($db, $idCounterModel);
  }

  public function getNilaiByGuru(string $guruId): array {
    return $this->queryService->getNilaiByGuru($guruId);
  }

  public function getNilaiByJadwal(string $jadwalId): array {
    return $this->queryService->getNilaiByJadwal($jadwalId);
  }

  public function getNilaiById(string $nilaiId): array|false {
    return $this->queryService->getNilaiById($nilaiId);
  }

  public function getJadwalForNilaiInput(string $guruId): array {
    return $this->queryService->getJadwalForNilaiInput($guruId);
  }

  public function createNilai(array $data): array {
    return $this->commandService->createNilai($data);
  }

  public function updateNilai(string $nilaiId, array $data): array {
    return $this->commandService->updateNilai($nilaiId, $data);
  }

  public function deleteNilai(string $nilaiId): array {
    return $this->commandService->deleteNilai($nilaiId);
  }
}

