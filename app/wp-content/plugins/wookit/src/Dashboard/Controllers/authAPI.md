# Auth API

## Check the Application Password

### Method: Post

### Body Format

key | type | value
--- | --- | ---
username | string | Username
appPassword | string | The password of this app

### API endpoint:

https://website.com/wp-json/wookit/v1/auth

````ts
export interface Auth {
    data: Data
    message: string
    status: (success | error)
}

export interface Data {
    hasPassed: boolean
}
````
