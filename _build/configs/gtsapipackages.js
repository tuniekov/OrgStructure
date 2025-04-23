export default {
    orgstructure:{
        name:'orgstructure',
        gtsAPITables:{
            osTree:{
                table:'osTree',
                autocomplete_field:'',
                version:12,
                type: 3,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
                gtsAPIUniTreeClass:{
                    osOrg: {
                        title_field: 'name',
                        svg:`
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
                properties: {
                    actions:{
                        create:{
                            tables:{
                                osOrg:{
                                    groups:'Administrator',
                                    label:'Создать организацию',
                                    parent_classes:['root'],
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
                        delete:{
                            groups:'Administrator'
                        },
                    },
                    nodeclick:{
                        classes:{
                            osOrg:{
                                tabs:{
                                    main:{
                                        type:'form',
                                        title:'Основное',
                                        table:'osOrg',
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
                            osFilial:{
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
                    useUniTree : true,
                    extendedModResource : true,
                    rootIds: 0,
                    idField:'id',
                    parentIdField: 'parent_id',
                    parents_idsField: 'parents_ids',
                    menuindexField: 'menuindex',
                    classField: 'class',
                    isLeaf:{
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
                table:'osOrg',
                autocomplete_field:'os_org_id',
                version:2,
                type: 1,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
                properties: {
                    autocomplete:{
                        tpl:'{$name}',
                        where:{
                            "name:LIKE":"%query%",
                        },
                        limit:0,
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
            osFilial:{
                table:'osFilial',
                autocomplete_field:'os_filial_id',
                version:2,
                type: 1,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
                properties: {
                    autocomplete:{
                        tpl:'{$name}',
                        where:{
                            "name:LIKE":"%query%",
                        },
                        limit:0,
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
                table:'osDepartment',
                autocomplete_field:'os_department_id',
                version:2,
                type: 1,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
                properties: {
                    autocomplete:{
                        tpl:'{$name}',
                        where:{
                            "name:LIKE":"%query%",
                        },
                        limit:0,
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
                table:'osPost',
                autocomplete_field:'os_post_id',
                version:1,
                type: 1,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
                properties: {
                    autocomplete:{
                        tpl:'{$name}',
                        where:{
                            "name:LIKE":"%query%",
                        },
                        limit:0,
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
                table:'osEmployee',
                autocomplete_field:'os_employee_id',
                version:2,
                type: 1,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
                properties: {
                    autocomplete:{
                        tpl:'{$name}',
                        where:{
                            "name:LIKE":"%query%",
                        },
                        limit:0,
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
                table:'osEmployeeChild',
                class:'osTree',
                autocomplete_field:'',
                version:4,
                type: 1,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
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
                table:'osAccess',
                autocomplete_field:'os_access_id',
                version:3,
                type: 1,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
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

    modx:{
        name:'modx',
        gtsAPITables:{
            osModUser:{
                table:'osModUser',
                class:'modUser',
                autocomplete_field:'',
                version:2,
                type: 1,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
                properties: {
                    autocomplete:{
                        tpl:'{$username}',
                        query:{
                            sortby:{
                                username:'ASC'
                            }
                        },
                        where:{
                            "username:LIKE":"%query%",
                        },
                        limit:0,
                    },
                }
            },
            modUserGroup:{
                table:'modUserGroup',
                class:'modUserGroup',
                autocomplete_field:'',
                version:1,
                type: 1,
                authenticated:true,
                groups:'',
                permitions:'',
                active:true,
                properties: {
                    autocomplete:{
                        tpl:'{$name}',
                        query:{
                            sortby:{
                                name:'ASC'
                            }
                        },
                        where:{
                            "name:LIKE":"%query%",
                        },
                        limit:0,
                    },
                }
            },
        }
    }
}
