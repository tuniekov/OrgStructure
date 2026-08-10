export default {
    'debug':{
        'xtype':'combo-boolean',
        'value':0,
        'area':'orgstructure_main',
    },
    // Обратная синхра OrgStructure → legacy gtsBStaff/gts_departarment_staff_links.
    // 1 (по умолчанию) — синкать (office); 0 — не писать в legacy (например modx28).
    'sync_legacy':{
        'xtype':'combo-boolean',
        'value':1,
        'area':'orgstructure_main',
    },
}