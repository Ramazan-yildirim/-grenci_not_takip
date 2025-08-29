<template>
  <q-page class="q-pa-md column q-gutter-md">
    <div class="column q-gutter-md">
      <div v-if="showDersler">
        <q-card bordered flat>
          <q-card-section class="text-h6">Dersler</q-card-section>
          <q-separator />
          <q-card-section>
            <q-skeleton v-if="loading" type="text" :lines="3" />
            <div v-else>
              <q-banner v-if="errors.ders" class="bg-red-2 text-red-10 q-mb-sm">
                {{ errors.ders }}
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
      </div>

      <div v-if="showEgitmenler">
        <q-card bordered flat>
          <q-card-section class="text-h6">Eğitmenler</q-card-section>
          <q-separator />
          <q-card-section>
            <q-skeleton v-if="loading" type="text" :lines="3" />
            <div v-else>
              <q-banner v-if="errors.egitmen" class="bg-red-2 text-red-10 q-mb-sm">
                {{ errors.egitmen }}
              </q-banner>
              <q-list bordered separator v-if="egitmenler.length">
                <q-item v-for="e in egitmenler" :key="e.id || e.uuid || JSON.stringify(e)" dense>
                  <q-item-section>{{ e.ad || e.name || JSON.stringify(e) }}</q-item-section>
                </q-item>
              </q-list>
              <div v-else class="text-grey">Kayıt yok</div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div v-if="showOgrenciler">
        <q-card bordered flat>
          <q-card-section class="text-h6">Öğrenciler</q-card-section>
          <q-separator />
          <q-card-section>
            <q-skeleton v-if="loading" type="text" :lines="3" />
            <div v-else>
              <q-banner v-if="errors.ogrenci" class="bg-red-2 text-red-10 q-mb-sm">
                {{ errors.ogrenci }}
              </q-banner>
              <q-list bordered separator v-if="ogrenciler.length">
                <q-item v-for="o in ogrenciler" :key="o.id || o.uuid || JSON.stringify(o)" dense>
                  <q-item-section>{{ o.ad || o.name || JSON.stringify(o) }}</q-item-section>
                </q-item>
              </q-list>
              <div v-else class="text-grey">Kayıt yok</div>
            </div>
          </q-card-section>
        </q-card>
      </div>

      <div v-if="showNotlar">
        <q-card bordered flat>
          <q-card-section class="text-h6">Notlar</q-card-section>
          <q-separator />
          <q-card-section>
            <q-skeleton v-if="loading" type="text" :lines="3" />
            <div v-else>
              <q-banner v-if="errors.not" class="bg-red-2 text-red-10 q-mb-sm">
                {{ errors.not }}
              </q-banner>
              <q-list bordered separator v-if="notlar.length">
                <q-item v-for="n in notlar" :key="n.id || n.uuid || JSON.stringify(n)" dense>
                  <q-item-section>{{ n.deger || n.puan || JSON.stringify(n) }}</q-item-section>
                </q-item>
              </q-list>
              <div v-else class="text-grey">Kayıt yok</div>
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>

    <div class="row">
      <div class="col-12">
        <q-btn color="primary" label="Yenile" :loading="loading" @click="fetchVisible" />
      </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 10000,
  headers: { Accept: 'application/json' },
})

const dersler = ref([])
const egitmenler = ref([])
const ogrenciler = ref([])
const notlar = ref([])
const loading = ref(false)
const errors = ref({ ders: '', egitmen: '', ogrenci: '', not: '' })

// Route section kontrolü
const route = useRoute()
const section = computed(() => (route.query.section || '').toString())
const showDersler = computed(() => !section.value || section.value === 'dersler')
const showEgitmenler = computed(() => !section.value || section.value === 'egitmenler')
const showOgrenciler = computed(() => !section.value || section.value === 'ogrenciler')
const showNotlar = computed(() => !section.value || section.value === 'notlar')

// Çalıştığı teyit edilen kesin endpoint'ler kullanılacak

async function fetchDersler() {
  try {
    const { data } = await api.get('/dersler')
    dersler.value = Array.isArray(data) ? data : (data?.data ?? [])
    errors.value.ders = ''
  } catch (err) {
    errors.value.ders = parseError(err)
    dersler.value = []
    console.error('Dersler hatası:', err)
  }
}

async function fetchEgitmenler() {
  try {
    const { data } = await api.get('/egitmenler')
    egitmenler.value = Array.isArray(data) ? data : (data?.data ?? [])
    errors.value.egitmen = ''
  } catch (err) {
    errors.value.egitmen = parseError(err)
    egitmenler.value = []
    console.error('Eğitmenler hatası:', err)
  }
}

async function fetchOgrenciler() {
  try {
    const { data } = await api.get('/ogrenciler')
    ogrenciler.value = Array.isArray(data) ? data : (data?.data ?? [])
    errors.value.ogrenci = ''
  } catch (err) {
    errors.value.ogrenci = parseError(err)
    ogrenciler.value = []
    console.error('Öğrenciler hatası:', err)
  }
}

async function fetchNotlar() {
  try {
    const { data } = await api.get('/notlar')
    notlar.value = Array.isArray(data) ? data : (data?.data ?? [])
    errors.value.not = ''
  } catch (err) {
    errors.value.not = parseError(err)
    notlar.value = []
    console.error('Notlar hatası:', err)
  }
}

function parseError(err) {
  // Sunucudan yanıt geldiyse
  if (err?.response) {
    const status = err.response.status
    const msg = err.response.data?.message || err.message || 'Sunucu hatası'
    return `${status} - ${msg}`
  }
  // İstek gönderildi ama hiç yanıt gelmediyse (Network Error)
  if (err?.request) {
    const hints = [
      'Sunucu kapalı olabilir',
      'CORS engeli (Access-Control-Allow-Origin yok)',
      'Yanlış baseURL veya port',
      'Mixed content (https -> http istek)',
      'Firewall / proxy engeli',
    ]
    return `Network Error - ${err.code || 'ERR_NETWORK'} | Olası nedenler: ${hints.join(', ')}`
  }
  // İstek oluşturulmadan hata
  return err?.message || 'Bilinmeyen hata'
}

async function fetchVisible() {
  loading.value = true
  const tasks = []
  if (showDersler.value) tasks.push(fetchDersler())
  if (showEgitmenler.value) tasks.push(fetchEgitmenler())
  if (showOgrenciler.value) tasks.push(fetchOgrenciler())
  if (showNotlar.value) tasks.push(fetchNotlar())
  await Promise.allSettled(tasks)
  loading.value = false
}

onMounted(fetchVisible)

watch(section, () => {
  fetchVisible()
})
</script>
