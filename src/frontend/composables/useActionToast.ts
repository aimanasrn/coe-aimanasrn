type ActionToastType = 'success' | 'error' | 'warning'

const icons: Record<ActionToastType, string> = {
  success: 'i-lucide-circle-check',
  error: 'i-lucide-circle-x',
  warning: 'i-lucide-triangle-alert'
}

const tones: Record<ActionToastType, string> = {
  success:
    '[--toast-accent:#6ee7b7] [--toast-tint:rgb(16_185_129/0.16)] [--toast-border:rgb(52_211_153/0.4)]',
  error:
    '[--toast-accent:#fda4af] [--toast-tint:rgb(244_63_94/0.16)] [--toast-border:rgb(251_113_133/0.4)]',
  warning:
    '[--toast-accent:#fcd34d] [--toast-tint:rgb(245_158_11/0.16)] [--toast-border:rgb(251_191_36/0.4)]'
}

export function useActionToast() {
  const toast = useToast()

  function show(type: ActionToastType, title: string, description: string) {
    return toast.add({
      title,
      description,
      color: type,
      icon: icons[type],
      duration: 3000,
      close: true,
      progress: {
        ui: {
          base: 'bg-white/8',
          indicator: 'bg-(--toast-accent) transition-none'
        }
      },
      ui: {
        root: [
          'relative gap-3.5 rounded-xl border border-(--toast-border) bg-[#020617]/90 px-4 pt-[18px] pb-[22px] ring-0',
          'shadow-[0_12px_40px_rgb(2_6_23/0.24)] backdrop-blur-[16px]',
          'before:pointer-events-none before:absolute before:top-[18px] before:left-4 before:size-9 before:rounded-[10px]',
          'before:border before:border-(--toast-border) before:bg-(--toast-tint)',
          'data-[state=open]:animate-toast-enter data-[state=closed]:animate-toast-leave',
          'motion-reduce:animate-none! motion-reduce:transition-none!',
          tones[type]
        ].join(' '),
        icon: 'size-9 text-(--toast-accent) bg-current [mask-size:20px_20px] [mask-position:center] [mask-repeat:no-repeat]',
        title: 'text-(--toast-accent) font-[650] leading-5 wrap-anywhere',
        description: 'text-[#cbd5e1] text-xs leading-[19px] wrap-anywhere',
        close:
          'text-[#94a3b8] cursor-pointer hover:text-white focus-visible:outline-2 focus-visible:outline-(--toast-accent) focus-visible:outline-offset-[3px] focus-visible:rounded'
      }
    })
  }

  return {
    success: (title: string, description: string) => show('success', title, description),
    error: (title: string, description: string) => show('error', title, description),
    warning: (title: string, description: string) => show('warning', title, description)
  }
}
