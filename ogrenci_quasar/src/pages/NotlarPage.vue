<template>
  <q-page class="q-pa-md column q-gutter-md">
    <q-card bordered flat>
      <q-card-section class="text-h6">Notlar</q-card-section>
      <q-separator />
      <q-card-section>
        <q-skeleton v-if="loading" type="text" :lines="3" />
        <div v-else>
          <q-banner v-if="error" class="bg-red-2 text-red-10 q-mb-sm">
            {{ error }}
          </q-banner>
          <div v-if="notlar.length" class="row q-col-gutter-md">
            <div
              v-for="n in notlar"
              :key="n.id || n.uuid || JSON.stringify(n)"
              class="col-12 col-sm-6 col-md-4 col-lg-3"
            >
              <q-card bordered flat>
                <q-card-section class="row items-center q-gutter-sm">
                  <q-avatar size="42px" color="grey-3" text-color="grey-8">
                    <q-icon name="menu_book" />
                  </q-avatar>
                  <div class="text-subtitle1 ellipsis">{{ courseName(n) }}</div>
                </q-card-section>
                <q-card-section class="q-pt-none">
                  <div class="text-body2 text-grey-7">Öğrenci</div>
                  <div class="text-subtitle2">{{ studentName(n) }}</div>
                </q-card-section>
                <q-separator />
                <q-card-section class="flex flex-center">
                  <q-chip color="primary" text-color="white" size="lg">
                    {{ noteValue(n) }}
                  </q-chip>
                </q-card-section>
              </q-card>
            </div>
          </div>
          <div v-else class="text-grey">Kayıt yok</div>
        </div>
      </q-card-section>
    </q-card>

    <div>
      <q-btn color="primary" label="Yenile" :loading="loading" @click="fetchNotlar" />
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

const notlar = ref([])
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

async function fetchNotlar() {
  loading.value = true
  try {
    const { data } = await api.get('/notlar')
    notlar.value = Array.isArray(data) ? data : (data?.data ?? [])
    error.value = ''
  } catch (e) {
    error.value = parseError(e)
    notlar.value = []
  } finally {
    loading.value = false
  }
}

onMounted(fetchNotlar)

function noteValue(n) {
  // Olası alanlar: deger, puan, not, score, value
  const v = n.deger ?? n.puan ?? n.not ?? n.score ?? n.value
  if (v !== undefined && v !== null && `${v}`.trim() !== '') return v
  return '—'
}

function courseName(n) {
  // Olası kök alanlar: ders, course, ders_bilgi
  const d = n.ders || n.course || n.ders_bilgi || n.dersBilgi || {}
  const ad = d.ad || d.adi || d.adı || d.name || d.title || n.ders_adi || n.dersAdi
  return ad || 'Ders Bilgisi Yok'
}

function studentName(n) {
  // Olası kök alanlar: ogrenci, student
  const o = n.ogrenci || n.student || {}
  const ad = o.ad || o.isim || o.adi || o.name || o.first_name
  const soyad = o.soyad || o.soyisim || o.soy_adi || o.surname || o.last_name
  const birlesik = o.ad_soyad || o.adSoyad || o.isim_soyisim || o.isimSoyisim || o.full_name || o.fullName
  const ikili = `${ad || ''} ${soyad || ''}`.trim()
  return (ikili || birlesik || 'Öğrenci Bilgisi Yok').trim()
}
</script>
