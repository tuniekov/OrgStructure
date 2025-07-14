<template>
  <div id="orgstructure">
    <UniTreePanel :treetabs="treetabs"/>
  </div>
</template>

<script setup>
  import { UniTreePanel } from 'pvtables/dist/pvtables'
  import { ref } from 'vue'

  const treetabs = ref({
    osTree:{ //Имя таба
      type:'tree', // тип таба
      title: 'Структура организации', // подпись таба
      table: 'osTree', // Таблица дерева заданная в _build\configs\gtsapipackages.js
      dragable: true // перемещать узлы
    },
  });
</script>

<style>
 
</style>
