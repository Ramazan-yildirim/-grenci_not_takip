<template>
  <q-page class="q-pa-md welcome-hero">
    <img :src="tux" alt="penguin placeholder" class="bg-placeholder" />
    <q-table
      flat
      bordered
      title="Eğitmenler"
      :rows="egitmenler"
      :columns="columns"
      :row-key="getRowKey"
      :loading="loading"
      :filter="filter"
      no-data-label="Kayıt bulunamadı"
      loading-label="Yükleniyor..."
      rows-per-page-label="Sayfa başına kayıt"
    >
      <template v-slot:body-cell-sira="props">
        <q-td :props="props">
          {{ props.rowIndex + 1 }}
        </q-td>
      </template>
      <template v-slot:top-right>
        <q-input borderless dense debounce="300" v-model="filter" placeholder="Ara">
          <template v-slot:append>
            <q-icon name="search" />
          </template>
        </q-input>
        <q-btn
          color="primary"
          icon="add"
          label="Eğitmen Ekle"
          @click="openAddDialog()"
          class="q-ml-md"
        />
        <q-btn
          flat
          round
          dense
          icon="refresh"
          class="q-ml-sm"
          :loading="loading"
          @click="fetchEgitmenler"
        />
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn icon="edit" color="info" flat dense round @click="openEditDialog(props.row)" />
          <q-btn
            icon="delete"
            color="negative"
            flat
            dense
            round
            @click="confirmDelete(props.row)"
          />
        </q-td>
      </template>

      <template v-slot:no-data="{ icon, message }">
        <div class="full-width row flex-center text-grey q-gutter-sm q-py-lg">
          <q-icon size="2em" :name="filter ? 'filter_b_and_w' : icon" />
          <span>{{ filter ? 'Arama sonucu eşleşmedi.' : message }}</span>
        </div>
      </template>

      <template v-slot:loading>
        <q-inner-loading showing color="primary" />
      </template>
    </q-table>

    <q-dialog v-model="dialog.show">
      <q-card style="min-width: 420px">
        <q-card-section class="row items-center q-pb-none">
          <div class="text-h6">{{ dialog.isEdit ? 'Eğitmen Düzenle' : 'Eğitmen Ekle' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input
            v-model="form.ad"
            label="Ad"
            dense
            outlined
            :rules="[(val) => !!val || 'Ad alanı zorunludur']"
            autofocus
          />
          <q-input
            v-model="form.soyad"
            label="Soyad"
            dense
            outlined
            :rules="[(val) => !!val || 'Soyad alanı zorunludur']"
          />
        </q-card-section>
        <q-card-actions align="right">
          <q-btn flat label="Vazgeç" v-close-popup />
          <q-btn color="primary" :loading="saving" label="Kaydet" @click="submitForm" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'
import axios from 'axios'
import tux from '../../Linux_mascot_tux.png'

const $q = useQuasar()
const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 10000,
  headers: { Accept: 'application/json' },
})

const egitmenler = ref([])
const loading = ref(false)
const filter = ref('')
const saving = ref(false)

const getRowKey = (row) => row.id || row.uuid

const fullName = (e) => {
  if (!e) return ''
  const ad = e.ad || e.isim || e.adi || e.name || e.first_name || ''
  const soyad = e.soyad || e.soyisim || e.soy_adi || e.surname || e.last_name || ''
  const birlesik =
    e.ad_soyad || e.adSoyad || e.isim_soyisim || e.isimSoyisim || e.full_name || e.fullName || ''
  const ikili = `${ad} ${soyad}`.trim()
  return (ikili || birlesik).trim() || 'İsimsiz Eğitmen'
}

const columns = [
  { name: 'sira', label: 'Sıra', align: 'left', field: 'sira', sortable: false },
  {
    name: 'fullName',
    label: 'Ad Soyad',
    align: 'left',
    field: (row) => fullName(row),
    format: (val) => val,
    sortable: true,
  },
  { name: 'actions', label: 'İşlemler', align: 'right', field: 'actions' },
]

const form = ref({
  id: null,
  uuid: null,
  ad: '',
  soyad: '',
})

const dialog = ref({
  show: false,
  isEdit: false,
})

function parseError(err) {
  let message = 'Bilinmeyen bir hata oluştu.'
  if (err?.response) {
    const { status, statusText, data } = err.response
    const detail = data?.message || data?.error || JSON.stringify(data)
    message = `HTTP ${status} ${statusText} - ${detail}`
  } else if (err?.request) {
    message = `Network Error: Sunucuya ulaşılamadı. API'nin çalıştığından emin olun.`
  } else if (err?.message) {
    message = err.message
  }
  $q.notify({ type: 'negative', message })
}

async function fetchEgitmenler() {
  loading.value = true
  try {
    const { data } = await api.get('/egitmenler')
    egitmenler.value = Array.isArray(data) ? data : (data?.data ?? [])
  } catch (e) {
    parseError(e)
    egitmenler.value = []
  } finally {
    loading.value = false
  }
}

function resetForm() {
  form.value = { id: null, uuid: null, ad: '', soyad: '' }
}

function openAddDialog() {
  resetForm()
  dialog.value = { show: true, isEdit: false }
}

function openEditDialog(egitmen) {
  resetForm()
  const ad = egitmen.ad || egitmen.isim || egitmen.adi || egitmen.name || egitmen.first_name || ''
  const soyad =
    egitmen.soyad ||
    egitmen.soyisim ||
    egitmen.soy_adi ||
    egitmen.surname ||
    egitmen.last_name ||
    ''
  form.value = { ...egitmen, ad, soyad }
  dialog.value = { show: true, isEdit: true }
}

async function submitForm() {
  if (!form.value.ad?.trim() || !form.value.soyad?.trim()) {
    $q.notify({ type: 'warning', message: 'Ad ve Soyad alanları zorunludur.' })
    return
  }
  saving.value = true
  try {
    const payload = {
      ad: form.value.ad,
      soyad: form.value.soyad,
      ad_soyad: `${form.value.ad} ${form.value.soyad}`.trim(),
      adSoyad: `${form.value.ad} ${form.value.soyad}`.trim(),
      full_name: `${form.value.ad} ${form.value.soyad}`.trim(),
    }
    if (dialog.value.isEdit) {
      const key = getRowKey(form.value)
      await api.put(`/egitmenler/${key}`, payload)
      $q.notify({ type: 'positive', message: 'Eğitmen başarıyla güncellendi.' })
    } else {
      await api.post('/egitmenler', payload)
      $q.notify({ type: 'positive', message: 'Eğitmen başarıyla eklendi.' })
    }
    dialog.value.show = false
    await fetchEgitmenler()
  } catch (e) {
    parseError(e)
  } finally {
    saving.value = false
  }
}

function confirmDelete(egitmen) {
  const key = getRowKey(egitmen)
  $q.dialog({
    title: 'Silme Onayı',
    message: `<b>${fullName(egitmen)}</b> adlı eğitmeni silmek istediğinizden emin misiniz?`,
    html: true,
    cancel: {
      label: 'Vazgeç',
      flat: true,
    },
    ok: {
      label: 'Sil',
      color: 'negative',
    },
    persistent: true,
  }).onOk(async () => {
    $q.loading.show({ delay: 400 })
    try {
      await api.delete(`/egitmenler/${key}`)
      $q.notify({ type: 'positive', message: 'Eğitmen başarıyla silindi.' })
      await fetchEgitmenler()
    } catch (e) {
      parseError(e)
    } finally {
      $q.loading.hide()
    }
  })
}

onMounted(fetchEgitmenler)
</script>

<style scoped>
.welcome-hero {
  position: relative;
  min-height: 100vh;
  background-color: #cacaca;
}

.bg-placeholder {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 380px;
  max-width: 60vw;
  height: auto;
  opacity: 0.18;
  filter: grayscale(100%);
  pointer-events: none;
  user-select: none;
}
</style>
