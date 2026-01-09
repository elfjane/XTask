export default defineNuxtRouteMiddleware(async (to, from) => {
    const { user, fetchMe, token } = useAuth()

    // If we have a token but no user, try to fetch the user first
    if (token.value && !user.value) {
        await fetchMe()
    }

    const allowedRoles = ['admin', 'manager', 'auditor']
    if (!user.value || !allowedRoles.includes(user.value.role)) {
        return navigateTo('/')
    }
})
