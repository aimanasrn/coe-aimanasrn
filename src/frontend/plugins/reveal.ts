import type { Directive } from 'vue'

/**
 * v-reveal fades an element in when it scrolls into view. Pass a number to delay it in
 * milliseconds, e.g. v-reveal="150". Content stays visible without JavaScript and for
 * people who prefer reduced motion.
 */
export default defineNuxtPlugin((nuxtApp) => {
  let observer: IntersectionObserver | undefined

  const reveal: Directive<HTMLElement, number | undefined> = {
    getSSRProps: () => ({}),
    mounted(element, binding) {
      const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
      if (reducedMotion || !('IntersectionObserver' in window)) return

      observer ??= new IntersectionObserver(
        (entries) => {
          for (const entry of entries) {
            if (!entry.isIntersecting) continue
            entry.target.classList.add('is-visible')
            observer?.unobserve(entry.target)
          }
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
      )

      if (binding.value) element.style.setProperty('--reveal-delay', `${binding.value}ms`)
      element.classList.add('reveal')
      observer.observe(element)
    },
    unmounted(element) {
      observer?.unobserve(element)
    }
  }

  nuxtApp.vueApp.directive('reveal', reveal)
})

declare module 'vue' {
  interface GlobalDirectives {
    vReveal: Directive<HTMLElement, number | undefined>
  }
}
