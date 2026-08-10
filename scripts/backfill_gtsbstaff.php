<?php
/**
 * Бэкфилл недостающих сотрудников в legacy gtsBStaff (gts_balance_staff) из OrgStructure.
 *
 * Зачем: osEmployee — мастер, а gtsBStaff держится в синке обратным триггером
 * OrgStructure::syncEmployeeToLegacy (связь по id 1:1). Если сотрудник создан в обход
 * триггера (импорт, до выката триггера, сбой) — в gtsBStaff его нет, и legacy-компоненты
 * (gtsBalance, табели, наряды, StaffWorkload, ВЫДАЧА ЗП) его не видят.
 *
 * Скрипт находит все osEmployee без пары в gtsBStaff и досоздаёт их, ВЫЗЫВАЯ ту же
 * syncEmployeeToLegacy — значит department_id резолвится идентично (resolveLegacyDeptId:
 * sync_to_zeh в пути → Цех Паша(1), иначе Офис(2)) и создаётся gts_departarment_staff_links.
 * Идемпотентно: existing gtsBStaff не трогает (только отсутствующие).
 *
 * Запуск:
 *   php backfill_gtsbstaff.php          # DRY-RUN (только показывает и пишет лог)
 *   php backfill_gtsbstaff.php apply     # реально создаёт недостающие gtsBStaff + link
 *
 * Лог пишется в scripts/backfill_gtsbstaff_<дата>.log (список недостающих + результат).
 */

if (!defined('MODX_API_MODE')) define('MODX_API_MODE', true);

// Ищем config.core.php вверх по дереву — скрипт работает из любого места под сайтом.
$dir = __DIR__;
while ($dir && !file_exists($dir . '/config.core.php')) {
    $parent = dirname($dir);
    if ($parent === $dir) { $dir = ''; break; }
    $dir = $parent;
}
if (!$dir) { fwrite(STDERR, "config.core.php не найден вверх по дереву от " . __DIR__ . "\n"); exit(1); }
require $dir . '/config.core.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = new modX();
$modx->initialize('web');
$modx->getService('error', 'error.modError');
$modx->setLogTarget('ECHO');

/** @var OrgStructure $service */
$service = $modx->getService('OrgStructure', 'OrgStructure', MODX_CORE_PATH . 'components/orgstructure/model/');
if (!$service) { fwrite(STDERR, "Сервис OrgStructure не загружен\n"); exit(1); }
// gtsbalance-пакет (gtsBStaff) подключается конструктором OrgStructure; подстрахуемся.
$modx->addPackage('gtsbalance', MODX_CORE_PATH . 'components/gtsbalance/model/');

$apply = in_array('apply', array_map('strtolower', array_slice($argv, 1)), true);

// Лог в файл + stdout
$logFile = __DIR__ . '/backfill_gtsbstaff_' . date('Ymd_His') . '.log';
$fh = fopen($logFile, 'w');
$log = function ($line) use ($fh) {
    fwrite(STDOUT, $line . "\n");
    if ($fh) fwrite($fh, $line . "\n");
};

$log("== Бэкфилл gtsBStaff из osEmployee ==");
$log("Дата: " . date('Y-m-d H:i:s') . "  Режим: " . ($apply ? 'APPLY (запись в БД)' : 'DRY-RUN (только показ)'));
$log("Лог-файл: " . $logFile);
$log("");

// Недостающие: osEmployee без пары в gtsBStaff по id (связь 1:1)
$empTable   = $modx->getTableName('osEmployee');      // modx_orgstructure_employees
$staffTable = $modx->getTableName('gtsBStaff');        // modx_gts_balance_staff
$sql = "SELECT e.id
          FROM {$empTable} e
          LEFT JOIN {$staffTable} s ON s.id = e.id
         WHERE s.id IS NULL
         ORDER BY e.id";
$missingIds = $modx->query($sql)->fetchAll(PDO::FETCH_COLUMN);

$log("Недостающих в gtsBStaff: " . count($missingIds));
$log("");
$log(sprintf("%-6s %-8s %-9s %-8s %s", 'id', 'active', 'user_id', 'post_id', 'name'));
$log(str_repeat('-', 70));

$done = 0; $fail = 0;
foreach ($missingIds as $empId) {
    $empId = (int)$empId;
    /** @var xPDOObject $emp */
    $emp = $modx->getObject('osEmployee', $empId);
    if (!$emp) { $log(sprintf("%-6d  -- osEmployee исчез, пропуск", $empId)); continue; }

    $log(sprintf(
        "%-6d %-8d %-9d %-8d %s",
        $empId,
        (int)$emp->get('active'),
        (int)$emp->get('user_id'),
        (int)$emp->get('post_id'),
        (string)$emp->get('name')
    ));

    if (!$apply) continue;

    // Вызываем тот же обратный триггер, что и при обычном CRUD — идентичная логика
    // (id 1:1, resolveLegacyDeptId, создание gts_departarment_staff_links).
    $params = ['type' => 'after', 'method' => 'update', 'object' => $emp];
    $resp = $service->syncEmployeeToLegacy($params);

    $staff = $modx->getObject('gtsBStaff', $empId);
    if ($staff) {
        $done++;
        $log(sprintf(
            "        ↳ создан gtsBStaff id=%d dept=%s post=%s active=%d",
            $empId,
            var_export($staff->get('department_id'), true),
            var_export($staff->get('post_id'), true),
            (int)$staff->get('active')
        ));
    } else {
        $fail++;
        $log(sprintf("        ↳ НЕ создан (resp: %s)", isset($resp['message']) ? $resp['message'] : 'нет ответа'));
    }
}

$log("");
if ($apply) {
    $log("Итог: создано {$done}, ошибок {$fail}, всего недостающих было " . count($missingIds));
} else {
    $log("Итог (DRY-RUN): нашлось " . count($missingIds) . " недостающих. Запусти с 'apply' чтобы создать.");
}
if ($fh) fclose($fh);
