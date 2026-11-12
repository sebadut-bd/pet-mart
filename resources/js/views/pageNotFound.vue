<template>
    <div class="pm-card">
        <div class="pm-empty py-5">
            <div class="pm-empty-icon"><i class="fa-solid fa-compass"></i></div>
            <h4>Page not found</h4>
            <p class="mb-3">The page you opened does not exist or has moved.</p>
            <router-link to="/admin/dashboard" class="btn btn-primary"><i class="fa-solid fa-gauge-high"></i> Back to dashboard</router-link>
        </div>
    </div>
</template>

<script>
export default {
    name: "pageNotFound",
}
</script>
