<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(name="Users", description="Operations about users")
 */
class UserController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Lấy danh sách người dùng",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="page", in="query", description="Trang số", @OA\Schema(type="integer", default=1)),
     *     @OA\Parameter(name="per_page", in="query", description="Số item mỗi trang", @OA\Schema(type="integer", default=10)),
     *     @OA\Parameter(name="role", in="query", description="Lọc theo role", @OA\Schema(type="string", enum={"admin","user"})),
     *     @OA\Parameter(name="is_active", in="query", description="Lọc theo trạng thái", @OA\Schema(type="boolean")),
     *     @OA\Parameter(name="search", in="query", description="Tìm kiếm theo tên hoặc email", @OA\Schema(type="string")),
     *     @OA\Response(
     *         response=200,
     *         description="Danh sách người dùng phân trang"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $filters = $request->only(['role', 'is_active', 'search']);
        $perPage = $request->input('per_page', 10);
        $users = $this->userService->getAllUsers($filters, $perPage);
        return $this->successResponse($users, 'Lấy danh sách người dùng thành công');
    }

    /**
     * @OA\Post(
     *     path="/api/users",
     *     summary="Tạo người dùng mới",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="password", type="string", format="password"),
     *             @OA\Property(property="role", type="string", enum={"admin","user"}),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Tạo người dùng thành công")
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'sometimes|in:admin,user',
            'is_active' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors());
        }

        $user = $this->userService->createUser($request->all());
        return $this->createdResponse($user, 'Tạo người dùng thành công');
    }

    /**
     * @OA\Get(
     *     path="/api/users/{id}",
     *     summary="Lấy thông tin chi tiết người dùng",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Thông tin người dùng")
     * )
     */
    public function show($id)
    {
        try {
            $user = $this->userService->findUser($id);
            return $this->successResponse($user, 'Lấy thông tin người dùng thành công');
        } catch (ValidationException $e) {
            return $this->notFoundResponse($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/api/users/{id}",
     *     summary="Cập nhật thông tin người dùng",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email"),
     *             @OA\Property(property="role", type="string", enum={"admin","user"}),
     *             @OA\Property(property="is_active", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Cập nhật thành công")
     * )
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255',
                'role' => 'sometimes|in:admin,user',
                'is_active' => 'sometimes|boolean'
            ]);

            $user = $this->userService->updateUser($id, $validatedData);
            return $this->successResponse($user, 'Cập nhật thông tin người dùng thành công');

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Không tìm thấy người dùng');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi cập nhật người dùng');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/users/{id}",
     *     summary="Xóa người dùng",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Xóa thành công")
     * )
     */
    public function destroy($id)
    {
        try {
            $this->userService->deleteUser($id);
            return $this->successResponse(null, 'Xóa người dùng thành công');
        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Không tìm thấy người dùng');
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Có lỗi xảy ra khi xóa người dùng');
        }
    }
}








