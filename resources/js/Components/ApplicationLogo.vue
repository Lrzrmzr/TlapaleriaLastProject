<template>
    <div class="logo-container">
        <img
            src="/images/totoro-logo.png"
            alt="Tlapalería Logo"
            class="logo-image"
        />
    </div>
</template>

<style scoped>
.logo-container {
    display: flex;
    align-items: center;
    justify-content: center;
}

.logo-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.logo-image:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}
</style>
