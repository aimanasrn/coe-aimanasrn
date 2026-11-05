type ActionToastType = 'success' | 'error' | 'warning'

const icons: Record<ActionToastType, string> = {
  success: 'i-lucide-circle-check',
  error: 'i-lucide-circle-x',
  warning: 'i-lucide-triangle-alert'
}

export function useActionToast() {
  const toast = useToast()

  function show(type: ActionToastType, title: string, description: string) {
    return toast.add({ title, description, color: type, icon: icons[type], duration: 3000 })
  }

  return {
    success: (title: string, description: string) => show('success', title, description),
    error: (title: string, description: string) => show('error', title, description),
    warning: (title: string, description: string) => show('warning', title, description)
  }
}
