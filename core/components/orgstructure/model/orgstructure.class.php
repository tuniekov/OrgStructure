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
    public $gtsShop;
    public $getTables;
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
        $this->gtsShop = $modx->getService("gtsShop","gtsShop",
            MODX_CORE_PATH."components/gtsshop/model/",[]);
        //$this->modx->lexicon->load('orgstructure:default');
        $gettables_core_path = $this->modx->getOption('gettables_core_path',null, MODX_CORE_PATH . 'components/gettables/core/');
        $gettables_core_path = str_replace('[[+core_path]]', MODX_CORE_PATH, $gettables_core_path);
        if ($this->modx->loadClass('gettables', $gettables_core_path, false, true)) {
            $this->getTables = new getTables($this->modx, []);
        }

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
        ];
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
        
        // Если пользователь администратор, не применяем фильтрацию
        if ($this->modx->user->isMember('Administrator')) {
            return $this->success();
        }
        
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
