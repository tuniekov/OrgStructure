<?php

class OrgStructure
{
    /** @var modX $modx */
    public $modx;

    /** @var pdoFetch $pdoTools */
    public $pdo;

    /** @var array() $config */
    public $config = array();
    
    public $timings = [];
    protected $start = 0;
    protected $time = 0;
    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/orgstructure/';
        // $assetsUrl = MODX_ASSETS_URL . 'components/orgstructure/';

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            // 'processorsPath' => $corePath . 'processors/',
            // 'customPath' => $corePath . 'custom/',

            // 'connectorUrl' => $assetsUrl . 'connector.php',
            // 'assetsUrl' => $assetsUrl,
            // 'cssUrl' => $assetsUrl . 'css/',
            // 'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('orgstructure', $this->config['modelPath']);
        // Пакет gtsbalance даёт xPDO классы gtsBStaff, gtsBPost, gtsBDepartment,
        // gtsBDepartmentStaffLink — нужны для миграции и обратной синхронизации.
        $this->modx->addPackage('gtsbalance', MODX_CORE_PATH . 'components/gtsbalance/model/');
        //$this->modx->lexicon->load('orgstructure:default');

        if ($this->pdo = $this->modx->getService('pdoFetch')) {
            $this->pdo->setConfig($this->config);
        }
        $this->timings = [];
        $this->time = $this->start = microtime(true);
    }
    /**
     * Add new record to time log
     *
     * @param $message
     * @param null $delta
     */
    public function addTime($message, $delta = null)
    {
        $time = microtime(true);
        if (!$delta) {
            $delta = $time - $this->time;
        }

        $this->timings[] = array(
            'time' => number_format(round(($delta), 7), 7),
            'message' => $message,
        );
        $this->time = $time;
    }
    /**
     * Return timings log
     *
     * @param bool $string Return array or formatted string
     *
     * @return array|string
     */
    public function getTime($string = true)
    {
        $this->timings[] = array(
            'time' => number_format(round(microtime(true) - $this->start, 7), 7),
            'message' => '<b>Total time</b>',
        );
        $this->timings[] = array(
            'time' => number_format(round((memory_get_usage(true)), 2), 0, ',', ' '),
            'message' => '<b>Memory usage</b>',
        );

        if (!$string) {
            return $this->timings;
        } else {
            $res = '';
            foreach ($this->timings as $v) {
                $res .= $v['time'] . ': ' . $v['message'] . "\n";
            }

            return $res;
        }
    }
    
    public function success($message = "",$data = []){
        return array('success'=>1,'message'=>$message,'data'=>$data);
    }
    public function error($message = "",$data = []){
        return array('success'=>0,'message'=>$message,'data'=>$data);
    }
    public function checkPermissions($rule_action){
        if($rule_action['authenticated']){
            if(!$this->modx->user->id > 0) return $this->error("Not api authenticated!",['user_id'=>$this->modx->user->id]);
        }
        if($rule_action['groups']){
            $groups = array_map('trim', explode(',', $rule_action['groups']));
            if(!$this->modx->user->isMember($groups)) return $this->error("Not api permission groups!");
        }
        if($rule_action['permitions']){
            $permitions = array_map('trim', explode(',', $rule_action['permitions']));
            foreach($permitions as $pm){
                if(!$this->modx->hasPermission($pm)) return $this->error("Not api modx permission!");
            }
        }
        return $this->success();
    }
    
    /**
     * Регистрация триггеров для gtsAPI
     * @return array
     */
    public function regTriggers()
    {
        return [
            'osTree' => [
                'gtsapifunc' => 'filterTreeByAccess',
            ],
            'osEmployee' => [
                'gtsapifunc' => 'syncEmployeeToLegacy',
            ],
            'osDepartment' => [
                'gtsapifunc' => 'syncDepartmentToLegacy',
            ],
        ];
    }

    /**
     * Маппинг старого gtsBDepartment.id → новый osDepartment.id.
     * Используется как при миграции, так и при обратной синхронизации (через reverse).
     *   - все «Цех» и его дочерние (включая «Цех Паша») → osDepartment id=3 (Цех)
     *   - ИТР (id=17) → osDepartment id=2 (Офис)
     *   - Якутия (закрылась) → null = пропуск
     *   - неактивные/служебные → null
     */
    public function getLegacyDeptMap()
    {
        // Базовые соответствия по статичным id. Спец-случаи (типа ИТР, у которого
        // отдельный osDepartment с разрешаемым по имени id) переопределяются
        // в migrateFromOld через resolveDeptMapWithDynamic().
        return [
            // Прямые
            2  => 2,  // Офис → Офис
            12 => 4,  // охрана → Охрана
            13 => 5,  // Склад → Склад отгрузки (default; поправить если надо в Склад производства=6)
            16 => 3,  // Монтажники → Цех (default)
            17 => 2,  // ИТР → Офис fallback (переопределяется на новый osDepartment 'ИТР' если есть)

            // Цех Паша + старый Цех + все их дочерние → новый Цех (id=3)
            1  => 3, 20 => 3,
            3  => 3, 4  => 3, 5  => 3, 6  => 3, 7  => 3, 8  => 3, 9  => 3,
            10 => 3, 11 => 3, 14 => 3, 15 => 3, 19 => 3, 21 => 3, 27 => 3,
            28 => 3, 29 => 3,

            // Закрытое / неактивное → пропуск
            18 => null,  // Зачистка сварных швов (active=0)
            22 => null, 23 => null, 24 => null, 25 => null, 26 => null,  // Якутия
            30 => null,  // не определен наряд (active=0)
        ];
    }

    /**
     * Карта спец-правил для миграции: post.name → osDepartment.name.
     * Если у сотрудника такая должность — он автоматически уходит в этот отдел,
     * независимо от его старого department_id в legacy. Это уменьшает ручной
     * разбор «не распределённых» для кадровика.
     *
     * Добавлять новые правила одной строкой: 'Имя должности' => 'Имя отдела'.
     */
    public function getPostToDepartmentMap()
    {
        return [
            'ИТР'                => 'ИТР',
            'Слесарь-жестянщик'  => 'Цех',
            // Сюда добавлять новые правила по типу 'имя_должности' => 'имя_отдела'
        ];
    }

    /**
     * Гарантирует существование osDepartment «Не распределённые» под Красноярск
     * + узла в osTree. Используется как fallback при миграции для сотрудников
     * с пустым department_id.
     *
     * @param array &$deptTreeCache кэш osTree узлов (osDepartment.id → osTree)
     * @return int|null osDepartment.id или null если не получилось создать
     */
    protected function ensureUnassignedDepartment(&$deptTreeCache)
    {
        $name = 'Не распределённые';
        $dept = $this->modx->getObject('osDepartment', ['name' => $name]);
        if (!$dept) {
            $dept = $this->modx->newObject('osDepartment', [
                'name'        => $name,
                'active'      => 1,
                'sync_to_zeh' => 0,
            ]);
            if (!$dept->save()) return null;
        }
        $deptId = (int)$dept->get('id');

        // osTree узел: ищем существующий, если нет — создаём под филиалом Красноярск
        $tree = $this->modx->getObject('osTree', [
            'class'     => 'osDepartment',
            'target_id' => $deptId,
        ]);
        if (!$tree) {
            // Находим узел Красноярск (osFilial). Если несколько филиалов —
            // берём первый osFilial-узел (для текущей модели достаточно).
            $filialTree = $this->modx->getObject('osTree', ['class' => 'osFilial']);
            if (!$filialTree) return $deptId; // нет филиала — отдел есть, но без узла
            $tree = $this->modx->newObject('osTree', [
                'class'       => 'osDepartment',
                'target_id'   => $deptId,
                'parent_id'   => (int)$filialTree->get('id'),
                'parents_ids' => $filialTree->get('parents_ids') . $filialTree->get('id') . '#',
                'title'       => $name,
                'active'      => 1,
                'menuindex'   => 999,  // в конце списка отделов
            ]);
            if (!$tree->save()) return $deptId;
        }
        $deptTreeCache[$deptId] = $tree;
        return $deptId;
    }

    /**
     * Возвращает getLegacyDeptMap с переопределениями по name-lookup.
     * Это нужно для случаев где новый osDepartment создаётся вручную позже
     * (например «ИТР» под Офис) и его id неизвестен на момент написания карты.
     * Если такого нового отдела нет — остаётся fallback из getLegacyDeptMap.
     */
    protected function resolveDeptMapWithDynamic()
    {
        $map = $this->getLegacyDeptMap();
        // Спец: gtsBDepartment.id=17 (ИТР) — если есть osDepartment с именем 'ИТР'
        // (он создаётся вручную как дочерний под Офис), используем его id.
        $itr = $this->modx->getObject('osDepartment', ['name' => 'ИТР']);
        if ($itr) $map[17] = (int)$itr->get('id');
        return $map;
    }

    /**
     * Определяет legacy gtsBDepartment.id для сотрудника.
     * Поднимается от сотрудника по osTree и проверяет sync_to_zeh у КАЖДОГО
     * osDepartment в пути:
     *   - если хотя бы один в пути имеет sync_to_zeh=1 → legacy = 1 (Цех Паша)
     *   - иначе → legacy = 2 (Офис)
     *
     * Это позволяет ставить галку как на корневом отделе (тогда все его дочерние
     * наследуют), так и на конкретном дочернем (например ИТР под Офисом — только
     * сотрудники ИТР пойдут в Цех Паша, остальные Офиса останутся в Офисе).
     *
     * @param int $empId osEmployee.id
     * @return int legacy department_id (1=Цех Паша или 2=Офис fallback)
     */
    public function resolveLegacyDeptId($empId)
    {
        $empTree = $this->modx->getObject('osTree', [
            'class'     => 'osEmployee',
            'target_id' => (int)$empId,
        ]);
        if (!$empTree) return 2;

        $maxDepth = 20;
        $node = $this->modx->getObject('osTree', (int)$empTree->get('parent_id'));
        while ($node && $node->get('class') === 'osDepartment' && $maxDepth-- > 0) {
            $dept = $this->modx->getObject('osDepartment', (int)$node->get('target_id'));
            if ($dept && (int)$dept->get('sync_to_zeh') === 1) {
                return 1; // нашли отдел с sync_to_zeh — Цех Паша
            }
            $node = $this->modx->getObject('osTree', (int)$node->get('parent_id'));
        }
        return 2; // ни одного флага по пути — Офис
    }

    /**
     * Поднимается по osTree от узла сотрудника к корневому osDepartment
     * (тому что лежит непосредственно под osFilial). Возвращает target_id
     * этого корневого osDepartment.
     *
     * Используется в syncEmployeeToLegacy чтобы для дочерних отделов
     * автоматически находить корневой и брать legacy department_id из карты.
     *
     * @param int $empId osEmployee.id
     * @return int|null target_id корневого osDepartment или null если не найден
     */
    public function findRootOsDepartmentId($empId)
    {
        $empTree = $this->modx->getObject('osTree', [
            'class'     => 'osEmployee',
            'target_id' => (int)$empId,
        ]);
        if (!$empTree) return null;

        // Защита от глубоких циклов / гнилых данных
        $maxDepth = 20;
        $node = $this->modx->getObject('osTree', (int)$empTree->get('parent_id'));
        while ($node && $maxDepth-- > 0) {
            if ($node->get('class') !== 'osDepartment') {
                // Поднялись выше osDepartment (например достигли osFilial без департамента —
                // не должно случаться, но защищаемся)
                return null;
            }
            // Проверяем родителя: если он не osDepartment — значит текущий и есть корневой
            $parent = $this->modx->getObject('osTree', (int)$node->get('parent_id'));
            if (!$parent || $parent->get('class') !== 'osDepartment') {
                return (int)$node->get('target_id');
            }
            $node = $parent;
        }
        return null;
    }

    /**
     * Идемпотентная миграция: gts_balance_posts → osPost, gts_balance_staff → osEmployee + osTree.
     * Запускать как action или из MODX shell. При повторном запуске не дублирует.
     *
     * Идентификаторы переносятся 1:1 (osPost.id = gtsBPost.id, osEmployee.id = gtsBStaff.id) —
     * это упрощает обратную синхронизацию и сохранение связей в legacy-данных.
     *
     * Мигрируем всех сотрудников (active=0 в том числе) — они нужны бухгалтерии.
     *
     * @param array $data (не используется, нужен для action-вызова)
     * @return array
     */
    public function migrateFromOld($data = [])
    {
        $stats = [
            'cleared_employees'   => 0,
            'cleared_emp_trees'   => 0,
            'cleared_links'       => 0,
            'posts_created'   => 0,
            'posts_updated'   => 0,
            'staff_created'   => 0,
            'staff_updated'   => 0,
            'staff_skipped'   => 0,
            'tree_created'    => 0,
            'tree_updated'    => 0,
            'errors'          => [],
        ];

        // Полная очистка перед миграцией: чтобы повторный запуск стартовал с чистого
        // листа и не было «зависших» osEmployee/osTree-узлов от прошлых попыток.
        // gtsBStaff не трогаем — он остаётся живой в legacy и пересоздастся через xPDO save.
        $stats['cleared_emp_trees'] = $this->modx->getCount('osTree', ['class' => 'osEmployee']);
        $this->modx->removeCollection('osTree', ['class' => 'osEmployee']);
        $stats['cleared_employees'] = $this->modx->getCount('osEmployee');
        $this->modx->removeCollection('osEmployee', []);
        // gtsBDepartmentStaffLink пересобираем полностью — этот компонент теперь
        // источник истины для связей (через sync_to_zeh).
        $stats['cleared_links'] = $this->modx->getCount('gtsBDepartmentStaffLink');
        $this->modx->removeCollection('gtsBDepartmentStaffLink', []);

        // 1. Должности: gtsBPost → osPost (id точь-в-точь)
        $oldPosts = $this->modx->getCollection('gtsBPost');
        foreach ($oldPosts as $oldPost) {
            $oldId = (int)$oldPost->get('id');
            $name = trim((string)$oldPost->get('name'));
            if ($name === '') continue;

            $newPost = $this->modx->getObject('osPost', $oldId);
            $isNew = false;
            if (!$newPost) {
                $newPost = $this->modx->newObject('osPost');
                $newPost->set('id', $oldId);
                $isNew = true;
            }
            $newPost->set('name', $name);
            if ($newPost->save()) {
                $isNew ? $stats['posts_created']++ : $stats['posts_updated']++;
            }
        }

        // 2. Сотрудники: gtsBStaff → osEmployee + osTree узел (id точь-в-точь, все включая active=0)
        // Используем resolveDeptMapWithDynamic — он подхватит созданный вручную «ИТР» под Офисом.
        $deptMap = $this->resolveDeptMapWithDynamic();

        // Кэш osTree узлов отделов: osDepartment.id → osTree объект
        $deptTreeCache = [];
        $deptTrees = $this->modx->getCollection('osTree', ['class' => 'osDepartment']);
        foreach ($deptTrees as $dt) {
            $deptTreeCache[(int)$dt->get('target_id')] = $dt;
        }

        // Гарантируем существование отдела «Не распределённые» под Красноярск —
        // fallback для сотрудников с department_id=NULL в legacy gtsBStaff.
        // Кадровик через UI постепенно перетаскивает их в нужные отделы.
        $unassignedDeptId = $this->ensureUnassignedDepartment($deptTreeCache);

        // Спец-правила: должность → отдел. Перебивают маппинг по department_id legacy,
        // чтобы кадровику не пришлось вручную растаскивать «и так понятные» должности.
        $postToDept = [];
        foreach ($this->getPostToDepartmentMap() as $postName => $deptName) {
            $post = $this->modx->getObject('osPost', ['name' => $postName]);
            $dept = $this->modx->getObject('osDepartment', ['name' => $deptName]);
            if ($post && $dept) {
                $postToDept[(int)$post->get('id')] = (int)$dept->get('id');
            }
        }

        // Сотрудников читаем отсортированными по имени, чтобы при назначении menuindex
        // в дереве они оказались в алфавитном порядке внутри отдела.
        $c = $this->modx->newQuery('gtsBStaff');
        $c->sortby('name', 'ASC');
        $oldStaffs = $this->modx->getCollection('gtsBStaff', $c);

        // Счётчики menuindex по newDeptId — каждому новому узлу выдаём
        // уникальный порядковый номер в рамках его отдела.
        $menuindexCounters = [];

        foreach ($oldStaffs as $old) {
            $oldId = (int)$old->get('id');
            $oldDeptId = (int)$old->get('department_id');
            $name = trim((string)$old->get('name'));
            $userId = (int)$old->get('modx_user_id');
            $postId = (int)$old->get('post_id');
            $active = (int)$old->get('active');

            // Маппинг отдела
            $newDeptId = null;
            if ($oldDeptId && array_key_exists($oldDeptId, $deptMap)) {
                $newDeptId = $deptMap[$oldDeptId];
            }
            // Fallback для NULL/0 department_id в legacy → «Не распределённые».
            // Якутия и неактивные старые отделы (mapped в null) → остаются без узла
            // (их сотрудников фактически нет, но если будут — пропустятся по умолчанию).
            if ($newDeptId === null && !$oldDeptId && $unassignedDeptId) {
                $newDeptId = $unassignedDeptId;
            }

            // Спец-правила post → dept: должность перебивает любой dept
            if (isset($postToDept[$postId])) {
                $newDeptId = $postToDept[$postId];
            }

            // osEmployee с фиксированным id (1:1 с gtsBStaff)
            $emp = $this->modx->getObject('osEmployee', $oldId);
            $isNew = false;
            if (!$emp) {
                $emp = $this->modx->newObject('osEmployee');
                $emp->set('id', $oldId);
                $isNew = true;
            }
            $emp->fromArray([
                'name'    => $name,
                'active'  => $active,
                'user_id' => $userId ?: 0,
                'post_id' => $postId ?: 0,
            ]);
            if (!$emp->save()) {
                $stats['errors'][] = "Не сохранился osEmployee id={$oldId} '{$name}'";
                continue;
            }
            $isNew ? $stats['staff_created']++ : $stats['staff_updated']++;

            // osTree узел только если есть валидный новый dept
            if ($newDeptId === null || !isset($deptTreeCache[$newDeptId])) {
                $stats['staff_skipped']++; // мигрирован, но без места в дереве
                continue;
            }
            $deptTree = $deptTreeCache[$newDeptId];

            $empTree = $this->modx->getObject('osTree', [
                'class'     => 'osEmployee',
                'target_id' => $oldId,
            ]);
            $treeIsNew = false;
            if (!$empTree) {
                $empTree = $this->modx->newObject('osTree', [
                    'class'     => 'osEmployee',
                    'target_id' => $oldId,
                ]);
                $treeIsNew = true;
            }
            // Уникальный menuindex в рамках отдела (по алфавиту имён, благодаря
            // sortby выше).
            if (!isset($menuindexCounters[$newDeptId])) {
                $menuindexCounters[$newDeptId] = 0;
            }
            $menuindex = $menuindexCounters[$newDeptId]++;

            $empTree->fromArray([
                'parent_id'   => (int)$deptTree->get('id'),
                'parents_ids' => $deptTree->get('parents_ids') . $deptTree->get('id') . '#',
                'title'       => $name,
                'active'      => $active,
                'menuindex'   => $menuindex,
            ]);
            if ($empTree->save()) {
                $treeIsNew ? $stats['tree_created']++ : $stats['tree_updated']++;
            }

            // Синхронизация gts_departarment_staff_links для legacy-компонентов.
            // Удаляем все старые связи этого сотрудника, создаём одну новую через
            // флаг sync_to_zeh корневого osDepartment.
            $legacyDeptId = $this->resolveLegacyDeptId($oldId);
            $this->modx->removeCollection('gtsBDepartmentStaffLink', ['staff_id' => $oldId]);
            $link = $this->modx->newObject('gtsBDepartmentStaffLink', [
                'staff_id'      => $oldId,
                'department_id' => $legacyDeptId,
            ]);
            $link->save();
        }

        return $this->success('Миграция завершена', $stats);
    }

    /**
     * Триггер обратной синхронизации osEmployee → gtsBStaff.
     * При CRUD osEmployee обновляет соответствующую gtsBStaff чтобы старые
     * компоненты (gtsBalance, табели, отчёты) продолжали видеть сотрудника.
     *
     * Связь по id 1:1 (osEmployee.id == gtsBStaff.id), post_id тоже 1:1.
     * Для department — обратная карта osDepartment.id → gtsBDepartment.id.
     */
    public function syncEmployeeToLegacy(&$params)
    {
        // Рубильник: на площадках без legacy (напр. modx28) синху отключаем настройкой.
        if (!$this->modx->getOption('orgstructure_sync_legacy', null, true)) return $this->success();
        if ($params['type'] !== 'after') return $this->success();
        $method = $params['method'];
        // 'nodedrop' — после drag-drop в osTree (приходит из gtsAPI tree.class.php).
        // Логика дальше та же, что и для обычного update.
        if (!in_array($method, ['create', 'update', 'nodedrop', 'delete'], true)) return $this->success();

        // Берём данные. Для delete — из object_old, иначе из сохранённого xPDO-объекта.
        if ($method === 'delete') {
            $data = isset($params['object_old']) ? $params['object_old'] : [];
        } else {
            $obj = isset($params['object']) ? $params['object'] : null;
            if ($obj && is_object($obj)) {
                $data = $obj->toArray();
            } else {
                $data = isset($params['object_new']) ? $params['object_new'] : [];
            }
        }

        $empId  = (int)($data['id'] ?? 0);
        if (!$empId) return $this->success();

        $name   = trim((string)($data['name'] ?? ''));
        $userId = (int)($data['user_id'] ?? 0);
        $postId = (int)($data['post_id'] ?? 0);
        $active = (int)($data['active'] ?? 0);

        // Связь по id 1:1
        $staff = $this->modx->getObject('gtsBStaff', $empId);

        if ($method === 'delete') {
            // Не удаляем gtsBStaff (могут быть связи с балансами/табелями), деактивируем.
            if ($staff) {
                $staff->set('active', 0);
                $staff->save();
            }
            return $this->success();
        }

        // Определяем legacy department_id через флаг sync_to_zeh корневого osDepartment
        $oldDeptId = $this->resolveLegacyDeptId($empId);

        if (!$staff) {
            $staff = $this->modx->newObject('gtsBStaff');
            $staff->set('id', $empId);
        }
        $staff->fromArray([
            'name'          => $name,
            'modx_user_id'  => $userId ?: null,
            'department_id' => $oldDeptId,
            'post_id'       => $postId ?: null,
            'active'        => $active,
        ]);
        $staff->save();

        // Синхронизация связующей таблицы gts_departarment_staff_links
        // (используется StaffWorkload и другими legacy-компонентами для распределения нарядов).
        // Удаляем все старые связи этого сотрудника и создаём одну с правильным dept.
        $this->modx->removeCollection('gtsBDepartmentStaffLink', ['staff_id' => $empId]);
        $link = $this->modx->newObject('gtsBDepartmentStaffLink', [
            'staff_id'      => $empId,
            'department_id' => $oldDeptId,
        ]);
        $link->save();

        return $this->success();
    }

    /**
     * Триггер обратной синхронизации osDepartment → gtsBStaff/gtsBDepartmentStaffLink
     * при смене флага sync_to_zeh.
     *
     * Меняем флаг у отдела → все сотрудники под ним (включая вложенные отделы)
     * меняют legacy department_id (1=Цех Паша / 2=Офис) — без этого legacy-компоненты
     * (StaffWorkload, отчёты, балансы) видели бы старое распределение пока не передёрнешь
     * каждого сотрудника вручную.
     *
     * Реагируем только на update и только если sync_to_zeh реально изменился —
     * иначе пустые update'ы (правка name/active) триггерили бы массовый пересбор.
     */
    public function syncDepartmentToLegacy(&$params)
    {
        // Рубильник: на площадках без legacy (напр. modx28) синху отключаем настройкой.
        if (!$this->modx->getOption('orgstructure_sync_legacy', null, true)) return $this->success();
        if ($params['type'] !== 'after') return $this->success();
        if ($params['method'] !== 'update') return $this->success();

        $newObj  = isset($params['object']) ? $params['object'] : null;
        $newData = ($newObj && is_object($newObj)) ? $newObj->toArray()
                                                  : (isset($params['object_new']) ? $params['object_new'] : []);
        $oldData = isset($params['object_old']) ? $params['object_old'] : [];

        $deptId  = (int)($newData['id'] ?? 0);
        if (!$deptId) return $this->success();

        $newFlag = (int)($newData['sync_to_zeh'] ?? 0);
        $oldFlag = (int)($oldData['sync_to_zeh'] ?? 0);
        if ($newFlag === $oldFlag) return $this->success();

        // Находим osTree-узел этого отдела, чтобы по material path собрать всех вложенных employee.
        $deptTree = $this->modx->getObject('osTree', [
            'class'     => 'osDepartment',
            'target_id' => $deptId,
        ]);
        if (!$deptTree) return $this->success();
        $deptTreeId = (int)$deptTree->get('id');

        // Все osEmployee-узлы под этим отделом (включая дочерние отделы) — через parents_ids LIKE.
        // Это совпадает с тем, как в filterTreeByAccess собираются потомки доступного узла.
        $this->pdo->setConfig([
            'class'  => 'osTree',
            'where'  => [
                'class' => 'osEmployee',
                'parents_ids:LIKE' => '%#' . $deptTreeId . '#%',
            ],
            'select' => 'target_id',
            'return' => 'data',
            'limit'  => 0,
        ]);
        $empNodes = $this->pdo->run();

        $count = 0;
        foreach ($empNodes as $row) {
            $empId = (int)$row['target_id'];
            if (!$empId) continue;

            // resolveLegacyDeptId учитывает sync_to_zeh всех отделов на пути от
            // сотрудника вверх — поэтому корректно отрабатывает и вложенные кейсы
            // (например ИТР под Офис с флагом — Офис сам без флага).
            $newLegacyId = $this->resolveLegacyDeptId($empId);

            // gtsBStaff.department_id — источник для отчётов/балансов; держим в синке с link'ом.
            $staff = $this->modx->getObject('gtsBStaff', $empId);
            if ($staff) {
                $staff->set('department_id', $newLegacyId);
                $staff->save();
            }

            $this->modx->removeCollection('gtsBDepartmentStaffLink', ['staff_id' => $empId]);
            $link = $this->modx->newObject('gtsBDepartmentStaffLink', [
                'staff_id'      => $empId,
                'department_id' => $newLegacyId,
            ]);
            $link->save();
            $count++;
        }

        return $this->success('Обновлено сотрудников: ' . $count, ['count' => $count]);
    }

    /**
     * Триггер для фильтрации узлов дерева в зависимости от прав доступа
     * @param array $params
     * @return array
     */
    public function filterTreeByAccess(&$params)
    {
        // Если метод не read или тип не after, не применяем фильтрацию
        if ($params['method'] != 'read' || $params['type'] != 'after') {
            return $this->success();
        }
        
        // // Логируем для отладки
        // $this->modx->log(1, 'Метод: ' . $params['method'] . ', Тип: ' . $params['type']);
        
        // Получаем ID текущего пользователя
        $userId = $this->modx->user->get('id');
        
        // Администраторы и HR видят всё дерево без osAccess-записей
        if ($this->modx->user->isMember(['Administrator', 'hr', 'Отдел кадров'])) {
            return $this->success();
        }
        //для демо
        if($this->modx->getOption('orgstructure_debug')) return $this->success();

        // Получаем группы пользователя
        $userGroups = [];
        $q = $this->modx->newQuery('modUserGroupMember');
        $q->where(['member' => $userId]);
        $q->select('user_group');
        if ($q->prepare() && $q->stmt->execute()) {
            $userGroups = $q->stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        // Если нет групп и не администратор, возвращаем пустой результат
        if (empty($userGroups) && !$this->modx->user->isMember('Administrator')) {
            return $this->success('', ['out' => ['rows' => [], 'slTree' => []]]);
        }
        
        // Получаем доступные узлы для пользователя и его групп с помощью pdoFetch
        $this->pdo->setConfig([
            'class' => 'osAccess',
            'where' => [
                'active' => 1,
                [
                    'user_id' => $userId,
                    'OR:group_id:IN' => $userGroups,
                ]
            ],
            'select' => 'tree_id',
            'return' => 'data',
            'limit' => 0,
        ]);
        $accessNodes = $this->pdo->run();
        
        // Если нет доступных узлов, возвращаем пустой результат
        if (empty($accessNodes)) {
            return $this->success('', ['out' => ['rows' => [], 'slTree' => []]]);
        }
        
        // Собираем ID доступных узлов
        $accessibleNodeIds = [];
        foreach ($accessNodes as $node) {
            $accessibleNodeIds[] = $node['tree_id'];
        }
        
        // Получаем все узлы, к которым у пользователя есть доступ, и их дочерние узлы
        $visibleNodeIds = $accessibleNodeIds;
        
        // Получаем информацию о доступных узлах
        $this->pdo->setConfig([
            'class' => 'osTree',
            'where' => [
                'id:IN' => $accessibleNodeIds,
            ],
            'select' => 'id, parents_ids, parent_id',
            'return' => 'data',
            'limit' => 0,
        ]);
        $accessibleNodes = $this->pdo->run();
        
        // Добавляем родительские узлы для доступных узлов
        foreach ($accessibleNodes as $node) {
            // Добавляем родительские узлы
            $parentsIds = $node['parents_ids'];
            if (!empty($parentsIds)) {
                $parents = explode('#', $parentsIds);
                foreach ($parents as $parentId) {
                    if (!empty($parentId) && is_numeric($parentId)) {
                        $visibleNodeIds[] = (int)$parentId;
                    }
                }
            }
        }
        
        // Получаем все дочерние узлы для доступных узлов
        $whereConditions = [];
        foreach ($accessibleNodeIds as $nodeId) {
            $whereConditions[] = "parents_ids LIKE '%#{$nodeId}#%'";
        }
        
        if (!empty($whereConditions)) {
            $this->pdo->setConfig([
                'class' => 'osTree',
                'where' => [
                    '(' . implode(' OR ', $whereConditions) . ')',
                ],
                'select' => 'id',
                'return' => 'data',
                'limit' => 0,
            ]);
            $childNodes = $this->pdo->run();
            // $this->modx->log(1, 'Дочерние узлы: ' . print_r($childNodes, 1) . ' для узлов: ' . print_r($accessibleNodeIds, 1) . ' SQL: ' . $this->pdo->getTime());
        } else {
            $childNodes = [];
        }
        
        // Добавляем дочерние узлы
        foreach ($childNodes as $node) {
            $visibleNodeIds[] = $node['id'];
        }
        
        // Удаляем дубликаты
        $visibleNodeIds = array_unique($visibleNodeIds);
        
        // // Логируем структуру $params для отладки
        // $this->modx->log(1, 'Структура $params: ' . print_r($params, true));
        
        // Фильтруем результаты
        if (isset($params['object_old']) && isset($params['object_old']['rows']) && is_array($params['object_old']['rows'])) {
            // $this->modx->log(1, 'childNodes22222' . print_r($childNodes, 1) . $this->pdo->getTime());
            $filteredRows = [];
            foreach ($params['object_old']['rows'] as $row) {
                if (in_array($row['id'], $visibleNodeIds)) {
                    $filteredRows[] = $row;
                }
            }
            
            // Возвращаем отфильтрованные данные
            return $this->success('', ['out' => [
                'rows' => $filteredRows,
                'slTree' => []
            ]]);
        }
        
        return $this->success();
    }
}
