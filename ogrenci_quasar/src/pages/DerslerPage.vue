<template>
  <q-page class="q-pa-md column q-gutter-md">
    <q-card bordered flat>
      <q-card-section class="text-h6">Dersler</q-card-section>
      <q-separator />
      <q-card-section>
        <q-skeleton v-if="loading" type="text" :lines="3" />
        <div v-else>
          <q-banner v-if="error" class="bg-red-2 text-red-10 q-mb-sm">
            {{ error }}
          </q-banner>
          <q-list bordered separator v-if="dersler.length">
            <q-item v-for="d in dersler" :key="d.id || d.uuid || JSON.stringify(d)" dense>
              <q-item-section>{{ d.ad || d.name || d.title || JSON.stringify(d) }}</q-item-section>
            </q-item>
          </q-list>
          <div v-else class="text-grey">Kayıt yok</div>
        </div>
      </q-card-section>
    </q-card>

    <div>
      <q-btn color="primary" label="Yenile" :loading="loading" @click="fetchDersler" />
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 10000,
  headers: { Accept: 'application/json' },
})

const dersler = ref([])
const loading = ref(false)
const error = ref('')

function parseError(err) {
  if (err?.response) {
    const { status, statusText, data } = err.response
    const detail = data?.message || data?.error || JSON.stringify(data)
    return `HTTP ${status} ${statusText} - ${detail}`
  }
  if (err?.request) {
    const hints = [
      'Sunucu kapalı veya erişilemez',
      'CORS engeli',
      'Yanlış baseURL veya port',
      'Mixed content (https -> http istek)',
      'Firewall / proxy engeli',
    ]
    return `Network Error - ${err.code || 'ERR_NETWORK'} | Olası nedenler: ${hints.join(', ')}`
  }
  return err?.message || 'Bilinmeyen hata'
}

async function fetchDersler() {
  loading.value = true
  try {
    const { data } = await api.get('/dersler')
    dersler.value = Array.isArray(data) ? data : (data?.data ?? [])
    error.value = ''
  } catch (e) {
    error.value = parseError(e)
    dersler.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchDersler)
</script>
