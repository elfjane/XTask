export default defineNuxtRouteMiddleware((to, from) => {
    const { isAuthenticated, loading } = useAuth()

    // Wait for auth to initialize
    if (loading.value) return

    // Public routes that don't require authentication
    const publicRoutes = ['/login', '/register']

    if (!isAuthenticated.value && !publicRoutes.includes(to.path)) {
        return navigateTo('/login')
    }

    if (isAuthenticated.value && publicRoutes.includes(to.path)) {
        return navigateTo('/')
    }
})
