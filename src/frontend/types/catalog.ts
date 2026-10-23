export interface Category {
  id: number
  name: string
  description?: string | null
  products_count?: number
}
export interface CategoryResponse {
  data: Category
  message?: string
}
export type CategoryDialogMode = 'add' | 'view' | 'edit' | 'delete'
export type CategoryProductFilter = 'all' | 'with' | 'empty'
export interface CategorySort {
  column: 'name' | 'products_count'
  direction: 'asc' | 'desc'
}
export interface CategoryPage {
  data: Category[]
  total: number
  current_page: number
  per_page: number
  last_page: number
}
export interface Product {
  id: number
  name: string
  sku: string
  price: string
  stock_quantity: number
  description?: string | null
  category: Category | null
  brand?: string | null
  barcode?: string | null
  unit?: 'item' | 'pack' | 'set' | 'box' | 'download'
  image_urls: string[]
  created_at?: string
  updated_at?: string
}
export interface ProductResponse {
  data: Product
  message?: string
}
export interface ProductImageEntry {
  key: string
  url: string
  existingIndex?: number
  file?: File
}
export type StockFilter = 'all' | 'low' | 'out'
export type ProductSortColumn = 'id' | 'name' | 'price' | 'stock_quantity'
export interface ProductSort {
  column: ProductSortColumn
  direction: 'asc' | 'desc'
}
export interface DashboardData {
  summary: {
    total_products: number
    total_categories: number
    stock_units: number
    stock_value: string
  }
  stock_counts: { in_stock: number; low_stock: number; out_of_stock: number }
  low_stock_threshold: number
  categories: Category[]
  products: Product[]
  filtered_total: number
}
export interface ProductPage {
  data: Product[]
  total: number
  current_page: number
  per_page: number
  last_page: number
  low_stock_threshold: number
}
