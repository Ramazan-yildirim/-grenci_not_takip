<template>
  <q-page class="q-pa-md column q-gutter-md">
    <q-card bordered flat>
      <q-card-section class="text-h6">Öğrenciler</q-card-section>
      <q-separator />
      <q-card-section>
        <q-skeleton v-if="loading" type="text" :lines="3" />
        <div v-else>
          <q-banner v-if="error" class="bg-red-2 text-red-10 q-mb-sm">
            {{ error }}
          </q-banner>
          <div v-if="ogrenciler.length" class="row q-col-gutter-md">
            <div
              v-for="o in ogrenciler"
              :key="o.id || o.uuid || JSON.stringify(o)"
              class="col-12 col-sm-6 col-md-4 col-lg-3"
            >
              <q-card bordered flat>
                <q-card-section class="flex flex-center q-pt-lg q-pb-sm">
                  <q-avatar size="72px" color="grey-3" text-color="grey-8">
                    <q-icon name="person" size="42px" />
                  </q-avatar>
                </q-card-section>
                <q-card-section class="text-center">
                  <div class="text-subtitle1">{{ fullName(o) }}</div>
                </q-card-section>
              </q-card>
            </div>
          </div>
          <div v-else class="text-grey">Kayıt yok</div>
        </div>
      </q-card-section>
    </q-card>

    <div>
      <q-btn color="primary" label="Yenile" :loading="loading" @click="fetchOgrenciler" />
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

const ogrenciler = ref([])
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

async function fetchOgrenciler() {
  loading.value = true
  try {
    const { data } = await api.get('/ogrenciler')
    ogrenciler.value = Array.isArray(data) ? data : (data?.data ?? [])
    error.value = ''
  } catch (e) {
    error.value = parseError(e)
    ogrenciler.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchOgrenciler)

function fullName(o) {
  // Muhtemel alan adları
  const ad = o.ad || o.isim || o.adi || o.name || o.first_name || ''
  const soyad = o.soyad || o.soyisim || o.soy_adi || o.surname || o.last_name || ''
  // Birleşik alanlar
  const birlesik = o.ad_soyad || o.adSoyad || o.isim_soyisim || o.isimSoyisim || o.full_name || o.fullName || ''

  const ikili = `${ad} ${soyad}`.trim()
  const sonuc = (ikili || birlesik).trim()
  return sonuc || 'İsimsiz Öğrenci'
}
</script>
