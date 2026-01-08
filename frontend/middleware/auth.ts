export default defineNuxtRouteMiddleware((to, from) => {
    const { user, loading } = useAuth()

    // Wait for auth to initialize
    if (loading.value) return

    if (!user.value && to.path !== '/login') {
        return navigateTo('/login')
    }

    if (user.value && to.path === '/login') {
        return navigateTo('/')
    }
})
