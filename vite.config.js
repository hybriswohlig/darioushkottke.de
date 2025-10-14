import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

// Change "ne-innovations" to your actual GitHub repo name
export default defineConfig({
  plugins: [react()],
  base: '/ne-innovations/', 
})
