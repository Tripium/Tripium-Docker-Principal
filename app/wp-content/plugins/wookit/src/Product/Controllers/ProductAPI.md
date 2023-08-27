# Product API

## 1.Get All Products

### Method:GET

### headers

key | type | value
--- | --- | ---
Authorization | string | Basic Auth

### params

key | type | value
--- | --- | ---
limit | string | Default: 12
s | string | 
page | string | Default: 1

### Method:GET

### Endpoint:

https://website.com/wp-json/wookit/v1/products

````ts
export interface Product {
    data: Data
    message: string
    status: (success | error)
}

export interface Data {
    items: Item[],
    maxPage: number
}

export interface Item {
    id: number
    title: string
    link: string
    image: Image
    price: string[]
}

export interface Image {
    src: string
    width: number
    height: number
}
````
