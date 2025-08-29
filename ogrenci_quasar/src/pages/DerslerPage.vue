<template>
  <q-page class="q-pa-md">
    <q-table
      flat
      bordered
      title="Dersler"
      :rows="dersler"
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
          label="Ders Ekle"
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
          @click="fetchDersler"
        />
      </template>

      <template v-slot:body-cell-actions="props">
        <q-td :props="props" class="q-gutter-sm">
          <q-btn
            icon="edit"
            color="info"
            flat
            dense
            round
            @click="openEditDialog(props.row)"
          />
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
          <div class="text-h6">{{ dialog.isEdit ? 'Ders Düzenle' : 'Ders Ekle' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-input
            v-model="form.ad"
            label="Ders Adı"
            dense
            outlined
            :rules="[val => !!val || 'Ders adı zorunludur']"
            autofocus
          />
          <q-select
            v-model="form.egitmen_id"
            :options="egitmenler"
            :option-label="instructorName"
            option-value="id"
            emit-value
            map-options
            label="Eğitmen"
            dense
            outlined
            :loading="optionsLoading"
            :rules="[val => !!val || 'Eğitmen seçimi zorunludur']"
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

const $q = useQuasar()
const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  timeout: 10000,
  headers: { Accept: 'application/json' },
})

const dersler = ref([])
const egitmenler = ref([])
const loading = ref(false)
const optionsLoading = ref(false)
const filter = ref('')
const saving = ref(false)

const getRowKey = (row) => row.id || row.uuid;

const courseName = (d) => {
  if (!d) return ''
  return d.ad || d.adi || d['adı'] || d.name || d.title || 'Ders Bilgisi Yok'
}

const instructorName = (d) => {
  if (!d) return ''
  // Handle both direct instructor object and nested instructor object
  const e = d.egitmen || d.instructor || d.hoca || d
  const ad = e.ad || e.isim || e.adi || e.name || e.first_name
  const soyad = e.soyad || e.soyisim || e.soy_adi || e.surname || e.last_name
  const birlesik = e.ad_soyad || e.adSoyad || e.isim_soyisim || e.isimSoyisim || e.full_name || e.fullName
  const ikili = `${ad || ''} ${soyad || ''}`.trim()
  return (ikili || birlesik || '—').trim()
}

const columns = [
  { name: 'sira', label: 'Sıra', align: 'left', field: 'sira', sortable: false },
  { name: 'ad', label: 'Ders Adı', align: 'left', field: row => courseName(row), format: val => val, sortable: true },
  { name: 'egitmen', label: 'Eğitmen', align: 'left', field: row => instructorName(row), format: val => val, sortable: true },
  { name: 'actions', label: 'İşlemler', align: 'right', field: 'actions' },
]

const form = ref({
  id: null,
  uuid: null,
  ad: '',
  egitmen_id: null,
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

async function fetchDersler() {
  loading.value = true
  try {
    const { data } = await api.get('/dersler')
    dersler.value = Array.isArray(data) ? data : (data?.data ?? [])
  } catch (e) {
    parseError(e)
    dersler.value = []
  } finally {
    loading.value = false
  }
}

async function fetchEgitmenler() {
  optionsLoading.value = true
  try {
    const { data } = await api.get('/egitmenler')
    egitmenler.value = Array.isArray(data) ? data : (data?.data ?? [])
  } catch (e) {
    parseError(e)
  } finally {
    optionsLoading.value = false
  }
}

function resetForm() {
  form.value = { id: null, uuid: null, ad: '', egitmen_id: null }
}

function openAddDialog() {
  resetForm()
  dialog.value = { show: true, isEdit: false }
  if (egitmenler.value.length === 0) {
    fetchEgitmenler()
  }
}

function openEditDialog(ders) {
  resetForm()
  form.value = {
    ...ders,
    ad: courseName(ders),
    egitmen_id: ders.egitmen?.id || null
  }
  dialog.value = { show: true, isEdit: true }
  if (egitmenler.value.length === 0) {
    fetchEgitmenler()
  }
}

async function submitForm() {
  if (!form.value.ad?.trim() || !form.value.egitmen_id) {
    $q.notify({ type: 'warning', message: 'Ders adı ve eğitmen alanları zorunludur.' })
    return
  }
  saving.value = true
  try {
    const payload = {
      ad: form.value.ad,
      egitmen_id: form.value.egitmen_id,
    }
    if (dialog.value.isEdit) {
      const key = getRowKey(form.value)
      await api.put(`/dersler/${key}`, payload)
      $q.notify({ type: 'positive', message: 'Ders başarıyla güncellendi.' })
    } else {
      await api.post('/dersler', payload)
      $q.notify({ type: 'positive', message: 'Ders başarıyla eklendi.' })
    }
    dialog.value.show = false
    await fetchDersler()
  } catch (e) {
    parseError(e)
  } finally {
    saving.value = false
  }
}

function confirmDelete(ders) {
  const key = getRowKey(ders)
  $q.dialog({
    title: 'Silme Onayı',
    message: `<b>${courseName(ders)}</b> adlı dersi silmek istediğinizden emin misiniz?`,
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
      await api.delete(`/dersler/${key}`)
      $q.notify({ type: 'positive', message: 'Ders başarıyla silindi.' })
      await fetchDersler()
    } catch (e) {
      parseError(e)
    } finally {
      $q.loading.hide()
    }
  })
}

onMounted(fetchDersler)
</script>