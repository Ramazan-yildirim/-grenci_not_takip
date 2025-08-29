<template>
  <q-page class="q-pa-md column q-gutter-md">
    <q-card bordered flat>
      <q-card-section class="text-h6">Eğitmenler</q-card-section>
      <q-separator />
      <q-card-section>
        <q-skeleton v-if="loading" type="text" :lines="3" />
        <div v-else>
          <q-banner v-if="error" class="bg-red-2 text-red-10 q-mb-sm">
            {{ error }}
          </q-banner>
          <div v-if="egitmenler.length" class="row q-col-gutter-md">
            <div
              v-for="e in egitmenler"
              :key="e.id || e.uuid || JSON.stringify(e)"
              class="col-12 col-sm-6 col-md-4 col-lg-3"
            >
              <q-card bordered flat>
                <q-card-section class="flex flex-center q-pt-lg q-pb-sm">
                  <q-avatar size="72px" color="grey-3" text-color="grey-8">
                    <q-icon name="person" size="42px" />
                  </q-avatar>
                </q-card-section>
                <q-card-section class="text-center">
                  <div class="text-subtitle1">{{ fullName(e) }}</div>
                </q-card-section>
              </q-card>
            </div>
          </div>
          <div v-else class="text-grey">Kayıt yok</div>
        </div>
      </q-card-section>
    </q-card>

    <div>
      <q-btn color="primary" label="Yenile" :loading="loading" @click="fetchEgitmenler" />
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

const egitmenler = ref([])
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

async function fetchEgitmenler() {
  loading.value = true
  try {
    const { data } = await api.get('/egitmenler')
    egitmenler.value = Array.isArray(data) ? data : (data?.data ?? [])
    error.value = ''
  } catch (e) {
    error.value = parseError(e)
    egitmenler.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchEgitmenler)

function fullName(e) {
  const ad = e.ad || e.isim || e.adi || e.name || e.first_name || ''
  const soyad = e.soyad || e.soyisim || e.soy_adi || e.surname || e.last_name || ''
  const birlesik = e.ad_soyad || e.adSoyad || e.isim_soyisim || e.isimSoyisim || e.full_name || e.fullName || ''
  const ikili = `${ad} ${soyad}`.trim()
  const sonuc = (ikili || birlesik).trim()
  return sonuc || 'İsimsiz Eğitmen'
}
</script>
