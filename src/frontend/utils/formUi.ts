// Complete Tailwind class names keep Nuxt UI controls consistent and discoverable by Tailwind.
const control = [
  'w-full min-h-12.5 rounded-lg border border-[#dce3df] bg-white px-3.5 py-3.25',
  'text-base leading-[22px] text-heading shadow-none ring-0',
  'placeholder:text-[#929a96] aria-invalid:border-[#c55749]',
  'focus-visible:border-primary focus-visible:outline-none',
  'focus-visible:ring-[3px] focus-visible:ring-primary/10',
  'max-md:min-h-12'
].join(' ')

export const catalogFormUi = {
  field: {
    root: 'flex flex-col gap-2',
    label:
      'text-sm leading-normal font-medium text-[#303d37] [&_span]:font-normal [&_span]:text-muted'
  },
  input: { base: control },
  select: { base: control },
  textarea: { base: [control, 'min-h-32 resize-y'].join(' ') }
}
