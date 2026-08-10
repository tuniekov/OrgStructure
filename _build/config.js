export default {
    name:"OrgStructure",
    name_lower:"orgstructure",
    version:"1.0.1", 
    release:"beta",
    schema:true,
    assets:true,
    core:true,
    update:{
        snippets: true,
        settings: false, // не перезаливать настройки при билде (иначе orgstructure_sync_legacy сбросится в 1, в т.ч. на modx28)
        gtsapirules: true,
        gtsapipackages: true,
    }
}