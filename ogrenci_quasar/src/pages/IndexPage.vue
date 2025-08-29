<template>
  <q-page class="welcome-hero">
    <img :src="tux" alt="penguin placeholder" class="bg-placeholder" />
    <div class="welcome-overlay">
      <div class="welcome-content column items-center q-gutter-md">
        <div class="welcome-title">WELCOME LKD</div>
      </div>
    </div>
  </q-page>
</template>

<script setup>
// Bu sayfa statik karşılama ekranıdır
// Proje kökünden görüntü import edilir
import tux from '../../Linux_mascot_tux.png'
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
.welcome-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
.welcome-title {
  color: #fff;
  font-size: 48px;
  font-weight: 700;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
}
@media (max-width: 600px) {
  .welcome-title {
    font-size: 32px;
  }
}
</style>
