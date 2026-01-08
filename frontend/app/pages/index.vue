<template>
  <div class="dashboard">
    <div class="hero">
      <div class="hero-content">
        <h1>{{ $t('common.welcome') }} <span class="accent">XTask</span></h1>
        <p>{{ $t('hero.subtitle') }}</p>
        <div class="actions">
          <NuxtLink to="/tasks" class="btn-primary">{{ $t('common.getStarted') }}</NuxtLink>
          <NuxtLink to="/schedules" class="btn-outline">{{ $t('common.viewSchedules') }}</NuxtLink>
        </div>
      </div>
      <div class="hero-image">
        <div class="abstract-shape"></div>
      </div>
    </div>

    <div class="features-grid">
      <div class="feature-card">
        <div class="icon-box purple">📅</div>
        <h3>{{ $t('features.smartScheduling') }}</h3>
        <p>{{ $t('features.smartSchedulingDesc') }}</p>
      </div>
      <div class="feature-card">
        <div class="icon-box orange">✅</div>
        <h3>{{ $t('features.taskTracking') }}</h3>
        <p>{{ $t('features.taskTrackingDesc') }}</p>
      </div>
      <div class="feature-card">
        <div class="icon-box blue">👥</div>
        <h3>{{ $t('features.collaboration') }}</h3>
        <p>{{ $t('features.collaborationDesc') }}</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  middleware: 'auth'
})
</script>

<style scoped>
.dashboard {
  padding: 1rem 0;
}

.hero {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: white;
  padding: 4rem;
  border-radius: 24px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.03);
  margin-bottom: 4rem;
  overflow: hidden;
  position: relative;
}

.hero-content {
  flex: 1;
  max-width: 600px;
  z-index: 2;
}

.hero-content h1 {
  font-size: 3.5rem;
  font-weight: 800;
  line-height: 1.1;
  margin-bottom: 1.5rem;
  color: #111;
}

.accent {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.hero-content p {
  font-size: 1.2rem;
  color: #666;
  margin-bottom: 2.5rem;
  line-height: 1.6;
}

.actions {
  display: flex;
  gap: 1.5rem;
}

.btn-primary {
  background: #764ba2;
  color: white;
  padding: 14px 28px;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 700;
  transition: transform 0.2s, box-shadow 0.2s;
  box-shadow: 0 10px 15px -3px rgba(118, 75, 162, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 20px 25px -5px rgba(118, 75, 162, 0.4);
}

.btn-outline {
  background: transparent;
  color: #764ba2;
  border: 2px solid #764ba2;
  padding: 12px 26px;
  border-radius: 12px;
  text-decoration: none;
  font-weight: 700;
  transition: background 0.2s;
}

.btn-outline:hover {
  background: rgba(118, 75, 162, 0.05);
}

.hero-image {
  flex: 1;
  display: flex;
  justify-content: flex-end;
  position: relative;
}

.abstract-shape {
  width: 300px;
  height: 300px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
  animation: morph 10s infinite ease-in-out;
  opacity: 0.1;
  position: absolute;
  top: -50px;
  right: -50px;
}

@keyframes morph {
  0% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
  50% { border-radius: 70% 30% 30% 70% / 70% 70% 30% 30%; }
  100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.feature-card {
  background: white;
  padding: 2.5rem;
  border-radius: 20px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.02);
  transition: transform 0.3s;
}

.feature-card:hover {
  transform: translateY(-10px);
}

.icon-box {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
}

.icon-box.purple { background: rgba(118, 75, 162, 0.1); }
.icon-box.orange { background: rgba(255, 152, 0, 0.1); }
.icon-box.blue { background: rgba(33, 150, 243, 0.1); }

.feature-card h3 {
  margin-top: 0;
  margin-bottom: 1rem;
  color: #111;
  font-size: 1.25rem;
}

.feature-card p {
  color: #666;
  line-height: 1.5;
  margin: 0;
}
</style>
