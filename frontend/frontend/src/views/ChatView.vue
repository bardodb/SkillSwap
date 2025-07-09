<template>
  <div class="chat-view">
    <h1>Chat</h1>
    <div class="chat-box">
      <div class="messages">
        <div v-for="msg in messages" :key="msg.id" class="message">
          <span class="user">{{ msg.user }}:</span>
          <span class="text">{{ msg.text }}</span>
        </div>
      </div>
      <form class="chat-input" @submit.prevent="sendMessage">
        <input v-model="newMessage" type="text" placeholder="Digite sua mensagem..." required />
        <button type="submit">Enviar</button>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const messages = ref([
  { id: 1, user: 'Você', text: 'Olá! Como posso ajudar?' },
  { id: 2, user: 'Maria', text: 'Quero aprender Python!' },
])
const newMessage = ref('')

function sendMessage() {
  if (!newMessage.value.trim()) return
  messages.value.push({
    id: messages.value.length + 1,
    user: 'Você',
    text: newMessage.value,
  })
  newMessage.value = ''
}
</script>

<style scoped>
.chat-view {
  padding: 2rem;
  max-width: 600px;
  margin: 0 auto;
}
.chat-box {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background: #fafafa;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  height: 400px;
}
.messages {
  flex: 1;
  overflow-y: auto;
  margin-bottom: 1rem;
}
.message {
  margin-bottom: 0.5rem;
}
.user {
  font-weight: bold;
  color: #42b983;
}
.chat-input {
  display: flex;
  gap: 0.5rem;
}
input[type="text"] {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
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