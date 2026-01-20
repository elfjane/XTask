export const useAuth = () => {
    const user = useState<any>('auth-user', () => null)
    const token = useCookie('auth-token', { maxAge: 60 * 60 * 24 * 7 }) // 1 week
    const loading = ref(false)

    const config = useRuntimeConfig()
    const apiBase = (config.public.apiBase as string) || ''

    const register = async (name: string, email: string, password: string, passwordConfirmation: string) => {
        loading.value = true
        try {
            const data = await $fetch<any>(`${apiBase}/api/register`, {
                method: 'POST',
                body: { name, email, password, password_confirmation: passwordConfirmation }
            })
            token.value = data.token
            user.value = JSON.parse(JSON.stringify(data.user))
            return true
        } catch (err: any) {
            console.error('Registration failed:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    const loginWithEmail = async (email: string, password: string) => {
        loading.value = true
        try {
            const data = await $fetch<any>(`${apiBase}/api/login`, {
                method: 'POST',
                body: { email, password }
            })
            token.value = data.token
            user.value = JSON.parse(JSON.stringify(data.user))
            return true
        } catch (err: any) {
            console.error('Login failed:', err)
            throw err
        } finally {
            loading.value = false
        }
    }

    const logout = async () => {
        try {
            if (token.value) {
                await $fetch(`${apiBase}/api/logout`, {
                    method: 'POST',
                    headers: { Authorization: `Bearer ${token.value}` }
                })
            }
        } catch (err) {
            console.error('Logout failed:', err)
        } finally {
            token.value = null
            user.value = null
            navigateTo('/login')
        }
    }

    const fetchMe = async () => {
        if (!token.value) return
        try {
            const data = await $fetch<any>(`${apiBase}/api/me`, {
                headers: { Authorization: `Bearer ${token.value}` }
            })
            user.value = JSON.parse(JSON.stringify(data))
        } catch (err) {
            console.error('Fetch me failed:', err)
            token.value = null
            user.value = null
        }
    }

    const getToken = async () => {
        return token.value
    }

    const hasRole = (roleOrRoles: string | string[]) => {
        if (!user.value) return false
        if (Array.isArray(roleOrRoles)) {
            return roleOrRoles.includes(user.value.role)
        }
        return user.value.role === roleOrRoles
    }

    const can = (action: string) => {
        if (!user.value) return false
        const role = user.value.role
        if (role === 'admin') return true

        switch (action) {
            case 'manage-users':
                return role === 'admin'
            case 'manage-departments':
            case 'manage-projects':
                return role === 'manager'
            case 'manage-schedules':
                return role === 'manager'
            case 'manage-tasks':
                return ['manager', 'task_user'].includes(role)
            case 'add-task':
                return ['manager', 'task_user', 'executor'].includes(role)
            case 'review-tasks':
                return ['admin', 'manager', 'auditor'].includes(role)
            case 'view-stats':
                return ['admin', 'manager', 'auditor'].includes(role)
            case 'view-admin':
                return ['admin', 'manager', 'auditor'].includes(role)
            case 'view-users':
                return ['admin', 'auditor'].includes(role)
            default:
                return false
        }
    }

    return {
        user,
        token,
        loading,
        register,
        loginWithEmail,
        logout,
        fetchMe,
        getToken,
        hasRole,
        can,
        isAuthenticated: computed(() => !!token.value)
    }
}
