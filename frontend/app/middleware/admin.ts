export default defineNuxtRouteMiddleware(async (to, from) => {
    const { user, fetchMe, token } = useAuth()

    // If we have a token but no user, try to fetch the user first
    if (token.value && !user.value) {
        await fetchMe()
    }

    if (!user.value || user.value.role !== 'admin') {
        return navigateTo('/')
    }
})
