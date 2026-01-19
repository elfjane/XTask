export const useTheme = () => {
    const isDark = useState('theme_is_dark', () => false)

    const toggleTheme = () => {
        isDark.value = !isDark.value
        applyTheme()
    }

    const applyTheme = () => {
        if (process.client) {
            if (isDark.value) {
                document.documentElement.classList.add('dark-mode')
                localStorage.setItem('theme', 'dark')
            } else {
                document.documentElement.classList.remove('dark-mode')
                localStorage.setItem('theme', 'light')
            }
        }
    }

    const initTheme = () => {
        if (process.client) {
            const savedTheme = localStorage.getItem('theme')
            const preferDark = window.matchMedia('(prefers-color-scheme: dark)').matches

            if (savedTheme === 'dark' || (!savedTheme && preferDark)) {
                isDark.value = true
            } else {
                isDark.value = false
            }
            applyTheme()
        }
    }

    return {
        isDark,
        toggleTheme,
        initTheme
    }
}
