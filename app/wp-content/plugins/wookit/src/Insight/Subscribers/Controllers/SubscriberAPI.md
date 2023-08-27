# Subscriber API

## 1.Get Insights Subscribers

### Method:GET

### Endpoint:

https://website.com/wp-json/wookit/v1/insights/subscribers

##### Params

<table>
<tr>
<th>Param</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>?filter</th>
<th>string</th>
<th>today</th>
<th>Listed Below</th>
</tr>
<tr>
<th>?postType</th>
<th>string</th>
<th>(popup | smartbar)</th>
<th>Dạng post type</th>
</tr>
<tr>
<th>Authorization</th>
<th>string</th>
<th></th>
<th>Basic Auth</th>
</tr>
</table>
Filter's Params

param | type | description
--- | --- | ---
thisWeek | string | Total of clicks this week
thisMonth | string | Total of clicks this month
lastWeek | string | Total of clicks previous week
lastMonth | string | Total of clicks previous month
today | string | Total of clicks today
yesterday | string | Total of clicks yesterday
custom | string | Total of clicks by custom date range

Custom Date Format (formatDate (Y-m-d))

param | type | description
--- | --- | ---
from | string | Start Date
to | string | End Date

````ts
export interface Subscribers {
    data: Data
    message: string
}

export interface Data {
    type: 'subscriber'
    summary: number
    timeline: Timeline[]
}

export interface Timeline {
    summary: number
    from: srting
    to: srting
}
````

## 2.Get Subscribers

### Method:GET

### Endpoint:

https://website.com/wp-json/myshopkit/v1/subscribers

##### Params

param | Type | Data Default |Description
--- | --- | --- | -----|
?limit | number | 0 | How many items / page
?page | number | 1 | Current Page
Authorization | string |  | Basic Auth

````ts
export interface Data {
    items: Items[];
    maxPages: number
}

export interface Items {
    ID: string;
    email: string;
    /** createdDate: Timestamp*/
    createdDate: string
    campaign: string
}

export interface Subcribers {
    data: Data
    message: string
    status: 'error' | 'success'
}
````

## 3.Get Subscribers With ID Campaign

### Method:GET

### Endpoint:

https://website.com/wp-json/myshopkit/v1/subscribers/:id

##### Params

param | Type | Data Default |Description
--- | --- | --- | -----|
?limit | number | 0 | How many items / page
?page | number | 1 | Current Page
Authorization | string |  | Basic Auth

````ts
export interface Data {
    items: Items[];
    maxPages: number
}

export interface Items {
    ID: string;
    email: string;
    /** createdDate: timestamp*/
    createdDate: string
    campaign: string
}

export interface Subcribers {
    data: Data
    message: string
    status: 'error' | 'success'
}
````

## 4.Create Subscriber

### Method:POST

### Endpoint

https://website.com/wp-json/myshopkit/v1/subscribers/:id

##### Params

param | Type | Data Default |Description
--- | --- | --- | -----|
email | string | - | Customer email
Authorization | string |  | Basic Auth
postType | string | (popup | smartbar) 
?fullName | string | '' | Customer Name
?gdpr | int | 1 | Agree to GDPR
locale | string | en | Shop Language

````ts
export interface Subscriber {
    data: Data
    message: string
    status: 'success' | 'error'
}

export interface Data {
    id: string
}
````

## 5.DELETE Subscriber

### Method:DELETE

### Endpoint:

https://website.com/wp-json/myshopkit/v1/subscribers/:id


##### Params

param | Type | Data Default |Description
--- | --- | --- | -----|
email | string | - |Customer Email
Authorization | string |  | Basic Auth

id: It's Campaign ID

````ts
export interface Subscriber {
    data: Data
    message: string
    status: 'success' | 'error'
}

export interface Data {
    id: string
}
````
## 6. DELETE Subscribers

### Method:DELETE

### Endpoint:

https://website.com/wp-json/myshopkit/v1/subscribers

##### Params

param | Type | Data Default |Description
--- | --- | --- | -----|
emails | string | - | Customers email
ids | string | - | List of campaign id
Authorization | string |  | Basic Auth


````ts
export interface Subscriber {
    data: Data
    message: string
    status: 'success' | 'error'
}

export interface Data {
    id: string
}
````
## 7.Export Subscribers

### Method:GET

### Endpoint:

https://website.com/wp-json/myshopkit/v1/subscribers/export

##### Params

param | Type | Data Default |Description
--- | --- | --- | -----|
?limit | number | 0 | Items / Page
?page | number | 1 | Current Page
?filter | string | custom | If this feature is empty => get all
?from | string | - | Start Date (Y-m-d)
?to | string | - | End Date (Y-m-d)
format | string | (d-m-Y) | Expected Date Format when returning
Authorization | string |  | Basic Auth

Date Format Allowable

param | type | description
--- | --- | ---
Y-M-d | string |  2021-Jun-16
d-M-Y | string |  16-Jun-2021
d-m-y | string |  16-06-2021

