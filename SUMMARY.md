# OrgStructure — резюме

MODX-компонент управления **организационной структурой** через дерево с разнотипными узлами и пер-узловыми правами доступа. На стеке gtsAPI + PVTables (UniTreePanel) + Vue 3 + PrimeVue + TailwindCSS.

## Что делает

- Хранит **иерархию** организаций → филиалов → отделов → сотрудников в **универсальном** дереве `osTree` (узел = ссылка `(class, target_id)` на одну из доменных таблиц).
- Поддерживает **разные типы узлов** в одном дереве: `osOrg`, `osFilial`, `osDepartment`, `osEmployee`. Каждый тип имеет свою таблицу с доменными полями, узел `osTree` связывает их в иерархию.
- **Должности** (`osPost`) — отдельный плоский справочник на отдельной вкладке.
- **Per-node access control** через `osAccess` — фильтрация дерева по правам пользователя/группы, прозрачная (триггер на read).

## Структура БД (`schema/orgstructure.mysql.schema.xml`)

| xPDO class | Таблица | Назначение |
|---|---|---|
| `osTree` | `orgstructure_tree` | Универсальное дерево: `parent_id`, `parents_ids` (path), `title`, `class`, `target_id`, `menuindex`, `active` |
| `osOrg` | `orgstructure_orgs` | Организации (`name`, `active`) |
| `osFilial` | `orgstructure_filials` | Филиалы (+ `default`) |
| `osDepartment` | `orgstructure_departments` | Отделы |
| `osPost` | `orgstructure_posts` | Должности |
| `osEmployee` | `orgstructure_employees` | Сотрудники (+ связь с `modUser` через `user_id`) |
| `osAccess` | `orgstructure_access` | Права на узлы дерева: `tree_id` × (`user_id` ∨ `group_id`), `active` |

Ключевая идея — **узел дерева не хранит доменных данных**. `osTree.class` указывает имя xPDO-класса, `osTree.target_id` — id записи в этой таблице. Это и есть «универсальное дерево» — добавляешь новый тип узла = добавляешь таблицу + регистрируешь в gtsapipackages.js, без миграций по `osTree`.

## Frontend (`src/App.vue`)

Простая структура:
- **Tabs / TabPanels** (PrimeVue):
  - Вкладка 1 — `<UniTreePanel :treetabs="treetabs"/>` с `osTree` (тип `tree`, dragable)
  - Вкладка 2 — `<PVTables table="osPost"/>` (плоский справочник должностей)
- Вся UI-логика дерева (создание, перетаскивание, иконки по классам, контекстное меню) — внутри **UniTreePanel** из PVTables. Конкретный компонент здесь только конфигурирует.

## Backend (`core/components/orgstructure/model/orgstructure.class.php`, 289 строк)

Класс `OrgStructure`:
- Подключает gtsShop и getTables (из конструктора, MODX-сервисы).
- `regTriggers()` регистрирует один триггер: `osTree.read.after → filterTreeByAccess`.
- `filterTreeByAccess($params)` — **главная логика прав**:
  1. Админ или режим `orgstructure_debug` → пропускаем фильтрацию.
  2. Получаем группы пользователя из `modUserGroupMember`.
  3. По `osAccess` находим разрешённые `tree_id` (`active=1` AND (`user_id=$me` OR `group_id IN $myGroups`)).
  4. Расширяем видимый набор:
     - **Родители** разрешённых узлов (по `parents_ids` через `#`-разделитель) — чтобы в дереве было видно куда прицеплено.
     - **Дети** разрешённых узлов (по `parents_ids LIKE '%#nodeId#%'`) — рекурсивно «всё под доступным».
  5. Фильтруем `params['object_old']['rows']` по этому набору и возвращаем.

`parents_ids` хранится как `#1#5#23#` — material path для быстрого LIKE-поиска предков и потомков без рекурсии.

## Конфигурация gtsAPI (`_build/configs/gtsapipackages.js`, 636 строк)

Главное определение — **`osTree`** как `type: 3` (UniTree, древовидная):

- `gtsAPIUniTreeClass: { osOrg, osFilial, osDepartment, osEmployee }` — для каждого типа узла:
  - `title_field` (откуда брать имя в дерево синхронно с доменной таблицей)
  - `svg` (иконка)
- `properties.actions.create.tables` — описание кнопок «Создать» с **ограничением вложенности через `parent_classes`**:
  - `osOrg` — только в `root`
  - `osFilial` — только под `osOrg`
  - `osDepartment` — под `osFilial` или другим `osDepartment`
  - `osEmployee` — под `osFilial` или `osDepartment`
- Для каждого `create` — группа доступа (`groups: 'Administrator'` или `'Administrator,hr'`), форма (`form: 'UniTree'`), `add_fields` для дополнительных доменных полей.

Дальше в файле — определения `osOrg`, `osFilial`, `osDepartment`, `osEmployee` как обычные таблицы PVTables (CRUD + поля), и `osPost`, `osAccess`, `osEmployee_link` (если есть).

## Внешние зависимости

- **gtsAPI** — REST-фреймворк (route, триггеры, autocomplete, права)
- **PVTables** (через `pvtables/dist/pvtables`) — UniTreePanel и PVTables компоненты, формы, рендер
- **gtsShop** — подключается из конструктора (предположительно для интеграций по части пользователей/прав, конкретное использование не явное в основном файле)
- **getTables** — для работы с табличными данными
- **pdoFetch** — для произвольных SQL-выборок (использован в filterTreeByAccess)
- **MODX-классы**: `modUserGroupMember`, `modUser` (для прав и связи сотрудник→пользователь)

## Сборка и деплой

Стандартная PVExtra (как vk24Snab, Squd, NaryadFlanec):
- `npm i` — установка зависимостей
- `.env` с `VITE_APP_PROTOCOL`, `VITE_APP_HOST`
- `npm run get_token` — токен для upconfig
- `npm run build` = `vite build` + `_build/upconfig.js` (заливает gtsapipackages.js на сайт через API)
- `_build/copy.js` — стартовый скрипт для клонирования заготовки в новый компонент

## Особенности и гипотезы по архитектуре

**Сильные стороны:**
- Универсальное дерево с разнотипными узлами — расширяемо без миграций структуры дерева.
- Права через триггер на read — единая точка фильтрации, frontend получает уже отфильтрованные данные. Не нужно дублировать проверки в UI.
- Material path (`parents_ids` через `#`) — быстрая выборка предков/потомков одним SQL без рекурсии.
- Отделение «должность» от «сотрудник» — должность как справочник, сотрудник как узел в дереве с привязкой к должности и пользователю.

**Подводные камни / места внимания:**
- В `filterTreeByAccess` LIKE-запрос `parents_ids LIKE '%#nodeId#%'` строится через **конкатенацию строк в SQL** — id-шки в `accessibleNodeIds` идут от `osAccess.tree_id`, контролируемого админами, поэтому SQL-инъекции реальной угрозы нет. Но в более общем случае стоит держать в уме.
- Триггер вызывается на **каждый** read дерева — для больших структур может стать узким местом. Сейчас это 3-4 SQL запроса (groups, accessNodes, accessibleNodes, childNodes). Кэширование результата на сессию пользователя может пригодиться при росте.
- `osEmployee.user_id` связывает с `modUser` — но не видно явного UNIQUE-индекса (один пользователь — несколько записей сотрудника возможно). Зависит от бизнес-логики.

**Что не разобрал детально:** конкретное использование `gtsShop` в конструкторе, полное определение `osAccess` UI (создание прав через какую форму), и интеграция с другими компонентами gtsERP.

## Связанные файлы

- [README.md](README.md) — установка и базовое описание
- [project_description.md](project_description.md) — более раннее описание архитектуры
- [prompts/](prompts/) — папка с инструкциями для AI (если есть)
