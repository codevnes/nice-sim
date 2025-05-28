# Nice SIM - Plugin WordPress Phân Tích Số Điện Thoại

Plugin WordPress để kiểm tra số điện thoại dựa trên nguyên lý phong thủy và tứ trụ mệnh.

## Tính năng

- Shortcode hiển thị form tra cứu SIM
- Gửi form bằng AJAX để hiển thị kết quả nhanh không cần tải lại trang
- Thiết kế responsive tương thích với điện thoại và máy tính
- Phân tích chi tiết số điện thoại dựa trên ngày sinh, giới tính và giờ sinh
- Bảo vệ plugin bằng hệ thống giấy phép
- Xác thực giấy phép thông qua API

## Cài đặt

1. Tải thư mục `nice-sim` vào thư mục `/wp-content/plugins/` 
2. Kích hoạt plugin qua menu 'Plugins' trong WordPress
3. Nhập mã giấy phép, App ID và API Key trong phần Cài đặt > Giấy phép Nice SIM
4. Sử dụng shortcode `[nice_sim_search]` trong trang hoặc bài viết để hiển thị form tìm kiếm SIM

## Giấy phép

Plugin này yêu cầu mã giấy phép hợp lệ để hoạt động. Để kích hoạt giấy phép:

1. Vào Cài đặt > Giấy phép Nice SIM trong trang quản trị WordPress
2. Nhập mã giấy phép của bạn (định dạng: ABCD-EFGH-IJKL-MNOP)
3. Nhập App ID và API Key được cấp cùng với giấy phép
4. Lưu thay đổi

Nếu không có mã giấy phép hợp lệ, plugin sẽ hiển thị thông báo thay vì form tìm kiếm.

### Xác thực giấy phép qua API

Plugin sử dụng API endpoint sau để xác thực giấy phép:

```
GET http://sonona.io/wp-json/acm/v1/validate?code=YOUR_CODE&domain=example.com&app_id=YOUR_APP_ID&api_key=YOUR_API_KEY
```

Tên miền hiện tại được tự động phát hiện và gửi kèm yêu cầu xác thực.

## Sử dụng

1. Thêm shortcode `[nice_sim_search]` vào trang hoặc bài viết nơi bạn muốn hiển thị form tìm kiếm SIM
2. Người dùng sẽ điền thông tin gồm số điện thoại, ngày sinh, giới tính và giờ sinh
3. Sau khi gửi, kết quả phân tích sẽ hiển thị phía dưới form

## Tùy chỉnh

### Giao diện

Plugin đã bao gồm CSS riêng, nhưng bạn có thể tùy chỉnh giao diện bằng cách thêm CSS tùy chỉnh vào theme.

### Shortcode

Plugin cung cấp shortcode sau:

- `[nice_sim_search]`: Hiển thị form tìm kiếm SIM và khu vực kết quả

## Tích hợp API

Plugin kết nối với API bên ngoài để phân tích số điện thoại. Điểm cuối API được cấu hình trong lớp chính của plugin.

## Tính năng bảo mật

- Hệ thống xác thực giấy phép qua API
- Lưu cache giấy phép để giảm thiểu yêu cầu API
- Kiểm tra tính toàn vẹn file để phát hiện sự can thiệp
- Xác thực môi trường và kiểm tra bảo mật
- Tự động vô hiệu hóa plugin nếu phát hiện vấn đề bảo mật

## Chế độ phát triển

Để phục vụ mục đích phát triển, bạn có thể bật chế độ phát triển bằng cách bỏ comment dòng này trong file chính của plugin:

```php
// define('NICE_SIM_DEV_MODE', true);
```

Điều này sẽ bỏ qua kiểm tra tính toàn vẹn file trong quá trình phát triển.

## Yêu cầu

- WordPress 5.0 trở lên
- PHP 7.0 trở lên
- Mã giấy phép, App ID và API Key hợp lệ

## Hỗ trợ

Để được hỗ trợ, vui lòng liên hệ tác giả plugin tại [author@example.com](mailto:author@example.com).

## Giấy phép phần mềm

GPL v2 trở lên 