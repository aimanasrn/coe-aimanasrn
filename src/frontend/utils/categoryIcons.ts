const icons: Record<string, string> = {
  'Writing Instruments': 'i-lucide-pen-line',
  'Notebooks & Paper': 'i-lucide-notebook-pen',
  'Erasers & Sharpeners': 'i-lucide-eraser',
  'Correction Supplies': 'i-lucide-undo-2',
  'Geometry & Measuring Tools': 'i-lucide-ruler',
  'Files & Folders': 'i-lucide-folder-open',
  'Office Accessories': 'i-lucide-paperclip',
  'Cutting & Adhesives': 'i-lucide-scissors',
  'Art & Colouring Supplies': 'i-lucide-palette',
  'Storage & Organisers': 'i-lucide-archive',
  Books: 'i-lucide-book-open'
}

export function categoryIcon(name: string) {
  return icons[name] ?? 'i-lucide-tag'
}
