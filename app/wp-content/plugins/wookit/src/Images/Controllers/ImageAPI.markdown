# Upload API

## 1.Upload IMG

### Endpoint:

https://website.com/wp-json/wookit/v1/images

#### Method:POST

### Headers

key | type | value
--- | --- | ---
Authorization | string | Basic Auth

##### Body

content | source | value
--- | --- | ---
'base64' / 'url' / 'form_file' | 'base64' / 'self_hosted' / 'file'

````ts
export interface UploadImage {
    item: Item
    msg: string
    status: 'error' | 'success'
}

export interface Item {
    id: number
    url: string
    thumbnails: Thumbnails;
    msg: string
    status: 'error' | 'success'
}

export interface Thumbnails {
    '5x5': ThumbnailMethodGet & { width: number; height: number };
    medium: ThumbnailMethodGet & { width: number; height: number };
    large: ThumbnailMethodGet & { width: number; height: number };
    thumbnail: ThumbnailMethodGet & { width: number; height: number };
    full: ThumbnailMethodGet & { width: number; height: number };
}
````

## 2.Get Images

### Endpoint:

https://website.com/wp-json/myshopkit/v1/images

#### Method:

GET

### Headers

key | type | value
--- | --- | ---
Authorization | string | Basic Auth

##### parameters

key | type | value
--- | --- | ---
post_mime_type | string | Image type: image/jpeg,image/jpg,image/png
order | string | 'desc'/'asc'
posts_per_page | number | Default: 20
orderby | string |  Default: id
paged | string |  Default: 1

````ts
export interface GetImages {
    items: Item[]
    msg: string
    status: 'error' | 'success'
    paged: number
}

export interface Item {
    id: number;
    label: string;
    url: string;
    thumbnails: Thumbnails;
}

export interface Thumbnails {
    '5x5': ThumbnailMethodGet & { width: number; height: number };
    medium: ThumbnailMethodGet & { width: number; height: number };
    large: ThumbnailMethodGet & { width: number; height: number };
    thumbnail: ThumbnailMethodGet & { width: number; height: number };
    full: ThumbnailMethodGet & { width: number; height: number };
}


````

## 3.Get Image

### Endpoint:

https://website.com/wp-json/myshopkit/v1/images/id

#### Method:
GET

### Header

key | type | value
--- | --- | ---
Authorization | string | Basic Auth

````ts
export interface GetImage {
    items: Item[]
    msg: string
    status: 'error' | 'success'
}

export interface Item {
    id: number
    label: string
    url: string
    width: number
    height: number
    thumbnails: Thumbnail[]
}

export interface Thumbnail {
    id: number
    url: string
    width: number
    height: number
}
````

## 4.Get My Images

### Endpoint
https://website.com/wp-json/myshopkit/v1/me/images

#### Method:
GET

### Headers

key | type | value
--- | --- | ---
Authorization | string | Basic Auth

##### parameters

key | type | value
--- | --- | ---
post_mime_type | string | image/jpeg,image/jpg,image/png
order | string | Default: desc
posts_per_page | number | Default: 20
orderby | string |  Default: id
paged | string |  Default: 1

````ts
export interface GetImage {
    items: Item[]
    msg: string
    status: 'error' | 'success'
}

export interface Item {
    id: number
    label: string
    url: string
    width: number
    height: number
    thumbnails: Thumbnail[]
}

export interface Thumbnail {
    id: number
    url: string
    width: number
    height: number
}
````
