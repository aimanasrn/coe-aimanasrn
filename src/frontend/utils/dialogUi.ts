export const catalogDialogUi = {
  content: [
    'w-[calc(100vw_-_32px)] max-w-140 rounded-2xl border border-border bg-white text-body font-sans',
    '[&_:is(button,_a):focus-visible]:outline-2 [&_:is(button,_a):focus-visible]:outline-solid',
    '[&_:is(button,_a):focus-visible]:outline-primary',
    '[&_:is(button,_a):focus-visible]:outline-offset-3',
    '[&_button:disabled]:opacity-50 [&_button:disabled]:cursor-not-allowed',
    'motion-reduce:[&_*]:scroll-auto motion-reduce:[&_*]:duration-0'
  ].join(' '),
  overlay: 'bg-[#10251c]/35 backdrop-blur-[2px]',
  title: 'text-heading text-[24px] font-semibold tracking-[-0.6px]'
}
