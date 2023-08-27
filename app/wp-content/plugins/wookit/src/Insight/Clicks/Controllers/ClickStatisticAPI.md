# Clicks API

## 1.Get Clicks

### Method:GET

### API endpoint:

https://website.com/wp-json/wookit/v1/insights/clicks

##### Parameters

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
<th>Default: today</th>
<th>Listed below</th>
</tr>
<tr>
<th>?postType</th>
<th>string</th>
<th>(popup | smartbar)</th>
<th>Listed below</th>
</tr>
</table>
Filter`s Params

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
export interface Clicks {
    data: Data
    message: string
}

export interface Data {
    type: 'click'
    summary: number
    timeline: Timeline[]
}

export interface Timeline {
    summary: number
    from: srting // Timestamp
    to: srting // Timestamp
}
````

## 2.Get Click With PopupID

### Method:GET

### API endpoint:

https://website.com/wp-json/wookit/v1/insights/clicks/:id

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
<th>Default: today</th>
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
export interface Timeline {
    summary: number
    from: srting // timestamp
    to: srting // timestamp
}

export interface clicks {
    data: Data
    message: string
}

export interface Data {
    type: 'click'
    summary: number
    timeline: Timeline[]
}
````

## 3.Update Click

### Method:PUT

### API endpoint:

https://website.com/wp-json/wookit/v1/insights/clicks/:id

##### parameters

<table>
<tr>
<th>Param</th>
<th>Type</th>
<th>Data Default</th>
<th>Description</th>
</tr>
<tr>
<th>?postType</th>
<th>string</th>
<th>(popup | smartbar)</th>
<th></th>
</tr>
</table>

````ts
export interface Clicks {
    data: Data
    message: string
    status: 'success' | 'error'
}

export interface Data {
    id: string
    type: 'click'
    summary: number
    timeline: []
}
````
