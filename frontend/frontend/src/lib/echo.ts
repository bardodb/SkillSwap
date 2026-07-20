import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}

window.Pusher = Pusher

let echo: Echo<'reverb'> | null = null

export function conversationChannelName(userA: number, userB: number): string {
  const min = Math.min(userA, userB)
  const max = Math.max(userA, userB)
  return `conversation.${min}.${max}`
}

export function getEcho(): Echo<'reverb'> {
  if (echo) return echo

  echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8081),
    wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 8081),
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: `${(import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/api$/, '')}/broadcasting/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${localStorage.getItem('token') ?? ''}`,
        Accept: 'application/json',
      },
    },
  })

  return echo
}

export function disconnectEcho(): void {
  echo?.disconnect()
  echo = null
}
