<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-8">Contato</h1>
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
          <BaseInput
            id="name"
            v-model="form.name"
            type="text"
            required
            class="mt-1"
          />
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <BaseInput
            id="email"
            v-model="form.email"
            type="email"
            required
            class="mt-1"
          />
        </div>
        <div>
          <label for="message" class="block text-sm font-medium text-gray-700">Mensagem</label>
          <textarea
            id="message"
            v-model="form.message"
            rows="4"
            required
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
          ></textarea>
        </div>
        <div>
          <BaseButton type="submit" class="w-full">Enviar Mensagem</BaseButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseButton from '@/components/ui/BaseButton.vue'

const form = ref({
  name: '',
  email: '',
  message: ''
})

const handleSubmit = () => {
  // TODO: Implement contact form submission
  console.log('Form submitted:', form.value)
}
</script> 