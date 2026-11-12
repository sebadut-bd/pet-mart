<template>
    <component
        :is="to ? 'router-link' : 'div'"
        :to="to || undefined"
        class="pm-card pm-stat"
        :class="{ 'is-link': !!to }"
        :style="{ '--accent': color }"
    >
        <span class="pm-stat-icon"><i :class="icon"></i></span>
        <div class="min-w-0">
            <p class="pm-stat-label">{{ label }}</p>
            <p class="pm-stat-value">{{ display }}</p>
            <p v-if="hint" class="pm-stat-hint">{{ hint }}</p>
        </div>
    </component>
</template>

<script>
export default {
    name: "statCard",
    props: {
        label: { type: String, required: true },
        value: { type: [Number, String], default: 0 },
        icon: { type: String, default: 'fa-solid fa-chart-simple' },
        color: { type: String, default: 'var(--pm-c-emerald)' },
        hint: { type: String, default: '' },
        to: { type: String, default: '' },
    },
    computed: {
        display() {
            return typeof this.value === 'number' ? this.value.toLocaleString('en-IN') : this.value;
        },
    },
}
</script>
