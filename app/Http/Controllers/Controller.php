<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Tạo response API chuẩn hóa
     *
     * Đây là phương thức cơ bản nhất để tạo một response API với format chuẩn,
     * bao gồm status, message, data và errors (nếu có).
     * Sử dụng phương thức này để đảm bảo tất cả API response đều theo một format nhất định.
     *
     * @param mixed $data Dữ liệu trả về cho client
     * @param string $message Thông điệp mô tả kết quả
     * @param string $status Trạng thái của response ('success' hoặc 'error')
     * @param int $code HTTP status code (200, 201, 400, 401, 403, 404, 422,...)
     * @param mixed $errors Chi tiết lỗi (thường là mảng các lỗi validation)
     * @param array $headers HTTP headers bổ sung (nếu cần)
     * @return JsonResponse
     */
    protected function apiResponse($data = null, string $message = '', string $status = 'success', int $code = 200, $errors = null, array $headers = []): JsonResponse
    {
        // Tạo cấu trúc response chuẩn
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        // Thêm data vào response nếu có
        if (!is_null($data)) {
            // Kiểm tra xem data có phải là một paginator không
            if ($data instanceof LengthAwarePaginator) {
                // Nếu là paginator thì thêm metadata phân trang
                $response['data'] = $data->items();
                $response['meta'] = [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total()
                ];
                $response['links'] = [
                    'first' => $data->url(1),
                    'last' => $data->url($data->lastPage()),
                    'prev' => $data->previousPageUrl(),
                    'next' => $data->nextPageUrl()
                ];
            } else {
                // Nếu không phải paginator thì thêm data trực tiếp
                $response['data'] = $data;
            }
        }

        // Thêm errors vào response nếu có
        if (!is_null($errors)) {
            $response['errors'] = $errors;
        }

        // Trả về response JSON với code và headers
        return response()->json($response, $code, $headers);
    }

    /**
     * Trả về response thành công
     *
     * Sử dụng phương thức này để trả về kết quả thành công với dữ liệu kèm theo.
     * Phổ biến cho các API GET, LIST, SHOW, UPDATE.
     *
     * @param mixed $data Dữ liệu trả về
     * @param string $message Thông điệp thành công
     * @param int $code HTTP status code (mặc định là 200 OK)
     * @param array $headers HTTP headers bổ sung
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Thao tác thành công', int $code = 200, array $headers = []): JsonResponse
    {
        return $this->apiResponse($data, $message, 'success', $code, null, $headers);
    }

    /**
     * Trả về response lỗi
     *
     * Sử dụng phương thức này khi có lỗi xảy ra và cần thông báo cho client.
     * Có thể đi kèm với chi tiết lỗi cụ thể.
     *
     * @param string $message Thông điệp lỗi
     * @param int $code HTTP status code (mặc định là 400 Bad Request)
     * @param mixed $errors Chi tiết các lỗi (nếu có)
     * @param array $headers HTTP headers bổ sung
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Đã xảy ra lỗi', int $code = 400, $errors = null, array $headers = []): JsonResponse
    {
        return $this->apiResponse(null, $message, 'error', $code, $errors, $headers);
    }

    /**
     * Trả về response lỗi validation
     *
     * Sử dụng khi có lỗi validation từ dữ liệu người dùng gửi lên.
     * Thường sử dụng với các API tạo mới hoặc cập nhật dữ liệu.
     *
     * @param mixed $errors Chi tiết các lỗi validation
     * @param string $message Thông điệp lỗi chung
     * @param array $headers HTTP headers bổ sung
     * @return JsonResponse
     */
    protected function validationErrorResponse($errors, string $message = 'Dữ liệu không hợp lệ', array $headers = []): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors, $headers);
    }

    /**
     * Trả về response không tìm thấy tài nguyên
     *
     * Sử dụng khi không tìm thấy tài nguyên được yêu cầu.
     * Ví dụ: GET /users/123 mà user 123 không tồn tại.
     *
     * @param string $message Thông điệp lỗi
     * @param array $headers HTTP headers bổ sung
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Không tìm thấy tài nguyên yêu cầu', array $headers = []): JsonResponse
    {
        return $this->errorResponse($message, 404, null, $headers);
    }

    /**
     * Trả về response tạo tài nguyên thành công
     *
     * Sử dụng khi tạo mới tài nguyên thành công.
     * Tuân theo chuẩn REST, trả về status code 201 Created.
     *
     * @param mixed $data Dữ liệu tài nguyên đã tạo
     * @param string $message Thông điệp thành công
     * @param array $headers HTTP headers bổ sung
     * @return JsonResponse
     */
    protected function createdResponse($data = null, string $message = 'Tạo mới tài nguyên thành công', array $headers = []): JsonResponse
    {
        return $this->successResponse($data, $message, 201, $headers);
    }

    /**
     * Trả về response không có nội dung
     *
     * Sử dụng khi thao tác thành công nhưng không cần trả về dữ liệu.
     * Thường dùng cho các API xóa tài nguyên.
     *
     * @param string $message Thông điệp (nếu cần)
     * @param array $headers HTTP headers bổ sung
     * @return JsonResponse
     */
    protected function noContentResponse(string $message = '', array $headers = []): JsonResponse
    {
        return $this->apiResponse(null, $message, 'success', 204, null, $headers);
    }

    /**
     * Trả về response không có quyền truy cập
     *
     * Sử dụng khi người dùng đã đăng nhập nhưng không có quyền thực hiện hành động.
     * Ví dụ: Người dùng thông thường cố gắng truy cập tài nguyên của admin.
     *
     * @param string $message Thông báo lỗi
     * @param array $headers HTTP headers bổ sung
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Bạn không có quyền truy cập', array $headers = []): JsonResponse
    {
        return $this->errorResponse($message, 403, null, $headers);
    }

    /**
     * Trả về response chưa xác thực
     *
     * Sử dụng khi người dùng chưa đăng nhập hoặc token không hợp lệ.
     * Thường dùng cho các API yêu cầu xác thực.
     *
     * @param string $message Thông báo lỗi
     * @param array $headers HTTP headers bổ sung
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Bạn chưa đăng nhập hoặc phiên làm việc đã hết hạn', array $headers = []): JsonResponse
    {
        return $this->errorResponse($message, 401, null, $headers);
    }
    
    /**
     * Trả về response xảy ra lỗi máy chủ
     *
     * Sử dụng khi có lỗi xảy ra từ phía máy chủ, không phải lỗi từ người dùng.
     * Ví dụ: Lỗi kết nối database, lỗi xử lý,...
     *
     * @param string $message Thông báo lỗi
     * @param array $headers HTTP headers bổ sung
     * @return JsonResponse
     */
    protected function serverErrorResponse(string $message = 'Đã xảy ra lỗi từ hệ thống', array $headers = []): JsonResponse
    {
        return $this->errorResponse($message, 500, null, $headers);
    }
}
