<template>
  <div id="orgstructure">
    <Tabs value="0">
      <TabList>
        <Tab value="0">Структура организации</Tab>
        <Tab value="1">Должности</Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0" class="orgstructure-tree">
          <UniTreePanel2 :treetabs="treetabs" storageKey="orgstructure"/>
        </TabPanel>
        <TabPanel value="1">
          <PVTables table="osPost"/>
        </TabPanel>
      </TabPanels>
    </Tabs>
  </div>
</template>

<script setup>
  import { PVTables, UniTreePanel2, Tabs, TabList, Tab, TabPanels, TabPanel } from 'pvtables/dist/pvtables'
  import { ref } from 'vue'

  const treetabs = ref({
    osTree:{
      type:'tree',
      title: 'Структура организации',
      table: 'osTree',
      dragable: true
    },
  });
</script>

<style>
 /* Высоту панель ставит себе сама (UniTreePanel2, fitViewport). Фиксированные
    100dvh здесь давали переполнение: шапка сайта над приложением высоту не
    резервирует, и внизу страницы появлялся лишний внешний скролл. */
 #orgstructure {
  display: flex;
  flex-direction: column;
  width: 100%;
  min-height: 0;
 }

 #orgstructure > div {
  width: 100%;
 }

 /* Только прямые потомки: без этого правила били и по вложенным PVTabs
    внутри правой панели, ломая их высоты. */
 #orgstructure > .p-tabs {
  width: 100%;
  display: flex;
  flex-direction: column;
  min-height: 0;
 }

 #orgstructure > .p-tabs > .p-tablist {
  flex-shrink: 0;
 }

 #orgstructure > .p-tabs > .p-tabpanels {
  flex-grow: 1;
  min-height: 0;
 }

 #orgstructure > .p-tabs > .p-tabpanels > .p-tabpanel {
  min-height: 0;
 }

 /* Панель дерева сама раздаёт высоту внутри себя — отступы и скролл здесь лишние. */
 #orgstructure > .p-tabs > .p-tabpanels > .p-tabpanel.orgstructure-tree {
  overflow: hidden;
  padding: 0;
 }
</style>
