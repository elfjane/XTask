export const useAuth = () => {
    const user = useState<any>('auth-user', () => null)
    const token = useCookie('auth-token', { maxAge: 60 * 60 * 24 * 7 }) // 1 week
    const loading = ref(false)

    const loginWithEmail = async (email: string, password: string) => {
        loading.value = true
        try {
            const data = await $fetch<any>('/api/login', {
                method: 'POST',
                body: { email, password }
            })
            token.value = data.token
            user.value = data.user
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
                await $fetch('/api/logout', {
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
            const data = await $fetch<any>('/api/me', {
                headers: { Authorization: `Bearer ${token.value}` }
            })
            user.value = data
        } catch (err) {
            console.error('Fetch me failed:', err)
            token.value = null
            user.value = null
        }
    }

    const getToken = async () => {
        return token.value
    }

    return {
        user,
        token,
        loading,
        loginWithEmail,
        logout,
        fetchMe,
        getToken,
        isAuthenticated: computed(() => !!token.value)
    }
}
