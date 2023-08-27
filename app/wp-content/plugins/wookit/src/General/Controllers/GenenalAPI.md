# Genenal API

## 1.Get Campaigns Status

### Method:GET

### headers

key | type | value
--- | --- | ---
Authorization | string | code xác thực

### Method:GET

### API endpoint:

https://website.com/wp-json/wookit/v1/campaigns/status

````ts
export interface campaigns {
    data: Data
    message: string
    status: (success | error)
}

export interface Data {
    "hasPopup": boolean,
    "hasSmartbar": boolean
}

````

## 2.Get Shop Info

### Method:GET

### headers

key | type | value
--- | --- | ---
Authorization | string | Basic Auth

### Method:GET

### API endpoint:

https://website.com/wp-json/wookit/v1/shop-info

````ts
export interface ShopInfo {
    data: Data
    message: string
    status: (success | error)
}

export interface Data {
    locale: string,
    currency: string,
    site: string,
}

````
