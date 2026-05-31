<?php
// ============================================================
// models/jadwal/JadwalModel.php
// Compatibility Facade (legacy).
//
// Tanggung jawab dipecah ke:
// - models/jadwal/JadwalQueryService.php
// - models/jadwal/JadwalCommandService.php
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';
require_once __DIR__ . '/JadwalQueryService.php';
require_once __DIR__ . '/JadwalCommandService.php';

class JadwalModel {

  private JadwalQueryService $queryService;
  private JadwalCommandService $commandService;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null) {
    $db = $db ?? getDB();
    $idCounterModel = $idCounterModel ?? new IdCounterModel($db);

    $this->queryService = new JadwalQueryService($db);
    $this->commandService = new JadwalCommandService($db, $idCounterModel);
  }

  public function getAllJadwal(): array {
    return $this->queryService->getAllJadwal();
  }

  public function getRelasiMapelAktif(): array {
    return $this->queryService->getRelasiMapelAktif();
  }

  public function getJadwalById(string $jadwalId): array|false {
    return $this->queryService->getJadwalById($jadwalId);
  }

  public function createJadwal(array $data): array {
    return $this->commandService->createJadwal($data);
  }

  public function updateJadwal(string $jadwalId, array $data): array {
    return $this->commandService->updateJadwal($jadwalId, $data);
  }

  public function deleteJadwal(string $jadwalId): array {
    return $this->commandService->deleteJadwal($jadwalId);
  }

  public function getJadwalByGuru(string $guruId): array {
    return $this->queryService->getJadwalByGuru($guruId);
  }

  public function getJadwalBySiswa(string $siswaId): array {
    return $this->queryService->getJadwalBySiswa($siswaId);
  }
}


