# SlideinAPI

## Get Slideins

#### Method: GET

#### Endpoint: https://website.com/wp-json/wookit/v1/slideins

## Params

| Param | Type | Description | Default |
| --- | --- | ----| --- |
| limit| int | Số lượng items / trang. Maximum: 30. Quá 30 sử dụng default | 10 |
| page | int | Page hiện tại | 1 |
| status | 'active' / 'deactive' / 'any' | Trường hợp all thì trả về cả slideins active và deactive| any |
| ?pluck | string | Mỗi pluck cách nhau bởi dấu phẩy. Ví dụ: title, id. Trường hợp không có pluck trả lai hết| undefined|

## Response

### Lỗi

```
export interface Response {
    message: "Message loi"
    code: Number
}
```

### Success

```
export interface Response {
    message: "Message Thanh cong"
    code: 200
    data: {
        items: [] // Items có thể rỗng nếu không có items nào.
        maxPages: Number
    }
}
```

## Get Slidein

### Method:GET

### API endpoint:

https://website.com/wp-json/wookit/v1/slideins/:id

##### parameters

<table>
<tr>
<th>Param</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>pluck</th>
<th>string</th>
<th></th>
<th>Xem Bên Dưới</th>
</tr>
<tr>
<th>shopName</th>
<th>string</th>
<th>Tên Shop</th>
<th>Bắn Tên Shopify Đã Đăng Ký Lên</th>
</tr>
</table>
Tham Số của Pluck

param | type | description
--- | --- | ---
title | string | Tên của slidein
date | string | ngày tạo bài
status | string | Trạng Thái Slideins: active/deactive
config | string | các config của font-end bắn lên
views | string | views của slidein
clicks | string | clicks của slidein
subscribers | string | subscribers của slidein
conversion | string | conversion của slidein
goal | string | goal của slidein

````ts
export interface Slidein {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
    /** status là trạng thái code sau xử lý api*/
    status: 'error' | 'success'
}

export interface Data {
    /** id là id của slidein*/
    id: string
    /** title Là tiêu đề của slidein*/
    title: string
    /** date Là thời gian tạo của slidein*/
    date: string
    /** config là các setting slidein của font-end*/
    config: datafontEnd
    /** status Là trạng thái của slidein*/
    status: (enable | disable)
    /** views Là số lượt xem của slidein*/
    views: number,
    /** clicks là số lượt clicks của slidein*/
    clicks: number,
    /** subscribers là số lượt gmail được gửi của slidein*/
    subscribers: number
    /** conversion Là đánh giá của slidein*/
    conversion: number
    /** goal là chiến dịch của slidein*/
    goal: string
    items: []
    /** các trang hiển thi slidein*/
    showToPage: string[] | []
}
````

## Get a specified slidein info

### Method:GET

### API endpoint:

https://website.com/wp-json/wookit/v1/slideins/:id/:param

##### parameters

<table>
<tr>
<th>Param</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>shopName</th>
<th>string</th>
<th>Tên Shop</th>
<th>Bắn Tên Shopify Đã Đăng Ký Lên</th>
</tr>
<tr>
<th>param</th>
<th>string</th>
<th></th>
<th>Xem Bên Dưới</th>
</tr>
</table>
Tham Số của Pluck

param | type | description
--- | --- | ---
title | string | Tên của slidein
date | string | ngày tạo bài
status | string | Trạng Thái Slidein active/deactive
config | string | các config của font-end bắn lên
views | string | views của slidein
clicks | string | clicks của slidein
subscribers | string | subscribers của slidein
conversion | string | conversion của slidein
goal | string | goal của slidein

````ts
export interface Slidein {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
    /** status là trạng thái code sau xử lý api*/
    status: 'error' | 'success'
}

export interface Data {
    /** id là id của slidein*/
    id: string
    /** title Là tiêu đề của slidein*/
    title: string
    /** date Là thời gian tạo của slidein*/
    date: string
    /** config là các setting slidein của font-end*/
    config: datafontEnd
    /** status Là trạng thái của slidein*/
    status: (active | deactive)
    /** views Là số lượt xem của slidein*/
    views: number,
    /** clicks là số lượt clicks của slidein*/
    clicks: number,
    /** subscribers là số lượt gmail được gửi của slidein*/
    subscribers: number
    /** conversion Là đánh giá của slidein*/
    conversion: number
    /** goal là chiến dịch của slidein*/
    goal: string
}
````

## 3.Create Slidein

### API endpoint:

https://website.com/wp-json/wookit/v1/slideins

##### parameters

<table>
<tr>
<th>Form-Data</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>shopName</th>
<th>string</th>
<th>Tên Shop</th>
<th>Bắn Tên Shopify Đã Đăng Ký Lên</th>
</tr>
<tr>
<th>config</th>
<th>json</th>
<th></th>
<th>Config Slidein Do Font-End Bắn Lên</th>
</tr>
<tr>
<th>title</th>
<th>string</th>
<th>Random</th>
<th>Title Của Slidein</th>
</tr>
 <tr>
<th>?status</th>
<th>string</th>
<th>active</th>
<th>Trạng thái Của Slidein</th>
</tr>
</table>

````ts
export interface Slidein {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
    /** status là trạng thái code sau xử lý api*/
    status: 'error' | 'success'
}
export interface Data {
    upgrade: Upgrade
    duplicate: Duplicate
    /** id là id của slidein vừa tạo*/
    id: string
    /**date là ngày tạo slidein */
    date: string
}

export interface Upgrade {
    isUpgrade: boolean
    message?: string
}

export interface Duplicate {
    isDuplicate: boolean
    message?: string
    ids?: string
}
````

## 4.Get All Slideins

### API endpoint:

https://website.com/wp-json/wookit/v1/slideins

##### parameters

<table>
<tr>
<th>Param</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>param</th>
<th>string</th>
<th></th>
<th>Xem Bên Dưới</th>
</tr>
<tr>
<th>shopName</th>
<th>string</th>
<th>Tên Shop</th>
<th>Bắn Tên Shopify Đã Đăng Ký Lên</th>
</tr>
<tr>
<th>?search</th>
<th>string</th>
<th></th>
<th>Tìm Kiếm</th>
</tr>
<tr>
<th>?limted</th>
<th>string</th>
<th></th>
<th>Giới Hạn Bao Nhiêu Slideins 1 Trang</th>
</tr>
<tr>
<th>?showToPage</th>
<th>string</th>
<th></th>
<th>Slideins hiển thị trên các trang nào có 7 giá trị:
template-index, template-blog,
template-article, template-list-collections,
template-collection, template-product, template-page
</th>
</tr>
</table>
Tham Số của Pluck

param | type | description
--- | --- | ---
title | string | Tên của slidein
date | string | ngày tạo bài
status | string | Trạng Thái Slidein active/deactive
config | string | các config của font-end bắn lên
views | string | views của slidein
clicks | string | clicks của slidein
subscribers | string | subscribers của slidein
conversion | string | conversion của slidein
goal | string | goal của slidein
showToPage | string | goal của slidein

````ts

interface Data {
    items: Items[];
    /** maxPages là số paged*/
    maxPages: number
}


export interface Slidein {
    data: Data
    /** messege là tin nhắn code trả lại*/
    message: string
    /** status trang thái code sau khi xử lý API*/
    status: 'error' | 'success'
}

export interface Data {
    /** id là id của slidein*/
    id: string
    /** title Là tiêu đề của slidein*/
    title: string
    /** date Là thời gian tạo của slidein*/
    date: string
    /** config là các setting slidein của font-end*/
    config: datafontEnd
    /** status Là trạng thái của slidein*/
    status: (enable | disable)
    /** views Là số lượt xem của slidein*/
    views: number,
    /** clicks là số lượt clicks của slidein*/
    clicks: number,
    /** subscribers là số lượt gmail được gửi của slidein*/
    subscribers: number
    /** conversion Là đánh giá của slidein*/
    conversion: number
    /** goal là chiến dịch của slidein*/
    goal: string
    /** các trang hiển thi slidein*/
    showToPage: string[] | []
}
````

## 5.Update,Patch Slidein

### API endpoint:

https://website.com/wp-json/wookit/v1/slideins/:id

##### parameters

<table>
<tr>
<th>x-wwww-form-urlencoded</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>shopName</th>
<th>string</th>
<th>Tên Shop</th>
<th>Bắn Tên Shopify Đã Đăng Ký Lên</th>
</tr>
<tr>
<th>?title</th>
<th>string</th>
<th>Random</th>
<th>title của slidein</th>
</tr>
<tr>
<th>?status</th>
<th>(active||deactive)</th>
<th>active</th>
<th>trạng thái của slidein</th>
</tr>
<tr>
<th>?config</th>
<th>json</th>
<th>-</th>
<th>config slidein do font-end bắn lên</th>
</tr>
</table>

````ts
export interface Slidein {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
    /** status là trạng thái code sau xử lý api*/
    status: 'error' | 'success'
}

export interface Data {
    upgrade: Upgrade
    duplicate: Duplicate
    /** id là id của slidein vừa tạo*/
    id: string
    /**date là ngày cập nhật slidein */
    date: string
}

export interface Upgrade {
    isUpgrade: boolean
    message?: string
}

export interface Duplicate {
    isDuplicate: boolean
    message?: string
    ids?: string
}
````

## 6.Delete Slidein:

### API endpoint:

https://website.com/wp-json/wookit/v1/slideins/id

##### parameters

<table>
<tr>
<th>Form-Data</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>shopName</th>
<th>string</th>
<th>Tên Shop</th>
<th>Bắn Tên Shopify Đã Đăng Ký Lên</th>
</tr>
</table>

````ts
export interface Slidein {
    /** id là id của slidein vừa tạo*/
    id: string
    /** messege là tin nhắn trả lại trên sever*/
    message: string
    /** status là trạng thái code sau xử lý api*/
    status: 'error' | 'success'
}
````

## 7.Many Delete Slidein:

### API endpoint:

https://website.com/wp-json/wookit/v1/slideins

##### parameters

<table>
<tr>
<th>Form-Data</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>shopName</th>
<th>string</th>
<th>Tên Shop</th>
<th>Bắn Tên Shopify Đã Đăng Ký Lên</th>
</tr>
<tr>
<th>ids</th>
<th>string</th>
<th></th>
<th>id của từng slidein ví dụ 1,2,3</th>
</tr>
</table>

````ts
export interface Slidein {
    /** id là id của slidein vừa tạo*/
    ids: string
    /** messege là tin nhắn trả lại trên sever*/
    message: string
    /** status là trạng thái code sau xử lý api*/
    status: 'error' | 'success'
}
````

## My Publishing Campaign

### Method: GET

### API endpoint:

https://website.com/vge/wookit/v1/me/slideins/publishing

### Params

```typescript
export interface PublishingSlidein {
    /** id là id của slidein vừa tạo*/
    showOnPage: 'all' | array
}
```

### Trả về

```typescript
export interface Item {
    title: string,
    id: string
}
```

```typescript
export interface Response {
    status: 'success',
    data?: Item
}
```

Dùng API [updateSlidein](#5updatepatch-slidein) để disable.

## Update force Active Slidein

### Method: PUT

### API endpoint:

https://website.com/vge/wookit/v1/me/slideins/:id/focus-active

### Params

id của slidein được active


### Trả về

```typescript
export interface Slidein {
    data: Data
    /** messege là tin nhắn trả lại trên sever*/
    message: string
    /** status là trạng thái code sau xử lý api*/
    status: 'error' | 'success'
}

export interface Data {
    /** id là id của list slidein vừa update*/
    id: string,
    deactivateIDs?: string[]
}
```
