import type { Product } from '~/types/catalog'
export type ShopProduct = Omit<Product, 'sku' | 'barcode'>
export interface ShopPage {
  data: ShopProduct[]
  total: number
  current_page: number
  last_page: number
  per_page: number
}
export interface CartItem {
  product: ShopProduct
  quantity: number
  max_quantity: number
  available: boolean
  line_total: string
}
export interface Cart {
  items: CartItem[]
  item_count: number
  subtotal: string
  currency: 'MYR'
}
