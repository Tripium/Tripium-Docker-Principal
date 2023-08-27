# Page API

## 1.Get All Pages

### Method:GET

### headers
key | type | value
--- | --- | ---
Authorization | string | Basic Auth

### params
key | type | value
--- | --- | ---
limit | string | Default: 10

### Method:GET

### Endpoint:

https://website.com/wp-json/wookit/v1/pages

````ts
export interface Page {
    data: Data
    message: string
    status: (success | error)
}

export interface Data {
    items: Item[]
}

export interface Item {
    id: number
    title: string,
    handle: string,
    body_html: string
    author: string
    created_at: string
    updated_at: string
    link: string
}
````

## 2.Get one Page

### Method:GET

### headers
key | type | value
--- | --- | ---
Authorization | string | Basic Auth

### Method:GET

### Endpoint:

https://website.com/wp-json/wookit/v1/pages/:id

````ts
export interface Page {
    data: Data
    message: string
    status: (success | error)
}

export interface Data {
    item: Item
}

export interface Item {
    id: number
    title: string
    body_html: string
    handle: string,
    author: string
    created_at: string
    updated_at: string
    link: string
}
````
