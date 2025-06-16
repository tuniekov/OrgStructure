export default {
    orgstructure:{
        name:'orgstructure', //имя пакета MODX
        gtsAPITables:{
            osTree:{
                table:'osTree', //Название таблицы
                //class:'osTree', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно. 
                autocomplete_field:'', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:13, // при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 3, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                gtsAPIUniTreeClass:{ // Определения связанных таблиц. Нужны для синхронизации названий записей с таблицей UniTree.
                    osOrg: {
                        title_field: 'name', // поле названия в связанной таблице.
                        svg: //svg картинка таблицы в дереве
                        `
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            <path d="M12 7v14"></path>
                            <path d="M8 3h8"></path>
                        </svg>
                        `
                    },
                    osFilial: {
                        title_field: 'name',
                        svg:`
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            <rect x="10" y="14" width="4" height="2"></rect>
                        </svg>
                        `
                    },
                    osDepartment: {
                        title_field: 'name',
                        svg:`
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 9a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V9z"></path>
                            <path d="M4 9v9a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9"></path>
                            <path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            <line x1="12" y1="12" x2="12" y2="12.01"></line>
                            <line x1="8" y1="12" x2="8" y2="12.01"></line>
                            <line x1="16" y1="12" x2="16" y2="12.01"></line>
                        </svg>
                        `
                    },
                    osEmployee: {
                        title_field: 'name',
                        svg:`
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                            <line x1="8" y1="16" x2="16" y2="16"></line>
                        </svg>
                        `
                    },
                },
                properties: { // свойства таблицы
                    actions:{ // Действия определенные для таблицы. Для дерева это действия в раскрывающемся списке дерева.
                        create:{ //создать
                            tables:{ // определяет кнопку создать для разных таблиц gtsAPI
                                osOrg:{ // Для таблицы с именем table="osOrg"
                                    groups:'Administrator', //Группа пользователей которым разрешено создавать организации
                                    label:'Создать организацию',
                                    parent_classes:['root'], //'root' можно создавать в корне дерева
                                    cls: 'p-button-rounded p-button-info',
                                    form:'UniTree', // пока нужно. Предустановленая форма для создания узла дерева.
                                    add_fields: { //добавочные поля в форму
                                        active: {
                                            label: 'Включено',
                                            type: 'boolean',
                                            default: 1,
                                        },
                                    }
                                },
                                osFilial:{
                                    groups:'Administrator',
                                    label:'Создать филиал',
                                    parent_classes:['osOrg'],
                                    cls: 'p-button-rounded p-button-info',
                                    form:'UniTree',
                                    add_fields: {
                                        active: {
                                            label: 'Включено',
                                            type: 'boolean',
                                            default: 1,
                                        },
                                    }
                                },
                                osDepartment:{
                                    groups:'Administrator',
                                    label:'Создать отдел',
                                    parent_classes:['osFilial', 'osDepartment'],
                                    cls: 'p-button-rounded p-button-info',
                                    form:'UniTree',
                                    add_fields: {
                                        active: {
                                            label: 'Включено',
                                            type: 'boolean',
                                            default: 1,
                                        },
                                    }
                                },
                                osEmployee:{
                                    groups:'Administrator,hr',
                                    label:'Создать сотрудника',
                                    parent_classes:['osFilial', 'osDepartment'],
                                    cls: 'p-button-rounded p-button-info',
                                    form:'UniTree',
                                    title_label: 'Имя пользователя',
                                    add_fields: {
                                        active: {
                                            label: 'Включено',
                                            type: 'boolean',
                                            default: 1,
                                        },
                                        post_id: {
                                            label: 'Должность',
                                            type: 'autocomplete',
                                            table: 'osPost',
                                        },
                                    }
                                },
                            }
                        },
                        delete:{ // удалить узел дерева
                            groups:'Administrator' // разрешено только администраторам.
                        },
                    },
                    nodeclick:{ //Действия при клике на узел дерева
                        classes:{ // Определяет действие при клике для разных классов узла
                            osOrg:{ //Для класс osOrg в панели связанной с деревом показывает табы
                                label: 'Организация', //подпись панели
                                tabs:{ //конфигурация табов
                                    main:{ //таб
                                        type:'form', //В табе форма редактирования
                                        title:'Основное', // подпись таба
                                        table:'osOrg', //Таблица gtsAPI с редактируемыми записями
                                    },
                                    employees:{
                                        type:'table', //В табе отображается таблица
                                        title:'Сотрудники', // подпись таба
                                        table:'osEmployeeChild', //Таблица gtsAPI с редактируемыми записями
                                        where:{ //Фильтр записей в таблице
                                            parents_ids: 'tree_id' //parents_ids: особое условие, что отбираются записи дочерние id заданному в parents_ids
                                            //'tree_id' подставляется id записе в дереве (В таблице osTree).
                                            //'current_id' id в связанной таблице. То есть это target_id из таблицы дерева
                                        }
                                    }
                                }
                            },
                            osFilial:{
                                label: 'Филиал',
                                tabs:{
                                    main:{
                                        type:'form',
                                        title:'Основное',
                                        table:'osFilial',
                                    },
                                    employees:{
                                        type:'table',
                                        title:'Сотрудники',
                                        table:'osEmployeeChild',
                                        where:{
                                            parents_ids: 'tree_id'
                                        }
                                    }
                                }
                            },
                            osDepartment:{
                                label: 'Отдел',
                                tabs:{
                                    main:{
                                        type:'form',
                                        title:'Основное',
                                        table:'osDepartment',
                                    },
                                    employees:{
                                        type:'table',
                                        title:'Сотрудники',
                                        table:'osEmployeeChild',
                                        where:{
                                            parents_ids: 'tree_id'
                                        }
                                    },
                                    access:{
                                        type:'table',
                                        title:'Права доступа',
                                        table:'osAccess',
                                        where:{
                                            tree_id: 'tree_id'
                                        }
                                    }
                                }
                            },
                            osEmployee:{
                                label: 'Сотрудник',
                                tabs:{
                                    main:{
                                        type:'form',
                                        title:'Основное',
                                        table:'osEmployee',
                                    }
                                }
                            },
                        }
                    },
                    useUniTree : true, //Включаем когда есть таблица - дерево и есть связанные таблицы. Если только таблица дерево, то выключаем
                    extendedModResource : false, //Включаем, если таблица дерево наследует modResource или связано с modResource
                    rootIds: 0, //С какого узла показывать дерево. Показываются дочерние только (Наверное, потом проверить).
                    idField:'id',
                    parentIdField: 'parent_id',
                    parents_idsField: 'parents_ids',
                    menuindexField: 'menuindex',
                    classField: 'class',
                    isLeaf:{ //Условие какие узлы дерева не имеют дочерних элементов.
                        class:'osEmployee'
                    }
                },
                "fields": {
                    "id": {
                        "type": "view",
                        "class": "osTree"
                    },
                    "parent_id": {
                        "type": "hidden",
                        "class": "osTree"
                    },
                    "parents_ids": {
                        "type": "hidden",
                        "class": "osTree"
                    },
                    "title": {
                        "type": "text",
                        "class": "osTree",
                        "label": "\u0417\u0430\u0433\u043e\u043b\u043e\u0432\u043e\u043a"
                    },
                    "class": {
                        "type": "hidden",
                        "class": "osTree"
                    },
                    "target_id": {
                        "type": "hidden",
                        "class": "osTree"
                    },
                    "menuindex": {
                        "type": "hidden",
                        "class": "osTree"
                    }
                }
            },
            osOrg:{
                table:'osOrg', //Название таблицы
                //class:'osOrg', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно.
                autocomplete_field:'os_org_id', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:2, //при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 1, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                properties: { // свойства таблицы
                    autocomplete:{ // Для таблицы включается автокомплект, который можно затем использовать в полях type:'autocomplete'
                        tpl:'{$name}', // шаблон записей для автокомплект
                        where:{
                            "name:LIKE":"%query%", // Условие поиска в поле
                        },
                        limit:0, // число показываемых записей при поиске.
                    },
                    actions:{ // Действия определенные для таблицы. read, create, update, delete стандартные
                        read:{},
                        update:{
                            groups:'Administrator' //Можно определить группы пользователей которые могут изменять запись
                        }
                    },
                    fields:{ // поля таблицы
                        id:{ //имя поля
                            type:'view', //тип поля
                        },
                        name:{ //имя поля
                            label:'Имя', //подпись поля
                            type:'text', //тип поля
                        },
                        active:{
                            label:'Включено',
                            type:'boolean',
                        },
                    }
                }
            },
            osFilial:{
                table:'osFilial', //Название таблицы
                //class:'osFilial', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно.
                autocomplete_field:'os_filial_id', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:2, //при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 1, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, 3 - дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                properties: { // свойства таблицы
                    autocomplete:{ // Для таблицы включается автокомплект, который можно затем использовать в полях type:'autocomplete'
                        tpl:'{$name}', // шаблон записей для автокомплект
                        where:{
                            "name:LIKE":"%query%", // Условие поиска в поле
                        },
                        limit:0, // число показываемых записей при поиске.
                    },
                    actions:{
                        read:{},
                        update:{
                            groups:'Administrator'
                        }
                    },
                    fields:{
                        id:{
                            type:'view',
                        },
                        name:{
                            label:'Имя',
                            type:'text',
                        },
                        active:{
                            label:'Включено',
                            type:'boolean',
                        },
                    }
                }
            },
            osDepartment:{
                table:'osDepartment', //Название таблицы
                //class:'osDepartment', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно.
                autocomplete_field:'os_department_id', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:2, //при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 1, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, 3 - дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                properties: { // свойства таблицы
                    autocomplete:{ // Для таблицы включается автокомплект, который можно затем использовать в полях type:'autocomplete'
                        tpl:'{$name}', // шаблон записей для автокомплект
                        where:{
                            "name:LIKE":"%query%", // Условие поиска в поле
                        },
                        limit:0, // число показываемых записей при поиске.
                    },
                    actions:{
                        read:{},
                        update:{
                            groups:'Administrator'
                        }
                    },
                    fields:{
                        id:{
                            type:'view',
                        },
                        name:{
                            label:'Название отдела',
                            type:'text',
                        },
                        active:{
                            label:'Включено',
                            type:'boolean',
                        },
                    }
                }
            },
            osPost:{
                table:'osPost', //Название таблицы
                //class:'osPost', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно.
                autocomplete_field:'os_post_id', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:1, //при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 1, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, 3 - дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                properties: { // свойства таблицы
                    autocomplete:{ // Для таблицы включается автокомплект, который можно затем использовать в полях type:'autocomplete'
                        tpl:'{$name}', // шаблон записей для автокомплект
                        where:{
                            "name:LIKE":"%query%", // Условие поиска в поле
                        },
                        limit:0, // число показываемых записей при поиске.
                    },
                    actions:{
                        read:{},
                        create:{
                            groups:'Administrator'
                        },
                        update:{
                            groups:'Administrator'
                        },
                        delete:{
                            groups:'Administrator'
                        }
                    },
                    fields:{
                        id:{
                            type:'view',
                        },
                        name:{
                            label:'Название должности',
                            type:'text',
                        },
                    }
                }
            },
            osEmployee:{
                table:'osEmployee', //Название таблицы
                //class:'osEmployee', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно.
                autocomplete_field:'os_employee_id', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:2, //при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 1, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, 3 - дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                properties: { // свойства таблицы
                    autocomplete:{ // Для таблицы включается автокомплект, который можно затем использовать в полях type:'autocomplete'
                        tpl:'{$name}', // шаблон записей для автокомплект
                        where:{
                            "name:LIKE":"%query%", // Условие поиска в поле
                        },
                        limit:0, // число показываемых записей при поиске.
                    },
                    actions:{
                        read:{},
                        update:{
                            groups:'Administrator,hr'
                        }
                    },
                    fields:{
                        id:{
                            type:'view',
                        },
                        name:{
                            label:'ФИО сотрудника',
                            type:'text',
                        },
                        active:{
                            label:'Активен',
                            type:'boolean',
                        },
                        user_id:{
                            label:'Пользователь',
                            type:'autocomplete',
                            table:'osModUser',
                        },
                        post_id:{
                            label:'Должность',
                            type:'autocomplete',
                            table:'osPost',
                        },
                    }
                }
            },
            osEmployeeChild:{
                table:'osEmployeeChild', //Название таблицы
                class:'osTree', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно.
                autocomplete_field:'', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:4, //при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 1, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, 3 - дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                properties: {
                    query:{
                        leftJoin:{
                            osEmployee:{
                                class:'osEmployee',
                                on:"osTree.class = 'osEmployee' and osTree.target_id = osEmployee.id"
                            }
                        },
                        where:{
                            'osTree.class':'osEmployee'
                        },
                        select:{
                            osEmployee:'*',
                            osTree:'osTree.id as tree_id'
                        }
                    },
                    actions:{
                        read:{},
                        update:{
                            groups:'Administrator,hr'
                        }
                    },
                    fields:{
                        id:{
                            type:'view',
                        },
                        name:{
                            label:'ФИО сотрудника',
                            type:'text',
                        },
                        active:{
                            label:'Активен',
                            type:'boolean',
                            filter:{
                                value: 1, matchMode: 'equals'
                            }
                        },
                        user_id:{
                            label:'Пользователь',
                            type:'autocomplete',
                            table:'osModUser',
                        },
                        post_id:{
                            label:'Должность',
                            type:'autocomplete',
                            table:'osPost',
                        },
                    }
                }
            },
            osAccess:{
                table:'osAccess', //Название таблицы
                //class:'osAccess', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно.
                autocomplete_field:'os_access_id', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:3, //при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 1, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, 3 - дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                properties: {
                    actions:{
                        read:{},
                        create:{
                            groups:'Administrator'
                        },
                        update:{
                            groups:'Administrator'
                        },
                        delete:{
                            groups:'Administrator'
                        }
                    },
                    fields:{
                        id:{
                            type:'view',
                        },
                        tree_id:{
                            label:'Элемент структуры',
                            type:'hidden',
                        },
                        user_id:{
                            label:'Пользователь',
                            type:'autocomplete',
                            table:'osModUser',
                        },
                        group_id:{
                            label:'Группа',
                            type:'autocomplete',
                            table:'modUserGroup',
                        },
                        active:{
                            label:'Активен',
                            type:'boolean',
                        },
                    }
                }
            },
        }
    },

    modx:{ //Второй пакет
        name:'modx', //имя пакета MODX
        gtsAPITables:{
            osModUser:{
                table:'osModUser', //Название таблицы
                class:'modUser', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно.
                autocomplete_field:'', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:2, //при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 1, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, 3 - дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                properties: { // свойства таблицы
                    autocomplete:{ // Для таблицы включается автокомплект, который можно затем использовать в полях type:'autocomplete'
                        tpl:'{$username}', // шаблон записей для автокомплект
                        query:{
                            sortby:{
                                username:'ASC'
                            }
                        },
                        where:{
                            "username:LIKE":"%query%", // Условие поиска в поле
                        },
                        limit:0, // число показываемых записей при поиске.
                    },
                }
            },
            modUserGroup:{
                table:'modUserGroup', //Название таблицы
                class:'modUserGroup', //Класс MODX таблицы базы данных. Если совпадает с table писать не обязательно.
                autocomplete_field:'', //Если задано то при определении полей таблицы автоматически узнает поле autocomplect
                version:1, //при изменении в файле надо обновлять версию, чтобы изменения применились при установке.
                type: 1, //тип таблицы: 1 - таблица PVTables, 2 - таблица JSON, 3 - дерево UniTree
                authenticated:true, //доступ к таблице только аутентифицированным пользователям
                groups:'', //Можно определить группы пользователей которые будут иметь доступ к таблицам.
                permitions:'', //Можно определить разрешения MODX для которых будет разрешён доступ.
                active:true, // Включено. Можно быстро временно выключить таблицу.
                properties: { // свойства таблицы
                    autocomplete:{ // Для таблицы включается автокомплект, который можно затем использовать в полях type:'autocomplete'
                        tpl:'{$name}', // шаблон записей для автокомплект
                        query:{
                            sortby:{
                                name:'ASC'
                            }
                        },
                        where:{
                            "name:LIKE":"%query%", // Условие поиска в поле
                        },
                        limit:0, // число показываемых записей при поиске.
                    },
                }
            },
        }
    }
}
