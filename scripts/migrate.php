<?php
/**
 * Миграция со старой системы (gts_balance_*) на новую (orgstructure_*).
 *
 * --------------------------------------------------------------------
 * СПОСОБ 1: MODX Console (System → Console в manager'е)
 * --------------------------------------------------------------------
 * Скопировать блок «=== BEGIN CONSOLE BLOCK ===» ниже целиком
 * (без открывающего <?php), вставить в Console и нажать Запуск.
 * $modx уже доступен в области видимости Console.
 *
 * --------------------------------------------------------------------
 * СПОСОБ 2: CLI (из git-bash или cmd на сервере)
 * --------------------------------------------------------------------
 *   "/v/OSPanel/modules/PHP-8.3/PHP/php.exe" \
 *       "v:/OSPanel/home/modx.pl/public/Extras/OrgStructure/scripts/migrate.php"
 */

if (php_sapi_name() === 'cli') {
    define('MODX_API_MODE', true);
    // scripts → OrgStructure → Extras → public → modx.pl, dirname x4
    require dirname(__DIR__, 4) . '/public/index.php';
    /** @var modX $modx */
    $modx->getService('error', 'error.modError');
    $modx->setLogLevel(modX::LOG_LEVEL_INFO);
    $modx->setLogTarget('ECHO');
}

// === BEGIN CONSOLE BLOCK ===
/** @var modX $modx */
$service = $modx->getService(
    'OrgStructure',
    'OrgStructure',
    MODX_CORE_PATH . 'components/orgstructure/model/'
);
if (!$service) {
    echo "Сервис OrgStructure не загружен\n";
    return;
}

$result = $service->migrateFromOld();

echo "=== Результат миграции ===\n";
echo "Status:  " . ($result['success'] ? 'OK' : 'FAIL') . "\n";
echo "Message: " . $result['message'] . "\n\n";

$data = $result['data'];
echo "Очищено перед миграцией:\n";
echo "  osEmployee:                       " . ($data['cleared_employees'] ?? 0) . "\n";
echo "  osTree (employee узлы):           " . ($data['cleared_emp_trees'] ?? 0) . "\n";
echo "  gts_departarment_staff_links:     " . ($data['cleared_links'] ?? 0) . "\n\n";

echo "Должности:\n";
echo "  создано:    " . $data['posts_created'] . "\n";
echo "  обновлено:  " . $data['posts_updated'] . "\n\n";

echo "Сотрудники:\n";
echo "  создано:    " . $data['staff_created'] . "\n";
echo "  обновлено:  " . $data['staff_updated'] . "\n";
echo "  пропущено:  " . $data['staff_skipped'] . " (Якутия / неактивные dept / без узла в дереве)\n\n";

echo "Узлы дерева:\n";
echo "  создано:    " . $data['tree_created'] . "\n";
echo "  обновлено:  " . $data['tree_updated'] . "\n";

if (!empty($data['errors'])) {
    echo "\nОшибки (" . count($data['errors']) . "):\n";
    foreach ($data['errors'] as $err) {
        echo "  - {$err}\n";
    }
}
// === END CONSOLE BLOCK ===
