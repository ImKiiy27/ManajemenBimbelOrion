<?php
// ============================================================
// models/admin/AdminUserModel.php
// Compatibility Facade (legacy).
//
// Tanggung jawab lama dipecah ke:
// - models/admin/AdminUserRepository.php
// - models/admin/AdminGuruRepository.php
// - models/admin/AdminSiswaRepository.php
// ============================================================

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../IdCounterModel.php';
require_once __DIR__ . '/AdminUserRepository.php';
require_once __DIR__ . '/AdminGuruRepository.php';
require_once __DIR__ . '/AdminSiswaRepository.php';

class AdminUserModel {

  private AdminUserRepository $userRepository;
  private AdminGuruRepository $guruRepository;
  private AdminSiswaRepository $siswaRepository;

  public function __construct(?PDO $db = null, ?IdCounterModel $idCounterModel = null) {
    $db = $db ?? getDB();
    $idCounterModel = $idCounterModel ?? new IdCounterModel($db);

    $this->userRepository = new AdminUserRepository($db, $idCounterModel);
    $this->guruRepository = new AdminGuruRepository($db);
    $this->siswaRepository = new AdminSiswaRepository($db, $idCounterModel);
  }

  public function findById(string $id): array|false {
    return $this->userRepository->findById($id);
  }

  public function unlock(string $userId): bool {
    return $this->userRepository->unlock($userId);
  }

  public function getAllUsersWithDetail(): array {
    return $this->userRepository->getAllUsersWithDetail();
  }

  public function getSiswaList(): array {
    return $this->siswaRepository->getSiswaList();
  }

  public function getGuruList(): array {
    return $this->guruRepository->getGuruList();
  }

  public function getSiswaOptions(): array {
    return $this->siswaRepository->getSiswaOptions();
  }

  public function getGuruOptions(): array {
    return $this->guruRepository->getGuruOptions();
  }

  public function getActiveMapelOptions(): array {
    return $this->guruRepository->getActiveMapelOptions();
  }

  public function updateGuruProfile(string $guruId, string $nama, string $mapelId): array {
    return $this->guruRepository->updateGuruProfile($guruId, $nama, $mapelId);
  }

  public function getMapelIdsBySiswa(string $siswaId): array {
    return $this->siswaRepository->getMapelIdsBySiswa($siswaId);
  }

  public function updateSiswaProfileAndMapel(string $siswaId, string $nama, string $kelas, array $mapelIds): array {
    return $this->siswaRepository->updateSiswaProfileAndMapel($siswaId, $nama, $kelas, $mapelIds);
  }

  public function countActiveSiswaMapel(): int {
    return $this->siswaRepository->countActiveSiswaMapel();
  }

  public function getUserWithDetail(string $userId): array|false {
    return $this->userRepository->getUserWithDetail($userId);
  }

  public function createUser(array $data): array {
    return $this->userRepository->createUser($data);
  }

  public function updateUser(string $userId, array $data): array {
    return $this->userRepository->updateUser($userId, $data);
  }

  public function deleteUser(string $userId): array {
    return $this->userRepository->deleteUser($userId);
  }
}
