<template>
  <div class="layout">
    <!-- Ambient background orbs -->
    <div class="orb orb-1" aria-hidden="true" />
    <div class="orb orb-2" aria-hidden="true" />

    <!-- Header -->
    <header class="layout-header">
      <div class="container">
        <RouterLink to="/create" class="logo" aria-label="Simple Poll — home">
          <span class="logo-icon" aria-hidden="true">◈</span>
          <span class="logo-text">Simple<strong>Poll</strong></span>
        </RouterLink>
      </div>
    </header>

    <!-- Main content -->
    <main class="layout-main">
      <div class="container">
        <RouterView v-slot="{ Component, route }">
          <Transition name="page" mode="out-in">
            <component :is="Component" :key="route.fullPath" />
          </Transition>
        </RouterView>
      </div>
    </main>

    <!-- Footer -->
    <footer class="layout-footer">
      <div class="container">
        <p class="footer-text">Simple Poll &mdash; create, share, vote.</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { RouterLink, RouterView } from 'vue-router'
</script>

<style scoped>
/* ── Layout shell ─────────────────────────────────────────── */
.layout {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  position: relative;
  isolation: isolate;
}

/* ── Ambient orbs ─────────────────────────────────────────── */
.orb {
  position: fixed;
  border-radius: 50%;
  filter: blur(120px);
  pointer-events: none;
  z-index: -1;
  opacity: 0.35;
}

.orb-1 {
  width: 600px;
  height: 600px;
  background: radial-gradient(circle, #7c3aed 0%, transparent 70%);
  top: -200px;
  right: -150px;
}

.orb-2 {
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, #06b6d4 0%, transparent 70%);
  bottom: -150px;
  left: -100px;
}

/* ── Header ───────────────────────────────────────────────── */
.layout-header {
  padding-block: var(--space-5);
  border-bottom: 1px solid var(--color-border);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  background: rgba(13, 13, 20, 0.7);
  position: sticky;
  top: 0;
  z-index: 10;
}

.logo {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  color: var(--color-text);
  font-size: 1.125rem;
  font-weight: 500;
  letter-spacing: -0.01em;
  text-decoration: none;
  transition: opacity var(--transition-fast);
}
.logo:hover { opacity: 0.8; color: var(--color-text); }

.logo-icon {
  font-size: 1.375rem;
  background: var(--gradient-brand);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.logo-text strong { font-weight: 700; }

/* ── Main ─────────────────────────────────────────────────── */
.layout-main {
  flex: 1;
  padding-block: var(--space-12);
}

/* ── Footer ───────────────────────────────────────────────── */
.layout-footer {
  padding-block: var(--space-6);
  border-top: 1px solid var(--color-border);
}

.footer-text {
  font-size: 0.8125rem;
  color: var(--color-text-dim);
  text-align: center;
}
</style>
