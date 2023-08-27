# Query Interface

## Các bước thực hiện query

1. definePostType
2. setRawArgs
3. parseArgs
4. query

Trong trường hợp không muốn query, chỉ muốn lấy dữ liệu parseArgs, ta dùng
**getArgs**

```
$aResponse = (new Query())->setRawArgs($aRawArgs)->parseArgs()->query(new PostSkeleton);
```
