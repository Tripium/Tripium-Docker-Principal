# Coupon Code

## Endpoint
https://website.com/vge/myshopkit/v1/coupons/giveaway

## Params

| Param | Type | Description | Required |
| --- | --- | ----| --- |
| id| string | Smartbar / Popup ID | yes|
| couponID | string | The coupon id | no|


### Error Response

```
export interface Response {
    message: string
    code: Number
}
```

### Success Response

```
export interface Response {
    message: string
    code: 200
    data: {
       couponCode: string
    }
}
```
