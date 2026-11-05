export default defineAppConfig({
  ui: {
    colors: {
      primary: 'brand',
      secondary: 'amber',
      success: 'emerald',
      info: 'sky',
      warning: 'amber',
      error: 'red',
      neutral: 'sage'
    },
    button: {
      slots: { base: 'font-semibold' }
    },
    card: {
      slots: {
        root: 'rounded-xl',
        header: 'p-5 sm:px-6',
        body: 'p-5 sm:p-6',
        footer: 'p-5 sm:px-6'
      }
    },
    modal: {
      slots: { content: 'rounded-xl' }
    }
  }
})
