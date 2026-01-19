export const useAvatar = () => {
    const config = useRuntimeConfig()
    const apiBase = (config.public.apiBase as string) || ''

    const getAvatarUrl = (userOrPhotoUrl: any) => {
        if (!userOrPhotoUrl) return `https://ui-avatars.com/api/?name=User&background=random`

        let url = ''
        let name = 'User'

        if (typeof userOrPhotoUrl === 'string') {
            url = userOrPhotoUrl
        } else {
            url = userOrPhotoUrl.photo_url
            name = userOrPhotoUrl.name || 'User'
        }

        if (url && url.startsWith('/storage')) {
            return `${apiBase}${url}`
        }

        return url || `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=random`
    }

    return {
        getAvatarUrl
    }
}
