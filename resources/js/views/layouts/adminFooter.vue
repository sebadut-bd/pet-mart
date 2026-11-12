<template>
    <footer class="pm-footer">
        <span>© {{ year }} PetMart · Bangladesh pet marketplace</span>
        <span>Admin panel</span>
    </footer>
</template>

<script>
export default {
    name: "adminFooter",
    data() {
        return { year: new Date().getFullYear() };
    },
}
</script>
