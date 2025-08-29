<template>
  <q-page class="q-pa-md">
    <q-table
      flat
      bordered
      title="Notlar"
      :rows="notlar"
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
          label="Not Ekle"
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
          @click="fetchNotlar"
        />
      </template>

      <template v-slot:body-cell-deger="props">
        <q-td :props="props">
          <q-chip
            :color="getGradeColor(props.value)"
            text-color="white"
            dense
            class="text-weight-bold"
          >
            {{ props.value }}
          </q-chip>
        </q-td>
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
          <div class="text-h6">{{ dialog.isEdit ? 'Not Düzenle' : 'Not Ekle' }}</div>
          <q-space />
          <q-btn icon="close" flat round dense v-close-popup />
        </q-card-section>
        <q-card-section class="q-gutter-md">
          <q-select
            v-model="form.ders_id"
            :options="dersler"
            :option-label="opt => courseName(opt, true)"
            option-value="id"
            emit-value
            map-options
            label="Ders"
            dense
            outlined
            :loading="optionsLoading"
            :rules="[val => !!val || 'Ders seçimi zorunludur']"
          />
          <q-select
            v-model="form.ogrenci_id"
            :options="ogrenciler"
            :option-label="opt => studentName(opt, true)"
            option-value="id"
            emit-value
            map-options
            label="Öğrenci"
            dense
            outlined
            :loading="optionsLoading"
            :rules="[val => !!val || 'Öğrenci seçimi zorunludur']"
          />
          <q-input
            v-model.number="form.deger"
            type="number"
            label="Not Değeri"
            dense
            outlined
            :rules="[
              val => val !== null && val !== '' || 'Not değeri zorunludur',
              val => val >= 0 && val <= 100 || 'Not 0-100 arasında olmalıdır'
            ]"
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

const notlar = ref([])
const dersler = ref([])
const ogrenciler = ref([])
const loading = ref(false)
const optionsLoading = ref(false)
const filter = ref('')
const saving = ref(false)

const getRowKey = (row) => row.id || row.uuid;

const noteValue = (n) => {
  if (!n) return '—'
  const v = n.deger ?? n.puan ?? n.not ?? n.score ?? n.value
  if (v !== undefined && v !== null && `${v}`.trim() !== '') return v
  return '—'
}

const courseName = (n, isOption = false) => {
  if (!n) return ''
  const d = isOption ? n : (n.ders || n.course || n.ders_bilgi || n.dersBilgi || {})
  const ad = d.ad || d.adi || d.adı || d.name || d.title || n.ders_adi || n.dersAdi
  return ad || 'Ders Bilgisi Yok'
}

const studentName = (n, isOption = false) => {
  if (!n) return ''
  const o = isOption ? n : (n.ogrenci || n.student || {})
  const ad = o.ad || o.isim || o.adi || o.name || o.first_name
  const soyad = o.soyad || o.soyisim || o.soy_adi || o.surname || o.last_name
  const birlesik = o.ad_soyad || o.adSoyad || o.isim_soyisim || o.isimSoyisim || o.full_name || o.fullName
  const ikili = `${ad || ''} ${soyad || ''}`.trim()
  return (ikili || birlesik || 'Öğrenci Bilgisi Yok').trim()
}

const columns = [
  { name: 'sira', label: 'Sıra', align: 'left', field: 'sira', sortable: false },
  { name: 'ders', label: 'Ders', align: 'left', field: row => courseName(row), format: val => val, sortable: true },
  { name: 'ogrenci', label: 'Öğrenci', align: 'left', field: row => studentName(row), format: val => val, sortable: true },
  { name: 'deger', label: 'Not', align: 'center', field: row => noteValue(row), sortable: true },
  { name: 'actions', label: 'İşlemler', align: 'right', field: 'actions' },
]

const form = ref({
  id: null,
  uuid: null,
  ders_id: null,
  ogrenci_id: null,
  deger: null,
})

const dialog = ref({
  show: false,
  isEdit: false,
})

function getGradeColor(grade) {
  const numericGrade = Number(grade)
  if (isNaN(numericGrade)) return 'grey'
  if (numericGrade < 45) return 'red'
  if (numericGrade < 55) return 'orange'
  if (numericGrade < 70) return 'blue'
  return 'green'
}

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

async function fetchNotlar() {
  loading.value = true
  try {
    const { data } = await api.get('/notlar')
    notlar.value = Array.isArray(data) ? data : (data?.data ?? [])
  } catch (e) {
    parseError(e)
    notlar.value = []
  } finally {
    loading.value = false
  }
}

async function fetchFormOptions() {
  optionsLoading.value = true
  try {
    const [dersRes, ogrRes] = await Promise.all([
      api.get('/dersler'),
      api.get('/ogrenciler'),
    ])
    dersler.value = Array.isArray(dersRes.data) ? dersRes.data : (dersRes.data?.data ?? [])
    ogrenciler.value = Array.isArray(ogrRes.data) ? ogrRes.data : (ogrRes.data?.data ?? [])
  } catch (e) {
    parseError(e)
  } finally {
    optionsLoading.value = false
  }
}

function resetForm() {
  form.value = { id: null, uuid: null, ders_id: null, ogrenci_id: null, deger: null }
}

function openAddDialog() {
  resetForm()
  dialog.value = { show: true, isEdit: false }
  if (dersler.value.length === 0 || ogrenciler.value.length === 0) {
    fetchFormOptions()
  }
}

function openEditDialog(not) {
  resetForm()
  form.value = {
    ...not,
    ders_id: not.ders?.id || null,
    ogrenci_id: not.ogrenci?.id || null,
    deger: noteValue(not),
  }
  dialog.value = { show: true, isEdit: true }
  if (dersler.value.length === 0 || ogrenciler.value.length === 0) {
    fetchFormOptions()
  }
}

async function submitForm() {
  const isFormValid = form.value.ders_id && form.value.ogrenci_id && form.value.deger !== null && form.value.deger >= 0 && form.value.deger <= 100
  if (!isFormValid) {
    $q.notify({ type: 'warning', message: 'Lütfen tüm alanları doğru bir şekilde doldurun.' })
    return
  }
  saving.value = true
  try {
    const payload = {
      ders_id: form.value.ders_id,
      ogrenci_id: form.value.ogrenci_id,
      deger: form.value.deger,
      not: form.value.deger,
      puan: form.value.deger,
      score: form.value.deger,
      value: form.value.deger,
    }
    if (dialog.value.isEdit) {
      const key = getRowKey(form.value)
      await api.put(`/notlar/${key}`, payload)
      $q.notify({ type: 'positive', message: 'Not başarıyla güncellendi.' })
    } else {
      await api.post('/notlar', payload)
      $q.notify({ type: 'positive', message: 'Not başarıyla eklendi.' })
    }
    dialog.value.show = false
    await fetchNotlar()
  } catch (e) {
    parseError(e)
  } finally {
    saving.value = false
  }
}

function confirmDelete(not) {
  const key = getRowKey(not)
  $q.dialog({
    title: 'Silme Onayı',
    message: `<b>${studentName(not)}</b> adlı öğrencinin <b>${courseName(not)}</b> dersine ait notunu silmek istediğinizden emin misiniz?`,
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
      await api.delete(`/notlar/${key}`)
      $q.notify({ type: 'positive', message: 'Not başarıyla silindi.' })
      await fetchNotlar()
    } catch (e) {
      parseError(e)
    } finally {
      $q.loading.hide()
    }
  })
}

onMounted(fetchNotlar)
</script>