export function formatMoney(value: string | number) {
  return (
    'RM ' +
    Number(value).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
  )
}
