<template>
  <div id="orgstructure">
    <Tabs value="0">
      <TabList>
        <Tab value="0">Структура организации</Tab>
        <Tab value="1">Должности</Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <UniTreePanel :treetabs="treetabs"/>
        </TabPanel>
        <TabPanel value="1">
          <PVTables table="osPost"/>
        </TabPanel>
      </TabPanels>
    </Tabs>
  </div>
</template>

<script setup>
  import { PVTables, UniTreePanel, Tabs, TabList, Tab, TabPanels, TabPanel } from 'pvtables/dist/pvtables'
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
 #orgstructure {
  display: flex;
  flex-direction: column;
  width: 100%;
  height: 100vh;
  overflow: hidden;
 }

 #orgstructure > div {
  width: 100%;
  height: 100%;
 }

 .p-tabs {
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
 }

 .p-tablist {
  flex-shrink: 0;
 }

 .p-tabpanels {
  flex-grow: 1;
  overflow: hidden;
  height: calc(100% - 50px);
 }

 .p-tabpanel {
  height: 100%;
  overflow: auto;
 }
</style>
