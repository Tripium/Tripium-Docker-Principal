# Views API

## 1.Get Views

### Method:GET

###  Endpoint:

https://website.com/wp-json/wookit/v1/insights/views

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
<th>Listed below</th>
</tr>
<tr>
<th>?postType</th>
<th>string</th>
<th>(popup | smartbar)</th>
<th></th>
</tr>
</table>
Filter's Params

param | type
--- | --- | 
thisWeek | string 
thisMonth | string
lastWeek | string
lastMonth | string
today | string
yesterday | string
custom | string 

Custom Date Format. Format: (Y-m-d)

param | type | description
--- | --- | ---
from | string | Start date
to | string | End Date

````ts
export interface Views {
    data: Data
    message: string
}

export interface Data {
    type: 'view'
    summary: number
    timeline: Timeline[]
}

export interface Timeline {
    summary: number
    /** from: timestamp*/
    from: srting
    /** to: timestamp*/
    to: srting
}
````

## 2.Get Views With PopupID

### Method:GET

### API endpoint:

https://website.com/wp-json/wookit/v1/insights/views/:id

##### parameters

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
<th></th>
</tr>
</table>

Filter's Params

param | type
--- | --- 
thisWeek | string
thisMonth | string
lastWeek | string 
lastMonth | string
today | string
yesterday | string 
custom | string 

Custom Date Range. Format (Y-m-d)

param | type
--- | --- 
from | string
to | string

````ts
export interface Timeline {
    summary: number
    /** from: timestamp*/
    from: srting
    /** to: timestamp*/
    to: srting
}

export interface View {
    data: Data
    message: string
}

export interface Data {
    type: 'view'
    summary: number
    timeline: Timeline[]
}
````

## 3.Update View

### Method:PUT

### API endpoint:

https://website.com/wp-json/wookit/v1/insights/views/:id

##### parameters

<table>
<tr>
<th>Param</th>
<th>Type</th>
<th>Data Default</th>
</tr>
<tr>
<th>?postType</th>
<th>string</th>
<th>(popup | smartbar)</th>
</tr>
</table>

````ts
export interface View {
    data: Data
    message: string
    status: 'success' | 'error'
}

export interface Data {
    id: string
    type: 'view'
    summary: number
    timeline: []
}
````
