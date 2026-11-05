export interface LandingCategory {
  id: number
  name: string
  description: string | null
  products_count: number
}

export interface LandingProduct {
  id: number
  name: string
  price: string
  category: string | null
  image_url: string | null
}

export interface LandingData {
  stats: { products: number; categories: number; in_stock: number }
  categories: LandingCategory[]
  featured: LandingProduct[]
}
