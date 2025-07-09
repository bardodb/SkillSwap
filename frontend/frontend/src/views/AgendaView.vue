<template>
  <div class="agenda-view">
    <h1>Agenda</h1>
    <section class="events-section">
      <h2>Meus Compromissos</h2>
      <ul>
        <li v-for="event in events" :key="event.id">
          <strong>{{ event.title }}</strong> - {{ event.date }}
        </li>
      </ul>
      <button @click="integrateCalendar">Integrar com Google Calendar</button>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const events = ref([
  { id: 1, title: 'Mentoria de Python', date: '2025-07-10 19:00' },
  { id: 2, title: 'Aula de UI/UX', date: '2025-07-12 14:00' },
])

function integrateCalendar() {
  alert('Integração com Google Calendar em breve!')
}
</script>

<style scoped>
.agenda-view {
  padding: 2rem;
  max-width: 500px;
  margin: 0 auto;
}
.events-section {
  margin-bottom: 2rem;
}
ul {
  padding-left: 1.2rem;
  margin-bottom: 1rem;
}
button {
  background: #42b983;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 0.5rem 1rem;
  cursor: pointer;
}
button:hover {
  background: #369870;
}
</style> 