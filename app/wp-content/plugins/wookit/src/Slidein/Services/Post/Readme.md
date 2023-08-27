# Mô tả thư mục Post

**Các class đại diện cho các chức năng. Ví dụ: CreatePostService đại diên cho chức năng tạo post mới**

**Tất cả các class theo follow IService Interface đặt dưới thư mục Shared -> Post**

**Trường hợp là Update thì cần set post id nên cần implement thêm IDeleteUpdateService dưới thư mục Shared -> Post**

## IService có các chức năng sau

* defineFields: Định nghĩa các fields
* setRawData: Dữ liệu thô ban đầu cần xử lý
* validateFields: Phân tích dữ liệu thô dựa vào config ở defineFields
* performSaveData: Tiến hành lưu dữ liệu

Trong quá trình validateFields, nếu có **1 lỗi**, sẽ bị trả về với status error ngay lập tức (MessageFactory)

## IDeleteUpdateService

Đây là Interface cho update / delete. Ví dụ ID cần phải có khi update delete thì cần mở rộng Interface này.

### defineFields có cấu trúc như sau

```php
$this->aFields = [
    'id'     => [
        'key'              => 'ID',
        'sanitizeCallback' => 'abs',
        'value'            => 0,
        'assert'            => ['isArray']
    ]
];
```

1. id: Là friendly key, được Frontend sử dụng. Lưu ý, id có thể có tên khác như title
2. key: Key thật được sử dụng. Ví dụ post_title
3. sanitizeCallback: Không bắt buộc. Function gọi về để làm sạch dữ liệu
4. value: Giá trị default
5. assert: Kiểm tra dữ liệu. Có các hàm được mô tả tại Shared -> Assert.php


