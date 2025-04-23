# OrgStructure
Заготовка для компонента для MODX на основе PVTables, PrimeVue, Vue, TailwindCSS.

# Установка
Клонируем репозиторий в папку сайта MODX Extras(site.loc\public\Extras\OrgStructure)
## В папке OrgStructure:
1. Выполняем команду npm i.
2. Запускаем скрипт npm run copy и вводим нужное название компонента.
## Переходим в получившиюся папку в Extras с именем компонента.
1. Выполняем команду npm i.
2. В папке Extras создаем файл .env c переменными:
VITE_APP_PROTOCOL=http
VITE_APP_HOST=site.loc
site.loc заменить на домен своего сайта.
3. Выполняем команду npm run get_token. Вводим логин и пароль админа сайта. Токен сохраняется в файле site.loc\public\Extras\.env
4. После или во время создания компонента выполняем команду npm run build. Команда создаст транспортный пакет в site.loc\public\core\package и установит его.

# Назначение файлов и папок
1. Папки assets и core копируются в соответствующее папки сайта.
2. В папке src исходный код Vue компонента.
3. В папке _build настройки сборки компонента.
4. В файле _build/config.js можно включить выключить файлы, папки и схему базы из сборки. И назначить MODX версию компонента.
5. В файле _build\configs\gtsapipackages.js настройки таблиц для gtsAPI. По ним gtsAPI и PVTables формируют доступное API и таблицы, деревья, формы на фронте.
6. В файле core\components\orgstructure\model\schema\orgstructure.mysql.schema.xml MODX схема базы данных.
7. В файле core\components\orgstructure\model\orgstructure\orgstructure.class.php класс MODX компонента. Триггеры и кастомные действия обращаются в него.

 

