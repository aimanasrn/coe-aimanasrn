type ActionToastType = 'success' | 'error' | 'warning' | 'info'

const icons: Record<ActionToastType, string> = {
  success: 'i-lucide-circle-check',
  error: 'i-lucide-circle-x',
  warning: 'i-lucide-triangle-alert',
  info: 'i-lucide-info'
}

export function useActionToast() {
  const toast = useToast()

  function show(type: ActionToastType, title: string, description: string) {
    return toast.add({
      title,
      description,
      color: type,
      icon: icons[type],
      duration: type === 'error' ? 7000 : 5000,
      close: true,
      progress: {
        ui: {
          base: 'sm-toast-progress-track',
          indicator: 'sm-toast-progress-fill'
        }
      },
      ui: {
        root: `sm-toast sm-toast-${type} ring-0 rounded-xl`,
        icon: 'sm-toast-icon',
        title: 'sm-toast-title',
        description: 'sm-toast-description',
        close: 'sm-toast-close'
      }
    })
  }

  return {
    success: (title: string, description: string) =>
      show('success', title, description),
    error: (title: string, description: string) =>
      show('error', title, description),
    warning: (title: string, description: string) =>
      show('warning', title, description),
    info: (title: string, description: string) =>
      show('info', title, description)
  }
}
